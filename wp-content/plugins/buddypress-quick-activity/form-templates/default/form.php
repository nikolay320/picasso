<!-- form wrapper -->
<div id="bpqa-popup-form-wrapper-default" class="bpqa-form-wrapper bpqa-popup-form-wrapper">

	<!-- form -->
	<form class="bpqa-form bpqa-popup-form" action="" method="post">
	           
		<?php do_action( 'bpqa_post_form_start' ); ?>
	      
	    <!-- Text area -->  
		<textarea name="bpqa_textarea" id="whats-new" class="bpqa-textarea bpqa-input" autofocus="true" placeholder="<?php echo $bpqa_form_labels['text_area_placeholder']; ?>"></textarea>
		
		<!-- Suggested Results -->
		<div class="bpqa-suggested-results"></div>
	    	    
	    <!-- Max Characters wrapper -->
	    <?php if ( !empty( $bpqa_options['form']['max_characters'] ) ) { ?>
	    	<div class="bpqa-characters-count">
	    		<span><?php echo $bpqa_options['form']['max_characters']; ?></span> 
	    		<?php echo $bpqa_form_labels['characters_remaining']; ?>
	    	</div>
	    <?php } ?>    
	    
		<div class="clear"></div>
		
		<div id="bpqa-form-footer-wrapper">
	    	
	    	<!-- Groups Dropdown -->
	  		<?php if ( !empty( $bpqa_options['form']['groups_publish'] ) ) { ?>
		    	
		    	<?php do_action( 'bpqa_post_form_before_groups' ); ?>
		    		    
		    	<div id="bpqa-post-in-wrapper"> 
		      		<select id="whats-new-post-in" class="bpqa-whats-new-post-in bpqa-form-select" name="bpqa_post_in">
		            	<option selected="" value=""><?php echo $bpqa_form_labels['post_in_default']; ?></option>
		            	<option value="0"><?php echo $bpqa_form_labels['post_in_my_profile']; ?></option>
		            	
		                <?php if ( bp_has_groups( 'user_id='.bp_loggedin_user_id().'&type=alphabetical&max=100&per_page=100&populate_extras=0' ) ) { ?>
		                    	
		                	<?php while ( bp_groups() ) : bp_the_group(); ?>
		                	
									<option value="<?php bp_group_id(); ?>">
										<?php bp_group_name(); ?>
									</option>
									
							<?php endwhile; ?>
		                            
		                <?php } ?>
		   			</select>
		    	</div>		    	
		    <?php } else { ?>
		    	<input type="hidden" name="bpqa_post_in" value="0" />
		    <?php } ?>
	                
	     	<?php do_action( 'bpqa_post_form_before_action_buttons' ); ?>
	                
	        <!-- Action buttons -->
	       	<div id="bpqa-buttons-wrapper">
	        	<input type="button" id="bpqa-submit" class="bpqa-submit bpqa-popup-submit button" value="<?php echo $bpqa_form_labels['submit_button']; ?>" name="bpqa_submit">
	        	<input type="button" id="bpqa-cancel" class="bpqa-cancel bpqa-popup-cancel button" value="<?php echo $bpqa_form_labels['cancel_button']; ?>" />
	    	</div>
		</div>
	            
	    <input type="hidden" name="bpqa_action" value="submit" />
	    
		<?php do_action( 'bpqa_post_form_end' ); ?>		
		<?php wp_nonce_field( 'bpqa_submit_form', 'bpqa_update_activity' ); ?>            
	</form>
</div>