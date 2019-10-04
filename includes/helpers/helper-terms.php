<?php
/**
 * @package W4 Post List
 * @author Shazzad Hossain Khan
 * @url http://w4dev.com/plugins/w4-post-list
**/


class W4PL_Helper_Terms
{
	function __construct()
	{
		/* Register User Shortcodes */
		add_filter( 'w4pl/get_shortcodes', 			array($this, 'get_shortcodes'), 21 );

		/* Filer Option */
		add_filter( 'w4pl/pre_get_options', 		array($this, 'pre_get_options') );

		/* Option Page Fields */
		add_filter( 'w4pl/list_edit_form_fields', 		array($this, 'list_edit_form_fields'), 10, 2 );

		/* Parse List Query Args */
		add_filter( 'w4pl/parse_query_args', 		array($this, 'parse_query_args'), 15 );
	}


	/* Register User Shortcodes */

	public static function get_shortcodes( $shortcodes )
	{
		$_shortcodes = array(
			'term_id' => array(
				'group' 	=> 'Term',
				'callback' 	=> array('W4PL_Helper_Terms', 'term_id'),
				'desc' 		=> '<strong>Output</strong>: term id'
			),
			'term_name' => array(
				'group' 	=> 'Term',
				'callback' 	=> array('W4PL_Helper_Terms', 'term_name'),
				'desc' 		=> '<strong>Output</strong>: term name'
			),
			'term_slug' => array(
				'group' 	=> 'Term',
				'callback' 	=> array('W4PL_Helper_Terms', 'term_slug'),
				'desc' 		=> '<strong>Output</strong>: term slug'
			),
			'term_link' => array(
				'group' 	=> 'Term',
				'callback' 	=> array('W4PL_Helper_Terms', 'term_link'),
				'desc' 		=> '<strong>Output</strong>: term page link'
			),
			'term_count' => array(
				'group' 	=> 'Term',
				'callback' 	=> array('W4PL_Helper_Terms', 'term_count'),
				'desc' 		=> '<strong>Output</strong>: term posts count'
			),
			'term_content' => array(
				'group' 	=> 'Term',
				'callback' 	=> array('W4PL_Helper_Terms', 'term_content'),
				'desc' 		=> '<strong>Output</strong>: term description'
			)
		);

		return array_merge( $shortcodes, $_shortcodes );
	}


	/* Term Shortcode Callbacks */

	public static function term_id( $attr, $cont, $list )
	{
		return isset($list->current_term) ? $list->current_term->term_id : 0;
	}
	public static function term_name( $attr, $cont, $list )
	{
		return isset($list->current_term) ? $list->current_term->name : '';
	}
	public static function term_slug( $attr, $cont, $list )
	{
		return isset($list->current_term) ? $list->current_term->slug : '';
	}
	public static function term_link( $attr, $cont, $list )
	{
		return isset($list->current_term) ? get_term_link($list->current_term) : '';
	}
	public static function term_count( $attr, $cont, $list )
	{
		return isset($list->current_term) ? $list->current_term->count : 0;
	}
	public static function term_content( $attr, $cont, $list )
	{
		return isset($list->current_term) ? $list->current_term->description : '';
	}


	/* Filer Option */

	public function pre_get_options($options)
	{
		if( !isset($options) || !is_array($options) )
			$options = array();

		if( isset($options['list_type']) && in_array($options['list_type'], array('terms', 'terms.posts') ) )
		{
			$options = wp_parse_args( $options, array(
				'terms_taxonomy' 			=> 'category',
				'terms__in' 				=> '',
				'terms__not_in' 			=> '',
				'terms_parent__in' 			=> '',
				'terms_name__like'			=> '',
				'terms_slug__like'			=> '',
				'terms_description__like'	=> '',
				'terms_count__min'			=> '',
				'terms_offset'				=> '',
				'terms_limit'				=> '',
				'terms_max'					=> '',
				'terms_orderby'				=> 'count',
				'terms_order'				=> 'DESC'
			));
		}

		return $options;
	}


