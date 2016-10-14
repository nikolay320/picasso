<?php
/**
 * EVO_generator class.
 *
 * @class 		EVO_generator
 * @version		2.4.4
 * @package		EventON/Classes
 * @category	Class
 * @author 		AJDE
 */

class EVO_generator {

	public $google_maps_load,
		$is_eventcard_open,
		$evopt1,
		$evopt2,
		$evcal_hide_sort;

	public $is_upcoming_list = false;
	public $is_eventcard_hide_forcer = false;
	public $_sc_hide_past = false; // shortcode hide past

	public $wp_arguments='';
	public $shortcode_args ='';
	public $filters;
	public $cal_filters = '';

	public $lang_array=array();

	public $current_event_ids = array();
	public $current_time ='';

	private $_hide_mult_occur = false;
	public	$events_processed = array();

	private $__apply_scheme_SEO = false;
	private $_featured_events = array();

	private $class_args = array();

	public $__calendar_type;

	public $event_types = 3;

	/**	Construction function	 */
		public function __construct(){

			$this->__calendar_type = 'default';

			/** set class wide variables **/
			$options_1 = get_option('evcal_options_evcal_1');
			$this->evopt1= (!empty($options_1))? $options_1:null;
			$this->evopt2= get_option('evcal_options_evcal_2');

			$this->cal_filters = '';

			$this->is_eventcard_open = (!empty($this->evopt1['evo_opencard']) && $this->evopt1['evo_opencard']=='yes')? true:false;

			// set reused values
			$this->evcal_hide_sort = (!empty($this->evopt1['evcal_hide_sort']))? $this->evopt1['evcal_hide_sort']:null;

			// load google maps api only on frontend
			add_action( 'init', array( $this, 'init' ) );

			//$this->google_maps_load = get_option('evcal_gmap_load');
			//add_action('wp_enqueue_scripts', array($this, 'load_evo_styles'));
		}

	/**
	 * Initiate the calendar
	 * @return
	 */
		function init(){
			$this->shell = new evo_cal_shell();
			$this->body = new evo_cal_body();
			$this->helper = new evo_cal_help();

			add_action( 'init', array( $this, 'load_google_maps_api' ) );

			$this->shell->verify_eventtypes();
			$this->shell->reused();


			// initiate calendar requirements
			global $eventon;
			//$this->shell = $eventon->cal_shell;

		}

	/**
	 * Deprecated functions since 2.2.22
	 */
		// load scripts
		function load_evo_files(){
			$this->shell->load_evo_files();
		}
		// SHORT CODE variables
		function get_supported_shortcode_atts(){
			return $this->shell->get_supported_shortcode_atts();
		}
		// GOOGLE MAP
		function load_google_maps_api(){
			$this->shell->load_google_maps_api();
		}
		// GET: single calendar month body content
		function get_calendar_month_body( $get_new_monthyear, $focus_start_date_range='', $focus_end_date_range=''){

			return $this->body->get_calendar_month_body( $get_new_monthyear, $focus_start_date_range='', $focus_end_date_range='');
		}
		// ABOVE calendar header
		public function cal_above_header($args){
			return $this->body->cal_above_header($args);
		}
		// GET: calendar starting month and year data
		function get_starting_monthYear(){
			return $this->shell->get_starting_monthYear();
		}
		// HEADER
		public function calendar_shell_header($arg){
			return $this->body->calendar_shell_header($arg);
		}
		// FOOTER
		public function calendar_shell_footer(){
			return $this->body->calendar_shell_footer();
		}
		// this is used in shell header as well as other headers
		function get_calendar_header($arguments){
			return $this->body->get_calendar_header($arguments);
		}
		// the reused variables and other things within the calendar
		function reused(){
			$this->shell->reused();
		}
		// SORT event list array
		public function evo_sort_events_array($events_array, $args=''){
			return $this->shell->evo_sort_events_array($events_array, $args='');
		}

	/**
	 * process shortcode and variables for calendar
	 * @param  array  $args
	 * @param  boolean $own_defaults
	 * @param  string  $type
	 * @return array
	 */
		function process_arguments($args='', $own_defaults=false, $type=''){

			$this->shell->load_evo_files();

			$default_arguments = $this->shell->get_supported_shortcode_atts();

			// if there are arguments passed for processing
			if(!empty($args) ){

				// merge default values of shortcode
				if(!$own_defaults)
					$args = shortcode_atts($default_arguments, $args);

				// load calendar filters
					$args = $this->load_calendar_filters($args);

				// check if shortcode arguments already set
				if(empty($this->shortcode_args)){
					$this->shortcode_args=$args;
				}else{
					$args =array_merge($this->shortcode_args,$args );
					$this->shortcode_args=$args;
				}

			// Args as empty value
			}else{
				if($type=='usedefault'){
					$args = (!empty($this->shortcode_args))? $this->shortcode_args:null;
				}else{
					$this->shortcode_args=$default_arguments; // set global arguments
					$args = $default_arguments;
				}
			}

			// hook to change shortcode arguments based on shortcode arguments given
			$args = apply_filters('eventon_process_after_shortcodes', $args);

			// Do other things based on shortcode arguments
				// EventCard open by default evc_open value
					// whats saved on settings
					$_settings_evc = (!empty($this->evopt1['evo_opencard']) && $this->evopt1['evo_opencard']=='yes')? 'yes':'no';
					$_args_evc = (!empty($args['evc_open']) && $args['evc_open']=='yes')? 'yes':'no';

					// Settings value set to yes will be override by shortcode values
					$__evc = ($_args_evc=='yes')? 'yes':
						( ($_settings_evc=='yes' )? 'yes':'no' );

					// set the value that was calculated
					$args['evc_open'] = $__evc;

					//echo $__evc;

				// Set hide past value for shortcode hide past event variation
					$this->_sc_hide_past = (!empty($args['hide_past']) && $args['hide_past']=='yes')? true:false;

				// check for possible filters
					$this->filters = (!empty($this->cal_filters))? 'true':'false';

				// process WPML
					if(defined('ICL_LANGUAGE_CODE')){
						$lang_count = apply_filters('eventon_lang_var_count', 3); // @version 2.2.24
						for($x=1; $x <= $lang_count; $x++){
							if(!empty($args['wpml_l'.$x]) && $args['wpml_l'.$x]==ICL_LANGUAGE_CODE){
								$args['lang']='L'.$x;
							}
						}
					}

			// set processed argument values to class variable
			$this->shortcode_args = $args;

			return $args;
		}

	// update calednar filters from shortcode arguments
	// @added: 2.2.30
		function load_calendar_filters($shortcode_args){
			// if filters are already passed - eg via ajax
			if(!empty($shortcode_args['filters']) && is_array($shortcode_args['filters'])){

				$this->cal_filters = $shortcode_args['filters'];
				// remove filters from shortcode arguments
				unset($shortcode_args['filters']);

				return $shortcode_args;
			}else{
				// if there are no filters passed on shortcode arguments

				// get all event type taxonomioes array
				$updated_event_types = apply_filters('eventon_event_types_update',$this->shell->get_all_event_tax(), $shortcode_args );

				// for each event types taxonomies
				foreach($updated_event_types  as $ety=>$event_type){

					if(empty($event_type)) continue;

					$event_type_val = (!empty($shortcode_args[$event_type])? $shortcode_args[$event_type]:'');

					// support actionUser user tax
					$event_type_val = apply_filters('eventon_event_type_value', $event_type_val, $event_type, $shortcode_args);

					if(!empty($event_type_val) && $event_type_val!='all'){

						// NOT feature
						if(strpos($event_type_val, 'NOT-')!== false){
							//$op = explode('NOT-', $event_type_val);
							$filter_op='NOT IN';
							//$vals = $op[1];
							$vals = str_replace('NOT-', '', $event_type_val);
						}else{
							$vals= $event_type_val;
							$filter_op = 'IN';
						}

						$filters[]=array(
							'filter_type'=>'tax',
							'filter_name'=>$event_type,
							'filter_val'=>$vals,
							'filter_op'=>$filter_op
						);
					}
				}
				//print_r($filters);

				// update filters array
				if(!empty($filters)){
					$this->cal_filters = $filters;
				}else{
					$this->cal_filters = '';
				}

				return $shortcode_args;
			}
		}

	function update_shortcode_arguments($new_args){
		$args = array_merge($this->shortcode_args, $new_args);
		$this->shortcode_args = $args;
		return $args;
	}


	/** GENERATE: function to build the entire event calendar */
		public function eventon_generate_calendar($args){
			global $eventon, $wpdb;

			// extract the variable values
			$args__ = $this->process_arguments($args);

			// /print_r($args__);
			extract($args__);

			$this->_hide_mult_occur= ($hide_mult_occur=='yes')?true:false;

			// Before beginning the eventON calendar Action
			do_action('eventon_cal_variable_action', $args);


			// If settings set to hide calendar
			if( $show_upcoming!=1 && ( !empty($this->evopt1['evcal_cal_hide']) && $this->evopt1['evcal_cal_hide']=='no') ||  empty($this->evopt1['evcal_cal_hide'])):


				$evcal_plugin_url= AJDE_EVCAL_URL;
				$content = $content_li='';

				// Check for empty month_incre values
				$month_incre = (!empty($month_incre))? $month_incre:0;


				// *** GET STARTING month and year
				extract( $this->shell->get_starting_monthYear() );

				// ========================================
				// HEADER with month and year name	- for NONE upcoming list events
				$content.= $this->body->get_calendar_header(array(
					'focused_month_num'=>$focused_month_num,
					'focused_year'=>$focused_year
					)
				);


				// Calendar month body
				$get_new_monthyear = eventon_get_new_monthyear($focused_month_num, $focused_year,0);
				$content.= $this->body->get_calendar_month_body($get_new_monthyear, $focus_start_date_range, $focus_end_date_range);

				$content.=$this->body->calendar_shell_footer($args);

				// action to perform at the end of the calendar
				do_action('eventon_cal_end');

				return  $content;

			// support for show_upcoming shortcode -- deprecated in the future
			elseif($show_upcoming==1 && $number_of_months>0):
				return $this->generate_events_list($args);
			endif;


		}


	/** MAIN function to generate individual ONLY events HTML */
		public function eventon_generate_events($args){

			global $eventon; $content ='';

			// get required shortcode based argument values
			$ecv = $this->process_arguments($args);

			$this->reused();

			// GET events list array
			$event_list_array = $this->evo_get_wp_events_array(
				'', $args, $this->cal_filters);

			// Events separated by month titles
			if(!empty($ecv['sep_month']) && $ecv['sep_month']=='yes' && $ecv['number_of_months']>1 ){

				//$event_list_array = $this->evo_get_wp_events_array('', $args, $this->cal_filters);
				$content.= $this->separate_eventlist_to_months($event_list_array, $ecv['event_count'], $ecv);
				

			}else{ // return only individual events
				// MOVE: featured events to top if set
				$months_event_array = $this->move_ft_to_top($event_list_array, $ecv);

				// GET: eventTop and eventCard for each event in order
				$months_event_array = $this->generate_event_data(
					$months_event_array, 	$ecv['focus_start_date_range']
				);

				$content = $this->evo_process_event_list_data($months_event_array, $args);
			}

			return $content;

		}// END evcal_generate_events()

		// Seperate event list events into months
			function separate_eventlist_to_months($eventList, $eventCount = false, $args){
				// if event count limit set - event_count
				$isEventCount = ( $eventCount>0)? $eventCount:false;

				$content = '';
				// if events exist
				if(sizeof($eventList)){
					$sorted_list = array();
					date_default_timezone_set('UTC');

					$count = 1;
					foreach($eventList as $event){
						if($isEventCount && $count>=$isEventCount) continue;

						$month = date('n', $event['event_start_unix']);
						$year = date('Y', $event['event_start_unix']);

						$sorted_list[$year.'-'.$month][] = $event;
						$count ++;
					}

					foreach($sorted_list as $moyr=>$events){

						$moyr = explode('-', $moyr);
						$this->shortcode_args = $args;

						$months_event_array = $this->generate_event_data(	$events	);

						$content.= "<div class='evcal_month_line'><p>".eventon_returnmonth_name_by_num($moyr['1']).' '.$moyr['0']."</p></div>";
						$content .= $this->evo_process_event_list_data($months_event_array);

					}
				}else{// no events selected
					$content .= $this->evo_process_event_list_data($eventList);
				}
				return $content;
			}

