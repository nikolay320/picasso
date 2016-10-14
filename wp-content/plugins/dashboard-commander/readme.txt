=== Dashboard Commander ===
Contributors: joshhartman
Tags: admin, dashboard, widgets, command, manage, hide, access, capabilities
Requires at least: 2.9.2
Tested up to: 4.3
Stable tag: 1.0.3

Command your admin dashboard. Manage built-in widgets and dynamically registered widgets. Hide widgets depending upon user capabilities.

== Description ==

Command your admin dashboard. Manage built-in widgets (Right Now, Recent Comments, etc.) and dynamically registered widgets (Google Analytics Summary, WP E-Commerce Dashboard, etc.). Hide widgets depending upon user capabilities.

This plugin is based upon Dave Kinkead's Dashboard Heaven plugin and extends it to support dynamically registered widgets, such as dashboard widgets that are added by a plugin.

After installation access to all dashboard widgets is removed, then you can use the options at Settings > Dashboard Commander to configure the minimum access level for each widget.

[youtube http://www.youtube.com/watch?v=7YBOm5ov3vs]

== Installation ==

1. Extract the `dashboard-commander` folder to your `wp-content/plugins` directory
1. Activate the plugin through the admin interface
1. Visit your dashboard once to build a list of dashboard widgets
1. Go to Settings > Dashboard Commander to configure access to widgets

[youtube http://www.youtube.com/watch?v=7YBOm5ov3vs]

== Frequently Asked Questions ==

= Why is my dashboard empty after installing your plugin? =

After installation access to all dashboard widgets is removed, then you can use the options at Settings > Dashboard Commander to configure the minimum access level for each widget.

= I have a widget that shows up when I'm logged in as an Administrator, but i can't get it to show up for an Editor/Author/Contributor? =

Some WordPress core dashboard widgets and other plugin dashboard widgets are restricted to a certain user capability level. Example: You can not make the Recent Comments dashboard widget visible to a Subscriber, Contributor or Author because of this fact. This can not be overridden using Dashboard Commander, but you can override this behavior by hacking the specific widget-setup function in the WordPress core or plugin code. 

= Have a question that is not addressed here? =

Leave a comment on the plugin homepage http://www.warpconduit.net/wordpress-plugins/dashboard-commander/

== Screenshots ==

1. Dashboard Commander Options found at Settings > Dashboard Commander

== Changelog ==

= 1.0.3 =
* Update for WordPress 3.8

= 1.0.2 =
* Removed orphaned code in deactivation function

= 1.0.1 =
* Tested on WordPress 3.1
* Updated FAQ
* Added Settings link on Plugins page

= 1.0 =
* First stable release
