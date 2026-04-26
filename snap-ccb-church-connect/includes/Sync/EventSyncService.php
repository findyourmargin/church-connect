<?php
namespace SnapChurchConnect\CCB\Sync;

use SnapChurchConnect\CCB\API\CCBClient;
use SnapChurchConnect\CCB\Logging\Logger;
use SnapChurchConnect\CCB\Support\Helpers;

if (! defined('ABSPATH')) {
	exit;
}

class EventSyncService {
	private $client;
	private $profile_cache = array();

	public function __construct(?CCBClient $client = null) {
		$this->client = $client ? $client : new CCBClient();
	}

	public function sync() {
		$counts = array('created' => 0, 'updated' => 0, 'skipped' => 0, 'failed' => 0);
		$start = current_time('Y-m-d');
		$end   = gmdate('Y-m-d', strtotime('+' . max(1, absint(Helpers::get_option('sync_window_months', 6))) . ' months'));
		Logger::info('sync', 'CCB event sync started.', array('date_start' => $start, 'date_end' => $end));

		$response = $this->client->get_public_calendar_listing($start, $end);
		if (empty($response['success'])) {
			$counts['failed']++;
			Helpers::update_options($this->last_sync_options('failed', $counts));
			Logger::error('sync', 'CCB calendar listing failed.', array('message' => isset($response['message']) ? $response['message'] : 'Unknown error'));
			return array_merge(array('success' => false), $counts);
		}

		$items = $this->extract_calendar_items($response['data']);
		if (Helpers::get_option('merge_multiday_occurrences', 1)) {
			$before_merge = count($items);
			$items = $this->merge_multiday_occurrences($items);
			if (count($items) !== $before_merge) {
				Logger::info('sync', 'Merged consecutive CCB multi-day occurrences.', array('before' => $before_merge, 'after' => count($items)));
			}
		}

		foreach ($items as $item) {
			$result = $this->upsert_event($item);
			isset($counts[$result]) ? $counts[$result]++ : $counts['failed']++;
		}

		$status = $counts['failed'] > 0 ? 'completed_with_errors' : 'completed';
		Helpers::update_options($this->last_sync_options($status, $counts));
		Logger::info('sync', 'CCB event sync finished.', $counts);
		return array_merge(array('success' => true), $counts);
	}

	private function upsert_event(array $item) {
		$event_id = $this->text($this->find_value($item, 'event_name', '@attributes', 'ccb_id'));
		if (! $event_id) {
			$event_id = $this->text($this->find_value($item, 'event_id'));
		}

		$profile = array();
		if ($event_id && Helpers::get_option('fetch_event_profiles', 1)) {
			$profile = $this->get_profile($event_id);
		}

		$event = $this->normalize_event($item, $profile, $event_id);
		if (empty($event['instance_id'])) {
			Logger::warning('sync', 'Skipped CCB calendar item without a generated occurrence key.');
			return 'failed';
		}

		$hash = wp_hash(wp_json_encode($event));
		$post_id = $this->find_existing_post($event['instance_id']);
		$post_status = in_array(Helpers::get_option('sync_post_status', 'publish'), array('publish', 'draft'), true) ? Helpers::get_option('sync_post_status', 'publish') : 'publish';

		$post_data = array(
			'post_type'    => 'church_event',
			'post_title'   => $event['name'],
			'post_content' => $event['description'],
			'post_excerpt' => wp_trim_words(wp_strip_all_tags($event['description']), 30),
			'post_status'  => $post_status,
		);

		if ($post_id) {
			if (get_post_meta($post_id, '_church_connect_sync_hash', true) === $hash) {
				update_post_meta($post_id, '_church_connect_last_synced_at', Helpers::iso_now());
				return 'skipped';
			}
			$post_data['ID'] = $post_id;
			$result = wp_update_post($post_data, true);
			if (is_wp_error($result)) {
				Logger::error('sync', 'Failed to update CCB event.', array('instance_id' => $event['instance_id'], 'error' => $result->get_error_message()));
				return 'failed';
			}
			$this->update_meta($post_id, $event, $hash);
			$this->assign_terms($post_id, $event);
			$this->retire_merged_occurrence_posts($event, $post_id);
			return 'updated';
		}

		$result = wp_insert_post($post_data, true);
		if (is_wp_error($result)) {
			Logger::error('sync', 'Failed to create CCB event.', array('instance_id' => $event['instance_id'], 'error' => $result->get_error_message()));
			return 'failed';
		}

		$this->update_meta((int) $result, $event, $hash);
		$this->assign_terms((int) $result, $event);
		$this->retire_merged_occurrence_posts($event, (int) $result);
		return 'created';
	}

