<?php

if (current_user_can('subscriber')) {
    add_action('wp_dashboard_setup', 'campanha_remove_dashboard_widgets');
    
    add_action('admin_init', function() {
        // disable help boxes in the admin pages
        remove_action('admin_enqueue_scripts', array('WP_Internal_Pointers', 'enqueue_scripts'));
        
        // disable screen options tab
        add_filter('screen_options_show_screen', function() {
            return false;
        });
        
        // remove help tab
        add_filter('contextual_help_list', function() {
            get_current_screen()->remove_help_tabs();
        });
    });
    
    add_action('wp_dashboard_setup', function() {
        // add widget to list user's campaigns to the dashboard 
        wp_add_dashboard_widget('user_campaigns', 'Suas campanhas', 'campanha_user_campaigns_widget');
        
        // add widget to start the creation of a new campaign to the dashboard 
        wp_add_dashboard_widget('create_campaign', 'Criar nova campanha', 'campanha_create_new_campaign_widget');
    });
    
    // remove default admin menu options
    add_action('admin_head', 'remove_menus');
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

/**
 * Print user's campaigns admin widget
 */
function campanha_user_campaigns_widget() {
    echo 'AHA!';
}

/**
 * Widget to start the creation of a new campaign
 */
function campanha_create_new_campaign_widget() {
    echo 'BUH!';
}

/**
 * Remove default menu options
 */
function remove_menus() {
    global $menu;
    $restricted = array(__('Dashboard'));
    foreach ($menu as $key => $value) {
        $value = explode(' ', $menu[$key][0]);
        if (in_array($value[0] != NULL ? $value[0] : "" , $restricted)){
            unset($menu[$key]);
        }
    }
}