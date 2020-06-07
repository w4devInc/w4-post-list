<?php
/**
 * Posts related template tags
 *
 * @class W4PL_Post_Template_Tags
 * @package W4_Post_List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Meta query implementor class
 *
 * @class W4PL_Post_Template_Tags
 */
class W4PL_Post_Template_Tags {

	/**
	 * Constructor
	 */
	function __construct() {
		add_filter( 'w4pl/get_shortcodes', array( $this, 'get_shortcodes' ), 21 );
	}

	/**
	 * Register post shortcodes
	 *
	 * @param  array $shortcodes  All shortcodes array.
	 */
	public static function get_shortcodes( $shortcodes ) {
		$_shortcodes = array(
			'id'                   => array(
				'group'    => 'Post',
				'callback' => array( 'W4PL_Post_Template_Tags', 'post_id' ),
				'output'     => __( 'Post id', 'w4-post-list' ),
			),
			'ID'                   => array(
				'group'    => 'Post',
				'callback' => array( 'W4PL_Post_Template_Tags', 'post_id' ),
				'output'     => __( 'Post id', 'w4-post-list' ),
			),
			'post_id'              => array(
				'group'    => 'Post',
				'callback' => array( 'W4PL_Post_Template_Tags', 'post_id' ),
				'output'     => __( 'Post id', 'w4-post-list' ),
			),
			'post_type'            => array(
				'group'    => 'Post',
				'callback' => array( 'W4PL_Post_Template_Tags', 'post_type' ),
				'output'     => __( 'Post type', 'w4-post-list' ),
			),
			'post_type_label'      => array(
				'group'    => 'Post',
				'callback' => array( 'W4PL_Post_Template_Tags', 'post_type_label' ),
				'output'     => __( 'Post type label', 'w4-post-list' ),
			),
			'post_status'          => array(
				'group'    => 'Post',
				'callback' => array( 'W4PL_Post_Template_Tags', 'post_status' ),
				'output'     => __( 'Post status', 'w4-post-list' ),
			),
			'post_status_label'    => array(
				'group'    => 'Post',
				'callback' => array( 'W4PL_Post_Template_Tags', 'post_status_label' ),
				'output'     => __( 'Post status label', 'w4-post-list' ),
			),
			'post_number'          => array(
				'group'    => 'Post',
				'callback' => array( 'W4PL_Post_Template_Tags', 'post_number' ),
				'output'     => __( 'Post number, starting from 1', 'w4-post-list' ),
			),
			'post_permalink'       => array(
				'group'    => 'Post',
				'callback' => array( 'W4PL_Post_Template_Tags', 'post_permalink' ),
				'output'     => __( 'Post permalink', 'w4-post-list' ),
			),
			'post_class'           => array(
				'group'    => 'Post',
				'callback' => array( 'W4PL_Post_Template_Tags', 'post_class' ),
				'output'     => __( 'HTML classes of post', 'w4-post-list' ),
			),
			'post_title'           => array(
				'group'    => 'Post',
				'callback' => array( 'W4PL_Post_Template_Tags', 'post_title' ),
				'parameters' => array(
					'wordlimit' => array(
						'desc' => __( 'Limit number of words to display', 'w4-post-list' ),
					),
					'charlimit' => array(
						'desc' => __( 'Limit number of characters to display', 'w4-post-list' ),
					)
				)
			),
			'post_name'            => array(
				'group'    => 'Post',
				'callback' => array( 'W4PL_Post_Template_Tags', 'post_name' ),
				'output'     => __( 'Post name', 'w4-post-list' ),
			),
			'post_comment_url'     => array(
				'group'    => 'Post',
				'callback' => array( 'W4PL_Post_Template_Tags', 'post_comment_url' ),
				'output'     => __( 'Post comment form permalink', 'w4-post-list' ),
			),
			'post_comment_count'   => array(
				'group'    => 'Post',
				'callback' => array( 'W4PL_Post_Template_Tags', 'post_comment_count' ),
				'output'     => __( 'Number of approved comments', 'w4-post-list' ),
			),
			'post_the_date'        => array(
				'group'    => 'Post',
				'code'     => '[post_the_date format="' . get_option( 'date_format' ) . '" before="" after=""]',
				'callback' => array( 'W4PL_Post_Template_Tags', 'post_the_date' ),
				'output'     => __( 'Unique post date. Ignored on current item if previous post date and curent post date is same ( date formatted )', 'w4-post-list' ),
				'parameters' => array(
					'format' => array(
						'desc' => __( 'php datetime format', 'w4-post-list' )
					),
					'before' => array(
						'desc' => __( 'php datetime format', 'w4-post-list' )
					),
					'before' => array(
						'desc' => __( 'php datetime format', 'w4-post-list' )
					)
				),
			),
			'post_date'            => array(
				'group'    => 'Post',
				'code'     => '[post_date format="' . get_option( 'date_format' ) . '"]',
				'callback' => array( 'W4PL_Post_Template_Tags', 'post_date' ),
				'output'     => __( 'Post date ( date formatted )', 'w4-post-list' ),
				'parameters' => array(
					'format' => array(
						'desc' => __( 'php datetime format', 'w4-post-list' )
					)
				),
			),
			'post_time'            => array(
				'group'    => 'Post',
				'code'     => '[post_time format="' . get_option( 'time_format' ) . '"]',
				'callback' => array( 'W4PL_Post_Template_Tags', 'post_time' ),
				'output'     => __( 'Post date ( time formatted )', 'w4-post-list' ),
				'parameters' => array(
					'format' => array(
						'desc' => __( 'php datetime format', 'w4-post-list' )
					)
				),
			),
			'post_modified_date'   => array(
				'group'    => 'Post',
				'code'     => '[post_modified_date format="' . get_option( 'date_format' ) . '"]',
				'callback' => array( 'W4PL_Post_Template_Tags', 'post_modified_date' ),
				'output'     => __( 'Post modified date ( date formatted )', 'w4-post-list' ),
				'parameters' => array(
					'format' => array(
						'desc' => __( 'php datetime format', 'w4-post-list' )
					)
				),
			),
			'post_modified_time'   => array(
				'group'    => 'Post',
				'code'     => '[post_modified_time format="' . get_option( 'time_format' ) . '"]',
				'callback' => array( 'W4PL_Post_Template_Tags', 'post_modified_time' ),
				'output'     => __( 'Post modified date ( time formatted )', 'w4-post-list' ),
				'parameters' => array(
					'format' => array(
						'desc' => __( 'php datetime format', 'w4-post-list' )
					)
				),
			),
			'post_author_meta'     => array(
				'group'    => 'Post',
				'code'     => '[post_author_meta name=""]',
				'callback' => array( 'W4PL_Post_Template_Tags', 'post_author_meta' ),
				'output'     => __( 'Post author\'s meta', 'w4-post-list' ),
				'parameters' => array(
					'name' => array(
						'desc' => __( 'name of the meta information', 'w4-post-list' ),
						'choices' => array(
							'admin_color',
						    'aim',
						    'comment_shortcuts',
						    'description',
						    'display_name',
						    'first_name',
						    'ID',
						    'jabber',
						    'last_name',
						    'nickname',
						    'plugins_last_view',
						    'plugins_per_page',
						    'rich_editing',
						    'syntax_highlighting',
						    'user_activation_key',
						    'user_description',
						    'user_email',
						    'user_firstname',
						    'user_lastname',
						    'user_level',
						    'user_login',
						    'user_nicename',
						    'user_pass',
						    'user_registered',
						    'user_status',
						    'user_url',
						    'yim'
						)
					)
				),
			),
			'post_author_name'     => array(
				'group'    => 'Post',
				'callback' => array( 'W4PL_Post_Template_Tags', 'post_author_name' ),
				'output'     => __( 'Post author\'s name', 'w4-post-list' ),
			),
			'post_author_url'      => array(
				'group'    => 'Post',
				'callback' => array( 'W4PL_Post_Template_Tags', 'post_author_url' ),
				'output'     => __( 'Post author\'s link', 'w4-post-list' ),
			),
			'post_author_email'    => array(
				'group'    => 'Post',
				'callback' => array( 'W4PL_Post_Template_Tags', 'post_author_email' ),
				'output'     => __( 'Post author\'s email address', 'w4-post-list' ),
			),
			'post_author_avatar'   => array(
				'group'    => 'Post',
				'code'     => '[post_author_avatar size=""]',
				'callback' => array( 'W4PL_Post_Template_Tags', 'post_author_avatar' ),
				'output'     => __( 'Post author\'s avatar', 'w4-post-list' ),
				'parameters' => array(
					'size' => array(
						'desc' => __( 'avatar image size', 'w4-post-list' )
					)
				),
			),
			'post_excerpt'         => array(
				'group'    => 'Post',
				'code'     => '[post_excerpt wordlimit=""]',
				'callback' => array( 'W4PL_Post_Template_Tags', 'post_excerpt' ),
				'output'     => __( 'Post excerpt / short description', 'w4-post-list' ),
				'parameters' => array(
					'wordlimit' => array(
						'desc' => __( 'Limit number of words to display', 'w4-post-list' )
					),
					'strip_shortcodes' => array(
						'desc' => __( 'Remove shortcodes from exceprt text. use strip_shortcode="1" to enabled.', 'w4-post-list' )
					)
				),
			),
			'post_content'         => array(
				'group'    => 'Post',
				'callback' => array( 'W4PL_Post_Template_Tags', 'post_content' ),
				'output'     => __( 'Post content', 'w4-post-list' ),
			),
			'featured_image'       => array(
				'group'    => 'Post',
				'code'     => '[featured_image size="" return=""]',
				'callback' => array( 'W4PL_Post_Template_Tags', 'featured_image' ),
				'output'     => __( '( text or number ) based on the rerurn attribute & only if the post has a featured image assigned', 'w4-post-list' ),
				'parameters' => array(
					'output' => array(
						'desc' => __( '"src" - will return src of the image, "id" - will return id of the image, by default it will return image html', 'w4-post-list' ),
						'choices' => array(
							'id',
							'src',
							'html'
						)
					),
					'class' => array(
						'desc' => __( '( string ), class name for the image ( &lt;img /&gt; ) tag', 'w4-post-list' )
					),
					'size' => array(
						'desc' => __( '( string ), image size', 'w4-post-list' )
					),
					'width' => array(
						'desc' => __( '( number ), image width', 'w4-post-list' )
					),
					'height' => array(
						'desc' => __( '( number ), image height', 'w4-post-list' )
					),
					'placeholder' => array(
						'desc' => __( '( text ), default placeholder text if post doesnt have featured image', 'w4-post-list' )
					)
				),
			),
			'post_thumbnail'       => array(
				'group'    => 'Post',
				'code'     => '[post_thumbnail size="" return=""]',
				'callback' => array( 'W4PL_Post_Template_Tags', 'post_thumbnail' ),
				'output'     => __( '( text or number ) based on the rerurn attribute & only if the post has a featured image assigned', 'w4-post-list' ),
				'parameters' => array(
					'output' => array(
						'desc' => __( '"src" - will return src of the image, "id" - will return id of the image, by default it will return image html', 'w4-post-list' ),
						'choices' => array(
							'id',
							'src',
							'html'
						)
					),
					'class' => array(
						'desc' => __( '( string ), class name for the image ( &lt;img /&gt; ) tag', 'w4-post-list' )
					),
					'size' => array(
						'desc' => __( '( string ), thumbnail size', 'w4-post-list' )
					),
					'width' => array(
						'desc' => __( '( number ), thumbnail width', 'w4-post-list' )
					),
					'height' => array(
						'desc' => __( '( number ), thumbnail height', 'w4-post-list' )
					),
					'placeholder' => array(
						'desc' => __( '( text ), default placeholder text if post doesnt have thumbnail', 'w4-post-list' )
					)
				),
			),
			'post_image'           => array(
				'group'    => 'Post',
				'code'     => '[post_image use_fallback="1"]',
				'callback' => array( 'W4PL_Post_Template_Tags', 'post_image' ),
				'desc'     => '<strong>' . __( 'Output', 'w4-post-list' ) . '</strong>: <strong>first</strong> or <strong>last</strong> image source ( src="" ) from post content
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>position</strong> = ( first|last )
				<br /><strong>output</strong> = ( text|number ),
				<br />----"src" - will return src of the image,
				<br />----by default it will return image html
				<br /><strong>class</strong> = ( string ), class name for the image ( &lt;img /&gt; ) tag
				<br /><strong>width</strong> = ( number ), set image width attr ( image scaling, not resizing )
				<br /><strong>height</strong> = ( number ), set image height attr ( image scaling, not resizing )
				<br /><strong>use_fallback</strong> = ( true|false ), set 1 to use <code>[featured_image]</code> shortcode as fallback while post content dont have any images. ',
			),
			'post_meta'            => array(
				'group'    => 'Post',
				'code'     => '[post_meta key="" multiple="0"]',
				'callback' => array( 'W4PL_Post_Template_Tags', 'post_meta' ),
				'desc'     => '<strong>' . __( 'Output', 'w4-post-list' ) . '</strong>: post meta value. if return value is an array, it will be migrated to string by using explode function
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>key</strong> = ( text|number ), meta key name
				<br /><strong>sub_key</strong> = ( text|number ), if meta value is array|object, display a specific value by it\'s key
				<br /><strong>multiple</strong> = ( 0|1 ), display meta value at multiple occurence
				<br /><strong>sep</strong> = ( text ), separate array meta value into string',
			),
			'post_meta_date'       => array(
				'group'    => 'Post',
				'code'     => '[post_meta_date key=""]',
				'callback' => array( 'W4PL_Post_Template_Tags', 'post_meta_date' ),
				'desc'     => '<strong>' . __( 'Output', 'w4-post-list' ) . '</strong>: post meta value. if return value is an array, it will be migrated to string by using explode function
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>key</strong> = ( text|number ), meta key name',
			),
			'post_terms'           => array(
				'group'    => 'Post',
				'code'     => '[post_terms tax="category" sep=", "]',
				'callback' => array( 'W4PL_Post_Template_Tags', 'post_terms' ),
				'desc'     => '<strong>' . __( 'Output', 'w4-post-list' ) . '</strong>: post type terms. if return value is an array, it will be migrated to string by using explode function
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>tax</strong> = ( string ), taxonomy name
				<br /><strong>sep</strong> = ( string ), separate array meta value into string
				<br /><strong>return</strong> = ( name|slug ), return plain name or slug',
			),
			'attachment_thumbnail' => array(
				'group'    => 'Post',
				'code'     => '[attachment_thumbnail size=""]',
				'callback' => array( 'W4PL_Post_Template_Tags', 'attachment_thumbnail' ),
				'desc'     => '<strong>' . __( 'Output', 'w4-post-list' ) . '</strong>: if the post type is attachment, the attached file thumb is displayed.
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>id</strong> = ( string ), attachment id
				<br /><strong>meta_key</strong> = ( string ), retrieve attachment id from meta value
				<br /><strong>size</strong> = ( string ), image size
				<br /><strong>class</strong> = ( string ), class name for the image ( &lt;img /&gt; ) tag
				<br /><strong>width</strong> = ( number ), image width
				<br /><strong>height</strong> = ( number ), image height
				<br /><strong>return</strong> = ( text|number ),
				<br />----"src" - will return src of the attachment,
				<br />----"id" - will return id of the attachment,
				<br />----by default it will return image html
				',
			),
			'attachment_url'       => array(
				'group'    => 'Post',
				'callback' => array( 'W4PL_Post_Template_Tags', 'attachment_url' ),
				'output'     => __( ' if the post is an attachment, the attached image source is returned', 'w4-post-list' ),
			),

			'parent_permalink'     => array(
				'group'    => 'Post',
				'code'     => '[parent_permalink self=1]',
				'callback' => array( 'W4PL_Post_Template_Tags', 'parent_permalink' ),
				'output'     => __( 'if the post type is hierarchical, it\'s parent post permalink is returned', 'w4-post-list' ),
				'parameters' => array(
					'self' => array(
						'desc' => 'if no parent item exist, return the self permalink',
						'default' => '1'
					)
				),
			),

			'title'                => array(
				'group'    => 'Post',
				'callback' => array( 'W4PL_Post_Template_Tags', 'template_title' ),
				'output'     => __( 'title template', 'w4-post-list' ),
			),
			'meta'                 => array(
				'group'    => 'Post',
				'callback' => array( 'W4PL_Post_Template_Tags', 'template_meta' ),
				'output'     => __( 'meta template', 'w4-post-list' ),
			),
			'publish'              => array(
				'group'    => 'Post',
				'callback' => array( 'W4PL_Post_Template_Tags', 'template_date' ),
				'output'     => __( 'publish time template', 'w4-post-list' ),
			),
			'date'                 => array(
				'group'    => 'Post',
				'callback' => array( 'W4PL_Post_Template_Tags', 'template_date' ),
				'output'     => __( 'publish time template', 'w4-post-list' ),
			),
			'modified'             => array(
				'group'    => 'Post',
				'callback' => array( 'W4PL_Post_Template_Tags', 'template_modified' ),
				'output'     => __( 'modified time template', 'w4-post-list' ),
			),
			'author'               => array(
				'group'    => 'Post',
				'callback' => array( 'W4PL_Post_Template_Tags', 'template_author' ),
				'output'     => __( 'author template', 'w4-post-list' ),
			),
			'excerpt'              => array(
				'group'    => 'Post',
				'callback' => array( 'W4PL_Post_Template_Tags', 'template_excerpt' ),
				'output'     => __( 'excerpt template', 'w4-post-list' ),
			),
			'content'              => array(
				'group'    => 'Post',
				'callback' => array( 'W4PL_Post_Template_Tags', 'template_content' ),
				'output'     => __( 'content template', 'w4-post-list' ),
			),
			'more'                 => array(
				'group'    => 'Post',
				'code'     => '[more text="Continue Reading"]',
				'callback' => array( 'W4PL_Post_Template_Tags', 'template_more' ),
				'desc'     => '<strong>' . __( 'Output', 'w4-post-list' ) . '</strong>: more link template
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>text</strong> = ( string ), text to be displayed',
			),

			'group_id'             => array(
				'group'    => 'Group',
				'callback' => array( 'W4PL_Post_Template_Tags', 'post_group_id' ),
				'output'     => __( 'group name / title', 'w4-post-list' ),
			),
			'group_title'          => array(
				'group'    => 'Group',
				'callback' => array( 'W4PL_Post_Template_Tags', 'post_group_title' ),
				'output'     => __( 'group name / title', 'w4-post-list' ),
			),
			'group_url'            => array(
				'group'    => 'Group',
				'callback' => array( 'W4PL_Post_Template_Tags', 'post_group_url' ),
				'output'     => __( 'group page link', 'w4-post-list' ),
			),
		);

		return array_merge( $shortcodes, $_shortcodes );
	}

