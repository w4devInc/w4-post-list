<?php
/**
 * Starter template registry, copy-on-select application, and legacy compat.
 *
 * @package W4_Post_List
 */

require_once __DIR__ . '/class-w4pl-snapshot-testcase.php';

class StarterTemplatesTest extends W4PL_Snapshot_TestCase {

	public static function wpSetUpBeforeClass( $factory ) {
		parent::wpSetUpBeforeClass( $factory );
		require_once dirname( __DIR__ ) . '/admin/class-admin-list-editor.php';
	}

	// ----- Registry validity -----

	public function test_registry_has_eight_starters_covering_all_list_types() {
		$registry = W4PL_Starter_Templates::get_registry();

		$this->assertGreaterThanOrEqual( 8, count( $registry ) );

		$types = wp_list_pluck( $registry, 'list_type' );
		foreach ( array( 'posts', 'terms', 'users', 'terms.posts', 'users.posts' ) as $type ) {
			$this->assertContains( $type, $types, "No starter covers list type $type" );
		}
	}

	public function test_registry_entries_are_well_formed() {
		foreach ( W4PL_Starter_Templates::get_registry() as $id => $starter ) {
			foreach ( array( 'label', 'description', 'list_type', 'template', 'css', 'options', 'thumbnail' ) as $key ) {
				$this->assertArrayHasKey( $key, $starter, "$id missing $key" );
			}
			$this->assertNotEmpty( $starter['template'], "$id has empty template" );
			$this->assertStringNotContainsString( '&lt;', $starter['template'], "$id template is entity-encoded" );
			$this->assertIsArray( $starter['options'] );
		}
	}

	public function test_malformed_filter_entries_are_dropped() {
		add_filter(
			'w4pl/starter_templates',
			function ( $starters ) {
				$starters['broken-no-template'] = array( 'list_type' => 'posts' );
				$starters['broken-not-array']   = 'just a string';
				$starters['minimal-valid']      = array(
					'list_type' => 'posts',
					'template'  => '[posts][post_title][/posts]',
				);
				return $starters;
			}
		);

		$registry = W4PL_Starter_Templates::get_registry();

		$this->assertArrayNotHasKey( 'broken-no-template', $registry );
		$this->assertArrayNotHasKey( 'broken-not-array', $registry );
		$this->assertArrayHasKey( 'minimal-valid', $registry );
		$this->assertSame( '', $registry['minimal-valid']['css'], 'Missing keys normalized to defaults' );
		$this->assertSame( array(), $registry['minimal-valid']['options'] );
	}

	public function test_crafted_array_selection_does_not_fatal() {
		$options = W4PL_Starter_Templates::apply(
			array(
				'list_type'        => 'posts',
				'template'         => '<p>keep me</p>',
				'starter_template' => array( 'x' ),
			)
		);

		$this->assertArrayNotHasKey( 'starter_template', $options );
		$this->assertSame( '<p>keep me</p>', $options['template'] );
	}

	public function test_successful_apply_increments_usage_counter() {
		delete_option( W4PL_Stats::OPTION );

		W4PL_Starter_Templates::apply(
			array(
				'list_type'        => 'posts',
				'starter_template' => 'simple-list',
			)
		);
		$this->assertSame( 1, W4PL_Stats::get( 'starter_applied' ) );

		// Consumed-but-not-applied selections do not count.
		W4PL_Starter_Templates::apply(
			array(
				'list_type'        => 'posts',
				'starter_template' => 'does-not-exist',
			)
		);
		$this->assertSame( 1, W4PL_Stats::get( 'starter_applied' ) );
	}

	public function test_registry_templates_use_only_registered_tags() {
		$registered   = array_keys( w4pl_get_shortcodes() );
		$registered[] = 'postlist';

		foreach ( W4PL_Starter_Templates::get_registry() as $id => $starter ) {
			preg_match_all( '/\[\/?([a-zA-Z_][a-zA-Z0-9_]*)[\s\]]/', $starter['template'], $m );
			$unknown = array_diff( array_unique( $m[1] ), $registered );

			$this->assertSame( array(), array_values( $unknown ), "Unregistered tags in starter $id: " . implode( ', ', $unknown ) );
		}
	}

