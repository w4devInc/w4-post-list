<?php
/**
 * List editor class.
 *
 * @class W4PL_List_Editor
 * @package W4 Post List
 */

defined(  'ABSPATH'  ) || exit;

class W4PL_Admin_Main {

	public function __construct() {
		add_filter(  'plugin_action_links_' . w4pl()->plugin_basename(), array(  $this, 'plugin_action_links'  ), 20  );

		// set update message for our post type, you dont like to use - "post update" !
		add_filter(  'post_updated_messages', array(  $this, 'post_updated_messages'  )  );
	}

	public function plugin_action_links(  $links  ) {
		$links['lists'] = '<a href="'. 'edit.php?post_type=w4pl">' . __(  'Lists', 'w4pl'  ). '</a>';
		$links['doc'] = '<a href="'. 'edit.php?post_type=w4pl&page=w4pl-docs">' . __(  'Docs', 'w4pl'  ). '</a>';
		return $links;
	}

	public function post_updated_messages(  $messages  ) {
		global $post_ID, $post;

		$input_attr = sprintf(
			'<input value="[postlist id=%d]" type="text" size="20" onfocus="this.select();" onclick="this.select();" readonly="readonly />"',
			$post_ID
		);

		$messages[ w4pl()->list_post_type() ] = array(
			/* translators: %s: html input field to copy shortcode */
			1 => sprintf(  __( 'List updated. Shortcode %s', 'w4pl' ), $input_attr  ),
			2 => '',
			3 => '',
			4 => __( 'List updated.', 'w4pl' ),
			5 => '',
			/* translators: %s: html input field to copy shortcode */
			6 => sprintf(  __( 'List published. Shortcode %s', 'w4pl' ), $input_attr  ),
			7 => __( 'List saved.', 'w4pl' ),
			/* translators: %s: html input field to copy shortcode */
			8 => sprintf(  __( 'List submitted. Shortcode %s', 'w4pl' ), $input_attr  ),
			/* translators: %s: html input field to copy shortcode */
			9 => sprintf(  __( 'List scheduled. Shortcode %s', 'w4pl' ), $input_attr  ),
			10 => ''
		);

		return $messages;
	}
}
