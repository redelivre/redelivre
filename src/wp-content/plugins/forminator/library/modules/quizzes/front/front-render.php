<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Front render class for custom forms
 */
class Forminator_QForm_Front extends Forminator_Render_Form {

	/**
	 * Model data
	 *
	 * Dev Autocomplete purpose
	 *
	 * @var Forminator_Quiz_Form_Model
	 */
	public $model = null;

	/**
	 * Class instance
	 *
	 * @var Forminator_Render_Form|null
	 */
	private static $instance = null;

	protected $ajax_load_action = 'forminator_load_quiz';

	/**
	 * @var array
	 */
	private $forms_properties = array();

	/**
	 * Return class instance
	 *
	 * @since 1.0
	 * @return Forminator_QForm_Front
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
		add_shortcode( 'forminator_quiz', array( $this, 'render_shortcode' ) );
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

		$version       = FORMINATOR_VERSION;
		$module_type   = 'quiz';
		$module_design = $this->get_quiz_theme();

		if ( $data && ! empty( $data ) ) {
			// New form, we have to update the form id
			$has_id = filter_var( $id, FILTER_VALIDATE_BOOLEAN );

			if ( ! $has_id && isset( $data['settings']['form_id'] ) ) {
				$id = $data['settings']['form_id'];
			}

			$this->model = Forminator_Quiz_Form_Model::model()->load_preview( $id, $data );
			// its preview!
			$this->model->id = $id;

			// If this module haven't been saved, the preview will be of the wrong module
			// if ( ! isset( $data['settings']['quiz_title'] ) || $data['settings']['quiz_title'] !== $this->model->settings['quiz_title'] ) {
			// 	echo $this->message_save_to_preview(); // WPCS: XSS ok.

			// 	return;
			// }
		} else {
			$this->model = Forminator_Quiz_Form_Model::model()->load( $id );

			if ( ! $this->model instanceof Forminator_Quiz_Form_Model ) {
				return;
			}
		}

		$this->maybe_define_cache_constants();

		// TODO: make preview and ajax load working similar
		$is_ajax_load = $this->is_ajax_load( $is_preview );

		// Load assets conditionally
		$assets = new Forminator_Assets_Enqueue_Quiz( $this->model, $is_ajax_load );
		$assets->load_assets();

		if ( $is_ajax_load ) {
			$this->generate_render_id( $id );
			$this->get_form_placeholder( $id, true );

			return;
		}

		if ( $this->is_displayable( $is_preview ) ) {

			echo $this->get_html( $hide, $is_preview );// wpcs xss ok.

			if ( $is_preview ) {
				$this->print_styles();
			}

			if ( is_admin() || $is_preview ) {
				$this->print_styles();
			} else {
				add_action( 'wp_footer', array( $this, 'print_styles' ), 9999 );
			}

			$google_fonts = $this->get_google_fonts();

			foreach ( $google_fonts as $font_name ) {
				if ( ! empty( $font_name ) ) {
					wp_enqueue_style( 'forminator-font-' . sanitize_title( $font_name ), 'https://fonts.googleapis.com/css?family=' . $font_name, array(), '1.0' );
				}
			}

			add_action( 'wp_footer', array( $this, 'forminator_render_front_scripts' ), 9999 );
		}

	}

	/**
	 * Return fields
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_fields() {
		return $this->model->questions;
	}

	/**
	 * Return form fields markup
	 *
	 * @since 1.0
	 *
	 * @param bool $render
	 *
	 * @return mixed
	 */
	public function render_fields( $render = true ) {

		$form_settings = $this->get_form_settings();

		$html = '';

		$fields = $this->get_fields();
		$num_fields = count( $fields );

		$i = 0;

		foreach ( $fields as $key => $field ) {

			$last_field = false;

			if ( ++$i === $num_fields ) {
				$last_field = true;
			}

			do_action( 'forminator_before_field_render', $field );

				// Render field
				$html .= $this->render_field( $field, $last_field );

			do_action( 'forminator_after_field_render', $field );
		}

		if ( $render ) {
			echo wp_kses_post( $html ); // WPCS: XSS ok.
		} else {
			return apply_filters( 'forminator_render_fields_markup', $html, $fields );
		}

	}

