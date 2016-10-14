<?php
/**
 * Weeklyview Front-end
 * @version 0.1
 * @updated 2015-7
 */
class evowv_frontend{
	//public $is_running_wv = false;
	public $events_list;		
	public $current_time;		
	public $start_of_week;
	public $focus_data = array(); // updated data for focus time
	private $shortcode_args;

	public function __construct(){
		add_action( 'init', array( $this, 'register_styles_scripts' ) ,15);
		add_action( 'wp_footer', array( $this, 'print_wv_scripts' ) ,15);

		// inclusion
		add_action('eventon_calendar_header_content',array($this, 'calendar_header_hook'), 10);
		add_filter('eventon_cal_jqdata',array($this,'add_evoWV_to_evoData'), 10, 1);

		// others
		add_action('evo_cal_footer',array($this, 'calendar_footer'), 10);

		$this->current_time = current_time('timestamp');
		$this->start_of_week = get_option('start_of_week');
	}

	//	Styles for the tab page	 */	
		public function register_styles_scripts(){		
			global $eventon_wv;
			wp_register_style( 'evo_wv_styles',$eventon_wv->addon_data['plugin_url'].'/assets/wv_styles.css');
			wp_register_script('evo_wv_ease',$eventon_wv->addon_data['plugin_url'].'/assets/jquery.easing.1.3.js', array('jquery'), 1.0, true );
			wp_register_script('evo_wv_mobile',$eventon_wv->addon_data['plugin_url'].'/assets/jquery.mobile.min.js', array('jquery'), 1.0, true );
			wp_register_script('evo_wv_script',$eventon_wv->addon_data['plugin_url'].'/assets/wv_script.js', array('jquery'), $eventon_wv->version, true );	

			if(has_eventon_shortcode('add_eventon_wv')){
				// LOAD JS files
				$this->print_scripts();					
			}
			add_action( 'wp_enqueue_scripts', array($this,'print_styles' ));				
		}
		public function print_scripts(){	
			wp_enqueue_script('evo_wv_ease');	
			wp_enqueue_script('evo_wv_mobile');	
			wp_enqueue_script('evo_wv_script');	
		}

		function print_styles(){
			wp_enqueue_style( 'evo_wv_styles');	
		}
		function print_wv_scripts(){
			global $eventon_wv;
			if($eventon_wv->is_running_wv)
				return;

			$this->print_scripts();
			$eventon_wv->is_running_wv=false;
		}

	// add WV hidden fields to calendar header
		function calendar_header_hook(){
			global $eventon_wv;
			// check if weekly cal is running on this calendar			
			if($eventon_wv->is_running_wv){					
				$day_data = $this->focus_data;
				$add = "<input type='hidden' class='eventon_other_vals evoWV_other_val' name='wv_focus_week' value='".$day_data['focus_week']."'/>";				
				
				$ALWAYSFIRST = (!empty($this->shortcode_args['always_first_week']) && $this->shortcode_args['always_first_week']=='yes')?'yes':'no';
				//$add .= "<input type='hidden' class='eventon_other_vals always_first_week' name='always_first_week' value='".$ALWAYSFIRST."'/>";
				
				echo $add;

				$this->print_scripts();
			}else{
				wp_dequeue_script('evo_wv_script');
			}
		}

