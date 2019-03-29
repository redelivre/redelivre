=== qTranslate X Cleanup and WPML Import ===
Contributors: OnTheGoSystems
Donate link: http://wpml.org
Tags: qTranslatex, wpml, multilingual, i18n, conversion, import, uninstall, cleanup
Requires at least: 3.0
Tested up to: 5.0
Stable tag: 1.9.7.2
License: GPLv2 or later

Allows a complete uninstall and cleanup of qTranslate X meta-tags or importing translations into WPML

== Description ==

This plugin can either cleanup the qTranslate X meta-HTML tags from your site and leave just one 'clean' language, or migrate all languages to WPML's format.

**Very important: This plugin will modify the entire content of your database. You must backup your database before attempting to use it.**

For complete documentation, please refer to the [qTranslate uninstall and WPML importer documentation](http://wpml.org/documentation/related-projects/qtranslate-importer/).

= qTranslate X uninstall and cleanup mode =

Notice: this mode is temporary not supported. We are working to reintroduce this feature. 

This mode is intended if you just want to keep one language in your site and you want to clean up the language meta-tags that qTranslate added. For this mode, you don't need WPML.

Instructions:

1. Save all qTranslate X settings
2. Go to the Plugins admin page and de-activate qTranslate X
3. Install & activate QT Importer
4. Go to Options -> QT Importer, select language to keep and click Start. 

= Migrate all languages from qTranslate X to WPML =

In this mode, the QT import plugin will convert the language information from qTranslate's language tags format to WPML's post-per-language format. For this to work, you must have [WPML](http://wpml.org) active in the site (but not necessarily configured).

Instructions:

1. Save all qTranslate settings
2. Go to the Plugins admin page and de-activate qTranslate X
3. Have WPML activated, but not yet configured (just activated)
4. Install & activate QT Importer
5. Go to Options -> QT Importer and click Start
6. Add redirects from old URLs to new URLs

The import runs in small batches so it doesn't have timeout issues with large databases. You can run it on sites of any size.

During the import process, the plugin generates a set of URL redirect rules. These rules tell visitors and search engines that the URLs in your site have changed (from qTranslate's format to WPML's format). When the import completes, you'll be able to export these rules either as rewrite directives for your .htaccess file or as a PHP file to add to the theme.

You can skip the redirect rules, but then, incoming links to internal pages may lead to 404 pages.

The import tool converts posts, meta data and taxonomy. We tried to take every possible scenario in mind, but there's no alternative to manual testing. Please consider spending time reviewing the final result and possible doing some last touch-ups before relaunching the site with WPML.

== Frequently Asked Questions ==

= Which version of WPML can I use this import with? =

It's been tested on WPML 3.5.3 and above. Previous versions might work, but might have unpredictable behavior.

= Do I have to get WPML to use this? =

This plugin has two modes of operation. Without WPML, it will let you clean the qTranslate X language codes from your content and keep just one language. With WPML, you'll be able to keep all languages.

= How long does the import take? =

It should be a few seconds for every 100 posts (depending on your server's CPU and database access). If the import runs for 10 minutes, it probably means that something is wrong. You should contact us in WPML technical forum and get help.

== Installation ==

Upload the plugin to your blog, activate it.

== Screenshots ==

1. Import screen

== Changelog ==

= 1.9.7.2 =
* added information about WP tested version

= 1.9.7 =
* added support for Greek language import (wpmlbridge-184)

= 1.9.6 = 
* Fixed accidental cutting last three letters from titles if the titles are not translated (wpmlbridge-180)

= 1.9.5 =
* Fixed migration when qTranslate uses default url structure (wpmlbridge-178)

= 1.9.4.1 = 
* Fixed the issue with import containing <!--more--> tags (wpmlbridge-172)

= 1.9.4 = 
* Fixed the issue with import containing <!--more--> tags (wpmlbridge-172)

= 1.9.3 =
* Fixed PHP warning about invalid argument for join() (wpmlbridge-153)

= 1.9.2 =
* Fixed issues with empty language names (wpmlbridge-146)
* More descriptive instructions how to update qTranslate settings before migration (wpmlbridge-145)


= 1.9.1 = 
* Fixed warnings (wpmlbridge-139)

= 1.9 =
* Better handling uppercased language codes (wpmlbridge-136)
* Handling empty titles (wpmlbridge-138)
* Fixed missing language information about primary language (wpmlbridge-137)
* Removed notices during metadata import (wpmlbridge-139)

= 1.8.1 =
* Fixed issue with uppercased language codes (wpmlbridge-103)

= 1.8 =
* Added support for legacy qTranslate (without X) syntax

= 1.7.2 =
* Changed notice to report issues in our forum

= 1.7.1 =
* Added notice to report any issue to our forum

= 1.7 =
* Plugin updated to be compatible with WPML 3.5 and newer

= 1.6.1 =
* qTranslate name updated to qTranslate X to avoid confusions (old qTranslate, without X is not supported)

= 1.6 =
* Tested with WP 4.6 and 4.7-nightly
* Updated to use current WPML functions (as in WPML 3.5.3.1)

= 1.5 =
* Tested with WP 4.1
* Changed title format to "%Original title% (%lang_code%)" if title not translate
* Bug fix: terms not synchronized when used uppercase codes in qTranslate X
* Bug fix: translated posts not imported if title not translated

= 1.4 =
* Tested with WP 4.0
* Added dependency for disable qTranslate X before import to WPML
* Added compatibility with WPML 3.1.8.*
* Bug fix: convert language codes from uppercase to lowercase

= 1.3 =
* Tested with WP 3.9.1
* Feature: using batches for taxonomies
* Bug fix: import custom post types
* Bug fix: copy content/title/excerpt to default language if translations don't exists
* Added "No" and "cz" codes to language mapping

= 1.2 =
* Bug fix: correct language mapping for Hebrew.

= 1.1 =
* Bug fixes: contents not being visible after import in some circumstances and others.

= 0.2.2 =
* Bug fixes

= 0.2 =
* Adds all custom fields to posts. If they have no translation, they are added to posts in all languages.
* Fixes cases where the importer was stuck.

= 1.0 =
* More bug fixes: importing terms, handling posts without titles, posts without translation in the default language and more

= 0.1 =
* Initial release

== Upgrade Notice ==

= 0.1 =
* Initial release

= 0.2 =
* Includes bug fixes and better support for custom fields


