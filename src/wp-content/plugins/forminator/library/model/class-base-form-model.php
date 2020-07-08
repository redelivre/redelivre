<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Author: Hoang Ngo
 *
 * @property  string $status
 */
abstract class Forminator_Base_Form_Model {
	const META_KEY = 'forminator_form_meta';
	/**
	 * Form ID
	 *
	 * @int
	 */
	public $id;
	/**
	 * Form name
	 *
	 * @string
	 */
	public $name;

	/**
	 * @var string
	 */
	public $client_id;

	/**
	 * Contain fields of this form
	 *
	 * @var Forminator_Form_Field_Model[]
	 */
	public $fields = array();

	/**
	 * Form settings
	 * array
	 */
	public $settings = array();

	/**
	 * Form notification
	 * array
	 */
	public $notifications = array();

	/**
	 * WP_Post
	 */
	public $raw;
	/**
	 * This post type
	 *
	 * @string
	 */
	protected $post_type;

	const STATUS_PUBLISH = 'publish';
	const STATUS_DRAFT   = 'draft';

	/**
	 * Save form
	 *
	 * @since 1.0
	 *
	 * @param bool $clone
	 *
	 * @return mixed
	 */
	public function save( $clone = false ) {
		//todo use save_post for saving the form and update_post_meta for saving fields
		//prepare the data
		$maps      = array_merge( $this->get_default_maps(), $this->get_maps() );
		$post_data = array();
		$meta_data = array();
		if ( ! empty( $maps ) ) {
			foreach ( $maps as $map ) {
				$attribute = $map['property'];
				if ( 'post' === $map['type'] ) {
					if ( isset( $this->$attribute ) ) {
						$post_data[ $map['field'] ] = $this->$attribute;
					} elseif ( isset( $map['default'] ) ) {
						$post_data[ $map['field'] ] = $map['default'];
					}
				} else {
					if ( 'fields' === $map['field'] ) {
						$meta_data[ $map['field'] ] = $this->get_fields_as_array();
					} else {
						$meta_data[ $map['field'] ] = $this->$attribute;
					}
				}
			}
		}

		$post_data['post_type'] = $this->post_type;

		//storing
		if ( is_null( $this->id ) ) {
			$id = wp_insert_post( $post_data );
		} else {
			$id = wp_update_post( $post_data );
		}

		// If cloned we have to update the fromID
		if ( $clone ) {
			$meta_data['settings']['form_id'] = $id;
		}

		update_post_meta( $id, self::META_KEY, $meta_data );

		return $id;
	}	

	/**
	 * @since 1.0
	 * @return Forminator_Form_Field_Model[]
	 */
	public function get_fields() {
		return $this->fields;
	}

	/**
	 * @since 1.0
	 * @since 1.2 Add $sanitize_function as optional param
	 *
	 * @param        $property
	 * @param        $name
	 * @param        $array
	 * @param string $sanitize_function custom sanitize function to use, default is sanitize_title
	 */
	public function set_var_in_array( $property, $name, $array, $sanitize_function = 'sanitize_title' ) {
		$val = isset( $array[ $name ] ) ? $array[ $name ] : null;
		if ( is_callable( $sanitize_function ) ) {
			$val = call_user_func_array( $sanitize_function, array( $val ) );
		}
		$this->$property = $val;
	}

	/**
	 * Add field
	 *
	 * @since 1.0
	 *
	 * @param $field
	 */
	public function add_field( $field ) {
		$this->fields[] = $field;
	}

	/**
	 * Get field
	 *
	 * @since 1.0
	 *
	 * @param $slug
	 *
	 * @return Forminator_Form_Field|null
	 */
	public function get_field( $slug ) {
		//get a field and return as object
		return isset( $this->fields[ $slug ] ) ? $this->fields[ $slug ] : null;
	}

	/**
	 * Remove field
	 *
	 * @since 1.0
	 *
	 * @param $slug
	 */
	public function remove_field( $slug ) {
		unset( $this->fields[ $slug ] );
	}

