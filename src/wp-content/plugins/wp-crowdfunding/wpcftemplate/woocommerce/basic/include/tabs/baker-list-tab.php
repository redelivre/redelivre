<?php
defined( 'ABSPATH' ) || exit;

global $post;

$campaign_id = $post->ID;

$baker_list = wpcf_function()->get_customers_product();
?>
<table>
    <tr>
        <th><?php _e('Name', 'wp-crowdfunding'); ?></th>
        <th><?php _e('Donate Amount', 'wp-crowdfunding'); ?></th>
        <th><?php _e('Date', 'wp-crowdfunding'); ?></th>
    </tr>

    <?php
    foreach($baker_list as $order){
        $order = new WC_Order($order);

        if ($order->get_status() == 'completed') {
            $ordered_items = $order->get_items();
            $ordered_this_item = '';
            foreach ($ordered_items as $item) {
                if (!empty($item['item_meta']['_product_id'][0])) {
                    if ($campaign_id == $item['item_meta']['_product_id'][0])
                        $ordered_this_item = $item;
                }
            }
            ?>
            <tr>
                <td>
                    <?php
                    if (get_post_meta(get_the_ID(), 'wpneo_mark_contributors_as_anonymous', true) == "1") {
                        echo __("Anonymous", "wp-crowdfunding");
                    } else {
                        $mark_anonymous = get_post_meta($order->get_id(), 'mark_name_anonymous', true);
                        if ($mark_anonymous === 'true'){
                            _e('Anonymous', 'wp-crowdfunding');
                        }else{
                            echo $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
                        }
                    }
                    ?>
                </td>
                <td>
                    <?php
                        echo wpcf_function()->price($order->get_total());
                    ?>
                </td>
                <td><?php echo date('F d, Y', strtotime($order->get_date_created())); ?></td>
            </tr>
            <?php
        } //if order completed
    }
    ?>
</table>
