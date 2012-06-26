<?php

global $wp_post_types;

$obj = get_queried_object();

$mapinfo = get_option('mapasdevista', true);

global $current_map_page_id;
$current_map_page_id = 1;
