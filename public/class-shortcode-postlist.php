<?php
/**
 * Post list shortcode handler class
 *
 * @class W4PL_Shortcode_List
 * @package W4 Post List
**/

defined( 'ABSPATH' ) || exit;

class W4PL_Shortcode_Postlist {

	function __construct( ) {
		add_shortcode( 'postlist', array( $this, 'shortcode' ), 6 );
		add_shortcode( 'w4pl-list', array( $this, 'shortcode' ), 6 );
	}

	/**
	 * Display List Using Shortcode
	 *
	 * @param array $attrs shortcode attributes
	 * @return mixed
	 */
	public function shortcode( $attrs ) {
		$options = $this->parse_shortcode_attrs( $attrs );
		if ( empty( $options ) ) {
			return '';
		}

		$options = apply_filters( 'w4pl/pre_get_options', $options );

		try {
			$list = W4PL_List_Factory::get_list( $options );
			return $list->get_html( );
		} catch( Exception $e ){
			// not showing error
			// return __( 'List not found' );
		}
	}

	/**
	 * Build list options from shortcode attributes
	 *
	 * @param array $attrs shortcode attributes
	 * @return array
	 */
	public function parse_shortcode_attrs( $attrs ) {
		if ( isset( $attrs['options'] ) ) {
			$options = maybe_unserialize ( base64_decode ( str_replace ( ' ', '', $attrs['options'] ) ) );
		} elseif ( isset( $attrs['id'] ) ) {
			$options = get_post_meta ( $attrs['id'], '_w4pl', true );
			$options['id'] = $attrs['id'];
		} elseif ( isset( $attrs['slug'] ) ) {
			global $wpdb;
			$post = $wpdb->get_row( $wpdb->prepare(
				"SELECT * FROM $wpdb->posts WHERE post_name = %s AND post_type = %s",
				$attrs['slug'], w4pl( )->plugin_slug( )
			 ) );
			if ( $post ) {
				$options = get_post_meta( $post->ID, '_w4pl', true );
				$options['id'] = $post->ID;
			}
		} elseif ( isset( $attrs['title'] ) ) {
			global $wpdb;
			$post = $wpdb->get_row( $wpdb->prepare(
				"SELECT * FROM $wpdb->posts WHERE post_title = %s AND post_type = %s",
				$attrs['title'], w4pl( )->plugin_slug( )
			 ) );
			if ( $post ) {
				$options = get_post_meta( $post->ID, '_w4pl', true );
				$options['id'] = $post->ID;
			}
		} else {
			$options = array( );

			if ( ! is_array( $attrs ) ) {
				$attrs = array( $attrs );
			}

			$list_id = array_shift( $attrs );
			$list_id = ( int ) $list_id;

			if ( $list_id && get_post( $list_id ) ) {
				$options = get_post_meta( $list_id, '_w4pl', true );
				$options['id'] = $list_id;
			}
		}

		return $options;
	}
}
