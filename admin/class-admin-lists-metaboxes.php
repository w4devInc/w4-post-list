<?php
/**
 * Admin list table custom columns
 *
 * @class W4PL_Admin_Lists_Metaboxes
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

			if ( ! empty( $options['id'] ) && current_user_can( 'edit_post', (int) $options['id'] ) ) {
				if ( is_object( $options ) ) {
					$options = get_object_vars( $options );
				}

				$options = $this->sanitize_options( $options );

				if ( ! empty( $options ) ) {
					$this->list_editor = new W4PL_List_Editor( $options );
					$this->list_editor->render_form();
				}
			} else {
				echo '<div class="error"><p>' . esc_html__( 'You do not have permission to edit this list.', 'w4-post-list' ) . '</p></div>';
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
	 * @param int $post_ID Current post id.
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

		$options = $this->sanitize_options( $options );

		$options = apply_filters( 'w4pl/pre_save_options', $options );

		update_post_meta( $post_ID, '_w4pl', $options );
	}

	/**
	 * Sanitize options
	 *
	 * @param array $options List options.
	 * @return array
	 */
	public function sanitize_options( $options ) {
		// Sanitize options.
		foreach ( array( 'no_items_text' ) as $key ) {
			if ( array_key_exists( $key, $options ) ) {
				$options[ $key ] = sanitize_textarea_field( $options[ $key ] );
			}
		}

		// foreach ( array( 'template', 'css', 'js' ) as $key ) {
		// 	if ( array_key_exists( $key, $options ) ) {
		// 		$options[ $key ] = wp_kses_post( $options[ $key ] );
		// 	}
		// }

		foreach ( array( 'post_s', 'post__in', 'post__not_in', 'post_parent__in', 'author__in', 'author__not_in', 'posts_per_page', 'limit', 'offset' ) as $key ) {
			if ( array_key_exists( $key, $options ) ) {
				$options[ $key ] = sanitize_text_field( $options[ $key ] );
			}
		}

		foreach ( array( 'terms__in', 'terms__not_in', 'terms_parent__in', 'terms_name__like', 'terms_slug__like', 'terms_description__like', 'terms_count__min', 'terms_offset', 'terms_limit', 'terms_max' ) as $key ) {
			if ( array_key_exists( $key, $options ) ) {
				$options[ $key ] = sanitize_text_field( $options[ $key ] );
			}
		}

		foreach ( array( 'users__in', 'users__not_in', 'users_display_name__like', 'users_user_email__like', 'users_offset', 'users_limit', 'users_max' ) as $key ) {
			if ( array_key_exists( $key, $options ) ) {
				$options[ $key ] = sanitize_text_field( $options[ $key ] );
			}
		}

		foreach ( array( 'class' ) as $key ) {
			if ( array_key_exists( $key, $options ) ) {
				$options[ $key ] = sanitize_html_class( $options[ $key ] );
			}
		}

		return $options;
	}
}
