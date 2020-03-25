<?php
/**
 * Feed Making View
 *
 * @link       https://webappick.com/
 * @since      1.0.0
 *
 * @package    Woo_Feed
 * @subpackage Woo_Feed/admin/partial
 * @author     Ohidul Islam <wahid@webappick.com>
 */
if ( ! defined( 'ABSPATH' ) ) {
	die();
}
/** @define "WOO_FEED_FREE_ADMIN_PATH" "./../" */
/**
 * globals
 *
 * @global array $feedRules
 * @global Woo_Feed_Dropdown $wooFeedDropDown
 * @global Woo_Feed_Products $wooFeedProduct
 * @global string $feedName
 * @global int $feedId
 * @global string $provider
 * @global array $wp_meta_boxes
 */
global $feedRules, $wooFeedDropDown, $wooFeedProduct, $feedName, $feedId, $provider, $wp_meta_boxes;
$feedName        = '';
$feedId          = '';
$current_screen  = get_current_screen();
$page            = $current_screen->id;
$wooFeedDropDown = new Woo_Feed_Dropdown();
$wooFeedProduct  = new Woo_Feed_Products();
$wooFeedProduct->load_attributes();
$feedRules = woo_feed_parse_feed_rules( $feedRules );
if ( 'adroll' == $feedRules['provider'] ) {
	$feedRules['provider'] = 'google';
}
register_and_do_woo_feed_meta_boxes( $current_screen, $feedRules );
?>
<div class="wrap wapk-admin" id="Feed">
	<div class="wapk-section">
		<h1 class="wp-heading-inline"><?php _e( 'New WooCommerce Product Feed', 'woo-feed' ); ?></h1>
	</div>
	<div class="wapk-section"><?php WPFFWMessage()->displayMessages(); ?></div>
	<hr class="wp-header-end">
	<div class="wapk-section">
		<form action="" name="feed" id="generateFeed" class="generateFeed add-new" method="post" autocomplete="off">
			<input type="hidden" name="feed_option_name" value="">
			<input type="hidden" name="feed_id" value="">
			<?php
			wp_nonce_field( 'woo_feed_form_nonce' );
			wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
			wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
			?>
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>">
					<div id="post-body-content">
						<?php require_once WOO_FEED_FREE_ADMIN_PATH . 'partials/woo-feed-content-settings.php'; ?>
					</div>
					<div id="postbox-container-1" class="postbox-container">
						<?php do_meta_boxes( get_current_screen(), 'side', $feedRules ); ?>
					</div>
				</div>
				<div class="clear"></div>
				<div id="providerPage"></div>
			</div>
		</form>
	</div>
</div><!-- /wrap -->