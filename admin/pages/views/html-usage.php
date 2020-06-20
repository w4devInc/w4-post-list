<?php
/**
 * Usage section template
 *
 * @package W4_Post_List
 */

?>
<div class="w4pl-usage">
	<h2><?php _e( 'Diplaying list with shortcode', 'w4-post-list' ); ?></h2>
	<p>
		<?php
		printf(
			/* translators: %s: postlist wrapper in code html tag */
			esc_html__( 'Shortcode %s van be used to display a list by list id, slug or title on post/page content area', 'w4-post-list' ),
			'<code>postlist</code>'
		);
		?>
		<pre>
[postlist id="LIST ID"]
[postlist slug="LIST SLUG"]
[postlist title="LIST TITLE"]</pre>
	</p>

	<h2><?php _e( 'Gutenberg / Block Editor', 'w4-post-list' ); ?>:</h2>
	<p><?php echo __( 'W4 Post list has a registered block that you can use to display a list', 'w4-post-list' ); ?></p>

	<h2><?php _e( 'Widget', 'w4-post-list' ); ?>:</h2>
	<p><?php echo __( 'W4 Post List has a dedicated widget that you can use on widget area from widgets manager page.', 'w4-post-list' ); ?></p>

	<h2><?php _e( 'Displaying list with PHP function', 'w4-post-list' ); ?>:</h2>
	<p>
		<?php
			printf(
				/* translators: %s: php function name */
				esc_html__( 'Display list using %s function', 'w4-post-list' ),
				'<code>do_shortcode</code>'
			);
			?>
	</p>
	<pre>&lt;?php
echo do_shortcode( '[postlist id="1"]' );
?&gt;</pre>
</div><!--postbox-->
