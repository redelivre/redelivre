<?php

/**
 * Wrapper for HubSpot's API.
 *
 * @since   3.0.72
 *
 * @package ET\Core\API\Email
 */
class ET_Core_API_Email_HubSpot extends ET_Core_API_Email_Provider {

	/**
	 * @inheritDoc
	 */
	public $BASE_URL = 'https://api.hubapi.com/contacts/v1';

	/**
	 * @inheritDoc
	 */
	public $FIELDS_URL = 'https://api.hubapi.com/properties/v1/contacts/properties';

	/**
	 * @inheritDoc
	 */
	public $LISTS_URL = 'https://api.hubapi.com/contacts/v1/lists/static';

	/**
	 * @inheritDoc
	 */
	public $SUBSCRIBE_URL = 'https://api.hubapi.com/contacts/v1/contact/createOrUpdate/email/@email@';

	/**
	 * @inheritDoc
	 */
	public $custom_fields_scope = 'account';

	/**
	 * @inheritDoc
	 */
	public $name = 'HubSpot';

	/**
	 * @inheritDoc
	 */
	public $slug = 'hubspot';

	protected function _fetch_custom_fields( $list_id = '', $list = array() ) {
		$this->response_data_key = false;

		$fields_unprocessed = parent::_fetch_custom_fields( $list_id, $list );
		$fields             = array();

		foreach ( $fields_unprocessed as $field ) {
			$field_id = $field['field_id'];

			if ( ! isset( $field['options'] ) ) {
				$fields[ $field_id ] = $field;
				continue;
			}

			$options = array();

			foreach ( $field['options'] as $option ) {
				$option = $this->transform_data_to_our_format( $option, 'custom_field_option' );
				$id     = $option['id'];

				$options[ $id ] = $option['name'];
			}

			$field['options'] = $options;

			$fields[ $field_id ] = $field;
		}

		return $fields;
	}

	protected function _get_list_add_contact_url( $list_id ) {
		$url = "{$this->BASE_URL}/lists/{$list_id}/add";

		return add_query_arg( 'hapikey', $this->data['api_key'], $url );
	}

	protected function _maybe_set_urls( $email = '' ) {
		if ( empty( $this->data['api_key'] ) ) {
			return;
		}

		$this->FIELDS_URL    = add_query_arg( 'hapikey', $this->data['api_key'], $this->FIELDS_URL );
		$this->LISTS_URL     = add_query_arg( 'hapikey', $this->data['api_key'], $this->LISTS_URL );
		$this->SUBSCRIBE_URL = add_query_arg( 'hapikey', $this->data['api_key'], $this->SUBSCRIBE_URL );

		if ( $email ) {
			$this->SUBSCRIBE_URL = str_replace( '@email@', rawurlencode( $email ), $this->SUBSCRIBE_URL );
		}
	}

	protected function _process_custom_fields( $args ) {
		$args['properties'] = array();

		if ( ! isset( $args['custom_fields'] ) ) {
			return $args;
		}

		$fields     = $args['custom_fields'];
		$properties = array();

		unset( $args['custom_fields'] );

		foreach ( $fields as $field_id => $value ) {
			if ( is_array( $value ) && $value ) {
				// This is a multiple choice field (eg. checkbox, radio, select)
				$value = array_values( $value );
				$value = implode( ';', $value );
			} else if ( 'radio' === $this->data['custom_fields'][ $field_id ]['type'] ) {
				// Should use array key. Sometimes it's different than value and Hubspot expects the key
				$radio_options = $this->data['custom_fields'][ $field_id ]['options'];
				$value         = array_search( $value, $radio_options );
			}

			$properties[] = array(
				'property' => $field_id,
				'value'    => $value,
			);
		}

		$args['properties'] = $properties;

		return $args;
	}

	/**
	 * @inheritDoc
	 */
	public function get_account_fields() {
		return array(
			'api_key' => array(
				'label' => esc_html__( 'API Key', 'et_core' ),
			),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_data_keymap( $keymap = array() ) {
		$keymap = array(
			'list'                => array(
				'list_id'           => 'listId',
				'name'              => 'name',
				'subscribers_count' => 'metaData.size',
			),
			'subscriber'          => array(
				'email'         => 'email',
				'name'          => 'firstname',
				'last_name'     => 'lastname',
				'custom_fields' => 'custom_fields',
			),
			'error'               => array(
				'error_message' => 'message',
			),
			'custom_field'        => array(
				'field_id' => 'name',
				'name'     => 'label',
				'type'     => 'fieldType',
				'options'  => 'options',
				'hidden'   => 'hidden',
			),
			'custom_field_option' => array(
				'id'   => 'value',
				'name' => 'label',
			),
			'custom_field_type'   => array(
				// Us <=> Them
				'select'          => 'select',
				'radio'           => 'radio',
				'checkbox'        => 'checkbox',
				// Us => Them
				'input'           => 'text',
				'textarea'        => 'text',
				// Them => Us
				'text'            => 'input',
				'booleancheckbox' => 'radio',
			),
		);

		return parent::get_data_keymap( $keymap );
	}

	/**
	 * @inheritDoc
	 */
	public function fetch_subscriber_lists() {
		if ( empty( $this->data['api_key'] ) ) {
			return $this->API_KEY_REQUIRED;
		}

		$this->_maybe_set_urls();

		/**
		 * The maximum number of subscriber lists to request from Hubspot's API at a time.
		 *
		 * @since 3.0.75
		 *
		 * @param int $max_lists Value must be <= 250.
		 */
		$max_lists = (int) apply_filters( 'et_core_api_email_hubspot_max_lists', 250 );

		$this->LISTS_URL = add_query_arg( 'count', $max_lists, $this->LISTS_URL );

		$this->response_data_key = 'lists';

		return parent::fetch_subscriber_lists();
	}

	/**
	 * @inheritDoc
	 */
	public function subscribe( $args, $url = '' ) {
		if ( empty( $this->data['api_key'] ) ) {
			return $this->API_KEY_REQUIRED;
		}

		$this->_maybe_set_urls( $args['email'] );

		$args = $this->_process_custom_fields( $args );

		$data = array(
			'properties' => array(
				array(
					'property' => 'email',
					'value'    => et_sanitized_previously( $args['email'] ),
				),
				array(
					'property' => 'firstname',
					'value'    => et_sanitized_previously( $args['name'] ),
				),
				array(
					'property' => 'lastname',
					'value'    => et_sanitized_previously( $args['last_name'] ),
				),
			),
		);

		$data['properties'] = array_merge( $data['properties'], $args['properties'] );

		$this->prepare_request( $this->SUBSCRIBE_URL, 'POST', false, $data, true );
		$this->make_remote_request();

		if ( $this->response->ERROR ) {
			return $this->get_error_message();
		}

		$url  = $this->_get_list_add_contact_url( $args['list_id'] );
		$data = array(
			'emails' => array( $args['email'] ),
		);

		$this->prepare_request( $url, 'POST', false, $data, true );
		$this->make_remote_request();

		if ( $this->response->ERROR ) {
			return $this->get_error_message();
		}

		return 'success';
	}
}
