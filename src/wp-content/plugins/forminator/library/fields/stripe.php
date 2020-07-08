<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Stripe
 *
 * @since 1.7
 */
class Forminator_Stripe extends Forminator_Field {

	/**
	 * @var string
	 */
	public $name = '';

	/**
	 * @var string
	 */
	public $slug = 'stripe';

	/**
	 * @var string
	 */
	public $type = 'stripe';

	/**
	 * @var int
	 */
	public $position = 23;

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
	public $icon = 'sui-icon-stripe';

	/**
	 * @var bool
	 */
	public $is_connected = false;

	/**
	 * @var string
	 */
	public $mode = 'test';

	/**
	 * Forminator_Stripe constructor.
	 *
	 */
	public function __construct() {
		parent::__construct();

		$this->name = __( 'Stripe', Forminator::DOMAIN );

		try {
			$stripe = new Forminator_Gateway_Stripe();
			if ( $stripe->is_test_ready() && $stripe->is_live_ready() ) {
				$this->is_connected = true;
			}
		} catch ( Forminator_Gateway_Exception $e ) {
			$this->is_connected = false;
		}

	}

	/**
	 * Field defaults
	 *
	 * @return array
	 */
	public function defaults() {

		$default_currency = 'USD';
		try {
			$stripe           = new Forminator_Gateway_Stripe();
			$default_currency = $stripe->get_default_currency();
		} catch ( Forminator_Gateway_Exception $e ) {
			forminator_maybe_log( __METHOD__, $e->getMessage() );
		}

		return array(
			'field_label'         => __( 'Credit / Debit Card', Forminator::DOMAIN ),
			'mode'                => 'test',
			'currency'            => $default_currency,
			'amount_type'         => 'fixed',
			'logo'                => '',
			'company_name'        => '',
			'product_description' => '',
			'customer_email'      => '',
			'receipt'             => 'false',
			'billing'             => 'false',
			'verify_zip'          => 'false',
			'card_icon'           => 'true',
			'language'            => 'auto',
			'options'             => array(),
			'base_class'          => 'StripeElement',
			'complete_class'      => 'StripeElement--complete',
			'empty_class'         => 'StripeElement--empty',
			'focused_class'       => 'StripeElement--focus',
			'invalid_class'       => 'StripeElement--invalid',
			'autofilled_class'    => 'StripeElement--webkit-autofill',
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
		$description         = esc_html( self::get_property( 'description', $field, '' ) );
		$label               = esc_html( self::get_property( 'field_label', $field, '' ) );
		$element_name        = $id;
		$field_id            = $id . '-field';
		$mode                = self::get_property( 'mode', $field, 'test' );
		$currency            = self::get_property( 'currency', $field, $this->get_default_currency() );
		$amount_type         = self::get_property( 'amount_type', $field, 'fixed' );
		$amount              = self::get_property( 'amount', $field, 1 );
		$amount_variable     = self::get_property( 'variable', $field, '' );
		$card_icon           = self::get_property( 'card_icon', $field, true );
		$verify_zip          = self::get_property( 'verify_zip', $field, false );
		$zip_field           = self::get_property( 'zip_field', $field, '' );
		$language            = self::get_property( 'language', $field, 'auto' );
		$base_class          = self::get_property( 'base_class', $field, 'StripeElement' );
		$complete_class      = self::get_property( 'complete_class', $field, 'StripeElement--complete' );
		$empty_class         = self::get_property( 'empty_class', $field, 'StripeElement--empty' );
		$focused_class       = self::get_property( 'focused_class', $field, 'StripeElement--focus' );
		$invalid_class       = self::get_property( 'invalid_class', $field, 'StripeElement--invalid' );
		$autofilled_class    = self::get_property( 'autofilled_class', $field, 'StripeElement--webkit-autofill');
		$billing             = self::get_property( 'billing', $field, false );
		$billing_name        = self::get_property( 'billing_name', $field, '' );
		$billing_email       = self::get_property( 'billing_email', $field, '' );
		$billing_address     = self::get_property( 'billing_address', $field, '' );
		$receipt             = self::get_property( 'receipt', $field, false );
		$customer_email      = self::get_property( 'customer_email', $field, '' );
		$metadata            = self::get_property( 'options', $field, array() );
		$desc                = self::get_property( 'product_description', $field, '' );
		$company             = self::get_property( 'company_name', $field, '' );
		$uniqid              = uniqid();

		if ( mb_strlen( $company ) > 22 ) {
			$company = mb_substr( $company, 0, 19 ) . '...';
		}

		$start_amount   = ( 'fixed' === $amount_type ? esc_html( $amount ) : 1 );
		$customer_email = forminator_clear_field_id( $customer_email );
		$custom_fonts   = false;

		if ( 'fixed' !== $amount_type ) {
			$currency = 'usd';
		}

		// Generate payment intent object
		$this->mode = $mode;

		if ( isset( $settings['form-font-family'] ) && 'custom' === $settings['form-font-family'] ) {
			$custom_fonts = true;
		}

		$attr = array(
			'data-field-id'         => $uniqid,
			'data-is-payment'       => 'true',
			'data-payment-type'     => $this->type,
			'data-secret'           => "",
			'data-paymentid'        => "",
			'data-key'              => esc_html( $this->get_publishable_key( 'test' !== $mode ) ),
			'data-card-icon'        => filter_var( $card_icon, FILTER_VALIDATE_BOOLEAN ),
			'data-veify-zip'        => filter_var( $verify_zip, FILTER_VALIDATE_BOOLEAN ),
			'data-zip-field'        => esc_html( $zip_field ),
			'data-language'         => esc_html( $language ),
			'data-base-class'       => esc_html( $base_class ),
			'data-complete-class'   => esc_html( $complete_class ),
			'data-empty-class'      => esc_html( $empty_class ),
			'data-focused-class'    => esc_html( $focused_class ),
			'data-invalid-class'    => esc_html( $invalid_class ),
			'data-autofilled-class' => esc_html( $autofilled_class ),
			'data-billing'          => filter_var( $billing, FILTER_VALIDATE_BOOLEAN ),
			'data-billing-name'     => esc_html( $billing_name ),
			'data-billing-email'    => esc_html( $billing_email ),
			'data-billing-address'  => esc_html( $billing_address ),
			'data-receipt'          => filter_var( $receipt, FILTER_VALIDATE_BOOLEAN ),
			'data-receipt-email'    => esc_html( $customer_email ),
			'data-custom-fonts'     => $custom_fonts,
			'data-placeholder'      => $this->get_form_setting( 'input-placeholder', $settings, '#888888' ),
			'data-font-color'       => $this->get_form_setting( 'input-color', $settings, '#000000' ),
			'data-font-color-focus' => $this->get_form_setting( 'input-color', $settings, '#000000' ),
			'data-font-color-error' => $this->get_form_setting( 'input-color', $settings, '#000000' ),
			'data-font-size'        => $this->get_form_setting( 'cform-input-font-size', $settings, '16' ) . 'px',
			// 'data-line-height'      => '1.3em',
			'data-font-family'      => $this->get_form_setting( 'cform-input-font-family', $settings, 'inherit' ),
			'data-font-weight'      => $this->get_form_setting( 'cform-input-font-weight', $settings, '400' ),
			'data-icon-color'       => $this->get_form_setting( 'input-icon', $settings, '#777771' ),
			'data-icon-color-hover' => $this->get_form_setting( 'input-icon-hover', $settings, '#17A8E3' ),
			'data-icon-color-focus' => $this->get_form_setting( 'input-icon-focus', $settings, '#17A8E3' ),
			'data-icon-color-error' => $this->get_form_setting( 'label-validation-color', $settings, '#E04562' ),
		);

		$attributes = self::implode_attr( $attr );

		$html = '<div class="forminator-field">';

		if ( $label ) {
			$html .= sprintf(
				'<label for="%s" class="forminator-label">%s %s</label>',
				$id . '-field',
				$label,
				forminator_get_required_icon()
			);
		}

		if ( 'material' === $this->form_settings['form-style'] ) {
			$classes = 'forminator-input--wrap forminator-input--stripe';

			if ( empty( $label ) ) {
				$classes .= ' forminator--no_label';
			}

			$html .= '<div class="' . $classes . '">';
		}

		$html .= sprintf( '<div id="card-element-%s" %s" class="forminator-stripe-element"></div>', $uniqid, $attributes );

		$html .= sprintf( '<input type="hidden" name="paymentid" value="%s" id="forminator-stripe-paymentid"/>', "" );

		if ( 'material' === $this->form_settings['form-style'] ) {
			$html .= '</div>';
		}

		$html .= '<span class="forminator-card-message"><span class="forminator-error-message" aria-hidden="true"></span></span>';

		$html .= self::get_description( $description );

		$html .= '</div>';

		return apply_filters( 'forminator_field_stripe_markup', $html, $attr, $field );
	}

	/**
	 * Generate Payment Intent object
	 *
	 * @since 1.7.3
	 *
	 * @param $amount
	 * @param $field
	 *
	 * @return mixed
	 */
	public function generate_paymentIntent( $amount, $field ) {
		$currency    = self::get_property( 'currency', $field, $this->get_default_currency() );
		$mode        = self::get_property( 'mode', $field, 'test' );
		$metadata    = self::get_property( 'options', $field, array() );
		$description = esc_html( self::get_property( 'product_description', $field, '' ) );
		$company     = esc_html( self::get_property( 'company_name', $field, '' ) );

		if ( mb_strlen( $company ) > 22 ) {
			$company = mb_substr( $company, 0, 19 ) . '...';
		}

		$key = $this->get_secret_key( 'test' !== $mode );
		\Forminator\Stripe\Stripe::setApiKey( $key );

		Forminator_Gateway_Stripe::set_stripe_app_info();

		$metadata_object = array();
		foreach ( $metadata as $meta ) {
			$metadata_object[ $meta['label'] ] = $meta['value'];
		}

		// Default options
		$options = array(
			'amount'   => $this->calculate_amount( $amount, $currency ),
			'currency' => $currency,
		);

		// Check if metadata is not empty and add it to the options
		if ( ! empty( $metadata_object ) ) {
			$options['metadata'] = $metadata_object;
		}

		// Check if statement_description is not empty and add it to the options
		if ( ! empty( $company ) ) {
			$options['statement_descriptor'] = $company;
		}

		// Check if description is not empty and add it to the options
		if ( ! empty( $description ) ) {
			$options['description'] = $description;
		}

		try {
			// Create Payment Intent object
			$intent = \Forminator\Stripe\PaymentIntent::create( $options );
		} catch ( Exception $e ) {
			$response = array(
				'message' => $e->getMessage(),
				'errors' => array()
			);

			wp_send_json_error( $response );
		}

		return $intent;
	}

	/**
	 * Calculate Stripe amount
	 *
	 * @since 1.11
	 *
	 * @param $amount
	 * @param $currency
	 *
	 * @return float|int
	 */
	public function calculate_amount( $amount, $currency ) {
		$zero_decimal_currencies = $this->get_zero_decimal_currencies();

		// Check if currency is zero decimal, then return original amount
		if ( in_array( $currency, $zero_decimal_currencies ) ) {
			return $amount;
		}

		// Currency has decimals, multiply by 100
		return $amount * 100;
	}

	/**
	 * Return currencies without decimal
	 *
	 * @since 1.11
	 *
	 * @return array
	 */
	public function get_zero_decimal_currencies() {
		return array(
			'MGA',
			'BIF',
			'CLP',
			'PYG',
			'DJF',
			'RWF',
			'GNF',
			'UGX',
			'VND',
			'JPY',
			'VUV',
			'XAF',
			'KMF',
			'XOF',
			'KRW',
			'XPF',
		);
	}

	/**
	 * Update amount
	 *
	 * @since 1.7.3
	 *
	 * @param $id
	 * @param $amount
	 * @param $submitted_data
	 * @param $field
	 * @param $pseudo_submitted_data
	 */
	public function update_paymentIntent( $id, $amount, $submitted_data, $field, $pseudo_submitted_data ) {
		$mode        = self::get_property( 'mode', $field, 'test' );
		$currency    = self::get_property( 'currency', $field, $this->get_default_currency() );

		// Get Stripe key
		$key = $this->get_secret_key( 'test' !== $mode );

		// Set Stripe key
		\Forminator\Stripe\Stripe::setApiKey( $key );

		Forminator_Gateway_Stripe::set_stripe_app_info();

		// Check if we already have payment ID, if not generate new one
		if ( empty( $id ) ) {
			$payment_intent = $this->generate_paymentIntent( $amount, $field );

			$id = $payment_intent->id;
		}

		try {
			// Retrieve PI object
			$intent = \Forminator\Stripe\PaymentIntent::retrieve( $id );
		} catch ( Exception $e ) {
			$payment_intent = $this->generate_paymentIntent( $amount, $field );

			$intent = \Forminator\Stripe\PaymentIntent::retrieve( $payment_intent->$id );
		}

		// Convert object to array
		$stored_metadata = $intent->metadata->toArray();

		// New metadata array
		$metadata = array();

		$submitted_data_combined = array_merge( $submitted_data, $pseudo_submitted_data );

		if( ! empty( $stored_metadata ) ) {
			foreach( (array) $stored_metadata as $key => $meta ) {
				$metadata[ $key ] = forminator_replace_form_data( '{' . $meta . '}', $submitted_data_combined );
			}
		}

		// Check if the PaymentIntent already succeeded and continue
		if( "succeeded" === $intent->status ) {
			wp_send_json_success(
				array(
					'paymentid' => $id,
					'paymentsecret' => $intent->client_secret,
				)
			);
		} else {
			try {
				// Check payment amount
				if ( 0 > $amount ) {
					throw new Exception( __( 'Payment amount should be larger than 0', Forminator::DOMAIN ) );
				}

				// Check payment ID
				if ( empty( $id ) ) {
					throw new Exception( __( 'Your Payment ID is empty!', Forminator::DOMAIN ) );
				}

				$options = array(
					'amount' => $this->calculate_amount( $amount, $currency ),
				);

				if ( ! empty( $metadata ) ) {
					$options['metadata'] = $metadata;
				}

				// Update Payment Intent amount
				\Forminator\Stripe\PaymentIntent::update(
					$id,
					$options
				);

				// Return success
				wp_send_json_success(
					array(
						'paymentid' => $id,
						'paymentsecret' => $intent->client_secret,
					)
				);

			} catch ( Exception $e ) {
				$response = array(
					'message' => $e->getMessage(),
					'errors' => array()
				);

				wp_send_json_error( $response );
			}
		}
	}

	/**
	 * Get form setting
	 *
	 * @since 1.9
	 *
	 * @param $id
	 * @param $settings
	 * @param $fallback
	 *
	 * @return mixed
	 */
	public function get_form_setting( $id, $settings, $fallback ) {
		// Check if user settings exist
		if ( isset( $settings[ $id ] ) ) {
			return $settings[ $id ];
		}

		// Return fallback
		return $fallback;
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

		return apply_filters( 'forminator_field_stripe_sanitize', $data, $field, $original_data );
	}

	/**
	 * @since 1.7
	 * @inheritdoc
	 */
	public function is_available( $field ) {
		$mode = self::get_property( 'mode', $field, 'test' );
		try {
			$stripe = new Forminator_Gateway_Stripe();

			if ( 'test' !== $mode ) {
				$stripe->set_live( true );
			}

			if ( $stripe->is_ready() ) {
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
			$stripe = new Forminator_Gateway_Stripe();

			if ( $live ) {
				return $stripe->get_live_key();
			}

			return $stripe->get_test_key();
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
	private function get_secret_key( $live = false ) {
		try {
			$stripe = new Forminator_Gateway_Stripe();

			if ( $live ) {
				return $stripe->get_live_secret();
			}

			return $stripe->get_test_secret();
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
			$stripe = new Forminator_Gateway_Stripe();

			return $stripe->get_default_currency();

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

		$mode     = self::get_property( 'mode', $field, 'test' );
		$currency = self::get_property( 'currency', $field, $this->get_default_currency() );

		// Check Stripe key
		$key = $this->get_secret_key( 'test' !== $mode );

		// Set Stripe key
		\Forminator\Stripe\Stripe::setApiKey( $key );

		Forminator_Gateway_Stripe::set_stripe_app_info();

		try {
			// Makue sure payment ID exist
			if ( ! isset( $submitted_data['paymentid'] ) ) {
				throw new Exception( __('Stripe Payment ID does not exist.', Forminator::DOMAIN ) );
			}

			// Check payment amount
			$intent = \Forminator\Stripe\PaymentIntent::retrieve( $submitted_data['paymentid'] );

			// Makue sure Payment Intent succeeded
			if ( "succeeded" !== $intent->status ) {
				throw new Exception( __('Stripe Token not found on submitted data.', Forminator::DOMAIN ) );
			}

			$charge_amount = $this->get_payment_amount( $field, $custom_form, $submitted_data, $pseudo_submitted_data );

			$entry_data['mode']     = $mode;
			$entry_data['currency'] = $currency;
			$entry_data['amount']   = $charge_amount;

			$entry_data['transaction_id'] = $intent->id;

			$transaction_link = 'https://dashboard.stripe.com/payments/' . rawurlencode( $intent->id );

			if ( 'test' === $mode ) {
				$transaction_link = 'https://dashboard.stripe.com/test/payments/' . rawurlencode( $intent->id );
			}

			$entry_data['transaction_link'] = $transaction_link;
			$entry_data['status']           = 'success';
			$entry_data['transaction_id']   = $intent->id;
		} catch ( Exception $e ) {
			$entry_data['status']     = 'fail';
			$entry_data['error']      = $e->getMessage();
			$entry_data['error_type'] = 'stripe_error';
		}

		/**
		 * Filter stripe entry data that will be stored
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
		$entry_data = apply_filters( 'forminator_field_stripe_process_to_entry_data', $entry_data, $field, $custom_form, $submitted_data, $field_data_array );

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
		 * Filter link to Stripe transaction id
		 *
		 * @since 1.7
		 *
		 * @param string $transaction_link
		 * @param string $transaction_id
		 * @param array  $meta_value
		 *
		 * @return string
		 */
		$transaction_link = apply_filters( 'forminator_field_stripe_linkify_transaction_id', $transaction_link, $transaction_id, $meta_value );

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
		 * Filter payment amount of stripe
		 *
		 * @since 1.7
		 *
		 * @param double                       $payment_amount
		 * @param array                        $field field settings
		 * @param Forminator_Custom_Form_Model $custom_form
		 * @param array                        $submitted_data
		 * @param array                        $pseudo_submitted_data
		 */
		$payment_amount = apply_filters( 'forminator_field_stripe_payment_amount', $payment_amount, $field, $custom_form, $submitted_data, $pseudo_submitted_data );

		return $payment_amount;
	}
}
