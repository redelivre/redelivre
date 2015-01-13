=== Unfiltered MU ===
Contributors: mdawaffe, donncha, automattic
Tags: embed, iframe, html, script, object, unfiltered_html, WPMU
Tested up to: 3.0
Stable tag: 1.3.1
Requires at least: 2.9.2

This WordPress MU/WordPress 3.0 multisite plugin gives blog Administrators and Editors the ability to post whatever HTML they want. "Evil" tags will not be stripped.

== Description ==

Unfiltered MU gives Administrators and Editors the `unfiltered_html` capability.  This prevents WordPress MU/WordPress 3.0 multisite from stripping `<iframe>`, `<embed>`, etc. from these users' posts. Authors and Contributors do not get this capability for security reasons.

The plugin can either be used globally for your entire MU site, or it can be applied on a blog-by-blog basis.

For WordPress MU or WordPress 3.0 multisite only. Regular WordPress already offers this feature and does not need this plugin.

Warning! This is a very dangerous plugin to activate if you have untrusted users on your site. Any user could add Javascript code to steal the login cookies of any visitor who runs a blog on the same site. The rogue user can then inpersonate any of those users and wreak havoc. If all you want is to display videos on your WordPress MU blogs, use the native [Embed Support](http://codex.wordpress.org/Embeds), [Viper's Video Quicktags](http://wordpress.org/extend/plugins/vipers-video-quicktags/) or any of the other [video plugins](http://wordpress.org/extend/plugins/tags/video) on WordPress.org.
If you use this plugin your site will be hacked in one way or another if you allow anonymous users on the Internet to create blogs on your site. It's very dangerous.

Are you still 100% sure you want to use this plugin? 

== Installation ==

If you want to enable this feature on *all* blogs on your MU site:

 1. Place the `unfiltered-mu.php` file in your `wp-content/mu-plugins/` directory.  That's it.  Removing the plugin will remove the capability.

If you want to enable this feature on a *blog-by-blog* basis:

 1. Place the `unfiltered-mu.php` file in your `wp-content/plugins/` directory.
 2. Activate this plugin for those blogs on which you want this feature enabled or enable it sitewide with the "network activate" feature. Deactivating the plugin will remove the capablitiy for that blog.