	/* Post Shortcode Callbacks */

	public static function post_id( $attr, $cont ) {
		return get_the_ID();
	}

	public static function post_type( $attr, $cont ) {
		return get_post_type();
	}
	public static function post_type_label( $attr, $cont ) {
		return get_post_type_object( get_post_type() )->labels->singular_name;
	}
	public static function post_status( $attr, $cont ) {
		return get_post_status();
	}
	public static function post_status_label( $attr, $cont ) {
		return get_post_status_object( get_post_status() )->label;
	}

	public static function post_number( $attr, $cont, $list ) {
		return (int) ( $list->posts_query->current_post + 1 );
	}
	public static function post_permalink( $attr, $cont ) {
		return get_permalink();
	}
	public static function post_class( $attr, $cont ) {
		return join( ' ', get_post_class() );
	}
	public static function post_title( $attr, $cont ) {
		 $return = get_the_title();
		if ( isset( $attr['wordlimit'] ) ) {
			$wordlimit = $attr['wordlimit'];
			$return    = wp_trim_words( $return, $wordlimit );
		} elseif ( isset( $attr['charlimit'] ) ) {
			$charlimit = $attr['charlimit'];
			$return    = substr( $return, 0, $charlimit );
		}
		return $return;
	}
	public static function post_name( $attr, $cont ) {
		global $post;
		return $post->post_name;
	}
	public static function post_comment_url( $attr, $cont ) {
		return get_permalink() . '#comments';
	}
	public static function post_comment_count( $attr, $cont ) {
		global $post;
		return (int) $post->comment_count;
	}

