<?php
/**
 * Subscriptions Admin Report - Subscriptions by customer
 *
 * Creates the subscription admin reports area.
 *
 * @package    WooCommerce Subscriptions
 * @subpackage WC_Subscriptions_Admin_Reports
 * @category   Class
 * @author     Prospress
 * @since      2.1
 */
class WCS_Report_Subscription_By_Customer extends WP_List_Table {

	private $totals;

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct( array(
			'singular'  => __( 'Customer', 'woocommerce-subscriptions' ),
			'plural'    => __( 'Customers', 'woocommerce-subscriptions' ),
			'ajax'      => false,
		) );
	}

	/**
	 * No subscription products found text.
	 */
	public function no_items() {
		esc_html_e( 'No customers found.', 'woocommerce-subscriptions' );
	}

	/**
	 * Output the report.
	 */
	public function output_report() {

		$this->prepare_items();
		echo '<div id="poststuff" class="woocommerce-reports-wide">';
		echo '	<div id="postbox-container-1" class="postbox-container" style="width: 280px;"><div class="postbox" style="padding: 10px;">';
		echo '	<h3>' . esc_html__( 'Customer Totals', 'woocommerce-subscriptions' ) . '</h3>';
		echo '	<p><strong>' . esc_html__( 'Total Subscribers', 'woocommerce-subscriptions' ) . '</strong>: ' . esc_html( $this->totals->total_customers ) . wcs_help_tip( __( 'The number of unique customers with a subscription of any status other than pending or trashed.', 'woocommerce-subscriptions' ) ) . '<br />';
		echo '	<strong>' . esc_html__( 'Active Subscriptions', 'woocommerce-subscriptions' ) . '</strong>: ' . esc_html( $this->totals->active_subscriptions ) . wcs_help_tip( __( 'The total number of subscriptions with a status of active or pending cancellation.', 'woocommerce-subscriptions' ) ) . '<br />';
		echo '	<strong>' . esc_html__( 'Total Subscriptions', 'woocommerce-subscriptions' ) . '</strong>: ' . esc_html( $this->totals->total_subscriptions ) . wcs_help_tip( __( 'The total number of subscriptions with a status other than pending or trashed.', 'woocommerce-subscriptions' ) ) . '<br />';
		echo '	<strong>' . esc_html__( 'Total Subscription Orders', 'woocommerce-subscriptions' ) . '</strong>: ' . esc_html( $this->totals->initial_order_count + $this->totals->renewal_switch_count ) . wcs_help_tip( __( 'The total number of sign-up, switch and renewal orders placed with your store with a paid status (i.e. processing or complete).', 'woocommerce-subscriptions' ) ) . '<br />';
		echo '	<strong>' . esc_html__( 'Average Lifetime Value', 'woocommerce-subscriptions' ) . '</strong>: ';
		echo wp_kses_post( wc_price( $this->totals->total_customers > 0 ? ( ( $this->totals->initial_order_total + $this->totals->renewal_switch_total ) / $this->totals->total_customers ) : 0 ) );
		echo wcs_help_tip( __( 'The average value of all customers\' sign-up, switch and renewal orders.', 'woocommerce-subscriptions' ) ) . '</p>';
		echo '</div></div>';
		$this->display();
		echo '</div>';

	}

	/**
	 * Get column value.
	 *
	 * @param WP_User $user
	 * @param string $column_name
	 * @return string
	 */
	public function column_default( $user, $column_name ) {
		global $wpdb;

		switch ( $column_name ) {

			case 'customer_name' :
				$user_info = get_userdata( $user->customer_id );
				return '<a href="' . get_edit_user_link( $user->customer_id ) . '">' . $user_info->user_email  . '</a>';

			case 'active_subscription_count' :
				return $user->active_subscriptions;

			case 'total_subscription_count' :
				return sprintf( '<a href="%s%d">%d</a>', admin_url( 'edit.php?post_type=shop_subscription&_customer_user=' ), $user->customer_id, $user->total_subscriptions );

			case 'total_subscription_order_count' :
				return sprintf( '<a href="%s%d">%d</a>', admin_url( 'edit.php?post_type=shop_order&_paid_subscription_orders_for_customer_user=' ), $user->customer_id, $user->initial_order_count + $user->renewal_switch_count );

			case 'customer_lifetime_value' :
				return wc_price( $user->initial_order_total + $user->renewal_switch_total );

		}

		return '';
	}

	/**
	 * Get columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'customer_name'                  => __( 'Customer', 'woocommerce-subscriptions' ),
			'active_subscription_count'      => sprintf( __( 'Active Subscriptions %s', 'woocommerce-subscriptions' ), wcs_help_tip( __( 'The number of subscriptions this customer has with a status of active or pending cancellation.', 'woocommerce-subscriptions' ) ) ),
			'total_subscription_count'       => sprintf( __( 'Total Subscriptions %s', 'woocommerce-subscriptions' ), wcs_help_tip( __( 'The number of subscriptions this customer has with a status other than pending or trashed.', 'woocommerce-subscriptions' ) ) ),
			'total_subscription_order_count' => sprintf( __( 'Total Subscription Orders %s', 'woocommerce-subscriptions' ), wcs_help_tip( __( 'The number of sign-up, switch and renewal orders this customer has placed with your store with a paid status (i.e. processing or complete).', 'woocommerce-subscriptions' ) ) ),
			'customer_lifetime_value'        => sprintf( __( 'Lifetime Value from Subscriptions %s', 'woocommerce-subscriptions' ), wcs_help_tip( __( 'The total value of this customer\'s sign-up, switch and renewal orders.', 'woocommerce-subscriptions' ) ) ),
		);

		return $columns;
	}

	/**
	 * Prepare subscription list items.
	 */
	public function prepare_items() {
		global $wpdb;

		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
		$current_page          = absint( $this->get_pagenum() );
		$per_page              = absint( apply_filters( 'wcs_reports_customers_per_page', 20 ) );
		$offset                = absint( ( $current_page - 1 ) * $per_page );

		$this->totals = self::get_data();

		$customer_query = apply_filters( 'wcs_reports_current_customer_query',
			"SELECT customer_ids.meta_value as customer_id,
					COUNT(subscription_posts.ID) as total_subscriptions,
					COALESCE( SUM(parent_total.meta_value), 0) as initial_order_total,
					COUNT(DISTINCT parent_order.ID) as initial_order_count,
					SUM(CASE
							WHEN subscription_posts.post_status
								IN  ( 'wc-" . implode( "','wc-", apply_filters( 'wcs_reports_active_statuses', array( 'active', 'pending-cancel' ) ) ) . "' ) THEN 1
							ELSE 0
							END) AS active_subscriptions
				FROM {$wpdb->posts} subscription_posts
				INNER JOIN {$wpdb->postmeta} customer_ids
					ON customer_ids.post_id = subscription_posts.ID
					AND customer_ids.meta_key = '_customer_user'
				LEFT JOIN {$wpdb->posts} parent_order
					ON parent_order.ID = subscription_posts.post_parent
					AND parent_order.post_status IN ( 'wc-" . implode( "','wc-", apply_filters( 'woocommerce_reports_paid_order_statuses', array( 'completed', 'processing' ) ) ) . "' )
				LEFT JOIN {$wpdb->postmeta} parent_total
					ON parent_total.post_id = parent_order.ID
					AND parent_total.meta_key = '_order_total'
				WHERE subscription_posts.post_type = 'shop_subscription'
					AND subscription_posts.post_status NOT IN ('wc-pending', 'trash')
				GROUP BY customer_ids.meta_value
				ORDER BY customer_id DESC
				LIMIT {$offset}, {$per_page}" );

		$this->items = $wpdb->get_results( $customer_query );

		// Now get each customer's renewal and switch total
		$customer_renewal_switch_total_query = apply_filters( 'wcs_reports_current_customer_renewal_switch_total_query',
			"SELECT
				customer_ids.meta_value as customer_id,
				COALESCE( SUM(renewal_switch_totals.meta_value), 0) as renewal_switch_total,
				COUNT(DISTINCT renewal_order_posts.ID) as renewal_switch_count
				FROM {$wpdb->postmeta} renewal_order_ids
				INNER JOIN {$wpdb->posts} subscription_posts
					ON renewal_order_ids.meta_value = subscription_posts.ID
					AND subscription_posts.post_type = 'shop_subscription'
					AND subscription_posts.post_status NOT IN ('wc-pending', 'trash')
				INNER JOIN {$wpdb->postmeta} customer_ids
					ON renewal_order_ids.meta_value = customer_ids.post_id
					AND customer_ids.meta_key = '_customer_user'
					AND customer_ids.meta_value IN ('" . implode( "','", wp_list_pluck( $this->items, 'customer_id' ) ) . "' )
				INNER JOIN {$wpdb->posts} renewal_order_posts
					ON renewal_order_ids.post_id = renewal_order_posts.ID
					AND renewal_order_posts.post_status IN ( 'wc-" . implode( "','wc-", apply_filters( 'woocommerce_reports_paid_order_statuses', array( 'completed', 'processing' ) ) ) . "' )
				LEFT JOIN {$wpdb->postmeta} renewal_switch_totals
					ON renewal_switch_totals.post_id = renewal_order_ids.post_id
					AND renewal_switch_totals.meta_key = '_order_total'
			WHERE renewal_order_ids.meta_key = '_subscription_renewal'
				OR renewal_order_ids.meta_key = '_subscription_switch'
			GROUP BY customer_id
			ORDER BY customer_id"
		);

		$customer_renewal_switch_totals = $wpdb->get_results( $customer_renewal_switch_total_query, OBJECT_K );

		foreach ( $this->items as $index => $item ) {
			if ( isset( $customer_renewal_switch_totals[ $item->customer_id ] ) ) {
				$this->items[ $index ]->renewal_switch_total = $customer_renewal_switch_totals[ $item->customer_id ]->renewal_switch_total;
				$this->items[ $index ]->renewal_switch_count = $customer_renewal_switch_totals[ $item->customer_id ]->renewal_switch_count;
			} else {
				$this->items[ $index ]->renewal_switch_total = $this->items[ $index ]->renewal_switch_count = 0;
			}
		}

		/**
		 * Pagination.
		 */
		$this->set_pagination_args( array(
			 'total_items' => $this->totals->total_customers,
			 'per_page'    => $per_page,
			 'total_pages' => ceil( $this->totals->total_customers / $per_page ),
		) );

	}

	/**
	* Gather totals for customers
	*/
	public static function get_data( $args = array() ) {
		global $wpdb;

		$default_args = array(
			'no_cache'     => false,
			'order_status' => apply_filters( 'woocommerce_reports_paid_order_statuses', array( 'completed', 'processing' ) ),
		);

		$args = apply_filters( 'wcs_reports_customer_total_args', $args );
		$args = wp_parse_args( $args, $default_args );

		$total_query = apply_filters( 'wcs_reports_customer_total_query',
			"SELECT COUNT( DISTINCT customer_ids.meta_value) as total_customers,
					COUNT(subscription_posts.ID) as total_subscriptions,
					COALESCE( SUM(parent_total.meta_value), 0) as initial_order_total,
					COUNT(DISTINCT parent_order.ID) as initial_order_count,
					COALESCE(SUM(CASE
							WHEN subscription_posts.post_status
								IN  ( 'wc-" . implode( "','wc-", apply_filters( 'wcs_reports_active_statuses', array( 'active', 'pending-cancel' ) ) ) . "' ) THEN 1
							ELSE 0
							END), 0) AS active_subscriptions
				FROM {$wpdb->posts} subscription_posts
				INNER JOIN {$wpdb->postmeta} customer_ids
					ON customer_ids.post_id = subscription_posts.ID
					AND customer_ids.meta_key = '_customer_user'
				LEFT JOIN {$wpdb->posts} parent_order
					ON parent_order.ID = subscription_posts.post_parent
					AND parent_order.post_status IN ( 'wc-" . implode( "','wc-", $args['order_status'] ) . "' )
				LEFT JOIN {$wpdb->postmeta} parent_total
					ON parent_total.post_id = parent_order.ID
					AND parent_total.meta_key = '_order_total'
				WHERE subscription_posts.post_type = 'shop_subscription'
				AND subscription_posts.post_status NOT IN ('wc-pending', 'trash')
		");

		$cached_results = get_transient( strtolower( __CLASS__ ) );
		$query_hash     = md5( $total_query );

		if ( $args['no_cache'] || false === $cached_results || ! isset( $cached_results[ $query_hash ] ) ) {
			// Enable big selects for reports
			$wpdb->query( 'SET SESSION SQL_BIG_SELECTS=1' );
			$cached_results[ $query_hash ] = apply_filters( 'wcs_reports_customer_total_data', $wpdb->get_row( $total_query ) );
			set_transient( strtolower( __CLASS__ ), $cached_results, WEEK_IN_SECONDS );
		}

		$customer_totals = $cached_results[ $query_hash ];

		$renewal_switch_total_query = apply_filters( 'wcs_reports_customer_total_renewal_switch_query',
			"SELECT COALESCE( SUM(renewal_switch_totals.meta_value), 0) as renewal_switch_total,
				COUNT(DISTINCT renewal_order_posts.ID) as renewal_switch_count
				FROM {$wpdb->postmeta} renewal_order_ids
				INNER JOIN {$wpdb->posts} subscription_posts
					ON renewal_order_ids.meta_value = subscription_posts.ID
					AND subscription_posts.post_type = 'shop_subscription'
					AND subscription_posts.post_status NOT IN ('wc-pending', 'trash')
				INNER JOIN {$wpdb->posts} renewal_order_posts
					ON renewal_order_ids.post_id = renewal_order_posts.ID
					AND renewal_order_posts.post_status IN ( 'wc-" . implode( "','wc-", $args['order_status'] ) . "' )
				LEFT JOIN {$wpdb->postmeta} renewal_switch_totals
					ON renewal_switch_totals.post_id = renewal_order_ids.post_id
					AND renewal_switch_totals.meta_key = '_order_total'
			WHERE renewal_order_ids.meta_key = '_subscription_renewal'
			OR renewal_order_ids.meta_key = '_subscription_switch'"
		);

		$query_hash = md5( $renewal_switch_total_query );

		if ( $args['no_cache'] || false === $cached_results || ! isset( $cached_results[ $query_hash ] ) ) {
			// Enable big selects for reports
			$wpdb->query( 'SET SESSION SQL_BIG_SELECTS=1' );
			$cached_results[ $query_hash ] = apply_filters( 'wcs_reports_customer_total_renewal_switch_data', $wpdb->get_row( $renewal_switch_total_query ) );
			set_transient( strtolower( __CLASS__ ), $cached_results, WEEK_IN_SECONDS );
		}

		$customer_totals->renewal_switch_total = $cached_results[ $query_hash ]->renewal_switch_total;
		$customer_totals->renewal_switch_count = $cached_results[ $query_hash ]->renewal_switch_count;

		return $customer_totals;
	}
}
