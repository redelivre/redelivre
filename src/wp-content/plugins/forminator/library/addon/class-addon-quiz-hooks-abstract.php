<?php

/**
 * Class Forminator_Addon_Quiz_Hooks_Abstract
 * Any change(s) to this file is subject to:
 * - Properly Written DocBlock! (what is this, why is that, how to be like those, etc, as long as you want!)
 * - Properly Written Changelog!
 *
 * If you override any of these method, please add necessary hooks in it,
 * Which you can see below, as a reference and keep the arguments signature.
 * If needed you can call these method, as parent::method_name(),
 * and add your specific hooks.
 *
 * @since 1.6.2
 */
abstract class Forminator_Addon_Quiz_Hooks_Abstract {

	/**
	 * Addon Instance
	 *
	 * @since 1.6.2
	 * @var Forminator_Addon_Abstract
	 */
	protected $addon;

	/**
	 * Current Quiz ID
	 *
	 * @since 1.6.2
	 * @var int
	 */
	protected $quiz_id;

	/**
	 * Customizable submit quiz error message
	 *
	 * @since 1.6.2
	 * @var string
	 */
	protected $_submit_quiz_error_message = '';

	/**
	 * Quiz settings instance
	 *
	 * @since 1.6.2
	 * @var Forminator_Addon_Quiz_Settings_Abstract|null
	 *
	 */
	protected $quiz_settings_instance;

	/**
	 * Quiz Model
	 *
	 * @since 1.6.2
	 * @var Forminator_Quiz_Form_Model
	 */
	protected $quiz;

	/**
	 * Forminator_Addon_Quiz_Hooks_Abstract constructor.
	 *
	 * @param Forminator_Addon_Abstract $addon
	 * @param int                       $quiz_id
	 *
	 * @since 1.6.2
	 * @throws Forminator_Addon_Exception
	 */
	public function __construct( Forminator_Addon_Abstract $addon, $quiz_id ) {
		$this->addon   = $addon;
		$this->quiz_id = $quiz_id;
		$this->quiz    = Forminator_Quiz_Form_Model::model()->load( $this->quiz_id );
		if ( ! $this->quiz ) {
			/* translators: ... */
			throw new Forminator_Addon_Exception( sprintf( __( 'Quiz with id %d could not be found', Forminator::DOMAIN ), $this->quiz_id ) );
		}

		$this->_submit_quiz_error_message = __( 'Failed to submit quiz because of an addon, please check your quiz and try again' );

		// get quiz settings instance to be available throughout cycle
		$this->quiz_settings_instance = $this->addon->get_addon_quiz_settings( $this->quiz_id );
	}

	/**
	 * Override this function to execute action before quiz questions rendered
	 *
	 * If function generate output, it will output-ed,
	 * race condition between addon probably happen.
	 * Its void function, so return value will be ignored, and forminator process will always continue,
	 * unless it generates unrecoverable error, so please be careful on extending this function.
	 * If you want to `wp_enqueue_script` this might be the best place.
	 *
	 * @since 1.6.2
	 */
	public function on_before_render_quiz_questions() {
		$addon_slug             = $this->addon->get_slug();
		$quiz_id                = $this->quiz_id;
		$quiz_settings_instance = $this->quiz_settings_instance;

		/**
		 * Fires before quiz questions rendered by forminator
		 *
		 * Although it can be used for all addon.
		 * Please keep in mind that if the addon override this method,
		 * then this action won't be triggered.
		 * To be sure please check individual addon documentations.
		 *
		 * @since 1.6.2
		 *
		 * @param int                                          $quiz_id                current Quiz ID
		 * @param Forminator_Addon_Quiz_Settings_Abstract|null $quiz_settings_instance of Addon Quiz Settings
		 */
		do_action(
			'forminator_addon_' . $addon_slug . '_on_before_render_quiz_questions',
			$quiz_id,
			$quiz_settings_instance
		);
	}

