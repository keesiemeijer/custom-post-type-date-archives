# Custom Post Type Date Archives [![Build Status](https://travis-ci.org/keesiemeijer/custom-post-type-date-archives.svg?branch=develop)](http://travis-ci.org/keesiemeijer/custom-post-type-date-archives) #

Version:           2.1.0  
Requires at least: 3.9  
Tested up to:      4.4  

Add date archives to WordPress custom post types

## Welcome to the GitHub repository for this plugin ##
This is the development repository for the Custom Post Type Date Archives plugin.

## Plugin Description ##
Add Date archives to custom post types right in the dashboard itself. The calendar and archives widget get a new option where you can now select the post type the widget should use. This plugin can be used, among other things, as a super simple events calendar.

Example url for a custom post type `events` date archive.
```
https://example.com/events/2015/06/12
```

By default WordPress only supports date archives for the `post` post type. This plugin provides the rewrite rules needed for custom post types to also have date archives.

This plugin works with your existing [date archives theme template files](https://developer.wordpress.org/themes/basics/template-hierarchy/#date). If you need to integrate the custom post types differently you can make use of [functions](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki/Functions) provided by this plugin.

**Notice** Custom post types must be [registered](https://codex.wordpress.org/Function_Reference/register_post_type) to have archives and be publicly queryable for this plugin to add date archives.

![Settings Page](/../screenshots/screenshot-1.png?raw=true)

## Documentation
For more information about this plugin see the [Wiki](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki)

* [Adding Date Archives](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki/Adding-Date-Archives)
* [Adding Date Archives in Your Theme](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki/Adding-Date-Archives-in-Your-Theme)
  * [Future Dates](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki/Adding-Date-Archives-in-Your-Theme#future-dates)
  * [Functions](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki/Functions)
  * [Pagination](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki/Pagination)
* [Scheduled Posts](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki/Scheduled-Posts)
  * [Update Scheduled Posts with Post Status Published](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki/Scheduled-Posts#update-scheduled-posts-with-post-status-published)
  * [Reschedule Published Posts with a Future Date](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki/Scheduled-Posts#reschedule-published-posts-with-a-future-date)
  * [Only Display Scheduled Posts in the Date Archives](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki/Scheduled-Posts#only-display-scheduled-posts-in-the-date-archives)
* [Custom Post Types](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki/Custom-Post-Types)
* [Filters](https://github.com/keesiemeijer/custom-post-type-date-archives/wiki/Filters)

## Developers

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