	public static function post_the_date( $attr, $cont ) {
		$format = $before = $after = '';
		if ( isset( $attr['format'] ) ) {
			$format = $attr['format'];
		}
		if ( isset( $attr['before'] ) ) {
			$before = $attr['before'];
		}
		if ( isset( $attr['after'] ) ) {
			$after = $attr['after'];
		}

		return the_date( $format, $before, $after, false );
	}
	public static function post_date( $attr, $cont ) {
		$format = get_option( 'date_format' );
		if ( isset( $attr['format'] ) ) {
			$format = $attr['format'];
		}
		return get_the_date( $format );
	}
	public static function post_time( $attr, $cont ) {
		$format = get_option( 'time_format' );
		if ( isset( $attr['format'] ) ) {
			$format = $attr['format'];
		}
		return get_the_time( $format );
	}
	public static function post_modified_date( $attr, $cont ) {
		 $format = get_option( 'date_format' );
		if ( isset( $attr['format'] ) ) {
			$format = $attr['format'];
		}
		return get_post_modified_time( $format );
	}
	public static function post_modified_time( $attr, $cont ) {
		 $format = get_option( 'time_format' );
		if ( isset( $attr['format'] ) ) {
			$format = $attr['format'];
		}
		return get_post_modified_time( $format );
	}
	public static function post_author_meta( $attr, $cont ) {
		if ( isset( $attr ) && ! is_array( $attr ) && is_string( $attr ) ) {
			$name = trim( $attr );
			$attr = array();
		} elseif ( isset( $attr['name'] ) ) {
			$name = $attr['name'];
		}
		if ( empty( $name ) || in_array( $name, array( 'pass', 'user_pass' ) ) ) {
			return;
		}

		return get_the_author_meta( $name, get_the_author_meta( 'ID' ) );
	}

