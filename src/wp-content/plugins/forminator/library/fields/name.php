<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Name
 *
 * @since 1.0
 */
class Forminator_Name extends Forminator_Field {

	/**
	 * @var string
	 */
	public $name = '';

	/**
	 * @var string
	 */
	public $slug = 'name';

	/**
	 * @var string
	 */
	public $type = 'name';

	/**
	 * @var int
	 */
	public $position = 1;

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
	public $icon = 'sui-icon-profile-male';

	/**
	 * Forminator_Name constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		parent::__construct();
		$this->name = __( 'Name', Forminator::DOMAIN );
	}

	/**
	 * Field defaults
	 *
	 * @since 1.0
	 * @return array
	 */
	public function defaults() {
		return array(
			'field_label'             => __( 'Name', Forminator::DOMAIN ),
			'placeholder'             => __( 'E.g. John Doe', Forminator::DOMAIN ),
			'prefix_label'            => __( 'Prefix', Forminator::DOMAIN ),
			'fname_label'             => __( 'First Name', Forminator::DOMAIN ),
			'fname_placeholder'       => __( 'E.g. John', Forminator::DOMAIN ),
			'mname_label'             => __( 'Middle Name', Forminator::DOMAIN ),
			'mname_placeholder'       => __( 'E.g. Smith', Forminator::DOMAIN ),
			'lname_label'             => __( 'Last Name', Forminator::DOMAIN ),
			'lname_placeholder'       => __( 'E.g. Doe', Forminator::DOMAIN ),
			'prefix'                  => 'true',
			'fname'                   => 'true',
			'mname'                   => 'true',
			'lname'                   => 'true',
			'required_message'        => __( 'Name is required.', Forminator::DOMAIN ),
			'prefix_required_message' => __( 'Prefix is required.', Forminator::DOMAIN ),
			'fname_required_message'  => __( 'First Name is required.', Forminator::DOMAIN ),
			'mname_required_message'  => __( 'Middle Name is required.', Forminator::DOMAIN ),
			'lname_required_message'  => __( 'Last Name is required.', Forminator::DOMAIN ),
		);
	}

	/**
	 * Autofill Setting
	 *
	 * @since 1.0.5
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	public function autofill_settings( $settings = array() ) {

		//single name
		$name_providers = apply_filters( 'forminator_field_' . $this->slug . '_autofill', array(), $this->slug );

		//multi name
		$prefix_providers = apply_filters( 'forminator_field_' . $this->slug . '_prefix_autofill', array(), $this->slug . '_prefix' );
		$fname_providers  = apply_filters( 'forminator_field_' . $this->slug . '_first_name_autofill', array(), $this->slug . '_first_name' );
		$mname_providers  = apply_filters( 'forminator_field_' . $this->slug . '_middle_name_autofill', array(), $this->slug . '_middle_name' );
		$lname_providers  = apply_filters( 'forminator_field_' . $this->slug . '_last_name_autofill', array(), $this->slug . '_last_name' );

		$autofill_settings = array(
			'name'             => array(
				'values' => forminator_build_autofill_providers( $name_providers ),
			),
			'name-prefix'      => array(
				'values' => forminator_build_autofill_providers( $prefix_providers ),
			),
			'name-first-name'  => array(
				'values' => forminator_build_autofill_providers( $fname_providers ),
			),
			'name-middle-name' => array(
				'values' => forminator_build_autofill_providers( $mname_providers ),
			),
			'name-last-name'   => array(
				'values' => forminator_build_autofill_providers( $lname_providers ),
			),
		);

		return $autofill_settings;
	}

	/**
	 * Return simple field markup
	 *
	 * @since 1.0
	 *
	 * @param $field
	 *
	 * @return string
	 */
	public function get_simple( $field, $design ) {
		$html        = '';
		$id          = self::get_property( 'element_id', $field );
		$name        = $id;
		$id          = 'forminator-field-' . $id;
		$required    = self::get_property( 'required', $field, false );
		$ariareq     = 'false';
		$label       = esc_html( self::get_property( 'field_label', $field, '' ) );
		$description = esc_html( self::get_property( 'description', $field, '' ) );
		$placeholder = $this->sanitize_value( self::get_property( 'placeholder', $field ) );

		if ( (bool) $required ) {
			$ariareq = 'true';
		}

		$value = '';

		// Check if Pre-fill parameter used
		if ( $this->has_prefill( $field ) ) {
			// We have pre-fill parameter, use its value or $value
			$value = $this->get_prefill( $field, $value );
		}

		$name_attr = array(
			'type'          => 'text',
			'name'          => $name,
			'value'         => $value,
			'placeholder'   => $placeholder,
			'id'            => $id,
			'class'         => 'forminator-input forminator-name--field',
			'aria-required' => $ariareq,
		);

		$autofill_markup = $this->get_element_autofill_markup_attr( $name, $this->form_settings );

		$name_attr = array_merge( $name_attr, $autofill_markup );

		$html .= '<div class="forminator-field">';

			$html .= self::create_input( $name_attr, $label, $description, $required, $design );

		$html .= '</div>';

		return $html;
	}

