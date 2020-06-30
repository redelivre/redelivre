<?php

require_once dirname( __FILE__ ) . '/class-addon-default-holder.php';

/**
 * Class Forminator_Addon_Autoload
 * Handling Autoloader
 *
 * @since 1.1
 */
class Forminator_Addon_Autoload {

	/**
	 * Pro addons list
	 *
	 * @since 1.1
	 * @var array
	 */
	protected $pro_addons = array();

	/**
	 * Forminator_Addon_Autoload constructor.
	 * Intialize with custom pro addons, or pass empty array otherwise
	 *
	 * @since 1.1
	 *
	 * @param array $pro_addons
	 */
	public function __construct( $pro_addons = array() ) {
		$this->pro_addons = $pro_addons;
	}

	/**
	 * Load Addons which lies in `addons/pro` directory
	 * And load placeholder for pro addons that are defined but not available in the pro directory
	 *
	 * @since 1.1
	 */
	public function load() {
		$pro_addons = $this->pro_addons;

		$pro_addons_dir = dirname( __FILE__ ) . '/pro/';

		/**
		 * Filter path of Pro addons directory located
		 *
		 * @since 1.1
		 *
		 * @param string $pro_addons_dir current dir path of pro addons
		 */
		$pro_addons_dir = apply_filters( 'forminator_addon_pro_addons_dir', $pro_addons_dir );

		// All of Forminator Official Addons must be registered here with fallback array
		// fallback array will be used to display pro addons on the list of addons, without files on `/pro` being available
		if ( empty( $pro_addons ) ) {
			$pro_addons = forminator_get_pro_addon_list();
		}
		// Load Available Pro Addon
		$directory = new DirectoryIterator( $pro_addons_dir );
		foreach ( $directory as $d ) {
			if ( $d->isDot() || $d->isFile() ) {
				continue;
			}
			// take directory name as addon name
			$addon_name = $d->getBasename();

			// new Addon !
			// valid addon is when addon have `addon_name.php` inside its addon directory
			$addon_initiator = $d->getPathname() . DIRECTORY_SEPARATOR . $addon_name . '.php';
			if ( ! file_exists( $addon_initiator ) ) {
				continue;
			}
			/** @noinspection PhpIncludeInspection */
			include_once $addon_initiator;
		}

		// Load unavailable Pro Addons
		$pro_slugs        = Forminator_Addon_Loader::get_instance()->get_addons()->get_slugs();
		$unavailable_pros = array_diff( array_keys( $pro_addons ), $pro_slugs );

		foreach ( $unavailable_pros as $unavailable_pro ) {
			if ( array_key_exists( $unavailable_pro, $pro_addons ) ) {
				$addon                                   = new Forminator_Addon_Default_Holder();
				$pro_addons[ $unavailable_pro ]['_slug'] = $unavailable_pro;

				$addon->from_array( $pro_addons[ $unavailable_pro ] );
				Forminator_Addon_Loader::get_instance()->register( $addon );
			}
		}
	}
}
