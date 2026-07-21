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

		// Everything between a loop tag pair repeats once per item; the
		// per-item wrapper div makes that visible at a glance.
		$posts = "[posts]\n\t<div class=\"post-item\">\n\t\t<a href=\"[post_permalink]\">[post_title]</a>\n\t</div>\n[/posts]\n[nav]";

		$terms = "[terms]\n\t<div class=\"term-item\">\n\t\t<a href=\"[term_link]\">[term_name]</a>\n\t</div>\n[/terms]";

		$users = "[users]\n\t<div class=\"user-item\">\n\t\t<a href=\"[user_link]\">[user_name]</a>\n\t</div>\n[/users]";

		$termsposts = "[terms]\n\t<div class=\"term-item\">\n\t\t<a href=\"[term_link]\">[term_name]</a>\n\t\t[posts]\n\t\t\t<div class=\"post-item\">\n\t\t\t\t<a href=\"[post_permalink]\">[post_title]</a>\n\t\t\t</div>\n\t\t[/posts]\n\t</div>\n[/terms]";

		$usersposts = "[users]\n\t<div class=\"user-item\">\n\t\t<a href=\"[user_link]\">[user_name]</a>\n\t\t[posts]\n\t\t\t<div class=\"post-item\">\n\t\t\t\t<a href=\"[post_permalink]\">[post_title]</a>\n\t\t\t</div>\n\t\t[/posts]\n\t</div>\n[/users]";

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