	/**
	 * Render field
	 *
	 * @since 1.0
	 *
	 * @param $field
	 *
	 * @return mixed
	 */
	public function render_field( $field, $last_field = false ) {

		if ( isset( $field['type'] ) && 'knowledge' === $field['type'] ) {
			$html = $this->_render_knowledge( $field, $last_field );
		} else {
			$html = $this->_render_nowrong( $field, $last_field );
		}

		return apply_filters( 'forminator_field_markup', $html, $field, $this );

	}

	/**
	 * Render No wrong quiz
	 *
	 * @since 1.0
	 *
	 * @param $field
	 *
	 * @return string
	 */
	private function _render_nowrong( $field, $last_field ) {

		ob_start();

		$class         = '';
		$uniq_id       = '-' . uniqid();
		$field_slug    = uniqid();
		$form_settings = $this->get_form_settings();
		$form_design   = $this->get_quiz_theme();

		// Make sure slug key exist
		if ( isset( $field['slug'] ) ) {
			$field_slug = $field['slug'];
		}

		$question      = isset( $field['title'] ) ? $field['title'] : '';
		$image         = isset( $field['image'] ) ? $field['image'] : '';
		$image_alt     = '';
		$answers       = isset( $field['answers'] ) ? $field['answers'] : '';
		$has_question  = ( isset( $question ) && ! empty( $question ) );
		$has_image     = ( isset( $image ) && ! empty( $image ) );
		$has_image_alt = ( isset( $image_alt ) && ! empty( $image_alt ) );
		$has_answers   = ( isset( $answers ) && ! empty( $answers ) );
		?>

		<div
			tabindex="0"
			role="radiogroup"
			id="<?php echo esc_html( $field_slug ); ?>"
			class="forminator-question<?php echo ( true === $last_field ) ? ' forminator-last' : ''; ?>"
			data-question-type="<?php echo ( isset( $field['type'] ) && 'knowledge' === $field['type'] ) ? 'knowledge' : 'personality'; ?>"
			aria-labelledby="<?php echo esc_html( $field_slug ) . '-label'; ?>"
			aria-describedby="<?php echo esc_html( $field_slug ) . '-description'; ?>"
			aria-required="true"
		>

			<span id="<?php echo esc_html( $field_slug ) . '-label'; ?>" class="forminator-legend"><?php echo esc_html( $question ); ?></span>

			<?php if ( $has_image ) { ?>
				<div class="forminator-image"<?php echo ( $has_image_alt ) ? '' : ' aria-hidden="true"'; ?>>
					<img
						src="<?php echo esc_attr( $field['image'] ); ?>"
						<?php echo ( $has_image_alt ) ? 'alt="' . esc_html( $image_alt ) . '"' : ''; ?>
					/>
				</div>
			<?php } ?>


			<?php
			if ( $has_answers ) {

				foreach ( $answers as $k => $answer ) {

					$answer_id     = $field_slug . '-' . $k . $uniq_id;
					$label         = isset( $answer['title'] ) ? $answer['title'] : '';
					$image         = isset( $answer['image'] ) ? $answer['image'] : '';
					$image_alt     = '';
					$has_label     = ( isset( $label ) && ! empty( $label ) );
					$has_image     = ( isset( $image ) && ! empty( $image ) );
					$has_image_alt = ( isset( $image_alt ) && ! empty( $image_alt ) );

					if ( $has_label && $has_image ) {
						$empty_class = '';
					} else {
						if ( $has_image ) {
							$empty_class = ' forminator-only--image';
						} else if ( $has_label ) {
							$empty_class = ' forminator-only--text';
						} else {
							$empty_class = ' forminator-empty';
						}
					}
					?>

					<label for="<?php echo esc_attr( $answer_id ); ?>" class="forminator-answer<?php echo $empty_class; // WPCS: XSS ok. ?>">

						<input
							type="radio"
							name="answers[<?php echo esc_attr( $field_slug ); ?>]"
							value="<?php echo esc_attr( $k ); ?>"
							id="<?php echo esc_attr( $answer_id ); ?>"
							class="<?php echo esc_attr( $class ); ?>"
						/>

						<?php if ( 'clean' !== $form_design ) {
							echo '<span class="forminator-answer--design" for="' . esc_attr( $answer_id ) . '">';
						} ?>

						<?php if ( $has_image ) : ?>

							<?php if ( $has_image_alt ) { ?>
								<span
									class="forminator-answer--image"
									style="background-image: url('<?php echo esc_attr( $image ); ?>');"
								>
									<span><?php echo esc_html( $image_alt ); ?></span>
								</span>
							<?php } else { ?>
								<span
									class="forminator-answer--image"
									style="background-image: url('<?php echo esc_attr( $image ); ?>');"
									aria-hidden="true"
								></span>
							<?php } ?>

						<?php endif; ?>

						<span class="forminator-answer--status" aria-hidden="true">
							<i class="forminator-icon-check"></i>
						</span>

						<?php if ( $has_label ) : ?>
							<span class="forminator-answer--name"><?php echo esc_html( $label ); ?></span>
						<?php endif; ?>

						<?php if ( 'clean' !== $form_design ) {
							echo '</span>';
						} ?>

					</label>

				<?php
				}
			}
			?>

		</div>

		<?php
		return ob_get_clean();
	}

