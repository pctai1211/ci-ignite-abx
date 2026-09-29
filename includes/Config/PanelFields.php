<?php

namespace CI\IgniteAbx\Config;

defined('ABSPATH') || exit;

class PanelFields
{
	public static function columns()
	{
		return array(
			'tg_name',
			'tg_short_name',
			'tg_label_name',
			'tg_show_req',
			'tg_position',
			'tg_bgcolor',
			'tg_popup_approve',
			'tg_popup_verified',
			'tg_comments',
			'tg_aw_results',
			'tg_only_group',
			'tg_manual_edit',
			'tg_hide_pending',
			'tg_notestcheckbox',
			'tg_parent',
			'tg_relate_to',
			'tg_antibiotic',
			'tg_auto_batchid',
			'tg_auto_approve_neg',
			'tg_auto_approve_pos_neg',
			'tg_auto_position',
			'tg_manual_antibiotic',
			'tg_hide_pdf_ct',
			'tg_show_controls_pe',
			'tg_remove_controls',
			'tg_pos_2_top',
			'tg_hide_phone_address',
			'tg_comment_next_row',
			'tg_logo',
			'tg_pdf_title',
			'tg_show_cli_IDs',
			'tg_notshow_cli_IDs',
			'tg_date_start',
			'tg_date_end',
			'tg_limit_days',
			'tg_plate_style',
			'tg_plate_style2',
			'tg_other_options',
			'tg_reflex_group_ID',
			'tg_lab_ID',
			'tg_ref_lab_ID',
			'tg_cpt_code',
		);
	}

	public static function checkboxes()
	{
		return array(
			'tg_show_req',
			'tg_only_group',
			'tg_manual_edit',
			'tg_notestcheckbox',
			'tg_auto_batchid',
			'tg_auto_approve_neg',
			'tg_auto_approve_pos_neg',
			'tg_manual_antibiotic',
			'tg_auto_position',
			'tg_aw_results',
			'tg_hide_pdf_ct',
			'tg_hide_pending',
			'tg_pos_2_top',
			'tg_hide_phone_address',
			'tg_comment_next_row',
			'tg_remove_controls',
			'tg_show_controls_pe',
			'tg_antibiotic',
		);
	}

	/**
	 * Display / behavior flags stored on the group row or in tg_other_options.
	 * Mirrors the PCR options from the legacy group form.
	 */
	public static function display_options()
	{
		return array(
			'tg_show_req'            => array('label' => 'Show', 'column' => true, 'default' => 1),
			'tg_only_group'          => array('label' => 'Only show Group', 'column' => true),
			'tg_manual_edit'         => array('label' => 'Manual Edit Result', 'column' => true),
			'tg_auto_approve_neg'    => array('label' => 'Auto approve when all Neg', 'column' => true),
			'groupnegs'              => array('label' => 'Group Test name when all Neg', 'other' => true),
			'tg_auto_position'       => array('label' => 'Auto Position when put result', 'column' => true),
			'tg_notestcheckbox'      => array('label' => 'Remove Test Checkbox', 'column' => true),
			'tg_hide_pdf_ct'         => array('label' => 'Hide PDF CT column(Cycle Threshold)(PDF)', 'column' => true, 'default' => 1),
			'tg_pos_2_top'           => array('label' => 'More Positive to top (PDF)', 'column' => true),
			'tg_comment_next_row'    => array('label' => 'Move comments to next row (PDF)', 'column' => true),
			'tg_remove_controls'     => array('label' => 'Hide Controls box(PDF)', 'column' => true),
			'tg_show_controls_pe'    => array('label' => 'Show Patient Extraction (in controls box)(PDF)', 'column' => true),
			'controls_rmpa'          => array('label' => 'Remove Pathogen word (in controls box)(PDF)', 'other' => true),
			'show_value_in_result'   => array('label' => 'Show Value in Result Column(PDF)', 'other' => true),
			'show_units_col'         => array('label' => 'Show Units Column(PDF)', 'other' => true),
			'breakpage'              => array('label' => 'Break page after(PDF)', 'other' => true),
			'split_table'            => array('label' => 'Split Table (one group in 2 tables)(PDF)', 'other' => true),
			'tg_auto_batchid'        => array('label' => 'Auto Current Batch when resulted', 'column' => true),
			'tg_auto_approve_pos_neg'=> array('label' => 'Auto approve when all POS or Neg', 'column' => true),
			'tg_manual_antibiotic'   => array('label' => 'Manual Antibiotics Result(MIC data)', 'column' => true),
			'tg_aw_results'          => array('label' => 'Always Show Result If Imported', 'column' => true),
			'show_qc'                => array('label' => 'Show in Data QC', 'other' => true),
			'tg_hide_pending'        => array('label' => 'Hide in Pending(PDF)', 'column' => true),
			'tg_hide_phone_address'  => array('label' => 'Hide Patient Phone & Address (PDF)', 'column' => true),
			'hide_comments'          => array('label' => 'Hide comments (PDF)', 'other' => true, 'default' => 1),
			'tg_always_show_controls'=> array('label' => 'Always Show Controls box(PDF)', 'other' => true),
			'show_controls_neg'      => array('label' => 'Show Reference Range is negative (in controls box)(PDF)', 'other' => true),
			'show_range'             => array('label' => 'Show Range Column(PDF)', 'other' => true),
			'red_sample_type'        => array('label' => 'Show Red Sample Type(PDF)', 'other' => true),
			'pdf_show_summary'       => array('label' => 'Dont Show Summary(PDF)', 'other' => true),
			'percent_positive'       => array('label' => 'Enable % Positive column', 'other' => true),
			'tg_antibiotic'          => array('label' => 'Antibiotic Resistance', 'column' => true),
		);
	}

