<?php

namespace CI\IgniteAbx;

use CI\IgniteAbx\Database\Schema;
use CI\IgniteAbx\Security\Roles;

defined('ABSPATH') || exit;

class Activator
{
	public static function activate()
	{
		self::install_schema();
		Roles::register();
		flush_rewrite_rules();
	}

	public static function install_schema()
	{
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		Schema::install();
		update_option('ci_abx_db_version', CI_ABX_DB_VERSION);
	}
}
