<?php

namespace CI\IgniteAbx\Modules;

use CI\IgniteAbx\Admin\PanelsPage;
use CI\IgniteAbx\Security\Roles;

defined('ABSPATH') || exit;

class PanelsModule implements Module
{
	public function id()
	{
		return 'panels';
	}

	public function pages()
	{
		$page = new PanelsPage();

		return array(
			array(
				'slug'         => 'ci-abx-panels',
				'title'        => __('Panels', 'ci-ignite-abx'),
				'capability'   => Roles::CAP_PANELS,
				'callback'     => array($page, 'render'),
				'show_in_menu' => Roles::is_lab_user() && !Roles::is_admin(),
				'icon'         => 'dashicons-analytics',
				'position'     => 27,
			),
		);
	}

	public function boot()
	{
		$page = new PanelsPage();
		add_action('admin_post_ci_abx_save_panel', array($page, 'handle_save'));
		add_action('admin_post_ci_abx_delete_panel', array($page, 'handle_delete'));
		add_action('admin_post_ci_abx_save_tests', array($page, 'handle_save_tests'));
		add_action('admin_post_ci_abx_export_panels', array($page, 'handle_export'));
		add_action('admin_post_ci_abx_import_panels', array($page, 'handle_import'));
	}
}
