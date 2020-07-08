<?php

/**
 * Front render class for custom forms
 *
 * @since 1.0
 */
class Forminator_Poll_Front extends Forminator_Render_Form {

	/**
	 * Class instance
	 *
	 * @var Forminator_Render_Form|null
	 */
	private static $instance = null;

	/**
	 * Scripts of graph results
	 *
	 * @var array
	 */
	private static $graph_result_scripts = array();

	/**
	 * @var array
	 */
	private $forms_properties = array();

	/**
	 * Default Combination of Chart Colors
	 *
	 * @var array
	 */
	public static $default_chart_colors = array( '#F4B414', '#1ABC9C', '#17A8E3', '#18485D', '#D30606' );

	/**
	 * Model
	 *
	 * @var Forminator_Poll_Form_Model|null
	 */
	public $model = null;

	protected $ajax_load_action = 'forminator_load_poll';

	/**
	 * Return class instance
	 *
	 * @since 1.0
	 * @return Forminator_Poll_Front
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initialize method
	 *
	 * @since 1.0
	 */
	public function init() {
		add_shortcode( 'forminator_poll', array( $this, 'render_shortcode' ) );
	}

	/**
	 * Render shortcode
	 *
	 * @since 1.0
	 *
	 * @param array $atts
	 *
	 * @return string
	 */
	public function render_shortcode( $atts = array() ) {

		//use already created instance if already available
		$view = self::get_instance();

		if ( ! isset( $atts['id'] ) ) {
			return $view->message_required();
		}

		$is_preview = isset( $atts['is_preview'] ) ? $atts['is_preview'] : false;
		$is_preview = filter_var( $is_preview, FILTER_VALIDATE_BOOLEAN );
		$is_preview = apply_filters( 'forminator_render_shortcode_is_preview', $is_preview );

		$preview_data = isset( $atts['preview_data'] ) ? $atts['preview_data'] : array();

		ob_start();

		$view->display( $atts['id'], $is_preview, $preview_data );
		$view->ajax_loader( $is_preview, $preview_data );

		return ob_get_clean();
	}


	/**
	 * Display form method
	 *
	 * @since 1.0
	 *
	 * @param      $id
	 * @param bool $is_preview
	 * @param bool $data
	 * @param bool $hide If true, display: none will be added on the form markup and later removed with JS
	 */
	public function display( $id, $is_preview = false, $data = false, $hide = true ) {

		if ( $data && ! empty( $data ) ) {

			// New form, we have to update the form id
			$has_id = filter_var( $id, FILTER_VALIDATE_BOOLEAN );

			if ( ! $has_id && isset( $data['settings']['form_id'] ) ) {
				$id = $data['settings']['form_id'];
			}

			$this->model = Forminator_Poll_Form_Model::model()->load_preview( $id, $data );

			// its preview!
			$this->model->id = $id;

		} else {

			$this->model = Forminator_Poll_Form_Model::model()->load( $id );

			if ( ! $this->model instanceof Forminator_Poll_Form_Model ) {
				return;
			}
		}

		$this->maybe_define_cache_constants();

		// TODO: make preview and ajax load working similar
		$is_ajax_load = $this->is_ajax_load( $is_preview );

		// Load assets conditionally
		$assets = new Forminator_Assets_Enqueue_Poll( $this->model, $is_ajax_load );
		$assets->load_assets();

		if ( $is_ajax_load && $this->model->current_user_can_vote() ) {

			$this->generate_render_id( $id );
			$this->get_form_placeholder( $id, true );

			$get_module_type   = $this->get_form_type();
			$get_module_design = $this->get_form_design();
			$get_form_settings = $this->get_form_settings();


			wp_enqueue_script( 'google-charts', 'https://www.gstatic.com/charts/loader.js', array( 'jquery' ), '1.0', false );

			return;
		}

		if ( $this->is_displayable( $is_preview ) ) {

			echo $this->get_html( $hide, $is_preview );// wpcs xss ok.

			$this->print_styles();

			wp_enqueue_script( 'google-charts', 'https://www.gstatic.com/charts/loader.js', array( 'jquery' ), '1.0', false );

			add_action( 'wp_footer', array( $this, 'forminator_render_front_scripts' ), 9999 );
			add_action( 'wp_footer', array( $this, 'graph_scripts' ), 100 );
		}
	}

	/**
	 * Return form fields
	 *
	 * @since 1.0
	 * @return array|mixed
	 */
	public function get_fields() {

		if ( is_object( $this->model ) ) {
			return $this->model->get_fields_grouped();
		} else {
			return $this->message_not_found();
		}
	}

