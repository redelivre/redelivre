=== XRDS-Simple ===
Contributors: singpolyma, wnorris, pfefferle
Tags: xrds, xrds-simple, discovery
Requires at least: 2.1
Tested up to: 4.3
Stable tag: 1.2

Provides framework for other plugins to advertise services via XRDS.


== Description ==

[XRDS-Simple][] is a profile of XRDS, a service discovery protocol which used
in the [OpenID][] authentication specification as well as [OAuth][].  This
plugin provides a generic framework to allow other plugins to contribute their
own service endpoints to be included in the XRDS service document for the
domain.

[XRDS-Simple]: https://de.wikipedia.org/wiki/XRDS#XRDS_Simple
[OpenID]: http://openid.net/
[OAuth]: http://oauth.net/


== Installation ==

This plugin follows the [standard WordPress installation method][]:

1. Upload the `xrds-simple` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

[standard WordPress installation method]: http://codex.wordpress.org/Managing_Plugins#Installing_Plugins


== Frequently Asked Questions ==

= How do I contribute services to the XRDS document =

Implement the filter 'xrds_simple', and see the public functions at the top of
the file.


== Changelog ==

Project maintined on github at
[diso/wordpress-xrds-simple](https://github.com/diso/wordpress-xrds-simple).

= version 1.2 (Jul 17, 2015)=
 - allow 'xri://$xrds*simple' Type to be filtered out
 - check if $_SERVER['HTTP_ACCEPT'] exist to avoid notice that break the xrds file

= version 1.1 (Nov 16, 2012)=
 - fix various PHP and WordPress errors and warnings
 - add ability to fetch plain text XRDS document (mainly for debugging.  see [example](http://willnorris.com/?xrds=1&format=text))

= version 1.0 (Oct 7, 2008) =
 - initial public release
