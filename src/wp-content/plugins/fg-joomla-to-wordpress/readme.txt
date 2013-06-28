=== FG Joomla to WordPress ===
Contributors: Frédéric GILLES
Plugin Uri: http://wordpress.org/extend/plugins/fg-joomla-to-wordpress/
Tags: joomla, mambo, wordpress, migrator, converter, import, k2, jcomments, joomlacomments, jomcomment, flexicontent, postviews
Requires at least: 3.0
Tested up to: WP 3.5.1
Stable tag: 1.12.1
License: GPLv2
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=fred%2egilles%40free%2efr&lc=FR&item_name=Fr%c3%a9d%c3%a9ric%20GILLES&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted

A plugin to migrate categories, posts, tags, images and other medias from Joomla to WordPress

== Description ==

This plugin migrates sections, categories, posts, images, medias and tags from Joomla to Wordpress.

It has been tested with **Joomla versions 1.5, 1.6, 1.7, 2.5 and 3.0** and **Wordpress 3.5.1** on huge databases (72 000+ posts). It is compatible with multisite installations.

Major features include:

* migrates Joomla sections as categories
* migrates categories as sub-categories
* migrates Joomla posts
* uploads all the posts media in WP uploads directories (as an option)
* uploads external media (as an option)
* modifies the post content to keep the media links
* resizes images according to the sizes defined in WP
* defines the thumbnail to be the first post image
* keeps the alt image attribute
* modifies the internal links
* migrates meta keywords as tags
* can import Joomla posts as posts or pages

The **Premium version** includes these extra features:

* migrates authors and other users
* SEO: redirects Joomla URLs to the new WordPress URLs
* compatible with **Joomla 1.0** and **Mambo 4.5 and 4.6** (process {mosimages} and {mospagebreak})
* migrates Joomla 1.0 static articles as pages
* migrates Joomla 2.5+ featured images

The **K2 module** is an extension of the Premium version. It includes these extra features:

* migrates K2 items
* migrates K2 categories
* migrates K2 tags
* migrates K2 comments
* migrates K2 images
* migrates K2 attachments
* migrates K2 custom fields

The **WP-PostViews module** is an extension of the Premium version. It includes these extra features:

* migrates the Joomla views counts. This module requires the WP-PostViews plugin.

The **JComments module** is an extension of the Premium version. It includes these extra features:

* migrates the comments from the JComments Joomla extension

The **JomComment module** is an extension of the Premium version. It includes these extra features:

* migrates the comments from the JomComment Joomla extension

The **Flexicontent module** extends the Premium version. It includes these extra features:

* migrates Flexicontent items/categories relations
* migrates Flexicontent tags

The Premium version and the modules can be purchased on: http://www.fredericgilles.net/fg-joomla-to-wordpress/

== Installation ==

1.  Extract plugin zip file and load up to your wp-content/plugin directory
2.  Activate Plugin in the Admin => Plugins Menu
3.  Run the importer in Tools > Import > Joomla (FG)

== Frequently Asked Questions ==

= All the posts are not migrated. Why ? =

* The archived posts or posts put in trash are not migrated. But unpublished posts are migrated as drafts.

= The migration stops and I get the message: "Fatal error: Allowed memory size of XXXXXX bytes exhausted" =

* You can run the migration again. It will continue where it stopped. You can also increase the memory limit in php.ini if you have write access to this file.

= The media are not migrated and I get the error message: "Warning: copy() [function.copy]: URL file-access is disabled in the server configuration" =

* The PHP directive "Allow URL fopen" must be turned on in php.ini to copy the medias. If your remote host doesn't allow this directive, you will have to do the migration on localhost.

= I get the message: "Fatal error: Class 'PDO' not found" =

* PDO and PDO_MySQL libraries are needed. You must enable them in php.ini.

= I get this error: PHP Fatal error: Undefined class constant 'MYSQL_ATTR_INIT_COMMAND' =

* You have to enable PDO_MySQL in php.ini. That means uncomment the line extension=pdo_mysql.so in php.ini

= I get the message: "SQLSTATE[28000] [1045] Access denied for user 'xxx'@'localhost' (using password: YES)" =

* First verify your login and password to your Joomla database.
* You must give access to the WordPress host on your Joomla database.
* If your provider doesn't allow external IP to access your database, you have two solutions:
- install WordPress on the same host as Joomla
- install WordPress and the Joomla database on your localhost and do the migration on localhost

= Does the migration process modify the Joomla site it migrates from? =

* No, it only reads the Joomla database.

= I get this error: Erreur !: SQLSTATE[HY000] [1193] Unknown system variable 'NAMES' =

* It comes from MySQL 4.0. It will work if you move your database to MySQL 5.0 before running the migration.

= None image get transferred into the WordPress uploads folder. I'm using Xampp on Windows. =

* Xampp puts the htdocs in the applications folder which is write protected. You need to move the htdocs to a writeable folder.

Don't hesitate to let a comment on the forum or to report bugs if you found some.
http://wordpress.org/support/plugin/fg-joomla-to-wordpress

== Screenshots ==

1. Parameters screen

== Translations ==
* English (default)
* French (fr_FR)
* German (de_DE)
* Russian (ru_RU)
* other can be translated

== Changelog ==

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

`<?php code(); // goes in backticks ?>`
