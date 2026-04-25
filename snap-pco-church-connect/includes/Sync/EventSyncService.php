<?php
namespace SnapChurchConnect\PCO\Sync;

use SnapChurchConnect\PCO\API\PlanningCenterClient;
use SnapChurchConnect\PCO\Logging\Logger;
use SnapChurchConnect\PCO\Support\Helpers;

if (! defined('ABSPATH')) {
	exit;
}

class EventSyncService {
	private $client;

	public function __construct(?PlanningCenterClient $client = null) {
		$this->client = $client ? $client : new PlanningCenterClient();
	}

	public function sync() {
		$counts = array('created' => 0, 'updated' => 0, 'skipped' => 0, 'failed' => 0);
		Logger::info('sync', 'Event sync started.');

		$args = $this->build_query_args();
		$response = $this->client->get_event_instances($args);

		if (empty($response['success'])) {
			$counts['failed']++;
			Helpers::update_options($this->last_sync_options('failed', $counts));
			Logger::error('sync', 'Event sync failed before processing events.', array('message' => isset($response['message']) ? $response['message'] : 'Unknown error'));
			return array_merge(array('success' => false), $counts);
		}

		$included = isset($response['included']) && is_array($response['included']) ? $response['included'] : array();

		foreach ((array) $response['data'] as $item) {
			$result = $this->upsert_event($item, $included);
			if (isset($counts[$result])) {
				$counts[$result]++;
			} else {
				$counts['failed']++;
			}
		}

		$status = $counts['failed'] > 0 ? 'completed_with_errors' : 'completed';
		Helpers::update_options($this->last_sync_options($status, $counts));
		Logger::info('sync', 'Event sync finished.', $counts);

		return array_merge(array('success' => true), $counts);
	}

	private function build_query_args() {
		$months = max(1, absint(Helpers::get_option('sync_window_months', 6)));
		return array(
			'order'                 => 'starts_at',
			'per_page'              => 100,
			'include'               => 'event,tags',
			'where[starts_at][gte]' => gmdate('c'),
			'where[starts_at][lte]' => gmdate('c', strtotime('+' . $months . ' months')),
		);
	}

	private function upsert_event(array $item, array $included) {
		$normalized = $this->normalize_event($item, $included);
		if (empty($normalized['instance_id'])) {
			Logger::warning('sync', 'Skipped event instance without an ID.');
			return 'failed';
		}

		if ('blockout' === strtolower((string) $normalized['kind'])) {
			return 'skipped';
		}

		$hash = wp_hash(wp_json_encode($normalized));
		$post_id = $this->find_existing_post($normalized['instance_id']);
		$post_status = Helpers::get_option('sync_post_status', 'publish');
		$post_status = in_array($post_status, array('publish', 'draft'), true) ? $post_status : 'publish';

		if ($post_id) {
			$existing_hash = get_post_meta($post_id, '_church_connect_sync_hash', true);
			if ($existing_hash === $hash) {
				update_post_meta($post_id, '_church_connect_last_synced_at', Helpers::iso_now());
				return 'skipped';
			}

			$result = wp_update_post(
				array(
					'ID'           => $post_id,
					'post_title'   => $normalized['name'],
					'post_content' => $normalized['description'],
					'post_excerpt' => wp_trim_words(wp_strip_all_tags($normalized['description']), 30),
					'post_status'  => $post_status,
				),
				true
			);

			if (is_wp_error($result)) {
				Logger::error('sync', 'Failed to update event post.', array('instance_id' => $normalized['instance_id'], 'error' => $result->get_error_message()));
				return 'failed';
			}

			$this->update_meta($post_id, $normalized, $hash);
			return 'updated';
		}

		$result = wp_insert_post(
			array(
				'post_type'    => 'church_event',
				'post_title'   => $normalized['name'],
				'post_content' => $normalized['description'],
				'post_excerpt' => wp_trim_words(wp_strip_all_tags($normalized['description']), 30),
				'post_status'  => $post_status,
			),
			true
		);

		if (is_wp_error($result)) {
			Logger::error('sync', 'Failed to create event post.', array('instance_id' => $normalized['instance_id'], 'error' => $result->get_error_message()));
			return 'failed';
		}

		$this->update_meta((int) $result, $normalized, $hash);
		return 'created';
	}

