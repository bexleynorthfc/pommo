=== poMMo for WordPress ===
Contributors: topquarky
Tags: email list, contact management, add-ons, framework
Requires at least: 2.5
Tested up to: 3.1.3
Stable tag: 1.0.8

poMMo for WordPress is an email list and extendable contact management plugin released on the Top Quark architecture

== Description ==

This plugin is a fork of poMMo, an open source newsletter project that's been publicly dormant for a while but has been a source of coding pleasure for me since I found it back in 2005, around the time Matt and the gang were releasing [Duke](http://en.wikipedia.org/wiki/WordPress).  Today, it's integrated into WordPress via the [Top Quark](http://wordpress.org/extend/plugins/topquark) architecture and forms the basis for a powerful contact management tool.  

= Newsletter Engine =

As a newsletter engine, poMMo offers the following useful features:

* *Throttling* - most web hosts only allow limited amount of emails to be sent per hour.  Find out what your web host allows and then set the throttle in poMMo
* *Templating* - save your newsletter as a template, or just save it as a draft to return to later
* *Personlization* - include the recipient's first name, or anything else you track
* *Groups* - create and use groups to filter your list
* *HTML & Plain Text* - emails are properly formatted to include both versions

= Contact Management Tool = 

poMMo is easy to use to keep track of a customizable set of data for your subscribers.  It's easy to add new fields, turn fields on/off (determines whether they appear on the signup form) and reorder fields.  It's also easy to create complex groups that will search your contacts based on a variety of matching criteria.  

Beyond that, it is possible for plugin developers to develop add-ons to the main poMMo plugin that can customize the database experience in just about any way they want, including adding extra admin pages, changing the look of any of the admin or user pages.  Using these techniques, it's possible for an organization to segment their list into different contexts.  For example, a non-profit organization might have a general email list and also a list of volunteers.  It's possible to have poMMo track them all, restrict access on a user-by-user basis to which records they can edit.  It's also possible to create a Read Only version of the database, preventing people from making unwanted changes while still giving them access to the data.  I've written several such add-ons for different clients and will release them as examples of what can be done.  The division into contexts is available via a Premium plugin called poMMo Plus that will be available from [topquark.com](http://topquark.com) shortly.  I've also written an add-on that allows you to send to your list using MailChimp.  

Unlike other email list programs, poMMo can handle contacts that don't have an email address, and it can also handle multiple records being associated with the same email address (via a "Parent Email" field).  Both of these features are turned on by default.  

= Invitation to collaboration =

The original poMMo project had several developers working on it.  I'm releasing this here in hopes that it piques the interest of open source developers out there with a desire to take this product to the next level.  Here's a brief roadmap of improvements that I think can be made:

* Integrate with the WordPress user database (right now they are separate, but to be a proper WordPress integration, they should be one and the same)
* The HTML mail editor is a separate installation of TinyMCE.  It would be good to make it run off of the WordPress version
* Bounce handling.  You're able to specify an address to send bounces to, but at this point it does nothing with that
* Along those same lines - enable a "post as email" feature to allow a post to use shortcodes and other WordPress features
* Integrate with other third party email service providers (iContact, Vertical Response, etc)
* Maybe you have ideas on things you'd like to see.  Get in touch at [topquark.com](http://topquark.com)

== Installation ==

1. Install Self Hosted Plugins directly from the repository like you would any other plugin

When you activate the plugin for the first time, it will automatically install the extra tables in the database that you need.  It also creates your list based on the name of your blog and the admin email.  It also sets up some frequently used fields (name, address, phone, etc).  Go to poMMo > Setup > Fields to customize this list.  I recommend keeping the "First Name" and "Last Name" fields as they are required by the poMMo Plus add-on, which offers a greatly improved admin experience.  This add-on will be released shortly on topquark.com

== Frequently Asked Questions ==

= How do I insert a subscription form? =

Use the shortcode `[topquark action=paint package=poMMo subject=subscribe]`

= Can I put a mini subscribe form in a widget? = 

Yes, though this is not yet available as a pre-defined widget.  To do it, you'll need to first enable `shortcode` parsing within your widgets.  Doing that is simple.  Somewhere within the functions.php file of your theme, add the line:

`add_filter('widget_text', 'do_shortcode');`

That's it.  Now, add the following shortcode to a Text widget:

`[topquark action=paint package=poMMo subject=mini_subscribe]`

= Can subscribers login to change their settings? =

Yes.  You'll need to direct them to the page `<?php plugins_url('pommo/user/login.php') ?>` (e.g. http://mysite.com/wp-content/plugins/pommo/login.php)

= My shared host only allows me to send 200 emails/hour.  Can poMMo handle this? =

Yes.  poMMo has throttling built in (as well as a variety of ways to specify the outgoing mail server).  Go to poMMo > Setup > Configure and click Throttling at the bottom.  You can set your throttle levels here and poMMo will only send out that number of emails per hour.  

= How do I turn off the "blank email allowed" and "parent email" features? =

The "blank email allowed" feature (on by default) allows records to exist in the database without any actual email address.  The "parent email" feature allows multiple records to be associated with the same email address.  To turn either of these off, add the following code to your functions.php file:

`add_filter(addition_conf_poMMo','my_conf_poMMo')
function my_conf_poMMo($args){
	// $args[0] is a reference to the Package object
	unset($args[0]->bm_BlankEmail); // turns off the blank email allowed feature
	unset($args[0]->bm_ParentEmailField); // turns off the parent email feature
	// No need to return anything.  The package object as already been altered by reference
}`

= Is there any way to show an archive of past newsletters? = 

Yes.  Include the following shortcode in your page/post: `[topquark action=paint package=poMMo subject=archives]`.  This will show a list of all newsletters sent to 'All Subscribers'.  You can adjust that within your `functions.php` file by using the following filter:

`add_filters('pommo_groups_for_archives','my_pommo_groups_for_archives');
function my_pommo_groups_for_archives($groups){
	$groups[] = 'My Other Group';
	return $groups;
}`

= Is it internationalization ready? = 

No, not really.  The version of poMMo that I forked had internationalization working well, but I didn't do a good job of keeping it going.  Wanna help?  Find me at [topquark.com](http://topquark.com/)

== Changelog ==

= 1.0.8 =
* Changed how DBO::query returned its value in a certain case.  Returning mysql_result(...) as a reference was causing problems on some configurations of PHP.  Instead, I assign a variable and return that variable.  It seemed to fix the issue.  

= 1.0.7 =
* Removed maxlength restriction on groups criteria input box in admin area

= 1.0.6 =
* Fix: array error in setup_smtp.php

= 1.0.5 =
* Added filter to allow disable password.  Do the following:
`add_filter('poMMo_use_password_field',create_function('$a,$poMMo_Package','return false;'),10,2);`

= 1.0.4 =
* Added filter to allow customized redirect on login
* Added a hook that allows a workaround if fsockopen won't work locally.  
* Added a filter that allows a workaround if server is compiled with --enable-magic-quotes

= 1.0.3 =
* Now works in Safe Mode (even though this is a deprecated feature as of PHP 5.3.0)

= 1.0.2 =
* Bug fixes - error on user login

= 1.0.1 =
* Fixed fatal error on install if Top Quark Architecture is not installed

= 1.0.0 =
* Initial check-in

== Requirements ==

= Requires the Top Quark Architecture plugin = 

I develop plugins using a framework that I've developed for allowing rapid database-driven plugin development.  The Self Hosted Plugins uses that framework, so you must install the [Top Quark Architecture](http://wordpress.org/extend/plugins/topquark/) plugin.  

== Acknowledgements ==

Huge thanks go to Brice Burgess and his team of collaborators for releasing the original poMMo program.

== Upgrade Notice ==

= 1.0.1 =
No upgrade notice

