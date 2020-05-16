<?php
/**
 * Post list shortcode handler class
 *
 * @class W4PL_List_Shortcode
 * @package W4 Post List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * List shortcode
 */
class W4PL_List_Shortcode {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_shortcode( 'postlist', array( $this, 'shortcode' ), 6 );
		add_shortcode( 'w4pl-list', array( $this, 'shortcode' ), 6 );
	}

	/**
	 * Display List Using Shortcode
	 *
	 * @param  array $attrs Shortcode attributes.
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
			return $list->get_html();
		} catch ( Exception $e ) {
			// Not showing error.
			return $e->getMessage();
		}
	}

	/**
	 * Build list options from shortcode attributes
	 *
	 * @param array $attrs Shortcode attributes.
	 * @return array
	 */
	public function parse_shortcode_attrs( $attrs ) {
		if ( isset( $attrs['id'] ) ) {
			$options       = get_post_meta( $attrs['id'], '_w4pl', true );
			$options['id'] = $attrs['id'];
		} elseif ( isset( $attrs['slug'] ) ) {
			$post = get_page_by_path( $attrs['slug'], OBJECT, array( W4PL_Config::LIST_POST_TYPE ) );
			if ( $post ) {
				$options       = get_post_meta( $post->ID, '_w4pl', true );
				$options['id'] = $post->ID;
			}
		} elseif ( isset( $attrs['title'] ) ) {
			$post = get_page_by_title( $attrs['title'], OBJECT, W4PL_Config::LIST_POST_TYPE );
			if ( $post ) {
				$options       = get_post_meta( $post->ID, '_w4pl', true );
				$options['id'] = $post->ID;
			}
		} else {
			$options = array();

			if ( ! is_array( $attrs ) ) {
				$attrs = array( $attrs );
			}

			$list_id = array_shift( $attrs );
			$list_id = (int) $list_id;

			if ( $list_id && get_post( $list_id ) ) {
				$options       = get_post_meta( $list_id, '_w4pl', true );
				$options['id'] = $list_id;
			}
		}

		return $options;
	}
}
