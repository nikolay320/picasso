<?php
/**
 * 
 * eventon rsvp front end class
 *
 * @author 		AJDE
 * @category 	Admin
 * @package 	eventon-rsvp/classes
 * @version     2.3.3
 */
class evors_front{
	public $rsvp_array = array('y'=>'yes','m'=>'maybe','n'=>'no');
	public $rsvp_array_ = array('y'=>'Yes','m'=>'Maybe','n'=>'No');
	public $evors_args;
	public $optRS;

	private $currentlang;

	function __construct(){
		global $eventon_rs;

		include_once('class-functions.php');
		$this->functions = new evorsvp_functions();

		add_filter('eventon_eventCard_evorsvp', array($this, 'frontend_box'), 10, 2);
		add_filter('eventon_eventcard_array', array($this, 'eventcard_array'), 10, 4);
		add_filter('evo_eventcard_adds', array($this, 'eventcard_adds'), 10, 1);

		// event top inclusion
		add_filter('eventon_eventtop_one', array($this, 'eventop'), 10, 3);
		add_filter('evo_eventtop_adds', array($this, 'eventtop_adds'), 10, 1);
		add_filter('eventon_eventtop_evors', array($this, 'eventtop_content'), 10, 2);			
		//add_action( 'wp_enqueue_scripts', array( $this, 'load_styles' ), 10 );	
		// scripts and styles 
		add_action( 'init', array( $this, 'register_styles_scripts' ) ,15);	

		$this->optRS = $eventon_rs->evors_opt;
		$this->opt2 = $eventon_rs->opt2;

		// add rsvp form HTML to footer
		add_action('wp_footer', array($this, 'footer_content'));
	}

	//	STYLES: for the tab page 
		public function register_styles_scripts(){
			global $eventon_rs;
			
			wp_register_style( 'evo_RS_styles',$eventon_rs->plugin_url.'/assets/RS_styles.css');
			wp_register_script('evo_RS_script',$eventon_rs->plugin_url.'/assets/RS_script.js', array('jquery'), $eventon_rs->version, true );

			wp_localize_script( 
				'evo_RS_script', 
				'evors_ajax_script', 
				array( 
					'ajaxurl' => admin_url( 'admin-ajax.php' ) , 
					'postnonce' => wp_create_nonce( 'evors_nonce' )
				)
			);
			
			$this->print_scripts();
			add_action( 'wp_enqueue_scripts', array($this,'print_styles' ));

		}
		public function print_scripts(){
			wp_enqueue_script('evo_RS_ease');	
			//wp_enqueue_script('evo_RS_mobile');	
			wp_enqueue_script('evo_RS_script');	
		}
		function print_styles(){
			wp_enqueue_style( 'evo_RS_styles');	
		}

	// EVENTTOP inclusion
		public function eventop($array, $pmv, $vals){
			$array['evors'] = array(	'vals'=>$vals,	);
			return $array;
		}		
		function eventtop_adds($array){
			$array[] = 'evors';
			return $array;
		}
		public function eventtop_content($object, $helpers){
			$output = '';
			
			$emeta = get_post_custom($object->vals['eventid']);

			// if rsvp and enabled for the event
			if( !empty($emeta['evors_rsvp']) && $emeta['evors_rsvp'][0]=='yes'							
			){
				global $eventon;
				$lang = (!empty($eventon->evo_generator->shortcode_args['lang'])? $eventon->evo_generator->shortcode_args['lang']:'L1');

				// logged-in user RSVPing with one click
					$existing_rsvp_status = false;
					if(!empty($this->optRS['evors_eventop_rsvp']) && $this->optRS['evors_eventop_rsvp']=='yes'){				
						if(is_user_logged_in()){
							global $current_user;
							get_currentuserinfo();
							$existing_rsvp_status = $this->functions->get_user_rsvp_status($current_user->ID, $object->vals['eventid'], $object->vals['ri'], $emeta);
							// if loggedin user have not rsvp-ed yet
							if(!$existing_rsvp_status){
								$TEXT = eventon_get_custom_language($this->opt2, 'evoRSL_001','RSVP to event', $lang);
								$output .=  "<span class='evors_eventtop_section evors_eventtop_rsvp' data-eid='{$object->vals['eventid']}' data-ri='{$object->vals['ri']}'data-uid='{$current_user->ID}' data-lang='{$lang}'>".$TEXT. $this->get_rsvp_choices($this->opt2, $this->optRS)."</span>";
							}else{
							// user has rsvp-ed already
								$TEXT = evo_lang('You have already RSVP-ed', $lang);
								$output .="<span class='evors_eventtop_section evors_eventtop_rsvp'>{$TEXT}: <em class='evors_rsvped_status_user'>".$this->get_rsvp_status($existing_rsvp_status)."</em></span>";
							}
						}							
					}

				// show attending count 
					if(!empty($this->optRS['evors_eventop_attend_count']) && $this->optRS['evors_eventop_attend_count']=='yes'){
						// correct language text for based on count coming to event
							$lang_str = array(
								'0'=>'Be the first to RSVP',
								'1'=>'One guest is attending',
								'2'=>'Guests are attending',
							);
						
						$yes_count = $this->functions->get_rsvp_count($emeta, 'y', $object->vals['ri']);
						$__count_lang = ($yes_count==1)?
							evo_lang($lang_str['1'], $lang):
							($yes_count>1? evo_lang($lang_str['2'], $lang): 
								evo_lang($lang_str['0'], $lang));
						$output .= "<span class='evors_eventtop_section evors_eventtop_data'>".($yes_count>1? '<em>'.$yes_count.'</em> ':'').$__count_lang."</span>";
					}

				// show remaining count 
					if($this->optRS['evors_show_whos_coming']=="no"){
						// /print_r($object);
						
							$remaining_rsvp = $this->functions->remaining_rsvp($emeta, $object->vals['ri'], $object->vals['eventid']);
							if($remaining_rsvp){							
								$output .= "<span class='evors_eventtop_section evors_eventtop_data remaining_count'><em>".($remaining_rsvp>0?$remaining_rsvp.' ':'na').'</em>'.( eventon_get_custom_language('','evoRSL_002b','Spots remaining', $lang))."</span>";
							}
						
					}

				//construct HTML
				if(!empty($output)){
					$output = "<span class='evcal_desc3_rsvp'>".$output."</span>";
				}
			}	
			return $output;
			
		}

