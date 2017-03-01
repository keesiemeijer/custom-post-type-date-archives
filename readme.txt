=== Custom Post Type Date Archives ===
Contributors: keesiemeijer
Tags: post type,date,archives,events,calendar
Requires at least: 4.0
Tested up to: 4.7
Stable tag:  2.4.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add date archives to WordPress custom post types

== Description ==
Add Date archives to custom post types right in the dashboard itself. This plugin also provides you with a calendar, archive and recent posts widget. This allows you to use this plugin as a super simple events calendar.

**Features**:

* Adds a date archives submenu for each custom post type
* Adds the rewrite rules needed for viewing the date archives
* Adds a calendar, archive and recent posts widget
* Allows you to publish scheduled posts with future dates like normal posts
* Allows you to use specific theme templates files for cpt date archives

[Plugin Documentation](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki)

WordPress doesn't support date archives for custom post types out of the box. This plugin adds the rewrite rules needed to view the date archives at a [pretty permalink](https://codex.wordpress.org/Using_Permalinks#Permalink_Types).

Example permalink (url) for a custom post type `events` date archive.
`
https:&#47;&#47;example.com&#47;events&#47;2015&#47;06&#47;12
`
The calendar, archive and recent posts widget are similar to the existing WordPress widgets, but with extra options added.

The cpt date archives use the same theme template files as the normal WordPress date archives. Extra [template files](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki/Theme-Template-Files) and [template functions](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki/Functions) are available for use in the custom post type date archives.

For more information visit the [Plugin Documentation](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki)

== Installation ==
Follow these instructions to install the plugin.

1. In your WordPress admin panel, go to Plugins > New Plugin, search for "custom post type date archives" and click "Install now".
2. Alternatively, download the plugin and upload the contents of custom-post-type-date-archives.zip to your plugins directory, which usually is /wp-content/plugins/.
3. Activate the plugin
4. Add date archives in the "Date Archives" sub menu of a custom post type.

== Frequently Asked Questions ==
For more information about this plugin visit the [Plugin Documentation](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki)

= Where do I add the date archives? =
You add the date archives in the "Date Archives" sub menu of a custom post type.

= I don't see the sub menu to add date archives? =
If you don't see the "Date Archives" sub menu in the menu of a custom post type, the post type is probably [registered](https://codex.wordpress.org/Function_Reference/register_post_type) to not be public or not have archives. See [Custom Post Types](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki/Custom-Post-Types)

== Screenshots ==

1. Date archives settings page for the custom post type Products.
2. The Calendar widget
3. The Archives widget
4. The Recent Posts widget

== Changelog ==
= 2.4.0 =
* Enhancement
	* Add recent posts widget
= 2.3.1 =
* Enhancement
	* Add selective refresh for widgets in the customizer

= 2.3.0 =
* Enhancement
	* Let WordPress create the rewrite rules for the date archives.
	* Add more control over creating feeds for the date archives (filter)
	* Flush rewrite rules after settings are updated (wp-admin)
	* More PHPUnit tests (github)

= 2.2.1 =
* Enhancement
	* Flush rewrite rules when date archives are removed in admin page
	* Update help section with WordPress repo links
	* Add screenshots

= 2.2.0 =
Initial Commit

== Upgrade Notice ==
= 2.4.0 =
This update adds a recent posts widget.