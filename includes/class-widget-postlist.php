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
 * Kept for classic (widget-area) themes. Block themes place a list with the
 * W4 Post List block instead, which is what the description points to.
 *
 * @class W4PL_Widget_Postlist
 */
class W4PL_Widget_Postlist extends WP_Widget {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct(
			'w4_post_list',
			__( 'W4 Post List', 'w4-post-list' ),
			array(
				'classname'                   => 'w4pl_widget',
				'description'                 => __( 'Show a list made with W4 Post List. In the block editor, use the W4 Post List block instead.', 'w4-post-list' ),
				'customize_selective_refresh' => true,
			),
			array(
				'width'  => 200,
				'height' => 400,
			)
		);
	}

	/**
	 * Front-end output.
	 *
	 * @param array $args     Widget area arguments (before/after markup from the theme).
	 * @param array $instance Saved widget settings.
	 */
	public function widget( $args, $instance ) {
		$list_id = 0;
		if ( isset( $instance['id'] ) ) {
			$list_id = (int) $instance['id'];
		}

		if ( $list_id < 1 ) {
			return;
		}

		$args = wp_parse_args(
			$args,
			array(
				'before_widget' => '',
				'after_widget'  => '',
				'before_title'  => '',
				'after_title'   => '',
			)
		);

		$title = '';
		if ( isset( $instance['title'] ) ) {
			$title = $instance['title'];
		}
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		// The before/after strings are the theme's own sidebar markup, printed
		// as every core widget prints them.
		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		if ( '' !== $title ) {
			echo $args['before_title'] . wp_kses_post( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		// The list is rendered by the plugin's own pipeline; the shortcode
		// output is the product, already assembled from escaped tags.
		echo do_shortcode( '[postlist id="' . $list_id . '"]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Sanitize the settings before they are saved.
	 *
	 * @param array $new_instance Submitted settings.
	 * @param array $old_instance Previously saved settings.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance          = $old_instance;
		$instance['title'] = '';
		$instance['id']    = 0;

		if ( isset( $new_instance['title'] ) ) {
			$instance['title'] = sanitize_text_field( $new_instance['title'] );
		}
		if ( isset( $new_instance['id'] ) ) {
			$instance['id'] = (int) $new_instance['id'];
		}

		return $instance;
	}

	/**
	 * Settings form.
	 *
	 * @param array $instance Saved widget settings.
	 */
	public function form( $instance ) {
		$title = '';
		$id    = 0;

		if ( isset( $instance['title'] ) ) {
			$title = $instance['title'];
		}
		if ( isset( $instance['id'] ) ) {
			$id = (int) $instance['id'];
		}

		$lists = get_posts(
			array(
				'post_status'    => array( 'publish', 'pending', 'private' ),
				'post_type'      => W4PL_Config::LIST_POST_TYPE,
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				<?php esc_html_e( 'Title:', 'w4-post-list' ); ?>
			</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text"
				value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'id' ) ); ?>">
				<?php esc_html_e( 'List:', 'w4-post-list' ); ?>
			</label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'id' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'id' ) ); ?>">
				<option value="0"<?php selected( $id, 0 ); ?>><?php esc_html_e( '— Select a list —', 'w4-post-list' ); ?></option>
				<?php foreach ( $lists as $list ) : ?>
					<option value="<?php echo esc_attr( $list->ID ); ?>"<?php selected( $id, $list->ID ); ?>>
						<?php
						$label = $list->post_title;
						if ( '' === $label ) {
							/* translators: %d: list ID */
							$label = sprintf( __( 'List #%d', 'w4-post-list' ), $list->ID );
						}
						echo esc_html( $label );
						?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<a class="button" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . W4PL_Config::LIST_POST_TYPE ) ); ?>">
				<?php esc_html_e( 'Create a new list', 'w4-post-list' ); ?>
			</a>
		</p>
		<?php
	}
}
