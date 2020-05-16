<?php
/**
 * Default html templates class.
 *
 * @class W4PL_List_Templates
 * @package W4_Post_List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * List templates
 */
class W4PL_List_Templates {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'w4pl/pre_get_template', array( $this, 'sanitize_template' ), 20, 2 );
		add_filter( 'w4pl/pre_save_template', array( $this, 'sanitize_template' ), 20, 2 );
	}

	/**
	 * Sanitize list template
	 *
	 * @param  string $template List template.
	 * @param  array  $options  List options.
	 * @return string           Sanitized html template.
	 */
	public function sanitize_template( $template, $options ) {
		if ( ! isset( $options['list_type'] ) || empty( $options['list_type'] ) ) {
			return $template;
		}

		if ( ! empty( $template ) ) {
			return $template;
		}

		$users      = '<ul>[users]' . "\n" . '<li>' . "\n" . '<a href="[user_link]">[user_name]</a>' . "\n" . '</li>' . "\n" . '[/users]</ul>';
		$terms      = '<ul>[terms]' . "\n" . '<li>' . "\n" . '<a href="[term_link]">[term_name]</a>' . "\n" . '</li>' . "\n" . '[/terms]</ul>';
		$posts      = '<ul>[posts]' . "\n" . '<li>' . "\n" . '[title]' . "\n" . '[excerpt wordlimit=20]' . "\n" . '[more]' . "\n" . '</li>' . "\n" . '[/posts]</ul>';
		$termsposts = '<ul>[terms]' . "\n" . '<li>' . "\n" . '<a href="[term_link]">[term_name]</a>' . "\n" . $posts . "\n" . '</li>' . "\n" . '[/terms]</ul>';
		$usersposts = '<ul>[users]' . "\n" . '<li>' . "\n" . '<a href="[user_link]">[user_name]</a>' . "\n" . $posts . "\n" . '</li>' . "\n" . '[/users]</ul>';

		if ( 'terms' === $options['list_type'] ) {
			$template = $terms;
		} elseif ( 'users' === $options['list_type'] ) {
			$template = $users;
		} elseif ( 'posts' === $options['list_type'] ) {
			$template = $posts;
		} elseif ( 'terms.posts' === $options['list_type'] ) {
			$template = $termsposts;
		} elseif ( 'users.posts' === $options['list_type'] ) {
			$template = $usersposts;
		}

		return $template;
	}
}
