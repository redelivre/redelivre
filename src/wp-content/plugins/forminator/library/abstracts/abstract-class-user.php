<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

//Remove password field in Submissions page
add_action( 'forminator_custom_form_build_fields_mappers', array( 'Forminator_User', 'remove_password_from_all_fields' ) );
add_action( 'forminator_custom_form_filter_fields', array( 'Forminator_User', 'remove_password_from_all_fields' ) );
//Change form settings
add_filter( 'forminator_builder_data_settings_before_saving', array( 'Forminator_User', 'change_form_settings' ), 11, 2 );
//Remove password field from merge tags {all_fields}, {all_non_empty_fields}
add_filter( 'forminator_custom_form_before_form_fields', array( 'Forminator_User', 'remove_password_from_all_fields' ) );

/**
 * Class Forminator_User
 *
 * @since 1.11
 */
abstract class Forminator_User {

	/**
	 * Main constructor
	 */
	public function __construct() {
		//Remove {password-N} in mail data
		add_filter( 'forminator_custom_form_mail_data', array( $this, 'remove_password_in_form_mail_data' ) );
	}

	/**
	 * Remove password-N in mail data
	 *
	 * @param array $data
	 * @return array
	 */
	public function remove_password_in_form_mail_data( $data ) {
		foreach ( $data as $key => $value ) {
			if ( false !== stripos( $key, 'password-' ) ) {
				unset( $data[ $key ] );
			}
		}

		return $data;
	}

	/**
	 * Replace user value
	 *
	 * @param array   $field_data_array
	 * @param string  $user_key
	 * @return string $user_value
	 */
	public function replace_value( $field_data_array, $user_key ) {
		$user_value = '';
		foreach ( $field_data_array as $key => $field_data ) {
			if ( is_array( $field_data['value'] ) && ! empty( $field_data['value'] ) ) {
				foreach ( $field_data['value'] as $key_value => $value ) {
					$field_name = $field_data['name'] . '-' . $key_value;
					if ( $field_name === $user_key ) {
						$user_value = $value;
						break;
					}
				}
			}
			if ( ! is_array( $field_data['value'] ) && $field_data['name'] === $user_key ) {
				$user_value = $field_data['value'];
				break;
			}
		}

		return $user_value;
	}

	/**
	 * Remove password field from merge tag {all_fields}
	 *
	 * @param array $form_fields
	 * @return array
	 */
	public static function remove_password_from_all_fields( $form_fields ) {
		if ( ! empty( $form_fields ) ) {
			foreach ( $form_fields as $key => $form_field ) {
				$field_array = $form_field->to_formatted_array();
				$field_forms = forminator_fields_to_array();
				$field_type  = $field_array['type'];
				if ( 'password' === $field_type ) {
					unset( $form_fields[ $key ] );
				}
			}
		}

		return $form_fields;
	}

	/**
	 * Get encryption key
	 *
	 * @return string
	 */
	protected static function get_encryption_key() {
		return 'forminator_encryption_key' . wp_salt( 'nonce' );
	}

