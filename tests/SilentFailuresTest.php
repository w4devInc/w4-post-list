<?php
/**
 * Error surfacing: problems are shown to editors, never to visitors.
 *
 * @package W4_Post_List
 */

class SilentFailuresTest extends WP_UnitTestCase {

	public static function wpSetUpBeforeClass( $factory ) {
		require_once dirname( __DIR__ ) . '/admin/class-admin-list-editor.php';
	}

	protected function make_admin() {
		return self::factory()->user->create( array( 'role' => 'administrator' ) );
	}

	public function test_wrong_list_id_is_silent_for_visitors() {
		wp_set_current_user( 0 );

		$this->assertSame( '', do_shortcode( '[postlist id="999999"]' ) );
	}

	public function test_wrong_list_id_shows_notice_to_editors() {
		wp_set_current_user( $this->make_admin() );

		$html = do_shortcode( '[postlist id="999999"]' );

		$this->assertStringContainsString( 'W4 Post List', $html );
		$this->assertStringContainsString( 'Shortcode column', $html );
	}

	public function test_render_exception_is_silent_for_visitors() {
		$list_id = self::factory()->post->create(
			array(
				'post_type'   => 'w4pl',
				'post_status' => 'publish',
			)
		);
		update_post_meta( $list_id, '_w4pl', array( 'list_type' => 'bogus' ) );

		wp_set_current_user( 0 );
		$this->assertSame( '', do_shortcode( '[postlist id="' . $list_id . '"]' ) );

		wp_set_current_user( $this->make_admin() );
		$html = do_shortcode( '[postlist id="' . $list_id . '"]' );
		$this->assertStringContainsString( 'W4 Post List error', $html );
	}

	public function test_new_list_editor_prefills_no_items_text() {
		$editor = new W4PL_List_Editor( array( 'id' => 12345 ) );

		$this->assertSame( 'No items found.', $editor->options['no_items_text'] );
	}

	public function test_existing_list_keeps_intentionally_blank_no_items_text() {
		$editor = new W4PL_List_Editor(
			array(
				'id'        => 12345,
				'list_type' => 'posts',
			)
		);

		$this->assertSame( '', $editor->options['no_items_text'] );
	}

	public function test_no_items_text_renders_on_empty_results() {
		$list_id = self::factory()->post->create(
			array(
				'post_type'   => 'w4pl',
				'post_status' => 'publish',
			)
		);
		update_post_meta(
			$list_id,
			'_w4pl',
			array(
				'list_type'     => 'posts',
				'post_type'     => array( 'post' ),
				'post__in'      => '999999',
				'no_items_text' => 'No items found.',
			)
		);

		wp_set_current_user( 0 );
		$html = do_shortcode( '[postlist id="' . $list_id . '"]' );

		$this->assertStringContainsString( 'No items found.', $html );
	}
}
