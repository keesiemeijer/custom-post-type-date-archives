<?php
foreach ( $recent_posts as $recent_post ) : 
	$permalink  = get_permalink( $recent_post );
	$post_title = get_the_title( $recent_post );
	$post_title = $post_title ? $post_title : $recent_post;

	if ( ! ( $permalink && $post_title ) ) {
		continue;
	}
?>
	<li>
		<a href="<?php echo $permalink; ?>"><?php echo $post_title; ?></a>
	<?php if ( $args['show_date'] ) : ?>
		<span class="post-date"><?php echo get_the_date( '', $recent_post ); ?></span>
	<?php endif; ?>
	</li>
<?php endforeach; ?>