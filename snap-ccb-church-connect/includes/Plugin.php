<?php
namespace SnapChurchConnect\CCB;

use SnapChurchConnect\CCB\Admin\AdminMenu;
use SnapChurchConnect\CCB\Admin\EventDetailsMetaBox;
use SnapChurchConnect\CCB\Admin\Settings;
use SnapChurchConnect\CCB\Content\ContentTypes;
use SnapChurchConnect\CCB\Content\MetaFields;
use SnapChurchConnect\CCB\Content\Taxonomies;
use SnapChurchConnect\CCB\Frontend\Shortcodes;
use SnapChurchConnect\CCB\Frontend\SingleEventContent;
use SnapChurchConnect\CCB\REST\EventsController;
use SnapChurchConnect\CCB\Sync\Scheduler;

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
		$event_details = new EventDetailsMetaBox();
		$rest          = new EventsController();
		$shortcodes    = new Shortcodes();
		$single_event  = new SingleEventContent();
		$scheduler     = new Scheduler();

		add_action('init', array($content_types, 'register'));
		add_action('init', array($taxonomies, 'register'));
		add_action('init', array($meta_fields, 'register'));
		add_action('admin_init', array($settings, 'register'));
		add_action('admin_menu', array($admin_menu, 'register'));
		add_action('add_meta_boxes_church_event', array($event_details, 'register'));
		add_action('admin_post_snap_ccb_church_connect_sync_now', array($admin_menu, 'handle_sync_now'));
		add_action('admin_post_snap_ccb_church_connect_test_connection', array($admin_menu, 'handle_test_connection'));
		add_action('admin_post_snap_ccb_church_connect_clear_logs', array($admin_menu, 'handle_clear_logs'));
		add_action('admin_post_snap_ccb_church_connect_refresh_calendars', array($admin_menu, 'handle_refresh_calendars'));
		add_action('rest_api_init', array($rest, 'register_routes'));
		add_action('init', array($shortcodes, 'register'));
		add_action('init', array($single_event, 'register'));
		add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));

		$scheduler->register();
	}

	public function load_textdomain() {
		load_plugin_textdomain('snap-ccb-church-connect', false, dirname(plugin_basename(SNAP_CCB_CHURCH_CONNECT_FILE)) . '/languages');
	}

	public function enqueue_frontend_assets() {
		wp_register_style(
			'snap-ccb-church-connect-frontend',
			SNAP_CCB_CHURCH_CONNECT_URL . 'assets/frontend.css',
			array(),
			SNAP_CCB_CHURCH_CONNECT_VERSION
		);
	}

	public function enqueue_admin_assets($hook) {
		if ('toplevel_page_snap-ccb-church-connect' !== $hook && 'post.php' !== $hook && 'post-new.php' !== $hook) {
			return;
		}

		wp_enqueue_style(
			'snap-ccb-church-connect-admin',
			SNAP_CCB_CHURCH_CONNECT_URL . 'assets/admin.css',
			array(),
			SNAP_CCB_CHURCH_CONNECT_VERSION
		);
	}
}
