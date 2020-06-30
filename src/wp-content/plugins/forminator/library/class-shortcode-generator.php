<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Shortcode_Generator
 */
class Forminator_Shortcode_Generator {

	/**
	 * Forminator_Shortcode_Generator constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		add_filter( 'media_buttons_context', array( $this, 'attach_button' ) );
		add_action( 'admin_footer', array( $this, 'enqueue_js_scripts' ) );
		if ( function_exists( 'hustle_activated' ) ) {
			add_action( 'admin_footer', array( $this, 'enqueue_preview_scripts_for_hustle' ) );
		}
	}

	/**
	 * Check if current page is Hustle wizard page
	 *
	 * @since 1.0.5
	 *
	 * @return bool
	 */
	public function is_hustle_wizard() {
		$screen = get_current_screen();

		// If no screen id, abort
		if( !isset( $screen->id ) ) return false;

		// Hustle wizard pages
		$pages = array(
			'hustle_page_hustle_popup',
			'hustle_page_hustle_slidein',
			'hustle_page_hustle_embedded',
			'hustle_page_hustle_sshare'
		);

		// Check if current page is hustle wizard page
		if( in_array( $screen->id, $pages, true ) ) return true;

		return false;
	}

	/**
	 * Attach button
	 *
	 * @since 1.0
	 * @param $content
	 *
	 * @return string
	 */
	public function attach_button( $content ) {
		global $pagenow;
		$html = '';

		// If page different than Post or Page, abort
		if ( 'post.php' !== $pagenow && 'post-new.php' !== $pagenow && ! $this->is_hustle_wizard() ) {
			return $content;
		}

		// Button markup
		$html .= sprintf(
			'<button type="button" id="%s" class="button" data-editor="content" data-a11y-dialog-show="forminator-popup">%s<span>%s</span></button>',
			'forminator-generate-shortcode',
			'<i class="forminator-scgen-icon" aria-hidden="true"></i>',
			esc_html__( 'Add Form', Forminator::DOMAIN )
		);

		$content .= $html;
		return $content;
	}

	/**
	 * @since 1.0
	 * @param $content
	 *
	 * @return mixed
	 */
	public function enqueue_js_scripts( $content ) {

		global $pagenow;

		$sanitize_version = str_replace( '.', '-', FORMINATOR_SUI_VERSION );
		$sui_body_class   = "sui-$sanitize_version";

		// If page different than Post or Page, abort
		if ( 'post.php' !== $pagenow && 'post-new.php' !== $pagenow && ! $this->is_hustle_wizard() ) {
			return $content;
		}

		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-widget' );
		wp_enqueue_script( 'jquery-ui-mouse' );
		wp_enqueue_script( 'jquery-ui-tabs' );

		// Get shortcode generator styles
		wp_enqueue_style(
			'forminator-shortcode-generator-styles',
			forminator_plugin_url() . 'assets/css/forminator-scgen.min.css',
			array(),
			FORMINATOR_VERSION
		);

		// Get SUI JS
		wp_enqueue_script(
			'shared-ui',
			forminator_plugin_url() . 'assets/js/shared-ui.min.js',
			array( 'jquery' ),
			$sui_body_class,
			true
		);

		// Get shortcode generator scripts
		wp_enqueue_script(
			'forminator-shortcode-generator',
			forminator_plugin_url() . 'build/admin/scgen.min.js',
			array( 'jquery' ),
			FORMINATOR_VERSION,
			false
		);

		wp_localize_script( 'forminator-shortcode-generator', 'forminatorScgenData', array(
				'suiVersion' => $sui_body_class,
		) );

		$this->print_markup();
		?>
		<script type="text/javascript">
			jQuery(document).ready(function () {
				jQuery("#forminator-generate-shortcode").on( 'click', function(e) {
					e.preventDefault();
				});
			});
		</script>
		<?php
	}

