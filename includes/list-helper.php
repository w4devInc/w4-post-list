<?php
/**
 * @package W4 Post List
 * @author Shazzad Hossain Khan
 * @url http://w4dev.com/plugins/w4-post-list
**/


class W4PL_List_Helper
{
	function __construct()
	{
		// get all available shortcodes
		add_filter( 'w4pl/get_shortcodes', 						array($this, 'get_shortcodes') );

		// filter option before saving them
		add_filter( 'w4pl/pre_save_options', 					array($this, 'pre_save_options') );

		// filter list options
		add_filter( 'w4pl/pre_get_list', 						array($this, 'pre_get_list') );

		// filter list options at higher priority
		add_filter( 'w4pl/pre_get_options', 					array($this, 'pre_get_options'), 5 );

		// load list options template from posted data.
		add_action( 'wp_ajax_w4pl_list_edit_form_html', 		array($this, 'list_edit_form_html_ajax') );


		add_action( 'w4pl/list_edit_form', 						array($this, 'list_edit_form') );

		// display create list page template
		add_filter( 'w4pl/list_edit_form_html', 				array($this, 'list_edit_form_html'), 5, 3 );

		// display list creation option page scripts, scripts get loaded on the head tag of that page.
		add_action( 'w4pl/list_options_print_scripts', 			array($this, 'list_options_print_scripts') );

		// get shortcode from posted data
		add_action( 'wp_ajax_w4pl_generate_shortcodes', 		array($this, 'generate_shortcodes_ajax') );
	}


	/*
	 * Shortcodes - Top Level ShortCodes
	*/

	public static function get_shortcodes( $shortcodes )
	{
		// Shortcodes
		$core_shortcodes = array(
			'posts' => array(
				'group' => 'Main',
				'code' => '[posts]'. "\n\n" .'[/posts]',
				'desc' => '<strong>return</strong> the posts template'
			),
			'terms' => array(
				'group' => 'Main',
				'code' => '[terms]'. "\n\n" .'[/terms]',
				'desc' => '<strong>return</strong> the terms template'
			),
			'users' => array(
				'group' => 'Main',
				'code' => '[users]'. "\n\n" .'[/users]',
				'desc' => '<strong>return</strong> the users template'
			),
			'groups' => array(
				'group' => 'Main',
				'code' => '[groups]'. "\n\n" .'[/groups]',
				'desc' => '<strong>return</strong> the groups template'
			),
			'nav' => array(
				'group' => 'Main',
				'code' => '[nav type="plain" ajax="1" prev_text="" next_text=""]',
				'desc' => '<strong>return</strong> pagination for the list
            <br><br><strong>Attributes</strong>:
            <br><strong>type</strong> = (text) allowed values  - plain, list, nav
            <br><strong>ajax</strong> = (0|1) use pagination with ajax
            <br><strong>prev_text</strong> = pagination next button text
            <br><strong>next_text</strong> = pagination prev button text'
			),
		);

		return array_merge( $shortcodes, $core_shortcodes );
	}


	/*
	 * List Options Template
	 * @param $options (array)
	 * @echo (string)
	*/

