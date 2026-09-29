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

	$(function () {
		initColor();
		initMedia();
		initTabs();
	});
})(jQuery);
