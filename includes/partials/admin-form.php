<form method="post" action="">
	<table class='form-table'>
		<?php wp_nonce_field( "custom_post_type_date_archives_{$post_type}_nonce" ); ?>
		<tr valign='top'>
			<th scope='row'><?php _e('Date Archives', '') ?></th>
			<td>
				<label for="date_archives">
					<input name="date_archives" id="date_archives" type="checkbox" value="1" 
					<?php echo isset( $settings['date_archives'][$post_type] ) ? ' checked="checked"' : ''; ?> />
					<?php _e('Add date archives to this post type', '') ?><br/>
				</label>
			<p class="description"><?php printf( __( 'Add date archives to the custom post type %s', '' ), $label ); ?></p>
			</td>
		</tr>
		<tr valign='top'>
			<th scope='row'></th>
			<td>
				<label for="publish_future_posts">
					<input name="publish_future_posts" id="publish_future_posts" type="checkbox" value="1" 
					<?php echo isset( $settings['publish_future_posts'][$post_type] ) ? ' checked="checked"' : ''; ?> />
					<?php _e('Publish posts with future dates', '') ?><br/>
				</label>
			
			<p class="description">
				<?php printf( __( 'Publish new scheduled posts from the post type %s as normal posts', '' ), $label ); ?><br/>
			</p>
			</td>
		</tr>
	</table>
    <?php submit_button(); ?>
</form>