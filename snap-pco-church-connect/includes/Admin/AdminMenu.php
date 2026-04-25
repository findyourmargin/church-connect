<?php
namespace SnapChurchConnect\PCO\Admin;

use SnapChurchConnect\PCO\API\PlanningCenterClient;
use SnapChurchConnect\PCO\Logging\Logger;
use SnapChurchConnect\PCO\Support\Helpers;
use SnapChurchConnect\PCO\Sync\EventSyncService;

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
			__('Snap! PCO Church Connect', 'snap-pco-church-connect'),
			__('Snap! PCO Connect', 'snap-pco-church-connect'),
			'manage_options',
			'snap-pco-church-connect',
			array($this, 'render'),
			'dashicons-admin-site-alt3',
			58
		);
	}

	public function render() {
		if (! current_user_can('manage_options')) {
			wp_die(esc_html__('You do not have permission to access this page.', 'snap-pco-church-connect'));
		}

		$tab     = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'dashboard';
		$tabs    = $this->tabs();
		$options = Helpers::get_options();

		if (! isset($tabs[$tab])) {
			$tab = 'dashboard';
		}

		include SNAP_PCO_CHURCH_CONNECT_PATH . 'includes/Admin/Views/admin-page.php';
	}

	public function handle_sync_now() {
		$this->verify_admin_action('snap_pco_church_connect_sync_now');
		Logger::info('admin', 'Manual sync requested.');

		$result = (new EventSyncService())->sync();
		$code   = empty($result['success']) ? 'sync_failed' : 'sync_complete';
		$url    = add_query_arg(
			array(
				'page'                 => 'snap-pco-church-connect',
				'tab'                  => isset($_POST['tab']) ? sanitize_key(wp_unslash($_POST['tab'])) : 'dashboard',
				'snap_pco_notice'      => $code,
			),
			admin_url('admin.php')
		);

		wp_safe_redirect($url);
		exit;
	}

	public function handle_test_connection() {
		$this->verify_admin_action('snap_pco_church_connect_test_connection');
		$result = (new PlanningCenterClient())->test_connection();

		Helpers::update_options(array('connection_status' => empty($result['success']) ? 'failed' : 'connected'));
		if (empty($result['success'])) {
			Logger::error('admin', 'Connection test failed.', array('message' => isset($result['message']) ? $result['message'] : 'Unknown error'));
		} else {
			Logger::info('admin', 'Connection test succeeded.');
		}

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'            => 'snap-pco-church-connect',
					'tab'             => 'connection',
					'snap_pco_notice' => empty($result['success']) ? 'connection_failed' : 'connection_success',
				),
				admin_url('admin.php')
			)
		);
		exit;
	}

	public function handle_clear_logs() {
		$this->verify_admin_action('snap_pco_church_connect_clear_logs');
		Logger::clear();
		Logger::info('admin', 'Logs cleared.');

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'            => 'snap-pco-church-connect',
					'tab'             => 'logs',
					'snap_pco_notice' => 'logs_cleared',
				),
				admin_url('admin.php')
			)
		);
		exit;
	}

	private function verify_admin_action($action) {
		if (! current_user_can('manage_options')) {
			wp_die(esc_html__('You do not have permission to perform this action.', 'snap-pco-church-connect'));
		}

		check_admin_referer($action);
	}

	private function tabs() {
		return array(
			'dashboard'     => __('Dashboard', 'snap-pco-church-connect'),
			'connection'    => __('Connection', 'snap-pco-church-connect'),
			'sync-settings' => __('Sync Settings', 'snap-pco-church-connect'),
			'display'       => __('Display', 'snap-pco-church-connect'),
			'logs'          => __('Logs', 'snap-pco-church-connect'),
			'advanced'      => __('Advanced', 'snap-pco-church-connect'),
		);
	}
}
