<?php

/**
 * This is used to save feed
 *
 * @since      1.0.0
 * @package    Woo_Feed
 * @subpackage Woo_Feed/includes
 * @author     Ohidul Islam <wahid@webappick.com>
 */
class Woo_Feed_Savefile {

    /**
     * Check if the directory for feed file exist or not and make directory
     *
     * @param $path
     * @return bool
     */
    public function checkDir( $path ) {
        if ( ! file_exists($path) ) {
            return wp_mkdir_p($path);
        }
        return true;
    }
	
	/**
	 * Save CSV Feed file
	 *
	 * @param $path
	 * @param $file
	 * @param $content
	 * @param $info
	 *
	 * @return bool
	 */
	public function saveCSVFile( $path, $file, $content, $info ) {
		if ( $this->checkDir( $path ) ) {
			/**
			 * @TODO see below
			 * @see Woo_Feed_Savefile::saveFile()
			 */
			if ( file_exists( $file ) ) {
				unlink( $file ); // phpcs:ignore
			}
			
			$fp = fopen( $file, 'wb' ); // phpcs:ignore
			
			if ( 'tab' == $info['delimiter'] ) {
				$delimiter = "\t";
			} else {
				$delimiter = $info['delimiter'];
			}
			
			$enclosure = $info['enclosure'];
			$eol       = PHP_EOL;
			if ( 'trovaprezzi' === $info['provider'] ) {
				$eol = '<endrecord>' . PHP_EOL;
			}
			if ( count( $content ) ) {
				foreach ( $content as $fields ) {
					if ( 'double' == $enclosure ) {
						fputcsv( $fp, $fields, $delimiter, chr( 34 ) ); // phpcs:ignore
					} elseif ( 'single' == $enclosure ) {
						fputcsv( $fp, $fields, $delimiter, chr( 39 ) ); // phpcs:ignore
					} else {
						fputs( $fp, implode( $delimiter, $fields ) . $eol ); // phpcs:ignore
					}
				}
			}
			
			fclose( $fp ); // phpcs:ignore
			
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Save XML and TXT File
	 *
	 * @param $path
	 * @param $file
	 * @param $content
	 *
	 * @return bool
	 */
	public function saveFile( $path, $file, $content ) {
		/**
		 * @TODO use WP Filesystem API
		 * @see https://codex.wordpress.org/Filesystem_API
		 * @see http://ottopress.com/2011/tutorial-using-the-wp_filesystem/
		 *
		 * @TODO Check write permission on installation and show admin warning
		 * @see wp_is_writable()
		 */
		if ( $this->checkDir( $path ) ) {
			if ( file_exists( $file ) ) {
				unlink( $file ); // phpcs:ignore
			}
			$fp = fopen( $file, 'w+' ); // phpcs:ignore
			fwrite( $fp, $content ); // phpcs:ignore
			fclose( $fp ); // phpcs:ignore
			
			return true;
		} else {
			return false;
		}
	}
}