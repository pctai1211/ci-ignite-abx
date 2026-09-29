<?php

namespace CI\IgniteAbx\Admin;

use CI\IgniteAbx\Plugin;

defined('ABSPATH') || exit;

class Menu
{
	/** @var Plugin */
	private $plugin;

	public function __construct(Plugin $plugin)
	{
		$this->plugin = $plugin;
	}

	public function register()
	{
		foreach ($this->plugin->modules() as $module) {
			foreach ($module->pages() as $page) {
				$show = !isset($page['show_in_menu']) || $page['show_in_menu'];
				if ($show) {
					add_menu_page(
						$page['title'],
						$page['title'],
						$page['capability'],
						$page['slug'],
						$page['callback'],
						isset($page['icon']) ? $page['icon'] : 'dashicons-admin-generic',
						isset($page['position']) ? $page['position'] : 58
					);
				} else {
					add_submenu_page(
						null,
						$page['title'],
						$page['title'],
						$page['capability'],
						$page['slug'],
						$page['callback']
					);
				}
			}
		}
	}
}
