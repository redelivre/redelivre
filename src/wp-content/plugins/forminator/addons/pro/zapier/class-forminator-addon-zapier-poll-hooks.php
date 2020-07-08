<?php

/**
 * Class Forminator_Addon_Zapier_Poll_Hooks
 *
 * @since 1.6.1
 *
 */
class Forminator_Addon_Zapier_Poll_Hooks extends Forminator_Addon_Poll_Hooks_Abstract {

	/**
	 * Addon instance are auto available form abstract
	 * Its added here for development purpose,
	 * Auto-complete will resolve addon directly to `Zapier` instance instead of the abstract
	 * And its public properties can be exposed
	 *
	 * @since 1.6.1
	 * @var Forminator_Addon_Zapier
	 */
	protected $addon;

	/**
	 * Poll Settings Instance
	 *
	 * @since 1.6.1
	 * @var Forminator_Addon_Zapier_Poll_Settings | null
	 */
	protected $poll_settings_instance;

	/**
	 * Forminator_Addon_Zapier_Poll_Hooks constructor.
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
		$this->_submit_poll_error_message = __( 'Zapier failed to process submitted data. Please check your form and try again', Forminator::DOMAIN );
	}

	/**
	 * Save status of request sent and received for each connected zap(s)
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
		 * Filter zapier submitted form data to be processed
		 *
		 * @since 1.6.1
		 *
		 * @param array                                 $submitted_data
		 * @param int                                   $poll_id                current Form ID
		 * @param Forminator_Addon_Zapier_Form_Settings $poll_settings_instance Zapier Addon Form Settings instance
		 */
		$submitted_data = apply_filters(
			'forminator_addon_zapier_poll_submitted_data',
			$submitted_data,
			$poll_id,
			$poll_settings_instance
		);

		forminator_addon_maybe_log( __METHOD__, $submitted_data );

		$addon_setting_values = $this->poll_settings_instance->get_poll_settings_values();
		$poll_settings        = $this->poll_settings_instance->get_poll_settings();

		$data = array();

		/**
		 * Fires before sending data to Webhook URL(s)
		 *
		 * @since 1.6.1
		 *
		 * @param int                                   $poll_id                current Poll ID
		 * @param array                                 $submitted_data
		 * @param Forminator_Addon_Zapier_Poll_Settings $poll_settings_instance Zapier Addon Poll Settings instance
		 */
		do_action( 'forminator_addon_zapier_poll_before_post_to_webhook', $poll_id, $submitted_data, $poll_settings_instance );

		foreach ( $addon_setting_values as $key => $addon_setting_value ) {
			// save it on entry field, with name `status-$MULTI_ID`, and value is the return result on sending data to zapier
			$data[] = array(
				'name'  => 'status-' . $key,
				'value' => $this->get_status_on_send_data( $key, $submitted_data, $addon_setting_value, $poll_settings ),
			);
		}

		$entry_fields = $data;
		/**
		 * Filter zapier entry fields to be saved to entry model
		 *
		 * @since 1.6.1
		 *
		 * @param array                                 $entry_fields
		 * @param int                                   $poll_id                current Poll ID
		 * @param array                                 $submitted_data
		 * @param Forminator_Addon_Zapier_Poll_Settings $poll_settings_instance Zapier Poll Settings instance
		 */
		$data = apply_filters(
			'forminator_addon_zapier_poll_entry_fields',
			$entry_fields,
			$poll_id,
			$submitted_data,
			$poll_settings_instance
		);

