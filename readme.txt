=== Custom Post Type Date Archives ===
Contributors: keesiemeijer
Tags: date archives
Requires at least: 3.9
Tested up to: 4.2.1
Stable tag: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add date archives to custom post types in your theme's functions.php file.

== Description ==

WordPress only supports date archives for the ***post*** post type . With this plugin you can add date archive support for custom post types in your theme's functions.php file with the [add_post_type_support()](http://codex.wordpress.org/Function_Reference/add_post_type_support) function. Add 'date-archives' to the supports parameter and this plugin will add the rewrite rules needed for the date archives of the custom post type.

The archives and calendar widget will be replaced with a widget where you can select the post type.

The ***has_archive*** parameter set to ***true*** is required for post types to have date archives.