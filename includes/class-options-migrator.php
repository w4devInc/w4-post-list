<?php
/**
 * Options schema versioning and lazy migration.
 *
 * Every list's `_w4pl` options array carries an `options_version`. Options
 * are migrated on read (priority 1 on `w4pl/pre_get_options`, before any
 * helper touches them) and the migrated shape is persisted only when the
 * list is next saved (`w4pl/pre_save_options`). Existing lists without the
 * key are treated as version 0 and must keep rendering byte-identically —
 * the snapshot suite in tests/ListRenderingTest.php enforces this.
 *
 * @class W4PL_Options_Migrator
 * @package W4_Post_List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lazy migration of stored list options between schema versions.
 */
class W4PL_Options_Migrator {

	/**
	 * Current schema version of the _w4pl options array.
	 */
	const OPTIONS_VERSION = 1;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'w4pl/pre_get_options', array( $this, 'migrate' ), 1 );
		add_filter( 'w4pl/pre_save_options', array( $this, 'stamp_version' ), 99 );
	}

	/**
	 * Migrate options read from storage up to the current schema version.
	 *
	 * Runs in memory on every read; nothing is written back here.
	 *
	 * @param  array $options List options as stored.
	 * @return array
	 */
	public function migrate( $options ) {
		if ( ! is_array( $options ) ) {
			return $options;
		}

		$version = 0;
		if ( isset( $options['options_version'] ) ) {
			$version = (int) $options['options_version'];
		}

		if ( $version >= self::OPTIONS_VERSION ) {
			return $options;
		}

		// One guarded block per schema step, e.g.:
		// if ( $version < 2 ) { ...transform to shape 2... }
		//
		// Version 1 only introduces the version key itself. The historical
		// 1.6.7 template_loop shim in W4PL_List_Helper::pre_get_options()
		// intentionally stays in place until a schema step absorbs it.

		$options['options_version'] = self::OPTIONS_VERSION;

		return $options;
	}

	/**
	 * Stamp the current schema version into options being saved.
	 *
	 * @param  array $options List options about to be persisted.
	 * @return array
	 */
	public function stamp_version( $options ) {
		if ( is_array( $options ) ) {
			$options['options_version'] = self::OPTIONS_VERSION;
		}

		return $options;
	}
}
