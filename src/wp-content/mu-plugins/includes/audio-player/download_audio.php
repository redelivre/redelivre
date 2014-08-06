<?php

$file_url = $_GET['file_url'];
    
$clean_name = str_replace(dirname($file_url) . '/', '', $file_url);
$clean_name = preg_replace('/(-[0-9a-f]{13}.mp3)$/', '.MP3', $clean_name);

if ($file_url) {
    
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header("Content-Type: 'audio/mpeg3'");
    header("Content-Disposition: attachment; \"filename=$clean_name\"");
    header("Content-Transfer-Encoding: binary");

    readfile($file_url);
    
}

?>
