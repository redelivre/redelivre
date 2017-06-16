<?php

// add menu options according to plan capabilities
global $capabilities;
$capabilities = Capability::getByPlanId($campaign->plan_id);

add_action('admin_menu', function() {
    global $capabilities;
    
    /*
    if ($capabilities->graphic_material->value) {
        $page = add_menu_page('Material gráfico', 'Material gráfico', 'read', 'graphic_material', function() {
            require(WPMU_PLUGIN_DIR . '/includes/graphic_material.php');
        });
        
        //TODO: refactor to remove the copy and paste code below to create the menu entries for graphic material
        $page = add_submenu_page('graphic_material', 'Santinho e colinha', 'Santinho e colinha', 'read', 'graphic_material_smallflyer', function() {
            global $campaign;

            if ($campaign->isPaid()) {
                require(WPMU_PLUGIN_DIR . '/includes/graphic_material_smallflyer.php');
            } else {
                print_msgs(array('error' => 'A geração de material gráfico é um recurso que está disponível somente para campanhas que já foram pagas.'));
            }
        });
        add_action('admin_print_styles-' . $page, array('GraphicMaterialManager', 'scriptsAndStyles'));
        
        $page = add_submenu_page('graphic_material', 'Flyer', 'Flyer', 'read', 'graphic_material_flyer', function() {
            global $campaign;

            if ($campaign->isPaid()) {
                //require(WPMU_PLUGIN_DIR . '/includes/graphic_material_flyer.php');
            } else {
                print_msgs(array('error' => 'A geração de material gráfico é um recurso que está disponível somente para campanhas que já foram pagas.'));
            }
        });

        add_action('admin_print_styles-' . $page, array('GraphicMaterial', 'scriptsAndStyles'));
        
    }
    */
    
    if (current_user_can('manage_options'))
    {
        $page = add_theme_page('Criar cabeçalho', 'Criar cabeçalho', 'manage_options', 'graphic_material_header', function() {
            require(WPMU_PLUGIN_DIR . '/includes/graphic_material_header.php');
        });
        add_action('admin_print_styles-' . $page, array('GraphicMaterialManager', 'scriptsAndStyles'));

        if(defined('REDELIVRE_ADMIN_MENU_BASE_PAGE'))
        {
        	add_submenu_page(
        		REDELIVRE_ADMIN_MENU_BASE_PAGE,
        		__('Social Network','redelivre'),
        		__('Social Network','redelivre'),
        		'manage_options',
        		'campaign_social_networks',
        		function() {
            		require(WPMU_PLUGIN_DIR . '/includes/admin-social-networks.php');
        		}
        	);
        }
        else
        {
        	add_menu_page(__('Social Network','redelivre'), __('Social Network','redelivre'), 'manage_options', 'campaign_social_networks', function() {
        		require(WPMU_PLUGIN_DIR . '/includes/admin-social-networks.php');
        	});
        }
    }
});
