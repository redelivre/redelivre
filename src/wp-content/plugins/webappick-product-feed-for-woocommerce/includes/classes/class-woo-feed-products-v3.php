<?php /** @noinspection PhpUnusedPrivateMethodInspection, PhpUnused, PhpUnusedLocalVariableInspection, DuplicatedCode */

/**
 * Created by PhpStorm.
 * User: wahid
 * Date: 11/16/19
 * Time: 5:10 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Woo_Feed_Products_v3
 */
class Woo_Feed_Products_v3 {
	/**
	 * The Increment
	 * @var int
	 */
	protected $pi = 0;
	/**
	 * Feed file headers
	 * @var string|array
	 */
	public $feedHeader;
	/**
	 * Feed File Body
	 * @var string|array
	 */
	public $feedBody;
	/**
	 * Feed file footer
	 * @var string|array
	 */
	public $feedFooter;
	/**
	 * CSV|TXT column (text|word) enclosure
	 * @var string
	 */
	protected $enclosure;
	/**
	 * CSV|TXT column delimiter
	 * @var string
	 */
	protected $delimiter;
	/**
	 * Feed Rules
	 * @var array
	 */
	protected $config;
	/**
	 * Post status to query
	 * @var string
	 */
	protected $post_status = 'publish';
	/**
	 * Processed Products
	 * @var array
	 */
	public $products = [];
	/**
	 * Query Method Selector
	 * @var string
	 */
	protected $queryType = 'wp';
	/**
	 * Google shipping tax attributes
	 * @var array
	 */
	protected $google_shipping_tax = array(
		'shipping_country',
		'shipping_region',
		'shipping_service',
		'shipping_price',
		'tax_country',
		'tax_region',
		'tax_rate',
		'tax_ship',
		'installment_months',
		'installment_amount',
		'subscription_period',
		'subscription_period_length',
		'subscription_amount',
	);
	
	/**
	 * Attribute to skip in attribute loop for processing separately
	 * @var array
	 */
	protected $skipped_merchant_attributes = array(
		'google'   => array(
			'shipping_country',
			'shipping_region',
			'shipping_service',
			'shipping_price',
			'tax_country',
			'tax_region',
			'tax_rate',
			'tax_ship',
			'installment_months',
			'installment_amount',
			'subscription_period',
			'subscription_period_length',
			'subscription_amount',
		),
		'facebook' => array(
			'shipping_country',
			'shipping_region',
			'shipping_service',
			'shipping_price',
			'tax_country',
			'tax_region',
			'tax_rate',
			'tax_ship',
			'installment_months',
			'installment_amount',
			'subscription_period',
			'subscription_period_length',
			'subscription_amount',
		),
	);
	
	/**
	 * Already Processed merchant attributes by the attribute loop
	 * this will ensure unique merchant attribute.
	 * @see Woo_Feed_Products_v3::exclude_current_attribute()
	 * @var array
	 */
	protected $processed_merchant_attributes = array();
	
	/**
	 * Product types for query
	 * @var array
	 */
	protected $product_types = array(
		'simple',
		'variable',
		'variation',
		'grouped',
		'external',
	);
	
	// TODO check if product is in those above types.
	/**
	 * Post meta prefix for dropdown item
	 * @since 3.1.18
	 * @var string
	 */
	const POST_META_PREFIX = 'wf_cattr_';
	/**
	 * Product Attribute (taxonomy & local) Prefix
	 * @since 3.1.18
	 * @var string
	 */
	const PRODUCT_ATTRIBUTE_PREFIX = 'wf_attr_';
	/**
	 * Product Taxonomy Prefix
	 * @since 3.1.18
	 * @var string
	 */
	const PRODUCT_TAXONOMY_PREFIX = 'wf_taxo_';
	
	public function __construct( $config ) {
		$this->config    = woo_feed_parse_feed_rules( $config );
		$this->queryType = strtolower( get_option( 'woo_feed_product_query_type', 'wc' ) );
		if ( ! in_array( $this->queryType, [ 'wc', 'wp', 'both' ] ) ) {
			$this->queryType = 'wc';
		}
		
		$this->config['itemWrapper']  = str_replace( ' ', '_', $this->config['itemWrapper'] );
		$this->config['itemsWrapper'] = str_replace( ' ', '_', $this->config['itemsWrapper'] );

//		woo_feed_log_feed_process( $this->config['filename'], sprintf( 'Current Query Type is %s', $this->queryType ) );
	}
	
	/**
	 * Get Products using WC_Product_Query
	 *
	 * @return array
	 */
	public function get_wc_query_products() {
		
		// Query Arguments
		$arg = array(
			'limit'            => 2e3,
			'status'           => $this->post_status,
			'orderby'          => 'date',
			'order'            => 'DESC',
			'return'           => 'ids',
			'suppress_filters' => false,
		);
		
		// Product Type
		$arg['type'] = array( 'simple', 'variable', 'grouped', 'external' );
		$query       = new WC_Product_Query( $arg );
//		if( woo_feed_is_debugging_enabled() ) {
//			woo_feed_log_feed_process( $this->config['filename'], sprintf( 'WC_Product_Query Args ::'.PHP_EOL.'%s', print_r( $arg, true ) ) );
//		}
		return $query->get_products();
	}
	
	/**
	 * Get Products using WP_Query
	 *
	 * @return array
	 */
	public function get_wp_query_products() {
		// Query Arguments
		$args = array(
			'posts_per_page'         => 2e3, // phpcs:ignore
			'post_type'              => 'product',
			'post_status'            => 'publish',
			'order'                  => 'DESC',
			'fields'                 => 'ids',
			'cache_results'          => false,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'suppress_filters'       => false,
		);
		
		$query = new WP_Query( $args );
//		if( woo_feed_is_debugging_enabled() ) {
//			woo_feed_log_feed_process( $this->config['filename'], sprintf( 'WP_Query Args ::'.PHP_EOL.'%s', print_r( $args, true ) ) );
//			woo_feed_log_feed_process( $this->config['filename'], sprintf( 'WP_Query Request ::'.PHP_EOL.'%s', $query->request ) );
//		}
		return $query->get_posts();
	}
	
	/**
	 * Get products
	 *
	 * @return array
	 */
	public function query_products() {
		$products = [];
		if ( 'wc' == $this->queryType ) {
			$products = $this->get_wc_query_products();
		} elseif ( 'wp' == $this->queryType ) {
			$products = $this->get_wp_query_products();
		} elseif ( 'both' == $this->queryType ) {
			$wc       = $this->get_wc_query_products();
			$wp       = $this->get_wp_query_products();
			$products = array_unique( array_merge( $wc, $wp ) );
		}
		
		return $products;
	}
	
	/**
	 * Organize Feed Attribute config
	 * @return array|bool
	 */
	public function get_attribute_config() {
		if ( empty( $this->config ) ) {
			return false;
		}
		
		$attributeConfig    = array();
		$merchantAttributes = $this->config['mattributes'];
		if ( ! empty( $merchantAttributes ) ) {
			$i = 0;
			foreach ( $merchantAttributes as $key => $value ) {
				$attributeConfig[ $i ]['mattributes'] = $value;
				$attributeConfig[ $i ]['prefix']      = $this->config['prefix'][ $key ];
				$attributeConfig[ $i ]['type']        = $this->config['type'][ $key ];
				$attributeConfig[ $i ]['attributes']  = $this->config['attributes'][ $key ];
				$attributeConfig[ $i ]['default']     = $this->config['default'][ $key ];
				$attributeConfig[ $i ]['suffix']      = $this->config['suffix'][ $key ];
				$attributeConfig[ $i ]['output_type'] = $this->config['output_type'][ $key ];
				$attributeConfig[ $i ]['limit']       = $this->config['limit'][ $key ];
				$i ++;
			}
		}
		
		return $attributeConfig;
	}
	
