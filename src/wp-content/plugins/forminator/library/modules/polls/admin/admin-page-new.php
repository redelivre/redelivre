<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Poll_New_Page
 *
 * @since 1.0
 */
class Forminator_Poll_New_Page extends Forminator_Admin_Page {

	/**
	 * Return wizard title
	 *
	 * @since 1.0
	 * @return mixed
	 */
	public function getWizardTitle() {
		if ( isset( $_REQUEST['id'] ) ) { // WPCS: CSRF OK
			return __( "Edit Poll", Forminator::DOMAIN );
		} else {
			return __( "New Poll", Forminator::DOMAIN );
		}
	}

	/**
	 * Add page screen hooks
	 *
	 * @since 1.6.1
	 *
	 * @param $hook
	 */
	public function enqueue_scripts( $hook ) {
		// Load jquery ui
		forminator_admin_jquery_ui();

		// Load shared-ui scripts
		forminator_sui_scripts();

		// Load admin fonts
		forminator_admin_enqueue_fonts( FORMINATOR_VERSION );

		// Load admin styles
		forminator_admin_enqueue_styles( FORMINATOR_VERSION );

		$forminator_data = new Forminator_Admin_Data();
		$forminator_l10n = new Forminator_Admin_L10n();

		// Load admin scripts
		forminator_admin_enqueue_scripts_polls(
			FORMINATOR_VERSION,
			$forminator_data->get_options_data(),
			$forminator_l10n->get_l10n_strings()
		);

		// Load front scripts for preview_form
		forminator_print_polls_admin_styles( FORMINATOR_VERSION );
		forminator_print_front_scripts( FORMINATOR_VERSION );
	}

	/**
	 * Render page header
	 *
	 * @since 1.6.1
	 */
	protected function render_header() { ?>
		<?php
		if ( $this->template_exists( $this->folder . '/header' ) ) {
			$this->template( $this->folder . '/header' );
		} else {
			?>
			<h1 class="sui-header-title"><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<?php } ?>
		<?php
	}
}
