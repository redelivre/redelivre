<?php


/**
 * Class Forminator_Addon_Abstract
 * Extend this class to create new forminator addon / integrations
 * Any change(s) to this file is subject to:
 * - Properly Written DocBlock! (what is this, why is that, how to be like those, etc, as long as you want!)
 * - Properly Written Changelog!
 * - Properly Written Sample Usage on @see Forminator_Addon_Simple
 *
 * @since 1.1
 */
abstract class Forminator_Addon_Abstract implements Forminator_Addon_Interface {

	/**
	 * Slug will be used as identifier throughout forminator
	 * make sure its unique, else it won't be loaded
	 * or will carelessly override other addon with same slug
	 *
	 * @since 1.1
	 * @var string
	 */
	protected $_slug;

	/**
	 * Version number of the Add-On
	 * It will save on the wp options
	 * And if user updated the addon, it will try to call @see Forminator_Addon_Abstract::version_changed()
	 *
	 * @since 1.1
	 * @var string
	 */
	protected $_version;

	/**
	 * Minimum version of Forminator, that the addon will work correctly
	 *
	 * @since 1.1
	 * @var string
	 */
	protected $_min_forminator_version;

	/**
	 * Full path the the Addon.
	 *
	 * @since  1.1
	 * @var string
	 * @example: __FILE__
	 */
	protected $_full_path;

	/**
	 * URL info to of the Addon website / doc / info
	 *
	 * @since  1.1
	 * @var string
	 */
	protected $_url;

	/**
	 * Title of the addon will be used on add on list and add on setting
	 *
	 * @since  1.1
	 * @var string
	 */
	protected $_title;

	/**
	 * Short version of the addon title, will be used at small places for the addon to be displayed
	 * its optional, when its omitted it will use $_title
	 * make sure its less then 10 chars to displayed correctly, we will auto truncate it if its more than 10 chars
	 *
	 * @since  1.1
	 * @var string
	 */
	protected $_short_title;

	/**
	 * Image url that will be displayed on settings popup
	 *
	 * @since  1.1
	 * @var string
	 */
	protected $_image;

	/**
	 * Retina image url that will be displayed on settings popup
	 *
	 * @since  1.1
	 * @var string
	 */
	protected $_image_x2;

	/**
	 * Banner image url that will be displayed on integration list
	 *
	 * @since  1.7.1
	 * @var string
	 */
	protected $_banner;

	/**
	 * Retina banner image url that will be displayed on integration list
	 *
	 * @since  1.7.1
	 * @var string
	 */
	protected $_banner_x2;

	/**
	 * icon url that will be displayed on addon list
	 *
	 * @since  1.1
	 * @var string
	 */
	protected $_icon;

	/**
	 * Retina icon url that will be displayed on addon list
	 *
	 * @since  1.1
	 * @var string
	 */
	protected $_icon_x2;

	/**
	 * Addon Brief Desription, of what it does
	 *
	 * @since  1.1
	 * @var string
	 */
	protected $_description = '';

	/**
	 * Addon promotion description
	 *
	 * @since  1.7.1
	 * @var string
	 */
	protected $_promotion = '';

	/**
	 * Addon documentation link
	 *
	 * @since  1.7.1
	 * @var string
	 */
	protected $_documentation = '';

	/**
	 * Classname of form settings in string
	 * or empty if form setting not needed
	 * Must be already exist on runtime
	 *
	 * @var null|string
	 */
	protected $_form_settings = null;

	/**
	 * Classname of form hooks in string
	 * or empty if form hooks not needed
	 * Must be already exist on runtime
	 *
	 * @since  1.1
	 * @var null|string
	 */
	protected $_form_hooks = null;

	/**
	 * Classname of poll settings in string
	 * or empty if poll setting not needed
	 * Must be already exist on runtime
	 *
	 * @since  1.6.1
	 * @var null|string
	 */
	protected $_poll_settings = null;

	/**
	 * Classname of poll hooks in string
	 * or empty if poll hooks not needed
	 * Must be already exist on runtime
	 *
	 * @since  1.6.1
	 * @var null|string
	 */
	protected $_poll_hooks = null;

	/**
	 * Classname of quiz settings in string
	 * or empty if quiz setting not needed
	 * Must be already exist on runtime
	 *
	 * @since  1.6.2
	 * @var null|string
	 */
	protected $_quiz_settings = null;

	/**
	 * Classname of quiz hooks in string
	 * or empty if quiz hooks not needed
	 * Must be already exist on runtime
	 *
	 * @since  1.6.2
	 * @var null|string
	 */
	protected $_quiz_hooks = null;

	/**
	 * Flag that an addon can be activated, that auto set by abstract
	 *
	 * @since  1.1
	 * @var bool
	 */
	private $is_activable = null;

	/**
	 * Semaphore non redundant hooks for admin side
	 *
	 * @since  1.1
	 * @var bool
	 */
	private $_is_admin_hooked = false;

	/**
	 * Semaphore non redundant hooks for global hooks
	 *
	 * @since  1.1
	 * @var bool
	 */
	private $_is_global_hooked = false;

	/**
	 * Add-on order position
	 *
	 * @since 1.7.1
	 * @var int
	 */
	protected $_position = 1;


	/*********************************** Errors Messages ********************************/
	/**
	 * These error message can be set on the start of addon as default, or dynamically set on each related process
	 *
	 * @example $_activation_error_message can be dynamically set on activate() to display custom error messages when activatation failed
	 *          Default is empty, which will be replaced by forminator default messages
	 *
	 */
	/**
	 * Error Message on activation
	 *
	 * @since  1.1
	 * @var string
	 */
	protected $_activation_error_message = '';

	/**
	 * Error Message on deactivation
	 *
	 * @since  1.1
	 * @var string
	 */
	protected $_deactivation_error_message = '';

	/**
	 * Error Message on update general settings
	 *
	 * @since  1.1
	 * @var string
	 */
	protected $_update_settings_error_message = '';
	/*********************************** END Errors Messages ********************************/

	/**
	 * Form Setting Instances with `form_id` as key
	 *
	 * @since  1.1
	 * @var Forminator_Addon_Form_Settings_Abstract[]|array
	 */
	protected $_addon_form_settings_instances = array();

	/**
	 * Form Hooks Instances with `form_id` as key
	 *
	 * @since  1.1
	 * @var Forminator_Addon_Form_Hooks_Abstract[]|array
	 */
	protected $_addon_form_hooks_instances = array();

	/**
	 * Poll Setting Instances with `poll_id` as key
	 *
	 * @since  1.6.1
	 * @var Forminator_Addon_Poll_Settings_Abstract[]|array
	 */
	protected $_addon_poll_settings_instances = array();

	/**
	 * Poll Hooks Instances with `poll_id` as key
	 *
	 * @since  1.6.1
	 * @var Forminator_Addon_Poll_Hooks_Abstract[]|array
	 */
	protected $_addon_poll_hooks_instances = array();

	/**
	 * Quiz Setting Instances with `quiz_id` as key
	 *
	 * @since  1.6.1
	 * @var Forminator_Addon_Quiz_Settings_Abstract[]|array
	 */
	protected $_addon_quiz_settings_instances = array();

	/**
	 * Quiz Hooks Instances with `quiz_id` as key
	 *
	 * @since  1.6.1
	 * @var Forminator_Addon_Quiz_Hooks_Abstract[]|array
	 */
	protected $_addon_quiz_hooks_instances = array();

	/**
	 * Get this addon slug
	 *
	 * @see    Forminator_Addon_Abstract::$_slug
	 *
	 * its behave like `IDENTIFIER`, used for :
	 * - easly calling this instance with @see forminator_get_addon(`slug`)
	 * - avoid collision, registered as FIFO of @see do_action()
	 *
	 * Shouldn't be implemented / overridden on addons
	 *
	 * @since  1.1
	 * @return string
	 */
	final public function get_slug() {
		return $this->_slug;
	}

	/**
	 * Get this addon version
	 *
	 * @since  1.1
	 * @return string
	 */
	final public function get_version() {
		return $this->_version;
	}

	/**
	 * Get this addon requirement of installed forminator version
	 *
	 * @since  1.1
	 * @return string
	 */
	final public function get_min_forminator_version() {
		return $this->_min_forminator_version;
	}

	/**
	 * Get Full Path of addon class file
	 *
	 * @since  1.1
	 *
	 * @return string
	 */
	final public function get_full_path() {
		return $this->_full_path;
	}

	/**
	 * Get external url of addon website / info / doc
	 *
	 * Can be overridden to offer dynamic external url display
	 *
	 * @since  1.1
	 * @return string
	 */
	public function get_url() {
		return $this->_url;
	}

	/**
	 * Get external title of addon
	 *
	 * @since  1.1
	 * @return string
	 */
	final public function get_title() {
		return $this->_title;
	}


	/**
	 * Get short title for small width placeholder
	 *
	 * @since  1.1
	 * @return string
	 */
	final public function get_short_title() {
		if ( empty( $this->_short_title ) ) {
			$this->_short_title = $this->_title;
		}

		return substr( $this->_short_title, 0, self::SHORT_TITLE_MAX_LENGTH );
	}

	/**
	 * Get Image
	 *
	 * @since  1.1
	 * @return string
	 */
	public function get_image() {
		return $this->_image;
	}

	/**
	 * Get Retina image
	 *
	 * @since  1.1
	 * @return string
	 */
	public function get_image_x2() {
		return $this->_image_x2;
	}

	/**
	 * Get banner image
	 *
	 * @since  1.7.1
	 * @return string
	 */
	public function get_banner() {
		return $this->_banner;
	}

	/**
	 * Get retina banner image
	 *
	 * @since  1.7.1
	 * @return string
	 */
	public function get_banner_x2() {
		return $this->_banner_x2;
	}

	/**
	 * Get icon
	 *
	 * @since  1.1
	 * @return string
	 */
	public function get_icon() {
		return $this->_icon;
	}

	/**
	 * Get Retina icon
	 *
	 * @since  1.1
	 * @return string
	 */
	public function get_icon_x2() {
		return $this->_icon_x2;
	}

	/**
	 * Get Description
	 *
	 * @since  1.1
	 * @return string
	 */
	public function get_description() {
		return $this->_description;
	}

	/**
	 * Get promotion
	 *
	 * @since  1.7.1
	 * @return string
	 */
	public function get_promotion() {
		return $this->_promotion;
	}