	//	MAIN Function to generate the calendar outter shell	for weekly view */
		public function generate_eventon_wv_calendar($args, $type=''){
			global $eventon;
			
			// process initial arguments for the calendar			
			$this->front_end_init($args, $type);
			$this->only_wv_actions();	
				
			$fwd = $this->focus_data;

			ob_start();

			if($type!='listOnly')
				echo $eventon->evo_generator->get_calendar_header(array(
					'focused_month_num'=>$fwd['month'], 
					'focused_year'=>$fwd['year']
					)
				);
			
			// calendar events
			$months_event_array = $eventon->evo_generator->generate_event_data( 
				$this->events_list, 
				$fwd['focus_start_date_range']
			);
			
			//print_r($args);
			echo $eventon->evo_generator->evo_process_event_list_data($months_event_array, $args);

			if($type!='listOnly')
				echo $eventon->evo_generator->calendar_shell_footer();

			$this->remove_only_wv_actions();
			$content = ob_get_clean();
			return  $content;
		}
	// INIT: Front End initiation only for WV calendar
		public function front_end_init($args, $type=''){
			global $eventon;

			$this->is_running_wv=true;

			// shortcode arguments set
			$this->shortcode_args = $args;

			// call styles for PHP
				if($type=='php')
					$this->print_scripts();
			// increments for focus month adjusting
				$month_incre = (!empty($args['month_incre']))?$args['month_incre']:0;
				$day_incre = (!empty($args['day_incre']))?$args['day_incre']:0;				

			//	DATE - for the calendar to focus on			
				$current_timestamp =  $this->current_time;

				if($day_incre!=0){
					$today_day = date('j',$current_timestamp);
					$today_day= ((int)$today_day)+ (int)$day_incre;
				}else{
					$today_day = date('j',$current_timestamp);
				}
				
				$focused_day=( !empty($args['fixed_day']) && $args['fixed_day']!=0 )? $args['fixed_day']: $today_day;
				// move to first of month on load
				$mo1st=( !empty($args['mo1st']) )? $args['mo1st']: '';
				

			// MONTH & YEAR					
				$focused_month_num = (!empty($args['fixed_month']))?
					$args['fixed_month']:
					date('n', strtotime($month_incre.' month', $current_timestamp) );
					
				$focused_year = (!empty($args['fixed_year']))?
					$args['fixed_year']:
					date('Y', strtotime($month_incre.' month', $current_timestamp) );

				$number_days_in_month = $this->days_in_month( $focused_month_num, $focused_year);
			
			// get the focus week
				$focus_week = (!empty($args['focus_week']) && $args['focus_week']!=0)?
					$args['focus_week']:
					'';
				$focus_week_array = $this->get_focused_week($focus_week, $focused_day, $focused_month_num, $focused_year);	

			// INITIAL SET calendar data globally within this class	
				$new_focus_data = array(
					'day'=>$focused_day, 
					'month'=>$focused_month_num,
					'year'=>$focused_year,
					'mo1st'=>$mo1st,
					'cal_id'=>((!empty($args['cal_id']))? $args['cal_id']:'1'),
					'number_days_in_month'=>$number_days_in_month,
				);
				// merge with existing values
				$this->focus_data = !empty($this->focus_data)? 
					array_merge($this->focus_data, $new_focus_data):
					$new_focus_data;
			
			// GET week date range based on focus week information
				$this->get_cal_date_range($focus_week_array);

			// Add extra arguments to shortcode arguments
				$new_arguments = array(
					'focus_start_date_range'=>$this->focus_data['focus_start_date_range'],
					'focus_end_date_range'=>$this->focus_data['focus_end_date_range'],
				);
			
			// process arguments to eventon class
				$args = (!empty($args) && is_array($args))? array_merge($args, $new_arguments): $new_arguments;	

				$args__ = $eventon->evo_generator->process_arguments($args, true);
				$this->shortcode_args=$args__;

				$this->events_list = $eventon->evo_generator->evo_get_wp_events_array('', $args__);
		}
	// RETURN month grid including the day names
		function get_grid_week($date, $month, $year, $filters='', $init='', $shortcode='', $week =''){
			
			$lang = (!empty($shortcode['lang']))? $shortcode['lang']: null;
			$this->set_three_letter_day_names($lang);

			$content ="
			<div class='eventon_wv_days'>";		
				$content .= $this->get_week_cal_view($date,$month, $year, $filters, $shortcode, $week);
				$content .="</div>";
			
			return $content;
		}	

