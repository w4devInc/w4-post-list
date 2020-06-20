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

		$this->_select = "SELECT";
		$this->_join = " FROM $this->table AS TB";
		$this->_where = " WHERE 1=1";

		if ( '' != $this->get( 'column' ) ) {
			$this->_fields .= " TB.". $this->get( 'column' ) ."";
		} elseif ( '' != $this->get( 'columns' ) ) {
			$this->_fields .= " TB.". implode( ", TB.", $this->get('columns') ) ."";
		} elseif ( $this->get( 'qr' ) == 'count_row' ) {
			$this->_fields .= " COUNT(*)";
		} else {
			$this->_fields .= " TB.*";
		}

		$this->parse_interger_fields( array(
			'ID__in' => 'TB.ID'
		) );
		$this->parse_interger_fields( array(
			'ID__not_in' => 'TB.ID'
		), 'NOT IN');

		$this->parse_search_fields( array(
			'display_name'	=> $this->get('display_name__like'),
			'user_email'	=> $this->get('user_email__like')
		) );

		if ( '' != $this->get('orderby') ) {
			$order = $this->get( 'order' );
			$orderby = $this->get( 'orderby' );
			$this->_order .= " ORDER BY $orderby $order";
		}

		if ( '' != $this->limit ) {
			if ( '' == $this->get( 'offset' ) ) {
				$start = ( $this->page - 1 ) * $this->limit . ', ';
				$this->_limit .= ' LIMIT ' . $start . $this->limit;
			} else {
				$this->set( 'offset', absint( $this->get( 'offset' ) ));
				$start = $this->get( 'offset' ) . ', ';
				$this->_limit .= ' LIMIT ' . $start . $this->limit;
			}
		}

		if ( '' != $this->limit ) {
			$this->_found_rows = " SQL_CALC_FOUND_ROWS";
		}

		$this->request = $this->_select . $this->_found_rows . $this->_fields . $this->_join . $this->_where . $this->_groupby . $this->_order . $this->_limit;
		$this->request = apply_filters( 'w4pl_query_request', $this->request, $this->query_args );

		$this->results();
	}

	/**
	 * Get results
	 *
	 * @return mixed
	 */
	function get_results() {
		return apply_filters( 'get_users', $this->results, $this->get('taxonomy'), $this->query_args );
	}
}
