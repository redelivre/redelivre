<?php

/**
 * Class Forminator_Addon_Googlesheet_Form_Hooks
 *
 * @since 1.0 Google Sheets Addon
 *
 */
class Forminator_Addon_Googlesheet_Form_Hooks extends Forminator_Addon_Form_Hooks_Abstract {

	/**
	 * Addon instance are auto available form abstract
	 * Its added here for development purpose,
	 * Auto-complete will resolve addon directly to `Google Sheets` instance instead of the abstract
	 * And its public properties can be exposed
	 *
	 * @since 1.0 Google Sheets Addon
	 * @var Forminator_Addon_Googlesheet
	 */
	protected $addon;

	/**
	 * Form Settings Instance
	 *
	 * @since 1.0 Google Sheets Addon
	 * @var Forminator_Addon_Googlesheet_Form_Settings | null
	 */
	protected $form_settings_instance;

	/**
	 * Forminator_Addon_Googlesheet_Form_Hooks constructor.
	 *
	 * @since 1.0 Google Sheets Addon
	 *
	 * @param Forminator_Addon_Abstract $addon
	 * @param                           $form_id
	 *
	 * @throws Forminator_Addon_Exception
	 */
	public function __construct( Forminator_Addon_Abstract $addon, $form_id ) {
		parent::__construct( $addon, $form_id );
		$this->_submit_form_error_message = __( 'Google Sheets failed to process submitted data. Please check your form and try again', Forminator::DOMAIN );
	}

	/**
	 * Save status of request sent and received for each connected Google Sheets
	 *
	 * @since 1.0 Google Sheets Addon
	 *
	 * @param array $submitted_data
	 * @param array $form_entry_fields
	 *
	 * @return array
	 */
	public function add_entry_fields( $submitted_data, $form_entry_fields = array() ) {

		$form_id                = $this->form_id;
		$form_settings_instance = $this->form_settings_instance;

		/**
		 * Filter Google Sheets submitted form data to be processed
		 *
		 * @since 1.2
		 *
		 * @param array                                      $submitted_data
		 * @param array                                      $form_entry_fields
		 * @param int                                        $form_id                current Form ID
		 * @param Forminator_Addon_Googlesheet_Form_Settings $form_settings_instance Google Sheets Addon Form Settings instance
		 */
		$submitted_data = apply_filters(
			'forminator_addon_googlesheet_form_submitted_data',
			$submitted_data,
			$form_entry_fields,
			$form_id,
			$form_settings_instance
		);

		/**
		 * Filter current form entry fields data to be processed by Google Sheets
		 *
		 * @since 1.2
		 *
		 * @param array                                      $form_entry_fields
		 * @param array                                      $submitted_data
		 * @param int                                        $form_id                current Form ID
		 * @param Forminator_Addon_Googlesheet_Form_Settings $form_settings_instance Google Sheets Addon Form Settings instance
		 */
		$form_entry_fields = apply_filters(
			'forminator_addon_googlesheet_form_entry_fields',
			$form_entry_fields,
			$submitted_data,
			$form_id,
			$form_settings_instance
		);

		forminator_addon_maybe_log( __METHOD__, $submitted_data );

		$addon_setting_values = $this->form_settings_instance->get_form_settings_values();

		$data = array();

		/**
		 * Fires before create row on Google Sheets
		 *
		 * @since 1.2
		 *
		 * @param int                                        $form_id                current Form ID
		 * @param array                                      $submitted_data
		 * @param Forminator_Addon_Googlesheet_Form_Settings $form_settings_instance Google Sheets Addon Form Settings instance
		 */
		do_action( 'forminator_addon_googlesheet_before_create_row', $form_id, $submitted_data, $form_settings_instance );

		foreach ( $addon_setting_values as $key => $addon_setting_value ) {
			// save it on entry field, with name `status-$MULTI_ID`, and value is the return result on sending data to Google Sheets
			if ( $form_settings_instance->is_multi_form_settings_complete( $key ) ) {
				// exec only on completed connection
				$data[] = array(
					'name'  => 'status-' . $key,
					'value' => $this->get_status_on_create_row( $key, $submitted_data, $addon_setting_value, $form_entry_fields ),
				);
			}

		}

		$entry_fields = $data;
		/**
		 * Filter Google Sheets entry fields to be saved to entry model
		 *
		 * @since 1.2
		 *
		 * @param array                                      $entry_fields
		 * @param int                                        $form_id                current Form ID
		 * @param array                                      $submitted_data
		 * @param array                                      $form_entry_fields
		 * @param Forminator_Addon_Googlesheet_Form_Settings $form_settings_instance Google Sheets Addon Form Settings instance
		 */
		$data = apply_filters(
			'forminator_addon_googlesheet_entry_fields',
			$entry_fields,
			$form_id,
			$submitted_data,
			$form_entry_fields,
			$form_settings_instance
		);

		return $data;

	}

