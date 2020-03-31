<?php
defined( 'ABSPATH' ) || exit;
/**
 * Reward selected by backer
 *
 * This if for campaign owner, at a glance all reward lists selected by backer.
 */


$user_id = get_current_user_id();

$args = array(
	'post_type'  => 'shop_order',
	'post_status'	=> 'wc-completed',
	'meta_query' => array(
		array(
			'key' => 'wpneo_selected_reward',
			'value'   => array('', null),
			'compare' => 'NOT IN'
		),
		array(
			'key' => '_cf_product_author_id',
			'value'   => $user_id,
			'compare' => '='
		)
	)
);
$rewards_query = new WP_Query($args);
?>

<div class="wpneo-content">
	<div class="wpneo-form">
		<div class="wpneo-shadow wpneo-padding25 wpneo-clearfix">
			<?php if ($rewards_query->have_posts()){ ?>
				<div class="wpneo-responsive-table">
					<table class="stripe-table reward_table_dashboard">
						<thead>
							<tr>
								<th><?php _e('Order', 'wp-crowdfunding'); ?></th>
								<th><?php _e('Campaign Name / Reward Amount', 'wp-crowdfunding'); ?></th>
								<th><?php _e('Action', 'wp-crowdfunding'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							while ($rewards_query->have_posts()) {
								$rewards_query->the_post();
								$reward = get_post_meta(get_the_ID(), 'wpneo_selected_reward', true);
								$order = new WC_Order(get_the_ID());
								?>
								<tr>
									<td>#<?php the_ID(); ?></td>
									<td>
										<?php
										foreach ($order->get_items() as $key => $item){
											echo $item['name'];
										}
										if ( ! empty($reward['wpneo_rewards_pladge_amount'])){
											echo ' / '.wc_price($reward['wpneo_rewards_pladge_amount']);
										}
										?>
										<div class="reward_description" style="display: none;">
											<?php
											if ( ! empty($reward['wpneo_rewards_endmonth'])){
												echo '<div><strong>'. __('Estimated Delivery', 'wp-crowdfunding')."</strong><br />";
												$est_delivery = ucfirst($reward['wpneo_rewards_endmonth']).'-'.$reward['wpneo_rewards_endyear'];
												echo date_i18n( 'F, Y', strtotime( '1-'.$est_delivery ) );
											}
											if ( ! empty($reward['wpneo_rewards_description'])){
												echo '<div><strong>'. __('Description', 'wp-crowdfunding')."</strong><br />";
												echo html_entity_decode( $reward['wpneo_rewards_description'] ).'</div>';
											}
											echo '<div><strong>'.__('Backer info', 'wp-crowdfunding').'</strong> <br />';

											if ( wpcf_function()->wc_version() ){
												echo $order->get_billing_first_name().' '.$order->get_billing_last_name().'<br />';
												echo $order->get_billing_email().'<br />';
												echo $order->get_billing_phone().'<br />';
												echo $order->get_billing_address_1().' '.$order->get_billing_address_2().' '
													.$order->get_billing_city().' '
													.$order->get_billing_country();
											}else{
												echo $order->billing_first_name.' '.$order->billing_last_name.'<br />';
												echo $order->billing_email.'<br />';
												echo $order->billing_phone.'<br />';
												echo $order->billing_address_1.' '.$order->billing_address_2.' '.$order->billing_city.' '.$order->billing_country;
											}
											echo '</div>';
											?>
										</div>
									</td>
									<td>
										<a href="javascript:;" class="button"><?php _e('View Details', 'wp-crowdfunding'); ?></a>
									</td>
								</tr> <?php
							}
							wp_reset_postdata(); ?>
						</tbody>
					</table>
				</div> <?php
			} else {
				echo '<div class="wpneocf-alert-info"> '.__('There is no rewards selected by backer', 'wp-crowdfunding').' </div>';
			}
			?>
		</div>
	</div>
</div>
