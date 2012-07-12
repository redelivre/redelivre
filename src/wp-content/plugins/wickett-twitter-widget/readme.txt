=== Wickett Twitter Widget ===
Contributors: automattic, niallkennedy, nickmomrik, beaulebens, tmoorewp
Tags: twitter, widget, tweets, WordPress.com
Stable tag: 1.0.6
Requires at least: 2.8
Tested up to: 3.2.1
License: GPLv2

Future upgrades to Wickett Twitter Widget will only be available in <a href="http://jetpack.me/" target="_blank">Jetpack</a>. Jetpack connects your blog to the WordPress.com cloud, <a href="http://jetpack.me/support/" target="_blank">enabling awesome features</a>.

== Description ==

Display the latest Tweets from your Twitter accounts inside WordPress widgets. Customize Tweet displays using your site or theme CSS.

Customize the number of tweets displayed. Filter out @replies from your displayed tweets. Optionally include retweets. The widget plugin automatically links usernames, lists, and hashtags mentioned in Tweets to keep your readers on top of the full conversation.

== Installation ==

1. Upload wickett-twitter-widget.php to your /wp-content/plugins/ directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Add the widget to your sidebar from Appearance->Widgets and configure the widget options.

== Frequently Asked Questions ==

= Can multiple instances of the widget be used? =

Yes.

= Can private Twitter accounts be used? =

No. The widget does not support authenticated requests for private data.

= I see less than the requested number of Tweets displayed =

Twitter may return less than the requested number of Tweets if the requested account has a high number of @replies in its user timeline.

== Screenshots ==

1. Enter your Twitter username, customize your widget title, set the total number of tweets, hide replies, and customize text display in your widget editor.
2. Latest tweets display.

== Changelog ==

= 1.0.6 =
* Add notices to upgrade to Jetpack for further updates.

= 1.0.5 =
* Support display of retweets via widget configuration option.

= 1.0.4 =
* Remove type hinting for broader compatibility with PHP versions.

= 1.0.3 =
* Compatible with Snowflake, Twitter's new message id system
* New Twitter URI structure accepted as username input
* Improved linking of usernames, lists, and hashtags. Now uses Twitter official regex.
* Filter replies on the server-side. Improves total bytes over wire.

= 1.0.1 =
* Rename time_since function to avoid conflicts with other plugins

= 1.0 =
* Initial version

== Upgrade Notice ==

= 1.0.5 =
Retweet display support.

= 1.0.4 =
Improve compatibility across older versions of PHP.

= 1.0.3 =
New Twitter support. Lists, snowflake IDs. Improved performance and stability.