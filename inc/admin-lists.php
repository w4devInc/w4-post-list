<?php
class W4PL_Lists_Admin extends W4PL_Core
{
	function __construct()
	{
		add_action( 'add_meta_boxes_'. W4PL_SLUG, 					array($this, 'add_meta_boxes') );
		add_action( 'save_post_'. W4PL_SLUG,  						array($this, 'save_post'), 10, 3 );

		add_filter( 'w4pl/template_default',  						array($this, 'template_default') );


		// set update message for our post type, you dont like to use - "post update" !
		add_filter( 'post_updated_messages', 						array($this, 'post_updated_messages'));

		// additional column
		add_filter( 'manage_'. W4PL_SLUG .'_posts_columns', 		array($this, 'manage_posts_columns') );
		add_action( 'manage_'. W4PL_SLUG .'_posts_custom_column', 	array($this, 'manage_posts_custom_column'), 10, 2 );

		// add lists link to plugin links, so one can navigate quickly
		add_filter( 'plugin_action_links_' . W4PL_BASENAME, 		array($this, 'plugin_action_links') );
	}

	// Meta box
	public function add_meta_boxes( $post )
	{
		// add configuration box right after post title, out of metabox
		add_action( 'edit_form_after_title', array($this, 'list_options_meta_box') );

		// add plugin news metabox one right side
		add_meta_box( "w4pl_news_meta_box", "Plugin Updates", array($this, 'news_meta_box'), W4PL_SLUG, "side", 'core');

		// enqueue script files, print css on header and print js on footer
		add_action('admin_head', array( $this, 'admin_head') );
	}

	public function admin_head()
	{
		do_action( 'w4pl/list_options_print_scripts' );
	}


	public function list_options_meta_box( $post )
	{
		$post_ID = $post->ID;
		$post_data = get_post_meta( $post_ID, '_w4pl', true );
		if( ! $post_data || !is_array($post_data) )
			$post_data = array();

		do_action( 'w4pl/list_options_template', $post_data );
	}



	public function save_post( $post_ID, $post, $update )
	{
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
			return $post_ID ;

		if( !isset($_POST['w4pl']) )
			return;

		$options = stripslashes_deep($_POST['w4pl']);
		#die( print_r($_POST) );

		if( isset($options) )
		{
			update_post_meta( $post_ID, '_w4pl', $options );
		}
	}

	// default templates
	public function template_default($r){
		return '<ul>[posts]'. "\n" . '<li>'. "\n" . '[title]'. "\n" . '[post_thumbnail]'. "\n" . '[excerpt]' . "\n" . '[more]' . "\n".'</li>'. "\n" . '[/posts]</ul>';
	}




	public function post_updated_messages( $messages )
	{
		global $post_ID, $post;

		$input_attr = 'type="text" size="9" onfocus="this.select();" onclick="this.select();" readonly="readonly"';

		$messages[W4PL_SLUG] = array(
			 1 => sprintf( __('List updated. Use Shortcode <input value="[postlist %1$d]" %2$s />'), $post_ID, $input_attr ),
			 2 => '',
			 3 => '',
			 4 => __('List updated.'),
			 5 => '',
			 6 => sprintf( __('List published. Use Shortcode <input value="[postlist %1$d]" %2$s />'), $post_ID, $input_attr ),
			 7 => __('List saved.'),
			 8 => sprintf( __('List submitted. Use Shortcode <input value="[postlist %1$d]" %2$s />'), $post_ID, $input_attr ),
			 9 => sprintf( __('List scheduled. Use Shortcode <input value="[postlist %1$d]" %2$s />'), $post_ID, $input_attr ),
			10 => ''
		);
		return $messages;
	}

	public function manage_posts_columns( $columns )
	{
		$date = false;
		if( isset($columns['date']) ){
			$date = $columns['date'];
			unset($columns['date']);
		}
		$columns['shortcode'] = __('Shortcode');

		if( $date ){
			$columns['date'] = $date;
		}

		return $columns;
	}

	public function manage_posts_custom_column( $column_name, $post_ID )
	{
		if( 'shortcode' == $column_name ){
			printf( '<input value="[postlist %d]" type="text" size="9" onfocus="this.select();" onclick="this.select();" readonly="readonly" />', $post_ID );
		}
	}


	public static function plugin_action_links( $links )
	{
		$readme_link['doc'] = '<a href="'. 'edit.php?post_type='. W4PL_SLUG . '-docs">' . __( 'Docs', W4PL_TXT_DOMAIN ). '</a>';
		return array_merge( $links, $readme_link );
	}



	public static function news_meta_box()
	{
		self::plugin_news();
	}

	/* Retrive latest updates about Post List plugin */
	public static function plugin_news( $echo = true, $refresh = false )
	{
		$transient = 'w4pl_plugin_news';
		$transient_old = $transient . '_old';
		$expiration = 7200;
	
		$output = get_transient( $transient );

		if( $refresh || !$output || empty( $output ))
		{
			$request = wp_remote_request('http://w4dev.com/wp-admin/admin-ajax.php?action=w4_ajax&action_call=plugin_news');
			$content = wp_remote_retrieve_body($request);

			if( is_wp_error( $content ) ){
				$output = get_option( $transient_old );
			}
			else
			{
				$output = $content;
				// Save last new forever if a newer is not available..
				update_option( $transient_old, $output );
			}

			set_transient( $transient, $output, $expiration );
		}

		$output = preg_replace( '/[\n]/', '<br />', $output );

		if( !$echo )
			return $output;
		else
			echo $output;
	}
}

	new W4PL_Lists_Admin;
?>