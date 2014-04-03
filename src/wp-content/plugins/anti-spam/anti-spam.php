<?php
/*
Plugin Name: Anti-spam
Plugin URI: http://wordpress.org/plugins/anti-spam/
Description: No spam in comments. No captcha.
Version: 2.1
Author: webvitaly
Author URI: http://web-profile.com.ua/wordpress/plugins/
License: GPLv3
*/

$antispam_send_spam_comment_to_admin = false; // if true, than rejected spam comments will be sent to admin email

$antispam_allow_trackbacks = false; // if true, than trackbacks will be allowed
// trackbacks almost not used by users, but mostly used by spammers; pingbacks are always enabled
// more about the difference between trackback and pingback - http://web-profile.com.ua/web/trackback-vs-pingback/

$antispam_version = '2.1';


if ( ! function_exists( 'antispam_scripts_styles_init' ) ) :
	function antispam_scripts_styles_init() {
		global $antispam_version;
		if ( !is_admin() ) { // && is_singular() && comments_open() && get_option( 'thread_comments' )
			//wp_enqueue_script('jquery');
			wp_enqueue_script( 'anti-spam-script', plugins_url( '/js/anti-spam.js', __FILE__ ), array( 'jquery' ), $antispam_version );
		}
	}
	add_action('init', 'antispam_scripts_styles_init');
endif; // end of antispam_scripts_styles_init()


if ( ! function_exists( 'antispam_form_part' ) ) :
	function antispam_form_part() {
		if ( ! is_user_logged_in() ) { // add anti-spam fields only for not logged in users
			$antispam_form_part = '
	<p class="comment-form-ant-spm" style="clear:both;">
		<strong>Current <span style="display:none;">day</span> <span style="display:none;">month</span> <span style="display:inline;">ye@r</span></strong> <span class="required">*</span>
		<input type="hidden" name="ant-spm-a" id="ant-spm-a" value="'.date('Y').'" />
		<input type="text" name="ant-spm-q" id="ant-spm-q" size="30" value="21" />
	</p>
	'; // question (hidden with js) [aria-required="true" required="required"]
			$antispam_form_part .= '
	<p class="comment-form-ant-spm-2" style="display:none;">
		<strong>Leave this field empty</strong> <span class="required">*</span>
		<input type="text" name="ant-spm-e-email-url" id="ant-spm-e-email-url" size="30" value=""/>
	</p>
	'; // empty field (hidden with css)
			echo $antispam_form_part;
		}
	}
	add_action( 'comment_form', 'antispam_form_part' ); // add anti-spam input to the comment form
endif; // end of antispam_form_part()