	private function normalize_event(array $item, array $profile, $event_id) {
		$is_merged = ! empty($item['_merged_end_date']);
		$timezone = $this->text($this->find_value($profile, 'timezone'));
		$timezone = $timezone ? $timezone : Helpers::site_timezone();
		$date = $this->text($this->find_value($item, 'date'));
		$end_date = $is_merged ? $this->text($item['_merged_end_date']) : $date;
		$start_time = $this->text($this->find_value($item, 'start_time'));
		$end_time = $this->text($this->find_value($item, 'end_time'));

		if (! $end_time) {
			$end_time = $start_time;
			Logger::warning('sync', 'CCB event missing end_time; using start_time fallback.', array('event_id' => $event_id));
		}

		$start = $is_merged ? '' : $this->text($this->find_value($profile, 'start_datetime'));
		$end = $is_merged ? '' : $this->text($this->find_value($profile, 'end_datetime'));
		$start = $start ? $start : trim($date . ' ' . $start_time);
		$end = $end ? $end : trim($end_date . ' ' . $end_time);
		$start_ts = Helpers::parse_timestamp($start, $timezone);
		$end_ts = Helpers::parse_timestamp($end, $timezone);
		$description = $this->text($this->find_value($profile, 'description'));
		$description = $description ? $description : $this->text($this->find_value($item, 'event_description'));
		$registration_url = $this->first_url($profile);
		$image_url = $this->text($this->find_value($profile, 'image_link'));

		return array(
			'provider'       => 'ccb',
			'external_id'    => sanitize_text_field($event_id),
			'instance_id'    => sanitize_text_field('ccb:' . $event_id . ':' . $date . ':' . $start_time . ':' . $end_time),
			'merged_occurrence_keys' => isset($item['_merged_occurrence_keys']) && is_array($item['_merged_occurrence_keys']) ? array_map('sanitize_text_field', $item['_merged_occurrence_keys']) : array(),
			'name'           => $this->prefer($this->find_value($profile, 'name'), $this->find_value($item, 'event_name'), __('Untitled Event', 'snap-ccb-church-connect')),
			'description'    => wp_kses_post($description),
			'starts_at'      => $start_ts ? gmdate('c', $start_ts) : sanitize_text_field($start),
			'ends_at'        => $end_ts ? gmdate('c', $end_ts) : sanitize_text_field($end),
			'start_ts'       => $start_ts,
			'end_ts'         => $end_ts,
			'timezone'       => sanitize_text_field($timezone),
			'location'       => $this->prefer($this->find_value($profile, 'location'), $this->find_value($item, 'location'), ''),
			'image_url'      => esc_url_raw($image_url),
			'registration_url' => esc_url_raw($registration_url),
			'external_url'   => '',
			'all_day'        => $start_time ? 0 : 1,
			'recurrence'     => $this->text($this->find_value($profile, 'recurrence_description')),
			'repeating'      => $this->text($this->find_value($profile, 'recurrence_description')) ? 1 : 0,
			'updated_at'     => $this->text($this->find_value($profile, 'modified')),
			'group_name'     => $this->text($this->find_value($item, 'group_name')),
			'group_type'     => $this->text($this->find_value($item, 'group_type')),
			'grouping_name'  => $this->text($this->find_value($item, 'grouping_name')),
			'event_type'     => $this->text($this->find_value($item, 'event_type')),
			'raw'            => array('listing' => $item, 'profile' => $profile),
		);
	}

	private function get_profile($event_id) {
		if (isset($this->profile_cache[$event_id])) {
			return $this->profile_cache[$event_id];
		}

		$response = $this->client->get_event_profile($event_id, true);
		if (empty($response['success'])) {
			Logger::warning('sync', 'CCB event_profile failed; using listing data.', array('event_id' => $event_id));
			$this->profile_cache[$event_id] = array();
			return array();
		}

		$this->profile_cache[$event_id] = $response['data'];
		return $response['data'];
	}

