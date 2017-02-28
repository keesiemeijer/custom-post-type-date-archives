<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>

<?php if ( $show_post_types ) : ?>
<p><label for="<?php echo $this->get_field_id( 'post_type' ); ?>"><?php _e( 'Post Type:', 'custom-post-type-date-archives' ); ?></label> <select name="<?php echo $this->get_field_name( 'post_type' ); ?>" id="<?php echo $this->get_field_id( 'post_type' ); ?>" class="widefat">
		<?php foreach ( $post_types as $name => $label ) : ?>
		<option value="<?php echo $name; ?>" <?php selected( $post_type, $name ); ?>>
			<?php echo $label; ?>
		</option>
		<?php endforeach; ?>
	</select>
</p>
<?php endif; ?>

<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to show:' ); ?></label>
<input class="tiny-text" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="number" step="1" min="1" value="<?php echo $number; ?>" size="3" /></p>

<p><input class="checkbox" type="checkbox" <?php checked( $instance['status_future'], true ); ?> id="<?php echo $this->get_field_id( 'status_future' ); ?>" name="<?php echo $this->get_field_name( 'status_future' ); ?>">
	<label for="<?php echo $this->get_field_id( 'status_future' ); ?>"><?php _e( 'Show posts with future dates only?', 'custom-post-type-date-archives' ); ?></label>
</p>

<p><input class="checkbox" type="checkbox"<?php checked( $show_date ); ?> id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" />
<label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Display post date?' ); ?></label></p>

<p><label for="<?php echo $this->get_field_id( 'posts_empty' ); ?>"><?php _e( 'No posts are found message:' ); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id( 'posts_empty' ); ?>" name="<?php echo $this->get_field_name( 'posts_empty' ); ?>" type="text" value="<?php echo $posts_empty; ?>" /></p>