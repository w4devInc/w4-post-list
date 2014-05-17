<?php
class W4_Post_list
{
	var $id 		= array();
	var $query 		= array();
	var $wp_query 	= array();
	var $options 	= array();
	var $groups 	= array();

	var $css		= '';
	var $js			= '';
	var $html		= '';


	function __construct()
	{
		$shortcodes = apply_filters( 'w4pl/get_shortcodes', array() );
		foreach( $shortcodes as $tag => $attr ){
			if( ! has_filter( 'w4pl/shortcode/'. $tag, array(&$this, $attr['func']) ) && method_exists($this, $attr['func']) )
				add_filter( 'w4pl/shortcode/'. $tag, array(&$this, $attr['func']), 10, 2 );
		}
	}


	function prepare( $options )
	{
		/*
		if( W4PL_SLUG != get_post_type($list_id) )
			return new WP_Error( 'postlist_not_found', 
			sprintf( __( 'List not found with id - %1$s', W4PL_TXT_DOMAIN ), $list_id ) );

		static $w4pl_loaded;
		if( !isset($w4pl_loaded) || !is_array($w4pl_loaded) )
			$w4pl_loaded = array();

		if( in_array($list_id, $w4pl_loaded) )
			return new WP_Error('list_loaded', 'A list can load only one.');

		$w4pl_loaded[] = $list_id;
		*/

		$this->options 			= $options;
		$this->id 				= isset($this->options['id']) ? $this->options['id'] : time();
		$this->query 			= array();
		$this->wp_query 		= '';


		if( isset($this->options['template_loop']) && !empty($this->options['template_loop']) ){
			if( isset($this->options['template']) 
				&& ! preg_match('/\[posts\](.*?)\[\/posts\]/sm', $this->options['template']) 
				&& preg_match('/\[loop\]/sm', $this->options['template'], $match ) 
			){
				$this->options['template'] = str_replace( $match[0], '[posts]'. $this->options['template_loop'] .'[/posts]', $this->options['template'] );
				unset($this->options['template_loop']);

				update_post_meta( $list_id, '_w4pl', $this->options );
			}
		}
	}