	// RSVP EVENTCARD form HTML
		// add RSVP box to front end
			function frontend_box($object, $helpers){
				global $eventon_rs;
				$event_pmv = get_post_custom($object->event_id);

				// loggedin user
					$currentUserID = 	$this->functions->current_user_id();	
					$currentUserRSVP = $this->functions->get_userloggedin_user_rsvp_status($object->event_id, $object->__repeatInterval, $event_pmv);	

				// RSVP enabled for this event
					if(empty($event_pmv['evors_rsvp']) || (!empty($event_pmv['evors_rsvp']) && $event_pmv['evors_rsvp'][0]=='no') ) return;

				$lang = (!empty($eventon->evo_generator->shortcode_args['lang'])? $eventon->evo_generator->shortcode_args['lang']:'L1');
				$text_event_title = get_the_title($object->event_id);
				
				$optRS = $this->optRS;
				$is_user_logged_in = is_user_logged_in();

				// if only loggedin users can see rsvp form
					if( evo_settings_val('evors_onlylogu', $optRS) && !$is_user_logged_in){
						return $this->rsvp_for_none_loggedin($helpers, $object);
						return;	// not proceeding forward from here
					}

				// if close rsvp is set and check time
					$close_time = evo_meta($event_pmv, 'evors_close_time');
					if( !empty( $close_time) ){						
						date_default_timezone_set('UTC');
						$time = time()+(get_option('gmt_offset', 0) * 3600);
						$close_t = (int)($close_time)*60;

						// adjust event end time for repeat intervals
						$row_endTime = $this->get_correct_event_end_time(
							$event_pmv, $object->__repeatInterval);
						$change_t = (int)$row_endTime - $close_t;

						if($change_t <= $time )	
							return;
					}				
				// show rsvp count
					if( evo_meta_yesno($event_pmv, 'evors_show_rsvp', 'yes', true, false) ){
						$countARR = array(
							'y' => (' ('.$this->functions->get_rsvp_count($event_pmv,'y',$object->__repeatInterval).')'),
							'n' => (' ('.$this->functions->get_rsvp_count($event_pmv,'n',$object->__repeatInterval).')'),
							'm' => (' ('.$this->functions->get_rsvp_count($event_pmv,'m',$object->__repeatInterval).')'),
						);
					}else{	$countARR = array();}
				// get options array
					$opt = $helpers['evoOPT2'];
					$fields_options = 	(!empty($optRS['evors_ffields']))?$optRS['evors_ffields']:false;
				// change rsvp button
					$_txt_changersvp = eventon_get_custom_language($opt, 'evoRSL_005a','Change my RSVP');
					$changeRSVP = (!empty($optRS['evors_hide_change']) && $optRS['evors_hide_change']=='yes')?'': "<span class='change' data-val='ch'>".$_txt_changersvp."</span>";

				ob_start();

				$remaining_rsvp = $this->functions->remaining_rsvp($event_pmv, $object->__repeatInterval, $object->event_id);
				
				echo  "<div class='evorow evcal_evdata_row bordb evcal_evrow_sm evo_metarow_rsvp".$helpers['end_row_class']."' data-rsvp='' data-event_id='".$object->event_id."'>
							<span class='evcal_evdata_icons'><i class='fa ".get_eventON_icon('evcal__evors_001', 'fa-envelope',$helpers['evOPT'] )."'></i></span>
							<div class='evcal_evdata_cell'>							
								<h3 class='evo_h3'>Votre reponse SVP</h3>";
						
						// RSVPing allowed and spaces left
						$eventtop_rsvp = (!empty($this->optRS['evors_eventop_rsvp']) && $this->optRS['evors_eventop_rsvp']=='yes')? true:false;
					 	
					 	// there are RSVP spots remaining OR user loggedin
						if(($remaining_rsvp==true || $remaining_rsvp >0) || $currentUserRSVP){
							echo "<div class='evoRS_status_option_selection' data-etitle='".$text_event_title."' data-eid='".$object->event_id."' data-ri='{$object->__repeatInterval}' data-cap='".(is_int($remaining_rsvp)? $remaining_rsvp:'na')."' data-precap='".$this->functions->is_per_rsvp_max_set($event_pmv)."'>";

							// if already RSVPED
							if($currentUserRSVP){
								echo "<p class='nobrbr loggedinuser' data-uid='{$currentUserID}' data-eid='{$object->event_id}' data-ri='{$object->__repeatInterval}'>";
								echo evo_lang('You have already RSVP-ed').": <em class='evors_rsvped_status_user'>".$this->get_rsvp_status($currentUserRSVP)."</em> ";
								echo "</p>";

							}else{// have no RSVPed yet								
								echo "<p>". $this->get_rsvp_choices($opt, $optRS, $countARR)."</p>";
							}							
							echo "</div>";
						}else{
							if(!$remaining_rsvp && empty($optRS['evors_hide_change'])  || (!empty($optRS['evors_hide_change']) && $optRS['evors_hide_change']!='yes')  ){
								echo "<div class='evoRS_status_option_selection' data-etitle='". get_the_title($object->event_id)."' data-eid='".$object->event_id."' data-ri='{$object->__repeatInterval}' data-cap='".(is_int($remaining_rsvp)? $remaining_rsvp:'na')."' data-precap='".$this->functions->is_per_rsvp_max_set($event_pmv)."'>";
								echo "<p class='nobrbr loggedinuser' data-uid='{$currentUserID}' data-eid='{$object->event_id}' data-ri='{$object->__repeatInterval}'>";
								echo "</p>";
								echo "</div>";
							}
						}
								
						// spots remaining
							if($this->functions->show_spots_remaining($event_pmv)){
								// no more spaces
								echo "<div class='evors_section evors_remaining_spots'>";
								if(!$remaining_rsvp){
									echo "<p class='remaining_count no_spots_left'>".eventon_get_custom_language($opt, 'evoRSL_002c','No more spots left!')."</p>";
								}else{
									echo "<p class='remaining_count'><em>".$remaining_rsvp  ."</em> ".eventon_get_custom_language($opt, 'evoRSL_002b','Spots remaining')."</p>";
								}
								echo "</div>";
							}
						// subtext for rsvp section
							$subtext = '';
							if(!$remaining_rsvp){
								$subtext = eventon_get_custom_language($opt, 'evoRSL_002d',"RSVPing is closed at this time.");
							}else{
								if(!$currentUserRSVP)
									$subtext = eventon_get_custom_language($opt, 'evoRSL_002','Make sure to RSVP to this amazing event!');
							}
							if(!empty($subtext))
								echo "<div class='evors_section evors_subtext'><p class='evo_data_val'>".$subtext."</p></div>";

						// minimum capacity event happening
							if(!empty($event_pmv['evors_min_cap']) && $event_pmv['evors_min_cap'][0]=='yes' && !empty($event_pmv['evors_min_count']) ){
								$output = '';
								$minCap = (int)$event_pmv['evors_min_count'][0];
								$coming = $this->functions->get_rsvp_count($event_pmv,'y',$object->__repeatInterval);
								if($coming>=$minCap){
									$output = evo_lang('Event is happening for certain');
								}else{
									$need = $minCap - $coming;
									$output = str_replace('-count-', '<em>'.$need.'</em>', evo_lang('Event is pending -count- more guests for it to happen') );
								}
								if(!empty($output))
									echo "<div class='evors_section evors_mincap ". ($coming>=$minCap? 'happening':'nothappening')."'><p class='evo_data_val'>".$output."</p></div>";
							}
						// whos coming section - guests list
							if($this->functions->show_whoscoming($event_pmv)){
								$attendee_icons = $this->GET_attendees_icons($object->event_id, $object->__repeatInterval);
								if($attendee_icons){
									echo "<div class='evors_section evors_guests_list'>";
									echo "<p class='evors_whos_coming_title'>".eventon_get_custom_language($opt, 'evoRSL_002a','Guests List').' <em>('.eventon_get_custom_language($opt, 'evoRSL_002a1','Attending').' '.$this->functions->get_rsvp_count($event_pmv,'y',$object->__repeatInterval).")</em></p><p class='evors_whos_coming'><em class='tooltip'></em>". $attendee_icons."</p>";
									echo "</div>";
								}
							}						
						// change RSVP status section							
							if( empty($this->optRS['evors_onlylog_chg']) || 
								(!empty($this->optRS['evors_onlylog_chg']) && $this->optRS['evors_onlylog_chg']=='no') ||
								(!empty($this->optRS['evors_onlylog_chg']) && $this->optRS['evors_onlylog_chg']=='yes' && $is_user_logged_in && 
									(	empty($this->optRS['evors_change_hidden']) || 
										(!empty($this->optRS['evors_change_hidden']) && $this->optRS['evors_change_hidden']=='no') ||
										(!empty($this->optRS['evors_change_hidden']) && $this->optRS['evors_change_hidden']=='yes' && $currentUserRSVP)
									)
								)								
							){
								echo "<div class='evors_section evors_change_rsvp'>
									<p class='evors_whos_coming_title' data-etitle='".$text_event_title."' data-uid='{$currentUserID}' data-eid='{$object->event_id}' data-ri='{$object->__repeatInterval}'>".eventon_get_custom_language($opt, 'evoRSL_002a2','Can not make it to this event?')."<span class='change' data-val='".($currentUserRSVP?'chu':'ch')."'>".$_txt_changersvp."</span></p></div>";
							}
						// additional information to rsvped logged in user
							if(!empty($event_pmv['evors_additional_data']) && $currentUserRSVP){
								echo "<div class='evors_additional_data'>";
								echo "<h3 class='evo_h3 additional_info'>".evo_lang('Additional Information', $lang, $opt)."</h3>";
								echo "<p class='evo_data_val'>".$event_pmv['evors_additional_data'][0]."</p>";
								echo "</div>";
							}

						//echo "</div><div class='evorsvp_eventcard_column'>";
						//echo "</div>";
								
						echo "</div>".$helpers['end'];
						echo "</div>";
							

				return ob_get_clean();
			}
			// for not loggedin users
				function rsvp_for_none_loggedin($helpers, $object){
					global $eventon;
					$lang = (!empty($eventon->evo_generator->shortcode_args['lang'])? $eventon->evo_generator->shortcode_args['lang']:'L1');
					ob_start();
					echo  "<div class='evorow evcal_evdata_row bordb evcal_evrow_sm evo_metarow_rsvp".$helpers['end_row_class']."' data-rsvp='' data-event_id='".$object->event_id."'>
								<span class='evcal_evdata_icons'><i class='fa ".get_eventON_icon('evcal__evors_001', 'fa-envelope',$helpers['evOPT'] )."'></i></span>
								<div class='evcal_evdata_cell'>							
									<h3 class='evo_h3'>".eventon_get_custom_language($helpers['evoOPT2'], 'evoRSL_001','RSVP to event')."</h3>";
							$txt_1 = evo_lang('You must login to RSVP for this event',$lang, $helpers['evoOPT2']);
							$txt_2 = evo_lang('Login Now',$lang, $helpers['evoOPT2']);
							echo "<p>{$txt_1}  <a style='margin-left:10px' href='".wp_login_url(get_permalink())."' class='evcal_btn'>{$txt_2}</a></p>";
					echo "</div></div>";
					return ob_get_clean();
				}
		// footer of rsvp form
			function footer_content(){
				$optRS = $this->optRS;
				$active_fields =(!empty($optRS['evors_ffields']))?$optRS['evors_ffields']:false;
				include_once('html_form.php');
			}
		// save a cookie for RSVP
			function set_user_cookie($args){
				//$ip =$this->get_client_ip();
				$cookie_name = 'evors_'.$args['email'].'_'.$args['e_id'].'_'.$args['repeat_interval'];
				$cookie_value = 'rsvped';
				setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/");
			}
			function check_user_cookie($userid, $eventid){
				$cookie_name = 'evors_'.$eventid.'_'.$userid;
				if(!empty($_COOKIE[$cookie_name]) && $_COOKIE[$cookie_name]=='rsvped'){
					return true;
				}else{
					return false;
				}
			}
		// get form messages html
			function get_form_message($code='', $lang=''){
				$opt = $this->opt2;
				$array =  array(
					'err'=>eventon_get_custom_language($opt, 'evoRSL_013','Required fields missing',$lang),
					'err2'=>eventon_get_custom_language($opt, 'evoRSL_014','Invalid email address',$lang),
					'err3'=>eventon_get_custom_language($opt, 'evoRSL_015','Please select RSVP option',$lang),
					'err4'=>eventon_get_custom_language($opt, 'evoRSL_016','Could not update RSVP, please contact us.',$lang),
					'err5'=>eventon_get_custom_language($opt, 'evoRSL_017','Could not find RSVP, please try again.',$lang),
					'err6'=>eventon_get_custom_language($opt, 'evoRSL_017x','Invalid Validation code.',$lang),
					'err7'=>eventon_get_custom_language($opt, 'evoRSL_017y','Could not create a RSVP please try later.',$lang),
					'err8'=>eventon_get_custom_language($opt, 'evoRSL_017z1','You can only RSVP once for this event.',$lang),
					'err9'=>eventon_get_custom_language($opt, 'evoRSL_017z2','Your party size exceed available space.',$lang),
					'err10'=>eventon_get_custom_language($opt, 'evoRSL_017z3','Your party size exceed allowed space per RSVP.',$lang),
					'succ'=>eventon_get_custom_language($opt, 'evoRSL_018','Thank you for submitting your rsvp',$lang),
					'succ_n'=>eventon_get_custom_language($opt, 'evoRSL_019','Sorry to hear you are not going to make it to our event.',$lang),
					'succ_m'=>eventon_get_custom_language($opt, 'evoRSL_020','Thank you for updating your rsvp',$lang),
					'succ_c'=>eventon_get_custom_language($opt, 'evoRSL_021','Great! we found your RSVP!',$lang),
				);				
				return (!empty($code))? $array[$code]: $array;
			}
			function get_form_msg($opt){
				$str='';
				$ar = array('codes'=> $this->get_form_message());
				return "<div class='evors_msg_' style='display:none'>". json_encode($ar)."</div>";
			}
		// GET attendees icons
			function GET_attendees_icons($eventID, $ri){
				$list = $this->functions->GET_rsvp_list($eventID, $ri);
				$output = false;

				$guestListInitials = (!empty($this->optRS['evors_guestlist']) && $this->optRS['evors_guestlist']!='fn')? true: false;

				if(!empty($list['y'])){
					foreach($list['y'] as $feild=>$value){
						//$gravatar_link = 'http://www.gravatar.com/avatar/' . md5($value['email']) . '?s=32';
						
						$initials = ($guestListInitials)?
							substr($value['fname'], 0, 1).substr($value['lname'], 0, 1):
							$value['fname'].' '.$value['lname'];

						$output .= "<span class='".($guestListInitials? 'initials':'fullname')."' data-name='{$value['fname']} {$value['lname']} ". ($value['count']>1? '(+'.$value['count'].')':'' )."' >{$initials}</span>";
					}
				}
				return $output;
			}
		// GET rsvp status selection HTML
			function get_rsvp_choices($opt2, $optRS, $countARR=''){
				$selection = (!empty($optRS['evors_selection']))? $optRS['evors_selection']: true;
				$selOpt = array(
					'y'=>array('Yes', 'evoRSL_003'),
					'n'=>array('No', 'evoRSL_005'),
					'm'=>array('Maybe', 'evoRSL_004'),
				);

				$content ='';
				$lang = !empty($this->currentlang)? $this->currentlang: 'L1';
				foreach($selOpt as $field=>$value){

					if( is_array($selection) && in_array($field, $selection) || $field=='y'){
						$selCount = (!is_array($selection))? 'one': '';
						$count = (!empty($countARR))? $countARR[$field]: null;
						$content .= "<span data-val='{$field}' id='{$field}' class='{$selCount}'>".eventon_get_custom_language($opt2, $value[1],$value[0], $lang).$count."</span>";
					}
				}
				return $content;
			}
		// add eventon rsvp event card field to filter
			function eventcard_array($array, $pmv, $eventid, $__repeatInterval){
				$array['evorsvp']= array(
					'event_id' => $eventid,
					'value'=>'tt',
					'__repeatInterval'=>(!empty($__repeatInterval)? $__repeatInterval:0)
				);
				return $array;
			}
			function eventcard_adds($array){
				$array[] = 'evorsvp';
				return $array;
			}

