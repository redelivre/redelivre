=== WPaudio MP3 Player ===
Contributors: toddiceton
Donate link: http://wpaudio.com/donate
Tags: audio, embed, links, media, mobile, mp3, music, plugin, podcast, post, widget
Requires at least: 2.5
Tested up to: 3.0.1
Stable tag: 3.1

Play mp3s and podcasts in posts with the simplest, cleanest, easiest mp3 player. New HTML5 support for iPhone, iPad, Android & mobile browsers.

== Description ==

### All the other WordPress audio players were crappy or ugly so I made a better one.  
  
![WPaudio](http://wpaudio.com/screenshot.png)

Deactivate that lame Flash player and install a plugin that makes you proud to embed mp3s. WPaudio installs in seconds, looks great, and uses simple tags... all without bulky files or affecting page load times.

#### Easy to install, easy to use

Install directly from WordPress (just go to *Plugins* -> *Add New*) and start embedding mp3s immediately. Now you can choose to convert all mp3 links or only the ones you select, and you still have the power of advanced tags.

#### Clean design with intuitive controls

Everything's tucked out of the way until you click play. Jog the track by clicking the position bar. Simple.

#### Compatible with your old audio player tags

Want to switch to a better player? Just deactivate your old plugin and let WPaudio give your old posts the dignity they deserve. WPaudio is compatible with Audio Player tags, with support for more on the way.

#### Won't slow down your site

WPaudio was written for performance.  It uses WordPress's built-in scripts, HTML5, and the lightweight SoundManager2 library.

### How to use WPaudio

* If you want to convert every mp3 link into a player, go to *Settings* -> *WPaudio* and select the first option, *Convert all mp3 links*.

* If you want to selectively convert mp3 links, just add the `wpaudio` class to the links you want converted, like this:

		<a href="http://url.to/your.mp3" class="wpaudio">Artist - Song</a>
	
* If you want to disable downloads or specify a different download URL, use the advanced tags.

		[wpaudio url="http://url.to/your.mp3" text="Artist - Song" dl="0"]
		
* For autoplay, just add autoplay="1" to your tags.

		[wpaudio url="http://url.to/your.mp3" text="Artist - Song" autoplay="1"]
  
Powered by the SoundManager 2 API  
http://www.schillmania.com/projects/soundmanager2/

== Installation ==

* Install directly from WordPress (just go to *Plugins* -> *Add New*) and activate.

Or

* Manually install to your WP plugins directory
	1. Unzip `wpaudio-mp3-player.X.zip` in the `/wp-content/plugins/` directory.
	2. Activate the plugin through the *Plugins* menu in WordPress.
	3. Have a look at the options by going to *Settings* -> *WPaudio*.

== Frequently Asked Questions ==

Find solutions, ask questions, and give feedback at the [WPaudio forum](http://wpaudio.com/forum/).

= It's not working! =

For best results, upgrade to the latest WordPress.

1. Try changing to the default theme.  Does it work now?  If so, make sure your theme has `wp_head();` and `wp_footer();`.
1. Try deactivating your other plugins and reactivating them one by one.  Let me know when you find the one causing the conflict.
1. Make sure the domain in your MP3 URLs matches the domain in your WordPress blog URL setting.
1. Check the [forum](http://wpaudio.com/forum/) and make a new post if you don't find a solution.

= How do I tell WPaudio which links to make into players? =

Just add the `wpaudio` class to those links.  If there's already a class, add it inside the quotes after a space (`class="anotherclass wpaudio"`).

	<a href="http://url.to/your.mp3" class="wpaudio">Artist - Song</a>

If you want to convert all mp3 links, just check the first option when you go to *Settings* -> *WPaudio*.  Then you don't have to add the class, but it won't hurt.

= I use (some other mp3 player).  Do I have to go back and change ALL my tags? =

Nope.  If you used Audio Player, just tell WPaudio to handle it on the *Settings* -> *WPaudio* page.  If you used another plugin, email me and I'll get support for it into the next version.

= With the advanced tags, should I include the text and dl parameters? (Can I specify what shows up next to the player?) =

Always include the *text* parameter.

The *dl* parameter isn't required.  See the next question.

= What if I don't want readers to download the mp3 from my server? =

Allow users to download from a file host by adding the *dl* parameter to your shortcode like this.

	[wpaudio url="http://url.to/your.mp3" dl="http://download-host.com/mp3_download_url"]
	
Or disable downloads by setting *dl* to "0" like this.

	[wpaudio url="http://url.to/your.mp3" dl="0"]


== Screenshots ==

1. WPaudio player in action.  Play by clicking the play button or the large text.  The blue bar indicates current position, the gray bar indicates the data loaded so far, and the light gray bar indicates total duration.  Download by clicking the name of the clip.  (Optionally, you can change the download link for use with Mediafire, YSI, etc. or disable downloads.)
2. WPaudio player before play is clicked.
3. WPaudio player in action with the download link disabled.
4. Editing the tag.

== Changelog ==

= 3.1 =
* Player styled by CSS and inline for better theme tolerance
* Set volume to 100% for HTML5 and SoundManager
* Handled HTML5 exceptions
* Handled SoundManager absence gracefully
* Preload play and pause images sequentially
* Play button reload fix

= 3.0.1 =
* Fixed 'convert all mp3s' option

= 3.0 =
* Added HTML5 support (now compatible with iPhone, iPad, and iPod)
* Complete rewrite of JavaScript
* Updated to latest SoundManager2
* Minified JS and optimized images

= 2.2.0 =
* Added autoplay
* Open players collapse when another is clicked.

= 2.1.1 =
* Updated to SoundManager2 v2.95b.20100101
* Removed unnecessary CDATA designation

= 2.1.0 =
* Progress bar clicks obey one play at a time
* Tested with WP 2.8.6

= 2.0.4 =
* Player button resets on completion
* JS events checking button status call handlers directly

= 2.0.3 =
* Link and play button CSS more specific
* CDATA around wpa_urls

= 2.0.2 =
* Load scripts only outside of admin (removes SM2 debug button)

= 2.0.1 =
* Fixed checkbox preference save issue

= 2.0.0 =
* Link conversion: all or selective mp3 links
* Customize font face and size
* Inline-block layout resolves layout issues (no floats, center now possible)
* Degrades gracefully: links converted to players solely with Javascript
* Options now stored in one serialized field
* ID3 read preventing play issue resolved
* Separated play/pause icons for customizability

= 1.5.2 =
* Restores support for WordPress 2.5 and up

= 1.5.1 =
* Parse_url now uses one parameter for maximum PHP compatibility

= 1.5.0 =
* Clicking player text link now plays song, separate download link
* Automatic URL correction if file exists locally (to satisfy Flash cross-domain policy)
* Fixed out-of-focus load issue (Safari)
* Scripts now external
* Wrapper for use outside posts (direct from template)
* Better iPhone/iPod links

= 1.4.0 =
* Color customization options
* Passes XHTML validation (added CDATA tags)
* Concealed mp3 URLs (Unicode-encoded)

= 1.3.1 =
* Moved to WP's internal jQuery library to avoid conflicts
* Removed legacy code (fixes IE layout break issue)
* Degradation more graceful: separate warnings for JS/Flash (invisible on load instead of hiding as player components load)
* Button icon fixed for multiple players on one page

= 1.3.0 =
* More consistent behavior all around (switched from JW Player to SoundManager 2 API)
* Documented option to disable download link
* Fixed player position/duration/load bar width for disabled downloads

= 1.2.2 =
* Fixed symbols in *text* field
* Enabled shortcode and audio tags in sidebar widgets
* Added support for audio tag titles and artists

= 1.2.1 =
* Enabled shortcode in the excerpt

= 1.2 =
* Fixed directory settings
* Improved readme

= 1.1 =
* Text support, smoother ID3 reading
* Download link parameter

= 1.0 =
* Shortcode support
* Google CDN-served jQuery and SWFObject

== Upgrade Notice ==

= 3.0 =
New iPhone, iPad, Android, WebOS, and mobile browser support (HTML5-enabled). Completely rewritten and smaller files for better performance.
