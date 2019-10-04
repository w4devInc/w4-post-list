<?php
/**
 * Date Query Implementation
 * @package WordPress
 * @subpackage W4 Post List
 * @author Shazzad Hossain Khan
 * @url http://w4dev.com/plugins/w4-post-list
**/


class W4PL_Helper_Date_Query
{
	function __construct()
	{
		add_filter( 'w4pl/list_edit_form_fields', array($this, 'list_edit_form_fields'), 10, 2 );

		add_filter( 'w4pl/pre_save_options', array($this, 'pre_save_options') );

		add_filter( 'w4pl/pre_get_options', array($this, 'pre_get_options') );

		add_filter( 'w4pl/parse_query_args', array($this, 'parse_query_args'), 18 );
	}



	/* Meta box */
	public function list_edit_form_fields( $fields, $post_data )
	{
		$list_type = $post_data['list_type'];
		if( ! in_array($list_type, array('posts', 'terms.posts', 'users.posts') ) )
			return $fields;

		$html = '<div id="w4pl_field_group_date_query" class="w4pl_field_group">
			<div class="w4pl_group_title">Posts: Date Query</div>
			<div class="w4pl_group_fields"><div class="w4pl_group_fields_wrap">';

		$html .= '<div class="wffw wff_clone_wrap">';
		$html .= '<table id="w4pl_date_query_table" class="widefat wff_clone_table">
			<thead>
				<tr>
					<th class="column">'. 	__('Column', 'w4pl') 		.'</th>
					<th class="key">'. 		__('Key', 'w4pl') 		.'</th>
					<th class="compare">'. 	__('Compare', 'w4pl') 	.'</th>
					<th class="value">'. 	__('Value', 'w4pl') 	.'</th>
					<th class="action">'. 	__('Action', 'w4pl') 	.'</th>
				</tr>
			</thead>
			<tbody class="wff_clone_to">';

		if( !empty($post_data['date_query']) )
		{
			foreach( $post_data['date_query'] as $date_query )
			{
				$html .= $this->get_date_query_form( $date_query );
			}
		}
		$html .= '</tbody>
			</table>';

		$html .= '
		<div id="w4pl_date_query_value_clone" style="display:none;">
			<div class="item">
				<input type="text" class="wff wfft_text wffi_w4pl_date_query_value">
				<a class="w4pl_date_query_value_add button" href="#">+</a> 
				<a class="w4pl_date_query_value_del button" href="#">-</a>
			</div>
		</div>';

		$html .= '
		<p style="text-align:right;"><a href="#" class="button wff_clone_btn">'. __('+ Add', 'w4pl') .'</a></p>

		<table class="csshide"><tbody class="wff_clone_from">'. $this->get_date_query_form() .'</table>';

		$html .= w4pl_form_field_html( array(
			'field_wrap' 	=> false,
			'name' 			=> 'w4pl[date_query_relation]',
			'label' 		=> __('Relation', 'w4pl'),
			'type' 			=> 'radio',
			'option' 		=> array('OR' => __('OR', 'w4pl'), 'AND' => __('AND', 'w4pl')),
			'value' 		=> $post_data['date_query_relation']
		));

		$html .= '<p class="wffdw2">';
		$html .= '<br />For the value field, you can also use following shortcodes to apply dynamic value:';
		$html .= '<br /><code>[w4pl_date day=+6 hour=-1 format="Y-m-d H:i:s"]</code> - for displaying datetime based on current time';
		$html .= '<br /><code>[w4pl_time day=+6 hour=-1]</code> - for displaying timestamp based on current time';
		$html .= '<br /><br />Note: Above Shortcodes generates times in GMT timezone. To compare time saved in another timezone, use hour attribute. for example: [w4pl_date hour=+6] will generate the time what is identical to GMT+6 timestamp.';
		$html .= '</p>';

		$html .= '</div><!--.wffw-->';
		$html .= '</div><!--.w4pl_group_fields_wrap--></div><!--.w4pl_group_fields--></div><!--#w4pl_field_group_date_query-->';

		$fields['date_query'] = array(
			'position'		=> '140',
			'type' 			=> 'html',
			'html'			=> $html
		);

		/* ========================================= */
		return $fields;
	}


