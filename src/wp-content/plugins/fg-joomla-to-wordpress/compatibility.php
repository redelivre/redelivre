<?php
/**
 * Get the last occurred error
 * for PHP < 5.2.0
 */
if (!function_exists('error_get_last')) {
	function error_get_last() {
		$__error_get_last_retval__ = array(
			'type'        => '',
			'message'     => '',
			'file'        => '',
			'line'        => ''
		);
		return $__error_get_last_retval__;
	}

}

/**
 * Set a post thumbnail
 * for WordPress < 3.1
 */
if (!function_exists('set_post_thumbnail')) {
	function set_post_thumbnail( $post, $thumbnail_id ) {
		$post = get_post( $post );
		$thumbnail_id = absint( $thumbnail_id );
		if ( $post && $thumbnail_id && get_post( $thumbnail_id ) ) {
			$thumbnail_html = wp_get_attachment_image( $thumbnail_id, 'thumbnail' );
			if ( ! empty( $thumbnail_html ) ) {
				return update_post_meta( $post->ID, '_thumbnail_id', $thumbnail_id );
			}
		}
		return false;
	}
}

/**
 * Suspend the cache
 * for WordPress < 3.3
 */
if (!function_exists('wp_suspend_cache_addition')) {
	function wp_suspend_cache_addition() {
	}
}

?>
