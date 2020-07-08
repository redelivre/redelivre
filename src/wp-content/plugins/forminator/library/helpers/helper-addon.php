<?php
/**
 * Get registered addon instance by `$slug`
 *
 * @since 1.1
 *
 * @param $slug
 *
 * @return Forminator_Addon_Abstract|null
 */
function forminator_get_addon( $slug ) {
	return Forminator_Addon_Loader::get_instance()->get_addon( $slug );
}

/**
 * Get Pro Addon List
 *
 * @todo  : Verify pro addon list from non user modifiable data source (API or similar others)
 *
 * @since 1.1
 * @return array
 */
function forminator_get_pro_addon_list() {
	$pro_addons = array(
		'mailchimp' => array(
			'_image'                  => 'https://via.placeholder.com/350x150?',
			'_icon'                   => 'mailchimp',
			'_title'                  => 'Mailchimp',
			'_short_title'            => 'Mailchimp',
			'_version'                => '1.0',
			'_description'            => __( 'Unlock this as part of a WPMU DEV Membership', Forminator::DOMAIN ),
			'_min_forminator_version' => FORMINATOR_VERSION,
		),
		'zapier'    => array(
			'_image'                  => 'https://via.placeholder.com/350x150',
			'_icon'                   => 'zapier',
			'_title'                  => 'Zapier',
			'_short_title'            => 'Zapier',
			'_version'                => '1.0',
			'_description'            => __( 'Unlock this as part of a WPMU DEV Membership', Forminator::DOMAIN ),
			'_min_forminator_version' => FORMINATOR_VERSION,
		),
	);

	return $pro_addons;
}


/**
 * Get all add-ons as list
 *
 * @since 1.1
 * @return array
 */
function forminator_get_registered_addons_list() {
	$addon_list = Forminator_Addon_Loader::get_instance()->get_addons()->to_array();
	usort( $addon_list, 'sort_addons' );

	// late init properties
	foreach ( $addon_list as $key => $addon ) {
		$addon_list[ $key ]['is_active'] = Forminator_Addon_Loader::get_instance()->addon_is_active( $key );
	}

	return $addon_list;
}

/**
 * Sort addons
 *
 * @param $a
 * @param $b
 *
 * @return mixed
 */
function sort_addons( $a, $b ) {
	return $a['position'] - $b['position'];
}

/**
 * Get registered addons grouped by connected status
 *
 * @since 1.1
 * @return array
 */
function forminator_get_registered_addons_grouped_by_connected() {
	$addon_list           = forminator_get_registered_addons_list();
	$connected_addons     = array();
	$not_connected_addons = array();

	// late init properties
	foreach ( $addon_list as $key => $addon ) {
		if ( $addon['is_connected'] ) {
			$connected_addons[] = $addon;
		} else {
			$not_connected_addons[] = $addon;
		}
	}

	return array(
		'connected'     => $connected_addons,
		'not_connected' => $not_connected_addons,
	);
}

/**
 * Get addon instances that connected with a form
 *
 * @since 1.1
 *
 * @todo  make instances static and available through runtime
 *
 * @param $form_id
 *
 * @return Forminator_Addon_Abstract[]
 */
function forminator_get_addons_instance_connected_with_form( $form_id ) {
	$addons = array();

	$active_addons_slug = Forminator_Addon_Loader::get_instance()->get_activated_addons();

	foreach ( $active_addons_slug as $active_addon_slug ) {
		$addon = forminator_get_addon( $active_addon_slug );
		if ( $addon ) {
			if ( $addon->is_connected() && $addon->is_form_connected( $form_id ) ) {
				$addons[] = $addon;
			}
		}
	}

	return $addons;
}

/**
 * Get addon(s) in array format grouped by connected / not connected with $form_id
 *
 * Every addon inside this array will be formatted first by @see Forminator_Addon_Abstract::to_array_with_form()
 *
 * @since 1.1
 *
 * @param $form_id
 *
 * @return array
 */
function forminator_get_registered_addons_grouped_by_form_connected( $form_id ) {
	$connected_addons     = array();
	$not_connected_addons = array();

	$addons = Forminator_Addon_Loader::get_instance()->get_addons();
	foreach ( $addons as $slug => $addon ) {
		/** @var Forminator_Addon_Abstract $addon */
		if ( $addon->is_connected() ) {
			if ( $addon->is_allow_multi_on_form() ) {
				$addon_array = $addon->to_array_with_form( $form_id );
				if ( $addon->is_form_connected( $form_id ) && isset( $addon_array['multi_ids'] ) && is_array( $addon_array['multi_ids'] ) ) {
					foreach ( $addon_array['multi_ids'] as $multi_id ) {
						$addon_array['multi_id']   = $multi_id['id'];
						$addon_array['multi_name'] = ! empty( $multi_id['label'] ) ? $multi_id['label'] : $multi_id['id'];
						$connected_addons[]        = $addon_array;
					}
				} else {
					$not_connected_addons[] = $addon->to_array_with_form( $form_id );
				}
			} else {
				if ( $addon->is_form_connected( $form_id ) ) {
					$connected_addons[] = $addon->to_array_with_form( $form_id );
				} else {
					$not_connected_addons[] = $addon->to_array_with_form( $form_id );
				}
			}
		}
	}

	return array(
		'form_connected'     => $connected_addons,
		'not_form_connected' => $not_connected_addons,
	);
}

/**
 * Attach default addon hooks for Addon.
 *
 * Call when needed only,
 * defined in @see Forminator_Addon_Abstract::global_hookable()
 * and @see Forminator_Addon_Abstract::admin_hookable on admin mode
 *
 * @since 1.1
 *
 * @param Forminator_Addon_Abstract $addon
 */
function forminator_maybe_attach_addon_hook( Forminator_Addon_Abstract $addon ) {
	$addon->global_hookable();
	// only hooks that available on admin
	if ( is_admin() ) {
		$addon->admin_hookable();
	}
}

/**
 * Helper Check if addon is active
 *
 * @since 1.1
 *
 * @param $slug
 *
 * @return bool
 */
function forminator_addon_is_active( $slug ) {
	return Forminator_Addon_Loader::get_instance()->addon_is_active( $slug );
}

/**
 * Get allowed field type available for addon
 *
 * @since 1.1
 * @return array
 */
