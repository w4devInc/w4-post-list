<?php
/**
 * List users & posts
 *
 * @class W4PL_List_Users_Posts
 * @package W4_Post_List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Users & posts list class
 *
 * @class W4PL_List_Users_Posts
 */
class W4PL_List_Users_Posts extends W4PL_List implements W4PL_List_Interface {

	/**
	 * Constructor
	 *
	 * @param array $options List options.
	 */
	public function __construct( $options = array() ) {
		parent::__construct( $options );

		$this->terms_args   = array();
		$this->terms_query  = array();
		$this->current_term = '';

		$this->users_args   = array();
		$this->users_query  = array();
		$this->current_user = '';

		$this->posts_args   = array();
		$this->posts_query  = array();
		$this->current_post = '';

		$this->groups        = array();
		$this->current_group = '';

		$this->css  = '';
		$this->js   = '';
		$this->html = '';

		// let helper class extend/modify this class.
		do_action_ref_array( 'w4pl/pre_get_list', array( &$this ) );
	}

	/**
	 * Get output html
	 */
	function get_html() {
		// let helper class extend/modify this class.
		do_action_ref_array( 'w4pl/parse_query_args', array( &$this ) );

		$template       = $this->list_type_users_template();
		$this->template = trim( $template );

		// html
		$this->html  = '';
		$this->html .= '<div id="w4pl-list-' . $this->id . '">' . "\n\t" . '<div id="w4pl-inner-' . $this->id . '" class="w4pl-inner">';
		if ( ! empty( $this->template ) ) {
			$this->html .= "\n\t\t" . $this->template . "\n\t";
		}
		$this->html .= '</div><!--#w4pl-inner-' . $this->id . '-->' . "\n" . '</div><!--#w4pl-' . $this->id . '-->';

		// let helper classes extend or modify this class.
		do_action_ref_array( 'w4pl/parse_html', array( &$this ) );

		return "<!--W4PL_List_{$this->id}-->\n" . $this->html . "\n<!--END_W4PL_List_{$this->id}-->\n";
	}

	/**
	 * Users template
	 */
	public function list_type_users_template() {
		$paged = isset( $_REQUEST[ 'page' . $this->id ] ) ? $_REQUEST[ 'page' . $this->id ] : 1;
		// create attern based on available tags
		$pattern = $this->get_shortcode_regex();
		// main template
		$template        = isset( $this->options['template'] ) ? $this->options['template'] : '';
		$terms_template  = '';
		$users_template  = '';
		$posts_template  = '';
		$groups_template = '';
		$template_nav    = '';

		// match [groups]
		if ( preg_match( '/\[terms\](.*?)\[\/terms\]/sm', $template, $terms_match ) ) {
			$terms_template = $terms_match['1'];
		}
		if ( preg_match( '/\[users\](.*?)\[\/users\]/sm', $template, $users_match ) ) {
			$users_template = $users_match['1'];
		}
		// match the loop template [posts]
		if ( preg_match( '/\[posts\](.*?)\[\/posts\]/sm', $template, $posts_match ) ) {
			$posts_template = $posts_match['1'];
		}
		// match [groups]
		if ( preg_match( '/\[groups\](.*?)\[\/groups\]/sm', $template, $groups_match ) ) {
			$groups_template = $groups_match['1'];
		}
		// parse navigation
		if ( preg_match( '/\[nav(.*?)\]/', $template, $nav_match ) ) {
			$template_nav = $nav_match[0];
		}

		$this->users_query = new W4PL_Users_Query( $this->users_args );
		$this->users_query->query();

		// echo '<pre>'; print_r($this->users_query); echo '</pre>';
		// $this->users_query = get_users( $this->options['users_taxonomy'], $this->users_args );

		if ( empty( $users_match ) || ! $this->users_query->get_results() ) {
			$template = '';
		} else {
			$users_loop = '';
			foreach ( $this->users_query->get_results() as $user ) {
				$users_template_clone = $users_template; // clone the group template
				$user_posts_loop      = '';

				$this->current_user = $user;

				// term posts
				if ( in_array( $this->options['list_type'], array( 'users.posts' ) ) ) {
					$this->posts_args['paged']          = 1;
					$this->posts_args['posts_per_page'] = isset( $this->options['limit'] ) ? (int) $this->options['limit'] : -1;
					$this->posts_args['author']         = $this->current_user->ID;
					$this->posts_query                  = new WP_Query( $this->posts_args );

					// echo '<pre>'; print_r($this->posts_query); echo '</pre>';

					// post loop
					if ( $this->posts_query->have_posts() ) {
						while ( $this->posts_query->have_posts() ) {
							$this->posts_query->the_post();
							$user_posts_loop .= preg_replace_callback( "/$pattern/s", array( &$this, 'do_shortcode_tag' ), $posts_template );
						}
					}

					// reset postdata back to normal.
					wp_reset_postdata();

				} // end term posts

				// replace [posts]
				if ( isset( $posts_match ) && isset( $posts_match['0'] ) ) {
					$users_template_clone = str_replace( $posts_match['0'], $user_posts_loop, $users_template_clone );
				}

				$users_loop .= preg_replace_callback( "/$pattern/s", array( &$this, 'do_shortcode_tag' ), $users_template_clone );
			}

			// replace [users]
			$template = str_replace( $users_match[0], $users_loop, $template );
		}

		// replace [nav]
		// template will be empty if there's no results
		if ( ! empty( $template_nav ) && ! empty( $template ) ) {
			if ( isset( $this->options['users_max'] ) && ! empty( $this->options['users_max'] ) && $this->options['users_max'] < ( $this->options['users_limit'] * $paged ) ) {
				$max_num_pages = $paged;
			} else {
				$max_num_pages = $this->users_query->max_num_pages;
			}

			$navigation = $this->navigation( $max_num_pages, $paged, shortcode_parse_atts( $nav_match[1] ) );
			$template   = str_replace( $nav_match[0], $navigation, $template );
		}

		return $template;
	}
}
