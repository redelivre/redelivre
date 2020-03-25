<?php
/**
 * Premium vs Free version
 *
 * @link       https://webappick.com/
 * @since      1.0.0
 *
 * @package    Woo_Feed
 * @subpackage Woo_Feed/admin/partial
 * @author     Ohidul Islam <wahid@webappick.com>
 * @version    1.0.2
 */
if ( ! function_exists( 'add_action' ) ) die();
// ### REF > utm parameters http://bit.ly/2KIwvTt
$features = array(
	array(
		'thumb'       => esc_url( WOO_FEED_PLUGIN_URL ) . 'admin/images/features/smart-filter-conditions.svg',
		'title'       => 'Smart Filter & Conditions',
		'description' => 'Exclude unwanted product from feed with the help of advanced filtration',
	),
	array(
		'thumb'       => esc_url( WOO_FEED_PLUGIN_URL ) . 'admin/images/features/dynamic-attributes.svg',
		'title'       => 'Dynamic Attributes',
		'description' => 'Make new attribute by combine multiple attributes with condition',
	),
	array(
		'thumb'       => esc_url( WOO_FEED_PLUGIN_URL ) . 'admin/images/features/category-mapping.svg',
		'title'       => 'Category Mapping',
		'description' => 'Automatically map product category  with merchant category',
	),
	array(
		'thumb'       => esc_url( WOO_FEED_PLUGIN_URL ) . 'admin/images/features/multilingual-feed.svg',
		'title'       => 'Multilingual Feed',
		'description' => 'WPML Support for Multilingual Product Feed',
	),
	array(
		'thumb'       => esc_url( WOO_FEED_PLUGIN_URL ) . 'admin/images/features/custom-taxonomy.svg',
		'title'       => 'Custom Taxonomy',
		'description' => 'Use any taxonomies attached to the product',
	),
	array(
		'thumb'       => esc_url( WOO_FEED_PLUGIN_URL ) . 'admin/images/features/product-title-customization.svg',
		'title'       => 'Product Title Customization',
		'description' => 'Customize product title with different attributes',
	),
	array(
		'thumb'       => esc_url( WOO_FEED_PLUGIN_URL ) . 'admin/images/features/conditional-pricing.svg',
		'title'       => 'Conditional Pricing',
		'description' => 'Change Product Price display as per your need',
	),
	array(
		'thumb'       => esc_url( WOO_FEED_PLUGIN_URL ) . 'admin/images/features/output-customization.svg',
		'title'       => 'Output Customization',
		'description' => 'Advanced Commands to customize every thing',
	),
	array(
		'thumb'       => esc_url( WOO_FEED_PLUGIN_URL ) . 'admin/images/features/tax-calculation.svg',
		'title'       => 'Tax Calculation',
		'description' => 'Include tax field or include tax with price as per your need',
	),
	array(
		'thumb'       => esc_url( WOO_FEED_PLUGIN_URL ) . 'admin/images/features/3rd-party-plugin-support.svg',
		'title'       => '3<sup>rd</sup> Party Plugin Support',
		'description' => 'Supports WooCommerce addons and <a href="https://webappick.com/plugin/woocommerce-product-feed-pro/" target="_blank">popular plugin</a>',
	),
	array(
		'thumb'       => esc_url( WOO_FEED_PLUGIN_URL ) . 'admin/images/features/customer-support.svg',
		'title'       => 'Premium customer support',
		'description' => 'Priority support to ensure error free feed generation',
	),
);

