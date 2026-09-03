<?php
/**
 * Role filtering for users lists, and the [user_role] template tag.
 *
 * Roles are not a column on wp_users -- they live in the serialized
 * {prefix}capabilities row in wp_usermeta -- so these tests pin both the
 * option -> query-arg -> SQL path and the fail-closed behavior for slugs
 * that match no role.
 *
 * @package W4_Post_List
 */

require_once __DIR__ . '/class-w4pl-snapshot-testcase.php';

class UsersRoleFilterTest extends W4PL_Snapshot_TestCase {

	protected static $editor_id;
	protected static $contributor_id;
	protected static $no_role_id;
	protected static $shop_id;
	protected static $dual_id;

	public static function wpSetUpBeforeClass( $factory ) {
		parent::wpSetUpBeforeClass( $factory );

		// Admin classes are only included under is_admin().
		require_once dirname( __DIR__ ) . '/admin/class-admin-lists-metaboxes.php';

		self::$editor_id      = $factory->user->create(
			array(
				'role'         => 'editor',
				'user_login'   => 'edna',
				'display_name' => 'Edna Editor',
			)
		);
		self::$contributor_id = $factory->user->create(
			array(
				'role'         => 'contributor',
				'user_login'   => 'carl',
				'display_name' => 'Carl Contributor',
			)
		);
		self::$no_role_id     = $factory->user->create(
			array(
				'role'         => '',
				'user_login'   => 'nora',
				'display_name' => 'Nora Norole',
			)
		);

		// An underscore in the slug is what pins esc_like's ordering: without
		// it, LIKE would treat the _ as a single-character wildcard.
		add_role( 'shop_manager', 'Shop Manager', array( 'read' => true ) );
		self::$shop_id = $factory->user->create(
			array(
				'role'         => 'shop_manager',
				'user_login'   => 'sam',
				'display_name' => 'Sam Shop',
			)
		);

		// One user holding two of the filtered roles: the case EXISTS exists for.
		self::$dual_id = $factory->user->create(
			array(
				'role'         => 'editor',
				'user_login'   => 'dana',
				'display_name' => 'Dana Dual',
			)
		);
		$dual = new WP_User( self::$dual_id );
		$dual->add_role( 'contributor' );
	}

	public static function wpTearDownAfterClass() {
		remove_role( 'shop_manager' );
	}

	/**
	 * Run the users query and return the matched user IDs.
	 *
	 * @param array $args Query args.
	 * @return array
	 */
	protected function query_ids( array $args ) {
		$query = new W4PL_Users_Query( $args );
		$query->query();

		return array_map(
			function ( $row ) {
				return (int) $row->ID;
			},
			(array) $query->get_results()
		);
	}

	public function test_single_role_returns_only_that_role() {
		$ids = $this->query_ids( array( 'role__in' => array( 'editor' ) ) );

		$this->assertContains( self::$editor_id, $ids );
		$this->assertNotContains( self::$contributor_id, $ids );
		$this->assertNotContains( self::$user_ann, $ids );
		$this->assertNotContains( self::$no_role_id, $ids );
	}

	public function test_multiple_roles_are_combined_with_or() {
		$ids = $this->query_ids( array( 'role__in' => array( 'editor', 'contributor' ) ) );

		$this->assertContains( self::$editor_id, $ids );
		$this->assertContains( self::$contributor_id, $ids );
		$this->assertNotContains( self::$user_ann, $ids );
		$this->assertNotContains( self::$no_role_id, $ids );
	}

	public function test_empty_role_option_returns_every_user() {
		$ids = $this->query_ids( array( 'role__in' => array() ) );

		$this->assertContains( self::$editor_id, $ids );
		$this->assertContains( self::$contributor_id, $ids );
		$this->assertContains( self::$no_role_id, $ids );
	}

	public function test_blank_entries_are_treated_as_no_filter() {
		$ids = $this->query_ids( array( 'role__in' => array( '' ) ) );

		$this->assertContains( self::$no_role_id, $ids, 'A blank checkbox value is noise, not a filter.' );
	}

	public function test_unknown_role_matches_nobody() {
		$ids = $this->query_ids( array( 'role__in' => array( 'role-that-was-deleted' ) ) );

		$this->assertSame(
			array(),
			$ids,
			'A role slug that no longer exists must fail closed, not fall back to every user.'
		);
	}

	public function test_role_filter_uses_the_blog_prefixed_meta_key() {
		global $wpdb;

		$query = new W4PL_Users_Query( array( 'role__in' => array( 'editor' ) ) );
		$query->query();

		$this->assertStringContainsString( $wpdb->get_blog_prefix() . 'capabilities', $query->request );
		$this->assertStringContainsString( $wpdb->usermeta, $query->request );
	}

