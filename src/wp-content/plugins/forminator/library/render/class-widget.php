<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Widget
 *
 * @since 1.0
 */
class Forminator_Widget extends WP_Widget {

	/**
	 * Forminator_Widget constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		parent::__construct(
			'forminator_widget',
			__( "Forminator Widget", Forminator::DOMAIN ),
			array( 'description' => __( 'Forminator Widget', Forminator::DOMAIN ) )
		);
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @since 1.0
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {

		// Print widget before markup
		if ( isset( $args['before_widget'] ) && ! empty( $args['before_widget'] ) ) {
			echo $args['before_widget']; // WPCS: XSS ok.
		}

		// widget title
		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );
		if ( ! empty( $title ) ) {
			echo ( isset( $args['before_title'] ) ? $args['before_title'] : '' ) . $instance['title'] . ( isset( $args['after_title'] ) ? $args['after_title'] : '' ); // WPCS: XSS ok.
		}

		// Make sure $form_type is set
		if ( isset( $instance['form_type'] ) && ! empty ( $instance['form_type'] ) ) {
			switch ( $instance['form_type'] ) {
				case 'form':
					if ( isset( $instance['form_id'] ) && ! empty( $instance['form_id'] ) ) {
						echo forminator_form( $instance['form_id'], false );// wpcs xss ok.
					}
					break;
				case 'poll':
					if ( isset( $instance['poll_id'] ) && ! empty( $instance['poll_id'] ) ) {
						echo forminator_poll( $instance['poll_id'], false );// wpcs xss ok.
					}
					break;
				case 'quiz':
					if ( isset( $instance['quiz_id'] ) && ! empty( $instance['quiz_id'] ) ) {
						echo forminator_quiz( $instance['quiz_id'], false );// wpcs xss ok.
					}
					break;
				default:
					break;
			}
		}

		// Print widget after markup
		if ( isset( $args['after_widget'] ) && ! empty( $args['after_widget'] ) ) {
			echo $args['after_widget']; // WPCS: XSS ok.
		}
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @since 1.0
	 * @since 1.3 add return empty string to comply with WP_Widget
	 *
	 * @param array $instance The widget options
	 *
	 * @return string
	 */
	public function form( $instance ) {
		$widget_title = '';
		$form_type    = '';
		$form_id      = '';
		$poll_id      = '';
		$quiz_id      = '';

		if ( isset( $instance['title'] ) ) {
			$widget_title = $instance['title'];
		}

		if ( isset( $instance['form_type'] ) ) {
			$form_type = $instance['form_type'];
		}

		if ( isset( $instance['form_id'] ) ) {
			$form_id = $instance['form_id'];
		}

		if ( isset( $instance['poll_id'] ) ) {
			$poll_id = $instance['poll_id'];
		}

		if ( isset( $instance['quiz_id'] ) ) {
			$quiz_id = $instance['quiz_id'];
		}
		$form_style = '' === $form_type || 'form' === $form_type ? 'block' : 'none';
		$poll_style = 'poll' === $form_type ? 'block' : 'none';
		$quiz_style = 'quiz' === $form_type ? 'block' : 'none';
		?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				<?php esc_html_e( "Title", Forminator::DOMAIN ); ?>
			</label>
			<input
					type="text"
					class="widefat"
					id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
					name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
					value="<?php echo esc_attr( $widget_title ); ?>">
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'form_type' ) ); ?>">
				<?php esc_html_e( "Form Type", Forminator::DOMAIN ); ?>
			</label>
			<select class="widefat forminator-form-type" id="<?php echo esc_attr( $this->get_field_id( 'form_type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'form_type' ) ); ?>">
				<option value="form" <?php selected( 'form', $form_type ); ?>><?php esc_html_e( "Form", Forminator::DOMAIN ); ?></option>
				<option value="poll" <?php selected( 'poll', $form_type ); ?>><?php esc_html_e( "Poll", Forminator::DOMAIN ); ?></option>
				<option value="quiz" <?php selected( 'quiz', $form_type ); ?>><?php esc_html_e( "Quiz", Forminator::DOMAIN ); ?></option>
			</select>
		</p>

		<p id="forminator-wrapper-form" class="forminator-form-wrapper" style="display:<?php echo $form_style; ?>">
			<label for="<?php echo esc_attr( $this->get_field_id( 'form_id' ) ); ?>">
				<?php esc_html_e( "Select Form", Forminator::DOMAIN ); ?>
			</label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'form_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'form_id' ) ); ?>">
				<?php
				$modules = forminator_cform_modules( 999 );
				foreach ( $modules as $module ) {
					$title = forminator_get_form_name( $module['id'], 'custom_form' );
					if ( strlen( $title ) > 25 ) {
						$title = substr( $title, 0, 25 ) . '...';
					}
					echo '<option value="' . $module['id'] . '" ' . selected( $module['id'], $form_id, false ) . '>' . $title . ' - ID: ' . $module['id'] . '</option>'; // WPCS: XSS ok.
				}
				?>
			</select>
		</p>

		<p id="forminator-wrapper-poll" class="forminator-form-wrapper" style="display:<?php echo $poll_style; ?>">
			<label for="<?php echo esc_attr( $this->get_field_id( 'poll_id' ) ); ?>">
				<?php esc_html_e( "Select Poll", Forminator::DOMAIN ); ?>
			</label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'poll_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'poll_id' ) ); ?>">
				<?php
				$modules = forminator_polls_modules( 999 );
				foreach ( $modules as $module ) {
					$title = forminator_get_form_name( $module['id'], 'poll' );
					if ( strlen( $title ) > 25 ) {
						$title = substr( $title, 0, 25 ) . '...';
					}
					echo '<option value="' . $module['id'] . '" ' . selected( $module['id'], $poll_id, false ) . '>' . $title . ' - ID: ' . $module['id'] . '</option>'; // WPCS: XSS ok.
				}
				?>
			</select>
		</p>

		<p id="forminator-wrapper-quiz" class="forminator-form-wrapper" style="display:<?php echo $quiz_style; ?>">
			<label for="<?php echo esc_attr( $this->get_field_id( 'quiz_id' ) ); ?>">
				<?php esc_html_e( "Select Quiz", Forminator::DOMAIN ); ?>
			</label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'quiz_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'quiz_id' ) ); ?>">
				<?php
				$modules = forminator_quizzes_modules( 999 );
				foreach ( $modules as $module ) {
					$title = forminator_get_form_name( $module['id'], 'quiz' );
					if ( strlen( $title ) > 25 ) {
						$title = substr( $title, 0, 25 ) . '...';
					}
					echo '<option value="' . $module['id'] . '" ' . selected( $module['id'], $quiz_id, false ) . '>' . $title . ' - ID: ' . $module['id'] . '</option>'; // WPCS: XSS ok.
				}
				?>
			</select>
		</p>

		<script type="text/javascript">
			jQuery(document).ready(function () {
				jQuery(".forminator-form-type").change(function () {
					var value   = jQuery(this).val(),
					    $widget = jQuery(this).closest('.widget-content')
					;

					$widget.find(".forminator-form-wrapper").hide();
					$widget.find("#forminator-wrapper-" + value).show();
				});
            });
		</script>
		<?php
		return '';
	}

	/**
	 * Processing widget options on save
	 *
	 * @since 1.0
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();

		if ( isset( $new_instance['title'] ) ) {
			$instance['title'] = trim( wp_strip_all_tags( $new_instance['title'] ) );
		}

		if ( isset( $new_instance['form_type'] ) ) {
			$instance['form_type'] = $new_instance['form_type'];
		}

		if ( isset( $new_instance['form_id'] ) ) {
			$instance['form_id'] = $new_instance['form_id'];
		}

		if ( isset( $new_instance['poll_id'] ) ) {
			$instance['poll_id'] = $new_instance['poll_id'];
		}

		if ( isset( $new_instance['quiz_id'] ) ) {
			$instance['quiz_id'] = $new_instance['quiz_id'];
		}

		return $instance;
	}
}

/**
 * Register widget
 *
 * @since 1.0
 */
function forminator_widget_register_widget() {
	register_widget( 'forminator_widget' );
}

add_action( 'widgets_init', 'forminator_widget_register_widget' );
