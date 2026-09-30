<?php

namespace CI\IgniteAbx\Repositories;

defined('ABSPATH') || exit;

class TestRepository extends Repository
{
	public function for_group($group_id)
	{
		return $this->db->get_results(
			$this->db->prepare(
				'SELECT * FROM ci_test_panels WHERE te_group_id = %d ORDER BY te_position ASC, te_name ASC',
				$group_id
			),
			ARRAY_A
		);
	}

	public function find_by_group_name($group_id, $name)
	{
		return $this->db->get_row(
			$this->db->prepare(
				'SELECT * FROM ci_test_panels WHERE te_group_id = %d AND te_name = %s LIMIT 1',
				(int) $group_id,
				$name
			),
			ARRAY_A
		);
	}

	public function stats_for_lab($lab_id)
	{
		$sql = $this->db->prepare(
			"SELECT
				SUM(CASE WHEN te.te_enabled = 1 THEN 1 ELSE 0 END) AS active_tests,
				COUNT(te.te_ID) AS configured,
				SUM(CASE WHEN te.te_enabled = 0 THEN 1 ELSE 0 END) AS pending
			FROM ci_test_panels te
			INNER JOIN ci_test_groups g ON g.tg_ID = te.te_group_id
			WHERE g.tg_lab_ID = %d",
			$lab_id
		);

		$row = $this->db->get_row($sql, ARRAY_A);
		return array(
			'active_tests' => (int) ($row['active_tests'] ?? 0),
			'configured'   => (int) ($row['configured'] ?? 0),
			'pending'      => (int) ($row['pending'] ?? 0),
		);
	}

	public function counts_by_category($group_id)
	{
		$rows = $this->db->get_results(
			$this->db->prepare(
				'SELECT te_category, COUNT(*) AS total, SUM(te_enabled) AS enabled
				FROM ci_test_panels WHERE te_group_id = %d GROUP BY te_category',
				$group_id
			),
			ARRAY_A
		);

		$out = array();
		foreach ($rows as $row) {
			$key = $row['te_category'] ?: 'other';
			$out[$key] = array(
				'total'   => (int) $row['total'],
				'enabled' => (int) $row['enabled'],
			);
		}
		return $out;
	}

	public function upsert($group_id, array $test)
	{
		$now = $this->now();
		$id  = isset($test['te_ID']) ? absint($test['te_ID']) : 0;

		$data = array(
			'te_name'      => sanitize_text_field($test['te_name'] ?? ''),
			'te_abrev'     => sanitize_text_field($test['te_abrev'] ?? ''),
			'te_group_id'  => (int) $group_id,
			'te_category'  => sanitize_key($test['te_category'] ?? ''),
			'te_ct_vlow'   => $test['te_ct_vlow'] ?? '',
			'te_ct_low'    => $test['te_ct_low'] ?? '',
			'te_ct_normal' => $test['te_ct_normal'] ?? '',
			'te_ct_high'   => $test['te_ct_high'] ?? '',
			'te_ct_vhigh'  => $test['te_ct_vhigh'] ?? '',
			'te_enabled'   => array_key_exists('te_enabled', $test) && empty($test['te_enabled']) ? 0 : 1,
			'te_position'  => isset($test['te_position']) ? absint($test['te_position']) : 0,
			'te_modified'  => $now,
		);
		$optional_text = array('te_abrev2', 'te_loinc_code', 'te_units', 'te_range');
		foreach ($optional_text as $key) {
			if (array_key_exists($key, $test)) {
				$data[$key] = sanitize_text_field($test[$key]);
			}
		}
		if (array_key_exists('te_desc', $test)) {
			$data['te_desc'] = sanitize_textarea_field($test['te_desc']);
		}
		foreach (array('te_is_control', 'te_hide_pdf') as $key) {
			if (array_key_exists($key, $test)) {
				$data[$key] = empty($test[$key]) ? 0 : 1;
			}
		}

		if ($id) {
			return $this->db->update('ci_test_panels', $data, array('te_ID' => $id, 'te_group_id' => (int) $group_id));
		}

		$existing = $this->db->get_var(
			$this->db->prepare(
				'SELECT te_ID FROM ci_test_panels WHERE te_group_id = %d AND te_name = %s',
				$group_id,
				$data['te_name']
			)
		);

		if ($existing) {
			return $this->db->update('ci_test_panels', $data, array('te_ID' => (int) $existing));
		}

		$data['te_created'] = $now;
		return $this->db->insert('ci_test_panels', $data);
	}

	public function delete_for_group($group_id)
	{
		return $this->db->delete('ci_test_panels', array('te_group_id' => (int) $group_id));
	}
}
