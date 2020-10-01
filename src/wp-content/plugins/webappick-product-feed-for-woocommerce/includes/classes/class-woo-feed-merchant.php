<?php /** @noinspection PhpUnused */

/**
 * Created by PhpStorm.
 * User: wahid
 * Date: 12/9/19
 * Time: 12:35 PM
 */
/** @define "WOO_FEED_FREE_PATH" "./../../" */ // phpcs:ignore

/**
 * Class Woo_Feed_Merchant
 */
class Woo_Feed_Merchant {

	/**
	 * Brand Name that going to be used as default pattern for the template.
	 *
	 * @var string
	 * @since 3.3.10
	 */
	public $brand_pattern;
	/**
	 * Store's default Currency
	 *
	 * @var string
	 * @since 3.3.10
	 */
	public $currency;
	/**
	 * List of merchant
	 * @var array
	 * @since 3.3.11
	 */
	protected $merchants;
	/**
	 * Merchant Templates
	 *
	 * @var array
	 */
	protected $templates;
	/**
	 * Merchant Infos
	 *
	 * @var array
	 */
	protected $merchant_infos;
	/**
	 * Current Merchant
	 *
	 * @var string
	 */
	protected $merchant;
	/**
	 * Current Template
	 *
	 * @var array
	 */
	protected $template_raw;
	/**
	 * Parsed Template (Rules) For rendering create form
	 *
	 * @var array
	 */
	protected $template;
	/**
	 * Allowed Feed Type for current Merchant
	 *
	 * @var array
	 */
	protected $feed_types;
	/**
	 * Current Merchant Info
	 *
	 * @var array
	 */
	protected $info;
	/**
	 * is default template.
	 * Flag that indicates if merchant template not exists and fallback to default template.
	 *
	 * @var bool
	 */
	protected $is_default_template = false;

	/**
	 * Woo_Feed_Merchant constructor.
	 *
	 * @param string $merchant      merchant slug
	 *                              if merchant not found default template params will be loaded.
	 * @param string $currency      currency code.
	 * @param string $brand_pattern brand name (pattern).
	 *
	 * @return void
	 *
	 * @see    Woo_Feed_Merchant::load_merchant_templates
	 * @since  3.3.10 $this->currency parameter
	 * @since  3.3.10 $brand_pattern
	 */
	public function __construct( $merchant = null, $currency = null, $brand_pattern = null ) {
		$this->merchant = $merchant;
		if ( ! empty( $currency ) ) {
			$this->currency = $currency;
		} else {
			$this->currency = get_woocommerce_currency();
		}
		if ( ! empty( $brand_pattern ) ) {
			$this->brand_pattern = $brand_pattern;
		} else {
			$this->brand_pattern = woo_feed_get_default_brand();
		}

		$this->load_merchant_infos();
		$this->load_merchant_templates();
		$this->get_template_raw();
		$this->merchantInfo();
	}

	/**
	 * Sets Merchant Info
	 *
	 * Follow this common format to add new merchant info
	 * [
	 *      'link'           => '',         # Merchant's feed specification url
	 *      'video'          => '',         # Video tutorial to make feed for this merchant
	 *      'doc'            => array(      # Plugin documentation for this merchant
	 *          esc_html__( 'link label 1', 'woo-feed' ) => 'https://link1',
	 *          esc_html__( 'link label 2', 'woo-feed' ) => 'https://link2',
	 *      ),
	 *      'feed_file_type' => array( 'XML', 'CSV', 'TXT' ),    # Feed file type (XML or CSV or TXT). Can be multiple.
	 * ];
	 *
	 * @return void
	 * @since 3.3.10
	 */
	protected function load_merchant_infos() {
		$this->merchant_infos = include WOO_FEED_FREE_PATH . 'includes/feeds/merchant_infos.php';
	}

