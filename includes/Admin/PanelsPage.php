<?php

namespace CI\IgniteAbx\Admin;

use CI\IgniteAbx\Config\PanelFields;
use CI\IgniteAbx\Repositories\LabRepository;
use CI\IgniteAbx\Repositories\PanelRepository;
use CI\IgniteAbx\Repositories\TestRepository;
use CI\IgniteAbx\Security\Access;
use CI\IgniteAbx\Security\Roles;
use CI\IgniteAbx\Support\Flash;
use CI\IgniteAbx\Support\Request;
use CI\IgniteAbx\Support\View;

defined('ABSPATH') || exit;

class PanelsPage
{
	/** @var PanelRepository */
	private $panels;

	/** @var TestRepository */
	private $tests;

	/** @var LabRepository */
	private $labs;

	/** @var Access */
	private $access;

	public function __construct()
	{
		$this->panels = new PanelRepository();
		$this->tests  = new TestRepository();
		$this->labs   = new LabRepository();
		$this->access = new Access();
	}

	public function render()
	{
		$this->access->require_panels();

		$lab_id = $this->resolve_lab_id();
		if (!$lab_id) {
			View::render('layout', array(
				'title'    => __('Groups & Tests', 'ci-ignite-abx'),
				'subtitle' => '',
				'content'  => 'panels/no-lab',
				'data'     => array('is_admin' => Roles::is_admin()),
			));
			return;
		}

		$act = sanitize_key(Request::get('act', ''));
		$id  = Request::int('id', 'get');

		if ($act === 'add' || $act === 'edit') {
			$this->render_form($lab_id, $act, $id);
			return;
		}

		$lab    = $this->labs->find($lab_id);
		$panels = $this->panels->for_lab($lab_id);
		foreach ($panels as &$panel) {
			$panel['tests']    = $this->tests->for_group($panel['tg_ID']);
			$panel['children'] = $this->panels->children_for_lab($lab_id, $panel['tg_ID']);
			foreach ($panel['children'] as &$child) {
				$child['tests'] = $this->tests->for_group($child['tg_ID']);
			}
			unset($child);
		}
		unset($panel);

		View::render('layout', array(
			'title'    => __('Test Groups', 'ci-ignite-abx'),
			'subtitle' => sprintf(
				/* translators: %s lab name */
				__('Manage groups and tests for %s', 'ci-ignite-abx'),
				$lab ? $lab['lab_name'] : ''
			),
			'content'  => 'panels/list',
			'data'     => array(
				'lab'    => $lab,
				'lab_id' => $lab_id,
				'panels' => $panels,
			),
		));
	}

	private function render_form($lab_id, $act, $id)
	{
		if ($act === 'edit') {
			$group = $this->panels->find($id);
			if (!$group || (int) $group['tg_lab_ID'] !== (int) $lab_id) {
				wp_die(esc_html__('Group not found.', 'ci-ignite-abx'));
			}
			$tests = $this->tests->for_group($id);
			$tests = $this->label_tests_with_group($tests, $group);
			if (empty($group['tg_parent'])) {
				foreach ($this->panels->children_for_lab($lab_id, $id) as $child) {
					$tests = array_merge($tests, $this->label_tests_with_group($this->tests->for_group($child['tg_ID']), $child));
				}
			}
		} else {
			$parent_id = Request::int('parent', 'get');
			if ($parent_id) {
				$parent = $this->panels->find($parent_id);
				if (!$parent || (int) $parent['tg_lab_ID'] !== (int) $lab_id || (int) $parent['tg_parent'] !== 0) {
					wp_die(esc_html__('Parent group not found.', 'ci-ignite-abx'));
				}
			}
			$group = $this->empty_group($lab_id, $parent_id);
			$tests = array();
		}

		View::render('layout', array(
			'title'    => $act === 'add' ? __('Add Test Group', 'ci-ignite-abx') : __('Edit Test Group', 'ci-ignite-abx'),
			'subtitle' => __('Organize parent groups, child groups, and their tests.', 'ci-ignite-abx'),
			'content'  => 'panels/form',
			'data'     => array(
				'lab_id'       => $lab_id,
				'group'        => $group,
				'parents'      => $this->panels->parents_for_lab($lab_id, $id),
				'tests'        => $tests,
				'mode'         => $act,
			),
		));
	}

