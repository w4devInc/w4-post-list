<?php
/**
 * Visitor sorting (#17): a per-list, opt-in dropdown that re-orders a posts
 * list through the w4pl_sort_{list id} query parameter.
 *
 * Pins three contracts:
 * - lists that do not opt in render exactly as before, whatever the URL says;
 * - only tokens the list offers reach WP_Query, compared byte-for-byte;
 * - the sort survives pagination (plain and AJAX) because the links carry it.
 *
 * The browser half (change-to-submit, AJAX swap, focus) is in MANUAL-QA.md.
 *
 * @package W4_Post_List
 */

require_once __DIR__ . '/class-w4pl-snapshot-testcase.php';

class VisitorSortTest extends W4PL_Snapshot_TestCase {

	const LOOP = '<ul>[posts]<li>[post_title]</li>[/posts]</ul>';

	/**
	 * Fixture titles, A to Z.
	 */
	const TITLES_AZ = array(
		'Hello from the archive',
		'Interview with a builder',
		'Spring cleaning tips',
		'Ten tips for faster sites',
		'Winter release notes',
		'Year in review',
	);

	/**
	 * Fixture titles, newest first (the default order).
	 */
	const TITLES_NEWEST = array(
		'Spring cleaning tips',
		'Winter release notes',
		'Year in review',
		'Interview with a builder',
		'Ten tips for faster sites',
		'Hello from the archive',
	);

	private $request_uri;

	public function set_up() {
		parent::set_up();

		require_once dirname( __DIR__ ) . '/admin/class-admin-lists-metaboxes.php';
		require_once dirname( __DIR__ ) . '/admin/class-admin-validation.php';

		$GLOBALS['wp_scripts'] = null;
		$this->request_uri     = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : null;
		$_SERVER['REQUEST_URI'] = '/';

		wp_set_current_user( 0 );
	}

	public function tear_down() {
		foreach ( array_keys( $_GET ) as $key ) {
			if ( 0 === strpos( $key, 'w4pl_sort_' ) || 0 === strpos( $key, 'page' ) || 'utm_source' === $key ) {
				unset( $_GET[ $key ], $_REQUEST[ $key ] );
			}
		}

		if ( null === $this->request_uri ) {
			unset( $_SERVER['REQUEST_URI'] );
		} else {
			$_SERVER['REQUEST_URI'] = $this->request_uri;
		}

		$GLOBALS['wp_scripts'] = null;

		parent::tear_down();
	}

	/**
	 * Options for a posts list over the six fixtures.
	 *
	 * @param array $extra Options to merge in.
	 * @return array
	 */
	private function options( array $extra = array() ) {
		return array_merge(
			array(
				'list_type'      => 'posts',
				'post_type'      => array( 'post' ),
				'posts_per_page' => 10,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'template'       => self::LOOP,
			),
			$extra
		);
	}

	private function make_list( array $options ) {
		$list_id = self::factory()->post->create(
			array(
				'post_type'   => 'w4pl',
				'post_title'  => 'Visitor sort list',
				'post_status' => 'publish',
			)
		);
		update_post_meta( $list_id, '_w4pl', $options );

		return $list_id;
	}

	private function render( $list_id ) {
		return do_shortcode( '[postlist id="' . $list_id . '"]' );
	}

	/**
	 * Simulate a request whose URL carries the given query parameters.
	 *
	 * @param array $params Query parameters.
	 */
	private function request( array $params ) {
		foreach ( $params as $key => $value ) {
			$_GET[ $key ]     = $value;
			$_REQUEST[ $key ] = $value;
		}

		$_SERVER['REQUEST_URI'] = '/?' . http_build_query( $params );
	}

	/**
	 * Fixture titles in the order they appear in `$html`.
	 *
	 * @param string $html Rendered list.
	 * @return array
	 */
	private function titles( $html ) {
		preg_match_all( '/<li>([^<]+)<\/li>/', $html, $m );

		return $m[1];
	}

	/* ---------------------------------------------------------------------
	 * Opt-in: lists without visitor sorting are untouched.
	 * ------------------------------------------------------------------ */

	public function test_list_without_visitor_sort_ignores_the_url_parameter() {
		$list_id = $this->make_list( $this->options() );
		$before  = $this->render( $list_id );

		$this->request( array( 'w4pl_sort_' . $list_id => 'title-asc' ) );
		$after = $this->render( $list_id );

		$this->assertSame( $before, $after, 'A list that did not opt in must render byte-for-byte the same.' );
		$this->assertSame( self::TITLES_NEWEST, $this->titles( $after ) );
		$this->assertStringNotContainsString( 'w4pl-sort', $after );
		$this->assertFalse( wp_script_is( 'w4pl-ajax-nav', 'enqueued' ) );
	}

