<?php
/**
 * Users query class.
 *
 * @class W4PL_Users_Query
 * @package W4_Post_List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Users query class.
 */
class W4PL_Users_Query extends W4PL_Query {

	/**
	 * Constructor
	 *
	 * @param array $query_args Query arguments.
	 */
	function __construct( $query_args ) {
		$query_args['table'] = 'users';
		parent::__construct( $query_args );
	}

	/**
	 * Build SQL
	 */
	function query() {
		$this->init();
		$this->parse_query_vars();

		if ( ! empty( $this->errors ) ) {
			return;
		}

		global $wpdb;

		$this->_select = 'SELECT';
		$this->_join   = " FROM $this->table AS TB";
		$this->_where  = ' WHERE 1=1';

		if ( '' != $this->get( 'column' ) ) {
			$this->_fields .= ' TB.' . $this->get( 'column' ) . '';
		} elseif ( '' != $this->get( 'columns' ) ) {
			$this->_fields .= ' TB.' . implode( ', TB.', $this->get( 'columns' ) ) . '';
		} elseif ( $this->get( 'qr' ) == 'count_row' ) {
			$this->_fields .= ' COUNT(*)';
		} else {
			$this->_fields .= ' TB.*';
		}

		$this->parse_interger_fields(
			array(
				'ID__in' => 'TB.ID',
			)
		);
		$this->parse_interger_fields(
			array(
				'ID__not_in' => 'TB.ID',
			),
			'NOT IN'
		);

		$this->parse_search_fields(
			array(
				'display_name' => $this->get( 'display_name__like' ),
				'user_email'   => $this->get( 'user_email__like' ),
			)
		);

		$this->parse_role_field();

		if ( '' != $this->get( 'orderby' ) ) {
			$order   = $this->get( 'order' );
			$orderby = $this->get( 'orderby' );

			// This class interpolates rather than prepares, so an orderby that
			// is not a known column never reaches the SQL. Fall back to ID
			// rather than dropping the clause: an unordered LIMIT query has no
			// stable row order, so pagination would repeat and skip users.
			if ( ! array_key_exists( $orderby, W4PL_Config::users_orderby_options() ) ) {
				$orderby = 'ID';
			}

			$this->_order .= " ORDER BY $orderby $order";
		}

		if ( '' != $this->limit ) {
			if ( '' == $this->get( 'offset' ) ) {
				$start         = ( $this->page - 1 ) * $this->limit . ', ';
				$this->_limit .= ' LIMIT ' . $start . $this->limit;
			} else {
				$this->set( 'offset', absint( $this->get( 'offset' ) ) );
				$start         = $this->get( 'offset' ) . ', ';
				$this->_limit .= ' LIMIT ' . $start . $this->limit;
			}
		}

		if ( '' != $this->limit ) {
			$this->_found_rows = ' SQL_CALC_FOUND_ROWS';
		}

		$this->request = $this->_select . $this->_found_rows . $this->_fields . $this->_join . $this->_where . $this->_groupby . $this->_order . $this->_limit;
		$this->request = apply_filters( 'w4pl_query_request', $this->request, $this->query_args );

		$this->results();
	}

	/**
	 * Restrict the query to the requested roles.
	 *
	 * Roles are not a column on wp_users: they live in the serialized
	 * {prefix}capabilities row in wp_usermeta, alongside any individually
	 * granted capabilities, which is why core's own WP_User_Query matches
	 * them with the same LIKE. An EXISTS subquery keeps the row count intact
	 * -- a user holding two of the filtered roles must still be one row --
	 * and that matters because pagination here rides on SQL_CALC_FOUND_ROWS.
	 *
	 * Every slug is checked against the live role registry, so only a
	 * registered role can reach the query at all. Requesting a role that is
	 * not registered fails closed: the list shows nobody rather than falling
	 * back to every user on the site, administrators included. Values still
	 * go through prepare, because a role slug may legitimately contain the
	 * LIKE wildcards _ and %.
	 */
	protected function parse_role_field() {
		global $wpdb;

		// A blank checkbox value is noise, not a filter.
		$requested = array_filter(
			array_map( 'strval', (array) $this->get( 'role__in', array() ) ),
			'strlen'
		);

		if ( empty( $requested ) ) {
			return;
		}

		$registered = wp_roles()->get_names();
		$roles      = array_filter(
			$requested,
			function ( $role ) use ( $registered ) {
				return array_key_exists( $role, $registered );
			}
		);

		if ( empty( $roles ) ) {
			// A filter was asked for and none of it is real. Match nothing.
			$this->_where .= ' AND 1=0';
			return;
		}

		$likes = array();
		foreach ( $roles as $role ) {
			$likes[] = $wpdb->prepare( 'UM.meta_value LIKE %s', '%"' . $wpdb->esc_like( $role ) . '"%' );
		}

		$meta_key = $wpdb->prepare( 'UM.meta_key = %s', $wpdb->get_blog_prefix() . 'capabilities' );

		// prepare() leaves its placeholder-escape tokens in these fragments;
		// wpdb strips them when the query runs. Anything reading $request
		// before then -- the w4pl_query_request filter, a debug dump -- sees
		// them in place of literal % characters.
		$this->_where .= " AND EXISTS ( SELECT 1 FROM $wpdb->usermeta AS UM"
			. " WHERE UM.user_id = TB.ID AND $meta_key AND ( " . implode( ' OR ', $likes ) . ' ) )';
	}

	/**
	 * Get results
	 *
	 * @return mixed
	 */
	function get_results() {
		return apply_filters( 'get_users', $this->results, $this->get( 'taxonomy' ), $this->query_args );
	}
}
