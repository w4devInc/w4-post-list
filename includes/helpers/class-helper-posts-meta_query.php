<?php
/**
 * Post meta query integration
 *
 * @class W4PL_Helper_Meta_Query
 * @package W4_Post_List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Meta query implementor class
 *
 * @class W4PL_Helper_Meta_Query
 */
class W4PL_Helper_Meta_Query {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'w4pl/list_edit_form_fields', array( $this, 'list_edit_form_fields' ), 10, 2 );
		add_filter( 'w4pl/pre_save_options', array( $this, 'pre_save_options' ) );
		add_filter( 'w4pl/pre_get_options', array( $this, 'pre_get_options' ) );
		add_filter( 'w4pl/parse_query_args', array( $this, 'parse_query_args' ), 18 );
	}

	/**
	 * Meta query control field on list editor
	 *
	 * @param  array $fields  List editor fields.
	 * @param  array $options List options.
	 * @return array          List editor fields.
	 */
	public function list_edit_form_fields( $fields, $post_data ) {
		$list_type = $post_data['list_type'];
		if ( ! in_array( $list_type, array( 'posts', 'terms.posts' ) ) ) {
			return $fields;
		}

		/* Meta Query */
		$html = '<div id="w4pl_field_group_meta_query" class="w4pl_field_group">
			<div class="w4pl_group_title">' . __( 'Posts: Meta Query', 'w4-post-list' ) . '</div>
			<div class="w4pl_group_fields"><div class="w4pl_group_fields_wrap">';

		$meta_query_relation = isset( $post_data['meta_query']['relation'] ) && ! empty( $post_data['meta_query']['relation'] ) ? $post_data['meta_query']['relation'] : 'OR';

		$html .= '<div class="wffw">';
		$html .= '<table id="w4pl_meta_query_table" class="widefat">
			<thead>
				<tr>
					<th id="w4pl_meta_query_key_cell_head">' . __( 'Key', 'w4-post-list' ) . '</th>
					<th id="w4pl_meta_query_compare_cell_head">' . __( 'Compare', 'w4-post-list' ) . '</th>
					<th id="w4pl_meta_query_value_cell_head">' . __( 'Value', 'w4-post-list' ) . '</th>
					<th id="w4pl_meta_query_action_cell_head">' . __( 'Action', 'w4-post-list' ) . '</th>
				</tr>
			</thead>
			<tbody>';

		if ( isset( $post_data['meta_query'] )
			&& isset( $post_data['meta_query']['key'] )
			&& is_array( $post_data['meta_query']['key'] )
			&& ! empty( $post_data['meta_query']['key'] )
		) {
			$index = 0;
			foreach ( $post_data['meta_query']['key'] as $i => $key ) {
				$compare = isset( $post_data['meta_query']['compare'][ $i ] ) ? $post_data['meta_query']['compare'][ $i ] : '';
				$value   = isset( $post_data['meta_query']['value'][ $i ] ) ? $post_data['meta_query']['value'][ $i ] : '';

				if ( empty( $key ) || empty( $compare ) ) {
					continue;
				}

				$html .= '
				<tr><td class="w4pl_meta_query_key_cell">
					' .
					w4pl_form_child_field_html(
						array(
							'id'          => 'w4pl_meta_query_key_' . $index,
							'name'        => 'w4pl[meta_query][key][' . $index . ']',
							'input_class' => 'w4pl_meta_query_key',
							'type'        => 'text',
							'value'       => $key,
						)
					)
					. '</td><td class="w4pl_meta_query_compare_cell">'
					. w4pl_form_child_field_html(
						array(
							'id'          => 'w4pl_meta_query_compare_' . $index,
							'name'        => 'w4pl[meta_query][compare][' . $index . ']',
							'input_class' => 'w4pl_meta_query_compare',
							'type'        => 'select',
							'option'      => W4PL_Config::meta_query_compare_options(),
							'value'       => $compare,
						)
					)
					. '</td><td class="w4pl_meta_query_value_cell values" data-pos="' . $index . '">';

				if ( ! is_array( $value ) ) {
					$value = array( $value );
				}

				$cindex = 0;
				foreach ( $value as $val ) {
					$html .= '
					<div class="item">
						<input type="text" value="' . esc_attr( $val ) . '" name="w4pl[meta_query][value][' . $index . '][]" class="wff wffi_w4pl_meta_query_value_' . $index . ' wfft_text ">
						<a class="w4pl_meta_query_value_add button" href="#">+</a>
						<a class="w4pl_meta_query_value_del button" href="#">-</a>
					</div>';
					++ $cindex;
				}
				$html .= '</td><td class="w4pl_meta_query_action_cell"><a class="w4pl_meta_query_remove_btn" href="#" class="button">' . __( 'Remove', 'w4-post-list' ) . '</a></td>
				</tr>';

				++$index;
			}
		}
		$html .= '</tbody>
			</table>';

		$html .= '
		<div id="w4pl_meta_query_value_clone" style="display:none;">
			<div class="item">
				<input type="text" class="wff wfft_text wffi_w4pl_meta_query_value">
				<a class="w4pl_meta_query_value_add button" href="#">+</a>
				<a class="w4pl_meta_query_value_del button" href="#">-</a>
			</div>
		</div>';

		$html .= '
		<p style="text-align:right;"><a id="w4pl_meta_query_add_btn" href="#" class="button">' . __( '+ Add', 'w4-post-list' ) . '</a></p>
		<table id="w4pl_meta_query_clone" style="display:none;">
		<tr><td class="w4pl_meta_query_key_cell">
			<input type="text" class="wff wffi_w4pl_meta_query_key wfft_text">
			</td><td class="w4pl_meta_query_compare_cell">'
			. w4pl_form_child_field_html(
				array(
					'name'        => 'w4pl[meta_query][compare][]',
					'input_class' => 'w4pl_meta_query_compare',
					'type'        => 'select',
					'option'      => W4PL_Config::meta_query_compare_options(),
				)
			)
			. '</td><td class="w4pl_meta_query_value_cell values">'
			. '<div class="item">
				<input type="text" class="wff wfft_text wffi_w4pl_meta_query_value">
				<a class="w4pl_meta_query_value_add button" href="#">+</a>
				<a class="w4pl_meta_query_value_del button" href="#">-</a>
			</div>'
			. '</td><td class="w4pl_meta_query_action_cell"><a class="w4pl_meta_query_remove_btn" href="#" class="button">' . __( 'Remove', 'w4-post-list' ) . '</a></td>'
			. '
		</tr></table>';

		$html .= w4pl_form_field_html(
			array(
				'field_wrap' => false,
				'name'       => 'w4pl[meta_query][relation]',
				'label'      => __( 'Relation', 'w4-post-list' ),
				'type'       => 'radio',
				'option'     => array(
					'OR'  => __( 'OR', 'w4-post-list' ),
					'AND' => __( 'AND', 'w4-post-list' ),
				),
				'value'      => $meta_query_relation,
			)
		);

		$html .= '<p class="wffdw2">';
		$html .= '<br />For the value field, you can also use following shortcodes to apply dynamic value:';
		$html .= '<br /><code>[w4pl_date day=+6 hour=-1 format="Y-m-d H:i:s"]</code> - for displaying datetime based on current time';
		$html .= '<br /><code>[w4pl_time day=+6 hour=-1]</code> - for displaying timestamp based on current time';
		$html .= '<br /><br />Note: Above Shortcodes generates times in GMT timezone. To compare time saved in another timezone, use hour attribute. for example: [w4pl_date hour=+6] will generate the time what is identical to GMT+6 timestamp.';
		$html .= '</p>';

		$html .= '</div><!--.wffw-->';
		$html .= '</div><!--.w4pl_group_fields_wrap--></div><!--.w4pl_group_fields--></div><!--#w4pl_field_group_meta_query-->';

		$fields['meta_query'] = array(
			'position' => '120',
			'type'     => 'html',
			'html'     => $html,
		);

		return $fields;
	}

	/**
	 * Set default options
	 *
	 * @param  array $options List options.
	 */
	public function pre_save_options( $options ) {
		if ( isset( $options['meta_query'] ) &&
			(
				( array_key_exists( 'value', $options['meta_query'] ) && empty( $options['meta_query']['value'] ) )
				|| ! array_key_exists( 'value', $options['meta_query'] )
			)
		) {
			unset( $options['meta_query'] );
		}

		return $options;
	}

	/**
	 * Filter options
	 *
	 * @param  array $options List options.
	 */
	public function pre_get_options( $options ) {
		if ( ! isset( $options['meta_query'] ) ) {
			$options['meta_query'] = array();
		}
		return $options;
	}

	/**
	 * Parse query arguments
	 *
	 * @param  object $list List options.
	 */
	public function parse_query_args( $list ) {
		// meta query
		if ( isset( $list->options['meta_query'] ) && isset( $list->options['meta_query']['key'] ) ) {
			$list->posts_args['meta_query'] = array();
			foreach ( $list->options['meta_query']['key'] as $index => $key ) {
				$value   = isset( $list->options['meta_query']['value'][ $index ] ) ? $list->options['meta_query']['value'][ $index ] : '';
				$compare = isset( $list->options['meta_query']['compare'][ $index ] ) ? $list->options['meta_query']['compare'][ $index ] : '';

				// parse shortcode from meta values, this allows to use dynamic values.
				if ( is_array( $value ) ) {
					$value = array_map( 'do_shortcode', $value );
				} elseif ( ! empty( $value ) ) {
					$value = do_shortcode( $value );
				}

				if ( ! empty( $key ) && ! empty( $compare ) ) {
					// we store meta values data as array. if compare string isn't array, shift the first value.
					if ( ! in_array( $compare, array( 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN' ), true ) ) {
						$value = array_shift( $value );
					}

					$meta_qyery = array(
						'key'     => $key,
						'compare' => $compare,
					);

					if ( '' !== $value ) {
						$meta_qyery['value'] = $value;
					}

					$list->posts_args['meta_query'][] = $meta_qyery;
				}
			}

			if ( ! empty( $list->posts_args['meta_query'] ) ) {
				$list->posts_args['meta_query']['relation'] = isset( $list->options['meta_query']['relation'] ) ? $list->options['meta_query']['relation'] : 'OR';
			}
		}
	}
}
