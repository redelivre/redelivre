=== Events Manager and WPML Compatibility ===
Contributors: netweblogic
Donate link: http://wp-events-plugin.com
Tags: events, multilingual, wpml, event, event registration, event calendar, events calendar, event management, events-manager
Requires at least: 3.3
Tested up to: 5.0
Stable tag: 1.2
License: GPLv2
Requires PHP: 5.2

Integrates the Events Manager and WPML plugins together to provide a smoother multilingual experience (Requires Events Manager and WPML)

== Description ==

This plugin helps make [Events Manager](http://wordpress.org/extend/plugins/events-manager/) and [WPML](http://wpml.org) work better together by improving various issues:

* Detects translated pages of specific EM pages (assigned in Events > Settings > Pages) and displays relevant language content
* Event translations now share relevant information across all translations, including
 * Event Times
 * Location Information
  * If translations for the location exist, translated events will show/link to location of the same language, if not the original location translation.
 * Bookings and Booking Forms
 * If you delete an event that is the originally translated event, booking and other meta info is transferred to default language or next available language translation.
* Custom texts, emails and formats can now be customized for each language in the settings page.

= Special Installation Steps =
Please ensure that WPML 3 and EM 5.6 or higher are installed BEFORE activating this plugin.

When setting up EM and WPML, you should create translated versions of the event, location, category, tag, etc. pages assigned in Events > Settings > Pages of your admin area. Duplicating them using WPML is enough.
 
= Nuances = 
WPML and Events Manager are both very complex plugins and there are some inevitable nuances and features that currently won't work and more time is needed to find appropriate solutions:

* Certain placeholders that output extra static text (such as #_BOOKINGSUMMARY outputting price 'Total') aren't fully translated.
* Recurring events
 * Recurring Events can't be translated when editing the recurrence template, they must be done one by one  i.e. at single event level
 * Recurring events are disabled by default due to the above
* Location Searching and Lists
 * currently autocompleter forces searches for current languages, we may want to change this in the future to search all languages but give precedence to showing the translated version if available
 * event-related search attributes won't on location work due to architectural limitations, but there are workarounds to get the same result:
   * if using `[locations_map]`, use `[events_map has_location="1"]` instead
   * if using `[locations_list eventful="1"]` use `[events_list limit="25" has_location="1" groupby="location_id"]` instead
* MultiSite
 * Event Manager's MultiSite Global Tables Mode will not work as expected, listing events and locations from other sites will not return the correct items (if at all). This is due to the architecture of WPML vs. EM when in Global Tables Mode. 
 
Given the flexibiltiy of both plugins, there is an huge number of possible setting/language combinations to test. Please let us know of any other nuances you come across on your setup and we'll do our best to fix them as time permits.
 
== Installation ==

This plugin requires WPML and Events Manager to be installed BEFORE installing this plugin.

Events Manager WPML works like any standard Wordpress plugin. [See the Codex for installation instructions](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins).

== Changelog ==

= 1.2 =
* added forced language redirect support for pro settings pages to prevent page reference errors
* fixed translations not getting published when using the translation editor
* additional indirect fixes within the core plugins:
 * added translatable email reminders in EM Pro 2.6.1
 * fixed translated custom emails in EM Pro 2.6
 * fixed translated custom booking form fields not getting translated in booking admin table columns in EM Pro 2.6
 * added information notice when viewing booking, showing the language booking was made in EM 5.9.2
 * added language column to booking admin tables, showing the language booking was made in EM 5.9.2 

= 1.1 =
* fixed validation issues on first submission of a translation due to recent WPML changes
* added fix for translation editor validation issues (kudos David)
* removed unnecessary taxonomy filters thanks to recent fixes in EM and how data is written to $wp_query globals
* fixed calendar day display issues in recent WPML versions
* fixed category page display issues (mainly fixed in Events Manager 5.8)
* fixed PHP warning on trash pages when viewing all lanaguages
* fixed duplicating events via WPML not copying location information first time around
* special thanks David Garcia Watkins and the rest of the WPML dev team for their assistance with many of these bugs!

= 1.0.1 =
* fixed PHP error causing parse errors and blank screens in some setups

= 1.0 =
* this is a complete rewrite, from the ground up, vastly improving overall stability and fixing many bugs that arose over time due to WPML/EM updates
* changed architecture so it hooks into EM's multilingual actions and filters made available in EM_ML and EM_ML.. objects
* changed and removed dependency on em_wpml index table, translations are now resolved on the fly using WPML's records and functions
* fixed RSS and iCal feed links translate and show correct languages
* fixed event category and tag page display issues related to formatting and language selectors
* fixed event duplication via EM not including translations
* fixed WPML duplication of languages not saving event/location properly
* fixed various PHP warnings
* fixed translated permalink and language selector issues on event pages showing events for a current calendar day
* fixed settings pages 'forgetting' certain EM-related page choices where formatting is used when saving/viewing in a different language to the main one
* fixed location validation issues when saving events and their translations
* fixed broken bookings between translations, where bookings are tied to event translations rather then the original event language
* fixed location sharing/translation issues between translations
* fixed various placeholders and formats not translating properly
* added event/location attribute sharing from original event/location as well as making translations of attributes possible
* added translateable booking ticket name and descriptions
* see Events Manager 5.6 and Events Manager Pro 2.4 changelogs for more information on MultiLingual supported features which are automatically compatible with this plugin 

= 0.3 =
* fixed version update checks and table installations on MultiSite causing event submission issues
* fixed attribute translations not being editable

= 0.2 =
* fixed PHP warnings due to non-static function declarations
* fixed unexpected behaviour when checking translated EM assigned pages

= 0.1 =
* first release