	//	OUTPUT week grid inside content */
		function get_week_cal_view($day, $month, $year, $filters='', $shortcode='', $week=''){
			global $eventon;

			$start_of_week = $this->start_of_week;			

			// if focus week data is empty fill that in - for AJAX
			if(empty($this->focus_data)){
				$dim = $this->days_in_month($month, $year);
				$this->create_focus_week_data(array(
					'day'=>$day,
					'month'=>$month,
					'year'=>$year, 
					'number_days_in_month'=>$dim
				));

				// get focus week if not passed
				$week = $this->get_focused_week($week);
				$focus_date = $this->focus_data;
			}else{
				$focus_date = $this->focus_data;
			}

			// we are getting events for entire month for days list
				$focus_start_date_range = mktime( 0,0,0,$month,1,$year );
				$focus_end_date_range = mktime( 23,59,59,$month,$focus_date['number_days_in_month'],$year );
			
			// GET GENERAL shortcode arguments if set class-wide
				$shortcode_args = (!empty($shortcode))? $shortcode: $this->shortcode_args;

			// Update date range to shortcode arguments
				$updated_shortcode_args['focus_start_date_range'] = $focus_start_date_range;
				$updated_shortcode_args['focus_end_date_range'] = $focus_end_date_range;
				$updated_shortcode_args = array_merge($shortcode_args, $updated_shortcode_args);
			
			// get Events array
				$event_list_array = $eventon->evo_generator->evo_get_wp_events_array('',$updated_shortcode_args, $filters );

			
			// build a month array with days that have events
			$date_with_events= $days_w_e = array();
			if(is_array($event_list_array) && count($event_list_array)>0){
				
				foreach($event_list_array as $event){
					
					$start_date = (int)(date('j',$event['event_start_unix']));
					$start_month = (int)(date('n',$event['event_start_unix']));
					
					$end_date = (int)(date('j',$event['event_end_unix']));
					$end_month = (int)(date('n',$event['event_end_unix']));
					
					
					$__duration='';
					$__dur_type ='';
					// same month
					if($start_month == $end_month){
						// same date
						if($start_date == $end_date){
							$__no_events = (!empty($date_with_events[$start_date]))?
								$date_with_events[$start_date]:0;
							
							$date_with_events[$start_date] = $__no_events+1;

							//$days_w_e[$start_date]['count'] = $__no_events+1;
							$days_w_e[$start_date]['et'][] = $event['event_title'];
							
						}else if($start_date<$end_date){
						// different date
							$__duration = $end_date - $start_date+1;						
						}
					}else{
						// different month
						// start on this month
						if($start_month == $month){						
							$__duration = $focus_date['number_days_in_month'] - $start_date+1;
							$__dur_type = ($__duration==0)? 'eom':'';

						}else{

							if( $end_month != $month){
								// end on next month
								$start_date=1;
								$__duration = $focus_date['number_days_in_month'];
								
							}else{
								// start on a past month
								$start_date=1;
								$__duration = ($end_date==1)? 1: $end_date;

							}							
						}
					}
					

					// run multi-day
					if(!empty($__duration) || $__dur_type=='eom'){

						$__duration = ($__duration==0 && $__dur_type=='eom')? 1: $__duration;
						for($x=0; $x<$__duration; $x++){

							if( $focus_date['number_days_in_month'] >= ($start_date+$x) ){
								
								$__this_date = $start_date+($x);

								// events on this day
								$__no_events = (!empty($date_with_events[$__this_date]))?
								$date_with_events[$__this_date]:0;
								
								$date_with_events[$__this_date] = $__no_events+1;
								$days_w_e[$__this_date]['et'][] = $event['event_title'];
							}
						}
					}					
				}							
			}		

			// initial variables
				$_box_count=1;
				$output='';
				$week_of_month = 1;
				$focus_week = false;
				$focus_week_count =0;
				$days_before= 0;
				$focused_week =1;

			// get week number count
			$week_num_for_first_of_month = date('W', mktime(0,0,0,$month, 1, $year));
			$week_num_for_focus_of_month = date('W', mktime(0,0,0,$month, $day, $year));

			$focused_week_of_the_month = $week_num_for_focus_of_month-$week_num_for_first_of_month+1;

			
			for($x=0; $x<$focus_date['number_days_in_month']; $x++){

				$__class_attr = array();

				$day_of_week_ = date('N-w',strtotime($year.'-'.$month.'-'.($x+1)));
				$day_of_week = explode('-', $day_of_week_);
				
				if(is_array($date_with_events) && count($date_with_events)>0){
					$days_with_events_class = (array_key_exists($x+1, $date_with_events))?
						' has_events':null;
				}else{
					$days_with_events_class=null;
				}			
				
				
				if($x==0){
					$day_of_week_1st = date('N',strtotime($year.'-'.$month.'-'.($x+1)));
					$boxes = ( $day_of_week_1st < $start_of_week)? 
						((7-$start_of_week) +$day_of_week_1st): 
						( $day_of_week_1st - $start_of_week);
					
					if($day_of_week_1st != $start_of_week && $boxes!= 7){
						for($y=0; $y<( $boxes );$y++){
							$output .= "<p class='evo_wv_day evo_wv_empty' data-cnt='{$_box_count}'>-</p>";
							$_box_count++;
						}
					}
				}

				// get number of events per this date
					$__events = (!empty($date_with_events[$x+1]))? "<span class='num_events'>".$date_with_events[$x+1]."</span>":null;
					$__events_x='';
				
				// HTML for the day box
				$__class_attr[] = ($day==($x+1))?' on_focus':null;
				$day_attr = $x+1;
					
				// class name for current focused week
					
					if($start_of_week == $day_of_week[1] ){
						$week_of_month++;
					}

					// start of the focus week
					if($focused_week_of_the_month == $week_of_month){
						$focus_week = true;	$focused_week = $week_of_month;					
					}

					if($focus_week){
						$focus_week_count++;
						if($focus_week_count==7)
							$focus_week= false;

						if($focus_week_count==1)
							$days_before=$x;
					}	

					$__class_attr[] = 'week_'.$week_of_month;
					$__class_attr[] = ($focus_week)? 'focus_week':null;

				// json array
				$json = (array_key_exists($x+1, $days_w_e))? json_encode($days_w_e[$x+1]): null;

				$output.= "<p class='evo_wv_day{$days_with_events_class}". implode(' ', $__class_attr) ."' data-dow='{$day_of_week[0]}' data-day='{$day_attr}' data-cnt='{$_box_count}' data-ed='".$json."' data-wk='{$week_of_month}'>"
					.'<span class="day_name">'.$this->day_names[$day_of_week[0]].'</span>'
					.'<span class="day_num">'.($x+1).$__events_x.'</span>'
					.$__events
					."</p>";
				
				$_box_count++;
			}
			$output.= "<div class='clear' data-wb='{$days_before}' data-fw='{$focused_week}' data-fwm='{$week_num_for_focus_of_month}'></div>";


			return $output;
		}
	//	create the content for the weekly cal grids	 */
		function content_below_sortbar_this($content){
			
			// check if weekly cal is running on this calendar
			if(!$this->is_running_wv)
				return;
			
			$this->set_three_letter_day_names();

			$focusWeekData = $this->focus_data;
			$evcal_val1= get_option('evcal_options_evcal_1');	


			$hide_arrows = ($evcal_val1['evcal_arrow_hide']=='yes')? true:false;	
				
				// active or not arrows
				$prev_active = ($focusWeekData['focus_week']==1)? 'disable':null;
				$next_active = ($focusWeekData['focus_week']== $focusWeekData['weeksinMonth'])? 'disable':null;
			
			$content.="
			<div class='eventon_weeklyview' cal_id='{$focusWeekData['cal_id']}' data-multiplier='0'>
				<a class='evowv_prev evowv_arrow {$prev_active}' data-dir='prev'><i class='fa fa-angle-left'></i></a>
				<a class='evowv_next evowv_arrow {$next_active}' data-dir='next'><i class='fa fa-angle-right'></i></a>
				<div class='evoWV_days' data-focus_week='".$focusWeekData['focus_week']."'>";					
					$content.= $this->get_grid_week(
						$focusWeekData['day'],
						$focusWeekData['month'], 
						$focusWeekData['year'],'', 
						'1'
					);							
					$content.="</div>
			</div>";
			
			echo $content;		
			
			// Stop this from being getting hooked into other calendars on the same page
			remove_action('eventon_after_loadbar',array( $this, 'content_below_sortbar_this' ));
		}	

