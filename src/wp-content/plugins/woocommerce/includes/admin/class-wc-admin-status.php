<?php
/**
 * Debug/Status page
 *
 * @package     WooCommerce/Admin/System Status
 * @version     2.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Admin_Status Class.
 */
class WC_Admin_Status {

	/**
	 * Handles output of the reports page in admin.
	 */
	public static function output() {
		include_once( dirname( __FILE__ ) . '/views/html-admin-page-status.php' );
	}

	/**
	 * Handles output of report.
	 */
	public static function status_report() {
		include_once( dirname( __FILE__ ) . '/views/html-admin-page-status-report.php' );
	}

	/**
	 * Handles output of tools.
	 */
	public static function status_tools() {
		$tools = self::get_tools();

		if ( ! empty( $_GET['action'] ) && ! empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'debug_action' ) ) {
			$tools_controller = new WC_REST_System_Status_Tools_Controller;
			$action           = wc_clean( $_GET['action'] );

			if ( array_key_exists( $action, $tools ) ) {
				$response = $tools_controller->execute_tool( $action );
			} else {
				$response = array( 'success' => false, 'message' => __( 'Tool does not exist.', 'woocommerce' ) );
			}

			if ( $response['success'] ) {
				echo '<div class="updated inline"><p>' . esc_html( $response['message'] ) . '</p></div>';
			} else {
				echo '<div class="error inline"><p>' . esc_html( $response['message'] ) . '</p></div>';
			}
		}

		// Display message if settings settings have been saved
		if ( isset( $_REQUEST['settings-updated'] ) ) {
			echo '<div class="updated inline"><p>' . __( 'Your changes have been saved.', 'woocommerce' ) . '</p></div>';
		}

