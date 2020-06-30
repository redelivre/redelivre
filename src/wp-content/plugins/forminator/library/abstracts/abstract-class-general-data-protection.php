<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_General_Data_Protection
 *
 * What it does :
 * - Hook WordPress Export Personal Data
 * - Hook WordPress Erase Personal Data
 * - Data Retention
 * - WordPress Policy Page
 *
 * @since 1.0.6
 */
abstract class Forminator_General_Data_Protection {

	/**
	 * Friendly name used
	 *
	 * @var string
	 */
	protected $name;

	protected $cron_cleanup_interval;

	protected $exporters = array();
	protected $erasers   = array();

	public function __construct( $name, $cron_cleanup_interval = 'hourly' ) {
		$this->name                  = $name;
		$this->cron_cleanup_interval = $cron_cleanup_interval;
		$this->init();
	}

	protected function init() {
		add_action( 'admin_init', array( $this, 'add_privacy_message' ) );
		add_filter( 'wp_privacy_personal_data_exporters', array( $this, 'register_exporters' ), 10 );
		add_filter( 'wp_privacy_personal_data_erasers', array( $this, 'register_erasers' ), 10 );

		// for data removal / anonymize data
		if ( ! wp_next_scheduled( 'forminator_general_data_protection_cleanup' ) ) {
			wp_schedule_event( time(), $this->get_cron_cleanup_interval(), 'forminator_general_data_protection_cleanup' );
		}

		add_action( 'forminator_general_data_protection_cleanup', array( $this, 'personal_data_cleanup' ) );

	}

	/**
	 * Add Privacy Messages
	 */
	public function add_privacy_message() {
		if ( function_exists( 'wp_add_privacy_policy_content' ) ) {
			$content = $this->get_privacy_message();
			if ( ! empty( $content ) ) {
				wp_add_privacy_policy_content( $this->name, $this->get_privacy_message() );
			}
		}
	}

	/**
	 * Privacy Message
	 *
	 * @return string
	 */
	public function get_privacy_message() {
		return '';
	}

	/**
	 * Append registered exporters to wp exporter
	 *
	 * @param array $exporters
	 *
	 * @return array
	 */
	public function register_exporters( $exporters = array() ) {
		foreach ( $this->exporters as $id => $exporter ) {
			$exporters[ $id ] = $exporter;
		}

		return $exporters;
	}

	/**
	 * Append registered eraser to wp eraser
	 *
	 * @param array $erasers
	 *
	 * @return array
	 */
	public function register_erasers( $erasers = array() ) {
		foreach ( $this->erasers as $id => $eraser ) {
			$erasers[ $id ] = $eraser;
		}

		return $erasers;
	}

	public function add_exporter( $id, $name, $callback ) {
		$this->exporters[ $id ] = array(
			'exporter_friendly_name' => $name,
			'callback'               => $callback,
		);

		return $this->exporters;
	}

	public function add_eraser( $id, $name, $callback ) {
		$this->erasers[ $id ] = array(
			'eraser_friendly_name' => $name,
			'callback'             => $callback,
		);

		return $this->erasers;
	}

	/**
	 * Get Interval
	 *
	 * @return string
	 */
	public function get_cron_cleanup_interval() {
		$cron_cleanup_interval = $this->cron_cleanup_interval;

		/**
		 * Filter interval to be used for cleanup process
		 *
		 * @since  1.0.6
		 *
		 * @params string $cron_cleanup_interval interval in string (daily,hourly, etc)
		 */
		$cron_cleanup_interval = apply_filters( 'forminator_general_data_cleanup_interval', $cron_cleanup_interval );

		return $cron_cleanup_interval;
	}

	/**
	 * Cleanup personal data
	 *
	 * @return bool
	 */
	public function personal_data_cleanup() {
		return false;
	}
}
