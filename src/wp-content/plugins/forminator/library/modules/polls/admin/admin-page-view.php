<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Poll_Page
 *
 * @since 1.0
 */
class Forminator_Poll_Page extends Forminator_Admin_Page {

	/**
	 * Page number
	 *
	 * @var int
	 */
	protected $page_number = 1;

	/**
	 * Init
	 *
	 * @since 1.0
	 */
	public function init() {
		$pagenum           = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 0; // WPCS: CSRF OK
		$this->page_number = max( 1, $pagenum );
		$this->processRequest();
	}

	/**
	 * Trigger before render
	 */
	public function before_render() {
		wp_enqueue_script( 'forminator-chart', forminator_plugin_url() . 'assets/js/library/Chart.bundle.min.js', array( 'jquery' ), '2.7.2', false );
	}

	/**
	 * Count modules
	 *
	 * @param $status
	 *
	 * @since 1.0
	 * @return int
	 */
	public function countModules( $status = '' ) {
		return Forminator_Poll_Form_Model::model()->count_all( $status );
	}

	/**
	 * Get models
	 *
	 * @return Forminator_Base_Form_Model[]
	 *
	 * @since 1.0
	 */
	public function get_models() {
		$data = Forminator_Poll_Form_Model::model()->get_all_paged( $this->page_number );

		return $data;
	}

	/**
	 * Get rate
	 *
	 * @since 1.0
	 *
	 * @param $module
	 *
	 * @return float|int
	 */
	public function getRate( $module ) {
		if ( $module['views'] > 0 ) {
			$rate = round( ( $module["entries"] * 100 ) / $module["views"], 1 );
		} else {
			$rate = 0;
		}

		return $rate;
	}

	/**
	 * Get modules
	 *
	 * @since 1.0
	 * @return array
	 */
	public function getModules() {
		$modules   = array();
		$data      = $this->get_models();
		$form_view = Forminator_Form_Views_Model::get_instance();

		// Fallback
		if ( ! isset( $data['models'] ) || empty( $data['models'] ) ) {
			return $modules;
		}

		foreach ( $data['models'] as $model ) {
			$modules[] = array(
				"id"              => $model->id,
				"title"           => $model->name,
				"entries"         => Forminator_Form_Entry_Model::count_entries( $model->id ),
				"last_entry_time" => forminator_get_latest_entry_time_by_form_id( $model->id ),
				"views"           => $form_view->count_views( $model->id ),
				"date"            => date( get_option( 'date_format' ), strtotime( $model->raw->post_date ) ),
				'status'          => $model->status,
				'name'            => forminator_get_name_from_model( $model ),
			);
		}

		return $modules;
	}

	/**
	 * Process request
	 *
	 * @since 1.0
	 */
	public function processRequest() {
		if ( ! isset( $_POST['forminatorNonce'] ) ) {
			return;
		}

		$nonce = $_POST['forminatorNonce']; // WPCS: CSRF OK
		if ( ! wp_verify_nonce( $nonce, 'forminatorPollFormRequest' ) ) {
			return;
		}

		$is_redirect = true;
		$action      = $_POST['forminator_action'];
		switch ( $action ) {
			case 'delete':
				$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
				if ( ! empty( $id ) ) {
					$this->delete_module( $id );
				}
				break;

			case 'clone':
				$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
				if ( ! empty( $id ) ) {
					$this->clone_module( $id );
				}
				break;

			case 'reset-views' :
				$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
				if ( ! empty( $id ) ) {
					$this->reset_module_views( $id );
				}
				break;

			case 'delete-votes' :
				$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
				if ( ! empty( $id ) ) {
					$this->delete_module_entries( $id );
				}
				break;

			case 'export':
				$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
				if ( ! empty( $id ) ) {
					$this->export_module( $id );
				}
				$is_redirect = false;
				break;

			case 'delete-polls' :
				$ids = isset( $_POST['ids'] ) ? $_POST['ids'] : '';
				if ( ! empty( $ids ) ) {
					$form_ids = explode( ',', $ids );
					if ( is_array( $form_ids ) && count( $form_ids ) > 0 ) {
						foreach ( $form_ids as $id ) {
							$this->delete_module( $id );
						}
					}
				}
				break;

			case 'delete-votes-polls' :
				$ids = isset( $_POST['ids'] ) ? $_POST['ids'] : '';
				if ( ! empty( $ids ) ) {
					$form_ids = explode( ',', $ids );
					if ( is_array( $form_ids ) && count( $form_ids ) > 0 ) {
						foreach ( $form_ids as $id ) {
							$this->delete_module_entries( $id );
						}
					}
				}
				break;

			case 'clone-polls' :
				$ids = isset( $_POST['ids'] ) ? $_POST['ids'] : '';
				if ( ! empty( $ids ) ) {
					$form_ids = explode( ',', $ids );
					if ( is_array( $form_ids ) && count( $form_ids ) > 0 ) {
						foreach ( $form_ids as $form_id ) {
							$this->clone_module( $form_id );
						}
					}
				}
				break;

			case 'reset-views-polls' :
				$ids = isset( $_POST['ids'] ) ? $_POST['ids'] : '';
				if ( ! empty( $ids ) ) {
					$form_ids = explode( ',', $ids );
					if ( is_array( $form_ids ) && count( $form_ids ) > 0 ) {
						foreach ( $form_ids as $id ) {
							$this->reset_module_views( $id );
						}
					}
				}
				break;
			case 'update-status' :
				$id     = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
				$status = isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : '';

				if ( ! empty( $id ) && ! empty( $status ) ) {
					// only publish and draft status avail
					if ( in_array( $status, array( 'publish', 'draft' ), true ) ) {
						$model = Forminator_Poll_Form_Model::model()->load( $id );
						if ( $model instanceof Forminator_Poll_Form_Model ) {
							$model->status = $status;
							$model->save();
						}
					}
				}
				break;
			case 'update-statuses' :
				$ids    = isset( $_POST['ids'] ) ? $_POST['ids'] : '';
				$status = isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : '';

				if ( ! empty( $ids ) && ! empty( $status ) ) {
					// only publish and draft status avail
					if ( in_array( $status, array( 'publish', 'draft' ), true ) ) {
						$form_ids = explode( ',', $ids );
						if ( is_array( $form_ids ) && count( $form_ids ) > 0 ) {
							foreach ( $form_ids as $id ) {
								$model = Forminator_Poll_Form_Model::model()->load( $id );
								if ( $model instanceof Forminator_Poll_Form_Model ) {
									$model->status = $status;
									$model->save();
								}
							}
						}
					}
				}
				break;
			default:
				break;
		}

		if ( $is_redirect ) {
			//todo add messaging as flash
			$fallback_redirect = admin_url( 'admin.php' );
			$fallback_redirect = add_query_arg(
				array(
					'page' => $this->get_admin_page(),
				),
				$fallback_redirect
			);
			$this->maybe_redirect_to_referer( $fallback_redirect );
		}

		exit;
	}

