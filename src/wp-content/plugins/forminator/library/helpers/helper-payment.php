<?php

/**
 * Check if stripe payment lib loaded
 *
 * @since 1.7.0
 *
 * @param string $version
 *
 * @return bool
 */
function forminator_payment_lib_stripe_version_loaded( $version = FORMINATOR_STRIPE_LIB_VERSION ) {
	$loaded          = false;
	$min_php_version = apply_filters( 'forminator_payments_stripe_min_php_version', '5.6.0' );

	if ( version_compare( PHP_VERSION, $min_php_version, 'ge' ) ) {
		if ( class_exists( '\Forminator\Stripe\Stripe' ) ) {
			if ( defined( '\Forminator\Stripe\Stripe::VERSION' ) ) {
				$loaded = \Forminator\Stripe\Stripe::VERSION === $version;
			}
		}
	}

	return $loaded;
}

/**
 * Get stripe php lib version
 *
 * @since 1.7.0
 * @return int|string
 */
function forminator_payment_lib_stripe_get_version() {
	if ( forminator_payment_lib_stripe_version_loaded() ) {
		return \Forminator\Stripe\Stripe::VERSION;
	}

	return 0;
}

/**
 * Check if PayPal payment lib loaded
 *
 * @since 1.7.1
 *
 * @param string $version
 *
 * @return bool
 */
function forminator_payment_lib_paypal_version_loaded( $version = FORMINATOR_PAYPAL_LIB_VERSION ) {
	$loaded          = false;
	$min_php_version = apply_filters( 'forminator_payments_paypal_min_php_version', '5.3' );

	if ( version_compare( PHP_VERSION, $min_php_version, 'ge' ) ) {
		if ( class_exists( '\Forminator\PayPal\Core\PayPalConstants' ) ) {
			if ( defined( '\Forminator\PayPal\Core\PayPalConstants::SDK_VERSION' ) ) {
				$loaded = \Forminator\PayPal\Core\PayPalConstants::SDK_VERSION === $version;
			}
		}
	}

	return $loaded;
}
