=== Page Builder by SiteOrigin ===
Tags: page builder, responsive, widget, widgets, builder, page, admin, gallery, content, cms, pages, post, css, layout, grid
Requires at least: 4.0
Tested up to: 4.5
Stable tag: 2.4.6
Build time: 2016-04-13T12:28:00+02:00
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html
Donate link: http://siteorigin.com/page-builder/#donate
Contributors: gpriday, braam-genis

Build responsive page layouts using the widgets you know and love using this simple drag and drop page builder.

== Description ==

[vimeo https://vimeo.com/114529361]

Page Builder by SiteOrigin is the most popular page creation plugin for WordPress. It makes it easy to create responsive column based content, using the widgets you know and love. Your content will accurately adapt to all mobile devices, ensuring your site is mobile-ready. Read more on [SiteOrigin](https://siteorigin.com/page-builder/).

We've created an intuitive interface that looks just like WordPress itself. It's easy to learn, so you'll be building beautiful, responsive content in no time.

Page Builder works with standard WordPress widgets, so you'll always find the widget you need. We've created the SiteOrigin Widgets Bundle to give you all the most common widgets, and with a world of plugins out there, you'll always find the widget you need.

= It works with your theme. =

Page Builder gives you complete freedom to choose any WordPress theme you like. It's not a commitment to a single theme or theme developer. The advantage is that you're free to change themes as often as you like. Your content will always come along with you.

We've also made some fantastic [free themes](https://siteorigin.com/theme/) that work well with Page Builder.

= No coding required. =

Page Builder's simple drag and drop interface means you'll never need to write a single line of code. Page Builder generates all the highly efficient code for you.

We don't limit you with a set of pre-defined row layouts. Page Builder gives you complete flexibility. You can choose the exact number of columns for each row and the precise weight of each column - down to the decimal point. This flexibility is all possible using our convenient row builder. And, if you're not sure what you like, the Row Builder will guide you towards beautifully proportioned content using advanced ratios.

= Live Editing. =

Page Builder supports live editing. This tool lets you see your content and edit widgets in real-time. It's the fastest way to adjust your content quickly and easily.

= History Browser. =

This tool lets you roll forward and back through your changes. It gives you the freedom to experiment with different layouts and content without the fear of breaking your content.

= Row and widget styles. =

Row and widget styles give you all the control you need to make your content uniquely your own. Change attributes like paddings, background colours and column spacing. You can also enter custom CSS and CSS classes if you need even finer grained control.

= It's free, and always will be. =

Page Builder is our commitment to the democratization of content creation. Like WordPress, Page Builder is, and always will be free. We'll continue supporting and developing it for many years to come. It'll only get better from here.

We offer free support on the [SiteOrigin support forums](https://siteorigin.com/thread/).

= Actively Developed =

Page Builder is actively developed with new features and exciting enhancements all the time. Keep track on the [Page Builder GitHub repository](https://github.com/siteorigin/siteorigin-panels).

Read the [Page Builder developer docs](https://siteorigin.com/docs/page-builder/) if you'd like to develop for Page Builder.

= Available in 17 Languages =

Through the efforts of both professional translators and our community, Page Builder is available in the following languages:  Afrikaans, Bulgarian, Chinese (simplified), Danish, Dutch, English, Finnish, French, German, Hindi, Italian, Japanese, Polish, Portuguese (BR), Russian, Spanish and Swedish.

== Installation ==

1. Upload and install Page Builder in the same way you'd install any other plugin.
2. Read the [usage documentation](http://siteorigin.com/page-builder/documentation/) on SiteOrigin.

== Screenshots ==

1. The page builder interface.
2. Powerful widget insert dialog with groups and search.
3. Live Editor that lets you change your content in real time.
4. Undo changes with the History Browser.
5. Row Builder that gives unlimited flexibility.

== Documentation ==

[Documentation](http://siteorigin.com/page-builder/documentation/) is available on SiteOrigin.

== Frequently Asked Questions ==

= How do I move a site created with Page Builder from one server to another? =

We recommend the [duplicator plugin](https://wordpress.org/plugins/duplicator/). We've tested it in several instances and it always works well with Page Builder data.

= Can I bundle Page Builder with my theme? =

Yes, provided your theme is licensed under GPL or a compatible license. If you're publishing your theme on ThemeForest, you must select the GPL license instead of their regular license.

Page Builder is actively developed and updated, so generally I'd recommend that you have your users install the original plugin so they can receive updates. You can try [TGM Plugin Activation](http://tgmpluginactivation.com/).

= Will plugin X work with Page Builder? =

We've tried to ensure that Page Builder is compatible with most plugin widgets. It's best to just download Page Builder and test for yourself.

== Changelog ==

= 2.4.6 - April 13 2016 =
* Fixed Javascript errors with layout builder widget.

= 2.4.5 - April 13 2016 =
* Only trigger contextual menu for topmost dialog.
* Improved design of Live Editor preview.
* Added Live Editor link in the admin menu bar.

= 2.4.4 - April 6 2016 =
* Fixed ordering of new rows, widgets and cells in builder interface.
* Fixed Layout Builder widget sanitization error. Was causing fatal error on older versions of PHP.

= 2.4.3 - April 6 2016 =
* Fixed measurement style fields.
* Properly process raw widgets in Live Editor.
* Remove empty widgets from raw widget processing.

= 2.4.2 - April 4 2016 =
* Improved error handling and reporting.
* Don't add widget class for TwentySixteen theme.

= 2.4.1 - April 2 2016 =
* Fixed: Copying content from standard editor to Page Builder
* Fixed: Plugin conflict with Jetpack Widget Visibility and other plugins.

= 2.4 - April 1 2016 =
* Created new Live Editor.
* Changes to Page Builder admin HTML structure for Live Editor.
* New layout for prebuilt dialog.
* Now possible to append, prepend and replace layouts in prebuilt dialog.
* Fixed contextual menu in Layout Builder widget.
* Added row/widget actions to contextual menu.
* Clarified functionality of "Switch to Editor" button by renaming to "Revert to Editor".
* refreshPanelsData function is called more consistently.
* Various background performance enhancements.
* Full JS code refactoring.
* Fixed cell bottom margins with reverse collapse order.
* Improved window scroll locking for dialogs.
* Added `in_widget_form` action when rendering widget forms
* Custom home page now saves revisions.

= 2.3.2 - March 11 2016 =
* Fixed compatibility with WordPress 4.5

= 2.3.1 - February 10 2016 =
* Fixed fatal error on RTL sites.
* Made setting to enable tablet layout. Disabled by default.

= 2.3 - February 10 2016 =
* Delete preview panels data if there are no widgets.
* Added a collapse order field.
* Added custom row ID field.
* Fixed copy content setting.
* Added tablet responsive level.
* Fixed admin templates.
* Fix to ensure live editor works with HTTPs admin requests.
* Fix for Yoast SEO compatibility.
* Removed use of filter_input for HHVM issues.
* Added panelsStretchRows event after frontend row stretch event.
* Minor performance enhancements.
* Merged all separate JS files into a single Browserify compiled file.
* Added version numbers to some JS files to ensure cache busting.

== Upgrade Notice ==

Page Builder 2.0 is a major update. Please ensure that you backup your database before updating from a 1.x version. Updating from 1.x to 2.0 is a smooth transition, but it's always better to have a backup.
