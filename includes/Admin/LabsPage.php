<?php

namespace CI\IgniteAbx\Admin;

use CI\IgniteAbx\Config\LabFields;
use CI\IgniteAbx\Repositories\LabRepository;
use CI\IgniteAbx\Repositories\LabUserRepository;
use CI\IgniteAbx\Repositories\PanelRepository;
use CI\IgniteAbx\Security\Access;
use CI\IgniteAbx\Security\Roles;
use CI\IgniteAbx\Support\Flash;
use CI\IgniteAbx\Support\Request;
use CI\IgniteAbx\Support\View;

defined('ABSPATH') || exit;

class LabsPage
{
	/** @var LabRepository */
	private $labs;

	/** @var LabUserRepository */
	private $lab_users;

	/** @var PanelRepository */
	private $panels;

	/** @var Access */
	private $access;

	public function __construct()
	{
		$this->labs      = new LabRepository();
		$this->lab_users = new LabUserRepository();
		$this->panels    = new PanelRepository();
		$this->access    = new Access();
	}

	public function render()
	{
		$this->access->require_labs();

		if (Roles::is_admin()) {
			if (sanitize_key(Request::get('act', '')) === 'new') {
				View::render('layout', array(
					'title'    => __('Add Lab', 'ci-ignite-abx'),
					'subtitle' => __('Create a laboratory profile', 'ci-ignite-abx'),
					'content'  => 'labs/form',
					'data'     => $this->form_data(LabFields::defaults(), 'create', true),
				));
				return;
			}

			$edit_id = Request::int('lab_id', 'get');
			if ($edit_id) {
				$lab = $this->labs->find($edit_id);
				if (!$lab) {
					wp_die(esc_html__('Lab not found.', 'ci-ignite-abx'));
				}
				View::render('layout', array(
					'title'    => __('Laboratory Settings', 'ci-ignite-abx'),
					'subtitle' => __('Manage laboratory profiles, reporting, and reference labs', 'ci-ignite-abx'),
					'content'  => 'labs/form',
					'data'     => $this->form_data($lab, 'edit', true),
				));
				return;
			}

			$labs = $this->labs->all();
			foreach ($labs as &$lab) {
				$lab['panel_count'] = $this->panels->count_for_lab($lab['lab_ID']);
				$lab['users']       = $this->lab_users->names_for_lab($lab['lab_ID']);
			}
			unset($lab);

			View::render('layout', array(
				'title'    => __('Labs', 'ci-ignite-abx'),
				'subtitle' => __('Manage laboratory profiles', 'ci-ignite-abx'),
				'content'  => 'labs/list',
				'data'     => array('labs' => $labs),
			));
			return;
		}

		$lab_id = $this->access->current_lab_id();
		if ($lab_id) {
			$lab = $this->labs->find($lab_id);
			View::render('layout', array(
				'title'    => __('Laboratory Settings', 'ci-ignite-abx'),
				'subtitle' => __('Manage laboratory profiles, reporting, and reference labs', 'ci-ignite-abx'),
				'content'  => 'labs/form',
				'data'     => $this->form_data($lab ?: LabFields::defaults(), 'edit', false),
			));
			return;
		}

		View::render('layout', array(
			'title'    => __('Create Lab', 'ci-ignite-abx'),
			'subtitle' => __('Your account is not linked to a laboratory yet.', 'ci-ignite-abx'),
			'content'  => 'labs/form',
			'data'     => $this->form_data(LabFields::defaults(), 'create', false),
		));
	}