	/**
	 * Get documentation link
	 *
	 * @since  1.7.1
	 * @return string
	 */
	public function get_documentation() {
		return $this->_documentation;
	}

	/**
	 * Get add-on position
	 *
	 * @since 1.7.1
	 * @return int
	 */
	public function get_position() {
		return $this->_position;
	}

	/**
	 * WP options name that holds settings of addon
	 *
	 * @since  1.1
	 * @return string
	 */
	final public function get_settings_options_name() {
		$addon_slug            = $this->get_slug();
		$addon                 = $this;
		$settings_options_name = 'forminator_addon_' . $this->get_slug() . '_settings';

		/**
		 * Filter wp options name for saving addon settings
		 *
		 * @since 1.1
		 *
		 * @param string                    $settings_options_name
		 * @param Forminator_Addon_Abstract $addon Addon instance
		 */
		$settings_options_name = apply_filters( 'forminator_addon_' . $addon_slug . '_settings_options_name', $settings_options_name, $addon );

		return $settings_options_name;
	}

	/**
	 * WP options name that holds current version of addon
	 *
	 * @since  1.1
	 * @return string
	 */
	final public function get_version_options_name() {
		$addon_slug           = $this->get_slug();
		$addon                = $this;
		$version_options_name = 'forminator_addon_' . $this->get_slug() . '_version';

		/**
		 * Filter wp options name for saving addon version
		 *
		 * @since 1.1
		 *
		 * @param string                    $version_options_name
		 * @param Forminator_Addon_Abstract $addon Addon instance
		 */
		$version_options_name = apply_filters( 'forminator_addon_' . $addon_slug . '_version_options_name', $version_options_name, $addon );

		return $version_options_name;
	}

	/**
	 * Transform addon instance into array
	 *
	 * @since  1.1
	 * @return array
	 */
	final public function to_array() {
		$to_array = array(
			'slug'                   => $this->get_slug(),
			'is_pro'                 => $this->is_pro(),
			'icon'                   => $this->get_icon(),
			'icon_x2'                => $this->get_icon_x2(),
			'image'                  => $this->get_image(),
			'image_x2'               => $this->get_image_x2(),
			'banner'                 => $this->get_banner(),
			'banner_x2'              => $this->get_banner_x2(),
			'short_title'            => $this->get_short_title(),
			'title'                  => $this->get_title(),
			'url'                    => $this->get_url(),
			'description'            => $this->get_description(),
			'promotion'              => $this->get_promotion(),
			'documentation'          => $this->get_documentation(),
			'version'                => $this->get_version(),
			'min_forminator_version' => $this->get_min_forminator_version(),
			'setting_options_name'   => $this->get_settings_options_name(),
			'version_option_name'    => $this->get_version_options_name(),
			'is_activable'           => $this->is_activable(),
			'is_settings_available'  => $this->is_settings_available(),
			'is_connected'           => $this->is_connected(),
			'position'               => $this->get_position(),
		);

		$addon_slug = $this->get_slug();
		$addon      = $this;

		/**
		 * Filter array of addon properties
		 *
		 * @since 1.1
		 *
		 * @param array                     $to_array array of addonn properties
		 * @param int                       $form_id  Form ID
		 * @param Forminator_Addon_Abstract $addon    Addon Instance
		 */
		$to_array = apply_filters( 'forminator_addon_' . $addon_slug . '_to_array', $to_array, $addon );

		return $to_array;
	}

	/**
	 * Transform addon instance into array with form relation
	 *
	 * @since  1.1
	 * @since  1.2 generate new multi_id to allow reference on wizard
	 *
	 * @param $form_id
	 *
	 * @return array
	 */
	final public function to_array_with_form( $form_id ) {
		$to_array                               = $this->to_array();
		$is_allow_multi_on_form                 = $this->is_allow_multi_on_form();
		$to_array['is_form_connected']          = $this->is_form_connected( $form_id );
		$to_array['is_form_settings_available'] = $this->is_form_settings_available( $form_id );
		$to_array['is_allow_multi_on_form']     = $is_allow_multi_on_form;

		$to_array['multi_id'] = $this->generate_form_settings_multi_id( $form_id );

		// handle multiple form setting
		if ( $is_allow_multi_on_form ) {
			$to_array['multi_ids'] = $this->get_form_settings_multi_ids( $form_id );
		}

		$to_array_with_form = $to_array;
		$addon_slug         = $this->get_slug();
		$addon              = $this;

		/**
		 * Filter array of addon properties
		 *
		 * Including relation with form_id
		 *
		 * @since 1.1
		 *
		 * @param array                     $to_array_with_form array of addonn properties
		 * @param int                       $form_id            Form ID
		 * @param Forminator_Addon_Abstract $addon              Addon instance
		 */
		$to_array_with_form = apply_filters( 'forminator_addon_' . $addon_slug . '_to_array_with_form', $to_array_with_form, $form_id, $addon );

		return $to_array_with_form;
	}


