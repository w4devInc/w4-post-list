<?php
/**
 * The Plugin Class
 * @package WordPress
 * @subpackage W4 POst List
 * @author Shazzad
 * @url http://w4dev.com/about
**/



class W4PL_Plugin
{
	// log created by addming action
	function __construct(){}

	// log created by addming action
	public static function log( $str = '' ){
		do_action('w4pl/log', $str);
	}
	public static function cron_log( $str = '' ){
		if( self::is_cron() ){ self::log( $str ); }
	}
	public static function is_cron(){
		return (bool) ( defined('DOING_CRON') && DOING_CRON );
	}
	public static function is_ajax(){
		return (bool) ( defined('DOING_AJAX') && DOING_AJAX );
	}
	public static function yesno_array(){
		return array(
			'yes' 		=> __('Yes', W4PL_TD),
			'no' 		=> __('No', W4PL_TD)
		);
	}


	/**
	 * Protect non admin's from performing and action
	**/

	public static function admin_auth()
	{
		if( ! current_user_can('install_plugins') ){
			$meg = __('Unauthorized Request', W4PL_TD);

			if( defined('DOING_AJAX') && DOING_AJAX )
			{ self::ajax_error( $meg );	}

			elseif( defined('DOING_CRON') && DOING_CRON )
			{ self::log( $meg ); }

			else
			{ wp_die( $meg ); }
		}
	}
	public static function encode_attrs($attrs){
		return base64_encode(maybe_serialize($attrs));
	}
	public static function decode_attrs($attrs){
		return stripslashes_deep(maybe_unserialize(base64_decode($attrs)));
	}
	public static function order_by_position( $a, $b ){
		if( !isset($a['position']) || !isset($b['position']) ) return -1;
		if( $a['position'] == $b['position'] ) return 0;
	    return ($a['position'] < $b['position']) ? -1 : 1;
	}
	public static function ajax_error( $html, $attrs = array() ){
		self::ajax_response( wp_parse_args( array( 'status'=>'error','html'=>$html ), $attrs ));
	}
	public static function ajax_ok( $html, $attrs = array() ){
		self::ajax_response( wp_parse_args( array( 'status'=>'ok','html'=>$html ), $attrs ));
	}
	public static function ajax_response( $a ){
		#@error_reporting(0);
		header( 'Content-type: application/json' );
		echo json_encode($a);
		die('');
	}
	public static function p($a){
		echo "\r\n";
		echo '<pre style="background-color:#fff;">';
		echo "\r\n";
		print_r( $a );
		echo "\r\n";
		echo '</pre>';
		echo "\r\n";
	}
	public static function d($a){
		self::p( $a );
		die();
	}
	public static function get_last_field_position( $fields ){
		if( count($fields) > 0 ){
			$pos = 0;
			foreach($fields as $f){
				if( ! empty($f['position']) && $f['position'] > $pos ){
					$pos = $f['position'];
				}
			}
		} else {
			$pos = 0;
		}

		return $pos;
	}

	public static function post_type_options()
	{
		global $wp_post_types;

		$return = array();
		foreach( $wp_post_types as $post_type => $post_type_object ){
			// exclude the list post type
			if( !in_array($post_type, array(w4pl()->plugin_slug(), 'revision', 'nav_menu_item') ) ){
				$return[$post_type] = $post_type_object->labels->name;
			}
		}

		return $return;
	}

	public static function taxonomies_options()
	{
		global $wp_taxonomies;
		$return = array();
		foreach( $wp_taxonomies as $t => $attr){
			#if( $attr->public ){
				$return[$t] = $attr->label;
			#}
		}
		return $return;
	}

