<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Module
 *
 * Abstract class for modules
 *
 * @property array fields
 * @property array settings
 * @since 1.0
 */

abstract class Forminator_Template {

	/*
	 * Template fields
	 *
	 * @var array
	 */
	protected $template_fields = array();

	/*
	 * Template options
	 *
	 * @var array
	 */
	public $options = array();

	public function __construct() {
		$this->fields   = $this->fields();
		$this->settings = $this->settings();
		$this->options  = $this->defaults();
	}

	/**
	 * @since 1.0
	 * @return array
	 */
	public function fields() {
		return array();
	}

	/**
	 * @since 1.0
	 * @return array
	 */
	public function settings() {
		return array();
	}

	/**
	 * @since 1.0
	 * @return array
	 */
	public function defaults() {
		return array();
	}

	/**
	 * Get specific option from module options
	 *
	 * @since 1.0
	 * @param $option
	 * @param string $default
	 *
	 * @return mixed|string
	 */
	public function get_option( $option, $default = '' ) {
		if ( isset( $this->options[ $option ] ) ) {
			return $this->options[ $option ];
		}
		return $default;
	}
}
