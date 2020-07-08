<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Upload
 *
 * @since 1.0
 */
class Forminator_Upload extends Forminator_Field {

	/**
	 * @var string
	 */
	public $name = '';

	/**
	 * @var string
	 */
	public $slug = 'upload';

	/**
	 * @var string
	 */
	public $type = 'upload';

	/**
	 * @var int
	 */
	public $position = 14;

	/**
	 * @var array
	 */
	public $options = array();

	/**
	 * @var string
	 */
	public $category = 'standard';

	/**
	 * @var string
	 */
	public $icon = 'sui-icon-download';

	/**
	 * Forminator_Upload constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		parent::__construct();

		$this->name = __( 'File Upload', Forminator::DOMAIN );
	}

	/**
	 * Field defaults
	 *
	 * @since 1.0
	 * @return array
	 */
	public function defaults() {
		$mimes = get_allowed_mime_types();

		return array(
			'field_label'  => __( 'Upload file', Forminator::DOMAIN ),
			'filetypes'    => array_keys( $mimes ),
			'upload-limit' => 8,
			'filesize'     => 'MB',
		);
	}

	/**
	 * Autofill Setting
	 *
	 * @since 1.0.5
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	public function autofill_settings( $settings = array() ) {
		//Unsupported Autofill
		$autofill_settings = array();

		return $autofill_settings;
	}

	/**
	 * Field front-end markup
	 *
	 * @since 1.0
	 *
	 * @param $field
	 * @param $settings
	 *
	 * @return mixed
	 */
	public function markup( $field, $settings = array() ) {

		$this->field = $field;

		$html        = '';
		$id          = self::get_property( 'element_id', $field );
		$name        = $id;
		$required    = self::get_property( 'required', $field, false );
		$design      = $this->get_form_style( $settings );
		$label       = esc_html( self::get_property( 'field_label', $field, '' ) );
		$description = esc_html( self::get_property( 'description', $field, '' ) );

		$html .= '<div class="forminator-field">';

		if ( $label ) {

			if ( $required ) {

				$html .= sprintf(
					'<label for="%s" class="forminator-label">%s %s</label>',
					'forminator-field-' . $id,
					$label,
					forminator_get_required_icon()
				);
			} else {

				$html .= sprintf(
					'<label for="%s" class="forminator-label">%s</label>',
					'forminator-field-' . $id,
					$label
				);
			}
		}

			$html .= self::create_file_upload(
				$id,
				$name,
				$description,
				$required,
				$design
			);

			$html .= self::get_description( $description, 'forminator-field-' . $id );

		$html .= '</div>';

		return apply_filters( 'forminator_field_file_markup', $html, $field );
	}

	/**
	 * Field back-end validation
	 *
	 * @since 1.0
	 *
	 * @param array        $field
	 * @param array|string $data
	 */
	public function validate( $field, $data ) {
		if ( $this->is_required( $field ) ) {
			$id               = self::get_property( 'element_id', $field );
			$required_message = self::get_property( 'required_message', $field, '' );
			if ( empty( $data ) ) {
				$this->validation_message[ $id ] = apply_filters(
					'forminator_upload_field_required_validation_message',
					( ! empty( $required_message ) ? $required_message : __( 'This field is required. Please upload a file', Forminator::DOMAIN ) ),
					$id,
					$field
				);
			}
		}
	}

	/**
	 * Return field inline validation rules
	 * Workaround for actually input file is hidden, so its not accessible via standar html5 `required` attribute
	 *
	 * @since 1.1
	 * @return string
	 */
	public function get_validation_rules() {
		$field       = $this->field;
		$id          = self::get_property( 'element_id', $field );
		$is_required = $this->is_required( $field );
		$rules       = '';

		if ( $is_required ) {
			$rules = '"' . $this->get_id( $field ) . '": {';
			if ( $is_required ) {
				$rules .= '"required": true,';
			}
			$rules .= '},';
		}

		return apply_filters( 'forminator_field_file_validation_rules', $rules, $id, $field );
	}

