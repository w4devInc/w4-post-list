<?php
/**
 * Live preview of unsaved list options in the editor.
 *
 * @class W4PL_Admin_Preview
 * @package W4_Post_List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the current (possibly unsaved) editor options to HTML on demand.
 */
class W4PL_Admin_Preview {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp_ajax_w4pl_list_preview', array( $this, 'ajax_preview' ) );
		add_action( 'edit_form_after_title', array( $this, 'preview_pane' ), 30 );
	}

	/**
	 * Render posted options through the exact pipeline the shortcode uses.
	 *
	 * @param  array $options Raw (unslashed) options from the editor form.
	 * @return string
	 * @throws Exception When the options cannot produce a list.
	 */
	public static function render_preview( array $options ) {
		$options = W4PL_Admin_Lists_Metaboxes::sanitize_options( $options );
		$options = apply_filters( 'w4pl/pre_get_options', $options );

		$list = W4PL_List_Factory::get_list( $options );

		return $list->get_html();
	}

	/**
	 * AJAX endpoint: nonce + per-post capability checked.
	 */
	public function ajax_preview() {
		check_ajax_referer( 'w4pl_preview', 'nonce' );

		$posted = array();
		if ( isset( $_POST['w4pl'] ) && is_array( $_POST['w4pl'] ) ) {
			// Sanitized field-by-field in render_preview() via sanitize_options().
			$posted = wp_unslash( $_POST['w4pl'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		}

		$list_id = 0;
		if ( isset( $posted['id'] ) ) {
			$list_id = (int) $posted['id'];
		}

		if ( ! $list_id || ! current_user_can( 'edit_post', $list_id ) ) {
			wp_send_json_error(
				array( 'message' => __( 'You do not have permission to preview this list.', 'w4-post-list' ) ),
				403
			);
		}

		// Only the render is guarded; the send calls stay outside so their
		// terminating behavior is never swallowed by this catch.
		try {
			$html = self::render_preview( $posted );
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}

		wp_send_json_success( array( 'html' => $html ) );
	}

	/**
	 * Preview region under the editor form. Lives outside #w4pl_list_options
	 * so it survives the AJAX form replacement.
	 *
	 * @param WP_Post $post Current post.
	 */
	public function preview_pane( $post ) {
		if ( ! $post instanceof WP_Post || W4PL_Config::LIST_POST_TYPE !== $post->post_type ) {
			return;
		}
		?>
		<div id="w4pl_preview_wrap" style="margin-top:12px;">
			<p style="margin:0 0 8px;">
				<button type="button" class="button" id="w4pl_preview_toggle" aria-expanded="false" aria-controls="w4pl_preview_pane">
					<?php esc_html_e( 'Preview', 'w4-post-list' ); ?>
				</button>
				<span id="w4pl_preview_status" role="status" style="margin-left:8px;color:#777;"></span>
			</p>
			<div id="w4pl_preview_pane" style="display:none;border:1px solid #dcdcde;border-radius:4px;background:#fff;">
				<iframe id="w4pl_preview_frame" title="<?php esc_attr_e( 'List preview', 'w4-post-list' ); ?>" style="width:100%;height:420px;border:0;"></iframe>
			</div>
			<p class="description" style="margin-top:6px;">
				<?php esc_html_e( 'The preview renders your unsaved settings without your theme\'s styles, so spacing and fonts will differ on the real page.', 'w4-post-list' ); ?>
			</p>
		</div>
		<?php
	}
}
