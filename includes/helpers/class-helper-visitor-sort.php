<?php
/**
 * Visitor sorting: a dropdown that lets visitors re-order a posts list.
 *
 * @class W4PL_Helper_Visitor_Sort
 * @package W4_Post_List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Visitor sort implementor class
 *
 * Opt-in per list: the list owner ticks the orders to offer ("visitor_sort"
 * option). A list with none ticked ignores the URL parameter and renders
 * exactly as before.
 *
 * The chosen order travels in w4pl_sort_{list id}, next to the existing
 * page{list id} pagination parameter. Its value is a token from
 * W4PL_Config::visitor_sort_options() that the list offers, or it is ignored;
 * the request value itself never reaches WP_Query.
 *
 * @since 3.1.0
 */
class W4PL_Helper_Visitor_Sort {

	/**
	 * Query parameter prefix; the list id is appended.
	 */
	const QUERY_VAR_PREFIX = 'w4pl_sort_';

	/**
	 * Stands in for the [sort] tag until the list html is assembled.
	 */
	const PLACEHOLDER = '<!--w4pl-sort-control-->';

	/**
	 * Sort state of the lists being rendered, keyed by spl_object_id() of the
	 * list object, so a list nested inside another render of the same list id
	 * cannot take the outer one's state.
	 *
	 * @var array
	 */
	private $pending = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'w4pl/list_edit_form_fields', array( $this, 'list_edit_form_fields' ), 10, 2 );
		add_filter( 'w4pl/get_shortcodes', array( $this, 'get_shortcodes' ) );
		add_action( 'w4pl/parse_query_args', array( $this, 'parse_query_args' ), 20 );
		add_action( 'w4pl/parse_html', array( $this, 'parse_html' ), 10 );
	}

	/**
	 * Query parameter carrying a list's sort.
	 *
	 * @param  int|string $list_id List id.
	 * @return string
	 */
	public static function query_var( $list_id ) {
		return self::QUERY_VAR_PREFIX . $list_id;
	}

	/**
	 * Sort choices, after the w4pl/visitor_sort_options filter.
	 *
	 * Entries a filter adds are checked here rather than trusted: the token
	 * must be a plain slug, the orderby one the list editor offers and the
	 * order ASC or DESC. Anything else is dropped.
	 *
	 * @return array Token => array( orderby, order, label ).
	 */
	public static function registry() {
		$choices = apply_filters( 'w4pl/visitor_sort_options', W4PL_Config::visitor_sort_options() );
		$orderby = array_keys( W4PL_Config::post_orderby_options() );
		$valid   = array();

		if ( ! is_array( $choices ) ) {
			return $valid;
		}

		foreach ( $choices as $token => $choice ) {
			if (
				! is_string( $token )
				|| ! preg_match( '/^[a-z0-9_-]+$/', $token )
				|| ! is_array( $choice )
				|| ! isset( $choice['orderby'], $choice['order'], $choice['label'] )
				|| ! in_array( $choice['orderby'], $orderby, true )
				|| ! in_array( $choice['order'], array( 'ASC', 'DESC' ), true )
				|| ! is_string( $choice['label'] )
				|| '' === $choice['label']
			) {
				continue;
			}

			$valid[ $token ] = array(
				'orderby' => $choice['orderby'],
				'order'   => $choice['order'],
				'label'   => $choice['label'],
			);
		}

		return $valid;
	}

	/**
	 * Keep only known tokens, in registry order. Used on save and on render.
	 *
	 * @param  mixed $tokens Stored or posted option value.
	 * @return array
	 */
	public static function filter_tokens( $tokens ) {
		if ( ! is_array( $tokens ) ) {
			return array();
		}

		$tokens = array_filter( $tokens, 'is_string' );

		return array_values( array_intersect( array_keys( self::registry() ), $tokens ) );
	}

	/**
	 * Choices this list offers. Empty when visitor sorting is off.
	 *
	 * @param  array $options List options.
	 * @return array Token => choice.
	 */
	public static function offered( $options ) {
		if ( ! isset( $options['list_type'], $options['visitor_sort'] ) || 'posts' !== $options['list_type'] ) {
			return array();
		}

		$tokens = self::filter_tokens( $options['visitor_sort'] );

		return array_intersect_key( self::registry(), array_flip( $tokens ) );
	}

	/**
	 * The offered token the current request asks for, or ''.
	 *
	 * Compared byte-for-byte against the offered tokens; the request value is
	 * never sanitized into something else.
	 *
	 * @param  int|string $list_id List id.
	 * @param  array      $offered Offered choices.
	 * @return string
	 */
	public static function requested( $list_id, $offered ) {
		$var = self::query_var( $list_id );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display preference, checked against a whitelist.
		if ( ! isset( $_GET[ $var ] ) || ! is_string( $_GET[ $var ] ) ) {
			return '';
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- validated by the whitelist below.
		$token = wp_unslash( $_GET[ $var ] );

		return isset( $offered[ $token ] ) ? $token : '';
	}

	/**
	 * Sort control field on list editor
	 *
	 * @param  array $fields  List editor fields.
	 * @param  array $options List options.
	 * @return array          List editor fields.
	 */
	public function list_edit_form_fields( $fields, $options ) {
		if ( ! isset( $options['list_type'] ) || 'posts' !== $options['list_type'] ) {
			return $fields;
		}

		$choices = array();
		foreach ( self::registry() as $token => $choice ) {
			$choices[ $token ] = esc_html( $choice['label'] );
		}

		$fields['visitor_sort'] = array(
			'position'    => '71.5',
			'option_name' => 'visitor_sort',
			'name'        => 'w4pl[visitor_sort]',
			'label'       => __( 'Visitor sorting', 'w4-post-list' ),
			'type'        => 'checkbox',
			'option'      => $choices,
			'desc2'       => esc_html__( 'Tick the orders visitors may choose from a "Sort by" dropdown. Leave all unticked to keep the order above fixed. The dropdown appears above the list, or wherever the template has the [sort] tag.', 'w4-post-list' ),
		);

		return $fields;
	}

	/**
	 * Register the [sort] template tag.
	 *
	 * @param  array $shortcodes All template tags.
	 * @return array
	 */
	public function get_shortcodes( $shortcodes ) {
		$shortcodes['sort'] = array(
			'group'      => 'Main',
			'code'       => '[sort label=""]',
			// Only reached in a list with visitor sorting off: an enabled list
			// swaps the tag out before the template is parsed.
			'callback'   => '__return_empty_string',
			'parameters' => array(
				'label' => array(
					'desc' => __( 'Text before the dropdown. Default: "Sort by".', 'w4-post-list' ),
				),
				'ajax'  => array(
					'choices' => array(
						'0',
						'1',
					),
					'desc'    => __( 'Re-sort without reloading the page. Default: same as [nav ajax].', 'w4-post-list' ),
				),
			),
			'output'     => __( 'Sort dropdown, for lists with visitor sorting enabled', 'w4-post-list' ),
		);

		return $shortcodes;
	}

	/**
	 * Apply the visitor's order and set aside the [sort] tag.
	 *
	 * Runs after W4PL_Helper_Posts has copied the list's own orderby/order
	 * into the query. Only orderby and order change; every filter stays,
	 * including a meta_key the list's own ordering added. The visitor's order
	 * applies to everything the list matches, and "Maximum items" and
	 * "Offset" then count in that order (the editor warns about this).
	 *
	 * @param object $list W4PL_List instance.
	 */
	public function parse_query_args( $list ) {
		$key = spl_object_id( $list );
		unset( $this->pending[ $key ] );

		$offered = self::offered( $list->options );
		if ( empty( $offered ) ) {
			return;
		}

		$token = self::requested( $list->id, $offered );
		if ( '' !== $token ) {
			$list->posts_args['orderby'] = $offered[ $token ]['orderby'];
			$list->posts_args['order']   = $offered[ $token ]['order'];
		}

		$template = isset( $list->options['template'] ) && is_string( $list->options['template'] ) ? $list->options['template'] : '';
		$attr     = array();

		if ( preg_match( '/\[sort(?![\w-])([^\]]*)\]/', $template, $match ) ) {
			$attr = shortcode_parse_atts( $match[1] );
			$attr = is_array( $attr ) ? $attr : array();

			$list->options['template'] = preg_replace( '/\[sort(?![\w-])[^\]]*\]/', self::PLACEHOLDER, $template );
		}

		// No ajax attribute: follow the list's pagination.
		if ( ! isset( $attr['ajax'] ) ) {
			$attr['ajax'] = '0';
			if ( preg_match( '/\[nav(.*?)\]/', $template, $nav_match ) ) {
				$nav_attr = shortcode_parse_atts( $nav_match[1] );
				if ( is_array( $nav_attr ) && ! empty( $nav_attr['ajax'] ) ) {
					$attr['ajax'] = '1';
				}
			}
		}

		$this->pending[ $key ] = array(
			'attr'    => $attr,
			'offered' => $offered,
			'current' => $token,
		);
	}

	/**
	 * Put the sort control in place of the [sort] tag, or at the top of the
	 * list when the template has none.
	 *
	 * @param object $list W4PL_List instance.
	 */
	public function parse_html( $list ) {
		$key = spl_object_id( $list );
		if ( ! isset( $this->pending[ $key ] ) ) {
			return;
		}

		$pending = $this->pending[ $key ];
		unset( $this->pending[ $key ] );

		// Nothing to sort: the template renders empty and so does the tag.
		$has_items = $list->posts_query instanceof WP_Query && $list->posts_query->post_count > 0;
		$control   = $has_items ? $this->control_html( $list, $pending ) : '';

		$at = strpos( $list->html, self::PLACEHOLDER );
		if ( false !== $at ) {
			// One control per list, even if the tag was used twice or inside a loop.
			$list->html = substr_replace( $list->html, $control, $at, strlen( self::PLACEHOLDER ) );
			$list->html = str_replace( self::PLACEHOLDER, '', $list->html );
		} elseif ( '' !== $control ) {
			$inner      = '<div id="w4pl-inner-' . $list->id . '" class="w4pl-inner">';
			$list->html = str_replace( $inner, $inner . "\n\t\t" . $control, $list->html );
		}
	}

	/**
	 * The sort form. A plain GET form, so it works without JavaScript; the
	 * front-end script also applies it when a pointer picks an option, over
	 * AJAX when ajax="1". The button stays visible for keyboard users, for
	 * whom a select fires "change" on every arrow key.
	 *
	 * @param  object $list    W4PL_List instance.
	 * @param  array  $pending Offered choices, current token, tag attributes.
	 * @return string
	 */
	private function control_html( $list, $pending ) {
		$var     = self::query_var( $list->id );
		$paged   = 'page' . $list->id;
		$attr    = $pending['attr'];
		$offered = $pending['offered'];
		$label   = isset( $attr['label'] ) && '' !== $attr['label'] ? $attr['label'] : __( 'Sort by', 'w4-post-list' );
		$ajax    = ! empty( $attr['ajax'] ) && '0' !== $attr['ajax'];

		// The list's own order, when it is one of the offered choices, stands
		// in for "Default"; otherwise "Default order" gets its own entry.
		$own_order = isset( $list->options['order'] ) && is_string( $list->options['order'] ) ? strtoupper( $list->options['order'] ) : '';
		$own_token = '';
		foreach ( $offered as $token => $choice ) {
			if ( isset( $list->options['orderby'] ) && $choice['orderby'] === $list->options['orderby'] && $choice['order'] === $own_order ) {
				$own_token = $token;
				break;
			}
		}

		$current = '' !== $pending['current'] ? $pending['current'] : $own_token;

		// Submit to the current URL, as it was requested: get_pagenum_link()
		// would drop the page's own /page/N/, and add_query_arg() and friends
		// run the query through parse_str(), which rewrites "." and " " in
		// keys and would change parameters that belong to someone else.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- split below, every piece escaped on output.
		$uri   = isset( $_SERVER['REQUEST_URI'] ) && is_string( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
		$uri   = current( explode( '#', $uri, 2 ) );
		$parts = explode( '?', $uri, 2 );
		$query = isset( $parts[1] ) ? $parts[1] : '';

		// A path of "//host/..." would make the action point at another site.
		$path = '/' . ltrim( $parts[0], '/' );

		// Previews (list editor, block editor) render through admin-ajax.php
		// or the REST API, where submitting would leave the editor.
		$preview  = is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST );
		$disabled = $preview ? ' disabled="disabled"' : '';

		$id   = 'w4pl-sort-' . $list->id;
		$html = '<form class="w4pl-sort" method="get" action="' . esc_url( $path ) . '"' . ( $ajax ? ' data-ajax="1"' : '' ) . '>';

		$html .= '<label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label> ';
		$html .= '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $var ) . '"' . $disabled . '>';

		if ( '' === $own_token ) {
			$html .= '<option value=""' . selected( $current, '', false ) . '>' . esc_html__( 'Default order', 'w4-post-list' ) . '</option>';
		}

		foreach ( $offered as $token => $choice ) {
			$html .= '<option value="' . esc_attr( $token ) . '"' . selected( $current, $token, false ) . '>' . esc_html( $choice['label'] ) . '</option>';
		}

		$html .= '</select>';

		// A GET form drops the action's query string, so carry it as fields.
		// A new order starts from page one; other lists' parameters are kept.
		foreach ( explode( '&', $query ) as $pair ) {
			if ( '' === $pair ) {
				continue;
			}

			$parts = explode( '=', $pair, 2 );
			$key   = urldecode( $parts[0] );
			$value = isset( $parts[1] ) ? urldecode( $parts[1] ) : '';

			if ( '' === $key || $key === $var || $key === $paged ) {
				continue;
			}

			$html .= '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
		}

		$html .= ' <button type="submit" class="w4pl-sort-submit"' . $disabled . '>' . esc_html__( 'Sort', 'w4-post-list' ) . '</button>';
		$html .= '</form>';

		w4pl_enqueue_ajax_nav_script();

		return $html;
	}
}
