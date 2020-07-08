<?php

/**
 * Class Forminator_Admin_Data
 *
 * @since 1.0
 */
class Forminator_Admin_Data {

	public $core = null;

	/**
	 * Current Nonce
	 *
	 * @since 1.2
	 * @var string
	 */
	private $_nonce = '';

	/**
	 * Forminator_Admin_Data constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		$this->core = Forminator::get_instance();
	}

	/**
	 * Combine Data and pass to JS
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_options_data() {
		$data           = $this->admin_js_defaults();
		$data           = apply_filters( 'forminator_data', $data );
		$data['fields'] = forminator_get_fields_sorted( 'position', SORT_ASC );

		return $data;
	}

	/**
	 * Generate nonce
	 *
	 * @since 1.2
	 */
	public function generate_nonce() {
		$this->_nonce = wp_create_nonce( 'forminator_load_google_fonts' );
	}

	/**
	 * Get current generated nonce
	 *
	 * @since 1.2
	 * @return string
	 */
	public function get_nonce() {
		return $this->_nonce;
	}

	/**
	 * Return published pages
	 *
	 * @since 1.8
	 *
	 * @return mixed
	 */
	public function get_pages() {
		$args = array(
			'sort_order' => 'DESC',
			'sort_column' => 'ID',
			'hierarchical' => 1,
			'exclude' => '',
			'include' => '',
			'meta_key' => '',
			'meta_value' => '',
			'authors' => '',
			'child_of' => 0,
			'parent' => -1,
			'exclude_tree' => '',
			'number' => '',
			'offset' => 0,
			'post_type' => 'page',
			'post_status' => 'publish'
		);

		$pages = get_pages($args);

		return $pages;
	}

	/**
	 * Default Admin properties
	 *
	 * @since 1.0
	 * @return array
	 */
	public function admin_js_defaults() {
		// Generate addon nonce
		Forminator_Addon_Admin_Ajax::get_instance()->generate_nonce();

		return array(
			'ajaxUrl'                        => forminator_ajax_url(),
			'application'                    => '',
			'is_touch'                       => wp_is_mobile(),
			'dashboardUrl'                   => menu_page_url( 'forminator', false ),
			'formEditUrl'                    => menu_page_url( 'forminator-cform-wizard', false ),
			'noWrongEditUrl'                 => menu_page_url( 'forminator-nowrong-wizard', false ),
			'knowledgeEditUrl'               => menu_page_url( 'forminator-knowledge-wizard', false ),
			'pollEditUrl'                    => menu_page_url( 'forminator-poll-wizard', false ),
			'settingsUrl'                    => menu_page_url( 'forminator-settings', false ),
			'integrationsUrl'                => menu_page_url( 'forminator-integrations', false ),
			'hasCaptcha'                     => forminator_has_captcha_settings(),
			'hasV2Captcha'                   => forminator_has_v2_captcha_settings(),
			'hasV2InvisibleCaptcha'          => forminator_has_v2_invisible_captcha_settings(),
			'hasV3Captcha'                   => forminator_has_v3_captcha_settings(),
			'hasStripe'                      => forminator_has_stripe_connected(),
			'formNonce'                      => $this->get_nonce(),
			'searchNonce'                    => wp_create_nonce( 'forminator_search_emails' ),
			'gFontNonce'                     => wp_create_nonce( 'forminator_load_google_fonts' ),
			'addons_enabled'                 => Forminator::is_addons_feature_enabled(),
			'pluginUrl'                      => forminator_plugin_url(),
			'imagesUrl'                      => forminator_plugin_url() . '/assets/images',
			'addonNonce'                     => Forminator_Addon_Admin_Ajax::get_instance()->get_nonce(),
			'countries'                      => forminator_get_countries_list(),
			'userList'                       => forminator_list_users(),
			'variables'                      => forminator_get_vars(),
			'payment_variables'              => forminator_get_payment_vars(),
			'maxUpload'                      => forminator_get_max_upload(),
			'captchaLangs'                   => forminator_get_captcha_languages(),
			'erasure'                        => get_option( 'forminator_enable_erasure_request_erase_form_submissions', false ),
			'retain_number'                  => get_option( 'forminator_retain_submissions_interval_number', 0 ),
			'retain_unit'                    => get_option( 'forminator_retain_submissions_interval_unit', 'days' ),
			'poll_ip_retain_number'          => get_option( 'forminator_retain_votes_interval_number', 0 ),
			'poll_ip_retain_unit'            => get_option( 'forminator_retain_votes_interval_unit', 'days' ),
			'submissions_ip_retain_number'   => get_option( 'forminator_retain_poll_submissions_interval_number', 0 ),
			'submissions_ip_retain_unit'     => get_option( 'forminator_retain_poll_submissions_interval_unit', 'days' ),
			'submissions_quiz_retain_number' => get_option( 'forminator_retain_quiz_submissions_interval_number', 0 ),
			'submissions_quiz_retain_unit'   => get_option( 'forminator_retain_quiz_submissions_interval_unit', 'days' ),
			'fileExts'                       => forminator_get_ext_types(),
			'version'                        => FORMINATOR_VERSION,
			'showDocLink'                    => forminator_is_show_documentation_link(),
			'showBranding'                   => forminator_is_show_branding(),
			'currencies'                     => forminator_currency_list(),
			'ppCurrencies'                   => forminator_pp_currency_list(),
			'postTypeList'                   => forminator_post_type_list(),
			'postCategories'                 => forminator_post_categories(),
			'isPro'                          => FORMINATOR_PRO,
			'userRoles'                      => get_editable_roles(),
			'pages'                          => $this->get_pages(),
			'hasPayPal'                      => forminator_has_paypal_settings(),
			'pollAnswerColors'               => forminator_get_poll_chart_colors(),
			'isMainSite'                     => forminator_is_main_site(),
			'isSubdomainNetwork'             => forminator_is_subdomain_network(),
		);
	}
}
