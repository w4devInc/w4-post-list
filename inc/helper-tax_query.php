<?php
class W4PL_Helper_Tax_Query extends W4PL_Core
{
	function __construct()
	{
		add_filter( 'w4pl/admin_list_post_type_fields', array($this, 'admin_list_post_type_fields'), 10, 2 );

		add_action( 'w4pl/admin_print_css', array($this, 'admin_print_css'), 10 );

		add_action( 'w4pl/admin_print_js', array($this, 'admin_print_js'), 10 );

		add_filter( 'w4pl/parse_query', array($this, 'parse_query'), 11 );
	}

	// Meta box
	public function admin_list_post_type_fields( $fields, $post_data )
	{
		$post_type = $post_data['post_type'];
		$taxonomies = self::post_type_taxonomies_options( $post_type );

		if( empty($taxonomies) )
			return $fields;

		$html = '<h2>Tax Query</h2>';

		$tax_query_relation = isset($post_data['tax_query']['relation']) && !empty($post_data['tax_query']['relation']) ? $post_data['tax_query']['relation'] : 'OR';
		$html .= w4pl_form_field_html( array(
			'name' 			=> 'w4pl[tax_query][relation]',
			'label' 		=> 'Relation',
			'type' 			=> 'radio',
			'option' 		=> array('OR' => 'OR', 'AND' => 'AND'),
			'value'			=> $tax_query_relation
		));

		$html .= '<div class="wffw">';
		$html .= '<table id="w4pl_tax_query_table" class="widefat"><thead><tr><th>Taxonomy</th><th>Field</th><th>Operator</th><th>Value</th><th>Action</th></tr></thead>
		<tbody>';

		if( isset($post_data['tax_query']) )
		{
			$index = 0;
			foreach( $post_data['tax_query']['taxonomy'] as $i => $taxonomy )
			{
				$field = isset($post_data['tax_query']['field'][$i]) ? $post_data['tax_query']['field'][$i] : '';
				$operator = isset($post_data['tax_query']['operator'][$i]) ? $post_data['tax_query']['operator'][$i] : '';
				$terms = isset($post_data['tax_query']['terms'][$i]) ? $post_data['tax_query']['terms'][$i] : '';

				if( empty($terms) || empty($operator))
					continue;


				$html .= '
				<tr><td>
					'.
					w4pl_form_child_field_html( array(
						'id' 			=> 'w4pl_tax_query_taxonomy_'. $index,
						'name' 			=> 'w4pl[tax_query][taxonomy]['.$index.']',
						'input_class' 	=> 'w4pl_tax_query_taxonomy',
						'type' 			=> 'select',
						'option' 		=> self::post_type_taxonomies_options( $post_type ),
						'value'			=> $taxonomy
					))
					. '</td><td>' 
					. w4pl_form_child_field_html( array(
						'id' 			=> 'w4pl_tax_query_field_'. $index,
						'name' 			=> 'w4pl[tax_query][field]['.$index.']',
						'input_class' 	=> 'w4pl_tax_query_field',
						'type' 			=> 'select',
						'option' 		=> self::tax_query_field_options(),
						'value'			=> $field
					))
					. '</td><td>' 
					. w4pl_form_child_field_html( array(
						'id' 			=> 'w4pl_tax_query_operator_'. $index,
						'name' 			=> 'w4pl[tax_query][operator]['.$index.']',
						'input_class' 	=> 'w4pl_tax_query_operator',
						'type' 			=> 'select',
						'option' 		=> self::tax_query_operator_options(),
						'value'			=> $operator
					))
					. '</td><td class="terms" data-pos="'. $index .'">';

					if( !is_array($terms) )
						$terms = array($terms);

				$cindex = 0;
				foreach( $terms as $val )
				{
					$html .= '
					<div class="item">
						<input type="text" value="'. esc_attr($val).'" name="w4pl[tax_query][terms]['.$index.'][]" class="wff wffi_w4pl_tax_query_terms_'. $index .' wfft_text ">
						<a class="w4pl_tax_query_value_add button" href="#">+</a> 
						<a class="w4pl_tax_query_value_del button" href="#">-</a>
					</div>';
					++ $cindex;
				}
				$html .= '</td><td><a class="w4pl_tax_query_remove_btn" href="#" class="button">Remove</a></td>
				</tr>';

				++$index;
			}
		}
		$html .= '</tbody>
			</table>';

		$html .= '
		<div id="w4pl_tax_query_value_clone" style="display:none;">
			<div class="item">
				<input type="text" class="wff wfft_text wffi_w4pl_tax_query_value">
				<a class="w4pl_tax_query_value_add button" href="#">+</a> 
				<a class="w4pl_tax_query_value_del button" href="#">-</a>
			</div>
		</div>';

		$html .= '
		<p style="text-align:right;"><a id="w4pl_tax_query_add_btn" href="#" class="button">+ Add</a></p>
		<table id="w4pl_tax_query_clone" style="display:none;">
		<tr><td>'
					. w4pl_form_child_field_html( array(
						'name' 			=> 'w4pl[tax_query][taxonomy][]',
						'input_class' 	=> 'w4pl_tax_query_taxonomy',
						'type' 			=> 'select',
						'option' 		=> self::post_type_taxonomies_options( $post_type )
					))
					. '</td><td>' 
					. w4pl_form_child_field_html( array(
						'name' 			=> 'w4pl[tax_query][field][]',
						'input_class' 	=> 'w4pl_tax_query_field',
						'type' 			=> 'select',
						'option' 		=> self::tax_query_field_options()
					))
					. '</td><td>' 
					. w4pl_form_child_field_html( array(
						'name' 			=> 'w4pl[tax_query][operator][]',
						'input_class' 	=> 'w4pl_tax_query_operator',
						'type' 			=> 'select',
						'option' 		=> self::tax_query_operator_options()
					))
			. '</td><td class="terms">' 
			. '<div class="item">
				<input type="text" class="wff wfft_text wffi_w4pl_tax_query_terms">
				<a class="w4pl_tax_query_value_add button" href="#">+</a> 
				<a class="w4pl_tax_query_value_del button" href="#">-</a>
			</div>'
			. '</td><td><a class="w4pl_tax_query_remove_btn" href="#" class="button">Remove</a></td>'
			.'
		</tr></table>';
		$html .= '</div>';

		$fields['tax_query'] = array(
			'position'		=> '30',
			'type' 			=> 'html',
			'html'			=> $html
		);

		/* ========================================= */

		return $fields;
	}


