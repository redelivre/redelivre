<?php



require_once ABSPATH . 'wp-admin/includes/import.php';
require_once ABSPATH . 'wp-admin/includes/image.php';  

global $wpdb;

//process_attachment( $newatt, $i );

if ($handle = opendir(WP_CONTENT_DIR . '/plugins/mapasdevista/default-pins/')) {
    while (false !== ($entry = readdir($handle))) {
        if (substr($entry, -3) == "png") {
            
            $newatt = array(
                'post_title' => $entry,
                'post_status' => 'publish', 
                'post_parent' => 0,
                'post_type' => 'attachment'
            );
            
            process_attachment( $newatt, site_url('/wp-content/plugins/mapasdevista/default-pins/' . $entry)  );
            
            
        }
    }
    closedir($handle);
}



function fetch_remote_file( $url, $post ) {
    
    global $url_remap;
    
    // extract the file name and extension from the url
    $file_name = basename( $url );

    // get placeholder file in the upload dir with a unique, sanitized filename
    $upload = wp_upload_bits( $file_name, 0, '',
				array_key_exists('upload_date', $post) ? $post['upload_date'] : null );
    if ( $upload['error'] )
        return new WP_Error( 'upload_dir_error', $upload['error'] );

    // fetch the remote url and write it to the placeholder file
    $headers = wp_get_http( $url, $upload['file'] );

    // request failed
    if ( ! $headers ) {
        @unlink( $upload['file'] );
        return new WP_Error( 'import_file_error', __('Remote server did not respond', 'wordpress-importer') );
    }

    // make sure the fetch was successful
    if ( $headers['response'] != '200' ) {
        @unlink( $upload['file'] );
        return new WP_Error( 'import_file_error', sprintf( __('Remote server returned error response %1$d %2$s', 'wordpress-importer'), esc_html($headers['response']), get_status_header_desc($headers['response']) ) );
    }

    $filesize = filesize( $upload['file'] );

    if ( isset( $headers['content-length'] ) && $filesize != $headers['content-length'] ) {
        @unlink( $upload['file'] );
        return new WP_Error( 'import_file_error', __('Remote file is incorrect size', 'wordpress-importer') );
    }

    if ( 0 == $filesize ) {
        @unlink( $upload['file'] );
        return new WP_Error( 'import_file_error', __('Zero size file downloaded', 'wordpress-importer') );
    }

    
    // keep track of the old and new urls so we can substitute them later
    $url_remap[$url] = $upload['url'];


    return $upload;
}


function process_attachment( $post, $url ) {
    
    // if the URL is absolute, but does not contain address, then upload it assuming base_site_url
    //if ( preg_match( '|^/[\w\W]+$|', $url ) )
    //	$url = rtrim( $this->base_url, '/' ) . $url;
    
    global $url_remap;
    
    $upload = fetch_remote_file( $url, $post );
    if ( is_wp_error( $upload ) )
        return $upload;

    if ( $info = wp_check_filetype( $upload['file'] ) )
        $post['post_mime_type'] = $info['type'];
    else
        return new WP_Error( 'attachment_processing_error', __('Invalid file type', 'wordpress-importer') );

    $post['guid'] = $upload['url'];

    // as per wp-admin/includes/upload.php
    $post_id = wp_insert_attachment( $post, $upload['file'] );
    wp_update_attachment_metadata( $post_id, wp_generate_attachment_metadata( $post_id, $upload['file'] ) );

    update_post_meta($post_id, '_pin_anchor', array('x' => 0, 'y' => 30 ));

    return $post_id;
}


?>