	/**
	 * Bulk actions
	 *
	 * @since 1.0
	 * @return array
	 */
	public function bulk_actions() {
		return apply_filters(
			'forminator_polls_bulk_actions',
			array(
				'clone-polls'        => __( "Duplicate", Forminator::DOMAIN ),
				'reset-views-polls'  => __( "Reset Tracking Data", Forminator::DOMAIN ),
				'delete-votes-polls' => __( "Delete Votes", Forminator::DOMAIN ),
				'delete-polls'       => __( "Delete", Forminator::DOMAIN ),
			) );
	}

	/**
	 * Pagination
	 *
	 * @since 1.0
	 */
	public function pagination() {
		$count = $this->countModules();
		forminator_list_pagination( $count );
	}

	/**
	 * Clone Module
	 *
	 * @since 1.6
	 *
	 * @param $id
	 */
	public function clone_module( $id ) {
		//check if this id is valid and the record is exists
		$model = Forminator_Poll_Form_Model::model()->load( $id );
		if ( is_object( $model ) ) {
			//create one
			//reset id
			$model->id = null;

			//update title
			if ( isset( $model->settings['formName'] ) ) {
				$model->settings['formName'] = sprintf( __( "Copy of %s", Forminator::DOMAIN ), $model->settings['formName'] );
			}

			//save it to create new record
			$new_id = $model->save( true );
			forminator_clone_poll_ip_address_retention( $id, $new_id );
		}
	}

	/**
	 * Reset views data
	 *
	 * @since 1.6
	 *
	 * @param $id
	 */
	public function reset_module_views( $id ) {
		$form_view = Forminator_Form_Views_Model::get_instance();
		$model     = Forminator_Poll_Form_Model::model()->load( $id );
		if ( is_object( $model ) ) {
			$form_view->delete_by_form( $id );
		}
	}

	/**
	 * Delete module
	 *
	 * @since 1.6
	 *
	 * @param $id
	 */
	public function delete_module( $id ) {
		//check if this id is valid and the record is exists
		$model = Forminator_Poll_Form_Model::model()->load( $id );
		if ( is_object( $model ) ) {
			Forminator_Form_Entry_Model::delete_by_form( $id );
			$form_view = Forminator_Form_Views_Model::get_instance();
			$form_view->delete_by_form( $id );
			forminator_update_poll_ip_address_retention( $id, null, null );
			wp_delete_post( $id );
		}
	}

	/**
	 * Delete module entries
	 *
	 * @since 1.6
	 *
	 * @param $id
	 */
	public function delete_module_entries( $id ) {
		//check if this id is valid and the record is exists
		$model = Forminator_Poll_Form_Model::model()->load( $id );
		if ( is_object( $model ) ) {
			Forminator_Form_Entry_Model::delete_by_form( $id );
		}
	}

	/**
	 * Export module
	 *
	 * @since 1.6
	 *
	 * @param $id
	 */
	public function export_module( $id ) {

		$exportable = array();
		$model_name = '';
		$model      = Forminator_Poll_Form_Model::model()->load( $id );
		if ( $model instanceof Forminator_Poll_Form_Model ) {
			$model_name = $model->name;
			$exportable = $model->to_exportable_data();
		}
		$encoded = wp_json_encode( $exportable );
		$fp      = fopen( 'php://memory', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen
		fwrite( $fp, $encoded ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fwrite
		fseek( $fp, 0 );

		$filename = 'forminator-' . sanitize_title( $model_name ) . '-poll-export' . '.txt';

		header( 'Content-Description: File Transfer' );
		header( 'Content-Type: text/plain' );
		header( 'Content-Disposition: attachment; filename="' . basename( $filename ) . '"' );
		header( 'Cache-Control: must-revalidate' );
		header( 'Content-Length: ' . strlen( $encoded ) );

		// make php send the generated csv lines to the browser
		fpassthru( $fp );
	}
}
