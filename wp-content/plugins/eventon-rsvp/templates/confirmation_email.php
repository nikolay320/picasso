<?php
/**
 * Confirmation email sent to the attendee
 * @version 	0.3
 *
 * To Customize this template: copy and paste this file to .../wp-content/themes/--your-theme-name--/eventon/templates/email/rsvp/ folder and edit that file.
 */

	global $eventon, $eventon_rs;
	echo $eventon->get_email_part('header');

	$args = $args;

	$event_name = get_the_title($args['e_id']);
	$event = get_post($args['e_id']);
	$e_pmv = get_post_meta($args['e_id'] );
	$rsvp_pmv = get_post_custom($args['rsvp_id']);
	
	$evo_options = get_option('evcal_options_evcal_1');
	$evo_options_2 = $eventon_rs->opt2;	
	$optRS = $eventon_rs->evors_opt;

	$lang = (!empty($args['lang']))? $args['lang']: 'L1';	 // language version

	//event time
		$repeat_interval = !empty($args['repeat_interval'])? $args['repeat_interval'][0]:0;		
		$time = $eventon_rs->frontend->functions->get_proper_times($e_pmv, $repeat_interval);

	// location data
		$location = (!empty($e_pmv['evcal_location_name'])? $e_pmv['evcal_location_name'][0].': ': null).(!empty($e_pmv['evcal_location'])? $e_pmv['evcal_location'][0]:null);

	//	styles
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
		$__styles_07 = "display: inline-block;padding: 5px 10px;border: 1px solid #B7B7B7;";
		$__sty_td ="padding:0px;border:none; text-align:center;";
		$__sty_m0 ="margin:0px;";

	// reused elements
		$__item_p_beg = "<p style='{$__styles_02}'><span style='{$__styles_02a}'>";
?>

<table width='100%' style='width:100%; margin:0; font-family:"open sans"'>
	<tr>
		<td style='<?php echo $__sty_td;?>'>
			<div style="padding:45px 20px; font-family:'open sans'">
				<p style='<?php echo $__sty_lh;?>font-size:18px; font-style:italic; margin:0'><?php echo $eventon_rs->lang('evoRSLX_009', 'You have RSVP-ed', $lang)?></p>
				<p style='<?php echo $__styles_07;?>'><?php echo $eventon_rs->frontend->get_rsvp_status($args['rsvp'], $lang);?></p>
				<p style='<?php echo $__styles_01.$__sty_lh;?> padding-bottom:15px;'><?php echo $event_name;?></p>

				<?php echo $__item_p_beg;?><?php echo $eventon_rs->lang('evoRSLX_008', 'Event Time', $lang)?>:</span> <?php echo $time;?></p>
				<p style='<?php echo $__styles_02;?> padding-top:10px;'><span style='<?php echo $__styles_02a;?>'><?php echo $eventon_rs->lang('evoRSLX_009', 'Event Details', $lang)?>:</span><br/><?php echo $event->post_content;?></p>

				<p style='<?php echo $__styles_02;?> padding-top:10px;'><span style='<?php echo $__styles_02a;?>'><?php echo $eventon_rs->lang('evoRSL_007a', 'RSVP ID', $lang)?>:</span> # <?php echo $args['rsvp_id'];?></p>

				<?php echo $__item_p_beg;?><?php echo $eventon_rs->lang('evoRSLX_002', 'Primary Contact on RSVP', $lang)?>:</span> <?php echo $args['last_name'].' '.$args['first_name'];?></p>

				<p style='<?php echo $__styles_02;?> padding-bottom:40px;'><span style='<?php echo $__styles_02a;?>'><?php echo $eventon_rs->lang('evoRSLX_003', 'Spaces', $lang)?>:</span> <?php echo evo_meta($rsvp_pmv,'count');?></p>

				
				<?php 
				//additional fields
				for($x=1; $x<4; $x++){
					if(evo_settings_val('evors_addf'.$x, $optRS) && !empty($optRS['evors_addf'.$x.'_1'])  ){
						echo $__item_p_beg. $optRS['evors_addf'.$x.'_1'].": </span>".( (!empty($rsvp_pmv['evors_addf'.$x.'_1']))? $rsvp_pmv['evors_addf'.$x.'_1'][0]: '-')."</p>";
					}
				}
				
				//-- additional information -->
					if(!empty($e_pmv['evors_additional_data'])){?>
						<p style='<?php echo $__styles_04;?>'><?php echo evo_lang('Additional Information', $lang);?></p>
						<p style='<?php echo $__styles_03;?> padding-bottom:10px;'><?php echo $e_pmv['evors_additional_data'][0];?></p><?php
					}?>	

				<!-- location -->
				<?php if(!empty($location)):?>
					<p style='<?php echo $__styles_04;?>'><?php echo $eventon_rs->lang('evoRSLX_003x', 'Location', $lang)?></p>
					<p style='<?php echo $__styles_03;?> padding-bottom:10px;'><?php echo $location;?></p>
				<?php endif;?>
				
				<?php do_action('eventonrs_confirmation_email', $args['rsvp_id'], $rsvp_pmv, $args['rsvp']);?>
				
				<?php //add to calendar ?>
				<p><a style='<?php echo $__styles_button;?>' href='<?php echo admin_url();?>admin-ajax.php?action=eventon_ics_download&event_id=<?php echo $args['e_id'];?>&sunix=<?php echo $e_pmv['evcal_srow'][0];?>&eunix=<?php echo $e_pmv['evcal_erow'][0];?>' target='_blank'><?php echo $eventon_rs->lang('evcal_evcard_addics', 'Add to calendar', $lang);?></a></p>
			</div>
		</td>
	</tr>
	<tr>
		<td  style='padding:20px; text-align:left;border-top:1px dashed #d1d1d1; font-style:italic; color:#ADADAD; text-align:center'>
			<?php
				$__link = (!empty($evo_options['evors_contact_link']))? $evo_options['evors_contact_link']:site_url();
			?>
			<p style='<?php echo $__sty_lh.$__sty_m0;?> padding-bottom:5px;'><?php echo $eventon_rs->lang('evoRSLX_005', 'We look forward to seeing you!', $lang)?></p>
			<p style='<?php echo $__sty_lh.$__sty_m0;?>'><a style='' href='<?php echo $__link;?>'><?php echo $eventon_rs->lang('evoRSLX_006', 'Contact Us for questions and concerns', $lang)?></a></p>
		</td>
	</tr>
</table>
<?php
	echo $eventon->get_email_part('footer');
?>