	/* GENERATE: upcoming list events*/
		function generate_events_list($args=''){

			$type = (empty($args))? 'usedefault':null;

			$args__ = $this->process_arguments($args, '', $type);
			extract($args__);
			$content='';

			// HIDE or show multiple occurance of events in upcoming list
			$this->_hide_mult_occur= ($hide_mult_occur=='yes')?true:false;


			// check if upcoming list calendar view
			if($number_of_months>0){
				$this->is_upcoming_list= true;
				$this->is_eventcard_open = false;
			}


			// *** GET STARTING month and year
			extract( $this->get_starting_monthYear() );

			// Calendar SHELL
			$content.=$this->body->get_calendar_header(array(
				'focused_month_num'=>$focused_month_num,
				'focused_year'=>$focused_year,
				'sortbar'=>false,
				'date_header'=>false,
				'_html_evcal_list'=>false,
				'_html_sort_section'=>false
				)
			);

			// generate each month
			for($x=0; $x<$number_of_months; $x++){

				//echo $number_of_months;
				$month_body='';

				$__mo_cnt = ($event_order=='DESC')? $number_of_months-$x-1: $x;

				$get_new_monthyear = eventon_get_new_monthyear($focused_month_num, $focused_year,$__mo_cnt);

				//print_r($get_new_monthyear);

				$active_month_name = eventon_returnmonth_name_by_num($get_new_monthyear['month']);

				// check settings to see if year should be shown or not
				$active_year = (!empty($show_year) && $show_year=='yes')?
					$get_new_monthyear['year']:null;

				// body content of the month
				$month_body= $this->body->get_calendar_month_body($get_new_monthyear);

				if($month_body=='false' && !empty($hide_empty_months) && $hide_empty_months=='yes' ){
					//$content.= "<div class='evcal_month_line'><p>".$active_month_name.' '.$active_year."</p></div>";
				}else{
					// Construct months exterior
					$content.= "<div class='evcal_month_line'><p>".$active_month_name.' '.$active_year."</p></div>";

					$content.= "<div id='evcal_list' class='eventon_events_list'>";
					$content.= $month_body;
					$content.= "</div>";
				}
			}

			ob_start();
			echo "<div class='clear'></div>";
			do_action('evo_cal_after_footer', $args__);
			echo "</div><!-- ajde_evcal_calendar-->";
			$content.= ob_get_clean();

			// RESET calendar stuff
			if($this->is_upcoming_list){ $this->is_upcoming_list=false;}

			return $content;
		}


		// RETURN array list of events
		// for a month by default but can change to set time line with args
			public function evo_get_wp_events_array(
				$wp_argument_additions='', $shortcode_args='', $filters=''
			){

				//print_r($shortcode_args['focus_start_date_range']);
				$ecv = $this->process_arguments($shortcode_args);

				$filters = (!empty($filters))? $filters: $this->cal_filters;

				//print_r($shortcode_args);
				//print_r($filters);

				$this->reused();

				// ===========================
				// WPQUery Arguments
					$wp_arguments_ = array (
						'post_type' 		=>'ajde_events' ,
						'post_status'		=>'publish',
						'posts_per_page'	=>-1 ,
						'order'				=>'ASC',
					);

					//search query addition
						if(!empty($ecv['s'])){
							$wp_arguments_ = array_merge($wp_arguments_, array('s'=>$ecv['s']));
						}

					$wp_arguments = (!empty($wp_argument_additions))?
						array_merge($wp_arguments_, $wp_argument_additions): $wp_arguments_;

				// apply other filters to wp argument
					$wp_arguments = $this->apply_evo_filters_to_wp_argument($wp_arguments, $filters);

					//print_r($wp_arguments);

				// hook for addons
					$wp_arguments = apply_filters('eventon_wp_query_args',$wp_arguments, $filters, $ecv);

				$this->wp_arguments = $wp_arguments;
				//print_r($wp_arguments);

				// ========================
				// GET: list of events for wp argument
				$event_list_array = $this->wp_query_event_cycle(
					$wp_arguments,
					$ecv['focus_start_date_range'],
					$ecv['focus_end_date_range'],
					$ecv
				);

				// sort events by date and default values
				$event_list_array = $this->shell->evo_sort_events_array($event_list_array, $shortcode_args);

				return $event_list_array;
			}

		// check and return if processed events lists array for no events
			public function evo_process_event_list_data($months_event_array, $args=''){

				$ecv = $this->process_arguments($args);
				$content_li='';

				// if there are events in the list array
				if( is_array($months_event_array) && count($months_event_array)>0){

					// MOVE: featured events to top if set
					$months_event_array = $this->move_ft_to_top($months_event_array, $ecv);

					if($ecv['event_count']==0 ){
						foreach($months_event_array as $event){
							$content_li.= $event['content'];
						}

					}else if($ecv['event_count']>0){
						// if show limit then show all events but css hide
						if(!empty($ecv['show_limit']) && $ecv['show_limit']=='yes'){
							$lesser_of_count = count($months_event_array);
						}else{
							// make sure we take lesser value of count
							$lesser_of_count = (count($months_event_array)<$ecv['event_count'])?
								count($months_event_array): $ecv['event_count'];
						}

						// for each event until count
						for($x=0; $x<$lesser_of_count; $x++){
							$content_li.= $months_event_array[$x]['content'];
						}

						// load more events button
						if(!empty($ecv['show_limit']) && $ecv['show_limit']=='yes' && count($months_event_array)> $ecv['event_count'] ){
							$loadMoreRedir = (!empty($ecv['show_limit_redir']))? $ecv['show_limit_redir']:'0';
							$content_li.= '<div class="evoShow_more_events" style="'.( $ecv['tile_height']!=0? 'height:'.$ecv['tile_height'].'px':'' ).'" data-dir="'.$loadMoreRedir.'">'.$this->lang_array['evsme'].'</div>';
						}
					}
				}else{
					// EMPTY month array
					if($this->is_upcoming_list && !empty($ecv['hide_empty_months']) && $ecv['hide_empty_months']=='yes'){
						$content_li = "empty";
					}else{
						$content_li = "<div class='eventon_list_event'><p class='no_events' >".$this->lang_array['no_event']."</p></div>";
					}
				}

				return $content_li;
			}

		// Order the events list so featured events go to top
			function move_ft_to_top($eventlist, $args){
				if($args['ft_event_priority']=='yes' && !empty($this->_featured_events) && count($this->_featured_events)>0){

					$ft_events = $events = array();

					foreach($eventlist as $event){

						if(in_array($event['event_id'], $this->_featured_events)){
							$ft_events[]=$event;
						}else{
							$events[]=$event;
						}
					}

					// move featured events to top
					return array_merge($ft_events,$events);
				}
				return $eventlist;
			}

		// custom search filter for event post titles
			function search_filter($where, &$wp_query){
				global $wpdb;

				if($search_term = $wp_query->get( 'search_prod_title' )){
					$search_term = $wpdb->esc_like($search_term);
					$search_term = ' \'%' . $search_term . '%\'';
					$where .= ' AND ' . $wpdb->posts . '.post_title LIKE '.$search_term;
	            }

	            //print_r($wpdb->last_query);
	            return $where;
			}

