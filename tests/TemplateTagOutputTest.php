<?php
/**
 * Output rules for template tags that print stored values.
 *
 * Custom field values are free-form text, so the meta tags print them through
 * the same markup rules WordPress uses for post content. Text that comes out
 * of a rendered item is printed as it is, never read as a template tag, and
 * values placed in attributes are attribute-escaped.
 *
 * @package W4_Post_List
 */

require_once __DIR__ . '/class-w4pl-snapshot-testcase.php';

class TemplateTagOutputTest extends W4PL_Snapshot_TestCase {

	const DISALLOWED_IMG    = '<img src="x" onerror="window.w4plMarker=1">';
	const DISALLOWED_SCRIPT = '<script>window.w4plMarker=1</script>';

	private function first_post_id() {
		return self::$post_ids[0];
	}

	private function render_posts( $template, array $extra = array() ) {
		return $this->render_list(
			array_merge(
				array(
					'list_type'      => 'posts',
					'post_type'      => array( 'post' ),
					'posts_per_page' => 10,
					'orderby'        => 'post_date',
					'order'          => 'DESC',
					'template'       => $template,
				),
				$extra
			)
		);
	}

	private function assertNoDisallowedMarkup( $html ) {
		$this->assertStringNotContainsString( 'onerror="', $html );
		$this->assertStringNotContainsString( '<script', $html );
	}

	public function test_post_meta_single_value_drops_disallowed_markup() {
		update_post_meta( $this->first_post_id(), 'subtitle', 'Hello ' . self::DISALLOWED_IMG . self::DISALLOWED_SCRIPT );

		$html = $this->render_posts( '[posts]<div class="sub">[post_meta key="subtitle"]</div>[/posts]' );

		$this->assertNoDisallowedMarkup( $html );
		$this->assertStringContainsString( '<div class="sub">Hello ', $html );
	}

	public function test_post_meta_keeps_allowed_markup_and_plain_text() {
		update_post_meta( $this->first_post_id(), 'subtitle', 'AT&amp;T <em>launch</em> <a href="https://example.com/">notes</a>' );

		$html = $this->render_posts( '[posts]<div class="sub">[post_meta key="subtitle"]</div>[/posts]' );

		$this->assertStringContainsString( '<div class="sub">AT&amp;T <em>launch</em> <a href="https://example.com/">notes</a></div>', $html );
	}

	public function test_post_meta_multiple_values_are_filtered_after_joining() {
		add_post_meta( $this->first_post_id(), 'parts', '<img src="x"' );
		add_post_meta( $this->first_post_id(), 'parts', ' onerror="window.w4plMarker=1">' );

		$html = $this->render_posts( '[posts]<div class="sub">[post_meta key="parts" multiple="1" sep=""]</div>[/posts]' );

		$this->assertNoDisallowedMarkup( $html );
		$this->assertStringContainsString( '<div class="sub"><img src="x"', $html );
	}

	public function test_post_meta_array_value_with_sub_key_drops_disallowed_markup() {
		update_post_meta( $this->first_post_id(), 'details', array( 'note' => self::DISALLOWED_SCRIPT . 'Kept' ) );

		$html = $this->render_posts( '[posts]<div class="sub">[post_meta key="details" sub_key="note"]</div>[/posts]' );

		$this->assertNoDisallowedMarkup( $html );
		$this->assertStringContainsString( 'Kept', $html );
	}

	public function test_post_meta_joined_array_value_drops_disallowed_markup() {
		update_post_meta( $this->first_post_id(), 'details', array( 'a' => self::DISALLOWED_IMG, 'b' => 'Kept' ) );

		$html = $this->render_posts( '[posts]<div class="sub">[post_meta key="details"]</div>[/posts]' );

		$this->assertNoDisallowedMarkup( $html );
		$this->assertStringContainsString( 'Kept', $html );
	}

	/**
	 * A nested array has no single display value. Before 3.1.1 this printed
	 * "Array" with a PHP warning.
	 */
	public function test_post_meta_nested_array_under_sub_key_prints_nothing() {
		update_post_meta( $this->first_post_id(), 'details', array( 'note' => array( 'deep' ) ) );

		$html = $this->render_posts( '[posts]<div class="sub">[post_meta key="details" sub_key="note"]</div>[/posts]' );

		$this->assertStringNotContainsString( 'Array', $html );
	}

	public function test_post_author_meta_drops_disallowed_markup() {
		update_user_meta( self::$user_ann, 'w4pl_profile_note', self::DISALLOWED_IMG . 'Ann note' );

		$html = $this->render_posts( '[posts]<div class="note">[post_author_meta name="w4pl_profile_note"]</div>[/posts]' );

		$this->assertNoDisallowedMarkup( $html );
		$this->assertStringContainsString( 'Ann note', $html );
	}

	public function test_user_meta_drops_disallowed_markup() {
		update_user_meta( self::$user_ann, 'w4pl_profile_note', self::DISALLOWED_SCRIPT . 'Ann note' );

		$html = $this->render_list(
			array(
				'list_type' => 'users',
				'template'  => '<ul>[users]<li>[user_name] [user_meta key="w4pl_profile_note"]</li>[/users]</ul>',
			)
		);

		$this->assertNoDisallowedMarkup( $html );
		$this->assertStringContainsString( 'Ann note', $html );
	}

