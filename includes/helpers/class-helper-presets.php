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
class W4PL_Helper_Presets {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'w4pl/list_edit_form_fields', array( $this, 'list_edit_form_fields' ), 13, 2 );
		add_filter( 'w4pl/pre_get_options', array( $this, 'pre_get_options' ) );
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
		$fields['preset'] = array(
			'position'    => '3.1',
			'option_name' => 'preset',
			'name'        => 'w4pl[preset]',
			'label'       => 'Preset',
			'type'        => 'select',
			'option'      => self::preset_options( $options['list_type'] ),
			'input_class' => 'w4pl_onchange_lfr',
			'desc'        => 'preset is predefined templates',
		);

		if ( isset( $options['preset'] ) && ! empty( $options['preset'] ) ) {
			unset(
				$fields['before_field_group_style'],
				$fields['js'],
				$fields['css'],
				$fields['class'],
				$fields['after_field_group_style'],
				$fields['before_field_group_template'],
				$fields['template1'],
				$fields['after_field_group_template']
			);
		}

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

		// Quick kill.
		if ( ! isset( $options['preset'] ) || empty( $options['preset'] ) ) {
			return $options;
		}

		if ( isset( $options['preset'] ) && 'simple_list' === $options['preset'] ) {
			if ( 'posts' === $options['list_type'] ) {
				$options['template'] = '<ul>[posts]
					<li class="post-item-[post_id]"><a href="[post_permalink]">[post_title]</a></li>
				[/posts]</ul>';
			} elseif ( 'terms.posts' === $options['list_type'] ) {
				$options['template'] = '<ul>[terms]
					<li class="term-item-[term_id]">
						<a href="[term_link]">[term_name]</a>
						<ul>[posts]
							<li class="post-item-[post_id]"><a href="[post_permalink]">[post_title]</a></li>
						[/posts]</ul>
					</li>
				[/terms]</ul>';
			} elseif ( 'terms' === $options['list_type'] ) {
				$options['template'] = '<ul>[terms]
					<li class="term-item-[term_id]"><a href="[term_link]">[term_name]</a></li>
				[/terms]</ul>';
			} elseif ( 'users' === $options['list_type'] ) {
				$options['template'] = '<ul>[users]
					<li class="user-item-[user_id]"><a href="[user_link]">[user_name]</a></li>
				[/users]</ul>';
			} elseif ( 'users.posts' === $options['list_type'] ) {
				$options['template'] = '<ul>[users]
					<li class="user-item-[user_id]">
						<a href="[user_link]">[user_name]</a>
						<ul>[posts]
							<li class="post-item-[post_id]"><a href="[post_permalink]">[post_title]</a></li>
						[/posts]</ul>
					</li>
				[/users]</ul>';
			}
		}

		if ( isset( $options['preset'] ) && 'post_with_thumbnail' === $options['preset'] ) {
			if ( 'posts' === $options['list_type'] ) {
				$options['template'] = '<ul class="posts-list">[posts]
					<li class="post-item-[post_id]"><a href="[post_permalink]">[post_title]<br />[post_thumbnail size="thumbnail"]</a></li>
				[/posts]</ul>';

				$options['class'] = 'w4pl_preset_post_with_thumbnail';
				$options['css']   = '
				.w4pl_preset_post_with_thumbnail ul.posts-list li{list-style:none outside!important; margin:0 0 10px 0!important; padding:0!important;}
				.w4pl_preset_post_with_thumbnail ul.posts-list li a{text-decoration: none;}';
			} elseif ( 'terms.posts' === $options['list_type'] ) {
				$options['template'] = '<ul>[terms]
					<li class="term-item-[term_id]">
						<a href="[term_link]">[term_name]</a>
						<ul class="posts-list">[posts]
							<li class="post-item-[post_id]"><a href="[post_permalink]">[post_title]<br />[post_thumbnail size="thumbnail"]</a></li>
						[/posts]</ul>
					</li>
				[/terms]</ul>';

				$options['class'] = 'w4pl_preset_post_with_thumbnail';
				$options['css']   = '
				.w4pl_preset_post_with_thumbnail ul.posts-list li{list-style:none outside!important; margin:0 0 10px 0!important; padding:0!important;}
				.w4pl_preset_post_with_thumbnail ul.posts-list li a{text-decoration: none;}';
			} elseif ( 'users.posts' === $options['list_type'] ) {
				$options['template'] = '<ul>[users]
					<li class="user-item-[user_id]">
						<a href="[user_link]">[user_name]</a>
						<ul class="posts-list">[posts]
							<li class="post-item-[post_id]"><a href="[post_permalink]">[post_title]<br />[post_thumbnail size="thumbnail"]</a></li>
						[/posts]</ul>
					</li>
				[/users]</ul>';

				$options['class'] = 'w4pl_preset_post_with_thumbnail';
				$options['css']   = '
				.w4pl_preset_post_with_thumbnail ul.posts-list li{list-style:none outside!important; margin:0 0 10px 0!important; padding:0!important;}
				.w4pl_preset_post_with_thumbnail ul.posts-list li a{text-decoration: none;}';
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
		if ( isset( $list->options['preset'] ) && 'post_with_thumbnail' === $list->options['preset'] ) {
			if ( ! isset( $list->posts_args['meta_query'] ) || ! is_array( $list->posts_args['meta_query'] ) ) {
				$list->posts_args['meta_query'] = array();
			}
			$list->posts_args['meta_query'][] = array(
				'key'     => '_thumbnail_id',
				'compare' => 'EXISTS',
			);
		}

		return $list;
	}

	/**
	 * Preset options
	 *
	 * @param string $list_type List type.
	 */
	public function preset_options( $list_type ) {
		$presets = array(
			''            => __( 'Custom', 'w4-post-list' ),
			'simple_list' => __( 'Simple list', 'w4-post-list' ),
		);

		if ( in_array( $list_type, array( 'posts', 'terms.posts', 'users.posts' ), true ) ) {
			$presets['post_with_thumbnail'] = __( 'Posts with Thumbnail', 'w4-post-list' );
		}

		return $presets;
	}
}
