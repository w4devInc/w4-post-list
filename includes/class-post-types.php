<?php
/**
 * List editor class.
 *
 * @class W4PL_List_Editor
 * @package W4 Post List
 */

defined( 'ABSPATH' ) || exit;

class W4PL_Post_Types {

	public function __construct() {
		add_action( 'init', array( $this, 'register_post_types' ), 4  );
	}

	public function register_post_types() {
		register_post_type( w4pl()->list_post_type(), array(
			'labels' => array(
				'name' 					=> _x( 'Lists', 'post type general name' ),
				'singular_name' 		=> _x( 'List', 'post type singular name' ),
				'menu_name'				=> __( 'W4 Post List', 'w4pl' ),
				'all_items'				=> __( 'All Lists', 'w4pl' ),
				'add_new' 				=> _x( 'Add New', 'note item' ),
				'add_new_item' 			=> __( 'New List', 'w4pl' ),
				'edit_item' 			=> __( 'Edit List', 'w4pl' ),
				'new_item' 				=> __( 'New List', 'w4pl' ),
				'view_item' 			=> __( 'View List', 'w4pl' ),
				'search_items' 			=> __( 'Search List', 'w4pl' ),
				'not_found' 			=> __( 'No list found', 'w4pl' ),
				'not_found_in_trash' 	=> __( 'No lists found in Trash', 'w4pl' ),
				'parent_item_colon' 	=> ''
			 ),
			'show_ui'  				=> true,
			'show_in_rest'  		=> true,
			'rewrite'  				=> array( 'slug' => 'list' ),
			'public'  				=> true,
			'has_archive'			=> false,
			'delete_with_user'		=> false,
			'show_in_admin_bar'		=> false,
			'supports' 				=> array( 'title' ),
			'menu_icon'				=> 'dashicons-list-view'
		 ) );

		if ( get_option( 'w4pl_flush_rules' ) ) {
			$deleted = delete_option( 'w4pl_flush_rules' );
			if ( true === $deleted ) {
				flush_rewrite_rules();
			}
		}
	}
}
