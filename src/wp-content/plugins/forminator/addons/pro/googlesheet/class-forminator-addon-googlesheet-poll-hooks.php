<?php

/**
 * Class Forminator_Addon_Googlesheet_Poll_Hooks
 *
 * @since 1.6.1
 *
 */
class Forminator_Addon_Googlesheet_Poll_Hooks extends Forminator_Addon_Poll_Hooks_Abstract {

	/**
	 * Addon instance are auto available form abstract
	 * Its added here for development purpose,
	 * Auto-complete will resolve addon directly to `Google Sheets` instance instead of the abstract
	 * And its public properties can be exposed
	 *
	 * @since 1.6.1
	 * @var Forminator_Addon_Googlesheet
	 */
	protected $addon;

	/**
	 * Poll Settings Instance
	 *
	 * @since 1.6.1
	 * @var Forminator_Addon_Googlesheet_Poll_Settings | null
	 */
	protected $poll_settings_instance;

	/**
	 * Google sheet column titles
	 */
	const GSHEET_ANSWER_COLUMN_NAME = 'Answer';
	const GSHEET_EXTRA_COLUMN_NAME  = 'Extra';

	/**
	 * Forminator_Addon_Googlesheet_Poll_Hooks constructor.
	 *
	 * @since 1.6.1
	 *
	 * @param Forminator_Addon_Abstract $addon
	 * @param                           $poll_id
	 *
	 * @throws Forminator_Addon_Exception
	 */
	public function __construct( Forminator_Addon_Abstract $addon, $poll_id ) {
		parent::__construct( $addon, $poll_id );
		$this->_submit_poll_error_message = __( 'Google Sheets failed to process submitted data. Please check your poll and try again', Forminator::DOMAIN );
	}

	/**
	 * Save status of request sent and received for each connected Google Sheets
	 *
	 * @since 1.6.1
	 *
	 * @param array $submitted_data
	 * @param array $current_entry_fields
	 *
	 * @return array
	 */
	public function add_entry_fields( $submitted_data, $current_entry_fields = array() ) {
		$poll_id                = $this->poll_id;
		$poll_settings_instance = $this->poll_settings_instance;

		/**
		 * Filter Google Sheets submitted poll data to be processed
		 *
		 * @since 1.6.1
		 *
		 * @param array                                      $submitted_data
		 * @param array                                      $current_entry_fields
		 * @param int                                        $poll_id                current Poll ID
		 * @param Forminator_Addon_Googlesheet_Poll_Settings $poll_settings_instance Google Sheets Addon poll Settings instance
		 */
		$submitted_data = apply_filters(
			'forminator_addon_googlesheet_poll_submitted_data',
			$submitted_data,
			$current_entry_fields,
			$poll_id,
			$poll_settings_instance
		);

		/**
		 * Filter current poll entry fields data to be processed by Google Sheets
		 *
		 * @since 1.6.1
		 *
		 * @param array                                      $current_entry_fields
		 * @param array                                      $submitted_data
		 * @param int                                        $poll_id                current Poll ID
		 * @param Forminator_Addon_Googlesheet_Poll_Settings $poll_settings_instance Google Sheets Addon Poll Settings instance
		 */
		$current_entry_fields = apply_filters(
			'forminator_addon_googlesheet_poll_entry_fields',
			$current_entry_fields,
			$submitted_data,
			$poll_id,
			$poll_settings_instance
		);

		forminator_addon_maybe_log( __METHOD__, $submitted_data );

		$addon_setting_values = $this->poll_settings_instance->get_poll_settings_values();

		$data = array();

		/**
		 * Fires before poll create row on Google Sheets
		 *
		 * @since 1.6.1
		 *
		 * @param int                                        $poll_id                current Poll ID
		 * @param array                                      $submitted_data
		 * @param Forminator_Addon_Googlesheet_Poll_Settings $poll_settings_instance Google Sheets Addon Poll Settings instance
		 */
		do_action( 'forminator_addon_googlesheet_poll_before_create_row', $poll_id, $submitted_data, $poll_settings_instance );

		foreach ( $addon_setting_values as $key => $addon_setting_value ) {
			// save it on entry field, with name `status-$MULTI_ID`, and value is the return result on sending data to Google Sheets
			if ( $poll_settings_instance->is_multi_poll_settings_complete( $key ) ) {
				// exec only on completed connection
				$data[] = array(
					'name'  => 'status-' . $key,
					'value' => $this->get_status_on_create_row( $key, $submitted_data, $addon_setting_value, $current_entry_fields ),
				);
			}
		}

		$entry_fields = $data;
		/**
		 * Filter Google Sheets entry fields to be saved to entry model
		 *
		 * @since 1.6.1
		 *
		 * @param array                                      $entry_fields
		 * @param int                                        $poll_id                current Poll ID
		 * @param array                                      $submitted_data
		 * @param array                                      $current_entry_fields
		 * @param Forminator_Addon_Googlesheet_Poll_Settings $poll_settings_instance Google Sheets Addon Poll Settings instance
		 */
		$data = apply_filters(
			'forminator_addon_poll_googlesheet_entry_fields',
			$entry_fields,
			$poll_id,
			$submitted_data,
			$current_entry_fields,
			$poll_settings_instance
		);

		return $data;
	}

