<?php
/**
 * Preview endpoint authorization (nonce + capability).
 *
 * @package W4_Post_List
 *
 * @group ajax
 */

class PreviewAjaxTest extends WP_Ajax_UnitTestCase {

	protected static $list_id;

	public static function wpSetUpBeforeClass( $factory ) {
		require_once dirname( __DIR__ ) . '/admin/class-admin-lists-metaboxes.php';
		require_once dirname( __DIR__ ) . '/admin/class-admin-preview.php';

		self::$list_id = $factory->post->create(
			array(
				'post_type'   => 'w4pl',
				'post_status' => 'publish',
			)
		);
	}

	public function set_up() {
		parent::set_up();

		// Hooks are restored to the run-global snapshot after every test, so
		// the ajax action must be registered per-test, after the backup.
		new W4PL_Admin_Preview();
	}

	public function test_missing_nonce_is_rejected() {
		$this->_setRole( 'administrator' );
		$_POST['w4pl'] = array( 'id' => self::$list_id );

		$this->expectException( 'WPAjaxDieStopException' );
		$this->_handleAjax( 'w4pl_list_preview' );
	}

	public function test_insufficient_capability_is_rejected() {
		$this->_setRole( 'subscriber' );
		$_POST['nonce'] = wp_create_nonce( 'w4pl_preview' );
		$_POST['w4pl']  = array(
			'id'        => self::$list_id,
			'list_type' => 'posts',
		);

		try {
			$this->_handleAjax( 'w4pl_list_preview' );
		} catch ( WPAjaxDieContinueException $e ) {
			// Expected: wp_send_json_error ends with die.
			unset( $e );
		}

		$response = json_decode( $this->_last_response, true );
		$this->assertNotNull( $response, 'RAW RESPONSE: ' . substr( $this->_last_response, 0, 400 ) );
		$this->assertFalse( $response['success'] );
	}

	public function test_authorized_request_returns_rendered_html() {
		$this->_setRole( 'administrator' );
		$_POST['nonce'] = wp_create_nonce( 'w4pl_preview' );
		$_POST['w4pl']  = array(
			'id'             => self::$list_id,
			'list_type'      => 'posts',
			'post_type'      => array( 'post' ),
			'posts_per_page' => 5,
		);

		try {
			$this->_handleAjax( 'w4pl_list_preview' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$response = json_decode( $this->_last_response, true );
		$this->assertNotNull( $response, 'RAW RESPONSE: ' . substr( $this->_last_response, 0, 400 ) );
		$this->assertTrue( $response['success'] );
		$this->assertStringContainsString( 'w4pl', $response['data']['html'] );
	}
}
