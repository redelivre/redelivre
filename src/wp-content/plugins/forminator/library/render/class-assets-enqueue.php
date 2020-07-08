<?php
/**
 * Class Forminator_Assets_Enqueue
 *
 * @since 1.11
 */
abstract class Forminator_Assets_Enqueue {
	/**
	 * Model data
	 *
	 * @var Forminator_Base_Form_Model
	 */
	public $model = null;

	/**
	 * Is form loaded with AJAX
	 *
	 * @var bool
	 */
	public $is_ajax_load = false;

	/**
	 * Forminator_Render_Form constructor.
	 *
	 * @since 1.11
	 */
	public function __construct( $model, $is_ajax_load ) {
		$this->model = $model;
		$this->is_ajax_load = $is_ajax_load;
	}

	/**
	 * Return Form Design
	 *
	 * @since 1.0
	 * @return mixed|string
	 */
	public function get_module_design() {
		$form_settings = $this->get_settings();

		if ( ! isset( $form_settings['form-style'] ) ) {
			return 'default';
		}

		return $form_settings['form-style'];
	}

	/**
	 * Return Form Settins
	 *
	 * @since 1.11
	 * @return mixed
	 */
	public function get_settings() {
		return $this->model->settings;
	}

	/**
	 * Return form wrappers & fields
	 *
	 * @since 1.11
	 * @return array|mixed
	 */
	public function get_wrappers() {
		if ( is_object( $this->model ) ) {
			return $this->model->get_fields_grouped();
		} else {
			return $this->message_not_found();
		}
	}

	/**
	 * Return form wrappers & fields
	 *
	 * @since 1.11
	 * @return array|mixed
	 */
	public function get_fields() {
		$fields   = array();
		$wrappers = $this->get_wrappers();

		// Fallback
		if ( empty( $wrappers ) ) {
			return $fields;
		}

		foreach ( $wrappers as $key => $wrapper ) {
			if ( ! isset( $wrapper['fields'] ) ) {
				return array();
			}

			foreach ( $wrapper['fields'] as $k => $field ) {
				$fields[] = $field;
			}
		}

		return $fields;
	}

	/**
	 * Enqueue module styles
	 *
	 * @since 1.11
	 */
	public function enqueue_styles() {}

	/**
	 * Enqueue module scripts
	 *
	 * @since 1.11
	 */
	public function enqueue_scripts() {}

	/**
	 * Check if form given field type
	 *
	 * @since 1.11
	 * @return bool
	 */
	public function has_field_type( $type ) {
		$fields = $this->get_fields();

		if ( ! empty( $fields ) ) {
			foreach ( $fields as $field ) {
				if ( $type === $field["type"] ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Check if field with type exist on a form, and check if its setting match
	 *
	 * @since 1.11
	 *
	 * @param             $field_type
	 * @param string|null $setting_name
	 * @param string|null $setting_value
	 *
	 * @return bool
	 */
	public function has_field_type_with_setting_value( $field_type, $setting_name = null, $setting_value = null ) {
		$fields = $this->get_fields();

		if ( ! empty( $fields ) ) {
			foreach ( $fields as $field ) {
				if ( $field_type === $field["type"] ) {
					if ( is_null( $setting_name ) ) {
						return true;
					} elseif ( isset( $field[ $setting_name ] ) ) {
						$field_settings_value = $field[ $setting_name ];

						if ( is_bool( $setting_value ) ) {
							// cast to bool
							$field_settings_value = filter_var( $field[ $setting_name ], FILTER_VALIDATE_BOOLEAN );
						}

						if ( $field_settings_value === $setting_value ) {
							return true;
						}
					}
				}
			}
		}

		return false;
	}
}