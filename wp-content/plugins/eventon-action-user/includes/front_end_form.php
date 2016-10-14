<?php
/**
 * OUTPUT submission form
 * @version 	0.3
 */
	
	global $eventon_au, $eventon;

	$evoopt= $this->evoau_opt;
	$evoopt_1= get_option('evcal_options_evcal_1');
	$opt_2 = get_option('evcal_options_evcal_2');

	$FIELD_ORDER = !empty($evoopt['evoau_fieldorder'])? array_filter(explode(',',$evoopt['evoau_fieldorder'])): false;
	$SELECTED_FIELDS = (!empty($evoopt['evoau_fields']))?
		( (is_array($evoopt['evoau_fields']) && count($evoopt['evoau_fields'])>0 )? $evoopt['evoau_fields']:
			array_filter(explode(',', $evoopt['evoau_fields']))):
		false;

	// the form type
		$_EDITFORM = (isset($_REQUEST['action']) && $_REQUEST['action']=='edit' && !empty($event_id))? true:false;
		$_EID = ($_EDITFORM && isset($_REQUEST['eid']))? $_REQUEST['eid']: null;
	
	// language for the form fields
		$lang = (!empty($atts['lang'])? $atts['lang']:'L1');

	//if shortcode arguments passed
		$atts = !empty($atts)? $atts: false;
		$_LIGTHBOX = ($atts && !empty($atts['ligthbox']) && $atts['ligthbox']=='yes')? true:false;
		$_msub = ($atts && !empty($atts['msub']) && $atts['msub']=='yes')? true:false;

	// login required
		$LOGINCHECK = (!empty($evoopt['evoau_access']) && $evoopt['evoau_access']=='yes' && !is_user_logged_in())? true:false;

	// limit submissions to one
		$LIMITSUB = (!empty($evoopt['evoau_limit_submissions']) && $evoopt['evoau_limit_submissions']=='yes' && isset($_COOKIE['evoau_event_submited']) && $_COOKIE['evoau_event_submited']=='yes')? true: false;

?>

<?php if($_LIGTHBOX):?>
	<a class='evoAU_form_trigger_btn'><?php echo !empty($atts['btntxt'])?$atts['btntxt']:'Submit New Event';?></a>
<?php endif;?>

