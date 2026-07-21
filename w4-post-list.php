<?php
/**
 * Plugin Name: W4 Post List
 * Plugin URI: https://w4dev.com/plugins/w4-post-list
 * Description: Create lists of posts, terms and users — grouped archives, taxonomy indexes and user directories — placed anywhere with the block, [postlist] shortcode or widget.
 * Version: 2.8.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: Shazzad Hossain Khan
 * Author URI: https://shazzad.me
 * Text Domain: w4-post-list
 * Domain Path: /languages
 *
 * @package W4_Post_List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define current file as plugin file.
if ( ! defined( 'W4PL_PLUGIN_FILE' ) ) {
	define( 'W4PL_PLUGIN_FILE', __FILE__ );
}

// Load dependencies.
require plugin_dir_path( W4PL_PLUGIN_FILE ) . '/vendor/autoload.php';

/**
 * Returns the main instance of Plugin.
 *
 * @return W4_Post_List
 */
function w4pl() {
	/* Require the main plug class */
	if ( ! class_exists( 'W4_Post_List' ) ) {
		require plugin_dir_path( W4PL_PLUGIN_FILE ) . 'includes/class-w4-post-list.php';
	}

	return W4_Post_List::instance();
}

/**
 * Bootstrap the plugin
 */
function w4pl_load() {
	w4pl();
}
add_action( 'plugins_loaded', 'w4pl_load', 10 );

/**
 * Run when plugin gets activated
 *
 * @param bool $network_wide Whether the plugin is being activated network-wide.
 */
function w4pl_activated( $network_wide = false ) {
	update_option( 'w4pl_flush_rules', time() );

	// Per-site onboarding; skipped on network-wide activation so bulk
	// activation does not touch every site.
	if ( ! $network_wide ) {
		update_option( 'w4pl_welcome_pending', time(), false );
		update_option( 'w4pl_maybe_create_sample', 1, false );
	}
}
register_activation_hook( W4PL_PLUGIN_FILE, 'w4pl_activated' );

// Block editor.
require plugin_dir_path( W4PL_PLUGIN_FILE ) . 'blocks.php';

// Appsero.
require plugin_dir_path( W4PL_PLUGIN_FILE ) . 'appsero.php';
