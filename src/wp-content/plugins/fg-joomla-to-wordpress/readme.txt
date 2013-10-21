=== FG Joomla to WordPress ===
Contributors: Frédéric GILLES
Plugin Uri: http://wordpress.org/extend/plugins/fg-joomla-to-wordpress/
Tags: joomla, mambo, wordpress, migrator, converter, import, k2, jcomments, joomlacomments, jomcomment, flexicontent, postviews, joomlatags, sh404sef, attachments, rokbox, kunena, phocagallery, phoca, joomsef, opensef, easyblog
Requires at least: 3.0
Tested up to: WP 3.6.1
Stable tag: 1.21.3
License: GPLv2
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=fred%2egilles%40free%2efr&lc=FR&item_name=Fr%c3%a9d%c3%a9ric%20GILLES&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted

A plugin to migrate categories, posts, tags, images and other medias from Joomla to WordPress

== Description ==

This plugin migrates sections, categories, posts, images, medias and tags from Joomla to Wordpress.

It has been tested with **Joomla versions 1.5, 1.6, 1.7, 2.5, 3.0 and 3.1** and **Wordpress 3.6.1** on huge databases (72 000+ posts). It is compatible with multisite installations.

Major features include:

* migrates Joomla sections as categories
* migrates categories as sub-categories
* migrates Joomla posts (published, unpublished and archived)
* uploads all the posts media in WP uploads directories (as an option)
* uploads external media (as an option)
* modifies the post content to keep the media links
* resizes images according to the sizes defined in WP
* defines the featured image to be the first post image
* keeps the alt image attribute
* keeps the image caption
* modifies the internal links
* migrates meta keywords as tags
* migrates page breaks
* can import Joomla articles as posts or pages

No need to subscribe to an external web site.

= Premium version =

The **Premium version** includes these extra features:

* migrates authors and other users with their passwords
* migrates the navigation menus
* SEO: migrates the meta description and the meta keywords
* SEO: keeps the Joomla articles IDs or redirects Joomla URLs to the new WordPress URLs
* compatible with **Joomla 1.0** and **Mambo 4.5 and 4.6** (process {mosimages} and {mospagebreak})
* migrates Joomla 1.0 static articles as pages
* migrates Joomla 2.5+ featured images

The Premium version can be purchased on: http://www.fredericgilles.net/fg-joomla-to-wordpress/

= Add-ons =

The Premium version allows the use of add-ons that enhance functionality:

* K2
* EasyBlog
* Flexicontent
* Kunena forum
* sh404sef
* JoomSEF
* OpenSEF
* WP-PostViews (keep Joomla hits)
* JComments
* JomComment
* Joomlatags
* Attachments
* Rokbox
* Phocagallery

These modules can be purchased on: http://www.fredericgilles.net/fg-joomla-to-wordpress/

== Installation ==

1.  Install the plugin in the Admin => Plugins menu => Add New => Upload => Select the zip file => Install Now
2.  Activate the plugin in the Admin => Plugins menu
3.  Run the importer in Tools > Import > Joomla (FG)

== Frequently Asked Questions ==

= I get the message: "[fgj2wp] Couldn't connect to the Joomla database. Please check your parameters. And be sure the WordPress server can access the Joomla database. SQLSTATE[28000] [1045] Access denied for user 'xxx'@'localhost' (using password: YES)" =

* First verify your login and password to your Joomla database.
* If Joomla and WordPress are not installed on the same host, you can do this:
- export the Joomla database to a SQL file (with phpMyAdmin for example)
- import this SQL file on the same database as WordPress
- run the migration by using WordPress database credentials (host, user, password, database) instead of the Joomla ones in the plugin settings.

= The migration stops and I get the message: "Fatal error: Allowed memory size of XXXXXX bytes exhausted" =

* You can run the migration again. It will continue where it stopped.
* You can add: `define('WP_MEMORY_LIMIT', '64M');` in your wp-config.php file to increase the memory allowed by WordPress
* You can also increase the memory limit in php.ini if you have write access to this file (ie: memory_limit = 128M).

= The media are not imported =

* Check the URL field that you filled in the plugin settings. It must be your Joomla home page URL and must start with http://

= The media are not imported and I get the error message: "Warning: copy() [function.copy]: URL file-access is disabled in the server configuration" =