	/**
	 * Sets Merchant Templates
	 *
	 * @return void
	 */
	protected function load_merchant_templates() {
		$this->templates = include WOO_FEED_FREE_PATH . 'includes/feeds/merchant_templates.php';

		// reduce duplicate data.
		//$this->templates['facebook']   = $this->templates['google'];
		$this->templates['pinterest']  = $this->templates['google'];
		$this->templates['adroll']     = $this->templates['google'];
		$this->templates['smartly.io'] = $this->templates['google'];
		$this->templates['connexity'] = $this->templates['become'];
		$this->templates['shopzilla'] = $this->templates['become'];
		$this->templates['fruugo.au'] = $this->templates['fruugo'];
		$this->templates['shopalike.fr'] = $this->templates['kijiji.ca'];
	}

	public function get_template_raw() {
		if ( is_array( $this->template_raw ) ) {
			return $this->template_raw;
		}

		if ( ! is_null( $this->merchant ) && array_key_exists( $this->merchant, $this->templates ) ) {
			$this->template_raw = $this->templates[ $this->merchant ];
		} else {
			$this->is_default_template = true;

			$this->template_raw = $this->templates['default'];
		}

		return $this->template_raw;
	}

	/**
	 * Set Merchant info array
	 * @return void
	 */
	protected function merchantInfo() {

		if ( ! is_null( $this->merchant ) && array_key_exists( $this->merchant, $this->merchant_infos ) ) {
			$this->info = $this->merchant_infos[ $this->merchant ];
		} else {
			$this->info = $this->merchant_infos['default'];
		}

		// ensure common data structure
		$this->info = wp_parse_args(
			$this->info,
			[
				'link'           => '',
				'video'          => '',
				'feed_file_type' => [],
				'doc'            => [],
			]
		);

		$this->feed_types = ( ! empty( $this->info['feed_file_type'] ) ) ? $this->info['feed_file_type'] : [ 'XML', 'CSV', 'TXT' ];

		/**
		 * Filter single merchant data before retrieve
		 *
		 * @param array  $feed_types
		 * @param string $merchant
		 */
		$this->feed_types = apply_filters( 'woo_feed_get_feed_types', $this->feed_types, $this->merchant );

		$this->info['feed_file_type'] = $this->feed_types;

		/**
		 * Filter single merchant data before retrieve
		 *
		 * @param array  $info
		 * @param string $merchant
		 */
		$this->info = apply_filters( 'woo_feed_get_merchant_info', $this->info, $this->merchant );
	}

	/**
	 * Get Merchant Template
	 *
	 * @return bool|array
	 * @deprecated 3.3.7
	 */
	public function get_merchant_template() {
		if ( ! is_null( $this->merchant ) && array_key_exists( $this->merchant, $this->templates ) ) {
			return $this->templates[ $this->merchant ];
		}

		return false;
	}

	/**
	 * Get Default template (blank template with some attribute for creating a base config).
	 *
	 * @return array
	 * @deprecated 3.3.7
	 */
	public function get_default_template() {
		return $this->templates['default'];
	}

	/**
	 * Get Merchant template with fallback to default template.
	 *
	 * @return array
	 */
	public function get_template() {
		if ( is_array( $this->template ) ) {
			return $this->template;
		}
		$this->template = array_merge( $this->template_raw,
			[
				'provider' => $this->merchant,
				'feedType' => $this->get_feed_types( true ),
			] );
		$this->template = woo_feed_parse_feed_rules( $this->template, 'create' );

		return $this->template;
	}

	/**
	 * Get Feed Types
	 *
	 * @param bool $default get the default type
	 *
	 * @return string[]|string|false
	 */
	public function get_feed_types( $default = false ) {

		if ( false === $default ) {
			return $this->feed_types;
		}

		return reset( $this->feed_types );
	}

	/**
	 * Get Selected Merchant Name (slug)
	 *
	 * @return string|null
	 */
	public function get_name() {
		return $this->merchant;
	}

	/**
	 * check if fallback to default template
	 *
	 * @return bool
	 */
	public function is_default_template() {
		return $this->is_default_template;
	}