	/**
	 * WP_Query function to generate relavent events for a given month
	 * return events list within start - end date range for WP_Query arg.
	 * return array
	 */
		public function wp_query_event_cycle(
			$wp_arguments,
			$focus_month_beg_range,
			$focus_month_end_range,
			$ecv=''
		){

			$event_list_array= $featured_events = array();
			$wp_arguments= (!empty($wp_arguments))?$wp_arguments: $this->wp_arguments;

			//print_r($wp_arguments);

			// check if multiple occurance of events b/w months allowed
			$__run_occurance_check = $hideMultOccur =  (($this->is_upcoming_list && $this->_hide_mult_occur) || (!empty($this->shortcode_args['hide_mult_occur']) && $this->shortcode_args['hide_mult_occur']=='yes'))? true:false;
			$_show_repeats = (!empty($this->shortcode_args['show_repeats']) && $this->shortcode_args['show_repeats']=='yes')? true: false;
			$is_user_logged_in = is_user_logged_in();

			/** RUN through all events **/
			//add_filter( 'posts_where', array($this, 'search_filter'), 10, 2 );
			$events = new WP_Query( $wp_arguments);
			//remove_filter( 'posts_where', array($this,'search_filter'), 10, 2 );

			// /print_r($this->shortcode_args);

			if ( $events->have_posts() ) :

				date_default_timezone_set('UTC');

				//shortcode driven hide_past value
					$evcal_cal_hide_past= ($this->_sc_hide_past)? 'yes':
						( (!empty($this->evopt1['evcal_cal_hide_past']))? $this->evopt1['evcal_cal_hide_past']: 'no');

				// override past event cut-off
					if(!empty($this->shortcode_args['pec'])){

						if( $this->shortcode_args['pec']=='cd'){
							// this is based on local time
							$current_time = strtotime( date("m/j/Y", current_time('timestamp')) );
						}else{
							// this is based on UTC time zone
							$current_time = current_time('timestamp');
						}

					}else{
						// Define option values for the front-end
						$cur_time_basis = (!empty($this->evopt1['evcal_past_ev']) )? $this->evopt1['evcal_past_ev'] : null;

						//date_default_timezone_set($tzstring);
						if($evcal_cal_hide_past=='yes' && $cur_time_basis=='today_date'){
							// this is based on local time
							$current_time = strtotime( date("m/j/Y", current_time('timestamp')) );
						}else{
							// this is based on UTC time zone
							$current_time = current_time('timestamp');
						}
					}//pec not present

						$this->current_time = $current_time;

					// current year month
						$__current_year = date('Y', (int)$focus_month_beg_range);
						$__current_month = date('n', (int)$focus_month_beg_range);

					// hide past by variable
						$hide_past_by = (!empty($this->shortcode_args['hide_past_by']))? $this->shortcode_args['hide_past_by']: null;

				// each event
				while( $events->have_posts()): $events->the_post();

					$p_id = $events->post->ID;
					$ev_vals = get_post_custom($p_id);

					// if event set to exclude from calendars
					if(!empty($ev_vals['evo_exclude_ev']) && $ev_vals['evo_exclude_ev'][0]=='yes')
						continue;

					$is_recurring_event = evo_check_yn($ev_vals, 'evcal_repeat');
					$__year_long_event = evo_check_yn($ev_vals, 'evo_year_long');
					$__month_long_event = ($__year_long_event? false: evo_check_yn($ev_vals, '_evo_month_long') ); // if year long then disregard month long

					// Show event only for logged in user filtering
						if( !empty($ev_vals['_onlyloggedin']) && $ev_vals['_onlyloggedin'][0]=='yes' && !$is_user_logged_in ) continue;

					// initial values
						$row_start = (!empty($ev_vals['evcal_srow']))?
							$ev_vals['evcal_srow'][0] :null;
						$row_end = ( !empty($ev_vals['evcal_erow']) )?
							$ev_vals['evcal_erow'][0]:$row_start;

						$evcal_event_color_n= (!empty($ev_vals['evcal_event_color_n']))?$ev_vals['evcal_event_color_n'][0]:'0';

						$_is_featured = (!empty($ev_vals['_featured']))?
							$ev_vals['_featured'][0] :'no';

					// move past events to trash
						/*
							if($__trash_old_events && !$is_recurring_event && !$__year_long_event){
								$rightnow = current_time('timestamp');
								if($row_end< $rightnow){
									$event = get_post($p_id, 'ARRAY_A');

									// only do this for event post types 5/19/15
									if($event['post_type']!='ajde_events') continue;

									$event['post_status']='trash';
									wp_update_post($event);
									eventon_record_trashedtime($this->evopt1);
								}
							}
						*/

					// check for recurring event
					if($is_recurring_event){

						// get saved repeat intervals for repeating events
						$repeat_intervals = (!empty($ev_vals['repeat_intervals']))?
							(is_serialized($ev_vals['repeat_intervals'][0])? unserialize($ev_vals['repeat_intervals'][0]): $ev_vals['repeat_intervals'][0] ) :null;

						$frequency = $ev_vals['evcal_rep_freq'][0];
						$repeat_gap_num = $ev_vals['evcal_rep_gap'][0];
						$repeat_num = (int)$ev_vals['evcal_rep_num'][0];

						// if repeat intervals are saved
						if(!empty($repeat_intervals) && is_array($repeat_intervals)){

							// check if only featured events to show
							if( (!empty($ecv['only_ft']) && $ecv['only_ft']=='yes' && $_is_featured=='yes') ||
								(!empty($ecv['only_ft']) && $ecv['only_ft']=='no' ) ||
								empty($ecv['only_ft'])
							){
								$feature = ($_is_featured!='no')?'yes':'no';

								$virtual_dates=array();
								$_inter = 0;

								// each repeating interval times
								foreach($repeat_intervals as $interval){

									$E_start_unix = $interval[0];
									$E_end_unix = $interval[1];
									$term_ar = 'rm';

									$__event_year = date('Y', $E_start_unix);

									// is future event
									$future_event = eventon_is_future_event($current_time, $E_start_unix, $E_end_unix, $evcal_cal_hide_past, $hide_past_by);
									$fe = $future_event;
									//$fe = ( (!empty($this->shortcode_args['el_type']))? true: $future_event );
									$event_past = eventon_is_event_past($current_time, $E_start_unix, $E_end_unix, $hide_past_by);

									// in date range
									$me = eventon_is_event_in_daterange($E_start_unix,$E_end_unix, $focus_month_beg_range,$focus_month_end_range, $this->shortcode_args);

									// /echo $E_start_unix.'-'.$E_end_unix.' '.$current_time.'-'.$p_id.' '.$future_event.' '.$this->shortcode_args['el_type'].' ';

									// /echo date('Y-m-d',$E_start_unix).'<br/>   ';

									if(($__year_long_event && !empty($ev_vals['event_year']) && $__event_year==$ev_vals['event_year'][0])
										|| ($__month_long_event && !empty($ev_vals['_event_month']) && $__current_month==$ev_vals['_event_month'][0] )
										|| ($fe && $me) ){

										if(
											(!$_show_repeats && !in_array($p_id, $this->events_processed) )
											|| (!$_show_repeats && !$hideMultOccur)
											|| ( $hideMultOccur && $_show_repeats )
										){
											if(!in_array($E_start_unix, $virtual_dates)){
												$virtual_dates[] = $E_start_unix;
												$event_list_array[] = array(
													'event_id' => $p_id,
													'event_start_unix'=>$E_start_unix,
													'event_end_unix'=>$E_end_unix,
													'event_title'=>get_the_title(),
													'event_color'=>$evcal_event_color_n,
													'event_type'=>$term_ar,
													'event_past'=>$event_past,
													'event_pmv'=>$ev_vals,
													'event_repeat_interval'=>$_inter
												);
											}

											if($feature!='no'){
												$featured_events[]=$p_id;
											}
										}
										$this->events_processed[]=$p_id;
									}
									$_inter ++;

								}// endforeeach

							}

						// does not have repeat intervals saved
						}else{

							// OLD WAY --- each repeating instance	OLD WAY
							for($x=0; $x<=($repeat_num); $x++){

								$feature='no';

								$repeat_multiplier = ((int)$repeat_gap_num) * $x;

								// Get repeat terms for different frequencies
								switch($frequency){
									// Additional frequency filters
									case has_filter("eventon_event_frequency_{$frequency}"):
										$terms = apply_filters("eventon_event_frequency_{$frequency}", $repeat_multiplier);
										$term = $terms['term'];
										$term_ar = $terms['term_ar'];
									break;
									case 'yearly':
										$term = 'year';	$term_ar = 'ry';
										$feature = ($_is_featured!='no')?'yes':'no';
									break;

									// MONTHLY
									case 'monthly':

										$term = 'month';	$term_ar = 'rm';
										$feature = ($_is_featured!='no')?'yes':'no';

									break;
									case 'weekly':
										$term = 'week';	$term_ar = 'rw';

									break;
									default: $term = $term_ar = ''; break;
								}

								$E_start_unix = strtotime('+'.$repeat_multiplier.' '.$term, $row_start);
								$E_end_unix = strtotime('+'.$repeat_multiplier.' '.$term, $row_end);

								// check if only featured events to show
								if( (!empty($ecv['only_ft']) && $ecv['only_ft']=='yes' && $_is_featured=='yes') ||
									(!empty($ecv['only_ft']) && $ecv['only_ft']=='no' ) ||
									empty($ecv['only_ft'])
								){

									$future_event = eventon_is_future_event($current_time, $E_start_unix, $E_end_unix, $evcal_cal_hide_past, $hide_past_by);
									$fe = ( (!empty($this->shortcode_args['el_type']))? true: $future_event );

									$me = eventon_is_event_in_daterange($E_start_unix,$E_end_unix, $focus_month_beg_range,$focus_month_end_range, $this->shortcode_args);
									$event_past = eventon_is_event_past($current_time, $E_start_unix, $E_end_unix, $hide_past_by);


									if($fe && $me){
										if($__run_occurance_check && !in_array($p_id, $this->events_processed) ||!$__run_occurance_check){

											$event_list_array[] = array(
												'event_id' => $p_id,
												'event_start_unix'=>$E_start_unix,
												'event_end_unix'=>$E_end_unix,
												'event_title'=>get_the_title(),
												'event_color'=>$evcal_event_color_n,
												'event_type'=>$term_ar,
												'event_past'=>$event_past,
												'event_pmv'=>$ev_vals
											);

											if($feature!='no'){
												$featured_events[]=$p_id;
											}
										}
										$this->events_processed[]=$p_id;
									}
								}
							} // end for statement

						} // end if statemtn

					}else{
					// Non recurring event
						// check if only featured events to show
						if( (!empty($ecv['only_ft']) && $ecv['only_ft']=='yes' && $_is_featured=='yes') ||
							(!empty($ecv['only_ft']) && $ecv['only_ft']=='no' ) ||
							empty($ecv['only_ft'])
						){

							$future_event = eventon_is_future_event($current_time, $row_start, $row_end, $evcal_cal_hide_past, $hide_past_by);
							$fe = $future_event;
							$me = eventon_is_event_in_daterange($row_start,$row_end, $focus_month_beg_range,$focus_month_end_range, $this->shortcode_args);
							$event_past = eventon_is_event_past($current_time, $row_start, $row_end, $hide_past_by);

							//echo $_is_featured.'tt';

							//echo get_the_title().$row_end.' v '.$current_time.'-</br>';

							if( ( $__year_long_event && !empty($ev_vals['event_year']) && $__current_year==$ev_vals['event_year'][0] )
								|| ($__month_long_event && !empty($ev_vals['_event_month']) && $__current_month==$ev_vals['_event_month'][0] )
								|| ($fe && $me )){

								if(
									($hideMultOccur && !in_array($p_id, $this->events_processed) )
									|| !$hideMultOccur
								){

									$feature = ($_is_featured!='no')?'yes':'no';

									$event_list_array[] = array(
										'event_id' => $p_id,
										'event_start_unix'=>$row_start,
										'event_end_unix'=>$row_end,
										'event_title'=>get_the_title(),
										'event_color'=>$evcal_event_color_n,
										'event_type'=>'nr',
										'event_past'=>$event_past,
										'event_pmv'=>$ev_vals
									);

									if($feature!='no'){
										$featured_events[]=$p_id;
									}

									$this->events_processed[]=$p_id;
								}
							}

						}
					}


				endwhile;

				$this->_featured_events=$featured_events;

			endif;
			wp_reset_postdata();

			return $event_list_array;
		}

	/**	output single event data	 */
		public function get_single_event_data($event_id, $lang='', $repeat_interval='', $args=''){

			$this->__calendar_type = 'single';

			if(!empty($lang)){
				$this->shell->update_shortcode_args('lang', $lang);
			}

			// GET Eventon files to load for single event
			$this->load_evo_files();

			$this->is_eventcard_open= ($this->is_eventcard_hide_forcer)?false:true;

			$emv = get_post_custom($event_id);

			// set base start and end unix
				$event_start_unix = isset($emv['evcal_srow'])? $emv['evcal_srow'][0]:0;
				$event_end_unix = isset($emv['evcal_erow'])?$emv['evcal_erow'][0]:0;

			// if repeat interval number set
			if(!empty($emv['evcal_repeat']) && $emv['evcal_repeat'][0]=='yes' && !empty($repeat_interval) && !empty($emv['repeat_intervals'])
			){
				$intervals = unserialize($emv['repeat_intervals'][0]);
				//print_r($intervals);
				if(!empty($intervals[$repeat_interval])){
					$event_start_unix = $intervals[$repeat_interval][0];
					$event_end_unix = $intervals[$repeat_interval][1];
				}
			}

			$this->shortcode_args = $args;

			$event_array[] = array(
				'event_id' => $event_id,
				'event_start_unix'=>$event_start_unix,
				'event_end_unix'=>$event_end_unix,
				'event_title'=>get_the_title($event_id),
				'event_color'=>(!empty($emv['evcal_event_color_n'])?
					$emv['evcal_event_color_n'][0]:''),
				'event_type'=>'nr',
				'event_pmv'=>$emv
			);

			$month_int = date('n', time() );

			return $this->generate_event_data($event_array, '', $month_int);

		}

	// RETURN event times
		private function generate_time($args){

			// start date is past enddate = focus day
			if($args['eventstart']['j'] < $args['cdate'] && $args['eventend']['j'] == $args['cdate']){
				return "<em class='evo_day'>".$args['eventstart']['M'].' '.$args['eventstart']['j']."</em><span class='start'>".$args['stime']."</span>". ( !$args['_hide_endtime']? "<span class='end'>- ".$args['etime']."</span>":null);

			// start day = focus day and end day in future
			}elseif($args['eventend']['j'] > $args['cdate'] && $args['eventstart']['j'] == $args['cdate']){
				return "<span class='start'>".$args['stime']."</span><em class='evo_day end'>".$args['eventend']['M'].' '.$args['eventend']['j']."</em>". ( !$args['_hide_endtime']? "<span class='end'>- ".$args['etime']."</span>":null);

			// both start day and end days are not focus day
			}elseif($args['eventend']['j'] != $args['cdate'] && $args['eventstart']['j'] != $args['cdate']){
				return "<em class='evo_day'>".$args['eventstart']['M'].' '.$args['eventstart']['j']."</em><span class='start'>".$args['stime']."</span><em class='evo_day end'>".$args['eventend']['M'].' '.$args['eventend']['j']."</em>". ( $args['_hide_endtime']? "<span class='end'>- ".$args['etime']."</span>":null);

			// same start day as focus day
			}elseif($args['eventstart']['j'] == $args['cdate']){
				return "<span class='start'>".$args['stime']."</span><em class='evo_day end'>".$args['eventend']['M'].' '.$args['eventend']['j']."</em>". ( !$args['_hide_endtime']? "<span class='end'>- ".$args['etime']."</span>":null);
			// same end day as focus day
			}elseif($args['eventend']['j'] == $args['cdate']){
				return "<em class='evo_day'>".$args['eventstart']['M'].' '.$args['eventstart']['j']."</em><span class='start'>".$args['stime']."</span><em class='evo_day end'>".$args['eventend']['M'].' '.$args['eventend']['j']."</em>". (!$args['_hide_endtime']? "<span class='end'>- ".$args['etime']."</span>":null);
			}
		}