	/**
	 * Check if Plugin Is Pro
	 *
	 * @see    forminator_get_pro_addon_list()
	 * @since  1.1
	 * @return bool
	 */
	final public function is_pro() {
		if ( in_array( $this->_slug, array_keys( forminator_get_pro_addon_list() ), true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get activable status
	 *
	 * @since  1.1
	 * @return bool
	 */
	final public function is_activable() {
		if ( is_null( $this->is_activable ) ) {
			$this->is_activable = $this->check_is_activable();
		}

		return $this->is_activable;
	}

	/**
	 * Actually check requirement of an addon that can be activated
	 * Override this method if you have another logic for checking activable_plugins
	 *
	 * @since  1.1
	 * @return bool
	 */
	public function check_is_activable() {
		if ( ! file_exists( $this->get_full_path() ) ) {
			forminator_addon_maybe_log( __METHOD__, $this->get_slug(), $this->get_full_path(), 'NOT Exist' );

			return false;
		}

		// Check supported forminator version
		if ( empty( $this->_min_forminator_version ) ) {
			forminator_addon_maybe_log( __METHOD__, $this->get_slug(), 'empty _min_forminator_version' );

			return false;
		}

		$is_forminator_version_supported = version_compare( FORMINATOR_VERSION, $this->_min_forminator_version, '>=' );
		if ( ! $is_forminator_version_supported ) {
			forminator_addon_maybe_log( __METHOD__, $this->get_slug(), $this->_min_forminator_version, FORMINATOR_VERSION, 'Forminator Version not supported' );

			// un-strict version compare of forminator, override if needed
			return true;
		}

		return true;
	}

	/**
	 * Override or implement this method to add action when user deactivate addon
	 *
	 * @example DROP table
	 * return true when succes
	 * return false on failure, forminator will stop deactivate process
	 *
	 * @since   1.1
	 * @return bool
	 */
	public function deactivate() {
		return true;
	}


	/**
	 * Override or implement this method to add action when user activate addon
	 *
	 * @example CREATE table
	 * return true when succes
	 * return false on failure, forminator will stop activation process
	 *
	 * @since   1.1
	 * @return bool
	 */
	public function activate() {
		return true;
	}

	/**
	 * Override or implement this method to add action when version of addon changed
	 *
	 * @example CREATE table
	 * return true when succes
	 * return false on failure, forminator will stop activation process
	 *
	 * @since   1.1
	 *
	 * @param $old_version
	 * @param $new_version
	 *
	 * @return bool
	 */
	public function version_changed( $old_version, $new_version ) {
		return true;
	}

	/**
	 * Check if addon version has changed
	 *
	 * @since   1.1
	 * @return bool
	 */
	final public function is_version_changed() {
		$installed_version = $this->get_installed_version();
		// new installed
		if ( false === $installed_version ) {
			return false;
		}
		$version_is_changed = version_compare( $this->_version, $installed_version, '!=' );
		if ( $version_is_changed ) {
			return true;
		}

		return false;
	}

	/**
	 * Get currently installed addon version
	 * retrieved from wp options
	 *
	 * @since 1.1
	 * @return string|bool
	 */
	final public function get_installed_version() {
		return get_option( $this->get_version_options_name(), false );
	}

	/**
	 * Get error message on activation
	 *
	 * @since 1.1
	 * @return string
	 */
	public function get_activation_error_message() {
		return $this->_activation_error_message;
	}

	/**
	 * Get error message on deactivation
	 *
	 * @since 1.1
	 * @return string
	 */
	public function get_deactivation_error_message() {
		return $this->_deactivation_error_message;
	}

	/**
	 * Get error message on deactivation
	 *
	 * @since 1.1
	 * @return string
	 */
	public function get_update_settings_error_message() {
		return $this->_update_settings_error_message;
	}


	/**
	 * Override this function to set wizardable settings
	 * Default its and empty array which is indicating that Addon doesnt have settings
	 *
	 * Its multi array, with numerical key, start with `0`
	 * Every step on wizard, will consist at least
	 * - `callback` : when application requesting wizard, Forminator will do `call_user_func` on this value, with these arguments
	 *      - `$submitted_data` : array of submitted data POST-ed by user
	 *      - `$form_id` : current form_id when called on `Form Settings` or 0 when called on Global Settings
	 * - `is_completed` : when application requesting wizard, will check if `Previous Step` `is_completed` by doing `call_user_func` on its value
	 *      this function should return `true` or `false`
	 *
	 * @since 1.1
	 * @return array
	 */
	public function settings_wizards() {
		// What this function return should looks like
		$steps = array(
			// First Step / step `0`
			array(
				/**
				 * Value of `callback` will be passed as first argument of `call_user_func`
				 * it does not have to be passed `$this` as reference such as `array( $this, 'sample_setting_first_step' )`,
				 * But its encouraged to passed `$this` because you will be benefited with $this class instance, in case you need to call private function or variable inside it
				 * you can make the value to be `some_function_name` as long `some_function_name` as long it will globally callable which will be checked with `is_callable`
				 * and should be able to accept 2 arguments $submitted_data, $form_id
				 *
				 * This callback should return an array @see Forminator_Addon_Abstract::sample_setting_first_step()
				 *
				 * @see Forminator_Addon_Abstract::sample_setting_first_step()
				 *
				 */
				'callback'     => array( $this, 'sample_setting_first_step' ),
				/**
				 * Before Forminator call the `calback`, Forminator will attempt to run `is_completed` from the previous step
				 * In this case, `is_completed` will be called when Forminator trying to display Settings Wizard for Second Step / step `1`
				 * Like `callback` its value will be passed as first argument of `call_user_func`
				 * and no arguments passed to this function when its called
				 *
				 * @see Forminator_Addon_Abstract::sample_setting_first_step_is_completed()
				 */
				'is_completed' => array( $this, 'sample_setting_first_step_is_completed' ),
			),
		);

		return array();
	}

	/**
	 * Get Global Setting Wizard
	 * This function will process @see Forminator_Addon_Abstract::settings_wizards()
	 * Please keep in mind this function will only be called when @see Forminator_Addon_Abstract::is_settings_available() return `true`
	 * Which is doing check on @see Forminator_Addon_Abstract::settings_wizards() requirements is passed
	 *
	 * @since 1.1
	 *
	 * @param     $submitted_data
	 * @param int $form_id
	 * @param int $current_step
	 * @param int $step
	 *
	 * @return array|mixed
	 */
	final public function get_settings_wizard( $submitted_data, $form_id = 0, $current_step = 0, $step = 0 ) {

		$steps = $this->settings_wizards();
		if ( ! is_array( $steps ) ) {
			/* translators: ... */
			return $this->get_empty_wizard( sprintf( __( 'No Settings available for %1$s', Forminator::DOMAIN ), $this->get_title() ) );
		}
		$total_steps = count( $steps );
		if ( $total_steps < 1 ) {
			/* translators: ... */
			return $this->get_empty_wizard( sprintf( __( 'No Settings available for %1$s', Forminator::DOMAIN ), $this->get_title() ) );
		}

		if ( ! isset( $steps[ $step ] ) ) {
			// go to last step
			$step = $total_steps - 1;

			return $this->get_settings_wizard( $submitted_data, $form_id, $current_step, $step );
		}

		if ( $step > 0 ) {
			if ( $current_step > 0 ) {
				//check previous step is complete
				$prev_step              = $current_step - 1;
				$prev_step_is_completed = true;
				// only call `is_completed` when its defined
				if ( isset( $steps[ $prev_step ]['is_completed'] ) && is_callable( $steps[ $prev_step ]['is_completed'] ) ) {
					$prev_step_is_completed = call_user_func( $steps[ $prev_step ]['is_completed'], $submitted_data );
				}
				if ( ! $prev_step_is_completed ) {
					$step --;

					return $this->get_settings_wizard( $submitted_data, $form_id, $current_step, $step );
				}
			}

			// only validation when it moves forward
			if ( $step > $current_step ) {
				$current_step_result = $this->get_settings_wizard( $submitted_data, $form_id, $current_step, $current_step );
				if ( isset( $current_step_result['has_errors'] ) && true === $current_step_result['has_errors'] ) {
					return $current_step_result;
				} else {
					//set empty submitted data for next step
					$submitted_data = array();
				}
			}
		}

		return $this->get_wizard( $steps, $submitted_data, $form_id, $step );
	}

	/**
	 * Get Form Setting Wizard
	 * This function will process @see Forminator_Addon_Abstract::settings_wizards()
	 * Please keep in mind this function will only be called when @see Forminator_Addon_Abstract::is_settings_available() return `true`
	 * Which is doing check on @see Forminator_Addon_Abstract::settings_wizards() requirements is passed
	 *
	 * @since 1.1
	 *
	 * @param     $submitted_data
	 * @param int $form_id
	 * @param int $current_step
	 * @param int $step
	 *
	 * @return array|mixed
	 */
	final public function get_form_settings_wizard( $submitted_data, $form_id, $current_step = 0, $step = 0 ) {

		$settings_steps = array();
		if ( ! $this->is_connected() ) {
			$settings_steps = $this->settings_wizards();
		}

		$form_settings_steps = $this->get_form_settings_steps( $form_id );

		$steps = array_merge( $settings_steps, $form_settings_steps );

		if ( ! is_array( $steps ) ) {
			/* translators: ... */
			return $this->get_empty_wizard( sprintf( __( 'No Form Settings available for %1$s', Forminator::DOMAIN ), $this->get_title() ) );
		}
		$total_steps = count( $steps );
		if ( $total_steps < 1 ) {
			/* translators: ... */
			return $this->get_empty_wizard( sprintf( __( 'No Form Settings available for %1$s', Forminator::DOMAIN ), $this->get_title() ) );
		}

		if ( ! isset( $steps[ $step ] ) ) {
			// go to last step
			$step = $total_steps - 1;

			return $this->get_form_settings_wizard( $submitted_data, $form_id, $current_step, $step );
		}

		if ( $step > 0 ) {
			if ( $current_step > 0 ) {
				//check previous step is complete
				$prev_step              = $current_step - 1;
				$prev_step_is_completed = true;
				// only call `is_completed` when its defined
				if ( isset( $steps[ $prev_step ]['is_completed'] ) && is_callable( $steps[ $prev_step ]['is_completed'] ) ) {
					$prev_step_is_completed = call_user_func( $steps[ $prev_step ]['is_completed'], $submitted_data );
				}
				if ( ! $prev_step_is_completed ) {
					$step --;

					return $this->get_form_settings_wizard( $submitted_data, $form_id, $current_step, $step );
				}
			}

			// only validation when it moves forward
			if ( $step > $current_step ) {
				$current_step_result = $this->get_form_settings_wizard( $submitted_data, $form_id, $current_step, $current_step );
				if ( isset( $current_step_result['has_errors'] ) && true === $current_step_result['has_errors'] ) {
					return $current_step_result;
				} else {
					//set empty submitted data for next step, except preserved as reference
					$preserved_keys = array(
						'multi_id',
					);
					foreach ( $submitted_data as $key => $value ) {
						if ( ! in_array( $key, $preserved_keys, true ) ) {
							unset( $submitted_data[ $key ] );
						}
					}
				}
			}
		}

		$form_settings_wizard = $this->get_wizard( $steps, $submitted_data, $form_id, $step );

		$addon_slug             = $this->get_slug();
		$addon                  = $this;
		$form_settings_instance = $this->get_addon_form_settings( $form_id );

		/**
		 * Filter form settings wizard returned to client
		 *
		 * @since 1.1
		 *
		 * @param array                                        $form_settings_wizard
		 * @param array                                        $submitted_data         $_POST from client
		 * @param int                                          $form_id                Form ID requested for
		 * @param int                                          $current_step           Current Step displayed to user, start from 0
		 * @param int                                          $step                   Step requested by client, start from 0
		 * @param Forminator_Addon_Abstract                    $addon                  Addon Instance
		 * @param Forminator_Addon_Form_Settings_Abstract|null $form_settings_instance Addon Form settings instancce, or null if unavailable
		 */
		$form_settings_wizard = apply_filters(
			'forminator_addon_' . $addon_slug . '_form_settings_wizard',
			$form_settings_wizard,
			$submitted_data,
			$form_id,
			$current_step,
			$step,
			$addon,
			$form_settings_instance
		);

		return $form_settings_wizard;
	}

	/**
	 * Get form settings wizard steps
	 *
	 * @since 1.1
	 *
	 * @param $form_id
	 *
	 * @return array
	 */
	private function get_form_settings_steps( $form_id ) {
		$addon_slug             = $this->get_slug();
		$addon                  = $this;
		$form_settings_steps    = array();
		$form_settings_instance = $this->get_addon_form_settings( $form_id );
		if ( ! is_null( $form_settings_instance ) && $form_settings_instance instanceof Forminator_Addon_Form_Settings_Abstract ) {
			$form_settings_steps = $form_settings_instance->form_settings_wizards();
		}

		/**
		 * Filter form settings step that will be used for building wizard
		 *
		 * More detail : @see Forminator_Addon_Form_Settings_Abstract::form_settings_wizards()
		 *
		 * @since 1.1
		 *
		 * @param array                                   $form_settings_steps
		 * @param int                                     $form_id current form id
		 * @param Forminator_Addon_Form_Settings_Abstract $addon   Addon instance
		 * @param Forminator_Addon_Form_Settings_Abstract|null Form settings of addon if available, or null otherwise
		 */
		$form_settings_steps = apply_filters( 'forminator_addon_' . $addon_slug . '_form_settings_steps', $form_settings_steps, $form_id, $addon, $form_settings_instance );

		return $form_settings_steps;
	}

	/**
	 * Get settings multi id
	 *
	 * @since 1.1
	 *
	 * @param $form_id
	 *
	 * @return array
	 */
	private function get_form_settings_multi_ids( $form_id ) {
		$addon_slug             = $this->get_slug();
		$addon                  = $this;
		$multi_ids              = array();
		$form_settings_instance = $this->get_addon_form_settings( $form_id );
		if ( $this->is_allow_multi_on_form() && ! is_null( $form_settings_instance ) && $form_settings_instance instanceof Forminator_Addon_Form_Settings_Abstract ) {
			$multi_ids = $form_settings_instance->get_multi_ids();
		}

		/**
		 * Filter multi id of addon form settings
		 *
		 * @since 1.1
		 *
		 * @param array                                   $multi_ids
		 * @param Forminator_Addon_Form_Settings_Abstract $addon                  Addon Instance
		 * @param Forminator_Addon_Form_Settings_Abstract $form_settings_instance Addon Form Settings Instance
		 */
		$multi_ids = apply_filters( 'forminator_addon_' . $addon_slug . '_form_settings_multi_ids', $multi_ids, $addon, $form_settings_instance );

		return $multi_ids;
	}

	/**
	 * Get the requested wizard
	 *
	 * @since 1.1
	 * @since 1.2 Refactor setup default values, rename `hasBack` to `has_back`
	 *
	 * @param     $steps
	 * @param     $submitted_data
	 * @param     $module_id
	 * @param int $step
	 *
	 * @return array|mixed
	 */
	private function get_wizard( $steps, $submitted_data, $module_id, $step = 0 ) {
		$total_steps = count( $steps );

		// validate callback, when its empty or not callable, mark as no wizard
		if ( ! isset( $steps[ $step ]['callback'] ) || ! is_callable( $steps[ $step ]['callback'] ) ) {
			/* translators: ... */
			return $this->get_empty_wizard( sprintf( __( 'No Settings available for %1$s', Forminator::DOMAIN ), $this->get_title() ) );
		}

		$wizard = call_user_func( $steps[ $step ]['callback'], $submitted_data, $module_id );
		// a wizard to be able to processed by our application need to has at least `html` which will be rendered or `redirect` which will be the url for redirect user to go to
		if ( ! isset( $wizard['html'] ) && ! isset( $wizard['redirect'] ) ) {
			/* translators: ... */
			return $this->get_empty_wizard( sprintf( __( 'No Settings available for %1$s', Forminator::DOMAIN ), $this->get_title() ) );
		}
		$wizard['forminator_addon_current_step']  = $step;
		$wizard['forminator_addon_count_step']    = $total_steps;
		$wizard['forminator_addon_has_next_step'] = ( ( $step + 1 ) >= $total_steps ? false : true );
		$wizard['forminator_addon_has_prev_step'] = ( $step > 0 ? true : false );

		$wizard_default_values = array(
			'has_errors'   => false,
			'is_close'     => false,
			'notification' => array(),
			'size'         => 'small',
			'has_back'     => false,
			'is_poll'      => false,
		);

		foreach ( $wizard_default_values as $key => $wizard_default_value ) {
			if ( ! isset( $wizard[ $key ] ) ) {
				$wizard[ $key ] = $wizard_default_value;
			}
		}

		$addon_slug = $this->get_slug();
		$addon      = $this;

		/**
		 * Filter returned setting wizard to client
		 *
		 * @since 1.1
		 *
		 * @param array                     $wizard         current wizard
		 * @param Forminator_Addon_Abstract $addon          current addon instance
		 * @param array                     $steps          defined settings / form settings steps
		 * @param array                     $submitted_data $_POST
		 * @param int                       $module_id      current form_id
		 * @param int                       $step           requested step
		 */
		$wizard = apply_filters( 'forminator_addon_' . $addon_slug . '_wizard', $wizard, $addon, $steps, $submitted_data, $module_id, $step );

		return $wizard;
	}

	/**
	 * Get Empty wizard markup
	 *
	 * @since   1.1
	 *
	 * @param $notice
	 *
	 * @return array
	 */
	protected function get_empty_wizard( $notice ) {

		$empty_wizard_html = '<span class="sui-notice sui-notice-error"><p>' . esc_html( $notice ) . '</p></span>';

		/**
		 * Filter html markup for empty wizard
		 *
		 * @since 1.1
		 *
		 * @param string $empty_wizard_html
		 * @param string $notice notice or message to be displayed on empty wizard
		 */
		$empty_wizard_html = apply_filters( 'forminator_addon_empty_wizard_html', $empty_wizard_html, $notice );

		return array(
			'html'    => $empty_wizard_html,
			'buttons' => array(
				'close' => array(
					'action' => 'close',
					'data'   => array(),
					'markup' => self::get_button_markup( esc_html__( 'Close', Forminator::DOMAIN ), 'sui-button-ghost forminator-addon-close' ),
				),
			),
		);
	}


	/**
	 * Override this function if addon need to do something with addon setting values
	 *
	 * @example transform, load from other storage ?
	 * called when rendering settings form
	 *
	 * @since   1.1
	 *
	 * @param $values
	 *
	 * @return mixed
	 */
	public function before_get_settings_values( $values ) {
		return $values;
	}

	/**
	 * Get settings value
	 * its already hooked with
	 *
	 * @see     before_get_settings_values
	 *
	 * @since   1.1
	 * @return array
	 */
	final public function get_settings_values() {
		$values = get_option( $this->get_settings_options_name(), array() );

		$addon_slug = $this->get_slug();

		/**
		 * Filter retrieved saved addon's settings values from db
		 *
		 * @since 1.1
		 *
		 * @param mixed $values
		 */
		$values = apply_filters( 'forminator_addon_' . $addon_slug . '_get_settings_values', $values );

		return $values;
	}

	/**
	 * Override this function if addon need to do something with addon setting values
	 *
	 * @example transform, save to other storage ?
	 * Called before settings values saved to db
	 *
	 * @since   1.1
	 *
	 * @param $values
	 *
	 * @return mixed
	 */
	public function before_save_settings_values( $values ) {
		return $values;
	}

	/**
	 * Save settings value
	 * its already hooked with
	 *
	 * @see     before_save_settings_values
	 *
	 * @since   1.1
	 *
	 * @param $values
	 */
	final public function save_settings_values( $values ) {
		$addon_slug = $this->get_slug();

		/**
		 * Filter settings values of addon to be saved
		 *
		 * `$addon_slug` is current slug of addon that will on save.
		 * Example : `malchimp`, `zapier`, `etc`
		 *
		 * @since 1.1
		 *
		 * @param mixed $values
		 */
		$values = apply_filters( 'forminator_addon_' . $addon_slug . '_save_settings_values', $values );
		update_option( $this->get_settings_options_name(), $values );
	}

	/**
	 * Auto Attach Default Admin hooks for addon
	 *
	 * @since   1.1
	 * @return bool
	 */
	final public function admin_hookable() {
		if ( $this->_is_admin_hooked ) {
			return true;
		}
		$default_filters = array(
			'forminator_addon_' . $this->get_slug() . '_save_settings_values' => array( array( $this, 'before_save_settings_values' ), 1 ),
		);

		if ( $this->is_connected() ) {
			$default_filters[ 'forminator_addon_' . $this->get_slug() . '_save_form_settings_values' ] = array( array( $this, 'before_save_form_settings_values' ), 2 );
			$default_filters[ 'forminator_addon_' . $this->get_slug() . '_save_poll_settings_values' ] = array( array( $this, 'before_save_poll_settings_values' ), 2 );
			$default_filters[ 'forminator_addon_' . $this->get_slug() . '_save_quiz_settings_values' ] = array( array( $this, 'before_save_quiz_settings_values' ), 2 );
		}

		foreach ( $default_filters as $filter => $default_filter ) {
			$function_to_add = $default_filter[0];
			if ( is_callable( $function_to_add ) ) {
				$accepted_args = $default_filter[1];
				add_filter( $filter, $function_to_add, 10, $accepted_args );
			}
		}
		$this->_is_admin_hooked = true;

		return true;
	}

	/**
	 * Maintain hooks all pages for addons
	 *
	 * @since   1.1
	 * @return bool
	 */
	final public function global_hookable() {
		if ( $this->_is_global_hooked ) {
			return true;
		}

		$default_filters = array(
			'forminator_addon_' . $this->get_slug() . '_get_settings_values' => array( array( $this, 'before_get_settings_values' ), 1 ),
		);

		if ( $this->is_connected() ) {
			$default_filters[ 'forminator_addon_' . $this->get_slug() . '_get_form_settings_values' ] = array( array( $this, 'before_get_form_settings_values' ), 2 );
			$default_filters[ 'forminator_addon_' . $this->get_slug() . '_get_poll_settings_values' ] = array( array( $this, 'before_get_poll_settings_values' ), 2 );
			$default_filters[ 'forminator_addon_' . $this->get_slug() . '_get_quiz_settings_values' ] = array( array( $this, 'before_get_quiz_settings_values' ), 2 );
		}

		foreach ( $default_filters as $filter => $default_filter ) {
			$function_to_add = $default_filter[0];
			if ( is_callable( $function_to_add ) ) {
				$accepted_args = $default_filter[1];
				add_filter( $filter, $function_to_add, 10, $accepted_args );
			}
		}
		$this->_is_global_hooked = true;

		return true;
	}

	/**
	 * Override this function if you need to apply some conditional logic on it
	 * By Default this function will only check @see Forminator_Addon_Abstract::settings_wizards() as valid multi array
	 *
	 * @since 1.1
	 * @return bool
	 */
	public function is_settings_available() {
		$steps = $this->settings_wizards();
		if ( ! is_array( $steps ) ) {
			return false;
		}

		if ( count( $steps ) < 1 ) {
			return false;
		}

		return true;
	}

	/**
	 * Override this function if you need to apply some conditional logic on it
	 * By Default this function will check
	 *
	 * @see     Forminator_Addon_Abstract::settings_wizards()
	 * @see     Forminator_Addon_Form_Settings_Abstract::form_settings_wizards()
	 * as valid multi array
	 *
	 * @since   1.1
	 *
	 * @param $form_id
	 *
	 * @return bool
	 */
	public function is_form_settings_available( $form_id ) {
		$steps      = $this->settings_wizards();
		$form_steps = $this->get_form_settings_steps( $form_id );

		$steps = array_merge( $steps, $form_steps );
		if ( ! is_array( $steps ) ) {
			return false;
		}

		if ( count( $steps ) < 1 ) {
			return false;
		}

		return true;
	}

	/**
	 * Flag for check if and addon connected (global settings such as api key complete)
	 *
	 * Please apply necessary WordPress hook on the inheritance class
	 *
	 * @since   1.1
	 * @return boolean
	 */
	abstract public function is_connected();

	/**
	 * Flag for check if and addon connected to a form(form settings such as list id completed)
	 *
	 * Please apply necessary WordPress hook on the inheritance class
	 *
	 * @since   1.1
	 *
	 * @param $form_id
	 *
	 * @return boolean
	 */
	abstract public function is_form_connected( $form_id );

	/**
	 * Check if this addon on active
	 *
	 * @since   1.1
	 * @return bool
	 */
	final public function is_active() {
		return forminator_addon_is_active( $this->get_slug() );
	}

	/**
	 * Get ClassName of addon Form Settings
	 *
	 * @see   Forminator_Addon_Form_Settings_Abstract
	 *
	 * @since 1.1
	 * @return null|string
	 */
	final public function get_form_settings_class_name() {
		$addon_slug               = $this->get_slug();
		$form_settings_class_name = $this->_form_settings;

		/**
		 * Filter class name of the addon form settings
		 *
		 * Form settings class name is a string
		 * it will be validated by `class_exists` and must be instanceof @see Forminator_Addon_Form_Settings_Abstract
		 *
		 * @since 1.1
		 *
		 * @param string $form_settings_class_name
		 */
		$form_settings_class_name = apply_filters( 'forminator_addon_' . $addon_slug . '_form_settings_class_name', $form_settings_class_name );

		return $form_settings_class_name;
	}

	/**
	 * Get Form Settings Instance
	 *
	 * @since   1.1
	 *
	 * @param $form_id
	 *
	 * @return Forminator_Addon_Form_Settings_Abstract | null
	 */
	final public function get_addon_form_settings( $form_id ) {
		$class_name = $this->get_form_settings_class_name();
		if ( ! isset( $this->_addon_form_settings_instances[ $form_id ] ) || ! $this->_addon_form_settings_instances[ $form_id ] instanceof Forminator_Addon_Form_Settings_Abstract ) {
			if ( empty( $class_name ) ) {
				return null;
			}

			if ( ! class_exists( $class_name ) ) {
				return null;
			}

			try {
				$form_settings_instance = new $class_name( $this, $form_id );
				if ( ! $form_settings_instance instanceof Forminator_Addon_Form_Settings_Abstract ) {
					throw new Forminator_Addon_Exception( $class_name . ' is not instanceof Forminator_Addon_Form_Settings_Abstract' );
				}
				$this->_addon_form_settings_instances[ $form_id ] = $form_settings_instance;
				forminator_maybe_attach_addon_hook( $this );
			} catch ( Exception $e ) {
				forminator_addon_maybe_log( $this->get_slug(), 'Failed to instantiate its _addon_form_settings_instance', $e->getMessage(), $e->getTrace() );

				return null;
			}
		}

		return $this->_addon_form_settings_instances[ $form_id ];
	}

	/**
	 * Executor of before get form settings values, to be correctly mapped with form_setting instance for form_id
	 *
	 * @since 1.1
	 *
	 * @param $values
	 * @param $form_id
	 *
	 * @return mixed
	 */
	final public function before_get_form_settings_values( $values, $form_id ) {
		$form_settings = $this->get_addon_form_settings( $form_id );
		if ( $form_settings instanceof Forminator_Addon_Form_Settings_Abstract ) {
			if ( is_callable( array( $form_settings, 'before_get_form_settings_values' ) ) ) {
				return $form_settings->before_get_form_settings_values( $values );
			}
		}

		return $values;
	}

	/**
	 * Executor of before save form settings values, to be correctly mapped with form_setting instance for form_id
	 *
	 * @since 1.1
	 *
	 * @param $values
	 * @param $form_id
	 *
	 * @return mixed
	 */
	final public function before_save_form_settings_values( $values, $form_id ) {
		$form_settings = $this->get_addon_form_settings( $form_id );
		if ( $form_settings instanceof Forminator_Addon_Form_Settings_Abstract ) {
			if ( is_callable( array( $form_settings, 'before_save_form_settings_values' ) ) ) {
				return $form_settings->before_save_form_settings_values( $values );
			}
		}

		return $values;
	}


	/**
	 * Get Form Hooks of Addons
	 *
	 * @since 1.1
	 *
	 * @param $form_id
	 *
	 * @return Forminator_Addon_Form_Hooks_Abstract|null
	 */
	final public function get_addon_form_hooks( $form_id ) {
		if ( ! isset( $this->_addon_form_hooks_instances[ $form_id ] ) || ! $this->_addon_form_hooks_instances[ $form_id ] instanceof Forminator_Addon_Form_Hooks_Abstract ) {
			if ( empty( $this->_form_hooks ) ) {
				return null;
			}

			if ( ! class_exists( $this->_form_hooks ) ) {
				return null;
			}

			try {

				$classname                                     = $this->_form_hooks;
				$this->_addon_form_hooks_instances[ $form_id ] = new $classname( $this, $form_id );
			} catch ( Exception $e ) {
				forminator_addon_maybe_log( $this->get_slug(), 'Failed to instantiate its _addon_form_hooks_instance', $e->getMessage() );

				return null;
			}
		}

		return $this->_addon_form_hooks_instances[ $form_id ];
	}

	/**
	 * SAMPLE of callback wizard
	 *
	 * @example {
	 * 'html' : '', => will contains title, description, and form it self
	 * 'has_errors' : true/false => true when it has error, such as invalid input
	 * buttons [
	 *      submit [
	 *          action: forminator_load_mailchimp_settings
	 *          data: {
	 *              step: 2
	 *          },
	 *          markup: '<a></a>'
	 *      ],
	 *      disconnect [
	 *          action: forminator_disconnect_mailchimp,
	 *          data: [],
	 *          markup: '<a></a>'
	 *      ]
	 * }
	 * 'redirect': '',
	 * 'is_close' : true if wizard should be closed
	 * ]
	 *
	 * @param $submitted_data
	 * @param $form_id
	 *
	 * @since   1.1
	 * @return array
	 */
	private function sample_setting_first_step( $submitted_data, $form_id ) {
		//TODO: break `html` into `parts` to make easier for addon to extend
		return array(
			'html'       => '<p>Hello im from first step settings</p>',
			'has_errors' => false,
		);

	}

	/**
	 * SAMPLE of is_completed wizard step
	 *
	 * @since   1.1
	 * @return bool
	 */
	private function sample_setting_first_step_is_completed() {
		// check something
		return true; // when check is passed
	}

	/**
	 * Override this function if you wanna make an addon allow multiple instance on 1 form
	 *
	 * @since 1.1
	 * @return bool
	 */
	public function is_allow_multi_on_form() {
		return false;
	}

	/**
	 * Return button markup
	 *
	 * @since 1.1
	 *
	 * @param        $label
	 * @param string $classes
	 * @param string $tooltip
	 *
	 * @return string
	 */
	public static function get_button_markup( $label, $classes = '', $tooltip = '' ) {
		$markup = '<button type="button" class="sui-button ';
		if ( ! empty( $classes ) ) {
			$markup .= $classes;
		}
		$markup .= '"';
		if ( ! empty( $tooltip ) ) {
			$markup .= 'data-tooltip="' . $tooltip . '"';
		}
		$markup .= '>';
		$markup .= '<span class="sui-loading-text">' . $label . '</span>';
		$markup .= '<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>';
		$markup .= '</button>';

		/**
		 * Filter Addon button markup for setting
		 *
		 * Its possible @see Forminator_Addon_Abstract::get_button_markup() overridden.
		 * Thus this filter wont be called
		 *
		 * @since 1.1
		 *
		 * @param string $markup  Current markup
		 * @param string $label   Button label
		 * @param string $classes Additional classes for `<button>`
		 * @param string $tooltip
		 */
		$markup = apply_filters( 'forminator_addon_setting_button_markup', $markup, $label, $classes, $tooltip );

		return $markup;
	}

	/**
	 * Get Template as string
	 *
	 * @since 1.2
	 *
	 * @param $template
	 * @param $params
	 *
	 * @return string
	 */
	public static function get_template( $template, $params ) {
		/** @noinspection PhpUnusedLocalVariableInspection */
		$template_vars = $params;
		ob_start();
		/** @noinspection PhpIncludeInspection */
		include $template;
		$html = ob_get_clean();

		/**
		 * Filter Html String from template
		 *
		 * @since 1.2
		 *
		 * @param string $html
		 * @param string $template template file path
		 * @param array  $params   template variables
		 */
		$html = apply_filters( 'forminator_addon_get_template', $html, $template, $params );

		return $html;
	}

	/**
	 * Register section(s) on integrations page
	 *
	 * @see   wp-admin/admin.php?page=forminator-integrations
	 *
	 * @since 1.2
	 *
	 * @return array
	 */
	public function register_integration_sections() {
		// callback must be public method on this class
		return array(//			array('section_name', 'callback')
		);
	}

	/**
	 * Get Callback of section on integration page
	 *
	 * when section not provided, it will return all callbacks
	 *
	 * @since 1.2
	 *
	 * @param string $section
	 *
	 * @return array|null
	 */
	public function get_integration_section_callback( $section = null ) {
		$addon_slug           = $this->_slug;
		$integration_sections = $this->register_integration_sections();

		$callback = null;

		if ( is_null( $section ) ) {
			$callback = $integration_sections;
		} elseif ( isset( $integration_sections[ $section ] ) ) {
			$callback = $integration_sections[ $section ];
		}

		/**
		 * Filter Integration section callback
		 *
		 * @since 1.2
		 *
		 * @param array|null  $callback
		 * @param string|null $section              requested section
		 * @param array       $integration_sections registered sections
		 */
		$callback = apply_filters( 'forminator_addon_' . $addon_slug . '_integration_section_callback', $callback, $section, $integration_sections );

		return $callback;

	}

	/**
	 * Generate multi_id
	 *
	 * @since 1.2
	 *
	 * @param $form_id
	 *
	 * @return array
	 */
	private function generate_form_settings_multi_id( $form_id ) {
		$addon_slug             = $this->get_slug();
		$addon                  = $this;
		$multi_id               = 0;
		$form_settings_instance = $this->get_addon_form_settings( $form_id );
		if ( $this->is_allow_multi_on_form() && ! is_null( $form_settings_instance ) && $form_settings_instance instanceof Forminator_Addon_Form_Settings_Abstract ) {
			$multi_id = $form_settings_instance->generate_multi_id();
		}

		/**
		 * Filter new generated multi id of addon form setting
		 *
		 * @since 1.2
		 *
		 * @param string                                  $multi_id
		 * @param Forminator_Addon_Form_Settings_Abstract $addon                  Addon Instance
		 * @param Forminator_Addon_Form_Settings_Abstract $form_settings_instance Addon Form Settings Instance
		 */
		$multi_ids = apply_filters( 'forminator_addon_' . $addon_slug . '_form_settings_multi_id', $multi_id, $addon, $form_settings_instance );

		return $multi_ids;
	}

	/**
	 * Get ClassName of addon Poll Settings
	 *
	 * @see   Forminator_Addon_Poll_Settings_Abstract
	 *
	 * @since 1.1
	 * @return null|string
	 */
	final public function get_poll_settings_class_name() {
		$addon_slug               = $this->get_slug();
		$poll_settings_class_name = $this->_poll_settings;

		/**
		 * Filter class name of the addon poll settings
		 *
		 * Poll settings class name is a string
		 * it will be validated by `class_exists` and must be instanceof @see Forminator_Addon_Poll_Settings_Abstract
		 *
		 * @since 1.1
		 *
		 * @param string $poll_settings_class_name
		 */
		$poll_settings_class_name = apply_filters( 'forminator_addon_' . $addon_slug . '_poll_settings_class_name', $poll_settings_class_name );

		return $poll_settings_class_name;
	}

	/**
	 * Get Poll Settings Instance
	 *
	 * @since   1.6.1
	 *
	 * @param $poll_id
	 *
	 * @return Forminator_Addon_Poll_Settings_Abstract|null
	 */
	final public function get_addon_poll_settings( $poll_id ) {
		$class_name = $this->get_poll_settings_class_name();
		if ( ! isset( $this->_addon_poll_settings_instances[ $poll_id ] )
			|| ! $this->_addon_poll_settings_instances[ $poll_id ] instanceof Forminator_Addon_Poll_Settings_Abstract ) {
			if ( empty( $class_name ) ) {
				return null;
			}

			if ( ! class_exists( $class_name ) ) {
				return null;
			}

			try {
				$poll_settings_instance = new $class_name( $this, $poll_id );
				if ( ! $poll_settings_instance instanceof Forminator_Addon_Poll_Settings_Abstract ) {
					throw new Forminator_Addon_Exception( $class_name . ' is not instanceof Forminator_Addon_Poll_Settings_Abstract' );
				}
				$this->_addon_poll_settings_instances[ $poll_id ] = $poll_settings_instance;
				forminator_maybe_attach_addon_hook( $this );
			} catch ( Exception $e ) {
				forminator_addon_maybe_log( $this->get_slug(), 'Failed to instantiate its _addon_poll_settings_instances', $e->getMessage(), $e->getTrace() );

				return null;
			}
		}

		return $this->_addon_poll_settings_instances[ $poll_id ];
	}

	/**
	 * Executor of before get form settings values, to be correctly mapped with poll_setting instance for poll_id
	 *
	 * @since 1.6.1
	 *
	 * @param $values
	 * @param $poll_id
	 *
	 * @return mixed
	 */
	final public function before_get_poll_settings_values( $values, $poll_id ) {
		$poll_settings = $this->get_addon_poll_settings( $poll_id );
		if ( $poll_settings instanceof Forminator_Addon_Poll_Settings_Abstract ) {
			if ( is_callable( array( $poll_settings, 'before_get_poll_settings_values' ) ) ) {
				return $poll_settings->before_get_poll_settings_values( $values );
			}
		}

		return $values;
	}

	/**
	 * Executor of before save form settings values, to be correctly mapped with poll_setting instance for poll_id
	 *
	 * @since 1.6.1
	 *
	 * @param $values
	 * @param $poll_id
	 *
	 * @return mixed
	 */
	final public function before_save_poll_settings_values( $values, $poll_id ) {
		$poll_settings = $this->get_addon_poll_settings( $poll_id );
		if ( $poll_settings instanceof Forminator_Addon_Poll_Settings_Abstract ) {
			if ( is_callable( array( $poll_settings, 'before_save_poll_settings_values' ) ) ) {
				return $poll_settings->before_save_poll_settings_values( $values );
			}
		}

		return $values;
	}

	/**
	 * Get Poll Setting Wizard
	 * This function will process @see Forminator_Addon_Abstract::settings_wizards()
	 * Please keep in mind this function will only be called when @see Forminator_Addon_Abstract::is_settings_available() return `true`
	 * Which is doing check on @see Forminator_Addon_Abstract::settings_wizards() requirements is passed
	 *
	 * @since 1.6.1
	 *
	 * @param     $submitted_data
	 * @param int $poll_id
	 * @param int $current_step
	 * @param int $step
	 *
	 * @return array|mixed
	 */
	final public function get_poll_settings_wizard( $submitted_data, $poll_id, $current_step = 0, $step = 0 ) {

		$settings_steps = array();
		if ( ! $this->is_connected() ) {
			$settings_steps = $this->settings_wizards();
		}

		$poll_settings_steps = $this->get_poll_settings_steps( $poll_id );

		$steps = array_merge( $settings_steps, $poll_settings_steps );

		if ( ! is_array( $steps ) ) {
			/* translators: ... */
			return $this->get_empty_wizard( sprintf( __( 'No Poll Settings available for %1$s', Forminator::DOMAIN ), $this->get_title() ) );
		}
		$total_steps = count( $steps );
		if ( $total_steps < 1 ) {
			/* translators: ... */
			return $this->get_empty_wizard( sprintf( __( 'No Poll Settings available for %1$s', Forminator::DOMAIN ), $this->get_title() ) );
		}

		if ( ! isset( $steps[ $step ] ) ) {
			// go to last step
			$step = $total_steps - 1;

			return $this->get_poll_settings_wizard( $submitted_data, $poll_id, $current_step, $step );
		}

		if ( $step > 0 ) {
			if ( $current_step > 0 ) {
				//check previous step is complete
				$prev_step              = $current_step - 1;
				$prev_step_is_completed = true;
				// only call `is_completed` when its defined
				if ( isset( $steps[ $prev_step ]['is_completed'] ) && is_callable( $steps[ $prev_step ]['is_completed'] ) ) {
					$prev_step_is_completed = call_user_func( $steps[ $prev_step ]['is_completed'], $submitted_data );
				}
				if ( ! $prev_step_is_completed ) {
					$step --;

					return $this->get_poll_settings_wizard( $submitted_data, $poll_id, $current_step, $step );
				}
			}

			// only validation when it moves forward
			if ( $step > $current_step ) {
				$current_step_result = $this->get_poll_settings_wizard( $submitted_data, $poll_id, $current_step, $current_step );
				if ( isset( $current_step_result['has_errors'] ) && true === $current_step_result['has_errors'] ) {
					return $current_step_result;
				} else {
					//set empty submitted data for next step, except preserved as reference
					$preserved_keys = array(
						'multi_id',
					);
					foreach ( $submitted_data as $key => $value ) {
						if ( ! in_array( $key, $preserved_keys, true ) ) {
							unset( $submitted_data[ $key ] );
						}
					}
				}
			}
		}

		$poll_settings_wizard = $this->get_wizard( $steps, $submitted_data, $poll_id, $step );

		$addon_slug             = $this->get_slug();
		$addon                  = $this;
		$poll_settings_instance = $this->get_addon_poll_settings( $poll_id );

		/**
		 * Filter poll settings wizard returned to client
		 *
		 * @since 1.6.1
		 *
		 * @param array                                        $poll_settings_wizard
		 * @param array                                        $submitted_data         $_POST from client
		 * @param int                                          $poll_id                poll ID requested for
		 * @param int                                          $current_step           Current Step displayed to user, start from 0
		 * @param int                                          $step                   Step requested by client, start from 0
		 * @param Forminator_Addon_Abstract                    $addon                  Addon Instance
		 * @param Forminator_Addon_Poll_Settings_Abstract|null $poll_settings_instance Addon Form settings instance, or null if unavailable
		 */
		$poll_settings_wizard = apply_filters(
			'forminator_addon_' . $addon_slug . '_poll_settings_wizard',
			$poll_settings_wizard,
			$submitted_data,
			$poll_id,
			$current_step,
			$step,
			$addon,
			$poll_settings_instance
		);

		return $poll_settings_wizard;
	}

	/**
	 * Get poll settings wizard steps
	 *
	 * @since 1.6.1
	 *
	 * @param $poll_id
	 *
	 * @return array
	 */
	private function get_poll_settings_steps( $poll_id ) {
		$addon_slug             = $this->get_slug();
		$addon                  = $this;
		$poll_settings_steps    = array();
		$poll_settings_instance = $this->get_addon_poll_settings( $poll_id );
		if ( ! is_null( $poll_settings_instance ) && $poll_settings_instance instanceof Forminator_Addon_Poll_Settings_Abstract ) {
			$poll_settings_steps = $poll_settings_instance->poll_settings_wizards();
		}

		/**
		 * Filter form settings step that will be used for building wizard
		 *
		 * More detail : @see Forminator_Addon_Poll_Settings_Abstract::poll_settings_wizards()
		 *
		 * @since 1.6.1
		 *
		 * @param array                                        $poll_settings_steps
		 * @param int                                          $poll_id                current form id
		 * @param Forminator_Addon_Abstract                    $addon                  Addon instance
		 * @param Forminator_Addon_Poll_Settings_Abstract|null $poll_settings_instance Form settings of addon if available, or null otherwise
		 */
		$poll_settings_steps = apply_filters( 'forminator_addon_' . $addon_slug . '_poll_settings_steps', $poll_settings_steps, $poll_id, $addon, $poll_settings_instance );

		return $poll_settings_steps;
	}

	/**
	 * Get poll settings multi id
	 *
	 * @since 1.6.1
	 *
	 * @param $poll_id
	 *
	 * @return array
	 */
	private function get_poll_settings_multi_ids( $poll_id ) {
		$addon_slug             = $this->get_slug();
		$addon                  = $this;
		$multi_ids              = array();
		$poll_settings_instance = $this->get_addon_poll_settings( $poll_id );
		if ( $this->is_allow_multi_on_poll() && ! is_null( $poll_settings_instance ) && $poll_settings_instance instanceof Forminator_Addon_Poll_Settings_Abstract ) {
			$multi_ids = $poll_settings_instance->get_multi_ids();
		}

		/**
		 * Filter multi id of addon poll settings
		 *
		 * @since 1.6.1
		 *
		 * @param array                                   $multi_ids
		 * @param Forminator_Addon_Abstract               $addon                  Addon Instance
		 * @param Forminator_Addon_Poll_Settings_Abstract $poll_settings_instance Addon Form Settings Instance
		 */
		$multi_ids = apply_filters( 'forminator_addon_' . $addon_slug . '_poll_settings_multi_ids', $multi_ids, $addon, $poll_settings_instance );

		return $multi_ids;
	}

	/**
	 * Override this function if you wanna make an addon allow multiple instance on 1 poll
	 *
	 * @since 1.6.1
	 * @return bool
	 */
	public function is_allow_multi_on_poll() {
		return false;
	}

	/**
	 * Transform addon instance into array with form relation
	 *
	 * @since  1.1
	 * @since  1.2 generate new multi_id to allow reference on wizard
	 *
	 * @param $poll_id
	 *
	 * @return array
	 */
	final public function to_array_with_poll( $poll_id ) {
		$to_array                               = $this->to_array();
		$is_allow_multi_on_poll                 = $this->is_allow_multi_on_poll();
		$to_array['is_poll_connected']          = $this->is_poll_connected( $poll_id );
		$to_array['is_poll_settings_available'] = $this->is_poll_settings_available( $poll_id );
		$to_array['is_allow_multi_on_poll']     = $is_allow_multi_on_poll;

		$to_array['multi_id'] = $this->generate_poll_settings_multi_id( $poll_id );

		// handle multiple form setting
		if ( $is_allow_multi_on_poll ) {
			$to_array['multi_ids'] = $this->get_poll_settings_multi_ids( $poll_id );
		}

		$to_array_with_poll = $to_array;
		$addon_slug         = $this->get_slug();
		$addon              = $this;

		/**
		 * Filter array of addon properties
		 *
		 * Including relation with form_id
		 *
		 * @since 1.6.1
		 *
		 * @param array                     $to_array_with_poll array of addon properties
		 * @param int                       $poll_id            Poll ID
		 * @param Forminator_Addon_Abstract $addon              Addon instance
		 */
		$to_array_with_poll = apply_filters( 'forminator_addon_' . $addon_slug . '_to_array_with_poll', $to_array_with_poll, $poll_id, $addon );

		return $to_array_with_poll;
	}

	/**
	 * Flag for check if and addon connected to a poll(poll settings such as list id completed)
	 *
	 * Unlike @see self::is_form_connected(), this is not ABSTRACT method, so previous addon not generating any error insheritance
	 *
	 * Please apply necessary WordPress hook on the inheritance class
	 *
	 * @since   1.6.1
	 *
	 * @param $poll_id
	 *
	 * @return boolean
	 */
	public function is_poll_connected( $poll_id ) {
		return false;
	}

	/**
	 * Override this function if you need to apply some conditional logic on it
	 * By Default this function will check
	 *
	 * @see     Forminator_Addon_Abstract::settings_wizards()
	 * @see     Forminator_Addon_Poll_Settings_Abstract::poll_settings_wizards()
	 * as valid multi array
	 *
	 * @since   1.6.1
	 *
	 * @param $poll_id
	 *
	 * @return bool
	 */
	public function is_poll_settings_available( $poll_id ) {
		$steps      = array();
		$poll_steps = $this->get_poll_settings_steps( $poll_id );

		$steps = array_merge( $steps, $poll_steps );
		if ( ! is_array( $steps ) ) {
			return false;
		}

		if ( count( $steps ) < 1 ) {
			return false;
		}

		return true;
	}

	/**
	 * Generate multi_id
	 *
	 * @since 1.6.1
	 *
	 * @param $poll_id
	 *
	 * @return array
	 */
	private function generate_poll_settings_multi_id( $poll_id ) {
		$addon_slug             = $this->get_slug();
		$addon                  = $this;
		$multi_id               = 0;
		$poll_settings_instance = $this->get_addon_poll_settings( $poll_id );
		if ( $this->is_allow_multi_on_poll() && ! is_null( $poll_settings_instance ) && $poll_settings_instance instanceof Forminator_Addon_Poll_Settings_Abstract ) {
			$multi_id = $poll_settings_instance->generate_multi_id();
		}

		/**
		 * Filter new generated multi id of addon form setting
		 *
		 * @since 1.6.1
		 *
		 * @param string                                  $multi_id
		 * @param Forminator_Addon_Abstract               $addon                  Addon Instance
		 * @param Forminator_Addon_Poll_Settings_Abstract $poll_settings_instance Addon Poll Settings Instance
		 */
		$multi_ids = apply_filters( 'forminator_addon_' . $addon_slug . '_poll_settings_multi_id', $multi_id, $addon, $poll_settings_instance );

		return $multi_ids;
	}

	/**
	 * Get poll Hooks of Addons
	 *
	 * @since 1.6.1
	 *
	 * @param $poll_id
	 *
	 * @return Forminator_Addon_Poll_Hooks_Abstract|null
	 */
	final public function get_addon_poll_hooks( $poll_id ) {
		if ( ! isset( $this->_addon_poll_hooks_instances[ $poll_id ] ) || ! $this->_addon_poll_hooks_instances[ $poll_id ] instanceof Forminator_Addon_Poll_Hooks_Abstract ) {
			if ( empty( $this->_poll_hooks ) ) {
				return null;
			}

			if ( ! class_exists( $this->_poll_hooks ) ) {
				return null;
			}

			try {

				$classname                                     = $this->_poll_hooks;
				$this->_addon_poll_hooks_instances[ $poll_id ] = new $classname( $this, $poll_id );
			} catch ( Exception $e ) {
				forminator_addon_maybe_log( $this->get_slug(), 'Failed to instantiate its _addon_poll_hooks_instances', $e->getMessage() );

				return null;
			}
		}

		return $this->_addon_poll_hooks_instances[ $poll_id ];
	}

	/**
	 * Get ClassName of addon Quiz Settings
	 *
	 * @see   Forminator_Addon_Quiz_Settings_Abstract
	 *
	 * @since 1.6.2
	 * @return null|string
	 */
	final public function get_quiz_settings_class_name() {
		$addon_slug               = $this->get_slug();
		$quiz_settings_class_name = $this->_quiz_settings;

		/**
		 * Filter class name of the addon quiz settings
		 *
		 * Quiz settings class name is a string
		 * it will be validated by `class_exists` and must be instanceof @see Forminator_Addon_Quiz_Settings_Abstract
		 *
		 * @since 1.1
		 *
		 * @param string $quiz_settings_class_name
		 */
		$quiz_settings_class_name = apply_filters( 'forminator_addon_' . $addon_slug . '_quiz_settings_class_name', $quiz_settings_class_name );

		return $quiz_settings_class_name;
	}

	/**
	 * Get Quiz Settings Instance
	 *
	 * @since   1.6.2
	 *
	 * @param $quiz_id
	 *
	 * @return Forminator_Addon_Quiz_Settings_Abstract|null
	 */
	final public function get_addon_quiz_settings( $quiz_id ) {
		$class_name = $this->get_quiz_settings_class_name();
		if ( ! isset( $this->_addon_quiz_settings_instances[ $quiz_id ] )
			|| ! $this->_addon_quiz_settings_instances[ $quiz_id ] instanceof Forminator_Addon_Quiz_Settings_Abstract ) {
			if ( empty( $class_name ) ) {
				return null;
			}

			if ( ! class_exists( $class_name ) ) {
				return null;
			}

			try {
				$quiz_settings_instance = new $class_name( $this, $quiz_id );
				if ( ! $quiz_settings_instance instanceof Forminator_Addon_Quiz_Settings_Abstract ) {
					throw new Forminator_Addon_Exception( $class_name . ' is not instanceof Forminator_Addon_Quiz_Settings_Abstract' );
				}
				$this->_addon_quiz_settings_instances[ $quiz_id ] = $quiz_settings_instance;
				forminator_maybe_attach_addon_hook( $this );
			} catch ( Exception $e ) {
				forminator_addon_maybe_log( $this->get_slug(), 'Failed to instantiate its _addon_quiz_settings_instances', $e->getMessage(), $e->getTrace() );

				return null;
			}
		}

		return $this->_addon_quiz_settings_instances[ $quiz_id ];
	}

	/**
	 * Executor of before get form settings values, to be correctly mapped with quiz_setting instance for quiz_id
	 *
	 * @since 1.6.2
	 *
	 * @param $values
	 * @param $quiz_id
	 *
	 * @return mixed
	 */
	final public function before_get_quiz_settings_values( $values, $quiz_id ) {
		$quiz_settings = $this->get_addon_quiz_settings( $quiz_id );
		if ( $quiz_settings instanceof Forminator_Addon_Quiz_Settings_Abstract ) {
			if ( is_callable( array( $quiz_settings, 'before_get_quiz_settings_values' ) ) ) {
				return $quiz_settings->before_get_quiz_settings_values( $values );
			}
		}

		return $values;
	}

	/**
	 * Executor of before save form settings values, to be correctly mapped with quiz_setting instance for quiz_id
	 *
	 * @since 1.6.2
	 *
	 * @param $values
	 * @param $quiz_id
	 *
	 * @return mixed
	 */
	final public function before_save_quiz_settings_values( $values, $quiz_id ) {
		$quiz_settings = $this->get_addon_quiz_settings( $quiz_id );
		if ( $quiz_settings instanceof Forminator_Addon_Quiz_Settings_Abstract ) {
			if ( is_callable( array( $quiz_settings, 'before_save_quiz_settings_values' ) ) ) {
				return $quiz_settings->before_save_quiz_settings_values( $values );
			}
		}

		return $values;
	}

	/**
	 * Get Quiz Setting Wizard
	 * This function will process @see Forminator_Addon_Abstract::settings_wizards()
	 * Please keep in mind this function will only be called when @see Forminator_Addon_Abstract::is_settings_available() return `true`
	 * Which is doing check on @see Forminator_Addon_Abstract::settings_wizards() requirements is passed
	 *
	 * @since 1.6.2
	 *
	 * @param     $submitted_data
	 * @param int $quiz_id
	 * @param int $current_step
	 * @param int $step
	 *
	 * @return array|mixed
	 */
	final public function get_quiz_settings_wizard( $submitted_data, $quiz_id, $current_step = 0, $step = 0 ) {

		$settings_steps = array();
		if ( ! $this->is_connected() ) {
			$settings_steps = $this->settings_wizards();
		}

		$quiz_settings_steps = $this->get_quiz_settings_steps( $quiz_id );

		$steps = array_merge( $settings_steps, $quiz_settings_steps );

		if ( ! is_array( $steps ) ) {
			/* translators: ... */
			return $this->get_empty_wizard( sprintf( __( 'No Quiz Settings available for %1$s', Forminator::DOMAIN ), $this->get_title() ) );
		}
		$total_steps = count( $steps );
		if ( $total_steps < 1 ) {
			/* translators: ... */
			return $this->get_empty_wizard( sprintf( __( 'No Quiz Settings available for %1$s', Forminator::DOMAIN ), $this->get_title() ) );
		}

		if ( ! isset( $steps[ $step ] ) ) {
			// go to last step
			$step = $total_steps - 1;

			return $this->get_quiz_settings_wizard( $submitted_data, $quiz_id, $current_step, $step );
		}

		if ( $step > 0 ) {
			if ( $current_step > 0 ) {
				//check previous step is complete
				$prev_step              = $current_step - 1;
				$prev_step_is_completed = true;
				// only call `is_completed` when its defined
				if ( isset( $steps[ $prev_step ]['is_completed'] ) && is_callable( $steps[ $prev_step ]['is_completed'] ) ) {
					$prev_step_is_completed = call_user_func( $steps[ $prev_step ]['is_completed'], $submitted_data );
				}
				if ( ! $prev_step_is_completed ) {
					$step --;

					return $this->get_quiz_settings_wizard( $submitted_data, $quiz_id, $current_step, $step );
				}
			}

			// only validation when it moves forward
			if ( $step > $current_step ) {
				$current_step_result = $this->get_quiz_settings_wizard( $submitted_data, $quiz_id, $current_step, $current_step );
				if ( isset( $current_step_result['has_errors'] ) && true === $current_step_result['has_errors'] ) {
					return $current_step_result;
				} else {
					//set empty submitted data for next step, except preserved as reference
					$preserved_keys = array(
						'multi_id',
					);
					foreach ( $submitted_data as $key => $value ) {
						if ( ! in_array( $key, $preserved_keys, true ) ) {
							unset( $submitted_data[ $key ] );
						}
					}
				}
			}
		}

		$quiz_settings_wizard = $this->get_wizard( $steps, $submitted_data, $quiz_id, $step );

		$addon_slug             = $this->get_slug();
		$addon                  = $this;
		$quiz_settings_instance = $this->get_addon_quiz_settings( $quiz_id );

		/**
		 * Filter quiz settings wizard returned to client
		 *
		 * @since 1.6.2
		 *
		 * @param array                                        $quiz_settings_wizard
		 * @param array                                        $submitted_data         $_POST from client
		 * @param int                                          $quiz_id                quiz ID requested for
		 * @param int                                          $current_step           Current Step displayed to user, start from 0
		 * @param int                                          $step                   Step requested by client, start from 0
		 * @param Forminator_Addon_Abstract                    $addon                  Addon Instance
		 * @param Forminator_Addon_Quiz_Settings_Abstract|null $quiz_settings_instance Addon Form settings instance, or null if unavailable
		 */
		$quiz_settings_wizard = apply_filters(
			'forminator_addon_' . $addon_slug . '_quiz_settings_wizard',
			$quiz_settings_wizard,
			$submitted_data,
			$quiz_id,
			$current_step,
			$step,
			$addon,
			$quiz_settings_instance
		);

		return $quiz_settings_wizard;
	}

	/**
	 * Get quiz settings wizard steps
	 *
	 * @since 1.6.2
	 *
	 * @param $quiz_id
	 *
	 * @return array
	 */
	private function get_quiz_settings_steps( $quiz_id ) {
		$addon_slug             = $this->get_slug();
		$addon                  = $this;
		$quiz_settings_steps    = array();
		$quiz_settings_instance = $this->get_addon_quiz_settings( $quiz_id );
		if ( ! is_null( $quiz_settings_instance ) && $quiz_settings_instance instanceof Forminator_Addon_Quiz_Settings_Abstract ) {
			$quiz_settings_steps = $quiz_settings_instance->quiz_settings_wizards();
		}

		/**
		 * Filter form settings step that will be used for building wizard
		 *
		 * More detail : @see Forminator_Addon_Quiz_Settings_Abstract::quiz_settings_wizards()
		 *
		 * @since 1.6.2
		 *
		 * @param array                                        $quiz_settings_steps
		 * @param int                                          $quiz_id                current quiz id
		 * @param Forminator_Addon_Abstract                    $addon                  Addon instance
		 * @param Forminator_Addon_Quiz_Settings_Abstract|null $quiz_settings_instance Quiz settings of addon if available, or null otherwise
		 */
		$quiz_settings_steps = apply_filters( 'forminator_addon_' . $addon_slug . '_quiz_settings_steps', $quiz_settings_steps, $quiz_id, $addon, $quiz_settings_instance );

		return $quiz_settings_steps;
	}

	/**
	 * Get quiz settings multi id
	 *
	 * @since 1.6.2
	 *
	 * @param $quiz_id
	 *
	 * @return array
	 */
	private function get_quiz_settings_multi_ids( $quiz_id ) {
		$addon_slug             = $this->get_slug();
		$addon                  = $this;
		$multi_ids              = array();
		$quiz_settings_instance = $this->get_addon_quiz_settings( $quiz_id );
		if ( $this->is_allow_multi_on_quiz() && ! is_null( $quiz_settings_instance ) && $quiz_settings_instance instanceof Forminator_Addon_Quiz_Settings_Abstract ) {
			$multi_ids = $quiz_settings_instance->get_multi_ids();
		}

		/**
		 * Filter multi id of addon quiz settings
		 *
		 * @since 1.6.2
		 *
		 * @param array                                   $multi_ids
		 * @param Forminator_Addon_Abstract               $addon                  Addon Instance
		 * @param Forminator_Addon_Quiz_Settings_Abstract $quiz_settings_instance Addon Quiz Settings Instance
		 */
		$multi_ids = apply_filters( 'forminator_addon_' . $addon_slug . '_quiz_settings_multi_ids', $multi_ids, $addon, $quiz_settings_instance );

		return $multi_ids;
	}

	/**
	 * Override this function if you wanna make an addon allow multiple instance on 1 quiz
	 *
	 * @since 1.6.2
	 * @return bool
	 */
	public function is_allow_multi_on_quiz() {
		return false;
	}

	/**
	 * Transform addon instance into array with form relation
	 *
	 * @since  1.6.2
	 *
	 * @param $quiz_id
	 *
	 * @return array
	 */
	final public function to_array_with_quiz( $quiz_id ) {
		$to_array                               = $this->to_array();
		$is_allow_multi_on_quiz                 = $this->is_allow_multi_on_quiz();
		$to_array['is_quiz_connected']          = $this->is_quiz_connected( $quiz_id );
		$to_array['is_quiz_settings_available'] = $this->is_quiz_settings_available( $quiz_id );
		$to_array['is_allow_multi_on_quiz']     = $is_allow_multi_on_quiz;

		$to_array['multi_id'] = $this->generate_quiz_settings_multi_id( $quiz_id );

		// handle multiple form setting
		if ( $is_allow_multi_on_quiz ) {
			$to_array['multi_ids'] = $this->get_quiz_settings_multi_ids( $quiz_id );
		}

		$to_array_with_quiz = $to_array;
		$addon_slug         = $this->get_slug();
		$addon              = $this;

		/**
		 * Filter array of addon properties
		 *
		 * Including relation with form_id
		 *
		 * @since 1.6.2
		 *
		 * @param array                     $to_array_with_quiz array of addon properties
		 * @param int                       $quiz_id            Quiz ID
		 * @param Forminator_Addon_Abstract $addon              Addon instance
		 */
		$to_array_with_quiz = apply_filters( 'forminator_addon_' . $addon_slug . '_to_array_with_quiz', $to_array_with_quiz, $quiz_id, $addon );

		return $to_array_with_quiz;
	}

	/**
	 * Flag for check if and addon connected to a quiz(quiz settings such as list id completed)
	 *
	 * Unlike @see self::is_form_connected(), this is not ABSTRACT method, so previous addon not generating any error insheritance
	 *
	 * Please apply necessary WordPress hook on the inheritance class
	 *
	 * @since   1.6.2
	 *
	 * @param $quiz_id
	 *
	 * @return boolean
	 */
	public function is_quiz_connected( $quiz_id ) {
		return false;
	}

	/**
	 * Override this function if you need to apply some conditional logic on it
	 * By Default this function will check
	 *
	 * @see     Forminator_Addon_Abstract::settings_wizards()
	 * @see     Forminator_Addon_Quiz_Settings_Abstract::quiz_settings_wizards()
	 * as valid multi array
	 *
	 * @since   1.6.2
	 *
	 * @param $quiz_id
	 *
	 * @return bool
	 */
	public function is_quiz_settings_available( $quiz_id ) {
		$steps      = array();
		$quiz_steps = $this->get_quiz_settings_steps( $quiz_id );

		$steps = array_merge( $steps, $quiz_steps );
		if ( ! is_array( $steps ) ) {
			return false;
		}

		if ( count( $steps ) < 1 ) {
			return false;
		}

		return true;
	}

	/**
	 * Generate multi_id
	 *
	 * @since 1.6.2
	 *
	 * @param $quiz_id
	 *
	 * @return array
	 */
	private function generate_quiz_settings_multi_id( $quiz_id ) {
		$addon_slug             = $this->get_slug();
		$addon                  = $this;
		$multi_id               = 0;
		$quiz_settings_instance = $this->get_addon_quiz_settings( $quiz_id );
		if ( $this->is_allow_multi_on_quiz() && ! is_null( $quiz_settings_instance ) && $quiz_settings_instance instanceof Forminator_Addon_Quiz_Settings_Abstract ) {
			$multi_id = $quiz_settings_instance->generate_multi_id();
		}

		/**
		 * Filter new generated multi id of addon form setting
		 *
		 * @since 1.6.2
		 *
		 * @param string                                  $multi_id
		 * @param Forminator_Addon_Abstract               $addon                  Addon Instance
		 * @param Forminator_Addon_Quiz_Settings_Abstract $quiz_settings_instance Addon Quiz Settings Instance
		 */
		$multi_ids = apply_filters( 'forminator_addon_' . $addon_slug . '_quiz_settings_multi_id', $multi_id, $addon, $quiz_settings_instance );

		return $multi_ids;
	}

	/**
	 * Get quiz Hooks of Addons
	 *
	 * @since 1.6.2
	 *
	 * @param $quiz_id
	 *
	 * @return Forminator_Addon_Quiz_Hooks_Abstract|null
	 */
	final public function get_addon_quiz_hooks( $quiz_id ) {
		if ( ! isset( $this->_addon_quiz_hooks_instances[ $quiz_id ] ) || ! $this->_addon_quiz_hooks_instances[ $quiz_id ] instanceof Forminator_Addon_Quiz_Hooks_Abstract ) {
			if ( empty( $this->_quiz_hooks ) ) {
				return null;
			}

			if ( ! class_exists( $this->_quiz_hooks ) ) {
				return null;
			}

			try {

				$classname                                     = $this->_quiz_hooks;
				$this->_addon_quiz_hooks_instances[ $quiz_id ] = new $classname( $this, $quiz_id );
			} catch ( Exception $e ) {
				forminator_addon_maybe_log( $this->get_slug(), 'Failed to instantiate its _addon_quiz_hooks_instances', $e->getMessage() );

				return null;
			}
		}

		return $this->_addon_quiz_hooks_instances[ $quiz_id ];
	}


}
