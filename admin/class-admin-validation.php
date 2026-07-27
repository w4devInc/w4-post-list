<?php
/**
 * Save-time validation: coerce obviously wrong values, warn about template
 * problems, never reject a save.
 *
 * @class W4PL_Admin_Validation
 * @package W4_Post_List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validates list options as they are saved and surfaces warnings.
 */
class W4PL_Admin_Validation {

	/**
	 * Loop tag required per list type (top-level section).
	 *
	 * @var array
	 */
	private static $required_loop_tags = array(
		'posts'       => 'posts',
		'terms'       => 'terms',
		'users'       => 'users',
		'terms.posts' => 'terms',
		'users.posts' => 'users',
	);

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'w4pl/pre_save_options', array( $this, 'validate_on_save' ), 90 );
		add_action( 'admin_notices', array( $this, 'show_warnings' ) );
	}

	/**
	 * Coerce numeric fields and collect warnings. Warn-only by design:
	 * the save always proceeds so no user input is ever lost.
	 *
	 * @param  array $options Options about to be persisted.
	 * @return array
	 */
	public function validate_on_save( $options ) {
		if ( ! is_array( $options ) ) {
			return $options;
		}

		$warnings = array();

		foreach ( array( 'posts_per_page', 'limit', 'offset', 'terms_limit', 'terms_offset', 'terms_max', 'users_limit', 'users_offset', 'users_max' ) as $key ) {
			if ( ! isset( $options[ $key ] ) || '' === $options[ $key ] || is_numeric( $options[ $key ] ) ) {
				continue;
			}

			$coerced = self::coerce_numeric( $options[ $key ] );

			$warnings[] = sprintf(
				/* translators: 1: field name, 2: entered value, 3: value used instead */
				__( '"%1$s" expects a number; "%2$s" was read as %3$s.', 'w4-post-list' ),
				$key,
				$options[ $key ],
				( '' === $coerced ) ? __( 'empty', 'w4-post-list' ) : $coerced
			);

			$options[ $key ] = $coerced;
		}

		if ( isset( $options['orderby_meta_key'] ) && is_string( $options['orderby_meta_key'] ) ) {
			$options['orderby_meta_key'] = sanitize_text_field( $options['orderby_meta_key'] );
		}

		$warnings = array_merge( $warnings, self::template_warnings( $options ) );

		if ( $warnings && ! empty( $options['id'] ) ) {
			set_transient( 'w4pl_save_warnings_' . (int) $options['id'], $warnings, 120 );
		}

		return $options;
	}

	/**
	 * Leading digits win ("10 posts" -> 10); pure garbage becomes empty
	 * (falls back to the field's default behavior).
	 *
	 * @param  mixed $value Raw value.
	 * @return string
	 */
	public static function coerce_numeric( $value ) {
		if ( ! is_scalar( $value ) ) {
			return '';
		}

		$int = (int) $value;

		if ( 0 === $int && ! preg_match( '/^\s*[0-9]/', (string) $value ) ) {
			return '';
		}

		return (string) $int;
	}

	/**
	 * Unknown-tag and missing-loop-tag warnings for a template.
	 *
	 * @param  array $options List options.
	 * @return array
	 */
	public static function template_warnings( $options ) {
		$warnings = array();

		if ( empty( $options['template'] ) || ! is_string( $options['template'] ) ) {
			return $warnings;
		}

		$registered = array_keys( w4pl_get_shortcodes() );

		preg_match_all( '/\[\/?([a-z_][a-z0-9_]*)/i', $options['template'], $m );
		$used    = array_unique( $m[1] );
		$unknown = array_diff( $used, $registered );

		foreach ( $unknown as $tag ) {
			$suggestion = self::closest_tag( $tag, $registered );

			if ( $suggestion ) {
				$warnings[] = sprintf(
					/* translators: 1: unknown tag, 2: suggested tag */
					__( 'Template tag [%1$s] is not registered and will print as plain text — did you mean [%2$s]?', 'w4-post-list' ),
					$tag,
					$suggestion
				);
			} else {
				$warnings[] = sprintf(
					/* translators: %s: unknown tag */
					__( 'Template tag [%s] is not registered and will print as plain text.', 'w4-post-list' ),
					$tag
				);
			}
		}

		if ( isset( $options['list_type'], self::$required_loop_tags[ $options['list_type'] ] ) ) {
			$loop = self::$required_loop_tags[ $options['list_type'] ];

			if ( false === strpos( $options['template'], '[' . $loop . ']' ) ) {
				$warnings[] = sprintf(
					/* translators: 1: loop tag, 2: list type label */
					__( 'The template has no [%1$s]…[/%1$s] loop, so a "%2$s" list will render nothing. Add the loop or pick a starter template.', 'w4-post-list' ),
					$loop,
					$options['list_type']
				);
			}
		}

		return $warnings;
	}

	/**
	 * Closest registered tag by edit distance, or empty string.
	 *
	 * @param  string $tag        Unknown tag.
	 * @param  array  $registered Registered tag names.
	 * @return string
	 */
	public static function closest_tag( $tag, $registered ) {
		$best      = '';
		$best_dist = 4; // Suggest only near-misses.

		foreach ( $registered as $candidate ) {
			$dist = levenshtein( strtolower( $tag ), strtolower( $candidate ) );

			if ( $dist < $best_dist ) {
				$best      = $candidate;
				$best_dist = $dist;
			}
		}

		return $best;
	}

	/**
	 * Surface stored warnings on the editor screen after the redirect.
	 */
	public function show_warnings() {
		global $post;

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || 'w4pl' !== $screen->id || ! $post instanceof WP_Post ) {
			return;
		}

		$warnings = get_transient( 'w4pl_save_warnings_' . $post->ID );
		if ( ! $warnings ) {
			return;
		}

		delete_transient( 'w4pl_save_warnings_' . $post->ID );
		?>
		<div class="notice notice-warning is-dismissible">
			<p><strong><?php esc_html_e( 'Your list was saved, with warnings:', 'w4-post-list' ); ?></strong></p>
			<ul style="list-style:disc;padding-left:20px;">
				<?php foreach ( $warnings as $warning ) : ?>
				<li><?php echo esc_html( $warning ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}
}
