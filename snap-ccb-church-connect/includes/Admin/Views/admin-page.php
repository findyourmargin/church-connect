<?php
if (! defined('ABSPATH')) {
	exit;
}

$notice = isset($_GET['snap_ccb_notice']) ? sanitize_key(wp_unslash($_GET['snap_ccb_notice'])) : '';
$messages = array(
	'sync_complete'       => __('Sync completed. Check the dashboard or logs for counts.', 'snap-ccb-church-connect'),
	'sync_failed'         => __('Sync finished with errors. Check the logs for details.', 'snap-ccb-church-connect'),
	'connection_success'  => __('Connection test succeeded.', 'snap-ccb-church-connect'),
	'connection_failed'   => __('Connection test failed. Check credentials and logs.', 'snap-ccb-church-connect'),
	'logs_cleared'        => __('Logs cleared.', 'snap-ccb-church-connect'),
);
?>
<div class="wrap snap-ccb-admin">
	<h1><?php echo esc_html__('Snap! CCB Church Connect', 'snap-ccb-church-connect'); ?></h1>
	<?php if ($notice && isset($messages[$notice])) : ?>
		<div class="notice <?php echo false !== strpos($notice, 'failed') ? 'notice-error' : 'notice-success'; ?> is-dismissible">
			<p><?php echo esc_html($messages[$notice]); ?></p>
		</div>
	<?php endif; ?>
	<nav class="nav-tab-wrapper">
		<?php foreach ($tabs as $tab_key => $label) : ?>
			<a class="nav-tab <?php echo $tab === $tab_key ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url(add_query_arg(array('page' => 'snap-ccb-church-connect', 'tab' => $tab_key), admin_url('admin.php'))); ?>"><?php echo esc_html($label); ?></a>
		<?php endforeach; ?>
	</nav>
	<div class="snap-ccb-admin__panel">
		<?php include SNAP_CCB_CHURCH_CONNECT_PATH . 'includes/Admin/Views/' . $tab . '.php'; ?>
	</div>
</div>
