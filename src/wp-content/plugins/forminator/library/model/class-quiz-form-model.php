<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Author: Hoang Ngo
 */
class Forminator_Quiz_Form_Model extends Forminator_Base_Form_Model {

	/**
	 * @var string
	 */
	protected $post_type = 'forminator_quizzes';

	/**
	 * @var array
	 */
	public $results = array();

	/**
	 * @var array
	 */
	public $questions = array();

	/**
	 * @var string
	 */
	public $quiz_type = '';

	/**
	 * @since 1.0
	 * @return array
	 */
	public function get_maps() {
		return array(
			array(
				'type'     => 'meta',
				'property' => 'questions',
				'field'    => 'questions',
			),
			array(
				'type'     => 'meta',
				'property' => 'results',
				'field'    => 'results',
			),
			array(
				'type'     => 'meta',
				'property' => 'quiz_type',
				'field'    => 'quiz_type',
			),
		);
	}

	/**
	 * @since 1.0
	 *
	 * @param $slug
	 *
	 * @return array|bool
	 */
	public function getRightAnswerForQuestion( $slug ) {
		if ( ! empty( $this->questions ) ) {
			foreach ( $this->questions as $question ) {
				if ( $question['slug'] === $slug ) {
					$answers = $question['answers'];
					$picked  = null;
					$index   = - 1;
					foreach ( $answers as $k => $answer ) {
						if ( isset( $answer['toggle'] ) && filter_var( $answer['toggle'], FILTER_VALIDATE_BOOLEAN ) === true ) {
							$picked = $answer;
							$index  = $k;
							break;
						}
					}

					return array( $index, $picked );
				}
			}
		}

		return array( false, false );
	}

	/**
	 * Return questions
	 *
	 * @since 1.0
	 *
	 * @param $slug
	 *
	 * @return mixed
	 */
	public function getQuestion( $slug ) {
		if ( ! empty( $this->questions ) ) {
			foreach ( $this->questions as $question ) {
				if ( $question['slug'] === $slug ) {
					return $question;
				}
			}
		}

		return false;
	}

	/**
	 * Return answer
	 *
	 * @since 1.0
	 *
	 * @param $slug
	 * @param $index
	 *
	 * @return bool
	 */
	public function getAnswer( $slug, $index ) {
		if ( ! empty( $this->questions ) ) {
			foreach ( $this->questions as $question ) {
				if ( $question['slug'] === $slug ) {
					$answers = $question['answers'];

					return $answers[ $index ];
				}
			}
		}

		return false;
	}

	/**
	 * Get result from answer
	 *
	 * @since 1.0
	 *
	 * @param $slug
	 * @param $index
	 *
	 * @return mixed
	 */
	public function getResultFromAnswer( $slug, $index ) {
		$this->getAnswer( $slug, $index );
		$answer = $this->getAnswer( $slug, $index );

		if ( isset( $answer['result'] ) ) {
			return $answer['result'];
		}

		return false;
	}

	/**
	 * @since 1.0
	 * @since 1.3 use results instead of non existent value of priority_order
	 * @return mixed
	 */
	public function getPriority() {
		foreach ( $this->results as $result ) {
			if ( isset( $result['order'] ) && isset( $result['slug'] ) && 0 === (int) $result['order'] ) {
				return $result['slug'];

			}
		}

		return false;
	}

	/**
	 * Return results
	 *
	 * @since 1.0
	 * @return array
	 */
	public function getResults() {
		$results = array();

		if ( empty( $this->results ) ) {
			return $results;
		}

		foreach ( $this->results as $slug => $result ) {
			$results[] = $result;
		}

		return $results;
	}

	/**
	 * Get result
	 *
	 * @since 1.0
	 *
	 * @param $slug
	 *
	 * @return mixed|null
	 */
	public function getResult( $slug ) {
		if ( ! empty( $this->results ) ) {
			foreach ( $this->results as $result ) {
				if ( $result['slug'] === $slug ) {
					return $result;
				}
			}
		}

		return null;
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

		if ( isset( $data['type'] ) ) {
			$form_model->quiz_type = $data['type'];
		}

		//build the field
		$questions = array();
		if ( isset( $data['questions'] ) ) {
			$questions = $data['questions'];
			unset( $data['questions'] );
		}

		$form_model->questions = $questions;

		//build the settings
		if ( isset( $data['settings'] ) ) {
			$settings             = $data['settings'];
			$form_model->settings = $settings;
		}

		return $form_model;
	}

