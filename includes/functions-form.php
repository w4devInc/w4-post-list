<?php
/**
 * Form related functions.
 *
 * @package W4_Post_List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Create a html form based on passed arguments.
 *
 * @param  array $fields  Array of fields.
 * @param  array  $values Field values.
 * @return string         Html form template.
 */
function w4pl_form_fields( $fields = array(), $values = array(), $form_args = array() ) {
	if ( ! is_array( $fields ) ) {
		$fields = array();
	}

	if ( ! is_array( $values ) ) {
		$values = array();
	}

	if ( ! is_array( $form_args ) ) {
		$form_args = array();
	}

	if ( empty( $form_args['qv'] ) ) {
		$form_args['qv'] = array();
	}

	if ( empty( $form_args['method'] ) )
		$form_args['method'] = 'POST';

	if ( empty( $form_args['class'] ) )
		$form_args['class'] = 'form-wrap w4pl_form';

	if ( empty( $form_args['action'] ) ) {
		$schema = is_ssl() ? 'https://' : 'http://';
		$form_args['action'] = $schema . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}

	if ( ! empty( $form_args['qv'] ) ) {
		$query_vars = array();
		foreach ( $form_args['qv'] as $q ) {
			if ( isset( $_GET[$q] ) && $_GET[$q] != '' ) {
				$query_vars[$q] = trim( $_GET[$q] );
			}
		}

		if ( ! empty( $query_vars ) ) {
			$form_args['action'] = add_query_arg( $query_vars, $form_args['action'] );
		}
	}

	if ( empty( $form_args['button_text'] ) ) {
		$form_args['button_text'] = __( 'Update', 'w4-post-list' );
	}

	$html = '';

	if ( ! isset( $form_args['no_form'] ) ) {
		$html .= '<form';
		$attr_keys = array( 'class', 'id', 'name', 'title', 'enctype', 'method', 'action' );
		foreach ( $form_args as $name => $attr ) {
			if ( ! empty( $name ) && in_array( $name, $attr_keys ) ) {
				$html .= ' '. $name .'="'. esc_attr( $attr ) .'"';
			}
		}
		$html .= '>';
	}

	if ( ! empty( $form_args['after_tag'] ) ) {
		$html .= $form_args['after_tag'];
	}

	if ( isset( $form_args['button_before'] ) &&  $form_args['button_before'] === true ) {
		$html .= "<p class='form_button_container button_container_top'><input type='submit' value='". $form_args['button_text'] ."' class='form_button button_top'></p>";
	}

	foreach ( $fields as $field ) {
		if ( isset( $field['name'] ) && $field['name'] != '' && ! array_key_exists( 'value', $field ) ) {
			$name = isset( $field['option_name'] ) ? $field['option_name'] : $field['name'];
			if ( array_key_exists( $name, $values ) ) {
				$field['value'] = $values[$name];
			} else {
				$field['value'] = '';
			}
		}

		$html .= w4pl_form_field_html( $field );
	}

	if ( ! isset( $form_args['button_after'] ) ||  $form_args['button_after'] !== false ) {
		$html .= "<p class='form_button_container button_container_bottom'><input type='submit' value='". $form_args['button_text'] ."' class='button-primary form_button button_bottom'></p>";
	}

	if ( ! empty( $form_args['form_closing'] ) ) {
		$html .= $form_args['form_closing'];
	}

	if ( ! isset( $form_args['no_form'] ) ) {
		$html .= '</form>';
	}

	return $html;
}

/**
 * Unwrapped field template
 *
 * @param  array  $args Field arguments.
 * @return string       Html field template.
 */
function w4pl_form_child_field_html( $args = array() ) {
	$args['label'] = '';
	$args['field_wrap'] = false;
	$args['label_wrap'] = false;
	$args['input_wrap'] = false;

	return w4pl_form_field_html( $args );
}


/**
 * Field template
 *
 * @param  array  $args Field arguments.
 * @return string       Html field template.
 */