	/**
	 * Clear fields
	 *
	 * @since 1.0
	 */
	public function clear_fields() {
		$this->fields = array();
	}

	/**
	 * Load model
	 *
	 * @since 1.0
	 *
	 * @param $id
	 *
	 * @return bool|$this
	 */
	public function load( $id, $callback = false ) {
		$post = get_post( $id );

		if ( ! is_object( $post ) ) {
			// If we haven't saved yet, fallback to latest ID and replace the data
			if ( $callback ) {
				$id   = $this->get_latest_id();
				$post = get_post( $id );

				if ( ! is_object( $post ) ) {
					return false;
				}
			} else {
				return false;
			}
		}

		return $this->_load( $post );
	}

	/**
	 * Return latest id for the post_type
	 *
	 * @since 1.0
	 * @return int
	 */
	public function get_latest_id() {
		$id   = 1;
		$args = array(
			'post_type'   => $this->post_type,
			'numberposts' => 1,
			'fields'      => 'ids',
		);

		$post = get_posts( $args );

		if ( isset( $post[0] ) ) {
			$id = $post[0];
		}

		return $id;
	}

	/**
	 * Count all form types
	 *
	 * @since 1.0
	 * @since 1.6 add optional param `status`
	 *
	 * @param string $status
	 *
	 * @return int
	 */
	public function count_all( $status = '' ) {
		$count_posts = wp_count_posts( $this->post_type );
		$count_posts = (array) $count_posts;
		if ( ! empty( $status ) ) {
			if ( isset( $count_posts[ $status ] ) ) {
				return $count_posts[ $status ];
			} else {
				return 0;
			}
		}

		return array_sum( $count_posts );
	}

	/**
	 * Get all paginated
	 *
	 * @since 1.2
	 * @since 1.5.4 add optional param per_page
	 * @since 1.5.4 add optional param $status
	 *
	 * @param int      $current_page
	 * @param null|int $per_page
	 * @param string   $status
	 *
	 * @return array
	 */
	public function get_all_paged( $current_page = 1, $per_page = null, $status = '' ) {
		if ( is_null( $per_page ) ) {
			$per_page = forminator_form_view_per_page();
		}
		$args = array(
			'post_type'      => $this->post_type,
			'post_status'    => 'any',
			'posts_per_page' => $per_page,
			'paged'          => $current_page,
		);

		if ( ! empty( $status ) ) {
			$args['post_status'] = $status;
		}

		$query  = new WP_Query( $args );
		$models = array();

		foreach ( $query->posts as $post ) {
			$models[] = $this->_load( $post );
		}

		return array(
			'totalPages'   => $query->max_num_pages,
			'totalRecords' => $query->post_count,
			'models'       => $models,
		);
	}

	/**
	 * Get all
	 *
	 * @since 1.0
	 * @since 1.6 add `status` in param
	 * @since 1.6 add `limit` in param
	 *
	 * @param string $status post_status arg
	 * @param int    $limit
	 *
	 * @return array()
	 */
	public function get_all_models( $status = '', $limit = - 1 ) {
		$args = array(
			'post_type'      => $this->post_type,
			'post_status'    => 'any',
			'posts_per_page' => $limit,
		);

		if ( ! empty( $status ) ) {
			$args['post_status'] = $status;
		}
		$query  = new WP_Query( $args );
		$models = array();

		foreach ( $query->posts as $post ) {
			$models[] = $this->_load( $post );
		}

		return array(
			'totalPages'   => $query->max_num_pages,
			'totalRecords' => $query->post_count,
			'models'       => $models,
		);
	}

	/**
	 * Get modules from field id
	 *
	 * @since 1.9
	 *
	 * @return array
	 */
	public function get_models_by_field( $id ) {
		$modules = array();
		$data    = $this->get_models( 999 );

		foreach ( $data as $model ) {
			if ( $model->get_field( $id ) ) {
				$modules[] = array(
					'id'      => $model->id,
					'title'   => $model->name,
					'version' => $model->version
				);
			}
		}

		return $modules;
	}

