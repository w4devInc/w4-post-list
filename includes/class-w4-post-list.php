<?php
/**
 * Main plugin class
 *
 * @class W4_Post_List
 * @package W4_Post_List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class
 *
 * @class W4_Post_List
 */
final class W4_Post_List {

	/**
	 * Plugin name
	 *
	 * @var string
	 */
	public $name = 'W4 Post List';

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	public $version = '2.5.0';

	/**
	 * This will hold current class instance
	 *
	 * @var mixed
	 */
	protected static $instance = null;

	/**
	 * Get singleton instance
	 *
	 * @return object W4_Post_List instance
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->define_constants();
		$this->includes();
		$this->initialize();
		$this->init_hooks();

		do_action( 'w4pl/loaded' );
	}

	/**
	 * Define constants
	 */
	private function define_constants() {
		define( 'W4PL_NAME', $this->name );
		define( 'W4PL_VERSION', $this->version );
		define( 'W4PL_DIR', plugin_dir_path( W4PL_PLUGIN_FILE ) );
		define( 'W4PL_URL', plugin_dir_url( W4PL_PLUGIN_FILE ) );
		define( 'W4PL_BASENAME', plugin_basename( W4PL_PLUGIN_FILE ) );
		define( 'W4PL_SLUG', 'w4pl' );
	}

	/**
	 * Include files
	 */
	private function includes() {
		/* core */
		include W4PL_DIR . '/includes/functions.php';
		include W4PL_DIR . '/includes/functions-form.php';
		include W4PL_DIR . '/includes/class-utils.php';
		include W4PL_DIR . '/includes/class-config.php';
		include W4PL_DIR . '/includes/class-post-types.php';
		include W4PL_DIR . '/includes/class-list-templates.php';
		include W4PL_DIR . '/includes/class-list-helper.php';
		include W4PL_DIR . '/includes/class-list-content.php';

		// interface classes.
		foreach ( glob( W4PL_DIR . 'includes/interfaces/*.php' ) as $file ) {
			include_once $file;
		}
		// abstract classes.
		foreach ( glob( W4PL_DIR . 'includes/abstracts/*.php' ) as $file ) {
			include_once $file;
		}
		// facade classes.
		foreach ( glob( W4PL_DIR . 'includes/list-types/*.php' ) as $file ) {
			include_once $file;
		}
		// factory classes.
		foreach ( glob( W4PL_DIR . 'includes/factories/*.php' ) as $file ) {
			include_once $file;
		}
		// query classes.
		foreach ( glob( W4PL_DIR . 'includes/queries/*.php' ) as $file ) {
			include_once $file;
		}
		// helper classes.
		foreach ( glob( W4PL_DIR . 'includes/helpers/*.php' ) as $file ) {
			include_once $file;
		}
		// template tags.
		foreach ( glob( W4PL_DIR . 'includes/template-tags/*.php' ) as $file ) {
			include_once $file;
		}
		// shortcode classes.
		foreach ( glob( W4PL_DIR . 'includes/shortcodes/*.php' ) as $file ) {
			include_once $file;
		}

		if ( is_admin() ) {
			include W4PL_DIR . '/admin/class-admin-main.php';
			include W4PL_DIR . '/admin/class-admin-lists-table-columns.php';
			include W4PL_DIR . '/admin/class-admin-lists-metaboxes.php';
			include W4PL_DIR . '/admin/class-admin-list-editor.php';

			/* Admin pages */
			foreach ( glob( W4PL_DIR . 'admin/pages/*.php' ) as $file ) {
				include_once $file;
			}
		}
	}

	/**
	 * Initialize the plugin.
	 */
	private function initialize() {
		new W4PL_Post_Types();
		new W4PL_List_Templates();
		new W4PL_List_Content();

		new W4PL_List_Shortcode();
		new W4PL_Date_Shortcode();

		new W4PL_List_Helper();
		new W4PL_Helper_Posts();
		new W4PL_Helper_Terms();
		new W4PL_Helper_Users();
		new W4PL_Helper_No_Items();
		new W4PL_Helper_Date_Query();
		new W4PL_Helper_Meta_Query();
		new W4PL_Helper_Tax_Query();
		new W4PL_Helper_Presets();
		new W4PL_Helper_Style();

		new W4PL_Post_Template_Tags();
		new W4PL_Term_Template_Tags();
		new W4PL_User_Template_Tags();

		if ( is_admin() ) {
			new W4PL_Admin_Main();
			new W4PL_Admin_Lists_Table_Columns();
			new W4PL_Admin_Lists_Metaboxes();

			new W4PL_Admin_Page_Docs();
		}
	}

	/**
	 * Init hooks
	 */
	public function init_hooks() {
		add_action( 'init', array( $this, 'load_plugin_translations' ) );
		add_action( 'widgets_init', array( $this, 'widget_init' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ), 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ), 2 );
	}

	/**
	 * Load plugin translation file
	 */
	public function load_plugin_translations() {
		load_plugin_textdomain( 'w4-post-list', false, basename( dirname( W4PL_PLUGIN_FILE ) ) . '/languages' );
	}

	/**
	 * Register widget
	 */
	public function widget_init() {
		include_once W4PL_DIR . '/includes/class-widget-postlist.php';
		register_widget( 'W4PL_Widget_Postlist' );
	}

	/**
	 * Register stylesheets / javascripts
	 */
	public function register_scripts() {
		$min = '.min';
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			$min = '';
		}

		wp_register_style(
			'w4pl_form',
			W4PL_URL . 'assets/css/form' . $min . '.css',
			array(),
			W4PL_VERSION
		);
		wp_register_style(
			'w4pl_list_editor',
			W4PL_URL . 'assets/css/list-editor' . $min . '.css',
			array(),
			W4PL_VERSION
		);
		wp_register_style(
			'w4pl-admin-documentation',
			W4PL_URL . 'assets/css/admin-documentation' . $min . '.css',
			array(),
			W4PL_VERSION
		);

		wp_register_script(
			'w4pl_form',
			W4PL_URL . 'assets/js/form' . $min . '.js',
			array( 'jquery', 'jquery-ui-sortable' ),
			W4PL_VERSION,
			true
		);
		wp_register_script(
			'w4pl_list_editor',
			W4PL_URL . 'assets/js/list-editor' . $min . '.js',
			array( 'jquery', 'jquery-ui-sortable' ),
			W4PL_VERSION,
			true
		);
	}
}