	/**
	 * Override this function to execute action after all quiz questions rendered
	 *
	 * If function generate output, it will output-ed
	 * race condition between addon probably happen
	 * its void function, so return value will be ignored, and forminator process will always continue
	 * unless it generates unrecoverable error, so please be careful on extending this function
	 *
	 * @since 1.6.2
	 */
	public function on_after_render_quiz_questions() {
		$addon_slug             = $this->addon->get_slug();
		$quiz_id                = $this->quiz_id;
		$quiz_settings_instance = $this->quiz_settings_instance;

		/**
		 * Fires when addon rendering extra output after connected quiz fields rendered
		 *
		 * Although it can be used for all addon.
		 * Please keep in mind that if the addon override this method,
		 * then this action won't be triggered.
		 * To be sure please check individual addon documentations.
		 *
		 * @since 1.6.2
		 *
		 * @param int                                          $quiz_id                current Form ID
		 * @param Forminator_Addon_Quiz_Settings_Abstract|null $quiz_settings_instance of Addon Quiz Settings
		 */
		do_action(
			'forminator_addon_' . $addon_slug . '_on_after_render_quiz_questions',
			$quiz_id,
			$quiz_settings_instance
		);
	}

	/**
	 * Override this function to execute action after html markup quiz rendered completely
	 *
	 * If function generate output, it will output-ed
	 * race condition between addon probably happen
	 * its void function, so return value will be ignored, and forminator process will always continue
	 * unless it generates unrecoverable error, so please be careful on extending this function
	 *
	 * @since 1.6.2
	 */
	public function on_after_render_quiz() {
		$addon_slug             = $this->addon->get_slug();
		$quiz_id                = $this->quiz_id;
		$quiz_settings_instance = $this->quiz_settings_instance;

		/**
		 * Fires when connected quiz completely rendered
		 *
		 * Although it can be used for all addon.
		 * Please keep in mind that if the addon override this method,
		 * then this action won't be triggered.
		 * To be sure please check individual addon documentations.
		 *
		 * @since 1.6.2
		 *
		 * @param int                                          $quiz_id                current quiz ID
		 * @param Forminator_Addon_Quiz_Settings_Abstract|null $quiz_settings_instance of Addon Quiz Settings
		 */
		do_action(
			'forminator_addon_' . $addon_slug . '_on_after_render_quiz',
			$quiz_id,
			$quiz_settings_instance
		);
	}

	/**
	 * Override this function to execute action on submit quiz
	 *
	 * Return true will continue forminator process,
	 * return false will stop forminator process,
	 * and display error message to user @see Forminator_Addon_Quiz_Hooks_Abstract::get_submit_quiz_error_message()
	 *
	 * @since 1.6.2
	 *
	 * @param $submitted_data
	 *
	 * @return bool
	 */
	public function on_quiz_submit( $submitted_data ) {
		$addon_slug             = $this->addon->get_slug();
		$quiz_id                = $this->quiz_id;
		$quiz_settings_instance = $this->quiz_settings_instance;

		/**
		 * Filter submitted quiz data to be processed by addon
		 *
		 * Although it can be used for all addon.
		 * Please keep in mind that if the addon override this method,
		 * then this filter won't be applied.
		 * To be sure please check individual addon documentations.
		 *
		 * @since 1.6.2
		 *
		 * @param array                                        $submitted_data
		 * @param int                                          $quiz_id                current Quiz ID
		 * @param Forminator_Addon_Quiz_Settings_Abstract|null $quiz_settings_instance Addon Quiz Settings instance
		 */
		$submitted_data = apply_filters(
			'forminator_addon_' . $addon_slug . '_quiz_submitted_data',
			$submitted_data,
			$quiz_id,
			$quiz_settings_instance
		);

		$is_success = true;
		/**
		 * Filter result of quiz submit
		 *
		 * Return `true` if success, or **(string) error message** on fail
		 * Although it can be used for all addon.
		 * Please keep in mind that if the addon override this method,
		 * then this filter won't be applied.
		 * To be sure please check individual addon documentations.
		 *
		 * @since 1.6.2
		 *
		 * @param bool                                         $is_success
		 * @param int                                          $quiz_id                current quiz ID
		 * @param array                                        $submitted_data
		 * @param Forminator_Addon_Quiz_Settings_Abstract|null $quiz_settings_instance Addon quiz Settings instance
		 */
		$is_success = apply_filters(
			'forminator_addon_' . $addon_slug . '_on_quiz_submit_result',
			$is_success,
			$quiz_id,
			$submitted_data,
			$quiz_settings_instance
		);

		// process filter
		if ( true !== $is_success ) {
			// only update `_submit_quiz_error_message` when not empty
			if ( ! empty( $is_success ) ) {
				$this->_submit_quiz_error_message = (string) $is_success;
			}

			return $is_success;
		}

		return $is_success;
	}

