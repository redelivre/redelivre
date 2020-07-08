<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Admin_Module
 *
 * @since 1.7
 */
abstract class Forminator_Import_Mediator {

	/**
	* Stored form data.
	*
	* @since  1.7
	* @access public
	* @var    array
	*/
	public $data = array();	

	/**
	 * Plugin instance
	 * @since  1.7
	 * @access private
	 * @var null
	 */
	private static $instance = null;

	/**
	 * Return the plugin instance
	 *
	 * @since 1.7
	 * @return Forminator
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Insert form data
	 * Override by child classes
	 *
	 * @since 1.7
	 * @return array Parsed form data
	 */
	public function import_form( $id ){

		return array();

	}

	/**
	 * Replaces invalid tags with forminator tags
	 *
	 *
	 * @since 1.7
	 * @return string returns string with valid field tag format
	 */
	public function replace_invalid_tags( $mayhavetags, $tags = array() ) {

		if( ! empty( $tags ) ){
			$tags = array_merge( $tags, array( 
				'{all_fields_table}' => '{all_fields}',
				'{referer}'			 => '{referer_url}'
			));
		}

		$mayhavetags = strtr( $mayhavetags, $tags );

		return $mayhavetags;
	}

	/**
	 * Replace default tag
	 *
	 * @param $tags
	 *
	 * @return string
	 */
	public function replace_default_tags( $tags ) {
		$value = '';
		if ( ! empty( $tags ) ) {
			switch ( $tags ) {
				case 'user_login':
					$value = "wp_user.login";
					break;
				case 'user_email':
					$value = "wp_user.email";
					break;
				case 'user_first_name':
					$value = "wp_user.first_name";
					break;
				case 'user_last_name':
					$value = "wp_user.last_name";
					break;
				case 'user_display_name':
					$value = "wp_user.display_name";
					break;
				default:
					break;
			}
		}

		return $value;
	}

	/**
	 * Default form fields
	 *
	 *
	 * @since 1.7
	 *
	 * @return array
	 */
	public static function default_fields() {
		//todo: check if defaults are available
		$fields = array(
			'name',
			'email',
			'phone',
			'address',
			'url',
			'website',
			'checkbox',
			'number',
			'textarea',
			'text',
			'select',
			'radio',
			'calculation',
			'date',
			'time',
			'upload',
			'section',
			'hidden',
			'html',
			'postdata',
			'pagination',
			'stripe',
			'currency',
			'gdprcheckbox',
			'honeypot',
			'captcha',
			'submit'
		);

		return apply_filters( 'forminator_default_fields', $fields );
	}

	/**
	 * Return random number
	 *
	 *
	 * @since 1.7
	 * @return int random number
	 */
	public function random_wrapper_int(){

		//get all forms

		$int = intval( (float)rand()/(float)getrandmax() * 9999 );

		return absint( $int );
	}

	/**
	 * Replaces cf7 tags with forminator tags
	 *
	 *
	 * @since 1.7
	 * @return string returns string with valid field tag format
	 */
	public function get_thirdparty_field_type( $type ){

		switch( trim( $type ) ) {
			case 'firstname':
			case 'lastname':
				$type='name';
				break;
			case 'textbox':
			case 'dynamichidden':
				$type='text';
				break;	
			case 'acceptance':
				$type='gdprcheckbox';
				break;
			case 'honeypot':
				$type='honeypot';
				break;
			case 'listradio':
				$type='radio';
				break;		
			case 'listselect':
			case 'listmultiselect':
			case 'multiselect':
			case 'multiple':
			case 'liststate':
				$type='select';
				break;		
			case 'listcheckbox':
				$type='checkbox';
				break;		
			case 'city':
			case 'listcountry':
			case 'zip':
				$type='address';
				break;
			case 'textarea':
			case 'description':
				$type='textarea';
				break;
			case 'tel':
				$type='phone';
				break;
			case 'url':
				$type='url';
				break;
			case 'page':
				$type='pagination';
				break;
			case 'file':
			case 'fileupload':
				$type='upload';
				break;
			case 'recaptcha':
				$type='captcha';
				break;
			default:
				break;
		}

		if( ! in_array( $type, self::default_fields(), true ) ) $type = '';

		return $type;
	}

	/**
	 * Tries the form import
	 *
	 *
	 * @since 1.7
	 * @return object instance of Forminator_Custom_Form_Model
	 */
	public function try_form_import( $import_data ){
		try {
			if ( empty( $import_data ) || ! is_array( $import_data ) ) {
				throw new Exception( __( 'Oops, looks like we found an issue. Import text can not include whitespace or special characters.', Forminator::DOMAIN ) );
			}

			if ( ! isset( $import_data['type'] ) || 'form' !== $import_data['type'] ) {
				throw new Exception( __( 'Oops, looks like we found an issue. Import text can not include whitespace or special characters.', Forminator::DOMAIN ) );
			}

			$import_data = $this->parse_import_data( $import_data );

			$model = Forminator_Custom_Form_Model::create_from_import_data( $import_data, 'Forminator_Custom_Form_Model' );


			if ( is_wp_error( $model ) ) {
				throw new Exception( $model->get_error_message() );
			}

			if ( !$model instanceof Forminator_Custom_Form_Model ) {
				throw new Exception( __( 'Failed to import form, please make sure import text is valid, and try again.', Forminator::DOMAIN ) );
			}

			$return_url = admin_url( 'admin.php?page=forminator-cform' );

			return array(
				'id'  => $model->id,
				'url' => $return_url,
				'type'=> 'success'
			);

		} catch ( Exception $e ) {
			return array( 
				'message' => $e->getMessage(), 
				'type'=>'fail'
			);
		}
	}

	/**
	 * Parses form data structure
	 *
	 *
	 * @since 1.7
	 * @return array Parsed form data
	 */
	public function parse_import_data( $data ){
		if( empty( $data ) || ! is_array( $data ) || ! isset( $data['data']['fields'] ) ) return;

		$fields = $form_data = array();

		foreach ($data['data']['fields'] as $key => $value) {
			$value['id'] = $value['element_id'];
			$fields[] = $value;
		}

		$form_data['data']['fields']    = $fields;
		$form_data['data']['settings'] 	= $data['data']['settings'];
		$form_data['name'] 	      	    = $data['data']['settings']['formName'];
		$form_data['type'] 	   	        = $data['type'];
		$form_data['status']	        = $data['status'];
		$form_data['version'] 	        = FORMINATOR_VERSION;

		return $form_data;
	}
}