	public function test_empty_visitor_sort_option_is_off() {
		$list_id = $this->make_list( $this->options( array( 'visitor_sort' => array() ) ) );
		$this->request( array( 'w4pl_sort_' . $list_id => 'title-asc' ) );

		$html = $this->render( $list_id );

		$this->assertSame( self::TITLES_NEWEST, $this->titles( $html ) );
		$this->assertStringNotContainsString( '<form', $html );
	}

	/**
	 * A disabled list keeps printing a literal [sort] outside the loops, as
	 * any unrendered tag always has; the editor warns about it on save.
	 */
	public function test_disabled_list_leaves_a_sort_tag_outside_the_loop_alone() {
		$list_id = $this->make_list( $this->options( array( 'template' => '[sort]' . self::LOOP ) ) );

		$html = $this->render( $list_id );

		$this->assertStringContainsString( '[sort]', $html );
		$this->assertStringNotContainsString( '<form', $html );
	}

	public function test_other_list_types_ignore_visitor_sort() {
		$offered = W4PL_Helper_Visitor_Sort::offered(
			array(
				'list_type'    => 'terms.posts',
				'visitor_sort' => array( 'title-asc' ),
			)
		);

		$this->assertSame( array(), $offered );
	}

	/* ---------------------------------------------------------------------
	 * Whitelist.
	 * ------------------------------------------------------------------ */

	public function test_offered_token_reorders_the_list() {
		$list_id = $this->make_list( $this->options( array( 'visitor_sort' => array( 'title-asc', 'title-desc' ) ) ) );

		$this->request( array( 'w4pl_sort_' . $list_id => 'title-asc' ) );
		$this->assertSame( self::TITLES_AZ, $this->titles( $this->render( $list_id ) ) );

		$this->request( array( 'w4pl_sort_' . $list_id => 'title-desc' ) );
		$this->assertSame( array_reverse( self::TITLES_AZ ), $this->titles( $this->render( $list_id ) ) );
	}

	public function test_no_parameter_keeps_the_lists_own_order() {
		$list_id = $this->make_list( $this->options( array( 'visitor_sort' => array( 'title-asc' ) ) ) );

		$this->assertSame( self::TITLES_NEWEST, $this->titles( $this->render( $list_id ) ) );
	}

	/**
	 * @dataProvider provide_rejected_values
	 *
	 * @param mixed $value Request value.
	 */
	public function test_values_outside_the_offered_set_are_ignored( $value ) {
		$list_id = $this->make_list( $this->options( array( 'visitor_sort' => array( 'title-asc', 'date-asc' ) ) ) );
		$this->request( array( 'w4pl_sort_' . $list_id => $value ) );

		$this->assertSame( self::TITLES_NEWEST, $this->titles( $this->render( $list_id ) ) );
	}

	public function provide_rejected_values() {
		return array(
			'registered but not offered' => array( 'title-desc' ),
			'case variant'               => array( 'TITLE-ASC' ),
			'trailing space'             => array( 'title-asc ' ),
			'sanitize_key would fix it'  => array( 'Title-Asc' ),
			'raw orderby'                => array( 'title' ),
			'random'                     => array( 'rand' ),
			'sql'                        => array( "title-asc' OR 1=1" ),
			'array'                      => array( array( 'title-asc' ) ),
			'empty'                      => array( '' ),
		);
	}

	/**
	 * $_GET arrives slashed (wp_magic_quotes), so the value is unslashed once
	 * and then compared as-is: a backslash the visitor typed survives and the
	 * token no longer matches.
	 */
	public function test_request_value_is_compared_after_unslashing_only() {
		$offered = W4PL_Helper_Visitor_Sort::offered(
			$this->options( array( 'visitor_sort' => array( 'title-asc' ) ) )
		);

		$_GET['w4pl_sort_7'] = wp_slash( 'title-asc' );
		$this->assertSame( 'title-asc', W4PL_Helper_Visitor_Sort::requested( 7, $offered ) );

		$_GET['w4pl_sort_7'] = wp_slash( 'title\\-asc' );
		$this->assertSame( '', W4PL_Helper_Visitor_Sort::requested( 7, $offered ) );

		unset( $_GET['w4pl_sort_7'] );
	}

