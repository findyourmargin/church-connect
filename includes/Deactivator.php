<?php
namespace SnapChurchConnect\PCO;

use SnapChurchConnect\PCO\Sync\Scheduler;

if (! defined('ABSPATH')) {
	exit;
}

class Deactivator {
	public static function deactivate() {
		(new Scheduler())->unschedule();
		flush_rewrite_rules();
	}
}
