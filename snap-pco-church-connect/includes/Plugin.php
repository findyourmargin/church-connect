<?php
namespace SnapChurchConnect\PCO;

use SnapChurchConnect\PCO\Admin\AdminMenu;
use SnapChurchConnect\PCO\Admin\Settings;
use SnapChurchConnect\PCO\Content\ContentTypes;
use SnapChurchConnect\PCO\Content\MetaFields;
use SnapChurchConnect\PCO\Content\Taxonomies;
use SnapChurchConnect\PCO\Frontend\Shortcodes;
use SnapChurchConnect\PCO\REST\EventsController;
use SnapChurchConnect\PCO\Sync\Scheduler;

if (! defined('ABSPATH')) {
	exit;
}

class Plugin {
	public function run() {
		add_action('init', array($this, 'load_textdomain'), 1);

		$content_types = new ContentTypes();
		$taxonomies    = new Taxonomies();
		$meta_fields   = new MetaFields();
		$settings      = new Settings();
		$admin_menu    = new AdminMenu($settings);
		$rest          = new EventsController();
		$shortcodes    = new Shortcodes();
		$scheduler     = new Scheduler();

		add_action('init', array($content_types, 'register'));
		add_action('init', array($taxonomies, 'register'));
		add_action('init', array($meta_fields, 'register'));
		add_action('admin_init', array($settings, 'register'));
		add_action('admin_menu', array($admin_menu, 'register'));
		add_action('admin_post_snap_pco_church_connect_sync_now', array($admin_menu, 'handle_sync_now'));
		add_action('admin_post_snap_pco_church_connect_test_connection', array($admin_menu, 'handle_test_connection'));
		add_action('admin_post_snap_pco_church_connect_clear_logs', array($admin_menu, 'handle_clear_logs'));
		add_action('rest_api_init', array($rest, 'register_routes'));
		add_action('init', array($shortcodes, 'register'));
		add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));

		$scheduler->register();
	}

	public function load_textdomain() {
		load_plugin_textdomain('snap-pco-church-connect', false, dirname(plugin_basename(SNAP_PCO_CHURCH_CONNECT_FILE)) . '/languages');
	}

	public function enqueue_frontend_assets() {
		wp_register_style(
			'snap-pco-church-connect-frontend',
			SNAP_PCO_CHURCH_CONNECT_URL . 'assets/frontend.css',
			array(),
			SNAP_PCO_CHURCH_CONNECT_VERSION
		);
	}

	public function enqueue_admin_assets($hook) {
		if ('toplevel_page_snap-pco-church-connect' !== $hook) {
			return;
		}

		wp_enqueue_style(
			'snap-pco-church-connect-admin',
			SNAP_PCO_CHURCH_CONNECT_URL . 'assets/admin.css',
			array(),
			SNAP_PCO_CHURCH_CONNECT_VERSION
		);
	}
}
