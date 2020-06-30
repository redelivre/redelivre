<?php

class Forminator_WP_Post_Autofill_Provider extends Forminator_Autofill_Provider_Abstract {

	protected $_slug       = 'wp_post';
	protected $_name       = 'WordPress Post';
	protected $_short_name = 'WP Post';

	/**
	 * @var self|null
	 */
	private static $_instance = null;

	/**
	 * @var WP_Post
	 */
	private $wp_post;

	/**
	 * Forminator_WP_Post_Autofill_Provider constructor.
	 */
	public function __construct() {

		$attributes_map = array(
			'id'        => array(
				'name'         => __( 'ID', Forminator::DOMAIN ),
				'value_getter' => array( $this, 'get_value_id' ),
			),
			'title'     => array(
				'name'         => __( 'Title', Forminator::DOMAIN ),
				'value_getter' => array( $this, 'get_value_title' ),
			),
			'permalink' => array(
				'name'         => __( 'Permalink', Forminator::DOMAIN ),
				'value_getter' => array( $this, 'get_value_permalink' ),
			),
		);

		$this->attributes_map = $attributes_map;

		$this->hook_to_fields();
	}

	/**
	 * @return int
	 */
	public function get_value_id() {
		return $this->wp_post->ID;
	}

	/**
	 * @return string
	 */
	public function get_value_title() {
		return $this->wp_post->post_title;
	}

	/**
	 * @return false|string
	 */
	public function get_value_permalink() {
		return get_permalink( $this->wp_post->ID );
	}

	/**
	 * @return bool
	 */
	public function is_enabled() {
		return true;
	}

	/**
	 * @return bool
	 */
	public function is_fillable() {
		if ( ! $this->wp_post instanceof WP_Post ) {
			return false;
		}

		return true;
	}

	/**
	 * @return Forminator_Autofill_Provider_Interface|Forminator_WP_Post_Autofill_Provider|null
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Init post
	 */
	public function init() {
		global $post;

		if ( $post instanceof WP_Post ) {
			$this->wp_post = get_post();
		} else {
			$wp_referer = wp_get_referer();
			if ( $wp_referer ) {
				$post_id = url_to_postid( $wp_referer );
				if ( $post_id ) {
					$post_object = get_post( $post_id );
					// make sure its wp_post
					if ( $post_object instanceof WP_Post ) {
						$this->wp_post = $post_object;
					}
				}
			}
		}
	}

	/**
	 * @return array
	 */
	public function get_attribute_to_hook() {
		return array(
			'text' => array(
				'wp_post.title',
			),
		);
	}
}
