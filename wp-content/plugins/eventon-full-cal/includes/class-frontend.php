<?php
/**
 * FullCal front-end
 * @version 	1.1
 */

class evofc_frontend{

	public $print_scripts_on;
	public $day_names = array();
	public $focus_day_data= array();
	public $shortcode_args;

	public $is_running_fc =false;

	function __construct(){
		// scripts and styles 
		add_action( 'init', array( $this, 'register_styles_scripts' ) ,15);	
		add_action( 'wp_footer', array( $this, 'print_fc_scripts' ) ,15);

		// inclusion
		add_action('eventon_calendar_header_content',array($this, 'calendar_header_hook'), 10, 1);
		add_filter('eventon_cal_jqdata',array($this,'add_evoFC_to_evoData'), 10, 1);

		// Widget		
		add_action( 'widgets_init', array( $this, 'register_widgets' ) );			

		// others
		add_action('evo_cal_footer',array($this, 'calendar_footer'), 10);
	}

	// if the calendar is hidden
		function evo_cal_hidden(){
			if(function_exists('evo_cal_hidden')){	return (evo_cal_hidden())? true: false;	}
			return ( !empty($eventon->frontend->evo_options['evcal_cal_hide']) && $eventon->frontend->evo_options['evcal_cal_hide']=='yes')? true: false;
		}

	// styles for fullCal	
		public function register_styles_scripts(){
			global $eventon_fc;
			
			wp_register_style( 'evo_fc_styles',$eventon_fc->addon_data['plugin_url'].'/assets/fc_styles.css');
			wp_register_script('evo_fc_ease',$eventon_fc->addon_data['plugin_url'].'/assets/jquery.easing.1.3.js', array('jquery'), $eventon_fc->version, true );
			wp_register_script('evo_fc_mobile',$eventon_fc->addon_data['plugin_url'].'/assets/jquery.mobile.min.js', array('jquery'), $eventon_fc->version, true );
			wp_register_script('evo_fc_script',$eventon_fc->addon_data['plugin_url'].'/assets/fc_script.js', array('jquery'), $eventon_fc->version, true );	

			if(has_eventon_shortcode('add_eventon_fc')){
				// LOAD JS files
				$this->print_scripts_();
					
			}
			add_action( 'wp_enqueue_scripts', array($this,'print_styles' ));
				
		}
		public function print_scripts_(){
			wp_enqueue_script('evo_fc_ease');	
			wp_enqueue_script('evo_fc_mobile');	
			wp_enqueue_script('evo_fc_script');	
		}

		function print_styles(){
			wp_enqueue_style( 'evo_fc_styles');	
		}
		function print_fc_scripts(){	
			if(!$this->print_scripts_on)
				return;

			$this->print_scripts_();
		}

	// add full cal hidden field to calendar header
		function calendar_header_hook($content){
			global $eventon_fc;
			// check if full cal is running on this calendar
			
			if($eventon_fc->is_running_fc){			
				
				$_cal_focus_day = (!empty($this->focus_day_data['mo1st']) && $this->focus_day_data['mo1st'] =='yes')? 1:$this->focus_day_data['day'];

				// Move to first of month class
				$mo1st_class = (!empty($this->focus_day_data['mo1st']) && $this->focus_day_data['mo1st'] =='yes')? ' mo1st':null;
				

				$day_data = $this->focus_day_data;
				$add = "<input type='hidden' class='eventon_other_vals{$mo1st_class} evoFC_val' name='fc_focus_day' value='".$_cal_focus_day."'/>";
				
				echo $add;
			}else{
				wp_dequeue_script('evo_fc_script');
			}
		}


	/**	MAIN Function to generate the calendar outter shell	for full calendar */
		public function generate_eventon_fc_calendar($args, $type=''){
			global $eventon, $wpdb;	

			$this->front_end_init($args, $type);
			$this->only_fc_actions();


			$fdd = $this->focus_day_data;
			
			ob_start();

				if($type!='listOnly')
					echo $eventon->evo_generator->get_calendar_header(array(
						'focused_month_num'=>$fdd['month'], 
						'focused_year'=>$fdd['year']
						)
					);
				
				// calendar events
				$months_event_array = $eventon->evo_generator->generate_event_data( 
					$this->events_list, 
					$fdd['focus_start_date_range']
				);

				echo $eventon->evo_generator->evo_process_event_list_data($months_event_array, $args);

				if($type!='listOnly')
					echo $eventon->evo_generator->calendar_shell_footer($args);

			$this->remove_only_fc_actions();

			$content = ob_get_clean();

			return  $content;		
			
		}

