<?php
namespace Wpform\Field;
use Wpform\Abstracts\Field;

class Textarea extends Field {
	function __construct( $data = array() ) {
		$data['type'] = 'textarea';
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
				$html .= sprintf( '<div class="%1$s %2$s"%3$s>', $this->form_pitc_class('wf-field-input-wrap', $id, $type), $input_wrap_class, $input_wrap_attr );
			}
	
			$html .= $input_before;
			$html .= sprintf( 
				'<textarea id="%2$s" class="%1$s %5$s" name="%3$s"%6$s>%4$s</textarea>', 
				$this->form_pitc_class('wf-field', $id, $type), $id, $name, $value, $input_class, $input_attr
			);
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

		$html .= '</div>';

		return $html;
	}
}

?>