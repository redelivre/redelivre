<?php
/**
 * Create settings and add meta boxes relating to retries
 *
 * @author   Prospress
 * @category Admin
 * @package  WooCommerce Subscriptions/Admin
 * @version  2.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WCS_Retry_Admin {

	/**
	 * Constructor
	 */
	public function __construct( $setting_id ) {

		$this->setting_id = $setting_id;

		add_filter( 'woocommerce_subscription_settings', array( $this, 'add_settings' ) );

		if ( WCS_Retry_Manager::is_retry_enabled() ) {
			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 50 );

			add_filter( 'wcs_display_date_type', array( $this, 'maybe_hide_date_type' ), 10, 3 );

			// Display the number of retries in the Orders list table
			add_action( 'manage_shop_order_posts_custom_column', __CLASS__ . '::add_column_content', 20, 2 );

			add_filter( 'wcs_system_status', array( $this, 'add_system_status_content' ) );
		}
	}

	/**
	 * Add a meta box to the Edit Order screen to display the retries relating to that order
	 */
	public function add_meta_boxes() {
		global $current_screen, $post_ID;

		// Only display the meta box if an order relates to a subscription
		if ( 'shop_order' === get_post_type( $post_ID ) && wcs_order_contains_renewal( $post_ID ) && WCS_Retry_Manager::store()->get_retry_count_for_order( $post_ID ) > 0 ) {
			add_meta_box( 'renewal_payment_retries', __( 'Automatic Failed Payment Retries', 'woocommerce-subscriptions' ), 'WCS_Meta_Box_Payment_Retries::output', 'shop_order', 'normal', 'low' );
		}
	}

	/**
	 * Only display the retry payment date on the Edit Subscription screen if the subscription has a pending retry
	 * and when that is the case, do not display the next payment date (because it will still be set to the original
	 * payment date, in the past).
	 *
	 * @param bool            $show_date_type
	 * @param string          $date_key
	 * @param WC_Subscription $the_subscription
	 *
	 * @return bool
	 */
	public function maybe_hide_date_type( $show_date_type, $date_key, $the_subscription ) {

		if ( 'payment_retry' === $date_key && 0 == $the_subscription->get_time( 'payment_retry' ) ) {
			$show_date_type = false;
		} elseif ( 'next_payment' === $date_key && $the_subscription->get_time( 'payment_retry' ) > 0 ) {
			$show_date_type = false;
		}

		return $show_date_type;
	}

	/**
	 * Dispay the number of retries on a renewal order in the Orders list table.
	 *
	 * @param string $column  The string of the current column
	 * @param int    $post_id The ID of the order
	 *
	 * @since 2.1
	 */
	public static function add_column_content( $column, $post_id ) {

		if ( 'subscription_relationship' == $column && wcs_order_contains_renewal( $post_id ) ) {

			$retries = WCS_Retry_Manager::store()->get_retries_for_order( $post_id );

			if ( ! empty( $retries ) ) {

				$retry_counts = array();
				$tool_tip     = '';

				foreach ( $retries as $retry ) {
					$retry_counts[ $retry->get_status() ] = isset( $retry_counts[ $retry->get_status() ] ) ? ++$retry_counts[ $retry->get_status() ] : 1;
				}

				foreach ( $retry_counts as $retry_status => $retry_count ) {

					switch ( $retry_status ) {
						case 'pending':
							$tool_tip .= sprintf( _n( '%d Pending Payment Retry', '%d Pending Payment Retries', $retry_count, 'woocommerce-subscriptions' ), $retry_count );
							break;
						case 'processing':
							$tool_tip .= sprintf( _n( '%d Processing Payment Retry', '%d Processing Payment Retries', $retry_count, 'woocommerce-subscriptions' ), $retry_count );
							break;
						case 'failed':
							$tool_tip .= sprintf( _n( '%d Failed Payment Retry', '%d Failed Payment Retries', $retry_count, 'woocommerce-subscriptions' ), $retry_count );
							break;
						case 'complete':
							$tool_tip .= sprintf( _n( '%d Successful Payment Retry', '%d Successful Payment Retries', $retry_count, 'woocommerce-subscriptions' ), $retry_count );
							break;
						case 'cancelled':
							$tool_tip .= sprintf( _n( '%d Cancelled Payment Retry', '%d Cancelled Payment Retries', $retry_count, 'woocommerce-subscriptions' ), $retry_count );
							break;
					}

					$tool_tip .= '<br />';
				}

				echo '<br /><span class="payment_retry tips" data-tip="' . esc_attr( $tool_tip ) . '"></span>';
			}
		}
	}

	/**
	 * Add a setting to enable/disable the retry system
	 *
	 * @param array
	 *
	 * @return null
	 */
	public function add_settings( $settings ) {

		$misc_section_end = wp_list_filter( $settings, array(
			'id'   => 'woocommerce_subscriptions_miscellaneous',
			'type' => 'sectionend',
		) );

		$spliced_array = array_splice( $settings, key( $misc_section_end ), 0, array(
			array(
				'name'     => __( 'Retry Failed Payments', 'woocommerce-subscriptions' ),
				'desc'     => __( 'Enable automatic retry of failed recurring payments', 'woocommerce-subscriptions' ),
				'id'       => $this->setting_id,
				'default'  => 'no',
				'type'     => 'checkbox',
				'desc_tip' => sprintf( __( 'Attempt to recover recurring revenue that would otherwise be lost due to payment methods being declined only temporarily. %sLearn more%s.', 'woocommerce-subscriptions' ), '<a href="https://docs.woocommerce.com/document/subscriptions/failed-payment-retry/">', '</a>' ),
			),
		) );

		return $settings;
	}

	/**
	 * Add system status information about custom retry rules.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public static function add_system_status_content( $data ) {
		$has_custom_retry_rules      = has_action( 'wcs_default_retry_rules' );
		$has_custom_retry_rule_class = has_action( 'wcs_retry_rule_class' );
		$has_custom_raw_retry_rule   = has_action( 'wcs_get_retry_rule_raw' );
		$has_custom_retry_rule       = has_action( 'wcs_get_retry_rule' );
		$has_retry_on_post_store     = WCS_Retry_Migrator::needs_migration();

		$data['wcs_retry_rules_overridden'] = array(
			'name'      => _x( 'Custom Retry Rules', 'label for the system status page', 'woocommerce-subscriptions' ),
			'label'     => 'Custom Retry Rules',
			'mark_icon' => $has_custom_retry_rules ? 'warning' : 'yes',
			'note'      => $has_custom_retry_rules ? 'Yes' : 'No',
			'success'   => ! $has_custom_retry_rules,
		);

		$data['wcs_retry_rule_class_overridden'] = array(
			'name'      => _x( 'Custom Retry Rule Class', 'label for the system status page', 'woocommerce-subscriptions' ),
			'label'     => 'Custom Retry Rule Class',
			'mark_icon' => $has_custom_retry_rule_class ? 'warning' : 'yes',
			'note'      => $has_custom_retry_rule_class ? 'Yes' : 'No',
			'success'   => ! $has_custom_retry_rule_class,
		);

		$data['wcs_raw_retry_rule_overridden'] = array(
			'name'      => _x( 'Custom Raw Retry Rule', 'label for the system status page', 'woocommerce-subscriptions' ),
			'label'     => 'Custom Raw Retry Rule',
			'mark_icon' => $has_custom_raw_retry_rule ? 'warning' : 'yes',
			'note'      => $has_custom_raw_retry_rule ? 'Yes' : 'No',
			'success'   => ! $has_custom_raw_retry_rule,
		);

		$data['wcs_retry_rule_overridden'] = array(
			'name'      => _x( 'Custom Retry Rule', 'label for the system status page', 'woocommerce-subscriptions' ),
			'label'     => 'Custom Retry Rule',
			'mark_icon' => $has_custom_retry_rule ? 'warning' : 'yes',
			'note'      => $has_custom_retry_rule ? 'Yes' : 'No',
			'success'   => ! $has_custom_retry_rule,
		);

		$data['wcs_retry_data_migration_status'] = array(
			'name'      => _x( 'Retries Migration Status', 'label for the system status page', 'woocommerce-subscriptions' ),
			'label'     => 'Retries Migration Status',
			'mark_icon' => $has_retry_on_post_store ? '' : 'yes',
			'note'      => $has_retry_on_post_store ? 'In-Progress' : 'Completed',
			'mark'      => ( $has_retry_on_post_store ) ? '' : 'yes',
		);

		return $data;
	}
}