	function display()
	{
		// push default options to query var
		foreach( array(
			'post_type', 
			'orderby', 
			'order', 
			'offset',
			'posts_per_page'
		) as $option_name )
		{
			if( !empty($this->options[$option_name]) )
				$this->query[$option_name] = $this->options[$option_name];
		}

		// array
		foreach( array(
			'post_mime_type', 
			'post_status'
		) as $option_name )
		{
			if( !empty($this->options[$option_name]) )
				$this->query[$option_name] = $this->options[$option_name];
		}


		// comma separated ids
		foreach( array(
			'post__in', 
			'post__not_in', 
			'post_parent__in', 
			'author__in',
		) as $option_name )
		{
			if( !empty($this->options[$option_name]) ){
				$opt = wp_parse_id_list($this->options[$option_name]);
				if( !empty($opt) )
					$this->query[$option_name] = $opt;
			}
		}

		// orderby meta key/value
		if( $this->options['orderby'] == 'meta_value' || $this->options['orderby'] == 'meta_value_num' )
		{
			$this->query['meta_key'] = $this->options['orderby_meta_key'];
		}


		#echo '<pre>'; print_r($this->query); echo '</pre>';


		// build taxonoomy query
		/*
		$this->query['tax_query'] = array();
		foreach( $this->options as $option_name => $option_val )
		{
			if( !empty($option_val) && 0 === strpos($option_name, 'tax_query_') )
			{
				$this->query['tax_query'][] = array(
					'taxonomy' 			=> str_replace('tax_query_', '', $option_name),
					'terms' 			=> $option_val,
					'operator' 			=> 'IN',
					'field' 			=> 'term_id'
				);
			}
		}
		*/

		#echo '<pre>'; print_r($this->options); echo '</pre>';

		# print_r($this->query);

		// we catch paged query using a non-pretty query var
		$paged = isset($_REQUEST['page'. $this->id]) ? $_REQUEST['page'. $this->id] : 1;

		$defaults = array(
			'post_status' 	=> 'publish',
			'post_type' 	=> 'post',
			'paged'			=> $paged
		);


		$this->query = wp_parse_args( $this->query, $defaults );



		// while maximum limit is set, we only fetch till the maximum post
		if( isset($this->options['limit']) && !empty($this->options['limit']) && $this->options['limit'] < ($this->options['posts_per_page'] * $paged) )
		{
			$this->query['offset'] = (int) $this->options['offset'] + ($paged - 1) * $this->options['posts_per_page'];
			$this->query['posts_per_page'] = $this->options['limit'] - ( $this->options['posts_per_page'] * ($paged-1) );
		}


		// let helper class extend/modify this class
		do_action_ref_array( 'w4pl/parse_query', array( &$this ) );


		#echo '<pre>'; print_r($this->query); echo '</pre>';


		$this->wp_query = new WP_Query( $this->query );

		# echo '<pre>'; print_r($this->wp_query); echo '</pre>';


		// main template
		$template = $this->options['template'];
		$groups_template = '';
		$posts_template = '';
		$template_nav = '';


		// match [groups]
		if( preg_match('/\[groups\](.*?)\[\/groups\]/sm', $template, $group_match) )
		{
			$groups_template = $group_match['1'];
		}
		#print_r($groups_template);


		// match the loop template [posts]
		if( preg_match('/\[posts\](.*?)\[\/posts\]/sm', $template, $posts_match) )
		{
			$posts_template = $posts_match['1'];
		}

		// parse navigation
		if( preg_match( "/\[nav(.*?)\]/", $template, $nav_match) )
		{
			$template_nav = $nav_match[0];
		}


		if( isset($this->options['groupby']) && !empty($this->options['groupby']) && !empty($groups_template) )
		{
			$this->init_groups();
		}
		elseif( !empty($groups_template) )
		{
			// remove the group block
			$template = str_replace( $group_match[0], $posts_match['0'], $template );
		}


		// create attern based on available tags
		$pattern = $this->get_shortcode_regex();


		if( $this->wp_query->have_posts() ):
			if( !empty($this->groups) )
			{
				$groups_loop = '';
				foreach( $this->groups as $group)
				{
					$group_posts_loop = '';
					$groups_template_clone = $groups_template; // clone the group template

					// post loop
					while( $this->wp_query->have_posts() )
					{
						$this->wp_query->the_post();
						if( in_array( get_the_ID(), $group['post_ids']) ){
							$group_posts_loop .= preg_replace_callback( "/$pattern/s", array(&$this, 'do_shortcode_tag'), $posts_template );
						}
					}

					// replace [posts]
					$groups_template_clone = str_replace( $posts_match['0'], $group_posts_loop, $groups_template_clone );

					// replace groups atribute
					$groups_template_clone = str_replace( "[group_title]", $group['title'], $groups_template_clone );
					$groups_template_clone = str_replace( "[group_url]", $group['url'], $groups_template_clone );

					$groups_loop .= $groups_template_clone;
				}

				// replace [groups]
				$template = str_replace( $group_match[0], $groups_loop, $template );
			}

			else
			{
				$posts_loop = '';
				// post loop
				while( $this->wp_query->have_posts() )
				{
					$this->wp_query->the_post();
					$posts_loop .= preg_replace_callback( "/$pattern/s", array(&$this, 'do_shortcode_tag'), $posts_template );
				}

				// replace [posts]
				$template = str_replace( $posts_match[0], $posts_loop, $template );
			}
		endif;


		// replace [nav]
		if( !empty($template_nav) )
		{
			$navigation = $this->get_navigation( shortcode_parse_atts($template_nav), null );
			$template = str_replace( $nav_match[0], $navigation, $template );
		}


		$return  = '';


		// main template
		$return .= '<div id="w4pl-list-'. $this->id .'"><div id="w4pl-inner-'. $this->id .'" class="w4pl-inner">';
		$return .= $template;
		$return .= '</div><!--#w4pl-inner-'. $this->id .'--></div><!--#w4pl-'. $this->id .'-->';


		// reset postdata back to normal.
		wp_reset_postdata();


		$this->html = $return;


		// let helper class extend/modify this class
		do_action_ref_array( 'w4pl/parse_html', array( &$this ) );


		// return the template
		return "<!--W4_Post_list_{$this->id}-->\n" . $this->html . "\n\n";
	}


