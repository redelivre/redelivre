<?php /** @noinspection PhpStatementHasEmptyBodyInspection, PhpUnusedLocalVariableInspection, PhpUnusedParameterInspection, PhpIncludeInspection */
/**
 * Helper Functions
 * @package WooFeed
 * @subpackage WooFeed_Helper_Functions
 * @version 1.0.2
 * @since WooFeed 3.1.40
 * @author KD <mhamudul.hk@gmail.com>
 * @copyright WebAppick
 */

if ( ! defined( 'ABSPATH' ) ) {
	die(); // Silence...
}
/** @define "WOO_FEED_FREE_ADMIN_PATH" "./../admin/" */ // phpcs:ignore

if ( ! function_exists( 'woo_feed_maybe_define_constant' ) ) {
	/**
	 * Define a constant if it is not already defined.
	 *
	 * @param string $name Constant name.
	 * @param mixed  $value Value.
	 *
	 * @return void
	 * @since 3.2.1
	 *
	 */
	function woo_feed_maybe_define_constant( $name, $value ) {
		// phpcs:disable
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
		// phpcs:enable
	}
}
if ( ! function_exists( 'woo_feed_doing_it_wrong' ) ) {
	/**
	 * Wrapper for _doing_it_wrong.
	 *
	 * @param string $function Function used.
	 * @param string $message Message to log.
	 * @param string $version Version the message was added in.
	 *
	 * @return void
	 * @since  3.2.1
	 *
	 */
	function woo_feed_doing_it_wrong( $function, $message, $version ) {
		// phpcs:disable
		$message .= ' Backtrace: ' . wp_debug_backtrace_summary();
		
		if ( is_ajax() || WC()->is_rest_api_request() ) {
			do_action( 'doing_it_wrong_run', $function, $message, $version );
			error_log( "{$function} was called incorrectly. {$message}. This message was added in version {$version}." );
		} else {
			_doing_it_wrong( $function, $message, $version );
		}
		// phpcs:enable
	}
}
if ( ! function_exists( 'is_ajax' ) ) {
	
	/**
	 * Is_ajax - Returns true when the page is loaded via ajax.
	 *
	 * @return bool
	 */
	function is_ajax() {
		return function_exists( 'wp_doing_ajax' ) ? wp_doing_ajax() : defined( 'DOING_AJAX' );
	}
}
if ( ! function_exists( 'woo_feed_is_plugin_active' ) ) {
	/**
	 * Determines whether a plugin is active.
	 *
	 * @param string $plugin Path to the plugin file relative to the plugins directory.
	 *
	 * @return bool True, if in the active plugins list. False, not in the list.
	 * @since 3.1.41
	 * @see is_plugin_active()
	 *
	 */
	function woo_feed_is_plugin_active( $plugin ) {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		
		return is_plugin_active( $plugin );
	}
}
if ( ! function_exists( 'wooFeed_is_plugin_inactive' ) ) {
	/**
	 * Determines whether the plugin is inactive.
	 *
	 * @param string $plugin Path to the plugin file relative to the plugins directory.
	 *
	 * @return bool True if inactive. False if active.
	 * @since 3.1.41
	 * @see wooFeed_is_plugin_inactive()
	 *
	 */
	function wooFeed_is_plugin_inactive( $plugin ) {
		return ! woo_feed_is_plugin_active( $plugin );
	}
}
if ( ! function_exists( 'wooFeed_deactivate_plugins' ) ) {
	/**
	 * Deactivate a single plugin or multiple plugins.
	 * Wrapper for core deactivate_plugins() function
	 *
	 * @param string|array $plugins Single plugin or list of plugins to deactivate.
	 * @param bool         $silent Prevent calling deactivation hooks. Default is false.
	 * @param mixed        $network_wide Whether to deactivate the plugin for all sites in the network.
	 *
	 * @return void
	 * @see deactivate_plugins()
	 *
	 */
	function wooFeed_Deactivate_plugins( $plugins, $silent = false, $network_wide = null ) {
		if ( ! function_exists( 'deactivate_plugins' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		deactivate_plugins( $plugins, $silent, $network_wide );
	}
}
if ( ! function_exists( 'wooFeed_is_supported_php' ) ) {
	/**
	 * Check if server php version meet minimum requirement
	 * @return bool
	 * @since 3.1.41
	 */
	function wooFeed_is_supported_php() {
		// PHP version need to be => WOO_FEED_MIN_PHP_VERSION
		return ! version_compare( PHP_VERSION, WOO_FEED_MIN_PHP_VERSION, '<' );
	}
}
if ( ! function_exists( 'wooFeed_check_WC' ) ) {
	function wooFeed_check_WC() {
		return class_exists( 'WooCommerce', false );
	}
}
if ( ! function_exists( 'wooFeed_is_WC_supported' ) ) {
	function wooFeed_is_WC_supported() {
		// Ensure WC is loaded before checking version
		return ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, WOO_FEED_MIN_WC_VERSION, '>=' ) );
	}
}
if ( ! function_exists( 'woo_feed_wc_version_check' ) ) {
	/**
	 * Check WooCommerce Version
	 *
	 * @param string $version
	 *
	 * @return bool
	 */
	function woo_feed_wc_version_check( $version = '3.0' ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$plugins = get_plugins();
		if ( array_key_exists( 'woocommerce/woocommerce.php', $plugins ) ) {
			$currentVersion = $plugins['woocommerce/woocommerce.php']['Version'];
			if ( version_compare( $currentVersion, $version, '>=' ) ) {
				return true;
			}
		}
		
		return false;
	}
}
if ( ! function_exists( 'woo_feed_wpml_version_check' ) ) {
	/**
	 * Check WooCommerce Version
	 *
	 * @param string $version
	 *
	 * @return bool
	 */
	function woo_feed_wpml_version_check( $version = '3.2' ) {
		// calling this function too early (before wc loaded) will not give correct output
		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			if ( version_compare( ICL_SITEPRESS_VERSION, $version, '>=' ) ) {
				return true;
			}
		}
		
		return false;
	}
}
if ( ! function_exists( 'wooFeed_Admin_Notices' ) ) {
	/**
	 * Display Admin Messages
	 * @hooked admin_notices
	 * @return void
	 * @since 3.1.41
	 */
	function wooFeed_Admin_Notices() {
		// @TODO Refactor this function with admin message class
		// WC Missing Notice..
		if ( ! wooFeed_check_WC() ) {
			$plugin_url = self_admin_url( 'plugin-install.php?s=woocommerce&tab=search&type=term' );
			/** @noinspection HtmlUnknownTarget */
			$plugin_url  = sprintf( '<a href="%s">%s</a>', $plugin_url, esc_html__( 'WooCommerce', 'woo-feed' ) );
			$plugin_name = sprintf( '<code>%s</code>', esc_html__( 'WooCommerce Product Feed', 'woo-feed' ) );
			$wc_name     = sprintf( '<code>%s</code>', esc_html__( 'WooCommerce', 'woo-feed' ) );
			$message = sprintf(
				/* translators: 1: this plugin name, 2: required plugin name, 3: required plugin name and installation url */
				esc_html__( '%1$s requires %2$s to be installed and active. You can installed/activate %3$s here.', 'woo-feed' ),
				$plugin_name,
				$wc_name,
				$plugin_url
			);
			printf( '<div class="error"><p><strong>%1$s</strong></p></div>', $message ); // phpcs:ignore
		}
		if ( wooFeed_check_WC() && ! wooFeed_is_WC_supported() ) {
			$plugin_url = self_admin_url( 'plugin-install.php?s=woocommerce&tab=search&type=term' );
			$wcVersion  = defined( 'WC_VERSION' ) ? '<code>' . WC_VERSION . '</code>' : '<code>UNKNOWN</code>';
			$minVersion = '<code>' . WOO_FEED_MIN_WC_VERSION . '</code>';
			/** @noinspection HtmlUnknownTarget */
			$plugin_url  = sprintf( '<a href="%s">%s</a>', $plugin_url, esc_html__( 'WooCommerce', 'woo-feed' ) );
			$plugin_name = sprintf( '<code>%s</code>', esc_html__( 'WooCommerce Product Feed', 'woo-feed' ) );
			$wc_name     = sprintf( '<code>%s</code>', esc_html__( 'WooCommerce', 'woo-feed' ) );
			$message = sprintf(
				/* translators: 1: this plugin name, 2: required plugin name, 3: required plugin required version, 4: required plugin current version, 5: required plugin update url and name */
                esc_html__( '%1$s requires %2$s version %3$s or above and %4$s found. Please upgrade %2$s to the latest version here %5$s', 'woo-feed' ),
				$plugin_name,
				$wc_name,
				$minVersion,
				$wcVersion,
				$plugin_url
            );
			printf( '<div class="error"><p><strong>%1$s</strong></p></div>', $message ); // phpcs:ignore
		}
	}
}
if ( ! function_exists( 'checkFTP_connection' ) ) {
	/**
	 * Verify if ftp module enabled
	 * @TODO improve module detection
	 * @return bool
	 */
	function checkFTP_connection() {
		return ( extension_loaded( 'ftp' ) || function_exists( 'ftp_connect' ) );
	}
}
if ( ! function_exists( 'checkSFTP_connection' ) ) {
	/**
	 * Verify if ssh/sftp module enabled
	 * @TODO improve module detection
	 * @return bool
	 */
	function checkSFTP_connection() {
		return ( extension_loaded( 'ssh2' ) || function_exists( 'ssh2_connect' ) );
	}
}
if ( ! function_exists( 'array_splice_assoc' ) ) {
	/**
	 * Array Splice Associative Array
	 * @see https://www.php.net/manual/en/function.array-splice.php#111204
	 *
	 * @param array      $input
	 * @param string|int $offset
	 * @param string|int $length
	 * @param array      $replacement
	 *
	 * @return array
	 */
	function array_splice_assoc( $input, $offset, $length, $replacement ) {
		$replacement = (array) $replacement;
		$key_indices = array_flip( array_keys( $input ) );
		if ( isset( $input[ $offset ] ) && is_string( $offset ) ) {
			$offset = $key_indices[ $offset ] + 1;
		}
		if ( isset( $input[ $length ] ) && is_string( $length ) ) {
			$length = $key_indices[ $length ] - $offset;
		}
		
		$input = array_slice( $input, 0, $offset, true ) + $replacement + array_slice( $input, $offset + $length, null, true );
		
		return $input;
	}
}
if ( ! function_exists( 'woo_feed_get_query_type_options' ) ) {
	/**
	 * Get Query available Types
	 *
	 * @return array
	 * @since 3.3.11
	 */
	function woo_feed_get_query_type_options() {
		return [
			'wc'   => __( 'WC_Product_Query', 'woo-feed' ),
			'wp'   => __( 'WP_Query', 'woo-feed' ),
			'both' => __( 'Both', 'woo-feed' ),
		];
	}
}
if ( ! function_exists( 'woo_feed_get_cache_ttl_options' ) ) {
	/**
	 * Cache Expiration Options
	 * @return array
	 */
	function woo_feed_get_cache_ttl_options() {
		return apply_filters(
			'woo_feed_cache_ttl_options',
			[
				0                    => esc_html__( 'No Expiration ', 'woo-feed' ),
				MONTH_IN_SECONDS     => esc_html__( '1 Month', 'woo-feed' ),
				WEEK_IN_SECONDS      => esc_html__( '1 Week', 'woo-feed' ),
				DAY_IN_SECONDS       => esc_html__( '24 Hours', 'woo-feed' ),
				12 * HOUR_IN_SECONDS => esc_html__( '12 Hours', 'woo-feed' ),
				6 * HOUR_IN_SECONDS  => esc_html__( '6 Hours', 'woo-feed' ),
				HOUR_IN_SECONDS      => esc_html__( '1 Hours', 'woo-feed' ),
			]
		);
	}
}
if ( ! function_exists( 'woo_feed_get_custom2_merchant' ) ) {
	/**
	 * Get Merchant list that are allowed on Custom2 Template
	 * @return array
	 */
	function woo_feed_get_custom2_merchant() {
		return array( 'custom2', 'admarkt', 'yandex_xml', 'glami' );
	}
}
if ( ! function_exists( 'woo_feed_get_merchant_class' ) ) {
	/**
	 * @param string $provider
	 *
	 * @return string
	 */
	function woo_feed_get_merchant_class( $provider ) {
		if ( in_array( $provider, [ 'google', 'adroll', 'smartly.io' ] ) ) {
			return 'Woo_Feed_Google';
		} elseif ( 'pinterest' == $provider ) {
			return 'Woo_Feed_Pinterest';
		} elseif ( 'facebook' == $provider ) {
			return 'Woo_Feed_Facebook';
		} elseif ( strpos( $provider, 'amazon' ) !== false ) {
			return 'Woo_Feed_Amazon';
		} elseif ( in_array( $provider, woo_feed_get_custom2_merchant() ) ) {
			return 'Woo_Feed_Custom_XML';
		} else {
			return 'Woo_Feed_Custom';
		}
	}
}
if ( ! function_exists( 'woo_feed_handle_file_transfer' ) ) {
	/**
	 * Transfer file as per ftp config
	 *
	 * @param string $fileFrom
	 * @param string $fileTo
	 * @param array  $info
	 *
	 * @return bool
	 */
	function woo_feed_handle_file_transfer( $fileFrom, $fileTo, $info ) {
		if ( 1 == (int) $info['ftpenabled'] ) {
			if ( ! file_exists( $fileFrom ) ) {
				woo_feed_log_feed_process( $info['filename'], 'Unable to process file transfer request. File does not exists.' );
				return false;
			}
			$ftpHost      = sanitize_text_field( $info['ftphost'] );
			$ftp_user     = sanitize_text_field( $info['ftpuser'] );
			$ftp_password = sanitize_text_field( $info['ftppassword'] );
			$ftpPath      = trailingslashit( untrailingslashit( sanitize_text_field( $info['ftppath'] ) ) );
            $ftp_passive_mode = (isset($info['ftpmode']) && sanitize_text_field( $info['ftpmode'] ) == 'passive') ? true : false;
			if ( isset( $info['ftporsftp'] ) & 'ftp' === $info['ftporsftp'] ) {
				$ftporsftp = 'ftp';
			} else {
				$ftporsftp = 'sftp';
			}
			if ( isset( $info['ftpport'] ) && ! empty( $info['ftpport'] ) ) {
				$ftp_port = absint( $info['ftpport'] );
			} else {
				$ftp_port = false;
			}
			if ( ! $ftp_port || ( ( 1 <= $ftp_port ) && ( $ftp_port <= 65535 ) ) ) {
				$ftp_port = 'sftp' === $ftporsftp ? 22 : 21;
			}
			woo_feed_log_feed_process( $info['filename'], sprintf( 'Uploading Feed file via %s.', $ftporsftp ) );
			try {
				if ( 'ftp' == $ftporsftp ) {
					$ftp = new FTPClient();
					if ( $ftp->connect( $ftpHost, $ftp_user, $ftp_password, $ftp_passive_mode, $ftp_port ) ) {
						return $ftp->upload_file( $fileFrom, $ftpPath . $fileTo );
					}
				} elseif ( 'sftp' == $ftporsftp ) {
					$sftp = new SFTPConnection( $ftpHost, $ftp_port );
					$sftp->login( $ftp_user, $ftp_password );
					
					return $sftp->upload_file( $fileFrom, $fileTo, $ftpPath );
				}
			} catch ( Exception $e ) {
				$message = 'Error Uploading Feed Via ' . $ftporsftp . PHP_EOL . 'Caught Exception :: ' . $e->getMessage();
				woo_feed_log( $info['filename'], $message, 'critical', $e, true );
				woo_feed_log_fatal_error( $message, $e );
				return false;
			}
		}
		return false;
	}
}
if ( ! function_exists( 'woo_feed_get_file_types' ) ) {
	function woo_feed_get_file_types() {
		return array(
			'xml' => 'XML',
			'csv' => 'CSV',
			'txt' => 'TXT',
		);
	}
}
if ( ! function_exists( 'woo_feed_get_default_brand' ) ) {
    /**
     * Guess Brand name from Site URL
     * @return string
     */
    function woo_feed_get_default_brand() {
        $brand = apply_filters( 'woo_feed_pre_get_default_brand_name', null );
        if ( ! is_null( $brand ) ) {
            return $brand;
        }
        $brand = '';
        $url   = filter_var( site_url(), FILTER_SANITIZE_URL );
        if ( false !== $url ) {
            $url = wp_parse_url( $url );
            if ( array_key_exists( 'host', $url ) ) {
                if ( strpos($url['host'], ".") !== false ) {
                    $arr   = explode( '.', $url['host'] );
                    $brand = $arr[ count( $arr ) - 2 ];
                    $brand = ucfirst( $brand );
                } else {
                    $brand = $url['host'];
                    $brand = ucfirst( $brand );
                }
            }
        }

        return apply_filters( 'woo_feed_get_default_brand_name', $brand );
    }
}
if ( ! function_exists( 'woo_feed_merchant_require_google_category' ) ) {
	/**
	 * Check if current merchant supports google taxonomy for current attribute.
	 * @param string $merchant
	 * @param string $attribute
	 *
	 * @return array|bool
	 */
	function woo_feed_merchant_require_google_category( $merchant = null, $attribute = null ) {
		$list = [
			'current_category'        => [
				'google',
				'google_shopping_action',
				'google_local',
				'google_local_inventory',
				'adroll',
				'smartly.io',
				'facebook',
				'pinterest',
				'rakuten.de',
			],
			'google_product_category' => [ 'rakuten.de' ],
			'google_category_id'      => [ 'daisycon', 'daisycon_automotive', 'daisycon_books', 'daisycon_cosmetics', 'daisycon_daily_offers', 'daisycon_electronics', 'daisycon_food_drinks', 'daisycon_home_garden', 'daisycon_housing', 'daisycon_fashion', 'daisycon_studies_trainings', 'daisycon_telecom_accessories', 'daisycon_telecom_all_in_one', 'daisycon_telecom_gsm_subscription', 'daisycon_telecom_gsm', 'daisycon_telecom_sim', 'daisycon_magazines', 'daisycon_holidays_accommodations', 'daisycon_holidays_accommodations_and_transport', 'daisycon_holidays_trips', 'daisycon_work_jobs' ],
		];
		if ( null !== $merchant && null !== $attribute ) {
			return ( isset( $list[ $attribute ] ) && in_array( $merchant, $list[ $attribute ] ) );
		}
		return $list;
	}
}
if ( ! function_exists( 'woo_feed_get_item_wrapper_hidden_merchant' ) ) {
    function woo_feed_get_item_wrapper_hidden_merchant(){
	    return apply_filters(
		    'woo_feed_item_wrapper_hidden_merchant',
		    [
			    'google',
				'google_shopping_action',
				'facebook',
				'pinterest',
				'fruugo.au',
			    'stylight.com',
				'nextad',
				'skinflint.co.uk',
				'comparer.be',
				'dooyoo',
				'hintaseuranta.fi',
			    'incurvy',
				'kijiji.ca',
				'marktplaats.nl',
				'rakuten.de',
				'shopalike.fr',
				'spartoo.fi',
			    'webmarchand',
				'skroutz',
				'daisycon',
				'daisycon_automotive',
				'daisycon_books',
			    'daisycon_cosmetics',
				'daisycon_daily_offers',
				'daisycon_electronics',
			    'daisycon_food_drinks',
				'daisycon_home_garden',
				'daisycon_housing',
				'daisycon_fashion',
			    'daisycon_studies_trainings',
				'daisycon_telecom_accessories',
				'daisycon_telecom_all_in_one',
			    'daisycon_telecom_gsm_subscription',
				'daisycon_telecom_gsm',
				'daisycon_telecom_sim',
			    'daisycon_magazines',
				'daisycon_holidays_accommodations',
			    'daisycon_holidays_accommodations_and_transport',
				'daisycon_holidays_trips',
				'daisycon_work_jobs',
		    ]
	    );
    }
}

