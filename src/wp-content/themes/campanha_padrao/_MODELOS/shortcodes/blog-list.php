<?php

// [blog-list] [blog-list showcurrent=no] [blog-list showcurrent=yes]
function bloglist_shortcode($atts) {
    if(!function_exists('get_blog_list'))
        return;
    $blogs = get_blog_list();
    if(!$blogs)
        return '';
    
    extract( shortcode_atts( array(
		'showcurrent' => 'no'
	), $atts ) );
    
    
    if($showcurrent == '0' || $showcurrent == 'no' || $showcurrent == 'false')
        $showcurrent = false;
    else
        $showcurrent = true;
    
    ob_start();
    ?>
<ul class="blog-list">
    <?php foreach($blogs as $blog): if($blog['blog_id'] == get_current_blog_id() && !$showcurrent) continue; ?>
    <li><a href="<?php echo get_blog_option($blog['blog_id'], 'siteurl'); ?>"><?php echo get_blog_option($blog['blog_id'], 'blogname'); ?></a></li>
    <?php switch_to_blog($cblog_id); endforeach; ?>
</ul>
    <?php 
    $result = ob_get_clean();
    
    return $result;
}

add_shortcode('blog-list', 'bloglist_shortcode');
?>
