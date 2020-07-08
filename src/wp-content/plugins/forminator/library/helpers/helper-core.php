<?php
/**
 * Return needed cap for admin pages
 *
 * @since 1.0
 * @return string
 */
function forminator_get_admin_cap() {
	$cap = 'manage_options';

	if ( is_multisite() && is_network_admin() ) {
		$cap = 'manage_network';
	}

	return apply_filters( 'forminator_admin_cap', $cap );
}

/**
 * Checks if user is allowed to perform the ajax actions
 *
 * @since 1.0
 * @return bool
 */
function forminator_is_user_allowed() {
	return current_user_can( 'manage_options' );
}

/**
 * Check if array value exists
 *
 * @since 1.0
 *
 * @param array  $array
 * @param string $key - the string key
 *
 * @return bool
 */
function forminator_array_value_exists( $array, $key ) {
	return ( isset( $array[ $key ] ) && ! empty( $array[ $key ] ) );
}

/**
 * Convert object to array
 *
 * @since 1.0
 *
 * @param $object
 *
 * @return array
 */
function forminator_object_to_array( $object ) {
	$array = array();

	if ( empty( $object ) ) {
		return $array;
	}

	foreach ( $object as $key => $value ) {
		$array[ $key ] = $value;
	}

	return $array;
}

/**
 * Return AJAX url
 *
 * @since 1.0
 * @return mixed
 */
function forminator_ajax_url() {
	return admin_url( 'admin-ajax.php', is_ssl() ? 'https' : 'http' );
}

/**
 * Checks if the AJAX call is valid
 *
 * @since 1.0
 *
 * @param $action
 */
function forminator_validate_ajax( $action ) {
	if ( ! forminator_is_user_allowed() || ! check_ajax_referer( $action ) ) {
		wp_send_json_error( __( 'Invalid request, you are not allowed to do that action.', Forminator::DOMAIN ) );
	}
}

/**
 * Enqueue admin fonts
 *
 * @since 1.0
 * @since 1.5.1 implement $version
 *
 * @param $version
 */
function forminator_admin_enqueue_fonts( $version ) {
	wp_enqueue_style(
		'forminator-roboto',
		'https://fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:300,300i,400,400i,500,500i,700,700i',
		array(),
		'1.0'
	); // cache as long as you can
	wp_enqueue_style(
		'forminator-opensans',
		'https://fonts.googleapis.com/css?family=Open+Sans:400,400i,700,700i',
		array(),
		'1.0'
	); // cache as long as you can
	wp_enqueue_style(
		'forminator-source',
		'https://fonts.googleapis.com/css?family=Source+Code+Pro',
		array(),
		'1.0'
	); // cache as long as you can

	// if plugin internal font need to enqueued, please use $version as its subject to cache
}

/**
 * Enqueue admin styles
 *
 * @since 1.0
 * @since 1.1 Remove forminator-admin css after migrate to shared-ui
 *
 * @param $version
 */
function forminator_admin_enqueue_styles( $version ) {
	wp_enqueue_style( 'select2-forminator-css', forminator_plugin_url() . 'assets/css/select2.min.css', array(), '4.0.3', false ); // Select2
	wp_enqueue_style( 'shared-ui', forminator_plugin_url() . 'assets/css/shared-ui.min.css', array(), $version, false );
	wp_enqueue_style( 'forminator-form-styles', forminator_plugin_url() . 'assets/css/front.min.css', array(), $version, false );
}

/**
 * Enqueue jQuery UI scripts on admin
 *
 * @since 1.0
 */
function forminator_admin_jquery_ui() {
	wp_enqueue_script( 'jquery-ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js', array(), '1.12.1', false );
}

/**
 * Load admin scripts
 *
 * @since 1.0
 */
function forminator_admin_jquery_ui_init() {
	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'jquery-ui-widget' );
	wp_enqueue_script( 'jquery-ui-mouse' );
	wp_enqueue_script( 'jquery-ui-tabs' );
	wp_enqueue_script( 'jquery-ui-sortable' );
	wp_enqueue_script( 'jquery-ui-draggable' );
	wp_enqueue_script( 'jquery-ui-droppable' );
	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_script( 'jquery-ui-resize' );
	wp_enqueue_style( 'wp-color-picker' );
}

/**
 * Enqueue SUI scripts on admin
 *
 * @since 1.1
 */
function forminator_sui_scripts() {

	$chartjs_version = '2.7.2';

	$sanitize_version = str_replace( '.', '-', FORMINATOR_SUI_VERSION );
	$sui_body_class   = "sui-$sanitize_version";

	wp_enqueue_script( 'shared-ui', forminator_plugin_url() . 'assets/js/shared-ui.min.js', array( 'jquery' ), $sui_body_class, true );

}

/**
 * Enqueue admin scripts
 *
 * @since 1.0
 *
 * @param       $version
 * @param array $data
 * @param array $l10n
 */
function forminator_admin_enqueue_scripts( $version, $data = array(), $l10n = array() ) {
	$language = get_option( 'forminator_captcha_language', 'en' );

	wp_enqueue_script( 'select2-forminator', forminator_plugin_url() . 'assets/js/library/select2.full.min.js', array( 'jquery' ), $version, false );
	wp_enqueue_script( 'ace-editor', forminator_plugin_url() . 'assets/js/library/ace/ace.js', array( 'jquery' ), $version, false );
	wp_enqueue_script( 'google-charts', 'https://www.gstatic.com/charts/loader.js', array( 'jquery' ), $version, false );

	if ( function_exists( 'wp_enqueue_editor' ) ) {
		wp_enqueue_editor();
	}
	if ( function_exists( 'wp_enqueue_media' ) ) {
		wp_enqueue_media();
	}

	wp_enqueue_script( 'forminator-admin-layout', forminator_plugin_url() . 'build/admin/layout.js', array( 'jquery' ), $version, false );
	wp_register_script(
		'forminator-admin',
		forminator_plugin_url() . 'build/main.js',
		array(
			'backbone',
			'underscore',
			'jquery',
			'wp-color-picker',
		),
		$version,
		true
	);
	wp_localize_script( 'forminator-admin', 'forminatorData', $data );
	wp_localize_script( 'forminator-admin', 'forminatorl10n', $l10n );
	wp_enqueue_script( 'forminator-admin' );
}

