<?php
/**
 * Webappick Promotion handler
 * @version 1.0.0
 * @package WebAppick
 * @subpackage AppServices
 */

namespace WebAppick\AppServices;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Promotions
 * Source Format
 *
	$promos = [
		(object) [
		// required; offer start
		'start'             => '2019-11-20 00:00:01',
		// required; offer end
		'end'               => '2019-12-04 23:59:00',
		// required; some unique hash for offer, use for hiding the offer when user click to close.
		'hash'              => '9df37798-bd35-421b-b6f2-fbc095686efc',
		// Optional; Allow closing default is 0, set to 1 to allow user hide it
		'dismissible'       => 1, // 0
		// required; main content required, will be filtered with wp_kses_post()
		'content'           => '<p>Biggest Sale of the year on this</p><h3>Black Friday & Cyber Monday</h3><p>Claim your discount on  till 4th December</p>',
		// optional; wrapper padding
		wrapperPadding      => ''
		// optional; image source url or data url
		'backgroundImage'  => '',
		// optional;
		'backgroundRepeat' => '',
		// optional;
		'backgroundSize'   => '',
		// optional; background color to apply with the background image
		'backgroundColor'  => '#000',
		// optional; text color will be inherited by the content
		'color'        => '#fff',
		// optional; can be empty
		'logo'              => [
		// required; image source url or data url
		'src' => '',
		// required;
		'alt' => 'Woo Feed Pro',
		],
		// optional; can be empty
		'button'            => [
		// required
		'label'         => 'Save 30%',
		// required
		'url'           => '#?utm_campaign=black_friday_&_cyber_monday&utm_medium=banner&utm_source=wp_dashboard',
		// optional;
		'backgroundColor'    => '#F71560',
		// optional;
		'color'         => '#FFF',
		// optional;
		'after'         => '<span style="font-size: 12px; font-weight: 700; margin-top: 12px; display: block;">Coupon: BFCM2019</span>', // html content filtered with wp_kses_post()
		],
		],
	];
 *
 */
class Promotions {
	
	/**
	 * WebAppick\AppServices\Client
	 *
	 * @var Client
	 */
	protected $client;
	
	/**
	 * URL for Promotions source json file
	 * @var string
	 */
	private $promotionSrc;
	
	/**
	 * Promotions
	 * @var bool|object[]
	 */
	private $promotions = false;
	/**
	 * List of hidden promotions for current user
	 * @var array
	 */
	private $hiddenPromotions;
	/**
	 * Current User Id
	 * @var int
	 */
	private $currentUser = 0;
	
	/**
	 * Promotions constructor.
	 * @param Client $client        The Client.
	 * @param string $data_source   Data Source URL
	 * @return void
	 */
	public function __construct( Client $client, $data_source = null ) {
		$this->client = $client;
		if ( ! is_null( $data_source ) ) $this->promotionSrc = esc_url( $data_source );
	}
	
	/**
	 * Set JSON Source File URL For getting promotion data
	 * @param string $URL      Set Data Source URL.
	 *
	 * @return Promotions
	 */
	public function set_source( $URL ) {
		$this->promotionSrc = esc_url( $URL );
		return $this;
	}
	
	/**
	 * Init Promotions
	 * @return void
	 */
	public function init() {
		if ( is_null( $this->promotionSrc ) ) {
			_doing_it_wrong( __METHOD__, esc_html__( 'Promotion Source URL Not Set. see Promotions::set_source( $URL )', 'webappick' ), '1.0.0' );
		}
		add_action( 'admin_init', [ $this, '__init_internal' ], 10 );
	}
	
	/**
	 * Set environment variables and init internal hooks
	 * @return void
	 */
	public function __init_internal() {
		$this->currentUser = get_current_user_id();
		$this->hiddenPromotions = (array) get_user_option( $this->client->getSlug() . '_hidden_promos', $this->currentUser );
		$this->promotions = $this->__get_promos();
		// only run if there is active promotions.
		if ( count( $this->promotions ) ) {
			add_action( 'admin_notices', [ $this, '__show_promos' ], 10 );
			add_action( 'wp_ajax_webappick_dismiss_promo', [ $this, '__webappick_dismiss_promo' ], 10 );
			add_action( 'admin_print_styles', [ $this, '__get_promo_styles' ], 99 );
			add_action( 'admin_enqueue_scripts', [ $this, '__enqueue_deps' ], 10 );
			add_action( 'admin_print_footer_scripts', [ $this, '__get_promo_scripts' ], 10 );
		}
	}
	
