<?php

class WPCF_Social_Share {
    /**
     * @var null
     *
     * Instance of this class
     */
    protected static $_instance = null;

    /**
     * @return null|WPCF
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        add_action( 'init',                                 array( $this, 'embed_data') );
        add_action( 'wp_enqueue_scripts',                   array( $this, 'social_share_enqueue_frontend_script') ); //Add social share js in footer
        add_filter( 'wpcf_settings_panel_tabs',             array( $this, 'add_social_share_tab_to_wpcf_settings') ); //Hook to add social share field with user registration form

        add_action( 'init',                                 array( $this, 'social_share_save_settings') ); // Social Share Settings
        add_action( 'wp_ajax_wpcf_embed_action',            array( $this, 'embed_campaign_action') );
        add_action( 'wp_ajax_nopriv_wpcf_embed_action',     array( $this, 'embed_campaign_action') );
        add_action( 'wpcf_after_single_campaign_summary',   array( $this, 'single_campaign_social_share') );
    }

    public function add_social_share_tab_to_wpcf_settings($tabs){
        $tabs['social-share'] = array(
            'tab_name' => __('Social Share','wp-crowdfunding'),
            'load_form_file' => WPCF_SOCIAL_SHARE_DIR_PATH.'pages/tab-social-share.php'
        );
        return $tabs;
    }

    public function social_share_enqueue_frontend_script() {
        wp_enqueue_script('wpcf-social-share-front', WPCF_DIR_URL .'addons/social-share/assets/js/SocialShare.min.js', array('jquery'), WPCF_VERSION, true);
    }

    /**
     * All settings will be save in this method
     */
    public function social_share_save_settings(){
        if (isset($_POST['wpneo_admin_settings_submit_btn']) && isset($_POST['wpcf_varify_share']) && wp_verify_nonce( $_POST['wpneo_settings_page_nonce_field'], 'wpneo_settings_page_action' ) ){
            // Checkbox
            $embed_share = sanitize_text_field(wpcf_function()->post('wpcf_embed_share'));
            wpcf_function()->update_checkbox('wpcf_embed_share', $embed_share);

            $social_share = wpcf_function()->post('wpcf_social_share');
            wpcf_function()->update_checkbox('wpcf_social_share', $social_share);

        }
    }