	public static function list_edit_form( $options )
	{
		$options = apply_filters( 'w4pl/pre_get_options', $options );

		$fields = array();

		// this wrap the whole fields are
		$fields['before_list_options'] = array(
			'position'		=> '0',
			'html' 			=> '<div id="w4pl_list_options">'
		);
		$fields['id'] = array(
			'position'		=> '1.1',
			'option_name' 	=> 'id',
			'name' 			=> 'w4pl[id]',
			'type' 			=> 'hidden'
		);
		$fields['tab_id'] = array(
			'position'		=> '1.2',
			'option_name' 	=> 'tab_id',
			'name' 			=> 'w4pl[tab_id]',
			'type' 			=> 'hidden'
		);


		/* Field Group - List Type */
		$fields['before_field_group_type'] = array(
			'position'		=> '2',
			'html' 			=> '<div id="w4pl_field_group_type" class="w4pl_field_group">
								<div class="w4pl_group_title">'. __('List Type', 'w4pl') .'</div>
								<div class="w4pl_group_fields"><div class="w4pl_group_fields_wrap">'
		);
		$fields['list_type'] = array(
			'position'		=> '3',
			'option_name' 	=> 'list_type',
			'name' 			=> 'w4pl[list_type]',
			'label' 		=> __('List Type', 'w4pl'),
			'type' 			=> 'radio',
			'option' 		=> self::list_type_options(),
			'input_class'	=> 'w4pl_onchange_lfr'
		);
		$fields['after_field_group_type'] = array(
			'position'		=> '4',
			'html' 			=> '</div><!--.w4pl_group_fields_wrap--></div><!--.w4pl_group_fields--></div><!--#w4pl_field_group_type-->'
		);

		/* Field Group - Template */
		$fields['before_field_group_template'] = array(
			'position'		=> '150',
			'html' 			=> '<div id="w4pl_field_group_template" class="w4pl_field_group">
				<div class="w4pl_group_title">'. __('Template', 'w4pl') .'</div>
				<div class="w4pl_group_fields"><div class="w4pl_group_fields_wrap">'
		);

		$template_html = '
		<div class="wffw wffwi_w4pl_template wffwt_textarea">
			<p style="margin-top:0px;">
				<a href="#" class="button w4pl_toggler" data-target="#w4pl_template_examples">'. __('Template Example', 'w4pl') .'</a>
				<a href="#" class="button w4pl_toggler" data-target="#w4pl_template_buttons">'. __('Shortcodes', 'w4pl') .'</a>
			</p>
			<div id="w4pl_template_examples" class="csshide">'
			. "<pre style='width:auto'>\n[groups]\n\t[group_title]\n\t[posts]\n\t\t[post_title]\n\t[/posts]\n[/groups]\n[nav]</pre>"
			. "<br />without group, a simple template should be like -"
			. "<pre style='width:auto'>[posts]\n\t[post_title]\n[/posts]\n[nav]</pre>"
			. '</div>';


		$shortcodes = apply_filters( 'w4pl/get_shortcodes', array() );
		$shortcode_groups = array();
		foreach( $shortcodes as $shortcode => $attr ){
			$group = $attr['group'];
			if( !isset($shortcode_groups[$group]) || !is_array($shortcode_groups[$group]) )
				$shortcode_groups[$group] = array();
			$shortcode_groups[$group][] = $shortcode;
		}

		$template_html .= '<div id="w4pl_template_buttons" class="csshide">';
		foreach( $shortcode_groups as $shortcode_group => $scodes ){
			$template_html .= sprintf(' <div class="w4pl_button_group"><span class="w4pl_button_group_title">%1$s</span>', $shortcode_group );
			foreach( $scodes as $shortcode ){
				$attr = $shortcodes[$shortcode];
				if( isset($attr['code']) )
					$code = $attr['code'];
				else
					$code = '['. $shortcode . ']';
				$template_html .= sprintf(' <a href="#%1$s" data-code="%2$s">%1$s</a>', $shortcode, esc_attr($code) );
			}
			$template_html .= '</div>';
		}
		$template_html .= '</div>';

		/*
		$template_html .= '
		<div class="wfflw wfflwi_w4pl_template wfflwt_textarea">
			<label for="w4pl_template" class="wffl wffli_w4pl_template wfflt_textarea">Template</label>
		</div>';
		*/
		$template_html .= w4pl_form_child_field_html( array(
			'id' 			=> 'w4pl_template',
			'name' 			=> 'w4pl[template]',
			'input_class' 	=> 'wff wffi_w4pl_template wfft_textarea widefat',
			'type' 			=> 'textarea',
			'default' 		=> apply_filters('w4pl/template_default', '' ),
			'value'			=> isset($options['template']) ? $options['template'] : ''
		));
		$template_html .= '</div>';

		$fields['template1'] = array(
			'position'		=> '155',
			'html' 			=> $template_html
		);

		$fields['after_field_group_template'] = array(
			'position'		=> '160',
			'html' 			=> '</div><!--.w4pl_group_fields_wrap--></div><!--.w4pl_group_fields--></div><!--#w4pl_field_group_type-->'
		);

		$fields['after_list_options'] = array(
			'position'		=> '999',
			'type' 			=> 'html',
			'html' 			=> '</div><!--after_list_options-->'
		);

		# echo '<pre>'; print_r($fields); echo '</pre>';

		$form_args = array(
			'no_form' 		=> true,
			'button_after' 	=> false
		);


		// let helper class extend/modify this class
		$fields = apply_filters('w4pl/list_edit_form_fields', $fields, $options);


		// order by position
		uasort($fields, array('W4PL_Plugin', 'order_by_position'));

		# echo '<pre>'; print_r($fields); echo '</pre>';

		$output = w4pl_form_fields($fields, $options, $form_args);

		// filter the output
		$output = apply_filters('w4pl/list_edit_form_html', $output, $fields, $options);

		echo $output;
	}

