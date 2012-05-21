<?php

$user = wp_get_current_user();

if (!empty($user->roles) && is_array($user->roles) && in_array('subscriber', $user->roles)) {
    add_action('admin_menu', function() {
        add_menu_page('Administrar campanhas', 'Administrar campanhas', 'read', 'campaigns', function() {
            require(TEMPLATEPATH . '/campaigns.php');
        });
    });
}
