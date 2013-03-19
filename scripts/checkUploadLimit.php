<?php

$_SERVER['HTTP_HOST'] = 'campanhacompleta.com.br';

require_once(__DIR__ . '/../src/wp-load.php');

$campaigns = $wpdb->get_results('SELECT * FROM campaigns');
$plans = array(2 => 1000, 3 => 2000, 4 => 3000, 5 => -1, 6 => 500);

foreach ($campaigns as $campaign) {
    $uploadLimit = $wpdb->get_var("SELECT option_value FROM wp_{$campaign->blog_id}_options WHERE option_name = 'blog_upload_space'");
    
    if ($plans[$campaign->plan_id] != $uploadLimit) {
        var_dump("Erro: valor deveria ser {$plans[$campaign->plan_id]} e é '$uploadLimit' para {$campaign->domain} ({$campaign->blog_id})");
    }
}
