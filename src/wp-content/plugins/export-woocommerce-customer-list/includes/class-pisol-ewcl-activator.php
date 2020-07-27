<?php

/**
 * Fired during plugin activation
 *
 * @link       piwebsolution.com
 * @since      1.0.0
 *
 * @package    Pisol_Ewcl
 * @subpackage Pisol_Ewcl/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Pisol_Ewcl
 * @subpackage Pisol_Ewcl/includes
 * @author     Rajesh Singh <rajeshsingh520@gmail.com>
 */
class Pisol_Ewcl_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		add_option('pi_ewcl_do_activation_redirect', true);
		self::create_folder();
	}

	/**
	 * This function creates a download folder this folder is download 
	 * protected, all the plugin files will go in this folder
	 */
	public static function create_folder() {
		$upload_dir      = wp_upload_dir();

		$files = array(
			array(
				'base'    => $upload_dir['basedir'] . '/ewcl_customers',
				'file'    => 'index.html',
				'content' => '',
			),
			array(
				'base'    => $upload_dir['basedir'] . '/ewcl_customers',
				'file'    => '.htaccess',
				'content' => 'deny from all',
			)
		);

		foreach ( $files as $file ) {
			if ( wp_mkdir_p( $file['base'] ) && ! file_exists( trailingslashit( $file['base'] ) . $file['file'] ) ) {
				$file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'w' ); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_read_fopen
				if ( $file_handle ) {
					fwrite( $file_handle, $file['content'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fwrite
					fclose( $file_handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
				}
			}
		}
		chmod($files[0]['base'], 0755);
	}

}