* The PHP directive "Allow URL fopen" must be turned on in php.ini to copy the medias. If your remote host doesn't allow this directive, you will have to do the migration on localhost.

= Nothing is imported at all =

* Check your Joomla version. The Joomla 1.0 database has got a different structure from the other versions of Joomla. Importing Joomla 1.0 database is a Premium feature.

= All the posts are not migrated. Why ? =

* The posts put in trash are not migrated. But unpublished and archived posts are migrated as drafts.

= I get the message: "Fatal error: Class 'PDO' not found" =

* PDO and PDO_MySQL libraries are needed. You must enable them in php.ini.

= I get this error: PHP Fatal error: Undefined class constant 'MYSQL_ATTR_INIT_COMMAND' =

* You have to enable PDO_MySQL in php.ini. That means uncomment the line extension=pdo_mysql.so in php.ini

= Does the migration process modify the Joomla site it migrates from? =

* No, it only reads the Joomla database.

= I get this error: Erreur !: SQLSTATE[HY000] [1193] Unknown system variable 'NAMES' =

* It comes from MySQL 4.0. It will work if you move your database to MySQL 5.0 before running the migration.

= None image get transferred into the WordPress uploads folder. I'm using Xampp on Windows. =

* Xampp puts the htdocs in the applications folder which is write protected. You need to move the htdocs to a writeable folder.

= How to import content from one section as posts and another section as pages? =

* You can use the Convert Post Types plugin after the migration.

Don't hesitate to let a comment on the forum or to report bugs if you found some.
http://wordpress.org/support/plugin/fg-joomla-to-wordpress

== Screenshots ==

1. Parameters screen

== Translations ==
* English (default)
* French (fr_FR)
* Spanish (es_ES)
* German (de_DE)
* Russian (ru_RU)
* Polish (pl_PL)
* other can be translated

== Changelog ==

= 1.21.3 =
* Fixed: "Warning: sprintf(): Too few arguments" message for image captions with %

= 1.21.0 =
* New translation: Spanish (thanks to Bradis García L.)

= 1.20.1 =
* Fixed: Use the modified post date if the creation date is empty
* Fixed: Warning: array_key_exists() [function.array-key-exists]: The second argument should be either an array or an object

= 1.19.3 =
* Fixed: Some spaces were removed (due to the extra newlines removal)
* Fixed: Better rule for the convert_post_attribs_to_array function
* Fixed: "WordPress database error Field 'post_content' doesn't have a default value"

= 1.19.0 =
* New: Import the page breaks
* New: Option to import the Joomla introtext in the post and in the excerpt
* New: Use the show_intro article parameter to import the introtext in the content or not
* Tested with WordPress 3.6.1

= 1.18.0 =
* New: Compatibility with Joomla 3.1
* Fixed: Remove extra newlines

= 1.17.0 =
* New: Add automatically http:// at the beginning of the URL if it is missing
* New: Option for the first image import
* FAQ updated

= 1.16.1 =
* Fixed: syntax error, "unexpected '&lt;'" in version 1.16.0

= 1.16.0 =
* New: Option to import images with duplicate names
* New translation: Polish (Thanks to Łukasz Z.)
* FAQ updated

= 1.15.2 =
* Optimize the Joomla connection

= 1.15.1 =
* New: Option to not import archived posts or to import them as drafts or as published posts

= 1.15.0 =
* New: Import archived posts as drafts
* Tested with WordPress 3.6

= 1.14.2 =
* Fixed: The HTML classes were lost in the a-href and img tags
* Unset by default the checkbox «Import the text above the "read more" to the excerpt»

= 1.14.1 =
* Fixed: The caption shortcode is imported twice if the image has a link a-href pointing to a different image

= 1.14.0 =
* New: Import images captions
* Improve speed of processing the image links
* Update the FAQ

= 1.13.0 =
* Tested with WordPress 3.5.2
* New: Add a button to save the settings
* New: Improve the speed of emptying the WordPress content

= 1.12.1 =
* Fixed: Replaces the publication date by the creation date as Joomla uses the creation date for sorting articles

= 1.12.0 =
* New: Add a button to remove the categories prefixes
* New: Option to not use the first post image as the featured image

= 1.11.0 =
* New: Import external media (as an option)
* New translation: Russian (Thanks to Julia N.)

= 1.10.6 =
* Fixed: Categories hierarchy lost when parent categories had an id greater than their children

