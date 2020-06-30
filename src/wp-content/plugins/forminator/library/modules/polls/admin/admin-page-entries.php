<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Poll_View_Page
 *
 * @since 1.0
 */
class Forminator_Poll_View_Page extends Forminator_Admin_Page {

	/**
	 * Current model
	 *
	 * @var Forminator_Poll_Form_Model
	 */
	protected $model = false;

	/**
	 * Current form id
	 *
	 * @var int
	 */
	protected $form_id = 0;

	/**
	 * Fields
	 *
	 * @var array
	 */
	protected $fields = array();

	/**
	 * Visible Fields
	 *
	 * @var array
	 */
	protected $visible_fields = array();

	/**
	 * Number of checked fields
	 *
	 * @var int
	 */
	protected $checked_fields = 0;

	/**
	 * Number of total fields
	 *
	 * @var int
	 */
	protected $total_fields = 0;

	/**
	 * Error message if avail
	 *
	 * @var string
	 */
	protected $error_message = '';

	/**
	 * Initialise variables
	 *
	 * @since 1.0
	 */
	public function before_render() {
		// This view is unused from 1.5.4 on, using "forminator-entries" instead.
		if ( 'forminator-poll-view' === $this->page_slug ) {
			$url = '?page=forminator-entries&form_type=forminator_polls';
			if ( isset( $_REQUEST['form_id'] ) ) { // WPCS: CSRF OK
				$url .= '&form_id=' .  intval( $_REQUEST['form_id'] ); // WPCS: CSRF OK
			}
			if ( wp_safe_redirect( $url ) ) {
				exit;
			}
		}

		if ( isset( $_REQUEST['form_id'] ) ) { // WPCS: CSRF OK
			$this->form_id = intval( $_REQUEST['form_id'] );
			$this->model   = Forminator_Poll_Form_Model::model()->load( $this->form_id );
			if ( is_object( $this->model ) ) {
				$this->fields = $this->model->get_fields();
				if ( is_null( $this->fields ) ) {
					$this->fields = array();
				}

				if ( Forminator_Form_Entry_Model::count_entries( $this->form_id ) ) {
					add_action( 'admin_footer', array( $this, 'render_pie_chart' ), 100 );
					add_action( 'admin_footer', array( $this, 'chart_style_override' ), 100 );
				}

			} else {
				$this->model = false;
			}
			$this->total_fields   = count( $this->fields ) + 1;
			$this->checked_fields = $this->total_fields;
			$this->process_request();
		}
	}

	/**
	 * Process request
	 *
	 * @since 1.0
	 */
	public function process_request() {

		if ( isset( $_GET['err_msg'] ) ) {
			$this->error_message = wp_kses_post( $_GET['err_msg'] );
		}

		if ( ! isset( $_POST['forminatorEntryNonce'] ) ) {
			return;
		}

		$nonce = $_POST['forminatorEntryNonce']; // WPCS: CSRF OK
		if ( ! wp_verify_nonce( $nonce, 'forminatorPollEntries' ) ) {
			return;
		}

		if ( isset( $_POST['field'] ) ) {
			$this->visible_fields = $_POST['field'];
			$this->checked_fields = count( $this->visible_fields );
		}

		if ( isset( $_POST['forminator_action'] ) ) {
			switch ( $_POST['forminator_action'] ) {
				case 'delete':
					if ( isset( $_POST['id'] ) ) {
						$id = $_POST['id'];

						Forminator_Form_Entry_Model::delete_by_form( $id );
						$this->maybe_redirect_to_referer();
						exit;
					}
					break;
				default:
					break;
			}
		}
	}

	/**
	 * Register content boxes
	 *
	 * @since 1.0
	 */
	public function register_content_boxes() {
		$this->add_box(
			'custom-form/entries/popup/exports-list',
			__( 'Your Exports', Forminator::DOMAIN ),
			'entries-popup-exports-list',
			null,
			null,
			null
		);

		$this->add_box(
			'custom-form/entries/popup/schedule-export',
			__( 'Edit Schedule Export', Forminator::DOMAIN ),
			'entries-popup-schedule-export',
			null,
			null,
			null
		);
	}

	/**
	 * Get fields
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_fields() {
		return $this->fields;
	}

	/**
	 * Visible fields
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_visible_fields() {
		return $this->visible_fields;
	}

	/**
	 * Checked field option
	 *
	 * @since 1.0
	 *
	 * @param string $slug - the field slug
	 *
	 * @return string
	 */
	public function checked_field( $slug ) {
		if ( ! empty( $this->visible_fields ) && is_array( $this->visible_fields ) ) {
			if ( in_array( $slug, $this->visible_fields, true ) ) {
				return checked( $slug, $slug );
			} else {
				return '';
			}
		}

		return checked( $slug, $slug );
	}

	/**
	 * Get model name
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_model_name() {
		if ( $this->model ) {
			return isset ( $this->model->settings['poll-title'] ) ? $this->model->settings['poll-title'] : $this->model->name;
		}

		return '';
	}

	/**
	 * Get poll question
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_poll_question() {
		if ( $this->model && isset ( $this->model->settings['poll-question'] ) ) {
			return $this->model->settings['poll-question'];
		}

		return '';
	}

	/**
	 * Get poll description
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_poll_description() {
		if ( $this->model && isset ( $this->model->settings['poll-description'] ) ) {
			return $this->model->settings['poll-description'];
		}

		return '';
	}

	/**
	 * Results chart design
	 *
	 * @since 1.0
	 * @return string
	 */
	private function get_chart_design() {
		if ( $this->model && isset ( $this->model->settings['results-style'] ) ) {
			return $this->model->settings['results-style'];
		}

		return 'bar';
	}

