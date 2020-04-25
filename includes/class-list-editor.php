<?php
/**
 * List editor class.
 *
 * @class W4PL_List_Editor
 * @package W4 Post List
 */

defined( 'ABSPATH' ) || exit;

class W4PL_List_Editor {

	public $options;

	public function _construct( $options = array() ) {
		$this->options = apply_filters('w4pl/pre_get_options', $options);
	}

	public function render_form() {
		$options = $this->options;
		include(w4pl()->plugin_dir() . 'admin/views/list-edit-form.php');
	}
}
