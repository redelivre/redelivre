<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Migration
 */
class Forminator_Migration {

	/**
	 * Static method to combine all settings migrations
	 *
	 * @param $settings
	 * @param $fields
	 *
	 * @since 1.6
	 *
	 * @return mixed
	 */
	public static function migrate_custom_form_settings( $settings, $fields ) {
		$version = self::get_version( $settings );

		if ( ! is_array( $settings ) ) {
			return $settings;
		}

		// skip alpha/beta ... from migration
		if ( version_compare( $version, '1.6-alpha.1', 'lt' ) ) {
			/**
			 * Migrate pagination settings
			 *
			 * @since 1.6
			 */
			$settings = self::migrate_pagination_settings_1_6( $settings );

			/**
			 * Migrate submit data
			 *
			 * @since 1.6
			 */
			$settings = self::migrate_submit_props_1_6( $settings );

			/**
			 * Migrate padding & border settings
			 *
			 * @since 1.6
			 */
			$settings = self::migrate_padding_border_settings_1_6( $settings );

			/**
			 * Migrate custom font settings
			 *
			 * @since 1.6
			 */
			$settings = self::migrate_custom_fonts_1_6( $settings );
		}

		if ( version_compare( $version, '1.6.1.alpha-1', 'lt' ) ) {
			/**
			 * Migrate padding & border settings
			 *
			 * @since 1.6
			 */
			$settings = self::migrate_padding_border_settings_1_6_1( $settings );
		}

		/**
		 * Migrate Page break settings
		 *
		 * @since 1.7.4
		 */
		$settings = self::migrate_pagination_form_settings( $settings, $fields );

		return $settings;
	}

	/**
	 * Static method to combine all field migrations
	 *
	 * @param        $field
	 * @param string $settings
	 *
	 * @since 1.6
	 *
	 * @return mixed
	 */
	public static function migrate_field( $field, $settings ) {
		if ( isset( $settings['version'] ) ) {
			$version = $settings['version'];
		} else {
			$version = FORMINATOR_VERSION;
		}

		// Fallback if field type is undefined
		if ( ! isset( $field['type'] ) ) {
			return $field;
		}

		// skip alpha/beta ... from migration
		if ( version_compare( $version, '1.6-alpha.1', 'lt' ) ) {

			/**
			 * Migrate to new field types
			 *
			 * @since 1.6
			 */
			$field = self::migrate_field_types_1_6( $field );

			/**
			 * Migrate invisible captcha
			 *
			 * @since 1.6
			 */
			$field = self::migrate_invisible_captcha_1_6( $field );

			/**
			 * Migrate email validation
			 *
			 * @since 1.6
			 */
			$field = self::migrate_email_validation_1_6( $field );

			/**
			 * Migrate name field required
			 *
			 * @since 1.6
			 */
			$field = self::migrate_multiple_required_1_6( $field );

			/**
			 * Migrate section border
			 *
			 * @since 1.6
			 */
			$field = self::migrate_section_border_1_6( $field, $settings );

			/**
			 * Migrate phone field validation
			 *
			 * @since 1.6
			 */
			$field = self::migrate_phone_validation_1_6( $field );

			/**
			 * Migrate conditions
			 *
			 * @since 1.6
			 */
			$field = self::migrate_field_conditions_1_6( $field );

			/**
			 * Migrate text_limit on `text` field
			 *
			 * @since 1.6
			 */
			$field = self::migrate_text_limit_1_6( $field );

		}

		if ( version_compare( $version, '1.7.alpha-1', 'lt' ) ) {
			return $field;
		}

		/**
		 * Migrate Page_break field border
		 *
		 * @since 1.7.4
		 */
		$field = self::migrate_page_break_pagination_field( $field );



		return $field;
	}

	/**
	 * Static method to combine all settings migrations
	 *
	 * @param $settings
	 *
	 * @since 1.6.1
	 *
	 * @return mixed
	 */
	public static function migrate_polls_settings( $settings ) {
		$version = self::get_version( $settings );

		if ( ! is_array( $settings ) ) {
			return $settings;
		}

		if ( version_compare( $version, '1.6.1.alpha-1', 'lt' ) ) {
			/**
			 * Migrate colors settings
			 *
			 * @since 1.6.1
			 */
			$settings = self::migrate_colors_settings_1_6_1( $settings );

			/**
			 * Migrate padding & border settings
			 *
			 * @since 1.6.1
			 */
			$settings = self::migrate_padding_border_settings_1_6_1( $settings );
		}


		return $settings;
	}

