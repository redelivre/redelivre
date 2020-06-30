<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_CForm_General_Data_Protection
 *
 * General Data Protection Applied for Custom Form
 *
 * @since 1.0.6
 */
class Forminator_CForm_General_Data_Protection extends Forminator_General_Data_Protection {

	/**
	 * Instances of custom form model
	 *
	 * avoid overhead on multiple entries in the same form
	 *
	 * @var array
	 */
	private static $custom_form_model_instances = array();

	public function __construct() {
		parent::__construct( __( 'Forminator Forms', Forminator::DOMAIN ) );

		$this->add_exporter(
			'forminator-form-submissions',
			__( 'Forminator Form Submissions', Forminator::DOMAIN ),
			array( 'Forminator_CForm_General_Data_Protection', 'form_submissions_exporter' )
		);

		$this->add_eraser(
			'forminator-form-submissions',
			__( 'Forminator Form Submissions', Forminator::DOMAIN ),
			array( 'Forminator_CForm_General_Data_Protection', 'form_submissions_eraser' )
		);

	}

	/**
	 * Privacy Policy recommendation message
	 *
	 * @since 1.0.6
	 * @return string
	 */
	public function get_privacy_message() {
		ob_start();
		include dirname( __FILE__ ) . '/policy-text.php';
		$content = ob_get_clean();
		$content = apply_filters( 'forminator_custom_form_privacy_policy_content', $content );

		return $content;
	}

	/**
	 * Export form submission
	 *
	 * @since 1.0.6
	 *
	 * @param $email_address
	 * @param $page
	 *
	 * @return array
	 */
	public static function form_submissions_exporter( $email_address, $page ) {
		$entry_ids = Forminator_Form_Entry_Model::get_custom_form_entry_ids_by_email( $email_address );

		$data_to_export = array();
		if ( ! empty( $entry_ids ) && is_array( $entry_ids ) ) {
			foreach ( $entry_ids as $entry_id ) {
				$entry_model = new Forminator_Form_Entry_Model( $entry_id );

				// avoid overhead
				if ( ! isset( self::$custom_form_model_instances[ $entry_model->form_id ] ) ) {
					$model                                                      = Forminator_Custom_Form_Model::model()->load( $entry_model->form_id );
					self::$custom_form_model_instances[ $entry_model->form_id ] = $model;
				} else {
					$model = self::$custom_form_model_instances[ $entry_model->form_id ];
				}

				$data = array();
				if ( is_object( $model ) ) {
					$mappers = self::get_custom_form_export_mappers( $model );
					foreach ( $mappers as $mapper ) {
						//its from model's property
						if ( isset( $mapper['property'] ) ) {
							if ( property_exists( $entry_model, $mapper['property'] ) ) {
								$property = $mapper['property'];
								// casting property to string
								$data[] = array(
									'name'  => $mapper['label'],
									'value' => (string) $entry_model->$property,
								);
							} else {
								$data[] = array(
									'name'  => $mapper['label'],
									'value' => '',
								);
							}

						} elseif ( isset( $mapper['meta_property'] ) ) {
							if ( isset( $entry_model->meta_data[ $mapper['meta_property'] ] ) ) {
								$entry_meta_data_val = $entry_model->meta_data[ $mapper['meta_property'] ];
								$data[]              = array(
									'name'  => $mapper['label'],
									'value' => $entry_meta_data_val['value'],
								);
							} else {
								$data[] = array(
									'name'  => $mapper['label'],
									'value' => '',
								);
							}
						} else {
							// meta_key based
							$meta_value = $entry_model->get_meta( $mapper['meta_key'], '' );
							if ( ! isset( $mapper['sub_metas'] ) ) {
								$data[] = array(
									'name'  => $mapper['label'],
									'value' => Forminator_Form_Entry_Model::meta_value_to_string( $mapper['type'], $meta_value ),
								);
							} else {
								// sub_metas available
								foreach ( $mapper['sub_metas'] as $sub_meta ) {
									$sub_key = $sub_meta['key'];
									if ( isset( $meta_value[ $sub_key ] ) && ! empty( $meta_value[ $sub_key ] ) ) {
										$value      = $meta_value[ $sub_key ];
										$field_type = $mapper['type'] . '.' . $sub_key;
										$data[]     = array(
											'name'  => $sub_meta['label'],
											'value' => Forminator_Form_Entry_Model::meta_value_to_string( $field_type, $value ),
										);

									} else {
										$data[] = array(
											'name'  => $sub_meta['label'],
											'value' => '',
										);
									}
								}
							}
						}

					}

				} else {
					// fallback to dump the rows
					$data [] = array(
						'name'  => __( 'Entry ID', Forminator::DOMAIN ),
						'value' => '#' . $entry_model->entry_id,
					);
					$data [] = array(
						'name'  => __( 'Submission Date', Forminator::DOMAIN ),
						'value' => $entry_model->date_created_sql,
					);

					foreach ( $entry_model->meta_data as $key => $meta_datum ) {
						$meta_datum_value = $meta_datum['value'];
						$meta_datum_encoded = $meta_datum_value;
						// check nested array
						if ( is_array( $meta_datum_value ) ) {
							foreach ( $meta_datum_value as $value ) {
								if ( is_array( $value ) ) {
									$meta_datum_encoded = wp_json_encode( $meta_datum_value );
								}
							}
						}

						$data [] = array(
							'name'  => $key,
							'value' => Forminator_Form_Entry_Model::meta_value_to_string( '', $meta_datum_encoded, false ),
						);

					}
				}

				$data_to_export[] = array(
					'group_id'    => 'forminator_form_submissions',
					'group_label' => __( 'Forminator Form Submissions', Forminator::DOMAIN ),
					'item_id'     => 'entry-' . $entry_id,
					'data'        => $data,
				);
			}
		}

		/**
		 * Filter Export data for Custom form submission on tools.php?page=export_personal_data
		 *
		 * @since 1.0.6
		 *
		 * @param array  $data_to_export
		 * @param string $email_address
		 * @param array  $entry_ids
		 */
		$data_to_export = apply_filters( 'forminator_general_data_custom_form_submissions_export_data', $data_to_export, $email_address, $entry_ids );

		return array(
			'data' => $data_to_export,
			'done' => true,
		);
	}