	public function handle_save()
	{
		$this->access->require_panels();

		if (!Request::verify_nonce('ci_abx_save_panel')) {
			wp_die(esc_html__('Invalid security token.', 'ci-ignite-abx'));
		}

		$lab_id = absint(Request::post('lab_id', 0));
		if (!$this->access->can_access_lab($lab_id) && !(Roles::is_admin() && $lab_id)) {
			wp_die(esc_html__('Access denied.', 'ci-ignite-abx'));
		}
		if (Roles::is_lab_user()) {
			$lab_id = $this->access->current_lab_id();
		}

		$input   = isset($_POST['group']) && is_array($_POST['group']) ? wp_unslash($_POST['group']) : array();
		$payload = $this->sanitize_group($input, $lab_id);
		$id      = isset($input['tg_ID']) ? absint($input['tg_ID']) : 0;
		$parent_id = (int) $payload['tg_parent'];
		if ($parent_id) {
			$parent = $this->panels->find($parent_id);
			if (!$parent || (int) $parent['tg_lab_ID'] !== (int) $lab_id || (int) $parent['tg_parent'] !== 0 || $parent_id === $id) {
				wp_die(esc_html__('Invalid parent group.', 'ci-ignite-abx'));
			}
			if ($id && !empty($this->panels->children_for_lab($lab_id, $id))) {
				wp_die(esc_html__('A parent group with child groups cannot be moved under another group.', 'ci-ignite-abx'));
			}
		}

		if ($payload['tg_name'] === '') {
			Flash::add_error(__('Panel name is required.', 'ci-ignite-abx'));
			wp_safe_redirect(wp_get_referer());
			exit;
		}

		if ($id) {
			$existing = $this->panels->find($id);
			if (!$existing || (int) $existing['tg_lab_ID'] !== (int) $lab_id) {
				wp_die(esc_html__('Panel not found.', 'ci-ignite-abx'));
			}
			$this->panels->update($id, $payload);
			Flash::add(__('Panel saved.', 'ci-ignite-abx'));
		} else {
			$id = $this->panels->create($payload);
			Flash::add(__('Panel created.', 'ci-ignite-abx'));
		}

		if (isset($_POST['tests']) && is_array($_POST['tests'])) {
			$this->save_tests($id, wp_unslash($_POST['tests']), $lab_id);
		}

		$args = array('act' => 'edit', 'id' => $id);
		if (isset($_POST['return_tab']) && sanitize_key(wp_unslash($_POST['return_tab'])) === 'tests') {
			$args['tab'] = 'tests';
		}
		if (Roles::is_admin()) {
			$args['lab_id'] = $lab_id;
		}
		wp_safe_redirect(View::url('ci-abx-panels', $args));
		exit;
	}

	public function handle_save_tests()
	{
		$this->access->require_panels();

		if (!Request::verify_nonce('ci_abx_save_tests')) {
			wp_die(esc_html__('Invalid security token.', 'ci-ignite-abx'));
		}

		$id     = Request::int('tg_ID', 'post');
		$lab_id = $this->lab_id_for_panel($id);
		$tests  = isset($_POST['tests']) && is_array($_POST['tests']) ? wp_unslash($_POST['tests']) : array();
		$this->save_tests($id, $tests, $lab_id);
		Flash::add(__('CT values saved.', 'ci-ignite-abx'));

		$args = array('act' => 'edit', 'id' => $id, 'tab' => 'tests');
		if (Roles::is_admin()) {
			$args['lab_id'] = $lab_id;
		}
		wp_safe_redirect(View::url('ci-abx-panels', $args));
		exit;
	}

	public function handle_delete()
	{
		$this->access->require_panels();

		if (!Request::verify_nonce('ci_abx_delete_panel')) {
			wp_die(esc_html__('Invalid security token.', 'ci-ignite-abx'));
		}

		$id     = Request::int('tg_ID', 'post');
		$lab_id = $this->lab_id_for_panel($id);
		$this->panels->delete($id);
		Flash::add(__('Panel deleted.', 'ci-ignite-abx'));

		$args = array();
		if (Roles::is_admin()) {
			$args['lab_id'] = $lab_id;
		}
		wp_safe_redirect(View::url('ci-abx-panels', $args));
		exit;
	}

	public function handle_export()
	{
		$this->access->require_panels();

		if (!Request::verify_nonce('ci_abx_export_panels')) {
			wp_die(esc_html__('Invalid security token.', 'ci-ignite-abx'));
		}

		$lab_id = $this->resolve_lab_id_post();
		$panels = $this->panels->for_lab($lab_id);
		$export = array();
		foreach ($panels as $panel) {
			$export[] = $this->export_group_tree($panel, $lab_id);
		}

		nocache_headers();
		header('Content-Type: application/json; charset=utf-8');
		header('Content-Disposition: attachment; filename=ci-abx-panels-lab-' . $lab_id . '.json');
		echo wp_json_encode($export, JSON_PRETTY_PRINT);
		exit;
	}

