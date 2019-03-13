<?php
namespace Wpform\Field;
use Wpform\Abstracts\Field;

class Hidden extends Field {
	function __construct( $data = array() ) {
		$data['type'] = 'hidden';
		parent::__construct( $data );
	}
	public function get_html( $form ){
		$data = $this->sanitize_data( $this->data );
		extract( $data );

		$html = $before;
		$html .= sprintf( 
			'<input class="%1$s %5$s" id="%2$s" name="%3$s" value="%4$s" type="%7$s"%6$s />', 
			$this->form_pitc_class('wf-field', $id, $type), $id, $name, $value, $input_class, $input_attr, $type
		);

		return $html;
	}
}

?>