	/**
	 * Get Product Information according to feed config
	 *
	 * @param int[] $productIds
	 *
	 * @return array
	 * @since 3.2.0
	 *
	 */
	public function get_products( $productIds ) {
		
		if ( empty( $productIds ) ) {
			return [];
		}
		
		/**
		 * Fires before looping through request product for getting product data
		 *
		 * @param int[] $productIds
		 * @param array $feedConfig
		 *
		 * @since 3.2.10
		 */
		do_action( 'woo_feed_before_product_loop', $productIds, $this->config );
		
		foreach ( $productIds as $key => $pid ) {
//			woo_feed_log_feed_process( $this->config['filename'], sprintf( 'Loading Product Data For %d.', $pid ) );
			$product = wc_get_product( $pid );
			
			if ( $this->exclude_from_loop( $product ) ) {
				continue;
			}
			
			if ( $this->process_variation( $product ) ) {
				continue;
			}

//			woo_feed_log_feed_process( $this->config['filename'], 'Formatting Feed Data...' );
			
			// Add Single item wrapper before product info loop start
			if ( 'xml' == $this->config['feedType'] ) {
				$this->feedBody .= "\n";
				$this->feedBody .= '<' . $this->config['itemWrapper'] . '>';
				$this->feedBody .= "\n";
			}
			
			// reset processed attribute list before loop
			$this->processed_merchant_attributes = [];
			// Process attribute values
			$this->process_attributes( $product );

//			try {
//				woo_feed_log_feed_process( $this->config['filename'], 'Processing Merchant Specific Fields' );
			// Process feed data for uncommon merchant feed like Google,Facebook,Pinterest
			$this->process_for_merchant( $product, $this->pi );
//			} catch ( Exception $e ) {
//				$message = 'Error Processing Merchant Specific Fields.' . PHP_EOL . 'Caught Exception :: ' . $e->getMessage();
//				woo_feed_log( $this->config['filename'], $message, 'critical', $e, true );
//				woo_feed_log_fatal_error( $message, $e );
//			}
			
			if ( 'xml' == $this->config['feedType'] ) {
				if ( empty( $this->feedHeader ) ) {
					$this->feedHeader = $this->process_xml_feed_header();
					$this->feedFooter = $this->process_xml_feed_footer();
				}
				
				$this->feedBody .= '</' . $this->config['itemWrapper'] . '>';
				
			} elseif ( 'txt' == $this->config['feedType'] ) {
				if ( empty( $this->feedHeader ) ) {
					$this->process_txt_feed_header();
				}
				$this->process_txt_feed_body();
			} else {
				if ( empty( $this->feedHeader ) ) {
					$this->process_csv_feed_header();
				}
				$this->process_csv_feed_body();
			}
//			woo_feed_log_feed_process( $this->config['filename'], 'Done Formatting...' );
			$this->pi ++;
		}
		
		/**
		 * Fires after looping through request product for getting product data
		 *
		 * @param int[] $productIds
		 * @param array $feedConfig
		 *
		 * @since 3.2.10
		 */
		do_action( 'woo_feed_after_product_loop', $productIds, $this->config );
		
		return $this->products;
	}
	
	/**
	 * Process product variations
	 * @param WC_Abstract_Legacy_Product $product
	 *
	 * @return bool
	 * @since 3.3.9
	 */
	protected function process_variation( $product ) {
		// Apply variable and variation settings
		if ( $product->is_type( 'variable' ) && $product->has_child() ) {
			$this->pi ++;
			$variations = $product->get_visible_children();
			if ( is_array( $variations ) && ( sizeof( $variations ) > 0 ) ) {
//				if( woo_feed_is_debugging_enabled() ) {
//					woo_feed_log_feed_process( $this->config['filename'], sprintf( 'Getting Variation Product(s) :: %s', implode( ', ', $variations ) ) );
//				}
				$this->get_products( $variations );
			}
		}
		return false;
	}
	
	/**
	 * Process The Attributes and assign value to merchant attribute
	 *
	 * @param WC_Abstract_Legacy_Product $product
	 *
	 * @return void
	 * @since 3.3.9
	 */
	protected function process_attributes( $product ) {
		// Get Product Attribute values by type and assign to product array
		foreach ( $this->config['attributes'] as $attr_key => $attribute ) {
			
			$merchant_attribute = $this->config['mattributes'][ $attr_key ];
			
			if ( $this->exclude_current_attribute( $product, $merchant_attribute, $attribute ) ) {
				continue;
			}
			
			// Add Prefix and Suffix into Output
			$prefix   = $this->config['prefix'][ $attr_key ];
			$suffix   = $this->config['suffix'][ $attr_key ];
			$merchant = $this->config['provider'];
			$feedType = $this->config['feedType'];
			
			if ( 'pattern' == $this->config['type'][ $attr_key ] ) {
				$attributeValue = $this->config['default'][ $attr_key ];
			} else { # Get Pattern value
				$attributeValue = $this->getAttributeValueByType( $product, $attribute );
			}
			
			// Format Output according to Output Type config.
			$outputType = $this->config['output_type'][ $attr_key ];
			$attributeValue = $this->format_output( $attributeValue, $this->config['output_type'][ $attr_key ], $product, $attribute );
			
			// Limit Output.
			$attributeValue = $this->crop_string( $attributeValue, 0, $this->config['limit'][ $attr_key ] );
			
			// Process prefix and suffix.
			$attributeValue = $this->process_prefix_suffix( $attributeValue, $prefix, $suffix, $attribute );
			
			if ( 'xml' == $feedType ) {
				
				// Replace XML Nodes according to merchant requirement.
				$getReplacedAttribute = woo_feed_replace_to_merchant_attribute( $merchant_attribute, $merchant, $feedType );
				
				// XML does not support space in node. So replace Space with Underscore.
				$getReplacedAttribute = str_replace( ' ', '_', $getReplacedAttribute );
				
				if ( ! empty( $attributeValue ) ) {
					$attributeValue = trim( $attributeValue );
				}
				
				// Add closing XML node if value is empty
				if ( '' != $attributeValue ) {
					# Add CDATA wrapper for XML feed to prevent XML error.
					$attributeValue = woo_feed_add_cdata( $merchant_attribute, $attributeValue, $merchant );
					
					#TODO Move to proper place
					# Replace Google Color attribute value according to requirements
					if ( 'g:color' == $getReplacedAttribute ) {
						$attributeValue = str_replace( ', ', '/', $attributeValue );
					}
					
					# Strip slash from output
					$attributeValue = stripslashes( $attributeValue );
					
					$this->feedBody .= '<' . $getReplacedAttribute . '>' . "$attributeValue" . '</' . $getReplacedAttribute . '>';
					$this->feedBody .= "\n";
					
				} else {
					$this->feedBody .= '<' . $getReplacedAttribute . '/>';
					$this->feedBody .= "\n";
				}
			} elseif ( 'csv' == $feedType ) {
				$merchant_attribute = woo_feed_replace_to_merchant_attribute( $merchant_attribute, $merchant, $feedType );
				$merchant_attribute = $this->processStringForCSV( $merchant_attribute );
				$attributeValue  = $this->processStringForCSV( $attributeValue );
			} elseif ( 'txt' == $feedType ) {
				$merchant_attribute = woo_feed_replace_to_merchant_attribute( $merchant_attribute, $merchant, $feedType );
				$merchant_attribute = $this->processStringForTXT( $merchant_attribute );
				$attributeValue  = $this->processStringForTXT( $attributeValue );
			}
			
			$this->products[ $this->pi ][ $merchant_attribute ] = $attributeValue;
		}
	}
	
	/**
	 * Check if current product should be processed for feed
	 * This should be using by Woo_Feed_Products_v3::get_products()
	 *
	 * @param WC_Product $product
	 *
	 * @return bool
	 * @since 3.3.9
	 *
	 */
	protected function exclude_from_loop( $product ) {
		// For WP_Query check available product types
		if ( 'wp' == $this->queryType && ! in_array( $product->get_type(), $this->product_types ) ) {
//			woo_feed_log_feed_process( $this->config['filename'], sprintf( 'Skipping Product :: Invalid Post/Product Type : %s.', $product->get_type() ) );
			return true;
		}
		
		// Skip for invalid products
		if ( ! is_object( $product ) ) {
//			woo_feed_log_feed_process( $this->config['filename'], 'Skipping Product :: Product data is not a valid WC_Product object.' );
			return true;
		}
		
		// Skip for invisible products
		if ( ! $product->is_visible() ) {
//			woo_feed_log_feed_process( $this->config['filename'], 'Skipping Product :: Product is not visible.' );
			return true;
		}
		return false;
	}
	
	/**
	 * Check if current attribute/merchant attribute should be processed for feed
	 * This should be using by Woo_Feed_Products_v3::get_products()
	 *
	 * @param WC_Product $product
	 * @param string $merchant_attribute
	 * @param string $product_attribute
	 * @param string $feedType
	 *
	 * @return bool
	 *
	 * @since 3.3.9
	 *
	 */
	protected function exclude_current_attribute( $product, $merchant_attribute, $product_attribute, $feedType = 'xml' ) {
		if (
			$feedType == $this->config['feedType'] &&
			in_array( $this->config['provider'], array_keys( $this->skipped_merchant_attributes ) ) &&
			in_array( $merchant_attribute, $this->skipped_merchant_attributes[ $this->config['provider'] ] )
		
		) {
			return true;
		}
		
		if ( in_array( $merchant_attribute, $this->processed_merchant_attributes ) ) {
			return true;
		}
		
		$this->processed_merchant_attributes[] = $merchant_attribute;
		
		return false;
	}
	
