<?php
/**
 * List Factory Class
 *
 * @package W4_Post_List
 */

/**
 * List factory /**
 */
class W4PL_List_Factory {

	/**
	 * Return list class
	 *
	 * @param  array $options List options.
	 * @return mixed          Instance of W4PL_List or Exception.
	 * @throws Exception      For invalid list type.
	 */
	public static function get_list( $options ) {
		if ( ! isset( $options['id'] ) ) {
			throw new Exception( __( 'Invalid list id', 'w4-post-list' ) );
		} elseif ( ! isset( $options['list_type'] ) ) {
			throw new Exception( __( 'Invalid list type', 'w4-post-list' ) );
		} else {
			$type_suffix = str_replace( array( ' ', '.' ), '_', ucwords( preg_replace( '/[^a-zA-Z]/i', ' ', $options['list_type'] ) ) );
			$class_name  = 'W4PL_List_' . $type_suffix;

			if ( class_exists( $class_name ) ) {
				return new $class_name( $options );
			} else {
				throw new Exception( __( 'Invalid list type', 'w4-post-list' ) );
			}
		}
	}
}