	/**
	 * Override this function to add another entry field to storage
	 *
	 * Return an multi array with format (at least, or it will be skipped)
	 * [
	 *  'name' => NAME,
	 *  'value' => VALUE', => can be array/object/scalar, it will serialized on storage
	 * ],
	 * [
	 *  'name' => NAME,
	 *  'value' => VALUE'
	 * ]
	 *
	 * @since          1.6.2
	 *
	 * @param array $submitted_data
	 * @param array $current_entry_fields
	 *
	 * @return array
	 *
	 */
	public function add_entry_fields( $submitted_data, $current_entry_fields = array() ) {
		$addon_slug             = $this->addon->get_slug();
		$quiz_id                = $this->quiz_id;
		$quiz_settings_instance = $this->quiz_settings_instance;

		/**
		 * Filter submitted quiz data to be processed by addon
		 *
		 * Although it can be used for all addon.
		 * Please keep in mind that if the addon override this method,
		 * then this filter might won't be available.
		 * To be sure please check individual addon documentations.
		 *
		 * @since 1.6.2
		 *
		 * @param array                                        $submitted_data
		 * @param int                                          $quiz_id                current Quiz ID
		 * @param Forminator_Addon_Quiz_Settings_Abstract|null $quiz_settings_instance Addon Quiz Settings instance
		 */
		$submitted_data = apply_filters(
			'forminator_addon_' . $addon_slug . '_quiz_submitted_data',
			$submitted_data,
			$quiz_id,
			$quiz_settings_instance
		);

		$quiz_entry_fields = $current_entry_fields;

		/**
		 * Filter current entry fields of quiz to be processed by addon
		 *
		 * Although it can be used for all addon.
		 * Please keep in mind that if the addon override this method,
		 * then this filter might be not available.
		 * To be sure please check individual addon documentations.
		 *
		 * @since 1.6.2
		 *
		 * @param array                                        $quiz_entry_fields
		 * @param array                                        $submitted_data
		 * @param int                                          $quiz_id                current Quiz ID
		 * @param Forminator_Addon_Quiz_Settings_Abstract|null $quiz_settings_instance Addon Quiz Settings instance
		 */
		$quiz_entry_fields = apply_filters(
			'forminator_addon_' . $addon_slug . '_quiz_entry_fields',
			$quiz_entry_fields,
			$submitted_data,
			$quiz_id,
			$quiz_settings_instance
		);

		$entry_fields = array();
		/**
		 * Filter addon entry fields to be saved to entry model
		 *
		 * @since 1.6.2
		 *
		 * @param array                                        $entry_fields
		 * @param int                                          $quiz_id                current quiz ID
		 * @param array                                        $submitted_data
		 * @param Forminator_Addon_Quiz_Settings_Abstract|null $quiz_settings_instance Addon Quiz Settings instance
		 * @param array                                        $quiz_entry_fields      Current entry fields of the quiz
		 */
		$entry_fields = apply_filters(
			'forminator_addon_quiz_' . $addon_slug . '_entry_fields',
			$entry_fields,
			$quiz_id,
			$submitted_data,
			$quiz_settings_instance,
			$quiz_entry_fields
		);

		return $entry_fields;
	}

