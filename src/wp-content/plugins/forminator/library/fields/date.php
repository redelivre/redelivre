<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Date
 *
 * @since 1.0
 */
class Forminator_Date extends Forminator_Field {

	/**
	 * @var string
	 */
	public $name = '';

	/**
	 * @var string
	 */
	public $slug = 'date';

	/**
	 * @var int
	 */
	public $position = 12;

	/**
	 * @var string
	 */
	public $type = 'date';

	/**
	 * @var string
	 */
	public $options = array();

	/**
	 * @var string
	 */
	public $category = 'standard';

	/**
	 * @var string
	 */
	public $icon = 'sui-icon-calendar';

	/**
	 * Forminator_Date constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		parent::__construct();

		$this->name = __( 'Datepicker', Forminator::DOMAIN );
	}

	/**
	 * Field defaults
	 *
	 * @since 1.0
	 * @return array
	 */
	public function defaults() {
		return array(
			'field_type'        => 'picker',
			'date_format'       => 'mm/dd/yy',
			'default_date'      => 'today',
			'field_label'       => __( 'Date', Forminator::DOMAIN ),
			'placeholder'       => __( 'Choose Date', Forminator::DOMAIN ),
			'icon'              => 'true',
			'day_label'         => __( 'Day', Forminator::DOMAIN ),
			'day_placeholder'   => __( 'E.g. 01', Forminator::DOMAIN ),
			'month_label'       => __( 'Month', Forminator::DOMAIN ),
			'month_placeholder' => __( 'E.g. 01', Forminator::DOMAIN ),
			'year_label'        => __( 'Year', Forminator::DOMAIN ),
			'year_placeholder'  => __( 'E.g. 2000', Forminator::DOMAIN ),
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
		$providers = apply_filters( 'forminator_field_' . $this->slug . '_autofill', array(), $this->slug );

		// TODO: support for multiple field date
		$autofill_settings = array(
			'date' => array(
				'values' => forminator_build_autofill_providers( $providers ),
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

		$this->field = $field;

		$html        = '';
		$design      = $this->get_form_style( $settings );
		$id          = self::get_property( 'element_id', $field );
		$name        = $id;
		$required    = self::get_property( 'required', $field, false );
		$placeholder = $this->sanitize_value( self::get_property( 'placeholder', $field ) );
		$label       = $this->sanitize_value( self::get_property( 'field_label', $field ) );
		$description = $this->sanitize_value( self::get_property( 'description', $field ) );
		$type        = trim( self::get_property( 'field_type', $field ) );
		$has_icon    = self::get_property( 'icon', $field, false, 'bool' );
		$has_icon    = filter_var( $has_icon, FILTER_VALIDATE_BOOLEAN );
		$date_format = esc_html( self::get_property( 'date_format', $field, 'm/d/Y' ) );
		if ( false !== strpos( $date_format, '-' ) ) {
			$sep = '-';
		} elseif ( false !== strpos( $date_format, '.' ) ) {
			$sep = '.';
		} else {
			$sep = '/';
		}
		$formats = explode( $sep, $date_format );

		$min_year = esc_html( self::get_property( 'min_year', $field, 1920 ) );
		$max_year = esc_html( self::get_property( 'max_year', $field, 2120 ) );

		$prefill = false;
		$is_prefil_valid = false;

		// Check if Pre-fill parameter used
		if( $this->has_prefill( $field ) ) {
			// We have pre-fill parameter, use its value or $value
			$prefill = $this->get_prefill( $field, $prefill );
		}

		$parsed_date = self::parse_date( $prefill, $date_format );
		if ( $parsed_date && $this->check_date( $parsed_date['month'], $parsed_date['day'], $parsed_date['year'] ) ) {
			$is_prefil_valid = true;
		}

		if ( 'picker' === $type ) {
			$html .= '<div class="forminator-field">';
		}

		if ( 'picker' === $type ) {

			$restrict      = array();
			$restrict_type = self::get_property( 'howto-restrict', $field );

			if ( 'week' === $restrict_type ) {

				$days = forminator_week_days();
				$i    = 0;

				foreach ( $days as $k => $day ) {

					if ( ! self::get_property( $k, $field ) ) {
						$restrict[] = $i;
					}

					$i ++;
				}
			} elseif ( 'custom' === $restrict_type ) {
				$dates = self::get_property( 'date_multiple', $field );
				$default_value = '';
				$default_date  = esc_html( self::get_property( 'default_date', $field, false ) );

				if ( ! empty( $dates ) ) {
					foreach ( $dates as $k => $date ) {
						$restrict[] = $date['value'];
					}
				}

				if ( 'custom' === $default_date ) {
					$default_date_value = esc_html( self::get_property( 'date', $field, '' ) );
					$default_value      = $default_date_value;
				}
			}

			$default_value = '';
			$default_date  = self::get_property( 'default_date', $field, false );

			if ( 'today' === $default_date ) {
				$datepicker_format = $this->normalize_date_format( $date_format );
				$default_value     = date( $datepicker_format );// phpcs:ignore
			}

			if ( 'custom' === $default_date ) {
				$default_date_value = self::get_property( 'date', $field, '' );
				$default_value      = $default_date_value;
			}

			if ( $is_prefil_valid ) {
				$default_value = $prefill;
			}

			$icon_markup = array(
				'<div class="forminator-input-with-icon">',
				'</div>',
				'calendar',
			);

			$html .= self::create_input(
				array(
					'type'               => 'text',
					'size'               => 1,
					'name'               => $name,
					'value'              => $default_value,
					'placeholder'        => $placeholder,
					'id'                 => 'forminator-field-' . $id . '-picker-' . uniqid(),
					'class'              => 'forminator-input forminator-datepicker',
					'data-required'      => $required,
					'data-format'        => $date_format,
					'data-restrict-type' => $restrict_type,
					'data-restrict'      => implode( ',', $restrict ),
					'data-start-year'    => $min_year,
					'data-end-year'      => $max_year,
				),
				$label,
				$description,
				$required,
				$design,
				$has_icon ? $icon_markup : ''
			);

		} elseif ( 'select' === $type ) {

			if ( ! empty( $label ) ) {

				if ( $required ) {

					$html .= sprintf(
						'<label for="%s" class="forminator-label">%s %s</label>',
						'forminator-field-' . $id,
						$label,
						forminator_get_required_icon()
					);

				} else {

					$html .= sprintf(
						'<label for="%s" class="forminator-label">%s</label>',
						'forminator-field-' . $id,
						$label
					);

				}

				// Mark day, month and year required markup as false
				$required = false;

			}

			$default_date       = esc_html( self::get_property( 'default_date', $field, false ) );
			$default_date_value = esc_html( self::get_property( 'date', $field, '' ) );

			if ( $is_prefil_valid ) {
				$default_value = $prefill;
			}

			if ( $is_prefil_valid ) {
				$day   = $parsed_date['day'];
				$month = $parsed_date['month'];
				$year  = $parsed_date['year'];
			} else if ( 'today' === $default_date ) {
				$day   = date( 'j' );
				$month = date( 'n' );
				$year  = date( 'Y' );
			} else if ( 'custom' === $default_date && ! empty( $default_date_value ) ) {
				$day   = date( "j", strtotime( $default_date_value ) );
				$month = date( "n", strtotime( $default_date_value ) );
				$year  = date( "Y", strtotime( $default_date_value ) );
			} else {
				$day   = '';
				$month = '';
				$year  = '';
			}

			// START: Row
			$html .= '<div class="forminator-date-select">';

				$html .= '<div class="forminator-row" data-multiple="true">';

					foreach ( $formats as $format ) {

						switch ( $format ) {

							case 'dd':
								$day_id = $id . '-day';
								$html  .= '<div id="' . $day_id . '" class="forminator-col">';

								$html .= '<div class="forminator-field">';

									$day_data = array(
										'name'        => $id . '-day',
										'id'          => 'forminator-field-' . $id . '-day',
										'class'       => 'forminator-select',
										'data-format' => $date_format,
										'data-parent' => $id,
									);

									if ( $required ) {

										$label = self::get_property( 'day_label', $field );

										if ( ! empty( $label ) ) {
											$html .= sprintf(
												'<label for="%s" class="forminator-label">%s %s</label>',
												$day_data['id'],
												$this->sanitize_value( $label ),
												'<span class="forminator-required">*</span>'
											);
										}

										$html .= self::create_select(
											$day_data,
											false,
											$this->get_day(),
											$day
										);

									} else {

										$html .= self::create_select(
											$day_data,
											$this->sanitize_value( self::get_property( 'day_label', $field ) ),
											$this->get_day(),
											$day
										);
									}

									$html .= '</div>';

									$html .= '</div>';

								break;

							case 'mm':
								$month_id = $id . '-month';
								$html    .= '<div id="' . $month_id . '" class="forminator-col">';

								$html .= '<div class="forminator-field">';

								$month_data = array(
									'name'        => $id . '-month',
									'id'          => 'forminator-field-' . $id . '-month',
									'class'       => 'forminator-select',
									'data-format' => $date_format,
									'data-parent' => $id,
								);

								if ( $required ) {

									$label = self::get_property( 'month_label', $field );

									if ( ! empty( $label ) ) {
										$html .= sprintf(
											'<label for="%s" class="forminator-label">%s %s</label>',
											$month_data['id'],
											$this->sanitize_value( $label ),
											'<span class="forminator-required">*</span>'
										);
									}

									$html .= self::create_select(
										$month_data,
										false,
										$this->get_months(),
										$month
									);

								} else {

									$html .= self::create_select(
										$month_data,
										$this->sanitize_value( self::get_property( 'month_label', $field ) ),
										$this->get_months(),
										$month
									);
								}

								$html .= '</div>';

								$html .= '</div>';

								break;

							case 'yy':
								$year_id = $id . '-year';
								$html   .= '<div id="' . $year_id . '" class="forminator-col">';

								$html .= '<div class="forminator-field">';

								$year_data = array(
									'name'        => $id . '-year',
									'id'          => 'forminator-field-' . $id . '-year',
									'class'       => 'forminator-select',
									'data-format' => $date_format,
									'data-parent' => $id,
								);

								if ( $required ) {

									$label = self::get_property( 'year_label', $field );

									if ( ! empty( $label ) ) {

										$html .= sprintf(
											'<label for="%s" class="forminator-label">%s %s</label>',
											$year_data['id'],
											$this->sanitize_value( $label ),
											'<span class="forminator-required">*</span>'
										);
									}

									$html .= self::create_select(
										$year_data,
										false,
										$this->get_years( $min_year, $max_year ),
										$year
									);

								} else {

									$html .= self::create_select(
										$year_data,
										$this->sanitize_value( self::get_property( 'year_label', $field ) ),
										$this->get_years( $min_year, $max_year ),
										$year
									);
								}

								$html .= '</div>';

								$html .= '</div>';

								break;
							default:
								break;
						}
					}

				$html .= '</div>';

			// END: Row
			$html .= '</div>';

			$html .= self::get_description( $description, 'forminator-field-' . $id );

		} elseif ( 'input' === $type ) {
				$day_value = $month_value = $year_value = '';

				if ( $is_prefil_valid ) {
					$day_value   = $parsed_date['day'];
					$month_value = $parsed_date['month'];
					$year_value  = $parsed_date['year'];
				}
				if ( ! empty( $label ) ) {

					if ( $required ) {

						$html .= sprintf(
							'<label for="%s" class="forminator-label">%s %s</label>',
							'forminator-field-' . $id,
							esc_html( $label ),
							forminator_get_required_icon()
						);

					} else {

						$html .= sprintf(
							'<label for="%s" class="forminator-label">%s</label>',
							'forminator-field-' . $id,
							esc_html( $label )
						);

					}
				}

				// START: Row
				$html .= '<div class="forminator-date-input">';

				$html .= '<div class="forminator-row" data-multiple="true">';

				foreach ( $formats as $format ) {

					switch ( $format ) {

						case 'dd':
							$day   = $id . '-day';
							$html .= '<div id="' . $day . '" class="forminator-col">';

							$html .= '<div class="forminator-field">';

							$day_data = array(
								'type'        => 'number',
								'min'         => 1,
								'max'         => 31,
								'name'        => $id . '-day',
								'value'       => $day_value,
								'placeholder' => $this->sanitize_value( self::get_property( 'day_placeholder', $field ) ),
								'id'          => 'forminator-field-' . $id . '-day',
								'class'       => 'forminator-input',
								'data-field'  => 'day',
								'data-format' => $date_format,
								'data-parent' => $id,
							);

							if ( $required ) {

								$label = self::get_property( 'day_label', $field );

								if ( ! empty( $label ) ) {

									$html .= sprintf(
										'<label for="%s" class="forminator-label">%s %s</label>',
										$day_data['id'],
										$this->sanitize_value( $label ),
										'<span class="forminator-required">*</span>'
									);
								}

								$html .= self::create_input(
									$day_data,
									false,
									'',
									$required,
									$design
								);

							} else {

								$html .= self::create_input(
									$day_data,
									$this->sanitize_value( self::get_property( 'day_label', $field ) ),
									'',
									$required,
									$design
								);
							}

							$html .= '</div>';

							$html .= '</div>';

							break;

						case 'mm':
							$month = $id . '-month';
							$html .= '<div id="' . $month . '" class="forminator-col">';

							$html .= '<div class="forminator-field">';

								$month_data = array(
									'type'        => 'number',
									'min'         => 1,
									'max'         => 12,
									'name'        => $id . '-month',
									'value'       => $month_value,
									'placeholder' => $this->sanitize_value( self::get_property( 'month_placeholder', $field ) ),
									'id'          => 'forminator-field-' . $id . '-month',
									'class'       => 'forminator-input',
									'data-field'  => 'month',
									'data-format' => $date_format,
									'data-parent' => $id,
								);

								if ( $required ) {

									$label = self::get_property( 'month_label', $field );

									if ( ! empty( $label ) ) {

										$html .= sprintf(
											'<label for="%s" class="forminator-label">%s %s</label>',
											$month_data['id'],
											$this->sanitize_value( $label ),
											'<span class="forminator-required">*</span>'
										);
									}

									$html .= self::create_input(
										$month_data,
										false,
										'',
										$required,
										$design
									);

								} else {

									$html .= self::create_input(
										$month_data,
										$this->sanitize_value( self::get_property( 'month_label', $field ) ),
										'',
										$required,
										$design
									);
								}

								$html .= '</div>';

								$html .= '</div>';

							break;

						case 'yy':
							$year  = $id . '-year';
							$html .= '<div id="' . $year . '" class="forminator-col">';

							$html .= '<div class="forminator-field">';

								$year_data = array(
									'type'        => 'number',
									'min'         => 1,
									'name'        => $id . '-year',
									'placeholder' => $this->sanitize_value( self::get_property( 'year_placeholder', $field ) ),
									'id'          => 'forminator-field-' . $id . '-year',
									'class'       => 'forminator-input',
									'data-field'  => 'year',
									'value'       => $year_value,
									'data-format' => $date_format,
									'data-parent' => $id,
								);

								if ( $required ) {

									$label = self::get_property( 'year_label', $field );

									if ( ! empty( $label ) ) {
										$html .= sprintf(
											'<label for="%s" class="forminator-label">%s %s</label>',
											$year_data['id'],
											$this->sanitize_value( $label ),
											'<span class="forminator-required">*</span>'
										);
									}

									$html .= self::create_input(
										$year_data,
										false,
										'',
										$required,
										$design
									);

								} else {

									$html .= self::create_input(
										$year_data,
										$this->sanitize_value( self::get_property( 'year_label', $field ) ),
										'',
										$required,
										$design
									);
								}

								$html .= '</div>';

								$html .= '</div>';
							break;

						default:
							break;
					}
				}

				$html .= '</div>';

			// END: Row
			$html .= '</div>';

			$html .= self::get_description( $description, 'forminator-field-' . $id );
		}

		if ( 'picker' === $type ) {
			$html .= '</div>';
		}

		return apply_filters( 'forminator_field_date_markup', $html, $field, $this );
	}

	/**
	 * Return modified date format
	 *
	 * @since 1.7.0.1
	 *
	 * @param string $date_format
	 *
	 * @return string
	 */
	public function normalize_date_format( $date_format ) {
		$date_format = str_replace( 'dd', 'd', $date_format );
		$date_format = str_replace( 'mm', 'm', $date_format );
		$date_format = str_replace( 'yy', 'Y', $date_format );

		return $date_format;
	}

	/**
	 * Return all years between two dates
	 *
	 * @since 1.0
	 *
	 * @param string $min_year
	 * @param string $max_year
	 *
	 * @return array
	 */
	public function get_years( $min_year = '', $max_year = '' ) {
		$array = array();
		$year  = intval( date( 'Y' ) );// phpcs:ignore
		$end   = empty( $min_year ) ? $year - 100 : intval( $min_year ) - 1;
		$start = empty( $max_year ) ? $year + 1 : intval( $max_year );
		for ( $i = $start; $i > $end; $i -- ) {
			$array[] = array(
				'label' => $i,
				'value' => $i,
			);
		}

		array_unshift( $array, [
			'label' => esc_html__( 'Select Year', Forminator::DOMAIN ),
			'value' => '',
		]);

		return apply_filters( 'forminator_field_date_get_years', $array, $min_year, $max_year, $year, $start, $end, $this );
	}

	/**
	 * Return monts
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_months() {
		$array = array();
		for ( $i = 1; $i < 13; $i ++ ) {
			$array[] = array(
				'label' => $i,
				'value' => $i,
			);
		}

		array_unshift( $array, [
			'label' => esc_html__( 'Select month', Forminator::DOMAIN ),
			'value' => '',
		]);

		return apply_filters( 'forminator_field_date_get_months', $array, $this );
	}

	/**
	 * Return days
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_day() {
		$array = array();
		for ( $i = 1; $i < 32; $i ++ ) {
			$array[] = array(
				'label' => $i,
				'value' => $i,
			);
		}
		array_unshift( $array, [
			'label' => esc_html__( 'Select day', Forminator::DOMAIN ),
			'value' => '',
		]);

		return apply_filters( 'forminator_field_date_get_day', $array, $this );
	}

	/**
	 * Parse date
	 *
	 * @since 1.0
	 *
	 * @param string|array $date - the date to be parsed
	 * @param string $format - the data format
	 *
	 * @return array
	 */
	public static function parse_date( $date, $format = 'yy-mm-dd' ) {
		$date_info = array(
			'year'  => 0,
			'month' => 0,
			'day'   => 0,
		);

		$position = substr( $format, 0, 8 );

		if ( is_array( $date ) ) {

			switch ( $position ) {
				case 'mm/dd/yy':
				case 'mm-dd-yy':
				case 'mm.dd.yy':
					$date_info['month'] = isset( $date['month'] ) ? $date['month'] : 0;
					$date_info['day']   = isset( $date['day'] ) ? $date['day'] : 0;
					$date_info['year']  = isset( $date['year'] ) ? $date['year'] : 0;
					break;
				case 'dd/mm/yy':
				case 'dd-mm-yy':
				case 'dd.mm.yy':
					$date_info['day']   = isset( $date['day'] ) ? $date['day'] : 0;
					$date_info['month'] = isset( $date['month'] ) ? $date['month'] : 0;
					$date_info['year']  = isset( $date['year'] ) ? $date['year'] : 0;
					break;
				case 'yy-mm-dd':
				case 'yy/mm/dd':
				case 'yy.mm.dd':
					$date_info['year']  = isset( $date['year'] ) ? $date['year'] : 0;
					$date_info['month'] = isset( $date['month'] ) ? $date['month'] : 0;
					$date_info['day']   = isset( $date['day'] ) ? $date['day'] : 0;
					break;

				default:
					break;
			}

			return apply_filters( 'forminator_field_date_parse_dates', $date_info, $date, $format );
		}

		$date = preg_replace( '|[/\.]|', '-', $date );
		if ( 'mm/dd/yy' === $position || 'mm-dd-yy' === $position || 'mm.dd.yy' === $position ) {
			if ( preg_match( '/^(\d{1,2})-(\d{1,2})-(\d{1,4})$/', $date, $matches ) ) {
				$date_info['month'] = $matches[1];
				$date_info['day']   = $matches[2];
				$date_info['year']  = $matches[3];
			}
		} elseif ( 'dd/mm/yy' === $position || 'dd-mm-yy' === $position || 'dd.mm.yy' === $position ) {
			if ( preg_match( '/^(\d{1,2})-(\d{1,2})-(\d{1,4})$/', $date, $matches ) ) {
				$date_info['day']   = $matches[1];
				$date_info['month'] = $matches[2];
				$date_info['year']  = $matches[3];
			}
		} elseif ( 'yy-mm-dd' === $position || 'yy/mm/dd' === $position || 'yy.mm.dd' === $position ) {
			if ( preg_match( '/^(\d{1,4})-(\d{1,2})-(\d{1,2})$/', $date, $matches ) ) {
				$date_info['year']  = $matches[1];
				$date_info['month'] = $matches[2];
				$date_info['day']   = $matches[3];
			}
		}

		return apply_filters( 'forminator_field_date_parse_dates', $date_info, $date, $format );
	}

	/**
	 * Check data
	 *
	 * @since 1.0
	 *
	 * @param int $month - the month
	 * @param int $day - the day
	 * @param int $year - the year
	 *
	 * @return bool
	 */
	public function check_date( $month, $day, $year ) {
		if ( empty( $month ) || ! is_numeric( $month ) || empty( $day ) || ! is_numeric( $day )
			|| empty( $year )
			|| ! is_numeric( $year )
			|| 4 !== strlen( $year ) ) {
			return false;
		}

		return checkdate( $month, $day, $year );
	}

	/**
	 * Return field inline validation rules
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_validation_rules() {
		$field       = $this->field;
		$id          = self::get_property( 'element_id', $field );
		$type        = trim( self::get_property( 'field_type', $field ) );
		$date_format = self::get_property( 'date_format', $field );
		$rules       = '';

		if ( 'picker' === $type ) {
			$rules .= '"' . $this->get_id( $field ) . '": {' . "\n";
			if ( $this->is_required( $field ) ) {
				$rules .= '"required": true,';
			}

			$rules .= '"dateformat": "' . $date_format . '",';
			$rules .= '},' . "\n";
		} else {
			if ( $this->is_required( $field ) ) {
				$rules .= '"' . $this->get_id( $field ) . '-day": "required",';
				$rules .= '"' . $this->get_id( $field ) . '-month": "required",';
				$rules .= '"' . $this->get_id( $field ) . '-year": "required",';
			}
		}

		return apply_filters( 'forminator_field_date_validation_rules', $rules, $id, $field );
	}

	/**
	 * Return field inline validation errors
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_validation_messages() {
		$field                       = $this->field;
		$type                        = trim( self::get_property( 'field_type', $field ) );
		$date_format                 = self::get_property( 'date_format', $field );
		$required_validation_message = self::get_property( 'required_message', $field, '' );
		$month_label                 = self::get_property( 'month_label', $field, '' );
		$day_label                   = self::get_property( 'day_label', $field, '' );
		$year_label                  = self::get_property( 'year_label', $field, '' );

		if ( empty( $required_validation_message ) ) {
			$required_validation_message = __( 'This field is required.', Forminator::DOMAIN );
		}

		$messages = '';
		if ( 'picker' === $type ) {
			$messages = '"' . $this->get_id( $field ) . '": {' . "\n";
			if ( $this->is_required( $field ) ) {
				$required_validation_message = apply_filters(
					'forminator_field_date_required_validation_message',
					$required_validation_message,
					$field,
					$type,
					$date_format,
					$this
				);
				$messages                   .= '"required": "' . forminator_addcslashes( $required_validation_message ) . '",' . "\n";
			}

			$format_validation_message = apply_filters(
				'forminator_field_date_format_validation_message',
				__( 'Not valid date', Forminator::DOMAIN ),
				$field,
				$type,
				$date_format,
				$this
			);

			$messages .= '"dateformat": "' . forminator_addcslashes( $format_validation_message ) . '",' . "\n";
			$messages .= '},' . "\n";
		} else {
			if ( $this->is_required( $field ) ) {
				$day_validation_message = apply_filters(
					'forminator_field_date_day_validation_message',
					$required_validation_message,
					$field,
					$type,
					$date_format,
					$this
				);
				$messages               = '"' . $this->get_id( $field ) . '-day": "<strong>' . $day_label . '</strong>: ' . forminator_addcslashes( $day_validation_message ) . '",' . "\n";

				$month_validation_message = apply_filters(
					'forminator_field_date_month_validation_message',
					$required_validation_message,
					$field,
					$type,
					$date_format,
					$this
				);
				$messages                .= '"' . $this->get_id( $field ) . '-month": "<strong>' . $month_label . '</strong>: ' . forminator_addcslashes( $month_validation_message ) . '",' . "\n";

				$year_validation_message = apply_filters(
					'forminator_field_date_year_validation_message',
					$required_validation_message,
					$field,
					$type,
					$date_format,
					$this
				);
				$messages               .= '"' . $this->get_id( $field ) . '-year": "<strong>' . $year_label . '</strong>: ' . forminator_addcslashes( $year_validation_message ) . '",' . "\n";
			}
		}

		return apply_filters( 'forminator_field_date_validation_message', $messages, $field, $type, $date_format, $this );
	}

	/**
	 * Field back-end validation
	 *
	 * @since 1.0
	 *
	 * @param array $field
	 * @param array|string $data
	 */
	public function validate( $field, $data ) {
		$id = self::get_property( 'element_id', $field );

		if ( $this->is_required( $field ) ) {
			$required_validation_message = self::get_property( 'required_message', $field, __( 'This field is required. Please enter a valid date', Forminator::DOMAIN ) );
			if ( empty( $data ) ) {
				$this->validation_message[ $id ] = apply_filters(
					'forminator_field_date_required_field_validation_message',
					$required_validation_message,
					$id,
					$data,
					$this
				);

				return;
			}
		}

		if ( empty( $data ) ) {
			return;
		}

		// subfields `{"year":"","day":"","month":""}`
		if ( is_array( $data ) ) {
			$is_all_empty = true;
			foreach ( $data as $value ) {
				if ( empty( $value ) ) {
					$is_all_empty = false;
					break;
				}
			}
			if ( $is_all_empty ) {
				return;
			}
		}

		// Always! (we dont have validate flag on builder) validate date_format
		$date_format = self::get_property( 'date_format', $field );
		$date        = self::parse_date( $data, $date_format );
		if ( empty( $date ) || ! $this->check_date( $date['month'], $date['day'], $date['year'] ) ) {
			$this->validation_message[ $id ] = apply_filters(
				'forminator_field_date_valid_date_validation_message',
				__( 'Please enter a valid date', Forminator::DOMAIN ),
				$id,
				$data,
				$date_format,
				$this
			);
		} else {
			$year_range = self::get_property( 'year_range', $field, false );
			if ( $year_range ) {
				$min_year = self::get_property( 'min_year', $field );
				$max_year = self::get_property( 'max_year', $field );
				$year     = intval( $date['year'] );
				if ( ! empty( $min_year ) && ! empty( $max_year ) ) {
					if ( $year < $min_year || $year > $max_year ) {
						$this->validation_message[ $id ] = apply_filters(
							'forminator_field_date_valid_maxmin_year_validation_message',
							__( 'Please enter a valid year', Forminator::DOMAIN )
						);
					}
				} else {
					if ( ! empty( $min_year ) ) {
						if ( $year < $min_year ) {
							$this->validation_message[ $id ] = apply_filters(
								'forminator_field_date_valid_maxmin_year_validation_message',
								__( 'Please enter a valid year', Forminator::DOMAIN )
							);
						}
					}
					if ( ! empty( $max_year ) ) {
						if ( $year > $max_year ) {
							$this->validation_message[ $id ] = apply_filters(
								'forminator_field_date_valid_maxmin_year_validation_message',
								__( 'Please enter a valid year', Forminator::DOMAIN )
							);
						}
					}
				}
			}
		}
	}


	/**
	 * Sanitize data
	 *
	 * @since 1.0.2
	 *
	 * @param array $field
	 * @param array|string $data - the data to be sanitized
	 *
	 * @return array|string $data - the data after sanitization
	 */
	public function sanitize( $field, $data ) {
		$original_data = $data;
		// Sanitize
		$data = forminator_sanitize_field( $data );

		return apply_filters( 'forminator_field_date_sanitize', $data, $field, $original_data );
	}
}
