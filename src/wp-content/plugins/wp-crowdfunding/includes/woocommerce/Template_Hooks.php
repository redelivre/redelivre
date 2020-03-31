<?php
namespace WPCF\woocommerce;

defined( 'ABSPATH' ) || exit;

class Template_Hooks {

    public function __construct() {
		add_action('wpcf_before_single_campaign_summary', 				array($this, 'campaign_single_feature_image'));
		add_action('wpcf_after_feature_img',               				array($this, 'campaign_single_description'));
        
        // Single campaign Template hook
        add_action('wpcf_single_campaign_summary',        				array($this, 'single_campaign_summary'));
        add_filter('wpcf_default_single_campaign_tabs',   				array($this, 'single_campaign_tabs'), 10);
        add_action('wpcf_after_single_campaign_summary',  				array($this, 'campaign_single_tab'));
        //Campaign Story Right Sidebar
        add_action('wpcf_campaign_story_right_sidebar',                	array($this, 'story_right_sidebar'));
        //Listing Loop
		add_action('wpcf_campaign_loop_item_before_content',           	array($this, 'loop_item_thumbnail'));
		add_action('wpcf_campaign_loop_item_content',                  	array($this, 'campaign_loop_item_content'));
        //Dashboard Campaigns
		add_action('wpcf_dashboard_campaign_loop_item_content',        	array($this, 'dashboard_campaign_loop_item_content'));
        add_action('wpcf_dashboard_campaign_loop_item_before_content', 	array($this, 'loop_item_thumbnail'));
        // Filter Search for Crowdfunding campaign
        add_filter('pre_get_posts' ,                                    array($this, 'search_shortcode_filter'));
        add_action('get_the_generator_html',                            array($this, 'tag_generator'), 10, 2 ); // Single Page Html
        add_action('get_the_generator_xhtml',                           array($this, 'tag_generator'), 10, 2 );
        add_action('wp',                                                array($this, 'woocommerce_single_page' ));
    }


    public function woocommerce_single_page(){
        if (is_product()){
            global $post;
            $product = wc_get_product($post->ID);
            if ($product->get_type() == 'crowdfunding'){
                add_action('woocommerce_single_product_summary',        array($this, 'single_fund_raised'), 20);
                add_action('woocommerce_single_product_summary',        array($this, 'loop_item_fund_raised_percent'), 20);
                add_action('woocommerce_single_product_summary',        array($this, 'single_fund_this_campaign_btn'), 20);
                add_action('woocommerce_single_product_summary',        array($this, 'campaign_location'), 20);
                add_action('woocommerce_single_product_summary',        array($this, 'creator_info'), 20);
                add_filter('woocommerce_single_product_image_html',     array($this, 'overwrite_product_feature_image'), 20);
            }
        }
    }


	public function search_shortcode_filter($query){
		if (!empty($_GET['product_type'])) {
			$product_type = $_GET['product_type'];
			if ($product_type == 'croudfunding') {
				if ($query->is_search) {
					$query->set('post_type', 'product');
					$taxquery = array(
						array(
							'taxonomy' => 'product_type',
							'field' => 'slug',
							'terms' => 'crowdfunding',
						)
					);
					if( wpcf_function()->wc_version() ){
						$taxquery['relation'] = 'AND';
						$taxquery[] = array(
							'taxonomy' => 'product_visibility',
							'field'    => 'name',
							'terms'    => 'exclude-from-search',
							'operator' => 'NOT IN',
						);
					}
					$query->set('tax_query', $taxquery);
				}
			}
		}
		return $query;
	}

	public function single_campaign_summary() {
		wpcf_function()->template('include/campaign-title');
		wpcf_function()->template('include/author');
		$this->loop_item_rating();
		$this->single_fund_raised();
		wpcf_function()->template('include/fund_raised_percent');
		$this->single_fund_this_campaign_btn();
		$this->campaign_location();
		$this->creator_info();
	}

	public function campaign_loop_item_content() {
		$this->loop_item_rating();
		$this->loop_item_title();
		$this->loop_item_author();
		$this->loop_item_location();
		wpcf_function()->template('include/loop/description');
		$this->loop_item_fund_raised_percent();
		$this->loop_item_funding_goal();
		$this->loop_item_time_remaining();
		$this->loop_item_fund_raised();
		$this->loop_item_button();
	}

	public function dashboard_campaign_loop_item_content() {
		$this->loop_item_title();
		$this->loop_item_author();
		$this->loop_item_location();
		$this->loop_item_fund_raised_percent();
		$this->loop_item_funding_goal();
		$this->loop_item_time_remaining();
		$this->loop_item_fund_raised();
		$this->loop_item_button();
	}