	public static function filter_multi_row_submit( $rows )
	{
		if( is_object($rows) ){
			$rows = get_object_vars( $rows );
		}

		$items = array();
		if( ! empty($rows) && is_array($rows) )
		{
			$keys = array_keys($rows);
			// raw formatted data
			foreach( array_keys($rows[ $keys[0] ]) as $i )
			{
				$row = array();
				foreach( $keys as $key )
				{ $row[$key] = isset($rows[$key][$i]) ? $rows[$key][$i] : ''; }

				$items[] = $row;
			}
		}

		if( empty($items) )
		{ $items = array(); }

		return $items;
	}

	public static function post_mime_type_options($post_types = '')
	{
		global $wpdb;
		if( empty($post_types) ){
			$post_types = array('post');
		}
		elseif( ! is_array($post_types) ){
			$post_types = array($post_types);
		}

		$mime_types = $wpdb->get_col(
			"SELECT DISTINCT post_mime_type FROM $wpdb->posts WHERE post_status != 'trash' AND post_type IN ('" . implode("','", $post_types) ."') AND post_mime_type <> ''"
		);

		if( !empty($mime_types) )
		{
			$return = array('' => 'Any');
			foreach( $mime_types as $mime_type ){
				if( !empty($mime_type) )
					$return[$mime_type] = $mime_type;
			}
			return $return;
		}
		return array();
	}

	public static function post_orderby_options( $post_types = array() )
	{
		$return = array(
			'ID'				=> __( 'ID', 					'w4pl'),
			'title'				=> __( 'Title', 				'w4pl'),
			'name'				=> __( 'Name', 					'w4pl'),
			'date'				=> __( 'Publish Date', 			'w4pl'),
			'modified'			=> __( 'Modified Date', 		'w4pl'),
			'menu_order'		=> __( 'Menu Order', 			'w4pl'),
			'meta_value'		=> __( 'Meta value', 			'w4pl'),
			'meta_value_num'	=> __( 'Meta numeric value', 	'w4pl'),
			'comment_count'		=> __( 'Comment Count', 		'w4pl'),
			'rand'				=> __( 'Random', 				'w4pl'),
			'none'				=> __( 'None', 					'w4pl'),
			'post__in'			=> __( 'Include posts', 	'w4pl'),
		);

		return $return;
	}

	public static function post_groupby_options( $post_types = array() )
	{
		$return = array(
			'' 				=> __('None', 			'w4pl'),
			'year' 			=> __('Year', 			'w4pl'),
			'month' 		=> __('Month', 			'w4pl'),
			'yearmonth' 	=> __('Year Months', 	'w4pl'),
			'author' 		=> __('Author',			'w4pl'),
			'parent' 		=> __('Parent', 		'w4pl'),
			'meta_value'	=> __('Custom field', 	'w4pl')
		);

		if( ! is_array($post_types) ){
			$post_types = array($post_types);
		}
		if( ! empty($post_types) && is_array($post_types) ){
			foreach( $post_types as $post_type ){
				foreach( get_object_taxonomies($post_type, 'all') as $taxonomy => $taxonomy_object ){
					if( $taxonomy == 'post_format' || !$taxonomy_object->public )
						continue;
					$return['tax_'. $taxonomy] = $taxonomy_object->labels->name;
				}
			}
		}

		return $return;
	}


	/* Retrive latest updates about Post List plugin */
	public static function plugin_news( $echo = true, $refresh = false )
	{
		$transient = 'w4pl_plugin_news';
		$transient_old = $transient . '_old';
		$expiration = 7200;

		$output = get_transient($transient);

		if ($refresh || !$output || empty($output)) {
			$request = wp_remote_request('http://w4dev.com/w4pl.txt');
			$content = wp_remote_retrieve_body($request);

			if( is_wp_error($content)) {
				$output = get_option( $transient_old );
			} else {
				$output = $content;
				// Save last new forever if a newer is not available..
				update_option( $transient_old, $output );
			}

			set_transient( $transient, $output, $expiration );
		}

		$output = preg_replace( '/[\n]/', '<br />', $output );

		if (! $echo) {
			return $output;
		} else {
			echo $output;
		}
	}
}


?>
