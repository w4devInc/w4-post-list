<?php
/**
 * Helps displaying no items
 *
 * @class W4PL_Helper_No_Items
 * @package W4_Post_List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * No items class
 *
 * @class W4PL_Helper_No_Items
 */
class W4PL_Helper_No_Items {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'w4pl/list_edit_form_fields', array( $this, 'list_edit_form_fields' ), 10, 2 );
		add_filter( 'w4pl/pre_save_options', array( $this, 'pre_save_options' ) );
		add_filter( 'w4pl/pre_get_options', array( $this, 'pre_get_options' ) );
		add_action( 'w4pl/parse_html', array( $this, 'parse_html' ), 60 );
	}

	/**
	 * No items text control field on list editor
	 *
	 * @param  array $fields  List editor fields.
	 * @param  array $options List options.
	 * @return array          List editor fields.
	 */
	public function list_edit_form_fields( Array $fields, Array $options ) {
		if ( 'posts' == $options['list_type'] ) {
			$pos = 99.2;
		} else {
			// Except posts, all other list type is bound within 5-50.
			$pos = 49.2;
		}

		$fields['no_items_text'] = array(
			'position'    => $pos,
			'option_name' => 'no_items_text',
			'name'        => 'w4pl[no_items_text]',
			'label'       => __( 'No items text', 'w4-post-list' ),
			'type'        => 'textarea',
			'input_class' => 'widefat',
			'desc'        => __( 'Displayed when no items found by this filter or pagination', 'w4-post-list' ),
		);

		return $fields;
	}

	/**
	 * If empty, unset no items text before saving list options
	 *
	 * @param  array $options List options.
	 */
	public function pre_save_options( Array $options ) {
		foreach ( array(
			'no_items_text' => '',
		) as $k => $v ) {
			if ( array_key_exists( $k, $options ) && empty( $options[ $k ] ) ) {
				unset( $options[ $k ] );
			}
		}
		return $options;
	}

	/**
	 * Set default not items text to empty when getting list options
	 *
	 * @param  array $options List options.
	 */
	public function pre_get_options( Array $options ) {
		foreach ( array(
			'no_items_text' => '',
		) as $k => $v ) {
			if ( ! isset( $options[ $k ] ) ) {
				  $options[ $k ] = '';
			}
		}
		return $options;
	}

	/**
	 * Parse list render html to include no items text.
	 *
	 * @param  object $obj Instance of W4PL_List
	 */
	public function parse_html( $obj ) {
		if ( empty( $obj->template ) && ! empty( $obj->options['no_items_text'] ) ) {
			$obj->html = str_replace( 'class="w4pl-inner">', 'class="w4pl-inner">' . $obj->options['no_items_text'], $obj->html );
		}
	}
}
