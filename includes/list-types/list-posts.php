<?php
/**
 * @package W4 Post List
 * @author Shazzad Hossain Khan
 * @url http://w4dev.com/plugins/w4-post-list
**/


class W4PL_List_Posts extends W4PL_List implements W4PL_Interface_List
{
	function __construct($options = array())
	{
		parent::__construct($options);

		$this->terms_args 		= array();
		$this->terms_query 		= array();
		$this->current_term 	= '';

		$this->users_args 		= array();
		$this->users_query 		= array();
		$this->current_user 	= '';

		$this->posts_args 		= array();
		$this->posts_query 		= array();
		$this->current_post		= '';

		$this->groups 			= array();
		$this->current_group	= '';

		$this->css  			= '';
		$this->js  				= '';
		$this->html 			= '';

		// let helper class extend/modify this class
		do_action_ref_array('w4pl/pre_get_list', array(&$this));
	}

	function get_html()
	{
		// let helper class extend/modify this class
		do_action_ref_array( 'w4pl/parse_query_args', array( &$this ) );
		#W4PL_Utils::d($this->options['list_type']);

		$template = $this->list_type_posts_template();
		$this->template = trim( $template );

		// html
		$this->html  = '';
		$this->html .= '<div id="w4pl-list-'. $this->id .'">'. "\n\t" .'<div id="w4pl-inner-'. $this->id .'" class="w4pl-inner">';
		if (! empty($this->template)) {
			$this->html .= "\n\t\t" . $this->template . "\n\t";
		}
		$this->html .= '</div><!--#w4pl-inner-'. $this->id .'-->'. "\n" .'</div><!--#w4pl-'. $this->id .'-->';


		// let helper classes extend or modify this class
		do_action_ref_array( 'w4pl/parse_html', array(&$this) );

		// return the template
		return "<!--W4PL_List_{$this->id}-->\n" . $this->html . "\n<!--END_W4PL_List_{$this->id}-->\n";
	}

	function list_type_posts_template()
	{
		$paged = isset($_REQUEST['page'. $this->id]) ? $_REQUEST['page'. $this->id] : 1;
		// create attern based on available tags
		$pattern = $this->get_shortcode_regex();
		// main template
		$template = isset($this->options['template']) ? $this->options['template'] : '';
		$terms_template = '';
		$users_template = '';
		$posts_template = '';
		$groups_template = '';
		$template_nav = '';

		// match [groups]
		if( preg_match('/\[terms\](.*?)\[\/terms\]/sm', $template, $terms_match) ) {
			$terms_template = $terms_match['1'];
		}
		if( preg_match('/\[users\](.*?)\[\/users\]/sm', $template, $users_match) ) {
			$users_template = $users_match['1'];
		}
		// match the loop template [posts]
		if( preg_match('/\[posts\](.*?)\[\/posts\]/sm', $template, $posts_match) ) {
			$posts_template = $posts_match['1'];
		}
		// match [groups]
		if( preg_match('/\[groups\](.*?)\[\/groups\]/sm', $template, $groups_match) ) {
			$groups_template = $groups_match['1'];
		}
		// parse navigation
		if( preg_match( "/\[nav(.*?)\]/", $template, $nav_match) ) {
			$template_nav = $nav_match[0];
		}

		#W4PL_Utils::d($this->posts_args);
		// do query posts
		$this->posts_query = new WP_Query( $this->posts_args );
		#W4PL_Utils::d($this->posts_query);

		#echo '<pre>'; print_r($this->posts_query->request); echo '</pre>';

		// if using groups
		if( isset($this->options['groupby']) && !empty($this->options['groupby']) && !empty($groups_template) ) :
			$this->init_posts_groups();
		// remove the group block
		elseif (! empty ($groups_template)) :
			$template = str_replace( $groups_match[0], $posts_match['0'], $template );
		endif;


		// found posts
		if ($this->posts_query->have_posts()) :
			if(! empty($this->groups)) {
				$groups_loop = '';
				foreach ($this->groups as $group) {
					$this->current_group = $group;
					$group_posts_loop = '';
					$groups_template_clone = $groups_template; // clone the group template
					// post loop
					while ($this->posts_query->have_posts()) {
						$this->posts_query->the_post();
						if (in_array (get_the_ID(), $group['post_ids'])) {
							$group_posts_loop .= preg_replace_callback( "/$pattern/s", array(&$this, 'do_shortcode_tag'), $posts_template );
						}
					}

					// replace [posts]
					$groups_template_clone = str_replace( $posts_match['0'], $group_posts_loop, $groups_template_clone );

					$groups_loop .= preg_replace_callback( "/$pattern/s", array(&$this, 'do_shortcode_tag'), $groups_template_clone );
				}

				// replace [groups]
				$template = str_replace( $groups_match[0], $groups_loop, $template );
			} else {
				#W4PL_Utils::d($posts_template);
				$posts_loop = '';
				// post loop
				while ($this->posts_query->have_posts()) {
					$this->posts_query->the_post();
					$posts_loop .= preg_replace_callback( "/$pattern/s", array(&$this, 'do_shortcode_tag'), $posts_template );
				}

				// replace [posts]
				$template = str_replace( $posts_match[0], $posts_loop, $template );
			}

		// no posts
		else :

			// replace [posts]
			$template = '';

		endif;


		// reset postdata back to normal.
		wp_reset_postdata();


		// replace [nav]
		// template will be empty if there's no results
		if( !empty($template_nav) && !empty($template) )
		{
			if( isset($this->options['limit']) && !empty($this->options['limit']) && (int) $this->options['limit'] < ($this->options['posts_per_page'] * $paged) )
				$max_num_pages = $paged;
			else
				$max_num_pages = $this->posts_query->max_num_pages;

			$navigation = $this->navigation( $max_num_pages, $paged, shortcode_parse_atts($nav_match[1]) );
			$template = str_replace( $nav_match[0], $navigation, $template );
		}

		return $template;
	}

}
?>
