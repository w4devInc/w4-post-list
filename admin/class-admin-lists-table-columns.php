<?php
/**
 * Admin list table custom columns
 *
 * @class W4PL_Admin_Lists_Table
 * @package W4 Post List
 */

defined( 'ABSPATH' ) || exit;

class W4PL_Admin_Lists_Table_Columns {

	public function __construct() {
		// additional column
		add_action( 'load-edit.php', array( $this, 'load_lists_page' ) );
		add_filter( 'manage_'. w4pl()->list_post_type() .'_posts_columns', array( $this, 'manage_posts_columns' ) );
		add_action( 'manage_'. w4pl()->list_post_type() .'_posts_custom_column', array( $this, 'manage_posts_custom_column' ), 10, 2 );
	}


	public function load_lists_page() {
		global $typenow;

		if ( $typenow === w4pl()->list_post_type() ) {
			add_filter( 'months_dropdown_results', '__return_empty_array' );
		}
	}

	public function manage_posts_columns( $columns ) {
		$date = false;
		if ( isset( $columns['date'] ) ) {
			$date = $columns['date'];
			unset( $columns['date'] );
		}

		$columns['list_type'] = __( 'List Type', 'w4pl' );
		$columns['shortcode'] = __( 'Shortcode', 'w4pl' );

		if ( $date ) {
			$columns['date'] = $date;
		}

		return $columns;
	}

	public function manage_posts_custom_column( $column_name, $post_ID ) {
		if ( 'list_type' == $column_name ) {
			echo $this->list_type_label( $post_ID );
		} elseif ( 'shortcode' == $column_name ) {
			printf(
				'<input value="[postlist id=%d]" type="text" size="20" onfocus="this.select();" onclick="this.select();" readonly="readonly" />',
				$post_ID
			);
		}
	}


	/**
	 * Prints a friendly list type of a given post list
	 * used on admin lists table
	 */

	public function list_type_label( $post_ID )	{
		// this is really odd to get information like this
		$options = get_post_meta( $post_ID, '_w4pl', true );
		$options['id'] = $post_ID;
		$options = apply_filters( 'w4pl/pre_get_options', $options );

		$lt = $options['list_type'];

		global $wp_post_types;

		$return = '';

		if ( 'terms.posts' == $lt ) {
			$tax_obj = get_taxonomy($options['terms_taxonomy']);
			$return = $tax_obj->label . ' & ' . $this->post_types_label( $options['post_type'] );
		} elseif ( 'users.posts' == $lt ) {
			$return = 'Users' . ' & ' . $this->post_types_label( $options['post_type'] );
		} elseif ( 'posts' == $lt ) {
			$return = $this->post_types_label( $options['post_type'] );
		} elseif ( 'terms' == $lt ) {
			$tax_obj = get_taxonomy( $options['terms_taxonomy'] );
			$return = $tax_obj->label;
		} elseif ( 'users' == $lt ) {
			$return = 'Users';
		}

		if ( empty( $return ) ) {
			$lt_options = W4PL_Utils::list_type_options();
			if ( ! empty( $lt ) && isset( $lt_options[ $lt ] ) ) {
				$return = $lt_options[ $lt ];
			} else {
				$return = '-';
			}
		}

		return $return;
	}


	public function post_types_label( $post_types ) {
		global $wp_post_types;
		$post_labels = array();
		foreach ($post_types as $post_type) {
			if (isset ($wp_post_types[$post_type])) {
				$post_labels[] = $wp_post_types[$post_type]->label;
			} else {
				$post_labels[] = __('Unregistered');
			}
		}
		return implode(', ', $post_labels);
	}

}
