=== Remove Query Strings From Static Resources Like CSS & JS Files ===
Contributors: designvkp
Tags: remove query string, css, js, google pagespeed, remove, query, strings, static, resources, pingdom, gtmetrix, yslow, pagespeed
Requires at least: 3.0.1
Tested up to: 4.4
Stable tag: 1.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Remove query strings from static resources like CSS & JS files in wordpress websites and blogs.

== Description ==
With this plugin you will be able to remove query string from static resources like javascript files and css files without even touching a single line of code. I am developing this plugin because I was getting too many complains about the wordpress themes on my blog. Everyone was complaining that after using these <a rel="dofollow" href="http://wpvkp.com/best-wordpress-magazine-themes/" target="_blank">wordpress magazine themes</a> their website page speed score went down. But they didn't knew that problem was not with theme but with the developers who add versions to better control the updates of css and js files.

**How this plugin actually remove query string from static resources ?**

Well its working is pretty simple. It searches all the static files which are loaded either from your plugins or from your themes for "?" or "&". If it finds them it simply removes them. It won't damage the functionality of your website in any way but it actually helps your website to get better Google page speed score and helps to make your site load way more faster.

**How to configure it ?**

You don't need to do anything. Just install it and forget it. It will do it's task in the backend of your site. You can read more about it on my blog :: [Remove Query String](http://wpvkp.com/wordpress-remove-query-string-css-javascript-js/).

**Will it create extra load on my servers ?**

No, No, No.. it won't. It's made to speed up your website not to slow it down. Don't you worry.


== Installation ==
1. Upload the `remove-query-strings-from-static-resources` folder to the `/wp-content/plugins/` directory

2. Activate the plugin through the 'Plugins' menu in WordPress.

3. That's it!

== Changelog ==

= 1.3 =

Major improvement, now works great with cache plugins and in admin area.

= 1.2 =

Improvements in codes.

= 1.1 =

Added support for admin pages.

= 1.0 =

* First release