	/**
	 * Return field inline validation messages
	 *
	 * @since 1.1
	 * @return string
	 */
	public function get_validation_messages() {
		$field       = $this->field;
		$id          = $this->get_id( $field );
		$is_required = $this->is_required( $field );
		$messages    = '"' . $id . '": {' . "\n";

		if ( $is_required ) {
			$settings_required_message = self::get_property( 'required_message', $field, '' );
			$required_message          = apply_filters(
				'forminator_upload_field_required_validation_message',
				( ! empty( $settings_required_message ) ? $settings_required_message : __( 'This field is required. Please upload a file', Forminator::DOMAIN ) ),
				$id,
				$field
			);
			$messages                  = $messages . '"required": "' . forminator_addcslashes( $required_message ) . '",' . "\n";
		}
		$messages .= '},' . "\n";

		return $messages;
	}

	/**
	 * Handle file uplload
	 *
	 * @since 1.6 copied from Forminator_Front_Action
	 *
	 * @param array field settings
	 *
	 * @return bool|array
	 */
	public function handle_file_upload( $field ) {
		$this->field       = $field;
		$id                = self::get_property( 'element_id', $field );
		$field_name        = $id;
		$custom_limit_size = true;
		$upload_limit      = self::get_property( 'upload-limit', $field, self::FIELD_PROPERTY_VALUE_NOT_EXIST );
		$filesize          = self::get_property( 'filesize', $field, 'MB' );
		$custom_file_type  = self::get_property( 'custom-files', $field, false );
		$use_library       = self::get_property( 'use_library', $field, false );
		$use_library       = filter_var( $use_library, FILTER_VALIDATE_BOOLEAN );
		$mime_types        = array();

		if ( self::FIELD_PROPERTY_VALUE_NOT_EXIST === $upload_limit || empty( $upload_limit ) ) {
			$custom_limit_size = false;
		}

		$custom_file_type = filter_var( $custom_file_type, FILTER_VALIDATE_BOOLEAN );
		if ( $custom_file_type ) {
			// check custom mime
			$filetypes = self::get_property( 'filetypes', $field, array(), 'array' );
			foreach ( $filetypes as $filetype ) {
				// Mime type format = Key is the file extension with value as the mime type.
				$mime_types[ $filetype ] = $filetype;
			}
		}

		if ( isset( $_FILES[ $field_name ] ) ) {
			if ( isset( $_FILES[ $field_name ]['name'] ) && ! empty( $_FILES[ $field_name ]['name'] ) ) {
				$file_name = sanitize_file_name( $_FILES[ $field_name ]['name'] );

				/**
				 * Filter mime types to be used as validation
				 *
				 * @since 1.6
				 *
				 * @param array $mime_types return null/empty array to use default WP file types @see https://codex.wordpress.org/Plugin_API/Filter_Reference/upload_mimes
				 * @param array $field
				 */
				$mime_types = apply_filters( 'forminator_upload_field_mime_types', $mime_types, $field );
				$valid      = wp_check_filetype( $file_name, $mime_types );

				if ( false === $valid['ext'] ) {
					return array(
						'success' => false,
						'message' => __( 'Error saving form. Uploaded file extension is not allowed.', Forminator::DOMAIN ),
					);
				}

				$allow = apply_filters( 'forminator_file_upload_allow', true, $field_name, $file_name, $valid );
				if ( false === $allow ) {
					return array(
						'success' => false,
						'message' => __( 'Error saving form. Uploaded file extension is not allowed.', Forminator::DOMAIN ),
					);
				}

				require_once ABSPATH . 'wp-admin/includes/file.php';
				WP_Filesystem();
				/** @var WP_Filesystem_Base $wp_filesystem */
				global $wp_filesystem;
				if ( ! is_uploaded_file( $_FILES[ $field_name ]['tmp_name'] ) ) {
					return array(
						'success' => false,
						'message' => __( 'Error saving form. Failed to read uploaded file.', Forminator::DOMAIN ),
					);
				}

				$upload_dir       = wp_upload_dir(); // Set upload folder
				$unique_file_name = wp_unique_filename( $upload_dir['path'], $file_name );
				$exploded_name    = explode( '/', $unique_file_name );
				$filename         = end( $exploded_name ); // Create base file name

				$max_size = wp_max_upload_size();
				$file_size = $this->file_size( $filesize );
				if ( $custom_limit_size ) {
					$max_size = $upload_limit * $file_size; // convert to byte
				}

				if ( 0 === $_FILES[ $field_name ]['size'] ) {
					return array(
						'success' => false,
						'message' => __( 'The attached file is empty and can\'t be uploaded.', Forminator::DOMAIN ),
					);
				}

				if ( $_FILES[ $field_name ]['size'] > $max_size ) {

					$rounded_max_size = round( $max_size / 1000000 );

					if ( $rounded_max_size <= 0 ) {
						// go to KB
						$rounded_max_size = round( $max_size / 1000 );

						if ( $rounded_max_size <= 0 ) {
							// go to B
							$rounded_max_size = round( $max_size ) . ' B';
						} else {
							$rounded_max_size .= ' KB';
						}
					} else {
						$rounded_max_size .= ' MB';
					}

					return array(
						'success' => false,
						'message' => sprintf( /* translators: ... */ __( 'Error saving form. Uploaded file size exceeds %1$s upload limit. ', Forminator::DOMAIN ), $rounded_max_size ),
					);
				}

				if ( UPLOAD_ERR_OK !== $_FILES[ $field_name ]['error'] ) {
					return array(
						'success' => false,
						'message' => __( 'Error saving form. Upload error. ', Forminator::DOMAIN ),
					);
				}

				if ( ! $wp_filesystem->is_dir( $upload_dir['path'] ) ) {
					$wp_filesystem->mkdir( $upload_dir['path'] );
				}

				if ( $wp_filesystem->is_writable( $upload_dir['path'] ) ) {
					$file_path = $upload_dir['path'] . '/' . $filename;
					$file_url  = $upload_dir['url'] . '/' . $filename;
				} else {
					$file_path = $upload_dir['basedir'] . '/' . $filename;
					$file_url  = $upload_dir['baseurl'] . '/' . $filename;
				}

				$file_mime = mime_content_type( $_FILES[ $field_name ]['tmp_name'] );

				// use move_uploaded_file instead of $wp_filesystem->put_contents
				// increase performance, and avoid permission issues
				if ( false !== move_uploaded_file( $_FILES[ $field_name ]['tmp_name'], $file_path ) ) {
					if ( $use_library ) {
						$upload_id = wp_insert_attachment(
							array(
								'guid'           => $file_path,
								'post_mime_type' => $file_mime,
								'post_title'     => preg_replace( '/\.[^.]+$/', '', $filename ),
								'post_content'   => '',
								'post_status'    => 'inherit',
							),
							$file_path
						);

						// wp_generate_attachment_metadata() won't work if you do not include this file
						require_once ABSPATH . 'wp-admin/includes/image.php';

						// Generate and save the attachment metas into the database
						wp_update_attachment_metadata( $upload_id, wp_generate_attachment_metadata( $upload_id, $file_path ) );
					}

					return array(
						'success'   => true,
						'file_url'  => $file_url,
						'file_path' => $file_path,
					);
				} else {
					return array(
						'success' => false,
						'message' => __( 'Error saving form. Upload error. ', Forminator::DOMAIN ),
					);
				}
			}
		}

		return false;
	}

	/**
	 * File size
	 *
	 * @param $file_size
	 *
	 * @return mixed
	 */
	public function file_size( $file_size ) {

		switch ( $file_size ) {
			case 'KB' :
				$size = 1000;
				break;
			case 'B' :
				$size = 1;
				break;
			default:
				$size = 1000000;
				break;
		}

		return $size;
	}

}