/**
 * Enqueue admin scripts
 *
 * @since 1.0
 *
 * @param $version
 */
function forminator_admin_enqueue_scripts_forms( $version, $data = array(), $l10n = array() ) {
	wp_enqueue_script( 'select2-forminator', forminator_plugin_url() . 'assets/js/library/select2.full.min.js', array( 'jquery' ), $version, false );
	wp_enqueue_script( 'ace-editor', forminator_plugin_url() . 'assets/js/library/ace/ace.js', array( 'jquery' ), $version, false );
	wp_enqueue_script( 'google-charts', 'https://www.gstatic.com/charts/loader.js', array( 'jquery' ), $version, false );

	if ( function_exists( 'wp_enqueue_editor' ) ) {
		wp_enqueue_editor();
	}
	if ( function_exists( 'wp_enqueue_media' ) ) {
		wp_enqueue_media();
	}

	wp_enqueue_script( 'forminator-admin-layout', forminator_plugin_url() . 'build/admin/layout.js', array( 'jquery' ), $version, false );
	wp_register_script(
		'forminator-admin',
		forminator_plugin_url() . 'assets/js/form-scripts.js',
		array(
			'jquery',
			'wp-color-picker',
			'react',
			'react-dom',
		),
		$version,
		true
	);
	wp_enqueue_script( 'wp-color-picker-alpha', forminator_plugin_url() . 'assets/js/library/wp-color-picker-alpha.min.js', array( 'wp-color-picker' ), $version, true );
	wp_localize_script( 'forminator-admin', 'forminatorData', $data );
	wp_localize_script( 'forminator-admin', 'forminatorl10n', $l10n );
	wp_enqueue_script( 'forminator-admin' );
}

/**
 * Enqueue admin scripts
 *
 * @since 1.0
 *
 * @param $version
 */
function forminator_admin_enqueue_scripts_polls( $version, $data = array(), $l10n = array() ) {
	wp_enqueue_script( 'select2-forminator', forminator_plugin_url() . 'assets/js/library/select2.full.min.js', array( 'jquery' ), $version, false );
	wp_enqueue_script( 'ace-editor', forminator_plugin_url() . 'assets/js/library/ace/ace.js', array( 'jquery' ), $version, false );
	wp_enqueue_script( 'google-charts', 'https://www.gstatic.com/charts/loader.js', array( 'jquery' ), $version, false );

	if ( function_exists( 'wp_enqueue_editor' ) ) {
		wp_enqueue_editor();
	}
	if ( function_exists( 'wp_enqueue_media' ) ) {
		wp_enqueue_media();
	}

	wp_enqueue_script( 'forminator-admin-layout', forminator_plugin_url() . 'build/admin/layout.js', array( 'jquery' ), $version, false );
	wp_register_script(
		'forminator-admin',
		forminator_plugin_url() . 'assets/js/poll-scripts.js',
		array(
			'jquery',
			'wp-color-picker',
			'react',
			'react-dom',
		),
		$version,
		true
	);
	wp_enqueue_script( 'wp-color-picker-alpha', forminator_plugin_url() . 'assets/js/library/wp-color-picker-alpha.min.js', array( 'wp-color-picker' ), $version, true );
	wp_localize_script( 'forminator-admin', 'forminatorData', $data );
	wp_localize_script( 'forminator-admin', 'forminatorl10n', $l10n );
	wp_enqueue_script( 'forminator-admin' );
}

/**
 * Enqueue admin scripts
 *
 * @since 1.6.2
 *
 * @param $version
 */
function forminator_admin_enqueue_scripts_knowledge( $version, $data = array(), $l10n = array() ) {
	wp_enqueue_script( 'select2-forminator', forminator_plugin_url() . 'assets/js/library/select2.full.min.js', array( 'jquery' ), $version, false );
	wp_enqueue_script( 'ace-editor', forminator_plugin_url() . 'assets/js/library/ace/ace.js', array( 'jquery' ), $version, false );
	wp_enqueue_script( 'google-charts', 'https://www.gstatic.com/charts/loader.js', array( 'jquery' ), $version, false );

	if ( function_exists( 'wp_enqueue_editor' ) ) {
		wp_enqueue_editor();
	}
	if ( function_exists( 'wp_enqueue_media' ) ) {
		wp_enqueue_media();
	}

	wp_enqueue_script( 'forminator-admin-layout', forminator_plugin_url() . 'build/admin/layout.js', array( 'jquery' ), $version, false );
	wp_register_script(
		'forminator-admin',
		forminator_plugin_url() . 'assets/js/knowledge-scripts.js',
		array(
			'jquery',
			'wp-color-picker',
			'react',
			'react-dom',
		),
		$version,
		true
	);
	wp_enqueue_script( 'wp-color-picker-alpha', forminator_plugin_url() . 'assets/js/library/wp-color-picker-alpha.min.js', array( 'wp-color-picker' ), $version, true );
	wp_localize_script( 'forminator-admin', 'forminatorData', $data );
	wp_localize_script( 'forminator-admin', 'forminatorl10n', $l10n );
	wp_enqueue_script( 'forminator-admin' );
}


/**
 * Enqueue admin scripts
 *
 * @since 1.6.2
 *
 * @param $version
 */
