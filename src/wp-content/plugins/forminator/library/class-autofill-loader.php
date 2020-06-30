<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Autofill_Loader
 *
 * @since 1.0.5
 */
class Forminator_Autofill_Loader {

	/**
	 * Instance
	 *
	 * @since 1.0.5
	 * @var self|null
	 */
	private static $_instance = null;


	/**
	 * SEMAPHORE of inited_providers, so it wont inited more than once per app load
	 *
	 * @since 1.0.5
	 * @var array
	 */
	private static $inited_providers = array();


	/**
	 * In case we want disable addons that have bad pattern or behaviour or still in pipeline
	 *
	 * @since   1.0.5
	 * @example {['BANNED_PROVIDER_SLUG','BANNED_PROVIDER_SLUG_2']}
	 *
	 * @var array
	 */
	private $_banned_providers
		= array(
			'wp_post',
		);


	/**
	 * Autofill Providers Container
	 *
	 * @since   1.0.5
	 * @example array(`slug` => INSTANCE);
	 * @var Forminator_Autofill_Provider_Abstract[]
	 */
	private $autofill_providers = array();

	/**
	 * Autofill Providers Container
	 *
	 * @since   1.0.5
	 * @example array(`slug` => PROVIDER_NAME);
	 * @var array
	 */
	private $autofill_providers_groups = array();


	/**
	 * Autofill Providers Flatten Container
	 *
	 * @since   1.0.5
	 * @example array(
	 *          `slug`.`attribute` => 'translated string'
	 *          )
	 * @var array
	 */
	private $autofill_providers_names = array();

	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Register Autofill Provider Class
	 *
	 * @since 1.0.5
	 *
	 * @param $class_name
	 *
	 * @return bool
	 */
	final public function register( $class_name ) {
		if ( ! class_exists( $class_name ) ) {
			return false;
		}
		if ( ! is_callable( array( $class_name, 'get_instance' ) ) ) {
			return false;
		}

		$autofill_instance = call_user_func( array( $class_name, 'get_instance' ) );

		if ( ! $autofill_instance instanceof Forminator_Autofill_Provider_Abstract ) {
			return false;
		}

		if ( in_array( $autofill_instance->get_slug(), $this->_banned_providers, true ) ) {
			// debug purpose only
//			error_log( 'Autofill Provider for ' . $autofill_instance->get_slug() . ' is banned' );
			return false;
		}

		$this->autofill_providers[ $autofill_instance->get_slug() ]        = $autofill_instance;
		$this->autofill_providers_groups[ $autofill_instance->get_slug() ] = $autofill_instance->get_name();

		return true;
	}

	/**
	 * Build Provider names
	 *
	 * @since   1.0.5
	 *
	 * @example {
	 * 'simple'.'simple_text' => 'Simple Text',
	 * 'PROVIDER_SLUG'.'ATTRIBUTE_SLUG' => 'ATTRIBUTE NICE NAME',
	 * }
	 *
	 */
	private function try_build_autofill_providers_names() {
		$this->autofill_providers_names = array();
		foreach ( $this->autofill_providers as $autofill_instance ) {
			/**@var Forminator_Autofill_Provider_Abstract $autofill_instance */
			$attributes_map = $autofill_instance->get_attributes_map();
			if ( empty( $attributes_map ) ) {
				continue;
			}
			foreach ( $attributes_map as $attribute => $mapper ) {
				$name = $autofill_instance->get_slug() . '.' . $attribute;

				//Dedupe
				if ( isset( $this->autofill_providers_names[ $name ] ) ) {
					continue;
				}
				$this->autofill_providers_names[ $name ] = $mapper['name'];
			}
		}
	}

	/**
	 * Get Providers names, build if its empty
	 *
	 * @since 1.0.5
	 * @return array
	 */
	public function get_autofill_providers_names() {
		if ( empty( $this->autofill_providers_names ) ) {
			$this->try_build_autofill_providers_names();
		}

		return $this->autofill_providers_names;
	}

	/**
	 * Get autofill providers as grouped array
	 *
	 * @since 1.0.5
	 *
	 * @example
	 * {
	 *  `SLUG` => [
	 *                  'name' => `NAME`,
	 *                  'attributes' => [
	 *                      [`SLUG.ATTRIBUTE` => [name : TRANSLATE_ATTRIBUTE]`,
	 *                      [`SLUG.ATTRIBUTE` => [name : TRANSLATE_ATTRIBUTE]`,
	 *                  ]
	 *              ]
	 * ...}
	 *
	 * @param $slug_attributes
	 *
	 * @return array
	 */
	public function get_grouped_autofill_providers( $slug_attributes ) {
		$this->get_autofill_providers_names();

		$grouped_autofill_providers = array();


		foreach ( $slug_attributes as $slug_attribute ) {
			$slug_attribute_parts = explode( '.', $slug_attribute );

			if ( ! isset( $slug_attribute_parts[0] ) || empty( $slug_attribute_parts[0] ) ) {
				continue;
			}

			$slug = $slug_attribute_parts[0];

			if ( ! isset( $this->autofill_providers[ $slug ] ) ) {
				continue;
			}

			if ( ! is_callable( array( $this->autofill_providers[ $slug ], 'get_short_name' ) ) ) {
				continue;
			}

			$provider_instance   = $this->autofill_providers[ $slug ];
			$provider_short_name = $provider_instance->get_short_name();

			$new_attribute = false;
			if ( isset( $this->autofill_providers_names[ $slug_attribute ] ) && ! empty( $this->autofill_providers_names[ $slug_attribute ] ) ) {
				$new_attribute = array(
					'name' => $this->autofill_providers_names[ $slug_attribute ],
				);
			}

			if ( ! $new_attribute ) {
				continue;
			}

			if ( ! in_array( $slug, array_keys( $grouped_autofill_providers ), true ) ) {
				$grouped_autofill_providers[ $slug ] = array(
					'name'       => $this->autofill_providers_groups[ $slug ],
					'attributes' => array(),
				);
			}
			$grouped_autofill_providers[ $slug ] ['attributes'][ $slug_attribute ] = $new_attribute;
		}

		return $grouped_autofill_providers;
	}

	/**
	 * Get the provider by its slug
	 *
	 * @since 1.0.5
	 *
	 * @param $provider_slug
	 *
	 * @return Forminator_Autofill_Provider_Abstract|null
	 */
	public function get_autofill_provider( $provider_slug ) {
		if ( isset( $this->autofill_providers[ $provider_slug ] ) ) {
			return $this->autofill_providers[ $provider_slug ];
		}

		return null;
	}

	/**
	 * Call `init` function of provider, if its not `inited` yet
	 *
	 * @since 1.0.5
	 *
	 * @param $provider_slug
	 *
	 * @return Forminator_Autofill_Provider_Abstract|null
	 */
	final public function init_provider( $provider_slug ) {
		$provider = $this->get_autofill_provider( $provider_slug );
		if ( ! $provider ) {
			return null;
		}
		if ( in_array( $provider->get_slug(), $this->_banned_providers, true ) ) {
			// debug purpose only
//			error_log( 'Autofill Provider for ' . $autofill_instance->get_slug() . ' is banned' );

			return null;
		}
		if ( ! in_array( $provider->get_slug(), self::$inited_providers, true ) ) {
			$provider->init();
			self::$inited_providers[] = $provider->get_slug();

			return $provider;
		}

		return $provider;
	}

	/**
	 * Cleanup providers that being inited
	 *
	 * @since 1.6.2
	 * @internal
	 */
	public function cleanup_initied_providers() {
		self::$inited_providers = array();
	}

}
