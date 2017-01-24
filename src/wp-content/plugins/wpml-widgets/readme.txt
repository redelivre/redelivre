=== WPML Widgets ===
Contributors: sormano
Tags: WPML, WPML widget, WordPress Multilanguage, WordPress Multilanguage widget, WPML widget selector
Requires at least: 3.6
Tested up to: 4.7
Stable tag: 1.0.6
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

WPML Widgets is a simple to use extension to add a language selector dropdown to your widgets.

== Description ==
WPML Widgets is a simple to use extension to add a language selector dropdown to your widgets.

This plugin is the easiest way to add multilingual widgets to your website.

WPML Widgets is a ultra lightweight plugin, so there will be (about) zero extra loading time.


== Installation ==

1. Upload the folder `wpml-widgets` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Screenshots ==

1. One widget per language
2. Different widgets for every language


== Changelog ==

= 1.0.6 - 04/01/2016 =
* Fix - Issue introduced in 1.0.5 where with some people the 'wpml_get_active_languages()' function doesn't exist.

= 1.0.5 - 02/01/2016 =
* Change - Removed the unneeded prefixes of the class methods.
* Tested - With the current WordPress version (4.7)
* Improvement - Change the deprecated 'wpml_active_languages' function to 'wpml_get_active_languages'

= 1.0.4 - 25/04/2014 =
* Fix - Escape url in admin

= 1.0.3 - 20/12/2014 =
* Fix - Notice on WP_Debug mode when values are not saved
* Add - Nagg message when WPML is not active ;-)
* Improvement - Use Singleton to initiate plugin
* Improvement - Better code/comment quality

= 1.0.2 =
* Fix - WordPress Multisite supported check

= 1.0.1=
* Fix - notices in debug mode
* Fix - Widgets won't break when WPML is not activated

= 1.0.0 =
* Initial release