    // Data Post Embed Code
    public function embed_data(){
        $url = $_SERVER["REQUEST_URI"];
        $embed = strpos($url, 'themeumembed');
        if ($embed!==false){
            $end_part = explode('/', rtrim($url, '/'));
            if( $end_part ){
                global $post;
                $post_id = end( $end_part );
                $args = array( 'p' => $post_id, 'post_type' => 'product','post_status' => 'publish' );
                $myposts = get_posts( $args );
                foreach ( $myposts as $post ) : setup_postdata( $post ); ?>
                    <!DOCTYPE html>
                    <html>
                        <head>
                            <style type="text/css">
                                .wpneo-listings{
                                    width: 300px;
                                    border: 1px solid #e9e9e9;
                                    border-radius: 3px;
                                }
                                .wpneo-listing-img {
                                    position: relative;
                                }
                                .wpneo-listing-img img {
                                    width: 100%;
                                    height: auto;
                                }
                                .wpneo-listing-content{
                                    padding: 15px;
                                }
                                .wpneo-listing-content h4{
                                    margin: 0;
                                }
                                .wpneo-listing-content h4 a {
                                    color: #000;
                                    font-size: 24px;
                                    font-weight: normal;
                                    line-height: 28px;
                                    box-shadow: none;
                                    text-decoration: none;
                                    letter-spacing: normal;
                                    text-transform: capitalize;
                                }
                                .wpneo-author {
                                    color: #737373;
                                    font-size: 16px;
                                    line-height: 18px;
                                    margin: 0;
                                }
                                .wpneo-author a {
                                    color: #737373;
                                    text-decoration: none;
                                    box-shadow: none;
                                }
    
                                #neo-progressbar {
                                    overflow: hidden;
                                    background-color: #f2f2f2;
                                    border-radius: 7px;
                                    padding: 0px;
                                }
                                #neo-progressbar > div {
                                    background-color: #4C76FF;
                                    height: 10px;
                                    border-radius: 10px;
                                }
                            </style>
                        </head>
                        <body>
                            <div class="wpneo-listings">
                                <div class="wpneo-listing-img">
                                    <a target="_top" href="<?php echo get_permalink(); ?>" title="<?php the_title(); ?>"><?php echo woocommerce_get_product_thumbnail(); ?></a>
                                </div>
                                <div class="wpneo-listing-content">
    
                                    <h4><a target="_top" href="<?php echo get_permalink(); ?>"><?php the_title(); ?></a></h4>
                                    <p class="wpneo-author"><?php _e('by','wp-crowdfunding'); ?> 
                                        <a target="_top" href="<?php echo wpcf_function()->get_author_url( get_the_author_meta( 'user_login' ) ); ?>"><?php echo wpcf_function()->get_author_name(); ?></a>
                                    </p>
    
                                    <?php
                                        $location = wpcf_function()->campaign_location(); 
                                        if ($location){ ?>
                                        <div class="wpneo-location-wrapper">
                                            <span><?php echo $location; ?></span>
                                        </div>
                                    <?php } ?>
    
                                    <p class="wpneo-short-description"><?php echo wpcf_function()->limit_word_text(strip_tags(get_the_content()), 130); ?></p>
    
                                    <?php $raised_percent = wpcf_function()->get_fund_raised_percent_format(); ?>
                                    <div class="wpneo-raised-percent">
                                        <div class="wpneo-meta-name"><?php _e('Raised Percent', 'wp-crowdfunding'); ?> :</div>
                                        <div class="wpneo-meta-desc" ><?php echo $raised_percent; ?></div>
                                    </div>
    
                                    <div class="wpneo-raised-bar sjkdhfjdshf">
                                        <div id="neo-progressbar">
                                            <?php $css_width = wpcf_function()->get_raised_percent(); if( $css_width >= 100 ){ $css_width = 100; } ?>
                                            <div style="width: <?php echo $css_width; ?>%"></div>
                                        </div>
                                    </div>
    
                                    <div class="wpneo-funding-data">
                                        
                                        <?php $funding_goal = get_post_meta( get_the_ID() , '_nf_funding_goal', true); ?>
                                        <div class="wpneo-funding-goal">
                                            <div class="wpneo-meta-desc"><?php echo wc_price( $funding_goal ); ?></div>
                                            <div class="wpneo-meta-name"><?php _e('Funding Goal', 'wp-crowdfunding'); ?></div>
                                        </div>
    
                                        <?php
                                        $end_method = get_post_meta(get_the_ID(), 'wpneo_campaign_end_method', true);
                                        $days_remaining = apply_filters('date_expired_msg', __('0', 'wp-crowdfunding'));
                                        if (wpcf_function()->get_date_remaining()){
                                            $days_remaining = apply_filters('date_remaining_msg', __(wpcf_function()->get_date_remaining(), 'wp-crowdfunding'));
                                        }
                                        if ($end_method != 'never_end'){ ?>
                                            <div class="wpneo-time-remaining">
                                                <div class="wpneo-meta-desc"><?php echo $days_remaining; ?></div>
                                                <div class="wpneo-meta-name float-left"><?php _e('Days to go', 'wp-crowdfunding'); ?></div>
                                            </div>
                                        <?php } ?>
                                        
                                        <?php
                                        $raised = 0;
                                        $total_raised = wpcf_function()->get_total_fund();
                                        if ($total_raised){ $raised = $total_raised; }
                                        ?>
                                        <div class="wpneo-fund-raised">
                                            <div class="wpneo-meta-desc"><?php echo wc_price($raised); ?></div>
                                            <div class="wpneo-meta-name"><?php _e('Fund Raised', 'wp-crowdfunding'); ?></div>
                                        </div>
    
                                    </div>                     
                                </div>
                            </div>
                        </body>
                    </html>
                <?php endforeach; 
                wp_reset_postdata();
            }
            exit();
        }
    }


    // Odrer Data View 
    public function embed_campaign_action(){
        
        $html = '';
        $title = __("Embed Code","wp-crowdfunding");
        $postid = sanitize_text_field($_POST['postid']);
        if( $postid ){
            $html .= '<div>';
            $html .= '<textarea><iframe width="310" height="656" src="'.esc_url( home_url( "/" ) ).'themeumembed/'.$postid.'" frameborder="0" scrolling="no"></iframe></textarea>';
            $html .= '<i>'.__("Copy this code and paste inside your content.","wp-crowdfunding").'</i>';
            $html .= '</div>';
        }
        die(json_encode(array('success'=> 1, 'message' => $html, 'title' => $title )));
    }

    public function single_campaign_social_share() {
        wpcf_function()->template('include/social-share');
    }
}
WPCF_Social_Share::instance();