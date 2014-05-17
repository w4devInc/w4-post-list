<?php
class W4PL_Core 
{
	function __construct()
	{
		// register post list
		add_action( 'init', 									array($this, 'register_post_type'));


		// add postlist shortcode
		add_shortcode( 'postlist', 								array($this, 'shortcode') );


		// register scripts
		add_action( 'wp_enqueue_scripts', 						array($this, 'register_scripts'), 2 );
		add_action( 'admin_enqueue_scripts', 					array($this, 'register_scripts'), 2 );


		// allow shortcode for text widget content
		add_filter( 'widget_text', 								'do_shortcode');


		// get all available shortcodes
		add_filter( 'w4pl/get_shortcodes', 						array($this, 'get_shortcodes') );


		// display list creation option page html
		add_filter( 'w4pl/list_options_template', 				array($this, 'list_options_template') );


		// load list options template from posted data.
		add_action( 'wp_ajax_w4pl_list_options_template', 		array($this, 'list_options_template_ajax') );


		// display list creation option page html
		# add_action( 'w4pl/list_options_head', 					array($this, 'list_options_head') );


		// display list creation option page scripts, scripts get loaded on the head tag of that page.
		add_action( 'w4pl/list_options_print_scripts', 			array($this, 'list_options_print_scripts') );


		// ouput fields template specific for selected post type
		add_action( 'wp_ajax_w4pl_post_type_fields_template', 	array($this, 'post_type_fields_template_ajax') );


		// get shortcode from posted data
		add_action( 'wp_ajax_w4pl_get_shortcode', 				array($this, 'w4pl_get_shortcode_ajax') );
	}



	/*
	 * Register List Post Type
	*/

	public function register_post_type()
	{
		global $wp, $wp_rewrite, $wp_post_types;

		register_post_type( W4PL_SLUG, array(
			'labels' => array(
				'name' 					=> _x('Lists', 'post type general name'),
				'singular_name' 		=> _x('List', 'post type singular name'),
				'menu_name'				=> W4PL_NAME,
				'all_items'				=> __('All Lists', W4PL_TXT_DOMAIN),
				'add_new' 				=> _x('Add New', 'note item'),
				'add_new_item' 			=> __('New List'),
				'edit_item' 			=> __('Edit List'),
				'new_item' 				=> __('New List'),
				'view_item' 			=> __('View List'),
				'search_items' 			=> __('Search List'),
				'not_found' 			=> __('No List found'),
				'not_found_in_trash' 	=> __('No List found in Trash'),
				'parent_item_colon' 	=> ''
			),
			'show_ui'  				=> true,
			'public'  				=> false,
			'has_archive'			=> false,
			'delete_with_user'		=> false,
			'show_in_admin_bar'		=> false,
			'supports' 				=> array('title' ),
			'menu_icon'				=> 'dashicons-admin-generic'
		));
	}



	/*
	 * Shortcodes
	*/

