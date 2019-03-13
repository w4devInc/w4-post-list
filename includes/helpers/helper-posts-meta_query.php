<?php
/**
 * Meta Query Implementation
 * @package WordPress
 * @subpackage W4 Post List
 * @author Shazzad Hossain Khan
 * @url http://w4dev.com/plugins/w4-post-list
**/


class W4PL_Helper_Meta_Query
{
	function __construct()
	{
		add_filter( 'w4pl/list_edit_form_fields', array($this, 'list_edit_form_fields'), 10, 2 );

		add_action( 'w4pl/admin_print_css', array($this, 'admin_print_css'), 10 );

		add_action( 'w4pl/admin_print_js', array($this, 'admin_print_js'), 10 );

		add_filter( 'w4pl/pre_save_options', array($this, 'pre_save_options') );

		add_filter( 'w4pl/pre_get_options', array($this, 'pre_get_options') );

		add_filter( 'w4pl/parse_query_args', array($this, 'parse_query_args'), 18 );
	}



	/* Meta box */
	public function list_edit_form_fields( $fields, $post_data )
	{
		$list_type = $post_data['list_type'];
		if( ! in_array($list_type, array('posts', 'terms.posts') ) )
			return $fields;


		/* Meta Query */
		$html = '<div id="w4pl_field_group_meta_query" class="w4pl_field_group">
			<div class="w4pl_group_title">'. __('Posts: Meta Query', 'w4pl') .'</div>
			<div class="w4pl_group_fields"><div class="w4pl_group_fields_wrap">';

		$meta_query_relation = isset($post_data['meta_query']['relation']) && !empty($post_data['meta_query']['relation']) ? $post_data['meta_query']['relation'] : 'OR';

		$html .= '<div class="wffw">';
		$html .= '<table id="w4pl_meta_query_table" class="widefat">
			<thead>
				<tr>
					<th id="w4pl_meta_query_key_cell_head">'. __('Key', 'w4pl') .'</th>
					<th id="w4pl_meta_query_compare_cell_head">'. __('Compare', 'w4pl') .'</th>
					<th id="w4pl_meta_query_value_cell_head">'. __('Value', 'w4pl') .'</th>
					<th id="w4pl_meta_query_action_cell_head">'. __('Action', 'w4pl') .'</th>
				</tr>
			</thead>
			<tbody>';

		if( isset($post_data['meta_query']) 
			&& isset($post_data['meta_query']['key']) 
			&& is_array($post_data['meta_query']['key']) 
			&& !empty($post_data['meta_query']['key']) 
		)
		{
			$index = 0;
			foreach( $post_data['meta_query']['key'] as $i => $key )
			{
				$compare = isset($post_data['meta_query']['compare'][$i]) ? $post_data['meta_query']['compare'][$i] : '';
				$value = isset($post_data['meta_query']['value'][$i]) ? $post_data['meta_query']['value'][$i] : '';

				if( empty($key) || empty($compare))
					continue;

				$html .= '
				<tr><td class="w4pl_meta_query_key_cell">
					'.
					w4pl_form_child_field_html( array(
						'id' 			=> 'w4pl_meta_query_key_'. $index,
						'name' 			=> 'w4pl[meta_query][key]['.$index.']',
						'input_class' 	=> 'w4pl_meta_query_key',
						'type' 			=> 'text',
						'value'			=> $key
					))
					. '</td><td class="w4pl_meta_query_compare_cell">' 
					. w4pl_form_child_field_html( array(
						'id' 			=> 'w4pl_meta_query_compare_'. $index,
						'name' 			=> 'w4pl[meta_query][compare]['.$index.']',
						'input_class' 	=> 'w4pl_meta_query_compare',
						'type' 			=> 'select',
						'option' 		=> self::meta_query_compare_options(),
						'value'			=> $compare
					))
					. '</td><td class="w4pl_meta_query_value_cell values" data-pos="'. $index .'">';

					if( !is_array($value) )
						$value = array($value);

				$cindex = 0;
				foreach( $value as $val )
				{
					$html .= '
					<div class="item">
						<input type="text" value="'. esc_attr($val).'" name="w4pl[meta_query][value]['.$index.'][]" class="wff wffi_w4pl_meta_query_value_'. $index .' wfft_text ">
						<a class="w4pl_meta_query_value_add button" href="#">+</a> 
						<a class="w4pl_meta_query_value_del button" href="#">-</a>
					</div>';
					++ $cindex;
				}
				$html .= '</td><td class="w4pl_meta_query_action_cell"><a class="w4pl_meta_query_remove_btn" href="#" class="button">'. __('Remove', 'w4pl') .'</a></td>
				</tr>';

				++$index;
			}
		}
		$html .= '</tbody>
			</table>';

		$html .= '
		<div id="w4pl_meta_query_value_clone" style="display:none;">
			<div class="item">
				<input type="text" class="wff wfft_text wffi_w4pl_meta_query_value">
				<a class="w4pl_meta_query_value_add button" href="#">+</a> 
				<a class="w4pl_meta_query_value_del button" href="#">-</a>
			</div>
		</div>';

		$html .= '
		<p style="text-align:right;"><a id="w4pl_meta_query_add_btn" href="#" class="button">'. __('+ Add', 'w4pl') .'</a></p>
		<table id="w4pl_meta_query_clone" style="display:none;">
		<tr><td class="w4pl_meta_query_key_cell">
			<input type="text" class="wff wffi_w4pl_meta_query_key wfft_text">
			</td><td class="w4pl_meta_query_compare_cell">' 
			. w4pl_form_child_field_html( array(
				'name' 			=> 'w4pl[meta_query][compare][]',
				'input_class' 	=> 'w4pl_meta_query_compare',
				'type' 			=> 'select',
				'option' 		=> self::meta_query_compare_options()
			))
			. '</td><td class="w4pl_meta_query_value_cell values">' 
			. '<div class="item">
				<input type="text" class="wff wfft_text wffi_w4pl_meta_query_value">
				<a class="w4pl_meta_query_value_add button" href="#">+</a> 
				<a class="w4pl_meta_query_value_del button" href="#">-</a>
			</div>'
			. '</td><td class="w4pl_meta_query_action_cell"><a class="w4pl_meta_query_remove_btn" href="#" class="button">'. __('Remove', 'w4pl') .'</a></td>'
			.'
		</tr></table>';

		$html .= w4pl_form_field_html( array(
			'field_wrap' 	=> false,
			'name' 			=> 'w4pl[meta_query][relation]',
			'label' 		=> __('Relation', 'w4pl'),
			'type' 			=> 'radio',
			'option' 		=> array('OR' => __('OR', 'w4pl'), 'AND' => __('AND', 'w4pl')),
			'value'			=> $meta_query_relation
		));

		$html .= '<p class="wffdw2">';
		$html .= '<br />For the value field, you can also use following shortcodes to apply dynamic value:';
		$html .= '<br /><code>[w4pl_date day=+6 hour=-1 format="Y-m-d H:i:s"]</code> - for displaying datetime based on current time';
		$html .= '<br /><code>[w4pl_time day=+6 hour=-1]</code> - for displaying timestamp based on current time';
		$html .= '<br /><br />Note: Above Shortcodes generates times in GMT timezone. To compare time saved in another timezone, use hour attribute. for example: [w4pl_date hour=+6] will generate the time what is identical to GMT+6 timestamp.';
		$html .= '</p>';

		$html .= '</div><!--.wffw-->';
		$html .= '</div><!--.w4pl_group_fields_wrap--></div><!--.w4pl_group_fields--></div><!--#w4pl_field_group_meta_query-->';

		$fields['meta_query'] = array(
			'position'		=> '120',
			'type' 			=> 'html',
			'html'			=> $html
		);

		/* ========================================= */

		return $fields;
	}


