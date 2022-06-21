<?php
/**
 * Configuration class.
 *
 * @class W4PL_Config
 * @package W4_Post_List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Configuration class
 */
class W4PL_Config {

	const LIST_POST_TYPE = 'w4pl';

	/**
	 * Post orderby options
	 *
	 * @param  array $post_types Array of post type names.
	 * @return array             Array of available orderby choices.
	 */
	public static function post_orderby_options( $post_types = array() ) {
		$return = array(
			'ID'             => __( 'ID', 'w4-post-list' ),
			'title'          => __( 'Title', 'w4-post-list' ),
			'name'           => __( 'Name', 'w4-post-list' ),
			'date'           => __( 'Publish Date', 'w4-post-list' ),
			'modified'       => __( 'Modified Date', 'w4-post-list' ),
			'menu_order'     => __( 'Menu Order', 'w4-post-list' ),
			'meta_value'     => __( 'Meta value', 'w4-post-list' ),
			'meta_value_num' => __( 'Meta numeric value', 'w4-post-list' ),
			'comment_count'  => __( 'Comment Count', 'w4-post-list' ),
			'rand'           => __( 'Random', 'w4-post-list' ),
			'none'           => __( 'None', 'w4-post-list' ),
			'post__in'       => __( 'Include posts', 'w4-post-list' ),
		);

		return $return;
	}

	/**
	 * Post groupby choices
	 *
	 * @param  array $post_types [description].
	 * @return array             [description].
	 */
	public static function post_groupby_options( $post_types = array() ) {
		$return = array(
			''           => __( 'None', 'w4-post-list' ),
			'year'       => __( 'Year', 'w4-post-list' ),
			'month'      => __( 'Month', 'w4-post-list' ),
			'yearmonth'  => __( 'Year Months', 'w4-post-list' ),
			'author'     => __( 'Author', 'w4-post-list' ),
			'parent'     => __( 'Parent', 'w4-post-list' ),
			'meta_value' => __( 'Custom field', 'w4-post-list' ),
		);

		if ( ! is_array( $post_types ) ) {
			$post_types = array( $post_types );
		}

		if ( ! empty( $post_types ) && is_array( $post_types ) ) {
			foreach ( $post_types as $post_type ) {
				foreach ( get_object_taxonomies( $post_type, 'all' ) as $taxonomy => $taxonomy_object ) {
					if ( 'post_format' === $taxonomy || ! $taxonomy_object->public ) {
						continue;
					}
					$return[ 'tax_' . $taxonomy ] = $taxonomy_object->labels->name;
				}
			}
		}

		return $return;
	}

	/**
	 * Post type choices
	 *
	 * @return array [description]
	 */
	public static function post_type_options() {
		global $wp_post_types;

		$return = array();
		foreach ( $wp_post_types as $post_type => $post_type_object ) {
			// exclude the list post type.
			if ( ! in_array( $post_type, array( self::LIST_POST_TYPE, 'revision', 'nav_menu_item' ), true ) ) {
				$return[ $post_type ] = $post_type_object->labels->name;
			}
		}

		return $return;
	}