	/**
	 * Get modules from field id & version
	 *
	 * @since 1.9
	 *
	 * @return array
	 */
	public function get_models_by_field_and_version( $id, $version ) {
		$modules   = array();
		$data      = $this->get_models(999);

		foreach ( $data as $model ) {
			if( $model->get_field( $id ) && version_compare( $model->settings['version'], $version, 'lt' ) ) {
				$modules[] = array(
					'id'      => $model->id,
					'title'   => $model->name,
					'version' => $model->settings['version']
				);
			}
		}

		return $modules;
	}

	/**
	 * Get Models
	 *
	 * @since 1.0
	 * @since 1.6 add `status` as optional param
	 *
	 * @param int    $total - the total. Defaults to 4
	 * @param string $status
	 *
	 * @return array $models
	 */
	public function get_models( $total = 4, $status = '' ) {
		$args = array(
			'post_type'      => $this->post_type,
			'post_status'    => 'any',
			'posts_per_page' => $total,
			'order'          => 'DESC',
		);
		if ( ! empty( $status ) ) {
			$args['post_status'] = $status;
		}
		$query  = new WP_Query( $args );
		$models = array();

		foreach ( $query->posts as $post ) {
			$models[] = $this->_load( $post );
		}

		return $models;
	}

	/**
	 * @since 1.0
	 *
	 * @param $post
	 *
	 * @return mixed
	 */
	private function _load( $post ) {
		if ( $this->post_type === $post->post_type ) {
			$class         = get_class( $this );
			$object        = new $class();
			$meta          = get_post_meta( $post->ID, self::META_KEY, true );
			$maps          = array_merge( $this->get_default_maps(), $this->get_maps() );
			$fields        = ! empty( $meta['fields'] ) ? $meta['fields'] : array();
			$form_settings = array(
				'version'                    => '1.0',
				'cform-section-border-color' => '#E9E9E9',
			);

			// Update version from form settings
			if ( isset( $meta['settings']['version'] ) ) {
				$form_settings['version'] = $meta['settings']['version'];
			}

			// Update section border color
			if ( isset( $meta['settings']['cform-section-border-color'] ) ) {
				$form_settings['cform-section-border-color'] = $meta['settings']['cform-section-border-color'];
			}

			if ( ! empty( $maps ) ) {
				foreach ( $maps as $map ) {
					$attribute = $map['property'];
					if ( 'post' === $map['type'] ) {
						$att                = $map['field'];
						$object->$attribute = $post->$att;
					} else {
						if ( ! empty( $meta['fields'] ) && 'fields' === $map['field'] ) {
							foreach ( $meta['fields'] as $field_data ) {
								$field          = new Forminator_Form_Field_Model( $form_settings );
								$field->form_id = $post->ID;
								$field->slug    = $field_data['id'];
								unset( $field_data['id'] );
								$field->import( $field_data );
								$object->add_field( $field );
							}
						} else {
							if ( isset( $meta[ $map['field'] ] ) ) {
								$object->$attribute = $meta[ $map['field'] ];
							}
						}
					}
				}
			}

			// Migrate settings Custom Form
			if ( 'forminator_forms' === $this->post_type ) {
				$object->settings      = Forminator_Migration::migrate_custom_form_settings( $object->settings, $fields );
				$object->notifications = Forminator_Migration::migrate_custom_form_notifications( $object->notifications, $object->settings, $meta );
			}

			// Migrate settings Polls
			if ( 'forminator_polls' === $this->post_type ) {
				$object->settings = Forminator_Migration::migrate_polls_settings( $object->settings );
			}

			// Migrate settings Polls
			if ( 'forminator_quizzes' === $this->post_type ) {
				$object->settings = Forminator_Migration::migrate_quizzes_settings( $object->settings );
			}

			$object->raw = $post;

			return $object;
		}

		return false;
	}

