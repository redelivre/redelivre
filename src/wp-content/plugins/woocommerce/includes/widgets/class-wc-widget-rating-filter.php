<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rating Filter Widget and related functions.
 *
 *
 * @author   WooThemes
 * @category Widgets
 * @package  WooCommerce/Widgets
 * @version  2.6.0
 * @extends  WC_Widget
 */
class WC_Widget_Rating_Filter extends WC_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->widget_cssclass    = 'woocommerce widget_rating_filter';
		$this->widget_description = __( 'Display a list of star ratings to filter products in your store.', 'woocommerce' );
		$this->widget_id          = 'woocommerce_rating_filter';
		$this->widget_name        = __( 'Filter Products by Rating', 'woocommerce' );
		$this->settings           = array(
			'title'  => array(
				'type'  => 'text',
				'std'   => __( 'Average rating', 'woocommerce' ),
				'label' => __( 'Title', 'woocommerce' ),
			),
		);
		parent::__construct();
	}

	/**
	 * Count products after other filters have occurred by adjusting the main query.
	 * @param  int $rating
	 * @return int
	 */
	protected function get_filtered_product_count( $rating ) {
		global $wpdb;

		$tax_query  = WC_Query::get_main_tax_query();
		$meta_query = WC_Query::get_main_meta_query();

		// Unset current rating filter.
		foreach ( $tax_query as $key => $query ) {
			if ( ! empty( $query['rating_filter'] ) ) {
				unset( $tax_query[ $key ] );
				break;
			}
		}

		// Set new rating filter.
		$product_visibility_terms = wc_get_product_visibility_term_ids();
		$tax_query[]             = array(
			'taxonomy'      => 'product_visibility',
			'field'         => 'term_taxonomy_id',
			'terms'         => $product_visibility_terms[ 'rated-' . $rating ],
			'operator'      => 'IN',
			'rating_filter' => true,
		);

		$meta_query     = new WP_Meta_Query( $meta_query );
		$tax_query      = new WP_Tax_Query( $tax_query );
		$meta_query_sql = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
		$tax_query_sql  = $tax_query->get_sql( $wpdb->posts, 'ID' );

		$sql  = "SELECT COUNT( DISTINCT {$wpdb->posts}.ID ) FROM {$wpdb->posts} ";
		$sql .= $tax_query_sql['join'] . $meta_query_sql['join'];
		$sql .= " WHERE {$wpdb->posts}.post_type = 'product' AND {$wpdb->posts}.post_status = 'publish' ";
		$sql .= $tax_query_sql['where'] . $meta_query_sql['where'];

		if ( $search = WC_Query::get_main_search_query_sql() ) {
			$sql .= ' AND ' . $search;
		}

		return absint( $wpdb->get_var( $sql ) );
	}

	/**
	 * widget function.
	 *
	 * @see WP_Widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		if ( ! is_shop() && ! is_product_taxonomy() ) {
			return;
		}

		if ( ! wc()->query->get_main_query()->post_count ) {
			return;
		}

		ob_start();

		$found         = false;
		$rating_filter = isset( $_GET['rating_filter'] ) ? array_filter( array_map( 'absint', explode( ',', $_GET['rating_filter'] ) ) ) : array();

		$this->widget_start( $args, $instance );

		echo '<ul>';

		for ( $rating = 5; $rating >= 1; $rating-- ) {
			$count = $this->get_filtered_product_count( $rating );
			if ( empty( $count ) ) {
				continue;
			}
			$found = true;
			$link  = $this->get_current_page_url();

			if ( in_array( $rating, $rating_filter ) ) {
				$link_ratings = implode( ',', array_diff( $rating_filter, array( $rating ) ) );
			} else {
				$link_ratings = implode( ',', array_merge( $rating_filter, array( $rating ) ) );
			}

			$class       = in_array( $rating, $rating_filter ) ? 'wc-layered-nav-rating chosen' : 'wc-layered-nav-rating';
			$link        = apply_filters( 'woocommerce_rating_filter_link', $link_ratings ? add_query_arg( 'rating_filter', $link_ratings ) : remove_query_arg( 'rating_filter' ) );
			$rating_html = wc_get_star_rating_html( $rating );
			$count_html  = esc_html( apply_filters( 'woocommerce_rating_filter_count', "({$count})", $count, $rating ) );

			printf( '<li class="%s"><a href="%s"><span class="star-rating">%s</span> %s</a></li>', esc_attr( $class ), esc_url( $link ), $rating_html, $count_html );
		}

		echo '</ul>';

		$this->widget_end( $args );

		if ( ! $found ) {
			ob_end_clean();
		} else {
			echo ob_get_clean();
		}
	}
}
