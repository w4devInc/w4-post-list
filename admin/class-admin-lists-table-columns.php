<?php
/**
 * Admin list table custom columns
 *
 * @class W4PL_Admin_Lists_Table
 * @package W4_Post_List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post list admin table columns
 */
class W4PL_Admin_Lists_Table_Columns {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'load-edit.php', array( $this, 'load_lists_page' ) );
		add_filter( 'manage_' . W4PL_Config::LIST_POST_TYPE . '_posts_columns', array( $this, 'manage_posts_columns' ) );
		add_action( 'manage_' . W4PL_Config::LIST_POST_TYPE . '_posts_custom_column', array( $this, 'manage_posts_custom_column' ), 10, 2 );
	}

	/**
	 * Apply filter on post list page to hide months dropdown
	 *
	 * @return void
	 */
	public function load_lists_page() {
		global $typenow;

		if ( W4PL_Config::LIST_POST_TYPE === $typenow ) {
			add_filter( 'months_dropdown_results', '__return_empty_array' );
		}
	}

	/**
	 * Add additional columns
	 *
	 * @param  array $columns Columns array.
	 * @return array
	 */
	public function manage_posts_columns( $columns ) {
		$date = false;
		if ( isset( $columns['date'] ) ) {
			$date = $columns['date'];
			unset( $columns['date'] );
		}

		$columns['list_type'] = __( 'List Type', 'w4-post-list' );
		$columns['shortcode'] = __( 'Shortcode', 'w4-post-list' );

		if ( $date ) {
			$columns['date'] = $date;
		}

		return $columns;
	}

	/**
	 * Display values for custom columns
	 *
	 * @param  string   $column_name Column name.
	 * @param  interger $post_ID     current post id.
	 * @return void
	 */
	public function manage_posts_custom_column( $column_name, $post_ID ) {
		if ( 'list_type' === $column_name ) {
			echo esc_html( $this->list_type_label( $post_ID ) );
		} elseif ( 'shortcode' === $column_name ) {
			printf(
				'<input value="[postlist id=%d]" type="text" size="20" onfocus="this.select();" onclick="this.select();" readonly="readonly" />',
				(int) $post_ID
			);
		}
	}

	/**
	 * Prints a friendly list type of a given post list
	 * used on admin lists table
	 *
	 * @param interger $post_ID Id of the post.
	 */
	public function list_type_label( $post_ID ) {
		// this is really odd to get information like this.
		$options = get_post_meta( $post_ID, '_w4pl', true );
		if ( empty( $options ) ) {
			$options = array();
		}

		$options['id'] = $post_ID;
		$options       = apply_filters( 'w4pl/pre_get_options', $options );

		$lt = $options['list_type'];

		$return = '';

		if ( 'terms.posts' === $lt ) {
			$tax_obj = get_taxonomy( $options['terms_taxonomy'] );
			$return  = $tax_obj->label . ' & ' . $this->post_types_label( $options['post_type'] );
		} elseif ( 'users.posts' === $lt ) {
			$return = 'Users & ' . $this->post_types_label( $options['post_type'] );
		} elseif ( 'posts' === $lt ) {
			$return = $this->post_types_label( $options['post_type'] );
		} elseif ( 'terms' === $lt ) {
			$tax_obj = get_taxonomy( $options['terms_taxonomy'] );
			$return  = $tax_obj->label;
		} elseif ( 'users' === $lt ) {
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

	/**
	 * Creates an array of post type with human readable labels
	 *
	 * @param  array $post_types Array of post type names.
	 * @return array
	 */
	public function post_types_label( $post_types ) {
		global $wp_post_types;
		$post_labels = array();
		foreach ( $post_types as $post_type ) {
			if ( isset( $wp_post_types[ $post_type ] ) ) {
				$post_labels[] = $wp_post_types[ $post_type ]->label;
			} else {
				$post_labels[] = __( 'Unregistered', 'w4-post-list' );
			}
		}
		return implode( ', ', $post_labels );
	}
}
