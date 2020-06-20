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
			'group' => 'Main',
			'code'  => '[posts]' . "\n\n" . '[/posts]',
			'output'=> __( 'Posts section', 'w4-post-list' )
		),
		'terms'  => array(
			'group' => 'Main',
			'code'  => '[terms]' . "\n\n" . '[/terms]',
			'output'=> __( 'Terms section', 'w4-post-list' )
		),
		'users'  => array(
			'group' => 'Main',
			'code'  => '[users]' . "\n\n" . '[/users]',
			'output'=> __( 'Users section', 'w4-post-list' )
		),
		'groups' => array(
			'group' => 'Main',
			'code'  => '[groups]' . "\n\n" . '[/groups]',
			'output'=> __( 'Groups section', 'w4-post-list' )
		),
		'nav'    => array(
			'group' => 'Main',
			'code'  => '[nav type="plain" ajax="1" prev_text="" next_text=""]',
			'parameters' => array(
				'type' => array(
					'choices' => array(
						'plain',
						'list',
						'nav'
					),
					'desc' => __( 'Type of pagination.', 'w4-post-list' )
				),
				'ajax' => array(
					'choices' => array(
						'0',
						'1'
					),
					'desc' => __( 'Enable ajax.', 'w4-post-list' )
				),
				'prev_text' => array(
					'desc' => __( 'Text for previous button.', 'w4-post-list' )
				),
				'next_text' => array(
					'desc' => __( 'Text for next button.', 'w4-post-list' )
				)
			),
			'output' => __( 'Pagination section', 'w4-post-list' )
		),
	);

	return apply_filters( 'w4pl/get_shortcodes', $shortcodes );
}