	public function handle_export_csv()
	{
		$this->access->require_panels();
		if (!Request::verify_nonce('ci_abx_export_panels')) {
			wp_die(esc_html__('Invalid security token.', 'ci-ignite-abx'));
		}

		$lab_id = $this->resolve_lab_id_post();
		$handle = fopen('php://temp', 'r+');
		$headers = $this->csv_headers();
		fputcsv($handle, $headers);
		if (empty($_POST['template'])) {
			foreach ($this->panels->for_lab($lab_id) as $group) {
				$this->write_group_csv($handle, $group, $lab_id);
			}
		}
		rewind($handle);
		$csv = stream_get_contents($handle);
		fclose($handle);

		nocache_headers();
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=ci-ignite-abx-groups-lab-' . $lab_id . ($this->is_template_request() ? '-template' : '') . '.csv');
		echo "\xEF\xBB\xBF" . $csv;
		exit;
	}

	public function handle_import()
	{
		$this->access->require_panels();

		if (!Request::verify_nonce('ci_abx_import_panels')) {
			wp_die(esc_html__('Invalid security token.', 'ci-ignite-abx'));
		}

		$lab_id = $this->resolve_lab_id_post();
		if (empty($_FILES['import_file']['tmp_name']) || !isset($_FILES['import_file']['error']) || UPLOAD_ERR_OK !== (int) $_FILES['import_file']['error']) {
			Flash::add_error(__('No file uploaded.', 'ci-ignite-abx'));
			wp_safe_redirect(wp_get_referer());
			exit;
		}

		$extension = strtolower(pathinfo(sanitize_file_name($_FILES['import_file']['name']), PATHINFO_EXTENSION));
		if ($extension === 'csv') {
			$counts = $this->import_csv($_FILES['import_file']['tmp_name'], $lab_id, sanitize_key(Request::post('import_mode', 'create_update')));
			Flash::add(sprintf(__('CSV import complete: %1$d groups and %2$d tests created, %3$d groups and %4$d tests updated, %5$d instrument-specific duplicate rows consolidated, %6$d rows skipped.', 'ci-ignite-abx'), $counts['groups_created'], $counts['tests_created'], $counts['groups_updated'], $counts['tests_updated'], $counts['duplicate_rows'], $counts['skipped']));
			$args = Roles::is_admin() ? array('lab_id' => $lab_id) : array();
			wp_safe_redirect(View::url('ci-abx-panels', $args));
			exit;
		}
		if ($extension !== 'json') {
			Flash::add_error(__('Please upload a JSON or CSV file.', 'ci-ignite-abx'));
			wp_safe_redirect(wp_get_referer());
			exit;
		}

		$raw  = file_get_contents($_FILES['import_file']['tmp_name']);
		$list = json_decode($raw, true);
		if (is_array($list) && (isset($list['cutoffs']) || isset($list['metas']) || isset($list['specimens']))) {
			Flash::add_error(__('This legacy JSON export contains cutoff, metadata, or specimen data that this plugin schema does not support importing. Export the current plugin data as JSON or use the CSV format for groups and tests.', 'ci-ignite-abx'));
			wp_safe_redirect(wp_get_referer());
			exit;
		}
		if (!is_array($list) || isset($list['groups'])) {
			Flash::add_error(__('Invalid JSON file. Import a JSON export created by this plugin.', 'ci-ignite-abx'));
			wp_safe_redirect(wp_get_referer());
			exit;
		}

		$mode = sanitize_key(Request::post('import_mode', 'create_update'));
		if (!in_array($mode, array('update_only', 'create_merge', 'create_update'), true)) {
			$mode = 'create_update';
		}
		$counts = array('groups_created' => 0, 'groups_updated' => 0, 'tests_created' => 0, 'tests_updated' => 0, 'skipped' => 0);
		foreach ($list as $item) {
			if (is_array($item)) {
				$this->import_json_group($item, $lab_id, 0, $mode, $counts);
			}
		}

		Flash::add(sprintf(__('JSON import complete: %1$d groups and %2$d tests created, %3$d groups and %4$d tests updated, %5$d items skipped.', 'ci-ignite-abx'), $counts['groups_created'], $counts['tests_created'], $counts['groups_updated'], $counts['tests_updated'], $counts['skipped']));
		$args = Roles::is_admin() ? array('lab_id' => $lab_id) : array();
		wp_safe_redirect(View::url('ci-abx-panels', $args));
		exit;
	}

