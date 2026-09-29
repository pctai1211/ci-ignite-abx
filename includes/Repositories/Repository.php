<?php

namespace CI\IgniteAbx\Repositories;

defined('ABSPATH') || exit;

abstract class Repository
{
	/** @var \wpdb */
	protected $db;

	public function __construct()
	{
		$this->db = $GLOBALS['wpdb'];
	}

	protected function now()
	{
		return current_time('mysql');
	}
}