function forminator_get_allowed_field_types_for_addon() {
	$allowed_field_types = array(
		'address-street_address',
		'address-address_line',
		'address-city',
		'address-state',
		'address-zip',
		'address-country',
		'date', // force into one
		'email',
		'hidden',
		'checkbox',
		'gdprcheckbox',
		'name', // single
		'name-prefix', // multiple
		'name-first-name',
		'name-middle-name',
		'name-last-name',
		'number',
		'phone',
		'postdata-post-title',
		'postdata-post-content',
		'postdata-post-excerpt',
		'postdata-post-category',
		'postdata-post-tags',
		'postdata-post-image',
		'select',
		'text',
		'time',
		//phpcs:ignore
		//			'time.hours', // force into one
		//			'time.minutes',
		//			'time.ampm',
		'upload',
		'url',
		// 1.6 fields
		'textarea',
		'radio',
		'checkbox',
		// 1.7 fields
		'calculation',
		'stripe',
		'paypal',
	);

	/**
	 * Filter allowed filed types to be used by addons
	 *
	 * This value will be used by **ALL** addons
	 *
	 * @since 1.1
	 *
	 * @param array $allowed_field_types current allowed field types
	 */
	$allowed_field_types = apply_filters( 'forminator_addon_allowed_field_types', $allowed_field_types );

	return $allowed_field_types;
}

/**
 * Format Form Fields
 *
 * @since 1.1
 *
 * @param Forminator_Base_Form_Model $custom_form_model
 *
 * @return array
 */
function forminator_addon_format_form_fields( Forminator_Base_Form_Model $custom_form_model ) {
	$formatted_fields    = array();
	$fields              = $custom_form_model->get_fields();
	$allowed_field_types = forminator_get_allowed_field_types_for_addon();

	foreach ( $fields as $field ) {
		$ignored_fields = Forminator_Form_Entry_Model::ignored_fields();
		if ( in_array( $field->__get( 'type' ), $ignored_fields, true ) ) {
			continue;
		}

		$field_as_array = $field->to_formatted_array();
		// check non label fields
		if ( ! isset( $field_as_array['field_label'] ) || empty( $field_as_array['field_label'] ) ) {
			$field_as_array['field_label'] = $field_as_array['type'];
		}

		// handle multiple
		$multi_fields = forminator_addon_flatten_mutiple_field( $field_as_array );
		if ( false === $multi_fields ) {
			if ( ! in_array( $field_as_array['type'], $allowed_field_types, true ) ) {
				continue;
			}
			$formatted_fields[] = $field_as_array;
		} else {
			foreach ( $multi_fields as $multi_field ) {
				if ( ! in_array( $multi_field['type'], $allowed_field_types, true ) ) {
					continue;
				}
				$formatted_fields[] = $multi_field;
			}
		}
	}

	/**
	 * Filter formatted fields to be used by addon
	 *
	 * This value will be used by **ALL** addons
	 *
	 * @since 1.1
	 *
	 * @param array                        $formatted_fields  current formatted fields
	 * @param Forminator_Custom_Form_Model $custom_form_model Custom form Model
	 */
	$formatted_fields = apply_filters( 'forminator_addon_formatted_fields', $formatted_fields, $custom_form_model );

	return $formatted_fields;
}

/**
 * Flatten multiple field
 *
 * @since 1.1
 *
 * @param $field_array
 *
 * @return array|bool array flatten multi-field or false, when its not considered as multi-field
 */
