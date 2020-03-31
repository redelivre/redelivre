<?php
/**
 * Modified @date: 21/04/2017
 */

global $post;
$product = new WC_Product($post->ID);
echo '<div class="woocommerce">';

if (wpcf_function()->wc_version()){
    echo wc_get_rating_html($product->get_average_rating());
}else{
    echo $product->get_rating_html();
}
echo '</div>';