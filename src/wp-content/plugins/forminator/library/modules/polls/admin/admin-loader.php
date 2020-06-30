<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Poll_Admin
 *
 * @property Forminator_Polls module
 * @since 1.0
 */
class Forminator_Poll_Admin extends Forminator_Admin_Module {

	/**
	 * Init
	 *
	 * @since 1.0
	 */
	public function init() {
		$this->module       = Forminator_Polls::get_instance();
		$this->page         = 'forminator-poll';
		$this->page_edit    = 'forminator-poll-wizard';
		$this->page_entries = 'forminator-poll-view';
	}

	/**
	 * Include files
	 *
	 * @since 1.0
	 */
	public function includes() {
		include_once dirname( __FILE__ ) . '/admin-page-new.php';
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
		new Forminator_Poll_Page( 'forminator-poll', 'poll/list', __( 'Polls', Forminator::DOMAIN ), __( 'Polls', Forminator::DOMAIN ), 'forminator' );
		new Forminator_Poll_New_Page( 'forminator-poll-wizard', 'poll/wizard', __( 'New Poll', Forminator::DOMAIN ), __( 'New Poll', Forminator::DOMAIN ), 'forminator' );
		new Forminator_Poll_View_Page( 'forminator-poll-view', 'poll/entries', __( 'Submissions:', Forminator::DOMAIN ), __( 'View Poll', Forminator::DOMAIN ), 'forminator' );
	}

	/**
	 * Remove necessary pages from menu
	 *
	 * @since 1.0
	 */
	public function hide_menu_pages() {
		remove_submenu_page( 'forminator', 'forminator-poll-wizard' );
		remove_submenu_page( 'forminator', 'forminator-poll-view' );
	}

	/**
	 * Pass module defaults to JS
	 *
	 * @since 1.0
	 * @param $data
	 *
	 * @return mixed
	 */
	public function add_js_defaults( $data ) {
		$model = null;
		if ( $this->is_admin_wizard() ) {
			$data['application'] = 'poll';
			$data['formNonce'] = wp_create_nonce( 'forminator_save_poll' );
			if ( ! self::is_edit() ) {
				$name     = '';
				if ( isset( $_GET['name'] ) ) { // WPCS: CSRF ok.
					$name = sanitize_text_field( $_GET['name'] );
				}

				$data['currentForm'] = array(
					'answers'  => array(),
					'settings' => array(
						'formName'               => $name,
						'admin-email-recipients' => array(
							get_option( 'admin_email' ),
						),
						'admin-email-title'      => __( "New Poll submission for {poll_name}", Forminator::DOMAIN ),
						'admin-email-editor'     => __(
							"You have a new poll submission: <br/><br/>{poll_answer}<br/><br/>Current results: <br/>{poll_result} <br/>---<br/> This message was sent from {site_url}.",
							Forminator::DOMAIN
						),
					),
				);

			} else {
				$id    = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : null;
				if ( ! is_null( $id ) ) {
					$model = Forminator_Poll_Form_Model::model()->load( $id );
				}
				$answers = array();
				if ( is_object( $model ) ) {
					foreach ( (array) $model->get_fields() as $field ) {
						$a = array(
							'title'      => $field->title,
							'element_id' => $field->element_id,
							'color'      => $field->color,
						);
						if ( filter_var( $field->use_extra, FILTER_VALIDATE_BOOLEAN ) === true ) {
							$a['use_extra'] = true;
							$a['extra']     = $field->extra;
						}
						$answers[] = $a;
					}
				}

				// Load stored record
				$settings = apply_filters( 'forminator_poll_settings', $model->settings, $model, $data, $this );

				$data['currentForm'] = array(
					'answers'  => $answers,
					'settings' => array_merge(
						$settings,
						array(
							'form_id'     => $model->id,
							'form_name'   => $model->name,
							'form_status' => $model->status,
						)
					),
				);
			}
		}

		$data['modules']['polls'] = array(
			'new_form_url'  => menu_page_url( $this->page_edit, false ),
			'form_list_url' => menu_page_url( $this->page, false ),
			'preview_nonce' => wp_create_nonce( 'forminator_popup_preview_polls' )
		);

		return apply_filters( 'forminator_poll_admin_data', $data, $model, $this );
	}