	/**
	 * Get Merchant Info
	 *
	 * @return array
	 */
	public function get_info() {
		return $this->info;
	}

	/**
	 * Custom Template List
	 * @return array
	 */
	protected function custom_merchants() {
		return array(
			'--1'    => esc_html__( 'Custom Template', 'woo-feed' ),
			'custom' => esc_html__( 'Custom Template 1', 'woo-feed' ),
			'---1'   => '',
		);
	}

	/**
	 * Popular Template List
	 * @return array
	 */
	protected function popular_merchants() {
		return array(
			'--2'                    => esc_html__( 'Popular Templates', 'woo-feed' ),
            'google'                 => esc_html__( 'Google Shopping', 'woo-feed' ),
            'google_local'           => esc_html__( 'Google Local Product', 'woo-feed' ),
            'google_local_inventory' => esc_html__( 'Google Local Product Inventory', 'woo-feed' ),
            'google_shopping_action' => esc_html__( 'Google Shopping Action', 'woo-feed' ),
			'adwords'                => esc_html__( 'Google Adwords', 'woo-feed' ),
            'facebook'               => esc_html__( 'Facebook', 'woo-feed' ),
            'pinterest'              => esc_html__( 'Pinterest', 'woo-feed' ),
			'bing'                   => esc_html__( 'Bing', 'woo-feed' ),
			'idealo'                 => esc_html__( 'Idealo', 'woo-feed' ),
			'pricespy'               => esc_html__( 'PriceSpy', 'woo-feed' ),
            'pricerunner'            => esc_html__( 'Price Runner', 'woo-feed' ),
			'yandex_csv'             => esc_html__( 'Yandex (CSV)', 'woo-feed' ),
			'---2'                   => '',
		);
	}

