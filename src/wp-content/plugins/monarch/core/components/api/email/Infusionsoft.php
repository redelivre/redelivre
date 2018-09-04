<?php

/**
 * Wrapper for Infusionsoft's API.
 *
 * @since   1.1.0
 *
 * @package ET\Core\API\Email
 */
class ET_Core_API_Email_Infusionsoft extends ET_Core_API_Email_Provider {

	private static $_data_keys = array(
		'app_name' => 'client_id',
		'api_key'  => 'api_key',
	);

	/**
	 * @inheritDoc
	 */
	public $ACCESS_TOKEN_URL = 'https://api.infusionsoft.com/token';

	/**
	 * @inheritDoc
	 */
	public $AUTHORIZATION_URL = 'https://signin.infusionsoft.com/app/oauth/authorize';

	/**
	 * @inheritDoc
	 */
	public $BASE_URL = '';

	/**
	 * @inheritDoc
	 * Use this variable to hold the pattern and update $BASE_URL dynamically when needed
	 */
	public $BASE_URL_PATTERN = 'https://@app_name@.infusionsoft.com/api/xmlrpc';

	/**
	 * @inheritDoc
	 */
	public $custom_fields_scope = 'account';

	/**
	 * @inheritDoc
	 */
	public $name = 'Infusionsoft';

	/**
	 * @inheritDoc
	 */
	public $oauth_version = '2.0';

	/**
	 * @inheritDoc
	 */
	public $slug = 'infusionsoft';

	/**
	 * @inheritDoc
	 * @internal If true, oauth endpoints properties must also be defined.
	 */
	public $uses_oauth = false;

	public function __construct( $owner = '', $account_name = '', $api_key = '' ) {
		parent::__construct( $owner, $account_name, $api_key );

		$this->http->expects_json = false;
	}

	protected function _add_contact_to_list( $contact_id, $list_id ) {
		$params = array( $this->data[ self::$_data_keys['api_key'] ], (int) $contact_id, (int) $list_id );
		$data = self::$_->prepare_xmlrpc_method_call( 'ContactService.addToGroup', $params );

		$this->_do_request( $data );

		if ( $this->response->ERROR ) {
			return false;
		}

		return self::$_->process_xmlrpc_response( $this->response->DATA );
	}

	protected function _create_contact( $contact_details ) {
		$params = array( $this->data[ self::$_data_keys['api_key'] ], $contact_details );
		$data   = self::$_->prepare_xmlrpc_method_call( 'ContactService.add', $params );

		$this->_do_request( $data );

		if ( $this->response->ERROR ) {
			return false;
		}

		$result = self::$_->process_xmlrpc_response( $this->response->DATA );

		return $result;
	}

	protected function _do_request( $data ) {
		$this->prepare_request( $this->_get_base_url(), 'POST', false, $data );
		$this->request->HEADERS = array( 'Content-Type' => 'application/xml', 'Accept-Charset' => 'UTF-8' );
		$this->make_remote_request();
	}

	protected function _fetch_custom_fields( $list_id = '', $list = array() ) {
		$params = array( $this->data[ self::$_data_keys['api_key'] ], 'DataFormField', 100, 0, array( 'FormId' => -1 ), array( 'Name', 'Label', 'DataType', 'Values' ) );
		$data   = self::$_->prepare_xmlrpc_method_call( 'DataService.query', $params );

		$this->_do_request( $data );

		if ( $this->response->ERROR ) {
			return array();
		}

		$data = self::$_->process_xmlrpc_response( $this->response->DATA );

		foreach ( $data as &$custom_field ) {
			$custom_field = (array) $custom_field;
			$custom_field = $this->transform_data_to_our_format( $custom_field, 'custom_field' );
		}

		$fields = array();

		foreach ( $data as $field ) {
			$field_id = $field['field_id'];
			$type     = self::$_->array_get( $field, 'type', 'any' );

			$field['type'] = self::$_->array_get( $this->data_keys, "custom_field_type.{$type}", 'any' );

			if ( isset( $field['options'] ) ) {
				$field['options'] = explode( "\n", $field['options'] );
				$field['options'] = array_filter( $field['options'] );
			}

			$fields[ $field_id ] = $field;
		}

		return $fields;
	}

	protected function _get_base_url() {
		$this->BASE_URL = str_replace( '@app_name@', $this->data[ self::$_data_keys['app_name'] ], $this->BASE_URL_PATTERN );
		return $this->BASE_URL;
	}