// The Editor.
if ( ! function_exists( 'woo_feed_parse_feed_rules' ) ) {
	/**
	 * Parse Feed Config/Rules to make sure that necessary array keys are exists
	 * this will reduce the uses of isset() checking
	 *
	 * @param array  $rules rules to parse.
	 * @param string $context parsing context. useful for filtering, view, save, db, create etc.
	 *
	 * @return array
	 * @since 3.3.5 $context parameter added.
	 *
	 * @uses wp_parse_args
	 *
	 */
	function woo_feed_parse_feed_rules( $rules = [], $context = 'view' ) {
		
		if ( empty( $rules ) ) {
			$rules = [];
		}
		$defaults             = [
			'provider'            => '',
			'filename'            => '',
			'feedType'            => '',
			'ftpenabled'          => 0,
			'ftporsftp'           => 'ftp',
			'ftphost'             => '',
			'ftpport'             => '21',
			'ftpuser'             => '',
			'ftppassword'         => '',
			'ftppath'             => '',
            'ftpmode'             => 'active',
			'is_variations'       => 'n',
			'variable_price'      => 'first',
			'variable_quantity'   => 'first',
			'feedLanguage'        => apply_filters( 'wpml_current_language', null ),
			'feedCurrency'        => get_woocommerce_currency(),
			'itemsWrapper'        => 'products',
			'itemWrapper'         => 'product',
			'delimiter'           => ',',
			'enclosure'           => 'double',
			'extraHeader'         => '',
			'vendors'             => [],
			// Feed Config
			'mattributes'         => [], // merchant attributes
			'prefix'              => [], // prefixes
			'type'                => [], // value (attribute) types
			'attributes'          => [], // product attribute mappings
			'default'             => [], // default values (patterns) if value type set to pattern
			'suffix'              => [], // suffixes
			'output_type'         => [], // output type (output filter)
			'limit'               => [], // limit or command
			// filters tab
			'composite_price'     => '',
			'product_ids'         => '',
			'categories'          => [],
			'post_status'         => [ 'publish' ],
			'filter_mode'         => [],
			'campaign_parameters' => [],
			//'is_outOfStock'         => 'y',
			//'product_visibility'    => 0,
			// include hidden ? 1 yes 0 no
			//'outofstock_visibility' => 1,
			// override wc global option for out-of-stock product hidden from catalog? 1 yes 0 no
			'ptitle_show'         => '',
			'decimal_separator'   => wc_get_price_decimal_separator(),
			'thousand_separator'  => wc_get_price_thousand_separator(),
			'decimals'            => wc_get_price_decimals(),
		];
		$rules                = wp_parse_args( $rules, $defaults );
		$rules['filter_mode'] = wp_parse_args( $rules['filter_mode'],
			[
				'product_ids' => 'include',
				'categories'  => 'include',
				'post_status' => 'include',
			] );
		
		$rules['campaign_parameters'] = wp_parse_args(
			$rules['campaign_parameters'],
			[
				'utm_source'   => '',
				'utm_medium'   => '',
				'utm_campaign' => '',
				'utm_term'     => '',
				'utm_content'  => '',
			]
		);
		
		if ( ! empty( $rules['provider'] ) && is_string( $rules['provider'] ) ) {
			/**
			 * filter parsed rules for provider
			 *
			 * @param array $rules
			 * @param string $context
			 *
			 * @since 3.3.7
			 *
			 */
			$rules = apply_filters( "woo_feed_{$rules['provider']}_parsed_rules", $rules, $context );
		}
		
		/**
		 * filter parsed rules
		 *
		 * @param array $rules
		 * @param string $context
		 *
		 * @since 3.3.7 $provider parameter removed
		 *
		 */
		return apply_filters( 'woo_feed_parsed_rules', $rules, $context );
	}
}
if ( ! function_exists( 'woo_feed_register_and_do_woo_feed_meta_boxes' ) ) {
	/**
	 * Registers the default Feed Editor MetaBoxes, and runs the `do_meta_boxes` actions.
	 *
	 * @param string|WP_Screen $screen Screen identifier. If you have used add_menu_page() or
	 *                                      add_submenu_page() to create a new screen (and hence screen_id)
	 *                                      make sure your menu slug conforms to the limits of sanitize_key()
	 *                                      otherwise the 'screen' menu may not correctly render on your page.
	 * @param array            $feedRules current feed being processed.
	 *
	 * @return void
	 * @see register_and_do_post_meta_boxes()
	 *
	 * @since 3.2.6
	 *
	 */
	function woo_feed_register_and_do_woo_feed_meta_boxes( $screen, $feedRules = array() ) {
		if ( empty( $screen ) ) {
			$screen = get_current_screen();
		} elseif ( is_string( $screen ) ) {
			$screen = convert_to_screen( $screen );
		}
		// edit page MetaBoxes
		if ( 'woo-feed_page_webappick-new-feed' === $screen->id || 'toplevel_page_webappick-manage-feeds' === $screen->id ) {
			add_meta_box( 'feed_merchant_info', 'Feed Merchant Info', 'woo_feed_merchant_info_metabox', null, 'side', 'default' );
		}
		/**
		 * This action is documented in wp-admin/includes/meta-boxes.php
		 * using screen id instead of post type
		 */
		do_action( 'add_meta_boxes', $screen->id, $feedRules );
		do_action( "add_meta_boxes_{$screen->id}", $feedRules );
		do_action( 'do_meta_boxes', $screen->id, 'normal', $feedRules );
		do_action( 'do_meta_boxes', $screen->id, 'advanced', $feedRules );
		do_action( 'do_meta_boxes', $screen->id, 'side', $feedRules );
	}
}
if ( ! function_exists( 'woo_feed_ajax_merchant_info' ) ) {
	add_action( 'wp_ajax_woo_feed_get_merchant_info', 'woo_feed_ajax_merchant_info' );
	function woo_feed_ajax_merchant_info() {
		if ( isset( $_REQUEST['nonce'] ) && wp_verify_nonce( sanitize_text_field( $_REQUEST['nonce'] ),
				'wpf_feed_nonce' ) ) {
			$provider     = ( isset( $_REQUEST['provider'] ) && ! empty( $_REQUEST['provider'] ) ) ? sanitize_text_field( $_REQUEST['provider'] ) : '';
			$merchantInfo = new Woo_Feed_Merchant( $provider );
			$data         = [];
			$na           = esc_html__( 'N/A', 'woo-feed' );
			foreach ( $merchantInfo->get_info() as $k => $v ) {
				if ( 'link' == $k ) {
					/** @noinspection HtmlUnknownTarget */
					$data[ $k ] = empty( $v ) ? $na : sprintf( '<a href="%s" target="_blank">%s</a>',
						esc_url( $v ),
						esc_html__( 'Read Article', 'woo-feed' ) );
				} elseif ( 'video' == $k ) {
					/** @noinspection HtmlUnknownTarget */
					$data[ $k ] = empty( $v ) ? $na : sprintf( '<a href="%s" target="_blank">%s</a>',
						esc_url( $v ),
						esc_html__( 'Watch Now', 'woo-feed' ) );
				} elseif ( 'feed_file_type' == $k ) {
					if ( ! empty( $v ) ) {
						$v          = array_map( function ( $type ) {
							return strtoupper( $type );
						},
							(array) $v );
						$data[ $k ] = esc_html( implode( ', ', $v ) );
					} else {
						$data[ $k ] = $na;
					}
				} elseif ( 'doc' == $k ) {
					$links = '';
					foreach ( $v as $label => $link ) {
						/** @noinspection HtmlUnknownTarget */
						$links .= sprintf( '<li><a href="%s" target="_blank">%s</a></li>',
							esc_url( $link ),
							esc_html( $label ) );
					}
					$data[ $k ] = empty( $links ) ? $na : $links;
				}
			}
			wp_send_json_success( $data );
		} else {
			wp_send_json_error( esc_html__( 'Invalid Request.', 'woo-feed' ) );
		}
		die();
	}
}
if ( ! function_exists( 'woo_feed_merchant_info_metabox' ) ) {
	/**
	 * Render Merchant Info Metabox
	 *
	 * @param array $feedConfig
	 *
	 * @return void
	 */
	function woo_feed_merchant_info_metabox( $feedConfig ) {
		$provider     = ( isset( $feedConfig['provider'] ) && ! empty( $feedConfig['provider'] ) ) ? $feedConfig['provider'] : '';
		$merchantInfo = new Woo_Feed_Merchant( $provider );
		?>
        <span class="spinner"></span>
        <div class="merchant-infos">
			<?php foreach ( $merchantInfo->get_info() as $k => $v ) { ?>
                <div class="merchant-info-section <?php echo esc_attr( $k ); ?>">
					<?php if ( 'link' == $k ) { ?>
                        <span class="dashicons dashicons-media-document" style="color: #82878c;" aria-hidden="true"></span>
                        <span><?php esc_html_e( 'Feed Specification:', 'woo-feed' ); ?></span>
                        <strong class="data">
                        <?php
							/** @noinspection HtmlUnknownTarget */
							( empty( $v ) ) ? esc_html_e( 'N/A',
								'woo-feed' ) : printf( '<a href="%s" target="_blank">%s</a>',
								esc_url( $v ),
								esc_html__( 'Read Article', 'woo-feed' ) );
							?>
                            </strong>
					<?php } elseif ( 'video' == $k ) { ?>
                        <span class="dashicons dashicons-video-alt3" style="color: #82878c;" aria-hidden="true"></span>
                        <span><?php esc_html_e( 'Video Documentation:', 'woo-feed' ); ?></span>
                        <strong class="data">
                        <?php
							/** @noinspection HtmlUnknownTarget */
							( empty( $v ) ) ? esc_html_e( 'N/A',
								'woo-feed' ) : printf( '<a href="%s" target="_blank">%s</a>',
								esc_url( $v ),
								esc_html__( 'Watch now', 'woo-feed' ) );
							?>
                            </strong>
					<?php } elseif ( 'feed_file_type' == $k ) { ?>
                        <span class="dashicons dashicons-media-text" style="color: #82878c;" aria-hidden="true"></span> <?php esc_html_e( 'Format Supported:', 'woo-feed' ); ?>
                        <strong class="data">
                        <?php
							if ( empty( $v ) ) {
							esc_html_e( 'N/A', 'woo-feed' );
							} else {
							$v = implode( ', ',
							array_map( function ( $type ) {
								return esc_html( strtoupper( $type ) );
								},
							(array) $v ) );
							echo esc_html( $v );
							} 
                            ?>
                            </strong>
						<?php
					} elseif ( 'doc' == $k ) { 
                    ?>
                        <span class="dashicons dashicons-editor-help" style="color: #82878c;" aria-hidden="true"></span>
                        <span><?php esc_html_e( 'Support Docs:', 'woo-feed' ); ?></span>
                        <ul class="data">
							<?php
							if ( empty( $v ) ) {
								esc_html_e( 'N/A', 'woo-feed' );
							} else {
								foreach ( $v as $label => $link ) {
									/** @noinspection HtmlUnknownTarget */
									printf( '<li><a href="%s" target="_blank">%s</a></li>',
										esc_url( $link ),
										esc_html( $label ) );
								}
							}
							?>
                        </ul>
						<?php
					} 
                    ?>
                </div>
			<?php } ?>
        </div>
		<?php
	}
}
if ( ! function_exists( 'woo_feed_get_csv_delimiters' ) ) {
	/**
	 * Get CSV/TXT/TSV Delimiters
	 * @return array
	 */
	function woo_feed_get_csv_delimiters() {
		return [
			','   => 'Comma',
			'tab' => 'Tab',
			':'   => 'Colon',
			' '   => 'Space',
			'|'   => 'Pipe',
			';'   => 'Semi Colon',
		];
	}
}
if ( ! function_exists( 'woo_feed_get_csv_enclosure' ) ) {
	/**
	 * Get CSV/TXT/TSV Enclosure for multiple words
	 * @return array
	 */
	function woo_feed_get_csv_enclosure() {
		return [
			'double' => '"',
			'single' => '\'',
			' '      => 'None',
		];
	}
}