function forminator_addon_flatten_mutiple_field( $field_array ) {
	$multiple_field_types = array(
		'name',
		'postdata',
		'address',
	);

	if ( ! in_array( $field_array['type'], $multiple_field_types, true ) ) {
		return false;
	}

	// flatten name
	if ( 'name' === $field_array['type'] ) {
		$is_multiple_name = isset( $field_array['multiple_name'] ) && filter_var( $field_array['multiple_name'], FILTER_VALIDATE_BOOLEAN ) ? true : false;
		if ( ! $is_multiple_name ) {
			return false;
		}

		$prefix_enabled      = isset( $field_array['prefix'] ) && filter_var( $field_array['prefix'], FILTER_VALIDATE_BOOLEAN ) ? true : false;
		$first_name_enabled  = isset( $field_array['fname'] ) && filter_var( $field_array['fname'], FILTER_VALIDATE_BOOLEAN ) ? true : false;
		$middle_name_enabled = isset( $field_array['mname'] ) && filter_var( $field_array['mname'], FILTER_VALIDATE_BOOLEAN ) ? true : false;
		$last_name_enabled   = isset( $field_array['lname'] ) && filter_var( $field_array['lname'], FILTER_VALIDATE_BOOLEAN ) ? true : false;
		if ( $prefix_enabled || $first_name_enabled || $middle_name_enabled || $last_name_enabled ) {
			$multi_fields = array();
			if ( $prefix_enabled ) {
				$multi_field = $field_array;

				$default_label = Forminator_Form_Entry_Model::translate_suffix( 'prefix' );
				$label         = isset( $multi_field['prefix_label'] ) ? $multi_field['prefix_label'] : '';

				$multi_field['type']        = $multi_field['type'] . '-prefix';
				$multi_field['element_id']  = $multi_field['element_id'] . '-prefix';
				$multi_field['field_label'] = ( $label ? $label : $default_label );

				$multi_fields [] = $multi_field;
			}

			if ( $first_name_enabled ) {
				$multi_field = $field_array;

				$default_label = Forminator_Form_Entry_Model::translate_suffix( 'first-name' );
				$label         = isset( $multi_field['fname_label'] ) ? $multi_field['fname_label'] : '';

				$multi_field['type']        = $multi_field['type'] . '-first-name';
				$multi_field['element_id']  = $multi_field['element_id'] . '-first-name';
				$multi_field['field_label'] = ( $label ? $label : $default_label );

				$multi_fields [] = $multi_field;
			}

			if ( $middle_name_enabled ) {
				$multi_field = $field_array;

				$default_label = Forminator_Form_Entry_Model::translate_suffix( 'middle-name' );
				$label         = isset( $multi_field['mname_label'] ) ? $multi_field['mname_label'] : '';

				$multi_field['type']        = $multi_field['type'] . '-middle-name';
				$multi_field['element_id']  = $multi_field['element_id'] . '-middle-name';
				$multi_field['field_label'] = ( $label ? $label : $default_label );

				$multi_fields [] = $multi_field;
			}

			if ( $last_name_enabled ) {
				$multi_field = $field_array;

				$default_label = Forminator_Form_Entry_Model::translate_suffix( 'last-name' );
				$label         = isset( $multi_field['lname_label'] ) ? $multi_field['lname_label'] : '';

				$multi_field['type']        = $multi_field['type'] . '-last-name';
				$multi_field['element_id']  = $multi_field['element_id'] . '-last-name';
				$multi_field['field_label'] = ( $label ? $label : $default_label );

				$multi_fields [] = $multi_field;
			}

			return $multi_fields;
		}
	} elseif ( 'postdata' === $field_array['type'] ) {
		// flatten POSTDATA
		$title_enabled   = isset( $field_array['post_title'] ) && ! empty( $field_array['post_title'] ) ? true : false;
		$content_enabled = isset( $field_array['post_content'] ) && ! empty( $field_array['post_content'] ) ? true : false;
		$excerpt_enabled = isset( $field_array['post_excerpt'] ) && ! empty( $field_array['post_excerpt'] ) ? true : false;
		$image_enabled   = isset( $field_array['post_image'] ) && ! empty( $field_array['post_image'] ) ? true : false;
		$post_type       = isset( $field_array['post_type'] ) && ! empty( $field_array['post_type'] ) ? $field_array['post_type'] : 'post';
		$category_list   = forminator_post_categories( $post_type );
		if ( $title_enabled || $content_enabled || $excerpt_enabled || $image_enabled || $category_list ) {
			$multi_fields = array();

			if ( $title_enabled ) {
				$multi_field = $field_array;

				$default_label = Forminator_Form_Entry_Model::translate_suffix( 'post-title' );
				$label         = isset( $multi_field['post_title_label'] ) ? $multi_field['post_title_label'] : '';

				$multi_field['type']        = $multi_field['type'] . '-post-title';
				$multi_field['element_id']  = $multi_field['element_id'] . '-post-title';
				$multi_field['field_label'] = ( $label ? $label : $default_label );

				$multi_fields [] = $multi_field;
			}

			if ( $content_enabled ) {
				$multi_field = $field_array;

				$default_label = Forminator_Form_Entry_Model::translate_suffix( 'post-content' );
				$label         = isset( $multi_field['post_content_label'] ) ? $multi_field['post_content_label'] : '';

				$multi_field['type']        = $multi_field['type'] . '-post-content';
				$multi_field['element_id']  = $multi_field['element_id'] . '-post-content';
				$multi_field['field_label'] = ( $label ? $label : $default_label );

				$multi_fields [] = $multi_field;
			}

			if ( $excerpt_enabled ) {
				$multi_field = $field_array;

				$default_label = Forminator_Form_Entry_Model::translate_suffix( 'post-excerpt' );
				$label         = isset( $multi_field['post_excerpt_label'] ) ? $multi_field['post_excerpt_label'] : '';

				$multi_field['type']        = $multi_field['type'] . '-post-excerpt';
				$multi_field['element_id']  = $multi_field['element_id'] . '-post-excerpt';
				$multi_field['field_label'] = ( $label ? $label : $default_label );

				$multi_fields [] = $multi_field;
			}

			if ( ! empty( $category_list ) ) {
				foreach ( $category_list as $category ) {
					$category_enabled = isset( $field_array[ $category['value'] ] ) && ! empty( $field_array[ $category['value'] ] ) ? true : false;
					if ( $category_enabled ) {
						$multi_field = $field_array;

						$default_label = $category['label'];
						$label         = isset( $multi_field[ $category['value'] . '_label' ] ) ? $multi_field[ $category['value'] . '_label' ] : '';

						$multi_field['type']        = $multi_field['type'] . '-' . $category['value'];
						$multi_field['element_id']  = $multi_field['element_id'] . '-' . $category['value'];
						$multi_field['field_label'] = ( $label ? $label : $default_label );

						$multi_fields [] = $multi_field;
					}
				}
			}

			if ( $image_enabled ) {
				$multi_field = $field_array;

				$default_label = Forminator_Form_Entry_Model::translate_suffix( 'post-image' );
				$label         = isset( $multi_field['post_image_label'] ) ? $multi_field['post_image_label'] : '';

				$multi_field['type']        = $multi_field['type'] . '-post-image';
				$multi_field['element_id']  = $multi_field['element_id'] . '-post-image';
				$multi_field['field_label'] = ( $label ? $label : $default_label );

				$multi_fields [] = $multi_field;
			}

			return $multi_fields;
		}
	} elseif ( 'address' === $field_array['type'] ) {
		// flatten ADDRESS
		$street_enabled  = isset( $field_array['street_address'] ) && filter_var( $field_array['street_address'], FILTER_VALIDATE_BOOLEAN ) ? true : false;
		$line_enabled    = isset( $field_array['address_line'] ) && filter_var( $field_array['address_line'], FILTER_VALIDATE_BOOLEAN ) ? true : false;
		$city_enabled    = isset( $field_array['address_city'] ) && filter_var( $field_array['address_city'], FILTER_VALIDATE_BOOLEAN ) ? true : false;
		$state_enabled   = isset( $field_array['address_state'] ) && filter_var( $field_array['address_state'], FILTER_VALIDATE_BOOLEAN ) ? true : false;
		$zip_enabled     = isset( $field_array['address_zip'] ) && filter_var( $field_array['address_zip'], FILTER_VALIDATE_BOOLEAN ) ? true : false;
		$country_enabled = isset( $field_array['address_country'] ) && filter_var( $field_array['address_country'], FILTER_VALIDATE_BOOLEAN ) ? true : false;
		if ( $street_enabled || $line_enabled || $city_enabled || $state_enabled || $zip_enabled || $country_enabled ) {
			$multi_fields = array();
			if ( $street_enabled ) {
				$multi_field = $field_array;

				$default_label = Forminator_Form_Entry_Model::translate_suffix( 'street_address' );
				$label         = isset( $multi_field['street_address_label'] ) ? $multi_field['street_address_label'] : '';

				$multi_field['type']        = $multi_field['type'] . '-street_address';
				$multi_field['element_id']  = $multi_field['element_id'] . '-street_address';
				$multi_field['field_label'] = ( $label ? $label : $default_label );

				$multi_fields [] = $multi_field;
			}

			if ( $line_enabled ) {
				$multi_field = $field_array;

				$default_label = Forminator_Form_Entry_Model::translate_suffix( 'address_line' );
				$label         = isset( $multi_field['address_line_label'] ) ? $multi_field['address_line_label'] : '';

				$multi_field['type']        = $multi_field['type'] . '-address_line';
				$multi_field['element_id']  = $multi_field['element_id'] . '-address_line';
				$multi_field['field_label'] = ( $label ? $label : $default_label );

				$multi_fields [] = $multi_field;
			}

			if ( $city_enabled ) {
				$multi_field = $field_array;

				$default_label = Forminator_Form_Entry_Model::translate_suffix( 'city' );
				$label         = isset( $multi_field['address_city_label'] ) ? $multi_field['address_city_label'] : '';

				$multi_field['type']        = $multi_field['type'] . '-city';
				$multi_field['element_id']  = $multi_field['element_id'] . '-city';
				$multi_field['field_label'] = ( $label ? $label : $default_label );

				$multi_fields [] = $multi_field;
			}

			if ( $state_enabled ) {
				$multi_field = $field_array;

				$default_label = Forminator_Form_Entry_Model::translate_suffix( 'state' );
				$label         = isset( $multi_field['address_state_label'] ) ? $multi_field['address_state_label'] : '';

				$multi_field['type']        = $multi_field['type'] . '-state';
				$multi_field['element_id']  = $multi_field['element_id'] . '-state';
				$multi_field['field_label'] = ( $label ? $label : $default_label );

				$multi_fields [] = $multi_field;
			}

			if ( $zip_enabled ) {
				$multi_field = $field_array;

				$default_label = Forminator_Form_Entry_Model::translate_suffix( 'zip' );
				$label         = isset( $multi_field['address_zip_label'] ) ? $multi_field['address_zip_label'] : '';

				$multi_field['type']        = $multi_field['type'] . '-zip';
				$multi_field['element_id']  = $multi_field['element_id'] . '-zip';
				$multi_field['field_label'] = ( $label ? $label : $default_label );

				$multi_fields [] = $multi_field;
			}

			if ( $country_enabled ) {
				$multi_field = $field_array;

				$default_label = Forminator_Form_Entry_Model::translate_suffix( 'country' );
				$label         = isset( $multi_field['address_country_label'] ) ? $multi_field['address_country_label'] : '';

				$multi_field['type']        = $multi_field['type'] . '-country';
				$multi_field['element_id']  = $multi_field['element_id'] . '-country';
				$multi_field['field_label'] = ( $label ? $label : $default_label );

				$multi_fields [] = $multi_field;
			}

			return $multi_fields;
		}
	}

	return false;

}

