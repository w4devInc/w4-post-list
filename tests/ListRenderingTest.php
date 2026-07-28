<?php
/**
 * Characterization tests for the options -> HTML rendering contract.
 *
 * Snapshots freeze the rendered output; regenerate deliberately with
 * W4PL_UPDATE_SNAPSHOTS=1.
 *
 * @package W4_Post_List
 */

require_once __DIR__ . '/class-w4pl-snapshot-testcase.php';

class ListRenderingTest extends W4PL_Snapshot_TestCase {

	public function test_posts_list_default_template() {
		$html = $this->render_list(
			array(
				'list_type'      => 'posts',
				'post_type'      => array( 'post' ),
				'posts_per_page' => 10,
				'orderby'        => 'post_date',
				'order'          => 'DESC',
			)
		);
		$this->assertMatchesHtmlSnapshot( 'posts-default', $html );
	}

	public function test_posts_list_custom_template() {
		$html = $this->render_list(
			array(
				'list_type'      => 'posts',
				'post_type'      => array( 'post' ),
				'posts_per_page' => 10,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'template'       => '<ul>[posts]<li class="[post_class]"><a href="[post_permalink]">[post_title]</a> <span>[post_excerpt]</span></li>[/posts]</ul>',
			)
		);
		$this->assertMatchesHtmlSnapshot( 'posts-custom', $html );
	}

	public function test_posts_grouped_by_year() {
		$html = $this->render_list(
			array(
				'list_type'      => 'posts',
				'post_type'      => array( 'post' ),
				'posts_per_page' => 10,
				'orderby'        => 'post_date',
				'order'          => 'DESC',
				'groupby'        => 'year',
				'template'       => '<ul>[groups]<li><strong>[group_title]</strong><ol>[posts]<li><a href="[post_permalink]">[post_title]</a></li>[/posts]</ol></li>[/groups]</ul>',
			)
		);
		$this->assertMatchesHtmlSnapshot( 'posts-grouped-year', $html );
	}

	public function test_posts_pagination() {
		$html = $this->render_list(
			array(
				'list_type'      => 'posts',
				'post_type'      => array( 'post' ),
				'posts_per_page' => 2,
				'orderby'        => 'post_date',
				'order'          => 'DESC',
				'template'       => '<ul>[posts]<li>[post_title]</li>[/posts]</ul>[nav type="plain"]',
			)
		);
		$this->assertMatchesHtmlSnapshot( 'posts-pagination', $html );
	}

	public function test_terms_list_default_template() {
		$html = $this->render_list(
			array(
				'list_type'      => 'terms',
				'terms_taxonomy' => 'category',
			)
		);
		$this->assertMatchesHtmlSnapshot( 'terms-default', $html );
	}

	public function test_terms_posts_list() {
		$html = $this->render_list(
			array(
				'list_type'      => 'terms.posts',
				'terms_taxonomy' => 'category',
				'post_type'      => array( 'post' ),
				'posts_per_page' => 10,
				'orderby'        => 'post_date',
				'order'          => 'DESC',
			)
		);
		$this->assertMatchesHtmlSnapshot( 'terms-posts-default', $html );
	}

	public function test_users_list_default_template() {
		$html = $this->render_list(
			array(
				'list_type' => 'users',
			)
		);
		$this->assertMatchesHtmlSnapshot( 'users-default', $html );
	}

	public function test_users_posts_list() {
		$html = $this->render_list(
			array(
				'list_type'      => 'users.posts',
				'post_type'      => array( 'post' ),
				'posts_per_page' => 10,
				'orderby'        => 'post_date',
				'order'          => 'DESC',
			)
		);
		$this->assertMatchesHtmlSnapshot( 'users-posts-default', $html );
	}

	/**
	 * Regression guard for the reported crash class: unit suffixes in
	 * template tag attributes (post_thumbnail width="50px") must never fatal.
	 */
	public function test_thumbnail_tag_with_unit_suffix_attribute_does_not_fatal() {
		$attachment_id = self::factory()->attachment->create_upload_object(
			DIR_TESTDATA . '/images/canola.jpg',
			self::$post_ids[0]
		);
		set_post_thumbnail( self::$post_ids[0], $attachment_id );

		$html = $this->render_list(
			array(
				'list_type'      => 'posts',
				'post_type'      => array( 'post' ),
				'posts_per_page' => 10,
				'template'       => '[posts]<div>[post_title] [post_thumbnail width="50px" height="50px"]</div>[/posts]',
			)
		);

		$this->assertIsString( $html );
		$this->assertStringContainsString( 'Winter release notes', $html );
		$this->assertStringContainsString( '<img', $html, 'Thumbnail should render despite the px-suffixed dimensions' );
	}
}