// Editor Tabs.
if ( ! function_exists( 'render_feed_config' ) ) {
	/**
	 * @param string $tabId
	 * @param array  $feedRules
	 * @param bool   $idEdit
	 */
	function render_feed_config( $tabId, $feedRules, $idEdit ) {
		global $provider, $wooFeedDropDown, $merchant;
		include WOO_FEED_FREE_ADMIN_PATH . 'partials/woo-feed-edit-config.php';
	}
}
if ( ! function_exists( 'render_filter_config' ) ) {
	/**
	 * @param string $tabId
	 * @param array  $feedRules
	 * @param bool   $idEdit
	 */
	function render_filter_config( $tabId, $feedRules, $idEdit ) {
		global $provider, $wooFeedDropDown, $merchant;
		include WOO_FEED_FREE_ADMIN_PATH . 'partials/woo-feed-edit-filter.php';
	}
}
if ( ! function_exists( 'render_ftp_config' ) ) {
	/**
	 * @param string $tabId
	 * @param array  $feedRules
	 * @param bool   $idEdit
	 */
	function render_ftp_config( $tabId, $feedRules, $idEdit ) {
		global $provider, $wooFeedDropDown, $merchant;
		include WOO_FEED_FREE_ADMIN_PATH . 'partials/woo-feed-edit-ftp.php';
	}
}

// Sanitization.
if ( ! function_exists( 'woo_feed_check_google_category' ) ) {
	/**
	 * @param array $feedInfo
	 *
	 * @return string
	 */
	function woo_feed_check_google_category( $feedInfo ) {
		// Check Google Product Category for Google & Facebook Template and show message.
		$list              = woo_feed_merchant_require_google_category();
		$cat_keys          = array_keys( $list );
		$merchants         = call_user_func_array( 'array_merge', array_values( $list ) );
		$checkCategory     = isset( $feedInfo['feedrules']['mattributes'] ) ? $feedInfo['feedrules']['mattributes'] : [];
		$checkCategoryType = isset( $feedInfo['feedrules']['type'] ) ? $feedInfo['feedrules']['type'] : [];
		$merchant          = isset( $feedInfo['feedrules']['provider'] ) ? $feedInfo['feedrules']['provider'] : [];
		$cat               = 'yes';
		foreach ( $list as $attribute => $merchants ) {
			if ( in_array( $merchant, $merchants ) && in_array( $attribute, $checkCategory ) ) {
				$catKey = array_search( $attribute, $checkCategory );
				if ( 'pattern' == $checkCategoryType[ $catKey ] ) {
					$checkCategoryValue = $feedInfo['feedrules']['default'];
				} else {
					$checkCategoryValue = $feedInfo['feedrules']['attributes'];
				}
				
				if ( empty( $checkCategoryValue[ $catKey ] ) ) {
					$cat = 'no';
				}
				break;
			}
		}
		
		return $cat;
	}
}
if ( ! function_exists( 'woo_feed_array_sanitize' ) ) {
	/**
	 * Sanitize array post
	 *
	 * @param array $array
	 *
	 * @return array
	 */
	function woo_feed_array_sanitize( $array ) {
		$newArray = array();
		if ( count( $array ) ) {
			foreach ( $array as $key => $value ) {
				if ( is_array( $value ) ) {
					foreach ( $value as $key2 => $value2 ) {
						if ( is_array( $value2 ) ) {
							foreach ( $value2 as $key3 => $value3 ) {
								$newArray[ $key ][ $key2 ][ $key3 ] = sanitize_text_field( $value3 );
							}
						} else {
							$newArray[ $key ][ $key2 ] = sanitize_text_field( $value2 );
						}
					}
				} else {
					$newArray[ $key ] = sanitize_text_field( $value );
				}
			}
		}
		
		return $newArray;
	}
}
if ( ! function_exists( 'woo_feed_sanitize_form_fields' ) ) {
	/**
	 * Sanitize Form Fields ($_POST Array)
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	function woo_feed_sanitize_form_fields( $data ) {
		foreach ( $data as $k => $v ) {
			if ( true === apply_filters( 'woo_feed_sanitize_form_fields', true, $k, $v, $data ) ) {
				if ( is_array( $v ) ) {
					$v = woo_feed_sanitize_form_fields( $v );
				} else {
					// $v = sanitize_text_field( $v ); #TODO should not trim Prefix and Suffix field
				}
			}
			$data[ $k ] = apply_filters( 'woo_feed_sanitize_form_field', $v, $k );
		}
		
		return $data;
	}
}
if ( ! function_exists( 'woo_feed_unique_feed_slug' ) ) {
	/**
	 * Generate Unique slug for feed.
	 * This function only check database for existing feed for generating unique slug.
	 * Use generate_unique_feed_file_name() for complete unique slug name.
	 *
	 * @param string $slug slug for checking uniqueness.
	 * @param string $prefix prefix to check with. Optional.
	 * @param int    $option_id option id. Optional option id to exclude specific option.
	 *
	 * @return string
	 * @see wp_unique_post_slug()
	 *
	 */
	function woo_feed_unique_feed_slug( $slug, $prefix = '', $option_id = null ) {
		global $wpdb;
		/** @noinspection SpellCheckingInspection */
		$disallowed = array( 'siteurl', 'home', 'blogname', 'blogdescription', 'users_can_register', 'admin_email' );
		if ( $option_id && $option_id > 0 ) {
			$checkSql  = "SELECT option_name FROM $wpdb->options WHERE option_name = %s AND option_id != %d LIMIT 1";
			$nameCheck = $wpdb->get_var( $wpdb->prepare( $checkSql, $prefix . $slug, $option_id ) ); // phpcs:ignore
		} else {
			$checkSql  = "SELECT option_name FROM $wpdb->options WHERE option_name = %s LIMIT 1";
			$nameCheck = $wpdb->get_var( $wpdb->prepare( $checkSql, $prefix . $slug ) ); // phpcs:ignore
		}
		// slug found or slug in disallowed list
		if ( $nameCheck || in_array( $slug, $disallowed ) ) {
			$suffix = 2;
			do {
				$altName = _truncate_post_slug( $slug, 200 - ( strlen( $suffix ) + 1 ) ) . "-$suffix";
				if ( $option_id && $option_id > 0 ) {
					$nameCheck = $wpdb->get_var( $wpdb->prepare( $checkSql, $prefix . $altName, $option_id ) ); // phpcs:ignore
				} else {
					$nameCheck = $wpdb->get_var( $wpdb->prepare( $checkSql, $prefix . $altName ) ); // phpcs:ignore
				}
				$suffix ++;
			} while ( $nameCheck );
			$slug = $altName;
		}
		
		return $slug;
	}
}
if ( ! function_exists( 'woo_feed_unique_option_name' ) ) {
	/**
	 * Alias of woo_feed_unique_feed_slug
	 *
	 * @param string $slug
	 * @param string $prefix
	 * @param null   $option_id
	 *
	 * @return string
	 * @see woo_feed_unique_feed_slug
	 *
	 * @since 3.3.8
	 *
	 */
	function woo_feed_unique_option_name( $slug, $prefix = '', $option_id = null ) {
		return woo_feed_unique_feed_slug( $slug, $prefix, $option_id );
	}
}
if ( ! function_exists( 'generate_unique_feed_file_name' ) ) {
	/**
	 * Generate Unique file Name.
	 * This will insure unique slug and file name for a single feed.
	 *
	 * @param string $filename
	 * @param string $type
	 * @param string $provider
	 *
	 * @return string|string[]
	 */
	function generate_unique_feed_file_name( $filename, $type, $provider ) {
		
		$feedDir      = woo_feed_get_file_dir( $provider, $type );
		$raw_filename = sanitize_title( $filename, '', 'save' );
		// check option name uniqueness ...
		$raw_filename = woo_feed_unique_feed_slug( $raw_filename, 'wf_feed_' );
		$raw_filename = sanitize_file_name( $raw_filename . '.' . $type );
		$raw_filename = wp_unique_filename( $feedDir, $raw_filename );
		$raw_filename = str_replace( '.' . $type, '', $raw_filename );
		
		return - 1 != $raw_filename ? $raw_filename : false;
	}
}

