<?php
/**
 * Gutenberg blocks related function
 *
 * @package W4_Post_List
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register gutenberg block function
 */
function w4pl_register_block() {
	if ( ! function_exists( 'register_block_type' ) ) {
		// Gutenberg is not active.
		return;
	}

	// automatically load dependencies and version.
	$asset_file = include W4PL_DIR . 'assets/block/build.asset.php';

	wp_register_script(
		'w4pl_block',
		W4PL_URL . 'assets/block/build.js',
		$asset_file['dependencies'],
		$asset_file['version'],
		true
	);

	register_block_type(
		'w4-post-list/postlist',
		array(
			'title'           => __( 'W4 Post List', 'w4-post-list' ),
			'description'     => __( 'Display a list from w4 post list plugin.', 'w4-post-list' ),
			'supports'        => array(
				'align' => false,
				'html'  => false,
			),
			'editor_script'   => 'w4pl_block',
			'attributes'      => array(
				'listId'    => array(
					'type'    => 'string',
					'default' => '0',
				),
				'className' => array(
					'type'    => 'string',
					'default' => '',
				),
			),
			'render_callback' => 'w4pl_render_block_postlist',
		)
	);

	if ( function_exists( 'wp_set_script_translations' ) ) {
		wp_set_script_translations( 'w4pl_block', 'w4-post-list' );
	}
}
add_action( 'init', 'w4pl_register_block' );

/**
 * Render postlist function
 *
 * @param array $attributes List Attributes for rendering postlist.
 */
function w4pl_render_block_postlist( $attributes ) {
	if ( ! empty( $attributes['listId'] ) ) {
		// include className if used.
		$before = '';
		$after  = '';
		if ( ! empty( $attributes['className'] ) ) {
			$before = sprintf( '<div class="%s">', $attributes['className'] );
			$after  = '</div>';
		}

		return $before . do_shortcode( '[postlist ' . $attributes['listId'] . ']' ) . $after;
	} else {
		return __( 'No list selected.', 'w4-post-list' );
	}
}