	public function campaign_location() {
		wpcf_function()->template('include/location');
	}

	public function campaign_single_tab() {
		wpcf_function()->template('include/campaign-tab');
    }
    
	public function campaign_single_feature_image() {
		wpcf_function()->template('include/feature-image');
	}

	public function campaign_single_description() {
		wpcf_function()->template('include/description');
	}

	public function single_fund_raised() {
		wpcf_function()->template('include/fund-raised');
	}

	public function single_fund_this_campaign_btn() {
		wpcf_function()->template('include/fund-campaign-btn');
	}

	public function single_campaign_tabs( $tabs = array() ) {
		global $product, $post;

		// Description tab - shows product content
		if ( $post->post_content ) {
			$tabs['description'] = array(
				'title'     => __( 'Campaign Story', 'wp-crowdfunding' ),
				'priority'  => 10,
				'callback'  => array($this, 'campaign_story_tab')
			);
		}

		$saved_campaign_update = get_post_meta($post->ID, 'wpneo_campaign_updates', true);
		$saved_campaign_update = json_decode($saved_campaign_update, true);
		if (is_array($saved_campaign_update) && count($saved_campaign_update) > 0) {
			$tabs['update'] = array(
				'title'     => __('Updates', 'wp-crowdfunding'),
				'priority'  => 10,
				'callback'  => array($this ,'campaign_update_tab')
			);
		}

		$show_table = get_post_meta($post->ID, 'wpneo_show_contributor_table', true);
		if($show_table == '1') {
			$baker_list = wpcf_function()->get_customers_product();
			if (count($baker_list) > 0) {
				$tabs['baker_list'] = array(
					'title' => __('Backer List', 'wp-crowdfunding'),
					'priority' => 10,
					'callback' => array($this, 'campaign_baker_list_tab')
				);
			}
		}

		// Reviews tab - shows comments
		if ( comments_open() ) {
			$tabs['reviews'] = array(
				'title'    => sprintf( __( 'Reviews (%d)', 'wp-crowdfunding' ), $product->get_review_count() ),
				'priority' => 30,
				'callback' => 'comments_template'
			);
		}

		return $tabs;
	}

	public function campaign_story_tab() {
		wpcf_function()->template('include/tabs/story-tab');
	}

	public function wpneo_crowdfunding_campaign_rewards_tab() {
		wpcf_function()->template('include/tabs/rewards-tab');
	}

	public function campaign_update_tab() {
		wpcf_function()->template('include/tabs/update-tab');
	}

	public function campaign_baker_list_tab() {
		wpcf_function()->template('include/tabs/baker-list-tab');
    }
    
	public function creator_info() {
		wpcf_function()->template('include/creator-info');
	}

	public function overwrite_product_feature_image($img_html) {
		global $post;
		$url = trim(get_post_meta($post->ID, 'wpneo_funding_video', true));
		if ( !empty($url) ) {
			wpcf_function()->get_embeded_video( $url );
		} else {
			return $img_html;
		}
	}

	public function loop_item_thumbnail()  {
		wpcf_function()->template('include/loop/thumbnail');
	}

	public function loop_item_button() {
		wpcf_function()->template('include/loop/details_button');
	}

	public function loop_item_title() {
		wpcf_function()->template('include/loop/title');
	}

	public function loop_item_author() {
		wpcf_function()->template('include/loop/author');
	}

	public function loop_item_rating() {
		wpcf_function()->template('include/loop/rating_html');
	}

	public function loop_item_location() {
		wpcf_function()->template('include/loop/location');
	}

	public function loop_item_funding_goal() {
		wpcf_function()->template('include/loop/funding_goal');
	}

	public function loop_item_fund_raised() {
		wpcf_function()->template('include/loop/fund_raised');
	}

	public function loop_item_fund_raised_percent() {
		wpcf_function()->template('include/loop/fund_raised_percent');
	}

	public function loop_item_time_remaining() {
		wpcf_function()->template('include/loop/time_remaining');
	}

	public function story_right_sidebar() {
		wpcf_function()->template('include/tabs/rewards-sidebar-form');
	}


	public function tag_generator( $gen, $type ) {
		switch ( $type ) {
			case 'html':
				$gen .= "\n" . '<meta name="generator" content="WP Crowdfunding ' . esc_attr( WPCF_VERSION ) . '">';
				break;
			case 'xhtml':
				$gen .= "\n" . '<meta name="generator" content="WP Crowdfunding ' . esc_attr( WPCF_VERSION ) . '" />';
				break;
		}
		return $gen;
	}


}