	/**
	 * Get status on create Google Sheets row
	 *
	 * @since 1.0 Google Sheets Addon
	 *
	 * @param string $connection_id
	 * @param array  $submitted_data
	 * @param array  $connection_settings
	 * @param array  $form_entry_fields
	 *
	 * @return array `is_sent` true means its success send data to Google Sheets, false otherwise
	 */
	public function get_status_on_create_row( $connection_id, $submitted_data, $connection_settings, $form_entry_fields ) {
		// initialize as null
		$api = null;

		$form_id                = $this->form_id;
		$form_settings_instance = $this->form_settings_instance;

		try {
			/**
			 * Fires before checking and modifying headers row of googlesheet
			 *
			 * @since 1.2
			 *
			 * @param array                                      $connection_settings
			 * @param int                                        $form_id                current Form ID
			 * @param array                                      $submitted_data
			 * @param array                                      $form_entry_fields
			 * @param Forminator_Addon_Googlesheet_Form_Settings $form_settings_instance Google Sheets Addon Form Settings instance
			 */
			do_action( 'forminator_addon_googlesheet_before_prepare_sheet_headers', $connection_settings, $form_id, $submitted_data, $form_entry_fields, $form_settings_instance );

			// prepare headers
			$header_fields = $this->get_sheet_headers( $connection_settings['file_id'] );

			/**
			 * Filter Sheet headers fields that will be used to map the entrt rows
			 *
			 * @since 1.2
			 *
			 * @param array                                      $header_fields          sheet headers
			 * @param array                                      $connection_settings
			 * @param int                                        $form_id                current Form ID
			 * @param array                                      $submitted_data
			 * @param array                                      $form_entry_fields
			 * @param Forminator_Addon_Googlesheet_Form_Settings $form_settings_instance Google Sheets Addon Form Settings instance
			 */
			$header_fields = apply_filters(
				'forminator_addon_googlesheet_sheet_headers',
				$header_fields,
				$connection_settings,
				$form_id,
				$submitted_data,
				$form_entry_fields,
				$form_settings_instance
			);

			/**
			 * Fires after headers row of googlesheet checked and modified
			 *
			 * @since 1.2
			 *
			 * @param array                                      $header_fields          sheet headers
			 * @param array                                      $connection_settings
			 * @param int                                        $form_id                current Form ID
			 * @param array                                      $submitted_data
			 * @param array                                      $form_entry_fields
			 * @param Forminator_Addon_Googlesheet_Form_Settings $form_settings_instance Google Sheets Addon Form Settings instance
			 */
			do_action( 'forminator_addon_googlesheet_after_prepare_sheet_headers', $header_fields, $connection_settings, $form_id, $submitted_data, $form_entry_fields, $form_settings_instance );

			$keyed_form_entry_fields = array();
			foreach ( $form_entry_fields as $form_entry_field ) {
				if ( isset( $form_entry_field['name'] ) ) {
					$keyed_form_entry_fields[ $form_entry_field['name'] ] = array(
						'id'    => $form_entry_field['name'],
						'value' => $form_entry_field['value'],
					);
				}

			}
			$form_entry_fields = $keyed_form_entry_fields;

			// all avail fields on library
			$fields      = forminator_fields_to_array();
			$field_types = array_keys( $fields );

			// sort by length, so stripos will work by traverse from longest field type first
			$field_types_strlen = array_map( 'strlen', $field_types );
			array_multisort( $field_types_strlen, $field_types );
			$field_types = array_reverse( $field_types );

			$values = array();
			foreach ( $header_fields as $element_id => $header_field ) {
				$field_type = '';

				foreach ( $field_types as $type ) {
					if ( false !== stripos( $element_id, $type . '-' ) ) {
						$field_type = $type;
						break;
					}
				}

				$meta_value = array();
				// take from entry fields (to be saved)
				if ( isset( $form_entry_fields[ $element_id ] ) ) {
					$meta_value = $form_entry_fields[ $element_id ]['value'];
				} elseif ( isset( $submitted_data[ $element_id ] ) ) {
					// fallback to submitted_data
					$meta_value = $submitted_data[ $element_id ];
				}

				forminator_addon_maybe_log( __METHOD__, $field_type, $meta_value );

				$form_value = Forminator_Form_Entry_Model::meta_value_to_string( $field_type, $meta_value, false );

				$value     = new Google_Service_Sheets_ExtendedValue();
				$cell_data = new Google_Service_Sheets_CellData();
				$value->setStringValue( $form_value );
				$cell_data->setUserEnteredValue( $value );
				$values[] = $cell_data;
			}

			// Build the RowData
			$row_data = new Google_Service_Sheets_RowData();
			$row_data->setValues( $values );

			// Prepare the request
			$append_request = new Google_Service_Sheets_AppendCellsRequest();
			$append_request->setSheetId( 0 );
			$append_request->setRows( $row_data );
			$append_request->setFields( 'userEnteredValue' );

			// Set the request
			$request = new Google_Service_Sheets_Request();
			$request->setAppendCells( $append_request );
			// Add the request to the requests array
			$requests   = array();
			$requests[] = $request;

			// Prepare the update
			$batch_update_request = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest(
				array(

					'requests' => $requests,
				)
			);

			$google_client = $this->addon->get_google_client();
			$google_client->setAccessToken( $this->addon->get_client_access_token() );
			$spreadsheet_service = new Google_Service_Sheets( $google_client );
			$spreadsheet_service->spreadsheets->batchUpdate( $connection_settings['file_id'], $batch_update_request );

			if ( $google_client->getAccessToken() !== $this->addon->get_client_access_token() ) {
				$this->addon->update_client_access_token( $google_client->getAccessToken() );
			}
			forminator_addon_maybe_log( __METHOD__, 'Success Send Data' );

			return array(
				'is_sent'         => true,
				'connection_name' => $connection_settings['name'],
				'description'     => __( 'Successfully send data to Google Sheets', Forminator::DOMAIN ),
			);

		} catch ( Google_Exception $e ) {
			forminator_addon_maybe_log( __METHOD__, 'Failed to Send to Google Sheets' );

			return array(
				'is_sent'         => false,
				'description'     => $e->getMessage(),
				'connection_name' => $connection_settings['name'],
			);
		} catch ( Forminator_Addon_Googlesheet_Exception $e ) {
			forminator_addon_maybe_log( __METHOD__, 'Failed to Send to Google Sheets' );

			return array(
				'is_sent'         => false,
				'description'     => $e->getMessage(),
				'connection_name' => $connection_settings['name'],
			);
		}
	}