	function init_groups()
	{
		$groupby = $this->options['groupby'];
		$this->groups = array();

		// post parent
		if( 'parent' == $groupby )
		{
			foreach( $this->wp_query->posts as $index => $post )
			{
				if( $post->post_parent )
				{
					$parent = get_post( $post->post_parent );
					if( !isset($this->groups[$parent->ID]) ){
						$this->groups[$parent->ID] = array(
							'title' => $parent->post_title,
							'url' 	=> get_permalink($parent->ID)
						);
					}
					if( !isset($this->groups[$parent->ID]['post_ids']) ){
						$this->groups[$parent->ID]['post_ids'] = array();
					}
					$this->groups[$parent->ID]['post_ids'][] = $post->ID;
					}
					else{
					if( !isset($this->groups[0]) ){
						$this->groups[0] = array(
							'title' => 'Unknown',
							'url' 	=> ''
						);
					}
					if( !isset($this->groups[0]['post_ids']) ){
						$this->groups[0]['post_ids'] = array();
					}
					$this->groups[0]['post_ids'][] = $post->ID;
				}
			}
		}

		// terms
		elseif( 0 === strpos($groupby, 'tax_') )
		{
			$tax = str_replace('tax_', '', $groupby);
			foreach( $this->wp_query->posts as $index => $post )
			{
				if( $terms = get_the_terms($post, $tax) )
				{
					#print_r($terms);

					$term = array_shift($terms);
					if( !isset($this->groups[$term->term_id]) ){
						$this->groups[$term->term_id] = array(
							'title' => $term->name,
							'url' 	=> get_term_link($term)
						);
					}
					if( !isset($this->groups[$term->term_id]['post_ids']) ){
						$this->groups[$term->term_id]['post_ids'] = array();
					}
					$this->groups[$term->term_id]['post_ids'][] = $post->ID;
					}
					else{
					if( !isset($this->groups[0]) ){
						$this->groups[0] = array(
							'title' => 'Unknown',
							'url' 	=> ''
						);
					}
					if( !isset($this->groups[0]['post_ids']) ){
						$this->groups[0]['post_ids'] = array();
					}
					$this->groups[0]['post_ids'][] = $post->ID;
				}
			}
		}

		elseif( 'author' == $groupby )
		{
			foreach( $this->wp_query->posts as $index => $post )
			{
				if( $post->post_author )
				{
					$parent = get_userdata( $post->post_author );
					if( !isset($this->groups[$parent->ID]) ){
						$this->groups[$parent->ID] = array(
							'title' => $parent->display_name,
							'url' 	=> get_author_posts_url($parent->ID)
						);
					}
					if( !isset($this->groups[$parent->ID]['post_ids']) ){
						$this->groups[$parent->ID]['post_ids'] = array();
					}
					$this->groups[$parent->ID]['post_ids'][] = $post->ID;
				}
				else{
					if( !isset($this->groups[0]) ){
						$this->groups[0] = array(
							'title' => 'Unknown',
							'url' 	=> ''
						);
					}
					if( !isset($this->groups[0]['post_ids']) ){
						$this->groups[0]['post_ids'] = array();
					}
					$this->groups[0]['post_ids'][] = $post->ID;
				}
			}
		}

		// year
		elseif( 'year' == $groupby )
		{
			foreach( $this->wp_query->posts as $index => $post )
			{
				if( $year = mysql2date( 'Y', $post->post_date ) )
				{
					if( !isset($this->groups[$year]) ){
						$this->groups[$year] = array(
							'title' => $year,
							'url' 	=> get_year_link($year)
						);
					}
					if( !isset($this->groups[$year]['post_ids']) ){
						$this->groups[$year]['post_ids'] = array();
					}
					$this->groups[$year]['post_ids'][] = $post->ID;
				}
				else{
					if( !isset($this->groups[0]) ){
						$this->groups[0] = array(
							'title' => 'Unknown',
							'url' 	=> ''
						);
					}
					if( !isset($this->groups[0]['post_ids']) ){
						$this->groups[0]['post_ids'] = array();
					}
					$this->groups[0]['post_ids'][] = $post->ID;
				}
			}
		}


		// month
		elseif( 'month' == $groupby )
		{
			foreach( $this->wp_query->posts as $index => $post )
			{
				$month = mysql2date( 'm', $post->post_date );
				$year = mysql2date( 'Y', $post->post_date );

				if( $month && $year )
				{
					if( !isset($this->groups[$month]) ){
						$this->groups[$month] = array(
							'title' => mysql2date( 'F', $post->post_date ),
							'url' 	=> get_month_link( $year, $month )
						);
					}
					if( !isset($this->groups[$month]['post_ids']) ){
						$this->groups[$month]['post_ids'] = array();
					}
					$this->groups[$month]['post_ids'][] = $post->ID;
				}
				else{
					if( !isset($this->groups[0]) ){
						$this->groups[0] = array(
							'title' => 'Unknown',
							'url' 	=> ''
						);
					}
					if( !isset($this->groups[0]['post_ids']) ){
						$this->groups[0]['post_ids'] = array();
					}
					$this->groups[0]['post_ids'][] = $post->ID;
				}
			}
		}

		// month
		elseif( 'yearmonth' == $groupby )
		{
			foreach( $this->wp_query->posts as $index => $post )
			{
				$month = mysql2date( 'm', $post->post_date );
				$year = mysql2date( 'Y', $post->post_date );

				if( $year && $month )
				{
					if( !isset($this->groups[$year.$month]) ){
						$this->groups[$year.$month] = array(
							'title' => mysql2date( 'Y, F', $post->post_date ),
							'url' 	=> get_month_link( $year, $month )
						);
					}
					if( !isset($this->groups[$year.$month]['post_ids']) ){
						$this->groups[$year.$month]['post_ids'] = array();
					}
					$this->groups[$year.$month]['post_ids'][] = $post->ID;
				}
				else{
					if( !isset($this->groups[0]) ){
						$this->groups[0] = array(
							'title' => 'Unknown',
							'url' 	=> ''
						);
					}
					if( !isset($this->groups[0]['post_ids']) ){
						$this->groups[0]['post_ids'] = array();
					}
					$this->groups[0]['post_ids'][] = $post->ID;
				}
			}
		}


		#print_r( $this->options['group_order'] );

		if( isset($this->options['group_order']) && !empty($this->options['group_order']) )
		{
			
			if( 'ASC' == $this->options['group_order'] )
			{
				uasort( $this->groups, array($this, 'cmp_asc') );
			}
			elseif( 'DESC' == $this->options['group_order'] )
			{
				uasort( $this->groups, array($this, 'cmp_desc') );
			}
		}

		#echo '<pre>';
		#print_r( $this->groups );
		#echo '</pre>';
	}

