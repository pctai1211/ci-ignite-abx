<?php

namespace CI\IgniteAbx\Admin;

use CI\IgniteAbx\Support\Flash;
use CI\IgniteAbx\Support\View;

defined('ABSPATH') || exit;

class ProfilePage
{
	public function render()
	{
		if (!is_user_logged_in() || !current_user_can('read')) {
			wp_die(esc_html__('You do not have permission to edit this profile.', 'ci-ignite-abx'));
		}

		$user = wp_get_current_user();
		View::render('layout', array(
			'title'    => __('Edit Profile', 'ci-ignite-abx'),
			'subtitle' => __('Manage your account details and password.', 'ci-ignite-abx'),
			'content'  => 'profile/form',
			'data'     => array('user' => $user),
		));
	}

	public function handle_save()
	{
		if (!is_user_logged_in() || !current_user_can('read')) {
			wp_die(esc_html__('You do not have permission to edit this profile.', 'ci-ignite-abx'));
		}

		$nonce = isset($_POST['ci_abx_nonce']) && is_string($_POST['ci_abx_nonce'])
			? sanitize_text_field(wp_unslash($_POST['ci_abx_nonce']))
			: '';
		if (!$nonce || !wp_verify_nonce($nonce, 'ci_abx_save_profile')) {
			wp_die(esc_html__('Invalid security token.', 'ci-ignite-abx'));
		}

		$user_id = get_current_user_id();
		if (!current_user_can('edit_user', $user_id)) {
			wp_die(esc_html__('You are not allowed to edit this account.', 'ci-ignite-abx'));
		}

		$profile = isset($_POST['profile']) && is_array($_POST['profile']) ? wp_unslash($_POST['profile']) : array();
		foreach (array('first_name', 'last_name', 'display_name', 'user_email', 'pass1', 'pass2') as $key) {
			if (isset($profile[$key]) && !is_scalar($profile[$key])) {
				Flash::add_error(__('Invalid profile data submitted.', 'ci-ignite-abx'));
				wp_safe_redirect(View::url('ci-abx-profile'));
				exit;
			}
		}
		$email   = sanitize_email((string) ($profile['user_email'] ?? ''));
		$display = sanitize_text_field((string) ($profile['display_name'] ?? ''));
		$pass1   = (string) ($profile['pass1'] ?? '');
		$pass2   = (string) ($profile['pass2'] ?? '');

		if (!$email || !is_email($email)) {
			Flash::add_error(__('Enter a valid email address.', 'ci-ignite-abx'));
			wp_safe_redirect(View::url('ci-abx-profile'));
			exit;
		}
		if ($display === '') {
			Flash::add_error(__('Display name is required.', 'ci-ignite-abx'));
			wp_safe_redirect(View::url('ci-abx-profile'));
			exit;
		}
		$existing_email = email_exists($email);
		if ($existing_email && (int) $existing_email !== $user_id) {
			Flash::add_error(__('That email address is already in use.', 'ci-ignite-abx'));
			wp_safe_redirect(View::url('ci-abx-profile'));
			exit;
		}
		if ($pass1 !== $pass2) {
			Flash::add_error(__('The new passwords do not match.', 'ci-ignite-abx'));
			wp_safe_redirect(View::url('ci-abx-profile'));
			exit;
		}

		$userdata = array(
			'ID'           => $user_id,
			'first_name'   => sanitize_text_field($profile['first_name'] ?? ''),
			'last_name'    => sanitize_text_field($profile['last_name'] ?? ''),
			'display_name' => $display,
			'user_email'   => $email,
		);
		if ($pass1 !== '') {
			$userdata['user_pass'] = $pass1;
		}

		$result = wp_update_user($userdata);
		if (is_wp_error($result)) {
			Flash::add_error($result->get_error_message());
		} else {
			Flash::add(__('Profile updated.', 'ci-ignite-abx'));
		}

		wp_safe_redirect(View::url('ci-abx-profile'));
		exit;
	}
}
