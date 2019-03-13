<?php
/**
 * @package W4 Post List
 * @author Shazzad Hossain Khan
 * @url http://w4dev.com/plugins/w4-post-list
**/


class W4PL_Helper_Users
{
	function __construct()
	{
		/* Register User Shortcodes */
		add_filter( 'w4pl/get_shortcodes', 			array($this, 'get_shortcodes'), 21 );

		/* Filer Option */
		add_filter( 'w4pl/pre_get_options', 		array($this, 'pre_get_options') );

		/* Option Page Fields */
		add_filter( 'w4pl/list_edit_form_fields', 		array($this, 'list_edit_form_fields'), 10, 2 );

		/* Parse List Query Args */
		add_filter( 'w4pl/parse_query_args', 		array($this, 'parse_query_args'), 15 );
	}



	/* Register User Shortcodes */

	public static function get_shortcodes( $shortcodes )
	{
		$_shortcodes = array(
			'user_id' => array(
				'group' 	=> 'User', 
				'callback' 	=> array('W4PL_Helper_Users', 'user_id'),
				'desc' 		=> '<strong>Output</strong>: user id'
			),
			'user_name' => array(
				'group' 	=> 'User', 
				'callback' 	=> array('W4PL_Helper_Users', 'user_name'),
				'desc' 		=> '<strong>Output</strong>: user name'
			),
			'user_email' => array(
				'group' 	=> 'User', 
				'callback' 	=> array('W4PL_Helper_Users', 'user_email'),
				'desc' 		=> '<strong>Output</strong>: user email'
			),
			'user_link' => array(
				'group' 	=> 'User', 
				'func' 		=> 'user_link', 
				'callback' 	=> array('W4PL_Helper_Users', 'user_link'),
				'desc' 		=> '<strong>Output</strong>: user post page link'
			),
			'user_count' => array(
				'group' 	=> 'User', 
				'callback' 	=> array('W4PL_Helper_Users', 'user_count'),
				'desc' 		=> '<strong>Output</strong>: user posts count'
			),
			'user_bio' => array(
				'group' 	=> 'User', 
				'callback' 	=> array('W4PL_Helper_Users', 'user_bio'),
				'desc' 		=> '<strong>Output</strong>: user description / biography'
			),
			'user_meta' => array(
				'group' 	=> 'User', 
				'code' 		=> '[user_meta key="" multiple="0"]', 
				'callback' 	=> array('W4PL_Helper_Users', 'user_meta'),
				'desc' 		=> '<strong>Output</strong>: user meta value. if return value is an array, it will be migrated to string by using explode function
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>key</strong> = (text|number), meta key name
				<br /><strong>multiple</strong> = (0|1), display meta value at multiple occurence
				<br /><strong>sep</strong> = (text), separate array meta value into string'
			),
			'user_avatar' => array(
				'group' 	=> 'User', 
				'callback' 	=> array('W4PL_Helper_Users', 'user_avatar'),
				'desc' 		=> '<strong>Output</strong>: user avatar
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>size</strong> = (number), avatar image size, ex: 32, 64, 128'
			)
		);

		return array_merge( $shortcodes, $_shortcodes );
	}


	/* User Shortcode Callbacks */

