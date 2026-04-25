<?php
if (! defined('ABSPATH')) {
	exit;
}
?>
<h2><?php echo esc_html__('Sync Settings', 'snap-pco-church-connect'); ?></h2>
<form method="post" action="options.php">
	<?php settings_fields('snap_pco_church_connect_settings'); ?>
	<table class="form-table" role="presentation">
		<tr><th scope="row"><?php echo esc_html__('Automatic Sync', 'snap-pco-church-connect'); ?></th><td><input type="hidden" name="<?php echo esc_attr(SNAP_PCO_CHURCH_CONNECT_OPTION); ?>[auto_sync_enabled]" value="0"><label><input type="checkbox" name="<?php echo esc_attr(SNAP_PCO_CHURCH_CONNECT_OPTION); ?>[auto_sync_enabled]" value="1" <?php checked($options['auto_sync_enabled']); ?>> <?php echo esc_html__('Enable WP-Cron event sync', 'snap-pco-church-connect'); ?></label></td></tr>
		<tr><th scope="row"><label for="snap-pco-frequency"><?php echo esc_html__('Sync Frequency', 'snap-pco-church-connect'); ?></label></th><td><select id="snap-pco-frequency" name="<?php echo esc_attr(SNAP_PCO_CHURCH_CONNECT_OPTION); ?>[sync_frequency]">
			<?php foreach (array('every_15_minutes' => 'Every 15 minutes', 'every_30_minutes' => 'Every 30 minutes', 'hourly' => 'Hourly', 'twicedaily' => 'Twice daily', 'daily' => 'Daily') as $value => $label) : ?>
				<option value="<?php echo esc_attr($value); ?>" <?php selected($options['sync_frequency'], $value); ?>><?php echo esc_html($label); ?></option>
			<?php endforeach; ?>
		</select></td></tr>
		<tr><th scope="row"><label for="snap-pco-status"><?php echo esc_html__('Sync Post Status', 'snap-pco-church-connect'); ?></label></th><td><select id="snap-pco-status" name="<?php echo esc_attr(SNAP_PCO_CHURCH_CONNECT_OPTION); ?>[sync_post_status]"><option value="publish" <?php selected($options['sync_post_status'], 'publish'); ?>><?php echo esc_html__('Publish', 'snap-pco-church-connect'); ?></option><option value="draft" <?php selected($options['sync_post_status'], 'draft'); ?>><?php echo esc_html__('Draft', 'snap-pco-church-connect'); ?></option></select></td></tr>
		<tr><th scope="row"><label for="snap-pco-window"><?php echo esc_html__('Sync Window Months Ahead', 'snap-pco-church-connect'); ?></label></th><td><input id="snap-pco-window" type="number" min="1" max="24" name="<?php echo esc_attr(SNAP_PCO_CHURCH_CONNECT_OPTION); ?>[sync_window_months]" value="<?php echo esc_attr($options['sync_window_months']); ?>"></td></tr>
		<tr><th scope="row"><label for="snap-pco-expired"><?php echo esc_html__('Expired Event Handling', 'snap-pco-church-connect'); ?></label></th><td><select id="snap-pco-expired" name="<?php echo esc_attr(SNAP_PCO_CHURCH_CONNECT_OPTION); ?>[expired_event_handling]"><option value="keep" <?php selected($options['expired_event_handling'], 'keep'); ?>><?php echo esc_html__('Keep', 'snap-pco-church-connect'); ?></option><option value="draft" <?php selected($options['expired_event_handling'], 'draft'); ?>><?php echo esc_html__('Draft', 'snap-pco-church-connect'); ?></option><option value="trash" <?php selected($options['expired_event_handling'], 'trash'); ?>><?php echo esc_html__('Trash', 'snap-pco-church-connect'); ?></option></select><p class="description"><?php echo esc_html__('Stored for the next implementation pass; v0.1 does not process expired events automatically.', 'snap-pco-church-connect'); ?></p></td></tr>
	</table>
	<?php submit_button(__('Save Sync Settings', 'snap-pco-church-connect')); ?>
</form>
<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
	<?php wp_nonce_field('snap_pco_church_connect_sync_now'); ?>
	<input type="hidden" name="action" value="snap_pco_church_connect_sync_now">
	<input type="hidden" name="tab" value="sync-settings">
	<?php submit_button(__('Sync Now', 'snap-pco-church-connect'), 'secondary', 'submit', false); ?>
</form>
