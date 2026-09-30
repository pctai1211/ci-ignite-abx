<?php

defined('ABSPATH') || exit;

use CI\IgniteAbx\Security\Roles;
use CI\IgniteAbx\Support\View;

$lab_id = isset($lab_id) ? (int) $lab_id : 0;
$panels = isset($panels) ? $panels : array();
$qs     = Roles::is_admin() ? array('lab_id' => $lab_id) : array();
?>
<div class="ci-abx-toolbar">
	<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
		<?php wp_nonce_field('ci_abx_export_panels', 'ci_abx_nonce'); ?>
		<input type="hidden" name="action" value="ci_abx_export_panels" />
		<input type="hidden" name="lab_id" value="<?php echo esc_attr($lab_id); ?>" />
		<button class="ci-btn ci-btn-ghost" type="submit"><?php esc_html_e('Export JSON', 'ci-ignite-abx'); ?></button>
	</form>
	<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
		<?php wp_nonce_field('ci_abx_export_panels', 'ci_abx_nonce'); ?>
		<input type="hidden" name="action" value="ci_abx_export_panels_csv" />
		<input type="hidden" name="lab_id" value="<?php echo esc_attr($lab_id); ?>" />
		<button class="ci-btn ci-btn-ghost" type="submit"><?php esc_html_e('Export CSV', 'ci-ignite-abx'); ?></button>
		<button class="ci-btn ci-btn-ghost" type="submit" name="template" value="1"><?php esc_html_e('CSV Template', 'ci-ignite-abx'); ?></button>
	</form>
	<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data" class="ci-import-form">
		<?php wp_nonce_field('ci_abx_import_panels', 'ci_abx_nonce'); ?>
		<input type="hidden" name="action" value="ci_abx_import_panels" />
		<input type="hidden" name="lab_id" value="<?php echo esc_attr($lab_id); ?>" />
		<select class="ci-import-mode" name="import_mode" aria-label="<?php esc_attr_e('Import mode', 'ci-ignite-abx'); ?>">
			<option value="create_update"><?php esc_html_e('Create & Update', 'ci-ignite-abx'); ?></option>
			<option value="create_merge"><?php esc_html_e('Create & Merge', 'ci-ignite-abx'); ?></option>
			<option value="update_only"><?php esc_html_e('Update Only', 'ci-ignite-abx'); ?></option>
		</select>
		<label class="ci-btn ci-btn-ghost">
			<?php esc_html_e('Choose JSON/CSV', 'ci-ignite-abx'); ?>
			<input type="file" name="import_file" accept=".json,.csv,application/json,text/csv" required hidden />
		</label>
		<button class="ci-btn ci-btn-primary" type="submit"><?php esc_html_e('Import', 'ci-ignite-abx'); ?></button>
	</form>
	<a class="ci-btn ci-btn-primary" href="<?php echo esc_url(View::url('ci-abx-panels', $qs + array('act' => 'add'))); ?>"><?php esc_html_e('+ New Group', 'ci-ignite-abx'); ?></a>
</div>

