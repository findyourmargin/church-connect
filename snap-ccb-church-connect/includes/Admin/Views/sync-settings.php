<?php
if (! defined('ABSPATH')) {
	exit;
}
?>
<h2><?php echo esc_html__('Sync Settings', 'snap-ccb-church-connect'); ?></h2>
<form method="post" action="options.php">
	<?php settings_fields('snap_ccb_church_connect_settings'); ?>
	<table class="form-table" role="presentation">
		<tr><th scope="row"><?php echo esc_html__('Automatic Sync', 'snap-ccb-church-connect'); ?></th><td><input type="hidden" name="<?php echo esc_attr(SNAP_CCB_CHURCH_CONNECT_OPTION); ?>[auto_sync_enabled]" value="0"><label><input type="checkbox" name="<?php echo esc_attr(SNAP_CCB_CHURCH_CONNECT_OPTION); ?>[auto_sync_enabled]" value="1" <?php checked($options['auto_sync_enabled']); ?>> <?php echo esc_html__('Enable WP-Cron event sync', 'snap-ccb-church-connect'); ?></label></td></tr>
		<tr><th scope="row"><label for="snap-ccb-frequency"><?php echo esc_html__('Sync Frequency', 'snap-ccb-church-connect'); ?></label></th><td><select id="snap-ccb-frequency" name="<?php echo esc_attr(SNAP_CCB_CHURCH_CONNECT_OPTION); ?>[sync_frequency]">
			<?php foreach (array('every_15_minutes' => 'Every 15 minutes', 'every_30_minutes' => 'Every 30 minutes', 'hourly' => 'Hourly', 'twicedaily' => 'Twice daily', 'daily' => 'Daily') as $value => $label) : ?>
				<option value="<?php echo esc_attr($value); ?>" <?php selected($options['sync_frequency'], $value); ?>><?php echo esc_html($label); ?></option>
			<?php endforeach; ?>
		</select></td></tr>
		<tr><th scope="row"><label for="snap-ccb-status"><?php echo esc_html__('Sync Post Status', 'snap-ccb-church-connect'); ?></label></th><td><select id="snap-ccb-status" name="<?php echo esc_attr(SNAP_CCB_CHURCH_CONNECT_OPTION); ?>[sync_post_status]"><option value="publish" <?php selected($options['sync_post_status'], 'publish'); ?>><?php echo esc_html__('Publish', 'snap-ccb-church-connect'); ?></option><option value="draft" <?php selected($options['sync_post_status'], 'draft'); ?>><?php echo esc_html__('Draft', 'snap-ccb-church-connect'); ?></option></select></td></tr>
		<tr><th scope="row"><label for="snap-ccb-window"><?php echo esc_html__('Sync Window Months Ahead', 'snap-ccb-church-connect'); ?></label></th><td><input id="snap-ccb-window" type="number" min="1" max="24" name="<?php echo esc_attr(SNAP_CCB_CHURCH_CONNECT_OPTION); ?>[sync_window_months]" value="<?php echo esc_attr($options['sync_window_months']); ?>"></td></tr>
		<tr><th scope="row"><?php echo esc_html__('Event Profile Details', 'snap-ccb-church-connect'); ?></th><td><input type="hidden" name="<?php echo esc_attr(SNAP_CCB_CHURCH_CONNECT_OPTION); ?>[fetch_event_profiles]" value="0"><label><input type="checkbox" name="<?php echo esc_attr(SNAP_CCB_CHURCH_CONNECT_OPTION); ?>[fetch_event_profiles]" value="1" <?php checked($options['fetch_event_profiles']); ?>> <?php echo esc_html__('Fetch event_profile details during sync', 'snap-ccb-church-connect'); ?></label></td></tr>
		<tr><th scope="row"><?php echo esc_html__('Multi-day Events', 'snap-ccb-church-connect'); ?></th><td><input type="hidden" name="<?php echo esc_attr(SNAP_CCB_CHURCH_CONNECT_OPTION); ?>[merge_multiday_occurrences]" value="0"><label><input type="checkbox" name="<?php echo esc_attr(SNAP_CCB_CHURCH_CONNECT_OPTION); ?>[merge_multiday_occurrences]" value="1" <?php checked($options['merge_multiday_occurrences']); ?>> <?php echo esc_html__('Merge consecutive CCB occurrences into one event', 'snap-ccb-church-connect'); ?></label><p class="description"><?php echo esc_html__('When CCB returns one occurrence per day for the same event, same time, and same location, sync will keep the first post, extend its end date, and draft the extra duplicate occurrence posts.', 'snap-ccb-church-connect'); ?></p></td></tr>
		<tr><th scope="row"><label for="snap-ccb-expired"><?php echo esc_html__('Expired Event Handling', 'snap-ccb-church-connect'); ?></label></th><td><select id="snap-ccb-expired" name="<?php echo esc_attr(SNAP_CCB_CHURCH_CONNECT_OPTION); ?>[expired_event_handling]"><option value="keep" <?php selected($options['expired_event_handling'], 'keep'); ?>><?php echo esc_html__('Keep', 'snap-ccb-church-connect'); ?></option><option value="draft" <?php selected($options['expired_event_handling'], 'draft'); ?>><?php echo esc_html__('Draft', 'snap-ccb-church-connect'); ?></option><option value="trash" <?php selected($options['expired_event_handling'], 'trash'); ?>><?php echo esc_html__('Trash', 'snap-ccb-church-connect'); ?></option></select><p class="description"><?php echo esc_html__('Stored for the next implementation pass; v0.1 does not process expired events automatically.', 'snap-ccb-church-connect'); ?></p></td></tr>
	</table>
	<?php submit_button(__('Save Sync Settings', 'snap-ccb-church-connect')); ?>
</form>
<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
	<?php wp_nonce_field('snap_ccb_church_connect_sync_now'); ?>
	<input type="hidden" name="action" value="snap_ccb_church_connect_sync_now">
	<input type="hidden" name="tab" value="sync-settings">
	<?php submit_button(__('Sync Now', 'snap-ccb-church-connect'), 'secondary', 'submit', false); ?>
</form>