	/**
	 * Wrapper for substr with <![CDATA[string]]> support
	 *
	 * @see substr
	 *
	 * @param string $string
	 * @param int $start
	 * @param int $limit
	 *
	 * @return string
	 */
	protected function crop_string( $string, $start = 0, $limit = null ) {
		$limit = absint( $limit );
		if ( $limit > 0 ) {
			$start = absint( $start );
			if ( strpos( $string, '<![CDATA[' ) !== false ) {
				$string = str_replace( array( '<![CDATA[', ']]>' ), array( '', '' ), $string );
				$string = substr( $string, $start, $limit );
				$string = '<![CDATA[' . $string . ']]>';
			} else {
				$string = substr( $string, $start, $limit );
			}
		}
		return $string;
	}
	
	/**
	 * Process feed data according to merchant uncommon requirements like Google
	 *
	 * @param $productObj WC_Product
	 * @param $index | Product Index
	 *
	 * @since 3.2.0
	 *
	 */
	protected function process_for_merchant( $productObj, $index ) {
		$product            = $this->products[ $index ];
		$merchantAttributes = $this->config['mattributes'];
		
		// Format Shipping and Tax data for CSV and TXT feed only for google and facebook
		if ( 'xml' != $this->config['feedType'] && in_array( $this->config['provider'], [ 'google', 'facebook' ] ) ) {
			
			$shipping     = array();
			$tax          = array();
			$installment  = array();
			$s            = 0; // Shipping Index
			$t            = 0; // Tax Index
			$i            = 0; // Installment Index
			$shippingAttr = array(
				'shipping_country',
				'shipping_service',
				'shipping_price',
				'shipping_region',
				'tax_country',
				'tax_region',
				'tax_rate',
				'tax_ship',
			);
			foreach ( $this->products[ $this->pi ] as $attribute => $value ) {
				if ( in_array( $attribute, $shippingAttr ) ) {
					
					if ( 'tax_country' == $attribute ) {
						$t ++;
						$tax[ $t ] .= $value . ':';
					} elseif ( 'tax_region' == $attribute ) {
						$tax[ $t ] .= $value . ':';
					} elseif ( 'tax_rate' == $attribute ) {
						$tax[ $t ] .= $value . ':';
					} elseif ( 'tax_ship' == $attribute ) {
						$tax[ $t ] .= $value . ':';
					}
					
					if ( 'shipping_country' == $attribute ) {
						$s ++;
						$shipping[ $s ] .= $value . ':';
					} elseif ( 'shipping_service' == $attribute ) {
						$shipping[ $s ] .= $value . ':';
					} elseif ( 'shipping_price' == $attribute ) {
						$shipping[ $s ] .= $value . ':';
					} elseif ( 'shipping_region' == $attribute ) {
						$shipping[ $s ] .= $value . ':';
					}
					
					unset( $this->products[ $this->pi ][ $attribute ] );
				}
			}
			
			foreach ( $shipping as $key => $val ) {
				$this->products[ $this->pi ]['shipping(country:region:service:price)'] = $val;
			}
			
			foreach ( $tax as $key => $val ) {
				$this->products[ $this->pi ]['tax(country:region:rate:tax_ship)'] = $val;
			}
		}
		
		if ( 'google' == $this->config['provider'] ) {
			
			// Reformat Shipping attributes for google, facebook
			$s        = 0;
			$t        = 0;
			$tax      = '';
			$shipping = '';
			if ( 'xml' == $this->config['feedType'] ) {
				foreach ( $merchantAttributes as $key => $value ) {
					
					if ( ! in_array( $value, $this->google_shipping_tax ) ) {
						continue;
					}
					
					$prefix = $this->config['prefix'][ $key ];
					$suffix = $this->config['suffix'][ $key ];
					
					if ( 'pattern' == $this->config['type'][ $key ] ) {
						$output = $this->config['default'][ $key ];
					} else { // Get Pattern value.
						$attribute = $this->config['attributes'][ $key ];
						$output    = $this->getAttributeValueByType( $productObj, $attribute );
					}
					
					if ( false !== strpos( $value, 'price' ) || false !== strpos( $value, 'rate' ) ) {
						$suffix = '' . $suffix;
					}
					
					
					$output = $prefix . $output . $suffix;
					
					if ( 'shipping_country' == $value ) {
						if ( 0 == $s ) {
							$shipping .= '<g:shipping>';
							$s        = 1;
						} else {
							$shipping .= '</g:shipping>' . "\n";
							$shipping .= '<g:shipping>';
						}
					} elseif ( ! in_array( 'shipping_country', $merchantAttributes ) && 'shipping_price' == $value ) {
						if ( 0 == $s ) {
							$shipping .= '<g:shipping>';
							$s        = 1;
						} else {
							$shipping .= '</g:shipping>' . "\n";
							$shipping .= '<g:shipping>';
						}
					}
					
					if ( 'shipping_country' == $value ) {
						$shipping .= '<g:country>' . $output . '</g:country>' . "\n";
					} elseif ( 'shipping_region' == $value ) {
						$shipping .= '<g:region>' . $output . '</g:region>' . "\n";
					} elseif ( 'shipping_service' == $value ) {
						$shipping .= '<g:service>' . $output . '</g:service>' . "\n";
					} elseif ( 'shipping_price' == $value ) {
						$shipping .= '<g:price>' . $output . '</g:price>' . "\n";
					} elseif ( 'tax_country' == $value ) {
						if ( 0 == $t ) {
							$tax .= '<g:tax>';
							$t   = 1;
						} else {
							$tax .= '</g:tax>' . "\n";
							$tax .= '<g:tax>';
						}
						$tax .= '<g:country>' . $output . '</g:country>' . "\n";
					} elseif ( 'tax_region' == $value ) {
						$tax .= '<g:region>' . $output . '</g:region>' . "\n";
					} elseif ( 'tax_rate' == $value ) {
						$tax .= '<g:rate>' . $output . '</g:rate>' . "\n";
					} elseif ( 'tax_ship' == $value ) {
						$tax .= '<g:tax_ship>' . $output . '</g:tax_ship>' . "\n";
					}
				}
				
				if ( 1 == $s ) {
					$shipping .= '</g:shipping>';
				}
				if ( 1 == $t ) {
					$tax .= '</g:tax>';
				}
				
				$this->feedBody .= $shipping;
				$this->feedBody .= $tax;
			}
			// ADD g:identifier_exists
			$identifier      = array( 'brand', 'upc', 'sku', 'mpn', 'gtin' );
			$countIdentifier = 0;
			if ( ! in_array( 'identifier_exists', $merchantAttributes ) ) {
				if ( count( array_intersect_key( array_flip( $identifier ), $product ) ) >= 2 ) {
					// Any 2 required keys exist!
					// @TODO Refactor with OR
					if ( array_key_exists( 'brand', $product ) && ! empty( $product['brand'] ) ) {
						$countIdentifier ++;
					}
					if ( array_key_exists( 'upc', $product ) && ! empty( $product['upc'] ) ) {
						$countIdentifier ++;
					}
					if ( array_key_exists( 'sku', $product ) && ! empty( $product['sku'] ) ) {
						$countIdentifier ++;
					}
					if ( array_key_exists( 'mpn', $product ) && ! empty( $product['mpn'] ) ) {
						$countIdentifier ++;
					}
					if ( array_key_exists( 'gtin', $product ) && ! empty( $product['gtin'] ) ) {
						$countIdentifier ++;
					}
				}
				
				if ( 'xml' == $this->config['feedType'] ) {
					if ( $countIdentifier >= 2 ) {
						$this->feedBody .= "<g:identifier_exists>yes</g:identifier_exists>";
					} else {
						$this->feedBody .= "<g:identifier_exists>no</g:identifier_exists>";
					}
				} else {
					if ( $countIdentifier >= 2 ) {
						$this->products[ $this->pi ]['identifier exists'] = "yes";
					} else {
						$this->products[ $this->pi ]['identifier exists'] = "no";
					}
				}
			}
		}
	}
	
	/**
	 * Generate TXT Feed Header
	 *
	 * @return string
	 * @since 3.2.0
	 *
	 */
	protected function process_txt_feed_header() {
		// Set Delimiter
		if ( 'tab' == $this->config['delimiter'] ) {
			$this->delimiter = "\t";
		} else {
			$this->delimiter = $this->config['delimiter'];
		}
		
		// Set Enclosure
		if ( ! empty( $this->config['enclosure'] ) ) {
			$this->enclosure = $this->config['enclosure'];
			if ( 'double' == $this->enclosure ) {
				$this->enclosure = '"';
			} elseif ( 'single' == $this->enclosure ) {
				$this->enclosure = "'";
			} else {
				$this->enclosure = '';
			}
		} else {
			$this->enclosure = '';
		}
		
		$eol = PHP_EOL;
		if ( 'trovaprezzi' === $this->config['provider'] ) {
			$eol = '<endrecord>' . PHP_EOL;
		}
		
		$product          = $this->products[ $this->pi ];
		$headers          = array_keys( $product );
		$this->feedHeader .= $this->enclosure . implode( "$this->enclosure$this->delimiter$this->enclosure", $headers ) . $this->enclosure . $eol;
		
		return $this->feedHeader;
	}
	
