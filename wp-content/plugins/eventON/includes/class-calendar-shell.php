<?php
/**
 * calendar outter shell content.
 *
 * @class 		evo_cal_shell
 * @version		2.3.11
 * @package		EventON/Classes
 * @category	Class
 * @author 		AJDE
 */

class evo_cal_shell {
	private $cal;
	public function __construct(){
		global $eventon;
		$this->cal = $eventon->evo_generator;

		add_action( 'init', array( $this, 'load_google_maps_api' ) );
	}

	// Load calendar required files
		public function load_evo_files(){
			global $eventon;
			$eventon->load_default_evo_scripts();
			$this->load_google_maps_api();
		}


	// Event types and other functions
		public function get_event_types(){
			$output;
			for($x = 1; $x< $this->cal->event_types ; $x++){
				$ab = ($x==1)? '':'_'.$x;
				$event_type = 'event_type'.$ab;

				$output[$x] = $event_type;
			}
			return $output;
		}
		public function get_extra_tax(){
			$output;
			$extras = apply_filters('eventon_extra_tax', array(
				'evloc'=>'event_location',
				'evorg'=>'event_organizer',
			));
			foreach($extras as $ff=>$vv){
				$output[] = $vv;
			}
			return $output;
		}
		public function get_all_event_tax(){
			return array_merge($this->get_event_types(), $this->get_extra_tax());
		}
		public function verify_eventtypes(){

			for($x= 3; $x<6; $x++){
				if( !empty($this->cal->evopt1['evcal_ett_'.$x]) && $this->cal->evopt1['evcal_ett_'.$x]=='yes'){
					$this->cal->event_types = $x+1;
				}else{
					break;
				}
			}
		}

	/**
	 * Shortcode variables that are available for calednar
	 * @return array array of all processed variables with values
	 */
		public function get_supported_shortcode_atts(){
			$args = array(
				'cal_id'=>'',
				'event_count'=>0,
				'month_incre'=>0,
				'number_of_events'=>5,
				'focus_start_date_range'=>'',
				'focus_end_date_range'=>'',
				'sort_by'=>'sort_date',		// sort_rand
				'filters'=>'',
				'filter_type'=>'',	// dropdown or select
				'fixed_month'=>0,
				'fixed_year'=>0,
					'hide_past'=>'no',
					'hide_past_by'=>'ee',	// ss | ee
				'show_et_ft_img'=>'no',
				'event_order'=>'ASC',
				'ft_event_priority'=>'no',
				'number_of_months'=>1,
				'hide_mult_occur'=>'no',
				'show_repeats'=>'no', // show repeating events while hide multiple occurance
				'show_upcoming'=>0,
				'show_limit'=>'no',		// show only event count but add view more
				'show_limit_redir'=>'',		// url to redirect show more button
					'tiles'=>'no',		// tile box style cal
					'tile_height'=>0,		// tile height
					'tile_bg'=>0,		// tile background
					'tile_count'=>2,		// tile count in a row
					'tile_style'=>0,		// tile style
					'layout_changer'=>'no',	// show option to change layout between tile and rows	

				'lang'=>'L1',
				'pec'=>'',				// past event cut-off
				'etop_month'=>'no',
				'evc_open'=>'no',		// open eventCard by default
				'ux_val'=>'0', 			// user interaction to override default user interaction values
				'etc_override'=>'no',	// even type color override the event colors
				'jumper'=>'no'	,		// month jumper
					'jumper_offset'=>'0', 	// jjumper start year offset
				'accord'=>'no',			// accordion
				'only_ft'=> 'no',		// only featured events
				'exp_so'=>'no',		// expand sort options by default
				'hide_so'=>'no',	// hide sort options
				'wpml_l1'=>'',		// WPML lanuage L1 = en
				'wpml_l2'=>'',		// WPML lanuage L2 = nl
				'wpml_l3'=>'',		// WPML lanuage L3 = es
				's'=>'',		// keywords to search
				'hide_arrows'=>'no',	// hide calendar arrows
				'members_only'=>'no'	// only visible for loggedin user
			);

			// each event type category
			foreach($this->get_event_types() as $ety=>$ett){
				$args[$ett] ='all';
			}

			// extra taxonomies
			foreach($this->get_extra_tax() as $tax){
				$args[$tax] ='all';
			}

			return apply_filters('eventon_shortcode_defaults', $args);
		}

