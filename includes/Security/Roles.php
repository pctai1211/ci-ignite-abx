<?php

namespace CI\IgniteAbx\Security;

defined('ABSPATH') || exit;

class Roles
{
	const ADMIN = 'administrator';
	const LAB   = 'ci_lab';

	const CAP_LABS   = 'ci_abx_manage_labs';
	const CAP_PANELS = 'ci_abx_manage_panels';

	public static function register()
	{
		$caps = array(
			'read'           => true,
			self::CAP_LABS   => true,
			self::CAP_PANELS => true,
			'upload_files'   => true,
		);

		$existing = get_role(self::LAB);
		if (!$existing) {
			add_role(self::LAB, __('Lab', 'ci-ignite-abx'), $caps);
		} else {
			foreach (array_keys($caps) as $cap) {
				$existing->add_cap($cap);
			}
		}

		$admin = get_role(self::ADMIN);
		if ($admin) {
			$admin->add_cap(self::CAP_LABS);
			$admin->add_cap(self::CAP_PANELS);
		}
	}

	public static function user_has_role($role, $user = null)
	{
		if (!$user) {
			$user = wp_get_current_user();
		}

		if (!$user || empty($user->ID)) {
			return false;
		}

		return in_array($role, (array) $user->roles, true);
	}

	/**
	 * WordPress administrator role only — not manage_options and not WP is_admin().
	 */
	public static function is_admin()
	{
		return self::user_has_role(self::ADMIN);
	}

	public static function is_lab_user()
	{
		return self::user_has_role(self::LAB) && !self::is_admin();
	}

	public static function can_labs()
	{
		return self::is_admin() || current_user_can(self::CAP_LABS);
	}

	public static function can_panels()
	{
		return self::is_admin() || current_user_can(self::CAP_PANELS);
	}
}
