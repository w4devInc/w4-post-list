<?php
/**
 * Documentation plugin links
 *
 * @package W4_Post_List
 */

$doc_url    = add_query_arg(
	array(
		'utm_source'   => 'wp-admin',
		'utm_medium'   => 'plugin',
		'utm_campaign' => 'w4-post-list',
	),
	'https://w4dev.com/docs/w4-post-list'
);
$plugin_url = add_query_arg(
	array(
		'utm_source'   => 'wp-admin',
		'utm_medium'   => 'plugin',
		'utm_campaign' => 'w4-post-list',
	),
	'https://w4dev.com/plugins/w4-post-list'
);
$demo_url   = add_query_arg(
	array(
		'utm_source'   => 'wp-admin',
		'utm_medium'   => 'plugin',
		'utm_campaign' => 'w4-post-list',
	),
	'https://w4dev.com/wp/w4-post-list-examples/#examples'
);
$soccer_url = add_query_arg(
	array(
		'utm_source'   => 'wp-admin',
		'utm_medium'   => 'plugin',
		'utm_campaign' => 'w4-post-list',
	),
	'http://codecanyon.net/item/soccer-engine-wordpress-plugin/9070583'
);
?>
<div class="w4pl-plugin-links">
	<h2 style="margin-top: 0;">
		<?php esc_html_e( 'Reference', 'w4-post-list' ); ?>
	</h2>
	<div class="inside">
		<ul>
			<li><a href="<?php echo esc_url( $doc_url ); ?>" target="_blank">
					<?php esc_html_e( 'Online Documentation', 'w4-post-list' ); ?>
				</a></li>
			<li><a href="<?php echo esc_url( $plugin_url ); ?>" target="_blank">
					<?php esc_html_e( 'Visit Plugin Page', 'w4-post-list' ); ?>
				</a></li>
			<li><a href="<?php echo esc_url( $demo_url ); ?>" target="_blank">
					<?php esc_html_e( 'Demos & Examples', 'w4-post-list' ); ?>
				</a></li>
			<li><a href="https://wordpress.org/support/plugin/w4-post-list/reviews/#new-post" target="_blank">
					<?php esc_html_e( 'Post a review', 'w4-post-list' ); ?>
				</a></li>
			<!-- <li><a href="<?php echo esc_url( $soccer_url ); ?> " target="_blank"><?php esc_html_e( 'Do you need a Soccer / Football Plugin ?', 'w4-post-list' ); ?></a></li> -->
		</ul>
	</div><!--inside-->
</div><!--postbox-->