	public static function user_id( $attr, $cont, $list )
	{
		return isset($list->current_user) ? $list->current_user->ID : 0;
	}
	public static function user_name( $attr, $cont, $list )
	{
		return isset($list->current_user) ? $list->current_user->display_name : '';
	}
	public static function user_email( $attr, $cont, $list )
	{
		return isset($list->current_user) ? $list->current_user->user_email : '';
	}
	public static function user_link( $attr, $cont, $list )
	{
		return isset($list->current_user) ? get_author_posts_url($list->current_user->ID) : '';
	}
	public static function user_count( $attr, $cont, $list )
	{
		return isset($list->current_user) ? count_user_posts($list->current_user->ID) : 0;
	}
	public static function user_bio( $attr, $cont, $list )
	{
		return isset($list->current_user) ? get_the_author_meta( 'description', $list->current_user->ID) : '';
	}
	public static function user_meta( $attr, $cont, $list )
	{
		if( isset($attr) && !is_array($attr) && is_string($attr) ){
			$meta_key = trim($attr);
			$attr = array();
		}
		if( isset($attr['key']) ){
			$meta_key = $attr['key'];
		}
		elseif( isset($attr['meta_key']) ){
			$meta_key = $attr['meta_key'];
		}
		if( ! $meta_key )
			return;

		$single = ! ( isset($attr) && is_array($attr) && array_key_exists('multiple', $attr) ?  (bool) $attr['multiple'] : true );

		$sep = ', ';
		if( isset($attr['sep']) ){
			$sep = $attr['sep'];
		}

		$return = get_user_meta( $list->current_user->ID, $meta_key, $single );

		if( is_array($return) ){
			$new = array();
			foreach( $return as $r => $d ){
				if( !is_array($d) ){
					$new[] = $d;
				}
			}
			if( $new )
				$return = implode( $sep, $new );
			else
				$return = '';
		}

		return $return;
	}

	public static function user_avatar( $attr, $cont, $list )
	{
		$size = isset($attr['size']) ? $attr['size'] : '96';
		return get_avatar( $list->current_user->ID, $size );
	}


	/* Option Page Fields */

	public function list_edit_form_fields( $fields, $options )
	{
		$list_type = $options['list_type'];
		if( ! in_array($list_type, array('users', 'users.posts') ) )
			return $fields;

		$fields['before_field_group_users_query'] = array(
			'position'		=> '5',
			'html' 			=> '<div id="w4pl_field_group_users_query" class="w4pl_field_group">
				<div class="w4pl_group_title">Users</div>
				<div class="w4pl_group_fields"><div class="w4pl_group_fields_wrap">'
		);
		$fields['users__in'] = array(
			'position'		=> '11',
			'option_name' 	=> 'users__in',
			'name' 			=> 'w4pl[users__in]',
			'label' 		=> 'Include users',
			'type' 			=> 'text',
			'input_class' 	=> 'widefat',
			'desc' 			=> 'comma separated term id'
		);
		$fields['users__not_in'] = array(
			'position'		=> '12',
			'option_name' 	=> 'users__not_in',
			'name' 			=> 'w4pl[users__not_in]',
			'label' 		=> 'Exclude users',
			'type' 			=> 'text',
			'input_class' 	=> 'widefat',
			'desc' 			=> 'comma separated term id'
		);

		$fields['users_display_name__like'] = array(
			'position'		=> '15',
			'option_name' 	=> 'users_display_name__like',
			'name' 			=> 'w4pl[users_display_name__like]',
			'label' 		=> 'Display Name search',
			'type' 			=> 'text',
			'desc' 			=> 'enter text that will be used to search users by name'
		);
		$fields['users_user_email__like'] = array(
			'position'		=> '16',
			'option_name' 	=> 'users_user_email__like',
			'name' 			=> 'w4pl[users_user_email__like]',
			'label' 		=> 'Email search',
			'type' 			=> 'text',
			'desc' 			=> 'enter text that will be used to search users by email'
		);

		$fields['users_orderby'] = array(
			'position'		=> '21',
			'option_name' 	=> 'users_orderby',
			'name' 			=> 'w4pl[users_orderby]',
			'label' 		=> 'Orderby',
			'type' 			=> 'select',
			'option' 		=> self::users_orderby_options()
		);
		$fields['users_order'] = array(
			'position'		=> '22',
			'option_name' 	=> 'users_order',
			'name' 			=> 'w4pl[users_order]',
			'label' 		=> 'Order',
			'type' 			=> 'radio',
			'option' 		=> array('ASC' => 'ASC', 'DESC' => 'DESC')
		);
		$fields['users_offset'] = array(
			'position'		=> '31',
			'option_name' 	=> 'users_offset',
			'name' 			=> 'w4pl[users_offset]',
			'label' 		=> 'Offset',
			'type' 			=> 'text',
			'desc' 			=> 'skip given number of users from beginning'
		);
		$fields['users_limit'] = array(
			'position'		=> '32',
			'option_name' 	=> 'users_limit',
			'name' 			=> 'w4pl[users_limit]',
			'label' 		=> 'Items per page',
			'type' 			=> 'text',
			'desc' 			=> 'number of items to show per page'
		);
		$fields['users_max'] = array(
			'position'		=> '33',
			'option_name' 	=> 'users_max',
			'name' 			=> 'w4pl[users_max]',
			'label' 		=> 'Maximum items',
			'type' 			=> 'text',
			'desc' 			=> 'maximum results to display in total, default all found'
		);

		$fields['after_field_group_users_query'] = array(
			'position'		=> '50',
			'html' 			=> '</div><!--.w4pl_group_fields_wrap--></div><!--.w4pl_group_fields--></div><!--#w4pl_field_group_users_query-->'
		);

		return $fields;
	}


