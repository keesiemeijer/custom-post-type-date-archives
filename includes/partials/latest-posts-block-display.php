<?php
foreach ( $recent_posts as $recent_post ) : 
	$permalink  = get_permalink( $recent_post );
	$post_title = get_the_title( $recent_post );
	$post_title = $post_title ? $post_title : $recent_post;

	if ( ! ( $permalink && $post_title ) ) {
		continue;
	}

	if ( $args['show_date'] ) {
		$datetime = esc_attr( get_the_date( 'c', $recent_post ) );
		$date     = esc_html( get_the_date( '', $recent_post ) );
	}
?>
	<li>
		<a href="<?php echo $permalink; ?>"><?php echo $post_title; ?></a>
	<?php if ( $args['show_date'] ) : ?>
		<time datetime="<?php echo $datetime; ?>" class="wp-block-latest-posts__post-date"><?php echo $date; ?></time>
	<?php endif; ?>
	</li>
<?php endforeach; ?>