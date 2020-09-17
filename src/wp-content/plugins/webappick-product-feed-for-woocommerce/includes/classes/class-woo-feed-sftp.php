<?php
/** @noinspection PhpUnhandledExceptionInspection */
class SFTPConnection
{
    private $connection;
    private $sftp;
    
	public function __construct( $host, $port = 22 ) {
	    if ( ! extension_loaded( 'ssh2' ) ) {
		    /* translators: 1: server host, 2: server port */
		    throw new Exception( sprintf( esc_html__( 'Could not connect to %1$s:%2$s. SSH2 is not enabled on this server.', 'woo-feed' ), $host, $port ) );
	    }
	    $this->connection = ssh2_connect( $host, $port );
    }
	
	public function login( $username, $password ) {
		if ( ! ssh2_auth_password( $this->connection, $username, $password ) ) {
			throw new Exception("Could not authenticate with username $username " . "and password $password.");
		}
		
		$this->sftp = ssh2_sftp( $this->connection );
		if ( ! $this->sftp ) {
			throw new Exception( 'Could not initialize SFTP subsystem.' );
		}
    }
	
	/**
	 * @param string $local_file    local file to upload
	 * @param string $remote_file   remote file name
	 * @param string $path          must use trailing slash. directory path to put the file on remote server.
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function upload_file( $local_file, $remote_file, $path ) {
		
		if ( ! file_exists( $local_file ) ) {
			throw new Exception( "Local file does't exists.: $local_file." );
		}
		$data_to_send = file_get_contents( $local_file ); // phpcs:ignore
		if ( false === $data_to_send ) {
			throw new Exception( "Could not open local file: $local_file." );
		}
		
        $sftp = $this->sftp;
		if ( ! is_dir( "ssh2.sftp://$sftp$path" ) ) {
			throw new Exception("Invalid Remote Path: $path");
		}
		
		if ( ! is_writeable( "ssh2.sftp://$sftp$path" ) ) { // phpcs:ignore
			throw new Exception("Could not Write file on: $path");
		}
		
	    $stream = fopen( "ssh2.sftp://$sftp$path$remote_file", 'w' ); // phpcs:ignore
	    if ( ! $stream ) {
        	throw new Exception("Could not open file: $path$remote_file");
        }
	    
	    if ( false === fwrite( $stream, $data_to_send ) ) { // phpcs:ignore
        	throw new Exception("Could not send data from file: $local_file.");
        }
	
	    fclose( $stream ); // phpcs:ignore
		return true;
    }
    public function delete_file( $remote_file ) {
        $sftp = $this->sftp;
        unlink("ssh2.sftp://$sftp$remote_file"); // phpcs:ignore
    }
}