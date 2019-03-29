=== Comment Attachment ===
Contributors: latorante
Donate link: http://donate.latorante.name/
Tags: comments, comment, image, attachment, images, files
Requires at least: 3.0
Tested up to: 4.9.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Stable tag: 1.5.8.1

Allow your visitors to attach files with their comments!

== Description ==

This plugin allows your visitors to attach files with their comments, such as documents and images. It uses only built in wordpress hooks, and provides you with multiple settings in you admin *(using native WP_Settings API)*. With these you can:

* Select if the upload field is before or after the default comment fields.
* Make attachment a required field.
* Select a label of upload field *(default is 'Upload Attachment')*
* Select a label of attachment in comment text *(default is 'Attachment:')*
* Select which file types are allowed to be attached.
* Select if attachment is visible in the the actual comment.
* Select if attachment should be attached to post your visitor comments on, or not.
* Select position of attachment in comment, either before the main comment, or after it.
* Decide whether attachment can be downloaded.
* Decide if the attachment image should be displayed in a comment and select image size (it automatically loads all image sizes set up in your wordpress installation and by your theme using 'add_image_size')
* Restrict file size of uploaded attachment.
* Try Wordpress 3.6^ experimental audio / video player.

All attachments are inserted in your main wordpress media gallery, and are attached (if set in settings) to current commented post. (if set in settings). Upon comment deletion the attachment in that comment is deleted as well (if set in settings).

If an error occurs, like required attachment, or visitor trying to upload not allowed file type, plugin uses native `wp_die()` to handle the error, which can play nicely, if you use some other plugin for handeling comment form errors in a different way, [like this one](http://wordpress.org/plugins/comment-form-inline-errors/ "Comment form inline errors").

To control the output, in your css, you can use these classes and id's. For form elements:

`.comment-form-attachment {}
.comment-form-attachment label .attachmentRules {}
.comment-form-attachment input#attachment {}`

and for inner comment elements:

`.attachmentFile {}
.attachmentFile p {}
.attachmentLink {}
.attachmentLink img {}`

It should be easy peasy for you to style it! :)

= Language translations thanks to:

* DE = =
* PT = Nomada (rodrigo)
* BR = Treed Box
* ES = Clara Roldan
* DK = Johan Michaelsen

Thank you all for contributing.

== Installation ==

1. Go to your admin area and select Plugins -> Add new from the menu.
2. Search for "Comment Attachment".
3. Click install.
4. Click activate.

== Screenshots ==

1. Settings page in wp admin. Settings > Discussion > Comment Attachment
2. Attachment field in comment form.
3. Attachments in comments with links and image thumbnails.
4. Wordpress 3.6.x video player
5. Wordpress 3.6.x audio player

== Upgrade Notice ==

= 1.5.8.1 =
* Removing name from description

= 1.5.8 =
* Text changes only

= 1.5.7 =
* Adding translations I've had for a while

= 1.5.5 =
* Wrong operator bugfix

= 1.5 =
* Tested on latest WordPress 4.4

= 1.3 =
* APK and rel="lightbox" on image attachments

= 1.2.1 =
* Experimental featured image feature removal, bug-fix.

== Changelog ==

= 1.5 =
* Tested on latest WordPress 4.4
* Added languages support (thanks to winnewoerp)
* Added German translation (thanks to winnewoerp)
* Added new plugin icon

= 1.3.5 =
* Added ability to delete attachment from wordpress admin.
* Attachment added to notification e-mail message

= 1.3 =
* Added new allowed file types (APK)
* Added rel="lightbox" to image links
* Better requirements check

= 1.2.1 =
* Experimental featured image feature removal, bug-fix.

= 1.2 =
* Categorized file types in the admin
* Added new allowed file types (RAR, ZIP, WMA, MP4, M4V, MOV, WMV, AVI, MPG, OGV, 3GP, 3G2, FLV, WEBM)
* Added experimental audio / video player, using native [video] and [audio] shortcode
* Added media gallery allowed mime types fix
* Added maxium file size info into comment form.

= 1.1 =
* Added new allowed file types (PPT, PPTX, PPS, PPSX, ODT, XLS, XLSX, MP3, M4A, OGG, WAV)
* Added max. file size settings.
* Added loads of non-official but used mime types.
* Added file extension check.
* Added option to change attachment title in comment text.

= 1.0 =
* Fixed small typos.
* Plugin released.