	/**
	 * Shortcode arguments as attributed in HTML for the cal header
	 * @return string
	 */
		function shortcode_args_for_cal(){

			$arg = $this->cal->shortcode_args;
			$_cd='';
			//print_r($arg);

			$cdata = apply_filters('eventon_calhead_shortcode_args', array(
				'hide_past'=>$arg['hide_past'],
				'show_et_ft_img'=>$arg['show_et_ft_img'],
				'event_order'=>$arg['event_order'],
				'ft_event_priority'=>((!empty($arg['ft_event_priority']))? $arg['ft_event_priority']: null),
				'lang'=>$arg['lang'],
				'month_incre'=>$arg['month_incre'],
				'only_ft'=>((!empty($arg['only_ft']))? $arg['only_ft']:'no'),
				'evc_open'=>((!empty($arg['evc_open']))? $arg['evc_open']:'no'),
				'show_limit'=>((!empty($arg['show_limit']))? $arg['show_limit']:'no'),
				'etc_override'=>((!empty($arg['etc_override']))? $arg['etc_override']:'no'),
				'tiles'=>$arg['tiles'],
					'tile_height'=>$arg['tile_height'],
					'tile_bg'=>$arg['tile_bg'],
					'tile_count'=>$arg['tile_count'],
					'tile_style'=>$arg['tile_style'],
				's'=>((!empty($arg['s']))? $arg['s']:''),
				'members_only' => $arg['members_only']
			), $arg);

			foreach ($cdata as $f=>$v){
				$_cd .='data-'.$f.'="'.$v.'" ';
			}

			return "<div class='cal_arguments' style='display:none' {$_cd}></div>";

		}


	/**
	 * load google maps scrips
	 * @return
	 */
		function load_google_maps_api(){
			// google maps loading conditional statement
			if( !empty($this->cal->evopt1['evcal_cal_gmap_api']) && ($this->cal->evopt1['evcal_cal_gmap_api']=='yes') 	){
				if(!empty($this->cal->evopt1['evcal_gmap_disable_section']) && $this->cal->evopt1['evcal_gmap_disable_section']=='complete'){

					$this->cal->google_maps_load = false;

					wp_enqueue_script( 'eventon_init_gmaps_blank');
					wp_enqueue_script( 'eventon_gmaps_blank');
				}else{

					//update_option('evcal_gmap_load',true);
					$this->cal->google_maps_load = true;
					wp_enqueue_script( 'eventon_init_gmaps_blank');
					wp_enqueue_script('eventon_gmaps');
				}

			}else {

				//update_option('evcal_gmap_load',true);
				$this->cal->google_maps_load = true;

				wp_enqueue_script( 'evcal_gmaps');
				wp_enqueue_script( 'eventon_gmaps');
				wp_enqueue_script('eventon_init_gmaps');

				// load map files only to frontend
				if ( !is_admin() ){
					wp_enqueue_script( 'evcal_gmaps');
					wp_enqueue_script( 'eventon_gmaps');
					wp_enqueue_script( 'eventon_init_gmaps');
				}
			}
		}

