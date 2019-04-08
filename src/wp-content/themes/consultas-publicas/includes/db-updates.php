<?php

// adiciona meta _user_created como false para os posts que não foram criados pelos usuários para facilitar distinção 
if (congelado_db_update('db-update-1')) {
    $posts = get_posts(array('post_type' => 'object', 'posts_per_page' => -1));
    
    foreach ($posts as $post) {
        if (!get_post_meta($post->ID, '_user_created', true)) {
            update_post_meta($post->ID, '_user_created', false);
        }
    }
}