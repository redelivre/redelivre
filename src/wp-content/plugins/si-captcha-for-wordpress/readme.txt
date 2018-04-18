=== SI CAPTCHA Anti-Spam ===
Contributors: fastsecure
Author URI: http://www.642weather.com/weather/scripts.php
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=KXJWLPPWZG83S
Tags: captcha, recaptcha, buddypress, bbpress, woocommerce, wpforo, multisite, jetpack, comment, comments, login, register, anti-spam, spam, security
Requires at least: 3.6.0
Tested up to: 4.8
Stable tag: 3.0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds Secure Image CAPTCHA on the forms for comments, login, registration, lost password, BuddyPress, bbPress, wpForo, and WooCommerce checkout.

== Description ==

Adds Secure Image CAPTCHA anti-spam to WordPress pages for comments, login, registration, lost password, BuddyPress register, bbPress register, wpForo register, bbPress New Topic and Reply to Topic Forms, Jetpack Contact Form, and WooCommerce checkout.
In order to post comments, login, or register, users will have to pass the CAPTCHA test. This prevents spam from automated bots, adds security, and is even compatible Akismet. Compatible with Multisite Network Activate. 
If you don't like image captcha and code entry, you can uninstall this plugin and try my new plugin [Fast Secure reCAPTCHA](https://wordpress.org/plugins/fast-secure-recaptcha/) 

= Help Keep This Plugin Free =