	/**
	 * @since 1.0
	 * @param $content
	 *
	 * @return mixed
	 */
	public function enqueue_preview_scripts_for_hustle( $content ) {

		// If page is not Hustle module settings page, abort
		if ( ! $this->is_hustle_wizard() ) {
			return $content;
		}

		wp_enqueue_style( 'forminator-shortcode-generator-front-styles', forminator_plugin_url() . 'assets/css/front.min.css', array(), FORMINATOR_VERSION );

		/**
		 * Forminator UI
		 * These stylesheets currently works with "forms" only.
		 *
		 * @since 1.7.0
		 */
		wp_enqueue_style( 'forminator-scgen-global', forminator_plugin_url() . 'assets/forminator-ui/css/forminator-global.min.css', array(), FORMINATOR_VERSION );
		wp_enqueue_style( 'forminator-scgen-icons', forminator_plugin_url() . 'assets/forminator-ui/css/forminator-icons.min.css', array(), FORMINATOR_VERSION );
		wp_enqueue_style( 'forminator-scgen-forms', forminator_plugin_url() . 'assets/forminator-ui/css/forminator-forms.min.css', array(), FORMINATOR_VERSION );

	}

	/**
	 * Print modal markup
	 *
	 * @since 1.0
	 */
	public function print_markup() {
		?>
		<div id="forminator-scgen-modal" class="sui-wrap" style="display: none;">

			<div
				id="forminator-popup"
				class="sui-dialog sui-dialog-alt sui-dialog-reduced"
				tabindex="-1"
				aria-hidden="true"
			>

				<div class="sui-dialog-overlay"></div>

				<div
					class="sui-dialog-content"
					role="dialog"
					aria-labelledby="scgenDialogTitle"
					aria-describedby="scgenDialogDescription"
				>

					<div class="sui-box" role="document">

						<div class="sui-box-header sui-block-content-center">

							<h3 id="scgenDialogTitle" class="sui-box-title"><?php esc_html_e( 'Forminator Shortcodes', Forminator::DOMAIN ); ?></h3>

							<p id="scgenDialogDescription" class="sui-description"><?php esc_html_e( 'Select an option from the dropdown menu and generate a shortcode to insert in your post or page.', Forminator::DOMAIN ); ?></p>

							<div class="sui-actions-right">

								<button class="sui-dialog-close">
									<span class="sui-screen-reader-text"><?php esc_html_e( 'Close this dialog window.', Forminator::DOMAIN ); ?></span>
								</button>

							</div>

						</div>

						<div class="sui-box-body sui-box-body-slim">

							<div class="sui-tabs sui-tabs-flushed">

								<div data-tabs>

									<div id="forminator-shortcode-type--forms" class="active"><?php esc_html_e( 'Forms', Forminator::DOMAIN ); ?></div>
									<div id="forminator-shortcode-type--polls"><?php esc_html_e( 'Polls', Forminator::DOMAIN ); ?></div>
									<div id="forminator-shortcode-type--quizzes"><?php esc_html_e( 'Quizzes', Forminator::DOMAIN ); ?></div>

								</div>

								<div data-panes>

									<!-- Forms -->
									<div id="forminator-custom-forms" class="active">

										<div class="sui-form-field">

											<label for="forminator-select-forms" class="sui-label"><?php esc_html_e( 'Choose an option', Forminator::DOMAIN ); ?></label>

											<?php echo $this->get_forms(); // WPCS: XSS ok. ?>

											<span class="sui-error-message" style="display: none;"><?php esc_html_e( 'Please, select an option before you proceed.', Forminator::DOMAIN ); ?></span>

										</div>

										<div class="fui-simulate-footer">

											<button class="sui-button sui-button-blue wpmudev-insert-cform">
												<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
												<span class="sui-loading-text"><?php esc_html_e( 'Generate Shortcode', Forminator::DOMAIN ); ?></span>
											</button>

										</div>

									</div>

									<!-- Polls -->
									<div id="forminator-polls">

										<div class="sui-form-field">

											<label for="forminator-select-forms" class="sui-label"><?php esc_html_e( 'Choose an option', Forminator::DOMAIN ); ?></label>

											<?php echo $this->get_polls(); // WPCS: XSS ok. ?>

											<span class="sui-error-message" style="display: none;"><?php esc_html_e( 'Please, select an option before you proceed.', Forminator::DOMAIN ); ?></span>

										</div>

										<div class="fui-simulate-footer">

											<button class="sui-button sui-button-blue wpmudev-insert-poll">
												<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
												<span class="sui-loading-text"><?php esc_html_e( 'Generate Shortcode', Forminator::DOMAIN ); ?></span>
											</button>

										</div>

									</div>

									<!-- Quizzes -->
									<div id="forminator-quizzes">

										<div class="sui-form-field">

											<label for="forminator-select-forms" class="sui-label"><?php esc_html_e( 'Choose an option', Forminator::DOMAIN ); ?></label>

											<?php echo $this->get_quizzes(); // WPCS: XSS ok. ?>

											<span class="sui-error-message" style="display: none;"><?php esc_html_e( 'Please, select an option before you proceed.', Forminator::DOMAIN ); ?></span>

										</div>

										<div class="fui-simulate-footer">

											<button class="sui-button sui-button-blue wpmudev-insert-quiz">
												<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
												<span class="sui-loading-text"><?php esc_html_e( 'Generate Shortcode', Forminator::DOMAIN ); ?></span>
											</button>

										</div>

									</div>

								</div>

							</div>

						</div>

					</div>

				</div>

			</div>

		</div>
		<?php
	}

