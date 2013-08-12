<?php 

// admin_bar removal
//wp_deregister_script('admin-bar');
//wp_deregister_style('admin-bar');
remove_action('wp_footer','wp_admin_bar_render',1000);
function remove_admin_bar(){
   return false;
}
add_filter( 'show_admin_bar' , 'remove_admin_bar');

// EXCERPT MORE

add_filter('utils_excerpt_more_link', 'campanha_utils_excerpt_more',10,2);
function campanha_utils_excerpt_more($more_link, $post){
	return '...<br /><a class="more-link" href="'. get_permalink($post->ID) . '">' . __('Continue reading &raquo;', 'campanha') . '</a>';
}


add_filter( 'excerpt_more', 'campanha_auto_excerpt_more' );
function campanha_auto_excerpt_more( $more ) {
	global $post;
	return '...<br /><a class="more-link" href="'. get_permalink($post->ID) . '">' . __('Continue reading &raquo;', 'campanha') . '</a>';
}

?>