	public function test_unknown_stored_tokens_are_dropped() {
		$this->assertSame(
			array( 'date-asc', 'title-asc' ),
			W4PL_Helper_Visitor_Sort::filter_tokens( array( 'title-asc', 'rand', 'date-asc', 'Title-Asc', 5, array( 'x' ), 'title-asc' ) ),
			'Known tokens only, in registry order, once each.'
		);
		$this->assertSame( array(), W4PL_Helper_Visitor_Sort::filter_tokens( 'title-asc' ) );
	}

	public function test_save_keeps_only_known_tokens() {
		$options = W4PL_Admin_Lists_Metaboxes::sanitize_options(
			array(
				'list_type'    => 'posts',
				'visitor_sort' => array( 'title-asc', '<script>', 'rand-asc', 'modified-desc' ),
			)
		);

		$this->assertSame( array( 'title-asc', 'modified-desc' ), $options['visitor_sort'] );
	}

	public function test_filter_can_add_a_choice_but_not_an_invalid_one() {
		$filter = function ( $choices ) {
			$choices['menu-order'] = array(
				'orderby' => 'menu_order',
				'order'   => 'ASC',
				'label'   => 'Menu order',
			);
			$choices['bad-orderby'] = array(
				'orderby' => 'post_title; DROP',
				'order'   => 'ASC',
				'label'   => 'Bad',
			);
			$choices['bad-order'] = array(
				'orderby' => 'title',
				'order'   => 'asc',
				'label'   => 'Lowercase order',
			);
			$choices['Bad Token'] = array(
				'orderby' => 'title',
				'order'   => 'ASC',
				'label'   => 'Bad token',
			);
			$choices['no-label'] = array(
				'orderby' => 'title',
				'order'   => 'ASC',
			);
			return $choices;
		};
		add_filter( 'w4pl/visitor_sort_options', $filter );

		$registry = W4PL_Helper_Visitor_Sort::registry();

		remove_filter( 'w4pl/visitor_sort_options', $filter );

		$this->assertArrayHasKey( 'menu-order', $registry );
		$this->assertArrayNotHasKey( 'bad-orderby', $registry );
		$this->assertArrayNotHasKey( 'bad-order', $registry );
		$this->assertArrayNotHasKey( 'Bad Token', $registry );
		$this->assertArrayNotHasKey( 'no-label', $registry );
	}

	/**
	 * Switching away from a meta_value order must not change which posts are
	 * listed, only their order: the list's meta_key stays in the query.
	 */
	public function test_visitor_order_keeps_the_lists_meta_key() {
		$options = $this->options(
			array(
				'orderby'          => 'meta_value',
				'orderby_meta_key' => 'rank',
				'visitor_sort'     => array( 'title-asc' ),
			)
		);

		$options['id'] = $this->make_list( $options );
		$this->request( array( 'w4pl_sort_' . $options['id'] => 'title-asc' ) );

		$list = W4PL_List_Factory::get_list( apply_filters( 'w4pl/pre_get_options', $options ) );
		$list->get_html();

		$this->assertSame( 'title', $list->posts_args['orderby'] );
		$this->assertSame( 'ASC', $list->posts_args['order'] );
		$this->assertSame( 'rank', $list->posts_args['meta_key'] );
	}

	/* ---------------------------------------------------------------------
	 * Two lists on one page.
	 * ------------------------------------------------------------------ */

	public function test_each_list_reads_only_its_own_parameter() {
		$options = $this->options( array( 'visitor_sort' => array( 'title-asc', 'date-asc' ) ) );
		$id_a    = $this->make_list( $options );
		$id_b    = $this->make_list( $options );

		$this->request( array( 'w4pl_sort_' . $id_a => 'title-asc' ) );

		$this->assertSame( self::TITLES_AZ, $this->titles( $this->render( $id_a ) ) );
		$this->assertSame( self::TITLES_NEWEST, $this->titles( $this->render( $id_b ) ) );
	}

	public function test_two_lists_on_one_page_each_get_their_own_form() {
		$options = $this->options( array( 'visitor_sort' => array( 'title-asc', 'date-asc' ) ) );
		$id_a    = $this->make_list( $options );
		$id_b    = $this->make_list( $options );

		$this->request( array( 'w4pl_sort_' . $id_b => 'date-asc' ) );

		$html = $this->render( $id_a ) . $this->render( $id_b );

		$this->assertSame( 2, substr_count( $html, '<form class="w4pl-sort"' ) );
		$this->assertStringContainsString( '<select id="w4pl-sort-' . $id_a . '" name="w4pl_sort_' . $id_a . '">', $html );
		$this->assertStringContainsString( '<select id="w4pl-sort-' . $id_b . '" name="w4pl_sort_' . $id_b . '">', $html );
		// A's form carries B's sort along, so sorting A keeps B sorted.
		$this->assertStringContainsString( '<input type="hidden" name="w4pl_sort_' . $id_b . '" value="date-asc" />', $html );
		$this->assertCount( 1, wp_scripts()->queue, 'One shared script for both.' );
	}

