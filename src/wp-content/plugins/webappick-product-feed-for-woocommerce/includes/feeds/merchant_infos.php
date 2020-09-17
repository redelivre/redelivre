<?php
/**
 * Merchant Infos
 *
 * @author    Kudratullah <mhamudul.hk@gmail.com>
 * @version   1.0.0
 * @package   WooFeed
 * @since     WooFeed 3.4.2
 * @copyright 2020 WebAppick
 */
if ( ! defined( 'ABSPATH' ) ) {
	die(); // silent
}
return array(
	'default'                           => array(
		'feed_file_type' => array( 'XML', 'CSV', 'TXT' ),
	),
	'google'                            => array(
		'link'           => 'http://bit.ly/38kmDrl',
		'video'          => 'https://youtu.be/PTUYgF7DwEo',
		'feed_file_type' => array( 'XML', 'CSV', 'TXT' ),
		'doc'            => array(
			esc_html__( 'How to make google merchant feed?', 'woo-feed' ) => 'http://bit.ly/355q0jY',
			esc_html__( 'How to configure shipping info?', 'woo-feed' ) => 'http://bit.ly/2Rzr0sI',
			esc_html__( 'How to set price with tax?', 'woo-feed' ) => 'http://bit.ly/2PuzWga',
			esc_html__( 'How to solve micro data error?', 'woo-feed' ) => 'http://bit.ly/345nIQz',
			esc_html__( 'How to configure google product categories?', 'woo-feed' ) => 'http://bit.ly/2RFWRrP',
		),
	), // Google.
	'google_local'                      => '',
	'google_local_inventory'            => array(
        'link'           => 'https://support.google.com/merchants/answer/3061342?hl=en',
        'feed_file_type' => array( 'XML', 'CSV', 'TXT' ),
    ),
	'adwords'                           => '',
	'facebook'                          => array(
		'link'           => 'http://bit.ly/2P5cA1V',
		'video'          => 'https://youtu.be/Wo3V_nf_eUU',
		'feed_file_type' => array( 'XML', 'CSV', 'TXT' ),
		'doc'            => array(
			esc_html__( 'How to configure google product categories?', 'woo-feed' ) => 'http://bit.ly/2RFWRrP',
		),
	), // Facebook.
	'pinterest'                         => array(
		'link'           => 'http://bit.ly/35h6YXG',
		'feed_file_type' => array( 'XML', 'CSV', 'TXT' ),
		'doc'            => array(
			esc_html__( 'How to configure google product categories?', 'woo-feed' ) => 'http://bit.ly/2RFWRrP',
		),
	), // Pinterest.
	'bing'                              => array(
		'link'           => 'http://bit.ly/33ZeuVS',
		'feed_file_type' => array( 'XML', 'CSV', 'TXT' ),
	), // Bing.
	'pricespy'                          => array(
		'link'           => 'http://bit.ly/2t0rEFm',
		'feed_file_type' => array( 'TXT' ),
	), // PriceSpy.
	'prisjakt'                          => array(
		'link'           => 'http://bit.ly/36iRT8a',
		'feed_file_type' => array( 'TXT' ),
	), // Prisjakt.
	'idealo'                            => array(
		'link'           => 'http://bit.ly/2LDgJI0',
		'feed_file_type' => array( 'CSV', 'TXT' ),
	), // Idealo.
	'yandex_csv'                        => array(
		'link'           => 'http://bit.ly/2t0tsy8',
		'feed_file_type' => array( 'CSV', 'TXT' ),
	), // Yandex (CSV).
	'adroll'                            => array(
		'link'           => 'http://bit.ly/2qzPtmt',
		'feed_file_type' => array( 'XML', 'CSV' ),
	), // adroll
	'adform'                            => array(
		'link'           => 'http://bit.ly/2s6yatQ',
		'feed_file_type' => array( 'XML', 'CSV' ),
	), // adform
	'kelkoo'                            => array(
		'link'           => 'http://bit.ly/2RAcqkL',
		'feed_file_type' => array( 'XML', 'CSV' ),
	), // Kelkoo.
	'shopmania'                         => array(
		'link'           => 'http://bit.ly/38pE2PA',
		'feed_file_type' => array( 'XML', 'CSV' ),
	), // Shop Mania.
	'connexity'                         => array(
		'link'           => 'http://bit.ly/36lPaLw',
		'feed_file_type' => array( 'TXT' ),
	), // Connexity.
	'twenga'                            => array(
		'link'           => 'http://bit.ly/2RwTIud',
		'feed_file_type' => array( 'XML', 'TXT' ),
	), // Twenga.
	'fruugo'                            => array(
		'link'           => 'http://bit.ly/2Yxabjn',
		'feed_file_type' => array( 'XML', 'CSV' ),
	), // Fruugo.
	'fruugo.au'                         => array(
		'link'           => 'http://bit.ly/2Yxabjn',
		'feed_file_type' => array( 'XML', 'CSV' ),
	), // Fruugo Australia.
	'pricerunner'                       => array(
		'link'           => 'http://bit.ly/2Pznn3s',
		'feed_file_type' => array( 'XML', 'CSV', 'TXT' ),
	), // Price Runner.
	'bonanza'                           => array(
		'link'           => 'http://bit.ly/2Rxi9aK',
		'feed_file_type' => array( 'CSV' ),
	), // Bonanza
	'bol'                               => array(
		'link'           => 'http://bit.ly/2P3MUCu',
		'feed_file_type' => array( 'XML', 'CSV' ),
	), // Bol.
	'wish'                              => array(
		'link'           => 'http://bit.ly/348iYtc',
		'feed_file_type' => array( 'CSV' ),
	), // Wish.com.
	'myshopping.com.au'                 => array(
		'link'           => 'http://bit.ly/2E6jugI',
		'feed_file_type' => array( 'XML', 'CSV', 'TXT' ),
	), // Myshopping.com.au.
	'skinflint.co.uk'                   => array(
		'link'           => 'http://bit.ly/2sg09qJ',
		'feed_file_type' => array( 'CSV' ),
	), // SkinFlint.co.uk.
	'yahoo_nfa'                         => array(
		'link'           => 'http://bit.ly/2LCC58k',
		'feed_file_type' => array( 'XML', 'CSV', 'TXT' ),
	), // Yahoo NFA.
	'comparer.be'                       => array(
		'link'           => 'http://bit.ly/38xz2sa',
		'feed_file_type' => array( 'XML', 'CSV' ),
	), // Comparer.be.
	'rakuten.de'                        => array(
		'link'           => 'http://bit.ly/2YCDdym',
		'feed_file_type' => array( 'CSV', 'TXT' ),
	), // rakuten.
	'avantlink'                         => array(
		'link'           => 'http://bit.ly/2PuExPv',
		'feed_file_type' => array( 'XML', 'CSV', 'TXT' ),
	), // Avantlink
	'shareasale'                        => array(
		'link'           => 'http://bit.ly/36uT1pF',
		'feed_file_type' => array( 'XML', 'CSV', 'TXT' ),
	), // ShareASale.
	'trovaprezzi'                       => array(
		'feed_file_type' => array( 'XML', 'CSV', 'TXT' ),
	), // trovaprezzi.it.
	'skroutz'                           => array(
		'link'           => 'https://developer.skroutz.gr/feedspec/',
		'video'          => '',
		'doc'            => array(
			esc_html__( 'Validator', 'woo-feed' ) => 'https://validator.skroutz.gr/',
		),
		'feed_file_type' => array( 'XML' ),
	),
    'bestprice'          => array(
        'feed_file_type' => array( 'XML' ),
    ),
	'google_shopping_action'            => array(
		'link'           => 'https://support.google.com/merchants/answer/9111285',
		'video'          => '',
		'doc'            => array(
			esc_html__( 'Set up return policies for Shopping Actions', 'woo-feed' ) => 'https://support.google.com/merchants/answer/7660817',
			esc_html__( 'Set up a return address for Shopping Actions', 'woo-feed' ) => 'https://support.google.com/merchants/answer/9035057',
		),
		'feed_file_type' => array( 'XML', 'CSV' ),
	), // Google Shopping Action
	'daisycon'                          => array(
		'link'           => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000721785--As-an-advertiser-how-do-I-submit-a-product-feed-',
		'video'          => '',
		'doc'            => array(
			esc_html__( 'Feed Field Data Types', 'woo-feed' ) => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000727049-Legend-productfeed-field-types',
			esc_html__( 'Product Feed Standard', 'woo-feed' ) => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115001431109-Productfeed-standard-General',
		),
		'feed_file_type' => array( 'XML', 'CSV' ),
	), // Daisycon Advertiser: General
	'daisycon_automotive'               => array(
		'link'           => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000721785--As-an-advertiser-how-do-I-submit-a-product-feed-',
		'video'          => '',
		'doc'            => array(
			esc_html__( 'Feed Field Data Types', 'woo-feed' ) => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000727049-Legend-productfeed-field-types',
			esc_html__( 'Product Feed Standard', 'woo-feed' ) => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115001440805-Productfeed-standard-Automotive',
		),
		'feed_file_type' => array( 'XML', 'CSV' ),
	), // Daisycon Advertiser: Automotive
	'daisycon_books'                    => array(
		'link'           => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000721785--As-an-advertiser-how-do-I-submit-a-product-feed-',
		'video'          => '',
		'doc'            => array(
			esc_html__( 'Feed Field Data Types', 'woo-feed' ) => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000727049-Legend-productfeed-field-types',
			esc_html__( 'Product Feed Standard', 'woo-feed' ) => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115001436885-Productfeed-standard-Books',
		),
		'feed_file_type' => array( 'XML', 'CSV' ),
	), // Daisycon Advertiser: Books
	'daisycon_cosmetics'                => array(
		'link'           => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000721785--As-an-advertiser-how-do-I-submit-a-product-feed-',
		'video'          => '',
		'doc'            => array(
			esc_html__( 'Feed Field Data Types', 'woo-feed' ) => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000727049-Legend-productfeed-field-types',
			esc_html__( 'Product Feed Standard', 'woo-feed' ) => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115001435825-Productfeed-standard-Cosmetics',
		),
		'feed_file_type' => array( 'XML', 'CSV' ),
	), // Daisycon Advertiser: Cosmetics
	'daisycon_daily_offers'             => array(
		'link'           => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000721785--As-an-advertiser-how-do-I-submit-a-product-feed-',
		'video'          => '',
		'doc'            => array(
			esc_html__( 'Feed Field Data Types', 'woo-feed' ) => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000727049-Legend-productfeed-field-types',
			esc_html__( 'Product Feed Standard', 'woo-feed' ) => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115001422549-Productfeed-standard-Daily-offers',
		),
		'feed_file_type' => array( 'XML', 'CSV' ),
	), // Daisycon Advertiser: Daily Offers
	'daisycon_electronics'              => array(
		'link'           => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000721785--As-an-advertiser-how-do-I-submit-a-product-feed-',
		'video'          => '',
		'doc'            => array(
			esc_html__( 'Feed Field Data Types', 'woo-feed' ) => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000727049-Legend-productfeed-field-types',
			esc_html__( 'Product Feed Standard', 'woo-feed' ) => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115001401605-Productfeed-standard-Electronics',
		),
		'feed_file_type' => array( 'XML', 'CSV' ),
	), // Daisycon Advertiser: Electronics
	'daisycon_food_drinks'              => array(
		'link'           => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000721785--As-an-advertiser-how-do-I-submit-a-product-feed-',
		'video'          => '',
		'doc'            => array(
			esc_html__( 'Feed Field Data Types', 'woo-feed' ) => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000727049-Legend-productfeed-field-types',
			esc_html__( 'Product Feed Standard', 'woo-feed' ) => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115001392409-Productfeed-standard-Food-Drinks',
		),
		'feed_file_type' => array( 'XML', 'CSV' ),
	), // Daisycon Advertiser: Food & Drinks
	'daisycon_home_garden'              => array(
		'link'           => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000721785--As-an-advertiser-how-do-I-submit-a-product-feed-',
		'video'          => '',
		'doc'            => array(
			esc_html__( 'Feed Field Data Types', 'woo-feed' ) => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000727049-Legend-productfeed-field-types',
			esc_html__( 'Product Feed Standard', 'woo-feed' ) => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115001406165-Productfeed-standard-House-and-Garden',
		),
		'feed_file_type' => array( 'XML' ),
	), // Daisycon Advertiser: Home & Garden
	'daisycon_housing'                  => array(
		'link'           => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000721785--As-an-advertiser-how-do-I-submit-a-product-feed-',
		'video'          => '',
		'doc'            => array(
			esc_html__( 'Feed Field Data Types', 'woo-feed' ) => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000727049-Legend-productfeed-field-types',
			esc_html__( 'Product Feed Standard', 'woo-feed' ) => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115001397509-Productfeed-standard-Housing',
		),
		'feed_file_type' => array( 'XML', 'CSV' ),
	), // Daisycon Advertiser: Housing
	'daisycon_fashion'                  => array(
		'link'           => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000721785--As-an-advertiser-how-do-I-submit-a-product-feed-',
		'video'          => '',
		'doc'            => array(
			esc_html__( 'Feed Field Data Types', 'woo-feed' ) => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000727049-Legend-productfeed-field-types',
			esc_html__( 'Product Feed Standard', 'woo-feed' ) => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115001410905-Productfeed-standard-Fashion',
		),
		'feed_file_type' => array( 'XML', 'CSV' ),
	), // Daisycon Advertiser: Fashion
	'daisycon_studies_trainings'        => array(
		'link'           => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000721785--As-an-advertiser-how-do-I-submit-a-product-feed-',
		'video'          => '',
		'doc'            => array(
			esc_html__( 'Feed Field Data Types', 'woo-feed' ) => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000727049-Legend-productfeed-field-types',
			esc_html__( 'Product Feed Standard', 'woo-feed' ) => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115001376185-Productfeed-standard-Studies-Courses',
		),
		'feed_file_type' => array( 'XML', 'CSV' ),
	), // Daisycon Advertiser: Studies & Trainings
	'daisycon_telecom_accessories'      => array(
		'link'           => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000721785--As-an-advertiser-how-do-I-submit-a-product-feed-',
		'video'          => '',
		'doc'            => array(
			esc_html__( 'Feed Field Data Types', 'woo-feed' ) => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000727049-Legend-productfeed-field-types',
			esc_html__( 'Product Feed Standard', 'woo-feed' ) => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115001359405-Productfeed-standard-Telecom-Accessoires',
		),
		'feed_file_type' => array( 'XML', 'CSV' ),
	), // Daisycon Advertiser: Telecom: Accessories
	'daisycon_telecom_all_in_one'       => array(
		'link'           => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000721785--As-an-advertiser-how-do-I-submit-a-product-feed-',
		'video'          => '',
		'doc'            => array(
			esc_html__( 'Feed Field Data Types', 'woo-feed' ) => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000727049-Legend-productfeed-field-types',
			esc_html__( 'Product Feed Standard', 'woo-feed' ) => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000740505-Productfeed-standard-Telecom-All-in-one',
		),
		'feed_file_type' => array( 'XML', 'CSV' ),
	), // Daisycon Advertiser: Telecom: All-in-one
	'daisycon_telecom_gsm_subscription' => array(
		'link'           => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000721785--As-an-advertiser-how-do-I-submit-a-product-feed-',
		'video'          => '',
		'doc'            => array(
			esc_html__( 'Feed Field Data Types', 'woo-feed' ) => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000727049-Legend-productfeed-field-types',
			esc_html__( 'Product Feed Standard', 'woo-feed' ) => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000711709-Productfeed-standard-Telecom-GSM-Subscription',
		),
		'feed_file_type' => array( 'XML', 'CSV' ),
	), // Daisycon Advertiser: Telecom: GSM + Subscription
	'daisycon_telecom_gsm'              => array(
		'link'           => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000721785--As-an-advertiser-how-do-I-submit-a-product-feed-',
		'video'          => '',
		'doc'            => array(
			esc_html__( 'Feed Field Data Types', 'woo-feed' ) => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000727049-Legend-productfeed-field-types',
			esc_html__( 'Product Feed Standard', 'woo-feed' ) => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115001359365-Productfeed-standard-GSM-devices',
		),
		'feed_file_type' => array( 'XML', 'CSV' ),
	), // Daisycon Advertiser: Telecom: GSM only
	'daisycon_telecom_sim'              => array(
		'link'           => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000721785--As-an-advertiser-how-do-I-submit-a-product-feed-',
		'video'          => '',
		'doc'            => array(
			esc_html__( 'Feed Field Data Types', 'woo-feed' ) => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000727049-Legend-productfeed-field-types',
			esc_html__( 'Product Feed Standard', 'woo-feed' ) => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115001359545-Productfeed-standard-Telecom-Simonly',
		),
		'feed_file_type' => array( 'XML', 'CSV' ),
	), // Daisycon Advertiser: Telecom: Sim only
	'daisycon_magazines'                => array(
		'link'           => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000721785--As-an-advertiser-how-do-I-submit-a-product-feed-',
		'video'          => '',
		'doc'            => array(
			esc_html__( 'Feed Field Data Types', 'woo-feed' ) => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000727049-Legend-productfeed-field-types',
			esc_html__( 'Product Feed Standard', 'woo-feed' ) => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115001357309-Productfeed-standard-Magazines',
		),
		'feed_file_type' => array( 'XML', 'CSV' ),
	), // Daisycon Advertiser: Magazines
	'daisycon_holidays_accommodations'  => array(
		'link'           => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000721785--As-an-advertiser-how-do-I-submit-a-product-feed-',
		'video'          => '',
		'doc'            => array(
			esc_html__( 'Feed Field Data Types', 'woo-feed' ) => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000727049-Legend-productfeed-field-types',
			esc_html__( 'Product Feed Standard', 'woo-feed' ) => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115001346949-Productfeed-standard-Vacation-Accommodations',
		),
		'feed_file_type' => array( 'XML', 'CSV' ),
	), // Daisycon Advertiser: Holidays: Accommodations
	'daisycon_holidays_accommodations_and_transport' => array(
		'link'           => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000721785--As-an-advertiser-how-do-I-submit-a-product-feed-',
		'video'          => '',
		'doc'            => array(
			esc_html__( 'Feed Field Data Types', 'woo-feed' ) => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000727049-Legend-productfeed-field-types',
			esc_html__( 'Product Feed Standard', 'woo-feed' ) => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115001347069-Productfeed-standard-Vacation-Accommodations-Transport',
		),
		'feed_file_type' => array( 'XML', 'CSV' ),
	), // Daisycon Advertiser: Holidays: Accommodations and transport
	'daisycon_holidays_trips'           => array(
		'link'           => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000721785--As-an-advertiser-how-do-I-submit-a-product-feed-',
		'video'          => '',
		'doc'            => array(
			esc_html__( 'Feed Field Data Types', 'woo-feed' ) => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000727049-Legend-productfeed-field-types',
			esc_html__( 'Product Feed Standard', 'woo-feed' ) => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115001360205-Productfeed-standard-Vacation-Trips',
		),
		'feed_file_type' => array( 'XML', 'CSV' ),
	), // Daisycon Advertiser: Holidays: Trips
	'daisycon_work_jobs'                => array(
		'link'           => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000721785--As-an-advertiser-how-do-I-submit-a-product-feed-',
		'video'          => '',
		'doc'            => array(
			esc_html__( 'Feed Field Data Types', 'woo-feed' ) => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115000727049-Legend-productfeed-field-types',
			esc_html__( 'Product Feed Standard', 'woo-feed' ) => 'https://faq-advertiser.daisycon.com/hc/en-us/articles/115001360329-Productfeed-standard-Work-Jobs',
		),
		'feed_file_type' => array( 'XML', 'CSV' ),
	), // Daisycon Advertiser: Work & Jobs
    'spartoo.fi'                => array(
        'feed_file_type' => array( 'CSV' ),
    ),
    'shopee'                => array(
        'link'           => 'https://webappick-assets.s3.amazonaws.com/feed_template/Shopee_mass_upload_29-06-2020_basic_template.xlsx.xls',
        'feed_file_type' => array( 'CSV' ),
    ),
    'zalando'                => array(
        'link'           => 'https://docs.partner-solutions.zalan.do/de/fci/getting-started.html#format',
        'feed_file_type' => array( 'CSV' ),
    ),
    'etsy'                => array(
        'feed_file_type' => array( 'CSV' ),
    ),
    'tweaker_xml'                => array(
        'link'           => 'https://webappick.com/wp-content/uploads/2020/08/Specificaties-productfeed-Tweakers-Pricewatch.pdf',
        'feed_file_type' => array( 'XML' ),
    ),
    'tweaker_csv'                => array(
        'link'           => 'https://webappick.com/wp-content/uploads/2020/08/Specificaties-productfeed-Tweakers-Pricewatch.pdf',
        'feed_file_type' => array( 'CSV' ),
    ),
    'profit_share'                => array(
        'link'           => 'https://support.profitshare.ro/hc/ro/articles/211436229-Importul-produselor-prin-CSV',
        'feed_file_type' => array( 'CSV' ),
    ),
);
// End of file merchant_infos.php