	public static function post_author_name( $attr, $cont ) {
		return get_the_author_meta( 'display_name' );
	}
	public static function post_author_url( $attr, $cont ) {
		return get_author_posts_url( get_the_author_meta( 'ID' ) );
	}
	public static function post_author_email( $attr, $cont ) {
		return get_the_author_meta( 'user_email' );
	}
	public static function post_author_avatar( $attr, $cont ) {
		 $size = 32;
		if ( isset( $attr['size'] ) ) {
			$size = $attr['size'];
		}

		return get_avatar( get_the_author_meta( 'user_email' ), $size );
	}

	public static function post_excerpt( $attr, $cont ) {
		$post    = get_post();
		$excerpt = $post->post_excerpt;

		if ( '' == $excerpt ) {
			$excerpt = $post->post_content;
		}

		if ( isset( $attr['strip_shortcodes'] ) && '1' === $attr['strip_shortcodes'] ) {
			$excerpt = strip_shortcodes( $excerpt );
		}

		if ( isset( $attr['wordlimit'] ) ) {
			$wordlimit = (int) $attr['wordlimit'];
			$excerpt   = wp_trim_words( $excerpt, $wordlimit );
		}

		return $excerpt;
	}

	public static function post_content( $attr, $cont ) {
		global $post;
		// Post content without wrapper --
		$content = apply_filters( 'the_content', get_the_content() );
		$content = str_replace( ']]>', ']]&gt;', $content );
		return $content;
	}