	/**
	 * Poll question
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_poll_question() {

		if ( is_object( $this->model ) && isset( $this->model->settings['poll-question'] ) ) {
			return $this->model->settings['poll-question'];
		} else {
			return '';
		}
	}

	/**
	 * Poll Description
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_poll_description() {

		if ( is_object( $this->model ) && isset( $this->model->settings['poll-description'] ) ) {
			return $this->model->settings['poll-description'];
		} else {
			return '';
		}
	}

	/**
	 * Poll Image
	 *
	 * @since 1.5.3
	 * @return string
	 */
	public function get_poll_image() {

		if ( is_object( $this->model ) && isset( $this->model->settings['poll-image'] ) ) {
			return $this->model->settings['poll-image'];
		} else {
			return '';
		}
	}

	/**
	 * Poll header
	 *
	 * @since 1.0
	 * @return string
	 */
	public function render_form_header() {

		$html = '<div class="forminator-response-message" aria-hidden="true">';

			ob_start();
			do_action( 'forminator_poll_post_message', $this->model->id ); // prints html, so we need to capture this

			if ( isset( $_REQUEST['saved'] ) && ! isset( $_REQUEST['results'] ) ) { // WPCS: CSRF OK

				if (
					isset( $_REQUEST['form_id'] ) && $_REQUEST['form_id'] === $this->model->id // WPCS: CSRF OK
					&& isset( $_REQUEST['render_id'] ) // WPCS: CSRF OK
					&& $_REQUEST['render_id'] === self::$render_ids[ $this->model->id ] // WPCS: CSRF OK
				) {

					$this->track_views = false; ?>

					<p><?php esc_html_e( 'Your vote has been saved.', Forminator::DOMAIN ); ?></p>

				<?php }
			} else {

				if (
					! $this->is_preview &&
					! $this->model->current_user_can_vote() &&
					! isset( $_REQUEST['action'] ) // WPCS: CSRF OK
				) {

					$this->track_views = false; ?>

					<p><?php esc_html_e( 'You have already voted for this poll.', Forminator::DOMAIN ); ?></p>

				<?php }
			}

			$html .= ob_get_clean();

		$html .= '</div>';

		$image       = $this->get_poll_image();
		$question    = $this->get_poll_question();
		$description = $this->get_poll_description();

		if (
			! empty( $question ) ||
			! empty( $image ) ||
			! empty( $description )
		) {

			$html .= '<div class="forminator-poll-header">';

				if ( ! empty( $question ) ) {
					$html .= sprintf( '<span class="forminator-question forminator-poll--question">%s</span>', $question );
				}

				if ( ! empty( $description ) ) {
					$html .= sprintf( '<span class="forminator-description">%s</span>', $description );
				}

				if ( ! empty( $image ) ) {
					$html .= sprintf( '<img class="forminator-image" src="%s" role="img" aria-hidden="true" />', esc_attr( $image ) );
				}

			$html .= '</div>';

		}

		return apply_filters( 'forminator_poll_header', $html, $this );

	}

	/**
	 * Poll question
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_submit_button_text() {

		if (
			is_object( $this->model ) &&
			isset( $this->model->settings['poll-button-label'] ) &&
			! empty( $this->model->settings['poll-button-label'] )
		) {
			return $this->model->settings['poll-button-label'];
		} else {
			return __( 'Submit', Forminator::DOMAIN );
		}
	}

	/**
	 * Button markup
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_button_markup( $form_settings = array() ) {

		// https://app.asana.com/0/385581670491499/789649735369091/f
		$disabled = '';
		$design   = $this->get_form_design();

		// If it's on admin then bypass current_user_can_vote
		if (
			is_object( $this->model ) &&
			( $this->is_preview || $this->model->current_user_can_vote() )
		) {

			$button = $this->get_submit_button_text();

			$html   = '<div class="forminator-poll-footer forminator-poll--actions">';

				$html .= sprintf(
					'<button class="forminator-button forminator-button-submit" %s>',
					$disabled
				);

					$html .= sprintf(
						'<span>%s</span>',
						$button
					);

					$html .= '<i class="forminator-icon-loader forminator-loading" aria-hidden="true"></i>';

					$html .=  ( 'material' === $design ) ? '<span aria-hidden="true"></span>' : '';

				$html .= '</button>';

				if ( isset( $_REQUEST['saved'] ) || $this->show_link() ) { // WPCS: CSRF OK

					$url = '';

					// Fallback, disable view results in Preview
					if ( $this->is_preview ) {
						$url = '#';
					} else {
						$url = add_query_arg(
							array(
								'results'   => 'true',
								'form_id'   => $this->model->id,
								'render_id' => self::$render_ids[ $this->model->id ],
							),
							$url
						);
					}

					if ( 0 === Forminator_Form_Entry_Model::count_entries( $this->model->id ) ) {

						$html .= sprintf(
							'<span class="forminator-note">%s</span>',
							__( 'No votes yet', Forminator::DOMAIN )
						);
					} else {

						$html .= sprintf(
							'<a href="%s" class="forminator-link">%s</a>',
							esc_url( $url ),
							esc_html__( 'View results', Forminator::DOMAIN )
						);
					}
				}

			$html .= '</div>';

			return apply_filters( 'forminator_render_button_markup', $html, $button );

		} else {

			$html = '<div class="forminator-poll-footer forminator-poll--actions">';

				if ( $this->show_link() ) {
					$url = '';
					// Fallback, disable view results in Preview
					if ( $this->is_preview ) {
						$url = '#';
					} else {
						$url = add_query_arg(
							array(
								'results'   => 'true',
								'form_id'   => $this->model->id,
								'render_id' => self::$render_ids[ $this->model->id ],
							),
							$url
						);
					}
					$html .= sprintf( '<a href="%s">%s</a>', esc_url( $url ), __( 'View results', Forminator::DOMAIN ) );
				}

			$html .= '</div>';

			return apply_filters( 'forminator_render_button_disabled_markup', $html, $this );
		}
	}

	/**
	 * Return Poll ID required message
	 *
	 * @since 1.0
	 * @return string
	 */
	public function message_required() {
		return __( "Poll ID attribute is required!", Forminator::DOMAIN );
	}

