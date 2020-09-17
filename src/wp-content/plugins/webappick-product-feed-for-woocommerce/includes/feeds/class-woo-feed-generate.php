<?php
/**
 * Class Woo_Generate_Feed
 */
class Woo_Generate_Feed {
	/**
	 * Provider Service Class
	 * @var Woo_Feed_Google|Woo_Feed_Pinterest|Woo_Feed_Facebook|Woo_Feed_Custom_XML|Woo_Feed_Custom
	 */
	public $service;
	
	/**
	 * Woo_Generate_Feed constructor.
	 *
	 * @param string $feedService
	 * @param array  $feedRule
	 */
	public function __construct( $feedService, $feedRule ) {
		$feedService   = woo_feed_get_merchant_class( $feedService );
		$this->service = new $feedService( $feedRule );
	}
	
	/**
	 * Get Product data
	 * @return array|bool|string
	 */
	public function getProducts() {
		return $this->service->returnFinalProduct();
	}
}