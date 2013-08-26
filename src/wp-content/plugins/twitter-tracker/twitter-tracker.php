<?php
/*
Plugin Name: Twitter Tracker
Plugin URI: http://wordpress.org/extend/plugins/twitter-tracker/
Description: Tracks the search results on Twitter search or Twitter profile in a sidebar widget.
Author: Simon Wheatley (Code for the People)
Version: 3.3.6
Author URI: http://codeforthepeople.com/
*/

// http://twitter.com/search.atom?q=wordcampuk

/*  Copyright 2013 Code For The People

				_____________
			   /      ____   \
		 _____/       \   \   \
		/\    \        \___\   \
	   /  \    \                \
	  /   /    /          _______\
	 /   /    /          \       /
	/   /    /            \     /
	\   \    \ _____    ___\   /
	 \   \    /\    \  /       \
	  \   \  /  \____\/    _____\
	   \   \/        /    /    / \
		\           /____/    /___\
		 \                        /
		  \______________________/


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

	--------------------------------------------------------------------------

	Emoji conversion library
	https://github.com/iamcal/php-emoji/
	By Cal Henderson cal@iamcal.com
	Parser rewrite based on a fork by 杜志刚
	This work is licensed under the GPL v3

	Emoji images MIT licensed: https://github.com/github/gemoji/blob/master/LICENSE

*/

require_once( dirname (__FILE__) . '/plugin.php' );
require_once( dirname (__FILE__) . '/class-TwitterTracker_Widget.php' );
require_once( dirname (__FILE__) . '/class-TwitterTracker_Profile_Widget.php' );
require_once( dirname (__FILE__) . '/class.twitter-authentication.php' );

/**
 *
 * @package default
 * @author Simon Wheatley
 **/
class TwitterTracker extends TwitterTracker_Plugin
{

	public $widget;

	public function __construct()
	{
		$this->register_plugin( 'twitter-tracker', __FILE__ );
		if ( is_admin() ) {
			$this->register_activation (__FILE__);
			$this->add_action( 'save_post', 'process_metabox', null, 2 );
			$this->add_action( 'add_meta_boxes' );
		}
		// Init
		$this->register_plugin ( 'twitter-tracker', __FILE__ );
		$this->add_action( 'init' );
		$this->add_action( 'wp_enqueue_scripts', 'action_wp_enqueue_scripts' );
		$this->add_filter( 'tt_allowed_post_types', 'warn_tt_allowed_post_types' );

		// register widget
		add_action('widgets_init', create_function('', 'return register_widget( "TwitterTracker_Widget" );'));
		add_action('widgets_init', create_function('', 'return register_widget( "TwitterTracker_Profile_Widget" );'));
	}
	
	// DOING IT WRONG
	// ==============
	
	/**
	 * Hooks the warn_tt_allowed_post_types filter to throw a
	 * doing it wrong warning if anyone has used the filter.
	 *
	 * @param mixed $pass_through A value to pass right through
	 * @return A value to pass right through
	 **/
	function warn_tt_allowed_post_types( $pass_through ) {
		remove_filter( 'tt_allowed_post_types', array( & $this, 'warn_tt_allowed_post_types' ) );
		if ( has_filter( 'tt_allowed_post_types' ) )
			_doing_it_wrong( 'tt_allowed_post_types', __( 'Twitter Tracker filter error: The tt_allowed_post_types filter has been deprecated and will be removed in a future version, please use tt_post_types_with_override instead.', 'twitter-tracker' ), '2.5' );
		return $pass_through;
	}
	
	// HOOKS
	// =====
	
	public function activate() {
		// Empty
	}
	
	/**
	 * Hooks the WP add_meta_boxes action to add the metaboxes to 
	 * post_type editing screens.
	 *
	 * @return void
	 **/
	function add_meta_boxes() {
		// Allow other plugins to add to the allowed post types for this metabox
		// First use the legacy filter name
		$allowed_post_types = apply_filters( 'tt_allowed_post_types', array( 'page', 'post' ) );
		// Now use the new, more sensible filter name
		$allowed_post_types = apply_filters( 'tt_post_types_with_override', $allowed_post_types );
		// This work around because the WO CIDIW client needs to upgrade WP,
		// but we need to get this plugin working before they do… can be removed
		// once everyone is up to v3.1.0 as required by the plugin.
		if ( function_exists( 'get_current_screen' ) ) {
			$screen = get_current_screen();
			if ( ! in_array( $screen->post_type, $allowed_post_types ) )
				return;
		}
		$this->add_meta_box( 'twitter_tracker', __( 'Twitter Tracker', 'twitter-tracker' ), 'metabox', 'post', 'normal', 'default' );
		$this->add_meta_box( 'twitter_tracker', __( 'Twitter Tracker', 'twitter-tracker' ), 'metabox', 'page', 'normal', 'default' );
	}
	