	/**
	 * @since 1.0
	 *
	 * @param int|string $class_name
	 *
	 * @return Forminator_Quiz_Form_Model
	 */
	public static function model( $class_name = __CLASS__ ) { // phpcs:ignore
		return parent::model( $class_name );
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
		$quiz_id        = (int) $this->id;
		$form_settings  = $this->settings;
		$global_enabled = parent::is_ajax_load( $force );

		$enabled = isset( $form_settings['use_ajax_load'] ) ? $this->settings['use_ajax_load'] : false;
		$enabled = filter_var( $enabled, FILTER_VALIDATE_BOOLEAN );

		$enabled = $global_enabled || $enabled;

		/**
		 * Filter is ajax load for Quiz
		 *
		 * @since 1.6.1
		 *
		 * @param bool  $enabled
		 * @param bool  $global_enabled
		 * @param int   $quiz_id
		 * @param array $form_settings
		 *
		 * @return bool
		 */
		$enabled = apply_filters( 'forminator_quiz_is_ajax_load', $enabled, $global_enabled, $quiz_id, $form_settings );

		return $enabled;
	}

	/**
	 * Flag to use `DONOTCACHEPAGE`
	 *
	 * @since 1.6.1
	 * @return bool
	 */
	public function is_use_donotcachepage_constant() {
		$quiz_id        = (int) $this->id;
		$form_settings  = $this->settings;
		$global_enabled = parent::is_use_donotcachepage_constant();

		$enabled = isset( $form_settings['use_donotcachepage'] ) ? $this->settings['use_donotcachepage'] : false;
		$enabled = filter_var( $enabled, FILTER_VALIDATE_BOOLEAN );

		$enabled = $global_enabled || $enabled;

		/**
		 * Filter use `DONOTCACHEPAGE` Quiz
		 *
		 * @since 1.6.1
		 *
		 * @param bool  $enabled
		 * @param bool  $global_enabled
		 * @param int   $quiz_id
		 * @param array $form_settings
		 *
		 * @return bool
		 */
		$enabled = apply_filters( 'forminator_quiz_is_use_donotcachepage_constant', $enabled, $global_enabled, $quiz_id, $form_settings );

		return $enabled;
	}

	/**
	 * Get result of nowrong quiz
	 *
	 * @since 1.6.1
	 *
	 * @param $answer_results
	 *
	 * @return array contains `title`, `order`, `slug` if found, return empty array otherwise
	 */
	public function get_nowrong_result( $answer_results ) {
		/**
		 * $answer_results FORMAT :
		 * {
		 *      'result-id-1' => `COUNT`,
		 *      'result-id-2' => `COUNT`,
		 * }
		 */

		// picking top results
		// sort by value since, count is on value,
		// do reverse sort, to get bigger value on top
		arsort( $answer_results );

		$top_results = array();
		$top_count   = 0;

		foreach ( $answer_results as $result_id => $count ) {
			// FIRST item always have BIGGEST count, means its prioritized
			if ( empty( $top_results ) ) {
				$top_results[] = $result_id;
				$top_count     = $count;
			} else {

				// already in the pool
				if ( in_array( $result_id, $top_results, true ) ) {
					continue;
				}
				// same $top_count found, means the this should be considered too!
				if ( $count === $top_count ) {
					$top_results[] = $result_id;
				} else {

					// if count not same as $top_count, this item onwards can safely ignored
					// since it will always be less than BIGGEST count
					break;
				}
			}
		}

		// somehow top results is empty,
		// we might got bad $answer_results
		if ( empty( $top_results ) ) {
			return array();
		}

		// default top_result is first on the pool
		$top_result_id = $top_results[0];
		$top_result    = $this->getResult( $top_result_id );

		// somehow the result could not be found, odin forbid
		if ( is_null( $top_result ) ) {
			return array();
		}

		// God knows why `order` could not be found,
		// but we might as well set it as 0 so it will get top priority
		// remember smaller `order` value means it gets more prioritized
		$top_priority = isset( $top_result['order'] ) ? (int) $top_result['order'] : 0;

		// > (more than) 1 result happening
		if ( count( $top_results ) > 1 ) {
			foreach ( $top_results as $top_result_id ) {
				$top_result_to_compare = $this->getResult( $top_result_id );

				if ( is_null( $top_result_to_compare ) ) {
					continue;
				}

				if ( ! isset( $top_result_to_compare['order'] ) ) {
					continue;
				}

				$top_priority_to_compare = (int) $top_result_to_compare['order'];

				// remember smaller `order` value means it gets more prioritized
				if ( $top_priority_to_compare < $top_priority ) {
					$top_result   = $top_result_to_compare;
					$top_priority = $top_priority_to_compare;
				}
			}
		}

		return $top_result;

	}

