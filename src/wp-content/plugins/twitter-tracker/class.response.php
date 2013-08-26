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

final class TT_Response {

	public $items = array();
	public $meta  = array(
		'count'  => null,
		'max_id' => null,
		'min_id' => null,
	);

	public function add_meta( $key, $value = null ) {

		if ( is_array( $key ) ) {

			foreach ( $key as $k => $v )
				$this->meta[$key] = $value;

		} else {

			$this->meta[$key] = $value;

		}

	}

	public function add_item( TT_Tweet $item ) {
		$this->items[] = $item;
	}

	public function have_tweets() {
		return ! empty( $this->items );
	}

	public function remove_retweets() {
		foreach ( $this->items as $i => & $item )
			if ( $item->retweeted )
				unset( $this->items[ $i ] );
		$this->items = array_values( $this->items );
	}

	public function remove_replies() {
		foreach ( $this->items as $i => & $item )
			// error_log( "SW: Item $item->in_reply_to_status_id_str : $item->content " );
			if ( ! is_null( $item->in_reply_to_status_id_str ) )
				unset( $this->items[ $i ] );
		$this->items = array_values( $this->items );
	}

	public function remove_without_hash( $hashtag ) {
		$hashtag = strtolower( $hashtag );
		foreach ( $this->items as $i => & $item ) {
			// Case insensitive in_array search, pls
			if ( ! in_array( $hashtag, array_map( 'strtolower', $item->hashtags ) ) )
				unset( $this->items[ $i ] );
		}
		$this->items = array_values( $this->items );
	}

	public function convert_emoji() {
		require_once( dirname( __FILE__ ) . '/emoji/emoji.php' );
		foreach ( $this->items as $i => & $item )
			$item->content = tt_emoji_unified_to_html( $item->content );
	}

}

final class TT_Tweet {

	public $id; // Numerical Twitter tweet ID, as a string
	public $content; // HTML text
	public $link; // URL for the Tweet on Twitter.com
	public $timestamp;
	public $date;
	public $twit; // Twitter screen name, e.g. "simonwheatley"
	public $twit_name; // Twitter real name, e.g. "Simon Wheatley"
	public $twit_link; // URL for the Twit's Twitter profile page on Twitter.com
	public $twit_pic; // URL for the Twit's Twitter avatar in a reasonable size
	public $twit_pic_bigger; // URL for a bigger version of the Twit's Twitter avatar
	public $twit_uid; // Numerical Twitter user ID
	public $retweeted; // Bool, was this a retweet
	public $original_twit; // Twitter screen name of the original twit who has been retweeted, e.g. "simonwheatley"
	public $in_reply_to_status_id_str; // The Twitter status ID this is in response to, as string
	public $hashtags; // An array of hashtags as strings

	/**
	 * Set the Twitter ID
	 *
	 * @param string $id_str The Twitter tweet ID, as a string to prevent issues on 32 bit systems
	 * @return void
	 * @author simonwheatley
	 **/
	public function set_id( $id_str ) {
		$this->id = $this->sanitise_id_str( $id_str );
	}

	/**
	 * Set the Twitter user screen name
	 *
	 * @param string $twit The Twitter tweet user screen name
	 * @return void
	 * @author simonwheatley
	 **/
	public function set_twit( $twit ) {
		$this->twit = $twit;
		$this->twit_link = sprintf( 'http://twitter.com/%s/', $twit );
	}

	/**
	 * Set the Twitter user ID as a string, to prevent overflows on 32 bit
	 * (though we're a ways off that yet with user IDs)
	 *
	 * @param string $twit The Twitter tweet user ID (as a string)
	 * @return void
	 * @author simonwheatley
	 **/
	public function set_twit_uid( $twit_uid ) {
		$this->twit_uid = $twit_uid;
	}

	/**
	 * Set the Tweet URL
	 *
	 * @param string $url The Tweet URL
	 * @return void
	 * @author simonwheatley
	 **/
	public function set_link( $url ) {
		$this->link = esc_url_raw( $url );
	}

	/**
	 * Set the Tweet content as HTML
	 *
	 * @param string $content The Tweet content
	 * @return void
	 * @author simonwheatley
	 **/
	public function set_content( $content ) {
		$this->content = $content;
	}

	/**
	 * Set the Twit's Avatar URL
	 *
	 * @param string $url The Twit's avatar URL
	 * @return void
	 * @author simonwheatley
	 **/
	public function set_thumbnail( $url ) {
		$url = apply_filters( 'tt_avatar_url', $url, $this->id, 48, $this );
		$this->twit_pic = esc_url_raw( $url );
	}

	/**
	 * Set the Tweet timestamp
	 *
	 * @param int $timestamp The timestamp of the tweet
	 * @return void
	 * @author simonwheatley
	 **/
	public function set_timestamp( $timestamp ) {
		$this->timestamp = absint( $timestamp );
		$this->date = date( 'M n, Y  @ G:i', $this->timestamp );
	}
	
