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
	<tr>
		<th scope="row"><?php echo esc_html__('Delete Synced Events', 'snap-ccb-church-connect'); ?></th>
		<td>
			<p><strong><?php echo esc_html__('This permanently deletes only CCB-synced church_event posts.', 'snap-ccb-church-connect'); ?></strong></p>
			<p><?php echo esc_html__('Manually created events and events from other providers are not deleted. This cannot be undone except by syncing again from CCB.', 'snap-ccb-church-connect'); ?></p>
			<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
				<?php wp_nonce_field('snap_ccb_church_connect_delete_events'); ?>
				<input type="hidden" name="action" value="snap_ccb_church_connect_delete_events">
				<label for="snap-ccb-confirm-delete"><?php echo esc_html__('Type DELETE to confirm:', 'snap-ccb-church-connect'); ?></label>
				<input id="snap-ccb-confirm-delete" type="text" name="confirm_delete" value="" autocomplete="off">
				<?php submit_button(__('Delete All Synced CCB Events', 'snap-ccb-church-connect'), 'delete', 'submit', false); ?>
			</form>
		</td>
	</tr>
</table>
