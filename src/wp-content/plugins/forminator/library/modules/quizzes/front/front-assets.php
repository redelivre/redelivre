<?php
/**
 * Conditionally load assets class
 *
 * @since 1.12
 */
class Forminator_Assets_Enqueue_Quiz extends Forminator_Assets_Enqueue {
	/**
	 * Load scripts and styles on front-end
	 *
	 * @since 1.12
	 */
	public function load_assets() {
		$this->enqueue_styles();
		$this->enqueue_scripts();
	}

	/**
	 * Enqueue form styles
	 *
	 * @since 1.12
	 */
	public function enqueue_styles() {

		$form_settings = $this->get_settings();
		$form_design   = isset( $form_settings['forminator-quiz-theme'] ) ? $form_settings['forminator-quiz-theme'] : '';

		// Forminator UI - Icons font.
		wp_enqueue_style(
			'forminator-icons',
			forminator_plugin_url() . 'assets/forminator-ui/css/forminator-icons.min.css',
			array(),
			FORMINATOR_VERSION
		);

		// Forminator UI - Utilities.
		wp_enqueue_style(
			'forminator-utilities',
			forminator_plugin_url() . 'assets/forminator-ui/css/src/forminator-utilities.min.css',
			array(),
			FORMINATOR_VERSION
		);

		if ( 'none' !== $form_design ) {

			wp_enqueue_style(
				'forminator-quiz-' . $form_design . '-base',
				forminator_plugin_url() . 'assets/forminator-ui/css/src/quiz/forminator-quiz-' . $form_design . '.base.min.css',
				array(),
				FORMINATOR_VERSION
			);
		}
	}

	/**
	 * Enqueue form scripts
	 *
	 * @since 1.11
	 */
	public function enqueue_scripts() {
		// Load form base scripts.
		$this->load_base_scripts();
	}

	/**
	 * Load base from scripts
	 *
	 * @since 1.11
	 */
	public function load_base_scripts() {
		// LOAD: Forminator validation scripts
		wp_enqueue_script( 'forminator-jquery-validate', forminator_plugin_url() . 'assets/js/library/jquery.validate.min.js', array( 'jquery' ), FORMINATOR_VERSION, false );


		// LOAD: Forminator UI JS
		wp_enqueue_script(
			'forminator-ui',
			forminator_plugin_url() . 'assets/forminator-ui/js/forminator-ui.min.js',
			array( 'jquery' ),
			FORMINATOR_VERSION,
			false
		);

		// LOAD: Forminator front scripts
		wp_enqueue_script(
			'forminator-front-scripts',
			forminator_plugin_url() . 'build/front/front.multi.min.js',
			array( 'jquery', 'forminator-ui', 'forminator-jquery-validate' ),
			FORMINATOR_VERSION,
			false
		);

		// Localize front script
		wp_localize_script( 'forminator-front-scripts', 'ForminatorFront', forminator_localize_data() );
	}
}