	// SAVE new RSVP
		function _form_save_rsvp($args){
			global $eventon_rs;
			$status = 0;
			
			// add new rsvp
			if($created_rsvp_id = $this->create_post() ){

				//$pmv = get_post_meta($args['e_id']);				
				$_count = (empty($args['count']))?1: $args['count'];
				$_count = (int)$_count;					

				// save rsvp data								
				$this->create_custom_fields($created_rsvp_id, 'first_name', $args['first_name']);
				if(!empty($args['last_name']))
					$this->create_custom_fields($created_rsvp_id, 'last_name', $args['last_name']);

				if(!empty($args['email']))
					$this->create_custom_fields($created_rsvp_id, 'email', $args['email']);

				if(!empty($args['phone']))		
					$this->create_custom_fields($created_rsvp_id, 'phone', $args['phone']);		

				$this->create_custom_fields($created_rsvp_id, 'rsvp', $args['rsvp']); // y n m	
				$this->create_custom_fields($created_rsvp_id, 'updates', $args['updates']);	
				$this->create_custom_fields($created_rsvp_id, 'count', $_count);	
				$this->create_custom_fields($created_rsvp_id, 'e_id', $args['e_id']);

				$__repeat_interval = (!empty($args['repeat_interval']))? $args['repeat_interval']: '0';
				$this->create_custom_fields($created_rsvp_id, 'repeat_interval', $__repeat_interval);

				// save additional form fields
					$optRS = $this->optRS;
					for($x=1; $x<4; $x++){
						if(evo_settings_val('evors_addf'.$x, $optRS) && !empty($optRS['evors_addf'.$x.'_1'])  ){
							$value = (!empty($args['evors_addf'.$x.'_1']))? $args['evors_addf'.$x.'_1']: '-';
							$this->create_custom_fields($created_rsvp_id, 'evors_addf'.$x.'_1', $value);
						}
					}

				// save loggedin user ID if prefill fields for loggedin enabled
					$prefill_enabled = (!empty($optRS['evors_prefil']) && $optRS['evors_prefil']=='yes')? true:false;
					if( ($this->functions->get_current_userid() && $prefill_enabled) || !empty($args['uid'])){
						// user ID if provided or find loggedin user id
						$CURRENT_user_id = !empty($args['uid'])? $args['uid']: $this->functions->get_current_userid();
						$this->create_custom_fields($created_rsvp_id, 'userid',$CURRENT_user_id);

						// add user meta
						//$this->functions->add_user_meta($CURRENT_user_id, $args['e_id'], $__repeat_interval, $args['rsvp']);
						$this->functions->save_user_rsvp_status($CURRENT_user_id, $args['e_id'], $__repeat_interval, $args['rsvp']);
					}

				// submission status
					$this->create_custom_fields($created_rsvp_id, 'submission_status', 'confirmed');

				$args['rsvp_id'] = $created_rsvp_id;

				// SYNC event's rsvp counts
				$this->functions->sync_rsvp_count($args['e_id']);

				
				// send out email confirmation
				if($args['rsvp']!='n'){	
					$this->send_email($args);
				}
				
				$this->send_email($args,'notification');

				$status = $created_rsvp_id;

			}else{	$status = 7; // new rsvp post was not created
			}
		
			return $status;
		}

