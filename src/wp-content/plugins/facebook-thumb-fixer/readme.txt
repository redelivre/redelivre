=== Facebook Thumb Fixer ===
Contributors: mikeyott
Tags: facebook, thumb, fixer, default, thumbnail, thumbnails, thumbs, og:image, og:description, og:title, open, graph, open graph
Requires at least: 4.0
Tested up to: 4.2.3
Stable tag: trunk

Fixes the problem of the missing (or wrong) thumbnail when a post is shared on Facebook.

== Description ==

This plug-in is for those who have the problem where sharing a post on Facebook shows the wrong (or no) thumbnail image.

It works by making sure the thumbnail is derived from the featured image of your post. If your post doesn't have a featured image then it will use a fall-back image that you can specify.

The plug-in inserts the open graph meta properties, which Facebook and other social services look for when someone shares your page. These properties contain information about the page and of course the image you specify.

<strong>NEW!</strong> View a preview of how your page will look when shared on Facebook.

== Installation ==

Install, activate, done.

== Uninstall ==

Deactivate the plugin, delete if desired.

== Official Web Site (and support) ==

<a href="https://wordpress.org/support/plugin/facebook-thumb-fixer">Facebook Thumb Fixer Support</a> at the official Wordpress repository.

Go to 'Facebook Thumb Fixer' in WordPress admin for more information about how it works, what it does, and how to resolve common problems.

== How to set a fall-back image ==

Go to Settings -> General and scroll down until you find 'Default Facebook Thumb'. Put the path to your fall-back image there. Make sure it's at least 1200x630 or 600x315.

== How to set an object type ==

<strong>Posts and Pages</strong>

On each page or post you edit there is an 'Open Graph Object Type' meta box. Simply make a section from there to specify what Object Type the page or post is. Example: If the it's an article, then choose 'article'. If it's a product, choose 'product'. To help you decide what Object Type to choose, go <a href-"https://developers.facebook.com/docs/reference/opengraph" target="_blank">here</a> to learn the differences between them all.

Note: If no selection is made for posts or pages then the Object Type will be 'article', which in most cases is fine.

<strong>Home page</strong>

To specify what Object Type your homepage is, go to the Wordpress Settings -> General page and make a selection from the 'Home page Object Type' field.

Note: If no selection is made for the home page then the Object Type will be 'webpage', which in most cases is probably what you want.

== Changelog ==

= 1.5.1 =

Fixed Undefined variables issue.
Confirmed WP 4.2.3 compatibility.
Updated support link.

= 1.5 =

Added Facebook preview.

= 1.4.9 =

Updated help topics with answers to some common questions.

= 1.4.8 =

Suppressed unnecessary warning when fall-back image is missing.

= 1.4.7 =

Fixed extremely rare conflict with other plug-ins. No need to update unless this plug-in is causing a conflict with another.

= 1.4.6 =

Non-critical updates: 
Fixed incorrect reference in help/tips of recommended Facebook image sizes. 
Added help topic and suggested fix for those experiencing rare issue of image dimensions not showing in plug-in. 

= 1.4.5 =

Full sized default thumbnail image preview.
Updated help topics and tips to reflect latest Facebook recommendations.
Presentation tweaks.

= 1.4.4 =

Prevented open graph from outputting on BuddyPress members pages to 'fix' malformed HTML issue.

= 1.4.3 =

Updated version compatibility for Wordpress 4.0.

= 1.4.2 =

Updated documentation page.

= 1.4.1 =

Added Google+ full bleed image support.

= 1.4 =

Added the ability to specify a unique Object Type for each post, page and the home page.
Updated documentation.

= 1.3.5 =

Replaced strip_tags with preferable wp_kses function.
HTML is now stripped from excerpts.
Fixed issue where title sometimes wasn't being output into og:title on posts.

= 1.3.4 =

'Tested up to' compatibility with Wordpress 3.8.
Wordpress 3.8 notification UI.
Updated documentation to reflect current Facebook requirements, removed document redundancy.
Typo corrections.

= 1.3.3 =

* Minor update: Added strip_tags in more places to prevent potential conflict issue.

= 1.3.2 =

* Changed function name to something less generic to avoid potential conflict with other plugins.

= 1.3.1 =

* Updated recommended og:image dimensions.

= 1.3 =

* Included new open graph properties for og:description, og:site_name and og:type.
* Added visual indication of the field on the settings page.
* Fixed width on preview image (for when someone accidentally uses a massive image).
* Updated help guide.

= 1.2.3 =

* Changed comment output in head to 'Open Graph' instead of 'Facebook Open Graph'.
* Clarification on how the plugin works.

= 1.2.2 =

* Added og:url meta property (according to Facebook debugger, now required).

= 1.2.1 =

* Updated documentation page, suggesting default thumbnail to be 155x155 (because thumbs are that size on Facebook brand pages).

= 1.2 =

* Minor edits, nothing important.

= 1.1 =

* Updated tags.
* Fixed typos.

= 1.0 =

* Release candidate finished.
* Added support information under admin Settings -> Facebook Thumb Fixer
* Updated support information.

= 0.9 =

* Swapped out deprecated Wordpress variables.