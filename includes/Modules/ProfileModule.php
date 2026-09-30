<?php

namespace CI\IgniteAbx\Modules;

use CI\IgniteAbx\Admin\ProfilePage;

defined('ABSPATH') || exit;

class ProfileModule implements Module
{
	public function id()
	{
		return 'profile';
	}

	public function pages()
	{
		$page = new ProfilePage();

		return array(
			array(
				'slug'         => 'ci-abx-profile',
				'title'        => __('Edit Profile', 'ci-ignite-abx'),
				'capability'   => 'read',
				'callback'     => array($page, 'render'),
				'show_in_menu' => false,
			),
		);
	}

	public function boot()
	{
		$page = new ProfilePage();
		add_action('admin_post_ci_abx_save_profile', array($page, 'handle_save'));
	}
}
