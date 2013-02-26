=== Easy Comment Uploads ===
Contributors: Tom Wright
Tags: comments, uploads, images, wpmu
Requires at least: 3.0.0
Tested up to: 3.1.0
Stable tag: 0.61

== Description ==

This plugin allow your visitors to attach images or other file to their comments as easily as possible. I originally designed it for http://langtreeshout.org . It also adds lightbox code for all inserted images. It has been tested for Wordpress and Wordpress Mu; if anything does not work please just email me at tom.tdw@gmail.com .

If you like it please rate it, give feedback or you can donate.

= Recent Changes =

**0.61**

 * Some small bug fixes which prevented some users uploading files.

**0.60**

 * Bulgarian translation by SiteGround ( http://www.siteground.com/ ).
 * French translation by M.HAROUY.
 * Now only file names instead of full urls are shown by default.
 * TinyMCEComments compatibility fixed.
 * NicEdit compatibility fixed.
 * Options reorganised and extended.
 * Manually blacklist file extensions.
 * Optionally allow only certain file extensions.
 * Set limits for uploads per hour based on ip or user for each class of users.
 * Various bug fixes.

**0.55**

 * Small compatibility imporvements.

**0.54**

 * Plugin compatibility improved.

**0.53**

 * Remaining theme compatibility issues finally fixed.

**0.52**

 * Allows only showing upload form on certain pages.

**0.51**

 * Slightly more failsafe plugin url function.
 * Separated CSS into separate file.

**0.50**

 * Ground-up rewrite of most of the plugin.
 * Removed temporary files.
 * No longer broken on Windows Server.
 * Can now be used in other plugins.
 * Uses the standard Wordpress upload directories.
 * Much improved nonce based security checks.
 * New option to limit size of uploaded files.
 * Much lighter design.

(Thanks to http://www.justin-klein.com/ for help and suggestions).

**0.31**

 * Temp files are now stored in /tmp .
 * Smart auto-insertion of new lines after auto-added links.
 * Fixes bug with reallowing all users to upload.

**0.30**

 * Much improved options page.
 * Options for choosing which users can upload comments.
 * Much nicer formatting for embedded images.
 * Pretty preview for uploaded images.
 * New, more "human friendly" naming scheme.
 * Images only option now transfered server side (so no spoofing).
 * Automated uninstall with cleanup (all file and comments will be saved).

**0.25**

Changes by Pascal de Vink ( www.finalist.com ):

 * Dutch translation.
 * Options page.
 * Filetype checking improvments.

**0.21**

 * be_BY translation thanks to ilyuha ( http://antsar.info/ ).
 * Filetype blacklist security fix.

**0.20**

 * ru_RU translation thanks to Fat Cow ( http://www.fatcow.com/ ).

**0.19**

 * pt_BR translation by Claudio Miranda ( http://www.claudius.com.br/ ).

**0.18**

 * Initial support for translations.
 * Improved theme compatibility.
 * General cleanup.

**0.17**

 * Fixed problems with image width in some themes.
 * Changed naming of uploads to prevent conflicts.

**0.15**

 * Ensure that any insecure scripts left over from old versions are removed.
 * Reduced file I/O (should boost performance).

**0.14**

 * Auto-adding the file links to comment (i.e. no copy and paste).
 * Much nice user experience.
 * More code cleanup.
 * Blacklist of dangerous file types (this was a serious security problem in old version, please upgrade ASAP).

**0.10**

 * Better support for non-image uploads.
 * Installation now much less hacky (if it did not work for you before it should now).
 * Upload directories combined.
 * General code cleanup.

For more updates about the plugin or to ask questions, follow me on Twitter: http://twitter.com/tomdwright

== Installation ==

Just add to /wp-content/plugins and activate or use the automatic plugin installer. If you want to use it in Wordpress Mu for all blogs just copy comment-uploads.php to /wp-content/mu-plugins and leave the rest in place or use the new activate site wide option for an much simpler installation.

== Frequently Asked Questions ==

= What license is this plugin available under? =

The GPLv3 of course :-). You can reuse it, hack it, redistribute it or do whatever else you like as long as you keep the source open under the same license. It is and will always be free for personal or commercial use but if you like it you can donate or pay me to work on the features you need. The plugin is supplied with no warranty whatsoever and any contributors will not be held responsible for any damages caused by its use.

= It does not work, what can I do? =

If you need help with the plugin then email me at tom.tdw@gmail.com and I will be more than willing to give any help I can.

= How can I help support the plugin's development? =

I develop the plugin in my free time whilst providing free support for it so if you enjoy using it and would like to support it then any donations would be gratefully received.

If you want to donate then you can do so securely using paypal:
(paypal link coming soon)

= Is it available in my language? =

Currently the plugin is only available in English and a few other languages but anyone can translate it using Wordpress's standard tools: http://codex.wordpress.org/Translating_WordPress . If you translate the plugin then please email me the PO files so that other users can benefit from them as well.

Alternatively you can translate the plugin online using an easy web interface on launchpad:
https://translations.edge.launchpad.net/easy-comment-uploads/trunk

= Is it secure? =

Currently the plugin blacklists unsafe filetypes and is bound by the global php security settings so it should be fairly safe to use. However as with any plugin there is a risk and if the security measures taken by your site are not adequate, then this risk is greatly increased. As always I recommend you keep backups and if you do discover security issues with the plugin please let me know so I can resolve them as soon as possible.

Since version 0.50, it includes new security features including checking the domain of the referrer and allowing you to set a filesize limit.

As with all plugins, new versions include security fixes and resolve other bugs so I always recommend running the latest stable version.

= How can I enable lightboxes =

If you install the wordpress lightbox plugin (http://wordpress.org/extend/plugins/lightbox-2/) then all uploaded images may be displayed using lightbox support.

= I have just thought of an amazing feature your plugin should have, what can I do? =

Good for you - send me and email or comment and if I like the idea, I will see whether my coding skills will stretch to making it. If you have a patch or want to contribute then even better - just contact me with a brief introduction and I will add you as a contributor to the plugin.