	/**
	 * Return From ID not found message
	 *
	 * @since 1.0
	 * @return string
	 */
	public function message_not_found() {
		return __( "Poll not found!", Forminator::DOMAIN );
	}

	/**
	 * Extra form classes for ajax
	 *
	 * @since 1.0
	 * @return string
	 */
	public function form_extra_classes() {

		$classes = '';
		$ajax_form = $this->is_ajax_submit();

		if ( $ajax_form || $this->is_preview ) {
			$classes .= ' forminator_ajax';
		}

		if ( is_object( $this->model ) && ! $this->is_preview && ! $this->model->current_user_can_vote() ) {
			$classes .= ' forminator-poll-disabled';
		}

		return apply_filters( 'forminator_polls_form_extra_classes', $classes, $this );
	}

	/**
	 * Return before wrapper markup
	 *
	 * @since 1.0
	 *
	 * @param $wrapper
	 *
	 * @return string
	 */
	public function render_wrapper_before( $wrapper ) {

		$html = '<fieldset role="radiogroup" class="forminator-field">';

		return apply_filters( 'forminator_before_wrapper_markup', $html );

	}

	/**
	 * Return after wrapper markup
	 *
	 * @since 1.0
	 *
	 * @param $wrapper
	 *
	 * @return mixed
	 */
	public function render_wrapper_after( $wrapper ) {

		$html = '</fieldset>';

		return apply_filters( 'forminator_after_wrapper_markup', $html );

	}

	/**
	 * Return fields markup
	 *
	 * @since 1.0
	 *
	 * @param bool $render
	 *
	 * @return string
	 */
	public function render_fields( $render = true ) {

		$html    = '';
		$wrappers = $this->get_fields();

		if ( ! empty( $wrappers ) ) {

			$html .= '<div class="forminator-poll-body">';

				foreach ( $wrappers as $key => $wrapper ) {

					if ( ! isset( $wrapper['fields'] ) ) {
						return;
					}

					// Render before wrapper markup
					$html .= $this->render_wrapper_before( $wrapper );

						foreach ( $wrapper['fields'] as $k => $field ) {

							if ( ! empty( $field['title'] ) ) {

								$uniq_id = uniqid();
								do_action( 'forminator_before_field_render', $field );

								// Render field
								$html .= $this->render_field_radio( $field, $uniq_id );

								do_action( 'forminator_after_field_render', $field );

								$use_extra = Forminator_Field::get_property( 'use_extra', $field, false );
								$use_extra = filter_var( $use_extra, FILTER_VALIDATE_BOOLEAN );

								if ( $use_extra ) {

									// Render before field markup
									$html .= '<div class="forminator-field forminator-hidden" style="margin-left: 30px;" aria-hidden="true">';

										$html .= $this->render_extra_field( $field, $uniq_id );

									// Render after field markup
									$html .= '</div>';

								}
							}
						}

					// Render after wrapper markup
					$html .= $this->render_wrapper_after( $wrapper );

				}

			$html .= '</div>';
		}

		if ( $render ) {

			echo wp_kses_post( $html ); // phpcs:ignore

		} else {

			return apply_filters(
				'forminator_render_fields_markup',
				$html,
				$wrappers,
				$this
			);
		}

	}

	/**
	 * Return field markup of Radio for poll
	 *
	 * @since 1.0
	 *
	 * @param $field
	 * @param $uniq_id
	 *
	 * @return mixed
	 */
	public function render_field_radio($field, $uniq_id) {

		$label = Forminator_Field::get_property( 'title', $field, $this->model->id );

		// Get field object
		$element_id = Forminator_Field::get_property( 'element_id', $field );
		$name       = $this->model->id;

		if ( ! isset( $field['value'] ) ) {
			$field['value'] = sanitize_title( $label );
		}

		// form_id - render_id - element_id
		$input_id = $name . '-' . self::$render_ids[ $this->model->id ] . '-' . $element_id;

		// Print field markup
		$html = sprintf( '<label for="%s" class="forminator-radio">', $input_id );

			$html .= $this->radio_field_markup( $field, $input_id, $name );

			$html .= '<span class="forminator-radio--design" aria-hidden="true"></span>';

			$html .= sprintf( '<span class="forminator-radio--label">%s</span>', $label );

		$html .= '</label>';

		return apply_filters( 'forminator_field_markup', $html, $field, $this );
	}