	public static function list_edit_form_html( $output, $fields, $options )
	{
		if( isset($options['tab_id']) ){
			$output = str_replace('id="'. $options['tab_id'] .'" class="', 'id="'. $options['tab_id'] .'" class="w4pl_active ', $output);
		}

		return $output;
	}

	public static function list_edit_form_html_ajax()
	{
		// if any selection data is passed, we will try to parse the data to get a list
		if( isset($_POST['selection']) && !empty($_POST['selection']) )
		{
			$selection = wp_unslash( $_POST['selection'] );
			if( preg_match( "/\[postlist options=[\"\'](.*?)[\"\']/sm", $selection, $selection_match) )
			{
				$options = maybe_unserialize( base64_decode( str_replace( ' ', '', $selection_match['1']) ) );
				if( is_object($options) ){
					$options = get_object_vars($options);
				}

				if( ! empty($options) ){
					do_action( 'w4pl/list_edit_form', $options );
				}
			}
			elseif( preg_match( "/\[postlist (id|title|slug)=[\"\'](.*?)[\"\']/sm", $selection, $selection_match) )
			{
				if( 'id' == $selection_match['1'] ){
					$list_id = preg_replace( '/[^0-9]/', '', $selection_match['2'] );
				}
				elseif( 'slug' == $selection_match['1'] )
				{
					global $wpdb;
					$post = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE post_name = %s AND post_type = %s", trim($selection_match['2']), w4pl()->plugin_slug() ));
					if( $post ){
						$list_id = $post->ID;
					}
				}
				elseif( 'title' == $selection_match['1'] )
				{
					global $wpdb;
					$post = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE post_title = %s AND post_type = %s", trim($selection_match['2']), w4pl()->plugin_slug() ));
					if( $post ){
						$list_id = $post->ID;
					}
				}

				if( $list_id )
				{
					$options = get_post_meta( $list_id, '_w4pl', true );
					$options['id'] = $list_id;

					$options = apply_filters( 'w4pl/pre_get_options', $options );

					do_action( 'w4pl/list_edit_form', $options );
				}
			}

			elseif( preg_match( "/\[postlist (.*?)]/sm", $selection, $selection_match) )
			{
				$list_id = preg_replace('/[^0-9]/', '', $selection_match['1']);
				if( $list_id ){
					$options = get_post_meta( $list_id, '_w4pl', true );
					$options['id'] = $list_id;

					$options = apply_filters( 'w4pl/pre_get_options', $options );

					do_action( 'w4pl/list_edit_form', $options );
				}
			}
		}
		elseif( isset($_POST['w4pl']) )
		{
			$options = stripslashes_deep( $_POST['w4pl'] );
			if( is_object($options) ){
				$options = get_object_vars($options);
			}
			if( !empty($options) ){
				do_action( 'w4pl/list_edit_form', $options );
			}
		}

		die('');
	}

	public function pre_save_options( $options )
	{
		if( isset($options['post_type']) && 'attachment' == $options['post_type'] ){
			unset( $options['post_status'] );
		}
		if( isset($options['template']) ){
			$options['template'] = apply_filters('w4pl/template', $options['template'], $options );
		}

		return $options;
	}


	public function pre_get_options($options)
	{
		if( !isset($options) || !is_array($options) )
			$options = array();


		/* Version 1.6.7 List Compat */
		if( isset($options['template_loop']) && !empty($options['template_loop']) ){
			if( isset($options['template'])
				&& ! preg_match('/\[posts\](.*?)\[\/posts\]/sm', $options['template'])
				&& preg_match('/\[loop\]/sm', $options['template'], $match )
			){
				$options['template'] = str_replace( $match[0], '[posts]'. $options['template_loop'] .'[/posts]', $options['template'] );
			}
			elseif( empty($options['template']) )
			{
				$options['template'] = str_replace( '[loop]', '[posts]'. $options['template_loop'] .'[/posts]', $options['template'] );
			}

			unset($options['template_loop']);
		} // end


		$options = wp_parse_args( $options, array(
			'id' 				=> md5( microtime() . rand() ),
			'tab_id' 			=> 'w4pl_field_group_type',
			'list_type' 		=> 'posts'
		));

		if( isset($options['template']) ){
			$options['template'] = apply_filters( 'w4pl/template', $options['template'], $options );
		}

		return $options;
	}

