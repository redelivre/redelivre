<?php
/**
 * Fired when the plugin is uninstalled.
 * @since      1.0.0
 *
 * @package    WooFeed
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined('WP_UNINSTALL_PLUGIN') ) {
    exit;
}

wp_clear_scheduled_hook( 'woo_feed_cleanup_logs' );
wp_clear_scheduled_hook( 'woo_feed_update' );
// End of file uninstall.php