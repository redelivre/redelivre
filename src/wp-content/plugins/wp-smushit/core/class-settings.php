<?php
/**
 * Smush Settings class: Settings
 *
 * @since 3.0  Migrated from old settings class.
 * @package Smush\Core
 */

namespace Smush\Core;

use WP_Smush;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class Settings
 *
 * @since 3.0
 */
class Settings {

	/**
	 * Plugin instance.
	 *
	 * @since 3.0
	 *
	 * @var null|Settings
	 */
	private static $instance = null;

	/**
	 * Settings array.
	 *
	 * @since 3.2.2
	 * @var array $settings
	 */
	private $settings = array();

	/**
	 * Default settings array.
	 *
	 * We don't want it to be edited directly, so we use public get_*, set_* and delete_* methods.
	 *
	 * @since 3.0    Improved structure.
	 * @simce 3.2.2  Changed to be a default array.
	 *
	 * @var array
	 */
	private $defaults = array(
		'auto'              => true,  // works with CDN.
		'lossy'             => false, // works with CDN.
		'strip_exif'        => true,  // works with CDN.
		'resize'            => false,
		'detection'         => false,
		'original'          => false,
		'backup'            => false,
		'png_to_jpg'        => false, // works with CDN.
		'nextgen'           => false,
		's3'                => false,
		'gutenberg'         => false,
		'js_builder'        => false,
		'cdn'               => false,
		'auto_resize'       => false,
		'webp'              => true,
		'usage'             => false,
		'accessible_colors' => false,
		'keep_data'         => true,
		'lazy_load'         => false,
		'background_images' => true,
		'rest_api_support'  => false, // CDN option.
	);

	/**
	 * Available modules.
	 *
	 * @sincr 3.2.2
	 * @var array $modules
	 */
	private $modules = array( 'bulk', 'integrations', 'lazy_load', 'cdn', 'tools', 'settings' );

	/**
	 * List of features/settings that are free.
	 *
	 * @var array $basic_features
	 */
	public static $basic_features = array( 'bulk', 'auto', 'strip_exif', 'resize', 'gutenberg', 'js_builder', 'lazy_load' );

	/**
	 * List of fields in bulk smush form.
	 *
	 * @used-by save()
	 *
	 * @var array
	 */
	private $bulk_fields = array( 'bulk', 'auto', 'lossy', 'original', 'strip_exif', 'resize', 'backup', 'png_to_jpg' );

	/**
	 * List of fields in integration form.
	 *
	 * @used-by save()
	 *
	 * @var array
	 */
	private $integrations_fields = array( 'gutenberg', 'js_builder', 's3', 'nextgen' );

	/**
	 * List of fields in CDN form.
	 *
	 * @used-by save()
	 *
	 * @var array
	 */
	private $cdn_fields = array( 'cdn', 'background_images', 'auto_resize', 'webp', 'rest_api_support' );

	/**
	 * List of fields in Settings form.
	 *
	 * @used-by save()
	 *
	 * @var array
	 */
	private $settings_fields = array( 'accessible_colors', 'usage', 'keep_data', 'api_auth' );

	/**
	 * List of fields in lazy loading form.
	 *
	 * @used-by save()
	 *
	 * @var array
	 */
	private $lazy_load_fields = array( 'lazy_load' );

	/**
	 * List of fields in tools form.
	 *
	 * @used-by save()
	 *
	 * @var array
	 */
	private $tools_fields = array( 'detection' );

	/**
	 * Return the plugin instance.
	 *
	 * @since 3.0
	 *
	 * @return Settings
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * WP_Smush_Settings constructor.
	 */
	private function __construct() {
		// Do not initialize if not in admin area
		// wp_head runs specifically in the frontend, good check to make sure we're accidentally not loading settings on required pages.
		if ( ! is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) && did_action( 'wp_head' ) ) {
			return;
		}

		// Save Settings.
		add_action( 'wp_ajax_save_settings', array( $this, 'save' ) );
		// Reset Settings.
		add_action( 'wp_ajax_reset_settings', array( $this, 'reset' ) );

