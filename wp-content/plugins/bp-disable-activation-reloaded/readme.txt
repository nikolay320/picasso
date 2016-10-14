=== BP Disable Activation Reloaded ===
Contributors: timersys
Donate link: http://www.timersys.com
Tags: BuddyPress, activation, WPMU
Requires at least: 3.6
Tested up to: 3.9.2
Site Wide Only: true
Stable tag: 1.2.1

Based on crashutah, apeatling plugin Disables the activation email and automatically activates new users in BuddyPress under a standard WP install and WPMU (multisite).  Also, automatically logs in the new user since the account is already active.

== Description ==

Based on crashutah, apeatling http://wordpress.org/plugins/bp-disable-activation/ Disables the activation email and automatically activates new users in BuddyPress under a standard WP install and WPMU (multisite).  Also, automatically logs in the new user since the account is already active. 


Basically i updated the plugin and added some features like:

-Option to turn off automatic login
-Redirect options after account creation

Known Bugs:
-Doesn't do the automatic login if you allow blog creation during the user creation in WPMU (multisite)

= Install Multiple plugins at once with WpFavs  =

Bulk plugin installation tool, import WP favorites and create your own lists ([http://wordpress.org/extend/plugins/wpfavs/](http://wordpress.org/extend/plugins/wpfavs/))

= Increase your twitter followers  =

Increase your Twitter followers with Twitter likebox Plugin ([http://wordpress.org/extend/plugins/twitter-like-box-reloaded/](http://wordpress.org/extend/plugins/twitter-like-box-reloaded/))

= Wordpress Social Invitations  =

Enhance your site by letting your users send Social Invitations ([http://wp.timersys.com/wordpress-social-invitations/](http://wp.timersys.com/wordpress-social-invitations/?utm_source=social-popup&utm_medium=readme))


== Installation ==

1. Upload the 'bp-disable-activation-reloaded' folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to the settings page

== Frequently Asked Questions ==

= Won't this allow more spammers to get in? =

Of course it could.  So, you should consider using other plugins and methods for preventing spammers from signing up on your site.  However, many people have seen spammers get through just fine even with email activation enabled.  Plus, some sites are designed so that email activation doesn't matter.  Thus the plugin.

= What if I don't want my users to automatically login? =

Why don't you?  Users will love that feature.  I'll look at adding an option to turn this on/off.  Until then you can comment out those lines if you don't want it.

== Changelog ==

= 1.2.1 =
* Arggg missed the fix link
* Added confirmation message

= 1.2 =

* Fixed bug that user had not role assigned and remains in pending tab
* Added fix link to fix all the users with the error above

= 1.1 = 
* Fixed problem with user not being asigned a role http://wordpress.org/support/topic/user-role-bp-20-21
= 1.0 =

* First release