# Custom Post Type Date Archives [![Build Status](https://travis-ci.org/keesiemeijer/custom-post-type-date-archives.svg?branch=master)](http://travis-ci.org/keesiemeijer/custom-post-type-date-archives) #

Version:           2.0.0  
Requires at least: 3.9  
Tested up to:      4.4  

Add date archives to custom post types.

The default archives and calendar widget will be replaced with similar widgets where you can now select a post type.

WordPress only supports date archives for the `post` post type. With this plugin you can add date archive support for custom post types with the [add_post_type_support()](http://codex.wordpress.org/Function_Reference/add_post_type_support) function. Add `date-archives` to the `$supports` parameter and this plugin will add the rewrite rules needed.

Example url for a custom post type `events` date archive.
```
https://example.com/events/2015/06/12
```

When registering a custom post type the `has_archive` parameter is required for it to have date archives added. See the example below. 

For example, add date archives to a `events` custom post type. Put this in your (child) theme's functions.php file. 

```php
function post_type_events_init() {

	$args = array(
		'label'       => 'Events',
		'public'      => true,
		'has_archive' => true, // required for date archives
	);

	// Registering the events custom post type.
	register_post_type( 'events', $args );

	// Adding date archives support to the events custom post type.
	add_post_type_support( 'events', array( 'date-archives' ) );
}

add_action( 'init', 'post_type_events_init' );
```

**Note** The functions [register_post_type()](https://codex.wordpress.org/Function_Reference/register_post_type) and [add_post_type_support()](https://codex.wordpress.org/Function_Reference/add_post_type_support) should be called using the `init` action hook, like in the example above.

## future dates
To allow future dates for a post type include `publish-future-posts` in the `$supports` parameter. (see the code example above)
```php
// Adding date archives and publish future post support for the 'events' custom post type.
add_post_type_support( 'events', array( 'date-archives', 'publish-future-posts' ) );
```

This will set the post status for **newly** published posts with a sceduled **future date** to `publish` instead of `future`. Sceduled (future) posts are no longer hidden in the front end of your site. To update old sceduled posts with the post status `publish` use this [bulk edit trick](http://bobwp.com/bulk-edit-posts-wordpress/) and set the status to `published`.

To reverse this behaviour for all post types use the code below in your (child) theme's functions.php file. 
```php
// Don't publish scheduled posts for all custom post types that support date archives.
// Sceduled posts are still displayed in the custom post type date archives.

add_filter( 'cptda_publish_future_posts', '__return_false' );
```
With this the post status for **newly** published scheduled posts remains `future`. Use the same bulk edit trick as above and set the post status to `published` for posts with a future date. It's counterintuitive to set it to `published`, but it will reschedule the posts with a future date with the post status `future` 

To reverse it for a single post type (called events).
```php
// Don't publish scheduled posts for the post type 'events'.
// Scheduled posts are still displayed in the 'events' date archives.

add_filter( 'cptda_publish_future_events', '__return_false' );
```
Use the same bulk edit trick as above to reschedule future date posts with the post status `future`. 

**Note** If you return false as in the `cptda_publish_future_*` filters examples above, scheduled future posts are **only** displayed in the custom post type date archives. Don't link to the individual single posts in your theme archive templates as it will go to a 404.

## Functions

These are the functions you can use in your theme template files.
See the functions.php file for what each function does.

```php
// Is the current query for a custom post type date archive?
cptda_is_cpt_date()
```

```php
// Checks if a specific post type supports date archives.
cptda_is_date_post_type( $post_type = '' )
```

```php
// Get the posts type for the current custom date archive.
cptda_get_date_archive_cpt()
```

```php
// Get the post stati for a post type thas support date archives. 
// Returns an array with post stati. Default array( 'publish' ).
// If the post type supports 'publish-future-posts' an array with 'publish' and 'future' is returned.
// The post stati can be filtered with the 'cptda_post_stati' filter.

cptda_get_cpt_date_archive_stati( $post_type = '' )
```

```php
// Display archive links based on post type, type and format.
// Similar to wp_get_archives. Use 'post_type' in the $args parameter to set the post type
cptda_get_archives( $args = '' )
```

```php
// Display a calendar for a custom post type with days that have posts as links.
// Similar to the WordPress function get_calendar(). Altered to include a custom post type parameter.
cptda_get_calendar( $post_type = '', $initial = true, $echo = true )
```

```php
// Retrieve the permalink for custom post type year archives.
cptda_get_year_link( $year, $post_type = '' )
```

```php
// Retrieve the permalink for custom post type month archives with year.
cptda_get_month_link( $year, $month, $post_type = '' )
```

```php
// Retrieve the permalink for custom post type day archives with year and month.
cptda_get_day_link( $year, $month, $day, $post_type = '' )
```

## Pagination
Pagination of cpt date archives is the same as normal date archives. For those needing to paginate by year, month or day there is a seperate plugin that does just that.
https://github.com/keesiemeijer/custom-post-type-date-archives-pagination 

