<?php
/**
 * Starter template registry and copy-on-select application.
 *
 * Starters are ready-made template/CSS/option bundles a user picks in the
 * list editor. Unlike the legacy preset system (W4PL_Helper_Presets, kept
 * untouched for lists that already store a `preset` key), applying a
 * starter COPIES its content into the visible, editable Template and Style
 * fields — one-shot, in the editor only, never at render time, and the
 * selection itself is never persisted.
 *
 * Third parties can add starters via the `w4pl/starter_templates` filter.
 *
 * @class W4PL_Starter_Templates
 * @package W4_Post_List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Starter template registry.
 */
class W4PL_Starter_Templates {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Closes the pick-then-publish race: a starter still pending in the
		// posted options (form refresh not completed) is applied on save.
		// Also guarantees the one-shot key itself is never persisted.
		add_filter( 'w4pl/pre_save_options', array( $this, 'apply_on_save' ), 5 );
	}

	/**
	 * Apply any pending starter during save.
	 *
	 * @param  array $options Options about to be persisted.
	 * @return array
	 */
	public function apply_on_save( $options ) {
		$options = self::apply( $options );

		if ( is_array( $options ) ) {
			unset( $options['_starter_applied'] );
		}

		return $options;
	}

	/**
	 * Option keys a starter is allowed to set when applied.
	 *
	 * @var array
	 */
	private static $allowed_option_keys = array(
		'groupby',
		'posts_per_page',
		'limit',
		'orderby',
		'order',
		'terms_taxonomy',
		'terms_orderby',
		'terms_order',
		'users_orderby',
		'users_order',
	);

	/**
	 * All registered starters, shape-normalized.
	 *
	 * Filter entries are parsed against a default shape; entries without a
	 * usable list_type or template are dropped so a malformed third-party
	 * starter can never emit warnings or write null into list options.
	 *
	 * @return array id => { label, description, list_type, template, css, options, thumbnail }
	 */
	public static function get_registry() {
		$raw = apply_filters( 'w4pl/starter_templates', self::core_starters() );

		$registry = array();
		$defaults = array(
			'label'       => '',
			'description' => '',
			'list_type'   => '',
			'template'    => '',
			'css'         => '',
			'options'     => array(),
			'thumbnail'   => '',
		);

		foreach ( (array) $raw as $id => $starter ) {
			if ( ! is_array( $starter ) ) {
				continue;
			}

			$starter = wp_parse_args( $starter, $defaults );

			if ( ! is_string( $starter['list_type'] ) || '' === $starter['list_type'] || ! is_string( $starter['template'] ) || '' === $starter['template'] ) {
				continue;
			}

			if ( ! is_string( $starter['css'] ) ) {
				$starter['css'] = '';
			}
			if ( ! is_array( $starter['options'] ) ) {
				$starter['options'] = array();
			}

			$registry[ $id ] = $starter;
		}

		return $registry;
	}

	/**
	 * One starter by id.
	 *
	 * @param  string $id Starter id.
	 * @return array|null
	 */
	public static function get( $id ) {
		$registry = self::get_registry();

		if ( isset( $registry[ $id ] ) ) {
			return $registry[ $id ];
		}

		return null;
	}

	/**
	 * Select-field choices for a list type.
	 *
	 * @param  string $list_type Current list type.
	 * @return array
	 */
	public static function options_for_select( $list_type ) {
		$choices = array(
			'' => __( '&mdash; Start from a template &mdash;', 'w4-post-list' ),
		);

		foreach ( self::get_registry() as $id => $starter ) {
			if ( $starter['list_type'] === $list_type ) {
				$choices[ $id ] = $starter['label'];
			}
		}

		return $choices;
	}

	/**
	 * Apply a picked starter to editor options (copy-on-select).
	 *
	 * Populates template/css and the starter's related query options, then
	 * clears the selection so it is a one-shot action. Intentionally
	 * overwrites existing template/css (maintainer decision).
	 *
	 * @param  array $options Editor options, possibly holding starter_template.
	 * @return array
	 */
	public static function apply( $options ) {
		if ( ! is_array( $options ) || ! isset( $options['starter_template'] ) ) {
			return $options;
		}

		$starter_id = $options['starter_template'];

		// Always consume the one-shot key, picked or not.
		unset( $options['starter_template'] );

		// Only a plain non-empty string can name a starter; anything else
		// (crafted array input, empty selection) is a no-op.
		if ( ! is_string( $starter_id ) || '' === $starter_id ) {
			return $options;
		}

		$starter = self::get( $starter_id );

		if ( ! $starter ) {
			return $options;
		}

		if ( isset( $options['list_type'] ) && $options['list_type'] !== $starter['list_type'] ) {
			return $options;
		}

		$options['template'] = $starter['template'];
		$options['css']      = $starter['css'];

		if ( ! empty( $starter['options'] ) && is_array( $starter['options'] ) ) {
			foreach ( $starter['options'] as $key => $value ) {
				if ( in_array( $key, self::$allowed_option_keys, true ) ) {
					$options[ $key ] = $value;
				}
			}
		}

		if ( class_exists( 'W4PL_Stats' ) ) {
			W4PL_Stats::increment( 'starter_applied' );
		}

		// Editor-only marker so the re-rendered form can confirm the apply
		// and reveal the Template section; stripped before any save.
		$options['_starter_applied'] = $starter_id;

		return $options;
	}

	/**
	 * Built-in starter library.
	 *
	 * @return array
	 */
	private static function core_starters() {
		$starters = array();

		// Registry entries are integrated from data/starter-templates.php to
		// keep the long template/css strings out of class logic.
		$data_file = W4PL_DIR . '/includes/data/starter-templates.php';
		if ( file_exists( $data_file ) ) {
			$starters = include $data_file;
		}

		if ( ! is_array( $starters ) ) {
			$starters = array();
		}

		return $starters;
	}
}