	/* ---------------------------------------------------------------------
	 * The control.
	 * ------------------------------------------------------------------ */

	public function test_control_renders_above_the_list_by_default() {
		$list_id = $this->make_list( $this->options( array( 'visitor_sort' => array( 'date-desc', 'title-asc' ) ) ) );

		$html = $this->render( $list_id );

		$this->assertMatchesRegularExpression(
			'/<div id="w4pl-inner-' . $list_id . '" class="w4pl-inner">\s*<form class="w4pl-sort" method="get"/',
			$html
		);
		$this->assertStringContainsString( '<label for="w4pl-sort-' . $list_id . '">Sort by</label>', $html );
		$this->assertStringContainsString( 'name="w4pl_sort_' . $list_id . '"', $html );
		$this->assertStringContainsString( '<button type="submit" class="w4pl-sort-submit">Sort</button>', $html );
		$this->assertTrue( wp_script_is( 'w4pl-ajax-nav', 'enqueued' ) );
	}

	public function test_own_order_stands_in_for_default_when_offered() {
		$list_id = $this->make_list( $this->options( array( 'visitor_sort' => array( 'date-desc', 'title-asc' ) ) ) );

		$html = $this->render( $list_id );

		$this->assertStringNotContainsString( 'Default order', $html );
		$this->assertMatchesRegularExpression( "/<option value=\"date-desc\" selected='selected'>Newest first<\/option>/", $html );
	}

	public function test_default_entry_when_own_order_is_not_offered() {
		$list_id = $this->make_list( $this->options( array( 'visitor_sort' => array( 'title-asc', 'title-desc' ) ) ) );

		$html = $this->render( $list_id );
		$this->assertMatchesRegularExpression( "/<option value=\"\" selected='selected'>Default order<\/option>/", $html );

		$this->request( array( 'w4pl_sort_' . $list_id => 'title-desc' ) );
		$html = $this->render( $list_id );
		$this->assertMatchesRegularExpression( "/<option value=\"title-desc\" selected='selected'>Title: Z to A<\/option>/", $html );
		$this->assertStringNotContainsString( "value=\"\" selected='selected'", $html );
	}

	public function test_control_renders_only_the_offered_choices() {
		$list_id = $this->make_list( $this->options( array( 'visitor_sort' => array( 'title-asc', 'rand' ) ) ) );

		$html = $this->render( $list_id );

		preg_match_all( '/<option value="([^"]*)"/', $html, $m );
		$this->assertSame( array( '', 'title-asc' ), $m[1] );
	}

	public function test_sort_tag_places_the_control_and_takes_a_label() {
		$list_id = $this->make_list(
			$this->options(
				array(
					'visitor_sort' => array( 'title-asc' ),
					'template'     => self::LOOP . '<p>[sort label="Order <by>"]</p>',
				)
			)
		);

		$html = $this->render( $list_id );

		$this->assertMatchesRegularExpression( '/<\/ul><p><form class="w4pl-sort"/', $html );
		$this->assertStringContainsString( '>Order &lt;by&gt;</label>', $html );
		$this->assertSame( 1, substr_count( $html, '<form' ) );
		$this->assertStringNotContainsString( '[sort', $html );
		$this->assertStringNotContainsString( 'w4pl-sort-control', $html, 'No placeholder may leak.' );
	}

	public function test_sort_tag_used_twice_or_in_the_loop_yields_one_control() {
		$list_id = $this->make_list(
			$this->options(
				array(
					'visitor_sort' => array( 'title-asc' ),
					'template'     => '[sort]<ul>[posts]<li>[post_title]</li>[sort][/posts]</ul>[sort]',
				)
			)
		);

		$html = $this->render( $list_id );

		$this->assertSame( 1, substr_count( $html, '<form' ) );
		$this->assertStringNotContainsString( 'w4pl-sort-control', $html );
		$this->assertCount( 6, $this->titles( $html ) );
	}

