<?php
/**
 * Postlist widget class.
 *
 * @package W4_Post_List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Postlist Widget Class
 * 
 * @class W4PL_Widget_Postlist
 */
class W4PL_Widget_Postlist extends WP_Widget {

	function __construct() {
		parent::__construct(
			'w4_post_list',
			__( 'W4 Post List', 'w4-post-list' ),
			array(
				'classname' 	=> 'w4pl_widget',
				'description' 	=> __( 'Display lists created with W4 Post List', 'w4-post-list' )
			 ),
			array(
				'width' => 200,
				'height' => 400
			 )
		 );
	}

	function widget( $args, $instance ) {
		extract( $args );

		if ( empty( $instance['id'] ) ) {
			return '';
		}

		$title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );

		echo $before_widget;

		if ( $title ) {
			echo $before_title . $title . $after_title;
		}

		echo do_shortcode( '[postlist id="'. $instance['id'] .'"]' );

		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance 			= $old_instance;
		$instance['title'] 	= strip_tags( $new_instance['title'] );
		$instance['id']		= ( int ) $new_instance['id'];

		return $instance;
	}

	function form( $instance ) {
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$id = isset( $instance['id'] ) ? (int) ( $instance['id'] ) : 0;

		$lists = get_posts(array(
			'post_status'    => array( 'publish', 'pending', 'private' ),
			'post_type'      => W4PL_Config::LIST_POST_TYPE,
			'posts_per_page' => -1
		));

		?>
		<p>
			<strong><?php _e( 'Title', 'w4-post-list' ); ?>:</strong>
            <br /><input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
			<strong><?php _e( 'Select a list', 'w4-post-list' ); ?>:</strong>
            <br />
			<select id="<?php echo $this->get_field_id( 'id' ); ?>" name="<?php echo $this->get_field_name( 'id' ); ?>">
			<?php

				$id = (int) $id;

				foreach ( $lists as $list ) {
					printf( 
						'<option value="%d"%s>%s</option>', 
						$list->ID, 
						$id === $list->ID ? ' selected="selected"' : '', 
						empty( $list->post_title ) ? 'List#' . $list->ID : $list->post_title
					);
				}
			?>
			</select>
			<br />
			<?php _e( 'OR', 'w4-post-list' ); ?>
			<br />
			<a class="button" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . W4PL_Config::LIST_POST_TYPE ) ); ?>"><?php _e( 'Create a new one', 'w4-post-list' ); ?></a>
		</p>
		<?php
	}
}
