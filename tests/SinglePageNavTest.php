<?php
/**
 * [nav type="plain"] / [nav type="list"] on a list that fits on one page.
 *
 * paginate_links() returns null when there is nothing to paginate. Through
 * 3.0.8 W4PL_List::navigation() passed that null on, and every list type
 * handed it to str_replace() as the replacement - a "Passing null to
 * parameter #2" deprecation on PHP 8.1+, printed into the page on sites that
 * display errors.
 *
 * @package W4_Post_List
 */

require_once __DIR__ . '/class-w4pl-snapshot-testcase.php';

class SinglePageNavTest extends W4PL_Snapshot_TestCase {

	/**
	 * Deprecations raised while rendering.
	 *
	 * @var string[]
	 */
	private $deprecations = array();

	/**
	 * Whether this test installed its error handler.
	 *
	 * @var bool
	 */
	private $handler_set = false;

	public function set_up() {
		parent::set_up();

		wp_set_current_user( 0 );

		$this->deprecations = array();

		// Record deprecations; hand everything else to the handler that was
		// there before, so PHPUnit still sees warnings and notices.
		$previous = null;
		$previous = set_error_handler(
			function ( $errno, $errstr, $errfile = '', $errline = 0 ) use ( &$previous ) {
				if ( E_DEPRECATED === $errno || E_USER_DEPRECATED === $errno ) {
					$this->deprecations[] = $errstr;
					return true;
				}

				return $previous ? call_user_func( $previous, $errno, $errstr, $errfile, $errline ) : false;
			}
		);

		$this->handler_set = true;
	}

	public function tear_down() {
		if ( $this->handler_set ) {
			restore_error_handler();
			$this->handler_set = false;
		}

		parent::tear_down();
	}

	/**
	 * @return array
	 */
	public function provide_nav_types() {
		return array(
			'plain' => array( 'plain' ),
			'list'  => array( 'list' ),
		);
	}

	/**
	 * @dataProvider provide_nav_types
	 *
	 * @param string $type [nav] type attribute.
	 */
	public function test_navigation_returns_an_empty_string_for_a_single_page( $type ) {
		$list = W4PL_List_Factory::get_list(
			apply_filters(
				'w4pl/pre_get_options',
				array(
					'id'        => 1,
					'list_type' => 'posts',
				)
			)
		);

		$this->assertSame( '', $list->navigation( 1, 1, array( 'type' => $type ) ) );
	}

	/**
	 * @dataProvider provide_nav_types
	 *
	 * @param string $type [nav] type attribute.
	 */
	public function test_one_page_list_renders_without_a_deprecation( $type ) {
		// Six fixture posts, ten per page: one page, so no pagination links.
		$html = $this->render_list(
			array(
				'list_type'      => 'posts',
				'post_type'      => array( 'post' ),
				'posts_per_page' => 10,
				'template'       => '<ul>[posts]<li>[post_title]</li>[/posts]</ul>[nav type="' . $type . '"]',
			)
		);

		// Only this one: a core deprecation on a newer PHP is not this test's business.
		$this->assertSame( array(), array_values( preg_grep( '/str_replace\(\)/', $this->deprecations ) ) );
		$this->assertStringContainsString( '<li>Hello from the archive</li></ul>', $html, 'Sanity: the list rendered.' );
		$this->assertStringNotContainsString( '[nav', $html, 'The tag is replaced by nothing.' );
		$this->assertStringNotContainsString( 'navigation', $html );
	}
}
