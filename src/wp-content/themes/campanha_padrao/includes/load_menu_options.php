<?php

// add menu options according to plan capabilities
$capabilities = Capability::getByPlanId($campaign->plan_id);

add_action('admin_menu', function() {
    global $capabilities;
    
    if ($capabilities->graphic_material->value) {
        $page = add_menu_page('Material gráfico', 'Material gráfico', 'read', 'graphic_material', function() {
            global $campaign;

            if ($campaign->isPaid()) {
                require(TEMPLATEPATH . '/includes/graphic_material.php');
            } else {
                print_msgs(array('error' => 'A geração de material gráfico é um recurso que está disponível somente para campanhas que já foram pagas.'));
            }
        });
        require_once(TEMPLATEPATH . '/includes/graphic_material/GraphicMaterial.php');
        add_action('admin_print_styles-' . $page, array('GraphicMaterial', 'scriptsAndStyles'));
        
        wp_enqueue_script('jquery-ui-draggable');
        add_submenu_page('graphic_material', 'Fotos', 'Fotos', 'read', 'graphic_material_photos', function(){
            require(TEMPLATEPATH . '/includes/graphic_material_photos.php');
        });
    }
    
    if ($capabilities->contact_manager->value) {
        add_menu_page('Gerenciador de contatos', 'Gerenciador de contatos', 'read', 'contact_manager', function() {
            require(TEMPLATEPATH . '/includes/contact_manager.php');
        });
    }
    
    //TODO: check why the last menu entry is not being displayed
    if ($capabilities->forum_support->value || $capabilities->email_support->value) {
        add_menu_page('Suporte', 'Suporte', 'read', 'campaign_support', function() {
            require(TEMPLATEPATH . '/includes/campaign_support.php');
        });
    }
});