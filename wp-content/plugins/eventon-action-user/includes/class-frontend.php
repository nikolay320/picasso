<?php
/*
 *	ActionUser front-end
 *	@version 	1.9.4
 */

class evoau_frontend{
	private $urls;
	var $log= array();
	var $users;
	var $formtype = 'new';

	public $message, $link, $lang;

	function __construct(){

		add_filter('eventon_extra_tax',array($this,'extra_tax'),10,1);
		add_action( 'init', array( $this, 'register_frontend_scripts' ) ,15);

		//when a new post is published
		add_action('transition_post_status',array($this,'send_approval_email'), 10, 3);

		$this->options = get_option('evcal_options_evcal_1');
		$this->tax_count = evo_get_ett_count($this->options);
		$this->tax_names = evo_get_ettNames($this->options);
		$this->evoau_opt = get_option('evcal_options_evoau_1');
		$this->evoau_opt_2 = get_option('evcal_options_evoau_2');

		// functions
		include_once('class_functions.php');
		$this->functions = new evoau_functions($this->evoau_opt);
	}
	function extra_tax($array){
		$array['evoau']='event_users';
		return $array;
	}

	// FRONTEND scripts
		function register_frontend_scripts(){
			global $eventon_au, $ajde;		

			wp_register_script( 'evoau_cookies',$eventon_au->assets_path.'js/jq_cookie.js',array('jquery','jquery-ui-core','jquery-ui-datepicker'), $eventon_au->version, true );
			wp_register_script( 'evo_au_frontend',$eventon_au->assets_path.'js/au_script_f.js',array('jquery','jquery-ui-core','jquery-ui-datepicker'), $eventon_au->version, true );
			wp_register_script( 'evo_au_timepicker',AJDE_EVCAL_URL.'/assets/js/jquery.timepicker.js',array('jquery'), $eventon_au->version, true );

			wp_register_style( 'evo_au_styles_f',$eventon_au->assets_path.'au_styles.css');

			wp_localize_script( 
				'evo_au_frontend', 
				'evoau_ajax_script', 
				array( 
					'ajaxurl' => admin_url( 'admin-ajax.php' ) , 
					'postnonce' => wp_create_nonce( 'eventonau_nonce' )
				)
			);
			$ajde->register_colorpicker();

		}
		// ENQUEUE
		public function print_frontend_scripts(){
			global $eventon_au;

			$jquery_version = isset( $wp_scripts->registered['jquery-ui-core']->ver ) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.10.4';	

			wp_enqueue_script('tiny_mce');	
	
			wp_enqueue_style("jquery-ui-css", "//ajax.googleapis.com/ajax/libs/jqueryui/{$jquery_version}/themes/smoothness/jquery-ui.min.css");
	
			$eventon_JQ_UI_tp = AJDE_EVCAL_URL.'/assets/css/jquery.timepicker.css';
			wp_enqueue_style( 'eventon_JQ_UI_tp',$eventon_JQ_UI_tp);
			wp_enqueue_style( 'evo_font_icons');
			wp_enqueue_style( 'evo_au_styles_f');			
			
			wp_enqueue_script('evo_au_timepicker');	
			wp_enqueue_script('jquery-form');
			wp_enqueue_script('evcal_ajax_handle');	// call eventon_script.js if not loaded
			wp_enqueue_script('evoau_cookies');
			wp_enqueue_script('evo_au_frontend');

			global $ajde;
			$ajde->load_colorpicker();			
		}

		public function print_styles(){
			global $eventon_au;

			$eventon_JQ_UI_tp = $eventon_au->assets_path.'css/jquery.timepicker.css';
			wp_enqueue_style( 'eventon_JQ_UI_tp',$eventon_JQ_UI_tp);
			wp_enqueue_style( 'evo_font_icons');
			wp_enqueue_style( 'evo_au_styles_f');
		}

	// submission form
		function output_event_submission_form($atts){
			// ACTUAL FORM content
			$this->lang = (!empty($atts['lang']))? $atts['lang']:'L1';
			$this->get_submission_form('',$atts);
		}

		// get only the submission form
			function get_submission_form($event_id ='', $atts=''){
				$this->print_frontend_scripts();	
				require_once('front_end_form.php');
			}