	public function test_role_slug_cannot_break_out_of_the_query() {
		global $wpdb;

		$ids = $this->query_ids( array( 'role__in' => array( 'editor" OR 1=1 -- ' ) ) );

		$this->assertSame( array(), $ids, 'A crafted role slug must not widen the result set.' );
		$this->assertEmpty( $wpdb->last_error, 'It must be neutralized, not a broken query.' );

		// An empty result from a syntax error would look identical, so prove
		// the very next query still works.
		$this->assertContains( self::$editor_id, $this->query_ids( array( 'role__in' => array( 'editor' ) ) ) );
	}

	public function test_a_role_slug_that_is_not_registered_matches_nobody() {
		$ids = $this->query_ids( array( 'role__in' => array( 'was-removed-with-its-plugin' ) ) );

		$this->assertSame( array(), $ids );
	}

	public function test_a_non_ascii_role_slug_does_not_drop_the_filter() {
		// sanitize_key() flattens this to an empty string. If the filter were
		// built on it, the list would quietly widen to every user on the site.
		$ids = $this->query_ids( array( 'role__in' => array( '編集者' ) ) );

		$this->assertSame( array(), $ids, 'An unregistered slug must fail closed, not open.' );
		$this->assertNotContains( self::$editor_id, $ids );
	}

	public function test_a_registered_non_ascii_role_still_filters() {
		add_role( '編集者', 'Editor JA', array( 'read' => true ) );
		$ja = self::factory()->user->create( array( 'role' => '編集者', 'display_name' => 'Jun Ja' ) );

		$ids = $this->query_ids( array( 'role__in' => array( '編集者' ) ) );

		remove_role( '編集者' );

		$this->assertSame( array( $ja ), $ids );
	}

	public function test_underscore_in_a_role_slug_is_escaped() {
		$ids = $this->query_ids( array( 'role__in' => array( 'shop_manager' ) ) );

		$this->assertSame( array( self::$shop_id ), $ids );
	}

	public function test_a_user_with_two_matching_roles_is_one_row() {
		$query = new W4PL_Users_Query( array( 'role__in' => array( 'editor', 'contributor' ) ) );
		$query->query();

		$ids = array_map(
			function ( $row ) {
				return (int) $row->ID;
			},
			(array) $query->get_results()
		);

		$this->assertSame( 1, count( array_keys( $ids, self::$dual_id ) ), 'A dual-role user must not be duplicated.' );
	}

	public function test_pagination_counts_are_correct_with_a_role_filter() {
		$query = new W4PL_Users_Query(
			array(
				'role__in' => array( 'editor' ),
				'limit'    => 1,
			)
		);
		$query->query();

		// Edna and Dana both hold editor.
		$this->assertSame( 1, count( (array) $query->get_results() ) );
		$this->assertSame( 2, (int) $query->found_item );
		$this->assertSame( 2, (int) $query->max_num_pages );
	}

	public function test_orderby_falls_back_to_id_when_not_whitelisted() {
		$query = new W4PL_Users_Query(
			array(
				'orderby' => 'ID, (SELECT 1)',
			)
		);
		$query->query();

		$this->assertStringNotContainsString( 'SELECT 1', $query->request );

		// Dropping ORDER BY entirely would leave a LIMIT query with no stable
		// row order, so pagination could repeat and skip users.
		$this->assertStringContainsString( 'ORDER BY ID', $query->request );
		$this->assertNotEmpty( $query->get_results(), 'A rejected orderby must not break the query.' );
	}

	public function test_whitelisted_orderby_is_kept() {
		$query = new W4PL_Users_Query( array( 'orderby' => 'display_name' ) );
		$query->query();

		$this->assertStringContainsString( 'ORDER BY display_name', $query->request );
	}

	public function test_defaults_include_an_empty_users_role() {
		$helper  = new W4PL_Helper_Users();
		$options = $helper->pre_get_options( array() );

		$this->assertArrayHasKey( 'users_role', $options );
		$this->assertSame( array(), $options['users_role'] );
	}

	public function test_option_is_mapped_onto_the_query_args() {
		$list = new stdClass();
		$list->id         = 1;
		$list->options    = array(
			'list_type'  => 'users',
			'users_role' => array( 'editor', 'contributor' ),
		);
		$list->users_args = array();

		$helper = new W4PL_Helper_Users();
		$helper->parse_query_args( $list );

		$this->assertSame( array( 'editor', 'contributor' ), $list->users_args['role__in'] );
	}

	public function test_role_field_offers_every_registered_role() {
		$helper = new W4PL_Helper_Users();
		$fields = $helper->list_edit_form_fields( array(), array( 'list_type' => 'users' ) );

		$this->assertArrayHasKey( 'users_role', $fields );
		$this->assertSame( 'checkbox', $fields['users_role']['type'] );
		$this->assertSame( 'w4pl[users_role][]', $fields['users_role']['name'] . '[]' );

		// Every registered role is offered, including one added at runtime.
		foreach ( array_keys( wp_roles()->get_names() ) as $slug ) {
			$this->assertArrayHasKey( $slug, $fields['users_role']['option'] );
		}
		$this->assertArrayHasKey( 'shop_manager', $fields['users_role']['option'] );
	}

