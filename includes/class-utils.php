<?php
/**
 * Utility class
 *
 * @class W4PL_Utils
 * @package W4_Post_List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Utility class
 *
 * @class W4PL_Utils
 */
class W4PL_Utils {

	/**
	 * Store log
	 *
	 * @param  string $str log message
	 */
	public static function log( $str = '' ) {
		do_action( 'w4pl/log', $str );
	}

	/**
	 * Prepare a stored meta value for display in a list template.
	 *
	 * Meta values are free-form, so they get the markup rules WordPress
	 * applies to post content. Arrays and objects have no single display
	 * value and print nothing.
	 *
	 * @param  mixed  $value    Meta value.
	 * @param  string $meta_key Meta key the value was read from.
	 * @param  string $tag      Template tag printing it, e.g. "post_meta".
	 * @return string
	 */
	public static function meta_display_value( $value, $meta_key = '', $tag = '' ) {
		$display = '';
		if ( is_scalar( $value ) ) {
			$display = wp_kses_post( (string) $value );
		}

		/**
		 * Filters a meta value as the meta template tags print it.
		 *
		 * @since 3.1.1
		 *
		 * @param string $display  Value after WordPress's post content markup rules.
		 * @param mixed  $value    Value as stored.
		 * @param string $meta_key Meta key the value was read from.
		 * @param string $tag      Template tag printing it: post_meta, user_meta,
		 *                         post_author_meta or group_title.
		 */
		return apply_filters( 'w4pl/meta_display_value', $display, $value, $meta_key, $tag );
	}

	public static function order_by_position( $a, $b ) {
		if ( ! isset( $a['position'] ) || ! isset( $b['position'] ) ) {
			return -1;
		}
		if ( $a['position'] == $b['position'] ) {
			return 0;
		}
		return ( $a['position'] < $b['position'] ) ? -1 : 1;
	}

	public static function p( $a ) {
		echo "\r\n";
		echo '<pre style="background-color:#fff;">';
		echo "\r\n";
		print_r( $a );
		echo "\r\n";
		echo '</pre>';
		echo "\r\n";
	}

	public static function d( $a ) {
		self::p( $a );
		die();
	}

	public static function taxonomies_options() {
		global $wp_taxonomies;

		$return = array();
		foreach ( $wp_taxonomies as $taxonomy => $attr ) {
			$return[ $taxonomy ] = $attr->label;
		}

		return $return;
	}

	public static function filter_multi_row_submit( $rows ) {
		if ( is_object( $rows ) ) {
			$rows = get_object_vars( $rows );
		}

		$items = array();
		if ( ! empty( $rows ) && is_array( $rows ) ) {
			$keys = array_keys( $rows );
			// raw formatted data
			foreach ( array_keys( $rows[ $keys[0] ] ) as $i ) {
				$row = array();
				foreach ( $keys as $key ) {
					$row[ $key ] = isset( $rows[ $key ][ $i ] ) ? $rows[ $key ][ $i ] : '';
				}

				$items[] = $row;
			}
		}

		if ( empty( $items ) ) {
			$items = array();
		}

		return $items;
	}

	/* Retrive latest updates about Post List plugin */
	public static function plugin_news( $echo = true, $refresh = false ) {
		$transient     = 'w4pl_plugin_news';
		$transient_old = $transient . '_old';
		$expiration    = 7200;

		$output = get_transient( $transient );

		if ( $refresh || ! $output || empty( $output ) ) {
			$request = wp_remote_request( 'http://w4dev.com/w4pl.txt' );
			$content = wp_remote_retrieve_body( $request );

			if ( is_wp_error( $content ) ) {
				$output = get_option( $transient_old );
			} else {
				$output = $content;
				// Save last new forever if a newer is not available..
				update_option( $transient_old, $output );
			}

			set_transient( $transient, $output, $expiration );
		}

		$output = preg_replace( '/[\n]/', '<br />', $output );

		if ( ! $echo ) {
			return $output;
		} else {
			echo $output;
		}
	}
}
