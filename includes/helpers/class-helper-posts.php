<?php
/**
 * Posts query integration
 *
 * @class W4PL_Helper_Posts
 * @package W4_Post_List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Meta query implementor class
 *
 * @class W4PL_Helper_Posts
 */
class W4PL_Helper_Posts {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'w4pl/pre_get_options', array( $this, 'pre_get_options' ) );
		add_filter( 'w4pl/list_edit_form_fields', array( $this, 'list_edit_form_fields' ), 10, 2 );
		add_filter( 'w4pl/parse_query_args', array( $this, 'parse_query_args' ), 10 );
	}

	/**
	 * Post query control field on list editor
	 *
	 * @param  array $fields  List editor fields.
	 * @param  array $options List options.
	 * @return array          List editor fields.
	 */
	public function list_edit_form_fields( $fields, $options ) {
		$list_type = $options['list_type'];
		if ( ! in_array( $list_type, array( 'posts', 'terms.posts', 'users.posts' ) ) ) {
			return $fields;
		}

		/* GROUP 2 */
		$fields['before_field_group_query'] = array(
			'position' => '51',
			'html'     => '<div id="w4pl_field_group_query" class="w4pl_field_group">
 								<div class="w4pl_group_title">Posts</div>
 								<div class="w4pl_group_fields"><div class="w4pl_group_fields_wrap">',
		);

		$fields['post_type'] = array(
			'position'    => '55',
			'option_name' => 'post_type',
			'name'        => 'w4pl[post_type]',
			'label'       => __( 'Post type', 'w4-post-list' ),
			'type'        => 'checkbox',
			'option'      => W4PL_Config::post_type_options(),
			'input_class' => 'w4pl_onchange_lfr',
		);

		// Mime type choices.
		$mime_type_options = W4PL_Config::post_mime_type_options( $options['post_type'] );
		if ( ! empty( $mime_type_options ) ) {
			$fields['post_mime_type'] = array(
				'position'    => '56',
				'option_name' => 'post_mime_type',
				'name'        => 'w4pl[post_mime_type]',
				'label'       => __( 'Post mime type', 'w4-post-list' ),
				'type'        => 'checkbox',
				'option'      => $mime_type_options,
				'desc'        => 'if displaying attachment, choose mime type to restrcit result to specific file types.',
			);
		}

		$fields['post_status'] = array(
			'position'    => '60',
			'option_name' => 'post_status',
			'name'        => 'w4pl[post_status]',
			'label'       => __( 'Post status', 'w4-post-list' ),
			'type'        => 'checkbox',
			'option'      => array(
				'any'     => __( 'Any', 'w4-post-list' ),
				'publish' => __( 'Publish', 'w4-post-list' ),
				'pending' => __( 'Pending', 'w4-post-list' ),
				'future'  => __( 'Future', 'w4-post-list' ),
				'inherit' => __( 'Inherit', 'w4-post-list' ),
			),
		);

		$fields['post_s']          = array(
			'position'    => '64',
			'option_name' => 'post_s',
			'name'        => 'w4pl[post_s]',
			'label'       => __( 'Search keywords', 'w4-post-list' ),
			'type'        => 'text',
			'input_class' => 'widefat',
			'desc'        => 'search posts',
		);
		$fields['post__in']        = array(
			'position'    => '65',
			'option_name' => 'post__in',
			'name'        => 'w4pl[post__in]',
			'label'       => __( 'Include posts', 'w4-post-list' ),
			'type'        => 'text',
			'input_class' => 'widefat',
			'desc'        => 'comma separated post id',
		);
		$fields['post__not_in']    = array(
			'position'    => '66',
			'option_name' => 'post__not_in',
			'name'        => 'w4pl[post__not_in]',
			'label'       => __( 'Exclude posts', 'w4-post-list' ),
			'type'        => 'text',
			'input_class' => 'widefat',
			'desc'        => 'comma separated post id',
		);
		$fields['post_parent__in'] = array(
			'position'    => '67',
			'option_name' => 'post_parent__in',
			'name'        => 'w4pl[post_parent__in]',
			'label'       => __( 'Post parent', 'w4-post-list' ),
			'type'        => 'text',
			'input_class' => 'widefat',
			'desc'        => 'display child posts. comma separated parent post ids',
		);
		$fields['author__in']      = array(
			'position'    => '68',
			'option_name' => 'author__in',
			'name'        => 'w4pl[author__in]',
			'label'       => __( 'Post author', 'w4-post-list' ),
			'type'        => 'text',
			'input_class' => 'widefat',
			'desc'        => 'comma separated user/author ids. use 0 to indicate current user.',
		);
		$fields['author__not_in']  = array(
			'position'    => '68.1',
			'option_name' => 'author__not_in',
			'name'        => 'w4pl[author__not_in]',
			'label'       => __( 'Exclude post author', 'w4-post-list' ),
			'type'        => 'text',
			'input_class' => 'widefat',
			'desc'        => 'comma separated user/author ids. use 0 to indicate current user.',
		);

		$fields['exclude_self']  = array(
			'position'    => '69',
			'option_name' => 'exclude_self',
			'name'        => 'w4pl[exclude_self]',
			'label'       => __( 'Exclude self', 'w4-post-list' ),
			'type'        => 'radio',
			'option'      => array(
				''    => 'No',
				'yes' => 'Yes',
			),
			'desc'        => 'exclude current post on single post/page pages',
		);
		$fields['child_of_self'] = array(
			'position'    => '69.1',
			'option_name' => 'child_of_self',
			'name'        => 'w4pl[child_of_self]',
			'label'       => __( 'Child of self', 'w4-post-list' ),
			'type'        => 'radio',
			'option'      => array(
				''    => 'No',
				'yes' => 'Yes',
			),
			'desc'        => 'display child posts of current post/page/custom post type',
		);

		$fields['orderby'] = array(
			'position'    => '70',
			'option_name' => 'orderby',
			'name'        => 'w4pl[orderby]',
			'label'       => __( 'Orderby', 'w4-post-list' ),
			'type'        => 'select',
			'option'      => W4PL_Config::post_orderby_options( $options['post_type'] ),
			'input_after' => '<div id="orderby_meta_key_wrap">Meta key: <input name="w4pl[orderby_meta_key]" type="text" value="'
				. ( isset( $options['orderby_meta_key'] ) ? esc_attr( $options['orderby_meta_key'] ) : '' ) . '" /></div>',
		);
		$fields['order']   = array(
			'position'    => '71',
			'option_name' => 'order',
			'name'        => 'w4pl[order]',
			'label'       => __( 'Order', 'w4-post-list' ),
			'type'        => 'radio',
			'option'      => array(
				'ASC'  => 'ASC',
				'DESC' => 'DESC',
			),
		);

		$fields['limit'] = array(
			'position'    => '76',
			'option_name' => 'limit',
			'name'        => 'w4pl[limit]',
			'label'       => __( 'Maximum items', 'w4-post-list' ),
			'type'        => 'text',
			'desc2'       => 'maximum results to display in total, restrict number of items even while paginating',
		);

		if ( 'posts' === $options['list_type'] ) {
			$fields['offset']         = array(
				'position'    => '77',
				'option_name' => 'offset',
				'name'        => 'w4pl[offset]',
				'label'       => __( 'Offset', 'w4-post-list' ),
				'type'        => 'text',
				'desc2'       => 'skip given number of posts from beginning. while building lists using the same filters but to display on separate places, use offset to skip the previously displayed amount of posts',
			);
			$fields['posts_per_page'] = array(
				'position'    => '75',
				'option_name' => 'posts_per_page',
				'name'        => 'w4pl[posts_per_page]',
				'label'       => __( 'Items per page', 'w4-post-list' ),
				'type'        => 'text',
				'placeholder' => get_option( 'posts_per_page' ),
				'desc'        => 'number of items to show per page
 				<br />use <strong>-1</strong> to display all',
			);
		}

		if ( 'posts' === $options['list_type'] ) {
			$fields['groupby'] = array(
				'position'    => '95',
				'option_name' => 'groupby',
				'name'        => 'w4pl[groupby]',
				'label'       => __( 'Group by', 'w4-post-list' ),
				'type'        => 'select',
				'option'      => W4PL_Config::post_groupby_options( $options['post_type'] ),
				'input_class' => 'w4pl_onchange_lfr',
			);

			if ( in_array( $options['groupby'], array( 'year', 'month', 'yearmonth' ) ) ) {
				$fields['groupby_time'] = array(
					'position'    => '95.5',
					'option_name' => 'groupby_time',
					'name'        => 'w4pl[groupby_time]',
					'label'       => __( 'Group by Date', 'w4-post-list' ),
					'type'        => 'radio',
					'option'      => array(
						'post_date'     => 'Publish date',
						'post_modified' => 'Modified date',
					),
					'desc2'       => 'which date we will use to caculate the group time',
				);
			} elseif ( in_array( $options['groupby'], array( 'meta_value' ) ) ) {
				$fields['groupby_meta_key'] = array(
					'position'    => '95.5',
					'option_name' => 'groupby_meta_key',
					'name'        => 'w4pl[groupby_meta_key]',
					'label'       => __( 'Group by "Custom field" name', 'w4-post-list' ),
					'type'        => 'text',
				);
			}

			$fields['group_order'] = array(
				'position'    => '96',
				'option_name' => 'group_order',
				'name'        => 'w4pl[group_order]',
				'label'       => __( 'Group Order', 'w4-post-list' ),
				'type'        => 'radio',
				'option'      => array(
					''     => 'None',
					'ASC'  => 'ASC',
					'DESC' => 'DESC',
				),
			);
		}

		$fields['after_field_group_query'] = array(
			'position' => '100',
			'html'     => '</div><!--.w4pl_group_fields_wrap--></div><!--.w4pl_group_fields--></div><!--#w4pl_field_group_query-->',
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

		if ( isset( $options['list_type'] ) && in_array( $options['list_type'], array( 'posts', 'terms.posts', 'users.posts' ), true ) ) {
			$options = wp_parse_args(
				$options,
				array(
					'post_type'        => array( 'post' ),
					'post_status'      => array( 'publish' ),
					'post_s'           => '',
					'post__in'         => '',
					'post__not_in'     => '',
					'post_parent__in'  => '',
					/* author */
					'author__in'       => '',
					'author__not_in'   => '',

					'exclude_self'     => '',
					'child_of_self'    => '',

					'posts_per_page'   => '',
					'limit'            => '',
					'offset'           => '',
					'orderby'          => 'date',
					'order'            => 'DESC',
					'groupby'          => '',
					'groupby_time'     => '',
					'groupby_meta_key' => '',
					'group_order'      => '',
				)
			);

			if ( ! empty( $options['post_type'] ) && ! is_array( $options['post_type'] ) ) {
				$options['post_type'] = array( $options['post_type'] );
			}
		}

		return $options;
	}

	/**
	 * Filter options
	 *
	 * @param  object $list List options.
	 */
	public function parse_query_args( $list ) {
		if ( in_array( $list->options['list_type'], array( 'posts', 'terms.posts', 'users.posts' ) ) ) {
			// push default options to query var.
			foreach ( array(
				'orderby',
				'order',
				'posts_per_page',
				'offset',
			) as $option_name ) {
				if ( ! empty( $list->options[ $option_name ] ) ) {
					$list->posts_args[ $option_name ] = $list->options[ $option_name ];
				}
			}

			if ( ! empty( $list->options['post_s'] ) ) {
				$list->posts_args['s'] = $list->options['post_s'];
			}

			// array
			foreach ( array(
				'post_mime_type',
				'post_type',
				'post_status',
			) as $option_name ) {
				if ( ! empty( $list->options[ $option_name ] ) ) {
					$list->posts_args[ $option_name ] = $list->options[ $option_name ];
				}
			}

			// comma separated ids
			foreach ( array(
				'post__in',
				'post__not_in',
				'post_parent__in',
			) as $option_name ) {
				if ( ! empty( $list->options[ $option_name ] ) ) {
					$opt = wp_parse_id_list( $list->options[ $option_name ] );
					if ( ! empty( $opt ) ) {
						$list->posts_args[ $option_name ] = $opt;
					}
				}
			}

			// comma separated ids
			foreach ( array(
				'author__in',
				'author__not_in',
			) as $option_name ) {
				if ( $list->options[ $option_name ] === '0' ) {
					$list->options[ $option_name ] = array( '0' );
				}

				if ( ! empty( $list->options[ $option_name ] ) ) {

					$opt = wp_parse_id_list( $list->options[ $option_name ] );
					foreach ( $opt as $k => $v ) {
						if ( 0 === $v && get_current_user_id() ) {
							$opt[ $k ] = get_current_user_id();
						}
					}

					$opt = array_unique( $opt );
					$opt = array_map( 'absint', $opt );

					// if nothing matches, query no posts
					if ( count( $opt ) === 1 && $opt[0] === 0 && $option_name === 'author__in' ) {
						$list->posts_args['post__in'] = array( 0 );
					} else {
						$list->posts_args[ $option_name ] = $opt;
					}
				}
			}

			// Exclude current post.
			if ( is_singular() && get_the_ID() ) {
				if ( isset( $list->options['exclude_self'] ) && 'yes' === $list->options['exclude_self'] ) {
					if ( ! isset( $list->posts_args['post__not_in'] ) || empty( $list->posts_args['post__not_in'] ) ) {
						$list->posts_args['post__not_in'] = array( get_the_ID() );
					} elseif ( is_array( $list->posts_args['post__not_in'] ) ) {
						$list->posts_args['post__not_in'][] = get_the_ID();
					}
				}

				if ( isset( $list->options['child_of_self'] ) && 'yes' === $list->options['child_of_self'] ) {
					if ( ! isset( $list->posts_args['post_parent__in'] ) || empty( $list->posts_args['post_parent__in'] ) ) {
						$list->posts_args['post_parent__in'] = array( get_the_ID() );
					} elseif ( is_array( $list->posts_args['post_parent__in'] ) ) {
						$list->posts_args['post_parent__in'][] = get_the_ID();
					}
				}
			}

			// Orderby meta key/value.
			if ( $list->options['orderby'] === 'meta_value' || $list->options['orderby'] === 'meta_value_num' ) {
				$list->posts_args['meta_key'] = $list->options['orderby_meta_key'];
			}

			// We handle paged query using an unique query parameter.
			$paged = isset( $_REQUEST[ 'page' . $list->id ] ) ? wp_unslash( $_REQUEST[ 'page' . $list->id ] ) : 1;

			$defaults = array(
				'post_status' => 'publish',
				'post_type'   => 'post',
				'paged'       => $paged,
			);

			$list->posts_args = wp_parse_args( $list->posts_args, $defaults );

			// set the posts per page.
			if ( ! isset( $list->posts_args['posts_per_page'] ) || empty( $list->posts_args['posts_per_page'] ) ) {
				$list->posts_args['posts_per_page'] = get_option( 'posts_per_page', 10 );
			}

			// while maximum limit is set, we only fetch till the maximum post.
			if ( ! empty( $list->options['limit'] ) && $list->options['limit'] < ( $list->posts_args['posts_per_page'] * $paged ) ) {
				$list->posts_args['offset']         = (int) $list->options['offset'] + ( ( $paged - 1 ) * $list->posts_args['posts_per_page'] );
				$list->posts_args['posts_per_page'] = $list->options['limit'] - ( $list->posts_args['posts_per_page'] * ( $paged - 1 ) );
			} elseif ( ! empty( $list->options['offset'] ) ) {
				// while maximum limit is set, we only fetch till the maximum post.
				$list->posts_args['offset'] = (int) $list->options['offset'] + ( $paged - 1 ) * $list->posts_args['posts_per_page'];
			}
		}
	}
}
