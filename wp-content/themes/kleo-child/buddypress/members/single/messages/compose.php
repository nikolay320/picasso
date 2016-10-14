<form action="<?php bp_messages_form_action('compose' ); ?>" method="post" id="send_message_form" class="standard-form" role="main" enctype="multipart/form-data">

    <?php

    /**
     * Fires before the display of message compose content.
     *
     * @since BuddyPress (1.1.0)
     */
    do_action( 'bp_before_messages_compose_content' ); ?>

	<label for="send-to-input"><?php _e("Send To (Username or Friend's Name)", 'buddypress' ); ?></label>
	<ul class="first acfb-holder">
		<li>
			<?php bp_message_get_recipient_tabs(); ?>
			<style>
				#atwho-container {
					position: relative;
					z-index: 999999 !important;
				}
				.compose label.wh-new {
    display: none !important;
}
			</style>			
			<div id="whats-new-textarea">
				<input type="text" name="send-to-input" class="send-to-input bp-suggestions testing" id="send-to-input whats-new" value="<?php if ( isset( $_GET['r'] ) ) : ?><?php echo $_GET['r']; ?><?php endif; ?>" />
				<label class="wh-new" for="subject">Faites apparaitre la liste des membres en utilisant @abc</label>
			</div>
		</li>
	</ul>

	<?php if ( bp_current_user_can( 'bp_moderate' ) ) : ?>
		<input type="checkbox" id="send-notice" name="send-notice" value="1" /> <?php _e( "This is a notice to all users.", "buddypress" ); ?>
	<?php endif; ?>

	<label for="subject"><?php _e( 'Subject', 'buddypress' ); ?></label>
	<input type="text" name="subject" id="subject" value="<?php bp_messages_subject_value(); ?>" />

	<label for="content"><?php _e( 'Message', 'buddypress' ); ?></label>
	<textarea name="content" id="message_content" rows="6" cols="40"><?php bp_messages_content_value(); ?></textarea>

	<input type="hidden" name="send_to_usernames" id="send-to-usernames" value="<?php bp_message_get_recipient_usernames(); ?>" class="<?php bp_message_get_recipient_usernames(); ?>" />

	<span class="bp-smiley-button-message" style="display: inline;">
	  <a class="buddypress-smiley-button"><i class="dashicons dashicons-smiley"></i></a>
	  </span>
	  <span class="bp-smiley-no-message" style="display: none;">
	  <a class="buddypress-smiley-button"><i class="dashicons dashicons-no"></i></a>
	  </span>
	  <div id="sl-message" class=""></div>
	  <style>
	 .bp-smiley-button-message a,.bp-smiley-no-message a
	 {	 
	 border: 1px solid #fe5815;
    border-radius: 50%;
    cursor: pointer;
    float: right;
    height: 28px;
    margin-left: 5px;
    padding: 2px;
    width: 28px;
	 }
	  </style>
	  
	  <script>
	  jQuery('.bp-smiley-button-message').click(function(){
		jQuery(this).hide();
		jQuery('.bp-smiley-no-message').show();
		  jQuery.ajax({
     url: ajaxurl,
	 type: 'post',
	 data: {'action': 'bp_sticker_ajax' },	
            success: function (html) {                
				  jQuery(' #sl-message').toggleClass ('smiley-buttons')
                  jQuery(".smiley-buttons").html(html);
				 jQuery(".bp-smiley-no-message").click(function() {                       
                         jQuery(".divsti").remove();	
						 jQuery('.bp-smiley-button-message').show();
						 jQuery(' .bp-smiley-no-message').hide();
						  jQuery(' #sl-message').removeClass('smiley-buttons');
						 });
				 
				  
				  }
			});
	});	
	  </script>
    <div style="display: none;"><?php

    /**
     * Fires after the display of message compose content.
     *
     * @since BuddyPress (1.1.0)
     */
    do_action( 'bp_after_messages_compose_content' ); ?></div>

	<div class="submit">
		<input type="submit" value="<?php esc_attr_e( "Send Message", 'buddypress' ); ?>" name="send" id="send" />
	</div>
<div id="sl"></div>
	<?php wp_nonce_field( 'messages_send_message' ); ?>
</form>
<script type="text/javascript">
	document.getElementById("send-to-input").focus();
</script>

