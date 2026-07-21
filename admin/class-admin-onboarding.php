<?php
/**
 * First-run onboarding: sample list, welcome notice, empty state, help tabs.
 *
 * @class W4PL_Admin_Onboarding
 * @package W4_Post_List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Guides a new user from activation to a first working list.
 */
class W4PL_Admin_Onboarding {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'maybe_create_sample_list' ) );
		add_action( 'admin_init', array( $this, 'maybe_dismiss_welcome' ) );
		add_action( 'admin_notices', array( $this, 'welcome_notice' ) );
		add_action( 'admin_notices', array( $this, 'empty_state' ) );
		add_action( 'wp_ajax_w4pl_dismiss_welcome', array( $this, 'ajax_dismiss_welcome' ) );
		add_action( 'current_screen', array( $this, 'help_tabs' ) );
	}

	/**
	 * Create a draft example list on first install, so the Lists screen is
	 * never empty and there is always a working configuration to learn from.
	 */
	public function maybe_create_sample_list() {
		if ( ! get_option( 'w4pl_maybe_create_sample' ) ) {
			return;
		}

		$counts = wp_count_posts( 'w4pl' );
		$total  = array_sum( (array) $counts );

		if ( $total > 0 ) {
			delete_option( 'w4pl_maybe_create_sample' );
			return;
		}

		// Wait for a user who can author lists; keep the flag until then.
		if ( ! current_user_can( 'edit_pages' ) ) {
			return;
		}

		delete_option( 'w4pl_maybe_create_sample' );

		self::create_sample_list();
	}

	/**
	 * Insert the example list through the same option pipeline the editor uses.
	 *
	 * @return int The list post ID.
	 */
	public static function create_sample_list() {
		$templates = new W4PL_List_Templates();

		$options = array(
			'list_type'      => 'posts',
			'post_type'      => array( 'post' ),
			'post_status'    => array( 'publish' ),
			'posts_per_page' => 5,
			'orderby'        => 'post_date',
			'order'          => 'DESC',
			'template'       => $templates->sanitize_template( '', array( 'list_type' => 'posts' ) ),
		);

		$list_id = wp_insert_post(
			array(
				'post_type'   => 'w4pl',
				'post_status' => 'draft',
				'post_title'  => __( 'Example: Recent Posts', 'w4-post-list' ),
			)
		);

		if ( ! $list_id || is_wp_error( $list_id ) ) {
			return 0;
		}

		$options['id'] = $list_id;
		$options       = apply_filters( 'w4pl/pre_save_options', $options );

		update_post_meta( $list_id, '_w4pl', $options );

		return $list_id;
	}

	/**
	 * Dismissible "Get started" notice shown after activation.
	 */
	public function welcome_notice() {
		if ( ! get_option( 'w4pl_welcome_pending' ) || ! current_user_can( 'edit_pages' ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || ! in_array( $screen->id, array( 'dashboard', 'plugins', 'edit-w4pl', 'w4pl' ), true ) ) {
			return;
		}

		$sample = get_posts(
			array(
				'post_type'   => 'w4pl',
				'post_status' => 'any',
				'numberposts' => 1,
				'orderby'     => 'ID',
				'order'       => 'ASC',
			)
		);

		if ( ! empty( $sample ) ) {
			$primary_url  = get_edit_post_link( $sample[0]->ID, 'url' );
			$primary_text = __( 'Open the example list', 'w4-post-list' );
		} else {
			$primary_url  = admin_url( 'post-new.php?post_type=w4pl' );
			$primary_text = __( 'Create your first list', 'w4-post-list' );
		}

		$docs_url    = admin_url( 'edit.php?post_type=w4pl&page=w4pl-docs' );
		$dismiss_url = wp_nonce_url( add_query_arg( 'w4pl_dismiss_welcome', '1' ), 'w4pl_dismiss_welcome' );
		?>
		<div class="notice notice-info is-dismissible w4pl-welcome-notice">
			<p>
				<strong><?php esc_html_e( 'Welcome to W4 Post List — three steps to your first list:', 'w4-post-list' ); ?></strong>
			</p>
			<ol style="margin-top:0;">
				<li><?php esc_html_e( 'Create a list (or open the ready-made example) and choose what it shows.', 'w4-post-list' ); ?></li>
				<li><?php esc_html_e( 'Publish it.', 'w4-post-list' ); ?></li>
				<li><?php esc_html_e( 'Place it on any page with the W4 Post List block, or paste its [postlist] shortcode.', 'w4-post-list' ); ?></li>
			</ol>
			<p>
				<a class="button button-primary" href="<?php echo esc_url( $primary_url ); ?>"><?php echo esc_html( $primary_text ); ?></a>
				<a class="button" href="<?php echo esc_url( $docs_url ); ?>"><?php esc_html_e( 'Read the guide', 'w4-post-list' ); ?></a>
				<a href="<?php echo esc_url( $dismiss_url ); ?>" style="margin-left:8px;"><?php esc_html_e( 'Dismiss', 'w4-post-list' ); ?></a>
			</p>
		</div>
		<script>
		( function () {
			var notice = document.querySelector( '.w4pl-welcome-notice' );
			if ( ! notice ) {
				return;
			}
			notice.addEventListener( 'click', function ( e ) {
				if ( ! e.target.classList.contains( 'notice-dismiss' ) ) {
					return;
				}
				var data = new FormData();
				data.append( 'action', 'w4pl_dismiss_welcome' );
				data.append( 'nonce', <?php echo wp_json_encode( wp_create_nonce( 'w4pl_dismiss_welcome' ) ); ?> );
				fetch( ajaxurl, { method: 'POST', credentials: 'same-origin', body: data } );
			} );
		} )();
		</script>
		<?php
	}

	/**
	 * No-JS dismiss fallback via nonce-checked GET parameter.
	 */
	public function maybe_dismiss_welcome() {
		if ( ! isset( $_GET['w4pl_dismiss_welcome'] ) ) {
			return;
		}

		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'w4pl_dismiss_welcome' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_pages' ) ) {
			return;
		}

		delete_option( 'w4pl_welcome_pending' );

		wp_safe_redirect( remove_query_arg( array( 'w4pl_dismiss_welcome', '_wpnonce' ) ) );
		exit;
	}

	/**
	 * Persist welcome-notice dismissal from the notice's X button.
	 */
	public function ajax_dismiss_welcome() {
		check_ajax_referer( 'w4pl_dismiss_welcome', 'nonce' );

		if ( ! current_user_can( 'edit_pages' ) ) {
			wp_send_json_error();
		}

		delete_option( 'w4pl_welcome_pending' );

		wp_send_json_success();
	}

	/**
	 * Onboarding panel on the Lists screen when no lists exist.
	 */
	public function empty_state() {
		$screen = get_current_screen();
		if ( ! $screen || 'edit-w4pl' !== $screen->id ) {
			return;
		}

		$counts = wp_count_posts( 'w4pl' );
		if ( array_sum( (array) $counts ) > 0 ) {
			return;
		}
		?>
		<div class="notice notice-info">
			<p>
				<strong><?php esc_html_e( 'No lists yet.', 'w4-post-list' ); ?></strong>
				<?php esc_html_e( 'A list is a saved query (posts, terms or users) plus a template that controls its HTML. Create one, publish it, then place it anywhere with the W4 Post List block or the [postlist] shortcode.', 'w4-post-list' ); ?>
			</p>
			<p>
				<a class="button button-primary" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=w4pl' ) ); ?>"><?php esc_html_e( 'Create your first list', 'w4-post-list' ); ?></a>
				<a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=w4pl&page=w4pl-docs' ) ); ?>"><?php esc_html_e( 'Getting started guide', 'w4-post-list' ); ?></a>
			</p>
		</div>
		<?php
	}

	/**
	 * Contextual Help tab on the list screens.
	 *
	 * @param WP_Screen $screen Current screen.
	 */
	public function help_tabs( $screen ) {
		if ( ! $screen || ! in_array( $screen->id, array( 'w4pl', 'edit-w4pl' ), true ) ) {
			return;
		}

		ob_start();
		?>
		<p><strong><?php esc_html_e( 'How W4 Post List works', 'w4-post-list' ); ?></strong></p>
		<ol>
			<li><?php esc_html_e( 'Create a list and pick a List Type (Posts, Terms, Users, or combined).', 'w4-post-list' ); ?></li>
			<li><?php esc_html_e( 'Configure what to show in the section named after your list type. The advanced query sections are optional.', 'w4-post-list' ); ?></li>
			<li><?php esc_html_e( 'Publish. A default template is applied automatically; edit the Template section for full HTML control.', 'w4-post-list' ); ?></li>
			<li><?php esc_html_e( 'Place the list with the W4 Post List block, its [postlist] shortcode (see the Shortcode column), or the widget.', 'w4-post-list' ); ?></li>
		</ol>
		<p><?php esc_html_e( 'If a list renders nothing: check it is Published, the shortcode ID matches, and the template keeps its loop tags.', 'w4-post-list' ); ?></p>
		<?php
		$content = ob_get_clean();

		$screen->add_help_tab(
			array(
				'id'      => 'w4pl-overview',
				'title'   => __( 'W4 Post List', 'w4-post-list' ),
				'content' => $content,
			)
		);

		$screen->set_help_sidebar(
			'<p><strong>' . esc_html__( 'More help', 'w4-post-list' ) . '</strong></p>' .
			'<p><a href="' . esc_url( admin_url( 'edit.php?post_type=w4pl&page=w4pl-docs' ) ) . '">' . esc_html__( 'Documentation', 'w4-post-list' ) . '</a></p>'
		);
	}
}