	public function set_twit_name( $twit_name ) {
		$this->twit_name = $twit_name;
	}
	
	public function set_retweeted( $retweeted ) {
		$this->retweeted = (bool) $retweeted;
	}

	/**
	 * Set the retweeted Twit's screen name
	 *
	 * @param string $twit The retweeted Twit's screen name
	 * @return void
	 * @author simonwheatley
	 **/
	public function set_original_twit( $twit ) {
		$this->original_twit = $twit;
	}

	/**
	 * Set the Twitter ID of the status this tweet is in response to
	 *
	 * @param string $id_str The Twitter tweet ID, as a string to prevent issues on 32 bit systems
	 * @return void
	 * @author simonwheatley
	 **/
	public function set_reply_to( $id_str ) {
		$this->in_reply_to_status_id_str = $this->sanitise_id_str( $id_str );
	}

	public function set_hashtags( $hashtags ) {
		$this->hashtags = $hashtags;
	}

	/**
	 * Ripped off from the bb_since bbPress function
	 */
	public function time_since( $do_more = 0 ) {
		$today = time();

		// array of time period chunks
		$chunks = array(
			( 60 * 60 * 24 * 365 ), // years
			( 60 * 60 * 24 * 30 ),  // months
			( 60 * 60 * 24 * 7 ),   // weeks
			( 60 * 60 * 24 ),       // days
			( 60 * 60 ),            // hours
			( 60 ),                 // minutes
			( 1 )                   // seconds
		);

		$since = $today - $this->timestamp;

		for ($i = 0, $j = count($chunks); $i < $j; $i++) {
			$seconds = $chunks[$i];

			if ( 0 != $count = floor($since / $seconds) )
				break;
		}

		$trans = array(
			$this->pluralise( __('%d year', 'twitter-tracker'), __('%d years', 'twitter-tracker'), $count ),
			$this->pluralise( __('%d month', 'twitter-tracker'), __('%d months', 'twitter-tracker'), $count ),
			$this->pluralise( __('%d week', 'twitter-tracker'), __('%d weeks', 'twitter-tracker'), $count ),
			$this->pluralise( __('%d day', 'twitter-tracker'), __('%d days', 'twitter-tracker'), $count ),
			$this->pluralise( __('%d hour', 'twitter-tracker'), __('%d hours', 'twitter-tracker'), $count ),
			$this->pluralise( __('%d minute', 'twitter-tracker'), __('%d minutes', 'twitter-tracker'), $count ),
			$this->pluralise( __('%d second', 'twitter-tracker'), __('%d seconds', 'twitter-tracker'), $count )
		);

		$basic = sprintf( $trans[$i], $count );

		if ( $do_more && $i + 1 < $j) {
			$seconds2 = $chunks[$i + 1];
			if ( 0 != $count2 = floor( ($since - $seconds * $count) / $seconds2) ) {
				$trans = array(
					$this->pluralise( __('a year', 'twitter-tracker'), __('%d years', 'twitter-tracker'), $count2 ),
					$this->pluralise( __('a month', 'twitter-tracker'), __('%d months', 'twitter-tracker'), $count2 ),
					$this->pluralise( __('a week', 'twitter-tracker'), __('%d weeks', 'twitter-tracker'), $count2 ),
					$this->pluralise( __('a day', 'twitter-tracker'), __('%d days', 'twitter-tracker'), $count2 ),
					$this->pluralise( __('an hour', 'twitter-tracker'), __('%d hours', 'twitter-tracker'), $count2 ),
					$this->pluralise( __('a minute', 'twitter-tracker'), __('%d minutes', 'twitter-tracker'), $count2 ),
					$this->pluralise( __('a second', 'twitter-tracker'), __('%d seconds', 'twitter-tracker'), $count2 )
				);
				$additional = sprintf( $trans[$i + 1], $count2 );
			}
			
			$final = sprintf( __( 'about %s, %s ago', 'twitter-tracker' ), $basic, $additional );
			return $final;
		}
		$final = sprintf( __( 'about %s ago', 'twitter-tracker' ), $basic );
		return $final;
	}
	
	// e.g. Jul 30, 2009 @ 10:01
	protected function set_tweet_date() {
		$this->date = date( 'M n, Y  @ G:i', $this->timestamp );
	}

	protected function pluralise( $singular, $plural, $count ) {
		if ( $count == 0 || $count > 1 ) return $plural;
		return $singular;
	}

	/**
	 * We cannot sanitise some ID strings by converting to integers, as 
	 * they will overflow 32 bit systems and corrupt data. This method
	 * sanitises without converting to ints.
	 * 
	 * @param string $id_str An integer ID represented as a string to be sanitised
	 * @return string A sanitised integer ID 
	 */
	public function sanitise_id_str( $id_str ) {
		if ( is_null( $id_str ) )
			return $id_str;
		$id_str = preg_replace( '/[^\d]/', '', (string) $id_str );
		return $id_str;
	}


}
