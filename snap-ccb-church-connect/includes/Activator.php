<?php
namespace SnapChurchConnect\CCB;

use SnapChurchConnect\CCB\Content\ContentTypes;
use SnapChurchConnect\CCB\Content\Taxonomies;
use SnapChurchConnect\CCB\Support\Helpers;
use SnapChurchConnect\CCB\Sync\Scheduler;

if (! defined('ABSPATH')) {
	exit;
}

class Activator {
	public static function activate() {
		Helpers::ensure_default_options();

		(new ContentTypes())->register();
		(new Taxonomies())->register();
		flush_rewrite_rules();

		$scheduler = new Scheduler();
		add_filter('cron_schedules', array($scheduler, 'add_intervals'));
		$scheduler->schedule_if_enabled();
	}
}
