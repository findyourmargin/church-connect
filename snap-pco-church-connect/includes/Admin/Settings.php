<?php
namespace SnapChurchConnect\PCO\Admin;

use SnapChurchConnect\PCO\Support\Helpers;
use SnapChurchConnect\PCO\Sync\Scheduler;

if (! defined('ABSPATH')) {
	exit;
}

class Settings {
	public function register() {
		register_setting(
			'snap_pco_church_connect_settings',
			SNAP_PCO_CHURCH_CONNECT_OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array($this, 'sanitize'),
				'default'           => Helpers::default_options(),
			)
		);

		add_action('updated_option', array($this, 'maybe_reschedule'), 10, 3);
	}

	public function sanitize($input) {
		$old = Helpers::get_options();
		$raw = is_array($input) ? $input : array();

		$clean = $old;
		$clean['client_id'] = isset($raw['client_id']) ? sanitize_text_field($raw['client_id']) : $old['client_id'];

		if (isset($raw['secret']) && '' !== trim((string) $raw['secret'])) {
			$clean['secret'] = sanitize_text_field($raw['secret']);
		}

		if (array_key_exists('auto_sync_enabled', $raw)) {
			$clean['auto_sync_enabled'] = ! empty($raw['auto_sync_enabled']) ? 1 : 0;
		}

		if (isset($raw['sync_frequency'])) {
			$frequency = sanitize_key($raw['sync_frequency']);
			$clean['sync_frequency'] = in_array($frequency, array('every_15_minutes', 'every_30_minutes', 'hourly', 'twicedaily', 'daily'), true) ? $frequency : 'hourly';
		}

		if (isset($raw['sync_post_status'])) {
			$status = sanitize_key($raw['sync_post_status']);
			$clean['sync_post_status'] = in_array($status, array('publish', 'draft'), true) ? $status : 'publish';
		}

		if (isset($raw['sync_window_months'])) {
			$clean['sync_window_months'] = max(1, min(24, absint($raw['sync_window_months'])));
		}

		if (isset($raw['expired_event_handling'])) {
			$expired = sanitize_key($raw['expired_event_handling']);
			$clean['expired_event_handling'] = in_array($expired, array('keep', 'draft', 'trash'), true) ? $expired : 'keep';
		}

		if (isset($raw['default_events_per_page'])) {
			$clean['default_events_per_page'] = max(1, min(100, absint($raw['default_events_per_page'])));
		}

		if (isset($raw['default_layout'])) {
			$layout = sanitize_key($raw['default_layout']);
			$clean['default_layout'] = in_array($layout, array('cards', 'list'), true) ? $layout : 'cards';
		}

		if (isset($raw['date_format'])) {
			$clean['date_format'] = sanitize_text_field($raw['date_format']);
		}

		if (isset($raw['time_format'])) {
			$clean['time_format'] = sanitize_text_field($raw['time_format']);
		}

		if (isset($raw['button_text'])) {
			$clean['button_text'] = sanitize_text_field($raw['button_text']);
		}

		if (array_key_exists('show_image', $raw)) {
			$clean['show_image'] = ! empty($raw['show_image']) ? 1 : 0;
		}

		if (array_key_exists('show_location', $raw)) {
			$clean['show_location'] = ! empty($raw['show_location']) ? 1 : 0;
		}

		return $clean;
	}

	public function maybe_reschedule($option, $old_value, $value) {
		if (SNAP_PCO_CHURCH_CONNECT_OPTION !== $option || ! is_array($old_value) || ! is_array($value)) {
			return;
		}

		$old_enabled = isset($old_value['auto_sync_enabled']) ? (int) $old_value['auto_sync_enabled'] : 0;
		$new_enabled = isset($value['auto_sync_enabled']) ? (int) $value['auto_sync_enabled'] : 0;
		$old_frequency = isset($old_value['sync_frequency']) ? $old_value['sync_frequency'] : 'hourly';
		$new_frequency = isset($value['sync_frequency']) ? $value['sync_frequency'] : 'hourly';

		if ($old_enabled !== $new_enabled || $old_frequency !== $new_frequency) {
			(new Scheduler())->reschedule();
		}
	}
}
