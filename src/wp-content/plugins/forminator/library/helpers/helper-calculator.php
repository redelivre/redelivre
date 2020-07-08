<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Replace fields with dummy value
 * dummy value will usually this format (1) depends @see Forminator_Field::get_dummy_calculable_value()
 *
 * use `1` : it less likely result error when calculation executed, unlike divide by 1 ( X/1 ), divide by 0 (X/0) will have INF result
 * use bracket : it will less likely miss interpreted, when 3{number-1} added, without bracket it will interpreted as `31` which is valid term but less likely what we want
 *
 * @since 1.7
 *
 * @param string $formula
 *
 * @return string
 */
function forminator_calculator_maybe_dummify_fields_on_formula( $formula ) {
	$fields_collection       = forminator_fields_to_array();
	$field_types             = array_keys( $fields_collection );
	$increment_field_pattern = sprintf( '(%s)-\d+', implode( '|', $field_types ) );
	$pattern                 = '/\{(' . $increment_field_pattern . ')(\-[A-Za-z-_]+)?\}/';

	if ( preg_match_all( $pattern, $formula, $matches ) ) {
		// use matches [2], because $field_types group there
		if ( ! isset( $matches[0] ) || ! is_array( $matches[0] ) || ! isset( $matches[2] ) || ! is_array( $matches[2] ) ) {
			return $formula;
		}

		// later usage for str_replace
		$full_matches = $matches[0];
		foreach ( $matches[2] as $key => $field_type ) {
			if ( ! isset( $full_matches[ $key ] ) ) {
				continue;
			}

			if ( ! isset( $fields_collection[ $field_type ] ) ) {
				continue;
			}

			/** @var Forminator_Field $field_object */
			$field_object = $fields_collection[ $field_type ];
			$dummy_value  = $field_object->get_dummy_calculable_value();
			// bracket-ify
			$dummy_value = '(' . ( $dummy_value ) . ')';

			$formula = str_replace( $full_matches[ $key ], $dummy_value, $formula );
		}
	}

	return $formula;
}

/**
 * Get max nexted formula
 *
 * To avoid infinite recursive on nested calculation, this needs to be limited
 *
 * @since 1.7
 *
 * @return int
 */
function forminator_calculator_get_max_nested_formula() {
	// default : 5
	$max_nested_formula = defined( 'FORMINATOR_CALC_MAX_NESTED_FORMULA' ) ? FORMINATOR_CALC_MAX_NESTED_FORMULA : 5;

	/**
	 * Filter max nested formula allowed
	 *
	 * @since 1.7
	 *
	 * @param int $max_nested_formula
	 *
	 * @return int
	 */
	$max_nested_formula = apply_filters( 'forminator_calculator_max_nested_formula', $max_nested_formula );

	return $max_nested_formula;
}

/**
 * Replacing field with values from submitted data
 *
 * in case nested formula exist, it will be replaced with the formula it self rather than the result
 *
 * @since 1.7
 *
 * @param string                       $formula
 * @param array                        $submitted_data
 * @param Forminator_Custom_Form_Model $custom_form
 * @param int                          $nested_count
 *
 * @return string
 */
function forminator_calculator_maybe_replace_fields_on_formula( $formula, $submitted_data, $custom_form, &$nested_count = 0 ) {

	// more then allowed nested formula
	if ( $nested_count > forminator_calculator_get_max_nested_formula() ) {
		return $formula;
	}

	$nested_exists = false;

	$fields_collection       = forminator_fields_to_array();
	$field_types             = array_keys( $fields_collection );
	$increment_field_pattern = sprintf( '(%s)-\d+', implode( '|', $field_types ) );
	$pattern                 = '/\{(' . $increment_field_pattern . ')(\-[A-Za-z-_]+)?\}/';

	if ( preg_match_all( $pattern, $formula, $matches ) ) {
		// use matches [2], because $field_types group there
		if ( ! isset( $matches[0] ) || ! is_array( $matches[0] ) || ! isset( $matches[2] ) || ! is_array( $matches[2] ) ) {
			return $formula;
		}

		// later usage for str_replace
		$full_matches = $matches[0];
		$field_ids    = $matches[1];
		foreach ( $matches[2] as $key => $field_type ) {
			if ( 'calculation' === $field_type && ! $nested_exists ) {
				$nested_exists = true;
			}

			if ( ! isset( $full_matches[ $key ] ) ) {
				continue;
			}

			if ( ! isset( $fields_collection[ $field_type ] ) ) {
				continue;
			}

			/** @var Forminator_Field $field_object */
			$field_object = $fields_collection[ $field_type ];

			if ( ! isset( $field_ids[ $key ] ) ) {
				continue;
			}

			$field_id = $field_ids[ $key ];

			$field_settings = $custom_form->get_formatted_array_field( $field_id );

			if ( is_null( $field_settings ) || ! is_array( $field_settings ) ) {
				continue;
			}

			$submitted_field_data = isset( $submitted_data[ $field_id ] ) ? $submitted_data[ $field_id ] : null;
			if ( $field_object->is_hidden( $field_settings, $submitted_data, [], $custom_form ) ) {
				// skip validation, hidden values = 0 or 1
				// see Forminator_CForm_Front_Action::replace_hidden_field_values()
				$value = $submitted_field_data;
			} else {
				$value = $field_object->get_calculable_value( $submitted_field_data, $field_settings );
			}

			// bracket-ify
			$dummy_value = '(' . ( $value ) . ')';
			$formula     = str_replace( $full_matches[ $key ], $dummy_value, $formula );

		}
	}

	if ( $nested_exists ) {
		$nested_count ++;
		$formula = forminator_calculator_maybe_replace_fields_on_formula( $formula, $submitted_data, $custom_form, $nested_count );
	}

	return $formula;
}