	private function export_group_tree(array $group, $lab_id)
	{
		$group['tg_other_options'] = PanelRepository::decode_options($group['tg_other_options']);
		$group['tests'] = $this->tests->for_group($group['tg_ID']);
		$group['children'] = array();
		foreach ($this->panels->children_for_lab($lab_id, $group['tg_ID']) as $child) {
			$group['children'][] = $this->export_group_tree($child, $lab_id);
		}
		return $group;
	}

	private function csv_headers()
	{
		return array('Group', 'Group Label', 'Sub Group', 'Test Name', 'Abbreviation', 'Control', 'LOINC', 'Category', 'Enabled', 'V.Low', 'Low', 'Normal', 'High', 'V.High');
	}

	private function is_template_request()
	{
		return !empty($_POST['template']);
	}

	private function write_group_csv($handle, array $parent, $lab_id)
	{
		$this->write_group_tests_csv($handle, $parent, $parent);
		foreach ($this->panels->children_for_lab($lab_id, $parent['tg_ID']) as $child) {
			$this->write_group_tests_csv($handle, $parent, $child);
		}
	}

	private function write_group_tests_csv($handle, array $parent, array $group)
	{
		$tests = $this->tests->for_group($group['tg_ID']);
		if (!$tests) {
			fputcsv($handle, array($parent['tg_name'], $parent['tg_short_name'], (int) $group['tg_ID'] !== (int) $parent['tg_ID'] ? $group['tg_name'] : '', '', '', '', '', '', '', '', '', '', '', ''));
			return;
		}
		foreach ($tests as $test) {
			fputcsv($handle, array(
				$parent['tg_name'],
				$parent['tg_short_name'],
				(int) $group['tg_ID'] !== (int) $parent['tg_ID'] ? $group['tg_name'] : '',
				$test['te_name'],
				$test['te_abrev'],
				!empty($test['te_is_control']) ? 'Yes' : 'No',
				$test['te_loinc_code'],
				$test['te_category'],
				!empty($test['te_enabled']) ? 'Yes' : 'No',
				$test['te_ct_vlow'],
				$test['te_ct_low'],
				$test['te_ct_normal'],
				$test['te_ct_high'],
				$test['te_ct_vhigh'],
			));
		}
	}

