<?php

namespace CI\IgniteAbx\Support;

defined('ABSPATH') || exit;

class Request
{
	public static function post($key, $default = '')
	{
		return isset($_POST[$key]) ? wp_unslash($_POST[$key]) : $default;
	}

	public static function get($key, $default = '')
	{
		return isset($_GET[$key]) ? wp_unslash($_GET[$key]) : $default;
	}

	public static function int($key, $source = 'request')
	{
		if ($source === 'post') {
			return absint(self::post($key, 0));
		}
		if ($source === 'get') {
			return absint(self::get($key, 0));
		}
		return isset($_REQUEST[$key]) ? absint($_REQUEST[$key]) : 0;
	}

	public static function verify_nonce($action, $field = 'ci_abx_nonce')
	{
		$nonce = isset($_POST[$field]) ? sanitize_text_field(wp_unslash($_POST[$field])) : '';
		return $nonce && wp_verify_nonce($nonce, $action);
	}

	public static function csv_ids($value)
	{
		if (is_array($value)) {
			$ids = $value;
		} elseif ($value === null || $value === '' || $value === 0 || $value === '0') {
			return array();
		} else {
			$ids = explode(',', (string) $value);
		}

		$return = array();
		foreach ($ids as $id) {
			$id = trim((string) $id);
			if ($id !== '' && $id !== '0') {
				$return[] = $id;
			}
		}

		return array_values(array_unique($return));
	}
}
