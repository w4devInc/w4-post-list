<?php
/**
 * Post taxtonomy query integration
 *
 * @class W4PL_Helper_Tax_Query
 * @package W4_Post_List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Meta query implementor class
 *
 * @class W4PL_Helper_Tax_Query
 */
class W4PL_Helper_Tax_Query {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'w4pl/list_edit_form_fields', array( $this, 'list_edit_form_fields' ), 10, 2 );
		add_filter( 'w4pl/pre_save_options', array( $this, 'pre_save_options' ) );
		add_filter( 'w4pl/pre_get_options', array( $this, 'pre_get_options' ) );
		add_filter( 'w4pl/parse_query_args', array( $this, 'parse_query_args' ), 20 );
	}

	/**
	 * Meta query control field on list editor
	 *
	 * @param  array $fields  List editor fields.
	 * @param  array $options List options.
	 * @return array          List editor fields.
	 */
	public function list_edit_form_fields( $fields, $options ) {
		$list_type = $options['list_type'];
		if ( ! in_array( $list_type, array( 'posts' ), true ) ) {
			return $fields;
		}

		$post_type  = $options['post_type'];
		$taxonomies = W4PL_Config::post_type_taxonomies_choices( $post_type );

		if ( empty( $taxonomies ) ) {
			return $fields;
		}

		$html = '<div id="w4pl_field_group_tax_query" class="w4pl_field_group">
			<div class="w4pl_group_title">' . __( 'Posts: Tax Query', 'w4-post-list' ) . '</div>
			<div class="w4pl_group_fields"><div class="w4pl_group_fields_wrap">';

		if ( ! empty( $options['tax_query']['relation'] ) ) {
			$tax_query_relation = $options['tax_query']['relation'];
		} else {
			$tax_query_relation = 'OR';
		}

		$html .= '<div class="wffw">';
		$html .= '<table id="w4pl_tax_query_table" class="widefat">
			<thead>
				<tr>
					<th id="w4pl_tax_query_taxonomy_cell_head">' . __( 'Taxonomy', 'w4-post-list' ) . '</th>
					<th id="w4pl_tax_query_operator_cell_head">' . __( 'Operator', 'w4-post-list' ) . '</th>
					<th id="w4pl_tax_query_field_cell_head">' . __( 'Field', 'w4-post-list' ) . '</th>
					<th id="w4pl_tax_query_terms_cell_head">' . __( 'Terms', 'w4-post-list' ) . '</th>
					<th id="w4pl_tax_query_action_cell_head">' . __( 'Action', 'w4-post-list' ) . '</th>
				</tr>
			</thead>
		<tbody>';

		if ( isset( $options['tax_query'] ) && ! empty( $options['tax_query'] ) ) {
			$index = 0;
			foreach ( $options['tax_query']['taxonomy'] as $i => $taxonomy ) {
				if ( isset( $options['tax_query']['field'][ $i ] ) ) {
					$field = $options['tax_query']['field'][ $i ];
				} else {
					$field = '';
				}

				if ( isset( $options['tax_query']['operator'][ $i ] ) ) {
					$operator = $options['tax_query']['operator'][ $i ];
				} else {
					$operator = '';
				}

				if ( isset( $options['tax_query']['terms'][ $i ] ) ) {
					$terms = $options['tax_query']['terms'][ $i ];
				} else {
					$terms = '';
				}

				if ( empty( $terms ) || empty( $operator ) ) {
					continue;
				}

				$html .= '
				<tr><td class="w4pl_tax_query_taxonomy_cell">
					' .
					w4pl_form_child_field_html(
						array(
							'id'          => 'w4pl_tax_query_taxonomy_' . $index,
							'name'        => 'w4pl[tax_query][taxonomy][' . $index . ']',
							'input_class' => 'w4pl_tax_query_taxonomy',
							'type'        => 'select',
							'option'      => W4PL_Config::post_type_taxonomies_choices( $post_type ),
							'value'       => $taxonomy,
						)
					)
					. '</td><td class="w4pl_tax_query_operator_cell">'
					. w4pl_form_child_field_html(
						array(
							'id'          => 'w4pl_tax_query_operator_' . $index,
							'name'        => 'w4pl[tax_query][operator][' . $index . ']',
							'input_class' => 'w4pl_tax_query_operator',
							'type'        => 'select',
							'option'      => W4PL_Config::tax_query_operator_options(),
							'value'       => $operator,
						)
					)
					. '</td><td class="w4pl_tax_query_field_cell">'
					. w4pl_form_child_field_html(
						array(
							'id'          => 'w4pl_tax_query_field_' . $index,
							'name'        => 'w4pl[tax_query][field][' . $index . ']',
							'input_class' => 'w4pl_tax_query_field',
							'type'        => 'select',
							'option'      => W4PL_Config::tax_query_field_options(),
							'value'       => $field,
						)
					)
					. '</td><td class="w4pl_tax_query_terms_cell terms" data-pos="' . $index . '">';

				if ( ! is_array( $terms ) ) {
					$terms = array( $terms );
				}

				$btn_class = '';
				if ( in_array( $operator, array( 'IN', 'NOT IN' ), true ) ) {
					$btn_class = 'csshide';
				}

				$cindex = 0;
				foreach ( $terms as $val ) {
					$html .= '
					<div class="item">
						<input type="text" value="' . esc_attr( $val ) . '" name="w4pl[tax_query][terms][' . $index . '][]" class="wff wffi_w4pl_tax_query_terms_' . $index . ' wfft_text ">
						<a class="w4pl_tax_query_value_add button" href="#">+</a>
						<a class="w4pl_tax_query_value_del button" href="#">-</a>
					</div>';
					++ $cindex;
				}
				$html .= '</td><td class="w4pl_tax_query_action_cell"><a class="w4pl_tax_query_remove_btn" href="#" class="button">' . __( 'Remove', 'w4-post-list' ) . '</a></td>
				</tr>';

				++ $index;
			}
		}
		$html .= '</tbody>
			</table>';

		$html .= '
		<div id="w4pl_tax_query_value_clone" style="display:none;">
			<div class="item">
				<input type="text" class="wff wfft_text wffi_w4pl_tax_query_value">
				<a class="w4pl_tax_query_value_add button" href="#">+</a>
				<a class="w4pl_tax_query_value_del button" href="#">-</a>
			</div>
		</div>';

		$html .= '
		<p style="text-align:right;"><a id="w4pl_tax_query_add_btn" href="#" class="button">' . __( '+ Add', 'w4-post-list' ) . '</a></p>
		<table id="w4pl_tax_query_clone" style="display:none;">
		<tr><td class="w4pl_tax_query_taxonomy_cell">'
					. w4pl_form_child_field_html(
						array(
							'name'        => 'w4pl[tax_query][taxonomy][]',
							'input_class' => 'w4pl_tax_query_taxonomy',
							'type'        => 'select',
							'option'      => W4PL_Config::post_type_taxonomies_choices( $post_type ),
						)
					)
					. '</td><td class="w4pl_tax_query_operator_cell">'
					. w4pl_form_child_field_html(
						array(
							'name'        => 'w4pl[tax_query][operator][]',
							'input_class' => 'w4pl_tax_query_operator',
							'type'        => 'select',
							'option'      => W4PL_Config::tax_query_operator_options(),
						)
					)
					. '</td><td class="w4pl_tax_query_field_cell">'
					. w4pl_form_child_field_html(
						array(
							'name'        => 'w4pl[tax_query][field][]',
							'input_class' => 'w4pl_tax_query_field',
							'type'        => 'select',
							'option'      => W4PL_Config::tax_query_field_options(),
						)
					)
			. '</td><td class="w4pl_tax_query_terms_cell terms">'
			. '<div class="item">
				<input type="text" class="wff wfft_text wffi_w4pl_tax_query_terms">
				<a class="w4pl_tax_query_value_add button" href="#">+</a>
				<a class="w4pl_tax_query_value_del button" href="#">-</a>
			</div>'
			. '</td><td class="w4pl_tax_query_action_cell terms"><a class="w4pl_tax_query_remove_btn" href="#" class="button">' . __( 'Remove', 'w4-post-list' ) . '</a></td>'
			. '
		</tr></table>';

		$html .= w4pl_form_field_html(
			array(
				'field_wrap' => false,
				'name'       => 'w4pl[tax_query][relation]',
				'label'      => __( 'Relation', 'w4-post-list' ),
				'type'       => 'radio',
				'option'     => array(
					'OR'  => 'OR',
					'AND' => 'AND',
				),
				'value'      => $tax_query_relation,
			)
		);

		$html .= '</div><!--.wffw-->';
		$html .= '</div><!--.w4pl_group_fields_wrap--></div><!--.w4pl_group_fields--></div><!--#w4pl_field_group_tax_query-->';

		$fields['tax_query'] = array(
			'position' => '110',
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
		if ( isset( $options['tax_query'] ) &&
			(
				( array_key_exists( 'terms', $options['tax_query'] ) && empty( $options['tax_query']['terms'] ) )
				|| ! array_key_exists( 'terms', $options['tax_query'] )
			)
		) {
			unset( $options['tax_query'] );
		}

		return $options;
	}

	/**
	 * Filter options
	 *
	 * @param  array $options List options.
	 */
	public function pre_get_options( $options ) {
		if ( ! isset( $options['tax_query'] ) ) {
			$options['tax_query'] = array();
		}
		return $options;
	}

	/**
	 * Filter options
	 *
	 * @param  object $list List options.
	 */
	public function parse_query_args( $list ) {
		if ( isset( $list->options['tax_query'] ) && isset( $list->options['tax_query']['taxonomy'] ) ) {
			$list->posts_args['tax_query'] = array();
			foreach ( $list->options['tax_query']['taxonomy'] as $index => $taxonomy ) {
				if ( isset( $list->options['tax_query']['field'][ $index ] ) ) {
					$field = $list->options['tax_query']['field'][ $index ];
				} else {
					$field = 'term_id';
				}

				if ( isset( $list->options['tax_query']['operator'][ $index ] ) ) {
					$operator = $list->options['tax_query']['operator'][ $index ];
				} else {
					$operator = '';
				}

				if ( isset( $list->options['tax_query']['terms'][ $index ] ) ) {
					$terms = $list->options['tax_query']['terms'][ $index ];
				} else {
					$terms = '';
				}

				if ( ! empty( $terms ) && ! empty( $operator ) ) {
					// tax query accept IN or NOT IN operator.
					if ( '=' === $operator ) {
						$operator = 'IN';
					} elseif ( '!=' === $operator ) {
						$operator = 'NOT IN';
					}

					if ( 'post_format' === $taxonomy ) {
						foreach ( $terms as $i => $term ) {
							$terms[ $i ] = 'post-format-' . str_replace( 'post-format-', '', $term );
						}
					}

					$list->posts_args['tax_query'][] = array(
						'taxonomy' => $taxonomy,
						'terms'    => $terms,
						'field'    => $field,
						'operator' => $operator,
					);
				}
			}

			if ( ! empty( $list->posts_args['tax_query'] ) ) {
				if ( isset( $list->options['tax_query']['relation'] ) ) {
					$list->posts_args['tax_query']['relation'] = $list->options['tax_query']['relation'];
				} else {
					$list->posts_args['tax_query']['relation'] = 'OR';
				}
			}
		}
	}
}
