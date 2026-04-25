<?php
namespace SnapChurchConnect\PCO\Logging;

use SnapChurchConnect\PCO\Support\Helpers;

if (! defined('ABSPATH')) {
	exit;
}

class Logger {
	const MAX_ENTRIES = 100;

	public static function info($source, $message, array $context = array()) {
		self::log('info', $source, $message, $context);
	}

	public static function warning($source, $message, array $context = array()) {
		self::log('warning', $source, $message, $context);
	}

	public static function error($source, $message, array $context = array()) {
		self::log('error', $source, $message, $context);
	}

	public static function log($level, $source, $message, array $context = array()) {
		$entries = self::get_entries();
		array_unshift(
			$entries,
			array(
				'timestamp' => Helpers::iso_now(),
				'level'     => self::sanitize_level($level),
				'source'    => sanitize_key($source),
				'message'   => sanitize_text_field($message),
				'context'   => self::sanitize_context($context),
			)
		);

		update_option(SNAP_PCO_CHURCH_CONNECT_LOG_OPTION, array_slice($entries, 0, self::MAX_ENTRIES), false);
	}

	public static function get_entries() {
		$entries = get_option(SNAP_PCO_CHURCH_CONNECT_LOG_OPTION, array());
		return is_array($entries) ? $entries : array();
	}

	public static function clear() {
		update_option(SNAP_PCO_CHURCH_CONNECT_LOG_OPTION, array(), false);
	}

	private static function sanitize_level($level) {
		return in_array($level, array('info', 'warning', 'error'), true) ? $level : 'info';
	}

	private static function sanitize_context(array $context) {
		$blocked = array('secret', 'authorization', 'password', 'token', 'client_secret');
		$clean   = array();

		foreach ($context as $key => $value) {
			$key = sanitize_key($key);
			if (in_array($key, $blocked, true)) {
				continue;
			}

			if (is_scalar($value) || null === $value) {
				$clean[$key] = sanitize_text_field((string) $value);
			} elseif (is_array($value)) {
				$clean[$key] = self::sanitize_context($value);
			}
		}

		return $clean;
	}
}