= 1.10.4 =
* Fixed: Posts were not imported when the skip media option was off

= 1.10.3 =
* Fixed: Categories hierarchy lost when parent categories had an id greater than their children (Joomla 1.6+)
* New: Add hooks for extra images and after saving options

= 1.10.2 =
* Tested with WordPress 3.5.1
* New: Add hooks in the modify_links method

= 1.10.1 =
* New: Add a hook for extra options
* Fixed: Move the fgj2wp_post_empty_database hook
* FAQ updated

= 1.10.0 =
* New: Compatibility with Joomla 3.0
* New: Option to delete only new imported posts without deleting the whole database

= 1.9.1 =
* Fixed: the internal links where not modified on pages

= 1.9.0 =
* Tested with WordPress 3.5
* New: Button to test the database connection
* New: Improve the user experience by displaying explanations on the parameters and error messages
* New: get_categories hook modified

= 1.8.5 =
* New: Option to not import already imported medias

= 1.8.4 =
* FAQ updated

= 1.8.3 =
* Fixed: Cache flushed after the migration
* Fixed: Compatibility issue with WordPress < 3.3

= 1.8.2 =
* New: Better compatibility for copying media: uses the WordPress HTTP API

= 1.8.1 =
* New: Better compatibility for copying media: uses the copy function if cURL is not loaded

= 1.8.0 =
* New: Compatibility with PHP 5.1 (thanks to dmikam)
* New: Compatibility with WordPress 3.0 (thanks to dmikam)
* New: Better compatibility for copying media (uses cURL) (thanks to dmikam)

= 1.7.1 =
* FAQ updated

= 1.7.0 =
* New: Compatibility with Joomla 2.5

= 1.6.3 =
* New hooks added
* Description updated

= 1.6.2 =
* FAQ updated

= 1.6.1 =
* Fixed: clean the cache after emptying the database
* Fixed: the categories slugs were not imported if they had no alias

= 1.6.0 =
* New: Compatibility with Joomla 1.6 and 1.7

= 1.5.0 =
* New: Can import posts as pages (thanks to LWille)
* Translation: German (thanks to LWille)

= 1.4.2 =
* Tested with WordPress 3.4

= 1.4.1 =
* Add "c" in the category slug to not be in conflict with the Joomla URLs
* FAQ and description updated

= 1.4.0 =
* New: Option to import meta keywords as tags

= 1.3.1 =
* New: Deactivate the cache during the migration for improving speed

= 1.3.0 =
* New: Modify posts internal links using WordPress permalinks setup
* Fixed: Exhausted memory issue

= 1.2.2 =
* Fixed: Don't import HTML links as medias
* FAQ updated

= 1.2.1 =
* New: Get the post creation date when the publication date is empty
* Fixed: Accept categories with spaces in alias

= 1.2.0 =
* New: Import all media
* Fixed: Do not reimport already imported categories
* Fixed: Update categories cache
* Fixed: Issue with media containing spaces
* Fixed: Original images sizes are kept in post contents

= 1.1.1 =
* New: Manage sections and categories duplicates
* Fixed: Wrong categorization of posts

= 1.1.0 =
* Update the FAQ
* New: Can restart an import where it left after a crash (for big databases)
* New: Display the number of categories, posts and images already imported
* Fixed: Issue with categories with alias but no name
* Fixed: Now import only post categories, not all categories (ie modules categories, …)

= 1.0.2 =
* Fixed: The images with absolute links were not imported.
* New: Option to skip the images import
* New: Skip external images

= 1.0.1 =
* Fixed: The content was not imported in the post content for the posts without a "Read more" link.
* New: Option to choose to import the Joomla introtext in the excerpt or in the post content with a «Read more» tag.

= 1.0.0 =
* Initial version: Import Joomla 1.5 sections, categories, posts and images

== Upgrade Notice ==

= 1.21.3 =
Fixed: "Warning: sprintf(): Too few arguments" message for image captions with %

= 1.21.0 =
New translation: Spanish (thanks to Bradis García L.)

= 1.20.1 =
Fixed: Use the modified post date if the creation date is empty
Fixed: Warning: array_key_exists() [function.array-key-exists]: The second argument should be either an array or an object

