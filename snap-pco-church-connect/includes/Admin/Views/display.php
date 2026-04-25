<?php
if (! defined('ABSPATH')) {
	exit;
}
?>
<h2><?php echo esc_html__('Display', 'snap-pco-church-connect'); ?></h2>
<form method="post" action="options.php">
	<?php settings_fields('snap_pco_church_connect_settings'); ?>
	<table class="form-table" role="presentation">
		<tr><th scope="row"><label for="snap-pco-default-limit"><?php echo esc_html__('Default Events Per Page', 'snap-pco-church-connect'); ?></label></th><td><input id="snap-pco-default-limit" type="number" min="1" max="100" name="<?php echo esc_attr(SNAP_PCO_CHURCH_CONNECT_OPTION); ?>[default_events_per_page]" value="<?php echo esc_attr($options['default_events_per_page']); ?>"></td></tr>
		<tr><th scope="row"><label for="snap-pco-layout"><?php echo esc_html__('Default Shortcode Layout', 'snap-pco-church-connect'); ?></label></th><td><select id="snap-pco-layout" name="<?php echo esc_attr(SNAP_PCO_CHURCH_CONNECT_OPTION); ?>[default_layout]"><option value="cards" <?php selected($options['default_layout'], 'cards'); ?>><?php echo esc_html__('Cards', 'snap-pco-church-connect'); ?></option><option value="list" <?php selected($options['default_layout'], 'list'); ?>><?php echo esc_html__('List', 'snap-pco-church-connect'); ?></option></select></td></tr>
		<tr><th scope="row"><label for="snap-pco-date-format"><?php echo esc_html__('Date Format', 'snap-pco-church-connect'); ?></label></th><td><input id="snap-pco-date-format" class="regular-text" type="text" name="<?php echo esc_attr(SNAP_PCO_CHURCH_CONNECT_OPTION); ?>[date_format]" value="<?php echo esc_attr($options['date_format']); ?>"></td></tr>
		<tr><th scope="row"><label for="snap-pco-time-format"><?php echo esc_html__('Time Format', 'snap-pco-church-connect'); ?></label></th><td><input id="snap-pco-time-format" class="regular-text" type="text" name="<?php echo esc_attr(SNAP_PCO_CHURCH_CONNECT_OPTION); ?>[time_format]" value="<?php echo esc_attr($options['time_format']); ?>"></td></tr>
		<tr><th scope="row"><label for="snap-pco-button-text"><?php echo esc_html__('Button Text', 'snap-pco-church-connect'); ?></label></th><td><input id="snap-pco-button-text" class="regular-text" type="text" name="<?php echo esc_attr(SNAP_PCO_CHURCH_CONNECT_OPTION); ?>[button_text]" value="<?php echo esc_attr($options['button_text']); ?>"></td></tr>
		<tr><th scope="row"><?php echo esc_html__('Shortcode Content', 'snap-pco-church-connect'); ?></th><td><input type="hidden" name="<?php echo esc_attr(SNAP_PCO_CHURCH_CONNECT_OPTION); ?>[show_image]" value="0"><label><input type="checkbox" name="<?php echo esc_attr(SNAP_PCO_CHURCH_CONNECT_OPTION); ?>[show_image]" value="1" <?php checked($options['show_image']); ?>> <?php echo esc_html__('Show image', 'snap-pco-church-connect'); ?></label><br><input type="hidden" name="<?php echo esc_attr(SNAP_PCO_CHURCH_CONNECT_OPTION); ?>[show_location]" value="0"><label><input type="checkbox" name="<?php echo esc_attr(SNAP_PCO_CHURCH_CONNECT_OPTION); ?>[show_location]" value="1" <?php checked($options['show_location']); ?>> <?php echo esc_html__('Show location', 'snap-pco-church-connect'); ?></label></td></tr>
	</table>
	<?php submit_button(__('Save Display Settings', 'snap-pco-church-connect')); ?>
</form>