	/**
	 * Radio field markup
	 *
	 * @since 1.0
	 *
	 * @param $field
	 * @param $id
	 * @param $name
	 *
	 * @return mixed
	 */
	public function radio_field_markup( $field, $id, $name ) {

		$required = Forminator_Field::get_property( 'required', $field, false );
		$value    = Forminator_Field::get_property( 'element_id', $field );
		$disabled = '';

		if ( ! $this->is_preview && ! $this->model->current_user_can_vote() ) {
			$disabled = 'disabled="disabled"';
		}

		$html = sprintf(
			'<input id="%s" type="radio" data-required="%s" name="%s" value="%s" %s />',
			$id,
			$required,
			$name,
			$value,
			$disabled
		);

		return apply_filters( 'forminator_field_radio_markup', $html, $id, $name, $required, $value );

	}

	/**
	 * Render extra field
	 *
	 * @since 1.0
	 *
	 * @param $field
	 * @param $uniq_id
	 *
	 * @return mixed
	 */
	public function render_extra_field( $field, $uniq_id, $form_settings = array() ) {

		$label = Forminator_Field::get_property( 'title', $field, $this->model->id );
		$extra  = Forminator_Field::get_property( 'extra', $field );
		$design = $this->get_form_design();

		// Get field object
		$element_id = Forminator_Field::get_property( 'element_id', $field );
		$name       = $this->model->id;

		// form_id - render_id - element_id
		$input_id = $name . '-' . self::$render_ids[ $this->model->id ] . '-' . $element_id;

		$html = '';

		if ( ! isset( $field['value'] ) ) {
			$field['value'] = sanitize_title( $label );
		}

		if ( '' !== $label || '' !== $extra ) {

			$html = sprintf(
				'<label for="%s" class="forminator-screen-reader-only">%s</label>',
				$input_id . '-extra',
				( '' !== $label ) ? $label : $extra
			);
		}

		if ( 'material' === $design ) {
			$html .= '<div class="forminator-input--wrap">';
		}

			$html .= sprintf(
				'<input
					type="text"
					name="%s"
					placeholder="%s"
					id="%s"
					class="forminator-input"
				/>',
				$name . '-extra',
				$extra,
				$input_id . '-extra'
			);

		if ( 'material' === $design ) {
			$html .= '</div>';
		}

		return apply_filters( 'forminator_field_textfield_extra_markup', $html, $name );
	}

	/**
	 * Return field columns
	 *
	 * @since 1.0
	 *
	 * @param $field
	 *
	 * @return string
	 */
	public function get_cols( $field ) {

		if ( ! isset( $field['cols'] ) ) {
			return '12';
		}

		return $field['cols'];
	}

	/**
	 * Return form type
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_form_type() {
		return 'poll';
	}

	/**
	 * Return form settings
	 *
	 * @since 1.0
	 * @return mixed
	 */
	public function get_form_settings() {
		return $this->model->settings;
	}

	/**
	 * Return form design
	 *
	 * @since 1.0
	 * @return mixed
	 */
	public function get_form_design() {

		$form_settings = $this->get_form_settings();

		if ( ! isset( $form_settings['forminator-poll-design'] ) ) {
			return 'default';
		}

		return $form_settings['forminator-poll-design'];
	}

	/**
	 * Results chart design
	 *
	 * @since 1.0
	 * @return string
	 */
	private function get_chart_design() {

		$form_settings = $this->get_form_settings();

		if ( ! isset( $form_settings['results-style'] ) ) {
			return 'bar';
		}

		return $form_settings['results-style'];
	}

	/**
	 * Results chart design
	 *
	 * @since 1.0
	 * @return string
	 */
	private function get_show_results() {

		$form_settings = $this->get_form_settings();

		if ( ! isset( $form_settings['results-behav'] ) || empty( $form_settings['results-behav'] ) ) {
			return 'not_show';
		}

		return $form_settings['results-behav'];
	}

	/**
	 * Ajax submit
	 * Check if the form is ajax submit
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function is_ajax_submit() {

		$form_settings = $this->get_form_settings();

		if ( ! isset( $form_settings['enable-ajax'] ) || empty( $form_settings['enable-ajax'] ) ) {
			return false;
		}

		return filter_var( $form_settings['enable-ajax'], FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Show results after poll submit
	 *
	 * @since 1.0
	 * @return bool
	 */
	private function show_results() {

		$show_results = $this->get_show_results();

		if ( 'show_after' === $show_results ) {
			return true;
		}

		return false;
	}

