<?php
/**
 * AJAX pagination must work without jQuery.
 *
 * Block themes (Twenty Twenty-Four / Twenty Twenty-Five and most 2026 themes)
 * do not enqueue jQuery on the front end, so the inline jQuery snippet that
 * shipped through 2.x threw a ReferenceError and AJAX pagination silently did
 * nothing. These tests pin the replacement: a registered, dependency-free
 * front-end script enqueued only when a rendered list actually uses [nav
 * ajax="1"], and zero inline JS in the rendered markup.
 *
 * PHPUnit cannot execute the browser half of this feature. The click / swap /
 * failure behaviours live in tests/MANUAL-QA.md.
 *
 * @package W4_Post_List
 */

require_once __DIR__ . '/class-w4pl-snapshot-testcase.php';

class AjaxNavTest extends W4PL_Snapshot_TestCase {

	const HANDLE = 'w4pl-ajax-nav';

	/**
	 * Template with a 3-page ajax pagination (6 fixture posts / 2 per page).
	 */
	const AJAX_TEMPLATE = '<ul>[posts]<li>[post_title]</li>[/posts]</ul>[nav type="plain" ajax="1"]';

	/**
	 * Same template, plain pagination.
	 */
	const PLAIN_TEMPLATE = '<ul>[posts]<li>[post_title]</li>[/posts]</ul>[nav type="plain"]';

	public function set_up() {
		parent::set_up();

		// Asset registries are globals and backupGlobals is off: start clean so
		// "registered" / "enqueued" assertions describe this test only.
		$GLOBALS['wp_scripts'] = null;
		$GLOBALS['wp_styles']  = null;

		// Render as a visitor; an admin-only notice would pollute assertions.
		wp_set_current_user( 0 );
	}

	public function tear_down() {
		$GLOBALS['wp_scripts'] = null;
		$GLOBALS['wp_styles']  = null;

		parent::tear_down();
	}

	/**
	 * Base options for a paginated posts list.
	 *
	 * @param string $template Template string.
	 * @return array
	 */
	private function paginated_options( $template ) {
		return array(
			'list_type'      => 'posts',
			'post_type'      => array( 'post' ),
			'posts_per_page' => 2,
			'orderby'        => 'post_date',
			'order'          => 'DESC',
			'template'       => $template,
		);
	}

	/**
	 * Same path as render_list(), but keeps the list id.
	 *
	 * @param array $options List options.
	 * @return int
	 */
	private function make_list( array $options ) {
		$list_id = self::factory()->post->create(
			array(
				'post_type'   => 'w4pl',
				'post_title'  => 'Ajax nav list',
				'post_status' => 'publish',
			)
		);
		update_post_meta( $list_id, '_w4pl', $options );

		return $list_id;
	}

	/**
	 * Build a list object directly, bypassing the CPT, so the accumulated
	 * $js property can be asserted on.
	 *
	 * @param array $options List options.
	 * @return W4PL_List
	 */
	private function build_list( array $options ) {
		$options['id'] = $this->make_list( $options );

		return W4PL_List_Factory::get_list( apply_filters( 'w4pl/pre_get_options', $options ) );
	}

	/* ---------------------------------------------------------------------
	 * No inline jQuery.
	 * ------------------------------------------------------------------ */

	public function test_ajax_nav_accumulates_no_inline_js() {
		$list = $this->build_list( $this->paginated_options( self::AJAX_TEMPLATE ) );
		$list->get_html();

		$this->assertSame(
			'',
			$list->js,
			'AJAX navigation must not push anything into the inline JS channel.'
		);
	}

	public function test_ajax_nav_output_contains_no_jquery_and_no_script_tag() {
		$html = $this->render_list( $this->paginated_options( self::AJAX_TEMPLATE ) );

		$this->assertStringContainsString( 'ajax-navigation', $html, 'Sanity: the list really did render ajax nav.' );
		$this->assertStringNotContainsString( 'jQuery', $html, 'Block themes do not load jQuery on the front end.' );
		$this->assertStringNotContainsString( '<script', $html, 'AJAX navigation must not emit per-list inline JS.' );
	}