	public function handle_save()
	{
		$this->access->require_labs();

		if (!Request::verify_nonce('ci_abx_save_lab')) {
			wp_die(esc_html__('Invalid security token.', 'ci-ignite-abx'));
		}

		$payload = isset($_POST['lab']) && is_array($_POST['lab']) ? wp_unslash($_POST['lab']) : array();
		$data    = $this->sanitize($payload);
		$lab_id  = isset($payload['lab_ID']) ? absint($payload['lab_ID']) : 0;

		if ($data['lab_name'] === '') {
			Flash::add_error(__('Lab name is required.', 'ci-ignite-abx'));
			wp_safe_redirect(wp_get_referer() ?: View::url('ci-abx-labs'));
			exit;
		}

		if (Roles::is_lab_user()) {
			$owned = $this->access->current_lab_id();
			if ($owned && $lab_id !== $owned) {
				wp_die(esc_html__('You can only edit your own lab.', 'ci-ignite-abx'));
			}
			if ($owned) {
				$lab_id = $owned;
			}
		}

		if ($lab_id) {
			if (!$this->access->can_access_lab($lab_id) && !Roles::is_admin()) {
				wp_die(esc_html__('Access denied.', 'ci-ignite-abx'));
			}
			$this->labs->update($lab_id, $data);
			Flash::add(__('Lab updated.', 'ci-ignite-abx'));
		} else {
			if (Roles::is_lab_user() && $this->access->current_lab_id()) {
				Flash::add_error(__('You already have a lab.', 'ci-ignite-abx'));
				wp_safe_redirect(View::url('ci-abx-labs'));
				exit;
			}
			$lab_id = $this->labs->create($data);
			if (Roles::is_lab_user()) {
				$this->lab_users->attach(get_current_user_id(), $lab_id);
			}
			Flash::add(__('Lab created.', 'ci-ignite-abx'));
		}

		if (Roles::is_admin()) {
			$assigned = isset($_POST['lab_users']) ? array_map('absint', (array) wp_unslash($_POST['lab_users'])) : array();
			$this->lab_users->sync($lab_id, $assigned);
		}

		$redirect = Roles::is_admin()
			? View::url('ci-abx-labs', array('lab_id' => $lab_id))
			: View::url('ci-abx-labs');
		wp_safe_redirect($redirect);
		exit;
	}

	public function handle_delete()
	{
		$this->access->require_labs();

		if (!Roles::is_admin()) {
			wp_die(esc_html__('Only administrators can delete labs.', 'ci-ignite-abx'));
		}

		if (!Request::verify_nonce('ci_abx_delete_lab')) {
			wp_die(esc_html__('Invalid security token.', 'ci-ignite-abx'));
		}

		$lab_id = Request::int('lab_ID', 'post');
		if ($lab_id) {
			$this->labs->delete($lab_id);
			Flash::add(__('Lab deleted.', 'ci-ignite-abx'));
		}

		wp_safe_redirect(View::url('ci-abx-labs'));
		exit;
	}

	private function sanitize(array $input)
	{
		$out = LabFields::defaults();
		foreach (LabFields::fillable() as $key) {
			if (!isset($input[$key])) {
				continue;
			}
			$value = $input[$key];
			if (in_array($key, array('lab_address', 'lab_results_footer'), true)) {
				$out[$key] = sanitize_textarea_field($value);
			} elseif ($key === 'lab_email') {
				$out[$key] = sanitize_email($value);
			} elseif ($key === 'lab_website') {
				$out[$key] = esc_url_raw($value);
			} elseif ($key === 'lab_status') {
				$out[$key] = $value ? 1 : 0;
			} elseif ($key === 'lab_color') {
				$out[$key] = sanitize_hex_color('#' . ltrim((string) $value, '#')) ?: '';
				$out[$key] = ltrim($out[$key], '#');
			} else {
				$out[$key] = sanitize_text_field($value);
			}
		}
		return $out;
	}

	private function form_data(array $lab, $mode, $is_admin)
	{
		$data = array(
			'lab'      => $lab,
			'mode'     => $mode,
			'is_admin' => $is_admin,
		);

		if ($is_admin) {
			$data['assignable_users'] = LabUserRepository::assignable_users();
			$data['assigned_user_ids'] = !empty($lab['lab_ID'])
				? $this->lab_users->user_ids_for_lab($lab['lab_ID'])
				: array();
		}

		return $data;
	}
}
