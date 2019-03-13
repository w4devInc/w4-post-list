<?php
/**
 * @package W4 Post List
 * @author Shazzad Hossain Khan
 * @url http://w4dev.com/plugins/w4-post-list
**/


class W4PL_Helper_Tax_Query
{
	function __construct()
	{
		add_filter( 'w4pl/list_edit_form_fields', array($this, 'list_edit_form_fields'), 10, 2 );

		add_action( 'w4pl/admin_print_css', array($this, 'admin_print_css'), 10 );

		add_action( 'w4pl/admin_print_js', array($this, 'admin_print_js'), 10 );

		add_filter( 'w4pl/pre_save_options', array($this, 'pre_save_options') );

		add_filter( 'w4pl/pre_get_options', array($this, 'pre_get_options') );

		add_filter( 'w4pl/parse_query_args', array($this, 'parse_query_args'), 20 );
	}

	// Meta box
	public function list_edit_form_fields( $fields, $post_data )
	{
		$list_type = $post_data['list_type'];
		if( ! in_array($list_type, array('posts') ) )
			return $fields;


		$post_type = $post_data['post_type'];
		$taxonomies = self::post_type_taxonomies_options( $post_type );

		if( empty($taxonomies) )
			return $fields;

		$html = '<div id="w4pl_field_group_tax_query" class="w4pl_field_group">
			<div class="w4pl_group_title">Posts: Tax Query</div>
			<div class="w4pl_group_fields"><div class="w4pl_group_fields_wrap">';

		$tax_query_relation = isset($post_data['tax_query']['relation']) && !empty($post_data['tax_query']['relation']) ? $post_data['tax_query']['relation'] : 'OR';

		$html .= '<div class="wffw">';
		$html .= '<table id="w4pl_tax_query_table" class="widefat">
			<thead>
				<tr>
					<th id="w4pl_tax_query_taxonomy_cell_head">Taxonomy</th>
					<th id="w4pl_tax_query_operator_cell_head">Operator</th>
					<th id="w4pl_tax_query_field_cell_head">Field</th>
					<th id="w4pl_tax_query_terms_cell_head">Terms</th>
					<th id="w4pl_tax_query_action_cell_head">Action</th>
				</tr>
			</thead>
		<tbody>';

		if( isset($post_data['tax_query']) && !empty($post_data['tax_query']) )
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
				<tr><td class="w4pl_tax_query_taxonomy_cell">
					'.
					w4pl_form_child_field_html( array(
						'id' 			=> 'w4pl_tax_query_taxonomy_'. $index,
						'name' 			=> 'w4pl[tax_query][taxonomy]['.$index.']',
						'input_class' 	=> 'w4pl_tax_query_taxonomy',
						'type' 			=> 'select',
						'option' 		=> self::post_type_taxonomies_options( $post_type ),
						'value'			=> $taxonomy
					))
					. '</td><td class="w4pl_tax_query_operator_cell">' 
					. w4pl_form_child_field_html( array(
						'id' 			=> 'w4pl_tax_query_operator_'. $index,
						'name' 			=> 'w4pl[tax_query][operator]['.$index.']',
						'input_class' 	=> 'w4pl_tax_query_operator',
						'type' 			=> 'select',
						'option' 		=> self::tax_query_operator_options(),
						'value'			=> $operator
					))
					. '</td><td class="w4pl_tax_query_field_cell">' 
					. w4pl_form_child_field_html( array(
						'id' 			=> 'w4pl_tax_query_field_'. $index,
						'name' 			=> 'w4pl[tax_query][field]['.$index.']',
						'input_class' 	=> 'w4pl_tax_query_field',
						'type' 			=> 'select',
						'option' 		=> self::tax_query_field_options(),
						'value'			=> $field
					))
					. '</td><td class="w4pl_tax_query_terms_cell terms" data-pos="'. $index .'">';

					if( !is_array($terms) )
						$terms = array($terms);


				$btn_class = '';
				if( in_array($operator, array('IN', 'NOT IN') ) ){
					$btn_class = 'csshide';
				}

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
				$html .= '</td><td class="w4pl_tax_query_action_cell"><a class="w4pl_tax_query_remove_btn" href="#" class="button">Remove</a></td>
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
		<tr><td class="w4pl_tax_query_taxonomy_cell">'
					. w4pl_form_child_field_html( array(
						'name' 			=> 'w4pl[tax_query][taxonomy][]',
						'input_class' 	=> 'w4pl_tax_query_taxonomy',
						'type' 			=> 'select',
						'option' 		=> self::post_type_taxonomies_options( $post_type )
					))
					. '</td><td class="w4pl_tax_query_operator_cell">' 
					. w4pl_form_child_field_html( array(
						'name' 			=> 'w4pl[tax_query][operator][]',
						'input_class' 	=> 'w4pl_tax_query_operator',
						'type' 			=> 'select',
						'option' 		=> self::tax_query_operator_options()
					))
					. '</td><td class="w4pl_tax_query_field_cell">' 
					. w4pl_form_child_field_html( array(
						'name' 			=> 'w4pl[tax_query][field][]',
						'input_class' 	=> 'w4pl_tax_query_field',
						'type' 			=> 'select',
						'option' 		=> self::tax_query_field_options()
					))
			. '</td><td class="w4pl_tax_query_terms_cell terms">' 
			. '<div class="item">
				<input type="text" class="wff wfft_text wffi_w4pl_tax_query_terms">
				<a class="w4pl_tax_query_value_add button" href="#">+</a> 
				<a class="w4pl_tax_query_value_del button" href="#">-</a>
			</div>'
			. '</td><td class="w4pl_tax_query_action_cell terms"><a class="w4pl_tax_query_remove_btn" href="#" class="button">Remove</a></td>'
			.'
		</tr></table>';


		$html .= w4pl_form_field_html( array(
			'field_wrap' 	=> false,
			'name' 			=> 'w4pl[tax_query][relation]',
			'label' 		=> __('Relation', 'w4pl'),
			'type' 			=> 'radio',
			'option' 		=> array('OR' => 'OR', 'AND' => 'AND'),
			'value'			=> $tax_query_relation
		));

		$html .= '</div><!--.wffw-->';
		$html .= '</div><!--.w4pl_group_fields_wrap--></div><!--.w4pl_group_fields--></div><!--#w4pl_field_group_tax_query-->';


		$fields['tax_query'] = array(
			'position'		=> '110',
			'type' 			=> 'html',
			'html'			=> $html
		);

		/* ========================================= */

		return $fields;
	}


