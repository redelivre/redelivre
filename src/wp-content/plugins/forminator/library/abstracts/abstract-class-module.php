<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Module
 *
 * Abstract class for modules
 *
 * @since 1.0
 */

abstract class Forminator_Module {

	/**
	 * Module ID
	 *
	 * @var string
	 */
	protected $id = '';

	/**
	 * Module Name
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * Module options
	 *
	 * @var array
	 */
	protected $options = array();

	/**
	 * Forminator_Module constructor.
	 *
	 * @since 1.0
	 * @param $id
	 * @param $name
	 */
	public function __construct( $id, $name ) {
		$this->id = $id;
		$this->name = $name;
		$this->options = $this->options();

		$this->init();
		$this->load_admin();
		$this->register_cpt();
		$this->load_front();
	}

	/**
	 * @since 1.0
	 */
	public function init() {}

	/**
	 * Register Module CPT.
	 *
	 * @since 1.0
	 */
	abstract public function register_cpt();

	/**
	 * Get Module ID
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get Module Name
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Get Module Description
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_description() {
		return $this->get_option('description');
	}

	/**
	 * Get slug
	 *
	 * @since 1.0
	 * @return mixed
	 */
	public function get_slug() {
		$id = $this->get_id();
		return str_replace( '_', '-', strtolower( $id ) );
	}

	/**
	 * Get Module icon
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_icon() {
		return $this->get_option('icon');
	}

	/**
	 * Get Module button label
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_label() {
		return $this->get_option('button_label');
	}

	/**
	 * Module Defaults
	 * Here we store Module name, description, icon, etc.
	 *
	 * @since 1.0
	 * @return array
	 */
	public function options() {
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
		if( isset( $this->options[ $option ] ) ) return $this->options[ $option ];
		return $default;
	}

	/**
	 * Load admin only scripts
	 *
	 * @since 1.0
	 */
	abstract public function load_admin();

	/**
	 * Load front only scripts
	 *
	 * @since 1.0
	 */
	abstract public function load_front();
}