// File process.
if ( ! function_exists( 'woo_feed_check_valid_extension' ) ) {
	/**
	 * Check Feed File Extension Validity
	 *
	 * @param string $extension Ext to check.
	 *
	 * @return bool
	 */
	function woo_feed_check_valid_extension( $extension ) {
		return in_array( $extension, array_keys( woo_feed_get_file_types() ) );
	}
}
if ( ! function_exists( 'woo_feed_save_feed_config_data' ) ) {
	/**
	 * Sanitize And Save Feed config data (array) to db (option table)
	 *
	 * @param array $data data to be saved in db
	 * @param null  $feed_option_name feed (file) name. optional, if empty or null name will be auto generated
	 * @param bool  $configOnly save only wf_config or both wf_config and wf_feed_. default is only wf_config
	 *
	 * @return bool|string          return false if failed to update. return filename if success
	 */
	function woo_feed_save_feed_config_data( $data, $feed_option_name = null, $configOnly = true ) {
		if ( ! is_array( $data ) ) {
			return false;
		}
		if ( ! isset( $data['filename'], $data['feedType'], $data['provider'] ) ) {
			return false;
		}
		// unnecessary form fields to remove
		$removables = [ 'closedpostboxesnonce', '_wpnonce', '_wp_http_referer', 'save_feed_config', 'edit-feed' ];
		foreach ( $removables as $removable ) {
			if ( isset( $data[ $removable ] ) ) {
				unset( $data[ $removable ] );
			}
		}
		// parse rules
		$data = woo_feed_parse_feed_rules( $data );
		// Sanitize Fields
		$data = woo_feed_sanitize_form_fields( $data );
		if ( empty( $feed_option_name ) ) {
			$feed_option_name = generate_unique_feed_file_name( $data['filename'],
				$data['feedType'],
				$data['provider'] );
		} else {
			$feed_option_name = woo_feed_extract_feed_option_name( $feed_option_name );
		}
		
		// get old config
		$old_data = get_option( 'wf_config' . $feed_option_name, array() );
		$update   = false;
		$updated  = false;
		if ( is_array( $old_data ) && ! empty( $old_data ) ) {
			$update = true;
		}
		
		/**
		 * Filters feed data just before it is inserted into the database.
		 *
		 * @param array $data An array of sanitized config
		 * @param array $old_data An array of old feed data
		 * @param string $feed_option_name Option name
		 *
		 * @since 3.3.3
		 *
		 */
		$data = apply_filters( 'woo_feed_insert_feed_data', $data, $old_data, 'wf_config' . $feed_option_name );
		
		if ( $update ) {
			/**
			 * Before Updating Config to db
			 *
			 * @param array $data An array of sanitized config
			 * @param string $feed_option_name Option name
			 */
			do_action( 'woo_feed_before_update_config', $data, 'wf_config' . $feed_option_name );
		} else {
			/**
			 * Before inserting Config to db
			 *
			 * @param array $data An array of sanitized config
			 * @param string $feed_option_name Option name
			 */
			do_action( 'woo_feed_before_insert_config', $data, 'wf_config' . $feed_option_name );
		}
		$updated = ( $data === $old_data );
		if ( false === $updated ) {
			// Store Config.
			$updated = update_option( 'wf_config' . $feed_option_name, $data, false );
		}
		// update wf_feed if wp_config update ok...
		if ( $updated && false === $configOnly ) {
			$old_feed  = maybe_unserialize( get_option( 'wf_feed_' . $feed_option_name ) );
			$feed_data = array(
				'feedrules'    => $data,
				'url'          => woo_feed_get_file_url( $feed_option_name, $data['provider'], $data['feedType'] ),
				'last_updated' => date('Y-m-d H:i:s', strtotime( current_time( 'mysql' ) ) ),
				'status'       => isset( $old_feed['status'] ) && 1 == $old_feed['status'] ? 1 : 0,
				// set old status or disable auto update.
			);
			
			$saved2    = update_option( 'wf_feed_' . $feed_option_name, maybe_serialize( $feed_data ), false );
		}
		
		if ( $update ) {
			/**
			 * After Updating Config to db
			 *
			 * @param array $data An array of sanitized config
			 * @param string $feed_option_name Option name
			 */
			do_action( 'woo_feed_after_update_config', $data, 'wf_config' . $feed_option_name );
		} else {
			/**
			 * After inserting Config to db
			 *
			 * @param array $data An array of sanitized config
			 * @param string $feed_option_name Option name
			 */
			do_action( 'woo_feed_after_insert_config', $data, 'wf_config' . $feed_option_name );
		}
		
		// return filename on success or update status
		return $updated ? $feed_option_name : $updated;
	}
}
if ( ! function_exists( 'woo_feed_extract_feed_option_name' ) ) {
	/**
	 * Remove Feed Option Name Prefix and return the slug
	 *
	 * @param string $feed_option_name
	 *
	 * @return string
	 */
	function woo_feed_extract_feed_option_name( $feed_option_name ) {
		return str_replace( [ 'wf_feed_', 'wf_config' ], '', $feed_option_name );
	}
}
if ( ! function_exists( 'woo_feed_get_file_path' ) ) {
	/**
	 * Get File Path for feed or the file upload path for the plugin to use.
	 *
	 * @param string $provider provider name.
	 * @param string $type feed file type.
	 *
	 * @return string
	 */
	function woo_feed_get_file_path( $provider = '', $type = '' ) {
		$upload_dir = wp_get_upload_dir();
		
		return sprintf( '%s/woo-feed/%s/%s/', $upload_dir['basedir'], $provider, $type );
	}
}
if ( ! function_exists( 'woo_feed_get_file' ) ) {
	/**
	 * Get Feed File URL
	 *
	 * @param string $fileName
	 * @param string $provider
	 * @param string $type
	 *
	 * @return string
	 */
	function woo_feed_get_file( $fileName, $provider, $type ) {
		$fileName = woo_feed_extract_feed_option_name( $fileName );
		$path     = woo_feed_get_file_path( $provider, $type );
		
		return sprintf( '%s/%s.%s', untrailingslashit( $path ), $fileName, $type );
	}
}
if ( ! function_exists( 'woo_feed_get_file_url' ) ) {
	/**
	 * Get Feed File URL
	 *
	 * @param string $fileName
	 * @param string $provider
	 * @param string $type
	 *
	 * @return string
	 */
	function woo_feed_get_file_url( $fileName, $provider, $type ) {
		$fileName   = woo_feed_extract_feed_option_name( $fileName );
		$upload_dir = wp_get_upload_dir();
		
		return esc_url( sprintf( '%s/woo-feed/%s/%s/%s.%s',
			$upload_dir['baseurl'],
			$provider,
			$type,
			$fileName,
			$type ) );
	}
}
if ( ! function_exists( 'woo_feed_check_feed_file' ) ) {
	/**
	 * Check if feed file exists
	 *
	 * @param string $fileName
	 * @param string $provider
	 * @param string $type
	 *
	 * @return bool
	 */
	function woo_feed_check_feed_file( $fileName, $provider, $type ) {
		$upload_dir = wp_get_upload_dir();
		
		return file_exists( sprintf( '%s/woo-feed/%s/%s/%s.%s',
			$upload_dir['basedir'],
			$provider,
			$type,
			$fileName,
			$type ) );
	}
}
if ( ! function_exists( 'woo_feed_get_file_dir' ) ) {
	/**
	 * Get Feed Directory
	 *
	 * @param string $provider
	 * @param string $feedType
	 *
	 * @return string
	 */
	function woo_feed_get_file_dir( $provider, $feedType ) {
		$upload_dir = wp_get_upload_dir();
		
		return sprintf( '%s/woo-feed/%s/%s', $upload_dir['basedir'], $provider, $feedType );
	}
}
if ( ! function_exists( 'woo_feed_save_batch_feed_info' ) ) {
	/**
	 * Save Feed Batch Chunk
	 *
	 * @param string       $feedService merchant.
	 * @param string       $type file type (ext).
	 * @param string|array $string data.
	 * @param string       $fileName file name.
	 * @param array        $info feed config.
	 *
	 * @return bool
	 */
	function woo_feed_save_batch_feed_info( $feedService, $type, $string, $fileName, $info ) {
		$ext = $type;
		if ( 'csv' == $type ) {
			$string = wp_json_encode( $string );
			$ext    = 'json';
		}
		// Save File.
		$path   = woo_feed_get_file_dir( $feedService, $type );
		$file   = $path . '/' . $fileName . '.' . $ext;
		$save   = new Woo_Feed_Savefile();
		$status = $save->saveFile( $path, $file, $string );
		if ( woo_feed_is_debugging_enabled() ) {
			if ( $status ) {
				$message = sprintf( 'Batch chunk file (%s) saved.', $fileName );
			} else {
				$message = sprintf( 'Unable to save batch chunk file %s.', $fileName );
			}
			woo_feed_log_feed_process( $info['filename'], $message );
		}
		
		return $status;
	}
}
if ( ! function_exists( 'woo_feed_get_batch_feed_info' ) ) {
	/**
	 * @param string $feedService
	 * @param string $type
	 * @param string $fileName
	 *
	 * @return bool|array|string
	 */
	function woo_feed_get_batch_feed_info( $feedService, $type, $fileName ) {
		$ext = $type;
		if ( 'csv' == $type ) {
			$ext = 'json';
		}
		// Save File
		$path = woo_feed_get_file_dir( $feedService, $type );
		$file = $path . '/' . $fileName . '.' . $ext;
		if ( ! file_exists( $file ) ) {
			return false;
		}
		
		$data = file_get_contents( $file ); // phpcs:ignore
		
		if ( 'csv' == $type ) {
			$data = ( $data ) ? json_decode( $data, true ) : false;
		}
		return $data;
	}
}
if ( ! function_exists( 'woo_feed_unlink_tempFiles' ) ) {
	/**
	 * Remove temporary feed files
	 *
	 * @param array  $config      Feed config
	 * @param string $fileName    feed file name.
	 *
	 * @return void
	 */
	function woo_feed_unlink_tempFiles( $config, $fileName ) {
		$type = $config['feedType'];
		$ext  = $type;
		$path = woo_feed_get_file_dir( $config['provider'], $type );
		
		if ( 'csv' == $type ) {
			$ext = 'json';
		}
		$files = [
			'headerFile' => $path . '/' . 'wf_store_feed_header_info_' . $fileName . '.' . $ext,
			'bodyFile'   => $path . '/' . 'wf_store_feed_body_info_' . $fileName . '.' . $ext,
			'footerFile' => $path . '/' . 'wf_store_feed_footer_info_' . $fileName . '.' . $ext,
		];
		
		woo_feed_log_feed_process( $config['filename'], sprintf( 'Deleting Temporary Files (%s).', implode( ', ', array_values( $files ) ) ) );
		foreach ( $files as $key => $file ) {
			if ( file_exists( $file ) ) {
				unlink( $file ); // phpcs:ignore
			}
		}
	}
}
if ( ! function_exists( 'woo_feed_delete_feed' ) ) {
	/**
	 * Delete feed option and the file from uploads directory
	 *
	 * @param string|int $feed_id feed option name or ID.
	 *
	 * @return bool
	 */
	function woo_feed_delete_feed( $feed_id ) {
		global $wpdb;
		if ( ! is_numeric( $feed_id ) ) {
			$feed_name = woo_feed_extract_feed_option_name( $feed_id );
		} else {
			$feed_data   = $wpdb->get_row( $wpdb->prepare( "SELECT option_name FROM $wpdb->options WHERE option_id = %d", $feed_id ) ); // phpcs:ignore
			$option_name = $feed_data->option_name;
			$feed_name   = woo_feed_extract_feed_option_name( $feed_data->option_name );
		}
		$feedInfo = maybe_unserialize( get_option( 'wf_feed_' . $feed_name ) );
		if ( false !== $feedInfo ) {
			$feedInfo = $feedInfo['feedrules'];
		} else {
			$feedInfo = maybe_unserialize( get_option( 'wf_config' . $feed_name ) );
		}
		$deleted = false;
		$file    = woo_feed_get_file( $feed_name, $feedInfo['provider'], $feedInfo['feedType'] );
		// delete any leftover
		woo_feed_unlink_tempFiles( $feedInfo, $feed_name );
		if ( file_exists( $file ) ) {
			// file exists in upload directory
			if ( unlink( $file ) ) { // phpcs:ignore
				delete_option( 'wf_feed_' . $feed_name );
				delete_option( 'wf_config' . $feed_name );
				$deleted = true;
			}
		} else {
			delete_option( 'wf_feed_' . $feed_name );
			delete_option( 'wf_config' . $feed_name );
			$deleted = true;
		}

		// Delete cron schedule.
		$feed_cron_param = 'wf_config' . $feed_name;
        wp_clear_scheduled_hook( 'woo_feed_update_single_feed',[ $feed_cron_param ]);
		
		return $deleted;
	}
}

