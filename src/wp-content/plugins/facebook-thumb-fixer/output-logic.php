<?php
global $post;
$featuredimg 			= wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), "Full");
$ftf_description		= get_the_excerpt($post);
$ot 					= get_post_meta($post->ID, 'ftf_open_type', true);
$title 					= get_the_title($post->ID);
$blog_name 				= get_bloginfo('name');
$post_page_description 	= wp_kses($ftf_description, array ());
$home_description 		= get_bloginfo('description');
$homepage_object_type	= get_option( 'homepage_object_type');
$post_page_object_type  = get_post_meta($post->ID, 'ftf_open_type', true);
$dog                    = get_post_meta($post->ID, "disable_open_graph", TRUE);

if($post_page_description) {
    $post_page_description = $post_page_description;
} else {
    $post = get_post($post->ID);
    $content = apply_filters('get_the_content', $post->post_content);
    $content_chars = substr($content, 0, 300);
    $page_description = strip_tags($content_chars);
    $post_page_description = str_replace(array("\n", "\t", "\r"), '', $page_description);
}

if($homepage_object_type) { 
    $homepage_object_type = get_option( 'homepage_object_type');
} else {
    $homepage_object_type = "website";
}

if($post_page_object_type) { 
    $post_page_object_type = get_post_meta($post->ID, 'ftf_open_type', true);
} else {
    $post_page_object_type = "article";
}

// If not the homepage
if ( !is_home() ) {

    // If not disabled
    if($dog !== "1") {

        // If there is a post image...
        if (has_post_thumbnail()) {
            
            $ftf_head = '
            <!--/ Facebook Thumb Fixer Open Graph /-->
            <meta property="og:type" content="'. $post_page_object_type . '" />
            <meta property="og:url" content="' . get_permalink() . '" />
            <meta property="og:title" content="' . str_replace('"', '', $title) . '" />
            <meta property="og:description" content="' . strip_shortcodes(str_replace('"', '', $post_page_description)) . '" />
            <meta property="og:site_name" content="' . str_replace('"', '', $blog_name) . '" />
            <meta property="og:image" content="' . $featuredimg[0] . '" />

            <meta itemscope itemtype="'. $post_page_object_type . '" />
            <meta itemprop="description" content="' . strip_shortcodes(str_replace('"', '', $post_page_description)) . '" />
            <meta itemprop="image" content="' . $featuredimg[0] . '" />
            ';
        } else { //...otherwise, if there is no post image.
            $ftf_head = '
            <!--/ Facebook Thumb Fixer Open Graph /-->
            <meta property="og:type" content="'. $post_page_object_type . '" />
            <meta property="og:url" content="' . get_permalink() . '" />
            <meta property="og:title" content="' . str_replace('"', '', $title) . '" />
            <meta property="og:description" content="' . strip_shortcodes(str_replace('"', '', $post_page_description)) . '" />
            <meta property="og:site_name" content="' . str_replace('"', '', $blog_name) . '" />
            <meta property="og:image" content="' . get_option('default_fb_thumb') . '" />

            <meta itemscope itemtype="'. $post_page_object_type . '" />
            <meta itemprop="description" content="' . strip_shortcodes(str_replace('"', '', $post_page_description)) . '" />
            <meta itemprop="image" content="' . get_option('default_fb_thumb') . '" />
            ';
        }

    } // End if not disabled

} else { //...otherwise, it must be the homepage so do this:

    $ftf_head = '
    <!--/ Facebook Thumb Fixer Open Graph /-->
    <meta property="og:type" content="' . $homepage_object_type . '" />
    <meta property="og:url" content="' . get_option('home') . '" />
    <meta property="og:title" content="' . str_replace('"', '', $blog_name) . '" />
    <meta property="og:description" content="' . strip_shortcodes(str_replace('"', '', $home_description)) . '" />
    <meta property="og:site_name" content="' . str_replace('"', '', $blog_name) . '" />
    <meta property="og:image" content="' . get_option('default_fb_thumb') . '" />

    <meta itemscope itemtype="'. $homepage_object_type . '" />
    <meta itemprop="description" content="' . strip_shortcodes(str_replace('"', '', $home_description)) . '" />
    <meta itemprop="image" content="' . get_option('default_fb_thumb') . '" />
    ';
}