function w4pl_form_field_html( $args = array() ) {
	if ( ! is_array( $args ) ) {
		return '';
	}

	$defaults = array(
		'label' 		=> '',
		'name' 			=> '',
		'type'			=> 'html',
		'html'			=> '',
		'placeholder'	=> '',
		'input_html'	=> '',
		'input_attr'	=> '',
		'desc'			=> '',
		'desc2'			=> '',
		'default' 		=> '',
		'value' 		=> '',
		'required' 		=> 'n',

		'id' 			=> '',
		'class'			=> '',
		'style' 		=> '',
		'attrs' 		=> array(),

		'before'		=> '',
		'after'			=> '',

		'field_wrap'	=> true,
		'field_before'	=> '',
		'field_after'	=> '',

		'label_wrap'	=> true,
		'label_wrap_before' => '',
		'label_before'	=> '',
		'label_after'	=> '',

		'input_wrap'	=> true,
		'input_wrap_before'	=> '',
		'input_before'	=> '',
		'input_after'	=> '',
		'input_class'	=> '',
		'input_html'		=> '',
		'input_attr'		=> ''
	 );

	$args = wp_parse_args( $args, $defaults );

	if ( empty( $args['id'] ) ) {
		$args['id'] = w4pl_form_field_id( $args['name'] );
	}

	extract( $args );

	if ( '' === $value ) {
		$value = $default;
	}

	$attr = '';
	if ( ! empty( $style ) ) {
		$attrs['style'] = $style;
	}

	foreach ( $attrs as $an => $av ) {
		$attr .= ' '. $an .'="'. esc_attr( $av ) .'"';
	}

	// simply include a pre option for combo fields.
	if ( in_array( $type, array( 'select', 'select_multi', 'select2', 'checkbox', 'radio' ) ) ) {
		if ( isset( $option_pre ) && ! empty( $option_pre ) && is_array( $option_pre ) ) {
			$_option = $option_pre;
			foreach ( $option as $k => $v ) {
				$_option[$k] = $v;
			}
			$option = $_option;
		}
	}

	// escape text and hidden field values to pass double or single quote
	if ( in_array( $type, array( 'hidden', 'text' ) ) ) {
		$value = esc_attr( $value );
	}

	$html .= $before;

	if ( ! in_array( $type, array( 'html', 'hidden' ) ) && $field_wrap ) {
		$html .= sprintf(
			'<div class="%1$s"%2$s>',
			w4pl_form_pitc_class( 'wffw', $id, $type, $class ),
			$attr
		);
	}

	$html .= $field_before;

	switch( $type ):

		case "hidden":
			$html .= sprintf(
				'<input class="%1$s %5$s" id="%2$s" name="%3$s" value="%4$s" type="hidden" />',
				w4pl_form_pitc_class( 'wff', $id, $type ),
				$id,
				$name,
				$value,
				$input_class
			);
			break;

		case "text":
		case "textarea":
		case "select":
		case "select_multi":
		case "radio":
		case "checkbox":
		case "image":
		case "image_src":
		case "select2":
		case "text_combo":
		case "html_input":

			// label
			$html .= $label_wrap_before;
			$html .= w4pl_form_field_label( $args );

			// description
			if ( ! empty( $desc ) ) {
				$html .= sprintf(
					'<div class="%1$s">%2$s</div>',
					w4pl_form_pitc_class( 'wffdw', $id, $type ),
					$desc
				);
			}

			// input
			$html .= $input_wrap_before;
			if ( $input_wrap ) {
				$html .= sprintf( '<div class="%1$s">', w4pl_form_pitc_class( 'wffew', $id, $type ) );
			}

			$html .= $input_before;

			if ( $type == 'text' ) {
				$html .= sprintf(
					'<input class="%1$s %5$s" id="%2$s" name="%3$s" value="%4$s" type="text" placeholder="%6$s" />',
					w4pl_form_pitc_class( 'wff', $id, $type ), $id, $name, $value, $input_class, $placeholder
				 );

			} elseif ( $type == 'textarea' ) {
				$html .= sprintf(
					'<textarea class="%1$s %5$s" id="%2$s" name="%3$s" placeholder="%6$s">%4$s</textarea>',
					w4pl_form_pitc_class( 'wff', $id, $type ), $id, $name, $value, $input_class, $placeholder
				 );

			} elseif ( $type == 'select' ) {
				$html .= sprintf( '<select class="%1$s %5$s" id="%2$s" name="%3$s"%4$s>', w4pl_form_pitc_class( 'wff', $id, $type ), $id, $name, $input_attr, $input_class );
				foreach ( $option as $k => $l ) {
					$_attr = '';
					$sel = $value == $k ? ' selected="selected"' : '';
					if ( is_array( $l ) )
					{
						$_attr = isset( $l['attr'] ) ? $l['attr'] : '';
						$l = $l['label'];
					}

					$html .= sprintf( '<option value="%1$s"%2$s%4$s>%3$s</option>', $k, $sel, $l, $_attr );
				}
				$html .= '</select>';

			} elseif ( $type == 'radio' ) {
				foreach ( $option as $k => $l ) {
					$sel = $value == $k ? ' checked="checked"' : '';
					$html .= sprintf(
						'<label><input id="%1$s_%2$s" class="%6$s" name="%3$s" value="%2$s" type="radio"%4$s /> %5$s</label>', $id, $k, $name, $sel, $l, $input_class
					 );
				}

			} elseif ( $type == 'checkbox' ) {
				foreach ( $option as $k => $l )
				{
					$before = '';
					$sel = is_array( $value ) && in_array( $k, $value ) ? ' checked="checked"' : '';
					if ( is_array( $l ) ) {
						$before = isset( $l['before'] ) ? $l['before'] : '';
						$l = $l['label'];
					}

					$html .= $before;
					$html .= sprintf( '<label><input id="%1$s_%2$s" name="%3$s[]" value="%2$s" type="checkbox"%4$s class="%6$s" /> %5$s</label>', $id, $k, $name, $sel, $l, $input_class );
				}

			} elseif ( ! empty( $input_html ) ) {
				$html .= $input_html;
			}

			$html .= $input_after;

			if ( $input_wrap ) {
				$html .= '</div>';
			}

			// description
			if ( ! empty( $desc2 ) ) {
				$html .= sprintf( '<div class="%1$s">%2$s</div>', w4pl_form_pitc_class( 'wffdw2', $id, $type ), $desc2 );
			}

		break;

		default:
			if ( ! empty( $callback ) && is_callable( $callback ) ) {
				$html .= call_user_func( $callback, $args );
			}
		break;

	endswitch;

	$html .= $field_after;

	if ( ! in_array( $type, array( 'html', 'hidden' ) ) && $field_wrap ) {
		$html .= '</div>';
	}

	return $html;
}

