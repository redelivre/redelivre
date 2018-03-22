=== AddToAny Share Buttons ===
Contributors: micropat, addtoany
Tags: AddToAny, share, sharing, social, share buttons, share button, social media, media, marketing, links, email, seo, woocommerce, google, linkedin, reddit, facebook, like, twitter, pinterest, whatsapp, instagram, youtube, share this, sharethis, feed, icons
Requires at least: 3.7
Tested up to: 4.8
Stable tag: 1.7.12

Share buttons for WordPress including the AddToAny sharing button, Facebook, Twitter, Google+, Pinterest, WhatsApp, many more, and follow icons too.

== Description ==

The AddToAny WordPress sharing plugin helps people share your posts and pages to any service, such as Facebook, Twitter, Pinterest, Google, WhatsApp, LinkedIn, Tumblr, Reddit, and over 100 more sharing and social media sites & apps.

AddToAny is the universal sharing platform, and AddToAny's plugin is the most popular share plugin for WordPress, making sites social media ready since 2006.

= Share Buttons & Follow Buttons =

* **Counters** — fast & official share counts in the same style
* **Floating** share buttons — responsive & customizable, vertical & horizontal
* **Vector** share & follow buttons (SVG icons)
* **Universal** Share Button and Smart Menu
* Individual share links and custom share icons
* Choose from over 100 services
* 3rd party buttons include the Facebook Like Button, Twitter Tweet Button, Pinterest Pin It Button, Google+ Share Button and Google +1 Button
* Universal email sharing makes it easy to share via Gmail, Yahoo! Mail, Outlook.com (Hotmail), AOL Mail, and any other web and native apps

<a href="https://www.addtoany.com/">Share Buttons</a> demo

= Custom Placement =
* Before content, after content, or before & after content
* Vertical Floating Share Bar, and Horizontal Floating Share Bar
* As a shortcode, or a widget within a theme's layout
* Programmatically with template tags

= Analytics Integration =

* Automatic Google Analytics integration (<a href="https://www.addtoany.com/ext/google_analytics/">access guide</a>) for sharing analytics
* Track shared links with Bitly, Google URL Shortener, and custom URL shorteners
* Display share counts on posts and pages

= WordPress Optimized =

* Loads asynchronously so your content always loads before or in parallel with AddToAny
* Supports theme features such as HTML5, widgets, infinite scroll, post formats
* Supports multilingual sites and multisite networks
* No signup, no login, no account necessary

= Mobile Optimized & Retina Ready =

* AddToAny gives users the choice in sharing from a service's native app or from a web app. For example, choose between Twitter's native app or Twitter's mobile web app
* Responsive Floating Share Buttons are mobile ready by default, and configurable breakpoints make floating buttons work with any theme
* AddToAny's SVG icons are super-lightweight and pixel-perfect at any size, and AddToAny's responsive share menu fits on all displays
* Automatic <a href="https://wordpress.org/plugins/amp/">AMP</a> (Accelerated Mobile Pages) support for social share buttons on AMP pages

= Customizable & Extensible =

* Choose exactly where you want AddToAny to appear
* Easily <a href="https://www.addtoany.com/buttons/customize/wordpress">customize sharing</a> on your WordPress site
* <a href="/plugins/add-to-any/faq/">Highly extensible</a> for developers and designers
* Custom icons let you use any icons at any location (media uploads directory, CDN, etc.)
* Many more publisher and user features

= Wide Support =

* Over 10 years of development
* Over 6 million downloads
* Translated into dozens of languages
* Ongoing support from the community

This plugin always strives to be the best WordPress plugin for sharing. Development is fueled by your praise and feedback.

<a href="https://www.addtoany.com/share#url=https%3A%2F%2Fwordpress.org%2Fplugins%2Fadd-to-any%2F&title=AddToAny%20Sharing%20Plugin%20for%20WordPress" title="Share">Share</a> this plugin

See also:

* The <a href="https://www.addtoany.com/buttons/">share buttons</a> for all platforms
* The <a href="https://www.addtoany.com/buttons/for/wordpress_com">share button for WordPress.com</a>

<a href="https://www.addtoany.com/blog/">AddToAny Blog</a> | <a href="https://www.addtoany.com/privacy">Privacy Policy</a>

== Installation ==

In WordPress:

1. Go to `Plugins` > `Add New` > search for `addtoany`
1. Press `Install Now` for the AddToAny plugin
1. Press `Activate Plugin`

Manual installation:

1. Upload the `add-to-any` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the `Plugins` menu in WordPress

WP-CLI installation:

1. `wp plugin install add-to-any --activate`

== Frequently Asked Questions ==

= Where are the options, and how can I customize the sharing plugin? =

In WordPress, go to `Settings` > `AddToAny`.

Setup Follow buttons (like Instagram, YouTube, etc.) using the AddToAny Follow widget in `Appearance` > `Widgets` (or `Appearance` > `Customize`).

To further customize AddToAny, see the <a href="https://www.addtoany.com/buttons/customize/wordpress">WordPress sharing documentation</a> for the AddToAny plugin. Many customizations will have you copy & paste one or more lines of code into your "Additional JavaScript" or "Additional CSS" box. Those boxes are in `Settings` > `AddToAny`.