	/**
	 * Hooks the WP wp_enqueue_scripts action
	 *
	 * @action wp_enqueue_scripts
	 *
	 * @return void
	 * @author Simon Wheatley
	 **/
	function action_wp_enqueue_scripts() {
		if ( 'convert' == get_option( 'tt_convert_emoji', 'hide' ) )
			wp_enqueue_style( 'tt_emoji', $this->url() .  '/emoji/emoji.css' , null, $this->filemtime( '/emoji/emoji.css' ) );
	}

	/**
	 * Callback function providing the HTML for the metabox
	 *
	 * @return void
	 * @author Simon Wheatley
	 **/
	public function metabox() {
		global $post;
		$vars = array();
		$vars[ 'query' ] = get_post_meta( $post->ID, '_tt_query', true );
		$vars[ 'username' ] = get_post_meta( $post->ID, '_tt_username', true );
		$this->render_admin( 'metabox', $vars );
	}
		
	public function process_metabox( $post_id, $post ) {
		// Are we being asked to do anything?
		$do_something = (bool) @ $_POST[ '_tt_query_nonce' ];
		if ( ! $do_something ) return;
		// Allow other plugins to add to the allowed post types
		// First use the legacy filter name
		$allowed_post_types = apply_filters( 'tt_allowed_post_types', array( 'page', 'post' ) );
		// Now use the new, more sensible filter name
		$allowed_post_types = apply_filters( 'tt_post_types_with_override', $allowed_post_types );
		// Don't bother doing this on revisions and wot not
		if ( ! in_array( $post->post_type, $allowed_post_types ) )
			return;
		// Are we authorised to do anything?
		check_admin_referer( 'tt_query', '_tt_query_nonce' );
		// OK. We are good to go.
		$query = @ $_POST[ 'tt_query' ];
		update_post_meta( $post_id, '_tt_query', $query );
		$username = @ $_POST[ 'tt_username' ];
		update_post_meta( $post_id, '_tt_username', $username );
	}
	
	public function init()
	{
		// Slightly cheeky, but change the cache age of Magpie from 60 minutes to 15 minutes
		// That's still plenty of caching IMHO :)
		if ( ! defined( 'MAGPIE_CACHE_AGE' ) )
			define( 'MAGPIE_CACHE_AGE', 60 * 15 ); // Fifteen of your Earth minutes
	}
		
	public function show( $instance = array() ) {
		// Backwards compatibility
		return $this->show_search( $instance );
	}

	public function show_search( $instance = array() )
	{
		$defaults = array (
			'convert_emoji' => 'hide',
			'hide_replies' => false,
			'include_retweets' => false,
			'mandatory_hash' => '',
			'max_tweets' => 30,
			'html_after' => '',
			'preamble' => '',
		);
		$instance = wp_parse_args( $instance, $defaults );

		extract( $instance );

		// Allow the local custom field to overwrite the widget's query
		if ( is_singular() && is_single() && $post_id = get_queried_object_id() )
			if ( $local_query = trim( get_post_meta( $post_id, '_tt_query', true ) ) )
				$twitter_search = $local_query;

		// Let the user know if there's no search query
		$twitter_search = trim( $twitter_search );
		if ( empty( $twitter_search ) ) {
			$vars = array( 
				'msg' => __( 'For this Twitter Tracker search widget to work you need to set at least a Twitter Search in the widget settings.', 'twitter-tracker' ),
				'additional_error_class' => '',
				'strong' => true,
			);
			$this->render( 'widget-error', $vars );
			return;
		}

		// Let the user know if there's no auth
		if ( ! TT_Twitter_Authentication::init()->is_authenticated() ) {
			$vars = array( 
				'msg' => __( 'For this Twitter Tracker search widget to work you need to authorise with Twitter in "Dashboard" -> "Settings" -> "Twitter Tracker Auth".', 'twitter-tracker' ),
				'additional_error_class' => '',
				'strong' => true,
			);
			$this->render( 'widget-error', $vars );
			return;
		}

		require_once( 'class.oauth.php' );
		require_once( 'class.wp-twitter-oauth.php' );
		require_once( 'class.response.php' );
		require_once( 'class.twitter-service.php' );

		$args = array(
			'params' => array(
				'count' => max( ($max_tweets * 4), 200 ), // Get *lots* as we have to throw some away later
				'q'     => $twitter_search,
			),
		);

		$transient_key = 'tt_profile-' . md5( serialize( $instance ) . serialize( $args ) );

		if ( $output = get_transient( $transient_key ) ) {
			echo $output;
			return;
		}

		$service = new TT_Service;
		$response = $service->request_search( $args );

		if ( is_wp_error( $response ) ) {
			error_log( "Twitter Tracker response error: " . print_r( $response, true ) );
			return;
		}

		if ( $hide_replies )
			$response->remove_replies();
		
		if ( ! $include_retweets )
			$response->remove_retweets();

		$response->convert_emoji( $convert_emoji );

		$mandatory_hash = strtolower( trim( ltrim( $mandatory_hash, '#' ) ) );
		if ( $mandatory_hash )
			$response->remove_without_hash( $mandatory_hash );

		$vars = array( 
			'tweets' => array_slice( $response->items, 0, $max_tweets ),
			'preamble' => $preamble,
			'html_after' => $html_after,
		);
		
		$vars[ 'datef' ] = _x( 'M j, Y @ G:i', 'Publish box date format', 'twitter-tracker' );

		if ( ! $response->have_tweets() ) {
			$vars[ 'msg' ] = apply_filters( 'tt_no_tweets', __( 'No tweets found.', 'twitter-tracker' ), $twitter_search, $instance );
			$vars[ 'additional_error_class' ] = 'no-tweets';
			$vars[ 'strong' ] = false;
			$output = $this->capture( 'widget-error', $vars );
		} else {
			$output = $this->capture( 'widget-contents', $vars );
		}
		echo PHP_EOL . "<!-- Regenerating cache $transient_key at " . current_time( 'mysql' ) . " -->" . PHP_EOL;
		echo $output;
		$output = PHP_EOL . "<!-- Retrieved from $transient_key, cached at " . current_time( 'mysql' ) . " -->" . PHP_EOL . $output;
		set_transient( $transient_key, $output, apply_filters( 'tt_cache_expiry', 300, $transient_key, $args ) );
	}

