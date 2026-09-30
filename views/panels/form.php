<?php

defined('ABSPATH') || exit;

use CI\IgniteAbx\Config\PanelFields;
use CI\IgniteAbx\Security\Roles;
use CI\IgniteAbx\Support\View;

$group        = isset($group) ? $group : array();
$lab_id       = isset($lab_id) ? (int) $lab_id : 0;
$parents      = isset($parents) ? $parents : array();
$tests        = isset($tests) ? $tests : array();
$options      = isset($group['tg_other_options']) && is_array($group['tg_other_options']) ? $group['tg_other_options'] : array();
$qs           = Roles::is_admin() ? array('lab_id' => $lab_id) : array();
$tab          = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'settings';
$sub          = isset($_GET['sub']) ? sanitize_key(wp_unslash($_GET['sub'])) : 'basic';
$id           = isset($group['tg_ID']) ? (int) $group['tg_ID'] : 0;
$relate       = array_filter(explode(',', (string) ($group['tg_relate_to'] ?? '')));

$val = static function ($group, $key, $default = '') {
	return isset($group[$key]) ? $group[$key] : $default;
};

$opt = static function ($options, $key, $default = '') {
	return isset($options[$key]) ? $options[$key] : $default;
};
?>
<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="ci-form ci-panel-form" id="ci-panel-form">
	<?php wp_nonce_field('ci_abx_save_panel', 'ci_abx_nonce'); ?>
	<input type="hidden" name="action" value="ci_abx_save_panel" />
	<input type="hidden" name="lab_id" value="<?php echo esc_attr($lab_id); ?>" />
	<input type="hidden" name="group[tg_ID]" value="<?php echo esc_attr($id); ?>" />
	<?php if ($tab === 'tests') : ?><input type="hidden" name="return_tab" value="tests" /><?php endif; ?>
	<div class="tw-flex tw-justify-between tw-gap-5 tw-flex-wrap tw-items-center tw-mb-4">
		<div class="ci-tabs">
			<button type="button" class="ci-tab <?php echo $tab !== 'tests' ? 'is-active' : ''; ?>" data-tab="settings"><?php esc_html_e('Group Settings', 'ci-ignite-abx'); ?></button>
			<button type="button" class="ci-tab <?php echo $tab === 'tests' ? 'is-active' : ''; ?>" data-tab="tests"><?php esc_html_e('Tests', 'ci-ignite-abx'); ?></button>
		</div>
		<div class="ci-form-actions-top">
			<a class="ci-btn ci-btn-ghost" href="<?php echo esc_url(View::url('ci-abx-panels', $qs)); ?>"><?php esc_html_e('Cancel', 'ci-ignite-abx'); ?></a>
			<button type="submit" class="ci-btn ci-btn-primary"><?php esc_html_e('Save Group', 'ci-ignite-abx'); ?></button>
		</div>
	</div>

	<div class="ci-tab-panel" data-panel="settings" <?php echo $tab === 'tests' ? 'hidden' : ''; ?>>
		<div class="ci-subtabs">
			<button type="button" class="ci-subtab <?php echo $sub === 'basic' ? 'is-active' : ''; ?>" data-sub="basic"><?php esc_html_e('Basic Info', 'ci-ignite-abx'); ?></button>
			<button type="button" class="ci-subtab <?php echo $sub === 'pdf' ? 'is-active' : ''; ?>" data-sub="pdf"><?php esc_html_e('PDF & Layout', 'ci-ignite-abx'); ?></button>
			<button type="button" class="ci-subtab <?php echo $sub === 'clinic' ? 'is-active' : ''; ?>" data-sub="clinic"><?php esc_html_e('Clinic Access', 'ci-ignite-abx'); ?></button>
			<button type="button" class="ci-subtab <?php echo $sub === 'abx' ? 'is-active' : ''; ?>" data-sub="abx"><?php esc_html_e('ABX Config', 'ci-ignite-abx'); ?></button>
			<button type="button" class="ci-subtab <?php echo $sub === 'display' ? 'is-active' : ''; ?>" data-sub="display"><?php esc_html_e('Display Options', 'ci-ignite-abx'); ?></button>
		</div>

		<div class="ci-card ci-subpanel" data-subpanel="basic" <?php echo $sub !== 'basic' ? 'hidden' : ''; ?>>
			<h2><?php esc_html_e('Group Information', 'ci-ignite-abx'); ?></h2>
			<p class="ci-muted"><?php esc_html_e('Set the group name, hierarchy, and availability.', 'ci-ignite-abx'); ?></p>
			<div class="ci-grid-3">
				<div>
					<label><?php esc_html_e('Group Name', 'ci-ignite-abx'); ?> *</label>
					<input type="text" name="group[tg_name]" required placeholder="<?php esc_attr_e('Enter group name', 'ci-ignite-abx'); ?>" value="<?php echo esc_attr($val($group, 'tg_name')); ?>" />
				</div>
				<div>
					<label><?php esc_html_e('Short Name', 'ci-ignite-abx'); ?></label>
					<input type="text" name="group[tg_short_name]" value="<?php echo esc_attr($val($group, 'tg_short_name')); ?>" />
				</div>
				<div>
					<label><?php esc_html_e('Description', 'ci-ignite-abx'); ?></label>
					<input type="text" name="group[tg_other_options][desc]" value="<?php echo esc_attr($opt($options, 'desc')); ?>" />
				</div>
				<div>
					<label><?php esc_html_e('Print Label', 'ci-ignite-abx'); ?></label>
					<input type="text" name="group[tg_label_name]" maxlength="5" value="<?php echo esc_attr($val($group, 'tg_label_name')); ?>" />
				</div>
				<div>
					<label><?php esc_html_e('Order Code', 'ci-ignite-abx'); ?></label>
					<input type="text" name="group[tg_cpt_code]" value="<?php echo esc_attr($val($group, 'tg_cpt_code')); ?>" />
				</div>
				<div>
					<label><?php esc_html_e('Position', 'ci-ignite-abx'); ?></label>
					<input type="number" name="group[tg_position]" value="<?php echo esc_attr($val($group, 'tg_position', 0)); ?>" />
				</div>
				<div>
					<label><?php esc_html_e('Parent Group', 'ci-ignite-abx'); ?></label>
					<select name="group[tg_parent]">
						<option value="0"><?php esc_html_e('Top-level group', 'ci-ignite-abx'); ?></option>
						<?php foreach ($parents as $parent) : ?>
							<option value="<?php echo esc_attr($parent->tg_ID); ?>" <?php selected((int) $val($group, 'tg_parent'), (int) $parent->tg_ID); ?>>
								<?php echo esc_html($parent->tg_name); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div>
					<label><?php esc_html_e('Related Groups', 'ci-ignite-abx'); ?></label>
					<select name="group[tg_relate_to][]" multiple class="ci-multiselect">
						<?php foreach ($parents as $parent) : ?>
							<option value="<?php echo esc_attr($parent->tg_ID); ?>" <?php echo in_array((string) $parent->tg_ID, $relate, true) ? 'selected' : ''; ?>>
								<?php echo esc_html($parent->tg_name); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div>
					<label class="ci-check" style="margin-top:1.8rem">
						<input type="hidden" name="group[tg_other_options][reflexed]" value="0" />
						<input type="checkbox" name="group[tg_other_options][reflexed]" value="1" <?php checked(!empty($options['reflexed'])); ?> />
						<?php esc_html_e('Reflexed', 'ci-ignite-abx'); ?>
					</label>
				</div>
				<div>
					<label><?php esc_html_e('Show for Clinics', 'ci-ignite-abx'); ?></label>
					<input type="text" name="group[tg_show_cli_IDs]" placeholder="<?php esc_attr_e('Clinic codes', 'ci-ignite-abx'); ?>" value="<?php echo esc_attr($val($group, 'tg_show_cli_IDs')); ?>" />
				</div>
				<div>
					<label><?php esc_html_e('Do Not Show for Clinics', 'ci-ignite-abx'); ?></label>
					<input type="text" name="group[tg_notshow_cli_IDs]" placeholder="<?php esc_attr_e('Clinic codes to exclude', 'ci-ignite-abx'); ?>" value="<?php echo esc_attr($val($group, 'tg_notshow_cli_IDs')); ?>" />
				</div>
				<div>
					<label><?php esc_html_e('Ref Lab', 'ci-ignite-abx'); ?></label>
					<input type="number" name="group[tg_ref_lab_ID]" min="0" value="<?php echo esc_attr($val($group, 'tg_ref_lab_ID', 0)); ?>" />
				</div>
			</div>
		</div>

		<div class="ci-card ci-subpanel" data-subpanel="pdf" <?php echo $sub !== 'pdf' ? 'hidden' : ''; ?>>
			<h2><?php esc_html_e('PDF & Layout Settings', 'ci-ignite-abx'); ?></h2>
			<div class="ci-grid-2">
				<div>
					<label><?php esc_html_e('Result Top Title (PDF)', 'ci-ignite-abx'); ?></label>
					<input type="text" name="group[tg_pdf_title]" value="<?php echo esc_attr($val($group, 'tg_pdf_title')); ?>" />
				</div>
				<div>
					<label><?php esc_html_e('Group Title (PDF)', 'ci-ignite-abx'); ?></label>
					<input type="text" name="group[tg_other_options][panel_pdf_title]" value="<?php echo esc_attr($opt($options, 'panel_pdf_title')); ?>" />
				</div>
			</div>
			<label><?php esc_html_e('PDF Style', 'ci-ignite-abx'); ?></label>
			<select name="group[tg_blood_style]">
				<?php foreach (PanelFields::pdf_styles() as $k => $label) : ?>
					<option value="<?php echo esc_attr($k); ?>" <?php selected((string) $opt($options, 'tg_blood_style'), (string) $k); ?>><?php echo esc_html($label); ?></option>
				<?php endforeach; ?>
			</select>
			<label><?php esc_html_e('PDF Logo', 'ci-ignite-abx'); ?></label>
			<div class="ci-logo-field">
				<input type="hidden" name="group[tg_logo]" class="ci-logo-input" value="<?php echo esc_attr($val($group, 'tg_logo')); ?>" />
				<button type="button" class="ci-btn ci-btn-ghost ci-logo-pick"><?php esc_html_e('Choose File', 'ci-ignite-abx'); ?></button>
				<div class="ci-logo-preview">
					<?php if (!empty($group['tg_logo'])) : ?>
						<img src="<?php echo esc_url($group['tg_logo']); ?>" alt="" />
					<?php endif; ?>
				</div>
			</div>
			<label><?php esc_html_e('Show this comment when the group is chosen', 'ci-ignite-abx'); ?></label>
			<textarea name="group[tg_comments]" rows="3"><?php echo esc_textarea($val($group, 'tg_comments')); ?></textarea>
			<div class="ci-grid-2">
				<div>
					<label><?php esc_html_e('Plate Layout #1', 'ci-ignite-abx'); ?></label>
					<select name="group[tg_plate_style]">
						<?php foreach (PanelFields::plate_styles() as $k => $label) : ?>
							<option value="<?php echo esc_attr($k); ?>" <?php selected((string) $val($group, 'tg_plate_style'), (string) $k); ?>><?php echo esc_html($label); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div>
					<label><?php esc_html_e('Rack Title #1 (in dropdown)', 'ci-ignite-abx'); ?></label>
					<input type="text" name="group[tg_other_options][rack_name]" value="<?php echo esc_attr($opt($options, 'rack_name')); ?>" />
				</div>
				<div>
					<label><?php esc_html_e('Plate Layout #2', 'ci-ignite-abx'); ?></label>
					<select name="group[tg_plate_style2]">
						<?php foreach (PanelFields::plate_styles() as $k => $label) : ?>
							<option value="<?php echo esc_attr($k); ?>" <?php selected((string) $val($group, 'tg_plate_style2'), (string) $k); ?>><?php echo esc_html($label); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div>
					<label><?php esc_html_e('Rack Title #2 (in dropdown)', 'ci-ignite-abx'); ?></label>
					<input type="text" name="group[tg_other_options][rack_name2]" value="<?php echo esc_attr($opt($options, 'rack_name2')); ?>" />
				</div>
			</div>
		</div>

		<div class="ci-card ci-subpanel" data-subpanel="clinic" <?php echo $sub !== 'clinic' ? 'hidden' : ''; ?>>
			<h2><?php esc_html_e('Clinic Access Control', 'ci-ignite-abx'); ?></h2>
			<p class="ci-muted"><?php esc_html_e('Select which clinics can access this group and its tests.', 'ci-ignite-abx'); ?></p>
			<label><?php esc_html_e('Available Clinics', 'ci-ignite-abx'); ?></label>
			<input type="text" name="group[tg_show_cli_IDs]" value="<?php echo esc_attr($val($group, 'tg_show_cli_IDs')); ?>" placeholder="<?php esc_attr_e('Select clinics… comma-separated codes', 'ci-ignite-abx'); ?>" />
			<div class="ci-info">
				<strong><?php esc_html_e('Access Summary', 'ci-ignite-abx'); ?></strong>
				<p><?php echo empty($group['tg_show_cli_IDs']) ? esc_html__('No clinics selected – this group will not be accessible', 'ci-ignite-abx') : esc_html($group['tg_show_cli_IDs']); ?></p>
			</div>
			<label class="ci-check">
				<input type="hidden" name="group[tg_other_options][auto_approve_clinics]" value="0" />
				<input type="checkbox" name="group[tg_other_options][auto_approve_clinics]" value="1" <?php checked(!empty($options['auto_approve_clinics'])); ?> />
				<?php esc_html_e('Auto-approve requests from selected clinics', 'ci-ignite-abx'); ?>
			</label>
			<label class="ci-check">
				<input type="hidden" name="group[tg_other_options][notify_lab_on_order]" value="0" />
				<input type="checkbox" name="group[tg_other_options][notify_lab_on_order]" value="1" <?php checked(!isset($options['notify_lab_on_order']) || $options['notify_lab_on_order']); ?> />
				<?php esc_html_e('Notify lab when a test in this group is ordered', 'ci-ignite-abx'); ?>
			</label>
		</div>

		<div class="ci-card ci-subpanel" data-subpanel="abx" <?php echo $sub !== 'abx' ? 'hidden' : ''; ?>>
			<h2><?php esc_html_e('ABX Configuration', 'ci-ignite-abx'); ?></h2>
			<p class="ci-muted"><?php esc_html_e('Configure antibiotic resistance testing integration', 'ci-ignite-abx'); ?></p>
			<label class="ci-check">
				<input type="hidden" name="group[tg_other_options][new_abx]" value="0" />
				<input type="checkbox" name="group[tg_other_options][new_abx]" value="1" <?php checked(!empty($options['new_abx'])); ?> />
				<?php esc_html_e('Enable New ABX', 'ci-ignite-abx'); ?>
			</label>
			<div class="ci-grid-2 ci-abx-extra">
				<div>
					<label><?php esc_html_e('New Title', 'ci-ignite-abx'); ?></label>
					<input type="text" name="group[tg_other_options][new_abx_title]" value="<?php echo esc_attr($opt($options, 'new_abx_title')); ?>" />
				</div>
				<div>
					<label><?php esc_html_e('Sub Title', 'ci-ignite-abx'); ?></label>
					<input type="text" name="group[tg_other_options][new_abx_sub_title]" value="<?php echo esc_attr($opt($options, 'new_abx_sub_title')); ?>" />
				</div>
				<div>
					<label><?php esc_html_e('ABX Type', 'ci-ignite-abx'); ?></label>
					<select name="group[tg_other_options][abx_type]">
						<?php foreach (PanelFields::abx_types() as $k => $label) : ?>
							<option value="<?php echo esc_attr($k); ?>" <?php selected((string) $opt($options, 'abx_type'), (string) $k); ?>><?php echo esc_html($label); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</div>

		<div class="ci-card ci-subpanel" data-subpanel="display" <?php echo $sub !== 'display' ? 'hidden' : ''; ?>>
			<h2><?php esc_html_e('Display Options', 'ci-ignite-abx'); ?></h2>
			<p class="ci-muted"><?php esc_html_e('Configure group display and behavior settings.', 'ci-ignite-abx'); ?></p>
			<div class="ci-check-grid">
				<?php foreach (PanelFields::display_options() as $key => $meta) : ?>
					<?php
					$checked = !empty($meta['column']) ? !empty($group[$key]) : !empty($options[$key]);
					if (!isset($group['tg_ID']) && isset($meta['default'])) {
						$checked = (bool) $meta['default'];
					}
					$name = !empty($meta['column']) ? 'group[' . $key . ']' : 'group[tg_other_options][' . $key . ']';
					?>
					<label class="ci-check">
						<input type="hidden" name="<?php echo esc_attr($name); ?>" value="0" />
						<input type="checkbox" name="<?php echo esc_attr($name); ?>" value="1" <?php checked($checked); ?> />
						<?php echo esc_html($meta['label']); ?>
					</label>
				<?php endforeach; ?>
			</div>
			<div class="ci-grid-2" style="margin-top:1.25rem">
				<div>
					<label><?php esc_html_e('Show as Small Table', 'ci-ignite-abx'); ?></label>
					<select name="group[tg_other_options][pdf_small_tbl]">
						<?php foreach (PanelFields::small_table() as $k => $label) : ?>
							<option value="<?php echo esc_attr($k); ?>" <?php selected((string) $opt($options, 'pdf_small_tbl'), (string) $k); ?>><?php echo esc_html($label); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div>
					<label><?php esc_html_e('Default Result Flag', 'ci-ignite-abx'); ?></label>
					<select name="group[tg_other_options][default_neg]">
						<?php foreach (PanelFields::result_flags() as $k => $label) : ?>
							<option value="<?php echo esc_attr($k); ?>" <?php selected((string) $opt($options, 'default_neg'), (string) $k); ?>><?php echo esc_html($label); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</div>
	</div>

	<div class="ci-tab-panel" data-panel="tests" <?php echo $tab === 'tests' ? '' : 'hidden'; ?>>
		<div class="ci-section-head">
			<div>
				<h2 class="tw-my-0"><?php esc_html_e('Tests in this group', 'ci-ignite-abx'); ?></h2>
				<p class="ci-muted"><?php echo empty($group['tg_parent']) ? esc_html__('Includes tests from this parent group and its child groups. New tests are added to the parent group.', 'ci-ignite-abx') : esc_html__('Tests saved here belong to this group. Add a row to create a test.', 'ci-ignite-abx'); ?></p>
			</div>
			<button type="button" class="ci-btn ci-btn-ghost" id="ci-add-test-row" data-group-id="<?php echo esc_attr($group['tg_ID']); ?>" data-group-name="<?php echo esc_attr($group['tg_name']); ?>"><?php esc_html_e('+ Add Test', 'ci-ignite-abx'); ?></button>
		</div>
		<div class="ci-card tw-overflow-x-auto">
			<table class="ci-table ci-test-table">
				<thead>
					<tr>
						<th><?php esc_html_e('Test Name', 'ci-ignite-abx'); ?></th>
						<th><?php esc_html_e('Group', 'ci-ignite-abx'); ?></th>
						<th><?php esc_html_e('Abbreviation', 'ci-ignite-abx'); ?></th>
						<th><?php esc_html_e('Category', 'ci-ignite-abx'); ?></th>
						<th>V.Low</th>
						<th>Low</th>
						<th>Normal</th>
						<th>High</th>
						<th>V.High</th>
						<th><?php esc_html_e('Enabled', 'ci-ignite-abx'); ?></th>
					</tr>
				</thead>
				<tbody id="ci-test-rows">
					<?php foreach ($tests as $i => $test) : ?>
						<tr>
							<td><input type="hidden" name="tests[<?php echo (int) $i; ?>][te_ID]" value="<?php echo esc_attr($test['te_ID']); ?>" /><input type="hidden" name="tests[<?php echo (int) $i; ?>][tg_group_id]" value="<?php echo esc_attr($test['tg_group_id']); ?>" /><input type="text" name="tests[<?php echo (int) $i; ?>][te_name]" value="<?php echo esc_attr($test['te_name']); ?>" /></td>
							<td><?php echo esc_html($test['tg_group_name']); ?></td>
							<td><input type="text" name="tests[<?php echo (int) $i; ?>][te_abrev]" value="<?php echo esc_attr($test['te_abrev']); ?>" /></td>
							<td><input type="text" name="tests[<?php echo (int) $i; ?>][te_category]" value="<?php echo esc_attr($test['te_category']); ?>" /></td>
							<?php foreach (array('vlow', 'low', 'normal', 'high', 'vhigh') as $band) : ?><td><input type="text" class="ci-ct" name="tests[<?php echo (int) $i; ?>][te_ct_<?php echo esc_attr($band); ?>]" value="<?php echo esc_attr($test['te_ct_' . $band]); ?>" /></td><?php endforeach; ?>
							<td><input type="hidden" name="tests[<?php echo (int) $i; ?>][te_enabled]" value="0" /><input type="checkbox" name="tests[<?php echo (int) $i; ?>][te_enabled]" value="1" <?php checked(!empty($test['te_enabled'])); ?> aria-label="<?php esc_attr_e('Enable test', 'ci-ignite-abx'); ?>" /></td>
						</tr>
					<?php endforeach; ?>
					<?php if (empty($tests)) : ?><tr class="ci-test-empty">
							<td colspan="10" class="ci-empty"><?php esc_html_e('This group has no tests yet.', 'ci-ignite-abx'); ?></td>
						</tr><?php endif; ?>
				</tbody>
			</table>
		</div>
		<p><button type="submit" class="ci-btn ci-btn-primary"><?php esc_html_e('Save Tests', 'ci-ignite-abx'); ?></button></p>
	</div>
</form>