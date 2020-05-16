<?php
/**
 * Abstract query class
 *
 * @package W4_Post_List
 */

/**
 * Query class
 */
abstract class W4PL_Query {
	/**
	 * Query arguments
	 *
	 * @var array
	 */
	public $query_args;

	/**
	 * SQL
	 *
	 * @var string
	 */
	public $request;

	/**
	 * Queried table
	 *
	 * @var string
	 */
	public $table;

	/**
	 * Errors
	 *
	 * @var array
	 */
	public $errors;

	/**
	 * Query result
	 *
	 * @var mixed
	 */
	public $results;

	/**
	 * Found items
	 *
	 * @var integer
	 */
	public $found_item = 0;

	/**
	 * Limit
	 *
	 * @var string
	 */
	public $limit = '';

	/**
	 * Offset page
	 *
	 * @var int
	 */
	public $page = 1;

	/**
	 * Number of page
	 *
	 * @var int
	 */
	public $max_num_pages = 1;

	/**
	 * MM
	 *
	 * @var string
	 */
	public $qr = 'get_results';

	/**
	 * Constructor
	 *
	 * @param array $query_args [description].
	 */
	public function __construct( $query_args ) {
		$this->query_args = $query_args;
	}

	/**
	 * Set query argument
	 *
	 * @param sting $key [description].
	 * @param mixed $val [description].
	 */
	public function set( $key, $val ) {
		$this->query_args[ $key ] = $val;
	}

	/**
	 * Get query argument
	 *
	 * @param sting $key [description].
	 * @param mixed $default [description].
	 */
	public function get( $key, $default = '' ) {
		return array_key_exists( $key, $this->query_args ) ? $this->query_args[ $key ] : $default;
	}

	/**
	 * Get results
	 */
	public function get_results() {
		return $this->results;
	}

	/**
	 * Parse query var
	 */
	public function parse_query_vars() {
		global $wpdb;

		if ( ! is_array( $this->query_args ) ) {
			$this->query_args = array();
		}

		if ( '' === $this->get( 'table' ) ) {
			$this->errors[] = __( 'Table Not Defined', 'w4-post-list' );
			return;
		}

		$allowed = array( 'posts', 'terms', 'users' );
		if ( ! in_array( $this->get( 'table' ), $allowed, true ) ) {
			/* translators: %s: MySql table name */
			$this->errors[] = sprintf( __( 'Quering for table is not allowed.:%s', 'w4-post-list' ), $this->get( 'table' ) );
		}

		foreach ( $allowed as $table ) {
			$this->$table = $wpdb->prefix . $table;
		}

		$this->table = $wpdb->prefix . $this->get( 'table' );

		if ( '' == $this->get( 'order' ) || ! in_array( strtoupper( $this->get( 'order' ) ), array( 'ASC', 'DESC' ) ) ) {
			$this->set( 'order', 'ASC' );
		}

		$this->set( 'order', strtoupper( $this->get( 'order' ) ) );

		if ( '' == $this->get( 'page' ) ) {
			$this->set( 'page', $this->page );
		} else {
			$this->page = $this->get( 'page' ) < 1 ? 1 : $this->get( 'page' );
		}

		if ( '' != $this->get( 'limit' ) ) {
			$this->limit = absint( $this->get( 'limit' ) );
		}

		$this->output = $this->get( 'output' ) ? $this->get( 'output' ) : OBJECT;
	}

	/**
	 * Flush variable
	 */
	public function init() {
		$this->table       = '';
		$this->output      = '';
		$this->_select     = '';
		$this->_fields     = '';
		$this->_found_rows = '';
		$this->_join       = '';
		$this->_where      = '';
		$this->_groupby    = '';
		$this->_order      = '';
		$this->_limit      = '';
		$this->_qr         = '';
	}

	/**
	 * Should be defined by extender
	 */
	public function query(){}

	/**
	 * Parse searchable field
	 *
	 * @param array $args [description].
	 */
	protected function parse_search_fields( $args = array() ) {
		global $wpdb;

		if ( empty( $args ) ) {
			return;
		}

		$args = array_filter( $args );

		foreach ( $args as $column => $term ) {
			/*
			if( 0 === strrpos($column, '__like') )
				$column = str_replace('__like', '', $column);
			*/

			$search_terms = array();
			preg_match_all( '/".*?("|$)|((?<=[\\s",+])|^)[^\\s",+]+/', $term, $matches );
			if ( is_array( $matches[0] ) ) {
				foreach ( $matches[0] as $s ) {
					$search_terms[] = trim( $s, "\"'\n\r " );
				}
			} else {
				$search_terms[] = $matches[0];
			}

			$n         = '%';
			$searchand = '';
			$search    = '';

			foreach ( (array) $search_terms as $term ) {
				$term      = $wpdb->esc_like( $term );
				$search   .= "{$searchand}($column LIKE '{$n}{$term}{$n}')";
				$searchand = ' OR ';
			}

			if ( ! empty( $search ) ) {
				$this->_where .= " AND ({$search}) ";
			}
		}
	}

