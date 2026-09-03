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
			// is not a known column never reaches the SQL.
			if ( array_key_exists( $orderby, W4PL_Config::users_orderby_options() ) ) {
				$this->_order .= " ORDER BY $orderby $order";
			}
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
	 * {prefix}capabilities row in wp_usermeta, keyed per site so the same
	 * user can hold different roles across a multisite network. An EXISTS
	 * subquery keeps the row count intact, which matters because pagination
	 * here is driven by SQL_CALC_FOUND_ROWS.
	 *
	 * Slugs are reduced to sanitize_key's alphabet before use, so nothing a
	 * saved option carries can escape the LIKE. A slug that no longer maps to
	 * a role simply matches no rows, which fails closed: a users list whose
	 * role was deleted shows nobody rather than everybody.
	 */
	protected function parse_role_field() {
		global $wpdb;

		$roles = array_filter( array_map( 'sanitize_key', (array) $this->get( 'role__in', array() ) ) );

		if ( empty( $roles ) ) {
			return;
		}

		$likes = array();
		foreach ( $roles as $role ) {
			$likes[] = $wpdb->prepare( 'UM.meta_value LIKE %s', '%"' . $wpdb->esc_like( $role ) . '"%' );
		}

		$meta_key = $wpdb->prepare( 'UM.meta_key = %s', $wpdb->get_blog_prefix() . 'capabilities' );

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