	public function get_date_query_form( $data = array() )
	{
		$data = wp_parse_args( $data, array(
			'column'		=> '',
			'key'			=> '',
			'compare' 		=> '',
			'value' 		=> ''
		));

		extract( $data );

		return '<tr class="wff_clone_item">'
			. '<td class="column">' 
			. w4pl_form_child_field_html( array(
				'name' 			=> 'w4pl[date_query][column][]',
				'type' 			=> 'select',
				'option' 		=> array(
					'post_date' 		=> 'post_date', 
					'post_date_gmt' 	=> 'post_date_gmt', 
					'post_modified' 	=> 'post_modified',
					'post_modified_gmt' => 'post_modified_gmt', 
					'comment_date' 		=> 'comment_date',
					'comment_date_gmt' 	=> 'comment_date_gmt',
					'user_registered' 	=> 'user_registered'
				),
				'value' => $column
			))
			. '</td>'
			. '<td class="key">' 
			. w4pl_form_child_field_html( array(
				'name' 			=> 'w4pl[date_query][key][]',
				'type' 			=> 'select',
				'option' 		=> array(
					'after' 		=> 'after', 
					'before' 		=> 'before', 
					'year' 			=> 'year',
					'month' 		=> 'month', 
					'monthnum' 		=> 'monthnum',
					'week' 			=> 'week',
					'w' 			=> 'w', 
					'dayofyear' 	=> 'dayofyear', 
					'dayofweek' 	=> 'dayofweek',
					'dayofweek_iso' => 'dayofweek_iso', 
					'hour' 			=> 'hour',
					'minute' 		=> 'minute',
					'second' 		=> 'second'
				),
				'value' => $key
			))
			. '</td>'
			. '<td class="compare">' 
			. w4pl_form_child_field_html( array(
				'name' 			=> 'w4pl[date_query][compare][]',
				'input_class' 	=> 'w4pl_field_compare',
				'type' 			=> 'select',
				'option' 		=> self::date_query_compare_options(),
				'value' 		=> $compare
			))
			. '</td>'
			. '<td class="value">'
			. w4pl_form_child_field_html( array(
				'name' 			=> 'w4pl[date_query][value][]',
				'input_class' 	=> 'w4pl_field_value',
				'type' 			=> 'text',
				'value' 		=> $value
			))
			. '</td>'
			. '<td class="w4pl_date_query_action_cell">
				<span class="wff_clone_remove_btn button">'. __('Remove', 'w4pl') .'</span>
			</td>'
			.'
		</tr>';
	}

	public function date_query_compare_options()
	{
		$return = array('=', '!=', '>', '>=', '<', '<=', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN');
		$return = array_combine($return, $return);

		return $return;
	}

	public function pre_save_options($options)
	{
		if( empty($options['date_query_relation']) ){
			$options['date_query_relation'] = 'AND';
		}

		return $options;
	}

	public function pre_get_options($options)
	{
		if( !empty($options['date_query']) ){
			$date_query = W4PL_Utils::filter_multi_row_submit( $options['date_query'] );
			if( $date_query )
			{ array_pop($date_query); }
			$options['date_query'] = $date_query;
		}
		if( empty($options['date_query_relation']) ){
			$options['date_query_relation'] = 'AND';
		}
		return $options;
	}


	public function parse_query_args( $list )
	{
		if( in_array($list->options['list_type'], array('posts', 'terms.posts', 'users.posts') ) )
		{
			// meta query
			if( !empty($list->options['date_query']) ){
				$list->posts_args['date_query'] = array();
				foreach( $list->options['date_query'] as $dq ){

					if( in_array($dq['compare'], array('IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN') ) ){
						$dq['value'] = explode(',', $dq['value']);
						$dq['value'] = array_map('trim', $dq['value']);
					}

					$dq[ $dq['key'] ] = $dq['value'];
					unset($dq['key'], $dq['value']);
					
					$list->posts_args['date_query'][] = $dq;
				}
				$list->posts_args['date_query']['relation'] = $list->options['date_query_relation'];
			}
		}
		#self::p($list->posts_args);;
	}
}

	new W4PL_Helper_Date_Query;
?>