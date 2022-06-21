<?php
/**
 * Post date query integration
 *
 * @class W4PL_Helper_Date_Query
 * @package W4_Post_List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Date query implementor class
 *
 * @class W4PL_Helper_Date_Query
 */
class W4PL_Helper_Date_Query {

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
	 * Date query control field on list editor
	 *
	 * @param  array $fields  List editor fields.
	 * @param  array $options List options.
	 * @return array          List editor fields.
	 */
	public function list_edit_form_fields( $fields, $options ) {
		$list_type = $options['list_type'];
		if ( ! in_array( $list_type, array( 'posts', 'terms.posts', 'users.posts' ) ) ) {
			return $fields;
		}

		$html = '<div id="w4pl_field_group_date_query" class="w4pl_field_group">
			<div class="w4pl_group_title">' . __( 'Posts: Date Query', 'w4-post-list' ). '</div>
			<div class="w4pl_group_fields"><div class="w4pl_group_fields_wrap">';

		$html .= '<div class="wffw wff_clone_wrap">';
		$html .= '<table id="w4pl_date_query_table" class="widefat wff_clone_table">
			<thead>
				<tr>
					<th class="column">' . __( 'Column', 'w4-post-list' ) . '</th>
					<th class="key">' . __( 'Key', 'w4-post-list' ) . '</th>
					<th class="compare">' . __( 'Compare', 'w4-post-list' ) . '</th>
					<th class="value">' . __( 'Value', 'w4-post-list' ) . '</th>
					<th class="action">' . __( 'Action', 'w4-post-list' ) . '</th>
				</tr>
			</thead>
			<tbody class="wff_clone_to">';

		if ( ! empty( $options['date_query'] ) ) {
			foreach ( $options['date_query'] as $date_query ) {
				$html .= $this->get_date_query_form_row( $date_query );
			}
		}
		$html .= '</tbody>
			</table>';

		$html .= '
		<div id="w4pl_date_query_value_clone" style="display:none;">
			<div class="item">
				<input type="text" class="wff wfft_text wffi_w4pl_date_query_value">
				<a class="w4pl_date_query_value_add button" href="#">+</a>
				<a class="w4pl_date_query_value_del button" href="#">-</a>
			</div>
		</div>';

		$html .= '
		<p style="text-align:right;"><a href="#" class="button wff_clone_btn">' . __( '+ Add', 'w4-post-list' ) . '</a></p>

		<table class="csshide"><tbody class="wff_clone_from">' . $this->get_date_query_form_row() . '</table>';

		$html .= w4pl_form_field_html(
			array(
				'field_wrap' => false,
				'name'       => 'w4pl[date_query_relation]',
				'label'      => __( 'Relation', 'w4-post-list' ),
				'type'       => 'radio',
				'option'     => array(
					'OR'  => __( 'OR', 'w4-post-list' ),
					'AND' => __( 'AND', 'w4-post-list' ),
				),
				'value'      => $options['date_query_relation'],
			)
		);

		$html .= '<p class="wffdw2">';
		$html .= '<br />All dates should be used in Y-m-d format. ie: 2020-12-31';
		$html .= '<br />For the value field, you can also use following shortcodes to apply dynamic value:';
		$html .= '<br /><code>[w4pl_date day=+6 hour=-1 format="Y-m-d H:i:s"]</code> - for displaying datetime based on current time';
		$html .= '<br /><code>[w4pl_time day=+6 hour=-1]</code> - for displaying timestamp based on current time';
		$html .= '<br /><br />Note: Above Shortcodes generates times in GMT timezone. To compare time saved in another timezone, use hour attribute. for example: [w4pl_date hour=+6] will generate the time what is identical to GMT+6 timestamp.';
		$html .= '</p>';

		$html .= '</div><!--.wffw-->';
		$html .= '</div><!--.w4pl_group_fields_wrap--></div><!--.w4pl_group_fields--></div><!--#w4pl_field_group_date_query-->';

		$fields['date_query'] = array(
			'position' => '140',
			'type'     => 'html',
			'html'     => $html,
		);

		return $fields;
	}