	/**
	 * Parse interger fields
	 *
	 * @param  array  $args    [description].
	 * @param  string $compare [description].
	 */
	public function parse_interger_fields( $args = array(), $compare = 'IN' ) {
		if ( empty( $args ) ) {
			return;
		}

		foreach ( $args as $request => $column ) {
			if ( is_numeric( $request ) ) {
				$request = $column;
			}

			if ( '' != $this->get( $request ) ) {
				$var = $this->get( $request );
				if ( empty( $var ) ) {
					continue;
				}

				if ( ! is_array( $var ) && strpos( $var, ',' ) ) {
					$var = explode( ',', $var );
				} elseif ( is_numeric( $var ) ) {
					$var = array( $var );
				}

				$this->_where .= " AND {$column} {$compare} (" . implode( ',', array_map( 'intval', $var ) ) . ')';
			}
		}
	}

	/**
	 * Parse text field
	 *
	 * @param array $args [description].
	 */
	public function parse_text_fields( $args = array() ) {
		if ( empty( $args ) ) {
			return;
		}

		foreach ( $args as $request => $column ) {
			if ( is_numeric( $request ) ) {
				$request = $column;
			}

			if ( '' != $this->get( $request ) ) {
				$var = $this->get( $request );
				if ( is_array( $var ) && ! empty( $var ) ) {
					$this->_where .= " AND $column IN (\"" . implode( '","', array_map( 'esc_sql', $var ) ) . '")';
				} else {
					$this->_where .= " AND $column = '" . esc_sql( $var ) . "'";
				}
			}
		}
	}

	/**
	 * Parse sortable field
	 *
	 * @param array $args [description].
	 */
	public function parse_sortable_fields( $args = array() ) {
		if ( empty( $args ) ) {
			return;
		}
		foreach ( $args as $request => $reset ) {
			if ( $request == $this->get( 'orderby' ) ) {
				$this->set( 'orderby', $reset );
			}
			if ( $request == $this->get( 'sb' ) ) {
				$this->set( 'sb', $reset );
			}
		}
	}

	/**
	 * Populate results
	 */
	public function results() {
		global $wpdb;

		if ( ! empty( $this->errors ) ) {
			$error_obj = new WP_Error();
			foreach ( $this->errors as $error ) {
				$error_obj->add( 'error', $error );
			}

			return $error_obj;
		}

		if ( ! empty( $this->errors ) ) {
			return new WP_Error( 'error', $this->errors );
		}

		if ( '' == $this->get( 'qr' ) ) {
			if ( '' != $this->_qr ) {
				$this->set( 'qr', $this->_qr );
			} elseif ( '' != $this->get( 'column' ) ) {
				$this->set( 'qr', 'get_col' );
			}
		}

		// $this->set( 'qr', 'get_var' );
		// echo $this->get( 'column' );
		// echo $this->get( 'qr' );
		// echo $this->_qr;

		if ( ! in_array( $this->get( 'qr' ), array( 'get_row', 'get_var', 'get_col', 'count_row' ) ) ) {
			$this->set( 'qr', 'get_results' );
		}

		if ( $this->get( 'qr' ) === 'get_col' ) {
			$result = $wpdb->get_col( $this->request );
		} elseif ( $this->get( 'qr' ) === 'count_row' || $this->get( 'qr' ) === 'get_var' ) {
			$result = $wpdb->get_var( $this->request );
		} elseif ( $this->get( 'qr' ) === 'get_row' ) {
			$result = $wpdb->get_row( $this->request, $this->output );
		} else {
			$result = $wpdb->get_results( $this->request, $this->output );
		}

		if ( '' != $this->limit ) {
			$this->found_item    = $wpdb->get_var( 'SELECT FOUND_ROWS()' );
			$this->max_num_pages = ceil( $this->found_item / $this->limit );
		} else {
			$this->found_item    = count( $result );
			$this->max_num_pages = 1;
		}

		$this->results = $result;

		return $this->results;
	}
}