	/* Option Page Fields */

	public function list_edit_form_fields( $fields, $options )
	{
		$list_type = $options['list_type'];
		if( ! in_array($list_type, array('terms', 'terms.posts') ) )
			return $fields;

		$fields['before_field_group_terms_query'] = array(
			'position'		=> '5',
			'html' 			=> '<div id="w4pl_field_group_terms_query" class="w4pl_field_group">
				<div class="w4pl_group_title">Terms</div>
				<div class="w4pl_group_fields"><div class="w4pl_group_fields_wrap">'
		);
		$fields['terms_taxonomy'] = array(
			'position'		=> '10',
			'option_name' 	=> 'terms_taxonomy',
			'name' 			=> 'w4pl[terms_taxonomy]',
			'label' 		=> 'Taxonomy',
			'type' 			=> 'radio',
			'option'		=> W4PL_Utils::taxonomies_options(),
			'input_class'	=> 'w4pl_onchange_lfr'
		);
		$fields['terms__in'] = array(
			'position'		=> '11',
			'option_name' 	=> 'terms__in',
			'name' 			=> 'w4pl[terms__in]',
			'label' 		=> 'Include terms',
			'type' 			=> 'text',
			'input_class' 	=> 'widefat',
			'desc' 			=> 'comma separated term id'
		);
		$fields['terms__not_in'] = array(
			'position'		=> '12',
			'option_name' 	=> 'terms__not_in',
			'name' 			=> 'w4pl[terms__not_in]',
			'label' 		=> 'Exclude terms',
			'type' 			=> 'text',
			'input_class' 	=> 'widefat',
			'desc' 			=> 'comma separated term id'
		);
		$fields['terms_parent__in'] = array(
			'position'		=> '13',
			'option_name' 	=> 'terms_parent__in',
			'name' 			=> 'w4pl[terms_parent__in]',
			'label' 		=> 'Parents',
			'type' 			=> 'text',
			'input_class' 	=> 'widefat',
			'desc' 			=> 'comma separated term id'
		);

		$fields['terms_name__like'] = array(
			'position'		=> '15',
			'option_name' 	=> 'terms_name__like',
			'name' 			=> 'w4pl[terms_name__like]',
			'label' 		=> 'Name search',
			'type' 			=> 'text',
			'desc' 			=> 'enter text that will be used to search terms by name &amp; slug'
		);
		$fields['terms_slug__like'] = array(
			'position'		=> '16',
			'option_name' 	=> 'terms_slug__like',
			'name' 			=> 'w4pl[terms_slug__like]',
			'label' 		=> 'Slug search',
			'type' 			=> 'text',
			'desc' 			=> 'enter text that will be used to search terms by name'
		);
		$fields['terms_description__like'] = array(
			'position'		=> '17',
			'option_name' 	=> 'terms_description__like',
			'name' 			=> 'w4pl[terms_description__like]',
			'label' 		=> 'Description search',
			'type' 			=> 'text',
			'desc' 			=> 'enter text that will be used to search terms by description'
		);
		$fields['terms_count__min'] = array(
			'position'		=> '18',
			'option_name' 	=> 'terms_count__min',
			'name' 			=> 'w4pl[terms_count__min]',
			'label' 		=> 'Having min posts',
			'type' 			=> 'text'
		);

		$fields['terms_orderby'] = array(
			'position'		=> '21',
			'option_name' 	=> 'terms_orderby',
			'name' 			=> 'w4pl[terms_orderby]',
			'label' 		=> 'Orderby',
			'type' 			=> 'select',
			'option' 		=> self::terms_orderby_options( $options['terms_taxonomy'] )
		);
		$fields['terms_order'] = array(
			'position'		=> '22',
			'option_name' 	=> 'terms_order',
			'name' 			=> 'w4pl[terms_order]',
			'label' 		=> 'Order',
			'type' 			=> 'radio',
			'option' 		=> array('ASC' => 'ASC', 'DESC' => 'DESC')
		);
		$fields['terms_offset'] = array(
			'position'		=> '31',
			'option_name' 	=> 'terms_offset',
			'name' 			=> 'w4pl[terms_offset]',
			'label' 		=> 'Offset',
			'type' 			=> 'text',
			'desc' 			=> 'skip given number of terms from beginning'
		);
		$fields['terms_limit'] = array(
			'position'		=> '32',
			'option_name' 	=> 'terms_limit',
			'name' 			=> 'w4pl[terms_limit]',
			'label' 		=> 'Items per page',
			'type' 			=> 'text',
			'desc' 			=> 'number of items to show per page'
		);
		$fields['terms_max'] = array(
			'position'		=> '33',
			'option_name' 	=> 'terms_max',
			'name' 			=> 'w4pl[terms_max]',
			'label' 		=> 'Maximum items',
			'type' 			=> 'text',
			'desc' 			=> 'maximum results to display in total, default all found'
		);

		$fields['after_field_group_terms_query'] = array(
			'position'		=> '50',
			'html' 			=> '</div><!--.w4pl_group_fields_wrap--></div><!--.w4pl_group_fields--></div><!--#w4pl_field_group_terms_query-->'
		);

		return $fields;
	}

