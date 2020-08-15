<?php
/**
 * Admin list table custom columns
 *
 * @class W4PL_Admin_Lists_Table
 * @package W4_Post_List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post list metaboxes
 */
class W4PL_Admin_Lists_Metaboxes {

	/**
	 * List editor class holder
	 *
	 * @var object
	 */
	public $list_editor = null;

	/**
	 * Constructor
	 */
	public function __construct() {

		// load list options template from posted data.
		add_action( 'wp_ajax_w4pl_list_edit_form_html', array( $this, 'list_edit_form_html_ajax' ) );
		add_action( 'add_meta_boxes_' . W4PL_Config::LIST_POST_TYPE, array( $this, 'add_meta_boxes' ), 20 );
		add_action( 'save_post_' . W4PL_Config::LIST_POST_TYPE, array( $this, 'save_post' ), 20, 1 );
	}

	/**
	 * Get list edit form html template through ajax
	 */
	public function list_edit_form_html_ajax() {
		if ( isset( $_POST['w4pl'] ) ) {
			$options = stripslashes_deep( $_POST['w4pl'] );
			if ( is_object( $options ) ) {
				$options = get_object_vars( $options );
			}
			if ( ! empty( $options ) ) {
				$this->list_editor = new W4PL_List_Editor( $options );
				$this->list_editor->render_form();
			}
		}

		exit;
	}

	/**
	 * Add metabox
	 *
	 * @param mixed $post WP_Post object.
	 */
	public function add_meta_boxes( $post ) {
		$options = get_post_meta( $post->ID, '_w4pl', true );
		if ( ! $options || ! is_array( $options ) ) {
			$options = array();
		}

		$options['id']     = $post->ID;
		$this->list_editor = new W4PL_List_Editor( $options );

		// add configuration box right after post title, out of metabox.
		add_action( 'edit_form_after_title', array( $this, 'list_options_meta_box' ) );

		// enqueue js & css.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Metabox for list form
	 *
	 * @param mixed $post WP_Post object.
	 */
	public function list_options_meta_box( $post ) {
		// Print inline css before the form.
		$this->list_editor->print_css();

		// Render editor form.
		$this->list_editor->render_form();

		// Print inline js after the form.
		$this->list_editor->print_js();
	}

	/**
	 * Enqueue js & css
	 */
	public function enqueue_scripts() {
		$this->list_editor->enqueue_scripts();
	}

	/**
	 * Save post list
	 *
	 * @param  interger $post_ID Current post id.
	 */
	public function save_post( $post_ID ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_ID;
		}

		if ( ! isset( $_POST['w4pl'] ) ) {
			return;
		}

		$options       = stripslashes_deep( $_POST['w4pl'] );
		$options['id'] = $post_ID;
		$options       = apply_filters( 'w4pl/pre_save_options', $options );

		update_post_meta( $post_ID, '_w4pl', $options );
	}
}