/**
 * Formatted submmiited data of Form to used by addon
 *
 * @since 1.1
 * @since 1.3 add entry fields as parameter to trace back submit data to entry fields to be added
 *
 * @param array $post_data            raw $_POST
 * @param array $files_data           raw $_FILES
 * @param array $form_fields          existing form fields
 * @param array $current_entry_fields current entry fields
 *
 * @return array
 */
function forminator_format_submitted_data_for_addon( $post_data, $files_data, $form_fields, $current_entry_fields = array() ) {
	$formatted_post_data = array();

	if ( isset( $post_data['render_id'] ) ) {
		$formatted_post_data['render_id'] = $post_data['render_id'];
	}

	if ( isset( $post_data['page_id'] ) ) {
		$formatted_post_data['page_id'] = $post_data['page_id'];
	}

	if ( isset( $post_data['current_url'] ) ) {
		$formatted_post_data['current_url'] = $post_data['current_url'];
	}

	if ( isset( $post_data['_wp_http_referer'] ) ) {
		$formatted_post_data['_wp_http_referer'] = $post_data['_wp_http_referer'];
	}

	unset( $post_data['forminator_nonce'] );
	unset( $post_data['form_id'] );
	unset( $post_data['action'] );

	// loop on form fields
	foreach ( $form_fields as $form_field ) {
		if ( isset( $post_data[ $form_field['element_id'] ] ) ) {
			$formatted_post_data [ $form_field['element_id'] ] = $post_data[ $form_field['element_id'] ];
		} else {
			if ( 'time' === $form_field['type'] ) {

				//need to be concatenated
				$element_id         = $form_field['element_id'];
				$hours_element_id   = $element_id . '-hours';
				$minutes_element_id = $element_id . '-minutes';
				$ampm_element_id    = $element_id . '-ampm';
				if ( isset( $post_data[ $hours_element_id ] ) && isset( $post_data[ $minutes_element_id ] ) ) {
					$hours   = (int) $post_data[ $hours_element_id ];
					$minutes = (int) $post_data[ $minutes_element_id ];

					$data = array(
						'hours'   => $hours,
						'minutes' => $minutes,
					);

					if ( isset( $post_data[ $ampm_element_id ] ) ) {
						$data['ampm'] = $post_data[ $ampm_element_id ];
					}

					$time = Forminator_Form_Entry_Model::meta_value_to_string( $form_field['type'], $data, false );

					$formatted_post_data[ $form_field['element_id'] ] = $time;
					unset( $post_data[ $hours_element_id ] );
					unset( $post_data[ $minutes_element_id ] );
					unset( $post_data[ $ampm_element_id ] );
				}
			} elseif ( 'date' === $form_field['type'] ) {
				$element_id       = $form_field['element_id'];
				$day_element_id   = $element_id . '-day';
				$month_element_id = $element_id . '-month';
				$year_element_id  = $element_id . '-year';

				if ( isset( $post_data[ $day_element_id ] ) && isset( $post_data[ $month_element_id ] ) && isset( $post_data[ $year_element_id ] ) ) {
					$data = array(
						'day'   => $post_data[ $day_element_id ],
						'month' => $post_data[ $month_element_id ],
						'year'  => $post_data[ $year_element_id ],
					);

					$date = Forminator_Form_Entry_Model::meta_value_to_string( $form_field['type'], $data, false );

					$formatted_post_data[ $form_field['element_id'] ] = $date;
					unset( $post_data[ $day_element_id ] );
					unset( $post_data[ $month_element_id ] );
					unset( $post_data[ $year_element_id ] );
				}
			} elseif ( isset( $files_data[ $form_field['element_id'] ] ) ) {
				// $_FILES
				$formatted_post_data[ $form_field['element_id'] ] = $files_data[ $form_field['element_id'] ];

				foreach ( $current_entry_fields as $current_entry_field ) {
					if ( isset( $current_entry_field['name'] ) && $form_field['element_id'] === $current_entry_field['name'] ) {
						if ( isset( $current_entry_field['value'] ) && isset( $current_entry_field['value']['file'] ) ) {
							$file_props                                       = $current_entry_field['value']['file'];
							$formatted_post_data[ $form_field['element_id'] ] = array_merge( $formatted_post_data[ $form_field['element_id'] ], $file_props );
							break;
						}
					}
				}
			}
		}
	}

	// add left-over $_POST
	foreach ( $post_data as $key => $post_datum ) {
		if ( ! isset( $formatted_post_data[ $key ] ) ) {
			$formatted_post_data[ $key ] = $post_datum;
		}
	}

	// add left-over $_FILES
	foreach ( $files_data as $key => $files_datum ) {
		if ( ! isset( $formatted_post_data[ $key ] ) ) {
			$formatted_post_data[ $key ] = $files_datum;
		}
	}

	/**
	 * Filter formatted form submmitted data to be used by addon
	 *
	 * @since 1.1
	 *
	 * @param array $formatted_post_data current formatted post data
	 * @param array $post_data           raw $_POST of form submit data
	 * @param array $files_data          raw $_FILES of form submit data
	 * @param array $form_fields         form fields that exist on the form
	 */
	$formatted_post_data = apply_filters( 'forminator_addon_formatted_submitted_data', $formatted_post_data, $post_data, $files_data, $form_fields );

	return $formatted_post_data;
}