	public function test_group_title_from_meta_value_drops_disallowed_markup() {
		update_post_meta( $this->first_post_id(), 'edition', self::DISALLOWED_IMG . 'Edition one' );

		$html = $this->render_posts(
			'[groups]<h3 data-id="[group_id]">[group_title]</h3>[posts][post_title][/posts][/groups]',
			array(
				'groupby'          => 'meta_value',
				'groupby_meta_key' => 'edition',
			)
		);

		$this->assertNoDisallowedMarkup( $html );
		$this->assertStringContainsString( '<h3 data-id="&lt;img src=&quot;x&quot; onerror=&quot;window.w4plMarker=1&quot;&gt;Edition one">', $html );
		$this->assertStringContainsString( '<img src="x">Edition one</h3>', $html );
	}

	public function test_meta_display_value_filter_receives_the_stored_value() {
		update_post_meta( $this->first_post_id(), 'embed', '<iframe src="https://example.com/embed"></iframe>' );

		update_post_meta( $this->first_post_id(), 'subtitle', self::DISALLOWED_SCRIPT . 'Kept' );

		$filter = function ( $display, $value, $meta_key, $tag ) {
			if ( 'embed' === $meta_key && 'post_meta' === $tag ) {
				return $value;
			}
			return $display;
		};
		add_filter( 'w4pl/meta_display_value', $filter, 10, 4 );
		$html = $this->render_posts( '[posts]<div class="sub">[post_meta key="embed"]</div><div class="sub2">[post_meta key="subtitle"]</div>[/posts]' );
		remove_filter( 'w4pl/meta_display_value', $filter, 10 );

		$this->assertStringContainsString( '<div class="sub"><iframe src="https://example.com/embed"></iframe></div>', $html );
		$this->assertStringContainsString( '<div class="sub2">window.w4plMarker=1Kept</div>', $html );
	}

	public function test_grouped_list_prints_tag_text_from_a_post_as_text() {
		update_post_meta( $this->first_post_id(), 'subtitle', '[post_type]' );

		$html = $this->render_posts(
			'[groups]<h3>[group_title]</h3>[posts]<p class="sub">[post_meta key="subtitle"]</p>[/posts][/groups]',
			array( 'groupby' => 'year' )
		);

		$this->assertStringContainsString( '<p class="sub">[post_type]</p>', $html );
		$this->assertStringContainsString( '<h3>2026</h3>', $html );
	}

	public function test_users_posts_default_template_prints_tag_text_in_a_title_as_text() {
		wp_update_post(
			array(
				'ID'         => $this->first_post_id(),
				'post_title' => 'Notes [post_type]',
			)
		);

		$html = $this->render_list(
			array(
				'list_type'      => 'users.posts',
				'post_type'      => array( 'post' ),
				'posts_per_page' => 10,
				'orderby'        => 'post_date',
				'order'          => 'DESC',
			)
		);

		$this->assertStringContainsString( '>Notes [post_type]</a>', $html );
		$this->assertStringContainsString( '>Ann Author</a>', $html );
	}

	public function test_escaped_tag_in_a_grouped_posts_block_prints_as_text() {
		$html = $this->render_posts(
			'[groups]<h3>[group_title]</h3>[posts]<p class="lit">[[post_type]]</p>[/posts][/groups]',
			array( 'groupby' => 'year' )
		);

		$this->assertStringContainsString( '<p class="lit">[post_type]</p>', $html );
	}

	public function test_terms_posts_list_prints_tag_text_from_a_post_as_text() {
		update_post_meta( $this->first_post_id(), 'subtitle', '[post_type]' );

		$html = $this->render_list(
			array(
				'list_type'      => 'terms.posts',
				'terms_taxonomy' => 'category',
				'post_type'      => array( 'post' ),
				'posts_per_page' => 10,
				'template'       => '[terms]<h3>[term_name]</h3>[posts]<p class="sub">[post_meta key="subtitle"]</p>[/posts][/terms]',
			)
		);

		$this->assertStringContainsString( '<p class="sub">[post_type]</p>', $html );
		$this->assertStringContainsString( '<h3>Alpha</h3>', $html );
	}

	public function test_more_link_title_attribute_is_plain_text() {
		wp_update_post(
			array(
				'ID'         => $this->first_post_id(),
				'post_title' => '<abbr title="a data-w4pl=1 b">Release</abbr> notes',
			)
		);

		$html = $this->render_posts( '[posts][more][/posts]' );

		$this->assertStringContainsString( 'title="Continue reading &raquo; Release notes"', $html );
		$this->assertStringNotContainsString( 'title="a data-w4pl=1 b"', $html );
	}

	public function test_author_link_title_attribute_is_escaped() {
		wp_update_user(
			array(
				'ID'           => self::$user_ann,
				'display_name' => 'Ann" data-w4pl="1',
			)
		);

		$html = $this->render_posts( '[posts][author][/posts]' );

		$this->assertStringNotContainsString( 'Ann" data-w4pl="1"', $html );
		$this->assertStringContainsString( 'title="View all posts by Ann&quot; data-w4pl=&quot;1"', $html );
	}

	public function test_attachment_thumbnail_id_from_meta_is_an_integer() {
		$attachment_id = self::factory()->attachment->create_object(
			'w4pl-test.jpg',
			$this->first_post_id(),
			array(
				'post_mime_type' => 'image/jpeg',
				'post_type'      => 'attachment',
			)
		);
		wp_update_attachment_metadata(
			$attachment_id,
			array(
				'width'  => 10,
				'height' => 10,
				'file'   => 'w4pl-test.jpg',
			)
		);
		update_post_meta( $this->first_post_id(), 'photo', $attachment_id . '<b>x</b>' );

		$html = $this->render_posts( '[posts]<i class="att">[attachment_thumbnail meta_key="photo" output="id"]</i>[/posts]' );

		$this->assertStringNotContainsString( '<b>x</b>', $html );
		$this->assertStringContainsString( '<i class="att">' . $attachment_id . '</i>', $html );
	}
}