	// GENERATE TIME for event
		public function generate_time_(
			$DATE_start_val='',
			$DATE_end_val='',
			$pmv,
			$evcal_lang_allday,
			$focus_month_beg_range='',
			$FOCUS_month_int='',
			$event_start_unix='',
			$event_end_unix=''
		){
			global $eventon;

			// INITIAL variables
				// start and end row times
					$event_start_unix = (!empty($event_start_unix))? $event_start_unix: (isset($pmv['evcal_srow'])?$pmv['evcal_srow'][0]:0);
					$event_end_unix = (!empty($event_end_unix))? $event_end_unix:
						(!empty($pmv['evcal_erow'])? $pmv['evcal_erow'][0]: $event_start_unix);


				

				$_is_allday = (!empty($pmv['evcal_allday']) && $pmv['evcal_allday'][0]=='yes')? true:false;
				$_hide_endtime = (!empty($pmv['evo_hide_endtime']) && $pmv['evo_hide_endtime'][0]=='yes')? true:false;

				$DATE_start_val= (!empty($DATE_start_val))? $DATE_start_val: eventon_get_formatted_time($event_start_unix);
				if(empty($event_end_unix)){
					$DATE_end_val= $DATE_start_val;
				}else{
					$DATE_end_val=(!empty($DATE_end_val))? $DATE_end_val: eventon_get_formatted_time($event_end_unix);
				}


				// FOCUSED values
				$CURRENT_month_INT = (!empty($FOCUS_month_int))?
					$FOCUS_month_int: (!empty($focus_month_beg_range)?
						date('n', $focus_month_beg_range ): date('n')); //
				$_current_date = (!empty($focus_month_beg_range))? date('j', $focus_month_beg_range ): 1;

				// time format
				$wp_time_format = get_option('time_format');
				$wp_date_format = get_option('date_format');
				$time_format = (!empty($this->evopt1['evcal_tdate_format']))? $this->evopt1['evcal_tdate_format']: 'F j(l) T';
				// M F j S l D


				// Universal time format
				// if activated get time values
				$__univ_time = false;
				if( !empty($this->evopt1['evo_timeF_v']) && !empty($this->evopt1['evo_timeF']) && $this->evopt1['evo_timeF'] =='yes' ){
					$__univ_time_s = eventon_get_langed_pretty_time($event_start_unix, $this->evopt1['evo_timeF_v']);

					$__univ_time = ($_hide_endtime)? $__univ_time_s:  $__univ_time_s .' - '. eventon_get_langed_pretty_time($event_end_unix, $this->evopt1['evo_timeF_v']);
				}

				//echo $wp_date_format.' '.$wp_time_format;

				//$formatted_start = date($wp_time_format,($event_start_unix));
				$formatted_start = eventon_get_lang_formatted_timestr($wp_time_format,$DATE_start_val);
				$formatted_end = eventon_get_lang_formatted_timestr($wp_time_format,$DATE_end_val);
				//$formatted_end = date($wp_time_format,($event_end_unix));


			$date_args = array(
				'cdate'=>$_current_date,
				'eventstart'=>$DATE_start_val,
				'eventend'=>$DATE_end_val,
				'stime'=>$formatted_start,
				'etime'=>$formatted_end,
				'_hide_endtime'=>$_hide_endtime
			);


			// same start and end months
			if($DATE_start_val['n'] == $DATE_end_val['n']){

				/** EVENT TYPE = start and end in SAME DAY **/
				if($DATE_start_val['j'] == $DATE_end_val['j']){

					// check all days event
					if($_is_allday){
						$__from_to ="<em class='evcal_alldayevent_text'>(".$evcal_lang_allday.": ".$DATE_start_val['l'].")</em>";
						$__prettytime = $evcal_lang_allday.' ('. ucfirst($DATE_start_val['l']).')';
						$__time = "<span class='start'>".$evcal_lang_allday."</span>";
					}else{
						$__from_to = ($_hide_endtime)?
							$formatted_start:
							$formatted_start.' - '. $formatted_end;

						$__prettytime = ($__univ_time)? 
							$__univ_time: 
							apply_filters('eventon_evt_fe_ptime', '('. ucfirst($DATE_start_val['l']).') '.$__from_to);
						$__time = "<span class='start'>".$formatted_start."</span>". (!$_hide_endtime ? "<span class='end'>- ".$formatted_end."</span>": null);
					}


					$_event_date_HTML = array(
						'html_date'=> '<span class="start">'.$DATE_start_val['j'].'<em>'.$DATE_start_val['M'].'</em></span>',
						'html_time'=>$__time,
						'html_fromto'=> apply_filters('eventon_evt_fe_time', $__from_to),
						'html_prettytime'=> $__prettytime,
						'class_daylength'=>"sin_val",
						'start_month'=>$DATE_start_val['M'],
					);

				}else{
					// different start and end date

					// check all days event
					if($_is_allday){
						$__from_to ="<em class='evcal_alldayevent_text'>(".$evcal_lang_allday.")</em>";
						$__prettytime = $DATE_start_val['j'].' ('. ucfirst($DATE_start_val['l']) .') - '.$DATE_end_val['j'].' ('. ucfirst($DATE_end_val['l']).')';
						$__time = "<span class='start'>".$evcal_lang_allday."</span>";
					}else{


						$__from_to = ($_hide_endtime)?
							$formatted_start:
							$formatted_start.' - '.$formatted_end. ' ('.$DATE_end_val['j'].')';
						$__prettytime =($__univ_time)?
							$__univ_time:
							apply_filters('eventon_evt_fe_ptime', $DATE_start_val['j'].' ('. ucfirst($DATE_start_val['l']).') '.$formatted_start.  ( !$_hide_endtime? ' - '.$DATE_end_val['j'].' ('. ucfirst($DATE_end_val['l']).') '.$formatted_end :'') ) ;

						// for daily view check if start day is same as focused day
						$__time = $this->generate_time($date_args);

					}


					$_event_date_HTML = array(
						'html_date'=> '<span class="start">'.$DATE_start_val['j'].'<em>'.$DATE_start_val['M'].'</em></span>'. ( !$_hide_endtime? '<span class="end"> - '.$DATE_end_val['j'].'</span>': ''),
						'html_time'=>$__time,
						'html_fromto'=> apply_filters('eventon_evt_fe_time', $__from_to),
						'html_prettytime'=> $__prettytime,
						'class_daylength'=>"mul_val",
						'start_month'=>$DATE_start_val['M']
					);
				}
			}else{
				/** EVENT TYPE = different start and end months **/

				/** EVENT TYPE = start month is before current month **/
				if($CURRENT_month_INT != $DATE_start_val['n']){
					// check all days event
					if($_is_allday){
						$__from_to ="<em class='evcal_alldayevent_text'>(".$evcal_lang_allday.")</em>";
						$__time = "<span class='start'>".$evcal_lang_allday."</span>";
					}else{
						$__start_this = '('.$DATE_start_val['F'].' '.$DATE_start_val['j'].') '.$formatted_start;
						$__end_this = (!$_hide_endtime? ' - ('.$DATE_end_val['F'].' '.$DATE_end_val['j'].') '.$formatted_end :'' );

						$__from_to = ($_hide_endtime)?
							$__start_this:$__start_this.$__end_this;

						// for daily view check if start day is same as focused day
						$__time = $this->generate_time($date_args);
					}

				}else{
					/** EVENT TYPE = start month is current month **/
					// check all days event
					if($_is_allday){
						$__from_to ="<em class='evcal_alldayevent_text'>(".$evcal_lang_allday.")</em>";
						$__time = "<span class='start'>".$evcal_lang_allday."</span>";
					}else{
						$__start_this = $formatted_start;
						$__end_this = ' - ('.$DATE_end_val['F'].' '.$DATE_end_val['j'].') '.$formatted_end;

						$__from_to =($_hide_endtime)? $__start_this:$__start_this.$__end_this;

						// for daily view check if start day is same as focused day
						$__time = $this->generate_time($date_args);
					}
				}


				// check all days event
				if($_is_allday){
					$__prettytime = ucfirst($DATE_start_val['F']) .' '.$DATE_start_val['j'].' ('. ucfirst($DATE_start_val['l']).')'. (!$_hide_endtime? ' - '. ucfirst($DATE_end_val['F']).' '.$DATE_end_val['j'].' ('. ucfirst($DATE_end_val['l']).')' :'' );
				}else{
					$__prettytime =
						ucfirst($DATE_start_val['F']) .' '.$DATE_start_val['j'].' ('. ucfirst($DATE_start_val['l']).') '.date($wp_time_format,($event_start_unix)). ( !$_hide_endtime? ' - '. ucfirst($DATE_end_val['F']).' '.$DATE_end_val['j'].' ('.ucfirst($DATE_end_val['l']).') '.date($wp_time_format,($event_end_unix)) :'' );
				}


				// html date
				$__this_html_date = ($_hide_endtime)?
					'<span class="start">'.$DATE_start_val['j'].'<em>'.$DATE_start_val['M'].'</em></span>':
					'<span class="start">'.$DATE_start_val['j'].'<em>'.$DATE_start_val['M'].'</em></span><span class="end"> - '.$DATE_end_val['j'].'<em>'.$DATE_end_val['M'].'</em></span>';

				$_event_date_HTML = apply_filters('evo_eventcard_dif_SEM', array(
					'html_date'=> $__this_html_date,
					'html_time'=>$__time,
					'html_fromto'=> apply_filters('eventon_evt_fe_time', $__from_to),
					'html_prettytime'=> ($__univ_time)? $__univ_time: apply_filters('eventon_evt_fe_ptime', $__prettytime),
					'class_daylength'=>"mul_val",
					'start_month'=>$DATE_start_val['M']
				));
			}

			// year long event
				$__is_year_long = (!empty($pmv['evo_year_long']) && $pmv['evo_year_long'][0]=='yes')? true:false;
				//if year long event
				if($__is_year_long){
					$evcal_lang_yrrnd = $this->lang_array['evcal_lang_yrrnd'];
					$_event_date_HTML = array(
						'html_date'=> '<span class="yearRnd"></span>',
						'html_time'=>'',
						'html_fromto'=> $evcal_lang_yrrnd . ' ('. $DATE_start_val['Y'] .')',
						'html_prettytime'=> $evcal_lang_yrrnd . ' ('. $DATE_start_val['Y'] .')',
						'class_daylength'=>"no_val",
						'start_month'=>$_event_date_HTML['start_month']
					);
				}

			// Month long event
				$__is_month_long = $__is_year_long? false:
					((!empty($pmv['_evo_month_long']) && $pmv['_evo_month_long'][0]=='yes')? true:false);
				//if month long event
				if($__is_month_long){
					$evcal_lang_mntlng = $this->lang_array['evcal_lang_mntlng'];
					$_event_date_HTML = array(
						'html_date'=> '<span class="yearRnd"></span>',
						'html_time'=>'',
						'html_fromto'=> $evcal_lang_mntlng . ' ('. $DATE_start_val['F'] .')',
						'html_prettytime'=> $evcal_lang_mntlng . ' ('. $DATE_start_val['F'] .')',
						'class_daylength'=>"no_val",
						'start_month'=>$_event_date_HTML['start_month']
					);
				}

			return $_event_date_HTML;
		}