= 1.19.3 =
Fixed: Some spaces were removed (due to the extra newlines removal)
Fixed: Better rule for the convert_post_attribs_to_array function
Fixed: "WordPress database error Field 'post_content' doesn't have a default value"

= 1.19.0 =
New: Import the page breaks
New: Option to import the Joomla introtext in the post and in the excerpt
New: Use the show_intro article parameter to import the introtext in the content or not
Tested with WordPress 3.6.1

= 1.18.0 =
New: Compatibility with Joomla 3.1
Fixed: Remove extra newlines

= 1.17.0 =
New: Add automatically http:// at the beginning of the URL if it is missing
New: Option for the first image import

= 1.16.1 =
Fixed: syntax error, "unexpected '&lt;'" in version 1.16.0

= 1.16.0 =
New: Option to import images with duplicate names
New translation: Polish

= 1.15.2 =
Optimize the Joomla connection

= 1.15.1 =
New: Option to not import archived posts or to import them as drafts or as published posts

= 1.15.0 =
New: Import archived posts as drafts
Works with WordPress 3.6

= 1.14.2 =
Fixed: The HTML classes were lost in the a-href and img tags

= 1.14.1 =
Fixed: The caption shortcode is imported twice if the image has a link a-href pointing to a different image

= 1.14.0 =
New: Import images captions
Improve speed of processing the image links

= 1.13.0 =
Works with WordPress 3.5.2
New: Add a button to save the settings
New: Improve the speed of emptying the WordPress content

= 1.12.1 =
Fixed: Replaces the publication date by the creation date as Joomla uses the creation date for sorting articles

= 1.12.0 =
New: Add a button to remove the categories prefixes
New: Option to not use the first post image as the featured image

= 1.11.0 =
New: Import external media (as an option)
New translation: Russian (Thanks to Julia N.)

= 1.10.6 =
Fixed: Categories hierarchy lost when parent categories had an id greater than their children

= 1.10.4 =
Fixed: Posts were not imported when the skip media option was off

= 1.10.3 =
Fixed: Categories hierarchy lost when parent categories had an id greater than their children (Joomla 1.6+)

= 1.10.2 =
Works with WordPress 3.5.1

= 1.10.1 =
Fixed: Move the fgj2wp_post_empty_database hook

= 1.10.0 =
Compatibility with Joomla 3.0
Option to delete only new imported posts without deleting the whole database

= 1.9.1 =
Fixed: the internal links where not modified on pages

= 1.9.0 =
Tested with WordPress 3.5
Button to test the database connection
Improve the user experience by displaying explanations on the parameters and error messages

= 1.8.5 =
Option to not import already imported medias

= 1.8.4 =
FAQ updated

= 1.8.3 =
Cache flushed after the migration
Fixed compatibility issue with WordPress < 3.3

= 1.8.2 =
Better compatibility for copying media

= 1.8.1 =
Better compatibility for copying media

= 1.8.0 =
Compatibility with PHP 5.1
Compatibility with WordPress 3.0
Better compatibility for copying media (uses cURL)

= 1.7.0 =
Compatibility with Joomla 2.5

= 1.6.3 =
New hooks added
Description updated

= 1.6.2 =
FAQ updated

= 1.6.1 =
Bug fixes

= 1.6.0 =
Compatibility with Joomla 1.6 and 1.7

= 1.5.0 =
Can import posts as pages
German translation

= 1.4.2 =
Works with WordPress 3.4

= 1.4.1 =
Add "c" in the category slug to not be in conflict with the Joomla URLs
FAQ and description updated

= 1.4.0 =
Option to import meta keywords as tags

= 1.3.1 =
Improve speed

= 1.3.0 =
Modify posts internal links using WordPress permalinks setup
Exhausted memory issue fixed

= 1.2.2 =
Don't import HTML links as medias

= 1.2.1 =
Get the post creation date when the publication date is empty
Accept categories with spaces in alias

= 1.2.0 =
Import all media

= 1.1.1 =
Manage sections and categories duplicates

= 1.1.0 =
You can restart an import where it left after a crash (for big databases).

= 1.0.2 =
You can now skip the images import. And even if you keep on importing the images, the external images are automatically skipped.

= 1.0.1 =
* Fixed: The content was not imported in the post content for the posts without a "Read more" link.
* New: You can now choose to import the Joomla introtext in the excerpt or in the post content with a «Read more» tag.

= 1.0.0 =
Initial version
