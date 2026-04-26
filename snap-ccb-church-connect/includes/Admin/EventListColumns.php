<?php
namespace SnapChurchConnect\CCB\Admin;

if (! defined('ABSPATH')) {
	exit;
}

class EventListColumns {
	public function register() {
		add_filter('manage_church_event_posts_columns', array($this, 'columns'));
		add_action('manage_church_event_posts_custom_column', array($this, 'render_column'), 10, 2);
		add_filter('manage_edit-church_event_sortable_columns', array($this, 'sortable_columns'));
		add_action('pre_get_posts', array($this, 'sort_by_event_date'));
	}

	public function columns($columns) {
		$new_columns = array();

		foreach ($columns as $key => $label) {
			$new_columns[$key] = $label;

			if ('title' === $key) {
				$new_columns['church_event_date'] = __('Event Date', 'snap-ccb-church-connect');
				$new_columns['church_event_location'] = __('Location', 'snap-ccb-church-connect');
				$new_columns['church_event_status'] = __('Event Status', 'snap-ccb-church-connect');
				$new_columns['church_event_provider'] = __('Provider', 'snap-ccb-church-connect');
			}
		}

		return $new_columns;
	}

	public function render_column($column, $post_id) {
		switch ($column) {
			case 'church_event_date':
				echo esc_html($this->event_date((int) $post_id));
				break;
			case 'church_event_location':
				echo esc_html((string) get_post_meta($post_id, 'church_event_location', true));
				break;
			case 'church_event_status':
				echo esc_html((string) get_post_meta($post_id, 'church_event_status', true));
				break;
			case 'church_event_provider':
				echo esc_html(strtoupper((string) get_post_meta($post_id, '_church_connect_provider', true)));
				break;
		}
	}

	public function sortable_columns($columns) {
		$columns['church_event_date'] = 'church_event_date';
		return $columns;
	}

	public function sort_by_event_date($query) {
		if (! is_admin() || ! $query->is_main_query()) {
			return;
		}

		if ('church_event' !== $query->get('post_type') || 'church_event_date' !== $query->get('orderby')) {
			return;
		}

		$query->set('meta_key', 'church_event_start_ts');
		$query->set('orderby', 'meta_value_num');
	}

	private function event_date($post_id) {
		$start_ts = (int) get_post_meta($post_id, 'church_event_start_ts', true);
		$end_ts = (int) get_post_meta($post_id, 'church_event_end_ts', true);
		if (! $start_ts) {
			return '';
		}

		$date_format = get_option('date_format', 'F j, Y');
		$time_format = get_option('time_format', 'g:i a');
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
}