	/**
	 * Override this function to execute action after entry saved
	 *
	 * Its void function, so return value will be ignored, and forminator process will always continue
	 * unless it generates unrecoverable error, so please be careful on extending this function
	 *
	 * @since 1.6.2
	 *
	 * @param Forminator_Form_Entry_Model $entry_model
	 */
	public function after_entry_saved( Forminator_Form_Entry_Model $entry_model ) {
		$addon_slug             = $this->addon->get_slug();
		$quiz_id                = $this->quiz_id;
		$quiz_settings_instance = $this->quiz_settings_instance;

		/**
		 * Fires when entry already saved on db
		 *
		 * Although it can be used for all addon.
		 * Please keep in mind that if the addon override this method,
		 * then this filter probably might be not available.
		 * To be sure please check individual addon documentations.
		 *
		 * @since 1.6.2
		 *
		 * @param int                                          $quiz_id                current Quiz ID
		 * @param Forminator_Form_Entry_Model                  $entry_model            Forminator Entry Model
		 * @param Forminator_Addon_Quiz_Settings_Abstract|null $quiz_settings_instance of Addon Quiz Settings
		 */
		do_action(
			'forminator_addon_quiz_' . $addon_slug . '_after_entry_saved',
			$quiz_id,
			$entry_model,
			$quiz_settings_instance
		);
	}

	/**
	 * Override this function to display another sub-row on entry detail
	 *
	 * Return a multi array with this format (at least, or it will skipped)
	 * [
	 *  'label' => LABEL,
	 *  'value' => VALUE (string) => its output is on html mode, so you can do styling, but please don't forgot to escape its html when needed
	 * ],
	 * [
	 *  'label' => LABEL,
	 *  'value' => VALUE
	 * ]
	 *
	 * @since 1.6.2
	 *
	 * @param Forminator_Form_Entry_Model $entry_model
	 * @param     array                   $addon_meta_data specific meta_data that added by current addon from @see: add_entry_fields()
	 *
	 * @return array
	 */
	public function on_render_entry( Forminator_Form_Entry_Model $entry_model, $addon_meta_data ) {
		$addon_slug             = $this->addon->get_slug();
		$quiz_id                = $this->quiz_id;
		$quiz_settings_instance = $this->quiz_settings_instance;

		/**
		 *
		 * Filter addon metadata that previously saved on db to be processed
		 *
		 * Although it can be used for all addon.
		 * Please keep in mind that if the addon override this method,
		 * then this filter probably won't be applied.
		 * To be sure please check individual addon documentations.
		 *
		 * @since 1.6.2
		 *
		 * @param array                                        $addon_meta_data
		 * @param int                                          $quiz_id                current Quiz ID
		 * @param Forminator_Form_Entry_Model                  $entry_model            Forminator Entry Model
		 * @param Forminator_Addon_Quiz_Settings_Abstract|null $quiz_settings_instance of Addon Quiz Settings
		 */
		$addon_meta_data = apply_filters(
			'forminator_addon_quiz_' . $addon_slug . '_metadata',
			$addon_meta_data,
			$quiz_id,
			$entry_model,
			$quiz_settings_instance
		);

		$entry_items = array();
		/**
		 * Filter addon row(s) to be displayed on entries page
		 *
		 * Although it can be used for all addon.
		 * Please keep in mind that if the addon override this method,
		 * then this filter probably might be not available.
		 * To be sure please check individual addon documentations.
		 *
		 * @since 1.6.2
		 *
		 * @param array                                        $entry_items            row(s) to be displayed on entries page
		 * @param int                                          $quiz_id                current Quiz ID
		 * @param Forminator_Form_Entry_Model                  $entry_model            Form Entry Model
		 * @param array                                        $addon_meta_data        meta data saved by addon on entry fields
		 * @param Forminator_Addon_Quiz_Settings_Abstract|null $quiz_settings_instance of Addon Quiz Settings
		 */
		$entry_items = apply_filters(
			'forminator_addon_quiz_' . $addon_slug . '_entry_items',
			$entry_items,
			$quiz_id,
			$entry_model,
			$addon_meta_data,
			$quiz_settings_instance
		);

		return $entry_items;
	}