	// EMAIL function  // deprecating
		public function _event_date($pmv, $start_unix, $end_unix){
			return $this->functions->_event_date($pmv, $start_unix, $end_unix);
		}

		// RETURN corected event end time for repeat interval
			function get_correct_event_end_time($e_pmv, $__repeatInterval){
				$datetime = new evo_datetime();
				return $datetime->get_int_correct_event_time($e_pmv, $__repeatInterval, 'end');	
		    }
		    function get_adjusted_event_formatted_times($e_pmv, $repeat_interval=''){
		    	$datetime = new evo_datetime();
		    	return $datetime->get_correct_formatted_event_repeat_time($e_pmv,$repeat_interval );
		    }

		// SEND email
			function send_email($args, $type='confirmation'){
				global $eventon_rs;

				// when email sending is disabled 
				if(!empty($this->optRS['evors_disable_emails']) && $this->optRS['evors_disable_emails']=='yes') return false;

				if($type=='confirmation'){
					$args['html']= 'yes';
					return $eventon_rs->helper->send_email(
						$this->get_email_data($args)
					);
				}elseif($type=='digest'){
					$args['html']= 'yes';
					return $eventon_rs->helper->send_email(
						$this->get_email_data($args, 'digest')
					);
				}else{// notification email
					if(!empty($this->optRS['evors_notif']) && $this->optRS['evors_notif']=='yes'){
						global $eventon_rs;
						$args['html']= 'yes';
						return $eventon_rs->helper->send_email(
							$this->get_email_data($args, 'notification')
						);
					}
				}
			}

