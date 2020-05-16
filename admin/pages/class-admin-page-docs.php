<?php
/**
 * Admin documentation page class
 *
 * @class W4PL_Admin_Page_Docs
 * @package W4_Post_List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin documentation page
 */
class W4PL_Admin_Page_Docs {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	/**
	 * Register admin menu
	 */
	public function admin_menu() {
		$admin_page = add_submenu_page(
			'edit.php?post_type=w4pl',
			__( 'Documentation', 'w4-post-list' ),
			__( 'Documentation', 'w4-post-list' ),
			'delete_users',
			'w4pl-docs',
			array( $this, 'admin_page' )
		);

		add_action( "admin_print_styles-{$admin_page}", array( $this, 'print_scripts' ) );
	}

	/**
	 * Page template
	 */
	public function admin_page() {
		$current_tab = isset( $_REQUEST['tab'] ) ? $_REQUEST['tab'] : 'template-examples';
		$tabs = array(
			'template-examples' => __( 'Template' ),
			'template-tags' => __( 'Template Tags' ),
			'usage' => __( 'Usage' ),
		);
		?>
		<div class="wrap w4pl-documentation-wrap">
			<h1><?php _e( 'Documentation', 'w4-post-list' ); ?></h1>
			<p>
				<?php
				printf(
					/* translators: %s: current plugin version */
					__( 'Plugin Version: %s', 'w4-post-list' ),
					W4PL_VERSION
				);
				?>
			</p>
			<!--<p class="description">As like id (<code>[postlist id=1]</code>), a list can also be called using slug or title.<br /><strong>Ex</strong>: <code>[postlist slug='my-list']</code>, <code>[postlist Title='My List']</code></p>-->
			<div class="has-right-sidebar w4pl-documentation-main">

				<div class="w4pl-documentation-content">
					<nav class="nav-tab-wrapper wp-clearfix" aria-label="<?php _e( 'Secondary menu', 'w4-post-list' ); ?>">
						<?php
						foreach ( $tabs as $tab => $label ) {
							$active_class = '';
							if ( $tab === $current_tab ) {
								$active_class = ' nav-tab-active';
							}
							printf(
								'<a href="%1$s" class="nav-tab%2$s" aria-current="page">%3$s</a>',
								add_query_arg( 'tab', $tab ),
								$active_class,
								$label
							);
						}
						?>
					</nav>
					<?php
					switch ( $current_tab ) {
						case 'template-tags':
							include_once dirname( __FILE__ ) . '/views/html-template-tags.php';
							break;
						case 'usage':
							include_once dirname( __FILE__ ) . '/views/html-usage.php';
							break;
						default:
							include_once dirname( __FILE__ ) . '/views/html-template-examples.php';
							break;
					}
					?>
				</div>

				<div class="w4pl-documentation-sidebar">
					<?php include_once dirname( __FILE__ ) . '/views/html-plugin-links.php'; ?>
					<?php # include_once dirname( __FILE__ ) . '/views/html-plugin-updates.php'; ?>
				</div>

			</div><!--#poststuff-->
		</div><!--wrap-->
		<?php
	}

	/**
	 * Enqueue current page scripts
	 */
	public function print_scripts() {
		wp_enqueue_style( 'w4pl-admin-documentation' );
	}
}