	/**
	 * Return fields as array
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_fields_as_array() {
		$arr = array();

		if ( empty( $this->fields ) ) {
			return $arr;
		}

		foreach ( $this->fields as $field ) {
			$arr[] = $field->to_array();
		}

		return $arr;
	}

	/**
	 * Return fields grouped
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_fields_grouped() {
		$wrappers = array();

		if ( empty( $this->fields ) ) {
			return $wrappers;
		}

		foreach ( $this->fields as $field ) {
			/** @var Forminator_Form_Field_Model $field */
			if ( strpos( $field->form_id, 'wrapper-' ) === 0 ) {
				$form_id = $field->form_id;
			} else {
				// Backward Compat
				$form_id = $field->formID; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar
			}

			if ( ! isset( $wrappers[ $form_id ] ) ) {
				$wrappers[ $form_id ] = array(
					'wrapper_id' => $form_id,
					'fields'     => array(),
				);
			}

			$field_data                       = $field->to_formatted_array();
			$wrappers[ $form_id ]['fields'][] = $field_data;
		}
		$wrappers = array_values( $wrappers );

		return $wrappers;
	}

	/**
	 * Model to array
	 *
	 * @since 1.0
	 * @return array
	 */
	public function to_array() {
		$data = array();
		$maps = array_merge( $this->get_default_maps(), $this->get_maps() );

		if ( empty( $maps ) ) {
			return $data;
		}

		foreach ( $maps as $map ) {
			$property          = $map['property'];
			$data[ $property ] = $this->$property;
		}

		return $data;
	}

	/**
	 * Model to json
	 *
	 * @since 1.0
	 * @return mixed|string
	 */
	public function to_json() {
		$wrappers = array();

		if ( ! empty( $this->fields ) ) {
			foreach ( $this->fields as $field ) {
				$wrappers[] = $field->to_json();
			}
		}

		$settings      = $this->settings;
		$notifications = $this->notifications;
		$data          = array_merge(
			array(
				'wrappers' => array(
					'fields' => $wrappers,
				),
			),
			$settings,
			$notifications
		);
		$ret           = array(
			'formName' => $this->name,
			'data'     => $data,
		);

		return wp_json_encode( $ret );
	}

	/**
	 * In here we will define how we store the properties
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_default_maps() {
		return array(
			array(
				'type'     => 'post',
				'property' => 'id',
				'field'    => 'ID',
			),
			array(
				'type'     => 'post',
				'property' => 'name',
				'field'    => 'post_title',
			),
			array(
				'type'     => 'post',
				'property' => 'status',
				'field'    => 'post_status',
				'default'  => self::STATUS_PUBLISH,
			),
			array(
				'type'     => 'meta',
				'property' => 'fields',
				'field'    => 'fields',
			),
			array(
				'type'     => 'meta',
				'property' => 'settings',
				'field'    => 'settings',
			),
			array(
				'type'     => 'meta',
				'property' => 'client_id',
				'field'    => 'client_id',
			),
			array(
				'type'     => 'meta',
				'property' => 'notifications',
				'field'    => 'notifications',
			),
		);
	}

	/**
	 * This should be get override by children
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_maps() {
		return array();
	}

	/**
	 * Return model
	 *
	 * @since 1.0
	 *
	 * @param string $class_name
	 *
	 * @return self
	 */
	public static function model( $class_name = __CLASS__ ) {
		$class = new $class_name();

		return $class;
	}

	/**
	 * Get Post Type of cpt
	 *
	 * @since 1.0.5
	 *
	 * @return mixed
	 */
	public function get_post_type() {
		return $this->post_type;
	}

	/**
	 * Get export Model
	 *
	 * @since 1.4
	 * Override-able, but use of hook `forminator_{$module_type}_model_to_exportable_data` encouraged
	 * @return array
	 */
	public function to_exportable_data() {

		if ( ! Forminator::is_import_export_feature_enabled() ) {
			return array();
		}

		$model_id = $this->id;

		switch ( $this->post_type ) {
			case 'forminator_quizzes':
				$module_type = 'quiz';
				break;
			case 'forminator_polls':
				$module_type = 'poll';
				break;
			default:
				$module_type = 'form';
				break;
		}

		// cleanup form id
		$post_meta = get_post_meta( $this->id, self::META_KEY, true );
		if ( is_array( $post_meta ) ) {
			if ( isset( $post_meta['settings'] ) && isset( $post_meta['settings'] ) ) {
				if ( isset( $post_meta['settings']['form_id'] ) ) {
					unset( $post_meta['settings']['form_id'] );
				}
				if ( isset( $post_meta['settings']['formID'] ) ) {
					unset( $post_meta['settings']['formID'] );
				}
			}
		}

		$exportable_data = array(
			'type'    => $module_type,
			'data'    => $post_meta,
			'status'  => $this->status,
			'version' => FORMINATOR_VERSION,
		);

		$exportable_data = apply_filters( "forminator_{$module_type}_model_to_exportable_data", $exportable_data, $module_type, $model_id );

		return $exportable_data;
	}

	/**
	 * Create model from import data
	 *
	 * @since 1.4
	 *
	 * @param        $import_data
	 * @param string $module
	 *
	 * @return self|Forminator_Custom_Form_Model|Forminator_Poll_Form_Model|Forminator_Quiz_Form_Model|WP_Error
	 */
	public static function create_from_import_data( $import_data, $module = __CLASS__ ) {
		try {
			if ( ! Forminator::is_import_export_feature_enabled() ) {
				throw new Exception( __( 'Export Import feature disabled', Forminator::DOMAIN ) );
			}

			if ( ! is_callable( array( $module, 'model' ) ) ) {
				throw new Exception( __( 'Model loader for importer does not exist.', Forminator::DOMAIN ) );
			}

			// call static method ::model
			$model = call_user_func( array( $module, 'model' ) );

			/**
			 * Executes before create model from import data
			 *
			 * @since 1.4
			 *
			 * @param array  $import_data
			 * @param string $module
			 */
			do_action( 'forminator_before_create_model_from_import_data', $import_data, $module );

			if ( ! isset( $import_data['type'] ) || empty( $import_data['type'] ) ) {
				throw new Exception( __( 'Invalid format of import data type', Forminator::DOMAIN ) );
			}

			$meta = ( isset( $import_data['data'] ) ? $import_data['data'] : array() );

			if ( empty( $meta ) ) {
				throw new Exception( __( 'Invalid format of import data', Forminator::DOMAIN ) );
			}

			if ( ! isset( $meta['settings'] ) || empty( $meta['settings'] ) ) {
				throw new Exception( __( 'Invalid format of import data settings', Forminator::DOMAIN ) );
			}

			if ( ! isset( $meta['settings']['formName'] ) || empty( $meta['settings']['formName'] ) ) {
				throw new Exception( __( 'Invalid format of import data name', Forminator::DOMAIN ) );
			}

			$form_name = $meta['settings']['formName'];

			$type = $import_data['type'];
			switch ( $type ) {
				case 'quiz':
					$post_type = 'forminator_quizzes';
					break;
				case 'poll':
					$post_type = 'forminator_polls';
					break;
				default:
					$post_type = 'forminator_forms';
					break;
			}

			$post_status = ( isset( $import_data['status'] ) && ! empty( $import_data['status'] ) ) ? $import_data['status'] : self::STATUS_PUBLISH;

			/**
			 * todo : use @see self::save()
			 */
			$post_data = array(
				'post_title'  => $form_name,
				'post_type'   => $post_type,
				'post_status' => $post_status,
			);

			$post_id = wp_insert_post( $post_data, true );

			if ( is_wp_error( $post_id ) ) {
				throw new Exception( $post_id->get_error_message(), $post_id->get_error_code() );
			}

			if ( ! isset( $meta['settings'] ) ) {
				$meta['settings'] = array();
			}
			// update form_id
			$meta['settings']['form_id'] = $post_id;

			update_post_meta( $post_id, self::META_KEY, $meta );

			/** @var Forminator_Base_Form_Model|Forminator_Poll_Form_Model|Forminator_Quiz_Form_Model|Forminator_Custom_Form_Model $model */
			$model = $model->load( $post_id );

			if ( ! $model instanceof $module ) {
				throw new Exception( __( 'Failed to load imported Forminator model', Forminator::DOMAIN ) );
			}


			/**
			 * Action called after module imported
			 *
			 * @since 1.11
			 *
			 * @param int    $post_id - module id
			 * @param string $post_status - module status
			 * @param object $model - module model
			 *
			 */
			do_action( 'forminator_' . $type . '_action_imported', $post_id, $post_status, $model );
		} catch ( Exception $e ) {
			$code = $e->getCode();
			if ( empty( $code ) ) {
				$code = 'forminator_import_model_error';
			}
			$model = new WP_Error( $code, $e->getMessage(), $import_data );
		}

		/**
		 * Filter imported model of form
		 *
		 * @since 1.4
		 *
		 * @param Forminator_Base_Form_Model|WP_Error $model
		 * @param array                               $import_data
		 * @param string                              $module
		 */
		$model = apply_filters( 'forminator_import_model', $model, $import_data, $module );

		return $model;

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
		//default disabled

		// from settings
		$settings_enabled = get_option( 'forminator_module_enable_load_ajax', false );

		// from constant
		$enabled = defined( 'FORMINATOR_MODULE_ENABLE_LOAD_AJAX' ) && FORMINATOR_MODULE_ENABLE_LOAD_AJAX;

		// if one is true, then its enabled
		$enabled = $force || $settings_enabled || $enabled;

		/**
		 * Filter flag is ajax load of module
		 *
		 * @since  1.6
		 *
		 * @param bool $enabled
		 * @param bool $settings_enabled
		 * @param bool $force
		 *
		 * @return bool
		 */
		$enabled = apply_filters( 'forminator_module_is_ajax_load', $enabled, $settings_enabled, $force );

		return $enabled;
	}

	/**
	 * Flag to use `DONOTCACHEPAGE`
	 *
	 * @since 1.6.1
	 * @return bool
	 */
	public function is_use_donotcachepage_constant() {
		//default disabled

		// from settings
		$settings_enabled = get_option( 'forminator_module_use_donotcachepage', false );

		// from constant
		$enabled = defined( 'FORMINATOR_MODULE_USE_DONOTCACHEPAGE' ) && FORMINATOR_MODULE_USE_DONOTCACHEPAGE;

		// if one is true, then its enabled
		$enabled = $settings_enabled || $enabled;

		/**
		 * Filter flag is use `DONOTCACHEPAGE` of module
		 *
		 * @since  1.6
		 *
		 * @param bool $enabled
		 *
		 * @return bool
		 */
		$enabled = apply_filters( 'forminator_module_is_use_donotcachepage_constant', $enabled );

		return $enabled;
	}

	/**
	 * Check if the result enable for share
	 *
	 * @since 1.7
	 * @return bool
	 */
	public function is_entry_share_enabled() {
		$module_id   = (int) $this->id;
		$module_type = $this->post_type;

		// from settings
		$settings_enabled = get_option( 'forminator_module_enable_share_entry', false );

		// from constant
		$enabled = defined( 'FORMINATOR_MODULE_ENABLE_SHARE_ENTRY' ) && FORMINATOR_MODULE_ENABLE_SHARE_ENTRY;

		// if one is true, then its enabled
		$enabled = $settings_enabled || $enabled;

		/**
		 * Filter flag is use `ENABLE_SHARE_ENTRY` of module
		 *
		 * @since  1.7
		 *
		 * @param bool   $enabled
		 * @param int    $module_id
		 * @param string $module_type
		 *
		 * @return bool
		 */
		$enabled = apply_filters( 'forminator_module_is_entry_share_enabled', $enabled, $module_id, $module_type );

		return $enabled;
	}
}
