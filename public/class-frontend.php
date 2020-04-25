<?php
/**
 * Frontend class
 *
 * @class W4PL_Frontend
 * @package W4 Post List
 */

defined( 'ABSPATH' ) || exit;

class W4PL_Frontend {

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