	public static function featured_image( $attr, $cont ) {
		if ( isset( $attr['size'] ) ) {
			$size = $attr['size'];
		} elseif ( isset( $attr['width'] ) ) {
			if ( isset( $attr['height'] ) ) {
				$height = $attr['height'];
			} else {
				$height = 9999;
			}

			$size = array( $attr['width'], $height );
		} elseif ( isset( $attr['height'] ) ) {
			if ( isset( $attr['width'] ) ) {
				$width = $attr['width'];
			} else {
				$width = 9999;
			}
			$size = array( $width, $attr['height'] );
		} else {
			$size = 'post-thumbnail';
		}

		$post_id           = get_the_ID();
		$post_thumbnail_id = (int) get_post_thumbnail_id( $post_id );

		// fallback.
		if ( isset( $attr['return'] ) && ! isset( $attr['output'] ) ) {
			$attr['output'] = $attr['return'];
		}

		if ( isset( $attr['output'] ) && 'id' == $attr['output'] ) {
			return $post_thumbnail_id;
		} elseif ( isset( $attr['output'] ) && 'src' == $attr['output'] ) {
			$img = wp_get_attachment_image_src( $post_thumbnail_id, $size );
			return isset( $img[0] ) ? $img[0] : '';
		} elseif ( $post_thumbnail_id ) {
			return wp_get_attachment_image( $post_thumbnail_id, $size, false, $attr );
		} elseif ( ! empty( $attr['placeholder'] ) ) {
			return $attr['placeholder'];
		}

		return '';
	}

