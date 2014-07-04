<?php

/**
 * Transfer theme data into new settings
 */
function siteorigin_panels_transfer_home_page(){
	if(get_option('siteorigin_panels_home_page', false) === false && get_theme_mod('panels_home_page', false) !== false) {
		// Transfer settings from theme mods into settings
		update_option( 'siteorigin_panels_home_page', get_theme_mod( 'panels_home_page', false ) );
		update_option( 'siteorigin_panels_home_page_enabled', get_theme_mod( 'panels_home_page_enabled', false ) );

		// Remove the theme mod data
		remove_theme_mod( 'panels_home_page' );
		remove_theme_mod( 'panels_home_page_enabled' );
	}
}
add_action('admin_init', 'siteorigin_panels_transfer_home_page');