	/**
	 * Generate TXT Feed Body
	 *
	 * @return string
	 * @since 3.2.0
	 *
	 */
	protected function process_txt_feed_body() {
		$productInfo = array_values( $this->products[ $this->pi ] );
		$eol         = PHP_EOL;
		if ( 'trovaprezzi' === $this->config['provider'] ) {
			$eol = '<endrecord>' . PHP_EOL;
		}
		$this->feedBody .= $this->enclosure . implode( "$this->enclosure$this->delimiter$this->enclosure", $productInfo ) . $this->enclosure . $eol;
		
		return $this->feedBody;
	}
	
	/**
	 * Generate CSV Feed Header
	 *
	 * @return array
	 * @since 3.2.0
	 *
	 */
	protected function process_csv_feed_header() {
		// Set Delimiter
		if ( 'tab' == $this->config['delimiter'] ) {
			$this->delimiter = "\t";
		} else {
			$this->delimiter = $this->config['delimiter'];
		}
		
		// Set Enclosure
		if ( ! empty( $this->config['enclosure'] ) ) {
			$this->enclosure = $this->config['enclosure'];
			if ( 'double' == $this->enclosure ) {
				$this->enclosure = '"';
			} elseif ( 'single' == $this->enclosure ) {
				$this->enclosure = "'";
			} else {
				$this->enclosure = '';
			}
		} else {
			$this->enclosure = '';
		}
		
		$product          = $this->products[ $this->pi ];
		$this->feedHeader = array_keys( $product );
		
		return $this->feedHeader;
	}
	
	/**
	 * Generate CSV Feed Body
	 * @return array
	 * @since 3.2.0
	 */
	protected function process_csv_feed_body() {
		$product          = $this->products[ $this->pi ];
		$this->feedBody[] = array_values( $product );
		
		return $this->feedBody;
	}
	
	/**
	 * Make XML feed header
	 * @return string
	 * @since 3.2.0
	 */
	protected function process_xml_feed_header() {
		$datetime_now = gmdate( 'Y-m-d H:i:s' );
		if ( 'fruugo.au' == $this->config["provider"] ) {
			return "<products version=\"1.0\" standalone=\"yes\">
                <datetime>$datetime_now</datetime>
                <title>" . get_bloginfo( 'name' ) . "</title>
                <link>" . get_bloginfo( 'url' ) . "</link>
                <description>" . get_bloginfo( 'description' ) . "</description>";
		} elseif ( 'zap.co.il' == $this->config['provider'] ) {
			return "<STORE>
                <datetime>$datetime_now</datetime>
                <title>" . get_bloginfo( 'name' ) . "</title>
                <link>" . get_bloginfo( 'url' ) . "</link>
                <description>" . get_bloginfo( 'description' ) . "</description>
                <agency>" . get_bloginfo( 'name' ) . "</agency>
                <email>" . get_bloginfo( 'admin_email' ) . "</email>";
			
		} elseif ( 'myshopping.com.au' == $this->config['provider'] ) {
			return "<productset>";
		} elseif ( 'stylight.com' == $this->config['provider'] ) {
			return "<products version=\"1.0\" standalone=\"yes\">
                <datetime>$datetime_now</datetime>
                <title>" . get_bloginfo( 'name' ) . "</title>
                <link>" . get_bloginfo( 'url' ) . "</link>
                <description>" . get_bloginfo( 'description' ) . "</description>";
		} elseif ( 'nextad' == $this->config['provider'] ) {
			return "<products version=\"1.0\" standalone=\"yes\">
                <datetime>$datetime_now</datetime>
                <title>" . get_bloginfo( 'name' ) . "</title>
                <link>" . get_bloginfo( 'url' ) . "</link>
                <description>" . get_bloginfo( 'description' ) . "</description>";
		} elseif ( 'skinflint.co.uk' == $this->config['provider'] ) {
			return "<products version=\"1.0\" standalone=\"yes\">
                <datetime>$datetime_now</datetime>
                <title>" . get_bloginfo( 'name' ) . "</title>
                <link>" . get_bloginfo( 'url' ) . "</link>
                <description>" . get_bloginfo( 'description' ) . "</description>";
		} elseif ( 'comparer.be' == $this->config['provider'] ) {
			return "<products version=\"1.0\" standalone=\"yes\">
                <datetime>$datetime_now</datetime>
                <title>" . get_bloginfo( 'name' ) . "</title>
                <link>" . get_bloginfo( 'url' ) . "</link>
                <description>" . get_bloginfo( 'description' ) . "</description>";
		} elseif ( 'dooyoo' == $this->config['provider'] ) {
			return "<products version=\"1.0\" standalone=\"yes\">
                <datetime>$datetime_now</datetime>
                <title>" . get_bloginfo( 'name' ) . "</title>
                <link>" . get_bloginfo( 'url' ) . "</link>
                <description>" . get_bloginfo( 'description' ) . "</description>";
		} elseif ( 'hintaseuranta.fi' == $this->config['provider'] ) {
			return "<products version=\"1.0\" standalone=\"yes\">
                <datetime>$datetime_now</datetime>
                <title>" . get_bloginfo( 'name' ) . "</title>
                <link>" . get_bloginfo( 'url' ) . "</link>
                <description>" . get_bloginfo( 'description' ) . "</description>";
		} elseif ( 'incurvy' == $this->config['provider'] ) {
			return "<products version=\"1.0\" standalone=\"yes\">
                <datetime>$datetime_now</datetime>
                <title>" . get_bloginfo( 'name' ) . "</title>
                <link>" . get_bloginfo( 'url' ) . "</link>
                <description>" . get_bloginfo( 'description' ) . "</description>";
		} elseif ( 'kijiji.ca' == $this->config['provider'] ) {
			return "<products version=\"1.0\" standalone=\"yes\">
                <datetime>$datetime_now</datetime>
                <title>" . get_bloginfo( 'name' ) . "</title>
                <link>" . get_bloginfo( 'url' ) . "</link>
                <description>" . get_bloginfo( 'description' ) . "</description>";
		} elseif ( 'marktplaats.nl' == $this->config['provider'] ) {
			return "<products version=\"1.0\" standalone=\"yes\">
                <datetime>$datetime_now</datetime>
                <title>" . get_bloginfo( 'name' ) . "</title>
                <link>" . get_bloginfo( 'url' ) . "</link>
                <description>" . get_bloginfo( 'description' ) . "</description>";
		} elseif ( 'rakuten.de' == $this->config['provider'] ) {
			return "<products version=\"1.0\" standalone=\"yes\">
                <datetime>$datetime_now</datetime>
                <title>" . get_bloginfo( 'name' ) . "</title>
                <link>" . get_bloginfo( 'url' ) . "</link>
                <description>" . get_bloginfo( 'description' ) . "</description>";
		} elseif ( 'shopalike.fr' == $this->config['provider'] ) {
			return "<products version=\"1.0\" standalone=\"yes\">
                <datetime>$datetime_now</datetime>
                <title>" . get_bloginfo( 'name' ) . "</title>
                <link>" . get_bloginfo( 'url' ) . "</link>
                <description>" . get_bloginfo( 'description' ) . "</description>";
		} elseif ( 'spartoo.fi' == $this->config['provider'] ) {
			return "<products version=\"1.0\" standalone=\"yes\">
                <datetime>$datetime_now</datetime>
                <title>" . get_bloginfo( 'name' ) . "</title>
                <link>" . get_bloginfo( 'url' ) . "</link>
                <description>" . get_bloginfo( 'description' ) . "</description>";
		} elseif ( 'webmarchand' == $this->config['provider'] ) {
			return "<products version=\"1.0\" standalone=\"yes\">
                <datetime>$datetime_now</datetime>
                <title>" . get_bloginfo( 'name' ) . "</title>
                <link>" . get_bloginfo( 'url' ) . "</link>
                <description>" . get_bloginfo( 'description' ) . "</description>";
		} else {
			$wrapper     = $this->config['itemsWrapper'];
			$extraHeader = $this->config['extraHeader'];
			
			$output = '<?xml version="1.0" encoding="UTF-8" ?>';
			$output .= "\n";
			$output .= '<' . $wrapper . '>';
			
			// $output .= "\n";
			//if ( ! empty( $extraHeader ) ) {
			//$output .= $extraHeader;
			//$output .= "\n";
			//}
			
			return $output;
		}
	}
	
	/**
	 * Make XML feed header
	 * @return string
	 * @since 3.2.0
	 */
	protected function process_xml_feed_footer() {
		if ( in_array( $this->config['provider'],
			[
				'fruugo.au',
				'stylight.com',
				'nextad',
				'skinflint.co.uk',
				'comparer.be',
				'dooyoo',
				'hintaseuranta.fi',
				'incurvy',
				'kijiji.ca',
				'marktplaats.nl',
				'rakuten.de',
				'shopalike.fr',
				'spartoo.fi',
				'webmarchand',
			] ) ) {
			return '</products>';
		} elseif ( 'zap.co.il' == $this->config['provider'] ) {
			return '</STORE>';
		} elseif ( 'myshopping.com.au' == $this->config['provider'] ) {
			return '</productset>';
		} else {
			$wrapper = $this->config['itemsWrapper'];
			$footer  = "\n";
			$footer  .= "</$wrapper>";
			
			return $footer;
		}
	}
	
