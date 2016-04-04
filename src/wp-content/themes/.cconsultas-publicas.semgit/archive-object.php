<?php

$list_type = get_theme_option('list_type');

if ($list_type == 'title_taxonomy' && !taxonomy_exists('object_type'))
    $list_type = 'title';

require_once('object-list-' . $list_type  . '.php');

?>
