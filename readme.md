# Custom Post Type Date Archives [![Build Status](https://travis-ci.org/keesiemeijer/custom-post-type-date-archives.svg?branch=develop)](http://travis-ci.org/keesiemeijer/custom-post-type-date-archives) #

Version:           2.3.0-alpha  
Requires at least: 3.9  
Tested up to:      4.5  

Add date archives to WordPress custom post types

### Plugin Description
Add Date archives to custom post types right in the dashboard itself. This plugin can be used, among other things, as a super simple events calendar.

Features
* Submenus for each custom post type to add the date archives
* Allow scheduled posts with future dates to be published like normal posts
* Selection of a post type inside the calendar and archive widgets
* Use theme templates specific for custom post type date archives

Example url for a custom post type `events` date archive.
```
https://example.com/events/2015/06/12
```

WordPress doesn't support date archives with [pretty permalinks](https://codex.wordpress.org/Using_Permalinks#Permalink_Types) for custom post types. This plugin provides the rewrite rules needed for custom post types to also have date archives with pretty permalinks.

The cpt date archives use the same theme template files as normal date archives. Extra [template files](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki/Theme-Template-Files) and [template functions](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki/Functions) are available for use in the custom post type date archives.

**Notice** Custom post types must be [registered](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki/Custom-Post-Types) to have archives and be publicly queryable for this plugin to add the date archives.

![Settings Page](/../screenshots/screenshot-1.png?raw=true)

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
* [Theme Template Files](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki/Theme-Template-Files)
* [Custom Post Types](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki/Custom-Post-Types)
* [Filters](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki/Filters)

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