<?php
/**
 * Admin Init
 * @package W4 Post List
 * @author Shazzad Hossain Khan
 * @url http://w4dev.com/plugins/w4-post-list
**/

class W4PL_List_Editor
{
	public $options;
	public function _construct($options = array())
	{
		$this->options = apply_filters('w4pl/pre_get_options', $options);
	}

	public function render_form()
	{
		$options = $this->options;
		include(w4pl()->plugin_dir() . 'admin/views/list-edit-form.php');
	}
}
?>
