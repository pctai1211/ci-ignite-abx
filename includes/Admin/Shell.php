<?php

namespace CI\IgniteAbx\Admin;

use CI\IgniteAbx\Security\Roles;
use CI\IgniteAbx\Support\View;

defined('ABSPATH') || exit;

class Shell
{
	public function register()
	{
		add_action('init', array($this, 'ensure_roles'));
		add_action('admin_menu', array($this, 'hide_wp_menus'), 999);
		add_action('admin_init', array($this, 'redirect_lab_dashboard'));
		add_filter('admin_body_class', array($this, 'body_class'));
		add_filter('show_admin_bar', array($this, 'maybe_hide_admin_bar'), 99);
		add_action('admin_head', array($this, 'hide_wp_notices'));
		add_action('admin_notices', array($this, 'strip_notices'), 0);
	}

	public function ensure_roles()
	{
		Roles::register();
	}

	public function hide_wp_menus()
	{
		if (!Roles::is_lab_user()) {
			return;
		}

		$keep = array('ci-abx-labs', 'ci-abx-panels');
		global $menu, $submenu;

		if (is_array($menu)) {
			foreach ($menu as $index => $item) {
				$slug = isset($item[2]) ? $item[2] : '';
				if (!in_array($slug, $keep, true)) {
					remove_menu_page($slug);
				}
			}
		}

		unset($submenu['index.php']);
	}

	public function redirect_lab_dashboard()
	{
		if (!Roles::is_lab_user() || !is_admin() || wp_doing_ajax()) {
			return;
		}

		global $pagenow;
		$allowed = array('admin.php', 'admin-post.php', 'async-upload.php', 'media-upload.php', 'profile.php');
		$page    = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';

		if ($pagenow === 'index.php' || ($pagenow === 'admin.php' && $page && strpos($page, 'ci-abx') !== 0)) {
			wp_safe_redirect(View::url('ci-abx-labs'));
			exit;
		}

		if ($pagenow && !in_array($pagenow, $allowed, true) && strpos((string) $pagenow, 'admin-ajax') === false) {
			wp_safe_redirect(View::url('ci-abx-labs'));
			exit;
		}
	}

	public function body_class($classes)
	{
		if (self::is_plugin_screen()) {
			$classes .= ' ci-abx-app-screen';
		}
		if (Roles::is_lab_user()) {
			$classes .= ' ci-abx-lab-role';
		}

		return $classes;
	}

	public function maybe_hide_admin_bar($show)
	{
		if (self::is_plugin_screen() || Roles::is_lab_user()) {
			return false;
		}

		return $show;
	}

	public function hide_wp_notices()
	{
		if (!self::is_plugin_screen()) {
			return;
		}

		echo '<style id="ci-abx-hide-wp">.notice,.update-nag,#wpfooter,#screen-meta,#screen-meta-links,.error,.updated{display:none!important}</style>';
	}

	public function strip_notices()
	{
		if (self::is_plugin_screen()) {
			remove_all_actions('admin_notices');
			remove_all_actions('all_admin_notices');
		}
	}

	public static function is_plugin_screen()
	{
		if (!is_admin()) {
			return false;
		}
		$page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
		if ($page && strpos($page, 'ci-abx') === 0) {
			return true;
		}
		$screen = function_exists('get_current_screen') ? get_current_screen() : null;

		return $screen && strpos((string) $screen->id, 'ci-abx') !== false;
	}

	public static function nav_items()
	{
		$page   = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
		$lab_id = isset($_GET['lab_id']) ? absint($_GET['lab_id']) : 0;
		$items  = array();

		if (Roles::can_labs()) {
			$items[] = array(
				'slug'   => 'ci-abx-labs',
				'label'  => __('Labs', 'ci-ignite-abx'),
				'url'    => View::url('ci-abx-labs'),
				'active' => $page === 'ci-abx-labs',
				'icon'   => 'labs',
			);
		}

		$show_panels = Roles::is_lab_user() || (Roles::is_admin() && $lab_id);
		if ($show_panels && Roles::can_panels()) {
			$args = Roles::is_admin() && $lab_id ? array('lab_id' => $lab_id) : array();
			$items[] = array(
				'slug'   => 'ci-abx-panels',
				'label'  => __('Groups & Tests', 'ci-ignite-abx'),
				'url'    => View::url('ci-abx-panels', $args),
				'active' => $page === 'ci-abx-panels',
				'icon'   => 'panels',
			);
		}

		if (Roles::is_admin()) {
			$items[] = array(
				'slug'   => 'wp-admin',
				'label'  => __('WP Admin', 'ci-ignite-abx'),
				'url'    => admin_url(),
				'active' => false,
				'icon'   => 'wp',
			);
		}

		return $items;
	}

	public static function breadcrumbs()
	{
		$page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
		$act  = isset($_GET['act']) ? sanitize_key(wp_unslash($_GET['act'])) : '';
		$crumbs = array(
			array('label' => __('Dashboard', 'ci-ignite-abx'), 'url' => View::url('ci-abx-labs')),
			array('label' => __('Settings', 'ci-ignite-abx'), 'url' => ''),
		);

		if ($page === 'ci-abx-profile') {
			$crumbs[] = array('label' => __('Edit Profile', 'ci-ignite-abx'), 'url' => '');
		} elseif ($page === 'ci-abx-panels') {
			$crumbs[] = array('label' => __('Groups & Tests', 'ci-ignite-abx'), 'url' => View::url('ci-abx-panels', Roles::is_admin() && !empty($_GET['lab_id']) ? array('lab_id' => absint($_GET['lab_id'])) : array()));
			if ($act === 'add') {
				$crumbs[] = array('label' => __('Add Group', 'ci-ignite-abx'), 'url' => '');
			} elseif ($act === 'edit') {
				$crumbs[] = array('label' => __('Edit Group', 'ci-ignite-abx'), 'url' => '');
			}
		} else {
			$crumbs[] = array('label' => __('Labs', 'ci-ignite-abx'), 'url' => View::url('ci-abx-labs'));
			if ($act === 'new' || (empty($_GET['lab_id']) && Roles::is_lab_user())) {
				$crumbs[] = array('label' => __('Lab', 'ci-ignite-abx'), 'url' => '');
			} elseif (!empty($_GET['lab_id'])) {
				$crumbs[] = array('label' => __('Edit Lab', 'ci-ignite-abx'), 'url' => '');
			}
		}

		return $crumbs;
	}

	public static function icon($name)
	{
		$icons = array(
			'labs'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 20V9l8-5 8 5v11H4z"/><path d="M9 20v-6h6v6"/></svg>',
			'panels' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="4" width="7" height="7" rx="1.5"/><rect x="13" y="4" width="7" height="7" rx="1.5"/><rect x="4" y="13" width="7" height="7" rx="1.5"/><rect x="13" y="13" width="7" height="7" rx="1.5"/></svg>',
			'wp'     => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zm0 1.8c.9 0 1.7.16 2.5.44L7.2 18.3A8.18 8.18 0 0112 3.8zm6.4 13.5c-.7-2.1-2.4-6.8-2.4-6.8-.5-1.1-.9-2-.9-3 0-1.2.6-2.1 1.5-2.6A8.18 8.18 0 0120.2 12c0 1.9-.64 3.66-1.8 5.3zM5.4 16.2L9.9 6.1C8.9 6 8 6.8 8 8c0 .8.3 1.7.8 2.9l2.1 5.3-5.5.z"/></svg>',
		);

		return isset($icons[$name]) ? $icons[$name] : $icons['labs'];
	}
}
