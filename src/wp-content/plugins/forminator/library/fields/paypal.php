<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_PayPal
 *
 * @since 1.7
 */
class Forminator_PayPal extends Forminator_Field {

	/**
	 * @var string
	 */
	public $name = '';

	/**
	 * @var string
	 */
	public $slug = 'paypal';

	/**
	 * @var string
	 */
	public $type = 'paypal';

	/**
	 * @var int
	 */
	public $position = 24;

	/**
	 * @var array
	 */
	public $options = array();

	/**
	 * @var string
	 */
	public $category = 'standard';


	/**
	 * @var string
	 */
	public $icon = 'sui-icon-paypal';

	public $is_connected = false;

	/**
	 * Forminator_PayPal constructor.
	 *
	 */
	public function __construct() {
		parent::__construct();

		$this->name = __( 'PayPal', Forminator::DOMAIN );
	}

	/**
	 * Field defaults
	 *
	 * @return array
	 */
	public function defaults() {

		$default_currency = 'USD';

		return array(
			'mode'        => 'sandbox',
			'currency'    => $default_currency,
			'amount_type' => 'fixed',
			'label'       => 'checkout',
			'color'       => 'gold',
			'shape'       => 'rect',
			'layout'      => 'vertical',
			'tagline'     => 'true',
			'locale'      => 'en_US',
			'debug_mode'  => 'disable',
			'height'      => '40',
			'options'     => array(),
		);
	}

	/**
	 * Field front-end markup
	 *
	 *
	 * @param $field
	 * @param $settings
	 *
	 * @return mixed
	 */
	public function markup( $field, $settings = array() ) {

		$this->field         = $field;
		$this->form_settings = $settings;

		$id                  = self::get_property( 'element_id', $field );
		$element_name        = $id;
		$field_id            = $id . '-field';
		$mode                = self::get_property( 'mode', $field, 'sandbox' );
		$currency            = self::get_property( 'currency', $field, $this->get_default_currency() );
		$amount_type         = self::get_property( 'amount_type', $field, 'fixed' );
		$amount              = self::get_property( 'amount', $field, '0' );
		$amount_variable     = self::get_property( 'variable', $field, '' );
		$logo                = self::get_property( 'logo', $field, '' );
		$company_name        = esc_html( self::get_property( 'company_name', $field, '' ) );
		$product_description = esc_html( self::get_property( 'product_description', $field, '' ) );
		$customer_email      = self::get_property( 'customer_email', $field, '' );
		$checkout_label      = esc_html( self::get_property( 'checkout_label', $field, '' ) );
		$collect_address     = esc_html( self::get_property( 'collect_address', $field, 'none', 'string' ) );
		$verify_zip          = esc_html( self::get_property( 'verify_zip', $field, false, 'bool' ) );
		$language            = self::get_property( 'language', $field, 'en' );

		$attr = array(
			'type'              => 'hidden',
			'name'              => $element_name,
			'id'                => 'forminator-' . $field_id . '-field',
			'class'             => 'forminator-paypal-input',
			'data-is-payment'   => 'true',
			'data-payment-type' => $this->type,
			'data-currency'     => esc_html( strtolower( $currency ) ),
			'data-amount-type'  => esc_html( $amount_type ),
			'data-amount'       => ( 'fixed' === $amount_type ? esc_html( $amount ) : $amount_variable ),
			'data-label'        => esc_html( $checkout_label ),
			'data-locale'       => esc_html( $language ),
		);

		if ( ! empty( $logo ) ) {
			$attr['data-image'] = esc_url( $logo );
		}

		if ( ! empty( $company_name ) ) {
			$attr['data-name'] = esc_html( $company_name );
		}

		if ( ! empty( $company_name ) ) {
			$attr['data-description'] = esc_html( $product_description );
		}

		if ( ! empty( $customer_email ) ) {
			$attr['data-email'] = esc_html( $customer_email );
		}

		if ( 'billing' === $collect_address || 'billing_shipping' === $collect_address ) {
			$attr['data-billing-address'] = 'true';
		}

		if ( 'billing_shipping' === $collect_address ) {
			$attr['data-shipping-address'] = 'true';
		}

		if ( $verify_zip ) {
			$attr['data-zip-code'] = 'true';
		}

		$html = self::create_input( $attr );

		return apply_filters( 'forminator_field_paypal_markup', $html, $attr, $field );
	}


