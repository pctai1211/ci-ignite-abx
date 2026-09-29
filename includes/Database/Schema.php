<?php

namespace CI\IgniteAbx\Database;

defined('ABSPATH') || exit;

class Schema
{
	public static function table($name)
	{
		return $name;
	}

	public static function install()
	{
		$charset = $GLOBALS['wpdb']->get_charset_collate();

		$labs = "CREATE TABLE `ci_labs` (
			`lab_ID` int(10) NOT NULL AUTO_INCREMENT,
			`lab_name` varchar(75) NOT NULL,
			`lab_dba` varchar(75) NOT NULL DEFAULT '',
			`lab_dba_init` varchar(15) NOT NULL DEFAULT '',
			`lab_logo` text,
			`lab_website` varchar(255) NOT NULL DEFAULT '',
			`lab_address` text,
			`lab_city` varchar(100) NOT NULL DEFAULT '',
			`lab_state` varchar(10) NOT NULL DEFAULT '',
			`lab_zip` varchar(12) NOT NULL DEFAULT '',
			`lab_phone` text,
			`lab_fax` text,
			`lab_email` text,
			`lab_npi` varchar(30) NOT NULL DEFAULT '',
			`lab_tax_id` varchar(30) NOT NULL DEFAULT '',
			`lab_clia` varchar(30) NOT NULL DEFAULT '',
			`lab_color` varchar(8) DEFAULT '',
			`lab_status` tinyint(1) NOT NULL DEFAULT 1,
			`lab_medical_director` text,
			`lab_director` text,
			`lab_director_signature` text,
			`lab_results_footer` text,
			`lab_created` datetime DEFAULT NULL,
			`lab_modified` datetime DEFAULT NULL,
			PRIMARY KEY (`lab_ID`),
			KEY `lab_name` (`lab_name`),
			KEY `lab_dba` (`lab_dba`)
		) {$charset};";

		$lab_users = "CREATE TABLE `ci_lab_users` (
			`ID` bigint(20) NOT NULL AUTO_INCREMENT,
			`user_id` bigint(20) unsigned NOT NULL,
			`lab_ID` int(10) NOT NULL,
			`created` datetime DEFAULT NULL,
			PRIMARY KEY (`ID`),
			UNIQUE KEY `user_id` (`user_id`),
			KEY `lab_ID` (`lab_ID`)
		) {$charset};";

		$groups = "CREATE TABLE `ci_test_groups` (
			`tg_ID` int(11) NOT NULL AUTO_INCREMENT,
			`tg_name` varchar(255) NOT NULL,
			`tg_short_name` varchar(50) NOT NULL DEFAULT '',
			`tg_label_name` varchar(5) NOT NULL DEFAULT '',
			`tg_show_req` int(1) DEFAULT 1,
			`tg_position` int(11) DEFAULT 0,
			`tg_show_one` int(1) DEFAULT 0,
			`tg_bgcolor` text,
			`tg_popup_approve` text,
			`tg_popup_verified` text,
			`tg_comments` text,
			`tg_comments_pos` text,
			`tg_comments_neg` text,
			`tg_aw_results` tinyint(1) DEFAULT 0,
			`tg_only_group` tinyint(1) DEFAULT 0,
			`tg_manual_edit` tinyint(1) DEFAULT 0,
			`tg_hide_pending` tinyint(1) DEFAULT 0,
			`tg_notestcheckbox` tinyint(1) DEFAULT 0,
			`tg_parent` int(11) DEFAULT 0,
			`tg_relate_to` varchar(50) DEFAULT '',
			`tg_antibiotic` tinyint(1) DEFAULT 0,
			`tg_auto_batchid` tinyint(1) DEFAULT 0,
			`tg_auto_approve_neg` tinyint(1) DEFAULT 0,
			`tg_auto_approve_pos_neg` tinyint(1) DEFAULT 0,
			`tg_auto_position` tinyint(1) DEFAULT 0,
			`tg_manual_antibiotic` tinyint(1) DEFAULT 0,
			`tg_hide_pdf_ct` tinyint(1) DEFAULT 1,
			`tg_show_controls_pe` tinyint(1) DEFAULT 0,
			`tg_remove_controls` tinyint(1) DEFAULT 0,
			`tg_pos_2_top` tinyint(1) DEFAULT 0,
			`tg_hide_phone_address` tinyint(1) DEFAULT 0,
			`tg_comment_next_row` tinyint(1) DEFAULT 0,
			`tg_hide_pdf_units` tinyint(1) DEFAULT 0,
			`tg_logo` text,
			`tg_pdf_title` text,
			`tg_show_cli_IDs` text,
			`tg_notshow_cli_IDs` text,
			`tg_date_start` date DEFAULT NULL,
			`tg_date_end` date DEFAULT NULL,
			`tg_limit_days` int(3) DEFAULT NULL,
			`tg_plate_style` int(3) DEFAULT NULL,
			`tg_plate_style2` int(3) DEFAULT NULL,
			`tg_group_code` varchar(20) DEFAULT '',
			`tg_other_options` text,
			`tg_reflex_group_ID` varchar(50) DEFAULT '',
			`tg_lab_ID` int(10) NOT NULL DEFAULT 0,
			`tg_ref_lab_ID` int(5) DEFAULT 0,
			`tg_cpt_code` text,
			`tg_created` datetime DEFAULT NULL,
			`tg_modified` datetime DEFAULT NULL,
			PRIMARY KEY (`tg_ID`),
			UNIQUE KEY `tg_name_lab_parent` (`tg_name`,`tg_lab_ID`,`tg_parent`),
			KEY `tg_lab_ID` (`tg_lab_ID`)
		) {$charset};";

		$tests = "CREATE TABLE `ci_test_panels` (
			`te_ID` int(11) NOT NULL AUTO_INCREMENT,
			`te_name` varchar(150) NOT NULL,
			`te_abrev` varchar(100) NOT NULL DEFAULT '',
			`te_abrev2` varchar(100) NOT NULL DEFAULT '',
			`te_loinc_code` text,
			`te_group_id` int(6) NOT NULL DEFAULT 0,
			`te_units` text,
			`te_range` text,
			`te_desc` text,
			`te_position` int(10) DEFAULT 0,
			`te_is_control` tinyint(1) DEFAULT 0,
			`te_hide_pdf` tinyint(1) DEFAULT 0,
			`te_category` varchar(40) NOT NULL DEFAULT '',
			`te_ct_vlow` varchar(10) NOT NULL DEFAULT '',
			`te_ct_low` varchar(10) NOT NULL DEFAULT '',
			`te_ct_normal` varchar(10) NOT NULL DEFAULT '',
			`te_ct_high` varchar(10) NOT NULL DEFAULT '',
			`te_ct_vhigh` varchar(10) NOT NULL DEFAULT '',
			`te_enabled` tinyint(1) NOT NULL DEFAULT 1,
			`te_created` datetime DEFAULT NULL,
			`te_modified` datetime DEFAULT NULL,
			PRIMARY KEY (`te_ID`),
			UNIQUE KEY `te_name_group` (`te_name`,`te_group_id`),
			KEY `te_group_id` (`te_group_id`)
		) {$charset};";

		dbDelta($labs);
		dbDelta($lab_users);
		dbDelta($groups);
		dbDelta($tests);
	}
}
