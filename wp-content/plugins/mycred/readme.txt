=== myCRED ===
Contributors: designbymerovingi
Tags: points, tokens, credit, management, reward, charge, community, contest, buddypress, jetpack, bbpress, simple press, woocommerce, marketpress, wp e-commerce, gravity forms, share-this
Requires at least: 3.8
Tested up to: 4.4.2
Stable tag: 1.6.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

myCRED is an adaptive points management system that lets you award / charge your users for interacting with your WordPress powered website.

== Description ==

> #### Plugin Support
> Free support is offered  via the [myCRED support forum](http://mycred.me/support/forums/). No support is provided here on the wordpress.org website.

myCRED is a adaptive points management tool to help you build reward programs, monetize your website or just reward users with points for posting comments or publishing content automatically.

Packed with features, myCRED also supports some of the most popular WordPress plugins like WooCommerce, BuddyPress, Jetpack, Contact Form 7 and [more](http://mycred.me/about/supported-plugins/).


= Points =

Each user on your WordPress websites gets their own point balance which you can manually [adjust](http://mycred.me/about/features/easy-manual-adjustments/) at any time. As of version 1.4, myCRED also supports multiple point types for those who need more then one type of points on their website.


= Log =

Each time myCRED adds or deducts points from a user, the adjustment is [logged](http://mycred.me/about/features/the-log/) in a dedicated log, allowing your users to browse their history. The log is also used to provide you with statistics or enforce limits you set.


= Awarding or Deducting Points Automatically =

myCRED supports a vast set of ways you can automatically give / take points from a user. Everything from new comments to store purchases. These automatic adjustments are managed by so called Hooks which you can setup in your admin area.


= Third-party plugin Support =

myCRED supports some of the most [popular plugins](http://mycred.me/about/supported-plugins/) for WordPress like BuddyPress, WooCommerce, Jetpack, Contact Form 7 etc. To prevent to much cluttering in the admin area with settings, myCRED will only show features/settings for third-party plugins that are installed and enabled.


= Add-ons =

There is so much more to myCRED then just adjusting balances. The plugin comes with several [built-in add-ons](http://mycred.me/add-ons/) which enabled more complex features such as allowing point transfers, buying points for real money, allow payments in stores etc.

To help fund development of myCRED, I also provide an ever growing set of [premium add-ons](http://mycred.me/store/) that brings you even more features or add support for more payment gateways.


= Documentation =

You can find [documentation](http://mycred.me/documentation/) on the myCRED.me website or if you are a developer, in the myCRED [Codex](http://codex.mycred.me/). Each add-on also has a dedicated page on the myCRED website with installation guides.


= Tutorials =

You can find several tutorials at [http://mycred.me/support/tutorials/](http://mycred.me/support/tutorials/).


= Code Snippets =

The most commonly asked customizations for myCRED are available as code snippets on the [myCRED website](http://mycred.me/code-snippets/), free to use by anyone.


= Support =

I provide free technical support via the [myCRED website](http://mycred.me/support/) Monday to Friday 9 AM - 5 PM (GMT+1). Support is **NOT** provided here on the wordpress.org support forum or on any social media site. I only provide support for the myCRED copy offered here as long as you have not made any changes to the plugin files. Clones, forks or copies of this plugin is not supported.


== Installation ==

= myCRED Guides =

[myCRED Codex - Setup Guides](http://codex.mycred.me/get-started/)

[myCRED Codex - Install](http://codex.mycred.me/get-started/install/)

[myCRED Codex - Setup Hooks](http://codex.mycred.me/get-started/setup-hooks/)

[myCRED Codex - Setup Addons](http://codex.mycred.me/get-started/setup-addons/)

[myCRED Codex - Multiple Point Types](http://codex.mycred.me/get-started/multiple-point-types/)

[myCRED Codex - Multisites](http://codex.mycred.me/get-started/multisites/)


== Frequently Asked Questions ==

= Does myCRED support Multisite Installations? =

Yep! myCRED also offers you the option to centralize your log or enforce your main sites installation on all sub sites via the "Master Template" feature.

= Can I as an administrator adjust my users balances? =

Yes of course. Administrators have full access to all users point types, balances and history.

= Does myCRED support Multiple Point Types? =

Yes! myCRED as of version 1.4 officially supports multiple point types. You can setup an unlimited number of point types with it's own settings, available hooks and log page for each administration. Note that add-ons have limited support. Please consult the myCRED website for more information.

= What point formats does myCRED support? =

myCRED supports whole numbers or the use of decimals (max 20 decimal places). You can setup to use both if you use multiple point types however the default point type must be set to use the highest number of decimal places.

= How many point types does myCRED support? =

There is no built-in limit for how many point types you can setup, however, with that being said, I do not recommend more then 5-6 types. Remember that myCRED will add an admin menu for each point type so with large sets of point types, you will have a very long admin menu.

= Can users use points to pay for items in my store? =

Yes, myCRED supports WooCommerce, MarketPress and WP E-Commerce straight out of the box. If you want users to pay for event tickets myCRED also supports Events Manger and Event Espresso.

You can find more commonly asked questions at [http://mycred.me/about/faq/](http://mycred.me/about/faq/).


== Screenshots ==

1. **The Log** - myCRED Logs everything for you. You can browse, search, export, edit or delete log entries.
2. **Add-ons** - Enable only the features you want to use.
3. **Hooks** - Instances where you might want to award or deduct points from users are referred to as a "hook".
4. **Settings** - As of version 1.4 you can create multiple point types!
5. **Edit Balances** - While browsing your users in the admin area you always adjust their point balances.


== Upgrade Notice ==

= 1.6.9 =
Big fixes & preparation for 1.7


== Other Notes ==

= Requirements =
* WordPress 3.8 or greater
* PHP version 5.3 or greater
* PHP mcrypt library enabled
* MySQL version 5.0 or greater

= Language Contributors =
* Swedish - Gabriel S Merovingi
* French - Chouf1 [Dan - BuddyPress France](http://bp-fr.net/)
* Persian - Mani Akhtar
* Spanish - Robert Rowshan [Website](http://robertrowshan.com)
* Russian - Skladchik
* Chinese - suifengtec [Website](http://coolwp.com)
* Portuguese (Brazil) - Guilherme
* Japanese - Mochizuki Hiroshi


== Changelog ==

= 1.6.9 =
* FIX - mycred_point_type_exists function declines total balances.
* FIX - %order_id% and %order_link% tags return an error if the order has been deleted.
* FIX - Added GROUP BY in mycred_best_user shortcode query.
* FIX - Re-added missing table classes for the log. Re-enables bootstrap support.
* FIX - Best User shortcode uses incorrect variable for %total% and %tatal_abs% template tags.
* FIX - Adjusted misspelled settings label for BuddyPress module.
* FIX - buyCRED Purchase history page shows n/a for the amount a user paid for a purchase.
* FIX - mycred_get_types() uses the mycred_get_point_type_name() function incorrectly, causing multiple point types first menu to use singular form instead of plural.
* TWEAK - Points for viewing content hook was moved to template_redirect. This should resolve multiple posts triggering point payouts when is_singular().


= Previous Versions =
http://mycred.me/support/changelog/