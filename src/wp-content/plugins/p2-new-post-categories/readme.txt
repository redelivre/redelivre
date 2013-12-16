=== P2 New Post Categories ===
Contributors:      iandunn
Donate link:       http://wordpressfoundation.org
Tags:              p2, category
Requires at least: 2.3
Tested up to:      3.8-RC1
Stable tag:        0.3
License:           GPLv2 or Later

Adds a dropdown menu of categories to the P2 new post form.


== Description ==
The <a href="http://wordpress.org/themes/p2">P2</a> theme lets you quickly create posts from the front end, but by default it only allows you to tag them. This plugin lets adds a dropdown box to the post form so that you can also assign a category to the post.

== Screenshots ==
1. The new post form with the category dropdown added


== Changelog ==

= v0.3 (12/4/2013) =
* [NEW] p2 version 1.5.2 is now required.
* [NEW] Fires an AJAX request on the new post trigger to store the category instead of hijacking the tag field.
* [NEW] Select the default category in the dropdown list instead of the first one.

= v0.2 (9/16/2013) =
* [NEW] Order category dropdown by category name instead of ID
* [FIX] [Show empty categories in the category dropdown](http://wordpress.org/support/topic/support-expectations?replies=2#post-4658920).

= v0.1 (9/16/2013) =
* [NEW] Initial release


== Upgrade Notice ==

= 0.3 =
The tag field is no longer used to store the category, which makes for a cleaner and less confusing experience. Requires p2 version 1.5.2.

= 0.2 =
The category dropdown is ordered by name instead of ID, and empty categories are shown.

= 0.1 =
Initial release.