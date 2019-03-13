<?php
namespace Wpform\Abstracts;

abstract class Field implements \ArrayAccess {

	public $data = array();

	function __construct( $data = array() ) {
		$this->data = $data;
	}
	public function form_field_label( $data ){
		extract( $data );
		$html = '';

		if( !empty($label) ){
			if( $label_wrap ){
				$html .= sprintf( '<div class="%1$s">', $this->form_pitc_class('wf-field-label-wrap', $id, $type) );
			}
			$html .= $label_before;

			if( isset($input_attrs['required']) && $input_attrs['required'] ){
				$label .= '<span class="req">*</span>';
			}

			// radio checkbox would use span, not label
			if( in_array($type, array('text', 'textarea', 'select', 'url', 'number') ) ){
				$html .= sprintf( '<label class="%1$s" for="%2$s">%3$s</label>', $this->form_pitc_class('wf-field-label', $id, $type), $id, $label );
			} else {
				$html .= sprintf( '<span class="%1$s">%2$s</span>', $this->form_pitc_class('wf-field-label', $id, $type), $label );
			}
	
			$html .= $label_after;
			if( $label_wrap ){
				$html .= '</div>';
			}
		}
	
		return $html;
	}
	public function form_pitc_class( $pref = '', $id = '', $type = '', $class = '' ){
		$return = "{$pref}";
		if( !empty($id) ){
			$return .= " {$pref}-id-{$id}";
		}
		if( !empty($type) ) { 
			$return .= " {$pref}-type-{$type}";
		}
		if( !empty($class) ){ 
			$return .= " {$class}"; 
		}
		return trim( esc_attr( $return ) );
	}
	public function form_field_id( $raw_id = '' ){
		$sanitized = preg_replace( '|%[a-fA-F0-9][a-fA-F0-9]|', '', $raw_id );
		$sanitized = preg_replace( '/[^A-Za-z0-9_-]/', '_', $sanitized );
		$sanitized = str_replace( '__', '_', $sanitized );
		$sanitized = trim( $sanitized, '_' );
		return $sanitized;
	}
	public function sanitize_data( $data ){
		$defaults = array(
			'type'				=> 'html',
			'name' 				=> '',
			'label' 			=> '',
			'html'				=> '',
			'desc'				=> '',
			'default' 			=> '',
			'value' 			=> '',

			'id' 				=> '',
			'class'				=> '',
			'style' 			=> '',
			'attrs' 			=> array(),
	
			'before'			=> '',
			'after'				=> '',
	
			'field_wrap'		=> true,
			'field_before'		=> '',
			'field_after'		=> '',
	
			'label_wrap'		=> true,
			'label_wrap_before' => '',
			'label_before'		=> '',
			'label_after'		=> '',
	
			'input_wrap'		=> true,
			'input_wrap_before'	=> '',
			'input_wrap_class'	=> '',
			'input_wrap_attr'	=> '',
			'input_before'		=> '',
			'input_after'		=> '',
			'input_class'		=> '',
			'input_html'		=> '',
			'input_attrs'		=> array(),
			'input_style'		=> ''
		);

		$data = array_merge( $defaults, $data );

		if( empty($data['id']) && false !== $data['id'] ){
			$data['id'] = $this->form_field_id( $data['name'] );
		}

		if( ! isset($data['value']) || '' === $data['value'] ) {
			$data['value'] = $data['default'];
		}

		$data['attr'] = '';
		foreach( $data['attrs'] as $an => $av ) {
			$data['attr'] .= ' '. $an .'="'. esc_attr($av) .'"';
		}
		$data['attr'] = trim( $data['attr'] );

		$data['input_attr'] = '';
		foreach( $data['input_attrs'] as $an => $av ) {
			$data['input_attr'] .= ' '. $an .'="'. esc_attr($av) .'"';
		}

		// simply include a pre option for combo fields.
		if( in_array($data['type'], array('select', 'select_multi', 'select2', 'checkbox', 'radio') ) ){
			if( isset($data['choices_pre']) && !empty($data['choices_pre']) && is_array($data['choices_pre']) ){
				$_choices = $data['choices_pre'];
				if( ! empty($data['choices']) ){
					foreach( $data['choices'] as $index => $choice ){
						$_choices[$index] = $choice;
					}
				}
				$data['choices'] = $_choices;
			}
			if( empty($data['choices']) ){
				$data['choices'] = array();
			}
		}

		// escape text and hidden field values to pass double or single quote
		if( in_array($data['type'], array('hidden', 'text', 'url') ) ){
			$data['value'] = @htmlspecialchars( $data['value'] );
		}

		return $data;
	}

	public function render(){
		echo $this->toHtml();
	}
	// usability
	public function toHtml( $form ){}
	public function toArray( $form ){
		return $this->data;
	}
	public function toJson( $form ){
		return json_encode( $this->data );
	}
	// no magic
	public function __sleep() {
        return array_keys($this->data);
	}
	public function __wakeup() {}
	public function __toString(){
		echo 'field '. $this->data['type'];
	}
	public function &__get ($key) {
        return $this->data[$key];
    }
	public function __set($key,$value) {
        $this->data[$key] = $value;
    }
	public function __isset ($key) {
        return isset($this->data[$key]);
    }
	public function __unset($key) {
        unset($this->data[$key]);
    }
	// array access
    public function offsetGet($offset) {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }
	public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }
	public function offsetExists($offset) {
        return isset($this->data[$offset]);
    }
	public function offsetUnset($offset) {
        unset($this->data[$offset]);
    }
}
