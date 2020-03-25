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
	 * @param mixed $value Value.
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
	 * @param bool $silent Prevent calling deactivation hooks. Default is false.
	 * @param mixed $network_wide Whether to deactivate the plugin for all sites in the network.
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
			if ( version_compare( $currentVersion, $version, ">=" ) ) {
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
			if ( version_compare( ICL_SITEPRESS_VERSION, $version, ">=" ) ) {
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
		//@TODO Refactor this function with admin message class
		// WC Missing Notice..
		if ( ! wooFeed_check_WC() ) {
			$plugin_url = self_admin_url( 'plugin-install.php?s=woocommerce&tab=search&type=term' );
			/** @noinspection HtmlUnknownTarget */
			$plugin_url  = sprintf( '<a href="%s">%s</a>', $plugin_url, esc_html__( 'WooCommerce', 'woo-feed' ) );
			$plugin_name = sprintf( '<code>%s</code>', esc_html__( 'WooCommerce Product Feed', 'woo-feed' ) );
			$wc_name     = sprintf( '<code>%s</code>', esc_html__( 'WooCommerce', 'woo-feed' ) );
			/* translators: 1: this plugin name, 2: required plugin name, 3: required plugin name and installation url */
			$message = sprintf( esc_html__( '%1$s requires %2$s to be installed and active. You can installed/activate %3$s here.',
				'woo-feed' ),
				$plugin_name,
				$wc_name,
				$plugin_url );
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
			/* translators: 1: this plugin name, 2: required plugin name, 3: required plugin required version, 4: required plugin current version, 5: required plugin update url and name */
			$message = sprintf( esc_html__( '%1$s requires %2$s version %3$s or above and %4$s found. Please upgrade %2$s to the latest version here %5$s',
				'woo-feed' ),
				$plugin_name,
				$wc_name,
				$minVersion,
				$wcVersion,
				$plugin_url );
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
	 * @param array $input
	 * @param string|int $offset
	 * @param string|int $length
	 * @param array $replacement
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
		
		$input = array_slice( $input, 0, $offset, true ) + $replacement + array_slice( $input,
				$offset + $length,
				null,
				true );
		
		return $input;
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
	 * @param array $info
	 *
	 * @return bool
	 */
	function woo_feed_handle_file_transfer( $fileFrom, $fileTo, $info ) {
		try {
			// Upload file to ftp server.
			if ( 1 == (int) $info['ftpenabled'] ) {
				if ( ! file_exists( $fileFrom ) ) {
//				    woo_feed_log_feed_process( $info['filename'], 'Unable to process file transfer request. File does not exists.' );
					return false;
				}
				$ftpHost      = sanitize_text_field( $info['ftphost'] );
				$ftp_user     = sanitize_text_field( $info['ftpuser'] );
				$ftp_password = sanitize_text_field( $info['ftppassword'] );
				$ftpPath      = trailingslashit( untrailingslashit( sanitize_text_field( $info['ftppath'] ) ) );
				$ftporsftp    = isset( $info['ftporsftp'] ) ? sanitize_text_field( $info['ftporsftp'] ) : 'ftp';
				$ftp_port     = isset( $info['ftpport'] ) && ! empty( $info['ftpport'] ) ? absint( $info['ftpport'] ) : 21;
//				woo_feed_log_feed_process( $info['filename'], sprintf( 'Uploading Feed file via %s.', $ftporsftp ) );
				if ( 'ftp' == $ftporsftp ) {
					$ftp = new FTPClient();
					if ( $ftp->connect( $ftpHost, $ftp_user, $ftp_password, false, $ftp_port ) ) {
						return $ftp->upload_file( $fileFrom, $ftpPath . $fileTo );
					}
				} elseif ( 'sftp' == $ftporsftp ) {
					$sftp = new SFTPConnection( $ftpHost, $ftp_port );
					$sftp->login( $ftp_user, $ftp_password );
					
					return $sftp->upload_file( $fileFrom, $fileTo, $ftpPath );
				}
			}
			
			return false;
		} catch ( Exception $e ) {
//			$message = 'Error Uploading Feed Via ' . $ftporsftp . PHP_EOL . 'Caught Exception :: ' . $e->getMessage();
//			woo_feed_log( $info['filename'], $message, 'critical', $e, true );
//			woo_feed_log_fatal_error( $message, $e );
			return false;
		}
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
				$arr   = explode( '.', $url['host'] );
				$brand = $arr[ count( $arr ) - 2 ];
				$brand = ucfirst( $brand );
			}
		}
		
		return apply_filters( 'woo_feed_get_default_brand_name', $brand );
	}
}

