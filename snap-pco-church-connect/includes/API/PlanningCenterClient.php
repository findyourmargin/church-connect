<?php
namespace SnapChurchConnect\PCO\API;

use SnapChurchConnect\PCO\Logging\Logger;
use SnapChurchConnect\PCO\Support\Helpers;

if (! defined('ABSPATH')) {
	exit;
}

class PlanningCenterClient {
	const BASE_URL = 'https://api.planningcenteronline.com';

	private $client_id;
	private $secret;
	private $last_rate_limit = array();

	public function __construct($client_id = null, $secret = null) {
		$this->client_id = null === $client_id ? Helpers::get_client_id() : $client_id;
		$this->secret    = null === $secret ? Helpers::get_secret() : $secret;
	}

	public function testConnection() {
		return $this->test_connection();
	}

	public function test_connection() {
		$result = $this->get('/calendar/v2/event_instances', array('per_page' => 1), false);
		if (empty($result['success'])) {
			return $result;
		}

		return array(
			'success' => true,
			'message' => 'Planning Center connection succeeded.',
			'data'    => isset($result['data']) ? $result['data'] : array(),
		);
	}

	public function getEventInstances($args = array()) {
		return $this->get_event_instances($args);
	}

	public function get_event_instances($args = array()) {
		$defaults = array(
			'order'    => 'starts_at',
			'per_page' => 100,
			'include'  => 'event,tags',
			'fields'   => array(
				'EventInstance' => 'name,description,starts_at,ends_at,all_day_event,church_center_url,image_url,location,recurrence,recurrence_description,compact_recurrence_description,kind,updated_at',
			),
		);

		$query = wp_parse_args($args, $defaults);
		$query['per_page'] = min(100, max(1, absint($query['per_page'])));

		$response = $this->get('/calendar/v2/event_instances', $query, true);
		if (empty($response['success'])) {
			return $response;
		}

		return $response;
	}

	public function get($path, array $query = array(), $paginate = false) {
		if (! $this->client_id || ! $this->secret) {
			return array(
				'success' => false,
				'message' => 'Planning Center credentials are missing.',
				'data'    => array(),
			);
		}

		$url     = self::BASE_URL . $path;
		$items   = array();
		$included = array();
		$meta    = array();
		$page    = 0;

		do {
			$page++;
			$request_url = add_query_arg($this->flatten_query($query), $url);
			$response    = wp_remote_get(
				$request_url,
				array(
					'timeout' => 20,
					'headers' => array(
						'Authorization' => 'Basic ' . base64_encode($this->client_id . ':' . $this->secret),
						'Accept'        => 'application/json',
					),
				)
			);

			if (is_wp_error($response)) {
				Logger::error('api', 'Planning Center request failed.', array('error' => $response->get_error_message(), 'path' => $path));
				return array('success' => false, 'message' => $response->get_error_message(), 'data' => $items);
			}

			$code = (int) wp_remote_retrieve_response_code($response);
			$this->capture_rate_limit_headers($response);

			$body = wp_remote_retrieve_body($response);
			$json = json_decode($body, true);

			if ($code < 200 || $code >= 300) {
				$message = isset($json['errors'][0]['detail']) ? $json['errors'][0]['detail'] : 'Planning Center returned a non-2xx response.';
				Logger::error('api', 'Planning Center API error.', array('status' => $code, 'message' => $message, 'path' => $path));
				return array('success' => false, 'message' => $message, 'status' => $code, 'data' => $items);
			}

			if (! is_array($json)) {
				Logger::error('api', 'Planning Center returned invalid JSON.', array('path' => $path));
				return array('success' => false, 'message' => 'Invalid JSON returned from Planning Center.', 'data' => $items);
			}

			if (isset($json['data']) && is_array($json['data'])) {
				$items = array_merge($items, $this->is_assoc($json['data']) ? array($json['data']) : $json['data']);
			}

			if (isset($json['included']) && is_array($json['included'])) {
				$included = array_merge($included, $json['included']);
			}

			$meta = isset($json['meta']) && is_array($json['meta']) ? $json['meta'] : $meta;
			$url  = $paginate && ! empty($json['links']['next']) ? esc_url_raw($json['links']['next']) : '';
			$query = array();
		} while ($paginate && $url && $page < 25);

		return array(
			'success'    => true,
			'message'    => 'OK',
			'data'       => $items,
			'included'   => $included,
			'meta'       => $meta,
			'rate_limit' => $this->last_rate_limit,
		);
	}

	private function flatten_query(array $query) {
		$flat = array();
		foreach ($query as $key => $value) {
			if (is_array($value)) {
				foreach ($value as $sub_key => $sub_value) {
					$flat[$key . '[' . $sub_key . ']'] = $sub_value;
				}
			} else {
				$flat[$key] = $value;
			}
		}
		return $flat;
	}

	private function capture_rate_limit_headers($response) {
		$headers = wp_remote_retrieve_headers($response);
		$keys    = array('x-ratelimit-limit', 'x-ratelimit-remaining', 'x-ratelimit-reset', 'retry-after');
		$this->last_rate_limit = array();

		foreach ($keys as $key) {
			$value = isset($headers[$key]) ? $headers[$key] : null;
			if (null !== $value) {
				$this->last_rate_limit[$key] = sanitize_text_field((string) $value);
			}
		}
	}

	private function is_assoc(array $array) {
		return array_keys($array) !== range(0, count($array) - 1);
	}
}