	/**
	 * Static method to combine all settings migrations
	 *
	 * @param $settings
	 *
	 * @since 1.6.1
	 *
	 * @return mixed
	 */
	public static function migrate_quizzes_settings( $settings ) {
		$version = self::get_version( $settings );

		if ( ! is_array( $settings ) ) {
			return $settings;
		}

		if ( version_compare( $version, '1.6.2.alpha-1', 'lt' ) ) {
			/**
			 * Migrate colors settings
			 *
			 * @since 1.6.2
			 */
			$settings = self::migrate_share_settings_1_6_2( $settings );
		}


		return $settings;
	}

	/**
	 * Migrate new field types from 1.6 version
	 *
	 * @param $field
	 *
	 * @since 1.6
	 *
	 * @return mixed
	 */
	public static function migrate_field_types_1_6( $field ) {
		// Migrate text to textarea
		if ( "text" === $field['type'] && isset( $field['input_type'] ) && "paragraph" === $field['input_type'] ) {
			$field['type'] = "textarea";
		}

		// Migrate text to textarea
		if ( "select" === $field['type'] && isset( $field['value_type'] ) && "radio" === $field['value_type'] ) {
			$field['type'] = "radio";
		}

		// Migrate multi select to select
		if ( "checkbox" === $field['type'] && ( isset( $field['value_type'] ) && "multiselect" === $field['value_type'] ) ) {
			$field['type'] = "select";
		}

		return $field;
	}

	/**
	 * Migrate invisible captcha from 1.6 version
	 *
	 * @param $field
	 *
	 * @since 1.6
	 *
	 * @return mixed
	 */
	public static function migrate_invisible_captcha_1_6( $field ) {

		if ( "captcha" === $field['type'] ) {
			$is_invisible_captcha = isset( $field['invisible_captcha'] ) ? filter_var( $field['invisible_captcha'], FILTER_VALIDATE_BOOLEAN ) : false;
			if ( $is_invisible_captcha ) {
				$field['captcha_type'] = "invisible";
			}
			unset( $field['invisible_captcha'] );
		}

		return $field;
	}

	/**
	 * Migrate email validation
	 *
	 * @param $field
	 *
	 * @since 1.6
	 *
	 * @return mixed
	 */
	public static function migrate_email_validation_1_6( $field ) {

		// Migrate email validation message
		if ( "email" === $field['type'] ) {
			$validation_text = isset( $field['validation_text'] ) ? $field['validation_text'] : '';
			if ( ! empty( $validation_text ) ) {
				$field['validation_message'] = $validation_text;
			}
			unset( $field['validation_text'] );
		}

		return $field;
	}

	/**
	 * Migration section border settings
	 *
	 * @param $field
	 * @param $settings
	 *
	 * @since 1.6
	 *
	 * @return mixed
	 */
	public static function migrate_section_border_1_6( $field, $settings ) {
		if ( "section" === $field['type'] ) {
			$has_border = $field['section_border'];
			$has_border = filter_var( $has_border, FILTER_VALIDATE_BOOLEAN );

			// Check if we need migration
			if ( $has_border && ! isset( $field['border_width'] ) && ! isset( $field['border_color'] ) ) {
				$field['section_border'] = 'solid';
				$field['border_width']   = '1';
				$field['border_color']   = $settings['cform-section-border-color'];
			}
		}

		return $field;
	}

	/**
	 * Migrate phone field validation
	 *
	 * @param $field
	 *
	 * @since 1.6
	 *
	 * @return mixed
	 */
	public static function migrate_phone_validation_1_6( $field ) {
		if ( isset( $field['validation'] ) ) {
			if ( true === $field['validation'] ) {
				$field['validation'] = "true";
			} else {
				$field['validation'] = "false";
			}
		}

		return $field;
	}

	/**
	 * Migrate multiple fields required
	 *
	 * @param $field
	 *
	 * @since 1.6
	 *
	 * @return mixed
	 */
	public static function migrate_multiple_required_1_6( $field ) {

		// migrate name required to multi
		if ( "name" === $field['type'] ) {
			$is_multi        = isset( $field['multiple_name'] ) ? filter_var( $field['multiple_name'], FILTER_VALIDATE_BOOLEAN ) : false;
			$is_old_required = isset( $field['required'] ) ? filter_var( $field['required'], FILTER_VALIDATE_BOOLEAN ) : false;
			if ( $is_multi && $is_old_required ) {
				$field['prefix_required'] = true;
				$field['fname_required']  = true;
				$field['mname_required']  = true;
				$field['lname_required']  = true;
				unset( $field['required'] );
			}
		}

		// migrate address required to multi
		if ( "address" === $field['type'] ) {
			$is_old_required = isset( $field['required'] ) ? filter_var( $field['required'], FILTER_VALIDATE_BOOLEAN ) : false;
			if ( $is_old_required ) {
				$field['street_address_required']  = true;
				$field['address_line_required']    = true;
				$field['address_city_required']    = true;
				$field['address_state_required']   = true;
				$field['address_zip_required']     = true;
				$field['address_country_required'] = true;
				unset( $field['required'] );
			}
		}

		return $field;
	}

