<?php
/**
 * Copyright (c) Facebook, Inc. and its affiliates. All Rights Reserved
 *
 * This source code is licensed under the license found in the
 * LICENSE file in the root directory of this source tree.
 *
 * @package FacebookCommerce
 */

namespace SkyVerge\WooCommerce\Facebook\API\Traits;

use SkyVerge\WooCommerce\Facebook\API\Response;

defined( 'ABSPATH' ) or exit;

/**
 * Rate limited API trait.
 *
 * @since 2.0.0
 */
trait Rate_Limited_API {


	/**
	 * Stores the delay, in seconds, for requests with the given rate limit ID.
	 *
	 * @since 2.0.0
	 *
	 * @param string $rate_limit_id request ID for rate limiting
	 * @param int $delay delay in seconds
	 */
	public function set_rate_limit_delay( $rate_limit_id, $delay ) {

		update_option( "wc_facebook_rate_limit_${rate_limit_id}", $delay );
	}


	/**
	 * Gets the number of seconds before a new request with the given rate limit ID can be made.
	 *
	 * @since 2.0.0
	 *
	 * @param string $rate_limit_id request ID for rate limiting
	 * @return int
	 */
	public function get_rate_limit_delay( $rate_limit_id ) {

		return (int) get_option( "wc_facebook_rate_limit_${rate_limit_id}", 0 );
	}


	/**
	 * Uses the response object and the array of headers to get information about the API usage
	 * and calculate the next delay for requests of the same type.
	 *
	 * @since 2.0.0
	 *
	 * @param Rate_Limited_Response $response API response object
	 * @param array $headers API response headers
	 * @return int
	 */
	protected function calculate_rate_limit_delay( $response, $headers ) {

		// TODO: Implement calculate_rate_limit_delay() method.
		return $response->get_rate_limit_estimated_time_to_regain_access( $headers );
	}


}