= Something is wrong. What should I try first? =

Try temporarily switching themes and deactivating other plugins to identify a potential conflict. If you find a conflict, try contacting that theme or plugin author. If an issue persists on a default theme with all other plugins deactivated, search the WordPress plugin's <a href="https://wordpress.org/support/plugin/add-to-any/">support forum</a>.

Feel free to <a href="https://wordpress.org/support/plugin/add-to-any">post here</a>, where the community can hopefully help you. Describe the issue, what troubleshooting you have already done, provide a link to your site, and any other potentially relevant information.

= The share buttons are not displaying for me. Why, and what should I try? =

Something on your own device/browser/connection is likely filtering out major social buttons.

Try another web browser, device, and/or Internet connection to see if the buttons appear. Tools like browserling.com or browserstack.com will give you an idea of what other people are seeing. The usual cause for this uncommon issue is 3rd party browser add-on software that blocks ads and optionally filters out major social buttons. Some security apps and Internet connections have an option to filter social buttons. Usually a social filter option is disabled by default, but if you find that some software is inappropriately filtering AddToAny buttons, <a href="https://www.addtoany.com/contact/">let AddToAny know</a>.

= What is the shortcode for sharing? =

You can place your share buttons exactly where you want them by inserting the following shortcode:

`[addtoany]`

Customize the shared URL like so:

`[addtoany url="https://www.example.com/page.html" title="Some Example Page"]`

Display specific share buttons by specifying comma-separated <a href="https://www.addtoany.com/services/">service codes</a>:

`[addtoany buttons="facebook,twitter,google_plus"]`

Share a specific image or video to certain services that accept arbitrary media (Pinterest, Yummly):

`[addtoany buttons="pinterest,yummly" media="https://www.example.com/media/picture.jpg"]`

= For Facebook sharing, how can I set the thumbnail image and description Facebook uses? =

Facebook expects the Title, Description, and Thumbnail of a shared page to be defined in the Open Graph <a href="https://www.addtoany.com/ext/meta-tags/" target="_blank">meta tags</a> of a shared page.

Use Facebook's <a href="https://developers.facebook.com/tools/debug/sharing/" target="_blank">Sharing Debugger</a> on your pages to see how Facebook reads your site. "Scrape Again" to test site changes and clear Facebook's cache of a page, or use the <a href="https://developers.facebook.com/tools/debug/sharing/batch/" target="_blank">Batch Invalidator</a> to purge Facebook's cache of multiple URLs.

To change the title, description and/or image on Facebook, your theme's header file should be modified according to <a href="https://developers.facebook.com/docs/sharing/opengraph" target="_blank">Facebook's OpenGraph specification</a>. With WordPress, this can be accomplished with plugins such as <a href="https://wordpress.org/plugins/wordpress-seo/">Yoast SEO</a> or the Social Meta feature of the <a href="https://wordpress.org/plugins/all-in-one-seo-pack/">All in One SEO Pack</a>. Please see those plugins for details, and post in the WordPress or plugin author's forums for more support.

For more technical information on setting your pages up for Facebook sharing, see "Sharing Best Practices for Websites" in <a href="https://developers.facebook.com/docs/sharing/best-practices">Facebook's documentation</a>.

= Why do share links route through AddToAny? =

Since 2006, AddToAny is trusted across the web to always route to each service's current endpoint. This routing enables publisher customization, visitor personalization, and keeps the AddToAny plugin remarkably lightweight without the need for constant plugin updates. In AddToAny menus, visitors see the services they actually use. On mobile, AddToAny presents the choice of sharing to a service's native app or mobile site and the preference is used on the next share. Publishers take advantage of AddToAny services such as <a href="https://www.addtoany.com/buttons/customize/wordpress/email_template">email templates</a>, <a href="https://www.addtoany.com/buttons/customize/wordpress/twitter_message">Twitter templates</a>, <a href="https://www.addtoany.com/buttons/customize/wordpress/link_tracking">URL shortener & parameters</a>, and more. Just as service icons change, service endpoints change too, and AddToAny is updated daily to reflect service endpoint and API changes.

= How can I use custom icons? =

Upload sharing icons in a single directory to a public location, and make sure the icon filenames match the icon filenames packaged in the AddToAny plugin. In WordPress, go to `Settings` > `AddToAny` > `Advanced Options` > check the "Use custom icons" checkbox and specify the URL to your custom icons directory (including the trailing `/`). For AddToAny's universal button, go to Universal Button, select `Image URL` and specify the exact location of your AddToAny universal share icon (including the filename).

= How can I place the share buttons in a specific area of my site? =

In the Theme Editor (or another code editor), place this code block where you want the button and individual icons to appear in your theme:

`<?php if ( function_exists( 'ADDTOANY_SHARE_SAVE_KIT' ) ) { ADDTOANY_SHARE_SAVE_KIT(); } ?>`

