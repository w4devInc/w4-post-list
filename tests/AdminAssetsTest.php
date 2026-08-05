<?php
/**
 * Admin-only assets must not be registered on front-end requests.
 *
 * Three styles and two scripts exist purely for the list editor and the
 * documentation page. They were registered on every front-end request as well,
 * where nothing has ever enqueued them - dead weight on ~2000 sites, and the
 * last jQuery-dependent registrations the front end carried.
 *
 * These tests pin the split both ways: gone from the front end, byte-identical
 * in wp-admin, with the new front-end AJAX nav script unaffected.
 *
 * @package W4_Post_List
 */

class AdminAssetsTest extends WP_UnitTestCase {

	/**
	 * Styles registered by register_admin_scripts(): handle => src.
	 */
	const ADMIN_STYLES = array(
		'w4pl_form'                => 'assets/css/form.css',
		'w4pl_list_editor'         => 'assets/css/list-editor.css',
		'w4pl-admin-documentation' => 'assets/css/admin-documentation.css',
	);

	/**
	 * Scripts registered by register_admin_scripts(): handle => src.
	 */
	const ADMIN_SCRIPTS = array(
		'w4pl_form'        => 'assets/js/form.js',
		'w4pl_list_editor' => 'assets/js/list-editor.js',
	);

	const NAV_HANDLE = 'w4pl-ajax-nav';

	public function set_up() {
		parent::set_up();

		// Asset registries are globals and backupGlobals is off: start clean so
		// every "registered" assertion describes this test only. Re-creating
		// them fires wp_default_scripts/styles, so core handles (jquery, ...)
		// are present exactly as on a real request.
		$GLOBALS['wp_scripts'] = null;
		$GLOBALS['wp_styles']  = null;

		wp_set_current_user( 0 );
	}

	public function tear_down() {
		$GLOBALS['wp_scripts'] = null;
		$GLOBALS['wp_styles']  = null;

		parent::tear_down();
	}

	/* ---------------------------------------------------------------------
	 * Front end: gone.
	 * ------------------------------------------------------------------ */

	public function test_admin_only_styles_are_not_registered_on_the_front_end() {
		do_action( 'wp_enqueue_scripts' );

		foreach ( array_keys( self::ADMIN_STYLES ) as $handle ) {
			$this->assertFalse(
				wp_style_is( $handle, 'registered' ),
				"Style '{$handle}' is admin-only and must not be registered on the front end."
			);
		}
	}

	public function test_admin_only_scripts_are_not_registered_on_the_front_end() {
		do_action( 'wp_enqueue_scripts' );

		foreach ( array_keys( self::ADMIN_SCRIPTS ) as $handle ) {
			$this->assertFalse(
				wp_script_is( $handle, 'registered' ),
				"Script '{$handle}' is admin-only and must not be registered on the front end."
			);
		}
	}

	/**
	 * The release theme: after this change the front end registers nothing
	 * that pulls in jQuery.
	 */
	public function test_front_end_registers_no_jquery_dependent_plugin_assets() {
		do_action( 'wp_enqueue_scripts' );

		foreach ( wp_scripts()->registered as $handle => $script ) {
			if ( 0 !== strpos( $handle, 'w4pl' ) ) {
				continue;
			}

			$this->assertNotContains(
				'jquery',
				(array) $script->deps,
				"Front-end handle '{$handle}' still depends on jQuery."
			);
		}
	}

	/* ---------------------------------------------------------------------
	 * Admin: unchanged.
	 * ------------------------------------------------------------------ */

	/**
	 * Fire admin_enqueue_scripts the way wp-admin does.
	 *
	 * A screen must exist first: core's own Site Health callback on this hook
	 * dereferences get_current_screen().
	 */
	private function do_admin_enqueue_scripts() {
		set_current_screen( 'post.php' );

		do_action( 'admin_enqueue_scripts', 'post.php' );
	}

	public function test_admin_styles_are_registered_exactly_as_before() {
		$this->do_admin_enqueue_scripts();

		foreach ( self::ADMIN_STYLES as $handle => $src ) {
			$this->assertTrue( wp_style_is( $handle, 'registered' ), "Style '{$handle}' must stay registered in admin." );

			$style = wp_styles()->registered[ $handle ];

			$this->assertSame( W4PL_URL . $src, $style->src );
			$this->assertSame( array(), $style->deps );
			$this->assertSame( W4PL_VERSION, $style->ver );
		}
	}

	public function test_admin_scripts_are_registered_exactly_as_before() {
		$this->do_admin_enqueue_scripts();

		foreach ( self::ADMIN_SCRIPTS as $handle => $src ) {
			$this->assertTrue( wp_script_is( $handle, 'registered' ), "Script '{$handle}' must stay registered in admin." );

			$script = wp_scripts()->registered[ $handle ];

			$this->assertSame( W4PL_URL . $src, $script->src );
			$this->assertSame( array( 'jquery', 'jquery-ui-sortable' ), $script->deps );
			$this->assertSame( W4PL_VERSION, $script->ver );
			$this->assertSame( 1, $script->extra['group'], "Script '{$handle}' must stay in the footer." );
		}
	}

	/**
	 * The list editor enqueues by bare handle, which silently no-ops for an
	 * unregistered handle. Guard the registration those call sites rely on.
	 */
	public function test_list_editor_can_enqueue_its_assets_in_admin() {
		require_once dirname( __DIR__ ) . '/admin/class-admin-list-editor.php';

		$this->do_admin_enqueue_scripts();

		wp_enqueue_style( 'w4pl_form' );
		wp_enqueue_style( 'w4pl_list_editor' );
		wp_enqueue_script( 'w4pl_form' );
		wp_enqueue_script( 'w4pl_list_editor' );

		$this->assertTrue( wp_style_is( 'w4pl_form', 'enqueued' ) );
		$this->assertTrue( wp_style_is( 'w4pl_list_editor', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'w4pl_form', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'w4pl_list_editor', 'enqueued' ) );
	}

