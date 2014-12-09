# Custom Post Type Date Archives

Add date archives to custom post types in your theme's functions.php file. 

WordPress only supports date archives for the `post` post type . With this plugin you can add date archive support for custom post types in your theme's functions.php file with the [add_post_type_support()](http://codex.wordpress.org/Function_Reference/add_post_type_support) function. Add `date-archives` to the supports parameter and this plugin will add the rewrite rules needed for the date archives of the custom post type.

The archives widget will be replaced with a widget where you can select the post type.

Only custom post types that have the `has_archive` set to true can have date archives. 

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