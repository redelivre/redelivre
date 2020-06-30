<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Fields
 *
 * @since 1.0
 */
class Forminator_Fields {
	/**
	 * Store fields objects
	 *
	 * @var array
	 */
	public $fields = array();

	/**
	 * Forminator_Fields constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {

		$this->load_forminator_autofill_providers();
		$this->maybe_load_external_autofill_providers();

		$loader = new Forminator_Loader();

		$fields = $loader->load_files(
			'library/fields',
			array(
				'stripe.php' => array(
					'php' => '5.4.0',
				),
			)
		);

		/**
		 * Filters the form fields
		 */
		$this->fields = apply_filters( 'forminator_fields', $fields );
	}

	/**
	 * Retrieve fields objects
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_fields() {
		return $this->fields;
	}

	/**
	 * Load autofill providers requirements
	 *
	 * @since 1.0.5
	 */
	public function load_forminator_autofill_providers() {
		include_once forminator_plugin_dir() . 'library/class-autofill-loader.php';
		$required_files = array(
			// load contracts
			forminator_plugin_dir() . 'library/field-autofill-providers/contracts/class-autofill-provider-interface.php',
			forminator_plugin_dir() . 'library/field-autofill-providers/contracts/class-autofill-provider-abstract.php',
			//load Forminator provider autoload
			forminator_plugin_dir() . 'library/field-autofill-providers/autoload.php',
		);

		$required_files_exists = true;
		foreach ( $required_files as $required_file ) {
			if ( ! file_exists( $required_file ) ) {
				$required_files_exists = false;
				break;
			}
		}

		if ( $required_files_exists ) {
			foreach ( $required_files as $required_file ) {
				/** @noinspection PhpIncludeInspection */
				include_once $required_file;
			}
		}

	}

	/**
	 * Load member's autofill provider
	 *
	 * @since 1.0.5
	 */
	public function maybe_load_external_autofill_providers() {
		/**
		 * see samples/forminator-simple-autofill-plugin for example how to use it
		 */
		do_action( 'forminator_register_autofill_provider' );
	}
}