<div class='eventon_au_form_section <?php echo ($_LIGTHBOX)?'overLay':'';?>' style='display:<?php echo $_LIGTHBOX?'none':'block';?>'>
<div id='eventon_form' class='evoau_submission_form <?php echo $LOGINCHECK?'loginneeded':''; echo $LIMITSUB?' limitSubmission':'';?>'>
	<a class='closeForm'>X</a>
	<form method="POST" action="" enctype="multipart/form-data" id='evoau_form' class='<?php echo ( $SELECTED_FIELDS && in_array('event_captcha', $SELECTED_FIELDS))?'captcha':null;?>' data-msub='<?php echo ($_msub)?'ow':'nehe';?>' data-redirect='<?php echo ($atts && !empty($atts['rlink']) && !empty($atts['rdir']) && $atts['rdir']=='yes')?$atts['rlink']:'nehe';?>' data-rdur='<?php echo ($atts && !empty($atts['rdur']))? $atts['rdur']:'';?>' data-limitsubmission='<?php echo (!empty($evoopt['evoau_limit_submissions']) && $evoopt['evoau_limit_submissions']=='yes')?'ow':'nehe';?>'>

	<?php 
		// form type parameters passing
		if($_EDITFORM){
			echo '<input type="hidden" name="form_action" value="editform"/>';
			echo '<input type="hidden" name="eventid" value="'.$event_id.'"/>';
		}
	?>
		<input type='hidden' name='action' value='evoau_event_submission'>
	<?php 	wp_nonce_field( AJDE_EVCAL_BASENAME, 'evoau_noncename' );	?>
		
		<div class='inner' style='display:<?php echo $LIMITSUB?'none':'block';?>'>
		<h2><?php echo $_EDITFORM? eventon_get_custom_language($opt_2, 'evoAUL_ese', 'Edit Submitted Event', $lang):
			(($atts && !empty($atts['header']))? stripslashes($atts['header']):  
			((!empty($evoopt['evo_au_title']))? stripslashes($evoopt['evo_au_title']):'Submit your event'));?></h2>
		<?php
			// form subtitle text
			$SUBTITLE = ($atts && !empty($atts['sheader']))? $atts['sheader']:
				(!empty($evoopt['evo_au_stitle'])? $evoopt['evo_au_stitle']: false);
			echo ($SUBTITLE)? '<h3>'.stripslashes($SUBTITLE).'</h3>':null;?>		
		<?php
		
	//access control to form
		if($LOGINCHECK ):
			$__001 = eventon_get_custom_language($opt_2, 'evoAUL_ymlse', 'You must login to submit events.', $lang);
			$text_login = eventon_get_custom_language($opt_2, 'evoAUL_00l1', 'Login', $lang);
			$text_register = eventon_get_custom_language($opt_2, 'evoAUL_00l2', 'Register', $lang);
			
			$log_msg = $__001. (sprintf(__(' <br/><a class="evcal_btn" title="%1$s" href="%2$s">%1$s</a>','eventon'), $text_login, wp_login_url(get_permalink()) ) );			

			// register new user
				if (get_option('users_can_register')){
					$log_msg.= (sprintf(__(' <a class="evcal_btn" title="%1$s" href="%2$s/wp-login.php?action=register">%1$s</a>','eventon'), $text_register, get_bloginfo('wpurl') ) );
				}
			echo "<p class='eventon_form_message'><span>".$log_msg."</span></p>";							
		else:	
	?>
		<div class='evoau_table'>
		<?php	
			// initials	
				$evcal_date_format = eventon_get_timeNdate_format();
				$timeFormat = ($evcal_date_format[2])? 'H:i':'h:i:a';
				$EPMV = '';
				if($_EDITFORM)
					$EPMV = get_post_custom($event_id);		
			
			// form messages
				echo "<div class='form_msg' style='display:none'></div>";
			
			// get all the fields
				$FORM_FIELDS = $eventon_au->frontend->au_form_fields();				
				if(!empty($FIELD_ORDER)){
					$EACH_FIELD = $FIELD_ORDER;
					$EACH_FIELD = array_merge( $this->au_form_fields('defaults_ar') , $EACH_FIELD);
				}else{
					$EACH_FIELD = $FORM_FIELDS;
					$EACH_FIELD = array_merge($this->au_form_fields('default'), $EACH_FIELD);
				}	

			// if the user is loggedin
			if(is_user_logged_in() ) $current_user = wp_get_current_user();

			// EACH field array from $eventon_au->au_form_fields()
				foreach($EACH_FIELD  as $__index=>$ff):

					$INDEX = (!empty($FIELD_ORDER))? $ff:$__index;
					
					if( ($SELECTED_FIELDS && in_array($INDEX, $SELECTED_FIELDS) )
						|| in_array($INDEX, $this->au_form_fields('defaults_ar')) ){

						// get form array for the field parameter
							if(empty($FORM_FIELDS[$INDEX])) continue;

							$field = $FORM_FIELDS[$INDEX];

							$__field_name = (!empty($field[4]))?  
								eventon_get_custom_language($opt_2, $field[4], $field[0], $lang) :$field[0];
							$__field_type = $field[2];
							$_placeholder = (!empty($field[3]))? "placeholder='".__($field[3],'eventon')."'":null;
							$__field_id =$field[1];
							$__req = (!empty($field[5]) && $field[5]=='req')? ' *':null;
							$__req_ = (!empty($field[5]) && $field[5]=='req')? ' req':null;
					
						// dont show name and email field is user is logged in
							if(is_user_logged_in() && ($INDEX=='yourname' || $INDEX=='youremail') && !empty($current_user) ){

								if($INDEX=='yourname')
									echo "<input type='hidden' name='yourname' value='{$current_user->display_name}'/>";
								if($INDEX=='youremail')
									echo "<input type='hidden' name='youremail' value='{$current_user->user_email}'/>";

								continue;
							}

						// default value for fields
							$default_val = (!empty($_POST[$__field_id]))? $_POST[$__field_id]: null;
							if($EPMV){								
							 	$default_val = !empty($EPMV[$__field_id])? $EPMV[$__field_id][0]:$default_val;
							}

						// switch statement for dif fields
						switch($__field_type){
							// pluggable
								case has_action("evoau_frontform_{$__field_type}"):
									do_action('evoau_frontform_'.$__field_type, $field, $event_id, $default_val, $EPMV, $opt_2, $lang);
								break;


							// default fields
								case 'title':
									if($EPMV)
										$default_val = get_the_title($event_id);
									echo "<div class='row'>
										<p class='label'>
										<input id='_evo_date_format' type='hidden' name='_evo_date_format' jq='".$evcal_date_format[0]."' value='".$evcal_date_format[1]."'/>
										<input id='_evo_time_format' type='hidden' name='_evo_time_format' value='".(($evcal_date_format[2])?'24h':'24h')."'/>
										<label for='event_name'>".$__field_name." *</label></p>
										<p><input type='text' class='fullwidth req' name='event_name' value='".$default_val."' placeholder='".$__field_name."'/></p>
									</div>";
								break;
								case 'startdate':
									$isAllDay = (!empty($EPMV['evcal_allday']) && $EPMV['evcal_allday'][0]=='yes')? 'display:none': '';

									$SD = ($EPMV)? date($evcal_date_format[1], $EPMV['evcal_srow'][0]):
										((!empty($_POST['event_start_date']))? $_POST['event_start_date']: null);
									$ST = ($EPMV)? date($timeFormat, $EPMV['evcal_srow'][0]):
										((!empty($_POST['event_start_time']))? $_POST['event_start_time']: null);

									echo "<div class='row'>
										<p class='label'><label for='event_start_date'>".$__field_name." *</label></p>
										<p><input id='evoAU_start_date' type='text' readonly='true' class='evoau_dpicker req datepickerstartdate' name='event_start_date' placeholder='".eventon_get_custom_language($opt_2, 'evoAUL_phsd', 'Start Date', $lang)."' value='".$SD."'/><input class='evoau_tpicker req time' type='text' name='event_start_time' placeholder='".eventon_get_custom_language($opt_2, 'evoAUL_phst', 'Start Time', $lang)."' value='".$ST."' style='{$isAllDay}'/></p>
									</div>";
								break;
								case 'enddate':
									$isAllDay = (!empty($EPMV['evcal_allday']) && $EPMV['evcal_allday'][0]=='yes')? 'display:none': '';

									$ED = ($EPMV)? date($evcal_date_format[1], $EPMV['evcal_erow'][0]):
										((!empty($_POST['event_end_date']))? $_POST['event_end_date']: null);
									$ET = ($EPMV)? date($timeFormat, $EPMV['evcal_erow'][0]):
										((!empty($_POST['event_end_time']))? $_POST['event_end_time']: null);

									echo "<div class='row' id='evoAU_endtime_row'>
										<p class='label'><label for='event_end_date'>".$__field_name." *</label></p>
										<p><input id='evoAU_end_date' class='evoau_dpicker req end datepickerenddate' readonly='true' type='text' name='event_end_date' placeholder='".eventon_get_custom_language($opt_2, 'evoAUL_phed', 'End Date', $lang)."' value='".$ED."'/><input class='evoau_tpicker req end time' type='text' name='event_end_time' placeholder='".eventon_get_custom_language($opt_2, 'evoAUL_phet', 'End Time', $lang)."' value='".$ET."' style='{$isAllDay}'/></p>
									</div>";
								break;
							case 'allday':
								$helper = new evo_helper();
								echo "<div class='row'>
									<p class='label'>";
								echo $helper->html_yesnobtn(array(
									'id'=>'evcal_allday',
									'input'=>true,
									'label'=>eventon_get_custom_language($opt_2, 'evoAUL_001', 'All Day Event', $lang),
									'var'=> (($EPMV && !empty($EPMV['evcal_allday']) && $EPMV['evcal_allday'][0]=='yes')?'yes':'no'),
									'lang'=>$lang
								));
								echo "</p>";

								echo "<p class='label' style='padding-top:5px'>";
								echo $helper->html_yesnobtn(array(
									'id'=>'evo_hide_endtime',
									'input'=>true,
									'label'=>eventon_get_custom_language($opt_2, 'evoAUL_002', 'No end time', $lang),
									'var'=> (($EPMV && !empty($EPMV['evo_hide_endtime']) && $EPMV['evo_hide_endtime'][0]=='yes')?'yes':'no'),
									'lang'=>$lang
								));
								echo "</p>";

								//echo "<input id='evoAU_all_day' name='event_all_day' type='checkbox' value='yes' ".( ($EPMV && !empty($EPMV['evcal_allday']) && $EPMV['evcal_allday'][0]=='yes')? 'checked="checked"':'')."/> <label>".eventon_get_custom_language($opt_2, 'evoAUL_001', 'All Day Event', $lang)."</label></p>
									//<p class='label'><input id='evoAU_hide_ee' name='evo_hide_endtime' type='checkbox' value='yes' ".( ($EPMV && !empty($EPMV['evo_hide_endtime']) && $EPMV['evo_hide_endtime'][0]=='yes')? 'checked="checked"':'')."/> <label>".eventon_get_custom_language($opt_2, 'evoAUL_002', 'No end time', $lang)."</label></p>
								echo "</div>";

								// if set to hide repeating fields from the form
								if(!empty($evoopt['evoau_hide_repeats']) && $evoopt['evoau_hide_repeats']=='yes'){}else{
									
									echo "<div class='row evoau_repeating'><p>";
									$evcal_repeat = ($EPMV && !empty($EPMV['evcal_repeat']) && $EPMV['evcal_repeat'][0]=='yes')? true: false;
									echo $helper->html_yesnobtn(array(
										'id'=>'evcal_repeat',
										'input'=>true,
										'label'=>eventon_get_custom_language($opt_2, 'evoAUL_ere1', 'This is a repeating event', $lang),
										'var'=> ($evcal_repeat?'yes':'no'),
										'lang'=>$lang
									));
									echo "</p></div>";

									// saved values for edit form
										$evcal_rep_freq = ($EPMV && !empty($EPMV['evcal_rep_freq']))? $EPMV['evcal_rep_freq'][0]:false;
										$evcal_rep_gap = ($EPMV && !empty($EPMV['evcal_rep_gap']))? $EPMV['evcal_rep_gap'][0]:false;
										$evcal_rep_num = ($EPMV && !empty($EPMV['evcal_rep_num']))? $EPMV['evcal_rep_num'][0]:false;

									echo "<div class='row' id='evoau_repeat_data' style='display:".($evcal_repeat?'':'none')."'>
										<p class='evoau_repeat_frequency'>
										<select name='evcal_rep_freq'>
											<option value='daily' ".( $evcal_rep_freq=='daily'? "selected='selected'":'').">".eventon_get_custom_language($opt_2, 'evoAUL_ere2', 'Daily', $lang)."</option>
											<option value='weekly' ".( $evcal_rep_freq=='weekly'? "selected='selected'":'').">".eventon_get_custom_language($opt_2, 'evoAUL_ere3', 'Weekly', $lang)."</option>
											<option value='monthly' ".( $evcal_rep_freq=='monthly'? "selected='selected'":'').">".eventon_get_custom_language($opt_2, 'evoAUL_ere4', 'Monthly', $lang)."</option>
										</select>
										<label>".eventon_get_custom_language($opt_2, 'evoAUL_ere5', 'Event Repeat Type', $lang)."</label>
										</p>
										<p class='evcal_rep_gap'>
											<input type='number' name='evcal_rep_gap' min='1' placeholder='1' value='".($evcal_rep_gap? $evcal_rep_gap:'1')."'/>
											<label>".eventon_get_custom_language($opt_2, 'evoAUL_ere6', 'Gap Between Repeats', $lang)."</label>
										</p>
										<p class='evcal_rep_num'>
											<input type='number' name='evcal_rep_num' min='1' placeholder='1' value='".($evcal_rep_num? $evcal_rep_num:'1')."'/>
											<label>".eventon_get_custom_language($opt_2, 'evoAUL_ere7', 'Number of Repeats', $lang)."</label>
										</p>
									</div>";
								}
							break;
							case 'text':
								$default_val = str_replace("'", '"', $default_val);
								echo "<div class='row'>
									<p class='label'><label for='".$__field_id."'>".$__field_name.$__req."</label></p>
									<p><input type='text' class='fullwidth{$__req_}' name='".$__field_id."' ".$_placeholder." value='{$default_val}'/></p>
								</div>";
							break;
							case 'html':
								$HTML = !empty($evoopt['evoau_html_content'])? $evoopt['evoau_html_content']: false;
								if($HTML){
									echo "<div class='row'>";
									//echo html_entity_decode (stripslashes($HTML));
									echo html_entity_decode($eventon->frontend->filter_evo_content($HTML));
									echo "</div>";
								}
							break;
							case 'button':
								echo "<div class='row'>
									<p class='label'><label for='".$__field_id."'>".$__field_name.' '.evo_lang('(Text)', $lang,$opt_2).' '.$__req."</label></p>
									<p><input type='text' class='fullwidth{$__req_}' name='".$__field_id."' ".$_placeholder." value='{$default_val}'/></p>
									<p class='label'><label for='".$__field_id."'>".$__field_name.' '.evo_lang('(Link)', $lang,$opt_2).' '.$__req."</label></p>
									<p><input type='text' class='fullwidth{$__req_}' name='".$__field_id."L' ".$_placeholder." value='".(!empty($EPMV[$__field_id."L"])? $EPMV[$__field_id."L"][0]:null)."'/></p>
								</div>";
							break;
							case 'textarea':
								// for event details field
								if($field[1]== 'event_description'){
									$event = get_post($event_id);
									if($event_id){
										setup_postdata($event);
										$content = $event->post_content;
										$content = apply_filters('the_content', $content);
										$default_val = str_replace(']]>', ']]&gt;', $content);
										wp_reset_postdata();
									}else{
										$default_val = '';
									}
								}
								echo "<div class='row ta'>
									<p class='label'><label for='".$__field_id."'>".$__field_name."</label></p>";

								if($field[1]== 'event_description'){
									
									// USE basic text editor
									if(!empty($eventon_au->frontend->evoau_opt['evoau_eventdetails_textarea']) && $eventon_au->frontend->evoau_opt['evoau_eventdetails_textarea']=='yes'){
										echo "<p><textarea id='".( !empty($field[4])? $field[4]:'')."' type='text' class='fullwidth' name='".$__field_id."' ".$_placeholder.">{$default_val}</textarea></p>";
									}else{
									// WYSIWYG editor
										$editor_id = (!empty($field[4])? $field[4]:'');
										$editor_var_name = 'event_description';
										$editor_args = array(
											'wpautop'=>true,
											'media_buttons'=>false,
											'textarea_name'=>$editor_var_name,
											'editor_class'=>'',
											'tinymce'=>true,
										);
										echo "<div id='{$editor_id}' class='evoau_eventdetails'>".wp_editor($default_val, $editor_id, $editor_args)."</div>";
									}

								}else{
									echo "<p><textarea id='".( !empty($field[4])? $field[4]:'')."' type='text' class='fullwidth' name='".$__field_id."' ".$_placeholder.">{$default_val}</textarea></p>";
								}

								echo "</div>";
							break;
							case 'color':

								// get the default color from eventon settings
								$defaultColor = !empty($eventon_au->frontend->options['evcal_hexcode'])? $eventon_au->frontend->options['evcal_hexcode']: '8c8c8c';

								echo "<div class='row'>
									<p class='color_circle' data-hex='".(!empty($EPMV['evcal_event_color'])? $EPMV['evcal_event_color'][0]:$defaultColor)."' style='background-color:#".(!empty($EPMV['evcal_event_color'])? $EPMV['evcal_event_color'][0]:$defaultColor)."'></p>
									<p class='evoau_color_picker'>
										<input type='hidden' class='evcal_event_color' name='evcal_event_color' value='".(!empty($EPMV['evcal_event_color'])? $EPMV['evcal_event_color'][0]:$defaultColor)."'/>
										<input type='hidden' name='evcal_event_color_n' class='evcal_event_color_n' value='".(!empty($EPMV['evcal_event_color_n'])? $EPMV['evcal_event_color_n'][0]:'0')."'/>
										<label for='".$__field_id."'>".$__field_name."</label>
									</p>									
								</div>";
							break;
							case 'tax':
								// get all terms for categories
								$terms = get_terms($field[1], array('hide_empty'=>false,));
								if(count($terms)>0){
									echo "<div class='row'>
										<p class='label'><label for='".$__field_id."'>".$__field_name."</label></p><p class='checkbox_row'>";
										
										// if edit form
										$slectedterms = array();
										if($_EDITFORM){
											$postterms = wp_get_post_terms($event_id, $field[1]);
											if(!empty($postterms)){
												foreach($postterms as $postterm)
													$slectedterms[] = $postterm->term_id;
											}
										}
										/*
										echo "<select multiple class='evoau_selectmul'>";
										foreach($terms as $term){											
											echo "<option ".( (count($slectedterms) && in_array($term->term_id, $slectedterms))? 'selected="selected"':null )." value='".$term->term_id."'>".$term->name."</option>";
										}
										echo "</select>";
										*/

										echo "<span class='evoau_cat_select_field {$field[1]}'>";
										foreach($terms as $term){
											echo "<span class='{$field[1]}_{$term->term_id}'><input type='checkbox' name='".$__field_id."[]' value='".$term->term_id."' ".( (count($slectedterms) && in_array($term->term_id, $slectedterms))? 'checked="checked"':null )."/> ".$term->name."</span>";
										}
										echo "</span>";

									echo "</p>";

									if(!empty($evoopt['evoau_add_cats']) && $evoopt['evoau_add_cats']=='yes')
										echo "<p class='label'><label>or create New (type other categories seperated by commas)</label></p><p><input class='fullwidth' type='text' name='".$__field_id."_new'/></p>";
									echo "</div>";
								}
							break;
							case 'image':
								// if image already exists
								if($_EDITFORM){
									$IMFSRC = false;
									$img_id =get_post_thumbnail_id($event_id);
									if($img_id!=''){
										$img_src = wp_get_attachment_image_src($img_id,'thumbnail');
										$IMFSRC = $img_src[0];
									}
								}
								echo "<div class='row'>
									<p class='label'><label for='".$__field_id."'>".$__field_name."</label></p>";
								if($_EDITFORM && $IMFSRC){
									echo"<p class='evoau_img_preview'><img src='{$IMFSRC}'/><span>".evo_lang('Remove Image',$lang,$opt_2)."</span>
										<input type='hidden' name='event_image_exists' value='yes'/>
									</p>";
								}
								echo "<p class='evoau_file_field' style='display:".($_EDITFORM?'none':'block')."'><span class='evoau_img_btn'>".eventon_get_custom_language($opt_2, 'evoAUL_img002', 'Select an Image', $lang)."</span>
										<input style='opacity:0' type='file' id='".$__field_id."' name='".$__field_id."' data-text='".eventon_get_custom_language($opt_2, 'evoAUL_img001', 'Image Chosen', $lang)."'/>";
										wp_nonce_field( 'my_image_upload', 'my_image_upload_nonce' );
									echo "</p>
								</div>";
							break;
							case 'uiselect':								
								// options
								$uis = array(
									'1'=>eventon_get_custom_language($opt_2, 'evoAUL_ux1', 'Slide Down EventCard', $lang),
									'2'=>eventon_get_custom_language($opt_2, 'evoAUL_ux2', 'External Link', $lang),
									'3'=>eventon_get_custom_language($opt_2, 'evoAUL_ux3', 'Lightbox Popup Window', $lang)
								);

								// if single event addon is enabled
								if(defined('EVO_SIN_EV') && EVO_SIN_EV)
									$uis['4'] = eventon_get_custom_language($opt_2, 'evoAUL_ux4a', 'Open as Single Event Page', $lang);

								echo "<div class='row evoau_ui'>
										<p class='label'><label for='".$__field_id."'>".$__field_name."</label></p><p class='dropdown_row'><select name='".$__field_id."'>";

										foreach($uis as $ui=>$uiv){
											echo "<option type='checkbox' value='".$ui."'/> ".$uiv."</option>";
										}
									echo "</select></p>
									<div class='evoau_exter' style='display:none'>
										<p class='label'><label for='evoau_ui'>".eventon_get_custom_language($opt_2, 'evoAUL_ux4', 'Type the External Url', $lang)."</label></p>
										<p><input name='evcal_exlink' class='fullwidth' type='text'/><br/>
										<i><input name='_evcal_exlink_target' value='yes' type='checkbox'/> ".eventon_get_custom_language($opt_2, 'evoAUL_lm1', 'Open in new window', $lang)."</i></p>
									</div></div>";
							break;
							case 'learnmore':
								$default_val = str_replace("'", '"', $default_val);
								echo "<div class='row'>
									<p class='label'><label for='".$__field_id."'>".$__field_name.$__req."</label></p>
									<p><input type='text' class='fullwidth{$__req_}' name='".$__field_id."' ".$_placeholder." value='{$default_val}'/></p>
									<p><input type='checkbox' name='".$__field_id."_target' value='yes'/> <label>".eventon_get_custom_language($opt_2, 'evoAUL_lm1', 'Open in new window', $lang)."</label></p>
								</div>";
							break;
							case 'locationselect':
								
								$locations = get_terms('event_location', array('hide_empty'=>false));
								if ( ! empty( $locations ) && ! is_wp_error( $locations ) ){

									// if location tax saved before
								    	$location_terms = !empty($_EID)? wp_get_post_terms($_EID, 'event_location'):'';
								    	$termMeta = $evo_location_tax_id = '';
								    	if ( $location_terms && ! is_wp_error( $location_terms ) ){
											$evo_location_tax_id =  $location_terms[0]->term_id;
											$termMeta = get_option( "taxonomy_$evo_location_tax_id");
										}

									echo "<div class='row locationSelect'>
										<p class='label'><label for='".$__field_id."'>".$__field_name.$__req."</label></p>";
									echo '<p><select class="evoau_location_select">';
										echo "<option value='-'>".eventon_get_custom_language($opt_2, 'evoAUL_ssl', 'Select Saved Locations', $lang)."</option>";
									// each select field optinos
									foreach ( $locations as $loc ) {
								    	$taxmeta = get_option("taxonomy_".$loc->term_id);

								    	$__selected = ($evo_location_tax_id== $loc->term_id)? "selected='selected'":null;
								       	
								    	// select option attributes
								    	$data = array(
								    		'add'=>'location_address',
								    		'lon'=>'location_lon',
								    		'lat'=>'location_lat',
								    		'link'=>'evcal_location_link',
								    		'img'=>'evo_loc_img',
								    	);
								    	$datastr = '';
								    	foreach($data as $f=>$v){	$datastr.= ' data-'.$f.'="'.( !empty($taxmeta[$v])?$taxmeta[$v]:'').'"';	}

								       	echo "<option value='{$loc->term_id}' {$datastr} {$__selected}>" . $loc->name . '</option>';								        
								    }
								    
								    $fields = $eventon_au->frontend->au_form_fields();
								    echo "</select>
										<input type='hidden' name='evo_location_tax_id' value=''/>
										<input type='hidden' name='evo_loc_img_id' value=''/>";
									if(!empty($evoopt['evoau_allow_new']) && $evoopt['evoau_allow_new']=='yes') echo "<span class='enterNew' data-txt='Select from List'>". eventon_get_custom_language($opt_2,'evoAUL_cn','Create New', $lang)."</span>";
								    echo "</p>";

								    $data = array(
								    	'event_location_name',
								    	'event_location',
								    	'event_location_cord',
								    	'event_location_link',
								    );
								    echo "<div class='enterownrow' style='display:none'>";
								    foreach($data as $v){
								    	$dataField = $fields[$v];
								    	$savedValue = (!empty($termMeta) && !empty($termMeta[$dataField[1]]) )?$termMeta[$dataField[1]]: ''; 

								    	// lat and lon values
								    	if($v=='event_location_cord'){
								    		$savedValue = (!empty($termMeta) && !empty($termMeta['location_lat']) && !empty($termMeta['location_lon']) )? $termMeta['location_lat'].','.$termMeta['location_lon']:'';
								    	}

								    	// location name
								    	if($v == 'event_location_name' && !empty($location_terms)){
								    		$savedValue = $location_terms[0]->name;
								    	}
								    	echo "<p class='subrows'><label>".eventon_get_custom_language($opt_2, $dataField[4], $dataField[0], $lang)."</label><input class='fullwidth' type='text' name='{$dataField[1]}' value='{$savedValue}'/></p>";
								    }
								    echo "</div>";
								    echo "</div>";
								}

							break;
							case 'organizerselect':
								$organizers = get_terms('event_organizer' , array('hide_empty'=>false));
								if ( ! empty( $organizers ) && ! is_wp_error( $organizers ) ){

									// if organizer tax saved before
								    	$organizer_terms = !empty($_EID)? wp_get_post_terms($_EID, 'event_organizer'):'';
								    	$termMeta = $evo_organizer_tax_id = '';
								    	if ( $organizer_terms && ! is_wp_error( $organizer_terms ) ){
											$evo_organizer_tax_id =  $organizer_terms[0]->term_id;
											$termMeta = get_option( "taxonomy_$evo_organizer_tax_id");
										}

									echo "<div class='row organizerSelect'>
										<p class='label'><label for='".$__field_id."'>".$__field_name.$__req."</label></p>";
									echo '<p><select class="evoau_organizer_select">';
										echo "<option value='-'>".eventon_get_custom_language($opt_2, 'evoAUL_sso', 'Select Saved Organizers', $lang)."</option>";
								    foreach ( $organizers as $org ) {
								    	$taxmeta = get_option("taxonomy_".$org->term_id);

								    	$__selected = ($evo_organizer_tax_id== $org->term_id)? "selected='selected'":null;

								    	// select option attributes
								    	$data = array(
								    		'contact'=>$taxmeta['evcal_org_contact'],
								    		'img'=>$taxmeta['evo_org_img'],
								    		'exlink'=>$taxmeta['evcal_org_exlink'],
								    		'address'=>$taxmeta['evcal_org_address'],
								    	);
								    	$datastr = '';
								    	foreach($data as $f=>$v){
								    		$datastr.= ' data-'.$f.'="'.$v.'"';
								    	}

								       	echo "<option value='{$org->term_id}' {$datastr} {$__selected}>" . $org->name . '</option>';								        
								    }
								    
								    $fields = $eventon_au->frontend->au_form_fields();
								    echo "</select>
										<input type='hidden' name='evo_organizer_tax_id' value=''/>
										<input type='hidden' name='evo_org_img_id' value=''/>";
									if(!empty($evoopt['evoau_allow_new']) && $evoopt['evoau_allow_new']=='yes') echo "<span class='enterNew' data-txt='Select from List'>". eventon_get_custom_language($opt_2,'evoAUL_cn','Create New', $lang)."</span>";
								    echo "</p>";

								    $data = array(
								    	'event_organizer',
								    	'event_org_contact',
								    	'event_org_address',
								    	'event_org_link',
								    );
								    echo "<div class='enterownrow' style='display:none'>";
								    foreach($data as $v){
								    	$dataField = $fields[$v];
								    	$savedValue = (!empty($termMeta) && !empty($termMeta[$dataField[1]]) )?$termMeta[$dataField[1]]: ''; 

								    	// Organizer name
								    	if($v == 'event_organizer' && !empty($organizer_terms)){
								    		$savedValue = $organizer_terms[0]->name;
								    	}

								    	echo "<p class='subrows'><label>".eventon_get_custom_language($opt_2, $dataField[4], $dataField[0], $lang)."</label><input class='fullwidth' type='text' name='{$dataField[1]}' value='{$savedValue}'/></p>";
								    }
								    echo "</div>";
								    echo "</div>";
								}
							break;
							case 'captcha':
								$cals = array(	0=>'3+8', '5-2', '4+2', '6-3', '7+1'	);
								$rr = rand(0, 4);
								$calc = $cals[$rr];

								echo "<div class='row au_captcha'>
									<p><span style='margin-bottom:6px; margin-top:3px' class='verification'>{$calc} = ?</span>
									<input type='text' data-cal='{$rr}' class='fullwidth' id='".$__field_id."' name='".$__field_id."' />
									</p>
									<p class='label'><label for='".$__field_id."'>".$__field_name."</label></p>									
								</div>";
							break;
						}

					}
				endforeach;
			
			
			// form message
			echo "<p class='formeMSG' style='display:none'></p>";

			// Submit button
			$btn_text = ($_EDITFORM)? evo_lang('Update Event',$lang, $opt_2): eventon_get_custom_language($opt_2, 'evoAUL_se', 'Submit Event', $lang);
			echo "<div class='submit_row row'><p><a id='evoau_submit' class='evcal_btn'>".$btn_text."</a></p></div>";
	
		?>			
		</div><!-- .evoau_table-->
		</div><!-- inner -->

		<div class='evoau_json' style='display:none'><?php 
		$nofs = array(
			'nof0'=>((!empty($this->evoau_opt['evoaun_msg_f']))?
							($this->evoau_opt['evoaun_msg_f'])
							:__('Required fields missing','eventon')),
			'nof1'=>eventon_get_custom_language($opt_2, 'evoAUL_nof1', 'Required Feidls Missing', $lang),
			'nof2'=>eventon_get_custom_language($opt_2, 'evoAUL_nof2', 'Invalid validation code please try again', $lang),
			'nof3'=>eventon_get_custom_language($opt_2, 'evoAUL_nof3', 'Thank you for submitting your event!', $lang),
			'nof4'=>eventon_get_custom_language($opt_2, 'evoAUL_nof4', 'Could not create event post, try again later!', $lang),
			'nof5'=>eventon_get_custom_language($opt_2, 'evoAUL_nof5', 'Bad nonce form verification, try again!', $lang),
			'nof6'=>eventon_get_custom_language($opt_2, 'evoAUL_nof6', 'You can only submit one event!', $lang),
		);
		echo json_encode($nofs);
		?></div>	

		<div class='evoau_success_msg' style='display:<?php echo $LIMITSUB?'block':'none';?>'><p><b></b><?php echo $LIMITSUB? eventon_get_custom_language($opt_2, 'evoAUL_nof6', 'You can only submit one event!', $lang):'';?></p></div>
		<?php if($_msub):?>
			<p class='msub_row' style='display:none;text-align:center'><a id='evoau_msub' class='msub evcal_btn'><?php echo evo_lang('Submit another event',$lang, $opt_2);?></a></p>
		<?php endif;?>
	<?php endif; // close if $LOGINCHECK?>
	</form>
	
</div>
</div><!--.eventon_au_form_section-->
<?php


?>