	public function cmp_asc($a, $b)
	{
		if ($a == $b) {
			return 0;
		}
		return ($a < $b) ? -1 : 1;
	}
	public function cmp_desc($a, $b)
	{
		if ($a == $b) {
			return 0;
		}
		return ($a > $b) ? -1 : 1;
	}



	function get_navigation( $attr = array() )
	{
		$paged = isset($_REQUEST['page'. $this->id]) ? $_REQUEST['page'. $this->id] : 1;
		$return = '';

		if( $this->wp_query->max_num_pages > 1 )
		{
			if( isset($attr['type']) && 'plain' == $attr['type'] ){
				$big = 10;
				$max_num_pages = $this->wp_query->max_num_pages;
				if( isset($this->options['limit']) && !empty($this->options['limit']) ){
					if( $this->options['limit'] > $this->options['posts_per_page'] ){
						$max_pages = ceil($this->options['limit'] / $this->options['posts_per_page']);
					}
					if( $max_pages && $max_pages < $max_num_pages )
						$max_num_pages = $max_pages;
				}
				$return .= paginate_links( array(
					'type' 		=> 'plain',
					'base' 		=> '?page'. $this->id .'=%#%',
					'format' 	=> '?page'. $this->id .'=%#%',
					'current' 	=> $paged,
					'total' 	=> $max_num_pages,
					'end_size' 	=> 2,
					'mid_size' 	=> 2,
					'prev_text' => 'Previous',
					'next_text' => 'Next'
				));
			}

			elseif( isset($attr['type']) && 'list' == $attr['type'] )
			{
				$big = 10;
				$max_num_pages = $this->wp_query->max_num_pages;
				if( isset($this->options['limit']) && !empty($this->options['limit']) ){
					if( $this->options['limit'] > $this->options['posts_per_page'] ){
						$max_pages = ceil($this->options['limit'] / $this->options['posts_per_page']);
					}
					if( $max_pages && $max_pages < $max_num_pages )
						$max_num_pages = $max_pages;
				}
				$return .= paginate_links( array(
					'type' 		=> 'list',
					'base' 		=> '?page'. $this->id .'=%#%',
					'format' 	=> '?page'. $this->id .'=%#%',
					'current' 	=> $paged,
					'total' 	=> $max_num_pages,
					'end_size' 	=> 2,
					'mid_size' 	=> 2,
					'prev_text' => 'Previous',
					'next_text' => 'Next'
				));
			}

			else
			{
				$big = 10;
				$max_num_pages = $this->wp_query->max_num_pages;
				if( isset($this->options['limit']) && !empty($this->options['limit']) ){
					if( $this->options['limit'] > $this->options['posts_per_page'] ){
						$max_pages = ceil($this->options['limit'] / $this->options['posts_per_page']);
					}
					if( $max_pages && $max_pages < $max_num_pages )
						$max_num_pages = $max_pages;
				}

				if( $paged == 2 )
					$return .= '<a href="'. remove_query_arg(array('page'. $this->id)) .'" class="prev page-numbers">Prev</a>';
				elseif( $paged > 2 )
					$return .= '<a href="'. add_query_arg( 'page'. $this->id, ($paged - 1) ) .'" class="prev page-numbers">Prev</a>';

				if( $max_num_pages > $paged )
					$return .= '<a href="'. add_query_arg( 'page'. $this->id, ($paged + 1) ) .'" class="next page-numbers">Next</a>';
			}
		}

		if( !empty($return) )
		{
			$class = 'navigation';
			if( isset($attr['ajax']) && ( (bool) $attr['ajax'] ) ){
				$class .= ' ajax-navigation';

				$this->js .= '(function($){$(document).ready(function(){$("#w4pl-list-'. $this->id 
				. ' .navigation a.page-numbers").live("click", function(){var that = $(this), parent = $("#w4pl-list-'. $this->id 
				. '");parent.addClass("w4pl-loading");parent.load( that.attr("href") + " #" + parent.attr("id") + " .w4pl-inner", function(e){parent.removeClass("w4pl-loading");});return false;});});})(jQuery) ;';

			}

			$return = '<div class="'. $class .'">'. $return . '</div>';
		}

		return $return;
	}