	public function test_registry_css_rules_are_scoped_to_the_list() {
		foreach ( W4PL_Starter_Templates::get_registry() as $id => $starter ) {
			if ( '' === trim( $starter['css'] ) ) {
				continue;
			}

			foreach ( explode( "\n", $starter['css'] ) as $line ) {
				$line = trim( $line );
				// Selector lines open a block and are not properties/closers/at-rules.
				if ( '' === $line || '}' === $line || 0 === strpos( $line, '@' ) || false === strpos( $line, '{' ) ) {
					continue;
				}
				$this->assertStringStartsWith( '#w4pl-list-[listid]', $line, "Unscoped CSS selector in $id: $line" );
			}
		}
	}

	public function test_select_options_filter_by_list_type() {
		$posts_choices = W4PL_Starter_Templates::options_for_select( 'posts' );
		$terms_choices = W4PL_Starter_Templates::options_for_select( 'terms' );

		$this->assertArrayHasKey( '', $posts_choices, 'Placeholder choice missing' );
		$this->assertArrayHasKey( 'thumbnail-cards', $posts_choices );
		$this->assertArrayNotHasKey( 'category-index', $posts_choices );
		$this->assertArrayHasKey( 'category-index', $terms_choices );
		$this->assertArrayNotHasKey( 'thumbnail-cards', $terms_choices );
	}

	// ----- Copy-on-select application -----

	public function test_apply_copies_template_css_and_related_options() {
		$options = W4PL_Starter_Templates::apply(
			array(
				'list_type'        => 'posts',
				'starter_template' => 'year-archive',
				'template'         => '<p>existing content</p>',
			)
		);

		$this->assertArrayNotHasKey( 'starter_template', $options, 'One-shot key must be consumed' );
		$this->assertStringContainsString( '[groups]', $options['template'], 'Starter template applied' );
		$this->assertStringNotContainsString( 'existing content', $options['template'], 'Overwrites silently by design' );
		$this->assertStringContainsString( '#w4pl-list-[listid]', $options['css'] );
		$this->assertSame( 'year', $options['groupby'], 'Related option set' );
	}

	public function test_apply_consumes_empty_and_unknown_selections_without_changes() {
		$base = array(
			'list_type'        => 'posts',
			'template'         => '<p>keep me</p>',
			'starter_template' => '',
		);

		$options = W4PL_Starter_Templates::apply( $base );
		$this->assertArrayNotHasKey( 'starter_template', $options );
		$this->assertSame( '<p>keep me</p>', $options['template'] );

		$base['starter_template'] = 'does-not-exist';
		$options                  = W4PL_Starter_Templates::apply( $base );
		$this->assertArrayNotHasKey( 'starter_template', $options );
		$this->assertSame( '<p>keep me</p>', $options['template'] );
	}

	public function test_apply_refuses_list_type_mismatch() {
		$options = W4PL_Starter_Templates::apply(
			array(
				'list_type'        => 'terms',
				'template'         => '<p>keep me</p>',
				'starter_template' => 'thumbnail-cards',
			)
		);

		$this->assertSame( '<p>keep me</p>', $options['template'] );
	}

	public function test_apply_ignores_disallowed_option_keys_from_filtered_starters() {
		add_filter(
			'w4pl/starter_templates',
			function ( $starters ) {
				$starters['evil'] = array(
					'label'       => 'Evil',
					'description' => '',
					'list_type'   => 'posts',
					'template'    => '[posts][post_title][/posts]',
					'css'         => '',
					'options'     => array(
						'id'             => 999,
						'posts_per_page' => 5,
					),
				);
				return $starters;
			}
		);

		$options = W4PL_Starter_Templates::apply(
			array(
				'list_type'        => 'posts',
				'id'               => 42,
				'starter_template' => 'evil',
			)
		);

		$this->assertSame( 42, $options['id'], 'Disallowed option key must not apply' );
		$this->assertSame( 5, $options['posts_per_page'], 'Allowed option key applies' );
	}

	public function test_pending_starter_applies_on_save_and_is_never_persisted() {
		$options = apply_filters(
			'w4pl/pre_save_options',
			array(
				'list_type'        => 'posts',
				'starter_template' => 'simple-list',
			)
		);

		$this->assertArrayNotHasKey( 'starter_template', $options );
		$this->assertArrayNotHasKey( '_starter_applied', $options, 'Editor-only marker must never be persisted' );
		$this->assertStringContainsString( 'w4pl-simple-list', $options['template'] );
		$this->assertSame( W4PL_Options_Migrator::OPTIONS_VERSION, $options['options_version'] );
	}

