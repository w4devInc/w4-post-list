<?php
/**
 * Documentation - Admin Page
 * @package W4 Post List
 * @author Shazzad Hossain Khan
 * @url http://w4dev.com/plugins/w4-post-list
**/

class W4PL_Admin_Page_Docs
{
	function __construct()
	{
		add_action( 'admin_menu'											, array($this, 'admin_menu') );
		// add lists link to plugin links, so one can navigate quickly
		add_filter( 'plugin_action_links_' . w4pl()->plugin_basename()		, array($this, 'plugin_action_links'), 30 );
	}

	public function admin_menu() {
		$admin_page = add_submenu_page( 
			'edit.php?post_type=w4pl',
			__('Documentation', 'w4pl'),
			__('Documentation', 'w4pl'),
			'delete_users',
			w4pl()->plugin_slug() . '-docs',
			array( $this, 'admin_page')
		);

		add_action( "admin_print_styles-{$admin_page}"	, array($this, 'print_scripts') );
		add_action( "load-{$admin_page}"				, array($this, 'load_page') );
	}

	public function admin_page() { 
		?><div class="wrap w4pl-documentation-wrap">
		<h1>Documentation - <strong><?php echo w4pl()->plugin_version(); ?></strong></h1>
		<!--<div class="about-text">As like id (<code>[postlist id=1]</code>), a list can also be called using slug or title.<br /><strong>Ex</strong>: <code>[postlist slug='my-list']</code>, <code>[postlist Title='My List']</code></div>-->
		<div class="has-right-sidebar">
			<div id="_poststuff">
	
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
	
	
			</div><!--has-right-sidebar-->
			</div><!--#poststuff-->
		</div><!--wrap-->
	<?php
	}

	public function load_page(){
	}

	public function print_scripts() {
		wp_enqueue_style( 'w4pl-admin-documentation', w4pl()->plugin_url() . 'admin/assets/documentation.css' );
	}

	public function plugin_action_links( $links ){
		$readme_link['doc'] = '<a href="'. 'edit.php?post_type=w4pl&page=w4pl-docs">' . __('Docs', 'w4pl' ). '</a>';
		return array_merge( $links, $readme_link );
	}
}

	new W4PL_Admin_Page_Docs;
?>