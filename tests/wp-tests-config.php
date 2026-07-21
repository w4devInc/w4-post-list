<?php
/**
 * WordPress test suite configuration, environment-driven.
 *
 * Local (docker): WP_TESTS_DB_HOST=w4pl_mysql etc. are passed by the runner.
 * CI: a mysql service on 127.0.0.1 with the credentials below as defaults.
 *
 * @package W4_Post_List
 */

$w4pl_root = dirname( __DIR__ );

define( 'ABSPATH', ( getenv( 'WP_ABSPATH' ) ? getenv( 'WP_ABSPATH' ) : $w4pl_root . '/wp' ) . '/' );

define( 'DB_NAME', getenv( 'WP_TESTS_DB_NAME' ) ? getenv( 'WP_TESTS_DB_NAME' ) : 'w4pl_tests' );
define( 'DB_USER', getenv( 'WP_TESTS_DB_USER' ) ? getenv( 'WP_TESTS_DB_USER' ) : 'root' );
define( 'DB_PASSWORD', getenv( 'WP_TESTS_DB_PASSWORD' ) ? getenv( 'WP_TESTS_DB_PASSWORD' ) : 'root' );
define( 'DB_HOST', getenv( 'WP_TESTS_DB_HOST' ) ? getenv( 'WP_TESTS_DB_HOST' ) : '127.0.0.1' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

$table_prefix = 'wptests_';

define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Test Blog' );
define( 'WP_PHP_BINARY', 'php' );
define( 'WP_DEBUG', true );
