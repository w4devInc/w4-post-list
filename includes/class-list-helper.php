<?php
/**
 * List helper class
 *
 * @class W4_Post_List
 * @package W4_Post_List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// TODO - move all methods to other class, and discard this file.
/**
 * List helper
 */
class W4PL_List_Helper {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'w4pl/pre_save_options', array( $this, 'pre_save_options' ) );
		add_filter( 'w4pl/pre_get_options', array( $this, 'pre_get_options' ), 5 );
		add_filter( 'w4pl/list_edit_form_html', array( $this, 'list_edit_form_html' ), 5, 3 );
	}

	public static function list_edit_form_html( $output, $fields, $options ) {
		if ( isset( $options['tab_id'] ) ) {
			$output = str_replace( 'id="' . $options['tab_id'] . '" class="', 'id="' . $options['tab_id'] . '" class="w4pl_active ', $output );
		}

		return $output;
	}

	public function pre_save_options( $options ) {
		if ( isset( $options['post_type'] ) && 'attachment' == $options['post_type'] ) {
			unset( $options['post_status'] );
		}
		if ( isset( $options['template'] ) ) {
			$options['template'] = apply_filters( 'w4pl/pre_save_template', $options['template'], $options );
		}

		return $options;
	}

	public function pre_get_options( $options ) {
		if ( ! isset( $options ) || ! is_array( $options ) ) {
			$options = array();
		}

		/* Version 1.6.7 List Compat */
		if ( isset( $options['template_loop'] ) && ! empty( $options['template_loop'] ) ) {
			if (
				isset( $options['template'] )
				&& ! preg_match( '/\[posts\](.*?)\[\/posts\]/sm', $options['template'] )
				&& preg_match( '/\[loop\]/sm', $options['template'], $match )
			) {
				$options['template'] = str_replace( $match[0], '[posts]' . $options['template_loop'] . '[/posts]', $options['template'] );
			} elseif ( empty( $options['template'] ) ) {
				$options['template'] = str_replace( '[loop]', '[posts]' . $options['template_loop'] . '[/posts]', $options['template'] );
			}

			unset( $options['template_loop'] );
		}

		$options = wp_parse_args(
			$options,
			array(
				'id'        => md5( microtime() . rand() ),
				'tab_id'    => 'w4pl_field_group_type',
				'list_type' => 'posts',
				'template'  => ''
			)
		);

		if ( isset( $options['template'] ) ) {
			$options['template'] = apply_filters( 'w4pl/pre_get_template', $options['template'], $options );
		}

		return $options;
	}
}