		$this->init();
	}

	/**
	 * Getter method for bulk settings fields.
	 *
	 * @since 3.2.2
	 * @return array
	 */
	public function get_bulk_fields() {
		return $this->bulk_fields;
	}

	/**
	 * Getter method for integration fields.
	 *
	 * @since 3.2.2
	 * @return array
	 */
	public function get_integrations_fields() {
		return $this->integrations_fields;
	}

	/**
	 * Getter method for CDN fields.
	 *
	 * @since 3.2.2
	 * @return array
	 */
	public function get_cdn_fields() {
		return $this->cdn_fields;
	}

	/**
	 * Getter method for tools fields.
	 *
	 * @since 3.2.2
	 * @return array
	 */
	public function get_tools_fields() {
		return $this->tools_fields;
	}

	/**
	 * Getter method for settings fields.
	 *
	 * @since 3.2.2
	 * @return array
	 */
	public function get_settings_fields() {
		return $this->settings_fields;
	}

	/**
	 * Getter method for lazy loading fields.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	public function get_lazy_load_fields() {
		return $this->lazy_load_fields;
	}

	/**
	 * Init settings.
	 *
	 * If there are no settings in the database, populate it with the defaults, if settings are present
	 */
	public function init() {
		$site_settings = array();

		$global = $this->is_network_enabled();

		// Always get global settings if global settings enabled or is in network admin.
		if ( true === $global || ( is_array( $global ) && is_network_admin() ) ) {
			$site_settings = get_site_option( WP_SMUSH_PREFIX . 'settings', array() );
		}

		if ( false === $global ) {
			$site_settings = get_option( WP_SMUSH_PREFIX . 'settings', array() );

			if ( ! is_multisite() ) {
				$this->settings = $site_settings;
			}

			// Make sure we're not missing any settings.
			$global_settings = get_site_option( WP_SMUSH_PREFIX . 'settings', array() );
			$undefined       = array_diff( $global_settings, $site_settings );

			$site_settings = array_merge( $site_settings, $undefined );

			// Settings are taken from global settings.
			if ( ! empty( $global_settings ) ) {
				$site_settings['accessible_colors'] = isset( $global_settings['accessible_colors'] ) ? $global_settings['accessible_colors'] : $this->defaults['accessible_colors'];
				$site_settings['usage']             = isset( $global_settings['usage'] ) ? $global_settings['usage'] : $this->defaults['usage'];
				$site_settings['keep_data']         = isset( $global_settings['keep_data'] ) ? $global_settings['keep_data'] : $this->defaults['keep_data'];
			}
		}

		// Custom access enabled - combine settings from network with site settings.
		if ( is_array( $global ) ) {
			$network_settings = array_diff( $this->modules, $global );
			$global_settings  = get_site_option( WP_SMUSH_PREFIX . 'settings', array() );
			$site_settings    = get_option( WP_SMUSH_PREFIX . 'settings', array() );

			foreach ( $network_settings as $key ) {
				// Remove values that are network wide from site settings.
				$site_settings = array_diff_key( $site_settings, array_flip( $this->{$key . '_fields'} ) );
				// Take the values from network settings.
				$network_part = array_intersect_key( $global_settings, array_flip( $this->{$key . '_fields'} ) );
				// And append them to the site settings.
				$site_settings = array_merge( $site_settings, $network_part );
			}
		}

		if ( empty( $site_settings ) ) {
			$this->settings = $this->defaults;
			$this->set_setting( WP_SMUSH_PREFIX . 'settings', $this->settings );
		} else {
			$this->settings = wp_parse_args( $site_settings, $this->defaults );
		}
	}

	/**
	 * Checks whether the settings are applicable for the whole network/site or sitewise (multisite).
	 */
	public function is_network_enabled() {
		// If single site return true.
		if ( ! is_multisite() ) {
			return false;
		}

		// Additional check for ajax (is_network_admin() does not work in ajax calls).
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_SERVER['HTTP_REFERER'] ) && preg_match( '#^' . network_admin_url() . '#i', wp_unslash( $_SERVER['HTTP_REFERER'] ) ) ) { // Input var ok.
			return true;
		}

		// Get directly from db.
		$network_enabled = get_site_option( WP_SMUSH_PREFIX . 'networkwide' );
		if ( ! isset( $network_enabled ) || false === (bool) $network_enabled ) {
			return true;
		}

		if ( '1' === $network_enabled || true === $network_enabled ) {
			return false;
		}

		// Partial enabled.
		return $network_enabled;
	}

	/**
	 * Check if user is able to access the page.
	 *
	 * @since 3.2.2
	 *
	 * @param string|bool $module    Check if a specific module is allowed.
	 * @param bool        $top_menu  Is this a top level menu point? Defaults to a Smush sub page.
	 *
	 * @return bool|array  Can access page or not. If custom access rules defined - return custom rules array.
	 */
	public static function can_access( $module = false, $top_menu = false ) {
		// Allow all access on single site installs.
		if ( ! is_multisite() ) {
			return true;
		}

		$access = get_site_option( WP_SMUSH_PREFIX . 'networkwide' );

		// Check to if the settings update is network-wide or not ( only if in network admin ).
		$action = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING );

		$is_network_admin = is_network_admin() || 'save_settings' === $action;

		// Additional check for ajax (is_network_admin() does not work in ajax calls).
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_SERVER['HTTP_REFERER'] ) && preg_match( '#^' . network_admin_url() . '#i', wp_unslash( $_SERVER['HTTP_REFERER'] ) ) ) { // Input var ok.
			$is_network_admin = true;
		}

		if ( $is_network_admin && ! $access && $top_menu ) {
			return true;
		}

		if ( current_user_can( 'manage_options' ) && ( '1' === $access || 'custom' === $access && $top_menu ) ) {
			return true;
		}

		if ( is_array( $access ) && current_user_can( 'manage_options' ) ) {
			if ( ! $module ) {
				return $access;
			}

			if ( $is_network_admin && ! in_array( $module, $access, true ) ) {
				return true;
			} elseif ( ! $is_network_admin && in_array( $module, $access, true ) ) {
				return true;
			}

			return false;
		}

		return false;
	}

	/**
	 * Getter method for $settings.
	 *
	 * @since 3.0
	 *
	 * @param string $setting Setting to get. Default: get all settings.
	 *
	 * @return array|bool  Return either a setting value or array of settings.
	 */
	public function get( $setting = '' ) {
		$settings = $this->settings;

		if ( ! empty( $setting ) ) {
			return isset( $settings[ $setting ] ) ? $settings[ $setting ] : false;
		}

		return $settings;
	}

	/**
	 * Setter method for $settings.
	 *
	 * @since 3.0
	 *
	 * @param string $setting  Setting to update.
	 * @param bool   $value    Value to set. Default: false.
	 */
	public function set( $setting = '', $value = false ) {
		if ( empty( $setting ) ) {
			return;
		}

		$this->settings[ $setting ] = $value;

		$this->set_setting( WP_SMUSH_PREFIX . 'settings', $this->settings );
	}

	/**
	 * Get all Smush settings, based on if network settings are enabled or not.
	 *
	 * @param string $name     Setting to fetch.
	 * @param mixed  $default  Default value.
	 *
	 * @return bool|mixed
	 */
	public function get_setting( $name = '', $default = false ) {
		if ( empty( $name ) ) {
			return false;
		}

		$global = $this->is_network_enabled();

		if ( $global && ! is_array( $global ) ) {
			return get_site_option( $name, $default );
		}

		// Fallback to network settings.
		$settings = get_option( $name, $default );

		// TODO: this fallback is dangerous! Make sure that a proper false option is not replaced.
		return $settings ? $settings : get_site_option( $name, $default );
	}

	/**
	 * Update value for given setting key
	 *
	 * @param string $name   Key.
	 * @param mixed  $value  Value.
	 *
	 * @return bool If the setting was updated or not
	 */
	public function set_setting( $name = '', $value = '' ) {
		if ( empty( $name ) ) {
			return false;
		}

		$global = $this->is_network_enabled();

		return $global && ! is_array( $global ) ? update_site_option( $name, $value ) : update_option( $name, $value );
	}

	/**
	 * Delete the given key name.
	 *
	 * @param string $name  Key.
	 *
	 * @return bool If the setting was updated or not
	 */
	public function delete_setting( $name = '' ) {
		if ( empty( $name ) ) {
			return false;
		}

		$global = $this->is_network_enabled();

		return $global && ! is_array( $global ) ? delete_site_option( $name ) : delete_option( $name );
	}

	/**
	 * Reset settings to defaults.
	 *
	 * @since 3.2.0
	 */
	public function reset() {
		check_ajax_referer( 'wp_smush_reset' );

		if ( ! current_user_can( 'manage_options' ) ) {
			die();
		}

		delete_site_option( WP_SMUSH_PREFIX . 'networkwide' );
		delete_site_option( WP_SMUSH_PREFIX . 'hide_smush_welcome' );
		$this->delete_setting( WP_SMUSH_PREFIX . 'settings' );
		$this->delete_setting( WP_SMUSH_PREFIX . 'image_sizes' );
		$this->delete_setting( WP_SMUSH_PREFIX . 'resize_sizes' );
		$this->delete_setting( WP_SMUSH_PREFIX . 'cdn_status' );
		$this->delete_setting( WP_SMUSH_PREFIX . 'lazy_load' );
		$this->delete_setting( 'skip-smush-setup' );
		$this->delete_setting( WP_SMUSH_PREFIX . 'hide_pagespeed_suggestion' );

		wp_send_json_success();
	}

	/**
	 * Save settings.
	 *
	 * @param bool $json_response  Send a JSON response.
	 */
	public function save( $json_response = true ) {
		check_ajax_referer( 'save_wp_smush_options', 'wp_smush_options_nonce' );

		if ( ! is_user_logged_in() ) {
			return;
		}

		$pages_with_settings = array( 'bulk', 'integrations', 'cdn', 'settings', 'lazy_load', 'tools' );
		$setting_form        = isset( $_POST['setting_form'] ) ? sanitize_text_field( wp_unslash( $_POST['setting_form'] ) ) : '';

		// Continue only if form name is set.
		if ( ! in_array( $setting_form, $pages_with_settings, true ) ) {
			return;
		}

		// Store that we need not redirect again on plugin activation.
		update_site_option( WP_SMUSH_PREFIX . 'hide_smush_welcome', true );

		$settings = $this->get();

		// Delete S3 alert flag, if S3 option is disabled again.
		if ( ! isset( $_POST['wp-smush-s3'] ) && isset( $settings['integration']['s3'] ) && $settings['integration']['s3'] ) {
			delete_site_option( WP_SMUSH_PREFIX . 'hide_s3support_alert' );
		}

		$core_settings = WP_Smush::get_instance()->core()->settings;

		// Process each setting and update options.
		foreach ( $core_settings as $name => $text ) {
			// Do not update if field is not available in current form.
			if ( ! in_array( $name, $this->{$setting_form . '_fields'}, true ) ) {
				continue;
			}

			// Update the setting.
			$settings[ $name ] = filter_input( INPUT_POST, WP_SMUSH_PREFIX . $name, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
		}

		// Check to if the settings update is network-wide or not ( only if in network admin ).
		$action = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING );

		// Access control settings for multisite.
		if ( 'save_settings' === $action && 'settings' === $setting_form ) {
			$settings['networkwide'] = $this->parse_access_settings();
		}

		// Settings that are specific to a page.
		if ( 'bulk' === $setting_form ) {
			$this->parse_bulk_settings();
		}

		if ( 'cdn' === $setting_form ) {
			$this->parse_cdn_settings();
		}

		if ( 'lazy_load' === $setting_form ) {
			$this->parse_lazy_load_settings();
		}

		// Store the option in table.
		$this->set_setting( WP_SMUSH_PREFIX . 'settings', $settings );
		$this->set_setting( WP_SMUSH_PREFIX . 'settings_updated', 1 );

		if ( $json_response ) {
			wp_send_json_success();
		}
	}

	/**
	 * Parse bulk Smush specific settings.
	 *
	 * @since 3.2.0  Moved from save method.
	 */
	private function parse_bulk_settings() {
		check_ajax_referer( 'save_wp_smush_options', 'wp_smush_options_nonce' );

		// Save the selected image sizes.
		if ( empty( $_POST['wp-smush-image_sizes'] ) || ( isset( $_POST['wp-smush-auto-image-sizes'] ) && 'all' === $_POST['wp-smush-auto-image-sizes'] ) ) {
			$this->delete_setting( WP_SMUSH_PREFIX . 'image_sizes' );
		} else {
			$image_sizes = array_filter( array_map( 'sanitize_text_field', wp_unslash( $_POST['wp-smush-image_sizes'] ) ) );
			$this->set_setting( WP_SMUSH_PREFIX . 'image_sizes', $image_sizes );
		}

		// Update Resize width and height settings if set.
		$resize_sizes['width']  = isset( $_POST['wp-smush-resize_width'] ) ? intval( $_POST['wp-smush-resize_width'] ) : 0; // Input var ok.
		$resize_sizes['height'] = isset( $_POST['wp-smush-resize_height'] ) ? intval( $_POST['wp-smush-resize_height'] ) : 0; // Input var ok.

		$this->set_setting( WP_SMUSH_PREFIX . 'resize_sizes', $resize_sizes );
	}

	/**
	 * Parse CDN specific settings.
	 *
	 * @since 3.2.0  Moved from save method.
	 */
	private function parse_cdn_settings() {
		// $status = connect to CDN.
		$status = WP_Smush::get_instance()->core()->mod->cdn->status();

		if ( 'disabled' === $status ) {
			$response = WP_Smush::get_instance()->api()->enable();

			// Probably an exponential back-off.
			if ( is_wp_error( $response ) ) {
				sleep( 1 ); // This is needed so we don't trigger the 597 API response.
				$response = WP_Smush::get_instance()->api()->enable( true );
			}

			if ( ! is_wp_error( $response ) ) {
				$response = json_decode( $response['body'] );
				$this->set_setting( WP_SMUSH_PREFIX . 'cdn_status', $response->data );
			}
		}
	}

	/**
	 * Parse lazy loading specific settings.
	 *
	 * @since 3.2.0
	 */
	private function parse_lazy_load_settings() {
		$previous_settings = $this->get_setting( WP_SMUSH_PREFIX . 'lazy_load' );

		$args = array(
			'format'          => array(
				'filter' => FILTER_VALIDATE_BOOLEAN,
				'flags'  => FILTER_REQUIRE_ARRAY,
			),
			'output'          => array(
				'filter' => FILTER_VALIDATE_BOOLEAN,
				'flags'  => FILTER_REQUIRE_ARRAY,
			),
			'animation'       => array(
				'filter' => FILTER_SANITIZE_STRING,
				'flags'  => FILTER_REQUIRE_ARRAY,
			),
			'include'         => array(
				'filter' => FILTER_VALIDATE_BOOLEAN,
				'flags'  => FILTER_REQUIRE_ARRAY,
			),
			'exclude-pages'   => FILTER_SANITIZE_STRING,
			'exclude-classes' => FILTER_SANITIZE_STRING,
			'footer'          => FILTER_VALIDATE_BOOLEAN,
		);

		$settings = filter_input_array( INPUT_POST, $args );

		// Fade-in settings.
		$settings['animation']['fadein']['duration'] = 0;
		if ( isset( $settings['animation']['duration'] ) ) {
			$settings['animation']['fadein']['duration'] = absint( $settings['animation']['duration'] );
			unset( $settings['animation']['duration'] );
		}

		$settings['animation']['fadein']['delay'] = 0;
		if ( isset( $settings['animation']['delay'] ) ) {
			$settings['animation']['fadein']['delay'] = absint( $settings['animation']['delay'] );
			unset( $settings['animation']['delay'] );
		}

		/**
		 * Spinner and placeholder settings.
		 */
		$items = array( 'spinner', 'placeholder' );
		foreach ( $items as $item ) {
			$settings['animation'][ $item ]['selected'] = isset( $settings['animation'][ "{$item}-icon" ] ) ? $settings['animation'][ "{$item}-icon" ] : 1;
			unset( $settings['animation'][ "{$item}-icon" ] );

			// Custom spinners.
			if ( ! isset( $previous_settings['animation'][ $item ]['custom'] ) || ! is_array( $previous_settings['animation'][ $item ]['custom'] ) ) {
				$settings['animation'][ $item ]['custom'] = array();
			} else {
				// Remove empty values.
				$settings['animation'][ $item ]['custom'] = array_filter( $previous_settings['animation'][ $item ]['custom'] );
			}

			// Add uploaded custom spinner.
			if ( isset( $settings['animation'][ "custom-{$item}" ] ) ) {
				if ( ! empty( $settings['animation'][ "custom-{$item}" ] ) && ! in_array( $settings['animation'][ "custom-{$item}" ], $settings['animation'][ $item ]['custom'], true ) ) {
					$settings['animation'][ $item ]['custom'][] = $settings['animation'][ "custom-{$item}" ];
					$settings['animation'][ $item ]['selected'] = $settings['animation'][ "custom-{$item}" ];
				}
				unset( $settings['animation'][ "custom-{$item}" ] );
			}
		}

		// Custom color for placeholder.
		if ( ! isset( $settings['animation']['color'] ) ) {
			$settings['animation']['placeholder']['color'] = $previous_settings['animation']['placeholder']['color'];
		} else {
			$settings['animation']['placeholder']['color'] = $settings['animation']['color'];
			unset( $settings['animation']['color'] );
		}

		/**
		 * Exclusion rules.
		 */
		// Convert to array.
		if ( ! empty( $settings['exclude-pages'] ) ) {
			$settings['exclude-pages'] = preg_split( '/[\r\n\t ]+/', $settings['exclude-pages'] );
		} else {
			$settings['exclude-pages'] = array();
		}
		if ( ! empty( $settings['exclude-classes'] ) ) {
			$settings['exclude-classes'] = preg_split( '/[\r\n\t ]+/', $settings['exclude-classes'] );
		} else {
			$settings['exclude-classes'] = array();
		}

		$this->set_setting( WP_SMUSH_PREFIX . 'lazy_load', $settings );
	}

	/**
	 * Parse access control settings on multisite.
	 *
	 * @since 3.2.2
	 *
	 * @return mixed
	 */
	private function parse_access_settings() {
		$current_value = get_site_option( WP_SMUSH_PREFIX . 'networkwide' );

		$new_value = filter_input( INPUT_POST, WP_SMUSH_PREFIX . 'subsite-access', FILTER_SANITIZE_STRING );
		$access    = filter_input( INPUT_POST, WP_SMUSH_PREFIX . 'access', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY );

		if ( 'custom' === $new_value ) {
			$new_value = $access;
		}

		if ( $current_value !== $new_value ) {
			update_site_option( WP_SMUSH_PREFIX . 'networkwide', $new_value );
		}

		return $new_value;
	}

	/**
	 * Apply a default configuration to lazy loading on first activation.
	 *
	 * @since 3.2.0
	 */
	public function init_lazy_load_defaults() {
		$defaults = array(
			'format'          => array(
				'jpeg'   => true,
				'png'    => true,
				'gif'    => true,
				'svg'    => true,
				'iframe' => true,
			),
			'output'          => array(
				'content'    => true,
				'widgets'    => true,
				'thumbnails' => true,
				'gravatars'  => true,
			),
			'animation'       => array(
				'selected'    => 'fadein', // Accepts: fadein, spinner, placeholder, false.
				'fadein'      => array(
					'duration' => 400,
					'delay'    => 0,
				),
				'spinner'     => array(
					'selected' => 1,
					'custom'   => array(),
				),
				'placeholder' => array(
					'selected' => 1,
					'custom'   => array(),
					'color'    => '#F3F3F3',
				),
			),
			'include'         => array(
				'frontpage' => true,
				'home'      => true,
				'page'      => true,
				'single'    => true,
				'archive'   => true,
				'category'  => true,
				'tag'       => true,
			),
			'exclude-pages'   => array(),
			'exclude-classes' => array(),
			'footer'          => true,
		);

		$this->set_setting( WP_SMUSH_PREFIX . 'lazy_load', $defaults );
	}

}
