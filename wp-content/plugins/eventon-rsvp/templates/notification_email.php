<?php
/**
 * Notification email sent to ADMIN
 * @version 	0.3
 *
 * To Customize this template: copy and paste this file to .../wp-content/themes/--your-theme-name--/eventon/templates/email/rsvp/ folder and edit that file.
 */

	global $eventon, $eventon_rs;
	echo $eventon->get_email_part('header');

	$args = $args;

	$event_name = get_the_title($args['e_id']);
	$e_pmv = get_post_meta($args['e_id'] );
	$rsvp_pmv = get_post_custom($args['rsvp_id']);
	
	$evo_options = get_option('evcal_options_evcal_1');
	$evo_options_2 = $eventon_rs->opt2;	
	$optRS = $eventon_rs->evors_opt;

	$lang = (!empty($args['lang']))? $args['lang']: 'L1';	 // language version
	$repeat_interval = (!empty($args['repeat_interval']))? $args['repeat_interval']: '0';	 // repeating interval

	//event time
		$date_string = $eventon_rs->frontend->functions->get_proper_times($e_pmv, $repeat_interval);

	// location data
		$location = (!empty($e_pmv['evcal_location_name'])? $e_pmv['evcal_location_name'][0].': ': null).(!empty($e_pmv['evcal_location'])? $e_pmv['evcal_location'][0]:null);

	// notification email type
		$header_text = (!empty($args['emailtype']) && $args['emailtype']=='update')? 
			$eventon_rs->lang('evoRSLX_010a', 'Update to RSVP From', $lang): 
			$eventon_rs->lang('evoRSLX_010', 'New RSVP From', $lang);

	//styles
		$__styles_date = "font-size:48px; color:#ABABAB; font-weight:bold; margin-top:5px";
		$__styles_em = "font-size:14px; font-weight:bold; text-transform:uppercase; display:block;font-style:normal";
		$__styles_button = "font-size:14px; background-color:#".( !empty($evo_options['evcal_gen_btn_bgc'])? $evo_options['evcal_gen_btn_bgc']: "237ebd")."; color:#".( !empty($evo_options['evcal_gen_btn_fc'])? $evo_options['evcal_gen_btn_fc']: "ffffff")."; padding: 5px 10px; text-decoration:none; border-radius:4px; ";
		$__styles_01 = "font-size:30px; color:#303030; font-weight:bold; text-transform:uppercase; margin-bottom:0px;  margin-top:0;";
		$__styles_02 = "font-size:18px; color:#303030; font-weight:normal; text-transform:uppercase; display:block; font-style:italic; margin: 4px 0; line-height:110%;";
		$__sty_lh = "line-height:110%;";
		$__styles_02a = "color:#afafaf; text-transform:none";
		$__styles_03 = "color:#afafaf; font-style:italic;font-size:14px; margin:0 0 10px 0;";
		$__styles_04 = "color:#303030; text-transform:uppercase; font-size:18px; font-style:italic; padding-bottom:0px; margin-bottom:0px; line-height:110%;";
		$__styles_05 = "padding-bottom:40px; ";
		$__styles_06 = "border-bottom:1px dashed #d1d1d1; padding:5px 20px";
		$__sty_td ="padding:0px;border:none";
		$__sty_m0 ="margin:0px;";
		$__sty_button ="display: inline-block;padding: 5px 10px;border: 1px solid #B7B7B7; text-decoration:none; font-style:normal;";
	
	// reused elements
		$__item_p_beg = "<p style='{$__styles_02}'><span style='{$__styles_02a}'>";
?>

<table width='100%' style='width:100%; margin:0;font-family:"open sans"'>
	<tr>
		<td style='<?php echo $__sty_td;?>'>			
			<div style="padding:20px; font-family:'open sans'">
				<p style='<?php echo $__sty_lh;?>font-size:18px; font-style:italic; margin:0; padding-bottom:10px; text-transform:uppercase'><?php echo $header_text;?></p>
				<p style='<?php echo $__styles_01.$__sty_lh;?>'><?php echo $args['last_name'].' '.$args['first_name'];?></p>
				
				<!-- rsvp ID-->
				<p style='<?php echo $__styles_02;?> padding-top:15px;'><span style='<?php echo $__styles_02a;?>'><?php echo $eventon_rs->lang('evoRSL_007a', 'RSVP ID', $lang)?>:</span> # <?php echo $args['rsvp_id'];?></p>
				<!-- RSVP status -->
				<?php echo $__item_p_beg;?><?php echo $eventon_rs->lang('evoRSLX_001', 'RSVP Status', $lang)?>:</span> <?php echo $eventon_rs->frontend->get_rsvp_status($args['rsvp'], $lang);?></p>
				<!-- name-->
				<?php echo $__item_p_beg;?><?php echo $eventon_rs->lang('evoRSLX_008a', 'Event Name', $lang)?>:</span> <?php echo $event_name;?></p>
				<!-- email address-->
				<?php echo $__item_p_beg;?><?php echo $eventon_rs->lang('evoRSL_009', 'Email Address', $lang)?>:</span> <?php echo $args['email'];?></p>

				<?php if(!empty($rsvp_pmv['phone'])):?>
				<?php echo $__item_p_beg;?><?php echo $eventon_rs->lang('evoRSL_009a', 'Phone Number', $lang)?>:</span> <?php echo evo_meta($rsvp_pmv,'phone');?></p><?php endif;?>

				<?php echo $__item_p_beg;?><?php echo $eventon_rs->lang('evoRSLX_003', 'Spaces', $lang)?>:</span> <?php echo evo_meta($rsvp_pmv,'count');?></p>
				<!-- event time -->
				<?php echo $__item_p_beg;?><?php echo $eventon_rs->lang('evoRSLX_008', 'Event Time', $lang)?>:</span> <?php echo $date_string;?></p>


				<?php echo $__item_p_beg;?><?php echo $eventon_rs->lang('evoRSLX_003a', 'Receive Updates', $lang)?>:</span> <?php echo evo_meta($rsvp_pmv,'updates');?></p>
				<?php 

				//additional fields
				for($x=1; $x<4; $x++){
					if(evo_settings_val('evors_addf'.$x, $optRS) && !empty($optRS['evors_addf'.$x.'_1'])  && !empty($rsvp_pmv['evors_addf'.$x.'_1'])){
						echo $__item_p_beg. $optRS['evors_addf'.$x.'_1'].": </span>".( (!empty($rsvp_pmv['evors_addf'.$x.'_1']))? $rsvp_pmv['evors_addf'.$x.'_1'][0]: '-')."</p>";
					}
				}
				?>
			</div>
		</td>
	</tr>
	<?php
		$event_edit_link = $eventon_rs->frontend->functions->edit_post_link($args['e_id']);
		$rsvp_edit_link = $eventon_rs->frontend->functions->edit_post_link($args['rsvp_id']);

		if(!empty($rsvp_edit_link) && !empty($event_edit_link)):
	?>
	<tr>
		<td  style='padding:20px; text-align:left;border-top:1px dashed #d1d1d1; font-style:italic; color:#ADADAD'>				
			<p style='<?php echo $__sty_lh.$__sty_m0;?>'><a style='<?php echo $__sty_button;?>' target='_blank' href='<?php echo $rsvp_edit_link; ?>'>View RSVP</a> | <a style='<?php echo $__sty_button;?>' target='_blank' href='<?php  echo $event_edit_link;?>'>Edit Event</a></p>
		</td>
	</tr>
	<?php endif;?>
</table>



<?php
	echo $eventon->get_email_part('footer');
?>

