<?php
/**
 * REST API Shipping Zone Methods controller
 *
 * Handles requests to the /shipping/zones/<id>/methods endpoint.
 *
 * @package Automattic/WooCommerce/RestApi
 * @since   3.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * REST API Shipping Zone Methods class.
 *
 * @package Automattic/WooCommerce/RestApi
 * @extends WC_REST_Shipping_Zone_Methods_V2_Controller
 */
class WC_REST_Shipping_Zone_Methods_Controller extends WC_REST_Shipping_Zone_Methods_V2_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc/v3';
}
