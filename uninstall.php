<?php

defined('WP_UNINSTALL_PLUGIN') || exit;

global $wpdb;

$tables = array(
	$wpdb->prefix . 'ci_test_panels',
	$wpdb->prefix . 'ci_test_groups',
	$wpdb->prefix . 'ci_lab_users',
	$wpdb->prefix . 'ci_labs',
);

foreach ($tables as $table) {
	$wpdb->query("DROP TABLE IF EXISTS {$table}");
}

delete_option('ci_abx_db_version');
delete_option('ci_abx_flash');

$role = get_role('ci_lab');
if ($role) {
	remove_role('ci_lab');
}

$admin = get_role('administrator');
if ($admin) {
	$admin->remove_cap('ci_abx_manage_labs');
	$admin->remove_cap('ci_abx_manage_panels');
}
