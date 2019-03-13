<?php
namespace Wpform\Abstracts;

abstract class Form implements \ArrayAccess {

	public $data = array(
		'settings' 	=> array(),
		'fields' 	=> array(),
		'values' 	=> array(),
		'rendered' 	=> false
	);
	public function __construct() {}
	public function set_settings( $settings = array() ) {
		$this->settings = $settings;
	}
	public function set_values( $values = array() ) {
		$this->values = $values;
	}
	public function add_fields( $fields ) {
		foreach( $fields as $field ){
			$this->add_field( $field );
		}
	}

	public function set_setting( $key, $val = null ) {
		$this->settings[$key] = $val;
	}
	public function set_value( $key, $val = null ) {
		$this->values[$key] = $val;
	}
	public function add_field( $data ) {
		$this->data['fields'][] = $this->create_field( $data );
	}
	public function create_field( $data ) {
		if( ! isset($data['type']) ){
			$data['type'] = 'html';
		}
		$class_name = '\\Wpform\\Field\\'. ucwords( str_replace( '-', '_', $data['type']) );
		if( ! class_exists( $class_name ) ){
			$class_name = '\\Wpform\\Field\\Html';
		}

		return new $class_name( $data, $this );
	}
	public function render(){
		echo $this->toHtml();
	}

	// usability
	public function toHtml(){}
	public function toArray(){
		return $this->data;
	}
	public function toJson(){
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
