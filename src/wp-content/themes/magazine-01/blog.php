<?php
/*
Template Name: Listagem dos posts do blog
*/

// Which page of the blog are we on?
$paged = get_query_var('paged');
query_posts('cat=-0&paged=' . $paged);

// make posts print only the first part with a link to rest of the post.
global $more;
$more = 0;

global $wp_query;
$wp_query->is_home = false;

//load index to show blog
load_template(TEMPLATEPATH . '/index.php');
?>
