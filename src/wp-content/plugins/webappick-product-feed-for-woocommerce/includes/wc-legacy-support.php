<?php
/**
 * Legacy WooCommerce Support
 */
if( ! function_exists( 'wc_get_attribute_taxonomy_labels' ) && ! woo_feed_wc_version_check( '3.6.0' ) ) {
	/**
	 * Support for wc < 3.6.0
	 * Get (cached) attribute taxonomy label and name pairs.
	 * @return array
	 */
	function wc_get_attribute_taxonomy_labels(){
		$prefix      = WC_Cache_Helper::get_cache_prefix( 'woocommerce-attributes' );
		$cache_key   = $prefix . 'labels';
		$cache_value = wp_cache_get( $cache_key, 'woocommerce-attributes' );
		
		if ( $cache_value ) {
			return $cache_value;
		}
		
		$taxonomy_labels = wp_list_pluck( wc_get_attribute_taxonomies(), 'attribute_label', 'attribute_name' );
		
		wp_cache_set( $cache_key, $taxonomy_labels, 'woocommerce-attributes' );
		
		return $taxonomy_labels;
	}
}
