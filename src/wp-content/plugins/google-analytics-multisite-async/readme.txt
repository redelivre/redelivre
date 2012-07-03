=== Google Analytics Multisite Async ===
Contributors: Dartur
Donate link: http://darturonline.se/ga_mu_async.html
Tags: Google Analytics, analytics, statistics, stats, lightweight, multisite, multiuser, multiblog, tracking, simple, network
Requires at least: 3.1
Tested up to: 3.1.2
Stable tag: 1.1
License: GPLv2

Network admin can collect statistics networkwide with one GA account and let the regular admins collect statistics on their own sites with own accounts.

== Description ==

Google Analytics Multisite Async lets the network admin collect statistics from all sites and it lets the regular site admins collect statistics from their own site. This means that statistics are collected to 2 different Analytics accounts at once, assuming that the site admin have entered an ID of course. It's the asynchronous version of Analytics. The network admin can choose whether the site admins should be able to collect statistics or not.

The plugin is localized and available in:

* English
* Swedish
* German (Thanks Jenny at http://www.professionaltranslation.com)
* Dutch (Thanks Rene at http://wpwebshop.com)
* Persian (Thanks Sushynat at http://www.zavarzadeh.org)

If you translate it to more languages please send me the language files so I can include them here.

More information and a small guide how to setup your Analytics account filters to be prepared for networkwide statistics can be found at the plugin website:

http://www.darturonline.se/ga_mu_async.html

== Installation ==

1. Upload the plugin folder and files to the `/wp-content/plugins/` directory.
2. Verify that your networks main blog ID is 1. If so, jump to step 4.
3. Open the plugin file and change the number next to MAIN_BLOG_ID to your main blog ID.
4. Activate the plugin for network.
5. You (network admin) will find a new option in the network admin page in the Settings menu called "Google Analytics".
6. Site admins will find a new option in the Settings menu called "Google Analytics".

== Screenshots ==

Please see the plugin website for screenshots:

http://www.darturonline.se/ga_mu_async.html

== Changelog ==

= 1.1 =
* Network admins now change settings through the network administration page.
* A new option added so that network admins can choose whether site admins should be able to collect statistics or not.
* Small bug corrected that caused the main blog to send data twice.
* New languages added: German, Dutch and Persian.

= 1.0 =
* First release!

== Upgrade Notice ==

= 1.1 =
If you are using older version than 3.1 DO NOT UPGRADE. This update makes use of the new network admin menu and it was first introduced in version 3.1.

= 1.0 =
* First release!

== Frequently Asked Questions == 

= I've translated your awesome plugin to another language. Could I send you the .po and .mo files? =

Yes, I will include your translation in future releases. E-mail me at `niklas [at] darturonline [dot] se`

= Does this work with subdirectories installation? =
 
This plugin have only been tested with the subdomains installation of WordPress Multisite.

But I can't think of any reason why it would not work with the subdirectories installation so if you are up to it, feel free to try. I suspect that it will be harder setting up filters in Analytics to see data from a specific site with subdirectories installation.

= Does it work with domain mapping? =

Yes, according to a user who have tried this it works with adding only one row of text to the tracking code and setting domain name to "none". More on this on the plugin website.

== Languages ==

This plugin is currently available in the following languages:

* English
* Swedish
* German
* Dutch
* Persian