	/**
	 * Show link after submit
	 *
	 * @since 1.0
	 * @return bool
	 */
	private function show_link() {

		$show_results = $this->get_show_results();

		if ( 'link_on' === $show_results ) {
			return true;
		}

		return false;
	}

	/**
	 * Render success
	 *
	 * @since 1.0
	 * @return string
	 */
	public function render_success( $render = true, $form_settings = array() ) {

		$design   = $this->get_form_design();
		$settings = $this->get_form_settings();

		if ( is_object( $this->model ) ) {

			$post_id         = $this->get_post_id();
			$return_url      = get_permalink( $post_id );
			$chart_container = 'forminator_chart_poll_' . uniqid() . '_' . $this->model->id;

			ob_start(); ?>

			<form
				class="forminator-ui forminator-poll forminator-poll-<?php echo esc_attr( $this->model->id ); ?> <?php echo $this->get_form_design_class(); // WPCS: XSS ok. ?> <?php echo $this->get_fields_type_class(); // WPCS: XSS ok. ?> <?php echo $this->form_extra_classes(); // WPCS: XSS ok. ?>"
				method="GET"
				action="<?php echo esc_url( $return_url ); ?>"
				data-forminator-render="<?php echo esc_attr( self::$render_ids[ $this->model->id ] ); ?>"
			>

				<?php echo $this->render_form_header(); // WPCS: XSS ok. ?>

				<div class="forminator-poll-body">

					<canvas id="<?php echo esc_attr( $chart_container ); ?>" class="forminator-chart forminator-show" role="img" aria-hidden="true"></canvas>

				</div>

				<?php if ( isset( $settings['enable-votes-limit'] ) && 'true' === $settings['enable-votes-limit'] ) { ?>

					<div class="forminator-poll-footer forminator-poll--actions">

						<?php if ( 'material' === $design ) : ?>

							<button class="forminator-button forminator-button-back-poll">
								<span class="forminator-button--mask" aria-label="hidden"></span>
								<span class="forminator-button--text"><?php esc_attr_e( 'Back to poll', Forminator::DOMAIN ); ?></span>
							</button>

						<?php else : ?>

							<button class="forminator-button forminator-button-back"><?php esc_attr_e( 'Back to poll', Forminator::DOMAIN ); ?></button>

						<?php endif; ?>

					</div>

				<?php } ?>

			</form>

			<?php
			self::$graph_result_scripts[] = array(
				'model'     => $this->model,
				'container' => $chart_container,
			);

			$html = ob_get_clean();

			if ( $render ) {
				echo apply_filters( 'forminator_render_form_success_markup', $html, $this->model ); // WPCS: XSS ok.
			} else {
				return apply_filters( 'forminator_render_form_success_markup', $html, $this->model );
			}
		}
	}

	public function graph_scripts() {

		foreach ( self::$graph_result_scripts as $graph_script ) {
			$this->success_footer_script( $graph_script['model'], $graph_script['container'] );
		}
	}

	/**
	 * Return chart type
	 *
	 * @return string
	 */
	public function get_chart_type() {
		$type = 'none';
		$form_settings = $this->get_form_settings();

		if ( isset( $form_settings['results-behav'] ) && ( "link_on" === $form_settings['results-behav'] || "show_after" === $form_settings['results-behav'] ) ) {
			$type = $form_settings['results-style'];
		}

		return $type;
	}

	/**
	 * Get Options for google chart
	 *
	 * @param $model
	 *
	 * @return array
	 */
	public static function get_default_chart_options( $model ) {
		$chart_colors     = forminator_get_poll_chart_colors( $model->id );
		$chart_design     = 'bar';
		$pie_tooltip_text = 'percentage';
		$form_settings    = $model->settings;
		if ( isset( $form_settings['results-style'] ) ) {
			$chart_design = $form_settings['results-style'];
		}

		if ( isset( $form_settings['show-votes-count'] ) && $form_settings['show-votes-count'] ) {
			if ( 'pie' === $chart_design ) {
				$pie_tooltip_text = 'both';
			}
		}

		if ( 'pie' !== $chart_design ) {
			$chart_options = array(
				'annotations'     => array(
					'textStyle' => array(
						'fontSize' => 13,
						'bold'     => false,
						'color'    => '#333',
					),
				),
				'backgroundColor' => 'transparent',
				'fontSize'        => 13,
				'fontName'        => 'Roboto',
				'hAxis'           => array(
					'format'        => 'decimal',
					'baselineColor' => '#4D4D4D',
					'gridlines'     => array(
						'color' => '#E9E9E9',
					),
					'textStyle'     => array(
						'color'    => '#4D4D4D',
						'fontSize' => 13,
						'bold'     => false,
						'italic'   => false,
					),
					'minValue'      => 0,
				),
				'vAxis'           => array(
					'baselineColor' => '#4D4D4D',
					'gridlines'     => array(
						'color' => '#E9E9E9',
					),
					'textStyle'     => array(
						'color'    => '#4D4D4D',
						'fontSize' => 13,
						'bold'     => false,
						'italic'   => false,
					),
					'minValue'      => 0,
				),
				'tooltip'         => array(
					'isHtml'  => true,
				),
				'legend'          => array(
					'position' => 'none',
				),
			);
		} else {
			$chart_options = array(
				'colors'          => $chart_colors,
				'backgroundColor' => 'transparent',
				'fontSize'        => 13,
				'fontName'        => 'Roboto',
				'tooltip'         => array(
					'isHtml'  => true,
					'trigger' => 'focus',
					'text'    => $pie_tooltip_text,
				),
				'chartArea'       => array(
					'width' => '90%',
				),
			);
		}

		return apply_filters( 'forminator_poll_chart_options', $chart_options, $model );
	}

