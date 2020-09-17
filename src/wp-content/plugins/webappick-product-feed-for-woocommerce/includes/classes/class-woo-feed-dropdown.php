<?php
/**
 * The file that defines the merchants attributes dropdown
 *
 * A class definition that includes attributes dropdown and functions used across the admin area.
 *
 * @link       https://webappick.com/
 * @since      1.0.0
 *
 * @package    Woo_Feed
 * @subpackage Woo_Feed/includes
 * @author     Ohidul Islam <wahid@webappick.com>
 */

class Woo_Feed_Dropdown {
	
	public $cats = array();
	public $output_types = array(
		'1'  => 'Default',
		'2'  => 'Strip Tags',
		'3'  => 'UTF-8 Encode',
		'4'  => 'htmlentities',
		'5'  => 'Integer',
		'6'  => 'Price',
		'7'  => 'Remove Space',
		'10' => 'Remove ShortCodes',
		'9'  => 'Remove Special Character',
		'8'  => 'CDATA',
        '11' => 'ucwords',
        '12' => 'ucfirst',
        '13' => 'strtoupper',
        '14' => 'strtolower',
        '15' => 'urlToSecure',
        '16' => 'urlToUnsecure',
        '17' => 'only_parent',
        '18' => 'parent',
        '19' => 'parent_if_empty',
	);
	
	public function __construct() {
	}
	
	/**
	 * Dropdown of Merchant List
	 *
	 * @param string $selected
	 *
	 * @return string
	 */
	public function merchantsDropdown( $selected = '' ) {
		$options = $this->get_cached_dropdown( 'merchantsDropdown', $selected );
		if ( false === $options ) {
			$attributes = new Woo_Feed_Merchant();
			$options = $this->cache_dropdown( 'merchantsDropdown', $attributes->merchants(), $selected );
		}
		return $options;
	}
	
	/**
	 * @param int|int[] $selected
	 *
	 * @return string
	 */
	public function outputTypes( $selected = 1 ) {
		$output_types = '';
		if ( ! is_array( $selected ) ) {
			$selected = (array) $selected;
		}
		foreach ( $this->output_types as $key => $value ) {
			$output_types .= "<option value=\"{$key}\"" . selected( in_array( $key, $selected ), true, false ) . ">{$value}</option>";
		}
		// @TODO remove update_option( 'woo_feed_output_type_options', $output_types, false );
		
		return $output_types;
	}
	
	/**
	 * Read txt file which contains google taxonomy list
	 *
	 * @param string $selected
	 *
	 * @return string
	 */
	public function googleTaxonomy( $selected = '' ) {
		// Get All Google Taxonomies
		$fileName           = WOO_FEED_FREE_ADMIN_PATH . '/partials/templates/taxonomies/google_taxonomy.txt';
		$customTaxonomyFile = fopen( $fileName, 'r' ); // phpcs:ignore
		$str                = '';
		if ( ! empty( $selected ) ) {
			$selected = trim( $selected );
			if ( ! is_numeric( $selected ) ) {
				$selected = html_entity_decode( $selected );
			} else {
				$selected = (int) $selected;
			}
		}
		if ( $customTaxonomyFile ) {
			// First line contains metadata, ignore it
			fgets( $customTaxonomyFile ); // phpcs:ignore
			while ( $line = fgets( $customTaxonomyFile ) ) { // phpcs:ignore
				list( $catId, $cat ) = explode( '-', $line );
				$catId = (int) trim( $catId );
				$cat   = trim( $cat );
				$str   .= sprintf(
					'<option value="%s" %s>%s</option>',
					$catId,
					selected( $selected, is_numeric( $selected ) ? $catId : $cat, false ),
					$cat
				);
			}
		}
		if ( ! empty( $str ) ) {
			$str = '<option></option>' . $str;
		}
		
		return $str;
	}
	
