<?php

/* Plugin main class */
final class W4_Post_List
{
	protected $plugin_name;
	protected $plugin_slug;
	protected $plugin_version = '2.3.0';
	protected $plugin_dir;
	protected $plugin_url;
	protected $plugin_basename;
	protected $list_post_type;

	protected static $_instance = null;

	public static function instance()
	{
		if ( is_null( self::$_instance  )  ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/* Private constructor, can not be called without instance */
	private function __construct()
	{
		$this->plugin_name 		= 'W4 Post List';
		$this->plugin_slug 		= 'w4pl';
		$this->plugin_dir 		= plugin_dir_path( W4PL_PLUGIN_FILE );
		$this->plugin_url 		= plugin_dir_url( W4PL_PLUGIN_FILE );
		$this->plugin_basename 	= plugin_basename( W4PL_PLUGIN_FILE  );
		$this->list_post_type 	= 'w4pl';

		add_action( 'plugins_loaded', array( $this, 'load_plugin' ), 20 );
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
		$this->initialize();
		$this->init_hooks();
		$this->load_plugin_textdomain();

		do_action( 'w4pl/loaded' );
	}

	private function load_plugin_textdomain()
	{
		/*
		if ( function_exists( 'determine_locale' ) ) {
			$locale = determine_locale();
		} else {
			$locale = is_admin() ? get_user_locale() : get_locale();
		}

		$locale = apply_filters( 'plugin_locale', $locale, 'w4pl' );

		unload_textdomain( 'w4pl' );
		load_textdomain( 'w4pl', WP_LANG_DIR . '/w4-post-list/w4pl-' . $locale . '.mo' );
		*/

		load_plugin_textdomain( 'w4pl', false, basename( dirname( W4PL_PLUGIN_FILE ) ) . '/languages' );
	}

	private function includes()
	{
		/* core */
		include( $this->plugin_dir() . '/includes/functions-form.php' );
		include( $this->plugin_dir() . '/includes/class-utils.php' );
		include( $this->plugin_dir() . '/includes/class-config.php' );
		include( $this->plugin_dir() . '/includes/class-post-types.php' );
		include( $this->plugin_dir() . '/includes/class-list-templates.php' );

		// interface classes
		foreach( glob( $this->plugin_dir() . 'includes/interfaces/*.php' ) as $file  ) {
			include_once( $file  );
		}
		// abstract classes
		foreach( glob( $this->plugin_dir() . 'includes/abstracts/*.php' ) as $file  ) {
			include_once( $file  );
		}
		// facade classes
		foreach( glob( $this->plugin_dir() . 'includes/list-types/*.php' ) as $file  ) {
			include_once( $file  );
		}
		// factory classes
		foreach( glob( $this->plugin_dir() . 'includes/factories/*.php' ) as $file  ) {
			include_once( $file  );
		}
		// query classes
		foreach( glob( $this->plugin_dir() . 'includes/queries/*.php' ) as $file  ) {
			include_once( $file  );
		}
		// query classes
		foreach( glob( $this->plugin_dir() . 'includes/helpers/*.php' ) as $file  ) {
			include_once( $file  );
		}

		include( $this->plugin_dir() . '/includes/list-helper.php' );
		#include( $this->plugin_dir() . '/includes/class-list-editor.php' );

		if ( is_admin() ) {
			include( $this->plugin_dir() . '/admin/class-admin-main.php' );
			include( $this->plugin_dir() . '/admin/class-admin-lists-table-columns.php' );
			include( $this->plugin_dir() . '/admin/class-admin-lists-metaboxes.php' );

			/* Admin pages */
			foreach( glob( $this->plugin_dir() . 'admin/pages/*.php' ) as $file  ) {
				include_once( $file  );
			}
		} else {
			/* public features */
			include( $this->plugin_dir() . '/public/class-frontend.php' );
			include( $this->plugin_dir() . '/public/class-shortcode-postlist.php' );
		}
	}

	private function initialize()
	{
		new W4PL_Post_Types();
		new W4PL_List_Templates();

		if ( is_admin() ) {
			new W4PL_Admin_Main();
			new W4PL_Admin_Lists_Table_Columns();
			new W4PL_Admin_Lists_Metaboxes();

			new W4PL_Admin_Page_Docs();
		} else {
			new W4PL_Frontend();
			new W4PL_Shortcode_Postlist();
		}
	}

	public function init_hooks()
	{
		add_action( 'widgets_init'							, array( $this, 'widget_init' ) );
		add_action( 'wp_enqueue_scripts'					, array( $this, 'register_scripts' ), 2  );
		add_action( 'admin_enqueue_scripts'					, array( $this, 'register_scripts' ), 2  );
	}

	public function widget_init()
	{
		include_once( $this->plugin_dir() . '/includes/class-widget-postlist.php' );
		register_widget( 'W4PL_Widget_Postlist' );
	}

	public function register_scripts()
	{
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['w4pl_debug'] ) ? '' : '.min';

		wp_register_style(  'w4pl_form', 				$this->plugin_url() . 'assets/css/form'. $min .'.css'  );
		wp_register_style(  'w4pl_admin', 				$this->plugin_url() . 'assets/css/admin'. $min .'.css'  );
		wp_register_style(  'w4pl-admin-documentation', $this->plugin_url() . 'assets/css/admin-documentation'. $min .'.css'  );

		wp_register_script( 'w4pl_form', 				$this->plugin_url() . 'assets/js/form'. $min .'.js', array( 'jquery', 'jquery-ui-sortable' )  );
	}
}