	public static function post_thumbnail( $attr, $cont ) {
		 return self::featured_image( $attr, $cont );
	}


	/**
	 * Display Image From Post Content
	 *
	 * @since 1.9.1
	 */
	public static function post_image( $attr, $cont ) {
		 global $post;

		$return = '';
		if ( ! isset( $post ) || ! isset( $post->post_content ) || empty( $post->post_content ) ) {
			return $return; }

		$position = '';
		if ( isset( $attr['position'] ) ) {
			$position = $attr['position'];
		}

		preg_match_all( "/<img[^>]*src\s*=\s*[\'\"]([+:%\/\?~=&;\\\(\),._a-zA-Z0-9-]*)[\'\"]?/i", $post->post_content, $images, PREG_SET_ORDER );

		if ( ! empty( $images ) ) {
			$image = $position == 'last' ? array_pop( $images ) : array_shift( $images );

			if ( ! isset( $image['1'] ) || empty( $image['1'] ) ) {
				return $return;
			}

			$attrs = array( 'src' => $image['1'] );
			foreach ( array( 'width', 'height', 'class' ) as $attribute ) {
				if ( isset( $attr[ $attribute ] ) ) {
					$attrs[ $attribute ] = $attr[ $attribute ];
				}
			}

			$return = rtrim( '<img' );
			foreach ( $attrs as $name => $value ) {
				$return .= " $name=" . '"' . $value . '"';
			}
			$return .= ' />';
		}

		// if no images were found & use_fallback is set to true( bool )
		elseif ( isset( $attr['use_fallback'] ) && ! empty( $attr['use_fallback'] ) ) {
			// use post thumbnail as fallback, $attr is already similar for both methods
			$return = self::post_thumbnail( $attr, $cont );

			// @ attachment_thumbnail
			if ( empty( $return ) ) {
				$return = self::attachment_thumbnail( $attr, $cont );
			}
		}

		return $return;
	}


