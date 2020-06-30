<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Spam_Protection
 * 
 * Spam Protection parent class
 *
 * @since 1.0
 */
abstract class Forminator_Spam_Protection {

	/**
	 * Main Plugin constructor
	 *
	 * @since 1.0
	 */
	public function __construct() {
		if ( $this->is_enabled() ) {
			add_filter( 'forminator_spam_protection', array( $this, '_handle_spam_protection'), 10, 4 );
		}
	}

	/**
	 * Check if the plugin or setting is enabled
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function is_enabled() {
		return false;
	}

	/**
	 * Handle spam protection
	 *
	 * @since 1.0
	 * @param bool $is_spam - if the data is spam
	 * @param array $posted_params - the posted parameters
	 * @param int $form_id - the form id
	 * @param string $form_type - the form type
	 * 
	 * @return bool $is_spam
	 */
	public function _handle_spam_protection( $is_spam, $posted_params, $form_id, $form_type ) {
		return $this->handle_spam_protection( $is_spam, $posted_params, $form_id, $form_type );
	}

	/**
	 * Handle spam protection
	 * 
	 * @see _handle_spam_protection
	 *
	 * @since 1.0
	 * @param bool $is_spam - if the data is spam
	 * @param array $posted_params - the posted parameters
	 * @param int $form_id - the form id
	 * @param string $form_type - the form type
	 * 
	 * @return bool $is_spam
	 */
	protected function handle_spam_protection( $is_spam, $posted_params, $form_id, $form_type ) {
		return $is_spam;
	}
}
