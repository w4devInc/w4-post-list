<?php

/* Plugin main class */
final class W4_Post_List
{
	protected $plugin_name;
	protected $plugin_slug;
	protected $plugin_version;
	protected $plugin_dir;
	protected $plugin_url;
	protected $plugin_basename;
	protected $list_post_type;

	protected static $_instance = null;

	public static function instance()
	{
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/* Private constructor, can not be called without instance */
	private function __construct()
	{
		$this->plugin_name 		= 'W4 Post List';
		$this->plugin_slug 		= 'w4pl';
		$this->plugin_version 	= '2.3.2';
		$this->plugin_dir 		= plugin_dir_path(W4PL_PLUGIN_FILE);
		$this->plugin_url 		= plugin_dir_url(W4PL_PLUGIN_FILE);
		$this->plugin_basename 	= plugin_basename( W4PL_PLUGIN_FILE );
		$this->list_post_type 	= 'w4pl';

		add_action('plugins_loaded', array($this, 'load_plugin'), 20);
	}

	/* plugin information getters */
	public function plugin_name()
	{
		return $this->plugin_name;
	}
	public function plugin_slug()
	{
		return $this->plugin_slug;
	}
	public function plugin_version()
	{
		return $this->plugin_version;
	}
	public function plugin_dir()
	{
		return $this->plugin_dir;
	}
	public function plugin_url()
	{
		return $this->plugin_url;
	}
	public function plugin_basename()
	{
		return $this->plugin_basename;
	}
	public function list_post_type()
	{
		return $this->list_post_type;
	}

	/*
	 * Load plugin files, register callbacks, load translations
	*/
	public function load_plugin()
	{
		$this->includes();
		$this->init_hooks();
		$this->load_plugin_textdomain();

		do_action('w4pl/loaded');
	}

	private function load_plugin_textdomain()
	{
		load_plugin_textdomain('w4pl', false, basename(dirname(W4PL_PLUGIN_FILE)) . '/languages');
	}

	private function includes()
	{
		/* core */
		include( $this->plugin_dir() .'/includes/form.php');
		include( $this->plugin_dir() .'/includes/plugin.php');
		include( $this->plugin_dir() .'/includes/config.php');

		// interface classes
		foreach( glob( $this->plugin_dir() . 'includes/interfaces/*.php') as $file ) {
			include_once( $file );
		}
		// abstract classes
		foreach( glob( $this->plugin_dir() . 'includes/abstracts/*.php') as $file ) {
			include_once( $file );
		}
		// facade classes
		foreach( glob( $this->plugin_dir() . 'includes/list-types/*.php') as $file ) {
			include_once( $file );
		}
		// factory classes
		foreach( glob( $this->plugin_dir() . 'includes/factories/*.php') as $file ) {
			include_once( $file );
		}
		// query classes
		foreach( glob( $this->plugin_dir() . 'includes/queries/*.php') as $file ) {
			include_once( $file );
		}
		// query classes
		foreach( glob( $this->plugin_dir() . 'includes/helpers/*.php') as $file ) {
			include_once( $file );
		}

		include( $this->plugin_dir() .'/includes/list-helper.php');

		if (is_admin()) {
			/* admin features */
			include( $this->plugin_dir() .'/admin/admin.php');
			include( $this->plugin_dir() .'/admin/list-editor.php');
			/* Admin pages */
			foreach( glob( $this->plugin_dir() . 'admin/pages/*.php') as $file ) {
				include_once( $file );
			}
		} else {
			/* public features */
			include( $this->plugin_dir() .'/public/front.php');
			/* shortcodes */
			foreach( glob( $this->plugin_dir() . 'public/shortcodes/*.php') as $file ) {
				include_once( $file );
			}
		}
	}
	public function init_hooks()
	{
		add_action( 'widgets_init'							, array($this, 'widget_init'));
		add_action( 'init'									, array($this, 'init'), 0 );
		add_action( 'wp_enqueue_scripts'					, array($this, 'register_scripts'), 2 );
		add_action( 'admin_enqueue_scripts'					, array($this, 'register_scripts'), 2 );
	}
	public function widget_init()
	{
		include_once( $this->plugin_dir() .'/includes/widget.php');
		register_widget('W4PL_Widget');
	}
	public function init()
	{
		register_post_type( $this->list_post_type(), array(
			'labels' => array(
				'name' 					=> _x('Lists', 'post type general name'),
				'singular_name' 		=> _x('List', 'post type singular name'),
				'menu_name'				=> __('W4 Post List', 'w4pl'),
				'all_items'				=> __('All Lists', 'w4pl'),
				'add_new' 				=> _x('Add New', 'note item'),
				'add_new_item' 			=> __('New List', 'w4pl'),
				'edit_item' 			=> __('Edit List', 'w4pl'),
				'new_item' 				=> __('New List', 'w4pl'),
				'view_item' 			=> __('View List', 'w4pl'),
				'search_items' 			=> __('Search List', 'w4pl'),
				'not_found' 			=> __('No list found', 'w4pl'),
				'not_found_in_trash' 	=> __('No lists found in Trash', 'w4pl'),
				'parent_item_colon' 	=> ''
			),
			'show_ui'  				=> true,
			'rewrite'  				=> array('slug' => 'list'),
			'public'  				=> true,
			'has_archive'			=> false,
			'delete_with_user'		=> false,
			'show_in_admin_bar'		=> false,
			'supports' 				=> array('title'),
			'menu_icon'				=> 'dashicons-editor-ul'
		));
	}

	public function register_scripts()
	{
		wp_register_style(  'w4pl_form', 				$this->plugin_url() . 'admin/assets/form.css' );
		wp_register_script( 'w4pl_form', 				$this->plugin_url() . 'admin/assets/form.js', array('jquery', 'jquery-ui-sortable') );
		wp_register_style(  'w4pl_admin', 				$this->plugin_url() . 'admin/assets/admin.css' );
	}
}