	/**
	 * Return multi field first row markup
	 *
	 * @since 1.0
	 *
	 * @param $field
	 * @param $design
	 *
	 * @return string
	 */
	public function get_multi_first_row( $field, $design ) {
		$html     = '';
		$cols     = 12;
		$id       = self::get_property( 'element_id', $field );
		$name     = $id;
		$required = self::get_property( 'required', $field, false );
		$prefix   = self::get_property( 'prefix', $field, false );
		$fname    = self::get_property( 'fname', $field, false );

		/**
		 * Backward compat, we dont have separate required configuration per fields
		 * Fallback value from global `required`
		 *
		 * @since 1.6
		 */
		$prefix_required = self::get_property( 'prefix_required', $field, false, 'bool' );
		$fname_required  = self::get_property( 'fname_required', $field, false, 'bool' );
		$fname_ariareq   = 'false';

		if ( (bool) self::get_property( 'fname_required', $field, false ) ) {
			$fname_ariareq = 'true';
		}

		// If both prefix & first name are enabled, change cols
		if ( $prefix && $fname ) {
			$cols = 6;
		}

		// START: Row
		$html .= '<div class="forminator-row" data-multiple="true">';

			// FIELD: Prefix
		if ( $prefix ) {

			$prefix_data = array(
				'name'  => $id . '-prefix',
				'id'    => 'forminator-field-prefix-' . $id,
				'class' => 'forminator-select',
			);

			$options        = array();
			$prefix_options = forminator_get_name_prefixes();
			$prefill        = false;

			if ( $this->has_prefill( $field, 'prefix' ) ) {
				// We have pre-fill parameter, use its value or $value
				$prefill = $this->get_prefill( $field, false, 'prefix' );
			}

			foreach ( $prefix_options as $key => $pfx ) {
				$selected = false;

				if ( strtolower( $key ) === strtolower( $prefill ) ) {
					$selected = true;
				}
				$options[] = array(
					'value' => esc_html( $key ),
					'label' => esc_html( $pfx ),
                    'selected' => $selected
				);
			}

			$html .= sprintf( '<div class="forminator-col forminator-col-%s">', $cols );

				$html .= '<div class="forminator-field">';

					$html .= self::create_select(
						$prefix_data,
						self::get_property( 'prefix_label', $field ),
						$options,
						self::get_property( 'prefix_placeholder', $field ),
						self::get_property( 'prefix_description', $field ),
						$prefix_required
					);

				$html .= '</div>';

			$html .= '</div>';
		}

			// FIELD: First Name
		if ( $fname ) {

			$first_name = array(
				'type'          => 'text',
				'name'          => $id . '-first-name',
				'placeholder'   => $this->sanitize_value( self::get_property( 'fname_placeholder', $field ) ),
				'id'            => 'forminator-field-first-' . $id,
				'class'         => 'forminator-input',
				'aria-required' => $fname_ariareq,
			);

			$autofill_markup = $this->get_element_autofill_markup_attr( $id . '-first-name', $this->form_settings );

			$first_name = array_merge( $first_name, $autofill_markup );

			$first_name = $this->replace_from_prefill( $field, $first_name, 'fname' );

			$html .= sprintf( '<div class="forminator-col forminator-col-%s">', $cols );

				$html .= '<div class="forminator-field">';

					$html .= self::create_input(
						$first_name,
						esc_html( self::get_property( 'fname_label', $field ) ),
						esc_html( self::get_property( 'fname_description', $field ) ),
						$fname_required,
						$design
					);

				$html .= '</div>';

			$html .= '</div>';
		}

		// END: Row
		$html .= '</div>';

		return $html;
	}

