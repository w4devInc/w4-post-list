<?php
/**
 * PHPUnit bootstrap: loads the WP test suite (wp-phpunit) with this plugin active.
 *
 * @package W4_Post_List
 */

require_once dirname( __DIR__ ) . '/vendor-dev/autoload.php';

$w4pl_tests_dir = getenv( 'WP_PHPUNIT__DIR' );
if ( ! $w4pl_tests_dir ) {
	$w4pl_tests_dir = dirname( __DIR__ ) . '/vendor-dev/wp-phpunit/wp-phpunit';
}

putenv( 'WP_PHPUNIT__TESTS_CONFIG=' . __DIR__ . '/wp-tests-config.php' );

require_once $w4pl_tests_dir . '/includes/functions.php';

tests_add_filter(
	'muplugins_loaded',
	function () {
		require dirname( __DIR__ ) . '/w4-post-list.php';
	}
);

require $w4pl_tests_dir . '/includes/bootstrap.php';
