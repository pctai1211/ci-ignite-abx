<?php

defined('ABSPATH') || exit;

use CI\IgniteAbx\Support\View;
?>
<div class="ci-abx-toolbar">
	<a class="ci-btn ci-btn-primary" href="<?php echo esc_url(View::url('ci-abx-labs', array('act' => 'new'))); ?>"><?php esc_html_e('+ New Lab', 'ci-ignite-abx'); ?></a>
</div>

<div class="ci-card">
	<table class="ci-table">
		<thead>
			<tr>
				<th><?php esc_html_e('Lab', 'ci-ignite-abx'); ?></th>
				<th><?php esc_html_e('DBA', 'ci-ignite-abx'); ?></th>
				<th><?php esc_html_e('City', 'ci-ignite-abx'); ?></th>
				<th><?php esc_html_e('Status', 'ci-ignite-abx'); ?></th>
				<th><?php esc_html_e('Users', 'ci-ignite-abx'); ?></th>
				<th><?php esc_html_e('Panels', 'ci-ignite-abx'); ?></th>
				<th><?php esc_html_e('Actions', 'ci-ignite-abx'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if (empty($labs)) { ?>
				<tr>
					<td colspan="7" class="ci-empty"><?php esc_html_e('No labs yet.', 'ci-ignite-abx'); ?></td>
				</tr>
			<?php } else { ?>
				<?php foreach ($labs as $lab) : ?>
					<tr>
						<td>
							<strong><?php echo esc_html($lab['lab_name']); ?></strong>
							<div class="ci-muted"><?php echo esc_html($lab['lab_dba_init']); ?></div>
						</td>
						<td><?php echo esc_html($lab['lab_dba']); ?></td>
						<td><?php echo esc_html(trim($lab['lab_city'] . ', ' . $lab['lab_state'], ', ')); ?></td>
						<td>
							<span class="ci-badge <?php echo !empty($lab['lab_status']) ? 'ci-badge-green' : 'ci-badge-gray'; ?>">
								<?php echo !empty($lab['lab_status']) ? esc_html__('Active', 'ci-ignite-abx') : esc_html__('Inactive', 'ci-ignite-abx'); ?>
							</span>
						</td>
						<td><?php echo esc_html(!empty($lab['users']) ? implode(', ', $lab['users']) : '—'); ?></td>
						<td>
							<a class="ci-link" href="<?php echo esc_url(View::url('ci-abx-panels', array('lab_id' => $lab['lab_ID']))); ?>">
								<?php echo esc_html(sprintf(__('Panels (%d)', 'ci-ignite-abx'), $lab['panel_count'])); ?>
							</a>
						</td>
						<td class="ci-actions">
							<a class="ci-btn ci-btn-ghost" href="<?php echo esc_url(View::url('ci-abx-labs', array('lab_id' => $lab['lab_ID']))); ?>"><?php esc_html_e('Edit', 'ci-ignite-abx'); ?></a>
							<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('<?php echo esc_js(__('Delete this lab and its panels?', 'ci-ignite-abx')); ?>');">
								<?php wp_nonce_field('ci_abx_delete_lab', 'ci_abx_nonce'); ?>
								<input type="hidden" name="action" value="ci_abx_delete_lab" />
								<input type="hidden" name="lab_ID" value="<?php echo esc_attr($lab['lab_ID']); ?>" />
								<button class="ci-btn ci-btn-danger-ghost" type="submit"><?php esc_html_e('Delete', 'ci-ignite-abx'); ?></button>
							</form>
						</td>
					</tr>
			<?php endforeach;
			} ?>
		</tbody>
	</table>
</div>