	public function test_role_field_is_absent_for_non_user_lists() {
		$helper = new W4PL_Helper_Users();
		$fields = $helper->list_edit_form_fields( array(), array( 'list_type' => 'posts' ) );

		$this->assertArrayNotHasKey( 'users_role', $fields );
	}

	public function test_role_option_drops_blanks_and_keeps_slugs_intact() {
		$options = W4PL_Admin_Lists_Metaboxes::sanitize_options(
			array(
				'users_role' => array( 'editor', '', 'shop_manager', '編集者' ),
			)
		);

		$this->assertSame(
			array( 'editor', 'shop_manager', '編集者' ),
			array_values( $options['users_role'] ),
			'A slug must survive saving byte for byte, or it will match no role.'
		);
	}

	public function test_role_option_strips_markup() {
		$options = W4PL_Admin_Lists_Metaboxes::sanitize_options(
			array(
				'users_role' => array( '<b>editor</b>' ),
			)
		);

		$this->assertSame( array( 'editor' ), array_values( $options['users_role'] ) );
	}

	public function test_clearing_every_checkbox_removes_the_filter() {
		// Unchecking every box posts no users_role key at all.
		$saved = W4PL_Admin_Lists_Metaboxes::sanitize_options(
			array(
				'list_type'  => 'users',
				'users_role' => array( 'editor' ),
			)
		);
		$this->assertSame( array( 'editor' ), $saved['users_role'] );

		$cleared = W4PL_Admin_Lists_Metaboxes::sanitize_options( array( 'list_type' => 'users' ) );
		$this->assertArrayNotHasKey( 'users_role', $cleared );

		// The list must then render unfiltered, the way the meta rewrite leaves it.
		$options = apply_filters( 'w4pl/pre_get_options', $cleared );
		$this->assertSame( array(), $options['users_role'] );

		$list             = new stdClass();
		$list->id         = 1;
		$list->options    = $options;
		$list->users_args = array();

		$helper = new W4PL_Helper_Users();
		$helper->parse_query_args( $list );

		$this->assertArrayNotHasKey( 'role__in', $list->users_args );
	}

	public function test_role_option_survives_a_non_array_value() {
		$options = W4PL_Admin_Lists_Metaboxes::sanitize_options( array( 'users_role' => 'editor' ) );

		$this->assertSame( array( 'editor' ), array_values( $options['users_role'] ) );
	}

	public function test_a_saved_list_without_the_key_is_unaffected() {
		// Every list saved before 3.0.5 looks like this.
		$options = apply_filters(
			'w4pl/pre_get_options',
			array(
				'list_type'     => 'users',
				'users_orderby' => 'display_name',
			)
		);

		$this->assertSame( array(), $options['users_role'] );

		$list             = new stdClass();
		$list->id         = 1;
		$list->options    = $options;
		$list->users_args = array();

		$helper = new W4PL_Helper_Users();
		$helper->parse_query_args( $list );

		$this->assertArrayNotHasKey( 'role__in', $list->users_args );
	}

	public function test_rendered_list_is_filtered_by_role() {
		$html = $this->render_list(
			array(
				'list_type'     => 'users',
				'users_role'    => array( 'editor' ),
				'users_orderby' => 'display_name',
				'users_order'   => 'ASC',
				'template'      => '<ul>[users]<li>[user_name]</li>[/users]</ul>',
			)
		);

		$this->assertStringContainsString( 'Edna Editor', $html );
		$this->assertStringNotContainsString( 'Carl Contributor', $html );
		$this->assertStringNotContainsString( 'Ann Author', $html );
	}

	public function test_user_role_tag_outputs_the_role_name() {
		$html = $this->render_list(
			array(
				'list_type'     => 'users',
				'users_role'    => array( 'editor' ),
				'users_orderby' => 'display_name',
				'users_order'   => 'ASC',
				'template'      => '<ul>[users]<li>[user_name] &mdash; [user_role]</li>[/users]</ul>',
			)
		);

		$this->assertStringContainsString( 'Edna Editor &mdash; Editor', $html );
	}

	public function test_user_role_tag_can_output_slugs() {
		$html = $this->render_list(
			array(
				'list_type'  => 'users',
				'users_role' => array( 'contributor' ),
				'template'   => '<ul>[users]<li class="role-[user_role format="slug"]">[user_name]</li>[/users]</ul>',
			)
		);

		$this->assertStringContainsString( 'role-contributor', $html );
	}

	public function test_user_role_tag_is_empty_for_a_user_without_a_role() {
		$html = $this->render_list(
			array(
				'list_type'  => 'users',
				'users__in'  => (string) self::$no_role_id,
				'template'   => '<ul>[users]<li>[user_name]|[user_role]|</li>[/users]</ul>',
			)
		);

		$this->assertStringContainsString( 'Nora Norole||', $html );
	}

	public function test_user_role_tag_is_registered_in_the_user_group() {
		$tags = w4pl_get_shortcodes();

		$this->assertArrayHasKey( 'user_role', $tags );
		$this->assertSame( 'User', $tags['user_role']['group'] );
		$this->assertNotEmpty( $tags['user_role']['output'] );
	}
}
