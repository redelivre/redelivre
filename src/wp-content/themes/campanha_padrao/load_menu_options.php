<?php

// add menu options according to plan capabilities
$capabilities = Capability::getByPlanId($campaign->plan_id);

// couldn't make the call to add_menu_page() work without first including the file below
require(ABSPATH . '/wp-admin/includes/plugin.php');

if ($capabilities->graphic_material->access) {
    add_menu_page('Material gráfico', 'Material gráfico', 'read', 'graphic_material', function() {
        require(TEMPLATEPATH . '/graphic_material.php');
    });
}

if ($capabilities->contact_manager->access) {
    add_menu_page('Gerenciador de contatos', 'Gerenciador de contatos', 'read', 'contact_manager', function() {
        require(TEMPLATEPATH . '/contact_manager.php');
    });
}

//TODO: check why the last menu entry is not being displayed
if ($capabilities->forum_support->access || $capabilities->email_support->access) {
    add_menu_page('Suporte', 'Suporte', 'read', 'campaign_support', function() {
        require(TEMPLATEPATH . '/campaign_support.php');
    });
}
