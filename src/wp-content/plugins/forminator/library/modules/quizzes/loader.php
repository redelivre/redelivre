<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Quizzes
 *
 * @since 1.0
 */
class Forminator_Quizzes extends Forminator_Module {

	/**
	 * Module instance
	 *
	 * @var null|Forminator_Quizzes
	 */
	private static $instance = null;

	/**
	 * Return the plugin instance
	 *
	 * @return Forminator_Quizzes
	 */
	public static function get_instance() {
		return self::$instance;
	}

	/**
	 * Initialize
	 *
	 * @since 1.0
	 */
	public function init() {
		self::$instance = $this;

		if ( ! class_exists( 'Forminator_General_Data_Protection' ) ) {
			include_once forminator_plugin_dir() . 'library/abstracts/abstract-class-general-data-protection.php';
		}
		include_once dirname( __FILE__ ) . '/protection/general-data-protection.php';
		if ( class_exists( 'Forminator_Quiz_General_Data_Protection' ) ) {
			new Forminator_Quiz_General_Data_Protection();
		}
	}

	/**
	 * Load module Admin part
	 *
	 * @since 1.0
	 */
	public function load_admin() {
		if ( is_admin() ) {
			include_once dirname(__FILE__) . '/admin/admin-loader.php';

			new Forminator_Quizz_Admin();
		}
	}

	/**
	 * Load front part
	 *
	 * @since 1.0
	 */
	public function load_front() {
		include_once dirname(__FILE__) . '/front/front-assets.php';
		include_once dirname(__FILE__) . '/front/front-result.php';
		include_once dirname(__FILE__) . '/front/front-render.php';
		include_once dirname(__FILE__) . '/front/front-action.php';
		include_once dirname(__FILE__) . '/front/front-mail.php';
		new Forminator_QForm_Front();
		new Forminator_Quizz_Front_Action();
		new Forminator_QForm_Result();

		add_action( 'wp_ajax_forminator_load_quiz', array( 'Forminator_QForm_Front', 'ajax_load_module' ) );

		add_action( 'wp_ajax_forminator_reload_quiz', array( 'Forminator_QForm_Front', 'ajax_reload_module' ) );
		add_action( 'wp_ajax_nopriv_forminator_reload_quiz', array( 'Forminator_QForm_Front', 'ajax_reload_module' ) );
	}

	/**
	 * Register CPT
	 *
	 * @since 1.0
	 */
	public function register_cpt() {
		$labels = array(
			'name'          => $this->get_option( 'name' ),
			'singular_name' => $this->get_option( 'singular_name' )
		);

		$args = array(
			'labels'             => $labels,
			'description'        => $this->get_option( 'description' ),
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

		register_post_type( 'forminator_quizzes', $args );
	}

	/**
	 * Module options
	 *
	 * @since 1.0
	 * @return array
	 */
	public function options() {
		return array(
			'name'          => __( 'Quizzes', Forminator::DOMAIN ),
			'singular_name' => __( 'Quiz', Forminator::DOMAIN ),
			'description'   => __( "Create fun quizzes for your users to take and share on social media. A great way to drive more traffic to your site.", Forminator::DOMAIN ),
			'button_label'  => __( "New Quiz", Forminator::DOMAIN ),
			'icon'          => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="19" viewBox="0 0 18 19" preserveAspectRatio="none" class="wpmudev-icon wpmudev-i_quizzes"><path fill-rule="evenodd" d="M9 .125c1.242 0 2.41.234 3.507.703 1.096.47 2.05 1.11 2.865 1.925.815.814 1.456 1.77 1.925 2.865.47 1.096.703 2.265.703 3.507s-.234 2.41-.703 3.507c-.47 1.096-1.11 2.05-1.925 2.865-.814.815-1.77 1.456-2.865 1.925-1.096.47-2.265.703-3.507.703s-2.41-.234-3.507-.703c-1.096-.47-2.05-1.11-2.865-1.925-.815-.814-1.456-1.77-1.925-2.865C.233 11.536 0 10.367 0 9.125s.234-2.41.703-3.507c.47-1.096 1.11-2.05 1.925-2.865.814-.815 1.77-1.456 2.865-1.925C6.59.358 7.758.125 9 .125zm.72 13.87h-.017c.094-.095.17-.21.23-.344.057-.133.087-.27.087-.412 0-.152-.03-.296-.088-.43-.06-.135-.135-.25-.23-.343-.104-.094-.227-.17-.368-.23-.14-.057-.287-.087-.44-.087-.164 0-.316.03-.457.088-.14.06-.263.135-.37.23-.092.093-.17.207-.227.342-.06.134-.088.278-.088.43 0 .14.03.28.088.413.058.136.135.25.228.344.106.094.223.167.352.22.13.053.27.08.422.08h.105c.153 0 .293-.027.422-.08.128-.053.245-.126.35-.22zm2.163-6.276v.034c.094-.176.164-.358.21-.545.048-.19.07-.388.07-.6v-.157c0-.375-.072-.724-.22-1.046-.145-.322-.347-.6-.605-.835-.54-.48-1.307-.72-2.303-.72h-.193c-.41 0-.797.067-1.16.202-.364.134-.692.32-.985.553-.27.247-.48.542-.633.888-.152.346-.228.712-.228 1.1v.122h2.04v-.053c0-.152.028-.302.087-.448.058-.147.135-.278.228-.396.107-.105.23-.187.37-.246.14-.058.288-.088.44-.088h.035c.727 0 1.09.4 1.09 1.196 0 .14-.018.275-.053.404-.035.13-.088.252-.158.37-.117.163-.246.32-.387.474-.14.152-.293.3-.457.44-.187.15-.354.32-.5.51-.147.186-.267.386-.36.597-.083.222-.147.457-.194.703-.047.246-.07.492-.07.738 0 .035.002.064.008.088.006.023.01.047.01.07h1.792l.035-.44c.024-.234.09-.45.194-.65.106-.2.24-.38.405-.545l.56-.544c.19-.164.36-.342.51-.536.153-.193.294-.407.423-.64z"/></svg>'
		);
	}
}
