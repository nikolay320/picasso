<?php
/**
 * 
 * Event Reviewer front end class
 *
 * @author 		AJDE
 * @category 	Admin
 * @package 	eventon-reviewer/classes
 * @version     0.1
 */
class evore_front{
	private $currentlang;

	function __construct(){
		global $eventon_re;

		include_once('class-functions.php');
		$this->functions = new evo_re_functions();

		add_filter('eventon_eventCard_evore', array($this, 'frontend_box'), 10, 2);
		add_filter('eventon_eventcard_array', array($this, 'eventcard_array'), 10, 4);
		add_filter('evo_eventcard_adds', array($this, 'eventcard_adds'), 10, 1);

		// scripts and styles 
		add_action( 'init', array( $this, 'register_styles_scripts' ) ,15);	

		$this->opt = $eventon_re->opt;
		$this->opt2 = $eventon_re->opt2;

		// add rsvp form HTML to footer
		add_action('wp_footer', array($this, 'footer_content'));
	}

	// STYLES: for the tab page 
		public function register_styles_scripts(){
			global $eventon_re;			
			wp_register_style( 'evo_RE_styles',$eventon_re->plugin_url.'/assets/RE_styles.css');
			wp_register_script('evo_RE_script',$eventon_re->plugin_url.'/assets/RE_script.js', array('jquery'), $eventon_re->version, true );
			
			$this->print_scripts();
			add_action( 'wp_enqueue_scripts', array($this,'print_styles' ));				
		}
		public function print_scripts(){
			wp_enqueue_script('evo_RE_ease');	
			//wp_enqueue_script('evo_RS_mobile');	
			wp_enqueue_script('evo_RE_script');	
		}
		function print_styles(){
			wp_enqueue_style( 'evo_RE_styles');	
		}

	// Review EVENTCARD form HTML
		// add Review box to front end
			function frontend_box($object, $helpers){
				global $eventon_re;
				$event_pmv = get_post_custom($object->event_id);

				// loggedin user
					$currentUserID = 	$this->functions->get_current_userid();	
					
				// Review enabled for this event
					if(empty($event_pmv['event_review']) || (!empty($event_pmv['event_review']) && $event_pmv['event_review'][0]=='no') ) return;

				$lang = (!empty($eventon->evo_generator->shortcode_args['lang'])? $eventon->evo_generator->shortcode_args['lang']:'L1');

				ob_start();

				echo  "<div class='evorow evcal_evdata_row bordb evcal_evrow_sm evo_metarow_review".$helpers['end_row_class']."' data-review='' data-event_id='".$object->event_id."' data-ri='{$object->__repeatInterval}' data-lang='{$lang}'>
							<span class='evcal_evdata_icons'><i class='fa ".get_eventON_icon('evcal__evore_001', 'fa-star',$helpers['evOPT'] )."'></i></span>
							<div class='evcal_evdata_cell'>							
								<h3 class='evo_h3'>".evo_lang('Event Reviews',$lang,$this->opt2)."</h3>";

					$current_average_rating = $this->functions->get_average_rating($object->event_id,$event_pmv, $object->__repeatInterval );

					// if this event have a current rating and average
					if($current_average_rating):
						echo "<h3 class='evo_h3 orating'>".evo_lang('Overall Rating:',$lang,$this->opt2)." <span class='orating_stars' title='{$current_average_rating}'>".$this->functions->get_star_rating_html($current_average_rating)."</span> <span class='orating_data'>".$this->functions->get_rating_count($object->event_id,$event_pmv, $object->__repeatInterval)." ".evo_lang('Ratings',$lang,$this->opt2)."</span>";
							echo ((!empty($event_pmv['_rating_data']) && $event_pmv['_rating_data'][0]!='yes') || empty($event_pmv['_rating_data']))? "<span class='extra_data' style='margin-left:5px;'>".evo_lang('Data',$lang,$this->opt2)."</span>":'';
						echo "</h3>";

						// additional rating data
						if((!empty($event_pmv['_rating_data']) && $event_pmv['_rating_data'][0]!='yes') || empty($event_pmv['_rating_data'])):
						echo "<div class='rating_data' style='display:none'>";
							$rate_count = $this->functions->get_rating_ind_counts($object->event_id, $object->__repeatInterval,$event_pmv);
							$rate_sum = ($rate_count && is_array($rate_count))? array_sum($rate_count):0;
							for($x=5; $x>0; $x--){
								$width_percentage = round(($rate_count[$x]/$rate_sum)*100);
								echo "<p><span class='rating'>".$this->functions->get_star_rating_html($x)."</span>
									<span class='bar'><em title='{$width_percentage}%' style='width:".($width_percentage)."%'></em></span>
									<span class='count'>".$rate_count[$x]."</span>
								</p>";
							}
						echo "</div>";
						endif;

						// all reviews list
						$reviews_array =  $this->functions->get_all_reviews($object->event_id, $object->__repeatInterval);
						if(!empty($reviews_array) && count($reviews_array)>0):
							echo "<div class='review_list'>";							
								$count = '';
									$count = 1;
									foreach($reviews_array as $review){
										echo "<p class='review ".($count==1?'show':'')."'>
											<span class='rating'>".$this->functions->get_star_rating_html($review['rating'])."</span>";
										echo "<span class='description'>".$review['review']."</span>";
										echo "<span class='reviewer'>".(!empty($review['reviewer'])? $review['reviewer']:'')." on ".$review['date']."</span></p>";
										$count++;
									}					
							echo "</div>";
							if($count>2)
								echo "<div class='review_list_control' data-revs='{$count}'><span class='fa fa-chevron-circle-left' data-dir='prev'></span><span class='fa fa-chevron-circle-right' data-dir='next'></span></div>";
						endif;
					else: // there are no reviews for this event yet
						echo "<h3 class='evo_h3 orating'>".evo_lang('There are no reviews for this event',$lang,$this->opt2)."</h3>";
					endif;

					// write a review button
					if(empty($this->opt['evore_only_logged']) || ($this->opt['evore_only_logged']=='yes' && is_user_logged_in()) || $this->opt['evore_only_logged']=='no'){
						$user_ID = get_current_user_id();
						$user_name = $user_email ='';
						if(!empty($user_ID) && $user_ID && !empty($this->opt['evore_prefil']) && $this->opt['evore_prefil']=='yes' ){
							$user_info = get_userdata($user_ID);
							$user_name = $user_info->display_name;
							$user_email = $user_info->user_email;
						}
						echo "<div class='review_actions'><a class='evcal_btn new_review_btn' data-username='{$user_name}' data-useremail='{$user_email}' data-uid='{$user_ID}' data-eventname='".get_the_title($object->event_id)."'>".evo_lang('Write a Review',$lang,$this->opt2)."</a></div>";
					}

				echo "</div>".$helpers['end'];
				echo "</div>";
				return ob_get_clean();
			}
			
