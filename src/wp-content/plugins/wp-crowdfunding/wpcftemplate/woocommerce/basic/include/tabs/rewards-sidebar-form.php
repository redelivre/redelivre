<?php
defined( 'ABSPATH' ) || exit;
global $post;
$campaign_rewards   = get_post_meta($post->ID, 'wpneo_reward', true);
$campaign_rewards   = stripslashes($campaign_rewards);
$campaign_rewards_a = json_decode($campaign_rewards, true);
if (is_array($campaign_rewards_a)) {
	if (count($campaign_rewards_a) > 0) {

		$i      = 0;
		$amount = array();

		echo '<h2>'. __('Rewards', 'wp-crowdfunding') .'</h2>';
		foreach ($campaign_rewards_a as $key => $row) {
			$amount[$key] = $row['wpneo_rewards_pladge_amount'];
		}
		array_multisort($amount, SORT_ASC, $campaign_rewards_a);

		foreach ($campaign_rewards_a as $key => $value) {
			$key++;
			$i++;
			$quantity = '';

			$post_id    = get_the_ID();
			$min_data   = $value['wpneo_rewards_pladge_amount'];
			$max_data   = '';
			$orders     = 0;
			( ! empty($campaign_rewards_a[$i]['wpneo_rewards_pladge_amount']))? ( $max_data = $campaign_rewards_a[$i]['wpneo_rewards_pladge_amount'] - 1 ) : ($max_data = 9000000000);
			if( $min_data != '' ){
				$orders = wpcf_campaign_order_number_data( $min_data,$max_data,$post_id );
			}
			if( $value['wpneo_rewards_item_limit'] ){
				$quantity = 0;
				if( $value['wpneo_rewards_item_limit'] >= $orders ){
					$quantity = $value['wpneo_rewards_item_limit'] - $orders;
				}
			}
			?>
            <div class="tab-rewards-wrapper<?php echo ( $quantity === 0 ) ? ' disable' : ''; ?>">
                <div class="wpneo-shadow wpneo-padding15 wpneo-clearfix">
                    <h3>
						<?php
						if (function_exists('wc_price')) {
							echo wc_price($value['wpneo_rewards_pladge_amount']);
							if( 'true' != get_option('wpneo_reward_fixed_price','') ){
								echo ( ! empty($campaign_rewards_a[$i]['wpneo_rewards_pladge_amount']))? ' - '. wc_price($campaign_rewards_a[$i]['wpneo_rewards_pladge_amount'] - 1) : __(" or more", 'wp-crowdfunding');
							}
						}
						?>
                    </h3>
                    <div><?php echo html_entity_decode($value['wpneo_rewards_description']); ?></div>
					<?php if( $value['wpneo_rewards_image_field'] ){ ?>
                        <div class="wpneo-rewards-image">
							<?php echo '<img src="'.wp_get_attachment_url( $value["wpneo_rewards_image_field"] ).'"/>'; ?>
                        </div>
					<?php } ?>

					<?php
					if ( ! empty($value['wpneo_rewards_endmonth']) || ! empty($value['wpneo_rewards_endyear'])){
						$month = date_i18n("F", strtotime($value['wpneo_rewards_endmonth']));
						$year = date_i18n("Y", strtotime($value['wpneo_rewards_endyear'].'-'.$month.'-15'));

						echo "<h4>{$month}, {$year}</h4>";
						echo '<div>'.__('Estimated Delivery', 'wp-crowdfunding').'</div>';
					}
					?>


                    <!-- Campaign Valid -->
					<?php if (wpcf_function()->is_campaign_valid()) { ?>
						<?php if (wpcf_function()->is_campaign_started()) {
							if (get_option('wpneo_single_page_reward_design') == 1) { ?>
                                <div class="overlay">
                                    <div>
                                        <div>
											<?php if( $quantity === 0 ){ ?>
                                                <span class="wpneo-error"><?php _e( 'Reward no longer available.', 'wp-crowdfunding' ); ?></span>
											<?php } else { ?>
                                                <form enctype="multipart/form-data" method="post" class="cart">
                                                    <input type="hidden" value="<?php echo $value['wpneo_rewards_pladge_amount']; ?>" name="wpneo_donate_amount_field" />
                                                    <input type="hidden" value='<?php echo json_encode($value); ?>' name="wpneo_selected_rewards_checkout" />
                                                    <input type="hidden" value="<?php echo $key; ?>" name="wpneo_rewards_index" />
                                                    <input type="hidden" value="<?php echo esc_attr($post->post_author); ?>" name="_cf_product_author_id">
                                                    <input type="hidden" value="<?php echo esc_attr($post->ID); ?>" name="add-to-cart">
                                                    <button type="submit" class="select_rewards_button"><?php _e('Select Reward','wp-crowdfunding'); ?></button>
                                                </form>
											<?php } ?>
                                        </div>
                                    </div>
                                </div>
							<?php } else if (get_option('wpneo_single_page_reward_design') == 2){ ?>
                                <div class="tab-rewards-submit-form-style1">
									<?php if( $quantity === 0 ): ?>
                                        <span class="wpneo-error"><?php _e( 'Reward no longer available.', 'wp-crowdfunding' ); ?></span>
									<?php else: ?>
                                        <form enctype="multipart/form-data" method="post" class="cart">
                                            <input type="hidden" value="<?php echo $value['wpneo_rewards_pladge_amount']; ?>" name="wpneo_donate_amount_field" />
                                            <input type="hidden" value='<?php echo json_encode($value); ?>' name="wpneo_selected_rewards_checkout" />
                                            <input type="hidden" value="<?php echo $key; ?>" name="wpneo_rewards_index" />
                                            <input type="hidden" value="<?php echo esc_attr($post->post_author); ?>" name="_cf_product_author_id">
                                            <input type="hidden" value="<?php echo esc_attr($post->ID); ?>" name="add-to-cart">
                                            <button type="submit" class="select_rewards_button"><?php _e('Select Reward','wp-crowdfunding'); ?></button>
                                        </form>
									<?php endif; ?>
                                </div>
							<?php } ?>
						<?php } ?>
					<?php } else { ?>
                        <div class="overlay until-date">
                            <div>
                                <div>
                                    <span class="info-text">
										<?php if( wpcf_function()->is_reach_target_goal() ){ ?>
											<?php _e('Campaign already completed.', 'wp-crowdfunding'); ?>
										<?php }else{ ?>
											<?php if (wpcf_function()->is_campaign_started()){ ?>
												<?php _e('Reward is not valid.', 'wp-crowdfunding'); ?>
											<?php }else{ ?>
												<?php _e('Campaign is not started.', 'wp-crowdfunding'); ?>
											<?php } ?>
										<?php } ?>
									</span>
                                </div>
                            </div>
                        </div>
					<?php } ?>
                    <!-- Campaign Over -->

					<?php
					if( $min_data != '' ){
						echo '<div>'.$orders.' '.__('backers','wp-crowdfunding').'</div>';
					} ?>
					<?php if( $value['wpneo_rewards_item_limit'] ){ ?>
                        <div>
							<?php
							echo $quantity;
							_e(' rewards left','wp-crowdfunding');
							?>
                        </div>
					<?php } ?>

                </div>
            </div>
			<?php
		}
	}
}
?>
<div style="clear: both"></div>