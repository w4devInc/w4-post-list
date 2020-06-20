<?php
/**
 * Extra shortcodes for generating dynamic comparison values
 *
 * @class W4PL_Date_Shortcode
 * @package W4_Post_List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Date shortcode
 */
class W4PL_Date_Shortcode {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_shortcode( 'w4pl_time', array( $this, 'time_shortcode' ) );
		add_shortcode( 'w4pl_date', array( $this, 'date_shortcode' ) );
	}

	/**
	 * Time shortcode
	 *
	 * @param  array $atts Attributes.
	 */
	public function time_shortcode( $atts ) {
		$args = shortcode_atts(
			array(
				'hour' => '0',
				'day'  => '0',
			),
			$atts
		);

		$adjust = intval( $args['hour'] ) * HOUR_IN_SECONDS + intval( $args['day'] ) * DAY_IN_SECONDS;
		return time() + $adjust;
	}

	/**
	 * Date shortcode
	 *
	 * @param  array $atts Attributes.
	 */
	public function date_shortcode( $atts = array() ) {
		$args = shortcode_atts(
			array(
				'hour'   => '0',
				'day'    => '0',
				'format' => 'Y-m-d H:i:s',
			),
			$atts
		);

		$adjust = intval( $args['hour'] ) * HOUR_IN_SECONDS + intval( $args['day'] ) * DAY_IN_SECONDS;
		return gmdate( $args['format'], time() + $adjust );
	}
}
