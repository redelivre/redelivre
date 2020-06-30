<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Quizz_Admin
 *
 * @property string            page_edit_nowrong
 * @property Forminator_Module module
 * @property string            page_edit_knowledge
 *
 * @since 1.0
 */
class Forminator_Quizz_Admin extends Forminator_Admin_Module {

	/**
	 * Initialize
	 *
	 * @since 1.0
	 */
	public function init() {
		$this->module              = Forminator_Quizzes::get_instance();
		$this->page                = 'forminator-quiz';
		$this->page_edit_nowrong   = 'forminator-nowrong-wizard';
		$this->page_edit_knowledge = 'forminator-knowledge-wizard';
		$this->page_entries        = 'forminator-quiz-view';
	}

	/**
	 * Include required files
	 *
	 * @since 1.0
	 */
	public function includes() {
		include_once dirname( __FILE__ ) . '/admin-page-new-nowrong.php';
		include_once dirname( __FILE__ ) . '/admin-page-new-knowledge.php';
		include_once dirname( __FILE__ ) . '/admin-page-view.php';
		include_once dirname( __FILE__ ) . '/admin-page-entries.php';
		include_once dirname( __FILE__ ) . '/admin-renderer-entries.php';
	}

	/**
	 * Add module pages to Admin
	 *
	 * @since 1.0
	 */
	public function add_menu_pages() {
		new Forminator_Quizz_Page( $this->page, 'quiz/list', __( 'Quizzes', Forminator::DOMAIN ), __( 'Quizzes', Forminator::DOMAIN ), 'forminator' );
		new Forminator_Quizz_New_NoWrong( $this->page_edit_nowrong, 'quiz/nowrong', __( 'New Quiz', Forminator::DOMAIN ), __( 'New Quiz', Forminator::DOMAIN ), 'forminator' );
		new Forminator_Quizz_New_Knowledge( $this->page_edit_knowledge, 'quiz/knowledge', __( 'New Quiz', Forminator::DOMAIN ), __( 'New Quiz', Forminator::DOMAIN ), 'forminator' );
		new Forminator_Quizz_View_Page( $this->page_entries, 'quiz/entries', __( 'Submissions:', Forminator::DOMAIN ), __( 'View Quizzes', Forminator::DOMAIN ), 'forminator' );
	}

	/**
	 * Remove necessary pages from menu
	 *
	 * @since 1.0
	 */
	public function hide_menu_pages() {
		remove_submenu_page( 'forminator', $this->page_edit_nowrong );
		remove_submenu_page( 'forminator', $this->page_edit_knowledge );
		remove_submenu_page( 'forminator', $this->page_entries );
	}

	/**
	 * Is the type of the quiz "knowledge"
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function is_knowledge_wizard() {
		global $plugin_page;

		return $this->page_edit_knowledge === $plugin_page;
	}

	/**
	 * Is the type of the quiz "no wrong answer"
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function is_nowrong_wizard() {
		global $plugin_page;

		return $this->page_edit_nowrong === $plugin_page;
	}

	/**
	 * Highlight parent page in sidebar
	 *
	 * @deprecated 1.1 No longer used because this function override prohibited WordPress global of $plugin_page
	 * @since      1.0
	 *
	 * @param $file
	 *
	 * @return mixed
	 */
	public function highlight_admin_parent( $file ) {
		_deprecated_function( __METHOD__, '1.1', null );

		return $file;
	}

	/**
	 * Highlight submenu on admin page
	 *
	 * @since 1.1
	 *
	 * @param $submenu_file
	 * @param $parent_file
	 *
	 * @return string
	 */
	public function admin_submenu_file( $submenu_file, $parent_file ) {
		global $plugin_page;

		if ( 'forminator' !== $parent_file ) {
			return $submenu_file;
		}

		if ( $this->page_edit_nowrong === $plugin_page || $this->page_edit_knowledge === $plugin_page || $this->page_entries === $plugin_page ) {
			$submenu_file = $this->page;
		}

		return $submenu_file;
	}

