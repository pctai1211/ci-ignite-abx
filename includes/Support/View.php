<?php

namespace CI\IgniteAbx\Support;

defined('ABSPATH') || exit;

class View
{
	public static function render($template, array $vars = array())
	{
		$path = CI_ABX_PATH . 'views/' . ltrim($template, '/') . '.php';
		if (!is_readable($path)) {
			return;
		}

		extract($vars, EXTR_SKIP);
		include $path;
	}

	public static function e($value)
	{
		echo esc_html((string) $value);
	}

	public static function attr($value)
	{
		echo esc_attr((string) $value);
	}

	public static function url($page, array $args = array())
	{
		$args['page'] = $page;
		return add_query_arg($args, admin_url('admin.php'));
	}
}