	/**
	 * Read txt file which contains google taxonomy list
	 *
	 * @return array
	 */
	public function googleTaxonomyArray() {
		// Get All Google Taxonomies
		$fileName           = WOO_FEED_FREE_ADMIN_PATH . '/partials/templates/taxonomies/google_taxonomy.txt';
		$customTaxonomyFile = fopen( $fileName, 'r' );  // phpcs:ignore
		$taxonomy           = array();
		if ( $customTaxonomyFile ) {
			// First line contains metadata, ignore it
			fgets( $customTaxonomyFile );  // phpcs:ignore
			while ( $line = fgets( $customTaxonomyFile ) ) {  // phpcs:ignore
				list( $catId, $cat ) = explode( '-', $line );
				$taxonomy[] = array(
					'value' => absint( trim( $catId ) ),
					'text'  => trim( $cat ),
				);
			}
		}
		$taxonomy = array_filter( $taxonomy );
		
		return $taxonomy;
	}
	
	// Product Attribute DropDowns.
	
	/**
	 * Get All Default WooCommerce Attributes
	 * @return array
	 */
	protected function getAttributeTaxonomies() {
		$taxonomies = woo_feed_get_cached_data( 'getAttributeTaxonomies' );
		if ( false === $taxonomies ) {
			// Load the main attributes
			$globalAttributes = wc_get_attribute_taxonomy_labels();
			if ( count( $globalAttributes ) ) {
				foreach ( $globalAttributes as $key => $value ) {
					$taxonomies[ Woo_Feed_Products_v3::PRODUCT_ATTRIBUTE_PREFIX . 'pa_' . $key ] = $value;
				}
			}
			woo_feed_set_cache_data( 'getAttributeTaxonomies', $taxonomies );
		}
		
		return $taxonomies;
	}
	
