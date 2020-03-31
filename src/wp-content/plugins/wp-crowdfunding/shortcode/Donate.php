<?php
namespace WPCF\shortcode;

defined( 'ABSPATH' ) || exit;

class Donate {

    function __construct() {
        add_shortcode( 'wpcf_donate', array( $this, 'donate_callback' ) );
    }

    function donate_callback( $atts, $shortcode ){
        $atts = shortcode_atts( array(
            'campaign_id'           => null,
            'amount'                => '',
            'show_input_box'        => 'true',
            'min_amount'            => '',
            'max_amount'            => '',
            'donate_button_text'    => __('Back Campaign', 'wp-crowdfunding'),
        ), $atts, $shortcode );

        if ( ! $atts['campaign_id']){
            return '<p class="wpcf-donate-form-response">'.__('Campaign ID required', 'wp-crowdfunding').'</p>';
        }

        $campaign = wc_get_product($atts['campaign_id']);
        if ( ! $campaign || $campaign->get_type() !== 'crowdfunding'){
            return '<p class="wpcf-donate-form-response">'.__('Invalid Campaign ID', 'wp-crowdfunding').'</p>';
        }
        ob_start();
        ?>
        <div class="wpcf-donate-form-wrap">
            <form enctype="multipart/form-data" method="post" class="cart">
                <?php
                if ($atts['show_input_box'] == 'true') {
                    echo get_woocommerce_currency_symbol(); ?>
                    <input type="number" step="any" min="0" placeholder="<?php echo $atts['amount']; ?>"
                        name="wpneo_donate_amount_field" class="input-text amount wpneo_donate_amount_field text"
                        value="<?php echo $atts['amount']; ?>" data-min-price="<?php echo $atts['min_amount'] ?>"
                        data-max-price="<?php echo $atts['max_amount'] ?>">
                    <?php
                }else{
                    echo '<input type="hidden" name="wpneo_donate_amount_field" value="'.$atts['amount'].'" />';
                }
                ?>
                <input type="hidden" value="<?php echo esc_attr($atts['campaign_id']); ?>" name="add-to-cart">
                <button type="submit" class="<?php echo apply_filters('add_to_donate_button_class', 'wpneo_donate_button'); ?>">
                    <?php
                    echo $atts['donate_button_text'];
                    if ($atts['show_input_box'] != 'true'){
                        echo ' ('.wc_price($atts['amount']).') ';
                    }
                    ?>
                </button>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
}