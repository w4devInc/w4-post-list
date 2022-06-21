<?php
/**
 * Class for displaying a list
 *
 * @package W4_Post_List
 */

/**
 * List class
 */
abstract class W4PL_List {

	/**
	 * List id
	 *
	 * @var int
	 */
	public $id;

	/**
	 * List type
	 *
	 * @var string
	 */
	public $type;

	/**
	 * List options
	 *
	 * @var array
	 */
	public $options;

	/**
	 * Constructor
	 *
	 * @param array $options List options.
	 */
	public function __construct( $options ) {
		$this->id      = $options['id'];
		$this->type    = $options['list_type'];
		$this->options = $options;
	}

	/**
	 * Navigation html template
	 *
	 * @param  int     $max_num_pages [description].
	 * @param  integer $paged         [description].
	 * @param  array   $attr          [description].
	 *
	 * @return string                 Html template.
	 */
	public function navigation( $max_num_pages, $paged = 1, $attr = array() ) {
		$nav_type  = isset( $attr['type'] ) ? $attr['type'] : '';
		$prev_text = isset( $attr['prev_text'] ) && ! empty( $attr['prev_text'] ) ? $attr['prev_text'] : __( 'Previous' );
		$next_text = isset( $attr['next_text'] ) && ! empty( $attr['next_text'] ) ? $attr['next_text'] : __( 'Next' );

		$return = '';

		// the query parameter for pagination.
		$paged_qp = 'page' . $this->id;

		// remove current lists query parameter from base, other lists qr will be kept.
		$base = remove_query_arg( $paged_qp, get_pagenum_link() ) . '%_%';

		// if base already have a query parameter, use &.
		if ( strpos( $base, '?' ) ) {
			$format = '&' . $paged_qp . '=%#%';
		} else {
			$format = '?' . $paged_qp . '=%#%';
		}

		$base = str_replace( '#038;', '&', $base );

		if ( in_array( $nav_type, array( 'plain', 'list' ), true ) ) {
			$big      = 10;
			$pag_args = array(
				'type'      => $nav_type,
				'base'      => $base,
				'format'    => $format,
				'current'   => $paged,
				'total'     => $max_num_pages,
				'end_size'  => 2,
				'mid_size'  => 2,
				'prev_text' => $prev_text,
				'next_text' => $next_text,
				'add_args'  => false, // stop wp to add query arguments.
			);

			$return = paginate_links( $pag_args );

		} else {
			$paged = (int) $paged;

			// default navigation.
			if ( 2 === $paged ) {
				$return .= '<a href="' . remove_query_arg( $paged_qp ) . '" class="prev page-numbers prev_text">' . $prev_text . '</a>';
			} elseif ( $paged > 2 ) {
				$return .= '<a href="' . add_query_arg( $paged_qp, ( $paged - 1 ) ) . '" class="prev page-numbers prev_text">' . $prev_text . '</a>';
			}

			if ( $max_num_pages > $paged ) {
				$return .= '<a href="' . add_query_arg( $paged_qp, ( $paged + 1 ) ) . '" class="next page-numbers next_text">' . $next_text . '</a>';
			}
		}

		if ( ! empty( $return ) ) {
			$class    = 'navigation';
			$use_ajax = isset( $attr['ajax'] ) ? (bool) $attr['ajax'] : false;

			if ( $use_ajax ) {
				$class    .= ' ajax-navigation';
				$this->js .= ';(function($){
					$(document).ready(function(){
						$(document.body).on("click", "#w4pl-list-' . $this->id . ' .navigation a.page-numbers", function(){
							var that = $(this), parent = $("#w4pl-list-' . $this->id . '");
							parent.addClass("w4pl-loading");
							parent.load( that.attr("href") + " #" + parent.attr("id") + " .w4pl-inner", function(e){
								parent.removeClass("w4pl-loading");
							});
							return false;
						});
					});
				})(jQuery);';
			}

