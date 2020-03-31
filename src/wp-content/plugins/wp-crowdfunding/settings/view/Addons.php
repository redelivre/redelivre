<div class="wrap">
    <div class="wp-crowdfunding-addons-list">
        <h1 class="addon-list-heading"><?php _e('Addons List', 'wp-crowdfunding'); ?></h1>
        <br class="clear">
		<?php
        $addons = apply_filters('wpcf_addons_lists_config', array());

		if (is_array($addons) && count($addons)){
			?>
            <div class="wp-list-table widefat plugin-install">
                <style>
                    .wp-crowdfunding-addons-list .plugin-card-top{
                        min-height: 98px;
                    }
                    .wp-crowdfunding-addons-list .plugin-icon{
                        width: 64px;
                        height: 64px;
                    }
                    .wp-crowdfunding-addons-list .wp-list-table a{
                        color: #fff;
                        margin-top: 100px;
                        padding: 10px 20px;
                        border-radius: 4px;
                        text-decoration: none;
                        background: #0085ba;
                        background-color: #0073aa;
                        border-color: #006a95 #00648c #00648c;
                        box-shadow: inset 0 -1px 0 #00648c;
                        text-shadow: 0 -1px 1px #005d82, 1px 0 1px #005d82, 0 1px 1px #005d82, -1px 0 1px #005d82;
                    }
                    .wp-crowdfunding-addons-list .plugin-card .name,
                    .wp-crowdfunding-addons-list .plugin-card .desc{
                        margin-left: 84px;
                    }

                    .wp-crowdfunding-addons-list .btn-switch {
                        display: inline-block;
                        height: 22px;
                        position: relative;
                        width: 50px;
                    }
                    .wp-crowdfunding-addons-list .btn-switch input {
                        display:none;
                    }
                    .wp-crowdfunding-addons-list .btn-slider {
                        background-color: #ccc;
                        bottom: 0;
                        cursor: pointer;
                        left: 0;
                        position: absolute;
                        right: 0;
                        top: 0;
                        transition: .4s;
                    }
                    .wp-crowdfunding-addons-list .btn-slider:before {
                        background-color: #fff;
                        bottom: 3px;
                        content: "";
                        height: 16px;
                        left: 4px;
                        position: absolute;
                        transition: .4s;
                        width: 16px;
                    }
                    .wp-crowdfunding-addons-list .btn-switch input:checked + .btn-slider {
                        background-color: #66bb6a;
                    }
                    .wp-crowdfunding-addons-list .btn-switch input:checked + .btn-slider:before {
                        transform: translateX(26px);
                    }
                    .wp-crowdfunding-addons-list .btn-slider.btn-round {
                        border-radius: 34px;
                    }
                    .wp-crowdfunding-addons-list .btn-slider.btn-round:before {
                        border-radius: 50%;
                    }
                </style>
                <div id="the-list">
					<?php
					foreach ( $addons as $basName => $addon ) {
						$addonConfig = wpcf_function()->get_addon_config($basName);
                        $isEnable = (bool)wpcf_function()->avalue_dot('is_enable', $addonConfig);

						$thumbnailURL =  WPCF_DIR_URL.'assets/images/wpcf-plugin.png';
						if (file_exists($addon['path'].'assets/images/thumbnail.png') ){
							$thumbnailURL = $addon['url'].'assets/images/thumbnail.png';
                        } elseif (file_exists($addon['path'].'assets/images/thumbnail.svg')){
							$thumbnailURL = $addon['url'].'assets/images/thumbnail.svg';
						}
						?>
                        <div class="plugin-card">
                            <div class="plugin-card-top">
                                <div class="name column-name">
                                    <h3>
										<?php
                                        echo $addon['name']; 
										echo "<img src='{$thumbnailURL}' class='plugin-icon' alt=''>";
										?>
                                    </h3>
                                </div>
                                <div class="action-links">
                                    <ul class="plugin-action-buttons">
                                        <li>
                                            <label class="btn-switch">
                                                <input type="checkbox" class="wpcf_addons_list_item" value="1" name="<?php echo $basName; ?>" <?php checked(true, $isEnable) ?> />
                                                <div class="btn-slider btn-round"></div>
                                            </label>
                                        </li>
                                    </ul>
                                </div>
                                <div class="desc column-description">
                                    <p><?php echo $addon['description']; ?></p>
                                </div>
                            </div>
                        </div>
                    <?php }

                    //PRO ADDONS LIST FOR DISPLAY
                    if( wpcf_function()->is_free() ) {
                        $proAddons = array(
                            '2checkout' => array(
                                'name'          => __( '2Checkout', 'wp-crowdfunding' ),
                                'description'   => __( 'Offer 2Checkout.com payment gateway option for all transactions.', 'wp-crowdfunding'),
                            ),
                            'authorizenet' => array(
                                'name'          => __( 'Authorize.Net', 'wp-crowdfunding' ),
                                'description'   => __( 'Provide Authorize.net payment gateway option for users.', 'wp-crowdfunding' ),
                            ),
                            'email' => array(
                                'name'          => __( 'Email', 'wp-crowdfunding' ),
                                'description'   => __( 'Connect with users through customizable email templates using Email addon.', 'wp-crowdfunding' ),
                            ),
                            'recaptcha' => array(
                                'name'          => __( 'reCAPTCHA', 'wp-crowdfunding' ),
                                'description'   => __( 'Secure your site from bots and other identity threats with reCAPTCHA.', 'wp-crowdfunding' ),
                            ),
                            'reports' => array(
                                'name'          => __( 'Reports', 'wp-crowdfunding' ),
                                'description'   => __( 'Get detailed analytics & stats using advanced filters with powerful reports.', 'wp-crowdfunding' ),
                            ),
                            'stripe-connect' => array(
                                'name'          => __( 'Stripe connect', 'wp-crowdfunding' ),
                                'description'   => __( 'Enable Stripe Connect payment gateways to boost donations of your campaigns.', 'wp-crowdfunding' ),
                            ),
                            'wallet' => array(
                                'name'          => __( 'Wallet', 'wp-crowdfunding' ),
                                'description'   => __( 'Support native payment system for all donations using the native wallet addon.', 'wp-crowdfunding' ),
                            )
                        );

                        foreach ( $proAddons as $basName => $addon ) {
                            $addonConfig = wpcf_function()->get_addon_config($basName);
    
                            $addons_path = trailingslashit(WPCF_DIR_PATH."assets/addons/{$basName}");
                            $addons_url = trailingslashit(WPCF_DIR_URL."assets/addons/{$basName}");
    
                            $thumbnailURL =  WPCF_DIR_URL.'assets/images/wpcf-plugin.png';
    
                            if (file_exists($addons_path.'thumbnail.png') ) {
                                $thumbnailURL = $addons_url.'thumbnail.png';
                            } elseif (file_exists($addons_path.'thumbnail.svg')) {
                                $thumbnailURL = $addons_url.'thumbnail.svg';
                            }
    
                            ?>
                            <div class="plugin-card">
                                <div class="plugin-card-top">
                                    <div class="name column-name">
                                        <h3>
                                            <?php
                                            echo $addon['name'];
                                            echo "<img src='{$thumbnailURL}' class='plugin-icon' alt=''>";
                                            ?>
                                        </h3>
                                    </div>
                                    <div class="action-links">
                                        <ul class="plugin-action-buttons">
                                            <li>
                                                <a href="https://www.themeum.com/product/wp-crowdfunding-plugin/?utm_source=crowdfunding_plugin"
                                                   class="addon-buynow-link" target="_blank"><?php _e('Buy Now','wp-crowdfunding'); ?></a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="desc column-description">
                                        <p><?php echo $addon['description']; ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php
                        }
                    } ?>
                </div>
            </div>

            <br class="clear">
			<?php
		}
		?>
    </div>
</div>