	protected function _get_contact_by_email( $email ) {
		$params = array( $this->data[ self::$_data_keys['api_key'] ], 'Contact', 1, 0, array( 'Email' => $email ), array( 'Id', 'Groups' ) );
		$data   = self::$_->prepare_xmlrpc_method_call( 'DataService.query', $params );

		$this->_do_request( $data );

		if ( $this->response->ERROR ) {
			return false;
		}

		return self::$_->process_xmlrpc_response( $this->response->DATA );
	}

	protected function _optin_email_address( $email ) {
		$params = array( $this->data[ self::$_data_keys['api_key'] ], $email, $this->SUBSCRIBED_VIA );
		$data   = self::$_->prepare_xmlrpc_method_call('APIEmailService.optIn', $params );

		$this->_do_request( $data );

		if ( $this->response->ERROR ) {
			return false;
		}

		return self::$_->process_xmlrpc_response( $this->response->DATA );
	}

	protected function _process_custom_fields( $args ) {
		if ( ! isset( $args['custom_fields'] ) ) {
			return array();
		}

		$fields = array();

		foreach ( $args['custom_fields'] as $field_id => $value ) {
			if ( is_array( $value ) && $value ) {
				// This is a multiple choice field (eg. checkbox, radio, select)
				$value = array_values( $value );
				$value = implode( ',', $value );
			}

			$fields[ "_{$field_id}" ] = $value;
		}

		return $fields;
	}

	public function retrieve_subscribers_count() {
		$existing_lists = $this->data['lists'];

		if ( empty( $existing_lists ) ) {
			return;
		}

		foreach( $existing_lists as $list_id => $list_data ) {
			$params = array( $this->data[ self::$_data_keys['api_key'] ], 'Contact', array( 'Groups' => "%{$list_id}%" ) );
			$data   = self::$_->prepare_xmlrpc_method_call( 'DataService.count', $params );

			$this->_do_request( $data );

			if ( $this->response->ERROR ) {
				continue;
			}

			$subscribers_count = self::$_->process_xmlrpc_response( $this->response->DATA );

			if ( empty( $subscribers_count ) || self::$_->is_xmlrpc_error( $subscribers_count ) ) {
				$subscribers_count = 0;
			}

			$existing_lists[ $list_id ]['subscribers_count'] = $subscribers_count;

			$this->data['lists'] = $existing_lists;

			$this->save_data();
		}
	}

