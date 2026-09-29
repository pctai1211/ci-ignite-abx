<?php

namespace CI\IgniteAbx\Admin;

defined('ABSPATH') || exit;

class Assets
{
	public function enqueue($hook)
	{
		$screen = function_exists('get_current_screen') ? get_current_screen() : null;
		$page   = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
		$on_app = ($screen && strpos((string) $screen->id, 'ci-abx') !== false) || strpos($page, 'ci-abx') === 0;

		if (!$on_app) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_style('wp-color-picker');
		wp_enqueue_script('wp-color-picker');

		wp_enqueue_script(
			'ci-abx-tailwind',
			'https://cdn.tailwindcss.com',
			array(),
			null,
			false
		);

		wp_add_inline_script(
			'ci-abx-tailwind',
			'tailwind.config = { important: ".ci-abx-app", corePlugins: { preflight: false }, theme: { extend: { colors: { ci: "#e85d04" } } } };',
			'after'
		);

		wp_enqueue_style(
			'ci-abx-admin',
			CI_ABX_URL . 'assets/css/admin.css',
			array(),
			CI_ABX_VERSION
		);

		wp_enqueue_script(
			'ci-abx-admin',
			CI_ABX_URL . 'assets/js/admin.js',
			array('jquery', 'wp-color-picker'),
			CI_ABX_VERSION,
			true
		);

		wp_localize_script('ci-abx-admin', 'ciAbxAdmin', array(
			'mediaTitle'  => __('Choose logo', 'ci-ignite-abx'),
			'mediaButton' => __('Use this file', 'ci-ignite-abx'),
		));
	}
}
