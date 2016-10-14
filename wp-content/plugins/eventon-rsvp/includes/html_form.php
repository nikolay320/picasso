<?php 
/**
 * HTML form for RSVP
 * @version 0.2
 */
?>
<div id='evors_get_form'>
	<div id='evors_form_section' class='evors_form_section' style='display:none' data-cal_id='' data-eid='' data-rsvpid='' data-uid='' data-ri='' data-prefillblock='<?php echo (!empty($optRS['evors_prefil_block']) && $optRS['evors_prefil_block']=='yes')?'yes':'no';?>' data-percap=''>
	<div id='evorsvp_form' class=''>
		<p id='evors_form_close'>X</p>		
		<div class='submission_form form_section'>
			<?php
				//loggedin user data
				$user_ID = get_current_user_id();
				$optRS = $this->optRS;
				if(!empty($user_ID) && $user_ID && !empty($optRS['evors_prefil']) && $optRS['evors_prefil']=='yes' ){
					$user_info = get_userdata($user_ID);
					echo "<div class='evorsvp_loggedin_user_data' data-uid='{$user_ID}' data-fname='{$user_info->first_name}' data-lname='{$user_info->last_name}' data-email='{$user_info->user_email}'></div>";
				}else{
					echo "<div class='evorsvp_loggedin_user_data'></div>";
				}				
			?>			

			<h3 class="form_header"><?php echo $this->replace_en( $this->lang('evoRSL_x2','RSVP to [event-name]'));?></h3>
			<div class="form_row rsvp_status">
				<p><?php echo $this->get_rsvp_choices($this->opt2, $optRS, array() );?></p>
			</div>			
			<?php
				$_field_fname = $this->lang( 'evoRSL_007','First Name');
				$_field_lname = $this->lang( 'evoRSL_008','Last Name');
			?>
			<div class="form_row">
				<input class='name input req' name='first_name' type="text" placeholder='<?php echo $_field_fname;?>' title='<?php echo $_field_fname;?>' value=''/>
				<input class='name input' name='last_name' type="text" placeholder='<?php echo $_field_lname;?>' title='<?php echo $_field_lname;?>' value=''/>
			</div>
		
			<?php	$_field_email = $this->lang( 'evoRSL_009','Email Address');	?>
			<div class="form_row">
				<input class='regular input req' name='email' type="text" placeholder='<?php echo $_field_email;?>' title='<?php echo $_field_email;?>' value=''/>
			</div>
		<?php  if($active_fields && in_array('phone', $active_fields)):?>

			<?php
				$_field_phone = $this->lang( 'evoRSL_009a','Phone Number');
			?>
			<div class="form_row">
				<input class='regular input req' name='phone' type="text" placeholder='<?php echo $_field_phone;?>' title='<?php echo $_field_phone;?>'/>
			</div>
		<?php endif;  if($active_fields && in_array('count', $active_fields)):?>
			<?php
				$_field_count = $this->lang('evoRSL_010','How many people in your party?');
			?>
			<div class="form_row count hide_no">
				<label><?php echo $_field_count;?></label>
				<input class='count input' name='count' type="text" placeholder='1'/>
			</div>
		<?php endif;?>

			<?php
			// ADDITIONAL FIELDS
				for($x=1; $x<4; $x++){
					// if fields is activated and name of the field is not empty
					if(evo_settings_val('evors_addf'.$x, $optRS) && !empty($optRS['evors_addf'.$x.'_1'])){
						$required = (!empty($optRS['evors_addf'.$x.'_3']) && $optRS['evors_addf'.$x.'_3']=='yes')? 
							'req':null;
						$FIELDTYPE = (!empty($optRS['evors_addf'.$x.'_2']) && !empty($optRS['evors_addf'.$x.'_4']))? 
							$optRS['evors_addf'.$x.'_2']:'text';
					?>
						<div class="form_row additional_field hide_no">
						<?php 
							if($FIELDTYPE=='text'):
						?>
							<input title='<?php echo $optRS['evors_addf'.$x.'_1'];?>' placeholder='<?php echo $optRS['evors_addf'.$x.'_1'];?>' class='regular input <?php echo $required;?>' name='<?php echo 'evors_addf'.$x.'_1';?>'type="text" />
						<?php elseif($FIELDTYPE=='html'):?>
							<p><?php echo $optRS['evors_addf'.$x.'_1'];?></p>
						<?php elseif($FIELDTYPE=='textarea'):?>
							<p><label for=""><?php echo $optRS['evors_addf'.$x.'_1'];?></label>
							<textarea title='<?php echo $optRS['evors_addf'.$x.'_1'];?>' placeholder='<?php echo $optRS['evors_addf'.$x.'_1'];?>' class='regular input <?php echo $required;?>' name='<?php echo 'evors_addf'.$x.'_1';?>'></textarea></p>
						<?php else:?>
							<p>
								<label for=""><?php echo $optRS['evors_addf'.$x.'_1'];?></label>
								<select name='<?php echo 'evors_addf'.$x.'_1';?>' class='input dropdown'>
								<?php
									global $eventon_rs;
									$OPTIONS = $eventon_rs->frontend->get_additional_field_options($optRS['evors_addf'.$x.'_4']);
									foreach($OPTIONS as $slug=>$option){
										echo "<option value='{$slug}'>{$option}</option>";
									}
								?>
								</select>
							</p>
						<?php endif;?>
						</div>
					<?php
					}
				}
			?>
	
			<?php
				// additional notes field for NO option
				if($active_fields && in_array('additional', $active_fields)):
					$_text_additional = $this->lang('evoRSL_010a','Additional Notes');
			?>
				<div class="form_row additional_note" style='display:none'>
					<label><?php echo $_text_additional;?></label>
					<textarea class='input' name='additional_notes' type="text" placeholder='<?php echo $_text_additional;?>'></textarea>
				</div>
			<?php endif;?>
			<?php 
				if($active_fields && in_array('captcha', $active_fields)):

				// validation calculations
				$cals = array(	0=>'3+8', '5-2', '4+2', '6-3', '7+1'	);
				$rr = rand(0, 4);
				$calc = $cals[$rr];
			?>

			<div class="form_row captcha">
				<p><?php echo $this->lang( 'evoRSL_011a','Verify you are a human');?></p>
				<p><?php echo $calc;?> = <input type="text" data-cal='<?php echo $rr;?>' class='regular_a captcha'/></p>
			</div>
			<?php endif;?>
			<?php if($active_fields && in_array('updates', $active_fields)):?>
			<div class="form_row updates">
				<input type="checkbox" /> <label><?php echo $this->lang( 'evoRSL_011','Receive updates about event');?></label>
			</div>
			<?php endif;?>
			<div class="form_row">
				<a id='submit_rsvp_form' class='evcal_btn evors_submit'><?php echo $this->lang( 'evoRSL_012','Submit');?></a>
				<!-- form terms & conditions -->
				<?php
					if(!empty($optRS['evors_terms']) && $optRS['evors_terms']=='yes' && !empty($optRS['evors_terms_link']) ){
						echo "<p class='terms' style='padding-top:10px'><a href='".$optRS['evors_terms_link']."' target='_blank'>".$this->lang( 'evoRSL_tnc','Terms & Conditions')."</a></p>";
					}
				?>
			</div>			
		</div>

	<!-- change RSVP FORM -->
		<div class='find_rsvp_to_change form_section' style='display:none'>
			<h3 class="form_header"><?php echo $this->replace_en( $this->lang('evoRSL_x3','Find my RSVP for [event-name]'));?></h3>
			<div class="form_row">
				<input class='name input req' name='first_name' type="text" placeholder=' <?php echo $_field_fname;?>'/>
				<input class='name input req' name='last_name' type="text" placeholder=' <?php echo $_field_lname;?>'/>
			</div>
			<div class="form_row">
				<input class='regular input req' name='rsvp_id' type="text" placeholder='<?php echo $this->lang( 'evoRSL_007a','RSVP ID');?>'/>
			</div>
			<div class="form_row">
				<p><?php echo $this->lang( 'evoRSL_x1','We have to look up your RSVP in order to change it');?></p>
				<a id='change_rsvp_form' class='evcal_btn evors_submit'><?php echo $this->lang( 'evoRSL_012y','Find my RSVP');?></a>
			</div>
		</div>
	

	<!-- Success RSVP confirmation -->
		<div class='rsvp_confirmation form_section' style="display:none" data-rsvpid=''>
			<b></b>
			<h3 class="form_header submit"><?php echo $this->replace_en( $this->lang( 'evoRSL_x5','Successfully RSVP-ed for [event-name]'));?></h3>
			<h3 class="form_header update" style='display:none'><?php echo $this->replace_en($this->lang( 'evoRSL_x4','Successfully updated RSVP for [event-name]'));?></h3>
			<p><?php echo $this->lang( 'evoRSL_x7','Thank You');?> <span class='name'></span></p>
			<?php if($active_fields && in_array('count', $active_fields)):
				$_txt_reseverd = str_replace('[spaces]', "<span class='spots'></span>", $this->lang( 'evoRSL_x6','You have reserved [spaces] space(s) for [event-name]'));
				$_txt_reseverd = $this->replace_en($_txt_reseverd);
			?>
				<p class='coming'><?php echo $_txt_reseverd;?></p>
			<?php endif;?>

			<?php
				$_txt_emails = str_replace('[email]', "<span class='email'></span>", $this->lang( 'evoRSL_x8','We have email-ed you a confirmation to [email]'));
			?>
			<p class='coming'><?php echo $_txt_emails;?></p>
			<div class="form_row" style='padding-top:10px'>
				<a id='call_change_rsvp_form' class='evcal_btn evors_submit'><?php echo $this->lang('evoRSL_012x','Change my RSVP');?></a>
			</div>
		</div>

	<!-- form messages -->			
		<div class="form_row notification" style='display:none'><p></p></div>		
		<?php echo $this->get_form_msg($this->opt2);?>

	</div>
	</div>
	</div>