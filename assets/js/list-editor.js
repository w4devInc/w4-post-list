/**
 * @package W4_Post_List
 * @author Shazzad Hossain Khan
 * @url https://w4dev.com/plugins/w4-post-list/
**/

(function ($) {

	var w4plEditors = {};
	var w4plRefreshTimer = null;

	/*
	 * CodeMirror for the template/css/js textareas. Re-runs after every AJAX
	 * form refresh, because the whole form DOM (and any editor in it) is
	 * replaced by w4pl_get_form().
	 */
	function w4pl_init_code_editors() {
		w4plEditors = {};

		if (!window.wp || !wp.codeEditor) {
			return;
		}

		var configs = [
			{ id: 'w4pl_template', mode: 'text/html' },
			{ id: 'w4pl_css', mode: 'css' },
			{ id: 'w4pl_js', mode: 'javascript' }
		];

		$.each(configs, function (i, cfg) {
			var el = document.getElementById(cfg.id);
			if (!el) {
				return;
			}

			var settings = wp.codeEditor.defaultSettings ? $.extend(true, {}, wp.codeEditor.defaultSettings) : {};
			settings.codemirror = $.extend({}, settings.codemirror, {
				indentUnit: 2,
				tabSize: 2,
				lineNumbers: true,
				mode: cfg.mode,
				lint: false
			});

			var editor = wp.codeEditor.initialize(el, settings);
			if (editor && editor.codemirror) {
				w4plEditors[cfg.id] = editor.codemirror;
			}
		});
	}

	/* Push editor contents back into their textareas (serialize reads those). */
	function w4pl_sync_code_editors() {
		$.each(w4plEditors, function (id, cm) {
			cm.save();
		});
	}

	/*
	 * CodeMirror measures its container at init time; editors created inside a
	 * hidden tab pane render garbled until re-measured. Call whenever a pane
	 * becomes visible.
	 */
	function w4pl_refresh_code_editors() {
		$.each(w4plEditors, function (id, cm) {
			cm.refresh();
		});
	}

	$(document).on('w4pl/form_loaded', function (el) {
		w4pl_init_code_editors();
		w4pl_adjust_height();

		/*
		 * A just-applied starter template renders a marker notice; open the
		 * tab it points at (the Template section) so the copied markup is
		 * immediately visible instead of hiding behind the tab the user
		 * happened to be on.
		 */
		var marker = $('#w4pl_list_options .w4pl-starter-applied[data-show-tab]').first();
		if (marker.length) {
			var target = $('#' + marker.data('show-tab'));
			if (target.length) {
				$('.w4pl_field_group').removeClass('w4pl_active');
				target.addClass('w4pl_active');
				$('#w4pl_tab_id').val(marker.data('show-tab'));
			}
		}

		setTimeout(function () {
			w4pl_refresh_code_editors();
			w4pl_adjust_height();
		}, 200);
		$('#w4pl_orderby').trigger('change');
	});

	$(document).ready(function () {
		$(document).trigger('w4pl/form_loaded', $('#w4pl_list_options'));
	});

	$(window).on('resize', function () {
		w4pl_adjust_height();
	});

	$(document.body).on('change', '.w4pl_field_compare', function () {
		if (-1 !== $.inArray($(this).val(), ['IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN'])) {
			$(this)
				.parent('td').next('td').children('.item').show()
				.children('.button').show();
		} else {
			$(this)
				.parent('td').next('td').children('.item').hide()
				.children('.button').hide();

			$(this)
				.parent('td').next('td').find('.item:first').show();
		}
	});

	$(document.body).on('click', '.w4pl_clone_parent', function (e) {
		var clone = $(this).parent('.wpce_parent_item').clone();
		var that = $(this);
		that.parent('.wpce_parent_item').after(clone);
		$(this).parent('.wpce_parent_item').parent().find('.w4pl_remove_parent').show();
		w4pl_adjust_height();
		return false;
	});

	$(document.body).on('click', '.w4pl_remove_parent', function (e) {
		var that = $(this);

		if (that.parent('.wpce_parent_item').siblings().length == 0) {
			that.hide();
			return false;
		} else {
			$('.w4pl_remove_parent').show();
		}
		that.parent('.wpce_parent_item').remove();
		w4pl_adjust_height();
		return false;
	});


	/* onchange post type, refresh the form (debounced so multi-checkbox bursts cost one request) */
	$(document.body).on('change', '.w4pl_onchange_lfr', function () {
		var id = $(this).parents('.w4pl_field_group').attr('id');

		clearTimeout(w4plRefreshTimer);
		w4plRefreshTimer = setTimeout(function () {
			w4pl_get_form(null, id);
		}, 300);
	});

	/* onclick button, display hidden elements */
	$(document.body).on('click', '.w4pl_toggler', function () {
		$($(this).data('target')).toggle();
		w4pl_adjust_height();
		return false;
	});

	/* onchange orderby, toggle meta input */
	$(document.body).on('change', '#w4pl_orderby', function () {
		if ('meta_value' == $(this).val() || 'meta_value_num' == $(this).val()) {
			$('#orderby_meta_key_wrap').show();
		}
		else {
			$('#orderby_meta_key_wrap').hide();
		}
	});
	/* show/hide group options */
	$(document.body).on('click', '.w4pl_group_title', function () {
		$('#w4pl_list_options').height('auto');
		$('.w4pl_field_group').removeClass('w4pl_active');
		$(this).parent('.w4pl_field_group').addClass('w4pl_active');

		$('#w4pl_tab_id').val($(this).parent('.w4pl_field_group').attr('id'));

		/* The revealed pane may hold editors initialized while hidden. */
		w4pl_refresh_code_editors();
		w4pl_adjust_height();

		return false;
	});
	/* put selected element code at pointer */
	$(document.body).on('click', '#w4pl_template_buttons a', function (e) {
		insertAtCaret('w4pl_template', $(this).data('code'));
		return false;
	});

	// Adjust form height
	function w4pl_adjust_height() {
		var miHeight = $('.w4pl_active .w4pl_group_fields').outerHeight();
		$('#w4pl_list_options').css('minHeight', miHeight);
	}

	function w4pl_get_form(data, showTab) {
		var publish = $('#publish');

		publish.addClass('disabled').prop('disabled', true);

		if (showTab === null || typeof showTab === 'undefined') {
			showTab = 'w4pl_field_group_type';
		}

		/* Editors hold the live values; textareas must match before serialize. */
		w4pl_sync_code_editors();

		if (data === null) {
			data = $('#w4pl_list_options :input').serialize() + '&action=w4pl_list_edit_form_html';
		}

		$('#w4pl_list_options .w4pl-refresh-error').remove();
		$('#w4pl_list_options').append('<div id="w4pl_lo"></div>');

		$.post(ajaxurl, data, function (r) {
			$('#w4pl_list_options').replaceWith(r);

			$('#' + showTab).addClass('w4pl_active');

			$(document).trigger('w4pl/form_loaded', $('#w4pl_list_options'));

			publish.removeClass('disabled').prop('disabled', false);
		}).fail(function () {
			/* The old form is still in the DOM; drop the overlay and let the user retry. */
			$('#w4pl_lo').remove();
			publish.removeClass('disabled').prop('disabled', false);

			/*
			 * A picked starter template was never applied (the refresh died),
			 * so clear the selection - otherwise a later save would apply it
			 * over the user's manual edits, contradicting the message below.
			 */
			$('#w4pl_list_options select[name="w4pl[starter_template]"]').val('');

			var message = (window.w4plListEditor && window.w4plListEditor.refreshFailed) ?
				window.w4plListEditor.refreshFailed :
				'Could not refresh the form. Your entries are unchanged - check your connection and try again.';

			$('#w4pl_list_options').prepend(
				$('<div class="notice notice-error inline w4pl-refresh-error" role="alert"></div>')
					.append($('<p></p>').text(message))
			);
		});
	}

	/* "Any" post status is exclusive of concrete statuses. */
	$(document.body).on('change', 'input[name="w4pl[post_status][]"]', function () {
		var boxes = $('input[name="w4pl[post_status][]"]');

		if ('any' === $(this).val() && this.checked) {
			boxes.not(this).prop('checked', false);
		} else if (this.checked) {
			boxes.filter('[value="any"]').prop('checked', false);
		}
	});

	/* One-click recovery from a template/list-type mismatch. */
	$(document.body).on('click', '#w4pl_use_default_template', function () {
		var tpl = $(this).data('template') || '';

		if (w4plEditors.w4pl_template) {
			w4plEditors.w4pl_template.setValue(tpl);
			w4plEditors.w4pl_template.save();
		} else {
			$('#w4pl_template').val(tpl);
		}

		$(this).closest('.notice').remove();
		return false;
	});

	/* ----- Live preview ----- */

	function w4pl_l10n(key, fallback) {
		if (window.w4plListEditor && window.w4plListEditor[key]) {
			return window.w4plListEditor[key];
		}
		return fallback;
	}

	function w4pl_refresh_preview() {
		var pane = $('#w4pl_preview_pane');
		var status = $('#w4pl_preview_status');

		if (!pane.length || !pane.is(':visible')) {
			return;
		}

		w4pl_sync_code_editors();
		status.text(w4pl_l10n('previewLoading', 'Rendering preview…'));

		var data = $('#w4pl_list_options :input').serialize()
			+ '&action=w4pl_list_preview'
			+ '&nonce=' + encodeURIComponent(w4pl_l10n('previewNonce', ''));

		$.post(ajaxurl, data, function (r) {
			if (r && r.success && r.data && typeof r.data.html === 'string') {
				var frame = document.getElementById('w4pl_preview_frame');
				frame.srcdoc = '<!doctype html><html><head><meta charset="utf-8">'
					+ '<style>body{margin:16px;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;color:#1e1e1e;}img{max-width:100%;height:auto;}</style>'
					+ '</head><body>' + r.data.html + '</body></html>';
				status.text('');
			} else {
				status.text((r && r.data && r.data.message) ? r.data.message : w4pl_l10n('previewFailed', 'Preview failed.'));
			}
		}).fail(function () {
			status.text(w4pl_l10n('previewFailed', 'Preview failed — check your connection and try again.'));
		});
	}

	$(document.body).on('click', '#w4pl_preview_toggle', function () {
		var pane = $('#w4pl_preview_pane');
		var open = pane.is(':visible');

		if (open) {
			pane.hide();
			$(this).attr('aria-expanded', 'false').text(w4pl_l10n('previewShow', 'Preview'));
		} else {
			pane.show();
			$(this).attr('aria-expanded', 'true').text(w4pl_l10n('previewHide', 'Hide preview'));
			w4pl_refresh_preview();
		}
		return false;
	});

	/* Keep an open preview in sync after each successful form refresh. */
	$(document).on('w4pl/form_loaded', function () {
		w4pl_refresh_preview();
	});

	/*
	 * Insert a template tag at the cursor - into the CodeMirror instance when
	 * one owns the field, or the plain textarea otherwise.
	 */
	function insertAtCaret(areaId, text) {
		if (w4plEditors[areaId]) {
			w4plEditors[areaId].replaceSelection(text);
			w4plEditors[areaId].focus();
			return;
		}

		var txtarea = document.getElementById(areaId);
		if (!txtarea) {
			return;
		}

		var scrollPos = txtarea.scrollTop;
		var start = txtarea.selectionStart || 0;
		var end = txtarea.selectionEnd || start;

		txtarea.value = txtarea.value.substring(0, start) + text + txtarea.value.substring(end);
		txtarea.selectionStart = start + text.length;
		txtarea.selectionEnd = start + text.length;
		txtarea.focus();
		txtarea.scrollTop = scrollPos;
	}


	$(document).ready(function () {
		$(document.body).on('click', '#w4pl_meta_query_add_btn', function () {
			var h = $($('#w4pl_meta_query_clone tbody').html());
			h.appendTo('#w4pl_meta_query_table tbody');
			reindex_meta_query();
			w4pl_adjust_height();
			return false;
		});
		$(document.body).on('click', '.w4pl_meta_query_remove_btn', function () {
			$(this).parents('tr').remove();
			reindex_meta_query();
			w4pl_adjust_height();
			return false;
		});

		function reindex_meta_query() {
			$('#w4pl_meta_query_table tbody tr').each(function (index, elem) {
				var h = $(elem);
				h.find('.wffi_w4pl_meta_query_key')
					.attr('name', 'w4pl[meta_query][key][' + index + ']');
				h.find('.wffi_w4pl_meta_query_compare')
					.attr('name', 'w4pl[meta_query][compare][' + index + ']');
				h.find('.wffi_w4pl_meta_query_value')
					.attr('name', 'w4pl[meta_query][value][' + index + '][]');
				h.find('td.values')
					.attr('data-pos', index);

				$(this).replaceWith(h);
			});
		}

		$(document.body).on('click', '.w4pl_meta_query_value_add', function () {
			$('.w4pl_meta_query_value_del').show();

			var td = $(this).parent('.item').parent('td');
			var that = $(this);

			that.parent('.item').after($('#w4pl_meta_query_value_clone').html());
			reindex_value(td);
			w4pl_adjust_height();
			return false;
		});
		$(document.body).on('click', '.w4pl_meta_query_value_del', function () {
			$('.w4pl_meta_query_value_del').show();

			var td = $(this).parent('.item').parent('td');
			var that = $(this);

			if (td.children('.item').length == 1) {
				$(this).hide();
				return false;
			}

			that.parent('.item').remove();
			reindex_value(td);
			w4pl_adjust_height();
			return false;
		});

		$(document.body).on('change', '.w4pl_meta_query_compare', function () {
			if ($.inArray($(this).val(), ['IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN']) != -1) {
				$(this)
					.parent('td').next('td').children('.item').show()
					.children('.w4pl_meta_query_value_add, .w4pl_meta_query_value_del').show();
			}
			else {
				$(this)
					.parent('td').next('td').children('.item').hide()
					.children('.w4pl_meta_query_value_add, .w4pl_meta_query_value_del').hide();

				$(this)
					.parent('td').next('td').find('.item:first').show();
			}
		});

		$('.w4pl_meta_query_compare').each(function (i, elem) {
			$(this).trigger('change');
		});

		function reindex_value(td) {
			var siblings = td.children('.item');
			var pos = td.data('pos');
			siblings.each(function (index, elem) {
				var h = $(elem);
				h.find('input')
					.attr('name', 'w4pl[meta_query][value][' + pos + '][' + index + ']');
				$(this).replaceWith(h);
			});
		}
	});

	$(document).ready(function () {
		$(document.body).on('click', '#w4pl_tax_query_add_btn', function () {
			var h = $($('#w4pl_tax_query_clone tbody').html());
			h.appendTo('#w4pl_tax_query_table tbody');
			reindex_tax_query();
			w4pl_adjust_height();
			return false;
		});

		$(document.body).on('click', '.w4pl_tax_query_remove_btn', function () {
			$(this).parents('tr').remove();
			reindex_tax_query();
			w4pl_adjust_height();
			return false;
		});

		function reindex_tax_query() {
			$('#w4pl_tax_query_table tbody tr').each(function (index, elem) {
				var h = $(elem);
				h.find('.wffi_w4pl_tax_query_taxonomy')
					.attr('name', 'w4pl[tax_query][taxonomy][' + index + ']')
					.removeAttr('id');
				h.find('.wffi_w4pl_tax_query_field')
					.attr('name', 'w4pl[tax_query][field][' + index + ']')
					.removeAttr('id');
				h.find('.wffi_w4pl_tax_query_operator')
					.attr('name', 'w4pl[tax_query][operator][' + index + ']')
					.removeAttr('id');
				h.find('.wffi_w4pl_tax_query_terms')
					.attr('name', 'w4pl[tax_query][terms][' + index + '][]')
					.removeAttr('id');
				h.find('td.terms')
					.attr('data-pos', index);

				$(this).replaceWith(h);
			});
		}

		$(document.body).on('click', '.w4pl_tax_query_value_add', function () {
			$('.w4pl_tax_query_value_del').show();

			var td = $(this).parent('.item').parent('td');
			var that = $(this);

			that.parent('.item').after($('#w4pl_tax_query_value_clone').html());
			reindex_value(td);
			w4pl_adjust_height();
			return false;
		});
		$(document.body).on('click', '.w4pl_tax_query_value_del', function () {
			$('.w4pl_tax_query_value_del').show();

			var td = $(this).parent('.item').parent('td');
			var that = $(this);

			if (td.children('.item').length == 1) {
				$(this).hide();
				return false;
			}

			that.parent('.item').remove();
			reindex_value(td);
			w4pl_adjust_height();
			return false;
		});

		$(document.body).on('change', '.w4pl_tax_query_operator', function () {

			if ($.inArray($(this).val(), ['IN', 'NOT IN']) != -1) {
				$(this)
					.parents('tr').find('.w4pl_tax_query_terms_cell').children('.item').show()
					.children('.w4pl_tax_query_value_add, .w4pl_tax_query_value_del').show();
			}
			else {
				$(this)
					.parents('tr').find('.w4pl_tax_query_terms_cell .item').hide()
					.children('.w4pl_tax_query_value_add, .w4pl_tax_query_value_del').hide();

				$(this)
					.parents('tr').find('.w4pl_tax_query_terms_cell .item:first').show();
			}
		});

		$('.w4pl_tax_query_operator').each(function (i, elem) {
			$(this).trigger('change');
		});

		function reindex_value(td) {
			var siblings = td.children('.item');
			var pos = td.data('pos');
			siblings.each(function (index, elem) {
				var h = $(elem);
				h.find('input')
					.attr('name', 'w4pl[tax_query][terms][' + pos + '][' + index + ']');
				$(this).replaceWith(h);
			});
		}
	});
})(jQuery);
