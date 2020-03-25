<?php
/**
 * Log Helper Functions
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
/** @define "WOO_FEED_ADMIN_PATH" "./../admin/" */ // phpcs:ignore

if ( ! function_exists( 'woo_feed_is_debugging_enabled' ) ) {
	function woo_feed_is_debugging_enabled() {
		return get_option( 'woo_feed_enable_error_debugging', false ) === 'on';
	}
}
if ( ! function_exists( 'woo_feed_get_logger' ) ) {
	/**
	 * Get The logger.
	 *
	 * Example:
	 *      woo_feed_get_logger()->debug( 'Test log', [ 'source' => 'test-debug' ] );
	 *
	 * @since 3.2.1
	 *
	 * @return WC_Logger
	 */
	function woo_feed_get_logger() {
		static $logger = null;
		if ( null !== $logger && is_a( $logger, 'WC_Logger' ) ) {
			return $logger;
		}
		$logger = new WC_Logger( [ new Woo_Feed_Log_Handler_File() ] );
		
		return $logger;
	}
}
if ( ! function_exists( 'woo_feed_log_errors_at_shutdown' ) ) {
	/**
	 * Shutdown log handler
	 * @since 3.2.1
	 * @return void
	 */
	function woo_feed_log_errors_at_shutdown(){
		$error = error_get_last();
		if ( $error && in_array( $error['type'], array( E_ERROR, E_PARSE, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR ), true ) ) {
			/* translators: 1: error message 2: file name and path 3: line number */
			$message = sprintf( __( '%1$s in %2$s on line %3$s', 'woo-feed' ), $error['message'], $error['file'], $error['line'] );
			woo_feed_log_fatal_error( $message );
			do_action( 'woo_feed_shutdown_error', $error );
		}
	}
}
if ( ! function_exists( 'woo_feed_log' ) ) {
	/**
	 * Write message to log file.
	 * Write log message if debugging is enable
	 *
	 * @since 3.2.1
	 *
	 * @param string $source will be use for log file name.
	 * @param string $message Log message.
	 * @param string $level One of the following:
	 *     'emergency': System is unusable.
	 *     'alert': Action must be taken immediately.
	 *     'critical': Critical conditions.
	 *     'error': Error conditions.
	 *     'warning': Warning conditions.
	 *     'notice': Normal but significant condition.
	 *     'info': Informational messages.
	 *     'debug': Debug-level messages.
	 * @param mixed $data Extra data for the log handler.
	 * @param bool $force_log ignore debugging settings
	 * @param bool $wc_log log data in wc-logs directory
	 *
	 * @return void
	 */
	function woo_feed_log( $source, $message, $level = 'debug', $data = null, $force_log = false, $wc_log = false ) {
		if ( woo_feed_is_debugging_enabled() || true === $force_log ) {
			if ( ! in_array( $level, [
				'emergency',
				'alert',
				'critical',
				'critical',
				'error',
				'warning',
				'notice',
				'info',
				'debug',
			] ) ) {
				return;
			}
			$context = [ 'source' => $source ];
			if ( is_array( $data ) ) {
				if ( isset( $data['source'] ) ) {
					unset( $data['source'] );
				}
				$context = array_merge( $context, $data );
			} else {
				$context['data'] = $data;
			}
			$loggers = [ woo_feed_get_logger() ];
			if ( true === $wc_log ) {
				$loggers[] = wc_get_logger();
			}
			foreach ( $loggers as $logger ) {
				if ( is_callable( [ $logger, $level ] ) ) {
					$logger->$level( $message . PHP_EOL, $context );
				}
			}
		}
	}
}
if ( ! function_exists( 'woo_feed_log_fatal_error' ) ) {
	/**
	 * Log Fatal Errors in both wc-logs and woo-feed/logs
	 *
	 * @param string $message The log message.
	 * @param mixed $data Extra data for the log handler.
	 */
	function woo_feed_log_fatal_error( $message, $data = null ) {
		// woocommerce use 'fatal-errors' as log handler...
		// make no conflicts with woocommerce fatal-errors logs
		woo_feed_log( 'woo-feed-fatal-errors', $message, 'critical', $data, true, true );
	}
}
if ( ! function_exists( 'woo_feed_log_debug_message' ) ) {
	/**
	 * Log Fatal Errors in both wc-logs and woo-feed/logs
	 *
	 * @param string $message The log message.
	 * @param mixed $data Extra data for the log handler.
	 */
	function woo_feed_log_debug_message( $message, $data = null ) {
		// woocommerce use 'fatal-errors' as log handler...
		// make no conflicts with woocommerce fatal-errors logs
		woo_feed_log( 'woo-feed-fatal-errors', $message, 'debug', $data, true, true );
	}
}
if ( ! function_exists( 'woo_feed_delete_log' ) ) {
	/**
	 * Delete Log file by source or handle name
	 *
	 * @param string $source log source or handle name
	 * @param bool $handle use source as handle
	 *
	 * @return bool
	 */
	function woo_feed_delete_log( $source, $handle = false ) {
		try {
			if ( 'woo-feed-fatal-errors' == $source ) {
				// fatal error are also logged in wc-logs dir.
				if ( class_exists( 'WC_Log_Handler_File', false ) ) {
					$log_handler = new WC_Log_Handler_File();
					$log_handler->remove( false == $handle ? WC_Log_Handler_File::get_log_file_name( $source ) : $source );
				}
			}
			$feed_log_handler = new Woo_Feed_Log_Handler_File();
			return $feed_log_handler->remove( false == $handle ? Woo_Feed_Log_Handler_File::get_log_file_name( $source ) : $source );
		} catch ( Exception $e ) {
			return false;
		}
	}
}
if ( ! function_exists( 'woo_feed_delete_all_logs' ) ) {
	function woo_feed_delete_all_logs() {
		// delete the fatal error log
		woo_feed_delete_log( 'woo-feed-fatal-errors' );
		// get all logs
		$logs = Woo_Feed_Log_Handler_File::get_log_files();
		foreach ( array_values( $logs ) as $log ) {
			woo_feed_delete_log( $log, true );
		}
	}
}
if ( ! function_exists( 'woo_feed_log_feed_process' ) ) {
	/**
	 * Log Feed Generation Progress to individual log file.
	 *
	 * @since 3.2.1
	 *
	 * @param string $feed_name Feed name, will be use for log file name.
	 * @param string $message Log message.
	 * @param mixed $data Extra data for the log handler.
	 * @param bool $force_log ignore debugging settings
	 *
	 * @return void
	 */
	function woo_feed_log_feed_process( $feed_name, $message, $data = null, $force_log = false ) {
		woo_feed_log( $feed_name, $message, 'debug', $data, $force_log, false );
	}
}
if ( ! function_exists( 'woo_feed_cleanup_logs' ) ) {
	/**
	 * Trigger logging cleanup using the logging class.
	 * @return void
	 */
	function woo_feed_cleanup_logs() {
		$logger = woo_feed_get_logger();
		// @TODO dont' clear all logs.
		// @TODO clear only the error log.
		// @TODO change shutdown function and send the main error log to wc-logs directory.
		if ( is_callable( array( $logger, 'clear_expired_logs' ) ) ) {
			$logger->clear_expired_logs();
		}
	}
}

// End of file log-helper.php.