<?php
/**
 * List editor class.
 *
 * @class W4PL_List_Editor
 * @package W4 Post List
 */

defined( 'ABSPATH' ) || exit;

class W4PL_List_Templates {

	public function __construct() {
		add_filter( 'w4pl/template_default' 									, array($this, 'template_default') );
		add_filter( 'w4pl/template'												, array($this, 'template'), 20, 2 );
	}

	// default templates
	public function template_default($r)
	{
		return '<ul>[posts]'. "\n" . '<li>'. "\n" . '[title]'. "\n" . '[excerpt wordlimit=20]' . "\n" . '[more]' . "\n".'</li>'. "\n" . '[/posts]</ul>';
	}

	// default templates
	public function template( $template, $opt )
	{
		if( !isset($opt['list_type']) || empty($opt['list_type']) )
			return $template;

		$users 			= '<ul>[users]'. "\n" . '<li>'. "\n" . '<a href="[user_link]">[user_name]</a>' . "\n".'</li>'. "\n" . '[/users]</ul>';
		$terms 			= '<ul>[terms]'. "\n" . '<li>'. "\n" . '<a href="[term_link]">[term_name]</a>' . "\n".'</li>'. "\n" . '[/terms]</ul>';
		$posts 			= '<ul>[posts]'. "\n" . '<li>'. "\n" . '[title]'. "\n" . '[excerpt wordlimit=20]' . "\n" . '[more]' . "\n".'</li>'. "\n" . '[/posts]</ul>';
		$termsposts 	= '<ul>[terms]'. "\n" . '<li>'. "\n" . '<a href="[term_link]">[term_name]</a>' . "\n" . $posts . "\n" . '</li>'. "\n" . '[/terms]</ul>';
		$usersposts 	= '<ul>[users]'. "\n" . '<li>'. "\n" . '<a href="[user_link]">[user_name]</a>' . "\n" . $posts . "\n" . '</li>'. "\n" . '[/users]</ul>';

		$was_default = (bool) ( empty($template) || in_array($template, array($terms, $users, $posts, $termsposts, $usersposts) ) );
		if( ! $was_default )
			return $template;

		if( 'terms' == $opt['list_type'] ){
			$template = $terms;
		}
		elseif( 'users' == $opt['list_type'] ){
			$template = $users;
		}
		elseif( 'posts' == $opt['list_type'] ){
			$template = $posts;
		}
		elseif( 'terms.posts' == $opt['list_type'] ){
			$template = $termsposts;
		}
		elseif( 'users.posts' == $opt['list_type'] ){
			$template = $usersposts;
		}
		return $template;
	}
}
