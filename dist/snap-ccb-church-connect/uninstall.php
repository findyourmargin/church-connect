<?php
/**
 * Uninstall handler for Snap! CCB Church Connect.
 *
 * Conservative by design: v0.1 does not delete synced events or options unless a
 * future setting explicitly enables destructive cleanup.
 *
 * @package SnapChurchConnect\CCB
 */

if (! defined('WP_UNINSTALL_PLUGIN')) {
	exit;
}

$options = get_option('snap_ccb_church_connect_options', array());

if (is_array($options) && ! empty($options['delete_data_on_uninstall'])) {
	delete_option('snap_ccb_church_connect_options');
	delete_option('snap_ccb_church_connect_logs');
}