	/**
	 * Prepare headers of spreadsheet
	 *
	 * @param $file_id
	 *
	 * @return array
	 * @throws Forminator_Addon_Googlesheet_Exception
	 */
	public function get_sheet_headers( $file_id ) {
		$form_fields = $this->form_settings_instance->get_form_fields();

		$google_client = $this->addon->get_google_client();
		$google_client->setAccessToken( $this->addon->get_client_access_token() );

		$spreadsheet_service = new Google_Service_Sheets( $google_client );
		$spreadsheet         = $spreadsheet_service->spreadsheets->get( $file_id );
		$sheets              = $spreadsheet->getSheets();

		if ( ! isset( $sheets[0] ) || ! isset( $sheets[0]->properties ) ) {
			throw new Forminator_Addon_Googlesheet_Exception( __( 'No sheet found', Forminator::DOMAIN ) );
		}
		$sheet_id = $sheets[0]->properties->sheetId;

		if ( ! isset( $sheets[0]->properties->title ) || empty( $sheets[0]->properties->title ) ) {
			throw new Forminator_Addon_Googlesheet_Exception( __( 'Sheet title not found', Forminator::DOMAIN ) );
		}

		if ( ! isset( $sheets[0]->properties->gridProperties ) || ! isset( $sheets[0]->properties->gridProperties->columnCount ) ) {
			throw new Forminator_Addon_Googlesheet_Exception( __( 'Failed to get column count of the sheet', Forminator::DOMAIN ) );
		}

		$sheet_title        = $sheets[0]->properties->title;
		$sheet_column_count = $sheets[0]->properties->gridProperties->columnCount;

		$headers_range = $sheet_title . '!' . '1:1';
		$header_rows   = $spreadsheet_service->spreadsheets_values->get(
			$spreadsheet->getSpreadsheetId(),
			$headers_range
		);

		$values = $header_rows->getValues();

		forminator_addon_maybe_log( __METHOD__, '$sheet_column_count', $sheet_column_count );

		$header_fields = array();

		$column_number  = 1;
		$columns_filled = 0;
		if ( isset( $values[0] ) && is_array( $values[0] ) ) {
			foreach ( $values[0] as $value ) {
				$key_range = $sheet_title . '!' . self::column_number_to_letter( $column_number ) . '1';
				// forminator header field format = 'FIELD-label|field-id'
				$header_values                = explode( '|', $value );
				$element_id                   = end( $header_values );
				$header_fields[ $element_id ] = array(
					'range' => $key_range,
					'value' => $value,
				);
				$column_number ++;
				$columns_filled ++;
			}
		}

		$new_column_count = 0;
		$update_bodies    = array();
		foreach ( $form_fields as $form_field ) {
			$element_id            = $form_field['element_id'];
			$expected_header_value = $form_field['field_label'] . '|' . $element_id;
			if ( ! in_array( $element_id, array_keys( $header_fields ), true ) ) {
				//add
				$new_range = $sheet_title . '!' . self::column_number_to_letter( $column_number ) . '1';

				// update headers map
				$header_fields[ $element_id ] = array(
					'range' => $new_range,
					'value' => $expected_header_value,
				);

				// increment for next usage
				$column_number ++;
				$update_body = new Google_Service_Sheets_ValueRange();
				$update_body->setRange( $new_range );
				$update_body->setValues( array( array( $expected_header_value ) ) );
				$update_bodies[] = $update_body;
				$new_column_count ++;
			} else {
				$header_field = $header_fields[ $element_id ];
				if ( $expected_header_value !== $header_field['value'] ) {
					// update headers map
					$header_fields[ $element_id ]['value'] = $expected_header_value;

					// update sheet
					$update_body = new Google_Service_Sheets_ValueRange();
					$update_body->setRange( $header_field['range'] );
					$update_body->setValues( array( array( $expected_header_value ) ) );
					$update_bodies[] = $update_body;
				}
			}
		}

		//calc column to be added
		$total_column_needed = $columns_filled + $new_column_count;
		$new_column_needed   = $total_column_needed - $sheet_column_count;
		if ( $new_column_needed > 0 ) {
			$dimension_range = new Google_Service_Sheets_DimensionRange();
			$dimension_range->setSheetId( 0 );
			$dimension_range->setDimension( 'COLUMNS' );
			$dimension_range->setStartIndex( $sheet_column_count );
			$dimension_range->setEndIndex( $total_column_needed );

			$insert_dimension = new Google_Service_Sheets_InsertDimensionRequest();
			$insert_dimension->setRange( $dimension_range );
			$insert_dimension->setInheritFromBefore( true );

			$request = new Google_Service_Sheets_Request();
			$request->setInsertDimension( $insert_dimension );

			$request_body = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest();
			$request_body->setRequests( array( $request ) );

			$spreadsheet_service->spreadsheets->batchUpdate( $file_id, $request_body );
		}
		if ( ! empty( $update_bodies ) ) {
			$request_body = new Google_Service_Sheets_BatchUpdateValuesRequest();
			$request_body->setData( $update_bodies );
			$request_body->setValueInputOption( 'RAW' );
			$spreadsheet_service->spreadsheets_values->batchUpdate( $file_id, $request_body );
		}

		$grid_properties = new Google_Service_Sheets_GridProperties();
		$grid_properties->setFrozenRowCount( 1 );

		$sheet_properties = new Google_Service_Sheets_SheetProperties();
		$sheet_properties->setSheetId( 0 );
		$sheet_properties->setGridProperties( $grid_properties );

		$update_properties = new Google_Service_Sheets_UpdateSheetPropertiesRequest();
		$update_properties->setProperties( $sheet_properties );
		$update_properties->setFields( 'gridProperties(frozenRowCount)' );

		$request = new Google_Service_Sheets_Request();
		$request->setUpdateSheetProperties( $update_properties );

		$request_body = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest();
		$request_body->setRequests( array( $request ) );

		$spreadsheet_service->spreadsheets->batchUpdate( $file_id, $request_body );

		if ( $google_client->getAccessToken() !== $this->addon->get_client_access_token() ) {
			$this->addon->update_client_access_token( $google_client->getAccessToken() );
		}

		return $header_fields;

	}