function forminator_admin_enqueue_scripts_personality( $version, $data = array(), $l10n = array() ) {
	wp_enqueue_script( 'select2-forminator', forminator_plugin_url() . 'assets/js/library/select2.full.min.js', array( 'jquery' ), $version, false );
	wp_enqueue_script( 'ace-editor', forminator_plugin_url() . 'assets/js/library/ace/ace.js', array( 'jquery' ), $version, false );
	wp_enqueue_script( 'google-charts', 'https://www.gstatic.com/charts/loader.js', array( 'jquery' ), $version, false );

	if ( function_exists( 'wp_enqueue_editor' ) ) {
		wp_enqueue_editor();
	}
	if ( function_exists( 'wp_enqueue_media' ) ) {
		wp_enqueue_media();
	}

	wp_enqueue_script( 'forminator-admin-layout', forminator_plugin_url() . 'build/admin/layout.js', array( 'jquery' ), $version, false );
	wp_register_script(
		'forminator-admin',
		forminator_plugin_url() . 'assets/js/personality-scripts.js',
		array(
			'jquery',
			'wp-color-picker',
			'react',
			'react-dom',
		),
		$version,
		true
	);
	wp_enqueue_script( 'wp-color-picker-alpha', forminator_plugin_url() . 'assets/js/library/wp-color-picker-alpha.min.js', array( 'wp-color-picker' ), $version, true );
	wp_localize_script( 'forminator-admin', 'forminatorData', $data );
	wp_localize_script( 'forminator-admin', 'forminatorl10n', $l10n );
	wp_enqueue_script( 'forminator-admin' );
}

/**
 * Enqueue custom form admin styles
 *
 * @since 1.11
 *
 * @param $version
 * @param $grid
 * @param $module_type
 * @param $module_design
 */
function forminator_print_forms_admin_styles( $version = '1.0' ) {
	wp_enqueue_style( 'forminator-ui-icons', forminator_plugin_url() . 'assets/forminator-ui/css/forminator-icons.min.css', array(), $version );
	wp_enqueue_style( 'forminator-ui', forminator_plugin_url() . 'assets/forminator-ui/css/src/forminator-ui.min.css', array(), $version );
}

/**
 * Enqueue poll admin styles
 *
 * @since 1.11
 *
 * @param $version
 * @param $grid
 * @param $module_type
 * @param $module_design
 */
function forminator_print_polls_admin_styles( $version = '1.0' ) {
	wp_enqueue_style( 'forminator-ui-icons', forminator_plugin_url() . 'assets/forminator-ui/css/forminator-icons.min.css', array(), $version );
	wp_enqueue_style( 'forminator-ui', forminator_plugin_url() . 'assets/forminator-ui/css/src/forminator-ui.min.css', array(), $version );
}

/**
 * Enqueue front-end styles
 *
 * only use core here, if the style dynamically loaded, then load on model
 *
 * @since 1.0
 *
 * @param $version
 * @param $grid
 * @param $module_type
 * @param $module_design
 */
function forminator_print_front_styles( $version = '1.0' ) {
	// Load old styles.
	// Remove on v1.12.0 quizzes migrate to Forminator UI.
	wp_enqueue_style( 'forminator-ui', forminator_plugin_url() . 'assets/forminator-ui/css/src/forminator-ui.min.css', array(), $version );

}

/**
 * Enqueue front-end script
 *
 * only use core here, if the style dynamically loaded, then load on model
 *
 * @since 1.0
 *
 * @param $version
 */
