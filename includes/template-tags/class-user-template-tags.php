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
				'desc'     => '<strong>Output</strong>: user id',
			),
			'user_name'   => array(
				'group'    => 'User',
				'callback' => array( 'W4PL_User_Template_Tags', 'user_name' ),
				'desc'     => '<strong>Output</strong>: user name',
			),
			'user_email'  => array(
				'group'    => 'User',
				'callback' => array( 'W4PL_User_Template_Tags', 'user_email' ),
				'desc'     => '<strong>Output</strong>: user email',
			),
			'user_link'   => array(
				'group'    => 'User',
				'func'     => 'user_link',
				'callback' => array( 'W4PL_User_Template_Tags', 'user_link' ),
				'desc'     => '<strong>Output</strong>: user post page link',
			),
			'user_count'  => array(
				'group'    => 'User',
				'callback' => array( 'W4PL_User_Template_Tags', 'user_count' ),
				'desc'     => '<strong>Output</strong>: user posts count',
			),
			'user_bio'    => array(
				'group'    => 'User',
				'callback' => array( 'W4PL_User_Template_Tags', 'user_bio' ),
				'desc'     => '<strong>Output</strong>: user description / biography',
			),
			'user_meta'   => array(
				'group'    => 'User',
				'code'     => '[user_meta key="" multiple="0"]',
				'callback' => array( 'W4PL_User_Template_Tags', 'user_meta' ),
				'desc'     => '<strong>Output</strong>: user meta value. if return value is an array, it will be migrated to string by using explode function
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>key</strong> = (text|number), meta key name
				<br /><strong>multiple</strong> = (0|1), display meta value at multiple occurence
				<br /><strong>sep</strong> = (text), separate array meta value into string',
			),
			'user_avatar' => array(
				'group'    => 'User',
				'callback' => array( 'W4PL_User_Template_Tags', 'user_avatar' ),
				'desc'     => '<strong>Output</strong>: user avatar
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>size</strong> = (number), avatar image size, ex: 32, 64, 128',
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

	public static function user_avatar( $attr, $cont, $list ) {
		 $size = isset( $attr['size'] ) ? $attr['size'] : '96';
		return get_avatar( $list->current_user->ID, $size );
	}
}
