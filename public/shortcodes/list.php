<?php
/**
 * @package W4 Post List
 * @author Shazzad Hossain Khan
 * @url http://w4dev.com/plugins/w4-post-list
**/


class W4PL_List_Shortcode
{
	function __construct()
	{
		// register shortcode (postlist)
		add_shortcode( 'postlist'								, array($this, 'shortcode'), 6 );
		add_shortcode( 'w4pl-list' 								, array($this, 'shortcode'), 6 );

		// allow shortcode for text widget content
		if( ! has_filter('widget_text', 'do_shortcode')) {
			add_filter('widget_text'								, 'do_shortcode');
		}
	}


	/*
	 * Display List Using Shortcode
	 * @param (array)
	 * @return (string)
	*/

	public function shortcode($attrs)
	{
		$options = $this->parse_shortcode_attrs($attrs);
		if (empty ($options)) {
			return '';
		}

		$options = apply_filters('w4pl/pre_get_options', $options);

		try {
			$list = W4PL_List_Factory::get_list ($options);
			return $list->get_html();
		} catch(Exception $e){
			// not showing error
		}
	}
	
	public function parse_shortcode_attrs($attrs)
	{
		if (isset ($attrs['options'])) {
			$options = maybe_unserialize (base64_decode (str_replace (' ', '', $attrs['options'])));
		} elseif (isset ($attrs['id'])) {
			$options = get_post_meta ($attrs['id'], '_w4pl', true);
			$options['id'] = $attrs['id'];
		} elseif (isset($attrs['slug'])) {
			global $wpdb;
			$post = $wpdb->get_row($wpdb->prepare(
				"SELECT * FROM $wpdb->posts WHERE post_name = %s AND post_type = %s", 
				$attrs['slug'], w4pl()->plugin_slug()
			));
			if ($post) {
				$options = get_post_meta($post->ID, '_w4pl', true);
				$options['id'] = $post->ID;
			}
		} elseif (isset ($attrs['title'])) {
			global $wpdb;
			$post = $wpdb->get_row( $wpdb->prepare( 
				"SELECT * FROM $wpdb->posts WHERE post_title = %s AND post_type = %s", 
				$attrs['title'], w4pl()->plugin_slug()
			));
			if( $post ){
				$options = get_post_meta( $post->ID, '_w4pl', true );
				$options['id'] = $post->ID;
			}
		} else {
			$options = array();

			if (! is_array($attrs)) {
				$attrs = array($attrs);
			}

			$list_id = array_shift($attrs);
			$list_id = (int) $list_id;

			if ($list_id && get_post($list_id)) {
				$options = get_post_meta( $list_id, '_w4pl', true );
				$options['id'] = $list_id;
			}
		}
		
		return $options;
	}
}

	new W4PL_List_Shortcode;
?>
