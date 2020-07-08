<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Admin_Import_Gravity
 *
 * @since 1.7
 */
class Forminator_Admin_Import_Gravity extends Forminator_Import_Mediator {

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
	 *
	 * @since 1.7
	 * @return array Form import message
	 */
	public function import_form( $id ){

		$form 			= GFAPI::get_form( $id );
		$form_fields	= $form['fields'];
		$notifications	= $form['notifications'];
		$confirmations	= $form['confirmations'];
		$data 			= array();
		$new_fields 	= array();
		$settings 		= array();
		$tags 			= array();

		// fields import
		foreach ( $form_fields as $mkey => $field ) {

			$type = $this->get_thirdparty_field_type( $field['type'] );
			if( '' === $type ) continue;

			if( isset( $count[ $type ] ) && $count[ $type ] > 0 ){
				$count[ $type ] = $count[ $type ] + 1;
			} else {
				$count[ $type ] = 1;
			}

			$options = $field['choices'];
			$field_options = array();
			$wrapper = 'wrapper-' . $this->random_wrapper_int() . '-' . $this->random_wrapper_int();

			if( !empty( $options ) ){

				foreach ( $options as $key => $option) {
					$field_options[] = array(
						'label' => esc_html( $option['text'] ), 
						'value' => esc_html( $option['value'] ),
						'limit'	=> ''
					);
				}
			}

			$new_fields[$mkey] = array(
				'field_label' 	=> esc_html( $field['label'] ),
				'type' 			=> esc_html( $type ),
				'element_id'  	=> esc_html( $type . '-' . $count[ $type ] ),
				'cols'  		=> 12,
				'wrapper_id'  	=> $wrapper,
				'options'		=> $field_options,
				'required'		=> filter_var( $field['isRequired'], FILTER_VALIDATE_BOOLEAN ),
				'custom-class'	=> $field['cssClass'],
				'description'	=> $field['description'],
				'placeholder'	=> esc_html( $field['placeholder'] ),
			);

			if( 'address' === $type ){
				foreach ($field['inputs'] as $key => $input) {
					if( '4.1' === $input['id'] ){
						$new_fields[$mkey]['street_address']	= ! isset ( $input['isHidden'] );
					}elseif( '4.2' === $input['id'] ){
						$new_fields[$mkey]['address_line'] 		= ! isset ( $input['isHidden'] );
					}elseif( '4.3' === $input['id'] ){
						$new_fields[$mkey]['address_city'] 		= ! isset ( $input['isHidden'] );
					}elseif( '4.4' === $input['id'] ){
						$new_fields[$mkey]['address_state'] 	= ! isset ( $input['isHidden'] );
					}elseif( '4.5' === $input['id'] ){
						$new_fields[$mkey]['address_zip'] 		= ! isset ( $input['isHidden'] );
					}elseif( '4.6' === $input['id'] ){
						$new_fields[$mkey]['address_country'] 	= ! isset ( $input['isHidden'] );
					}
				}

			}

			if( 'multiselect' === $field['type'] ){

				$new_fields[$mkey]['value_type'] = 'multiselect';
			}

			if( 'page' === $field['type'] ){
				$new_fields[$mkey]['btn_left'] 	= $field['previousButton']['text'];
				$new_fields[$mkey]['btn_right'] = $field['nextButton']['text'];
			}

			$tag_key = $field['label'] . ':' . $field['id'];
			$tags["{$tag_key}"] = $new_fields[$mkey]['element_id'];

		}//endforeach fields import

		$settings['use-admin-email'] = false;
		$settings['use-user-email']  = false;

		//form actions
		if( ! empty( $notifications ) ){

			foreach ($notifications as $key => $action) {

				if( 'email' === $action['toType'] ){

					if( isset( $action['to'] ) && '{admin_email}' === $action['to'] && false === $settings['use-admin-email']  ){

						$settings['use-admin-email']				= true;
						$settings['admin-email-title']				= ( isset( $action['subject'] ) ? $this->replace_invalid_tags( $action['subject'], $tags ) : '' );

						$settings['admin-email-editor']				= ( isset( $action['message'] ) ? $this->replace_invalid_tags( $action['message'], $tags ) : '' );

						$settings['admin-email-from-name']			= ( isset( $action['fromName'] ) ? $this->replace_invalid_tags( $action['fromName'], $tags ) : '' );

						$settings['admin-email-recipients']			= get_bloginfo( 'admin_email' );

						$settings['admin-email-bcc-address']		= ( isset( $action['bcc'] ) ? $this->replace_invalid_tags( $action['bcc'], $tags ) : '' );

						$settings['admin-email-cc-address'] 		= ( isset( $action['cc'] ) ? $this->replace_invalid_tags( $action['cc'], $tags ) : '' );

						$settings['admin-email-reply-to-address'] 	= ( isset( $action['replyTo'] ) ? $this->replace_invalid_tags( $action['replyTo'], $tags ) : '' );

					}elseif( isset( $action['to'] ) && '{admin_email}' !== $action['to'] && false === $settings['use-user-email'] ){

						$settings['use-user-email']					= true;

						$settings['user-email-title']				= ( isset( $action['subject'] ) ? $this->replace_invalid_tags( $action['subject'], $tags ) : '' );

						$settings['user-email-editor']				= ( isset( $action['message'] ) ? $this->replace_invalid_tags( $action['message'], $tags ) : '' );

						$settings['user-email-from-name']			= ( isset( $action['fromName'] ) ? $this->replace_invalid_tags( $action['fromName'], $tags ) : '' );

						$settings['user-email-recipients']			= ( isset( $action['to'] ) ? $this->replace_invalid_tags( $action['to'], $tags ) : '' );

						$settings['user-email-bcc-address']			= ( isset( $action['bcc'] ) ? $this->replace_invalid_tags( $action['bcc'], $tags ) : '' );

						$settings['user-email-cc-address'] 			= ( isset( $action['cc'] ) ? $this->replace_invalid_tags( $action['cc'], $tags ) : '' );

						$settings['user-email-reply-to-address'] 	= ( isset( $action['replyTo'] ) ? $this->replace_invalid_tags( $action['replyTo'], $tags ) : '' );
					}
				}
			}
		}//end settings loop

		$action = ( !empty( $confirmations ) ? current( $confirmations ) : '' ); 

		if( ! empty( $action ) && isset( $action['type'] ) ){
			switch ( $action['type'] ) {
				case 'page':
				case 'redirect':
					$settings['submission-behaviour']	= 'behaviour-redirect';
					$url = ( isset( $action['pageid'] ) ? get_permalink( $action['pageid'] ) : $action['url'] );
					$settings['redirect-url']			= esc_url( $url );	
					break;			
				case 'message':
					$settings['submission-behaviour']	= 'behaviour-thankyou';
					$settings['thankyou-message']		= $action['message'];
					break;
				default:
					break;
			}

		}

		//final settings
		$settings['formName']			= esc_html( $form['title'] );
		$settings['custom-submit-text']	= esc_html( $form['button']['text'] );

		//form data
		$data['status']	 			= 'publish';
		$data['version'] 			= FORMINATOR_VERSION;
		$data['type'] 				= 'form';
		$data['data']['fields']   	= $new_fields;
		$data['data']['settings'] 	= $settings;

		$data = apply_filters( 'forminator_gravity_form_import_data', $data );
		$import = $this->try_form_import( $data );

		return $import;
	}

}
