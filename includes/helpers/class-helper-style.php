<?php
/**
 * Integrate style & js for post list.
 *
 * @class W4PL_Helper_Style
 * @package W4_Post_List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Per-list CSS / JavaScript: editor fields, defaults and output.
 *
 * @class W4PL_Helper_Style
 */
class W4PL_Helper_Style {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'w4pl/list_edit_form_fields', array( $this, 'list_edit_form_fields' ), 10, 2 );
		add_filter( 'w4pl/pre_save_options', array( $this, 'pre_save_options' ) );
		add_filter( 'w4pl/pre_get_options', array( $this, 'pre_get_options' ) );
		add_filter( 'w4pl/parse_html', array( $this, 'parse_html' ), 60 );
	}

	/**
	 * Style control field on list editor
	 *
	 * @param  array $fields    List editor fields.
	 * @param  array $post_data List post data.
	 * @return array            List editor fields.
	 */
	public function list_edit_form_fields( $fields, $post_data ) {
		/* Style */
		$fields['before_field_group_style'] = array(
			'position' => '165',
			'html'     => '<div id="w4pl_field_group_style" class="w4pl_field_group">
				<div class="w4pl_group_title">' . __( 'Style', 'w4-post-list' ) . '</div>
				<div class="w4pl_group_fields"><div class="w4pl_group_fields_wrap">',
		);
		$fields['css']                      = array(
			'position'    => '170',
			'option_name' => 'css',
			'name'        => 'w4pl[css]',
			'id'          => 'w4pl_css',
			'label'       => __( 'CSS', 'w4-post-list' ),
			'type'        => 'textarea',
			'input_class' => 'widefat',
			'desc2'       => sprintf(
				/* translators: %s: list ID */
				__(
					'Printed once per page, right before the list (not in the HEAD tag), even when the list appears more than once.
								<br />To style just this list, use <code>#w4pl-list-%s</code> as the parent selector.
								<br />Alternatively, use <code>#w4pl-list-[listid]</code> and the ID is filled in for you.',
					'w4-post-list'
				),
				$post_data['id']
			),
		);
		$fields['js']                       = array(
			'position'    => '172',
			'option_name' => 'js',
			'name'        => 'w4pl[js]',
			'id'          => 'w4pl_js',
			'label'       => __( 'JavaScript', 'w4-post-list' ),
			'type'        => 'textarea',
			'input_class' => 'widefat',
			'desc2'       => __( 'Runs once per page, right after the list. Don\'t use a <code>&lt;script&gt;</code> tag.', 'w4-post-list' ),
			'after'       => '</div><!--.w4pl_group_fields--></div><!--#w4pl_field_group_style-->',
		);
		$fields['class']                    = array(
			'position'    => '174',
			'option_name' => 'class',
			'name'        => 'w4pl[class]',
			'label'       => __( 'HTML class name', 'w4-post-list' ),
			'type'        => 'text',
			'desc'        => __( 'applied on the wrapper html DIV tag', 'w4-post-list' ),
			'input_class' => 'widefat',
		);
		$fields['after_field_group_style']  = array(
			'position' => '175',
			'html'     => '</div><!--.w4pl_group_fields_wrap--></div><!--.w4pl_group_fields--></div><!--#w4pl_field_group_style-->',
		);

		return $fields;
	}

	/**
	 * Set default options
	 *
	 * @param  array $options List options.
	 */
	public function pre_save_options( $options ) {
		foreach ( array( 'class', 'js', 'css' ) as $k ) {
			if ( array_key_exists( $k, $options ) && empty( $options[ $k ] ) ) {
				unset( $options[ $k ] );
			}
		}

		return $options;
	}

	/**
	 * Filter options
	 *
	 * @param  array $options List options.
	 */
	public function pre_get_options( $options ) {
		$field_names = array(
			'class',
			'js',
			'css',
		);

		// Generate default values.
		foreach ( $field_names as $field_name ) {
			if ( ! isset( $options[ $field_name ] ) ) {
				$options[ $field_name ] = '';
			}
		}

		return $options;
	}

	/**
	 * Parse list render html to include css/js.
	 *
	 * The list's CSS and JavaScript go through the WordPress style and script
	 * API under a per-list handle (`w4pl-list-{id}`) and are printed in place,
	 * where the raw `<style>` / `<script>` tags used to be: the CSS right
	 * before the list, the JavaScript right after it. Going through the API
	 * means a list that appears twice on a page prints its CSS and JavaScript
	 * once, the inline script tag carries whatever `wp_inline_script_attributes`
	 * adds (CSP nonces, for instance), and site owners can read or replace the
	 * data through `wp_styles()` / `wp_scripts()`.
	 *
	 * Printing in place rather than from the footer keeps every consumer of
	 * the returned HTML working unchanged: the admin live preview and the
	 * block editor's server-side render have no `wp_footer`, and a list
	 * rendered after `wp_print_footer_scripts` would otherwise lose its CSS.
	 *
	 * @param  object $obj Instance of W4PL_List.
	 */
	public function parse_html( $obj ) {
		// Unique list class.
		$class     = trim( 'w4pl ' . $obj->options['class'] );
		$obj->html = str_replace( 'id="w4pl-list-' . $obj->id . '"', 'id="w4pl-list-' . $obj->id . '" class="' . $class . '"', $obj->html );

		if ( ! empty( $obj->options['css'] ) ) {
			$obj->css .= str_replace( '[listid]', $obj->id, $obj->options['css'] );
		}

		if ( ! empty( $obj->options['js'] ) ) {
			$obj->js .= str_replace( '[listid]', $obj->id, $obj->options['js'] );
		}

		// Css push.
		if ( ! empty( $obj->css ) ) {
			$obj->html = w4pl_get_list_inline_style( $obj->id, $obj->css ) . $obj->html;
		}

		// Js push.
		if ( ! empty( $obj->js ) ) {
			$obj->html .= "\n" . w4pl_get_list_inline_script( $obj->id, $obj->js );
		}
	}
}
