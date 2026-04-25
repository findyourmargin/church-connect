<?php
/**
 * Plugin Name: Snap! PCO Church Connect
 * Description: Sync Planning Center Calendar events into WordPress using native church_event content for Bricks, Elementor, shortcodes, and app-ready REST endpoints.
 * Version: 0.1.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Snap! Church Connect
 * License: GPL-2.0-or-later
 * Text Domain: snap-pco-church-connect
 * Update URI: false
 *
 * @package SnapChurchConnect\PCO
 */

if (! defined('ABSPATH')) {
	exit;
}

define('SNAP_PCO_CHURCH_CONNECT_VERSION', '0.1.0');
define('SNAP_PCO_CHURCH_CONNECT_FILE', __FILE__);
define('SNAP_PCO_CHURCH_CONNECT_PATH', plugin_dir_path(__FILE__));
define('SNAP_PCO_CHURCH_CONNECT_URL', plugin_dir_url(__FILE__));
define('SNAP_PCO_CHURCH_CONNECT_OPTION', 'snap_pco_church_connect_options');
define('SNAP_PCO_CHURCH_CONNECT_LOG_OPTION', 'snap_pco_church_connect_logs');
define('SNAP_PCO_CHURCH_CONNECT_CRON_HOOK', 'snap_pco_church_connect_sync_events');
define('SNAP_PCO_CHURCH_CONNECT_ADMIN_REST_NAMESPACE', 'snap-pco-church-connect/v1');
define('SNAP_PCO_CHURCH_CONNECT_PUBLIC_REST_NAMESPACE', 'church-connect/v1');

spl_autoload_register(
	static function ($class) {
		$prefix = 'SnapChurchConnect\\PCO\\';
		if (0 !== strpos($class, $prefix)) {
			return;
		}

		$relative = str_replace('\\', '/', substr($class, strlen($prefix)));
		$file     = SNAP_PCO_CHURCH_CONNECT_PATH . 'includes/' . $relative . '.php';

		if (is_readable($file)) {
			require_once $file;
		}
	}
);

register_activation_hook(__FILE__, array('SnapChurchConnect\\PCO\\Activator', 'activate'));
register_deactivation_hook(__FILE__, array('SnapChurchConnect\\PCO\\Deactivator', 'deactivate'));

add_action(
	'plugins_loaded',
	static function () {
		// If a private update server is added later, change Update URI above to that server/plugin URI.
		$plugin = new SnapChurchConnect\PCO\Plugin();
		$plugin->run();
	}
);
