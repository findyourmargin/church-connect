<?php
namespace SnapChurchConnect\CCB\Content;

if (! defined('ABSPATH')) {
	exit;
}

class MetaFields {
	public function register() {
		$string_fields = array(
			'church_event_start',
			'church_event_end',
			'church_event_timezone',
			'church_event_location',
			'church_event_address',
			'church_event_summary',
			'church_event_recurrence',
			'church_event_status',
		);

		foreach ($string_fields as $field) {
			$this->register_public($field, 'string', 'sanitize_text_field');
		}

		$this->register_public('church_event_description', 'string', 'wp_kses_post');
		$this->register_public('church_event_start_ts', 'integer', 'absint');
		$this->register_public('church_event_end_ts', 'integer', 'absint');
		$this->register_public('church_event_image_url', 'string', 'esc_url_raw', 'uri');
		$this->register_public('church_event_registration_url', 'string', 'esc_url_raw', 'uri');
		$this->register_public('church_event_external_url', 'string', 'esc_url_raw', 'uri');
		$this->register_public('church_event_featured', 'boolean', array($this, 'sanitize_bool'));
		$this->register_public('church_event_all_day', 'boolean', array($this, 'sanitize_bool'));
		$this->register_public('church_event_repeating', 'boolean', array($this, 'sanitize_bool'));

		$internal = array(
			'_church_connect_provider',
			'_church_connect_external_id',
			'_church_connect_external_instance_id',
			'_church_connect_external_updated_at',
			'_church_connect_last_synced_at',
			'_church_connect_sync_hash',
			'_church_connect_raw_data',
		);

		foreach ($internal as $field) {
			register_post_meta(
				'church_event',
				$field,
				array(
					'type'              => 'string',
					'single'            => true,
					'show_in_rest'      => false,
					'sanitize_callback' => 'sanitize_textarea_field',
					'auth_callback'     => array($this, 'can_edit_posts'),
				)
			);
		}
	}

	public function sanitize_bool($value) {
		return filter_var($value, FILTER_VALIDATE_BOOLEAN);
	}

	public function can_edit_posts() {
		return current_user_can('edit_posts');
	}

	private function register_public($key, $type, $sanitize_callback, $format = null) {
		$schema = array(
			'type' => $type,
		);

		if ($format) {
			$schema['format'] = $format;
		}

		register_post_meta(
			'church_event',
			$key,
			array(
				'type'              => $type,
				'single'            => true,
				'show_in_rest'      => array('schema' => $schema),
				'sanitize_callback' => $sanitize_callback,
				'auth_callback'     => '__return_true',
			)
		);
	}
}
