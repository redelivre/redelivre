<?php
/**
 * Copyright (c) Facebook, Inc. and its affiliates. All Rights Reserved
 *
 * This source code is licensed under the license found in the
 * LICENSE file in the root directory of this source tree.
 *
 * @package FacebookCommerce
 */

namespace SkyVerge\WooCommerce\Facebook\API;

defined( 'ABSPATH' ) or exit;

use SkyVerge\WooCommerce\PluginFramework\v5_5_4 as Framework;

/**
 * Base API request object.
 *
 * @since 2.0.0
 */
class Request extends Framework\SV_WC_API_JSON_Request {


	use Traits\Rate_Limited_Request;


	/**
	 * API request constructor.
	 *
	 * @since 2.0.0
	 *
	 * @param string $path endpoint route
	 * @param string $method HTTP method
	 */
	public function __construct( $path, $method ) {

		$this->method = $method;
		$this->path   = $path;
	}


	/**
	 * Sets the request parameters.
	 *
	 * @since 2.0.0
	 *
	 * @param array $params request parameters
	 */
	public function set_params( $params ) {

		$this->params = $params;
	}


	/**
	 * Sets the request data.
	 *
	 * @since 2.0.0
	 *
	 * @param array $data request data
	 */
	public function set_data( $data ) {

		$this->data = $data;
	}


}
