<?php

defined('ABSPATH') || exit;

use CI\IgniteAbx\Support\View;

$is_admin = !empty($is_admin);
?>
<div class="ci-card ci-empty-card">
	<?php if ($is_admin) : ?>
		<p><?php esc_html_e('Open a lab from the Labs list, then use the Panels link for that laboratory.', 'ci-ignite-abx'); ?></p>
	<?php else : ?>
		<p><?php esc_html_e('Create a lab first to manage PCR panels.', 'ci-ignite-abx'); ?></p>
	<?php endif; ?>
	<a class="ci-btn ci-btn-primary" href="<?php echo esc_url(View::url('ci-abx-labs')); ?>"><?php esc_html_e('Go to Labs', 'ci-ignite-abx'); ?></a>
</div>