	public static function get_shortcodes()
	{
		// Shortcodes
		return array(
			'posts' => array(
				'group' => 'Main', 
				'code' => '[posts]'. "\n\n" .'[/posts]', 
				'func' => '',
				'desc' => '<strong>return</strong> the posts template'
			),
			'groups' => array(
				'group' => 'Main', 
				'code' => '[groups]'. "\n\n" .'[/groups]', 
				'func' => '',
				'desc' => '<strong>return</strong> the groups template'
			),
			'nav' => array(
				'group' => 'Main', 
				'code' => '[nav ajax=""]', 
				'func' => '',
				'desc' => '<strong>return</strong> pagination for the list
            <br><br><strong>Attributes</strong>:
            <br><strong>type</strong> = (text) allowed values  - plain, list, nav
            <br><strong>ajax</strong> = (0|1) use pagination with ajax'
			),
			'id' => array(
				'group' => 'Post', 
				'func' => 'post_id', 
				'desc' => '<strong>Output</strong>: post id'
			),
			'ID' => array(
				'group' => 'Post', 
				'func' => 'post_id', 
				'desc' => '<strong>Output</strong>: post id'
			),
			'post_id' => array(
				'group' => 'Post', 
				'func' => 'post_id', 
				'desc' => '<strong>Output</strong>: post id'
			),
			'post_number' => array(
				'group' => 'Post', 
				'func' => 'post_number', 
				'desc' => '<strong>Output</strong>: post item number, starting from 1'
			),
			'post_permalink' => array(
				'group' => 'Post', 
				'func' => 'post_permalink', 
				'desc' => '<strong>Output</strong>: post url/link'
			),
			'post_class' => array(
				'group' => 'Post', 
				'func' => 'post_class', 
				'desc' => '<strong>Output</strong>: post html classes'
			),
			'post_title' => array(
				'group' => 'Post', 
				'func' => 'post_title', 
				'desc' => '<strong>Output</strong>: post title
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>wordlimit</strong> = (number), limit number of words to display'
			),
			'post_comment_url' => array(
				'group' => 'Post', 
				'func' => 'post_comment_url', 
				'desc' => '<strong>Output</strong>: post comment form link/url'
			),
			'post_comment_count'=> array(
				'group' => 'Post', 
				'func' => 'post_comment_count', 
				'desc' => '<strong>Output</strong>: (numeric) amount of approved comments'
			),
			'post_date' => array(
				'group' => 'Post', 
				'code' => '[post_date format="'. get_option('date_format') .'"]', 
				'func' => 'post_date', 
				'desc' => '<strong>Output</strong>: post date (date formatted)
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>format</strong> = php datetime format'
			),
			'post_time' => array(
				'group' => 'Post', 
				'code' => '[post_time format="'. get_option('time_format') .'"]', 
				'func' => 'post_time', 
				'desc' => '<strong>Output</strong>: post date (time formatted)
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>format</strong> = php datetime format'
			),
			'post_modified_date' => array(
				'group' => 'Post', 
				'code' => '[post_modified_date format="'. get_option('date_format') .'"]', 
				'func' => 'post_modified_date', 
				'desc' => '<strong>Output</strong>: post modified date (date formatted)
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>format</strong> = php datetime format'
			),
			'post_modified_time'=> array(
				'group' => 'Post', 
				'code' => '[post_modified_time format="'. get_option('time_format') .'"]', 
				'func' => 'post_modified_time', 
				'desc' => '<strong>Output</strong>: post modified date (time formatted)
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>format</strong> = php datetime format'
			),
			'post_author_name' => array(
				'group' => 'Post', 
				'func' => 'post_author_name', 
				'desc' => '<strong>Output</strong>: post author name'
			),
			'post_author_url'	=> array(
				'group' => 'Post', 
				'func' => 'post_author_url', 
				'desc' => '<strong>Output</strong>: post author name url'
			),
			'post_author_email'	=> array(
				'group' => 'Post', 
				'func' => 'post_author_email', 
				'desc' => '<strong>Output</strong>: post author email address'
			),
			'post_author_avatar'=> array(
				'group' => 'Post', 
				'code' => '[post_author_avatar size=""]', 
				'func' => 'post_author_avatar', 
				'desc' => '<strong>Output</strong>: post author avatar
				<br /><br /><strong>attributes:</strong>
				<br /><strong>size</strong> = (number), avatar image size'
			),
			'post_excerpt' => array(
				'group' => 'Post', 
				'code' => '[post_excerpt wordlimit=""]', 
				'func' => 'post_excerpt', 
				'desc' => '<strong>Output</strong>: post excerpt/short description
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>wordlimit</strong> = (number), limit number of words to display'
			),
			'post_content'		=> array(
				'group' => 'Post', 
				'func' => 'post_content', 
				'desc' => '<strong>Output</strong>: post content'
			),
			'post_thumbnail' => array(
				'group' => 'Post', 
				'code' => '[post_thumbnail size="" return=""]', 
				'func' => 'post_thumbnail', 
				'desc' => '<strong>Output</strong>: (text|number) based on the rerurn attribute & only if the post has a thumbnail assigned
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>return</strong> = (text|number), 
				<br />----"src" - will return src of the image, 
				<br />----"id" - will return id of the image, 
				<br />----by default it will return image html
				<br /><strong>size</strong> = (string), post_thumbnail size

				<br /><strong>width</strong> = (number), post_thumbnail width
				<br /><strong>height</strong> = (number), post_thumbnail height'
			),
			'post_meta' => array(
				'group' => 'Post', 
				'code' => '[post_meta key="" multiple="0"]', 
				'func' => 'post_meta', 
				'desc' => '<strong>Output</strong>: post meta value. if return value is an array, it will be migrated to string by using explode function
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>key</strong> = (text|number), meta key name
				<br /><strong>multiple</strong> = (0|1), display meta value at multiple occurence
				<br /><strong>sep</strong> = (text), separate array meta value into string'
			),
			'post_terms' => array(
				'group' => 'Post', 
				'code' => '[post_terms tax="category" sep=", "]', 
				'func' => 'post_terms', 
				'desc' => '<strong>Output</strong>: post type terms. if return value is an array, it will be migrated to string by using explode function
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>tax</strong> = (string), taxonomy name
				<br /><strong>sep</strong> = (string), separate array meta value into string'
			),
			'attachment_thumbnail' => array(
				'group' => 'Media', 
				'code' => '[attachment_thumbnail size=""]', 
				'func' => 'attachment_thumbnail', 
				'desc' => '<strong>Output</strong>: if the post is an attachment, the attached image is displayed as thumbnail
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>size</strong> = (string), image size
				<br /><strong>width</strong> = (number), image width
				<br /><strong>height</strong> = (number), image height'
			),
			'attachment_url' => array(
				'group' => 'Media', 
				'func' => 'attachment_url', 
				'desc' => '<strong>Output</strong>:  if the post is an attachment, the attached image source is returned'
			),
			'group_title' => array(
				'group' => 'Group', 
				'func' => '', 
				'desc' => '<strong>Output</strong>: group name / title'
			),
			'group_url' => array(
				'group' => 'Group', 
				'func' => '', 
				'desc' => '<strong>Output</strong>: group page link'
			),

			'title' => array(
				'group' => 'Template', 
				'func' => 'template_title', 
				'desc' => '<strong>Output</strong>: title template'
			),
			'meta' => array(
				'group' => 'Template', 
				'func' => 'template_meta', 
				'desc' => '<strong>Output</strong>: meta template'
			),
			'publish' => array(
				'group' => 'Template', 
				'func' => 'template_date', 
				'desc' => '<strong>Output</strong>: publish time template'
			),
			'date'				=> array(
				'group' => 'Template', 
				'func' => 'template_date', 
				'desc' => '<strong>Output</strong>: publish time template'
			),
			'modified' => array(
				'group' => 'Template', 
				'func' => 'template_modified', 
				'desc' => '<strong>Output</strong>: modified time template'
			),
			'author' => array(
				'group' => 'Template', 
				'func' => 'template_author', 
				'desc' => '<strong>Output</strong>: author template'
			),
			'excerpt' => array(
				'group' => 'Template', 
				'func' => 'template_excerpt', 
				'desc' => '<strong>Output</strong>: excerpt template'
			),
			'content' => array(
				'group' => 'Template', 
				'func' => 'template_content', 
				'desc' => '<strong>Output</strong>: content template'
			),
			'more' => array(
				'group' => 'Template', 
				'func' => 'template_more',
				'desc' => '<strong>Output</strong>: more link template'
			)
		);
	}



