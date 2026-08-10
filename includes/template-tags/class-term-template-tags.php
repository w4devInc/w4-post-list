<?php
/**
 * Terms query integration
 *
 * @class W4PL_Term_Template_Tags
 * @package W4_Post_List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Meta query implementor class
 *
 * @class W4PL_Term_Template_Tags
 */
class W4PL_Term_Template_Tags {

	function __construct() {
		add_filter( 'w4pl/get_shortcodes', array( $this, 'get_shortcodes' ), 21 );
	}


	/* Register User Shortcodes */
	public static function get_shortcodes( $shortcodes ) {
		$_shortcodes = array(
			'term_id'      => array(
				'group'    => 'Term',
				'callback' => array( 'W4PL_Term_Template_Tags', 'term_id' ),
				'output'   => __( 'Term id', 'w4-post-list' ),
			),
			'term_name'    => array(
				'group'    => 'Term',
				'callback' => array( 'W4PL_Term_Template_Tags', 'term_name' ),
				'output'   => __( 'Term name', 'w4-post-list' ),
			),
			'term_slug'    => array(
				'group'    => 'Term',
				'callback' => array( 'W4PL_Term_Template_Tags', 'term_slug' ),
				'output'   => __( 'Term slug', 'w4-post-list' ),
			),
			'term_link'    => array(
				'group'    => 'Term',
				'callback' => array( 'W4PL_Term_Template_Tags', 'term_link' ),
				'output'   => __( 'Term archive permalink', 'w4-post-list' ),
			),
			'term_count'   => array(
				'group'    => 'Term',
				'callback' => array( 'W4PL_Term_Template_Tags', 'term_count' ),
				'output'   => __( 'Number of posts in the term', 'w4-post-list' ),
			),
			'term_content' => array(
				'group'    => 'Term',
				'callback' => array( 'W4PL_Term_Template_Tags', 'term_content' ),
				'output'   => __( 'Term description', 'w4-post-list' ),
			),
		);

		return array_merge( $shortcodes, $_shortcodes );
	}


	/* Term Shortcode Callbacks */
	public static function term_id( $attr, $cont, $list ) {
		return isset( $list->current_term ) ? $list->current_term->term_id : 0;
	}
	public static function term_name( $attr, $cont, $list ) {
		return isset( $list->current_term ) ? $list->current_term->name : '';
	}
	public static function term_slug( $attr, $cont, $list ) {
		return isset( $list->current_term ) ? $list->current_term->slug : '';
	}
	public static function term_link( $attr, $cont, $list ) {
		return isset( $list->current_term ) ? get_term_link( $list->current_term ) : '';
	}
	public static function term_count( $attr, $cont, $list ) {
		return isset( $list->current_term ) ? $list->current_term->count : 0;
	}
	public static function term_content( $attr, $cont, $list ) {
		return isset( $list->current_term ) ? $list->current_term->description : '';
	}
}
