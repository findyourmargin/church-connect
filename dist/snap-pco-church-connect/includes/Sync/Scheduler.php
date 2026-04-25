<?php
namespace SnapChurchConnect\PCO\Sync;

use SnapChurchConnect\PCO\Logging\Logger;
use SnapChurchConnect\PCO\Support\Helpers;

if (! defined('ABSPATH')) {
	exit;
}

class Scheduler {
	public function register() {
		add_filter('cron_schedules', array($this, 'add_intervals'));
		add_action(SNAP_PCO_CHURCH_CONNECT_CRON_HOOK, array($this, 'run_cron_sync'));
	}

	public function add_intervals($schedules) {
		$schedules['every_15_minutes'] = array(
			'interval' => 15 * MINUTE_IN_SECONDS,
			'display'  => __('Every 15 minutes', 'snap-pco-church-connect'),
		);
		$schedules['every_30_minutes'] = array(
			'interval' => 30 * MINUTE_IN_SECONDS,
			'display'  => __('Every 30 minutes', 'snap-pco-church-connect'),
		);

		return $schedules;
	}

	public function schedule_if_enabled() {
		if (! Helpers::get_option('auto_sync_enabled', 0)) {
			return;
		}

		if (! wp_next_scheduled(SNAP_PCO_CHURCH_CONNECT_CRON_HOOK)) {
			wp_schedule_event(time() + MINUTE_IN_SECONDS, Helpers::get_option('sync_frequency', 'hourly'), SNAP_PCO_CHURCH_CONNECT_CRON_HOOK);
		}
	}

	public function reschedule() {
		$this->unschedule();
		$this->schedule_if_enabled();
	}

	public function unschedule() {
		$timestamp = wp_next_scheduled(SNAP_PCO_CHURCH_CONNECT_CRON_HOOK);
		while ($timestamp) {
			wp_unschedule_event($timestamp, SNAP_PCO_CHURCH_CONNECT_CRON_HOOK);
			$timestamp = wp_next_scheduled(SNAP_PCO_CHURCH_CONNECT_CRON_HOOK);
		}
	}

	public function run_cron_sync() {
		Logger::info('cron', 'Scheduled event sync started.');
		$result = (new EventSyncService())->sync();
		if (empty($result['success'])) {
			Logger::error('cron', 'Scheduled event sync failed.');
		} else {
			Logger::info('cron', 'Scheduled event sync completed.', $result);
		}
	}
}
