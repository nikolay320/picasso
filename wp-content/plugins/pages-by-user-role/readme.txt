=== Pages by User Role for WordPress ===
Author: Alberto Lau (RightHere LLC)
Author URL: http://plugins.righthere.com/pages-by-user-role/
Tags: Access Control, User Roles, Hide Pages, Menu, Custom Post Types, Categories, WordPress, Restrict access to content, Allow access to content
Requires at least: 3.6
Tested up to: 4.4.2
Stable tag: 1.2.9.69262



======== CHANGELOG ========
Version 1.2.9.69262 - April 9, 2016
* New Feature: Added support for BuddyPress
* New Feature: Added support for BBPress

Version 1.2.8.69096 - March 31, 2016
* Update: Changed order of Option Panel tabs
* Bug Fixed: post_type_enabled fixed getting post_type name
* Compatibility Fix: Added check for WooCommerce Shop, Cart, My Account and Checkout) to avoid PHP warning.

Version 1.2.7.68965 - March 26, 2016
* Compatibility Fix: Change classes where the constructor has the same name as the class to __construct (PHP 7 compatibility).
* New Feature: Allow restricting access to WooCommerce pages Shop, Cart, My Account and Checkout (restrictions for WooCommerce Custom Post Type archive page)
* Update: Updated Options Panel to version 2.8.3

Version 1.2.6.67786 - February 11, 2016
* Compatibility Fix: An undetermined third party plugin is causing a PHP warning
* New Feature: Added option to include filtering in the Ajax (Usage: Javascript loaded posts that use wp-admin/admin-ajax.php in the front end).

Version 1.2.5.58443 - April 24, 2015
* Improvement: Replaced add_query_arg() due to an XSS vulnerability issue that affects many WordPress plugins and themes. Please observe that before the function could be accessed the user had to be an Administrator, meaning that the potential issue was not available to the public.

Version 1.2.4.55151 - January 6, 2015
* Bug Fixed: When blocked post id’s make the query result empty, the blocked posts are not blocked at all.

Version 1.2.3.54781 - November 19, 2014
* Compatibility Fix: BBpress topic not shown, replies show when put is active

Version 1.2.2.54690 - November 8, 2014
* New Feature: Advanced option to out a custom HTML/Javascript when a page is restricted to the user
* New Feature: Restrict Post Type Archive by User Role, which allows you to restrict access to Post Type Archives by User Role and set an independent redirect URL for it
* Bug Fixed: Disappearing Options Tab
* Bug Fixed: When using inverted PUR the edit post link in the toolbar was still visible, and the user can actually bypass the restriction and edit the post.
* Compatibility Update: WooCommerce product pages 

Version 1.2.1 rev47835 - March 17, 2014
* Bug Fixed: Handle a situation where under some buggy conditions, output have been already sent by the site, before it should, and thus breaking redirection.
* Bug Fixed: Removed php warnings
* New Feature: Added a setting to restrict what user roles will be able to view the “Access Control” Metabox.
* New Feature: Experimental Inverted PUR functionality

Version 1.2.0 rev22604 - March 6, 2012
* New Feature: pur_not_logged_in shortcode for showing content only to visitors NOT logged in.
* New Feature: Enable Administrator to allow or block access to user roles (previously was only allow)
* New Feature: Show in menu when restricted post type
* New Feature: In the list of posts, in the Access Control column show if PUR is Allowing or Denying access to listed roles
* New Feature: Show Allow as Green and Deny as Red.
* Bug Fixed: Avoid a crash with Options Panel version 2

Version 1.1.8 rev14552 - December 19, 2011
* Update: Enabled WordPress 3.3 functionality

Version 1.1.7 rev11497 - December 10, 2011
* Bug Fixed: pur_restricted Shortcode was not rendering Shortcodes in the content
* New Feature: pur_restricted Shortcode now allow alternative text with HTML.

Version 1.1.6 rev9091 - September 26, 2011
* New Feature: No Access behavior customization. Admin can specify if a restricted page should redirect to login or to redirect URL.
* Bug Fixed: Adjust the redirect URL field in the metabox
* Bug Fixed: Added missing registration service library

Version 1.1.5 rev7652 - August 8, 2011
* New Feature: Built-in Shortcode pur_restricted to restrict access to certain sections of the content by capability; defaults to view_restricted-content but any capability
* Updated Options Panel updated
* New Feature: No access behavior customization. Admin can specify if a restricted page should redirect to login or to redirect URL.
* New Feature: Custom Post Types by User Role. This only shows if there are custom post types.
This is a mini-plugin itself that adds the following functionality:
In the tab option it shows a list of custom post types, and checkboxes of all the existing user roles for each custom post type.
By checking a user role for a custom post type you are restricting admin access to that post type only to the checked user role.

VERY IMPORTANT: Always check the Administrator. Note we do not do it by default, thus maybe the Administrator changed the the administrator user role, so we don't really know what the administrator role is.

In the case of incorrectly setting the administrator user role, there is an option on the same tab to disable this feature an recover access to the custom post types.

Version 1.1.4 rev4375 - May 7, 2011
* Bug Fixed: After setting user roles in Category and removing all, all roles where denied access afterwards.

Version 1.1.3 rev 2526 - March 24, 2011
* New feature, added comment filtering to comments fetch with the wp method get_comments (recent comments widget)

Version 1.1.2 rev 1863 - March 1, 2011
* Changed the procedure for redirect;
     1) the custom url
     2) the default url
     3) the login page
     4) if you are logged in and do not have access, an error message is shown.

Version 1.1.1 - February 8, 2011
* Fixed broken default redirect URL
* Fixed Post and Post redirect URL

Version 1.1.0 - February 3, 2011
* Added support for non standard WordPress table pre-fix
* Added support for access control to Categories
* Categories with access control are not searchable (unless you have access)
* Restrict access to Post by using the Posts ID.
* Category will not show in the menu if restricted access

Version 1.0.0 - November 3, 2010
* First release.


======== DESCRIPTION ========

This plugin lets you restrict access to a Page, Post, Custom Post Type or Category depending on which Role the user has. It removes the Page, Post, Custom Post Type or Categories from search results and blog roll. You can hide Page and Categories from the menu when the user is not logged in.

You can also set a specific redirect URL for users that don’t have the required User Role.

== INSTALLATION ==

1. Upload the 'pages-by-user-role' folder to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Click on 'Pages by User Role' in the left admin bar of your dashboard

== Frequently Asked Questions ==

Q: Can I hide a Page from the menu when a user is not logged in?
A: Yes, if you choose to restrict access to a Page, Post or Custom Post Type then the page will NOT show in the menu.

Q: What happens if I don't set a redirect URL and a user try to access a Page or Post they he or she doesn't have access to?
A: The user will get redirect to the default page saying "You don't have access to this page, contact the website administrator."

Q: Can I create a custom page that users will be redirect to if they don't have access?
A: Yes, you can create a custom page and then enter the URL either as the default redirect page. You can actually redirect user to an individual Page for every Post or Post you create.

Q: Can I provide access to more than one User Role to the same Page or Post?
A: Yes, simply by selecting more than one User Role in the "Page Access" box.