	// RETURN focus week number within a month 1-5
		function get_focused_week($week='', $day='', $month='', $year=''){
			$data = $this->focus_data;
			
			// get the focused day, if passed or get todays day
			$day = !empty($day)? $day: date('j',$this->current_time);
			$month = !empty($month)? $month: $data['month'];
			$year = !empty($year)? $year: $data['year'];
			$days_in_month = !empty($data['number_days_in_month'])? $data['number_days_in_month']: $this->days_in_month($month, $year);


			$first_dow = date('N', mktime(0,0,0,$month, 1, $year));
			$startOfWeek = ($this->start_of_week ==0)? 7: $this->start_of_week;
			
			// calculate days in first week
			$daysInFirstWeek = ($startOfWeek > $first_dow)? 
				($startOfWeek-$first_dow): (7- ($first_dow- $startOfWeek));

			if(empty($week)){			
				$fw = $this->get_focus_week($daysInFirstWeek, $day);	
				$this->focus_data['focus_week']=$fw;

			}else{
				$fw = $this->focus_data['focus_week'] = $week;
			}

			// number of weeks in month
				$subDays = (7-$daysInFirstWeek)+$days_in_month;
				$fullweeks = (int)($subDays /7);
				$partialWeeks = $subDays -( $fullweeks*7 );

				$allWeeks = ($partialWeeks<=0)? $fullweeks: $fullweeks+1;

				$this->focus_data['weeksinMonth']=$allWeeks;
				$this->focus_data['difw']=$daysInFirstWeek;


			return array(
				'focus_week'=>$fw, 
				'difw'=>$daysInFirstWeek, 
				'wim'=>$allWeeks
			);				
		}

