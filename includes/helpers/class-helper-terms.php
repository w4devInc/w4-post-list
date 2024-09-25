<?php
/**
 * Terms query integration
 *
 * @class W4PL_Helper_Terms
 * @package W4_Post_List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Meta query implementor class
 *
 * @class W4PL_Helper_Terms
 */
class W4PL_Helper_Terms {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'w4pl/list_edit_form_fields', array( $this, 'list_edit_form_fields' ), 10, 2 );
		add_filter( 'w4pl/pre_get_options', array( $this, 'pre_get_options' ) );
		add_filter( 'w4pl/parse_query_args', array( $this, 'parse_query_args' ), 15 );
	}

	/**
	 * Terms query control field on list editor
	 *
	 * @param  array $fields  List editor fields.
	 * @param  array $options List options.
	 * @return array          List editor fields.
	 */
	public function list_edit_form_fields( $fields, $options ) {
		$list_type = $options['list_type'];
		if ( ! in_array( $list_type, array( 'terms', 'terms.posts' ) ) ) {
			return $fields;
		}

		$fields['before_field_group_terms_query'] = array(
			'position' => '5',
			'html'     => '<div id="w4pl_field_group_terms_query" class="w4pl_field_group">
				<div class="w4pl_group_title">' . esc_html__( 'Terms', 'w4-post-list' ) . '</div>
				<div class="w4pl_group_fields"><div class="w4pl_group_fields_wrap">',
		);
		$fields['terms_taxonomy']                 = array(
			'position'    => '10',
			'option_name' => 'terms_taxonomy',
			'name'        => 'w4pl[terms_taxonomy]',
			'label'       => __( 'Taxonomy', 'w4-post-list' ),
			'type'        => 'radio',
			'option'      => W4PL_Utils::taxonomies_options(),
			'input_class' => 'w4pl_onchange_lfr',
		);
		$fields['terms__in']                      = array(
			'position'    => '11',
			'option_name' => 'terms__in',
			'name'        => 'w4pl[terms__in]',
			'label'       => __( 'Include terms', 'w4-post-list' ),
			'type'        => 'text',
			'input_class' => 'widefat',
			'desc'        => __( 'comma separated term id', 'w4-post-list' ),
		);
		$fields['terms__not_in']                  = array(
			'position'    => '12',
			'option_name' => 'terms__not_in',
			'name'        => 'w4pl[terms__not_in]',
			'label'       => __( 'Exclude terms', 'w4-post-list' ),
			'type'        => 'text',
			'input_class' => 'widefat',
			'desc'        => __( 'comma separated term id', 'w4-post-list' ),
		);
		$fields['terms_parent__in']               = array(
			'position'    => '13',
			'option_name' => 'terms_parent__in',
			'name'        => 'w4pl[terms_parent__in]',
			'label'       => __( 'Parents', 'w4-post-list' ),
			'type'        => 'text',
			'input_class' => 'widefat',
			'desc'        => __( 'comma separated term id', 'w4-post-list' ),
		);

		$fields['terms_name__like']        = array(
			'position'    => '15',
			'option_name' => 'terms_name__like',
			'name'        => 'w4pl[terms_name__like]',
			'label'       => __( 'Name search', 'w4-post-list' ),
			'type'        => 'text',
			'desc'        => __( 'enter text that will be used to search terms by name &amp; slug', 'w4-post-list' ),
		);
		$fields['terms_slug__like']        = array(
			'position'    => '16',
			'option_name' => 'terms_slug__like',
			'name'        => 'w4pl[terms_slug__like]',
			'label'       => __( 'Slug search', 'w4-post-list' ),
			'type'        => 'text',
			'desc'        => __( 'enter text that will be used to search terms by name', 'w4-post-list' ),
		);
		$fields['terms_description__like'] = array(
			'position'    => '17',
			'option_name' => 'terms_description__like',
			'name'        => 'w4pl[terms_description__like]',
			'label'       => __( 'Description search', 'w4-post-list' ),
			'type'        => 'text',
			'desc'        => __( 'enter text that will be used to search terms by description', 'w4-post-list' ),
		);
		$fields['terms_count__min']        = array(
			'position'    => '18',
			'option_name' => 'terms_count__min',
			'name'        => 'w4pl[terms_count__min]',
			'label'       => __( 'Having min posts', 'w4-post-list' ),
			'type'        => 'text',
		);

		$fields['terms_orderby'] = array(
			'position'    => '21',
			'option_name' => 'terms_orderby',
			'name'        => 'w4pl[terms_orderby]',
			'label'       => __( 'Orderby', 'w4-post-list' ),
			'type'        => 'select',
			'option'      => W4PL_Config::terms_orderby_options(),
		);
		$fields['terms_order']   = array(
			'position'    => '22',
			'option_name' => 'terms_order',
			'name'        => 'w4pl[terms_order]',
			'label'       => __( 'Order', 'w4-post-list' ),
			'type'        => 'radio',
			'option'      => array(
				'ASC'  => __( 'ASC', 'w4-post-list' ),
				'DESC' => __( 'DESC', 'w4-post-list' ),
			),
		);
		$fields['terms_offset']  = array(
			'position'    => '31',
			'option_name' => 'terms_offset',
			'name'        => 'w4pl[terms_offset]',
			'label'       => __( 'Offset', 'w4-post-list' ),
			'type'        => 'text',
			'desc'        => __( 'skip given number of terms from beginning', 'w4-post-list' ),
		);
		$fields['terms_limit']   = array(
			'position'    => '32',
			'option_name' => 'terms_limit',
			'name'        => 'w4pl[terms_limit]',
			'label'       => __( 'Items per page', 'w4-post-list' ),
			'type'        => 'text',
			'desc'        => __( 'number of items to show per page', 'w4-post-list' ),
		);
		$fields['terms_max']     = array(
			'position'    => '33',
			'option_name' => 'terms_max',
			'name'        => 'w4pl[terms_max]',
			'label'       => __( 'Maximum items', 'w4-post-list' ),
			'type'        => 'text',
			'desc'        => __( 'maximum results to display in total, default all found', 'w4-post-list' ),
		);

		$fields['after_field_group_terms_query'] = array(
			'position' => '50',
			'html'     => '</div><!--.w4pl_group_fields_wrap--></div><!--.w4pl_group_fields--></div><!--#w4pl_field_group_terms_query-->',
		);

		return $fields;
	}

	/**
	 * Filter options
	 *
	 * @param  array $options List options.
	 */
	public function pre_get_options( $options ) {
		if ( ! isset( $options ) || ! is_array( $options ) ) {
			$options = array();
		}

		if ( isset( $options['list_type'] ) && in_array( $options['list_type'], array( 'terms', 'terms.posts' ) ) ) {
			$options = wp_parse_args(
				$options,
				array(
					'terms_taxonomy'          => 'category',
					'terms__in'               => '',
					'terms__not_in'           => '',
					'terms_parent__in'        => '',
					'terms_name__like'        => '',
					'terms_slug__like'        => '',
					'terms_description__like' => '',
					'terms_count__min'        => '',
					'terms_offset'            => '',
					'terms_limit'             => '',
					'terms_max'               => '',
					'terms_orderby'           => 'count',
					'terms_order'             => 'DESC',
				)
			);
		}

		return $options;
	}

	/**
	 * Parse list render html to include css/js.
	 *
	 * @param  object $obj Instance of W4PL_List
	 */
	public function parse_query_args( $list ) {
		if ( in_array( $list->options['list_type'], array( 'terms', 'terms.posts' ) ) ) {
			// push default options to query var.
			$default_options = array(
				'terms_count__min'        => 'count__min',
				'terms_name__like'        => 'name__like',
				'terms_slug__like'        => 'slug__like',
				'terms_description__like' => 'description__like',
				'terms_offset'            => 'offset',
				'terms_limit'             => 'limit',
				'terms_orderby'           => 'orderby',
				'terms_order'             => 'order',
			);
			foreach ( $default_options as $option => $name ) {
				if ( ! empty( $list->options[ $option ] ) ) {
					$list->terms_args[ $name ] = $list->options[ $option ];
				}
			}

			// Comma separated ids.
			$id_options = array(
				'terms__in'        => 'term_id__in',
				'terms__not_in'    => 'term_id__not_in',
				'terms_parent__in' => 'term_parent__in',
			);
			foreach ( $id_options as $option => $name ) {
				if ( ! empty( $list->options[ $option ] ) ) {
					$opt = wp_parse_id_list( $list->options[ $option ] );
					if ( ! empty( $opt ) ) {
						$list->terms_args[ $name ] = $opt;
					}
				}
			}

			// when term ids are provided, order by ids.
			if ( isset( $list->terms_args['orderby'] ) && 'custom' == $list->terms_args['orderby'] ) {
				if ( isset( $list->terms_args['term_id__in'] ) && ! empty( $list->terms_args['term_id__in'] ) ) {
					$list->terms_args['orderby'] = 'term_id__in';
				} else {
					$list->terms_args['orderby'] = 'count';
				}
			}

			$list->terms_args['taxonomy'] = $list->options['terms_taxonomy'];

			$paged = isset( $_REQUEST[ 'page' . $list->id ] ) ? $_REQUEST[ 'page' . $list->id ] : 1;

			if ( ! empty( $list->options['terms_limit'] ) ) {
				$list->terms_args['offset'] = (int) $list->options['terms_offset'] + ( $paged - 1 ) * $list->options['terms_limit'];
			}

			if ( ! empty( $list->options['terms_max'] ) && ! empty( $list->options['terms_limit'] ) && $list->options['terms_max'] < ( $list->options['terms_limit'] * $paged ) ) {
				$list->terms_args['limit'] = $list->options['terms_max'] - ( $list->options['terms_limit'] * ( $paged - 1 ) );
			}
		}
	}
}
