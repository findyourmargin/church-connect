<?php
namespace SnapChurchConnect\CCB\Admin;

use SnapChurchConnect\CCB\API\CCBClient;
use SnapChurchConnect\CCB\Logging\Logger;
use SnapChurchConnect\CCB\Support\Helpers;
use SnapChurchConnect\CCB\Sync\EventSyncService;

if (! defined('ABSPATH')) {
	exit;
}

class AdminMenu {
	private $settings;

	public function __construct(Settings $settings) {
		$this->settings = $settings;
	}

	public function register() {
		add_menu_page(
			__('Snap! CCB Church Connect', 'snap-ccb-church-connect'),
			__('Snap! CCB Connect', 'snap-ccb-church-connect'),
			'manage_options',
			'snap-ccb-church-connect',
			array($this, 'render'),
			'dashicons-admin-site-alt3',
			58
		);
	}

	public function render() {
		if (! current_user_can('manage_options')) {
			wp_die(esc_html__('You do not have permission to access this page.', 'snap-ccb-church-connect'));
		}

		$tab     = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'dashboard';
		$tabs    = $this->tabs();
		$options = Helpers::get_options();

		if (! isset($tabs[$tab])) {
			$tab = 'dashboard';
		}

		include SNAP_CCB_CHURCH_CONNECT_PATH . 'includes/Admin/Views/admin-page.php';
	}

	public function handle_sync_now() {
		$this->verify_admin_action('snap_ccb_church_connect_sync_now');
		Logger::info('admin', 'Manual sync requested.');

		$result = (new EventSyncService())->sync();
		$code   = empty($result['success']) ? 'sync_failed' : 'sync_complete';
		$url    = add_query_arg(
			array(
				'page'                 => 'snap-ccb-church-connect',
				'tab'                  => isset($_POST['tab']) ? sanitize_key(wp_unslash($_POST['tab'])) : 'dashboard',
				'snap_ccb_notice'      => $code,
			),
			admin_url('admin.php')
		);

		wp_safe_redirect($url);
		exit;
	}

	public function handle_test_connection() {
		$this->verify_admin_action('snap_ccb_church_connect_test_connection');
		$result = (new CCBClient())->test_connection();

		Helpers::update_options(array('connection_status' => empty($result['success']) ? 'failed' : 'connected'));
		if (empty($result['success'])) {
			Logger::error('admin', 'Connection test failed.', array('message' => isset($result['message']) ? $result['message'] : 'Unknown error'));
		} else {
			Logger::info('admin', 'Connection test succeeded.');
		}

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'            => 'snap-ccb-church-connect',
					'tab'             => 'connection',
					'snap_ccb_notice' => empty($result['success']) ? 'connection_failed' : 'connection_success',
				),
				admin_url('admin.php')
			)
		);
		exit;
	}

	public function handle_clear_logs() {
		$this->verify_admin_action('snap_ccb_church_connect_clear_logs');
		Logger::clear();
		Logger::info('admin', 'Logs cleared.');

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'            => 'snap-ccb-church-connect',
					'tab'             => 'logs',
					'snap_ccb_notice' => 'logs_cleared',
				),
				admin_url('admin.php')
			)
		);
		exit;
	}

	private function verify_admin_action($action) {
		if (! current_user_can('manage_options')) {
			wp_die(esc_html__('You do not have permission to perform this action.', 'snap-ccb-church-connect'));
		}

		check_admin_referer($action);
	}

	private function tabs() {
		return array(
			'dashboard'     => __('Dashboard', 'snap-ccb-church-connect'),
			'connection'    => __('Connection', 'snap-ccb-church-connect'),
			'sync-settings' => __('Sync Settings', 'snap-ccb-church-connect'),
			'display'       => __('Display', 'snap-ccb-church-connect'),
			'logs'          => __('Logs', 'snap-ccb-church-connect'),
			'advanced'      => __('Advanced', 'snap-ccb-church-connect'),
		);
	}
}