	/*
	 * Display List Using Shortcode
	 * @param (array)
	 * @return (string)
	*/
	public function shortcode( $attr)
	{
		if( isset($attr['options']) ){
			$options = maybe_unserialize( base64_decode( str_replace( ' ', '', $attr['options'] ) ) );
		}
		elseif( isset($attr['id']) ){
			$options = get_post_meta( $attr['id'], '_w4pl', true );
		}
		else{
			if( !is_array($attr) )
				$attr = array($attr);

			$list_id = array_shift( $attr );
			$list_id = (int) $list_id;

			if( $list_id ){
				$options = get_post_meta( $list_id, '_w4pl', true );
			}
		}

		if( empty($options) )
			return '';

		return self::the_list( $options );
	}


	/*
	 * The List
	 * @param (int), list id
	 * @return (string)
	*/
	public static function the_list( $options )
	{
		$w4_post_list = new W4_Post_list();
		$list = $w4_post_list->prepare( $options );
		if( is_wp_error($list) )
		{
			if( is_user_logged_in() && current_user_can( 'delete_users') ){
				return '<p>
					<strong>W4 Post List Error:</strong> <span style="color:#FF0000">'. $list->get_error_message() .'</span>
					<br /><small>*** this error is only visible to admins and won\'t effect in search engine.</small>
				</p>';
			}
			return '<!-- W4 post list Error: '. $list->get_error_message() .'-->';
		}
		return "<!-- Post list Created by W4 post list WordPress Plugin @ http://w4dev.com/w4-plugin/w4-post-list -->\n" . $w4_post_list->display();
	}


