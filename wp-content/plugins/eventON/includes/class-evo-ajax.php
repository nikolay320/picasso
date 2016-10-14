<?php
/**
 * EventON Ajax Handlers
 *
 * Handles AJAX requests via wp_ajax hook (both admin and front-end events)
 *
 * @author 		AJDE
 * @category 	Core
 * @package 	EventON/Functions/AJAX
 * @version     2.3.20
 */

class evo_ajax{
	/**
	 * Hook into ajax events
	 */
	public function __construct(){
		$ajax_events = array(
			'ics_download'=>'eventon_ics_download',
			'the_ajax_hook'=>'evcal_ajax_callback',
			'evo_dynamic_css'=>'eventon_dymanic_css',
			'export_events_ics'=>'export_events_ics',
		);
		foreach ( $ajax_events as $ajax_event => $class ) {

			$prepend = ( in_array($ajax_event, array('the_ajax_hook','evo_dynamic_css','the_post_ajax_hook_3','the_post_ajax_hook_2')) )? '': 'eventon_';

			add_action( 'wp_ajax_'. $prepend . $ajax_event, array( $this, $class ) );
			add_action( 'wp_ajax_nopriv_'. $prepend . $ajax_event, array( $this, $class ) );
		}

		add_action('wp_ajax_eventon-feature-event', array($this, 'eventon_feature_event'));
	}

	// OUTPUT: json headers
		private function json_headers() {
			header( 'Content-Type: application/json; charset=utf-8' );
		}

	// for event post repeat intervals 
	// @return converted unix time stamp on UTC timezone
		public function repeat_interval(){
			$date_format = $_POST['date_format'];
		}

	// Primary function to load event data 
		function evcal_ajax_callback(){
			global $eventon;
			$shortcode_args='';
			$status = 'GOOD';

			$evodata = !empty($_POST['evodata'])? $_POST['evodata']: false;
			
			// Initial values
				$current_month = (int)(!empty($evodata['cmonth'])? ($evodata['cmonth']): $_POST['current_month']);
				$current_year = (int)(!empty($evodata['cyear'])? $evodata['cyear']: $_POST['current_year']);	

				$send_unix = (isset($evodata['send_unix']))? $evodata['send_unix']:null;
				$direction = $_POST['direction'];
				$sort_by = (!empty($_POST['sort_by']))? $_POST['sort_by']: 
					( !empty($evodata['sort_by'])? $evodata['sort_by'] :'sort_date');
			
			// generate new UNIX range dates for calendar
				if($send_unix=='1'){
					$focus_start_date_range = (isset($evodata['range_start']))? (int)($evodata['range_start']):null;
					$focus_end_date_range = (isset($evodata['range_end']))? (int)($evodata['range_end']):null;	
					
					$focused_month_num = $current_month;
					$focused_year = $current_year;

				}else{
					if($direction=='none'){
						$focused_month_num = $current_month;
						$focused_year = $current_year;
					}else{
						$focused_month_num = ($direction=='next')?
							(($current_month==12)? 1:$current_month+1):
							(($current_month==1)? 12:$current_month-1);
						
						$focused_year = ($direction=='next')? 
							(($current_month==12)? $current_year+1:$current_year):
							(($current_month==1)? $current_year-1:$current_year);
					}	
					
						
					$focus_start_date_range = mktime( 0,0,0,$focused_month_num,1,$focused_year );
					$time_string = $focused_year.'-'.$focused_month_num.'-1';		
					$focus_end_date_range = mktime(23,59,59,($focused_month_num),(date('t',(strtotime($time_string) ))), ($focused_year));
				}
				
			// base calendar arguments at this stage
				$eve_args = array(
					'focus_start_date_range'=>$focus_start_date_range,
					'focus_end_date_range'=>$focus_end_date_range,
					'sort_by'=>$sort_by,		
					'event_count'=>(!empty($_POST['event_count']))? $_POST['event_count']: 
						( !empty($evodata['ev_cnt'])? $evodata['ev_cnt']: '' ),
					'filters'=>((isset($_POST['filters']))? $_POST['filters']:null)
				);
				//print_r($eve_args);
			
			// shortcode arguments USED to build calendar
				$shortcode_args_arr = $_POST['shortcode'];
				
				if(!empty($shortcode_args_arr) && count($shortcode_args_arr)>0){
					foreach($shortcode_args_arr as $f=>$v){
						$shortcode_args[$f]=$v;
					}
					$eve_args = array_merge($eve_args, $shortcode_args);
					$lang = $shortcode_args_arr['lang'];
				}else{
					$lang ='';
				}
				
					
			// GET calendar header month year values
				$calendar_month_title = get_eventon_cal_title_month($focused_month_num, $focused_year, $lang);
					
			// AJAX Addon hook
				$eve_args = apply_filters('eventon_ajax_arguments',$eve_args, $_POST);

			// Calendar content		
				$EVENTlist = $eventon->evo_generator->evo_get_wp_events_array('', $eve_args, $eve_args['filters']);

				if(!empty($eve_args['sep_month']) && $eve_args['sep_month']=='yes' && $eve_args['number_of_months']>1){
					$content_li = $eventon->evo_generator->separate_eventlist_to_months($EVENTlist, $eve_args['event_count'], $eve_args);
				}else{
					$date_range_events_array = $eventon->evo_generator->generate_event_data( 
						$EVENTlist, 
						$focus_start_date_range,
						$focused_month_num , $focused_year 
					);
					$content_li = $eventon->evo_generator->evo_process_event_list_data($date_range_events_array, $eve_args);
				}
				
			//$content_li = $eventon->evo_generator->eventon_generate_events( $eve_args);

			// Update the events list to remove post meta values to reduce load on AJAX
				$NEWevents = array();
				foreach($EVENTlist as $event_id=>$event){
					unset($event['event_pmv']);
					$NEWevents[$event_id]= $event;
				}

			// RETURN VALUES
			// Array of content for the calendar's AJAX call returned in JSON format
				$return_content = array(
					'status'=>(!$evodata? 'Need updated':$status),
					'eventList'=>$NEWevents,
					'content'=>$content_li,
					'cal_month_title'=>$calendar_month_title,
					'month'=>$focused_month_num,
					'year'=>$focused_year,
					'focus_start_date_range'=>$focus_start_date_range,
					'focus_end_date_range'=>$focus_end_date_range,		
				);			
			
			
			echo json_encode($return_content);
			exit;
		}

