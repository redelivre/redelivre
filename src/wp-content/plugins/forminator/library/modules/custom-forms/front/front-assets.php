<?php
/**
 * Conditionally load assets class
 *
 * @since 1.11
 */
class Forminator_Assets_Enqueue_Form extends Forminator_Assets_Enqueue {
	/**
	 * Load scripts and styles on front-end
	 *
	 * @since 1.11
	 */
	public function load_assets() {
		$this->enqueue_styles();
		$this->enqueue_scripts();
	}

	/**
	 * Enqueue form styles
	 *
	 * @since 1.11
	 */
	public function enqueue_styles() {

		$form_design   = $this->get_module_design();
		$form_settings = $this->get_settings();

		$has_phone_validate      = $this->has_field_type_with_setting_value( 'phone', 'validation', 'true' );
		$has_phone_national      = $this->has_field_type_with_setting_value( 'phone', 'phone_validation_type', 'standard' );
		$has_phone_international = $this->has_field_type_with_setting_value( 'phone', 'phone_validation_type', 'international' );
		$has_phone_settings      = ( $has_phone_validate && ( $has_phone_national || $has_phone_international ) );

		$has_address_country     = $this->has_field_type_with_setting_value( 'address', 'address_country', 'true' );

		$has_select_single       = $this->has_field_type_with_setting_value( 'select', 'value_type', 'single' );
		$has_select_multiple     = $this->has_field_type_with_setting_value( 'select', 'value_type', 'multiselect' );
		$has_select_search       = $this->has_field_type_with_setting_value( 'select', 'search_status', 'enable' );
		$has_select_settings     = ( $has_select_single && $has_select_search );

		$has_datepicker          = $this->has_field_type_with_setting_value( 'date', 'field_type', 'picker' );

		$has_timepicker          = $this->has_field_type( 'time' );

		$has_uploader            = $this->has_field_type( 'upload' );

		$has_post_feat_image     = $this->has_field_type_with_setting_value( 'postdata', 'post_image', true );
		$has_post_categories     = $this->has_field_type_with_setting_value( 'postdata', 'category', true );
		$has_post_tags           = $this->has_field_type_with_setting_value( 'postdata', 'post_tag', true );
		$has_multi_categories    = $this->has_field_type_with_setting_value( 'postdata', 'category_multiple', '1' );
		$has_multi_tags          = $this->has_field_type_with_setting_value( 'postdata', 'post_tag_multiple', '1' );

		$has_currency            = $this->has_field_type( 'currency' );
		$has_paypal              = $this->has_field_type( 'paypal' );
		$has_stripe              = $this->has_field_type( 'stripe' );

		$has_password            = $this->has_field_type( 'password' );

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

		// Forminator UI - Grid.
		if ( isset( $form_settings['fields-style'] ) && 'open' === $form_settings['fields-style'] ) {

			wp_enqueue_style(
				'forminator-grid-default',
				forminator_plugin_url() . 'assets/forminator-ui/css/src/grid/forminator-grid.open.min.css',
				array(),
				FORMINATOR_VERSION
			);
		} else if ( isset( $form_settings['fields-style'] ) && 'enclosed' === $form_settings['fields-style'] ) {

			wp_enqueue_style(
				'forminator-grid-enclosed',
				forminator_plugin_url() . 'assets/forminator-ui/css/src/grid/forminator-grid.enclosed.min.css',
				array(),
				FORMINATOR_VERSION
			);
		}

		// Forminator UI - Base stylesheet.
		if ( 'none' !== $form_design ) {

			wp_enqueue_style(
				'forminator-forms-' . $form_design . '-base',
				forminator_plugin_url() . 'assets/forminator-ui/css/src/form/forminator-form-' . $form_design . '.base.min.css',
				array(),
				FORMINATOR_VERSION
			);

			// Forminator UI - Full stylesheet.
			if ( $has_phone_settings || $has_address_country || $has_select_multiple || $has_select_settings || $has_datepicker || $has_timepicker || $has_uploader || $has_post_feat_image || ( $has_post_categories && $has_multi_categories ) || ( $has_post_tags && $has_multi_tags ) || $has_currency || $has_paypal || $has_stripe ) {
				wp_enqueue_style(
					'forminator-forms-' . $form_design . '-full',
					forminator_plugin_url() . 'assets/forminator-ui/css/src/form/forminator-form-' . $form_design . '.full.min.css',
					array(),
					FORMINATOR_VERSION
				);
			}

			// Forminator UI - Pagination stylesheet.
			if ( $this->has_field_type( 'page-break' ) ) {

				wp_enqueue_style(
					'forminator-forms-' . $form_design . '-pagination',
					forminator_plugin_url() . 'assets/forminator-ui/css/src/form/forminator-form-' . $form_design . '.pagination.min.css',
					array(),
					FORMINATOR_VERSION
				);
			}
		}

		// Forminator UI - Select2 stylesheet.
		if ( $has_address_country || $has_select_settings ) {

			wp_enqueue_style(
				'select2',
				forminator_plugin_url() . 'assets/forminator-ui/css/src/form/select2.min.css',
				array(),
				FORMINATOR_VERSION
			);
		}

		// Forminator UI - Authentication stylesheet.
		if ( $has_password ) {

			wp_enqueue_style(
				'forminator-authentication',
				forminator_plugin_url() . 'assets/forminator-ui/css/src/form/forminator-authentication.min.css',
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

		// FIELD: Phone.
		if ( $this->has_field_type( 'phone' ) ) {
			$this->load_phone_scripts();
		}

		// FIELD: Date picker.
		if ( $this->has_field_type( 'date' ) ) {
			$this->load_date_scripts();
		}

		// $this->get_module_design() returns the design
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

	public function load_date_scripts() {
		global $wp_locale;
		// load date picker scripts always
		wp_enqueue_script( 'jquery-ui-datepicker' );

		//localize Datepicker js
		$datepicker_date_format = str_replace(
			array(
				'd', 'j', 'l', 'z', // Day.
				'F', 'M', 'n', 'm', // Month.
				'Y', 'y'            // Year.
			),
			array(
				'dd', 'd', 'DD', 'o',
				'MM', 'M', 'm', 'mm',
				'yy', 'y'
			),
			get_option( 'date_format' )
		);

		$datepicker_data        = array(
			'monthNames'      => array_values( $wp_locale->month ),
			'monthNamesShort' => array_values( $wp_locale->month_abbrev ),
			'dayNames'        => array_values( $wp_locale->weekday ),
			'dayNamesShort'   => array_values( $wp_locale->weekday_abbrev ),
			'dayNamesMin'     => array_values( $wp_locale->weekday_initial ),
			'dateFormat'      => $datepicker_date_format,
			'firstDay'        => absint( get_option( 'start_of_week' ) ),
			'isRTL'           => $wp_locale->is_rtl(),
		);

		wp_localize_script( 'forminator-front-scripts', 'datepickerLang', $datepicker_data );
	}

	/**
	 * Load phone field scripts conditionally
	 *
	 * @since 1.11
	 */
	public function load_phone_scripts() {

		// Load int-tels.
		$style_src     = forminator_plugin_url() . 'assets/css/intlTelInput.min.css';
		$style_version = "4.0.3";

		$script_src     = forminator_plugin_url() . 'assets/js/library/intlTelInput.min.js';
		$script_version = FORMINATOR_VERSION;

		wp_enqueue_style( 'intlTelInput-forminator-css', $style_src, array(), $style_version ); // intlTelInput
		wp_enqueue_script( 'forminator-intlTelInput', $script_src, array( 'jquery' ), $script_version, false ); // intlTelInput
	}
}
