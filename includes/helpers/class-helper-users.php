<?php
/**
 * Terms query integration
 *
 * @class W4PL_Helper_Users
 * @package W4_Post_List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Meta query implementor class
 *
 * @class W4PL_Helper_Users
 */
class W4PL_Helper_Users {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'w4pl/pre_get_options', array( $this, 'pre_get_options' ) );
		add_filter( 'w4pl/list_edit_form_fields', array( $this, 'list_edit_form_fields' ), 10, 2 );
		add_filter( 'w4pl/parse_query_args', array( $this, 'parse_query_args' ), 15 );
	}

	/**
	 * Users query control field on list editor
	 *
	 * @param  array $fields  List editor fields.
	 * @param  array $options List options.
	 * @return array          List editor fields.
	 */
	public function list_edit_form_fields( $fields, $options ) {
		$list_type = $options['list_type'];
		if ( ! in_array( $list_type, array( 'users', 'users.posts' ) ) ) {
			return $fields;
		}

		$fields['before_field_group_users_query'] = array(
			'position' => '5',
			'html'     => '<div id="w4pl_field_group_users_query" class="w4pl_field_group">
				<div class="w4pl_group_title">Users</div>
				<div class="w4pl_group_fields"><div class="w4pl_group_fields_wrap">',
		);
		$fields['users__in']                      = array(
			'position'    => '11',
			'option_name' => 'users__in',
			'name'        => 'w4pl[users__in]',
			'label'       => 'Include users',
			'type'        => 'text',
			'input_class' => 'widefat',
			'desc'        => 'comma separated term id',
		);
		$fields['users__not_in']                  = array(
			'position'    => '12',
			'option_name' => 'users__not_in',
			'name'        => 'w4pl[users__not_in]',
			'label'       => 'Exclude users',
			'type'        => 'text',
			'input_class' => 'widefat',
			'desc'        => 'comma separated term id',
		);

		$fields['users_display_name__like'] = array(
			'position'    => '15',
			'option_name' => 'users_display_name__like',
			'name'        => 'w4pl[users_display_name__like]',
			'label'       => 'Display Name search',
			'type'        => 'text',
			'desc'        => 'enter text that will be used to search users by name',
		);
		$fields['users_user_email__like']   = array(
			'position'    => '16',
			'option_name' => 'users_user_email__like',
			'name'        => 'w4pl[users_user_email__like]',
			'label'       => 'Email search',
			'type'        => 'text',
			'desc'        => 'enter text that will be used to search users by email',
		);

		$fields['users_orderby'] = array(
			'position'    => '21',
			'option_name' => 'users_orderby',
			'name'        => 'w4pl[users_orderby]',
			'label'       => 'Orderby',
			'type'        => 'select',
			'option'      => W4PL_Config::users_orderby_options(),
		);
		$fields['users_order']   = array(
			'position'    => '22',
			'option_name' => 'users_order',
			'name'        => 'w4pl[users_order]',
			'label'       => 'Order',
			'type'        => 'radio',
			'option'      => array(
				'ASC'  => 'ASC',
				'DESC' => 'DESC',
			),
		);
		$fields['users_offset']  = array(
			'position'    => '31',
			'option_name' => 'users_offset',
			'name'        => 'w4pl[users_offset]',
			'label'       => 'Offset',
			'type'        => 'text',
			'desc'        => 'skip given number of users from beginning',
		);
		$fields['users_limit']   = array(
			'position'    => '32',
			'option_name' => 'users_limit',
			'name'        => 'w4pl[users_limit]',
			'label'       => 'Items per page',
			'type'        => 'text',
			'desc'        => 'number of items to show per page',
		);
		$fields['users_max']     = array(
			'position'    => '33',
			'option_name' => 'users_max',
			'name'        => 'w4pl[users_max]',
			'label'       => 'Maximum items',
			'type'        => 'text',
			'desc'        => 'maximum results to display in total, default all found',
		);

		$fields['after_field_group_users_query'] = array(
			'position' => '50',
			'html'     => '</div><!--.w4pl_group_fields_wrap--></div><!--.w4pl_group_fields--></div><!--#w4pl_field_group_users_query-->',
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

		$options = wp_parse_args(
			$options,
			array(
				'users__in'                => '',
				'users__not_in'            => '',
				'users_display_name__like' => '',
				'users_user_email__like'   => '',
				'users_offset'             => '',
				'users_limit'              => '',
				'users_max'                => '',
				'users_orderby'            => 'ID',
				'users_order'              => 'DESC',
			)
		);

		return $options;
	}

	/**
	 * Parse list render html to include css/js.
	 *
	 * @param  object $obj Instance of W4PL_List
	 */
	public function parse_query_args( $list ) {
		if ( in_array( $list->options['list_type'], array( 'users', 'users.posts' ) ) ) {
			// push default options to query var.
			foreach ( array(
				'users_display_name__like' => 'display_name__like',
				'users_user_email__like'   => 'user_email__like',
				'users_offset'             => 'offset',
				'users_limit'              => 'limit',
				'users_orderby'            => 'orderby',
				'users_order'              => 'order',
			) as $option => $name ) {
				if ( ! empty( $list->options[ $option ] ) ) {
					$list->users_args[ $name ] = $list->options[ $option ];
				}
			}

			// comma separated ids.
			foreach ( array(
				'users__in'     => 'ID__in',
				'users__not_in' => 'ID__not_in',
			) as $option => $name ) {
				if ( ! empty( $list->options[ $option ] ) ) {
					$opt = wp_parse_id_list( $list->options[ $option ] );
					if ( ! empty( $opt ) ) {
						$list->users_args[ $name ] = $opt;
					}
				}
			}

			$paged = isset( $_REQUEST[ 'page' . $list->id ] ) ? $_REQUEST[ 'page' . $list->id ] : 1;

			if ( ! empty( $list->options['users_limit'] ) ) {
				$list->users_args['offset'] = (int) $list->options['users_offset'] + ( $paged - 1 ) * $list->options['users_limit'];
			}
			if ( isset( $list->options['users_max'] ) && ! empty( $list->options['users_max'] ) && $list->options['users_max'] < ( $list->options['users_limit'] * $paged ) ) {
				$list->users_args['limit'] = $list->options['users_max'] - ( $list->options['users_limit'] * ( $paged - 1 ) );
			}
		}
	}
}