	/**
	 * Product Attributes
	 *
	 * @return array
	 */
	protected function get_product_attributes() {
		$attributes = array(
			'--1'                       => esc_html__( 'Primary Attributes', 'woo-feed' ),
			'id'                        => esc_html__( 'Product Id', 'woo-feed' ),
			'title'                     => esc_html__( 'Product Title', 'woo-feed' ),
			'description'               => esc_html__( 'Product Description', 'woo-feed' ),
			'short_description'         => esc_html__( 'Product Short Description', 'woo-feed' ),
			'primary_category'          => esc_html__( 'Primary Category', 'woo-feed' ),
			'primary_category_id'       => esc_html__( 'Primary Category ID', 'woo-feed' ),
			'product_type'              => esc_html__( 'Product Local Category [Category Path]', 'woo-feed' ),
			'link'                      => esc_html__( 'Product URL', 'woo-feed' ),
			'canonical_link'            => esc_html__( 'Canonical URL', 'woo-feed' ),
			'ex_link'                   => esc_html__( 'External Product URL', 'woo-feed' ),
			'condition'                 => esc_html__( 'Condition', 'woo-feed' ),
			'item_group_id'             => esc_html__( 'Parent Id [Group Id]', 'woo-feed' ),
			'sku'                       => esc_html__( 'SKU', 'woo-feed' ),
			'parent_sku'                => esc_html__( 'Parent SKU', 'woo-feed' ),
			'availability'              => esc_html__( 'Availability', 'woo-feed' ),
			'quantity'                  => esc_html__( 'Quantity', 'woo-feed' ),
			'price'                     => esc_html__( 'Regular Price', 'woo-feed' ),
			'current_price'             => esc_html__( 'Price', 'woo-feed' ),
			'sale_price'                => esc_html__( 'Sale Price', 'woo-feed' ),
			'price_with_tax'            => esc_html__( 'Regular Price With Tax', 'woo-feed' ),
			'current_price_with_tax'    => esc_html__( 'Price With Tax', 'woo-feed' ),
			'sale_price_with_tax'       => esc_html__( 'Sale Price With Tax', 'woo-feed' ),
			'sale_price_sdate'          => esc_html__( 'Sale Start Date', 'woo-feed' ),
			'sale_price_edate'          => esc_html__( 'Sale End Date', 'woo-feed' ),
			'weight'                    => esc_html__( 'Weight', 'woo-feed' ),
			'width'                     => esc_html__( 'Width', 'woo-feed' ),
			'height'                    => esc_html__( 'Height', 'woo-feed' ),
			'length'                    => esc_html__( 'Length', 'woo-feed' ),
			'shipping_class'            => esc_html__( 'Shipping Class', 'woo-feed' ),
			'type'                      => esc_html__( 'Product Type', 'woo-feed' ),
			'variation_type'            => esc_html__( 'Variation Type', 'woo-feed' ),
			'visibility'                => esc_html__( 'Visibility', 'woo-feed' ),
			'rating_total'              => esc_html__( 'Total Rating', 'woo-feed' ),
			'rating_average'            => esc_html__( 'Average Rating', 'woo-feed' ),
			'tags'                      => esc_html__( 'Tags', 'woo-feed' ),
			'sale_price_effective_date' => esc_html__( 'Sale Price Effective Date', 'woo-feed' ),
			'is_bundle'                 => esc_html__( 'Is Bundle', 'woo-feed' ),
			'author_name'               => esc_html__( 'Author Name', 'woo-feed' ),
			'author_email'              => esc_html__( 'Author Email', 'woo-feed' ),
			'date_created'              => esc_html__( 'Date Created', 'woo-feed' ),
			'date_updated'              => esc_html__( 'Date Updated', 'woo-feed' ),
			'tax_class'                 => esc_html__( 'Tax Class', 'woo-feed' ),
			'tax_status'                => esc_html__( 'Tax Status', 'woo-feed' ),
            'woo_feed_gtin'             => esc_html__( 'GTIN', 'woo-feed' ),
            'woo_feed_mpn'              => esc_html__( 'MPN', 'woo-feed' ),
            'woo_feed_ean'              => esc_html__( 'EAN', 'woo-feed' ),
			'---1'                      => '',
		);
		if ( class_exists( 'All_in_One_SEO_Pack' ) ) {
			$attributes = array_merge( $attributes,
				[
					'_aioseop_title'       => esc_html__( 'Title [All in One SEO]', 'woo-feed' ),
					'_aioseop_description' => esc_html__( 'Description [All in One SEO]', 'woo-feed' ),
				] );
		}
		if ( class_exists( 'WPSEO_Frontend' ) ) {
			$attributes = array_merge( $attributes,
				[
					'yoast_wpseo_title'    => esc_html__( 'Title [Yoast SEO]', 'woo-feed' ),
					'yoast_wpseo_metadesc' => esc_html__( 'Description [Yoast SEO]', 'woo-feed' ),
				] );
		}

        if ( class_exists( 'WC_Subscriptions' ) ) {
            $attributes = array_merge( $attributes,
                [
                    'subscription_period'    => esc_html__( 'Subscription Period', 'woo-feed' ),
                    'subscription_period_interval'    => esc_html__( 'Subscription Period Interval', 'woo-feed' ),
                    'subscription_amount'    => esc_html__( 'Subscription Amount', 'woo-feed' ),
                ] );
        }
		
		// Image Attributes.
		$attributes['--2'] = esc_html__( 'Image Attributes', 'woo-feed' );
		$attributes = $attributes + array(
			'image'         => esc_html__( 'Main Image', 'woo-feed' ),
			'feature_image' => esc_html__( 'Featured Image', 'woo-feed' ),
			'images'        => esc_html__( 'Images [Comma Separated]', 'woo-feed' ),
			'image_1'       => esc_html__( 'Additional Image 1', 'woo-feed' ),
			'image_2'       => esc_html__( 'Additional Image 2', 'woo-feed' ),
			'image_3'       => esc_html__( 'Additional Image 3', 'woo-feed' ),
			'image_4'       => esc_html__( 'Additional Image 4', 'woo-feed' ),
			'image_5'       => esc_html__( 'Additional Image 5', 'woo-feed' ),
			'image_6'       => esc_html__( 'Additional Image 6', 'woo-feed' ),
			'image_7'       => esc_html__( 'Additional Image 7', 'woo-feed' ),
			'image_8'       => esc_html__( 'Additional Image 8', 'woo-feed' ),
			'image_9'       => esc_html__( 'Additional Image 9', 'woo-feed' ),
			'image_10'      => esc_html__( 'Additional Image 10', 'woo-feed' ),
		);
		$attributes['---2'] = '';
		
		// Product Attribute (taxonomy).
		$_attributes = $this->getAttributeTaxonomies();
		if ( ! empty( $_attributes ) && is_array( $_attributes ) ) {
			$attributes['--3'] = esc_html__( 'Product Attributes', 'woo-feed' );
			$attributes = $attributes + $this->getAttributeTaxonomies();
			$attributes['---3'] = '';
		}

		// Category Mapping
        $_category_mappings = $this->getCustomCategoryMappedAttributes();
        if ( ! empty( $_category_mappings ) && is_array( $_category_mappings ) ) {
            $attributes['--4']  = esc_html__( 'Category Mappings', 'woo-feed' );
            $attributes         = $attributes + $_category_mappings;
            $attributes['---4'] = '';
        }
		
		return $attributes;
	}