	/**
	 * Print forms select
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_forms() {

		$html = '';

		$html .= '<select id="forminator-select-forms" name="forms" class="sui-select forminator-custom-form-list">';

			$html .= '<option value="">' . __( 'Select Custom Form', Forminator::DOMAIN ) . '</option>';

			$modules = forminator_cform_modules( 999 );

			foreach( $modules as $module ) {

				$title = forminator_get_form_name( $module['id'], 'custom_form' );

				if ( mb_strlen( $title ) > 25 ) {
					$title = mb_substr( $title, 0, 25 ) . '...';
				}

				$html .= '<option value="' . $module['id'] . '">' . $title. ' - ID: ' . $module['id'] . '</option>';

			}
		$html .= '</select>';

		return $html;

	}

	/**
	 * Print polls select
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_polls() {

		$html = '';

		$html .= '<select id="forminator-select-polls" name="forms" class="sui-select forminator-insert-poll">';

			$html .= '<option value="">' . __( "Select Poll", Forminator::DOMAIN ) . '</option>';

			$modules = forminator_polls_modules( 999 );

			foreach( $modules as $module ) {

				$title = forminator_get_form_name( $module['id'], 'poll');

				if ( mb_strlen( $title ) > 25 ) {
					$title = mb_substr( $title, 0, 25 ) . '...';
				}

				$html .= '<option value="' . $module['id'] . '">' . $title . ' - ID: ' . $module['id'] . '</option>';

			}

		$html .= '</select>';

		return $html;
	}

	/**
	 * Print quizzes select
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_quizzes() {

		$html = '';

		$html .= '<select id="forminator-select-quizzes" name="forms" class="sui-select forminator-quiz-list">';

			$html .= '<option value="">' . __( "Select Quiz", Forminator::DOMAIN ) . '</option>';

			$modules = forminator_quizzes_modules( 999 );

			foreach( $modules as $module ) {

				$title = forminator_get_form_name( $module['id'], 'quiz');

				if ( mb_strlen( $title ) > 25 ) {
					$title = mb_substr( $title, 0, 25 ) . '...';
				}

				$html .= '<option value="' . $module['id'] . '">' . $title . ' - ID: ' . $module['id'] . '</option>';

			}

		$html .= '</select>';

		return $html;

	}
}
