<?php
if (! defined('ABSPATH')) {
	exit;
}
?>
<h2><?php echo esc_html__('Advanced', 'snap-ccb-church-connect'); ?></h2>
<table class="form-table" role="presentation">
	<tr><th scope="row"><?php echo esc_html__('Plugin Version', 'snap-ccb-church-connect'); ?></th><td><?php echo esc_html(SNAP_CCB_CHURCH_CONNECT_VERSION); ?></td></tr>
	<tr><th scope="row"><?php echo esc_html__('Settings Status', 'snap-ccb-church-connect'); ?></th><td><?php echo get_option(SNAP_CCB_CHURCH_CONNECT_OPTION) ? esc_html__('Options installed', 'snap-ccb-church-connect') : esc_html__('Options missing', 'snap-ccb-church-connect'); ?></td></tr>
	<tr><th scope="row"><?php echo esc_html__('Normalized CCB API URL', 'snap-ccb-church-connect'); ?></th><td><code><?php echo esc_html(SnapChurchConnect\CCB\Support\Helpers::get_api_base_url() ? SnapChurchConnect\CCB\Support\Helpers::get_api_base_url() : __('Not configured', 'snap-ccb-church-connect')); ?></code></td></tr>
	<tr><th scope="row"><?php echo esc_html__('Required CCB Services', 'snap-ccb-church-connect'); ?></th><td><?php echo esc_html__('api_status, public_calendar_listing, event_profile. Optional future: event_profiles, campus_list.', 'snap-ccb-church-connect'); ?></td></tr>
	<tr><th scope="row"><?php echo esc_html__('Clear Logs', 'snap-ccb-church-connect'); ?></th><td><a class="button" href="<?php echo esc_url(add_query_arg(array('page' => 'snap-ccb-church-connect', 'tab' => 'logs'), admin_url('admin.php'))); ?>"><?php echo esc_html__('Open Logs', 'snap-ccb-church-connect'); ?></a></td></tr>
	<tr><th scope="row"><?php echo esc_html__('Clear Synced Events', 'snap-ccb-church-connect'); ?></th><td><p><?php echo esc_html__('Future feature. This is intentionally not a button in v0.1 to avoid deleting church_event content unexpectedly.', 'snap-ccb-church-connect'); ?></p></td></tr>
</table>