	/**
	 * It wil add new row on entry table of submission page, with couple of subentries
	 * subentries included are defined in @see Forminator_Addon_Googlesheet_Form_Hooks::get_additional_entry_item()
	 *
	 * @since 1.0 Google Sheets Addon
	 *
	 * @param Forminator_Form_Entry_Model $entry_model
	 * @param                             $addon_meta_data
	 *
	 * @return array
	 */
	public function on_render_entry( Forminator_Form_Entry_Model $entry_model, $addon_meta_data ) {

		$form_id                = $this->form_id;
		$form_settings_instance = $this->form_settings_instance;

		/**
		 *
		 * Filter Google Sheets metadata that previously saved on db to be processed
		 *
		 * @since 1.2
		 *
		 * @param array                                      $addon_meta_data
		 * @param int                                        $form_id                current Form ID
		 * @param Forminator_Addon_Googlesheet_Form_Settings $form_settings_instance Google Sheets Addon Form Settings instance
		 */
		$addon_meta_data = apply_filters(
			'forminator_addon_googlesheet_metadata',
			$addon_meta_data,
			$form_id,
			$form_settings_instance
		);


		$addon_meta_datas = $addon_meta_data;
		if ( ! isset( $addon_meta_data[0] ) || ! is_array( $addon_meta_data[0] ) ) {
			return array();
		}

		return $this->on_render_entry_multi_connection( $addon_meta_datas );

	}

