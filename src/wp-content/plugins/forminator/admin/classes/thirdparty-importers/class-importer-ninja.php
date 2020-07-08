<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Admin_Import_Ninja
 *
 * @since 1.7
 */
class Forminator_Admin_Import_Ninja extends Forminator_Import_Mediator {

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
	 * Check in multipart exists
	 *
	 * @since 1.7
	 * @return bool
	 */
	public function ninja_multipart(){

		if( class_exists( 'NF_MultiPart' ) )
			return true;

		return false;
		
	}
	public function insert_pagination( $array, $insert, $position ) {
	    /*
	    $array : The initial array i want to modify
	    $insert : the new array i want to add, eg array('key' => 'value') or array('value')
	    $position : the position where the new array will be inserted into. Please mind that arrays start at 0
	    */
	    return array_slice( $array, 0, $position, true ) + $insert + array_slice( $array, $position, null, true );
	}
	/**
	 * Insert form data
	 *
	 * @since 1.7
	 * @return array Form import message
	 */
	public function import_form( $id ){

		$form 			= Ninja_Forms()->form( $id );
		$form_fields	= $form->get_fields();
		$actions 		= $form->get_actions();
		$pagination		= ( ! empty( $form->get()->get_setting( 'formContentData' ) ) ? $form->get()->get_setting( 'formContentData' ) : array() );
		$data 			= array();
		$new_fields 	= array();
		$settings 		= array();
		$tags 			= array();
		$count 			= array();
		$page_total 	= 0;
		$mkey 			= 0;

		//multipart
		if( $this->ninja_multipart() && isset( $pagination[0]['formContentData'] ) ){
			$page_total = count( $pagination );
			foreach ($pagination as $key => $value) {
				$page_key = call_user_func( 'end', array_values( $value['formContentData'] ) );
				$page[ "{$page_key}" ] = $value['order'] + 1; 
			}
		}

		// fields import
		foreach ( $form_fields as $key => $field ) {

			$type = $this->get_thirdparty_field_type( $field->get_setting( 'type' ) );
			if( '' === $type ) continue;

			if( 'submit' === $type ){

				$submit_label = esc_html( $field->get_setting( 'label' ) );

			} else {

				if( isset( $count[ $type ] ) && $count[ $type ] > 0 ){

					$count[ $type ] = $count[ $type ] + 1;

				} else {

					$count[ $type ] = 1;
				}

				$options = $field->get_setting('options');
				$field_options = array();
				$wrapper = 'wrapper-' . $this->random_wrapper_int() . '-' . $this->random_wrapper_int();

				if( !empty( $options ) ){

					foreach ( $options as $key => $option) {
					
						$field_options[] = array(
							'label' => esc_html( $option['label'] ), 
							'value' => esc_html( $option['value'] ),
							'limit'	=> ''
						);
					}
				}

				$new_fields[$mkey] = array(
					'field_label' 	=> esc_html( $field->get_setting( 'label' ) ),
					'type' 			=> esc_html( $type ),
					'element_id'  	=> esc_html( $type . '-' . $count[ $type ] ),
					'cols'  		=> 12,
					'wrapper_id'  	=> $wrapper,
					'options'		=> $field_options,
					'required'		=> filter_var( $field->get_setting( 'required' ), FILTER_VALIDATE_BOOLEAN ),
					'custom-class'	=> $field->get_setting( 'element_class' ),
					'description'	=> ( !empty( $field->get_setting( 'desc_text' ) ) ? $field->get_setting( 'desc_text' ) : '' ),
					'placeholder'	=> $field->get_setting( 'placeholder' ),
				);

				if( 'address' === $type ){

					if( 'address' === $field->get_setting('type') ){

						$new_fields[$mkey]['street_address']	= true;
						$new_fields[$mkey]['address_city'] 		= true;
						$new_fields[$mkey]['address_state'] 	= true;
						$new_fields[$mkey]['address_zip'] 		= true;
						$new_fields[$mkey]['address_country'] 	= true;
						$new_fields[$mkey]['address_line'] 		= true;

					} elseif( 'city' === $field->get_setting('type') ) {

						$new_fields[$mkey]['address_city']		= true;

					} elseif( 'zip' === $field->get_setting('type') ) {

						$new_fields[$mkey]['address_zip'] 		= true;

					} elseif( 'country' === $field->get_setting('type') ) {

						$new_fields[$mkey]['address_country'] 	= true;
					}
				}

				if( 'multiselect' === $type ){

					$new_fields[$mkey]['value_type'] = 'multiselect';
				}
			}

			$tag_key = $field->get_setting('key');
			$tags["{$tag_key}"] = "{$new_fields[$mkey]['element_id']}";

			if( isset( $page["{$tag_key}"] ) && $page["{$tag_key}"] < $page_total ){

				$mkey++;
				$element_key = $page["{$tag_key}"];

				$new_fields[$mkey] = array(
					'type' 			=> 'pagination',
					'element_id'  	=> esc_html( 'pagination-' . $element_key ),
					'cols'  		=> 12,
					'wrapper_id'  	=> 'wrapper-' . $this->random_wrapper_int() . '-' . $this->random_wrapper_int(),
				);

			}

			$mkey++;
		}//endforeach fields import

		$settings['use-admin-email'] = false;
		$settings['use-user-email'] = false;

		//form actions
		foreach ($actions as $key => $action) {
			$action = $action->get_settings();
			$active = filter_var( $action['active'], FILTER_VALIDATE_BOOLEAN );

			if( false === $active ) continue;

			if( 'email' === $action['type'] ){

				//admin email detection.
				if( isset( $action['to'] ) && '{system:admin_email}' === $action['to'] && false === $settings['use-admin-email']  ){

					$settings['use-admin-email']		= true;
					$settings['admin-email-title']		= $this->replace_invalid_tags( $action['email_subject'], $tags );
					$settings['admin-email-editor']		= $this->replace_invalid_tags( $action['email_message'], $tags );
					$settings['admin-email-from-name']	= $this->replace_invalid_tags( $action['reply_to'], $tags );
					$settings['admin-email-recipients']	= get_bloginfo( 'admin_email' );

				}

				//get the first user notification action
				if( isset( $action['to'] ) && '{system:admin_email}' !== $action['to'] && false === $settings['use-user-email'] ){

					$settings['use-user-email']			= true;
					$settings['user-email-title']		= $this->replace_invalid_tags( $action['email_subject'], $tags );
					$settings['user-email-editor']		= $this->replace_invalid_tags( $action['email_message'], $tags );
					$settings['user-email-from-name']	= $this->replace_invalid_tags( $action['reply_to'], $tags );
					$settings['user-email-recipients']	= $this->replace_invalid_tags( $action['to'], $tags );

				}
			}
			elseif( 'redirect' === $action['type'] ){
				$settings['submission-behaviour']	= 'behaviour-redirect';
				$settings['redirect-url']			= $action['redirect_url'];
			}
			elseif( 'successmessage' === $action['type'] && ! isset( $settings['submission-behaviour'] ) ){
				$settings['submission-behaviour']	= 'behaviour-thankyou';
				$settings['thankyou-message']		= $action['message'];
			}
			elseif( 'save' === $action['type'] ){
				$settings['store']	= $action['active'];
			}
		}//endforeach form actions

		//final settings
		$settings['formName']	= esc_html( $form->get()->get_setting( 'title' ) );
		$settings['custom-submit-text']	= isset( $submit_label ) ? $submit_label : '' ;

		//form data
		$data['status']	 			= 'publish';
		$data['version'] 			= FORMINATOR_VERSION;
		$data['type'] 				= 'form';
		$data['data']['fields']   	= $new_fields;
		$data['data']['settings'] 	= $settings;

		$data = apply_filters( 'forminator_ninja_form_import_data', $data );

		$import = $this->try_form_import( $data );

		return $import;
	}

}
