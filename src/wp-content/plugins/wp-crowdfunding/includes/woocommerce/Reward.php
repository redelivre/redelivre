<?php
namespace WPCF\woocommerce;

defined( 'ABSPATH' ) || exit;

class Reward{

    public function __construct(){
        add_filter('woocommerce_product_data_tabs',     array($this, 'reward_tabs'));
        add_action('woocommerce_product_data_panels',   array($this, 'reward_content'));
        add_action('woocommerce_process_product_meta',  array($this, 'reward_action'));

        //Show reward in woocommerce order details
        add_action('woocommerce_order_details_after_order_table', array($this, 'selected_reward_in_order_view'));
        add_action('woocommerce_review_order_after_cart_contents', array($this, 'selected_reward_in_order_review'));
        //add_filter('the_content', array($this, 'show_reward_in_general_tab'));
    }

    /*
    * Add Reward tab (Woocommerce).
    * Only show if type "Crowdfunding" Selected
    */
    function reward_tabs($tabs){
        $tabs['reward'] = array(
            'label'     => __('Reward', 'wp-crowdfunding'),
            'target'    => 'reward_options',
            'class'     => array('show_if_neo_crowdfunding_options', 'show_if_neo_crowdfunding_options'),
        );
        return $tabs;
    }

    /*
    * Add Reward tab Content(Woocommerce).
    * Only show the fields under Reward Tab
    */
    function reward_content($post_id){
        global $post;

        $var = get_post_meta($post->ID, 'wpneo_reward', true);
        $var = stripslashes($var);
        $data_array = json_decode($var, true);

        $woocommerce_meta_field = array(
            // Pledge Amount
            array(
                'id'            => 'wpneo_rewards_pladge_amount[]',
                'label'         => __('Pledge Amount', 'wp-crowdfunding'),
                'desc_tip'      => 'true',
                'type'          => 'text',
                'placeholder'   => __('Pledge Amount', 'wp-crowdfunding'),
                'value'         => '',
                'class'         => 'wc_input_price',
                'field_type'    => 'textfield'
            ),
            // Reward Image
            array(
                'id'            => 'wpneo_rewards_image_field[]',
                'label'         => __('Image Field', 'wp-crowdfunding'),
                'desc_tip'      => 'true',
                'type'          => 'image',
                'placeholder'   => __('Image Field', 'wp-crowdfunding'),
                'value'         => '',
                'class'         => '',
                'field_type'    => 'image'
            ),
            // Reward Description
            array(
                'id'            => 'wpneo_rewards_description[]',
                'label'         => __('Reward', 'wp-crowdfunding'),
                'desc_tip'      => 'true',
                'type'          => 'text',
                'placeholder'   => __('Reward Description', 'wp-crowdfunding'),
                'value'         => '',
                'field_type'    => 'textareafield',
            ),
            // Reward Month
            array(
                'id'            => 'wpneo_rewards_endmonth[]',
                'label'         => __('Estimated Delivery Month', 'wp-crowdfunding'),
                'type'          => 'text',
                'value'         => '',
                'options'       => array(
                    ''    => __('- Select -', 'wp-crowdfunding'),
                    'jan' => __('January', 'wp-crowdfunding'),
                    'feb' => __('February', 'wp-crowdfunding'),
                    'mar' => __('March', 'wp-crowdfunding'),
                    'apr' => __('April', 'wp-crowdfunding'),
                    'may' => __('May', 'wp-crowdfunding'),
                    'jun' => __('June', 'wp-crowdfunding'),
                    'jul' => __('July', 'wp-crowdfunding'),
                    'aug' => __('August', 'wp-crowdfunding'),
                    'sep' => __('September', 'wp-crowdfunding'),
                    'oct' => __('October', 'wp-crowdfunding'),
                    'nov' => __('November', 'wp-crowdfunding'),
                    'dec' => __('December', 'wp-crowdfunding'),
                ),
                'field_type'    => 'selectfield',
            ),
            // Reward Year
            array(
                'id'            => 'wpneo_rewards_endyear[]',
                'label'         => __('Estimated Delivery Year', 'wp-crowdfunding'),
                'type'          => 'text',
                'value'         => '',
                'options'       => array(
                    ''     => __('- Select -', 'wp-crowdfunding'),
                    '2019' => __('2019', 'wp-crowdfunding'),
                    '2020' => __('2020', 'wp-crowdfunding'),
                    '2021' => __('2021', 'wp-crowdfunding'),
                    '2022' => __('2022', 'wp-crowdfunding'),
                    '2023' => __('2023', 'wp-crowdfunding'),
                    '2024' => __('2024', 'wp-crowdfunding'),
                    '2025' => __('2025', 'wp-crowdfunding'),
                ),
                'field_type'    => 'selectfield',
            ),
            // Quantity (Number of Pledge Items)
            array(
                'id'            => 'wpneo_rewards_item_limit[]',
                'label'         => __('Quantity', 'wp-crowdfunding'),
                'desc_tip'      => 'true',
                'type'          => 'text',
                'placeholder'   => __('Number of Rewards(Physical Product)', 'wp-crowdfunding'),
                'value'         => '',
                'class'         => 'wc_input_price',
                'field_type'    => 'textfield'
            ),

        );
        ?>

        <div id='reward_options' class='panel woocommerce_options_panel'>
            <?php
            $display = 'block';
            $meta_count = is_array($data_array) ? count($data_array) : 0;
            $field_count = count($woocommerce_meta_field);
            if ( $meta_count > 0 ){ $display = 'none'; }

            /*
            * Print without value of Reward System for clone group
            */
            echo "<div class='reward_group' style='display:" . $display . ";'>";
            echo "<div class='campaign_rewards_field_copy'>";

            foreach ($woocommerce_meta_field as $value) {
                switch ($value['field_type']) {

                    case 'textareafield':
                        woocommerce_wp_textarea_input($value);
                        break;

                    case 'selectfield':
                        woocommerce_wp_select($value);
                        break;

                    case 'image':
                        echo '<p class="form-field">';
                        echo '<label for="wpneo_rewards_image_field">'.$value["label"].'</label>';
                        echo '<input type="hidden" class="wpneo_rewards_image_field" name="'.$value["id"].'" value="" placeholder="'.$value["label"].'"/>';
                        echo '<span class="wpneo-image-container"></span>';
                        echo '<button class="wpneo-image-upload-btn shorter">'.__("Upload","wp-crowdfunding").'</button>';
                        echo '</p>';
                        break;

                    default:
                        woocommerce_wp_text_input($value);
                        break;
                }
            }

            echo '<input name="remove_rewards" type="button" class="button tagadd removeCampaignRewards" value="' . __('- Remove', 'wp-crowdfunding') . '" />';
            echo "</div>";
            echo "</div>";


            /*
            * Print with value of Reward System
            */
            if ($meta_count > 0) {
                if (is_array($data_array) && !empty($data_array)) {
                    foreach ($data_array as $k => $v) {
                        echo "<div class='reward_group'>";
                        echo "<div class='campaign_rewards_field_copy'>";
                        foreach ($woocommerce_meta_field as $value) {
                            if(isset( $v[str_replace('[]', '', $value['id'])] )){
                                $value['value'] = $v[str_replace('[]', '', $value['id'])];
                            }else{
                                $value['value'] = '';
                            }
                            switch ($value['field_type']) {

                                case 'textareafield':
                                    $value['value'] = html_entity_decode($value['value'],ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                    woocommerce_wp_textarea_input($value);
                                    break;

                                case 'selectfield':
                                    woocommerce_wp_select($value);
                                    break;

                                case 'image':
                                    $image_id = $value['value'];
                                    $raw_id = $image_id;
                                    if( $image_id!=0 && $image_id!='' ){
                                        $image_id = wp_get_attachment_url( $image_id );
                                        $image_id = '<img width="100" src="'.$image_id.'"><span class="wpneo-image-remove">x</span>';
                                    }else{
                                        $image_id = '';
                                    }
                                    echo '<p class="form-field">';
                                    echo '<label for="wpneo_rewards_image_field">'.$value["label"].'</label>';
                                    echo '<input type="hidden" class="wpneo_rewards_image_field" name="'.$value["id"].'" value="'.$raw_id.'" placeholder="'.$value["label"].'"/>';
                                    echo '<span class="wpneo-image-container">'.$image_id.'</span>';
                                    echo '<button class="wpneo-image-upload-btn shorter">'.__("Upload","wp-crowdfunding").'</button>';
                                    echo '</p>';
                                    break;

                                default:
                                    woocommerce_wp_text_input($value);
                                    break;
                            }
                        }
                        echo '<input name="remove_rewards" type="button" class="button tagadd removeCampaignRewards" value="' . __('- Remove', 'wp-crowdfunding') . '" />';
                        echo "</div>";
                        echo "</div>";
                    }
                }
            }

            if ( wpcf_function()->is_free() ) {
                ?>
                <p class="description"><?php _e('pro version is required to add more than 1 reward', 'wp-crowdfunding') ?>. <a href="https://www.themeum.com/product/wp-crowdfunding-plugin/?utm_source=crowdfunding_plugin" target="_blank"> <?php _e('click here to get pro version', 'wp-crowdfunding') ?></a></p>
                <?php
            } else {
                ?>
                <div id="rewards_addon_fields"></div>
                <input name="save" type="button" class="button button-primary tagadd" id="addreward" value="<?php _e('+ Add Reward', 'wp-crowdfunding'); ?>">
            <?php } ?>
        </div>

        <?php
    }

    /*
    * Save Reward tab Data(Woocommerce).
    * Update Post Meta for Reward Tab
    */
    function reward_action($post_id){
        if (!empty($_POST['wpneo_rewards_pladge_amount'])) {
            $data             = array();
            $pladge_amount    = $_POST['wpneo_rewards_pladge_amount'];
            $image_field      = $_POST['wpneo_rewards_image_field'];
            $description      = $_POST['wpneo_rewards_description'];
            $end_month        = $_POST['wpneo_rewards_endmonth'];
            $end_year         = $_POST['wpneo_rewards_endyear'];
            $item_limit       = $_POST['wpneo_rewards_item_limit'];
            $field_count      = count($pladge_amount);
            for ($i = 0; $i < $field_count; $i++) {
                if (!empty($pladge_amount[$i])) {
                    $data[] = array(
                        'wpneo_rewards_pladge_amount'   => intval($pladge_amount[$i]),
                        'wpneo_rewards_image_field'     => intval($image_field[$i]),
                        'wpneo_rewards_description'     => esc_textarea($description[$i]),
                        'wpneo_rewards_endmonth'        => esc_html($end_month[$i]),
                        'wpneo_rewards_endyear'         => esc_html($end_year[$i]),
                        'wpneo_rewards_item_limit'      => esc_html($item_limit[$i]),
                    );
                }
            }
            $data_json = json_encode( $data, JSON_UNESCAPED_UNICODE );
            wpcf_function()->update_meta($post_id, 'wpneo_reward', $data_json);
        }
    }

    /**
     * @param $order
     *
     * Show selected reward
     */
    public function selected_reward_in_order_view($order){
        $order_id = $order->get_id();
        $html = '';

        $r = get_post_meta($order_id, 'wpneo_selected_reward', true);
        if ( ! empty($r) && is_array($r) ){
            $html .="<h2>".__('Selected Reward', 'wp-crowdfunding')."</h2>";
            if ( ! empty($r['wpneo_rewards_description'])){
                $html .= "<div>{$r['wpneo_rewards_description']}</div>";
            }
            if ( ! empty($r['wpneo_rewards_pladge_amount'])){
                $html .= "<div><abbr>".__('Amount','wp-crowdfunding').' : '.wc_price($r['wpneo_rewards_pladge_amount']).', '.__(', Delivery','wp-crowdfunding').' : '.$r['wpneo_rewards_endmonth'].', '.$r['wpneo_rewards_endyear'];
            }
    
        }
        echo $html;
    }

    public function selected_reward_in_order_review(){
        $rewards_data = WC()->session->get('wpneo_rewards_data');
        $reward_data = isset($rewards_data['wpneo_selected_rewards_checkout']) ? $rewards_data['wpneo_selected_rewards_checkout'] : array();
        if (is_array($reward_data) && count($reward_data) ) {
            ?>
            <tr>
                <td>
                    <h4><?php _e('Selected Reward','wp-crowdfunding'); ?> </h4>
                    <?php
                    if ( isset($reward_data['wpneo_rewards_description'])){
                        echo "<div>{$reward_data['wpneo_rewards_description']}</div>";
                        echo __('Amount','wp-crowdfunding').' : <strong>'.wc_price($reward_data['wpneo_rewards_pladge_amount']).'</strong>, ';
                        echo __('Delivery','wp-crowdfunding').' : '.$reward_data['wpneo_rewards_endmonth'].', '.$reward_data['wpneo_rewards_endyear'];
                    }
                    ?>
                </td>
                <td></td>
            </tr>
            <?php
        }
    }

    public function show_reward_in_general_tab($content){
        if (is_product()) {
            global $post;
            $product = wc_get_product($post->ID);
            if ($product->get_type() === 'crowdfunding') {
                $col_9 = '';
                $col_3 = '';
                $campaign_rewards = get_post_meta($post->ID, 'wpneo_reward', true);
                $campaign_rewards = stripslashes($campaign_rewards);
                $campaign_rewards_a = json_decode($campaign_rewards, true);
                if (is_array($campaign_rewards_a)) {
                    if (count($campaign_rewards_a) > 0) {
                        $col_9 = 'tab_col_9';
                        $col_3 = 'tab_col_3';
                    }
                }

                $html = "<div class='tab-description-wrap wpneo-clearfix'>";
                    $html .= "<div class='tab-description {$col_9} '>";
                        $html .= $content;
                    $html .= '</div>';
                    $html .= "<div class='tab-rewards {$col_3} '>";
                        ob_start();
                        wpneo_campaign_story_right_sidebar();
                        $html .= ob_get_clean();
                    $html .= '</div>';
                $html .= '</div>';

                return $html;
            }
        }
        return $content;
    }

}