	public function show_profile( $instance = array() )
	{
		$defaults = array (
			'convert_emoji' => 'hide',
			'hide_replies' => false,
			'include_retweets' => false,
			'mandatory_hash' => '',
			'max_tweets' => 3,
			'html_after' => '',
			'preamble' => '',
		);
		$instance = wp_parse_args( $instance, $defaults );

		extract( $instance );

		// Allow the local custom field to overwrite the widget's query, but
		// only on single post (of any type)
		if ( is_singular() && $post_id = get_queried_object_id() )
			if ( $local_username = trim( get_post_meta( $post_id, '_tt_username', true ) ) )
				$username = $local_username;

		// Let the user know if there's no search query
		$username = trim( $username );
		if ( empty( $username ) ) {
			$vars = array( 
				'msg' => __( 'For this Twitter Tracker profile widget to work you need to set at least a Twitter screenname (username) in the widget settings.', 'twitter-tracker' ),
				'additional_error_class' => '',
				'strong' => true,
			);
			$this->render( 'widget-error', $vars );
			return;
		}

		// Let the user know if there's no auth
		if ( ! TT_Twitter_Authentication::init()->is_authenticated() ) {
			$vars = array( 
				'msg' => __( 'For this Twitter Tracker profile widget to work you need to authorise with Twitter in "Dashboard" -> "Settings" -> "Twitter Tracker Auth".', 'twitter-tracker' ),
				'additional_error_class' => '',
				'strong' => true,
			);
			$this->render( 'widget-error', $vars );
			return;
		}

		require_once( 'class.oauth.php' );
		require_once( 'class.wp-twitter-oauth.php' );
		require_once( 'class.response.php' );
		require_once( 'class.twitter-service.php' );

		$args = array(
			'count' => max( ($max_tweets * 4), 200 ), // Get *lots* as we have to throw some away later
		);

		$transient_key = 'tt_search-' . md5( serialize( $instance ) . $username . serialize( $args ) );

		if ( $output = get_transient( $transient_key ) ) {
			echo $output;
			return;
		}

		$service = new TT_Service;
		$response = $service->request_user_timeline( $username, $args );

		if ( is_wp_error( $response ) ) {
			error_log( "Twitter Tracker response error: " . print_r( $response, true ) );
			return;
		}

		if ( $hide_replies )
			$response->remove_replies();

		if ( ! $include_retweets )
			$response->remove_retweets();

		$response->convert_emoji();

		$mandatory_hash = strtolower( trim( ltrim( $mandatory_hash, '#' ) ) );
		if ( $mandatory_hash )
			$response->remove_without_hash( $mandatory_hash );

		// @TODO Setup a method for the default vars needed
		$vars = array( 
			'tweets' => array_slice( $response->items, 0, $max_tweets ),
			'preamble' => $preamble,
			'html_after' => $html_after,
		);
		$vars[ 'datef' ] = _x( 'M j, Y @ G:i', 'Publish box date format', 'twitter-tracker' );
		$output = $this->capture( 'widget-contents', $vars );
		echo PHP_EOL . "<!-- Regenerating cache $transient_key at " . current_time( 'mysql' ) . " -->" . PHP_EOL;
		echo $output;
		$output = PHP_EOL . "<!-- Retrieved from $transient_key, cached at " . current_time( 'mysql' ) . " -->" . PHP_EOL . $output;
		set_transient( $transient_key, $output, apply_filters( 'tt_cache_expiry', 300, $transient_key, $username, $args ) );
	}

	public function & get()
	{
	    static $instance;

	    if ( ! isset ( $instance ) ) {
			$c = __CLASS__;
			$instance = new $c;
	    }

	    return $instance;
	}

}

function twitter_tracker( $instance )
{
	$tracker = TwitterTracker::get();
	$tracker->show_search( $instance );
}

function twitter_tracker_profile( $instance )
{
	$tracker = TwitterTracker::get();
	$tracker->show_profile( $instance );
}


/**
 * Instantiate the plugin
 *
 * @global
 **/

$GLOBALS[ 'TwitterTracker' ] = new TwitterTracker();

?>