    /**
     * Get Category Mappings
     * @return array
     */
    protected function getCustomCategoryMappedAttributes() {
        global $wpdb;
        // Load Custom Category Mapped Attributes
        $info = array();
        // query cached and escaped
        $data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->options WHERE option_name LIKE %s;", Woo_Feed_Products_v3::PRODUCT_CATEGORY_MAPPING_PREFIX . '%' ) );  // phpcs:ignore
        if ( count( $data ) ) {
            foreach ( $data as $key => $value ) {
                $opts                        = maybe_unserialize( $value->option_value );
                $opts                        = maybe_unserialize( $opts );
                $info[ $value->option_name ] = is_array( $opts ) && isset( $opts['mappingname'] ) ? $opts['mappingname'] : str_replace( 'wf_cmapping_',
                    '',
                    $value->option_name );
            }
        }
        return (array) $info;
    }
	
	/**
	 * Local Attribute List to map product value with merchant attributes
	 *
	 * @param string $selected
	 *
	 * @return string
	 */
	public function product_attributes_dropdown( $selected = '' ) {
		
		$attributeDropdown = $this->get_cached_dropdown( 'woo_feed_dropdown_product_attributes', $selected );
		
		if ( false === $attributeDropdown ) {
			return $this->cache_dropdown( 'woo_feed_dropdown_product_attributes', $this->get_product_attributes(), $selected, __( 'Select Attributes', 'woo-feed' ) );
		}
		
		return $attributeDropdown;
	}
	
	// Helper functions.
	
	/**
	 * Get Cached Dropdown Entries
	 *
	 * @param string $key      cache key
	 * @param string $selected selected option
	 *
	 * @return string|false
	 */
	protected function get_cached_dropdown( $key, $selected = '' ) {
		$options = woo_feed_get_cached_data( $key );
		if ( strlen( $selected ) ) {
			$selected = esc_attr( $selected );
			$options = str_replace( "value=\"{$selected}\"", "value=\"{$selected}\" selected", $options );
		}
		return empty( $options ) ? false : $options;
	}
	
	/**
	 * create dropdown options and cache for next use
	 *
	 * @param string $cache_key cache key
	 * @param array  $items     dropdown items
	 * @param string $selected  selected option
	 * @param string $default   default option
	 *
	 * @return string
	 */
	protected function cache_dropdown( $cache_key, $items, $selected = '', $default = '' ) {
		
		if ( empty( $items ) || ! is_array( $items ) ) {
			return '';
		}
		
		if ( ! empty( $default ) ) {
			$options = '<option value="" class="disabled" selected>' . esc_html( $default ) . '</option>';
		} else {
			$options = '<option></option>';
		}
		
		foreach ( $items as $key => $value ) {
			if ( substr( $key, 0, 2 ) == '--' ) {
				$options .= "<optgroup label=\"{$value}\">";
			} elseif ( substr( $key, 0, 2 ) == '---' ) {
				$options .= '</optgroup>';
			} else {
				$options .= sprintf( '<option value="%s">%s</option>', $key, $value );
			}
		}
		
		woo_feed_set_cache_data( $cache_key, $options );
		
		if ( strlen( $selected ) ) {
			$selected = esc_attr( $selected );
			$options = str_replace( "value=\"{$selected}\"", "value=\"{$selected}\" selected", $options );
		}
		
		return $options;
	}
	