	/**
	 * Loop through addon meta data on multiple Google Sheets setup(s)
	 *
	 * @since 1.0 Google Sheets Addon
	 *
	 * @param $addon_meta_datas
	 *
	 * @return array
	 */
	private function on_render_entry_multi_connection( $addon_meta_datas ) {
		$additional_entry_item = array();
		foreach ( $addon_meta_datas as $addon_meta_data ) {
			$additional_entry_item[] = $this->get_additional_entry_item( $addon_meta_data );
		}

		return $additional_entry_item;

	}

	/**
	 * Format additional entry item as label and value arrays
	 *
	 * - Integration Name : its defined by user when they adding Google Sheets integration on their form
	 * - Sent To Google Sheets : will be Yes/No value, that indicates whether sending data to Google Sheets API was successful
	 * - Info : Text that are generated by addon when building and sending data to Google Sheets @see Forminator_Addon_Googlesheet_Form_Hooks::add_entry_fields()
	 *
	 * @param $addon_meta_data
	 *
	 * @since 1.0 Google Sheets Addon
	 * @return array
	 */
	private function get_additional_entry_item( $addon_meta_data ) {

		if ( ! isset( $addon_meta_data['value'] ) || ! is_array( $addon_meta_data['value'] ) ) {
			return array();
		}
		$status                = $addon_meta_data['value'];
		$additional_entry_item = array(
			'label' => __( 'Google Sheets Integration', Forminator::DOMAIN ),
			'value' => '',
		);


		$sub_entries = array();
		if ( isset( $status['connection_name'] ) ) {
			$sub_entries[] = array(
				'label' => __( 'Integration Name', Forminator::DOMAIN ),
				'value' => $status['connection_name'],
			);
		}

		if ( isset( $status['is_sent'] ) ) {
			$is_sent       = true === $status['is_sent'] ? __( 'Yes', Forminator::DOMAIN ) : __( 'No', Forminator::DOMAIN );
			$sub_entries[] = array(
				'label' => __( 'Sent To Google Sheets', Forminator::DOMAIN ),
				'value' => $is_sent,
			);
		}

		if ( isset( $status['description'] ) ) {
			$sub_entries[] = array(
				'label' => __( 'Info', Forminator::DOMAIN ),
				'value' => $status['description'],
			);
		}

		$additional_entry_item['sub_entries'] = $sub_entries;

		// return single array
		return $additional_entry_item;
	}

