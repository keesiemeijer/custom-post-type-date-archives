<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
</p>

<?php if ( $show_post_types ) : ?>
<p>
	<label for="<?php echo $this->get_field_id( 'post_type' ); ?>">
		<?php _e( 'Post Type:', 'custom-post-type-date-archives' ); ?>
	</label> 
	<select name="<?php echo $this->get_field_name( 'post_type' ); ?>" id="<?php echo $this->get_field_id( 'post_type' ); ?>" class="widefat">
		<?php foreach ( $post_types as $name => $label ) : ?>
		<option value="<?php echo esc_attr( $name ); ?>" <?php selected( $post_type, $name ); ?>>
			<?php echo $label; ?>
		</option>
		<?php endforeach; ?>
	</select>
</p>
<?php endif; ?>

<p>
	<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to show:' ); ?></label>
	<input class="tiny-text" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="number" step="1" min="1" value="<?php echo esc_attr( $number ); ?>" size="3" />
</p>

<p>
	<input class="checkbox" type="checkbox"<?php checked( $show_date ); ?> id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" />
	<label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Display post date?' ); ?></label>
</p>

<p>
	<label for="<?php echo $this->get_field_id( 'include' ); ?>">
		<?php _e( 'Include Posts:', 'custom-post-type-date-archives' ); ?>
	</label> 
	<select name="<?php echo $this->get_field_name( 'include' ); ?>" id="<?php echo $this->get_field_id( 'include' ); ?>" class="widefat">
		<?php foreach ( $this->include as $name => $label ) : ?>
		<option value="<?php echo esc_attr( $name ); ?>" <?php selected( $include, $name ); ?>>
			<?php echo $label; ?>
		</option>
		<?php endforeach; ?>
	</select>
</p>

<p>
	<label for="<?php echo $this->get_field_id( 'message' ); ?>"><?php _e( 'No posts found message:' ); ?></label>
	<textarea class="widefat" rows="4" cols="20" id="<?php echo $this->get_field_id( 'message' ); ?>" name="<?php echo $this->get_field_name( 'message' ); ?>"><?php echo esc_textarea( $message ); ?></textarea>
</p>
<p class="description">
	<?php _e( 'This message is displayed when no posts are found', 'custom-post-type-date-archives' ); ?>. <?php _e( 'Leave blank to not display the widget when no posts are found', 'custom-post-type-date-archives' ); ?>.
</p>