	// Event manager
		// user event manager for front-end
		// @version 0.1
			function event_manager($atts){
				global $eventon_au, $eventon;

				$this->print_frontend_scripts();	
				$this->lang = (!empty($atts['lang']))? $atts['lang']:'L1';			

				// intial variables
				$current_user = get_user_by( 'id', get_current_user_id() );
				$USERID = is_user_logged_in()? get_current_user_id(): false;
				$current_page_link = get_page_link();

				// loading child templates
					$file_name = 'event_manager.php';
					$paths = array(
						0=> TEMPLATEPATH.'/'.$eventon->template_url.'actionuser/',
						1=> STYLESHEETPATH.'/'.$eventon->template_url.'actionuser/',
						2=> $eventon_au->plugin_path.'/templates/',
					);

					foreach($paths as $path){	
						if(file_exists($path.$file_name) ){	
							$template = $path.$file_name;	
							break;
						}
					}

				require_once($template);
			}
		// user created events
			function get_user_events($userid){
				$events = new WP_Query(array(
					'post_type'=>'ajde_events',
					'posts_per_page'=>-1,
					'post_status'=>'any',
					'author'=>$userid
				));

				$eventIDs = array();

				if($events->have_posts()){
					while($events->have_posts()): $events->the_post();
						$eventIDs[$events->post->ID] = array(
							$events->post->post_title,
							$events->post->post_status,
							$events->post->ID
						);
					endwhile;
					wp_reset_postdata();
				}

				return $eventIDs;
				
			}
		// print event manager styles into the page body
			public function print_em_styles(){
				global $eventon_au;
				echo "<style type='text/css'>";
				include_once($eventon_au->plugin_path."/assets/au_eventmanager_styles.php");
				echo "</style>";
			}

	// FORM Fields for the front end form
		public function au_form_fields($var=''){
			$evcal_opt = $this->options;		
			
			/* 
				structure = 0=>name, 1=> var, 2=> field type, 3=>placeholder, 4=>lang var, 5=> required or not, 6=>special name for settings
			*/
			$event_fields = array(				
				'event_name'=>array('Event Name', 'event_name', 'title','','evoAUL_evn'),
				'event_subtitle'=>array('Event Sub Title', 'evcal_subtitle', 'text','','evoAUL_est'),
				'event_description'=>array('Event Details', 'event_description', 'textarea','','evcal_evcard_details_au'),
				'event_start_date'=>array('Event Start Date/Time', 'evcal_start_date', 'startdate','','evoAUL_esdt'),
				'event_end_date'=>array('Event End Date/Time', 'event_end_date', 'enddate','','evoAUL_eedt'),
				'event_allday'=>array('All Day', 'event_allday', 'allday','',''),
				
				'event_location_select'=>array('Event Location Fields', 'evcal_location_select', 'locationselect','','evoAU_pseld'),
					'event_location_name'=>array('Event Location Name', 'evcal_location_name', 'text','', 'evoAUL_lca'),
					'event_location'=>array('Event Location Address', 'location_address', 'text','','evoAUL_ln'),
					'event_location_cord'=>array('Event Location Coordinates (lat,lon Seperated by comma)', 'event_location_cord', 'text','','evoAUL_lcor'),
					'event_location_link'=>array('Event Location Link', 'evcal_location_link', 'text','','evoAUL_llink'),
				'event_color'=>array('Event Color', 'evcal_event_color', 'color','','evoAUL_ec'),
				'event_organizer_select'=>array('Event Organizer Fields', 'evcal_organizer_select', 'organizerselect','','evoAU_pseod'),
					'event_organizer'=>array('Event Organizer', 'evcal_organizer', 'text','','evoAUL_eo'),	
					'event_org_contact'=>array('Event Organizer Contact Information', 'evcal_org_contact', 'text','','evoAUL_eoc'),	
					'event_org_address'=>array('Event Organizer Address', 'evcal_org_address', 'text','','evoAUL_eoa'),	
					'event_org_link'=>array('Event Organizer Link', 'evcal_org_exlink', 'text','','evoAUL_eol'),	
				'learnmorelink'=>array('Learn More Link', 'evcal_lmlink', 'learnmore','','evoAUL_lml'),	
			);

			// event type categories
				$ett_verify = $this->tax_count;
				$_tax_names_array = $this->tax_names;
				for($x=1; $x< ($ett_verify+1); $x++){
					$ab = ($x==1)? '':'_'.$x;
					$__tax_name = $_tax_names_array[$x];

					$event_fields['event_type'.$ab] = array(
						'Select the '.$__tax_name.' Category', 
						'event_type'.$ab, 'tax','',
						'evoAUL_stet'.$x
					);
				}

			$event_fields_1 = array(
				'event_image'=>array('Event Image', 'event_image', 'image','','evoAUL_ei'),
				'yourname'=>array('Your Full Name', 'yourname', 'text','','evoAUL_fn','req'),
				'youremail'=>array('Your Email Address', 'youremail', 'text','','evoAUL_ea','req'),
				'user_interaction'=>array('User Interaction', 'uinter', 'uiselect','','evoAUL_ui'),		
				'event_captcha'=>array('Form Human Submission Validation', 'evcal_captcha', 'captcha','','evoAUL_cap'),
				'event_additional'=>array('Additonal Private Notes', 'evcalau_notes', 'textarea','','evoAU_add','','*** Additional private notes for admin'),
				'event_html'=>array('** Additonal HTML Field', 'evoau_html', 'html'),
			);
			$event_fields = array_merge($event_fields, $event_fields_1);

			// get custom meta fields for 
				$custom_field_count = evo_calculate_cmd_count($evcal_opt);	// get activated custom field count		
				for($x=1; $x<=$custom_field_count; $x++){	
					$new_additions='';

					if(eventon_is_custom_meta_field_good($x, $evcal_opt)){
						$index = 'evo_customfield_'.$x;
						$_variable_name = '_evcal_ec_f'.$x.'a1_cus';
						$_field_name = $evcal_opt['evcal_ec_f'.$x.'a1'];
						$content_type = $evcal_opt['evcal_ec_f'.$x.'a2'];

						$new_additions[$index]= array(
							$_field_name, $_variable_name,  $content_type, $_field_name, 'evcal_cmd_'.$x
						);
						$event_fields = array_merge($event_fields, $new_additions);
					}
				}

			// Filter for other additions
			$event_fields = apply_filters('evoau_form_fields', $event_fields);
			
			// return certain fields from above list
				if($var=='savefields'){
					unset($event_fields['event_name']);
					unset($event_fields['event_start_date']);
					unset($event_fields['event_end_date']);
					unset($event_fields['event_allday']);
					unset($event_fields['event_description']);
					unset($event_fields['yourname']);
					unset($event_fields['youremail']);
					unset($event_fields['event_captcha']);
					unset($event_fields['user_interaction']);
					unset($event_fields['event_location_cord']);
					unset($event_fields['event_color']);
				}
				if($var=='default'){
					$event_fields = array(
						'event_name'=>$event_fields['event_name'],
						'event_start_date'=>$event_fields['event_start_date'],
						'event_end_date'=>$event_fields['event_end_date'],
						'event_allday'=>$event_fields['event_allday'],
					);
				}
				if($var=='additional'){
					unset($event_fields['event_name']);
					unset($event_fields['event_start_date']);
					unset($event_fields['event_end_date']);
					unset($event_fields['event_allday']);

					unset($event_fields['event_organizer']);
					unset($event_fields['event_org_contact']);
					unset($event_fields['event_org_address']);
					unset($event_fields['event_org_link']);

					unset($event_fields['event_location_name']);
					unset($event_fields['event_location']);
					unset($event_fields['event_location_cord']);
					unset($event_fields['event_location_link']);
				}
				if($var== 'defaults_ar'){
					$event_fields = array('event_name','event_start_date','event_end_date','event_allday');
				}
			
			return $event_fields;
		}