		// initiate all required for front end
			function front_end_init($args, $type=''){
				global $eventon, $wpdb;	

				$this->is_running_fc=true;

				// call styles for PHP
					if($type=='php')
						$this->print_scripts_();

				// GET anu preset values for date
					$month_incre = (!empty($args['month_incre']))?$args['month_incre']:0;
					$day_incre = (!empty($args['day_incre']))?$args['day_incre']:0;				
				
				//	DATE - for the calendar to focus on
					$current_timestamp =  current_time('timestamp');
					if($day_incre!=0){
						$today_day = date('j',$current_timestamp);
						$today_day= ((int)$today_day)+ (int)$day_incre;
					}else{
						$today_day = date('j',$current_timestamp);
					}
					
					$focused_day=( !empty($args['fixed_day']) && $args['fixed_day']!=0 )? $args['fixed_day']: $today_day;
					$mo1st=( !empty($args['mo1st']) )? $args['mo1st']: '';
								
				// MONTH & YEAR
					$focused_month_num = (!empty($args['fixed_month']))?
						$args['fixed_month']:
						date('n', strtotime($month_incre.' month', $current_timestamp) );
						
					$focused_year = (!empty($args['fixed_year']))?
						$args['fixed_year']:
						date('Y', strtotime($month_incre.' month', $current_timestamp) );
					
					// load entire month of events on load or not
					if(!empty($args['load_fullmonth']) && $args['load_fullmonth']=='yes'){
						$end_day = $this->days_in_month( $focused_month_num, $focused_year);
						$start_day = 1;					
					}else{
						$start_day = $end_day = $focused_day;
					}
					// DAY RANGES
					$focus_start_date_range = mktime( 0,0,0,$focused_month_num,$start_day,$focused_year );
					$focus_end_date_range = mktime(23,59,59,($focused_month_num),$end_day, ($focused_year));
								
				// Set focus day data within the class
					$this->focus_day_data = array(
						'day'=>$focused_day,
						'month'=>$focused_month_num,
						'year'=>$focused_year,
						'mo1st'=>$mo1st,
						'focus_start_date_range'=>$focus_start_date_range,
						'focus_end_date_range'=>$focus_end_date_range,
						'cal_id'=>((!empty($args['cal_id']))? $args['cal_id']:'1'),
						'grid_ux'=>( !empty($args['grid_ux'])? $args['grid_ux']: 0)
					);
				
				// Add extra arguments to shortcode arguments
					$new_arguments = array(
						'focus_start_date_range'=>$this->focus_day_data['focus_start_date_range'],
						'focus_end_date_range'=>$this->focus_day_data['focus_end_date_range'],
					);
					

				$args = (!empty($args) && is_array($args))? array_merge($args, $new_arguments): $new_arguments;


				$args__ = $eventon->evo_generator->process_arguments($args);
				$this->shortcode_args=$args__;

				
				$this->events_list = $eventon->evo_generator->evo_get_wp_events_array('', $args__);			
			}

	// month grid including the day names
		function get_grid_month($date, $month, $year, $start_of_week, $filters='', $init='', $shortcode=''
		){
			
			$lang = (!empty($shortcode['lang']))? $shortcode['lang']: null;

			$this->set_three_letter_day_names($lang);

			$content ="<div class='evofc_month ".( (!empty($init) && $init=='1')? 'focus':null)."' month='".$month."'>
				<div class='eventon_fc_daynames'>";			
				for($t=1; $t<8; $t++){
				
					$start_of_week = ($start_of_week>7)?$start_of_week-7: 
						( ($start_of_week==0)?7: $start_of_week );
					
					$dow = $start_of_week;
					
					$content.="<p class='evo_fc_day' data-dow='{$dow}'>".$this->day_names[$start_of_week]."</p>";
					$start_of_week++;
				}				
				$content.="<div class='clear'></div>
			</div>
			<div class='eventon_fc_days'>";		
				$content .= $this->get_full_cal_view($date,$month, $year, $filters, $shortcode);
				$content .="</div></div>";
			
			return $content;
		}
	