	/* Filer Option */

	public function pre_get_options($options)
	{
		if( !isset($options) || !is_array($options) )
			$options = array();

		$options = wp_parse_args( $options, array(
			'users__in' 				=> '', 
			'users__not_in' 			=> '', 
			'users_display_name__like'	=> '',
			'users_user_email__like'	=> '',
			'users_offset'				=> '',
			'users_limit'				=> '',
			'users_max'					=> '',
			'users_orderby'				=> 'ID',
			'users_order'				=> 'DESC'
		));

		return $options;
	}


	/* Parse List Query Args */

	public function parse_query_args( $list )
	{
		// users
		if( in_array($list->options['list_type'], array('users', 'users.posts') ) )
		{
			// push default options to query var
			foreach( array(
				'users_display_name__like'	=> 'display_name__like',
				'users_user_email__like'	=> 'user_email__like',
				'users_offset'				=> 'offset',
				'users_limit'				=> 'limit',
				'users_orderby'				=> 'orderby',
				'users_order'				=> 'order'
			) as $option => $name )
			{
				if( !empty($list->options[$option]) )
					$list->users_args[$name] = $list->options[$option];
			}
			#echo '<pre>'; print_r($list->users_args); echo '</pre>';

			// comma separated ids
			foreach( array(
				'users__in' 		=> 'ID__in',
				'users__not_in' 	=> 'ID__not_in'
			) as $option => $name )
			{
				if( !empty($list->options[$option]) )
				{
					$opt = wp_parse_id_list( $list->options[$option] );
					if( !empty($opt) )
						$list->users_args[$name] = $opt;
				}
			}

			$paged = isset($_REQUEST['page'. $list->id]) ? $_REQUEST['page'. $list->id] : 1;

			if( !empty($list->options['users_limit']) ){
				$list->users_args['offset'] = (int) $list->options['users_offset'] + ($paged - 1) * $list->options['users_limit'];
			}
			if( isset($list->options['users_max']) && !empty($list->options['users_max']) && $list->options['users_max'] < ($list->options['users_limit'] * $paged) )
			{
				$list->users_args['limit'] = $list->options['users_max'] - ( $list->options['users_limit'] * ($paged-1) );
			}
			// users query var ends
		}
	}


	public static function users_orderby_options()
	{
		$return = array(
			'ID'				=> __( 'ID', 					'w4pl'),
			'user_login'		=> __( 'Username', 				'w4pl'),
			'user_nicename'		=> __( 'Nicename', 				'w4pl'),
			'user_email'		=> __( 'Email', 				'w4pl'),
			'user_registered'	=> __( 'Registration time', 	'w4pl'),
			'display_name'		=> __( 'Display name', 	'w4pl')
		);

		return $return;
	}
}

	new W4PL_Helper_Users;
?>