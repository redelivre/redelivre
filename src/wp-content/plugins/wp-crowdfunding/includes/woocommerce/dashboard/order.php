<?php
defined( 'ABSPATH' ) || exit;

$html .= '<div class="wpneo-content">';
$html .= '<div class="wpneo-form">';

$args = array(
    'post_type' 		=> 'product',
    'author'    		=> get_current_user_id(),
    'tax_query' 		=> array(
        array(
            'taxonomy' => 'product_type',
            'field'    => 'slug',
            'terms'    => 'crowdfunding',
        ),
    ),
    'posts_per_page'    => -1
);
$id_list = get_posts( $args );
$id_array = array();
foreach ($id_list as $value) {
    $id_array[] = $value->ID;
}

$order_ids = array();
if( is_array( $id_array ) ){
    if(!empty($id_array)){
        $id_array = implode( ', ', $id_array );
        global $wpdb;
        $prefix = $wpdb->prefix;

        $query = "SELECT order_id 
						FROM {$wpdb->prefix}woocommerce_order_items oi 
						LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta woim 
						ON woim.order_item_id = oi.order_item_id 
						WHERE woim.meta_key='_product_id' AND woim.meta_value IN ( {$id_array} )";
        $order_ids = $wpdb->get_col( $query );
        if(is_array($order_ids)){
            if(empty($order_ids)){
                $order_ids = array( '9999999' );
            }
        }
    }else{
        $order_ids = array( '9999999' );
    }
}

$page_numb = max( 1, get_query_var('paged') );

$my_orders_columns = apply_filters( 'woocommerce_my_account_my_orders_columns', array(
    'order-number'  => __( 'Order', 'wp-crowdfunding' ),
    'order-date'    => __( 'Date', 'wp-crowdfunding' ),
    'order-status'  => __( 'Status', 'wp-crowdfunding' ),
    'order-total'   => __( 'Total', 'wp-crowdfunding' ),
    'order-rewards' => __( 'Rewards', 'wp-crowdfunding' ),
    'order-actions'    => __( 'Actions', 'wp-crowdfunding' ),
    //'order-actions' => '&nbsp;',
) );

$customer_orders = get_posts( apply_filters( 'woocommerce_my_account_my_orders_query', array(
    'numberposts' => 10, // Chnage Number
    'paged'		  => $page_numb,
    'post__in'	  => $order_ids,
    'meta_key'    => '_customer_user',
    'post_type'   => wc_get_order_types( 'view-orders' ),
    'post_status' => array_keys( wc_get_order_statuses() )
) ) );
$html .='<div class="wpneo-shadow wpneo-padding25 wpneo-clearfix">';

if ( $customer_orders ) :
    $html .='<div class="wpneo-responsive-table">';
        $html .='<table width="100%" class="stripe-table">';

        $html .='<thead>';
        $html .='<tr>';
        foreach ( $my_orders_columns as $column_id => $column_name ) :
            $html .='<th class="'.esc_attr( $column_id ).'"><span class="nobr">'.esc_html( $column_name ).'</span></th>';
        endforeach;
        $html .='</tr>';
        $html .='</thead>';

        $html .='<tbody>';
        foreach ( $customer_orders as $customer_order ) :
            $order      = wc_get_order( $customer_order );
            $order_date = (array) $order->get_date_created();
            $item_count = $order->get_item_count();

            $html .='<tr class="order">';
            foreach ( $my_orders_columns as $column_id => $column_name ) :
                $html .='<td class="'.esc_attr( $column_id ).'" data-title="'.esc_attr( $column_name ).'">';
                if ( has_action( 'woocommerce_my_account_my_orders_column_' . $column_id ) ) :
                    do_action( 'woocommerce_my_account_my_orders_column_' . $column_id, $order );

                elseif ( 'order-number' === $column_id ) :

                    $html .= _x( '#', 'hash before order number', 'wp-crowdfunding' ) . $order->get_order_number();

                elseif ( 'order-view' === $column_id ) :
                    $html .= '<a class="label-info" href="'.$order->get_view_order_url().'">'.__("View","wp-crowdfunding").'</a>';

                elseif ( 'order-date' === $column_id ) :
                    $html .='<time datetime="'.date( 'Y-m-d', strtotime( $order_date['date'] ) ).'" title="'.esc_attr( strtotime( $order_date['date'] ) ).'">'.date_i18n( get_option( "date_format" ), strtotime( $order_date['date'] ) ).'</time>';

                elseif ( 'order-status' === $column_id ) :
                    $html .= wc_get_order_status_name( $order->get_status() );

                elseif ( 'order-total' === $column_id ) :
                    $html .= sprintf( _n( '%s for %s item', '%s for %s items', $item_count, 'wp-crowdfunding' ), $order->get_formatted_order_total(), $item_count );


                elseif ( 'order-rewards' === $column_id ) :
                    /**
                    * Get specific rewards from amount
                    */
                    $ordered_items = $order->get_items();
                    foreach ( $ordered_items as $item ) {
                        $product_id = $item['product_id'];

                        //$html .= $product_id;

                        $campaign_rewards = get_post_meta($product_id, 'wpneo_reward', true);
                        $campaign_rewards_a = json_decode($campaign_rewards, true);

                        //$order_total = $order->order_total;
                        $order_total = 150;

                        $rewards_amount = '';
                        $temp = 0;
                        if (is_array($campaign_rewards_a)) {
                            if (count($campaign_rewards_a) > 0) {
                                foreach ($campaign_rewards_a as $key => $value) {
                                    if ($order_total >= $value['wpneo_rewards_pladge_amount']) {
                                        if( $temp <= $value['wpneo_rewards_pladge_amount'] ){
                                            $temp = $value['wpneo_rewards_pladge_amount'];
                                            $rewards_amount = '<a class="label-default" href="'.get_permalink($product_id).'" target="_blank">'.__('Rewards', 'wp-crowdfunding'). wpcf_function()->price($value['wpneo_rewards_pladge_amount']).'</a>';
                                        }

                                    }
                                }

                            }
                        }

                        $html .= $rewards_amount;
                    }

                elseif ( 'order-actions' === $column_id ) :
                    $html .='<div class="button wpcf-order-view" data-orderid="'.$order->get_ID().'">' . __( "View","wp-crowdfunding" ) . '</div>';
                endif;

                $html .='</td>';
            endforeach;
            $html .='</tr>';
        endforeach;
        $html .='</tbody>';
        $html .='</table>';
    $html .='</div>';//wpneo-responsive-table
else:
    $html .= "<p>".__( 'Sorry, No Pledges Received Data Found.','wp-crowdfunding' )."</p>";
endif;
$html .= "</div>";//wpneo-padding25

$customer_order_all = get_posts( apply_filters( 'woocommerce_my_account_my_orders_query', array(
    'numberposts' => -1,
    'post__in'	  => $order_ids,
    'meta_key'    => '_customer_user',
    'meta_value'  => get_current_user_id(),
    'post_type'   => wc_get_order_types( 'view-orders' ),
    'post_status' => array_keys( wc_get_order_statuses() )
) ) );

$max_page = 1;
if(!empty($customer_order_all)){
    $max_page = ceil( count($customer_order_all)/10 );
}
// Pagination
$html .= wpcf_function()->get_pagination( $page_numb , $max_page );


$html .='</div>';
$html .='</div>';