	public function test_no_results_renders_no_control() {
		$list_id = $this->make_list(
			$this->options(
				array(
					'visitor_sort' => array( 'title-asc' ),
					'post__in'     => '999999',
					'template'     => '[sort]' . self::LOOP,
				)
			)
		);

		$html = $this->render( $list_id );

		$this->assertStringNotContainsString( '<form', $html );
		$this->assertStringNotContainsString( 'w4pl-sort-control', $html );
		$this->assertFalse( wp_script_is( 'w4pl-ajax-nav', 'enqueued' ) );
	}

	public function test_ajax_follows_nav_unless_the_tag_says_otherwise() {
		$ajax_nav = self::LOOP . '[nav type="plain" ajax="1"]';
		$paged    = array(
			'visitor_sort'   => array( 'title-asc' ),
			'posts_per_page' => 2,
		);

		$list_id = $this->make_list( $this->options( $paged + array( 'template' => $ajax_nav ) ) );
		$this->assertStringContainsString( ' data-ajax="1">', $this->render( $list_id ) );

		$list_id = $this->make_list( $this->options( $paged + array( 'template' => self::LOOP . '[nav type="plain"]' ) ) );
		$this->assertStringNotContainsString( 'data-ajax', $this->render( $list_id ) );

		$list_id = $this->make_list( $this->options( $paged + array( 'template' => '[sort ajax="0"]' . $ajax_nav ) ) );
		$this->assertStringNotContainsString( 'data-ajax', $this->render( $list_id ) );

		$list_id = $this->make_list( $this->options( $paged + array( 'template' => '[sort ajax="1"]' . self::LOOP ) ) );
		$this->assertStringContainsString( ' data-ajax="1">', $this->render( $list_id ) );
	}

	/**
	 * A GET form drops the query string of its action, so every other
	 * parameter on the page must ride along as a hidden field - except this
	 * list's page and sort, which the form itself resets.
	 */
	public function test_form_carries_other_parameters_and_resets_its_own() {
		$list_id = $this->make_list(
			$this->options(
				array(
					'visitor_sort'   => array( 'title-asc' ),
					'posts_per_page' => 2,
				)
			)
		);
		$other   = $list_id + 1000;

		$this->request(
			array(
				'utm_source'               => 'a b.c',
				'page' . $other            => '3',
				'w4pl_sort_' . $other      => 'date-asc',
				'page' . $list_id          => '2',
				'w4pl_sort_' . $list_id    => 'title-asc',
			)
		);

		$html = $this->render( $list_id );

		$this->assertStringContainsString( '<input type="hidden" name="utm_source" value="a b.c" />', $html );
		$this->assertStringContainsString( '<input type="hidden" name="page' . $other . '" value="3" />', $html );
		$this->assertStringContainsString( '<input type="hidden" name="w4pl_sort_' . $other . '" value="date-asc" />', $html );
		$this->assertStringNotContainsString( 'name="page' . $list_id . '"', $html );
		$this->assertStringNotContainsString( 'type="hidden" name="w4pl_sort_' . $list_id . '"', $html );
		$this->assertMatchesRegularExpression( '/action="[^"?]*"/', $html, 'The action carries no query string.' );
	}

	public function test_hostile_query_string_is_escaped() {
		$list_id = $this->make_list( $this->options( array( 'visitor_sort' => array( 'title-asc' ) ) ) );

		$_SERVER['REQUEST_URI'] = '/?x%22%3E%3Cscript%3E=%22%3E%3Cimg+src%3Dx+onerror%3Dalert(1)%3E';

		$html = $this->render( $list_id );

		$this->assertStringNotContainsString( '<script', $html );
		$this->assertStringNotContainsString( '<img', $html );
		$this->assertStringContainsString( 'name="x&quot;&gt;&lt;script&gt;" value="&quot;&gt;&lt;img src=x onerror=alert(1)&gt;"', $html );
	}

	/**
	 * The form submits to the URL as requested: the page's own /page/N/ is
	 * kept (sorting a sidebar list must not leave archive page 3), and keys
	 * reach the hidden fields verbatim - parse_str() would turn "foo.bar"
	 * into "foo_bar" and "a[]" into "a[0]".
	 */
	public function test_form_submits_to_the_requested_path_with_keys_verbatim() {
		$list_id = $this->make_list( $this->options( array( 'visitor_sort' => array( 'title-asc' ) ) ) );

		$_SERVER['REQUEST_URI'] = '/category/news/page/3/?foo.bar=1&a%5B%5D=1&a%5B%5D=2&w4pl_sort_' . $list_id . '=title-asc#frag';

		$html = $this->render( $list_id );

		$this->assertStringContainsString( 'action="/category/news/page/3/"', $html );
		$this->assertStringContainsString( '<input type="hidden" name="foo.bar" value="1" />', $html );
		$this->assertSame( 2, substr_count( $html, '<input type="hidden" name="a[]"' ) );
		$this->assertStringNotContainsString( 'frag', $html );
	}

