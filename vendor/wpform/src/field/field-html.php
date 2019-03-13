<?php
namespace Wpform\Field;
use Wpform\Abstracts\Field;

class Html extends Field {
	function __construct( $data = array() ) {
		$data['type'] = 'html';
		parent::__construct( $data );
	}
	public function get_html( $form ){
		return $this->html;
	}
}

?>