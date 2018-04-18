<?php

if( ! class_exists( 'SiteOrigin_Learn_Dialog' ) ) {
	
	class SiteOrigin_Learn_Dialog {
		
		private $lessons;
		const SUBMIT_URL = 'https://siteorigin.com/wp-admin/admin-ajax.php?action=lesson_signup_submit';
		
		function __construct() {
			$this->lessons = array();
			add_action( 'admin_footer', array( $this, 'admin_footer' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 100 );
		}
		
		public static function single() {
			static $single;
			
			return empty( $single ) ? $single = new self() : $single;
		}
		
		/**
		 * Add a course that we might display
		 *
		 * @param $id
		 * @param $data
		 */
		public function add_lessons( $id, $data ) {
			$this->lessons[ $id ] = $data;
		}
		
		/**
		 * Get all the available courses
		 *
		 * @return mixed|void
		 */
		public function get_lessons() {
			return apply_filters( 'siteorigin_learn_lessons', $this->lessons );
		}
		
		/**
		 * Add the dialog to the footer when this is setup
		 */
		public function admin_footer() {
			wp_localize_script( 'siteorigin-learn', 'soLearn', array(
				'lessons' => $this->get_lessons(),
			) );
			
			// These strings should be filtered and translated by the calling plugin.
			$dialog_strings = apply_filters( 'siteorigin_learn_strings', array(
				'watch_video'       => 'Watch Intro Video',
				'loaded_from_vimeo' => 'Loaded from Vimeo Servers',
				'valid_email'       => 'Please enter a valid email address.',
				
				'your_name'  => 'Your Name',
				'your_email' => 'Your Email',
				'sign_up'    => 'Sign Up',
				'close'      => 'Close',
			) );
			
			include plugin_dir_path( __FILE__ ) . 'tpl/dialog.php';
		}
		
		public function enqueue_scripts() {
			wp_enqueue_script( 'siteorigin-learn', plugin_dir_url( __FILE__ ) . 'js/learn' . ( WP_DEBUG ? '' : '.min' ) . '.js', array( 'jquery' ), false, true );
			wp_enqueue_style( 'siteorigin-learn', plugin_dir_url( __FILE__ ) . 'css/learn.css', array() );
		}
	}
	
}