	public function test_protocol_relative_path_cannot_point_the_form_elsewhere() {
		$list_id = $this->make_list( $this->options( array( 'visitor_sort' => array( 'title-asc' ) ) ) );

		$_SERVER['REQUEST_URI'] = '//evil.example/x?y=1';

		$html = $this->render( $list_id );

		$this->assertStringContainsString( 'action="/evil.example/x"', $html );
		$this->assertStringNotContainsString( 'action="//', $html );
	}

	/**
	 * The list editor's live preview renders through admin-ajax.php, where a
	 * submit would navigate the editor away. The control shows, disabled.
	 */
	public function test_control_is_disabled_in_admin_previews() {
		$list_id = $this->make_list( $this->options( array( 'visitor_sort' => array( 'title-asc' ) ) ) );

		set_current_screen( 'edit.php' );
		$html = $this->render( $list_id );
		set_current_screen( 'front' );

		$this->assertMatchesRegularExpression( '/<select id="w4pl-sort-\d+" name="w4pl_sort_\d+" disabled="disabled">/', $html );
		$this->assertStringContainsString( '<button type="submit" class="w4pl-sort-submit" disabled="disabled">', $html );

		$html = $this->render( $list_id );
		$this->assertStringNotContainsString( 'disabled', $html );
	}

	/* ---------------------------------------------------------------------
	 * Pagination keeps the sort.
	 * ------------------------------------------------------------------ */

	public function test_pagination_links_carry_the_sort() {
		$list_id = $this->make_list(
			$this->options(
				array(
					'visitor_sort'   => array( 'title-asc' ),
					'posts_per_page' => 2,
					'template'       => self::LOOP . '[nav type="plain" ajax="1"]',
				)
			)
		);

		$this->request( array( 'w4pl_sort_' . $list_id => 'title-asc' ) );
		$html = $this->render( $list_id );

		$this->assertSame( array_slice( self::TITLES_AZ, 0, 2 ), $this->titles( $html ) );
		$this->assertMatchesRegularExpression(
			'/class="page-numbers" href="[^"]*w4pl_sort_' . $list_id . '=title-asc[^"]*page' . $list_id . '=2"/',
			$html
		);
	}

	public function test_sorted_page_two_renders_server_side() {
		$list_id = $this->make_list(
			$this->options(
				array(
					'visitor_sort'   => array( 'title-asc' ),
					'posts_per_page' => 2,
					'template'       => self::LOOP . '[nav type="plain" ajax="1"]',
				)
			)
		);

		$this->request(
			array(
				'w4pl_sort_' . $list_id => 'title-asc',
				'page' . $list_id       => '2',
			)
		);

		$this->assertSame( array_slice( self::TITLES_AZ, 2, 2 ), $this->titles( $this->render( $list_id ) ) );
	}

	/* ---------------------------------------------------------------------
	 * Editor.
	 * ------------------------------------------------------------------ */

	public function test_editor_field_only_for_posts_lists() {
		$fields = apply_filters( 'w4pl/list_edit_form_fields', array(), apply_filters( 'w4pl/pre_get_options', array( 'list_type' => 'posts' ) ) );
		$this->assertArrayHasKey( 'visitor_sort', $fields );
		$this->assertSame( 'w4pl[visitor_sort]', $fields['visitor_sort']['name'] );
		$this->assertSame( array_keys( W4PL_Config::visitor_sort_options() ), array_keys( $fields['visitor_sort']['option'] ) );

		$fields = apply_filters( 'w4pl/list_edit_form_fields', array(), apply_filters( 'w4pl/pre_get_options', array( 'list_type' => 'terms' ) ) );
		$this->assertArrayNotHasKey( 'visitor_sort', $fields );
	}

	public function test_sort_tag_is_registered() {
		$this->assertArrayHasKey( 'sort', w4pl_get_shortcodes() );
	}

	public function test_save_warns_about_a_sort_tag_with_nothing_ticked() {
		$warning = 'no "Visitor sorting" order is ticked';

		$warnings = W4PL_Admin_Validation::template_warnings( $this->options( array( 'template' => '[sort]' . self::LOOP ) ) );
		$this->assertStringContainsString( $warning, implode( "\n", $warnings ) );

		$warnings = W4PL_Admin_Validation::template_warnings(
			$this->options(
				array(
					'template'     => '[sort]' . self::LOOP,
					'visitor_sort' => array( 'title-asc' ),
				)
			)
		);
		$this->assertStringNotContainsString( $warning, implode( "\n", $warnings ) );
	}

