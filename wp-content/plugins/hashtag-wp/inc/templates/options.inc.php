<div class="wrap">
	
	<div class="card">
		
		<h1><?= __( 'Hashtag WP', Glcdesign\HashtagWp\Plugin::TEXTDOMAIN ); ?></h1>
		
		<?php if( $post && $saved ): ?>
			
			<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
				<p>
					<strong><?= __('Settings saved.', Glcdesign\HashtagWp\Plugin::TEXTDOMAIN); ?></strong>
				</p>
				<button type="button" class="notice-dismiss">
					<span class="screen-reader-text">Dismiss this notice.</span>
				</button>
			</div>
		
		<?php endif; ?>

		<?php if(  !empty( $messages ) ): ?>

			<?php foreach( $messages as $message ): ?>
			<div id="setting-error-settings_error" class="error settings-error notice is-dismissible">
				<p>
					<strong><?= $message; ?></strong>
				</p>
				<button type="button" class="notice-dismiss">
					<span class="screen-reader-text">Dismiss this notice.</span>
				</button>
			</div>
			<?php endforeach; ?>

		<?php endif; ?>
		
		<form method="POST">
			
			<h3><?= __( 'Options', Glcdesign\HashtagWp\Plugin::TEXTDOMAIN ); ?></h3>
			
			
			<?= wp_nonce_field('save_hashtag_wp', 'save_hashtag_wp_nonce'); ?>
			
			<p><?= __( "You can configure Hashtag WP using this page.", Glcdesign\HashtagWp\Plugin::TEXTDOMAIN ); ?></p>

			<table class="form-table">
				
				<tbody>
				
					<tr>
						<th scope="row"><label for="commentsEnabled"><?= __('Enable hashtags in comments', Glcdesign\HashtagWp\Plugin::TEXTDOMAIN); ?></label></th>
						<td>
							<input type="checkbox" class="postform" name="commentsEnabled" id="commentsEnabled" value="1" <?php echo $commentsEnabled ? 'checked="checked"' : ''; ?>>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="buddypressEnabled"><?= __('Enable BuddyPress support', Glcdesign\HashtagWp\Plugin::TEXTDOMAIN); ?></label></th>
						<td>
							<input type="checkbox" class="postform" name="buddypressEnabled" id="buddypressEnabled" value="1" <?php echo $buddypressEnabled ? 'checked="checked"' : ''; ?>>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="hashtagWidget"><?= __('Enable hashtag widget', Glcdesign\HashtagWp\Plugin::TEXTDOMAIN); ?></label></th>
						<td>
							<input type="checkbox" class="postform" name="hashtagWidget" id="hashtagWidget" value="1" <?php echo $hashtagWidget ? 'checked="checked"' : ''; ?>>
						</td>
					</tr>
				
				</tbody>

			</table>

			<p>
				<input type="submit" name="submit" class="button button-primary" value="<?= __('Save', Glcdesign\HashtagWp\Plugin::TEXTDOMAIN); ?>">
			</p>

			<br>

			<h3><?php _e( 'Thank your for choosing Glcdesign!', Glcdesign\HashtagWp\Plugin::TEXTDOMAIN ); ?></h3>
			
			<p>
				<strong>
					<?php _e( "You are using the Full version and have premium support!", Glcdesign\HashtagWp\Plugin::TEXTDOMAIN ); ?>
				</strong>
			</p>
			
			<h3>
				<a href="http://codecanyon.net/item/hashtag-wp-hashtags-for-wordpress/14351772/support"><?php _e( 'I need support', Glcdesign\HashtagWp\Plugin::TEXTDOMAIN ); ?></a>
			</h3>

		</form>
	
	</div>

</div>