	/*
	 * List Options Template
	 * @param $options (array)
	 * @echo (string)
	*/


	public static function list_options_template( $options )
	{
		if( !isset($options['id']) )
			$options['id'] = md5( microtime().rand() );
		
		if( ! isset($options['post_type']) )
			$options['post_type'] = 'post';

		if( ! isset($options['post_status']) )
			$options['post_status'] = 'publish';

		$fields = array();
		$fields['post_type'] = array(
			'position'		=> '5',
			'before'		=> '<h2>Type</h2>',
			'option_name' 	=> 'post_type',
			'name' 			=> 'w4pl[post_type]',
			'label' 		=> 'Post Type',
			'type' 			=> 'select',
			'option' 		=> self::post_type_options(),
			'input_after'	=> '<span class="spinner" style="position:relative; float:none; left:10px; top:5px; margin: 0; height:19px;"></span>'
		);
		$fields['before_post_type_options'] = array(
			'position'		=> '15',
			'type' 			=> 'html',
			'html' 			=> '<div id="w4pl_post_type_options">'
		);
		// intialize post type fields
		self::post_type_fields($fields, $options);
		$fields['after_post_type_options'] = array(
			'position'		=> '39',
			'type' 			=> 'html',
			'html' 			=> '</div><!--w4pl_post_type_options-->'
		);

		#echo '<pre>';	print_r($options);	echo '</pre>';

		$fields['post_status'] = array(
			'position'		=> '40',
			'before'		=> '<h2>Query</h2>',
			'option_name' 	=> 'post_status',
			'name' 			=> 'w4pl[post_status]',
			'label' 		=> 'Post Status',
			'type' 			=> 'checkbox',
			'option' 		=> array('any' => 'Any', 'publish' => 'Publish', 'pending' => 'Pending', 'future' => 'Future', 'inherit' => 'Inherit')
		);

		$fields['post__in'] = array(
			'position'		=> '45',
			'option_name' 	=> 'post__in',
			'name' 			=> 'w4pl[post__in]',
			'label' 		=> 'Include post by ids',
			'type' 			=> 'text',
			'desc' 			=> 'comma separated post id'
		);
		$fields['post__not_in'] = array(
			'position'		=> '46',
			'option_name' 	=> 'post__not_in',
			'name' 			=> 'w4pl[post__not_in]',
			'label' 		=> 'Exclude post by ids',
			'type' 			=> 'text',
			'desc' 			=> 'comma separated post id'
		);
		$fields['post_parent__in'] = array(
			'position'		=> '50',
			'option_name' 	=> 'post_parent__in',
			'name' 			=> 'w4pl[post_parent__in]',
			'label' 		=> 'Post parent ids',
			'type' 			=> 'text',
			'desc' 			=> 'comma separated post parent id'
		);
		$fields['author__in'] = array(
			'position'		=> '55',
			'option_name' 	=> 'author__in',
			'name' 			=> 'w4pl[author__in]',
			'label' 		=> 'Post author ids',
			'type' 			=> 'text',
			'desc' 			=> 'comma separated author id'
		);




		$fields['orderby'] = array(
			'position'		=> '65',
			'before'		=> '<h2>Order</h2>',
			'option_name' 	=> 'orderby',
			'name' 			=> 'w4pl[orderby]',
			'label' 		=> 'Orderby',
			'type' 			=> 'select',
			'option' 		=> self::post_orderby_options($options['post_type']),
			'input_after'	=> '<div id="orderby_meta_key_wrap">Meta key: <input name="w4pl[orderby_meta_key]" type="text" value="'
				. (isset($options['orderby_meta_key']) ? esc_attr($options['orderby_meta_key']) : '') .'" /></div>'
		);
		$fields['order'] = array(
			'position'		=> '70',
			'option_name' 	=> 'order',
			'name' 			=> 'w4pl[order]',
			'label' 		=> 'Order',
			'type' 			=> 'radio',
			'option' 		=> array('ASC' => 'ASC', 'DESC' => 'DESC')
		);


		$fields['posts_per_page'] = array(
			'position'		=> '75',
			'before'		=> '<h2>Limit</h2>',
			'option_name' 	=> 'posts_per_page',
			'name' 			=> 'w4pl[posts_per_page]',
			'label' 		=> 'Items per page',
			'type' 			=> 'text',
			'desc' 			=> 'number of items to show per page'
		);
		$fields['limit'] = array(
			'position'		=> '80',
			'option_name' 	=> 'limit',
			'name' 			=> 'w4pl[limit]',
			'label' 		=> 'Maximum items to display',
			'type' 			=> 'text',
			'desc' 			=> 'maximum results to display in total'
		);
		$fields['offset'] = array(
			'position'		=> '95',
			'option_name' 	=> 'offset',
			'name' 			=> 'w4pl[offset]',
			'label' 		=> 'Offset',
			'type' 			=> 'text',
			'desc' 			=> 'skip given number of posts from beginning'
		);


		$fields['template'] = array(
			'position'		=> '100',
			'before'		=> '<h2>Template</h2>',
			'option_name' 	=> 'template',
			'name' 			=> 'w4pl[template]',
			'label' 		=> '',
			'type' 			=> 'textarea',
			'input_class' 	=> 'widefat',
			'default' 		=> apply_filters('w4pl/template_default', ''),
			'desc' 			=> 'top level shortcodes are [nav], [groups][/groups], [posts][/posts]. while using group by option, posts should be nested in groups tag. example:'
			. "<pre style='width:auto'>
[groups]
  [group_title]
  [posts]
    [post_title]
  [/posts]
[/groups]
[nav]
</pre>"
			. '<br />without group, a simple template should be like -'
			. "<pre style='width:auto'>
[posts]
  [post_title]
[/posts]
[nav]
</pre>"
		);


		$shortcodes = self::get_shortcodes();
		$shortcode_groups = array();
		foreach( $shortcodes as $shortcode => $attr ){
			$group = $attr['group'];
			if( !isset($shortcode_groups[$group]) || !is_array($shortcode_groups[$group]) )
				$shortcode_groups[$group] = array();

			#if( ! in_array($attr['group'], $shortcode_groups) )
			$shortcode_groups[$group][] = $shortcode;
		}

		#print_r($shortcode_groups);


		$input_before = '<div id="w4pl_template_buttons">';
		foreach( $shortcode_groups as $shortcode_group => $scodes ){
			$input_before .= sprintf(' <div class="w4pl_button_group"><span class="w4pl_button_group_title">%1$s</span>', $shortcode_group );
			foreach( $scodes as $shortcode ){
				$attr = $shortcodes[$shortcode];
				if( isset($attr['code']) )
					$code = $attr['code'];
				else
					$code = '['. $shortcode . ']';
				$input_before .= sprintf(' <a href="#%1$s" data-code="%2$s">%1$s</a>', $shortcode, esc_attr($code) );
			}
			$input_before .= '</div>';
		}
		$input_before .= '</div>';


		$fields['template']['input_before'] = $input_before;
		$fields['template']['input_wrap_before'] = self::get_shortcode_hint_html();


		/* Migration procedure */
		if( isset($options['template_loop']) && !empty($options['template_loop']) )
		{
			$options['template'] = str_replace( '[loop]', '[posts]'. $options['template_loop'] .'[/posts]', $options['template'] );
			unset($options['template_loop']);
		}

		if( isset($options['template']) && ! preg_match('/\[posts\](.*?)\[\/posts\]/sm', $options['template']) && preg_match('/\[loop\]/sm', $options['template'], $match ) )
		{
			$options['template'] = str_replace( $match[0], '[posts]'. $options['template_loop'] .'[/posts]', $options['template'] );
		}



		#echo '<pre>'; print_r($options); echo '</pre>';


		$form_args = array(
			'no_form' 		=> true,
			'button_after' 	=> false
		);


		// let helper class extend/modify this class
		$fields = apply_filters( 'w4pl/admin_list_fields', $fields, $options );


		// order by position
		uasort( $fields, array( get_class(), 'order_by_position') );


		echo w4pl_form_fields( $fields, $options, $form_args );
	}