	// Merchant Attribute DropDown.
	
	/**
	 * Dropdown of Google Attribute List
	 *
	 * @param string $selected
	 *
	 * @return string
	 */
	public function googleAttributesDropdown( $selected = '' ) {
		$options = $this->get_cached_dropdown( 'googleAttributesDropdown', $selected );
		
		if ( false === $options ) {
			$attributes = new Woo_Feed_Default_Attributes();
			return $this->cache_dropdown( 'googleAttributesDropdown', $attributes->googleAttributes(), $selected );
		}
		return $options;
	}
	
	/**
	 * Google Shopping Action Attribute list
	 * Alias of google attribute dropdown for facebook
	 *
	 * @param string $selected
	 *
	 * @return string
	 */
	public function google_shopping_actionAttributesDropdown( $selected = '' ) {
		return $this->googleAttributesDropdown( $selected );
	}
	
	/**
	 * Facebook Attribute list
	 * Alias of google attribute dropdown for facebook
	 *
	 * @param string $selected
	 *
	 * @return string
	 */
	public function facebookAttributesDropdown( $selected = '' ) {
		return $this->googleAttributesDropdown( $selected );
	}
	
	/**
	 * Pinterest Attribute list
	 * Alias of google attribute dropdown for pinterest
	 *
	 * @param string $selected
	 *
	 * @return string
	 */
	public function pinterestAttributesDropdown( $selected = '' ) {
		return $this->googleAttributesDropdown( $selected );
	}
	
	/**
	 * AdRoll Attribute list
	 * Alias of google attribute dropdown for AdRoll
	 *
	 * @param string $selected
	 *
	 * @return string
	 */
	public function adrollAttributesDropdown( $selected = '' ) {
		return $this->googleAttributesDropdown( $selected );
	}
	
	/**
	 * Skroutz Attribute list
	 *
	 * @param string $selected
	 *
	 * @return string
	 */
	public function skroutzAttributesDropdown( $selected = '' ) {
		$options = $this->get_cached_dropdown( 'skroutzAttributesDropdown', $selected );
		if ( false === $options ) {
			$attributes = new Woo_Feed_Default_Attributes();
			$options = $this->cache_dropdown( 'skroutzAttributesDropdown', $attributes->skroutzAttributes(), $selected );
		}
		return $options;
	}

    /**
     * Best Price Attribute list
     *
     * @param string $selected
     *
     * @return string
     */
    public function bestpriceAttributesDropdown( $selected = '' ) {
        $options = $this->get_cached_dropdown( 'bestpriceAttributesDropdown', $selected );
        if ( false === $options ) {
            $attributes = new Woo_Feed_Default_Attributes();
            $options = $this->cache_dropdown( 'bestpriceAttributesDropdown', $attributes->bestpriceAttributes(), $selected );
        }
        return $options;
    }

	/**
	 * Daisycon Advertiser (General) Attribute list
	 *
	 * @param string $selected
	 *
	 * @return string
	 */
	public function daisyconAttributesDropdown( $selected = '' ) {
		$options = $this->get_cached_dropdown( 'daisycon_AttributesDropdown', $selected );
		if ( false === $options ) {
			$attributes = new Woo_Feed_Default_Attributes();
			$options = $this->cache_dropdown( 'daisycon_AttributesDropdown', $attributes->daisyconAttributes(), $selected );
		}
		return $options;
	}
	
	/**
	 * Daisycon Advertiser (Automotive) Attribute list
	 *
	 * @param string $selected
	 *
	 * @return string
	 */
	public function daisycon_automotiveAttributesDropdown( $selected = '' ) {
		$options = $this->get_cached_dropdown( 'daisycon_automotiveAttributesDropdown', $selected );
		if ( false === $options ) {
			$attributes = new Woo_Feed_Default_Attributes();
			$options = $this->cache_dropdown( 'daisycon_automotiveAttributesDropdown', $attributes->daisycon_automotiveAttributes(), $selected );
		}
		return $options;
	}
	
