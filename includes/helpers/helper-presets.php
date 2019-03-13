<?php
/**
 * @package W4 Post List
 * @author Shazzad Hossain Khan
 * @url http://w4dev.com/plugins/w4-post-list
**/


class W4PL_Helper_Presets
{
	function __construct()
	{
		/* Filer Option */
		add_filter( 'w4pl/pre_get_options', 		array($this, 'pre_get_options') );

		/* Option Page Fields */
		add_filter( 'w4pl/list_edit_form_fields', 		array($this, 'list_edit_form_fields'), 13, 2 );

		/* Parse List Query Args */
		add_filter( 'w4pl/parse_query_args', 		array($this, 'parse_query_args'), 10 );
	}


	/* Filer Option */

	public function pre_get_options($options)
	{
		if( !isset($options) || !is_array($options) )
			$options = array();

		// quick kill
		if( ! isset($options['preset']) || empty($options['preset']) )
		{ return $options; }


		if( isset($options['preset']) && 'simple_list' == $options['preset'] )
		{
			if( 'posts' == $options['list_type'] )
			{
				$options['template'] = '<ul>[posts]
					<li class="post-item-[post_id]"><a href="[post_permalink]">[post_title]</a></li>
				[/posts]</ul>';
			}
			elseif( 'terms.posts' == $options['list_type'] )
			{
				$options['template'] = '<ul>[terms]
					<li class="term-item-[term_id]">
						<a href="[term_link]">[term_name]</a>
						<ul>[posts]
							<li class="post-item-[post_id]"><a href="[post_permalink]">[post_title]</a></li>
						[/posts]</ul>
					</li>
				[/terms]</ul>';
			}
			elseif( 'terms' == $options['list_type'] )
			{
				$options['template'] = '<ul>[terms]
					<li class="term-item-[term_id]"><a href="[term_link]">[term_name]</a></li>
				[/terms]</ul>';
			}
			elseif( 'users' == $options['list_type'] )
			{
				$options['template'] = '<ul>[users]
					<li class="user-item-[user_id]"><a href="[user_link]">[user_name]</a></li>
				[/users]</ul>';
			}
			elseif( 'users.posts' == $options['list_type'] )
			{
				$options['template'] = '<ul>[users]
					<li class="user-item-[user_id]">
						<a href="[user_link]">[user_name]</a>
						<ul>[posts]
							<li class="post-item-[post_id]"><a href="[post_permalink]">[post_title]</a></li>
						[/posts]</ul>
					</li>
				[/users]</ul>';
			}
		}

		if( isset($options['preset']) && 'post_with_thumbnail' == $options['preset'] )
		{
			if( 'posts' == $options['list_type'] )
			{
				$options['template'] = '<ul class="posts-list">[posts]
					<li class="post-item-[post_id]"><a href="[post_permalink]">[post_title]<br />[post_thumbnail size="thumbnail"]</a></li>
				[/posts]</ul>';

				$options['class'] = 'w4pl_preset_post_with_thumbnail';
				$options['css'] = '
				.w4pl_preset_post_with_thumbnail ul.posts-list li{list-style:none outside!important; margin:0 0 10px 0!important; padding:0!important;}
				.w4pl_preset_post_with_thumbnail ul.posts-list li a{text-decoration: none;}';
			}
			elseif( 'terms.posts' == $options['list_type'] )
			{
				$options['template'] = '<ul>[terms]
					<li class="term-item-[term_id]">
						<a href="[term_link]">[term_name]</a>
						<ul class="posts-list">[posts]
							<li class="post-item-[post_id]"><a href="[post_permalink]">[post_title]<br />[post_thumbnail size="thumbnail"]</a></li>
						[/posts]</ul>
					</li>
				[/terms]</ul>';

				$options['class'] = 'w4pl_preset_post_with_thumbnail';
				$options['css'] = '
				.w4pl_preset_post_with_thumbnail ul.posts-list li{list-style:none outside!important; margin:0 0 10px 0!important; padding:0!important;}
				.w4pl_preset_post_with_thumbnail ul.posts-list li a{text-decoration: none;}';
			}
			elseif( 'users.posts' == $options['list_type'] )
			{
				$options['template'] = '<ul>[users]
					<li class="user-item-[user_id]">
						<a href="[user_link]">[user_name]</a>
						<ul class="posts-list">[posts]
							<li class="post-item-[post_id]"><a href="[post_permalink]">[post_title]<br />[post_thumbnail size="thumbnail"]</a></li>
						[/posts]</ul>
					</li>
				[/users]</ul>';

				$options['class'] = 'w4pl_preset_post_with_thumbnail';
				$options['css'] = '
				.w4pl_preset_post_with_thumbnail ul.posts-list li{list-style:none outside!important; margin:0 0 10px 0!important; padding:0!important;}
				.w4pl_preset_post_with_thumbnail ul.posts-list li a{text-decoration: none;}';
			}
		}

		return $options;
	}


	/* Option Page Fields */
	public function list_edit_form_fields( $fields, $options )
	{
		$fields['preset'] = array(
			'position'		=> '3.1',
			'option_name' 	=> 'preset',
			'name' 			=> 'w4pl[preset]',
			'label' 		=> 'Preset',
			'type' 			=> 'select',
			'option' 		=> self::preset_options($options),
			'input_class'	=> 'w4pl_onchange_lfr',
			'desc'			=> 'preset is predefined templates'
		);

		if( isset($options['preset']) && !empty($options['preset']) )
		{
			unset(
				$fields['before_field_group_style'], 
				$fields['js'], 
				$fields['css'], 
				$fields['class'], 
				$fields['after_field_group_style'], 
				$fields['before_field_group_template'], 
				$fields['template1'], 
				$fields['after_field_group_template']
			);
		}

		return $fields;
	}


	/* Parse List Query Args */

	public function parse_query_args( $obj )
	{
		if( isset($obj->options['preset']) && 'post_with_thumbnail' == $obj->options['preset'] )
		{
			if( ! isset($obj->posts_args['meta_query']) || ! is_array($obj->posts_args['meta_query']) ){
				$obj->posts_args['meta_query'] = array();
			}
			$obj->posts_args['meta_query'][] = array(
				'key' 		=> '_thumbnail_id',
				'compare' 	=> 'EXISTS'
			);
		}

		return $obj;
	}


	/* Preset options */

	public function preset_options($options)
	{
		$presets = array(
			'' 				=> 'Custom',
			'simple_list' 	=> 'Simple list'
		);
		$list_type = $options['list_type'];
		if( in_array($list_type, array('posts', 'terms.posts', 'users.posts') ) ){
			$presets['post_with_thumbnail'] = 'Posts with Thumbnail';
		}

		return $presets;
	}
}

	new W4PL_Helper_Presets;
?>