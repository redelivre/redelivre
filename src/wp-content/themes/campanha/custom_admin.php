<?php

add_action('admin_menu', function() {
    add_menu_page('Projetos', 'Projetos', 'read', 'campaigns', function() {
        require(TEMPLATEPATH . '/includes/campaigns.php');
    });
    
    add_submenu_page('campaigns', 'Novo Projeto', 'Novo Projeto', 'read', 'campaigns_new', function() {
        require(TEMPLATEPATH . '/includes/campaigns_new.php');
    });
});
