<?php
namespace SnapChurchConnect\PCO\Content;

if (! defined('ABSPATH')) {
	exit;
}

class ContentTypes {
	public function register() {
		register_post_type(
			'church_event',
			array(
				'labels' => array(
					'name'          => __('Church Events', 'snap-pco-church-connect'),
					'singular_name' => __('Church Event', 'snap-pco-church-connect'),
					'add_new_item'  => __('Add New Church Event', 'snap-pco-church-connect'),
					'edit_item'     => __('Edit Church Event', 'snap-pco-church-connect'),
				),
				'public'       => true,
				'has_archive'  => true,
				'rewrite'      => array('slug' => 'events'),
				'show_in_rest' => true,
				'supports'     => array('title', 'editor', 'excerpt', 'thumbnail', 'custom-fields'),
				'menu_icon'    => 'dashicons-calendar-alt',
			)
		);
	}
}