	/* Parse List Query Args */

	public function parse_query_args( $list )
	{
		// terms
		if( in_array($list->options['list_type'], array('terms', 'terms.posts') ) )
		{
			// push default options to query var
			foreach( array(
				'terms_count__min'			=> 'count__min',
				'terms_name__like'			=> 'name__like',
				'terms_slug__like'			=> 'slug__like',
				'terms_description__like'	=> 'description__like',
				'terms_offset'				=> 'offset',
				'terms_limit'				=> 'limit',
				'terms_orderby'				=> 'orderby',
				'terms_order'				=> 'order'
			) as $option => $name )
			{
				if( !empty($list->options[$option]) )
					$list->terms_args[$name] = $list->options[$option];
			}
			# echo '<pre>'; print_r($list->options); echo '</pre>';

			// comma separated ids
			foreach( array(
				'terms__in' 		=> 'term_id__in',
				'terms__not_in' 	=> 'term_id__not_in',
				'terms_parent__in' 	=> 'term_parent__in'
			) as $option => $name )
			{
				if( !empty($list->options[$option]) )
				{
					$opt = wp_parse_id_list( $list->options[$option] );
					if( !empty($opt) )
						$list->terms_args[$name] = $opt;
				}
			}

			// when term ids are provided, order by ids
			if( isset($list->terms_args['orderby']) && 'custom' == $list->terms_args['orderby'] ){
				if( isset($list->terms_args['term_id__in']) && !empty($list->terms_args['term_id__in']) ){
					$list->terms_args['orderby'] = 'term_id__in';
				}
				else{
					$list->terms_args['orderby'] = 'count';
				}
			}

			$list->terms_args['taxonomy'] = $list->options['terms_taxonomy'];

			$paged = isset($_REQUEST['page'. $list->id]) ? $_REQUEST['page'. $list->id] : 1;

			if (! empty($list->options['terms_limit'])) {
				$list->terms_args['offset'] = (int) $list->options['terms_offset'] + ($paged - 1) * $list->options['terms_limit'];
			}
			if (! empty($list->options['terms_max']) && !empty($list->options['terms_limit']) && $list->options['terms_max'] < ($list->options['terms_limit'] * $paged)) {
				$list->terms_args['limit'] = $list->options['terms_max'] - ( $list->options['terms_limit'] * ($paged-1) );
			}
			// terms query var ends
		}
	}


	public static function terms_orderby_options($taxonomy)
	{
		$return = array(
			'term_id'			=> __( 'ID', 					'w4pl'),
			'name'				=> __( 'Name', 					'w4pl'),
			'slug'				=> __( 'Slug', 					'w4pl'),
			'count'				=> __( 'Count', 				'w4pl'),
			'custom'			=> __( 'Include terms', 		'w4pl')
		);

		return $return;
	}
}

	new W4PL_Helper_Terms;
?>
