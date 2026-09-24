<?php
/**
 * Documentation plugin links
 *
 * @package W4_Post_List
 */

$doc_url          = add_query_arg(
	array(
		'utm_source'   => 'wp-admin',
		'utm_medium'   => 'plugin',
		'utm_campaign' => 'w4-post-list',
	),
	'https://w4dev.com/docs/w4-post-list'
);
$plugin_url       = add_query_arg(
	array(
		'utm_source'   => 'wp-admin',
		'utm_medium'   => 'plugin',
		'utm_campaign' => 'w4-post-list',
	),
	'https://w4dev.com/plugins/w4-post-list'
);
$demo_url         = add_query_arg(
	array(
		'utm_source'   => 'wp-admin',
		'utm_medium'   => 'plugin',
		'utm_campaign' => 'w4-post-list',
	),
	'https://w4dev.com/wp/w4-post-list-examples/#examples'
);
$adminkeep_active = false;
if ( defined( 'ADMINKEEP_VERSION' ) || is_plugin_active( 'adminkeep/adminkeep.php' ) ) {
	$adminkeep_active = true;
}
$adminkeep_url = 'https://wordpress.org/plugins/adminkeep/';
if ( current_user_can( 'install_plugins' ) ) {
	$adminkeep_url = admin_url( 'plugin-install.php?s=adminkeep&tab=search&type=term' );
}
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
		</ul>
	</div><!--inside-->
</div><!--postbox-->

<?php if ( ! $adminkeep_active ) : ?>
<div class="w4pl-plugin-links w4pl-also-free">
	<h2 style="margin-top: 0;">
		<?php esc_html_e( 'Your list, styled. Your theme, untouched.', 'w4-post-list' ); ?>
	</h2>
	<div class="inside">
		<p>
			<?php esc_html_e( 'Adminkeep gives WordPress a Custom CSS screen: write a rule for your list, save, done. No child theme, no theme editor, and it survives theme updates.', 'w4-post-list' ); ?>
		</p>
		<p>
			<a class="button" href="<?php echo esc_url( $adminkeep_url ); ?>">
				<?php esc_html_e( 'Get Adminkeep, free', 'w4-post-list' ); ?>
			</a>
		</p>
	</div><!--inside-->
</div><!--postbox-->
<?php endif; ?>
