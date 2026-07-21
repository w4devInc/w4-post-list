<?php
/**
 * Characterization tests for the options -> HTML rendering contract.
 *
 * These snapshots freeze the output of the rendering pipeline for all five
 * list types. They are the compatibility guarantee for lists stored on
 * existing sites: a refactor that changes any snapshot is a breaking change
 * until proven otherwise. Regenerate deliberately with:
 *
 *   W4PL_UPDATE_SNAPSHOTS=1 vendor-dev/bin/phpunit --filter ListRenderingTest
 *
 * @package W4_Post_List
 */

class ListRenderingTest extends WP_UnitTestCase {

	protected static $cat_alpha;
	protected static $cat_beta;
	protected static $user_ann;
	protected static $user_bob;
	protected static $post_ids = array();

	public static function wpSetUpBeforeClass( $factory ) {
		self::$cat_alpha = $factory->term->create(
			array(
				'taxonomy' => 'category',
				'name'     => 'Alpha',
				'slug'     => 'alpha',
			)
		);
		self::$cat_beta  = $factory->term->create(
			array(
				'taxonomy' => 'category',
				'name'     => 'Beta',
				'slug'     => 'beta',
			)
		);

		self::$user_ann = $factory->user->create(
			array(
				'role'         => 'author',
				'user_login'   => 'ann',
				'display_name' => 'Ann Author',
			)
		);
		self::$user_bob = $factory->user->create(
			array(
				'role'         => 'author',
				'user_login'   => 'bob',
				'display_name' => 'Bob Builder',
			)
		);

		$fixtures = array(
			array( 'Winter release notes', '2026-01-05 09:00:00', self::$cat_alpha, self::$user_ann ),
			array( 'Spring cleaning tips', '2026-04-10 10:00:00', self::$cat_beta, self::$user_bob ),
			array( 'Year in review', '2025-09-14 12:00:00', self::$cat_alpha, self::$user_ann ),
			array( 'Interview with a builder', '2025-06-21 08:30:00', self::$cat_beta, self::$user_bob ),
			array( 'Ten tips for faster sites', '2024-11-02 15:00:00', self::$cat_alpha, self::$user_ann ),
			array( 'Hello from the archive', '2024-03-10 11:00:00', self::$cat_beta, self::$user_bob ),
		);

		foreach ( $fixtures as $f ) {
			self::$post_ids[] = $factory->post->create(
				array(
					'post_title'    => $f[0],
					'post_date'     => $f[1],
					'post_category' => array( $f[2] ),
					'post_author'   => $f[3],
					'post_content'  => 'Fixture content for ' . $f[0] . '.',
					'post_excerpt'  => 'Excerpt for ' . $f[0] . '.',
				)
			);
		}
	}

	/**
	 * Render a list from an options array through the same path the
	 * [postlist] shortcode uses: a real w4pl post with _w4pl meta.
	 */
	protected function render_list( array $options ) {
		$list_id = self::factory()->post->create(
			array(
				'post_type'   => 'w4pl',
				'post_title'  => 'Characterization list',
				'post_status' => 'publish',
			)
		);
		update_post_meta( $list_id, '_w4pl', $options );

		return do_shortcode( '[postlist id="' . $list_id . '"]' );
	}

	/**
	 * Strip run-dependent identifiers so snapshots are stable across runs.
	 */
	protected function normalize( $html ) {
		$patterns = array(
			'/W4PL_List_\d+/'                 => 'W4PL_List_{ID}',
			'/w4pl-list-\d+/'                 => 'w4pl-list-{ID}',
			'/w4pl-inner-\d+/'                => 'w4pl-inner-{ID}',
			'/w4pl-\d+/'                      => 'w4pl-{ID}',
			'/\?p=\d+/'                       => '?p={ID}',
			'/\?page_id=\d+/'                 => '?page_id={ID}',
			'/\?cat=\d+/'                     => '?cat={ID}',
			'/\?author=\d+/'                  => '?author={ID}',
			'/page\d+=(\d+)/'                 => 'page{ID}=$1',
			'/\b(post|tag|category|user|term)[-_]item[-_]\d+/' => '$1-item-{ID}',
			'/\bpost-\d+\b/'                  => 'post-{ID}',
			'/\btag-\d+\b/'                   => 'tag-{ID}',
		);

		$html = preg_replace( array_keys( $patterns ), array_values( $patterns ), $html );

		// Trim per-line trailing whitespace; keep structure otherwise.
		$html = preg_replace( '/[ \t]+$/m', '', $html );

		return trim( $html ) . "\n";
	}

	protected function assertMatchesHtmlSnapshot( $name, $html ) {
		$normalized = $this->normalize( $html );
		$dir        = __DIR__ . '/snapshots';
		$file       = $dir . '/' . $name . '.html';

		if ( getenv( 'W4PL_UPDATE_SNAPSHOTS' ) ) {
			if ( ! is_dir( $dir ) ) {
				mkdir( $dir, 0777, true );
			}
			file_put_contents( $file, $normalized );
		}

		$this->assertFileExists( $file, "Snapshot '$name' missing. Generate with W4PL_UPDATE_SNAPSHOTS=1." );
		$this->assertSame( file_get_contents( $file ), $normalized, "Rendered HTML diverged from snapshot '$name'." );
	}

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
	}
}