		// send email confirmation of RSVP  to submitter
			function get_email_data($args, $type='confirmation'){
				$this->evors_args = $args;

				$email_data = array();

				$from_email = $this->get_from_email($type);
				
				$email_data['args'] = $args;
				$email_data['type'] = $type;

				switch ($type) {
					case 'confirmation':
						$email_data['to'] = $args['email'];

						$email_data['subject'] = '[#'.$args['rsvp_id'].'] '.((!empty($this->optRS['evors_notfiesubjest_e']))? 
						$this->optRS['evors_notfiesubjest_e']: __('RSVP Confirmation','eventon'));
						$filename = 'confirmation_email';
						$headers = 'From: '.$from_email;

					break;

					case 'digest':
						
						$__to_email = (!empty($this->optRS['evors_digestemail_to']) )?
							htmlspecialchars_decode ($this->optRS['evors_digestemail_to'])
							:get_bloginfo('admin_email');
						$email_data['to'] = $__to_email;

						$text = (!empty($this->optRS['evors_digestemail_subjest']))? $this->optRS['evors_digestemail_subjest']: 'Digest Email for {event-name}';
						
						if(!empty($args['e_id']))
							$text = str_replace('{event-name}', get_the_title($args['e_id']), $text);

						$email_data['subject'] = $text;
						$filename = 'digest_email';
						$headers = 'From: '.$from_email. "\r\n";

					break;
					
					default: // notification email
						$__to_email = (!empty($this->optRS['evors_notfiemailto']) )?
							htmlspecialchars_decode ($this->optRS['evors_notfiemailto'])
							:get_bloginfo('admin_email');
						$_other_to = get_post_meta($args['e_id'],'evors_add_emails', true);
						$_other_to = (!empty($_other_to))? $_other_to: null;

						$email_data['to'] = $__to_email.','.$_other_to;

						if(!empty($args['emailtype']) && $args['emailtype']=='update'){							
							$text = (!empty($this->optRS['evors_notfiesubjest_update']))? $this->optRS['evors_notfiesubjest_update']: 'Update RSVP Notification';
						}else{
							$text = (!empty($this->optRS['evors_notfiesubjest']))? $this->optRS['evors_notfiesubjest']: 'New RSVP Notification';
						}

						$email_data['subject'] ='[#'.$args['rsvp_id'].'] '.$text;
						$filename = 'notification_email';
						$headers = 'From: '.$from_email. "\r\n";
						$headers .= 'Reply-To: '.$args['email']. "\r\n";

					break;
				}
			
				if(isset($email_data['to'])){
					$email_data['message'] = $this->_get_email_body($args, $filename);
					$email_data['header'] = $headers;	
					$email_data['from'] = $from_email;
				}	
				
				return $email_data;
			}

