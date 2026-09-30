<?php

defined('ABSPATH') || exit;

use CI\IgniteAbx\Support\View;

$user = isset($user) ? $user : wp_get_current_user();
?>
<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="ci-form ci-profile-form">
	<?php wp_nonce_field('ci_abx_save_profile', 'ci_abx_nonce'); ?>
	<input type="hidden" name="action" value="ci_abx_save_profile" />

	<section class="ci-card">
		<div class="ci-profile-heading">
			<?php echo get_avatar($user->ID, 64, '', esc_attr($user->display_name), array('class' => 'ci-profile-avatar')); ?>
			<div><h2><?php esc_html_e('Profile Information', 'ci-ignite-abx'); ?></h2><p class="ci-muted"><?php esc_html_e('Your profile image is managed by WordPress Gravatar.', 'ci-ignite-abx'); ?></p></div>
		</div>
		<div class="ci-grid-2">
			<div><label for="ci-first-name"><?php esc_html_e('First name', 'ci-ignite-abx'); ?></label><input id="ci-first-name" type="text" name="profile[first_name]" value="<?php echo esc_attr($user->first_name); ?>" autocomplete="given-name" /></div>
			<div><label for="ci-last-name"><?php esc_html_e('Last name', 'ci-ignite-abx'); ?></label><input id="ci-last-name" type="text" name="profile[last_name]" value="<?php echo esc_attr($user->last_name); ?>" autocomplete="family-name" /></div>
			<div><label for="ci-display-name"><?php esc_html_e('Display name', 'ci-ignite-abx'); ?></label><input id="ci-display-name" type="text" name="profile[display_name]" value="<?php echo esc_attr($user->display_name); ?>" required autocomplete="nickname" /></div>
			<div><label for="ci-user-email"><?php esc_html_e('Email', 'ci-ignite-abx'); ?></label><input id="ci-user-email" type="email" name="profile[user_email]" value="<?php echo esc_attr($user->user_email); ?>" required autocomplete="email" /></div>
		</div>
	</section>

	<section class="ci-card">
		<h2><?php esc_html_e('Change Password', 'ci-ignite-abx'); ?></h2>
		<p class="ci-muted"><?php esc_html_e('Leave both password fields blank to keep your current password.', 'ci-ignite-abx'); ?></p>
		<div class="ci-grid-2">
			<div><label for="ci-pass1"><?php esc_html_e('New password', 'ci-ignite-abx'); ?></label><input id="ci-pass1" type="password" name="profile[pass1]" autocomplete="new-password" /></div>
			<div><label for="ci-pass2"><?php esc_html_e('Confirm new password', 'ci-ignite-abx'); ?></label><input id="ci-pass2" type="password" name="profile[pass2]" autocomplete="new-password" /></div>
		</div>
	</section>

	<div class="ci-form-actions-top"><button type="submit" class="ci-btn ci-btn-primary"><?php esc_html_e('Save Profile', 'ci-ignite-abx'); ?></button></div>
</form>