	/**
	 * Migrate new pagination settings
	 *
	 * @param $settings
	 *
	 * @since 1.6
	 *
	 * @return mixed
	 */
	public static function migrate_pagination_settings_1_6( $settings ) {

		if ( isset( $settings['pagination-header-design'] ) ) {
			if ( "bar" === $settings['pagination-header-design'] || "nav" === $settings['pagination-header-design'] ) {
				$settings['pagination-header']        = $settings['pagination-header-design'];
				$settings['pagination-header-design'] = 'show';
			}
		} else {
			$settings['pagination-header-design'] = 'off';
		}

		if ( isset( $settings['pagination-footer-button'] ) || isset( $settings['pagination-right-button'] ) ) {
			$settings['pagination-labels'] = 'custom';
		}

		return $settings;
	}

	/**
	 * Migrate new padding settings
	 *
	 * @param $settings
	 *
	 * @since 1.6
	 *
	 * @return mixed
	 */
	public static function migrate_padding_border_settings_1_6( $settings ) {
		if ( ! isset( $settings['form-padding'] ) ) {
			$settings['form-padding'] = 'custom';
		}

		if ( ! isset( $settings['form-border'] ) ) {
			$settings['form-border'] = 'custom';
		}

		return $settings;
	}

	/**
 * Migrate new padding settings
 *
 * @param $settings
 *
 * @since 1.6.1
 *
 * @return mixed
 */
	public static function migrate_padding_border_settings_1_6_1( $settings ) {
		if ( ! isset( $settings['poll-padding'] ) ) {
			$settings['poll-padding'] = 'custom';
		}

		if ( ! isset( $settings['poll-border'] ) ) {
			$settings['poll-border'] = 'custom';
		}

		return $settings;
	}

	/**
	 * Migrate new padding settings
	 *
	 * @param $settings
	 *
	 * @since 1.6.1
	 *
	 * @return mixed
	 */
	public static function migrate_colors_settings_1_6_1( $settings ) {
		if ( ! isset( $settings['poll-colors'] ) ) {
			$settings['poll-colors'] = 'true';
		}

		return $settings;
	}

	/**
	 * Migrate custom font settings
	 *
	 * @param $settings
	 *
	 * @since 1.6
	 *
	 * @return mixed
	 */
	public static function migrate_custom_fonts_1_6( $settings ) {
		if ( ! isset( $settings['form-font-family'] ) ) {
			$needs_migration = false;

			// List of old font toggles
			$old_settings = array(
				'cform-label-font-settings',
				'cform-input-font-settings',
				'cform-radio-font-settings',
				'cform-select-font-settings',
				'cform-dropdown-font-settings',
				'cform-button-font-settings',
				'cform-timeline-font-settings',
				'cform-pagination-font-settings',
			);

			// Check if we have enabled custom font settings
			foreach ( $old_settings as $prop ) {
				if ( isset( $settings[ $prop ] ) && $settings[ $prop ] ) {
					$needs_migration = true;
				}
			}

			// We need to migrate
			if ( $needs_migration ) {
				// Update new property
				$settings['form-font-family'] = "custom";

				// Unset all old properties
				foreach ( $old_settings as $prop ) {
					unset( $settings[ $prop ] );
				}
			}
		}

		return $settings;
	}

	/**
	 * Migrate submit data
	 *
	 * @param $settings
	 *
	 * @since 1.6
	 *
	 * @return mixed
	 */
	public static function migrate_submit_props_1_6( $settings ) {
		if ( ! isset( $settings['submitData'] ) ) {
			$settings['submitData'] = array();
		}

		if ( isset( $settings['use-custom-submit'] ) && $settings['use-custom-submit'] && isset( $settings['custom-submit-text'] ) ) {
			$settings['submitData']['custom-submit-text'] = $settings['custom-submit-text'];
		}
		unset( $settings['use-custom-submit'] );
		unset( $settings['custom-submit-text'] );

		if ( isset( $settings['use-custom-invalid-form'] ) && $settings['use-custom-invalid-form'] && isset( $settings['custom-invalid-form-message'] ) ) {
			$settings['submitData']['custom-invalid-form-message'] = $settings['custom-invalid-form-message'];
		}
		unset( $settings['use-custom-invalid-form'] );
		unset( $settings['custom-invalid-form-message'] );

		return $settings;
	}