<section class="ci-card">
	<div class="ci-section-head">
		<div>
			<h2><?php esc_html_e('Groups', 'ci-ignite-abx'); ?></h2>
			<p class="ci-muted"><?php esc_html_e('Parent groups contain child groups; each group owns its tests.', 'ci-ignite-abx'); ?></p>
		</div>
		<span class="ci-badge ci-badge-gray"><?php echo esc_html((string) count($panels)); ?> <?php esc_html_e('parent groups', 'ci-ignite-abx'); ?></span>
	</div>
	<div class="tw-overflow-x-auto">
		<table class="ci-table">
			<thead><tr>
				<th><?php esc_html_e('Group', 'ci-ignite-abx'); ?></th>
				<th><?php esc_html_e('Parent', 'ci-ignite-abx'); ?></th>
				<th><?php esc_html_e('Tests', 'ci-ignite-abx'); ?></th>
				<th><?php esc_html_e('Status', 'ci-ignite-abx'); ?></th>
				<th><?php esc_html_e('Actions', 'ci-ignite-abx'); ?></th>
			</tr></thead>
			<tbody>
			<?php if (empty($panels)) : ?>
				<tr><td colspan="5" class="ci-empty"><?php esc_html_e('No groups yet. Create a parent group to get started.', 'ci-ignite-abx'); ?></td></tr>
			<?php endif; ?>
			<?php foreach ($panels as $group) : ?>
				<?php
				$edit_url  = View::url('ci-abx-panels', $qs + array('act' => 'edit', 'id' => $group['tg_ID']));
				$tests_url = View::url('ci-abx-panels', $qs + array('act' => 'edit', 'id' => $group['tg_ID'], 'tab' => 'tests'));
				$child_url = View::url('ci-abx-panels', $qs + array('act' => 'add', 'parent' => $group['tg_ID']));
				$group_tests = isset($group['tests']) ? count($group['tests']) : 0;
				foreach ($group['children'] as $child_group) {
					$group_tests += isset($child_group['tests']) ? count($child_group['tests']) : 0;
				}
				?>
				<tr class="ci-group-table__parent">
					<td><strong><?php echo esc_html($group['tg_name']); ?></strong><span class="ci-muted"><?php esc_html_e('Parent group', 'ci-ignite-abx'); ?></span></td>
					<td>—</td>
					<td><a href="<?php echo esc_url($tests_url); ?>"><?php echo esc_html((string) $group_tests); ?></a></td>
					<td><span class="ci-badge <?php echo !empty($group['tg_show_req']) ? 'ci-badge-green' : 'ci-badge-gray'; ?>"><?php echo !empty($group['tg_show_req']) ? esc_html__('Active', 'ci-ignite-abx') : esc_html__('Inactive', 'ci-ignite-abx'); ?></span></td>
					<td class="ci-table-actions">
						<a href="<?php echo esc_url($tests_url); ?>"><?php esc_html_e('Manage Tests', 'ci-ignite-abx'); ?></a>
						<a href="<?php echo esc_url($edit_url); ?>"><?php esc_html_e('Edit Group', 'ci-ignite-abx'); ?></a>
						<a href="<?php echo esc_url($child_url); ?>"><?php esc_html_e('Add Child', 'ci-ignite-abx'); ?></a>
					</td>
				</tr>
				<?php foreach ($group['children'] as $child) : ?>
					<?php
					$child_edit_url  = View::url('ci-abx-panels', $qs + array('act' => 'edit', 'id' => $child['tg_ID']));
					$child_tests_url = View::url('ci-abx-panels', $qs + array('act' => 'edit', 'id' => $child['tg_ID'], 'tab' => 'tests'));
					$child_tests     = isset($child['tests']) ? count($child['tests']) : 0;
					?>
					<tr>
						<td><span class="ci-group-table__indent">↳</span><?php echo esc_html($child['tg_name']); ?></td>
						<td><?php echo esc_html($group['tg_name']); ?></td>
						<td><a href="<?php echo esc_url($child_tests_url); ?>"><?php echo esc_html((string) $child_tests); ?></a></td>
						<td><span class="ci-badge <?php echo !empty($child['tg_show_req']) ? 'ci-badge-green' : 'ci-badge-gray'; ?>"><?php echo !empty($child['tg_show_req']) ? esc_html__('Active', 'ci-ignite-abx') : esc_html__('Inactive', 'ci-ignite-abx'); ?></span></td>
						<td class="ci-table-actions"><a href="<?php echo esc_url($child_tests_url); ?>"><?php esc_html_e('Manage Tests', 'ci-ignite-abx'); ?></a><a href="<?php echo esc_url($child_edit_url); ?>"><?php esc_html_e('Edit Group', 'ci-ignite-abx'); ?></a></td>
					</tr>
				<?php endforeach; ?>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</section>
