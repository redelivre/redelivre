<?php

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

add_action('wp_print_scripts', 'campanha_add_common_js');
/**
 * Add JS files shared by all themes.
 */
function campanha_add_common_js() {
    if (is_user_logged_in()) {
        wp_enqueue_script('uservoice', site_url() . '/wp-content/mu-plugins/js/uservoice.js', 'jquery');
    }
}