	/* ---------------------------------------------------------------------
	 * "Maximum items" / "Offset": no visitor sorting at all.
	 *
	 * Both count posts in the query's order, so a visitor's order would
	 * change which posts are shown ("5 latest" sorted A to Z becoming the
	 * first 5 by title). Such a list renders in its configured order, with
	 * no dropdown, whatever is ticked and whatever the URL says.
	 * ------------------------------------------------------------------ */

	/**
	 * @dataProvider provide_limit_and_offset_values
	 *
	 * @param mixed $value    Stored option value.
	 * @param bool  $expected Whether it counts as set.
	 */
	public function test_set_means_what_it_means_to_the_query( $value, $expected ) {
		$this->assertSame( $expected, W4PL_Helper_Visitor_Sort::is_limited( array( 'limit' => $value ) ), 'limit' );
		$this->assertSame( $expected, W4PL_Helper_Visitor_Sort::is_limited( array( 'offset' => $value ) ), 'offset' );
	}

	public function provide_limit_and_offset_values() {
		return array(
			'empty string'  => array( '', false ),
			'string zero'   => array( '0', false ),
			'integer zero'  => array( 0, false ),
			'null'          => array( null, false ),
			'string number' => array( '5', true ),
			'integer'       => array( 5, true ),
			'negative'      => array( '-1', true ),
		);
	}

	public function test_missing_limit_and_offset_are_not_set() {
		$this->assertFalse( W4PL_Helper_Visitor_Sort::is_limited( array() ) );
	}

	public function test_list_with_maximum_items_ignores_the_parameter_and_renders_no_dropdown() {
		$list_id = $this->make_list(
			$this->options(
				array(
					'limit'        => '4',
					'visitor_sort' => array( 'title-asc', 'date-asc' ),
				)
			)
		);
		$before  = $this->render( $list_id );

		$this->request( array( 'w4pl_sort_' . $list_id => 'title-asc' ) );
		$after = $this->render( $list_id );

		$this->assertSame( $before, $after, 'The parameter must change nothing.' );
		$this->assertSame( array_slice( self::TITLES_NEWEST, 0, 4 ), $this->titles( $after ) );
		$this->assertStringNotContainsString( '<form', $after );
		$this->assertStringNotContainsString( 'w4pl-sort', $after );
		$this->assertStringNotContainsString( 'w4pl_sort_', $after );
		$this->assertFalse( wp_script_is( 'w4pl-ajax-nav', 'enqueued' ) );
	}

	public function test_list_with_offset_ignores_the_parameter_and_renders_no_dropdown() {
		$list_id = $this->make_list(
			$this->options(
				array(
					'offset'       => '2',
					'visitor_sort' => array( 'title-asc', 'date-asc' ),
				)
			)
		);
		$before  = $this->render( $list_id );

		$this->request( array( 'w4pl_sort_' . $list_id => 'title-asc' ) );
		$after = $this->render( $list_id );

		$this->assertSame( $before, $after, 'The parameter must change nothing.' );
		$this->assertSame( array_slice( self::TITLES_NEWEST, 2 ), $this->titles( $after ) );
		$this->assertStringNotContainsString( '<form', $after );
		$this->assertStringNotContainsString( 'w4pl-sort', $after );
		$this->assertFalse( wp_script_is( 'w4pl-ajax-nav', 'enqueued' ) );
	}

	public function test_limited_list_never_hands_the_visitors_order_to_the_query() {
		$options = $this->options(
			array(
				'limit'        => '4',
				'visitor_sort' => array( 'title-asc' ),
			)
		);

		$options['id'] = $this->make_list( $options );
		$this->request( array( 'w4pl_sort_' . $options['id'] => 'title-asc' ) );

		$list = W4PL_List_Factory::get_list( apply_filters( 'w4pl/pre_get_options', $options ) );
		$list->get_html();

		$this->assertSame( 'date', $list->posts_args['orderby'] );
		$this->assertSame( 'DESC', $list->posts_args['order'] );
		$this->assertSame( array(), W4PL_Helper_Visitor_Sort::offered( $options ) );
		$this->assertArrayHasKey( 'title-asc', W4PL_Helper_Visitor_Sort::configured( $options ), 'The ticks themselves are untouched.' );
	}

