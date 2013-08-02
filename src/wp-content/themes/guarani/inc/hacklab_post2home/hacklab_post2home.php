<?php
//********* SELECIONA DESTAQUES DA HOME *****************//

add_action('manage_posts_custom_column', 'hacklab_post2home_select', 10, 2);
add_filter('manage_posts_columns','hacklab_post2home_add_column');
add_action('manage_pages_custom_column', 'hacklab_post2home_select', 10, 2);
add_filter('manage_pages_columns','hacklab_post2home_add_column');
add_action('load-edit.php', 'hacklab_post2home_JS');
add_action('load-edit-pages.php', 'hacklab_post2home_JS');

function hacklab_post2home_add_column($defaults){
    global $post_type;
    if ('post' == $post_type || 'page' == $post_type)
        $defaults['destaques'] = 'Destaque';
    return $defaults;
}

function hacklab_post2home_select($column_name, $id){

    if ($column_name=="destaques"){
        $highlighted = get_post_meta($id, "_home", true) == 1 ?  "checked" : "";
    ?>  
        <input type="checkbox" class="hacklab_post2home_button" id="hacklab_post2home_<?php echo $id; ?>" <?php echo $highlighted; ?>>
    <?php
    }
}

function hacklab_post2home_JS() {
	wp_enqueue_script('hacklab_post2home', get_template_directory_uri() . '/inc/hacklab_post2home/admin.js', array('jquery'));
	wp_enqueue_style('hacklab_post2home', get_template_directory_uri() . '/inc/hacklab_post2home/post2home.css');
	wp_localize_script('hacklab_post2home', 'hacklab', array('ajaxurl' => admin_url('admin-ajax.php') ));
}


//********* FIM SELECIONA DESTAQUES DA HOME *****************//

add_action('wp_ajax_destaque_add', 'hacklab_post2home_add');
add_action('wp_ajax_destaque_remove', 'hacklab_post2home_remove');

function hacklab_post2home_add() {
    update_post_meta($_POST['post_id'], '_home', 1);
    echo 'ok';
    die;
}

function hacklab_post2home_remove() {
    delete_post_meta($_POST['post_id'], '_home');
    echo 'ok';
    die;
}

?>
