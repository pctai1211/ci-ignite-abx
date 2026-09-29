<?php

namespace CI\IgniteAbx\Support;

defined('ABSPATH') || exit;

class Flash
{
	const KEY = 'ci_abx_flash_' ;

	public static function boot()
	{
		// no-op; messages are user transients
	}

	public static function add($message, $type = 'success')
	{
		$uid = get_current_user_id();
		set_transient(self::KEY . $uid, array(
			'message' => $message,
			'type'    => $type,
		), 60);
	}

	public static function add_error($message)
	{
		self::add($message, 'error');
	}

	public static function pull()
	{
		$uid = get_current_user_id();
		$data = get_transient(self::KEY . $uid);
		if ($data) {
			delete_transient(self::KEY . $uid);
		}
		return $data;
	}
}
