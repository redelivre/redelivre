=== Tumblr Importer ===
Contributors: wordpressdotorg, Otto42, dd32, westi, dllh
Tags: tumblr, import
Requires at least: 3.2
Tested up to: 3.6
Stable tag: 0.8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

Imports a Tumblr blog into a WordPress blog.

* Correctly handles post formats
* Background importing: start it up, then come back later to see how far it's gotten
* Duplicate checking, will not create duplicate imported posts
* Imports posts, drafts, and pages
* Media Side loading (for audio, video, and image posts)

== Installation ==

1. Upload the files to the `/wp-content/plugins/tumblr-importer/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Go to Tools->Import and use the new importer.

== Upgrade Notice ==

Version 0.8 fixes a problem with authorization caused by Tumblr's undocumented change to their OAuth handling.

== Changelog ==

= 0.8 =
* Fix callback handling for Tumblr OAuth. They no longer recognize the callback in the authorize URL, and instead expect a non-urlencoded callback parameter in the request-token call. This is not documented anywhere that I can find.

= 0.7 =
* Update to use new Tumblr API, many fixes and improvements.

= 0.6 =
* Significant improvements in the performance of the importer
* Improves import of images from Tumblr - better choice of images sizes for theme display
* Improved author selection logic on single author blogs
* Auto refreshing to show import progress and give clearer feedback
* Improves import videos from Tumble - enable auto-embedding for for content.
* Block imports from Tumblr sites with mapped domains enabled because they don't work well - you have to temporarily disable the mapping.

= 0.5 =
* Fix edge cases for tumblr photos where tumblr isn't returning expected headers for filenames

= 0.4 =
* Map multi-image posts to Gallery post format
* Import Tags
* Import Media to server (Images, Audio, Custom uploaded Video's)
* Set the date on Media imports for easier management

= 0.3 = 
* Handle multi-image posts
* Handle question/answer posts
* Handle video posts somewhat better
* Speedup (reduce importer delay from 3 minutes to 1 minute)

= 0.2 = 
* The audio, video, and image formats no longer use the caption for the titles. Tumblr seems to facilitate putting all sorts of crazy stuff into the caption fields as part of their reblogging system. So instead, these types of posts will have no titles at all. Sorry, but Tumblr simply doesn't have any sort of title fields here to work with, and no data that can be used to "create" a title for them.
* Minor debug error cleanup.
* Sideloading now done on drafts and pages as well.

= 0.1 =
* First version, not meant to be used except for testing.
