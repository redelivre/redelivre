<?php

/**
 * Addon Name: Gutenberg
 * Version: 1.0
 * Plugin URI:  https://premium.wpmudev.org/
 * Description: Gutenberg blocks for Forminator
 * Author: WPMU DEV
 * Author URI: http://premium.wpmudev.org
 */

define( 'FORMINATOR_ADDON_GUTENBERG_VERSION', '1.0' );

// Load Gutenberg module after Forminator loaded
add_action( 'init', array( 'Forminator_Gutenberg', 'init' ), 5 );

class Forminator_Gutenberg {
	/**
	 * @var self|null
	 */
	private static $_instance = null;

	/**
	 * Get Instance
	 *
	 * @since 1.0 Gutenberg Addon
	 * @return self|null
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Initialize addon
	 *
	 * @since 1.0 Gutenberg Addon
	 */
	public static function init() {
		// Load abstracts
		require_once dirname( __FILE__ ) . '/library/class-forminator-gfblock-abstract.php';

		// Load blocks
		self::load_blocks();
	}

	/**
	 * Automatically include blocks files
	 *
	 * @since 1.0 Gutenberg Addon
	 */
	public static function load_blocks() {
		// Load blocks automatically
		foreach ( glob( plugin_dir_path( __FILE__ ) . 'library/blocks/class-forminator-gfblock-*.php' ) as $file ) {
			require_once $file;
		}
	}

	/**
	 * Return Addon URL
	 *
	 * @since 1.0 Gutenberg Addon
	 *
	 * @return mixed
	 */
	public function get_plugin_url() {
		return trailingslashit( forminator_plugin_url() . 'addons/pro/gutenberg' );
	}

	/**
	 * Return Addon DIR
	 *
	 * @since 1.0 Gutenberg Addon
	 *
	 * @return mixed
	 */
	public function get_plugin_dir() {
		return trailingslashit( dirname( __FILE__ ) );
	}
}

/**
 * Instance of Gutenberb Addon
 *
 * @since 1.0 Gutenberg Addon
 *
 * @return Forminator_Gutenberg
 */
function forminator_gutenberg() {
	return Forminator_Gutenberg::get_instance();
}