	/**
	 * starting month and year values
	 * @return array focus month and year
	 */
		public function get_starting_monthYear(){
			$args = $this->cal->shortcode_args;
			extract($args);


			// *** GET STARTING month and year
			if($fixed_month!=0 && $fixed_year!=0){
				$focused_month_num = $fixed_month;
				$focused_year = $fixed_year;
			}else{
			// GET offset month/year values
				$this_month_num = date('n', current_time('timestamp'));
				$this_year_num = date('Y', current_time('timestamp'));


				if($month_incre !=0){

					$mi_int = (int)$month_incre;

					$new_month_num = $this_month_num +$mi_int;

					//month
					$focused_month_num = ($new_month_num>12)?
						$new_month_num-12:
						( ($new_month_num<1)?$new_month_num+12:$new_month_num );

					// year
					$focused_year = ($new_month_num>12)?
						$this_year_num+1:
						( ($new_month_num<1)?$this_year_num-1:$this_year_num );


				}else{
					$focused_month_num = $this_month_num;
					$focused_year = $this_year_num;
				}

			}

			//echo strtotime($month_incre.' month', $current_timestamp);
			return array('focused_month_num'=>$focused_month_num, 'focused_year'=>$focused_year);
		}

	/**
	 * sort events list arrau
	 * @param  array $events_array list of events
	 * @param  array $args         shortcode arguments
	 * @return array               sorted events list
	 */
		public function evo_sort_events_array($events_array, $args=''){

			$ecv = $this->cal->process_arguments($args);

			//echo $ecv['sort_by'];

			if(is_array($events_array)){
				switch($ecv['sort_by']){
					case has_action("eventon_event_sorting_{$ecv['sort_by']}"):
						do_action("eventon_event_sorting_{$ecv['sort_by']}", $events_array);
					break;
					case 'sort_date':
						usort($events_array, 'cmp_esort_enddate' );
						usort($events_array, 'cmp_esort_startdate' );

					break;case 'sort_title':
						usort($events_array, 'cmp_esort_title' );
					break; case 'sort_color':
						usort($events_array, 'cmp_esort_color' );
					break;
					case 'sort_rand':
						shuffle($events_array);
					break;
				}
			}


			// ALT: reverse events order within the events array list
			$events_array = ($ecv['event_order']=='DESC')?
				array_reverse($events_array) : $events_array;

			return $events_array;
		}

	/**
	 * reusable variables within the calendar
	 * @return
	 */
		public function reused(){
			$lang = (!empty($this->cal->shortcode_args['lang']))? $this->cal->shortcode_args['lang']: 'L1';


			// for each event type category
			$ett_i18n_names = evo_get_localized_ettNames( $lang, $this->cal->evopt1, $this->cal->evopt2);

			for($x = 1; $x< $this->cal->event_types ; $x++){
				$ab = ($x==1)? '':$x;

				$this->cal->lang_array['et'.$ab] = $ett_i18n_names[$x];
			}

			$this->cal->lang_array['no_event'] = $this->cal->lang('evcal_lang_noeve','No Events',$lang);
			$this->cal->lang_array['evcal_lang_yrrnd'] = $this->cal->lang('evcal_lang_yrrnd','Year Around Event',$lang);
			$this->cal->lang_array['evcal_lang_mntlng'] = $this->cal->lang('evcal_lang_mntlng','Month Long Event',$lang);
			$this->cal->lang_array['evloc'] = $this->cal->lang('evcal_lang_evloc','Event Location', $lang);
			$this->cal->lang_array['evorg'] = $this->cal->lang('evcal_lang_evorg','Event Organizer', $lang);
			$this->cal->lang_array['evsme'] = $this->cal->lang('evcal_lang_sme','Show More Events', $lang);


			//print_r($this->cal->lang_array);
		}

	/**
	 * update or change shortcode argument values after its processed on globally
	 * @param  string $field   shortcode field
	 * @param  string $new_val value of the field
	 * @return
	 */
		public function update_shortcode_args($field, $new_val){
			$sca = $this->cal->shortcode_args;
			if(!empty($sca) && !empty($sca[$field])){
				$new_sca = $sca;
				$new_sca[$field]= $new_val;

				$this->cal->shortcode_args = $new_sca;
			}

			if($field=='lang' && empty($sca)){
				$this->cal->shortcode_args = array('lang'=>$new_val);
			}
		}
}