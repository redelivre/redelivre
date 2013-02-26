<?php

function delibera_archive_template( $archive_template )
{
	if(!file_exists(get_stylesheet_directory()."/archive-pauta.php"))
	{
		global $post;
		
		wp_enqueue_style('delibera_style', WP_CONTENT_URL.'/plugins/delibera/themes/delibera_style.css');
	
		if ( is_post_type_archive ( 'pauta' ) ) {
			$archive_template = dirname( __FILE__ ).DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.'archive-pauta.php';
		}
	}
	return $archive_template;
}

add_filter( 'archive_template', 'delibera_archive_template' ) ;

function delibera_single_template($single_template)
{
	if(!file_exists(get_stylesheet_directory()."/single-pauta.php"))
	{
		global $post;
		
		wp_enqueue_style('delibera_style', WP_CONTENT_URL.'/plugins/delibera/themes/delibera_style.css');
	
		if ($post->post_type == 'pauta') {
			$single_template = dirname( __FILE__ ).DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.'single-pauta.php';
		}
	}
	return $single_template;
}

add_filter( "single_template", "delibera_single_template" ) ;



?>