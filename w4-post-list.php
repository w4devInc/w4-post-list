<?php
/**
 * Plugin Name: W4 Post List
 * Plugin URI: http://w4dev.com/plugins/w4-post-list
 * Description: This plugin lets you create a list of - Posts, Terms, Users, Terms + Posts and Users + Posts. Outputs are completely customizable using Shortcode, HTML & CSS. Read documentation plugin usage.
 * Version: 2.2.0
 * Author: Shazzad Hossain Khan
 * Author URI: http://w4dev.com/about
 * Text Domain: w4pl
 * Domain Path: /languages
 * Tested up to: 5.3.2
 * Requires at least: 4.0
 * Requires PHP: 5.3
 */

/**
 * Copyright 2011  Shazzad Hossain Khan  (email : sajib1223@gmail.com)
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.

 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */


/* Define current file as plugin file */
if (! defined('W4PL_PLUGIN_FILE')) {
	define('W4PL_PLUGIN_FILE', __FILE__);
}


function w4pl() {
	/* Require the main plug class */
	if (! class_exists('W4_Post_List')) {
		require plugin_dir_path(__FILE__) . 'includes/class-w4-post-list.php';
	}

	return W4_Post_List::instance();
}


/* Initialize on plugins loaded */
add_action('plugins_loaded', 'w4pl_load', 10);
function w4pl_load() {
	w4pl();
}

/* flush rewrite rules */
register_activation_hook(W4PL_PLUGIN_FILE, 'w4pl_activated');
function w4pl_activated() {
	update_option('w4pl_flush_rules', time());
}


# BETA
require plugin_dir_path(__FILE__) . 'w4-blocks.php';