if ( ! function_exists( 'antispam_check_comment' ) ) :
	function antispam_check_comment( $commentdata ) {
		global $antispam_send_spam_comment_to_admin, $antispam_allow_trackbacks;
		extract( $commentdata );

		$antispam_pre_error_message = '<p><strong><a href="javascript:window.history.back()">Go back</a></strong> and try again.</p>';
		$antispam_error_message = '';

		if ( $antispam_send_spam_comment_to_admin ) { // if sending email to admin is enabled
			$antispam_admin_email = get_option('admin_email');  // admin email

			$post = get_post( $comment->comment_post_ID );
			$antispam_message_spam_info  = 'Spam for post: "'.$post->post_title.'"' . "\r\n";
			$antispam_message_spam_info .= get_permalink( $comment->comment_post_ID ) . "\r\n\r\n";

			$antispam_message_spam_info .= 'IP : ' . $_SERVER['REMOTE_ADDR'] . "\r\n";
			$antispam_message_spam_info .= 'User agent : ' . $_SERVER['HTTP_USER_AGENT'] . "\r\n";
			$antispam_message_spam_info .= 'Referer : ' . $_SERVER['HTTP_REFERER'] . "\r\n\r\n";

			$antispam_message_spam_info .= 'Comment data:'."\r\n"; // lets see what comment data spammers try to submit
			foreach ( $commentdata as $key => $value ) {
				$antispam_message_spam_info .= '$commentdata['.$key. '] = '.$value."\r\n"; // .chr(13).chr(10)
			}
			$antispam_message_spam_info .= "\r\n\r\n";

			$antispam_message_spam_info .= 'Post vars:'."\r\n"; // lets see what post vars spammers try to submit
			foreach ( $_POST as $key => $value ) {
				$antispam_message_spam_info .= '$_POST['.$key. '] = '.$value."\r\n"; // .chr(13).chr(10)
			}
			$antispam_message_spam_info .= "\r\n\r\n";

			$antispam_message_spam_info .= 'Cookie vars:'."\r\n"; // lets see what cookie vars spammers try to submit
			foreach ( $_COOKIE as $key => $value ) {
				$antispam_message_spam_info .= '$_COOKIE['.$key. '] = '.$value."\r\n"; // .chr(13).chr(10)
			}
			$antispam_message_spam_info .= "\r\n\r\n";

			$antispam_message_append = '-----------------------------'."\r\n";
			$antispam_message_append .= 'This is spam comment rejected by Anti-spam plugin - wordpress.org/plugins/anti-spam/' . "\r\n";
			$antispam_message_append .= 'You may edit "anti-spam.php" file and disable this notification.' . "\r\n";
			$antispam_message_append .= 'You should find "$antispam_send_spam_comment_to_admin" and make it equal to "false".' . "\r\n";
		}

		if ( ! is_user_logged_in() && $comment_type != 'pingback' && $comment_type != 'trackback' ) { // logged in user is not a spammer
			$spam_flag = false;

			if ( trim( $_POST['ant-spm-q'] ) != date('Y') ) { // answer is wrong - maybe spam
				$spam_flag = true;
				if ( empty( $_POST['ant-spm-q'] ) ) { // empty answer - maybe spam
					$antispam_error_message .= 'Error: empty answer. ['.$_POST['ant-spm-q'].']<br> ';
				} else {
					$antispam_error_message .= 'Error: answer is wrong. ['.$_POST['ant-spm-q'].']<br> ';
				}
			}
			if ( ! empty( $_POST['ant-spm-e-email-url'] ) ) { // field is not empty - maybe spam
				$spam_flag = true;
				$antispam_error_message .= 'Error: field should be empty. ['.$_POST['ant-spm-e-email-url'].']<br> ';
			}
			if ( $spam_flag ) { // if we have spam
				if ( $antispam_send_spam_comment_to_admin ) { // if sending email to admin is enabled

					$antispam_subject = 'Spam comment on site ['.get_bloginfo( 'name' ).']'; // email subject
					$antispam_message = '';

					$antispam_message .= $antispam_error_message . "\r\n\r\n";

					$antispam_message .= $antispam_message_spam_info; // spam comment, post, cookie and other data

					$antispam_message .= $antispam_message_append;

					@wp_mail( $antispam_admin_email, $antispam_subject, $antispam_message ); // send spam comment to admin email
				}
				wp_die( $antispam_pre_error_message . $antispam_error_message ); // die - do not send comment and show errors
			}
		}

		if ( ! $antispam_allow_trackbacks ) { // if trackbacks are blocked (pingbacks are alowed)
			if ( $comment_type == 'trackback' ) { // if trackbacks ( || $comment_type == 'pingback' )
				$antispam_error_message .= 'Error: trackbacks are disabled.<br> ';
				if ( $antispam_send_spam_comment_to_admin ) { // if sending email to admin is enabled
					$antispam_subject = 'Spam trackback on site ['.get_bloginfo( 'name' ).']'; // email subject

					$antispam_message = '';

					$antispam_message .= $antispam_error_message . "\r\n\r\n";

					$antispam_message .= $antispam_message_spam_info; // spam comment, post, cookie and other data

					$antispam_message .= $antispam_message_append;

					@wp_mail( $antispam_admin_email, $antispam_subject, $antispam_message ); // send trackback comment to admin email
				}
				wp_die( $antispam_pre_error_message . $antispam_error_message ); // die - do not send trackback
			}
		}

		return $commentdata; // if comment does not looks like spam
	}

	if ( ! is_admin() ) {
		add_filter( 'preprocess_comment', 'antispam_check_comment', 1 );
	}
endif; // end of antispam_check_comment()


if ( ! function_exists( 'antispam_plugin_meta' ) ) :
	function antispam_plugin_meta( $links, $file ) { // add 'Plugin page' and 'Donate' links to plugin meta row
		if ( strpos( $file, 'anti-spam.php' ) !== false ) {
			$links = array_merge( $links, array( '<a href="http://web-profile.com.ua/wordpress/plugins/anti-spam/" title="Plugin page">Anti-spam</a>' ) );
			$links = array_merge( $links, array( '<a href="http://web-profile.com.ua/donate/" title="Support the development">Donate</a>' ) );
			$links = array_merge( $links, array( '<a href="http://codecanyon.net/item/antispam-pro/6491169" title="Anti-spam Pro">Anti-spam Pro</a>' ) );
		}
		return $links;
	}
	add_filter( 'plugin_row_meta', 'antispam_plugin_meta', 10, 2 );
endif; // end of antispam_plugin_meta()