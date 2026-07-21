<?php
/**
 * Options versioning and lazy migration behavior.
 *
 * @package W4_Post_List
 */

class OptionsVersionTest extends WP_UnitTestCase {

	public function test_version_is_stamped_on_save_path() {
		$options = apply_filters( 'w4pl/pre_save_options', array( 'list_type' => 'posts' ) );

		$this->assertArrayHasKey( 'options_version', $options );
		$this->assertSame( W4PL_Options_Migrator::OPTIONS_VERSION, $options['options_version'] );
	}

	public function test_legacy_options_are_migrated_on_read() {
		$options = apply_filters( 'w4pl/pre_get_options', array( 'list_type' => 'posts' ) );

		$this->assertSame( W4PL_Options_Migrator::OPTIONS_VERSION, $options['options_version'] );
	}

	public function test_migrator_passes_non_array_through_untouched() {
		$migrator = new W4PL_Options_Migrator();

		$this->assertFalse( $migrator->migrate( false ) );
		$this->assertFalse( $migrator->stamp_version( false ) );
	}

	public function test_legacy_and_versioned_options_render_identically() {
		self::factory()->post->create(
			array(
				'post_title' => 'Migration fixture post',
				'post_date'  => '2025-05-05 05:05:05',
			)
		);

		$base = array(
			'id'             => 999999,
			'list_type'      => 'posts',
			'post_type'      => array( 'post' ),
			'posts_per_page' => 10,
			'orderby'        => 'post_date',
			'order'          => 'DESC',
		);

		$legacy    = $base;
		$versioned = $base;

		$versioned['options_version'] = W4PL_Options_Migrator::OPTIONS_VERSION;

		$legacy_html    = W4PL_List_Factory::get_list( apply_filters( 'w4pl/pre_get_options', $legacy ) )->get_html();
		$versioned_html = W4PL_List_Factory::get_list( apply_filters( 'w4pl/pre_get_options', $versioned ) )->get_html();

		$this->assertNotEmpty( $legacy_html );
		$this->assertSame( $legacy_html, $versioned_html );
	}
}
