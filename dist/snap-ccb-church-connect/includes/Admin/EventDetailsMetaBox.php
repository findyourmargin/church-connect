<?php
namespace SnapChurchConnect\CCB\Admin;

if (! defined('ABSPATH')) {
	exit;
}

class EventDetailsMetaBox {
	public function register() {
		add_meta_box(
			'snap-ccb-church-connect-event-details',
			__('Church Event Details', 'snap-ccb-church-connect'),
			array($this, 'render'),
			'church_event',
			'normal',
			'high'
		);
	}

	public function render($post) {
		$fields = array(
			__('Start', 'snap-ccb-church-connect')       => get_post_meta($post->ID, 'church_event_start', true),
			__('End', 'snap-ccb-church-connect')         => get_post_meta($post->ID, 'church_event_end', true),
			__('Timezone', 'snap-ccb-church-connect')    => get_post_meta($post->ID, 'church_event_timezone', true),
			__('Location', 'snap-ccb-church-connect')    => get_post_meta($post->ID, 'church_event_location', true),
			__('Status', 'snap-ccb-church-connect')      => get_post_meta($post->ID, 'church_event_status', true),
			__('Registration URL', 'snap-ccb-church-connect') => get_post_meta($post->ID, 'church_event_registration_url', true),
			__('External URL', 'snap-ccb-church-connect') => get_post_meta($post->ID, 'church_event_external_url', true),
			__('Image URL', 'snap-ccb-church-connect')   => get_post_meta($post->ID, 'church_event_image_url', true),
		);

		$provider = get_post_meta($post->ID, '_church_connect_provider', true);
		$external_id = get_post_meta($post->ID, '_church_connect_external_id', true);
		$instance_id = get_post_meta($post->ID, '_church_connect_external_instance_id', true);
		$last_synced = get_post_meta($post->ID, '_church_connect_last_synced_at', true);
		?>
		<table class="widefat striped snap-ccb-event-details">
			<tbody>
				<?php foreach ($fields as $label => $value) : ?>
					<tr>
						<th scope="row"><?php echo esc_html($label); ?></th>
						<td>
							<?php if ($this->is_url($value)) : ?>
								<a href="<?php echo esc_url($value); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($value); ?></a>
							<?php else : ?>
								<?php echo $value ? esc_html($value) : '&mdash;'; ?>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				<tr>
					<th scope="row"><?php echo esc_html__('Provider', 'snap-ccb-church-connect'); ?></th>
					<td><?php echo esc_html($provider ? $provider : __('Not synced', 'snap-ccb-church-connect')); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html__('External Event ID', 'snap-ccb-church-connect'); ?></th>
					<td><?php echo esc_html($external_id ? $external_id : ''); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html__('Occurrence Key', 'snap-ccb-church-connect'); ?></th>
					<td><code><?php echo esc_html($instance_id ? $instance_id : ''); ?></code></td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html__('Last Synced', 'snap-ccb-church-connect'); ?></th>
					<td><?php echo esc_html($last_synced ? $last_synced : ''); ?></td>
				</tr>
			</tbody>
		</table>
		<p class="description"><?php echo esc_html__('These fields are synced from CCB and exposed through the shared Church Connect schema for builders, shortcodes, and REST endpoints.', 'snap-ccb-church-connect'); ?></p>
		<?php
	}

	private function is_url($value) {
		return is_string($value) && filter_var($value, FILTER_VALIDATE_URL);
	}
}
