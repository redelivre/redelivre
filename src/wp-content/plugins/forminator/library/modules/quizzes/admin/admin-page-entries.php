<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Quizz_View_Page
 *
 * @since 1.0
 */
class Forminator_Quizz_View_Page extends Forminator_Admin_Page {

	/**
	 * Current model
	 *
	 * @var bool|Forminator_Quiz_Form_Model
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
	 * Per page
	 *
	 * @var int
	 */
	protected $per_page = 10;

	/**
	 * Error message if avail
	 *
	 * @var string
	 */
	protected $error_message = '';

	/**
	 * @since 1.6.2
	 * @var Forminator_Addon_Abstract[]
	 */
	private static $registered_addons = null;

	/**
	 * Initialise variables
	 *
	 * @since 1.0
	 */
	public function before_render() {
		// This view is unused from 1.5.4 on, using "forminator-entries" instead.
		if ( 'forminator-quiz-view' === $this->page_slug ) {
			$url = '?page=forminator-entries&form_type=forminator_quizzes';
			if ( isset( $_REQUEST['form_id'] ) ) { // WPCS: CSRF OK
				$url .= '&form_id=' . intval( $_REQUEST['form_id'] ); // WPCS: CSRF OK
			}
			if ( wp_safe_redirect( $url ) ) {
				exit;
			}
		}

		if ( isset( $_REQUEST['form_id'] ) ) { // WPCS: CSRF OK
			$this->form_id = intval( $_REQUEST['form_id'] );
			$this->model   = Forminator_Quiz_Form_Model::model()->load( $this->form_id );
			if ( is_object( $this->model ) ) {
				$this->fields = $this->model->get_fields();
				if ( is_null( $this->fields ) ) {
					$this->fields = array();
				}
			} else {
				$this->model = false;
			}
			$this->per_page       = forminator_form_view_per_page( 'entries' );
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
		if ( wp_verify_nonce( $nonce, 'forminatorQuizEntries' ) ) {
			if ( isset( $_POST['field'] ) ) {
				$this->visible_fields = $_POST['field'];
				$this->checked_fields = count( $this->visible_fields );
			}

			return;
		}

		$action = '';
		if ( wp_verify_nonce( $nonce, 'forminator_quiz_bulk_action' ) ) {
			if ( isset( $_POST['entries-action'] ) || isset( $_POST['entries-action-bottom'] ) ) {
				if ( isset( $_POST['entries-action'] ) && ! empty( $_POST['entries-action'] ) ) {
					$action = $_POST['entries-action'];
				} elseif ( isset( $_POST['entries-action-bottom'] ) ) {
					$action = $_POST['entries-action-bottom'];
				}

				switch ( $action ) {
					case 'delete-all' :
						if ( isset( $_POST['ids'] ) && is_array( $_POST['ids'] ) ) {
							$entries = implode( ",", $_POST['ids'] );
							Forminator_Form_Entry_Model::delete_by_entrys( $this->model->id, $entries );
							$url = add_query_arg( '', '' );
							wp_safe_redirect( $url );
							exit;
						}
						break;
					default:
						break;
				}
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
			return $this->model->name;
		}

		return '';
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
	 * @return array
	 */
	public function get_table() {
		$per_page = $this->get_per_page();
		$entries  = Forminator_Form_Entry_Model::list_entries( $this->form_id, $per_page, ( $this->get_paged() - 1 ) * $per_page );

		return $entries;
	}

	/**
	 * Get paged
	 *
	 * @since 1.0
	 * @return int
	 */
	public function get_paged() {
		$paged = isset( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 1;

		return $paged;
	}

	/**
	 * Get the results per page
	 *
	 * @since 1.0.3
	 *
	 * @return int
	 */
	public function get_per_page() {
		return $this->per_page;
	}

	/**
	 * @since 1.0
	 * @return int
	 */
	public function get_total_entries() {
		$count = Forminator_Form_Entry_Model::count_entries( $this->form_id );

		return $count;
	}

	/**
	 * Get form type
	 *
	 * @since 1.0
	 * @return mixed
	 */
	public function get_form_type() {
		return $this->model->quiz_type;
	}

	/**
	 * Bulk actions
	 *
	 * @since 1.0
	 *
	 * @param string $position
	 */
	public function bulk_actions( $position = 'top' ) { ?>

		<select name="<?php echo ( 'top' === $position ) ? 'entries-action' : 'entries-action-bottom'; ?>"
		        class="sui-select-sm sui-select-inline"
		        style="min-width: 200px;">
			<option value=""><?php esc_html_e( "Bulk Actions", Forminator::DOMAIN ); ?></option>
			<option value="delete-all"><?php esc_html_e( "Delete Entries", Forminator::DOMAIN ); ?></option>
		</select>

		<button class="sui-button"><?php esc_html_e( "Apply", Forminator::DOMAIN ); ?></button>

		<?php
	}

	/**
	 * Pagination
	 *
	 * @since 1.1
	 */
	public function paginate() {
		$count = $this->get_total_entries();
		forminator_list_pagination( $count, 'entries' );
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
	 * Get integrations data
	 *
	 * @since 1.6.2
	 *
	 * @param Forminator_Form_Entry_Model $entry
	 *
	 * @return array
	 */
	public function get_integrations_data_from_entry( Forminator_Form_Entry_Model $entry ) {
		return $this->attach_addon_on_render_entry( $entry );
	}

	/**
	 * Get Globally registered Addons, avoid overhead for checking registered addons many times
	 *
	 * @since 1.6.2
	 *
	 * @return array|Forminator_Addon_Abstract[]
	 */
	public function get_registered_addons() {
		if ( empty( self::$registered_addons ) ) {
			self::$registered_addons = array();

			$registered_addons = forminator_get_registered_addons();
			foreach ( $registered_addons as $registered_addon ) {
				try {
					$quiz_hooks = $registered_addon->get_addon_quiz_hooks( $this->form_id );
					if ( $quiz_hooks instanceof Forminator_Addon_Quiz_Hooks_Abstract ) {
						self::$registered_addons[] = $registered_addon;
					}
				} catch ( Exception $e ) {
					forminator_addon_maybe_log( $registered_addon->get_slug(), 'failed to get_addon_quiz_hooks', $e->getMessage() );
				}
			}
		}

		return self::$registered_addons;
	}

	/**
	 * Executor of adding additional items on entry page
	 *
	 * @see   Forminator_Addon_Quiz_Hooks_Abstract::on_render_entry()
	 * @since 1.6.2
	 *
	 * @param Forminator_Form_Entry_Model $entry_model
	 *
	 * @return array
	 */
	private function attach_addon_on_render_entry( Forminator_Form_Entry_Model $entry_model ) {
		$additional_items = array();
		//find all registered addons, so history can be shown even for deactivated addons
		$registered_addons = $this->get_registered_addons();

		foreach ( $registered_addons as $registered_addon ) {
			try {
				$quiz_hooks = $registered_addon->get_addon_quiz_hooks( $this->form_id );
				$meta_data  = forminator_find_addon_meta_data_from_entry_model( $registered_addon, $entry_model );

				$addon_additional_items = $quiz_hooks->on_render_entry( $entry_model, $meta_data );// run and forget
				$addon_additional_items = self::format_addon_additional_items( $addon_additional_items );
				$additional_items       = array_merge( $additional_items, $addon_additional_items );
			} catch ( Exception $e ) {
				forminator_addon_maybe_log( $registered_addon->get_slug(), 'failed to on_render_entry', $e->getMessage() );
			}
		}

		return $additional_items;
	}

	/**
	 * Ensuring additional items for addons met the entries data requirement
	 * Format used is,
	 * - label
	 * - value
	 * - subentries[]
	 *      - label
	 *      - value
	 *
	 * @since 1.6.2
	 *
	 * @param  array $addon_additional_items
	 *
	 * @return mixed
	 */
	private static function format_addon_additional_items( $addon_additional_items ) {
		//to `name` and `value` basis
		$formatted_additional_items = array();
		if ( ! is_array( $addon_additional_items ) ) {
			return array();
		}

		foreach ( $addon_additional_items as $additional_item ) {
			// make sure label and value exist, without it, it will display empty row, so leave it
			if ( ! isset( $additional_item['label'] ) || ! isset( $additional_item['value'] ) ) {
				continue;
			}
			$sub_entries = array();

			// do below check if sub_entries available
			if ( isset( $additional_item['sub_entries'] ) && is_array( $additional_item['sub_entries'] ) ) {
				foreach ( $additional_item['sub_entries'] as $sub_entry ) {
					// make sure label and value exist, without it, it will display empty row, so leave it
					if ( ! isset( $sub_entry['label'] ) || ! isset( $sub_entry['value'] ) ) {
						continue;
					}
					$sub_entries[] = array(
						'label' => $sub_entry['label'],
						'value' => $sub_entry['value'],
					);
				}
			}

			$formatted_additional_items[] = array(
				'label'       => $additional_item['label'],
				'value'       => $additional_item['value'],
				'sub_entries' => $sub_entries,
			);
		}

		return $formatted_additional_items;
	}
}
