<?php
/**
 * Functions.
 *
 * @package W4_Post_List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get template tags.
 *
 * @return array Array of template tags
 */
function w4pl_get_shortcodes() {
	$shortcodes = array(
		'posts'  => array(
			'group'  => 'Main',
			'code'   => '[posts]' . "\n\n" . '[/posts]',
			'output' => __( 'Posts section', 'w4-post-list' ),
		),
		'terms'  => array(
			'group'  => 'Main',
			'code'   => '[terms]' . "\n\n" . '[/terms]',
			'output' => __( 'Terms section', 'w4-post-list' ),
		),
		'users'  => array(
			'group'  => 'Main',
			'code'   => '[users]' . "\n\n" . '[/users]',
			'output' => __( 'Users section', 'w4-post-list' ),
		),
		'groups' => array(
			'group'  => 'Main',
			'code'   => '[groups]' . "\n\n" . '[/groups]',
			'output' => __( 'Groups section', 'w4-post-list' ),
		),
		'nav'    => array(
			'group'      => 'Main',
			'code'       => '[nav type="plain" ajax="1" prev_text="" next_text=""]',
			'parameters' => array(
				'type'      => array(
					'choices' => array(
						'plain',
						'list',
						'nav',
					),
					'desc'    => __( 'Type of pagination.', 'w4-post-list' ),
				),
				'ajax'      => array(
					'choices' => array(
						'0',
						'1',
					),
					'desc'    => __( 'Enable ajax.', 'w4-post-list' ),
				),
				'prev_text' => array(
					'desc' => __( 'Text for previous button.', 'w4-post-list' ),
				),
				'next_text' => array(
					'desc' => __( 'Text for next button.', 'w4-post-list' ),
				),
			),
			'output'     => __( 'Pagination section', 'w4-post-list' ),
		),
	);

	return apply_filters( 'w4pl/get_shortcodes', $shortcodes );
}

/**
 * Version string used for cache busting plugin assets.
 *
 * @return string
 */
function w4pl_asset_version() {
	// While debugging locally, bypass browser caches.
	if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
		return (string) time();
	}

	return W4PL_VERSION;
}

/**
 * Enqueue the front-end AJAX pagination script.
 *
 * Called from W4PL_List::navigation() at render time, so the script only
 * reaches pages where a list actually rendered [nav ajax="1"] links. Passing
 * the full arguments means the handle self-registers in contexts where
 * `wp_enqueue_scripts` never fires (REST block renderer, the editor preview),
 * instead of being silently dropped.
 *
 * Safe to call repeatedly: WP_Dependencies keeps one entry per handle, so N
 * lists on a page still add up to a single script tag.
 */
function w4pl_enqueue_ajax_nav_script() {
	wp_enqueue_script(
		'w4pl-ajax-nav',
		W4PL_URL . 'assets/js/list-ajax-nav.js',
		array(),
		w4pl_asset_version(),
		true
	);

	/*
	 * A list rendered after wp_print_footer_scripts (wp_footer, priority 20)
	 * enqueues into a queue nothing will flush any more. Print the tag here
	 * instead - through 2.x the inline snippet was part of the returned HTML,
	 * so it always shipped. WP_Scripts marks the handle done, so several late
	 * lists still emit a single script tag.
	 */
	if ( did_action( 'wp_print_footer_scripts' ) ) {
		wp_print_scripts( 'w4pl-ajax-nav' );
	}
}

function w4pl_debug( $var, $exit = false ) {
	echo '<pre>';
	print_r( $var );
	echo '</pre>';

	if ( $exit ) {
		exit;
	}
}