	public function pre_get_list($list)
	{
		return $list;
	}


	/*
	 * Display List Shortcode - Ajax
	**/

	public static function generate_shortcodes_ajax()
	{
		$options = isset($_POST) ? stripslashes_deep($_POST) : array();
		if (isset($options['w4pl'])) {
			$options = $options['w4pl'];
		}

		if (empty($options)) {
			die('');
		}

		// if a list exists, we save the data and return the short with id
		if (is_numeric ($options['id']) && get_post ($options['id'])) {
			// pass options through callback
			$options = apply_filters( 'w4pl/pre_save_options', $options );
			// update into post meta
			update_post_meta ($options['id'], '_w4pl', $options);
		} else {
			// filter options, remove default values
			$options = apply_filters( 'w4pl/pre_save_options', $options );
			// encode options, split string by 100 characters to avoid
			$encode = chunk_split( base64_encode( maybe_serialize($options) ), 100, ' ');

			printf( '[postlist options="%s"]', trim($encode) );
		}

		die();
	}

	public static function list_options_print_scripts($options)
	{
		$options = apply_filters('w4pl/pre_get_options', $options);

		wp_print_styles(array ('w4pl_form', 'w4pl_admin'));
		wp_print_scripts( 'w4pl_form' );

		?><style type="text/css"><?php do_action( 'w4pl/admin_print_css' ); ?></style>

		<script type="text/javascript">
(function($){
	$(document).on('w4pl/form_loaded', function(el){
		//console.log('w4pl/form_loaded');
		//$('#w4pl_list_options').css('minHeight', $('.w4pl_group_fields.w4pl_active').outerHeight() );
		w4pl_adjust_height();
		$('#w4pl_orderby').trigger('change');
	});

	$(document).ready(function(){
		$(document).trigger( 'w4pl/form_loaded', $('#w4pl_list_options') );
	});


	$('.w4pl_field_compare').live('change', function(){
		if( $.inArray( $(this).val(), ['IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN']) != -1 ){
			//console.log($(this).val());
			$(this)
				.parent('td').next('td').children('.item').show()
				.children('.button').show();
		}
		else
		{
			$(this)
				.parent('td').next('td').children('.item').hide()
				.children('.button').hide();
			$(this)
				.parent('td').next('td').find('.item:first').show();
		}
	});


	$('.w4pl_clone_parent').live('click', function(e){
		var clone = $(this).parent('.wpce_parent_item').clone();
		var that = $(this);
		that.parent('.wpce_parent_item').after( clone );
		$(this).parent('.wpce_parent_item').parent().find('.w4pl_remove_parent').show();
		return false;
	});
	$('.w4pl_remove_parent').live('click', function(e){
		var that = $(this);
		console.log(that.parent('.wpce_parent_item').siblings().length);
		if( that.parent('.wpce_parent_item').siblings().length == 0 ){
			that.hide();
			return false;
		}
		else{
			$('.w4pl_remove_parent').show();
		}
		that.parent('.wpce_parent_item').remove();
		return false;
	});



	/* onchange post type, refresh the form */
	$('.w4pl_onchange_lfr').live('change', function(){
		var id = $(this).parents('.w4pl_field_group').attr('id');
		// console.log( id );
		w4pl_get_form(null, id );
	});
	/* onclick button, display hidden elements */
	$('.w4pl_toggler').live('click', function(){
		$( $(this).data('target') ).toggle();
		w4pl_adjust_height();
		return false;
	});
	/* onchange orderby, toggle meta input */
	$('#w4pl_orderby').live('change', function(){
		if( 'meta_value' == $(this).val() || 'meta_value_num' == $(this).val() ){
			$('#orderby_meta_key_wrap').show();
		}
		else{
			$('#orderby_meta_key_wrap').hide();
		}
	});
	/* show/hide group options */
	$('.w4pl_group_title').live('click', function()
	{
		$('#w4pl_list_options').height('auto');
		$('.w4pl_field_group').removeClass('w4pl_active');
		$(this).parent('.w4pl_field_group').addClass('w4pl_active');

		$('#w4pl_tab_id').val( $(this).parent('.w4pl_field_group').attr('id') );
		w4pl_adjust_height();

		return false;
	});
	/* put selected element code at pointer */
	$('#w4pl_template_buttons a').live('click', function(e){
		insertAtCaret( 'w4pl_template', $(this).data('code') );
		return false;
	});

	// Adjust form height
	function w4pl_adjust_height()
	{
		var miHeight = $('.w4pl_active .w4pl_group_fields').outerHeight();
		$('#w4pl_list_options').css('minHeight', miHeight);
	}

	function w4pl_get_form( data, showTab )
	{
		/* onchange post type, refresh the form */
		$('#publish').addClass('disabled');

		if( showTab === null ){
			showTab = 'w4pl_field_group_type';
		}
		if( data === null ){
			var data = $('#w4pl_list_options :input').serialize() + '&action=w4pl_list_edit_form_html';
		}

		$('#w4pl_list_options').append('<div id="w4pl_lo"></div>');
		//return false;

		$.post( ajaxurl, data, function(r)
		{
			$('#w4pl_list_options').replaceWith(r);

			$('#'+ showTab).addClass('w4pl_active');

			$(document).trigger('w4pl/form_loaded', $('#w4pl_list_options') );

			// $('.wffwi_w4pl_post_type .spinner').css('display', 'none');
			$('#publish').removeClass('disabled');

			return false;
		})
	}

	/*
	 * Similar feature as tinymce quicktag button
	 * This function helps to place shortcode right at the cursor position
	*/
	function insertAtCaret(areaId,text) {
		var txtarea = document.getElementById(areaId);
		var scrollPos = txtarea.scrollTop;
		var strPos = 0;
		var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ?
			"ff" : (document.selection ? "ie" : false ) );
		if (br == "ie") {
			txtarea.focus();
			var range = document.selection.createRange();
			range.moveStart ('character', -txtarea.value.length);
			strPos = range.text.length;
		}
		else if (br == "ff") strPos = txtarea.selectionStart;

		var front = (txtarea.value).substring(0,strPos);
		var back = (txtarea.value).substring(strPos,txtarea.value.length);
		txtarea.value=front+text+back;
		strPos = strPos + text.length;
		if (br == "ie") {
			txtarea.focus();
			var range = document.selection.createRange();
			range.moveStart ('character', -txtarea.value.length);
			range.moveStart ('character', strPos);
			range.moveEnd ('character', 0);
			range.select();
		}
		else if (br == "ff") {
			txtarea.selectionStart = strPos;
			txtarea.selectionEnd = strPos;
			txtarea.focus();
		}
		txtarea.scrollTop = scrollPos;
	}

	<?php do_action( 'w4pl/admin_print_js' ); ?>

})(jQuery);
		</script>
        <?php
	}

	public static function list_type_options()
	{
		$return = array(
			'posts' 		=> __('Posts', 'w4pl') .' - <small>'. implode(', ', W4PL_Plugin::post_type_options()) .'</small>',
			'terms' 		=> __('Terms', 'w4pl') .' - <small>'. implode(', ', W4PL_Plugin::taxonomies_options()) .'</small>',
			'users' 		=> __('Users', 'w4pl'),
			'terms.posts' 	=> __('Terms + Posts', 'w4pl'),
			'users.posts' 	=> __('Users + Posts', 'w4pl')
		);

		return $return;
	}

	public static function post_status_options()
	{
		global $wp_post_statuses;

		$return = array();
		foreach( $wp_post_types as $post_type => $post_type_object ){
			if(	!$post_type_object->public )
				continue;

			$return[$post_type] = $post_type_object->labels->name;
		}

		return $return;
	}



	public static function get_shortcode_hint_html()
	{
		$shortcodes = W4PL_Core::get_shortcodes();
		$return = '<a target="#shortcode_hint" class="button w4pl_toggler">shortcodes details</a>';
		$return .= '<table id="shortcode_hint" class="widefat csshide">';
		$return .= '<thead><tr><th style="text-align: right;">Tag</th><th>Details</th></tr></thead><tbody>';
		foreach( $shortcodes as $shortcode => $attr ){
			$rc = isset($rc) && $rc == '' ? $rc = 'alt' : '';
			$return .= '<tr class="'. $rc .'">';
			$return .= '<th valign="top" style="text-align: right; font-size:12px; line-height: 1.3em;"><code>['. $shortcode. ']</code></th>';
			$return .= '<td style="font-size:12px; line-height: 1.3em;">'. $attr['desc'] . '</td>';
			$return .= '</tr>';
		}
		$return .= '</tbody></table>';
		return $return;
	}
}

	new W4PL_List_Helper();
?>