// Mics..
if ( ! function_exists( 'woo_feed_remove_query_args' ) ) {
	/**
	 * Add more items to the removable query args array...
	 *
	 * @param array $removable_query_args
	 *
	 * @return array
	 */
	function woo_feed_remove_query_args( $removable_query_args ) {
		global $plugin_page;
		if ( strpos( $plugin_page, 'webappick' ) !== false ) {
			$removable_query_args[] = 'feed_created';
			$removable_query_args[] = 'feed_updated';
			$removable_query_args[] = 'feed_imported';
			$removable_query_args[] = 'feed_regenerate';
			$removable_query_args[] = 'feed_name';
			$removable_query_args[] = 'link';
			$removable_query_args[] = 'wpf_message';
			$removable_query_args[] = 'cat';
			$removable_query_args[] = 'schedule_updated';
			$removable_query_args[] = 'settings_updated';
			/** @noinspection SpellCheckingInspection */
			$removable_query_args[] = 'WPFP_WPML_CURLANG';
		}
		
		return $removable_query_args;
	}
	
	add_filter( 'removable_query_args', 'woo_feed_remove_query_args', 10, 1 );
}
if ( ! function_exists( 'woo_feed_usort_reorder' ) ) {
	/**
	 * This checks for sorting input and sorts the data in our array accordingly.
	 *
	 * In a real-world situation involving a database, you would probably want
	 * to handle sorting by passing the 'orderby' and 'order' values directly
	 * to a custom query. The returned data will be pre-sorted, and this array
	 * sorting technique would be unnecessary.
	 *
	 * @param array $a first data.
	 *
	 * @param array $b second data.
	 *
	 * @return bool
	 */
	function woo_feed_usort_reorder( $a, $b ) {
		// If no sort, default to title
		$orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? sanitize_text_field( $_REQUEST['orderby'] ) : 'option_name'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		// If no order, default to asc
		$order  = ( ! empty( $_REQUEST['order'] ) ) ? sanitize_text_field( $_REQUEST['order'] ) : 'asc'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$result = strcmp( $a[ $orderby ], $b[ $orderby ] ); // Determine sort order
		
		return ( 'asc' === $order ) ? $result : - $result; // Send final sort direction to usort
	}
}
if ( ! function_exists( 'str_replace_trim' ) ) {
	/**
	 * str_replace() wrapper with trim()
	 *
	 * @param mixed  $search    The value being searched for, otherwise known as the needle.
	 *                          An array may be used to designate multiple needles.
	 * @param mixed  $replace   The replacement value that replaces found search values.
	 *                          An array may be used to designate multiple replacements.
	 * @param mixed  $subject   The string or array being searched and replaced on,
	 *                          otherwise known as the haystack.
	 * @param string $charlist  [optional]
	 *                          Optionally, the stripped characters can also be specified using the charlist parameter.
	 *                          Simply list all characters that you want to be stripped.
	 *                          With this you can specify a range of characters.
	 *
	 * @return array|string
	 */
	function str_replace_trim( $search, $replace, $subject, $charlist = " \t\n\r\0\x0B" ) {
		$replaced = str_replace( $search, $replace, $subject );
		if ( is_array( $replaced ) ) {
			return array_map(
				function ( $item ) use ( $charlist ) {
					return trim( $item, $charlist );
				},
				$replaced
			);
		} else {
			return trim( $replaced, $charlist );
		}
	}
}
if ( ! function_exists( 'woo_feed_export_config' ) ) {
	/**
	 * Handle config export request
	 *
	 * @return void
	 * @since 3.3.10
	 */
    function woo_feed_export_config(){
        if ( isset( $_REQUEST['feed'], $_REQUEST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( $_REQUEST['_wpnonce'] ), 'wpf-export' ) ) {
        	$feed = sanitize_text_field( $_REQUEST['feed'] );
        	$feed = woo_feed_extract_feed_option_name( $feed );
        	if ( ! empty( $feed ) ) {
        		$feed = maybe_unserialize( get_option( 'wf_feed_' . $feed ) );
        		$feed = ( isset( $feed['feedrules'] ) && is_array( $feed['feedrules'] ) ) ? $feed['feedrules'] : [];
	        }
	        if ( ! is_array( $feed ) ) {
		        wp_die( esc_html__( 'Invalid Request', 'woo-feed' ), esc_html__( 'Invalid Request', 'woo-feed' ), [ 'back_link' => true ] );
	        }
	        $file_name = sprintf(
		        '%s-%s-%s.wpf',
		        sanitize_title( $feed['filename'] ),
		        $feed['provider'],
		        time()
	        );
	        $feed      = wp_json_encode( $feed );
	        $meta      = wp_json_encode( [
		        'version'   => WOO_FEED_FREE_VERSION,
		        'file_name' => $file_name,
		        'hash'      => md5( $feed ),
	        ] );
	        $bin       = pack( 'VA*VA*', strlen( $meta ), $meta, strlen( $feed ), $feed );
	        $feed      = gzdeflate( $bin, 9 );
	        // Let set the header...
	        if ( ! headers_sent() ) {
		        status_header( 200 );
		        header( 'Content-Type: application/octet-stream;' );
		        header( 'Content-disposition: attachment; filename=' . $file_name );
		        header( 'Content-Length: ' . strlen( $feed ) );
		        header( 'Pragma: no-cache' );
		        header( 'Expires: 0' );
	        }
	        // exporting data.
	        echo $feed; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        } else {
	        wp_die(
	        	esc_html__( 'Invalid Request', 'woo-feed' ),
		        esc_html__( 'Invalid Request', 'woo-feed' ),
		        [ 'back_link' => true ]
	        );
        }
    }
}
if ( ! function_exists( 'woo_feed_import_config' ) ) {
	/**
	 * Handle config import request
	 *
	 * @return void
	 * @since 3.3.10
	 */
    function woo_feed_import_config() {
	    check_admin_referer( 'wpf_import' );
	    
	    if (
	    	isset(
	    		$_FILES['wpf_import_file'],
			    $_POST['wpf_import_feed_name'],
			    $_FILES['wpf_import_file']['name'],
			    $_FILES['wpf_import_file']['tmp_name']
		    ) &&
		    'wpf' === pathinfo( $_FILES['wpf_import_file']['name'], PATHINFO_EXTENSION ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	    ) {
		    $file_name = sanitize_text_field( $_FILES['wpf_import_file']['name'] );
		    $data      = file_get_contents( sanitize_text_field( $_FILES['wpf_import_file']['tmp_name'] ) );
		    if ( empty( $data ) ) {
			    wp_die(
				    esc_html__( 'Empty File Uploaded. Try again.', 'woo-feed' ),
				    esc_html__( 'Empty File', 'woo-feed' ),
				    [
					    'link_url'  => esc_url( admin_url( 'admin.php?page=webappick-manage-feeds' ) ),
					    'link_text' => esc_html__( '&laquo; Back', 'woo-feed' ),
				    ]
			    );
		    }
		    $feed = gzinflate( $data );
		    if ( false === $feed ) {
			    wp_die(
				    esc_html__( 'Unable to read file content', 'woo-feed' ),
				    esc_html__( 'Invalid File', 'woo-feed' ),
				    [
					    'link_url'  => esc_url( admin_url( 'admin.php?page=webappick-manage-feeds' ) ),
					    'link_text' => esc_html__( '&laquo; Back', 'woo-feed' ),
				    ]
			    );
		    }
		    // unpack meta data.
		    $meta_length = unpack( 'V', $feed );
		    if ( false === $meta_length ) {
			    wp_die(
				    esc_html__( 'Unable to read data from file.', 'woo-feed' ),
				    esc_html__( 'Invalid File', 'woo-feed' ),
				    [
					    'link_url'  => esc_url( admin_url( 'admin.php?page=webappick-manage-feeds' ) ),
					    'link_text' => esc_html__( '&laquo; Back', 'woo-feed' ),
				    ]
			    );
		    }
		    $meta = unpack( 'A*', substr( $feed, 4, $meta_length[1] ) )[1];
		    if ( false === $meta || 0 !== strpos( $meta, '{' ) ) {
			    wp_die(
				    esc_html__( 'Unable to read file info.', 'woo-feed' ),
				    esc_html__( 'Invalid File', 'woo-feed' ),
				    [
					    'link_url'  => esc_url( admin_url( 'admin.php?page=webappick-manage-feeds' ) ),
					    'link_text' => esc_html__( '&laquo; Back', 'woo-feed' ),
				    ]
			    );
		    }
		    $meta = json_decode( $meta, true );
		    // unpack feed data.
		    $feed = substr( $feed, $meta_length[1] + 8 ); // 4 bytes for each V (length data)
		    $feed = unpack( 'A*', $feed )[1];
		    if ( false === $feed || 0 !== strpos( $feed, '{' ) ) {
			    wp_die(
				    esc_html__( 'Unable to read feed data from file.', 'woo-feed' ),
				    esc_html__( 'Invalid File', 'woo-feed' ),
				    [
					    'link_url'  => esc_url( admin_url( 'admin.php?page=webappick-manage-feeds' ) ),
					    'link_text' => esc_html__( '&laquo; Back', 'woo-feed' ),
				    ]
			    );
		    }
		    if ( md5( $feed ) !== $meta['hash'] ) {
			    wp_die(
				    esc_html__( 'Unable to verify the file.', 'woo-feed' ),
				    esc_html__( 'Invalid File', 'woo-feed' ),
				    [
					    'link_url'  => esc_url( admin_url( 'admin.php?page=webappick-manage-feeds' ) ),
					    'link_text' => esc_html__( '&laquo; Back', 'woo-feed' ),
				    ]
			    );
		    }
		
		    $feed = json_decode( $feed, true );
		    if ( ! is_array( $feed ) ) {
			    wp_die(
				    esc_html__( 'Invalid or corrupted config file.', 'woo-feed' ),
				    esc_html__( 'Invalid File', 'woo-feed' ),
				    [
					    'link_url'  => esc_url( admin_url( 'admin.php?page=webappick-manage-feeds' ) ),
					    'link_text' => esc_html__( '&laquo; Back', 'woo-feed' ),
				    ]
			    );
		    }
		
		    $feed     = woo_feed_parse_feed_rules( $feed );
		    $new_name = sanitize_text_field( $_POST['wpf_import_feed_name'] );
		    $new_name = trim( $new_name );
		    if ( ! empty( $new_name ) ) {
			    $opt_name         = $new_name;
			    $feed['filename'] = $new_name;
		    } else {
			    $opt_name         = $feed['filename'];
			    $feed['filename'] = str_replace_trim( [ '-', '_' ], ' ', $feed['filename'] );
			    $feed['filename'] = sprintf(
				    '%s: %s',
				    esc_html__( ' Imported', 'woo-feed' ),
				    ucwords( $feed['filename'] )
			    );
		    }
		    // New Slug.
		    $opt_name = generate_unique_feed_file_name( $opt_name,
			    $feed['feedType'],
			    $feed['provider'] );
		    // save config.
		    $fileName = woo_feed_save_feed_config_data( $feed, $opt_name, false );
		    // Redirect back to the list.
		    wp_safe_redirect(
			    add_query_arg(
				    [
					    'feed_imported'   => (int) false !== $fileName,
					    'feed_regenerate' => 1,
					    'feed_name'       => $fileName ? $fileName : '',
				    ],
				    esc_url( admin_url( 'admin.php?page=webappick-manage-feeds' ) )
			    )
		    );
		    die();
	    }
	    wp_die(
		    esc_html__( 'Invalid Request.', 'woo-feed' ),
		    esc_html__( 'Invalid Request', 'woo-feed' ),
		    [
			    'link_url'  => esc_url( admin_url( 'admin.php?page=webappick-manage-feeds' ) ),
			    'link_text' => esc_html__( '&laquo; Back', 'woo-feed' ),
		    ]
	    );
    }
}

// Feed Functions.
if ( ! function_exists( 'woo_feed_generate_feed' ) ) {
	/**
	 * Update Feed Information
	 *
	 * @param array  $info feed config array
	 * @param string $feed_option_name feed option/file name
	 *
	 * @return string|bool
	 */
	function woo_feed_generate_feed( $info, $feed_option_name ) {
		if ( false === $info || empty( $info ) ) {
			return false;
		}
		// parse rules.
		$info             = woo_feed_parse_feed_rules( isset( $info['feedrules'] ) ? $info['feedrules'] : $info );
		$feed_option_name = woo_feed_extract_feed_option_name( $feed_option_name );
		if ( ! empty( $info['provider'] ) ) {
			do_action( 'before_woo_feed_generate_feed', $info );
			
			// Generate Feed Data
			$products  = new Woo_Generate_Feed( $info['provider'], $info );
			$getString = $products->getProducts();
			if ( 'csv' == $info['feedType'] ) {
				$csvHead[0] = $getString['header'];
				if ( ! empty( $csvHead ) && ! empty( $getString['body'] ) ) {
					$string = array_merge( $csvHead, $getString['body'] );
				} else {
					$string = array();
				}
			} else {
				$string = $getString['header'] . $getString['body'] . $getString['footer'];
			}
			
			$saveFile = false;
			// Check If any products founds
			if ( $string && ! empty( $string ) ) {
				// Save File
				$path = woo_feed_get_file_path( $info['provider'], $info['feedType'] );
				$file = woo_feed_get_file( $feed_option_name, $info['provider'], $info['feedType'] );
				$save = new Woo_Feed_Savefile();
				if ( 'csv' == $info['feedType'] ) {
					$saveFile = $save->saveCSVFile( $path, $file, $string, $info );
				} else {
					$saveFile = $save->saveFile( $path, $file, $string );
				}
				
				// Upload file to ftp server
				if ( 1 == (int) $info['ftpenabled'] ) {
					woo_feed_handle_file_transfer( $file, $feed_option_name . '.' . $info['feedType'], $info );
				}
			}
			$feed_URL = woo_feed_get_file_url( $feed_option_name, $info['provider'], $info['feedType'] );
			// Save Info into database
			$feedInfo = array(
				'feedrules'    => $info,
				'url'          => $feed_URL,
				'last_updated' => date('Y-m-d H:i:s', strtotime( current_time( 'mysql' ) ) ),
				'status'       => 1,
			);
			update_option( 'wf_feed_' . $feed_option_name, serialize( $feedInfo ), false ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
			do_action( 'after_woo_feed_generate_feed', $info );
			if ( $saveFile ) {
				return $feed_URL;
			} else {
				return false;
			}
		}
		
		return false;
	}
}

if ( ! function_exists( 'woo_feed_get_schedule_interval_options' ) ) {
	/**
	 * Get Schedule Intervals
	 * @return mixed
	 */
	function woo_feed_get_schedule_interval_options() {
		return apply_filters(
			'woo_feed_schedule_interval_options',
			[
				WEEK_IN_SECONDS      => esc_html__( '1 Week', 'woo-feed' ),
				DAY_IN_SECONDS       => esc_html__( '24 Hours', 'woo-feed' ),
				12 * HOUR_IN_SECONDS => esc_html__( '12 Hours', 'woo-feed' ),
				6 * HOUR_IN_SECONDS  => esc_html__( '6 Hours', 'woo-feed' ),
				HOUR_IN_SECONDS      => esc_html__( '1 Hours', 'woo-feed' ),
			]
		);
	}
}
if ( ! function_exists( 'woo_feed_get_minimum_interval_option' ) ) {
	function woo_feed_get_minimum_interval_option() {
		$intervals = array_keys( woo_feed_get_schedule_interval_options() );
		if ( ! empty( $intervals ) ) {
			return end( $intervals );
		}
		
		return 15 * MINUTE_IN_SECONDS;
	}
}
if ( ! function_exists( 'woo_feed_stripInvalidXml' ) ) {
	/**
	 * Remove non supported xml character
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	function woo_feed_stripInvalidXml( $value ) {
		$ret = '';
		if ( empty( $value ) ) {
			return $ret;
		}
		$length = strlen( $value );
		for ( $i = 0; $i < $length; $i ++ ) {
			$current = ord( $value[ $i ] );
			if ( ( 0x9 == $current ) || ( 0xA == $current ) || ( 0xD == $current ) || ( ( $current >= 0x20 ) && ( $current <= 0xD7FF ) ) || ( ( $current >= 0xE000 ) && ( $current <= 0xFFFD ) ) || ( ( $current >= 0x10000 ) && ( $current <= 0x10FFFF ) ) ) {
				$ret .= chr( $current );
			} else {
				$ret .= '';
			}
		}
		
		return $ret;
	}
}
if ( ! function_exists( 'woo_feed_get_formatted_url' ) ) {
	/**
	 * Get Formatted URL
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	function woo_feed_get_formatted_url( $url = '' ) {
		if ( ! empty( $url ) ) {
			if ( substr( trim( $url ), 0, 4 ) === 'http' || substr( trim( $url ),
					0,
					3 ) === 'ftp' || substr( trim( $url ), 0, 4 ) === 'sftp' ) {
				return rtrim( $url, '/' );
			} else {
				$base = get_site_url();
				$url  = $base . $url;
				
				return rtrim( $url, '/' );
			}
		}
		
		return '';
	}
}
if ( ! function_exists( 'array_value_first' ) ) {
	/**
	 * Get First Value of an array
	 *
	 * @param array $arr
	 *
	 * @return mixed|null
	 * @since 3.0.0
	 */
	function array_value_first( array $arr ) {
		foreach ( $arr as $key => $unused ) {
			return $unused;
		}
		
		return null;
	}
}
if ( ! function_exists( 'woo_feed_make_url_with_parameter' ) ) {
	/**
	 * Make proper URL using parameters
	 *
	 * @param string $output
	 * @param string $suffix
	 *
	 * @return string
	 */
	function woo_feed_make_url_with_parameter( $output = '', $suffix = '' ) {
		if ( empty( $output ) || empty( $suffix ) ) {
			return $output;
		}
		
		$getParam = explode( '?', $output );
		$URLParam = array();
		if ( isset( $getParam[1] ) ) {
			$URLParam = woo_feed_parse_string( $getParam[1] );
		}
		
		$EXTRAParam = array();
		if ( ! empty( $suffix ) ) {
			$suffix     = str_replace( '?', '', $suffix );
			$EXTRAParam = woo_feed_parse_string( $suffix );
		}
		
		$params = array_merge( $URLParam, $EXTRAParam );
		if ( ! empty( $params ) && '' != $output ) {
			$params  = http_build_query( $params );
			$baseURL = isset( $getParam ) ? $getParam[0] : $output;
			$output  = $baseURL . '?' . $params;
		}
		
		return $output;
	}
}
if ( ! function_exists( 'woo_feed_parse_string' ) ) {
	/**
	 * Parse URL parameter
	 *
	 * @param string $str
	 *
	 * @return array
	 */
	function woo_feed_parse_string( $str = '' ) {
		
		// result array
		$arr = array();
		
		if ( empty( $str ) ) {
			return $arr;
		}
		
		// split on outer delimiter
		$pairs = explode( '&', $str );
		
		if ( ! empty( $pairs ) ) {
			
			// loop through each pair
			foreach ( $pairs as $i ) {
				// split into name and value
				list( $name, $value ) = explode( '=', $i, 2 );
				
				// if name already exists
				if ( isset( $arr[ $name ] ) ) {
					// stick multiple values into an array
					if ( is_array( $arr[ $name ] ) ) {
						$arr[ $name ][] = $value;
					} else {
						$arr[ $name ] = array( $arr[ $name ], $value );
					}
				} // otherwise, simply stick it in a scalar
				else {
					$arr[ $name ] = $value;
				}
			}
		} elseif ( ! empty( $str ) ) {
			list( $name, $value ) = explode( '=', $str, 2 );
			$arr[ $name ] = $value;
		}
		
		// return result array
		return $arr;
	}
}
if ( ! function_exists( 'woo_feed_replace_to_merchant_attribute' ) ) {
	/**
	 * Parse URL parameter
	 *
	 * @param string                      $pluginAttribute
	 * @param string                      $merchant
	 * @param string feedType CSV XML TXT
	 *
	 * @return string
	 */
	function woo_feed_replace_to_merchant_attribute( $pluginAttribute, $merchant, $feedType ) {
		$attributeClass     = new Woo_Feed_Default_Attributes();
		$merchantAttributes = '';
		if ( 'google' == $merchant || 'adroll' == $merchant || 'smartly.io' == $merchant ) {
			if ( 'xml' == $feedType ) {
				$merchantAttributes = $attributeClass->googleXMLAttribute;
			} elseif ( 'csv' == $feedType || 'txt' == $feedType ) {
				$merchantAttributes = $attributeClass->googleCSVTXTAttribute;
			}
		} elseif ( 'facebook' == $merchant ) {
			if ( 'xml' == $feedType ) {
				$merchantAttributes = $attributeClass->facebookXMLAttribute;
			} elseif ( 'csv' == $feedType || 'txt' == $feedType ) {
				$merchantAttributes = $attributeClass->facebookCSVTXTAttribute;
			}
		} elseif ( 'pinterest' == $merchant ) {
			if ( 'xml' == $feedType ) {
				$merchantAttributes = $attributeClass->pinterestXMLAttribute;
			} elseif ( 'csv' == $feedType || 'txt' == $feedType ) {
				$merchantAttributes = $attributeClass->pinterestCSVTXTAttribute;
			}
		} elseif ( 'skroutz' == $merchant ) {
			if ( 'xml' == $feedType ) {
				$merchantAttributes = $attributeClass->skroutzXMLAttributes;
			}
		}
		
		if ( ! empty( $merchantAttributes ) && array_key_exists( $pluginAttribute, $merchantAttributes ) ) {
			return $merchantAttributes[ $pluginAttribute ][0];
		}
		
		return $pluginAttribute;
	}
}
if ( ! function_exists( 'woo_feed_add_cdata' ) ) {
	/**
	 * Parse URL parameter
	 *
	 * @param string $pluginAttribute
	 * @param string $attributeValue
	 * @param string $merchant
	 *
	 * @return string
	 */
	function woo_feed_add_cdata( $pluginAttribute, $attributeValue, $merchant ) {
		if ( strpos( $attributeValue, '<![CDATA[' ) !== false ) {
			return "$attributeValue";
		}
		
		$attributeClass     = new Woo_Feed_Default_Attributes();
		$merchantAttributes = '';
		if ( 'google' == $merchant ) {
			$merchantAttributes = $attributeClass->googleXMLAttribute;
		} elseif ( 'facebook' == $merchant ) {
			$merchantAttributes = $attributeClass->facebookXMLAttribute;
		} elseif ( 'pinterest' == $merchant ) {
			$merchantAttributes = $attributeClass->pinterestXMLAttribute;
		} elseif ( 'skroutz' == $merchant ) {
			$merchantAttributes = $attributeClass->skroutzXMLAttributes;
		}
		
		if ( ! empty( $merchantAttributes ) && array_key_exists( $pluginAttribute, $merchantAttributes ) ) {
			if ( 'true' == $merchantAttributes[ $pluginAttribute ][1] ) {
				return "<![CDATA[$attributeValue]]>";
			} else {
				return "$attributeValue";
			}
		} elseif ( false !== strpos( $attributeValue, '&' ) || 'http' == substr( trim( $attributeValue ), 0, 4 ) ) {
			return "<![CDATA[$attributeValue]]>";
		} else {
			return "$attributeValue";
		}
	}
}

// WooFeed Settings API
if ( ! function_exists( 'woo_feed_get_options' ) ) {
	/**
	 * Get saved settings.
	 *
	 * @param string $key     Option name.
	 *                        All default values will be returned if this set to 'defaults',
	 *                        all settings will be return if set to 'all'.
	 * @param bool   $default value to return if no matching data found for the key (option)
	 *
	 * @return array|bool|string|mixed
	 * @since 3.3.11
	 */
	function woo_feed_get_options( $key, $default = false ) {
		$defaults = [
			'per_batch'              => 200,
			'product_query_type'     => 'both',
			'enable_error_debugging' => 'off',
			'cache_ttl'              => 6 * HOUR_IN_SECONDS,
		];
		
		/**
		 * Add defaults without chainging the core values.
		 *
		 * @param array $defaults
		 *
		 * @since 3.3.11
		 */
		$defaults = wp_parse_args( apply_filters( 'woo_feed_settings_extra_defaults', [] ), $defaults );
		
		if ( 'defaults' === $key ) {
			return $defaults;
		}
		
		$settings = wp_parse_args( get_option( 'woo_feed_settings', [] ), $defaults );
		
		if ( 'all' === $key ) {
			return $settings;
		}
		
		if ( array_key_exists( $key, $settings ) ) {
			return $settings[ $key ];
		}
		
		return $default;
	}
}
if ( ! function_exists( 'woo_feed_save_options' ) ) {
	/**
	 * Save Settings.
	 *
	 * @param array $args Required. option key value paired array to save.
	 *
	 * @return bool
	 * @since 3.3.11
	 */
	function woo_feed_save_options( $args ) {
		$data = woo_feed_get_options( 'all' );
		$defaults = woo_feed_get_options( 'defaults' );
		$_data = $data;
		
		if ( array_key_exists( 'per_batch', $args ) ) {
			$data['per_batch'] = absint( $args['per_batch'] );
			if ( $data['per_batch'] <= 0 ) {
				$data['per_batch'] = $_data['per_batch'] > 0 ? $_data['per_batch'] : $defaults['per_batch'];
			}
			unset( $args['unset'] );
		}
		if ( array_key_exists( 'product_query_type', $args ) ) {
			$data['product_query_type'] = strtolower( $args['product_query_type'] );
			$query_types                = array_keys( woo_feed_get_query_type_options() );
			if ( ! in_array( $data['product_query_type'], $query_types ) ) {
				$data['product_query_type'] = in_array( $_data['product_query_type'], $query_types ) ? $_data['product_query_type'] : $defaults['product_query_type'];
			}
			unset( $args['product_query_type'] );
		}
		if ( array_key_exists( 'enable_error_debugging', $args ) ) {
			$data['enable_error_debugging'] = strtolower( $args['enable_error_debugging'] );
			if ( ! in_array( $data['enable_error_debugging'], [ 'on', 'off' ] ) ) {
				$data['enable_error_debugging'] = in_array( $_data['enable_error_debugging'], [ 'on', 'off' ] ) ? $_data['enable_error_debugging'] : $defaults['enable_error_debugging'];
			}
			unset( $args['enable_error_debugging'] );
		}
		if ( array_key_exists( 'cache_ttl', $args ) ) {
			$data['cache_ttl'] = absint( $args['cache_ttl'] ); // cache ttl can be zero.
			unset( $args['cache_ttl'] );
		}
		if ( ! empty( $args ) ) {
			foreach ( $args as $key => $value ) {
				if ( has_filter( "woo_feed_save_{$key}_option" ) ) {
					$data[ $key ] = apply_filters( "woo_feed_save_{$key}_option", sanitize_text_field( $value ) );
				}
			}
		}
		
		return update_option( 'woo_feed_settings', $data, false );
	}
}
if ( ! function_exists( 'woo_feed_reset_options' ) ) {
	/**
	 * Restore the default settings.
	 *
	 * @return bool
	 * @since 3.3.11
	 */
	function woo_feed_reset_options() {
		return update_option( 'woo_feed_settings', woo_feed_get_options( 'defaults' ), false );
	}
}

// Caching. Wrapper for Transient API.
if ( ! function_exists( 'woo_feed_get_cached_data' ) ) {
	/**
	 * Get Cached Data
	 *
	 * @param string $key   Cache Name
	 *
	 * @return mixed|false  false if cache not found.
	 * @since 3.3.10
	 */
	function woo_feed_get_cached_data( $key ) {
		if ( empty( $key ) ) {
			return false;
		}
		
		return get_transient( '__woo_feed_cache_' . $key );
	}
}
if ( ! function_exists( 'woo_feed_set_cache_data' ) ) {
	/**
	 *
	 * @param string   $key        Cache name. Expected to not be SQL-escaped. Must be
	 *                             172 characters or fewer in length.
	 * @param mixed    $data       Data to cache. Must be serializable if non-scalar.
	 *                             Expected to not be SQL-escaped.
	 * @param int|bool $expiration Optional. Time until expiration in seconds. Default 0 (no expiration).
	 *
	 * @return bool
	 */
	function woo_feed_set_cache_data( $key, $data, $expiration = false ) {
		
		if ( empty( $key ) ) {
			return false;
		}
		
		if ( false === $expiration ) $expiration = WOO_FEED_CACHE_TTL;
		
		return set_transient( '__woo_feed_cache_' . $key, $data, (int) $expiration );
	}
}
if ( ! function_exists( 'woo_feed_delete_cache_data' ) ) {
	/**
	 * Delete Cached Data
	 * @param string $key  cache name.
	 *
	 * @return bool
	 */
	function woo_feed_delete_cache_data( $key ) {
		if ( empty( $key ) ) {
			return false;
		}
		
		return delete_transient( '__woo_feed_cache_' . $key );
	}
}
if ( ! function_exists( 'woo_feed_flush_cache_data' ) ) {
	/**
	 * Delete All Cached Data
	 *
	 * @return void
	 */
	function woo_feed_flush_cache_data() {
		global $wpdb;
//		$wpdb->query( "DELETE FROM $wpdb->options WHERE {$wpdb->options}.option_name LIKE '_transient___woo_feed_cache_%' " ); // phpcs:ignore
//		$wpdb->query( "DELETE FROM $wpdb->options WHERE {$wpdb->options}.option_name LIKE '_transient_timeout___woo_feed_cache_%'" ); // phpcs:ignore
        $wpdb->query( "DELETE FROM $wpdb->options WHERE ({$wpdb->options}.option_name LIKE '_transient_timeout___woo_feed_cache_%') OR ({$wpdb->options}.option_name LIKE '_transient___woo_feed_cache_%')" ); // phpcs:ignore
	}
}

// Price And Tax.
if ( ! function_exists( 'woo_feed_apply_tax_location_data' ) ) {
	/**
	 * Filter and Change Location data for tax calculation
	 *
	 * @param array       $location Location array.
	 * @param string      $tax_class Tax class.
	 * @param WC_Customer $customer WooCommerce Customer Object.
	 *
	 * @return array
	 */
	function woo_feed_apply_tax_location_data( $location, $tax_class, $customer ) {
		// @TODO use filter. add tab in feed editor so user can set custom settings.
		// @TODO tab should not list all country and cities. it only list available tax settings and user can just select one.
		// @TODO then it will extract the location data from it to use here.
		$wc_tax_location = [
			WC()->countries->get_base_country(),
			WC()->countries->get_base_state(),
			WC()->countries->get_base_postcode(),
			WC()->countries->get_base_city(),
		];
		/**
		 * Filter Tax Location to apply before product loop
		 *
		 * @param array $tax_location
		 *
		 * @since 3.3.0
		 */
		$tax_location = apply_filters( 'woo_feed_tax_location_data', $wc_tax_location );
		if ( ! is_array( $tax_location ) || ( is_array( $tax_location ) && 4 !== count( $tax_location ) ) ) {
			$tax_location = $wc_tax_location;
		}
		
		return $tax_location;
	}
}

// Hook feed generating process...
if ( ! function_exists( 'woo_feed_apply_hooks_before_product_loop' ) ) {
	/**
	 * Apply Hooks Before Looping through ProductIds
	 *
	 * @param int[] $productIds product id array.
	 * @param array $feedConfig feed config array.
	 */
	function woo_feed_apply_hooks_before_product_loop( $productIds, $feedConfig ) {
		add_filter( 'woocommerce_get_tax_location', 'woo_feed_apply_tax_location_data', 10, 3 );
	}
}
if ( ! function_exists( 'woo_feed_remove_hooks_before_product_loop' ) ) {
	/**
	 * Remove Applied Hooks Looping through ProductIds
	 *
	 * @param int[] $productIds product id array.
	 * @param array $feedConfig feed config array.
	 *
	 * @see woo_feed_apply_hooks_before_product_loop
	 */
	function woo_feed_remove_hooks_before_product_loop( $productIds, $feedConfig ) {
		remove_filter( 'woocommerce_get_tax_location', 'woo_feed_apply_tax_location_data', 10 );
	}
}
if ( ! function_exists( 'woo_feed_product_taxonomy_term_separator' ) ) {
	/**
	 * Filter Product local category (type) separator
	 *
	 * @param string $separator
	 * @param array  $config
	 *
	 * @return string
	 */
	function woo_feed_product_taxonomy_term_separator( $separator, $config ) {
		if ( 'trovaprezzi' === $config['provider'] ) {
			$separator = ',';
		}
		
		if ( false !== strpos( $config['provider'], 'daisycon' ) ) {
			$separator = '|';
		}
		
		return $separator;
	}
}
if ( ! function_exists( 'woo_feed_get_availability_attribute_filter' ) ) {
	/**
	 * Filter Product Availability Attribute Output For Template
	 *
	 * @param string     $output    Output string.
	 * @param WC_Product $product   Product Object
	 * @param array      $config    Feed Config
	 *
	 * @return int
	 */
	function woo_feed_get_availability_attribute_filter( $output, $product, $config ) {
		$status = $product->get_stock_status();
		$provider = $config['provider'];
		
		if ( 'trovaprezzi' === $provider ) {
			$output = 2;
			if ( $status ) {
				if ( 'instock' == $status ) {
					$output = 2;
				} elseif ( 'outofstock' == $status ) {
					$output = 0;
				} elseif ( 'onbackorder' == $status ) {
					$output = 1;
				}
			}
		}
		
		if ( false !== strpos( $provider, 'daisycon' ) ) {
			$output = 'true';
			if ( $status ) {
				if ( 'instock' == $status ) {
					$output = 'true';
				} elseif ( 'outofstock' == $status ) {
					$output = 'false';
				} elseif ( 'onbackorder' == $status ) {
					$output = 'false';
				}
			}
		}
		
		return $output;
	}
}

// Parse feed rules.
if ( ! function_exists( 'woo_feed_filter_parsed_rules' ) ) {
	/**
	 * Filter Feed parsed rules
	 *
	 * @param array  $rules     Feed Config
	 * @param string $context   Parsing context
	 *
	 * @return array
	 * @since 3.3.7
	 */
	function woo_feed_filter_parsed_rules( $rules, $context ) {
		$provider = $rules['provider'];
		if ( 'create' === $context ) {
			if ( 'criteo' === $provider ) {
				$rules['itemsWrapper'] = 'channel';
				$rules['itemWrapper']  = 'item';
			}
			
			if ( 'trovaprezzi' === $provider ) {
				$rules['decimal_separator']  = ',';
				$rules['thousand_separator'] = '';
				$rules['decimals']           = 2;
				$rules['itemsWrapper']       = 'Products';
				$rules['itemWrapper']        = 'Offer';
				$rules['delimiter']          = '|';
				$rules['enclosure']          = ' ';
			}
			
			if ( false !== strpos( $provider, 'daisycon' ) ) {
				$rules['itemsWrapper'] = 'channel';
				$rules['itemWrapper']  = 'item';
			}
		}
		
		return $rules;
	}
}

if ( ! function_exists( 'woo_feed_category_mapping' ) ) {
    /**
     * Category Mapping
     */
    function woo_feed_category_mapping() {
        // Manage action for category mapping.
        if ( isset( $_GET['action'], $_GET['cmapping'] ) && 'edit-mapping' == $_GET['action'] ) {
            if ( count( $_POST ) && isset( $_POST['mappingname'] ) && isset( $_POST['edit-mapping'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
                check_admin_referer( 'category-mapping' );

                $mappingOption = sanitize_text_field( $_POST['mappingname'] );
                $mappingOption = 'wf_cmapping_' . sanitize_title( $mappingOption );
                $mappingData = woo_feed_array_sanitize( $_POST );
                $oldMapping = maybe_unserialize( get_option( $mappingOption, array() ) );

                # Delete product attribute drop-down cache
                delete_transient( '__woo_feed_cache_woo_feed_dropdown_product_attributes');

                if ( $oldMapping === $mappingData ) {
                    update_option( 'wpf_message', esc_html__( 'Mapping Not Changed', 'woo-feed' ), false );
                    wp_safe_redirect( admin_url( 'admin.php?page=webappick-feed-category-mapping&wpf_message=warning' ) );
                    die();
                }

                if ( update_option( $mappingOption, serialize( $mappingData ), false ) ) { // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
                    update_option( 'wpf_message', esc_html__( 'Mapping Updated Successfully', 'woo-feed' ), false );
                    wp_safe_redirect( admin_url( 'admin.php?page=webappick-feed-category-mapping&wpf_message=success' ) );
                    die();
                } else {
                    update_option( 'wpf_message', esc_html__( 'Failed To Updated Mapping', 'woo-feed' ), false );
                    wp_safe_redirect( admin_url( 'admin.php?page=webappick-feed-category-mapping&wpf_message=error' ) );
                    die();
                }
            }
            require WOO_FEED_FREE_ADMIN_PATH . 'partials/woo-feed-category-mapping.php';
        } elseif ( isset( $_GET['action'] ) && 'add-mapping' == $_GET['action'] ) {
            if ( count( $_POST ) && isset( $_POST['mappingname'] ) && isset( $_POST['add-mapping'] ) ) {
                check_admin_referer( 'category-mapping' );

                $mappingOption = 'wf_cmapping_' . sanitize_text_field( $_POST['mappingname'] );

                # Delete product attribute drop-down cache
                delete_transient( '__woo_feed_cache_woo_feed_dropdown_product_attributes');

                if ( false !== get_option( $mappingOption, false ) ) {
                    update_option( 'wpf_message', esc_html__( 'Another category mapping exists with the same name.', 'woo-feed' ), false );
                    wp_safe_redirect( admin_url( 'admin.php?page=webappick-feed-category-mapping&wpf_message=warning' ) );
                    die();
                }
                if ( update_option( $mappingOption, serialize( woo_feed_array_sanitize( $_POST ) ), false ) ) { // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
                    update_option( 'wpf_message', esc_html__( 'Mapping Added Successfully', 'woo-feed' ), false );
                    wp_safe_redirect( admin_url( 'admin.php?page=webappick-feed-category-mapping&wpf_message=success' ) );
                    die();
                } else {
                    update_option( 'wpf_message', esc_html__( 'Failed To Add Mapping', 'woo-feed' ), false );
                    wp_safe_redirect( admin_url( 'admin.php?page=webappick-feed-category-mapping&wpf_message=error' ) );
                    die();
                }
            }
            require WOO_FEED_FREE_ADMIN_PATH . 'partials/woo-feed-category-mapping.php';
        } else {
            require WOO_FEED_FREE_ADMIN_PATH . 'partials/woo-feed-category-mapping-list.php';
        }
    }
}

// Category mapping.
if ( ! function_exists( 'woo_feed_render_categories' ) ) {
    /**
     * Get Product Categories
     *
     * @param int    $parent Parent ID.
     * @param string $par separator.
     * @param string $value mapped values.
     */
    function woo_feed_render_categories( $parent = 0, $par = '', $value = '' ) {
        $categoryArgs = [
            'taxonomy'     => 'product_cat',
            'parent'       => $parent,
            'orderby'      => 'term_group',
            'show_count'   => 1,
            'pad_counts'   => 1,
            'hierarchical' => 1,
            'title_li'     => '',
            'hide_empty'   => 0,
        ];
        $categories   = get_categories( $categoryArgs );
        if ( ! empty( $categories ) ) {
            if ( ! empty( $par ) ) {
                $par = $par . ' > ';
            }
            foreach ( $categories as $cat ) {
                $class = $parent ? "treegrid-parent-{$parent} category-mapping" : 'treegrid-parent category-mapping';
                ?>
                <tr class="treegrid-1 ">
                    <th>
                        <label for="cat_mapping_<?php echo esc_attr( $cat->term_id ); ?>"><?php echo esc_html( $par . $cat->name ); ?></label>
                    </th>
                    <td><!--suppress HtmlUnknownAttribute -->
                        <input id="cat_mapping_<?php echo esc_attr( $cat->term_id ); ?>"
                               class="<?php echo esc_attr( $class ); ?> woo-feed-mapping-input"
                               autocomplete="off"
                               type="text"
                               name="cmapping[<?php echo esc_attr( $cat->term_id ); ?>]"
                               placeholder="<?php echo esc_attr( $par . $cat->name ); ?>"
                               data-cat_id="<?php echo esc_attr( $cat->term_id ); ?>"
                               value="<?php echo is_array( $value ) && isset( $value['cmapping'][ $cat->term_id ] ) ? esc_attr( $value['cmapping'][ $cat->term_id ] ) : ''; ?>"
                        >
                    </td>
                </tr>
                <?php
                // call and render the child category if any.
                woo_feed_render_categories( $cat->term_id, $par . $cat->name, $value );
            }
        }
    }
}

if ( ! function_exists( 'woo_feed_get_category_mapping_value' ) ) {
    /**
     * Return Category Mapping Values by Parent Product Id
     *
     * @param string $cmappingName Category Mapping Name
     * @param int    $parent Parent id of the product
     *
     * @return mixed
     */
    function woo_feed_get_category_mapping_value( $cmappingName, $parent ) {
        $getValue = maybe_unserialize( get_option( $cmappingName ) );
        if ( ! isset( $getValue['cmapping'] ) ) {
            return '';
        }
        $cmapping   = is_array( $getValue['cmapping'] ) ? array_reverse( $getValue['cmapping'], true ) : $getValue['cmapping'];
        $categories = '';
        if ( get_the_terms( $parent, 'product_cat' ) ) {
            $categories = array_reverse( get_the_terms( $parent, 'product_cat' ) );
        }
        if ( ! empty( $categories ) && is_array( $categories ) && count( $categories ) ) {
            foreach ( $categories as $key => $category ) {
                if ( isset( $cmapping[ $category->term_id ] ) && ! empty( $cmapping[ $category->term_id ] ) ) {
                    return $cmapping[ $category->term_id ];
                } else {
                    return '';
                }
            }
        }

        return '';
    }
}


if ( ! function_exists( 'woo_feed_add_identifier_fields' ) ) {
    /**
     * Add Custom fields into product inventory tab for Unique Identifier (GTIN,MPN,EAN)
     *
     * @since 3.7.8
     */
    function woo_feed_add_identifier_fields() {

        echo '<div class="options_group">';
        echo sprintf( '<h4 class="%s" style="padding-left: 10px;">%s</h4>', esc_attr( 'woo-feed-option-title' ), esc_html( 'Unique Identifier - Woo Feed', 'woo-feed' ) );

        //GTIN input field
        woocommerce_wp_text_input( array(
            'id'          => 'woo_feed_gtin',
            'value'       => get_post_meta( get_the_ID(), 'woo_feed_gtin', true ),
            'placeholder' => esc_html( 'Set product GTIN', 'woo-feed' ),
            'label'       => esc_html( 'GTIN', 'woo-feed' ),
            'desc_tip'    => true,
            'description' => esc_html( 'Set product GTIN code here.', 'woo-feed' ),
        ) );

        //MPN input field
        woocommerce_wp_text_input( array(
            'id'          => 'woo_feed_mpn',
            'value'       => get_post_meta( get_the_ID(), 'woo_feed_mpn', true ),
            'placeholder' => esc_html( 'Set product MPN', 'woo-feed' ),
            'label'       => esc_html( 'MPN', 'woo-feed' ),
            'desc_tip'    => true,
            'description' => esc_html( 'Set product MPN code here.', 'woo-feed' ),
        ) );

        //EAN input field
        woocommerce_wp_text_input( array(
            'id'          => 'woo_feed_ean',
            'value'       => get_post_meta( get_the_ID(), 'woo_feed_ean', true ),
            'placeholder' => esc_html( 'Set product EAN', 'woo-feed' ),
            'label'       => esc_html( 'EAN', 'woo-feed' ),
            'desc_tip'    => true,
            'description' => esc_html( 'Set product EAN code here.', 'woo-feed' ),
        ) );

        echo '</div>';

    }
    add_action( 'woocommerce_product_options_inventory_product_data', 'woo_feed_add_identifier_fields');
}

if ( ! function_exists( 'woo_feed_save_identifier_fields_data' ) ) {

    /**
     * Updating custom fields data. (Unique Identifier (GTIN,MPN,EAN))
     *
     * @param int $id Post Id
     * @param WP_Post $post Wp Post Object.
     * @since 3.7.8
     */
    function woo_feed_save_identifier_fields_data( $id, $post ) {

        //save gtin fields value
        if ( isset( $_POST['woo_feed_gtin'] ) && ! empty( $_POST['woo_feed_gtin'] ) ) {
            update_post_meta( $id, 'woo_feed_gtin', $_POST['woo_feed_gtin'] );
        } else {
            delete_post_meta( $id, 'woo_feed_gtin' );
        }

        //save mpn fields value
        if ( isset( $_POST['woo_feed_mpn'] ) && ! empty( $_POST['woo_feed_mpn'] ) ) {
            update_post_meta( $id, 'woo_feed_mpn', $_POST['woo_feed_mpn'] );
        } else {
            delete_post_meta( $id, 'woo_feed_mpn' );
        }

        //save ean fields value
        if ( isset( $_POST['woo_feed_ean'] ) && ! empty( $_POST['woo_feed_ean'] ) ) {
            update_post_meta( $id, 'woo_feed_ean', $_POST['woo_feed_ean'] );
        } else {
            delete_post_meta( $id, 'woo_feed_ean' );
        }
    }

    add_action( 'woocommerce_process_product_meta', 'woo_feed_save_identifier_fields_data', 10, 2 );
}

if ( ! function_exists( 'woo_feed_add_identifier_fields_for_variation' ) ) {

    /**
     * Custom options in variation tab, here we are putting gtin, mpn, ean input fields in product variation tab
     *
     * @param int $loop Variation loop index.
     * @param array $variation_data Variation info.
     * @param WP_Post $variation Post Object.
     *
     * @since 3.7.8
     */
    function woo_feed_add_identifier_fields_for_variation( $loop, $variation_data, $variation ) {

        echo '<div class="woo-feed-variation-options">';
        echo sprintf( '<h4 class="%s">%s</h4>', esc_attr( 'woo-feed-variation-option-title' ), esc_html( 'Unique Identifier - Woo Feed', 'woo-feed' ) );
        ?>
        <style>
            .woo-feed-variation-options {
                border-top: 1px solid #ccc;
                margin-top: 20px;
            }
            .woo-feed-variation-options h4 {
                margin-bottom: 0;
            }
            .woo-feed-variation-options .form-field input[type="text"] {
                width: 100%;
                padding: 5px;
            }
            .woo-feed-variation-items {
                display: flex;
                flex-wrap: wrap;
            }
            .woo-feed-variation-items p {
                width: 33.33%;
                padding: 0 10px;
                box-sizing: border-box;
            }
            .woo-feed-variation-items p:first-child,.woo-feed-variation-items p:last-child {
                padding: 0;
            }
        </style>
        <?php

        echo '<div class="woo-feed-variation-items">';
        //GTIN variation input field
        woocommerce_wp_text_input( array(
            'id'          => "woo_feed_gtin_var[$variation->ID]",
            'value'       => get_post_meta( $variation->ID, "woo_feed_gtin_var", true ),
            'placeholder' => esc_html( 'Set product GTIN', 'woo-feed' ),
            'label'       => esc_html( 'GTIN', 'woo-feed' ),
        ) );

        //MPN variation input field
        woocommerce_wp_text_input( array(
            'id'          => "woo_feed_mpn_var[$variation->ID]",
            'value'       => get_post_meta( $variation->ID, 'woo_feed_mpn_var', true ),
            'placeholder' => esc_html( 'Set product MPN', 'woo-feed' ),
            'label'       => esc_html( 'MPN', 'woo-feed' ),
        ) );

        //EAN variation input field
        woocommerce_wp_text_input( array(
            'id'          => "woo_feed_ean_var[$variation->ID]",
            'value'       => get_post_meta( $variation->ID, 'woo_feed_ean_var', true ),
            'placeholder' => esc_html( 'Set product EAN', 'woo-feed' ),
            'label'       => esc_html( 'EAN', 'woo-feed' ),
        ) );

        echo '</div></div>';

    }
    add_action( 'woocommerce_product_after_variable_attributes', 'woo_feed_add_identifier_fields_for_variation', 10, 3 );
}

if ( ! function_exists( 'woo_feed_save_identifier_fields_data_for_variation' ) ) {

    /**
     * Saving variation custom fields.
     *
     * @param int $variation_id Variation Id.
     * @param int $i variations loop index.
     *
     * @since 3.7.8
     */
    function woo_feed_save_identifier_fields_data_for_variation( $variation_id, $i ) {

        //save gtin field
        if ( isset($_POST['woo_feed_gtin_var'][ $variation_id ]) ) {
            $woo_feed_gtin_field = $_POST['woo_feed_gtin_var'][ $variation_id ];
            if ( isset( $woo_feed_gtin_field ) ) update_post_meta( $variation_id, 'woo_feed_gtin_var', esc_attr( $woo_feed_gtin_field ) );
        }

        //save mpn field
        if ( isset($_POST['woo_feed_mpn_var'][ $variation_id ]) ) {
            $woo_feed_mpn_field = $_POST['woo_feed_mpn_var'][ $variation_id ];
            if ( isset( $woo_feed_mpn_field ) ) update_post_meta( $variation_id, 'woo_feed_mpn_var', esc_attr( $woo_feed_mpn_field ) );
        }

        //save ean field
        if ( isset($_POST['woo_feed_ean_var'][ $variation_id ]) ) {
            $woo_feed_ean_field = $_POST['woo_feed_ean_var'][ $variation_id ];
            if ( isset( $woo_feed_ean_field ) ) update_post_meta( $variation_id, 'woo_feed_ean_var', esc_attr( $woo_feed_ean_field ) );
        }

    }
    add_action( 'woocommerce_save_product_variation', 'woo_feed_save_identifier_fields_data_for_variation', 10, 2 );
}


if ( ! function_exists( 'woo_feed_clear_cache_button' ) ) {
    /**
     * Clear cache button.
     *
     * @return void
     * @since 4.1.2
     */
    function woo_feed_clear_cache_button() {
        ?>
        <div class="wf_clean_cache_wrapper">
            <img class="woo-feed-cache-loader" src="data:image/svg+xml,%3C%3Fxml%20version%3D%221.0%22%20encoding%3D%22iso-8859-1%22%3F%3E%0D%0A%3C%21--%20Generator%3A%20Adobe%20Illustrator%2019.0.0%2C%20SVG%20Export%20Plug-In%20.%20SVG%20Version%3A%206.00%20Build%200%29%20%20--%3E%0D%0A%3Csvg%20version%3D%221.1%22%20id%3D%22Capa_1%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20xmlns%3Axlink%3D%22http%3A%2F%2Fwww.w3.org%2F1999%2Fxlink%22%20x%3D%220px%22%20y%3D%220px%22%0D%0A%09%20viewBox%3D%220%200%20458.186%20458.186%22%20style%3D%22enable-background%3Anew%200%200%20458.186%20458.186%3B%22%20xml%3Aspace%3D%22preserve%22%3E%0D%0A%3Cg%3E%0D%0A%09%3Cg%3E%0D%0A%09%09%3Cpath%20d%3D%22M445.651%2C201.95c-1.485-9.308-10.235-15.649-19.543-14.164c-9.308%2C1.485-15.649%2C10.235-14.164%2C19.543%0D%0A%09%09%09c0.016%2C0.102%2C0.033%2C0.203%2C0.051%2C0.304c17.38%2C102.311-51.47%2C199.339-153.781%2C216.719c-102.311%2C17.38-199.339-51.47-216.719-153.781%0D%0A%09%09%09S92.966%2C71.232%2C195.276%2C53.852c62.919-10.688%2C126.962%2C11.29%2C170.059%2C58.361l-75.605%2C25.19%0D%0A%09%09%09c-8.944%2C2.976-13.781%2C12.638-10.806%2C21.582c0.001%2C0.002%2C0.002%2C0.005%2C0.003%2C0.007c2.976%2C8.944%2C12.638%2C13.781%2C21.582%2C10.806%0D%0A%09%09%09c0.003-0.001%2C0.005-0.002%2C0.007-0.002l102.4-34.133c6.972-2.322%2C11.675-8.847%2C11.674-16.196v-102.4%0D%0A%09%09%09C414.59%2C7.641%2C406.949%2C0%2C397.523%2C0s-17.067%2C7.641-17.067%2C17.067v62.344C292.564-4.185%2C153.545-0.702%2C69.949%2C87.19%0D%0A%09%09%09s-80.114%2C226.911%2C7.779%2C310.508s226.911%2C80.114%2C310.508-7.779C435.905%2C339.799%2C457.179%2C270.152%2C445.651%2C201.95z%22%2F%3E%0D%0A%09%3C%2Fg%3E%0D%0A%3C%2Fg%3E%0D%0A%3Cg%3E%0D%0A%3C%2Fg%3E%0D%0A%3Cg%3E%0D%0A%3C%2Fg%3E%0D%0A%3Cg%3E%0D%0A%3C%2Fg%3E%0D%0A%3Cg%3E%0D%0A%3C%2Fg%3E%0D%0A%3Cg%3E%0D%0A%3C%2Fg%3E%0D%0A%3Cg%3E%0D%0A%3C%2Fg%3E%0D%0A%3Cg%3E%0D%0A%3C%2Fg%3E%0D%0A%3Cg%3E%0D%0A%3C%2Fg%3E%0D%0A%3Cg%3E%0D%0A%3C%2Fg%3E%0D%0A%3Cg%3E%0D%0A%3C%2Fg%3E%0D%0A%3Cg%3E%0D%0A%3C%2Fg%3E%0D%0A%3Cg%3E%0D%0A%3C%2Fg%3E%0D%0A%3Cg%3E%0D%0A%3C%2Fg%3E%0D%0A%3Cg%3E%0D%0A%3C%2Fg%3E%0D%0A%3Cg%3E%0D%0A%3C%2Fg%3E%0D%0A%3C%2Fsvg%3E%0D%0A" alt="loader">
            <input type="hidden" class="woo-feed-clean-cache-nonce" value="<?php echo wp_create_nonce( 'clean_cache_nonce', 'clean_cache_nonce' ); ?>">
            <button type="button"><?php esc_html_e( 'Clear Cache', 'woo-feed' ); ?></button>
        </div>
        <?php
    }
}

if ( ! function_exists( 'woo_feed_clear_cache_data' ) ) {
    /**
     * Clear cache data.
     *
     * @param int _ajax_clean_nonce nonce number.
     *
     * @since 4.1.2
     */
    function woo_feed_clear_cache_data() {
        if ( isset( $_REQUEST['_ajax_clean_nonce'] ) ) {

            if ( wp_verify_nonce( sanitize_text_field( $_REQUEST['_ajax_clean_nonce'] ), 'clean_cache_nonce' ) ) {
                $data = [];

                global $wpdb;
                $wpdb->query( "DELETE FROM $wpdb->options WHERE ({$wpdb->options}.option_name LIKE '_transient_timeout___woo_feed_cache_%') OR ({$wpdb->options}.option_name LIKE '_transient___woo_feed_cache_%')" ); // phpcs:ignore

                $data = [ 'success' => true ];

                wp_send_json_success( $data );
            }        
} else {
            wp_send_json_error( esc_html__( 'Invalid Request.', 'woo-feed' ) );
        }
        wp_die();
    }
}
add_action( 'wp_ajax_clear_cache_data', 'woo_feed_clear_cache_data' );


// End of file helper.php.