	private function normalize_event(array $item, array $included) {
		$attributes = isset($item['attributes']) && is_array($item['attributes']) ? $item['attributes'] : array();
		$event_id   = $this->related_id($item, 'event');
		$starts_at  = isset($attributes['starts_at']) ? (string) $attributes['starts_at'] : '';
		$ends_at    = isset($attributes['ends_at']) ? (string) $attributes['ends_at'] : '';
		$recurrence = isset($attributes['recurrence_description']) ? $attributes['recurrence_description'] : '';

		if (! $recurrence && isset($attributes['compact_recurrence_description'])) {
			$recurrence = $attributes['compact_recurrence_description'];
		}

		return array(
			'provider'          => 'pco',
			'external_id'       => $event_id,
			'instance_id'       => isset($item['id']) ? (string) $item['id'] : '',
			'name'              => isset($attributes['name']) ? sanitize_text_field($attributes['name']) : __('Untitled Event', 'snap-pco-church-connect'),
			'description'       => isset($attributes['description']) ? wp_kses_post($attributes['description']) : '',
			'starts_at'         => $starts_at,
			'ends_at'           => $ends_at,
			'start_ts'          => Helpers::parse_timestamp($starts_at),
			'end_ts'            => Helpers::parse_timestamp($ends_at),
			'timezone'          => Helpers::site_timezone(),
			'location'          => isset($attributes['location']) ? sanitize_text_field($attributes['location']) : '',
			'image_url'         => isset($attributes['image_url']) ? esc_url_raw($attributes['image_url']) : '',
			'external_url'      => isset($attributes['church_center_url']) ? esc_url_raw($attributes['church_center_url']) : '',
			'all_day'           => ! empty($attributes['all_day_event']) ? 1 : 0,
			'recurrence'        => sanitize_text_field((string) $recurrence),
			'repeating'         => ! empty($attributes['recurrence']) || ! empty($recurrence) ? 1 : 0,
			'kind'              => isset($attributes['kind']) ? sanitize_key($attributes['kind']) : '',
			'updated_at'        => isset($attributes['updated_at']) ? sanitize_text_field($attributes['updated_at']) : '',
			'raw'               => $item,
		);
	}

	private function related_id(array $item, $relationship) {
		if (isset($item['relationships'][$relationship]['data']['id'])) {
			return sanitize_text_field((string) $item['relationships'][$relationship]['data']['id']);
		}

		return '';
	}

	private function find_existing_post($instance_id) {
		$query = new \WP_Query(
			array(
				'post_type'      => 'church_event',
				'post_status'    => 'any',
				'fields'         => 'ids',
				'posts_per_page' => 1,
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'key'   => '_church_connect_provider',
						'value' => 'pco',
					),
					array(
						'key'   => '_church_connect_external_instance_id',
						'value' => $instance_id,
					),
				),
			)
		);

		return $query->have_posts() ? (int) $query->posts[0] : 0;
	}

	private function update_meta($post_id, array $event, $hash) {
		$meta = array(
			'church_event_start'                    => $event['starts_at'],
			'church_event_end'                      => $event['ends_at'],
			'church_event_start_ts'                 => $event['start_ts'],
			'church_event_end_ts'                   => $event['end_ts'],
			'church_event_timezone'                 => $event['timezone'],
			'church_event_location'                 => $event['location'],
			'church_event_summary'                  => wp_trim_words(wp_strip_all_tags($event['description']), 30),
			'church_event_description'              => $event['description'],
			'church_event_image_url'                => $event['image_url'],
			'church_event_external_url'             => $event['external_url'],
			'church_event_all_day'                  => $event['all_day'],
			'church_event_repeating'                => $event['repeating'],
			'church_event_recurrence'               => $event['recurrence'],
			'church_event_status'                   => 'scheduled',
			'_church_connect_provider'              => 'pco',
			'_church_connect_external_id'           => $event['external_id'],
			'_church_connect_external_instance_id'  => $event['instance_id'],
			'_church_connect_external_updated_at'   => $event['updated_at'],
			'_church_connect_last_synced_at'        => Helpers::iso_now(),
			'_church_connect_sync_hash'             => $hash,
			'_church_connect_raw_data'              => wp_json_encode($event['raw']),
		);

		foreach ($meta as $key => $value) {
			update_post_meta($post_id, $key, $value);
		}
	}

	private function last_sync_options($status, array $counts) {
		return array(
			'last_sync_time'    => Helpers::iso_now(),
			'last_sync_status'  => $status,
			'last_sync_created' => $counts['created'],
			'last_sync_updated' => $counts['updated'],
			'last_sync_skipped' => $counts['skipped'],
			'last_sync_failed'  => $counts['failed'],
		);
	}
}