function forminator_print_front_scripts( $version = '1.0' ) {

	global $wp_locale;

	// LOAD: ChartJS
	wp_enqueue_script(
		'chartjs',
		forminator_plugin_url() . 'assets/js/front/Chart.min.js',
		array( 'jquery' ),
		'2.8.0',
		false
	);
	$save_global_color = "if (typeof window !== 'undefined' && typeof window.Color !== 'undefined') {window.notChartColor = window.Color;}";
	$restore_global_color = "if (typeof window !== 'undefined' && typeof window.notChartColor !== 'undefined') {window.Color = window.notChartColor;}";
	wp_add_inline_script( 'chartjs', $save_global_color, 'before' );
	wp_add_inline_script( 'forminator-chart', $save_global_color, 'before' );
	wp_add_inline_script( 'chartjs', $restore_global_color );
	wp_add_inline_script( 'forminator-chart', $restore_global_color );

	// LOAD: Datalabels plugin for ChartJS
	wp_enqueue_script(
		'chartjs-plugin-datalabels',
		forminator_plugin_url() . 'assets/js/front/chartjs-plugin-datalabels.min.js',
		array( 'jquery' ),
		'0.6.0',
		false
	);

	// LOAD: Forminator UI JS
	wp_enqueue_script(
		'forminator-ui',
		forminator_plugin_url() . 'assets/forminator-ui/js/forminator-ui.min.js',
		array( 'jquery' ),
		'1.7.1',
		false
	);

	// TODO : check if its always needed
	// wp_enqueue_script( 'select2-forminator', forminator_plugin_url() . 'assets/js/library/select2.full.min.js', array( 'jquery' ), $version, false );

	// TODO : check if its always needed
	wp_enqueue_script( 'forminator-jquery-validate', forminator_plugin_url() . 'assets/js/library/jquery.validate.min.js', array( 'jquery' ), FORMINATOR_VERSION, false );


	wp_enqueue_script(
		'forminator-front-scripts',
		forminator_plugin_url() . 'build/front/front.multi.min.js',
		array( 'jquery', 'forminator-ui', 'forminator-jquery-validate' ),
		$version,
		false
	);

	wp_localize_script( 'forminator-front-scripts', 'ForminatorFront', forminator_localize_data() );

	//localize Datepicker js
	$datepicker_date_format = str_replace(
		array(
			'd',
			'j',
			'l',
			'z', // Day.
			'F',
			'M',
			'n',
			'm', // Month.
			'Y',
			'y', // Year.
		),
		array(
			'dd',
			'd',
			'DD',
			'o',
			'MM',
			'M',
			'm',
			'mm',
			'yy',
			'y',
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
 * Return front-end localization data
 *
 * @since 1.0
 */
function forminator_localize_data() {
	return array(
		'ajaxUrl' => forminator_ajax_url(),
		'cform'   => array(
			'processing'                => __( 'Submitting form, please wait', Forminator::DOMAIN ),
			'error'                     => __( 'An error occurred processing the form. Please try again', Forminator::DOMAIN ),
			'upload_error'              => __( 'An upload error occurred processing the form. Please try again', Forminator::DOMAIN ),
			'pagination_prev'           => __( 'Previous', Forminator::DOMAIN ),
			'pagination_next'           => __( 'Next', Forminator::DOMAIN ),
			'pagination_go'             => __( 'Submit', Forminator::DOMAIN ),
			'gateway'                   => array(
				'processing' => __( 'Processing payment, please wait', Forminator::DOMAIN ),
				'paid'       => __( 'Success! Payment confirmed. Submitting form, please wait', Forminator::DOMAIN ),
				'error'      => __( 'Error! Something went wrong when verifying the payment', Forminator::DOMAIN ),
			),
			'captcha_error'             => __( 'Invalid CAPTCHA', Forminator::DOMAIN ),
			'no_file_chosen'            => __( 'No file chosen', Forminator::DOMAIN ),
			// This is the file "/build/js/utils.js" found into intlTelInput plugin. Renamed so it makes sense within the "js/library" directory context.
			'intlTelInput_utils_script' => forminator_plugin_url() . 'assets/js/library/intlTelInputUtils.js',
		),
		'poll'    => array(
			'processing' => __( 'Submitting vote, please wait', Forminator::DOMAIN ),
			'error'      => __( 'An error occurred saving the vote. Please try again', Forminator::DOMAIN ),
		),
		'select2' => array(
			'load_more'       => __( 'Loading more results…', Forminator::DOMAIN ),
			'no_result_found' => __( 'No results found', Forminator::DOMAIN ),
			'searching'       => __( 'Searching…', Forminator::DOMAIN ),
			'loaded_error'    => __( 'The results could not be loaded.', Forminator::DOMAIN ),
		),
	);
}

/**
 * Return existing templates
 *
 * @since 1.0
 *
 * @param $path
 * @param $args
 *
 * @return mixed
 */

function forminator_template( $path, $args = array() ) {
	$file    = forminator_plugin_dir() . "admin/views/$path.php";
	$content = '';

	if ( is_file( $file ) ) {
		ob_start();

		if ( isset( $args['id'] ) ) {
			$args['template_class'] = $args['class'];
			$args['template_id']    = $args['id'];
			$title                  = $args['title'];
			$header_callback        = $args['header_callback'];
			$main_callback          = $args['main_callback'];
			$footer_callback        = $args['footer_callback'];
		}

		include $file;

		$content = ob_get_clean();
	}

	return $content;
}

/**
 * Return if template exist
 *
 * @since 1.0
 *
 * @param $path
 *
 * @return bool
 */
function forminator_template_exist( $path ) {
	$file = forminator_plugin_dir() . "admin/views/$path.php";

	return is_file( $file );
}

/**
 * Return if paypal settings are filled
 *
 * @since 1.0
 * @return bool
 */
function forminator_has_paypal_settings() {
	$config = get_option( 'forminator_paypal_configuration', array() );

	if ( empty( $config ) ) {
		return false;
	}

	return true;
}

/**
 * Return if captcha settings are filled
 *
 * @since 1.0
 * @return bool
 */
function forminator_has_captcha_settings() {
	$key    = get_option( 'forminator_captcha_key', false );
	$secret = get_option( 'forminator_captcha_secret', false );

	if ( empty( $key ) || empty( $secret ) ) {
		return false;
	}

	return true;
}

/**
 * Return if captcha v2 settings are filled
 *
 * @since 1.0
 * @return bool
 */
function forminator_has_v2_captcha_settings() {
	$key    = get_option( 'forminator_captcha_key', false );
	$secret = get_option( 'forminator_captcha_secret', false );

	if ( empty( $key ) || empty( $secret ) ) {
		return false;
	}

	return true;
}

/**
 * Return if captcha v2 invisible settings are filled
 *
 * @since 1.0
 * @return bool
 */
function forminator_has_v2_invisible_captcha_settings() {
	$key    = get_option( 'forminator_v2_invisible_captcha_key', false );
	$secret = get_option( 'forminator_v2_invisible_captcha_secret', false );

	if ( empty( $key ) || empty( $secret ) ) {
		return false;
	}

	return true;
}

/**
 * Return if captcha v3 settings are filled
 *
 * @since 1.0
 * @return bool
 */
function forminator_has_v3_captcha_settings() {
	$key    = get_option( 'forminator_v3_captcha_key', false );
	$secret = get_option( 'forminator_v3_captcha_secret', false );

	if ( empty( $key ) || empty( $secret ) ) {
		return false;
	}

	return true;
}

/**
 * Return if Stripe is is_connected
 *
 * @since 1.7
 * @return bool
 */
function forminator_has_stripe_connected() {
	if ( class_exists( 'Forminator_Gateway_Stripe' ) ) {
		try {
			$stripe = new Forminator_Gateway_Stripe();
			if ( $stripe->is_test_ready() && $stripe->is_live_ready() ) {
				return true;
			}
		} catch ( Forminator_Gateway_Exception $e ) {
			return false;
		}
	}

	return false;
}
/**
 * Return form ID
 *
 * @since 1.0
 * @return int
 */
function forminator_get_form_id_helper() {
	$screen = get_current_screen();
	$ids    = forminator_get_page_ids_helper();

	if ( ! in_array( $screen->id, $ids, true ) ) {
		return 0;
	}

	return isset( $_GET['form_id'] ) ? intval( $_GET['form_id'] ) : 0;
}

/**
 * Get Page IDs
 *
 * @since 1.2
 * @return array
 */
function forminator_get_page_ids_helper() {
	if ( FORMINATOR_PRO ) {
		return array(
			'forminator-pro_page_forminator-quiz-view',
			'forminator-pro_page_forminator-cform-view',
			'forminator-pro_page_forminator-poll-view',
			'forminator-pro_page_forminator-entries',
		);
	} else {
		// Free version
		return array(
			'forminator_page_forminator-quiz-view',
			'forminator_page_forminator-cform-view',
			'forminator_page_forminator-poll-view',
			'forminator_page_forminator-entries',
		);
	}
}

/**
 * Return form type
 *
 * @since 1.0
 * @return int|null|string
 */
function forminator_get_form_type_helper() {
	$screen = get_current_screen();
	$ids    = forminator_get_page_ids_helper();
	if ( ! in_array( $screen->id, $ids, true ) ) {
		return 0;
	}

	$form_type = "";
	$page      = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : null;

	if ( is_null( $page ) ) {
		return null;
	}

	switch ( $page ) {
		case 'forminator-quiz-view':
			$form_type = 'quiz';
			break;
		case 'forminator-poll-view':
			$form_type = 'poll';
			break;
		case 'forminator-cform-view':
			$form_type = 'cform';
			break;
		case 'forminator-entries':
			if ( isset( $_GET['form_type'] ) && $_GET['form_type'] ) { // phpcs:ignore
				switch ( $_GET['form_type'] ) { // phpcs:ignore
					case 'forminator_forms':
						$form_type = 'cform';
						break;
					case 'forminator_polls':
						$form_type = 'poll';
						break;
					case 'forminator_quizzes':
						$form_type = 'quiz';
						break;
					default:
						break;
				}
			}
			break;
		default:
			break;
	}

	return $form_type;
}

/**
 * @since 1.0
 *
 * @param $info
 * @param $key
 *
 * @return mixed
 */
function forminator_get_exporter_info( $info, $key ) {
	$data = get_option( 'forminator_entries_export_schedule', array() );
	if ( 'email' === $info && ! empty( $data[ $key ][ $info ] ) && ! is_array( $data[ $key ][ $info ] ) ) {
		return array( $data[ $key ][ $info ] );
	}

	return isset( $data[ $key ][ $info ] ) ? $data[ $key ][ $info ] : null;
}

/**
 * Return current logged in username
 *
 * @since 1.0
 * @return string
 */
function forminator_get_current_username() {
	$current_user = wp_get_current_user();
	if ( ! ( $current_user instanceof WP_User ) || empty( $current_user->user_login ) ) {
		return '';
	}

	return $current_user->user_login;
}

/**
 * @since 1.0
 *
 * @param $form_id
 *
 * @return bool
 */
function delete_export_logs( $form_id ) {
	if ( ! $form_id ) {
		return false;
	}

	$data   = get_option( 'forminator_exporter_log', array() );
	$delete = false;

	if ( isset( $data[ $form_id ] ) ) {
		unset( $data[ $form_id ] );
		$delete = update_option( 'forminator_exporter_log', $data );
	}

	return $delete;
}

/**
 * @since 1.0
 *
 * @param $form_id
 *
 * @return array
 */
function forminator_get_export_logs( $form_id ) {
	if ( ! $form_id ) {
		return array();
	}

	$data = get_option( 'forminator_exporter_log', array() );
	$row  = isset( $data[ $form_id ] ) ? $data[ $form_id ] : array();

	foreach ( $row as &$item ) {
		$item['time'] = date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $item['time'] );// phpcs:ignore
	}

	return $row;
}

/**
 * Return current page url
 *
 * @since 1.0.3
 *
 * @return mixed
 */
function forminator_get_current_url() {
	global $wp;

	return add_query_arg( esc_attr( $_SERVER['QUERY_STRING'] ), '', trailingslashit( home_url( $wp->request ) ) );
}

/**
 * Return week day from number
 *
 * @since 1.0
 *
 * @param $day
 *
 * @return string
 */
function forminator_get_day_translated( $day ) {
	$days = array(
		'mon' => __( 'Monday', Forminator::DOMAIN ),
		'tue' => __( 'Tuesday', Forminator::DOMAIN ),
		'wed' => __( 'Wednesday', Forminator::DOMAIN ),
		'thu' => __( 'Thursday', Forminator::DOMAIN ),
		'fri' => __( 'Friday', Forminator::DOMAIN ),
		'sat' => __( 'Saturday', Forminator::DOMAIN ),
		'sun' => __( 'Sunday', Forminator::DOMAIN ),
	);

	return isset( $days[ $day ] ) ? $days[ $day ] : $day;
}

/**
 * Add log of forminator
 *
 * By default it will check `WP_DEBUG` and `FORMINATOR_DEBUG`
 * then will check `filters`
 *
 * @since 1.1
 * @since 1.3 add FORMINATOR_DEBUG as enabled flag
 */
function forminator_maybe_log() {
	$wp_debug_enabled = ( defined( 'WP_DEBUG' ) && WP_DEBUG );

	$enabled = ( defined( 'FORMINATOR_DEBUG' ) && FORMINATOR_DEBUG );

	$enabled = ( $wp_debug_enabled && $enabled );

	/**
	 * Filter log enable for forminator
	 *
	 * y default it will check `WP_DEBUG`, `FORMINATOR_DEBUG` must be true
	 *
	 * @since 1.1
	 *
	 * @param bool $enabled current enable status
	 */
	$enabled = apply_filters( 'forminator_enable_log', $enabled );

	if ( $enabled ) {
		$args    = func_get_args();
		$message = wp_json_encode( $args );
		if ( false !== $message ) {
			error_log( '[Forminator] ' . $message );// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
	}
}

/**
 * Helper to cast variable to target type
 *
 * @since 1.6
 *
 * @param $var
 * @param $type
 *
 * @return mixed
 */
function forminator_var_type_cast( $var, $type ) {
	switch ( $type ) {
		case 'bool':
			if ( ! is_bool( $var ) ) {
				$var = filter_var( $var, FILTER_VALIDATE_BOOLEAN );
			}
			break;
		case 'str':
			if ( ! is_string( $var ) ) {
				if ( is_array( $var ) ) {
					$var = implode( ', ', $var );
				} else {
					// juggling
					$var = (string) $var;
				}
			}
			break;
		case 'num':
			if ( ! is_numeric( $var ) ) {
				// juggling
				$var = (int) $var;
			}
			$var = $var + 0;
			break;
		case 'array':
			if ( ! is_array( $var ) ) {
				// juggling
				$var = (array) $var;
			}
			break;
		default:
			break;
	}

	return $var;
}

/**
 * Get chart colors combination for Polls
 *
 * @since 1.5.3
 *
 * @param int $poll_id
 *
 * @return array
 */
function forminator_get_poll_chart_colors( $poll_id = null ) {

	$chart_colors = array(
		'rgba(54, 162, 235, 0.2)', // Blue
		'rgba(255, 99, 132, 0.2)', // Red
		'rgba(255, 206, 86, 0.2)', // Yellow
		'rgba(75, 192, 192, 0.2)', // Green
		'rgba(255, 159, 64, 0.2)', // Orange
		'rgba(153, 102, 255, 0.2)', // Purple
		'rgba(102, 137, 161, 0.2)', // Blue Alt
		'rgba(234, 86, 118, 0.2)', // Red Alt
		'rgba(216, 220, 106, 0.2)', // Yellow Alt
		'rgba(107, 193, 146, 0.2)', // Green Alt
		'rgba(235, 130, 88, 0.2)', // Orange Alt
		'rgba(153, 93, 129, 0.2)', // Purple Alt
		'rgba(0, 0, 0, 0.2)', // Black
		'rgba(136, 136, 136, 0.2)', // Black Alt
	);

	$chart_colors = apply_filters_deprecated( 'forminator_poll_chart_color', array( $chart_colors ), '1.5.3', 'forminator_poll_chart_colors' );

	/**
	 * Filter chart colors to be used for polls
	 *
	 * @since 1.5.3
	 *
	 * @param array $chart_colors
	 * @param int   $poll_id
	 */
	$chart_colors = apply_filters( 'forminator_poll_chart_colors', $chart_colors, $poll_id );

	return $chart_colors;
}

/**
 * Return reCAPTCHA languages
 *
 * @since 1.5.4
 * @return array
 */
function forminator_get_captcha_languages() {
	return apply_filters(
		'forminator_captcha_languages',
		array(
			'ar'     => esc_html__( 'Arabic', Forminator::DOMAIN ),
			'af'     => esc_html__( 'Afrikaans', Forminator::DOMAIN ),
			'am'     => esc_html__( 'Amharic', Forminator::DOMAIN ),
			'hy'     => esc_html__( 'Armenian', Forminator::DOMAIN ),
			'az'     => esc_html__( 'Azerbaijani', Forminator::DOMAIN ),
			'eu'     => esc_html__( 'Basque', Forminator::DOMAIN ),
			'bn'     => esc_html__( 'Bengali', Forminator::DOMAIN ),
			'bg'     => esc_html__( 'Bulgarian', Forminator::DOMAIN ),
			'ca'     => esc_html__( 'Catalan', Forminator::DOMAIN ),
			'zh-HK'  => esc_html__( 'Chinese (Hong Kong)', Forminator::DOMAIN ),
			'zh-CN'  => esc_html__( 'Chinese (Simplified)', Forminator::DOMAIN ),
			'zh-TW'  => esc_html__( 'Chinese (Traditional)', Forminator::DOMAIN ),
			'hr'     => esc_html__( 'Croatian', Forminator::DOMAIN ),
			'cs'     => esc_html__( 'Czech', Forminator::DOMAIN ),
			'da'     => esc_html__( 'Danish', Forminator::DOMAIN ),
			'nl'     => esc_html__( 'Dutch', Forminator::DOMAIN ),
			'en-GB'  => esc_html__( 'English (UK)', Forminator::DOMAIN ),
			'en'     => esc_html__( 'English (US)', Forminator::DOMAIN ),
			'et'     => esc_html__( 'Estonian', Forminator::DOMAIN ),
			'fil'    => esc_html__( 'Filipino', Forminator::DOMAIN ),
			'fi'     => esc_html__( 'Finnish', Forminator::DOMAIN ),
			'fr'     => esc_html__( 'French', Forminator::DOMAIN ),
			'fr-CA'  => esc_html__( 'French (Canadian)', Forminator::DOMAIN ),
			'gl'     => esc_html__( 'Galician', Forminator::DOMAIN ),
			'ka'     => esc_html__( 'Georgian', Forminator::DOMAIN ),
			'de'     => esc_html__( 'German', Forminator::DOMAIN ),
			'de-AT'  => esc_html__( 'German (Austria)', Forminator::DOMAIN ),
			'de-CH'  => esc_html__( 'German (Switzerland)', Forminator::DOMAIN ),
			'el'     => esc_html__( 'Greek', Forminator::DOMAIN ),
			'gu'     => esc_html__( 'Gujarati', Forminator::DOMAIN ),
			'iw'     => esc_html__( 'Hebrew', Forminator::DOMAIN ),
			'hi'     => esc_html__( 'Hindi', Forminator::DOMAIN ),
			'hu'     => esc_html__( 'Hungarain', Forminator::DOMAIN ),
			'is'     => esc_html__( 'Icelandic', Forminator::DOMAIN ),
			'id'     => esc_html__( 'Indonesian', Forminator::DOMAIN ),
			'it'     => esc_html__( 'Italian', Forminator::DOMAIN ),
			'ja'     => esc_html__( 'Japanese', Forminator::DOMAIN ),
			'kn'     => esc_html__( 'Kannada', Forminator::DOMAIN ),
			'ko'     => esc_html__( 'Korean', Forminator::DOMAIN ),
			'lo'     => esc_html__( 'Laothian', Forminator::DOMAIN ),
			'lv'     => esc_html__( 'Latvian', Forminator::DOMAIN ),
			'lt'     => esc_html__( 'Lithuanian', Forminator::DOMAIN ),
			'ms'     => esc_html__( 'Malay', Forminator::DOMAIN ),
			'ml'     => esc_html__( 'Malayalam', Forminator::DOMAIN ),
			'mr'     => esc_html__( 'Marathi', Forminator::DOMAIN ),
			'mn'     => esc_html__( 'Mongolian', Forminator::DOMAIN ),
			'no'     => esc_html__( 'Norwegian', Forminator::DOMAIN ),
			'fa'     => esc_html__( 'Persian', Forminator::DOMAIN ),
			'pl'     => esc_html__( 'Polish', Forminator::DOMAIN ),
			'pt'     => esc_html__( 'Portuguese', Forminator::DOMAIN ),
			'pt-BR'  => esc_html__( 'Portuguese (Brazil)', Forminator::DOMAIN ),
			'pt-PT'  => esc_html__( 'Portuguese (Portugal)', Forminator::DOMAIN ),
			'ro'     => esc_html__( 'Romanian', Forminator::DOMAIN ),
			'ru'     => esc_html__( 'Russian', Forminator::DOMAIN ),
			'rs'     => esc_html__( 'Serbian', Forminator::DOMAIN ),
			'si'     => esc_html__( 'Sinhalese', Forminator::DOMAIN ),
			'sk'     => esc_html__( 'Slovak', Forminator::DOMAIN ),
			'sl'     => esc_html__( 'Slovenian', Forminator::DOMAIN ),
			'es'     => esc_html__( 'Spanish', Forminator::DOMAIN ),
			'es-419' => esc_html__( 'Spanish (Latin America)', Forminator::DOMAIN ),
			'sw'     => esc_html__( 'Swahili', Forminator::DOMAIN ),
			'sv'     => esc_html__( 'Swedish', Forminator::DOMAIN ),
			'ta'     => esc_html__( 'Tamil', Forminator::DOMAIN ),
			'te'     => esc_html__( 'Telugu', Forminator::DOMAIN ),
			'th'     => esc_html__( 'Thai', Forminator::DOMAIN ),
			'tr'     => esc_html__( 'Turkish', Forminator::DOMAIN ),
			'uk'     => esc_html__( 'Ukrainian', Forminator::DOMAIN ),
			'ur'     => esc_html__( 'Urdu', Forminator::DOMAIN ),
			'vi'     => esc_html__( 'Vietnamese', Forminator::DOMAIN ),
			'zu'     => esc_html__( 'Zulu', Forminator::DOMAIN ),
		)
	);
}

/**
 * Flag whether doc link should shown or not
 *
 * @since 1.6
 * @return bool
 */
function forminator_is_show_documentation_link() {
	if ( Forminator::is_wpmudev_member() ) {
		return ! apply_filters( 'wpmudev_branding_hide_doc_link', false );
	}

	return true;
}

/**
 * Flag whether branding should shown or not
 *
 * @since 1.6
 * @return bool
 */
function forminator_is_show_branding() {
	if ( Forminator::is_wpmudev_member() ) {
		return ! apply_filters( 'wpmudev_branding_hide_branding', false );
	}

	return true;
}

/**
 * Get Dashboard settings
 *
 * @since 1.6.3
 *
 * @param string|null $widget
 * @param mixed       $default
 *
 * @return array|mixed
 */
function forminator_get_dashboard_settings( $widget = null, $default = array() ) {
	$settings           = array();
	$dashboard_settings = get_option( 'forminator_dashboard_settings', $default );

	if ( ! is_null( $widget ) ) {
		if ( isset( $dashboard_settings[ $widget ] ) ) {
			$settings = $dashboard_settings[ $widget ];
		} else {
			$settings = $default;
		}
	}

	/**
	 * Filter Dashboard settings
	 *
	 * @since 1.6.3
	 *
	 * @param mixed $settings
	 * @param string widget
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	$settings = apply_filters( 'forminator_dashboard_settings', $settings, $widget, $default );

	return $settings;

}

/**
 * Reset Forminator Settings
 *
 * @see   forminator_delete_custom_options()
 * @see   forminator_delete_addon_options()
 * @see   forminator_delete_custom_posts()
 * @since 1.6.3
 */
function forminator_reset_settings() {
	global $wpdb;

	/**
	 * Fires before Settings reset
	 *
	 * @since 1.6.3
	 */
	do_action( 'forminator_before_reset_settings' );

	/**
	 * @see forminator_delete_custom_options()
	 */

	delete_option( "forminator_pagination_listings" );
	delete_option( "forminator_pagination_entries" );
	delete_option( "forminator_captcha_key" );
	delete_option( "forminator_captcha_secret" );
	delete_option( "forminator_v2_invisible_captcha_key" );
	delete_option( "forminator_v2_invisible_captcha_secret" );
	delete_option( "forminator_v3_captcha_key" );
	delete_option( "forminator_v3_captcha_secret" );
	delete_option( "forminator_captcha_language" );
	delete_option( "forminator_captcha_theme" );
	delete_option( "forminator_welcome_dismissed" );
	delete_option( "forminator_version" );
	delete_option( "forminator_retain_votes_interval_number" );
	delete_option( "forminator_retain_votes_interval_unit" );
	delete_option( "forminator_retain_submissions_interval_number" );
	delete_option( "forminator_retain_submissions_interval_unit" );
	delete_option( "forminator_enable_erasure_request_erase_form_submissions" );
	delete_option( "forminator_form_privacy_settings" );
	delete_option( "forminator_poll_privacy_settings" );
	delete_option( "forminator_retain_ip_interval_number" );
	delete_option( "forminator_retain_ip_interval_unit" );
	delete_option( "forminator_retain_poll_submissions_interval_number" );
	delete_option( "forminator_retain_poll_submissions_interval_unit" );
	delete_option( "forminator_posts_map" );
	delete_option( "forminator_module_enable_load_ajax" );
	delete_option( "forminator_module_use_donotcachepage" );
	delete_option( "forminator_retain_quiz_submissions_interval_number" );
	delete_option( "forminator_retain_quiz_submissions_interval_unit" );
	delete_option( "forminator_dashboard_settings" );
	delete_option( "forminator_sender_email_address" );
	delete_option( "forminator_sender_name" );
	delete_option( "forminator_enable_accessibility" );
	delete_option( "forminator_entries_export_schedule" );
	delete_option( "forminator_paypal_api_mode" );
	delete_option( "forminator_paypal_secret" );
	delete_option( "forminator_currency" );
	delete_option( "forminator_exporter_log" );
	delete_option( "forminator_uninstall_clear_data" );
	delete_option( "forminator_stripe_configuration" );
	delete_option( "forminator_paypal_configuration" );

	/**
	 * @see forminator_delete_addon_options()
	 */
	delete_option( 'forminator_activated_addons' );
	$registered_addons = forminator_get_registered_addons();
	foreach ( $registered_addons as $addon_slug => $registered_addon ) {
		delete_option( "forminator_addon_{$addon_slug}_version" );
		delete_option( "forminator_addon_{$addon_slug}_settings" );
	}

	/**
	 * @see forminator_delete_custom_posts()
	 */
	//Now we delete the custom posts
	$entry_table      = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY );
	$entry_meta_table = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY_META );
	$forms_sql        = "SELECT GROUP_CONCAT(`ID`) FROM {$wpdb->posts} WHERE `post_type` = %s";
	$delete_forms_sql = "DELETE FROM {$wpdb->posts} WHERE `post_type` = %s";
	$form_types       = array(
		'forminator_forms',
		'forminator_polls',
		'forminator_quizzes',
	);
	foreach ( $form_types as $type ) {
		$ids = $wpdb->get_var( $wpdb->prepare( $forms_sql, $type ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		if ( $ids ) {
			$array_ids = explode( ',', $ids );
			foreach ( $array_ids as $array_id ) {
				wp_cache_delete( $array_id, 'forminator_total_entries' );
			}

			$delete_form_meta_sql = "DELETE FROM {$wpdb->postmeta} WHERE `post_id` in($ids)";
			$wpdb->query( $delete_form_meta_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

			$entry_sql = "SELECT GROUP_CONCAT(`entry_id`) FROM {$entry_table} WHERE `form_id` IN ($ids)";
			$entries   = $wpdb->get_var( $entry_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

			$delete_entry_meta_sql = "DELETE FROM {$entry_meta_table} WHERE `entry_id` in($entries)";
			$wpdb->query( $delete_entry_meta_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

			$delete_entry_sql = "DELETE FROM {$entry_table} WHERE `form_id` in($ids)";
			$wpdb->query( $delete_entry_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
		$wpdb->query( $wpdb->prepare( $delete_forms_sql, $type ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Fires after Settings reset
	 *
	 * @since 1.6.3
	 */
	do_action( 'forminator_after_reset_settings' );
}

/**
 * Reset plugin to fresh install
 *
 * @since 1.6.3
 */
function forminator_reset_plugin() {
	global $wpdb;

	/**
	 * Fires before Plugin reset
	 *
	 * @since 1.6.3
	 */
	do_action( 'forminator_before_reset_plugin' );

	forminator_reset_settings();

	/**
	 * @see forminator_clear_module_views()
	 */
	$wpdb->query( "TRUNCATE {$wpdb->prefix}frmt_form_views" );

	/**
	 * @see forminator_clear_module_submissions()
	 */
	$max_entry_id_query = "SELECT MAX(`entry_id`) FROM {$wpdb->prefix}frmt_form_entry";
	$max_entry_id       = $wpdb->get_var( $max_entry_id_query ); // phpcs:ignore

	if ( $max_entry_id && is_numeric( $max_entry_id ) && $max_entry_id > 0 ) {
		for ( $i = 1; $i <= $max_entry_id; $i ++ ) {
			wp_cache_delete( $i, 'Forminator_Form_Entry_Model' );
		}
	}

	$wpdb->query( "TRUNCATE {$wpdb->prefix}frmt_form_entry" );
	$wpdb->query( "TRUNCATE {$wpdb->prefix}frmt_form_entry_meta" );

	wp_cache_delete( 'all_form_types', 'forminator_total_entries' );
	wp_cache_delete( 'custom-forms_form_type', 'forminator_total_entries' );
	wp_cache_delete( 'poll_form_type', 'forminator_total_entries' );
	wp_cache_delete( 'quizzes_form_type', 'forminator_total_entries' );

	/**
	 * Fires after Plugin reset
	 *
	 * @since 1.6.3
	 */
	do_action( 'forminator_after_reset_plugin' );
}

/**
 * Add Slash in string
 *
 * @since 1.8
 *
 * @param $value
 * @param string $char
 *
 * @return string
 */

function forminator_addcslashes( $value, $char = '"\\/' ) {

	return addcslashes( $value, $char );
}
