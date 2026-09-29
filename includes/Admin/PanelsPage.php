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
				'title'    => __('Panels', 'ci-ignite-abx'),
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
			$panel['categories'] = $this->tests->counts_by_category($panel['tg_ID']);
			$panel['tests']      = $this->tests->for_group($panel['tg_ID']);
			$panel['options']    = PanelRepository::decode_options($panel['tg_other_options']);
		}
		unset($panel);

		View::render('layout', array(
			'title'    => __('Test Panel Configuration', 'ci-ignite-abx'),
			'subtitle' => sprintf(
				/* translators: %s lab name */
				__('Manage PCR panels for %s', 'ci-ignite-abx'),
				$lab ? $lab['lab_name'] : ''
			),
			'content'  => 'panels/list',
			'data'     => array(
				'lab'    => $lab,
				'lab_id' => $lab_id,
				'panels' => $panels,
				'stats'  => $this->tests->stats_for_lab($lab_id),
			),
		));
	}

	private function render_form($lab_id, $act, $id)
	{
		if ($act === 'edit') {
			$group = $this->panels->find($id);
			if (!$group || (int) $group['tg_lab_ID'] !== (int) $lab_id) {
				wp_die(esc_html__('Panel not found.', 'ci-ignite-abx'));
			}
			$tests = $this->tests->for_group($id);
		} else {
			$group = $this->empty_group($lab_id);
			$tests = array();
		}

		$tests_by_cat = $this->merge_catalog($tests);

		View::render('layout', array(
			'title'    => $act === 'add' ? __('Add New Molecular Panel', 'ci-ignite-abx') : __('Edit Molecular Panel', 'ci-ignite-abx'),
			'subtitle' => __('Create a new molecular test panel with custom configuration', 'ci-ignite-abx'),
			'content'  => 'panels/form',
			'data'     => array(
				'lab_id'       => $lab_id,
				'group'        => $group,
				'parents'      => $this->panels->parents_for_lab($lab_id, $id),
				'tests_by_cat' => $tests_by_cat,
				'categories'   => PanelFields::categories(),
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

		if (!empty($_POST['tests']) && is_array($_POST['tests'])) {
			$this->save_tests($id, wp_unslash($_POST['tests']));
		}

		$args = array('act' => 'edit', 'id' => $id);
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
		$this->save_tests($id, $tests);
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
			$panel['tg_other_options'] = PanelRepository::decode_options($panel['tg_other_options']);
			$panel['tests']            = $this->tests->for_group($panel['tg_ID']);
			$export[]                  = $panel;
		}

		nocache_headers();
		header('Content-Type: application/json; charset=utf-8');
		header('Content-Disposition: attachment; filename=ci-abx-panels-lab-' . $lab_id . '.json');
		echo wp_json_encode($export, JSON_PRETTY_PRINT);
		exit;
	}

	public function handle_import()
	{
		$this->access->require_panels();

		if (!Request::verify_nonce('ci_abx_import_panels')) {
			wp_die(esc_html__('Invalid security token.', 'ci-ignite-abx'));
		}

		$lab_id = $this->resolve_lab_id_post();
		if (empty($_FILES['import_file']['tmp_name'])) {
			Flash::add_error(__('No file uploaded.', 'ci-ignite-abx'));
			wp_safe_redirect(wp_get_referer());
			exit;
		}

		$raw  = file_get_contents($_FILES['import_file']['tmp_name']);
		$list = json_decode($raw, true);
		if (!is_array($list)) {
			Flash::add_error(__('Invalid JSON file.', 'ci-ignite-abx'));
			wp_safe_redirect(wp_get_referer());
			exit;
		}

		foreach ($list as $item) {
			if (empty($item['tg_name'])) {
				continue;
			}
			$item['tg_lab_ID'] = $lab_id;
			$tests = isset($item['tests']) && is_array($item['tests']) ? $item['tests'] : array();
			unset($item['tg_ID'], $item['tests'], $item['categories'], $item['options']);
			$id = $this->panels->create($item);
			foreach ($tests as $test) {
				unset($test['te_ID']);
				$test['te_group_id'] = $id;
				$this->tests->upsert($id, $test);
			}
		}

		Flash::add(__('Panels imported.', 'ci-ignite-abx'));
		$args = Roles::is_admin() ? array('lab_id' => $lab_id) : array();
		wp_safe_redirect(View::url('ci-abx-panels', $args));
		exit;
	}

	private function save_tests($group_id, array $tests)
	{
		$position = 0;
		foreach ($tests as $category => $rows) {
			if (!is_array($rows)) {
				continue;
			}
			foreach ($rows as $row) {
				$position++;
				$row['te_category'] = sanitize_key($category);
				$row['te_position'] = $position;
				if (empty($row['te_name'])) {
					continue;
				}
				$this->tests->upsert($group_id, $row);
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
		$other['auto_approve_clinics']= empty($other_in['auto_approve_clinics']) ? 0 : 1;
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

	private function empty_group($lab_id)
	{
		$group = array(
			'tg_ID'           => 0,
			'tg_lab_ID'       => $lab_id,
			'tg_name'         => '',
			'tg_short_name'   => '',
			'tg_label_name'   => '',
			'tg_position'     => 0,
			'tg_parent'       => 0,
			'tg_relate_to'    => '',
			'tg_show_req'     => 1,
			'tg_hide_pdf_ct'  => 1,
			'tg_other_options'=> PanelFields::other_defaults(),
			'tg_show_cli_IDs' => '',
			'tg_notshow_cli_IDs' => '',
			'tg_comments'     => '',
			'tg_pdf_title'    => '',
			'tg_logo'         => '',
			'tg_cpt_code'     => '',
			'tg_ref_lab_ID'   => 0,
			'tg_plate_style'  => '',
			'tg_plate_style2' => '',
			'tg_popup_approve'=> '',
			'tg_popup_verified'=> '',
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

	private function merge_catalog(array $existing)
	{
		$by_name = array();
		foreach ($existing as $row) {
			$cat = $row['te_category'] ?: 'bacteria';
			$by_name[$cat][$row['te_name']] = $row;
		}

		$out = array();
		foreach (PanelFields::default_targets() as $cat => $rows) {
			$out[$cat] = array();
			foreach ($rows as $def) {
				if (isset($by_name[$cat][$def['name']])) {
					$out[$cat][] = $by_name[$cat][$def['name']];
					unset($by_name[$cat][$def['name']]);
				} else {
					$out[$cat][] = array(
						'te_ID'       => 0,
						'te_name'     => $def['name'],
						'te_enabled'  => $def['enabled'],
						'te_ct_vlow'  => $def['vlow'],
						'te_ct_low'   => $def['low'],
						'te_ct_normal'=> $def['normal'],
						'te_ct_high'  => $def['high'],
						'te_ct_vhigh' => $def['vhigh'],
						'te_category' => $cat,
					);
				}
			}
			if (!empty($by_name[$cat])) {
				foreach ($by_name[$cat] as $extra) {
					$out[$cat][] = $extra;
				}
			}
		}

		return $out;
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
