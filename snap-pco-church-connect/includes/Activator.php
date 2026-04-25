<?php
namespace SnapChurchConnect\PCO;

use SnapChurchConnect\PCO\Content\ContentTypes;
use SnapChurchConnect\PCO\Content\Taxonomies;
use SnapChurchConnect\PCO\Support\Helpers;
use SnapChurchConnect\PCO\Sync\Scheduler;

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