	private function import_csv($path, $lab_id, $mode)
	{
		if (!in_array($mode, array('update_only', 'create_merge', 'create_update'), true)) {
			$mode = 'create_update';
		}
		$handle = fopen($path, 'r');
		if (!$handle) {
			wp_die(esc_html__('Could not read the uploaded CSV file.', 'ci-ignite-abx'));
		}
		$first_line = fgets($handle);
		$delimiter = substr_count((string) $first_line, ';') > substr_count((string) $first_line, ',') ? ';' : ',';
		rewind($handle);
		$headers = fgetcsv($handle, 0, $delimiter);
		if (!$headers || count($headers) < 2) {
			fclose($handle);
			wp_die(esc_html__('The CSV file must include a header row and group/test columns.', 'ci-ignite-abx'));
		}
		$map = array();
		foreach ($headers as $index => $header) {
			$key = $this->csv_field_key($header);
			if ($key) {
				$map[$key] = $index;
			}
		}
		if (!isset($map['group'])) {
			fclose($handle);
			wp_die(esc_html__('The CSV file must contain a Group column.', 'ci-ignite-abx'));
		}

		$counts = array('groups_created' => 0, 'groups_updated' => 0, 'tests_created' => 0, 'tests_updated' => 0, 'duplicate_rows' => 0, 'skipped' => 0);
		$group_cache = array();
		$seen_tests = array();
		$position = 0;
		$last_parent_name = '';
		$last_child_name = '';
		while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
			if (count($row) === 1 && trim((string) $row[0]) === '') {
				continue;
			}
			$parent_cell = isset($map['group'], $row[$map['group']]) ? sanitize_text_field($row[$map['group']]) : '';
			$child_cell = isset($map['subgroup'], $row[$map['subgroup']]) ? sanitize_text_field($row[$map['subgroup']]) : '';
			if ($parent_cell !== '') {
				if ($parent_cell !== $last_parent_name || $child_cell === '') {
					$last_child_name = '';
				}
				$parent_name = $parent_cell;
				$last_parent_name = $parent_cell;
			} else {
				$parent_name = $last_parent_name;
			}
			if ($parent_name === '') {
				$counts['skipped']++;
				continue;
			}
			$parent_label = isset($map['group_label'], $row[$map['group_label']]) ? sanitize_text_field($row[$map['group_label']]) : '';
			$parent_id = $this->resolve_import_group($parent_name, $parent_label, 0, $lab_id, $mode, $group_cache, $counts);
			if (!$parent_id) {
				$counts['skipped']++;
				continue;
			}
			$group_id = $parent_id;
			$child_name = $child_cell !== '' ? $child_cell : $last_child_name;
			if ($child_name !== '') {
				$group_id = $this->resolve_import_group($child_name, '', $parent_id, $lab_id, $mode, $group_cache, $counts);
				if (!$group_id) {
					$counts['skipped']++;
					continue;
				}
			}
			$last_child_name = $child_name;
			$name = isset($map['test_name'], $row[$map['test_name']]) ? sanitize_text_field($row[$map['test_name']]) : '';
			if ($name === '') {
				continue;
			}

			$test_key = (int) $group_id . ':' . strtolower($name);
			$instrument = strtolower(trim($this->csv_value($row, $map, 'instrument')));
			$seen_before = isset($seen_tests[$test_key]);
			if ($seen_before) {
				$counts['duplicate_rows']++;
				if ($instrument !== '*' || !empty($seen_tests[$test_key]['wildcard'])) {
					continue;
				}
			}

			$existing = $this->tests->find_by_group_name($group_id, $name);
			if (!$existing && $mode === 'update_only') {
				$counts['skipped']++;
				$seen_tests[$test_key] = array('wildcard' => $instrument === '*', 'imported' => false);
				continue;
			}
			if ($existing && $mode === 'create_merge' && (!$seen_before || empty($seen_tests[$test_key]['imported']))) {
				$seen_tests[$test_key] = array('wildcard' => $instrument === '*', 'imported' => false);
				continue;
			}
			$position++;
			$test = array(
				'te_ID'          => $existing ? (int) $existing['te_ID'] : 0,
				'te_name'        => $name,
				'te_abrev'       => $this->csv_value($row, $map, 'abbreviation'),
				'te_loinc_code'  => $this->csv_value($row, $map, 'loinc'),
				'te_category'    => $this->csv_value($row, $map, 'category'),
				'te_enabled'     => $this->csv_bool($this->csv_value($row, $map, 'enabled', 'yes')),
				'te_is_control'  => $this->csv_bool($this->csv_value($row, $map, 'control')),
				'te_ct_vlow'     => $this->csv_ct_value($row, $map, 'ct_vlow'),
				'te_ct_low'      => $this->csv_ct_value($row, $map, 'ct_low'),
				'te_ct_normal'   => $this->csv_ct_value($row, $map, 'ct_normal'),
				'te_ct_high'     => $this->csv_ct_value($row, $map, 'ct_high'),
				'te_ct_vhigh'    => $this->csv_ct_value($row, $map, 'ct_vhigh'),
				'te_position'    => $position,
			);
			$result = $this->tests->upsert($group_id, $test);
			if (false === $result) {
				$counts['skipped']++;
				continue;
			}
			if (!$seen_before) {
				$counts[$existing ? 'tests_updated' : 'tests_created']++;
			}
			$seen_tests[$test_key] = array(
				'wildcard' => $instrument === '*' || !empty($seen_tests[$test_key]['wildcard']),
				'imported' => true,
			);
		}
		fclose($handle);
		return $counts;
	}

	private function csv_field_key($header)
	{
		$key = strtolower(trim(preg_replace('/\\s+/', ' ', ltrim((string) $header, "\xEF\xBB\xBF"))));
		$aliases = array(
			'group' => array('group', 'panel', 'group name'),
			'group_label' => array('group label', 'label'),
			'subgroup' => array('sub', 'sub group', 'subgroup', 'child group'),
			'test_name' => array('test name', 'name', 'test', 'target name'),
			'abbreviation' => array('abbreviation', 'abbrev', 'abrev'),
			'control' => array('control', 'is control'),
			'loinc' => array('loinc', 'loinc code', 'test code'),
			'category' => array('category'),
			'enabled' => array('enabled', 'active', 'in requisition'),
			'instrument' => array('instrument'),
			'ct_vlow' => array('v.low', 'very low', 'repeat'),
			'ct_low' => array('low'),
			'ct_normal' => array('normal', 'medium'),
			'ct_high' => array('high', 'ct / high'),
			'ct_vhigh' => array('v.high', 'very high', 'very high/amp score'),
		);
		foreach ($aliases as $field => $labels) {
			if (in_array($key, $labels, true)) {
				return $field;
			}
		}
		return '';
	}

	private function csv_value(array $row, array $map, $key, $default = '')
	{
		return isset($map[$key], $row[$map[$key]]) ? sanitize_text_field($row[$map[$key]]) : $default;
	}

	private function csv_ct_value(array $row, array $map, $key)
	{
		$value = $this->csv_value($row, $map, $key);
		return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
	}

	private function csv_bool($value)
	{
		return in_array(strtolower(trim((string) $value)), array('1', 'yes', 'y', 'true', 'on'), true) ? 1 : 0;
	}

	private function resolve_import_group($name, $short_name, $parent_id, $lab_id, $mode, array &$cache, array &$counts)
	{
		$key = (int) $parent_id . ':' . strtolower($name);
		if (isset($cache[$key])) {
			return $cache[$key];
		}
		$existing = $this->panels->find_by_name($lab_id, $name, $parent_id);
		if (!$existing) {
			if ($mode === 'update_only') {
				return 0;
			}
			$id = $this->panels->create(array(
				'tg_lab_ID' => (int) $lab_id,
				'tg_parent' => (int) $parent_id,
				'tg_name' => sanitize_text_field($name),
				'tg_short_name' => sanitize_text_field($short_name),
				'tg_show_req' => 1,
				'tg_position' => 0,
			));
			$counts['groups_created']++;
			$cache[$key] = $id;
			return $id;
		}
		$id = (int) $existing['tg_ID'];
		if ($mode !== 'create_merge' && $short_name !== '') {
			$this->panels->update($id, array('tg_short_name' => sanitize_text_field($short_name)));
			$counts['groups_updated']++;
		}
		$cache[$key] = $id;
		return $id;
	}

	private function import_json_group(array $item, $lab_id, $parent_id, $mode, array &$counts)
	{
		$name = sanitize_text_field($item['tg_name'] ?? '');
		if ($name === '') {
			$counts['skipped']++;
			return 0;
		}
		$existing = $this->panels->find_by_name($lab_id, $name, $parent_id);
		if (!$existing && $mode === 'update_only') {
			$counts['skipped']++;
			return 0;
		}
		if ($existing) {
			$id = (int) $existing['tg_ID'];
			if ($mode !== 'create_merge') {
				$this->panels->update($id, $this->sanitize_import_group($item, $lab_id, $parent_id));
				$counts['groups_updated']++;
			}
		} else {
			$id = $this->panels->create($this->sanitize_import_group($item, $lab_id, $parent_id));
			$counts['groups_created']++;
		}

		$tests = isset($item['tests']) && is_array($item['tests']) ? $item['tests'] : array();
		foreach ($tests as $test) {
			if (!is_array($test) || empty($test['te_name'])) {
				$counts['skipped']++;
				continue;
			}
			$test_name = sanitize_text_field($test['te_name']);
			$test_existing = $this->tests->find_by_group_name($id, $test_name);
			if ((!$test_existing && $mode === 'update_only') || ($test_existing && $mode === 'create_merge')) {
				$counts['skipped']++;
				continue;
			}
			$test['te_ID'] = $test_existing ? (int) $test_existing['te_ID'] : 0;
			$this->tests->upsert($id, $test);
			$counts[$test_existing ? 'tests_updated' : 'tests_created']++;
		}
		$children = isset($item['children']) && is_array($item['children']) ? $item['children'] : array();
		foreach ($children as $child) {
			if (is_array($child)) {
				$this->import_json_group($child, $lab_id, $id, $mode, $counts);
			}
		}
		return $id;
	}

	private function sanitize_import_group(array $item, $lab_id, $parent_id)
	{
		global $wpdb;
		$columns = $wpdb->get_col('SHOW COLUMNS FROM ci_test_groups', 0);
		$skip = array('tg_ID', 'tg_lab_ID', 'tg_parent', 'tg_created', 'tg_modified');
		$data = array(
			'tg_lab_ID' => (int) $lab_id,
			'tg_parent' => (int) $parent_id,
			'tg_name' => sanitize_text_field($item['tg_name'] ?? ''),
		);
		$checkboxes = PanelFields::checkboxes();
		$numeric = array('tg_position', 'tg_limit_days', 'tg_plate_style', 'tg_plate_style2', 'tg_ref_lab_ID');
		foreach ($item as $key => $value) {
			if (!in_array($key, $columns, true) || in_array($key, $skip, true) || $key === 'tg_name') {
				continue;
			}
			if ($key === 'tg_other_options') {
				$opts = PanelRepository::decode_options($value);
				$clean = array();
				foreach ($opts as $option_key => $option_value) {
					if (is_scalar($option_value)) {
						$clean[sanitize_key($option_key)] = sanitize_textarea_field((string) $option_value);
					}
				}
				$data[$key] = $clean;
			} elseif (in_array($key, $checkboxes, true)) {
				$data[$key] = empty($value) ? 0 : 1;
			} elseif (in_array($key, $numeric, true)) {
				$data[$key] = $value === null || $value === '' ? null : absint($value);
			} elseif ($value === null) {
				$data[$key] = null;
			} elseif (is_scalar($value)) {
				$data[$key] = strpos($key, 'comments') !== false || strpos($key, 'popup') !== false ? sanitize_textarea_field((string) $value) : sanitize_text_field((string) $value);
			}
		}
		return $data;
	}

	private function label_tests_with_group(array $tests, array $group)
	{
		foreach ($tests as &$test) {
			$test['tg_group_id'] = (int) $group['tg_ID'];
			$test['tg_group_name'] = $group['tg_name'];
		}
		unset($test);
		return $tests;
	}

	private function save_tests($group_id, array $tests, $lab_id)
	{
		$allowed_group_ids = array((int) $group_id => true);
		$group = $this->panels->find($group_id);
		if ($group && empty($group['tg_parent'])) {
			foreach ($this->panels->children_for_lab($lab_id, $group_id) as $child) {
				$allowed_group_ids[(int) $child['tg_ID']] = true;
			}
		}
		$position = 0;
		foreach ($tests as $key => $item) {
			if (!is_array($item)) {
				continue;
			}
			$is_single_row = isset($item['te_name']);
			$rows = $is_single_row ? array($item) : $item;
			foreach ($rows as $row) {
				if (!is_array($row)) {
					continue;
				}
				$position++;
				if (!isset($row['te_category']) && !$is_single_row && is_string($key)) {
					$row['te_category'] = sanitize_key($key);
				}
				$row['te_position'] = $position;
				if (empty($row['te_name'])) {
					continue;
				}
				$test_group_id = isset($row['tg_group_id']) ? absint($row['tg_group_id']) : (int) $group_id;
				if (!isset($allowed_group_ids[$test_group_id])) {
					continue;
				}
				$this->tests->upsert($test_group_id, $row);
			}
		}
	}

	private function sanitize_group(array $input, $lab_id)
	{
		$checkboxes = PanelFields::checkboxes();
		$out        = array(
			'tg_lab_ID' => (int) $lab_id,
			'tg_name'   => sanitize_text_field($input['tg_name'] ?? ''),
		);

		$text = array(
			'tg_short_name',
			'tg_label_name',
			'tg_popup_approve',
			'tg_popup_verified',
			'tg_bgcolor',
			'tg_pdf_title',
			'tg_logo',
			'tg_cpt_code',
			'tg_date_start',
			'tg_date_end',
		);
		foreach ($text as $key) {
			$out[$key] = sanitize_text_field($input[$key] ?? '');
		}

		$out['tg_comments']      = sanitize_textarea_field($input['tg_comments'] ?? '');
		$out['tg_position']      = absint($input['tg_position'] ?? 0);
		$out['tg_parent']        = absint($input['tg_parent'] ?? 0);
		$out['tg_ref_lab_ID']    = absint($input['tg_ref_lab_ID'] ?? 0);
		$out['tg_limit_days']    = $input['tg_limit_days'] === '' ? null : absint($input['tg_limit_days']);
		$out['tg_plate_style']   = absint($input['tg_plate_style'] ?? 0);
		$out['tg_plate_style2']  = absint($input['tg_plate_style2'] ?? 0);
		$out['tg_relate_to']     = implode(',', Request::csv_ids($input['tg_relate_to'] ?? array()));
		$out['tg_reflex_group_ID'] = implode(',', Request::csv_ids($input['tg_reflex_group_ID'] ?? array()));
		$out['tg_show_cli_IDs']  = sanitize_text_field($input['tg_show_cli_IDs'] ?? '');
		$out['tg_notshow_cli_IDs'] = sanitize_text_field($input['tg_notshow_cli_IDs'] ?? '');

		foreach ($checkboxes as $key) {
			$out[$key] = empty($input[$key]) ? 0 : 1;
		}

		$other_in = isset($input['tg_other_options']) && is_array($input['tg_other_options'])
			? $input['tg_other_options']
			: array();
		$other    = PanelFields::other_defaults();

		foreach (PanelFields::display_options() as $key => $meta) {
			if (empty($meta['other'])) {
				continue;
			}
			$other[$key] = empty($other_in[$key]) ? 0 : 1;
		}

		$other['desc']                = sanitize_text_field($other_in['desc'] ?? '');
		$other['panel_pdf_title']     = sanitize_text_field($other_in['panel_pdf_title'] ?? '');
		$other['rack_name']           = sanitize_text_field($other_in['rack_name'] ?? '');
		$other['rack_name2']          = sanitize_text_field($other_in['rack_name2'] ?? '');
		$other['pdf_small_tbl']       = sanitize_text_field($other_in['pdf_small_tbl'] ?? '');
		$other['default_neg']         = sanitize_text_field($other_in['default_neg'] ?? '');
		$other['new_abx']             = empty($other_in['new_abx']) ? 0 : 1;
		$other['new_abx_title']       = sanitize_text_field($other_in['new_abx_title'] ?? '');
		$other['new_abx_sub_title']   = sanitize_text_field($other_in['new_abx_sub_title'] ?? '');
		$other['abx_type']            = sanitize_text_field($other_in['abx_type'] ?? '');
		$other['auto_approve_clinics'] = empty($other_in['auto_approve_clinics']) ? 0 : 1;
		$other['notify_lab_on_order'] = empty($other_in['notify_lab_on_order']) ? 0 : 1;
		$other['filters']             = is_array($other_in['filters'] ?? null)
			? implode(',', array_map('sanitize_text_field', $other_in['filters']))
			: sanitize_text_field($other_in['filters'] ?? '');
		$other['tg_blood_style']      = sanitize_text_field($input['tg_blood_style'] ?? ($other_in['tg_blood_style'] ?? ''));

		if (isset($input['tg_blood_style'])) {
			$out['tg_other_options'] = $other;
			$other['tg_blood_style'] = sanitize_text_field($input['tg_blood_style']);
		}

		$out['tg_other_options'] = $other;
		$out['tg_blood_style']   = absint($input['tg_blood_style'] ?? 0);

		if (isset($out['tg_blood_style'])) {
			// stored on group as tg_blood_style was in original table; we dropped it, keep in JSON
			$out['tg_other_options']['tg_blood_style'] = $out['tg_blood_style'];
			unset($out['tg_blood_style']);
		}

		return $out;
	}

	private function empty_group($lab_id, $parent_id = 0)
	{
		$group = array(
			'tg_ID'           => 0,
			'tg_lab_ID'       => $lab_id,
			'tg_name'         => '',
			'tg_short_name'   => '',
			'tg_label_name'   => '',
			'tg_position'     => 0,
			'tg_parent'       => (int) $parent_id,
			'tg_relate_to'    => '',
			'tg_show_req'     => 1,
			'tg_hide_pdf_ct'  => 1,
			'tg_other_options' => PanelFields::other_defaults(),
			'tg_show_cli_IDs' => '',
			'tg_notshow_cli_IDs' => '',
			'tg_comments'     => '',
			'tg_pdf_title'    => '',
			'tg_logo'         => '',
			'tg_cpt_code'     => '',
			'tg_ref_lab_ID'   => 0,
			'tg_plate_style'  => '',
			'tg_plate_style2' => '',
			'tg_popup_approve' => '',
			'tg_popup_verified' => '',
			'tg_bgcolor'      => '',
			'tg_date_start'   => '',
			'tg_date_end'     => '',
			'tg_limit_days'   => '',
		);

		foreach (PanelFields::checkboxes() as $key) {
			if (!isset($group[$key])) {
				$group[$key] = 0;
			}
		}

		return $group;
	}

	private function resolve_lab_id()
	{
		if (Roles::is_admin()) {
			$lab_id = Request::int('lab_id', 'get');
			return ($lab_id && $this->access->can_access_lab($lab_id)) ? $lab_id : 0;
		}

		if (Roles::is_lab_user()) {
			return $this->access->current_lab_id();
		}

		return 0;
	}

	private function resolve_lab_id_post()
	{
		if (Roles::is_lab_user()) {
			$lab_id = $this->access->current_lab_id();
		} else {
			$lab_id = absint(Request::post('lab_id', 0));
		}

		if (!$lab_id || (!$this->access->can_access_lab($lab_id) && !Roles::is_admin())) {
			wp_die(esc_html__('Access denied.', 'ci-ignite-abx'));
		}

		return $lab_id;
	}

	private function lab_id_for_panel($id)
	{
		$panel = $this->panels->find($id);
		if (!$panel) {
			wp_die(esc_html__('Panel not found.', 'ci-ignite-abx'));
		}
		if (!$this->access->can_access_lab($panel['tg_lab_ID'])) {
			wp_die(esc_html__('Access denied.', 'ci-ignite-abx'));
		}
		return (int) $panel['tg_lab_ID'];
	}
}