	/**
	 * Fields header
	 *
	 * @since 1.0
	 * @return string
	 */
	public function fields_header() {
		echo esc_html( sprintf( __( 'Showing %$1s of %$2s fields', Forminator::DOMAIN ), $this->checked_fields, $this->total_fields ) );
	}

	/**
	 * Get fields table
	 *
	 * @since 1.0
	 * @return Forminator_Entries_List_Table
	 */
	public function get_table() {
		return new Forminator_Entries_List_Table(
			array(
				'model'          => $this->model,
				'visible_fields' => $this->visible_fields,
			) );
	}

	/**
	 * Get custom votes
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_custom_votes() {
		$custom_votes = array();
		if ( is_object( $this->model ) ) {
			$entries = Forminator_Form_Entry_Model::get_entries( $this->model->id );
			foreach ( $entries as $entry ) {
				$custom_votes[] = $entry->get_meta( 'extra', '' );
			}
			if ( ! empty( $custom_votes ) ) {
				$custom_votes = array_unique( $custom_votes );
			}
		}

		return $custom_votes;
	}

	/**
	 * Map Custom Votes
	 *
	 * @since   1.0.5
	 * @example {
	 *  'ELEMENT_ID' => [
	 *      'EXTRA_VALUE' => COUNT
	 *  ],
	 * 'answer-2' => [
	 *      'skip it' => 9
	 *  ]
	 * }
	 *
	 * @return array
	 */
	public function map_custom_votes() {
		$custom_votes = array();
		if ( is_object( $this->model ) ) {
			$fields_with_extra_enabled = array();

			$fields_array = $this->model->get_fields_as_array();
			// Trigger Update DB if needed.
			Forminator_Form_Entry_Model::map_polls_entries( $this->model->id, $fields_array );

			$fields = $this->model->get_fields();

			foreach ( (array) $fields as $field ) {
				if ( filter_var( $field->use_extra, FILTER_VALIDATE_BOOLEAN ) === true ) {
					$fields_with_extra_enabled[] = $field->slug;
				}
			}

			if ( ! empty( $fields_with_extra_enabled ) ) {
				$custom_votes = Forminator_Form_Entry_Model::count_polls_with_extra( $this->model->id, $fields_with_extra_enabled );
			}
		}

		return $custom_votes;
	}

	/**
	 * Get Element Title
	 *
	 * @since 1.0.5
	 *
	 * @param $element_id
	 *
	 * @return mixed
	 */
	public function get_field_title( $element_id ) {
		$fields = $this->model->pluck_fields_array( 'title', 'element_id', $element_id );

		return ( isset( $fields[ $element_id ] ) ? $fields[ $element_id ] : $element_id );
	}

	/**
	 * Render the chart
	 * Generate the google charts js for the chart
	 *
	 * @since 1.0
	 */
	public function render_pie_chart() {

		$chart_colors         = forminator_get_poll_chart_colors( $this->model->id );
		$default_chart_colors = $chart_colors;
		$chart_design         = $this->get_chart_design();
		$chart_data           = forminator_get_chart_data( $this->model );
		?>

		<script type="text/javascript">

			( function ( $, doc ) {

				'use strict';

				$( 'document' ).ready( function() {

					var randomScalingFactor = function() {
						return Math.round( Math.random() * 100 );
					};

					var chartId   = '#forminator-chart-poll';
					var chartData = <?php echo wp_json_encode( $chart_data ); ?>;
					var chartDesign = '<?php echo esc_html( $chart_design ); ?>';

					var chartExtras = [
						'<?php echo esc_html__( 'vote(s)' ); ?>',
						true, // Always show votes
						[
							'#E5E5E5', // [0] Grid lines color
							'#777771', // [1] Axis labels color
							'#333333'  // [2] On-chart label (bars)
						],
						[
							'#333333', // [0] Background color
							'#FFFFFF' // [1] Text color
						]
					];

					FUI.pollChart( chartId, chartData, chartDesign, chartExtras );

					var chartCanvas  = $( '#forminator-chart-poll' ),
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
	 * Get current error message
	 *
	 * @return string
	 *
	 * @since 1.5.2
	 */
	public function error_message() {
		return $this->error_message;
	}

	/**
	 * Override style used for displayed chart
	 *
	 * @since 1.5.4
	 *
	 * @return void
	 */
	public function chart_style_override() {
		ob_start();
		?>
		<style>
			.forminator-poll--chart svg > g:last-child > g:last-child {
				pointer-events: none;
			}

			.forminator-poll--chart .google-visualization-tooltip {
				pointer-events: none;
			}

			.forminator-poll--chart .google-visualization-tooltip-item {
				white-space: nowrap;
			}
		</style>
		<?php
		$style = ob_get_clean();
		/**
		 * Filter styles to be attached in poll submissions page
		 *
		 * @since 1.5.4
		 *
		 * @param string $style
		 */
		$style = apply_filters( 'forminator_admin_poll_chart_style_override', $style );
		echo $style; // WPCS: XSS ok.
	}
}
