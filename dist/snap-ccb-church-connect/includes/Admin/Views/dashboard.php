<?php
if (! defined('ABSPATH')) {
	exit;
}

$ccb_events = new WP_Query(array(
	'post_type'      => 'church_event',
	'post_status'    => 'publish',
	'fields'         => 'ids',
	'posts_per_page' => 1,
	'meta_query'     => array(array('key' => '_church_connect_provider', 'value' => 'ccb')),
));
$published = (int) $ccb_events->found_posts;
$next_sync = wp_next_scheduled(SNAP_CCB_CHURCH_CONNECT_CRON_HOOK);
?>
<h2><?php echo esc_html__('Dashboard', 'snap-ccb-church-connect'); ?></h2>
<div class="snap-ccb-grid">
	<div class="snap-ccb-card"><strong><?php echo esc_html__('Connection', 'snap-ccb-church-connect'); ?></strong><span><?php echo esc_html($options['connection_status']); ?></span></div>
	<div class="snap-ccb-card"><strong><?php echo esc_html__('Automatic Sync', 'snap-ccb-church-connect'); ?></strong><span><?php echo $options['auto_sync_enabled'] ? esc_html__('Enabled', 'snap-ccb-church-connect') : esc_html__('Disabled', 'snap-ccb-church-connect'); ?></span></div>
	<div class="snap-ccb-card"><strong><?php echo esc_html__('Synced Events', 'snap-ccb-church-connect'); ?></strong><span><?php echo esc_html((string) $published); ?></span></div>
	<div class="snap-ccb-card"><strong><?php echo esc_html__('Next Sync', 'snap-ccb-church-connect'); ?></strong><span><?php echo $next_sync ? esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $next_sync)) : esc_html__('Not scheduled', 'snap-ccb-church-connect'); ?></span></div>
</div>
<h3><?php echo esc_html__('Last Sync', 'snap-ccb-church-connect'); ?></h3>
<p>
	<?php echo esc_html__('Time:', 'snap-ccb-church-connect'); ?> <?php echo esc_html($options['last_sync_time'] ? $options['last_sync_time'] : __('Never', 'snap-ccb-church-connect')); ?><br>
	<?php echo esc_html__('Status:', 'snap-ccb-church-connect'); ?> <?php echo esc_html($options['last_sync_status'] ? $options['last_sync_status'] : __('Not run', 'snap-ccb-church-connect')); ?><br>
	<?php echo esc_html(sprintf('Created: %d | Updated: %d | Skipped: %d | Failed: %d', $options['last_sync_created'], $options['last_sync_updated'], $options['last_sync_skipped'], $options['last_sync_failed'])); ?>
</p>
<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
	<?php wp_nonce_field('snap_ccb_church_connect_sync_now'); ?>
	<input type="hidden" name="action" value="snap_ccb_church_connect_sync_now">
	<input type="hidden" name="tab" value="dashboard">
	<?php submit_button(__('Sync Now', 'snap-ccb-church-connect'), 'primary', 'submit', false); ?>
</form>
<h3><?php echo esc_html__('Shortcode', 'snap-ccb-church-connect'); ?></h3>
<code>[church_connect_events limit="6" layout="cards"]</code>
<h3><?php echo esc_html__('REST Endpoints', 'snap-ccb-church-connect'); ?></h3>
<p><code>/wp-json/church-connect/v1/events</code></p>
<p><code>/wp-json/church-connect/v1/events?limit=3&amp;featured=true</code></p>
<h3><?php echo esc_html__('Required CCB API Services', 'snap-ccb-church-connect'); ?></h3>
<p><?php echo esc_html__('api_status, public_calendar_listing, and event_profile are required for the v0.1 sync flow. event_profiles and campus_list are optional future services.', 'snap-ccb-church-connect'); ?></p>
