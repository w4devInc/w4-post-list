<?php
/**
 * Terms query class.
 *
 * @class W4PL_Terms_Query
 * @package W4_Post_List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Terms query class.
 */
class W4PL_Terms_Query extends W4PL_Query {

	/**
	 * Constructor
	 *
	 * @param array $query_args Query arguments.
	 */
	function __construct( $query_args = array() ) {
		$query_args['table'] = 'terms';
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
		} elseif ( '' != $this->get( 'columns' )) {
			$this->_fields .= " TB.". implode( ", TB.", $this->get('columns')) ."";
		} elseif ( $this->get( 'qr' ) == 'count_row' ) {
			$this->_fields .= " COUNT(*)";
		} else{
			$this->_fields .= " TB.*";
		}

		if ( '' != $this->get( 'taxonomy' ) ) {
			$this->_fields .= ", TT1.*";
			$this->_join .= " INNER JOIN {$wpdb->term_taxonomy} TT1 ON (TT1.term_id = TB.term_id AND TT1.taxonomy = '". $this->get('taxonomy') ."')";
		}

		$count__min = (int) $this->get('count__min');
		if ( $count__min > 0 ) {
			$this->_where .= " AND TT1.count >= $count__min";
		}

		$this->parse_interger_fields( array(
			'term_id__in' 		=> 'TB.term_id',
			'term_parent__in' 	=> 'TT1.parent'
		));
		$this->parse_interger_fields( array(
			'term_id__not_in' => 'TB.term_id'
		), 'NOT IN');

		$this->parse_text_fields( array(
			'name'			=> 'name',
			'slug'			=> 'slug'
		));

		$this->parse_search_fields( array(
			'name'			=> $this->get('name__like'),
			'slug'			=> $this->get('slug__like')
		));

		if ( '' != $this->get( 'orderby' ) ) {
			$order = $this->get('order');
			$orderby = $this->get('orderby');

			if ( 'term_id__in' == $orderby ) {
				$term_id__in = implode( ',', array_map('absint', $this->get('term_id__in') ) );
				$this->_order .= " ORDER BY FIELD( TB.term_id, $term_id__in )";
			} else {
				if ( in_array( $orderby, array( 'term_id', 'name', 'slug', 'term_group', 'term_order' ) ) ) {
					$orderby = "TB.{$orderby}";
				} elseif ( in_array( $orderby, array( 'parent', 'count' ) ) ) {
					$orderby = "TT1.{$orderby}";
				}
				
				$this->_order .= " ORDER BY {$orderby} $order";
			}
		}

		if ( '' != $this->limit ) {
			if ( '' == $this->get( 'offset' ) ) {
				$start = ( $this->page - 1 ) * $this->limit . ', ';
				$this->_limit .= ' LIMIT ' . $start . $this->limit;
			} else {
				$this->set( 'offset', absint( $this->get( 'offset' )));
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
		// let post format change the result
		return apply_filters( 'get_terms', $this->results, $this->get( 'taxonomy' ), $this->query_args );
	}
}
