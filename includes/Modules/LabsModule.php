<?php

namespace CI\IgniteAbx\Modules;

use CI\IgniteAbx\Admin\LabsPage;
use CI\IgniteAbx\Security\Roles;

defined('ABSPATH') || exit;

class LabsModule implements Module
{
	public function id()
	{
		return 'labs';
	}

	public function pages()
	{
		$page = new LabsPage();

		return array(
			array(
				'slug'       => 'ci-abx-labs',
				'title'      => __('Labs', 'ci-ignite-abx'),
				'capability' => Roles::CAP_LABS,
				'callback'   => array($page, 'render'),
				'show_in_menu' => true,
				'icon'       => 'dashicons-building',
				'position'   => 26,
			),
		);
	}

	public function boot()
	{
		$page = new LabsPage();
		add_action('admin_post_ci_abx_save_lab', array($page, 'handle_save'));
		add_action('admin_post_ci_abx_delete_lab', array($page, 'handle_delete'));
	}
}
