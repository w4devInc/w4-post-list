<?php
/**
 * Usage counter behavior.
 *
 * @package W4_Post_List
 */

class StatsTest extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		delete_option( W4PL_Stats::OPTION );
	}

	public function test_increment_and_get() {
		W4PL_Stats::increment( 'sample_counter' );
		W4PL_Stats::increment( 'sample_counter', 2 );

		$this->assertSame( 3, W4PL_Stats::get( 'sample_counter' ) );
		$this->assertSame( 0, W4PL_Stats::get( 'unknown_counter' ) );
	}

	public function test_publishing_a_list_increments_counters() {
		self::factory()->post->create(
			array(
				'post_type'   => 'w4pl',
				'post_status' => 'publish',
			)
		);

		$this->assertSame( 1, W4PL_Stats::get( 'lists_created' ) );
		$this->assertSame( 1, W4PL_Stats::get( 'lists_published' ) );
	}

	public function test_draft_then_publish_counts_creation_once() {
		$id = self::factory()->post->create(
			array(
				'post_type'   => 'w4pl',
				'post_status' => 'draft',
			)
		);

		$this->assertSame( 1, W4PL_Stats::get( 'lists_created' ) );
		$this->assertSame( 0, W4PL_Stats::get( 'lists_published' ) );

		wp_publish_post( $id );

		$this->assertSame( 1, W4PL_Stats::get( 'lists_created' ) );
		$this->assertSame( 1, W4PL_Stats::get( 'lists_published' ) );
	}

	public function test_regular_posts_do_not_count() {
		self::factory()->post->create( array( 'post_status' => 'publish' ) );

		$this->assertSame( 0, W4PL_Stats::get( 'lists_created' ) );
	}

	public function test_counters_reach_insights_extra() {
		W4PL_Stats::increment( 'lists_created', 5 );

		$extra = w4pl_insights_extra();

		$this->assertSame( 5, $extra['Stat_lists_created'] );
	}
}