	/**
	 * Google Sheets will add a column on the title/header row
	 * its called `Google Sheets Info` which can be translated on forminator lang
	 *
	 * @since 1.0 Google Sheets Addon
	 * @return array
	 */
	public function on_export_render_title_row() {

		$export_headers = array(
			'info' => __( 'Google Sheets Info', Forminator::DOMAIN ),
		);

		$form_id                = $this->form_id;
		$form_settings_instance = $this->form_settings_instance;

		/**
		 * Filter Google Sheets headers on export file
		 *
		 * @since 1.2
		 *
		 * @param array                                      $export_headers         headers to be displayed on export file
		 * @param int                                        $form_id                current Form ID
		 * @param Forminator_Addon_Googlesheet_Form_Settings $form_settings_instance Google Sheets Addon Form Settings instance
		 */
		$export_headers = apply_filters(
			'forminator_addon_googlesheet_export_headers',
			$export_headers,
			$form_id,
			$form_settings_instance
		);

		return $export_headers;
	}

	/**
	 * Google Sheets will add a column that give user information whether sending data to Google Sheets successfully or not
	 * It will only add one column even its multiple connection, every connection will be separated by comma
	 *
	 * @since 1.0 Google Sheets Addon
	 *
	 * @param Forminator_Form_Entry_Model $entry_model
	 * @param                             $addon_meta_data
	 *
	 * @return array
	 */
	public function on_export_render_entry( Forminator_Form_Entry_Model $entry_model, $addon_meta_data ) {

		$form_id                = $this->form_id;
		$form_settings_instance = $this->form_settings_instance;

		/**
		 *
		 * Filter Google Sheets metadata that previously saved on db to be processed
		 *
		 * @since 1.2
		 *
		 * @param array                                      $addon_meta_data
		 * @param int                                        $form_id                current Form ID
		 * @param Forminator_Addon_Googlesheet_Form_Settings $form_settings_instance Google Sheets Addon Form Settings instance
		 */
		$addon_meta_data = apply_filters(
			'forminator_addon_googlesheet_metadata',
			$addon_meta_data,
			$form_id,
			$form_settings_instance
		);

		$export_columns = array(
			'info' => $this->get_from_addon_meta_data( $addon_meta_data, 'description', '' ),
		);

		/**
		 * Filter Google Sheets columns to be displayed on export submissions
		 *
		 * @since 1.2
		 *
		 * @param array                                      $export_columns         column to be exported
		 * @param int                                        $form_id                current Form ID
		 * @param Forminator_Form_Entry_Model                $entry_model            Form Entry Model
		 * @param array                                      $addon_meta_data        meta data saved by addon on entry fields
		 * @param Forminator_Addon_Googlesheet_Form_Settings $form_settings_instance Google Sheets Addon Form Settings instance
		 */
		$export_columns = apply_filters(
			'forminator_addon_googlesheet_export_columns',
			$export_columns,
			$form_id,
			$entry_model,
			$addon_meta_data,
			$form_settings_instance
		);

		return $export_columns;
	}