	/**
	 * Process string for TXT CSV Feed
	 *
	 * @param $string
	 *
	 * @return mixed|string
	 * @since 3.2.0
	 *
	 */
	protected function processStringForTXT( $string ) {
		if ( ! empty( $string ) ) {
			$string = html_entity_decode( $string, ENT_HTML401 | ENT_QUOTES ); // Convert any HTML entities
			
			if ( stristr( $string, '"' ) ) {
				$string = str_replace( '"', '""', $string );
			}
			$string = str_replace( "\n", ' ', $string );
			$string = str_replace( "\r", ' ', $string );
			$string = str_replace( "\t", ' ', $string );
			$string = trim( $string );
			$string = stripslashes( $string );
			
			return $string;
		} elseif ( '0' == $string ) {
			return '0';
		} else {
			return '';
		}
	}
	
	/**
	 * Process string for CSV
	 *
	 * @param $string
	 *
	 * @return mixed|string
	 * @since 3.2.0
	 *
	 */
	protected function processStringForCSV( $string ) {
		if ( ! empty( $string ) ) {
			$string = str_replace( "\n", ' ', $string );
			$string = str_replace( "\r", ' ', $string );
			$string = trim( $string );
			$string = stripslashes( $string );
			
			return $string;
		} elseif ( '0' == $string ) {
			return '0';
		} else {
			return '';
		}
	}
	
	/**
	 * Get Product Attribute Value by Type
	 *
	 * @param $product  WC_Product
	 * @param $attribute
	 *
	 * @return mixed|string
	 * @since 3.2.0
	 *
	 */
	protected function getAttributeValueByType( $product, $attribute ) {
		
		if ( method_exists( $this, $attribute ) ) {
			$output = call_user_func_array( array( $this, $attribute ), array( $product ) );
		} elseif ( false !== strpos( $attribute, self::PRODUCT_ATTRIBUTE_PREFIX ) ) {
			$attribute = str_replace( self::PRODUCT_ATTRIBUTE_PREFIX, '', $attribute );
			$output    = $this->getProductAttribute( $product, $attribute );
		} elseif ( false !== strpos( $attribute, self::POST_META_PREFIX ) ) {
			$attribute = str_replace( self::POST_META_PREFIX, '', $attribute );
			$output    = $this->getProductMeta( $product, $attribute );
		} elseif ( false !== strpos( $attribute, self::PRODUCT_TAXONOMY_PREFIX ) ) {
			$attribute = str_replace( self::PRODUCT_TAXONOMY_PREFIX, '', $attribute );
			$output    = $this->getProductTaxonomy( $product, $attribute );
		} elseif ( 'image_' == substr( $attribute, 0, 6 ) ) {
			// For additional image method images() will be used with extra parameter - image number
			$imageKey = explode( '_', $attribute );
			if ( ! isset( $imageKey[1] ) || ( isset( $imageKey[1] ) && ( empty( $imageKey[1] ) || ! is_numeric( $imageKey[1] ) ) ) ) {
				$imageKey[1] = '';
			}
			$output = call_user_func_array( array( $this, 'images' ), array( $product, $imageKey[1] ) );
		} else {
			// return the attribute so multiple attribute can be join with separator to make custom attribute.
			$output = $attribute;
		}
		
		// Json encode if value is an array
		if ( is_array( $output ) ) {
			$output = wp_json_encode( $output );
		}
		
		/**
		 * Filter attribute value before return based on merchant and attribute name
		 *
		 * @param string $output the output
		 * @param WC_Product|WC_Product_Variable|WC_Product_Variation|WC_Product_Grouped|WC_Product_External|WC_Product_Composite $product Product Object.
		 * @param array feed config/rule
		 *
		 * @since 3.3.7
		 *
		 */
		$output = apply_filters( "woo_feed_get_{$this->config['provider']}_{$attribute}_attribute", $output, $product, $this->config );
		
		/**
		 * Filter attribute value before return based on attribute name
		 *
		 * @param string $output the output
		 * @param WC_Product|WC_Product_Variable|WC_Product_Variation|WC_Product_Grouped|WC_Product_External|WC_Product_Composite $product Product Object.
		 * @param array feed config/rule
		 *
		 * @since 3.3.5
		 *
		 */
		return apply_filters( "woo_feed_get_{$attribute}_attribute", $output, $product, $this->config );
	}
	
	/**
	 * Get Product Id
	 *
	 * @param WC_Product|WC_Product_Variable|WC_Product_Variation|WC_Product_Grouped|WC_Product_External|WC_Product_Composite $product Product Object.
	 *
	 * @return mixed
	 * @since 3.2.0
	 */
	protected function id( $product ) {
		return $product->get_id();
	}
	
	/**
	 * Get Product Title
	 *
	 * @param WC_Product $product
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function title( $product ) {
		return $product->get_name();
	}
	
	/**
	 * Get Yoast Product Title
	 *
	 * @param WC_Product $product
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function yoast_wpseo_title( $product ) {
		$title = '';
		if ( class_exists( 'WPSEO_Frontend' ) ) {
			$title = WPSEO_Frontend::get_instance()->get_seo_title( get_post( $product->get_id() ) );
		}
		if ( ! empty( $title ) ) {
			return $title;
		}
		
		return $this->title( $product );
	}
	
	/**
	 * Get All In One Product Title
	 *
	 * @param WC_Product $product
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function _aioseop_title( $product ) {
		$title = '';
		if ( class_exists( 'All_in_One_SEO_Pack' ) ) {
			global $aioseop_options, $aiosp;
			if ( ! is_array( $aioseop_options ) ) {
				$aioseop_options = get_option( 'aioseop_options' );
			}
			if ( ! ( $aiosp instanceof All_in_One_SEO_Pack ) ) {
				$aiosp = new All_in_One_SEO_Pack();
			}
			
			if ( in_array( 'product', $aioseop_options['aiosp_cpostactive'], true ) ) {
				if ( ! empty( $aioseop_options['aiosp_rewrite_titles'] ) ) {
					$title = $aiosp->get_aioseop_title( get_post( $product->get_id() ) );
					$title = $aiosp->apply_cf_fields( $title );
				}
				$title = apply_filters( 'aioseop_title', $title );
			}
		}
		if ( ! empty( $title ) ) {
			return $title;
		}
		
		return $this->title( $product );
	}
	
	/**
	 * Get Product Description
	 *
	 * @param WC_Product|WC_Product_Variable|WC_Product_Variation|WC_Product_Grouped|WC_Product_External|WC_Product_Composite $product Product Object.
	 *
	 * @return mixed|string
	 * @since 3.2.0
	 *
	 */
	protected function description( $product ) {
		
		$description = $product->get_description();
		
		// Get Variation Description
		if ( $product->is_type( 'variation' ) && empty( $description ) ) {
			$parent      = wc_get_product( $product->get_parent_id() );
			$description = $parent->get_description();
		}
		$description = $this->remove_short_codes( $description );
		
		// Add variations attributes after description to prevent Facebook error
		if ( 'facebook' == $this->config['provider'] ) {
			$variationInfo = explode( "-", $product->get_name() );
			if ( isset( $variationInfo[1] ) ) {
				$extension = $variationInfo[1];
			} else {
				$extension = $product->get_id();
			}
			$description .= ' ' . $extension;
		}
		
		return $description;
	}
	
