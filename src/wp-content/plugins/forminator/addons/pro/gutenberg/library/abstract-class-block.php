<?php

/**
 * Class Forminator_GFBlock_Abstract
 * Extend this class to create new gutenberg block
 *
 * @since 1.0 Gutenber Addon
 */
abstract class Forminator_GFBlock_Abstract {

	/**
	 * Type will be used as identifier
	 *
	 * @since 1.0 Gutenber Addon
	 *
	 * @var string
	 */
	protected $_slug;

	/**
	 * Get block type
	 *
	 * @since  1.0 Gutenber Addon
	 * @return string
	 */
	final public function get_slug() {
		return $this->_slug;
	}

	/**
	 * Initialize block
	 *
	 * @since 1.0 Gutenberg Addon
	 */
	public function init() {
		// Register block
		add_action( 'init', array( $this, 'register_block' ), 5 );

		// Register preview REST API
		add_action( 'rest_api_init', array( $this, 'block_preview_api' ) );

		// Load block scripts
		add_action( 'enqueue_block_editor_assets', array( $this, 'load_assets' ) );
	}

	/**
	 * Register block type callback
	 * Shouldn't be overridden on block class
	 *
	 * @since 1.0 Gutenberg Addon
	 */
	public function register_block() {
		register_block_type( 'forminator/' . $this->get_slug(), array(
			'render_callback' => array( $this, 'render_block' ),
		) );
	}

	/**
	 * Register REST API route for block preview.
	 *
	 * @since 1.0 Gutenberg Addon
	 */
	public function block_preview_api() {
		register_rest_route( 'forminator/v1', '/preview/' . $this->get_slug(), array(
			array(
				'methods'  => WP_REST_Server::READABLE,
				'callback' => array( $this, 'preview_block_markup' ),
				'args'     => array(
					'module_id'      => array(
						'description' => __( 'Module ID' ),
						'type'        => 'integer',
						'required'    => true,
					)
				),
			),
		) );
	}

	/**
	 * Print block preview markup
	 *
	 * @since 1.0 Gutenberg Addon
	 * @param $data
	 */
	public function preview_block_markup( $data ) {
		// Get properties
		$properties = $data->get_params();

		// Get module ID
		$id = isset( $properties['module_id'] ) ? $properties['module_id'] : false;

		// Get block preview markup
		$markup = $this->preview_block( $properties );

		if( $markup ) {
			wp_send_json_success( array( 'markup' => trim( $markup ) ) );
		} else {
			wp_send_json_error();
		}
	}

	/**
	 * Render block on front-end
	 * Should be overriden in block class
	 *
	 * @since 1.0 Gutenberg Addon
	 * @param array $properties Block properties
	 *
	 * @return string
	 */
	public function render_block( $properties = array() ) {
		return '';
	}

	/**
	 * Preview form in the block
	 * Should be overriden in block class
	 *
	 * @since 1.0 Gutenberg Addon
	 * @param array $properties Block properties
	 *
	 * @return string
	 */
	public function preview_block( $properties = array() ) {
		return '';
	}

	/**
	 * Enqueue assets ( scritps / styles )
	 * Should be overriden in block class
	 *
	 * @since 1.0 Gutenberg Addon
	 */
	public function load_assets() {
		return true;
	}
}
