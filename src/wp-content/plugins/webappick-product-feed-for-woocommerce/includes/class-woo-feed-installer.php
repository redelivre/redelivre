<?php
/**
 * Installer
 *
 * @package WooFeed
 * @version 1.0.0
 * @since WooFeed 3.2.1
 * @copyright 2019 WebAppick
 * @author KD <mhaudul.hk@gmail.com>
 */
if ( ! defined( 'ABSPATH' ) ) {
	die(); // Silence...
}
class Woo_Feed_installer {
	
	public static function init() {
		add_action( 'init', array( __CLASS__, 'check_version' ), 5 );
		add_filter( 'cron_schedules', array( __CLASS__, 'cron_schedules' ) ); // phpcs:ignore
	}
	
	/**
	 * Check WooCommerce version and run the updater is required.
	 *
	 * This check is done on all requests and runs if the versions do not match.
	 */
	public static function check_version() {
		if ( ! defined( 'IFRAME_REQUEST' ) && version_compare( get_option( 'woo_feed_free_version' ), WOO_FEED_FREE_VERSION, '<' ) ) {
			if ( ! defined( 'WOO_FEED_UPDATING' ) ) {
				define( 'WOO_FEED_UPDATING', TRUE );
			}
			self::install();
			do_action( 'woo_feed_plugin_updated' );
		}
	}
	
	/**
	 * Add more cron schedules.
	 *
	 * @param array $schedules List of WP scheduled cron jobs.
	 *
	 * @return array
	 */
	public static function cron_schedules( $schedules ) {
		$interval                   = get_option( 'wf_schedule' );
		$schedules['woo_feed_corn'] = array(
			'display'  => esc_html__( 'Woo Feed Update Interval', 'woo-feed' ),
			'interval' => $interval,
		);
		return $schedules;
	}
	
	/**
	 * Install WooFeed.
	 */
	public static function install() {
		if ( ! is_blog_installed() ) {
			return;
		}
		
		// Bail if unsupported php version
		if ( ! wooFeed_is_supported_php() ) {
			/* translators: 1: minimum required php version, 2: server php version */
			echo '<div class="notice error"><p>' . sprintf( __( 'The Minimum PHP Version Requirement for <b>WooCommerce Product Feed</b> is %1$s. You are Running PHP %2$s', 'woo-feed' ), WOO_FEED_MIN_PHP_VERSION, phpversion() ) . '</p></div>'; // phpcs:ignore
			die();
		}
		
		// Check if we are not already running this routine.
		if ( 'yes' === get_transient( 'woo_feed_installing' ) ) {
			return;
		}
		
		if ( ! defined( 'WOO_FEED_INSTALLING' ) ) {
			define( 'WOO_FEED_INSTALLING', true );
		}
		
		// If we made it till here nothing is running yet, lets set the transient now.
		set_transient( 'woo_feed_installing', 'yes', 10 * MINUTE_IN_SECONDS );
		
		self::pro_version_warning();
		self::migrate_db();
		self::create_cron_jobs();
		self::create_files();
		self::update_woo_feed_version();
		
		// installation finished.
		delete_transient( 'woo_feed_installing' );
	}
	
	private static function pro_version_warning() {
		// Deactivate Free version if pro already available.
		if ( woo_feed_is_plugin_active( "webappick-product-feed-for-woocommerce-pro/webappick-product-feed-for-woocommerce-pro.php" ) ) {
			echo '<div class="notice error"><p>'. __( 'Please deactivate the <b>WooCommerce Product Feed Pro</b> version to activate free version again.', 'woo-feed' ) .'</p></div>'; // phpcs:ignore
			die();
		}
	}
	
	/**
	 * DB Updates
	 */
	private static function migrate_db() {
		if ( ! defined( 'WOO_FEED_UPDATING' ) ) {
			return;
		}
		// settings api update.
		if ( version_compare( '3.3.10', WOO_FEED_FREE_VERSION, '<' ) ) {
			$keys = [ 'per_batch', 'product_query_type', 'enable_error_debugging', 'woo_feed_cache_ttl' ];
			$data = [];
			foreach ( $keys as $key ) {
				$data[ $key ] = get_option( 'woo_feed_' . $key );
				delete_option( 'woo_feed_' . $key );
			}
			woo_feed_save_options( $data );
		}
	}
	
	/**
	 * Create cron jobs (clear them first).
	 */
	private static function create_cron_jobs() {
		// Schedule Update Interval
		if ( ! get_option( 'wf_schedule', false ) ) {
			update_option( 'wf_schedule', HOUR_IN_SECONDS, false );
		}
		// clear previous scheduled cron jobs
		wp_clear_scheduled_hook( 'woo_feed_corn' );
		wp_clear_scheduled_hook( 'woo_feed_cleanup_logs' );
		wp_clear_scheduled_hook( 'woo_feed_update' );
		// Schedule Cron jobs
		wp_schedule_event( time(), 'woo_feed_corn', 'woo_feed_update' );
		wp_schedule_event( time() + ( 3 * HOUR_IN_SECONDS ), 'daily', 'woo_feed_cleanup_logs' );
	}
	
	/**
	 * Create files/directories.
	 */
	private static function create_files() {
		// Bypass if filesystem is read-only and/or non-standard upload system is used.
		if ( apply_filters( 'woo_feed_install_skip_create_files', false ) ) {
			return;
		}
		// Install files and folders for uploading files and prevent hotlinking.
		$files = array(
			array(
				'base'    => WOO_FEED_LOG_DIR,
				'file'    => '.htaccess',
				'content' => 'deny from all',
			),
			array(
				'base'    => WOO_FEED_LOG_DIR,
				'file'    => 'index.html',
				'content' => '',
			),
		);
		
		foreach ( $files as $file ) {
			if ( wp_mkdir_p( $file['base'] ) && ! file_exists( trailingslashit( $file['base'] ) . $file['file'] ) ) {
				$file_handle = fopen( trailingslashit( $file['base'] ) . $file['file'], 'w' ); // phpcs:ignore
				if ( false !== $file_handle ) {
					fwrite( $file_handle, $file['content'] ); // phpcs:ignore
					fclose( $file_handle ); // phpcs:ignore
				}
			}
		}
	}
	
	/**
	 * Update WC version to current.
	 */
	private static function update_woo_feed_version() {
		delete_option( 'woo_feed_free_version' );
		delete_option( 'woo_feed_version' ); // old option.
		update_option( 'woo_feed_free_version', WOO_FEED_FREE_VERSION, false );
		if ( ! defined( 'WOO_FEED_UPDATING' ) ) {
			update_option( 'woo-feed-free-activation-time', get_option( 'woo-feed-activation-time', time() ), false ); // check for old version time.
		}
	}
}
Woo_Feed_installer::init();
// End of file class-woo-feed-installer.php