	public function admin_print_css()
	{
		?>
		#w4pl_tax_query_table th{ text-align:left; padding-left:10px;}
		#w4pl_tax_query_table th, #w4pl_tax_query_table td, #w4pl_tax_query_table input, #w4pl_tax_query_table select{ font-size:11px;}
       	#w4pl_tax_query_table .wfft_select{ width: 99%; margin-left:0px; margin-right:0px; height: auto; padding:2px}
       	#w4pl_tax_query_table .wfft_text{ margin-left:0px; padding:3px 5px; height: auto;}
       	#w4pl_tax_query_taxonomy_cell_head, .w4pl_tax_query_taxonomy_cell{ width: 130px;}
       	#w4pl_tax_query_field_cell_head, .w4pl_tax_query_field_cell,
       	#w4pl_tax_query_operator_cell_head, .w4pl_tax_query_operator_cell{ width: 60px; padding-left:0 !important; text-align:left;}
       	#w4pl_tax_query_terms_cell_head, .w4pl_tax_query_terms_cell{ width: 160px; padding-left:0 !important; text-align:left;}
       	.w4pl_tax_query_terms_cell .wfft_text{ width: 135px;}
       	#w4pl_tax_query_action_cell_head, .w4pl_tax_query_action_cell{ width: 40px; padding-left:0 !important; text-align:left;}
      	a.w4pl_tax_query_value_add.button, a.w4pl_tax_query_value_del.button{ padding: 3px 5px 4px; height:20px; line-height:12px; margin:2px 0;}
       	a.w4pl_tax_query_remove_btn{ color:#D02A21; text-decoration: none;}

        body.rtl #w4pl_tax_query_compare_cell_head, 
        body.rtl .w4pl_tax_query_compare_cell, 
        body.rtl .w4pl_tax_query_value_cell,
        body.rtl #w4pl_tax_query_table th,
        body.rtl .w4pl_tax_query_action_cell{text-align:right}
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
				console.log($(this).val());
				$(this)
					.parents('tr').find('.w4pl_tax_query_terms_cell').children('.item').show()
					.children('.w4pl_tax_query_value_add, .w4pl_tax_query_value_del').show();
			}
			else
			{
				$(this)
					.parents('tr').find('.w4pl_tax_query_terms_cell .item').hide()
					.children('.w4pl_tax_query_value_add, .w4pl_tax_query_value_del').hide();

				$(this)
					.parents('tr').find('.w4pl_tax_query_terms_cell .item:first').show();
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

	public function pre_save_options($options)
	{
		if( isset($options['tax_query']) && 
			( 
				( array_key_exists('terms', $options['tax_query']) && empty($options['tax_query']['terms']) )
				|| ! array_key_exists('terms', $options['tax_query'])
			)
		)
			unset($options['tax_query']);

		return $options;
	}

	public function pre_get_options($options)
	{
		if( !isset($options['tax_query']) )
			$options['tax_query'] = array();
		return $options;
	}


	public function parse_query_args( $list )
	{
		// meta query
		if( isset($list->options['tax_query']) && isset($list->options['tax_query']['taxonomy']) )
		{
			$list->posts_args['tax_query'] = array();
			foreach( $list->options['tax_query']['taxonomy'] as $index => $taxonomy )
			{
				$field = isset($list->options['tax_query']['field'][$index]) ? $list->options['tax_query']['field'][$index] : 'term_id';
				$operator = isset($list->options['tax_query']['operator'][$index]) ? $list->options['tax_query']['operator'][$index] : '';
				$terms = isset($list->options['tax_query']['terms'][$index]) ? $list->options['tax_query']['terms'][$index] : '';

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

					$list->posts_args['tax_query'][] = array(
						'taxonomy' 	=> $taxonomy,
						'terms' 	=> $terms,
						'field' 	=> $field,
						'operator' 	=> $operator
					);
				}
			}
			if( !empty($list->posts_args['tax_query']) )
			{
				$list->posts_args['tax_query']['relation'] = isset($list->options['tax_query']['relation']) ? $list->options['tax_query']['relation'] : 'OR';
			}
		}

		#echo '<pre>'; print_r($list->posts_args); echo '</pre>';
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