		// footer of review form
			function footer_content(){				
				include_once('html_form.php');
			}
		// save a cookie for Review
			function set_user_cookie($args){
				//$ip =$this->get_client_ip();
				$cookie_name = 'evore_'.$args['email'].'_'.$args['e_id'].'_'.$args['repeat_interval'];
				$cookie_value = 'rated';
				setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/");
			}
			function check_user_cookie($userid, $eventid){
				$cookie_name = 'evore_'.$eventid.'_'.$userid;
				if(!empty($_COOKIE[$cookie_name]) && $_COOKIE[$cookie_name]=='rated'){
					return true;
				}else{
					return false;
				}
			}
		// get form messages html
			function get_form_message($code='', $lang=''){
				$array =  array(
					'err'=>evo_lang('Required fields missing',$lang,$this->opt2),
					'err2'=>evo_lang('Invalid email address',$lang,$this->opt2),
					'err6'=>evo_lang('Invalid Validation code.',$lang,$this->opt2),
					'err7'=>evo_lang('Could not save review please try later.',$lang,$this->opt2),
					'err8'=>evo_lang('You can only submit once for this event.',$lang,$this->opt2),
					'succ'=>evo_lang('Thank you for submitting your review',$lang,$this->opt2),
				);				
				return (!empty($code))? $array[$code]: $array;
			}
			function get_form_msg($opt){
				$str='';
				$ar = array('codes'=> $this->get_form_message());
				return "<div class='evore_msg_' style='display:none'>". json_encode($ar)."</div>";
			}
				
		// add eventon review event card field to filter
			function eventcard_array($array, $pmv, $eventid, $__repeatInterval){
				$array['evore']= array(
					'event_id' => $eventid,
					'__repeatInterval'=>(!empty($__repeatInterval)? $__repeatInterval:0)
				);
				return $array;
			}
			function eventcard_adds($array){
				$array[] = 'evore';
				return $array;
			}

	// SAVE new Review
		function _form_save_review($args){
			global $eventon_re;
			$status = 0;
			
			// add new review
			if($created_review_id = $this->create_post() ){
				
				// save review data								
				if(!empty($args['name']))
					$this->create_custom_fields($created_review_id, 'name', $args['name']);

				if(!empty($args['email']))
					$this->create_custom_fields($created_review_id, 'email', $args['email']);

				if(!empty($args['review']))		
					$this->create_custom_fields($created_review_id, 'review', $args['review']);		

				$this->create_custom_fields($created_review_id, 'rating', $args['rating']); 
				$this->create_custom_fields($created_review_id, 'e_id', $args['e_id']);

				$__repeat_interval = (!empty($args['repeat_interval']))? $args['repeat_interval']: '0';
				$this->create_custom_fields($created_review_id, 'repeat_interval', $__repeat_interval);
				
				// save loggedin user ID if prefill fields for loggedin enabled
					$prefill_enabled = (!empty($this->opt['evore_prefil']) && $this->opt['evore_prefil']=='yes')? true:false;
					$CURRENT_user_id = $this->functions->get_current_userid();
					if( ($CURRENT_user_id && $prefill_enabled) || !empty($args['uid'])){
						// user ID if provided or find loggedin user id
						$CURRENT_user_id = !empty($args['uid'])? $args['uid']: $CURRENT_user_id;
						$this->create_custom_fields($created_review_id, 'userid',$CURRENT_user_id);						
					}

				$args['review_id'] = $created_review_id;

				if(!empty($this->opt['evore_draft']) && $this->opt['evore_draft']=='yes'){}else{
					// SYNC event's rating value
					$this->functions->add_new_rating($args['rating'], $args['e_id'], $__repeat_interval);
					$this->functions->sync_ratings($args['e_id']);
				}
				
				$this->send_email_notif($args);
				$status = $created_review_id;

			}else{	$status = 7; // new rsvp post was not created
			}
		
			return $status;
		}

