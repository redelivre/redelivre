<?php

define('REDELIVRE_ADMIN_MENU_BASE_PAGE', 'platform-strings');

add_action('admin_menu', function() {
	$base_page = REDELIVRE_ADMIN_MENU_BASE_PAGE;

	add_menu_page(
		__(Campaign::getStrings('MenuPlataforma'),'redelivre'),
		__(Campaign::getStrings('MenuPlataforma'),'redelivre'),
		'manage_options',
		$base_page,
		array(),
		WPMU_PLUGIN_URL.'/img/plataform.16x16.jpg'
	);

	add_submenu_page($base_page, __('Strings','redelivre'), __('Strings','redelivre'), 'manage_network_options', $base_page, function(){
		require MUCAMPANHAPATH.'/admin-strings-tpl.php';
	});

	add_submenu_page($base_page, __('Settings','redelivre'), __('Settings','redelivre'), 'manage_options', 'platform-settings', function(){
		require MUCAMPANHAPATH.'/admin-settings-tpl.php';
	});


    add_menu_page(Campaign::getStrings('MenuPrincipal'), Campaign::getStrings('MenuPrincipal'), 'manage_options', 'campaigns', function() {
        require MUCAMPANHAPATH.'/includes/campaigns.php';
    });
    
    add_submenu_page('campaigns', Campaign::getStrings('NovoProjeto'), Campaign::getStrings('NovoProjeto'), 'manage_options', 'campaigns_new', function() {
        require MUCAMPANHAPATH.'/includes/campaigns_new.php';
    });
    	
});
