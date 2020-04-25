<?php
/**
 * Admin list table custom columns
 *
 * @class W4PL_Admin_Lists_Table
 * @package W4 Post List
 */

defined( 'ABSPATH' ) || exit;

class W4PL_Admin_Lists_Metaboxes {

	public function __construct() {
		add_action( 'add_meta_boxes_'. w4pl()->list_post_type(), array( $this, 'add_meta_boxes' ), 20 );
		add_action( 'save_post_'. w4pl()->list_post_type(), array( $this, 'save_post' ), 20, 3 );
	}

	// Meta box
	public function add_meta_boxes( $post )	{
		// add configuration box right after post title, out of metabox
		add_action( 'edit_form_after_title', array( $this, 'list_options_meta_box' ) );

		// add plugin news metabox one right side
		# add_meta_box( "w4pl_news_meta_box", __('Plugin Updates', 'w4pl'), array($this, 'news_meta_box'), w4pl()->plugin_slug(), "side", 'core');

		// enqueue script files, print css on header and print js on footer
		add_action( 'admin_head', array( $this, 'admin_head' ) );
	}

	public function admin_head() {
		$options = get_post_meta( get_the_ID(), '_w4pl', true );
		if ( ! $options || ! is_array( $options ) ) {
			$options = array();
		}

		$options['id'] = get_the_ID();

		do_action( 'w4pl/list_options_print_scripts', $options );
	}

	public function list_options_meta_box( $post ) {
		$options = get_post_meta($post->ID, '_w4pl', true);
		if (! $options || ! is_array($options)) {
			$options = array();
		}

		$options['id'] = $post->ID;

		# $editor = new W4PL_List_Editor($options);
		# $editor->render_form();

		do_action( 'w4pl/list_edit_form', $options );
	}


	public function save_post( $post_ID, $post, $update ) {
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
			return $post_ID ;
		}

		if ( ! isset( $_POST['w4pl'] ) ) {
			return;
		}

		$options = stripslashes_deep( $_POST['w4pl'] );
		if ( ! isset( $options['id'] ) ) {
			$options['id'] = $post_ID;
		}

		$options = apply_filters( 'w4pl/pre_save_options', $options );
		# self::p( $options ); die();

		update_post_meta( $post_ID, '_w4pl', $options );
	}

	public static function news_meta_box()
	{
		W4PL_Utils::plugin_news();
	}
}
