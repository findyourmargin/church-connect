<?php
namespace SnapChurchConnect\PCO\Support;

if (! defined('ABSPATH')) {
	exit;
}

class Helpers {
	public static function default_options() {
		return array(
			'client_id'              => '',
			'secret'                 => '',
			'connection_status'      => 'not_tested',
			'auto_sync_enabled'      => 0,
			'sync_frequency'         => 'hourly',
			'sync_post_status'       => 'publish',
			'sync_window_months'     => 6,
			'expired_event_handling' => 'keep',
			'default_events_per_page'=> 6,
			'default_layout'         => 'cards',
			'date_format'            => get_option('date_format', 'F j, Y'),
			'time_format'            => get_option('time_format', 'g:i a'),
			'button_text'            => 'View Event',
			'show_image'             => 1,
			'show_location'          => 1,
			'last_sync_time'         => '',
			'last_sync_status'       => '',
			'last_sync_created'      => 0,
			'last_sync_updated'      => 0,
			'last_sync_skipped'      => 0,
			'last_sync_failed'       => 0,
			'delete_data_on_uninstall' => 0,
		);
	}

	public static function ensure_default_options() {
		$options = get_option(SNAP_PCO_CHURCH_CONNECT_OPTION);
		if (! is_array($options)) {
			add_option(SNAP_PCO_CHURCH_CONNECT_OPTION, self::default_options());
			return;
		}

		update_option(SNAP_PCO_CHURCH_CONNECT_OPTION, wp_parse_args($options, self::default_options()));
	}

	public static function get_options() {
		self::ensure_default_options();
		return wp_parse_args((array) get_option(SNAP_PCO_CHURCH_CONNECT_OPTION, array()), self::default_options());
	}

	public static function get_option($key, $default = null) {
		$options = self::get_options();
		return array_key_exists($key, $options) ? $options[$key] : $default;
	}

	public static function update_options(array $updates) {
		$options = self::get_options();
		update_option(SNAP_PCO_CHURCH_CONNECT_OPTION, array_merge($options, $updates));
	}

	public static function get_client_id() {
		if (defined('SNAP_PCO_CHURCH_CONNECT_CLIENT_ID') && SNAP_PCO_CHURCH_CONNECT_CLIENT_ID) {
			return (string) SNAP_PCO_CHURCH_CONNECT_CLIENT_ID;
		}

		return (string) self::get_option('client_id', '');
	}

	public static function get_secret() {
		if (defined('SNAP_PCO_CHURCH_CONNECT_SECRET') && SNAP_PCO_CHURCH_CONNECT_SECRET) {
			return (string) SNAP_PCO_CHURCH_CONNECT_SECRET;
		}

		return (string) self::get_option('secret', '');
	}

	public static function site_timezone() {
		$timezone = wp_timezone_string();
		return $timezone ? $timezone : 'UTC';
	}

	public static function iso_now() {
		return gmdate('c');
	}

	public static function parse_timestamp($value) {
		if (! $value) {
			return 0;
		}

		$timestamp = strtotime((string) $value);
		return false === $timestamp ? 0 : (int) $timestamp;
	}

	public static function bool_value($value) {
		return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
	}
}