	/**
	 * Get data mappers for retrieving entries meta
	 *
	 * @example [
	 *  [
	 *      'meta_key'  => 'FIELD_ID',
	 *      'label'     => 'LABEL',
	 *      'type'      => 'TYPE',
	 *      'sub_metas'      => [
	 *          [
	 *              'key'   => 'SUFFIX',
	 *              'label'   => 'LABEL',
	 *          ]
	 *      ],
	 *  ]
	 * ]
	 *
	 * @since   1.0.6
	 *
	 * @param Forminator_Custom_Form_Model|Forminator_Base_Form_Model $model
	 *
	 * @return array
	 */
	public static function get_custom_form_export_mappers( $model ) {
		/** @var  Forminator_Custom_Form_Model $model */
		$fields              = $model->get_fields();
		$ignored_field_types = Forminator_Form_Entry_Model::ignored_fields();

		/** @var  Forminator_Form_Field_Model $fields */
		$mappers = array(
			array(
				// read form model's meta property
				'property' => 'entry_id', // must be on export
				'label'    => __( 'Entry ID', Forminator::DOMAIN ),
				'type'     => 'entry_id',
			),
			array(
				// read form model's property
				'property' => 'date_created_sql', // must be on export
				'label'    => __( 'Submission Date', Forminator::DOMAIN ),
				'type'     => 'entry_date_created',
			),
			array(
				// read form model's meta property
				'meta_property' => '_forminator_user_ip', // must be on export
				'label'         => __( 'IP Address', Forminator::DOMAIN ),
				'type'          => '_forminator_user_ip',
			),
		);

		foreach ( $fields as $field ) {
			$field_type = $field->__get( 'type' );

			if ( in_array( $field_type, $ignored_field_types, true ) ) {
				continue;
			}

			// base mapper for every field
			$mapper             = array();
			$mapper['meta_key'] = $field->slug;
			$mapper['label']    = $field->get_label_for_entry();
			$mapper['type']     = $field_type;


			// fields that should be displayed as multi column (sub_metas)
			if ( 'name' === $field_type ) {
				$is_multiple_name = filter_var( $field->__get( 'multiple_name' ), FILTER_VALIDATE_BOOLEAN );
				if ( $is_multiple_name ) {
					$prefix_enabled      = filter_var( $field->__get( 'prefix' ), FILTER_VALIDATE_BOOLEAN );
					$first_name_enabled  = filter_var( $field->__get( 'fname' ), FILTER_VALIDATE_BOOLEAN );
					$middle_name_enabled = filter_var( $field->__get( 'mname' ), FILTER_VALIDATE_BOOLEAN );
					$last_name_enabled   = filter_var( $field->__get( 'lname' ), FILTER_VALIDATE_BOOLEAN );
					// at least one sub field enabled
					if ( $prefix_enabled || $first_name_enabled || $middle_name_enabled || $last_name_enabled ) {
						// sub metas
						$mapper['sub_metas'] = array();
						if ( $prefix_enabled ) {
							$default_label         = Forminator_Form_Entry_Model::translate_suffix( 'prefix' );
							$label                 = $field->__get( 'prefix_label' );
							$mapper['sub_metas'][] = array(
								'key'   => 'prefix',
								'label' => $mapper['label'] . ' - ' . ( $label ? $label : $default_label ),
							);
						}

						if ( $first_name_enabled ) {
							$default_label         = Forminator_Form_Entry_Model::translate_suffix( 'first-name' );
							$label                 = $field->__get( 'fname_label' );
							$mapper['sub_metas'][] = array(
								'key'   => 'first-name',
								'label' => $mapper['label'] . ' - ' . ( $label ? $label : $default_label ),
							);
						}

						if ( $middle_name_enabled ) {
							$default_label         = Forminator_Form_Entry_Model::translate_suffix( 'middle-name' );
							$label                 = $field->__get( 'mname_label' );
							$mapper['sub_metas'][] = array(
								'key'   => 'middle-name',
								'label' => $mapper['label'] . ' - ' . ( $label ? $label : $default_label ),
							);
						}
						if ( $last_name_enabled ) {
							$default_label         = Forminator_Form_Entry_Model::translate_suffix( 'last-name' );
							$label                 = $field->__get( 'lname_label' );
							$mapper['sub_metas'][] = array(
								'key'   => 'last-name',
								'label' => $mapper['label'] . ' - ' . ( $label ? $label : $default_label ),
							);
						}

					} else {
						// if no subfield enabled when multiple name remove mapper (means dont show it on export)
						$mapper = array();
					}
				}

			} elseif ( 'address' === $field_type ) {
				$street_enabled  = filter_var( $field->__get( 'street_address' ), FILTER_VALIDATE_BOOLEAN );
				$line_enabled    = filter_var( $field->__get( 'address_line' ), FILTER_VALIDATE_BOOLEAN );
				$city_enabled    = filter_var( $field->__get( 'address_city' ), FILTER_VALIDATE_BOOLEAN );
				$state_enabled   = filter_var( $field->__get( 'address_state' ), FILTER_VALIDATE_BOOLEAN );
				$zip_enabled     = filter_var( $field->__get( 'address_zip' ), FILTER_VALIDATE_BOOLEAN );
				$country_enabled = filter_var( $field->__get( 'address_country' ), FILTER_VALIDATE_BOOLEAN );
				if ( $street_enabled || $line_enabled || $city_enabled || $state_enabled || $zip_enabled || $country_enabled ) {
					$mapper['sub_metas'] = array();
					if ( $street_enabled ) {
						$default_label         = Forminator_Form_Entry_Model::translate_suffix( 'street_address' );
						$label                 = $field->__get( 'street_address_label' );
						$mapper['sub_metas'][] = array(
							'key'   => 'street_address',
							'label' => $mapper['label'] . ' - ' . ( $label ? $label : $default_label ),
						);
					}
					if ( $line_enabled ) {
						$default_label         = Forminator_Form_Entry_Model::translate_suffix( 'address_line' );
						$label                 = $field->__get( 'address_line_label' );
						$mapper['sub_metas'][] = array(
							'key'   => 'address_line',
							'label' => $mapper['label'] . ' - ' . ( $label ? $label : $default_label ),
						);
					}
					if ( $city_enabled ) {
						$default_label         = Forminator_Form_Entry_Model::translate_suffix( 'city' );
						$label                 = $field->__get( 'address_city_label' );
						$mapper['sub_metas'][] = array(
							'key'   => 'city',
							'label' => $mapper['label'] . ' - ' . ( $label ? $label : $default_label ),
						);
					}
					if ( $state_enabled ) {
						$default_label         = Forminator_Form_Entry_Model::translate_suffix( 'state' );
						$label                 = $field->__get( 'address_state_label' );
						$mapper['sub_metas'][] = array(
							'key'   => 'state',
							'label' => $mapper['label'] . ' - ' . ( $label ? $label : $default_label ),
						);
					}
					if ( $zip_enabled ) {
						$default_label         = Forminator_Form_Entry_Model::translate_suffix( 'zip' );
						$label                 = $field->__get( 'address_zip_label' );
						$mapper['sub_metas'][] = array(
							'key'   => 'zip',
							'label' => $mapper['label'] . ' - ' . ( $label ? $label : $default_label ),
						);
					}
					if ( $country_enabled ) {
						$default_label         = Forminator_Form_Entry_Model::translate_suffix( 'country' );
						$label                 = $field->__get( 'address_country_label' );
						$mapper['sub_metas'][] = array(
							'key'   => 'country',
							'label' => $mapper['label'] . ' - ' . ( $label ? $label : $default_label ),
						);
					}
				} else {
					// if no subfield enabled when multiple name remove mapper (means dont show it on export)
					$mapper = array();
				}
			}

			if ( ! empty( $mapper ) ) {
				$mappers[] = $mapper;
			}
		}

		return $mappers;
	}

