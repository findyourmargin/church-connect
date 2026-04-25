<?php
namespace SnapChurchConnect\PCO\REST;

if (! defined('ABSPATH')) {
	exit;
}

class EventsController {
	public function register_routes() {
		register_rest_route(
			SNAP_PCO_CHURCH_CONNECT_PUBLIC_REST_NAMESPACE,
			'/events',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array($this, 'get_events'),
				'permission_callback' => '__return_true',
				'args'                => $this->collection_args(),
			)
		);

		register_rest_route(
			SNAP_PCO_CHURCH_CONNECT_PUBLIC_REST_NAMESPACE,
			'/events/(?P<id>\d+)',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array($this, 'get_event'),
				'permission_callback' => '__return_true',
				'args'                => array(
					'id' => array(
						'validate_callback' => 'is_numeric',
						'sanitize_callback' => 'absint',
					),
				),
			)
		);
	}

	public function get_events(\WP_REST_Request $request) {
		$limit = min(100, max(1, absint($request->get_param('limit') ? $request->get_param('limit') : 10)));
		$page  = max(1, absint($request->get_param('page') ? $request->get_param('page') : 1));
		$meta_query = array(
			array(
				'key'     => 'church_event_start_ts',
				'value'   => time(),
				'compare' => '>=',
				'type'    => 'NUMERIC',
			),
		);

		if (null !== $request->get_param('featured')) {
			$meta_query[] = array(
				'key'   => 'church_event_featured',
				'value' => filter_var($request->get_param('featured'), FILTER_VALIDATE_BOOLEAN) ? '1' : '0',
			);
		}

		if ($request->get_param('after')) {
			$meta_query[] = array(
				'key'     => 'church_event_start_ts',
				'value'   => strtotime($request->get_param('after')),
				'compare' => '>=',
				'type'    => 'NUMERIC',
			);
		}

		if ($request->get_param('before')) {
			$meta_query[] = array(
				'key'     => 'church_event_start_ts',
				'value'   => strtotime($request->get_param('before')),
				'compare' => '<=',
				'type'    => 'NUMERIC',
			);
		}

		$args = array(
			'post_type'      => 'church_event',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'paged'          => $page,
			'meta_key'       => 'church_event_start_ts',
			'orderby'        => 'meta_value_num',
			'order'          => 'ASC',
			'meta_query'     => $meta_query,
			'tax_query'      => $this->tax_query($request),
		);

		$query = new \WP_Query($args);
		$data  = array_map(array($this, 'format_post'), $query->posts);

		$response = new \WP_REST_Response($data, 200);
		$response->header('X-WP-Total', (int) $query->found_posts);
		$response->header('X-WP-TotalPages', (int) $query->max_num_pages);

		return $response;
	}

	public function get_event(\WP_REST_Request $request) {
		$post = get_post(absint($request['id']));
		if (! $post || 'church_event' !== $post->post_type || 'publish' !== $post->post_status) {
			return new \WP_Error('church_connect_event_not_found', __('Event not found.', 'snap-pco-church-connect'), array('status' => 404));
		}

		return new \WP_REST_Response($this->format_post($post), 200);
	}

	private function collection_args() {
		return array(
			'limit'    => array('sanitize_callback' => 'absint'),
			'page'     => array('sanitize_callback' => 'absint'),
			'featured' => array('sanitize_callback' => 'sanitize_text_field'),
			'category' => array('sanitize_callback' => 'sanitize_title'),
			'campus'   => array('sanitize_callback' => 'sanitize_title'),
			'ministry' => array('sanitize_callback' => 'sanitize_title'),
			'after'    => array('sanitize_callback' => 'sanitize_text_field'),
			'before'   => array('sanitize_callback' => 'sanitize_text_field'),
		);
	}

	private function tax_query(\WP_REST_Request $request) {
		$tax_query = array();
		$map = array(
			'category' => 'church_event_category',
			'campus'   => 'church_campus',
			'ministry' => 'church_ministry',
		);

		foreach ($map as $param => $taxonomy) {
			if ($request->get_param($param)) {
				$tax_query[] = array(
					'taxonomy' => $taxonomy,
					'field'    => 'slug',
					'terms'    => sanitize_title($request->get_param($param)),
				);
			}
		}

		if (count($tax_query) > 1) {
			$tax_query['relation'] = 'AND';
		}

		return $tax_query;
	}

	private function format_post($post) {
		$post = get_post($post);

		return array(
			'id'               => (int) $post->ID,
			'title'            => get_the_title($post),
			'slug'             => $post->post_name,
			'excerpt'          => get_the_excerpt($post),
			'content'          => apply_filters('the_content', $post->post_content),
			'start'            => get_post_meta($post->ID, 'church_event_start', true),
			'end'              => get_post_meta($post->ID, 'church_event_end', true),
			'start_ts'         => (int) get_post_meta($post->ID, 'church_event_start_ts', true),
			'end_ts'           => (int) get_post_meta($post->ID, 'church_event_end_ts', true),
			'timezone'         => get_post_meta($post->ID, 'church_event_timezone', true),
			'location'         => get_post_meta($post->ID, 'church_event_location', true),
			'address'          => get_post_meta($post->ID, 'church_event_address', true),
			'summary'          => get_post_meta($post->ID, 'church_event_summary', true),
			'description'      => get_post_meta($post->ID, 'church_event_description', true),
			'image_url'        => get_post_meta($post->ID, 'church_event_image_url', true),
			'registration_url' => get_post_meta($post->ID, 'church_event_registration_url', true),
			'external_url'     => get_post_meta($post->ID, 'church_event_external_url', true),
			'featured'         => (bool) get_post_meta($post->ID, 'church_event_featured', true),
			'all_day'          => (bool) get_post_meta($post->ID, 'church_event_all_day', true),
			'repeating'        => (bool) get_post_meta($post->ID, 'church_event_repeating', true),
			'recurrence'       => get_post_meta($post->ID, 'church_event_recurrence', true),
			'status'           => get_post_meta($post->ID, 'church_event_status', true),
			'permalink'        => get_permalink($post),
		);
	}
}
