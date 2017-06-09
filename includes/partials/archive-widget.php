<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'custom-post-type-date-archives' ); ?></label> <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" placeholder="<?php echo esc_attr( $this->defaults['title'] ); ?>">
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'limit' ); ?>"><?php _e('Limit', 'custom-post-type-date-archives'); ?></label> <input type="number" class="smallfat code" size="5" min="0" id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" value="<?php echo esc_attr( $instance['limit'] ); ?>" placeholder="10">
</p><?php if ( $show_post_types ) : ?>
<p>
	<label for="<?php echo $this->get_field_id( 'post_type' ); ?>"><?php _e( 'Post Type:', 'custom-post-type-date-archives' ); ?></label> <select name="<?php echo $this->get_field_name( 'post_type' ); ?>" id="<?php echo $this->get_field_id( 'post_type' ); ?>" class="widefat">
		<?php foreach ( $post_types as $name => $label ) : ?>
		<option value="<?php echo $name; ?>" <?php selected( $post_type, $name ); ?>>
			<?php echo $label; ?>
		</option><?php endforeach; ?>
	</select>
</p><?php endif; ?>
<p>
	<label for="<?php echo $this->get_field_id( 'type' ); ?>"><?php _e('Type', 'custom-post-type-date-archives') ?></label> <select class="widefat" id="<?php echo $this->get_field_id( 'type' ); ?>" name="<?php echo $this->get_field_name( 'type' ); ?>">
		<?php foreach ( $type as $option_value => $option_label ) { ?>
		<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $instance['type'], $option_value ); ?>>
			<?php echo esc_html( $option_label ); ?>
		</option><?php } ?>
	</select>
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'order' ); ?>"><?php _e('Order', 'custom-post-type-date-archives') ?></label> <select class="widefat" id="<?php echo $this->get_field_id( 'order' ); ?>" name="<?php echo $this->get_field_name( 'order' ); ?>">
		<?php foreach ( $order as $option_value => $option_label ) { ?>
		<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $instance['order'], $option_value ); ?>>
			<?php echo esc_html( $option_label ); ?>
		</option><?php } ?>
	</select>
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'format' ); ?>"><?php _e('Format', 'custom-post-type-date-archives') ?></label> <select class="widefat" id="<?php echo $this->get_field_id( 'format' ); ?>" name="<?php echo $this->get_field_name( 'format' ); ?>">
		<?php foreach ( $format as $option_value => $option_label ) { ?>
		<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $instance['format'], $option_value ); ?>>
			<?php echo esc_html( $option_label ); ?>
		</option><?php } ?>
	</select>
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'show_post_count' ); ?>">
		<input class="checkbox" type="checkbox" <?php checked( $instance['show_post_count'], true ); ?> id="<?php echo $this->get_field_id( 'show_post_count' ); ?>" name="<?php echo $this->get_field_name( 'show_post_count' ); ?>"> <?php _e( 'Show post count?', 'custom-post-type-date-archives' ); ?>
	</label>
</p>