	//	Function to OUTPUT the full cal view	
		function get_full_cal_view($day, $month, $year, $filters='', $shortcode=''){
			global $eventon;

			$number_days_in_month = $this->days_in_month( $month, $year);	
			
			// calculate date range for the calendar
				date_default_timezone_set('UTC');
				$focus_month_beg_range = mktime( 0,0,0,$month,1,$year );
				$focus_month_end_range = mktime( 23,59,59,$month,$number_days_in_month,$year );
			
			// GET GENERAL shortcode arguments if set ELSE class-wide
				$shortcode_args = (!empty($shortcode))? $shortcode: $this->shortcode_args;
			
			// Update date range to shortcode arguments
				$updated_shortcode_args['focus_start_date_range'] = $focus_month_beg_range;
				$updated_shortcode_args['focus_end_date_range'] = $focus_month_end_range;
				$updated_shortcode_args = array_merge($shortcode_args, $updated_shortcode_args);

			// get Events array
				$event_list_array = $eventon->evo_generator->evo_get_wp_events_array('',$updated_shortcode_args, $filters );			

			// build a month array with days that have events
			$date_with_events= $days_w_e = array();
			
			if(is_array($event_list_array) && count($event_list_array)>0){
				
				foreach($event_list_array as $event){				
					
					// check for all year event
					$_is_all_year = (!empty($event['event_pmv']['evo_year_long']) && $event['event_pmv']['evo_year_long'][0]=='yes')? true:false;
					
					$__duration='';
					$__dur_type ='';

					if($_is_all_year){
						$__duration= $number_days_in_month;
						$start_date = 1;
					}else{
						$start_date = (int)(date('j',$event['event_start_unix']));
						$start_month = (int)(date('n',$event['event_start_unix']));
						
						$end_date = (int)(date('j',$event['event_end_unix']));
						$end_month = (int)(date('n',$event['event_end_unix']));

						// same month
						if($start_month == $end_month){
							// same date
							if($start_date == $end_date){
								$__no_events = (!empty($date_with_events[$start_date]))?
									$date_with_events[$start_date]:0;
								
								$date_with_events[$start_date] = $__no_events+1;

								//$days_w_e[$start_date]['count'] = $__no_events+1;
								$days_w_e[$start_date]['et'][] = $event['event_title'];
								$days_w_e[$start_date]['ec'][] = (!empty($event['event_pmv']['evcal_event_color'])? $event['event_pmv']['evcal_event_color']:'');
								
							}else if($start_date<$end_date){
							// different date
								$__duration = $end_date - $start_date+1;				
							}
						}else{
							// different month
							// start on this month
							if($start_month == $month){						
								$__duration = $number_days_in_month - $start_date+1;
								$__dur_type = ($__duration==0)? 'eom':'';
							}else{

								if( $end_month != $month){
									// end on next month & start month is before this month
									$start_date=1;
									$__duration = $number_days_in_month;
								}else{
									// start on a past month & end this month
									$start_date=1;
									$__duration = ($end_date==1)? 1: $end_date;
								}
							}
						}		
					}
					
					// run multi-day
					if(!empty($__duration) || $__dur_type=='eom'){
						$__duration = ($__duration==0 && $__dur_type=='eom')? 1: $__duration;
						for($x=0; $x<$__duration; $x++){
							if( $number_days_in_month >= ($start_date+$x) ){

								$__this_date = (int)$start_date+($x);

								// events on this day
								$__no_events = (!empty($date_with_events[$__this_date]))?
								$date_with_events[$__this_date]:0;
								
								$date_with_events[$__this_date] = $__no_events+1;
								$days_w_e[$__this_date]['et'][] = $event['event_title'];
								$days_w_e[$__this_date]['ec'][] = (!empty($event['event_pmv']['evcal_event_color'])? $event['event_pmv']['evcal_event_color']:'');
							}
						}
					}					
				}	
			}	
			
			//print_r($date_with_events);
			//print_r($days_w_e);
			
			$start_of_week = get_option('start_of_week');
			
			//ob_start();
			$_box_count=1;
			$output='';
			for($x=0; $x<$number_days_in_month; $x++){

				$__class_attr = array();

				$day_of_week = date('N',strtotime($year.'-'.$month.'-'.($x+1)));
				
				if(is_array($date_with_events) && count($date_with_events)>0){
					$days_with_events_class = (array_key_exists($x+1, $date_with_events))?
						' has_events':null;
				}else{
					$days_with_events_class=null;
				}
				
				if($x==0){
					//echo $day_of_week.' '.$start_of_week.' '.$month.' '.$x;
					$boxes = ( $day_of_week < $start_of_week)? 
						((7-$start_of_week) +$day_of_week): ($day_of_week- $start_of_week);
					
					if($day_of_week != $start_of_week && $boxes!=7){
						for($y=0; $y<( $boxes );$y++){
							$output .= "<p class='evo_fc_day evo_fc_empty' data-cnt='{$_box_count}'>-</p>";
							$_box_count++;
						}
					}
				}
				
				// get number of events per this date
				$__events = (!empty($date_with_events[$x+1]))? 'data-events="'.$date_with_events[$x+1].'"':null;
				/*$__events_x = (!empty($date_with_events[$x+1]))? '<em>'.$date_with_events[$x+1].'</em>':null;*/
				$__events_x='';
				
				// HTML for the day box
				$__class_attr[] = ($day==($x+1))?' on_focus':null;
				$day_attr = $x+1;

					// last day of month
					($x==($number_days_in_month-1))? $__class_attr[]='br': null;
					//last 7 days
					($x>=($number_days_in_month-7))? $__class_attr[]='bb': null;

				// json array
				$json = (array_key_exists($x+1, $days_w_e))? json_encode($days_w_e[$x+1]): null;

				$output.= "<p class='evo_fc_day{$days_with_events_class}". implode(' ', $__class_attr) ."' data-dow='{$day_of_week}' {$__events} data-day='{$day_attr}' data-cnt='{$_box_count}' data-ed='".$json."' data-color=''>".($x+1).$__events_x."</p>";
				
				$_box_count++;
			}
			$output.= "<div class='clear'></div>";
			
			return $output;
			//return ob_get_clean();
		}

