<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Author: Hoang Ngo
 */
class Forminator_Poll_Form_Model extends Forminator_Base_Form_Model {
	protected $post_type = 'forminator_polls';

	/**
	 * Whether to check form access
	 *
	 * @since 1.0.5
	 *
	 * @var bool
	 */
	protected $check_access = true;

	/**
	 * @param int|string $class_name
	 *
	 * @since 1.0
	 * @return self
	 */
	public static function model( $class_name = __CLASS__ ) { // phpcs:ignore
		return parent::model( $class_name );
	}

	/**
	 * Load preview
	 *
	 * @since 1.0
	 *
	 * @param $id
	 * @param $data
	 *
	 * @return bool|Forminator_Base_Form_Model
	 */
	public function load_preview( $id, $data ) {
		$form_model = $this->load( $id, true );

		// If bool, abort
		if ( is_bool( $form_model ) ) {
			return false;
		}

		$form_model->clear_fields();
		$form_model->set_var_in_array( 'name', 'formName', $data );

		//build the field
		$fields = array();
		if ( isset( $data['answers'] ) ) {
			$fields = $data['answers'];
			unset( $data['answers'] );
		}

		//build the settings
		if ( isset( $data['settings'] ) ) {
			$settings             = $data['settings'];
			$form_model->settings = $settings;
		}

		// Set fields
		foreach ( $fields as $f ) {
			$field          = new Forminator_Form_Field_Model();
			$field->form_id = isset( $f['wrapper_id'] ) ? $f['wrapper_id'] : $f['title'];
			$field->slug    = isset( $f['element_id'] ) ? $f['element_id'] : $f['title'];
			$field->import( $f );
			$form_model->add_field( $field );
		}

		$form_model->check_access = false;

		return $form_model;
	}

	/**
	 * Check if the vote clause is set up and if a user can vote again
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function current_user_can_vote() {
		/**
		 * Added condition for poll access.
		 *
		 * @since 1.0.5
		 */
		if ( $this->check_access ) {
			if ( $this->is_method_browser_cookie() ) {
				return $this->poll_votes_method_browser_cookie();
			} else {
				return $this->poll_votes_method_user_ip();
			}
		}