/**
 * Format form settings to used by addon
 *
 * @since 1.1
 *
 * @param Forminator_Base_Form_Model $custom_form
 *
 * @return array formatted and filtered form settings
 */
function forminator_addon_format_form_settings( Forminator_Base_Form_Model $custom_form ) {
	$form_settings = $custom_form->settings;

	/**
	 * Filter form settings to used by addon
	 *
	 * It will be used by all Addons
	 *
	 * @since 1.1
	 *
	 * @param array                      $form_settings Current formatted form_settings
	 * @param Forminator_Base_Form_Model $custom_form   Custom Form Model
	 */
	$form_settings = apply_filters( 'forminator_addon_formatted_form_settings', $form_settings, $custom_form );

	return $form_settings;
}

/**
 * Find addon meta data from entry model that saved on db
 *
 * @since 1.1
 *
 * @param Forminator_Addon_Abstract   $connected_addon
 * @param Forminator_Form_Entry_Model $entry_model
 *
 * @return array
 */
function forminator_find_addon_meta_data_from_entry_model( Forminator_Addon_Abstract $connected_addon, Forminator_Form_Entry_Model $entry_model ) {
	$addon_meta_data        = array();
	$addon_meta_data_prefix = 'forminator_addon_' . $connected_addon->get_slug() . '_';
	foreach ( $entry_model->meta_data as $key => $meta_datum ) {
		if ( false !== stripos( $key, $addon_meta_data_prefix ) ) {
			$addon_meta_data[] = array(
				'name'  => str_ireplace( $addon_meta_data_prefix, '', $key ),
				'value' => $meta_datum['value'],
			);
		}
	}

	/**
	 * Filter addon's meta data retrieved from db
	 *
	 * @since 1.1
	 *
	 * @param array                       $addon_meta_data        Current addon meta data retrieved from db
	 * @param Forminator_Addon_Abstract   $connected_addon
	 * @param Forminator_Form_Entry_Model $entry_model
	 * @param string                      $addon_meta_data_prefix default prefix of connected addon meta data key
	 */
	$addon_meta_data = apply_filters( 'forminator_addon_meta_data_from_entry_model', $addon_meta_data, $connected_addon, $entry_model, $addon_meta_data_prefix );

	return $addon_meta_data;
}

/**
 * Generate Html for **single** addon
 *
 * Used on Integrations page, and Form Settings Integration Tab
 *
 * @param array $addon     that already formatted to_array
 * @param int   $form_id
 * @param bool  $show_pro_info
 * @param bool  $is_active (show as active addon ?)
 *
 * @return string
 */
function forminator_addon_row_html_markup( $addon, $form_id, $show_pro_info = false, $is_active = false ) {
	ob_start();

	$single_addon_template_path = forminator_plugin_dir() . 'admin/views/integrations/addon.php';

	/**
	 * Filter Template path of single addon html
	 *
	 * @since 1.1
	 *
	 * @param string $single_addon_template_path current used path
	 */
	$single_addon_template_path = apply_filters( 'forminator_addon_single_addon_template_path', $single_addon_template_path );

	/** @noinspection PhpIncludeInspection */
	include $single_addon_template_path;

	$html = ob_get_clean();

	/**
	 * Filter displayed html **single** addon
	 *
	 * @since 1.1
	 * @since 1.5.1 add $is_active
	 *
	 * @param string $html          current html to be displayed
	 * @param array  $addon         addon instance that already formatted to_array
	 * @param int    $form_id
	 * @param bool   $show_pro_info whether to show pro info
	 *
	 */
	$html = apply_filters( 'forminator_addon_row_html', $html, $addon, $form_id, $show_pro_info, $is_active );

	return $html;
}

/**
 * Add log of forminator addon related if permitted
 *
 * To be this can be active :
 * - WP_DEBUG : true
 * - FORMINATOR_ADDON_DEBUG : true
 * - forminator_addon_enable_log : true
 *
 * @see   forminator_maybe_log()
 *
 * @since 1.1
 * @since 1.3 add FORMINATOR_ADDON_DEBUG as enabled flag
 */
function forminator_addon_maybe_log() {
	$enabled = ( defined( 'FORMINATOR_ADDON_DEBUG' ) && FORMINATOR_ADDON_DEBUG );

	/**
	 * Filter log enable for forminator addon
	 *
	 * By default it will check `WP_DEBUG`, `FORMINATOR_ADDON_DEBUG`, `FORMINATOR_DEBUG` must be true
	 *
	 * @since 1.1
	 *
	 * @param bool $enabled current enable status
	 */
	$enabled = apply_filters( 'forminator_addon_enable_log', $enabled );

	if ( $enabled ) {
		if ( is_callable( 'forminator_maybe_log' ) ) {
			$args  = array( '[ADDON]' );
			$fargs = func_get_args();
			$args  = array_merge( $args, $fargs );
			call_user_func_array( 'forminator_maybe_log', $args );
		}
	}
}

/**
 * Replacer custom form vars
 *
 * @since 1.2
 *
 * @param                              $content
 * @param                              $submitted_data
 * @param Forminator_Custom_Form_Model $custom_form
 * @param                              $entry_meta
 * @param bool                         $allow_html
 *
 * @return mixed|string
 */
