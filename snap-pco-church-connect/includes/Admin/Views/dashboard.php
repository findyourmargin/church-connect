<?php
if (! defined('ABSPATH')) {
	exit;
}

$event_count = wp_count_posts('church_event');
$published = $event_count && isset($event_count->publish) ? (int) $event_count->publish : 0;
$next_sync = wp_next_scheduled(SNAP_PCO_CHURCH_CONNECT_CRON_HOOK);
?>
<h2><?php echo esc_html__('Dashboard', 'snap-pco-church-connect'); ?></h2>
<div class="snap-pco-grid">
	<div class="snap-pco-card"><strong><?php echo esc_html__('Connection', 'snap-pco-church-connect'); ?></strong><span><?php echo esc_html($options['connection_status']); ?></span></div>
	<div class="snap-pco-card"><strong><?php echo esc_html__('Automatic Sync', 'snap-pco-church-connect'); ?></strong><span><?php echo $options['auto_sync_enabled'] ? esc_html__('Enabled', 'snap-pco-church-connect') : esc_html__('Disabled', 'snap-pco-church-connect'); ?></span></div>
	<div class="snap-pco-card"><strong><?php echo esc_html__('Synced Events', 'snap-pco-church-connect'); ?></strong><span><?php echo esc_html((string) $published); ?></span></div>
	<div class="snap-pco-card"><strong><?php echo esc_html__('Next Sync', 'snap-pco-church-connect'); ?></strong><span><?php echo $next_sync ? esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $next_sync)) : esc_html__('Not scheduled', 'snap-pco-church-connect'); ?></span></div>
</div>
<h3><?php echo esc_html__('Last Sync', 'snap-pco-church-connect'); ?></h3>
<p>
	<?php echo esc_html__('Time:', 'snap-pco-church-connect'); ?> <?php echo esc_html($options['last_sync_time'] ? $options['last_sync_time'] : __('Never', 'snap-pco-church-connect')); ?><br>
	<?php echo esc_html__('Status:', 'snap-pco-church-connect'); ?> <?php echo esc_html($options['last_sync_status'] ? $options['last_sync_status'] : __('Not run', 'snap-pco-church-connect')); ?><br>
	<?php echo esc_html(sprintf('Created: %d | Updated: %d | Skipped: %d | Failed: %d', $options['last_sync_created'], $options['last_sync_updated'], $options['last_sync_skipped'], $options['last_sync_failed'])); ?>
</p>
<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
	<?php wp_nonce_field('snap_pco_church_connect_sync_now'); ?>
	<input type="hidden" name="action" value="snap_pco_church_connect_sync_now">
	<input type="hidden" name="tab" value="dashboard">
	<?php submit_button(__('Sync Now', 'snap-pco-church-connect'), 'primary', 'submit', false); ?>
</form>
<h3><?php echo esc_html__('Shortcode', 'snap-pco-church-connect'); ?></h3>
<code>[church_connect_events limit="6" layout="cards"]</code>
<h3><?php echo esc_html__('REST Endpoints', 'snap-pco-church-connect'); ?></h3>
<p><code>/wp-json/church-connect/v1/events</code></p>
<p><code>/wp-json/church-connect/v1/events?limit=3&amp;featured=true</code></p>