	public static function list_options_template_ajax()
	{
		$selection = isset($_POST['selection']) ? stripslashes($_POST['selection']) : '';
		if( preg_match( "/\[postlist options=[\"\'](.*?)=[\"\']/sm", $selection, $selection_match) )
		{
			$options = maybe_unserialize( base64_decode( str_replace( ' ', '', $selection_match['1']) ) );
			if( is_object($options) ){
				$options = get_object_vars($options);
			}
			if( !empty($options) ){
				do_action( 'w4pl/list_options_template', $options );
			}
		}

		die('');
	}


	public static function post_type_fields_template_ajax()
	{
		$post_ID = isset($_POST['post_id']) ? $_POST['post_id'] : 0;
		$options = get_post_meta( $post_ID, '_w4pl', true );
		if( ! $options || !is_array($options) )
			$options = array();

		if( isset($_POST['post_type']) )
			$options['post_type'] = $_POST['post_type'];

		$fields = array();
		self::post_type_fields( $fields, $options );

		if( empty($fields) ){
			die('');
		}

		echo w4pl_form_fields( 
			$fields, 
			$options, 
			array('no_form' => true, 'button_after' => false) 
		);
		die('');
	}

	public static function post_type_fields( &$fields, $options )
	{
		$post_type = $options['post_type'];

		// mime type field
		if( $mime_type_options = self::post_mime_type_options($post_type) )
		{
			$fields['post_mime_type'] = array(
				'position' 		=> '20',
				'option_name' 	=> 'post_mime_type',
				'name' 			=> 'w4pl[post_mime_type]',
				'label' 		=> 'Post Mime Type',
				'type' 			=> 'checkbox',
				'option' 		=> $mime_type_options,
				'desc' 			=> 'if displaying attachment, choose mime type to restrcit result to specific file types.'
			);
		}

		$fields['groupby'] = array(
			'before'		=> '<h2>Group</h2>',
			'position' 		=> '25',
			'option_name' 	=> 'groupby',
			'name' 			=> 'w4pl[groupby]',
			'label' 		=> 'Group By',
			'type' 			=> 'select',
			'option' 		=> self::post_groupby_options($post_type)
		);
		$fields['group_order'] = array(
			'position' 		=> '26',
			'option_name' 	=> 'group_order',
			'name' 			=> 'w4pl[group_order]',
			'label' 		=> 'Group Order',
			'type' 			=> 'radio',
			'option' 		=> array('' => 'None', 'ASC' => 'ASC', 'DESC' => 'DESC')
		);

		$fields = apply_filters( 'w4pl/admin_list_post_type_fields', $fields, $options );

		return $fields;
	}