	/**
	 * Get status on create Google Sheets row
	 *
	 * @since 1.6.1
	 *
	 * @param string $connection_id
	 * @param array  $submitted_data
	 * @param array  $connection_settings
	 * @param array  $poll_entry_fields
	 *
	 * @return array `is_sent` true means its success send data to Google Sheets, false otherwise
	 */
	public function get_status_on_create_row( $connection_id, $submitted_data, $connection_settings, $poll_entry_fields ) {
		// initialize as null
		$api = null;

		$poll_id                = $this->poll_id;
		$poll_settings_instance = $this->poll_settings_instance;

		try {

			/**
			 * Fires before checking and modifying headers row of googlesheet
			 *
			 * @since 1.6.1
			 *
			 * @param array                                      $connection_settings
			 * @param int                                        $poll_id                current Poll ID
			 * @param array                                      $submitted_data
			 * @param array                                      $poll_entry_fields
			 * @param Forminator_Addon_Googlesheet_Poll_Settings $poll_settings_instance Google Sheets Addon Poll Settings instance
			 */
			do_action( 'forminator_addon_poll_googlesheet_before_prepare_sheet_headers', $connection_settings, $poll_id, $submitted_data, $poll_entry_fields, $poll_settings_instance );

			// prepare headers
			$header_fields = $this->get_sheet_headers( $connection_settings['file_id'] );

			/**
			 * Filter Sheet headers fields that will be used to map the entry rows
			 *
			 * @since 1.6.1
			 *
			 * @param array                                      $header_fields          sheet headers
			 * @param array                                      $connection_settings
			 * @param int                                        $poll_id                current Poll ID
			 * @param array                                      $submitted_data
			 * @param array                                      $poll_entry_fields
			 * @param Forminator_Addon_Googlesheet_Poll_Settings $poll_settings_instance Google Sheets Addon Poll Settings instance
			 */
			$header_fields = apply_filters(
				'forminator_addon_poll_googlesheet_sheet_headers',
				$header_fields,
				$connection_settings,
				$poll_id,
				$submitted_data,
				$poll_entry_fields,
				$poll_settings_instance
			);

			/**
			 * Fires after headers row of googlesheet checked and modified
			 *
			 * @since 1.6.1
			 *
			 * @param array                                      $header_fields          sheet headers
			 * @param array                                      $connection_settings
			 * @param int                                        $poll_id                current Poll ID
			 * @param array                                      $submitted_data
			 * @param array                                      $poll_entry_fields
			 * @param Forminator_Addon_Googlesheet_Poll_Settings $poll_settings_instance Google Sheets Addon Poll Settings instance
			 */
			do_action( 'forminator_addon_poll_googlesheet_after_prepare_sheet_headers', $header_fields, $connection_settings, $poll_id, $submitted_data, $poll_entry_fields, $poll_settings_instance );

			$values = array();

			$answer = '';
			$extra  = '';
			foreach ( $poll_entry_fields as $poll_entry_field ) {
				$key   = isset( $poll_entry_field['name'] ) ? $poll_entry_field['name'] : '';
				$value = isset( $poll_entry_field['value'] ) ? $poll_entry_field['value'] : '';
				if ( stripos( $key, 'answer-' ) === 0 ) {
					$answer = $value;
				} elseif ( 'extra' === $key ) {
					$extra = $value;
				}
			}
			forminator_addon_maybe_log( __METHOD__, $poll_entry_fields, $answer, $extra );

			foreach ( $header_fields as $column_name => $header_field ) {
				if ( self::GSHEET_ANSWER_COLUMN_NAME === $column_name ) {
					$value     = new Forminator_Google_Service_Sheets_ExtendedValue();
					$cell_data = new Forminator_Google_Service_Sheets_CellData();
					$value->setStringValue( $answer );
					$cell_data->setUserEnteredValue( $value );
					$values[] = $cell_data;
				} elseif ( self::GSHEET_EXTRA_COLUMN_NAME === $column_name ) {
					$value     = new Forminator_Google_Service_Sheets_ExtendedValue();
					$cell_data = new Forminator_Google_Service_Sheets_CellData();
					$value->setStringValue( $extra );
					$cell_data->setUserEnteredValue( $value );
					$values[] = $cell_data;
				} else {
					// unknown column, set empty
					$value     = new Forminator_Google_Service_Sheets_ExtendedValue();
					$cell_data = new Forminator_Google_Service_Sheets_CellData();
					$value->setStringValue( '' );
					$cell_data->setUserEnteredValue( $value );
					$values[] = $cell_data;
				}
			}

			// Build the RowData
			$row_data = new Forminator_Google_Service_Sheets_RowData();
			$row_data->setValues( $values );

			// Prepare the request
			$append_request = new Forminator_Google_Service_Sheets_AppendCellsRequest();
			$append_request->setSheetId( 0 );
			$append_request->setRows( $row_data );
			$append_request->setFields( 'userEnteredValue' );

			// Set the request
			$request = new Forminator_Google_Service_Sheets_Request();
			$request->setAppendCells( $append_request );
			// Add the request to the requests array
			$requests   = array();
			$requests[] = $request;

			// Prepare the update
			$batch_update_request = new Forminator_Google_Service_Sheets_BatchUpdateSpreadsheetRequest(
				array(
					'requests' => $requests,
				)
			);

			$google_client = $this->addon->get_google_client();
			$google_client->setAccessToken( $this->addon->get_client_access_token() );
			$spreadsheet_service = new Forminator_Google_Service_Sheets( $google_client );
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

		} catch ( Forminator_Google_Exception $e ) {
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
		} catch ( Exception $e ) {
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
	 * @since 1.6.1
	 *
	 * @param $file_id
	 *
	 * @return array
	 * @throws Forminator_Addon_Googlesheet_Exception
	 * @throws Exception
	 */
	public function get_sheet_headers( $file_id ) {

		$google_client = $this->addon->get_google_client();
		$google_client->setAccessToken( $this->addon->get_client_access_token() );

		$spreadsheet_service = new Forminator_Google_Service_Sheets( $google_client );
		$spreadsheet         = $spreadsheet_service->spreadsheets->get( $file_id );
		$sheets              = $spreadsheet->getSheets();

		if ( ! isset( $sheets[0] ) || ! isset( $sheets[0]->properties ) ) {
			throw new Forminator_Addon_Googlesheet_Exception( __( 'No sheet found', Forminator::DOMAIN ) );
		}

		if ( ! isset( $sheets[0]->properties->title ) || empty( $sheets[0]->properties->title ) ) {
			throw new Forminator_Addon_Googlesheet_Exception( __( 'Sheet title not found', Forminator::DOMAIN ) );
		}

		if ( ! isset( $sheets[0]->properties->gridProperties ) || ! isset( $sheets[0]->properties->gridProperties->columnCount ) ) {
			throw new Forminator_Addon_Googlesheet_Exception( __( 'Failed to get column count of the sheet', Forminator::DOMAIN ) );
		}

		$sheet_title        = $sheets[0]->properties->title;
		$sheet_column_count = $sheets[0]->properties->gridProperties->columnCount;

		$headers_range = $sheet_title . '!1:1';
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
				$key_range = $sheet_title . '!' . Forminator_Addon_Googlesheet_Form_Hooks::column_number_to_letter( $column_number ) . '1';
				// forminator poll header format = 'Answer,Extra'
				$header_fields[ $value ] = array(
					'range' => $key_range,
					'value' => $value,
				);
				$column_number ++;
				$columns_filled ++;
			}
		}

		// dont use translation because it will be used as reference
		$required_header_columns = array( self::GSHEET_ANSWER_COLUMN_NAME, self::GSHEET_EXTRA_COLUMN_NAME );

		$new_column_count = 0;
		$update_bodies    = array();
		foreach ( $required_header_columns as $required_header_column ) {
			$expected_header_value = $required_header_column;
			if ( ! in_array( $required_header_column, array_keys( $header_fields ), true ) ) {
				//add
				$new_range = $sheet_title . '!' . Forminator_Addon_Googlesheet_Form_Hooks::column_number_to_letter( $column_number ) . '1';

				// update headers map
				$header_fields[ $required_header_column ] = array(
					'range' => $new_range,
					'value' => $expected_header_value,
				);

				// increment for next usage
				$column_number ++;
				$update_body = new Forminator_Google_Service_Sheets_ValueRange();
				$update_body->setRange( $new_range );
				$update_body->setValues( array( array( $expected_header_value ) ) );
				$update_bodies[] = $update_body;
				$new_column_count ++;
			} else {
				$header_field = $header_fields[ $required_header_column ];
				if ( $expected_header_value !== $header_field['value'] ) {
					// update headers map
					$header_fields[ $required_header_column ]['value'] = $expected_header_value;

					// update sheet
					$update_body = new Forminator_Google_Service_Sheets_ValueRange();
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
			$dimension_range = new Forminator_Google_Service_Sheets_DimensionRange();
			$dimension_range->setSheetId( 0 );
			$dimension_range->setDimension( 'COLUMNS' );
			$dimension_range->setStartIndex( $sheet_column_count );
			$dimension_range->setEndIndex( $total_column_needed );

			$insert_dimension = new Forminator_Google_Service_Sheets_InsertDimensionRequest();
			$insert_dimension->setRange( $dimension_range );
			$insert_dimension->setInheritFromBefore( true );

			$request = new Forminator_Google_Service_Sheets_Request();
			$request->setInsertDimension( $insert_dimension );

			$request_body = new Forminator_Google_Service_Sheets_BatchUpdateSpreadsheetRequest();
			$request_body->setRequests( array( $request ) );

			$spreadsheet_service->spreadsheets->batchUpdate( $file_id, $request_body );
		}
		if ( ! empty( $update_bodies ) ) {
			$request_body = new Forminator_Google_Service_Sheets_BatchUpdateValuesRequest();
			$request_body->setData( $update_bodies );
			$request_body->setValueInputOption( 'RAW' );
			$spreadsheet_service->spreadsheets_values->batchUpdate( $file_id, $request_body );
		}

		$grid_properties = new Forminator_Google_Service_Sheets_GridProperties();
		$grid_properties->setFrozenRowCount( 1 );

		$sheet_properties = new Forminator_Google_Service_Sheets_SheetProperties();
		$sheet_properties->setSheetId( 0 );
		$sheet_properties->setGridProperties( $grid_properties );

		$update_properties = new Forminator_Google_Service_Sheets_UpdateSheetPropertiesRequest();
		$update_properties->setProperties( $sheet_properties );
		$update_properties->setFields( 'gridProperties(frozenRowCount)' );

		$request = new Forminator_Google_Service_Sheets_Request();
		$request->setUpdateSheetProperties( $update_properties );

		$request_body = new Forminator_Google_Service_Sheets_BatchUpdateSpreadsheetRequest();
		$request_body->setRequests( array( $request ) );

		$spreadsheet_service->spreadsheets->batchUpdate( $file_id, $request_body );

		if ( $google_client->getAccessToken() !== $this->addon->get_client_access_token() ) {
			$this->addon->update_client_access_token( $google_client->getAccessToken() );
		}

		return $header_fields;

	}

	/**
	 * Google Sheets will add a column on the title/header row
	 * its called `Google Sheets Info` which can be translated on forminator lang
	 *
	 * @since 1.6.1
	 * @return array
	 */
	public function on_export_render_title_row() {

		$export_headers = array(
			'info' => __( 'Google Sheets Info', Forminator::DOMAIN ),
		);

		$poll_id                = $this->poll_id;
		$poll_settings_instance = $this->poll_settings_instance;

		/**
		 * Filter Google Sheets headers on export file
		 *
		 * @since 1.6.1
		 *
		 * @param array                                      $export_headers         headers to be displayed on export file
		 * @param int                                        $poll_id                current Poll ID
		 * @param Forminator_Addon_Googlesheet_Poll_Settings $poll_settings_instance Google Sheets Addon Poll Settings instance
		 */
		$export_headers = apply_filters(
			'forminator_addon_poll_googlesheet_export_headers',
			$export_headers,
			$poll_id,
			$poll_settings_instance
		);

		return $export_headers;
	}

	/**
	 * Google Sheets will add a column that give user information whether sending data to Google Sheets successfully or not
	 * It will only add one column even its multiple connection, every connection will be separated by comma
	 *
	 * @since 1.6.1
	 *
	 * @param Forminator_Form_Entry_Model $entry_model
	 * @param                             $addon_meta_data
	 *
	 * @return array
	 */
	public function on_export_render_entry( Forminator_Form_Entry_Model $entry_model, $addon_meta_data ) {

		$poll_id                = $this->poll_id;
		$poll_settings_instance = $this->poll_settings_instance;

		/**
		 *
		 * Filter Google Sheets metadata that previously saved on db to be processed
		 *
		 * @since 1.6.1
		 *
		 * @param array                                      $addon_meta_data
		 * @param int                                        $poll_id                current Poll ID
		 * @param Forminator_Addon_Googlesheet_Poll_Settings $poll_settings_instance Google Sheets Addon Form Settings instance
		 */
		$addon_meta_data = apply_filters(
			'forminator_addon_poll_googlesheet_metadata',
			$addon_meta_data,
			$poll_id,
			$poll_settings_instance
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
		 * @param int                                        $poll_id                current Poll ID
		 * @param Forminator_Form_Entry_Model                $entry_model            Form Entry Model
		 * @param array                                      $addon_meta_data        meta data saved by addon on entry fields
		 * @param Forminator_Addon_Googlesheet_Poll_Settings $poll_settings_instance Google Sheets Addon Poll Settings instance
		 */
		$export_columns = apply_filters(
			'forminator_addon_poll_googlesheet_export_columns',
			$export_columns,
			$poll_id,
			$entry_model,
			$addon_meta_data,
			$poll_settings_instance
		);

		return $export_columns;
	}

}
