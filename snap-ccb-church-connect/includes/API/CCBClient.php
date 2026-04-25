<?php
namespace SnapChurchConnect\CCB\API;

use SnapChurchConnect\CCB\Logging\Logger;
use SnapChurchConnect\CCB\Support\Helpers;

if (! defined('ABSPATH')) {
	exit;
}

class CCBClient {
	private $base_url;
	private $username;
	private $password;
	private $last_rate_limit = array();

	public function __construct($base_url = null, $username = null, $password = null) {
		$this->base_url = null === $base_url ? Helpers::get_api_base_url() : Helpers::normalize_api_url($base_url);
		$this->username = null === $username ? Helpers::get_username() : $username;
		$this->password = null === $password ? Helpers::get_password() : $password;
	}

	public function testConnection() {
		return $this->test_connection();
	}

	public function test_connection() {
		return $this->get('api_status');
	}

	public function getPublicCalendarListing($date_start, $date_end) {
		return $this->get_public_calendar_listing($date_start, $date_end);
	}

	public function get_public_calendar_listing($date_start, $date_end) {
		return $this->get(
			'public_calendar_listing',
			array(
				'date_start' => sanitize_text_field($date_start),
				'date_end'   => sanitize_text_field($date_end),
			)
		);
	}

	public function getEventProfile($id, $include_image_link = true) {
		return $this->get_event_profile($id, $include_image_link);
	}

	public function get_event_profile($id, $include_image_link = true) {
		return $this->get(
			'event_profile',
			array(
				'id'                 => sanitize_text_field($id),
				'include_image_link' => $include_image_link ? 'true' : 'false',
			)
		);
	}

	public function get($service, array $query = array()) {
		if (! $this->base_url || ! $this->username || ! $this->password) {
			return array('success' => false, 'message' => 'CCB credentials or API URL are missing.', 'data' => array());
		}

		$url = $this->build_service_url($service, $query);
		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 30,
				'headers' => array(
					'Authorization' => 'Basic ' . base64_encode($this->username . ':' . $this->password),
					'Accept'        => 'application/xml,text/xml',
				),
			)
		);

		if (is_wp_error($response)) {
			Logger::error('api', 'CCB request failed.', array('service' => $service, 'error' => $response->get_error_message()));
			return array('success' => false, 'message' => $response->get_error_message(), 'data' => array());
		}

		$code = (int) wp_remote_retrieve_response_code($response);
		$this->capture_rate_limit_headers($response);
		$body = wp_remote_retrieve_body($response);

		if ($code < 200 || $code >= 300) {
			Logger::error('api', 'CCB API returned a non-2xx response.', array('service' => $service, 'status' => $code));
			return array('success' => false, 'message' => 'CCB returned HTTP ' . $code . '.', 'status' => $code, 'data' => array());
		}

		$parsed = $this->parse_xml($body);
		if (empty($parsed['success'])) {
			Logger::error('api', 'CCB returned invalid XML.', array('service' => $service));
			return $parsed;
		}

		if ('public_calendar_listing' === $service) {
			Logger::info(
				'api',
				'CCB public_calendar_listing response shape.',
				array(
					'top_level_keys' => is_array($parsed['data']) ? implode(',', array_slice(array_map('sanitize_key', array_keys($parsed['data'])), 0, 30)) : '',
					'response_keys'  => is_array($parsed['data']) && isset($parsed['data']['response']) && is_array($parsed['data']['response']) ? implode(',', array_slice(array_map('sanitize_key', array_keys($parsed['data']['response'])), 0, 30)) : '',
					'body_bytes'     => strlen($body),
				)
			);
		}

		$error_message = $this->extract_error_message($parsed['data']);
		if ($error_message) {
			Logger::error('api', 'CCB API returned an application error.', array('service' => $service, 'message' => $error_message));
			return array(
				'success'    => false,
				'message'    => $error_message,
				'data'       => $parsed['data'],
				'rate_limit' => $this->last_rate_limit,
			);
		}

		return array(
			'success'    => true,
			'message'    => 'OK',
			'data'       => $parsed['data'],
			'rate_limit' => $this->last_rate_limit,
		);
	}

	public function build_service_url($service, array $query = array()) {
		$query = array_merge(array('srv' => sanitize_key($service)), $query);
		return add_query_arg(array_map('sanitize_text_field', $query), $this->base_url);
	}

	private function parse_xml($body) {
		if (! $body) {
			return array('success' => false, 'message' => 'Empty CCB response.', 'data' => array());
		}

		$previous = libxml_use_internal_errors(true);
		$xml = simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NONET | LIBXML_NOCDATA);
		libxml_clear_errors();
		libxml_use_internal_errors($previous);

		if (! $xml) {
			return array('success' => false, 'message' => 'Invalid XML returned from CCB.', 'data' => array());
		}

		return array('success' => true, 'data' => $this->xml_to_array($xml));
	}

	public function xml_to_array(\SimpleXMLElement $xml) {
		$result = array();
		foreach ($xml->attributes() as $key => $value) {
			$result['@attributes'][(string) $key] = sanitize_text_field((string) $value);
		}

		$children = $xml->children();
		if (! count($children)) {
			$text = trim((string) $xml);
			return $result ? array_merge($result, array('@text' => sanitize_text_field($text))) : sanitize_text_field($text);
		}

		foreach ($children as $key => $child) {
			$value = $this->xml_to_array($child);
			if (isset($result[$key])) {
				if (! is_array($result[$key]) || ! array_key_exists(0, $result[$key])) {
					$result[$key] = array($result[$key]);
				}
				$result[$key][] = $value;
			} else {
				$result[$key] = $value;
			}
		}

		return $result;
	}

	private function capture_rate_limit_headers($response) {
		$headers = wp_remote_retrieve_headers($response);
		foreach (array('x-ratelimit-limit', 'x-ratelimit-remaining', 'x-ratelimit-reset', 'retry-after') as $key) {
			if (isset($headers[$key])) {
				$this->last_rate_limit[$key] = sanitize_text_field((string) $headers[$key]);
			}
		}
	}

	private function extract_error_message($data) {
		if (! is_array($data) || empty($data['response']['errors'])) {
			return '';
		}

		$errors = $data['response']['errors'];
		$messages = array();
		$this->collect_error_messages($errors, $messages);

		return implode(' | ', array_slice(array_unique(array_filter($messages)), 0, 5));
	}

	private function collect_error_messages($value, array &$messages) {
		if (! is_array($value)) {
			$text = sanitize_text_field((string) $value);
			if ($text) {
				$messages[] = $text;
			}
			return;
		}

		if (isset($value['@text'])) {
			$text = sanitize_text_field((string) $value['@text']);
			if ($text) {
				$messages[] = $text;
			}
		}

		foreach ($value as $key => $child) {
			if ('@attributes' === $key) {
				continue;
			}
			$this->collect_error_messages($child, $messages);
		}
	}
}
