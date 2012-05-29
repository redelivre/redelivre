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

