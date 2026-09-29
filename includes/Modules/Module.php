<?php

namespace CI\IgniteAbx\Modules;

defined('ABSPATH') || exit;

interface Module
{
	public function id();

	/**
	 * @return array<int, array{slug:string,title:string,capability:string,callback:callable,show_in_menu?:bool,parent?:string|null}>
	 */
	public function pages();

	public function boot();
}
