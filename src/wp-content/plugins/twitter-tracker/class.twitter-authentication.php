<?php
 
/*  Copyright 2012 Code for the People Ltd

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

/**
 * Twitter oAuth Authentication Line Dance Caller
 *
 * @package Twitter Tracker
 * @since 3.3
 */
class TT_Twitter_Authentication {
	
	/**
	 * An array of error messages for the user
	 * 
	 * @var type array
	 */
	public $errors;

	protected $creds; 

	/**
	 * Singleton stuff.
	 * 
	 * @access @static
	 * 
	 * @return NAO_Duplicates_Checker
	 */
	static public function init() {

		static $instance = false;

		if ( ! $instance )
			$instance = new TT_Twitter_Authentication;

		return $instance;

	}

	/**
	 * Let's go!
	 *
	 * @return void
	 **/
	public function __construct() {

		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'load-settings_page_tt_auth', array( $this, 'load_settings' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_filter( 'tt_twitter_credentials', array( $this, 'tt_twitter_credentials' ) );

		$this->errors = array();

		$this->load_creds();
	}
	
	// HOOKS
	// =====

	public function tt_twitter_credentials( $creds ) {
		if ( ! $this->creds[ 'authenticated' ] )
			return $creds;
		return $this->creds;
	}

	public function is_authenticated() {
		return $this->creds[ 'authenticated' ];
	}
	
	public function admin_menu() {
		add_options_page( 'Twitter Tracker Authentication', 'Twitter Tracker Auth', 'manage_options', 'tt_auth', array( $this, 'settings' ) );
	}

	public function load_settings() {

		// Denied?
		if ( isset( $_GET[ 'denied' ] ) ) {
			if ( $_GET[ 'denied' ] == $this->creds[ 'oauth_token' ] ) {
				$unset_creds = array(
					'oauth_token',
					'oauth_token_secret',
					'user_id',
					'screen_name',
					'authenticated',
				);
				$this->unset_creds( $unset_creds );
				nocache_headers();
				wp_redirect( admin_url( 'options-general.php?page=tt_auth&tt_denied=1' ) );
				exit;
			}
		}

		// Un-authentication request:
		if ( isset( $_POST[ 'tt_unauthenticate' ] ) ) {
			check_admin_referer ( "tt_unauthenticate_{$this->creds[ 'user_id' ]}", '_tt_unauth_nonce_field' );
			$unset_creds = array(
				'oauth_token',
				'oauth_token_secret',
				'user_id',
				'screen_name',
				'authenticated',
			);
			$this->unset_creds( $unset_creds );

			nocache_headers();
			wp_redirect( admin_url( 'options-general.php?page=tt_auth&tt_unauthenticated=1' ) );
			exit;			
		}
		
		// Authentication request:
		if ( ! $this->creds[ 'authenticated' ] && isset( $_POST[ 'tt_authenticate' ] ) ) {
		
			if ( isset( $_POST[ '_cftp_tt_nonce_field' ] ) )
				check_admin_referer ( 'tt_authenticate', '_tt_auth_nonce_field' );

			$connection = $this->oauth_connection();
			$request_token_response = $connection->getRequestToken( admin_url( 'options-general.php?page=tt_auth' ) );
			
			$new_creds = array(
				'oauth_token'        => $request_token_response[ 'oauth_token' ],
				'oauth_token_secret' => $request_token_response[ 'oauth_token_secret' ],
			);
			$this->set_creds( $new_creds );

			nocache_headers();
			$authorize_url = $connection->getAuthorizeURL( $this->creds[ 'oauth_token' ] );
			wp_redirect( $authorize_url );
			exit;
		}
		
		// Partway through the authentication:
		if ( ! $this->creds[ 'authenticated' ] && $this->is_authentication_response() ) {
			$connection = $this->oauth_connection();
			$params = array(
				'oauth_token' => $this->creds[ 'oauth_token' ],
			);
			$oauth_verifier = isset( $_GET[ 'oauth_verifier' ] ) ? $_GET[ 'oauth_verifier' ] : false;
			$access_token_response = $connection->getAccessToken( $oauth_verifier, $params );

			$creds_option = get_option( 'tt_twitter_creds', array() );
			$new_creds = array(
				'oauth_token'        => $access_token_response[ 'oauth_token' ],
				'oauth_token_secret' => $access_token_response[ 'oauth_token_secret' ],
				'user_id'            => $access_token_response[ 'user_id' ],
				'screen_name'        => $access_token_response[ 'screen_name' ],
				'authenticated'      => true,
			);
			$this->set_creds( $new_creds );

			nocache_headers();
			wp_redirect( admin_url( 'options-general.php?page=tt_auth&tt_authenticated=1' ) );
			exit;			
		}

		// No authentication process in progress

	}

	public function admin_notices() {
		$screen = get_current_screen();
		if ( 'settings_page_tt_auth' != $screen->id && current_user_can( 'manage_options' ) && ! $this->creds[ 'authenticated' ] )
			printf( '<div class="error"><p>%s</p></div>', sprintf( __( 'Before you can use Twitter Tracker, you need to <a href="%s">authorise this site with Twitter</a>.', 'twitter-tracker' ), admin_url( 'options-general.php?page=tt_auth' ) ) );
		if ( isset( $_GET[ 'tt_authenticated' ] ) )
			printf( '<div class="updated"><p>%s</p></div>', sprintf( __( 'You have authorised Twitter Tracker to access Twitter using the <strong>@%s</strong> account', 'twitter-tracker' ), $this->creds[ 'screen_name' ] ) );
		if ( isset( $_GET[ 'tt_unauthenticated' ] ) )
			printf( '<div class="updated"><p>%s</p></div>', sprintf( __( "You have removed Twitter Tracker's authorisation with Twitter", 'twitter-tracker' ), $this->creds[ 'screen_name' ] ) );
		if ( isset( $_GET[ 'tt_denied' ] ) )
			printf( '<div class="error"><p>%s</p></div>', sprintf( __( 'Authorisation with Twitter was <strong>not</strong> completed.', 'twitter-tracker' ), $this->creds[ 'screen_name' ] ) );
	}	
	
	// CALLBACKS
	// =========
	
	public function settings() {
		// var_dump( $this->creds );
		if ( $this->creds[ 'authenticated' ] ) {
			$vars = array();
			$vars[ 'authenticated' ] = $this->creds[ 'authenticated' ];
			$vars[ 'user_id' ]       = $this->creds[ 'user_id' ];
			$vars[ 'screen_name' ]   = $this->creds[ 'screen_name' ];
			$this->view_unauthenticate( $vars );
		} else {
			$this->view_authenticate( array() );
		}

	}

	// "VIEWS"
	// =======

	public function view_unauthenticate( $vars ) {
		extract( $vars );
?>

<div class="wrap">
	
	<?php screen_icon(); ?>
	<h2 class="title">Twitter Tracker</h2>

	<p><?php _e( 'Note that the restrictions registered for this plugin with Twitter mean that it can only read your tweets and mentions, and see your followers, it cannot tweet on your behalf or see DMs.', 'twitter-tracker' ); ?></p>
		
	<?php if ( $authenticated ) : ?>
	
		<form action="" method="post">
			<?php wp_nonce_field( "tt_unauthenticate_$user_id", '_tt_unauth_nonce_field' ); ?>

			<p>
				<?php 
					printf( 
						__( 'You are currently accessing Twitter as the following user: %s', 'twitter-tracker' ), 
						'<a href="http://twitter.com/<?php echo esc_attr( $screen_name ); ?>">@' . esc_html( $screen_name ) . '</a> ' . get_submit_button( __( 'Remove Authentication', 'twitter-tracker' ), 'delete', 'tt_unauthenticate', false )
					);
				?> 
			</p>

		</form>
	
	<?php endif; ?>
	
</div>

<?php
	}

	public function view_authenticate( $vars ) {
		extract( $vars );
?>

<div class="wrap">
	
	<?php screen_icon(); ?>
	<h2 class="title"><?php _e( 'Twitter Tracker', 'twitter-tracker' ); ?></h2>
	
	<p><?php _e( 'Note that the restrictions registered for this plugin with Twitter mean that it can only read your tweets and mentions, and see your followers, it cannot tweet on your behalf or see DMs.', 'twitter-tracker' ); ?></p>

	<form action="" method="post">
		<?php wp_nonce_field( 'tt_authenticate', '_tt_auth_nonce_field' ); ?>
		<p>
			<?php 
				printf( 
					__( 'In order to read and display Twitter searches and user tweets, we need you to authorise Twitter Tracker with Twitter: %s', 'twitter-tracker' ),
					get_submit_button( __( 'Authorise with Twitter', 'twitter-tracker' ), null, 'tt_authenticate', false )
				); ?>
		</p>

	</form>
	
</div>


<?php
	}

	// UTILITIES
	// =========

	public function oauth_connection() {
		require_once( 'class.oauth.php' );
		require_once( 'class.wp-twitter-oauth.php' );
		return new TT_Twitter_OAuth( 
			$this->creds[ 'consumer_key' ], 
			$this->creds[ 'consumer_secret' ],
			$this->creds[ 'oauth_token' ],
			$this->creds[ 'oauth_token_secret' ]
		);
	}

	public function load_creds() {
		$creds_defaults = array(
			'consumer_key'       => 'XV7HZZKjYpPtGwhsTZY6A',
			'consumer_secret'    => 'etSpBLB6951otLgmAsKP67oV7ALKe8ipAaKe5OIyU',
			'oauth_token'        => null,
			'oauth_token_secret' => null,
			'authenticated'      => false,
			'user_id'            => null,
			'screen_name'        => null,
		);
		$creds_option = get_option( 'tt_twitter_creds', array() );
		$this->creds = wp_parse_args( $creds_option, $creds_defaults );
	}

	public function set_creds( $new_creds ) {
		$current_creds = get_option( 'tt_twitter_creds', array() );
		unset( $current_creds[ 'consumer_key' ] );
		unset( $current_creds[ 'consumer_secret' ] );
		update_option( 'tt_twitter_creds', wp_parse_args( $new_creds, $current_creds ) );
		$this->load_creds();
	}

	public function unset_creds( $names ) {
		$creds = get_option( 'tt_twitter_creds', array() );
		unset( $creds[ 'consumer_key' ] );
		unset( $creds[ 'consumer_secret' ] );
		foreach ( $names as $name )
			unset( $creds[ $name ] );
		update_option( 'tt_twitter_creds', $creds );
		$this->load_creds();
	}

	public function is_authentication_response() {
		return isset( $_GET[ 'oauth_token' ] );
	}
	
}

TT_Twitter_Authentication::init();
