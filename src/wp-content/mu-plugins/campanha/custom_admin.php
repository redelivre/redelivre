<?php

add_action('admin_menu', function() {
	$base_page = 'rede-livre';

	add_object_page( __(Campaign::getStrings('MenuPlataforma'),'redelivre'), __(Campaign::getStrings('MenuPlataforma'),'redelivre'), 'manage_options', $base_page, array());

	add_submenu_page($base_page, __('Strings','redelivre'), __('Strings','redelivre'), 'manage_options', $base_page, function(){
		require MUCAMPANHAPATH.'/admin-strings-tpl.php';
	});

	add_submenu_page($base_page, __('Settings','redelivre'), __('Settings','redelivre'), 'manage_options', 'redelivre', function(){
		require MUCAMPANHAPATH.'/admin-settings-tpl.php';
	});


    add_menu_page(Campaign::getStrings('MenuPrincipal'), Campaign::getStrings('MenuPrincipal'), 'read', 'campaigns', function() {
        require MUCAMPANHAPATH.'/includes/campaigns.php';
    });
    
    add_submenu_page('campaigns', Campaign::getStrings('NovoProjeto'), Campaign::getStrings('NovoProjeto'), 'read', 'campaigns_new', function() {
        require MUCAMPANHAPATH.'/includes/campaigns_new.php';
    });
});