			$return = '<div class="' . $class . '">' . $return . '</div>';
		}

		return $return;
	}

	/**
	 * Parse post group
	 */
	public function init_posts_groups() {
		$groupby      = $this->options['groupby'];
		$this->groups = array();

		/*
		 * Allow group using modified date.
		 *
		 * @since 1.9.9.6
		 */
		if ( in_array( $groupby, array( 'year', 'month', 'yearmonth' ), true ) && ! in_array( $this->options['groupby_time'], array( 'post_date', 'post_modified' ), true ) ) {
			$groupby_time = 'post_date';
		} else {
			$groupby_time = $this->options['groupby_time'];
		}

		// post parent.
		if ( 'parent' === $groupby ) {
			foreach ( $this->posts_query->posts as $index => $post ) {
				if ( $post->post_parent ) {
					$parent   = get_post( $post->post_parent );
					$group_id = $parent->ID;
					if ( ! isset( $this->groups[ $group_id ] ) ) {
						$this->groups[ $group_id ] = array(
							'id'    => $group_id,
							'title' => $parent->post_title,
							'url'   => get_permalink( $parent->ID ),
						);
					}
				} else {
					$group_id = 0;
					if ( ! isset( $this->groups[ $group_id ] ) ) {
						$this->groups[ $group_id ] = array(
							'id'    => $group_id,
							'title' => 'Unknown',
							'url'   => '',
						);
					}
				}

				if ( ! isset( $this->groups[ $group_id ]['post_ids'] ) ) {
					$this->groups[ $group_id ]['post_ids'] = array();
				}

				$this->groups[ $group_id ]['post_ids'][] = $post->ID;
			}
		} elseif ( 0 === strpos( $groupby, 'tax_' ) ) {
			// terms
			$tax = str_replace( 'tax_', '', $groupby );
			foreach ( $this->posts_query->posts as $index => $post ) {
				if ( $terms = get_the_terms( $post, $tax ) ) {
					$term     = array_shift( $terms );
					$group_id = $term->term_id;
					if ( ! isset( $this->groups[ $group_id ] ) ) {
						$this->groups[ $group_id ] = array(
							'id'    => $group_id,
							'title' => $term->name,
							'url'   => get_term_link( $term ),
						);
					}
				} else {
					$group_id = 0;
					if ( ! isset( $this->groups[ $group_id ] ) ) {
						$this->groups[ $group_id ] = array(
							'id'    => $group_id,
							'title' => 'Unknown',
							'url'   => '',
						);
					}
				}

				if ( ! isset( $this->groups[ $group_id ]['post_ids'] ) ) {
					$this->groups[ $group_id ]['post_ids'] = array();
				}

				$this->groups[ $group_id ]['post_ids'][] = $post->ID;
			}
		} elseif ( 'author' == $groupby ) {
			foreach ( $this->posts_query->posts as $index => $post ) {
				if ( $post->post_author ) {
					$parent   = get_userdata( $post->post_author );
					$group_id = $parent->ID;
					if ( ! isset( $this->groups[ $group_id ] ) ) {
						$this->groups[ $group_id ] = array(
							'id'    => $group_id,
							'title' => $parent->display_name,
							'url'   => get_author_posts_url( $parent->ID ),
						);
					}
				} else {
					$group_id = 0;
					if ( ! isset( $this->groups[ $group_id ] ) ) {
						$this->groups[ $group_id ] = array(
							'id'    => $group_id,
							'title' => 'Unknown',
							'url'   => '',
						);
					}
				}

				if ( ! isset( $this->groups[ $group_id ]['post_ids'] ) ) {
					$this->groups[ $group_id ]['post_ids'] = array();
				}

				$this->groups[ $group_id ]['post_ids'][] = $post->ID;
			}
		}

		// year
		elseif ( 'year' == $groupby ) {
			foreach ( $this->posts_query->posts as $index => $post ) {
				if ( $year = mysql2date( 'Y', $post->{$groupby_time} ) ) {
					$group_id = $year;
					if ( ! isset( $this->groups[ $group_id ] ) ) {
						$this->groups[ $group_id ] = array(
							'id'    => $group_id,
							'title' => $year,
							'url'   => get_year_link( $year ),
						);
					}
				} else {
					$group_id = 0;
					if ( ! isset( $this->groups[ $group_id ] ) ) {
						$this->groups[ $group_id ] = array(
							'id'    => $group_id,
							'title' => 'Unknown',
							'url'   => '',
						);
					}
				}

				if ( ! isset( $this->groups[ $group_id ]['post_ids'] ) ) {
					$this->groups[ $group_id ]['post_ids'] = array();
				}

				$this->groups[ $group_id ]['post_ids'][] = $post->ID;
			}
		}

		// month
		elseif ( 'month' == $groupby ) {
			foreach ( $this->posts_query->posts as $index => $post ) {
				$month = mysql2date( 'm', $post->{$groupby_time} );
				$year  = mysql2date( 'Y', $post->{$groupby_time} );

				if ( $month && $year ) {
					$group_id = $month;
					if ( ! isset( $this->groups[ $group_id ] ) ) {
						$this->groups[ $group_id ] = array(
							'id'    => $group_id,
							'title' => mysql2date( 'F', $post->{$groupby_time} ),
							'url'   => get_month_link( $year, $month ),
						);
					}
				} else {
					$group_id = 0;
					if ( ! isset( $this->groups[ $group_id ] ) ) {
						$this->groups[ $group_id ] = array(
							'id'    => $group_id,
							'title' => 'Unknown',
							'url'   => '',
						);
					}
				}

				if ( ! isset( $this->groups[ $group_id ]['post_ids'] ) ) {
					$this->groups[ $group_id ]['post_ids'] = array();
				}

				$this->groups[ $group_id ]['post_ids'][] = $post->ID;
			}
		}

		// month
		elseif ( 'yearmonth' == $groupby ) {
			foreach ( $this->posts_query->posts as $index => $post ) {
				$month = mysql2date( 'm', $post->{$groupby_time} );
				$year  = mysql2date( 'Y', $post->{$groupby_time} );

				if ( $year && $month ) {
					$group_id = $year . '-' . $month;
					if ( ! isset( $this->groups[ $group_id ] ) ) {
						$this->groups[ $group_id ] = array(
							'id'    => $group_id,
							'title' => mysql2date( 'Y, F', $post->{$groupby_time} ),
							'url'   => get_month_link( $year, $month ),
						);
					}
				} else {
					$group_id = 0;
					if ( ! isset( $this->groups[ $group_id ] ) ) {
						$this->groups[ $group_id ] = array(
							'id'    => $group_id,
							'title' => 'Unknown',
							'url'   => '',
						);
					}
				}

				if ( ! isset( $this->groups[ $group_id ]['post_ids'] ) ) {
					$this->groups[ $group_id ]['post_ids'] = array();
				}

				$this->groups[ $group_id ]['post_ids'][] = $post->ID;
			}
		} elseif ( 'meta_value' == $groupby ) {
			$groupby_meta_key = $this->options['groupby_meta_key'];
			foreach ( $this->posts_query->posts as $index => $post ) {
				$value = get_post_meta( $post->ID, $groupby_meta_key, true );
				if ( $value ) {
					$group_id = $value;
					if ( ! isset( $this->groups[ $group_id ] ) ) {
						$this->groups[ $group_id ] = array(
							'id'    => $group_id,
							'title' => $value,
							'url'   => '',
						);
					}
				} else {
					$group_id = 0;
					if ( ! isset( $this->groups[ $group_id ] ) ) {
						$this->groups[ $group_id ] = array(
							'id'    => $group_id,
							'title' => 'Unknown',
							'url'   => '',
						);
					}
				}

				if ( ! isset( $this->groups[ $group_id ]['post_ids'] ) ) {
					$this->groups[ $group_id ]['post_ids'] = array();
				}

				$this->groups[ $group_id ]['post_ids'][] = $post->ID;
			}
		}

		if ( isset( $this->options['group_order'] ) && ! empty( $this->options['group_order'] ) ) {
			if ( 'ASC' === $this->options['group_order'] ) {
				uasort( $this->groups, array( $this, 'cmp_asc' ) );
			} elseif ( 'DESC' === $this->options['group_order'] ) {
				uasort( $this->groups, array( $this, 'cmp_desc' ) );
			}
		}
	}

	/**
	 * Shortcode regular expression, take from wordpress shortcode parse.
	 */
	public function get_shortcode_regex() {
		$tagnames  = array_keys( w4pl_get_shortcodes() );
		$tagregexp = join( '|', array_map( 'preg_quote', $tagnames ) );

		return '\\['                              // Opening bracket.
			. '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]].
			. "($tagregexp)"                     // 2: Shortcode name.
			. '(?![\\w-])'                       // Not followed by word character or hyphen.
			. '('                                // 3: Unroll the loop: Inside the opening shortcode tag.
			. '[^\\]\\/]*'                   // Not a closing bracket or forward slash.
			. '(?:'
			. '\\/(?!\\])'               // A forward slash not followed by a closing bracket.
			. '[^\\]\\/]*'               // Not a closing bracket or forward slash.
			. ')*?'
			. ')'
			. '(?:'
			. '(\\/)'                        // 4: Self closing tag ...
			. '\\]'                          // ... and closing bracket.
			. '|'
			. '\\]'                          // Closing bracket.
			. '(?:'
			. '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags.
			. '[^\\[]*+'             // Not an opening bracket.
			. '(?:'
			. '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag.
			. '[^\\[]*+'         // Not an opening bracket.
			. ')*+'
			. ')'
			. '\\[\\/\\2\\]'             // Closing shortcode tag.
			. ')?'
			. ')'
			. '(\\]?)';                          // 6: Optional second closing brocket for escaping shortcodes: [[tag]].
	}

	/**
	 * Replace shortcode with html.
	 *
	 * @param  array $m [description].
	 */
	public function do_shortcode_tag( $m ) {
		if ( '[' === $m[1] && ']' === $m[6] ) {
			return substr( $m[0], 1, -1 );
		}

		$tag  = $m[2];
		$attr = shortcode_parse_atts( $m[3] );

		$shortcodes = w4pl_get_shortcodes();
		$callback   = $shortcodes[ $tag ]['callback'];

		if ( isset( $m[5] ) ) {
			$content = $m[5];
		} else {
			$content = null;
		}

		if ( ! empty( $callback ) ) {
			return $m[1] . call_user_func( $callback, $attr, $content, $this ) . $m[6];
		} else {
			return $m[1] . $content . $m[6];
		}
	}

	/**
	 * Order ascending
	 *
	 * @param  mixed $a Var one.
	 * @param  mixed $b Var two.
	 * @return int      Number.
	 */
	public function cmp_asc( $a, $b ) {
		if ( $a === $b ) {
			return 0;
		}
		return ( $a < $b ) ? -1 : 1;
	}

	/**
	 * Order descending
	 *
	 * @param  mixed $a Var one.
	 * @param  mixed $b Var two.
	 * @return int      Number.
	 */
	public function cmp_desc( $a, $b ) {
		if ( $a === $b ) {
			return 0;
		}

		return ( $a > $b ) ? -1 : 1;
	}
}