			// return proper FROM email with name
				function get_from_email($type='confirmation'){

					if($type=='digest'){
						$__from_email = (!empty($this->optRS['evors_digestemail_from']) )?
							htmlspecialchars_decode ($this->optRS['evors_digestemail_from'])
							:get_bloginfo('admin_email');
						$__from_email_name = (!empty($this->optRS['evors_digestemail_fromN']) )?
							($this->optRS['evors_digestemail_fromN'])
							:get_bloginfo('name');
							$from_email = (!empty($__from_email_name))? 
								$__from_email_name.' <'.$__from_email.'>' : $__from_email;
					}else{
						$var = ($type=='confirmation')?'_e':'';

						$__from_email = (!empty($this->optRS['evors_notfiemailfrom'.$var]) )?
							htmlspecialchars_decode ($this->optRS['evors_notfiemailfrom'.$var])
							:get_bloginfo('admin_email');
						$__from_email_name = (!empty($this->optRS['evors_notfiemailfromN'.$var]) )?
							($this->optRS['evors_notfiemailfromN'.$var])
							:get_bloginfo('name');
							$from_email = (!empty($__from_email_name))? 
								$__from_email_name.' <'.$__from_email.'>' : $__from_email;
					}					
					return $from_email;
				}

		// email body for confirmation
			function _get_email_body($evors_args, $file){
				global $eventon, $eventon_rs;

				$path = $eventon_rs->addon_data['plugin_path']."/templates/";
				$args = $evors_args;

				$paths = array(
					0=> TEMPLATEPATH.'/'.$eventon->template_url.'templates/email/rsvp/',
					1=> $path,
				);

				$file_name = $file.'.php';
				foreach($paths as $path_){	
					// /echo $path.$file_name.'<br/>';			
					if(file_exists($path_.$file_name) ){	
						$template = $path_.$file_name;	
						break;
					}
				}

				ob_start();
				include($template);
				return ob_get_clean();
			}
			// this will return eventon email template driven email body
			// need to update this after evo 2.3.8 release
			function get_evo_email_body($message){
				global $eventon;
				// /echo $eventon->get_email_part('footer');
				ob_start();
				$wrapper = "
					background-color: #e6e7e8;
					-webkit-text-size-adjust:none !important;
					margin:0;
					padding: 25px 25px 25px 25px;
				";
				$innner = "
					background-color: #ffffff;
					-webkit-text-size-adjust:none !important;
					margin:0;
					border-radius:5px;
				";
				?>
				<!DOCTYPE html>
				<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /></head>
				<body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
					<div style="<?php echo $wrapper; ?>">
						<div style="<?php echo $innner;?>"><?php
				echo $message;
				echo $eventon->get_email_part('footer');
				return ob_get_clean();
			}

