<?php /** @noinspection PhpDeprecationInspection */
/** @noinspection DuplicatedCode */

/**
 * This is used to store all the information about wooCommerce store products
 *
 * @since      1.0.0
 * @package    Woo_Feed
 * @subpackage Woo_Feed/includes
 * @author     Ohidul Islam <wahid@webappick.com>
 */
class Woo_Feed_Products {

	/**
	 * Contain all parent product information for the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $parent Contain all parent product information for the plugin.
	 */
	public $parent;

	/**
	 * Contain all child product information for the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $parent Contain all child product information for the plugin.
	 */
	public $child;

	/**
	 * The parent id of current product.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $parentID The current product's Parent ID.
	 */
	public $parentID;
	/**
	 * The child id of current product.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $parentID The current product's child ID.
	 */
	public $childID;

	/**
	 * The Variable that contain all products.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array $productsList Products list array.
	 */
	public $productsList;

	/**
	 * The Variable that contain all attributes.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array $attributeList attributes list array.
	 */
	public $attributeList;

	public $feedRule;
	public $idExist = array();
	public $pi;



	public function wooProductQuery( $arg ) {

		global $wpdb;


		$limit = '';
		$offset = '';
		if ( '' != $arg['limit'] && '-1' != $arg['limit'] && $arg['limit'] > 0 ) {
			$limit = absint( $arg['limit'] );
			$limit = "LIMIT $limit";
			
			
			if ( '' != $arg['offset'] && '-1' != $arg['offset'] ) {
				$offset = absint( $arg['offset'] );
				$offset = "OFFSET $offset";
			}
		}



		$query = "SELECT DISTINCT $wpdb->posts.ID
				FROM $wpdb->posts
				LEFT JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id)
				LEFT JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
				LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id)
				WHERE $wpdb->posts.post_status = 'publish' AND $wpdb->posts.post_type IN ('product','product_variation')
				ORDER BY ID DESC $limit $offset";
		$products = $wpdb->get_results($query, ARRAY_A ); // phpcs:ignore
		return $products;
	}

	/**
	 * Get All Products
	 * @since  2.2.0
	 * @for    WC 3.1+
	 * @param $limit
	 * @param $offset
	 *
	 * @return array
	 */

	public function getWC3Products( $limit, $offset ) {
		wp_cache_delete( 'wf_check_duplicate', 'options' );
		$limit  = ! empty( $limit ) && is_numeric( $limit ) ? absint( $limit ) : '2000';
		$offset = ! empty( $offset ) && is_numeric( $offset ) ? absint( $offset ) : '0';

		# Process Duplicate Products
		if ( '0' == $offset ) {
			delete_option( "wf_check_duplicate" );
		}
		$getIDs = get_option( "wf_check_duplicate" );

		# Query Arguments
		$arg = array(
			'limit'   => $limit,
			'offset'  => $offset,
			'status'  => 'publish',
			'orderby' => 'date',
			'order'   => 'DESC',
			'type'    => array( 'variable', 'variation', 'simple', 'grouped', 'external' ),
			'return'  => 'ids',
		);


		# Don't Include Variations for Facebook
//		if ( $this->feedRule['provider'] == 'facebook' ) {
//			$this->feedRule['is_variations'] = 'n';
//		}

		$query    = new WC_Product_Query( $arg );
		$products = $query->get_products();

		$this->pi = 1; # Product Index
		foreach ( $products as $key => $productId ) {
			$prod = wc_get_product($productId);

			$id  = $prod->get_id();
			
			if ( $getIDs ) {
				if ( in_array( $id, $getIDs ) ) {
					continue;
				} else {
					array_push( $this->idExist, $id );
				}
            }else {
				array_push($this->idExist,$id);
			}

			if ( $prod->is_type( 'simple' ) ) {

				$simple = new WC_Product_Simple( $id );

				$this->productsList[ $this->pi ]['id']                = $simple->get_id();
				$this->productsList[ $this->pi ]['title']             = $simple->get_name();
				$this->productsList[ $this->pi ]['description']       = $this->remove_short_codes($simple->get_description());
				$this->productsList[ $this->pi ]['variation_type']    = $simple->get_type();

				$this->productsList[ $this->pi ]['short_description'] = $this->remove_short_codes($simple->get_short_description());
				$this->productsList[ $this->pi ]['product_type']      = wp_strip_all_tags( wc_get_product_category_list( $id, ">", '' ) );
				$this->productsList[ $this->pi ]['link']              = $simple->get_permalink();
				$this->productsList[ $this->pi ]['ex_link']           = $simple->get_permalink();


				# Featured Image
				if ( has_post_thumbnail( $id ) ) :
					$image                                            = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'single-post-thumbnail' );
					$this->productsList[ $this->pi ]['image']         = $this->get_formatted_url( $image[0] );
					$this->productsList[ $this->pi ]['feature_image'] = $this->get_formatted_url( $image[0] );
				else :
					$this->productsList[ $this->pi ]['image']         = $this->get_formatted_url( wp_get_attachment_url( $id ) );
					$this->productsList[ $this->pi ]['feature_image'] = $this->get_formatted_url( wp_get_attachment_url( $id ) );
				endif;

				# Additional Images
				$attachmentIds = $simple->get_gallery_image_ids();
				$imgUrls       = array();
				if ( $attachmentIds && is_array( $attachmentIds ) ) {
					$mKey = 1;
					foreach ( $attachmentIds as $attachmentId ) {
						$imgUrls[ $mKey ]                               = $this->get_formatted_url( wp_get_attachment_url( $attachmentId ) );
						$this->productsList[ $this->pi ][ "image_$mKey" ] = $imgUrls[ $mKey ];
						$mKey ++;
					}               
}

				$this->productsList[ $this->pi ]['images']           = implode( ',', $imgUrls );
				$this->productsList[ $this->pi ]['condition']        = 'new';
				$this->productsList[ $this->pi ]['type']             = $simple->get_type();
				$this->productsList[ $this->pi ]['is_bundle']        = 'no';
				$this->productsList[ $this->pi ]['multipack']        = '';
				$this->productsList[ $this->pi ]['visibility']       = $simple->get_catalog_visibility();
				$this->productsList[ $this->pi ]['rating_total']     = $simple->get_rating_count();
				$this->productsList[ $this->pi ]['rating_average']   = $simple->get_average_rating();
				$this->productsList[ $this->pi ]['tags']             = $this->getProductTaxonomy($id,'product_tag');
				$this->productsList[ $this->pi ]['item_group_id']    = $simple->get_id();
				$this->productsList[ $this->pi ]['sku']              = $simple->get_sku();
				$this->productsList[ $this->pi ]['parent_sku']       = $simple->get_sku();
				$this->productsList[ $this->pi ]['availability']     = $this->availability( $id );
				$this->productsList[ $this->pi ]['quantity']         = $simple->get_stock_quantity();
				$this->productsList[ $this->pi ]['sale_price_sdate'] = $simple->get_date_on_sale_from();
				$this->productsList[ $this->pi ]['sale_price_edate'] = $simple->get_date_on_sale_to();
				$this->productsList[ $this->pi ]['price']            = $simple->get_regular_price();
				$this->productsList[ $this->pi ]['current_price']    = $simple->get_price();
				$this->productsList[ $this->pi ]['price_with_tax']   = $this->getPriceWithTax($simple);
				$this->productsList[ $this->pi ]['sale_price']       = $simple->get_sale_price();
				$this->productsList[ $this->pi ]['weight']           = $simple->get_weight();
				$this->productsList[ $this->pi ]['width']            = $simple->get_width();
				$this->productsList[ $this->pi ]['height']           = $simple->get_height();
				$this->productsList[ $this->pi ]['length']           = $simple->get_length();
				$this->productsList[ $this->pi ]['shipping_class']   = $simple->get_shipping_class();

                $this->productsList[ $this->pi ]['date_created']   = $this->format_product_date($simple->get_date_created());
                $this->productsList[ $this->pi ]['date_updated']   = $this->format_product_date($simple->get_date_modified());

				# Sale price effective date
				$from = $this->sale_price_effective_date( $id, '_sale_price_dates_from' );
				$to   = $this->sale_price_effective_date( $id, '_sale_price_dates_to' );
				if ( ! empty( $from ) && ! empty( $to ) ) {
					$from                                                         = gmdate( "c", strtotime( $from ) );
					$to                                                           = gmdate( "c", strtotime( $to ) );
					$this->productsList[ $this->pi ]['sale_price_effective_date'] = $from . '/' . $to;
				} else {
					$this->productsList[ $this->pi ]['sale_price_effective_date'] = '';
				}

				// # Get all Attributes and their values
				// $data=$simple->get_data();
				// $attributes=$data['attributes'];
				// if(!empty($attributes)){
				// 	foreach ($attributes as $aKey=>$attr){
				// 		$this->productsList[ $this->pi ][$aKey] = $simple->get_attribute($aKey);
				// 	}
				// }
			}
			elseif ( $prod->is_type( 'external' ) ) {

				$external = new WC_Product_External( $id );

				$this->productsList[ $this->pi ]['id']                = $external->get_id();
				$this->productsList[ $this->pi ]['title']             = $external->get_name();
				$this->productsList[ $this->pi ]['description']       = $this->remove_short_codes($external->get_description());
				$this->productsList[ $this->pi ]['variation_type']    = $external->get_type();
				$this->productsList[ $this->pi ]['short_description'] = $this->remove_short_codes($external->get_short_description());
				$this->productsList[ $this->pi ]['product_type']      = wp_strip_all_tags( wc_get_product_category_list( $id, ">", '' ) );
				$this->productsList[ $this->pi ]['link']              = $external->get_permalink();
				$this->productsList[ $this->pi ]['ex_link']           = $external->get_product_url();


				# Featured Image
				if ( has_post_thumbnail( $id ) ) :
					$image                                            = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'single-post-thumbnail' );
					$this->productsList[ $this->pi ]['image']         = $this->get_formatted_url( $image[0] );
					$this->productsList[ $this->pi ]['feature_image'] = $this->get_formatted_url( $image[0] );
				else :
					$this->productsList[ $this->pi ]['image']         = $this->get_formatted_url( wp_get_attachment_url( $id ) );
					$this->productsList[ $this->pi ]['feature_image'] = $this->get_formatted_url( wp_get_attachment_url( $id ) );
				endif;

				# Additional Images
				$attachmentIds = $external->get_gallery_image_ids();
				$imgUrls       = array();
				if ( $attachmentIds && is_array( $attachmentIds ) ) {
					$mKey = 1;
					foreach ( $attachmentIds as $attachmentId ) {
						$imgUrls[ $mKey ]                               = $this->get_formatted_url( wp_get_attachment_url( $attachmentId ) );
						$this->productsList[ $this->pi ][ "image_$mKey" ] = $imgUrls[ $mKey ];
						$mKey ++;
					}               
}

				$this->productsList[ $this->pi ]['images']           = implode( ',', $imgUrls );
				$this->productsList[ $this->pi ]['condition']        = 'new';
				$this->productsList[ $this->pi ]['type']             = $external->get_type();
				$this->productsList[ $this->pi ]['is_bundle']        = 'no';
				$this->productsList[ $this->pi ]['multipack']        = '';
				$this->productsList[ $this->pi ]['visibility']       = $external->get_catalog_visibility();
				$this->productsList[ $this->pi ]['rating_total']     = $external->get_rating_count();
				$this->productsList[ $this->pi ]['rating_average']   = $external->get_average_rating();
				$this->productsList[ $this->pi ]['tags']             = $this->getProductTaxonomy($id,'product_tag');
				$this->productsList[ $this->pi ]['item_group_id']    = $external->get_id();
				$this->productsList[ $this->pi ]['sku']              = $external->get_sku();
				$this->productsList[ $this->pi ]['parent_sku']       = $external->get_sku();
				$this->productsList[ $this->pi ]['availability']     = $this->availability( $id );
				$this->productsList[ $this->pi ]['quantity']         = $external->get_stock_quantity();
				$this->productsList[ $this->pi ]['sale_price_sdate'] = $external->get_date_on_sale_from();
				$this->productsList[ $this->pi ]['sale_price_edate'] = $external->get_date_on_sale_to();
				$this->productsList[ $this->pi ]['price']            = $external->get_regular_price();
                $this->productsList[ $this->pi ]['current_price']    = $external->get_price();
                $this->productsList[ $this->pi ]['price_with_tax']   = $this->getPriceWithTax($external);
				$this->productsList[ $this->pi ]['sale_price']       = $external->get_sale_price();
				$this->productsList[ $this->pi ]['weight']           = $external->get_weight();
				$this->productsList[ $this->pi ]['width']            = $external->get_width();
				$this->productsList[ $this->pi ]['height']           = $external->get_height();
				$this->productsList[ $this->pi ]['length']           = $external->get_length();
				$this->productsList[ $this->pi ]['shipping_class']   = $external->get_shipping_class();

                $this->productsList[ $this->pi ]['date_created']   = $this->format_product_date($external->get_date_created());
                $this->productsList[ $this->pi ]['date_updated']   = $this->format_product_date($external->get_date_modified());

				# Sale price effective date
				$from = $this->sale_price_effective_date( $id, '_sale_price_dates_from' );
				$to   = $this->sale_price_effective_date( $id, '_sale_price_dates_to' );
				if ( ! empty( $from ) && ! empty( $to ) ) {
					$from                                                         = gmdate( "c", strtotime( $from ) );
					$to                                                           = gmdate( "c", strtotime( $to ) );
					$this->productsList[ $this->pi ]['sale_price_effective_date'] = $from . '/' . $to;
				} else {
					$this->productsList[ $this->pi ]['sale_price_effective_date'] = '';
				}

				# Get all Attributes and their values
				// $data=$external->get_data();
				// $attributes=$data['attributes'];
				// if(!empty($attributes)){
				// 	foreach ($attributes as $aKey=>$attr){
				// 		$this->productsList[ $this->pi ][$aKey] = $external->get_attribute($aKey);
				// 	}
				// }
			}
			elseif ( $prod->is_type( 'grouped' ) ) {

				$grouped = new WC_Product_Grouped( $id );

				$this->productsList[ $this->pi ]['id']             = $grouped->get_id();
				$this->productsList[ $this->pi ]['title']          = $grouped->get_name();
				$this->productsList[ $this->pi ]['description']    = $this->remove_short_codes($grouped->get_description());
				$this->productsList[ $this->pi ]['variation_type'] = $grouped->get_type();

				$this->productsList[ $this->pi ]['short_description'] = $this->remove_short_codes($grouped->get_short_description());
				$this->productsList[ $this->pi ]['product_type']      = wp_strip_all_tags( wc_get_product_category_list( $id, ">", '' ) );//$this->get_product_term_list( $post->ID, 'product_cat', '', ">" );// $this->categories($this->parentID);//TODO
				$this->productsList[ $this->pi ]['link']              = $grouped->get_permalink();
				$this->productsList[ $this->pi ]['ex_link']           = $grouped->get_permalink();

				# Featured Image
				if ( has_post_thumbnail( $id ) ) :
					$image                                            = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'single-post-thumbnail' );
					$this->productsList[ $this->pi ]['image']         = $this->get_formatted_url( $image[0] );
					$this->productsList[ $this->pi ]['feature_image'] = $this->get_formatted_url( $image[0] );
				else :
					$this->productsList[ $this->pi ]['image']         = $this->get_formatted_url( wp_get_attachment_url( $id ) );
					$this->productsList[ $this->pi ]['feature_image'] = $this->get_formatted_url( wp_get_attachment_url( $id ) );
				endif;

				# Additional Images
				$attachmentIds = $grouped->get_gallery_image_ids();
				$imgUrls       = array();
				if ( $attachmentIds && is_array( $attachmentIds ) ) {
					$mKey = 1;
					foreach ( $attachmentIds as $attachmentId ) {
						$imgUrls[ $mKey ]                               = $this->get_formatted_url( wp_get_attachment_url( $attachmentId ) );
						$this->productsList[ $this->pi ][ "image_$mKey" ] = $imgUrls[ $mKey ];
						$mKey ++;
					}               
}

				$this->productsList[ $this->pi ]['images']           = implode( ',', $imgUrls );
				$this->productsList[ $this->pi ]['condition']        = 'new';
				$this->productsList[ $this->pi ]['type']             = $grouped->get_type();
				$this->productsList[ $this->pi ]['is_bundle']        = 'no';
				$this->productsList[ $this->pi ]['multipack']        = ( count( $grouped->get_children() ) > 0 ) ? count( $grouped->get_children() ) : '';
				$this->productsList[ $this->pi ]['visibility']       = $grouped->get_catalog_visibility();
				$this->productsList[ $this->pi ]['rating_total']     = $grouped->get_rating_count();
				$this->productsList[ $this->pi ]['rating_average']   = $grouped->get_average_rating();
				$this->productsList[ $this->pi ]['tags']             = $this->getProductTaxonomy($id,'product_tag');
				$this->productsList[ $this->pi ]['item_group_id']    = $grouped->get_id();
				$this->productsList[ $this->pi ]['sku']              = $grouped->get_sku();
				$this->productsList[ $this->pi ]['parent_sku']       = $grouped->get_sku();
				$this->productsList[ $this->pi ]['availability']     = $this->availability( $id );
				$this->productsList[ $this->pi ]['quantity']         = $grouped->get_stock_quantity();
				$this->productsList[ $this->pi ]['sale_price_sdate'] = $grouped->get_date_on_sale_from();
				$this->productsList[ $this->pi ]['sale_price_edate'] = $grouped->get_date_on_sale_to();
				$this->productsList[ $this->pi ]['price']            = $this->getGroupProductPrice($grouped,"regular");
                $this->productsList[ $this->pi ]['current_price']    = $this->getGroupProductPrice($grouped,"sale");
				$this->productsList[ $this->pi ]['sale_price']       = $this->getGroupProductPrice($grouped,"sale");
				$this->productsList[ $this->pi ]['weight']           = $grouped->get_weight();
				$this->productsList[ $this->pi ]['width']            = $grouped->get_width();
				$this->productsList[ $this->pi ]['height']           = $grouped->get_height();
				$this->productsList[ $this->pi ]['length']           = $grouped->get_length();
				$this->productsList[ $this->pi ]['shipping_class']   = $grouped->get_shipping_class();

                $this->productsList[ $this->pi ]['date_created']   = $this->format_product_date($grouped->get_date_created());
                $this->productsList[ $this->pi ]['date_updated']   = $this->format_product_date($grouped->get_date_modified());

				# Sale price effective date
				$from = $this->sale_price_effective_date( $id, '_sale_price_dates_from' );
				$to   = $this->sale_price_effective_date( $id, '_sale_price_dates_to' );
				if ( ! empty( $from ) && ! empty( $to ) ) {
					$from                                                         = gmdate( "c", strtotime( $from ) );
					$to                                                           = gmdate( "c", strtotime( $to ) );
					$this->productsList[ $this->pi ]['sale_price_effective_date'] = $from . '/' . $to;
				} else {
					$this->productsList[ $this->pi ]['sale_price_effective_date'] = '';
				}

				# Get all Attributes and their values
				// $data=$grouped->get_data();
				// $attributes=$data['attributes'];
				// if(!empty($attributes)){
				// 	foreach ($attributes as $aKey=>$attr){
				// 		$this->productsList[ $this->pi ][$aKey] = $grouped->get_attribute($aKey);
				// 	}
				// }
			}
			elseif ( $prod->is_type( 'variable' ) ) {

				$variable = new WC_Product_Variable( $id );

				$this->productsList[ $this->pi ]['id']             = $variable->get_id();
				$this->productsList[ $this->pi ]['title']          = $variable->get_name();
				$this->productsList[ $this->pi ]['description']    = $this->remove_short_codes($variable->get_description());
				$this->productsList[ $this->pi ]['variation_type'] = $variable->get_type();

				$this->productsList[ $this->pi ]['short_description'] = $this->remove_short_codes($variable->get_short_description());
				$this->productsList[ $this->pi ]['product_type']      = wp_strip_all_tags( wc_get_product_category_list( $id, ">", '' ) );
				$this->productsList[ $this->pi ]['link']              = $variable->get_permalink();
				$this->productsList[ $this->pi ]['ex_link']           = $variable->get_permalink();


				# Featured Image
				if ( has_post_thumbnail( $id ) ) :
					$image                                            = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'single-post-thumbnail' );
					$this->productsList[ $this->pi ]['image']         = $this->get_formatted_url( $image[0] );
					$this->productsList[ $this->pi ]['feature_image'] = $this->get_formatted_url( $image[0] );
				else :
					$this->productsList[ $this->pi ]['image']         = $this->get_formatted_url( wp_get_attachment_url( $id ) );
					$this->productsList[ $this->pi ]['feature_image'] = $this->get_formatted_url( wp_get_attachment_url( $id ) );
				endif;

				# Additional Images
				$attachmentIds = $variable->get_gallery_image_ids();
				$imgUrls       = array();
				if ( $attachmentIds && is_array( $attachmentIds ) ) {
					$mKey = 1;
					foreach ( $attachmentIds as $attachmentId ) {
						$imgUrls[ $mKey ]                               = $this->get_formatted_url( wp_get_attachment_url( $attachmentId ) );
						$this->productsList[ $this->pi ][ "image_$mKey" ] = $imgUrls[ $mKey ];
						$mKey ++;
					}               
}

				$this->productsList[ $this->pi ]['images']         = implode( ',', $imgUrls );
				$this->productsList[ $this->pi ]['condition']      = 'new';
				$this->productsList[ $this->pi ]['type']           = $variable->get_type();
				$this->productsList[ $this->pi ]['is_bundle']      = 'no';
				$this->productsList[ $this->pi ]['multipack']      = '';
				$this->productsList[ $this->pi ]['visibility']     = $variable->get_catalog_visibility();
				$this->productsList[ $this->pi ]['rating_total']   = $variable->get_rating_count();
				$this->productsList[ $this->pi ]['rating_average'] = $variable->get_average_rating();
				$this->productsList[ $this->pi ]['tags']           = $this->getProductTaxonomy($id,'product_tag');
				$this->productsList[ $this->pi ]['item_group_id']  = $variable->get_id();
				$this->productsList[ $this->pi ]['sku']            = $variable->get_sku();
				$this->productsList[ $this->pi ]['parent_sku']     = $variable->get_sku();
				$this->productsList[ $this->pi ]['availability']   = $this->availability( $id );


				$this->productsList[ $this->pi ]['quantity']         = $variable->get_stock_quantity();
				$this->productsList[ $this->pi ]['sale_price_sdate'] = $variable->get_date_on_sale_from();
				$this->productsList[ $this->pi ]['sale_price_edate'] = $variable->get_date_on_sale_to();
				$this->productsList[ $this->pi ]['price']            = $variable->get_variation_regular_price();
                $this->productsList[ $this->pi ]['current_price']    = $variable->get_variation_price();
                $this->productsList[ $this->pi ]['price_with_tax']   = $this->getPriceWithTax($variable);
				$this->productsList[ $this->pi ]['sale_price']       = $variable->get_variation_sale_price();
				$this->productsList[ $this->pi ]['weight']           = $variable->get_weight();
				$this->productsList[ $this->pi ]['width']            = $variable->get_width();
				$this->productsList[ $this->pi ]['height']           = $variable->get_height();
				$this->productsList[ $this->pi ]['length']           = $variable->get_length();
				$this->productsList[ $this->pi ]['shipping_class']   = $variable->get_shipping_class();

                $this->productsList[ $this->pi ]['date_created']   = $this->format_product_date($variable->get_date_created());
                $this->productsList[ $this->pi ]['date_updated']   = $this->format_product_date($variable->get_date_modified());

				# Sale price effective date
				$from = $this->sale_price_effective_date( $id, '_sale_price_dates_from' );
				$to   = $this->sale_price_effective_date( $id, '_sale_price_dates_to' );
				if ( ! empty( $from ) && ! empty( $to ) ) {
					$from                                                         = gmdate( "c", strtotime( $from ) );
					$to                                                           = gmdate( "c", strtotime( $to ) );
					$this->productsList[ $this->pi ]['sale_price_effective_date'] = $from . '/' . $to;
				} else {
					$this->productsList[ $this->pi ]['sale_price_effective_date'] = '';
				}

				# Get all Attributes and their values
				// $data=$variable->get_data();
				// $attributes=$data['attributes'];
				// if(!empty($attributes)){
				// 	foreach ($attributes as $aKey=>$attr){
				// 		$this->productsList[ $this->pi ][$aKey] = $variable->get_attribute($aKey);
				// 	}
				// }
			}
			elseif ( $prod->is_type( 'variation' ) ) {

				$variation = new WC_Product_Variation($id);

				# Parent Info
                $parentId = $variation->get_parent_id();
				$parent = new WC_Product_Variable($parentId);

				# Skip if not a valid product
				if ( ! $variation->variation_is_visible() ) {
					continue;
				}

				if ( ! $parent->is_purchasable() ) {
					continue;
				}

				# Set Variation Description
				$description = $variation->get_description();
				if ( empty($description) ) {
					$description = $parent->get_description();
				}
				$description = $this->remove_short_codes($description);

				# Set Variation Short Description
				$short_description = $variation->get_short_description();
				if ( empty($short_description) ) {
					$short_description = $parent->get_short_description();
				}
				$short_description = $this->remove_short_codes($short_description);

				$this->productsList[ $this->pi ]['id']                = $variation->get_id();
				$this->productsList[ $this->pi ]['title']             = $variation->get_name();

				if ( 'facebook' == $this->feedRule['provider'] ) {
				    $variationInfo = explode("-",$variation->get_name());
				    if ( isset($variationInfo[1]) ) {
                        $extension = $variationInfo[1];
                    }else {
                        $extension = $variation->get_id();
                    }
				    $description .= ''.$extension;
                }

				$this->productsList[ $this->pi ]['description']       = $description;
				$this->productsList[ $this->pi ]['variation_type']    = $variation->get_type();
				$this->productsList[ $this->pi ]['short_description'] = $short_description;
				$this->productsList[ $this->pi ]['product_type']      = wp_strip_all_tags(wc_get_product_category_list($variation->get_parent_id(),">",''));
				$this->productsList[ $this->pi ]['link']              = $variation->get_permalink();
				$this->productsList[ $this->pi ]['ex_link']           = $variation->get_permalink();


				# Featured Image
				$image = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'single-post-thumbnail' );
				if ( has_post_thumbnail( $id ) && ! empty($image[0]) ) :

					$this->productsList[ $this->pi ]['image']         = $this->get_formatted_url( $image[0] );
					$this->productsList[ $this->pi ]['feature_image'] = $this->get_formatted_url( $image[0] );
				else :
					$image = wp_get_attachment_image_src( get_post_thumbnail_id( $variation->get_parent_id() ), 'single-post-thumbnail' );
					$this->productsList[ $this->pi ]['image']         = $this->get_formatted_url( $image[0] );
					$this->productsList[ $this->pi ]['feature_image'] = $this->get_formatted_url( $image[0] );
				endif;

				# Additional Images
				$attachmentIds = $variation->get_gallery_image_ids();
				$imgUrls = array();
				if ( $attachmentIds && is_array( $attachmentIds ) ) {
					$mKey = 1;
					foreach ( $attachmentIds as $attachmentId ) {
						$imgUrls[ $mKey ] = $this->get_formatted_url( wp_get_attachment_url( $attachmentId ));
						$this->productsList[ $this->pi ][ "image_$mKey" ] = $imgUrls[ $mKey ];
						$mKey ++;
					}               
}

				$this->productsList[ $this->pi ]['images']         = implode( ',', $imgUrls );
				$this->productsList[ $this->pi ]['condition']      = 'new';
				$this->productsList[ $this->pi ]['type']           = $variation->get_type();
				$this->productsList[ $this->pi ]['is_bundle']      = 'no';
				$this->productsList[ $this->pi ]['multipack']      = '';
				$this->productsList[ $this->pi ]['visibility']     = $variation->get_catalog_visibility();
				$this->productsList[ $this->pi ]['rating_total']   = $variation->get_rating_count();
				$this->productsList[ $this->pi ]['rating_average'] = $variation->get_average_rating();
				$this->productsList[ $this->pi ]['tags']           = $this->getProductTaxonomy($parentId,'product_tag');
				$this->productsList[ $this->pi ]['item_group_id']  = $variation->get_parent_id();
				$this->productsList[ $this->pi ]['sku']            = $variation->get_sku();
				$this->productsList[ $this->pi ]['parent_sku']     = $parent->get_sku();
				$this->productsList[ $this->pi ]['availability']   = $this->availability( $id );
				$this->productsList[ $this->pi ]['quantity']         = $variation->get_stock_quantity();
				$this->productsList[ $this->pi ]['sale_price_sdate'] = $variation->get_date_on_sale_from();
				$this->productsList[ $this->pi ]['sale_price_edate'] = $variation->get_date_on_sale_to();
				$this->productsList[ $this->pi ]['price']            = $variation->get_regular_price();
                $this->productsList[ $this->pi ]['current_price']    = $variation->get_price();
                $this->productsList[ $this->pi ]['price_with_tax']   = $this->getPriceWithTax($variation);
				$this->productsList[ $this->pi ]['sale_price']       = $variation->get_sale_price();
				$this->productsList[ $this->pi ]['weight']           = $variation->get_weight();
				$this->productsList[ $this->pi ]['width']            = $variation->get_width();
				$this->productsList[ $this->pi ]['height']           = $variation->get_height();
				$this->productsList[ $this->pi ]['length']           = $variation->get_length();
				$this->productsList[ $this->pi ]['shipping_class']   = $variation->get_shipping_class();

                $this->productsList[ $this->pi ]['date_created']   = $this->format_product_date($variation->get_date_created());
                $this->productsList[ $this->pi ]['date_updated']   = $this->format_product_date($variation->get_date_modified());

				# Sale price effective date
				$from = $this->sale_price_effective_date( $id, '_sale_price_dates_from' );
				$to   = $this->sale_price_effective_date( $id, '_sale_price_dates_to' );
				if ( ! empty( $from ) && ! empty( $to ) ) {
					$from                                                  = gmdate( "c", strtotime( $from ) );
					$to                                                    = gmdate( "c", strtotime( $to ) );
					$this->productsList[ $this->pi ]['sale_price_effective_date'] = $from . '/' . $to;
				} else {
					$this->productsList[ $this->pi ]['sale_price_effective_date'] = '';
				}

				# Get all Attributes and their values
				// $data=$variation->get_data();
				// $attributes=$data['attributes'];
				// if(!empty($attributes)){
				// 	foreach ($attributes as $aKey=>$attr){
				// 		$this->productsList[ $this->pi ][$aKey] = $variation->get_attribute($aKey);
				// 	}
				// }
			}

			$this->pi ++;
		}

		if ( $getIDs ) {
			$mergedIds = array_merge($getIDs,$this->idExist);
			update_option("wf_check_duplicate",$mergedIds);
		}else {
			update_option("wf_check_duplicate",$this->idExist);
		}

		return $this->productsList;
	}
	
	/**
	 * Get total price of grouped product
	 *
	 * @since 2.2.14
	 * @param WC_Product_Grouped $grouped
	 * @param string $type
	 * @param bool $taxable
	 * @return int|string
	 */
	public function getGroupProductPrice( $grouped, $type, $taxable = false ) {
		$groupProductIds = $grouped->get_children();
		$sum = 0;
		if ( ! empty( $groupProductIds ) ) {
			foreach ( $groupProductIds as $id ) {
				$product = wc_get_product( $id );
				if ( ! $product ) {
					continue; // make sure that the product exists..
				}
				$regularPrice = $product->get_regular_price();
				if ( empty( $regularPrice ) ) $regularPrice = 0;
				$currentPrice = $product->get_price();
				if ( empty( $currentPrice ) ) $currentPrice = 0;
				if ( 'regular' == $type ) {
					if ( $taxable ) {
						$regularPrice = $this->getPriceWithTax( $product );
					}
					$sum += $regularPrice;
				} else {
					if ( $taxable ) {
						$currentPrice = $this->getPriceWithTax( $product );
					}
					$sum += $currentPrice;
				}
			}
		}
		
		return $sum;
	}

	/**
	 * Get Product Variations of a variable product
	 * @since  2.2.0
	 * @for    WC 3.1+
	 * @param $variations
	 * @param $parent
	 */
	public function getWC3Variations( $variations, $parent ) {
		try {
			if ( is_array($variations) && (sizeof($variations) > 0 ) ) {
				foreach ( $variations as $vKey => $variationProd ) {

					$id = $variationProd['variation_id'];

					$variation = new WC_Product_Variation($id);

					if ( ! is_object($variation) ) {
						continue;
					}
					$parentId = $variation->get_parent_id();
					$parent = new WC_Product_Variable($parentId);
					# Parent Info
					$parentInfo = $variation->get_parent_data();
					$variationTitle = $parentInfo['title'];

					# Set Variation Description
					$description = $variation->get_description();
					if ( empty($description) ) {
						$description = $parent->get_description();
					}
					$description = $this->remove_short_codes($description);

					# Set Variation Short Description
					$short_description = $variation->get_short_description();
					if ( empty($short_description) ) {
						$short_description = $parent->get_short_description();
					}
					$short_description = $this->remove_short_codes($short_description);

					$this->productsList[ $this->pi ]['id']                = $variation->get_id();
					$this->productsList[ $this->pi ]['title']             = $variationTitle;
					$this->productsList[ $this->pi ]['description']       = $description;
					$this->productsList[ $this->pi ]['variation_type']    = $variation->get_type();

					$this->productsList[ $this->pi ]['short_description'] = $short_description;
					$this->productsList[ $this->pi ]['product_type']      = wp_strip_all_tags(wc_get_product_category_list($variation->get_parent_id(),">",''));
					$this->productsList[ $this->pi ]['link']              = $variation->get_permalink();
					$this->productsList[ $this->pi ]['ex_link']           = $variation->get_permalink();


					# Featured Image
					$image = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'single-post-thumbnail' );
					if ( has_post_thumbnail( $id ) && ! empty($image[0]) ) :

						$this->productsList[ $this->pi ]['image']         = $this->get_formatted_url( $image[0] );
						$this->productsList[ $this->pi ]['feature_image'] = $this->get_formatted_url( $image[0] );
					else :
						$image = wp_get_attachment_image_src( get_post_thumbnail_id( $variation->get_parent_id() ), 'single-post-thumbnail' );
						$this->productsList[ $this->pi ]['image']         = $this->get_formatted_url( $image[0] );
						$this->productsList[ $this->pi ]['feature_image'] = $this->get_formatted_url( $image[0] );
					endif;

					# Additional Images
					$attachmentIds = $variation->get_gallery_image_ids();
					$imgUrls = array();
					if ( $attachmentIds && is_array( $attachmentIds ) ) {
						$mKey = 1;
						foreach ( $attachmentIds as $attachmentId ) {
							$imgUrls[ $mKey ] = $this->get_formatted_url( wp_get_attachment_url( $attachmentId ));
							$this->productsList[ $this->pi ][ "image_$mKey" ] = $imgUrls[ $mKey ];
							$mKey ++;
						}                   
}

					$this->productsList[ $this->pi ]['images']         = implode( ',', $imgUrls );
					$this->productsList[ $this->pi ]['condition']      = 'new';
					$this->productsList[ $this->pi ]['type']           = $variation->get_type();
					$this->productsList[ $this->pi ]['is_bundle']      = 'no';
					$this->productsList[ $this->pi ]['multipack']      = '';
					$this->productsList[ $this->pi ]['visibility']     = $variation->get_catalog_visibility();
					$this->productsList[ $this->pi ]['rating_total']   = $variation->get_rating_count();
					$this->productsList[ $this->pi ]['rating_average'] = $variation->get_average_rating();
					$this->productsList[ $this->pi ]['tags']           = wp_strip_all_tags(wc_get_product_tag_list($id,','));
					$this->productsList[ $this->pi ]['item_group_id']  = $variation->get_parent_id();
					$this->productsList[ $this->pi ]['sku']            = $variation->get_sku();
					$this->productsList[ $this->pi ]['parent_sku']     = $parent->get_sku();
					$this->productsList[ $this->pi ]['availability']   = $this->availability( $id );
					$this->productsList[ $this->pi ]['quantity']         = $variation->get_stock_quantity();
					$this->productsList[ $this->pi ]['sale_price_sdate'] = $variation->get_date_on_sale_from();
					$this->productsList[ $this->pi ]['sale_price_edate'] = $variation->get_date_on_sale_to();
					$this->productsList[ $this->pi ]['price']            = $variation->get_regular_price();
                    $this->productsList[ $this->pi ]['current_price']    = $variation->get_price();
                    $this->productsList[ $this->pi ]['price_with_tax']   = $this->getPriceWithTax($variation);
					$this->productsList[ $this->pi ]['sale_price']       = $variation->get_sale_price();
					$this->productsList[ $this->pi ]['weight']           = $variation->get_weight();
					$this->productsList[ $this->pi ]['width']            = $variation->get_width();
					$this->productsList[ $this->pi ]['height']           = $variation->get_height();
					$this->productsList[ $this->pi ]['length']           = $variation->get_length();
					$this->productsList[ $this->pi ]['shipping_class']   = $variation->get_shipping_class();


					# Sale price effective date
					$from = $this->sale_price_effective_date( $id, '_sale_price_dates_from' );
					$to   = $this->sale_price_effective_date( $id, '_sale_price_dates_to' );
					if ( ! empty( $from ) && ! empty( $to ) ) {
						$from                                                  = gmdate( "c", strtotime( $from ) );
						$to                                                    = gmdate( "c", strtotime( $to ) );
						$this->productsList[ $this->pi ]['sale_price_effective_date'] = $from . '/' . $to;
					} else {
						$this->productsList[ $this->pi ]['sale_price_effective_date'] = '';
					}

					# Get all Attributes and their values
					$data = $variation->get_data();
					$attributes = $data['attributes'];
					if ( ! empty($attributes) ) {
						foreach ( $attributes as $aKey => $attr ) {
							$this->productsList[ $this->pi ][ $aKey ] = $variation->get_attribute($aKey);
						}
					}


//					# Get all Product Post Meta and Their Values
//					$metas=get_post_meta($id);
//					if(!empty($metas)){
//						foreach ($metas as $mKey=>$meta){
//							if($mKey!="_product_attributes"){
//								$metaValue=get_post_meta($id,$mKey,true);
//								$this->productsList[ $this->pi ]["wf_cattr_".$mKey]=(!empty($metaValue))?$metaValue:'';
//							}
//						}
//					}
//
//					# Get all Product Taxonomies and Their values
//					$taxonomies=get_post_taxonomies($id);
//					if(!empty($taxonomies)){
//						foreach ($taxonomies as $tKey=>$taxonomy){
//							$this->productsList[ $this->pi ]["wf_taxo_".$taxonomy]=wp_strip_all_tags(get_the_term_list($id,$taxonomy,'',',',''));
//						}
//					}
					$this->pi++;
				}
			}
		}catch ( Exception $e ) {

		}


	}


	/**
	 * Get WooCommerce Product
	 * @param array $feedRule
	 * @return array
	 */
	public function woo_feed_get_visible_product( $feedRule = array() ) {
		wp_cache_delete( 'wf_check_duplicate', 'options' );
		$this->feedRule = $feedRule;
		$limit  = ! empty( $feedRule['Limit'] ) && is_numeric( $feedRule['Limit'] ) ? absint( $feedRule['Limit'] ) : '2000';
		$offset = ! empty( $feedRule['Offset'] ) && is_numeric( $feedRule['Offset'] ) ? absint( $feedRule['Offset'] ) : '0';
		# WC 3.1+ Compatibility

		if ( woo_feed_wc_version_check(3.1 ) ) {

			return $this->getWC3Products( $limit, $offset);

		} else {
			try {

				if ( ! empty( $feedRule ) ) {
					$this->feedRule = $feedRule;
				}


				if ( '0' == $offset ) {
					delete_option( "wf_check_duplicate" );
				}
				$getIDs = get_option( "wf_check_duplicate" );
				$arg    = array(
					'post_type'              => array( 'product', 'product_variation' ),
					'post_status'            => 'publish',
					'posts_per_page'         => $limit,
					'orderby'                => 'date',
					'order'                  => 'desc',
					'fields'                 => 'ids',
					'offset'                 => $offset,
					'cache_results'          => false,
					'update_post_term_cache' => false,
					'update_post_meta_cache' => false,
				);


				# Query Database for products
				$loop = new WP_Query( $arg );

				$i = 0;

				while ( $loop->have_posts() ) : $loop->the_post();

					$this->childID  = get_the_ID();
					$this->parentID = ( wp_get_post_parent_id( $this->childID ) ) ? wp_get_post_parent_id( $this->childID ) : $this->childID;

					global $product;
					if ( ! is_object( $product ) || ! $product->is_visible() ) {
						continue;
					}

					$type1 = '';
					if ( is_object( $product ) && $product->is_type( 'simple' ) ) {
						# No variations to product
						$type1 = "simple";
					} elseif ( is_object( $product ) && $product->is_type( 'variable' ) ) {
						# Product has variations
						$type1 = "variable";
					} elseif ( is_object( $product ) && $product->is_type( 'grouped' ) ) {
						$type1 = "grouped";
					} elseif ( is_object( $product ) && $product->is_type( 'external' ) ) {
						$type1 = "external";
					} elseif ( is_object( $product ) && $product->is_downloadable() ) {
						$type1 = "downloadable";
					} elseif ( is_object( $product ) && $product->is_virtual() ) {
						$type1 = "virtual";
					}


					$post = get_post( $this->parentID );

					if ( ! is_object( $post ) ) {
						continue;
					}

					if ( 'trash' == $post->post_status ) {
						continue;
					}


					if ( 'product_variation' == get_post_type() && 'facebook' !== $this->feedRule['provider'] ) {
						if ( 0 != $this->parentID ) {

							$status = get_post( $this->childID );
							if ( ! $status || ! is_object( $status ) ) {
								continue;
							}

							if ( 'trash' == $status->post_status ) {
								continue;
							}

							$parentStatus = get_post( $this->parentID );
							if ( $parentStatus && is_object( $parentStatus ) && 'publish' != $parentStatus->post_status ) {
								continue;
							}

							# Check Valid URL
							$mainImage = wp_get_attachment_url( $product->get_image_id() );
							$link      = $product->get_permalink();

							if ( 'custom' != $this->feedRule['provider'] ) {
								if ( 'http' !== substr( trim( $link ), 0, 4 ) && 'http' !== substr( trim( $mainImage ), 0, 4 ) ) {
									continue;
								}
							}

							if ( $getIDs ) {
								if ( in_array( $this->childID, $getIDs ) ) {
									continue;
								} else {
									array_push( $this->idExist, $this->childID );
								}
							} else {
								array_push( $this->idExist, $this->childID );
							}


							$this->productsList[ $i ]['id']             = $this->childID;
							$this->productsList[ $i ]['variation_type'] = "child";
							$this->productsList[ $i ]['item_group_id']  = $this->parentID;
							$this->productsList[ $i ]['sku']            = $this->getAttributeValue( $this->childID, "_sku" );
							$this->productsList[ $i ]['parent_sku']     = $this->getAttributeValue( $this->parentID, "_sku" );
							$this->productsList[ $i ]['title']          = $post->post_title;
							$this->productsList[ $i ]['description']    = $post->post_content;

							# Short Description to variable description
							$vDesc = $this->getAttributeValue( $this->childID, "_variation_description" );
							if ( ! empty( $vDesc ) ) {
								$this->productsList[ $i ]['short_description'] = $vDesc;
							} else {
								$this->productsList[ $i ]['short_description'] = $post->post_excerpt;
							}

							$this->productsList[ $i ]['product_type'] = $this->get_product_term_list( $post->ID, 'product_cat', '', ">" );
							$this->productsList[ $i ]['link']         = $link;
							$this->productsList[ $i ]['ex_link']      = '';
							$this->productsList[ $i ]['image']        = $this->get_formatted_url( $mainImage );

							# Featured Image
							if ( has_post_thumbnail( $post->ID ) ) :
								$image                                     = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' );
								$this->productsList[ $i ]['feature_image'] = $this->get_formatted_url( $image[0] );
							else :
								$this->productsList[ $i ]['feature_image'] = $this->get_formatted_url( $mainImage );
							endif;

							# Additional Images
							$imageLinks = array();
							$images     = $this->additionalImages( $this->childID );
							if ( $images && is_array( $images ) ) {
								$mKey = 1;
								foreach ( $images as $key => $value ) {
									if ( $value != $this->productsList[ $i ]['image'] ) {
										$imgLink                                 = $this->get_formatted_url( $value );
										$this->productsList[ $i ][ "image_$mKey" ] = $imgLink;
										if ( ! empty( $imgLink ) ) {
											array_push( $imageLinks, $imgLink );
										}
									}
									$mKey ++;
								}
							}
							$this->productsList[ $i ]['images']         = implode( ',', $imageLinks );
							$this->productsList[ $i ]['condition']      = 'new';
							$this->productsList[ $i ]['type']           = $product->get_type();
							$this->productsList[ $i ]['visibility']     = $this->getAttributeValue( $this->childID, "_visibility" );
							$this->productsList[ $i ]['rating_total']   = $product->get_rating_count();
							$this->productsList[ $i ]['rating_average'] = $product->get_average_rating();
							$this->productsList[ $i ]['tags']           = $this->get_product_term_list( $post->ID, 'product_tag' );
							$this->productsList[ $i ]['shipping']       = $product->get_shipping_class();

							$this->productsList[ $i ]['availability']     = $this->availability( $this->childID );
							$this->productsList[ $i ]['quantity']         = $this->get_quantity( $this->childID, "_stock" );
							$this->productsList[ $i ]['sale_price_sdate'] = $this->get_date( $this->childID, "_sale_price_dates_from" );
							$this->productsList[ $i ]['sale_price_edate'] = $this->get_date( $this->childID, "_sale_price_dates_to" );
							$this->productsList[ $i ]['price']            = ( $product->get_regular_price() ) ? $product->get_regular_price() : $product->get_price();
							$this->productsList[ $i ]['sale_price']       = ( $product->get_sale_price() ) ? $product->get_sale_price() : '';
							$this->productsList[ $i ]['weight']           = ( $product->get_weight() ) ? $product->get_weight() : '';
							$this->productsList[ $i ]['width']            = ( $product->get_width() ) ? $product->get_width() : '';
							$this->productsList[ $i ]['height']           = ( $product->get_height() ) ? $product->get_height() : '';
							$this->productsList[ $i ]['length']           = ( $product->get_length() ) ? $product->get_length() : '';

							# Sale price effective date
							$from = $this->sale_price_effective_date( $this->childID, '_sale_price_dates_from' );
							$to   = $this->sale_price_effective_date( $this->childID, '_sale_price_dates_to' );
							if ( ! empty( $from ) && ! empty( $to ) ) {
								$from                                                  = gmdate( "c", strtotime( $from ) );
								$to                                                    = gmdate( "c", strtotime( $to ) );
								$this->productsList[ $i ]['sale_price_effective_date'] = $from . '/' . $to;
							} else {
								$this->productsList[ $i ]['sale_price_effective_date'] = '';
							}                       
}
					} elseif ( get_post_type() == 'product' ) {
						if ( 'simple' == $type1 ) {

							$mainImage = wp_get_attachment_url( $product->get_image_id() );
							$link      = get_permalink( $post->ID );

							if ( 'custom' != $this->feedRule['provider'] ) {
								if ( 'http' !== substr( trim( $link ), 0, 4 ) && 'http' !== substr( trim( $mainImage ), 0, 4 ) ) {
									continue;
								}
							}

							if ( $getIDs ) {
								if ( in_array( $post->ID, $getIDs ) ) {
									continue;
								} else {
									array_push( $this->idExist, $post->ID );
								}
							} else {
								array_push( $this->idExist, $post->ID );
							}

							$this->productsList[ $i ]['id']             = $post->ID;
							$this->productsList[ $i ]['variation_type'] = "simple";
							$this->productsList[ $i ]['title']          = $post->post_title;
							$this->productsList[ $i ]['description']    = $post->post_content;

							$this->productsList[ $i ]['short_description'] = $post->post_excerpt;
							$this->productsList[ $i ]['product_type']      = $this->get_product_term_list( $post->ID, 'product_cat', '', ">" );
							$this->productsList[ $i ]['link']              = $link;
							$this->productsList[ $i ]['ex_link']           = '';
							$this->productsList[ $i ]['image']             = $this->get_formatted_url( $mainImage );

							# Featured Image
							if ( has_post_thumbnail( $post->ID ) ) :
								$image                                     = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' );
								$this->productsList[ $i ]['feature_image'] = $this->get_formatted_url( $image[0] );
							else :
								$this->productsList[ $i ]['feature_image'] = $this->get_formatted_url( $mainImage );
							endif;

							# Additional Images
							$imageLinks = array();
							$images     = $this->additionalImages( $post->ID );
							if ( $images && is_array( $images ) ) {
								$mKey = 1;
								foreach ( $images as $key => $value ) {
									if ( $value != $this->productsList[ $i ]['image'] ) {
										$imgLink                                 = $this->get_formatted_url( $value );
										$this->productsList[ $i ][ "image_$mKey" ] = $imgLink;
										if ( ! empty( $imgLink ) ) {
											array_push( $imageLinks, $imgLink );
										}
									}
									$mKey ++;
								}
							}
							$this->productsList[ $i ]['images'] = implode( ',', $imageLinks );

							$this->productsList[ $i ]['condition']      = 'new';
							$this->productsList[ $i ]['type']           = $product->get_type();
							$this->productsList[ $i ]['visibility']     = $this->getAttributeValue( $post->ID, "_visibility" );
							$this->productsList[ $i ]['rating_total']   = $product->get_rating_count();
							$this->productsList[ $i ]['rating_average'] = $product->get_average_rating();
							$this->productsList[ $i ]['tags']           = $this->get_product_term_list( $post->ID, 'product_tag' );

							$this->productsList[ $i ]['item_group_id'] = $post->ID;
							$this->productsList[ $i ]['sku']           = $this->getAttributeValue( $post->ID, "_sku" );

							$this->productsList[ $i ]['availability']     = $this->availability( $post->ID );
							$this->productsList[ $i ]['quantity']         = $this->get_quantity( $post->ID, "_stock" );
							$this->productsList[ $i ]['sale_price_sdate'] = $this->get_date( $post->ID, "_sale_price_dates_from" );
							$this->productsList[ $i ]['sale_price_edate'] = $this->get_date( $post->ID, "_sale_price_dates_to" );
							$this->productsList[ $i ]['price']            = ( $product->get_regular_price() ) ? $product->get_regular_price() : $product->get_price();
							$this->productsList[ $i ]['sale_price']       = ( $product->get_sale_price() ) ? $product->get_sale_price() : '';
							$this->productsList[ $i ]['weight']           = ( $product->get_weight() ) ? $product->get_weight() : '';
							$this->productsList[ $i ]['width']            = ( $product->get_width() ) ? $product->get_width() : '';
							$this->productsList[ $i ]['height']           = ( $product->get_height() ) ? $product->get_height() : '';
							$this->productsList[ $i ]['length']           = ( $product->get_length() ) ? $product->get_length() : '';

							# Sale price effective date
							$from = $this->sale_price_effective_date( $post->ID, '_sale_price_dates_from' );
							$to   = $this->sale_price_effective_date( $post->ID, '_sale_price_dates_to' );
							if ( ! empty( $from ) && ! empty( $to ) ) {
								$from                                                  = gmdate( "c", strtotime( $from ) );
								$to                                                    = gmdate( "c", strtotime( $to ) );
								$this->productsList[ $i ]['sale_price_effective_date'] = $from . '/' . $to;
							} else {
								$this->productsList[ $i ]['sale_price_effective_date'] = '';
							}                       
}
						elseif ( 'external' == $type1 ) {

							$mainImage = wp_get_attachment_url( $product->get_image_id() );

							$getLink = new WC_Product_External( $post->ID );
							$EX_link = $getLink->get_product_url();
							$link    = get_permalink( $post->ID );
							if ( 'custom' != $this->feedRule['provider'] ) {
								if ( 'http' !== substr( trim( $link ), 0, 4 ) && 'http' !== substr( trim( $mainImage ), 0, 4 ) ) {
									continue;
								}
							}

							$this->productsList[ $i ]['id']             = $post->ID;
							$this->productsList[ $i ]['variation_type'] = "external";
							$this->productsList[ $i ]['title']          = $post->post_title;
							$this->productsList[ $i ]['description']    = do_shortcode( $post->post_content );

							$this->productsList[ $i ]['short_description'] = $post->post_excerpt;
							$this->productsList[ $i ]['product_type']      = $this->get_product_term_list( $post->ID, 'product_cat', '', ">" );
							$this->productsList[ $i ]['link']              = $link;
							$this->productsList[ $i ]['ex_link']           = $EX_link;
							$this->productsList[ $i ]['image']             = $this->get_formatted_url( $mainImage );

							# Featured Image
							if ( has_post_thumbnail( $post->ID ) ) :
								$image                                     = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' );
								$this->productsList[ $i ]['feature_image'] = $this->get_formatted_url( $image[0] );
							else :
								$this->productsList[ $i ]['feature_image'] = $this->get_formatted_url( $mainImage );
							endif;

							# Additional Images
							$imageLinks = array();
							$images     = $this->additionalImages( $post->ID );
							if ( $images && is_array( $images ) ) {
								$mKey = 1;
								foreach ( $images as $key => $value ) {
									if ( $value != $this->productsList[ $i ]['image'] ) {
										$imgLink                                 = $this->get_formatted_url( $value );
										$this->productsList[ $i ][ "image_$mKey" ] = $imgLink;
										if ( ! empty( $imgLink ) ) {
											array_push( $imageLinks, $imgLink );
										}
									}
									$mKey ++;
								}
							}
							$this->productsList[ $i ]['images'] = implode( ',', $imageLinks );

							$this->productsList[ $i ]['condition']      = 'new';
							$this->productsList[ $i ]['type']           = $product->get_type();
							$this->productsList[ $i ]['visibility']     = $this->getAttributeValue( $post->ID, "_visibility" );
							$this->productsList[ $i ]['rating_total']   = $product->get_rating_count();
							$this->productsList[ $i ]['rating_average'] = $product->get_average_rating();
							$this->productsList[ $i ]['tags']           = $this->get_product_term_list( $post->ID, 'product_tag' );

							$this->productsList[ $i ]['item_group_id'] = $post->ID;
							$this->productsList[ $i ]['sku']           = $this->getAttributeValue( $post->ID, "_sku" );

							$this->productsList[ $i ]['availability'] = $this->availability( $post->ID );

							$this->productsList[ $i ]['quantity']         = $this->get_quantity( $post->ID, "_stock" );
							$this->productsList[ $i ]['sale_price_sdate'] = $this->get_date( $post->ID, "_sale_price_dates_from" );
							$this->productsList[ $i ]['sale_price_edate'] = $this->get_date( $post->ID, "_sale_price_dates_to" );
							$this->productsList[ $i ]['price']            = ( $product->get_regular_price() ) ? $product->get_regular_price() : $product->get_price();
							$this->productsList[ $i ]['sale_price']       = ( $product->get_sale_price() ) ? $product->get_sale_price() : '';
							$this->productsList[ $i ]['weight']           = ( $product->get_weight() ) ? $product->get_weight() : '';
							$this->productsList[ $i ]['width']            = ( $product->get_width() ) ? $product->get_width() : '';
							$this->productsList[ $i ]['height']           = ( $product->get_height() ) ? $product->get_height() : '';
							$this->productsList[ $i ]['length']           = ( $product->get_length() ) ? $product->get_length() : '';

							# Sale price effective date
							$from = $this->sale_price_effective_date( $post->ID, '_sale_price_dates_from' );
							$to   = $this->sale_price_effective_date( $post->ID, '_sale_price_dates_to' );
							if ( ! empty( $from ) && ! empty( $to ) ) {
								$from                                                  = gmdate( "c", strtotime( $from ) );
								$to                                                    = gmdate( "c", strtotime( $to ) );
								$this->productsList[ $i ]['sale_price_effective_date'] = $from . '/' . $to;
							} else {
								$this->productsList[ $i ]['sale_price_effective_date'] = '';
							}                       
}
						elseif ( 'grouped' == $type1 ) {

							$grouped        = new WC_Product_Grouped( $post->ID );
							$children       = $grouped->get_children();
							$this->parentID = $post->ID;
							if ( $children ) {
								foreach ( $children as $cKey => $child ) {

									$product       = new WC_Product( $child );
									$this->childID = $child;
									$post          = get_post( $this->childID );

									if ( 'trash' == $post->post_status ) {
										continue;
									}

									if ( ! empty( $this->ids_in ) && ! in_array( $post->ID, $this->ids_in ) ) {
										continue;
									}

									if ( ! empty( $this->ids_not_in ) && in_array( $post->ID, $this->ids_in ) ) {
										continue;
									}

									if ( ! $product->is_visible() ) {
										continue;
									}

									$i ++;

									$mainImage = wp_get_attachment_url( $product->get_image_id() );
									$link      = get_permalink( $post->ID );
									if ( 'custom' != $this->feedRule['provider'] ) {
										if ( 'http' !== substr( trim( $link ), 0, 4 ) && 'http' !== substr( trim( $mainImage ), 0, 4 ) ) {
											continue;
										}
									}

									$this->productsList[ $i ]['id']             = $post->ID;
									$this->productsList[ $i ]['variation_type'] = "grouped";
									$this->productsList[ $i ]['title']          = $post->post_title;
									$this->productsList[ $i ]['description']    = do_shortcode( $post->post_content );

									$this->productsList[ $i ]['short_description'] = $post->post_excerpt;
									$this->productsList[ $i ]['product_type']      = $this->get_product_term_list( $post->ID, 'product_cat', '', ">" );
									$this->productsList[ $i ]['link']              = $link;
									$this->productsList[ $i ]['ex_link']           = '';
									$this->productsList[ $i ]['image']             = $this->get_formatted_url( $mainImage );

									# Featured Image
									if ( has_post_thumbnail( $post->ID ) ) :
										$image                                     = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' );
										$this->productsList[ $i ]['feature_image'] = $this->get_formatted_url( $image[0] );
									else :
										$this->productsList[ $i ]['feature_image'] = $this->get_formatted_url( $mainImage );
									endif;

									# Additional Images
									$imageLinks = array();
									$images     = $this->additionalImages( $this->childID );
									if ( $images and is_array( $images ) ) {
										$mKey = 1;
										foreach ( $images as $key => $value ) {
											if ( $value != $this->productsList[ $i ]['image'] ) {
												$imgLink                                 = $this->get_formatted_url( $value );
												$this->productsList[ $i ][ "image_$mKey" ] = $imgLink;
												if ( ! empty( $imgLink ) ) {
													array_push( $imageLinks, $imgLink );
												}
											}
											$mKey ++;
										}
									}
									$this->productsList[ $i ]['images']         = implode( ',', $imageLinks );
									$this->productsList[ $i ]['condition']      = 'new';
									$this->productsList[ $i ]['type']           = $product->get_type();
									$this->productsList[ $i ]['visibility']     = $this->getAttributeValue( $post->ID, "_visibility" );
									$this->productsList[ $i ]['rating_total']   = $product->get_rating_count();
									$this->productsList[ $i ]['rating_average'] = $product->get_average_rating();
									$this->productsList[ $i ]['tags']           = $this->get_product_term_list( $post->ID, 'product_tag' );

									$this->productsList[ $i ]['item_group_id'] = $this->parentID;
									$this->productsList[ $i ]['sku']           = $this->getAttributeValue( $post->ID, "_sku" );

									$this->productsList[ $i ]['availability'] = $this->availability( $post->ID );

									$this->productsList[ $i ]['quantity']         = $this->get_quantity( $post->ID, "_stock" );
									$this->productsList[ $i ]['sale_price_sdate'] = $this->get_date( $post->ID, "_sale_price_dates_from" );
									$this->productsList[ $i ]['sale_price_edate'] = $this->get_date( $post->ID, "_sale_price_dates_to" );
									$this->productsList[ $i ]['price']            = ( $product->get_regular_price() ) ? $product->get_regular_price() : $product->get_price();
									$this->productsList[ $i ]['sale_price']       = ( $product->get_sale_price() ) ? $product->get_sale_price() : '';
									$this->productsList[ $i ]['weight']           = ( $product->get_weight() ) ? $product->get_weight() : '';
									$this->productsList[ $i ]['width']            = ( $product->get_width() ) ? $product->get_width() : '';
									$this->productsList[ $i ]['height']           = ( $product->get_height() ) ? $product->get_height() : '';
									$this->productsList[ $i ]['length']           = ( $product->get_length() ) ? $product->get_length() : '';

									# Sale price effective date
									$from = $this->sale_price_effective_date( $post->ID, '_sale_price_dates_from' );
									$to   = $this->sale_price_effective_date( $post->ID, '_sale_price_dates_to' );
									if ( ! empty( $from ) && ! empty( $to ) ) {
										$from                                                  = gmdate( "c", strtotime( $from ) );
										$to                                                    = gmdate( "c", strtotime( $to ) );
										$this->productsList[ $i ]['sale_price_effective_date'] = $from . '/' . $to;
									} else {
										$this->productsList[ $i ]['sale_price_effective_date'] = '';
									}
								}
							}
						}
						elseif ( 'variable' == $type1 && $product->has_child() ) {

							# Check Valid URL
							$mainImage = wp_get_attachment_url( $product->get_image_id() );
							$link      = get_permalink( $post->ID );

							if ( 'custom' != $this->feedRule['provider'] ) {
								if ( 'http' !== substr( trim( $link ), 0, 4 ) && 'http' !== substr( trim( $mainImage ), 0, 4 ) ) {
									continue;
								}
							}


							$this->productsList[ $i ]['id']             = $post->ID;
							$this->productsList[ $i ]['variation_type'] = "parent";
							$this->productsList[ $i ]['title']          = $post->post_title;
							$this->productsList[ $i ]['description']    = $post->post_content;

							$this->productsList[ $i ]['short_description'] = $post->post_excerpt;
							$this->productsList[ $i ]['product_type']      = $this->get_product_term_list( $post->ID, 'product_cat', '', ">" );
							$this->productsList[ $i ]['link']              = $link;
							$this->productsList[ $i ]['ex_link']           = '';
							$this->productsList[ $i ]['image']             = $this->get_formatted_url( $mainImage );

							# Featured Image
							if ( has_post_thumbnail( $post->ID ) ) :
								$image                                     = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' );
								$this->productsList[ $i ]['feature_image'] = $this->get_formatted_url( $image[0] );
							else :
								$this->productsList[ $i ]['feature_image'] = $this->get_formatted_url( $mainImage );
							endif;

							# Additional Images
							$imageLinks = array();
							$images     = $this->additionalImages( $post->ID );
							if ( $images and is_array( $images ) ) {
								$mKey = 1;
								foreach ( $images as $key => $value ) {
									if ( $value != $this->productsList[ $i ]['image'] ) {
										$imgLink                                 = $this->get_formatted_url( $value );
										$this->productsList[ $i ][ "image_$mKey" ] = $imgLink;
										if ( ! empty( $imgLink ) ) {
											array_push( $imageLinks, $imgLink );
										}
									}
									$mKey ++;
								}
							}
							$this->productsList[ $i ]['images'] = implode( ',', $imageLinks );

							$this->productsList[ $i ]['condition']      = 'new';
							$this->productsList[ $i ]['type']           = $product->get_type();
							$this->productsList[ $i ]['visibility']     = $this->getAttributeValue( $post->ID, "_visibility" );
							$this->productsList[ $i ]['rating_total']   = $product->get_rating_count();
							$this->productsList[ $i ]['rating_average'] = $product->get_average_rating();
							$this->productsList[ $i ]['tags']           = $this->get_product_term_list( $post->ID, 'product_tag' );

							$this->productsList[ $i ]['item_group_id'] = $post->ID;
							$this->productsList[ $i ]['sku']           = $this->getAttributeValue( $post->ID, "_sku" );

							$this->productsList[ $i ]['availability']     = $this->availability( $post->ID );
							$this->productsList[ $i ]['quantity']         = $this->get_quantity( $post->ID, "_stock" );
							$this->productsList[ $i ]['sale_price_sdate'] = $this->get_date( $post->ID, "_sale_price_dates_from" );
							$this->productsList[ $i ]['sale_price_edate'] = $this->get_date( $post->ID, "_sale_price_dates_to" );

							$price = ( $product->get_price() ) ? $product->get_price() : false;

							$this->productsList[ $i ]['price']      = ( $product->get_regular_price() ) ? $product->get_regular_price() : $price;
							$this->productsList[ $i ]['sale_price'] = ( $product->get_sale_price() ) ? $product->get_sale_price() : '';
							$this->productsList[ $i ]['weight']     = ( $product->get_weight() ) ? $product->get_weight() : '';
							$this->productsList[ $i ]['width']      = ( $product->get_width() ) ? $product->get_width() : '';
							$this->productsList[ $i ]['height']     = ( $product->get_height() ) ? $product->get_height() : '';
							$this->productsList[ $i ]['length']     = ( $product->get_length() ) ? $product->get_length() : '';

							# Sale price effective date
							$from = $this->sale_price_effective_date( $post->ID, '_sale_price_dates_from' );
							$to   = $this->sale_price_effective_date( $post->ID, '_sale_price_dates_to' );
							if ( ! empty( $from ) && ! empty( $to ) ) {
								$from                                                  = gmdate( "c", strtotime( $from ) );
								$to                                                    = gmdate( "c", strtotime( $to ) );
								$this->productsList[ $i ]['sale_price_effective_date'] = $from . '/' . $to;
							} else {
								$this->productsList[ $i ]['sale_price_effective_date'] = '';
							}
						}
					}
					$i ++;
				endwhile;
				wp_reset_postdata();

				if ( $getIDs ) {
					$mergedIds = array_merge( $getIDs, $this->idExist );
					update_option( "wf_check_duplicate", $mergedIds );
				} else {
					update_option( "wf_check_duplicate", $this->idExist );
				}

				return $this->productsList;
			} catch ( Exception $e ) {
				return $this->productsList;
			}
		}
	}

	/**
	 * Remove ShortCodes from contents
	 * @param $content
	 *
	 * @return mixed|string
	 */
	public function remove_short_codes( $content ) {
		if ( empty( $content ) ) {
			return '';
		}
        $content = do_shortcode( $content );
        $content = $this->stripInvalidXml( $content );
		# Remove DIVI Builder Short Codes
		if ( class_exists('ET_Builder_Module') || defined('ET_BUILDER_PLUGIN_VERSION') ) {
			$content = preg_replace('/\[\/?et_pb.*?\]/', '', $content);
		}
        return strip_shortcodes($content);

	}

    /**
     * Remove Invalid Character from XML
     *
     * @param $value
     *
     * @return string
     */
    public function stripInvalidXml( $value ) {
        return woo_feed_stripInvalidXml( $value );
    }

	/**
	 * Get formatted image url
	 *
	 * @param $url
	 * @return bool|string
	 */
	public function get_formatted_url( $url = '' ) {
		if ( ! empty($url) ) {
			if ( 'http' === substr(trim($url), 0, 4) || substr(trim($url), 0, 3) === 'ftp' || substr(trim($url), 0, 4) === 'sftp' ) {
				return rtrim($url, '/');
			} else {
				$base = get_site_url();
				$url = $base . $url;
				return rtrim($url, '/');
			}
		}
		return $url;
	}


	/**
	 * Get formatted product date
	 *
	 * @param $id
	 * @param $name
	 * @return bool|string
	 */
	public function get_date( $id, $name ) {
		$date = $this->getAttributeValue($id, $name);
		if ( $date ) {
			return gmdate('Y-m-d', $date);
		}
		return false;
	}

	/**
	 * Get formatted product quantity
	 *
	 * @param $id
	 * @param $name
	 * @return bool|mixed
	 */
	public function get_quantity( $id, $name ) {
		$qty = $this->getAttributeValue( $id, $name );
		if ( $qty ) {
			return $qty + 0;
		}
		
		return '0';
	}

	/**
	 * Retrieve a post's terms as a list with specified format.
	 *
	 * @since 2.5.0
	 *
	 * @param int $id Post ID.
	 * @param string $taxonomy Taxonomy name.
	 * @param string $before Optional. Before list.
	 * @param string $sep Optional. Separate items using this.
	 * @param string $after Optional. After list.
	 *
	 * @return string|false|WP_Error A list of terms on success, false if there are no terms, WP_Error on failure.
	 */
	function get_product_term_list( $id, $taxonomy, $before = '', $sep = ',', $after = '' ) {
		$terms = get_the_terms($id, $taxonomy);

		if ( is_wp_error($terms) ) {
			return $terms;
		}

		if ( empty($terms) ) {
			return false;
		}

		$links = array();

		foreach ( $terms as $term ) {
			$links[] = $term->name;
		}
		ksort($links);
		return $before . join($sep, $links) . $after;
	}

	/** Return additional image URLs
	 *
	 * @param int $Id
	 *
	 * @return bool|array
	 */

	public function additionalImages( $Id ) {
		$ids    = $this->getAttributeValue( $Id, '_product_image_gallery' );
		$imgIds = ! empty( $ids ) ? explode( ',', $ids ) : '';
		
		$images = array();
		if ( ! empty( $imgIds ) ) {
			foreach ( $imgIds as $key => $value ) {
				if ( $key < 10 ) {
					$images[ $key ] = wp_get_attachment_url( $value );
				}
			}
			
			return $images;
		}
		
		return false;
	}

	/**
	 * Give space to availability text
	 *
	 * @param integer $id
	 *
	 * @return string
	 */
	public function availability( $id ) {
		$status = $this->getProductMeta( $id, '_stock_status' );
		if ( $status ) {
			if ( 'instock' == $status ) {
				return 'in stock';
			} elseif ( 'outofstock' == $status ) {
				return 'out of stock';
			}
		}
		
		return 'in stock';
	}

	/**
	 * Ger Product Attribute
	 *
	 * @since 2.2.3
	 * @param $id
	 * @param $attr
	 *
	 * @return string
	 */
	public function getProductAttribute( $id,$attr ) {
		
		$attr = str_replace( 'wf_attr_', '', $attr );
		
		if ( woo_feed_wc_version_check( 3.1 ) ) {
			# Get Product
			$product = wc_get_product( $id );
			
			if ( ! is_object( $product ) ) {
				return '';
			}
			
			if ( woo_feed_wc_version_check( 3.6 ) ) {
				$attr = str_replace( 'pa_', '', $attr );
			}
			
			$value = $product->get_attribute( $attr );
			
			if ( ! empty( $value ) ) {
				$value = trim( $value );
			}

			return $value;
		} else {
			return implode( ',', wc_get_product_terms( $id, $attr, array( 'fields' => 'names' ) ) );
		}
	}


	/**
	 * Get Meta
	 *
	 * @since 2.2.3
	 * @param $id
	 * @param $meta
	 *
	 * @return mixed|string
	 */

	public function getProductMeta( $id,$meta ) {
		
		$meta = str_replace( 'wf_cattr_', '', $meta );
		
		if ( strpos( $meta, 'attribute_pa' ) !== false ) {
			return $this->getProductAttribute( $id, str_replace( 'attribute_', '', $meta ) );
		} else {
			return get_post_meta( $id, $meta, true );
		}
	}

	/**
	 * Get Product Attribute Value
	 *
	 * @deprecated 2.2.5
	 * @param $id
	 * @param $name
	 *
	 * @return mixed
	 */
	public function getAttributeValue( $id, $name ) {

		return $this->getProductMeta($id,$name);
//        if (strpos($name, 'attribute_pa') !== false) {
//            $taxonomy = str_replace("attribute_','",$name);
//            $meta = get_post_meta($id,$name, true);
//            $term = get_term_by('slug', $meta, $taxonomy);
//            return $term->name;
//        }else{
//            return get_post_meta($id, $name, true);
//        }

	}

	/**
	 * Get Sale price effective date for google
	 *
	 * @param $id
	 * @param $name
	 * @return string
	 */
	public function sale_price_effective_date( $id, $name ) {
		$date = $this->getAttributeValue( $id, $name );
		return ( $date ) ? date_i18n( 'Y-m-d', $date ) : '';
	}


	/**
	 * Get All Default WooCommerce Attributes
	 * @return bool|array
	 */
	public function getAllAttributes() {
		global $wpdb;
		$info = array();
		//Load the main attributes
		$globalAttributes = wc_get_attribute_taxonomy_labels();
		if ( count( $globalAttributes ) ) {
			foreach ( $globalAttributes as $key => $value ) {
				$info[ 'wf_attr_pa_' . $key ] = $value;
			}
		}
		
		return $info;
	}
	
	/**
	 * Local Attribute List to map product value with merchant attributes
	 *
	 * @param string $selected
	 *
	 * @return string
	 */
	public function attributeDropdown( $selected = '' ) {
		
		$attributeDropdown = wp_cache_get( 'woo_feed_dropdown_product_attributes' );

		if ( false === $attributeDropdown ) {
			$attributes = array(
				'id'                        => esc_attr__( 'Product Id', 'woo-feed' ),
				'title'                     => esc_attr__( 'Product Title', 'woo-feed' ),
				'description'               => esc_attr__( 'Product Description', 'woo-feed' ),
				'short_description'         => esc_attr__( 'Product Short Description', 'woo-feed' ),
				'product_type'              => esc_attr__( 'Product Local Category', 'woo-feed' ),
				'link'                      => esc_attr__( 'Product URL', 'woo-feed' ),
				'ex_link'                   => esc_attr__( 'External Product URL', 'woo-feed' ),
				'condition'                 => esc_attr__( 'Condition', 'woo-feed' ),
				'item_group_id'             => esc_attr__( 'Parent Id [Group Id]', 'woo-feed' ),
				'sku'                       => esc_attr__( 'SKU', 'woo-feed' ),
				'parent_sku'                => esc_attr__( 'Parent SKU', 'woo-feed' ),
				'availability'              => esc_attr__( 'Availability', 'woo-feed' ),
				'quantity'                  => esc_attr__( 'Quantity', 'woo-feed' ),
				'price'                     => esc_attr__( 'Regular Price', 'woo-feed' ),
				'current_price'             => esc_attr__( 'Price', 'woo-feed' ),
				'sale_price'                => esc_attr__( 'Sale Price', 'woo-feed' ),
				'price_with_tax'            => esc_attr__( 'Regular Price With Tax', 'woo-feed' ),
				'current_price_with_tax'    => esc_attr__( 'Price With Tax', 'woo-feed' ),
				'sale_price_with_tax'       => esc_attr__( 'Sale Price With Tax', 'woo-feed' ),
				'sale_price_sdate'          => esc_attr__( 'Sale Start Date', 'woo-feed' ),
				'sale_price_edate'          => esc_attr__( 'Sale End Date', 'woo-feed' ),
				'weight'                    => esc_attr__( 'Weight', 'woo-feed' ),
				'width'                     => esc_attr__( 'Width', 'woo-feed' ),
				'height'                    => esc_attr__( 'Height', 'woo-feed' ),
				'length'                    => esc_attr__( 'Length', 'woo-feed' ),
				'shipping_class'            => esc_attr__( 'Shipping Class', 'woo-feed' ),
				'type'                      => esc_attr__( 'Product Type', 'woo-feed' ),
				'variation_type'            => esc_attr__( 'Variation Type', 'woo-feed' ),
				'visibility'                => esc_attr__( 'Visibility', 'woo-feed' ),
				'rating_total'              => esc_attr__( 'Total Rating', 'woo-feed' ),
				'rating_average'            => esc_attr__( 'Average Rating', 'woo-feed' ),
				'tags'                      => esc_attr__( 'Tags', 'woo-feed' ),
				'sale_price_effective_date' => esc_attr__( 'Sale Price Effective Date', 'woo-feed' ),
				'is_bundle'                 => esc_attr__( 'Is Bundle', 'woo-feed' ),
				'author_name'               => esc_attr__( 'Author Name', 'woo-feed' ),
				'author_email'              => esc_attr__( 'Author Email', 'woo-feed' ),
				'date_created'              => esc_attr__( 'Date Created', 'woo-feed' ),
				'date_updated'              => esc_attr__( 'Date Updated', 'woo-feed' ),
			);
			$images     = array(
				'image'         => esc_attr__( 'Main Image', 'woo-feed' ),
				'feature_image' => esc_attr__( 'Featured Image', 'woo-feed' ),
				'images'        => esc_attr__( 'Images [Comma Separated]', 'woo-feed' ),
				'image_1'       => esc_attr__( 'Additional Image 1', 'woo-feed' ),
				'image_2'       => esc_attr__( 'Additional Image 2', 'woo-feed' ),
				'image_3'       => esc_attr__( 'Additional Image 3', 'woo-feed' ),
				'image_4'       => esc_attr__( 'Additional Image 4', 'woo-feed' ),
				'image_5'       => esc_attr__( 'Additional Image 5', 'woo-feed' ),
				'image_6'       => esc_attr__( 'Additional Image 6', 'woo-feed' ),
				'image_7'       => esc_attr__( 'Additional Image 7', 'woo-feed' ),
				'image_8'       => esc_attr__( 'Additional Image 8', 'woo-feed' ),
				'image_9'       => esc_attr__( 'Additional Image 9', 'woo-feed' ),
				'image_10'      => esc_attr__( 'Additional Image 10', 'woo-feed' ),
			);
			
			if ( class_exists( 'All_in_One_SEO_Pack' ) ) {
				$attributes = array_merge( $attributes,
					[
						'_aioseop_title'       => esc_attr__( 'Title [All in One SEO]', 'woo-feed' ),
						'_aioseop_description' => esc_attr__( 'Description [All in One SEO]', 'woo-feed' ),
					] );
			}
			if ( class_exists( 'WPSEO_Frontend' ) ) {
				$attributes = array_merge( $attributes,
					[
						'yoast_wpseo_title'    => esc_attr__( 'Title [Yoast SEO]', 'woo-feed' ),
						'yoast_wpseo_metadesc' => esc_attr__( 'Description [Yoast SEO]', 'woo-feed' ),
					] );
			}
			
			# Primary Attributes
			$attributeDropdown = '<option></option>';
			if ( is_array( $attributes ) && ! empty( $attributes ) ) {
				$attributeDropdown .= sprintf( '<optgroup label="%s">', esc_attr__( 'Primary Attributes', 'woo-feed' ) );
				foreach ( $attributes as $key => $value ) {
					$attributeDropdown .= sprintf( '<option value="%s">%s</option>', $key, $value );
				}
				$attributeDropdown .= '</optgroup>';
			}
			
			# Additional Images
			if ( is_array( $images ) && ! empty( $images ) ) {
				$attributeDropdown .= sprintf( '<optgroup label="%s">', esc_attr__( 'Image Attributes', 'woo-feed' ) );
				foreach ( $images as $key => $value ) {
					$attributeDropdown .= sprintf( '<option value="%s">%s</option>', $key, $value );
				}
				$attributeDropdown .= '</optgroup>';
			}
			
			# Get All WooCommerce Attributes
			$vAttributes = get_option( 'wpfw_vAttributes', array() );
			if ( is_array( $vAttributes ) && ! empty( $vAttributes ) ) {
				$attributeDropdown .= sprintf( '<optgroup label="%s">', esc_attr__( 'Select Attributes', 'woo-feed' ) );
				foreach ( $vAttributes as $key => $value ) {
					$attributeDropdown .= sprintf( '<option value="%s">%s</option>', $key, $value );
				}
				$attributeDropdown .= '</optgroup>';
			}
			
			# Get All Custom Attributes
			$customAttributes = get_option( 'wpfw_customAttributes', array() );
			if ( is_array( $customAttributes ) && ! empty( $customAttributes ) ) {
				$attributeDropdown .= sprintf( '<optgroup label="%s">', esc_attr__( 'Product Custom Attributes', 'woo-feed' ) );
				foreach ( $customAttributes as $key => $value ) {
					$attributeDropdown .= sprintf( '<option value="%s">%s</option>', $key, $value );
				}
				$attributeDropdown .= '</optgroup>';
			}
			$postMetas = get_option( 'wpfw_customMetaKeys', array() );
			if ( is_array( $postMetas ) && ! empty( $postMetas ) ) {
				$attributeDropdown .= sprintf( '<optgroup label="%s">', esc_attr__( 'Custom Fields/Post Meta', 'woo-feed' ) );
				foreach ( $postMetas as $key => $value ) {
					$attributeDropdown .= sprintf( '<option value="%s">%s</option>', $key, $value );
				}
				$attributeDropdown .= '</optgroup>';
			}
			
			# Get All Custom Taxonomies
			$customTaxonomy = get_option( 'wpfw_customTaxonomy', array() );
			if ( is_array( $customTaxonomy ) && ! empty( $customTaxonomy ) ) {
				$attributeDropdown .= sprintf( '<optgroup label="%s">', esc_attr__( 'Custom Taxonomies', 'woo-feed' ) );
				foreach ( $customTaxonomy as $key => $value ) {
					$attributeDropdown .= sprintf( '<option value="%s">%s</option>', $key, $value );
				}
				$attributeDropdown .= '</optgroup>';
			}
			
			wp_cache_add( 'woo_feed_dropdown_product_attributes', $attributeDropdown, '', WEEK_IN_SECONDS );
		}
		
		if ( $selected && strpos( $attributeDropdown, 'value="' . $selected . '"' ) !== false ) {
			$attributeDropdown = str_replace( 'value="' . $selected . '"', 'value="' . $selected . '"' . ' selected', $attributeDropdown );
		}
		
		return $attributeDropdown;
	}

	/**
	 * Load all WooCommerce attributes into an option
	 */
	public function load_attributes() {
		# Get All WooCommerce Attributes
		$vAttributes = $this->getAllAttributes();
		update_option('wpfw_vAttributes', $vAttributes);
	}

    /** Return product price with tax
     * @param $product
     * @return float|string
     */
    public function getPriceWithTax( $product ) {
        if ( woo_feed_wc_version_check(3.0) ) {
            return wc_get_price_including_tax($product,array( 'price' => $product->get_price() ));
        }else {
            return $product->get_price_including_tax();
        }
    }

    /** Format product created & updated date
     * @param $date
     * @return false|string
     */
    public function format_product_date( $date ) {
	    return gmdate( 'Y-m-d', strtotime( $date ) );
	}

    /**
     * Get Taxonomy
     *
     * @param $id
     * @param $taxonomy
     *
     * @return string
     */
    public function getProductTaxonomy( $id, $taxonomy ) {
        $taxonomy = str_replace('wf_taxo_', '',$taxonomy);
	    $Taxo     = get_the_term_list( $id, $taxonomy, '', ',', '' );
	
	    if ( ! empty( $Taxo ) ) {
		    return wp_strip_all_tags( $Taxo );
	    }

        return '';
    }
}