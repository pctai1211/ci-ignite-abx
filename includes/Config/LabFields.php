<?php

namespace CI\IgniteAbx\Config;

defined('ABSPATH') || exit;

class LabFields
{
	public static function fillable()
	{
		return array(
			'lab_name',
			'lab_dba',
			'lab_dba_init',
			'lab_logo',
			'lab_website',
			'lab_address',
			'lab_city',
			'lab_state',
			'lab_zip',
			'lab_phone',
			'lab_fax',
			'lab_email',
			'lab_npi',
			'lab_tax_id',
			'lab_clia',
			'lab_color',
			'lab_status',
			'lab_medical_director',
			'lab_director',
			'lab_results_footer',
		);
	}

	public static function defaults()
	{
		return array(
			'lab_name'             => '',
			'lab_dba'              => '',
			'lab_dba_init'         => '',
			'lab_logo'             => '',
			'lab_website'          => '',
			'lab_address'          => '',
			'lab_city'             => '',
			'lab_state'            => '',
			'lab_zip'              => '',
			'lab_phone'            => '',
			'lab_fax'              => '',
			'lab_email'            => '',
			'lab_npi'              => '',
			'lab_tax_id'           => '',
			'lab_clia'             => '',
			'lab_color'            => 'D83D00',
			'lab_status'           => 1,
			'lab_medical_director' => '',
			'lab_director'         => '',
			'lab_results_footer'   => '',
		);
	}
}
