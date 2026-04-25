<?php
namespace SnapChurchConnect\CCB\Content;

use SnapChurchConnect\CCB\Logging\Logger;

if (! defined('ABSPATH')) {
	exit;
}

class Taxonomies {
	public function register() {
		$this->register_taxonomy('church_campus', __('Campuses', 'snap-ccb-church-connect'), __('Campus', 'snap-ccb-church-connect'), 'campus');
		$this->register_taxonomy('church_ministry', __('Ministries', 'snap-ccb-church-connect'), __('Ministry', 'snap-ccb-church-connect'), 'ministry');
		$this->register_taxonomy('church_event_category', __('Event Categories', 'snap-ccb-church-connect'), __('Event Category', 'snap-ccb-church-connect'), 'event-category');
	}

	private function register_taxonomy($slug, $plural, $singular, $rewrite_slug) {
		if (taxonomy_exists($slug)) {
			register_taxonomy_for_object_type($slug, 'church_event');
			Logger::info('admin', 'Existing taxonomy attached to church_event.', array('taxonomy' => $slug));
			return;
		}

		register_taxonomy(
			$slug,
			array('church_event'),
			array(
				'labels' => array(
					'name'          => $plural,
					'singular_name' => $singular,
				),
				'public'       => true,
				'show_in_rest' => true,
				'hierarchical' => true,
				'rewrite'      => array('slug' => $rewrite_slug),
			)
		);
	}
}