	public function test_docs_page_can_enqueue_its_stylesheet_in_admin() {
		$this->do_admin_enqueue_scripts();

		wp_enqueue_style( 'w4pl-admin-documentation' );

		$this->assertTrue( wp_style_is( 'w4pl-admin-documentation', 'enqueued' ) );
	}

	/* ---------------------------------------------------------------------
	 * Interaction with the new front-end script.
	 * ------------------------------------------------------------------ */

	public function test_ajax_nav_script_is_still_registered_on_the_front_end() {
		do_action( 'wp_enqueue_scripts' );

		$this->assertTrue( wp_script_is( self::NAV_HANDLE, 'registered' ) );
		$this->assertSame( array(), wp_scripts()->registered[ self::NAV_HANDLE ]->deps );
	}

	public function test_rendering_an_ajax_list_on_the_front_end_still_enqueues_the_nav_script() {
		do_action( 'wp_enqueue_scripts' );

		$this->render_paginated_ajax_list();

		$this->assertTrue( wp_script_is( self::NAV_HANDLE, 'enqueued' ) );
	}

	/**
	 * Nothing else may ride along with it.
	 */
	public function test_a_front_end_page_with_an_ajax_list_queues_only_the_nav_script() {
		do_action( 'wp_enqueue_scripts' );

		$this->render_paginated_ajax_list();

		$this->assertSame( array( self::NAV_HANDLE ), $this->plugin_handles( wp_scripts()->queue ) );
		$this->assertSame( array(), $this->plugin_handles( wp_styles()->queue ) );
	}

	/* ---------------------------------------------------------------------
	 * Paths that must not regress.
	 * ------------------------------------------------------------------ */

	/**
	 * The block's editor script is registered from blocks.php on `init`, not
	 * from the admin/front split, so the block editor is untouched.
	 */
	public function test_block_editor_script_registration_is_untouched() {
		$block = WP_Block_Type_Registry::get_instance()->get_registered( 'w4-post-list/postlist' );

		$this->assertInstanceOf( 'WP_Block_Type', $block );

		$handles = property_exists( $block, 'editor_script_handles' )
			? (array) $block->editor_script_handles
			: array( $block->editor_script );

		$this->assertContains( 'w4pl_block', $handles );
		$this->assertSame( 'w4pl_render_block_postlist', $block->render_callback );
	}

	/**
	 * The preview endpoint runs in admin-ajax.php, where neither enqueue hook
	 * fires. It never needed the admin handles and must keep working without
	 * them - including enqueueing the nav script by self-registration.
	 */
	public function test_preview_render_path_needs_no_registered_assets() {
		require_once dirname( __DIR__ ) . '/admin/class-admin-lists-metaboxes.php';
		require_once dirname( __DIR__ ) . '/admin/class-admin-preview.php';

		// Enough posts to paginate; the fodder post is created last so the
		// newest-first first page is the one asserted on below.
		for ( $i = 0; $i < 5; $i++ ) {
			$this->factory->post->create( array( 'post_title' => 'Filler ' . $i ) );
		}
		$this->factory->post->create( array( 'post_title' => 'Preview fodder' ) );

		// Deliberately fire neither wp_enqueue_scripts nor admin_enqueue_scripts.
		$this->assertFalse( wp_script_is( self::NAV_HANDLE, 'registered' ), 'Sanity: nothing pre-registered the handle.' );

		$html = W4PL_Admin_Preview::render_preview(
			array(
				'id'             => 424242,
				'list_type'      => 'posts',
				'post_type'      => array( 'post' ),
				'posts_per_page' => 2,
				'template'       => '<ul>[posts]<li>[post_title]</li>[/posts]</ul>[nav type="plain" ajax="1"]',
			)
		);

		$this->assertStringContainsString( 'Preview fodder', $html );
		$this->assertStringContainsString( 'ajax-navigation', $html, 'Sanity: the preview really rendered ajax nav.' );
		$this->assertTrue( wp_script_is( self::NAV_HANDLE, 'enqueued' ), 'The nav script must self-register where no enqueue hook fires.' );
		$this->assertFalse( wp_style_is( 'w4pl_form', 'registered' ) );
	}

	/* ------------------------------------------------------------------ */

	/**
	 * Keep only this plugin's handles; core queues its own on every request.
	 *
	 * @param array $handles Enqueued handles.
	 * @return array
	 */
	private function plugin_handles( array $handles ) {
		return array_values(
			array_filter(
				$handles,
				function ( $handle ) {
					return 0 === strpos( $handle, 'w4pl' );
				}
			)
		);
	}

	/**
	 * Render a 3-page ajax-paginated list through the shortcode pipeline.
	 *
	 * @return string
	 */
	private function render_paginated_ajax_list() {
		for ( $i = 0; $i < 6; $i++ ) {
			$this->factory->post->create( array( 'post_title' => 'Item ' . $i ) );
		}

		$list_id = $this->factory->post->create(
			array(
				'post_type'   => 'w4pl',
				'post_status' => 'publish',
			)
		);
		update_post_meta(
			$list_id,
			'_w4pl',
			array(
				'list_type'      => 'posts',
				'post_type'      => array( 'post' ),
				'posts_per_page' => 2,
				'template'       => '<ul>[posts]<li>[post_title]</li>[/posts]</ul>[nav type="plain" ajax="1"]',
			)
		);

		return do_shortcode( '[postlist id="' . $list_id . '"]' );
	}
}