	/**
	 * Get Yoast Product Description
	 *
	 * @param WC_Product $product
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function yoast_wpseo_metadesc( $product ) {
		$description = '';
		if ( class_exists( 'WPSEO_Frontend' ) ) {
			$description = wpseo_replace_vars( WPSEO_Meta::get_value( 'metadesc', $product->get_id() ),
				get_post( $product->get_id() ) );
		}
		if ( ! empty( $description ) ) {
			return $description;
		}
		
		return $this->description( $product );
	}
	
	/**
	 * Get All In One Product Description
	 *
	 * @param WC_Product $product
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function _aioseop_description( $product ) {
		$description = '';
		if ( class_exists( 'All_in_One_SEO_Pack' ) ) {
			global $aioseop_options, $aiosp;
			if ( ! is_array( $aioseop_options ) ) {
				$aioseop_options = get_option( 'aioseop_options' );
			}
			if ( ! ( $aiosp instanceof All_in_One_SEO_Pack ) ) {
				$aiosp = new All_in_One_SEO_Pack();
			}
			if ( in_array( 'product', $aioseop_options['aiosp_cpostactive'], true ) ) {
				$description = $aiosp->get_main_description( get_post( $product->get_id() ) );    // Get the description.
				$description = $aiosp->trim_description( $description );
				$description = apply_filters( 'aioseop_description_full',
					$aiosp->apply_description_format( $description, get_post( $product->get_id() ) ) );
			}
		}
		if ( ! empty( $description ) ) {
			return $description;
		}
		
		return $this->description( $product );
	}
	
	/**
	 * Get Product Short Description
	 *
	 * @param WC_Product $product
	 *
	 * @return mixed|string
	 * @since 3.2.0
	 *
	 */
	protected function short_description( $product ) {
		
		$short_description = $product->get_short_description();
		
		// Get Variation Short Description
		if ( $product->is_type( 'variation' ) && empty( $short_description ) ) {
			$parent            = wc_get_product( $product->get_parent_id() );
			$short_description = $parent->get_short_description();
		}
		
		
		$short_description = $this->remove_short_codes( $short_description );
		
		return $short_description;
	}
	
	
	/**
	 * At First convert Short Codes and then Remove failed Short Codes from String
	 *
	 * @param $content
	 *
	 * @return mixed|string
	 * @since 3.2.0
	 *
	 */
	protected function remove_short_codes( $content ) {
		if ( empty( $content ) ) {
			return '';
		}
		
		// Remove DIVI Builder Short Codes
		if ( class_exists( 'ET_Builder_Module' ) || defined( 'ET_BUILDER_PLUGIN_VERSION' ) ) {
			/** @noinspection RegExpRedundantEscape */
			$content = preg_replace( '/\[\/?et_pb.*?\]/', '', $content );
		}
		
		$content = do_shortcode( $content );
		
		$content = woo_feed_stripInvalidXml( $content );
		
		return strip_shortcodes( $content );
	}
	
	/**
	 * Get Product Categories
	 *
	 * @param WC_Product $product
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function product_type( $product ) {
		$id = $product->get_id();
		if ( $product->is_type( 'variation' ) ) {
			$id = $product->get_parent_id();
		}
		
		$separator = apply_filters( 'woo_feed_product_type_separator', '>', $this->config, $product );
		
		return wp_strip_all_tags( wc_get_product_category_list( $id, $separator, '' ) );
	}
	
	/**
	 * Get Product URL
	 *
	 * @param WC_Product $product
	 *
	 * @return string
	 * @since 3.2.0
	 *
	 */
	protected function link( $product ) {
		$utm = $this->config['campaign_parameters'];
		if ( ! empty( $utm['utm_source'] ) && ! empty( $utm['utm_medium'] ) && ! empty( $utm['utm_campaign'] ) ) {
			$utm = [
				'utm_source'   => $utm['utm_source'],
				'utm_medium'   => $utm['utm_medium'],
				'utm_campaign' => $utm['utm_campaign'],
				'utm_term'     => $utm['utm_term'],
				'utm_content'  => $utm['utm_content'],
			];
			
			return add_query_arg( array_filter( $utm ), $product->get_permalink() );
		}
		
		return $product->get_permalink();
	}
	
	/**
	 * Get External Product URL
	 *
	 * @param WC_Product $product
	 *
	 * @return string
	 * @since 3.2.0
	 *
	 */
	protected function ex_link( $product ) {
		if ( $product->is_type( 'external' ) ) {
			return $product->get_product_url();
		}
		
		return '';
	}
	
	/**
	 * Get Product Image
	 *
	 * @param WC_Product|WC_Product_Variable|WC_Product_Variation|WC_Product_Grouped|WC_Product_External|WC_Product_Composite $product Product Object.
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function image( $product ) {
		if ( $product->is_type( 'variation' ) ) {
			$getImage = wp_get_attachment_image_src( get_post_thumbnail_id( $product->get_id() ),
				'single-post-thumbnail' );
			if ( has_post_thumbnail( $product->get_id() ) && ! empty( $getImage[0] ) ) :
				$image = woo_feed_get_formatted_url( $getImage[0] );
			else :
				$getImage = wp_get_attachment_image_src( get_post_thumbnail_id( $product->get_parent_id() ),
					'single-post-thumbnail' );
				$image    = woo_feed_get_formatted_url( $getImage[0] );
			endif;
		} else {
			if ( has_post_thumbnail( $product->get_id() ) ) :
				$getImage = wp_get_attachment_image_src( get_post_thumbnail_id( $product->get_id() ),
					'single-post-thumbnail' );
				$image    = woo_feed_get_formatted_url( $getImage[0] );
			else :
				$image = woo_feed_get_formatted_url( wp_get_attachment_url( $product->get_id() ) );
			endif;
		}
		
		return $image;
	}
	
	/**
	 * Get Product Featured Image
	 *
	 * @param WC_Product|WC_Product_Variable|WC_Product_Variation|WC_Product_Grouped|WC_Product_External|WC_Product_Composite $product Product Object.
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function feature_image( $product ) {
		return $this->image( $product );
	}
	
	/**
	 * Get Comma Separated Product Images
	 *
	 * @param WC_Product|WC_Product_Variable|WC_Product_Variation|WC_Product_Grouped|WC_Product_External|WC_Product_Composite $product Product Object.
	 * @param string $additionalImg Specific Additional Image.
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function images( $product, $additionalImg = '' ) {
		if ( $product->is_type( 'variation' ) ) {
			// TODO Test Variation Images
			$imgUrls = $this->get_product_gallery( wc_get_product( $product->get_parent_id() ) );
		} else {
			$imgUrls = $this->get_product_gallery( $product );
		}
		
		// Return Specific Additional Image URL
		if ( '' != $additionalImg ) {
			if ( array_key_exists( $additionalImg, $imgUrls ) ) {
				return $imgUrls[ $additionalImg ];
			} else {
				return '';
			}
		}
		
		return implode( ",", array_filter( $imgUrls ) );
	}
	
	/**
	 * Get Product Gallery Items (URL) array.
	 * This can contains empty array values
	 *
	 * @param WC_Product|WC_Product_Variable|WC_Product_Variation|WC_Product_Grouped|WC_Product_External|WC_Product_Composite $product
	 *
	 * @return string[]
	 * @since 3.2.6
	 */
	protected function get_product_gallery( $product ) {
		$attachmentIds = $product->get_gallery_image_ids();
		$imgUrls       = array();
		if ( $attachmentIds && is_array( $attachmentIds ) ) {
			$mKey = 1;
			foreach ( $attachmentIds as $attachmentId ) {
				$imgUrls[ $mKey ] = woo_feed_get_formatted_url( wp_get_attachment_url( $attachmentId ) );
				$mKey ++;
			}
		}
		
		return $imgUrls;
	}
	