	/** GENERATE individual event data	for event list array */
		public function generate_event_data(
			$event_list_array,
			$focus_month_beg_range='',
			$FOCUS_month_int='',
			$FOCUS_year_int='',
			$eventCardData= true
		){

			$months_event_array='';

			// Initial variables
				$wp_time_format = get_option('time_format');
				$__shortC_arg = $this->shortcode_args;
				
				// user interavtion for the calendar
					$calendar_ux_val = !empty($__shortC_arg['ux_val'])? $__shortC_arg['ux_val']: '0';

				$__count=0;

				// EVENT CARD open by default variables
					$_is_eventCardOpen = (!empty($__shortC_arg['evc_open']) && $__shortC_arg['evc_open']=='yes' )? true: ( $this->is_eventcard_open? true:false);
					$eventcard_script_class = ($_is_eventCardOpen)? "gmaponload":null;
					$this->is_eventcard_open = false;

				// check featured events are prioritized
				$__feature_events = (!empty($__shortC_arg['ft_event_priority']) && $__shortC_arg['ft_event_priority']!='no')?true:false;
			
			// Number of activated taxnomonies v 2.2.15
				$_active_tax = evo_get_ett_count($this->evopt1);

			// eventCARD HTML
			require_once(AJDE_EVCAL_PATH.'/includes/eventon_eventCard.php');
			require_once(AJDE_EVCAL_PATH.'/includes/eventon-eventTop.php');

			$dateTime = new evo_datetime();

			// calendar event default values
			$calendar_defaults = $this->helper->get_calendar_defaults();
			
			$eventop_fields = $calendar_defaults['eventtop_fields'];

			// EACH EVENT
			if(is_array($event_list_array) ){
			foreach($event_list_array as $event_):
				//print_r($event_);

				// Intials
					$html_event_detail_card='';
					$_eventClasses = $_eventInClasses = array();
					$_eventAttr = $_eventInAttr = array();

					$__count++;
					$event_id = $event_['event_id'];
					$event_start_unix = $event_['event_start_unix'];
					$event_end_unix = $event_['event_end_unix'];
					$event_type = $event_['event_type'];
					$ev_vals = $event_['event_pmv'];

					$event = get_post($event_id);

					$_eventInClasses[] = $eventcard_script_class;

				// set how a single event would interact
					$event_ux_val = (!empty($ev_vals['_evcal_exlink_option']) )?$ev_vals['_evcal_exlink_option'][0]:1;
					$event_permalink = get_permalink($event_id);
					// calendar UX val will override individual event ux val
					$event_ux_val = ($calendar_ux_val !='0')? $calendar_ux_val:
						( (!empty($__shortC_arg['tiles']) && $__shortC_arg['tiles']=='yes' && $event_ux_val==1)? 3:	$event_ux_val );

				// whether eventcard elements need to be included or not
					$card_for_cal = ($calendar_ux_val=='4' || $calendar_ux_val=='X')? false: true; // whether calendar call for card
					$_event_card_on = ($card_for_cal || ($event_ux_val!= '4' && $event_ux_val!= '2') )? true:false;
						$_event_card_on = ($_is_eventCardOpen)? true: $_event_card_on;// if event card is forced to open then

						// override by whats passed to function
						$_event_card_on = (!$eventCardData)? false: $_event_card_on;

					$html_tag = ($event_ux_val=='1')? 'div':'a';
					$html_tag = ($_event_card_on)? 'a':$html_tag;

				// year/month long or not
					$__year_long_event = $this->helper->evo_meta('evo_year_long', $ev_vals,'tf');
					$__month_long_event = $__year_long_event? false: ( ($this->helper->evo_meta('_evo_month_long', $ev_vals,'tf'))? true:0);

				// define variables
					$ev_other_data = $ev_other_data_top = $html_event_type_info= $_event_date_HTML=$_eventcard= $html_event_type_2_info =''; $_is_end_date=true;

				// UNIX date values
					$DATE_start_val = eventon_get_formatted_time($event_start_unix);
					if(empty($event_end_unix)){
						$_is_end_date=false;
						$DATE_end_val= $DATE_start_val;
					}else{
						$DATE_end_val = eventon_get_formatted_time($event_end_unix);
					}

				// if this event featured
					$_eventInClasses['__featured'] = $this->helper->evo_meta('_featured', $ev_vals,'tf');
					$_eventInClasses['_completed'] = $this->helper->evo_meta('_completed', $ev_vals,'tf');
					$_eventInClasses['_cancel'] = $this->helper->evo_meta('_cancel', $ev_vals,'tf');

				// GET: repeat interval for this event
					$__repeatInterval = $this->helper->get_ri_for_event($event_);
					$is_recurring_event = evo_check_yn($ev_vals, 'evcal_repeat');
					//print_r($__repeatInterval);

				// Unique ID generation
					$unique_varied_id = 'evc'.$event_start_unix.(uniqid()).$event_id;
					$unique_id = 'evc_'.$event_start_unix.$event_id;

				// All day event variables
					$_is_allday = $this->helper->evo_meta('evcal_allday',$ev_vals,'tf');
					$_hide_endtime = $this->helper->evo_meta('evo_hide_endtime',$ev_vals,'tf');
					$evcal_lang_allday = $this->lang( 'evcal_lang_allday', 'All Day');

				/*
					evo_hide_endtime
					NOTE: if its set to hide end time, meaning end time and date would be empty on wp-admin, which will fall into same start end month category.
				*/

				$_event_date_HTML = $this->generate_time_($DATE_start_val, $DATE_end_val, $ev_vals, $evcal_lang_allday, $focus_month_beg_range, $FOCUS_month_int, $event_start_unix, $event_end_unix);

				// HOOK for addons
					$_event_date_HTML= apply_filters('eventon_eventcard_date_html', $_event_date_HTML, $event_id);

				// EACH DATA FIELD
					// EVENT FEATURES IMAGE
						$img_id =get_post_thumbnail_id($event_id);
						$img_src = $img_med_src = $img_thumb_src ='';
						if($img_id!=''){
							$img_src = wp_get_attachment_image_src($img_id,'full');
							$img_med_src = wp_get_attachment_image_src($img_id,'medium');
							$img_thumb_src = wp_get_attachment_image_src($img_id,'thumbnail');
						}elseif( !empty($calendar_defaults['image'])){
							$img_src = $img_med_src = $img_thumb_src = array(0=>$calendar_defaults['image']);
						}
							// append to eventcard array
							if(!empty($img_src) ){
								$_eventcard['ftimage'] = array(
									'img'=>$img_src,
									'hovereffect'=> !empty($this->evopt1['evo_ftimghover'])? $this->evopt1['evo_ftimghover']:null,
									'clickeffect'=> (!empty($this->evopt1['evo_ftimgclick']))? $this->evopt1['evo_ftimgclick']:null,
									'min_height'=>	(!empty($this->evopt1['evo_ftimgheight'])? $this->evopt1['evo_ftimgheight']: 400),
									'ftimg_sty'=> (!empty($this->evopt1['evo_ftimg_height_sty'])? $this->evopt1['evo_ftimg_height_sty']: 'minimized'),
								);
							}

					// EVENT DESCRIPTION
						$evcal_event_content =(isset($event->post_content)?$event->post_content:'');

						if(!empty($evcal_event_content) ){
							$event_full_description = $evcal_event_content;
						}else{
							// event description compatibility from older versions.
							$event_full_description =(!empty($ev_vals['evcal_description']))?$ev_vals['evcal_description'][0]:null;
						}
						if(!empty($event_full_description) ){

							$except = $event->post_excerpt;
							$event_excerpt = eventon_get_event_excerpt($event_full_description, 30, $except);

							$_eventcard['eventdetails'] = array(
								'fulltext'=>$event_full_description,
								'excerpt'=>$event_excerpt,
							);
						}

					// EVENT LOCATION
						$location_terms = wp_get_post_terms($event_id, 'event_location');
						$location_address = $location_name = $lonlat = false;
						if ( $location_terms && ! is_wp_error( $location_terms ) ){
							$evo_location_tax_id =  $location_terms[0]->term_id;
							$LocTermMeta = get_option( "taxonomy_$evo_location_tax_id");

							$location_address = !empty( $LocTermMeta['location_address'])? $LocTermMeta['location_address']: false;

							// location name
								$location_name = stripslashes( $location_terms[0]->name );
							// Lat Long
								$lonlat = ( !empty( $LocTermMeta['location_lat']) && !empty( $LocTermMeta['location_lon']) )?
									'data-latlng="'.$LocTermMeta['location_lat'].','.$LocTermMeta['location_lon'].'" ': null;

							// Location Image
								$loc_img_id = !empty( $LocTermMeta['evo_loc_img'])? $LocTermMeta['evo_loc_img']: false;
								if(!empty($loc_img_id)){
									$_eventcard['locImg'] = array(
										'id'=>$loc_img_id,
										'fullheight'=> (!empty($this->evopt1['evo_locimgheight'])? $this->evopt1['evo_locimgheight']: 400),
									);

									// location name and address
									if( evo_check_yn($ev_vals, 'evcal_name_over_img') && !empty($location_name)){
										$_eventcard['locImg']['locName']=$location_name;
										if(!empty($location_address))
											$_eventcard['locImg']['locAdd']=$location_address;
									}
								}
						}

						$_eventcard['timelocation'] = array(
							//'timetext'=>$dateTime->get_formatted_smart_time($event_start_unix, $event_end_unix, $ev_vals),
							'timetext'=>$_event_date_HTML['html_prettytime'],
							'timezone'=>(!empty($ev_vals['evo_event_timezone'])? $ev_vals['evo_event_timezone'][0]:null),
							'address'=>$location_address,
							'location_name'=> ((!empty($ev_vals['evcal_hide_locname']) && $ev_vals['evcal_hide_locname'][0] == 'yes')?'':$location_name),
							'location_link'=> (!empty($LocTermMeta['evcal_location_link'])? $LocTermMeta['evcal_location_link']: null )
						);

					// Repeat series
						if($is_recurring_event && !empty($ev_vals['_evcal_rep_series']) && $ev_vals['_evcal_rep_series'][0] == 'yes'){
							$repeat_intervals = (!empty($ev_vals['repeat_intervals']))?
								(is_serialized($ev_vals['repeat_intervals'][0])? unserialize($ev_vals['repeat_intervals'][0]): $ev_vals['repeat_intervals'][0] ) :null;

							if($repeat_intervals){
								$future_intervals = array();
								foreach($repeat_intervals as $ri=>$interval){
									if($ri>$__repeatInterval)
										$future_intervals[$ri]= $interval;
								}
								if(count($future_intervals)>0){
									//print_r($future_intervals);
									$_eventcard['repeats'] = array(
										'repeat_interval' => $__repeatInterval,
										'future_intervals'=>$future_intervals,
										'date_format'=>$dateTime->wp_date_format,
										'time_format'=>$dateTime->wp_time_format,
									);
								}
							}
						}

					// GOOGLE maps
						if( ($this->google_maps_load) && ($location_address || !empty($lonlat)) && (!empty($ev_vals['evcal_gmap_gen']) && $ev_vals['evcal_gmap_gen'][0]=='yes') ){

							$_eventcard['gmap'] = array(
								'id'=>$unique_varied_id,
							);

							// GET directions
							if(!empty($ev_vals['evcal_location']))
								$_eventcard['getdirection'] = array('fromaddress'=>$ev_vals['evcal_location'][0]);
						}else{	$_eventInAttr['data-gmap_status'] = 'null';	}

					// PAYPAL Code
						if(!empty($ev_vals['evcal_paypal_item_price'][0]) && $this->evopt1['evcal_paypal_pay']=='yes'
							&& !empty($this->evopt1['evcal_pp_email'])){

							$_eventcard['paypal'] = array(
								'title'=>$event->post_title ,
								'price'=>$ev_vals['evcal_paypal_item_price'][0],
								'text'=> (!empty($ev_vals['evcal_paypal_text'])? $ev_vals['evcal_paypal_text'][0]: null),
								'email'=>(!empty($ev_vals['evcal_paypal_email'])? $ev_vals['evcal_paypal_email'][0]: $this->evopt1['evcal_pp_email']),
								'estart'=> ($event_start_unix),
								'evvals'=>$ev_vals
							);
						}

					// Event Organizer
						$organizer_terms = wp_get_post_terms($event_id, 'event_organizer');
						$organizer_term_id = $organizer_name = false;
						if($organizer_terms && ! is_wp_error( $organizer_terms )){
							$organizer_term_id = $organizer_terms[0]->term_id;
							$organizer_name = $organizer_terms[0]->name;
						}

						$hideOrganizer_from_eventCard = (!empty($ev_vals['evo_evcrd_field_org']) && $ev_vals['evo_evcrd_field_org'][0] == 'yes')? true: false;

						if($organizer_term_id && !$hideOrganizer_from_eventCard){
							$_eventcard['organizer'] = array(
								'organizer_term_id'=>$organizer_term_id,
								'organizer_name'=>$organizer_name,
								'openinnewwindow'=>(!empty($ev_vals['_evocal_org_exlink_target'])? $ev_vals['_evocal_org_exlink_target'][0]: false)
							);
						}

					// Custom fields
						$_cmf_count = evo_retrieve_cmd_count($this->evopt1);
						for($x =1; $x<$_cmf_count+1; $x++){
							if( !empty($this->evopt1['evcal_ec_f'.$x.'a1']) && !empty($this->evopt1['evcal__fai_00c'.$x])	&& !empty($ev_vals["_evcal_ec_f".$x."a1_cus"])	){

								// check if hide this from eventCard set to yes
								if(empty($this->evopt1['evcal_ec_f'.$x.'a3']) || $this->evopt1['evcal_ec_f'.$x.'a3']=='no'){

									$faicon = $this->evopt1['evcal__fai_00c'.$x];

									$_eventcard['customfield'.$x] = array(
										'imgurl'=>$faicon,
										'x'=>$x,
										'value'=>$ev_vals["_evcal_ec_f".$x."a1_cus"][0],
										'valueL'=>( (!empty($ev_vals["_evcal_ec_f".$x."a1_cusL"]))?
											$ev_vals["_evcal_ec_f".$x."a1_cusL"][0]:null ),
										'_target'=>( (!empty($ev_vals["_evcal_ec_f".$x."_onw"]))?
											$ev_vals["_evcal_ec_f".$x."_onw"][0]:null ),
										'type'=>$this->evopt1['evcal_ec_f'.$x.'a2'],
										'visibility_type'=> (!empty($this->evopt1['evcal_ec_f'.$x.'a4'])? $this->evopt1['evcal_ec_f'.$x.'a4']: 'all')
									);
								}
							}
						}

					// LEARN MORE and ICS
						$_eventcard['learnmoreICS'] = array(
							'event_id'=>$event_id,
							'learnmorelink'=>( (!empty($ev_vals['evcal_lmlink']))? $ev_vals['evcal_lmlink'][0]: null),
							'learnmore_target'=> ((!empty($ev_vals['evcal_lmlink_target'])  && $ev_vals['evcal_lmlink_target'][0]=='yes')? 'target="_blank"':null),
							'estart'=> ($event_start_unix),
							'eend'=>($event_end_unix),
							'etitle'=>(isset($event->post_title)?$event->post_title:''),
							'excerpt'=> ( !empty($event_full_description)? eventon_get_normal_excerpt($event_full_description, 30): (isset($event->post_title)?$event->post_title:'') ),
							'evals'=>$ev_vals,
						);

				// =======================
				/** CONSTRUCT the EVENT CARD	 **/
					// filter hook for eventcard content array - updated 2.3.21
						$_eventcard = apply_filters('eventon_eventcard_array', $_eventcard, $ev_vals, $event_id, $__repeatInterval);

					if($_event_card_on && !empty($_eventcard) && count($_eventcard)>0){

						// if an order is set reorder things
						if(!empty($this->evopt1['evoCard_order']))
							$_eventcard = $this->helper->eventcard_sort($_eventcard, $this->evopt1 );

						ob_start();

						echo "<div class='event_description evcal_eventcard ".( $_is_eventCardOpen?'open':null)."' ".( $_is_eventCardOpen? 'style="display:block"':'style="display:none"').">";

						echo  eventon_eventcard_print($_eventcard, $this->evopt1, $this->evopt2);


						// (---) hook for addons
						do_action('eventon_eventcard_additions', $event_id, (isset($this->__calendar_type)? $this->__calendar_type:''), (isset($event->post_title)?$event->post_title:''), $event_full_description, $img_thumb_src, $__repeatInterval
						);

						echo "</div>";

						$html_event_detail_card = ob_get_clean();

					}

					/** Trigger attributes **/
						$event_description_trigger =  "desc_trig";
						$_eventInAttr['data-gmtrig'] = (!empty($ev_vals['evcal_gmap_gen']) && $ev_vals['evcal_gmap_gen'][0]=='yes')? '1':'0';

					// Generate tax terms for event top
						$html_event_type_tax_ar = array();
						$_tax_names_array = evo_get_localized_ettNames('',$this->evopt1,$this->evopt2);
						//$_tax_names_array = evo_get_ettNames($this->evopt1);
						//print_r($_tax_names_array);
						$evcal_terms_ ='';

						// event _type 1 terms - this is also used for etc_override
							$ett_terms = wp_get_post_terms($event_id,'event_type');

						if(!empty($eventop_fields)){
							// foreach active tax
							for($b=1; $b<=$_active_tax; $b++){
								$__tx_content = '';

								$__tax_slug = 'event_type'.($b==1?'':'_'.$b);
								$__tax_fields = 'eventtype'.($b==1?'':$b);


								if(in_array($__tax_fields,$eventop_fields)  ){

									$evcal_terms = (!empty($evcal_terms_) && $b==1)? $ett_terms: wp_get_post_terms($event_id,$__tax_slug);
									if($evcal_terms){

										$__tax_name = $_tax_names_array[$b];

										$__tx_content .="<span class='evcal_event_types ett{$b}'><em><i>".$__tax_name.":</i></em>";
										$i=1;
										foreach($evcal_terms as $termA):
											// get translated tax term name
											$term_name = $this->lang('evolang_'.$__tax_slug.'_'.$termA->term_id, $termA->name);

											// event type 1 tax icon
												$icon_str = $this->helper->get_tax_icon($__tax_slug , $termA->term_id , $this->evopt1);

											// tax term slug as class name
											$_eventInClasses[] = 'evo_'.$termA->slug;
											$__tx_content .="<em data-filter='{$__tax_slug}'>".$icon_str.$term_name.( count($evcal_terms)!=$i? ',':'')."</em>";
											$i++;
										endforeach;
										$__tx_content .="<i class='clear'></i></span>";

										$html_event_type_tax_ar[$b] = $__tx_content;
									}
								}
							}
						}

						$_html_tax_content = (count($html_event_type_tax_ar)>0 )? implode('', $html_event_type_tax_ar):null;

					// event color
						$event_color = eventon_get_hex_color($ev_vals, $calendar_defaults['color']);

						// override event colors
						if(!empty($__shortC_arg['etc_override']) && $__shortC_arg['etc_override']=='yes' && !empty($ett_terms)){
							$ev_id = $ett_terms[0]->term_id;
							$ev_color = get_option( "evo_et_taxonomy_$ev_id" );

							$event_color = (!empty($ev_color['et_color']))?
								$ev_color['et_color']:$event_color;

							// remove additional '#' in the hex code
							$event_color = '#'.str_replace('#', '', $event_color);
						}

					// if UX to be open in new window then use link to single event or that link
						$link_append = array(); $_link_text  ='';
						if(!empty($__shortC_arg['lang']) && $__shortC_arg['lang']!='L1'){
							$link_append['l'] = $__shortC_arg['lang'];
						}

						// append repeat interval value to event link
						$link_append['ri']= $__repeatInterval;

						if(!empty($link_append)){
							foreach($link_append as $lp=>$lpk){
								if($lp=='ri' && $lpk=='0') continue;
								$_link_text .= $lp.'='.$lpk.'&';
							}
						}

						// passing URL variables values
						$_link_text_append =  (strpos($event_permalink, '?')=== false)?'?':'&';
						$_link_text = (!empty($_link_text))?
							$_link_text_append.$_link_text: null;

						$_eventInAttr['rest'][] = (!empty($ev_vals['evcal_exlink']) && $event_ux_val!='1' )?
							'data-exlk="1" href="'.$ev_vals['evcal_exlink'][0].$_link_text.'"'
							:'data-exlk="0"';

					// target
					$_eventInAttr['rest'][] = (!empty($ev_vals['_evcal_exlink_target'])  && $ev_vals['_evcal_exlink_target'][0]=='yes')? 'target="_blank"':null;

					// EVENT LOCATION
						if( $location_name && ($location_address || $lonlat) ){

							// location as LON LAT
							$event_location_variables = ((!empty($lonlat))? $lonlat:null ). ' data-location_address="'.$location_address.'" ';

							// conditional schema data for event
							if(!empty($this->evopt1['evo_schema']) && $this->evopt1['evo_schema']=='yes'){
								$__scheme_data_location ='';
							}else{
								$__scheme_data_location = '
									<item style="display:none" itemprop="location" itemscope itemtype="http://schema.org/Place">
										'. ( !empty($location_name)? '<span itemprop="name">'.$location_name.'</span>':'').'
										<span itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
											<item itemprop="streetAddress">'.$location_address.'</item>
										</span>
									</item>';
							}

							$ev_location = (!empty($location_address))?
								'<em class="evcal_location" '.( (!empty($lonlat))? $lonlat:null ).' data-add_str="'.$location_address.'">'.$location_address.'</em>':null;
							// location type
							$event_location_variables .= (!empty($lonlat))? 'data-location_type="lonlat"': 'data-location_type="address"';
							// location name
							$event_location_variables .= (!empty($ev_vals['evcal_location_name']))? ' data-location_name="'.$ev_vals['evcal_location_name'][0].'"':null;
							// location status
							$event_location_variables .= ' data-location_status="true"';
						}else{
							// location status
							$ev_location = $event_location_variables= $__scheme_data_location= null;
							$event_location_variables .= ' data-location_status="false"';

						}

					// EVENT tags
						$_event_tags = wp_get_post_tags($event_id);

				// EVENT TOP
					$eventtop_html=$eventop_fields_= $__eventtop = '';
					// CHECK for event top fields array
						$eventop_fields_ = (is_array($eventop_fields) )? true:false;
					// featured image
						if((!empty($img_thumb_src) && !empty($__shortC_arg['show_et_ft_img']) && $__shortC_arg['show_et_ft_img']=='yes') ){
							$__eventtop['ft_img'] = array(
								'url'=>$img_thumb_src[0],
								'url_med'=>(!empty($img_med_src[0])? $img_med_src[0]:''),
								'url_full'=>$img_src
							);
						}

					// date block
						if(!$__year_long_event){
							// date number
								$___day_name = ($eventop_fields_ && in_array('dayname',$eventop_fields))?
									"<em class='evo_day' >".$DATE_start_val['D']."</em>":
									null;
							$__eventtop['day_block']= array(
								'start'=>$DATE_start_val,
								'color'=>$event_color,
								'day_name'=>$___day_name,
								'html'=>$_event_date_HTML,
								'yearlong'=>$__year_long_event,'monthlong'=>$__month_long_event,
							);
						}
					// event titles
						$__eventtop['titles']= array(
							'yearlong'=>$__year_long_event,
							'monthlong'=>$__month_long_event,
							'loc_vars'=>$event_location_variables,
							'title'=>(isset($event->post_title)? $event->post_title:''),
							'cancel'=>$_eventInClasses['_cancel'],
							'cancel_reason'=>($this->helper->evo_meta('_cancel_reason', $ev_vals,'null')),
							'subtitle'=> (!empty($ev_vals['evcal_subtitle'])? $ev_vals['evcal_subtitle'][0]:null),
							'evvals'=>$ev_vals,'ri'=>$__repeatInterval,
							'eventid'=>$event_id,
						);

					// below title
						$__eventtop['belowtitle']= array(
							'fields_'=>$eventop_fields_,
							'fields'=>$eventop_fields,
							'evvals'=>$ev_vals,
							'html'=> $_event_date_HTML,
							'location'=>$ev_location,
							'locationname'=> (!empty($ev_vals['evcal_location_name'])?
							$ev_vals['evcal_location_name'][0]:null),
							'tax'=>$_html_tax_content,
							'tags'=>$_event_tags,
							'cmdcount'=>$_cmf_count,
							'timezone'=>(!empty($ev_vals['evo_event_timezone'])? $ev_vals['evo_event_timezone'][0]:null)
						);

					// close eventtop
							$_passVal = array(
								'eventid'=>$event_id,
								'ri'=>$__repeatInterval,
								'fields_'=>$eventop_fields_,
								'fields'=>$eventop_fields,
							);
							$__eventtop = apply_filters('eventon_eventtop_one', $__eventtop, $ev_vals, $_passVal);
						$__eventtop['close1'] = array();
							$__eventtop = apply_filters('eventon_eventtop_two', $__eventtop, $ev_vals, $_passVal);
						$__eventtop['close2'] = array();


					// CONSTRUCT event top html
					if(!empty($__eventtop) && count($__eventtop)>0){
						ob_start();

						echo  eventon_get_eventtop_print($__eventtop, $this->evopt1, $this->evopt2);
						$eventtop_html = ob_get_clean();
						$eventtop_html = apply_filters('eventon_eventtop_html',$eventtop_html);
					}else{
						$eventtop_html=null;
					}

				// (---) hook for addons
				$html_info_line = apply_filters('eventon_event_cal_short_info_line', $eventtop_html);

				// SCHEME SEO
					// conditional schema data
					if(!empty($this->evopt1['evo_schema']) && $this->evopt1['evo_schema']=='yes'){// remove schema
						$__scheme_data ='<div class="evo_event_schema" style="display:none" >
							<a href="'.$event_permalink.'"></a></div>';
						$__scheme_attributes = '';
					}else{
						//$event_permalink = ($calendar_defaults['single_addon'])? $event_permalink.$_link_text: "http://" . $_SERVER['HTTP_HOST']  . $_SERVER['REQUEST_URI'];
						$event_permalink = ($calendar_defaults['single_addon'])? $event_permalink.$_link_text: get_permalink($event_id);
						$__scheme_data =
							'<div class="evo_event_schema" style="display:none" >
							<a href="'.$event_permalink.'" itemprop="url"></a>
							<time itemprop="startDate" datetime="'.$DATE_start_val['Y'].'-'.$DATE_start_val['n'].'-'.$DATE_start_val['j'].'"></time>
							<time itemprop="endDate" datetime="'.$DATE_end_val['Y'].'-'.$DATE_end_val['n'].'-'.$DATE_end_val['j'].'"></time>'.
							$__scheme_data_location.
							'</div>';
						$__scheme_attributes = "itemscope itemtype='http://schema.org/Event'";
					}

				// CLASES - attribute
					$_eventClasses [] = 'eventon_list_event';
					$_eventClasses [] = (isset($event_['event_past']) && $event_['event_past'])? 'past_event':'';
					$_eventClasses [] = 'event';

					$_eventInClasses[] = $_event_date_HTML['class_daylength'];
					$_eventInClasses[] = 'evcal_list_a';

					$_eventInClasses_ = $this->helper->get_eventinclasses(array(
						'existing_classes'=>$_eventInClasses,
						'show_et_ft_img'=>(!empty($__shortC_arg['show_et_ft_img'])?$__shortC_arg['show_et_ft_img']:'no'),
						'img_thumb_src'=>$img_thumb_src,
						'event_type'=>$event_type,
						'event_description_trigger'=>$event_description_trigger,
						'monthlong'=>$__month_long_event,
						'yearlong'=>$__year_long_event,
					));

					// show limit styles
					if( !empty($__shortC_arg['show_limit']) && $__shortC_arg['show_limit']=='yes' && !empty($__shortC_arg['event_count']) && $__shortC_arg['event_count']>0 && $__count> $__shortC_arg['event_count']){

						$_eventAttr['style'][] = "display:none;";
						$_eventClasses[] = 'evSL';
					}

					$eventbefore = '';
					// TILES STYLE
						if(!empty($__shortC_arg['tiles']) && $__shortC_arg['tiles'] =='yes'){
							// boxy event colors
							// if featured image exists for an event
							if(!empty($img_med_src) && $__shortC_arg['tile_bg']==1){
								$_this_style = 'background-image: url('.$img_med_src[0].'); background-color:'.$event_color.';';
								$_eventClasses[] = 'hasbgimg';
							}else{
								$_this_style = 'background-color: '.$event_color.';';
							}

							// support different tile style
							if(!empty($__shortC_arg['tile_style']) && $__shortC_arg['tile_style'] !='0'){
								$eventbefore = '<div class="evo_topbar" style="'.$_this_style.'"></div>';
							}else{
								$_eventAttr['style'][] = $_this_style;
							}

							// tile height
							if($__shortC_arg['tile_height']!=0)
								$_eventAttr['style'][] = 'height: '.$__shortC_arg['tile_height'].'px;';

							// tile count
							if($__shortC_arg['tile_count']!=2){
								//$perct = (int)(100/$__shortC_arg['tile_count']);
								//$_eventAttr['style'][] = 'width: '.$perct.'%;';
							}
						}else{
							$_eventInAttr['style'][] = 'border-color: '.$event_color.';';
						}

				$_eventAttr['id'] = 'event_'.$event_id;
				$_eventAttr['class'] = $this->helper->implode($_eventClasses);
				$_eventAttr['data-event_id'] = $event_id;
				$_eventAttr['data-ri'] = $__repeatInterval;
				$_eventAttr['data-time'] = $event_start_unix.'-'.$event_end_unix;
				$_eventAttr['data-colr'] = $event_color;
				$_eventAttr['rest'][] = $__scheme_attributes;

				$atts = $this->helper->get_attrs($_eventAttr);

				$_eventInAttr['id']=$unique_id;
				$_eventInAttr['class']=$_eventInClasses_;
				$_eventInAttr['data-ux_val']=$event_ux_val;

				$attsIn = $this->helper->get_attrs($_eventInAttr);

				// event item html
					$html_tag_start = ($html_tag=='a')?'p class="desc_trig_outter"><a': $html_tag;
					$html_tag_end = ($html_tag=='a')?'p></a': $html_tag;

				// build the event HTML
				$event_html_code="<div {$atts}>{$eventbefore}{$__scheme_data}
				<{$html_tag_start} {$attsIn}>{$html_info_line}</{$html_tag_end}>".$html_event_detail_card."<div class='clear end'></div></div>";

				// prepare output
				$months_event_array[]=array(
					'event_id'=>$event_id,
					'srow'=>$event_start_unix,
					'erow'=>$event_end_unix,
					'content'=>$event_html_code
				);

			endforeach;

			}else{// if event list is not an array
				$months_event_array;
			}

			return $months_event_array;
		}


