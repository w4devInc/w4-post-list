<?php
/**
 * Documentation plugin links
 *
 * @package W4_Post_List
 */

?>
<div class="w4pl-plugin-links">
	<h2 style="margin-top: 0;"><?php esc_html_e( 'Reference', 'w4-post-list' ); ?></h2>
	<div class="inside">
		<ul>
			<?php $siteurl = site_url( '/' ); ?>
			<li><a href="
		<?php
		echo esc_url(
			add_query_arg(
				array(
					'utm_source'   => $siteurl,
					'utm_medium'   => 'w4%2Bplugin',
					'utm_campaign' => 'w4pl',
				),
				'http://w4dev.com/plugins/w4-post-list'
			)
		);
		?>
		" target="_blank"><?php esc_html_e( 'Visit Plugin Page', 'w4-post-list' ); ?></a></li>
		<li><a href="
		<?php
		echo esc_url(
			add_query_arg(
				array(
					'utm_source'   => $siteurl,
					'utm_medium'   => 'w4%2Bplugin',
					'utm_campaign' => 'w4pl',
				),
				'http://w4dev.com/wp/w4-post-list-examples/#examples'
			)
		);
		?>
		" target="_blank"><?php esc_html_e( 'Demos & Examples', 'w4-post-list' ); ?></a></li>
		<li><a href="http://wordpress.org/support/view/plugin-reviews/w4-post-list" target="_blank"><?php esc_html_e( 'Post a review', 'w4-post-list' ); ?></a></li>
		<li><a href="
		<?php
		echo esc_url(
			add_query_arg(
				array(
					'utm_source'   => $siteurl,
					'utm_medium'   => 'w4%2Bplugin',
					'utm_campaign' => 'w4pl',
				),
				'http://codecanyon.net/item/soccer-engine-wordpress-plugin/9070583'
			)
		);
		?>
		" target="_blank"><?php esc_html_e( 'Do you need a Soccer / Football Plugin ?', 'w4-post-list' ); ?></a></li>
		</ul>
	</div><!--inside-->
</div><!--postbox-->
