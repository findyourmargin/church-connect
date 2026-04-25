<?php
/**
 * Plugin Name: Snap! CCB Church Connect
 * Description: Sync Church Community Builder / Pushpay ChMS calendar events into WordPress using native church_event content for Bricks, Elementor, shortcodes, and app-ready REST endpoints.
 * Version: 0.1.6
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Snap! Church Connect
 * License: GPL-2.0-or-later
 * Text Domain: snap-ccb-church-connect
 * Update URI: false
 *
 * @package SnapChurchConnect\CCB
 */

if (! defined('ABSPATH')) {
	exit;
}

define('SNAP_CCB_CHURCH_CONNECT_VERSION', '0.1.6');
define('SNAP_CCB_CHURCH_CONNECT_FILE', __FILE__);
define('SNAP_CCB_CHURCH_CONNECT_PATH', plugin_dir_path(__FILE__));
define('SNAP_CCB_CHURCH_CONNECT_URL', plugin_dir_url(__FILE__));
define('SNAP_CCB_CHURCH_CONNECT_OPTION', 'snap_ccb_church_connect_options');
define('SNAP_CCB_CHURCH_CONNECT_LOG_OPTION', 'snap_ccb_church_connect_logs');
define('SNAP_CCB_CHURCH_CONNECT_CRON_HOOK', 'snap_ccb_church_connect_sync_events');
define('SNAP_CCB_CHURCH_CONNECT_ADMIN_REST_NAMESPACE', 'snap-ccb-church-connect/v1');
define('SNAP_CCB_CHURCH_CONNECT_PUBLIC_REST_NAMESPACE', 'church-connect/v1');

spl_autoload_register(
	static function ($class) {
		$prefix = 'SnapChurchConnect\\CCB\\';
		if (0 !== strpos($class, $prefix)) {
			return;
		}

		$relative = str_replace('\\', '/', substr($class, strlen($prefix)));
		$file     = SNAP_CCB_CHURCH_CONNECT_PATH . 'includes/' . $relative . '.php';

		if (is_readable($file)) {
			require_once $file;
		}
	}
);

register_activation_hook(__FILE__, array('SnapChurchConnect\\CCB\\Activator', 'activate'));
register_deactivation_hook(__FILE__, array('SnapChurchConnect\\CCB\\Deactivator', 'deactivate'));

add_action(
	'plugins_loaded',
	static function () {
		// If a private update server is added later, change Update URI above to that server/plugin URI.
		$plugin = new SnapChurchConnect\CCB\Plugin();
		$plugin->run();
	}
);
