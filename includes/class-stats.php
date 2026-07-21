<?php
/**
 * Anonymous usage counters.
 *
 * Counters accumulate locally in the `w4pl_stats` option and are only ever
 * transmitted as part of the Appsero Insights ping, which the user must
 * explicitly opt in to (see appsero.php / w4pl_insights_extra). No events
 * are sent individually and nothing is collected without consent.
 *
 * Future features add their own counters via W4PL_Stats::increment().
 *
 * @class W4PL_Stats
 * @package W4_Post_List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Local usage counters for opt-in telemetry.
 */
class W4PL_Stats {

	const OPTION = 'w4pl_stats';

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'transition_post_status', array( $this, 'track_list_status' ), 10, 3 );
	}

	/**
	 * Increment a named counter.
	 *
	 * @param string $key Counter name.
	 * @param int    $by  Amount to add.
	 */
	public static function increment( $key, $by = 1 ) {
		$stats = self::all();

		if ( ! isset( $stats[ $key ] ) ) {
			$stats[ $key ] = 0;
		}

		$stats[ $key ] = (int) $stats[ $key ] + $by;

		update_option( self::OPTION, $stats, false );
	}

	/**
	 * Get one counter value.
	 *
	 * @param  string $key Counter name.
	 * @return int
	 */
	public static function get( $key ) {
		$stats = self::all();

		if ( ! isset( $stats[ $key ] ) ) {
			return 0;
		}

		return (int) $stats[ $key ];
	}

	/**
	 * Get all counters.
	 *
	 * @return array
	 */
	public static function all() {
		$stats = get_option( self::OPTION, array() );

		if ( ! is_array( $stats ) ) {
			return array();
		}

		return $stats;
	}

	/**
	 * Count list creations and first-time publishes.
	 *
	 * @param string  $new_status New post status.
	 * @param string  $old_status Old post status.
	 * @param WP_Post $post       Post object.
	 */
	public function track_list_status( $new_status, $old_status, $post ) {
		if ( ! $post instanceof WP_Post || 'w4pl' !== $post->post_type ) {
			return;
		}

		if ( in_array( $old_status, array( 'new', 'auto-draft' ), true ) && ! in_array( $new_status, array( 'new', 'auto-draft' ), true ) ) {
			self::increment( 'lists_created' );
		}

		if ( 'publish' === $new_status && 'publish' !== $old_status ) {
			self::increment( 'lists_published' );
		}
	}
}