		return true;
	}

	/**
	 * Check user can vote by browser cookie
	 *
	 * @return bool
	 */
	public function poll_votes_method_browser_cookie() {
		$settings    = $this->settings;
		$poll_cookie = 'poll-cookie-' . md5( $this->id );
		if ( ! isset( $_COOKIE[ $poll_cookie ] ) ) {
			return true;
		}
		if ( $this->is_allow_multiple_votes() ) {
			if ( isset( $settings['vote_limit_input'] ) && ! empty( $settings['vote_limit_input'] ) ) {
				$duration           = is_numeric( $settings['vote_limit_input'] ) ? $settings['vote_limit_input'] : '1';
				$vote_limit_options = isset( $settings['vote_limit_options'] ) ? $settings['vote_limit_options'] : 'm';
				switch ( $vote_limit_options ) {
					case 'h':
						$interval = 'hour';
						break;
					case 'd':
						$interval = 'day';
						break;
					case 'W':
						$interval = 'week';
						break;
					case 'M':
						$interval = 'month';
						break;
					case 'm':
						$interval = 'minute';
						break;
					case 'Y':
						$interval = 'year';
						break;
					default:
						$interval = 'year';
						break;
				}
				$cookie_value  = date_i18n( 'Y-m-d H:i:s', strtotime( $_COOKIE[ $poll_cookie ] ) );
				$cookie_expire = $cookie_value . ' +' . $duration . ' ' . $interval;
				if ( time() < strtotime( $cookie_expire ) ) {
					return false;
				} else {
					return true;
				}
			} else {
				return true;
			}
		} else {
			return false;
		}
	}


	/**
	 * Check user can vote by user IP
	 *
	 * @return bool
	 */
	public function poll_votes_method_user_ip() {
		$settings = $this->settings;
		$user_ip  = Forminator_Geo::get_user_ip();
		if ( $this->is_allow_multiple_votes() ) {

			if ( isset( $settings['vote_limit_input'] ) ) {
				$duration           = is_numeric( $settings['vote_limit_input'] ) ? $settings['vote_limit_input'] : 0;
				$vote_limit_options = isset( $settings['vote_limit_options'] ) ? $settings['vote_limit_options'] : 'm';
				switch ( $vote_limit_options ) {
					case 'h':
						$interval = "INTERVAL $duration HOUR";
						break;
					case 'd':
						$interval = "INTERVAL $duration DAY";
						break;
					case 'W':
						$interval = "INTERVAL $duration WEEK";
						break;
					case 'M':
						$interval = "INTERVAL $duration MONTH";
						break;
					case 'Y':
						$interval = "INTERVAL $duration YEAR";
						break;
					default:
						$interval = "INTERVAL $duration MINUTE";
						break;
				}
				$last_entry = Forminator_Form_Entry_Model::get_last_entry_by_ip_and_form( $this->id, $user_ip );
				if ( $last_entry ) {
					$can_vote = Forminator_Form_Entry_Model::check_entry_date_by_ip_and_form( $this->id, $user_ip, $last_entry, $interval );
					if ( $can_vote ) {
						return true;
					} else {
						return false;
					}
				} else {
					return true;
				}
			} else {
				return true;
			}
		} else {
			$last_entry = Forminator_Form_Entry_Model::get_last_entry_by_ip_and_form( $this->id, $user_ip );
			if ( $last_entry ) {
				return false;
			}
		}
		return true;
	}


	/**
	 * Overridden load function to check element_id of answers for older poll
	 * Backward compat for <= 1.0.4
	 * which is forminator poll doesnt have element_id on poll answers
	 *
	 * @since 1.0.5
	 *
	 * @param      $id
	 * @param bool $callback
	 *
	 * @return bool|Forminator_Poll_Form_Model
	 */
	public function load( $id, $callback = false ) {
		$model = parent::load( $id, $callback );

		// callback means load latest post and replace data,
		// so we dont need to add element_id since its must be try to loading preview
		if ( ! $callback ) {
			if ( $model instanceof Forminator_Poll_Form_Model ) {
				// patch for backward compat
				return $this->maybe_add_element_id_on_answers( $model );
			}
		}

		return $model;
	}

	/**
	 * Add Element id on answers that doesnt have it
	 *
	 * @since 1.0.5
	 *
	 * @param Forminator_Poll_Form_Model $model
	 *
	 * @return Forminator_Poll_Form_Model
	 */
	private function maybe_add_element_id_on_answers( Forminator_Poll_Form_Model $model ) {
		$answers                   = $model->get_fields_as_array();
		$is_need_to_add_element_id = false;

		foreach ( $answers as $key => $answer ) {
			if ( ! isset( $answer['element_id'] ) || ! $answer['element_id'] ) {
				$is_need_to_add_element_id = true;
				break;
			}
		}

		if ( $is_need_to_add_element_id ) {

			// get max element id here
			$max_element_id = 0;
			foreach ( $answers as $answer ) {
				if ( isset( $answer['element_id'] ) && $answer['element_id'] ) {
					$element_id = trim( str_replace( 'answer-', '', $answer['element_id'] ) );
					if ( $element_id > $max_element_id ) {
						$max_element_id = $element_id;
					}
				}
			}
			foreach ( $answers as $key => $answer ) {
				if ( ! isset( $answer['element_id'] ) || ! $answer['element_id'] ) {
					$max_element_id ++;
					$answers[ $key ]['element_id'] = 'answer-' . $max_element_id; // start from 1
					$answers[ $key ]['id']         = 'answer-' . $max_element_id; // start from 1
				}
			}

			$model->clear_fields();
			foreach ( $answers as $answer ) {
				$field          = new Forminator_Form_Field_Model();
				$field->form_id = $model->id;
				$field->slug    = $answer['id'];
				unset( $answer['id'] );
				$field->import( $answer );
				$model->add_field( $field );
			}

			return $this->resave_and_reload( $model );
		}

		return $model;
	}

	/**
	 * Resave model and then load to return new model
	 *
	 * @since 1.0.5
	 *
	 * @param Forminator_Poll_Form_Model $model
	 *
	 * @return Forminator_Poll_Form_Model
	 */
	private function resave_and_reload( Forminator_Poll_Form_Model $model ) {
		$model->save();

		return $model;

	}

	/**
	 * Get Fields as array with `$key` as key of array and `$pluck_key` as $value with `$default` as fallback
	 *
	 * @since 1.0.5
	 *
	 * @param  string $pluck_key
	 * @param  string|null $key
	 * @param null $default
	 *
	 * @return array
	 */
	public function pluck_fields_array( $pluck_key, $key = null, $default = null ) {
		$fields_with_key = array();
		$fields          = $this->get_fields_as_array();

		foreach ( $fields as $field ) {
			if ( '*' === $pluck_key ) {
				$field_value = $field;
			} else {
				if ( isset( $field[ $pluck_key ] ) ) {
					$field_value = $field[ $pluck_key ];
				} else {
					$field_value = $default;
				}
			}

			if ( ! is_null( $key ) ) {
				if ( isset( $field[ $key ] ) ) {
					$fields_with_key[ $field[ $key ] ] = $field_value;
				} else {
					$fields_with_key[] = $field_value;
				}
			} else {
				$fields_with_key[] = $field_value;
			}
		}

		return $fields_with_key;
	}

	/**
	 * Get enable limit votes status flag
	 *
	 * @since 1.6.1
	 * @return bool
	 */
	public function is_allow_multiple_votes() {
		$settings             = $this->settings;
		$poll_id              = $this->id;
		$allow_multiple_votes = isset( $settings['enable-votes-limit'] ) ? filter_var( $settings['enable-votes-limit'], FILTER_VALIDATE_BOOLEAN ) : false;

		/**
		 * Filter allow_multiple_votes flag of a poll
		 *
		 * @since 1.6.1
		 *
		 * @param bool $allow_multiple_votes
		 * @param int $poll_id
		 * @param array $settings
		 */
		$allow_multiple_votes = apply_filters( 'forminator_poll_allow_multiple_votes', $allow_multiple_votes, $poll_id, $settings );

		return $allow_multiple_votes;
	}

	/**
	 * Get status of prevent_store
	 *
	 * @since 1.6.1
	 *
	 * @return boolean
	 */
	public function is_prevent_store() {
		$poll_id       = (int) $this->id;
		$poll_settings = $this->settings;

		// default is always store
		$is_prevent_store = false;

		$is_prevent_store = isset( $this->settings['store'] ) ? $this->settings['store'] : $is_prevent_store;
		$is_prevent_store = filter_var( $is_prevent_store, FILTER_VALIDATE_BOOLEAN );

		/**
		 * Filter is_prevent_store flag of a poll
		 *
		 * @since 1.6.1
		 *
		 * @param bool $is_prevent_store
		 * @param int $poll_id
		 * @param array $poll_settings
		 */
		$is_prevent_store = apply_filters( 'forminator_poll_is_prevent_store', $is_prevent_store, $poll_id, $poll_settings );

		return $is_prevent_store;
	}

	/**
	 * Export model
	 *
	 * Add filter to include `integrations`
	 *
	 * @since 1.6.1
	 * @return array
	 */
	public function to_exportable_data() {

		if ( ! Forminator::is_import_export_feature_enabled() ) {
			return array();
		}

		if ( Forminator::is_export_integrations_feature_enabled() ) {
			add_filter( 'forminator_poll_model_to_exportable_data', array( $this, 'export_integrations_data' ), 1, 1 );
		}

		$exportable_data = parent::to_exportable_data();

		// avoid filter executed on next cycle
		remove_filter( 'forminator_poll_model_to_exportable_data', array( $this, 'export_integrations_data' ), 1 );

		return $exportable_data;
	}

	/**
	 * Export integrations Poll setting
	 *
	 * @since 1.6.1
	 *
	 * @param $exportable_data
	 *
	 * @return array
	 */
	public function export_integrations_data( $exportable_data ) {
		$model_id                = $this->id;
		$exportable_integrations = array();

		$connected_addons = forminator_get_addons_instance_connected_with_poll( $model_id );

		foreach ( $connected_addons as $connected_addon ) {
			try {
				$poll_settings = $connected_addon->get_addon_poll_settings( $model_id );
				if ( $poll_settings instanceof Forminator_Addon_Poll_Settings_Abstract ) {
					$exportable_integrations[ $connected_addon->get_slug() ] = $poll_settings->to_exportable_data();
				}
			} catch ( Exception $e ) {
				forminator_addon_maybe_log( $connected_addon->get_slug(), 'failed to get to_exportable_data', $e->getMessage() );
			}
		}

		/**
		 * Filter integrations data to export
		 *
		 * @since 1.6.1
		 *
		 * @param array $exportable_integrations
		 * @param array $exportable_data all exportable data from model, useful
		 */
		$exportable_integrations         = apply_filters( 'forminator_poll_model_export_integrations_data', $exportable_integrations, $model_id );
		$exportable_data['integrations'] = $exportable_integrations;

		return $exportable_data;
	}

	/**
	 * Import Model
	 *
	 * add filter `forminator_import_model`
	 *
	 * @since 1.6.1
	 *
	 * @param        $import_data
	 * @param string $module
	 *
	 * @return Forminator_Base_Form_Model|WP_Error
	 */
	public static function create_from_import_data( $import_data, $module = __CLASS__ ) {
		if ( Forminator::is_import_integrations_feature_enabled() ) {
			add_filter(
				'forminator_import_model',
				array(
					'Forminator_Poll_Form_Model',
					'import_integrations_data',
				),
				1,
				3
			);
		}

		$model = parent::create_from_import_data( $import_data, $module );

		// avoid filter executed on next cycle
		remove_filter(
			'forminator_import_model',
			array(
				'Forminator_Poll_Form_Model',
				'import_integrations_data',
			),
			1
		);

		return $model;
	}

	/**
	 * Import Integrations data model
	 *
	 * @since 1.6.1
	 *
	 * @param $model
	 * @param $import_data
	 * @param $module
	 *
	 * @return Forminator_Poll_Form_Model
	 */
	public static function import_integrations_data( $model, $import_data, $module ) {
		// return what it is
		if ( is_wp_error( $model ) ) {
			return $model;
		}

		// make sure its custom form
		if ( __CLASS__ !== $module ) {
			return $model;
		}

		if ( ! isset( $import_data['integrations'] ) || empty( $import_data['integrations'] ) || ! is_array( $import_data['integrations'] ) ) {
			return $model;
		}

		/** @var Forminator_Poll_Form_Model $model */

		$integrations_data = $import_data['integrations'];
		foreach ( $integrations_data as $slug => $integrations_datum ) {
			try {
				$addon = forminator_get_addon( $slug );
				if ( $addon instanceof Forminator_Addon_Abstract ) {
					$poll_settings = $addon->get_addon_poll_settings( $model->id );
					if ( $poll_settings instanceof Forminator_Addon_Poll_Settings_Abstract ) {
						$poll_settings->import_data( $integrations_datum );
					}
				}
			} catch ( Exception $e ) {
				forminator_addon_maybe_log( $slug, 'failed to get import form settings', $e->getMessage() );
			}
		}

		return $model;

	}

	/**
	 * Flag if module should be loaded via ajax
	 *
	 * @since 1.6.1
	 *
	 * @param bool $force
	 *
	 * @return bool
	 */
	public function is_ajax_load( $force = false ) {
		$poll_id        = (int) $this->id;
		$form_settings  = $this->settings;
		$global_enabled = parent::is_ajax_load( $force );

		$enabled = isset( $form_settings['use_ajax_load'] ) ? $this->settings['use_ajax_load'] : false;
		$enabled = filter_var( $enabled, FILTER_VALIDATE_BOOLEAN );

		$enabled = $global_enabled || $enabled;

		/**
		 * Filter is ajax load for Poll
		 *
		 * @since 1.6.1
		 *
		 * @param bool $enabled
		 * @param bool $global_enabled
		 * @param int $poll_id
		 * @param array $form_settings
		 *
		 * @return bool
		 */
		$enabled = apply_filters( 'forminator_poll_is_ajax_load', $enabled, $global_enabled, $poll_id, $form_settings );

		return $enabled;
	}

	/**
	 * Flag to use `DONOTCACHEPAGE`
	 *
	 * @since 1.6.1
	 * @return bool
	 */
	public function is_use_donotcachepage_constant() {
		$poll_id        = (int) $this->id;
		$form_settings  = $this->settings;
		$global_enabled = parent::is_use_donotcachepage_constant();

		$enabled = isset( $form_settings['use_donotcachepage'] ) ? $this->settings['use_donotcachepage'] : false;
		$enabled = filter_var( $enabled, FILTER_VALIDATE_BOOLEAN );

		$enabled = $global_enabled || $enabled;

		/**
		 * Filter use `DONOTCACHEPAGE` Poll
		 *
		 * @since 1.6.1
		 *
		 * @param bool $enabled
		 * @param bool $global_enabled
		 * @param int $poll_id
		 * @param array $form_settings
		 *
		 * @return bool
		 */
		$enabled = apply_filters( 'forminator_poll_is_use_donotcachepage_constant', $enabled, $global_enabled, $poll_id, $form_settings );

		return $enabled;
	}

	/**
	 * Get Browser votes method enable status flag
	 *
	 * @since 1.7
	 *
	 * @return bool
	 */
	public function is_method_browser_cookie() {
		$settings       = $this->settings;
		$poll_id        = $this->id;
		$browser_method = isset( $settings['enable-votes-method'] ) && 'browser_cookie' === $settings['enable-votes-method'] ? true : false;

		/**
		 * Filter browser_method flag of a poll
		 *
		 * @since 1.7
		 *
		 * @param bool $browser_method
		 * @param int $poll_id
		 * @param array $settings
		 */
		$browser_method = apply_filters( 'forminator_poll_method_browser_cookie', $browser_method, $poll_id, $settings );

		return $browser_method;
	}
}