	/**
	 * Other Template LIst
	 * @return array
	 */
	protected function others_merchants() {
		return array(
			'--3'                               => esc_html__( 'Templates', 'woo-feed' ),
			'adform'                            => esc_html__( 'AdForm', 'woo-feed' ),
			'adroll'                            => esc_html__( 'AdRoll', 'woo-feed' ),
			'avantlink'                         => esc_html__( 'Avantlink', 'woo-feed' ),
			'become'                            => esc_html__( 'Become', 'woo-feed' ),
			'beslist.nl'                        => esc_html__( 'Beslist.nl', 'woo-feed' ),
            'bestprice'                         => esc_html__( 'Bestprice', 'woo-feed' ),
			'billiger.de'                       => esc_html__( 'Billiger.de', 'woo-feed' ),
			'bol'                               => esc_html__( 'Bol.com', 'woo-feed' ),
			'bonanza'                           => esc_html__( 'Bonanza', 'woo-feed' ),
			'cdiscount.fr'                      => esc_html__( 'CDiscount.fr', 'woo-feed' ),
			'comparer.be'                       => esc_html__( 'Comparer.be', 'woo-feed' ),
			'connexity'                         => esc_html__( 'Connexity', 'woo-feed' ),
			'criteo'                            => esc_html__( 'Criteo', 'woo-feed' ),
			'crowdfox'                          => esc_html__( 'Crowdfox', 'woo-feed' ),
			'daisycon'                          => esc_html__( 'Daisycon Advertiser (General)', 'woo-feed' ),
			'daisycon_automotive'               => esc_html__( 'Daisycon Advertiser (Automotive)', 'woo-feed' ),
			'daisycon_books'                    => esc_html__( 'Daisycon Advertiser (Books)', 'woo-feed' ),
			'daisycon_cosmetics'                => esc_html__( 'Daisycon Advertiser (Cosmetics)', 'woo-feed' ),
			'daisycon_daily_offers'             => esc_html__( 'Daisycon Advertiser (Daily Offers)', 'woo-feed' ),
			'daisycon_electronics'              => esc_html__( 'Daisycon Advertiser (Electronics)', 'woo-feed' ),
			'daisycon_fashion'                  => esc_html__( 'Daisycon Advertiser (Fashion)', 'woo-feed' ),
			'daisycon_food_drinks'              => esc_html__( 'Daisycon Advertiser (Food & Drinks)', 'woo-feed' ),
			'daisycon_holidays_accommodations_and_transport' => esc_html__( 'Daisycon Advertiser (Holidays: Accommodations and transport)', 'woo-feed' ),
			'daisycon_holidays_accommodations'  => esc_html__( 'Daisycon Advertiser (Holidays: Accommodations)', 'woo-feed' ),
			'daisycon_holidays_trips'           => esc_html__( 'Daisycon Advertiser (Holidays: Trips)', 'woo-feed' ),
			'daisycon_home_garden'              => esc_html__( 'Daisycon Advertiser (Home & Garden)', 'woo-feed' ),
			'daisycon_housing'                  => esc_html__( 'Daisycon Advertiser (Housing)', 'woo-feed' ),
			'daisycon_magazines'                => esc_html__( 'Daisycon Advertiser (Magazines)', 'woo-feed' ),
			'daisycon_studies_trainings'        => esc_html__( 'Daisycon Advertiser (Studies & Trainings)', 'woo-feed' ),
			'daisycon_telecom_accessories'      => esc_html__( 'Daisycon Advertiser (Telecom: Accessories)', 'woo-feed' ),
			'daisycon_telecom_all_in_one'       => esc_html__( 'Daisycon Advertiser (Telecom: All-in-one)', 'woo-feed' ),
			'daisycon_telecom_gsm_subscription' => esc_html__( 'Daisycon Advertiser (Telecom: GSM + Subscription)', 'woo-feed' ),
			'daisycon_telecom_gsm'              => esc_html__( 'Daisycon Advertiser (Telecom: GSM only)', 'woo-feed' ),
			'daisycon_telecom_sim'              => esc_html__( 'Daisycon Advertiser (Telecom: Sim only)', 'woo-feed' ),
			'daisycon_work_jobs'                => esc_html__( 'Daisycon Advertiser (Work & Jobs)', 'woo-feed' ),
			'dooyoo'                            => esc_html__( 'Dooyoo', 'woo-feed' ),
            'etsy'                              => esc_html__( 'Etsy', 'woo-feed' ),
			'fruugo'                            => esc_html__( 'Fruugo', 'woo-feed' ),
			'fruugo.au'                         => esc_html__( 'Fruugoaustralia.com', 'woo-feed' ),
			'fyndiq.se'                         => esc_html__( 'Fyndiq.se', 'woo-feed' ),
            'heureka.sk'                        => esc_html__( 'Heureka.sk', 'woo-feed' ),
			'hintaseuranta.fi'                  => esc_html__( 'Hintaseuranta.fi', 'woo-feed' ),
			'incurvy'                           => esc_html__( 'Incurvy', 'woo-feed' ),
			'jet'                               => esc_html__( 'Jet.com', 'woo-feed' ),
			'kelkoo'                            => esc_html__( 'Kelkoo', 'woo-feed' ),
			'kieskeurig.nl'                     => esc_html__( 'Kieskeurig.nl', 'woo-feed' ),
			'kijiji.ca'                         => esc_html__( 'Kijiji.ca', 'woo-feed' ),
			'leguide'                           => esc_html__( 'LeGuide', 'woo-feed' ),
			'marktplaats.nl'                    => esc_html__( 'Marktplaats.nl', 'woo-feed' ),
			'miinto.nl'                         => esc_html__( 'Miinto.nl', 'woo-feed' ),
			'modina.de'                         => esc_html__( 'Modina.de', 'woo-feed' ),
            'moebel.de'                         => esc_html__( 'Moebel.de', 'woo-feed' ),
			'myshopping.com.au'                 => esc_html__( 'Myshopping.com.au', 'woo-feed' ),
			'nextad'                            => esc_html__( 'TheNextAd', 'woo-feed' ),
			'nextag'                            => esc_html__( 'Nextag', 'woo-feed' ),
			'polyvore'                          => esc_html__( 'Polyvore', 'woo-feed' ),
			'pricegrabber'                      => esc_html__( 'Price Grabber', 'woo-feed' ),
			'prisjakt'                          => esc_html__( 'Prisjakt', 'woo-feed' ),
            'profit_share'                      => esc_html__( 'Profit Share', 'woo-feed' ),
			'rakuten.de'                        => esc_html__( 'Rakuten.de', 'woo-feed' ),
			'real'                              => esc_html__( 'Real', 'woo-feed' ),
			'shareasale'                        => esc_html__( 'ShareASale', 'woo-feed' ),
			'shopalike.fr'                      => esc_html__( 'Shopalike.fr', 'woo-feed' ),
			'shopbot'                           => esc_html__( 'Shopbot', 'woo-feed' ),
			'shopmania'                         => esc_html__( 'Shopmania', 'woo-feed' ),
			'shopping'                          => esc_html__( 'Shopping.com', 'woo-feed' ),
			'shopzilla'                         => esc_html__( 'Shopzilla', 'woo-feed' ),
			'skinflint.co.uk'                   => esc_html__( 'SkinFlint.co.uk', 'woo-feed' ),
			'skroutz'                           => esc_html__( 'Skroutz.gr', 'woo-feed' ),
			'smartly.io'                        => esc_html__( 'Smartly.io', 'woo-feed' ),
			'spartoo.fi'                        => esc_html__( 'Spartoo.fi', 'woo-feed' ),
            'shopee'                            => esc_html__( 'Shopee', 'woo-feed' ),
			'stylight.com'                      => esc_html__( 'Stylight.com', 'woo-feed' ),
			'trovaprezzi'                       => esc_html__( 'Trovaprezzi.it', 'woo-feed' ),
			'twenga'                            => esc_html__( 'Twenga', 'woo-feed' ),
            'tweaker_xml'                       => esc_html__( 'Tweakers (XML)', 'woo-feed' ),
            'tweaker_csv'                       => esc_html__( 'Tweakers (CSV)', 'woo-feed' ),
            'vertaa.fi'                         => esc_html__( 'Vertaa.fi', 'woo-feed' ),
			'walmart'                           => esc_html__( 'Walmart', 'woo-feed' ),
			'webmarchand'                       => esc_html__( 'Webmarchand', 'woo-feed' ),
			'wish'                              => esc_html__( 'Wish.com', 'woo-feed' ),
			'yahoo_nfa'                         => esc_html__( 'Yahoo NFA', 'woo-feed' ),
			'zap.co.il'                         => esc_html__( 'Zap.co.il', 'woo-feed' ),
			'zalando'                           => esc_html__( 'Zalando', 'woo-feed' ),
            '---3'                              => '',
		);
	}

	/**
	 * Available Merchant Template list
	 * @return array
	 */
	public function merchants() {
		if ( is_array( $this->merchants ) ) return $this->merchants;
		$this->merchants = $this->custom_merchants() + $this->popular_merchants() + $this->others_merchants();
		return $this->merchants;
	}

	/**
	 * Get Merchant Info
	 *
	 * @param string $merchant
	 *
	 * @return array
	 * @deprecated 3.3.7
	 *
	 */
	public function getInfo( $merchant = '' ) {
		_deprecated_function( __METHOD__, '3.3.7', __CLASS__ . '::get_info' );
		$_info     = $this->get_info();
		$_merchant = $this->merchant;
		if ( ! empty( $merchant ) && is_string( $merchant ) ) {
			$this->merchant = $merchant;
			// reload info
			$this->merchantInfo();
		}
		$info = $this->get_info();
		// restore info for the instance
		$this->merchant = $_merchant;
		$this->info     = $_info;

		return $info;
	}
}
