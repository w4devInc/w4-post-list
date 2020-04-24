<?php
function w4pl_register_block() {
	if ( ! function_exists( 'register_block_type' ) ) {
		// Gutenberg is not active.
		return;
	}

    // automatically load dependencies and version
    $asset_file = include( w4pl()->plugin_dir() . 'build/index.asset.php');

    wp_register_script(
        'w4pl_block',
        w4pl()->plugin_url() . 'build/index.js',
        $asset_file['dependencies'],
        $asset_file['version']
    );

    register_block_type(
		'w4-post-list/postlist',
		array(
	        'editor_script' => 'w4pl_block',
			'attributes' => array(
				'listId' => array(
					'type' => 'string',
					'default' => 0
				),
				'className' => array(
					'type' => 'string',
					'default' => ''
				)
			),
			'render_callback' => 'w4pl_render_block_postlist'
	    )
	);

	if ( function_exists( 'wp_set_script_translations' ) ) {
		wp_set_script_translations( 'w4pl_block', 'w4pl' );
	}
}
add_action( 'init', 'w4pl_register_block' );

function w4pl_render_block_postlist( $attributes ) {
	if ( ! empty( $attributes['listId'] ) ) {
		// include className if used
		$before = $after = '';
		if ( ! empty( $attributes['className'] ) ) {
			$before = sprintf( '<div class="%s">', $attributes['className'] );
			$after = '</div>';
		}

		return $before . do_shortcode( '[postlist '. $attributes['listId'] .']' ) . $after;
	} else {
		return __( 'No list' );
	}
}