	/**
	 * Get status of prevent_store
	 *
	 * @since 1.6.2
	 *
	 * @return boolean
	 */
	public function is_prevent_store() {
		$quiz_id       = (int) $this->id;
		$quiz_settings = $this->settings;

		// default is always store
		$is_prevent_store = false;

		$is_prevent_store = isset( $this->settings['store'] ) ? $this->settings['store'] : $is_prevent_store;
		$is_prevent_store = filter_var( $is_prevent_store, FILTER_VALIDATE_BOOLEAN );

		/**
		 * Filter is_prevent_store flag of a quiz
		 *
		 * @since 1.6.2
		 *
		 * @param bool  $is_prevent_store
		 * @param int   $quiz_id
		 * @param array $quiz_settings
		 */
		$is_prevent_store = apply_filters( 'forminator_quiz_is_prevent_store', $is_prevent_store, $quiz_id, $quiz_settings );

		return $is_prevent_store;
	}

	/**
	 * Check whether answer is correct for a question on Knowledge Quiz
	 *
	 * @since 1.6.2
	 *
	 * @param string $slug         question slug
	 * @param  int   $answer_index answer index
	 *
	 * @return bool
	 */
	public function is_correct_answer_for_question( $slug, $answer_index ) {
		if ( ! empty( $this->questions ) ) {
			foreach ( $this->questions as $question ) {
				if ( isset( $question['slug'] ) && $question['slug'] === $slug ) {
					$answers = $question['answers'];
					foreach ( $answers as $k => $answer ) {
						if ( isset( $answer['toggle'] ) && filter_var( $answer['toggle'], FILTER_VALIDATE_BOOLEAN ) === true ) {
							if ( (int) $answer_index === (int) $k ) {
								return true;
							}
						}
					}
				}
			}
		}

		return false;
	}

	/**
	 * Find Correct Answers for a question on knowledge quiz
	 *
	 * @since 1.6.2
	 *
	 * @param string $slug question slug
	 *
	 * @return array
	 */
	public function get_correct_answers_for_question( $slug ) {
		$correct_answers = array();
		if ( ! empty( $this->questions ) ) {
			foreach ( $this->questions as $question ) {
				if ( isset( $question['slug'] ) && $question['slug'] === $slug ) {
					$answers = $question['answers'];
					foreach ( $answers as $k => $answer ) {
						if ( isset( $answer['toggle'] ) && filter_var( $answer['toggle'], FILTER_VALIDATE_BOOLEAN ) === true ) {
							$correct_answers[] = $answer;
						}
					}
				}
			}
		}

		return $correct_answers;
	}

	/**
	 * Check whether entry share-able
	 *
	 * @since 1.7
	 * @return bool
	 */
	public function is_entry_share_enabled() {
		$quiz_id        = (int) $this->id;
		$quiz_settings  = $this->settings;
		$global_enabled = parent::is_entry_share_enabled();

		$enabled = isset( $quiz_settings['enable-share'] ) ? $quiz_settings['enable-share'] : false;
		$enabled = filter_var( $enabled, FILTER_VALIDATE_BOOLEAN );

		$enabled = $global_enabled || $enabled;

		/**
		 * Filter is entry share enabled for Quiz
		 *
		 * @since 1.7
		 *
		 * @param bool  $enabled
		 * @param bool  $global_enabled
		 * @param int   $quiz_id
		 * @param array $form_settings
		 *
		 * @return bool
		 */
		$enabled = apply_filters( 'forminator_quiz_is_result_share_enabled', $enabled, $global_enabled, $quiz_id, $quiz_settings );

		return $enabled;
	}
}
