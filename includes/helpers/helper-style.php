<?php
/**
 * @package W4 Post List
 * @author Shazzad Hossain Khan
 * @url http://w4dev.com/plugins/w4-post-list
**/


class W4PL_Helper_Style
{
	function __construct()
	{
		add_filter( 'w4pl/list_edit_form_fields', array($this, 'list_edit_form_fields'), 10, 2 );

		add_action( 'w4pl/admin_print_css', array($this, 'admin_print_css'), 10 );

		add_action( 'w4pl/admin_print_js', array($this, 'admin_print_js'), 10 );

		add_filter( 'w4pl/pre_save_options', array($this, 'pre_save_options') );

		add_filter( 'w4pl/pre_get_options', array($this, 'pre_get_options') );

		add_filter( 'w4pl/parse_html', array($this, 'parse_html'), 60 );
	}

	// Meta box
	public function list_edit_form_fields( $fields, $post_data )
	{
		/* Style */
		$fields['before_field_group_style'] = array(
			'position'		=> '165',
			'html' 			=> '<div id="w4pl_field_group_style" class="w4pl_field_group">
				<div class="w4pl_group_title">'. __('Style', 'w4pl') .'</div>
				<div class="w4pl_group_fields"><div class="w4pl_group_fields_wrap">'
		);
		$fields['css'] = array(
			'position'		=> '170',
			'option_name' 	=> 'css',
			'name' 			=> 'w4pl[css]',
			'label' 		=> __('CSS', 'w4pl'),
			'type' 			=> 'textarea',
			'input_class' 	=> 'widefat',
			'desc2' 		=> 'this css loads just before the list template on front-end (not in the HEAD tag, in BODY as inline css). 
								<br />to apply css just for current list, use <code>#w4pl-list-'. $post_data['id'] . '</code> as parent selector, or 
								<br />use <code>#w4pl-list-[listid]</code>, where you can port the style from one list to another by just copying'
		);
		$fields['js'] = array(
			'position'		=> '172',
			'option_name' 	=> 'js',
			'name' 			=> 'w4pl[js]',
			'label' 		=> __('JavaScript', 'w4pl'),
			'type' 			=> 'textarea',
			'input_class' 	=> 'widefat',
			'desc2' 		=> 'javascript loads right after template ends. don\'t use <code>&lt;script&gt;</code> tag',
			'after'			=> '</div><!--.w4pl_group_fields--></div><!--#w4pl_field_group_style-->'
		);
		$fields['class'] = array(
			'position'		=> '174',
			'option_name' 	=> 'class',
			'name' 			=> 'w4pl[class]',
			'label' 		=> __('HTML class name', 'w4pl'),
			'type' 			=> 'text',
			'desc' 			=> 'applied on the wrapper html DIV tag',
			'input_class' 	=> 'widefat'
		);
		$fields['after_field_group_style'] = array(
			'position'		=> '175',
			'html' 			=> '</div><!--.w4pl_group_fields_wrap--></div><!--.w4pl_group_fields--></div><!--#w4pl_field_group_style-->'
		);

		/* ========================================= */

		return $fields;
	}


	public function admin_print_css()
	{
		?>
		#w4pl_css{height:250px;}
        <?php
	}

	public function admin_print_js()
	{
		?>
        <?php
	}

	public function pre_save_options($options)
	{
		foreach( array(
			'class'			=> '',
			'js'			=> '',
			'css'			=> ''
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
			'class'			=> '',
			'js'			=> '',
			'css'			=> ''
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
		$class = trim('w4pl ' . $obj->options['class']);
		$obj->html = str_replace( 'id="w4pl-list-'. $obj->id .'"', 'id="w4pl-list-'. $obj->id .'" class="'. $class .'"', $obj->html );

		if( !empty($obj->options['css']) )
			$obj->css .= str_replace( '[listid]', $obj->id, $obj->options['css'] );
		if( !empty($obj->options['js']) )
			$obj->js .= str_replace( '[listid]', $obj->id, $obj->options['js'] );


		// css push
		if( !empty($obj->css) )
			$obj->html = '<style id="w4pl-css-'. $obj->id .'" type="text/css">' . $obj->css . '</style>' . "\n" . $obj->html;

		// js push
		if( !empty($obj->js) )
			$obj->html .= "\n" . '<script id="w4pl-js-'. $obj->id .'" type="text/javascript">' . $obj->js . '</script>' . "\n";
	
		#echo '<pre>'; print_r($obj->query); echo '</pre>';
	}
}

	new W4PL_Helper_Style;
?>