	/**
	 * Get date query form row
	 *
	 * @param  array  $data [description].
	 */
	public function get_date_query_form_row( $data = array() ) {
		$data = wp_parse_args(
			$data,
			array(
				'column'  => '',
				'key'     => '',
				'compare' => '',
				'value'   => '',
			)
		);

		return '<tr class="wff_clone_item">'
			. '<td class="column">'
			. w4pl_form_child_field_html(
				array(
					'name'   => 'w4pl[date_query][column][]',
					'type'   => 'select',
					'option' => W4PL_Config::date_query_column_choices(),
					'value'  => $data['column'],
				)
			)
			. '</td>'
			. '<td class="key">'
			. w4pl_form_child_field_html(
				array(
					'name'   => 'w4pl[date_query][key][]',
					'type'   => 'select',
					'option' => W4PL_Config::date_query_key_choices(),
					'value'  => $data['key'],
				)
			)
			. '</td>'
			. '<td class="compare">'
			. w4pl_form_child_field_html(
				array(
					'name'        => 'w4pl[date_query][compare][]',
					'input_class' => 'w4pl_field_compare',
					'type'        => 'select',
					'option'      => W4PL_Config::date_query_compare_choices(),
					'value'       => $data['compare'],
				)
			)
			. '</td>'
			. '<td class="value">'
			. w4pl_form_child_field_html(
				array(
					'name'        => 'w4pl[date_query][value][]',
					'input_class' => 'w4pl_field_value',
					'type'        => 'text',
					'value'       => $data['value'],
				)
			)
			. '</td>'
			. '<td class="w4pl_date_query_action_cell">
				<span class="wff_clone_remove_btn button">' . __( 'Remove', 'w4-post-list' ) . '</span>
			</td>'
			. '
		</tr>';
	}

	/**
	 * Set default options
	 *
	 * @param  array $options List options.
	 */
	public function pre_save_options( $options ) {
		if ( empty( $options['date_query_relation'] ) ) {
			$options['date_query_relation'] = 'AND';
		}

		return $options;
	}

	/**
	 * Filter options
	 *
	 * @param  array $options List options.
	 */
	public function pre_get_options( $options ) {
		if ( ! empty( $options['date_query'] ) ) {
			$date_query = W4PL_Utils::filter_multi_row_submit( $options['date_query'] );
			if ( $date_query ) {
				array_pop( $date_query );
			}
			$options['date_query'] = $date_query;
		}
		if ( empty( $options['date_query_relation'] ) ) {
			$options['date_query_relation'] = 'AND';
		}
		return $options;
	}

	/**
	 * Parse query arguments
	 *
	 * @param  [type] $list [description]
	 * @return [type]       [description]
	 */
	public function parse_query_args( $list ) {
		if ( in_array( $list->options['list_type'], array( 'posts', 'terms.posts', 'users.posts' ) ) ) {
			if ( ! empty( $list->options['date_query'] ) ) {
				$list->posts_args['date_query'] = array();

				foreach ( $list->options['date_query'] as $dq ) {
					if ( in_array( $dq['compare'], array( 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN' ) ) ) {
						$dq['value'] = explode( ',', $dq['value'] );
						$dq['value'] = array_map( 'trim', $dq['value'] );
					}

					if ( is_array( $dq['value'] ) ) {
						$dq['value'] = array_map( 'do_shortcode', $dq['value'] );
					} elseif ( ! empty( $dq['value'] ) ){
						$dq['value'] = do_shortcode( $dq['value'] );
					}

					$dq[ $dq['key'] ] = $dq['value'];
					unset( $dq['key'], $dq['value'] );

					$list->posts_args['date_query'][] = $dq;
				}

				$list->posts_args['date_query']['relation'] = $list->options['date_query_relation'];
			}

			# W4PL_Utils::d($list->posts_args['date_query']);
		}
	}
}
