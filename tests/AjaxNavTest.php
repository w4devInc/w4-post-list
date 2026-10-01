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
	 * What the script's history handling relies on from the server.
	 *
	 * The script keeps a list's page in the address bar as page{list id}, so
	 * Back / Forward / reload re-request that URL. These pin the server half:
	 * the URL alone decides the page, each list reads only its own parameter,
	 * and a link to page one carries no parameter (the script removes it from
	 * the address bar rather than leaving page{id}=1 behind).
	 * ------------------------------------------------------------------ */

	/**
	 * Render with the request simulated as `/?{query}`.
	 *
	 * @param int    $list_id List id.
	 * @param string $query   Query string, without the leading "?".
	 * @return string
	 */
	private function render_at( $list_id, $query ) {
		$saved_uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : null;
		$saved_req = $_REQUEST;

		parse_str( $query, $params );
		$_REQUEST               = array_merge( $_REQUEST, $params );
		$_SERVER['REQUEST_URI'] = '/' . ( '' === $query ? '' : '?' . $query );

		try {
			return do_shortcode( '[postlist id="' . $list_id . '"]' );
		} finally {
			$_REQUEST = $saved_req;
			if ( null === $saved_uri ) {
				unset( $_SERVER['REQUEST_URI'] );
			} else {
				$_SERVER['REQUEST_URI'] = $saved_uri;
			}
		}
	}

	/**
	 * Hrefs of the pagination links in `$html`, keyed by link text.
	 *
	 * @param string $html Rendered list.
	 * @return array
	 */
	private function nav_hrefs( $html ) {
		preg_match_all( '/<a[^>]*class="[^"]*page-numbers[^"]*"[^>]*href="([^"]*)"[^>]*>(.*?)<\/a>|<a[^>]*href="([^"]*)"[^>]*class="[^"]*page-numbers[^"]*"[^>]*>(.*?)<\/a>/', $html, $m, PREG_SET_ORDER );

		$hrefs = array();
		foreach ( $m as $match ) {
			$href = '' !== $match[1] ? $match[1] : $match[3];
			$text = '' !== $match[1] ? $match[2] : $match[4];

			$hrefs[ wp_strip_all_tags( $text ) ] = html_entity_decode( $href );
		}

		return $hrefs;
	}

	public function test_reload_on_page_three_renders_page_three() {
		$list_id = $this->make_list( $this->paginated_options( self::AJAX_TEMPLATE ) );

		$html = $this->render_at( $list_id, 'page' . $list_id . '=3' );

		$this->assertStringContainsString( 'Ten tips for faster sites', $html );
		$this->assertStringContainsString( 'Hello from the archive', $html );
		$this->assertStringNotContainsString( 'Spring cleaning tips', $html );
	}

	public function test_link_back_to_page_one_carries_no_page_parameter() {
		$list_id = $this->make_list( $this->paginated_options( self::AJAX_TEMPLATE ) );

		$hrefs = $this->nav_hrefs( $this->render_at( $list_id, 'utm_source=news&page' . $list_id . '=2' ) );

		$this->assertArrayHasKey( '1', $hrefs, 'Sanity: page two links back to page one.' );
		$this->assertStringNotContainsString( 'page' . $list_id, $hrefs['1'] );
		$this->assertStringContainsString( 'utm_source=news', $hrefs['1'], 'Other parameters stay.' );
		$this->assertStringContainsString( 'page' . $list_id . '=3', $hrefs['3'] );
	}

	public function test_default_nav_previous_link_from_page_two_carries_no_page_parameter() {
		$template = '<ul>[posts]<li>[post_title]</li>[/posts]</ul>[nav ajax="1"]';
		$list_id  = $this->make_list( $this->paginated_options( $template ) );

		$hrefs = $this->nav_hrefs( $this->render_at( $list_id, 'page' . $list_id . '=2' ) );

		$this->assertStringNotContainsString( 'page' . $list_id, $hrefs['Previous'] );
		$this->assertStringContainsString( 'page' . $list_id . '=3', $hrefs['Next'] );
	}

	/**
	 * One URL holds both lists' pages; each list renders its own and its
	 * links keep the other's.
	 */
	public function test_two_lists_restore_their_own_pages_from_one_url() {
		$id_a = $this->make_list( $this->paginated_options( self::AJAX_TEMPLATE ) );
		$id_b = $this->make_list( $this->paginated_options( self::AJAX_TEMPLATE ) );

		$query  = 'page' . $id_a . '=3&page' . $id_b . '=2';
		$html_a = $this->render_at( $id_a, $query );
		$html_b = $this->render_at( $id_b, $query );

		$this->assertStringContainsString( 'Hello from the archive', $html_a, 'List A: page 3.' );
		$this->assertStringContainsString( 'Year in review', $html_b, 'List B: page 2.' );
		$this->assertStringNotContainsString( 'Hello from the archive', $html_b );

		// A's link to its page 2: B's page kept, A's own page replaced, and
		// nothing else in the query.
		$hrefs_a = $this->nav_hrefs( $html_a );
		parse_str( (string) wp_parse_url( $hrefs_a['2'], PHP_URL_QUERY ), $query_a );

		$this->assertSame(
			array(
				'page' . $id_b => '2',
				'page' . $id_a => '2',
			),
			$query_a
		);
		$this->assertSame( '/', wp_parse_url( $hrefs_a['2'], PHP_URL_PATH ) );

		// And the same with this list's parameter second in the URL.
		$hrefs_b = $this->nav_hrefs( $html_b );
		parse_str( (string) wp_parse_url( $hrefs_b['3'], PHP_URL_QUERY ), $query_b );

		$this->assertSame(
			array(
				'page' . $id_a => '3',
				'page' . $id_b => '3',
			),
			$query_b
		);
		$this->assertArrayHasKey( '1', $hrefs_b );
		$this->assertSame( 'page' . $id_a . '=3', wp_parse_url( $hrefs_b['1'], PHP_URL_QUERY ), "B's link to page one drops only B's page." );
	}

	/**
	 * A value that is not a page number renders page one, with and without
	 * "Maximum items".
	 *
	 * @dataProvider provide_junk_page_values
	 *
	 * @param string $query_value Raw `page{id}` part of the query string, from "=" on (or "[]=…").
	 * @param string $limit       "Maximum items" option.
	 */
	public function test_junk_page_value_renders_page_one( $query_value, $limit ) {
		$options          = $this->paginated_options( self::AJAX_TEMPLATE );
		$options['limit'] = $limit;
		$list_id          = $this->make_list( $options );

		$html = $this->render_at( $list_id, 'page' . $list_id . $query_value );

		$this->assertStringContainsString( 'Spring cleaning tips', $html );
		$this->assertStringNotContainsString( 'Year in review', $html );
	}

	public function provide_junk_page_values() {
		$values = array(
			'word'  => '=abc',
			'zero'  => '=0',
			'empty' => '=',
			'array' => '[]=2',
		);

		$cases = array();
		foreach ( $values as $name => $value ) {
			$cases[ $name ]                 = array( $value, '' );
			$cases[ $name . ', limited' ] = array( $value, '5' );
		}

		return $cases;
	}

	/**
	 * @dataProvider provide_page_values
	 *
	 * @param mixed $value    Request value, or null for "not in the request".
	 * @param int   $expected Page number.
	 */
	public function test_list_page_is_always_a_positive_integer( $value, $expected ) {
		if ( null !== $value ) {
			$_REQUEST['page987'] = $value;
		}

		$paged = w4pl_get_list_page( 987 );

		unset( $_REQUEST['page987'] );

		$this->assertSame( $expected, $paged );
	}

	public function provide_page_values() {
		return array(
			'absent'         => array( null, 1 ),
			'string number'  => array( '3', 3 ),
			'integer'        => array( 3, 3 ),
			'one'            => array( '1', 1 ),
			'zero'           => array( '0', 1 ),
			'word'           => array( 'abc', 1 ),
			'empty'          => array( '', 1 ),
			'array'          => array( array( '2' ), 1 ),
			// As WP_Query has always read it, through absint().
			'leading digits' => array( '2abc', 2 ),
			'negative'       => array( '-3', 3 ),
		);
	}

	/* ---------------------------------------------------------------------
	 * Characterization snapshot.
	 * ------------------------------------------------------------------ */

	public function test_posts_pagination_ajax_snapshot() {
		$html = $this->render_list( $this->paginated_options( self::AJAX_TEMPLATE ) );

		$this->assertMatchesHtmlSnapshot( 'posts-pagination-ajax', $html );
	}
}
