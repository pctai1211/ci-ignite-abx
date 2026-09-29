<?php

defined('ABSPATH') || exit;

use CI\IgniteAbx\Config\PanelFields;
use CI\IgniteAbx\Security\Roles;
use CI\IgniteAbx\Support\View;

$lab_id = isset($lab_id) ? (int) $lab_id : 0;
$stats  = isset($stats) ? $stats : array('active_tests' => 0, 'configured' => 0, 'pending' => 0);
$panels = isset($panels) ? $panels : array();
$cats   = PanelFields::categories();
$qs     = Roles::is_admin() ? array('lab_id' => $lab_id) : array();
?>
<div class="ci-abx-toolbar">
	<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
		<?php wp_nonce_field('ci_abx_export_panels', 'ci_abx_nonce'); ?>
		<input type="hidden" name="action" value="ci_abx_export_panels" />
		<input type="hidden" name="lab_id" value="<?php echo esc_attr($lab_id); ?>" />
		<button class="ci-btn ci-btn-ghost" type="submit"><?php esc_html_e('Export All', 'ci-ignite-abx'); ?></button>
	</form>
	<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data" class="ci-import-form">
		<?php wp_nonce_field('ci_abx_import_panels', 'ci_abx_nonce'); ?>
		<input type="hidden" name="action" value="ci_abx_import_panels" />
		<input type="hidden" name="lab_id" value="<?php echo esc_attr($lab_id); ?>" />
		<label class="ci-btn ci-btn-ghost">
			<?php esc_html_e('Import Settings', 'ci-ignite-abx'); ?>
			<input type="file" name="import_file" accept="application/json" onchange="this.form.submit()" hidden />
		</label>
	</form>
</div>

<div class="ci-pill-row">
	<span class="ci-pill ci-pill-active"><?php esc_html_e('PCR', 'ci-ignite-abx'); ?></span>
</div>

<div class="ci-section-head">
	<h2><?php echo esc_html(sprintf(__('Panels %d', 'ci-ignite-abx'), count($panels))); ?></h2>
	<a class="ci-btn ci-btn-primary" href="<?php echo esc_url(View::url('ci-abx-panels', $qs + array('act' => 'add'))); ?>"><?php esc_html_e('+ New Panel', 'ci-ignite-abx'); ?></a>
</div>

<div class="ci-panel-grid">
	<?php if (empty($panels)) : ?>
		<div class="ci-card ci-empty-card"><?php esc_html_e('No panels yet. Create your first PCR panel.', 'ci-ignite-abx'); ?></div>
	<?php endif; ?>
	<?php foreach ($panels as $panel) : ?>
		<?php
		$active = !empty($panel['tg_show_req']);
		$edit   = View::url('ci-abx-panels', $qs + array('act' => 'edit', 'id' => $panel['tg_ID']));
		?>
		<article class="ci-card ci-panel-card">
			<div class="ci-panel-card__top">
				<div>
					<h3><a href="<?php echo esc_url($edit); ?>"><?php echo esc_html($panel['tg_name']); ?></a></h3>
					<div class="ci-chip-row">
						<span class="ci-chip"><?php esc_html_e('PCR', 'ci-ignite-abx'); ?></span>
						<span class="ci-badge <?php echo $active ? 'ci-badge-green' : 'ci-badge-yellow'; ?>">
							<?php echo $active ? esc_html__('Active', 'ci-ignite-abx') : esc_html__('Draft', 'ci-ignite-abx'); ?>
						</span>
					</div>
				</div>
				<a class="ci-icon-btn" href="<?php echo esc_url($edit); ?>" aria-label="<?php esc_attr_e('Edit', 'ci-ignite-abx'); ?>">✎</a>
			</div>
			<p class="ci-muted">
				<?php
				$bits = array();
				foreach ($cats as $key => $label) {
					$count = isset($panel['categories'][$key]['enabled']) ? (int) $panel['categories'][$key]['enabled'] : 0;
					if ($count) {
						$bits[] = $label . ' ' . $count;
					}
				}
				echo esc_html($bits ? implode('  ·  ', $bits) : __('No targets enabled', 'ci-ignite-abx'));
				?>
			</p>
			<p class="ci-muted"><?php echo esc_html($panel['tg_comments'] ? wp_trim_words($panel['tg_comments'], 12) : ''); ?></p>
			<details class="ci-targets">
				<summary><?php esc_html_e('Show targets', 'ci-ignite-abx'); ?></summary>
				<ul>
					<?php foreach ($panel['tests'] as $test) : ?>
						<?php if (empty($test['te_enabled'])) { continue; } ?>
						<li><?php echo esc_html($test['te_name']); ?></li>
					<?php endforeach; ?>
				</ul>
			</details>
		</article>
	<?php endforeach; ?>
</div>

<section class="ci-card">
	<h2><?php esc_html_e('Tests · PCR', 'ci-ignite-abx'); ?></h2>
	<p class="ci-muted"><?php esc_html_e('Create and configure laboratory tests', 'ci-ignite-abx'); ?></p>
	<div class="ci-stat-grid">
		<div class="ci-stat ci-stat-blue">
			<div><?php esc_html_e('Active Tests', 'ci-ignite-abx'); ?></div>
			<strong><?php echo esc_html((string) $stats['active_tests']); ?></strong>
		</div>
		<div class="ci-stat ci-stat-green">
			<div><?php esc_html_e('Configured', 'ci-ignite-abx'); ?></div>
			<strong><?php echo esc_html((string) $stats['configured']); ?></strong>
		</div>
		<div class="ci-stat ci-stat-orange">
			<div><?php esc_html_e('Pending', 'ci-ignite-abx'); ?></div>
			<strong><?php echo esc_html((string) $stats['pending']); ?></strong>
		</div>
	</div>
</section>