$pricingFeatures = array(
	__( 'Unlimited Products', 'woo-feed' ),
	__( 'Support for 1 Year', 'woo-feed' ),
	__( 'Updates for 1 Year', 'woo-feed' ),
	__( 'Support 100+ Merchants', 'woo-feed' ),
	__( 'Advanced Commands', 'woo-feed' ),
	__( 'Dynamic Attribute', 'woo-feed' ),
	__( 'Extended Product Title', 'woo-feed' ),
	__( 'Custom taxonomy', 'woo-feed' ),
	__( 'WP Options', 'woo-feed' ),
	__( 'Product/Post Meta', 'woo-feed' ),
	__( 'Multilingual Support', 'woo-feed' ),
	__( 'Multi-vendor Support', 'woo-feed' ),
	__( 'Smart Filter & Conditions', 'woo-feed' ),
	__( '3<sup>rd</sup> Party Plugin Supports', 'woo-feed'),
);
$pricing = array(
	array(
		'title'          => __( 'Personal', 'woo-feed' ),
		'currency'       => '$',
		'amount'         => 119,
		'period'         => __( 'Yearly', 'woo-feed' ),
		'allowed_domain' => 1,
		'featured'       => __( 'Popular', 'woo-feed' ),
		'cart_url'       => 'https://webappick.com/plugin/woocommerce-product-feed-pro/?add-to-cart=45657&variation_id=45660&attribute_pa_license=single-site-119-usd',
	),
	array(
		'title'          => __( 'Plus', 'woo-feed' ),
		'currency'       => '$',
		'amount'         => 199,
		'period'         => __( 'Yearly', 'woo-feed' ),
		'allowed_domain' => 2,
		'featured'       => null,
		'cart_url'       => 'https://webappick.com/plugin/woocommerce-product-feed-pro/?add-to-cart=45657&variation_id=45659&attribute_pa_license=two-site-199-usd',
	),
	array(
		'title'          => __( 'Expert', 'woo-feed' ),
		'currency'       => '$',
		'amount'         => 299,
		'period'         => __( 'Yearly', 'woo-feed' ),
		'allowed_domain' => 5,
		'featured'       => null,
		'cart_url'       => 'https://webappick.com/plugin/woocommerce-product-feed-pro/?add-to-cart=45657&variation_id=45658&attribute_pa_license=five-site-229-usd',
	),
);
$allowedHtml = array(
	'br'   => array(),
	'code' => array(),
	'sub'  => array(),
	'sup'  => array(),
	'span' => array(),
	'a'    => array(
		'href'   => array(),
		'target' => array(),
	),
);
ob_start();
foreach ( $pricingFeatures as $feature ) { ?>
<li class="item">
	<span class="wapk-price__table__feature">
		<span class="dashicons dashicons-yes" aria-hidden="true"></span>
		<span><?php echo wp_kses( $feature, $allowedHtml ); ?></span>
	</span>
</li>
<?php }
$pricingFeatures = ob_get_clean();
$compareTable = array(
	array(
		'title' => __( 'Export Product Variations', 'woo-feed' ),
		'free'  => true,
	),
	array(
		'title' => __( 'Custom Feed Template', 'woo-feed' ),
		'free'  => true,
	),
	array(
		'title' => __( 'Support All Comparison Shopping Engines', 'woo-feed' ),
		'free'  => true,
	),
	array(
		'title' => __( 'Support All Affiliate Networks', 'woo-feed' ),
		'free'  => true,
	),
	array(
		'title' => __( 'Auto Feed Update', 'woo-feed' ),
		'free'  => true,
	),
	array(
		'title' => __( 'Product Attributes', 'woo-feed' ),
		'free'  => true,
	),
	array(
		'title' => __( 'Unlimited Feed', 'woo-feed' ),
		'free'  => true,
	),
	array(
		'title' => __( 'CSV, TXT and XML Feed', 'woo-feed' ),
		'free'  => true,
	),
	array(
		'title' => __( 'Pre Configured Feed Template', 'woo-feed' ),
		'free'  => true,
	),
	array(
		'title' => __( 'Variations & Custom Attribute Value', 'woo-feed' ),
		'free'  => true,
	),
	array(
		'title' => __( 'Upload Feed via FTP', 'woo-feed' ),
		'free'  => true,
	),
	array(
		'title' => __( 'Unlimited Products', 'woo-feed' ),
		'free'  => false,
	),
	array(
		'title' => __( 'Product Filtering by Id,SKU, Title, Category and Others Attributes.', 'woo-feed' ),
		'free'  => false,
	),
	array(
		'title' => __( 'Feed By Category', 'woo-feed' ),
		'free'  => false,
	),
	array(
		'title' => __( 'Customized Product Title', 'woo-feed' ),
		'free'  => false,
	),
	array(
		'title' => __( 'Multilingual Feed With WPML', 'woo-feed' ),
		'free'  => false,
	),
	array(
		'title' => __( 'Product Taxonomy value like Brand or Others Plugin data', 'woo-feed' ),
		'free'  => false,
	),
	array(
		'title' => __( 'WooCommerce Composite Products', 'woo-feed' ),
		'free'  => false,
	),
	array(
		'title' => __( 'WooCommerce Bundle Products', 'woo-feed' ),
		'free'  => false,
	),
	array(
		'title' => __( 'Remove Variation Products', 'woo-feed' ),
		'free'  => false,
	),
	array(
		'title' => __( 'Remove Parent Products', 'woo-feed' ),
		'free'  => false,
	),
	array(
		'title' => __( 'Category Mapping', 'woo-feed' ),
		'free'  => false,
	),
	array(
		'title' => __( 'Dynamic Attributes', 'woo-feed' ),
		'free'  => false,
	),
	array(
		'title' => __( 'Price With Tax', 'woo-feed' ),
		'free'  => false,
	),
	array(
		'title' => __( 'Conditional Pricing', 'woo-feed' ),
		'free'  => false,
	),
	array(
		'title' => __( 'WP Post Meta Value', 'woo-feed' ),
		'free'  => false,
	),
	array(
		'title' => __( 'WP Options Value', 'woo-feed' ),
		'free'  => false,
	),
	array(
		'title' => __( 'Output Customization', 'woo-feed' ),
		'free'  => false,
	),
);
$compareTableFreeFeatures = $compareTableProFeatures = '';
foreach ( $compareTable as $feature ) {
	$compareTableFreeFeatures .= sprintf( '<li class="%s"><span class="dashicons dashicons-%s" aria-hidden="true"></span><span>%s</span></li>', $feature['free'] ? 'available' : 'unavailable', $feature['free'] ? 'yes' : 'no', wp_kses( $feature['title'], $allowedHtml ) );
	$compareTableProFeatures  .= sprintf( '<li class="available"><span class="dashicons dashicons-yes" aria-hidden="true"></span><span>%s</span></li>', wp_kses( $feature['title'], $allowedHtml ) );
}
$testimonials = array(
	array(
		'comment' => 'We\'ve been using the WooCommerce Product Feed Pro for several months. It really helps us to build good feeds and boost our sale.',
		'name'    => 'Miraclewaresocial',
		'meta'    => '',
		'avatar'  => '',
	),
	array(
		'comment' => 'Using the premium version since few months. Tried many different feeds for WooCommerce, but this one is the best by far! Use it for Bonanza, eBid, eCrater, and others. All work perfectly. I highly recommend.',
		'name'    => 'Sandeep',
		'meta'    => '',
		'avatar'  => '',
	),
	array(
		'comment' => 'Easy and powerful<br>Only in woocommerce product feed pro i finding solution to works with vendor shops.<br>Friendly support. Original service.',
		'name'    => 'Ireneusz A.',
		'meta'    => '',
		'avatar'  => '',
	),
	array(
		'comment' => 'In my multilingual and multicurrency shop I needed really flexible feed generator. I tried couple of them but none of them was able to deal with WPML and WooCommerce Multilingual in 100%.',
		'name'    => 'Ireneusz A.',
		'meta'    => '',
		'avatar'  => '',
	),
	array(
		'comment' => 'Works perfectly as stated<br>Works perfectly as stated. Very easy to use, very straightforward. What is complex is using it on the many platforms but this plugin does the job.',
		'name'    => 'Alex',
		'meta'    => '',
		'avatar'  => '',
	),
	array(
		'comment' => 'Very good plugin that has worked perfectly for the feeds I needed. Some feeds are very specific and Webappick have been great at helping me with existing and non-existing functions. Nice, professional and quick support. Great work!',
		'name'    => 'Anna',
		'meta'    => '',
		'avatar'  => '',
	),
	array(
		'comment' => 'This is one of the most useful plugin I use for all my clients. WebAppick Support is always so willing to go the extra mile to help me get my clients up and going. Thanks WebAppick!',
		'name'    => 'Mathias',
		'meta'    => '',
		'avatar'  => '',
	),
	
	array(
		'comment' => 'WooCommerce Product Feed Pro is the most powerful and flexible feed generation plugin we have seen. We spent many hours researching Feed generation plugin for WooCommerce and quickly found WebAppick\'s WooCommerce Product Feed Pro to be the best fit for our business.',
		'name'    => 'Anfrage',
		'meta'    => '',
		'avatar'  => '',
	),
	array(
		'comment' => 'The paid version is worth the purchase. This plugin can handle all of my product feeds, complete with category filtering, etc. It\'s easy to start with a preset template and then tweak it from there. The initial purchase had me worried. My credit card was processed but I didn\'۪t get my key. Support fixed this pretty quickly. ',
		'name'    => 'Les-imprimeurs',
		'meta'    => '',
		'avatar'  => '',
	),
);
?>
<div class="wrap wapk-admin wapk-feed-pro-upgrade">
	<div class="wapk-section wapk-feed-banner">
		<div class="wapk-banner">
			<a href="http://bit.ly/2KIwvTt" target="_blank">
				<img class="wapk-banner__graphics" src="<?php echo esc_url( WOO_FEED_PLUGIN_URL ); ?>admin/images/woo-feed-pro-banner.png" alt="<?php esc_attr_e( 'Upgrade to WooFeed Pro to unlock more powerful features.', 'woo-feed' ); ?>">
			</a>
		</div>
	</div>
	<div class="clear"></div>
	<div class="wapk-section feed-features">
		<div class="section-title">
			<h2><?php esc_html_e( 'Why Upgrade', 'woo-feed' ); ?></h2>
			<span class="section-sub-title"><?php esc_html_e( 'Super charge your Store with awesome features', 'woo-feed' ); ?></span>
		</div>
		<div class="feed-feature__list">
			<?php foreach ( $features as $feature ) { ?>
			<div class="feed-feature__item">
				<div class="feed-feature__thumb">
					<img src="<?php echo esc_url( $feature['thumb'] ); ?>" alt="<?php echo esc_attr( $feature['title'] ); ?>" title="<?php echo esc_attr( $feature['title'] ); ?>">
				</div>
				<div class="feed-feature__description">
					<h3><?php echo wp_kses( $feature['title'], $allowedHtml ); ?></h3>
					<p><?php echo wp_kses( $feature['description'], $allowedHtml ); ?></p>
				</div>
			</div>
			<?php } ?>
		</div>
		<div class="feed-features__more">
			<a class="wapk-button wapk-button-primary wapk-button-hero" href="https://webappick.com/plugin/woocommerce-product-feed-pro/" target="_blank"><?php esc_html_e( 'See All Features', 'woo-feed' ); ?> <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 17.5 12.5" xml:space="preserve"><path d="M10.6,1.5c-0.4-0.4-0.4-0.9,0-1.3c0.4-0.3,0.9-0.3,1.3,0l5.3,5.3c0.2,0.2,0.3,0.4,0.3,0.7s-0.1,0.5-0.3,0.7 l-5.3,5.3c-0.4,0.4-0.9,0.4-1.3,0c-0.4-0.4-0.4-0.9,0-1.3l3.8-3.8H0.9C0.4,7.1,0,6.7,0,6.2s0.4-0.9,0.9-0.9h13.5L10.6,1.5z M10.6,1.5"></path></svg></a>
		</div>
	</div>
	<div class="clear"></div>
	<div class="wapk-section feed-pro-comparison">
		<div class="section-title">
			<h2 id="comparison-header"><?php printf( '%s <span>%s</span> %s', esc_html__( 'Free', 'woo-feed' ), esc_html__( 'vs', 'woo-feed' ), esc_html__( 'Pro', 'woo-feed' ) ); ?></h2>
			<span class="section-sub-title" id="comparison-sub-header"><?php esc_html_e( 'Find the plan that suits best for you business', 'woo-feed' ); ?></span>
		</div>
		<div class="comparison-table">
			<div class="comparison free">
				<div class="product-header">
					<img src="<?php echo esc_url( WOO_FEED_PLUGIN_URL ); ?>admin/images/woo-feed-lite.svg" alt="<?php esc_attr_e( 'WooFeed Lite', 'woo-feed' ); ?>">
				</div>
				<ul class="product-features">
					<?php
					// Data already escaped.
					echo $compareTableFreeFeatures; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
				</ul>
			</div>
			<div class="comparison pro">
				<div class="product-header">
					<img src="<?php echo esc_url( WOO_FEED_PLUGIN_URL ); ?>admin/images/woo-feed-pro.svg" alt="<?php esc_attr_e( 'WooFeed Pro', 'woo-feed' ); ?>">
				</div>
				<ul class="product-features">
					<?php
					// Data already escaped.
					echo $compareTableProFeatures; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
				</ul>
			</div>
		</div>
	</div>
	<div class="clear"></div>
	<div class="wapk-section feed-pricing">
		<div class="section-title">
			<h2 id="pricing_header"><?php esc_html_e( 'Take Your Products To The Next Level', 'woo-feed' ); ?></h2>
			<span class="section-sub-title" id="pricing_sub_header"><?php esc_html_e( 'Choose your subscription plan and get started', 'woo-feed' ); ?></span>
		</div>
		<div class="wapk-pricing__table">
			<?php foreach ( $pricing as $price ) {
				$integer = $decimal = 0;
				if ( false !== strpos( $price['amount'], '.' ) ) list( $integer, $decimal ) = array_map( 'intval', explode( '.', (string) $price['amount'] ) );
				else $integer = $price['amount'];
			?>
			<div class="wapk-pricing__table__item">
				<div class="wapk-price__table__wrapper">
					<div class="wapk-price__table" role="table" aria-labelledby="pricing_header" aria-describedby="pricing_sub_header">
						<div class="wapk-price__table__header">
							<h3 class="wapk-price__table__heading"><?php echo esc_html( $price['title'] ); ?></h3>
						</div>
						<div class="wapk-price__table__price">
							<?php if ( $integer > 0 || $decimal > 0 ) { ?>
								<span class="wapk-price__table__currency"><?php echo esc_html( $price['currency'] ); ?></span>
							<?php } ?>
							<span class="wapk-price__table__amount"><?php
								if ( 0 == $integer && 0 == $decimal ) printf( '<span class="free">%s</span>', esc_html_x( 'Free', 'Free Package Price Display', 'woo-feed' ) );
								if ( $integer > 0 || $decimal > 0 ) printf( '<span class="integer-part">%d</span>', esc_html( $integer ) );
								if ( $decimal > 0 ) printf( '<span class="decimal-part">.%d</span>', esc_html( $decimal ) );
								if ( ! empty( $price['period'] ) ) printf( '<span class="period">/%s</span>', esc_html( $price['period'] ) );
								?></span>
							<?php if ( ! empty( $price['allowed_domain'] ) ) {
								if ( is_numeric( $price['allowed_domain'] ) ) {
									$allowed = sprintf(
										/* translators: %d: number of allowed domain. */
										_n( 'For %d Site', 'For %d Sites', $price['allowed_domain'], 'woo-feed' ),
										$price['allowed_domain']
									);
								} else $allowed = $price['allowed_domain'];
								printf( '<span class="wapk-price__table__amount___legend">%s</span>', esc_html( $allowed ) );
							} ?>
						</div>
						<?php printf( '<ul class="wapk-price__table__features">%s</ul>', $pricingFeatures ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<div class="wapk-price__table__footer">
							<a href="<?php echo esc_url( $price['cart_url'] . '&utm_source=freePlugin&utm_medium=go_premium&utm_campaign=free_to_pro&utm_term=wooFeed' ); ?>" class="wapk-button wapk-button-primary wapk-button-hero" target="_blank"><?php esc_html_e( 'Buy Now', 'woo-feed' ); ?></a>
						</div>
					</div>
					<?php if ( ! empty( $price['featured'] ) ) { ?>
						<div class="wapk-price__table__ribbon">
							<div class="wapk-price__table__ribbon__inner"><?php echo esc_html( $price['featured'] ); ?></div>
						</div>
					<?php } ?>
				</div>
			</div>
			<?php } ?>
		</div>
	</div>
	<div class="clear"></div>
	<div class="wapk-section wapk-payment">
		<div class="payment-guarantee">
			<div class="guarantee-seal">
				<img src="<?php echo esc_url( WOO_FEED_PLUGIN_URL ); ?>admin/images/14-days-money-back-guarantee.svg" alt="<?php esc_html_e( '14 Days Money Back Guarantee', 'woo-feed' ); ?>">
			</div>
			<div class="guarantee-detail">
				<h2><?php esc_html_e( '14 Days Money Back Guarantee', 'woo-feed' ); ?></h2>
				<p><?php esc_html_e( 'After successful purchase, you will be eligible for conditional refund', 'woo-feed' ); ?></p>
				<a href="https://webappick.com/terms-and-conditions/" target="_blank"><span class="dashicons dashicons-visibility" aria-hidden="true"></span> <?php esc_html_e( 'Terms &amp; Condition Applied', 'woo-feed' ); ?></a>
			</div>
		</div>
		<div class="payment-options">
			<h3><?php esc_html_e( 'Payment Options:', 'woo-feed' ); ?></h3>
			<div class="options">
				<h4><?php esc_attr_e( 'Credit Cards (Stripe)', 'woo-feed' ); ?></h4>
				<ul>
					<li><img src="<?php echo esc_url( WOO_FEED_PLUGIN_URL ); ?>admin/images/payment-options/visa.svg" alt="<?php esc_attr_e( 'Visa', 'woo-feed' ); ?>"></li>
					<li><img src="<?php echo esc_url( WOO_FEED_PLUGIN_URL ); ?>admin/images/payment-options/amex.svg" alt="<?php esc_attr_e( 'American Express', 'woo-feed' ); ?>"></li>
					<li><img src="<?php echo esc_url( WOO_FEED_PLUGIN_URL ); ?>admin/images/payment-options/mastercard.svg" alt="<?php esc_attr_e( 'Mastercard', 'woo-feed' ); ?>"></li>
					<li><img src="<?php echo esc_url( WOO_FEED_PLUGIN_URL ); ?>admin/images/payment-options/discover.svg" alt="<?php esc_attr_e( 'Discover', 'woo-feed' ); ?>"></li>
					<li><img src="<?php echo esc_url( WOO_FEED_PLUGIN_URL ); ?>admin/images/payment-options/jcb.svg" alt="<?php esc_attr_e( 'JCB', 'woo-feed' ); ?>"></li>
					<li><img src="<?php echo esc_url( WOO_FEED_PLUGIN_URL ); ?>admin/images/payment-options/diners.svg" alt="<?php esc_attr_e( 'Diners', 'woo-feed' ); ?>"></li>
				</ul>
			</div>
		</div>
	</div>
	<div class="clear"></div>
	<div class="wapk-section wapk-testimonial">
		<div class="section-title">
			<h2><?php esc_html_e( 'Our Happy Customer', 'woo-feed' ); ?></h2>
			<span class="section-sub-title"><?php esc_html_e( 'Join the squad today!', 'woo-feed' ); ?></span>
		</div>
		<div class="wapk-testimonial-wrapper">
			<div class="wapk-slider">
				<?php foreach ( $testimonials as $testimonial ) { ?>
				<div class="testimonial-item">
					<div class="testimonial-item__comment">
						<p><?php echo wp_kses( $testimonial['comment'], $allowedHtml ); ?></p>
					</div>
					<div class="testimonial-item__user">
						<?php /*<div class="avatar">
							<img src="<?php echo esc_url( $testimonial['avatar'] ); ?>" alt="<?php echo esc_attr( $testimonial['name'] ); ?>">
						</div>*/ ?>
						<h4 class="author-name"><?php echo esc_html( $testimonial['name'] ); ?></h4>
						<?php if ( isset( $testimonial['meta'] ) && ! empty( $testimonial['meta'] ) ) { ?>
						<span class="author-meta"><?php echo esc_html( $testimonial['meta'] ); ?></span>
						<?php } ?>
					</div>
				</div>
				<?php } ?>
			</div>
		</div>
	</div>
	<div class="clear"></div>
	<div class="wapk-section wapk-feed-cta">
		<div class="wapk-cta">
			<div class="wapk-cta-icon">
				<span class="dashicons dashicons-editor-help" aria-hidden="true"></span>
			</div>
			<div class="wapk-cta-content">
				<h2><?php _e( 'Still need help?', 'woo-feed' ); ?></h2>
				<p><?php _e( 'Have we not answered your question?<br>Don\'t worry, you can contact us for more information...', 'woo-feed') ?></p>
			</div>
			<div class="wapk-cta-action">
				<a href="https://wordpress.org/support/plugin/webappick-product-feed-for-woocommerce/#new-topic-0" class="wapk-button wapk-button-primary" target="_blank"><?php _e( 'Get Support', 'woo-feed' ); ?></a>
			</div>
		</div>
	</div>
</div>