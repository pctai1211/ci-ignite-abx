<?php

namespace CI\IgniteAbx;

defined('ABSPATH') || exit;

class Deactivator
{
	public static function deactivate()
	{
		flush_rewrite_rules();
	}
}