	public static function other_defaults()
	{
		return array(
			'desc'                => '',
			'panel_pdf_title'     => '',
			'rack_name'           => '',
			'rack_name2'          => '',
			'hide_comments'       => 1,
			'pdf_small_tbl'       => '',
			'default_neg'         => '',
			'new_abx'             => 0,
			'new_abx_title'       => '',
			'new_abx_sub_title'   => '',
			'abx_type'            => '',
			'auto_approve_clinics'=> 0,
			'notify_lab_on_order' => 1,
			'filters'             => '',
		);
	}

	public static function pdf_styles()
	{
		return array(
			''  => 'Default (#1)',
			'1' => 'Style #1',
			'2' => 'Style #2',
			'4' => 'Style #4',
		);
	}

	public static function plate_styles()
	{
		return array(
			''  => 'None',
			'1' => '96-well',
			'2' => '384-well',
			'3' => 'Custom',
		);
	}

	public static function abx_types()
	{
		return array(
			''      => 'Select Type',
			'uti'   => 'UTI',
			'wound' => 'Wound',
			'gi'    => 'GI',
			'ei'    => 'ENT',
			'nail'  => 'Nail',
			'r'     => 'Respiratory',
			'v'     => 'Vaginitis',
		);
	}

	public static function result_flags()
	{
		return array(
			''  => 'Default Result Flag',
			'1' => 'Default: Neg',
			'2' => 'Default: N/A',
		);
	}

	public static function small_table()
	{
		return array(
			''  => 'table auto width',
			'1' => 'small table in Column 1',
			'2' => 'small table in Column 2',
			'3' => 'table full width',
		);
	}

	public static function categories()
	{
		return array(
			'bacteria'   => 'Bacteria',
			'fungi'      => 'Fungi',
			'resistance' => 'Resistance Genes',
			'viral'      => 'Viral',
		);
	}

	public static function default_targets()
	{
		return array(
			'bacteria' => array(
				array('name' => 'E. coli', 'vlow' => '18', 'low' => '22', 'normal' => '28', 'high' => '33', 'vhigh' => '38', 'enabled' => 1),
				array('name' => 'Klebsiella pneumoniae', 'vlow' => '19', 'low' => '23', 'normal' => '29', 'high' => '34', 'vhigh' => '39', 'enabled' => 1),
				array('name' => 'Proteus mirabilis', 'vlow' => '20', 'low' => '24', 'normal' => '30', 'high' => '35', 'vhigh' => '40', 'enabled' => 1),
				array('name' => 'Enterococcus faecalis', 'vlow' => '', 'low' => '', 'normal' => '', 'high' => '', 'vhigh' => '', 'enabled' => 0),
			),
			'fungi' => array(
				array('name' => 'Candida albicans', 'vlow' => '21', 'low' => '25', 'normal' => '31', 'high' => '36', 'vhigh' => '', 'enabled' => 1),
				array('name' => 'Candida glabrata', 'vlow' => '', 'low' => '', 'normal' => '', 'high' => '', 'vhigh' => '', 'enabled' => 0),
			),
			'resistance' => array(
				array('name' => 'CTX-M', 'vlow' => '22', 'low' => '26', 'normal' => '32', 'high' => '37', 'vhigh' => '', 'enabled' => 1),
				array('name' => 'KPC', 'vlow' => '', 'low' => '', 'normal' => '', 'high' => '', 'vhigh' => '', 'enabled' => 0),
			),
			'viral' => array(
				array('name' => 'Influenza A', 'vlow' => '', 'low' => '', 'normal' => '', 'high' => '', 'vhigh' => '', 'enabled' => 0),
				array('name' => 'SARS-CoV-2', 'vlow' => '', 'low' => '', 'normal' => '', 'high' => '', 'vhigh' => '', 'enabled' => 0),
			),
		);
	}
}
