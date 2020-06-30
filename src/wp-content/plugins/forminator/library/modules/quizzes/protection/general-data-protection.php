<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Quiz_General_Data_Protection
 *
 * General Data Protection Applied for Quiz
 *
 * @since 1.6.2
 */
class Forminator_Quiz_General_Data_Protection extends Forminator_General_Data_Protection {

	public function __construct() {
		parent::__construct( __( 'Forminator Quizzes', Forminator::DOMAIN ) );
	}

	/**
	 * Add Privacy Message
	 *
	 * @since 1.0.6
	 *
	 * @return string
	 */
	public function get_privacy_message() {
		ob_start();
		include dirname( __FILE__ ) . '/policy-text.php';
		$content = ob_get_clean();
		$content = apply_filters( 'forminator_quiz_privacy_policy_content', $content );

		return $content;
	}

	/**
	 * Clean up quiz submissions
	 *
	 * @since 1.6.2
	 *
	 * @return bool
	 */
	public function personal_data_cleanup() {

		$global_retain_number = get_option( 'forminator_retain_quiz_submissions_interval_number', 0 );
		$global_retain_unit   = get_option( 'forminator_retain_quiz_submissions_interval_unit', 'days' );


		$quiz_status = 'any';

		/**
		 * Filter quiz status to be processed for data cleanup
		 *
		 * @param string $quiz_status
		 *
		 * @return string
		 */
		$quiz_status = apply_filters( 'forminator_quiz_general_data_protection_cleanup_quiz_status', $quiz_status );

		/**
		 * Get all quizzes
		 */
		$quizzes = Forminator_Quiz_Form_Model::model()->get_all_models( $quiz_status );

		/** @var Forminator_Quiz_Form_Model[] $models */
		$models = isset( $quizzes['models'] ) && is_array( $quizzes['models'] ) ? $quizzes['models'] : array();

		/**
		 * walk through quizzes
		 */
		foreach ( $models as $model ) {
			if ( ! $model instanceof Forminator_Quiz_Form_Model ) {
				continue;
			}

			$settings = $model->settings;

			/**
			 * Find out whether its overridden
			 */
			$is_overridden = false;
			if ( isset( $settings['enable-submissions-retention'] ) ) {
				$is_overridden = filter_var( $settings['enable-submissions-retention'], FILTER_VALIDATE_BOOLEAN );
			}


			// use overridden settings
			if ( $is_overridden ) {
				$retain_number = 0;
				if ( isset( $settings['submissions-retention-number'] ) ) {
					$retain_number = intval( $settings['submissions-retention-number'] );
				}

				$retain_unit = 'days';
				if ( isset( $settings['submissions-retention-unit'] ) ) {
					$retain_unit = $settings['submissions-retention-unit'];
				}

			} else {
				// Use GLOBAL settings
				$retain_number = $global_retain_number;
				$retain_unit   = $global_retain_unit;

			}

			// Time unit valid ?
			if ( ! $this->is_cleanable_time_unit( $retain_number, $retain_unit ) ) {
				continue;
			}

			// start deleting
			$retain_time = strtotime( '-' . $retain_number . ' ' . $retain_unit, current_time( 'timestamp' ) );
			$retain_time = date_i18n( 'Y-m-d H:i:s', $retain_time );
			$this->delete_older_entries( $model->id, $retain_time );

		}


		return true;
	}

	/**
	 * Time unit validation for entry to be able cleanup-ed
	 *
	 * @param int    $retain_number
	 * @param string $retain_unit
	 *
	 * @return bool
	 */
	private function is_cleanable_time_unit( $retain_number, $retain_unit ) {
		if ( $retain_number <= 0 ) {
			// set to forever
			return false;
		}

		$possible_units = array(
			'days',
			'weeks',
			'months',
			'years',
		);

		if ( ! in_array( $retain_unit, $possible_units, true ) ) {
			// invalid unit
			return false;
		}

		return true;
	}

	/**
	 * Delete older entries
	 *
	 * @param int    $quiz_id
	 * @param string $retain_time
	 */
	private function delete_older_entries( $quiz_id, $retain_time ) {
		$entry_ids = Forminator_Form_Entry_Model::get_older_entry_ids_of_form_id( $quiz_id, $retain_time );
		foreach ( $entry_ids as $entry_id ) {
			$entry_model = new Forminator_Form_Entry_Model( $entry_id );
			Forminator_Form_Entry_Model::delete_by_entry( $entry_model->form_id, $entry_id );
		}
	}
}