You can specify [AddToAny service code(s)](https://www.addtoany.com/services/) to show specific share buttons, for example:

`<?php if ( function_exists( 'ADDTOANY_SHARE_SAVE_KIT' ) ) { 
	ADDTOANY_SHARE_SAVE_KIT( array( 
		'buttons' => array( 'facebook', 'twitter', 'google_plus', 'whatsapp' ),
	) );
} ?>`

To customize the shared URL and title:

`<?php if ( function_exists( 'ADDTOANY_SHARE_SAVE_KIT' ) ) { 
	ADDTOANY_SHARE_SAVE_KIT( array( 
		'linkname' => 'Example Page',
		'linkurl'  => 'https://example.com/page.html',
	) );
} ?>`

To share the current URL and title (detected on the client-side):

`<?php if ( function_exists( 'ADDTOANY_SHARE_SAVE_KIT' ) ) { 
	ADDTOANY_SHARE_SAVE_KIT( array( 'use_current_page' => true ) );
} ?>`

To hardcode the shared current URL and modify the title (server-side):

`<?php if ( function_exists( 'ADDTOANY_SHARE_SAVE_KIT' ) ) { 
	ADDTOANY_SHARE_SAVE_KIT( array( 
		'linkname' => is_home() ? get_bloginfo( 'description' ) : wp_title( '', false ),
		'linkurl'  => esc_url_raw( ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ),
	) );
} ?>`

To share a specific image or video to certain services that accept arbitrary media (Pinterest, Yummly):

`<?php if ( function_exists( 'ADDTOANY_SHARE_SAVE_KIT' ) ) { 
	ADDTOANY_SHARE_SAVE_KIT( array( 
		'buttons'   => array( 'pinterest', 'yummly' ),
		'linkmedia' => 'https://www.example.com/media/picture.jpg',
		'linkname'  => 'Example Page',
		'linkurl'   => 'https://www.example.com/page.html',
	) );
} ?>`

= How can I place the follow buttons in a specific area of my site? =

See the [supported follow services](https://www.addtoany.com/buttons/customize/follow_buttons) for service code names, then place this example code in your theme's file(s) where you want the follow buttons to appear:

`<?php if ( function_exists( 'ADDTOANY_FOLLOW_KIT' ) ) {
	ADDTOANY_FOLLOW_KIT( array(
		'buttons' => array(
			'facebook'  => array( 'id' => 'zuck' ),
			'instagram' => array( 'id' => 'kevin' ),
			'twitter'   => array( 'id' => 'jack' ),
		),
	) );
} ?>`

= How can I add a custom standalone share button? =
You can create a plugin or customize the following example PHP code to add to your theme's functions.php file:

`function addtoany_add_share_services( $services ) {
	$services['example_share_service'] = array(
		'name'        => 'Example Share Service',
		'icon_url'    => 'https://www.example.com/my-icon.svg',
		'icon_width'  => 32,
		'icon_height' => 32,
		'href'        => 'https://www.example.com/share?url=A2A_LINKURL&title=A2A_LINKNAME',
	);
	return $services;
}
add_filter( 'A2A_SHARE_SAVE_services', 'addtoany_add_share_services', 10, 1 );`

= How can I add a custom follow button? =
You can create a plugin or customize the following example PHP code to add to your theme's functions.php file:

`function addtoany_add_follow_services( $services ) {
	$services['example_follow_service'] = array(
		'name'        => 'Example Follow Service',
		'icon_url'    => 'https://www.example.com/my-icon.svg',
		'icon_width'  => 32,
		'icon_height' => 32,
		'href'        => 'https://www.example.com/ID',
	);
	return $services;
}
add_filter( 'A2A_FOLLOW_services', 'addtoany_add_follow_services', 10, 1 );`

= How can I align the standard sharing buttons to the center or to the right side of posts? =
It depends on your theme, but you can try adding the following CSS code to your Additional CSS box in Settings > AddToAny.

To align right:

`.addtoany_share_save_container { text-align:right; }`

To align center:

`.addtoany_share_save_container { text-align:center; }`

= How can I remove the button(s) from individual posts and pages? =

When editing a post or page, uncheck "Show sharing buttons", which is located at the bottom of the editor page. Be sure to update or publish to save your changes.

An older method was to insert the following tag into the page or post (HTML tab) that you do not want the button(s) to appear in: `<!--nosharesave-->`

= How can I force the button(s) to appear in individual posts and pages? =

When editing a post or page, check the "Show sharing buttons" checkbox, which is located at the bottom of the editor page. Be sure to update or publish to save your changes. Note that, by default, AddToAny is setup to display on all posts and pages.

An older method was to insert the following tag into the page or post (HTML tab) that you want the button(s) to appear in: `<!--sharesave-->`

= How can I remove the button(s) from category pages, or tag/author/date/search pages? =

Go to `Settings` > `AddToAny` > uncheck `Display at the top or bottom of posts on archive pages`. Archive pages include Category, Tag, Author, Date, and also Search pages.

= How can I programmatically remove the button(s)? =

In your theme's `functions.php`, you can add a filter to disable AddToAny sharing.

Disable AddToAny sharing in specific categories, for example:

`function addtoany_disable_sharing_in_some_categories() {
	// Examples of in_category usage: https://codex.wordpress.org/Function_Reference/in_category
	if ( in_category( array( 'my_category_1_slug', 'my_category_2_slug' ) ) ) {
		return true;
	}
}
add_filter( 'addtoany_sharing_disabled', 'addtoany_disable_sharing_in_some_categories' );`

Disable AddToAny sharing on a custom post type, for example:

`function addtoany_disable_sharing_on_my_custom_post_type() {
	if ( 'my_custom_post_type' == get_post_type() ) {
		return true;
	}
}
add_filter( 'addtoany_sharing_disabled', 'addtoany_disable_sharing_on_my_custom_post_type' );`

= How can I position a vertical floating share buttons bar relative to content? =

In settings, disable the default placement of the Vertical Buttons. In your theme's file(s), find the parent element that you want to position the vertical bar to (the parent element should have a specified width), then add the following example PHP code as a child of that parent element:

`<?php if ( function_exists( 'ADDTOANY_SHARE_SAVE_FLOATING' ) ) {
	ADDTOANY_SHARE_SAVE_FLOATING( array(
		'kit_style' => 'margin-left:-100px; top:150px;',
		'vertical_type' => true,
	) );
} ?>`

= Why does the Facebook Like Button, Pinterest Pin It Button, Google+ Share Button, or Google +1 Button have so much whitespace to the right of it? =

The minimum width for the Facebook Like Button is 90 pixels. This is required to display the total number of Likes to the right of the button.  See Facebook's <a href="https://developers.facebook.com/docs/plugins/like-button">Like Button documentation</a> for details

It's not recommended, but you can change the width of the Facebook Like Button using CSS code, for instance: `.a2a_button_facebook_like { width:50px !important; }`

The Pinterest Pin It Button with 'show count' enabled is 76 pixels. You can change the width using CSS code, for instance: `.a2a_button_pinterest_pin { width:90px !important; }`

The Google +1 Button with 'show count' enabled is 90 pixels. You can change the width using CSS code, for instance: `.a2a_button_google_plusone { width:65px !important; }`

The Google+ Share Button width can be changed using CSS code, for instance: `.a2a_button_google_plus_share { width:57px !important; }`

= Does the plugin output W3C valid code? =

Yes, this plugin outputs 100% W3C valid HTML5 and W3C valid CSS 3 by default.

= How can I load the buttons after content insertion with Ajax and infinite scroll? =

AddToAny supports the <a href="https://codex.wordpress.org/AJAX_in_Plugins#The_post-load_JavaScript_Event">standard `post-load` event</a>.

Ajax and infinite scroll plugins/themes should always fire the `post-load` event after content insertion, so request <a href="https://codex.wordpress.org/AJAX_in_Plugins#The_post-load_JavaScript_Event">standard `post-load` support</a> from plugin & theme authors as needed.

Use the following line to dispatch the `post-load` event for AddToAny and other plugins:

`jQuery( 'body' ).trigger( 'post-load' );`

= How can I set the plugin as a "Must-Use" plugin that is autoloaded and activated for all sites? =

Upload (or move) the `add-to-any` plugin directory into the `/wp-content/mu-plugins/` directory. Then create a proxy PHP loader file (such as `load.php`) in your `mu-plugins` directory, for example:

`<?php require WPMU_PLUGIN_DIR . '/add-to-any/add-to-any.php';`

== Screenshots ==

1. AddToAny vector share buttons (SVG icons) are pixel-perfect and customizable
2. Mini share menu that drops down when visitors use the universal share button
3. Full universal share menu modal that includes all services
4. Settings for Standard Share Buttons
5. Settings for Floating Share Bars

== Changelog ==

= 1.7.12 =
* Fix the `[addtoany]` shortcode's `buttons` attribute
* Use `rel="noopener"` on links that open in a new tab/window for site JavaScript performance in some browsers

= 1.7.11 =
* Add icon size option to Follow buttons widget
* Replace "Large" and "Small" icon size options with single field
* Replace old universal buttons with custom button URLs
* Update CSS to apply style to custom icon sizes
* Always use HTTPS script and endpoints

= 1.7.10 =
* Fix vertical floating bar's class name and media query from the previous release

= 1.7.9 =
* Show optional meta box ("Show sharing buttons") below the WordPress editor by default, without having to save AddToAny settings first
* Enable the `shortcode_atts_addtoany` hook to filter the default attributes of the `[addtoany]` shortcode
* Accept `kit_additional_classes` argument in Floating and Follow button output functions (thanks Rocco Marco)

= 1.7.8 =
* AddToAny Follow widgets will now use full URLs when specified for a service ID
* Add Papaly
* Add Refind
* Update Pinterest icon

= 1.7.7 =
* Resolve syntax issue with [out-of-date PHP versions](https://secure.php.net/eol.php) below PHP 5.3

= 1.7.6 =
* Skip background colors on AMP icons for [out-of-date PHP versions](https://secure.php.net/eol.php) below PHP 5.3

= 1.7.5 =
* Add background colors to share buttons on [AMP](https://wordpress.org/plugins/amp/) (Accelerated Mobile Pages) to support new AddToAny SVG icons
* Fix AMP issue from `in_the_loop` check in 1.7.3
* Remove `in_the_loop` context check because AMP doesn't use the loop
* Use packaged AddToAny icon in admin (thanks xaviernieto)

= 1.7.4 =
* Fix custom/secondary contexts check when the WP query object is unavailable

= 1.7.3 =
* Add icon size options for the floating share buttons
* Replace packaged PNG icons with SVG icons
* Update services in AddToAny settings
* Update standard placement to prevent the share buttons from automatically appearing in custom/secondary contexts
* Set feed URL & title in AddToAny Follow widgets using HTML5 data attributes

= 1.7.2 =
* Accept arguments in universal button template tag for additional classes and HTML contents
* Override box shadow on buttons in some themes such as the new Twenty Seventeen default theme in WordPress 4.7

= 1.7.1 =
* Fix floating share buttons fallback so that the current page is shared by default
* Show meta box ("Show sharing buttons") below the WordPress editor when a floating share bar is enabled
* Remove deprecated option that displayed the title in the mini menu
* Add Douban share button
* Add Draugiem share button

= 1.7 =
* Simplify AddToAny asynchronous loading method
* Use HTML5 data attributes for AddToAny instances
* Remove old script block in footer
* Increase support for AJAX loading by listening for the `post-load` event on non-AJAX requests
* AddToAny readiness check in AddToAny settings
* Add placement option for media pages in AddToAny settings
* Handle "Show sharing buttons" option for media when updating from the WordPress editor
* Add Copy Link button
* Add Facebook Messenger share button
* Add Trello share button
* Update a few packaged icons

= 1.6.18 =
* Fix default icon size

= 1.6.17 =
* New `media` attribute for sharing a specific image or video to the few services that accept arbitrary media (Pinterest, Yummly)
* Update `[addtoany]` shortcode to accept specific `media` URL
 * Specify a direct media URL in the `media` attribute like `[addtoany buttons="pinterest,yummly" media="https://www.example.com/media/picture.jpg"]` to have Pinterest pin that image

= 1.6.16 =
* Fix customizer preview check for WordPress versions older than 4.0 (thanks Jessica)

= 1.6.15 =
* AddToAny widgets support new selective refresh in the WordPress 4.5 Customizer
* AddToAny share endpoints default to HTTPS on HTTPS sites
* Permit changing the target attribute for custom services (thanks Jasper)
* The meta box ("Show sharing buttons") below the WordPress editor will no longer show when standard placement is disabled for the current post type
* Add Kik share button
* Add Skype share button
* Add Viber share button
* Add WeChat share button
* Add Snapchat follow button

= 1.6.14 =
* [AMP](https://wordpress.org/plugins/amp/) (Accelerated Mobile Pages) support for share buttons in posts
* PHP template code now accepts the `icon_size` argument

= 1.6.13 =
* Fix automatic placement in post excerpts for certain themes & plugins that display post content on a page (thanks Agis)

= 1.6.12 =
* Enable counters on floating share buttons when enabled for standard share buttons
* Fix settings link for Multisite administrators (thanks Jan)
* Simplify internal methods that automatically place standard share buttons
* Automatic placement logic for [WordPress excerpts](https://codex.wordpress.org/Excerpt) has changed ("excerpt" usage & presentation varies widely among WordPress themes & plugins)
* If needed, you can uncheck the "Display at the bottom of excerpts" placement option in AddToAny settings to disable button placement in a post's excerpt/snippet/intro
* Add width & height fields for custom icons in the Advanced Options section
* Update admin slug

= 1.6.11 =
* Update `[addtoany]` shortcode to accept specific share buttons
 * Specify [AddToAny service codes](https://www.addtoany.com/services/) in the `buttons` attribute like: `[addtoany buttons="facebook,twitter,google_plus"]`
* Add SMS share button
* Add Telegram share button
* Add Google Classroom share button
* Add GitHub follow button
* Update Instagram URL

= 1.6.10 =
* Enable simpler syntax when using multiple share message templates
* Override box shadow on buttons caused by CSS such as the default Twenty Sixteen theme's
* Replace deprecated WP function used for the local cache option

= 1.6.9 =
* Fix decoding of HTML entities in shared titles when the default character encoding is not UTF-8
* Update packaged languages
* Use Romanian language pack (thanks Adrian Pop)
* Use Swedish language pack (thanks Piani)

= 1.6.8 =
* Universal share counter is now available
* Tweet counters have been removed because <a href="https://www.addtoany.com/blog/twitter-share-count/">Twitter no longer provides counts</a>
* Official buttons (Facebook Like, etc.) have been moved to the bottom of the available services list
* Support WP-CLI v0.21.0+ (thanks Compute and Daniel Bachhuber)
* Support bootstrapped WordPress
* Support for other script loading methods

= 1.6.7 =
* New Additional CSS box for AddToAny CSS customizations
* Rename Additional Options box to Additional JavaScript box
* Fix quotes and other special characters in shared titles
* Simplify sections in settings
* Update universal button to canonical endpoint
* Use SSL for local cache updates
* Support must-use plugin usage via a proxy PHP loader so `add-to-any.php` can remain in the `add-to-any` directory
* Remove support for old method of moving `add-to-any.php` into `mu-plugins` for auto-loading

= 1.6.6 =
* Harden local caching option (thanks pineappleclock)
* Remove old warning message when template tags seem to be missing (thanks Tenebral, and theme authors everywhere)
* Adjust gettext calls by switching to single quotes for the text domain argument

= 1.6.5 =
* Update Google icon
* Update Google+ icon
* Update Tumblr logo
* Remove NewsTrust

= 1.6.4 =
* Fix placement option for custom post types to not inherit the placement option for regular posts (thanks Air)
* Permit custom AddToAny button in floating share bars (thanks billsmithem)
* Update widget docblocks so they are not mistaken for PHP 4 constructors

= 1.6.3 =
* Fix Google+ follow button URL by removing the hardcoded `+` (thanks foxtucker)
 * Be sure to add the `+` to your `ID` if you have a Google+ custom URL.
* Custom follow services can be added to the Follow widget using the `A2A_FOLLOW_services` filter hook (see the FAQ)
* Harden CSS vertical alignment of custom icon images and Tweet button
* Change admin heading to `<h1>` for improved accessibility

= 1.6.2 =
* Support AJAX loading from `admin-ajax.php`
* Update CSS to fix alignment issues in some themes with button images and the Facebook Like button
* Add small follow icons (Instagram, YouTube, Vimeo, Flickr, Foursquare, Behance, and RSS PNGs)
* Add Known
* Remove obsoleted detection of page.js versus feed.js

= 1.6.1 =
* Titles with special characters are sanitized differently
 * Using `wp_json_encode` (or `json_encode`) instead of `esc_js` for sanitizing JavaScript strings (thanks Nigel Fish)
* Fix issue where the new feed button pointed to an example URL instead of the saved URL (thanks debij)
* Resolve Follow widget notice when widget is empty in debug mode

= 1.6.0.1 =
* Resolve notices in debug mode

= 1.6 =
* <a href="https://www.addtoany.com/buttons/customize/wordpress/follow_buttons">Follow buttons</a> have arrived for WordPress!
* Includes Instagram, YouTube, Vimeo, Flickr, Foursquare, Behance, and RSS
* Go to `Appearance` > `Customize` or `Appearance` > `Widgets` to setup your follow buttons

= 1.5.9 =
* Accept custom icons of all file types such as png, svg, gif, jpg, webp, etc.
* Remove empty width and height attributes on custom icons for W3C validation
* AddToAny is certified as multilingual-ready by <a href="https://wpml.org/">WPML</a>

= 1.5.8 =
* Toggle share buttons on custom post types in AddToAny settings
 * Supports WooCommerce Product post types
 * Supports bbPress Forum, Topic, Reply post types
* Remove QQ (use Qzone)
* Remove border from buttons that some themes add to links in posts

= 1.5.7 =
* <a href="https://www.addtoany.com/buttons/customize/wordpress/icon_color">Custom color share buttons</a> have arrived!
* Remove Bookmark/Favorites (low browser support)
* Remove duplicate Print service (old and redundant)
* Remove FriendFeed
* Remove Springpad

= 1.5.6 =
* Bulgarian translation (by Artem Delik)
* Update French translation to resolve missing placement options

= 1.5.5 =
* Support multilingual sites using WPML, including Polylang

= 1.5.4 =
* New optional sharing header to easily place a label above the default share buttons
 * Accepts HTML
* Fix text width of counters where long numbers would break to a newline (thanks Chris)
* Remove old Additional Options note in settings

= 1.5.3 =
* <a href="https://www.addtoany.com/buttons/customize/wordpress/events" title="Track shares, or change the shared URL">Share event handling</a> comes to the WordPress plugin!
* This means you can track and modify the share with some JavaScripting
 * Track shares with Adobe Analytics, Clicky, KISSmetrics, Mixpanel, Piwik, Webtrends, Woopra, custom analytics, etc.
 * Google Analytics integration is automatic as always
 * Modify the shared URL
 * Modify the shared Title for services that accept a Title directly (i.e. Twitter, but not Facebook)
* Update asynchronous loading to use new callbacks property
* Add Renren
* Remove blip, iwiw

= 1.5.2 =
* Localize More button string
* Improve interoperability among feed readers by removing official service buttons from feeds

= 1.5.1 =
* Update Print icon
* Update YouMob icon
* Update Symbaloo icon
* Update Qzone (QQ) icon
* Remove allvoices, arto, bebo, funp, jumptags, khabbr, linkagogo, linkatopia, nowpublic, orkut, phonefavs, startaid, technotizie, wists, xerpi

= 1.5 =
* Custom icon sizes (applied to AddToAny vector icons)
* Fix custom standalone service icons when used with large icons
* Add Kakao
* Add Qzone
* Add Yummly
* Update Wykop icon

= 1.4.1 =
* Update CSS to fix Facebook Like button verical aligment caused by Facebook's inline styling change
* Fix issue with shared titles containing unwanted HTML tags caused by some themes

= 1.4 =
* New: Share counters in the same style for Facebook, Twitter, Pinterest, Linkedin and Reddit!
 * In Settings > AddToAny > Standalone Buttons, click the down arrow and toggle the "Show count" checkbox for any supported service
* Floating buttons do not display share counts in this release
* Update CSS to support AddToAny share counters

= 1.3.8.1 =
* Add LINE icon (thanks tokyodev)
* Add Mixi icon
* Add Hacker News icon
* Update Twitter icon

= 1.3.8 =
* Floating share buttons are now disabled when "Show sharing buttons" is unchecked for a post/page
* French translation (by Jacques Soulé)

= 1.3.7 =
* New <a href="https://www.addtoany.com/blog/new-universal-sharing-menu-design/">share menu</a> design for the universal share button!
* Floating share buttons code can be placed manually in theme files
 * Ideal for positioning the vertical share bar relative to site content
 * See the FAQ "How can I position a vertical floating share buttons bar relative to content?"
* Fix JS console "undefined" notices during site previews (thanks Georgi Yankov)
* Update universal sharing fallback to use HTTPS/SSL

= 1.3.6 =
* Add LINE
* Add Mixi
* Add Hacker News
* Update Twitter icon

= 1.3.5 =
* Fix 'small icon + text' double-icon issue where the AddToAny Kit icon would appear in addition to packaged icon
* Adjust default placement options for better control over the excerpts option

= 1.3.4 =
* Fix large text-only icon where the AddToAny universal icon was appearing
* Remove !important style declaration for small universal icon (thanks Tom Bryan)
* Remove Mister Wong
* Remove Yigg 

= 1.3.3 =
* Append trailing slash to custom icons location as necessary
* Point to additional placement options and information
* Remove old placement instructions for hardcoding
* Remove old error reporting suppression in admin
* Ukrainian translation (by Michael Yunat)

= 1.3.2 =
* Avoid loading button script in admin, especially for visual editor plugins
* Add addtoany_script_disabled hook to programmatically disable JavaScript

= 1.3.1 =
* Floating share buttons are now responsive by default
* Responsive breakpoints configurable in Floating options
* "Use default CSS" must be enabled in Advanced Options for responsiveness

= 1.3.0.1 =
* Apply universal sharing button toggle to floating buttons

= 1.3 =
* Floating share buttons (major new feature!)
* AddToAny Vertical Share Bar
* AddToAny Horizontal Share Bar
* Update admin interface
* Update options

= 1.2.9.3 =
* Prevent script output in feeds on some sites
* Remove constants for old WP versions (below 2.6)

= 1.2.9.2 =
* Fix transparency on AddToAny's universal share icon
* Add addtoany_sharing_disabled hook to programmatically disable sharing
* Update Menu Options

= 1.2.9.1 =
* Update template code
* Update Advanced Options description
* Fix button return notice for certain use cases

= 1.2.9 =
* New Custom Icons option in the Advanced Options section
 * Use any icons at any location (media uploads directory, CDN, etc.)
 * This new method for using custom icons permits plugin updates that do not overwrite custom icons
* Improve descriptions in settings

= 1.2.8.7 =
* Fix code styling

= 1.2.8.6 =
* Fix advanced caching option (most sites should not use this option)

= 1.2.8.5 =
* For CSS customization, delineate between top and bottom in default sharing placement
* Add .addtoany_content_top and .addtoany_content_top class names to .addtoany_share_save_container

= 1.2.8.4 =
* Add Wanelo
* Add WhatsApp

= 1.2.8.3 =
* Set border to none on images for small icons and custom icons

= 1.2.8.2 =
* Add pointer to settings in admin

= 1.2.8.1 =
* Remove Favoriten
* Remove Grono
* Remove Hyves

= 1.2.8.0 =
* Update Customize documentation links

= 1.2.7.9 =
* Fix Print icon

= 1.2.7.8 =
* Update Google+ Share button
* Update Google +1 button
* Update Delicious small icon
* Update Diaspora small icon

= 1.2.7.7 =
* Perfect compatibility with WordPress 3.8
* Update email icon
* Add confirm dialog to universal sharing toggle
* Rename files and backend improvements for admin

= 1.2.7.6 =
* Add option to disable universal sharing button
 * Disables the universal sharing button in automatic placement and ADDTOANY_SHARE_SAVE_KIT
 * The universal sharing button is still available via ADDTOANY_SHARE_SAVE_BUTTON (see FAQ)
* Fix fieldset focusing in admin

= 1.2.7.5 =
* Update Facebook Like button

= 1.2.7.4 =
* Improve vertical alignment consistency of 3rd party buttons across themes

= 1.2.7.3 =
* Harden AJAX detection

= 1.2.7.2 =
* Support infinite scrolling of post content in themes
* Support AJAX loading (on a `post-load` event)

= 1.2.7.1 =
* Wrap addtoany shortcode with .addtoany_shortcode to enable specific styling
* Add Flipboard
* Update FAQ on hardcoding just the icons (both large and small icons)

= 1.2.7 =
* Fix backwards compatibility with WordPress 2.8
 * Checkbox option to disable sharing buttons on each post and page
* Add Baidu
* Add Mendeley
* Add Springpad
* Update VK (Vkontakte)

= 1.2.6 =
* Harden option to disable sharing buttons on each post and page
 * Extend option to custom post types

= 1.2.5 =
* Large SVG icons are the default sharing format
* Default standalone services are Facebook, Twitter, Google Plus
* Note: If your sharing button suddenly changes (because you haven't saved AddToAny settings yet), you can go back to the traditional sharing button in Settings > AddToAny. Choose "Small" for the Icon Size to see the traditional buttons, and in Standalone Buttons, click "Add/Remove Services" to deselect Facebook, Twitter, and Google Plus. Then click Save Changes to save your AddToAny Share Settings.
* Popular services have been moved to the top of the Standalone Buttons list

= 1.2.4 =
* Add Pinterest Pin It Button and options

= 1.2.3 =
* Adjust CSS to improve icon and text alignment
* Add FAQ for removing sharing button(s) from Archive pages (including Category, Tag, Author, Date, and Search pages)

= 1.2.2 =
* Fix code formatting (indents, newlines)

= 1.2.1 =
* Checkbox option to disable sharing buttons on each post and page

= 1.2 =
* Large sharing buttons (major new feature!)
 * Scalable vector icons (SVG icons)
 * Mobile ready
 * Retina and high-PPI ready
 * Customizable through CSS code (effects, height & width, border-radius, and much more)
 * Support for older browsers (reverts gracefully to PNG)
 * Large icons are available for the most popular services right now. More services will roll out soon
* Adjust CSS for large icons
* Remove old Internet Explorer stylesheet for rollover opacity effect
* Add Icon Size options to settings
* Defaults and settings adjustments
* Simplify labels in settings

= 1.1.6 =
* Add App.net
* Add Outlook.com (formerly Hotmail)
* Update Pinterest icon
* Update Box icon
* Update Digg icon
* Update Evernote icon
* Update Instapaper icon
* Update Yahoo! icon
* Update Vkontakte (VK) icon
* Remove unused icons

= 1.1.5 =
* Fix debug mode notices when manually placed in a theme file that does not display a post/page
* Sanitize active services output in admin

= 1.1.4 =
* Fix admin panel display of 3rd party button options after saving changes
* Fix debug mode notices in admin

= 1.1.3 =
* Fix validation and semantics of script tag placement, particularly when button is contained in a list element

= 1.1.2 =
* Fix settings page conflict with certain features of the Jetpack plugin and other plugins & themes
 * Fix saving of 3rd party button options (for Facebook Like, Twitter Tweet, Google +1)
 * Fix service sorting
* Add Pocket (formerly Read It Later)
* Remove Read It Later (now Pocket)
* Remove HelloTxt
* Update Slashdot icon

= 1.1.1 =
* Add Pinboard
* Add Buffer
* Add Diaspora
* Add Kindle It
* Add Mail.Ru
* Add Odnoklassniki
* Update Pinterest icon
* Update Google icon
* Update Google+ icon
* Remove Google Reader
* Remove Posterous
* Remove Sphere
* Remove Tipd
* Remove Vodpod

= 1.1 =
* Fix settings page conflict with certain features of the Jetpack plugin and other plugins & themes
* Fix settings page's down arrow icon for advanced 3rd party buttons (Like, Tweet, etc.)
* Update branding
* Update buttons

= 1.0.3 =
* Update Twitter logo

= 1.0.2 =
* Add new Google+ Share button
* Update FAQ for resizing advanced 3rd party buttons

= 1.0.1 =
* Fix markup output for advanced 3rd party buttons when displayed as WordPress widget

= 1.0 =
* After 5 years of development: Version One Point Oh!
* Load advanced 3rd party buttons faster (Facebook Like, Twitter Tweet, Google +1)
 * Use A2A Kit for speed, extensibility, measurability
* Adjust CSS to accommodate external resources for advanced 3rd party buttons
* Lithuanian translation (by Vincent G.)

= 0.9 =
* For all prior versions, see 1.6.12 or earlier

== Upgrade Notice ==

= 1.6.12 =
Automatic placement logic for [WordPress excerpts](https://codex.wordpress.org/Excerpt) has changed. If your theme displays buttons in a post's excerpt/snippet/intro after this plugin update, you can uncheck the "Display at the bottom of excerpts" placement option in AddToAny settings to remove those buttons. Use custom icons? For improved layout and compatibility, specify the width & height of your icons in Settings > AddToAny > Advanced Options.

= 1.6.7 =
If you are using AddToAny as a "must-use" autoloaded plugin (in the `mu-plugins` directory), the old method of moving `add-to-any.php` into `mu-plugins` is no longer supported and will not work. See the plugin's last FAQ about using a proxy PHP loader file that autoloads AddToAny.

= 1.6.3 =
If you configured a Google+ follow button through an AddToAny Follow widget, the automatic `+` in your URL has been removed to permit default Google+ URLs which do not have a `+` preceding the ID number. Be sure to add the `+` back if you have a Google+ custom URL.

= 1.6 =
Follow buttons are now available! Look for the AddToAny Follow widget in Appearance > Customize or Appearance > Widgets.

= 1.5.3 =
Switch to the Large or Custom icon size if you haven't already - AddToAny's vector icons are way better than the traditional Small icons.

= 1.4 =
AddToAny share counters are now available for supported standalone buttons! For Facebook, Twitter, Pinterest, Reddit and Linkedin, click the down arrow and toggle the "Show count" checkbox.

= 1.3.1 =
Floating share buttons are now responsive by default, ready for mobile & tablets. You can toggle responsiveness and set responsive breakpoints in the "Floating" tab of Settings > AddToAny.

= 1.3 =
Floating Share Buttons are now available! Click the "Floating" tab in Settings > AddToAny.