	/**
	 * @inheritDoc
	 */
	protected function _process_subscriber_lists( $lists ) {
		$result = array();

		if ( empty( $lists ) || ! is_array( $lists ) ) {
			return $result;
		}

		foreach( $lists as $list ) {
			$list_id   = (string) $list->Id;
			$list_name = (string) $list->GroupName;

			$result[ $list_id ]['list_id']           = $list_id;
			$result[ $list_id ]['name']              = $list_name;
			$result[ $list_id ]['subscribers_count'] = 0;
		}

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function get_account_fields() {
		return array(
			self::$_data_keys['api_key']  => array(
				'label' => esc_html__( 'API Key', 'et_core' ),
			),
			self::$_data_keys['app_name'] => array(
				'label' => esc_html__( 'App Name', 'et_core' ),
			),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_data_keymap( $keymap = array() ) {
		$keymap = array(
			'subscriber'        => array(
				'name'          => 'FirstName',
				'last_name'     => 'LastName',
				'email'         => 'Email',
				'custom_fields' => 'custom_fields',
			),
			'custom_field'      => array(
				'field_id' => 'Name',
				'name'     => 'Label',
				'type'     => 'DataType',
				'options'  => 'Values',
			),
			'custom_field_type' => array(
				// Us => Them
				'input'    => 15,
				'textarea' => 16,
				'checkbox' => 17,
				'radio'    => 20,
				'select'   => 21,
				// Them => Us
				15         => 'input',
				16         => 'textarea',
				17         => 'checkbox',
				20         => 'radio',
				21         => 'select',
			),
		);

		return parent::get_data_keymap( $keymap );
	}

	/**
	 * @inheritDoc
	 */
	public function fetch_subscriber_lists() {
		if ( empty( $this->data[ self::$_data_keys['api_key'] ] ) || empty( $this->data[ self::$_data_keys['app_name'] ] ) ) {
			return $this->API_KEY_REQUIRED;
		}

		$this->response_data_key = false;

		$params_count = array( $this->data[ self::$_data_keys['api_key'] ], 'ContactGroup', array( 'Id' => '%' ) );
		$data_count   = self::$_->prepare_xmlrpc_method_call( 'DataService.count', $params_count );

		$this->_do_request( $data_count );

		if ( $this->response->ERROR ) {
			return $this->response->ERROR_MESSAGE;
		}

		$records_count = (int) self::$_->process_xmlrpc_response( $this->http->response->DATA );

		if ( 0 === $records_count ) {
			return 'success';
		}

		// determine how many requests we need to retrieve all lists
		$number_of_additional_requests = floor( $records_count / 1000 );

		$list_data = array();

		for ( $i = 0; $i <= $number_of_additional_requests; $i++ ) {
			$params = array( $this->data[ self::$_data_keys['api_key'] ], 'ContactGroup', 1000, $i, array( 'Id' => '%' ), array( 'Id', 'GroupName' ) );
			$data   = self::$_->prepare_xmlrpc_method_call( 'DataService.query', $params );

			$this->_do_request( $data );

			if ( $this->http->response->ERROR ) {
				return $this->http->response->ERROR_MESSAGE;
			}

			$response = self::$_->process_xmlrpc_response( $this->http->response->DATA );

			if ( self::$_->is_xmlrpc_error( $response ) ) {
				return $response->faultString;
			}

			$list_data = array_merge( $list_data, $response );
		}

		$lists = $this->_process_subscriber_lists( $list_data );

		if ( false === $lists ) {
			return $this->response->ERROR_MESSAGE;
		} else if ( self::$_->is_xmlrpc_error( $lists ) ) {
			return $lists->faultString;
		}

		$result = 'success';

		$this->data['lists']         = $lists;
		$this->data['custom_fields'] = $this->_fetch_custom_fields();
		$this->data['is_authorized'] = true;

		// retrieve counts right away if it can be done in reasonable time ( in 20 seconds )
		if ( 20 >= count( $lists ) ) {
			$this->retrieve_subscribers_count();
		} else {
			// estimate the time for all lists update assuming that one list can be updated in 1 second
			$estimated_time = ceil( count( $lists ) / 60 );
			$result         = array(
				'need_counts_update' => true,
				'message'            => sprintf(
					esc_html__( 'Successfully authorized. Subscribers count will be updated in background, please check back in %1$s %2$s', 'et_core' ),
					$estimated_time,
					1 === (int) $estimated_time ? esc_html__( 'minute', 'et_core' ) : esc_html__( 'minutes', 'et_core' )
				),
			);
		}

		$this->save_data();

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function subscribe( $args, $url = '' ) {
		if ( empty( $this->data[ self::$_data_keys['api_key'] ] ) || empty( $this->data[ self::$_data_keys['app_name'] ] ) ) {
			return $this->API_KEY_REQUIRED;
		}

		$message       = '';
		$search_result = $this->_get_contact_by_email( $args['email'] );

		if ( false === $search_result ) {
			return $this->response->ERROR_MESSAGE;
		} else if ( self::$_->is_xmlrpc_error( $search_result ) ) {
			return $search_result->faultString;
		}

		if ( ! empty( $search_result ) ) {
			$message = esc_html__( 'Already subscribed', 'bloom' );

			if ( false === strpos( $search_result[0]->Groups, $args['list_id'] ) ) {
				$result = $this->_add_contact_to_list( $search_result[0]->Id, $args['list_id'] );
				$message = 'success';

				if ( false === $result ) {
					return $this->response->ERROR_MESSAGE;
				} else if ( self::$_->is_xmlrpc_error( $result ) ) {
					return $result->faultString;
				}
			}
		} else {
			$custom_fields   = $this->_process_custom_fields( $args );
			$contact_details = array(
				'FirstName' => $args['name'],
				'LastName'  => $args['last_name'],
				'Email'     => $args['email'],
			);

			$new_contact_id = $this->_create_contact( array_merge( $contact_details, $custom_fields ) );

			if ( false === $new_contact_id ) {
				return $this->response->ERROR_MESSAGE;
			} else if ( self::$_->is_xmlrpc_error( $new_contact_id ) ) {
				return $new_contact_id->faultString;
			}

			$result = $this->_add_contact_to_list( $new_contact_id, $args['list_id'] );

			if ( false === $result ) {
				return $this->response->ERROR_MESSAGE;
			} else if ( self::$_->is_xmlrpc_error( $result ) ) {
				return $search_result->faultString;
			}

			if ( $this->_optin_email_address( $args['email'] ) ) {
				$message = 'success';
			}
		}

		return ( '' !== $message ) ? $message : $this->FAILURE_MESSAGE;
	}
}
