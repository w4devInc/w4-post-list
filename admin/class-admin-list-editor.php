<?php
/**
 * List editor class.
 *
 * @class W4PL_List_Editor
 * @package W4_Post_List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * List editor class
 */
class W4PL_List_Editor {
	/**
	 * List options
	 *
	 * @var array
	 */
	public $options;

	/**
	 * Constructor
	 *
	 * @param array $options List options.
	 */
	public function __construct( $options = array() ) {
		// Never-saved list (callers may pass only the post id): prefill
		// defaults the user can edit or clear. Existing lists - even ones
		// with intentionally blank fields - are untouched.
		$stored = array_diff_key( (array) $options, array( 'id' => true ) );
		if ( empty( $stored ) ) {
			$options['no_items_text'] = __( 'No items found.', 'w4-post-list' );
		}

		$this->options = apply_filters( 'w4pl/pre_get_options', $options );
	}

	/**
	 * Render form template
	 */
	public function render_form() {
		$options = $this->options;
		include __DIR__ . '/views/html-list-edit-form.php';
	}

	/**
	 * Enqueue js & css scripts
	 */
	public function enqueue_scripts() {
		wp_enqueue_code_editor( array( 'type' => 'text/html' ) );

		wp_enqueue_script( 'w4pl_form' );
		wp_enqueue_script( 'w4pl_list_editor' );
		wp_localize_script(
			'w4pl_list_editor',
			'w4plListEditor',
			array(
				'refreshFailed' => __( 'Could not refresh the form. Your entries are unchanged — check your connection and try again.', 'w4-post-list' ),
			)
		);
		wp_enqueue_style( 'w4pl_form' );
		wp_enqueue_style( 'w4pl_list_editor' );
	}

	/**
	 * Print inline css
	 */
	public function print_css() {
		do_action( 'w4pl/list_editor_css', $this->options );
	}

	/**
	 * Print inline js
	 */
	public function print_js() {
		do_action( 'w4pl/list_editor_js', $this->options );
	}
}
