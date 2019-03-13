<?php
namespace Wpform\Field;
use Wpform\Abstracts\Field;

class Repeater extends Field {
	function __construct( $data = array() ) {
		$data['type'] = 'repeater';
		parent::__construct( $data );
	}
	public function get_html( $form ){
		$data = $this->sanitize_data( $this->data );
		extract( $data );

		$html = $before;

		if( $field_wrap ){
			$html .= sprintf( '<div class="%1$s"%2$s>', $this->form_pitc_class('wf-field-wrap', $id, $type, $class), $attr );
		}

		$html .= $field_before;

			// label
			$html .= $label_wrap_before;
			$html .= $this->form_field_label( $data );

			// description
			if( ! empty($desc) ){
				$html .= sprintf( '<div class="%1$s">%2$s</div>', $this->form_pitc_class('wf-field-desc-wrap', $id, $type), $desc );
			}

			// input
			$html .= $input_wrap_before;
			if( $input_wrap ){
				$html .= sprintf( '<div class="%1$s">', $this->form_pitc_class('wf-field-input-wrap', $id, $type) );
			}
		
			$html .= $input_before;
		
			if( empty($value) ) {
				$value = $default;
			}
		
			$total_columns = 0;
			foreach( $fields as $key => $rf ) {
				if( in_array($rf['type'], array('text', 'number', 'html', 'select') ) ){
					++ $total_columns;
				}
				if( ! empty($rf['name']) ){
					$fields[$key]['name'] = $data['name'] ."[KEY][". $rf['name'] . "]";
					$fields[$key]['option_name'] = $rf['name'];
				}
				if( empty($rf['id']) ){
					$rf['id'] = $fields[$key]['id'] =  $rf['name'];
				}
				if( ! empty($rf['id']) ){
					$fields[$key]['id'] = $data['id'] ."_". $rf['id'];
				}
				if( empty($rf['class']) ){
					$fields[$key]['class'] = $rf['id'];
				}
			}
		
			$key = $data['key'];
		
			$html .= '<table id="wf_repeated_'.$key.'" class="wf_repetable" data-parent="'.$key.'"><thead><tr>';
			foreach( $fields as $repeat_field ) {
				if( in_array($repeat_field['type'], array('text', 'number', 'html', 'select') ) ){
					$html .= '<th class="wf_col '. $repeat_field['class'] .'">'. $repeat_field['label'] .'</th>';
				}
			}
			$html .= sprintf( '<th>%s</th>', 'Action' );
			$html .= '</tr></thead><tbody>';
		
			// load existing fields
			if( ! empty($value) && is_array($value) ){
				$i = 1;
	
				foreach( $value as $_value ){
					$hiddens = '';
					$row_key = 'row-'. $i;
	
					$html .= '<tr class="wf_row">';
	
					foreach( $fields as $repeat_field ) {
	
						$repeat_field['name'] = str_replace( 'KEY', $row_key, $repeat_field['name'] );
	
						$option_name = $repeat_field['option_name'];
						if( isset($_value[$option_name]) ) {
							$repeat_field['value'] = $_value[$option_name];
						}
	
						if( in_array($repeat_field['type'], array('hidden') ) ) {
							$field = $form->create_field( $repeat_field );
							$hiddens .= $field->get_html( $form );
						}
						elseif( in_array($repeat_field['type'], array('text', 'number', 'html', 'select') ) ) {
							$html .= '<td class="wf_col '. $repeat_field['class'] .'">';

							$repeat_field['label'] = '';
							$repeat_field['field_wrap'] = false;
							$repeat_field['label_wrap'] = false;
							$repeat_field['input_wrap'] = false;

							$field = $form->create_field( $repeat_field );
							$html .= $field->get_html( $form );
							$html .= '</td>';
						}
					}
		
					$html .= '<td>';
					$html .= '<a href="#" class="wf_repeater_remove" data-parent="'.$key.'">Remove</a>';
					$html .= $hiddens;
					$html .= '</td>';
					$html .= '</tr>';
					
					++ $i;
				}
			}
		
			$html .= '</tbody><tfoot><tr>';
			$html .= '<td colspan="'. ( $total_columns + 1 ) .'"><a href="#" class="wf_repeater_add" data-parent="'.$key.'">Add Item</a></td>';
			$html .= '<tr></tfoot></table>';
		
			$hiddens = '';
	
			$html .= '<table id="wf_repeater_'. $key .'" class="wf_repeater" data-parent="'.$key.'"><tbody>';
			$html .= '<tr class="wf_row">';
			foreach( $fields as $repeat_field ) {
				if( in_array($repeat_field['type'], array('hidden') ) ) {
					$field = $form->create_field( $repeat_field );
					$hiddens .= $field->get_html( $form );
				}
				elseif( in_array($repeat_field['type'], array('text', 'number', 'html', 'select') ) ) {
					$html .= '<td class="wf_col '. $repeat_field['class'] .'">';

					$repeat_field['label'] = '';
					$repeat_field['field_wrap'] = false;
					$repeat_field['label_wrap'] = false;
					$repeat_field['input_wrap'] = false;
					$field = $form->create_field( $repeat_field );

					$html .= $field->get_html( $form );
					$html .= '</td>';
				}
			}
			$html .= '<td>';
			$html .= '<a href="#" class="wf_repeater_remove" data-parent="'.$key.'">Remove</a>';
			$html .= $hiddens;
			$html .= '</td>';
			$html .= '</tr>';
			$html .= '</tbody></table>';
	
			$html .= $input_after;
			if( $input_wrap ){
				$html .= '</div>';
			}

		$html .= $field_after;
		
		if( isset($desc_after) ){
			if( ! empty($desc_after) ){
				$html .= sprintf( '<div class="%1$s">%2$s</div>', $this->form_pitc_class('wf-field-desc-after-wrap', $id, $type), $desc_after );
			}
		}

		if( $field_wrap ){
			$html .= '</div>';
		}

		return $html;
	}
}

?>