	/**
	 * The owner ticked orders and placed the tag, then set a limit: the tag
	 * must render nothing, not print as "[sort]".
	 */
	public function test_limited_list_renders_nothing_for_the_sort_tag() {
		$list_id = $this->make_list(
			$this->options(
				array(
					'limit'        => '4',
					'visitor_sort' => array( 'title-asc' ),
					'template'     => '[sort label="Order"]<ul>[posts]<li>[post_title]</li>[sort][/posts]</ul>',
				)
			)
		);

		$html = $this->render( $list_id );

		$this->assertCount( 4, $this->titles( $html ) );
		$this->assertStringNotContainsString( '[sort', $html );
		$this->assertStringNotContainsString( '<form', $html );
		$this->assertStringNotContainsString( 'w4pl-sort', $html, 'No control, no placeholder.' );
	}

	/**
	 * The AJAX path is the same server render fetched by the script, so a
	 * sorted deep link to page 2 must come back in the configured order.
	 */
	public function test_limited_list_keeps_its_order_on_a_paged_ajax_request() {
		$list_id = $this->make_list(
			$this->options(
				array(
					'limit'          => '5',
					'posts_per_page' => 2,
					'visitor_sort'   => array( 'title-asc' ),
					'template'       => self::LOOP . '[nav type="plain" ajax="1"]',
				)
			)
		);

		$this->request(
			array(
				'w4pl_sort_' . $list_id => 'title-asc',
				'page' . $list_id       => '2',
			)
		);
		$html = $this->render( $list_id );

		$this->assertSame( array_slice( self::TITLES_NEWEST, 2, 2 ), $this->titles( $html ) );
		$this->assertStringNotContainsString( '<form', $html );
		$this->assertStringContainsString( 'ajax-navigation', $html, 'Pagination itself still works.' );
	}

	/**
	 * '0' is "no limit" / "no offset" to the query, so it must not switch
	 * sorting off either.
	 */
	public function test_zero_limit_and_offset_leave_sorting_on() {
		$list_id = $this->make_list(
			$this->options(
				array(
					'limit'        => '0',
					'offset'       => '0',
					'visitor_sort' => array( 'title-asc' ),
				)
			)
		);

		$this->request( array( 'w4pl_sort_' . $list_id => 'title-asc' ) );
		$html = $this->render( $list_id );

		$this->assertSame( self::TITLES_AZ, $this->titles( $html ) );
		$this->assertStringContainsString( '<form class="w4pl-sort"', $html );
	}

	public function test_editor_says_sorting_is_unavailable_on_a_limited_list() {
		$note = 'Visitor sorting is off for this list';

		foreach ( array( 'limit', 'offset' ) as $field ) {
			$options = apply_filters(
				'w4pl/pre_get_options',
				array(
					'list_type'    => 'posts',
					$field         => '5',
					'visitor_sort' => array( 'title-asc' ),
				)
			);
			$fields  = apply_filters( 'w4pl/list_edit_form_fields', array(), $options );

			$this->assertStringContainsString( $note, $fields['visitor_sort']['input_before'], $field );
			$this->assertCount( 6, $fields['visitor_sort']['option'], 'The checkboxes stay, so the ticks are posted and kept.' );
		}

		$fields = apply_filters( 'w4pl/list_edit_form_fields', array(), apply_filters( 'w4pl/pre_get_options', array( 'list_type' => 'posts' ) ) );
		$this->assertArrayNotHasKey( 'input_before', $fields['visitor_sort'], 'No note on a list without a limit or offset.' );
	}

	public function test_saving_a_limited_list_keeps_the_ticked_orders() {
		$options = W4PL_Admin_Lists_Metaboxes::sanitize_options(
			array(
				'list_type'    => 'posts',
				'limit'        => '5',
				'visitor_sort' => array( 'title-asc', 'date-asc' ),
			)
		);

		$this->assertSame( array( 'date-asc', 'title-asc' ), $options['visitor_sort'], 'Clearing the limit later brings sorting back.' );
	}

	/**
	 * The note next to the checkboxes replaces a save-time warning; and a
	 * [sort] tag on a limited list with ticked orders is not "nothing ticked".
	 */
	public function test_limited_list_saves_without_sort_warnings() {
		$warnings = W4PL_Admin_Validation::template_warnings(
			$this->options(
				array(
					'limit'        => '5',
					'template'     => '[sort]' . self::LOOP,
					'visitor_sort' => array( 'title-asc' ),
				)
			)
		);

		$this->assertSame( array(), $warnings );
	}
}
