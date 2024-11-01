=== WP Green Cache ===

Plugin Name: WP Green Cache
SSL Compatible: yes
WordPress Compatible: yes
Tested up to: 3.2.1
Requires at least: 3.1
Requires: WordPress 3.1+, PHP 5.2.3+
Copyright: (c) 2010 Tankado.com
License: GNU General Public License
Contributors: Ozgur Koca, tankado@tankado.com
Facebook: http://www.facebook.com/zerostoheroes
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=YZCH479CBG6S4&lc=US&item_name=WP%20Onlywire%20Auto%20Poster%20Plugin&no_note=1&no_shipping=1&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHosted%20--%3E
Tags: performance, cache, caching, cacher, wp cache, wp, green cache, WordPress green cache, simple cache, wp simple cache
Stable tag: trunk

Fastest cache system for WordPress Blogs to improve performance and save the World.

== Description ==

WP Green Cache is a `powerfull caching plugin` for WordPress blogs. It's higly minimize (%95) the usage of server's resources and `provides very fast blog pages`. If you care about the speed of your site, WP Green Cache is one of those plugins that you `absolutely MUST have installed`.

To explain some tecnical; WP infrastructure has tens of thousands of lines PHP codes which executing on `every request`. To generate requested page WordPress `executes the all of PHP codes`. At this stage, WP Green Cache redirects the requests to own cache system and answers the request fastest as much as possible because the cached pages are not generated again. 

