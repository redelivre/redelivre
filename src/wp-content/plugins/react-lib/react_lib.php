<?php
/**
 * Plugin Name: React Lib
 * Description: Add missing react and react-dom for older WP versions.
 */

function add_react() {
	if ( ! wp_script_is( 'react', 'registered' ) ) {
		wp_register_script( 'react', plugins_url( 'react.min.js', __FILE__ ) );
	}

	if ( ! wp_script_is( 'react-dom', 'registered' ) ) {
		wp_register_script( 'react-dom', plugins_url( 'react-dom.min.js', __FILE__ ) );
	}
}

add_action( 'init', 'add_react' );
