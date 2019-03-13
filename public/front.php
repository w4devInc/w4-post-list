<?php
/**
 * @package W4 Post List
 * @author Shazzad Hossain Khan
 * @url http://w4dev.com/plugins/w4-post-list
**/


class W4PL_Front
{
	function __construct()
	{
		// filter list options at higher priority
		add_filter( 'the_content', 								__CLASS__ . '::the_content', 5 );
	}

	public static function the_content( $content )
	{
		if( 'w4pl' == get_post_type() ){
			$content = '[postlist id="'. get_the_ID() .'"]';
		}
		return $content;
	}
}

	new W4PL_Front;
?>