	/**
	 * Eraser
	 *
	 * @since 1.0.6
	 *
	 * @param $email_address
	 * @param $page
	 *
	 * @return array
	 */
	public static function form_submissions_eraser( $email_address, $page ) {
		$form_submission_erasure_enabled = get_option( 'forminator_enable_erasure_request_erase_form_submissions', false );
		$form_submission_erasure_enabled = filter_var( $form_submission_erasure_enabled, FILTER_VALIDATE_BOOLEAN );

		$response = array(
			'items_removed'  => false,
			'items_retained' => false,
			'messages'       => array(),
			'done'           => true,
		);

		$entry_ids = Forminator_Form_Entry_Model::get_custom_form_entry_ids_by_email( $email_address );
		foreach ( $entry_ids as $entry_id ) {
			$entry_model = new Forminator_Form_Entry_Model( $entry_id );

			if ( ! empty( $entry_model->form_id ) ) {
				$custom_form = Forminator_Custom_Form_Model::model()->load( $entry_model->form_id );
				if ( $custom_form instanceof Forminator_Custom_Form_Model ) {
					$settings = $custom_form->settings;
					if ( isset( $settings['enable-submissions-erasure'] ) ) {
						$custom_erasure = filter_var( $settings['enable-submissions-erasure'], FILTER_VALIDATE_BOOLEAN );
						// IS OVERRIDDEN ?
						if ( $custom_erasure ) {
							if ( isset( $settings['submission-erasure-remove'] ) ) {
								// TRUE means remove!
								$form_submission_erasure_enabled = filter_var( $settings['submission-erasure-remove'], FILTER_VALIDATE_BOOLEAN );
							}
						}
					}
				}
			}

			$remove_form_submission = apply_filters( 'forminator_general_data_erase_form_submission', $form_submission_erasure_enabled, $entry_model );

			if ( $remove_form_submission ) {
				if ( ! empty( $entry_model->form_id ) ) {
					Forminator_Form_Entry_Model::delete_by_entry( $entry_model->form_id, $entry_id );
					$response['messages'][]    = sprintf( __( 'Removed form #%1$s submission #%2$s.', Forminator::DOMAIN ), $entry_model->form_id, $entry_id );
					$response['items_removed'] = true;
				}
			} else {
				$response['messages'][]     = sprintf( __( 'Form #%1$s submission #%2$s has been retained.', Forminator::DOMAIN ), $entry_model->form_id, $entry_id );
				$response['items_retained'] = true;
			}

		}

		return $response;
	}