![Save The World with WP Green Cache](http://tankado.com/projects/my_wp_plugins/wp_green_cache/greencache_logo_x450.jpg)

Also WP Green Cache Plugin highly reduce unnecessary usage of server resources so reduces electricity consumption and carbon emmisions. You think that there are hundreds of pages on blog and there are tens of millions blogs and visitors who waiting for pages... Use WP Green Cache and save The World!

**Some features:**

* `It's easy & simple`
* `Saves The World!`
* `Quick and easy installation/configuration`
* Compressing on cache file
* Compressing http traffic
* Fast load and reduces bandwidth usage
* Performance box which shows site performance (sql+exec time)

**Keywords:** [WP Green Cache](http://www.tankado.com/wp-green-cache/), [cache](http://www.tankado.com/wp-green-cache/), [caching](http://www.tankado.com/wp-green-cache/), [compress](http://www.tankado.com/wp-green-cache/), [database cache](http://www.tankado.com/wp-green-cache/), [disk cache](http://www.tankado.com/wp-green-cache/), [eacclerator](http://www.tankado.com/wp-green-cache/), [gzip](http://www.tankado.com/wp-green-cache/), [http compression](http://www.tankado.com/wp-green-cache/), [page cache](http://www.tankado.com/wp-green-cache/), [performance](http://www.tankado.com/wp-green-cache/), [wp-cache](http://www.tankado.com/wp-green-cache/), [w3 total cache](http://www.tankado.com/wp-green-cache/), [wp-super-cache](http://www.tankado.com/wp-green-cache/), [WP-Cache](http://www.tankado.com/wp-green-cache/)
, [WP Plugin Cache](http://www.tankado.com/wp-green-cache/), [DB Cache Reloaded](http://www.tankado.com/wp-green-cache/), [WordPress performance](http://www.tankado.com/wp-green-cache/)

More can be read on the [official plugin page](http://www.tankado.com/wp-green-cache/) also write any issue as comment.

**Check out my other plugins**:

* [WP Onlywire Auto Poster](http://www.tankado.com/onlywire-auto-poster-WordPress-eklentisi/ "Autosubmits a excerpt of a posts to Onlywire when the post published.")
* [WP MySQL Console](http://www.tankado.com/wp-mysql-console/ "WP MySQL Console is a web shell to operate databases such as mysql command shell.")

**This plugin, and all support, is supplied for free, but [donations](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=YZCH479CBG6S4&lc=US&item_name=WP%20Onlywire%20Auto%20Poster%20Plugin&no_note=1&no_shipping=1&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHosted%20--%3E) are always welcome.**

== Installation ==

**Before install you must check the file system permissions. Please check below:**

1. "/wp-content/" directory must be writable (0777)
2. "/wp-content/plugins/wp-green-cache/" must be writable (0777)
3. "/wp-content/plugins/wp-green-cache/cache/" must be writable (0777)
4. "/wp-config.php" must be writable (0777)

**WP Green Cache is very easy to install:**

1. Put the plugin folder into "/wp-content/plugins/"
2. Go into the WordPress admin interface and activate the plugin
3. Go to the options page and enabled it
4. Its ok!

== Manual Installation ==

1. Put the plugin folder into "/wp-content/plugins/"
2. Upload advanced-cache.php file to "/wp-content/"
3. Make "/wp-content/plugins/wp-green-cache/cache/" directory writable (0777)
4. Add this line to the wp-config.php file ("/wp-config.php"): define('WP_CACHE', true);
5. Set file access permision to 640 for wp-config.php (for security)
5. Go into the WordPress admin interface and activate the plugin
6. Go to the options page and enabled it

== How to manually uninstall WP Green Cache? ==

1. Deactivate plugin
2. Remove plugin directory /wp-content/plugins/wp-green-cache/
3. Remove /wp-content/advanced-cache.php
4. Remove the WP_CACHE define from wp-config.php. It looks like `define( 'WP_CACHE', true );`

That's all!

== Frequently Asked Questions ==

= How can I help your GPL plugin? =

Please link to [WP Green Cache](http://www.tankado.com/wp-green-cache/ "WP Green Cache") and introduce it on your blog or [donate](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=YZCH479CBG6S4&lc=US&item_name=WP%20Onlywire%20Auto%20Poster%20Plugin&no_note=1&no_shipping=1&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHosted%20--%3E "Donate for chuck") for chuck (my dog).

= Where can I get support about WP Green Cache Plugin? =

Write your questions as comment at [offical plugin page](http://www.tankado.com/wp-green-cache/ "WP Green Cache").

= Who is the developer of this plugin? =

I'm Ozgur (means freedom), please [checkout my facebook](http://www.facebook.com/zerostoheroes "Ozgur Koca").

= What is different between WP Green Cache and others ? =

* It's simple 
* Automatic installation
* Lowest size of PHP code (~29KB)
* It has performance box (easly compare caching performance)
* Fastest trigering on page request

= Pricing and Licensing =

Good news, this plugin is free for everyone! Since its released under GPL, you can use it free of charge on your personal or commercial blog. But if you enjoy this plugin, you can thank me and leave a small [donation](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=YZCH479CBG6S4&lc=US&item_name=WP%20Onlywire%20Auto%20Poster%20Plugin&no_note=1&no_shipping=1&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHosted%20--%3E "Pleade donate to motivate") for the time I have spent writing and supporting this plugin. And I realy dont want to know how many hours of my life this plugin has already eaten ;) 
[Donate](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=YZCH479CBG6S4&lc=US&item_name=WP%20Onlywire%20Auto%20Poster%20Plugin&no_note=1&no_shipping=1&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHosted%20--%3E "Pleade donate to motivate")

== Screenshots ==

1. Screenshot of options page
2. Screenshot of performance box when plugin disabled
3. Screenshot of performance box when plugin enabled

== Changelog ==

= 0.1.6 =

* Fixed bugs
* Added more control
* Some cosmetic fixs

= 0.1.5 =

* Fixed bugs

= 0.1.4 =

* Fixed bugs

= 0.1.3 =

* Fixed bugs

= 0.1.2 =

* Fixed bugs
* Carbon emission calculator ;)
* Using Linfo - PHP server health/information script

= 0.1.1 =

* Fixed bugs
* Feature: Handling trashed posts
* Feature: Deleting all categories's cache on any update

= 0.1 =

* First relase of WP Green Cache (WP Simple Cache)


== More Info ==

For more info, please visit [WP Green Cache](http://www.tankado.com/wp-green-cache/ "WP Green Cache") or mail me at <tankado@tankado.com>