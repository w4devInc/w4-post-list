<?php
/**
 * Admin main class.
 *
 * @class W4PL_Admin_Main
 * @package W4_Post_List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main admin class
 */
class W4PL_Admin_Main {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'plugin_action_links_' . W4PL_BASENAME, array( $this, 'plugin_action_links' ), 20 );

		// set update message for our post type, you dont like to use - "post update" !
		add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );
	}

	/**
	 * Include post list & documentation link
	 *
	 * @param  array $links Action links.
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$links['lists'] = '<a href="edit.php?post_type=w4pl">' . __( 'Lists', 'w4-post-list' ) . '</a>';
		$links['doc']   = '<a href="edit.php?post_type=w4pl&page=w4pl-docs">' . __( 'Docs', 'w4-post-list' ) . '</a>';
		return $links;
	}

	/**
	 * Post list updated message overring default wp post update message
	 *
	 * @param  array $messages Array of messages.
	 * @return array
	 */
	public function post_updated_messages( $messages ) {
		global $post_ID;

		$input_attr = sprintf(
			'<input value="[postlist id=%d]" type="text" size="20" onfocus="this.select();" onclick="this.select();" readonly="readonly />"',
			$post_ID
		);

		$messages[ W4PL_Config::LIST_POST_TYPE ] = array(
			/* translators: %s: html input field to copy shortcode */
			1  => sprintf( __( 'List updated. Shortcode %s', 'w4-post-list' ), $input_attr ),
			2  => '',
			3  => '',
			4  => __( 'List updated.', 'w4-post-list' ),
			5  => '',
			/* translators: %s: html input field to copy shortcode */
			6  => sprintf( __( 'List published. Shortcode %s', 'w4-post-list' ), $input_attr ),
			7  => __( 'List saved.', 'w4-post-list' ),
			/* translators: %s: html input field to copy shortcode */
			8  => sprintf( __( 'List submitted. Shortcode %s', 'w4-post-list' ), $input_attr ),
			/* translators: %s: html input field to copy shortcode */
			9  => sprintf( __( 'List scheduled. Shortcode %s', 'w4-post-list' ), $input_attr ),
			10 => '',
		);

		return $messages;
	}
}
