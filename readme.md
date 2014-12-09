# Custom Post Type Date Archives

Add date archives to custom post types in your theme's functions.php file. 

WordPress only supports date archives for the `post` post type . With this plugin you can add date archive support for custom post types in your theme's functions.php file with the [add_post_type_support()](http://codex.wordpress.org/Function_Reference/add_post_type_support) function. Add `date-archives` to the supports parameter and this plugin will add the rewrite rules needed for the date archives of the custom post type.

The archives widget will be replaced with a widget where you can select the post type.

The `has_archive` parameter set to `true` is required for post types to have date archives. 

For example, add date archives to our 'events' custom post type.

```php
// Registering the events custom post type.
// And adding date archives support.
function post_type_events_init() {

	$args = array(
		'label'       => 'Events',
		'public'      => true,
		'has_archive' => true, // required for date archives
	);
	register_post_type( 'events', $args );

	// Adding date archives support to the events custom post type.
	add_post_type_support( 'events', array( 'date-archives' ) );
}

add_action( 'init', 'post_type_events_init' );
```

**Note** The function `add_post_type_support()` should be called using the `init` action hook, like in the above example.

## Functions

These are the functions you can use in your theme template files.
```php
// Is the current query for a custom post type date archive?
cptda_is_cpt_date()
```

```php
// Checks if the post type supports date archives.
cptda_is_date_post_type( $post_type = '' )
```

```php
// Get the current date archive custom post type.
cptda_get_date_archive_cpt()
```

```php
// Display archive links based on post type, type and format.
cptda_get_archives( $args = '' )
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

