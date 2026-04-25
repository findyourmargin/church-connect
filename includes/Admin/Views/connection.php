<?php
if (! defined('ABSPATH')) {
	exit;
}

$constant_client = defined('SNAP_PCO_CHURCH_CONNECT_CLIENT_ID') && SNAP_PCO_CHURCH_CONNECT_CLIENT_ID;
$constant_secret = defined('SNAP_PCO_CHURCH_CONNECT_SECRET') && SNAP_PCO_CHURCH_CONNECT_SECRET;
?>
<h2><?php echo esc_html__('Connection', 'snap-pco-church-connect'); ?></h2>
<p><?php echo esc_html__('Planning Center credentials are stored server-side only. They are not exposed to JavaScript, public REST responses, frontend output, or logs.', 'snap-pco-church-connect'); ?></p>
<p><strong><?php echo esc_html__('Status:', 'snap-pco-church-connect'); ?></strong> <?php echo esc_html($options['connection_status']); ?></p>
<form method="post" action="options.php">
	<?php settings_fields('snap_pco_church_connect_settings'); ?>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><label for="snap-pco-client-id"><?php echo esc_html__('Client ID / App ID', 'snap-pco-church-connect'); ?></label></th>
			<td>
				<input id="snap-pco-client-id" class="regular-text" type="text" name="<?php echo esc_attr(SNAP_PCO_CHURCH_CONNECT_OPTION); ?>[client_id]" value="<?php echo esc_attr($constant_client ? '' : $options['client_id']); ?>" <?php disabled($constant_client); ?>>
				<?php if ($constant_client) : ?><p class="description"><?php echo esc_html__('Overridden by SNAP_PCO_CHURCH_CONNECT_CLIENT_ID.', 'snap-pco-church-connect'); ?></p><?php endif; ?>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="snap-pco-secret"><?php echo esc_html__('Secret', 'snap-pco-church-connect'); ?></label></th>
			<td>
				<input id="snap-pco-secret" class="regular-text" type="password" name="<?php echo esc_attr(SNAP_PCO_CHURCH_CONNECT_OPTION); ?>[secret]" value="" autocomplete="new-password" <?php disabled($constant_secret); ?>>
				<p class="description"><?php echo $options['secret'] || $constant_secret ? esc_html__('A secret is saved. Leave blank to keep the existing value.', 'snap-pco-church-connect') : esc_html__('No secret is saved yet.', 'snap-pco-church-connect'); ?></p>
				<?php if ($constant_secret) : ?><p class="description"><?php echo esc_html__('Overridden by SNAP_PCO_CHURCH_CONNECT_SECRET.', 'snap-pco-church-connect'); ?></p><?php endif; ?>
			</td>
		</tr>
	</table>
	<?php submit_button(__('Save Connection Settings', 'snap-pco-church-connect')); ?>
</form>
<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
	<?php wp_nonce_field('snap_pco_church_connect_test_connection'); ?>
	<input type="hidden" name="action" value="snap_pco_church_connect_test_connection">
	<?php submit_button(__('Test Connection', 'snap-pco-church-connect'), 'secondary', 'submit', false); ?>
</form>
<p class="description"><?php echo esc_html__('OAuth is recommended for distributed and multi-church use. Version 0.1 keeps the class structure ready for OAuth but supports Personal Access Token / Basic Auth only.', 'snap-pco-church-connect'); ?></p>