	/**
	 * Success footer scripts
	 *
	 * @since 1.0
	 */
	public function success_footer_script( $model, $container_id ) {

		if ( ! is_object( $model ) ) {
			return '';
		}

		$form_settings = $model->settings;

		$chart_design = 'bar';

		if ( isset( $form_settings['results-style'] ) ) {
			$chart_design = $form_settings['results-style'];
		}

		$number_votes_enabled = (bool) false;

		if ( isset( $form_settings['show-votes-count'] ) && $form_settings['show-votes-count'] ) {
			$number_votes_enabled = (bool) true;
		}

		$chart_colors         = forminator_get_poll_chart_colors( $model->id );

		$default_chart_colors = $chart_colors;
		$votes_count          = 'false';

		// Votes count
		if ( isset( $form_settings['show-votes-count'] ) ) {
			$votes_count = $form_settings['show-votes-count'];
		}

		$chart_data = forminator_get_chart_data( $model );

		// Chart basic colors
		$grids_color = ( isset( $form_settings['grid_lines'] ) && ! empty( $form_settings['grid_lines'] ) ) ? $form_settings['grid_lines'] : '#E5E5E5';
		$labels_color = ( isset( $form_settings['grid_labels'] ) && ! empty( $form_settings['grid_labels'] ) ) ? $form_settings['grid_labels'] : '#777771';
		$onchart_label = ( isset( $form_settings['onbar_votes'] ) && ! empty( $form_settings['onbar_votes'] ) ) ? $form_settings['onbar_votes'] : '#333333';

		// Tooltips
		$tooltips_bg = ( isset( $form_settings['tooltips_background'] ) && ! empty( $form_settings['tooltips_background'] ) ) ? $form_settings['tooltips_background'] : '#333333';
		$tooltips_color = ( isset( $form_settings['tooltips_text'] ) && ! empty( $form_settings['tooltips_text'] ) ) ? $form_settings['tooltips_text'] : '#FFFFFF';
		?>

		<script type="text/javascript">

			( function ( $, doc ) {

				'use strict';

				$( 'document' ).ready( function() {

					var chartExtras = [
						'<?php echo esc_html_e( 'vote(s)' ); ?>',
						<?php echo esc_html( $votes_count ); ?>,
						[
							'<?php echo esc_html( $grids_color ); ?>',
							'<?php echo esc_html( $labels_color ); ?>',
							'<?php echo esc_html( $onchart_label ); ?>'
						],
						[
							'<?php echo esc_html( $tooltips_bg ); ?>',
							'<?php echo esc_html( $tooltips_color ); ?>'
						]
					];

					FUI.pollChart(
						'#<?php echo esc_attr( $container_id ); ?>',
						<?php echo wp_json_encode( $chart_data ); ?>,
						'<?php echo esc_html( $chart_design ); ?>',
						chartExtras
					);

					var chartCanvas  = $( '#<?php echo esc_attr( $container_id ); ?>' ),
						chartBody    = chartCanvas.closest( '.forminator-poll-body' ),
						chartWrapper = chartBody.find( '.forminator-chart-wrapper' )
						;

					if ( chartWrapper.length ) {

						chartCanvas.addClass( 'forminator-show' );

						chartWrapper.addClass( 'forminator-show' );
						chartWrapper.removeAttr( 'aria-hidden' );
						chartWrapper.attr( 'tabindex', '-1' );

						chartWrapper.focus();

					}

				});

			}( jQuery, document ) );

		</script>

		<?php
	}