/**
 * Ouput field label html
 *
 * @param  array $args Field arguments.
 * @return string      Label html code.
 */
function w4pl_form_field_label( $args ) {
	extract( $args );
	$html = '';

	if ( ! empty( $label ) ) {
		if ( $label_wrap ) {
			$html .= sprintf( '<div class="%1$s">', w4pl_form_pitc_class( 'wfflw', $id, $type ) );
		}
		$html .= $label_before;

		if ( $required == 'y' ) {
			$label .= '<span class="req">*</span>';
		}

		// radio checkbox would use span, not label
		if ( in_array( $type, array( 'radio', 'checkbox', 'html_input' ) ) ) {
			$html .= sprintf(
				'<span class="%1$s">%2$s</span>',
				w4pl_form_pitc_class( 'wffl', $id, $type ),
				$label
			);
		} else {
			$html .= sprintf(
				'<label class="%1$s" for="%2$s">%3$s</label>',
				w4pl_form_pitc_class( 'wffl', $id, $type ),
				$id,
				$label
			);
		}

		$html .= $label_after;
		if ( $label_wrap ) {
			$html .= '</div>';
		}
	}

	return $html;
}

/**
 * Generate a css class based on form prefix, field id, field type & other class
 *
 * @param  string $pref  Prefix.
 * @param  string $id    Field id.
 * @param  string $type  Field type.
 * @param  string $class Other classes
 * @return string        Class name
 */
function w4pl_form_pitc_class( $pref = '', $id = '0', $type = '', $class = '' ) {
	return trim( esc_attr( "{$pref} {$pref}i_{$id} {$pref}t_{$type} {$class}" ) );
}

/**
 * Sanitize field id attribute
 *
 * @param  string $raw_id Raw id
 * @return string         Sanitized id
 */
function w4pl_form_field_id( $raw_id = '' ) {
	$sanitized = preg_replace( '|%[a-fA-F0-9][a-fA-F0-9]|', '', $raw_id );
	$sanitized = preg_replace( '/[^A-Za-z0-9_-]/', '_', $sanitized );
	$sanitized = str_replace( '__', '_', $sanitized );
	$sanitized = trim( $sanitized, '_' );
	return $sanitized;
}