	/**
	 * Get Product Condition
	 *
	 * @param WC_Product $product
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function condition( $product ) {
		return apply_filters( 'woo_feed_product_condition', 'new', $product );
	}
	
	/**
	 *  Get Product Type
	 *
	 * @param WC_Product $product
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function type( $product ) {
		return $product->get_type();
	}
	
	/**
	 *  Get Product is a bundle product or not
	 *
	 * @param WC_Product $product
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function is_bundle( $product ) {
		if ( $product->is_type( 'bundle' ) || $product->is_type( 'yith_bundle' ) ) {
			return "yes";
		}
		
		return 'no';
	}
	
	/**
	 *  Get Product is a multi-pack product or not
	 *
	 * @param WC_Product $product
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function multipack( $product ) {
		$multi_pack = '';
		if ( $product->is_type( 'grouped' ) ) {
			$multi_pack = ( ! empty( $product->get_children() ) ) ? count( $product->get_children() ) : '';
		}
		
		return $multi_pack;
	}
	
	/**
	 *  Get Product visibility status
	 *
	 * @param WC_Product $product
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function visibility( $product ) {
		return $product->get_catalog_visibility();
	}
	
	/**
	 *  Get Product Total Rating
	 *
	 * @param WC_Product $product
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function rating_total( $product ) {
		return $product->get_rating_count();
	}
	
	/**
	 * Get Product average rating
	 *
	 * @param WC_Product $product
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function rating_average( $product ) {
		return $product->get_average_rating();
	}
	
	/**
	 * Get Product tags
	 *
	 * @param WC_Product $product
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function tags( $product ) {
		$id = $product->get_id();
		if ( $product->is_type( 'variation' ) ) {
			$id = $product->get_parent_id();
		}
		
		$tags = get_the_term_list( $id, 'product_tag', '', ',', '' );
		
		if ( ! empty( $tags ) ) {
			return wp_strip_all_tags( $tags );
		}
		
		return '';
	}
	
	/**
	 * Get Product Parent Id
	 *
	 * @param WC_Product $product
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function item_group_id( $product ) {
		$id = $product->get_id();
		if ( $product->is_type( 'variation' ) ) {
			$id = $product->get_parent_id();
		}
		
		return $id;
	}
	
	/**
	 * Get Product SKU
	 *
	 * @param WC_Product $product
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function sku( $product ) {
		return $product->get_sku();
	}
	
	/**
	 * Get Product Parent SKU
	 *
	 * @param WC_Product $product
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function parent_sku( $product ) {
		if ( $product->is_type( 'variation' ) ) {
			$id     = $product->get_parent_id();
			$parent = wc_get_product( $id );
			
			return $parent->get_sku();
		}
		
		return $product->get_sku();
	}
	
	/**
	 * Get Product Availability Status
	 *
	 * @param WC_Product $product
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function availability( $product ) {
		$status = $product->get_stock_status();
		if ( 'instock' == $status ) {
			return 'in stock';
		} elseif ( 'outofstock' == $status ) {
			return 'out of stock';
		} elseif ( 'onbackorder' == $status ) {
			return 'on backorder';
		} else {
			return 'in stock';
		}
	}
	
	/**
	 * Get Product Quantity
	 *
	 * @param WC_Product $product
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function quantity( $product ) {
		if ( $product->is_type( 'variable' ) && $product->has_child() ) {
			$visible_children = $product->get_visible_children();
			$qty              = array();
			foreach ( $visible_children as $key => $child ) {
				$childQty = get_post_meta( $child, '_stock', true );
				$qty[]    = (int) $childQty + 0;
			}
			
			if ( isset( $this->config['variable_quantity'] ) ) {
				$vaQty = $this->config['variable_quantity'];
				if ( 'max' == $vaQty ) {
					return max( $qty );
				} elseif ( 'min' == $vaQty ) {
					return min( $qty );
				} elseif ( 'sum' == $vaQty ) {
					return array_sum( $qty );
				} elseif ( 'first' == $vaQty ) {
					return ( (int) $qty[0] );
				}
				
				return array_sum( $qty );
			}
		}
		
		return $product->get_stock_quantity();
	}
	
	/**
	 * Get Product Sale Price Start Date
	 *
	 * @param WC_Product $product
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function sale_price_sdate( $product ) {
		$startDate = $product->get_date_on_sale_from();
		if ( is_object( $startDate ) ) {
			return $startDate->date_i18n();
		}
		
		return '';
	}
	
	/**
	 * Get Product Sale Price End Date
	 *
	 * @param WC_Product $product
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function sale_price_edate( $product ) {
		$endDate = $product->get_date_on_sale_to();
		if ( is_object( $endDate ) ) {
			return $endDate->date_i18n();
		}
		
		return '';
	}
	
	/**
	 * Get Product Regular Price
	 *
	 * @param WC_Product|WC_Product_Variable|WC_Product_Grouped $product Product Object.
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function price( $product ) {
		if ( $product->is_type( 'variable' ) ) {
			return $this->getVariableProductPrice( $product, 'regular_price' );
		} elseif ( $product->is_type( 'grouped' ) ) {
			return $this->getGroupProductPrice( $product,
				'regular' ); // this calls self::price() so no need to use self::getWPMLPrice()
		} else {
			return $product->get_regular_price();
		}
	}
	
	/**
	 * Get Product Price
	 *
	 * @param WC_Product|WC_Product_Variable|WC_Product_Grouped $product
	 *
	 * @return int|float|double|mixed
	 * @since 3.2.0
	 *
	 */
	protected function current_price( $product ) {
		if ( $product->is_type( 'variable' ) ) {
			return $this->getVariableProductPrice( $product, 'price' );
		} elseif ( $product->is_type( 'grouped' ) ) {
			return $this->getGroupProductPrice( $product, 'current' );
		} else {
			return $product->get_price();
		}
	}
	
	/**
	 * Get Product Sale Price
	 *
	 * @param WC_Product|WC_Product_Variable|WC_Product_Grouped $product
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function sale_price( $product ) {
		if ( $product->is_type( 'variable' ) ) {
			return $this->getVariableProductPrice( $product, 'sale_price' );
		} elseif ( $product->is_type( 'grouped' ) ) {
			return $this->getGroupProductPrice( $product, 'sale' );
		} else {
			$price = $product->get_sale_price();
			
			return $price > 0 ? $price : '';
		}
	}
	
	/**
	 * Get Product Regular Price with Tax
	 *
	 * @param WC_Product|WC_Product_Variable|WC_Product_Grouped $product Product Object.
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function price_with_tax( $product ) {
		if ( $product->is_type( 'variable' ) ) {
			return $this->getVariableProductPrice( $product, 'regular_price', true );
		} elseif ( $product->is_type( 'grouped' ) ) {
			return $this->getGroupProductPrice( $product, 'regular', true );
		} else {
			$price = $this->price( $product );
			
			// Get price with tax.
			return ( $product->is_taxable() && ! empty( $price ) ) ? $this->get_price_with_tax( $product,
				$price ) : $price;
		}
	}
	
	/**
	 * Get Product Regular Price with Tax
	 *
	 * @param WC_Product|WC_Product_Variable|WC_Product_Grouped $product
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function current_price_with_tax( $product ) {
		if ( $product->is_type( 'variable' ) ) {
			return $this->getVariableProductPrice( $product, 'current_price', true );
		} elseif ( $product->is_type( 'grouped' ) ) {
			return $this->getGroupProductPrice( $product, 'current', true );
		} else {
			$price = $this->current_price( $product );
			
			// Get price with tax
			return ( $product->is_taxable() && ! empty( $price ) ) ? $this->get_price_with_tax( $product,
				$price ) : $price;
		}
	}
	
	/**
	 * Get Product Regular Price with Tax
	 *
	 * @param WC_Product|WC_Product_Variable|WC_Product_Grouped $product
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function sale_price_with_tax( $product ) {
		if ( $product->is_type( 'variable' ) ) {
			return $this->getVariableProductPrice( $product, 'sale_price', true );
		} elseif ( $product->is_type( 'grouped' ) ) {
			return $this->getGroupProductPrice( $product, 'sale', true );
		} else {
			$price = $this->sale_price( $product );
			if ( $product->is_taxable() && ! empty( $price ) ) {
				$price = $this->get_price_with_tax( $product, $price );
			}
			
			return $price > 0 ? $price : '';
		}
	}
	
	/**
	 * Get total price of grouped product
	 *
	 * @param WC_Product_Grouped $grouped
	 * @param string $type
	 * @param bool $tax
	 *
	 * @return int|string
	 * @since 3.2.0
	 *
	 */
	protected function getGroupProductPrice( $grouped, $type, $tax = false ) {
		$groupProductIds = $grouped->get_children();
		$sum             = 0;
		if ( ! empty( $groupProductIds ) ) {
			foreach ( $groupProductIds as $id ) {
				$product = wc_get_product( $id );
				
				if ( ! is_object( $product ) ) {
					continue; // make sure that the product exists..
				}
				
				if ( $tax ) {
					if ( 'regular' == $type ) {
						$regularPrice = $this->price_with_tax( $product );
						$sum          += (float) $regularPrice;
					} elseif ( 'current' == $type ) {
						$currentPrice = $this->current_price_with_tax( $product );
						$sum          += (float) $currentPrice;
					} else {
						$salePrice = $this->sale_price_with_tax( $product );
						$sum       += (float) $salePrice;
					}
				} else {
					if ( 'regular' == $type ) {
						$regularPrice = $this->price( $product );
						$sum          += (float) $regularPrice;
					} elseif ( 'current' == $type ) {
						$currentPrice = $this->current_price( $product );
						$sum          += (float) $currentPrice;
					} else {
						$salePrice = $this->sale_price( $product );
						$sum       += (float) $salePrice;
					}
				}
			}
		}
		
		if ( 'sale' == $type ) {
			$sum = $sum > 0 ? $sum : '';
		}
		
		return $sum;
	}
	
	/**
	 * Get total price of variable product
	 *
	 * @param WC_Product_Variable $variable
	 * @param string $type regular_price, sale_price & current_price
	 * @param bool $tax calculate tax
	 *
	 * @return int|string
	 * @since 3.2.0
	 *
	 */
	protected function getVariableProductPrice( $variable, $type, $tax = false ) {
		$price = 0;
		if ( 'regular_price' == $type ) {
			$price = $variable->get_variation_regular_price();
		} elseif ( 'sale_price' == $type ) {
			$price = $variable->get_variation_sale_price();
		} else {
			$price = $variable->get_variation_price();
		}
		if ( true === $tax && $variable->is_taxable() ) {
			$price = $this->get_price_with_tax( $variable, $price );
		}
		if ( 'sale_price' != $type ) {
			$price = $price > 0 ? $price : '';
		}
		
		return $price;
	}
	
	/**
	 * Return product price with tax
	 *
	 * @param WC_Product $product Product.
	 * @param float $price Price.
	 *
	 * @return float|string
	 * @since 3.2.0
	 *
	 */
	protected function get_price_with_tax( $product, $price ) {
		if ( woo_feed_wc_version_check( 3.0 ) ) {
			return wc_get_price_including_tax( $product, array( 'price' => $price ) );
		} else {
			return $product->get_price_including_tax( 1, $price );
		}
	}
	