	/**
	 * Render knowledge quiz
	 *
	 * @since 1.0
	 *
	 * @param $field
	 *
	 * @return string
	 */
	private function _render_knowledge( $field, $last_field ) {

		ob_start();

		$class         = ( isset( $this->model->settings['results_behav'] ) && 'end' === $this->model->settings['results_behav'] ) ? '' : 'forminator-submit-rightaway';
		$uniq_id       = '-' . uniqid();
		$field_slug    = uniqid();
		$form_settings = $this->get_form_settings();
		$form_design   = $this->get_quiz_theme();

		// Make sure slug key exist
		if ( isset( $field['slug'] ) ) {
			$field_slug = $field['slug'];
		}

		$question      = $field['title'];
		$image         = isset( $field['image'] ) ? $field['image'] : '';
		$image_alt     = '';
		$answers       = $field['answers'];
		$has_question  = ( isset( $question ) && ! empty( $question ) );
		$has_image     = ( ! empty( $image ) );
		$has_image_alt = ( isset( $image_alt ) && ! empty( $image_alt ) );
		$has_answers   = ( isset( $answers ) && ! empty( $answers ) );
		?>

		<div
			tabindex="0"
			role="radiogroup"
			id="<?php echo esc_html( $field_slug ); ?>"
			class="forminator-question<?php echo ( true === $last_field ) ? ' forminator-last' : ''; ?>"
			aria-labelledby="<?php echo esc_html( $field_slug ) . '-label'; ?>"
			aria-describedby="<?php echo esc_html( $field_slug ) . '-description'; ?>"
			aria-required="true"
		>

			<span id="<?php echo esc_html( $field_slug ) . '-label'; ?>" class="forminator-legend"><?php echo esc_html( $question ); ?></span>

			<?php if ( $has_image ) { ?>
				<div class="forminator-image"<?php echo ( $has_image_alt ) ? '' : ' aria-hidden="true"'; ?>>
					<img
						src="<?php echo esc_attr( $field['image'] ); ?>"
						<?php echo ( $has_image_alt ) ? 'alt="' . esc_html( $image_alt ) . '"' : ''; ?>
					/>
				</div>
			<?php } ?>

			<?php
			if ( $has_answers ) {

				foreach ( $answers as $k => $answer ) {

					$answer_id     = $field_slug . '-' . $k . $uniq_id;
					$label         = $answer['title'];
					$image         = isset( $answer['image'] ) ? $answer['image'] : '';
					$image_alt     = '';
					$has_label     = ( isset( $label ) && ! empty( $label ) );
					$has_image     = ( ! empty( $image ) );
					$has_image_alt = ( isset( $image_alt ) && ! empty( $image_alt ) );

					if ( $has_label && $has_image ) {
						$empty_class = '';
					} else {
						if ( $has_image ) {
							$empty_class = ' forminator-only--image';
						} else if ( $has_label ) {
							$empty_class = ' forminator-only--text';
						} else {
							$empty_class = ' forminator-empty';
						}
					}
					?>

					<label for="<?php echo esc_attr( $answer_id ); ?>" class="forminator-answer<?php echo $empty_class; // WPCS: XSS ok. ?>">

						<input
							type="radio"
							name="answers[<?php echo esc_attr( $field_slug ); ?>]"
							value="<?php echo esc_attr( $k ); ?>"
							id="<?php echo esc_attr( $answer_id ); ?>"
							class="<?php echo esc_attr( $class ); ?>"
						/>

						<?php if ( 'clean' !== $form_design ) {
							echo '<span class="forminator-answer--design" for="' . esc_attr( $answer_id ) . '">';
						} ?>

						<?php if ( $has_image ) : ?>

							<?php if ( $has_image_alt ) { ?>
								<span
									class="forminator-answer--image"
									style="background-image: url('<?php echo esc_attr( $image ); ?>');"
								>
									<span><?php echo esc_html( $image_alt ); ?></span>
								</span>
							<?php } else { ?>
								<span
									class="forminator-answer--image"
									style="background-image: url('<?php echo esc_attr( $image ); ?>');"
									aria-hidden="true"
								></span>
							<?php } ?>

						<?php endif; ?>

						<span class="forminator-answer--status" aria-hidden="true"></span>

						<?php if ( $has_label ) : ?>
							<span class="forminator-answer--name"><?php echo esc_html( $label ); ?></span>
						<?php endif; ?>

						<?php if ( 'clean' !== $form_design ) {
							echo '</span>';
						} ?>

					</label>

				<?php
				}
			}
			?>

			<span id="<?php echo esc_html( $field_slug ) . '-description'; ?>" class="forminator-question--result"></span>

		</div><?php // END .forminator-question ?>

		<?php
		$html = ob_get_clean();

		return $html;
	}

