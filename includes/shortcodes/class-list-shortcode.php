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

		} catch (Exception $e) {

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
		if ( isset( $attrs['id'] ) && W4PL_Config::LIST_POST_TYPE === get_post_type( $attrs['id'] ) ) {
			$options       = get_post_meta( $attrs['id'], '_w4pl', true );
			$options['id'] = $attrs['id'];

		} elseif ( isset( $attrs['slug'] ) ) {
			$query = new WP_Query(
				array(
					'post_type'              => W4PL_Config::LIST_POST_TYPE,
					'name'                   => $attrs['slug'],
					'posts_per_page'         => 1,
					'no_found_rows'          => true,
					'ignore_sticky_posts'    => true,
					'update_post_term_cache' => false,
					'update_post_meta_cache' => false,
				)
			);

			if ( ! empty( $query->post ) ) {
				$options       = get_post_meta( $query->post->ID, '_w4pl', true );
				$options['id'] = $query->post->ID;
			}
		} elseif ( isset( $attrs['title'] ) ) {
			$query = new WP_Query(
				array(
					'post_type'              => W4PL_Config::LIST_POST_TYPE,
					'title'                  => $attrs['title'],
					'posts_per_page'         => 1,
					'no_found_rows'          => true,
					'ignore_sticky_posts'    => true,
					'update_post_term_cache' => false,
					'update_post_meta_cache' => false,
				)
			);

			if ( ! empty( $query->post ) ) {
				$options       = get_post_meta( $query->post->ID, '_w4pl', true );
				$options['id'] = $query->post->ID;
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