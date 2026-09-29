<?php

namespace CI\IgniteAbx;

defined('ABSPATH') || exit;

class Autoloader
{
	public static function register()
	{
		spl_autoload_register(array(self::class, 'load'));
	}

	public static function load($class)
	{
		$prefix = __NAMESPACE__ . '\\';
		if (strpos($class, $prefix) !== 0) {
			return;
		}

		$relative = str_replace('\\', '/', substr($class, strlen($prefix)));
		$file     = CI_ABX_PATH . 'includes/' . $relative . '.php';

		if (is_readable($file)) {
			require_once $file;
		}
	}
}