	/**
	 * Return multi field second row markup
	 *
	 * @since 1.0
	 *
	 * @param $field
	 * @param $design @since 1.0.5
	 *
	 * @return string
	 */
	public function get_multi_second_row( $field, $design ) {
		$html     = '';
		$cols     = 12;
		$id       = self::get_property( 'element_id', $field );
		$name     = $id;
		$required = self::get_property( 'required', $field, false );
		$mname    = self::get_property( 'mname', $field, false );
		$lname    = self::get_property( 'lname', $field, false );

		/**
		 * Backward compat, we dont have separate required configuration per fields
		 * Fallback value from global `required`
		 *
		 * @since 1.6
		 */
		$mname_required = self::get_property( 'mname_required', $field, false, 'bool' );
		$mname_ariareq  = 'false';
		$lname_required = self::get_property( 'lname_required', $field, false, 'bool' );
		$lname_ariareq  = 'false';

		if ( (bool) self::get_property( 'mname_required', $field, false ) ) {
			$mname_ariareq = 'true';
		}

		if ( (bool) self::get_property( 'lname_required', $field, false ) ) {
			$lname_ariareq = 'true';
		}

		if ( $mname && $lname ) {
			$cols = 6;
		}

		// START: Row
		$html .= '<div class="forminator-row" data-multiple="true">';

			// FIELD: Middle Name
		if ( $mname ) {

			$middle_name = array(
				'type'          => 'text',
				'name'          => $id . '-middle-name',
				'placeholder'   => $this->sanitize_value( self::get_property( 'mname_placeholder', $field ) ),
				'id'            => 'forminator-field-middle-' . $id,
				'class'         => 'forminator-input',
				'aria-required' => $mname_ariareq,
			);

			$middle_name = $this->replace_from_prefill( $field, $middle_name, 'mname' );

			$html .= sprintf( '<div class="forminator-col forminator-col-%s">', $cols );

				$html .= '<div class="forminator-field">';

				$html .= self::create_input(
					$middle_name,
					esc_html( self::get_property( 'mname_label', $field ) ),
					esc_html( self::get_property( 'mname_description', $field ) ),
					$mname_required,
					$design
				);

				$html .= '</div>';

			$html .= '</div>';
		}

			// FIELD: Last Name
		if ( $lname ) {

			$last_name = array(
				'type'          => 'text',
				'name'          => $id . '-last-name',
				'placeholder'   => $this->sanitize_value( self::get_property( 'lname_placeholder', $field ) ),
				'id'            => 'forminator-field-last-' . $id,
				'class'         => 'forminator-input',
				'aria-required' => $lname_ariareq,
			);

			$autofill_markup = $this->get_element_autofill_markup_attr( $id . '-last-name', $this->form_settings );

			$last_name = array_merge( $last_name, $autofill_markup );

			$last_name = $this->replace_from_prefill( $field, $last_name, 'lname' );

			$html .= sprintf( '<div class="forminator-col forminator-col-%s">', $cols );

			$html .= '<div class="forminator-field">';

				$html .= self::create_input(
					$last_name,
					esc_html( self::get_property( 'lname_label', $field ) ),
					esc_html( self::get_property( 'lname_description', $field ) ),
					$lname_required,
					$design
				);

				$html .= '</div>';

			$html .= '</div>';
		}

		// END: Row
		$html .= '</div>';

		return $html;
	}

	/**
	 * Field front-end markup
	 *
	 * @since 1.0
	 *
	 * @param $field
	 * @param $settings
	 *
	 * @return mixed
	 */
	public function markup( $field, $settings = array() ) {
		$this->field         = $field;
		$this->form_settings = $settings;

		$this->init_autofill( $settings );

		$multiple = self::get_property( 'multiple_name', $field, false, 'bool' );
		$design   = $this->get_form_style( $settings );

		// Check we use multi fields
		if ( ! $multiple ) {
			// Only one field
			$html = $this->get_simple( $field, $design );
		} else {
			// Multiple fields
			$html  = $this->get_multi_first_row( $field, $design );
			$html .= $this->get_multi_second_row( $field, $design );
		}

		return apply_filters( 'forminator_field_name_markup', $html, $field );
	}

