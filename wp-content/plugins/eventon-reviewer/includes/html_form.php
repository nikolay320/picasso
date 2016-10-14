<?php 
/**
 * HTML form for Reviewer
 * @version 0.2
 */
	
	$active_fields = !empty($this->opt['evore_fields'])? explode(',',$this->opt['evore_fields']): false;
?>
<div id='evore_get_form'>
	<div id='evore_form_section' class='evore_form_section' style='display:none' data-cal_id='' data-eid='' data-ri='' >
		<div id='evore_form' class=''>
			<p id='evore_form_close'>X</p>		
			<div class='review_submission_form'>
				<h3 class="form_header"><?php echo $this->replace_en( $this->lang('evoREL_x8','Write a review for [event-name]'));?></h3>
				<p class='star_rating'><?php echo $this->functions->get_star_rating_html(1);?><input class='input' type='hidden' name='rating' value='1'/></p>
				
				<?php if($active_fields && in_array('name', $active_fields)): ?>
					<p><label for=""><?php echo $this->lang( 'evoREL_x9','Your Name');?></label><input class='input' name='name' type="text" value=''></p>
				<?php endif;?>

				<p><label for=""><?php echo $this->lang( 'evoREL_x10','Your Email Address');?><?php echo (!empty($this->opt['evore_email_req']) && $this->opt['evore_email_req']=='yes')? ' *':'';?></label><input class='input inputemail <?php echo (!empty($this->opt['evore_email_req']) && $this->opt['evore_email_req']=='yes')? 'req':'';?>' name='email' type="text" value=''></p>
				
				<?php if($active_fields && in_array('review', $active_fields)): ?>
					<p><label for=""><?php echo $this->lang( 'evoREL_x11','Event Review Text');?><?php echo (!empty($this->opt['evore_review_req']) && $this->opt['evore_review_req']=='yes')? ' *':'';?></label><textarea class='input<?php echo (!empty($this->opt['evore_review_req']) && $this->opt['evore_review_req']=='yes')? ' req':'';?>' name="review" id="" cols="30" rows="10"></textarea></p>
				<?php endif;?>
				
				<?php 
				if($active_fields && in_array('validation', $active_fields)):
					// validation calculations
					$cals = array(	0=>'3+8', '5-2', '4+2', '6-3', '7+1'	);
					$rr = rand(0, 4);
					$calc = $cals[$rr];
				?>
					<div class="form_row captcha">
						<p><?php echo $this->lang( 'evoREL_x12','Verify you are a human:');?> <?php echo $calc;?> = ?<input type="text" data-cal='<?php echo $rr;?>' class='regular_a captcha'/></p>
					</div>
				<?php endif;?>

				<p><a id='submit_review_form' class='evcal_btn evore_submit'><?php echo $this->lang( 'evoREL_x13','Submit');?></a></p>

				<?php if($active_fields && in_array('terms', $active_fields) && !empty($this->opt['evore_termscond_text'])): ?>
					<p><a href='<?php echo $this->opt['evore_termscond_text'];?>' target='_blank'><?php echo $this->lang( 'evoREL_x12a','Terms & Conditions');?></a></p>
				<?php endif;?>
			</div>

			<!-- Success review confirmation -->
			<div class='review_confirmation form_section' style="display:none" data-rsvpid=''>
				<b></b>
				<p><?php echo $this->lang( 'evoREL_x4','Thank you for submitting your review');?> <span class='name'></span></p>
			</div>
			<!-- form messages -->			
			<div class="form_row notification" style='display:none'><p></p></div>		
			<?php echo $this->get_form_msg($this->opt2);?>
		</div>
	</div>
</div>