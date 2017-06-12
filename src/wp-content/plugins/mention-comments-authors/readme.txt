=== Mention comment's Authors by Wabeo ===
Contributors: willybahuaud
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=A4P2WCN4TZK26&lc=FR&item_name=Wabeo&item_number=3
Tags: mention, twitter, facebook, poke, comments, authors, cite, quote, comment, response, answer, commentator, reply, mentions
Requires at least: 3.5
Tested up to: 4.1
Stable tag: 0.9.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

When adding a comment, your users can directly mentioning the author of another comment, like facebook or twitter do,using the "@" symbol.

== Description ==

"Mention comment's authors" is a plugin that improves the WordPress comments fonctionality, adding a response system between authors.
When adding a comment, your readers can directly mentioning the author of another comment, like facebook or twitter do,using the "@" symbol.

This mention plugin add two features :

* In the comments field, when an user entered the "@" symbol, it allows, through an autocompletion system, to quote (or poke) a preceding commentator.
* Once comments validated, the mentioned names take the appearance of buttons. When the user clicks on it, window scrolls to the preceding comment from the person named. A class is added to it, for temporarily customize it in CSS.

This WordPress plugin is based on ["jquery-sew" jQuery plugin](https://github.com/tactivos/jquery-sew), by [mural.ly](https://mural.ly/).

You can find more information on this post : [wabeo : Un système de réponse dans les commentaires](http://wabeo.fr/blog/systeme-reponse-commentaires/)

== Installation ==

= For a non-ajax website =

1. Upload the plugin's folder into `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. All done !

= For an ajax-based website =

1. Upload the plugin's folder into `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Add the line `add_filter( 'mcaajaxenable', '__return_true' );` to your fonctions.php theme file
4. Call the function `mcaAjaxChange();` in your javascript after each succefull ajax refresh
5. Be sure to apply the filter "commment_text" each time you load comments in ajax 
3. All done ! ;-)

== Frequently Asked Questions ==

= How to customize Mention Comment's Authors apparence ? =

You can Easaly overide MCA style, in CSS, because all style use only one class (refer to the *mca-styles.css* file)
But if you prefer, you can dequeue plugin's style and include (and modify) the plugin's stylesheet into your own theme file.

To disable the inclusion of the style sheet, just add this code to the functions.php file of your theme :
`add_filter( 'mca-load-styles', '__return_false' );`

= Why the plugin isn't working ? =

There are several reasons why the plugin does not work:

* make sure your theme uses properly "comment_text" filter hook to display the comments
* make sure your theme uses properly "comment_form" action hook after the comment form
* make sure your theme uses properly "comment_post" action hook after publishing comments (if you're running an ajax based comment system). Don't forget to pass the arguments to this hook.
* make sure there are no conflit between the plugin and your javascripts file (regards to dependancies !), maybe your script have to load after the plugin...

= How to disable (or filter) mail sending ? =

The plugin automatically sends an email to comment's authors having been mentioned by another user.
If you want to disable this feature, just paste this code to the functions.php file of your theme :
`add_filter( 'mca_send_email_on_mention', '__return_false' );`

But if you want, you can also and conditions.
To help you filter, the hook embeds the comment and the list of recipients expected.

For example, if you want to doesn't send mail to commentators already mailed by the "subscribe to comments" plugin, You can do this :
`add_filter( 'mca_filter_recipient','dont_send_user_who_already_subscribe', 100, 2 );
function dont_send_user_who_already_subscribe( $recipients, $comment ) {
    global $wpdb;
    $su = $wpdb->get_results( "
        SELECT comment_author 
        FROM {$wpdb->comments} 
        WHERE comment_subscribe = 'Y' 
        AND comment_post_ID = {$comment->comment_post_ID};", ARRAY_N );

    foreach( $su as $val )
        if( array_key_exists( sanitize_title( $val ), $recipients ) )
            unset( $recipients[ sanitize_title( $val ) ] );

    return $recipients;
}`

= How to customize mail subject ? =

You can use the `mca-email-subject` filter.

For example:

`add_filter( 'mca-email-subject', 'my_mail_subject', 10, 5 );
function my_mail_subject( $subject, $comment, $name, $mail, $title ) {
    // $subject is actual text
    // $comment is the comment object
    // $name and $mail of the comment author
    // $title is the post title
    return sprintf( 'Hello, %s answer to you on %s !', $name, $title );
}`

= How to customize mail content ? =

You can use the `mca-email-subject` filter.

For example:

`add_filter( 'mca-email-message', 'my_mail_content', 10, 5 );
function my_mail_content( $content, $comment, $name, $mail, $title ) {
    // $content is actual text
    // $comment is the comment object
    // $name and $mail of the comment author
    // $title is the post title
    $out = array( sprintf( '<p>%s just answer to you !<br> Her is his message:</p>', $name ) );
    $out[] = '<blockquote>' . wp_trim_words( $comment->comment_content, 80 ) . '...</blockquote>'; 
    $out[] = '<a href="' . get_comment_link( $comment->comment_id ) . '">Read more</a>';
    return implode ( $out );
}`

== Screenshots ==

1. Screen capture of Mention comment's Authors in action
2. Screen capture of Mention comment's Authors on the admin side

== Changelog ==

= 0.9.7 =
* You can now create mentions on admin side
* Update jQuery.sew (lib improvement)

= 0.9.6 =
* Regex improvement (preserve spaces and commas around mentions)
* Send mail notifications only when comment is approved (thanks to @wpformation)

= 0.9.5 =
* Now compatible with nested comments

= 0.9.4 =
* Add filter hook "mca_comment_form" to target the comment form textarea
* Compact javascripts into one package, compatible with jQuery 1.9 (embed browser detection)
* Solve bugs...

= 0.9.2 =
* Improve filtering recipients system
* Solve minor bugs

= 0.9.1 =
* Prevent fatal error of mcaAuthors undefined
* On non-ajax mod, return only approved prevent commentators
* add filter hooks for mail composition and mail sending

= 0.9 =
* Initial release
