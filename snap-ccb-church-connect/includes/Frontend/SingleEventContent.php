<?php
namespace SnapChurchConnect\CCB\Frontend;

use SnapChurchConnect\CCB\Support\Helpers;

if (! defined('ABSPATH')) {
	exit;
}

class SingleEventContent {
	public function register() {
		add_filter('the_content', array($this, 'append_event_details'));
	}

	public function append_event_details($content) {
		if (! is_singular('church_event') || ! in_the_loop() || ! is_main_query()) {
			return $content;
		}

		$post_id = get_the_ID();
		if ('ccb' !== get_post_meta($post_id, '_church_connect_provider', true)) {
			return $content;
		}

		wp_enqueue_style('snap-ccb-church-connect-frontend');

		$details = $this->render_details($post_id);
		if (! $details) {
			return $content;
		}

		return $details . $content;
	}

	private function render_details($post_id) {
		$options = Helpers::get_options();
		$start_ts = (int) get_post_meta($post_id, 'church_event_start_ts', true);
		$end_ts = (int) get_post_meta($post_id, 'church_event_end_ts', true);
		$image = has_post_thumbnail($post_id) ? get_the_post_thumbnail_url($post_id, 'large') : get_post_meta($post_id, 'church_event_image_url', true);
		$description = get_post_meta($post_id, 'church_event_description', true);
		$summary = get_post_meta($post_id, 'church_event_summary', true);
		$location = get_post_meta($post_id, 'church_event_location', true);
		$address = get_post_meta($post_id, 'church_event_address', true);
		$status = get_post_meta($post_id, 'church_event_status', true);
		$recurrence = get_post_meta($post_id, 'church_event_recurrence', true);
		$registration_url = get_post_meta($post_id, 'church_event_registration_url', true);
		$external_url = get_post_meta($post_id, 'church_event_external_url', true);
		$url = $external_url ? $external_url : $registration_url;
		$date = $this->format_date_range($start_ts, $end_ts, $options);
		$terms = $this->get_terms($post_id);

		ob_start();
		?>
		<section class="church-connect-single-event" aria-label="<?php echo esc_attr__('Event details', 'snap-ccb-church-connect'); ?>">
			<?php if ($image) : ?>
				<img class="church-connect-single-event__image" src="<?php echo esc_url($image); ?>" alt="">
			<?php endif; ?>

			<div class="church-connect-single-event__details">
				<?php if ($date) : ?>
					<div class="church-connect-single-event__row">
						<strong><?php echo esc_html__('Date', 'snap-ccb-church-connect'); ?></strong>
						<span><?php echo esc_html($date); ?></span>
					</div>
				<?php endif; ?>

				<?php if ($location) : ?>
					<div class="church-connect-single-event__row">
						<strong><?php echo esc_html__('Location', 'snap-ccb-church-connect'); ?></strong>
						<span><?php echo esc_html($location); ?></span>
					</div>
				<?php endif; ?>

				<?php if ($address) : ?>
					<div class="church-connect-single-event__row">
						<strong><?php echo esc_html__('Address', 'snap-ccb-church-connect'); ?></strong>
						<span><?php echo esc_html($address); ?></span>
					</div>
				<?php endif; ?>

				<?php if ($status) : ?>
					<div class="church-connect-single-event__row">
						<strong><?php echo esc_html__('Status', 'snap-ccb-church-connect'); ?></strong>
						<span><?php echo esc_html($status); ?></span>
					</div>
				<?php endif; ?>

				<?php if ($recurrence) : ?>
					<div class="church-connect-single-event__row">
						<strong><?php echo esc_html__('Repeats', 'snap-ccb-church-connect'); ?></strong>
						<span><?php echo esc_html($recurrence); ?></span>
					</div>
				<?php endif; ?>

				<?php foreach ($terms as $label => $values) : ?>
					<?php if (! empty($values)) : ?>
						<div class="church-connect-single-event__row">
							<strong><?php echo esc_html($label); ?></strong>
							<span><?php echo esc_html(implode(', ', $values)); ?></span>
						</div>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>

			<?php if ($summary || $description) : ?>
				<div class="church-connect-single-event__description">
					<?php echo wp_kses_post(wpautop($description ? $description : $summary)); ?>
				</div>
			<?php endif; ?>

			<?php if ($url) : ?>
				<p><a class="church-connect-event-button" href="<?php echo esc_url($url); ?>"><?php echo esc_html($options['button_text']); ?></a></p>
			<?php endif; ?>
		</section>
		<?php
		return trim(ob_get_clean());
	}

	private function format_date_range($start_ts, $end_ts, array $options) {
		if (! $start_ts) {
			return '';
		}

		$date_format = isset($options['date_format']) ? $options['date_format'] : get_option('date_format');
		$time_format = isset($options['time_format']) ? $options['time_format'] : get_option('time_format');
		$start_date = date_i18n($date_format, $start_ts);
		$start_time = date_i18n($time_format, $start_ts);

		if (! $end_ts || $end_ts <= $start_ts) {
			return $start_date . ' ' . $start_time;
		}

		$end_date = date_i18n($date_format, $end_ts);
		$end_time = date_i18n($time_format, $end_ts);
		if ($start_date === $end_date) {
			return $start_date . ' ' . $start_time . ' - ' . $end_time;
		}

		return $start_date . ' ' . $start_time . ' - ' . $end_date . ' ' . $end_time;
	}

	private function get_terms($post_id) {
		return array(
			__('Campus', 'snap-ccb-church-connect') => wp_get_post_terms($post_id, 'church_campus', array('fields' => 'names')),
			__('Ministry', 'snap-ccb-church-connect') => wp_get_post_terms($post_id, 'church_ministry', array('fields' => 'names')),
			__('Category', 'snap-ccb-church-connect') => wp_get_post_terms($post_id, 'church_event_category', array('fields' => 'names')),
		);
	}
}
