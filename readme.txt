=== Custom Post Type Date Archives ===
Contributors: keesiemeijer
Tags: date archives
Requires at least: 3.9
Tested up to: 4.4
Stable tag: 2.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add date archives to WordPress custom post types

== Description ==
Date archives can be added to custom post types right in the dashboard itself. The calendar and archives widget get a new option where you can now select the post type the widget should use. This plugin can be used, among other things, as a super simple events calendar.

Example url for a custom post type `events` date archive.
`
https:&#47;&#47;example.com&#47;events&#47;2015&#47;06&#47;12
`

By default WordPress only supports date archives for the post type `post`. This plugin provides the rewrite rules needed for custom post type date archives. This plugin works with your existing theme's (date) template files. If you need to integrate the custom post types differently you can make use of [functions](https://github.com/keesiemeijer/custom-post-type-date-archives/tree/master#functions) provided by this plugin.

**Notice** Date archives can only be added to custom post types that are [registered](https://codex.wordpress.org/Function_Reference/register_post_type) to have archives and be publicly queryable.

For more information about this plugin visit the [GitHub repository](https://github.com/keesiemeijer/custom-post-type-date-archives#custom-post-type-date-archives-)

== Installation ==
Follow these instructions to install the plugin.

1. In your WordPress admin panel, go to Plugins > New Plugin, search for "custom post type date archives" and click "Install now".
2. Alternatively, download the plugin and upload the contents of custom-post-type-date-archives.zip to your plugins directory, which usually is /wp-content/plugins/.
3. Activate the plugin
4. Add date archives in the submenu "Date Archives" of a custom post type.

== Frequently Asked Questions ==

= Where do I add the date archives? =
You add the date archives in the submenu "Date Archives" of a custom post type.

= I don't see the option to add date archives? =
If you don't see the submenu "Date Archives" in the menu of a custom post type, the post type is probably [registered](https://codex.wordpress.org/Function_Reference/register_post_type) without `has_archive` or is not publicly queryable on the front end of your site. Register the post type with `public`, `has_archive` and `publicly_queryable` set to `true`.

== Changelog ==
= 2.1.0 =
Initial Commit