	public static function post_meta( $attr, $cont ) {
		if ( isset( $attr ) && ! is_array( $attr ) && is_string( $attr ) ) {
			$meta_key = trim( $attr );
			$attr     = array();
		}

		if ( isset( $attr['key'] ) ) {
			$meta_key = $attr['key'];
		} elseif ( isset( $attr['meta_key'] ) ) {
			$meta_key = $attr['meta_key'];
		}

		if ( empty( $meta_key ) ) {
			return;
		}

		$single = ! ( isset( $attr ) && is_array( $attr ) && array_key_exists( 'multiple', $attr ) ? (bool) $attr['multiple'] : false );

		$sep = ', ';
		if ( isset( $attr['sep'] ) ) {
			$sep = $attr['sep'];
		}

		$meta_value = get_post_meta( get_the_ID(), $meta_key, $single );

		// end the game here if the value is string
		if ( ! is_object( $meta_value ) && ! is_array( $meta_value ) ) {
			return $meta_value; }

		$return = '';
		if ( is_object( $meta_value ) ) {
			$meta_value = get_object_vars( $meta_value ); }

		if ( is_array( $meta_value ) && ! empty( $meta_value ) ) {
			// when meta value is serialized array, return specific array value by using subkey
			if ( isset( $attr['sub_key'] ) && ! empty( $attr['sub_key'] ) ) {
				if ( array_key_exists( $attr['sub_key'], $meta_value ) ) {
					return $meta_value[ $attr['sub_key'] ];
				}
			} else {
				$values = array();
				foreach ( $meta_value as $r => $d ) {
					if ( ! is_array( $d ) ) {
						$values[] = $d; }
				}

				if ( $values ) {
					return implode( $sep, $values ); }
			}
		}

		return '';
	}

	public static function post_meta_date( $attr, $content ) {
		if ( isset( $attr['key'] ) ) {
			$meta_key = $attr['key']; } elseif ( isset( $attr['meta_key'] ) ) {
			$meta_key = $attr['meta_key']; }
			if ( ! $meta_key ) {
				return; }

			$format     = isset( $attr['format'] ) ? $attr['format'] : 'Y-m-d';
			$meta_value = get_post_meta( get_the_ID(), $meta_key, true );

			return ! empty( $meta_value ) ? mysql2date( $format, $meta_value ) : '';
	}

	public static function post_terms( $attr, $cont ) {
		if ( isset( $attr['tax'] ) ) {
			$taxonomy = $attr['tax'];
		} elseif ( isset( $attr['taxonomy'] ) ) {
			$taxonomy = $attr['taxonomy'];
		}

		if ( ! isset( $taxonomy ) || ! taxonomy_exists( $taxonomy ) ) {
			return;
		}

		$sep = isset( $attr['sep'] ) ? $attr['sep'] : ', ';

		// New code
		if ( isset( $attr['output'] ) && in_array( $attr['output'], array( 'name', 'slug' ) ) ) {
			$terms = get_the_terms( get_the_ID(), $taxonomy );
			$names = wp_list_pluck( $terms, $attr['output'] );
			return implode( $sep, $names );
		} else {
			return get_the_term_list( get_the_ID(), $taxonomy, '', $sep );
		}
	}