	// Digest emails
		public function schedule_digest_email(){
			if(!empty($this->optRS['evors_digest']) && $this->optRS['evors_digest']=='yes'){
				$events = new WP_Query(array(
					'post_type'=>'ajde_events',
					'posts_per_page'=>-1,
					'meta_key'     => 'evors_daily_digest',
					'meta_value'   => 'yes',
				));

				// if there are events with RSVP digest enabled
				if($events->have_posts()){
					global $eventon_rs;

					while($events->have_posts()): $events->the_post();
						$eventid = $events->post->ID;

						$what = $eventon_rs->frontend->send_email(array(
							'e_id'=>$eventid,
						), 'digest');
					endwhile;
				}
				wp_reset_postdata();
			}
		}

	// user RSVP manager
		function user_rsvp_manager(){
			global $eventon_rs, $eventon;

			$this->register_styles_scripts();
			add_action('wp_footer', array($this, 'footer_content'));	
			$this->footer_content();		

			// intial variables
			$current_user = get_user_by( 'id', get_current_user_id() );
			$USERID = is_user_logged_in()? get_current_user_id(): false;
			$current_page_link = get_page_link();

			// loading child templates
				$file_name = 'rsvp_user_manager.php';
				$paths = array(
					0=> TEMPLATEPATH.'/'.$eventon->template_url.'rsvp/',
					1=> $eventon_rs->plugin_path.'/templates/',
				);

				foreach($paths as $path){	
					if(file_exists($path.$file_name) ){	
						$template = $path.$file_name;	
						break;
					}
				}

			require_once($template);
		}
	
