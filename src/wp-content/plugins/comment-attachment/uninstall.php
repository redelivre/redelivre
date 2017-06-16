<?php
//if uninstall not called from WordPress exit
if (!defined('WP_UNINSTALL_PLUGIN'))
    exit();

$optionName = 'commentAttachment';

// For Single site
if (!is_multisite()){
    delete_option($optionName);
    foreach (get_users('fields=ID') as $userId){
        delete_user_meta($userId, 'wpCommentAttachmentIgnoreNag'); // remove the nag notice, clean after us
    }
} else {
    global $wpdb;
    $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
    $original_blog_id = get_current_blog_id();
    foreach ($blog_ids as $blog_id){
        switch_to_blog($blog_id);
        delete_site_option($optionName);
    }
    switch_to_blog($original_blog_id);
}