<?php
class W4PL_Helper_Style extends W4PL_Core
{
	function __construct()
	{
		add_filter( 'w4pl/admin_list_fields', array($this, 'admin_list_fields'), 10, 2 );

		add_action( 'w4pl/admin_print_css', array($this, 'admin_print_css'), 10 );

		add_action( 'w4pl/admin_print_js', array($this, 'admin_print_js'), 10 );

		add_filter( 'w4pl/parse_html', array($this, 'parse_html'), 60 );
	}

	// Meta box
	public function admin_list_fields( $fields, $post_data )
	{
		/* Style */

		$fields['class'] = array(
			'position'		=> '120',
			'before'		=> '<h2>Style</h2>',
			'option_name' 	=> 'class',
			'name' 			=> 'w4pl[class]',
			'label' 		=> 'List class',
			'type' 			=> 'text',
			'input_class' 	=> 'widefat',
			'desc' 			=> 'add html class to the list'
		);
		$fields['css'] = array(
			'position'		=> '125',
			'option_name' 	=> 'css',
			'name' 			=> 'w4pl[css]',
			'label' 		=> 'Custom css',
			'type' 			=> 'textarea',
			'input_class' 	=> 'widefat',
			'desc' 			=> 'this css loaded just before the list template. to make the style unique just for this list, use <code>#w4pl-list-'. $post_data['id'] . '</code> as parent selector. Alternatively, you can use <code>#w4pl-[listid]</code> which will do the same thing.'
		);
		$fields['js'] = array(
			'position'		=> '130',
			'option_name' 	=> 'js',
			'name' 			=> 'w4pl[js]',
			'label' 		=> 'JavaScript',
			'type' 			=> 'textarea',
			'input_class' 	=> 'widefat',
			'desc' 			=> 'this js loaded just after the list template.'

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