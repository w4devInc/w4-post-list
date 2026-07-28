<?php
/**
 * Save-time validation, nonce behavior, and the review prompt gate.
 *
 * @package W4_Post_List
 */

class ValidationTest extends WP_UnitTestCase {

	public static function wpSetUpBeforeClass( $factory ) {
		require_once dirname( __DIR__ ) . '/admin/class-admin-lists-metaboxes.php';
		require_once dirname( __DIR__ ) . '/admin/class-admin-validation.php';
		require_once dirname( __DIR__ ) . '/admin/class-admin-onboarding.php';
	}

	public function set_up() {
		parent::set_up();
		new W4PL_Admin_Validation();

		if ( ! function_exists( 'set_current_screen' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-screen.php';
			require_once ABSPATH . 'wp-admin/includes/screen.php';
		}

		delete_option( 'w4pl_first_publish_time' );
		delete_option( 'w4pl_review_dismissed' );
	}

	public function tear_down() {
		unset( $_POST['w4pl'], $_POST['w4pl_options_nonce'] );
		parent::tear_down();
	}

	// ----- Numeric coercion -----

	public function test_coerce_numeric_reads_leading_digits_and_drops_garbage() {
		$this->assertSame( '10', W4PL_Admin_Validation::coerce_numeric( '10 posts' ) );
		$this->assertSame( '', W4PL_Admin_Validation::coerce_numeric( 'ten' ) );
		$this->assertSame( '3', W4PL_Admin_Validation::coerce_numeric( '3' ) );
	}

	public function test_save_filter_coerces_and_records_warnings() {
		$options = apply_filters(
			'w4pl/pre_save_options',
			array(
				'id'             => 777,
				'list_type'      => 'posts',
				'posts_per_page' => 'ten',
			)
		);

		$this->assertSame( '', $options['posts_per_page'] );

		$warnings = get_transient( 'w4pl_save_warnings_777' );
		$this->assertNotEmpty( $warnings );
		$this->assertStringContainsString( 'posts_per_page', $warnings[0] );
	}

	public function test_valid_numeric_values_produce_no_warnings() {
		apply_filters(
			'w4pl/pre_save_options',
			array(
				'id'             => 778,
				'list_type'      => 'posts',
				'posts_per_page' => '12',
				'template'       => '[posts][post_title][/posts]',
			)
		);

		$this->assertFalse( get_transient( 'w4pl_save_warnings_778' ) );
	}

	// ----- Template scanning -----

	public function test_unknown_tag_warning_includes_suggestion() {
		$warnings = W4PL_Admin_Validation::template_warnings(
			array(
				'list_type' => 'posts',
				'template'  => '[posts][post_titel][/posts]',
			)
		);

		$this->assertCount( 1, $warnings );
		$this->assertStringContainsString( 'post_titel', $warnings[0] );
		$this->assertStringContainsString( 'post_title', $warnings[0] );
	}

	public function test_missing_loop_tag_warning_for_list_type() {
		$warnings = W4PL_Admin_Validation::template_warnings(
			array(
				'list_type' => 'terms',
				'template'  => '[posts][post_title][/posts]',
			)
		);

		$found = false;
		foreach ( $warnings as $warning ) {
			if ( false !== strpos( $warning, '[terms]' ) ) {
				$found = true;
			}
		}
		$this->assertTrue( $found, 'Missing-loop warning expected for terms list with posts-only template' );
	}

	public function test_clean_template_produces_no_warnings() {
		$warnings = W4PL_Admin_Validation::template_warnings(
			array(
				'list_type' => 'posts',
				'template'  => "[posts]\n<div class=\"post-item\">[post_title]</div>\n[/posts]\n[nav]",
			)
		);

		$this->assertSame( array(), $warnings );
	}

	// ----- Save-path nonce -----

	protected function make_list_with_options() {
		$id = self::factory()->post->create( array( 'post_type' => 'w4pl' ) );
		update_post_meta( $id, '_w4pl', array( 'list_type' => 'posts', 'posts_per_page' => 5 ) );
		return $id;
	}

	public function test_save_without_posted_options_leaves_meta_untouched() {
		$id = $this->make_list_with_options();

		$metaboxes = new W4PL_Admin_Lists_Metaboxes();
		$metaboxes->save_post( $id );

		$this->assertSame( 5, get_post_meta( $id, '_w4pl', true )['posts_per_page'], 'Quick-edit style save must not wipe options' );
	}

	public function test_save_with_options_but_bad_nonce_bails_without_wiping() {
		$id = $this->make_list_with_options();

		$_POST['w4pl']               = array( 'list_type' => 'terms' );
		$_POST['w4pl_options_nonce'] = 'not-a-nonce';

		$metaboxes = new W4PL_Admin_Lists_Metaboxes();
		$metaboxes->save_post( $id );

		$stored = get_post_meta( $id, '_w4pl', true );
		$this->assertSame( 'posts', $stored['list_type'], 'Invalid nonce must bail, not wipe or apply' );
	}

	public function test_save_with_valid_nonce_persists_options() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );

		$id = $this->make_list_with_options();

		$_POST['w4pl']               = array(
			'list_type'      => 'posts',
			'posts_per_page' => '7',
		);
		$_POST['w4pl_options_nonce'] = wp_create_nonce( 'w4pl_save_options' );

		$metaboxes = new W4PL_Admin_Lists_Metaboxes();
		$metaboxes->save_post( $id );

		$stored = get_post_meta( $id, '_w4pl', true );
		$this->assertSame( '7', $stored['posts_per_page'] );
		$this->assertSame( W4PL_Options_Migrator::OPTIONS_VERSION, $stored['options_version'] );
	}

	// ----- Review prompt gate -----

	public function test_review_cta_markup_carries_the_resolving_link() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		update_option( 'w4pl_first_publish_time', time() - 8 * DAY_IN_SECONDS );
		set_current_screen( 'edit-w4pl' );

		$onboarding = new W4PL_Admin_Onboarding();

		ob_start();
		$onboarding->review_prompt();
		$html = ob_get_clean();

		$this->assertStringContainsString( 'w4pl_review_go', $html, 'Review CTA must route through the dismissing redirect' );
		$this->assertStringContainsString( 'w4pl_dismiss_review', $html );
	}

	public function test_review_prompt_waits_for_first_publish_plus_seven_days() {
		$this->assertFalse( W4PL_Admin_Onboarding::review_prompt_due(), 'No publish yet' );

		update_option( 'w4pl_first_publish_time', time() - DAY_IN_SECONDS );
		$this->assertFalse( W4PL_Admin_Onboarding::review_prompt_due(), 'Too recent' );

		update_option( 'w4pl_first_publish_time', time() - 8 * DAY_IN_SECONDS );
		$this->assertTrue( W4PL_Admin_Onboarding::review_prompt_due() );

		update_option( 'w4pl_review_dismissed', time() );
		$this->assertFalse( W4PL_Admin_Onboarding::review_prompt_due(), 'Dismissal is permanent' );
	}

	public function test_first_publish_time_is_recorded_once() {
		self::factory()->post->create(
			array(
				'post_type'   => 'w4pl',
				'post_status' => 'publish',
			)
		);

		$first = get_option( 'w4pl_first_publish_time' );
		$this->assertNotEmpty( $first );

		self::factory()->post->create(
			array(
				'post_type'   => 'w4pl',
				'post_status' => 'publish',
			)
		);

		$this->assertSame( $first, get_option( 'w4pl_first_publish_time' ), 'First-publish time never moves' );
	}
}
