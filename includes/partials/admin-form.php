<div class="wrap">
	<h1><?php _e( 'Date Archives', 'custom-post-type-date-archives' ); ?></h1>

	<?php settings_errors(); ?>

	<form method="post" action="">
		<table class='form-table'>
			<?php wp_nonce_field( "custom_post_type_date_archives_{$post_type}_nonce" ); ?>
			<tr valign='top'>
				<th scope='row'><?php _e('Date Archives', 'custom-post-type-date-archives') ?></th>
				<td>
					<fieldset>
						<legend class="screen-reader-text">
							<span><?php _e('Date archives', 'custom-post-type-date-archives') ?></span>
						</legend>
						<label for="date_archives">
							<input name="date_archives" id="date_archives" type="checkbox" value="1" 
							<?php echo isset( $settings['date_archives'][ $post_type ] ) ? ' checked="checked"' : ''; ?> />
							<?php _e('Add date archives', 'custom-post-type-date-archives') ?><br/>
						</label>
						<p class="description"><?php printf( __( 'Add date archives to the custom post type %s', 'custom-post-type-date-archives' ), $label ); ?></p>
					</fieldset>
				</td>
			</tr>
			<tr valign='top'>
				<th scope='row'></th>
				<td>
					<fieldset>
						<legend class="screen-reader-text">
							<span><?php _e('Publish scheduled posts', 'custom-post-type-date-archives') ?></span>
						</legend>
						<label for="publish_future_posts">
							<input name="publish_future_posts" id="publish_future_posts" type="checkbox" value="1" 
							<?php echo isset( $settings['publish_future_posts'][ $post_type ] ) ? ' checked="checked"' : ''; ?> />
							<?php _e('Publish posts with future dates', 'custom-post-type-date-archives') ?><br/>
						</label>
						<p class="description">
							<?php printf( __( 'Publish new scheduled posts from the post type %s as normal posts.', 'custom-post-type-date-archives' ), $label ); ?>
						</p>
					</fieldset>
				</td>
			</tr>
		</table>
		<?php submit_button(); ?>
	</form>
	<?php include 'admin-info.php'; ?>
</div>