	/**
	 * [post_mime_type_options description]
	 *
	 * @param  string $post_types [description].
	 * @return [type]             [description].
	 */
	public static function post_mime_type_options( $post_types = '' ) {
		global $wpdb;
		if ( empty( $post_types ) ) {
			$post_types = array( 'post' );
		} elseif ( ! is_array( $post_types ) ) {
			$post_types = array( $post_types );
		}

		$placeholders = implode( ',', array_fill( 0, count( $post_types ), '%s' ) );

		$cache_key  = 'w4pl_' . md5( maybe_serialize( $post_types ) );
		$mime_types = wp_cache_get( $cache_key );

		if ( false === $mime_types ) {
			$mime_types = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT DISTINCT post_mime_type FROM $wpdb->posts WHERE post_status != 'trash' AND post_type IN ($placeholders) AND post_mime_type <> ''",
					$post_types
				)
			);
			wp_cache_set( $cache_key, $mime_types );
		}

		if ( ! empty( $mime_types ) ) {
			$return = array( '' => 'Any' );
			foreach ( $mime_types as $mime_type ) {
				if ( ! empty( $mime_type ) ) {
					$return[ $mime_type ] = $mime_type;
				}
			}
			return $return;
		}

		return array();
	}

	/**
	 * List type choices
	 *
	 * @return [type] [description]
	 */
	public static function list_type_options() {
		return array(
			'posts'       => __( 'Posts', 'w4-post-list' ) . ' - <small>' . implode( ', ', self::post_type_options() ) . '</small>',
			'terms'       => __( 'Terms', 'w4-post-list' ) . ' - <small>' . implode( ', ', W4PL_Utils::taxonomies_options() ) . '</small>',
			'users'       => __( 'Users', 'w4-post-list' ),
			'terms.posts' => __( 'Terms + Posts', 'w4-post-list' ),
			'users.posts' => __( 'Users + Posts', 'w4-post-list' ),
		);
	}

	/**
	 * Date query columns
	 *
	 * @return [type] [description]
	 */
	public static function date_query_column_choices() {
		return array(
			'post_date'         => 'post_date',
			'post_date_gmt'     => 'post_date_gmt',
			'post_modified'     => 'post_modified',
			'post_modified_gmt' => 'post_modified_gmt',
			'comment_date'      => 'comment_date',
			'comment_date_gmt'  => 'comment_date_gmt',
			'user_registered'   => 'user_registered',
		);
	}

	/**
	* Date query keys
	 *
	 * @return [type] [description]
	 */
	public static function date_query_key_choices() {
		return array(
			'after'         => 'after',
			'before'        => 'before',
			'year'          => 'year',
			'month'         => 'month',
			'monthnum'      => 'monthnum',
			'week'          => 'week',
			'w'             => 'w',
			'dayofyear'     => 'dayofyear',
			'dayofweek'     => 'dayofweek',
			'dayofweek_iso' => 'dayofweek_iso',
			'hour'          => 'hour',
			'minute'        => 'minute',
			'second'        => 'second',
		);
	}

	public static function date_query_compare_choices() {
		$return = array( '=', '!=', '>', '>=', '<', '<=', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN' );
		$return = array_combine( $return, $return );

		return $return;
	}

	public static function meta_query_compare_options() {
		$return = array( '=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'NOT EXISTS', 'REGEXP', 'NOT REGEXP', 'RLIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN' );
		$return = array_combine( $return, $return );

		return $return;
	}

	public static function post_type_taxonomies_choices( $post_type ) {
		$return = array();
		foreach ( get_object_taxonomies( $post_type, 'all' ) as $taxonomy => $taxonomy_object ) {
			if ( ! $taxonomy_object->public ) {
				continue;
			}

			$return[ $taxonomy ] = $taxonomy_object->labels->name;
		}
		return $return;
	}

	public static function tax_query_operator_options() {
		$return = array( '=', '!=', 'IN', 'NOT IN' );
		$return = array_combine( $return, $return );

		return $return;
	}

	public static function tax_query_field_options() {
		return array(
			'term_id' => 'Term id',
			'name'    => 'Name',
			'slug'    => 'Slug',
		);
	}

	public static function terms_orderby_options() {
		 return array(
			 'term_id'    => __( 'ID', 'w4-post-list' ),
			 'name'       => __( 'Name', 'w4-post-list' ),
			 'slug'       => __( 'Slug', 'w4-post-list' ),
			 'count'      => __( 'Count', 'w4-post-list' ),
			 'term_order' => __( 'Term order', 'w4-post-list' ),
			 'custom'     => __( 'Include terms', 'w4-post-list' ),
		 );
	}

	public static function users_orderby_options() {
		return array(
			'ID'              => __( 'ID', 'w4-post-list' ),
			'user_login'      => __( 'Username', 'w4-post-list' ),
			'user_nicename'   => __( 'Nicename', 'w4-post-list' ),
			'user_email'      => __( 'Email', 'w4-post-list' ),
			'user_registered' => __( 'Registration time', 'w4-post-list' ),
			'display_name'    => __( 'Display name', 'w4-post-list' ),
		);
	}
}
