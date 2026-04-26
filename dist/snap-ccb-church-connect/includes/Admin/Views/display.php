<?php
if (! defined('ABSPATH')) {
	exit;
}

$options = wp_parse_args((array) $options, SnapChurchConnect\CCB\Support\Helpers::default_options());
?>
<h2><?php echo esc_html__('Display', 'snap-ccb-church-connect'); ?></h2>
<form method="post" action="options.php">
	<?php settings_fields('snap_ccb_church_connect_settings'); ?>
	<table class="form-table" role="presentation">
		<tr><th scope="row"><label for="snap-ccb-default-limit"><?php echo esc_html__('Default Events Per Page', 'snap-ccb-church-connect'); ?></label></th><td><input id="snap-ccb-default-limit" type="number" min="1" max="100" name="<?php echo esc_attr(SNAP_CCB_CHURCH_CONNECT_OPTION); ?>[default_events_per_page]" value="<?php echo esc_attr($options['default_events_per_page']); ?>"></td></tr>
		<tr><th scope="row"><label for="snap-ccb-layout"><?php echo esc_html__('Default Shortcode Layout', 'snap-ccb-church-connect'); ?></label></th><td><select id="snap-ccb-layout" name="<?php echo esc_attr(SNAP_CCB_CHURCH_CONNECT_OPTION); ?>[default_layout]"><option value="cards" <?php selected($options['default_layout'], 'cards'); ?>><?php echo esc_html__('Cards', 'snap-ccb-church-connect'); ?></option><option value="list" <?php selected($options['default_layout'], 'list'); ?>><?php echo esc_html__('List', 'snap-ccb-church-connect'); ?></option></select></td></tr>
		<tr><th scope="row"><label for="snap-ccb-date-format"><?php echo esc_html__('Date Format', 'snap-ccb-church-connect'); ?></label></th><td><input id="snap-ccb-date-format" class="regular-text" type="text" name="<?php echo esc_attr(SNAP_CCB_CHURCH_CONNECT_OPTION); ?>[date_format]" value="<?php echo esc_attr($options['date_format']); ?>"></td></tr>
		<tr><th scope="row"><label for="snap-ccb-time-format"><?php echo esc_html__('Time Format', 'snap-ccb-church-connect'); ?></label></th><td><input id="snap-ccb-time-format" class="regular-text" type="text" name="<?php echo esc_attr(SNAP_CCB_CHURCH_CONNECT_OPTION); ?>[time_format]" value="<?php echo esc_attr($options['time_format']); ?>"></td></tr>
		<tr><th scope="row"><label for="snap-ccb-button-text"><?php echo esc_html__('Button Text', 'snap-ccb-church-connect'); ?></label></th><td><input id="snap-ccb-button-text" class="regular-text" type="text" name="<?php echo esc_attr(SNAP_CCB_CHURCH_CONNECT_OPTION); ?>[button_text]" value="<?php echo esc_attr($options['button_text']); ?>"></td></tr>
		<tr><th scope="row"><?php echo esc_html__('Shortcode Content', 'snap-ccb-church-connect'); ?></th><td><input type="hidden" name="<?php echo esc_attr(SNAP_CCB_CHURCH_CONNECT_OPTION); ?>[show_image]" value="0"><label><input type="checkbox" name="<?php echo esc_attr(SNAP_CCB_CHURCH_CONNECT_OPTION); ?>[show_image]" value="1" <?php checked($options['show_image']); ?>> <?php echo esc_html__('Show image', 'snap-ccb-church-connect'); ?></label><br><input type="hidden" name="<?php echo esc_attr(SNAP_CCB_CHURCH_CONNECT_OPTION); ?>[require_image]" value="0"><label><input type="checkbox" name="<?php echo esc_attr(SNAP_CCB_CHURCH_CONNECT_OPTION); ?>[require_image]" value="1" <?php checked($options['require_image']); ?>> <?php echo esc_html__('Only show events with an image', 'snap-ccb-church-connect'); ?></label><p class="description"><?php echo esc_html__('This filters shortcode output to events with either a WordPress featured image or a synced CCB image URL. You can override it per shortcode with image_only="true" or image_only="false".', 'snap-ccb-church-connect'); ?></p><input type="hidden" name="<?php echo esc_attr(SNAP_CCB_CHURCH_CONNECT_OPTION); ?>[show_location]" value="0"><label><input type="checkbox" name="<?php echo esc_attr(SNAP_CCB_CHURCH_CONNECT_OPTION); ?>[show_location]" value="1" <?php checked($options['show_location']); ?>> <?php echo esc_html__('Show location', 'snap-ccb-church-connect'); ?></label></td></tr>
	</table>
	<?php submit_button(__('Save Display Settings', 'snap-ccb-church-connect')); ?>
</form>
