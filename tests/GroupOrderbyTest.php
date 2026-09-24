<?php
/**
 * Group ordering: groups can be sorted by their title, not only their ID.
 *
 * @package W4_Post_List
 */

require_once __DIR__ . '/class-w4pl-snapshot-testcase.php';

class GroupOrderbyTest extends W4PL_Snapshot_TestCase {

	protected static $term_ids = array();

	public static function wpSetUpBeforeClass( $factory ) {
		parent::wpSetUpBeforeClass( $factory );

		require_once dirname( __DIR__ ) . '/admin/class-admin-lists-metaboxes.php';

		// Created in an order that puts IDs and names at odds.
		foreach ( array( 'Zebra', 'Apple', 'Mango' ) as $name ) {
			$term_id = $factory->term->create(
				array(
					'taxonomy' => 'category',
					'name'     => $name,
				)
			);
			self::$term_ids[ $name ] = $term_id;
			$factory->post->create(
				array(
					'post_title'    => $name . ' post',
					'post_category' => array( $term_id ),
				)
			);
		}
	}

	protected function group_titles( array $extra ) {
		$html = $this->render_list(
			array_merge(
				array(
					'list_type'      => 'posts',
					'post_type'      => array( 'post' ),
					'posts_per_page' => 20,
					'orderby'        => 'post_date',
					'order'          => 'DESC',
					'groupby'        => 'tax_category',
					'template'       => '[groups]<h3>[group_title]</h3>[posts][/posts][/groups]',
				),
				$extra
			)
		);

		preg_match_all( '/<h3>([^<]+)<\/h3>/', $html, $m );

		return array_values( array_intersect( $m[1], array( 'Zebra', 'Apple', 'Mango' ) ) );
	}

	public function test_default_group_order_is_by_id() {
		$this->assertSame( array( 'Zebra', 'Apple', 'Mango' ), $this->group_titles( array( 'group_order' => 'ASC' ) ) );
	}

	public function test_groups_ordered_by_title_asc() {
		$this->assertSame(
			array( 'Apple', 'Mango', 'Zebra' ),
			$this->group_titles(
				array(
					'group_orderby' => 'title',
					'group_order'   => 'ASC',
				)
			)
		);
	}

	public function test_groups_ordered_by_title_desc() {
		$this->assertSame(
			array( 'Zebra', 'Mango', 'Apple' ),
			$this->group_titles(
				array(
					'group_orderby' => 'title',
					'group_order'   => 'DESC',
				)
			)
		);
	}

	public function test_yearmonth_grouping_ignores_name_and_stays_chronological() {
		$factory = self::factory();
		foreach ( array( '2019-01-15', '2019-04-15', '2019-08-15' ) as $date ) {
			$factory->post->create(
				array(
					'post_title' => 'Dated ' . $date,
					'post_date'  => $date . ' 10:00:00',
				)
			);
		}

		$html = $this->render_list(
			array(
				'list_type'      => 'posts',
				'post_type'      => array( 'post' ),
				'posts_per_page' => 20,
				'orderby'        => 'post_date',
				'order'          => 'ASC',
				'groupby'        => 'yearmonth',
				'group_orderby'  => 'title',
				'group_order'    => 'ASC',
				'template'       => '[groups]<h3>[group_title]</h3>[posts][/posts][/groups]',
			)
		);

		preg_match_all( '/<h3>2019, ([A-Za-z]+)<\/h3>/', $html, $m );

		// Alphabetical would be April, August, January.
		$this->assertSame( array( 'January', 'April', 'August' ), $m[1] );
	}

	public function test_unknown_group_orderby_is_not_saved() {
		$options = W4PL_Admin_Lists_Metaboxes::sanitize_options(
			array(
				'list_type'     => 'posts',
				'group_orderby' => 'evil',
			)
		);

		$this->assertArrayNotHasKey( 'group_orderby', $options );
	}
}
