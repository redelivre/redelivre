<?php

$user = wp_get_current_user();

if (!empty($user->roles) && is_array($user->roles) && in_array('subscriber', $user->roles)) {
    add_action('init', function() {
        // couldn't make the call to add_menu_page() work without first including the file below
        require(ABSPATH . '/wp-admin/includes/plugin.php');
        add_menu_page('Administrar campanhas', 'Administrar campanhas', 'read', 'campaigns', function() {
            require(TEMPLATEPATH . '/campaigns.php');
        });
    });
}