	// generate event data for all eventON events
		public function get_all_event_data(){
			global $eventon;
			$evo_opt = $eventon->frontend->evo_options;

			$events = new WP_Query(array(
				'posts_per_page'=>-1,
				'post_type' => 'ajde_events',
				'post_status'=>'any'			
			));

			$designated_meta_fields = array(
				'publish_status'=>'publish_status',
				'evcal_event_color'=>'color',
				'evcal_subtitle'=>'event_subtitle',
				'evcal_lmlink'=>'learnmore_link',
				'_featured'=>'featured',
				'all_day'=>'all_day_event',
				'evo_year_long'=>'year_long_event',
				'_evo_month_long'=>'month_long_event'
			);
			

			$output = array();
			if($events->have_posts()):
				while($events->have_posts()): $events->the_post();
					$event_id = $events->post->ID;
					$ev_vals = get_post_meta($event_id);

					// event name
						$output[$event_id]['name'] = get_the_title();

					// date times
						$row_start = (!empty($ev_vals['evcal_srow']))?	$ev_vals['evcal_srow'][0] :null;
						$row_end = ( !empty($ev_vals['evcal_erow']) )?	$ev_vals['evcal_erow'][0]:$row_start;
						$output[$event_id]['start']= $row_start;
						$output[$event_id]['end']= $row_end;

					// details
						$output[$event_id]['details'] = $eventon->frontend->filter_evo_content(get_the_content()); 

					// repeating event
						if( !empty($ev_vals['evcal_repeat']) && $ev_vals['evcal_repeat'][0]=='yes' && !empty($ev_vals['repeat_intervals']))
							$output[$event_id]['repeats'] = unserialize($ev_vals['repeat_intervals'][0]);

					// Event timezone
						if( !empty($ev_vals['evo_event_timezone']))
							$output[$event_id]['event_timezone'] = $ev_vals['evo_event_timezone'][0];

					// designated meta fields
						foreach($designated_meta_fields as $field=>$name){
							if(!empty($ev_vals[$field]))
								$output[$event_id][$name] = $ev_vals[$field][0];
						}

					// image
						if(has_post_thumbnail()){
							$img_id =get_post_thumbnail_id($event_id);
							$img_src = wp_get_attachment_image_src($img_id,'full');
							if($img_src) $output[$event_id]['image_url'] = $img_src[0];
						}

					// location
						$location_terms = wp_get_post_terms($event_id, 'event_location');
						if ( $location_terms && ! is_wp_error( $location_terms ) ){
							$location_tax_id =  $location_terms[0]->term_id;
							$LocTermMeta = get_option( "taxonomy_$location_tax_id");

							// location taxonomy id
								$output[$event_id]['location_tax'] = $location_tax_id;

							$output[$event_id]['location_name'] = $location_terms[0]->name;

							// location address
							if(!empty( $LocTermMeta['location_address']))
								$output[$event_id]['location_address'] = $LocTermMeta['location_address']; 

							// Lat Long
							if( !empty( $LocTermMeta['location_lat']) && !empty( $LocTermMeta['location_lon']) ){
								$output[$event_id]['location_lat'] = $LocTermMeta['location_lat'];
								$output[$event_id]['location_lon'] = $LocTermMeta['location_lon'];
							}								
						}

					// Organizer
						$organizer_terms = wp_get_post_terms($event_id, 'event_organizer');
						if ( $organizer_terms && ! is_wp_error( $organizer_terms ) ){
							$organizer_term_id =  $organizer_terms[0]->term_id;

							$orgTermMeta = get_option( "taxonomy_$organizer_term_id");

							// organizer initial
								$output[$event_id]['organizer_tax'] = $organizer_term_id;
								$output[$event_id]['organier_name'] = $organizer_terms[0]->name;
								
							// organizer address
							if(!empty( $orgTermMeta['evcal_org_address']))
								$output[$event_id]['organizer_address'] = $orgTermMeta['evcal_org_address']; 

							// organizer contact
							if(!empty( $orgTermMeta['evcal_org_contact']))
								$output[$event_id]['organizer_contact'] = $orgTermMeta['evcal_org_contact']; 
								
						}

					// Custom fields
						$_cmf_count = evo_retrieve_cmd_count($evo_opt);
						for($x =1; $x<$_cmf_count+1; $x++){
							if( !empty($evo_opt['evcal_ec_f'.$x.'a1']) && !empty($evo_opt['evcal__fai_00c'.$x])	&& !empty($ev_vals["_evcal_ec_f".$x."a1_cus"])	){

								// check if hide this from eventCard set to yes
								if(empty($evo_opt['evcal_ec_f'.$x.'a3']) || $evo_opt['evcal_ec_f'.$x.'a3']=='no'){
								
									$output[$event_id]['customfield_'.$x] =  array(
										'x'=>$x,
										'value'=>$ev_vals["_evcal_ec_f".$x."a1_cus"][0],
										'valueL'=>( (!empty($ev_vals["_evcal_ec_f".$x."a1_cusL"]))?
											$ev_vals["_evcal_ec_f".$x."a1_cusL"][0]:null ),
										'_target'=>( (!empty($ev_vals["_evcal_ec_f".$x."_onw"]))?
											$ev_vals["_evcal_ec_f".$x."_onw"][0]:null ),
										'type'=>$evo_opt['evcal_ec_f'.$x.'a2'],
										'visibility_type'=> (!empty($evo_opt['evcal_ec_f'.$x.'a4'])? $evo_opt['evcal_ec_f'.$x.'a4']: 'all')
									);
								}
							}
						}

					// event types
						for($y=1; $y<=evo_get_ett_count($evo_opt);  $y++){
							$_ett_name = ($y==1)? 'event_type': 'event_type_'.$y;
							$terms = get_the_terms( $event_id, $_ett_name );

							if ( $terms && ! is_wp_error( $terms ) ){
								foreach ( $terms as $term ) {
									$output[$event_id][$_ett_name][$term->term_id] = $term->name;
								}
							}
						}

					// all meta values
						$output[$event_id]['pmv'] = $ev_vals;
					
				endwhile;
				wp_reset_postdata();
			endif;

			return $output;
		}


