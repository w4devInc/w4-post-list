(function($){
	'use strict';
	function register_listen_on_trigger( $parent ){
		$parent.find('.listen_on_trigger').each(function(){
			var $wrap = $(this);
			if( ! $wrap.data('listen_on_trigger_init') ){
				$(document.body).on( $wrap.data('listen_on_trigger_name'), function( e, value ){
					var key = $wrap.data('listen_on_trigger_key') || 'field';
					var data = $wrap.data('listen_on_trigger_args') || '{}';
					if( 'string' === typeof(data) ){
						data = JSON.parse( ''+ data);
					}
					if( typeof(value) !== 'undefined' ){
						data[key] = value;
					}
					// console.log(data);

					if( 'ajax_fetch' === $wrap.data('listen_on_trigger_action') ){
						$wrap.addClass('ld');

						var ajaxurl = $wrap.data('listen_on_ajaxurl') ? $wrap.data('listen_on_ajaxurl') : wf.ajaxurl;
	
						$.post( ajaxurl, data, function(r){
							$wrap.removeClass('ld');
							if( r.status == 'ok' ) {
								$wrap.html( r.html );
								register_listen_on_trigger( $wrap );
								register_ajax_forms( $wrap );
							}

							$(document.body).trigger( data.action, [r, data, $wrap] );
						});
					}
					else if( 'set_value' == $wrap.data('listen_on_trigger_action') ){
						if( typeof(value[key]) !== 'undefined' ){
							$wrap.val( value[key] );
						}
					}
					else if( 'set_visibility' == $wrap.data('listen_on_trigger_action') ){
						// console.log(data);
						// console.log($wrap);
						if( data[key] == data.visible_if_value ){
							$wrap.show();
						} else {
							$wrap.hide();
						}
					}
					else if( 'set_html' == $wrap.data('listen_on_trigger_action') ){
						if( typeof(value[key]) !== 'undefined' ){
							$wrap.html( value[key] );
						}
					}
					else if( 'remove' == $wrap.data('listen_on_trigger_action') ){
						$wrap.remove();
					}
				});
				$wrap.data( 'listen_on_trigger_init', true );
			}
		});
	}
	function register_trigger_on_change( $parent ){
		$parent.find('.trigger_on_change').each(function(){
			var $wrap = $(this);
			if( ! $wrap.data('trigger_on_change_init') ){
				$wrap.on( 'change', function(){
					$(document.body).trigger( $wrap.data('on_change_trigger_name'), [$wrap.val()] );
				});
				if( 'radio' === $wrap.attr('type') ) {
					if( $wrap.is(':checked') ) {
						$wrap.trigger( 'change' );
					}
				} else if( 'SELECT' === $wrap.prop("tagName") ) {
					if( $wrap.val() ) {
						$wrap.trigger( 'change' );
					}
				} else {
					$wrap.trigger( 'change' );
				}

				$wrap.data('trigger_on_change_init', true);
			}
		});
	}
	function register_ajax_forms( $parent ){
		$parent.find('.wf-ajax').each(function(){
			var 
			$form 		= $(this),
			$button 	= $form.find('.form_button'),
			action 		= $form.find('.form_action').val() || '';

			$form.find('.wf-field-wrap-type-submit').append( '<div class="wf_notes"></div>' );
			var $notes = $form.find('.wf_notes');
			

			// $(document.body).trigger( 'wf/datepicker_init', [$form] );

			if( action ) {
				$(document.body).trigger( action + '/init', [$form] );
			}

			$button.click( function(e){

				if( action ) {
					$(document.body).trigger( action + '/submit', [$form] );
				}

				e.preventDefault();
				var data = $form.serialize();
	
				$notes.removeClass('_error _ok').empty();
				if( $button.hasClass('ld') )
				{
					$notes.html('Please hold on till the last request completes').addClass('_error');
					return false;
				}
	
				$button.addClass('ld').attr('disabled', 'disabled');
				if( typeof( $form.data('loading_text') ) !== undefined ){
					$notes.html( $form.data('loading_text') ).addClass('_note ld');
				}
	
				$.post(ajaxurl, data)
				.done(function(r){
					if( '0' == r ){
						$notes.html('Invalid form response.').addClass('_error');
					}
					else{
						$notes.html(r.html).addClass('_'+ r.status);
					}
	
					if( action ){
						var _data = data.split("&").reduce(function(prev, curr, i, arr) {
							var p = curr.split("=");
							prev[decodeURIComponent(p[0])] = decodeURIComponent(p[1]);
							return prev;
						}, {});
	
						$(document.body).trigger( action, [r, _data, $form] );
					}
	
					if( typeof(r.urlReplace) !== 'undefined' ){
						window.history.pushState("", "", r.urlReplace);
					}
					if( typeof(r.urlRedirect) !== 'undefined' ){
						window.location.replace(r.urlRedirect);
					}
					if( typeof(r.urlReload) !== 'undefined' ){
						document.location.reload();
					}
				})
				.complete(function(){
					$button.removeClass('ld').removeAttr('disabled');
					$notes.removeClass('ld');
				});
			});
		});
	}

$(document).ready(function(){

	register_listen_on_trigger( $('body') );
	register_trigger_on_change( $('body') );
	register_ajax_forms( $('body') );

	$(document.body).on('wf/listen_on_trigger', function(e, $wrap){
		register_listen_on_trigger( $wrap );
	});
	$(document.body).on('wf/trigger_on_change', function(e, $wrap){
		register_trigger_on_change( $wrap );
	});
	$(document.body).on('wf/ajax_form', function(e, $wrap){
		register_ajax_forms( $wrap );
	});

	/* button click action */
	$(document.body).on('click', '.wf-field_ajax_action_btn', function(e){
		e.preventDefault();
		var 
			$that 		= $(this),
			action 		= $that.data('action') || '',
			target 		= $that.data('target') || '',
			_confirm 	= $that.data('confirm') || '',
			_alert 		= $that.data('alert');

		if( $that.hasClass('ld') || action === '' ){
			return false;
		}

		if( _confirm && ! confirm(_confirm) ){
			return false;
		}

		var data = $that.data('form') ? $( $that.data('form') ).serialize() + '&action='+ action : $that.data();

		$that.addClass('ld').attr('disabled', 'disabled');
		$.post(ajaxurl, data)
		.done(function(r){

			if( 'ok' == r.status ){
				if( target ){
					$(target).html( r.html );
				}
				else if( _alert == '1' ){
					alert( r.html );
				}
			}
			else if( 'error' == r.status ){
				if( target ){
					$(target).html( r.html );
				}
				else if( _alert == '1' ){
					alert( r.html );
				}
			}

			$(document.body).trigger( action, [r, data, $that] );
		})
		.complete(function(){
			$that.removeClass('ld').removeAttr('disabled');
		});
	});

	/* repeater field */
	$(document.body).on('click', '.wf_repeater_add', function(e){
		e.preventDefault();
		var 
			$button 	= $(this),
			$form		= $(this).closest('form'),
			key			= $button.data('parent'),
			$to			= $form.find('#wf_repeated_'+ key + ' tbody'),
			$html		= $form.find('#wf_repeater_'+ key + ' tbody').html();


		if( $html.indexOf('KEY') ){
			var uid = guid();
			$html = $html.replace(/KEY/g, uid);
		}
		$to.append( $html );

		$(document).trigger( 'wf/row_cloned' );
		return false;
	});
	$(document).on('click', '.wf_repeater_remove', function(e){
		e.preventDefault();
		var $button = $(this), $item = $button.closest('.wf_row');
		if( $button.data('action') ) {
			$(document).trigger( $button.data('action'), [$item] );
		} else {
			$item.remove();
		}
		$(document).trigger( 'wf/row_removed' );
		return false;
	});

	/* image field */
	$(document).on('click', '.wf-field_image_btn', function(e){
		e.preventDefault();

		var _that = $(this), 
		field = _that.data('field'), 
		$wrap = _that.closest('.wf-field-wrap'),
		file_frame = wp.media.frames.file_frame = wp.media({
			title: 'Upload or Select Image',
			multiple: false
		});
		file_frame.on( 'select', function() {
			var selected = file_frame.state().get('selection').toJSON();
			var file = selected[0], _file;

			if( typeof(file.sizes) !== 'undefined' ){
				var size = $( '#'+ _that.attr('rel') + '_img' ).data('size');
				if( file.sizes.hasOwnProperty(size) ) {
					_file = file.sizes[size];
				} else if( file.sizes.hasOwnProperty('thumbnail') ) {
					_file = file.sizes.thumbnail;
				} else {
					_file = file.sizes.full;
				}
			} else if( typeof(file.icon) !== 'undefined' ){
				_file = {url: file.icon};
			}

			$wrap.find( '#'+ _that.attr('rel') + '_input' ).val( file[field] );
			$wrap.find( '#'+ _that.attr('rel') + '_img' ).html( '<img src="'+ _file.url +'" class="wf-image-preview" />' );
		});
		file_frame.open();
	});

	$('.wf-field_image_remove_btn').on('click', function(event){
		var _that = $(this);
		event.preventDefault();
		$( '#'+ _that.attr('rel') + '_input').val('');
		$( '#'+ _that.attr('rel') + '_img' ).empty();
	});
});})(jQuery);

function guid() {
  return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
    s4() + '-' + s4() + s4() + s4();
}

function s4() {
  return Math.floor((1 + Math.random()) * 0x10000)
    .toString(16)
    .substring(1);
}