If you find this plugin useful to you, please consider [__making a small donation__](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=KXJWLPPWZG83S) to help contribute to my time invested and to further development. Thanks for your kind support! - [__Mike Challis__](http://profiles.wordpress.org/users/MikeChallis/)


Features:
--------
 * Secure Image CAPTCHA.
 * Optional setting to hide the Comments CAPTCHA from logged in users.
 * Enable or disable the CAPTCHA on any of the pages for comments, login, registration, lost password, BuddyPress register, bbPress register, wpForo Register, Jetpack Contact Form, and WooCommerce checkout.
 * Login form - WordPress, BuddyPress, bbPress, wpForo Forum, WooCommerce, WP Multisite
 * Lost Password form - WordPress, BuddyPress, bbPress, wpForo Forum, WooCommerce, WP Multisite. 
 * Register form - WordPress, BuddyPress, bbPress, wpForo Forum, WooCommerce, WP Multisite.
 * Comment form - WordPress, WP Multisite.  
 * Signup new site - WP Multisite.
 * Checkout form - WooCommerce.
 * Jetpack Contact Form.
 * bbPress New Topic, Reply to Topic Forms.
 * You can disable any of the forms you don't want CAPTCHA on.
 * Style however you need with CSS.
 * I18n language translation support.
 * Compatible with Akismet.
 * Compatible with Multisite Network Activate.
 * I18n language translation support. [See FAQ](http://wordpress.org/extend/plugins/si-captcha-for-wordpress/faq/).

Captcha Image Support:
---------------------
 * Open-source free PHP CAPTCHA library by www.phpcaptcha.org is included (customized version)
 * Abstract background with multi colored, angled, and transparent text
 * Arched lines through text
 * Refresh button to reload captcha if you cannot read it.


== Installation ==

= How to Install on WordPress =

1. Install automatically through the `Plugins`, `Add New` menu in WordPress, find in the Plugins directory, click Install, or upload the `si-captcha-for-wordpress.zip` file.

2. Activate the plugin through the `Plugins` menu in WordPress.

3. Configure on the settings page, be sure to select all the forms you want to protect.

4. Updates are automatic. Click on "Upgrade Automatically" if prompted from the admin menu. If you ever have to manually upgrade, simply deactivate, uninstall, and repeat the installation steps with the new version. 

= How to install on WordPress Multisite with Network Activate and Main site control of the settings =

1. Install the plugin from Network Admin menu then click Network Activate.

2. Go to the Main site dashboard and click on settings for this new plugin. 

3. Configure on the settings page, be sure to select all the forms you want to protect. All the settings configured here will be applied to all the sites. Other site admins cannot see or change the settings.


= How to install on WordPress Multisite with Network Activate and individual site control of the settings =

1. Install the plugin from Network Admin menu then click Network Activate.

2. Go to the Main site dashboard and click on settings for this new plugin. Configure on the settings page, be sure to select all the forms you want to protect. 

3. Check the setting: "Allow Multisite network activated sites to have individual SI CAPTCHA settings." Now each site admin can configure the settings on their dashboard SI CAPTCHA settings page, and be sure to select all the forms to protect.


== Screenshots ==

1. screenshot-1.png is the captcha on the comment form.

2. screenshot-2.png is the captcha on the registration form.

3. screenshot-3.png is the `Captcha options` tab on the `Admin Plugins` page.


== Configuration ==

After the plugin is activated, you can configure it by selecting the `SI Captcha options` tab on the `Admin Plugins` page.


== Usage ==

Once activated, a captcha image and captcha code entry is added to the comment and register forms. The Login form captcha is not enabled by default because it might be annoying to users. Only enable it if you are having spam problems related to bots automatically logging in.


== Frequently Asked Questions ==

= How does it work? =

Users users will have to pass the Secure Image CAPTCHA test. They are shown an image with a code, then they have to enter the code in the form field before the click submit. If the code does not match, the form will return "Incorrect CAPTCHA".

= What are spammers doing anyway? =

= Human spammers = 
They actually visit your form and fill it out including the CAPTCHA.

= Human or Spambot probes =
Sometimes contain content that does not make any sense (jibberish). Humans or Spam bots will try to target any forms that they discover. They first attempt an email header injection attack to use your web form to relay spam emails. This form is to prevent relaying email to other addresses. After failing that, they simply submit the form with a spammy URL or black hat SEO text with embedded HTML, hoping someone will be phished or click the link.

= Blackhat SEO spammers = 
Spamming blog comment forms, contact forms, Wikis, etc. By using randomly generated unique "words", they can then do a Google search to find websites where their content has been posted un-moderated. Then they can go back to these websites, identify if the links have been posted without the rel="nofollow" attribute (which would prevent them contributing to Google's algorithm), and if not they can post whatever spam links they like on those websites, in an effort to boost Google rankings for certain sites. Or worse, use it to post whatever content they want onto those websites, even embedded malware.

= Human-powered CAPTCHA solvers =
It is easy and cheap for someone to hire a person to enter this spam. Usually it can be done for about $0.75 for 1,000 or so form submissions. The spammer gives their employee a list of sites and what to paste in and they go at it. Not all of your spam (and other trash) will be computer generated - using CAPTCHA proxy or farm the bad guys can have real people spamming you. A CAPTCHA farm has many cheap laborers (India, far east, etc) solving them. CAPTCHA proxy is when they use a bot to fetch and serve your image to users of other sites, e.g. porn, games, etc. After the CAPTCHA is solved, they post spam to your form.

= Spammers have been able to bypass my CAPTCHA, what can I do? =

Make sure you have configured the settings page and enabled the CAPTCHA on all your forms.

The CAPTCHA will not show to logged in users posting comments if you have enabled this setting: 'No comment form CAPTCHA for logged in users'. Enable this setting if a logged in user is the spammer.

Check for a plugin conflict.
A plugin conflict can break the validation test so that the CAPTCHA is never checked.
Be sure to always test all the comments, login, registration, and lost password CAPTCHA forms after installing or updating themes or plugins. 

Troubleshoot plugin conflicts, see troubleshooting below.

Sometimes your site becomes targeted by a human spammer or a spam bot and human captcha solver. If the issue persists, try the following suggestions:

Try allowing only Registered users to post, and or moderating comments.
Read more about [Combating Comment Spam](http://codex.wordpress.org/Combating_Comment_Spam)

Filter Spam with Akismet - The [Akismet plugin](https://docs.akismet.com/getting-started/activate/) filters spam comments. Akismet should able to block most of or all spam that comes in. 

I made another plugin with Google No CAPTCHA reCAPTCHA that has realtime bot detection. You can uninstall this plugin and try my new plugin [Fast Secure reCAPTCHA](https://wordpress.org/plugins/fast-secure-recaptcha/) 


= How can I change the color of the CAPTCHA input field on the comment form? =
If you need to adjust the captcha input form colors, [See this FAQ](http://www.fastsecurecontactform.com/si-captcha-comment-form-css)


= Troubleshooting the CAPTCHA image or form field does not display, or it does not block the form properly =
Another plugin could be causing a conflict. 
Temporarily deactivate other plugins to see if the CAPTCHA starts working. 

Your theme could be missing the wp_head or wp_footer PHP tag. Your theme should be considered broken if the wp_head or wp_footer PHP tag is missing.

Do this as a test:
In Admin, click on Appearance, Themes. Temporarily activate your theme to one of the default default themes. 
It does not cause any harm to temporarily change the theme, test and then change back. Does it work properly with the default theme?
If it does then the theme you are using is the cause. 

Missing CAPTCHA image and input field on comment form?
You may have a theme that has an improperly coded comments.php

When diagnosing missing CAPTCHA field on comment form....

Make sure your theme has `<?php comment_form(); ?>`
inside `/wp-content/themes/[your_theme]/comments.php`. (look inside the Twenty Ten theme's comments.php for proper example.

Make sure that the theme comments.php file contains at least one or more of the standard hooks: 
`do_action ( 'comment_form_logged_in_after' );`
`do_action ( 'comment_form_after_fields' );` 
`do_action ( 'comment_form' );` 
If you didn't find one of these hooks, then put this string in the comment form: 
`<?php do_action( 'comment_form', $post->ID ); ?>` 

= The CAPTCHA and input field does not display on JetPack comments form =
If you have JetPack comments module enabled then captcha/recaptca/anti-spam plugins will not work on your comments form because the comments are then loaded in an iFrame from WordPress.com The solution is to disable the comments module in JetPack, then the CAPTCHA plugin will work correctly on your comments form.

= Troubleshooting if the CAPTCHA image itself is not being shown on the comment form: =

By default, a logged in user not see the CAPTCHA on the comment form. If you click "log out", go look and it should be there. Make sure you have configured the settings page and enabled the CAPTCHA on the comment form.

If the image is broken and you have the CAPTCHA entry box:

This can happen if a server has folder permission problem, or the WordPress address (URL)
or Blog address (URL) are set incorrectly in WP settings: Admin,  Settings,  General

[See FAQ page on fixing this problem](http://www.fastsecurecontactform.com/captcha-image-not-showing-si-captcha-anti-spam)


= The CAPTCHA refresh button does not work =

Your theme could be missing the wp_footer PHP tag. Your theme should be considered broken if the wp_footer PHP tag is missing.

All WordPress themes should always have `<?php wp_footer(); ?>` PHP tag just before the closing `</body>` tag of your theme's footer.php, or you will break many plugins which generally use this hook to reference JavaScript files. The solution: edit your theme's footer.php and make sure this tag is there. If it is missing, add it. Next, be sure to test that the CAPTCHA refresh button works, if it does not work and you have performed this step correctly, you could have some other cause.


= The CAPTCHA is not working and I cannot login at my login page =
This failure could have been caused by another plugin conflict with this one.
If you use CAPTCHA on the login form and ever get locked out due to CAPTCHA is broken, here is how to get back in:
FTP to your WordPress directory `/wp-content/plugins/`
Delete this folder: 
`si-captcha-for-wordpress`
This manually removes the plugin so you should be able to login again. 


= Is this plugin available in other languages? =

Yes. To use a translated version, you need to obtain or make the language file for it. 
At this point it would be useful to read [Installing WordPress in Your Language](http://codex.wordpress.org/Installing_WordPress_in_Your_Language "Installing WordPress in Your Language") from the Codex.
You will need an .mo file for this plugin that corresponds with the "WPLANG" setting in your wp-config.php file.
Translations are listed below -- if a translation for your language is available, all you need to do is place it in the `/wp-content/plugins/si-captcha-for-wordpress/languages` directory of your WordPress installation.
If one is not available, and you also speak good English, please consider doing a translation yourself (see the next question).


The following translations are included:

= Translators =
* Albanian (sq_AL) - Romeo Shuka
* Arabic (ar) - Amine Roukh
* Belorussian (by_BY) - Marcis Gasuns
* Chinese (zh_CN) - Awu
* Czech (cs_CZ) - Radovan
* Danish (da_DK) - Parry
* Dutch (nl_NL) - Robert Jan Lamers
* French (fr_FR) - BONALDI
* German (de_DE) - Sebastian Kreideweiss
* Greek (el) - Ioannis
* Hungarian (hu_HU) - Vil
* Indonesian (id_ID) - Masino Sinaga
* Italian (it_IT) - Gianni Diurno
* Japanese (ja) - Chestnut
* Lithuanian (lt_LT) - Vincent G
* Norwegian (nb_NO) - Roger Sylte
* Polish (pl_PL) - Tomasz
* Portuguese Brazil (pt_BR) - Newton Dan Faoro
* Portuguese Portugal (pt_PT) - PL Monteiro
* Romanian (ro_RO) - Laszlo SZOKE
* Russian (ru_RU) - Urvanov
* Serbian (sr_SR) - Milan Dinic
* Slovakian (sk_SK) - Marek Chochol
* Spanish (en_ES) - zinedine
* Swedish (sv_SE) - Benct
* Traditional Chinese, Taiwan Language (zh_TW) - Cjh
* Turkish (tr_TR) - Burak Yavuz
* More are needed... Please help translate.


= Can I provide a new translation? =

Yes please. 
Please read [How to translate SI Captcha Anti-Spam for WordPress](http://www.fastsecurecontactform.com/translate-si-captcha-anti-spam) 

= Can I update a translation? =

Yes please. 
Please read [How to update a translation of SI Captcha Anti-Spam for WordPress](http://www.fastsecurecontactform.com/update-translation-si-captcha-anti-spam) 


== Changelog ==

= 3.0.3 =
- Removed versions 3.0.1 and 3.0.2 for malicious code. This version is identical to 3.0.0.20.

= 3.0.0.20 =
* (20 Jun 2017) - Fix readme

= 3.0.0.19 =
* (05 Jun 2017) - Fix duplicate si_captcha_code ID.

= 3.0.0.18 =
* (05 Jun 2017) - Fix possible empty needle error.

= 3.0.0.17 =
* (13 May 2017) - Fix possible Catchable fatal error on WooCommerce password reset.

= 3.0.0.16 =
* (09 May 2017) - Fix typo in code causing validation error on WooCommerce checkout. Sorry for any inconvenience.

= 3.0.0.15 =
* (04 May 2017) - Revert changes to last update to fix missing CAPTCHA on JetPack Contact form.

= 3.0.0.14 =
* (04 May 2017) - Fix rare but possible double CAPTCHA on JetPack Contact form.

= 3.0.0.13 =
* (02 May 2017) - Fix "You have selected an incorrect CAPTCHA value" error on WooCommerce checkout page if "Create an account" is checked and Enable CAPTCHA on WooCommerce checkout is disabled.

= 3.0.0.12 =
* (21 Apr 2017) - Fix "You have selected an incorrect CAPTCHA value" error on WooCommerce checkout page if "Create an account" is checked.

= 3.0.0.11 =
* (20 Apr 2017) - Fix WooCommerce /my-account/lost-password/ page validation error causes cannot click "Reset password".

= 3.0.0.10 =
* (10 Apr 2017) - Fix double CAPTCHA WooCommerce register My Account forms WooCommerce 2.x

= 3.0.0.9 =
* (10 Apr 2017) - Fix CAPTCHA did not work on WooCommerce register My Account forms since WooCommerce 3.

= 3.0.0.8 =
* (21 Mar 2017) - Fixed error caused by uninitialized value si_captcha_login on line 764.

= 3.0.0.7 =
* (03 Mar 2017) - Fixed CAPTCHA not loading on register form on BuddyPress when Extended Profiles is disabled.
- Fixed CAPTCHA not loading on JetPack Contact Form in a widget.

= 3.0.0.6 =
* (27 Feb 2017) - Fixed WooCommerce checkout CAPTCHA was still on the form when not enabled.

= 3.0.0.5 =
* (25 Feb 2017) - Fixed bbPress Register form did not have the CAPTCHA.
- Added support for bbPress New Topic and Reply to Topic Forms.

= 3.0.0.4 =
* (18 Feb 2017) - Added CAPTCHA for Jetpack Contact Form.
- Fix CAPTCHA not showing on Woocommerce /my-account/ page when "My account page" is enabled in Woocommerce settings.
- Fix CAPTCHA missing on comment form on some old themes.
- Improved text on enable forms settings.
- Fix some strings that could not be internationalized.
- Update French (fr_FR) - BONALDI (thank you).
- Update Russian (ru_RU) - Urvanov (thank yuu).

= 3.0.0.3 =
* (12 Feb 2017) - Fixed reCAPTCHA on wpForo Registration page was not working unless comment form was also checked.

= 3.0.0.2 =
* (12 Feb 2017) - Added CAPTCHA for wpForo Forum Registration page. (you can enable/disable it on the settings page).

= 3.0.0.1 =
- (12 Feb 2017) - removed aria setting.
- fixed broken links at top of settings page.

= 3.0.0.0 =
- (12 Feb 2017) - all new codebase, this is a major update.
- Make compatible with WooCommerce, BuddyPress, and Multisite Network Activate.
- Test and fix all forms on the most current WordPress.
- Remove some of the difficult style features.
- Remove some obsolete settings.
- If you don't like image captcha and code entry, you can uninstall this plugin and try my new plugin [Fast Secure reCAPTCHA](https://wordpress.org/plugins/fast-secure-recaptcha/) 
- Language files need updating, if you want to help, please read [How to update a translation of SI Captcha Anti-Spam](http://www.fastsecurecontactform.com/update-translation-si-captcha-anti-spam).

= 2.7.7.8 =
- (22 Oct 2016) - requires at least WP 3.0
- fix deprecated errors.
- Change alternative text "CAPTCHA image" and "Refresh image" to "CAPTCHA" and "Refresh".

= 2.7.7.7 =
- (13 Feb 2016) - Fix: PHP Fatal error: Class 'securimage_si' not found 

= 2.7.7.6 =
- (12 Feb 2016) - Fix: Captcha did not show on some PHP7 installations

= 2.7.7.5 =
- (22 Dec 2014) - akismet 3.xx compatible.
- Bug fix: disabling registration captcha also disabled lost password captcha.
- added support for the wp_login_form wordpress function used in some themes. Third party plugins that use wp_login_form need to allow SI CAPTCHA fields to pass through any login form submission handling [see example for sidebar-login plugin](https://wordpress.org/support/topic/allow-inserted-captcha-or-other-fields)

= 2.7.7.4 =
- (15 May 2014) - Removed themefuse ad (their site was flagged by Google as possibly infected).
- tested for WP 3.9.1

= 2.7.7.3 =
- (18 Nov 2013) - Update Turkish Language.

= 2.7.7.2 =
- (19 Oct 2013) - improve readability of CAPTCHA image.

= 2.7.7.1 =
- (18 Jul 2013) - added captcha font randomization.
- fix captcha gd font fallback.

= 2.7.7 =
- (13 Jul 2013) - Bug fixes and code cleanup.
- Update Turkish language (tr_TR) - Translated by [Burak Yavuz] 

= 2.7.6.4 =
- (05 Jan 2013) - added new setting "Enable honeypot spambot trap". Enables empty field token honyepot trap. For best results, do not enable unless you have a spam bot problem. Does not stop human spammers.
- fixed some bugs.

= 2.7.6.2 =
- (23 Dec 2012) - fixed some bugs with login redirect.

= 2.7.6.1 =
- (17 Dec 2012) - fixed some bugs.
- cleaned up some code.
- added settings to change all the error messages.

= 2.7.6 =
- (15 Dec 2012) - Tested compatible with WP 3.5
- improved spam bot detection.
- Removed CAPTCHA test pages.
- Fix possible error: preg_match() expects parameter to be string.
- Other optimizations. 
- Updated Dutch language (nl_NL)  - Translated by [Paul Backus](http://backups.nl/)
- Updated Italian language (it_IT) - Translated by [Gianni Diurno](http://gidibao.net/ "Gianni Diurno")
- Added Lithuanian (lt_LT) - Translated by [Vincent G](http://www.Host1Free.com)

= 2.7.5 =
- (07 Dec 2011) - WP 3.3 compatibility fix for wp_enqueue_script was called incorrectly.
- Remove more leftover audio code.
- CAPTCHA code cache file performance improvement.

= 2.7.4 =
- (18 Jul 2011) - Fixed bug in CAPTCHA code reset reported by USSliberty, please update now for better spam protection.
- Fix CAPTCHA position on some themes like Suffusion.

= 2.7.3 =
- (05 Jul 2011) - Tested / fixed to be compatible with WP 3.2
- Fixed to be compatible with SFC Comments plugin.
- Fixed error: Undefined variable: securimage_url 
- CAPTCHA audio feature removed.
- Updated Italian language (it_IT) - Translated by [Gianni Diurno](http://gidibao.net/ "Gianni Diurno")

= 2.7.2 =
- (02 Jun 2011) - CAPTCHA Audio feature is disabled by Mike Challis until further notice because a proof of concept code CAPTCHA solving exploit was released - Security Advisory - SOS-11-007. CAPTCHA image is not involved.
- Fix javascript error when CAPTCHA audio is disabled.
- Fixed missing width/height attributes for CAPTCHA images.

= 2.7.1 =
- (26 Apr 2011) - Fix for users of the MU domain mapping plugin.

= 2.7 =
- (19 Feb 2011) - Modified the setting "CAPTCHA input label position on the comment form:" with more options for input and label positions for matching themes.
- Added new setting in the "Text Labels:" to allow you to change the required field indicator. The default is " *", but you can now change it to "(required)" or anything you want. 
- Added lost password CAPTCHA
- Fixed Valid HTML for BuddyPress
- Fixed sidebar logon for BuddyPress

= 2.6.5 =
- (12 Feb 2011) - New feature: New settings for "Internal Style Sheet CSS" or "External Style Sheet CSS". If you need to learn how to adjust the captcha form colors, [See FAQ](http://www.fastsecurecontactform.com/si-captcha-comment-form-css)
- Fix: one CAPTCHA random position always has to be a number so that a 4 letter swear word could never appear. 
- Improvement: javascript is only loaded on pages when it is conditionally needed.
- Updated Romanian (ro_RO) - Translated by [Anunturi Jibo](http://www.jibo.ro)
- Requires at least WordPress: 2.9

= 2.6.4 =
- (19 Jan 2011) - Added more settings for setting CAPTCHA input field and label CAPTCHA input field CSS. These settings can be used to adjust the CAPTCHA input field to match your theme. [See FAQ Page](http://www.fastsecurecontactform.com/si-captcha-comment-form-css)
- Added new setting: "CAPTCHA input label position on the comment form:" Changes position of the CAPTCHA input labels on the comment form. Some themes have different label positions on the comment form. On suffusion, set it to "right".
- Added Portuguese Portugal (pt_PT) - Translated by [PL Monteiro](http://thepatientcapacitor.com/)
- Added Serbian (sr_SR) - Translated by [Milan Dinic]
- Updated Spanish (en_ES) - Translated by [zinedine](http://www.informacioniphone.com/)
- Updated Romanian (ro_RO) - Translated by [Anunturi Jibo](http://www.jibo.ro/)

= 2.6.3.2 =
- (17 Dec 2010) - Rename CAPTCHA font files all lower case.
- Small changes to admin page.

= 2.6.3.1 =
- (19 Nov 2010) - Fixed WP 3.0 multi-site admin settings page 404 (hopefully).
- Updated Japanese

= 2.6.3 =
- (28 Sep 2010) - Improved transparent audio and refresh images for the CAPTCHA
- Added Japanese (ja) - Translated by [Chestnut](http://staff.blog.bng.net/)
- Added Persian Iran (fa_IR) - Translated by [najeekurd](http://www.najeekurd.net/)

= 2.6.2 =
- (19 Aug 2010) - Fixed error "WP_Error as array" recorded in error log when on register page. 
- Added Akismet spam prevention status to the contact form settings page, so you can know if Akismet is protecting or not.
- Added automatic SSL support for the CAPTCHA URL.
- Added download count and star rating on admin options page. 
- cleaned up options page.

= 2.6.1 =
- (11 Aug 2010) - Fixed critical error that broke comment replies from admin menu with "CAPTCHA ERROR".

= 2.6 =
- (09 Aug 2010) - PHP Sessions are no longer required for the CAPTCHA. The new method uses temporary files to store the CAPTCHA codes until validation. PHP sessions can still be reactivated by unchecking the setting: "Use CAPTCHA without PHP session".
- Added rel="nofollow" tag to CAPTCHA Audio and CAPTCHA Refresh links.
- Removed CAPTCHA WAV sound files, included mp3 ones take up 500k less space.
- Improved the CAPTCHA test page. 
- Added captcha-temp directory permission check to alert the admin if there is a problem. This check is on the admin settings page, the captcha test page, and when posting the captcha.
- Added more help notes to the admin settings page.

= 2.5.4 =
- (25 Jul 2010) - Added compatibility for WP 3.0 feature: "multisite user or blog marked as spammer".
- Fixed rare problem on some servers, CAPTCHA image had missing letters.

= 2.5.3 =
- (23 Jun 2010) - Fix placement of CAPTCHA on comment form.

= 2.5.2 =
- (15 May 2010) - Made WP3 Compatible.

= 2.5.1 =
- (11 May 2010) - Added option to disable audio.
- Fixed file path issue when installed in mu-plugins folder
- Updated Italian language (it_IT) - Translated by [Gianni Diurno](http://gidibao.net/ "Gianni Diurno")

= 2.5 =
- (23 Apr 2010) - Updated for latest version of buddypress 1.2.3 compatibility.
- Added setting to make the CAPTCHA image smaller.
- Fixed so multiple forms can be on the same page. 
- Split code into 2 smaller files for better performance.
- Updated Danish (da_DK) 

= 2.2.9 =
- (16 Feb 2010) - Fixed XMLRPC logins did not work when "Enable CAPTCHA on the login form" was enabled.

= 2.2.8 =
- (14 Jan 2010) - Added Dutch (nl_NL) - Translated by [Robert Jan Lamers](http://www.salek.nl/)

= 2.2.7 =
- (31 Dec 2009) - New setting for a few people who had problems with the text transparency "Disable CAPTCHA transparent text (only if captcha text is missing on the image, try this)". 
- Added Slovakian (sk_SK) - Translated by [Marek Chochol]
- Updated Arabic (ar) - Translated by [Amine Roukh](http://amine27.zici.fr/)

= 2.2.6 =
- (16 Dec 2009) - Added SSL compatibility.
- Added Hungarian (hu_HU) - Translated by [Vil]

= 2.2.5 =
- (06 Dec 2009) - More improvements for CAPTCHA images and fonts.

= 2.2.4 =
- (30 Nov 2009) - Fix blank CAPTCHA text issue some users were having.
- Added CAPTCHA difficulty level setting on the settings page (Low, Medium, Or High).
- Added Indonesian (id_ID) - Translated by [Masino Sinaga](http://www.openscriptsolution.com).
- Added Romanian (ro_RO) - Translated by [Laszlo SZOKE](http://www.naturaumana.ro).

= 2.2.3 =
- (23 Nov 2009) - Fix completely broke CAPTCHA, sorry about that

= 2.2.2 =
- (23 Nov 2009) - Added 5 random CAPTCHA fonts
- Fixed fail over to GD Fonts on the CAPTCHA when TTF Fonts are not enabled in PHP (it was broken)

= 2.2.1 =
- (21 Nov 2009) - Fixed Flash audio was not working.

= 2.2 =
- (20 Nov 2009) - Updated to SecureImage CAPTCHA library version 2.0
- New CAPTCHA features include: increased CAPTCHA difficulty using mathematical distortion, streaming MP3 audio of CAPTCHA code using Flash, random audio distortion, better distortion lines, random backgrounds and more.
- Other minor fixes.

= 2.1.1 =
- (10 Nov 2009) - Fix style and input alignments.

= 2.1 =
- (03 Nov 2009) - Fix for settings not being deleted when plugin is deleted from admin page.

= 2.0.9 =
- (30 Oct 2009) - Fixed issue on some sites with blank css fields that caused image misalignment.

= 2.0.8 =
- (29 Oct 2009) - Added new setting in advanced options: "CSS style for CAPTCHA div".

= 2.0.7 =
- (21 Oct 2009) - Added Chinese (zh_CN) - Translated by [Awu](http://www.awuit.cn/) 

= 2.0.6 =
- (13 Oct 2009) - Fixed array_merge error on WPMU, Buddypress.
- Added Czech (cs_CZ) - Translated by [Radovan](http://algymsa.cz)

= 2.0.5 =
- (09 Oct 2009) - Added Albanian (sq_AL) - Translated by [Romeo Shuka](http://www.romeolab.com)

= 2.0.4 =
- (03 Oct 2009) - Fixed session error on Buddypress versions.

= 2.0.3 =
- (01 Oct 2009) - Renamed to SI CAPTCHA Anti-Spam

= 2.0.2 =
- (30 Sep 2009) - Fixed settings were deleted at deactivation. Settings are now only deleted at uninstall.

= 2.0.1 =
- (25 Sep 2009) - BuddyPress 1.1 CSS fixes for the CAPTCHA position on the regstration form.

= 2.0 =
- (25 Sep 2009) - Added full WPMU and BuddyPress compatibility. WPMU and BuddyPress users can now protect comment form, registration, and login from spam.
- Added login form CAPTCHA. The Login form captcha is not enabled by default because it might be annoying to users. Only enable it if you are having spam problems related to bots automatically logging in.
- New feature: An "advanced options" section to the options page. Some people wanted to change the text labels for the CAPTCHA and code input field.
These advanced options fields can be filled in to override the standard included text labels.
- Added new advanced options for editing inline CSS style of captcha image, audio image, and reload image.
- Supports BuddyPress 1.0.3 and 1.1 
- Minor code cleanup.

= 1.8 =
- (15 Sep 2009) - Plugin options are now stored in a single database row instead of many. (and it will auto migrate/cleanup old options database rows).
- Language files are now stored in the `si-captcha-for-wordpress/languages` folder.
- Options are now deleted when this plugin is deleted.
- Added proper nonce protection to options forms.

= 1.7.12 =
- (08 Sep 2009) - Fixed redirect/logout problem on admin menu reported by a user.

= 1.7.11 =
- (03 Sep 2009) Updated German Language (de_DE) - Translated by [Sebastian Kreideweiss](http://sebastian.kreideweiss.info/)

= 1.7.10 =
- (02 Sep 2009) Updated Traditional Chinese, Taiwan Language (zh_TW) - Translated by [Cjh]

= 1.7.9 =
- (31 Aug 2009) Added more diagnostic test scripts: a Cookie Test, Captcha test, and a PHP Requirements Test.
Click on the "Test if your PHP installation will support the CAPTCHA" link on the Options page.
or open this URL in your web browser to run the test:
`/wp-content/plugins/si-captcha-for-wordpress/captcha-secureimage/test/index.php`
- Updated Italian language (it_IT) - Translated by [Gianni Diurno](http://gidibao.net/ "Gianni Diurno")

= 1.7.8 =
- (31 Aug 2009) Improved cookie error

= 1.7.7 =
- (30 Aug 2009) Added a `cookie_test.php` to help diagnose if a web browser has cookies disabled. (see the FAQ) 

= 1.7.6 =
- (29 Aug 2009) Added this script to test if your PHP installation will support the CAPTCHA:
Click on the "Test if your PHP installation will support the CAPTCHA" link on the Options page.
or open this URL in your web browser to run the test:
`/wp-content/plugins/si-captcha-for-wordpress/captcha-secureimage/test/index.php`

= 1.7.5 =
- (28 Aug 2009) Added Arabic Language (ar) - Translated by [Amine Roukh](http://amine27.zici.fr/)
- CAPTCHA fix - Added Automatic fail over from TTF Fonts to GD Fonts if the PHP installation is configured without "--with-ttf".
  Some users were reporting there was no error indicating this TTF Fonts not supported condition and the captcha was not working.

= 1.7.4 =
- (28 Aug 2009) Updated Italian language (it_IT) - Translated by [Gianni Diurno](http://gidibao.net/ "Gianni Diurno")

= 1.7.3 =
- (28 Aug 2009) Updated Italian language (it_IT) - Translated by [Gianni Diurno](http://gidibao.net/ "Gianni Diurno")

= 1.7.2 =
- (28 Aug 2009) fix options permission bug introduced by last update, sorry

= 1.7.1 =
- (27 Aug 2009) added settings link to the plugin action links

= 1.7 =
- (26 Aug 2009) Added error code for when the user has cookies disabled (the CAPTCHA requires cookies)
- added setting to enable aria-required form tags for screen readers(disabled by default)
- added a donate button on the options page. If you find this plugin useful to you, please consider making a small donation to help contribute to further development. Thanks for your kind support! - Mike Challis

= 1.6.9 =
- (03 Aug 2009) Added Greek Language (el) - Translated by [Ioannis](http://www.jbaron.gr/)

= 1.6.8 =
- (29 Jul 2009) Added Polish Language (pl_PL) - Translated by [Tomasz](http://www.ziolczynski.pl/)

= 1.6.7 = 
- (12 Jun 2009) WP 2.8 Compatible

= 1.6.6 = 
- (10 Jun 2009) Updated Russian Language (ru_RU) - Translated by [Neponyatka](http://www.free-lance.ru/users/neponyatka)

= 1.6.5 = 
- (09 Jun 2009) Added Traditional Chinese, Taiwan Language (zh_TW) - Translated by [Cjh]

= 1.6.4 = 
- (15 May 2009) Added Swedish Language (sv_SE) - Translated by [Benct]

= 1.6.3 =
- (10 May 2009) Added Russian Language (ru_RU) - Translated by [Fat Cow](http://www.fatcow.com/)

= 1.6.2 =
- (05 May 2009) Added Spanish Language (en_ES) - Translated by [LoPsT](http://www.lopst.com/)

= 1.6.1 =
- (06 Apr 2009) Added Belorussian Language (by_BY) - Translated by [Marcis Gasuns](http://www.comfi.com/)
- Fixed audio CAPTCHA link URL, it did not work properly on Safari 3.2.1 (Mac OS X 10.5.6).
- Note: the proper way the audio CAPTCHA is supposed to work is like this: a dialog pops up, You have chosen to open:
secureimage.wav What should (Firefox, Safari, IE, etc.) do with this file? Open with: (Choose) OR Save File. Be sure to select open, then it will play in WMP, Quicktime, Itunes, etc.

= 1.6 =
- (23 Mar 2009) Added new option on configuration page: You can set a CSS class name for CAPTCHA input field on the comment form: 
(Enter a CSS class name only if your theme uses one for comment text inputs. Default is blank for none.)

= 1.5.4 =
- (19 Mar 2009) Updated Danish Language (da_DK) - Translated by [Parry](http://www.detheltnyestore.dk/)

= 1.5.3 =
- (12 Mar 2009) Added German Language (de_DE) - Translated by [Sebastian Kreideweiss](http://sebastian.kreideweiss.info/)
- Updated Danish Language (da_DK) - Translated by [Parry](http://www.detheltnyestore.dk/)

= 1.5.2 =
- (24 Feb 2009) Added Danish Language (da_DK) - Translated by [Parry](http://www.detheltnyestore.dk/)

= 1.5.1 =
- (11 Feb 2009) Added Portuguese_brazil Language (pt_BR) - Translated by [Newton Dan Faoro]

= 1.5 =
- (22 Jan 2009) Added fix for compatibility with WP Wall plugin. This does NOT add CAPTCHA to WP Wall plugin, it just prevents the "Error: You did not enter a Captcha phrase." when submitting a WP Wall comment.
- Added Norwegian language (nb_NO) - Translated by [Roger Sylte](http://roger.inro.net/)

= 1.4 = 
- (04 Jan 2009) Added Turkish language (tr_TR) - Translated by [Volkan](http://www.kirpininyeri.com/)

= 1.3.3 =
-  (02 Jan 2009) Fixed a missing "Refresh Image" language variable

= 1.3.2 =
-  (19 Dec 2008) Added WAI ARIA property aria-required to captcha input form for more accessibility

= 1.3.1 =
- (17 Dec 2008) Changed screenshots to WP 2.7
- Better detection of GD and a few misc. adjustments

= 1.3 =
- (14 Dec 2008) Added language translation to the permissions drop down select on the options admin page, thanks Pierre
- Added French language (fr_FR) - Translated by [Pierre Sudarovich](http://pierre.sudarovich.free.fr/)

= 1.2.1 =
- (23 Nov 2008) Fixed compatibility with custom `WP_PLUGIN_DIR` installations

= 1.2 =
- (23 Nov 2008) Fixed install path from `si-captcha` to `si-captcha-for-wordpress` so automatic update works correctly.

= 1.1.1 =
- (22 Nov 2008) Added Italian language (it_IT) - Translated by [Gianni Diurno](http://gidibao.net/ "Gianni Diurno")

= 1.1 =
- (21 Nov 2008) Added I18n language translation feature

= 1.0 =
- (21 Aug 2008) Initial Release



