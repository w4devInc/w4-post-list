<?php
/**
 * Widget Registration
 * @package WordPress
 * @subpackage W4 Post List
 * @author Shazzad Hossain Khan
 * @url http://w4dev.com/plugins/w4-post-list
 * @since 1.0
**/


class W4PL_Widget extends WP_Widget
{
	function __construct()
	{
		$widget_ops = array(
			'classname' 	=> 'w4_post_list_widget',
			'description' 	=> __( 'Display lists created with W4 Post List', 'w4pl' )
		);
		$control_ops = array( 'width' => 200, 'height' => 400);
		parent::__construct( 'w4_post_list', 'W4 Post List', $widget_ops, $control_ops );
	}

	function widget( $args, $instance)
	{
		extract($args);
		$title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);

		$options = get_post_meta($instance['id'], '_w4pl', true);
		$options['id'] = $instance['id'];
		$options = apply_filters( 'w4pl/pre_get_options', $options );

		echo $before_widget;
		if ($title) {
			echo $before_title . $title . $after_title;
		}

		try {
			$list = W4PL_List_Factory::get_list($options);
			echo $list->get_html();
		} catch(Exception $e){
			// not showing error
		}

		echo $after_widget;
	}
	function update($new_instance, $old_instance)
	{
		$instance 					= $old_instance;
		$instance['title'] 			= strip_tags($new_instance['title']);
		$instance['id']				= (int) $new_instance['id'];
		return $instance;
	}
	function form($instance)
	{
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		$id = isset($instance['id']) ? (int)($instance['id']) : 0;

		?>
		<p>
			<strong><?php _e('Title:', 'w4pl'); ?></strong>
            <br /><input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" 
			value="<?php echo $title; ?>" />
		</p>
		<p>
			<strong><?php _e( 'Select list:', 'w4pl'); ?></strong>
            <br /><select id="<?php echo $this->get_field_id('id'); ?>" name="<?php echo $this->get_field_name('id'); ?>"><?php
				$lists = get_posts( 'post_status=publish&post_type='. w4pl()->plugin_slug() . '&posts_per_page=-1' );
				$id = (int) $id;
				foreach( $lists as $list ){
					$sel = ( $id == $list->ID ) ? 'selected="selected"' : '';
					$title = empty($list->post_title) ? 'List#' . $list->ID : $list->post_title;
					echo "<option value=\"$list->ID\" $sel >$title</option>\n";
				}
			?></select>
			or <a class="button" href="<?php echo esc_url(admin_url('post-new.php?post_type='. w4pl()->plugin_slug())); ?>">create new</a>
		</p>
		<?php
	}
}

?>