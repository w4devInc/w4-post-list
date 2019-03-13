<?php
/**
 * @package W4 Post List
 * @author Shazzad Hossain Khan
 * @url http://w4dev.com/plugins/w4-post-list
**/

class W4PL_Helper_Posts
{
	function __construct()
	{
		/* Register User Shortcodes */
		add_filter( 'w4pl/get_shortcodes', 			array($this, 'get_shortcodes'), 21 );

		/* Filer Option */
		add_filter( 'w4pl/pre_get_options', 		array($this, 'pre_get_options') );

		/* Option Page Fields */
		add_filter( 'w4pl/list_edit_form_fields', 	array($this, 'list_edit_form_fields'), 10, 2 );

		/* Parse List Query Args */
		add_filter( 'w4pl/parse_query_args', 		array($this, 'parse_query_args'), 10 );
	}


	/* Register User Shortcodes */

	public static function get_shortcodes( $shortcodes )
	{
		$_shortcodes = array(
			'id' => array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_id'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: post id'
			),
			'ID' => array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_id'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: post id'
			),
			'post_id' => array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_id'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: post id'
			),
			'post_type' => array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_type'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: post type'
			),
			'post_type_label' => array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_type_label'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: post type label'
			),
			'post_status' => array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_status'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: post status'
			),
			'post_status_label' => array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_status_label'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: post status label'
			),
			'post_number' => array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_number'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: post item number, starting from 1'
			),
			'post_permalink' => array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_permalink'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: post url/link'
			),
			'post_class' => array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_class'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: post html classes'
			),
			'post_title' => array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_title'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: post title
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>wordlimit</strong> = (number), limit number of words to display
				<br /><strong>charlimit</strong> = (number), limit number of characters to display'
			),
			'post_name' => array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_name'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: post name'
			),
			'post_comment_url' => array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_comment_url'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: post comment form link/url'
			),
			'post_comment_count'=> array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_comment_count'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: (numeric) amount of approved comments'
			),
			'post_the_date' => array(
				'group' 	=> 'Post', 
				'code' 		=> '[post_the_date format="'. get_option('date_format') .'" before="" after=""]', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_the_date'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: unique post date, ignored on current item if previous post date and curent post date is same (date formatted)
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>format</strong> = php datetime format
				<br /><strong>before</strong> = before date
				<br /><strong>after</strong> = after date'
			),
			'post_date' => array(
				'group' 	=> 'Post', 
				'code' 		=> '[post_date format="'. get_option('date_format') .'"]', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_date'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: post date (date formatted)
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>format</strong> = php datetime format'
			),
			'post_time' => array(
				'group' 	=> 'Post', 
				'code' 		=> '[post_time format="'. get_option('time_format') .'"]', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_time'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: post date (time formatted)
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>format</strong> = php datetime format'
			),
			'post_modified_date' => array(
				'group' 	=> 'Post', 
				'code' 		=> '[post_modified_date format="'. get_option('date_format') .'"]', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_modified_date'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: post modified date (date formatted)
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>format</strong> = php datetime format'
			),
			'post_modified_time'=> array(
				'group' 	=> 'Post', 
				'code' 		=> '[post_modified_time format="'. get_option('time_format') .'"]', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_modified_time'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: post modified date (time formatted)
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>format</strong> = php datetime format'
			),
			'post_author_meta' => array(
				'group' 	=> 'Post', 
				'code' 		=> '[post_author_meta name=""]', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_author_meta'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: post author meta value
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>name</strong> = ex: display_name, bio, user_email etc'
			),
			'post_author_name' => array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_author_name'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: post author name'
			),
			'post_author_url'	=> array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_author_url'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: post author name url'
			),
			'post_author_email'	=> array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_author_email'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: post author email address'
			),
			'post_author_avatar'=> array(
				'group' 	=> 'Post', 
				'code' 		=> '[post_author_avatar size=""]', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_author_avatar'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: post author avatar
				<br /><br /><strong>attributes:</strong>
				<br /><strong>size</strong> = (number), avatar image size'
			),
			'post_excerpt' => array(
				'group' 	=> 'Post', 
				'code' 		=> '[post_excerpt wordlimit=""]', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_excerpt'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: post excerpt/short description
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>wordlimit</strong> = (number), limit number of words to display'
			),
			'post_content'		=> array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_content'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: post content'
			),
			'post_thumbnail' => array(
				'group' 	=> 'Post', 
				'code' 		=> '[post_thumbnail size="" return=""]', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_thumbnail'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: (text|number) based on the rerurn attribute & only if the post has a thumbnail assigned
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>return</strong> = (id|src|html), 
				<br />----"src" - will return src of the image, 
				<br />----"id" - will return id of the image, 
				<br />----by default it will return image html
				<br /><strong>class</strong> = (string), class name for the image (&lt;img /&gt;) tag
				<br /><strong>size</strong> = (string), thumbnail size
				<br /><strong>width</strong> = (number), thumbnail width
				<br /><strong>height</strong> = (number), thumbnail height
				<br /><strong>placeholder</strong> = (text), default placeholder text if post thumbnail no found'
			),
			'post_image' => array(
				'group' 	=> 'Post', 
				'code' 		=> '[post_image use_fallback="1"]', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_image'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: <strong>first</strong> or <strong>last</strong> image source (src="") from post content
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>position</strong> = (first|last)
				<br /><strong>return</strong> = (text|number), 
				<br />----"src" - will return src of the image, 
				<br />----by default it will return image html
				<br /><strong>class</strong> = (string), class name for the image (&lt;img /&gt;) tag
				<br /><strong>width</strong> = (number), set image width attr (image scaling, not resizing)
				<br /><strong>height</strong> = (number), set image height attr (image scaling, not resizing)
				<br /><strong>use_fallback</strong> = (true|false), set 1 to use <code>[post_thumbnail]</code> shortcode as fallback while post content dont have any images. '
			),
			'post_meta' => array(
				'group' 	=> 'Post', 
				'code' 		=> '[post_meta key="" multiple="0"]', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_meta'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: post meta value. if return value is an array, it will be migrated to string by using explode function
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>key</strong> = (text|number), meta key name
				<br /><strong>sub_key</strong> = (text|number), if meta value is array|object, display a specific value by it\'s key
				<br /><strong>multiple</strong> = (0|1), display meta value at multiple occurence
				<br /><strong>sep</strong> = (text), separate array meta value into string'
			),
			'post_meta_date' => array(
				'group' 	=> 'Post', 
				'code' 		=> '[post_meta_date key=""]', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_meta_date'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: post meta value. if return value is an array, it will be migrated to string by using explode function
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>key</strong> = (text|number), meta key name'
			),
			'post_terms' => array(
				'group' 	=> 'Post', 
				'code' 		=> '[post_terms tax="category" sep=", "]', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_terms'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: post type terms. if return value is an array, it will be migrated to string by using explode function
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>tax</strong> = (string), taxonomy name
				<br /><strong>sep</strong> = (string), separate array meta value into string
				<br /><strong>return</strong> = (name|slug), return plain name or slug'
			),
			'attachment_thumbnail' => array(
				'group' 	=> 'Post', 
				'code' 		=> '[attachment_thumbnail size=""]', 
				'callback' 	=> array('W4PL_Helper_Posts', 'attachment_thumbnail'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: if the post type is attachment, the attached file thumb is displayed.
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>id</strong> = (string), attachment id
				<br /><strong>meta_key</strong> = (string), retrieve attachment id from meta value
				<br /><strong>size</strong> = (string), image size
				<br /><strong>class</strong> = (string), class name for the image (&lt;img /&gt;) tag
				<br /><strong>width</strong> = (number), image width
				<br /><strong>height</strong> = (number), image height
				<br /><strong>return</strong> = (text|number), 
				<br />----"src" - will return src of the attachment, 
				<br />----"id" - will return id of the attachment, 
				<br />----by default it will return image html
				'
			),
			'attachment_url' => array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'attachment_url'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>:  if the post is an attachment, the attached image source is returned'
			),

			'parent_permalink' => array(
				'group' 	=> 'Post', 
				'code' 		=> '[parent_permalink self=1]', 
				'callback' 	=> array('W4PL_Helper_Posts', 'parent_permalink'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: if the post type is hierarchical, it\'s parent post permalink is returned
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>self</strong> = (int), if no parent item exist, return the self permalink'
			),

			'title' => array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'template_title'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: title template'
			),
			'meta' => array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'template_meta'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: meta template'
			),
			'publish' => array(
				'group' 	=> 'Post', 
				'callback'	=> array('W4PL_Helper_Posts', 'template_date'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: publish time template'
			),
			'date' => array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'template_date'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: publish time template'
			),
			'modified' => array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'template_modified'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: modified time template'
			),
			'author' => array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'template_author'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: author template'
			),
			'excerpt' => array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'template_excerpt'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: excerpt template'
			),
			'content' => array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'template_content'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: content template'
			),
			'more' => array(
				'group' 	=> 'Post', 
				'code' 		=> '[more text="Continue Reading"]', 
				'callback' 	=> array('W4PL_Helper_Posts', 'template_more'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: more link template
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>text</strong> = (string), text to be displayed'
			),

			'group_id' => array(
				'group' 	=> 'Group', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_group_id'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: group name / title'
			),
			'group_title' => array(
				'group' 	=> 'Group', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_group_title'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: group name / title'
			),
			'group_url' => array(
				'group' 	=> 'Group', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_group_url'),
				'desc' 		=> '<strong>'. __('Output', 'w4pl') .'</strong>: group page link'
			)
		);

		return array_merge( $shortcodes, $_shortcodes );
	}

	/* Post Shortcode Callbacks */

	public static function post_id($attr, $cont){ return get_the_ID(); }

	public static function post_type($attr, $cont){ return get_post_type(); }
	public static function post_type_label($attr, $cont){ return get_post_type_object( get_post_type() )->labels->singular_name; }
	public static function post_status($attr, $cont){ return get_post_status(); }
	public static function post_status_label($attr, $cont){ return get_post_status_object( get_post_status() )->label; }

	public static function post_number($attr, $cont, $list){ return (int) ($list->posts_query->current_post + 1); }
	public static function post_permalink($attr, $cont){ return get_permalink(); }
	public static function post_class($attr, $cont){ return join( ' ', get_post_class() ); }
	public static function post_title($attr, $cont)
	{
		$return = get_the_title();
		if( isset($attr['wordlimit']) ){
			$wordlimit = $attr['wordlimit'];
			$return = wp_trim_words( $return, $wordlimit );
		}
		elseif( isset($attr['charlimit']) ){
			$charlimit = $attr['charlimit'];
			$return = substr($return, 0, $charlimit );
		}
		return $return;
	}
	public static function post_name($attr, $cont){ global $post; return $post->post_name; }
	public static function post_comment_url($attr, $cont){ return get_permalink() . "#comments"; }
	public static function post_comment_count($attr, $cont){ global $post; return (int) $post->comment_count; }

	public static function post_the_date($attr, $cont)
	{
		$format = $before = $after = '';
		if( isset($attr['format']) )
			$format = $attr['format'];
		if( isset($attr['before']) )
			$before = $attr['before'];
		if( isset($attr['after']) )
			$after = $attr['after'];

		return the_date( $format, $before, $after, false );
	}
	public static function post_date($attr, $cont)
	{
		$format = get_option('date_format');
		if( isset($attr['format']) ){
			$format = $attr['format'];
		}
		return get_the_date( $format );
	}
	public static function post_time($attr, $cont)
	{
		$format = get_option('time_format');
		if( isset($attr['format']) ){
			$format = $attr['format'];
		}
		return get_the_time($format);
	}
	public static function post_modified_date($attr, $cont)
	{
		$format = get_option('date_format');
		if( isset($attr['format']) ){
			$format = $attr['format'];
		}
		return get_post_modified_time($format);
	}
	public static function post_modified_time($attr, $cont)
	{
		$format = get_option('time_format');
		if( isset($attr['format']) ){
			$format = $attr['format'];
		}
		return get_post_modified_time($format);
	}
	public static function post_author_meta( $attr, $cont)
	{
		if( isset($attr) && !is_array($attr) && is_string($attr) ){
			$name = trim($attr);
			$attr = array();
		}
		elseif( isset($attr['name']) ){
			$name = $attr['name'];
		}
		if( empty($name) || in_array($name, array('pass', 'user_pass')))
			return;

		return get_the_author_meta( $name, get_the_author_meta('ID') );
	}

	public static function post_author_name($attr, $cont){ return get_the_author_meta('display_name'); }
	public static function post_author_url($attr, $cont){ return get_author_posts_url( get_the_author_meta('ID') ); }
	public static function post_author_email($attr, $cont){ return get_the_author_meta('user_email'); }
	public static function post_author_avatar($attr, $cont)
	{
		$size = 32;
		if( isset($attr['size']) ){
			$size = $attr['size'];
		}
		return get_avatar( get_the_author_meta('user_email'), $size );
	}

	public static function post_excerpt( $attr, $cont )
	{
		$post = get_post();
		$excerpt = $post->post_excerpt;
		if ( '' == $excerpt )
		{ $excerpt = $post->post_content; }

		if( isset($attr['wordlimit']) ){
			$wordlimit = (int) $attr['wordlimit'];
			$excerpt = wp_trim_words( $excerpt, $wordlimit );
		}

		return $excerpt;
	}
	public static function post_content($attr, $cont)
	{
		global $post;
		// Post content without wrapper --
		$content = apply_filters( 'the_content', get_the_content() );
		$content = str_replace(']]>', ']]&gt;', $content);
		return $content;
	}
	public static function post_thumbnail($attr, $cont)
	{
		if( isset($attr['size']) )
		{ $size = $attr['size']; }

		elseif( isset($attr['width']) ){
			if( isset($attr['height']) )
			{ $height = $attr['height']; }
			else
			{ $height = 9999; }
			$size = array($attr['width'], $height);
		}

		elseif( isset($attr['height']) )
		{
			if( isset($attr['width']) )
			{ $width = $attr['width']; }
			else
			{ $width = 9999; }
			$size = array($width, $attr['height']);
		}
		else
		{ $size = 'post-thumbnail'; }


		$post_id = get_the_ID();
		$post_thumbnail_id = (int) get_post_thumbnail_id( $post_id );


		if( isset($attr['return']) && 'id' == $attr['return'] )
		{ return $post_thumbnail_id; }

		elseif( isset($attr['return']) && 'src' == $attr['return'] )
		{
			$img = wp_get_attachment_image_src( $post_thumbnail_id, $size );
			return isset($img[0]) ? $img[0] : '';
		}
		elseif ( $post_thumbnail_id )
		{
			return wp_get_attachment_image( $post_thumbnail_id, $size, false, $attr );
		}
		elseif ( !empty($attr['placeholder']) )
		{
			return $attr['placeholder'];
		}

		return '';
	}


	/**
	 * Display Image From Post Content
	 * @since 1.9.1
	**/

	public static function post_image( $attr, $cont )
	{
		global $post;

		$return = '';
		if( ! isset($post) || ! isset($post->post_content) || empty($post->post_content) )
		{ return $return; }
		

		$position = '';
		if( isset($attr['position']) )
		{ $position = $attr['position']; }

		preg_match_all( "/<img[^>]*src\s*=\s*[\'\"]([+:%\/\?~=&;\\\(\),._a-zA-Z0-9-]*)[\'\" ]?/i", $post->post_content, $images, PREG_SET_ORDER );
		if( !empty($images) )
		{
			$image = $position == 'last' ? array_pop( $images ) : array_shift( $images );
			if( !isset($image['1']) || empty($image['1']) )
			{ return $return; }

			$attrs = array('src' => $image['1']);
			foreach( array('width', 'height', 'class') as $a ){
				if( isset($attr[$a]) )
				{ $attrs[$a] = $attr[$a]; }
			}

			$return = rtrim("<img");
			foreach ( $attrs as $name => $value )
			{ $return .= " $name=" . '"' . $value . '"'; }
			$return .= ' />';
		}

		// if no images were found & use_fallback is set to true(bool)
		elseif( isset($attr['use_fallback']) && !empty($attr['use_fallback']) )
		{
			// use post thumbnail as fallback, $attr is already similar for both methods
			$return = self::post_thumbnail( $attr, $cont );

			// @ attachment_thumbnail
			if( empty($return) )
			{ $return = self::attachment_thumbnail( $attr, $cont ); }
		}

		return $return;
	}


	public static function post_meta($attr, $cont)
	{
		if( isset($attr) && !is_array($attr) && is_string($attr) ){
			$meta_key = trim($attr);
			$attr = array();
		}

		if( isset($attr['key']) )
		{ $meta_key = $attr['key']; }
		elseif( isset($attr['meta_key']) )
		{ $meta_key = $attr['meta_key']; }
		if( ! $meta_key )
		{ return; }

		$single = ! ( isset($attr) && is_array($attr) && array_key_exists('multiple', $attr) ?  (bool) $attr['multiple'] : false );

		$sep = ', ';
		if( isset($attr['sep']) ){
			$sep = $attr['sep'];
		}

		$meta_value = get_post_meta( get_the_ID(), $meta_key, $single );

		// end the game here if the value is string
		if( ! is_object($meta_value) && ! is_array($meta_value) )
		{ return $meta_value; }


		$return = '';
		if( is_object($meta_value) )
		{ $meta_value = get_object_vars($meta_value); }


		if( is_array($meta_value) && !empty($meta_value) )
		{
			// when meta value is serialized array, return specific array value by using subkey
			if( isset($attr['sub_key']) && !empty($attr['sub_key']) ){
				if( array_key_exists($attr['sub_key'], $meta_value) ){
					return $meta_value[ $attr['sub_key'] ];
				}
			}
			else{
				$values = array();
				foreach( $meta_value as $r => $d ){
					if( ! is_array($d) )
					{ $values[] = $d; }
				}

				if( $values )
				{ return implode( $sep, $values ); }
			}
		}

		return '';
	}

	public static function post_meta_date( $attr, $content )
	{
		if( isset($attr['key']) )
		{ $meta_key = $attr['key']; }
		elseif( isset($attr['meta_key']) )
		{ $meta_key = $attr['meta_key']; }
		if( ! $meta_key )
		{ return; }

		$format = isset($attr['format']) ? $attr['format'] : 'Y-m-d';
		$meta_value = get_post_meta( get_the_ID(), $meta_key, true );

		return !empty($meta_value) ? mysql2date( $format, $meta_value ) : '';
	}
	
	public static function post_terms($attr, $cont)
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

		// New code
		if( isset($attr['return']) && in_array($attr['return'], array('name', 'slug') ) )
		{
			$terms = get_the_terms( get_the_ID(), $taxonomy );
			$names = wp_list_pluck( $terms, $attr['return'] );

			return implode( $sep, $names );
		}
		else
		{
			return get_the_term_list( get_the_ID(), $taxonomy, '', $sep );
		}
	}


	// Attachment
	public static function attachment_thumbnail($attr, $cont)
	{
		if( isset($attr['size']) )
		{ $size = $attr['size']; }

		elseif( isset($attr['width']) ){
			if( isset($attr['height']) )
			{ $height = $attr['height']; }
			else
			{ $height = 9999; }
			$size = array($attr['width'], $height);
		}

		elseif( isset($attr['height']) )
		{
			if( isset($attr['width']) )
			{ $width = $attr['width']; }
			else
			{ $width = 9999; }
			$size = array($width, $attr['height']);
		}
		else
		{ $size = 'post-thumbnail'; }


		if( isset($attr['id']) )
		{ $attachment_id = (int) $attr['id']; }
		elseif( isset($attr['meta_key']) )
		{ $attachment_id = get_post_meta(get_the_ID(), $attr['meta_key'], true); }
		else
		{ $attachment_id = get_the_ID(); }


		if( 'attachment' != get_post_type($attachment_id) )
		{ return ''; }


		// if attachment is an image, then we have something more to return
		if( wp_attachment_is_image($attachment_id) )
		{
			if( isset($attr['return']) && 'id' == $attr['return'] )
			{ return $attachment_id; }
	
			elseif( isset($attr['return']) && 'src' == $attr['return'] )
			{
				$img = wp_get_attachment_image_src( $attachment_id, $size );
				return isset($img[0]) ? $img[0] : '';
			}
			elseif ( $attachment_id )
			{ return wp_get_attachment_image( $attachment_id, $size, false, $attr ); }

			return '';
		}

		$icon = true;
		if ( $attachment_id )
		{ $html = wp_get_attachment_image( $attachment_id, $size, $icon, $attr ); } 
		else
		{ $html = ''; }

		return $html;
	}
	public static function attachment_url($attr, $cont)
	{
		if( isset($attr['id']) )
			$post_id = (int) $attr['id'];
		else
			$post_id = get_the_ID();

		if( 'attachment' != get_post_type($post_id) )
			return '';

		return wp_get_attachment_url($post_id);
	}

	// Parent
	public static function parent_permalink($attr, $cont)
	{
		$post = get_post();
		$parent = ( $post->post_parent > 0 && $post->post_parent != $post->ID ) ? get_post( $post->post_parent ) : false;
		if( $parent )
			return get_permalink( $parent );
		elseif( isset($attr['self']) && $attr['self'] )
			return get_permalink( $post );
		else
			return '#';
	}


	// Tempate
	public static function template_title($attr, $cont){
		return sprintf( 
			'<a class="post_title w4pl_post_title" href="%1$s" title="View %2$s">%3$s</a>', 
			get_permalink(),
			the_title_attribute( array('echo' => false)),
			get_the_title()
		);
	}

	public static function template_meta($attr, $cont){
		return sprintf( 
			__('Posted on','w4pl'). ': <abbr class="published post-date" title="%1$s">%2$s</abbr> <span class="post_author">'. __('by','w4pl') .' %3$s</span>', 
			get_the_time( get_option('time_format') ), 
			get_the_time( get_option('date_format') ), 
			get_the_author()
		);
	}
	public static function template_date($attr, $cont){
		return sprintf( 
			'<abbr class="published post-date" title="%1$s"><strong>'. __('Published','w4pl') .'</strong>: %2$s</abbr>',
			get_the_time( get_option('time_format') ), 
			get_the_time( get_option('date_format') )
		);
	}
	public static function template_modified($attr, $cont){
		return sprintf( 
			'<abbr class="modified post-modified" title="%1$s"><strong>'. __('Updated','w4pl') .'</strong>: %2$s</abbr>',
			get_post_modified_time( get_option('time_format')), 
			get_post_modified_time( get_option('date_format'))
		);
	}
	public static function template_author($attr, $cont){
		return sprintf( 
			'<a href="%1$s" title="View all posts by %2$s" rel="author">%2$s</a>', 
			get_author_posts_url( get_the_author_meta('ID') ), 
			get_the_author() 
		);
	}
	public static function template_excerpt($attr, $cont, $list){
		return sprintf( 
			'<div class="post-excerpt">%s</div>',
			self::post_excerpt($attr, $cont, $list)
		);
	}
	public static function template_content($attr, $cont){
		return sprintf( 
			'<div class="post-excerpt">%s</div>',
			$this->post_content($attr, $cont)
		);
	}
	public static function template_more($attr, $cont){
		$read_more = !empty( $attr['text'] ) ? $attr['text'] : __( 'Continue reading &raquo;', 'w4pl' );
		return sprintf( 
			'<a class="read_more" href="%1$s" title="%3$s %2$s">%3$s</a>', 
			get_permalink(), 
			get_the_title(), 
			esc_attr( $read_more )
		);
	}


	public static function post_group_id( $attr, $cont, $list )
	{
		return isset($list->current_group) ? $list->current_group['id'] : 0;
	}
	public static function post_group_title( $attr, $cont, $list )
	{
		return isset($list->current_group) ? $list->current_group['title'] : '';
	}
	public static function post_group_url( $attr, $cont, $list )
	{
		return isset($list->current_group) ? $list->current_group['url'] : '';
	}


	/* Filer Option */

	public function pre_get_options($options)
	{
		if( !isset($options) || !is_array($options) )
			$options = array();

		if( isset($options['list_type']) && in_array($options['list_type'], array('posts', 'terms.posts', 'users.posts') ) )
		{
			$options = wp_parse_args( $options, array(
				'post_type' 		=> array('post'), 
				'post_status' 		=> array('publish'), 
				'post_s'			=> '',
				'post__in' 			=> '', 
				'post__not_in' 		=> '', 
				'post_parent__in' 	=> '',
				'author__in' 		=> '',

				'exclude_self'		=> '',
				'child_of_self'		=> '',

				'posts_per_page'	=> '',
				'limit'				=> '',
				'offset'			=> '',
				'orderby'			=> 'date',
				'order'				=> 'DESC',
				'groupby'			=> '',
				'groupby_time'		=> '',
				'groupby_meta_key'	=> '',
				'group_order'		=> ''
			));

			if( !empty($options['post_type']) && !is_array($options['post_type']) )
			{ $options['post_type'] = array($options['post_type']); }
		}

		return $options;
	}


	/* Option Page Fields */

	public function list_edit_form_fields( $fields, $options )
	{
		$list_type = $options['list_type'];
		if( ! in_array($list_type, array('posts', 'terms.posts', 'users.posts') ) )
			return $fields;

		/* GROUP 2 */
		$fields['before_field_group_query'] = array(
			'position'		=> '51',
			'html' 			=> '<div id="w4pl_field_group_query" class="w4pl_field_group">
								<div class="w4pl_group_title">Posts</div>
								<div class="w4pl_group_fields"><div class="w4pl_group_fields_wrap">'
		);

		$fields['post_type'] = array(
			'position'		=> '55',
			'option_name' 	=> 'post_type',
			'name' 			=> 'w4pl[post_type]',
			'label' 		=> __('Post type', 'w4pl'),
			'type' 			=> 'checkbox',
			'option' 		=> W4PL_Plugin::post_type_options(),
			'input_class'	=> 'w4pl_onchange_lfr'
		);

		// mime type field
		if( $mime_type_options = W4PL_Plugin::post_mime_type_options($options['post_type']) )
		{
			$fields['post_mime_type'] = array(
				'position' 		=> '56',
				'option_name' 	=> 'post_mime_type',
				'name' 			=> 'w4pl[post_mime_type]',
				'label' 		=> __('Post mime type', 'w4pl'),
				'type' 			=> 'checkbox',
				'option' 		=> $mime_type_options,
				'desc' 			=> 'if displaying attachment, choose mime type to restrcit result to specific file types.'
			);
		}

		$fields['post_status'] = array(
			'position'		=> '60',
			'option_name' 	=> 'post_status',
			'name' 			=> 'w4pl[post_status]',
			'label' 		=> __('Post status', 'w4pl'),
			'type' 			=> 'checkbox',
			'option' 		=> array(
				'any' 			=> __('Any', 'w4pl'), 
				'publish' 		=> __('Publish', 'w4pl'), 
				'pending' 		=> __('Pending', 'w4pl'), 
				'future' 		=> __('Future', 'w4pl'), 
				'inherit' 		=> __('Inherit', 'w4pl')
			)
		);

		$fields['post_s'] = array(
			'position'		=> '64',
			'option_name' 	=> 'post_s',
			'name' 			=> 'w4pl[post_s]',
			'label' 		=> __('Search keywords', 'w4pl'),
			'type' 			=> 'text',
			'input_class' 	=> 'widefat',
			'desc' 			=> 'search posts'
		);
		$fields['post__in'] = array(
			'position'		=> '65',
			'option_name' 	=> 'post__in',
			'name' 			=> 'w4pl[post__in]',
			'label' 		=> __('Include posts', 'w4pl'),
			'type' 			=> 'text',
			'input_class' 	=> 'widefat',
			'desc' 			=> 'comma separated post id'
		);
		$fields['post__not_in'] = array(
			'position'		=> '66',
			'option_name' 	=> 'post__not_in',
			'name' 			=> 'w4pl[post__not_in]',
			'label' 		=> __('Exclude posts', 'w4pl'),
			'type' 			=> 'text',
			'input_class' 	=> 'widefat',
			'desc' 			=> 'comma separated post id'
		);
		$fields['post_parent__in'] = array(
			'position'		=> '67',
			'option_name' 	=> 'post_parent__in',
			'name' 			=> 'w4pl[post_parent__in]',
			'label' 		=> __('Post parent', 'w4pl'),
			'type' 			=> 'text',
			'input_class' 	=> 'widefat',
			'desc' 			=> 'display child posts. comma separated parent post ids'
		);
		$fields['author__in'] = array(
			'position'		=> '68',
			'option_name' 	=> 'author__in',
			'name' 			=> 'w4pl[author__in]',
			'label' 		=> __('Post author', 'w4pl'),
			'type' 			=> 'text',
			'input_class' 	=> 'widefat',
			'desc' 			=> 'comma separated user/author ids. use 0 to indicate current user.'
		);

		$fields['exclude_self'] = array(
			'position'		=> '69',
			'option_name' 	=> 'exclude_self',
			'name' 			=> 'w4pl[exclude_self]',
			'label' 		=> __('Exclude self', 'w4pl'),
			'type' 			=> 'radio',
			'option' 		=> array('' => 'No', 'yes' => 'Yes'),
			'desc' 			=> 'exclude current post on single post/page pages'
		);
		$fields['child_of_self'] = array(
			'position'		=> '69.1',
			'option_name' 	=> 'child_of_self',
			'name' 			=> 'w4pl[child_of_self]',
			'label' 		=> __('Child of self', 'w4pl'),
			'type' 			=> 'radio',
			'option' 		=> array('' => 'No', 'yes' => 'Yes'),
			'desc' 			=> 'display child posts of current post/page/custom post type'
		);

		$fields['orderby'] = array(
			'position'		=> '70',
			'option_name' 	=> 'orderby',
			'name' 			=> 'w4pl[orderby]',
			'label' 		=> __('Orderby', 'w4pl'),
			'type' 			=> 'select',
			'option' 		=> W4PL_Plugin::post_orderby_options( $options['post_type'] ),
			'input_after'	=> '<div id="orderby_meta_key_wrap">Meta key: <input name="w4pl[orderby_meta_key]" type="text" value="'
				. (isset($options['orderby_meta_key']) ? esc_attr($options['orderby_meta_key']) : '') .'" /></div>'
		);
		$fields['order'] = array(
			'position'		=> '71',
			'option_name' 	=> 'order',
			'name' 			=> 'w4pl[order]',
			'label' 		=> __('Order', 'w4pl'),
			'type' 			=> 'radio',
			'option' 		=> array('ASC' => 'ASC', 'DESC' => 'DESC')
		);


		$fields['limit'] = array(
			'position'		=> '76',
			'option_name' 	=> 'limit',
			'name' 			=> 'w4pl[limit]',
			'label' 		=> __('Maximum items', 'w4pl'),
			'type' 			=> 'text',
			'desc2' 		=> 'maximum results to display in total, restrict number of items even while paginating'
		);

		if( 'posts' == $options['list_type'] )
		{
			$fields['offset'] = array(
				'position'		=> '77',
				'option_name' 	=> 'offset',
				'name' 			=> 'w4pl[offset]',
				'label' 		=> __('Offset', 'w4pl'),
				'type' 			=> 'text',
				'desc2' 		=> 'skip given number of posts from beginning. while building lists using the same filters but to display on separate places, use offset to skip the previously displayed amount of posts'
			);
			$fields['posts_per_page'] = array(
				'position'		=> '75',
				'option_name' 	=> 'posts_per_page',
				'name' 			=> 'w4pl[posts_per_page]',
				'label' 		=> __('Items per page', 'w4pl'),
				'type' 			=> 'text',
				'placeholder'	=> get_option('posts_per_page'),
				'desc' 			=> 'number of items to show per page
				<br />use <strong>-1</strong> to display all' 
			);
		}

		if( 'posts' == $options['list_type'] )
		{
			$fields['groupby'] = array(
				'position' 		=> '95',
				'option_name' 	=> 'groupby',
				'name' 			=> 'w4pl[groupby]',
				'label' 		=> __('Group by', 'w4pl'),
				'type' 			=> 'select',
				'option' 		=> W4PL_Plugin::post_groupby_options($options['post_type']),
				'input_class'	=> 'w4pl_onchange_lfr'
			);

			if( in_array($options['groupby'], array('year', 'month', 'yearmonth') ) )
			{
				$fields['groupby_time'] = array(
					'position' 		=> '95.5',
					'option_name' 	=> 'groupby_time',
					'name' 			=> 'w4pl[groupby_time]',
					'label' 		=> __('Group by Date', 'w4pl'),
					'type' 			=> 'radio',
					'option' 		=> array('post_date' => 'Publish date', 'post_modified' => 'Modified date'),
					'desc2' 		=> 'which date we will use to caculate the group time'
				);
			}
			elseif( in_array($options['groupby'], array('meta_value') ) )
			{
				$fields['groupby_meta_key'] = array(
					'position' 		=> '95.5',
					'option_name' 	=> 'groupby_meta_key',
					'name' 			=> 'w4pl[groupby_meta_key]',
					'label' 		=> __('Group by "Custom field" name', 'w4pl'),
					'type' 			=> 'text'
				);
			}

			$fields['group_order'] = array(
				'position' 		=> '96',
				'option_name' 	=> 'group_order',
				'name' 			=> 'w4pl[group_order]',
				'label' 		=> __('Group Order', 'w4pl'),
				'type' 			=> 'radio',
				'option' 		=> array('' => 'None', 'ASC' => 'ASC', 'DESC' => 'DESC')
			);
		}

		$fields['after_field_group_query'] = array(
			'position'		=> '100',
			'html' 			=> '</div><!--.w4pl_group_fields_wrap--></div><!--.w4pl_group_fields--></div><!--#w4pl_field_group_query-->'
		);

		return $fields;
	}


	/* Parse List Query Args */

	public function parse_query_args( $list )
	{
		if( in_array($list->options['list_type'], array('posts', 'terms.posts', 'users.posts') ) )
		{
			// push default options to query var
			foreach( array(
				'orderby', 
				'order', 
				'posts_per_page', 
				'offset'
			) as $option_name )
			{
				if( !empty($list->options[$option_name]) )
					$list->posts_args[$option_name] = $list->options[$option_name];
			}

			if( !empty($list->options['post_s']) )
				$list->posts_args['s'] = $list->options['post_s'];

			// array
			foreach(array(
				'post_mime_type', 
				'post_type', 
				'post_status'
			) as $option_name) {
				if(! empty($list->options[$option_name])) {
					$list->posts_args[$option_name] = $list->options[$option_name];
				}
			}


			// comma separated ids
			foreach( array(
				'post__in', 
				'post__not_in', 
				'post_parent__in', 
				'author__in',
			) as $option_name ) {
				if ( 'author__in' == $option_name) {
					if ($list->options[$option_name] == '0') {
						$list->options[$option_name] = array();
					}

					if (! empty($list->options[$option_name])) {
						$opt = wp_parse_id_list($list->options[$option_name]);
						foreach($opt as $k => $v) {
							if(0 == $v && get_current_user_id()) {
								$opt[$k] = get_current_user_id();
							}
						}

						$opt = array_unique( $opt);

						if (count($opt) == 1 && $opt[0] == 0) {
							$list->posts_args['p'] = -1;
						} else {
							$list->posts_args[$option_name] = $opt;
						}
					}
				} elseif (! empty($list->options[$option_name])) {
					$opt = wp_parse_id_list($list->options[$option_name]);
					if (! empty($opt)) {
						$list->posts_args[$option_name] = $opt;
					}
				}
			}

			# self::p( $list->posts_args );

			// exclude current post
			if( is_singular() && get_the_ID()) {
				if( isset($list->options['exclude_self']) && 'yes' == $list->options['exclude_self']) {
					if (! isset ($list->posts_args['post__not_in']) || empty ($list->posts_args['post__not_in'])) {
						$list->posts_args['post__not_in'] = array( get_the_ID() );
					} elseif (is_array ($list->posts_args['post__not_in'])) {
						$list->posts_args['post__not_in'][] = get_the_ID();
					}
				}

				if(isset ($list->options['child_of_self']) && 'yes' == $list->options['child_of_self']) {
					if(! isset ($list->posts_args['post_parent__in']) || empty($list->posts_args['post_parent__in'])) {
						$list->posts_args['post_parent__in'] = array( get_the_ID() );
					}
					elseif (is_array ($list->posts_args['post_parent__in'])) {
						$list->posts_args['post_parent__in'][] = get_the_ID();
					}
				}
			}

			// orderby meta key/value
			if ($list->options['orderby'] == 'meta_value' || $list->options['orderby'] == 'meta_value_num') {
				$list->posts_args['meta_key'] = $list->options['orderby_meta_key'];
			}

			// we catch paged query using a non-pretty query var
			$paged = isset($_REQUEST['page'. $list->id]) ? $_REQUEST['page'. $list->id] : 1;

			$defaults = array(
				'post_status' 	=> 'publish',
				'post_type' 	=> 'post',
				'paged'			=> $paged
			);

			$list->posts_args = wp_parse_args( $list->posts_args, $defaults );

			// set the posts per page
			if( !isset($list->posts_args['posts_per_page']) || empty($list->posts_args['posts_per_page']) ){
				$list->posts_args['posts_per_page'] = get_option('posts_per_page', 10);
			}


			// while maximum limit is set, we only fetch till the maximum post
			if( !empty($list->options['limit']) && $list->options['limit'] < ($list->posts_args['posts_per_page'] * $paged) ) {
				$list->posts_args['offset'] = (int) $list->options['offset'] + ( ($paged - 1) * $list->posts_args['posts_per_page'] );
				$list->posts_args['posts_per_page'] = $list->options['limit'] - ( $list->posts_args['posts_per_page'] * ($paged-1) );
			}
			// while maximum limit is set, we only fetch till the maximum post
			elseif( !empty($list->options['offset']) )
			{
				$list->posts_args['offset'] = (int) $list->options['offset'] + ($paged - 1) * $list->posts_args['posts_per_page'];
			}
		}
		// ends post query
	}
}

	new W4PL_Helper_Posts;
?>