# Custom Post Type Date Archives [![Build Status](https://travis-ci.org/keesiemeijer/custom-post-type-date-archives.svg?branch=master)](http://travis-ci.org/keesiemeijer/custom-post-type-date-archives) #

Version:           2.4.0  
Requires at least: 4.0  
Tested up to:      4.8  

Add date archives to WordPress custom post types

### Plugin Description
Add Date archives to custom post types right in the dashboard itself. This plugin also provides you with a calendar, archive and recent posts widget. This allows you to use this plugin as a super simple events calendar.

**Features**:

* Adds a date archives submenu for each custom post type
* Adds the rewrite rules needed for viewing the date archives
* Adds a calendar, archive and recent posts widget
* Allows you to publish scheduled posts with future dates like normal posts
* Allows you to use specific theme templates files for cpt date archives

WordPress doesn't support date archives for custom post types out of the box. This plugin adds the rewrite rules needed to view the date archives at a [pretty permalink](https://codex.wordpress.org/Using_Permalinks#Permalink_Types).

Example permalink (url) for a custom post type `events` date archive.
```
https://example.com/events/2015/06/12
```

The calendar, archive and recent posts widget are similar to the existing WordPress widgets, but with extra options added.

The cpt date archives use the same theme template files as the normal WordPress date archives. Extra [template files](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki/Theme-Template-Files) and [template functions](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki/Functions) are available for use in the custom post type date archives.

**Notice** Custom post types must be [registered](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki/Custom-Post-Types) to have archives and be publicly queryable for this plugin to add the date archives.

![Settings Page](https://user-images.githubusercontent.com/1436618/28248708-23656246-6a49-11e7-9591-fdfc63a65ae8.png)

## Documentation
For more information about this plugin see the [Wiki](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki)

* [Adding Date Archives](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki/Adding-Date-Archives)
* [Adding Date Archives in Your Theme](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki/Adding-Date-Archives-in-Your-Theme)
  * [Publish Scheduled Posts](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki/Adding-Date-Archives-in-Your-Theme#publish-scheduled-posts)
  * [Functions](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki/Functions)
  * [Pagination](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki/Pagination)
* [Scheduled Posts](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki/Scheduled-Posts)
  * [Update Scheduled Posts with Post Status Published](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki/Scheduled-Posts#update-scheduled-posts-with-post-status-published)
  * [Reschedule Published Posts with a Future Date](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki/Scheduled-Posts#reschedule-published-posts-with-a-future-date)
  * [Display Scheduled Posts in the Date Archives Only](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki/Scheduled-Posts#display-scheduled-posts-in-the-date-archives-only)
* [Calendars](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki/Calendars)
* [Theme Template Files](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki/Theme-Template-Files)
* [Custom Post Types](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki/Custom-Post-Types)
* [Filters](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki/Filters)
* [Feeds](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki/Feeds)

## Developers
This is the development repository for the Custom Post Type Date Archives plugin.

### Branches
The `master` branch is where you'll find the most recent, stable release.
The `develop` branch is the current working branch for development. Both branches are required to pass all unit tests. Any pull requests are first merged with the `develop` branch before being merged into the `master` branch.

### Pull Requests
When starting work on a new feature, branch off from the `develop` branch.
```bash
# clone the repository
git clone https://github.com/keesiemeijer/custom-post-type-date-archives.git

# cd into the custom-post-type-date-archives directory
cd custom-post-type-date-archives

# switch to the develop branch
git checkout develop

# create new branch newfeature and switch to it
git checkout -b newfeature develop
```

### Creating a new build
To compile the plugin without all the development files use the following commands:
```bash
# Go to the master branch
git checkout master

# Install Grunt tasks
npm install

# Build the production plugin
grunt build
```
The plugin will be compiled in the `build` directory.

### Bugs
If you find an issue, let us know [here](https://github.com/keesiemeijer/custom-post-type-date-archives/issues?state=open)!