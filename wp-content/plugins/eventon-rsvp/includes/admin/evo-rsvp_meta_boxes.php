<?php
/**
 * Meta boxes for evo-rsvp
 *
 * @author 		AJDE
 * @category 	Admin
 * @package 	EventON/Admin/evo-rsvp
 * @version     0.2
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Initiate
	function evoRS_meta_boxes(){
		add_meta_box('evors_mb1','RSVP Event', 'evors_metabox_content','ajde_events', 'normal', 'high');
		add_meta_box('evors_mb1','RSVP Event', 'evoRS_metabox_rsvp','evo-rsvp', 'normal', 'high');
		add_meta_box('evors_mb2','RSVP Notifications', 'evoRS_notifications_box','evo-rsvp', 'side', 'default');
		add_meta_box('evors_mb3','RSVP Email', 'evoRS_notifications_box2','evo-rsvp', 'side', 'default');
		
		do_action('evoRS_add_meta_boxes');
	}
	add_action( 'add_meta_boxes', 'evoRS_meta_boxes' );
	add_action( 'eventon_save_meta', 'evoRS_save_meta_data', 10 , 2 );
	add_action( 'save_post', 'evoRS_save_rsvp_meta_data', 1 , 2 );
	
// notification email box
	function evoRS_notifications_box(){
		global $post;
		?>
		<div class='evoRS_resend_conf'>
			<div class='evoRS_rc_in'>
				<p><i><?php _e('You can re-send the RSVP confirmation email to submitter if they have not received it. Make sure to check spam folder.','eventon');?></i></p>
				<a id='evoRS_resend_email' class='button' data-rsvpid='<?php echo $post->ID;?>'><?php _e('Re-send Confirmation Email','eventon');?></a>
				<p class='message' style='display:none'><?php _e('Email resend action performed!','eventon');?></p>
			</div>
		</div>
		<?php
	}
	function evoRS_notifications_box2(){
		global $post;
		?>
		<div class='evoRS_resend_conf'>
			<div class='evoRS_rc_in'>
				<p><i><?php _e('Send RSVP confirmation email to other email addresses using below fields. <br/>NOTE: you can send to multiple email address separated by commas.','eventon');?></i></p>
				<p class='field'><input type='text' placeholder='Comma separated email addresses' style="width:100%" /></p>
				<a id='evoRS_custom_email' class='button' data-rsvpid='<?php echo $post->ID;?>' data-empty='<?php _e('Email field can not be empty!','eventon');?>' ><?php _e('Send Email','eventon');?></a>
				<p class='message' style='display:none'><?php _e('Email send action performed!','eventon');?></p>
			</div>
		</div>
		<?php
	}

// META box for rsvp page
	function evoRS_metabox_rsvp(){
		global $post, $eventon_rs, $ajde, $pagenow;
		$pmv = get_post_meta($post->ID);
		$optRS = $eventon_rs->evors_opt;

		//$what = $eventon_rs->frontend->send_email(array(
		//	'e_id'=>1335,
		//), 'digest');

		// Debug email templates
			$show_debug_email = false;
			if($show_debug_email):
				$tt = $eventon_rs->frontend->_get_email_body(array('e_id'=>'90','rsvp'=>'y','count'=>'1','first_name'=>'Jason','last_name'=>'Miller','rsvp_id'=>'709','email'=>'test@msn.com'), 'confirmation_email');
				print_r($tt);
			endif;
		
		// get translated check-in status
			$_checkinST = (!empty($pmv['status']) && $pmv['status'][0]=='checked')?
				'checked':'check-in';
			$checkin_status = $eventon_rs->frontend->get_checkin_status($_checkinST);			
			wp_nonce_field( plugin_basename( __FILE__ ), 'evorsvp_nonce' );
		?>	
		<div class='eventon_mb' style='margin:-6px -12px -12px'>
		<div style='background-color:#ECECEC; padding:15px;'>
			<div style='background-color:#fff; border-radius:8px;'>
			<table id='evors_rsvp_tb' width='100%' class='evo_metatable'>				
				<tr><td><?php _e('RSVP #','eventon');?>: </td><td><?php echo $post->ID;?></td></tr>
				<tr><td><?php _e('RSVP Status','eventon');?>: </td>
					<td><select name='rsvp'>
					<?php 
						$savedrsvpO = (!empty($pmv['rsvp']))?$pmv['rsvp'][0]:false;
						foreach($eventon_rs->rsvp_array_ as $rsvpOptions=>$rsvpV){
							echo "<option ".( $savedrsvpO && $rsvpOptions==$pmv['rsvp'][0]? 'selected="selected"':'')." value='{$rsvpOptions}'>{$rsvpV}</option>";
						}
					?>
					</select>
					</td></tr>
				<tr><td><?php _e('Checkin to Event Status','eventon');?>: </td><td><span class='rsvp_ch_st <?php echo $_checkinST;?>' data-status='<?php echo $_checkinST;?>' data-rsvpid='<?php echo $post->ID;?>'><?php echo $checkin_status;?></span>

				</td></tr>
				<tr><td><?php _e('First Name','eventon');?>:* </td>
					<td><input type='text' name='first_name' value='<?php echo (!empty($pmv['first_name']) )? $pmv['first_name'][0]:'';?>'/>
					</td></tr>
				<tr><td><?php _e('Last Name','eventon');?>: </td>
					<td><input type='text' name='last_name' value='<?php echo (!empty($pmv['last_name']) )? $pmv['last_name'][0]:'';?>'/>
					</td></tr>
				<tr><td><?php _e('Email Address','eventon');?>:* </td>
					<td><input type='text' name='email' value='<?php echo !empty( $pmv['email'])? $pmv['email'][0]:'';?>'/>
					</td></tr>
				<tr><td><?php _e('Count','eventon');?>: </td>
					<td><input type='text' name='count' value='<?php echo !empty($pmv['count'])?$pmv['count'][0]:'1';?>'/></td></tr>
				<tr><td><?php _e('Phone','eventon');?>: </td>
					<td><input type='text' name='phone' value='<?php echo !empty($pmv['phone'])?$pmv['phone'][0]:'';?>'/></td></tr>
				<tr><td><?php _e('Receive Email Updates','eventon');?>: </td>
					<td><?php echo $ajde->wp_admin->html_yesnobtn(array(
						'id'=>'updates','input'=>true,
						'default'=>((!empty($pmv['updates']) && $pmv['updates'][0]=='yes')? 'yes':'no' )
					));?></td></tr>
				<tr><td><?php _e('Event','eventon');?>: </td>
					<td><?php 
						// event for rsvp
						if(empty($pmv['e_id'])){
							$events = get_posts(array('posts_per_page'=>-1, 'post_type'=>'ajde_events'));
							if($events && count($events)>0 ){
								echo "<select name='e_id'>";
								foreach($events as $event){
									echo "<option value='".$event->ID."'>".get_the_title($event->ID)."</option>";
								}
								echo "</select>";
							}
							wp_reset_postdata();
						}else{
							echo '<a href="'.get_edit_post_link($pmv['e_id'][0]).'">'.get_the_title($pmv['e_id'][0]).'</a></td></tr>';
						}
				// REPEATING interval
				if($pagenow!='post-new.php' && !empty($pmv['e_id'])){

					$saved_ri = (!empty($pmv['repeat_interval']) && $pmv['repeat_interval'][0]!='0')?
						$pmv['repeat_interval'][0]:'0';
					$event_pmv = get_post_custom($pmv['e_id'][0]);
					?>
					<tr><td><?php _e('Event Date','eventon');?>: </td>
					<td><?php 
					$repeatIntervals = (!empty($event_pmv['repeat_intervals'])? unserialize($event_pmv['repeat_intervals'][0]): false);
					if($repeatIntervals && count($repeatIntervals)>0){
						$datetime = new evo_datetime();		
						echo "<select name='repeat_interval'>";
						$x=0;
						$wp_date_format = get_option('date_format');
						foreach($repeatIntervals as $interval){
							$time = $datetime->get_int_correct_event_time($event_pmv,$x);
							echo "<option value='".$x."' ".( $saved_ri == $x?'selected="selected"':'').">".date($wp_date_format.' h:i:a',$time)."</option>"; $x++;
						}
						echo "</select>";
					}
					?></td></tr>
					<?php
				}
				// additional fields
				for($x=1; $x<4; $x++){
					// if fields is activated and name of the field is not empty
					if(evo_settings_val('evors_addf'.$x, $optRS) && !empty($optRS['evors_addf'.$x.'_1'])){
						$FIELDTYPE = !empty($optRS['evors_addf'.$x.'_2'])? $optRS['evors_addf'.$x.'_2']:'text';

						if($FIELDTYPE=='text'){
							echo "<tr><td>".$optRS['evors_addf'.$x.'_1']."</td>
								<td><input type='text' name='evors_addf'.$x.'_1' value='".( (!empty($pmv['evors_addf'.$x.'_1']))? $pmv['evors_addf'.$x.'_1'][0]: '-')."'/></td></tr>";
						}else{
							echo "<tr><td>".$optRS['evors_addf'.$x.'_1']."</td>
								<td><select name='evors_addf{$x}_1'>";

								$OPTIONS = $eventon_rs->frontend->get_additional_field_options($optRS['evors_addf'.$x.'_4']);
								foreach($OPTIONS as $slug=>$options ){
									echo "<option ".(!empty($pmv['evors_addf'.$x.'_1']) && $slug==$pmv['evors_addf'.$x.'_1'][0]?'selected="selected"':'')." value='{$slug}'>{$options}</option>";
								}
							echo "</select></td></tr>";
						}
					}
				}?>
				
				<tr><td><?php _e('Additional Notes','eventon');?>: </td>
					<td><textarea style='width:100%' type='text' name='additional_notes'><?php echo !empty($pmv['additional_notes'])?$pmv['additional_notes'][0]:'';?></textarea></td></tr>

				<?php
				// plugabble hook
				if(!empty($pmv['e_id']))
					do_action('eventonrs_rsvp_post_table',$post->ID, $pmv);
				?>
			</table>
			</div>
		</div>
		</div>
		<?php
	}

	// SAVE values for evo-rsvp post 
	function evoRS_save_rsvp_meta_data($post_id, $post){
		if($post->post_type!='evo-rsvp')
			return;
			
		// Stop WP from clearing custom fields on autosave
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)	return;

		// Prevent quick edit from clearing custom fields
		if (defined('DOING_AJAX') && DOING_AJAX)	return;
		
		// verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times
		if( isset($_POST['evorsvp_nonce']) && !wp_verify_nonce( $_POST['evorsvp_nonce'], plugin_basename( __FILE__ ) ) ){
			return;
		}

		// Check permissions
		if ( !current_user_can( 'edit_post', $post_id ) )	return;	

		global $pagenow;
		$_allowed = array( 'post-new.php', 'post.php' );
		if(!in_array($pagenow, $_allowed)) return;

		$fields = array(
			'count', 'first_name','last_name','email','phone',
			'rsvp','updates','e_id','repeat_interval',
			'evors_addf1_1',
			'evors_addf2_1',
			'evors_addf3_1',
			'additional_notes'
		);

		foreach($fields as $field){
			if(!empty($_POST[$field])){
				update_post_meta( $post_id, $field, $_POST[$field] );
			}else{
				if($field!='e_id')
					delete_post_meta($post_id, $field);
			}
		}

		// sync event rsvp count
		global $eventon_rs;
		if(!empty($_POST['e_id'])){
			$eventon_rs->frontend->functions->sync_rsvp_count($_POST['e_id']);
		}

	}

// RSVP meta box for EVENT posts
	function evors_metabox_content(){

		global $post, $eventon_rs, $eventon, $ajde;

		$optRS = $eventon_rs->evors_opt;
		$pmv = get_post_meta($post->ID);

		wp_nonce_field( plugin_basename( __FILE__ ), 'evors_nonce' );

		ob_start();

		$evors_rsvp = (!empty($pmv['evors_rsvp']))? $pmv['evors_rsvp'][0]:null;
		$evors_show_rsvp = (!empty($pmv['evors_show_rsvp']))? $pmv['evors_show_rsvp'][0]:null;
		$evors_show_whos_coming = (!empty($pmv['evors_show_whos_coming']))? $pmv['evors_show_whos_coming'][0]:null;
		$evors_add_emails = (!empty($pmv['evors_add_emails']))? $pmv['evors_add_emails'][0]:null;
	?>
	<div class='eventon_mb'>
	<div class="evors">
		<p class='yesno_leg_line ' style='padding:10px'>
			<?php echo eventon_html_yesnobtn(array('var'=>$evors_rsvp, 'attr'=>array('afterstatement'=>'evors_details'))); ?>
			<input type='hidden' name='evors_rsvp' value="<?php echo ($evors_rsvp=='yes')?'yes':'no';?>"/>
			<label for='evors_rsvp'><?php _e('Allow visitors to RSVP to this event')?></label>
		</p>
		<div id='evors_details' class='evors_details evomb_body ' <?php echo ( $evors_rsvp=='yes')? null:'style="display:none"'; ?>>		
			<div class="evors_stats" style='padding-top:5px'>			
			<?php
				$yes_count =  $eventon_rs->frontend->functions->get_event_rsvp_count($post->ID, 'yes', $pmv);
				$maybe_count =  $eventon_rs->frontend->functions->get_event_rsvp_count($post->ID, 'maybe', $pmv);
				$no_count =  $eventon_rs->frontend->functions->get_event_rsvp_count($post->ID, 'no', $pmv);

				$evors_capacity_count = (!empty($pmv['evors_capacity_count']))? $pmv['evors_capacity_count'][0]:null; 
			?>
				<p><b><?php echo $yes_count; ?></b><span><?php _e('YES','eventon');?></span></p>
				<p><b><?php echo $maybe_count;?></b><span><?php _e('Maybe','eventon');?></span></p>
				<p><b><?php echo $no_count;?></b><span><?php _e('No','eventon');?></span></p>
				<div class='clear'></div>
			</div>
			<?php if(!empty($evors_capacity_count)):?>
				<div class='evors_stats_bar'>
					<p><span class='yes' style='width:<?php echo (int)(($yes_count/$evors_capacity_count)*100);?>%'></span><span class='maybe' style='width:<?php echo (int)(($maybe_count/$evors_capacity_count)*100);?>%'></span><span class='no' style='width:<?php echo (int)(($no_count/$evors_capacity_count)*100);?>%'></span></p>
				</div>
			<?php endif;?>
			<div class='evo_negative_25'>
			<table width='100%' class='eventon_settings_table'>
				<tr><td colspan='2'>
					<p class='yesno_leg_line '>
						<?php echo eventon_html_yesnobtn(array('var'=>$evors_show_rsvp)); ?>					
						<input type='hidden' name='evors_show_rsvp' value="<?php echo ($evors_show_rsvp=='yes')?'yes':'no';?>"/>
						<label for='evors_show_rsvp'><?php _e('Show RSVP count for the event on EventCard')?><?php echo $eventon->throw_guide("This will show how many guests are coming for each RSVP option as a number next to it on eventcard.", '',false)?></label>
					</p>
				</td></tr>
				<!-- show whos coming to the event -->
				<tr><td colspan='2'>
					<p class='yesno_leg_line '>
						<?php echo eventon_html_yesnobtn(array('var'=>$evors_show_whos_coming)); ?>
						<input type='hidden' name='evors_show_whos_coming' value="<?php echo ($evors_show_whos_coming=='yes')?'yes':'no';?>"/>
						<label for='evors_show_whos_coming'><?php _e("Show who's coming to event")?></label>
					</p>
				</td></tr>
				
				<?php
				// if notifications enabled show additional emails field
					if(!empty($optRS['evors_notif']) && $optRS['evors_notif']=='yes' ):?>
						<tr><td colspan='2'>
						<p style='padding:5px 0'>
							<label for='evors_add_emails' style='padding-bottom:8px; display:inline-block'><i><?php _e('Additional email addresses to receive email notifications','eventon')?><?php echo $eventon->throw_guide("Set additional email addresses seperated by commas to receive email notifications upon new RSVP reciept", '',false)?></i></label>
							<input type='text' name='evors_add_emails' value="<?php echo $evors_add_emails;?>" style='width:100%' placeholder='eg. you@domain.com'/>			
						</p>
						</td></tr>
					<?php endif;?>

				<tr><td colspan='2'>			
					<?php $evors_max_active = (!empty($pmv['evors_max_active']))? $pmv['evors_max_active'][0]:null;
						$evors_max_count = (!empty($pmv['evors_max_count']))? $pmv['evors_max_count'][0]:null;
					?>
					<p class='yesno_leg_line '>
						<?php echo eventon_html_yesnobtn(array('var'=>$evors_max_active,'attr'=>array('afterstatement'=>'evors_max_count_row','as_type'=>'class'))); ?>
						<input type='hidden' name='evors_max_active' value="<?php echo ($evors_max_active=='yes')?'yes':'no';?>"/>
						<label for='evors_max_active'><?php _e('Limit maximum capacity count per RSVP','eventon')?><?php echo $eventon->throw_guide('This will allow you to limit each RSVP reservation count to a set max number, then the guests can not book more spaces than this limit.');?></label>
					</p>
				</td></tr>
					<tr class='evors_max_count_row yesnosub' style='display:<?php echo ($evors_max_active=='yes')?'':'none';?>'>
						<td><?php _e('Maximum count number','eventon'); ?></td>
						<td><input type='text' id='evors_max_count' name='evors_max_count' value="<?php echo $evors_max_count;?>"/></td>
					</tr>


				<tr><td colspan='2'>			
					<?php $evors_capacity = (!empty($pmv['evors_capacity']))? $pmv['evors_capacity'][0]:null;?>
					<p class='yesno_leg_line '>
						<?php echo eventon_html_yesnobtn(array('var'=>$evors_capacity,'attr'=>array('afterstatement'=>'evors_capacity_row','as_type'=>'class'))); ?>
						<input type='hidden' name='evors_capacity' value="<?php echo ($evors_capacity=='yes')?'yes':'no';?>"/>
						<label for='evors_capacity'><?php _e('Set capacity limit for RSVP','eventon')?><?php echo $eventon->throw_guide('Activating this will allow you to add a limit to how many RSVPs you can receive. When the limit is reached RSVP will close.');?></label>
					</p>
				</td></tr>
				<?php 					
					$evors_capacity_show = (!empty($pmv['evors_capacity_show']))? $pmv['evors_capacity_show'][0]:null; 
				?>
				<tr class='evors_capacity_row yesnosub' style='display:<?php echo ($evors_capacity=='yes')?'':'none';?>'>
					<td><?php _e('Total available RSVP capacity','eventon'); ?></td>
					<td><input type='text' id='evors_capacity_count' name='evors_capacity_count' value="<?php echo $evors_capacity_count;?>"/></td>
				</tr>

				<?php
				// manange RSVP capacity separate for repeating events
					if(!empty($pmv['evcal_repeat']) && $pmv['evcal_repeat'][0]=='yes'):
						$manage_repeat_cap = evo_meta_yesno($pmv,'_manage_repeat_cap_rs','yes','yes','no' );
				?>
				<tr class='evors_capacity_row yesnosub' style='display:<?php echo ($evors_capacity=='yes')?'':'none';?>'><td colspan='2'>
					<p class='yesno_leg_line ' >
						<?php echo eventon_html_yesnobtn(array('id'=>'evors_mcap',
						'var'=>$manage_repeat_cap, 'attr'=>array('afterstatement'=>'evors_ri_cap'))); ?>
						<input type='hidden' name='_manage_repeat_cap_rs' value="<?php echo $manage_repeat_cap;?>"/>

						<label for='_manage_repeat_cap_rs'><?php _e('Manage available capacity separate for each repeating interval of this event'); echo $eventon->throw_guide('Once repeating event capacities are set the total capacity for event will be overridden. If you just made event repeat, this event need to be updated for repeat options to show up.','',false)?></label>
					</p>
				<?php 
					$repeat_intervals = !empty($pmv['repeat_intervals'])? unserialize($pmv['repeat_intervals'][0]): false;
				?>
					<div id='evors_ri_cap' class='evotx_repeat_capacity' style='padding-top:15px; padding-bottom:20px;display:<?php echo evo_meta_yesno($pmv,'_manage_repeat_cap_rs','yes','','none' );?>'>
						<p><em style='opacity:0.6'><?php _e('NOTE: The capacity above should match the total number of capacity for each repeat occurance below for this event.','eventon');?></em></p>
						<?php
							// if repeat intervals set 
							if($repeat_intervals && count($repeat_intervals)>0){
								$count =0;

								// get saved capacities for repeats
								$ri_capacity_rs = !empty($pmv['ri_capacity_rs'])?
									unserialize($pmv['ri_capacity_rs'][0]): false;

								echo "<div class='evotx_ri_cap_inputs'>";
								// for each repeat interval
								$evcal_opt1 = get_option('evcal_options_evcal_1');
								
								foreach($repeat_intervals as $interval){
									$date_format = eventon_get_timeNdate_format($evcal_opt1);
									$TIME = eventon_get_editevent_kaalaya($interval[0], $date_format[1], $date_format[2]);
									$ri_open_count = ($ri_capacity_rs && !empty($ri_capacity_rs[$count]))? $ri_capacity_rs[$count]:'0';

									echo "<p class='".($count>10?'hidden':'')."'><input type='text' name='ri_capacity_rs[]' value='". ($ri_open_count) . "'/><span>" . $TIME[0] . "<br/><em>Remaining: ".$eventon_rs->frontend->functions->get_ri_remaining_count('y', $count,$ri_open_count,$pmv)."</em></span></p>";
									$count++;
								}
								
								echo "<div class='clear'></div>";
								echo $count>10? "<p class='evors_repeats_showrest'>".__('Show Rest','eventon')."</p>":'';
								echo "</div>";
								echo (count($repeat_intervals)>5)? 
									"<p class='evotx_ri_view_more'><a class='button_evo'>Click here</a> to view the rest of repeat occurances.</p>":null;
							}
						?>
					</div>
				</td></tr>
				<?php endif;?>

				<tr class='evors_capacity_row yesnosub' style='display:<?php echo ($evors_capacity=='yes')?'':'none';?>'><td colspan='2'>
					<p class='yesno_leg_line '>
						<?php echo eventon_html_yesnobtn(array('var'=>$evors_capacity_show,)); ?>
						<input type='hidden' name='evors_capacity_show' value="<?php echo ($evors_capacity_show=='yes')?'yes':'no';?>"/>
						<label for='evors_capacity_show'><?php _e('Show available spaces count on front-end')?></label>
					</p>
				</td></tr>
				
				<!-- is happening -->
					<tr><td colspan='2'>			
						<?php $evors_min_cap = (!empty($pmv['evors_min_cap']))? $pmv['evors_min_cap'][0]:null;
							$evors_min_count = (!empty($pmv['evors_min_count']))? $pmv['evors_min_count'][0]:null;
						?>
						<p class='yesno_leg_line '>
							<?php echo $ajde->wp_admin->html_yesnobtn(array('var'=>$evors_min_cap,'attr'=>array('afterstatement'=>'evors_min_count_row','as_type'=>'class'))); ?>
							<input type='hidden' name='evors_min_cap' value="<?php echo ($evors_min_cap=='yes')?'yes':'no';?>"/>
							<label for='evors_min_cap'><?php _e('Activate event happening minimum capacity','eventon')?><?php echo $eventon->throw_guide('With this you can set a minimum capacity for this event, at which point the event will take place for certain.');?></label>
						</p>
					</td></tr>
						<tr class='evors_min_count_row yesnosub' style='display:<?php echo ($evors_min_cap=='yes')?'':'none';?>'>
							<td><?php _e('Minimum capacity for even to happen','eventon'); ?></td>
							<td><input type='text' id='evors_min_count' name='evors_min_count' value="<?php echo $evors_min_count;?>"/></td>
						</tr>


				<!-- close rsvp before X time -->
					<?php $evors_close_time = (!empty($pmv['evors_close_time']))? $pmv['evors_close_time'][0]:null;  ?>
					<tr>
						<td><?php _e('Close RSVP before time (in minutes)','eventon');?><?php echo $eventon->throw_guide('Set how many minutes before the event end time to close RSVP form. Time must be in minutes. Leave blank to not close RSVP before event time.');?></td>
						<td><input type='text' id='evors_close_time' name='evors_close_time' value="<?php echo $evors_close_time;?>" placeholder='eg. 45'/></td>
					</tr>

				<!-- additional information for rsvped -->
					<tr>
						<td colspan='2' style='padding-top:10px;'><?php _e('Additional Information only visible to loggedin RSVPed guests & in Confirmation Email','eventon');?><?php echo $eventon->throw_guide('Information entered in here will only be visible on front-end once user has RSVPed to the event.');?>
						<br/><textarea style='width:100%; margin-top:10px' name='evors_additional_data'><?php echo (!empty($pmv['evors_additional_data']))? $pmv['evors_additional_data'][0]:null;?></textarea>
						</td>
					</tr>
				
				<!-- daily digest -->
					<tr><td colspan='2'>
						<p class='yesno_leg_line '>
							<?php $evors_daily_digest = (!empty($pmv['evors_daily_digest']))? $pmv['evors_daily_digest'][0]:null; 
								echo eventon_html_yesnobtn(array('var'=>$evors_daily_digest)); ?>					
							<input type='hidden' name='evors_daily_digest' value="<?php echo ($evors_daily_digest=='yes')?'yes':'no';?>"/>
							<label for='evors_daily_digest'><?php _e('Receive Daily Digest for this event (BETA)','eventon')?><?php echo $eventon->throw_guide("This will send you daily email digest of RSVP information for this event. Email settings can be customized from RSVP settings. This is in BETA version", '',false)?></label>
						</p>
					</td></tr>

				<tr><td colspan='2' style=''><p style='opacity:0.7'><i><?php _e('NOTE: All text strings that appear for RSVP section on eventcard can be editted via myEventon > languages','eventon');?></i></p></td></tr>
			</table>
			</div>
			<?php

			// INITIAL information for lightbox data section
			// @version 0.2
			// DOWNLOAD CSV link 
				$exportURL = add_query_arg(array(
				    'action' => 'the_ajax_evors_a3',
				    'e_id' => $post->ID,     // cache buster
				), admin_url('admin-ajax.php'));

				// repeat event interval data
				$ri_count_active = $eventon_rs->frontend->functions->is_ri_count_active($pmv);
				$repeatIntervals = !empty($pmv['repeat_intervals'])? unserialize($pmv['repeat_intervals'][0]): false;
				$datetime = new evo_datetime();	$wp_date_format = get_option('date_format');	
			?>
			<div class='evcal_rep evors_info_actions'>				
				<div class='evcalr_1'>
				<p class='actions'>
					<a id='evors_VA' data-e_id='<?php echo $post->ID;?>' data-riactive='<?php echo ($ri_count_active && $repeatIntervals)?'yes':'no';?>' data-popc='evors_lightbox' class='button_evo attendees ajde_popup_trig' ><?php _e('View Attendees','eventon');?></a> 
					<a class='button_evo download' href="<?php echo $exportURL;?>"><?php _e('Download (CSV)','eventon');?></a> 
					<a id='evors_SY' data-e_id='<?php echo $post->ID;?>' class='button_evo sync' ><?php _e('Sync Count','eventon');?></a> 
					<a id='evors_EMAIL' data-e_id='<?php echo $post->ID;?>' data-popc='evors_email_attendee' class='button_evo email ajde_popup_trig' ><?php _e('Emailing','eventon');?></a> 
				</p>
				
				<?php 
				// lightbox content for emailing section
				ob_start();?>
				<div id='evors_emailing' style=''>
					<p><label><?php _e('Select emailing option','eventon');?></label>
						<select name="" id="evors_emailing_options">
							<option value="someone"><?php _e('Email Attendees List to someone','eventon');?></option>
							<option value="coming"><?php _e('Email only attending guests','eventon');?></option>
							<option value="notcoming"><?php _e('Email guests not coming to event','eventon');?></option>
							<option value="all"><?php _e('Email all rsvped guests','eventon');?></option>
						</select>
					</p>
					<?php
						// if repeat interval count separatly						
						if($ri_count_active && $repeatIntervals ){
							if(count($repeatIntervals)>0){
								echo "<p><label>". __('Select Event Repeat Instance','eventon')."</label> ";
								echo "<select name='repeat_interval' id='evors_emailing_repeat_interval'>
									<option value='all'>".__('All','eventon')."</option>";																
								$x=0;								
								foreach($repeatIntervals as $interval){
									$time = $datetime->get_correct_formatted_event_repeat_time($pmv,$x, $wp_date_format);
									echo "<option value='".$x."'>".$time['start']."</option>"; $x++;
								}
								echo "</select>";
								echo $eventon->throw_guide("Select which instance of repeating events of this event you want to use for this emailing action.", '',false);
								echo "</p>";
							}
						}
					?>
					<p style='' class='text'><label for=""><?php _e('Email Addresses (separated by commas)','eventon');?></label><br/><input style='width:100%' type="text"></p>
					<p style='' class='subject'><label for=""><?php _e('Subject for email','eventon');?> *</label><br/><input style='width:100%' type="text"></p>
					<p style='' class='textarea'><label for=""><?php _e('Message for the email','eventon');?></label><br/>
						<textarea cols="30" rows="5" style='width:100%'></textarea></p>
					<p><a data-eid='<?php echo $post->ID;?>' id="evors_email_submit" class='evo_admin_btn btn_prime'><?php _e('Send Email','eventon');?></a></p>
				</div>
				<?php $emailing_content = ob_get_clean();?>

				<?php
					// lightbox content for view attendees
					
					if($repeatIntervals && $ri_count_active && count($repeatIntervals)>0):
					ob_start();?>
					<div id='evors_view_attendees'>
						<p style='text-align:center'><label><?php _e('Select Repeating Instance of Event','eventon');?></label> 
							<select name="" id="evors_event_repeatInstance">
								<option value="all"><?php _e('All Repeating Instances','eventon');?></option>
								<?php
								$x=0;								
								foreach($repeatIntervals as $interval){
									$time = $datetime->get_correct_formatted_event_repeat_time($pmv,$x, $wp_date_format);
									echo "<option value='".$x."'>".$time['start']."</option>"; $x++;
								}
								?>
							</select>
						</p>
						<p style='text-align:center'><a id='evors_VA_submit' data-e_id='<?php echo $post->ID;?>' class='evo_admin_btn btn_prime' ><?php _e('Submit','eventon');?></a> </p>
					</div>
					<div id='evors_view_attendees_list'></div>
					<?php $viewattendee_content = ob_get_clean();
					else:	$viewattendee_content = "<div id='evors_view_attendees'>LOADING...</div>";	endif;
				?>

				<p id='evors_message' style='display:none'></p>
				<?php echo $eventon->output_eventon_pop_window(array('class'=>'evors_lightbox', 'content'=>$viewattendee_content, 'title'=>__('View Attendee List','eventon'), 'type'=>'padded', 'max_height'=>450 ));
					echo $eventon->output_eventon_pop_window(array('class'=>'evors_email_attendee', 'content'=>$emailing_content, 'title'=>__('Email Attendee List','eventon'), 'type'=>'padded', 'max_height'=>450 ));
				?>
				</div>
			</div>
		</div>
	</div>
	</div>
	<?php
		echo ob_get_clean();
	}

/** Save the menu data meta box. **/
	function evoRS_save_meta_data($arr, $post_id){
		$fields = array(
			'evors_rsvp', 'evors_show_rsvp','evors_show_whos_coming','evors_add_emails','evors_close_time',
			'evors_capacity','evors_capacity_count','evors_capacity_show',
			'_manage_repeat_cap_rs','evors_additional_data','evors_min_cap','evors_min_count',
			'evors_max_active','evors_max_count', 'evors_daily_digest'
		);

		foreach($fields as $field){
			if(!empty($_POST[$field])){
				update_post_meta( $post_id, $field, $_POST[$field] );
			}else{
				delete_post_meta($post_id, $field);
			}
		}

		// repeat interval capacities
		if(!empty($_POST['ri_capacity_rs']) && $_POST['_manage_repeat_cap_rs']=='yes'){

			// get total
			$count = 0; 
			foreach($_POST['ri_capacity_rs'] as $cap){
				$count = $count + $cap;
			}
			update_post_meta( $post_id, 'ri_capacity_rs',$_POST['ri_capacity_rs']);
		}
			
	}