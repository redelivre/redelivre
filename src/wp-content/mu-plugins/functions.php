<?php

// load code used only for campaign sites (exclude main site)
if (!is_main_site()) {
    require(dirname(__FILE__) . '/includes/payment.php');
}

function print_msgs($msg, $extra_class='', $id='') {
    if (!is_array($msg)) {
        return false;
    }

    foreach ($msg as $type => $msgs) {
        if (!$msgs) {
            continue;
        }
        
        echo "<div class='$type $extra_class' id='$id'><ul>";
        
        if (!is_array($msgs)) {
            echo "<li>$msgs</li>";
        } else {
            foreach ($msgs as $m) {
                echo "<li>$m</li>";
            }
        }
        
        echo "</ul></div>";
    }
}

/**
 * Return the URL to mu-plugins directory.
 * 
 * @return string
 */
function get_muplugins_url() {
    return plugins_url('', __FILE__);
}

add_action('wp_print_scripts', 'campanha_add_common_js');
/**
 * Add JS files shared by all themes.
 */
function campanha_add_common_js() {
    if (is_user_logged_in()) {
        wp_enqueue_script('uservoice', site_url() . '/wp-content/mu-plugins/js/uservoice.js', 'jquery');
    }
}
