<?php
namespace SnapChurchConnect\PCO\Content;

if (! defined('ABSPATH')) {
	exit;
}

class Taxonomies {
	public function register() {
		$this->register_taxonomy('church_campus', __('Campuses', 'snap-pco-church-connect'), __('Campus', 'snap-pco-church-connect'), 'campus');
		$this->register_taxonomy('church_ministry', __('Ministries', 'snap-pco-church-connect'), __('Ministry', 'snap-pco-church-connect'), 'ministry');
		$this->register_taxonomy('church_event_category', __('Event Categories', 'snap-pco-church-connect'), __('Event Category', 'snap-pco-church-connect'), 'event-category');
	}

	private function register_taxonomy($slug, $plural, $singular, $rewrite_slug) {
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