	/*
	 * Encoded Shortcode data
	**/

	public static function w4pl_get_shortcode_ajax()
	{
		$options = isset($_POST) ? stripslashes_deep($_POST) : array();
		if( isset($options['w4pl']) )
			$options = $options['w4pl'];


		/*
		$r = '[postlist';
		foreach( $options as $okey => $oval ){
			if( empty($oval) ){
				continue;
			}
			elseif( is_array($oval) ){
				foreach( $oval as $ckey => $cval ){
					if( empty($cval) ){
						continue;
					}
					elseif( ! is_array($cval) ){
						$r .= ' '. $okey .'.'. $ckey .'="'.htmlentities($cval).'"';
					}
				}
			}
			else{
				$r .= ' '. $okey .'="'.htmlentities($oval).'"';
			}
		}
		$r .= ']';
		
		echo maybe_serialize($options);

		echo $r;
		die();
		*/

		$encode = chunk_split( base64_encode( maybe_serialize($options) ), 100, ' ');

		#$strLen = strlen($encode);
		#$re = '';
		#for( $i = 0; $i < $strLen; $i += 100 ){
		#	$re .= substr($encode, $i, 100) . ' ';
		#}

		printf( '[postlist data="%s"]', trim($encode) );

		die();
	}


	public static function register_scripts()
	{
		wp_register_style(  'w4pl_form', 				W4PL_URL . 'assets/form/form.css' );
		wp_register_script( 'w4pl_form', 				W4PL_URL . 'assets/form/form.js', array('jquery', 'jquery-ui-core') );

		wp_register_style(  'w4pl_jquery_ui_custom', 	W4PL_URL . 'assets/jquery/jquery-ui-1.9.2.custom.css' );
		wp_register_script( 'w4pl_colorpicker', 		W4PL_URL . 'assets/colorpicker/jscolor.js' );
		wp_register_script( 'w4pl_tinymce_popup', 		W4PL_URL . 'assets/tinymce/tinymce_popup.js' );
	}


