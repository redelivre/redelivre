<?php

$user = wp_get_current_user();

if (!empty($user->roles) && is_array($user->roles) && in_array('subscriber', $user->roles)) {
    add_action('wp_dashboard_setup', 'campanha_remove_dashboard_widgets');
    
    add_action('admin_init', function() {
        // disable help boxes in the admin pages
        remove_action('admin_enqueue_scripts', array('WP_Internal_Pointers', 'enqueue_scripts'));
        
        // disable wp and my sites menus from admin bar
        remove_action('admin_bar_menu', 'wp_admin_bar_wp_menu');
        remove_action('admin_bar_menu', 'wp_admin_bar_my_sites_menu', 20);
        
        // disable screen options tab
        add_filter('screen_options_show_screen', function() {
            return false;
        });
        
        // remove help tab
        add_filter('contextual_help_list', function() {
            get_current_screen()->remove_help_tabs();
        });

        // remove default menu options        
        remove_menu_page('index.php');
        remove_menu_page('profile.php'); 
    });
    
    add_action('init', function() {
        // couldn't make the call to add_menu_page() work without first including the file below
        require( ABSPATH . '/wp-admin/includes/plugin.php' );
        add_menu_page('Administrar campanhas', 'Administrar campanhas', 'read', 'campaigns', function() {
            require(TEMPLATEPATH . '/campaigns.php');
        });
    });
}

/**
 * Remove all defaul widgets from admin dashboard
 * for subscribers.
 */
function campanha_remove_dashboard_widgets() {
    global$wp_meta_boxes;

    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);
}
