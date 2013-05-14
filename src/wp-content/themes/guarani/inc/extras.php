<?php
/**
 * Custom functions & tweaks
 *
 * @package Guarani
 * @since Guarani 1.0
 */


/**
 * Filters wp_title to print a neat <title> tag based on what is being viewed.
 *
 * @since Guarani 1.0
 */
function guarani_wp_title( $title, $sep ) {
	global $page, $paged;

	if ( is_feed() )
		return $title;

	// Add the blog name
	$title .= get_bloginfo( 'name' );

	// Add the blog description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		$title .= " $sep $site_description";

	// Add a page number if necessary:
	if ( $paged >= 2 || $page >= 2 )
		$title .= " $sep " . sprintf( __( 'Page %s', '_s' ), max( $paged, $page ) );

	return $title;
}
add_filter( 'wp_title', 'guarani_wp_title', 10, 2 );


/**
 * Adds new body classes
 *
 * @since Guarani 1.0 
 */
function guarani_body_class( $classes ) {

	// Adds a class of group-blog to blogs with more than 1 published author
	if ( is_multi_author() )
		$classes[] = 'group-blog';

	// The Scheme class
	$classes[] = 'scheme-' . get_theme_mod( 'guarani_color_scheme' );
	
	return $classes;
}
add_action( 'body_class', 'guarani_body_class' );


/**
 * Add new post classes
 * 
 * @since Guarani 1.0 
 */
function guarani_post_class( $classes ) {
	
	global $post;
	
	// If has post format
	if ( get_post_format( $post->ID ) !== false )
		$classes[] = 'has-format';
	
	return $classes;
	
}
add_filter( 'post_class', 'guarani_post_class' );


/**
 * Get our wp_nav_menu() fallback, wp_page_menu(), to show a home link.
 *
 * @since Guarani 1.0
 */
function guarani_page_menu_args( $args ) {
	$args['show_home'] = true;
	return $args;
}
add_filter( 'wp_page_menu_args', 'guarani_page_menu_args' );


/**
 * Change the default args for the Tag Cloud Widget
 *
 * Units in 'em', same font-size always, order by tag count DESC
 *
 * @param array $args The default arguments
 * @return array $args New arguments
 *
 * @since Guarani 1.0
 */
function guarani_tag_cloud( $args ) {

	$args['format'] = 'list';
	$args['unit'] = 'em';
	$args['largest'] = 1;
	$args['smallest'] = 1;
	$args['orderby'] = 'count';
	$args['order'] = 'DESC';
	
	return $args;

}
add_filter( 'widget_tag_cloud_args', 'guarani_tag_cloud' );


/**
 * Change the excerpt size for the front-page
 * 
 * @since Guarani 1.0
 */
function guarani_excerpt_length() {
	return 30;
}
add_filter( 'excerpt_length', 'guarani_excerpt_length', 999 );


/**
 * Replaces the default end of excerpt with a proper ellipsis
 * 
 * @since Guarani 1.0
 */
function guarani_trim_excerpt( $excerpt ) {
	return str_replace( '[...]', '&hellip;', $excerpt );
}
add_filter( 'wp_trim_excerpt', 'guarani_trim_excerpt' );


/**
 * Add different icons for registered post formats
 * 
 * @since Guarani 1.0
 */
function guarani_post_thumbnail( $html, $post_id ) {
	
	if ( has_post_format( 'video', $post_id ) )
		return '<span aria-hidden="true" class="icon-camera"></span>' . $html;
	
	return $html;
}
//add_filter( 'post_thumbnail_html', 'guarani_post_thumbnail', 10, 2 );


/**
 * Add new user fields
 * 
 * @since Guarani 1.0
 */
function guarani_contactmethods( $user_contactmethods ) {
  
	// We don't need these
	unset( $user_contactmethods['yim'] );
	unset( $user_contactmethods['aim'] );
	unset( $user_contactmethods['jabber'] );
	
	// Add trendy ones
	$user_contactmethods['twitter'] = 'Twitter';
	$user_contactmethods['facebook'] = 'Facebook';
	$user_contactmethods['googleplus'] = 'Google+';
	
	return $user_contactmethods;
  
}
add_filter( 'user_contactmethods', 'guarani_contactmethods' );


/**
 * Add a new RSS icon for RSS Widget
 * 
 * @since Guarani 1.0
 */
function guarani_rss_widget_icon( $title, $instance, $id ) {
	
	if ( $id == 'rss' )
		return '<span aria-hidden="true" class="icon-rss"></span>' . $title;
		
	return $title;	
}
//add_filter( 'widget_title', 'guarani_rss_widget_icon', 10, 3 );
?>