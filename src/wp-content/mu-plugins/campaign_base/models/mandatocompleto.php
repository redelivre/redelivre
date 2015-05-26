<?php 

include WP_PLUGIN_DIR.'/blog-copier/blog-copier.php';

if( class_exists('BlogCopier') && $blogId > 1 )
{

	$bgcopier = new BlogCopier();

	$to_blog_id = $blogId;
	
	$from_blog_id = 360;
	
	$bgcopier->copy_blog_data( $from_blog_id, $to_blog_id );
	$bgcopier->copy_blog_files( $from_blog_id, $to_blog_id );
	$bgcopier->replace_content_urls( $from_blog_id, $to_blog_id );
	
}
