<?php
/**
 * Validation of the list options the terms and users queries are built from.
 *
 * Each option is pinned twice: in the query (a value saved by an older version
 * must still produce a sensible query) and at save (sanitize_options stores
 * only values the editor UI can produce).
 *
 * @package W4_Post_List
 */

require_once __DIR__ . '/class-w4pl-snapshot-testcase.php';

class QueryOptionValidationTest extends W4PL_Snapshot_TestCase {

	const MARKER = '(SELECT 4242)';

	/**
	 * SQL statements sent by the list queries during the current test.
	 *
	 * @var array
	 */
	protected $requests = array();

	public static function wpSetUpBeforeClass( $factory ) {
		parent::wpSetUpBeforeClass( $factory );

		// Admin classes are only included under is_admin().
		require_once dirname( __DIR__ ) . '/admin/class-admin-lists-metaboxes.php';
	}

	public function set_up() {
		parent::set_up();

		$this->requests = array();
		add_filter( 'w4pl_query_request', array( $this, 'capture_request' ) );

		// Lists render for logged-out visitors.
		wp_set_current_user( 0 );
	}

	public function capture_request( $request ) {
		$this->requests[] = $request;
		return $request;
	}

	protected function all_requests() {
		return implode( "\n", $this->requests );
	}

	public function test_unknown_terms_orderby_falls_back_to_term_id() {
		$html = $this->render_list(
			array(
				'list_type'      => 'terms',
				'terms_taxonomy' => 'category',
				'terms_orderby'  => self::MARKER,
			)
		);

		$this->assertNotEmpty( $this->requests, 'The terms query must have run.' );
		$this->assertStringNotContainsString( '4242', $this->all_requests() );

		// A rejected value falls back to a real column instead of breaking the list.
		$this->assertStringContainsString( 'ORDER BY TB.term_id', $this->all_requests() );
		$this->assertStringContainsString( 'Alpha', $html );
	}

	public function test_unknown_terms_order_falls_back_to_asc() {
		$this->render_list(
			array(
				'list_type'      => 'terms',
				'terms_taxonomy' => 'category',
				'terms_orderby'  => 'name',
				'terms_order'    => ', ' . self::MARKER,
			)
		);

		$this->assertNotEmpty( $this->requests );
		$this->assertStringNotContainsString( '4242', $this->all_requests() );
		$this->assertStringContainsString( 'ORDER BY TB.name ASC', $this->all_requests() );
	}

	public function test_unknown_users_order_falls_back_to_asc() {
		$this->render_list(
			array(
				'list_type'     => 'users',
				'users_orderby' => 'ID',
				'users_order'   => ', ' . self::MARKER,
			)
		);

		$this->assertNotEmpty( $this->requests );
		$this->assertStringNotContainsString( '4242', $this->all_requests() );
		$this->assertStringContainsString( 'ORDER BY ID ASC', $this->all_requests() );
	}

	public function test_unknown_terms_taxonomy_matches_nothing() {
		$html = $this->render_list(
			array(
				'list_type'      => 'terms',
				'terms_taxonomy' => "nope' OR 'x'='x",
			)
		);

		$this->assertNotEmpty( $this->requests );
		$this->assertStringNotContainsString( 'Alpha', $html );
	}

	public function test_terms_name_search_is_matched_literally() {
		$html = $this->render_list(
			array(
				'list_type'        => 'terms',
				'terms_taxonomy'   => 'category',
				'terms_name__like' => "x'OR(1=1)OR'x",
			)
		);

		$this->assertNotEmpty( $this->requests );
		$this->assertStringNotContainsString( 'Alpha', $html );
	}

	public function test_users_display_name_search_is_matched_literally() {
		$html = $this->render_list(
			array(
				'list_type'                => 'users',
				'users_display_name__like' => "x'OR(1=1)OR'x",
			)
		);

		$this->assertNotEmpty( $this->requests );
		$this->assertStringNotContainsString( 'Ann Author', $html );
	}

	public function test_name_search_still_matches() {
		$html = $this->render_list(
			array(
				'list_type'        => 'terms',
				'terms_taxonomy'   => 'category',
				'terms_name__like' => 'alp',
			)
		);

		$this->assertStringContainsString( 'Alpha', $html );
		$this->assertStringNotContainsString( 'Beta', $html );
	}

	public function test_terms_query_normalizes_order_case() {
		$query = new W4PL_Terms_Query(
			array(
				'taxonomy' => 'category',
				'orderby'  => 'count',
				'order'    => 'desc',
			)
		);
		$query->query();

		$this->assertStringContainsString( 'ORDER BY TT1.count DESC', $query->request );
	}

	public function test_sanitize_options_drops_order_values_the_ui_cannot_produce() {
		$options = W4PL_Admin_Lists_Metaboxes::sanitize_options(
			array(
				'terms_orderby'  => self::MARKER,
				'terms_order'    => ', ' . self::MARKER,
				'users_orderby'  => 'ID, ' . self::MARKER,
				'users_order'    => ', ' . self::MARKER,
				'terms_taxonomy' => "category' OR 1=1 -- ",
			)
		);

		foreach ( array( 'terms_orderby', 'terms_order', 'users_orderby', 'users_order', 'terms_taxonomy' ) as $key ) {
			$this->assertArrayNotHasKey( $key, $options, "$key must not be stored." );
		}
	}

	public function test_sanitize_options_keeps_values_the_ui_produces() {
		$input = array(
			'terms_orderby'  => 'custom',
			'terms_order'    => 'DESC',
			'users_orderby'  => 'user_registered',
			'users_order'    => 'ASC',
			'terms_taxonomy' => 'post_tag',
		);

		$options = W4PL_Admin_Lists_Metaboxes::sanitize_options( $input );

		foreach ( $input as $key => $value ) {
			$this->assertSame( $value, $options[ $key ], $key );
		}
	}
}