	function get_shortcode_regex()
	{
		$tagnames = array_keys( apply_filters( 'w4pl/get_shortcodes', array() ) );
		$tagregexp = join( '|', array_map('preg_quote', $tagnames) );
	
		return
			  '\\['                              // Opening bracket
			. '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
			. "($tagregexp)"                     // 2: Shortcode name
			. '(?![\\w-])'                       // Not followed by word character or hyphen
			. '('                                // 3: Unroll the loop: Inside the opening shortcode tag
			.     '[^\\]\\/]*'                   // Not a closing bracket or forward slash
			.     '(?:'
			.         '\\/(?!\\])'               // A forward slash not followed by a closing bracket
			.         '[^\\]\\/]*'               // Not a closing bracket or forward slash
			.     ')*?'
			. ')'
			. '(?:'
			.     '(\\/)'                        // 4: Self closing tag ...
			.     '\\]'                          // ... and closing bracket
			. '|'
			.     '\\]'                          // Closing bracket
			.     '(?:'
			.         '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
			.             '[^\\[]*+'             // Not an opening bracket
			.             '(?:'
			.                 '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
			.                 '[^\\[]*+'         // Not an opening bracket
			.             ')*+'
			.         ')'
			.         '\\[\\/\\2\\]'             // Closing shortcode tag
			.     ')?'
			. ')'
			. '(\\]?)';                          // 6: Optional second closing brocket for escaping shortcodes: [[tag]]
	}

	function do_shortcode_tag( $m )
	{
		if ( $m[1] == '[' && $m[6] == ']' ) {
			return substr($m[0], 1, -1);
		}
		$tag = $m[2];
		$attr = shortcode_parse_atts( $m[3] );
		if ( isset( $m[5] ) ){
			return $m[1] . apply_filters( 'w4pl/shortcode/'. $tag, $attr, '', $m[5] ) . $m[6];
		} else {
			return $m[1] . apply_filters( 'w4pl/shortcode/'. $tag, $attr, '', null ) . $m[6];
		}
	}


	// Callback Functions - Post
	function post_id($attr, $cont){ return get_the_ID(); }
	function post_number($attr, $cont){ return $this->wp_query->current_post + 1; }
	function post_permalink($attr, $cont){ return get_permalink(); }
	function post_class($attr, $cont){ return join( ' ', get_post_class() ); }
	function post_title($attr, $cont)
	{
		$return = get_the_title();
		if( isset($attr['wordlimit']) ){
			$wordlimit = $attr['wordlimit'];
			$return = wp_trim_words( $return, $wordlimit );
		}
		return $return;
	}