	/**
	 * Daisycon Advertiser (Books) Attribute list
	 *
	 * @param string $selected
	 *
	 * @return string
	 */
	public function daisycon_booksAttributesDropdown( $selected = '' ) {
		$options = $this->get_cached_dropdown( 'daisycon_booksAttributesDropdown', $selected );
		if ( false === $options ) {
			$attributes = new Woo_Feed_Default_Attributes();
			$options = $this->cache_dropdown( 'daisycon_booksAttributesDropdown', $attributes->daisycon_booksAttributes(), $selected );
		}
		return $options;
	}
	
	/**
	 * Daisycon Advertiser (Cosmetics) Attribute list
	 *
	 * @param string $selected
	 *
	 * @return string
	 */
	public function daisycon_cosmeticsAttributesDropdown( $selected = '' ) {
		$options = $this->get_cached_dropdown( 'daisycon_cosmeticsAttributesDropdown', $selected );
		if ( false === $options ) {
			$attributes = new Woo_Feed_Default_Attributes();
			$options = $this->cache_dropdown( 'daisycon_cosmeticsAttributesDropdown', $attributes->daisycon_cosmeticsAttributes(), $selected );
		}
		return $options;
	}
	
	/**
	 * Daisycon Advertiser (Daily Offers) Attribute list
	 *
	 * @param string $selected
	 *
	 * @return string
	 */
	public function daisycon_daily_offersAttributesDropdown( $selected = '' ) {
		$options = $this->get_cached_dropdown( 'daisycon_daily_offersAttributesDropdown', $selected );
		if ( false === $options ) {
			$attributes = new Woo_Feed_Default_Attributes();
			$options = $this->cache_dropdown( 'daisycon_daily_offersAttributesDropdown', $attributes->daisycon_daily_offersAttributes(), $selected );
		}
		return $options;
	}
	
	/**
	 * Daisycon Advertiser (Electronics) Attribute list
	 *
	 * @param string $selected
	 *
	 * @return string
	 */
	public function daisycon_electronicsAttributesDropdown( $selected = '' ) {
		$options = $this->get_cached_dropdown( 'daisycon_electronicsAttributesDropdown', $selected );
		if ( false === $options ) {
			$attributes = new Woo_Feed_Default_Attributes();
			$options = $this->cache_dropdown( 'daisycon_electronicsAttributesDropdown', $attributes->daisycon_electronicsAttributes(), $selected );
		}
		return $options;
	}
	
	/**
	 * Daisycon Advertiser (Food & Drinks) Attribute list
	 *
	 * @param string $selected
	 *
	 * @return string
	 */
	public function daisycon_food_drinksAttributesDropdown( $selected = '' ) {
		$options = $this->get_cached_dropdown( 'daisycon_food_drinksAttributesDropdown', $selected );
		if ( false === $options ) {
			$attributes = new Woo_Feed_Default_Attributes();
			$options = $this->cache_dropdown( 'daisycon_food_drinksAttributesDropdown', $attributes->daisycon_food_drinksAttributes(), $selected );
		}
		return $options;
	}
	
	/**
	 * Daisycon Advertiser (Home & Garden) Attribute list
	 *
	 * @param string $selected
	 *
	 * @return string
	 */
	public function daisycon_home_gardenAttributesDropdown( $selected = '' ) {
		$options = $this->get_cached_dropdown( 'daisycon_home_gardenAttributesDropdown', $selected );
		if ( false === $options ) {
			$attributes = new Woo_Feed_Default_Attributes();
			$options = $this->cache_dropdown( 'daisycon_home_gardenAttributesDropdown', $attributes->daisycon_home_gardenAttributes(), $selected );
		}
		return $options;
	}
	
	/**
	 * Daisycon Advertiser (Housing) Attribute list
	 *
	 * @param string $selected
	 *
	 * @return string
	 */
	public function daisycon_housingAttributesDropdown( $selected = '' ) {
		$options = $this->get_cached_dropdown( 'daisycon_housingAttributesDropdown', $selected );
		if ( false === $options ) {
			$attributes = new Woo_Feed_Default_Attributes();
			$options = $this->cache_dropdown( 'daisycon_housingAttributesDropdown', $attributes->daisycon_housingAttributes(), $selected );
		}
		return $options;
	}
	