	private function merge_multiday_occurrences(array $items) {
		$groups = array();
		$passthrough = array();

		foreach ($items as $item) {
			$key = $this->merge_group_key($item);
			$date = $this->text($this->find_value($item, 'date'));
			if (! $key || ! $date) {
				$passthrough[] = $item;
				continue;
			}

			$groups[$key][] = $item;
		}

		$merged = array();
		foreach ($groups as $group_items) {
			usort(
				$group_items,
				function ($a, $b) {
					$a_date = $this->text($this->find_value($a, 'date'));
					$b_date = $this->text($this->find_value($b, 'date'));
					return strcmp($a_date, $b_date);
				}
			);

			$sequence = array();
			foreach ($group_items as $item) {
				if (empty($sequence) || $this->is_next_day(end($sequence), $item)) {
					$sequence[] = $item;
					continue;
				}

				$merged[] = $this->build_merged_item($sequence);
				$sequence = array($item);
			}

			if (! empty($sequence)) {
				$merged[] = $this->build_merged_item($sequence);
			}
		}

		return array_merge($passthrough, $merged);
	}

	private function merge_group_key(array $item) {
		$event_id = $this->text($this->find_value($item, 'event_name', '@attributes', 'ccb_id'));
		if (! $event_id) {
			$event_id = $this->text($this->find_value($item, 'event_id'));
		}

		if (! $event_id) {
			return '';
		}

		return implode(
			'|',
			array(
				sanitize_key($event_id),
				sanitize_title($this->text($this->find_value($item, 'event_name'))),
				sanitize_title($this->text($this->find_value($item, 'location'))),
				$this->text($this->find_value($item, 'start_time')),
				$this->text($this->find_value($item, 'end_time')),
			)
		);
	}

	private function is_next_day(array $previous, array $current) {
		$previous_date = $this->text($this->find_value($previous, 'date'));
		$current_date = $this->text($this->find_value($current, 'date'));
		if (! $previous_date || ! $current_date) {
			return false;
		}

		$previous_ts = strtotime($previous_date . ' 00:00:00 UTC');
		$current_ts = strtotime($current_date . ' 00:00:00 UTC');
		return false !== $previous_ts && false !== $current_ts && 86400 === ($current_ts - $previous_ts);
	}

	private function build_merged_item(array $sequence) {
		if (count($sequence) < 2) {
			return $sequence[0];
		}

		$first = $sequence[0];
		$last = $sequence[count($sequence) - 1];
		$first['_merged_end_date'] = $this->text($this->find_value($last, 'date'));
		$first['_merged_occurrence_keys'] = array();

		foreach ($sequence as $item) {
			$key = $this->listing_occurrence_key($item);
			if ($key) {
				$first['_merged_occurrence_keys'][] = $key;
			}
		}

		return $first;
	}

	private function listing_occurrence_key(array $item) {
		$event_id = $this->text($this->find_value($item, 'event_name', '@attributes', 'ccb_id'));
		if (! $event_id) {
			$event_id = $this->text($this->find_value($item, 'event_id'));
		}

		$date = $this->text($this->find_value($item, 'date'));
		$start_time = $this->text($this->find_value($item, 'start_time'));
		$end_time = $this->text($this->find_value($item, 'end_time'));

		if (! $event_id || ! $date) {
			return '';
		}

		return sanitize_text_field('ccb:' . $event_id . ':' . $date . ':' . $start_time . ':' . $end_time);
	}

	private function extract_calendar_items($data) {
		$candidates = array();
		$this->collect_item_arrays($data, $candidates, 'root');
		return $candidates;
	}

	private function collect_item_arrays($value, array &$items, $path = 'root') {
		if (! is_array($value)) {
			return;
		}

		if (isset($value['event_name']) || isset($value['date'])) {
			$items[] = $value;
		}

		foreach ($value as $key => $child) {
			if ('@attributes' === $key || '@text' === $key) {
				continue;
			}
			$this->collect_item_arrays($child, $items, $path . '.' . sanitize_key((string) $key));
		}
	}

	private function find_value($array, ...$keys) {
		$current = $array;
		foreach ($keys as $key) {
			if (! is_array($current) || ! array_key_exists($key, $current)) {
				return '';
			}
			$current = $current[$key];
		}
		return $current;
	}

	private function text($value) {
		if (is_array($value)) {
			if (isset($value['@text'])) {
				return sanitize_text_field((string) $value['@text']);
			}
			return '';
		}
		return sanitize_text_field((string) $value);
	}

	private function prefer($first, $second, $fallback) {
		$first = $this->text($first);
		if ($first) {
			return $first;
		}
		$second = $this->text($second);
		return $second ? $second : $fallback;
	}

