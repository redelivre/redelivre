<?php

add_action('admin_menu', function() {
    add_menu_page('Administrar campanhas', 'Administrar campanhas', 'read', 'campaigns', function() {
        require(TEMPLATEPATH . '/campaigns.php');
    });
});