	// ICS file generation for add to calendar buttons
		function eventon_ics_download(){
			$event_id = (int)($_GET['event_id']);
			$sunix = (int)($_GET['sunix']);
			$eunix = (int)($_GET['eunix']);

			//error_reporting(E_ALL);
			//ini_set('display_errors', '1');
			
			//$the_event = get_post($event_id);
			$ev_vals = get_post_custom($event_id);
			
			$event_start_unix = $sunix;
			$event_end_unix = (!empty($eunix))? $eunix : $sunix;
			
			
			$name = $summary = htmlspecialchars_decode(get_the_title($event_id));

			// summary for ICS file
			$event = get_post($event_id);
			if(empty($event)) return false;
			
			$content = (!empty($event->post_content))? $event->post_content:'';
			if(!empty($content)){
				$content = strip_tags($content);
				$content = str_replace(']]>', ']]&gt;', $content);
				$summary = wp_trim_words($content, 50, '[..]');
				//$summary = substr($content, 0, 500).' [..]';
			}			
			
			
			$location_name = (!empty($ev_vals['evcal_location_name']))? $ev_vals['evcal_location_name'][0] : ''; 
			$location = (!empty($ev_vals['evcal_location']))? $location_name.' - '.$ev_vals['evcal_location'][0] : ''; 
				$location = $this->esc_ical_text($location);
			$start = evo_get_adjusted_utc($event_start_unix);
			$end = evo_get_adjusted_utc($event_end_unix);
			$uid = uniqid();
			//$description = $the_event->post_content;
			
			//ob_clean();
			
			//$slug = strtolower(str_replace(array(' ', "'", '.'), array('_', '', ''), $name));
			$slug = $event->post_name;
			
			
			header("Content-Type: text/Calendar; charset=utf-8");
			header("Content-Disposition: inline; filename={$slug}.ics");
			echo "BEGIN:VCALENDAR\n";
			echo "VERSION:2.0\n";
			echo "PRODID:-//eventon.com NONSGML v1.0//EN\n";
			//echo "METHOD:REQUEST\n"; // requied by Outlook
			echo "BEGIN:VEVENT\n";
			echo "UID:{$uid}\n"; // required by Outlok
			echo "DTSTAMP:".date_i18n('Ymd').'T'.date_i18n('His')."\n"; // required by Outlook
			echo "DTSTART:{$start}\n"; 
			echo "DTEND:{$end}\n";
			echo "LOCATION:{$location}\n";
			echo "SUMMARY:{$name}\n";
			echo "DESCRIPTION: ".$this->esc_ical_text($summary)."\n";
			echo "END:VEVENT\n";
			echo "END:VCALENDAR";
			exit;
		}
		function esc_ical_text( $text='' ) {
		    $text = str_replace("\\", "", $text);
		    $text = str_replace("\r", "\r\n ", $text);
		    $text = str_replace("\n", "\r\n ", $text);
		    $text = str_replace(",", "\, ", $text);
		    $text = htmlspecialchars_decode($text);
		    return $text;
		}

