<?php

add_action('admin_menu', function() {
	$base_page = 'rede-livre';

	add_object_page( __('Rede Livre','redelivre'), __('Rede Livre','redelivre'), 'manage_options', $base_page, array());

	add_submenu_page($base_page, __('Strings','redelivre'), __('Strings','redelivre'), 'manage_options', $base_page, function(){
		require MUCAMPANHAPATH.'/admin-strings-tpl.php';
	});

	add_submenu_page($base_page, __('Settings','redelivre'), __('Settings','redelivre'), 'manage_options', 'redelivre', function(){
		require MUCAMPANHAPATH.'/admin-settings-tpl.php';
	});


    add_menu_page('Projetos', 'Projetos', 'read', 'campaigns', function() {
        require MUCAMPANHAPATH.'/includes/campaigns.php';
    });
    
    add_submenu_page('campaigns', 'Novo Projeto', 'Novo Projeto', 'read', 'campaigns_new', function() {
        require MUCAMPANHAPATH.'/includes/campaigns_new.php';
    });
});
