<?php
/**
 * List content class
 *
 * @class W4PL_List_Content
 * @package W4_Post_List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// TODO - move the filter to other class, and discard this file.

class W4PL_List_Content {

	function __construct() {
		// filter list options at higher priority
		add_filter( 'the_content', array( $this, 'list_content'), 5 );
	}

	/**
	 * Override list content to contain postlist shortcode only.
	 *
	 * @param string $content list content
	 * @return string
	 */
	public function list_content( $content ) {
		if ( 'w4pl' == get_post_type() ) {
			$content = '[postlist id="'. get_the_ID() .'"]';
		}

		return $content;
	}
}
