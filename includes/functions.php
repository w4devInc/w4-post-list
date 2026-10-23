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

/**
 * The style and script handle a list's own CSS and JavaScript print under.
 *
 * @since 3.0.10
 *
 * @param int $list_id List ID.
 * @return string
 */
function w4pl_list_asset_handle( $list_id ) {
	return 'w4pl-list-' . (int) $list_id;
}

/**
 * The list's CSS as a style tag, through the WordPress style API.
 *
 * The CSS is attached to a src-less per-list handle and printed in place,
 * where the raw tag used to be. Every render prints its own tag, as every
 * render did through 3.0.9: a render whose output is thrown away (an excerpt
 * built from `the_content`, an Open Graph scanner) must not use up the tag
 * the visible render needs, so the handle is taken off the printed list
 * first. The data stays readable through `wp_styles()->get_data()`.
 *
 * @since 3.0.10
 *
 * @param int    $list_id List ID.
 * @param string $css     The list's CSS, `[listid]` already replaced.
 * @return string Style tag.
 */
function w4pl_get_list_inline_style( $list_id, $css ) {
	$handle = w4pl_list_asset_handle( $list_id );
	$styles = wp_styles();

	if ( ! wp_style_is( $handle, 'registered' ) ) {
		wp_register_style( $handle, false, array(), W4PL_VERSION );
		wp_add_inline_style( $handle, $css );
	}

	$styles->done = array_diff( $styles->done, array( $handle ) );

	// Not wp_print_styles(): that would also run the once-per-page
	// `wp_print_styles` action from inside the content.
	ob_start();
	$styles->do_items( array( $handle ) );

	return (string) ob_get_clean();
}

/**
 * The list's JavaScript as a script tag, through the WordPress script API.
 *
 * Same contract as w4pl_get_list_inline_style(): printed in place, once per
 * render. On WordPress 6.3+ the tag carries whatever the
 * `wp_inline_script_attributes` filter adds, so a CSP plugin's nonce covers it.
 *
 * @since 3.0.10
 *
 * @param int    $list_id List ID.
 * @param string $js      The list's JavaScript, `[listid]` already replaced.
 * @return string Script tag.
 */
function w4pl_get_list_inline_script( $list_id, $js ) {
	$handle  = w4pl_list_asset_handle( $list_id );
	$scripts = wp_scripts();

	if ( ! wp_script_is( $handle, 'registered' ) ) {
		wp_register_script( $handle, false, array(), W4PL_VERSION, false );
		wp_add_inline_script( $handle, $js );
	}

	$scripts->done = array_diff( $scripts->done, array( $handle ) );

	// Not wp_print_scripts(): that fires the once-per-page `wp_print_scripts`
	// action every time it is called.
	ob_start();
	$scripts->do_items( array( $handle ) );

	return (string) ob_get_clean();
}

/**
 * The page of a list the request asks for, from page{list id}.
 *
 * Normalised to a whole number of one or more; anything that is not a page
 * number is page one.
 *
 * @param  int|string $list_id List id.
 * @return int
 */
function w4pl_get_list_page( $list_id ) {
	$key = 'page' . $list_id;

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only pagination, cast to int.
	if ( ! isset( $_REQUEST[ $key ] ) || ! is_scalar( $_REQUEST[ $key ] ) ) {
		return 1;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only pagination, cast to int.
	return max( 1, absint( wp_unslash( $_REQUEST[ $key ] ) ) );
}

function w4pl_debug( $var, $exit = false ) {
	echo '<pre>';
	print_r( $var );
	echo '</pre>';

	if ( $exit ) {
		exit;
	}
}
