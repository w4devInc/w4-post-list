<?php
namespace Wpform\Form;
use Wpform\Abstracts\Form;

class Simple extends Form {
	function __construct() {
		parent::__construct();
	}

	public function toHtml() {
		$this->rendered = true;
		if( ! is_array( $this->fields ) ) {
			$this->fields = array();
		}
		if( ! is_array( $this->values ) ) {
			$this->values = array();
		}
		if( ! is_array($this->settings) ) {
			$this->settings = array();
		}

		if( empty( $this->settings['method'] )){
			$this->settings['method'] = 'POST';
		}
		if( empty( $this->settings['class'] )){
			$this->settings['class'] = 'wf wf-basic';
		} else {
			$this->settings['class'] = 'wf '. $this->settings['class'];
		}
		if( ! empty( $this->settings['ajax'] ) ) {
			$this->settings['class'] .= ' wf-ajax';
		}
		if( ! empty( $this->settings['ajax'] ) && empty($this->settings['loading_text']) ) {
			$this->settings['loading_text'] = 'Updating';
		}
		if( empty($this->settings['button_text']) ) {
			$this->settings['button_text'] = 'Update';
		}
		// @since v:2.0
		if( empty( $this->settings['attr'] )){
			$this->settings['attr'] = '';
		}
		if( ! empty($this->settings['loading_text']) ) {
			$this->settings['attr'] .= ' data-loading_text="'. esc_attr($this->settings['loading_text']) .'"';
		}
		if( ! empty( $this->settings['context'] ) ) {
			$this->settings['class'] .= ' wf-field-context-'. $this->settings['context'];
		}

		if( empty( $this->settings['action']) ) {
			$this->settings['action'] = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		}

		// query variables
		if( ! empty($this->settings['qv']) ) {
			$query_vars = array();
			foreach( $this->settings['qv'] as $q) {
				if( isset($_GET[$q]) && $_GET[$q] != '' ) {
					$query_vars[$q] = trim($_GET[$q]);
				}
			}

			if( ! empty( $query_vars) ){
				$this->settings['action'] = add_query_arg( $query_vars, $this->settings['action'] );
			}
		}

		$html = '';

		// form opening tag
		if( ! isset($this->settings['no_form']) ) {
			$html .= '<form';
			$attr_keys = array( 'class', 'id', 'name', 'title', 'enctype', 'method', 'action' );
			foreach( $this->settings as $name => $attr ) {
				if( ! empty($name) && in_array($name, $attr_keys) ) {
					$html .= ' '. $name .'="'. esc_attr( $attr ) .'"';
				}
			}
			if( ! empty($this->settings['attr']) ) {
				$html .= $this->settings['attr'];
			}
			$html .= '>';
		}

		if( ! empty( $this->settings['after_tag'] ) ) {
			$html .= $this->settings['after_tag'];
		}

		if( ! empty( $this->settings['title'] ) ) {
			$html .= '<div class="wf-form-title">' . $this->settings['title'] . '</div>';
		}

		// Submit button on top of the form
		if( isset( $this->settings['submit_top'] ) &&  $this->settings['submit_top'] === true ) {
			$html .= "<div class='wf-field-wrap wf-field-wrap-type-submit wf-field-wrap-type-submit-top'>
				<input type='submit' value='". $this->settings['button_text'] ."' class='button button-primary form_button button_top'>
			</div>";
		}

		foreach( $this->fields as $k => &$field ) {
			if( isset($field->name) && '' != $field->name && ! isset($field->value) ) {
				$name = isset($field->option_name) ? $field->option_name : $field->name;
				if( array_key_exists($name, $this->values) ) {
					$field->value = $this->values[$name];
				} else {
					$field->value = '';
				}
			}
			if( empty($field->input_class) && ! empty($field->name) && 'action' == $field->name ){
				$field->input_class = 'form_action';
			}

			$html .= $field->get_html( $this );
		}

		// Submit button at the end of the form
		if( ! isset( $this->settings['submit_bottom'] ) ||  $this->settings['submit_bottom'] !== false ) {
			$html .= "<div class='wf-field-wrap wf-field-wrap-type-submit wf-field-wrap-type-submit-bottom'>
				<input type='submit' value='". $this->settings['button_text'] ."' class='form_button button_bottom'>
			</div>";
		}

		if( ! empty( $this->settings['form_closing'] ) ) {
			$html .= $this->settings['form_closing'];
		}

		if( ! isset( $this->settings['no_form'] ) ) {
			$html .= '</form>';
		}
	
		return $html;
	}
}

?>