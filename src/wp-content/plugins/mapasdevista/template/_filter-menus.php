<?php

add_filter('wp_nav_menu_objects', 'mapasdevista_change_menu_links', 10, 2);

function mapasdevista_change_menu_links($objects, $args) {
    foreach ($objects as $o) {
        
        $o->classes = empty( $o->classes ) ? array() : (array) $o->classes;
        $o->classes[] = 'js-menu-link-to-post';
    
        
    }
    return $objects;
    

}

add_filter('nav_menu_item_id', 'mapasdevista_change_menu_item_ids', 10, 3);

function mapasdevista_change_menu_item_ids($id, $item, $args) {
    return 'menu-item-' . $item->object_id;
}