	public static function list_options_print_scripts()
	{
		wp_print_styles(  'w4pl_form' );
		wp_print_scripts( 'w4pl_form' );

		?>
		<style>
/*W4 Post List Admin*/
#w4pl_template_before, #w4pl_template_after, #w4pl_template{width:99%;height:50px;}
#w4pl_template{height:250px;}
#minor-publishing-actions, #misc-publishing-actions{display:none;}
#shortcode_hint_toggle{position:relative;margin:10px 0;float:left;clear:left;}
.wffw{margin:15px 0;padding-top:15px;padding-bottom:15px;border-width: 0 0 0 5px;box-shadow:0 0 1px #AAAAAA;box-sizing: border-box;-moz-box-sizing: border-box;-webkit-box-sizing: border-box;overflow:hidden;}
.wfflw, .wffdw {width:200px;float:left;clear:left;}
.wffew {margin-left:220px;}
.wffl{font-size:13px;}
#w4pl_post_type_options{position:relative;}
#w4pl_post_type_options:after{ background:url(images/loading.gif) no-repeat; width:30px; height:30px; display:block;}
#w4pl_template_buttons a{ padding:4px 8px; display:inline-block; border:1px solid #DDD; background-color:#EEE; line-height:12px; font-size:12px; margin:0 2px 2px 0; text-decoration:none; border-radius: 3px; -moz-border-radius:3px; -webkit-border-radius:3px;}
.w4pl_button_group{ padding:0 0 10px;}
.w4pl_button_group_title{ display:block;}
<?php do_action( 'w4pl/admin_print_css' ); ?>
        </style>

		<script type="text/javascript">
(function($){
	$(document).ready(function(){
		$('#shortcode_hint_toggle').click(function(){
			$('#shortcode_hint').toggle();
			return false;
		});

		/* onchange post type, refresh post type fields template */
		$('#w4pl_post_type').live('change', function(){
			$('.wffwi_w4pl_post_type .spinner').css('display', 'inline-block');
			$('#publish').hide();

			$.post( ajaxurl, 'action=w4pl_post_type_fields_template&post_id='+ $('#post_ID').val() +'&post_type='+ $(this).val(), function(r){
				$('#w4pl_post_type_options').html(r);
				$('.wffwi_w4pl_post_type .spinner').css('display', 'none');
				$('#publish').show();
				
				return false;
			})
		});

		$('#w4pl_orderby').change(function(){
			if( 'meta_value' == $(this).val() || 'meta_value_num' == $(this).val() ){
				$('#orderby_meta_key_wrap').show();
			}
			else{
				$('#orderby_meta_key_wrap').hide();
			}
		});
		$('#w4pl_orderby').trigger('change');



		$('#w4pl_template_buttons a').click(function(e){
			insertAtCaret( 'w4pl_template', $(this).data('code') );
			return false;
		});
	});

	<?php do_action( 'w4pl/admin_print_js' ); ?>

})(jQuery);

/*
 * Similar feature as tinymce quicktag button
 * This function helps to place shortcode right at the cursor position
*/

function insertAtCaret(areaId,text) {
    var txtarea = document.getElementById(areaId);
    var scrollPos = txtarea.scrollTop;
    var strPos = 0;
    var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ? 
        "ff" : (document.selection ? "ie" : false ) );
    if (br == "ie") { 
        txtarea.focus();
        var range = document.selection.createRange();
        range.moveStart ('character', -txtarea.value.length);
        strPos = range.text.length;
    }
    else if (br == "ff") strPos = txtarea.selectionStart;

    var front = (txtarea.value).substring(0,strPos);  
    var back = (txtarea.value).substring(strPos,txtarea.value.length); 
    txtarea.value=front+text+back;
    strPos = strPos + text.length;
    if (br == "ie") { 
        txtarea.focus();
        var range = document.selection.createRange();
        range.moveStart ('character', -txtarea.value.length);
        range.moveStart ('character', strPos);
        range.moveEnd ('character', 0);
        range.select();
    }
    else if (br == "ff") {
        txtarea.selectionStart = strPos;
        txtarea.selectionEnd = strPos;
        txtarea.focus();
    }
    txtarea.scrollTop = scrollPos;
}
		</script>
        <?php
	}