	/**
	 * Override this function to Add another Column on title Row
	 *
	 * This TITLE_ID will be referenced on @see Forminator_Addon_Quiz_Hooks_Abstract::on_export_render_entry_row()
	 *
	 * @example
	 * {
	 *         TITLE_ID_1 => 'TITLE 1',
	 *         TITLE_ID_2 => 'TITLE 2',
	 *         TITLE_ID_3 => 'TITLE 3',
	 * }
	 *
	 * @since 1.6.2
	 * @return array
	 */
	public function on_export_render_title_row() {
		$addon_slug             = $this->addon->get_slug();
		$quiz_id                = $this->quiz_id;
		$quiz_settings_instance = $this->quiz_settings_instance;

		$export_headers = array();
		/**
		 * Filter addon headers on export file
		 *
		 * Although it can be used for all addon.
		 * Please keep in mind that if the addon override this method,
		 * then this filter probably won't be applied.
		 * To be sure please check individual addon documentations.
		 *
		 * @since 1.6.2
		 *
		 * @param array                                        $export_headers         headers to be displayed on export file
		 * @param int                                          $quiz_id                current Quiz ID
		 * @param Forminator_Addon_Quiz_Settings_Abstract|null $quiz_settings_instance of Addon Quiz Settings
		 */
		$export_headers = apply_filters(
			'forminator_addon_quiz_' . $addon_slug . '_export_headers',
			$export_headers,
			$quiz_id,
			$quiz_settings_instance
		);

		return $export_headers;
	}

	/**
	 * Add Additional Column on entry row,
	 *
	 * Use TITLE_ID from @see Forminator_Addon_Quiz_Hooks_Abstract::on_export_render_title_row()
	 *
	 * @example
	 * {
	 *   'TITLE_ID_1' => 'VALUE OF TITLE_1',
	 *   'TITLE_ID_2' => 'VALUE OF TITLE_2',
	 *   'TITLE_ID_3' => 'VALUE OF TITLE_3',
	 * }
	 *
	 * @since 1.6.2
	 *
	 * @param Forminator_Form_Entry_Model $entry_model
	 * @param                             $addon_meta_data
	 *
	 * @return array
	 */
	public function on_export_render_entry( Forminator_Form_Entry_Model $entry_model, $addon_meta_data ) {
		$addon_slug             = $this->addon->get_slug();
		$quiz_id                = $this->quiz_id;
		$quiz_settings_instance = $this->quiz_settings_instance;

		/**
		 *
		 * Filter addon metadata that previously saved on db to be processed
		 *
		 * Although it can be used for all addon.
		 * Please keep in mind that if the addon override this method,
		 * then this filter probably might be not available.
		 * To be sure please check individual addon documentations.
		 *
		 * @since 1.6.2
		 *
		 * @param array                                        $addon_meta_data
		 * @param int                                          $quiz_id                current quiz ID
		 * @param Forminator_Form_Entry_Model                  $entry_model            Forminator Entry Model
		 * @param Forminator_Addon_Quiz_Settings_Abstract|null $quiz_settings_instance of Addon Quiz Settings
		 */
		$addon_meta_data = apply_filters(
			'forminator_addon_quiz_' . $addon_slug . '_metadata',
			$addon_meta_data,
			$quiz_id,
			$entry_model,
			$quiz_settings_instance
		);

		$export_columns = array();

		/**
		 * Filter addon columns to be displayed on export submissions
		 *
		 * Although it can be used for all addon.
		 * Please keep in mind that if the addon override this method,
		 * then this filter probably not available.
		 * To be sure please check individual addon documentations.
		 *
		 * @since 1.6.2
		 *
		 * @param array                                        $export_columns         column to be exported
		 * @param int                                          $quiz_id                current Quiz ID
		 * @param Forminator_Form_Entry_Model                  $entry_model            Form Entry Model
		 * @param array                                        $addon_meta_data        meta data saved by addon on entry fields
		 * @param Forminator_Addon_Quiz_Settings_Abstract|null $quiz_settings_instance of Addon Quiz Settings
		 */
		$export_columns = apply_filters(
			'forminator_addon_quiz_' . $addon_slug . '_export_columns',
			$export_columns,
			$quiz_id,
			$entry_model,
			$addon_meta_data,
			$quiz_settings_instance
		);

		return $export_columns;
	}