	/**
	 * Render Promotions
	 * @return void
	 */
	public function __show_promos() {
		foreach ( $this->promotions as $promotion ) {
			$wrapperStyles  = '';
			$buttonStyles   = '';
			$is_dismissible = ! isset( $promotion->dismissible ) || isset( $promotion->dismissible ) && 0 == $promotion->dismissible ? false : true;
			
			$has_columns = isset( $promotion->button, $promotion->logo );
			if ( isset( $promotion->color ) ) {
				$wrapperStyles .= 'color: ' . $promotion->color . ';';
			}
			if ( isset( $promotion->wrapperPadding ) ) {
				$wrapperStyles .= 'padding: ' . $promotion->wrapperPadding . ';';
			}
			if ( isset( $promotion->backgroundColor ) ) {
				$wrapperStyles .= 'background-color: ' . $promotion->backgroundColor . ';';
			}
			if ( isset( $promotion->backgroundImage ) ) {
				$wrapperStyles .= 'background-image: url("' . esc_url( $promotion->backgroundImage ) . '");';
			}
			if ( isset( $promotion->backgroundRepeat ) ) {
				$wrapperStyles .= 'background-repeat: ' . $promotion->backgroundRepeat . ';';
			}
			if ( isset( $promotion->backgroundSize ) ) {
				$wrapperStyles .= 'background-size: ' . $promotion->backgroundSize . ';';
			}
			if ( property_exists( $promotion, 'button' ) ) {
				if ( isset( $promotion->button->backgroundColor ) ) {
					$buttonStyles .= 'background-color: ' . $promotion->button->backgroundColor . ';border-color: ' . $promotion->button->backgroundColor . ';';
				}
				if ( isset( $promotion->button->color ) ) {
					$buttonStyles .= 'color: ' . $promotion->button->color . ';';
				}
			}
			$noticeClasses = 'notice notice-success wapk-promo';
			if ( $is_dismissible ) {
				$noticeClasses .= ' is-dismissible';
			}
	?>
		<div class="<?php echo esc_attr( $noticeClasses ); ?> " id="<?php echo esc_attr( $promotion->hash ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wapk-dismiss-promo' ) ); ?>" style="<?php echo esc_attr( $wrapperStyles ); ?>">
			<div class="wapk-promo-wrap<?php if ( ! $has_columns ) echo ' no-column'; ?>">
				<?php if ( isset( $promotion->logo ) && ! empty( $promotion->logo ) ) { ?>
				<div class="wapk-logo wapk-column">
					<img src="<?php echo esc_url( $promotion->logo->src ); ?>" alt="<?php echo esc_attr( $promotion->logo->alt ); ?>">
				</div>
				<?php } ?>
				<div class="wapk-details<?php if ( $has_columns ) echo ' wapk-column'; ?>">
					<?php echo wp_kses_post( $promotion->content ); ?>
				</div>
				<?php if ( isset( $promotion->button ) && ! empty( $promotion->button ) ) { ?>
					<div class="wapk-btn-container wapk-column">
						<a href="<?php echo esc_url( $promotion->button->url ); ?>" class="button wapk-promo-btn" style="<?php echo esc_attr( $buttonStyles ); ?>" target="_blank"><?php echo wp_kses_post( $promotion->button->label ); ?></a>
						<?php
						if ( isset( $promotion->button->after ) && ! empty( $promotion->button->after ) ) {
							echo wp_kses_post( $promotion->button->after );
						}
						?>
					</div>
				<?php } ?>
				<?php if ( isset( $promotion->button ) && ! empty( $promotion->button ) ) { ?>
				<?php } ?>
			</div>
		</div>
	<?php
		}
	}
	
	/**
	 * Get Promotion Data
	 * Cache First then fetch source url for json data.
	 * @return array
	 */
	private function __get_promos() {
		$promos = get_transient( $this->client->getSlug() . '_cached_promos' );
		if ( empty( $promos ) ) {
			// get promotions data from json source.
			$response = wp_safe_remote_get( $this->promotionSrc, array( 'timeout' => 15 ) ); // phpcs:ignore
			$promos   = wp_remote_retrieve_body( $response );
			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
				$promos = '[]';
			}
			// cache data.
			set_transient( $this->client->getSlug() . '_cached_promos', $promos, 12 * HOUR_IN_SECONDS );
		}
		// decode to array.
		$promos = json_decode( $promos );
		
