<?php
/**
 * A class definition responsible for processing FTP Uploading
 *
 * @link       https://webappick.com/
 * @since      1.0.0
 *
 * @package    Woo_Feed
 * @subpackage Woo_Feed/includes
 * @author     Ohidul Islam <wahid@webappick.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	die(); // If this file is called directly, abort.
}

/**
 * Class FTPClient
 */
class FTPClient {
	/**
	 * Holds The FTP Connection Resource
	 *
	 * @var resource
	 */
	private $connection_id;
	/**
	 * Login Check Flag
	 *
	 * @var bool
	 */
	private $login_ok = false;
	/**
	 * Message Array
	 *
	 * @var array
	 */
	private $message_array = array();

	/**
	 * FTPClient constructor.
	 */
	public function __construct() {
	}

	/**
	 * Store Log Messages.
	 *
	 * @param string $message   message to add.
	 */
	private function log_message( $message ) {
		$this->message_array[] = $message;
	}

	/**
	 * Get Logs.
	 *
	 * @return array
	 */
	public function get_messages() {
		return $this->message_array;
	}

	/**
	 * Connect to FTP Server
	 *
	 * @param string $server        Server host.
	 * @param string $ftp_user       FTP Username.
	 * @param string $ftp_password   FTP Password.
	 * @param bool   $is_passive     FTP Transfer Mode.
	 * @param int    $ftp_port       FTP Port.
	 * @return bool
	 */
	public function connect( $server, $ftp_user, $ftp_password, $is_passive = false, $ftp_port = 21 ) {

		// *** Set up basic connection
		$this->connection_id = ftp_connect( $server, $ftp_port );
		if ( ! $this->connection_id ) {
			$this->log_message( esc_html__( 'FTP connection has failed!', 'woo-feed' ) );
			/* translators: 1: ftp username, 2: server host, 3: server port */
			$this->log_message( sprintf( esc_html__( 'Attempted to connect to %1$s@%2$s:%3$s', 'woo-feed' ), $ftp_user, $server, $ftp_port ) );
			return false;
		}
		// *** Login with username and password
		$login_result = ftp_login( $this->connection_id, $ftp_user, $ftp_password );
		// *** Sets passive mode on/off (default off)
		ftp_pasv( $this->connection_id, $is_passive );
		// *** Check connection
		if ( ! $login_result ) {
			$this->log_message( esc_html__( 'FTP Login has failed!', 'woo-feed' ) );
			/* translators: 1: ftp username, 2: server host, 3: server port */
			$this->log_message( sprintf( esc_html__( 'Attempted to login %1$s@%2$s:%3$s', 'woo-feed' ), $ftp_user, $server, $ftp_port ) );
			return false;
		} else {
			/* translators: 1: ftp username, 2: server host, 3: server port */
			$this->log_message( sprintf( esc_html__( 'Connected to %1$s@%2$s:%3$s', 'woo-feed' ), $ftp_user, $server, $ftp_port ) );
			$this->login_ok = true;
			return true;
		}
	}

	/**
	 * Check if input is valid octal
	 *
	 * @param mixed $input  input data.
	 *
	 * @return bool
	 */
	private function is_octal( $input ) {
		return decoct( octdec( $input ) ) == $input;
	}

	/**
	 * Give permission to file.
	 *
	 * @param int    $permissions          permission mode.
	 * @param string $remote_filename   remote file name with full path.
	 * @return bool
	 */
	public function chmod( $permissions, $remote_filename ) {
		if ( $this->is_octal( $permissions ) ) {
			$result = ftp_chmod( $this->connection_id, $permissions, $remote_filename );
			if ( $result ) {
				$this->log_message( esc_html__( 'File Permission Granted', 'woo-feed' ) );

				return true;
			} else {
				$this->log_message( esc_html__( 'File Permission Failed', 'woo-feed' ) );

				return false;
			}
		} else {
			/* translators: Permission Mode */
			$this->log_message( sprintf( esc_html__( '%s must be an octal number', 'woo-feed' ), $permissions ) );

			return false;
		}
	}

	/**
	 * Make Directory.
	 *
	 * @param string $directory     Directory name and path.
	 * @return bool
	 */
	public function make_dir( $directory ) {
		// *** If creating a directory is successful...
		if ( ftp_mkdir( $this->connection_id, $directory ) ) {
			/* translators: Permission Mode */
			$this->log_message( sprintf( esc_html__( 'Directory "%s" created successfully.', 'woo-feed' ), $directory ) );
			return true;

		} else {
			/* translators: Directory Path */
			$this->log_message( sprintf( esc_html__( 'Failed creating directory "%s".', 'woo-feed' ), $directory ) );
			return false;
		}
	}

	/**
	 * Upload files to FTP server
	 *
	 * @param string $file_from      file name and path that needs to be uploaded.
	 * @param string $file_to        file name and path where the to put the file.
	 *
	 * @return bool
	 */
	public function upload_file( $file_from, $file_to ) {

		// *** Set the transfer mode
		$ascii_array   = array( 'txt', 'csv', 'xml' );
		$get_extension = explode( '.', $file_from );
		$extension     = end( $get_extension );

		$mode = in_array( $extension, $ascii_array ) ? FTP_ASCII : FTP_BINARY;

		// *** Upload the file
		$upload = ftp_put( $this->connection_id, $file_to, $file_from, $mode );
		// *** Check upload status
		if ( ! $upload ) {
			$this->log_message( 'FTP upload has failed!' );
			return false;
		} else {
			/* translators: 1: file from, 2: file to */
			$this->log_message( sprintf( esc_html__( 'Uploaded "%1$s" as "%2$s"', 'woo-feed' ), $file_from, $file_to ) );
			return true;
		}
	}
}
