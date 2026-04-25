<?php
if (! defined('ABSPATH')) {
	exit;
}
?>
<h2><?php echo esc_html__('Advanced', 'snap-pco-church-connect'); ?></h2>
<table class="form-table" role="presentation">
	<tr><th scope="row"><?php echo esc_html__('Plugin Version', 'snap-pco-church-connect'); ?></th><td><?php echo esc_html(SNAP_PCO_CHURCH_CONNECT_VERSION); ?></td></tr>
	<tr><th scope="row"><?php echo esc_html__('Settings Status', 'snap-pco-church-connect'); ?></th><td><?php echo get_option(SNAP_PCO_CHURCH_CONNECT_OPTION) ? esc_html__('Options installed', 'snap-pco-church-connect') : esc_html__('Options missing', 'snap-pco-church-connect'); ?></td></tr>
	<tr><th scope="row"><?php echo esc_html__('Clear Logs', 'snap-pco-church-connect'); ?></th><td><a class="button" href="<?php echo esc_url(add_query_arg(array('page' => 'snap-pco-church-connect', 'tab' => 'logs'), admin_url('admin.php'))); ?>"><?php echo esc_html__('Open Logs', 'snap-pco-church-connect'); ?></a></td></tr>
	<tr><th scope="row"><?php echo esc_html__('Clear Synced Events', 'snap-pco-church-connect'); ?></th><td><p><?php echo esc_html__('Future feature. This is intentionally not a button in v0.1 to avoid deleting church_event content unexpectedly.', 'snap-pco-church-connect'); ?></p></td></tr>
</table>