	// EMAIL function 		
		public function _event_date($pmv, $repeat_interval){
			global $eventon;

			$datetime = new evo_datetime();
			$eventtime = $datetime->get_correct_formatted_event_repeat_time($pmv, $repeat_interval);	
			return $eventtime;	
		}
		// send email confirmation of Review  to submitter
			function get_email_data($args){
				$this->evore_args = $args;

				$email_data = array();

				$from_email = $this->get_from_email();

				$__to_email = (!empty($this->opt['evore_notfiemailto']) )?
					htmlspecialchars_decode ($this->opt['evore_notfiemailto'])
					:get_bloginfo('admin_email');

				$email_data['to'] = $__to_email;			

				if(!empty($email_data['to'])){
					$email_data['subject'] =((!empty($this->opt['evore_notfiesubjest']))? $this->opt['evore_notfiesubjest']: __('New Review Notification','eventon'));
					$filename = 'notification_email';
					$headers = 'From: '.$from_email. "\r\n";
					$headers .= 'Reply-To: '.$args['email']. "\r\n";
					
					$email_data['message'] = $this->_get_email_body($args, $filename);
					$email_data['header'] = $headers;	
					$email_data['from'] = $from_email;	
				}
				return $email_data;
			}

			function send_email_notif($args){				
				if(!empty($this->opt['evore_notif']) && $this->opt['evore_notif']=='yes'){
					global $eventon_re;
					$args['html']= 'yes';
					return $eventon_re->helper->send_email(
						$this->get_email_data($args)
					);
				}
			}
			// return proper from email with name
				function get_from_email(){
					$__from_email = (!empty($this->opt['evore_notfiemailfrom']) )?
						htmlspecialchars_decode ($this->opt['evore_notfiemailfrom'])
						:get_bloginfo('admin_email');
					$__from_email_name = (!empty($this->opt['evore_notfiemailfromN']) )?
						($this->opt['evore_notfiemailfromN'])
						:get_bloginfo('name');
						$from_email = (!empty($__from_email_name))? 
							$__from_email_name.' <'.$__from_email.'>' : $__from_email;
					return $from_email;
				}

		// email body from template file
			function _get_email_body($evore_args, $file){
				global $eventon, $eventon_re;

				$path = $eventon_re->addon_data['plugin_path']."/templates/";
				$args = $evore_args;

				$paths = array(
					0=> TEMPLATEPATH.'/'.$eventon->template_url.'templates/email/reviewer/',
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

	// user review manager
		function user_review_manager(){
			global $eventon_re, $eventon;

			$this->register_styles_scripts();
			add_action('wp_footer', array($this, 'footer_content'));	
			$this->footer_content();		

			// intial variables
			$current_user = get_user_by( 'id', get_current_user_id() );
			$USERID = is_user_logged_in()? get_current_user_id(): false;
			$current_page_link = get_page_link();

			// loading child templates
				$file_name = 'user_review_manager.php';
				$paths = array(
					0=> TEMPLATEPATH.'/'.$eventon->template_url.'rsvp/',
					1=> $eventon_re->plugin_path.'/templates/',
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
				global $eventon_re;
				return $eventon_re->lang($variable, $default_text);
			}
		// function replace event name from string
			function replace_en($string){
				return str_replace('[event-name]', "<span class='eventName'>Event Name</span>", $string);
			}		
		
		function create_post() {			
			$type = 'evo-review';
	        $valid_type = (function_exists('post_type_exists') &&  post_type_exists($type));

	        if (!$valid_type) {
	            $this->log['error']["type-{$type}"] = sprintf(
	                'Unknown post type "%s".', $type);
	        }
	       
	        $title = 'REVIEW '.date('M d Y @ h:i:sa', time());
	        $author = ($this->get_author_id())? $this->get_author_id(): 1;
	        $post_status = (!empty($this->opt['evore_draft']) && $this->opt['evore_draft']=='yes')? 'draft':'publish';

	        $new_post = array(
	            'post_title'   => $title,	            
	            'post_type'    => $type,
	            'post_status'  => $post_status,
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
