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
			$display = self::kses_meta( (string) $value );
		}

		/**
		 * Filters a meta value as the meta template tags print it.
		 *
		 * @since 3.1.1
		 *
		 * @param string $display  Value after WordPress's post content markup rules,
		 *                         keeping iframes from known embed sources (3.1.2).
		 * @param mixed  $value    Value as stored.
		 * @param string $meta_key Meta key the value was read from.
		 * @param string $tag      Template tag printing it: post_meta, user_meta,
		 *                         post_author_meta or group_title.
		 */
		return apply_filters( 'w4pl/meta_display_value', $display, $value, $meta_key, $tag );
	}

	/**
	 * Post content markup rules, plus iframes whose src is a known embed URL
	 * (video, audio and map players).
	 *
	 * If any iframe in the value points somewhere else, every iframe is
	 * removed and the rest of the value is kept.
	 *
	 * @param  string $value Meta value.
	 * @return string
	 */
	private static function kses_meta( $value ) {
		if ( false === stripos( $value, '<iframe' ) ) {
			return wp_kses_post( $value );
		}

		$allowed           = wp_kses_allowed_html( 'post' );
		$allowed['iframe'] = array(
			'src'             => true,
			'width'           => true,
			'height'          => true,
			'title'           => true,
			'allow'           => true,
			'allowfullscreen' => true,
			'frameborder'     => true,
			'loading'         => true,
			'referrerpolicy'  => true,
			'class'           => true,
			'style'           => true,
		);

		$display = wp_kses( $value, $allowed );

		if ( preg_match_all( '/<iframe\b([^>]*)>/i', $display, $iframes ) ) {
			foreach ( $iframes[1] as $attributes ) {
				if ( ! self::is_embed_iframe( $attributes ) ) {
					return wp_kses_post( $value );
				}
			}
		}

		return preg_replace_callback( '/<iframe\b[^>]*>/i', array( __CLASS__, 'limit_iframe_allow' ), $display );
	}

	/**
	 * Keep only the permissions embed players use in an iframe's allow
	 * attribute (no camera, microphone, location or screen capture).
	 *
	 * @param  array $m preg_replace_callback() match: the iframe start tag.
	 * @return string
	 */
	private static function limit_iframe_allow( $m ) {
		return preg_replace_callback(
			'/\ballow=(["\'])(.*?)\1/i',
			function ( $a ) {
				$features = array( 'accelerometer', 'autoplay', 'clipboard-write', 'encrypted-media', 'fullscreen', 'gyroscope', 'picture-in-picture', 'web-share' );
				$keep     = array();
				foreach ( explode( ';', $a[2] ) as $directive ) {
					$directive = trim( $directive );
					$parts     = preg_split( '/\s+/', $directive );
					if ( '' !== $directive && in_array( strtolower( $parts[0] ), $features, true ) ) {
						$keep[] = strtolower( $parts[0] );
					}
				}
				return 'allow=' . $a[1] . implode( '; ', $keep ) . $a[1];
			},
			$m[0]
		);
	}

	/**
	 * Whether an iframe's src starts with one of the known embed URLs.
	 *
	 * @param  string $attributes The iframe's attribute string, as wp_kses() printed it.
	 * @return bool
	 */
	private static function is_embed_iframe( $attributes ) {
		$attributes = wp_kses_hair( rtrim( $attributes, " \t\n\r/" ), wp_allowed_protocols() );
		if ( empty( $attributes['src']['value'] ) ) {
			return false;
		}

		// Compare what the browser will load: entities decoded.
		$src = html_entity_decode( $attributes['src']['value'], ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		if ( 0 === strpos( $src, '//' ) ) {
			$src = 'https:' . $src;
		} elseif ( 0 === stripos( $src, 'http://' ) ) {
			$src = 'https://' . substr( $src, 7 );
		}

		// No dot segments (browsers resolve "%2e%2e" like ".."), no backslashes.
		if ( false !== strpos( $src, '..' ) || false !== stripos( $src, '%2e' ) || false !== strpos( $src, '\\' ) ) {
			return false;
		}

		/**
		 * Filters the URL prefixes an iframe src must start with for the meta
		 * template tags to keep it. Each prefix needs the scheme, the host and
		 * the slash after it, e.g. "https://player.example.com/embed/".
		 *
		 * @since 3.1.2
		 *
		 * @param string[] $prefixes Embed URL prefixes.
		 */
		$prefixes = apply_filters(
			'w4pl/meta_iframe_src_prefixes',
			array(
				'https://www.youtube.com/embed/',
				'https://youtube.com/embed/',
				'https://m.youtube.com/embed/',
				'https://www.youtube-nocookie.com/embed/',
				'https://player.vimeo.com/video/',
				'https://www.dailymotion.com/embed/',
				'https://geo.dailymotion.com/player.html',
				'https://geo.dailymotion.com/player/',
				'https://fast.wistia.net/embed/',
				'https://www.loom.com/embed/',
				'https://open.spotify.com/embed/',
				'https://open.spotify.com/embed-podcast/',
				'https://w.soundcloud.com/player/',
				'https://www.google.com/maps/embed?',
				'https://www.google.com/maps/embed/v1/',
				'https://www.google.com/maps/d/embed?',
				'https://www.google.com/maps/d/u/0/embed?',
			)
		);

		foreach ( (array) $prefixes as $prefix ) {
			// A prefix without the slash after the host would also match look-alike hosts.
			if ( ! is_string( $prefix ) || ! preg_match( '#^https://[^/\s]+/#i', $prefix ) ) {
				continue;
			}
			if ( 0 === stripos( $src, $prefix ) ) {
				return true;
			}
		}

		return false;
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
