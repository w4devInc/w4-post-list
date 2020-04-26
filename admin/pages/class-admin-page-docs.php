<?php
/**
 * Admin documentation page class
 *
 * @class W4PL_Admin_Page_Docs
 * @package W4 Post List
 */

defined( 'ABSPATH' ) || exit;

class W4PL_Admin_Page_Docs {

	function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	public function admin_menu() {
		$admin_page = add_submenu_page(
			'edit.php?post_type=w4pl',
			__( 'Documentation', 'w4pl' ),
			__( 'Documentation', 'w4pl' ),
			'delete_users',
			w4pl()->plugin_slug() . '-docs',
			array( $this, 'admin_page')
		);

		add_action( "admin_print_styles-{$admin_page}", array( $this, 'print_scripts' ) );
	}


	public function admin_page() {
		?>
		<div class="wrap w4pl-documentation-wrap">
			<h1>
				<?php
				printf(
					/* translators: %s: current plugin version */
					__( 'Documentation: Version - <strong>%s</strong>', 'w4pl' ),
					w4pl()->plugin_version()
				); ?>
			</h1>
			<!--<p class="description">As like id (<code>[postlist id=1]</code>), a list can also be called using slug or title.<br /><strong>Ex</strong>: <code>[postlist slug='my-list']</code>, <code>[postlist Title='My List']</code></p>-->
			<div class="has-right-sidebar">

				<div class="inner-sidebar" id="side-info-column">
					<?php include_once( w4pl()->plugin_dir() . 'admin/views/documentation-plugin-links.php' ); ?>
					<?php include_once( w4pl()->plugin_dir() . 'admin/views/documentation-plugin-updates.php' ); ?>
				</div><!--#side-info-column-->

				<div id="post-body">
					<div id="post-body-content">
						<?php include_once( w4pl()->plugin_dir() . 'admin/views/documentation-template.php' ); ?>
						<?php include_once( w4pl()->plugin_dir() . 'admin/views/documentation-template-tags.php' ); ?>
						<?php include_once( w4pl()->plugin_dir() . 'admin/views/documentation-usage.php' ); ?>
					</div><!--#post-body-content-->
				</div><!--#post-body-->

			</div><!--#poststuff-->
		</div><!--wrap-->
		<?php
	}

	public function print_scripts() {
		wp_enqueue_style( 'w4pl-admin-documentation' );
	}
}
