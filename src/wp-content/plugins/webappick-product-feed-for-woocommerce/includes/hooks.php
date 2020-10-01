<?php
/**
 * Default Hooks
 * @package WooFeed
 * @subpackage WooFeed_Helper_Functions
 * @version 1.0.0
 * @since WooFeed 3.3.0
 * @author KD <mhamudul.hk@gmail.com>
 * @copyright WebAppick
 */

if ( ! defined( 'ABSPATH' ) ) {
	die(); // Silence...
}
/** @define "WOO_FEED_FREE_ADMIN_PATH" "./../admin/" */ // phpcs:ignore

// Admin Page Form Actions.

// The Editor.
add_filter( 'woo_feed_parsed_rules', 'woo_feed_filter_parsed_rules', 10, 2 );

// Mics.
add_action( 'admin_post_wf_export_feed', 'woo_feed_export_config', 10 );
add_action( 'admin_post_wpf_import', 'woo_feed_import_config' );

// Product Loop Start.
add_action( 'woo_feed_before_product_loop', 'woo_feed_apply_hooks_before_product_loop', 10, 2 );

// In The Loop
add_filter( 'woo_feed_product_type_separator', 'woo_feed_product_taxonomy_term_separator', 10, 2 );
add_filter( 'woo_feed_tags_separator', 'woo_feed_product_taxonomy_term_separator', 10, 2 );
add_filter( 'woo_feed_get_availability_attribute', 'woo_feed_get_availability_attribute_filter', 10, 3 );

// Product Loop End.
add_action( 'woo_feed_after_product_loop', 'woo_feed_remove_hooks_before_product_loop', 10, 2 );

// End of file hooks.php.
