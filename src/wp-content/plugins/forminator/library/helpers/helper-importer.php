<?php

/**
 * Return status of external plugins for Import/export feature
 *
 * If this function return false, Import/export feature for the 
 * plugin will be disabled
 *
 * @since 1.7
 * @param  string $plugin name of the plugin
 * @return bool
 */
function forminator_is_import_plugin_enabled( $plugin ) {
	// enable import export feature for entire planet by default
	$active = false;

	switch ($plugin) {
		case 'cf7':
			if( function_exists( 'wpcf7_contact_form' ) ) $active = true;
			break;		
		case 'ninjaforms':
			if( class_exists( 'Ninja_Forms' ) ) $active = false;
			break;
		case 'gravityforms':
			if( class_exists( 'GFForms' ) ) $active = false;
			break;
		
		default:
			# code...
			break;
	}
	
	/**
	 * Filter the status of Import/export feature
	 *
	 * @since 1.4
	 *
	 * @param bool $active current status of the plugin
	 */
	$active = apply_filters( 'forminator_is_import_export_feature_enabled', $active );

	return $active;
}

/**
 * Get plugin deactivation link
 *
 * @param $plugin
 *
 * @since 1.7
 *
 * @return bool
 */
function forminator_get_disable_url( $plugin, $slug ) {
	if ( ! forminator_is_import_plugin_enabled( $plugin ) ) {
		return false;
	}

	return wp_nonce_url( 'plugins.php?action=deactivate&amp;plugin=' . $slug . '&amp;plugin_status=all', 'deactivate-plugin_' . $slug );
}


/**
 * Return status of external plugins for Import/export feature
 *
 * If this function return false, Import/export feature for the 
 * plugin will be disabled
 *
 * @since 1.7
 * @param  string $plugin name of the plugin
 * @return bool
 */
function forminator_are_import_plugins_enabled( $plugins = array() ) {

	$active = false;
	foreach ($plugins as $key => $plugin) {
		if( true === $active ) break; 

		$active = forminator_is_import_plugin_enabled( $plugin );
	}

	$active = apply_filters( 'forminator_are_import_plugins_enabled', $active );

	return $active;
}

/**
 * Return all the contact forms from thirdparties
 *
 *
 * @since 1.7
 * @return array list of forms
 */
function forminator_list_thirdparty_contact_forms( $type ){
	//get all forms
	$forms = array();
	switch ($type) {
		case 'cf7':
			$forms = get_posts( array(
				'post_type' => 'wpcf7_contact_form', 
				'posts_per_page' => -1
			) );
		break;		
		case 'ninjaforms':
			$forms = Ninja_Forms()->form()->get_forms();
			break;		
		case 'gravityforms':
			$forms = GFAPI::get_forms();
			break;
	}

	return $forms;
}
