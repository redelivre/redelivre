=== Simple Dropbox Upload ===
Contributors: hiphopsmurf
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=K6XUBZSU8RWR2
Tags: simple, dropbox, upload, integration, api, form, file, photos, shortcode, widget
Requires at least: 3.3.0
Tested up to: 3.5.1
Stable tag: 1.8.8.2

Inserts an upload form for visitors to upload files to you Dropbox account without the need of a Dropbox developer account.

== Description ==

This plugin lets you insert an upload form on your pages or in a post so visitors can upload files to your Dropbox account.

== Installation ==

1. Upload `simple-dropbox-upload-form` to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress admin.
1. Goto Simple Dropbox in the WordPress admin.
1. Enter the file extensions of files you wish to be allowed for upload and save settings.
1. Click the 'Activate' button located at the bottom of the page and follow the prompts.
1. Place `[simple-wp-dropbox]` in a page, post or widget.

== Requirements ==

* WordPress 3.3.0 or higher
* PHP 5.0 or higher
* The wp-content/uploads directory needs to be writable by the plugin.  This is likely already the case as WordPress stores your media and various other uploads here.

== Usage ==

1. Go to Site Admin > Simple Dropbox
1. (Optional)Enter the folder path you would like to save the files to on Dropbox.
1. (Optional) Change the temporary path for files uploaded to your server before being uploaded to Dropbox.
1. (Required) Enter the file extensions without periods for the files you want to allow users to upload separated by one space.
1. (Optional) Enter a message you want displayed after the user uploads a file.
1. (Optional) Choose a color for the message you want displayed after the user uploads a file.
1. Choose whether or not to display upload form again after the first file has been uploaded to Dropbox.
1. Choose whether or not to delete the file located on your server after it has been uploaded to Dropbox.
1. Click Save options.
1. If you have already authorized this plugin to use your Dropbox account you can skip to step 17
1. Click the Authorize button at the bottom of the screen.
1. Click Continue to be taken to Dropbox.
1. Once at Dropbox Click the Allow button so this plugin can link with your Dropbox account.
1. Go to Site Admin > Simple Dropbox
1. Click the Confirm button located at the bottom of the page to confirm your Dropbox account.
1. You should see the email address used with your Dropbox account. If you don't, Reset your settings and start over.
1. Click Finish.
1. Create a Page, Post or Widget to insert the shortcode into.
1. Insert **[simple-wp-dropbox]** where you would like the form to display.
1. Click Save or Publish.
1. Visit the location to confirm everything is working properly.

== To-do list ==
* Multiple file upload
* Add ability to append uploaders username to file name/folder path
* Add ability to control file upload size
* Add ability to limit the number of submissions per user/day
* Restyle admin interface (Done|)
* Change database structure (Done|)

== Frequently Asked Questions ==

= I can no longer upload to DropBox =

If you have a version before 1.8.8.2 this is due to someone finding a security hole. To protect your account, I kicked everyone out.
You will have to reset your settings and Authorize this plugin with DropBox again. Sorry!

= I updated to 1.7.0 and my settings got reset =

This is due to a bug that was found in the database update function.

= I updated to 1.5.0 and my settings got erased =

This is because I got approval from dropbox for root level access. What does this mean for you? You need to reauthorize your account.

= Is this your first Wordpress plugin =

Yes so I make no promises that it will work for you.

= I am on a VPS or Dedicated server and this plugin still won't play nice =

You have a few options:

1. Enable `ini_set` in your php.ini file (Look for disabled_functions).

1. Enable `set_include_path` in your php.ini file (Look for disabled_functions).

1. Add the `Net::OAuth` module to your Perl installation.

== Screenshots ==

1. Admin Panel before activation.

2. Step 1 of dropbox authorization.

3. Step 2 of dropbox authorization.

4. Step 3 of dropbox authorization.

5. Before a file is uploaded by user.

6. After a file is uploaded by user.

== Changelog ==

= 1.8.8.2 =
* Changed DropBox API Keys

= 1.8.8.1 =
* Security bug fixed

= 1.8.8 =
* Fixed file extensions vanishing
* A few other code tweeks

= 1.8.7 =
* Skipped

= 1.8.6 =
* Fixed wpdb->prepare bug
* Enabled multiple file upload
* Added upload progress bar
* Fixed a few other bugs

= 1.8.5 =
* Fixed CURL issue and re-enabled the option to use it.

= 1.8.4 =
* Disabled CURL since it stopped working. My guess, a result of the security changes Dropbox has been working on.

= 1.8.3 =
* Minor bug fixes.

= 1.8.2 =
* Style sheet lost in upload to wordpress.

= 1.8.1 =
* Fixed authorization thickbox.

= 1.8.0 =
* Admin panel facelift.
* Added color options for thank you message.
* Added CURL support.
* Other bug fixes.

= 1.7.0 =
* Major bug fix with update function.
* Renamed database keys to make plugin unique.

= 1.6.0 =
* Fixed bug with form showing after upload.

= 1.5.3 =
* Typo correction in FAQ.

= 1.5.2 =
* Corrected link to stylesheet.

= 1.5.1 =
* Cleaned up code a little.
* Fixed some grammar.

= 1.5.0 =
* Switched dropbox access from app folder access to root access.

= 1.4.0 =
* Added extra options and the ability to change the thank you message.

= 1.3.1 =
* Made a few minor tweeks to make compatible with more hosts.

= 1.3.0 =
* Added server/host error checking.
* Updated file upload error checking.

= 1.2.0 =
* Changed PEAR include method.

= 1.1.2 =
* Fixed a few typos.

= 1.1.1 =
* Fixed bug that prevented files with spaces in the name from being uploaded.

= 1.1.0 =
* Switched OAuth Auth method for servers that don't have PHP OAuth.

= 1.0.0 =
* Dropbox approved my API request.

= 0.5.0 =
* Initial release.

== Upgrade Notice ==

= 1.8.8.2 =
IMPORTANT SECURITY UPDATE.

= 1.8.8.1 =
IMPORTANT SECURITY UPDATE.

= 1.8.6 =
Added upload progress indicator and multiple file upload.

= 1.8.5 =
CURL is working again.

= 1.8.4 =
Fixes errors with curl not working.

= 1.7.0 =
All Simple Dropbox Plugin Settings Will Be RESET for versions less then 1.7