function forminator_addon_replace_custom_vars( $content, $submitted_data, Forminator_Custom_Form_Model $custom_form, $entry_meta, $allow_html = false ) {
	$entry_model = new Forminator_Form_Entry_Model( null );
	foreach ( $entry_meta as $meta ) {
		$entry_model->meta_data[ $meta['name'] ] = array(
			'id'    => $meta['name'],
			'value' => wp_unslash( $meta['value'] ),
		);
	}

	$content = forminator_replace_variables( $content, $custom_form->id );
	$content = forminator_replace_custom_form_data( $content, $custom_form, $submitted_data, $entry_model );

	$fields      = forminator_fields_to_array();
	$field_types = array_keys( $fields );

	// sort by length, so stripos will work by traverse from longest field type first
	$field_types_strlen = array_map( 'strlen', $field_types );
	array_multisort( $field_types_strlen, $field_types, SORT_DESC );
	$field_types = array_reverse( $field_types );

	$randomed_field_pattern  = 'field-\d+-\d+';
	$increment_field_pattern = sprintf( '(%s)-\d+', implode( '|', $field_types ) );
	$pattern                 = '/\{((' . $randomed_field_pattern . ')|(' . $increment_field_pattern . '))(\-[A-Za-z-_]+)?\}/';

	// Find all field ID's
	if ( preg_match_all( $pattern, $content, $matches ) ) {
		if ( ! isset( $matches[0] ) || ! is_array( $matches[0] ) ) {
			return $content;
		}
		foreach ( $matches[0] as $match ) {
			$element_id = forminator_clear_field_id( $match );

			$field_type = '';
			$meta_value = array();
			foreach ( $field_types as $type ) {
				if ( false !== stripos( $element_id, $type . '-' ) ) {
					$field_type = $type;
					break;
				}
			}

			if ( isset( $entry_model->meta_data[ $element_id ] ) ) {
				$meta_value = $entry_model->meta_data[ $element_id ]['value'];
			} elseif ( isset( $submitted_data[ $element_id ] ) ) {
				$meta_value = $submitted_data[ $element_id ];
			}

			$value = Forminator_Form_Entry_Model::meta_value_to_string( $field_type, $meta_value, $allow_html );

			$content = str_replace( $match, $value, $content );
		}
	}

	return $content;

}

/**
 * Get admin url of addon integration page
 *
 * @since 1.2
 *
 * @param      $slug
 * @param      $section
 *
 * @param bool $with_nonce
 *
 * @return string
 */
function forminator_addon_integration_section_admin_url( $slug, $section, $with_nonce = true ) {
	if ( ! class_exists( 'Forminator_Integrations_Page' ) ) {
		return '';
	}

	$admin_url  = admin_url( 'admin.php' );
	$query_args = array(
		'page'    => 'forminator-integrations',
		'slug'    => $slug,
		'section' => $section,
	);
	if ( $with_nonce ) {
		$nonce               = Forminator_Integrations_Page::get_addon_page_nonce();
		$query_args['nonce'] = $nonce;
	}

	return add_query_arg(
		$query_args,
		$admin_url
	);
}

/**
 * Get Registered Addons
 *
 * @since 1.5.3
 *
 * @return Forminator_Addon_Abstract[]
 */
function forminator_get_registered_addons() {
	$addons            = array();
	$registered_addons = Forminator_Addon_Loader::get_instance()->get_addons();

	foreach ( $registered_addons as $slug => $registered_addon ) {
		$addon = forminator_get_addon( $slug );
		if ( $addon instanceof Forminator_Addon_Abstract ) {
			$addons[ $addon->get_slug() ] = $addon;
		}
	}

	return $addons;
}

/**
 * Find addon meta data from entry model that saved on db
 *
 * @since 1.5.3
 *
 * @param Forminator_Form_Entry_Model $entry_model
 *
 * @return array
 */
function forminator_find_addon_slugs_from_entry_model( Forminator_Form_Entry_Model $entry_model ) {
	$addon_slugs                   = array();
	$addon_metadata_pattern_prefix = 'forminator_addon_';
	foreach ( $entry_model->meta_data as $key => $meta_datum ) {
		if ( false !== stripos( $key, $addon_metadata_pattern_prefix ) ) {
			$key  = str_ireplace( $addon_metadata_pattern_prefix, '', $key );
			$keys = explode( '_', $key, 2 );
			if ( isset( $keys[0] ) ) {
				$slug = $keys[0];
				if ( ! in_array( $slug, $addon_slugs, true ) ) {
					$addon_slugs[] = $slug;
				}
			}
		}
	}

	/**
	 * Filter addons slug from entry model retrieved from db
	 *
	 * @since 1.5.3
	 *
	 * @param Forminator_Form_Entry_Model $entry_model
	 * @param string                      $addon_meta_data_prefix default prefix of connected addon meta data key
	 */
	$addon_slugs = apply_filters( 'forminator_addon_slugs_from_entry_model', $addon_slugs, $entry_model, $addon_metadata_pattern_prefix );

	return $addon_slugs;
}

/**
 * Format poll settings to used by addon
 *
 * @since 1.6.1
 *
 * @param Forminator_Base_Form_Model $poll
 *
 * @return array formatted and filtered form settings
 */
function forminator_addon_format_poll_settings( Forminator_Base_Form_Model $poll ) {
	$poll_settings = $poll->settings;

	/**
	 * Filter poll settings to used by addon
	 *
	 * It will be used by all Addons
	 *
	 * @since 1.6.1
	 *
	 * @param array                      $poll_settings Current formatted form_settings
	 * @param Forminator_Base_Form_Model $poll          Custom Form Model
	 */
	$poll_settings = apply_filters( 'forminator_addon_formatted_poll_settings', $poll_settings, $poll );

	return $poll_settings;
}

/**
 * Format Poll Fields (contains poll answers)
 *
 * @since 1.6.1
 *
 * @param Forminator_Base_Form_Model $poll
 *
 * @return array
 */
function forminator_addon_format_poll_fields( Forminator_Base_Form_Model $poll ) {
	$formatted_fields = array();
	$fields           = $poll->get_fields();

	foreach ( $fields as $field ) {
		$field_as_array     = $field->to_formatted_array();
		$formatted_fields[] = $field_as_array;
	}

	/**
	 * Filter formatted fields to be used by addon
	 *
	 * This value will be used by **ALL** addons
	 *
	 * @since 1.1
	 *
	 * @param array                        $formatted_fields current formatted fields
	 * @param Forminator_Custom_Form_Model $poll             Custom form Model
	 */
	$formatted_fields = apply_filters( 'forminator_addon_formatted_poll_fields', $formatted_fields, $poll );

	return $formatted_fields;
}

