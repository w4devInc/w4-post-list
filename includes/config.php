<?php
/**
 * Configuration Class
 * @package WordPress
 * @subpackage W4 POst List
 * @author Shazzad
 * @url http://w4dev.com/about
**/



class W4PL_Config
{
	public static function list_type_options()
	{
		$return = array(
			'posts' 		=> __('Posts', 'w4pl') .' - <small>'. implode(', ', W4PL_Plugin::post_type_options()) .'</small>',
			'terms' 		=> __('Terms', 'w4pl') .' - <small>'. implode(', ', W4PL_Plugin::taxonomies_options()) .'</small>',
			'users' 		=> __('Users', 'w4pl'),
			'terms.posts' 	=> __('Terms + Posts', 'w4pl'),
			'users.posts' 	=> __('Users + Posts', 'w4pl')
		);

		return $return;
	}
}


?>