	/**
	 * Return Form ID required message
	 *
	 * @since 1.0
	 * @return mixed
	 */
	public function message_required() {
		return esc_html__( "Form ID attribute is required!", Forminator::DOMAIN );
	}

	/**
	 * Return Save to preview message
	 *
	 * @since 1.0
	 * @return mixed
	 */
	public function message_save_to_preview() {
		return esc_html__( "Please, save the quiz in order to preview it.", Forminator::DOMAIN );
	}

	/**
	 * Return From ID not found message
	 *
	 * @since 1.0
	 * @return mixed
	 */
	public function message_not_found() {
		return esc_html__( "Form ID not found!", Forminator::DOMAIN );
	}

	/**
	 * Return form type
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_form_type() {
		return 'quiz';
	}

	/**
	 * Return form settings
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_form_settings() {
		if ( is_object( $this->model ) ) {
			return $this->model->settings;
		}

		return $this->model['settings'];
	}

	/**
	 * Return form design
	 *
	 * @since 1.0
	 * @since 1.2 Added Theme and Filter
	 * @return mixed|string
	 */
	public function get_form_design() {
		$form_settings = $this->get_form_settings();

		$form_design = '';

		$visual_style = 'list';
		if ( isset( $form_settings['visual_style'] ) ) {
			$visual_style = $form_settings['visual_style'];
		}

		$form_design .= $visual_style;

		$quiz_theme = $this->get_quiz_theme();
		if ( 'clean' !== $quiz_theme ) {
			$form_design .= ' forminator-design--' . $quiz_theme;
		}

		return $form_design;
	}

	/**
	 * Render quiz header
	 *
	 * @since 1.0
	 * @return string
	 */
	public function render_form_header() {
		ob_start();

		// TO-DO: Get featured image alt text.
		$feat_image_alt = '';
		?>

		<?php if ( isset( $this->model->settings['quiz_name'] ) && ! empty( $this->model->settings['quiz_name'] ) ): ?>
			<h3 class="forminator-quiz--title"><?php echo esc_html( $this->model->settings['quiz_name'] ); ?></h3>
		<?php endif; ?>

		<?php if ( isset( $this->model->settings['quiz_feat_image'] ) && ! empty( $this->model->settings['quiz_feat_image'] ) ): ?>
			<img
				src="<?php echo esc_html( $this->model->settings['quiz_feat_image'] ); ?>"
				class="forminator-quiz--image"
				<?php echo ( '' !== $feat_image_alt ) ? 'alt="' . esc_html( $feat_image_alt ) . '"' : 'aria-hidden="true"'; ?>
			/>
		<?php endif; ?>

		<?php if ( isset( $this->model->settings['quiz_description'] ) && ! empty( $this->model->settings['quiz_description'] ) ):

			$content = forminator_replace_variables( $this->model->settings['quiz_description'], $this->model->id );

			if ( stripos( $content, '{quiz_name}' ) !== false ) :
				$quiz_name = forminator_get_name_from_model( $this->model );
				$content   = str_ireplace( '{quiz_name}', $quiz_name, $content );
			endif; ?>
			<div class="forminator-quiz--description"><?php echo wp_kses_post( $content ); ?></div>
		<?php endif; ?>

		<?php
		return ob_get_clean();
	}

