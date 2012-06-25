<?php

add_action('admin_init', function() {
    if (!is_super_admin() && !defined('DOING_AJAX')) {
        add_action('wp_dashboard_setup', 'campanha_remove_dashboard_widgets');
    
        // disable help boxes in the admin pages
        remove_action('admin_enqueue_scripts', array('WP_Internal_Pointers', 'enqueue_scripts'));
        
        // disable wp and my sites menus from admin bar
        remove_action('admin_bar_menu', 'wp_admin_bar_wp_menu');
        
        // remove help tab
        add_filter('contextual_help_list', function() {
            get_current_screen()->remove_help_tabs();
        });
    
        // remove default menu options
        if (!is_main_site()) {        
            remove_submenu_page('index.php', 'my-sites.php');
        } else {
            remove_menu_page('index.php');
        }
        remove_menu_page('profile.php'); 
    }
});
    


/**
 * Remove all defaul widgets from admin dashboard
 * for subscribers.
 */
function campanha_remove_dashboard_widgets() {
    global $wp_meta_boxes;

    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);

    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_recent_drafts']);
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);
}