	/**
	 * Check if migration is needed
	 *
	 * @param $version
	 *
	 * @since 1.6
	 *
	 * @return bool
	 */
	public static function skip_migration( $version ) {
		if ( FORMINATOR_VERSION === $version ) {
			return true;
		}

		return false;
	}

	/**
	 * Get form version
	 *
	 * @param $settings
	 *
	 * @since 1.6
	 *
	 * @return string
	 */
	public static function get_version( $settings ) {
		if ( isset( $settings['version'] ) && ! empty( $settings['version'] ) ) {
			return $settings['version'];
		}

		return '1.0';
	}

	/**
	 * Migrate conditions struct
	 *
	 * @since 1.6
	 *
	 * @param $field
	 *
	 * @return mixed
	 */
	public static function migrate_field_conditions_1_6( $field ) {
		if ( isset( $field['use_conditions'] ) ) {
			$use_condition = filter_var( $field['use_conditions'], FILTER_VALIDATE_BOOLEAN );
			if ( ! $use_condition ) {
				unset( $field['conditions'] );
			}
			unset( $field['use_conditions'] );
		}

		return $field;
	}

	/**
	 * Migrate `text_limit` to `limit`
	 *
	 * @param $field
	 *
	 * @since 1.6
	 *
	 * @return mixed
	 */
	public static function migrate_text_limit_1_6( $field ) {
		// Migrate text_limit to limit
		// text area added here because its executed after types migrated
		if ( 'text' === $field['type'] || 'textarea' === $field['type'] ) {
			if ( isset( $field['text_limit'] ) ) {
				$has_limit = filter_var( $field['text_limit'], FILTER_VALIDATE_BOOLEAN );
				if ( ! $has_limit ) {
					unset( $field['limit'] );
				}
				unset( $field['text_limit'] );
			}

		}

		return $field;
	}

	/**
	 * Migrate share settings
	 * 
	 * @param $settings
	 * 
	 * @since 1.6.2
	 * 
	 * @return mixed
	 */
	public static function migrate_share_settings_1_6_2( $settings ) {
		if ( isset( $settings['facebook'] ) || isset( $settings['twitter'] ) || isset( $settings['google'] ) || isset( $settings['linkedin'] ) ) {
			$settings['enable-share'] = 'on';
		}

		return $settings;
	}

	/**
	 * Migrate Pagination settings
	 *
	 * @param $field
	 *
	 * @since 1.7.4
	 *
	 * @return mixed
	 */
	public static function migrate_page_break_pagination_field( $field ) {

		// Migrate page break
		if ( 'pagination' === $field[ 'type' ] ) {
			$field[ 'type' ] = 'page-break';
			$element_id = $field[ 'element_id' ];
			$element_num = explode( '-', $element_id );
			if( isset( $element_num[ 1 ] ) ) {
				$element_id = $field[ 'type' ] . '-' . $element_num[ 1 ];
			}

			$field[ 'element_id' ] = $element_id;
		}

		return $field;
	}

	/**
	 *  pagination settings migrations
	 *
	 * @param $fields
	 * @param $settings
	 *
	 * @since 1.7.4
	 *
	 * @return mixed
	 */
	public static function migrate_pagination_form_settings( $settings, $fields ) {

		if ( empty( $settings ) || ! is_array( $settings ) ) {
			return $settings;
		}

		if ( empty( $fields ) ) {
			return $settings;
		}

		foreach ( $fields as $field ) {
			if ( isset( $field['type'] ) && 'pagination' === $field['type'] ) {
				$element_id    = $field['element_id'];
				$element_num   = explode( '-', $element_id );
				if ( isset( $element_num[1] ) ) {
					$element_id =  'page-break-' . $element_num[1];
				}
				if ( isset( $field['pagination-label'] ) ) {
					$settings['paginationData'][ $element_id . '-steps' ] = $field['pagination-label'];
				}

				if ( isset( $field['pagination-labels'] ) && 'custom' === $field['pagination-labels'] ) {
					$settings['paginationData']['pagination-labels'] = $field['pagination-labels'];
				}

				if ( isset( $field['pagination-footer-button-text'] ) ) {
					$settings['paginationData'][ $element_id . '-previous' ] = $field['pagination-footer-button-text'];
				}

				if ( ! isset( $settings['paginationData'][ $element_id . '-next' ] ) && isset( $field['pagination-right-button-text'] ) ) {
					$settings['paginationData'][ $element_id . '-next' ] = $field['pagination-right-button-text'];
				}
			}
		}

		if ( ! isset( $settings['paginationData']['pagination-header-design']  ) && isset( $settings['pagination-header-design'] ) ) {
			$settings['paginationData']['pagination-header-design'] = $settings['pagination-header-design'];
		}

		if ( ! isset( $settings['paginationData']['pagination-header'] ) && isset( $settings['pagination-header'] ) ) {
			$settings['paginationData']['pagination-header'] = $settings['pagination-header'];
		}

		return $settings;
	}