/**
 * Get addon(s) in array format grouped by connected / not connected with $poll_id
 *
 * Every addon inside this array will be formatted first by @see Forminator_Addon_Abstract::to_array_with_poll()
 *
 * @since 1.6.1
 *
 * @param $poll_id
 *
 * @return array
 */
function forminator_get_registered_addons_grouped_by_poll_connected( $poll_id ) {
	$connected_addons     = array();
	$not_connected_addons = array();

	$addons = Forminator_Addon_Loader::get_instance()->get_addons();
	foreach ( $addons as $slug => $addon ) {
		/** @var Forminator_Addon_Abstract $addon */
		if ( $addon->is_connected() ) {
			if ( $addon->is_allow_multi_on_poll() ) {
				$addon_array = $addon->to_array_with_poll( $poll_id );
				if ( $addon->is_poll_connected( $poll_id ) && isset( $addon_array['multi_ids'] ) && is_array( $addon_array['multi_ids'] ) ) {
					foreach ( $addon_array['multi_ids'] as $multi_id ) {
						$addon_array['multi_id']   = $multi_id['id'];
						$addon_array['multi_name'] = ! empty( $multi_id['label'] ) ? $multi_id['label'] : $multi_id['id'];
						$connected_addons[]        = $addon_array;
					}
				} else {
					$not_connected_addons[] = $addon->to_array_with_poll( $poll_id );
				}
			} else {
				if ( $addon->is_poll_connected( $poll_id ) ) {
					$connected_addons[] = $addon->to_array_with_poll( $poll_id );
				} else {
					$not_connected_addons[] = $addon->to_array_with_poll( $poll_id );
				}
			}
		}
	}

	return array(
		'poll_connected'     => $connected_addons,
		'not_poll_connected' => $not_connected_addons,
	);
}

/**
 * Generate Html for **single** poll addon
 *
 * Used on Integrations page, and Form Settings Integration Tab
 *
 * @since 1.6.1
 *
 * @param array $addon     that already formatted to_array
 * @param int   $poll_id
 * @param bool  $show_pro_info
 * @param bool  $is_active (show as active addon ?)
 *
 * @return string
 */
function forminator_addon_poll_row_html_markup( $addon, $poll_id, $show_pro_info = false, $is_active = false ) {
	ob_start();

	$single_addon_template_path = forminator_plugin_dir() . 'admin/views/integrations/poll-addon.php';

	/**
	 * Filter Template path of single addon html
	 *
	 * @since 1.6.1
	 *
	 * @param string $single_addon_template_path current used path
	 */
	$single_addon_template_path = apply_filters( 'forminator_addon_single_poll_addon_template_path', $single_addon_template_path );

	/** @noinspection PhpIncludeInspection */
	include $single_addon_template_path;

	$html = ob_get_clean();

	/**
	 * Filter displayed html **single** addon
	 *
	 * @since 1.6.1
	 *
	 * @param string $html          current html to be displayed
	 * @param array  $addon         addon instance that already formatted to_array
	 * @param int    $poll_id
	 * @param bool   $show_pro_info whether to show pro info
	 *
	 */
	$html = apply_filters( 'forminator_addon_poll_row_html', $html, $addon, $poll_id, $show_pro_info, $is_active );

	return $html;
}

/**
 * Formatted submitted data of Form to used by addon
 *
 * @since 1.6.1
 *
 * @param array $post_data  raw $_POST
 * @param array $files_data raw $_FILES
 *
 * @return array
 */
function forminator_addon_format_poll_submitted_data( $post_data, $files_data ) {
	$formatted_post_data = array();

	if ( isset( $post_data['render_id'] ) ) {
		$formatted_post_data['render_id'] = $post_data['render_id'];
	}

	if ( isset( $post_data['page_id'] ) ) {
		$formatted_post_data['page_id'] = $post_data['page_id'];
	}

	if ( isset( $post_data['current_url'] ) ) {
		$formatted_post_data['current_url'] = $post_data['current_url'];
	}

	if ( isset( $post_data['_wp_http_referer'] ) ) {
		$formatted_post_data['_wp_http_referer'] = $post_data['_wp_http_referer'];
	}

	unset( $post_data['forminator_nonce'] );
	unset( $post_data['form_id'] );
	unset( $post_data['action'] );

	// add left-over $_POST
	foreach ( $post_data as $key => $post_datum ) {
		if ( ! isset( $formatted_post_data[ $key ] ) ) {
			$formatted_post_data[ $key ] = $post_datum;
		}
	}

	// add left-over $_FILES
	foreach ( $files_data as $key => $files_datum ) {
		if ( ! isset( $formatted_post_data[ $key ] ) ) {
			$formatted_post_data[ $key ] = $files_datum;
		}
	}

	/**
	 * Filter formatted form submmitted data to be used by addon
	 *
	 * @since 1.6.1
	 *
	 * @param array $formatted_post_data current formatted post data
	 * @param array $post_data           raw $_POST of form submit data
	 * @param array $files_data          raw $_FILES of form submit data
	 * @param array $form_fields         form fields that exist on the form
	 */
	$formatted_post_data = apply_filters( 'forminator_addon_formatted_poll_submitted_data', $formatted_post_data, $post_data, $files_data );

	return $formatted_post_data;
}


/**
 * Get addon instances that connected with a form
 *
 * @since 1.6.1
 *
 * @todo  make instances static and available through runtime
 *
 * @param $poll_id
 *
 * @return Forminator_Addon_Abstract[]
 */
function forminator_get_addons_instance_connected_with_poll( $poll_id ) {
	$addons = array();

	$active_addons_slug = Forminator_Addon_Loader::get_instance()->get_activated_addons();

	foreach ( $active_addons_slug as $active_addon_slug ) {
		$addon = forminator_get_addon( $active_addon_slug );
		if ( $addon ) {
			if ( $addon->is_connected() && $addon->is_poll_connected( $poll_id ) ) {
				$addons[] = $addon;
			}
		}
	}

	return $addons;
}

/**
 * Format quiz settings to used by addon
 *
 * @since 1.6.2
 *
 * @param Forminator_Quiz_Form_Model $quiz
 *
 * @return array formatted and filtered form settings
 */