	/**
	 * Get Submit quiz error message
	 *
	 * @since 1.6.2
	 * @return string
	 */
	public function get_submit_quiz_error_message() {
		$addon_slug             = $this->addon->get_slug();
		$quiz_id                = $this->quiz_id;
		$quiz_settings_instance = $this->quiz_settings_instance;

		$error_message = $this->_submit_quiz_error_message;
		/**
		 * Filter error message on submit
		 *
		 * Although it can be used for all addon.
		 * Please keep in mind that if the addon override this method,
		 * then this filter probably not be available.
		 * To be sure please check individual addon documentations.
		 *
		 * @since 1.6.2
		 *
		 * @param array                                        $export_columns         column to be exported
		 * @param int                                          $quiz_id                current quiz ID
		 * @param Forminator_Addon_Quiz_Settings_Abstract|null $quiz_settings_instance of Addon quiz Settings
		 */
		$error_message = apply_filters(
			'forminator_addon_' . $addon_slug . '_submit_quiz_error_message',
			$error_message,
			$quiz_id,
			$quiz_settings_instance
		);

		return $error_message;
	}

	/**
	 * Override this function to execute action before submission deleted
	 *
	 * If function generate output, it will output-ed
	 * race condition between addon probably happen
	 * its void function, so return value will be ignored, and forminator process will always continue
	 * unless it generates unrecoverable error, so please be careful on extending this function
	 *
	 * @since 1.6.2
	 *
	 * @param Forminator_Form_Entry_Model $entry_model
	 * @param                             $addon_meta_data
	 */
	public function on_before_delete_entry( Forminator_Form_Entry_Model $entry_model, $addon_meta_data ) {
		$addon_slug             = $this->addon->get_slug();
		$quiz_id                = $this->quiz_id;
		$quiz_settings_instance = $this->quiz_settings_instance;

		/**
		 *
		 * Filter addon metadata that previously saved on db to be processed
		 *
		 * Although it can be used for all addon.
		 * Please keep in mind that if the addon override this method,
		 * then this filter probably not available.
		 * To be sure please check individual addon documentations.
		 *
		 * @since 1.6.2
		 *
		 * @param array                                        $addon_meta_data
		 * @param int                                          $quiz_id                current Quiz ID
		 * @param Forminator_Form_Entry_Model                  $entry_model            Forminator Entry Model
		 * @param Forminator_Addon_Quiz_Settings_Abstract|null $quiz_settings_instance of Addon Quiz Settings
		 */
		$addon_meta_data = apply_filters(
			'forminator_addon_quiz_' . $addon_slug . '_metadata',
			$addon_meta_data,
			$quiz_id,
			$entry_model,
			$quiz_settings_instance
		);

		/**
		 * Fires when connected quiz delete a submission
		 *
		 * Although it can be used for all addon.
		 * Please keep in mind that if the addon override this method,
		 * then this action might be unavailable.
		 * To be sure please check individual addon documentations.
		 *
		 * @since 1.6.2
		 *
		 * @param int                                          $quiz_id                current quiz ID
		 * @param Forminator_Form_Entry_Model                  $entry_model            Forminator Entry Model
		 * @param array                                        $addon_meta_data        addon meta data
		 * @param Forminator_Addon_Quiz_Settings_Abstract|null $quiz_settings_instance of Addon quiz Settings
		 */
		do_action(
			'forminator_addon_quiz_' . $addon_slug . '_on_before_delete_submission',
			$quiz_id,
			$entry_model,
			$addon_meta_data,
			$quiz_settings_instance
		);
	}

	/**
	 * Get Addon meta data, will be recursive if meta data is multiple because of multiple connection added
	 *
	 * @since 1.6.2
	 *
	 * @param        $addon_meta_data
	 * @param        $key
	 * @param string $default
	 *
	 * @return string
	 */
	protected function get_from_addon_meta_data( $addon_meta_data, $key, $default = '' ) {
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

}
