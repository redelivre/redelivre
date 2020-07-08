<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Dashboard_Page
 *
 * @since 1.0
 */
class Forminator_Dashboard_Page extends Forminator_Admin_Page {

	/**
	 * Register content boxes
	 *
	 * @since 1.0
	 */
	public function register_content_boxes() {
		$this->add_box(
			'dashboard/create',
			__( 'Create Modules', Forminator::DOMAIN ),
			'dashboard-create',
			null,
			array( $this, 'dashboard_create_screen' ),
			null
		);
	}

	/**
	 * Print Dashboard box
	 *
	 * @since 1.0
	 */
	public function dashboard_create_screen() {
		$modules = forminator_get_modules();
		$this->template(
			'dashboard/create-content',
			array(
				'modules' => $modules,
			)
		);
	}

	/**
	 * Return admin edit url
	 *
	 * @since 1.6
	 * @param $type
	 * @param $id
	 *
	 * @return mixed
	 */
	public function getAdminEditUrl( $type, $id ) {
		if ( 'nowrong' === $type ) {
			return admin_url( 'admin.php?page=forminator-nowrong-wizard&id=' . $id );
		} else {
			return admin_url( 'admin.php?page=forminator-knowledge-wizard&id=' . $id );
		}
	}

	/**
	 * Count modules
	 *
	 * @since 1.6
	 * @return int
	 */
	public function countModules( $status = '' ) {
		return Forminator_Custom_Form_Model::model()->count_all( $status );
	}

	/**
	 * Return all forms containing Stripe field
	 *
	 * @since 1.9
	 *
	 * @return array
	 */
	public function stripeModules() {
		return Forminator_Custom_Form_Model::model()->get_models_by_field_and_version( 'stripe-1', '1.9-alpha.1' );
	}

	/**
	 * Override scripts to be loaded
	 *
	 * @since 1.11
	 *
	 * @param $hook
	 */
	public function enqueue_scripts( $hook ) {
		parent::enqueue_scripts( $hook );

		forminator_print_forms_admin_styles( FORMINATOR_VERSION );
		forminator_print_polls_admin_styles( FORMINATOR_VERSION );
		forminator_print_front_styles( FORMINATOR_VERSION );

		forminator_print_front_scripts( FORMINATOR_VERSION );
	}
}