	/**
	 * Encrypt non-Openssl
	 *
	 * @param string $text  The text to encrypt
	 * @param string $key   Key for encryption
	 *
	 * @return string       Encrypted String
	 */
	protected static function encrypt( $text, $key ) {
		$salt   = self::make_salt();
		$key    = $salt . $key;
		$strlen = strlen( $text );
		$hash   = base64_encode( sha1( $key ) );//phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$hslen  = strlen( $hash );
		$rt     = '';

		for ( $i = 0; $i < $strlen; $i++ ) {
			$rt .= chr( ord( ( $text[ $i ] ^ $hash[ $hslen % ( $i + 1 ) ] ^ $hash[ $i % ( ( $hslen % ( $i + 1 ) ) + 1 ) ] ) ^ $hash[ $i % ( $hslen - 1 ) ] ) + ( (int) round( $hslen / ( $i + 1 ) ) ) );
		}

		return strrev( base64_encode( strrev( strrev( $salt ) . $rt ) ) );//phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}

	/**
	 * Decrypt non-Openssl
	 *
	 * @param string $text  The text to decrypt
	 * @param string $key   Key for encryption
	 *
	 * @return string       Decrypted String
	 */
	protected static function decrypt( $text, $key ) {
		$text   = strrev( base64_decode( strrev( ( $text ) ) ) );//phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		$salt   = substr( $text, 0, 8 );
		$text   = substr( $text, 8 );
		$key    = strrev( $salt ) . $key;
		$strlen = strlen( $text );
		$hash   = base64_encode( sha1( $key ) );//phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$hslen  = strlen( $hash );

		$rt = '';
		for ( $i = 0; $i < $strlen; $i++ ) {
			$text[ $i ] = chr( ord( $text[ $i ] ) - ( (int) round( $hslen / ( $i + 1 ) ) ) );
			$rt        .= chr( ord( ( $text[ $i ] ^ $hash[ $hslen % ( $i + 1 ) ] ^ $hash[ $i % ( ( $hslen % ( $i + 1 ) ) + 1 ) ] ) ^ $hash[ $i % ( $hslen - 1 ) ] ) );
		}

		return $rt;
	}

	/**
	 * Make salt
	 *
	 * @return string
	 */
	private static function make_salt() {
		$chars = 'QWERTYUIOPASDFGHJKLZXCVBNMqwertyuiopasdfghjklzxcvbnm1234567890_';
		$salt  = '';
		for ( $i = 0; $i < 8; $i++ ) {
			$salt .= $chars[ wp_rand( 0, 62 ) ];
		}

		return $salt;
	}

	/**
	 * Encrypt AES-256-CTR with HMAC-SHA-512 hash
	 *
	 * @param string $text           The text to encrypt
	 * @param string $encryption_key Key for encryption
	 * @param string $cipher_name    The cypher name. Default 'aes-256-ctr'
	 * @param string $mac_key        The key to be used to generate the hash
	 *
	 * @return string|false
	 */
	public static function openssl_encrypt( $text, $encryption_key = null, $cipher_name = 'aes-256-ctr', $mac_key = null ) {
		if ( function_exists( 'openssl_encrypt' ) ) {
			$nonce = openssl_random_pseudo_bytes( 16 );

			if ( empty( $encryption_key ) ) {
				$encryption_key = self::get_encryption_key();
			}

			// OPENSSL_RAW_DATA is not available on PHP 5.3
			$options    = defined( 'OPENSSL_RAW_DATA' ) ? OPENSSL_RAW_DATA : 1;
			$ciphertext = openssl_encrypt( $text, $cipher_name, $encryption_key, $options, $nonce );

			if ( empty( $ciphertext ) ) {
				return false;
			}

			if ( empty( $mac_key ) ) {
				$mac_key = 'forminator_encryption_mac' . wp_salt( 'nonce' );
			}

			$mac             = hash_hmac( 'sha512', $nonce . $ciphertext, $mac_key, true );
			$encrypted_value = base64_encode( $mac . $nonce . $ciphertext );//phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		} else {
			if ( empty( $encryption_key ) ) {
				$encryption_key = self::get_encryption_key();
			}
			$encrypted_value = self::encrypt( $text, $encryption_key );
		}

		return $encrypted_value;
	}

	/**
	 * Decrypt AES-256-CTR with HMAC-SHA-512 hash.
	 *
	 * @param string $text           The text to decrypt
	 * @param string $encryption_key Key for encryption
	 * @param string $cipher_name    The cypher name. Default 'aes-256-ctr'
	 * @param string $mac_key        The key to be used for the hash
	 *
	 * @return string|false
	 */
	public static function openssl_decrypt( $text, $encryption_key = null, $cipher_name = 'aes-256-ctr', $mac_key = null ) {
		if ( function_exists( 'openssl_decrypt' ) ) {
			$text_decoded = base64_decode( $text );//phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
			$mac          = substr( $text_decoded, 0, 64 );
			$nonce        = substr( $text_decoded, 64, 16 );
			$ciphertext   = substr( $text_decoded, 80 );

			if ( empty( $mac_key ) ) {
				$mac_key = 'forminator_encryption_mac' . wp_salt( 'nonce' );
			}

			$mac_check = hash_hmac( 'sha512', $nonce . $ciphertext, $mac_key, true );
			if ( ! hash_equals( $mac_check, $mac ) ) {
				return false;
			}

			if ( empty( $encryption_key ) ) {
				$encryption_key = self::get_encryption_key();
			}

			// OPENSSL_RAW_DATA is not available on PHP 5.3
			$options         = defined( 'OPENSSL_RAW_DATA' ) ? OPENSSL_RAW_DATA : 1;
			$decrypted_value = openssl_decrypt( $ciphertext, $cipher_name, $encryption_key, $options, $nonce );
		} else {
			if ( empty( $encryption_key ) ) {
				$encryption_key = self::get_encryption_key();
			}
			$decrypted_value = self::decrypt( $text, $encryption_key );
		}

		return $decrypted_value;
	}

	/**
	 * Remove a password field.
	 *
	 * @param array $field_data_array
	 *
	 * @return array
	 */
	public function remove_password( $field_data_array ) {
		foreach ( $field_data_array as $key => $field_arr ) {
			if ( false !== stripos( $field_arr['name'], 'password-' ) ) {
				unset( $field_data_array[ $key ] );
				break;
			}
		}

		return $field_data_array;
	}

	/**
	 * Change the settings by saving the specified HTML tags
	 *
	 * @param array $sanitized_settings
	 * @param array $settings
	 *
	 * @return array
	 */
	public static function change_form_settings( $sanitized_settings, $settings ) {
		if ( isset( $sanitized_settings['form-type'] ) && in_array( $sanitized_settings['form-type'], array( 'login', 'registration' ), true ) ) {
			$form_key = 'hidden-' . $sanitized_settings['form-type'] . '-form-message';
			if ( isset( $settings[ $form_key ] ) && ! empty( $settings[ $form_key ] ) ) {
				$allowed_html                    = array(
					'a'      => array(
						'href'  => true,
						'title' => true,
					),
					'br'     => array(),
					'em'     => array(),
					'strong' => array(),
				);
				$sanitized_settings[ $form_key ] = wp_kses( $settings[ $form_key ], $allowed_html );
			}
		}

		return $sanitized_settings;
	}
}