	// SAVE form submittions UPON submit
		function save_form_submissions(){
			$status= $cu_email='';

			//process $_POST array
				foreach($_POST as $ff=>$post){
					if(!is_array($post))
						$_POST[$ff]= urldecode($post);
				}

			// edit or add new
				if(isset($_POST['form_action']) && isset($_POST['eventid']) && $_POST['form_action']=='editform' ){
					$created_event_id = (int)$_POST['eventid'];
					$this->formtype = 'edit';
					$__post_content = (!empty($_POST['event_description']))?
	        			wpautop(convert_chars(stripslashes($_POST['event_description']))): null;
					
					// update event name and event details
					$event = array(
						'ID'=> $created_event_id,
						'post_title'=>wp_strip_all_tags($_POST['event_name']),
						'post_content'=>$__post_content
					);
					wp_update_post( $event );
				}else{
					$created_event_id = $this->create_post();
					$this->formtype = 'new';
				}

			if($created_event_id){	
				// saved field valy
				$saved_fields = (!empty($this->evoau_opt['evoau_fields']) && is_array($this->evoau_opt['evoau_fields']) && count($this->evoau_opt['evoau_fields'])>0)? $this->evoau_opt['evoau_fields']: false;	

				// SAVE DATE TIMES and start/end - meta data
					if(isset($_POST['event_start_date'])  ){

						// if no end date
						$end_date = (!empty($_POST['event_end_date']))? $_POST['event_end_date']: $_POST['event_start_date'];

						// start times
						$start_time = (!empty($_POST['event_start_time']))? $_POST['event_start_time']:'1:00:am';
						$end_time = (!empty($_POST['event_end_time']))? $_POST['event_end_time']:'11:55:pm';
					
						$start_time = explode(":",$start_time);
						$end_time = explode(":",$end_time);

							$__ampm_s = (!empty($start_time[2]))? $start_time[2]: null;
							$__ampm_e = (!empty($end_time[2]))? $end_time[2]: null;	

						// date and time array
							$date_array_end = array(
								'evcal_end_date'=>$end_date,
								'evcal_end_time_hour'=>$end_time[0],
								'evcal_end_time_min'=>$end_time[1],
								'evcal_et_ampm'=>$__ampm_e,
								'evcal_start_date'=>$_POST['event_start_date'],
								'evcal_start_time_hour'=>$start_time[0],
								'evcal_start_time_min'=>$start_time[1],
								'evcal_st_ampm'=>$__ampm_s,
							);			
										
						// all day events
						if(!empty($_POST['evcal_allday']) && $_POST['evcal_allday']=='yes'){
							$this->create_custom_fields($created_event_id, 'evcal_allday', 'yes');
						}

						if(!empty($_POST['evo_hide_endtime']) && $_POST['evo_hide_endtime']=='yes'){
							$this->create_custom_fields($created_event_id, 'evo_hide_endtime', 'yes');
						}					
						
						// merge both start and end time values
						$date_array = $date_array_end;
												
						$__evo_date_format = (!empty($_POST['_evo_date_format']))? $_POST['_evo_date_format']: 'd/m/Y';
						$_evo_time_format = (!empty($_POST['_evo_time_format']))? $_POST['_evo_time_format']: '12h';
						$proper_time = eventon_get_unix_time($date_array, $__evo_date_format, $_evo_time_format);
										
						// save required start time variables
						$this->create_custom_fields($created_event_id, 'evcal_srow', $proper_time['unix_start']);
						$this->create_custom_fields($created_event_id, 'evcal_erow', $proper_time['unix_end']);	

						// save repeating data for the event
							if( isset($_POST['evcal_repeat']) && $_POST['evcal_repeat']=='yes' 
								&& isset($_POST['evcal_rep_freq'])
							){
								$repeat_intervals = eventon_get_repeat_intervals($proper_time['unix_start'],$proper_time['unix_end'] );
								if ( !empty($repeat_intervals) ){
									asort($repeat_intervals);
									$this->create_custom_fields($created_event_id, 'repeat_intervals', $repeat_intervals);

									// other repeat data
									$this->create_custom_fields($created_event_id, 'evcal_repeat', $_POST['evcal_repeat']);
									$this->create_custom_fields($created_event_id, 'evcal_rep_freq', $_POST['evcal_rep_freq']);
									$this->create_custom_fields($created_event_id, 'evcal_rep_gap', $_POST['evcal_rep_gap']);
									$this->create_custom_fields($created_event_id, 'evcal_rep_num', $_POST['evcal_rep_num']);
								}
							}
					}
				
				// initial
					$image_set = false;

				// create custom meta fields and assign taxonomies			
					foreach($this->au_form_fields('savefields') as $field=>$fn){
						$__var_name = $fn[1];

						// check if value passed
						if(isset($_POST[$__var_name])){
							// for event taxonomies
							if($fn[2] =='tax' ){	
								// save post terms
								if(count($_POST[$__var_name])>0 && is_array($_POST[$__var_name])){	
									// for tax #1 and #2
									if($field=='event_type_2' || $field=='event_type'){
										$ab = ($field=='event_type')? '':'_2';
										$terms = $_POST[$__var_name];

										// append default tax terms if activated in options
										if(!empty($this->evoau_opt['evoau_set_def_ett'.$ab]) 
											&& !empty($this->evoau_opt['evoau_def_ett_v'.$ab]) 
											&& $this->evoau_opt['evoau_def_ett_v'.$ab]!='-' 
											&& $this->evoau_opt['evoau_set_def_ett'.$ab]=='yes'
										){
											$terms[] = $this->evoau_opt['evoau_def_ett_v'.$ab];
										}	
										wp_set_post_terms($created_event_id, $terms, $field);

									}else{
										wp_set_post_terms($created_event_id, $_POST[$__var_name], $field);
									}
								}
							// learn more field
							}elseif($fn[2] == 'learnmore'){
								// the actual learn more link url
								$value = $this->convert_links_to_proper($_POST[$__var_name]);
								$this->create_custom_fields($created_event_id, $__var_name, $value);
								
								// learn more open in new window
								if(isset($_POST['evcal_lmlink_target'])) 
									$this->create_custom_fields($created_event_id, 'evcal_lmlink_target', $_POST['evcal_lmlink_target']);

							}elseif(!empty($fn[3]) && $fn[3] == 'custom'){
								// pluggable
								do_action('evoau_save_formfields',$field, $fn, $created_event_id);
							}else{
								$value = addslashes($_POST[$__var_name]);
								$this->create_custom_fields($created_event_id, $__var_name, $value);
							}

							// custom meta field that is a button
							if($fn[2]=='button' && !empty($_POST[$__var_name.'L'])){
								$this->create_custom_fields($created_event_id, $__var_name.'L', $_POST[$__var_name.'L']);
							}
						}// end if var not set

						// create new tax term
							if($fn[2] =='tax' && !empty($_POST[$__var_name.'_new'])){								
								$terms = $_POST[$__var_name.'_new'];
								$terms = explode(',', $terms);

								foreach($terms as $term){
									$this->set_new_term($term, $__var_name, $created_event_id);	
								}
							}	

						// Assign tax terms if activated but NOT visible on the form
							if($field=='event_type_2' || $field=='event_type'){
								$ab = ($field=='event_type')? '':'_2';
								
								// append default tax terms if activated in options
								if(!empty($this->evoau_opt['evoau_set_def_ett'.$ab]) 
									&& !empty($this->evoau_opt['evoau_def_ett_v'.$ab]) 
									&& $this->evoau_opt['evoau_def_ett_v'.$ab]!='-' 
									&& $this->evoau_opt['evoau_set_def_ett'.$ab]=='yes'
								){
									$terms[] = $this->evoau_opt['evoau_def_ett_v'.$ab];
									wp_set_post_terms($created_event_id, $terms, $field);
								}	
							}

						// image
							// check id default image set for forms
							if(!empty($this->evoau_opt['evoau_def_image']) && !$image_set){
								set_post_thumbnail($created_event_id, $this->evoau_opt['evoau_def_image']);
								$image_set = true;
							}

							if($field == 'event_image'  ){
								// on edit form if image already set
								if(isset($_POST['event_image_exists']) && $_POST['event_image_exists']=='yes')
									continue;

								if( !empty( $_FILES ) && 'POST' == $_SERVER['REQUEST_METHOD']  ){

									if ($_FILES[$__var_name]['error'] !== UPLOAD_ERR_OK) __return_false();

									require_once (ABSPATH.'/wp-admin/includes/media.php');
									require_once (ABSPATH.'/wp-admin/includes/file.php');
									require_once (ABSPATH.'/wp-admin/includes/image.php');	

									$attachmentId = media_handle_upload($__var_name, $created_event_id);
									unset($_FILES);

									set_post_thumbnail($created_event_id, $attachmentId);
									$this->create_custom_fields($created_event_id, 'ftimg', $attachmentId);
								} else if ($_POST['event_image_exists']=='no') {
									delete_post_thumbnail( $created_event_id );
								}
							}
						
					} // end foreach
				
				// event color
					$COLOR = !empty($_POST['evcal_event_color'])? $_POST['evcal_event_color']: 
						( !empty($this->options['evcal_hexcode'])? $this->options['evcal_hexcode']:'206177' );
					$this->create_custom_fields($created_event_id, 'evcal_event_color', $COLOR);
					if(isset($_POST['evcal_event_color_n']))
						$this->create_custom_fields($created_event_id, 'evcal_event_color_n', $_POST['evcal_event_color_n']);

				// current user 
					$current_user = wp_get_current_user();
					// if user is logged in
					if(!empty($current_user)){
						// get the user email if the user is logged in and has email
						$cu_email = $current_user->user_email;						
					}

				// assign author is set to do so
					if($this->formtype=='new' && is_user_logged_in() && !empty($this->evoau_opt['evoau_assignu']) && $this->evoau_opt['evoau_assignu']=='yes'){
						
						// if user is logged in
						if(!empty($current_user)){	
							$user_id = (string)$current_user->ID;			
							wp_set_object_terms( $created_event_id, array( $user_id ), 'event_users' );
						}
					}

				// Save user interaction fields
					if($saved_fields && in_array('user_interaction', $saved_fields) || (!empty($this->evoau_opt['evoau_ux']) && $this->evoau_opt['evoau_ux']=='yes') ){
						if(isset($_POST['uinter']) ){
							// only for external links
							if($_POST['uinter']==2){ // open as external links
								if(!empty($_POST['_evcal_exlink_target']))
									$this->create_custom_fields($created_event_id, '_evcal_exlink_target', $_POST['_evcal_exlink_target']);
								if(!empty($_POST['evcal_exlink']))
									$this->create_custom_fields($created_event_id, 'evcal_exlink', $_POST['evcal_exlink']);
							}elseif($_POST['uinter']==4){// open as single events
								$exlink = get_permalink($created_event_id);
								$this->create_custom_fields($created_event_id, 'evcal_exlink', $exlink);
							}
						}

						// ux value, check if submit from form, else default val set in settings
						$ux_val = (!empty($_POST['uinter']))? $_POST['uinter']:
							( (!empty($this->evoau_opt['evoau_ux']) && $this->evoau_opt['evoau_ux']=='yes')?
								$this->evoau_opt['evoau_ux_val']:
								false
							);

						if($ux_val)
							$this->create_custom_fields($created_event_id, '_evcal_exlink_option', $ux_val);
					}

				//$this->create_custom_fields($created_event_id,'aaa', $debug);

				// generate google maps
					$googleMapsVal = ( !empty($_POST['evcal_location'])) ?'yes':'no';
					$this->create_custom_fields($created_event_id, 'evcal_gmap_gen', $googleMapsVal);
				
				// save location as taxonomy
					if(!empty($_POST['evcal_location_name'])){
						$this->set_new_term($_POST['evcal_location_name'], 'event_location', $created_event_id);
					}					

				// save organizer as taxonomy
					if(!empty($_POST['evcal_organizer'])){
						$this->set_new_term($_POST['evcal_organizer'], 'event_organizer', $created_event_id);	
					}
				
				// OTHER eventon addon intergration
					// Reviewer addon
						if( !empty($this->evoau_opt['evoar_re_addon']) && $this->evoau_opt['evoar_re_addon']=='yes' ){
							$this->create_custom_fields($created_event_id, 'event_review', 'yes');
						}
				// PLUGGABLE eventon addon intergration
					do_action('eventonau_save_form_submissions',$created_event_id, $this->formtype);

				// save submitter email address
					if(!empty($_POST['yourname']) && isset($_POST['yourname']))
						$this->create_custom_fields($created_event_id, '_submitter_name', $_POST['yourname']);

					// save email address for submitter
					if(!empty($_POST['youremail']) && isset($_POST['youremail'])){
						$this->create_custom_fields($created_event_id, '_submitter_email', $_POST['youremail']);
					}elseif(!empty($cu_email)){
						// save current user email if it exist
						$this->create_custom_fields($created_event_id, '_submitter_email', $cu_email);
					}

				// save whether to notify when draft is published if submission saved as draft
					if((empty($this->evoau_opt['evoau_post_status'])) || 
						( !empty($this->evoau_opt['evoau_post_status']) && $this->evoau_opt['evoau_post_status']=='draft' )){
						$this->create_custom_fields($created_event_id, '_send_publish_email', 'true');
					}				

				// email notification
					$__evo_admin_email = get_option('admin_email');
					if($this->formtype=='new'){
						$this->send_au_email_notif($created_event_id, $__evo_admin_email);
						$this->send_submitter_email_notif($created_event_id, $__evo_admin_email);
					}
				
				return array('status'=>'good','msg'=>'');
			}else{
				// could not create custom post type
				return array('status'=>'bad','msg'=>'nof4');
			}
		}
	
