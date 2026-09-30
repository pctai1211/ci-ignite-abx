<?php

namespace CI\IgniteAbx\Repositories;

defined('ABSPATH') || exit;

class PanelRepository extends Repository
{
	public function for_lab($lab_id)
	{
		return $this->db->get_results(
			$this->db->prepare(
				'SELECT * FROM ci_test_groups WHERE tg_lab_ID = %d AND tg_parent = 0 ORDER BY tg_position ASC, tg_name ASC',
				$lab_id
			),
			ARRAY_A
		);
	}

	public function children_for_lab($lab_id, $parent_id)
	{
		return $this->db->get_results(
			$this->db->prepare(
				'SELECT * FROM ci_test_groups WHERE tg_lab_ID = %d AND tg_parent = %d ORDER BY tg_position ASC, tg_name ASC',
				$lab_id,
				$parent_id
			),
			ARRAY_A
		);
	}

	public function parents_for_lab($lab_id, $exclude_id = 0)
	{
		$sql = $this->db->prepare(
			'SELECT tg_ID, tg_name, tg_show_req FROM ci_test_groups WHERE tg_lab_ID = %d AND tg_parent = 0',
			$lab_id
		);
		if ($exclude_id) {
			$sql .= $this->db->prepare(' AND tg_ID != %d', $exclude_id);
		}
		$sql .= ' ORDER BY tg_position ASC, tg_name ASC';

		return $this->db->get_results($sql);
	}

	public function find($id)
	{
		$row = $this->db->get_row(
			$this->db->prepare('SELECT * FROM ci_test_groups WHERE tg_ID = %d', $id),
			ARRAY_A
		);

		if (!$row) {
			return null;
		}

		$row['tg_other_options'] = self::decode_options($row['tg_other_options']);
		return $row;
	}

	public function find_by_name($lab_id, $name, $parent_id = 0)
	{
		$row = $this->db->get_row(
			$this->db->prepare(
				'SELECT * FROM ci_test_groups WHERE tg_lab_ID = %d AND tg_parent = %d AND tg_name = %s LIMIT 1',
				(int) $lab_id,
				(int) $parent_id,
				$name
			),
			ARRAY_A
		);

		if (!$row) {
			return null;
		}

		$row['tg_other_options'] = self::decode_options($row['tg_other_options']);
		return $row;
	}

	public function create(array $data)
	{
		$data['tg_created']  = $this->now();
		$data['tg_modified'] = $this->now();
		if (isset($data['tg_other_options']) && is_array($data['tg_other_options'])) {
			$data['tg_other_options'] = wp_json_encode($data['tg_other_options']);
		}
		$this->db->insert('ci_test_groups', $data);
		return (int) $this->db->insert_id;
	}

	public function update($id, array $data)
	{
		$data['tg_modified'] = $this->now();
		if (isset($data['tg_other_options']) && is_array($data['tg_other_options'])) {
			$data['tg_other_options'] = wp_json_encode($data['tg_other_options']);
		}
		return false !== $this->db->update('ci_test_groups', $data, array('tg_ID' => (int) $id));
	}

	public function delete($id)
	{
		(new TestRepository())->delete_for_group($id);
		return false !== $this->db->delete('ci_test_groups', array('tg_ID' => (int) $id));
	}

	public function delete_for_lab($lab_id)
	{
		$ids = $this->db->get_col(
			$this->db->prepare('SELECT tg_ID FROM ci_test_groups WHERE tg_lab_ID = %d', $lab_id)
		);
		foreach ($ids as $id) {
			$this->delete($id);
		}
	}

	public function count_for_lab($lab_id)
	{
		return (int) $this->db->get_var(
			$this->db->prepare('SELECT COUNT(*) FROM ci_test_groups WHERE tg_lab_ID = %d AND tg_parent = 0', $lab_id)
		);
	}

	public static function decode_options($raw)
	{
		if (is_array($raw)) {
			return $raw;
		}
		if ($raw === null || $raw === '') {
			return array();
		}
		$decoded = json_decode($raw, true);
		return is_array($decoded) ? $decoded : array();
	}
}
