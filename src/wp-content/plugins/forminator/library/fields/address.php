<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Address
 *
 * @since 1.0
 */
class Forminator_Address extends Forminator_Field {

	/**
	 * @var string
	 */
	public $name = '';

	/**
	 * @var string
	 */
	public $slug = 'address';

	/**
	 * @var int
	 */
	public $position = 4;

	/**
	 * @var string
	 */
	public $type = 'address';

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
	public $icon = 'sui-icon-pin';

	/**
	 * Forminator_Address constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		parent::__construct();
		$this->name = __( 'Address', Forminator::DOMAIN );
	}

	/**
	 * Field defaults
	 *
	 * @since 1.0
	 * @return array
	 */
	public function defaults() {
		return array(
			'street_address'                   => 'true',
			'address_city'                     => 'true',
			'address_state'                    => 'true',
			'address_zip'                      => 'true',
			'address_country'                  => 'true',
			'address_line'                     => 'true',
			'street_address_label'             => __( 'Street Address', Forminator::DOMAIN ),
			'street_address_placeholder'       => __( 'E.g. 42 Wallaby Way', Forminator::DOMAIN ),
			'address_city_label'               => __( 'City', Forminator::DOMAIN ),
			'address_city_placeholder'         => __( 'E.g. Sydney', Forminator::DOMAIN ),
			'address_state_label'              => __( 'State/Province', Forminator::DOMAIN ),
			'address_state_placeholder'        => __( 'E.g. New South Wales', Forminator::DOMAIN ),
			'address_zip_label'                => __( 'ZIP / Postal Code', Forminator::DOMAIN ),
			'address_zip_placeholder'          => __( 'E.g. 2000', Forminator::DOMAIN ),
			'address_country_label'            => __( 'Country', Forminator::DOMAIN ),
			'address_line_label'               => __( 'Apartment, suite, etc', Forminator::DOMAIN ),
			'street_address_required_message'  => __( 'This field is required. Please enter the street address.', Forminator::DOMAIN ),
			'address_zip_required_message'     => __( 'This field is required. Please enter the zip code.', Forminator::DOMAIN ),
			'address_country_required_message' => __( 'This field is required. Please select the country.', Forminator::DOMAIN ),
			'address_city_required_message'    => __( 'This field is required. Please enter the city.', Forminator::DOMAIN ),
			'address_state_required_message'   => __( 'This field is required. Please enter the state.', Forminator::DOMAIN ),
			'address_line_required_message'    => __( 'This field is required. Please enter address line.', Forminator::DOMAIN ),
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
		$street_address_providers = apply_filters( 'forminator_field_' . $this->slug . '_street_address_autofill', array(), $this->slug . '_street_address' );
		$address_line_providers   = apply_filters( 'forminator_field_' . $this->slug . '_address_line_autofill', array(), $this->slug . '_address_line' );
		$city_providers           = apply_filters( 'forminator_field_' . $this->slug . '_city_autofill', array(), $this->slug . '_city' );
		$state_providers          = apply_filters( 'forminator_field_' . $this->slug . '_state_autofill', array(), $this->slug . '_state' );
		$zip_providers            = apply_filters( 'forminator_field_' . $this->slug . '_zip_autofill', array(), $this->slug . '_zip' );

		$autofill_settings = array(
			'address-street_address' => array(
				'values' => forminator_build_autofill_providers( $street_address_providers ),
			),
			'address-address_line'   => array(
				'values' => forminator_build_autofill_providers( $address_line_providers ),
			),
			'address-city'           => array(
				'values' => forminator_build_autofill_providers( $city_providers ),
			),
			'address-state'          => array(
				'values' => forminator_build_autofill_providers( $state_providers ),
			),
			'address-zip'            => array(
				'values' => forminator_build_autofill_providers( $zip_providers ),
			),
		);

		return $autofill_settings;
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

		$design = $this->get_form_style( $settings );

		// Address
		$html = $this->get_address( $field, 'street_address', $design );

		// Second Address
		$html .= $this->get_address( $field, 'address_line', $design );

		// City & State fields
		$html .= $this->get_city_state( $field, $design );

		// ZIP & Country fields
		$html .= $this->get_zip_country( $field, $design );

		return apply_filters( 'forminator_field_address_markup', $html, $field );
	}

	/**
	 * Return address input markup
	 *
	 * @since 1.0
	 *
	 * @param $field
	 * @param $slug
	 *
	 * @return string
	 */
	public function get_address( $field, $slug, $design ) {

		$html        = '';
		$cols        = 12;
		$id          = self::get_property( 'element_id', $field );
		$name        = $id;
		$required    = self::get_property( $slug . '_required', $field, false, 'bool' );
		$ariareq     = 'false';
		$enabled     = self::get_property( $slug, $field );
		$description = self::get_property( $slug . '_description', $field );

		if ( (bool) self::get_property( $slug . '_required', $field, false ) ) {
			$ariareq = 'true';
		}

		$address = array(
			'type'          => 'text',
			'name'          => $name . '-' . $slug,
			'placeholder'   => $this->sanitize_value( self::get_property( $slug . '_placeholder', $field ) ),
			'id'            => 'forminator-field-' . $slug . '-' . $name,
			'class'         => 'forminator-input',
			'data-required' => $required,
			'aria-required' => $ariareq,
		);

		$address = $this->replace_from_prefill( $field, $address, $slug );

		if ( $enabled ) {

			$html .= '<div class="forminator-row">';

				$html .= '<div class="forminator-col">';

					$html .= '<div class="forminator-field">';

						$html .= self::create_input(
							$address,
							self::get_property( $slug . '_label', $field ),
							$description,
							$required,
							$design
						);

					$html .= '</div>';

				$html .= '</div>';

			$html .= '</div>';
		}

		return $html;
	}

	/**
	 * Return City and State fields markup
	 *
	 * @since 1.0
	 *
	 * @param $field
	 *
	 * @return string
	 */
	public function get_city_state( $field, $design ) {
		$html           = '';
		$cols           = 12;
		$id             = self::get_property( 'element_id', $field );
		$city           = self::get_property( 'address_city', $field, false );
		$state          = self::get_property( 'address_state', $field, false );
		$city_desc      = self::get_property( 'address_city_description', $field );
		$state_desc     = self::get_property( 'address_state_description', $field );
		$city_required  = self::get_property( 'address_city_required', $field, false, 'bool' );
		$city_ariareq   = 'false';
		$state_required = self::get_property( 'address_state_required', $field, false, 'bool' );
		$state_ariareq  = 'false';
		$multirow       = 'false';

		if ( (bool) self::get_property( 'address_city_required', $field, false ) ) {
			$city_ariareq = 'true';
		}

		if ( (bool) self::get_property( 'address_state_required', $field, false ) ) {
			$state_ariareq = 'true';
		}

		// If both prefix & first name are enabled, change cols
		if ( $city && $state ) {
			$cols     = 6;
			$multirow = 'true';
		}

		if ( $city || $state ) {

			$html .= sprintf( '<div class="forminator-row" data-multiple="%s">', $multirow );

			if ( $city ) {

				$city_data = array(
					'type'          => 'text',
					'name'          => $id . '-city',
					'placeholder'   => $this->sanitize_value( self::get_property( 'address_city_placeholder', $field ) ),
					'id'            => 'forminator-field-city' . $id,
					'class'         => 'forminator-input',
					'data-required' => $city_required,
					'aria-required' => $city_ariareq,
				);

				$city_data = $this->replace_from_prefill( $field, $city_data, 'address_city' );

				$html .= sprintf( '<div class="forminator-col forminator-col-%s">', $cols );

					$html .= '<div class="forminator-field">';

						$html .= self::create_input(
							$city_data,
							self::get_property( 'address_city_label', $field ),
							$city_desc,
							$city_required,
							$design
						);

					$html .= '</div>';

				$html .= '</div>';

			}

			if ( $state ) {

				$state_data = array(
					'type'          => 'text',
					'name'          => $id . '-state',
					'placeholder'   => $this->sanitize_value( self::get_property( 'address_state_placeholder', $field ) ),
					'id'            => 'forminator-field-state-' . $id,
					'class'         => 'forminator-input',
					'data-required' => $state_required,
					'aria-required' => $state_ariareq,
				);

				$state_data = $this->replace_from_prefill( $field, $state_data, 'address_state' );

				$html .= sprintf( '<div class="forminator-col forminator-col-%s">', $cols );

					$html .= '<div class="forminator-field">';

						$html .= self::create_input(
							$state_data,
							self::get_property( 'address_state_label', $field ),
							$state_desc,
							$state_required,
							$design
						);

					$html .= '</div>';

				$html .= '</div>';

			}

			$html .= '</div>';

		}

		return $html;
	}

	/**
	 * Return Zip and County inputs
	 *
	 * @since 1.0
	 *
	 * @param $field
	 *
	 * @return string
	 */
	public function get_zip_country( $field, $design ) {
		$html            = '';
		$cols            = 12;
		$id              = self::get_property( 'element_id', $field );
		$address_zip     = self::get_property( 'address_zip', $field, false );
		$address_country = self::get_property( 'address_country', $field, false );
		$zip_desc        = self::get_property( 'address_zip_description', $field );
		$country_desc    = self::get_property( 'address_country_description', $field );

		$zip_required     = self::get_property( 'address_zip_required', $field, false, 'bool' );
		$country_required = self::get_property( 'address_country_required', $field, false, 'bool' );

		$zip_ariareq = 'false';

		if ( (bool) self::get_property( 'address_zip_required', $field, false ) ) {
			$zip_ariareq = 'true';
		}

		$multirow = 'false';

		// If both prefix & first name are enabled, change cols
		if ( $address_zip && $address_country ) {
			$cols     = 6;
			$multirow = 'true';
		}

		if ( $address_zip || $address_country ) {

			$html .= sprintf( '<div class="forminator-row" data-multiple="%s">', $multirow );

			if ( $address_zip ) {

				$zip_data = array(
					'type'        => 'text',
					'name'        => $id . '-zip',
					'placeholder' => $this->sanitize_value( self::get_property( 'address_zip_placeholder', $field ) ),
					'id'          => 'forminator-field-zip-' . $id,
					'class'       => 'forminator-input',
				);

				$zip_data = $this->replace_from_prefill( $field, $zip_data, 'address_zip' );

				$html .= sprintf( '<div class="forminator-col forminator-col-%s">', $cols );

					$html .= '<div class="forminator-field">';

						$html .= self::create_input(
							$zip_data,
							self::get_property( 'address_zip_label', $field ),
							$zip_desc,
							$zip_required,
							$design
						);

					$html .= '</div>';

				$html .= '</div>';

			}

			if ( $address_country ) {

				$country_data = array(
					'name'  => $id . '-country',
					'id'    => $id . '-country',
					'class' => 'forminator-select2',
				);

				$countries = array(
					array(
						'value' => '',
						'label' => __( 'Select country', Forminator::DOMAIN ),
					),
				);

				$options   = forminator_to_field_array( forminator_get_countries_list() );
				$countries = array_merge( $countries, $options );
				$prefill   = false;

				if ( $this->has_prefill( $field, 'address_country' ) ) {
					// We have pre-fill parameter, use its value or $value
					$prefill = $this->get_prefill( $field, false, 'address_country' );
				}

				$new_countries = array();
				foreach ( $countries as $option ) {
					$selected = false;

					if ( strtolower( $option['value'] ) === strtolower( $prefill ) ) {
						$selected = true;
					}
					$new_countries[] = array(
						'value'    => $option['value'],
						'label'    => $option['label'],
						'selected' => $selected,
					);
				}

				/**
				 * Filter countries for <options> on <select> field
				 *
				 * @since 1.5.2
				 * @param array $countries
				 */
				$countries = apply_filters( 'forminator_countries_field', $new_countries );
					$options   = forminator_to_field_array( forminator_get_countries_list() );
					$countries = array_merge( $countries, $options );

                    $prefill   = false;

                    if( $this->has_prefill( $field, 'address_country' ) ) {
                        // We have pre-fill parameter, use its value or $value
                        $prefill = $this->get_prefill( $field, false, 'address_country' );
                    }

					$new_countries = array();
                    foreach ( $countries as $option ) {
                        $selected = false;

                        if( strtolower( $option['value'] ) === strtolower( $prefill ) ) {
                            $selected = true;
                        }
                        $new_countries[] = array(
                            'value' => $option['value'],
                            'label' => $option['label'],
                            'selected' => $selected
                        );
                    }
					/**
					 * Filter countries for <options> on <select> field
					 *
					 * @since 1.5.2
					 * @param array $countries
					 */
					$countries = apply_filters( 'forminator_countries_field', $new_countries );

					$html .= sprintf( '<div class="forminator-col forminator-col-%s">', $cols );

						$html .= '<div class="forminator-field">';

							$html .= self::create_country_select(
								$country_data,
								self::get_property( 'address_country_label', $field ),
								$countries,
								self::get_property( 'address_country_placeholder', $field ),
								$country_desc,
								$country_required
							);

						$html .= '</div>';

					$html .= '</div>';

			}

			$html .= '</div>';

		}

		return $html;
	}

	/**
	 * Return new select field
	 *
	 * @since 1.7.3
	 *
	 * @param array  $attr
	 * @param string $label
	 * @param array  $options
	 * @param string $value
	 *
	 * @param string $description
	 * @param bool   $required
	 *
	 * @return mixed
	 */
	public static function create_country_select( $attr = array(), $label = '', $options = array(), $value = '', $description = '', $required = false ) {

		$html = '';

		$markup = self::implode_attr( $attr );

		if ( isset( $attr['id'] ) ) {
			$get_id = $attr['id'];
		} else {
			$get_id = uniqid( 'forminator-select-' );
		}

		if ( self::get_post_data( $attr['name'], false ) ) {
			$value = self::get_post_data( $attr['name'] );
		}

		if ( $label ) {
			$html .= sprintf(
				'<label for="%s" class="forminator-label">%s %s</label>',
				$get_id,
				esc_html( $label ),
				$required ? forminator_get_required_icon() : ''
			);
		}

		$markup .= ' data-default-value="' . esc_attr( $value ) . '"';

		$html .= sprintf( '<select %s>', $markup );

		foreach ( $options as $option ) {
			$selected = '';

			if ( ( $option['label'] == $value ) || ( isset( $option['selected'] ) && $option['selected'] ) ) { // phpcs:ignore -- loose comparison ok : possible compare '1' and 1.
				$selected = 'selected="selected"';
			}

			if( 'Select country' === $option['label'] ) {
				$html .= sprintf( '<option value="" data-country-code="%s" %s>%s</option>', $option['value'], $selected, $option['label'] );
			} else {
				$html .= sprintf( '<option value="%s" data-country-code="%s" %s>%s</option>', $option['label'], $option['value'], $selected, $option['label'] );
			}
		}

		$html .= '</select>';

		if ( ! empty( $description ) ) {
			$html .= self::get_description( $description, $get_id );
		}

		return apply_filters( 'forminator_field_create_select', $html, $attr, $label, $options, $value, $description );
	}

	/**
	 * Return field inline validation rules
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_validation_rules() {

		$field = $this->field;
		$rules = '';

		$id      = self::get_property( 'element_id', $field );
		$street  = self::get_property( 'street_address', $field, false );
		$line    = self::get_property( 'address_line', $field, false );
		$city    = self::get_property( 'address_city', $field, false );
		$state   = self::get_property( 'address_state', $field, false );
		$zip     = self::get_property( 'address_zip', $field, false );
		$country = self::get_property( 'address_country', $field, false );

		$street_required  = self::get_property( 'street_address_required', $field, false, 'bool' );
		$line_required    = self::get_property( 'address_line_required', $field, false, 'bool' );
		$city_required    = self::get_property( 'address_city_required', $field, false, 'bool' );
		$state_required   = self::get_property( 'address_state_required', $field, false, 'bool' );
		$zip_required     = self::get_property( 'address_zip_required', $field, false, 'bool' );
		$country_required = self::get_property( 'address_country_required', $field, false, 'bool' );

		if ( $street ) {
			if ( $street_required ) {
				$rules .= '"' . $this->get_id( $field ) . '-street_address": "trim",';
				$rules .= '"' . $this->get_id( $field ) . '-street_address": "required",';
			}
		}
		if ( $line ) {
			if ( $line_required ) {
				$rules .= '"' . $this->get_id( $field ) . '-address_line": "trim",';
				$rules .= '"' . $this->get_id( $field ) . '-address_line": "required",';
			}
		}
		if ( $city ) {
			if ( $city_required ) {
				$rules .= '"' . $this->get_id( $field ) . '-city": "trim",';
				$rules .= '"' . $this->get_id( $field ) . '-city": "required",';
			}
		}
		if ( $state ) {
			if ( $state_required ) {
				$rules .= '"' . $this->get_id( $field ) . '-state": "trim",';
				$rules .= '"' . $this->get_id( $field ) . '-state": "required",';
			}
		}
		if ( $zip ) {
			if ( $zip_required ) {
				$rules .= '"' . $this->get_id( $field ) . '-zip": "trim",';
				$rules .= '"' . $this->get_id( $field ) . '-zip": "required",';
			}
		}
		if ( $country ) {
			if ( $country_required ) {
				$rules .= '"' . $this->get_id( $field ) . '-country": "trim",';
				$rules .= '"' . $this->get_id( $field ) . '-country": "required",';
			}
		}

		return apply_filters( 'forminator_field_address_validation_rules', $rules, $id, $field );
	}

	/**
	 * Return field inline validation errors
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_validation_messages() {
		$field    = $this->field;
		$id       = $this->get_id( $field );
		$messages = '';

		$street  = self::get_property( 'street_address', $field, false );
		$line    = self::get_property( 'address_line', $field, false );
		$city    = self::get_property( 'address_city', $field, false );
		$state   = self::get_property( 'address_state', $field, false );
		$zip     = self::get_property( 'address_zip', $field, false );
		$country = self::get_property( 'address_country', $field, false );

		$street_required  = self::get_property( 'street_address_required', $field, false, 'bool' );
		$line_required    = self::get_property( 'address_line_required', $field, false, 'bool' );
		$city_required    = self::get_property( 'address_city_required', $field, false, 'bool' );
		$state_required   = self::get_property( 'address_state_required', $field, false, 'bool' );
		$zip_required     = self::get_property( 'address_zip_required', $field, false, 'bool' );
		$country_required = self::get_property( 'address_country_required', $field, false, 'bool' );

		if ( $street && $street_required ) {
			$required_message = $this->get_field_multiple_required_message(
				$id,
				$field,
				'street_address_required_message',
				'street_address',
				__( 'This field is required. Please enter the street address.', Forminator::DOMAIN )
			);
			$messages        .= '"' . $this->get_id( $field ) . '-street_address": "' . forminator_addcslashes( $required_message ) . '",' . "\n";
		}
		if ( $line && $line_required ) {
			$required_message = $this->get_field_multiple_required_message(
				$id,
				$field,
				'address_line_required_message',
				'address_line',
				__( 'This field is required. Please enter address line.', Forminator::DOMAIN )
			);
			$messages        .= '"' . $this->get_id( $field ) . '-address_line": "' . forminator_addcslashes( $required_message ) . '",' . "\n";
		}

		if ( $city && $city_required ) {
			$required_message = $this->get_field_multiple_required_message(
				$id,
				$field,
				'address_city_required_message',
				'address_city',
				__( 'This field is required. Please enter the city.', Forminator::DOMAIN )
			);
			$messages        .= '"' . $this->get_id( $field ) . '-city": "' . forminator_addcslashes( $required_message ) . '",' . "\n";
		}

		if ( $state && $state_required ) {
			$required_message = $this->get_field_multiple_required_message(
				$id,
				$field,
				'address_state_required_message',
				'address_state',
				__( 'This field is required. Please enter the state.', Forminator::DOMAIN )
			);
			$messages        .= '"' . $this->get_id( $field ) . '-state": "' . forminator_addcslashes( $required_message ) . '",' . "\n";
		}

		if ( $zip && $zip_required ) {
			$required_message = $this->get_field_multiple_required_message(
				$id,
				$field,
				'address_zip_required_message',
				'address_zip',
				__( 'This field is required. Please enter the zip code.', Forminator::DOMAIN )
			);
			$messages        .= '"' . $this->get_id( $field ) . '-zip": "' . forminator_addcslashes( $required_message ) . '",' . "\n";
		}

		if ( $country && $country_required ) {
			$required_message = $this->get_field_multiple_required_message(
				$id,
				$field,
				'address_country_required_message',
				'address_country',
				__( 'This field is required. Please select the country.', Forminator::DOMAIN )
			);
			$messages        .= '"' . $this->get_id( $field ) . '-country": "' . forminator_addcslashes( $required_message ) . '",' . "\n";
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
		$id = self::get_property( 'element_id', $field );

		$street  = self::get_property( 'street_address', $field, false );
		$line    = self::get_property( 'address_line', $field, false );
		$city    = self::get_property( 'address_city', $field, false );
		$state   = self::get_property( 'address_state', $field, false );
		$zip     = self::get_property( 'address_zip', $field, false );
		$country = self::get_property( 'address_country', $field, false );

		$street_required  = self::get_property( 'street_address_required', $field, false, 'bool' );
		$line_required    = self::get_property( 'address_line_required', $field, false, 'bool' );
		$city_required    = self::get_property( 'address_city_required', $field, false, 'bool' );
		$state_required   = self::get_property( 'address_state_required', $field, false, 'bool' );
		$zip_required     = self::get_property( 'address_zip_required', $field, false, 'bool' );
		$country_required = self::get_property( 'address_country_required', $field, false, 'bool' );

		$street_data  = isset( $data['street_address'] ) ? $data['street_address'] : '';
		$line_data    = isset( $data['address_line'] ) ? $data['address_line'] : '';
		$zip_data     = isset( $data['zip'] ) ? $data['zip'] : '';
		$country_data = isset( $data['country'] ) ? $data['country'] : '';
		$city_data    = isset( $data['city'] ) ? $data['city'] : '';
		$state_data   = isset( $data['state'] ) ? $data['state'] : '';

		if ( $street && $street_required && empty( $street_data ) ) {
			$this->validation_message[ $id . '-street_address' ] = $this->get_field_multiple_required_message(
				$id,
				$field,
				'street_address_required_message',
				'street_address',
				__( 'This field is required. Please enter the street address.', Forminator::DOMAIN )
			);
		}
		if ( $line && $line_required && empty( $line_data ) ) {
			$this->validation_message[ $id . '-address_line' ] = $this->get_field_multiple_required_message(
				$id,
				$field,
				'address_line_required_message',
				'address_line',
				__( 'This field is required. Please enter address line.', Forminator::DOMAIN )
			);
		}

		if ( $city && $city_required && empty( $city_data ) ) {
			$this->validation_message[ $id . '-city' ] = $this->get_field_multiple_required_message(
				$id,
				$field,
				'address_city_required_message',
				'address_city',
				__( 'This field is required. Please enter the city.', Forminator::DOMAIN )
			);
		}

		if ( $state && $state_required && empty( $state_data ) ) {
			$this->validation_message[ $id . '-state' ] = $this->get_field_multiple_required_message(
				$id,
				$field,
				'address_state_required_message',
				'address_state',
				__( 'This field is required. Please enter the state.', Forminator::DOMAIN )
			);
		}

		if ( $zip && $zip_required && empty( $zip_data ) ) {
			$this->validation_message[ $id . '-zip' ] = $this->get_field_multiple_required_message(
				$id,
				$field,
				'address_zip_required_message',
				'address_zip',
				__( 'This field is required. Please enter the zip code.', Forminator::DOMAIN )
			);
		}

		if ( $country && $country_required && empty( $country_data ) ) {
			$this->validation_message[ $id . '-country' ] = $this->get_field_multiple_required_message(
				$id,
				$field,
				'address_country_required_message',
				'address_country',
				__( 'This field is required. Please select the country.', Forminator::DOMAIN )
			);
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

		return apply_filters( 'forminator_field_address_sanitize', $data, $field, $original_data );
	}
}
