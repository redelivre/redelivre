<?php
/*
Copyright © 2013 Code for the People Ltd

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

*/

defined( 'ABSPATH' ) or die();

class TT_Service {

	public $credentials = null;

	public function __construct() {

	}

	public function load() {

	}

	public function request_search( array $request ) {

		if ( is_wp_error( $connection = $this->get_connection() ) )
			return $connection;

		$args = $request['params'];
		$args[ 'result_type' ] = 'recent';

		$response = $connection->get( sprintf( '%s/search/tweets.json', untrailingslashit( $connection->host ) ), $args );

		if ( 200 == $connection->http_code ) {

			return $this->search_response( $response );

		} else {

			return new WP_Error(
				'tt_twitter_failed_request',
				sprintf( __( 'Could not connect to Twitter (error %s).', 'twitter-tracker' ),
					esc_html( $connection->http_code )
				)
			);

		}

	}

	public function request_user_timeline( $screen_name, $args = array() ) {

		if ( is_wp_error( $connection = $this->get_connection() ) )
			return $connection;

		$defaults = array( 
			'count'           => 20,
			'exclude_replies' => false,
		);
		$args = wp_parse_args( $args, $defaults );

		array_walk( $args, array( $this, 'bool_to_str' ) );

		$args[ 'screen_name' ] = $screen_name;
		$args[ 'contributor_details' ] = true;

		$response = $connection->get( sprintf( '%s/statuses/user_timeline.json', untrailingslashit( $connection->host ) ), $args );

		// var_dump( $response );
		// var_dump( $connection );

		# @TODO switch the twitter oauth class over to wp http api:
		if ( 200 == $connection->http_code ) {

			return $this->timeline_response( $response );

		} else {

			return new WP_Error(
				'tt_twitter_failed_request',
				sprintf( __( 'Could not connect to Twitter (error %s).', 'twitter-tracker' ),
					esc_html( $connection->http_code )
				)
			);

		}

	}

	public static function status_url( $status ) {

		return sprintf( 'https://twitter.com/%s/status/%s',
			$status->user->screen_name,
			$status->id_str
		);

	}

	public static function status_content( $status ) {

		$text = $status->text;

		# @TODO more processing (hashtags, @s etc)
		$text = make_clickable( $text );
		$text = str_replace( ' href="', ' target="_blank" href="', $text );
		
		// En-link-ify all the #hashtags in the message text
		$hashtag_regex = '/(^|\s)#(\w*[a-zA-Z_]+\w*)/';
		// preg_match_all( $hashtag_regex, $text, $preg_output );
		$text = preg_replace( $hashtag_regex, '\1<a href="http://twitter.com/search?q=%23\2" target="_blank">#\2</a>', $text );
		
		// En-link-ify all the @usernames in the message text
		$username_regex = '/(^\.?|\s|)\@(\w*[a-zA-Z_]+\w*)/';
		// preg_match_all( $username_regex, $text, $preg_output );
		$text = preg_replace( $username_regex, '\1<a href="http://twitter.com/\2" target="_blank">@\2</a>', $text );

		return $text;

	}

	public static function status_retweeted( $status ) {
		return isset( $status->retweeted_status ) && $status->retweeted_status;
	}

	public static function status_original_twit( $status ) {
		if ( ! isset( $status->retweeted_status ) || ! $status->retweeted_status )
			return;
		return $status->retweeted_status->user->screen_name;
	}

	public function status_hashtags( $status ) {
		return wp_list_pluck( $status->entities->hashtags, 'text' );
	}

	public function status_avatar( $status ) {
		if ( self::status_retweeted( $status ) )
			return is_ssl() ? $status->retweeted_status->user->profile_image_url_https : $status->retweeted_status->user->profile_image_url;
		return is_ssl() ? $status->user->profile_image_url_https : $status->user->profile_image_url;
	}

	public function get_max_id( $next ) {

		parse_str( ltrim( $next, '?' ), $vars );

		if ( isset( $vars['max_id'] ) )
			return $vars['max_id'];
		else
			return null;

	}

	public function search_response( $r ) {

		$response = new TT_Response;

		if ( !isset( $r->statuses ) or empty( $r->statuses ) )
			return $response;

		$this->response_statuses( $response, $r->statuses );

		return $response;

	}

	public function timeline_response( $statuses ) {

		if ( !isset( $statuses ) or empty( $statuses ) )
			return false;

		$response = new TT_Response;
		$this->response_statuses( $response, $statuses );

		return $response;

	}

