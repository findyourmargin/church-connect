<?php
namespace SnapChurchConnect\CCB\Content;

use SnapChurchConnect\CCB\Logging\Logger;

if (! defined('ABSPATH')) {
	exit;
}

class ContentTypes {
	public function register() {
		if (post_type_exists('church_event')) {
			Logger::info('admin', 'church_event post type already exists; Snap! CCB Church Connect reused it.');
			return;
		}

		register_post_type(
			'church_event',
			array(
				'labels' => array(
					'name'          => __('Church Events', 'snap-ccb-church-connect'),
					'singular_name' => __('Church Event', 'snap-ccb-church-connect'),
					'add_new_item'  => __('Add New Church Event', 'snap-ccb-church-connect'),
					'edit_item'     => __('Edit Church Event', 'snap-ccb-church-connect'),
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
