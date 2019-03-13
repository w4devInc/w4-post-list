<?php
/**
 * @package W4 Post List
 * @author Shazzad Hossain Khan
 * @url http://w4dev.com/plugins/w4-post-list
**/


class W4PL_Helper_No_Items
{
	function __construct()
	{
		add_filter( 'w4pl/list_edit_form_fields', array($this, 'list_edit_form_fields'), 10, 2 );

		add_filter( 'w4pl/pre_save_options', array($this, 'pre_save_options') );

		add_filter( 'w4pl/pre_get_options', array($this, 'pre_get_options') );

		add_filter( 'w4pl/parse_html', array($this, 'parse_html'), 60 );
	}

	// Meta box
	public function list_edit_form_fields( $fields, $post_data )
	{
		if( 'posts' == $post_data['list_type'] ){
			$pos = 99.2;
		}
		// except posts, every other object type is bound within 5-50
		else{
			$pos = 49.2;
		}

		$fields['no_items_text'] = array(
			'position'		=> $pos,
			'option_name' 	=> 'no_items_text',
			'name' 			=> 'w4pl[no_items_text]',
			'label' 		=> 'No items text',
			'type' 			=> 'textarea',
			'input_class' 	=> 'widefat',
			'desc' 			=> __('displayed when no items found by this filter or pagination', 'w4pl')
		);
		/* ========================================= */

		return $fields;
	}

	public function pre_save_options($options)
	{
		foreach( array(
			'no_items_text'	=> ''
		) as $k => $v )
		{
			if( array_key_exists($k , $options) && empty($options[$k ]) )
				unset($options[$k]);
		}
		return $options;
	}


	public function pre_get_options($options)
	{
		foreach( array(
			'no_items_text'	=> ''
		) as $k => $v )
		{
			if( !isset($options[$k]) )
				$options[$k] = '';
		}
		return $options;
	}


	public function parse_html( $obj )
	{
		// unique list class
		if( empty($obj->template) && !empty($obj->options['no_items_text']) ){
			$obj->html = str_replace( 'class="w4pl-inner">', 'class="w4pl-inner">'. $obj->options['no_items_text'], $obj->html );
		}
	}
}

	new W4PL_Helper_No_Items;
?>