<?php

defined('ABSPATH') || exit;

use CI\IgniteAbx\Security\Roles;
use CI\IgniteAbx\Support\View;

$lab               = isset($lab) && is_array($lab) ? $lab : array();
$mode              = isset($mode) ? $mode : 'create';
$is_admin          = !empty($is_admin);
$assignable_users  = isset($assignable_users) ? $assignable_users : array();
$assigned_user_ids = isset($assigned_user_ids) ? array_map('intval', $assigned_user_ids) : array();
$color             = ltrim(isset($lab['lab_color']) ? $lab['lab_color'] : 'D83D00', '#');
$logo              = !empty($lab['lab_logo']) ? $lab['lab_logo'] : '';
?>
<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="ci-form">
	<?php wp_nonce_field('ci_abx_save_lab', 'ci_abx_nonce'); ?>
	<input type="hidden" name="action" value="ci_abx_save_lab" />
	<input type="hidden" name="lab[lab_ID]" value="<?php echo esc_attr($lab['lab_ID'] ?? 0); ?>" />

	<div class="ci-form-actions-top">
		<?php if ($is_admin) : ?>
			<a class="ci-btn ci-btn-ghost" href="<?php echo esc_url(View::url('ci-abx-labs')); ?>"><?php esc_html_e('Back to list', 'ci-ignite-abx'); ?></a>
		<?php endif; ?>
		<?php if (!empty($lab['lab_ID'])) : ?>
			<a class="ci-btn ci-btn-ghost" href="<?php echo esc_url(View::url('ci-abx-panels', Roles::is_admin() ? array('lab_id' => $lab['lab_ID']) : array())); ?>">
				<?php esc_html_e('Panels', 'ci-ignite-abx'); ?>
			</a>
		<?php endif; ?>
		<button type="submit" class="ci-btn ci-btn-primary"><?php echo $mode === 'create' ? esc_html__('Create Lab', 'ci-ignite-abx') : esc_html__('Save Lab', 'ci-ignite-abx'); ?></button>
	</div>

	<div class="ci-pill-row">
		<span class="ci-pill ci-pill-active"><?php esc_html_e('Labs', 'ci-ignite-abx'); ?></span>
	</div>

	<section class="ci-card">
		<h2><?php esc_html_e('Basic Information', 'ci-ignite-abx'); ?></h2>
		<div class="ci-grid-2">
			<div>
				<label><?php esc_html_e('Lab Name', 'ci-ignite-abx'); ?></label>
				<input type="text" name="lab[lab_name]" required value="<?php echo esc_attr($lab['lab_name'] ?? ''); ?>" />

				<label><?php esc_html_e('Lab DBA', 'ci-ignite-abx'); ?></label>
				<input type="text" name="lab[lab_dba]" value="<?php echo esc_attr($lab['lab_dba'] ?? ''); ?>" />

				<label><?php esc_html_e('DBA Initials', 'ci-ignite-abx'); ?></label>
				<input type="text" name="lab[lab_dba_init]" maxlength="15" value="<?php echo esc_attr($lab['lab_dba_init'] ?? ''); ?>" />

				<label><?php esc_html_e('Website', 'ci-ignite-abx'); ?></label>
				<input type="url" name="lab[lab_website]" placeholder="https://example.com" value="<?php echo esc_attr($lab['lab_website'] ?? ''); ?>" />
			</div>
			<div>
				<label><?php esc_html_e('Main Color', 'ci-ignite-abx'); ?></label>
				<div class="ci-color-row">
					<input type="text" class="ci-color-picker" name="lab[lab_color]" value="#<?php echo esc_attr($color); ?>" />
				</div>

				<label><?php esc_html_e('Logo', 'ci-ignite-abx'); ?></label>
				<div class="ci-logo-field">
					<input type="hidden" name="lab[lab_logo]" class="ci-logo-input" value="<?php echo esc_attr($logo); ?>" />
					<button type="button" class="ci-btn ci-btn-ghost ci-logo-pick"><?php esc_html_e('Choose File', 'ci-ignite-abx'); ?></button>
					<div class="ci-logo-preview">
						<?php if ($logo) : ?>
							<img src="<?php echo esc_url($logo); ?>" alt="" />
							<button type="button" class="ci-logo-remove" aria-label="<?php esc_attr_e('Remove', 'ci-ignite-abx'); ?>">&times;</button>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</section>

	<section class="ci-card">
		<h2><?php esc_html_e('Contact Information', 'ci-ignite-abx'); ?></h2>
		<label><?php esc_html_e('Address', 'ci-ignite-abx'); ?></label>
		<input type="text" name="lab[lab_address]" value="<?php echo esc_attr($lab['lab_address'] ?? ''); ?>" />

		<div class="ci-grid-2">
			<div>
				<label><?php esc_html_e('City', 'ci-ignite-abx'); ?></label>
				<input type="text" name="lab[lab_city]" value="<?php echo esc_attr($lab['lab_city'] ?? ''); ?>" />
				<label><?php esc_html_e('State', 'ci-ignite-abx'); ?></label>
				<input type="text" name="lab[lab_state]" maxlength="10" value="<?php echo esc_attr($lab['lab_state'] ?? ''); ?>" />
				<label><?php esc_html_e('Zip', 'ci-ignite-abx'); ?></label>
				<input type="text" name="lab[lab_zip]" value="<?php echo esc_attr($lab['lab_zip'] ?? ''); ?>" />
			</div>
			<div>
				<label><?php esc_html_e('Phone', 'ci-ignite-abx'); ?></label>
				<input type="text" name="lab[lab_phone]" value="<?php echo esc_attr($lab['lab_phone'] ?? ''); ?>" />
				<label><?php esc_html_e('Fax', 'ci-ignite-abx'); ?></label>
				<input type="text" name="lab[lab_fax]" value="<?php echo esc_attr($lab['lab_fax'] ?? ''); ?>" />
				<label><?php esc_html_e('Email', 'ci-ignite-abx'); ?></label>
				<input type="email" name="lab[lab_email]" value="<?php echo esc_attr($lab['lab_email'] ?? ''); ?>" />
			</div>
		</div>
	</section>

	<?php if ($is_admin) : ?>
		<section class="ci-card">
			<h2><?php esc_html_e('Assign users', 'ci-ignite-abx'); ?></h2>
			<p class="ci-muted"><?php esc_html_e('Administrators can attach Lab users to this laboratory. Assigned users receive the Lab role and can edit this lab and its panels.', 'ci-ignite-abx'); ?></p>
			<label><?php esc_html_e('Lab users', 'ci-ignite-abx'); ?></label>
			<select name="lab_users[]" multiple class="ci-multiselect">
				<?php foreach ($assignable_users as $user) : ?>
					<option value="<?php echo esc_attr($user->ID); ?>" <?php selected(in_array((int) $user->ID, $assigned_user_ids, true)); ?>>
						<?php echo esc_html($user->display_name . ' (' . $user->user_login . ')'); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<?php if (empty($assignable_users)) : ?>
				<p class="ci-muted"><?php esc_html_e('No assignable users yet. Create a WordPress user that is not an administrator, then assign them here.', 'ci-ignite-abx'); ?></p>
			<?php endif; ?>
		</section>
	<?php endif; ?>

	<section class="ci-card">
		<h2><?php esc_html_e('Credentials', 'ci-ignite-abx'); ?></h2>
		<div class="ci-grid-3">
			<div>
				<label><?php esc_html_e('NPI', 'ci-ignite-abx'); ?></label>
				<input type="text" name="lab[lab_npi]" value="<?php echo esc_attr($lab['lab_npi'] ?? ''); ?>" />
			</div>
			<div>
				<label><?php esc_html_e('Tax ID', 'ci-ignite-abx'); ?></label>
				<input type="text" name="lab[lab_tax_id]" value="<?php echo esc_attr($lab['lab_tax_id'] ?? ''); ?>" />
			</div>
			<div>
				<label><?php esc_html_e('CLIA', 'ci-ignite-abx'); ?></label>
				<input type="text" name="lab[lab_clia]" value="<?php echo esc_attr($lab['lab_clia'] ?? ''); ?>" />
			</div>
		</div>
		<label><?php esc_html_e('Medical Director', 'ci-ignite-abx'); ?></label>
		<input type="text" name="lab[lab_medical_director]" value="<?php echo esc_attr($lab['lab_medical_director'] ?? ''); ?>" />
		<label><?php esc_html_e('Lab Director', 'ci-ignite-abx'); ?></label>
		<input type="text" name="lab[lab_director]" value="<?php echo esc_attr($lab['lab_director'] ?? ''); ?>" />
		<label><?php esc_html_e('Results Footer', 'ci-ignite-abx'); ?></label>
		<textarea name="lab[lab_results_footer]" rows="3"><?php echo esc_textarea($lab['lab_results_footer'] ?? ''); ?></textarea>
		<label class="ci-check">
			<input type="hidden" name="lab[lab_status]" value="0" />
			<input type="checkbox" name="lab[lab_status]" value="1" <?php checked(!empty($lab['lab_status'])); ?> />
			<?php esc_html_e('Active', 'ci-ignite-abx'); ?>
		</label>
	</section>
</form>