	function post_comment_url($attr, $cont){ return get_permalink() . "#comments"; }
	function post_comment_count($attr, $cont){ global $post; return (int) $post->comment_count; }

	function post_date($attr, $cont)
	{
		$format = get_option('date_format');
		if( isset($attr['format']) ){
			$format = $attr['format'];
		}
		return get_the_time($format);
	}
	function post_time($attr, $cont)
	{
		$format = get_option('time_format');
		if( isset($attr['format']) ){
			$format = $attr['format'];
		}
		return get_the_time($format);
	}
	function post_modified_date($attr, $cont)
	{
		$format = get_option('date_format');
		if( isset($attr['format']) ){
			$format = $attr['format'];
		}
		return get_post_modified_time($format);
	}
	function post_modified_time($attr, $cont)
	{
		$format = get_option('time_format');
		if( isset($attr['format']) ){
			$format = $attr['format'];
		}
		return get_post_modified_time($format);
	}

	function post_author_name($attr, $cont){ return get_the_author_meta('display_name'); }
	function post_author_url($attr, $cont){ return get_author_posts_url( get_the_author_meta('ID') ); }
	function post_author_email($attr, $cont){ return get_the_author_meta('user_email'); }
	function post_author_avatar($attr, $cont)
	{
		$size = 32;
		if( isset($attr['size']) ){
			$size = $attr['size'];
		}
		return get_avatar( get_the_author_meta('user_email'), $size );
	}

	function post_excerpt($attr, $cont)
	{
		$post = get_post();
		$excerpt = $post->post_excerpt;
		if ( '' == $excerpt )
			$excerpt = $post->post_content;

		$excerpt = wp_strip_all_tags( $excerpt );

		if( isset($attr['wordlimit']) ){
			$wordlimit = $attr['wordlimit'];
			$excerpt = wp_trim_words( $excerpt, $wordlimit );
		}

		return $excerpt;
	}
	function post_content($attr, $cont)
	{
		global $post;
		// Post content without wrapper --
		$content = apply_filters( 'the_content', get_the_content() );
		$content = str_replace(']]>', ']]&gt;', $content);
		return $content;
	}
	function post_thumbnail($attr, $cont)
	{
		if( isset($attr['size']) ){
			$size = $attr['size'];
		}
		elseif( isset($attr['width']) ){
			if( isset($attr['height']) ){
				$height = $attr['height'];
			}
			else{
				$height = 9999;
			}
			$size = array($attr['width'], $height);
		}
		elseif( isset($attr['height']) )
		{
			if( isset($attr['width']) ){
				$width = $attr['width'];
			}
			else{
				$width = 9999;
			}
			$size = array($width, $attr['height']);
		}
		else{
			$size = 'post-thumbnail';
		}

		$post_id = get_the_ID();
		$post_thumbnail_id = get_post_thumbnail_id( $post_id );

		if( isset($attr['return']) && 'id' == $attr['return'] ){
			return (int) $post_thumbnail_id;
		}
		elseif( isset($attr['return']) && 'src' == $attr['return'] ){
			$img = wp_get_attachment_image_src( $post_thumbnail_id );
			return isset($img[0]) ? $img[0] : '';
		}
		elseif ( $post_thumbnail_id ) {
			return wp_get_attachment_image( $post_thumbnail_id, $size );
		}

		return '';
	}


	function post_meta($attr, $cont)
	{
		if( isset($attr['key']) ){
			$meta_key = $attr['key'];
		}
		elseif( isset($attr['meta_key']) ){
			$meta_key = $attr['meta_key'];
		}
		if( ! $meta_key )
			return;

		$single = true;
		if( array_key_exists('multiple', $attr) ){
			$single = ! ( (bool) $attr['multiple'] );
		}

		$sep = ', ';
		if( isset($attr['sep']) ){
			$sep = $attr['sep'];
		}

		$return = get_post_meta( get_the_ID(), $meta_key, $single );

		if( is_array($return) ){
			$new = array();
			foreach( $return as $r => $d ){
				if( !is_array($d) ){
					$new[] = $d;
				}
			}
			if( $new )
				$return = implode($sep, $new);
			else
				$return = '';
		}

		return $return;
	}
	function post_terms($attr, $cont)
	{
		if( isset($attr['tax']) ){
			$taxonomy = $attr['tax'];
		}
		elseif( isset($attr['taxonomy']) ){
			$taxonomy = $attr['taxonomy'];
		}
		if( ! isset($taxonomy) || ! taxonomy_exists($taxonomy) )
			return;

		$sep = ', ';
		if( isset($attr['sep']) ){
			$sep = $attr['sep'];
		}

		return get_the_term_list( get_the_ID(), $taxonomy, '', $sep );
	}


