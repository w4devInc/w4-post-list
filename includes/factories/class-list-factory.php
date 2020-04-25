<?php
/**
 * List Factory Class
 * @package W4 Post List
 * @author Shazzad Hossain Khan
 * @url http://w4dev.com/plugins/w4-post-list
**/

class W4PL_List_Factory
{
	public static function get_list($options)
	{
		if (! isset($options['id'])) {
			throw new Exception('Invalid list id');
			return;
		} elseif (! isset($options['list_type'])) {
			throw new Exception('Invalid list type');
			return;
		}

		$className = 'W4PL_List_'. str_replace([' ', '.'], '_', ucwords(preg_replace('/[^a-zA-Z]/i', ' ', $options['list_type'])));


		if (class_exists ($className)) {
			return new $className($options);
		} else {
			throw new Exception('Invalid list type');
			return;
		}
	}
}
?>