	// Attachment
	public static function attachment_thumbnail( $attr, $cont ) {
		if ( isset( $attr['size'] ) ) {
			$size = $attr['size'];
		} elseif ( isset( $attr['width'] ) ) {
			if ( isset( $attr['height'] ) ) {
				$height = $attr['height'];
			} else {
				$height = 9999;
			}
			$size = array( $attr['width'], $height );
		} elseif ( isset( $attr['height'] ) ) {
			if ( isset( $attr['width'] ) ) {
				$width = $attr['width'];
			} else {
				$width = 9999;
			}
			$size = array( $width, $attr['height'] );
		} else {
			$size = 'post-thumbnail';
		}

		if ( isset( $attr['id'] ) ) {
			$attachment_id = (int) $attr['id'];
		} elseif ( isset( $attr['meta_key'] ) ) {
			$attachment_id = get_post_meta( get_the_ID(), $attr['meta_key'], true );
		} else {
			$attachment_id = get_the_ID();
		}

		if ( 'attachment' != get_post_type( $attachment_id ) ) {
			return '';
		}

		// if attachment is an image, then we have something more to return
		if ( wp_attachment_is_image( $attachment_id ) ) {
			if ( isset( $attr['output'] ) && 'id' == $attr['output'] ) {
				return $attachment_id;
			} elseif ( isset( $attr['output'] ) && 'src' == $attr['output'] ) {
				$img = wp_get_attachment_image_src( $attachment_id, $size );
				return isset( $img[0] ) ? $img[0] : '';
			} elseif ( $attachment_id ) {
				return wp_get_attachment_image( $attachment_id, $size, false, $attr );
			}

			return '';
		}

		$icon = true;
		if ( $attachment_id ) {
			$html = wp_get_attachment_image( $attachment_id, $size, $icon, $attr );
		} else {
			$html = '';
		}

		return $html;
	}
	public static function attachment_url( $attr, $cont ) {
		if ( isset( $attr['id'] ) ) {
			$post_id = (int) $attr['id'];
		} else {
			$post_id = get_the_ID();
		}

		if ( 'attachment' != get_post_type( $post_id ) ) {
			return '';
		}

		return wp_get_attachment_url( $post_id );
	}

	// Parent
	public static function parent_permalink( $attr, $cont ) {
		$post   = get_post();
		$parent = ( $post->post_parent > 0 && $post->post_parent != $post->ID ) ? get_post( $post->post_parent ) : false;
		if ( $parent ) {
			return get_permalink( $parent );
		} elseif ( isset( $attr['self'] ) && $attr['self'] ) {
			return get_permalink( $post );
		} else {
			return '#';
		}
	}


	// Tempate
	public static function template_title( $attr, $cont ) {
		return sprintf(
			'<a class="post_title w4pl_post_title" href="%1$s" title="View %2$s">%3$s</a>',
			get_permalink(),
			the_title_attribute( array( 'echo' => false ) ),
			get_the_title()
		);
	}

	public static function template_meta( $attr, $cont ) {
		return sprintf(
			__( 'Posted on', 'w4-post-list' ) . ': <abbr class="published post-date" title="%1$s">%2$s</abbr> <span class="post_author">' . __( 'by', 'w4-post-list' ) . ' %3$s</span>',
			get_the_time( get_option( 'time_format' ) ),
			get_the_time( get_option( 'date_format' ) ),
			get_the_author()
		);
	}
	public static function template_date( $attr, $cont ) {
		return sprintf(
			'<abbr class="published post-date" title="%1$s"><strong>' . __( 'Published', 'w4-post-list' ) . '</strong>: %2$s</abbr>',
			get_the_time( get_option( 'time_format' ) ),
			get_the_time( get_option( 'date_format' ) )
		);
	}
	public static function template_modified( $attr, $cont ) {
		return sprintf(
			'<abbr class="modified post-modified" title="%1$s"><strong>' . __( 'Updated', 'w4-post-list' ) . '</strong>: %2$s</abbr>',
			get_post_modified_time( get_option( 'time_format' ) ),
			get_post_modified_time( get_option( 'date_format' ) )
		);
	}
	public static function template_author( $attr, $cont ) {
		return sprintf(
			'<a href="%1$s" title="View all posts by %2$s" rel="author">%2$s</a>',
			get_author_posts_url( get_the_author_meta( 'ID' ) ),
			get_the_author()
		);
	}
	public static function template_excerpt( $attr, $cont, $list ) {
		return sprintf(
			'<div class="post-excerpt">%s</div>',
			self::post_excerpt( $attr, $cont, $list )
		);
	}
	public static function template_content( $attr, $cont ) {
		return sprintf(
			'<div class="post-excerpt">%s</div>',
			$this->post_content( $attr, $cont )
		);
	}
	public static function template_more( $attr, $cont ) {
		$read_more = ! empty( $attr['text'] ) ? $attr['text'] : __( 'Continue reading &raquo;', 'w4-post-list' );
		return sprintf(
			'<a class="read_more" href="%1$s" title="%3$s %2$s">%3$s</a>',
			get_permalink(),
			get_the_title(),
			esc_attr( $read_more )
		);
	}


	public static function post_group_id( $attr, $cont, $list ) {
		return isset( $list->current_group ) ? $list->current_group['id'] : 0;
	}
	public static function post_group_title( $attr, $cont, $list ) {
		return isset( $list->current_group ) ? $list->current_group['title'] : '';
	}
	public static function post_group_url( $attr, $cont, $list ) {
		return isset( $list->current_group ) ? $list->current_group['url'] : '';
	}
}
