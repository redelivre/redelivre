<?php

$content = get_the_content();

$reg = '|(http://[^ "\']+.mp3)|';
    
$audio = false;

$matches = preg_match_all($reg, $content, $m);

if (is_array($m) && isset($m[0]) && is_array($m[0]) && isset($m[0][0]) ) {
    $audio = $m[0][0];
}

echo apply_filters('the_content', '[wpaudio url="'.$audio.'" dl="0"]');

?>