		include_once( dirname( __FILE__ ) . '/views/html-admin-page-status-tools.php' );
	}

	/**
	 * Get tools.
	 * @return array of tools
	 */
	public static function get_tools() {
		$tools_controller = new WC_REST_System_Status_Tools_Controller;
		return $tools_controller->get_tools();
	}

	/**
	 * Show the logs page.
	 */
	public static function status_logs() {
		if ( defined( 'WC_LOG_HANDLER' ) && 'WC_Log_Handler_DB' === WC_LOG_HANDLER ) {
			self::status_logs_db();
		} else {
			self::status_logs_file();
		}
	}

	/**
	 * Show the log page contents for file log handler.
	 */
	public static function status_logs_file() {

		$logs = self::scan_log_files();

		if ( ! empty( $_REQUEST['log_file'] ) && isset( $logs[ sanitize_title( $_REQUEST['log_file'] ) ] ) ) {
			$viewed_log = $logs[ sanitize_title( $_REQUEST['log_file'] ) ];
		} elseif ( ! empty( $logs ) ) {
			$viewed_log = current( $logs );
		}

		$handle = ! empty( $viewed_log ) ? self::get_log_file_handle( $viewed_log ) : '';

		if ( ! empty( $_REQUEST['handle'] ) ) {
			self::remove_log();
		}

		include_once( 'views/html-admin-page-status-logs.php' );
	}

	/**
	 * Show the log page contents for db log handler.
	 */
	public static function status_logs_db() {

		// Flush
		if ( ! empty( $_REQUEST['flush-logs'] ) ) {
			self::flush_db_logs();
		}

		// Bulk actions
		if ( isset( $_REQUEST['action'] ) && isset( $_REQUEST['log'] ) ) {
			self::log_table_bulk_actions();
		}

		$log_table_list = new WC_Admin_Log_Table_List();
		$log_table_list->prepare_items();

		include_once( 'views/html-admin-page-status-logs-db.php' );
	}

	/**
	 * Retrieve metadata from a file. Based on WP Core's get_file_data function.
	 * @since  2.1.1
	 * @param  string $file Path to the file
	 * @return string
	 */
	public static function get_file_version( $file ) {

		// Avoid notices if file does not exist
		if ( ! file_exists( $file ) ) {
			return '';
		}

		// We don't need to write to the file, so just open for reading.
		$fp = fopen( $file, 'r' );

		// Pull only the first 8kiB of the file in.
		$file_data = fread( $fp, 8192 );

		// PHP will close file handle, but we are good citizens.
		fclose( $fp );

		// Make sure we catch CR-only line endings.
		$file_data = str_replace( "\r", "\n", $file_data );
		$version   = '';

		if ( preg_match( '/^[ \t\/*#@]*' . preg_quote( '@version', '/' ) . '(.*)$/mi', $file_data, $match ) && $match[1] ) {
			$version = _cleanup_header_comment( $match[1] );
		}

		return $version ;
	}

	/**
	 * Return the log file handle.
	 *
	 * @param string $filename
	 * @return string
	 */
	public static function get_log_file_handle( $filename ) {
		return substr( $filename, 0, strlen( $filename ) > 37 ? strlen( $filename ) - 37 : strlen( $filename ) - 4 );
	}

	/**
	 * Scan the template files.
	 * @param  string $template_path
	 * @return array
	 */
	public static function scan_template_files( $template_path ) {

		$files  = @scandir( $template_path );
		$result = array();

		if ( ! empty( $files ) ) {

			foreach ( $files as $key => $value ) {

				if ( ! in_array( $value, array( ".", ".." ) ) ) {

					if ( is_dir( $template_path . DIRECTORY_SEPARATOR . $value ) ) {
						$sub_files = self::scan_template_files( $template_path . DIRECTORY_SEPARATOR . $value );
						foreach ( $sub_files as $sub_file ) {
							$result[] = $value . DIRECTORY_SEPARATOR . $sub_file;
						}
					} else {
						$result[] = $value;
					}
				}
			}
		}
		return $result;
	}

	/**
	 * Scan the log files.
	 * @return array
	 */
	public static function scan_log_files() {
		$files  = @scandir( WC_LOG_DIR );
		$result = array();

		if ( ! empty( $files ) ) {

			foreach ( $files as $key => $value ) {

				if ( ! in_array( $value, array( '.', '..' ) ) ) {
					if ( ! is_dir( $value ) && strstr( $value, '.log' ) ) {
						$result[ sanitize_title( $value ) ] = $value;
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Get latest version of a theme by slug.
	 * @param  object $theme WP_Theme object.
	 * @return string Version number if found.
	 */
	public static function get_latest_theme_version( $theme ) {
		include_once( ABSPATH . 'wp-admin/includes/theme.php' );

		$api = themes_api( 'theme_information', array(
			'slug'     => $theme->get_stylesheet(),
			'fields'   => array(
				'sections' => false,
				'tags'     => false,
			),
		) );

		$update_theme_version = 0;

		// Check .org for updates.
		if ( is_object( $api ) && ! is_wp_error( $api ) ) {
			$update_theme_version = $api->version;

		// Check WooThemes Theme Version.
		} elseif ( strstr( $theme->{'Author URI'}, 'woothemes' ) ) {
			$theme_dir = substr( strtolower( str_replace( ' ','', $theme->Name ) ), 0, 45 );

			if ( false === ( $theme_version_data = get_transient( $theme_dir . '_version_data' ) ) ) {
				$theme_changelog = wp_safe_remote_get( 'http://dzv365zjfbd8v.cloudfront.net/changelogs/' . $theme_dir . '/changelog.txt' );
				$cl_lines  = explode( "\n", wp_remote_retrieve_body( $theme_changelog ) );
				if ( ! empty( $cl_lines ) ) {
					foreach ( $cl_lines as $line_num => $cl_line ) {
						if ( preg_match( '/^[0-9]/', $cl_line ) ) {
							$theme_date         = str_replace( '.' , '-' , trim( substr( $cl_line , 0 , strpos( $cl_line , '-' ) ) ) );
							$theme_version      = preg_replace( '~[^0-9,.]~' , '' ,stristr( $cl_line , "version" ) );
							$theme_update       = trim( str_replace( "*" , "" , $cl_lines[ $line_num + 1 ] ) );
							$theme_version_data = array( 'date' => $theme_date , 'version' => $theme_version , 'update' => $theme_update , 'changelog' => $theme_changelog );
							set_transient( $theme_dir . '_version_data', $theme_version_data , DAY_IN_SECONDS );
							break;
						}
					}
				}
			}

			if ( ! empty( $theme_version_data['version'] ) ) {
				$update_theme_version = $theme_version_data['version'];
			}
		}

		return $update_theme_version;
	}

	/**
	 * Remove/delete the chosen file.
	 */
	public static function remove_log() {
		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'remove_log' ) ) {
			wp_die( __( 'Action failed. Please refresh the page and retry.', 'woocommerce' ) );
		}

		if ( ! empty( $_REQUEST['handle'] ) ) {
			$log_handler = new WC_Log_Handler_File();
			$log_handler->remove( $_REQUEST['handle'] );
		}

		wp_safe_redirect( esc_url_raw( admin_url( 'admin.php?page=wc-status&tab=logs' ) ) );
		exit();
	}

	/**
	 * Clear DB log table.
	 *
	 * @since 3.0.0
	 */
	private static function flush_db_logs() {
		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'woocommerce-status-logs' ) ) {
			wp_die( __( 'Action failed. Please refresh the page and retry.', 'woocommerce' ) );
		}

		WC_Log_Handler_DB::flush();

		wp_safe_redirect( esc_url_raw( admin_url( 'admin.php?page=wc-status&tab=logs' ) ) );
		exit();
	}

	/**
	 * Bulk DB log table actions.
	 *
	 * @since 3.0.0
	 */
	private static function log_table_bulk_actions() {
		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'woocommerce-status-logs' ) ) {
			wp_die( __( 'Action failed. Please refresh the page and retry.', 'woocommerce' ) );
		}

		$log_ids = array_map( 'absint', (array) $_REQUEST['log'] );

		if ( 'delete' === $_REQUEST['action'] || 'delete' === $_REQUEST['action2'] ) {
			WC_Log_Handler_DB::delete( $log_ids );
			wp_safe_redirect( esc_url_raw( admin_url( 'admin.php?page=wc-status&tab=logs' ) ) );
			exit();
		}
	}
}
