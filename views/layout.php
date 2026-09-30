<?php

defined('ABSPATH') || exit;

use CI\IgniteAbx\Admin\Shell;
use CI\IgniteAbx\Security\Roles;
use CI\IgniteAbx\Support\Flash;
use CI\IgniteAbx\Support\View;

$flash     = Flash::pull();
$nav       = Shell::nav_items();
$crumbs    = Shell::breadcrumbs();
$user      = wp_get_current_user();
$role_label = Roles::is_admin() ? __('Administrator', 'ci-ignite-abx') : __('Lab Technician', 'ci-ignite-abx');
$display    = $user->display_name ?: $user->user_login;
$profile_url = View::url('ci-abx-profile');
$logout_url  = wp_logout_url(View::url('ci-abx-labs'));
?>
<div class="ci-abx-shell">
	<aside class="ci-abx-sidebar tw-border-r-style-solid">
		<div class="ci-abx-logo">
			<img class="tw-w-full tw-object-cover" src="<?php echo esc_url(CI_ABX_URL . 'assets/images/logo.png'); ?>" alt="<?php esc_attr_e('Ignite Medical Technologies', 'ci-ignite-abx'); ?>" />
		</div>

		<nav class="ci-abx-nav">
			<?php foreach ($nav as $item) : ?>
				<a class="ci-abx-nav__item <?php echo !empty($item['active']) ? 'is-active' : ''; ?>" href="<?php echo esc_url($item['url']); ?>">
					<span class="ci-abx-nav__icon"><?php echo Shell::icon($item['icon']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
													?></span>
					<?php echo esc_html($item['label']); ?>
				</a>
			<?php endforeach; ?>
		</nav>

		<div class="ci-abx-sidebar__user tw-border-t-style-solid">
			<?php echo get_avatar($user->ID, 36, '', esc_attr($display), array('class' => 'ci-abx-avatar-image')); ?>
			<div>
				<strong><?php echo esc_html($user->display_name ?: $user->user_login); ?></strong>
				<small><?php echo esc_html($role_label); ?></small>
			</div>
		</div>
	</aside>

	<div class="ci-abx-main">
		<header class="ci-abx-topbar tw-border-b-style-solid">
			<div class="ci-abx-topbar__left">
				<span class="ci-abx-status"><span class="ci-abx-dot"></span> <?php esc_html_e('System Online', 'ci-ignite-abx'); ?></span>
			</div>
			<div class="ci-abx-topbar__right">
				<div class="ci-abx-user-menu tw-relative" data-user-menu>
					<button class="ci-abx-user-menu__toggle tw-inline-flex tw-cursor-pointer tw-items-center tw-gap-2 tw-rounded tw-border-0 tw-bg-transparent tw-p-1 tw-font-semibold tw-text-sm tw-text-slate-700 hover:tw-text-slate-900" type="button" aria-expanded="false" aria-haspopup="true" aria-controls="ci-abx-user-menu" data-user-menu-toggle>
						<?php echo get_avatar($user->ID, 32, '', esc_attr($display), array('class' => 'ci-abx-avatar-image')); ?>
						<span><?php echo esc_html($display); ?></span>
						<span class="ci-abx-user-menu__chevron" aria-hidden="true">▾</span>
					</button>
					<div class="ci-abx-user-menu__dropdown" id="ci-abx-user-menu" role="menu" hidden>
						<a role="menuitem" href="<?php echo esc_url($profile_url); ?>"><?php esc_html_e('Edit Profile', 'ci-ignite-abx'); ?></a>
						<a role="menuitem" href="<?php echo esc_url($logout_url); ?>"><?php esc_html_e('Log Out', 'ci-ignite-abx'); ?></a>
					</div>
				</div>
			</div>
		</header>

		<div class="ci-abx-page">
			<nav class="ci-abx-crumbs" aria-label="<?php esc_attr_e('Breadcrumb', 'ci-ignite-abx'); ?>">
				<?php foreach ($crumbs as $i => $crumb) : ?>
					<?php if ($i) : ?><span class="ci-abx-crumbs__sep">›</span><?php endif; ?>
					<?php if (!empty($crumb['url']) && $i < count($crumbs) - 1) : ?>
						<a href="<?php echo esc_url($crumb['url']); ?>"><?php echo esc_html($crumb['label']); ?></a>
					<?php else : ?>
						<span><?php echo esc_html($crumb['label']); ?></span>
					<?php endif; ?>
				<?php endforeach; ?>
			</nav>

			<div class="ci-abx-pagehead">
				<div>
					<h1><?php echo esc_html($title); ?></h1>
					<?php if (!empty($subtitle)) : ?>
						<p class="ci-abx-subtitle"><?php echo esc_html($subtitle); ?></p>
					<?php endif; ?>
				</div>
			</div>

			<?php if ($flash) : ?>
				<div class="ci-abx-alert ci-abx-alert--<?php echo esc_attr($flash['type']); ?>">
					<?php echo esc_html($flash['message']); ?>
				</div>
			<?php endif; ?>

			<div class="ci-abx-app">
				<?php View::render($content, isset($data) ? $data : array()); ?>
			</div>
		</div>
	</div>
</div>