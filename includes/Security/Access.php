<?php

namespace CI\IgniteAbx\Security;

use CI\IgniteAbx\Repositories\LabUserRepository;

defined('ABSPATH') || exit;

class Access
{
	/** @var LabUserRepository */
	private $lab_users;

	public function __construct()
	{
		$this->lab_users = new LabUserRepository();
	}

	public function current_lab_id()
	{
		if (Roles::is_admin()) {
			$lab_id = isset($_GET['lab_id']) ? absint($_GET['lab_id']) : 0;
			return $lab_id ?: 0;
		}

		if (!Roles::is_lab_user()) {
			return 0;
		}

		return $this->lab_users->lab_id_for_user(get_current_user_id());
	}

	public function can_access_lab($lab_id)
	{
		$lab_id = (int) $lab_id;
		if ($lab_id < 1) {
			return false;
		}

		if (Roles::is_admin()) {
			return true;
		}

		return $this->current_lab_id() === $lab_id;
	}

	public function require_labs()
	{
		if (!Roles::can_labs()) {
			wp_die(esc_html__('You do not have permission to manage labs.', 'ci-ignite-abx'));
		}
	}

	public function require_panels()
	{
		if (!Roles::can_panels()) {
			wp_die(esc_html__('You do not have permission to manage panels.', 'ci-ignite-abx'));
		}
	}
}
