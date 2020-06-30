<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Core
 */
class Forminator_Modules {

	/**
	 * Store modules objects
	 *
	 * @var array
	 */
	public $modules = array();

	/**
	 * Forminator_Modules constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		$this->includes();
		$this->load_modules();
	}

	/**
	 * Includes
	 *
	 * @since 1.0
	 */
	private function includes() {
		/* @noinspection PhpIncludeInspection */
		include_once forminator_plugin_dir() . 'library/abstracts/abstract-class-module.php';
	}

	/**
	 * Load modules
	 *
	 * @since 1.0
	 */
	private function load_modules() {
		/**
		 * Filters modules list
		 */
		$modules = apply_filters( 'forminator_modules', array(
			'custom_forms' => array(
				'class'	  => 'Custom_Form',
				'slug'  => 'custom-forms',
				'label'	  => __( 'Custom Forms', Forminator::DOMAIN )
			),
			'polls' => array(
				'class'	  => 'Polls',
				'slug'  => 'polls',
				'label'	  => __( 'Polls', Forminator::DOMAIN )
			),
			'quizzes' => array(
				'class'	  => 'Quizzes',
				'slug'  => 'quizzes',
				'label'	  => __( 'Quizzes', Forminator::DOMAIN )
			),
		) );

		array_walk( $modules, array( $this, 'load_module' ) );
	}

	/**
	 * Load module
	 *
	 * @since 1.0
	 * @param $data
	 * @param $id
	 */
	public function load_module( $data, $id ) {
		$module_class = 'Forminator_' . $data[ 'class' ];
		$module_slug = $data[ 'slug' ];
		$module_label = $data[ 'label' ];
		
		// Include module
		$path = forminator_plugin_dir() . 'library/modules/' . $module_slug . '/loader.php';
		if ( file_exists( $path ) ) {
			include_once $path;
		}

		if ( class_exists( $module_class ) ) {
			$module_object = new $module_class( $id, $module_label );

			$this->modules[ $id ] = $module_object;
		}
	}

	/**
	 * Retrieve modules objects
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_modules() {
		return $this->modules;
	}
}