	public function test_editor_apply_sets_confirmation_marker_and_field_notice() {
		$editor = new W4PL_List_Editor(
			array(
				'id'               => 1,
				'list_type'        => 'posts',
				'starter_template' => 'thumbnail-cards',
			)
		);

		$this->assertSame( 'thumbnail-cards', $editor->options['_starter_applied'], 'Marker must survive the editor filter chain' );

		$fields = apply_filters( 'w4pl/list_edit_form_fields', array(), $editor->options );

		$this->assertArrayHasKey( 'starter_applied_notice', $fields );
		$this->assertStringContainsString( 'data-show-tab="w4pl_field_group_template"', $fields['starter_applied_notice']['html'] );
		$this->assertStringContainsString( 'Thumbnail cards', $fields['starter_applied_notice']['html'] );
	}

	public function test_editor_applies_starter_before_field_rendering() {
		$editor = new W4PL_List_Editor(
			array(
				'id'               => 1,
				'list_type'        => 'posts',
				'starter_template' => 'thumbnail-cards',
			)
		);

		$this->assertStringContainsString( 'w4pl-cards', $editor->options['template'] );
		$this->assertSame( 9, $editor->options['posts_per_page'] );
		$this->assertArrayNotHasKey( 'starter_template', $editor->options );
	}

	// ----- Editor field behavior -----

	public function test_lists_without_legacy_preset_get_starter_field() {
		$options = apply_filters( 'w4pl/pre_get_options', array( 'list_type' => 'posts' ) );
		$fields  = apply_filters(
			'w4pl/list_edit_form_fields',
			array( 'before_field_group_template' => array( 'html' => 'x' ) ),
			$options
		);

		$this->assertArrayHasKey( 'starter_template', $fields );
		$this->assertArrayNotHasKey( 'preset', $fields );
		$this->assertArrayHasKey( 'before_field_group_template', $fields, 'Template group must stay visible' );
		$this->assertSame( 'Start from a template', $fields['starter_template']['label'] );
	}

	public function test_lists_with_legacy_preset_keep_old_field_and_hiding() {
		$options = apply_filters(
			'w4pl/pre_get_options',
			array(
				'list_type' => 'posts',
				'preset'    => 'simple_list',
			)
		);
		$fields  = apply_filters(
			'w4pl/list_edit_form_fields',
			array( 'before_field_group_template' => array( 'html' => 'x' ) ),
			$options
		);

		$this->assertArrayHasKey( 'preset', $fields );
		$this->assertArrayNotHasKey( 'starter_template', $fields );
		$this->assertArrayNotHasKey( 'before_field_group_template', $fields, 'Legacy preset lists keep hiding the template group' );
	}

	// ----- Legacy render-time behavior frozen -----

	public function test_legacy_stored_preset_still_renders_via_override() {
		$html = $this->render_list(
			array(
				'list_type'      => 'posts',
				'post_type'      => array( 'post' ),
				'posts_per_page' => 10,
				'orderby'        => 'post_date',
				'order'          => 'DESC',
				'preset'         => 'simple_list',
			)
		);

		$this->assertMatchesHtmlSnapshot( 'legacy-preset-simple-list', $html );
	}

	// ----- Every starter renders -----

	public function starter_provider() {
		$cases = array();
		foreach ( W4PL_Starter_Templates::get_registry() as $id => $starter ) {
			$cases[ $id ] = array( $id );
		}
		return $cases;
	}

	/**
	 * @dataProvider starter_provider
	 */
	public function test_starter_renders_to_stable_output( $id ) {
		$starter = W4PL_Starter_Templates::get( $id );

		$options = W4PL_Starter_Templates::apply(
			array(
				'list_type'        => $starter['list_type'],
				'post_type'        => array( 'post' ),
				'starter_template' => $id,
			)
		);

		$html = $this->render_list( $options );

		$this->assertStringNotContainsString( '[post_title]', $html, "Unparsed tags in starter $id output" );
		$this->assertMatchesHtmlSnapshot( 'starter-' . $id, $html );
	}
}
