<?php

add_action('admin_menu', function() {
    add_menu_page('Campanhas', 'Campanhas', 'read', 'campaigns', function() {
        require(TEMPLATEPATH . '/includes/campaigns.php');
    });
    
    add_submenu_page('campaigns', 'Nova campanha', 'Nova campanha', 'read', 'campaigns_new', function() {
        require(TEMPLATEPATH . '/includes/campaigns_new.php');
    });
});
