=== Eletro Widgets ===
Contributors: HackLab
Donate link: http://eletrowidgets.hacklab.com.br
Tags: widgets, home, cms
Requires at least: 2.7
Tested up to: 2.9.2
Stable tag: 1.0

Allows you to use the power and flexibility of the WordPress Widgets to set up a dynamic area anywhere in your site and manage multiple columns of widgets, dragging and dropping them around

== Description ==

Allows you to use the power and flexibility of the WordPress Widgets to set up a dynamic area anywhere in your site and manage multiple columns of widgets, dragging and dropping them around

== Installation ==

1. Download the package
2. Upload to the plugins folder
3. Activate it
4. See Other Notes for more details on how to configure and use this plugin

== Usage ==

<code>
<?php new EletroWidgets([$cols = 2][, $id = 0] [, $onlyEletroWidgets = false]); ?>
</code>

The basics:

Go to the theme file where you want to put eletro-widgets and add the following code:
<code>
<?php if (class_exists('EletroWidgets')) new EletroWidgets(); ?>
</code>
This will create a two column container on your site where you will be able to add the widgets.

The first paramater it takes is the number of columns you want your container to have. So, if you want a 3 columns container, this is what you have to do:
<code>
<?php if (class_exists('EletroWidgets')) new EletroWidgets(3); ?>
</code>

After putting the code above in a template file, go to any of the pages that uses this template (logged with an user with administrative privileges) and you will be able to add or remove widgets from the Eletro Widgets area. You can add a Wordpress default widget or you can create your own widget (see http://www.emanueleferonato.com/2008/02/15/how-to-create-a-wordpress-widget/).

ATTENTION: there are some default Wordpress widgets that doesn't work with Eletro Widgets (we are working on this issue for future releases)

== Customizing the appearance of the widgets ==

Eletro Widgets comes with a default css stylesheet that you can customize to fit your theme.

In order to do that, copy the file eletro-widgets.css to your theme's folder and edit it there as you like.


== Advanced - multiple containers on the same page ==

Eletro Widgets allow you to have more than one container of widgets at the same page.

For example, you can have one line with 3 columns of widgets, and another set of 2 columns of widgets underneath.

In order to do that, all you have to do is declare a new instance of EletroWidgets passing the second parameter, wich is the unique ID for this container.

Example:
<code>
<?php if (class_exists('EletroWidgets')) new EletroWidgets(); ?>
<?php if (class_exists('EletroWidgets')) new EletroWidgets(3, 2); ?>
</code>
Note: There MUST be at least one container with the ID 0 (zero). All you have to do is declare at least one EletroWidget leaving the ID parameter empty


== How to restrict available widgets==

You may want to choose only a few widgets to be available on your eletro widget canvas.

You can do that by passing the third paramater as true. It will tell this canvas to hide from the list of available widgets all the widgets that were not defined as an "eletro-widget".

To define a widget as an "eletro-widget", do the following:

<code>
<?php defineAsEletroWidget($widgetId); ?>
</code>

== Screenshots ==


	
