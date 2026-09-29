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
$profile_url = get_edit_profile_url($user->ID);
?>
<div class="ci-abx-shell">
	<aside class="ci-abx-sidebar">
		<div class="ci-abx-brand">
			<div class="ci-abx-logo">
				<div>
					<strong>ignite<span>LIS</span></strong>
					<small><?php esc_html_e('DEMO WEBSITE', 'ci-ignite-abx'); ?></small>
				</div>
			</div>
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

		<div class="ci-abx-sidebar__user">
			<div class="ci-abx-avatar"><?php echo esc_html(strtoupper(substr($user->display_name ?: $user->user_login, 0, 2))); ?></div>
			<div>
				<strong><?php echo esc_html($user->display_name ?: $user->user_login); ?></strong>
				<small><?php echo esc_html($role_label); ?></small>
			</div>
		</div>
	</aside>

	<div class="ci-abx-main">
		<header class="ci-abx-topbar">
			<div class="ci-abx-topbar__left">
				<span class="ci-abx-status"><span class="ci-abx-dot"></span> <?php esc_html_e('System Online', 'ci-ignite-abx'); ?></span>
			</div>
			<div class="ci-abx-topbar__right">
				<span class="ci-abx-topbar__who">
					<span class="ci-abx-avatar ci-abx-avatar--sm"><?php echo esc_html(strtoupper(substr($user->display_name ?: $user->user_login, 0, 1))); ?></span>
					<?php echo esc_html($user->display_name ?: $user->user_login); ?>
				</span>
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