	// SUPPORT functions	
		// RETURN: language
			function lang($variable, $default_text){
				global $eventon_rs;
				return $eventon_rs->lang($variable, $default_text);
			}
		// function replace event name from string
			function replace_en($string){
				return str_replace('[event-name]', "<span class='eventName'>Event Name</span>", $string);
			}
		// get proper rsvp status name I18N
			public function get_checkin_status($status, $lang='', $evopt=''){
				$evopt = $this->opt2;
				$lang = (!empty($lang))? $lang : 'L1';

				if($status=='check-in'){
					return (!empty($evopt[$lang]['evoRSL_003x']))? $evopt[$lang]['evoRSL_003x']: 'check-in';
				}else{
					return (!empty($evopt[$lang]['evoRSL_003y']))? $evopt[$lang]['evoRSL_003y']: 'checked';
				}
			}
			public function get_trans_checkin_status($lang=''){
				$evopt = $this->opt2;
				$lang = (!empty($lang))? $lang : 'L1';

				return array(
					'check-in'=>(!empty($evopt[$lang]['evoRSL_003x'])? $evopt[$lang]['evoRSL_003x']: 'check-in'),
					'checked'=>(!empty($evopt[$lang]['evoRSL_003y'])? $evopt[$lang]['evoRSL_003y']: 'checked'),
				);
			}

		// Internationalization rsvp status yes, no, maybe
			public function get_rsvp_status($status, $lang=''){
				if(empty($status)) return;

				$opt2 = $this->opt2;
				$_sta = array(
					'y'=>array('Yes', 'evoRSL_003'),
					'n'=>array('No', 'evoRSL_005'),
					'm'=>array('Maybe', 'evoRSL_004'),
				);

				$lang = (!empty($lang))? $lang : (!empty($this->currentlang)? $this->currentlang: 'L1');
				return $this->lang($_sta[$status][1], $_sta[$status][0], $lang);
			}
		function create_post() {
			
			$type = 'evo-rsvp';
	        $valid_type = (function_exists('post_type_exists') &&  post_type_exists($type));

	        if (!$valid_type) {
	            $this->log['error']["type-{$type}"] = sprintf(
	                'Unknown post type "%s".', $type);
	        }
	       
	        $title = 'RSVP '.date('M d Y @ h:i:sa', time());
	        $author = ($this->get_author_id())? $this->get_author_id(): 1;

	        $new_post = array(
	            'post_title'   => $title,
	            'post_status'  => 'publish',
	            'post_type'    => $type,
	            'post_name'    => sanitize_title($title),
	            'post_author'  => $author,
	        );
	       
	        // create!
	        $id = wp_insert_post($new_post);
	       
	        return $id;
	    }
		function create_custom_fields($post_id, $field, $value) {       
	        add_post_meta($post_id, $field, $value);
	    }
	    function update_custom_fields($post_id, $field, $value) {       
	        update_post_meta($post_id, $field, $value);
	    }
    	function get_author_id() {
			$current_user = wp_get_current_user();
	        return (($current_user instanceof WP_User)) ? $current_user->ID : 0;
	    }	
	    function get_event_post_date() {
	        return date('Y-m-d H:i:s', time());        
	    }
	    // return sanitized additional rsvp field option values
	    function get_additional_field_options($val){
	    	$OPTIONS = stripslashes($val);
			$OPTIONS = str_replace(', ', ',', $OPTIONS);
			$OPTIONS = explode(',', $OPTIONS);
			$output = false;
			foreach($OPTIONS as $option){
				$slug = str_replace(' ', '-', $option);
				$output[$slug]= $option;
			}
			return $output;
	    }
}