	public function get_submit_data() {
		$settings = $this->get_form_settings();

		$data = array(
			'class' => '',
			'label' => esc_html__( "Ready to send", Forminator::DOMAIN ),
			'loading' => esc_html__( "Calculating Result", Forminator::DOMAIN )
		);

		// Submit data is missing
		if ( ! isset( $settings['submitData'] ) ) {
			return $data;
		}

		if( isset( $settings['submitData']['button-text'] ) && ! empty( $settings['submitData']['button-text'] ) ) {
			$data['label'] = $settings['submitData']['button-text'];
		}

		if( isset( $settings['submitData']['button-processing-text'] ) && ! empty( $settings['submitData']['button-processing-text'] ) ) {
			$data['loading'] = $settings['submitData']['button-processing-text'];
		}

		if( isset( $settings['submitData']['custom-class'] ) ) {
			$data['class'] = $settings['submitData']['custom-class'];
		}

		return $data;
	}

	/**
	 * Return form submit button markup
	 *
	 * @since 1.0
	 *
	 * @param      $form_id
	 * @param bool $render
	 *
	 * @return mixed
	 */
	public function get_submit( $form_id, $render = true ) {

		// FIX:
		// https://app.asana.com/0/385581670491499/789649735369091/f
		$disabled = '';

		if ( $this->is_preview ) {
			$disabled = 'aria-disabled="true" disabled="disabled"';
		}

		$nonce   = $this->nonce_field( 'forminator_submit_form', 'forminator_nonce' );
		$post_id = $this->get_post_id();

		$submit_data = $this->get_submit_data();

		$html = '<div class="forminator-quiz--result">';

			if ( 'nowrong' === $this->model->quiz_type || ( isset( $this->model->settings['results_behav'] ) && 'end' === $this->model->settings['results_behav'] ) ) {

				if ( 'material' === $this->get_quiz_theme() ) {

					$html .= sprintf(
						'<button class="forminator-button forminator-button-submit %s" %s data-loading="%s" aria-live="polite"><span class="forminator-button--mask" aria-label="hidden"></span><span class="forminator-button--text">%s</span></button>',
						$submit_data['class'],
						$disabled,
						$submit_data['loading'],
						$submit_data['label']
					);
				} else {

					$html .= sprintf(
						'<button class="forminator-button forminator-button-submit %s" data-loading="%s" %s>%s</button>',
						$submit_data['class'],
						$submit_data['loading'],
						$disabled,
						$submit_data['label']
					);
				}
			}

		$html .= '</div>';

		$html .= $nonce;
		$html .= sprintf( '<input type="hidden" name="form_id" value="%s">', $form_id );
		$html .= sprintf( '<input type="hidden" name="page_id" value="%s">', $post_id );
		$html .= sprintf( '<input type="hidden" name="current_url" value="%s">', forminator_get_current_url() );

		if ( $this->is_preview ) {
			$html .= '<input type="hidden" name="action" value="forminator_submit_preview_form_quizzes" />';
		} else {
			$html .= '<input type="hidden" name="action" value="forminator_submit_form_quizzes" />';
		}

		if ( $render ) {
			echo apply_filters( 'forminator_render_form_submit_markup', $html, $form_id, $post_id, $nonce ); // WPCS: XSS ok.
		} else {
			return apply_filters( 'forminator_render_form_submit_markup', $html, $form_id, $post_id, $nonce );
		}
	}