	/**
	 * Return styles template path
	 *
	 * @since 1.0
	 * @return bool|string
	 */
	public function styles_template_path() {

		$theme = $this->get_form_design();

		if ( 'bold' === $theme ) {
			return realpath( forminator_plugin_dir() . '/assets/js/front/templates/poll/bold.html' );
		}

		if ( 'flat' === $theme ) {
			return realpath( forminator_plugin_dir() . '/assets/js/front/templates/poll/flat.html' );
		}

		if ( 'default' === $theme ) {
			return realpath( forminator_plugin_dir() . '/assets/js/front/templates/poll/default.html' );
		}

		if ( 'material' === $theme ) {
			return realpath( forminator_plugin_dir() . '/assets/js/front/templates/poll/material.html' );
		}

		if ( 'empty' !== $theme && ( empty( $theme ) || '' !== $theme ) ) {
			return realpath( forminator_plugin_dir() . '/assets/js/front/templates/poll/default.html' );
		}
	}

	/**
	 * Return if view votes setting is enabled
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function has_votes_enabled() {
		$settings = $this->get_form_settings();
		if ( isset( $settings['show-votes-count'] ) && $settings['show-votes-count'] ) {
			return true;
		}

		return false;
	}

	/**
	 * Get Properties styles of each rendered forms
	 *
	 * @return array
	 */
	public function get_styles_properties() {
		$properties = array();
		if ( ! empty( $this->forms_properties ) ) {
			// avoid same custom style printed
			$style_rendered = array();
			foreach ( $this->forms_properties as $form_properties ) {
				if ( ! in_array( $form_properties['id'], $style_rendered, true ) ) {
					$properties[] = $form_properties;
				}
			}
		}

		return $properties;
	}

	/**
	 * Print poll styles
	 *
	 * @since 1.0
	 */
	public function print_styles() {

		$style_properties = $this->get_styles_properties();

		if ( ! empty( $style_properties ) ) {

			foreach ( $style_properties as $style_property ) {

				if ( ! isset( $style_property['settings'] ) || empty( $style_property['settings'] ) ) {
					continue;
				}

				$properties = $style_property['settings'];

				$properties['fonts_settings'] = array();

				/**
				 * Font Settings.
				 *
				 * Use this to properly check if font settings is enabled.
				 */
				if ( isset( $style_property['fonts_settings'] ) ) {
					$properties['fonts_settings'] = $style_property['fonts_settings'];
				}

				/**
				 * Form ID.
				 * If we don't have form_id, use $model->id.
				 *
				 * @var array $properties
				 */
				if ( ! isset( $properties['form_id'] ) ) {

					if ( ! isset( $style_property ['id'] ) ) {
						continue;
					}

					$properties['form_id'] = $style_property['id'];
				}

				ob_start();

				if ( isset( $properties['custom_css'] ) && isset( $properties['form_id'] ) ) {
					if ( isset( $properties['forminator-poll-design'] ) && 'clean' === $properties['forminator-poll-design'] ) {
						$properties['custom_css'] = forminator_prepare_css(
							$properties['custom_css'],
							'.forminator-ui.forminator-poll-' . $properties['form_id'],
							false,
							true,
							'forminator-poll'
						);
					} else {
						$properties['custom_css'] = forminator_prepare_css(
							$properties['custom_css'],
							'.forminator-ui.forminator-poll-' . $properties['form_id'] . ' ',
							false,
							true,
							'forminator-poll'
						);
					}
				}

				/** @noinspection PhpIncludeInspection */
				include $this->styles_template_path();

				$styles         = ob_get_clean();
				$trimmed_styles = trim( $styles );

				if ( isset( $properties['form_id'] ) && strlen( trim( $trimmed_styles ) ) > 0 ) {
					echo '<style type="text/css" id="forminator-quiz-styles-' . esc_attr( $properties['form_id'] ) . '">' . esc_html( $trimmed_styles ) . '</style>';
				}
			}
		}
	}


	/**
	 * Initiate `forminatorFront` front javascript for rendered form(s)
	 *
	 * @since 1.0
	 */
	public function forminator_render_front_scripts() {
		?>
		<script type="text/javascript">
			jQuery(document).ready(function () {
				<?php
				if ( ! empty( $this->forms_properties ) ) {
					foreach ( $this->forms_properties as $form_properties ) {
						if( isset( $form_properties['rendered'] ) && $form_properties['rendered'] ) {
						$options = $this->get_front_init_options( $form_properties );
							?>
							jQuery('#forminator-module-<?php echo esc_attr( $form_properties['id'] ); ?>[data-forminator-render="<?php echo esc_attr( $form_properties['render_id'] ); ?>"]')
								.forminatorFront(<?php echo wp_json_encode( $options ); ?>);
							<?php
						}
					}
				}
				?>
			});
		</script>
		<?php

	}