	/**
	 * Guard rail against over-deleting: the shared inline-JS channel still
	 * carries the user's own per-list JS option.
	 */
	public function test_custom_per_list_js_option_still_renders() {
		$options       = $this->paginated_options( self::AJAX_TEMPLATE );
		$options['js'] = 'window.w4plCustomHook = 1;';

		$html = $this->render_list( $options );

		$this->assertStringContainsString( 'window.w4plCustomHook = 1;', $html );
		$this->assertMatchesRegularExpression( '/<script id="w4pl-js-\d+"/', $html );
	}

	/* ---------------------------------------------------------------------
	 * Markup contract the script keys off.
	 * ------------------------------------------------------------------ */

	public function test_ajax_nav_markup_carries_every_hook_the_script_needs() {
		$list_id = $this->make_list( $this->paginated_options( self::AJAX_TEMPLATE ) );
		$html    = do_shortcode( '[postlist id="' . $list_id . '"]' );

		// 1. The wrapper the script swaps into, addressable from a clicked link.
		$this->assertStringContainsString( 'id="w4pl-list-' . $list_id . '"', $html );
		// 2. The subtree that gets replaced.
		$this->assertStringContainsString( 'class="w4pl-inner"', $html );
		// 3. The opt-in marker that distinguishes ajax nav from plain nav.
		$this->assertStringContainsString( 'class="navigation ajax-navigation"', $html );
		// 4. The links, carrying this list's own page parameter.
		$this->assertStringContainsString( 'class="page-numbers" href="', $html );
		$this->assertStringContainsString( 'page' . $list_id . '=2', $html );
	}

	public function test_plain_nav_is_not_marked_as_ajax() {
		$html = $this->render_list( $this->paginated_options( self::PLAIN_TEMPLATE ) );

		$this->assertStringContainsString( 'class="navigation"', $html );
		$this->assertStringNotContainsString( 'ajax-navigation', $html );
	}

	/* ---------------------------------------------------------------------
	 * Asset registration / conditional enqueue.
	 * ------------------------------------------------------------------ */

	public function test_script_is_registered_on_the_front_end() {
		do_action( 'wp_enqueue_scripts' );

		$this->assertTrue( wp_script_is( self::HANDLE, 'registered' ) );
	}

	public function test_registered_script_has_no_dependencies_and_loads_in_the_footer() {
		do_action( 'wp_enqueue_scripts' );

		$script = wp_scripts()->registered[ self::HANDLE ];

		$this->assertSame( array(), $script->deps, 'Vanilla JS: block themes do not load jQuery.' );
		$this->assertNotEmpty( $script->ver, 'A version is needed for cache busting.' );
		$this->assertSame( 1, $script->extra['group'], 'The script must print in the footer.' );
	}

	public function test_registered_script_points_at_a_file_that_exists() {
		do_action( 'wp_enqueue_scripts' );

		$src  = wp_scripts()->registered[ self::HANDLE ]->src;
		$path = str_replace( W4PL_URL, trailingslashit( W4PL_DIR ), $src );

		$this->assertFileExists( $path, 'A registered but missing script 404s silently.' );
	}

	public function test_rendering_an_ajax_list_enqueues_the_script() {
		$this->render_list( $this->paginated_options( self::AJAX_TEMPLATE ) );

		$this->assertTrue( wp_script_is( self::HANDLE, 'enqueued' ) );
	}

	/**
	 * Pins both halves of the register-then-enqueue split on a page with no
	 * list at all. Without this, collapsing wp_register_script() into
	 * wp_enqueue_script() in register_frontend_scripts() - a plausible
	 * "simplification" - would ship the script on every front-end request and
	 * the whole suite would stay green.
	 */
	public function test_a_front_end_page_with_no_ajax_list_registers_but_does_not_enqueue_the_script() {
		do_action( 'wp_enqueue_scripts' );

		$this->assertTrue( wp_script_is( self::HANDLE, 'registered' ) );
		$this->assertFalse( wp_script_is( self::HANDLE, 'enqueued' ) );
	}

	public function test_rendering_a_plain_nav_list_does_not_enqueue_the_script() {
		$this->render_list( $this->paginated_options( self::PLAIN_TEMPLATE ) );

		$this->assertFalse( wp_script_is( self::HANDLE, 'enqueued' ) );
	}

