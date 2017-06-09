<ul>
<?php while ( $r->have_posts() ) : $r->the_post(); ?>
	<li>
		<a href="<?php the_permalink(); ?>"><?php get_the_title() ? the_title() : the_ID(); ?></a>
	<?php if ( $show_date ) : ?>
		<span class="post-date"><?php echo get_the_date(); ?></span>
	<?php endif; ?>
	</li>
<?php endwhile; ?>
</ul>