		// filter promotions by date.
		$promos = array_filter( $promos, [ $this, '__is_promo_active' ] );
		if ( ! empty( $promos ) ) {
			// filter promotions by list of hidden promotions by the user.
			$promos = array_filter( $promos, [ $this, '__is_promo_hidden' ] );
		}
		return $promos;
	}
	
	/**
	 * Check if promotion is active by date.
	 * must have start and end property
	 * @param object $promo {   the promo object.
	 *      Single Promo Object
	 *      @type string    $content   string. required
	 *      @type string    $start     valid timestamp. required
	 *      @type string    $end       valid timestamp. required
	 * }
	 *
	 * @return bool
	 */
	public function __is_promo_active( $promo ) {
		$ct = current_time( 'timestamp' ); // phpcs:ignore
		return ( ! empty( $promo->content ) && strtotime( $promo->start ) < $ct && $ct < strtotime( $promo->end ) );
	}
	
	/**
	 * Check if promo is hidden by current user
	 * @param object $promo {   the promo object.
	 *      Single Promo Object
	 *      @type string    $hash     valid unique hash for a promo
	 * }
	 *
	 * @return bool         true if promo is hidden by user
	 */
	public function __is_promo_hidden( $promo ) {
		return ! in_array( $promo->hash, $this->hiddenPromotions );
	}
	
	/**
	 * Js Dependencies
	 * @return void
	 */
	public function __enqueue_deps(){
		wp_enqueue_script( 'wp-util' );
		wp_enqueue_script( 'jquery' );
	}
	
	/**
	 * Script for hiding promo on user click
	 * @return void
	 */
	public function __get_promo_scripts() {
	?>
		<!--suppress ES6ConvertVarToLetConst -->
		<script>
			(function($){
                $('body').on('click', '.wapk-promo .notice-dismiss', function (e) {
                    e.preventDefault();
                    var $parent = $(this).closest( '.wapk-promo' );
                    wp.ajax.post('webappick_dismiss_promo', {
                        dismissed:  true,
	                    hash:       $parent.attr( 'id' ),
                        _wpnonce:   $parent.data( 'nonce' ),
                    });
                });
			})(jQuery);
		</script>
	<?php
	}
	
	/**
	 * Global Promo Styles
	 * @return void
	 */
	public function __get_promo_styles() {
	?>
		<!--suppress CssUnusedSymbol -->
		<style>
			.wapk-promo { border: none; padding: 15px 0; }
			.wapk-promo-wrap { display: flex; justify-content: center; align-items: center; text-align: center; color: inherit; max-width: 1820px; margin: 0 auto; }
			.wapk-promo-wrap.no-column{ display: block; }
			.wapk-column.wapk-logo { flex: 0 0 25%; }
			.wapk-column.wapk-logo img { height: 48px; width: auto; }
			.wapk-details {display: block;}
			.wapk-details h3 { color: inherit; font-size: 30px; margin: 12px 0; }
			.wapk-details p { color: inherit; font-size: 15px; }
			.wapk-column.wapk-details { flex: 0 0 50%; }
			.wapk-column.wapk-btn-container { flex: 0 0 25%; }
			.wapk-promo-wrap .wapk-promo-btn { position: relative; padding: 15px; border-radius: 30px; font-size: 15px; font-weight: 700; display: block; color: inherit; text-decoration: none; max-width: 200px; margin: 0 auto; line-height: normal; height: auto; box-shadow: 1px 2px 0 rgba(0, 0, 0, 0.1); }
			.wapk-promo-wrap .wapk-promo-btn:focus,
			.wapk-promo-wrap .wapk-promo-btn:hover,
			.wapk-promo-wrap .wapk-promo-btn:active { box-shadow: inset 3px 4px 6px 0 rgba(1, 9, 12, 0.25); }
			.wapk-promo-wrap .wapk-promo-btn:active { top: 1px; }
			@media screen and (max-width: 1200px) {
				.wapk-promo-wrap { display: block; overflow: hidden; }
				.wapk-column .wapk-logo { width: 100%; margin: 0 auto; }
				.wapk-column .wapk-details { width: 68%; float: left; margin-right: 4%; margin-top: 32px; }
				.wapk-column.wapk-btn-container { width: 28%; float: right; margin-top: 42px; }
			}
			@media screen and (max-width: 782px) {
				.wapk-promo-wrap .wapk-details { float: none; width: 100%; }
				.wapk-btn-container { float: none; width: 100%; margin-top: 32px; }
				.wapk-column.wapk-btn-container { width: 100%; float: right; margin-top: 42px; }
			}
		</style>
	<?php
	}
	
	/**
	 * Ajax Callback handler for hiding promo
	 * @return void
	 */
	public function __webappick_dismiss_promo() {
		if (
				isset( $_REQUEST['dismissed'], $_REQUEST['hash'], $_REQUEST['_wpnonce'] ) &&
				'true' == $_REQUEST['dismissed'] && ! empty( $_REQUEST['hash'] ) &&
				wp_verify_nonce( sanitize_text_field( $_REQUEST['_wpnonce'] ), 'wapk-dismiss-promo' )
		) {
			$this->hiddenPromotions = array_merge( $this->hiddenPromotions, [ sanitize_text_field( $_REQUEST['hash'] ) ] );
			update_user_option( $this->currentUser, $this->client->getSlug() . '_hidden_promos', $this->hiddenPromotions );
			wp_send_json_success( esc_html__( 'Promo hidden', 'webappick' ) );
		}
		wp_send_json_error( esc_html__( 'Invalid Request', 'webappick' ) );
		die();
	}
	
	/**
	 * @noinspection PhpUnused
	 * Clear Hidden Promotion preference for User
	 * @return bool
	 */
	public function clear_hidden_promos() {
		if ( ! did_action( 'admin_init' ) ) {
			_doing_it_wrong( __METHOD__, esc_html__( 'Method must be invoked inside admin_init action', 'webappick' ), '1.0.0' );
		}
		$this->currentUser = get_current_user_id();
		return delete_user_option( $this->currentUser, $this->client->getSlug() . '_hidden_promos' );
	}
	
	/**
	 * Clear Cached Promotion data
	 * @return bool
	 */
	public function clear_cache() {
		return delete_transient( $this->client->getSlug() . '_cached_promos' );
	}
}
// End of file Promotions.php.