	/**
	 * Localize modules
	 *
	 * @since 1.0
	 * @param $data
	 *
	 * @return mixed
	 */
	public function add_l10n_strings( $data ) {

		$data['polls'] = array(
			'poll'								=> __( 'Poll', Forminator::DOMAIN ),

			// Appearance » Poll results behaviour
			'poll_results_behav'				=> __( 'Poll results behaviour', Forminator::DOMAIN ),
			'link_on'							=> __( 'Link on poll', Forminator::DOMAIN ),
			'show_after'						=> __( 'Show after voted', Forminator::DOMAIN ),
			'not_show'							=> __( 'Do not show', Forminator::DOMAIN ),

			// Appearance » Poll results style
			'poll_results_style'				=> __( 'Poll results style', Forminator::DOMAIN ),
			"chart_bar"							=> __( "Bar chart", Forminator::DOMAIN ),
			"chart_pie"							=> __( "Pie chart", Forminator::DOMAIN ),

			// Appearance » Submission
			'submission'						=> __( 'Submission', Forminator::DOMAIN ),
			'submission_notice'					=> __( 'Enable AJAX to prevent refresh while submitting poll data.', Forminator::DOMAIN ),
			'enable_ajax'						=> __( 'Enable AJAX', Forminator::DOMAIN ),

			// Appearance » Poll votes count
			'poll_votes_count'					=> __( 'Poll votes count', Forminator::DOMAIN ),
			'show_votes'						=> __( 'Show number of votes', Forminator::DOMAIN ),
			'poll_votes_count_description'		=> __( 'Enable this option to display number of votes on Bar Chart results.', Forminator::DOMAIN ),

			// Appearance » Poll votes limit
			'poll_votes_limit'					=> __( 'Poll votes limit', Forminator::DOMAIN ),
			'enable_limit'						=> __( 'Allow same visitor to vote more than once', Forminator::DOMAIN ),
			'how_long'							=> __( 'How long before user can vote again', Forminator::DOMAIN ),

			// Appearance » Poll privacy
			'poll_privacy'						=> __( 'Poll privacy', Forminator::DOMAIN ),
			'how_long_privacy'					=> __( 'How long will you retain user IP address', Forminator::DOMAIN ),
			'enable_ip_address_retention'		=> __( "Enable IP address retention", Forminator::DOMAIN ),

			// Appearance » Poll design
			'poll_design'						=> __( 'Poll design', Forminator::DOMAIN ),
			'poll_design_description'			=> __( "Choose a pre-made style for your poll and further customize it's appearance", Forminator::DOMAIN ),
			'vanilla_message'					=> __( 'Vanilla Theme will provide you a clean design (with no styles) and simple markup.', Forminator::DOMAIN ),
			'customize_poll_colors'				=> __( 'Customize poll colors', Forminator::DOMAIN ),
			'customize_poll_container'			=> __( 'Customize poll container', Forminator::DOMAIN ),
			'enable_box_shadow'					=> __( 'Add box shadow to your poll container', Forminator::DOMAIN ),

			// Appearance » Customize poll colors
			'poll_container'					=> __( 'Poll container', Forminator::DOMAIN ),
			'poll_content'						=> __( 'Poll content', Forminator::DOMAIN ),
			'description_color'					=> __( 'Description color', Forminator::DOMAIN ),
			'question_color'					=> __( 'Question color', Forminator::DOMAIN ),
			'poll_answer'						=> __( 'Poll answer', Forminator::DOMAIN ),
			'custom_answer'						=> __( 'Custom answer', Forminator::DOMAIN ),
			'poll_button'						=> __( 'Poll button', Forminator::DOMAIN ),
			'poll_link'							=> __( 'Poll link', Forminator::DOMAIN ),

			// CLEAN-UP (OLD)
			"add_answer"					 => __( "Add Answer", Forminator::DOMAIN ),
			"answer_placeholder"             => __( "Enter poll answer", Forminator::DOMAIN ),
			"custom_input_placeholder_label" => __( "Custom input placeholder", Forminator::DOMAIN ),
			"custom_input_placeholder"       => __( "Type placeholder here...", Forminator::DOMAIN ),
			"add_custom_field"               => __( "Add custom input field", Forminator::DOMAIN ),
			"remove_custom_field"            => __( "Remove custom input field", Forminator::DOMAIN ),
			"delete_answer"                  => __( "Delete answer", Forminator::DOMAIN ),
			"details"                        => __( "Details", Forminator::DOMAIN ),
			"appearance"                     => __( "Appearance", Forminator::DOMAIN ),
			"preview"                        => __( "Preview", Forminator::DOMAIN ),
			"details_title"                  => __( "Details", Forminator::DOMAIN ),
			"poll_title"                     => __( "Title", Forminator::DOMAIN ),
			"poll_desc"                      => __( "Description", Forminator::DOMAIN ),
			"poll_question"                  => __( "Question", Forminator::DOMAIN ),
			"poll_button"                    => __( "Button label", Forminator::DOMAIN ),
			"poll_title_placeholder"         => __( "Enter title", Forminator::DOMAIN ),
			"poll_desc_placeholder"          => __( "Enter description", Forminator::DOMAIN ),
			"poll_question_placeholder"      => __( "Enter question title", Forminator::DOMAIN ),
			"poll_button_placeholder"			=> __( "E.g. Vote", Forminator::DOMAIN ),
			"appearance_title"					=> __( "Poll Appearance", Forminator::DOMAIN ),

			"validate_form_name"				=> __( "Form name cannot be empty! Please pick a name for your poll.", Forminator::DOMAIN ),
			"validate_form_question"			=> __( "Poll question cannot be empty! Please add questions for your poll.", Forminator::DOMAIN ),
			"validate_form_answers"				=> __( "Poll answers cannot be empty! Please add answers to your poll.", Forminator::DOMAIN ),
			"back"								=> __( "Back", Forminator::DOMAIN ),
			"cancel"							=> __( "Cancel", Forminator::DOMAIN ),
			"continue"							=> __( "Continue", Forminator::DOMAIN ),
			"finish"							=> __( "Finish", Forminator::DOMAIN ),

			"poll_title_desc"					=> __( "This name won't be displayed on your poll, but will help you to identify it.", Forminator::DOMAIN ),
			"poll_question_desc"				=> __( "This is the question you will be asking to users.", Forminator::DOMAIN ),

			"answer_color"						=> __( "Answer (font color)", Forminator::DOMAIN ),
			"button_styles"						=> __( "Button styles", Forminator::DOMAIN ),
			"results_link"						=> __( "Results link", Forminator::DOMAIN ),
			"results_link_hover"				=> __( "Results link (hover)", Forminator::DOMAIN ),
			"results_link_active"				=> __( "Results link (active)", Forminator::DOMAIN ),
		);

		return $data;
	}
}
