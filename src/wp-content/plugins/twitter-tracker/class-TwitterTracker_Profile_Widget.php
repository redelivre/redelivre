<?php 

/*  
	Copyright 2011 Simon Wheatley

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

require_once( dirname (__FILE__) . '/class-TwitterTracker_SW_Widget.php' );

/**
 * TwitterTracker widget class
 */
class TwitterTracker_Profile_Widget extends TwitterTracker_SW_Widget {

    /** constructor */
    function TwitterTracker_Profile_Widget() {
		$name = __( 'Twitter Profile Tracker', 'twitter-tracker' );
		$options = array( 'description' => __( 'A widget which displays the tweets from a specific Twitter user account.', 'twitter-tracker' ) );
        $this->WP_Widget( 'twitter-profile-tracker', $name, $options );	
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {
        extract( $args );
        extract( $instance );

        $class = isset( $class ) ? $class : '';
		
		// Add any additional classes
		$before_widget = $this->add_classes( $before_widget, $class );
		
		echo $before_widget;

		if ( $title ) echo $before_title . $title . $after_title;

		twitter_tracker_profile( $instance );

		echo $after_widget;
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {
		// Delete the cache
		delete_option( 'twitter-tracker-profile' );
		$new_instance[ 'title' ] = $this->maybe_strip_tags( $new_instance[ 'title' ] );
		$new_instance[ 'preamble' ] = $this->maybe_wp_kses( $new_instance[ 'preamble' ], 'preamble' );
		$new_instance[ 'username' ] = strip_tags( $new_instance[ 'username' ] );
		$new_instance[ 'hide_replies' ] = isset( $new_instance[ 'hide_replies' ] ) ? (bool) $new_instance[ 'hide_replies' ] : false;
		$new_instance[ 'max_tweets' ] = absint( $new_instance[ 'max_tweets' ] );
		$new_instance[ 'include_retweets' ] = isset( $new_instance[ 'include_retweets' ] ) ? (bool) $new_instance[ 'include_retweets' ] : false;
		$new_instance[ 'mandatory_hash' ] = strip_tags( $new_instance[ 'mandatory_hash' ] );
		$new_instance[ 'html_after' ] = $this->maybe_wp_kses( $new_instance[ 'html_after' ], 'html_after' );
		$new_instance[ 'class' ] = strip_tags( $new_instance[ 'class' ] );

        $convert_emoji = 'hide' == $new_instance[ 'convert_emoji' ] ? 'hide' : 'convert';
		update_option( 'tt_convert_emoji', $convert_emoji );

        return $new_instance;
    }

    /** @see WP_Widget::form */
    function form( $instance ) {
		extract( $instance );
        $title = isset( $title ) ? esc_attr( $title ) : '';
		$preamble = isset( $preamble ) ? esc_attr( $preamble ) : '';
		$username = isset( $username ) ? esc_attr( $username ) : '';
		$max_tweets = isset( $max_tweets ) && 0 !== $max_tweets ? esc_attr( $max_tweets ) : 3;
		$hide_replies = isset( $hide_replies ) ? (bool) $hide_replies : false;
		$include_retweets = isset( $include_retweets ) ? (bool) $include_retweets : false;
		$mandatory_hash = isset( $mandatory_hash ) ? esc_attr( $mandatory_hash ) : '';
		$html_after = isset( $html_after ) ? esc_attr( $html_after ) : '';
		$class = isset( $class ) ? esc_attr( $class ) : '';

        $convert_emoji = 'hide' == get_option( 'tt_convert_emoji', 'hide' ) ? 'hide' : 'convert';

		// Now show the input fields
		$this->input_text( __( 'Title:', 'twitter-tracker' ), 'title', $title );
		$this->input_text( __( 'Preamble (HTML limited to <kbd>&lt;a&gt;</kbd>, <kbd>&lt;em&gt;</kbd>, <kbd>&lt;strong&gt;</kbd>, <kbd>&lt;p&gt;</kbd>, <kbd>&lt;br&gt;</kbd>):', 'twitter-tracker' ), 'preamble', $preamble );
		$this->input_text( __( 'Username:', 'twitter-tracker' ), 'username', $username );
		$this->input_conversational_mini_text( __( 'Max tweets to show:', 'twitter-tracker' ), 'max_tweets', $max_tweets );
		$replies_note = __( 'When replies are hidden the widget will <em>attempt</em> to keep the number of tweets constant, however this may not be possible.', 'twitter-tracker' );
		$this->input_checkbox( __( 'Hide @ replies:', 'twitter-tracker' ), 'hide_replies', $hide_replies, $replies_note );
		$this->input_checkbox( __( 'Include retweets:', 'twitter-tracker' ), 'include_retweets', $include_retweets );
		$options = array( 'hide' => __( 'Hide all Emoji', 'twitter-tracker' ), 'convert' => __( 'Show all Emoji as images', 'twitter-tracker' ) );
		$emoji_note = sprintf( __( 'Showing Emoji is a setting which applies to <strong>all</strong> Twitter Tracker widgets in your site, it also involves downloading a 700kb image and a 40kb CSS file, which will increase page load times for your website. If you don’t know what Emoji are, this is <a href="%s" target="_blank">a good introduction</a>.', 'twitter-tracker' ), 'http://www.iamcal.com/emoji-in-web-apps/' );
		$this->input_radios( __( 'Show or hide Emoji in ALL Twitter Tracker widgets?', 'twitter-tracker' ), 'convert_emoji', $options, $convert_emoji, $emoji_note, $no_selection = false );
		$hashtag_note = __( 'Include the "#". Tweets without this #hashtag will not be shown.', 'twitter-tracker' );
		$this->input_text( __( 'Mandatory hashtag:', 'twitter-tracker' ), 'mandatory_hash', $mandatory_hash, $hashtag_note );
		$this->input_text( __( 'HTML to put after the results:', 'twitter-tracker' ), 'html_after', $html_after, __( 'Optional, use for things like a link to this Twitter search, etc.', 'twitter-tracker' ) );
		$class_note = __( 'You can put an individual class, or classes (separate with spaces), on each instance of the Twitter Tracker to enable you to style them differently.', 'twitter-tracker' );
		$this->input_text( __( 'HTML Class:', 'twitter-tracker' ), 'class', $class, $class_note );
    }

	protected function add_classes( $before_widget, $class )
	{
		$classes = "widget_twitter-profile-tracker " . esc_attr( $class ) . " ";
		return str_replace( 'widget_twitter-profile-tracker', $classes, $before_widget );
	}

}

?>