<?php

function delibera_archive_template( $archive_template )
{
	global $post;
	
	if(get_post_type($post) == "pauta" || is_post_type_archive( 'pauta' ))
	{
		
		if(!file_exists(get_stylesheet_directory()."/delibera_style.css"))
		{
			wp_enqueue_style('delibera_style', WP_CONTENT_URL.'/plugins/delibera/themes/delibera_style.css');
		}
		else
		{
			wp_enqueue_style('delibera_style', get_stylesheet_directory_uri()."/delibera_style.css");
		}
		
		if(!file_exists(get_stylesheet_directory()."/archive-pauta.php"))
		{
			if ( is_post_type_archive ( 'pauta' ) ) {
				$archive_template = dirname( __FILE__ ).DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.'archive-pauta.php';
			}
		}
	}
	return $archive_template;
}

add_filter( 'archive_template', 'delibera_archive_template' ) ;

function delibera_single_template($single_template)
{
	global $post;
	if(get_post_type($post) == "pauta" || is_post_type_archive( 'pauta' ))
	{
		if(!file_exists(get_stylesheet_directory()."/delibera_style.css"))
		{
			wp_enqueue_style('delibera_style', WP_CONTENT_URL.'/plugins/delibera/themes/delibera_style.css');
		}
		else
		{
			wp_enqueue_style('delibera_style', get_stylesheet_directory_uri()."/delibera_style.css");
		}
		
		if(!file_exists(get_stylesheet_directory()."/single-pauta.php"))
		{
			global $post;
			
			if ($post->post_type == 'pauta') {
				$single_template = dirname( __FILE__ ).DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.'single-pauta.php';
			}
		}
	}
	return $single_template;
}

add_filter( "single_template", "delibera_single_template" ) ;

function delibera_themes_admin_print_styles()
{
	if(!file_exists(get_stylesheet_directory()."/delibera_admin.css"))
	{
		wp_enqueue_style('delibera_admin_style', WP_CONTENT_URL.'/plugins/delibera/themes/delibera_admin.css');
	}
	else
	{
		wp_enqueue_style('delibera_admin_style', get_stylesheet_directory_uri()."/delibera_admin.css");
	}
}
add_action('admin_print_styles', 'delibera_print_styles');

require_once __DIR__.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.'delibera_header.php';

function delibera_themes_archive_pauta_loop()
{
	if(!file_exists(get_stylesheet_directory()."/archive-pauta.php"))
	{
		load_template(WP_PLUGIN_DIR.DIRECTORY_SEPARATOR.'delibera'.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.'delibera-loop-archive.php', true);
	}
	else
	{
		get_template_part( 'loop', 'archive' );
	}
}

?>