	/**
	 * Get Addon meta data, will be recursive if meta data is multiple because of multiple connection added
	 *
	 * @since 1.0 Google Sheets Addon
	 *
	 * @param        $addon_meta_data
	 * @param        $key
	 * @param string $default
	 *
	 * @return string
	 */
	private function get_from_addon_meta_data( $addon_meta_data, $key, $default = '' ) {
		$addon_meta_datas = $addon_meta_data;
		if ( ! isset( $addon_meta_data[0] ) || ! is_array( $addon_meta_data[0] ) ) {
			return $default;
		}

		$addon_meta_data = $addon_meta_data[0];

		// make sure its `status`, because we only add this
		if ( 'status' !== $addon_meta_data['name'] ) {
			if ( stripos( $addon_meta_data['name'], 'status-' ) === 0 ) {
				$meta_data = array();
				foreach ( $addon_meta_datas as $addon_meta_data ) {
					// make it like single value so it will be processed like single meta data
					$addon_meta_data['name'] = 'status';

					// add it on an array for next recursive process
					$meta_data[] = $this->get_from_addon_meta_data( array( $addon_meta_data ), $key, $default );
				}

				return implode( ', ', $meta_data );
			}

			return $default;

		}

		if ( ! isset( $addon_meta_data['value'] ) || ! is_array( $addon_meta_data['value'] ) ) {
			return $default;
		}
		$status = $addon_meta_data['value'];
		if ( isset( $status[ $key ] ) ) {
			$connection_name = '';
			if ( 'connection_name' !== $key ) {
				if ( isset( $status['connection_name'] ) ) {
					$connection_name = '[' . $status['connection_name'] . '] ';
				}
			}

			return $connection_name . $status[ $key ];
		}

		return $default;
	}

	/**
	 * Convert column number to letter format for spreadsheet
	 *
	 * start from 1
	 *
	 * @param $int
	 *
	 * @return string
	 */
	public static function column_number_to_letter( $int ) {
		$chars = array(
			'A',
			'B',
			'C',
			'D',
			'E',
			'F',
			'G',
			'H',
			'I',
			'J',
			'K',
			'L',
			'M',
			'N',
			'O',
			'P',
			'Q',
			'R',
			'S',
			'T',
			'U',
			'V',
			'W',
			'X',
			'Y',
			'Z',
		);

		/**
		 * 1 = A
		 * 27 = AA
		 */
		$base               = 26;
		$temp_number        = $int;
		$output_column_name = '';
		while ( $temp_number > 0 ) {
			$position = $temp_number % $base;
			if ( 0 === $position ) {
				$output_column_name = 'Z' . $output_column_name;
			} else {
				if ( $position > 0 ) {
					$output_column_name = $chars[ $position - 1 ] . $output_column_name;
				} else {
					$output_column_name = $chars[0] . $output_column_name;
				}
			}
			$temp_number --;
			$temp_number = floor( $temp_number / $base );
		}

		return $output_column_name;
	}
}
