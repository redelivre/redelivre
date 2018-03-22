<?php
/*
Plugin Name: Mention comment's Authors
Plugin URI: http://wabeo.fr
Description: "Mention comment's authors" is a plugin that improves the WordPress comments fonctionality, adding a response system between authors.
When adding a comment, your readers can directly mentioning the author of another comment, like facebook or twitter do,using the "@" symbol.
Version: 0.9.8
Author: Willy Bahuaud
Author URI: http://wabeo.fr
License: GPLv2 or later
*/

/**
INIT CONSTANT & LANGS
*/
DEFINE( 'MCA_PLUGIN_URL', trailingslashit( WP_PLUGIN_URL ) . basename( dirname( __FILE__ ) ) );
DEFINE( 'MCA_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
DEFINE( 'MCA_PLUGIN_VERSION', '0.9.8' );

add_action( 'init', 'mca_lang_init' );
function mca_lang_init() {
    load_plugin_textdomain( 'mca', false, basename( dirname( __FILE__ ) ) . '/langs/' );
    global $mcaAuthors;
    $mcaAuthors = array();
}

/**
LOAD JS ON FRONT OFFICE
* a classic script enqueue

* @uses mca-load-styles FILTER HOOK to allow/disallow css enqueue
* @uses mcaajaxenable FILTER HOOK to turn plugin into ajax mod (another script is loaded, different functions are used)
* @since 0.9.7 No need to enqueue jQuery anymore
* @since 0.9.7 include minified version for scripts and stylesheets
*/
add_action( 'wp_enqueue_scripts', 'mca_enqueue_comments_scripts' );
function mca_enqueue_comments_scripts() {
    $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
    wp_register_style( 'mca-styles', MCA_PLUGIN_URL . '/css/mca-styles.min.css', false, MCA_PLUGIN_VERSION, 'all' );
    if( apply_filters( 'mca-load-styles', true ) ) {
        wp_enqueue_style( 'mca-styles' );
    }

    wp_register_script( 'jquery-mention', MCA_PLUGIN_URL . '/js/jquery-mention' . $suffix . '.js', array( 'jquery' ), MCA_PLUGIN_VERSION, true );
    wp_register_script( 'mca-comment-script', MCA_PLUGIN_URL . '/js/mca-comment-script' . $suffix . '.js', array( 'jquery','jquery-mention' ), MCA_PLUGIN_VERSION, true );
    wp_register_script( 'mca-comment-script-ajax', MCA_PLUGIN_URL . '/js/mca-comment-script-ajax' . $suffix . '.js', array( 'jquery','jquery-mention' ), MCA_PLUGIN_VERSION, true );

    if( ! apply_filters( 'mcaajaxenable', false ) ) {
        wp_enqueue_script( 'mca-comment-script' );
        wp_localize_script( 'mca-comment-script', 'mcaCommentTextarea', apply_filters( 'mca_comment_form', 'textarea[name="comment"]' ) );
    } else {
        wp_enqueue_script( 'mca-comment-script-ajax' );
        wp_localize_script( 'mca-comment-script-ajax', 'mcaCommentTextarea', apply_filters( 'mca_comment_form', 'textarea[name="comment"]' ) );
    }
}

/**
LOAD JS ON BACK OFFICE
* a classic script enqueue

*/
add_action( 'admin_enqueue_scripts', 'mca_enqueue_admin_comments_scripts' );
function mca_enqueue_admin_comments_scripts() {
    if ( current_user_can( 'edit_posts' ) ) {
        $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
        wp_register_style( 'mca-styles', MCA_PLUGIN_URL . '/css/mca-styles.min.css', false, MCA_PLUGIN_VERSION, 'all' );
        wp_enqueue_style( 'mca-styles' );

        wp_register_script( 'jquery-mention', MCA_PLUGIN_URL . '/js/jquery-mention' . $suffix . '.js', array( 'jquery' ), MCA_PLUGIN_VERSION, true );
        wp_register_script( 'mca-admin-comment-script', MCA_PLUGIN_URL . '/js/mca-admin-comment-script' . $suffix . '.js', array( 'jquery', 'jquery-mention', 'admin-comments' ), MCA_PLUGIN_VERSION, true );

        $screen = get_current_screen();
        if ( in_array( $screen->base, array( 'comment', 'post', 'edit-comments', 'dashboard' ) ) 
          && current_user_can( 'moderate_comments' ) ) {
            wp_enqueue_script( 'mca-admin-comment-script' );
        }
        if ( 'comment' == $screen->base && current_user_can( 'moderate_comments' ) ) {
            $comment = get_comment( intval( $_GET['c'] ) );
            $old_authors = mca_get_previous_commentators( $comment->comment_post_ID, $comment->comment_ID );
            $authors = array();
            foreach ( $old_authors as $k => $author ) {
                $authors[] = array( 'val' => $k, 'meta' => $author );
            }
            wp_register_script( 'mca-admin-editcomment-script', MCA_PLUGIN_URL . '/js/mca-admin-editcomment-script' . $suffix . '.js', array( 'jquery', 'jquery-mention', 'admin-comments' ), MCA_PLUGIN_VERSION, true );
            wp_enqueue_script( 'mca-admin-editcomment-script' );
            wp_localize_script( 'mca-admin-editcomment-script', 'oldAuthors', $authors );
        }
    }
}

/**
CATCH NAME IN COMMENTS & ADD ANCHOR LINK (OR OPACITY)
* mca_modify_comment_text FUNCTION will rewrite the comment text, including buttons + some usefull datas. It based on comment_text FILTER HOOK !!
* mca_comment_callback FUNCTION is the working callback

* @var mcaAuthors ARRAY to receive list of authors
* @var modifiedcontent VARCHAR contant avec preg_replace_all

* @uses mca_get_previous_commentators FUNCTION to retrieve full list of authors (only ajax mod)
* @uses mcaajaxenable FILTER HOOK to turn plugin into ajax mod (another script is loaded, different functions are used)

* @since 0.9.7 apply mention system also on get_comment_excerpt
*/
add_filter('comment_text', 'mca_modify_comment_text', 10, 2);
function mca_modify_comment_text( $content, $com = '' ) {
    if ( is_admin() ) {
        $modifiedcontent = preg_replace_callback('/((?:^|\s))\@([a-zA-Z0-9-]*)((?:$|\s|\.|,))/', 'mca_comment_admin_callback', $content);
        return $modifiedcontent;
    }
    global $mcaAuthors;

    if( apply_filters( 'mcaajaxenable', false ) ) {
        $mcaAuthors = mca_get_previous_commentators( $com->comment_post_ID, $com->comment_ID );
    } else {
        if( ! is_array( $mcaAuthors ) ) {
            $mcaAuthors = array();
        }

        $newEntry = $com->comment_author;
        if( ! in_array( $newEntry, $mcaAuthors ) ) {
            $mcaAuthors[ sanitize_title( $com->comment_author ) ] = $newEntry;
        }
    }
    //Rearrange content
    $modifiedcontent = preg_replace_callback('/((?:^|\s))\@([a-zA-Z0-9-]*)((?:$|\s|\.|,))/', 'mca_comment_callback', $content);
    if( apply_filters( 'mcaajaxenable', false ) ) {
        return '<div class="mca-author" data-name="' . sanitize_title( $com->comment_author ) . '" data-realname="' . esc_attr( $com->comment_author ) . '">' . $modifiedcontent . '</div>';
    } else {
        return '<div class="mca-author" data-name="' . sanitize_title( $com->comment_author ) . '">' . $modifiedcontent . '</div>';
    }
}

add_filter( 'get_comment_excerpt', 'mca_modify_comment_excerpt', 10, 3 );
function mca_modify_comment_excerpt( $excerpt, $comment_id, $com ) {
    return mca_modify_comment_text( $excerpt, $com );
}

function mca_comment_callback( $matches ) {
    global $mcaAuthors;
    $name = ( isset( $mcaAuthors[ $matches[2] ] ) ) ? $mcaAuthors[ $matches[2] ] : $matches[2];
    return $matches[1] . '<button type="button" data-target="' . $matches[2] . '" class="mca-button">@' . $name . '</button>' . $matches[3];
}

function mca_comment_admin_callback( $matches ) {
    return $matches[1] . '<strong style="color:#0074a2;">@' . $matches[2] . '</strong>' . $matches[3];
}

/**
RETRIEVE AUTHORS NAMES ON THE OTHER SIDE (SAVING ONE)
* only on non-ajax mod, will push authors names in script. Start at comment_form ACTION HOOK !!

* @var mcaAuthors ARRAY contain full list of authors
* @var authors ARRAY to receive ordered list of authors

* @uses mcaajaxenable FILTER HOOK to turn plugin into ajax mod (another script is loaded, different functions are used)
*/
add_action( 'comment_form', 'mca_printnames' );
function mca_printnames() {
    if( ! apply_filters( 'mcaajaxenable', false ) ) {
        global $mcaAuthors;

        //reorder $mcaAuthors
        $authors = array();
        foreach( $mcaAuthors as $k => $a )
            $authors[] = array( 'val' => $k, 'meta' => $a );

        if( ! apply_filters( 'mcaajaxenable', false ) ) {
            wp_localize_script( 'mca-comment-script', 'mcaAuthors', $authors );
        }
    }
}

/**
RETRIEVE LAST COMMENTATORS KEYS/NAMES
* usefull function to collect authors names, slug and emails

* @uses mca_get_previous_commentators FUNCTION take 3 args : post ID, comment ID, and a BOOL for retrieve emails or only names
* @uses mca_admin_get_previous_commentators FUNCTION to retrieve old commentators (admin-ajax side)
* @since 0.9.7 include current comment author
*/
function mca_get_previous_commentators( $postid, $commid, $email = false ) {
    global $wpdb;
    $prev = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT comment_author,comment_author_email FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_ID <= %d AND comment_approved = '1'", $postid, $commid ) );
    $out = array();
    if( $email ) {
        foreach( $prev as $p ) {
            $out[ sanitize_title( $p->comment_author ) ] = array( $p->comment_author, $p->comment_author_email );
        }
    } else {
        foreach( $prev as $p ) {
            $out[ sanitize_title( $p->comment_author ) ] = $p->comment_author;
        }
    }
    return $out;
}

add_action( 'wp_ajax_mca_admin_get_previous_commentators', 'mca_admin_get_previous_commentators' );
function mca_admin_get_previous_commentators() {
    if ( isset( $_POST[ 'comment_id'], $_POST[ 'comment_post_id' ] ) ) {
        $comment_post_id = intval( $_POST[ 'comment_post_id' ] );
        if ( ! $_POST[ 'comment_id'] ) {
            // Retrieve last comment ID
            $old_comments = get_comments( array(
                'post_id' => $comment_post_id,
                'status'  => 'approve',
                'number'  => 1
                ) );
            if ( isset( $old_comments[0] ) ) {
                $comment_id = $old_comments[0]->comment_ID;
            } else {
                wp_send_json_error( array( 'error' => 'no old authors' ) );
            }
        } else {
            $comment_id = intval( $_POST[ 'comment_id'] );
        }
        $old_authors = mca_get_previous_commentators( $comment_post_id, $comment_id );
        if ( ! empty( $old_authors ) ) {
            $authors = array();
            foreach ( $old_authors as $k => $author ) {
                $authors[] = array( 'val' => $k, 'meta' => $author );
            }
            wp_send_json_success( $authors );
        } else {
            wp_send_json_error( array( 'error' => 'no old authors' ) );
        }
    }
    wp_send_json_error( array( 'error' => 'missing data' ) );
}

/**
SEND EMAILS TO POKED ONES
* this function send email for poked commentators

* @uses mca_email_poked_ones FUNCTION to send emails (if have to...). It based on comment_post ACTION HOOK
* @uses mca_send_email_on_mention FILTER HOOK to disable sendings
* @uses mca-email-subject FILTER HOOK to alter email subject
* @uses mca-email-message FILTER HOOK to alter email content

* @var comment OBJECT to store current comment datas
* @var prev_authors ARRAY contain lists of comment's authors (including emails...)
* @var pattern REGEX PATTERN
* @var matches ARRAY results of the preg_match_all()

* @since 0.9.6 mail are send only for approved comments
* @since 0.9.2 new mca_filter_recipient FILTER HOOK to filter email recipients
*/
add_action( 'comment_post', 'mca_email_poked_ones', 90, 2 ); // Launching after spam test
function mca_email_poked_ones( $comment_id, $approved ) {
	if( add_filter( 'mca_send_email_on_mention', true ) && in_array( $approved, array( 1, 'approve' )) ) {
        $comment = get_comment( $comment_id );
        $prev_authors = mca_get_previous_commentators( $comment->comment_post_ID, $comment_id, true );
        $prev_authors = apply_filters( 'mca_filter_recipient', $prev_authors, $comment );
        //do preg_match
        $pattern = '/(?:^|\s)\@(' . implode( '|', array_keys( $prev_authors ) ) . ')(?:$|\s|\.|,)/';
        preg_match_all( $pattern, $comment->comment_content, $matches );

        foreach( $matches[1] as $m ) {
            $mail = $prev_authors[ $m ][1];
            $name = $prev_authors[ $m ][0];
            $titre = get_the_title( $comment->comment_post_ID );

            $subject = wp_sprintf( __( ' %s replied to your comment on the article «%s»' , 'mca' ), $comment->comment_author, $titre );
            $subject = apply_filters( 'mca-email-subject', $subject, $comment, $name, $mail, $titre );

            $message = '<div><h1>' . $subject . '</h1><div style="Border:5px solid grey;padding:1em;">' . apply_filters( 'the_content', wp_trim_words( $comment->comment_content, 25 ) ) . "</div></div><p>" . __( 'Read post', 'mca' ) . ' : <a href="' . get_permalink( $comment->comment_post_ID ) . '">' . $titre . '</a> ' . __( 'on', 'mca' ) . ' <a href="' . get_bloginfo( 'url' ) . '">' . get_bloginfo( 'name' ) . '</a></p>';
            $message = apply_filters( 'mca-email-message', $message, $comment, $name, $mail, $titre );

            add_filter( 'wp_mail_content_type', create_function( '', 'return "text/html"; ' ) );
            wp_mail( $mail, $subject, $message );
        }
    }
}

add_action( 'wp_set_comment_status', 'mca_maybe_email_poked_ones', 90, 2 );
function mca_maybe_email_poked_ones( $comment_id, $comment_status ) {
    if ( in_array( $comment_status, array( '1', 'approve' ) ) ) {
        mca_email_poked_ones( $comment_id, 1 );
    }
}