	//	create the content for the full cal grids	 
		function content_below_sortbar_this($content, $args=''){
			
			// check if full cal is running on this calendar
			if(!$this->is_running_fc)
				return;
			
			$this->set_three_letter_day_names();


			$day_data = $this->focus_day_data;
			$evcal_val1= get_option('evcal_options_evcal_1');
			
			$start_of_week = get_option('start_of_week');
			
			$hide_arrows = ($evcal_val1['evcal_arrow_hide']=='yes')? true:false;
			$heat = !empty($args['heat'])? $args['heat']: 'no';
			$style = (!empty($args['style']) && $args['style']=='nobox')? 'nobox': '';

			$moretext = evo_lang_get('evo_lang_more','MORE');
					
			$content.="
			<div class='eventon_fullcal' cal_id='{$day_data['cal_id']}' data-hover='".(!empty($args['hover'])?$args['hover']:'no')."'>
				
				<div class='evoFC_tip' style='display:none'></div>
				<div class='evofc_title_tip' style='display:none' data-txt='{$moretext}'>
					<span class='evofc_ttle_cnt'>3</span>
					<ul class='evofc_ttle_events'>
						<li><b style='background-color:#FBAD61'></b>Super event man</li>
					</ul>
				</div>
				<div class='evofc_months_strip ".( $style)."' data-multiplier='0' data-color='".($evcal_val1['evcal_hexcode']? $evcal_val1['evcal_hexcode']:'206177')."' data-heat='{$heat}'>";
					
					$content.= $this->get_grid_month($day_data['day'],$day_data['month'], $day_data['year'], $start_of_week, '', '1');
					
					$content.="
				</div><div class='clear'></div>
			</div>";
			
			echo $content;		
			
			// Stop this from being getting hooked into other calendars on the same page
			remove_action('eventon_after_loadbar',array( $this, 'content_below_sortbar_this' ));
		}

	// other supported functions
		function calendar_footer(){
			global $eventon_fc;
			$eventon_fc->is_running_fc=false;
		}
		// add fullcal only variable values to evoData div in the calendar
		function add_evoFC_to_evoData($array){
			if(!empty($this->focus_day_data)){
				$focusDay = $this->focus_day_data;

				$array['grid_ux'] = $focusDay['grid_ux'];
			}
			return $array;
		}
		public function only_fc_actions(){
			add_filter('eventon_cal_class', array($this, 'eventon_cal_class'), 10, 1);		
			add_action('eventon_after_loadbar',array( $this, 'content_below_sortbar_this' ), 10,2);

		}
		public function remove_only_fc_actions(){
			//add_filter('eventon_cal_class', array($this, 'remove_eventon_cal_class'), 10, 1);	
			remove_filter('eventon_cal_class', array($this, 'eventon_cal_class'));
			
		}
		// add class name to calendar header for EM
		function eventon_cal_class($name){

			if(!empty($this->shortcode_args['nexttogrid']) && $this->shortcode_args['nexttogrid']=='yes' && $this->shortcode_args['grid_ux']==0)
				$name[]='evoFC_nextto';

			$name[]='evoFC';
			return $name;
		}
		// remove class name to calendar header for EM
		function remove_eventon_cal_class($name){
			if(($key = array_search('evoFC', $name)) !== false) unset($name[$key]);
			if(($key = array_search('evoFC_nextto', $name)) !== false) unset($name[$key]);

			return $name;
		}
		// three letter day array
		function set_three_letter_day_names($lang=''){			
			// Build 3 letter day name array to use in the fullcal from custom language
			for($x=1; $x<8; $x++){			
				$evcal_day_is[$x] =eventon_return_timely_names_('day_num_to_name',$x, 'three', $lang);
				//print_r($evcal_day_is[$x]);
				
			}				
			$this->day_names = $evcal_day_is;
		}
		function days_in_month($month, $year) { 
			return date('t', mktime(0, 0, 0, $month+1, 0, $year)); 
		}


	/** register_widgets function.	 */
		function register_widgets() {
			global $eventon_fc;
			// Include - no need to use autoload as WP loads them anyway
			include_once( $eventon_fc->addon_data['plugin_path'].'/includes/class-evo-fc-widget.php' );
			
			// Register widgets
			register_widget( 'evoFC_Widget' );
		}
}