function forminator_addon_format_quiz_settings( Forminator_Quiz_Form_Model $quiz ) {
	$quiz_settings = $quiz->settings;

	/**
	 * Filter quiz settings to used by addon
	 *
	 * It will be used by all Addons
	 *
	 * @since 1.6.2
	 *
	 * @param array                      $quiz_settings Current formatted quiz_settings
	 * @param Forminator_Quiz_Form_Model $quiz          Quiz Model
	 */
	$quiz_settings = apply_filters( 'forminator_addon_formatted_quiz_settings', $quiz_settings, $quiz );

	return $quiz_settings;
}

/**
 * Get addon(s) in array format grouped by connected / not connected with $quiz_id
 *
 * Every addon inside this array will be formatted first by @see Forminator_Addon_Abstract::to_array_with_quiz()
 *
 * @since 1.6.2
 *
 * @param $quiz_id
 *
 * @return array
 */
function forminator_get_registered_addons_grouped_by_quiz_connected( $quiz_id ) {
	$connected_addons     = array();
	$not_connected_addons = array();

	$addons = Forminator_Addon_Loader::get_instance()->get_addons();
	foreach ( $addons as $slug => $addon ) {
		/** @var Forminator_Addon_Abstract $addon */
		if ( $addon->is_connected() ) {
			if ( $addon->is_allow_multi_on_quiz() ) {
				$addon_array = $addon->to_array_with_quiz( $quiz_id );
				if ( $addon->is_quiz_connected( $quiz_id ) && isset( $addon_array['multi_ids'] ) && is_array( $addon_array['multi_ids'] ) ) {
					foreach ( $addon_array['multi_ids'] as $multi_id ) {
						$addon_array['multi_id']   = $multi_id['id'];
						$addon_array['multi_name'] = ! empty( $multi_id['label'] ) ? $multi_id['label'] : $multi_id['id'];
						$connected_addons[]        = $addon_array;
					}
				} else {
					$not_connected_addons[] = $addon->to_array_with_quiz( $quiz_id );
				}
			} else {
				if ( $addon->is_quiz_connected( $quiz_id ) ) {
					$connected_addons[] = $addon->to_array_with_quiz( $quiz_id );
				} else {
					$not_connected_addons[] = $addon->to_array_with_quiz( $quiz_id );
				}
			}
		}
	}

	return array(
		'quiz_connected'     => $connected_addons,
		'not_quiz_connected' => $not_connected_addons,
	);
}

/**
 * Generate Html for **single** quiz addon
 *
 * Used on Quiz Settings Integration Tab
 *
 * @since 1.6.2
 *
 * @param array $addon     that already formatted to_array
 * @param int   $quiz_id
 * @param bool  $show_pro_info
 * @param bool  $is_active (show as active addon ?)
 *
 * @return string
 */
function forminator_addon_quiz_row_html_markup( $addon, $quiz_id, $show_pro_info = false, $is_active = false ) {
	ob_start();

	$single_addon_template_path = forminator_plugin_dir() . 'admin/views/integrations/quiz-addon.php';

	/**
	 * Filter Template path of single addon html
	 *
	 * @since 1.6.2
	 *
	 * @param string $single_addon_template_path current used path
	 */
	$single_addon_template_path = apply_filters( 'forminator_addon_single_quiz_addon_template_path', $single_addon_template_path );

	/** @noinspection PhpIncludeInspection */
	include $single_addon_template_path;

	$html = ob_get_clean();

	/**
	 * Filter displayed html **single** addon
	 *
	 * @since 1.6.2
	 *
	 * @param string $html          current html to be displayed
	 * @param array  $addon         addon instance that already formatted to_array
	 * @param int    $quiz_id
	 * @param bool   $show_pro_info whether to show pro info
	 *
	 */
	$html = apply_filters( 'forminator_addon_quiz_row_html', $html, $addon, $quiz_id, $show_pro_info, $is_active );

	return $html;
}

/**
 * Formatted submitted data of Quiz to used by addon
 *
 * @since 1.6.2
 *
 * @param array $post_data  raw $_POST
 * @param array $files_data raw $_FILES
 *
 * @return array
 */
function forminator_addon_format_quiz_submitted_data( $post_data, $files_data ) {
	$formatted_post_data = array();

	if ( isset( $post_data['render_id'] ) ) {
		$formatted_post_data['render_id'] = $post_data['render_id'];
	}

	if ( isset( $post_data['page_id'] ) ) {
		$formatted_post_data['page_id'] = $post_data['page_id'];
	}

	if ( isset( $post_data['current_url'] ) ) {
		$formatted_post_data['current_url'] = $post_data['current_url'];
	}

	if ( isset( $post_data['_wp_http_referer'] ) ) {
		$formatted_post_data['_wp_http_referer'] = $post_data['_wp_http_referer'];
	}

	unset( $post_data['forminator_nonce'] );
	unset( $post_data['form_id'] );
	unset( $post_data['action'] );

	// add left-over $_POST
	foreach ( $post_data as $key => $post_datum ) {
		if ( ! isset( $formatted_post_data[ $key ] ) ) {
			$formatted_post_data[ $key ] = $post_datum;
		}
	}

	// add left-over $_FILES
	foreach ( $files_data as $key => $files_datum ) {
		if ( ! isset( $formatted_post_data[ $key ] ) ) {
			$formatted_post_data[ $key ] = $files_datum;
		}
	}

	/**
	 * Filter formatted form submitted data to be used by addon
	 *
	 * @since 1.6.1
	 *
	 * @param array $formatted_post_data current formatted post data
	 * @param array $post_data           raw $_POST of form submit data
	 * @param array $files_data          raw $_FILES of form submit data
	 * @param array $form_fields         form fields that exist on the form
	 */
	$formatted_post_data = apply_filters( 'forminator_addon_formatted_quiz_submitted_data', $formatted_post_data, $post_data, $files_data );

	return $formatted_post_data;
}

/**
 * Get addon instances that connected with a quiz
 *
 * @since 1.6.2
 *
 * @todo  make instances static and available through runtime
 *
 * @param $quiz_id
 *
 * @return Forminator_Addon_Abstract[]
 */
function forminator_get_addons_instance_connected_with_quiz( $quiz_id ) {
	$addons = array();

	$active_addons_slug = Forminator_Addon_Loader::get_instance()->get_activated_addons();

	foreach ( $active_addons_slug as $active_addon_slug ) {
		$addon = forminator_get_addon( $active_addon_slug );
		if ( $addon ) {
			if ( $addon->is_connected() && $addon->is_quiz_connected( $quiz_id ) ) {
				$addons[] = $addon;
			}
		}
	}

	return $addons;
}
