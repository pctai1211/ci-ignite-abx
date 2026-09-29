<?php

namespace CI\IgniteAbx\Repositories;

use CI\IgniteAbx\Security\Roles;

defined('ABSPATH') || exit;

class LabUserRepository extends Repository
{
	public function lab_id_for_user($user_id)
	{
		$id = $this->db->get_var(
			$this->db->prepare('SELECT lab_ID FROM ci_lab_users WHERE user_id = %d', $user_id)
		);

		return $id ? (int) $id : 0;
	}

	public function user_ids_for_lab($lab_id)
	{
		$ids = $this->db->get_col(
			$this->db->prepare('SELECT user_id FROM ci_lab_users WHERE lab_ID = %d', $lab_id)
		);

		return array_map('intval', (array) $ids);
	}

	public function names_for_lab($lab_id)
	{
		$ids = $this->user_ids_for_lab($lab_id);
		if (!$ids) {
			return array();
		}

		$users = get_users(array('include' => $ids, 'orderby' => 'display_name'));
		$names = array();
		foreach ($users as $user) {
			$names[] = $user->display_name;
		}

		return $names;
	}

	public function attach($user_id, $lab_id)
	{
		$existing = $this->lab_id_for_user($user_id);
		if ($existing) {
			return $this->db->update(
				'ci_lab_users',
				array('lab_ID' => (int) $lab_id),
				array('user_id' => (int) $user_id)
			);
		}

		return $this->db->insert(
			'ci_lab_users',
			array(
				'user_id' => (int) $user_id,
				'lab_ID'  => (int) $lab_id,
				'created' => $this->now(),
			)
		);
	}

	public function detach($user_id)
	{
		return $this->db->delete('ci_lab_users', array('user_id' => (int) $user_id));
	}

	public function sync($lab_id, array $user_ids)
	{
		$lab_id   = (int) $lab_id;
		$user_ids = array_values(array_unique(array_filter(array_map('intval', $user_ids))));
		$current  = $this->user_ids_for_lab($lab_id);

		foreach (array_diff($current, $user_ids) as $uid) {
			$this->db->delete(
				'ci_lab_users',
				array(
					'user_id' => (int) $uid,
					'lab_ID'  => $lab_id,
				)
			);
		}

		foreach ($user_ids as $uid) {
			$user = get_userdata($uid);
			if (!$user || Roles::user_has_role(Roles::ADMIN, $user)) {
				continue;
			}
			$this->attach($uid, $lab_id);
			if (!in_array(Roles::LAB, (array) $user->roles, true)) {
				$user->add_role(Roles::LAB);
			}
		}
	}

	public static function assignable_users()
	{
		return get_users(
			array(
				'orderby'      => 'display_name',
				'order'        => 'ASC',
				'role__not_in' => array(Roles::ADMIN),
			)
		);
	}
}
