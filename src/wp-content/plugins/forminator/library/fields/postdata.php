<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Postdata
 *
 * @since 1.0
 */
class Forminator_Postdata extends Forminator_Field {

	/**
	 * @var string
	 */
	public $name = '';

	/**
	 * @var string
	 */
	public $slug = 'postdata';

	/**
	 * @var string
	 */
	public $type = 'postdata';

	/**
	 * @var int
	 */
	public $position = 15;

	/**
	 * @var array
	 */
	public $options = array();

	/**
	 * @var string
	 */
	public $category = 'posts';

	/**
	 * @var string
	 */
	public $icon = 'sui-icon-post-pin';

	/**
	 * Forminator_Postdata constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		parent::__construct();

		$this->name = __( 'Post Data', Forminator::DOMAIN );
	}

	/**
	 * Field defaults
	 *
	 * @since 1.0
	 * @return array
	 */
	public function defaults() {

		return apply_filters(
			'forminator_post_data_defaults_settings',
			array(
				'data_status'        => 'pending',
				'post_title_label'   => 'Post Title',
				'post_content_label' => 'Post Content',
				'post_excerpt_label' => 'Post Excerpt',
				'post_image_label'   => 'Featured Image',
				'category_label'     => 'Category',
				'post_tag_label'     => 'Tags',
				'select_author'      => 1,
				'category_multiple'  => '0',
				'post_tag_multiple'  => '0',
				'post_type'          => 'post',
				'options'            => array(
					array(
						'label' => '',
						'value' => '',
					),
				),
			)
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
		return $settings;

		// TODO: support autofill-for-postdata
		//      $title_providers    = apply_filters( 'forminator_field_' . $this->slug . '_post_titlle_autofill', array(), $this->slug . '_post_titlle' );
		//      $content_providers  = apply_filters( 'forminator_field_' . $this->slug . '_post_content_autofill', array(), $this->slug . '_post_content' );
		//      $excerpt_providers  = apply_filters( 'forminator_field_' . $this->slug . '_post_excerpt_autofill', array(), $this->slug . '_post_excerpt' );
		//
		//      $autofill_settings = array(
		//          'postdata-post-title'    => array(
		//              'values' => forminator_build_autofill_providers( $title_providers ),
		//          ),
		//          'postdata-post-content'  => array(
		//              'values' => forminator_build_autofill_providers( $content_providers ),
		//          ),
		//          'postdata-post-excerpt'  => array(
		//              'values' => forminator_build_autofill_providers( $excerpt_providers ),
		//          ),
		//      );
		//
		//      return $autofill_settings;
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

		$html     = '';
		$required = self::get_property( 'required', $field, false );
		$id       = self::get_property( 'element_id', $field );
		$name     = $id;
		$design   = $this->get_form_style( $settings );

		$html .= $this->get_post_title( $id, $name, $field, $required, $design );
		$html .= $this->get_post_content( $id, $name, $field, $required );
		$html .= $this->get_post_excerpt( $id, $name, $field, $required, $design );
		$html .= $this->get_post_image( $id, $name, $field, $required, $design );
		$html .= $this->get_post_categories( $id, $name, $field, $required );
		$html .= $this->_render_custom_fields( $id, $name, $field, $required );

		return apply_filters( 'forminator_field_postdata_markup', $html, $field, $required, $id, $this );
	}

	/**
	 * Return post title
	 *
	 * @since 1.0
	 *
	 * @param $id
	 * @param $name
	 * @param $field
	 * @param $required
	 * @param $design
	 *
	 * @return string
	 */
	public function get_post_title( $id, $name, $field, $required, $design ) {

		return apply_filters(
			'forminator_field_postdata_post_title',
			$this->_get_post_field(
				$id,
				$name,
				$field,
				$required,
				'post_title',
				'text',
				'forminator-input',
				'post-title',
				array(),
				'',
				$design
			)
		);
	}

	/**
	 * Return post content
	 *
	 * @since 1.0
	 *
	 * @param $id
	 * @param $name
	 * @param $field
	 * @param $required
	 *
	 * @return string
	 */
	public function get_post_content( $id, $name, $field, $required ) {
		return apply_filters( 'forminator_field_postdata_post_content', $this->_get_post_field( $id, $name, $field, $required, 'post_content', 'wp_editor', 'forminator-textarea', 'post-content' ) );
	}

	/**
	 * Return post excerpt
	 *
	 * @since 1.0
	 *
	 * @param $id
	 * @param $name
	 * @param $field
	 * @param $required
	 * @param $design
	 *
	 * @return string
	 */
	public function get_post_excerpt( $id, $name, $field, $required, $design ) {

		return apply_filters(
			'forminator_field_postdata_post_excerpt',
			$this->_get_post_field(
				$id,
				$name,
				$field,
				$required,
				'post_excerpt',
				'textarea',
				'forminator-textarea',
				'post-excerpt',
				array(),
				'',
				$design
			)
		);
	}

	/**
	 * Return post featured image
	 *
	 * @since 1.0
	 *
	 * @param $id
	 * @param $name
	 * @param $field
	 * @param $required
	 * @param $design
	 *
	 * @return string
	 */
	public function get_post_image( $id, $name, $field, $required, $design ) {

		return apply_filters(
			'forminator_field_postdata_post_image',
			$this->_get_post_field(
				$id,
				$name,
				$field,
				$required,
				'post_image',
				'file',
				'forminator-upload',
				'post-image',
				array(),
				'',
				$design
			)
		);
	}

	/**
	 * Return categories
	 *
	 * @since 1.0
	 *
	 * @param $id
	 * @param $name
	 * @param $field
	 * @param $required
	 * @param $multiple
	 *
	 * @return string
	 */
	public function get_post_categories( $id, $name, $field, $required ) {
		$html          = '';
		$post_type     = self::get_property( 'post_type', $field, 'post' );
		$category_list = forminator_post_categories( $post_type );

		if ( ! empty( $category_list ) ) {
			foreach ( $category_list as $category ) {
				$options    = array();
				$categories = get_categories(
					array(
						'orderby'    => 'name',
						'order'      => 'ASC',
						'hide_empty' => false,
						'taxonomy'   => $category['value'],
					)
				);

				$categories = apply_filters( 'forminator_field_postdata_' . $category['value'] . '_list', $categories );

				foreach ( $categories as $cat ) {
					$options[] = array(
						'value' => $cat->term_id,
						'label' => $cat->name,
					);
				}

				$value          = '';
				$design         = '';
				$multiple       = self::get_property( $category['value'] . '_multiple', $field, false );
				$allow_multiple = $multiple ? 'multiple' : '';
				$select_type    = $multiple ? 'multiselect' : 'select';

				$html .= apply_filters(
					'forminator_field_postdata_' . $category['value'],
					$this->_get_post_field( $id, $name, $field, $required, $category['value'], $select_type, 'forminator-select', $category['value'], $options, $value, $design, $allow_multiple )
				);
			}
		}
		return $html;

	}

	/**
	 * Return post field
	 *
	 * @since 1.0
	 *
	 * @param        $id
	 * @param        $name
	 * @param        $field
	 * @param        $required
	 * @param        $field_name
	 * @param        $type
	 * @param        $class
	 * @param        $input_suffix
	 * @param array  $options
	 * @param string $value
	 * @param string $design
	 *
	 * @return string
	 */
	public function _get_post_field( $id, $name, $field, $required, $field_name, $type, $class, $input_suffix, $options = array(), $value = '', $design = '', $multiple = '' ) {

		$html          = '';
		$field_enabled = self::get_property( $field_name, $field, '' );
		$type          = trim( $type );

		if ( ! empty( $field_enabled ) ) {
			$cols         = 12;
			$placeholder  = esc_html( self::get_property( $field_name . '_placeholder', $field ) );
			$label        = esc_html( self::get_property( $field_name . '_label', $field ) );
			$description  = esc_html( self::get_property( $field_name . '_description', $field ) );
			$field_markup = array(
				'type'        => $type,
				'name'        => $id . '-' . $input_suffix,
				'placeholder' => $placeholder,
				'id'          => 'forminator-field-' . $input_suffix . '-' . $id,
				'class'       => $class,
			);

			if ( $required ) {
				$field_markup['required'] = $required;
			}

			if ( ! empty( $multiple ) ) {
				$field_markup['multiple'] = $multiple;
				$field_markup['name']     = $field_markup['name'] . '[]';
			}

			$html .= '<div class="forminator-row">';

				$html .= sprintf( '<div class="forminator-col forminator-col-%s">', $cols );

					$html .= '<div class="forminator-field">';

			if ( 'wp_editor' === $type ) {

				// multiple wp_editor support
				$field_markup['id'] = $field_markup['id'] . '-' . uniqid();

				$html .= self::create_wp_editor(
					$field_markup,
					$label,
					$description,
					$required
				);
			} elseif ( 'textarea' === $type ) {

				$html .= self::create_textarea(
					$field_markup,
					$label,
					$description,
					$required,
					$design
				);
			} elseif ( 'select' === $type ) {

				if ( empty( $options ) ) {
					unset( $field_markup['required'] );
				}

				$html .= self::create_select(
					$field_markup,
					$label,
					$options,
					$value,
					$description,
					$required
				);
			} elseif ( 'multiselect' === $type ) {
				if ( $label ) {
					if ( $required ) {
						$html .= sprintf(
							'<label for="%s" class="forminator-label">%s %s</label>',
							$id . '-field',
							$label,
							forminator_get_required_icon()
						);
					} else {
						$html .= sprintf(
							'<label for="%s" class="forminator-label">%s</label>',
							$id . '-field',
							$label
						);
					}
				}

				$post_value = self::get_post_data( $name, self::FIELD_PROPERTY_VALUE_NOT_EXIST );
				$name       = $id . '-' . $field_name . '[]';
				$get_id     = $id . '-' . $field_name;
				$html      .= '<div class="forminator-multiselect">';
				$i          = 1;
				foreach ( $options as $option ) {

					$value    = $option['value'] ? $option['value'] : $option['label'];
					$input_id = $id . '-' . $i . '-' . $field_name;

					$selected = false;

					if ( self::FIELD_PROPERTY_VALUE_NOT_EXIST !== $post_value ) {

						if ( is_array( $post_value ) ) {
							$selected = in_array( $value, $post_value ); // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
						}
					}

					$selected = $selected ? 'checked="checked"' : '';

					$html .= sprintf( '<label for="%s" class="forminator-option">', $input_id );

					$html .= sprintf(
						'<input type="checkbox" name="%s" value="%s" id="%s" %s />',
						$name,
						$value,
						$input_id,
						$selected
					);

					$html .= $option['label'];

					$html .= '</label>';

					$i ++;
				}

				$html .= '</div>';

				if ( ! empty( $description ) ) {
					$html .= self::get_description( $description, $get_id );
				}
			} elseif ( 'file' === $type ) {

				if ( $required ) {
					$html .= sprintf(
						'<label for="%s" class="forminator-label">%s %s</label>',
						'forminator-field-' . $input_suffix . '-' . $id,
						$label,
						forminator_get_required_icon()
					);
				} else {

					$html .= sprintf(
						'<label for="%s" class="forminator-label">%s</label>',
						'forminator-field-' . $input_suffix . '-' . $id,
						$label
					);
				}

				$html .= self::create_file_upload(
					'forminator-field-' . $input_suffix . '-' . $id,
					$name . '-' . $input_suffix,
					$description,
					$required,
					$design
				);
			} else {

				$html .= self::create_input(
					$field_markup,
					$label,
					$description,
					$required,
					$design
				);
			}

					$html .= '</div>';

				$html .= '</div>';

			$html .= '</div>';

		}

		return $html;
	}

	/**
	 * Render custom fields
	 *
	 * @since 1.0
	 *
	 * @param $id
	 * @param $name
	 * @param $field
	 * @param $required
	 *
	 * @return string
	 */
	private function _render_custom_fields( $id, $name, $field, $required ) {
		$html              = '';
		$cols              = 12;
		$has_custom_fields = self::get_property( 'post_custom', $field, false );

		if ( $has_custom_fields ) {
			$custom_vars = self::get_property( 'custom_vars', $field );

			if ( ! empty( $custom_vars ) ) {
				$html .= '<div class="forminator-row forminator-row--inner">';

				foreach ( $custom_vars as $variable ) {
					$html        .= sprintf( '<div class="forminator-col forminator-col-%s">', $cols );
					$value        = ! empty( $variable['value'] ) ? $variable['value'] : sanitize_title( $variable['label'] );
					$input_id     = $id . '-post_meta-' . $value;
					$label        = $variable['label'];
					$field_markup = array(
						'type'        => 'text',
						'class'       => 'forminator-input',
						'name'        => $input_id,
						'id'          => $input_id,
						'placeholder' => $label,
					);
					$html        .= self::create_input( $field_markup, $label, '' );
					$html        .= '</div>';
				}
			}

			$html .= '</div>';
		}

		return $html;
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

		$id = self::get_property( 'element_id', $field );

		$post_title               = self::get_property( 'post_title', $field, '' );
		$post_content             = self::get_property( 'post_content', $field, '' );
		$post_excerpt             = self::get_property( 'post_excerpt', $field, '' );
		$setting_required_message = self::get_property( 'required_message', $field, '' );
		$post_type                = self::get_property( 'post_type', $field, 'post' );

		$title         = isset( $data['post-title'] ) ? $data['post-title'] : '';
		$content       = isset( $data['post-content'] ) ? $data['post-content'] : '';
		$excerpt       = isset( $data['post-excerpt'] ) ? $data['post-excerpt'] : '';
		$image         = isset( $data['post-image'] ) ? $data['post-image'] : '';
		$category_list = forminator_post_categories( $post_type );

		if ( $this->is_required( $field ) ) {
			if ( empty( $data ) ) {
				$postdata_validation_message     = apply_filters(
					'forminator_postdata_field_validation_message',
					( ! empty( $setting_required_message ) ? $setting_required_message : __( 'This field is required. Please fill in post data', Forminator::DOMAIN ) ),
					$id
				);
				$this->validation_message[ $id ] = $postdata_validation_message;
			} elseif ( is_array( $data ) ) {
				$post_image = self::get_property( 'post_image', $field, '' );

				if ( ! empty( $post_title ) && empty( $title ) ) {

					$postdata_post_title_validation_message          = apply_filters(
						'forminator_postdata_field_post_title_validation_message',
						( ! empty( $setting_required_message ) ? $setting_required_message : __( 'This field is required. Please enter the post title', Forminator::DOMAIN ) ),
						$id
					);
					$this->validation_message[ $id . '-post-title' ] = $postdata_post_title_validation_message;
				}
				if ( ! empty( $post_content ) && empty( $content ) ) {
					$postdata_post_content_validation_message          = apply_filters(
						'forminator_postdata_field_post_content_validation_message',
						( ! empty( $setting_required_message ) ? $setting_required_message : __( 'This field is required. Please enter the post content', Forminator::DOMAIN ) ),
						$id
					);
					$this->validation_message[ $id . '-post-content' ] = $postdata_post_content_validation_message;
				}
				if ( ! empty( $post_excerpt ) && empty( $excerpt ) ) {
					$postdata_post_excerpt_validation_message          = apply_filters(
						'forminator_postdata_field_post_excerpt_validation_message',
						( ! empty( $setting_required_message ) ? $setting_required_message : __( 'This field is required. Please enter the post excerpt', Forminator::DOMAIN ) ),
						$id
					);
					$this->validation_message[ $id . '-post-excerpt' ] = $postdata_post_excerpt_validation_message;
				}
				if ( ! empty( $post_image ) && empty( $image ) ) {
					$postdata_post_image_validation_message          = apply_filters(
						'forminator_postdata_field_post_image_validation_message',
						( ! empty( $setting_required_message ) ? $setting_required_message : __( 'This field is required. Please upload a post image', Forminator::DOMAIN ) ),
						$id
					);
					$this->validation_message[ $id . '-post-image' ] = $postdata_post_image_validation_message;
				}
				if ( ! empty( $category_list ) ) {
					foreach ( $category_list as $cat ) {
						$post_category = self::get_property( $cat['value'], $field, '' );
						$category      = isset( $data[ $cat['value'] ] ) ? $data[ $cat['value'] ] : '';
						if ( ! empty( $post_category ) && empty( $category ) ) {
							$postdata_post_category_validation_message             = apply_filters(
								'forminator_postdata_field_' . $cat['value'] . '_validation_message',
								( ! empty( $setting_required_message ) ? $setting_required_message : sprintf( /* translators: ... */ __( 'This field is required. Please select a %s', Forminator::DOMAIN ), $cat['label'] ) ),
								$id
							);
							$this->validation_message[ $id . '-' . $cat['value'] ] = $postdata_post_category_validation_message;
						}
					}
				}
			}
		} else {
			// validation for postdata when its not required.
			// `wp_insert_post` required at least ONE OF THESE to be available title / content / excerpt.
			// check only when user send some data
			if ( ! empty( $data ) && is_array( $data ) ) {
				if ( ! $title && ! $content && ! $excerpt ) {
					// check if there is any field with content
					$is_content_available = false;
					foreach ( $data as $datum ) {
						if ( ! empty( $datum ) ) {
							$is_content_available = true;
							break;
						}
					}

					// when $is_content_available false means, field not required, and user didnt put any content on form
					if ( $is_content_available ) {
						//check if on postdata these sub field is avail available
						if ( ! empty( $post_title ) ) {
							$this->validation_message[ $id . '-post-title' ] = apply_filters(
								// nr = not required
								'forminator_postdata_field_post_title_nr_validation_message',
								__( 'At least one of these fields is required: Post Title, Post Excerpt or Post Content', Forminator::DOMAIN ),
								$id
							);
						}
						if ( ! empty( $post_content ) ) {
							$this->validation_message[ $id . '-post-content' ] = apply_filters(
								// nr = not required
								'forminator_postdata_field_post_content_nr_validation_message',
								__( 'At least one of these fields is required: Post Title, Post Excerpt or Post Content', Forminator::DOMAIN ),
								$id
							);
						}
						if ( ! empty( $post_excerpt ) ) {
							$this->validation_message[ $id . '-post-excerpt' ] = apply_filters(
								// nr = not required
								'forminator_postdata_field_post_excerpt_nr_validation_message',
								__( 'At least one of these fields is required: Post Title, Post Excerpt or Post Content', Forminator::DOMAIN ),
								$id
							);
						}
					}
				}
			}
		}
	}

	/**
	 * Upload post image
	 *
	 * @since 1.0
	 *
	 * @param array  $field      - the field
	 * @param string $field_name - the field name
	 *
	 * @return array|bool - if success, return an array
	 */
	public function upload_post_image( $field, $field_name ) {
		$post_image = self::get_property( 'post_image', $field, '' );

		if ( ! empty( $post_image ) ) {
			if ( isset( $_FILES[ $field_name ] ) ) {
				if ( isset( $_FILES[ $field_name ]['name'] ) && ! empty( $_FILES[ $field_name ]['name'] ) ) {
					$file_name = sanitize_file_name( $_FILES[ $field_name ]['name'] );
					//TODO: refactor upload to use WP filesystem api
					$file_data        = file_get_contents( $_FILES[ $field_name ]['tmp_name'] ); // phpcs:ignore
					$upload_dir       = wp_upload_dir(); // Set upload folder
					$unique_file_name = wp_unique_filename( $upload_dir['path'], $file_name );
					$filename         = basename( $unique_file_name ); // Create base file name

					if ( wp_mkdir_p( $upload_dir['path'] ) ) {
						$file = $upload_dir['path'] . '/' . $filename;
					} else {
						$file = $upload_dir['basedir'] . '/' . $filename;
					}

					// Create the  file on the server
					file_put_contents( $file, $file_data ); // phpcs:ignore

					// Check image file type
					$wp_filetype = wp_check_filetype( $filename, null );
					$image_exts  = apply_filters( 'forminator_field_postdata_image_file_types', array( 'jpg', 'jpeg', 'jpe', 'gif', 'png', 'bmp' ) );
					if ( in_array( (string) $wp_filetype['ext'], $image_exts, true ) ) {
						// Set attachment data
						$attachment = array(
							'post_mime_type' => $wp_filetype['type'],
							'post_title'     => sanitize_file_name( $filename ),
							'post_content'   => '',
							'post_status'    => 'inherit',
						);

						// Create the attachment
						$attachment_id = wp_insert_attachment( $attachment, $file );

						// Include image.php
						require_once ABSPATH . 'wp-admin/includes/image.php';

						// Define attachment metadata
						$attach_data = wp_generate_attachment_metadata( $attachment_id, $file );

						// Assign metadata to attachment
						wp_update_attachment_metadata( $attachment_id, $attach_data );
						$uploaded_file = wp_get_attachment_image_src( $attachment_id, 'large', false );
						if ( $uploaded_file && is_array( $uploaded_file ) ) {
							return array(
								'attachment_id' => $attachment_id,
								'uploaded_file' => $uploaded_file,
							);
						}
					}
				}
			}

			return array(
				'attachment_id' => 0,
				'uploaded_file' => 0,
			);
		}

		return true;
	}

	/**
	 * Save post
	 *
	 * @since 1.0
	 *
	 * @param array $field - field array
	 * @param array $data  - post data
	 *
	 * @return bool|int - success is post id
	 */
	public function save_post( $field, $data ) {
		$post_type            = self::get_property( 'post_type', $field, 'post' );
		$force_default_author = self::get_property( 'default_author', $field, false );
		$force_default_author = filter_var( $force_default_author, FILTER_VALIDATE_BOOLEAN );

		// default behaviour
		if ( is_user_logged_in() ) {
			$post_author = get_current_user_id();
		} else {
			$post_author = self::get_property( 'select_author', $field, 1 );
			if ( empty( $post_author ) || ! get_user_by( 'ID', $post_author ) ) {
				$post_author = $this->set_anonymous_author();
			}
		}

		// force to selected author
		if ( $force_default_author ) {
			$post_author = self::get_property( 'select_author', $field, 1 );
		}

		$post_status = self::get_property( 'data_status', $field, 'draft' );
		$title       = isset( $data['post-title'] ) ? $data['post-title'] : '';
		$content     = isset( $data['post-content'] ) ? $data['post-content'] : '';
		$excerpt     = isset( $data['post-excerpt'] ) ? $data['post-excerpt'] : '';
		$image       = isset( $data['post-image'] ) ? $data['post-image'] : '';
		$post_meta   = isset( $data['post-custom'] ) ? $data['post-custom'] : '';

		$post          = array(
			'post_author'  => $post_author,
			'post_content' => wp_kses_post( $content ),
			'post_excerpt' => $excerpt,
			'post_name'    => sanitize_text_field( $title ),
			'post_status'  => $post_status,
			'post_title'   => $title,
			'post_type'    => $post_type,
		);
		$category_list = forminator_post_categories( $post_type );
		if ( ! empty( $category_list ) ) {
			$taxonomy = array();
			foreach ( $category_list as $cat ) {
				$cat_value = $cat['value'];
				$category  = isset( $data[ $cat_value ] ) ? $data[ $cat_value ] : '';
				if ( ! empty( $category ) ) {
					if ( is_array( $category ) ) {
						$taxonomy[ $cat_value ] = array_map( 'intval', $category );
					} else {
						$taxonomy[ $cat_value ] = array( intval( $category ) );
					}
				}
			}
			$post['tax_input'] = $taxonomy;
		}

		$post = apply_filters( 'forminator_post_data_post_info', $post, $field, $data );

		//trigger wp_error for is_wp_error to be correctly identified
		$post_id = wp_insert_post( $post, true );
		if ( ! is_wp_error( $post_id ) ) {
			$post_image = self::get_property( 'post_image', $field, '' );
			if ( ! empty( $post_image ) && ! empty( $image ) && is_array( $image ) ) {
				set_post_thumbnail( $post_id, $image['attachment_id'] );
			}

			if ( ! empty( $post_meta ) ) {
				foreach ( $post_meta as $meta ) {
					add_post_meta( $post_id, $meta['key'], $meta['value'] );
				}
				add_post_meta( $post_id, '_has_forminator_meta', true );
			}

			do_action( 'forminator_post_data_field_post_saved', $post_id, $field, $data, $this );

			return $post_id;
		}

		return false;
	}

	private function set_anonymous_author() {
		$user = get_user_by( 'login', 'anonymous_user' );
		if ( $user ) {
			return $user->ID;
		} else {
			$userdata = array(
				'user_login'    => 'anonymous_user',
				// Set different user_nicename and display_name for security
				'user_nicename' => 'anonymous',
				'display_name'  => 'Anonymous',
				'role'          => 'author',
				'user_pass'     => null,
			);
			$new_user = wp_insert_user( $userdata );
			if ( ! is_wp_error( $new_user ) ) {
				return $new_user;
			}

			return 1;
		}
	}

	/**
	 * Sanitize data
	 *
	 * @since 1.0.2
	 *
	 * @param array        $field
	 * @param array|string $data - the data to be sanitized
	 *
	 * @return array|string $data - the data after sanitization
	 */
	public function sanitize( $field, $data ) {
		$original_data = $data;
		$image         = '';
		$content       = '';

		// Do not sanitize image URL
		if ( isset( $data['post-image'] ) ) {
			$image = $data['post-image'];
		}

		// Do not sanitize post content
		if ( isset( $data['post-content'] ) ) {
			$content = $data['post-content'];
		}

		// Sanitize
		$data = forminator_sanitize_field( $data );

		// Return image url original value
		if ( isset( $data['post-image'] ) ) {
			$data['post-image'] = $image;
		}

		// Return post content original value
		if ( isset( $data['post-content'] ) ) {
			$data['post-content'] = $content;
		}

		return apply_filters( 'forminator_field_postdata_sanitize', $data, $field, $original_data );
	}

	/**
	 * Return field inline validation rules
	 * Workaround for actually input file is hidden, so its not accessible via standar html5 `required` attribute
	 *
	 * @since 1.1
	 * @return string
	 */
	public function get_validation_rules() {
		$field              = $this->field;
		$id                 = self::get_property( 'element_id', $field );
		$is_required        = $this->is_required( $field );
		$post_image         = self::get_property( 'post_image', $field, '' );
		$post_type          = self::get_property( 'post_type', $field, 'post' );
		$post_image_enabled = ! empty( $post_image );
		$rules              = '';

		if ( $is_required && $post_image_enabled ) {
			$rules .= '"' . $this->get_id( $field ) . '-post-image": {';
			if ( $is_required ) {
				$rules .= '"required": true,';
			}
			$rules .= '},';
		}
		$category_list = forminator_post_categories( $post_type );
		if ( ! empty( $category_list ) ) {
			foreach ( $category_list as $category ) {
				$post_category_enabled = self::get_property( $category['value'], $field, '' );
				if ( $is_required && $post_category_enabled ) {
					$rules .= '"' . $this->get_id( $field ) . '-' . $category['value'] . '[]": {';
					if ( $is_required ) {
						$rules .= '"required": true,';
					}
					$rules .= '},';
				}
			}
		}

		return apply_filters( 'forminator_field_postdata_validation_rules', $rules, $id, $field );
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
		$messages    = '';

		$post_title               = self::get_property( 'post_title', $field, '' );
		$post_content             = self::get_property( 'post_content', $field, '' );
		$post_excerpt             = self::get_property( 'post_excerpt', $field, '' );
		$post_image               = self::get_property( 'post_image', $field, '' );
		$setting_required_message = self::get_property( 'required_message', $field, '' );
		$post_type                = self::get_property( 'post_type', $field, 'post' );

		$post_title_enabled   = ! empty( $post_title );
		$post_content_enabled = ! empty( $post_content );
		$post_excerpt_enabled = ! empty( $post_excerpt );
		$post_image_enabled   = ! empty( $post_image );
		$category_list        = forminator_post_categories( $post_type );

		if ( $is_required ) {
			if ( $post_title_enabled ) {
				$messages .= '"' . $id . '-post-title": {' . "\n";

				$required_message = apply_filters(
					'forminator_postdata_field_post_title_validation_message',
					( ! empty( $setting_required_message ) ? $setting_required_message : __( 'This field is required. Please enter the post title', Forminator::DOMAIN ) ),
					$id,
					$field
				);
				$messages         = $messages . '"required": "' . forminator_addcslashes( $required_message ) . '",' . "\n";

				$messages .= '},' . "\n";
			}
			if ( $post_content_enabled ) {
				$messages .= '"' . $id . '-post-content": {' . "\n";

				$required_message = apply_filters(
					'forminator_postdata_field_post_content_validation_message',
					( ! empty( $setting_required_message ) ? $setting_required_message : __( 'This field is required. Please enter the post content', Forminator::DOMAIN ) ),
					$id,
					$field
				);
				$messages         = $messages . '"required": "' . forminator_addcslashes( $required_message ) . '",' . "\n";

				$messages .= '},' . "\n";
			}
			if ( $post_excerpt_enabled ) {
				$messages .= '"' . $id . '-post-excerpt": {' . "\n";

				$required_message = apply_filters(
					'forminator_postdata_field_post_excerpt_validation_message',
					( ! empty( $setting_required_message ) ? $setting_required_message : __( 'This field is required. Please enter the post excerpt', Forminator::DOMAIN ) ),
					$id,
					$field
				);
				$messages         = $messages . '"required": "' . forminator_addcslashes( $required_message ) . '",' . "\n";

				$messages .= '},' . "\n";
			}
			if ( $post_image_enabled ) {
				$messages .= '"' . $id . '-post-image": {' . "\n";

				$required_message = apply_filters(
					'forminator_postdata_field_post_image_validation_message',
					( ! empty( $setting_required_message ) ? $setting_required_message : __( 'This field is required. Please upload a post image', Forminator::DOMAIN ) ),
					$id,
					$field
				);
				$messages         = $messages . '"required": "' . forminator_addcslashes( $required_message ) . '",' . "\n";

				$messages .= '},' . "\n";
			}
			if ( ! empty( $category_list ) ) {
				foreach ( $category_list as $category ) {
					$post_category_enabled = self::get_property( $category['value'], $field, '' );
					if ( $post_category_enabled ) {
						$post_category_multiple = self::get_property( $category['value'] . '_multiple', $field, '' );
						if ( $post_category_multiple ) {
							$messages .= '"' . $id . '-' . $category['value'] . '[]": {' . "\n";
						} else {
							$messages .= '"' . $id . '-' . $category['value'] . '": {' . "\n";
						}

						$required_message = apply_filters(
							'forminator_postdata_field_' . $category['value'] . '_validation_message',
							( ! empty( $setting_required_message ) ? $setting_required_message : sprintf( /* translators: ... */ __( 'This field is required. Please select a %s', Forminator::DOMAIN ), $category['singular'] ) ),
							$id,
							$field
						);
						$messages         = $messages . '"required": "' . forminator_addcslashes( $required_message ) . '",' . "\n";

						$messages .= '},' . "\n";
					}
				}
			}
		}

		return $messages;
	}
}
