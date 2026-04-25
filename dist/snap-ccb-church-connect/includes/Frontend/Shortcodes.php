<?php
namespace SnapChurchConnect\CCB\Frontend;

use SnapChurchConnect\CCB\Support\Helpers;

if (! defined('ABSPATH')) {
	exit;
}

class Shortcodes {
	public function register() {
		add_shortcode('church_connect_events', array($this, 'render_events'));
	}

	public function render_events($atts) {
		wp_enqueue_style('snap-ccb-church-connect-frontend');

		$options = Helpers::get_options();
		$atts = shortcode_atts(
			array(
				'limit'    => $options['default_events_per_page'],
				'layout'   => $options['default_layout'],
				'featured' => '',
				'campus'   => '',
				'category' => '',
				'ministry' => '',
			),
			(array) $atts,
			'church_connect_events'
		);

		$layout = in_array($atts['layout'], array('cards', 'list'), true) ? $atts['layout'] : 'cards';
		$args = array(
			'post_type'      => 'church_event',
			'post_status'    => 'publish',
			'posts_per_page' => min(100, max(1, absint($atts['limit']))),
			'meta_key'       => 'church_event_start_ts',
			'orderby'        => 'meta_value_num',
			'order'          => 'ASC',
			'meta_query'     => array(
				array(
					'key'     => '_church_connect_provider',
					'value'   => 'ccb',
					'compare' => '=',
				),
				array(
					'key'     => 'church_event_start_ts',
					'value'   => time(),
					'compare' => '>=',
					'type'    => 'NUMERIC',
				),
			),
			'tax_query'      => $this->tax_query($atts),
		);

		if ('' !== $atts['featured']) {
			$args['meta_query'][] = array(
				'key'   => 'church_event_featured',
				'value' => filter_var($atts['featured'], FILTER_VALIDATE_BOOLEAN) ? '1' : '0',
			);
		}

		$query = new \WP_Query($args);
		if (! $query->have_posts()) {
			return '<div class="church-connect-events church-connect-events--empty">' . esc_html__('No upcoming events found.', 'snap-ccb-church-connect') . '</div>';
		}

		ob_start();
		?>
		<div class="church-connect-events church-connect-events--<?php echo esc_attr($layout); ?>">
			<?php while ($query->have_posts()) : $query->the_post(); ?>
				<?php $this->render_event_card(get_the_ID(), $options); ?>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>
		<?php
		return ob_get_clean();
	}

	private function render_event_card($post_id, array $options) {
		$start_ts = (int) get_post_meta($post_id, 'church_event_start_ts', true);
		$location = get_post_meta($post_id, 'church_event_location', true);
		$image = get_post_meta($post_id, 'church_event_image_url', true);
		$url = get_post_meta($post_id, 'church_event_external_url', true);
		$url = $url ? $url : get_post_meta($post_id, 'church_event_registration_url', true);
		$url = $url ? $url : get_permalink($post_id);
		$date = $start_ts ? date_i18n($options['date_format'] . ' ' . $options['time_format'], $start_ts) : '';
		?>
		<article class="church-connect-event-card">
			<?php if (! empty($options['show_image']) && $image) : ?>
				<img class="church-connect-event-image" src="<?php echo esc_url($image); ?>" alt="">
			<?php endif; ?>
			<h3 class="church-connect-event-title"><a href="<?php echo esc_url(get_permalink($post_id)); ?>"><?php echo esc_html(get_the_title($post_id)); ?></a></h3>
			<?php if ($date) : ?>
				<div class="church-connect-event-date"><?php echo esc_html($date); ?></div>
			<?php endif; ?>
			<?php if (! empty($options['show_location']) && $location) : ?>
				<div class="church-connect-event-location"><?php echo esc_html($location); ?></div>
			<?php endif; ?>
			<a class="church-connect-event-button" href="<?php echo esc_url($url); ?>"><?php echo esc_html($options['button_text']); ?></a>
		</article>
		<?php
	}

	private function tax_query(array $atts) {
		$tax_query = array();
		$map = array(
			'category' => 'church_event_category',
			'campus'   => 'church_campus',
			'ministry' => 'church_ministry',
		);

		foreach ($map as $att => $taxonomy) {
			if (! empty($atts[$att])) {
				$tax_query[] = array(
					'taxonomy' => $taxonomy,
					'field'    => 'slug',
					'terms'    => sanitize_title($atts[$att]),
				);
			}
		}

		if (count($tax_query) > 1) {
			$tax_query['relation'] = 'AND';
		}

		return $tax_query;
	}
}