		return $data;

	}

	/**
	 * Get status on sending data to zapier
	 *
	 * @since 1.6.1
	 *
	 * @param $connection_id
	 * @param $submitted_data
	 * @param $connection_settings
	 * @param $poll_settings
	 *
	 * @return array `is_sent` true means its success send data to zapier, false otherwise
	 */
	private function get_status_on_send_data( $connection_id, $submitted_data, $connection_settings, $poll_settings ) {
		// initialize as null
		$zapier_api = null;

		$poll_id                = $this->poll_id;
		$poll_settings_instance = $this->poll_settings_instance;

		//check required fields
		try {
			if ( ! isset( $connection_settings['webhook_url'] ) ) {
				throw new Forminator_Addon_Zapier_Exception( __( 'Webhook URL is not properly setup', Forminator::DOMAIN ) );
			}

			$endpoint = $connection_settings['webhook_url'];
			/**
			 * Filter Endpoint Webhook URL to send
			 *
			 * @since 1.6.1
			 *
			 * @param string $endpoint
			 * @param int    $poll_id             current Form ID
			 * @param array  $connection_settings current connection setting, it contains `name` and `webhook_url`
			 */
			$endpoint = apply_filters(
				'forminator_addon_zapier_poll_endpoint',
				$endpoint,
				$poll_id,
				$connection_settings
			);

			$zapier_api = $this->addon->get_api( $endpoint );

			$args              = array();
			$args['poll-name'] = forminator_get_name_from_model( $this->poll );

			$answer_data   = isset( $submitted_data[ $this->poll_id ] ) ? $submitted_data[ $this->poll_id ] : '';
			$extra_field   = isset( $submitted_data[ $this->poll_id . '-extra' ] ) ? $submitted_data[ $this->poll_id . '-extra' ] : '';
			$fields_labels = $this->poll->pluck_fields_array( 'title', 'element_id', '1' );

			$answer = isset( $fields_labels[ $answer_data ] ) ? $fields_labels[ $answer_data ] : $answer_data;
			$extra  = $extra_field;

			$args['vote']       = $answer;
			$args['vote-extra'] = $extra;
			$args['results']    = array();

			$fields_array = $this->poll->get_fields_as_array();
			$map_entries  = Forminator_Form_Entry_Model::map_polls_entries( $this->poll_id, $fields_array );

			// append new answer
			if ( ! $this->poll->is_prevent_store() ) {
				$answer_data = isset( $submitted_data[ $this->poll_id ] ) ? $submitted_data[ $this->poll_id ] : '';

				$entries = 0;
				// exists on map entries
				if ( in_array( $answer_data, array_keys( $map_entries ), true ) ) {
					$entries = $map_entries[ $answer_data ];
				}

				$entries ++;
				$map_entries[ $answer_data ] = $entries;

			}

			$fields = $this->poll->get_fields();

			if ( ! is_null( $fields ) ) {
				foreach ( $fields as $field ) {
					$label = addslashes( $field->title );

					$slug    = isset( $field->slug ) ? $field->slug : sanitize_title( $label );
					$entries = 0;
					if ( in_array( $slug, array_keys( $map_entries ), true ) ) {
						$entries = $map_entries[ $slug ];
					}
					$args['results'][ $slug ] = array(
						'label' => $label,
						'votes' => $entries,
					);
				}
			}

			/**
			 * Filter arguments to passed on to Zapier Webhook API
			 *
			 * @since 1.6.1
			 *
			 * @param array                                 $args
			 * @param int                                   $poll_id                Current Poll id
			 * @param string                                $connection_id          ID of current connection
			 * @param array                                 $submitted_data
			 * @param array                                 $connection_settings    current connection setting, contains `name` and `webhook_url`
			 * @param array                                 $poll_settings          Displayed Poll settings
			 * @param Forminator_Addon_Zapier_Poll_Settings $poll_settings_instance Zapier Poll Settings instance
			 */
			$args = apply_filters(
				'forminator_addon_zapier_poll_post_to_webhook_args',
				$args,
				$poll_id,
				$connection_id,
				$submitted_data,
				$connection_settings,
				$poll_settings,
				$poll_settings_instance
			);

			$zapier_api->post_( $args );

			forminator_addon_maybe_log( __METHOD__, 'Success Send Data' );

			return array(
				'is_sent'         => true,
				'connection_name' => $connection_settings['name'],
				'description'     => __( 'Successfully send data to Zapier', Forminator::DOMAIN ),
				'data_sent'       => $zapier_api->get_last_data_sent(),
				'data_received'   => $zapier_api->get_last_data_received(),
				'url_request'     => $zapier_api->get_last_url_request(),
			);

		} catch ( Forminator_Addon_Zapier_Exception $e ) {
			forminator_addon_maybe_log( __METHOD__, 'Failed to Send to Zapier' );

			return array(
				'is_sent'         => false,
				'description'     => $e->getMessage(),
				'connection_name' => $connection_settings['name'],
				'data_sent'       => ( ( $zapier_api instanceof Forminator_Addon_Zapier_Wp_Api ) ? $zapier_api->get_last_data_sent() : array() ),
				'data_received'   => ( ( $zapier_api instanceof Forminator_Addon_Zapier_Wp_Api ) ? $zapier_api->get_last_data_received() : array() ),
				'url_request'     => ( ( $zapier_api instanceof Forminator_Addon_Zapier_Wp_Api ) ? $zapier_api->get_last_url_request() : '' ),
			);
		}
	}

	/**
	 * Zapier will add a column on the title/header row
	 * its called `Zapier Info` which can be translated on forminator lang
	 *
	 * @since 1.6.1
	 * @return array
	 */
	public function on_export_render_title_row() {

		$export_headers = array(
			'info' => __( 'Zapier Info', Forminator::DOMAIN ),
		);

		$poll_id                = $this->poll_id;
		$poll_settings_instance = $this->poll_settings_instance;

		/**
		 * Filter zapier headers on export file
		 *
		 * @since 1.6.1
		 *
		 * @param array                                 $export_headers         headers to be displayed on export file
		 * @param int                                   $poll_id                current Form ID
		 * @param Forminator_Addon_Zapier_Poll_Settings $poll_settings_instance Zapier Poll Settings instance
		 */
		$export_headers = apply_filters(
			'forminator_addon_zapier_poll_export_headers',
			$export_headers,
			$poll_id,
			$poll_settings_instance
		);

		return $export_headers;
	}

	/**
	 * Zapier will add a column that give user information whether sending data to zapier successfully or not
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
		 * Filter zapier metadata that previously saved on db to be processed
		 *
		 * @since 1.1
		 *
		 * @param array                                 $addon_meta_data
		 * @param int                                   $poll_id                current Poll ID
		 * @param Forminator_Addon_Zapier_Poll_Settings $poll_settings_instance Zapier Poll Settings instance
		 */
		$addon_meta_data = apply_filters(
			'forminator_addon_zapier_poll_metadata',
			$addon_meta_data,
			$poll_id,
			$poll_settings_instance
		);

		$export_columns = array(
			'info' => $this->get_from_addon_meta_data( $addon_meta_data, 'description', '' ),
		);

		/**
		 * Filter zapier columns to be displayed on export submissions
		 *
		 * @since 1.6.1
		 *
		 * @param array                                 $export_columns         column to be exported
		 * @param int                                   $poll_id                current Poll ID
		 * @param Forminator_Form_Entry_Model           $entry_model            Form Entry Model
		 * @param array                                 $addon_meta_data        meta data saved by addon on entry fields
		 * @param Forminator_Addon_Zapier_Poll_Settings $poll_settings_instance Zapier Poll Settings instance
		 */
		$export_columns = apply_filters(
			'forminator_addon_zapier_poll_export_columns',
			$export_columns,
			$poll_id,
			$entry_model,
			$addon_meta_data,
			$poll_settings_instance
		);

		return $export_columns;
	}
}
