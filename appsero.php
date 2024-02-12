<?php
/**
 * Appsero functions.
 *
 * @package W4_Post_List
 */

/**
 * Appsero insights instance
 */
function w4pl_appsero_insights_instance() {
	static $insights = null;

	if ( is_null( $insights ) && class_exists( 'Appsero\Client' ) ) {
		$client   = new Appsero\Client(
			'fc943997-f146-4d10-867c-115d155c7fdd',
			'W4 Post List',
			W4PL_PLUGIN_FILE
		);
		$insights = $client->insights();
	}

	return $insights;
}

/**
 * Appsero initializion function
 */
function w4pl_appsero_init() {
	$insights = w4pl_appsero_insights_instance();

	if ( ! is_null( $insights ) ) {
		$insights
			->add_extra( 'w4pl_insights_extra' )
			->init();

		// We want to some texts, so lets remove it from here.
		remove_action( 'admin_notices', array( $insights, 'admin_notice' ) );

		// Display the modified notice.
		add_action( 'admin_notices', 'w4pl_appsero_admin_notices' );
	}
}
w4pl_appsero_init();

/**
 * Appsero initializion function
 */
function w4pl_appsero_admin_notices() {
	global $pagenow, $typenow;

	$insights = w4pl_appsero_insights_instance();
	if ( is_null( $insights ) ) {
		return;
	}

	if (
		( in_array( $pagenow, array( 'edit.php' ), true ) && 'w4pl' === $typenow )
		|| 'plugins.php' === $pagenow
	) {
		$insights->notice = __( 'Allow us to understand how we can improve W4 Post List Plugin by collecting some diagnostic data and usage information.' );

		ob_start();
		$insights->admin_notice();
		$notice = ob_get_clean();

		$notice = str_replace(
			array(
				', Your name and email address',
				' <a href="https://appsero.com/privacy-policy/">Learn more</a> about how Appsero collects and handle your data.',
			),
			'',
			$notice
		);

		$allowed_html           = wp_kses_allowed_html( 'post' );
		$allowed_html['script'] = array( 'type' => true, 'src' => true, 'async' => true, 'defer' => true );

		$notice = wp_kses( $notice, $allowed_html );

		// Not sure why kses is not working for style attribute.
		$notice = str_replace( '<p class="description">', '<p class="description" style="display:none">', $notice );
		echo $notice;
	}
}

/**
 * Admin footer styles for plugins.php page
 */
function w4pl_appsero_admin_footer() {
	global $pagenow;

	if ( 'plugins.php' !== $pagenow ) {
		return;
	}

	echo '<style>.wd-dr-modal-body{height:280px; overflow-x:auto;}.wd-dr-modal-body textarea{max-width:100%;}</style>';
}
add_action( 'admin_footer', 'w4pl_appsero_admin_footer', 11 );

/**
 * Appsero extradata callback function
 *
 * @return array Possible extra data.
 */
function w4pl_insights_extra() {
	global $wpdb;

	$extra = array(
		'ListCount'      => 0,
		'Editor'         => 'Nop',
		'Elementor'      => 'Nop',
		'VisualComposer' => 'Nop',
	);

	$counts             = wp_count_posts( 'w4pl' );
	$extra['ListCount'] = $counts->publish + $counts->private + $counts->pending + $counts->draft;

	global $wp_version;
	if ( defined( 'GUTENBERG_VERSION' ) ) {
		$extra['Editor'] = 'Block';
	} elseif ( version_compare( $wp_version, '5.0.0', '>=' ) ) {
		if ( class_exists( 'Classic_Editor' ) ) {
			$extra['Editor'] = 'Classic';
		} else {
			$extra['Editor'] = 'Block';
		}
	}

	if ( defined( 'ELEMENTOR_VERSION' ) ) {
		$extra['Elementor'] = 'Active';
	}

	if ( defined( 'WPB_VC_VERSION' ) ) {
		$extra['VisualComposer'] = 'Active';
	}

	return $extra;
}


/**
 * We don't need admin name & email, better exclude them.
 *
 * @param  array  $args  Arguments.
 * @param  string $url  Request url.
 * @return array        [description].
 */
function w4pl_filter_appsero_request( $args, $url ) {
	if ( 0 === strpos( $url, 'https://api.appsero.com' ) ) {
		if ( ! empty( $args['body'] ) ) {
			if ( isset( $args['body']['admin_email'] ) ) {
				$args['body']['admin_email'] = 'anonymous@example.com';
			}
			if ( isset( $args['body']['user_email'] ) ) {
				$args['body']['user_email'] = 'anonymous@example.com';
			}
			if ( isset( $args['body']['first_name'] ) ) {
				$args['body']['first_name'] = 'John';
			}
			if ( isset( $args['body']['first_name'] ) ) {
				$args['body']['last_name'] = 'Doe';
			}
			if ( isset( $args['body']['ip_address'] ) ) {
				$args['body']['ip_address'] = '192.168.' . wp_rand( 0, 255 ) . '.' . wp_rand( 0, 255 );
			}

			if ( isset( $args['body']['extra'] ) ) {
				$extra = sprintf(
					'%d, %s',
					$args['body']['extra']['ListCount'],
					$args['body']['extra']['Editor']
				);

				foreach ( array( 'Elementor', 'VisualComposer' ) as $param ) {
					if ( 'Active' === $args['body']['extra'][ $param ] ) {
						$extra .= ', ' . $param;
					}
				}

				$args['body']['site'] = sprintf(
					'%s - %s',
					substr( $args['body']['site'], 0, 30 ),
					$extra
				);
			}
		}
	}

	return $args;
}
add_filter( 'http_request_args', 'w4pl_filter_appsero_request', 10, 2 );
