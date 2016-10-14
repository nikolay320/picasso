<!-- form wrapper -->
<div class="bpqa-form-wrapper bpqa-form-wrapper-lightblue">

	<!-- form -->
	<form class="bpqa-form bpqa-form-lightblue" action="" method="post">
	           
		<?php do_action( 'bpqa_post_form_start' ); ?>
	      
	    <!-- Text area -->  
		<textarea name="bpqa_textarea" class="bp-suggestions bpqa-textarea bpqa-input" autofocus="true" placeholder="<?php echo $bpqa_form_labels['text_area_placeholder']; ?>"></textarea>
	    
	    <!-- Max Characters wrapper -->
	    <?php if ( !empty( $bpqa_options['form']['max_characters'] ) ) { ?>
	    	<div class="bpqa-characters-count">
	    		<span><?php echo $bpqa_options['form']['max_characters']; ?></span> 
	    		<?php echo $bpqa_form_labels['characters_remaining']; ?>
	    	</div>
	    <?php } ?>    
	    
	    <!-- Suggested Results -->
		<div class="bpqa-suggested-results"></div>
		
		<div class="clear"></div>
		
		<div class="bpqa-form-footer-wrapper">
	    	
	    	<!-- Groups Dropdown -->
	  		<?php if ( !empty( $bpqa_options['form']['groups_publish'] ) ) { ?>
		    	
		    	<?php do_action( 'bpqa_post_form_before_groups' ); ?>
		    		    
		    	<div class="bpqa-post-in-wrapper"> 
		      		<select class="bpqa-whats-new-post-in bpqa-form-select" name="bpqa_post_in">
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
	       	<div class="bpqa-buttons-wrapper">
	        	<input type="button" class="bpqa-submit bpqa-popup-submit button" value="<?php echo $bpqa_form_labels['submit_button']; ?>" name="bpqa_submit">
	        	<input type="button" class="bpqa-cancel bpqa-popup-cancel button" value="<?php echo $bpqa_form_labels['cancel_button']; ?>" />
	    	</div>
		</div>
	            
	    <input type="hidden" name="bpqa_action" value="submit" />
	    
		<?php do_action( 'bpqa_post_form_end' ); ?>		
		<?php wp_nonce_field( 'bpqa_submit_form', 'bpqa_update_activity' ); ?>            
	</form>
</div>