	/**
	 * Clean up form submissions
	 *
	 * @since 1.0.6
	 *
	 * @return bool
	 */
	public function personal_data_cleanup() {
		$overridden_forms_privacy = get_option( 'forminator_form_privacy_settings', array() );

		// process overridden
		foreach ( $overridden_forms_privacy as $form_id => $retentions ) {
			$retain_number = (int) $retentions['submissions_retention_number'];
			$retain_unit   = $retentions['submissions_retention_unit'];
			if ( empty( $retain_number ) ) {
				// forever
				continue;
			}
			$possible_units = array(
				'days',
				'weeks',
				'months',
				'years',
			);

			if ( ! in_array( $retain_unit, $possible_units, true ) ) {
				continue;
			}

			$retain_time = strtotime( '-' . $retain_number . ' ' . $retain_unit, current_time( 'timestamp' ) );
			$retain_time = date_i18n( 'Y-m-d H:i:s', $retain_time );

			$entry_ids = Forminator_Form_Entry_Model::get_older_entry_ids_of_form_id( $form_id, $retain_time );

			foreach ( $entry_ids as $entry_id ) {
				$entry_model = new Forminator_Form_Entry_Model( $entry_id );
				Forminator_Form_Entry_Model::delete_by_entry( $entry_model->form_id, $entry_id );
			}

		}

		$this->cleanup_ip_address();

		$retain_number = get_option( 'forminator_retain_submissions_interval_number', 0 );
		$retain_unit   = get_option( 'forminator_retain_submissions_interval_unit', 'days' );

		if ( empty( $retain_number ) ) {
			return false;
		}

		$possible_units = array(
			'days',
			'weeks',
			'months',
			'years',
		);

		if ( ! in_array( $retain_unit, $possible_units, true ) ) {
			return false;
		}

		$retain_time = strtotime( '-' . $retain_number . ' ' . $retain_unit, current_time( 'timestamp' ) );
		$retain_time = date_i18n( 'Y-m-d H:i:s', $retain_time );

		$entry_ids = Forminator_Form_Entry_Model::get_older_entry_ids( 'custom-forms', $retain_time );

		foreach ( $entry_ids as $entry_id ) {
			$entry_model = new Forminator_Form_Entry_Model( $entry_id );
			if ( in_array( $entry_model->form_id, array_keys( $overridden_forms_privacy ) ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				// use overridden settings
				continue;
			}
			Forminator_Form_Entry_Model::delete_by_entry( $entry_model->form_id, $entry_id );
		}

		return true;
	}

	/**
	 * Cleanup IP Address based on settings
	 *
	 * @since 1.5.4
	 */
	public function cleanup_ip_address() {
		$retain_number = get_option( 'forminator_retain_ip_interval_number', 0 );
		$retain_unit   = get_option( 'forminator_retain_ip_interval_unit', 'days' );

		if ( empty( $retain_number ) ) {
			return false;
		}

		$possible_units = array(
			'days',
			'weeks',
			'months',
			'years',
		);

		if ( ! in_array( $retain_unit, $possible_units, true ) ) {
			return false;
		}

		$retain_time = strtotime( '-' . $retain_number . ' ' . $retain_unit, current_time( 'timestamp' ) );
		$retain_time = date_i18n( 'Y-m-d H:i:s', $retain_time );

		// todo : select only un-anonymized
		$entry_ids = Forminator_Form_Entry_Model::get_older_entry_ids( 'custom-forms', $retain_time );

		foreach ( $entry_ids as $entry_id ) {
			$entry_model = new Forminator_Form_Entry_Model( $entry_id );
			$this->anonymize_entry_model( $entry_model );
		}

		return true;
	}

	/**
	 * Anon Entry model IP
	 *
	 * @since 1.5.4
	 *
	 * @param Forminator_Form_Entry_Model $entry_model
	 */
	private function anonymize_entry_model( Forminator_Form_Entry_Model $entry_model ) {
		if ( isset( $entry_model->meta_data['_forminator_user_ip'] ) ) {
			$meta_id    = $entry_model->meta_data['_forminator_user_ip']['id'];
			$meta_value = $entry_model->meta_data['_forminator_user_ip']['value'];

			if ( function_exists( 'wp_privacy_anonymize_data' ) ) {
				$anon_value = wp_privacy_anonymize_data( 'ip', $meta_value );
			} else {
				$anon_value = '';
			}

			if ( $anon_value !== $meta_value ) {
				$entry_model->update_meta( $meta_id, '_forminator_user_ip', $anon_value );
			}

		}
	}
}
