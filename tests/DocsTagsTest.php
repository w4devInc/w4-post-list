<?php
/**
 * Docs-drift guard: every template tag used in shipped documentation
 * examples, default templates, and presets must exist in the tag registry.
 *
 * This is the CI tripwire for the class of bug fixed in 2.5.8, where the
 * official examples taught tags that were registered nowhere.
 *
 * @package W4_Post_List
 */

class DocsTagsTest extends WP_UnitTestCase {

	/**
	 * Extract candidate template tags from a template/HTML string.
	 * Matches [tag ...] and [/tag], both raw and HTML-entity-encoded.
	 */
	protected function extract_tags( $content ) {
		$content = html_entity_decode( $content, ENT_QUOTES );
		preg_match_all( '/\[\/?([a-zA-Z_][a-zA-Z0-9_]*)[\s\]]/', $content, $m );

		return array_unique( $m[1] );
	}

	protected function registered_tags() {
		$tags = array_keys( w4pl_get_shortcodes() );

		// The embed shortcode itself appears in docs and is registered with WP.
		$tags[] = 'postlist';

		return $tags;
	}

	protected function assertTagsRegistered( $used, $source ) {
		$unknown = array_diff( $used, $this->registered_tags() );

		$this->assertSame(
			array(),
			array_values( $unknown ),
			"Unregistered template tags referenced in $source: " . implode( ', ', $unknown )
		);
	}

	public function test_doc_examples_use_only_registered_tags() {
		$file = dirname( __DIR__ ) . '/admin/pages/views/html-template-examples.php';
		$this->assertTagsRegistered( $this->extract_tags( file_get_contents( $file ) ), 'html-template-examples.php' );
	}

	public function test_default_templates_use_only_registered_tags() {
		$templates = new W4PL_List_Templates();

		foreach ( array( 'posts', 'terms', 'users', 'terms.posts', 'users.posts' ) as $type ) {
			$template = $templates->sanitize_template( '', array( 'list_type' => $type ) );
			$this->assertNotEmpty( $template, "No default template for $type" );
			$this->assertTagsRegistered( $this->extract_tags( $template ), "default template for $type" );
		}
	}

	public function test_preset_templates_use_only_registered_tags() {
		$presets = new W4PL_Helper_Presets();
		$combos  = array(
			array( 'simple_list', 'posts' ),
			array( 'simple_list', 'terms' ),
			array( 'simple_list', 'users' ),
			array( 'simple_list', 'terms.posts' ),
			array( 'simple_list', 'users.posts' ),
			array( 'post_with_thumbnail', 'posts' ),
			array( 'post_with_thumbnail', 'terms.posts' ),
			array( 'post_with_thumbnail', 'users.posts' ),
		);

		foreach ( $combos as $combo ) {
			$options = $presets->pre_get_options(
				array(
					'preset'    => $combo[0],
					'list_type' => $combo[1],
				)
			);
			$this->assertNotEmpty( $options['template'], "Preset {$combo[0]} produced no template for {$combo[1]}" );
			$this->assertTagsRegistered( $this->extract_tags( $options['template'] ), "preset {$combo[0]} / {$combo[1]}" );
		}
	}
}
