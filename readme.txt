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
Add Date archives to custom post types right in the dashboard itself. Allow scheduled posts to be published on a per post type basis. A new option to select a post type is added to the calendar and archive widgets.

This plugin can be used, among other things, as a super simple events calendar.

Example url for a custom post type `events` date archive.
`
https:&#47;&#47;example.com&#47;events&#47;2015&#47;06&#47;12
`

WordPress only supports date archives for the `post` post type. This plugin provides the rewrite rules needed for custom post types to also have date archives.

The new date archives use your existing [date archives theme template files](https://developer.wordpress.org/themes/basics/template-hierarchy/#date). If you need to integrate the custom post types differently you can make use of [functions](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki/Functions) provided by this plugin.

For more information about this plugin visit the [Plugin Documentation](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki)

== Installation ==
Follow these instructions to install the plugin.

1. In your WordPress admin panel, go to Plugins > New Plugin, search for "custom post type date archives" and click "Install now".
2. Alternatively, download the plugin and upload the contents of custom-post-type-date-archives.zip to your plugins directory, which usually is /wp-content/plugins/.
3. Activate the plugin
4. Add date archives in the "Date Archives" sub menu of a custom post type.

== Frequently Asked Questions ==

= Where do I add the date archives? =
You add the date archives in the "Date Archives" sub menu of a custom post type.

= I don't see the sub menu to add date archives? =
If you don't see the "Date Archives" sub menu in the menu of a custom post type, the post type is probably [registered](https://codex.wordpress.org/Function_Reference/register_post_type) to not be public or not have archives. See [Custom Post Types](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki/Custom-Post-Types)

== Changelog ==
= 2.1.0 =
Initial Commit