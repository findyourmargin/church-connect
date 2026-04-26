<?php
namespace SnapChurchConnect\CCB\Support;

if (! defined('ABSPATH')) {
	exit;
}

class Helpers {
	public static function default_options() {
		return array(
			'account'                => '',
			'username'               => '',
			'password'               => '',
			'api_base_url'           => '',
			'connection_status'      => 'not_tested',
			'auto_sync_enabled'      => 0,
			'sync_frequency'         => 'hourly',
			'sync_post_status'       => 'publish',
			'sync_window_months'     => 6,
			'fetch_event_profiles'   => 1,
			'merge_multiday_occurrences' => 0,
			'expired_event_handling' => 'keep',
			'default_events_per_page'=> 6,
			'default_layout'         => 'cards',
			'date_format'            => get_option('date_format', 'F j, Y'),
			'time_format'            => get_option('time_format', 'g:i a'),
			'button_text'            => 'View Event',
			'show_image'             => 1,
			'require_image'          => 0,
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
		$options = get_option(SNAP_CCB_CHURCH_CONNECT_OPTION);
		if (! is_array($options)) {
			add_option(SNAP_CCB_CHURCH_CONNECT_OPTION, self::default_options());
			return;
		}

		$merged = wp_parse_args($options, self::default_options());
		if ($merged !== $options) {
			update_option(SNAP_CCB_CHURCH_CONNECT_OPTION, $merged);
		}
	}

	public static function get_options() {
		self::ensure_default_options();
		return wp_parse_args((array) get_option(SNAP_CCB_CHURCH_CONNECT_OPTION, array()), self::default_options());
	}

	public static function get_option($key, $default = null) {
		$options = self::get_options();
		return array_key_exists($key, $options) ? $options[$key] : $default;
	}

	public static function update_options(array $updates) {
		wp_cache_delete(SNAP_CCB_CHURCH_CONNECT_OPTION, 'options');
		$updated = update_option(SNAP_CCB_CHURCH_CONNECT_OPTION, array_merge(self::get_options(), $updates));
		wp_cache_delete(SNAP_CCB_CHURCH_CONNECT_OPTION, 'options');
		return $updated;
	}

	public static function get_account() {
		if (defined('SNAP_CCB_CHURCH_CONNECT_ACCOUNT') && SNAP_CCB_CHURCH_CONNECT_ACCOUNT) {
			return (string) SNAP_CCB_CHURCH_CONNECT_ACCOUNT;
		}
		return (string) self::get_option('account', '');
	}

	public static function get_username() {
		if (defined('SNAP_CCB_CHURCH_CONNECT_USERNAME') && SNAP_CCB_CHURCH_CONNECT_USERNAME) {
			return (string) SNAP_CCB_CHURCH_CONNECT_USERNAME;
		}
		return (string) self::get_option('username', '');
	}

	public static function get_password() {
		if (defined('SNAP_CCB_CHURCH_CONNECT_PASSWORD') && SNAP_CCB_CHURCH_CONNECT_PASSWORD) {
			return (string) SNAP_CCB_CHURCH_CONNECT_PASSWORD;
		}
		return (string) self::get_option('password', '');
	}

	public static function get_api_base_url() {
		if (defined('SNAP_CCB_CHURCH_CONNECT_API_BASE_URL') && SNAP_CCB_CHURCH_CONNECT_API_BASE_URL) {
			return self::normalize_api_url((string) SNAP_CCB_CHURCH_CONNECT_API_BASE_URL);
		}

		$override = (string) self::get_option('api_base_url', '');
		if ($override) {
			return self::normalize_api_url($override);
		}

		return self::normalize_api_url(self::get_account());
	}

	public static function normalize_account($value) {
		$value = trim((string) $value);
		$value = preg_replace('#^https?://#i', '', $value);
		$value = preg_replace('#/.*$#', '', $value);
		$value = preg_replace('#\.ccbchurch\.com$#i', '', $value);
		return sanitize_key($value);
	}

	public static function normalize_api_url($value) {
		$value = trim((string) $value);
		if (! $value) {
			return '';
		}

		if (false === strpos($value, '://')) {
			$value = self::normalize_account($value) . '.ccbchurch.com';
			$value = 'https://' . $value;
		}

		$parts = wp_parse_url($value);
		if (! is_array($parts) || empty($parts['host'])) {
			return '';
		}

		$scheme = isset($parts['scheme']) ? strtolower($parts['scheme']) : 'https';
		if ('https' !== $scheme) {
			return '';
		}

		$path = isset($parts['path']) && $parts['path'] ? $parts['path'] : '/api.php';
		if (false === strpos($path, 'api.php')) {
			$path = rtrim($path, '/') . '/api.php';
		}

		return esc_url_raw('https://' . strtolower($parts['host']) . $path);
	}

	public static function site_timezone() {
		$timezone = wp_timezone_string();
		return $timezone ? $timezone : 'UTC';
	}

	public static function iso_now() {
		return gmdate('c');
	}

	public static function parse_timestamp($value, $timezone = '') {
		if (! $value) {
			return 0;
		}

		try {
			$zone = new \DateTimeZone($timezone ? $timezone : self::site_timezone());
			$date = new \DateTime((string) $value, $zone);
			return (int) $date->format('U');
		} catch (\Exception $e) {
			$timestamp = strtotime((string) $value);
			return false === $timestamp ? 0 : (int) $timestamp;
		}
	}
}