		function get_focus_week($difw, $day){
			$addition = ($difw==7)?0: 7-$difw;
			$dds = $day + $addition;
			$num1 = (int) ($dds/7);
			$num2 = $dds-($num1*7);
			return ($num2==0)? $num1: $num1+1; 
		}
	// GET calendar date range for the focused week only
		function get_cal_date_range($args='', $return=false){

			$data = (!empty($this->focus_data))?$this->focus_data:null;

			// month and year values
				$month = (!empty($args['month']))? $args['month'] :$data['month'];
				$year = (!empty($args['year']))? $args['year'] :$data['year'];

			$focusWeek = (!empty($args['focus_week']))? $args['focus_week']: $data['focus_week'];
			$daysInFirstWeek =(!empty($args['difw']))? $args['difw']:$data['difw'];

			$weekLastDay = ( ($focusWeek-1)*7  )+$daysInFirstWeek;
			$weekStartDay = ($weekLastDay-6);
			$weekStartDay = ($weekStartDay>0)?$weekStartDay: 1;

			$number_days_in_month = $this->days_in_month( $month, $year);

			$weekLastDay = ($weekLastDay>$number_days_in_month)?$number_days_in_month: $weekLastDay;

			$focus_start_date_range = mktime( 0,0,0,$month,$weekStartDay,$year );
			$focus_end_date_range = mktime( 23,59,59,$month,$weekLastDay,$year );

			if($return){
				return array($focus_start_date_range, $focus_end_date_range);
			}else{
				$this->focus_data['focus_start_date_range']= $focus_start_date_range;
				$this->focus_data['focus_end_date_range']= $focus_end_date_range;	
			}			
		}
	// ONLY for WV calendar actions 
		public function only_wv_actions(){
			add_filter('eventon_cal_class', array($this, 'eventon_cal_class'), 10, 1);		
			add_action('eventon_after_loadbar',array( $this, 'content_below_sortbar_this' ), 10,1);

		}
		public function remove_only_wv_actions(){
			//add_filter('eventon_cal_class', array($this, 'remove_eventon_cal_class'), 10, 1);	
			remove_filter('eventon_cal_class', array($this, 'eventon_cal_class'));			
		}
	// add class name to calendar header for EM
		function eventon_cal_class($name){
			$name[]='evoWV';
			return $name;
		}
	// remove class name to calendar header for EM
		function remove_eventon_cal_class($name){
			if(($key = array_search('evoWV', $name)) !== false) {
			    unset($name[$key]);
			}
			return $name;
		}

	// append WV only values to evo data section in the calendar
		function add_evoWV_to_evoData($array){
			if(!empty($this->focus_data)){
				$focusWeek = $this->focus_data;

				$array['focus_week'] = $focusWeek['focus_week'];
				$array['dim'] = $focusWeek['number_days_in_month'];
				$array['wim'] = $focusWeek['weeksinMonth'];
				$array['difw'] = $focusWeek['difw'];
			}
			return $array;
		}
	// if focus_data is empty then update with new values
		function create_focus_week_data($args){	
			$basValus = array(
				'day','month','year','number_days_in_month','focus_week',
				'weeksinMonth','difw'
			);
			foreach($basValus as $bv){
				if(!empty($args[$bv]))
					$this->focus_data[$bv] = $args[$bv];
			}
		}
	// three letter day array
		function set_three_letter_day_names($lang=''){
			
			// Build 3 letter day name array to use in the weeklyview from custom language
			for($x=1; $x<8; $x++){			
				$evcal_day_is[$x] =eventon_return_timely_names_('day_num_to_name',$x, 'three', $lang);
				//print_r($evcal_day_is[$x]);				
			}	
			
			$this->day_names = $evcal_day_is;
		}
	
		function days_in_month($month, $year) { 
			return date('t', mktime(0, 0, 0, $month+1, 0, $year)); 
		}
		function calendar_footer(){
			$this->is_running_wv=false;
		}

}