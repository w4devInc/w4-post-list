<?php
/**
 * Shared fixtures, normalization and snapshot assertion for rendering tests.
 *
 * These snapshots freeze the output of the rendering pipeline for all five
 * list types. They are the compatibility guarantee for lists stored on
 * existing sites: a refactor that changes any snapshot is a breaking change
 * until proven otherwise. Regenerate deliberately with:
 *
 *   W4PL_UPDATE_SNAPSHOTS=1 vendor-dev/bin/phpunit
 *
 * @package W4_Post_List
 */

abstract class W4PL_Snapshot_TestCase extends WP_UnitTestCase {

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
			'/W4PL_List \d+/'                 => 'W4PL_List {ID}',
			'/w4pl-list-\d+/'                 => 'w4pl-list-{ID}',
			'/w4pl-inner-\d+/'                => 'w4pl-inner-{ID}',
			'/w4pl-css-\d+/'                  => 'w4pl-css-{ID}',
			'/w4pl-js-\d+/'                   => 'w4pl-js-{ID}',
			'/w4pl-\d+/'                      => 'w4pl-{ID}',
			'/\?p=\d+/'                       => '?p={ID}',
			'/\?page_id=\d+/'                 => '?page_id={ID}',
			'/\?cat=\d+/'                     => '?cat={ID}',
			'/\?author=\d+/'                  => '?author={ID}',
			'/page\d+=(\d+)/'                 => 'page{ID}=$1',
			'/\b(post|tag|category|user|term)[-_]item[-_]\d+/' => '$1-item-{ID}',
			'/\bpost-\d+\b/'                  => 'post-{ID}',
			'/\btag-\d+\b/'                   => 'tag-{ID}',
			'/[\'"]https?:\/\/[^\'"]*gravatar[^\'"]*[\'"]/' => '"{AVATAR_URL}"',
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
}