	public function admin_print_css()
	{
		?>
		#w4pl_meta_query_table th{ text-align:left; font-weight:bold;}
		#w4pl_meta_query_table th, #w4pl_meta_query_table td, #w4pl_meta_query_table input, #w4pl_meta_query_table select{ font-size:11px;}
		#w4pl_meta_query_table .wfft_select, .w4pl_meta_query_key_cell .wfft_text{ width: 100%; margin-left:0px; margin-right:0px; height: auto; padding:2px;box-sizing:border-box;}
		#w4pl_meta_query_table .wfft_text{ margin-left:0px; padding:3px 5px; height: auto;}
		#w4pl_meta_query_key_cell_head, .w4pl_meta_query_key_cell{ width: 200px;}
		#w4pl_meta_query_compare_cell_head, .w4pl_meta_query_compare_cell{ width: 50px; padding-left:0 !important; text-align:left;}
		#w4pl_meta_query_value_cell_head, .w4pl_meta_query_value_cell{ width: 260px; padding-left:0 !important; text-align:left;}
		.w4pl_meta_query_value_cell .wfft_text{ width: 205px;}
		#w4pl_meta_query_action_cell_head, .w4pl_meta_query_action_cell{ width: 40px; padding-left:0 !important; text-align:left;}
		a.w4pl_meta_query_value_add.button, a.w4pl_meta_query_value_del.button{ padding: 3px 5px 4px; height:20px; line-height:12px; margin:2px 0;}
		a.w4pl_meta_query_remove_btn{ color:#D02A21;}
        
        body.rtl #w4pl_meta_query_compare_cell_head, 
        body.rtl .w4pl_meta_query_compare_cell, 
        body.rtl .w4pl_meta_query_value_cell,
        body.rtl #w4pl_meta_query_table th,
        body.rtl .w4pl_meta_query_action_cell{text-align:right}
		<?php
	}

	public function admin_print_js()
	{
		?>
	$(document).ready(function(){
		$('#w4pl_meta_query_add_btn').live('click', function(){
			var h = $( $('#w4pl_meta_query_clone tbody').html() );
			h.appendTo( '#w4pl_meta_query_table tbody' );
			reindex_meta_query();
			return false;
		});
		$('.w4pl_meta_query_remove_btn').live('click', function(){
			$(this).parents('tr').remove();
			reindex_meta_query();
			return false;
		});

		function reindex_meta_query(){
			$('#w4pl_meta_query_table tbody tr').each(function(index, elem){
				//console.log(index);
				var h = $(elem);
				h.find('.wffi_w4pl_meta_query_key')
				.attr('name', 'w4pl[meta_query][key]['+ index+ ']');
				h.find('.wffi_w4pl_meta_query_compare')
				.attr('name', 'w4pl[meta_query][compare]['+ index+ ']');
				h.find('.wffi_w4pl_meta_query_value')
				.attr('name', 'w4pl[meta_query][value]['+ index+ '][]');
				h.find('td.values')
				.attr('data-pos', index);

				$(this).replaceWith(h);
			});
		}

		$('.w4pl_meta_query_value_add').live('click', function(){
			$('.w4pl_meta_query_value_del').show();

			var td = $(this).parent('.item').parent('td');
			var that = $(this);
			
			that.parent('.item').after( $('#w4pl_meta_query_value_clone').html() );
			reindex_value(td);
			return false;
		});
		$('.w4pl_meta_query_value_del').live('click', function(){
			$('.w4pl_meta_query_value_del').show();

			var td = $(this).parent('.item').parent('td');
			var that = $(this);

			if( td.children('.item').length == 1 ){
				$(this).hide();
				return false;
			}

			that.parent('.item').remove();
			reindex_value(td);
			return false;
		});

		$('.w4pl_meta_query_compare').live('change', function(){
			if( $.inArray( $(this).val(), ['IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN']) != -1 ){
				//console.log($(this).val());
				$(this)
					.parent('td').next('td').children('.item').show()
					.children('.w4pl_meta_query_value_add, .w4pl_meta_query_value_del').show();
			}
			else
			{
				$(this)
					.parent('td').next('td').children('.item').hide()
					.children('.w4pl_meta_query_value_add, .w4pl_meta_query_value_del').hide();
				
				$(this)
					.parent('td').next('td').find('.item:first').show();
			}
		});
		
		$('.w4pl_meta_query_compare').each(function(i,elem){
			$(this).trigger('change');
		});

		function reindex_value(td){
			var siblings = td.children('.item');
			var pos = td.data('pos');
			siblings.each(function(index,elem){
				//console.log(index);
				var h = $(elem);
				h.find('input')
				//.attr('id', 'w4pl_meta_query_value_'+ pos + '_' + index)
				.attr('name', 'w4pl[meta_query][value]['+ pos+ ']['+ index+ ']');
				$(this).replaceWith(h);
			});
		}
	});
		<?php
	}

	public function pre_save_options($options)
	{
		if( isset($options['meta_query']) && 
			( 
				( array_key_exists('value', $options['meta_query']) && empty($options['meta_query']['value']) )
				|| ! array_key_exists('value', $options['meta_query'])
			)
		)
			unset($options['meta_query']);

		return $options;
	}

	public function pre_get_options($options)
	{
		if( !isset($options['meta_query']) )
			$options['meta_query'] = array();
		return $options;
	}


	public function parse_query_args( $obj )
	{
		// meta query
		if( isset($obj->options['meta_query']) && isset($obj->options['meta_query']['key']) )
		{
			$obj->posts_args['meta_query'] = array();
			foreach( $obj->options['meta_query']['key'] as $index => $key )
			{
				$value = isset($obj->options['meta_query']['value'][$index]) ? $obj->options['meta_query']['value'][$index] : '';
				$compare = isset($obj->options['meta_query']['compare'][$index]) ? $obj->options['meta_query']['compare'][$index] : '';

				// parse shortcode from meta values, this allows to use dynamic values
				if( is_array($value) ){
					$value = array_map('do_shortcode', $value);
				}
				elseif( !empty($value) ){
					$value = do_shortcode($value);
				}
				
				if( !empty($key) && !empty($compare) )
				{
					// we store meta values data as array. if compare string isn't array, shift the first value
					if( !in_array($compare, array('IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN') ) ) 
						$value = array_shift($value);

					$meta_qyery = array(
						'key' 		=> $key,
						'compare' 	=> $compare
					);

					if( '' != $value )
					{ $meta_qyery['value'] = $value; }

					$obj->posts_args['meta_query'][] = $meta_qyery;
				}
			}
			if( !empty($obj->posts_args['meta_query']) )
			{
				$obj->posts_args['meta_query']['relation'] = isset($obj->options['meta_query']['relation']) ? $obj->options['meta_query']['relation'] : 'OR';
			}
		}

		# echo '<pre>'; print_r($obj->posts_args); echo '</pre>';
	}


	public function meta_query_compare_options()
	{
		$return = array('=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE','NOT EXISTS', 'REGEXP', 'NOT REGEXP', 'RLIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN');
		$return = array_combine($return, $return);

		return $return;
	}
}

	new W4PL_Helper_Meta_Query;
?>