	/**
	 * Get Product Weight
	 *
	 * @param WC_Product $product
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function weight( $product ) {
		return $product->get_weight();
	}
	
	/**
	 * Get Product Width
	 *
	 * @param WC_Product $product
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function width( $product ) {
		return $product->get_width();
	}
	
	/**
	 * Get Product Height
	 *
	 * @param WC_Product $product
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function height( $product ) {
		return $product->get_height();
	}
	
	/**
	 * Get Product Length
	 *
	 * @param WC_Product $product
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function length( $product ) {
		return $product->get_length();
	}
	
	/**
	 * Get Product Shipping Class
	 *
	 * @param WC_Product $product
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function shipping_class( $product ) {
		return $product->get_shipping_class();
	}
	
	/**
	 * Get Product Author Name
	 *
	 * @param WC_Product $product
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function author_name( $product ) {
		$post = get_post( $product->get_id() );
		
		return get_the_author_meta( 'user_login', $post->post_author );
	}
	
	/**
	 * Get Product Author Email
	 *
	 * @param WC_Product $product
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function author_email( $product ) {
		$post = get_post( $product->get_id() );
		
		return get_the_author_meta( 'user_email', $post->post_author );
	}
	
	/**
	 * Get Product Created Date
	 *
	 * @param WC_Product $product
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function date_created( $product ) {
		return gmdate( 'Y-m-d', strtotime( $product->get_date_created() ) );
	}
	
	/**
	 * Get Product Last Updated Date
	 *
	 * @param WC_Product $product
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function date_updated( $product ) {
		return gmdate( 'Y-m-d', strtotime( $product->get_date_modified() ) );
	}
	
	/**
	 * Get Product Sale Price Effected Date for Google Shopping
	 *
	 * @param WC_Product $product
	 *
	 * @return string
	 * @since 3.2.0
	 *
	 */
	protected function sale_price_effective_date( $product ) {
		$effective_date = '';
		$from           = $this->sale_price_sdate( $product );
		$to             = $this->sale_price_edate( $product );
		if ( ! empty( $from ) && ! empty( $to ) ) {
			$from = gmdate( 'c', strtotime( $from ) );
			$to   = gmdate( 'c', strtotime( $to ) );
			
			$effective_date = $from . '/' . $to;
		}
		
		return $effective_date;
	}
	
	/**
	 * Ger Product Attribute
	 *
	 * @param WC_Product $product
	 * @param $attr
	 *
	 * @return string
	 * @since 2.2.3
	 *
	 */
	protected function getProductAttribute( $product, $attr ) {
		$id = $product->get_id();
		
		if ( woo_feed_wc_version_check( 3.2 ) ) {
			if ( woo_feed_wc_version_check( 3.6 ) ) {
				$attr = str_replace( 'pa_', '', $attr );
			}
			$value = $product->get_attribute( $attr );
			
			return $value;
		} else {
			return implode( ',', wc_get_product_terms( $id, $attr, array( 'fields' => 'names' ) ) );
		}
	}
	
	/**
	 * Get Meta
	 *
	 * @param WC_Product $product
	 * @param string $meta post meta key
	 *
	 * @return mixed|string
	 * @since 2.2.3
	 *
	 */
	protected function getProductMeta( $product, $meta ) {
		$value = get_post_meta( $product->get_id(), $meta, true );
		// if empty get meta value of parent post
		if ( '' == $value && $product->get_parent_id() ) {
			$value = get_post_meta( $product->get_parent_id(), $meta, true );
		}
		
		return $value;
	}
	
	/**
	 * Get Taxonomy
	 *
	 * @param WC_Product $product
	 * @param $taxonomy
	 *
	 * @return string
	 * @since 2.2.3
	 *
	 */
	protected function getProductTaxonomy( $product, $taxonomy ) {
		$id = $product->get_id();
		if ( $product->is_type( 'variation' ) ) {
			$id = $product->get_parent_id();
		}
		
		$separator = apply_filters( 'woo_feed_product_taxonomy_term_list_separator', ',', $this->config, $product );
		
		return wp_strip_all_tags( get_the_term_list( $id, $taxonomy, '', $separator, '' ) );
	}
	
	/**
	 * Format price value
	 *
	 * @param string $name Attribute Name
	 * @param int $conditionName condition
	 * @param int $result price
	 *
	 * @return mixed
	 * @since 3.2.0
	 *
	 */
	protected function price_format( $name, $conditionName, $result ) {
		$plus    = "+";
		$minus   = "-";
		$percent = "%";
		
		if ( strpos( $name, 'price' ) !== false ) {
			if ( strpos( $result, $plus ) !== false && strpos( $result, $percent ) !== false ) {
				$result = str_replace( "+", '', $result );
				$result = str_replace( "%", '', $result );
				if ( is_numeric( $result ) ) {
					$result = $conditionName + ( ( $conditionName * $result ) / 100 );
				}
			} elseif ( strpos( $result, $minus ) !== false && strpos( $result, $percent ) !== false ) {
				$result = str_replace( "-", '', $result );
				$result = str_replace( "%", '', $result );
				if ( is_numeric( $result ) ) {
					$result = $conditionName - ( ( $conditionName * $result ) / 100 );
				}
			} elseif ( strpos( $result, $plus ) !== false ) {
				$result = str_replace( "+", "", $result );
				if ( is_numeric( $result ) ) {
					$result = ( $conditionName + $result );
				}
			} elseif ( strpos( $result, $minus ) !== false ) {
				$result = str_replace( "-", "", $result );
				if ( is_numeric( $result ) ) {
					$result = $conditionName - $result;
				}
			}
		}
		
		return $result;
	}
	
	/**
	 * Format output According to Output Type config
	 *
	 * @param string $output
	 * @param array $outputTypes
	 * @param WC_Product $product
	 * @param string $productAttribute
	 *
	 * @return float|int|string
	 * @since 3.2.0
	 *
	 */
	protected function format_output( $output, $outputTypes, $product, $productAttribute ) {
		if ( ! empty( $outputTypes ) && is_array( $outputTypes ) ) {
			
			// Format Output According to output type
			if ( in_array( 2, $outputTypes ) ) { // Strip Tags
				$output = wp_strip_all_tags( html_entity_decode( $output ) );
			}
			
			if ( in_array( 3, $outputTypes ) ) { // UTF-8 Encode
				$output = utf8_encode( $output );
			}
			
			if ( in_array( 4, $outputTypes ) ) { // htmlentities
				$output = htmlentities( $output, ENT_QUOTES, 'UTF-8' );
			}
			
			if ( in_array( 5, $outputTypes ) ) { // Integer
				$output = intval( $output );
			}
			
			if ( in_array( 6, $outputTypes ) ) { // Format Price
				if ( ! empty( $output ) && $output > 0 ) {
					$output = (float) $output;
					$output = number_format( $output, 2, '.', '' );
				}
			}
			
			if ( in_array( 7, $outputTypes ) ) { // Delete Space
				$output = trim( $output );
				$output = preg_replace( '!\s+!', ' ', $output );
			}
			
			if ( in_array( 9, $outputTypes ) ) { // Remove Invalid Character
				$output = woo_feed_stripInvalidXml( $output );
			}
			
			if ( in_array( 10, $outputTypes ) ) {  // Remove ShortCodes
				$output = $this->remove_short_codes( $output );
			}
			
			if ( in_array( 8, $outputTypes ) ) { // Add CDATA
				$output = '<![CDATA[' . $output . ']]>';
			}
		}
		
		return $output;
	}
	
	/**
	 * Add Prefix and Suffix with attribute value
	 *
	 * @param $output
	 * @param $prefix
	 * @param $suffix
	 * @param $attribute
	 *
	 * @return string
	 * @since 3.2.0
	 *
	 */
	protected function process_prefix_suffix( $output, $prefix, $suffix, $attribute = '' ) {
		
		if ( '' == $output ) {
			return $output;
		}
		
		// Add Prefix before Output
		if ( '' != $prefix ) {
			$output = "$prefix" . $output;
		}
		
		// Add Suffix after Output
		if ( '' !== $suffix ) {
			if (
				'price' == $attribute
				|| 'sale_price' == $attribute
				|| 'current_price' == $attribute
				|| 'price_with_tax' == $attribute
				|| 'current_price_with_tax' == $attribute
				|| 'sale_price_with_tax' == $attribute
				|| 'shipping_price' == $attribute
				|| 'tax_rate' == $attribute
			) { // Add space before suffix if attribute contain price.
				$output = $output . ' ' . $suffix;
			} elseif ( substr( $output, 0, 4 ) === 'http' ) {
				// Parse URL Parameters if available into suffix field
				$output = woo_feed_make_url_with_parameter( $output, $suffix );
				
			} else {
				$output = $output . "$suffix";
			}
		}
		
		return "$output";
	}
}