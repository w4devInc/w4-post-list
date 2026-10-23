<?php
/**
 * A list's own CSS and JavaScript go through the style / script API, printed
 * in place under a per-list handle, once per request.
 *
 * @package W4_Post_List
 */

require_once __DIR__ . '/class-w4pl-snapshot-testcase.php';

class InlineAssetsTest extends W4PL_Snapshot_TestCase {

	const TEMPLATE = '<ul>[posts]<li>[post_title]</li>[/posts]</ul>';

	public function set_up() {
		parent::set_up();

		$GLOBALS['wp_scripts'] = null;
		$GLOBALS['wp_styles']  = null;

		wp_set_current_user( 0 );
	}

	public function tear_down() {
		$GLOBALS['wp_scripts'] = null;
		$GLOBALS['wp_styles']  = null;

		// The excerpt test go_to()s a list page; the base test case does not
		// undo that for plugin suites, and pagination links in later tests
		// are built from the request URI.
		$this->go_to( '/' );
		$_SERVER['REQUEST_URI'] = '';

		parent::tear_down();
	}

	private function styled_options() {
		return array(
			'list_type'      => 'posts',
			'post_type'      => array( 'post' ),
			'posts_per_page' => 2,
			'template'       => self::TEMPLATE,
			'css'            => '#w4pl-list-[listid] li { color: red; }',
			'js'             => 'window.w4plList[listid] = true;',
		);
	}

	private function make_styled_list() {
		$list_id = self::factory()->post->create(
			array(
				'post_type'   => 'w4pl',
				'post_title'  => 'Styled list',
				'post_status' => 'publish',
			)
		);
		update_post_meta( $list_id, '_w4pl', $this->styled_options() );

		return $list_id;
	}

	public function test_css_and_js_print_in_place_around_the_list_with_listid_filled_in() {
		$list_id = $this->make_styled_list();
		$html    = do_shortcode( '[postlist id="' . $list_id . '"]' );

		$style_pos  = strpos( $html, '<style id="w4pl-list-' . $list_id . '-inline-css">' );
		$list_pos   = strpos( $html, '<div id="w4pl-list-' . $list_id . '"' );
		$script_pos = strpos( $html, '<script id="w4pl-list-' . $list_id . '-js-after"' );

		$this->assertNotFalse( $style_pos, 'The CSS prints as a style tag under the per-list handle.' );
		$this->assertNotFalse( $script_pos, 'The JS prints as a script tag under the per-list handle.' );
		$this->assertLessThan( $list_pos, $style_pos, 'CSS comes right before the list, as it always has.' );
		$this->assertGreaterThan( $list_pos, $script_pos, 'JS comes right after the list, as it always has.' );

		$this->assertStringContainsString( '#w4pl-list-' . $list_id . ' li { color: red; }', $html );
		$this->assertStringContainsString( 'window.w4plList' . $list_id . ' = true;', $html );
	}

	public function test_the_css_and_js_are_readable_through_the_asset_registries() {
		$list_id = $this->make_styled_list();
		do_shortcode( '[postlist id="' . $list_id . '"]' );

		$handle = 'w4pl-list-' . $list_id;

		$this->assertTrue( wp_style_is( $handle, 'registered' ) );
		$this->assertTrue( wp_script_is( $handle, 'registered' ) );
		$this->assertSame( array( '#w4pl-list-' . $list_id . ' li { color: red; }' ), wp_styles()->get_data( $handle, 'after' ) );
		$this->assertSame( array( 'window.w4plList' . $list_id . ' = true;' ), array_values( array_filter( wp_scripts()->get_data( $handle, 'after' ) ) ) );
	}

	/**
	 * Every render is self-contained, as through 3.0.9. A render whose output
	 * is thrown away (an excerpt built from `the_content`, an Open Graph
	 * scanner reading the page early) must not use up the tag the visible
	 * render needs.
	 */
	public function test_a_render_that_is_thrown_away_does_not_cost_the_next_render_its_css_and_js() {
		$list_id = $this->make_styled_list();

		$discarded = do_shortcode( '[postlist id="' . $list_id . '"]' );
		$this->assertStringContainsString( 'color: red', $discarded, 'Sanity: the first render had the CSS.' );

		$html = do_shortcode( '[postlist id="' . $list_id . '"]' );

		$this->assertStringContainsString( '<style id="w4pl-list-' . $list_id . '-inline-css">', $html );
		$this->assertStringContainsString( '#w4pl-list-' . $list_id . ' li { color: red; }', $html );
		$this->assertStringContainsString( '<script id="w4pl-list-' . $list_id . '-js-after"', $html );
		$this->assertStringContainsString( 'window.w4plList' . $list_id . ' = true;', $html );
	}