	/**	 Add other filters to wp_query argument	 */
		public function apply_evo_filters_to_wp_argument($wp_arguments, $arguments){
			// -----------------------------
			// FILTERING events

			$filters = (!empty($arguments['filters']))? $arguments['filters']:
				(!empty($arguments)? $arguments: false);

			// values from filtering events
			if($filters!=false && is_array($filters)){

				// build out the proper format for filtering with WP_Query
				$cnt =0;
				$filter_tax['relation']='AND';
				foreach($filters as $filter){
					if(empty($filter['filter_type'])) continue;

					if($filter['filter_type']=='tax'){

						$filter_val = explode(',', $filter['filter_val']);

						if(empty($filter_val)) continue;

						$filter_field_type = apply_filters('eventon_filter_field_type', 'id', $filter['filter_name']);
						$filter_tax[] = array(
							'taxonomy'=>$filter['filter_name'],
							'field'=> $filter_field_type,
							'terms'=>$filter_val,
							'operator'=>(!empty($filter['filter_op'])? $filter['filter_op']: 'IN'),
						);
						$cnt++;
					}else{
						$filter_meta[] = array(
							'key'=>$filter['filter_name'],
							'value'=>$filter['filter_val'],
						);
					}
				}


				if(!empty($filter_tax)){

					// for multiple taxonomy filtering
					if($cnt>1){
						$filters_tax_wp_argument = array(
							'tax_query'=>$filter_tax
						);
					}else{
						$filters_tax_wp_argument = array(
							'tax_query'=>$filter_tax
						);
					}
					$wp_arguments = array_merge($wp_arguments, $filters_tax_wp_argument);
				}
				if(!empty($filter_meta)){
					$filters_meta_wp_argument = array(
						'meta_query'=>$filter_meta
					);
					$wp_arguments = array_merge($wp_arguments, $filters_meta_wp_argument);
				}
			}else{

				// For each event type category + location and organizer tax
				foreach($this->shell->get_all_event_tax() as $ety=>$event_type){
					// if the ett is  not empty and not equal to all
					if(!empty($ecv[$event_type]) && $ecv[$event_type] !='all'){
						$ev_type = explode(',', $ecv[$event_type]);
						$ev_type_ar = array(
								'tax_query'=>array(
								array('taxonomy'=>$event_type,
									'field'=>'id',
									'terms'=>$ev_type,
								) )
							);

						$wp_arguments = array_merge($wp_arguments, $ev_type_ar);
					}
				}
			}

			//print_r($wp_arguments);
			return $wp_arguments;
		}