	/** Create the event post	 */
		function create_post() {
			$helper = new evo_helper();
		
			// event post status
			$opt_draft = (!empty($this->evoau_opt['evoau_post_status']))?
				$this->evoau_opt['evoau_post_status']:'draft';

				// override new event publish status if the user can submit events
					if($opt_draft=='draft' && current_user_can('publish_eventon'))
						$opt_draft = 'publish';
				
	        $type = 'ajde_events';
	        $valid_type = (function_exists('post_type_exists') &&  post_type_exists($type));

	        if (!$valid_type) {
	            $this->log['error']["type-{$type}"] = sprintf(
	                'Unknown post type "%s".', $type);
	        }

	        $__post_content = (!empty($_POST['event_description']))?
	        	wpautop(convert_chars(stripslashes($_POST['event_description']))): '';

	        return $helper->create_posts(array(
				'post_type'=>$type,
				'post_title'=>wp_strip_all_tags($_POST['event_name']),
				'post_status'=>$opt_draft,
				'post_content'=>$__post_content,
			));     

	        return $post_id;
	    }
		function create_custom_fields($post_id, $field, $value) { 
			if($this->formtype=='new'){
				add_post_meta($post_id, $field, $value);
			}else{
				update_post_meta($post_id, $field, $value);
			}	        
	    }
	    function set_new_term($term, $taxonomy, $post_id){
	    	$TERMEXIST = term_exists($term, $taxonomy);
	    	$data = '';

	    	// if term doesnt exists
			if(!$TERMEXIST){
				$slug = str_replace(' ', '-', $term);
				$NEWTERMID = wp_insert_term(
				  	$term, // the term 
				  	$taxonomy, // the taxonomy
				  	array(
				  		'slug'=>$slug
				  	)
				);
				if(!is_wp_error($NEWTERMID)){
					if($taxonomy =='event_organizer'){
						$term_meta = array();

						// save organizer taxonomy data as meta
						if(isset($_POST['evcal_org_contact'])) $term_meta['evcal_org_contact'] = $_POST['evcal_org_contact'];
						if(isset($_POST['evcal_org_address'])) $term_meta['evcal_org_address'] = $_POST['evcal_org_address'];
						if(isset($_POST['evcal_org_exlink'])) $term_meta['evcal_org_exlink'] = $_POST['evcal_org_exlink'];
												
						//$term_meta['evo_org_img'] = (isset($_POST['evo_org_img']))?$_POST['evo_org_img']:null;;
						update_option("taxonomy_".$NEWTERMID['term_id'], $term_meta);
					}

					// Location
					if($taxonomy == 'event_location'){
						$term_meta = array();

						if(!empty($_POST['event_location_cord'])){
							$cord = explode(',', $_POST['event_location_cord']);
							$term_meta['location_lat'] = $cord[0];
							$term_meta['location_lon'] = $cord[1];							
						}

						if(isset($_POST['evcal_location'])) $term_meta['location_address'] = $_POST['evcal_location'];
						if(isset($_POST['event_location_link'])) $term_meta['evcal_location_link'] = $_POST['event_location_link'];

						// location image
						if(!empty($_POST['evo_loc_img_id']))
							$this->create_custom_fields($post_id, 'evo_loc_img', $_POST['evo_loc_img_id']);

						update_option("taxonomy_".$NEWTERMID['term_id'], $term_meta);
						$data .='3';
					}
					wp_set_object_terms($post_id, $term, $taxonomy);
					$data .='2';
				}
			}else{
			// term exists
				wp_set_object_terms($post_id, $term, $taxonomy);	
				$data .='1';			
			}

			//update_post_meta(1350, 'aa', $data);
	    }
	    // check if the submitted link data have complete url if not make it http:// url
	    function convert_links_to_proper($linkData){
	    	if(strpos($linkData, 'http')!== false){
	    		return $linkData;
	    	}else{
	    		$linkData =str_replace('http://', '', $linkData);
	    		$linkData =str_replace('http:/', '', $linkData);
	    		$linkData =str_replace('http', '', $linkData);
	    		$linkData =str_replace('://', '', $linkData);
	    		return 'http://'.$linkData;
	    	}
	    }