	public function response_statuses( & $response, $statuses ) {
		foreach ( $statuses as $status ) {

			$item = new TT_Tweet;

			// @TODO Check protected status and drop tweets with it

			$item->set_id( $status->id_str );
			$item->set_link( self::status_url( $status ) );
			$item->set_content( self::status_content( $status ) );
			$item->set_timestamp( strtotime( $status->created_at ) );
			$item->set_twit( $status->user->screen_name );
			$item->set_twit_name( $status->user->name );
			$item->set_twit_uid( $status->user->id_str );
			$item->set_retweeted( self::status_retweeted( $status ) );
			$item->set_original_twit( self::status_original_twit( $status ) );
			$item->set_reply_to( $status->in_reply_to_status_id_str );
			$item->set_hashtags( self::status_hashtags( $status ) );
			$item->set_thumbnail( self::status_avatar( $status ) );

			$response->add_item( $item );

		}
		return $response;
	}

	public function tabs() {
		return array(
			#'welcome' => array(
			#	'text' => _x( 'Welcome', 'Tab title', 'twitter-tracker'),
			#),
			'all' => array(
				'text'    => _x( 'All', 'Tab title', 'twitter-tracker'),
				'default' => true
			),
			'hashtag' => array(
				'text' => _x( 'With Hashtag', 'Tab title', 'twitter-tracker'),
			),
			#'images' => array(
			#	'text' => _x( 'With Images', 'Tab title', 'twitter-tracker'),
			#),
			'by_user' => array(
				'text' => _x( 'By User', 'Tab title', 'twitter-tracker'),
			),
			'to_user' => array(
				'text' => _x( 'To User', 'Tab title', 'twitter-tracker'),
			),
			'location' => array(
				'text' => _x( 'By Location', 'Tab title', 'twitter-tracker'),
			),
		);
	}

	public function requires() {
		return array(
			'oauth' => 'TT_OAuthConsumer'
		);
	}

	public function labels() {
		return array(
			'title'     => sprintf( __( 'Insert from %s', 'twitter-tracker' ), 'Twitter' ),
			# @TODO the 'insert' button text gets reset when selecting items. find out why.
			'insert'    => __( 'Insert Tweet', 'twitter-tracker' ),
			'noresults' => __( 'No tweets matched your search query', 'twitter-tracker' ),
			'gmaps_url' => set_url_scheme( 'http://maps.google.com/maps/api/js' )
		);
	}

	protected function bool_to_str( $arg ) {
		if ( is_bool( $arg ) )
			$arg = $arg ? 'true' : 'false';
		return $arg;
	}

	private function get_connection() {

		$credentials = $this->get_credentials();

		# Despite saying that application-only authentication for search would be available by the
		# end of March 2013, Twitter has still not implemented it. This means that for API v1.1 we
		# still need user-level authentication in addition to application-level authentication.
		#
		# If the time comes that application-only authentication is made available for search, the
		# use of the oauth_token and oauth_token_secret fields below can simply be removed.
		#
		# Further bedtime reading:
		#
		# https://dev.twitter.com/discussions/11079
		# https://dev.twitter.com/discussions/13210
		# https://dev.twitter.com/discussions/14016
		# https://dev.twitter.com/discussions/15744

		foreach ( array( 'consumer_key', 'consumer_secret', 'oauth_token', 'oauth_token_secret' ) as $field ) {
			if ( !isset( $credentials[$field] ) or empty( $credentials[$field] ) ) {
				return new WP_Error(
					'tt_twitter_no_connection',
					__( 'oAuth connection to Twitter not found.', 'twitter-tracker' )
				);
			}
		}

		if ( !class_exists( 'TT_Twitter_OAuth' ) )
			require_once dirname( __FILE__ ) . '/class.wp-twitter-oauth.php';

		$connection = new TT_Twitter_OAuth(
			$credentials['consumer_key'],
			$credentials['consumer_secret'],
			$credentials['oauth_token'],
			$credentials['oauth_token_secret']
		);

		$connection->useragent = sprintf( 'Twitter Tracker at %s', home_url() );

		return $connection;

	}

	private function get_credentials() {

		if ( is_null( $this->credentials ) )
			$this->credentials = (array) apply_filters( 'tt_twitter_credentials', array() );

		return $this->credentials;

	}

}