	/**
	 * Daisycon Advertiser (Fashion) Attribute list
	 *
	 * @param string $selected
	 *
	 * @return string
	 */
	public function daisycon_fashionAttributesDropdown( $selected = '' ) {
		$options = $this->get_cached_dropdown( 'daisycon_fashionAttributesDropdown', $selected );
		if ( false === $options ) {
			$attributes = new Woo_Feed_Default_Attributes();
			$options = $this->cache_dropdown( 'daisycon_fashionAttributesDropdown', $attributes->daisycon_fashionAttributes(), $selected );
		}
		return $options;
	}
	
	/**
	 * Daisycon Advertiser (Studies & Trainings) Attribute list
	 *
	 * @param string $selected
	 *
	 * @return string
	 */
	public function daisycon_studies_trainingsAttributesDropdown( $selected = '' ) {
		$options = $this->get_cached_dropdown( 'daisycon_studies_trainingsAttributesDropdown', $selected );
		if ( false === $options ) {
			$attributes = new Woo_Feed_Default_Attributes();
			$options = $this->cache_dropdown( 'daisycon_studies_trainingsAttributesDropdown', $attributes->daisycon_studies_trainingsAttributes(), $selected );
		}
		return $options;
	}
	
	/**
	 * Daisycon Advertiser (Telecom: Accessories) Attribute list
	 *
	 * @param string $selected
	 *
	 * @return string
	 */
	public function daisycon_telecom_accessoriesAttributesDropdown( $selected = '' ) {
		$options = $this->get_cached_dropdown( 'daisycon_telecom_accessoriesAttributesDropdown', $selected );
		if ( false === $options ) {
			$attributes = new Woo_Feed_Default_Attributes();
			$options = $this->cache_dropdown( 'daisycon_telecom_accessoriesAttributesDropdown', $attributes->daisycon_telecom_accessoriesAttributes(), $selected );
		}
		return $options;
	}
	
	/**
	 * Daisycon Advertiser (Telecom: All-in-one) Attribute list
	 *
	 * @param string $selected
	 *
	 * @return string
	 */
	public function daisycon_telecom_all_in_oneAttributesDropdown( $selected = '' ) {
		$options = $this->get_cached_dropdown( 'daisycon_telecom_all_in_oneAttributesDropdown', $selected );
		if ( false === $options ) {
			$attributes = new Woo_Feed_Default_Attributes();
			$options = $this->cache_dropdown( 'daisycon_telecom_all_in_oneAttributesDropdown', $attributes->daisycon_telecom_all_in_oneAttributes(), $selected );
		}
		return $options;
	}
	
	/**
	 * Daisycon Advertiser (Telecom: GSM + Subscription) Attribute list
	 *
	 * @param string $selected
	 *
	 * @return string
	 */
	public function daisycon_telecom_gsm_subscriptionAttributesDropdown( $selected = '' ) {
		$options = $this->get_cached_dropdown( 'daisycon_telecom_gsm_subscriptionAttributesDropdown', $selected );
		if ( false === $options ) {
			$attributes = new Woo_Feed_Default_Attributes();
			$options = $this->cache_dropdown( 'daisycon_telecom_gsm_subscriptionAttributesDropdown', $attributes->daisycon_telecom_gsm_subscriptionAttributes(), $selected );
		}
		return $options;
	}
	
	/**
	 * Daisycon Advertiser (Telecom: GSM only) Attribute list
	 *
	 * @param string $selected
	 *
	 * @return string
	 */
	public function daisycon_telecom_gsmAttributesDropdown( $selected = '' ) {
		$options = $this->get_cached_dropdown( 'daisycon_telecom_gsmAttributesDropdown', $selected );
		if ( false === $options ) {
			$attributes = new Woo_Feed_Default_Attributes();
			$options = $this->cache_dropdown( 'daisycon_telecom_gsmAttributesDropdown', $attributes->daisycon_telecom_gsmAttributes(), $selected );
		}
		return $options;
	}
	
