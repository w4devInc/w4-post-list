<?php
/**
 * Register post types.
 *
 * @class W4PL_Post_Types
 * @package W4_Post_List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register list post type
 */
class W4PL_Post_Types {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_post_types' ), 4 );
	}

	/**
	 * Register list as post type
	 */
	public function register_post_types() {
		register_post_type(
			'w4pl', // W4PL_Config::LIST_POST_TYPE.
			array(
				'labels'            => array(
					'name'               => _x( 'Lists', 'post type general name', 'w4-post-list' ),
					'singular_name'      => _x( 'List', 'post type singular name', 'w4-post-list' ),
					'menu_name'          => __( 'W4 Post List', 'w4-post-list' ),
					'all_items'          => __( 'All Lists', 'w4-post-list' ),
					'add_new'            => _x( 'Add New', 'note item', 'w4-post-list' ),
					'add_new_item'       => __( 'New List', 'w4-post-list' ),
					'edit_item'          => __( 'Edit List', 'w4-post-list' ),
					'new_item'           => __( 'New List', 'w4-post-list' ),
					'view_item'          => __( 'View List', 'w4-post-list' ),
					'search_items'       => __( 'Search List', 'w4-post-list' ),
					'not_found'          => __( 'No list found', 'w4-post-list' ),
					'not_found_in_trash' => __( 'No lists found in Trash', 'w4-post-list' ),
					'parent_item_colon'  => '',
				),
				'show_ui'           => true,
				'show_in_rest'      => true,
				'rewrite'           => array( 'slug' => 'list' ),
				'public'            => true,
				'has_archive'       => false,
				'delete_with_user'  => false,
				'show_in_admin_bar' => false,
				'supports'          => array( 'title' ),
				'menu_icon'         => 'dashicons-list-view',
			)
		);

		if ( get_option( 'w4pl_flush_rules' ) ) {
			$deleted = delete_option( 'w4pl_flush_rules' );
			if ( true === $deleted ) {
				flush_rewrite_rules();
			}
		}
	}
}