	/**
	 * Return styles template path
	 *
	 * @since 1.0
	 * @return bool|string
	 */
	public function styles_template_path() {
		$theme = $this->get_quiz_theme();

		if ( isset( $this->model->quiz_type ) && 'knowledge' === $this->model->quiz_type ) {

			if ( 'none' !== $theme ) {
				return realpath( forminator_plugin_dir() . '/assets/js/front/templates/quiz/knowledge/global.html' );
			} else {
				return realpath( forminator_plugin_dir() . '/assets/js/front/templates/quiz/knowledge/grid.html' );
			}
		} else {

			if ( 'none' !== $theme ) {
				return realpath( forminator_plugin_dir() . '/assets/js/front/templates/quiz/nowrong/global.html' );
			} else {
				return realpath( forminator_plugin_dir() . '/assets/js/front/templates/quiz/nowrong/grid.html' );
			}
		}
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
					$properties[]     = $form_properties;
					$style_rendered[] = $form_properties['id'];
				}
			}
		}

		return $properties;
	}

	/**
	 * Return font specific front-end styles
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

				// use this to properly check font settings is enabled
				$properties['fonts_settings'] = array();
				if ( isset( $style_property['fonts_settings'] ) ) {
					$properties['fonts_settings'] = $style_property['fonts_settings'];
				}

				// If we don't have a form_id use $model->id
				/** @var array $properties */
				if ( ! isset( $properties['form_id'] ) ) {
					if ( ! isset( $style_property ['id'] ) ) {
						continue;
					}
					$properties['form_id'] = $style_property['id'];
				}

				ob_start();

				if ( isset( $properties['custom_css'] ) && isset( $properties['form_id'] ) ) {
					$properties['custom_css'] = forminator_prepare_css( $properties['custom_css'], '.forminator-quiz-' . $properties['form_id'] . ' ', false, true, 'forminator-quiz' );
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
	 *
	 */
	public function forminator_render_front_scripts() {
		?>
		<script type="text/javascript">
			jQuery(document).ready(function () {
				<?php
				if ( ! empty( $this->forms_properties ) ) {
				foreach ( $this->forms_properties as $form_properties ) {
				$options = $this->get_front_init_options( $form_properties );
				?>
				jQuery('#forminator-module-<?php echo esc_attr( $form_properties['id'] ); ?>[data-forminator-render="<?php echo esc_attr( $form_properties['render_id'] ); ?>"]')
					.forminatorFront(<?php echo wp_json_encode( $options ); ?>);
				<?php
				}
				}
				?>
			});
		</script>
		<?php

	}

	/**
	 * Get Quiz Theme
	 *
	 * @since 1.2
	 * @return string
	 */
	public function get_quiz_theme() {
		$quiz_theme = 'default';
		$settings   = $this->get_form_settings();
		if ( isset( $settings['forminator-quiz-theme'] ) && ! empty( $settings['forminator-quiz-theme'] ) ) {
			$quiz_theme = $settings['forminator-quiz-theme'];
		}

		$quiz_id = $this->get_module_id();

		/**
		 * Filter Quiz Theme to be used
		 *
		 * @since 1.2
		 *
		 * @param string $quiz_theme ,
		 * @param int    $quiz_id
		 * @param array  $settings   quiz settings
		 */
		$quiz_theme = apply_filters( 'forminator_quiz_theme', $quiz_theme, $quiz_id, $settings );

		return $quiz_theme;
	}

	/**
	 * Get module ID
	 *
	 * @since 1.11
	 *
	 * @return string
	 */
	public function get_module_id() {
		if ( is_object( $this->model ) ) {
			return $this->model->id;
		}

		return $this->model['id'];
	}

	/**
	 * Get Google Fonts setup on a quiz
	 *
	 * @since 1.2
	 * @return array
	 */
	public function get_google_fonts() {
		$fonts     = array();
		$settings  = $this->get_form_settings();
		$quiz_id   = $this->get_module_id();
		$quiz_type = $this->model->quiz_type;

		$custom_typography_enabled = false;
		// on clean design, disable google fonts
		if ( 'clean' !== $this->get_quiz_theme() ) {

			$configs = array();
			if ( 'nowrong' === $quiz_type ) {
				if ( isset( $settings['nowrong-toggle-typography'] ) ) {
					$custom_typography_enabled = filter_var( $settings['nowrong-toggle-typography'], FILTER_VALIDATE_BOOLEAN );
				}
				$configs = array(
					'nowrong-title-font-family',
					'nowrong-description-font-family',
					'nowrong-question-font-family',
					'nowrong-answer-font-family',
					'nowrong-submit-font-family',
					'nowrong-result-quiz-font-family',
					'nowrong-result-retake-font-family',
					'nowrong-result-title-font-family',
					'nowrong-result-description-font-family',
					'nowrong-sshare-font-family',
				);
			} elseif ( 'knowledge' === $quiz_type ) {
				if ( isset( $settings['knowledge-toggle-typography'] ) ) {
					$custom_typography_enabled = filter_var( $settings['knowledge-toggle-typography'], FILTER_VALIDATE_BOOLEAN );
				}
				$configs = array(
					'knowledge-title-font-family',
					'knowledge-description-font-family',
					'knowledge-question-font-family',
					'knowledge-answer-font-family',
					'knowledge-phrasing-font-family',
					'knowledge-submit-font-family',
					'knowledge-summary-font-family',
					'knowledge-sshare-font-family',
				);
			}

			foreach ( $configs as $config ) {
				if ( ! $custom_typography_enabled ) {
					$fonts[ $config ] = false;
					continue;
				}

				if ( isset( $settings[ $config ] ) ) {
					$font_family_name = $settings[ $config ];

					if ( empty( $font_family_name ) || 'custom' === $font_family_name ) {
						$fonts[ $config ] = false;
						continue;
					}

					$fonts[ $config ] = $font_family_name;
					continue;
				}
				$fonts[ $config ] = false;
			}

		}

		/**
		 * Filter google fonts to be loaded for a quiz
		 *
		 * @since 1.2
		 *
		 * @param array  $fonts
		 * @param int    $quiz_id
		 * @param string $quiz_type (nowrong|knowledge)
		 * @param array  $settings  quiz settings
		 */
		$fonts = apply_filters( 'forminator_quiz_google_fonts', $fonts, $quiz_id, $quiz_type, $settings );

		return $fonts;

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

		$id = $this->get_module_id();

		$this->render( $id, $hide, $is_preview );

		$this->forms_properties[] = array(
			'id'             => $id,
			'render_id'      => self::$render_ids[ $id ],
			'settings'       => $this->get_form_settings(),
			'fonts_settings' => $this->get_google_fonts(),
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
		$id = $this->get_module_id();

		if ( $this->model instanceof Forminator_Quiz_Form_Model && ( $is_preview || Forminator_Quiz_Form_Model::STATUS_PUBLISH === $this->model->status ) ) {
			$this->generate_render_id( $id );

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
			$this->model = Forminator_Quiz_Form_Model::model()->load_preview( $id, $data );
			$this->model->id = $id; // its preview!
		} else {
			$this->model = Forminator_Quiz_Form_Model::model()->load( $id );
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
			// return nothing
			return $response;
		}

		// setup extra param
		if ( isset( $extra ) && is_array( $extra ) ) {
			if ( isset( $extra['_wp_http_referer'] ) ) {
				$this->_wp_http_referer = $extra['_wp_http_referer'];
			}
			if ( isset( $extra['page_id'] ) ) {
				$this->_page_id = $extra['page_id'];
			}
		}

		if ( ! empty( $last_submit_data ) && is_array( $last_submit_data ) ) {
			$_POST = $last_submit_data;
		}

		$response['is_ajax_load'] = true;
		$response['html']         = $this->get_html( $hide, $is_preview );

		$properties = isset( $this->forms_properties[0] ) ? $this->forms_properties[0] : array();
		$response['options']      = $this->get_front_init_options( $properties );

		ob_start();
		$this->print_styles();
		$styles = ob_get_clean();

		$google_fonts = $this->get_google_fonts();

		foreach ( $google_fonts as $font_name ) {
			if ( ! empty( $font_name ) ) {
				$response['styles'][ 'forminator-font-' . sanitize_title( $font_name ) ] =
					array( 'src' => 'https://fonts.googleapis.com/css?family=' . $font_name );
			}
		}


		$response['style'] = $styles;

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
			'form_type' => $this->get_form_type(),
		);

		return $options;
	}

	/**
	 * Ajax handler to reload module
	 *
	 * @since 1.11
	 *
	 * @return void
	 */
	public function ajax_reload_module() {
		if ( isset( $_REQUEST['nonce'] ) && ! wp_verify_nonce( $_REQUEST['nonce'], 'forminator_submit_form' ) ) {
			wp_send_json_error( new WP_Error( 'invalid_code' ) );
		}

		$page_id = isset( $_POST['pageId'] ) ? sanitize_text_field( $_POST['pageId'] ) : false; // WPCS: CSRF OK

		if ( $page_id ) {
			$link = get_permalink( $page_id );

			if ( $link ) {
				$response = array( 'success' => true, 'html' => $link );
				wp_send_json( $response );
			} else {
				wp_send_json_error( new WP_Error( 'invalid_post' ) );
			}
		} else {
			wp_send_json_error( new WP_Error( 'invalid_id' ) );
		}
	}
}