	// Attachment
	function attachment_thumbnail($attr, $cont)
	{
		if( isset($attr['size']) ){
			$size = $attr['size'];
		}
		elseif( isset($attr['width']) ){
			if( isset($attr['height']) ){
				$height = $attr['height'];
			}
			else{
				$height = 9999;
			}
			$size = array($attr['width'], $height);
		}
		elseif( isset($attr['height']) )
		{
			if( isset($attr['width']) ){
				$width = $attr['width'];
			}
			else{
				$width = 9999;
			}
			$size = array($width, $attr['height']);
		}
		else{
			$size = 'post-thumbnail';
		}

		if( isset($attr['id']) )
			$post_id = (int) $attr['id'];
		else
			$post_id = get_the_ID();


		if( 'attachment' != get_post_type($post_id) )
			return '';


		$icon = false;
		if( ! wp_attachment_is_image($post_id) )
			$icon = true;

		if ( $post_id ) {
			$html = wp_get_attachment_image( $post_id, $size, $icon );
		} else {
			$html = '';
		}

		return $html;
	}
	function attachment_url($attr, $cont)
	{
		if( isset($attr['id']) )
			$post_id = (int) $attr['id'];
		else
			$post_id = get_the_ID();

		if( 'attachment' != get_post_type($post_id) )
			return '';

		return wp_get_attachment_url($post_id);
	}

	function group_title($attr, $cont){
		$gr = array();
		foreach( $this->groups as $group ){
			if( in_array( get_the_ID(), $group['post_ids']) ){
				$gr = $group;
				break;
			}
		}
		return $gr['title'];
	}

	// Tempate
	function template_title($attr, $cont){
		return sprintf( 
			'<a class="post_title w4pl_post_title" href="%1$s" title="View %2$s">%2$s</a>', 
			get_permalink(), 
			get_the_title() 
		);
	}
	function template_meta($attr, $cont){
		return sprintf( 
			__("Posted on:", W4PL_TXT_DOMAIN). ' <abbr class="published post-date" title="%1$s">%2$s</abbr> <span class="post_author">by %3$s</span>', 
			get_the_time( get_option('time_format') ), 
			get_the_time( get_option('date_format') ), 
			get_the_author()
		);
	}
	function template_date($attr, $cont){
		return sprintf( 
			'<abbr class="published post-date" title="%1$s"><strong>' . __(" Published:", W4PL_TXT_DOMAIN).'</strong> %2$s</abbr>',
			get_the_time( get_option('time_format') ), 
			get_the_time( get_option('date_format') )
		);
	}
	function template_modified($attr, $cont){
		return sprintf( 
			'<abbr class="modified post-modified" title="%1$s"><strong>' . __( "Updated:", W4PL_TXT_DOMAIN ) . '</strong> %2$s</abbr>',
			get_post_modified_time( get_option('time_format')), 
			get_post_modified_time( get_option('date_format'))
		);
	}
	function template_author($attr, $cont){
		return sprintf( 
			'<a href="%1$s" title="View all posts by %2$s" rel="author">%2$s</a>', 
			get_author_posts_url( get_the_author_meta('ID') ), 
			get_the_author() 
		);
	}
	function template_excerpt($attr, $cont){
		return sprintf( 
			'<div class="post-excerpt">%s</div>',
			$this->post_excerpt($attr, $cont)
		);
	}
	function template_content($attr, $cont){
		return sprintf( 
			'<div class="post-excerpt">%s</div>',
			$this->post_content($attr, $cont)
		);
	}
	function template_more($attr, $cont){
		$read_more = !empty( $attr['text'] ) ? $attr['text'] : __( 'Continue reading &raquo;', W4PL_TXT_DOMAIN );
		return sprintf( 
			'<a class="read_more" href="%1$s" title="Cotinue reading %2$s">%3$s</a>', 
			get_permalink(), 
			get_the_title(), 
			$read_more 
		);
	}
}


?>