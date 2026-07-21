<?php
/**
 * First-run onboarding behavior.
 *
 * @package W4_Post_List
 */

class OnboardingTest extends WP_UnitTestCase {

	public static function wpSetUpBeforeClass( $factory ) {
		// Admin-only class is not loaded by the plugin bootstrap in CLI context.
		require_once dirname( __DIR__ ) . '/admin/class-admin-onboarding.php';
	}

	public function set_up() {
		parent::set_up();

		delete_option( 'w4pl_welcome_pending' );
		delete_option( 'w4pl_maybe_create_sample' );

		if ( ! function_exists( 'set_current_screen' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-screen.php';
			require_once ABSPATH . 'wp-admin/includes/screen.php';
		}
	}

	public function test_activation_sets_onboarding_flags() {
		w4pl_activated();

		$this->assertNotEmpty( get_option( 'w4pl_welcome_pending' ) );
		$this->assertNotEmpty( get_option( 'w4pl_maybe_create_sample' ) );
	}

	public function test_network_wide_activation_skips_onboarding_flags() {
		w4pl_activated( true );

		$this->assertEmpty( get_option( 'w4pl_welcome_pending' ) );
		$this->assertEmpty( get_option( 'w4pl_maybe_create_sample' ) );
	}

	public function test_sample_list_is_created_and_renders() {
		self::factory()->post->create( array( 'post_title' => 'Onboarding fixture post' ) );

		$list_id = W4PL_Admin_Onboarding::create_sample_list();

		$this->assertGreaterThan( 0, $list_id );

		$list = get_post( $list_id );
		$this->assertSame( 'w4pl', $list->post_type );
		$this->assertSame( 'draft', $list->post_status );

		$options = get_post_meta( $list_id, '_w4pl', true );
		$this->assertSame( 'posts', $options['list_type'] );
		$this->assertNotEmpty( $options['template'], 'Sample list must ship with a visible, editable template' );
		$this->assertArrayHasKey( 'options_version', $options, 'Sample must go through the versioned save pipeline' );

		$html = do_shortcode( '[postlist id="' . $list_id . '"]' );
		$this->assertStringContainsString( 'Onboarding fixture post', $html, 'Sample list must actually render' );
	}

	public function test_sample_not_created_when_lists_exist() {
		self::factory()->post->create( array( 'post_type' => 'w4pl' ) );
		update_option( 'w4pl_maybe_create_sample', 1 );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );

		$onboarding = new W4PL_Admin_Onboarding();
		$onboarding->maybe_create_sample_list();

		$counts = wp_count_posts( 'w4pl' );
		$this->assertSame( 1, array_sum( (array) $counts ), 'No sample should be added next to existing lists' );
		$this->assertEmpty( get_option( 'w4pl_maybe_create_sample' ), 'Flag should be cleared' );
	}

	public function test_sample_creation_waits_for_capable_user() {
		update_option( 'w4pl_maybe_create_sample', 1 );
		wp_set_current_user( 0 );

		$onboarding = new W4PL_Admin_Onboarding();
		$onboarding->maybe_create_sample_list();

		$this->assertNotEmpty( get_option( 'w4pl_maybe_create_sample' ), 'Flag must survive until a capable user visits' );
		$this->assertSame( 0, array_sum( (array) wp_count_posts( 'w4pl' ) ) );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$onboarding->maybe_create_sample_list();

		$this->assertEmpty( get_option( 'w4pl_maybe_create_sample' ) );
		$this->assertSame( 1, array_sum( (array) wp_count_posts( 'w4pl' ) ) );
	}

	public function test_welcome_notice_renders_only_when_pending() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		set_current_screen( 'dashboard' );

		$onboarding = new W4PL_Admin_Onboarding();

		ob_start();
		$onboarding->welcome_notice();
		$this->assertSame( '', ob_get_clean(), 'No notice without the pending flag' );

		update_option( 'w4pl_welcome_pending', time() );

		ob_start();
		$onboarding->welcome_notice();
		$html = ob_get_clean();

		$this->assertStringContainsString( 'Welcome to W4 Post List', $html );
		$this->assertStringContainsString( 'w4pl_dismiss_welcome', $html, 'Dismiss affordance present' );
	}

	public function test_empty_state_renders_only_with_zero_lists() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		set_current_screen( 'edit-w4pl' );

		$onboarding = new W4PL_Admin_Onboarding();

		ob_start();
		$onboarding->empty_state();
		$this->assertStringContainsString( 'No lists yet', ob_get_clean() );

		self::factory()->post->create( array( 'post_type' => 'w4pl' ) );

		ob_start();
		$onboarding->empty_state();
		$this->assertSame( '', ob_get_clean(), 'No empty-state once a list exists' );
	}

	public function test_help_tab_added_on_list_screens() {
		set_current_screen( 'edit-w4pl' );
		$screen = get_current_screen();

		$onboarding = new W4PL_Admin_Onboarding();
		$onboarding->help_tabs( $screen );

		$this->assertNotNull( $screen->get_help_tab( 'w4pl-overview' ) );
	}

	public function test_appsero_notice_silent_before_first_published_list() {
		global $pagenow, $typenow;
		$pagenow = 'edit.php';
		$typenow = 'w4pl';

		ob_start();
		w4pl_appsero_admin_notices();
		$this->assertSame( '', ob_get_clean(), 'Telemetry ask must wait for the first published list' );
	}
}