	/**	 out put just the sort bar for the calendar	 */
		public function eventon_get_cal_sortbar($args, $sortbar=true){

			// define variable values
			$sorting_options = (!empty($this->evopt1['evcal_sort_options']))?$this->evopt1['evcal_sort_options']:array();

			$filtering_options = (!empty($this->evopt1['evcal_filter_options']))?$this->evopt1['evcal_filter_options']:array();
			$content='';

			$this->reused(); // update reusable variables real quikc


			// START the magic
			ob_start();

			// IF sortbar is set to be shown
			if($sortbar && (count($filtering_options)>0 || count($sorting_options)) ){
				echo ( $this->evcal_hide_sort!='yes' )? "<a class='evo_sort_btn'>".$this->lang('evcal_lang_sopt','Filter Events')."</a>":null;
			}

			// expand sort section by default or not
				$SO_display = (!empty($args['exp_so']) && $args['exp_so'] =='yes')? 'block': 'none';

				echo "<div class='eventon_sorting_section' style='display:{$SO_display}'>";
				if( $this->evcal_hide_sort!='yes' ){ // if sort bar is set to show

					// sorting section
					$evsa1 = array(
						'date'=>'Date',
						'title'=>'Title',
						'color'=>'Color',
						'posted'=>'Post Date'
					);
					$sort_options = array(	1=>'sort_date', 'sort_title','sort_color','sort_posted');
						$__sort_key = substr($args['sort_by'], 5);

					if(count($sorting_options)>0){
						echo "
						<div class='eventon_sort_line evo_sortOpt' >
							<div class='evo_sortby'><p>".$this->lang('evcal_lang_sort','Sort By').":</p></div>
							<div class='evo_srt_sel'><p class='fa'>".$this->lang('evcal_lang_s'.$__sort_key,$__sort_key)."</p>";

								$sorting_options['date'] = 'Date';

								if(!empty($sorting_options)){

									echo "<div class='evo_srt_options'>";
									$cnt =1;
									if(is_array($sorting_options) ){
										foreach($evsa1 as $so=>$sov){
											if(in_array($so, $sorting_options) || $so=='date' ){
											echo "<p data-val='sort_".$so."' data-type='".$so."' class='evs_btn ".( ($args['sort_by'] == $sort_options[$cnt])? 'evs_hide':null)."' >"
													.$this->lang('evcal_lang_s'.$so,$sov)
													."</p>";
											}
											$cnt++;
										}
									}
									echo "</div>";
								}// endif;

						echo "</div>";
						echo "<div class='clear'></div>
						</div>";
					}


				}

			$__text_all_ = $this->lang('evcal_lang_all', 'All');
			
			// EACH EVENT TYPE
				$__event_types = $this->shell->get_event_types();
				foreach($__event_types as $ety=>$event_type){
					$_filter_array[$ety]= $event_type;
				}
				$_filter_array['evloc']= 'event_location';
				$_filter_array['evorg']= 'event_organizer';
			
			// hook for additional filters
				$_filter_array = apply_filters('eventon_so_filters', $_filter_array);


			// filtering section
			$selectfilterType = (!empty($args['filter_type']) && $args['filter_type']=='select')? true: false;

			echo "<div class='eventon_filter_line ".($selectfilterType?'selecttype':'')."'>";

				foreach($_filter_array as $ff=>$vv){ // vv = event_type etc.

					$cats = get_categories(array( 'taxonomy'=>$vv));

					// filtering value filter is set to show
					if(in_array($vv, $filtering_options) && strpos($args[$vv], 'NOT-')=== false && $cats){
						//print_r($cats);
						$inside ='';

						// ALL; check whether this filter type value passed
							if($args[$vv]=='all'){ // show all filter type
								$__filter_val = 'all';
								$__text_all=$__text_all_;

								if(!$selectfilterType) $inside .= "<p class='evf_hide' data-filter_val='all'>{$__text_all}</p>";
																
								// each term
								foreach($cats as $ct){ 
									// event type 1 tax icon
										$icon_str = $this->helper->get_tax_icon($vv,$ct->term_id, $this->evopt1 );

									$term_name = $this->lang('evolang_'.$vv.'_'.$ct->term_id,$ct->name );
									if(!$selectfilterType){
										$inside .=  "<p class='". ($icon_str?'has_icon':'')."' data-filter_val='".$ct->term_id."' data-filter_slug='".$ct->slug."'>". $icon_str . $term_name."</p>";
									}else{
										$inside .=  "<p class='' ><input type='checkbox' data-filter_val='".$ct->term_id."' data-filter_slug='".$ct->slug."'/> ". $term_name."</p>";
									}
								}

							}else{
								$__filter_val = (!empty($args[$vv])? $args[$vv]: 'all');
								$__text_all = get_term_by('id', $args[$vv], $vv,ARRAY_N);
								$__text_all = $__text_all[1];

								// All terms value
								if(!$selectfilterType) $inside .= "<p class='evf_hide' data-filter_val='{$args[$vv]}'>{$__text_all}</p>";
								if(!$selectfilterType) $inside .=  "<p  data-filter_val='all'>{$__text_all_}</p>";

								foreach($cats as $ct){

									// skip shortcode via set filter values from showing in list
										if($ct->term_id == $__filter_val && !$selectfilterType) continue;
									
									// event type 1 tax icon
										$icon_str = $this->helper->get_tax_icon($vv,$ct->term_id, $this->evopt1 );

									$term_name = $this->lang('evolang_'.$vv.'_'.$ct->term_id,$ct->name );
									if(!$selectfilterType){
										$inside .=  "<p class='". ($icon_str?'has_icon':'')."' data-filter_val='".$ct->term_id."' data-filter_slug='".$ct->slug."'>". $icon_str . $term_name."</p>";
									}else{// checkbox select option
										$checked = ($__filter_val == $ct->term_id) ? 'checked="checked"':'';
										$inside .=  "<p class='{$ct->term_id}' ><input {$checked} type='checkbox' data-filter_val='".$ct->term_id."' data-filter_slug='".$ct->slug."'/> ". $term_name."</p>";
									}
								}
							}

						// only for event type taxonomies
						$_isthis_ett = (in_array($vv, $__event_types))? true:false;

						$ett_count = ($ff==1)? '':$ff;

						$lang__ = ($_isthis_ett)? $this->lang_array['et'.$ett_count]:$this->lang_array[$ff];

						// filter in or not
						$filter_op = 'IN';
						if(strpos($__filter_val, 'NOT-')!== false){
							$filter_op = 'NOT';
							$__filter_val = str_replace('NOT-', '', $__filter_val);
							//$__filter_val = substr($__filter_val, 4);
						}

						// update clickable text
						$__text_all = $selectfilterType? $lang__: $__text_all;


						echo "<div class='eventon_filter evo_sortOpt evo_sortList_{$vv}' data-filter_field='{$vv}' data-filter_val='{$__filter_val}' data-filter_type='tax' data-fl_o='{$filter_op}'>
							<div class='eventon_sf_field'><p>".$lang__.":</p></div>

							<div class='eventon_filter_selection'>
								<p class='filtering_set_val' data-opts='evs4_in'>{$__text_all}</p>
								<div class='eventon_filter_dropdown' style='display:none'>";

								echo $inside;

							echo "</div>
							</div><div class='clear'></div>
						</div>";
					}else{
						// if no tax values is passed
						if(!empty($args[$vv])){
							$taxFL = eventon_tax_filter_pro($args[$vv]);

							echo "<div class='eventon_filter' data-filter_field='{$vv}' data-filter_val='{$taxFL[0]}' data-filter_type='tax' data-fl_o='{$taxFL[1]}'></div>";
						}
					}
				}

				// for select filter type
				if($selectfilterType){
					echo "<p class='evo_filter_submit'>".__('Apply Filters')."</p>";
				}

				// (---) Hook for addon
				echo  do_action('eventon_sorting_filters', $content);

			echo "</div>"; // #eventon_filter_line

			echo "<div class='clear'></div>"; // clear

			echo "</div>"; // #eventon_sorting_section

			// (---) Hook for addon
			echo  do_action('eventon_below_sorts', $content, $args);

			// load bar for calendar
			echo "<div id='eventon_loadbar_section'><div id='eventon_loadbar'></div></div>";

			// (---) Hook for addon
			echo  do_action('eventon_after_loadbar', $content, $args);


			return ob_get_clean();
		}

		//return tranlated language
		function lang($var, $default){
			$lang = !empty($this->shortcode_args['lang'])? $this->shortcode_args['lang']: 'L1';
			return eventon_get_custom_language($this->evopt2, $var,$default, $lang);
		}


	// SUPPORT functions

} // class EVO_generator


?>