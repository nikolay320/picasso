<?php
/**
 * event card content processed and output as html
 * @version 2.3.21
 */
function eventon_eventcard_print($array, $evOPT, $evoOPT2){
	global $eventon;
	
	$evoOPT2 = (!empty($evoOPT2))? $evoOPT2: '';
	
	$OT ='';
	$count = 1;
	$items = count($array);	
	
	// close button
	$close = "<div class='evcal_evdata_row evcal_close' title='".eventon_get_custom_language($evoOPT2, 'evcal_lang_close','Close')."'></div>";

	// additional fields array 
	$_additions = apply_filters('evo_eventcard_adds' , array());
	
	// FOR each
	foreach($array as $box_f=>$box){
		
		$end = ($count == $items)? $close: null;
		$end_row_class = ($count == $items)? ' lastrow': null;
		
		// convert to an object
		$object = new stdClass();
		foreach ($box as $key => $value){
			$object->$key = $value;
		}
		
		$boxname = (in_array($box_f, $_additions))? $box_f: null;

		//echo($box_f.' ');
		//print_r($boxname);
		//$OT.="".$items.'-'.$count." ".$box_f;
		
		// each eventcard type
		switch($box_f){

			// addition
				case has_filter("eventon_eventCard_{$boxname}"):
				
					$helpers = array(
						'evOPT'=>$evOPT,
						'evoOPT2'=>$evoOPT2,
						'end_row_class'=>$end_row_class,
						'end'=>$end,
					);

					$OT.= apply_filters("eventon_eventCard_{$boxname}", $object, $helpers);							
					
				break;
				
			// Event Details
				case 'eventdetails':
					
					// check if character length of description is longer than X size
					if( !empty($evOPT['evo_morelass']) && $evOPT['evo_morelass']!='yes' && (strlen($object->fulltext) )>600 ){
						$more_code = 
							"<div class='eventon_details_shading_bot'>
								<p class='eventon_shad_p' content='less'><span class='ev_more_text' data-txt='".eventon_get_custom_language($evoOPT2, 'evcal_lang_less','less')."'>".eventon_get_custom_language($evoOPT2, 'evcal_lang_more','more')."</span><span class='ev_more_arrow'></span></p>
							</div>";
						$evo_more_active_class = 'shorter_desc';
					}else{
						$more_code=''; $evo_more_active_class = '';
					}
					
					$OT.="<div class='evo_metarow_details evorow evcal_evdata_row bordb evcal_event_details".$end_row_class."'>
							".$object->excerpt."
							<span class='evcal_evdata_icons'><i class='fa ".get_eventON_icon('evcal__fai_001', 'fa-align-justify',$evOPT )."'></i></span>
							<div class='evcal_evdata_cell ".$evo_more_active_class."'>".$more_code."<div class='eventon_full_description'>
									<h3 class='padb5 evo_h3'>".eventon_get_custom_language($evoOPT2, 'evcal_evcard_details','Event Details')."</h3><div class='eventon_desc_in' itemprop='description'>
									". $eventon->frontend->filter_evo_content($object->fulltext) ."</div>";
									// pluggable inside event details
									do_action('eventon_eventcard_event_details');
									$OT.="<div class='clear'></div>
								</div>
							</div>
						".$end."</div>";
								
				break;

			// TIME and LOCATION
				case 'timelocation':
					
					if($object->address || $object->location_name){

						$timezone = (!empty($object->timezone)? ' <em class="evo_eventcard_tiemzone">'. $object->timezone.'</em>':null);
						$locationLink = (!empty($object->location_link))? '<a target="_blank" href="'.$object->location_link.'">':false;

						$OT.= 
						"<div class='evo_metarow_time_location evorow bordb".$end_row_class." '>
						<div class='tb' >
							<div class='tbrow'>
							<div class='evcal_col50 bordr'>
								<div class='evcal_evdata_row evo_time'>
									<span class='evcal_evdata_icons'><i class='fa ".get_eventON_icon('evcal__fai_002', 'fa-clock-o',$evOPT )."'></i></span>
									<div class='evcal_evdata_cell'>							
										<h3 class='evo_h3'>".eventon_get_custom_language($evoOPT2, 'evcal_lang_time','Time')."</h3><p>".$object->timetext. $timezone. "</p>
									</div>
								</div>
							</div><div class='evcal_col50'>
								<div class='evcal_evdata_row evo_location'>
									<span class='evcal_evdata_icons'><i class='fa ".get_eventON_icon('evcal__fai_003', 'fa-map-marker',$evOPT )."'></i></span>
									<div class='evcal_evdata_cell'>							
										<h3 class='evo_h3'>".($locationLink? $locationLink:'').eventon_get_custom_language($evoOPT2, 'evcal_lang_location','Location').($locationLink?'</a>':'')."</h3>". ( (!empty($object->location_name))? "<p class='evo_location_name'>".stripslashes($object->location_name)."</p>":null ) ."<p>". ( !empty($object->address)? stripslashes($object->address): null)."</p>
									</div>
								</div>
							</div><div class='clear'></div>
							</div></div>
						".$end."</div>";
						
					}else{
					// time only
						
						$OT.="<div class='evo_metarow_time evorow evcal_evdata_row bordb evcal_evrow_sm ".$end_row_class."'>
							<span class='evcal_evdata_icons'><i class='fa ".get_eventON_icon('evcal__fai_002', 'fa-clock-o',$evOPT )."'></i></span>
							<div class='evcal_evdata_cell'>							
								<h3 class='evo_h3'>".eventon_get_custom_language($evoOPT2, 'evcal_lang_time','Time')."</h3><p>".$object->timetext."</p>
							</div>
						".$end."</div>";
						
					}
					
				break;

			// REPEAT SERIES
				case 'repeats':
					$OT.="<div class='evo_metarow_repeats evorow evcal_evdata_row bordb evcal_evrow_sm ".$end_row_class."'>
							<span class='evcal_evdata_icons'><i class='fa ".get_eventON_icon('evcal__fai_repeats', 'fa-repeat',$evOPT )."'></i></span>
							<div class='evcal_evdata_cell'>							
								<h3 class='evo_h3'>".eventon_get_custom_language($evoOPT2, 'evcal_lang_repeats','Future Event Times in this Repeating Event Series')."</h3>
								<p class='evo_repeat_series_dates'>";
						foreach($object->future_intervals as $interval){
							$OT.="<span>". date($object->date_format.' '.$object->time_format, $interval[0])."</span>";
						}
					$OT.="</p></div>".$end."</div>";
				break;

			// Location Image
				case 'locImg':
					$img_src = wp_get_attachment_image_src($object->id,'full');
					$fullheight = (int)$object->fullheight;

					if(!empty($img_src)){
						// text over location image
						$inside='';
						if(!empty($object->locName)){
							$inner = (!empty($object->locAdd))? '<span>'.$object->locAdd.'</span>':null;
							$inside = "<p class='evoLOCtxt'>{$object->locName}{$inner}</p>";
						}
						$OT.="<div class='evo_metarow_locImg evorow bordb ".( !empty($inside)?'tvi':null)."' style='height:{$fullheight}px; background-image:url(".$img_src[0].")' id='".$object->id."_locimg' >{$inside}</div>";
					}
				break;

			// GOOGLE map
				case 'gmap':					
					$OT.="<div class='evo_metarow_gmap evorow evcal_gmaps bordb ' id='".$object->id."_gmap' style='max-width:none'></div>";
				break;
			
			// Featured image
				case 'ftimage':
					
					$__hoverclass = (!empty($object->hovereffect) && $object->hovereffect!='yes')? ' evo_imghover':null;
					$__noclickclass = (!empty($object->clickeffect) && $object->clickeffect=='yes')? ' evo_noclick':null;
					$__zoom_cursor = (!empty($evOPT['evo_ftim_mag']) && $evOPT['evo_ftim_mag']=='yes')? ' evo_imgCursor':null;

					// if set to direct image
					if(!empty($evOPT['evo_ftimg_height_sty']) && $evOPT['evo_ftimg_height_sty']=='direct'){
						$OT .= "<div class='evo_metarow_directimg'><img src='{$object->img[0]}'/></div>";
					}else{
						$height = !empty($object->img[2])? $object->img[2]:'';
						$width = !empty($object->img[1])? $object->img[1]:'';
						$OT.= "<div class='evo_metarow_fimg evorow evcal_evdata_img ".$end_row_class.$__hoverclass.$__zoom_cursor.$__noclickclass."' data-imgheight='".$height."' data-imgwidth='".$width."'  style='background-image: url(".$object->img[0].")' data-imgstyle='".$object->ftimg_sty."' data-minheight='".$object->min_height."' data-status=''>".$end."</div>";
					}
					
				break;
			
			// event organizer
				case 'organizer':					
					$evcal_evcard_org = eventon_get_custom_language($evoOPT2, 'evcal_evcard_org','Organizer');

					$ORGMeta = get_option( "taxonomy_".$object->organizer_term_id);

					$img_src = (!empty($ORGMeta['evo_org_img'])? 
						wp_get_attachment_image_src($ORGMeta['evo_org_img'],'medium'): null);

					// organizer name text openinnewwindow
						if(!empty($ORGMeta['evcal_org_exlink'])){
							$orgNAME = "<span class='evo_card_organizer_name_t'><a ".( $object->openinnewwindow=='yes'?'target="_blank"':'')." href='{$ORGMeta['evcal_org_exlink']}'>".$object->organizer_name."</a></span>";
						}else{
							$orgNAME = "<span class='evo_card_organizer_name_t'>".$object->organizer_name."</span>";
						}					
							
					
					$OT.= "<div class='evo_metarow_organizer evorow evcal_evdata_row bordb evcal_evrow_sm ".$end_row_class."'>
							<span class='evcal_evdata_icons'><i class='fa ".get_eventON_icon('evcal__fai_004', 'fa-headphones',$evOPT )."'></i></span>
							<div class='evcal_evdata_cell'>							
								<h3 class='evo_h3'>".$evcal_evcard_org."</h3>
								".(!empty($img_src)? 
									"<p class='evo_data_val evo_card_organizer_image'><img src='{$img_src[0]}'/></p>":null)."
								<div class='evo_card_organizer'><p class='evo_data_val evo_card_organizer_name'>
									".$orgNAME.(!empty($ORGMeta['evcal_org_contact'])? 
									"<span class='evo_card_organizer_contact'>{$ORGMeta['evcal_org_contact']}</span>":null)."
									".(!empty($ORGMeta['evcal_org_address'])? 
									"<span class='evo_card_organizer_address'>{$ORGMeta['evcal_org_address']}</span>":null)."
									</p></div>								
							</div>
						".$end."</div>";
					
				break;
			
			// get directions
				case 'getdirection':
					
					$_lang_1 = eventon_get_custom_language($evoOPT2, 'evcalL_getdir_placeholder','Type your address to get directions');
					$_lang_2 = eventon_get_custom_language($evoOPT2, 'evcalL_getdir_title','Click here to get directions');
					
					$OT.="<div class='evo_metarow_getDr evorow evcal_evdata_row bordb evcal_evrow_sm getdirections'>
						<form action='https://maps.google.com/maps' method='get' target='_blank'>
						<input type='hidden' name='daddr' value='{$object->fromaddress}'/> 
						<p><input class='evoInput' type='text' name='saddr' placeholder='{$_lang_1}' value=''/>
						<button type='submit' class='evcal_evdata_icons evcalicon_9' title='{$_lang_2}'><i class='fa ".get_eventON_icon('evcal__fai_008a', 'fa-road',$evOPT )."'></i></button>
						</p></form>
					</div>";
					
				break;
					
			// learnmore ICS and close button
				case 'learnmoreICS':				

					// Initial 
						$__ics_url =admin_url('admin-ajax.php').'?action=eventon_ics_download&amp;event_id='.$object->event_id.'&amp;sunix='.$object->estart.'&amp;eunix='.$object->eend;
						$__googlecal_link = eventon_get_addgoogle_cal($object);

					// which options to show for add to calendar
						$addCaloptions = !empty($evOPT['evo_addtocal'])? $evOPT['evo_addtocal']: 'all';
						$addCalContent = '';

					// add to cal section
						switch($addCaloptions){
							case 'ics':
								$addCalContent = "<a href='{$__ics_url}' class='evo_ics_nCal' title='".eventon_get_custom_language($evoOPT2, 'evcal_evcard_addics','Add to your calendar')."'>".eventon_get_custom_language($evoOPT2, 'evcal_evcard_calncal','Calendar')."</a>";
							break;
							case 'gcal':
								$addCalContent = "<a href='{$__googlecal_link}' target='_blank' class='evo_ics_gCal' title='".eventon_get_custom_language($evoOPT2, 'evcal_evcard_addgcal','Add to google calendar')."'>".eventon_get_custom_language($evoOPT2, 'evcal_evcard_calgcal','GoogleCal')."</a>";
							break;
							case 'all':
								$addCalContent = "<a href='{$__ics_url}' class='evo_ics_nCal' title='".eventon_get_custom_language($evoOPT2, 'evcal_evcard_addics','Add to your calendar')."'>".eventon_get_custom_language($evoOPT2, 'evcal_evcard_calncal','Calendar')."</a>".
									"<a href='{$__googlecal_link}' target='_blank' class='evo_ics_gCal' title='".eventon_get_custom_language($evoOPT2, 'evcal_evcard_addgcal','Add to google calendar')."'>".eventon_get_custom_language($evoOPT2, 'evcal_evcard_calgcal','GoogleCal')."</a>";
							break;
						}

					

					// learn more and ICS
					if( !empty($object->learnmorelink) && $addCaloptions!='none'){
						
						ob_start();					
						?>
						<div class='evo_metarow_learnMICS evorow bordb <?php echo $end_row_class;?>'>
						<div class='tb'>
							<div class='tbrow'>
							<a class='evcal_col50 dark1 bordr evo_clik_row' href='<?php echo $object->learnmorelink;?>' <?php echo $object->learnmore_target;?>>
								<span class='evcal_evdata_row ' >
									<span class='evcal_evdata_icons'><i class='fa <?php echo get_eventON_icon('evcal__fai_006', 'fa-link',$evOPT );?>'></i></span>
									<h3 class='evo_h3'><?php echo eventon_get_custom_language($evoOPT2, 'evcal_evcard_learnmore2','Learn More');?></h3>
								</span>
							</a>						
							<div class='evo_ics evcal_col50 dark1 evo_clik_row' >
								<div class='evcal_evdata_row'>
									<span class="evcal_evdata_icons"><i class="fa fa-calendar"></i></span>
									<div class='evcal_evdata_cell'>
										<p><?php echo $addCalContent;?></p>	
									</div>
								</div>
							</div></div></div>
						<?php echo $end;?></div>
						<?php
						$OT.= ob_get_clean();
					
					// only learn more
					}else if(!empty($object->learnmorelink) ){
						$OT.="<div class='evo_metarow_learnM evorow bordb'>
							<a class='evcal_evdata_row evo_clik_row dark1 ' href='".$object->learnmorelink."' ".$object->learnmore_target.">
								<span class='evcal_evdata_icons'><i class='fa ".get_eventON_icon('evcal__fai_006', 'fa-link',$evOPT )."'></i></span>
								<h3 class='evo_h3'>".eventon_get_custom_language($evoOPT2, 'evcal_evcard_learnmore2','Learn More')."</h3>
							</a>
							".$end."</div>";

					// only ICS
					}else if($addCaloptions!='none'){

						ob_start();
						//echo get_option('gmt_offset', 0).'ttt';
						?>
						<div class='evo_metarow_ICS evorow bordb evcal_evdata_row'>
							<span class="evcal_evdata_icons"><i class="fa fa-calendar"></i></span>
							<div class='evcal_evdata_cell'>
								<p><?php echo $addCalContent;?></p>	
							</div><?php echo $end;?>
						</div>
						<?php
						$OT.= ob_get_clean();
					}
				
				break;
		
			// paypal link
					case 'paypal':
						$text = (!empty($object->text))? $object->text: eventon_get_custom_language($evoOPT2, 'evcal_evcard_tix1','Buy ticket via Paypal');

						$email = $object->email;
						$currency = !empty($evOPT['evcal_pp_cur'])? $evOPT['evcal_pp_cur']: false;


						if($currency && $email):

							// get proper time to append to event title 
							$evodate = new evo_datetime();
							$eventtime = $evodate->evo_date( $object->estart, $object->evvals);

						ob_start();

						?>

						<div class='evo_metarow_paypal evorow evcal_evdata_row bordb evo_paypal'>
								<span class='evcal_evdata_icons'><i class='fa <?php echo get_eventON_icon('evcal__fai_007', 'fa-ticket',$evOPT );?>'></i></span>
								<div class='evcal_evdata_cell'>
									<p><?php echo $text;?></p>
									<form target="_blank" name="_xclick" action="https://www.paypal.com/us/cgi-bin/webscr" method="post">
										<input type="hidden" name="cmd" value="_xclick">
										<input type="hidden" name="business" value="<?php echo $email;?>">
										<input type="hidden" name="currency_code" value="<?php echo $currency;?>">
										<input type="hidden" name="item_name" value="<?php echo $object->title.' '.$eventtime;?>">
										<input type="hidden" name="amount" value="<?php echo $object->price;?>">
										<input type='submit' class='evcal_btn' value='<?php echo eventon_get_custom_language($evoOPT2, 'evcal_evcard_btn1','Buy Now');?>'/>
									</form>
									
								</div><?php echo $end;?></div>
						
						<?php $OT.= ob_get_clean();
						endif;

					break;
			
		}// end switch

		// for custom meta data fields
			if(!empty($object->x) && $box_f == 'customfield'.$object->x){
				$i18n_name = eventon_get_custom_language($evoOPT2,'evcal_cmd_'.$object->x , $evOPT['evcal_ec_f'.$object->x.'a1']);

				if( ($object->visibility_type=='admin' && !current_user_can( 'manage_options' ) ) ||
					($object->visibility_type=='loggedin' && !is_user_logged_in() )
				) continue;

				$OT .="<div class='evo_metarow_cusF{$object->x} evorow evcal_evdata_row bordb evcal_evrow_sm '>
						<span class='evcal_evdata_custometa_icons'><i class='fa ".$object->imgurl."'></i></span>
						<div class='evcal_evdata_cell'>							
							<h3 class='evo_h3'>".$i18n_name."</h3>";
					if($object->type=='button'){
						$_target = (!empty($object->_target) && $object->_target=='yes')? 'target="_blank"':null;
						$OT .="<a href='".$object->valueL."' {$_target} class='evcal_btn evo_cusmeta_btn'>".$object->value."</a>";
					}else{
						$OT .="<div class='evo_custom_content evo_data_val'>". 
						(  $eventon->frontend->filter_evo_content($object->value) )."</div>";
					}
						$OT .="</div>".$end."</div>";
			}
		
		$count++;
	
	}// end foreach
	
	return $OT;
	
}	
	
	
?>