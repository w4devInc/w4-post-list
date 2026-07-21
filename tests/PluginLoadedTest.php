<?php
/**
 * Smoke test: the plugin bootstraps inside the test suite.
 *
 * @package W4_Post_List
 */

class PluginLoadedTest extends WP_UnitTestCase {

	public function test_plugin_main_class_loaded() {
		$this->assertTrue( class_exists( 'W4_Post_List' ) );
		$this->assertTrue( defined( 'W4PL_VERSION' ) );
	}

	public function test_list_post_type_registered() {
		$this->assertTrue( post_type_exists( 'w4pl' ) );
	}

	public function test_postlist_shortcode_registered() {
		$this->assertTrue( shortcode_exists( 'postlist' ) );
	}
}