	public function test_rendering_a_list_with_no_nav_at_all_does_not_enqueue_the_script() {
		$this->render_list(
			array(
				'list_type'      => 'posts',
				'post_type'      => array( 'post' ),
				'posts_per_page' => 10,
				'template'       => '<ul>[posts]<li>[post_title]</li>[/posts]</ul>',
			)
		);

		$this->assertFalse( wp_script_is( self::HANDLE, 'enqueued' ) );
	}

	/**
	 * With no results there are no pagination links, so there is nothing for
	 * the script to bind to and it must stay out of the page.
	 */
	public function test_ajax_list_with_no_results_does_not_enqueue_the_script() {
		$this->render_list(
			array(
				'list_type'      => 'posts',
				'post_type'      => array( 'post' ),
				'post__in'       => '999999',
				'posts_per_page' => 2,
				'template'       => self::AJAX_TEMPLATE,
			)
		);

		$this->assertFalse( wp_script_is( self::HANDLE, 'enqueued' ) );
	}

	/**
	 * Through 2.x the inline IIFE was part of the returned HTML, so it shipped
	 * whenever the list rendered. An enqueue is only honoured while something
	 * still flushes the queue: a list rendered after wp_print_footer_scripts
	 * (wp_footer, priority 20) would otherwise emit no script at all.
	 *
	 * did_action() counters accumulate across tests in one process, so fire the
	 * hook explicitly rather than relying on a fresh wp_footer().
	 */
	public function test_list_rendered_after_footer_scripts_still_ships_the_script() {
		$list_id = $this->make_list( $this->paginated_options( self::AJAX_TEMPLATE ) );

		ob_start();
		do_action( 'wp_print_footer_scripts' );
		$html = do_shortcode( '[postlist id="' . $list_id . '"]' );
		$out  = ob_get_clean();

		$this->assertStringContainsString( 'ajax-navigation', $html, 'Sanity: the list really did render ajax nav.' );
		$this->assertStringContainsString( 'list-ajax-nav.js', $out . $html, 'A late-rendered list must still print the script tag.' );
	}

	/* ---------------------------------------------------------------------
	 * N lists on one page.
	 * ------------------------------------------------------------------ */

	public function test_two_ajax_lists_share_one_script_and_keep_independent_page_params() {
		$id_a = $this->make_list( $this->paginated_options( self::AJAX_TEMPLATE ) );
		$id_b = $this->make_list( $this->paginated_options( self::AJAX_TEMPLATE ) );

		$html = do_shortcode( '[postlist id="' . $id_a . '"]' ) . do_shortcode( '[postlist id="' . $id_b . '"]' );

		$this->assertNotSame( $id_a, $id_b );
		$this->assertStringContainsString( 'page' . $id_a . '=2', $html );
		$this->assertStringContainsString( 'page' . $id_b . '=2', $html );
		$this->assertStringNotContainsString( '<script', $html, 'No per-list inline JS: one shared script serves N lists.' );

		// One handle, enqueued once, however many lists are on the page.
		$this->assertTrue( wp_script_is( self::HANDLE, 'enqueued' ) );
		$this->assertCount( 1, wp_scripts()->queue );
	}

	/**
	 * The server half of ajax pagination: a deep link to page 2 must render
	 * page 2 without any JS involved.
	 */
	public function test_deep_link_to_page_two_renders_page_two_server_side() {
		$list_id                       = $this->make_list( $this->paginated_options( self::AJAX_TEMPLATE ) );
		$_REQUEST[ 'page' . $list_id ] = 2;

		$html = do_shortcode( '[postlist id="' . $list_id . '"]' );

		unset( $_REQUEST[ 'page' . $list_id ] );

		$this->assertStringContainsString( 'Year in review', $html, 'Page 2 item.' );
		$this->assertStringNotContainsString( 'Spring cleaning tips', $html, 'Page 1 item.' );
	}

	/* ---------------------------------------------------------------------
	 * Characterization snapshot.
	 * ------------------------------------------------------------------ */

	public function test_posts_pagination_ajax_snapshot() {
		$html = $this->render_list( $this->paginated_options( self::AJAX_TEMPLATE ) );

		$this->assertMatchesHtmlSnapshot( 'posts-pagination-ajax', $html );
	}
}
