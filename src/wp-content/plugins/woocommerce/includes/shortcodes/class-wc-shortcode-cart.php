<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cart Shortcode
 *
 * Used on the cart page, the cart shortcode displays the cart contents and interface for coupon codes and other cart bits and pieces.
 *
 * @author 		WooThemes
 * @category 	Shortcodes
 * @package 	WooCommerce/Shortcodes/Cart
 * @version     2.3.0
 */
class WC_Shortcode_Cart {

	/**
	 * Calculate shipping for the cart.
	 */
	public static function calculate_shipping() {
		try {
			WC()->shipping->reset_shipping();

			$country  = isset( $_POST['calc_shipping_country'] ) ? wc_clean( wp_unslash( $_POST['calc_shipping_country'] ) ) : ''; // WPCS: input var ok, CSRF ok, sanitization ok.
			$state    = isset( $_POST['calc_shipping_state'] ) ? wc_clean( wp_unslash( $_POST['calc_shipping_state'] ) ) : ''; // WPCS: input var ok, CSRF ok, sanitization ok.
			$postcode = isset( $_POST['calc_shipping_postcode'] ) ? wc_clean( wp_unslash( $_POST['calc_shipping_postcode'] ) ) : ''; // WPCS: input var ok, CSRF ok, sanitization ok.
			$city     = isset( $_POST['calc_shipping_city'] ) ? wc_clean( wp_unslash( $_POST['calc_shipping_city'] ) ) : ''; // WPCS: input var ok, CSRF ok, sanitization ok.

			if ( $postcode && ! WC_Validation::is_postcode( $postcode, $country ) ) {
				throw new Exception( __( 'Please enter a valid postcode / ZIP.', 'woocommerce' ) );
			} elseif ( $postcode ) {
				$postcode = wc_format_postcode( $postcode, $country );
			}

			if ( $country ) {
				WC()->customer->set_location( $country, $state, $postcode, $city );
				WC()->customer->set_shipping_location( $country, $state, $postcode, $city );
			} else {
				WC()->customer->set_billing_address_to_base();
				WC()->customer->set_shipping_address_to_base();
			}

			WC()->customer->set_calculated_shipping( true );
			WC()->customer->save();

			wc_add_notice( __( 'Shipping costs updated.', 'woocommerce' ), 'notice' );

			do_action( 'woocommerce_calculated_shipping' );

		} catch ( Exception $e ) {
			if ( ! empty( $e ) ) {
				wc_add_notice( $e->getMessage(), 'error' );
			}
		}
	}

	/**
	 * Output the cart shortcode.
	 *
	 * @param array $atts
	 */
	public static function output( $atts ) {
		// Constants.
		wc_maybe_define_constant( 'WOOCOMMERCE_CART', true );

		$atts = shortcode_atts( array(), $atts, 'woocommerce_cart' );

		// Update Shipping
		if ( ! empty( $_POST['calc_shipping'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'woocommerce-cart' ) ) {
			self::calculate_shipping();

			// Also calc totals before we check items so subtotals etc are up to date
			WC()->cart->calculate_totals();
		}

		// Check cart items are valid
		do_action( 'woocommerce_check_cart_items' );

		// Calc totals
		WC()->cart->calculate_totals();

		if ( WC()->cart->is_empty() ) {
			wc_get_template( 'cart/cart-empty.php' );
		} else {
			wc_get_template( 'cart/cart.php' );
		}
	}
}