	/**
	 * Daisycon Advertiser (Telecom: Sim only) Attribute list
	 *
	 * @param string $selected
	 *
	 * @return string
	 */
	public function daisycon_telecom_simAttributesDropdown( $selected = '' ) {
		$options = $this->get_cached_dropdown( 'daisycon_telecom_simAttributesDropdown', $selected );
		if ( false === $options ) {
			$attributes = new Woo_Feed_Default_Attributes();
			$options = $this->cache_dropdown( 'daisycon_telecom_simAttributesDropdown', $attributes->daisycon_telecom_simAttributes(), $selected );
		}
		return $options;
	}
	
	/**
	 * Daisycon Advertiser (Magazines) Attribute list
	 *
	 * @param string $selected
	 *
	 * @return string
	 */
	public function daisycon_magazinesAttributesDropdown( $selected = '' ) {
		$options = $this->get_cached_dropdown( 'daisycon_magazinesAttributesDropdown', $selected );
		if ( false === $options ) {
			$attributes = new Woo_Feed_Default_Attributes();
			$options = $this->cache_dropdown( 'daisycon_magazinesAttributesDropdown', $attributes->daisycon_magazinesAttributes(), $selected );
		}
		return $options;
	}
	
	/**
	 * Daisycon Advertiser (Holidays: Accommodations) Attribute list
	 *
	 * @param string $selected
	 *
	 * @return string
	 */
	public function daisycon_holidays_accommodationsAttributesDropdown( $selected = '' ) {
		$options = $this->get_cached_dropdown( 'daisycon_holidays_accommodationsAttributesDropdown', $selected );
		if ( false === $options ) {
			$attributes = new Woo_Feed_Default_Attributes();
			$options = $this->cache_dropdown( 'daisycon_holidays_accommodationsAttributesDropdown', $attributes->daisycon_holidays_accommodationsAttributes(), $selected );
		}
		return $options;
	}
	
	/**
	 * Daisycon Advertiser (Holidays: Accommodations and transport) Attribute list
	 *
	 * @param string $selected
	 *
	 * @return string
	 */
	public function daisycon_holidays_accommodations_and_transportAttributesDropdown( $selected = '' ) {
		$options = $this->get_cached_dropdown( 'daisycon_holidays_accommodations_and_transportAttributesDropdown', $selected );
		if ( false === $options ) {
			$attributes = new Woo_Feed_Default_Attributes();
			$options = $this->cache_dropdown( 'daisycon_holidays_accommodations_and_transportAttributesDropdown', $attributes->daisycon_holidays_accommodations_and_transportAttributes(), $selected );
		}
		return $options;
	}
	
	/**
	 * Daisycon Advertiser (Holidays: Trips) Attribute list
	 *
	 * @param string $selected
	 *
	 * @return string
	 */
	public function daisycon_holidays_tripsAttributesDropdown( $selected = '' ) {
		$options = $this->get_cached_dropdown( 'daisycon_holidays_tripsAttributesDropdown', $selected );
		if ( false === $options ) {
			$attributes = new Woo_Feed_Default_Attributes();
			$options = $this->cache_dropdown( 'daisycon_holidays_tripsAttributesDropdown', $attributes->daisycon_holidays_tripsAttributes(), $selected );
		}
		return $options;
	}
	
	/**
	 * Daisycon Advertiser (Work & Jobs) Attribute list
	 *
	 * @param string $selected
	 *
	 * @return string
	 */
	public function daisycon_work_jobsAttributesDropdown( $selected = '' ) {
		$options = $this->get_cached_dropdown( 'daisycon_work_jobsAttributesDropdown', $selected );
		if ( false === $options ) {
			$attributes = new Woo_Feed_Default_Attributes();
			$options = $this->cache_dropdown( 'daisycon_work_jobsAttributesDropdown', $attributes->daisycon_work_jobsAttributes(), $selected );
		}
		return $options;
	}
}
