<?php
/**
 * Live preview rendering and endpoint authorization.
 *
 * @package W4_Post_List
 */

require_once __DIR__ . '/class-w4pl-snapshot-testcase.php';

class PreviewTest extends W4PL_Snapshot_TestCase {

	public static function wpSetUpBeforeClass( $factory ) {
		parent::wpSetUpBeforeClass( $factory );
		require_once dirname( __DIR__ ) . '/admin/class-admin-lists-metaboxes.php';
		require_once dirname( __DIR__ ) . '/admin/class-admin-preview.php';
	}

	public function test_render_preview_matches_shortcode_output_for_same_options() {
		$options = array(
			'id'             => 424242,
			'list_type'      => 'posts',
			'post_type'      => array( 'post' ),
			'posts_per_page' => 10,
			'orderby'        => 'post_date',
			'order'          => 'DESC',
		);

		$preview = W4PL_Admin_Preview::render_preview( $options );

		$this->assertStringContainsString( 'Winter release notes', $preview );
		$this->assertStringContainsString( 'class="post-item"', $preview, 'Preview uses the default template pipeline' );
	}

	public function test_render_preview_renders_unsaved_starter_output() {
		$options = W4PL_Starter_Templates::apply(
			array(
				'id'               => 424242,
				'list_type'        => 'posts',
				'post_type'        => array( 'post' ),
				'starter_template' => 'thumbnail-cards',
			)
		);

		$preview = W4PL_Admin_Preview::render_preview( $options );

		$this->assertStringContainsString( 'w4pl-cards', $preview );
	}

	public function test_render_preview_throws_for_invalid_type() {
		$this->expectException( Exception::class );

		W4PL_Admin_Preview::render_preview(
			array(
				'id'        => 424242,
				'list_type' => 'bogus',
			)
		);
	}
}
