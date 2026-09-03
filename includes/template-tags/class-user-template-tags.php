<?php
/**
 * User template tags
 *
 * @class W4PL_User_Template_Tags
 * @package W4_Post_List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Meta query implementor class
 *
 * @class W4PL_User_Template_Tags
 */
class W4PL_User_Template_Tags {
	function __construct() {
		add_filter( 'w4pl/get_shortcodes', array( $this, 'get_shortcodes' ), 21 );
	}

	/**
	 * Register User Shortcodes
	 *
	 * @param  array $shortcodes [description].
	 */
	public static function get_shortcodes( $shortcodes ) {
		$_shortcodes = array(
			'user_id'     => array(
				'group'    => 'User',
				'callback' => array( 'W4PL_User_Template_Tags', 'user_id' ),
				'output'   => __( 'User id', 'w4-post-list' ),
			),
			'user_name'   => array(
				'group'    => 'User',
				'callback' => array( 'W4PL_User_Template_Tags', 'user_name' ),
				'output'   => __( 'User display name', 'w4-post-list' ),
			),
			'user_email'  => array(
				'group'    => 'User',
				'callback' => array( 'W4PL_User_Template_Tags', 'user_email' ),
				'output'   => __( 'User email address', 'w4-post-list' ),
			),
			'user_link'   => array(
				'group'    => 'User',
				'func'     => 'user_link',
				'callback' => array( 'W4PL_User_Template_Tags', 'user_link' ),
				'output'   => __( 'Author archive permalink', 'w4-post-list' ),
			),
			'user_count'  => array(
				'group'    => 'User',
				'callback' => array( 'W4PL_User_Template_Tags', 'user_count' ),
				'output'   => __( 'Number of posts by the user', 'w4-post-list' ),
			),
			'user_bio'    => array(
				'group'    => 'User',
				'callback' => array( 'W4PL_User_Template_Tags', 'user_bio' ),
				'output'   => __( 'User biography', 'w4-post-list' ),
			),
			'user_meta'   => array(
				'group'      => 'User',
				'code'       => '[user_meta key="" multiple="0"]',
				'callback'   => array( 'W4PL_User_Template_Tags', 'user_meta' ),
				'output'     => __( 'User meta value. An array value is joined into a string.', 'w4-post-list' ),
				'parameters' => array(
					'key'      => array(
						'desc' => __( 'Meta key name', 'w4-post-list' ),
					),
					'multiple' => array(
						'desc'    => __( 'Display every occurrence of the meta value rather than the first', 'w4-post-list' ),
						'choices' => array( '0', '1' ),
					),
					'sep'      => array(
						'desc' => __( 'Separator used when joining an array meta value into a string', 'w4-post-list' ),
					),
				),
			),
			'user_role'   => array(
				'group'      => 'User',
				'code'       => '[user_role]',
				'callback'   => array( 'W4PL_User_Template_Tags', 'user_role' ),
				'output'     => __( 'The user\'s role name, for example Editor. Users with more than one role are joined into a string.', 'w4-post-list' ),
				'parameters' => array(
					'format' => array(
						'desc'    => __( 'Output the translated role name, or the raw slug for use in a CSS class', 'w4-post-list' ),
						'choices' => array( 'name', 'slug' ),
					),
					'sep'    => array(
						'desc' => __( 'Separator used when a user holds more than one role', 'w4-post-list' ),
					),
				),
			),
			'user_avatar' => array(
				'group'      => 'User',
				'callback'   => array( 'W4PL_User_Template_Tags', 'user_avatar' ),
				'output'     => __( 'User avatar image tag', 'w4-post-list' ),
				'parameters' => array(
					'size' => array(
						'desc' => __( 'Avatar image size in pixels, for example 32, 64 or 128', 'w4-post-list' ),
					),
				),
			),
		);

		return array_merge( $shortcodes, $_shortcodes );
	}


	/* User Shortcode Callbacks */

	public static function user_id( $attr, $cont, $list ) {
		return isset( $list->current_user ) ? $list->current_user->ID : 0;
	}
	public static function user_name( $attr, $cont, $list ) {
		return isset( $list->current_user ) ? $list->current_user->display_name : '';
	}
	public static function user_email( $attr, $cont, $list ) {
		return isset( $list->current_user ) ? $list->current_user->user_email : '';
	}
	public static function user_link( $attr, $cont, $list ) {
		return isset( $list->current_user ) ? get_author_posts_url( $list->current_user->ID ) : '';
	}
	public static function user_count( $attr, $cont, $list ) {
		return isset( $list->current_user ) ? count_user_posts( $list->current_user->ID ) : 0;
	}
	public static function user_bio( $attr, $cont, $list ) {
		return isset( $list->current_user ) ? get_the_author_meta( 'description', $list->current_user->ID ) : '';
	}
	public static function user_meta( $attr, $cont, $list ) {
		if ( isset( $attr ) && ! is_array( $attr ) && is_string( $attr ) ) {
			$meta_key = trim( $attr );
			$attr     = array();
		}
		if ( isset( $attr['key'] ) ) {
			$meta_key = $attr['key'];
		} elseif ( isset( $attr['meta_key'] ) ) {
			$meta_key = $attr['meta_key'];
		}
		if ( ! $meta_key ) {
			return;
		}

		$single = ! ( isset( $attr ) && is_array( $attr ) && array_key_exists( 'multiple', $attr ) ? (bool) $attr['multiple'] : true );

		$sep = ', ';
		if ( isset( $attr['sep'] ) ) {
			$sep = $attr['sep'];
		}

		$return = get_user_meta( $list->current_user->ID, $meta_key, $single );

		if ( is_array( $return ) ) {
			$new = array();
			foreach ( $return as $r => $d ) {
				if ( ! is_array( $d ) ) {
					$new[] = $d;
				}
			}
			if ( $new ) {
				$return = implode( $sep, $new );
			} else {
				$return = '';
			}
		}

		return $return;
	}

	/**
	 * Role names for the current user in the loop.
	 *
	 * @param  array  $attr Tag attributes.
	 * @param  string $cont Tag content.
	 * @param  object $list Instance of W4PL_List.
	 * @return string
	 */
	public static function user_role( $attr, $cont, $list ) {
		if ( ! isset( $list->current_user ) ) {
			return '';
		}

		// current_user is a raw wp_users row, so roles have to be looked up.
		$user = get_userdata( $list->current_user->ID );
		if ( ! $user || empty( $user->roles ) ) {
			return '';
		}

		$sep    = isset( $attr['sep'] ) ? $attr['sep'] : ', ';
		$format = isset( $attr['format'] ) ? $attr['format'] : 'name';

		if ( 'slug' === $format ) {
			return implode( $sep, $user->roles );
		}

		$names = wp_roles()->get_names();
		$out   = array();
		foreach ( $user->roles as $role ) {
			$out[] = isset( $names[ $role ] ) ? translate_user_role( $names[ $role ] ) : $role;
		}

		return implode( $sep, $out );
	}

	public static function user_avatar( $attr, $cont, $list ) {
		$size = isset( $attr['size'] ) ? $attr['size'] : '96';
		return get_avatar( $list->current_user->ID, $size );
	}
}
