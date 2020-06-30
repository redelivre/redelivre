<?php
/**
 * Class Forminator_Protection
 * Loas all protection modules if vailable
 */
class Forminator_Protection {

	/**
	 * Plugin instance
	 *
	 * @var null
	 */
	private static $instance = null;

	/**
	 * Return the plugin instance
	 *
	 * @since 1.0
	 * @return Forminator_Protection
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * Main constructor
	 *
	 * @since 1.0
	 */
	public function __construct() {
		$this->include_in_built();
		$this->init_modules();
	}

	/**
	 * Load our in-built spam protectors
	 *
	 * @since 1.0
	 */
	private function include_in_built() {
		/* @noinspection PhpIncludeInspection */
		include_once forminator_plugin_dir() . 'library/protection/class-akismet.php';
	}

	/**
	 * Initialise spam modules
	 *
	 * @since 1.0
	 */
	public function init_modules() {
		Forminator_Akismet::get_instance();
		do_action( 'fominator_init_spam_modules' );
	}
}
