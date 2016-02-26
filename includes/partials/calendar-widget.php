<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'custom-post-type-date-archives' ); ?></label> <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" placeholder="<?php echo $instance['placeholder']; ?>">
</p>
<?php if ( $show_post_types ) : ?>
<p>
	<label for="<?php echo $this->get_field_id( 'post_type' ); ?>"><?php _e( 'Post Type:', 'custom-post-type-date-archives' ); ?></label> <select name="<?php echo $this->get_field_name( 'post_type' ); ?>" id="<?php echo $this->get_field_id( 'post_type' ); ?>" class="widefat">
		<?php foreach ( $post_types as $name => $label ) : ?>
		<option value="<?php echo $name; ?>" <?php selected( $post_type, $name ); ?>>
			<?php echo $label; ?>
		</option><?php endforeach; ?>
	</select>
</p>
<?php endif; ?>