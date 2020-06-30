<?php
// phpcs:ignoreFile -- this class currently unused, for reference only
/**
 * PayPal Express Payment Gateway
 *
 * @since 1.0
 */

/**
 * To do
 * - do form validation before requesting paypal
 */
class Forminator_PayPal_Express extends Forminator_Payment_Gateway {
	/**
	 * Gateway slug
	 *
	 * @var string
	 */
	protected $_slug = 'paypal_express';

	/**
	 * Api mode
	 *
	 * @var string
	 */
	protected $api_mode = '';

	/**
	 * Sandbox Client ID
	 *
	 * @var string
	 */
	protected $sandbox_id = '';

	/**
	 * Sandbox Secret
	 *
	 * @var string
	 */
	protected $sandbox_secret = '';

	/**
	 * Live Client Id
	 *
	 * @var string
	 */
	protected $live_id = '';

	/**
	 * Live Secret
	 *
	 * @var string
	 */
	protected $live_secret = '';

	/**
	 * Live Mode flag
	 *
	 * @var bool
	 */
	protected $is_live = false;

	protected $redirect_url = '';

	protected $apiContext = null;

	/**
	 * Currency
	 *
	 * @var string
	 */
	protected $currency = 'USD';

	const INVALID_SANDBOX_SECRET_EXCEPTION = 90;
	const INVALID_LIVE_SECRET_EXCEPTION = 91;

	const INVALID_SANDBOX_ID_EXCEPTION = 92;
	const INVALID_LIVE_ID_EXCEPTION = 93;

	const EMPTY_SANDBOX_SECRET_EXCEPTION = 94;
	const EMPTY_LIVE_SECRET_EXCEPTION = 95;

	const EMPTY_SANDBOX_ID_EXCEPTION = 96;
	const EMPTY_LIVE_ID_EXCEPTION = 97;

	/**
	 * Init PayPal settings
	 *
	 * @since 1.0
	 */
	public function init_settings() {
		global $wp;
		$config = get_option( 'forminator_paypal_configuration', array() );

		$this->sandbox_id     = isset( $config['sandbox_id'] ) ? esc_html( $config['sandbox_id'] ) : '';
		$this->sandbox_secret = isset( $config['sandbox_secret'] ) ? esc_html( $config['sandbox_secret'] ) : '';
		$this->live_id        = isset( $config['live_id'] ) ? esc_html( $config['live_id'] ) : '';
		$this->live_secret    = isset( $config['live_secret'] ) ? esc_html( $config['live_secret'] ) : '';
		$this->currency       = isset( $config['currency'] ) ? esc_html ( $config['currency'] ) : 'USD';
		$this->_enabled       = forminator_has_paypal_settings();
		$this->redirect_url   = home_url( $wp->request );

		if ( empty( $this->sandbox_id ) && defined( 'FORMINATOR_PAYPAL_SANDBOX_ID' ) ) {
			$this->sandbox_id = FORMINATOR_PAYPAL_SANDBOX_ID;
		}

		if ( empty( $this->sandbox_secret ) && defined( 'FORMINATOR_PAYPAL_SANDBOX_SECRET' ) ) {
			$this->sandbox_secret = FORMINATOR_PAYPAL_SANDBOX_SECRET;
		}

		add_filter( 'script_loader_src', array( $this, 'forminator_remove_ver_paypal' ), 9999 );
	}

	/**
	 * @return string
	 */
	public function get_sandbox_id() {
		return $this->sandbox_id;
	}

	/**
	 * @return string
	 */
	public function get_sandbox_secret() {
		return $this->sandbox_secret;
	}

	/**
	 * @return string
	 */
	public function get_live_id() {
		return $this->live_id;
	}

	/**
	 * @return string
	 */
	public function get_live_secret() {
		return $this->live_secret;
	}

	/**
	 * @return string
	 */
	public function get_default_currency() {
		return $this->currency;
	}

	/**
	 * @return bool
	 */
	public function is_live() {
		return $this->is_live;
	}

	/**
	 * Store stripe settings
	 *
	 * @param $settings
	 */
	public static function store_settings( $settings ) {
		update_option( 'forminator_paypal_configuration', $settings );
	}

	/**
	 * @return bool
	 */
	public function is_live_ready() {
		return ! empty( $this->live_id ) && ! empty( $this->live_secret );
	}

	/**
	 * @return bool
	 */
	public function is_test_ready() {
		return ! empty( $this->sandbox_id ) && ! empty( $this->sandbox_secret );
	}

	/**
	 * @return bool
	 */
	public function is_ready() {
		if ( $this->is_live ) {
			return $this->is_live_ready();
		}

		return $this->is_test_ready();
	}

	/**
	 * @param bool $live
	 */
	public function set_live( $live ) {
		$this->is_live = $live;
	}

	public static function is_available() {
		$min_php_version = apply_filters( 'forminator_payments_paypal_min_php_version', '5.3' );
		$loaded          = forminator_payment_lib_paypal_version_loaded();

		if ( version_compare( PHP_VERSION, $min_php_version, 'lt' ) ) {
			return false;
		}

		return $loaded;
	}


