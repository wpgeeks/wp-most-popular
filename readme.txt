=== WP Most Popular ===
Contributors: MattGeri
Tags: popular, most viewed, popular mosts, most viewed posts
Requires at least: 3.0
Tested up to: 3.3.1
Stable tag: 0.1

WP Most Popular is a simple plugin which tracks your most popular blog posts based on views and lets you display them in your blog theme or blog sidebar.

== Description ==

WP Most Popular was born out of frustration in finding a decent plugin which performs one simple task and that is to rank your most popular blog posts.

The plugin keeps a log of your most popular posts based on views and lets you display them in your blog theme with custom styling. You can display popular posts from the last day, 7 days, 30 days or all time.

It also comes with a sidebar widget to let you display your popular posts on your blogs sidebar.

== Installation ==

Setting up WP Most Popular is very simple. Follow these easy steps

1. Upload the plugin to your `/wp-content/plugins/` directory
2. Activate the plugin in your WordPress admin
3. Add sidebar widget or integrate functions in to your theme

== FAQ ==

= What are the minimum requirements for the plugin? =

You will need a web server or shared host that supports a version 5 or newer of PHP. Javascript is also required to log post views.

= Why does the plugin use Javascript to track the post views? =

The original version of the plugin that I wrote used PHP to track the post views and the reason why I switched to Javascript was because if a caching plugin is enabled on your blog, the page will be loaded statically to your visitor and the PHP code to log a view on a post will not be run.

= Can I request a feature? =

Yes, please do so on the WordPress support forum for the plugin. I will consider it and if I feel it is worth adding, I will schedule it for a future release.

= Can I contribute code to the plugin? =

Yes! The plugin is open source and I host it on [Github](https://github.com/MattGeri/WP-Most-Popular). Feel free to send me pull requests.

== Changelog ==

= 0.1 =
* First version of the plugin released