	/**
	 * Html markup of form
	 *
	 * @since 1.6.1
	 *
	 * @param bool $hide
	 * @param bool $is_preview
	 *
	 * @return false|string
	 */
	public function get_html( $hide = true, $is_preview = false ) {
		ob_start();

		$is_same_form   = false;
		$is_same_render = false;
		$rendered       = false;
		if ( isset( $_REQUEST['form_id'] ) && (int) $_REQUEST['form_id'] === (int) $this->model->id ) { // WPCS: CSRF OK
			$is_same_form = true;
		}

		if ( isset( $_REQUEST['render_id'] ) && (int) $_REQUEST['render_id'] === (int) self::$render_ids[ $this->model->id ] ) { // WPCS: CSRF OK
			$is_same_render = true;
		}

		if ( isset( $_REQUEST['saved'] ) && $is_same_form && $is_same_render && $this->show_results() ) { // WPCS: CSRF OK
			$this->track_views = false;
			$this->render_success();
		} elseif ( isset( $_REQUEST['results'] ) && $is_same_form && $is_same_render && $this->show_link() ) { // WPCS: CSRF OK
			$this->track_views = false;
			$this->render_success();
		} elseif ( ( ! $this->is_admin || $is_preview ) && ( ! $this->model->current_user_can_vote() && ( $this->show_results() || $this->show_link() ) ) ) { // WPCS: CSRF OK
			$this->track_views = false;
			$this->render_success();
		} else {
			$this->render( $this->model->id, $hide, $is_preview );

			$rendered = true;
		}

		$this->forms_properties[] = array(
			'id'            => $this->model->id,
			'render_id'     => self::$render_ids[ $this->model->id ],
			'settings'      => $this->get_form_settings(),
			'chart_design'  => $this->get_chart_design(),
			'chart_options' => self::get_default_chart_options( $this->model ),
			'rendered'      => $rendered,
		);

		$html = ob_get_clean();

		return $html;
	}

	/**
	 * Check if form should be displayed
	 *
	 * @since 1.6.1
	 *
	 * @param $is_preview
	 *
	 * @return bool
	 */
	public function is_displayable( $is_preview ) {

		if ( $this->model instanceof Forminator_Poll_Form_Model && ( $is_preview || Forminator_Poll_Form_Model::STATUS_PUBLISH === $this->model->status ) ) {
			$this->generate_render_id( $this->model->id );

			return true;
		} else {
			return false;
		}
	}

	/**
	 * Ajax response for displaying form
	 *
	 * @since 1.6.1
	 * @since 1.6.2 add $extra arg
	 *
	 * @param       $id
	 * @param bool  $is_preview
	 * @param bool  $data
	 * @param bool  $hide
	 * @param array $last_submit_data
	 * @param array $extra
	 *
	 * @return array
	 */
	public function ajax_display( $id, $is_preview = false, $data = false, $hide = true, $last_submit_data = array(), $extra = array() ) {

		if ( $data && ! empty( $data ) ) {
			$this->model = Forminator_Poll_Form_Model::model()->load_preview( $id, $data );

			// its preview!
			if( is_object( $this->model ) ) {
				$this->model->id = $id;
			}
		} else {
			$this->model = Forminator_Poll_Form_Model::model()->load( $id );
		}

		$response = array(
			'html'         => '',
			'style'        => '',
			'styles'       => array(),
			'scripts'      => array(),
			'script'       => '',
			'callback'     => '',
			'is_ajax_load' => false,
		);

		if ( ! $this->is_displayable( $is_preview ) ) {
			return $response;
		}

		if ( ! $this->model->is_ajax_load( $is_preview ) ) {
			return $response; // return nothing
		}

		if ( ! empty( $last_submit_data ) && is_array( $last_submit_data ) ) {
			$_POST = $last_submit_data;
		}

		// Setup extra param
		if ( isset( $extra ) && is_array( $extra ) ) {

			if ( isset( $extra['_wp_http_referer'] ) ) {
				$this->_wp_http_referer = $extra['_wp_http_referer'];
			}
			if ( isset( $extra['page_id'] ) ) {
				$this->_page_id = $extra['page_id'];
			}
		}

		$response['is_ajax_load'] = true;
		$response['html']         = $this->get_html( $hide, $is_preview );

		$properties = isset( $this->forms_properties[0] ) ? $this->forms_properties[0] : array();

		$response['options'] = $this->get_front_init_options( $properties );

		ob_start();
		$this->print_styles();
		$styles = ob_get_clean();

		ob_start();
		$this->graph_scripts();
		$script = ob_get_clean();

		$response['style']  = $styles;
		$response['script'] = $script;

		if ( $this->can_track_views() ) {

			$form_view = Forminator_Form_Views_Model::get_instance();
			$post_id   = $this->get_post_id();

			if ( ! $this->is_admin ) {
				$form_view->save_view( $id, $post_id, '' );
			}
		}

		return $response;

	}

	/**
	 * Get forminatorFront js init options to be passed
	 *
	 * @since 1.6.1
	 *
	 * @param $form_properties
	 *
	 * @return array
	 */
	public function get_front_init_options( $form_properties ) {

		if ( empty( $form_properties ) ) {
			return array();
		}

		$options = array(
			'form_type'     => $this->get_form_type(),
			'chart_design'  => $form_properties['chart_design'],
			'chart_options' => $form_properties['chart_options'],
		);

		return $options;
	}
}
