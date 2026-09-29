<?php

namespace CI\IgniteAbx\Repositories;

defined('ABSPATH') || exit;

class LabRepository extends Repository
{
	const TABLE = 'ci_labs';

	public function all()
	{
		return $this->db->get_results("SELECT * FROM ci_labs ORDER BY lab_name ASC", ARRAY_A);
	}

	public function find($id)
	{
		return $this->db->get_row(
			$this->db->prepare('SELECT * FROM ci_labs WHERE lab_ID = %d', $id),
			ARRAY_A
		);
	}

	public function create(array $data)
	{
		$data['lab_created']  = $this->now();
		$data['lab_modified'] = $this->now();
		$this->db->insert('ci_labs', $data);
		return (int) $this->db->insert_id;
	}

	public function update($id, array $data)
	{
		$data['lab_modified'] = $this->now();
		return false !== $this->db->update('ci_labs', $data, array('lab_ID' => (int) $id));
	}

	public function delete($id)
	{
		$panel_repo = new PanelRepository();
		$panel_repo->delete_for_lab($id);

		$this->db->delete('ci_lab_users', array('lab_ID' => (int) $id));
		return false !== $this->db->delete('ci_labs', array('lab_ID' => (int) $id));
	}
}
