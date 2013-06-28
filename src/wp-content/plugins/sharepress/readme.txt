=== Plugin Name ===
Contributors: aaroncollegeman
Donate link: http://aaroncollegeman.com/sharepress
Tags: facebook, twitter, social, like, posts, page
Requires at least: 2.9
Tested up to: 3.5.1
Stable tag: 2.2.11

Share the content you write in WordPress with your Facebook Fans and Twitter Followers, simply and reliably.

== Description ==

SharePress is a WordPress plugin that helps you communicate with your [tribes](http://sethgodin.typepad.com/seths_blog/2008/01/tribal-manageme.html) on Facebook and Twitter by automatically publishing your WordPress posts the moment they become live on your site.

**With this FREE version of SharePress you'll be able to**

* Automatically publish your WordPress posts to your personal Facebook wall
* Publish while you sleep: your post is automatically published when it goes live

**Upgrade to the [PRO version](http://aaroncollegeman.com/sharepress?utm_source=wordpress.org&utm_medium=app-store&utm_campaign=pro-version) and you'll be able to**

* Publish to any/all of the Facebook pages you manage
* Publish to your Twitter followers
* Customize each Facebook status message
* Control the image Facebook uses just by setting the post's featured image
* Customize Twitter status hashtag
* Delay SharePress' sharing for minutes, hours, or days after a post goes live
* Schedule reposts of your content: keep traffic flowing to your site day and night
* Get e-mail based support directly from the developer, often on the same day

Single-site licensing, and unlimited Developer licensing are available. SharePress is compatible with WordPress MU and WordPress Multi-Network.

Want to read what our customers have said about SharePress? [Check this out](http://aaroncollegeman.com/sharepress?utm_source=wordpress.org&utm_medium=app-store&utm_campaign=testimonials).

[youtube http://youtube.com/watch?v=X6dRD0pd1nM]

Awesome product promo video by [Keyframe Films](http://keyframefilms.com).

== Installation ==

Want to try SharePress for FREE?

1. Get the plugin. Activate the plugin.

2. [Create a Facebook application](http://aaroncollegeman.com/sharepress/help/how-to-setup-sharepress/?utm_source=wordpress.org&utm_medium=app-store&utm_campaign=get-support#creating-your-facebook-application).

3. Go to Settings / SharePress, and run setup.

Need support? [Go here](http://aaroncollegeman.com/sharepress/help?utm_source=wordpress.org&utm_medium=app-store&utm_campaign=get-support).

== Frequently Asked Questions ==

= How do I post to the wall of my Facebook page? =

You need the PRO version. All you have to do is [buy a key](http://aaroncollegeman.com/sharepress?utm_source=wordpress.org&utm_medium=app-store&utm_campaign=post-to-page).

= Why do I need to pay for this essential feature (posting to my Facebook page)? =

SharePress' features are without parallel. Being able to post automatically to your Facebook page is only the beginning. The reposting feature is the one that has the potential to bring the most traffic growth to your site.

Plus, Facebook is a very difficult platform the work with - their motto is, "Move fast and break things." No joke. When you pay for SharePress you are paying for Fat Panda to constantly monitor and maintain this stable connection between your site and the social Web.

= I'm posting to Facebook via Twitter. Isn't that the same thing? =

No. SharePress PRO provides you with the ability to customize message (status update) and the photo that appears on Facebook. Posting via Twitter does not provide this feature.

= When will SharePress support LinkedIn? =

Soon. SharePress 3.0 is nigh.

= When will SharePress support Google+? =

At this time it is not possible to publish to Google+ as Google does not offer a writable API. 

Other services and plugins support writing to Google+, but they do this by storing your Google account username and password, which is *extremely* dangerous.

= I'm having trouble. Where can I get support? =

Documentation for SharePress is available [here](http://aaroncollegeman.com/sharepress/help?utm_source=wordpress.org&utm_medium=app-store&utm_campaign=get-support).

E-mail based tech support is available to PRO customers. All you have to do is [buy a key](http://aaroncollegeman.com/sharepress?utm_source=wordpress.org&utm_medium=app-store&utm_campaign=get-support).

== Changelog ==

= 2.2.11 =
* Fix: Compatible with Twitter's API v1.1

= 2.2.10 =
* Change: Compatible with WordPress 3.5.1
* Change: No longer using intercom.io for support or usage tracking - too expensive
* Change: Implemented Google Analytics for anonymous usage tracking, on by default but can be disabled from the settings screen
* Change: For usability, gray-out Application pages from Target lists

= 2.2.9 =
* Change: Compatible with WordPress 3.5

= 2.2.8 =
* Change: Compatible with WordPress 3.4.1
* Added: Restore the ability to set image Facebook uses to be the first image in the Post's gallery

= 2.2.7 =
* Change: Version bump - compatible with WordPress 3.4

= 2.2.6 =
* Fixed: Corrected major usability issue in initial setup process

= 2.2.5 =
* Fixed: SharePress was posting Page updates to default sharing targets - DOH!
* Added: Now reporting SharePress version to intercom, so that I can safely notify users who haven't upgraded without annoying users who have

= 2.2.4 =
* Added: More concise description of schedule fixing feature on settings screen regarding 
* Change: When using schedule fixing feature, ignore errors that occurred more than twenty-four hours ago
* Fixed: Sometimes Facebook API errors still made it through into WP admin screen, instead of being handled and warning the user

= 2.2.3 =
* Added: Schedule fixing feature is off by default, and is now configurable from the settings screen

= 2.2.2 =
* Fixed: Twitter posting was broken in 2.2
* Fixed: Posts created via the QuickPress on the Dashboard weren't posting to Facebook or Twitter
* Added: A technique for recovering from "missed schedules" - not necessarily the fault of SharePress

= 2.2.1 =
* Fixed: Big bug in "The first image in the content" option for Facebook post image
* Added: Better control over new support features

= 2.2 =
* Change: Fail gracefully from Facebook API issues, and never clear stored session token unless asked to do so by an administrator (e.g., run setup again)
* Change: No more hourly cron "keeping alive" the session token
* Change: Cache things for a LONG time - at least 30 days in most cases - including request for user profile data, as well as page (target) list and the access tokens therein
* Change: Failure to post to Facebook now no longer results in failure to post to Twitter - the post will still go to Twitter, if programmed to do so
* Fixed: The default open graph image is now 200x200 - well within Facebook's required minimum range
* Added: In-admin support care of Intercom

= 2.1.24 =
* Added: New filter: sharepress_user_can_edit_post($bool_can_edit, $wp_post) - return boolean indicating whether or not the current user has permission to modify sharepress meta data for the given $post
* Added: New filter: sharepress_ignore_save_nonce($bool_can_ignore) - return boolean indicating whether or not a valid nonce should be required for modification a post's sharepress meta data

= 2.1.23 =
* Fixed: When searching for images in post content, do not execute shortcodes - in some instances, this was creating content duplication on the page, in others content would not appear
* Fixed: strtotime error that was appearing for any post that had been published to Facebook more than once
* Fixed: When using the "Use the first image in the post" option, and when photos are set in the gallery, SharePress was selecting the last option, not the first
* Fixed: Corrected the language "Use the first image in the post," (hopefully) made it less confusing
* Added: Debugging (logging) can now be enabled via the settings screen in the admin
* Change: Updated Plugin description to eliminate any confusion created by differences in FREE and PRO versions of SharePress
* Change: Raised og:image size to 200x200 from 150x150

= 2.1.22 =
* Fixed: sharepress_og_tag_<tagName> didn't include $post or $meta arguments the way sharepress_og_tags does
* Added: Allow for open graph meta tag content to be overriden on a case-by-case basis by post meta data

= 2.1.21 =
* Fixed: Press-This tool
* Fixed: Somehow turned logging on by default; setting back to off by default
* Fixed: Issue with preg_replace turning extra whitespace into funky characters in og:description

= 2.1.20 =
* Updated: Link to better setup instructions

= 2.1.19 =
* Fixed: Version bump to address Subversion commit mishap

= 2.1.18 =
* Added: Better debugging output for MU sites that use Domain Mapping
* Added: More concise logging of Facebook meta data and Bit.ly API results
* Added: Read-only fields for Facebook App ID, Secret, and the current User Access token
* Added: Link for jumping to Facebook's Debugger to view details about the User Access token
* Fixed: Restored text about the offline access deprecation and link to my blog post about it (http://aaroncollegeman.com/2012/02/03/breaking-change-configuring-your-facebook-application-for-offline_access-deprecation/)

= 2.1.17 =
* Fixed: Sometimes, the server would fail to verify Bit.ly's SSL certificate; we set sslverify => false, so this shouldn't happen again
* Added: Global default Twitter hashtag

= 2.1.16 =
* Fixed: Using the core Facebook classes is breaking installations. Going back to renaming the core classes.

= 2.1.15 =
* Fixed: Trapping for wrong Facebook class, resulting in upgrades failing if some class named Facebook already exists in the stack

= 2.1.14 =
* Fixed: Custom values for og:locale were not displaying in meta tags
* Fixed: Deleted the remaining Fancybox stuff... I thought I did this once before, but it came back!
* Fixed: Reimplemented Facebook PHP SDK, making better use of OOP design, and correcting some problems with session management
* Added: Better logging for Bit.ly issues

= 2.1.13 =
* Fixed: Was using '150x150' to control og:image thumbnail size, but it needed to be array(150, 150)

= 2.1.12 =
* Fixed: "Fatal error: Call to undefined function get_post_thumbnail_id() in /home/chicagop/public_html/wp-content/plugins/sharepress/sharepress.php on line 564"

= 2.1.11 =
* Fixed: If the global default for Open Graph image is set to Global Default, then you can't use "Use Featured Image" option on individual posts

= 2.1.10 =
* Added: Support for custom post types. See: http://aaroncollegeman.com/2012/02/16/using-sharepress-with-custom-post-types

= 2.1.9 =
* Fixed: Unlocked version was posting "null" in status message

= 2.1.8 =
* Fixed: If user unchecks "Same as title" and leaves the message box empty, don't fill it up automatically

= 2.1.7 =
* Added: Hourly cron job that pings the Facebook API, helping to ensure stored access token stays current

= 2.1.6 =
* Fixed: Stop caching Bit.ly API results
* Fixed: When publishing via XML-RPC, post meta would have title "Auto Draft" and default WP shortlink

= 2.1.5 =
* Fixed: After setting up Facebook app, user was being redirected to options-general.php instead of options-general.php?page=sharepress

= 2.1.4 =
* Fixed: More orphaned Fancybox stuff (sorry!)

= 2.1.3 =
* Fixed: Orphaned bit of JS from when Fancybox was part of SharePress

= 2.1.2 =
* Added: Allow SharePress to be disabled globally with sharepress_enabled filter
* Fixed: There was a bug in the setup step, preventing some users from getting past SDK connect... yikes

= 2.1.1 =
* Added: Delay configuration now visible from post management screen

= 2.1.0 =
* Added: The ability to delay new posts from being posted to SharePress for a user-defineable amount of time.

= 2.0.25 =
* Fixed: "Let facebook choose" mode will now seek out the first image in the content, but only if it finds no other available configuration data (e.g., featured image, or images from the post's gallery)
* Added: Bit.ly powered URL shortening (optional)
* Change: Moved the Save Settings button over to the right and fixed it at the top, since the settings page is now awfully long... and maybe just awful.
* Fixed: SharePress::setting was returning $default when stored setting was == false

= 2.0.24 =
* Fixed: Another bug in og:image selection - this one in the "let facebook choose" mode

= 2.0.23 =
* Fixed: Proper detection of Featured Image defaults in XML-RPC posts
* Change: Dismiss inline errors as user makes corrections to meta data selections (e.g., if we say pick a target, and they do, immediately hide the error)

= 2.0.22 =
* Fixed: Regression: not posting in XML-RPC requests

= 2.0.21 =
* Fully compatible with WordPress 3.3 "Sonny"
* Change: "Let Facebook choose" mode for post image has been replaced with "Use the first image in the post," which is a much better default
* Change: Don't display the setup warning everywhere
* Added: Now you can toggle the post link that appears at the end of Facebook messages
* Added: Filter "sharepress_get_permalink" for influencing the permalink SharePress uses in posts to Facebook and Twitter, and for the og:url field
* Fixed: Scheduled posts were posted to Facebook even when set not to be
* Fixed: Don't display error for Featured Image when post is being submitted for review by a contributor

= 2.0.20 =
* Fixed: (again) Taking another shot at the shortcode-in-og:description-problem
* Fixed: Usability issues with the new Featured Image confirmation prompt
* Added: New global options for controlling from where the picture used by Facebook is sourced (featured image, global, or essentially-random)
* Change: Turned off the pinger. I wasn't using the data, and it upset some of my licensed customers. This will come back later in some more manageable form

= 2.0.19 =
* Fixed: The sharepress-mu.php file was all kinds of broken. Now works for the purpose of setting your license key, App Id, and App Secret in one place. Also, it's no longer part of the distribution. Sent only to people who buy the license.
* Fixed: Shortcodes appearing in og:description
* Fixed: Google+Blog wasn't posting to Facebook
* Fixed: Missing campaign tracking on some links in free version of the plugin
* Fixed: Default image size now 150x150
* Added: JS confirmation when no Featured Image is specified
* Added: Optional Twitter hash tag, customizeable for each post

= 2.0.18 =
* Fixed: If a Facebook connection error occurs on the Edit Post screen, the error message is hidden in the collapsed "Advanced" section of the meta box
* Fixed: Sometimes WP fires SharePress' one-minute cron job more than once a minute, resulting in multiple posts to Facebook
* Fixed: OG meta tags not turned on by default
* Fixed: A bunch of usability issues in the Settings screens
* Added: You can now elect to have your Posts shared on Twitter - no messaging customization yet: just post title and permalink

= 2.0.17 =
* Change: The Open Graph tags SharePress is allowed to insert can now be independently turned on and off, instead of in bulky groups
* Added: fb:app_id can be inserted automatically 
* Added: og:description can be inserted automatically

= 2.0.16 =
* Added: Log file viewer

= 2.0.15 =
* Fixed: Upgraded to Facebook PHP SDK 3.1.1
* Fixed: Now using oAuth 2.0 for Facebook login
* Fixed: No longer using JavaScript SDK in the admin, so domain name restrictions no longer matter (i.e., WordPress MU)
* Fixed: No longer dependent upon cURL, instead using WP_Http (thanks to [Curtiss Grymala](http://www.facebook.com/cgrymala))

= 2.0.14 =
* Fixed: Some minor issues related to calling array_merge without a defined array

= 2.0.13 =
* Fixed: The bug introduced by 2.0.12 - everything was considered RPC because I forgot to treat the constant like a constant...

= 2.0.12 =
* Added: Support for posting to Facebook with SharePress via XML-RPC. You can't configure what the Facebook post will say -- it's all defaults. But it didn't work at all before. This is progress.

= 2.0.11 =
* Fixed: I wasn't actually reading the user's per-post configuration when determining what image to identify in the og:image tag

= 2.0.10 =
* Fixed: Stop escaping unicode characters in og: meta data
* Added: You can now indicate that SharePress should only insert the og:image meta tag; this is useful for installations that already have plugins inserting the meta data, but not the og:image tag

= 2.0.9 =
* Fixed: Choice "No" in SharePress meta box was not being saved
* Fixed: Took some steps to reduce issues with license keys
* Added: a lot more logging statements, to help debug some problems with scheduled posts

= 2.0.8 =
* Change: Facebook changed the URL for the linter, so I've updated SharePress to use the new URL

= 2.0.7 =
* Change: Made it possible to reset the Facebook session from within the text of critical error messages

= 2.0.6 =
* Fixed: For sites that don't use a Page for the front door, the og:url meta was using the first permalink of the first post from the loop. This is wrong, it should be using the siteurl on the home page. This is now fixed.
* Fixed: Default piture was not being used on Posts that didn't set a Featured Image, but weren't set to allow Facebook to pick a picture

= 2.0.5 =
* Change: Renamed "Sharepress" to "SharePress"
* Added: Tutorial video for setting up SharePress and registering a Facebook Application

= 2.0.4 =
* Fixed: Major bug in setup process, prevented establishing API key and app secret in the database.

= 2.0.3 =
* Fixed: Featured Image feature of SharePress was not working unless the activate Theme supported post-thumbnails. 

= 2.0.2 =
* Fixed: Activating SharePress when the active theme did not use add_theme_support('post-thumbnails') would result in error messages being displayed in the Media management tool and other places

= 2.0.1 =
* Fixed: cron job is working again 
* Fixed: cron job is no longer dependent upon activation/deactivation 
* Fixed: if Facebook error occurs on Settings screen, wp_die is thrown with directions on how to get more information 
* Fixed: no inline error when user has no Pages to manage

= 2.0 =
* SharePress Pro is now available! If you want access to the pro features, you'll need to upgrade SharePress and then buy a license key. This release also fixes a number of bugs and usability issues, and 

= 1.0.10 =
* [Jen Russo](http://www.mauitime.com) reported that posts created via e-mailing to Posterous weren't triggering SharePress. There was a bug that prevented SharePress from firing in all cases other than the ones wherein the user was manually accessing the admin. This is now fixed.

= 1.0.9 =
* [Corey Brown](http://www.twitter.com/coreyweb) reported a bug in the "Publish to Facebook again" feature: not only was it not publishing again, but it was deleting the original meta data. This is now fixed.

= 1.0.8 =
* Ron Kochanowski reported a strange problem with brand new posts displaying the message "This post is older than the date on which SharePress was activated." in the editor. I couldn't fix the problem, so I eliminated the "feature." Problem solved.

= 1.0.7 =
* Added "sp" prefix to the Facebook classes, now "spFacebook" and "spSpFacebookApiException" - was creating namespace conflicts with other Facebook plugins (thanks [Ben Gillbanks](http://twitter.com/binarymoon) of [WPVOTE](http://www.wpvote.com))

= 1.0.6 =
* Addressing some inconsistencies in the way the plugin is named, and the way that name is used internally.

= 1.0.5 =
* Major typo in the readme. Sheesh.

= 1.0.4 =
* Discovered that the only message that displays within the WordPress plugin library search is under the Description header.

= 1.0.3 =
* Forgot to up the plugin version. Hope I don't make that mistake twice.

= 1.0.2 =
* Admin notices should not display for any users other than administrators
* Broad update to readme.txt

= 1.0.1 =
* Added links for learning more about SharePress Pro

= 1.0 =
* The first release!

== Upgrade Notice ==

= 2.2.11 =
Twitter retired v1.0 of their API. This version of SharePress is compatible with Twitter's API v1.1, but you may need to regenerate your Twitter App keys.

= 2.0.15 =
Critical bug fix release. Please upgrade before October 1. Also note that when you upgrade, you will need to run SharePress setup again.

= 2.0.6 =
Critical bug fix release. Please upgrade soon.

= 2.0.4 =
Critical bug fix release. Please upgrade soon.

= 2.0.3 =
Critical bug fix release. Please upgrade soon.

= 2.0.2 =
Critical bug release. Please upgrade soon.

= 2.0 =
SharePress Pro is now available! If you want access to the pro features, you'll need to upgrade SharePress and then buy a license key

= 1.0.6 =
Fixes a bug that results in breaking core JavaScript in the WordPress admin

= 1.0 =
Because it's the first version!