	// EMAILING
	// ACTUAL SENDING OF EMAIL
		function send_email($to, $from, $subject, $message){			
			add_filter('wp_mail_content_type',create_function('', 'return "text/html";'));

			$headers = 'From: '.$from;
			$send_wp_mail = wp_mail($to, $subject, $message, $headers);
				
			return $send_wp_mail;
		}
	// when event is published or apporved and published
		function send_approval_email($new_status, $old_status, $post){

			$post_type  = get_post_type($post->ID);
			if( $post_type !== 'ajde_events' )
       			return;

			if($old_status == 'draft' && $new_status == 'publish'){

				$pmv = get_post_custom($post->ID);
				$event_id = $post->ID;
				$this->create_custom_fields($event_id, 'tester', 'sendOut');

				// settings set to send approval email notifications and the event is set to notify upon event approval (publish) 
				if(!empty($this->evoau_opt['evoau_notsubmitterAP']) 
					&& ($this->evoau_opt['evoau_notsubmitterAP'])=='yes' 
					&& ( !empty($pmv['_send_publish_email']) && $pmv['_send_publish_email'][0]=='true')
					&&  !empty($pmv['_submitter_email']) 
				){									

					$this->create_custom_fields($event_id, 'tester', 'send');

					$to = $pmv['_submitter_email'][0];

					$from = (!empty( $this->evoau_opt['evoau_ntf_pub_from'])) ? htmlspecialchars_decode($this->evoau_opt['evoau_ntf_pub_from']) : $__evo_admin_email;

					$subject = (!empty( $this->evoau_opt['evoau_ntf_pub_subject'])) ? $this->evoau_opt['evoau_ntf_pub_subject'] : 'We have approved your event!';

					$_message = (!empty( $this->evoau_opt['evoau_ntf_pub_msg'])) ? stripslashes($this->evoau_opt['evoau_ntf_pub_msg']) : 'Thank you for submitting your event and we have approved it!';

					$message = $this->_get_email_body($_message, $event_id);

					$send_wp_mail = $this->send_email($to, $from, $subject, $message);

					// set post meta to not send emails again 
					update_post_meta($event_id, '_send_publish_email', 'no');
					
					return $send_wp_mail;
				}
			}
		}