	public static function post_type_options()
	{
		global $wp_post_types;

		$return = array();
		foreach( $wp_post_types as $post_type => $post_type_object ){
			if(	!$post_type_object->public )
				continue;

			$return[$post_type] = $post_type_object->labels->name;
		}
	
		return $return;
	}

	public static function post_mime_type_options($post_type = 'post')
	{
		global $wpdb;
		$mime_types = $wpdb->get_col( $wpdb->prepare( 
			"SELECT DISTINCT post_mime_type FROM $wpdb->posts WHERE post_status != 'trash' AND post_type=%s AND post_mime_type <> ''", $post_type
		));

		if( !empty($mime_types) )
		{
			$return = array('' => 'Any');
			foreach( $mime_types as $mime_type ){
				if( !empty($mime_type) )
					$return[$mime_type] = $mime_type;
			}
			return $return;
		}
		return array();
	}

	public static function post_status_options()
	{
		global $wp_post_statuses;


		$return = array();
		foreach( $wp_post_types as $post_type => $post_type_object ){
			if(	!$post_type_object->public )
				continue;

			$return[$post_type] = $post_type_object->labels->name;
		}

		return $return;
	}


	public static function post_groupby_options( $post_type )
	{
		$return = array(
			'' 			=> 'None',
			'year' 		=> 'Year',
			'month' 	=> 'Month',
			'yearmonth' => 'Year Months',
			'author' 	=> 'Author',
			'parent' 	=> 'Parent'
		);
		foreach( get_object_taxonomies($post_type, 'all') as $taxonomy => $taxonomy_object ){
			if( $taxonomy == 'post_format' || !$taxonomy_object->public )
				continue;
			$return['tax_'. $taxonomy] = $taxonomy_object->labels->name;
		}

		return $return;
	}

	public static function post_orderby_options( $post_type )
	{
		$return = array(
			'ID'				=> __( 'ID', 					W4PL_TXT_DOMAIN),
			'title'				=> __( 'Title', 				W4PL_TXT_DOMAIN),
			'name'				=> __( 'Name', 					W4PL_TXT_DOMAIN),
			'date'				=> __( 'Publish Date', 			W4PL_TXT_DOMAIN),
			'modified'			=> __( 'Modified Date', 		W4PL_TXT_DOMAIN),
			'menu_order'		=> __( 'Menu Order', 			W4PL_TXT_DOMAIN),
			'meta_value'		=> __( 'Meta value', 			W4PL_TXT_DOMAIN),
			'meta_value_num'	=> __( 'Meta numeric value', 	W4PL_TXT_DOMAIN),
			'rand'				=> __( 'Random', 				W4PL_TXT_DOMAIN),
		);

		if( post_type_supports($post_type, 'comments') )
			$return['comment_count'] = __( 'Comment Count',W4PL_TXT_DOMAIN);

		return $return;
	}

	public static function get_shortcode_hint_html()
	{
		$shortcodes = W4PL_Core::get_shortcodes();
		$return = '<a id="shortcode_hint_toggle" class="button">shortcodes details</a>';
		$return .= '<table id="shortcode_hint" class="widefat" style="display:none;">';
		$return .= '<thead><tr><th style="text-align: right;">Tag</th><th>Details</th></tr></thead><tbody>';
		foreach( $shortcodes as $shortcode => $attr ){ 
			$rc = isset($rc) && $rc == '' ? $rc = 'alt' : '';
			$return .= '<tr class="'. $rc .'">';
			$return .= '<th valign="top" style="text-align: right; font-size:12px; line-height: 1.3em;"><code>['. $shortcode. ']</code></th>';
			$return .= '<td style="font-size:12px; line-height: 1.3em;">'. $attr['desc'] . '</td>';
			$return .= '</tr>';
		}
		$return .= '</tbody></table>';
		return $return;
	}

	/*
	 * Order array elements by position
	 * @param (array)
	 * @param (array)
	 * @return (bool)
	*/

	public static function order_by_position( $a, $b )
	{
		if( !isset($a['position']) || !isset($b['position']) )
			return -1;

		if( $a['position'] == $b['position'] )
	        return 0;

	    return ($a['position'] < $b['position']) ? -1 : 1;
	}
}

	new W4PL_Core;
?>