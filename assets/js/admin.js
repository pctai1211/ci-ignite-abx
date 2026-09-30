(function ($) {
	'use strict';

	function initColor() {
		if ($.fn.wpColorPicker) {
			$('.ci-color-picker').wpColorPicker();
		}
	}

	function initMedia() {
		$('.ci-abx-app').on('click', '.ci-logo-pick', function (e) {
			e.preventDefault();
			var $wrap = $(this).closest('.ci-logo-field');
			var frame = wp.media({
				title: (window.ciAbxAdmin && ciAbxAdmin.mediaTitle) || 'Choose file',
				button: { text: (window.ciAbxAdmin && ciAbxAdmin.mediaButton) || 'Use this file' },
				multiple: false
			});
			frame.on('select', function () {
				var file = frame.state().get('selection').first().toJSON();
				$wrap.find('.ci-logo-input').val(file.url);
				$wrap.find('.ci-logo-preview').html(
					'<img src="' + file.url + '" alt="" /><button type="button" class="ci-logo-remove">&times;</button>'
				);
			});
			frame.open();
		});

		$('.ci-abx-app').on('click', '.ci-logo-remove', function (e) {
			e.preventDefault();
			var $wrap = $(this).closest('.ci-logo-field');
			$wrap.find('.ci-logo-input').val('');
			$wrap.find('.ci-logo-preview').empty();
		});
	}

	function initTabs() {
		$('.ci-abx-app').on('click', '.ci-tab', function () {
			var tab = $(this).data('tab');
			$('.ci-tab').removeClass('is-active');
			$(this).addClass('is-active');
			$('.ci-tab-panel').attr('hidden', true);
			$('.ci-tab-panel[data-panel="' + tab + '"]').removeAttr('hidden');
		});

		$('.ci-abx-app').on('click', '.ci-subtab', function () {
			var sub = $(this).data('sub');
			$('.ci-subtab').removeClass('is-active');
			$(this).addClass('is-active');
			$('.ci-subpanel').attr('hidden', true);
			$('.ci-subpanel[data-subpanel="' + sub + '"]').removeAttr('hidden');
		});
	}

	function initTestRows() {
		var $rows = $('#ci-test-rows');
		if (!$rows.length) {
			return;
		}

		var nextIndex = $rows.find('tr:not(.ci-test-empty)').length;
		$('#ci-add-test-row').on('click', function () {
			$rows.find('.ci-test-empty').remove();
			var prefix = 'tests[' + nextIndex + ']';
			var $button = $(this);
			var groupId = parseInt($button.attr('data-group-id'), 10) || 0;
			var groupName = $('<div>').text($button.attr('data-group-name') || '').html();
			var html = '<tr>' +
				'<td><input type="hidden" name="' + prefix + '[te_ID]" value="0" /><input type="hidden" name="' + prefix + '[tg_group_id]" value="' + groupId + '" /><input type="text" name="' + prefix + '[te_name]" value="" /></td>' +
				'<td>' + groupName + '</td>' +
				'<td><input type="text" name="' + prefix + '[te_abrev]" value="" /></td>' +
				'<td><input type="text" name="' + prefix + '[te_category]" value="" /></td>' +
				'<td><input type="text" class="ci-ct" name="' + prefix + '[te_ct_vlow]" value="" /></td>' +
				'<td><input type="text" class="ci-ct" name="' + prefix + '[te_ct_low]" value="" /></td>' +
				'<td><input type="text" class="ci-ct" name="' + prefix + '[te_ct_normal]" value="" /></td>' +
				'<td><input type="text" class="ci-ct" name="' + prefix + '[te_ct_high]" value="" /></td>' +
				'<td><input type="text" class="ci-ct" name="' + prefix + '[te_ct_vhigh]" value="" /></td>' +
				'<td><input type="hidden" name="' + prefix + '[te_enabled]" value="0" /><input type="checkbox" name="' + prefix + '[te_enabled]" value="1" checked aria-label="Enable test" /></td>' +
				'</tr>';
			$rows.append(html);
			nextIndex++;
			$rows.find('tr:last input[name$="[te_name]"]').trigger('focus');
		});
	}

	function initUserMenu() {
		var $menus = $('[data-user-menu]');

		$menus.each(function () {
			var $menu = $(this);
			var $toggle = $menu.find('[data-user-menu-toggle]');
			var $dropdown = $menu.find('.ci-abx-user-menu__dropdown');

			function closeMenu(returnFocus) {
				$dropdown.prop('hidden', true);
				$toggle.attr('aria-expanded', 'false');
				if (returnFocus) {
					$toggle.trigger('focus');
				}
			}

			$toggle.on('click', function () {
				var open = $toggle.attr('aria-expanded') === 'true';
				$dropdown.prop('hidden', open);
				$toggle.attr('aria-expanded', open ? 'false' : 'true');
				if (!open) {
					$dropdown.find('[role="menuitem"]').first().trigger('focus');
				}
			});

			$menu.on('keydown', function (e) {
				var $items = $dropdown.find('[role="menuitem"]');
				var index = $items.index(document.activeElement);
				if (e.key === 'Escape') {
					e.preventDefault();
					closeMenu(true);
				} else if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
					e.preventDefault();
					if ($toggle.attr('aria-expanded') !== 'true') {
						$toggle.trigger('click');
					} else {
						index = e.key === 'ArrowDown' ? (index + 1) % $items.length : (index <= 0 ? $items.length - 1 : index - 1);
						$items.eq(index).trigger('focus');
					}
				}
			});

			$(document).on('click.ciAbxUserMenu', function (e) {
				if (!$menu.is(e.target) && !$menu.has(e.target).length) {
					closeMenu(false);
				}
			});
		});
	}

	$(function () {
		initColor();
		initMedia();
		initTabs();
		initTestRows();
		initUserMenu();
	});
})(jQuery);