	/**
	 * Static method to combine all notification migrations
	 *
	 * @param $notifications
	 * @param $settings
	 * @param $forms
	 *
	 * @since 1.6
	 *
	 * @return mixed
	 */
	public static function migrate_custom_form_notifications( $notifications, $settings, $forms ) {
		if ( ! isset( $forms['notifications'] ) ) {
			if ( isset( $settings['use-admin-email'] ) && ! empty( $settings['use-admin-email'] ) ) {
				$admin_args = array(
					'slug'             => 'notification-1111-2222',
					'label'            => 'Admin Email',
					'email-recipients' => 'default',
				);
				if ( ! empty( $settings['admin-email-recipients'] ) ) {
					$admin_args['recipients'] = implode( ',', $settings['admin-email-recipients'] );
				}
				if ( ! empty( $settings['admin-email-cc-address'] ) ) {
					$admin_args['cc-email'] = implode( ',', $settings['admin-email-cc-address'] );
				}
				if ( ! empty( $settings['admin-email-bcc-address'] ) ) {
					$admin_args['bcc-email'] = implode( ',', $settings['admin-email-bcc-address'] );
				}
				if ( ! empty( $settings['admin-email-from-name'] ) ) {
					$admin_args['from-name'] = $settings['admin-email-from-name'];
				}
				if ( ! empty( $settings['admin-email-from-address'] ) ) {
					$admin_args['form-email'] = $settings['admin-email-from-address'];
				}
				if ( ! empty( $settings['admin-email-reply-to-address'] ) ) {
					$admin_args['replyto-email'] = $settings['admin-email-reply-to-address'];
				}
				if ( ! empty( $settings['admin-email-title'] ) ) {
					$admin_args['email-subject'] = $settings['admin-email-title'];
				}
				if ( ! empty( $settings['admin-email-editor'] ) ) {
					$admin_args['email-editor'] = nl2br( $settings['admin-email-editor'] );
				}
				$notifications[] = $admin_args;
			}
			if ( isset( $settings['use-user-email'] ) && ! empty( $settings['use-user-email'] ) ) {
				$user_args = array(
					'slug'             => 'notification-3333-4444',
					'label'            => 'Confirmation Email',
					'email-recipients' => 'default',
				);
				if ( ! empty( $settings['user-email-recipients'] ) ) {
					$user_args['recipients'] = implode( ',', $settings['user-email-recipients'] );
				}
				if ( ! empty( $settings['user-email-cc-address'] ) ) {
					$user_args['cc-email'] = implode( ',', $settings['user-email-cc-address'] );
				}
				if ( ! empty( $settings['user-email-bcc-address'] ) ) {
					$user_args['bcc-email'] = implode( ',', $settings['user-email-bcc-address'] );
				}
				if ( ! empty( $settings['user-email-from-name'] ) ) {
					$user_args['from-name'] = $settings['user-email-from-name'];
				}
				if ( ! empty( $settings['user-email-from-address'] ) ) {
					$user_args['form-email'] = $settings['user-email-from-address'];
				}
				if ( ! empty( $settings['user-email-reply-to-address'] ) ) {
					$user_args['replyto-email'] = $settings['user-email-reply-to-address'];
				}
				if ( ! empty( $settings['user-email-title'] ) ) {
					$user_args['email-subject'] = $settings['user-email-title'];
				}
				if ( ! empty( $settings['user-email-editor'] ) ) {
					$user_args['email-editor'] = nl2br( $settings['user-email-editor'] );
				}
				$notifications[] = $user_args;
			}
		}

		return $notifications;
	}
}