	/**
	 * Field back-end validation
	 *
	 *
	 * @param array        $field
	 * @param array|string $data
	 */
	public function validate( $field, $data ) {
		$id = self::get_property( 'element_id', $field );
	}

	/**
	 * Sanitize data
	 *
	 *
	 * @param array        $field
	 * @param array|string $data - the data to be sanitized
	 *
	 * @return array|string $data - the data after sanitization
	 */
	public function sanitize( $field, $data ) {
		$original_data = $data;
		// Sanitize
		$data = forminator_sanitize_field( $data );

		return apply_filters( 'forminator_field_paypal_sanitize', $data, $field, $original_data );
	}

	/**
	 * @since 1.7
	 * @inheritdoc
	 */
	public function is_available( $field ) {
		$mode = self::get_property( 'mode', $field, 'sandbox' );
		try {
			$paypal = new Forminator_PayPal_Express();

			if ( 'sandbox' !== $mode ) {
				$paypal->set_live( true );
			}

			if ( $paypal->is_ready() ) {
				return true;
			}
		} catch ( Forminator_Gateway_Exception $e ) {
			return false;
		}
	}

	/**
	 * Get publishable key
	 *
	 * @since 1.7
	 *
	 * @param bool $live
	 *
	 * @return bool|string
	 */
	private function get_publishable_key( $live = false ) {
		try {
			$paypal = new Forminator_PayPal_Express();

			if ( $live ) {
				return $paypal->get_live_id();
			}

			return $paypal->get_sandbox_id();
		} catch ( Forminator_Gateway_Exception $e ) {
			return false;
		}

	}

	/**
	 * Get default currency
	 *
	 * @return string
	 */
	private function get_default_currency() {
		try {
			$paypal = new Forminator_PayPal_Express();

			return $paypal->get_default_currency();

		} catch ( Forminator_Gateway_Exception $e ) {
			return 'USD';
		}
	}

	/**
	 * @param array                        $field
	 * @param Forminator_Custom_Form_Model $custom_form
	 * @param array                        $submitted_data
	 * @param array                        $pseudo_submitted_data
	 * @param array                        $field_data_array
	 *
	 * @return array
	 */
	public function process_to_entry_data( $field, $custom_form, $submitted_data, $pseudo_submitted_data, $field_data_array ) {
		$entry_data = array(
			'mode'             => '',
			'status'           => '',
			'amount'           => '',
			'currency'         => '',
			'transaction_id'   => '',
			'transaction_link' => '',
		);

		$element_id = self::get_property( 'element_id', $field );
		$mode       = self::get_property( 'mode', $field, 'sandbox' );
		$currency   = self::get_property( 'currency', $field, $this->get_default_currency() );

		$entry_data['mode']     = $mode;
		$entry_data['currency'] = $currency;
		$charge_amount          = $this->get_payment_amount( $field, $custom_form, $submitted_data, $pseudo_submitted_data );

		$entry_data['amount']         = number_format( $charge_amount, 2 );
		$entry_data['status']         = 'success';
		$entry_data['transaction_id'] = $submitted_data[ $element_id ];

		$transaction_link = 'https://www.paypal.com/activity/payment/' . rawurlencode( $submitted_data[ $element_id ] );
		if ( 'sandbox' === $mode ) {
			$transaction_link = 'https://www.sandbox.paypal.com/activity/payment/' . rawurlencode( $submitted_data[ $element_id ] );
		}
		$entry_data['transaction_link'] = $transaction_link;
		/**
		 * Filter PayPal entry data that will be stored
		 *
		 * @since 1.7
		 *
		 * @param array                        $entry_data
		 * @param array                        $field            field properties
		 * @param Forminator_Custom_Form_Model $custom_form
		 * @param array                        $submitted_data
		 * @param array                        $field_data_array current entry meta
		 *
		 * @return array
		 */
		$entry_data = apply_filters( 'forminator_field_paypal_process_to_entry_data', $entry_data, $field, $custom_form, $submitted_data, $field_data_array );

		return $entry_data;
	}