	/**
	 * Handle purchase
	 *
	 * @since 1.0
	 *
	 * @param array $response
	 * @param array $product_fields
	 * @param $field_data_array
	 * @param int $entry_id
	 * @param int $page_id
	 * @param int $shipping
	 *
	 * @return array
	 */
	protected function handle_purchase( $response, $product_fields, $field_data_array, $entry_id, $page_id, $shipping ) {
		return $response;
	}

	/**
	 * Gateway footer scripts
	 *
	 * @since 1.0
	 */
	public function gateway_footer_scripts() {

	}

	/**
	 * Gateway footer scripts
	 *
	 * @since 1.0
	 */
	public function render_buttons_script( $paypal_form_id ) {

	}

	/**
	 * Make PayPal call
	 *
	 * @since 1.0
	 *
	 * @param $payment_id
	 * @param $form_id
	 * @param $data
	 *
	 * @return array
	 */
	public function paypal_check( $payment_id, $form_id, $data ) {
		$payment_response = array();
		$error            = array();
		if ( ! empty( $payment_id ) && ! empty( $form_id ) ) {
			try {
				$mode = ! empty( $data['mode'] ) ? $data['mode'] : 'sandbox';
				$debug_mode = ! empty( $data['debug_mode'] ) ? $data['debug_mode'] : 'disable';
				$this->apiContext = $this->getApiContext( $form_id, $mode, $debug_mode );
				$payment          = Forminator\PayPal\Api\Payment::get( $payment_id, $this->apiContext );
				$payment_result   = json_decode( $payment );
				if ( ! empty( $payment_result ) && 'approved' !== $payment_result->state ) {
					$error[] = __( 'PayPal payment fail', Forminator::DOMAIN );
				}
				$payment_response['error'] = $error;
				forminator_maybe_log( __METHOD__, $payment_response );
			} catch ( Exception $e ) {
				$payment_response['error'] = $e->getMessage();
			}
		}

		return $payment_response;
	}

	/**
	 * @param $mode
	 * @param $id
	 * @param $secret
	 * @param $error
	 *
	 * @throws Forminator_Gateway_Exception
	 */
	public static function validate_id( $mode, $id, $secret, $error = self::INVALID_SANDBOX_SECRET_EXCEPTION ) {
		/**
		 * You should use Checkout, Elements, or our mobile libraries to perform this process, client-side.
		 * This ensures that no sensitive card data touches your server, and allows your integration to operate in a PCI-compliant way.
		 */

		try {
			$cred       = new Forminator\PayPal\Auth\OAuthTokenCredential(
				$id,
				$secret
			);
			$config     = array(
				'mode'           => $mode,
				'log.LogEnabled' => true,
				'log.FileName'   => forminator_plugin_dir() . 'PayPal.log',
				'log.LogLevel'   => 'DEBUG',
				'cache.enabled'  => false,
			);
			if ( class_exists( 'Forminator\PayPal\REST\ApiContext' ) ) {
				$apiContext = new Forminator\PayPal\REST\ApiContext( $cred );
				$apiContext->setConfig( $config );
				forminator_maybe_log( __METHOD__, $apiContext );
			}
			$access_token = $cred->getAccessToken( $config );
			if ( ! $access_token ) {
				throw new Forminator_Gateway_Exception( __( 'Failed to configure PayPal payment', Forminator::DOMAIN ) );
			}
		} catch ( Exception $e ) {
			forminator_maybe_log( __METHOD__, $e->getMessage() );
			throw new Forminator_Gateway_Exception(
				__( 'Some error has occurred while connecting to your PayPal account. Please resolve the following errors and try to connect again.', Forminator::DOMAIN ),
				$error,
				$e
			);
		}
	}


	/**
	 * Get PayPal API Context
	 *
	 * @param $form_id
	 * @param $mode
	 * @param $debug
	 *
	 * @return \Forminator\PayPal\Rest\ApiContext
	 */
	public function getApiContext( $form_id, $mode, $debug ) {

		$clientId     = 'live' === $mode ? $this->live_id : $this->sandbox_id;
		$clientSecret = 'live' === $mode ? $this->live_secret : $this->sandbox_secret;
		$apiContext   = new Forminator\PayPal\Rest\ApiContext(
			new Forminator\PayPal\Auth\OAuthTokenCredential(
				$clientId, $clientSecret
			)
		);
		$apiContext->setConfig(
			array(
				'mode'           => $mode,
				'log.LogEnabled' => 'enable' === $debug ? true : false,
				'log.FileName'   => forminator_plugin_dir() . 'PayPal' . $form_id . '.log',
				'log.LogLevel'   => 'DEBUG',
				'cache.enabled'  => false,
			)
		);

		return $apiContext;
	}

	/**
	 * Remove ver from script
	 * @param $src
	 *
	 * @return string
	 */
	public function forminator_remove_ver_paypal( $src ) {
		if ( strpos( $src, 'paypal.com' ) && strpos( $src, 'ver=' ) )
			$src = remove_query_arg( 'ver', $src );
		return $src;
	}
}