	/**
	 * Return field inline validation rules
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_validation_rules() {
		$rules    = '';
		$field    = $this->field;
		$id       = self::get_property( 'element_id', $field );
		$multiple = self::get_property( 'multiple_name', $field, false, 'bool' );
		$required = $this->is_required( $field );

		if ( $multiple ) {
			$prefix = self::get_property( 'prefix', $field, false, 'bool' );
			$fname  = self::get_property( 'fname', $field, false, 'bool' );
			$mname  = self::get_property( 'mname', $field, false, 'bool' );
			$lname  = self::get_property( 'lname', $field, false, 'bool' );

			$prefix_required = self::get_property( 'prefix_required', $field, false, 'bool' );
			$fname_required  = self::get_property( 'fname_required', $field, false, 'bool' );
			$mname_required  = self::get_property( 'mname_required', $field, false, 'bool' );
			$lname_required  = self::get_property( 'lname_required', $field, false, 'bool' );

			if ( $prefix ) {
				if ( $prefix_required ) {
					$rules .= '"' . $this->get_id( $field ) . '-prefix": "trim",';
					$rules .= '"' . $this->get_id( $field ) . '-prefix": "required",';
				}
			}

			if ( $fname ) {
				if ( $fname_required ) {
					$rules .= '"' . $this->get_id( $field ) . '-first-name": "trim",';
					$rules .= '"' . $this->get_id( $field ) . '-first-name": "required",';
				}
			}

			if ( $mname ) {
				if ( $mname_required ) {
					$rules .= '"' . $this->get_id( $field ) . '-middle-name": "trim",';
					$rules .= '"' . $this->get_id( $field ) . '-middle-name": "required",';
				}
			}

			if ( $lname ) {
				if ( $lname_required ) {
					$rules .= '"' . $this->get_id( $field ) . '-last-name": "trim",';
					$rules .= '"' . $this->get_id( $field ) . '-last-name": "required",';
				}
			}
		} else {
			if ( $required ) {
				$rules .= '"' . $this->get_id( $field ) . '": "required",';
				$rules .= '"' . $this->get_id( $field ) . '": "trim",';
			}
		}

		return apply_filters( 'forminator_field_name_validation_rules', $rules, $id, $field );
	}

	/**
	 * Return field inline validation errors
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_validation_messages() {
		$field    = $this->field;
		$id       = self::get_property( 'element_id', $field );
		$multiple = self::get_property( 'multiple_name', $field, false, 'bool' );
		$messages = '';
		$required = $this->is_required( $field );

		if ( $multiple ) {
			$prefix = self::get_property( 'prefix', $field, false, 'bool' );
			$fname  = self::get_property( 'fname', $field, false, 'bool' );
			$mname  = self::get_property( 'mname', $field, false, 'bool' );
			$lname  = self::get_property( 'lname', $field, false, 'bool' );

			$prefix_required = self::get_property( 'prefix_required', $field, false, 'bool' );
			$fname_required  = self::get_property( 'fname_required', $field, false, 'bool' );
			$mname_required  = self::get_property( 'mname_required', $field, false, 'bool' );
			$lname_required  = self::get_property( 'lname_required', $field, false, 'bool' );

			if ( $prefix && $prefix_required ) {
				$required_message = $this->get_field_multiple_required_message(
					$id,
					$field,
					'prefix_required_message',
					'prefix',
					__( 'Prefix is required.', Forminator::DOMAIN )
				);
				$messages        .= '"' . $this->get_id( $field ) . '-prefix": "' . forminator_addcslashes( $required_message ) . '",' . "\n";
			}

			if ( $fname && $fname_required ) {
				$required_message = $this->get_field_multiple_required_message(
					$id,
					$field,
					'fname_required_message',
					'first',
					__( 'This field is required. Please input your first name.', Forminator::DOMAIN )
				);
				$messages        .= '"' . $this->get_id( $field ) . '-first-name": "' . forminator_addcslashes( $required_message ) . '",' . "\n";
			}

			if ( $mname && $mname_required ) {
				$required_message = $this->get_field_multiple_required_message(
					$id,
					$field,
					'mname_required_message',
					'middle',
					__( 'This field is required. Please input your middle name', Forminator::DOMAIN )
				);
				$messages        .= '"' . $this->get_id( $field ) . '-middle-name": "' . forminator_addcslashes( $required_message ) . '",' . "\n";
			}

			if ( $lname && $lname_required ) {
				$required_message = $this->get_field_multiple_required_message(
					$id,
					$field,
					'lname_required_message',
					'last',
					__( 'This field is required. Please input your last name', Forminator::DOMAIN )
				);
				$messages        .= '"' . $this->get_id( $field ) . '-last-name": "' . forminator_addcslashes( $required_message ) . '",' . "\n";
			}
		} else {
			if ( $required ) {
				// backward compat
				$required_message = self::get_property( 'required_message', $field, self::FIELD_PROPERTY_VALUE_NOT_EXIST, 'string' );
				if ( self::FIELD_PROPERTY_VALUE_NOT_EXIST === $required_message ) {
					$required_message = __( 'This field is required. Please input your name', Forminator::DOMAIN );
				}

				$required_message = apply_filters( 'forminator_name_field_required_validation_message', $required_message, $id, $field );
				$messages        .= '"' . $this->get_id( $field ) . '": "' . forminator_addcslashes( $required_message ) . '",' . "\n";

			}
		}

		return $messages;
	}

	/**
	 * Field back-end validation
	 *
	 * @since 1.0
	 *
	 * @param array        $field
	 * @param array|string $data
	 */
	public function validate( $field, $data ) {
		$id          = self::get_property( 'element_id', $field );
		$is_multiple = self::get_property( 'multiple_name', $field, false, 'bool' );
		$required    = $this->is_required( $field );

		if ( $is_multiple ) {
			$prefix = self::get_property( 'prefix', $field, false, 'bool' );
			$fname  = self::get_property( 'fname', $field, false, 'bool' );
			$mname  = self::get_property( 'mname', $field, false, 'bool' );
			$lname  = self::get_property( 'lname', $field, false, 'bool' );

			$prefix_required = self::get_property( 'prefix_required', $field, false, 'bool' );
			$fname_required  = self::get_property( 'fname_required', $field, false, 'bool' );
			$mname_required  = self::get_property( 'mname_required', $field, false, 'bool' );
			$lname_required  = self::get_property( 'lname_required', $field, false, 'bool' );

			$prefix_data = isset( $data['prefix'] ) ? $data['prefix'] : '';
			$fname_data  = isset( $data['first-name'] ) ? $data['first-name'] : '';
			$mname_data  = isset( $data['middle-name'] ) ? $data['middle-name'] : '';
			$lname_data  = isset( $data['last-name'] ) ? $data['last-name'] : '';

			if ( is_array( $data ) ) {
				if ( $prefix && $prefix_required && empty( $prefix_data ) ) {
					$this->validation_message[ $id . '-prefix' ] = $this->get_field_multiple_required_message(
						$id,
						$field,
						'prefix_required_message',
						'prefix',
						__( 'Prefix is required.', Forminator::DOMAIN )
					);
				}

				if ( $fname && $fname_required && empty( $fname_data ) ) {
					$this->validation_message[ $id . '-first-name' ] = $this->get_field_multiple_required_message(
						$id,
						$field,
						'fname_required_message',
						'first',
						__( 'This field is required. Please input your first name.', Forminator::DOMAIN )
					);
				}

				if ( $mname && $mname_required && empty( $mname_data ) ) {
					$this->validation_message[ $id . '-middle-name' ] = $this->get_field_multiple_required_message(
						$id,
						$field,
						'mname_required_message',
						'middle',
						__( 'This field is required. Please input your middle name', Forminator::DOMAIN )
					);
				}

				if ( $lname && $lname_required && empty( $lname_data ) ) {
					$this->validation_message[ $id . '-last-name' ] = $this->get_field_multiple_required_message(
						$id,
						$field,
						'lname_required_message',
						'last',
						__( 'This field is required. Please input your last name', Forminator::DOMAIN )
					);
				}
			}
		} else {
			if ( $required ) {
				if ( empty( $data ) ) {
					// backward compat
					$required_message = self::get_property( 'required_message', $field, self::FIELD_PROPERTY_VALUE_NOT_EXIST, 'string' );
					if ( self::FIELD_PROPERTY_VALUE_NOT_EXIST === $required_message ) {
						$required_message = __( 'This field is required. Please input your name', Forminator::DOMAIN );
					}

					$required_message                = apply_filters( 'forminator_name_field_required_validation_message', $required_message, $id, $field );
					$this->validation_message[ $id ] = $required_message;
				}
			}
		}
	}

	/**
	 * Sanitize data
	 *
	 * @since 1.0.2
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

		return apply_filters( 'forminator_field_name_sanitize', $data, $field, $original_data );
	}
}
