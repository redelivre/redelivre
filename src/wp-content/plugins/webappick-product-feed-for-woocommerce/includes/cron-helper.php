<?php
/**
 * Cron Helper Functions
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


// Cron Action.
if ( ! function_exists( 'woo_feed_cron_update_feed' ) ) {
	/**
	 * Scheduled Action Hook
	 * @return void
	 */
	function woo_feed_cron_update_feed() {
		global $wpdb;
		if ( woo_feed_is_debugging_enabled() ) {
			woo_feed_delete_log( 'woo-feed-cron' );
			woo_feed_log_feed_process( 'woo-feed-cron', 'Preparing WooFeed Auto Update' );
			$processed = 0;
		}
		$query = $wpdb->prepare( "SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s;", 'wf_feed_' . '%' );
		$result = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore
		foreach ( $result as $option ) {
			$feedInfo = maybe_unserialize( get_option( $option['option_name'] ) );
			if ( ! isset( $feedInfo['feedrules'] ) || isset( $feedInfo['status'] ) && '0' == $feedInfo['status'] ) continue;
				try {
				if ( woo_feed_is_debugging_enabled() ) {
					$processed++;
					woo_feed_delete_log( $feedInfo['feedrules']['filename'] );
					woo_feed_log_feed_process( $feedInfo['feedrules']['filename'], sprintf( 'Getting Data for %s feed.', $option['option_name'] ) );
					woo_feed_log_feed_process( $feedInfo['feedrules']['filename'], 'Generating Feed VIA CRON JOB...' );
					woo_feed_log_feed_process( $feedInfo['feedrules']['filename'], sprintf( 'Getting Data for %s feed.', $option['option_name'] ) );
					woo_feed_log_feed_process( $feedInfo['feedrules']['filename'], 'Current Limit is ---.' );
					woo_feed_log( $feedInfo['feedrules']['filename'], 'Feed Config::' . PHP_EOL . print_r( $feedInfo['feedrules'], true ), 'info' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
					}
				woo_feed_generate_feed( $feedInfo['feedrules'], $option['option_name'] );
			} catch ( Exception $e ) {
				$message = 'Error Updating Feed Via CRON Job' . PHP_EOL . 'Caught Exception :: ' . $e->getMessage();
				woo_feed_log( $feedInfo['feedrules']['filename'], $message, 'critical', $e, true );
				woo_feed_log_fatal_error( $message, $e );
			}
		}
		if ( woo_feed_is_debugging_enabled() ) {
			woo_feed_log_feed_process( 'woo-feed-cron', sprintf( 'Total %d Feed Processed', $processed ) );
			woo_feed_log_feed_process( 'woo-feed-cron', 'WooFeed Auto Update Completed' );
		}
	}
	
	add_action( 'woo_feed_update', 'woo_feed_cron_update_feed' );
}

// Single Feed Update Cron
if ( ! function_exists( 'woo_feed_cron_update_single_feed' ) ) {
	/**
	 * Scheduled Action Hook
	 *
	 * @param array $feedName
	 *
	 * @return void
	 */
    function woo_feed_cron_update_single_feed( $feedName ) {
        global $wpdb;
        if ( is_array($feedName) ) {
            $feedName = $feedName[0];
        }

        $cron_param_feedName = $feedName;

        if ( woo_feed_is_debugging_enabled() ) {
            woo_feed_delete_log( 'woo-feed-cron' );
            woo_feed_log_feed_process( 'woo-feed-cron', 'Preparing WooFeed Auto Update' );
            $processed = 0;
        }

        $feedName = str_replace('wf_config','wf_feed_',$feedName);

        // get interval
        $interval = absint( get_option( 'wf_schedule' ) );

        // schedule single feed update
        wp_clear_scheduled_hook( 'woo_feed_update_single_feed', array( $cron_param_feedName ) );
        if ( ! wp_next_scheduled( 'woo_feed_update_single_feed', array( $cron_param_feedName ) ) ) {
            wp_schedule_event( time() + $interval, 'woo_feed_corn', 'woo_feed_update_single_feed', array( $cron_param_feedName ) );
        }

        $result   = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->options WHERE option_name = %s", $feedName ), 'ARRAY_A' ); // phpcs:ignore

        if ( ! empty( $result ) ) {
            foreach ( $result as $key => $value ) {
                $feedInfo = maybe_unserialize( get_option( $value['option_name'] ) );
                if ( ! isset( $feedInfo['feedrules'] ) || isset( $feedInfo['status'] ) && '0' == $feedInfo['status'] ) continue;
                try {
                    if ( woo_feed_is_debugging_enabled() ) {
                        $processed++;
                        woo_feed_delete_log( $feedInfo['feedrules']['filename'] );
                        woo_feed_log_feed_process( $feedInfo['feedrules']['filename'], sprintf( 'Getting Data for %s feed.', $feedInfo['feedrules']['filename'] ) );
                        woo_feed_log_feed_process( $feedInfo['feedrules']['filename'], 'Generating Feed VIA CRON JOB...' );
                        woo_feed_log_feed_process( $feedInfo['feedrules']['filename'], sprintf( 'Getting Data for %s feed.', $feedInfo['feedrules']['filename'] ) );
                        woo_feed_log_feed_process( $feedInfo['feedrules']['filename'], 'Current Limit is ---.' );
                        woo_feed_log( $feedInfo['feedrules']['filename'], 'Feed Config::' . PHP_EOL . print_r( $feedInfo['feedrules'], true ), 'info' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
                    }
                    woo_feed_generate_feed( $feedInfo['feedrules'], $value['option_name'] );
                } catch ( Exception $e ) {
                    $message = 'Error Updating Feed Via CRON Job' . PHP_EOL . 'Caught Exception :: ' . $e->getMessage();
                    woo_feed_log( $feedInfo['feedrules']['filename'], $message, 'critical', $e, true );
                    woo_feed_log_fatal_error( $message, $e );
                }
            }
            if ( woo_feed_is_debugging_enabled() ) {
                woo_feed_log_feed_process( 'woo-feed-cron', sprintf( 'Total %d Feed Processed', $processed ) );
                woo_feed_log_feed_process( 'woo-feed-cron', 'WooFeed Auto Update Completed' );
            }
        }
    }
	
	add_action( 'woo_feed_update_single_feed', 'woo_feed_cron_update_single_feed' );
}

add_action( 'woo_feed_after_update_config', function( $data, $feed_slug ) {
    // Schedule Cron.
    if ( ! wp_next_scheduled('woo_feed_update_single_feed',[ $feed_slug ]) ) {
        wp_schedule_event( time(), 'woo_feed_corn', 'woo_feed_update_single_feed', [ $feed_slug ] );
    }

}, 10, 3 );

add_action( 'woo_feed_after_insert_config', function( $data, $feed_slug ) {
    // Schedule Cron.
    if ( ! wp_next_scheduled('woo_feed_update_single_feed',[ $feed_slug ]) ) {
        wp_schedule_event( time(), 'woo_feed_corn', 'woo_feed_update_single_feed', [ $feed_slug ] );
    }

}, 10, 3 );
// End of file cron-helper.php.