	private function first_url(array $array) {
		foreach ($array as $value) {
			if (is_array($value)) {
				$found = $this->first_url($value);
				if ($found) {
					return $found;
				}
			} elseif (filter_var($value, FILTER_VALIDATE_URL)) {
				return $value;
			}
		}
		return '';
	}

	private function find_existing_post($instance_id) {
		$query = new \WP_Query(array(
			'post_type' => 'church_event',
			'post_status' => 'any',
			'fields' => 'ids',
			'posts_per_page' => 1,
			'meta_query' => array(
				array('key' => '_church_connect_provider', 'value' => 'ccb'),
				array('key' => '_church_connect_external_instance_id', 'value' => $instance_id),
			),
		));
		return $query->have_posts() ? (int) $query->posts[0] : 0;
	}

	private function update_meta($post_id, array $event, $hash) {
		$meta = array(
			'church_event_start' => $event['starts_at'],
			'church_event_end' => $event['ends_at'],
			'church_event_start_ts' => $event['start_ts'],
			'church_event_end_ts' => $event['end_ts'],
			'church_event_timezone' => $event['timezone'],
			'church_event_location' => $event['location'],
			'church_event_summary' => wp_trim_words(wp_strip_all_tags($event['description']), 30),
			'church_event_description' => $event['description'],
			'church_event_image_url' => $event['image_url'],
			'church_event_registration_url' => $event['registration_url'],
			'church_event_external_url' => $event['external_url'],
			'church_event_all_day' => $event['all_day'],
			'church_event_repeating' => $event['repeating'],
			'church_event_recurrence' => $event['recurrence'],
			'church_event_status' => $event['event_type'] ? $event['event_type'] : 'scheduled',
			'_church_connect_provider' => 'ccb',
			'_church_connect_external_id' => $event['external_id'],
			'_church_connect_external_instance_id' => $event['instance_id'],
			'_church_connect_external_updated_at' => $event['updated_at'],
			'_church_connect_last_synced_at' => Helpers::iso_now(),
			'_church_connect_sync_hash' => $hash,
			'_church_connect_raw_data' => wp_json_encode($event['raw']),
		);

		foreach ($meta as $key => $value) {
			update_post_meta($post_id, $key, $value);
		}
	}

	private function assign_terms($post_id, array $event) {
		if ($event['group_name']) {
			wp_set_object_terms($post_id, $event['group_name'], 'church_ministry', false);
		}
		if ($event['group_type']) {
			wp_set_object_terms($post_id, $event['group_type'], 'church_ministry', true);
		}
		if ($event['grouping_name']) {
			wp_set_object_terms($post_id, $event['grouping_name'], 'church_event_category', false);
		}
	}

	private function retire_merged_occurrence_posts(array $event, $kept_post_id) {
		if (empty($event['merged_occurrence_keys']) || ! is_array($event['merged_occurrence_keys'])) {
			return;
		}

		$keys = array_values(array_diff($event['merged_occurrence_keys'], array($event['instance_id'])));
		if (empty($keys)) {
			return;
		}

		$query = new \WP_Query(array(
			'post_type'      => 'church_event',
			'post_status'    => array('publish', 'pending', 'draft', 'future', 'private'),
			'fields'         => 'ids',
			'posts_per_page' => 100,
			'meta_query'     => array(
				array('key' => '_church_connect_provider', 'value' => 'ccb'),
				array('key' => '_church_connect_external_id', 'value' => $event['external_id']),
				array('key' => '_church_connect_external_instance_id', 'value' => $keys, 'compare' => 'IN'),
			),
		));

		$retired = 0;
		foreach ($query->posts as $post_id) {
			$post_id = (int) $post_id;
			if ($post_id === (int) $kept_post_id) {
				continue;
			}

			$result = wp_update_post(array('ID' => $post_id, 'post_status' => 'draft'), true);
			if (! is_wp_error($result)) {
				$retired++;
			}
		}

		if ($retired > 0) {
			Logger::info('sync', 'Drafted merged duplicate CCB occurrence posts.', array('event_id' => $event['external_id'], 'retired' => $retired));
		}
	}

	private function last_sync_options($status, array $counts) {
		return array(
			'last_sync_time' => Helpers::iso_now(),
			'last_sync_status' => $status,
			'last_sync_created' => $counts['created'],
			'last_sync_updated' => $counts['updated'],
			'last_sync_skipped' => $counts['skipped'],
			'last_sync_failed' => $counts['failed'],
		);
	}
}