// The Editor.
if ( ! function_exists( 'woo_feed_get_custom2_merchant' ) ) {
	/**
	 * Get Merchant list that are allowed on Custom2 Template
	 * @return array
	 */
	function woo_feed_get_custom2_merchant() {
		return array( 'custom2', 'admarkt', 'yandex_xml' );
	}
}
if ( ! function_exists( 'woo_feed_parse_feed_rules' ) ) {
	/**
	 * Parse Feed Config/Rules to make sure that necessary array keys are exists
	 * this will reduce the uses of isset() checking
	 *
	 * @param array $rules rules to parse.
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
			'provider'              => '',
			'filename'              => '',
			'feedType'              => '',
			'ftpenabled'            => 0,
			'ftporsftp'             => 'ftp',
			'ftphost'               => '',
			'ftpport'               => '21',
			'ftpuser'               => '',
			'ftppassword'           => '',
			'ftppath'               => '',
			'is_variations'         => 'n',
			'variable_price'        => 'first',
			'variable_quantity'     => 'first',
			'feedLanguage'          => apply_filters( 'wpml_current_language', null ),
			'feedCurrency'          => get_woocommerce_currency(),
			'itemsWrapper'          => 'products',
			'itemWrapper'           => 'product',
			'delimiter'             => ',',
			'enclosure'             => 'double',
			'extraHeader'           => '',
			'vendors'               => [],
			// Feed Config
			'mattributes'           => [], // merchant attributes
			'prefix'                => [], // prefixes
			'type'                  => [], // value (attribute) types
			'attributes'            => [], // product attribute mappings
			'default'               => [], // default values (patterns) if value type set to pattern
			'suffix'                => [], // suffixes
			'output_type'           => [], // output type (output filter)
			'limit'                 => [], // limit or command
			// filters tab
			'composite_price'       => '',
			'product_ids'           => '',
			'categories'            => [],
			'post_status'           => [ 'publish' ],
			'filter_mode'           => [],
			'campaign_parameters'   => [],
			'is_outOfStock'         => 'n',
			'product_visibility'    => 0,
			// include hidden ? 1 yes 0 no
			'outofstock_visibility' => 0,
			// override wc global option for out-of-stock product hidden from catalog? 1 yes 0 no
			'ptitle_show'           => '',
			'decimal_separator'     => wc_get_price_decimal_separator(),
			'thousand_separator'    => wc_get_price_thousand_separator(),
			'decimals'              => wc_get_price_decimals(),
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
if ( ! function_exists( 'register_and_do_woo_feed_meta_boxes' ) ) {
	/**
	 * Registers the default Feed Editor MetaBoxes, and runs the `do_meta_boxes` actions.
	 *
	 * @param string|WP_Screen $screen Screen identifier. If you have used add_menu_page() or
	 *                                      add_submenu_page() to create a new screen (and hence screen_id)
	 *                                      make sure your menu slug conforms to the limits of sanitize_key()
	 *                                      otherwise the 'screen' menu may not correctly render on your page.
	 * @param array $feedRules current feed being processed.
	 *
	 * @return void
	 * @see register_and_do_post_meta_boxes()
	 *
	 * @since 3.2.6
	 *
	 */
	function register_and_do_woo_feed_meta_boxes( $screen, $feedRules = array() ) {
		if ( empty( $screen ) ) {
			$screen = get_current_screen();
		} elseif ( is_string( $screen ) ) {
			$screen = convert_to_screen( $screen );
		}
		// edit page MetaBoxes
		if ( 'woo-feed_page_webappick-new-feed' === $screen->id || 'toplevel_page_webappick-manage-feeds' === $screen->id ) {
			add_meta_box( 'feed_merchant_info',
				'Feed Merchant Info',
				'woo_feed_merchant_info_metabox',
				null,
				'side',
				'default' );
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
                        <span class="dashicons dashicons-media-document" style="color: #82878c;"
                              aria-hidden="true"></span>
                        <span><?php esc_html_e( 'Feed Specification:', 'woo-feed' ) ?></span>
                        <strong class="data"><?php
							/** @noinspection HtmlUnknownTarget */
							( empty( $v ) ) ? esc_html_e( 'N/A',
								'woo-feed' ) : printf( '<a href="%s" target="_blank">%s</a>',
								esc_url( $v ),
								esc_html__( 'Read Article', 'woo-feed' ) );
							?></strong>
					<?php } elseif ( 'video' == $k ) { ?>
                        <span class="dashicons dashicons-video-alt3" style="color: #82878c;" aria-hidden="true"></span>
                        <span><?php esc_html_e( 'Video Documentation:', 'woo-feed' ) ?></span>
                        <strong class="data"><?php
							/** @noinspection HtmlUnknownTarget */
							( empty( $v ) ) ? esc_html_e( 'N/A',
								'woo-feed' ) : printf( '<a href="%s" target="_blank">%s</a>',
								esc_url( $v ),
								esc_html__( 'Watch now', 'woo-feed' ) );
							?></strong>
					<?php } elseif ( 'feed_file_type' == $k ) { ?>
                        <span class="dashicons dashicons-media-text" style="color: #82878c;"
                              aria-hidden="true"></span> <?php esc_html_e( 'Format Supported:', 'woo-feed' ) ?>
                        <strong class="data"><?php
							if ( empty( $v ) ) {
								esc_html_e( 'N/A', 'woo-feed' );
							} else {
								$v = implode( ', ',
									array_map( function ( $type ) {
										return esc_html( strtoupper( $type ) );
									},
										(array) $v ) );
								echo esc_html( $v );
							} ?></strong>
						<?php
					} elseif ( 'doc' == $k ) { ?>
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
					} ?>
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
	 * @param array $feedRules
	 * @param bool $idEdit
	 */
	function render_feed_config( $tabId, $feedRules, $idEdit ) {
		global $provider, $wooFeedDropDown, $wooFeedProduct;
		if ( $idEdit ) {
			include WOO_FEED_FREE_ADMIN_PATH . 'partials/woo-feed-edit-config.php';
		} else {
			if ( 'smartly.io' == $provider ) {
				include WOO_FEED_FREE_ADMIN_PATH . 'partials/templates/google_add-feed.php';
			} elseif ( file_exists( WOO_FEED_FREE_ADMIN_PATH . 'partials/templates/' . $provider . '_add-feed.php' ) ) {
				include WOO_FEED_FREE_ADMIN_PATH . 'partials/templates/' . $provider . '_add-feed.php';
			} else {
				include WOO_FEED_FREE_ADMIN_PATH . 'partials/templates/common_add-feed.php';
			}
		}
	}
}
if ( ! function_exists( 'render_filter_config' ) ) {
	/**
	 * @param string $tabId
	 * @param array $feedRules
	 * @param bool $idEdit
	 */
	function render_filter_config( $tabId, $feedRules, $idEdit ) {
		global $provider, $wooFeedDropDown, $wooFeedProduct;
		include WOO_FEED_FREE_ADMIN_PATH . 'partials/woo-feed-edit-filter.php';
	}
}
if ( ! function_exists( 'render_ftp_config' ) ) {
	/**
	 * @param string $tabId
	 * @param array $feedRules
	 * @param bool $idEdit
	 */
	function render_ftp_config( $tabId, $feedRules, $idEdit ) {
		global $provider, $wooFeedDropDown, $wooFeedProduct;
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
		# Check Google Product Category for Google & Facebook Template and show message
		$checkCategory     = isset( $feedInfo['feedrules']['mattributes'] ) ? $feedInfo['feedrules']['mattributes'] : [];
		$checkCategoryType = isset( $feedInfo['feedrules']['type'] ) ? $feedInfo['feedrules']['type'] : [];
		$merchant          = isset( $feedInfo['feedrules']['provider'] ) ? $feedInfo['feedrules']['provider'] : [];
		$cat               = "yes";
		if ( in_array( $merchant, array( 'google', 'facebook' ) ) && in_array( "current_category", $checkCategory ) ) {
			$catKey = array_search( 'current_category', $checkCategory );
			if ( 'pattern' == $checkCategoryType[ $catKey ] ) {
				$checkCategoryValue = $feedInfo['feedrules']['default'];
			} else {
				$checkCategoryValue = $feedInfo['feedrules']['attributes'];
			}
			
			if ( empty( $checkCategoryValue[ $catKey ] ) ) {
				$cat = 'no';
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
					//$v = sanitize_text_field( $v ); #TODO should not trim Prefix and Suffix field
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
	 * @param int $option_id option id. Optional option id to exclude specific option.
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
	 * @param null $option_id
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
	 * @param null $feed_option_name feed (file) name. optional, if empty or null name will be auto generated
	 * @param bool $configOnly save only wf_config or both wf_config and wf_feed_. default is only wf_config
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
				'last_updated' => gmdate( 'Y-m-d H:i:s' ),
				'status'       => isset( $oldFeed['status'] ) && 1 == $old_feed['status'] ? 1 : 0,
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
	 * @param string $feedService merchant.
	 * @param string $type file type (ext).
	 * @param string|array $string data.
	 * @param string $fileName file name.
	 * @param array $info feed config.
	 *
	 * @return bool
	 */
	function woo_feed_save_batch_feed_info( $feedService, $type, $string, $fileName, $info ) {
		$ext = $type;
		if ( 'csv' == $type ) {
			$string = wp_json_encode( $string );
			$ext    = 'json';
		}
		# Save File.
		$path   = woo_feed_get_file_dir( $feedService, $type );
		$file   = $path . '/' . $fileName . '.' . $ext;
		$save   = new Woo_Feed_Savefile();
		$status = $save->saveFile( $path, $file, $string );
//		if ( woo_feed_is_debugging_enabled() ) {
//			if ( $status ) {
//				$message = sprintf( 'Batch chunk file (%s) saved.', $fileName );
//			} else {
//				$message = sprintf( 'Unable to save batch chunk file %s.', $fileName );
//			}
//			woo_feed_log_feed_process( $info['filename'], $message );
//		}
		
		return $status;
	}
}
if ( ! function_exists( 'woo_feed_get_batch_feed_info' ) ) {
	/**
	 * @param string $feedService
	 * @param string $type
	 * @param string $fileName
	 *
	 * @return bool|false|mixed|string
	 */
	function woo_feed_get_batch_feed_info( $feedService, $type, $fileName ) {
		$ext = $type;
		if ( 'csv' == $type ) {
			$ext = 'json';
		}
		# Save File
		$path = woo_feed_get_file_dir( $feedService, $type );
		$file = $path . '/' . $fileName . '.' . $ext;
		if ( 'csv' == $type && file_exists( $file ) ) {
			// should not cache
			$file = file_get_contents( $file ); // phpcs:ignore
			
			return ( $file ) ? json_decode( $file, true ) : false;
		} elseif ( file_exists( $file ) ) {
			// should not cache
			return file_get_contents( $file ); // phpcs:ignore
		}
		
		return false;
	}
}
if ( ! function_exists( 'woo_feed_unlink_tempFiles' ) ) {
	/**
	 * Remove temporary feed files
	 *
	 * @param string $feedService merchant name.
	 * @param string $fileName feed file name.
	 * @param string $type file type (ext).
	 *
	 * @return bool
	 */
	function woo_feed_unlink_tempFiles( $feedService, $fileName, $type ) {
		$path = woo_feed_get_file_dir( $feedService, $type );
		$ext  = $type;
		if ( 'csv' == $type ) {
			$ext = 'json';
		}
		$files = [
			'headerFile' => $path . '/' . 'wf_store_feed_header_info_' . $fileName . '.' . $ext,
			'bodyFile'   => $path . '/' . 'wf_store_feed_body_info_' . $fileName . '.' . $ext,
			'footerFile' => $path . '/' . 'wf_store_feed_footer_info_' . $fileName . '.' . $ext,
		];
		
		if ( ! empty( $files ) ) {
//			woo_feed_log_feed_process( $fileName, sprintf( 'Deleting Temporary Files (%s).', implode( ', ', array_values( $tempFiles ) ) ) );
			foreach ( $files as $key => $file ) {
				if ( file_exists( $file ) ) {
					unlink( $file ); // phpcs:ignore
				}
			}
			
			return true;
		}
		
		return false;
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
			$feed_data   = $wpdb->get_row( $wpdb->prepare( "SELECT option_name FROM $wpdb->options WHERE option_id = %d",
				$feed_id ) ); // phpcs:ignore
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
	 * @param mixed $search The value being searched for, otherwise known as the needle.
	 *                          An array may be used to designate multiple needles.
	 * @param mixed $replace The replacement value that replaces found search values.
	 *                          An array may be used to designate multiple replacements.
	 * @param mixed $subject The string or array being searched and replaced on,
	 *                          otherwise known as the haystack.
	 * @param string $charlist [optional]
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

// Feed Functions.
if ( ! function_exists( 'woo_feed_generate_feed' ) ) {
	/**
	 * Update Feed Information
	 *
	 * @param array $info feed config array
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
				'last_updated' => gmdate( 'Y-m-d H:i:s' ),
				'status'       => 1,
			);
			update_option( 'wf_feed_' . $feed_option_name, serialize( $feedInfo ), false );
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
if ( ! function_exists( 'woo_feed_get_field_output_type_options' ) ) {
	function woo_feed_get_field_output_type_options() {
		$wooFeedDropDown = new Woo_Feed_Dropdown();
		
		return apply_filters( 'woo_feed_field_output_options', $wooFeedDropDown->output_types );
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
			if ( substr( trim( $url ), 0, 4 ) === "http" || substr( trim( $url ),
					0,
					3 ) === "ftp" || substr( trim( $url ), 0, 4 ) === "sftp" ) {
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
		
		# result array
		$arr = array();
		
		if ( empty( $str ) ) {
			return $arr;
		}
		
		# split on outer delimiter
		$pairs = explode( '&', $str );
		
		if ( ! empty( $pairs ) ) {
			
			# loop through each pair
			foreach ( $pairs as $i ) {
				# split into name and value
				list( $name, $value ) = explode( '=', $i, 2 );
				
				# if name already exists
				if ( isset( $arr[ $name ] ) ) {
					# stick multiple values into an array
					if ( is_array( $arr[ $name ] ) ) {
						$arr[ $name ][] = $value;
					} else {
						$arr[ $name ] = array( $arr[ $name ], $value );
					}
				} # otherwise, simply stick it in a scalar
				else {
					$arr[ $name ] = $value;
				}
			}
		} elseif ( ! empty( $str ) ) {
			list( $name, $value ) = explode( '=', $str, 2 );
			$arr[ $name ] = $value;
		}
		
		# return result array
		return $arr;
	}
}
if ( ! function_exists( 'woo_feed_replace_to_merchant_attribute' ) ) {
	/**
	 * Parse URL parameter
	 *
	 * @param string $pluginAttribute
	 * @param string $merchant
	 * @param string feedType CSV XML TXT
	 *
	 * @return string
	 */
	function woo_feed_replace_to_merchant_attribute( $pluginAttribute, $merchant, $feedType ) {
		$attributeClass     = new Woo_Feed_Default_Attributes();
		$merchantAttributes = '';
		if ( 'google' == $merchant && 'xml' == $feedType ) {
			$merchantAttributes = $attributeClass->googleXMLAttribute;
		} elseif ( 'google' == $merchant && ( 'csv' == $feedType || 'txt' == $feedType ) ) {
			$merchantAttributes = $attributeClass->googleCSVTXTAttribute;
		} elseif ( 'facebook' == $merchant && 'xml' == $feedType ) {
			$merchantAttributes = $attributeClass->facebookXMLAttribute;
		} elseif ( 'facebook' == $merchant && ( 'csv' == $feedType || 'txt' == $feedType ) ) {
			$merchantAttributes = $attributeClass->facebookCSVTXTAttribute;
		} elseif ( 'pinterest' == $merchant && 'xml' == $feedType ) {
			$merchantAttributes = $attributeClass->pinterestXMLAttribute;
		} elseif ( 'pinterest' == $merchant && ( 'csv' == $feedType || 'txt' == $feedType ) ) {
			$merchantAttributes = $attributeClass->pinterestCSVTXTAttribute;
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
		if ( strpos( $attributeValue, "<![CDATA[" ) !== false ) {
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
		}
		
		if ( ! empty( $merchantAttributes ) && array_key_exists( $pluginAttribute, $merchantAttributes ) ) {
			if ( 'true' == $merchantAttributes[ $pluginAttribute ][1] ) {
				return "<![CDATA[$attributeValue]]>";
			} else {
				return "$attributeValue";
			}
		} elseif ( false !== strpos( $attributeValue, "&" ) || 'http' == substr( trim( $attributeValue ), 0, 4 ) ) {
			return "<![CDATA[$attributeValue]]>";
		} else {
			return "$attributeValue";
		}
	}
}

// Price And Tax.
if ( ! function_exists( 'woo_feed_apply_tax_location_data' ) ) {
	/**
	 * Filter and Change Location data for tax calculation
	 *
	 * @param array $location Location array.
	 * @param string $tax_class Tax class.
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
if ( ! function_exists( 'woo_feed_product_type_separator' ) ) {
	/**
	 * Filter Product local category (type) separator
	 *
	 * @param string $separator
	 * @param array $config
	 *
	 * @return string
	 */
	function woo_feed_product_type_separator( $separator, $config ) {
		if ( 'trovaprezzi' === $config['provider'] ) {
			$separator = ',';
		}
		
		return $separator;
	}
}
if ( ! function_exists( 'woo_feed_get_trovaprezzi_availability_attribute_filter' ) ) {
	/**
	 * Filter Product Availability Attribute Output For Trovaprezzi.it Template
	 *
	 * @param string $output
	 * @param WC_Product $product
	 *
	 * @return int
	 */
	function woo_feed_get_trovaprezzi_availability_attribute_filter( $output, $product ) {
		$status = $product->get_stock_status();
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
		
		return $output;
	}
}

// Parse feed rules.
if ( ! function_exists( 'woo_feed_filter_parsed_trovaprezzi_rules' ) ) {
	/**
	 * Filter Feed parsed rules for trovaprezzi.it
	 *
	 * @param array $rules
	 * @param string $context
	 *
	 * @return array
	 * @since 3.3.7
	 */
	function woo_feed_filter_parsed_trovaprezzi_rules( $rules, $context ) {
		if ( 'create' === $context ) {
			$rules['decimal_separator']  = ',';
			$rules['thousand_separator'] = '';
			$rules['decimals']           = 2;
			$rules['itemsWrapper']       = 'Products';
			$rules['itemWrapper']        = 'Offer';
			$rules['delimiter']          = '|';
			$rules['enclosure']          = ' ';
		}
		
		return $rules;
	}
}
if ( ! function_exists( 'woo_feed_filter_parsed_criteo_rules' ) ) {
	/**
	 * Filter Feed parsed rules for criteo
	 *
	 * @param array $rules
	 * @param string $context
	 *
	 * @return array
	 * @since 3.3.7
	 */
	function woo_feed_filter_parsed_criteo_rules( $rules, $context ) {
		if ( 'create' === $context ) {
			$rules['itemsWrapper'] = 'channel';
			$rules['itemWrapper']  = 'item';
		}
		
		return $rules;
	}
}

// End of file helper.php.