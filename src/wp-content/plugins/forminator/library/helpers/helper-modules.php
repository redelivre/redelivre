<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Return the array of modules objects
 *
 * @since 1.0
 * @return mixed
 */

function forminator_get_modules() {
	$forminator = Forminator_Core::get_instance();

	return $forminator->modules;
}

/**
 * Return specific module by ID
 *
 * @since 1.0
 * @param $id
 *
 * @return bool
 */
function forminator_get_module( $id ) {
	$modules = forminator_get_modules();

	return isset( $modules[ $id ] ) && ! empty( $modules[ $id] ) ? $modules[ $id] : false;
}
