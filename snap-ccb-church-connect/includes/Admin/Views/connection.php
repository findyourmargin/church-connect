<?php
if (! defined('ABSPATH')) {
	exit;
}

$constant_account  = defined('SNAP_CCB_CHURCH_CONNECT_ACCOUNT') && SNAP_CCB_CHURCH_CONNECT_ACCOUNT;
$constant_user     = defined('SNAP_CCB_CHURCH_CONNECT_USERNAME') && SNAP_CCB_CHURCH_CONNECT_USERNAME;
$constant_password = defined('SNAP_CCB_CHURCH_CONNECT_PASSWORD') && SNAP_CCB_CHURCH_CONNECT_PASSWORD;
?>
<h2><?php echo esc_html__('Connection', 'snap-ccb-church-connect'); ?></h2>
<p><?php echo esc_html__('CCB credentials are stored server-side only. They are never exposed to JavaScript, public REST responses, frontend output, or logs.', 'snap-ccb-church-connect'); ?></p>
<p><strong><?php echo esc_html__('Status:', 'snap-ccb-church-connect'); ?></strong> <?php echo esc_html($options['connection_status']); ?></p>
<form method="post" action="options.php">
	<?php settings_fields('snap_ccb_church_connect_settings'); ?>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><label for="snap-ccb-account"><?php echo esc_html__('CCB Account / Subdomain', 'snap-ccb-church-connect'); ?></label></th>
			<td><input id="snap-ccb-account" class="regular-text" type="text" name="<?php echo esc_attr(SNAP_CCB_CHURCH_CONNECT_OPTION); ?>[account]" value="<?php echo esc_attr($constant_account ? '' : $options['account']); ?>" <?php disabled($constant_account); ?>><p class="description"><?php echo esc_html__('Examples: yourchurch, yourchurch.ccbchurch.com, or https://yourchurch.ccbchurch.com.', 'snap-ccb-church-connect'); ?></p></td>
		</tr>
		<tr>
			<th scope="row"><label for="snap-ccb-username"><?php echo esc_html__('API Username', 'snap-ccb-church-connect'); ?></label></th>
			<td><input id="snap-ccb-username" class="regular-text" type="text" name="<?php echo esc_attr(SNAP_CCB_CHURCH_CONNECT_OPTION); ?>[username]" value="<?php echo esc_attr($constant_user ? '' : $options['username']); ?>" <?php disabled($constant_user); ?>></td>
		</tr>
		<tr>
			<th scope="row"><label for="snap-ccb-password"><?php echo esc_html__('API Password', 'snap-ccb-church-connect'); ?></label></th>
			<td><input id="snap-ccb-password" class="regular-text" type="password" name="<?php echo esc_attr(SNAP_CCB_CHURCH_CONNECT_OPTION); ?>[password]" value="" autocomplete="new-password" <?php disabled($constant_password); ?>><p class="description"><?php echo $options['password'] || $constant_password ? esc_html__('A password is saved. Leave blank to keep the existing value.', 'snap-ccb-church-connect') : esc_html__('No password is saved yet.', 'snap-ccb-church-connect'); ?></p></td>
		</tr>
		<tr>
			<th scope="row"><label for="snap-ccb-api-base-url"><?php echo esc_html__('Advanced API Base URL Override', 'snap-ccb-church-connect'); ?></label></th>
			<td><input id="snap-ccb-api-base-url" class="regular-text" type="url" name="<?php echo esc_attr(SNAP_CCB_CHURCH_CONNECT_OPTION); ?>[api_base_url]" value="<?php echo esc_attr($options['api_base_url']); ?>" placeholder="https://yourchurch.ccbchurch.com/api.php"><p class="description"><?php echo esc_html__('Optional. Must be HTTPS. Leave blank for the normalized account URL.', 'snap-ccb-church-connect'); ?></p></td>
		</tr>
	</table>
	<?php submit_button(__('Save Connection Settings', 'snap-ccb-church-connect')); ?>
</form>
<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
	<?php wp_nonce_field('snap_ccb_church_connect_test_connection'); ?>
	<input type="hidden" name="action" value="snap_ccb_church_connect_test_connection">
	<?php submit_button(__('Test Connection', 'snap-ccb-church-connect'), 'secondary', 'submit', false); ?>
</form>
<p class="description"><?php echo esc_html__('The API user needs access to api_status, public_calendar_listing, and event_profile. event_profiles and campus_list are optional future services.', 'snap-ccb-church-connect'); ?></p>
