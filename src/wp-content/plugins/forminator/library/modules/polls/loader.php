<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Polls
 *
 * @since 1.0
 */
class Forminator_Polls extends Forminator_Module {

	/**
	 * Module instance
	 *
	 * @var null
	 */
	private static $instance = null;

	/**
	 * Return the plugin instance
	 *
	 * @since 1.0
	 * @return self
	 */
	public static function get_instance() {
		return self::$instance;
	}

	/**
	 * Initialize
	 *
	 * @since 1.0
	 * @since 1.0.6 Add General Data Protection
	 */
	public function init() {
		self::$instance = $this;

		if ( ! class_exists( 'Forminator_General_Data_Protection' ) ) {
			include_once forminator_plugin_dir() . 'library/abstracts/abstract-class-general-data-protection.php';
		}
		include_once dirname( __FILE__ ) . '/protection/general-data-protection.php';
		if ( class_exists( 'Forminator_Polls_General_Data_Protection' ) ) {
			new Forminator_Polls_General_Data_Protection();
		}
	}

	/**
	 * Load module Admin part
	 *
	 * @since 1.0
	 */
	public function load_admin() {
		if( is_admin() ) {
			include_once dirname(__FILE__) . '/admin/admin-loader.php';

			new Forminator_Poll_Admin();
		}
	}


	/**
	 * Load front part
	 *
	 * @since 1.0
	 */
	public function load_front() {
		include_once dirname(__FILE__) . '/front/front-action.php';
		include_once dirname(__FILE__) . '/front/front-render.php';
		include_once dirname(__FILE__) . '/front/front-mail.php';
		new Forminator_Poll_Front_Action();
		new Forminator_Poll_Front();

		add_action( 'wp_ajax_forminator_load_poll', array( 'Forminator_Poll_Front', 'ajax_load_module' ) );
		add_action( 'wp_ajax_nopriv_forminator_load_poll', array( 'Forminator_Poll_Front', 'ajax_load_module' ) );
	}

	/**
	 * Register CPT
	 *
	 * @since 1.0
	 */
	public function register_cpt() {
		$labels = array(
			'name'          => $this->get_option('name'),
			'singular_name' => $this->get_option('singular_name')
		);

		$args = array(
			'labels'             => $labels,
			'description'        => $this->get_option('description'),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => false,
			'show_in_menu'       => false,
			'query_var'          => false,
			'rewrite'            => false,
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'supports'           => array()
		);

		register_post_type( 'forminator_polls', $args );
	}

	/**
	 * Module option
	 *
	 * @return array
	 */
	public function options() {
		return array(
			'name'          => __('Polls', Forminator::DOMAIN),
			'singular_name' => __('Poll', Forminator::DOMAIN),
			'description'   => __( "Create polls, and collect user data. Choose a visualization style that best suits your needs.", Forminator::DOMAIN ),
			'button_label'  => __( "New Poll", Forminator::DOMAIN ),
			'icon'          => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="17" viewBox="0 0 18 17" preserveAspectRatio="none" class="wpmudev-icon wpmudev-i_polls"><path fill-rule="evenodd" d="M8.156 10.746H5.098c-.094 0-.17.032-.23.097-.057.064-.087.137-.087.22v4.728c0 .095.03.174.09.238.058.065.134.097.228.097h3.058c.082 0 .156-.032.22-.097.064-.064.097-.143.097-.237v-4.727c0-.083-.033-.156-.097-.22-.064-.065-.138-.097-.22-.097zM12.902.973H9.844c-.082 0-.156.032-.22.096s-.097.143-.097.237V15.79c0 .095.033.174.097.238.064.065.138.097.22.097h3.058c.094 0 .17-.032.23-.097.057-.064.087-.143.087-.237V1.308c0-.094-.03-.173-.09-.238-.058-.065-.134-.097-.228-.097zm4.764 4.412h-3.04c-.095 0-.17.032-.23.096-.058.066-.087.14-.087.22v10.09c0 .095.028.174.086.238.06.065.135.097.23.097h3.04c.094 0 .173-.032.237-.097.065-.064.097-.143.097-.237V5.7c0-.08-.032-.154-.097-.22-.064-.063-.143-.095-.237-.095zm-14.29 0H.333c-.094 0-.173.032-.237.096C.032 5.547 0 5.62 0 5.7v10.09c0 .095.032.174.097.238.064.065.143.097.237.097h3.04c.095 0 .17-.032.23-.097.058-.064.087-.143.087-.237V5.7c0-.08-.028-.154-.086-.22-.06-.063-.135-.095-.23-.095z"/></svg>'
		);
	}
}
