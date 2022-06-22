/**
 * @package W4_Post_List
 * @author Shazzad Hossain Khan
 * @url https://w4dev.com/plugins/w4-post-list/
**/

(function ($) {

	$(document).on('w4pl/form_loaded', function (el) {
		w4pl_adjust_height();
		$('#w4pl_orderby').trigger('change');
	});

	$(document).ready(function () {
		$(document).trigger('w4pl/form_loaded', $('#w4pl_list_options'));

		// var editorSettings = wp.codeEditor.defaultSettings ? _.clone(wp.codeEditor.defaultSettings) : {};
		// editorSettings.codemirror = _.extend(
		// 	{},
		// 	editorSettings.codemirror,
		// 	{
		// 		indentUnit: 2,
		// 		tabSize: 2,
		// 		lineNumbers: false
		// 	}
		// );
		// wp.codeEditor.initialize($('#w4pl_template'), editorSettings);
		// editorSettings.codemirror.mode = 'css';
		// wp.codeEditor.initialize($('#w4pl_css'), editorSettings);
		// editorSettings.codemirror.mode = 'javascript';
		// wp.codeEditor.initialize($('#w4pl_js'), editorSettings);
	});

	$(document.body).on('change', '.w4pl_field_compare', function () {
		if (-1 !== $.inArray($(this).val(), ['IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN'])) {
			//console.log($(this).val());
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
		return false;
	});


	/* onchange post type, refresh the form */
	$(document.body).on('change', '.w4pl_onchange_lfr', function () {
		var id = $(this).parents('.w4pl_field_group').attr('id');
		// console.log( id );
		w4pl_get_form(null, id);
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
		/* onchange post type, refresh the form */
		$('#publish').addClass('disabled');

		if (showTab === null) {
			showTab = 'w4pl_field_group_type';
		}
		if (data === null) {
			var data = $('#w4pl_list_options :input').serialize() + '&action=w4pl_list_edit_form_html';
		}

		$('#w4pl_list_options').append('<div id="w4pl_lo"></div>');
		//return false;

		$.post(ajaxurl, data, function (r) {
			$('#w4pl_list_options').replaceWith(r);

			$('#' + showTab).addClass('w4pl_active');

			$(document).trigger('w4pl/form_loaded', $('#w4pl_list_options'));

			// $('.wffwi_w4pl_post_type .spinner').css('display', 'none');
			$('#publish').removeClass('disabled');

			return false;
		})
	}

	/*
	 * Similar feature as tinymce quicktag button
	 * This function helps to place shortcode right at the cursor position
	*/
	function insertAtCaret(areaId, text) {
		var txtarea = document.getElementById(areaId);
		var scrollPos = txtarea.scrollTop;
		var strPos = 0;
		var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ?
			"ff" : (document.selection ? "ie" : false));
		if (br == "ie") {
			txtarea.focus();
			var range = document.selection.createRange();
			range.moveStart('character', -txtarea.value.length);
			strPos = range.text.length;
		}
		else if (br == "ff") strPos = txtarea.selectionStart;

		var front = (txtarea.value).substring(0, strPos);
		var back = (txtarea.value).substring(strPos, txtarea.value.length);
		txtarea.value = front + text + back;
		strPos = strPos + text.length;
		if (br == "ie") {
			txtarea.focus();
			var range = document.selection.createRange();
			range.moveStart('character', -txtarea.value.length);
			range.moveStart('character', strPos);
			range.moveEnd('character', 0);
			range.select();
		}
		else if (br == "ff") {
			txtarea.selectionStart = strPos;
			txtarea.selectionEnd = strPos;
			txtarea.focus();
		}
		txtarea.scrollTop = scrollPos;
	}


	$(document).ready(function () {
		$(document.body).on('click', '#w4pl_meta_query_add_btn', function () {
			var h = $($('#w4pl_meta_query_clone tbody').html());
			h.appendTo('#w4pl_meta_query_table tbody');
			reindex_meta_query();
			return false;
		});
		$(document.body).on('click', '.w4pl_meta_query_remove_btn', function () {
			$(this).parents('tr').remove();
			reindex_meta_query();
			return false;
		});

		function reindex_meta_query() {
			$('#w4pl_meta_query_table tbody tr').each(function (index, elem) {
				//console.log(index);
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
			return false;
		});

		$(document.body).on('change', '.w4pl_meta_query_compare', function () {
			if ($.inArray($(this).val(), ['IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN']) != -1) {
				//console.log($(this).val());
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
				//console.log(index);
				var h = $(elem);
				h.find('input')
					//.attr('id', 'w4pl_meta_query_value_'+ pos + '_' + index)
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
			return false;
		});

		$(document.body).on('click', '.w4pl_tax_query_remove_btn', function () {
			$(this).parents('tr').remove();
			reindex_tax_query();
			return false;
		});

		function reindex_tax_query() {
			$('#w4pl_tax_query_table tbody tr').each(function (index, elem) {
				//console.log(index);
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
			return false;
		});

		$(document.body).on('change', '.w4pl_tax_query_operator', function () {

			if ($.inArray($(this).val(), ['IN', 'NOT IN']) != -1) {
				console.log($(this).val());
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
				//console.log(index);
				var h = $(elem);
				h.find('input')
					//.attr('id', 'w4pl_tax_query_terms_'+ pos + '_' + index)
					.attr('name', 'w4pl[tax_query][terms][' + pos + '][' + index + ']');
				$(this).replaceWith(h);
			});
		}
	});
})(jQuery);