	/**
	 * Make linkify transaction_id
	 *
	 * @param $transaction_id
	 * @param $meta_value
	 *
	 * @return string
	 */
	public static function linkify_transaction_id( $transaction_id, $meta_value ) {
		$transaction_link = $transaction_id;
		if ( isset( $meta_value['transaction_link'] ) && ! empty( $meta_value['transaction_link'] ) ) {
			$url              = $meta_value['transaction_link'];
			$transaction_link = '<a href="' . $url . '" target="_blank" rel="noopener noreferrer" title="' . $transaction_id . '">' . $transaction_id . '</a>';
		}

		/**
		 * Filter link to PayPal transaction id
		 *
		 * @since 1.7
		 *
		 * @param string $transaction_link
		 * @param string $transaction_id
		 * @param array  $meta_value
		 *
		 * @return string
		 */
		$transaction_link = apply_filters( 'forminator_field_paypal_linkify_transaction_id', $transaction_link, $transaction_id, $meta_value );

		return $transaction_link;
	}

	/**
	 * Get payment amount
	 *
	 * @since 1.7
	 *
	 * @param array                        $field
	 * @param Forminator_Custom_Form_Model $custom_form
	 * @param array                        $submitted_data
	 * @param array                        $pseudo_submitted_data
	 *
	 * @return double
	 */
	public function get_payment_amount( $field, $custom_form, $submitted_data, $pseudo_submitted_data ) {
		$payment_amount  = 0.0;
		$amount_type     = self::get_property( 'amount_type', $field, 'fixed' );
		$amount          = self::get_property( 'amount', $field, '0' );
		$amount_variable = self::get_property( 'variable', $field, '' );

		if ( 'fixed' === $amount_type ) {
			$payment_amount = $amount;
		} else {
			$amount_var = $amount_variable;
			$form_field = $custom_form->get_field( $amount_var, false );
			if ( $form_field ) {
				$form_field        = $form_field->to_formatted_array();
				$fields_collection = forminator_fields_to_array();
				if ( isset( $form_field['type'] ) ) {
					if ( 'calculation' === $form_field['type'] ) {

						// Calculation field get the amount from pseudo_submit_data
						if ( isset( $pseudo_submitted_data[ $amount_var ] ) ) {
							$payment_amount = $pseudo_submitted_data[ $amount_var ];
						}
					} elseif ( 'currency' === $form_field['type'] ) {
						// Currency field get the amount from submitted_data
						$field_id = $form_field['element_id'];
						if ( isset( $submitted_data[ $field_id ] ) ) {
							$payment_amount = $submitted_data[ $field_id ];
						}
					} else {
						if ( isset( $fields_collection[ $form_field['type'] ] ) ) {
							/** @var Forminator_Field $field_object */
							$field_object = $fields_collection[ $form_field['type'] ];

							$field_id             = $form_field['element_id'];
							$submitted_field_data = isset( $submitted_data[ $field_id ] ) ? $submitted_data[ $field_id ] : null;
							$payment_amount       = $field_object->get_calculable_value( $submitted_field_data, $form_field );
						}
					}
				}
			}
		}

		if ( ! is_numeric( $payment_amount ) ) {
			$payment_amount = 0.0;
		}

		/**
		 * Filter payment amount of PayPal
		 *
		 * @since 1.7
		 *
		 * @param double                       $payment_amount
		 * @param array                        $field field settings
		 * @param Forminator_Custom_Form_Model $custom_form
		 * @param array                        $submitted_data
		 * @param array                        $pseudo_submitted_data
		 */
		$payment_amount = apply_filters( 'forminator_field_paypal_payment_amount', $payment_amount, $field, $custom_form, $submitted_data, $pseudo_submitted_data );

		return $payment_amount;
	}
}
