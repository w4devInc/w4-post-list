<?php
class W4PL_Helper_Meta_Query extends W4PL_Core
{
	function __construct()
	{
		add_filter( 'w4pl/admin_list_fields', array($this, 'admin_list_fields'), 10, 2 );

		add_action( 'w4pl/admin_print_css', array($this, 'admin_print_css'), 10 );

		add_action( 'w4pl/admin_print_js', array($this, 'admin_print_js'), 10 );

		add_filter( 'w4pl/parse_query', array($this, 'parse_query'), 10 );
	}

	/* Meta box */
	public function admin_list_fields( $fields, $post_data )
	{
		/* Meta Query */
		$html = '<h2>Meta Query</h2>';

		$meta_query_relation = isset($post_data['meta_query']['relation']) && !empty($post_data['meta_query']['relation']) ? $post_data['meta_query']['relation'] : 'OR';
		$html .= w4pl_form_field_html( array(
			'name' 			=> 'w4pl[meta_query][relation]',
			'label' 		=> 'Relation',
			'type' 			=> 'radio',
			'option' 		=> array('OR' => 'OR', 'AND' => 'AND'),
			'value'			=> $meta_query_relation
		));

		$html .= '<div class="wffw">';
		$html .= '<table id="w4pl_meta_query_table" class="widefat">
			<thead>
				<tr>
					<th>Key</th>
					<th>Compare</th>
					<th>Value</th>
					<th>Action</th>
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
				<tr><td>
					'.
					w4pl_form_child_field_html( array(
						'id' 			=> 'w4pl_meta_query_key_'. $index,
						'name' 			=> 'w4pl[meta_query][key]['.$index.']',
						'input_class' 	=> 'w4pl_meta_query_key',
						'type' 			=> 'text',
						'value'			=> $key
					))
					. '</td><td>' 
					. w4pl_form_child_field_html( array(
						'id' 			=> 'w4pl_meta_query_compare_'. $index,
						'name' 			=> 'w4pl[meta_query][compare]['.$index.']',
						'input_class' 	=> 'w4pl_meta_query_compare',
						'type' 			=> 'select',
						'option' 		=> self::meta_query_compare_options(),
						'value'			=> $compare
					))
					. '</td><td class="values" data-pos="'. $index .'">';

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
				$html .= '</td><td><a class="w4pl_meta_query_remove_btn" href="#" class="button">Remove</a></td>
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
		<p style="text-align:right;"><a id="w4pl_meta_query_add_btn" href="#" class="button">+ Add</a></p>
		<table id="w4pl_meta_query_clone" style="display:none;">
		<tr><td>
			<input type="text" class="wff wffi_w4pl_meta_query_key wfft_text">
			</td><td>' 
			. w4pl_form_child_field_html( array(
				'name' 			=> 'w4pl[meta_query][compare][]',
				'input_class' 	=> 'w4pl_meta_query_compare',
				'type' 			=> 'select',
				'option' 		=> self::meta_query_compare_options()
			))
			. '</td><td class="values">' 
			. '<div class="item">
				<input type="text" class="wff wfft_text wffi_w4pl_meta_query_value">
				<a class="w4pl_meta_query_value_add button" href="#">+</a> 
				<a class="w4pl_meta_query_value_del button" href="#">-</a>
			</div>'
			. '</td><td><a class="w4pl_meta_query_remove_btn" href="#" class="button">Remove</a></td>'
			.'
		</tr></table>';
		$html .= '</div>';

		$fields['meta_query'] = array(
			'position'		=> '60',
			'type' 			=> 'html',
			'html'			=> $html
		);

		/* ========================================= */

		return $fields;
	}


	public function admin_print_css()
	{
		?>
		#w4pl_meta_query_table th{ text-align:left; padding-left:8px;}
        <?php
	}

	public function admin_print_js()
	{
		?>
	$(document).ready(function(){
		$('#w4pl_meta_query_add_btn').click(function(){
			var h = $( $('#w4pl_meta_query_clone tbody').html() );
			h.appendTo( '#w4pl_meta_query_table tbody' );
			reindex_meta_query();
			return false;
		});

		$('.w4pl_meta_query_remove_btn').live('click',function(){
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

	public function parse_query( $obj )
	{
		// meta query
		if( isset($obj->options['meta_query']) && isset($obj->options['meta_query']['key']) )
		{
			$obj->query['meta_query'] = array();
			foreach( $obj->options['meta_query']['key'] as $index => $key )
			{
				$value = isset($obj->options['meta_query']['value'][$index]) ? $obj->options['meta_query']['value'][$index] : '';
				$compare = isset($obj->options['meta_query']['compare'][$index]) ? $obj->options['meta_query']['compare'][$index] : '';
				if( !empty($key) && !empty($compare) )
				{
					// we store meta values data as array. if compare string isn't array, shift the first value
					if( !in_array($compare, array('IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN') ) ) 
						$value = array_shift($value);

					$obj->query['meta_query'][] = array(
						'key' 		=> $key,
						'compare' 	=> $compare,
						'value' 	=> $value
					);
				}
			}
			if( !empty($obj->query['meta_query']) )
			{
				$obj->query['meta_query']['relation'] = isset($obj->options['meta_query']['relation']) ? $obj->options['meta_query']['relation'] : 'OR';
			}
		}
		
		#echo '<pre>'; print_r($obj->query); echo '</pre>';
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