	// send email notification of new events to ADMIN
		function send_au_email_notif($event_id, $admin_email){

			$__evo_admin_email = $admin_email;				

			if(!empty($this->evoau_opt['evoau_notif']) && ($this->evoau_opt['evoau_notif'])=='yes'){
				
				$to = (!empty( $this->evoau_opt['evoau_ntf_admin_to'])) ? htmlspecialchars_decode($this->evoau_opt['evoau_ntf_admin_to']):$__evo_admin_email;

				$from = (!empty( $this->evoau_opt['evoau_ntf_admin_from'])) ? htmlspecialchars_decode($this->evoau_opt['evoau_ntf_admin_from']) : $__evo_admin_email;

				$subject = (!empty( $this->evoau_opt['evoau_ntf_admin_subject'])) ? $this->evoau_opt['evoau_ntf_admin_subject'] : 'New Event Submission';

				$_message = (!empty( $this->evoau_opt['evoau_ntf_admin_msg'])) ? stripslashes($this->evoau_opt['evoau_ntf_admin_msg']) : 'You have a new event submission!';

				$message = $this->_get_email_body($_message, $event_id);

				$send_wp_mail = $this->send_email($to, $from, $subject, $message);
				
				return $send_wp_mail;

			}
		}

	// send email to event submitter
		function send_submitter_email_notif($event_id, $admin_email){
			$__evo_admin_email = $admin_email;	
			
			if(!empty($this->evoau_opt['evoau_notsubmitter']) && ($this->evoau_opt['evoau_notsubmitter'])=='yes' ){
				
				// current user if there is any
				$current_user = wp_get_current_user();

				if(!empty($current_user->user_email) || (!empty($_POST['youremail']) && isset($_POST['youremail'])) ){

					// use the correct email address logged in email first and then submitted email
					$to = (!empty($current_user->user_email))? $current_user->user_email: $_POST['youremail'];

					$from = (!empty( $this->evoau_opt['evoau_ntf_user_from'])) ? htmlspecialchars_decode($this->evoau_opt['evoau_ntf_user_from']) : $__evo_admin_email;

					$subject = (!empty( $this->evoau_opt['evoau_ntf_drf_subject'])) ? $this->evoau_opt['evoau_ntf_drf_subject'] : 'We have received your event!';

					$_message = (!empty( $this->evoau_opt['evoau_ntf_drf_msg'])) ? stripslashes($this->evoau_opt['evoau_ntf_drf_msg']) : 'Thank you for submitting your event!';
					
					$message = $this->_get_email_body($_message, $event_id);

					$send_wp_mail = $this->send_email($to, $from, $subject, $message);
					
					return $send_wp_mail;
				}

			}
			
		}

	// GET email body for messages
		function _get_email_body($message, $eventid=''){
			global $eventon, $eventon_au;

			$adminurl = get_admin_url();
			$editlink = $adminurl."post.php?post={$eventid}&action=edit";

			// process body tags for email message 
				$message =str_replace('{event-edit-link}', $editlink, $message);
				$message =str_replace('{event-name}', get_the_title($eventid), $message);
				$message =str_replace('{event-link}', get_permalink($eventid), $message);
				$message =str_replace('{event-start-date}', $_POST['event_start_date'], $message);
				$message =str_replace('{event-start-time}', $_POST['event_start_time'], $message);
				$message =str_replace('{event-end-date}', $_POST['event_end_date'], $message);
				$message =str_replace('{event-end-time}', $_POST['event_end_time'], $message);

				if(isset($_POST['yourname']))
					$message =str_replace('{submitter-name}', $_POST['yourname'], $message);
				if(isset($_POST['youremail']))
					$message =str_replace('{submitter-email}', $_POST['youremail'], $message);


			$this->message = html_entity_decode($message);			
			$path = $eventon_au->plugin_path.'/templates/';

			return $eventon->get_email_body('notif_email',$path);
		}
}
