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

		$admin_page = SNAP_CCB_CHURCH_CONNECT_PATH . 'includes/Admin/Views/admin-page.php';
		if (! is_readable($admin_page)) {
			wp_die(esc_html__('Snap! CCB Church Connect admin view files are missing.', 'snap-ccb-church-connect'));
		}

		include $admin_page;
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

	public function handle_refresh_calendars() {
		$this->verify_admin_action('snap_ccb_church_connect_refresh_calendars');

		$start = current_time('Y-m-d');
		$end = gmdate('Y-m-d', strtotime('+' . max(1, absint(Helpers::get_option('sync_window_months', 6))) . ' months'));
		$response = (new CCBClient())->get_public_calendar_listing($start, $end);
		$notice = 'calendars_failed';

		if (! empty($response['success'])) {
			$items = array();
			$this->collect_calendar_items($response['data'], $items);
			$calendars = $this->calendar_options_from_items($items);
			Helpers::update_options(array('available_calendars' => $calendars));
			Logger::info('admin', 'Available CCB calendars refreshed.', array('count' => count($calendars)));
			$notice = 'calendars_refreshed';
		} else {
			Logger::error('admin', 'Failed to refresh CCB calendars.', array('message' => isset($response['message']) ? $response['message'] : 'Unknown error'));
		}

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'            => 'snap-ccb-church-connect',
					'tab'             => 'sync-settings',
					'snap_ccb_notice' => $notice,
				),
				admin_url('admin.php')
			)
		);
		exit;
	}

	public function handle_delete_events() {
		$this->verify_admin_action('snap_ccb_church_connect_delete_events');

		$confirm = isset($_POST['confirm_delete']) ? sanitize_text_field(wp_unslash($_POST['confirm_delete'])) : '';
		if ('DELETE' !== $confirm) {
			Logger::warning('admin', 'Delete synced CCB events confirmation failed.');
			wp_safe_redirect(
				add_query_arg(
					array(
						'page'            => 'snap-ccb-church-connect',
						'tab'             => 'advanced',
						'snap_ccb_notice' => 'delete_confirm_failed',
					),
					admin_url('admin.php')
				)
			);
			exit;
		}

		$deleted = 0;
		do {
			$query = new \WP_Query(array(
				'post_type'      => 'church_event',
				'post_status'    => 'any',
				'fields'         => 'ids',
				'posts_per_page' => 100,
				'no_found_rows'  => true,
				'meta_query'     => array(
					array(
						'key'   => '_church_connect_provider',
						'value' => 'ccb',
					),
				),
			));

			foreach ($query->posts as $post_id) {
				if (wp_delete_post((int) $post_id, true)) {
					$deleted++;
				}
			}
		} while (! empty($query->posts));

		Helpers::update_options(array(
			'last_sync_time'    => '',
			'last_sync_status'  => 'events_deleted',
			'last_sync_created' => 0,
			'last_sync_updated' => 0,
			'last_sync_skipped' => 0,
			'last_sync_failed'  => 0,
		));

		Logger::warning('admin', 'Deleted synced CCB events from WordPress.', array('deleted' => $deleted));

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'            => 'snap-ccb-church-connect',
					'tab'             => 'advanced',
					'snap_ccb_notice' => 'events_deleted',
					'deleted'         => $deleted,
				),
				admin_url('admin.php')
			)
		);
		exit;
	}

	private function collect_calendar_items($value, array &$items) {
		if (! is_array($value)) {
			return;
		}

		if (isset($value['event_name']) || isset($value['date'])) {
			$items[] = $value;
		}

		foreach ($value as $key => $child) {
			if ('@attributes' === $key || '@text' === $key) {
				continue;
			}
			$this->collect_calendar_items($child, $items);
		}
	}

	private function calendar_options_from_items(array $items) {
		$calendars = array();
		foreach ($items as $item) {
			$label = $this->calendar_label_for_item($item);
			if (! $label) {
				continue;
			}

			$key = sanitize_key($label);
			if ($key) {
				$calendars[$key] = $label;
			}
		}

		asort($calendars, SORT_NATURAL | SORT_FLAG_CASE);
		return $calendars;
	}

	private function calendar_label_for_item(array $item) {
		foreach (array('grouping_name', 'group_type', 'event_type', 'group_name') as $field) {
			$value = $this->calendar_text(isset($item[$field]) ? $item[$field] : '');
			if ($value) {
				return $value;
			}
		}
		return '';
	}

	private function calendar_text($value) {
		if (is_array($value)) {
			if (isset($value['@text'])) {
				return sanitize_text_field((string) $value['@text']);
			}
			return '';
		}
		return sanitize_text_field((string) $value);
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