	// download all event data as ICS
		function export_events_ics(){
			global $eventon;

			if(!wp_verify_nonce($_REQUEST['nonce'], 'eventon_download_events')) die('Nonce Security Failed.');

			$events = $eventon->evo_generator->get_all_event_data();
			if(!empty($events)):
				$slug = 'eventon_events';
				header("Content-Type: text/Calendar; charset=utf-8");
				header("Content-Disposition: inline; filename={$slug}.ics");
				echo "BEGIN:VCALENDAR\n";
				echo "VERSION:2.0\n";
				echo "PRODID:-//eventon.com NONSGML v1.0//EN\n";
				echo "CALSCALE:GREGORIAN\n";
				echo "METHOD:PUBLISH\n";

				foreach($events as $event_id=>$event){
					$location = $summary = '';

					if(!empty($event['details'])){
						$summary = wp_trim_words($event['details'], 50, '[..]');
					}

					$location_name = (!empty($event['location_name']))? $event['location_name'] : ''; 
					$location = (!empty($event['location_address']))? $location_name.' - '.$event['location_address'] : ''; 

					$uid = uniqid();
					echo "BEGIN:VEVENT\n";
					echo "UID:{$uid}\n"; // required by Outlok
					echo "DTSTAMP:".date_i18n('Ymd').'T'.date_i18n('His')."\n"; // required by Outlook
					echo "DTSTART:" . evo_get_adjusted_utc($event['start']) ."\n"; 
					echo "DTEND:" . evo_get_adjusted_utc($event['end']) ."\n";
					if(!empty($location)) echo "LOCATION:". $this->esc_ical_text($location) ."\n";
					echo "SUMMARY:".htmlspecialchars_decode($event['name'])."\n";
					if(!empty($summary)) echo "DESCRIPTION: ".$this->esc_ical_text($summary)."\n";
					echo "END:VEVENT\n";

					// repeating instances
						if(!empty($event['repeats']) && is_array($event['repeats'])){
							foreach( $event['repeats'] as $interval=>$repeats){
								if($interval==0) continue;

								$uid = uniqid();
								echo "BEGIN:VEVENT\n";
								echo "UID:{$uid}\n"; // required by Outlok
								echo "DTSTAMP:".date_i18n('Ymd').'T'.date_i18n('His')."\n"; // required by Outlook
								echo "DTSTART:" . evo_get_adjusted_utc($repeats[0]) ."\n"; 
								echo "DTEND:" . evo_get_adjusted_utc($repeats[1]) ."\n";
								if(!empty($location)) echo "LOCATION:". $this->esc_ical_text($location) ."\n";
								echo "SUMMARY:".htmlspecialchars_decode($event['name'])."\n";
								if(!empty($summary)) echo "DESCRIPTION: ".$this->esc_ical_text($summary)."\n";
								echo "END:VEVENT\n";
							}
						}

				}
				echo "END:VCALENDAR";

			endif;
		}

	/* dynamic styles */
		function eventon_dymanic_css(){
			//global $foodpress_menus;
			require('admin/inline-styles.php');
			exit;
		}

}
new evo_ajax();