	public function admin_print_css()
	{
		?>
		#w4pl_tax_query_table th{ text-align:left; padding-left:8px;}
        <?php
	}

	public function admin_print_js()
	{
		?>
	$(document).ready(function(){
		$('#w4pl_tax_query_add_btn').live('click', function(){
			var h = $( $('#w4pl_tax_query_clone tbody').html() );
			h.appendTo( '#w4pl_tax_query_table tbody' );
			reindex_tax_query();
			return false;
		});

		$('.w4pl_tax_query_remove_btn').live('click',function(){
			$(this).parents('tr').remove();
			reindex_tax_query();
			return false;
		});

		function reindex_tax_query(){
			$('#w4pl_tax_query_table tbody tr').each(function(index, elem){
				//console.log(index);
				var h = $(elem);
				h.find('.wffi_w4pl_tax_query_taxonomy')
				.attr('name', 'w4pl[tax_query][taxonomy]['+ index+ ']')
                .removeAttr('id');
				h.find('.wffi_w4pl_tax_query_field')
				.attr('name', 'w4pl[tax_query][field]['+ index+ ']')
                .removeAttr('id');
				h.find('.wffi_w4pl_tax_query_operator')
				.attr('name', 'w4pl[tax_query][operator]['+ index+ ']')
                .removeAttr('id');
				h.find('.wffi_w4pl_tax_query_terms')
				.attr('name', 'w4pl[tax_query][terms]['+ index+ '][]')
                .removeAttr('id');
				h.find('td.terms')
				.attr('data-pos', index);

				$(this).replaceWith(h);
			});
		}

		$('.w4pl_tax_query_value_add').live('click', function(){
			$('.w4pl_tax_query_value_del').show();

			var td = $(this).parent('.item').parent('td');
			var that = $(this);
			
			that.parent('.item').after( $('#w4pl_tax_query_value_clone').html() );
			reindex_value(td);
			return false;
		});
		$('.w4pl_tax_query_value_del').live('click', function(){
			$('.w4pl_tax_query_value_del').show();

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

		$('.w4pl_tax_query_operator').live('change', function(){
			if( $.inArray( $(this).val(), ['IN', 'NOT IN']) != -1 ){
				//console.log($(this).val());
				$(this)
					.parent('td').next('td').children('.item').show()
					.children('.w4pl_tax_query_value_add, .w4pl_tax_query_value_del').show();
			}
			else
			{
				$(this)
					.parent('td').next('td').children('.item').hide()
					.children('.w4pl_tax_query_value_add, .w4pl_tax_query_value_del').hide();
				
				$(this)
					.parent('td').next('td').find('.item:first').show();
			}
		});
		
		$('.w4pl_tax_query_operator').each(function(i,elem){
			$(this).trigger('change');
		});

		function reindex_value(td){
			var siblings = td.children('.item');
			var pos = td.data('pos');
			siblings.each(function(index,elem){
				//console.log(index);
				var h = $(elem);
				h.find('input')
				//.attr('id', 'w4pl_tax_query_terms_'+ pos + '_' + index)
				.attr('name', 'w4pl[tax_query][terms]['+ pos+ ']['+ index+ ']');
				$(this).replaceWith(h);
			});
		}
	});
        <?php
	}

	public function parse_query( $obj )
	{
		// meta query
		if( isset($obj->options['tax_query']) && isset($obj->options['tax_query']['taxonomy']) )
		{
			$obj->query['tax_query'] = array();
			foreach( $obj->options['tax_query']['taxonomy'] as $index => $taxonomy )
			{
				$field = isset($obj->options['tax_query']['field'][$index]) ? $obj->options['tax_query']['field'][$index] : 'term_id';
				$operator = isset($obj->options['tax_query']['operator'][$index]) ? $obj->options['tax_query']['operator'][$index] : '';
				$terms = isset($obj->options['tax_query']['terms'][$index]) ? $obj->options['tax_query']['terms'][$index] : '';

				if( !empty($terms) && !empty($operator) )
				{
					// meta query accept IN or NOT IN operator.
					if( '=' == $operator ){
						$operator = 'IN';
					}
					elseif( '!=' == $operator ){
						$operator = 'NOT IN';
					}

					if( 'post_format' == $taxonomy ){
						foreach( $terms as $i => $term ){
							$terms[$i] = 'post-format-' . str_replace('post-format-', '', $term );
						}
					}

					$obj->query['tax_query'][] = array(
						'taxonomy' 	=> $taxonomy,
						'terms' 	=> $terms,
						'field' 	=> $field,
						'operator' 	=> $operator
					);
				}
			}
			if( !empty($obj->query['tax_query']) )
			{
				$obj->query['tax_query']['relation'] = isset($obj->options['tax_query']['relation']) ? $obj->options['tax_query']['relation'] : 'OR';
			}
		}

		#echo '<pre>'; print_r($obj->query); echo '</pre>';
	}

	public static function post_type_taxonomies_options( $post_type )
	{
		$return = array();
		foreach( get_object_taxonomies($post_type, 'all') as $taxonomy => $taxonomy_object ){
			if( ! $taxonomy_object->public )
				continue;

			$return[$taxonomy] = $taxonomy_object->labels->name;
		}
		return $return;
	}

	public static function tax_query_field_options()
	{
		$return = array('term_id' => 'Term id', 'name' => 'Name', 'slug' => 'Slug');
		return $return;
	}

	public static function tax_query_operator_options()
	{
		$return = array('=', '!=', 'IN', 'NOT IN');
		$return = array_combine($return, $return);

		return $return;
	}
}

	new W4PL_Helper_Tax_Query;
?>