	/**
	 * Pass module defaults to JS
	 *
	 * @since 1.0
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public function add_js_defaults( $data ) {
		$id    = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : null;
		$model = null;

		if ( ! is_null( $id ) ) {
			/** @var  Forminator_Quiz_Form_Model $model */
			$model = Forminator_Quiz_Form_Model::model()->load( $id );
		}

		if ( $this->is_knowledge_wizard() ) {
			$data['formNonce']   = wp_create_nonce( 'forminator_save_quiz' );
			$data['application'] = 'knowledge';

			if ( ! self::is_edit() ) {
				$name = '';
				if ( isset( $_GET['name'] ) ) { // WPCS: CSRF ok.
					$name = sanitize_text_field( $_GET['name'] );
				}

				$data['currentForm'] = array(
					'settings' => array(
						'formName'               => $name,
						'admin-email-recipients' => array(
							get_option( 'admin_email' ),
						),
						'admin-email-title'      => __( "New Quiz Submission for {quiz_name}", Forminator::DOMAIN ),
						'admin-email-editor'     => __(
							"You have a new quiz submission: <br/><br/>{quiz_answer}<br/><br/>Quiz results: <br/>{quiz_result} <br/>---<br/> This message was sent from {site_url}.",
							Forminator::DOMAIN
						),
						'results_behav' => 'after',
						'visual_style' => 'list',
						'forminator-quiz-theme' => 'default',
						'msg_correct' => 'Correct! It was %UserAnswer%.',
						'msg_incorrect' => 'Wrong! It was %CorrectAnswer%, sorry...',
						'msg_count' => 'You got %YourNum%/%Total% correct!',
						// KNOWLEDGE title
						'knowledge-title-color' =>                       '#333333',
						'knowledge-title-font-family' =>                 'Roboto',
						'knowledge-title-font-size' =>                   '42',
						'knowledge-title-font-weight' =>                 '500',
						// KNOWLEDGE description
						'knowledge-description-color' =>                 '#8C8C8C',
						'knowledge-description-font-family' =>           'Roboto',
						'knowledge-description-font-size' =>             '20',
						'knowledge-description-font-weight' =>           '400',
						// KNOWLEDGE question
						'knowledge-question-color' =>                    '#333333',
						'knowledge-question-font-family' =>              'Roboto',
						'knowledge-question-font-size' =>                '24',
						'knowledge-question-font-weight' =>              '500',
						// KNOWLEDGE answer
						'knowledge-answer-background-static' =>          '#FAFAFA',
						'knowledge-answer-background-hover' =>           '#F3FBFE',
						'knowledge-answer-background-active' =>          '#F3FBFE',
						'knowledge-aright-background' =>                 '#F4FCF2',
						'knowledge-awrong-background' =>                 '#FDF2F2',
						'knowledge-answer-border-static' =>              '#EBEDEB',
						'knowledge-answer-border-hover' =>               '#17A8E3',
						'knowledge-answer-border-active' =>              '#17A8E3',
						'knowledge-aright-border' =>                     '#0BC30B',
						'knowledge-awrong-border' =>                     '#DA0000',
						'knowledge-answer-color-static' =>               '#888888',
						'knowledge-answer-color-active' =>               '#333333',
						'knowledge-aright-color' =>                      '#0BC30B',
						'knowledge-awrong-color' =>                      '#DA0000',
						'knowledge-answer-font-size' =>                  '14',
						'knowledge-answer-font-family' =>                'Roboto',
						'knowledge-answer-font-weight' =>                '500',
						'knowledge-answer-check-border-static' =>        '#BFBFBF',
						'knowledge-answer-check-border-active' =>        '#17A8E3',
						'knowledge-answer-check-border-correct' =>       '#0BC30B',
						'knowledge-answer-check-border-incorrect' =>     '#DA0000',
						'knowledge-answer-check-background-static' =>    '#FFFFFF',
						'knowledge-answer-check-background-active' =>    '#17A8E3',
						'knowledge-answer-check-background-correct' =>   '#0BC30B',
						'knowledge-answer-check-background-incorrect' => '#DA0000',
						'knowledge-phrasing-color' =>                    '#4D4D4D',
						'knowledge-phrasing-font-size' =>                '16',
						'knowledge-phrasing-font-family' =>              'Roboto',
						'knowledge-phrasing-font-weight' =>              '700',
						// KNOWLEDGE button
						'knowledge-submit-background-static' =>          '#17A8E3',
						'knowledge-submit-background-hover' =>           '#008FCA',
						'knowledge-submit-background-active' =>          '#008FCA',
						'knowledge-submit-color-static' =>               '#FFFFFF',
						'knowledge-submit-color-hover' =>                '#FFFFFF',
						'knowledge-submit-color-active' =>               '#FFFFFF',
						'knowledge-submit-font-family' =>                'Roboto',
						'knowledge-submit-font-size' =>                  '14',
						'knowledge-submit-font-weight' =>                '500',
						// KNOWLEDGE summary
						'knowledge-summary-color' =>                     '#333333',
						'knowledge-summary-font-family' =>               'Roboto',
						'knowledge-summary-font-size' =>                 '40',
						'knowledge-summary-font-weight' =>               '400',
						// KNOWLEDGE social
						'enable-share' =>                                'on',
						'knowledge-sshare-color' =>                      '#4D4D4D',
						'knowledge-sshare-font-family' =>                'Roboto',
						'knowledge-sshare-font-size' =>                  '20',
						'knowledge-social-facebook' =>                   '#0084BF',
						'knowledge-social-twitter' =>                    '#1DA1F2',
						'knowledge-social-google' =>                     '#DB4437',
						'forminator-knowledge-social-linkedin' =>        '#0084BF',
						'knowledge-social-size' =>                       '36'
					),
				);
			} else {
				// Load stored record
				if ( is_object( $model ) ) {
					$settings = apply_filters( 'forminator_quiz_settings', $model->settings, $model, $data, $this );

					$data['currentForm'] = array(
						'results'   => array(),
						'questions' => $model->questions,
						'settings'  => array_merge(
							$settings,
							array(
								'formName'    => forminator_get_name_from_model( $model ),
								'form_id'     => $model->id,
								'form_status' => $model->status,
								'quiz_title'  => $model->name,
								'version'     => FORMINATOR_VERSION,
							)
						),
					);
				} else {
					$data['currentForm'] = array();
				}
			}
		}

		if ( $this->is_nowrong_wizard() ) {
			$data['formNonce']   = wp_create_nonce( 'forminator_save_quiz' );
			$data['application'] = 'nowrong';

			if ( ! self::is_edit() ) {
				$name = '';
				if ( isset( $_GET['name'] ) ) { // WPCS: CSRF ok.
					$name = sanitize_text_field( $_GET['name'] );
				}

				$data['currentForm'] = array(
					'questions' => array(),
					'results'   => array(),
					'settings'  => array(
						'formName'               => $name,
						'admin-email-recipients' => array(
							get_option( 'admin_email' ),
						),
						'results_behav' => 'after',
						'visual_style' => 'list',
						'forminator-quiz-theme' => 'default',
						'msg_correct' => 'Correct! It was %UserAnswer%.',
						'msg_incorrect' => 'Wrong! It was %CorrectAnswer%, sorry...',
						'msg_count' => 'You got %YourNum%/%Total% correct!',
						// NOWRONG title
						'nowrong-title-settings' =>                      false,
						'nowrong-title-color' =>                         '#333333',
						'nowrong-title-font-family' =>                   'Roboto',
						'nowrong-title-font-size' =>                     '42',
						'nowrong-title-font-weight' =>                   '500',
						// NOWRONG description
						'nowrong-description-settings' =>                false,
						'nowrong-description-color' =>                   '#8C8C8C',
						'nowrong-description-font-family' =>             'Roboto',
						'nowrong-description-font-size' =>               '20',
						'nowrong-description-font-weight' =>             '400',
						// NOWRONG image
						'nowrong-image-settings' =>                      false,
						'nowrong-image-border-color' =>                  '#000000',
						'nowrong-image-border-width' =>                  '0',
						'nowrong-image-border-style' =>                  'solid',
						// NOWRONG question
						'nowrong-question-settings' =>                   false,
						'nowrong-question-font-size' =>                  '24',
						'nowrong-question-font-family' =>                'Roboto',
						'nowrong-question-font-weight' =>                '500',
						// NOWRONG answer
						'nowrong-answer-settings' =>                     false,
						'nowrong-answer-border-static' =>                '#EBEDEB',
						'nowrong-answer-border-hover' =>                 '#17A8E3',
						'nowrong-answer-border-active' =>                '#17A8E3',
						'nowrong-answer-background-static' =>            '#FAFAFA',
						'nowrong-answer-background-hover' =>             '#F3FBFE',
						'nowrong-answer-background-active' =>            '#F3FBFE',
						'nowrong-answer-chkbo-static' =>                 '#BFBFBF',
						'nowrong-answer-chkbo-active' =>                 '#17A8E3',
						'nowrong-answer-color-static' =>                 '#888888',
						'nowrong-answer-color-active' =>                 '#333333',
						'nowrong-answer-font-size' =>                    '14',
						'nowrong-answer-font-family' =>                  'Roboto',
						'nowrong-answer-font-weight' =>                  '500',
						// NOWRONG submit
						'nowrong-submit-background-static' =>            '#17A8E3',
						'nowrong-submit-background-hover' =>             '#008FCA',
						'nowrong-submit-background-active' =>            '#008FCA',
						'nowrong-submit-color-static' =>                 '#FFFFFF',
						'nowrong-submit-color-hover' =>                  '#FFFFFF',
						'nowrong-submit-color-active' =>                 '#FFFFFF',
						'nowrong-submit-font-family' =>                  'Roboto',
						'nowrong-submit-font-size' =>                    '14',
						'nowrong-submit-font-weight' =>                  '500',
						// NOWRONG result
						'nowrong-result-background-main' =>              '#FAFAFA',
						'nowrong-result-background-header' =>            '#FAFAFA',
						'nowrong-result-border-color' =>                 '#17A8E3',
						'nowrong-result-quiz-color' =>                   '#FFFFFF',
						'nowrong-result-quiz-font-family' =>             'Roboto',
						'nowrong-result-quiz-font-size' =>               '15',
						'nowrong-result-quiz-font-weight' =>             '500',
						'nowrong-result-retake-font-family' =>           'Roboto',
						'nowrong-result-retake-font-size' =>             '13',
						'nowrong-result-retake-font-weight' =>           '500',
						'nowrong-result-retake-background-static' =>     '#17A8E3',
						'nowrong-result-retake-background-hover' =>      '#17A8E3',
						'nowrong-result-retake-background-active' =>     '#17A8E3',
						'nowrong-result-background-body' =>              '#EBEDEB',
						'nowrong-result-title-color' =>                  '#333333',
						'nowrong-result-title-font-family' =>            'Roboto',
						'nowrong-result-title-font-size' =>              '15',
						'nowrong-result-title-font-weight' =>            '500',
						'nowrong-result-description-color' =>            '#4D4D4D',
						'nowrong-result-description-font-family' =>      'Roboto',
						'nowrong-result-description-font-size' =>        '13',
						'nowrong-result-description-font-weight' =>      '400',
						// NOWRONG social
						'enable-share' =>                                'on',
					),
				);
			} else {
				// Load stored record
				if ( is_object( $model ) ) {
					unset( $model->settings['priority_order'] );
					$settings = apply_filters( 'forminator_quiz_settings', $model->settings, $model, $data, $this );

					$data['currentForm'] = array(
						'results'   => $model->getResults(),
						'questions' => $model->questions,
						'settings'  => array_merge(
							$settings,
							array(
								'formName'    => forminator_get_name_from_model( $model ),
								'form_id'     => $model->id,
								'form_status' => $model->status,
								'quiz_title'  => $model->name,
								'version'     => FORMINATOR_VERSION,
							)
						),
					);
				} else {
					$data['currentForm'] = array();
				}
			}
		}

		$data['modules']['quizzes'] = array(
			'nowrong_url'   => menu_page_url( $this->page_edit_nowrong, false ),
			'knowledge_url' => menu_page_url( $this->page_edit_knowledge, false ),
			'form_list_url' => menu_page_url( $this->page, false ),
			'preview_nonce' => wp_create_nonce( 'forminator_popup_preview_quizzes' ),
		);

		return apply_filters( 'forminator_quiz_admin_data', $data, $model, $this );
	}

	/**
	 * Localize modules strings
	 *
	 * @since 1.0
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public function add_l10n_strings( $data ) {
		$data['quizzes'] = array(
			'quizzes'                      => __( 'Quizzes', Forminator::DOMAIN ),
			"popup_label"                  => __( "Choose Quiz Type", Forminator::DOMAIN ),
			"results"                      => __( "Results", Forminator::DOMAIN ),
			"questions"                    => __( "Questions", Forminator::DOMAIN ),
			"details"                      => __( "Details", Forminator::DOMAIN ),
			"settings"                     => __( "Settings", Forminator::DOMAIN ),
			"appearance"                   => __( "Appearance", Forminator::DOMAIN ),
			"preview"                      => __( "Preview", Forminator::DOMAIN ),
			"preview_quiz"                 => __( "Preview Quiz", Forminator::DOMAIN ),
			"list"                         => __( "List", Forminator::DOMAIN ),
			"grid"                         => __( "Grid", Forminator::DOMAIN ),
			"visual_style"                 => __( "Visual style", Forminator::DOMAIN ),
			"quiz_title"                   => __( "Quiz Title", Forminator::DOMAIN ),
			"quiz_title_desc"              => __( "Further customize the appearance for quiz title. It appears as result's header.", Forminator::DOMAIN ),
			"title"                        => __( "Title", Forminator::DOMAIN ),
			"title_desc"                   => __( "Further customize appearance for quiz title.", Forminator::DOMAIN ),
			"image_desc"                   => __( "Further customize appearance for quiz featured image.", Forminator::DOMAIN ),
			"enable_styles"                => __( "Enable custom styles", Forminator::DOMAIN ),
			"desc_desc"                    => __( "Further customize appearance for quiz description / intro.", Forminator::DOMAIN ),
			"description"                  => __( "Description / Intro", Forminator::DOMAIN ),
			"feat_image"                   => __( "Featured image", Forminator::DOMAIN ),
			"font_color"                   => __( "Font color", Forminator::DOMAIN ),
			"browse"                       => __( "Browse", Forminator::DOMAIN ),
			"clear"                        => __( "Clear", Forminator::DOMAIN ),
			"results_behav"                => __( "Results behaviour", Forminator::DOMAIN ),
			"rb_description"               => __( "Pick if you want to reveal the correct answer as user finishes question, or only after the whole quiz is completed.", Forminator::DOMAIN ),
			"reveal"                       => __( "When to reveal correct answer", Forminator::DOMAIN ),
			"after"                        => __( "After user picks answer", Forminator::DOMAIN ),
			"before"                       => __( "At the end of whole quiz", Forminator::DOMAIN ),
			"phrasing"                     => __( "Answer phrasing", Forminator::DOMAIN ),
			"phrasing_desc"                => __( "Pick how you want the correct & incorrect answers to read. Use <strong>%UserAnswer%</strong> to pull in the value user selected & <strong>%CorrectAnswer%</strong> to pull in the correct value.",
												  Forminator::DOMAIN ),
			"phrasing_desc_alt"            => __( "Further customize appearance for answer message.", Forminator::DOMAIN ),
			"msg_correct"                  => __( "Correct answer message", Forminator::DOMAIN ),
			"msg_incorrect"                => __( "Incorrect answer message", Forminator::DOMAIN ),
			"msg_count"                    => __( "Final count message", Forminator::DOMAIN ),
			"msg_count_desc"               => __( "Edit the copy of the final result count message that will appear after the quiz is complete. Use <strong>%YourNum%</strong> to display number of correct answers and <strong>%Total%</strong> for total number of questions.",
												  Forminator::DOMAIN ),
			"msg_count_info"               => __( "You can now add some html content here to personalize even more text displayed as Final Count Message. Try it now!", Forminator::DOMAIN ),
			"share"                        => __( "Share on social media", Forminator::DOMAIN ),
			"order"                        => __( "Results priority order", Forminator::DOMAIN ),
			"order_label"                  => __( "Pick priority for results", Forminator::DOMAIN ),
			"order_alt"                    => __( "Quizzes can have even number of scores for 2 or more results, in those scenarios, this order will help determine the result.", Forminator::DOMAIN ),
			"questions_title"              => __( "Questions", Forminator::DOMAIN ),
			"question_desc"                => __( "Further customize appearance for quiz questions.", Forminator::DOMAIN ),
			"result_title"                 => __( "Result title", Forminator::DOMAIN ),
			"result_description"           => __( "Result description", Forminator::DOMAIN ),
			"result_description_desc"      => __( "Further customize the appearance for result description typography.", Forminator::DOMAIN ),
			"result_title_desc"            => __( "Further customize the appearance for result title typography.", Forminator::DOMAIN ),
			"retake_button"                => __( "Retake button", Forminator::DOMAIN ),
			"retake_button_desc"           => __( "Further customize the appearance for retake quiz button.", Forminator::DOMAIN ),
			"validate_form_name"           => __( "Form name cannot be empty! Please pick a name for your quiz.", Forminator::DOMAIN ),
			"validate_form_question"       => __( "Quiz question cannot be empty! Please add questions for your quiz.", Forminator::DOMAIN ),
			"validate_form_answers"        => __( "Quiz answers cannot be empty! Please add some questions.", Forminator::DOMAIN ),
			"validate_form_answers_result" => __( "Result answer cannot be empty! Please select a result.", Forminator::DOMAIN ),
			"validate_form_correct_answer" => __( "This question needs a correct answer. Please, select one before saving or proceeding to next step.", Forminator::DOMAIN ),
			"validate_form_no_answer"      => __( "Please add an answer for this question.", Forminator::DOMAIN ),
			"answer"                       => __( "Answers", Forminator::DOMAIN ),
			"no_answer"                    => __( "You don't have any answer for this question yet.", Forminator::DOMAIN ),
			"answer_desc"                  => __( "Further customize appearance for quiz answers.", Forminator::DOMAIN ),
			"back"                         => __( "Back", Forminator::DOMAIN ),
			"cancel"                       => __( "Cancel", Forminator::DOMAIN ),
			"continue"                     => __( "Continue", Forminator::DOMAIN ),
			"correct_answer"               => __( "Correct answer", Forminator::DOMAIN ),
			"correct_answer_desc"          => __( "Customize appearance for correct answers.", Forminator::DOMAIN ),
			"finish"                       => __( "Finish", Forminator::DOMAIN ),
			"smartcrawl"                   => __( "<strong>Want more control?</strong> <strong><a href='https://premium.wpmudev.org/project/smartcrawl-wordpress-seo/' target='_blank'>SmartCrawl</a></strong> OpenGraph and Twitter Card support lets you choose how your content looks when it’s shared on social media.",
												  Forminator::DOMAIN ),
			"submit"                       => __( "Submit", Forminator::DOMAIN ),
			"submit_desc"                  => __( "Further customize appearance for quiz submit button.", Forminator::DOMAIN ),
			"main_styles"                  => __( "Main styles", Forminator::DOMAIN ),
			"border"                       => __( "Border", Forminator::DOMAIN ),
			"border_desc"                  => __( "Further customize border for result's main container.", Forminator::DOMAIN ),
			"padding"                      => __( "Padding", Forminator::DOMAIN ),
			"background"                   => __( "Background", Forminator::DOMAIN ),
			"background_desc"              => __( "The Results box has three different backgrounds: main container, header background (where quiz title and reload button are placed), and content background (where result title and description are placed). Here you can customize the three of them.",
												  Forminator::DOMAIN ),
			"bg_main"                      => __( "Main BG", Forminator::DOMAIN ),
			"bg_header"                    => __( "Header BG", Forminator::DOMAIN ),
			"bg_content"                   => __( "Content BG", Forminator::DOMAIN ),
			"color"                        => __( "Color", Forminator::DOMAIN ),
			"result_appearance"            => __( "Result's Box", Forminator::DOMAIN ),
			"margin"                       => __( "Margin", Forminator::DOMAIN ),
			"summary"                      => __( "Summary", Forminator::DOMAIN ),
			"summary_desc"                 => __( "Further customize appearance for quiz final count message", Forminator::DOMAIN ),
			"sshare"                       => __( "Sharing text", Forminator::DOMAIN ),
			"sshare_desc"                  => __( "Further customize appearance for share on social media text", Forminator::DOMAIN ),
			"social"                       => __( "Social icons", Forminator::DOMAIN ),
			"social_desc"                  => __( "Further customize appearance for social media icons", Forminator::DOMAIN ),
			"wrong_answer"                 => __( "Wrong answer", Forminator::DOMAIN ),
			"wrong_answer_desc"            => __( "Customize appearance for wrong answers.", Forminator::DOMAIN ),
			"msg_description"              => __( "Use <strong>%UserAnswer%</strong> to pull in the value user selected and <strong>%CorrectAnswer%</strong> to pull in the correct value.",
												  Forminator::DOMAIN ),
			"facebook"                     => __( "Facebook", Forminator::DOMAIN ),
			"twitter"                      => __( "Twitter", Forminator::DOMAIN ),
			"google"                       => __( "Google", Forminator::DOMAIN ),
			"linkedin"                     => __( "LinkedIn", Forminator::DOMAIN ),
			"title_styles"                 => __( "Title Appearance", Forminator::DOMAIN ),
			"enable"                       => __( "Enable", Forminator::DOMAIN ),
			"checkbox_styles"              => __( "Checkbox styles", Forminator::DOMAIN ),
			"main"                         => __( "Main", Forminator::DOMAIN ),
			"header"                       => __( "Header", Forminator::DOMAIN ),
			"content"                      => __( "Content", Forminator::DOMAIN ),
			"quiz_design"                  => __( "Quiz design", Forminator::DOMAIN ),
			"quiz_design_description"      => __( "Choose a pre-made style for your quiz and further customize it's appearance.", Forminator::DOMAIN ),
			"customize_quiz_colors"        => __( "Customize quiz colors", Forminator::DOMAIN ),
			"visual_style_description"     => __( "There are two ways for displaying your quiz answers: grid or list.", Forminator::DOMAIN ),
		);

		$data['quiz_details'] = array(
			'name'                => __( 'Quiz Name', Forminator::DOMAIN ),
			'name_details'        => __( "This won't be displayed on your quiz, but will help you to identify it.", Forminator::DOMAIN ),
			'name_validate'       => __( 'Quiz name cannot be empty! Please, pick a name for your quiz.', Forminator::DOMAIN ),
			'title'               => __( 'Quiz Title', Forminator::DOMAIN ),
			'title_details'       => __( 'This is the main title of your quiz and will be displayed on front.', Forminator::DOMAIN ),
			'image'               => __( 'Featured image', Forminator::DOMAIN ),
			'image_details'       => __( 'Add some nice main image to your quiz.', Forminator::DOMAIN ),
			'description'         => __( 'Description', Forminator::DOMAIN ),
			'description_details' => __( 'Give more information related to your quiz. This content will be displayed on front.' ),
		);

		$data['quiz_appearance'] = array(
			'answer'               => __( 'Answer', Forminator::DOMAIN ),
			'checkbox'             => __( 'Checkbox', Forminator::DOMAIN ),
			'container_border'     => __( 'Container border', Forminator::DOMAIN ),
			'container_background' => __( 'Container background', Forminator::DOMAIN ),
			'customize_main'       => __( 'Customize main colors', Forminator::DOMAIN ),
			'customize_question'   => __( 'Customize question colors', Forminator::DOMAIN ),
			'customize_answer'     => __( 'Customize answer colors', Forminator::DOMAIN ),
			'customize_result'     => __( "Customize result's box colors", Forminator::DOMAIN ),
			'customize_submit'     => __( 'Customize submit button colors', Forminator::DOMAIN ),
			'main_container'       => __( 'Main container', Forminator::DOMAIN ),
			'main_border'          => __( 'Main border', Forminator::DOMAIN ),
			'main_styles'          => __( 'Main styles', Forminator::DOMAIN ),
			'header_styles'        => __( 'Header styles', Forminator::DOMAIN ),
			'content_styles'       => __( 'Content styles', Forminator::DOMAIN ),
			'quiz_title'           => __( 'Quiz Title', Forminator::DOMAIN ),
			'retake_button'        => __( 'Retake button', Forminator::DOMAIN ),
			'result_title'         => __( 'Result title', Forminator::DOMAIN ),
			'quiz_description'     => __( 'Quiz description', Forminator::DOMAIN ),
			'result_description'   => __( 'Result description', Forminator::DOMAIN ),
			'quiz_image'           => __( 'Quiz image', Forminator::DOMAIN ),
			'question'             => __( 'Question', Forminator::DOMAIN ),
			'answer_message'       => __( 'Answer message', Forminator::DOMAIN ),
			'submit_button'        => __( 'Submit Button', Forminator::DOMAIN ),
			'quiz_result'          => __( 'Quiz result', Forminator::DOMAIN ),
			'social_share'         => __( 'Social share', Forminator::DOMAIN ),
			'customize_colors'     => __( 'Customize colors', Forminator::DOMAIN ),
			'customize_typography' => __( 'Customize typography', Forminator::DOMAIN ),
			'checkbox_border'      => __( 'Checkbox border', Forminator::DOMAIN ),
			'checkbox_background'  => __( 'Checkbox background', Forminator::DOMAIN ),
			'checkbox_icon'        => __( 'Checkbox icon', Forminator::DOMAIN ),
			'quiz_title_notice'    => __( "The quiz title appears on result's header.", Forminator::DOMAIN ),
		);

		return $data;
	}
}
