<?php
/**
 * Static Shortcodes for generating dynamic comparison values
 * @package WordPress
 * @subpackage W4 Post List
 * @author Shazzad Hossain Khan
 * @url http://w4dev.com/plugins/w4-post-list
**/


class W4PL_Helper_Shortcodes
{
	function __construct()
	{
		add_shortcode( 'w4pl_time', 		array($this, 'time_shortcode') );
		add_shortcode( 'w4pl_date', 		array($this, 'date_shortcode') );
	}

	public function time_shortcode( $atts )
	{
		$args = shortcode_atts( array('hour' => '0', 'day' => '0'), $atts );
		extract( $args );

		$adjust = intval($hour) * HOUR_IN_SECONDS + intval($days) * DAY_IN_SECONDS;
		return time() + $adjust;
	}

	public function date_shortcode( $atts )
	{
		$args = shortcode_atts( array('hour' => '0', 'day' => '0', 'format' => 'Y-m-d H:i:s',), $atts );
		extract( $args );

		$adjust = intval($hour) * HOUR_IN_SECONDS + intval($day) * DAY_IN_SECONDS;
		return date($format, time() + $adjust );
	}
}

	new W4PL_Helper_Shortcodes;
?>