	public function test_a_list_rendered_twice_on_one_page_prints_its_css_and_js_with_each_copy_as_before() {
		$list_id = $this->make_styled_list();
		$html    = do_shortcode( '[postlist id="' . $list_id . '"] [postlist id="' . $list_id . '"]' );

		$this->assertSame( 2, substr_count( $html, '<div id="w4pl-list-' . $list_id . '"' ), 'Sanity: the list rendered twice.' );
		$this->assertSame( 2, substr_count( $html, '<style' ) );
		$this->assertSame( 2, substr_count( $html, '<script' ) );
	}

	public function test_the_list_excerpt_pass_does_not_strip_the_css_from_the_visible_list() {
		$list_id = $this->make_styled_list();

		// A list's own single page: W4PL_List_Content injects the list into
		// the_content; an SEO plugin reading the excerpt runs that pass first.
		$this->go_to( get_permalink( $list_id ) );
		$this->assertTrue( is_singular( 'w4pl' ), 'Sanity: on the list page.' );

		$excerpt = get_the_excerpt( $list_id );
		$this->assertNotEmpty( $excerpt, 'Sanity: the excerpt pass rendered something.' );

		$html = apply_filters( 'the_content', get_post( $list_id )->post_content );

		$this->assertStringContainsString( '#w4pl-list-' . $list_id . ' li { color: red; }', $html );
		$this->assertStringContainsString( 'window.w4plList' . $list_id . ' = true;', $html );
	}

	public function test_printing_does_not_fire_the_page_level_print_actions() {
		$fired = 0;
		$count = function () use ( &$fired ) {
			++$fired;
		};
		add_action( 'wp_print_scripts', $count );
		add_action( 'wp_print_styles', $count );

		$list_id = $this->make_styled_list();
		do_shortcode( '[postlist id="' . $list_id . '"]' );

		remove_action( 'wp_print_scripts', $count );
		remove_action( 'wp_print_styles', $count );

		$this->assertSame( 0, $fired );
	}

	public function test_two_different_lists_each_print_their_own_css_and_js() {
		$a    = $this->make_styled_list();
		$b    = $this->make_styled_list();
		$html = do_shortcode( '[postlist id="' . $a . '"] [postlist id="' . $b . '"]' );

		$this->assertStringContainsString( 'id="w4pl-list-' . $a . '-inline-css"', $html );
		$this->assertStringContainsString( 'id="w4pl-list-' . $b . '-inline-css"', $html );
		$this->assertStringContainsString( 'window.w4plList' . $a . ' = true;', $html );
		$this->assertStringContainsString( 'window.w4plList' . $b . ' = true;', $html );
	}

	public function test_a_list_without_css_or_js_prints_no_style_or_script_tag_and_registers_nothing() {
		$options = $this->styled_options();
		unset( $options['css'], $options['js'] );

		$list_id = self::factory()->post->create( array( 'post_type' => 'w4pl', 'post_status' => 'publish' ) );
		update_post_meta( $list_id, '_w4pl', $options );

		$html = do_shortcode( '[postlist id="' . $list_id . '"]' );

		$this->assertStringNotContainsString( '<style', $html );
		$this->assertStringNotContainsString( '<script', $html );
		$this->assertFalse( wp_style_is( 'w4pl-list-' . $list_id, 'registered' ) );
		$this->assertFalse( wp_script_is( 'w4pl-list-' . $list_id, 'registered' ) );
	}

	/**
	 * The admin live preview (admin-ajax) and the block editor's server-side
	 * render (REST) consume the returned HTML with no wp_footer; the tags
	 * must be in the HTML there too.
	 */
	public function test_css_and_js_are_in_the_returned_html_during_an_ajax_request() {
		require_once dirname( __DIR__ ) . '/admin/class-admin-lists-metaboxes.php';
		require_once dirname( __DIR__ ) . '/admin/class-admin-preview.php';

		add_filter( 'wp_doing_ajax', '__return_true' );

		$list_id = $this->make_styled_list();
		$html    = W4PL_Admin_Preview::render_preview( array_merge( $this->styled_options(), array( 'id' => $list_id ) ) );

		remove_filter( 'wp_doing_ajax', '__return_true' );

		$this->assertStringContainsString( '#w4pl-list-' . $list_id . ' li { color: red; }', $html );
		$this->assertStringContainsString( 'window.w4plList' . $list_id . ' = true;', $html );
	}

	public function test_inline_script_attributes_filter_reaches_the_list_script_tag() {
		$add_nonce = function ( $attributes ) {
			$attributes['nonce'] = 'w4pl-test-nonce';
			return $attributes;
		};
		add_filter( 'wp_inline_script_attributes', $add_nonce );

		$list_id = $this->make_styled_list();
		$html    = do_shortcode( '[postlist id="' . $list_id . '"]' );

		remove_filter( 'wp_inline_script_attributes', $add_nonce );

		$this->assertMatchesRegularExpression( '/<script[^>]*nonce="w4pl-test-nonce"[^>]*>/', $html );
	}
}
