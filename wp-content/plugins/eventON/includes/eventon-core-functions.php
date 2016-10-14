<?php
/**
 * EventON Core Functions
 *
 * Functions available on both the front-end and admin.
 *
 * @author 		AJDE
 * @category 	Core
 * @package 	EventON/Functions
 * @version     2.4.3
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// check whether custom fields are activated and have values set ready
	function eventon_is_custom_meta_field_good($number, $opt=''){
		$opt = (!empty($opt))? $opt: get_option('evcal_options_evcal_1');
		return ( !empty($opt['evcal_af_'.$number]) 
			&& $opt['evcal_af_'.$number]=='yes'
			&& !empty($opt['evcal_ec_f'.$number.'a1']) 
			&& !empty($opt['evcal__fai_00c'.$number])  )? true: false;
	}


/*	Dynamic styles generation */
	function eventon_generate_options_css($newdata='') {
	 
		/** Define some vars **/
		$data = $newdata; 
		$uploads = wp_upload_dir();
		
		//$css_dir = get_template_directory() . '/css/'; // Shorten code, save 1 call
		$css_dir = AJDE_EVCAL_DIR . '/'. EVENTON_BASE.  '/assets/css/'; 
		//$css_dir = plugin_dir_path( __FILE__ ).  '/assets/css/'; 
		
		//echo $css_dir;

		/** Save on different directory if on multisite **/
		if(is_multisite()) {
			$aq_uploads_dir = trailingslashit($uploads['basedir']);
		} else {
			$aq_uploads_dir = $css_dir;
		}
		
		/** Capture CSS output **/
		ob_start();
		require($css_dir . 'dynamic_styles.php');
		$css = ob_get_clean();

		//print_r($css);
		
		/** Write to options.css file **/
		WP_Filesystem();
		global $wp_filesystem;
		if ( ! $wp_filesystem->put_contents( $aq_uploads_dir . 'eventon_dynamic_styles.css', $css, 0777) ) {
		    return true;
		}		
	}

// check for a shortcode in post content
	function has_eventon_shortcode( $shortcode='', $post_content=''){
		global $post;

		$shortcode = (!empty($shortcode))? $shortcode : 'add_eventon';
	 
		$post_content = (!empty($post_content))? $post_content: 
			( (!empty($post->post_content))? $post->post_content:'' );

		if(!empty($post_content)){
			if(has_shortcode($post_content, $shortcode) || 
				has_shortcode($post_content, $shortcode)){
		
				return true;
			}else{
				return false;
			}
		}else{	return false;	}
	}

// CHECEK if the date is future date	
	function eventon_is_future_event($current_time, $start_unix, $end_unix, $evcal_cal_hide_past, $hide_past_by=''){
		
		// hide past by
		$hide_past_by = (!empty($hide_past_by))? $hide_past_by: false;

		// classify past events by end date/time
		if(!$hide_past_by || $hide_past_by=='ee'){
			$future_event = ($end_unix >= $current_time )? true:false;		
		}else{
			// classify past events by start date/time
			$future_event = ($start_unix >= $current_time )? true:false;
		}
		
		
		if( 
			( ($evcal_cal_hide_past=='yes' ) && $future_event )
			|| ( ($evcal_cal_hide_past=='no' ) || ($evcal_cal_hide_past=='' ))
		){
			return true;
		}else{
			return false;
		}
	}
	// check if and event is past event
	// @version 2.3.11
	function eventon_is_event_past($current_time, $start_unix, $end_unix, $hide_past_by=''){
		$hide_past_by = (!empty($hide_past_by))? $hide_past_by: false;
		// classify past events by end date/time
		if(!$hide_past_by || $hide_past_by=='ee'){
			return ($end_unix < $current_time )? true:false;
		}else{
			// classify past events by start date/time
			return ($start_unix < $current_time )? true:false;
		}
	}

// if event is in date range
	function eventon_is_event_in_daterange($Estart_unix, $Eend_unix, $Mstart_unix, $Mend_unix, $shortcode=''){	

		// past event only cal
		if(!empty($shortcode['el_type']) && $shortcode['el_type']=='pe'){
			if(		
				( $Eend_unix <= $Mend_unix) &&
				( $Eend_unix >= $Mstart_unix)
			){
				return true;
			}else{
				return false;
			}
		}else{
			if(
				($Estart_unix<=$Mstart_unix && $Eend_unix>=$Mstart_unix) ||
				($Estart_unix<=$Mend_unix && $Eend_unix>=$Mend_unix) ||
				($Mstart_unix<=$Estart_unix && $Estart_unix<=$Mend_unix && $Eend_unix=='') ||		
				($Mstart_unix<=$Estart_unix && $Estart_unix<=$Mend_unix && $Eend_unix==$Estart_unix) 	||
				($Mstart_unix<=$Estart_unix && $Estart_unix<=$Mend_unix && $Eend_unix!=$Estart_unix)
			){
				return true;
			}else{
				return false;
			}
		}
	}

// TIME formatting
	// pretty time on event card
	function eventon_get_langed_pretty_time($unixtime, $dateformat){

		$datest = str_split($dateformat);
		$__output = '';
		$__new_dates = array();

		// full month name
		if(in_array('F', $datest)){
			$num = date('n', $unixtime);
			$_F = eventon_return_timely_names_('month_num_to_name',$num,'full');
			$__new_dates['F'] = $_F;
		}

		// 3 letter month name
		if(in_array('M', $datest)){
			$num = date('n', $unixtime);
			$_M = eventon_return_timely_names_('month_num_to_name',$num,'three');
			$__new_dates['M'] = $_M;
		}

		//full day name
		if(in_array('l', $datest)){
			$num = date('l', $unixtime);
			$_l = eventon_return_timely_names_('day',$num, 'full');
			$__new_dates['l'] = $_l;
		}

		//3 letter day name
		if(in_array('D', $datest)){
			$num = date('N', $unixtime);
			$_D = eventon_return_timely_names_('day_num_to_name',$num, 'three');
			$__new_dates['D'] = $_D;
		}


		// process values
		foreach($datest as $date_part){
			if(is_array($__new_dates) && array_key_exists($date_part, $__new_dates)){
				$__output .= $__new_dates[$date_part];
			}else{
				$__output .= date($date_part, $unixtime);
			}
		}
		return $__output;
	}

// RETURN: formatted event time in multiple formats
	function eventon_get_formatted_time($row_unix, $lang=''){
		/*
				D = Mon - Sun
			1	j = 1-31
				l = Sunday - Saturday
			3	N - day of week 1 (monday) -7(sunday)
				S - st, nd rd
			5	n - month 1-12
				F - January - Decemer
			7	t - number of days in month
				z - day of the year
			9	Y - 2000
				g = hours
			11	i = minute
				a = am/pm
			13	M = Jan - Dec
				m = 01-12
			15	d = 01-31
				H = hour 00 - 23
			17	A = AM/PM
				y = yea in 2 digits
				G = 24h format 0-23
		*/

		date_default_timezone_set('UTC');
				
		$key = array('D','j','l','N','S','n','F','t','z','Y','g','i','a','M','m','d','H', 'A', 'y','G');
		
		$date = date('D-j-l-N-S-n-F-t-z-Y-g-i-a-M-m-d-H-A-y-G',$row_unix);
		$date = explode('-',$date);
		
		foreach($date as $da=>$dv){
			// month name
			if($da==6){
				$output[$key[$da]]= eventon_return_timely_names_('month_num_to_name',$date[5]); 
			}else if($da==2){
				
				// day name - full day name
				$output[$key[$da]]= eventon_return_timely_names_('day',$date[2]); 
			
			// 3 letter month name
			}else if($da==13){
				$output[$key[$da]]= eventon_return_timely_names_('month_num_to_name',$date[5],'three'); 

			// 3 letter day name
			}else if($da==0){
				$output[$key[$da]]= eventon_return_timely_names_('day_num_to_name',$date[3],'three'); 
			}

			// am pm
			else if($da==12){				
				$output[$key[$da]]= eventon_return_timely_names_('ampm',$date[12]); 
			}else if( $da==17){				
				$output[$key[$da]]= eventon_return_timely_names_('ampm',$date[17]); 
			}else{
				$output[$key[$da]]= $dv;
			}
		}	
		//print_r($output);
		return $output;
	}

/*	return date value and time values from unix timestamp */
	function eventon_get_editevent_kaalaya($unix, $dateformat='', $timeformat24=''){
				
		// in case of empty date format provided
		// find it within system
		$DT_format = eventon_get_timeNdate_format();
		
		//$offset = (get_option('gmt_offset', 0) * 3600);

		date_default_timezone_set('UTC');
		$unix = $unix ;

		$dateformat = (!empty($dateformat))? $dateformat: $DT_format[1];
		$timeformat24 = (!empty($timeformat24))? $timeformat24: $DT_format[2];
		
		$date = date($dateformat, $unix);		
		
		$timestring = ($timeformat24)? 'H-i': 'g-i-A';
		$times_val = date($timestring, $unix);
		$time_data = explode('-',$times_val);		
		
		$output = array_merge( array($date), $time_data);
		
		return $output;
	}

/**
 * GET event UNIX time from date and time format $_POST values
 * @updated 2.2.25
 */
	function eventon_get_unix_time($data='', $date_format='', $time_format=''){
		
		$data = (!empty($data))? $data : $_POST;
		
		// check if start and end time are present
		if(!empty($data['evcal_end_date']) && !empty($data['evcal_start_date'])){
			// END DATE
			$__evo_end_date =(empty($data['evcal_end_date']))?
				$data['evcal_start_date']: $data['evcal_end_date'];
			
			// date format
			$_wp_date_format = (!empty($date_format))? $date_format: 
				( (isset($_POST['_evo_date_format']))? $_POST['_evo_date_format']
					: get_option('date_format')
				);
			
			$_is_24h = (!empty($time_format) && $time_format=='24h')? true:
				( (isset($_POST['_evo_time_format']) && $_POST['_evo_time_format']=='24h')? 
					true: false
				); // get default site-wide date format
				
			
			//$_wp_date_str = split("[\s|.|,|/|-]",$_wp_date_format);
			
			date_default_timezone_set('UTC');	

			// ---
			// START UNIX
			$unix_start =0;
			if( !empty($data['evcal_start_date']) ){

				// ALL day event
				if(!empty($data['evcal_allday']) && $data['evcal_allday']=='yes'){
					$Date = date_parse_from_format($_wp_date_format, $data['evcal_start_date']);
					$unix_start = mktime(00, 05,00, $Date['month'], $Date['day'], $Date['year'] );
				}else{
					if(!empty($data['evcal_start_time_hour'])){
						$__Sampm = (!empty($data['evcal_st_ampm']))? $data['evcal_st_ampm']:null;

						//get hours minutes am/pm 
						$time_string = $data['evcal_start_time_hour']
							.':'.$data['evcal_start_time_min'].$__Sampm;
						
						// event start time string
						$date = $data['evcal_start_date'].' '.$time_string;
						
						// parse string to array by time format
						$__ti = ($_is_24h)?
							date_parse_from_format($_wp_date_format.' H:i', $date):
							date_parse_from_format($_wp_date_format.' g:ia', $date);

						$unix_start = mktime($__ti['hour'], $__ti['minute'],0, $__ti['month'], $__ti['day'], $__ti['year'] );
					}
				}
			}
			
			// ---
			// END TIME UNIX
			$unix_end =0;
			if( !empty($data['evcal_end_date']) ){
				
				// ALL DAY
				if(!empty($data['evcal_allday']) && $data['evcal_allday']=='yes' ){
					$Date = date_parse_from_format($_wp_date_format, $data['evcal_end_date']);
					$unix_end = mktime(00, 05,00, $Date['month'], $Date['day'], $Date['year'] );
				}else{
					if( !empty($data['evcal_end_time_hour'])  ){
						$__Eampm = (!empty($data['evcal_et_ampm']))? $data['evcal_et_ampm']:null;

						//get hours minutes am/pm 
						$time_string = $data['evcal_end_time_hour']
							.':'.$data['evcal_end_time_min'].$__Eampm;
						
						// event start time string
						$date = $__evo_end_date.' '.$time_string;
														
						// parse string to array by time format
						$__ti = ($_is_24h)?
							date_parse_from_format($_wp_date_format.' H:i', $date):
							date_parse_from_format($_wp_date_format.' g:ia', $date);
						
						$unix_end = mktime($__ti['hour'], $__ti['minute'],0, $__ti['month'], $__ti['day'], $__ti['year'] );	
					}
				}	
			}
			$unix_end =(!empty($unix_end) )?$unix_end:$unix_start;
			
		}else{
			// if no start or end present
			$unix_start = $unix_end = 0;
		}
		// output the unix timestamp
		$output = array(
			'unix_start'=>$unix_start,
			'unix_end'=>$unix_end
		);		
		return $output;
	}

/*
	return jquery and HTML UNIVERSAL date format for the site
	added: version 2.1.19
	updated: 
*/
	function eventon_get_timeNdate_format($evcal_opt=''){
		
		if(empty($evcal_opt))
			$evcal_opt = get_option('evcal_options_evcal_1');
		
		if(!empty($evcal_opt) && $evcal_opt['evo_usewpdateformat']=='yes'){
					
			/** get date formate and convert to JQ datepicker format**/				
			$wp_date_format = get_option('date_format');
			$format_str = str_split($wp_date_format);
			
			foreach($format_str as $str){
				switch($str){							
					case 'j': $nstr = 'd'; break;
					case 'd': $nstr = 'dd'; break;	
					case 'D': $nstr = 'D'; break;	
					case 'l': $nstr = 'DD'; break;	
					case 'm': $nstr = 'mm'; break;
					case 'M': $nstr = 'M'; break;
					case 'n': $nstr = 'm'; break;
					case 'F': $nstr = 'MM'; break;							
					case 'Y': $nstr = 'yy'; break;
					case 'y': $nstr = 'y'; break;
					case 'S': $nstr = '-'; break;
											
					default :  $nstr = ''; break;							
				}
				$jq_date_format[] = (!empty($nstr))? ($nstr=='-'?'':$nstr) :$str;
				
			}
			$jq_date_format = implode('',$jq_date_format);
			$evo_date_format = $wp_date_format;
		}else{
			$jq_date_format ='yy/mm/dd';
			$evo_date_format = 'Y/m/d';
		}		
		
		// time format
		$wp_time_format = get_option('time_format');
		
		$hr24 = (strpos($wp_time_format, 'H')!==false || strpos($wp_time_format, 'G')!==false)?true:false;
		
		return array(
			$jq_date_format, 
			$evo_date_format,
			$hr24
		);
	}

// get single letter month names
	function eventon_get_oneL_months($lang_options){
		if(!empty($lang_options)) {$lang_options = $lang_options;}
		else{
			$opt = get_option('evcal_options_evcal_2');
			$lang_options = $opt['L1'];
		}

		$__months = array('J','F','M','A','M','J','J','A','S','O','N','D');
		$count = 1;
		$output = array();

		foreach($__months as $month){
			$output[] = (!empty($lang_options['evo_lang_1Lm_'.$count]))? $lang_options['evo_lang_1Lm_'.$count]: $month;
			$count++;
		}
		return $output;
	}

// ---
// SUPPORTIVE time and date functions
	// GET time for ICS adjusted for unix
		function evo_get_adjusted_utc($unix){
			$offset = (get_option('gmt_offset', 0) * 3600);
			/*
				We are making time (mktime) and getting time (date) using php server timezone
				So we first adjust save UNIX to get unix at UTC/GMT 0 and then we adjust that time to offset for timezone saved on wordpress settings.
			*/
			//$__unix = $unix - (date('Z')) - $offset;
			$__unix = $unix - $offset;

			//$the_date = $unix;
			//date_default_timezone_get();
			//date_default_timezone_set("UTC");
			//$new_timeT = gmdate("Ymd", $unix);
			//$new_timeT = date_i18n("Ymd", $__unix);
			$new_timeT = date("Ymd", $__unix);
			$new_timeZ = date("Hi", $__unix);

			return $new_timeT.'T'.$new_timeZ.'00Z';
		}

	function evo_unix_offset($unix){
		$offset = (get_option('gmt_offset', 0) * 3600);
	}

// return 24h or 12h or true false
	function eventon_get_time_format($return='tf'){
		// time format
		$wp_time_format = get_option('time_format');

		if($return=='tf'){
			return  (strpos($wp_time_format, 'H')!==false)?true:false;
		}else{
			return  (strpos($wp_time_format, 'H')!==false)?'24h':'12h';
		}
	}	



/*
	function to return day names and month names in correct language
	type: day, month, month_num_to_name, day_num_to_name
*/
	function eventon_return_timely_names_($type, $data, $len='full', $lang=''){
		global $eventon;

		$eventon_day_names = array(
		1=>'monday','tuesday','wednesday','thursday','friday','saturday','sunday');
		$eventon_month_names = array(1=>'january','february','march','april','may','june','july','august','september','october','november','december');
		$eventon_ampm = array(1=>'am', 'pm');
				
		$output ='';
		
		// lower case the data values
		$original = $data;
		$data = strtolower($data);
		
		$evo_options = !empty($eventon->evo_generator->evopt2)?
			$eventon->evo_generator->evopt2: get_option('evcal_options_evcal_2');
		$shortcode_arg = $eventon->evo_generator->shortcode_args;
		
		// check which language is called for
		$evo_options = (!empty($evo_options))? $evo_options: get_option('evcal_options_evcal_2');
		
		// check for language preference
		$_lang_variation = ( (!empty($lang))? $lang: 
			( (!empty($shortcode_arg['lang']))? $shortcode_arg['lang']:'L1' ) );
		//$_lang_variation = strtoupper($_lang_variation);
		
		// day name
		if($type=='day'){
			
			//global $eventon_day_names;
			$text_num = array_search($data, $eventon_day_names); // 1-7
					
			if($len=='full'){			
				
				$option_name_prefix = 'evcal_lang_day';
				$_not_value = $eventon_day_names[ $text_num];				
			
			// 3 letter day names
			}else if($len=='three'){
				
				$option_name_prefix = 'evo_lang_3Ld_';
				$_not_value = substr($eventon_day_names[ $text_num], 0 , 3);
			}
		
		// day number to name
		}else if($type=='day_num_to_name'){
		
			$text_num = $data; // 1-7
			
			if($len=='full'){	
				$option_name_prefix = 'evcal_lang_day';
				$_not_value = $eventon_day_names[ $text_num];
			
			// 3 letter day names
			}else if($len=='three'){				
				$option_name_prefix = 'evo_lang_3Ld_';
				$_not_value = substr($eventon_day_names[ $text_num], 0 , 3);	
			}
					
		// month names
		}else if($type=='month'){
			//global $eventon_month_names;
			$text_num = array_search($data, $eventon_month_names); // 1-12
			
			if($len == 'full'){
			
				$option_name_prefix = 'evcal_lang_';
				$_not_value = $eventon_month_names[ $text_num];
				
			}else if($len=='three'){
			
				$option_name_prefix = 'evo_lang_3Lm_';
				$_not_value = substr($eventon_month_names[ $text_num], 0 , 3);
				
			}
		
		// month number to name
		}else if($type=='month_num_to_name'){
			
			//global $eventon_month_names;
			$text_num = $data; // 1-12
			
			if($len == 'full'){
				$option_name_prefix = 'evcal_lang_';
				$_not_value = $eventon_month_names[ $text_num];

			}else if($len=='three'){
				$option_name_prefix = 'evo_lang_3Lm_';
				$_not_value = substr($eventon_month_names[ $text_num], 0 , 3);
			}
		// am pm
		}else if($type=='ampm'){
			$text_num = $data; 
			
			$option_name_prefix = 'evo_lang_';
			$_not_value = $original;
		}
		
		$output = (!empty($evo_options[$_lang_variation][$option_name_prefix.$text_num]))? 
					$evo_options[$_lang_variation][$option_name_prefix.$text_num]
					: $_not_value;

		return $output;
	}

// return event date-time in given date format using date item array
// deprecating -> evo_datetime
// version 2.3.21 used in evo datetime function
	function eventon_get_lang_formatted_timestr($dateform, $datearray){
		$time = str_split($dateform);
		$newtime = '';
		$count = 0;
		foreach($time as $timestr){
			// check previous chractor
				if( strpos($time[ $count], '\\') !== false ){ 
					//echo $timestr;
					$newtime .='';
				}elseif($count!= 0 &&  strpos($time[ $count-1 ], '\\') !== false ){
					$newtime .= $timestr;
				}else{
					$newtime .= (array_key_exists($timestr, $datearray))? $datearray[$timestr]: $timestr;
				}
			
			$count ++;
		}
		return $newtime;
	}

	function eventon_get_event_day_name($day_number){
		return eventon_return_timely_names_('day_num_to_name',$day_number);
	}

	// return month and year numbers from current month and difference
		function eventon_get_new_monthyear($current_month_number, $current_year, $difference){
			
			$month_num = $current_month_number + $difference;

			// /echo $current_month_number.' '.$month_num.' --';
			
			$count = ($difference>=0)? '+'.$difference: '-'.$difference;


			$time = mktime(0,0,0,$current_month_number,1,$current_year);
			$new_time = strtotime($count.'month ', $time);
			
			$new_time= explode('-',date('Y-n', $new_time));
			
			
			$ra = array(
				'month'=>$new_time[1], 'year'=>$new_time[0]
			);
			return $ra;
		}


// Calendar Parts
	/*
		RETURN calendar header with month and year data
		string - should be m, Y if empty
	*/
		function get_eventon_cal_title_month($month_number, $year_number, $lang=''){
			
			$evopt = get_option('evcal_options_evcal_1');
			
			$string = !empty($evopt['evcal_header_format'])? 
				$evopt['evcal_header_format']:'m, Y';

			$str = str_split($string, 1);
			$new_str = '';
			
			foreach($str as $st){
				switch($st){
					case 'm':
						$new_str.= eventon_return_timely_names_('month_num_to_name',$month_number, 'full', $lang);
						
					break;
					case 'Y':
						$new_str.= $year_number;
					break;
					case 'y':
						$new_str.= substr($year_number, -2);
					break;
					default:
						$new_str.= $st;
					break;
				}
			}		
			return $new_str;
		}

// =========
// LANGUAGE 
	/** return custom language text saved in settings **/
		function eventon_get_custom_language($evo_options='', $field, $default_val, $lang=''){
			global $eventon;
				
			// check which language is called for
			$evo_options = (!empty($evo_options))? $evo_options: get_option('evcal_options_evcal_2');
			
			// check for language preference
			if(!empty($lang)){
				$_lang_variation = $lang;
			}else{
				$shortcode_arg = $eventon->evo_generator->shortcode_args;
				$_lang_variation = (!empty($shortcode_arg['lang']))? $shortcode_arg['lang']:'L1';
			}
			
			$new_lang_val = (!empty($evo_options[$_lang_variation][$field]) )?
				stripslashes($evo_options[$_lang_variation][$field]): $default_val;
				
			return $new_lang_val;
		}

		function eventon_process_lang_options($options){
			$new_options = array();
			
			foreach($options as $f=>$v){
				$new_options[$f]= stripslashes($v);
			}
			return $new_options;
		}

	// @version 2.2.28
	// self sufficient language translattion
	// faster translation
		function evo_lang($text, $lang='', $language_options=''){
			global $eventon;
			$language_options = (!empty($language_options))? $language_options: get_option('evcal_options_evcal_2');
			$shortcode_arg = $eventon->evo_generator->shortcode_args;

			// conditional correct language 
			$lang = (!empty($lang))? $lang:
				(!empty($eventon->lang) ? $eventon->lang:
					( !empty($shortcode_arg['lang'])? $shortcode_arg['lang']: 'L1')
				);

			$field_name = str_replace(' ', '-',  strtolower($text));

			return !empty($language_options[$lang][$field_name])? $language_options[$lang][$field_name]:$text;
		}
		// this function with directly echo the values
			function evo_lang_e($text, $lang='', $language_options=''){
				echo evo_lang($text, $lang='', $language_options='');
			}
	// get eventon language using variable
	// 2.3.16
		function evo_lang_get($var, $default, $lang='', $language_options=''){
			global $eventon;
			$language_options = (!empty($language_options))? $language_options: get_option('evcal_options_evcal_2');
			$shortcode_arg = $eventon->evo_generator->shortcode_args;
			// conditional correct language 
			$lang = (!empty($lang))? $lang:
				(!empty($eventon->lang) ? $eventon->lang:
					( !empty($shortcode_arg['lang'])? $shortcode_arg['lang']: 'L1')
				);
			$new_lang_val = (!empty($language_options[$lang][$var]) )?
				stripslashes($language_options[$lang][$var]): $default;				
			return $new_lang_val;

		}

/* ADD TO CALENDAR */
	function eventon_get_addgoogle_cal($object){
		$location = (!empty($object->evals['evcal_location']))? urlencode($object->evals['evcal_location'][0]) : ''; 
		$start = evo_get_adjusted_utc($object->estart);
		$end = evo_get_adjusted_utc($object->eend);
		$title = urlencode($object->etitle);
		$excerpt = !empty($object->excerpt)? $object->excerpt: $object->etitle;

		return 'http://www.google.com/calendar/event?action=TEMPLATE&amp;text='.$title.'&amp;dates='.$start.'/'.$end.'&amp;details='.( urlencode($excerpt) ).'&amp;location='.$location;
	}

/** SORTING arrangement functions **/
	function cmp_esort_startdate($a, $b){
		return $a["event_start_unix"] - $b["event_start_unix"];
	}
	function cmp_esort_enddate($a, $b){
		return $a["event_end_unix"] - $b["event_end_unix"];
	}
	function cmp_esort_title($a, $b){
		return strcmp($a["event_title"], $b["event_title"]);
	}
	function cmp_esort_color($a, $b){
		return strcmp($a["event_color"], $b["event_color"]);
	}

// GET EVENT
	function get_event($the_event){	global $eventon;}

// Return formatted time 
	if( !function_exists ('ajde_evcal_formate_date')){
		function ajde_evcal_formate_date($date,$return_var){	
			$srt = strtotime($date);
			$f_date = date($return_var,$srt);
			return $f_date;
		}
	}

	if( !function_exists ('returnmonth')){
		function returnmonth($n){
			$timestamp = mktime(0,0,0,$n,1,2013);
			return date('F',$timestamp);
		}
	}
	if( !function_exists ('eventon_returnmonth_name_by_num')){
		function eventon_returnmonth_name_by_num($n){
			return eventon_return_timely_names_('month_num_to_name', $n);
		}
	}

/*	eventON return font awesome icons names*/
	function get_eventON_icon($var, $default, $options_value){

		$options_value = (!empty($options_value))? $options_value: get_option('evcal_options_evcal_1');

		return (!empty( $options_value[$var]))? $options_value[$var] : $default;
	}

// Return a excerpt of the event details
	function eventon_get_event_excerpt($text, $excerpt_length, $default_excerpt='', $title=true){
		global $eventon;
		
		$content='';
		
		if(empty($default_excerpt) ){
		
			$words = explode(' ', $text);

			if(count($words)> $excerpt_length)
				$words = array_slice($words, 0, 5, true);

			$content = implode(' ', $words);
			$content = strip_shortcodes($content);
			$content = str_replace(']]>', ']]&gt;', $content);
			$content = strip_tags($content);
		}else{
			$content = $default_excerpt;
		}		
		
		$titletx = ($title)? '<h3 class="padb5 evo_h3">' . eventon_get_custom_language($eventon->evo_generator->evopt2, 'evcal_evcard_details','Event Details').'</h3>':null;
		
		$content = '<div class="event_excerpt" style="display:none">'.$titletx.'<p>'. $content . '</p></div>';
		
		return $content;
	}
	function eventon_get_normal_excerpt($string, $excerpt_length){
		$content='';

		$words = explode(' ', $string);

		if(count($words)> $excerpt_length)
			$words = array_slice($words, 0, 5, true);

		$content = implode(' ', $words);

		$content = strip_shortcodes($content);
		$content = str_replace(']]>', ']]&gt;', $content);
		$content = strip_tags($content);

		return $content;
	}

/** eventon Term Meta API - Get term meta */
	function get_eventon_term_meta( $term_id, $key, $single = true ) {
		return get_metadata( 'eventon_term', $term_id, $key, $single );
	}

/** Get template part (for templates like the event-loop). */
	function eventon_get_template_part( $slug, $name = '' , $preurl='') {
		global $eventon;
		$template = '';
				
		if($preurl){
			$template =$preurl."/{$slug}-{$name}.php";
		}else{
			// Look in yourtheme/slug-name.php and yourtheme/eventon/slug-name.php
			if ( $name )
				$template = locate_template( array ( "{$slug}-{$name}.php", "{$eventon->template_url}{$slug}-{$name}.php" ) );

			// Get default slug-name.php
			if ( !$template && $name && file_exists( AJDE_EVCAL_PATH . "/templates/{$slug}-{$name}.php" ) )
				$template = AJDE_EVCAL_PATH . "/templates/{$slug}-{$name}.php";

			// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/eventon/slug.php
			if ( !$template )
				$template = locate_template( array ( "{$slug}.php", "{$eventon->template_url}{$slug}.php" ) );			
		}
		
		if ( $template )	load_template( $template, false );
	}

/** 
 * Get other templates passing attributes and including the file
 * @access public
 * @version 0.1
 * @since  2.3.6
 */
	function evo_get_template($template_name, $args=array(), $template_path='', $default_path=''){

		if($args && is_array($args))
			extract($args);

		$located = evo_locate_template( $template_name, $template_path, $default_path );

		if ( ! file_exists( $located ) ) {
	         _doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $located ), '2.1' );
	         return;
	     }
	 
	    // Allow 3rd party plugin filter template file from their plugin
	    $located = apply_filters( 'evo_get_template', $located, $template_name, $args, $template_path, $default_path );
	 	     
	    include( $located );
	}

	function evo_locate_template($template_name, $template_path = '', $default_path = ''){

		if(!$template_path)
			$template_path = AJDE_EVCAL_PATH;

		if(!$default_path)
			$default_path = AJDE_EVCAL_PATH.'/templates/';

		$template = locate_template(
			array(
				trailingslashit( $template_path ) . $template_name,
				$template_name
			)
		);

		// get default template
		if(!$template ){
			$template = $default_path.$template_name;
		}

		return $template;

	}

/* Initiate capabilities for eventON */
	function eventon_init_caps(){
		global $wp_roles;

		//print_r($wp_roles);
		
		if ( class_exists('WP_Roles') )
			if ( ! isset( $wp_roles ) )
				$wp_roles = new WP_Roles();
		
		$capabilities = eventon_get_core_capabilities();
		
		foreach( $capabilities as $cap_group ) {
			foreach( $cap_group as $cap ) {
				$wp_roles->add_cap( 'administrator', $cap );
			}
		}
	}

// for style values
	function eventon_styles($default, $field, $options){	
		return (!empty($options[$field]))? $options[$field]:$default;
	}

// GET activated event type count
	function evo_verify_extra_ett($evopt=''){

		$evopt = (!empty($evopt))? $evopt: get_option('evcal_options_evcal_1');

		$count=array();
		for($x=3; $x<6; $x++ ){
			if(!empty($evopt['evcal_ett_'.$x]) && $evopt['evcal_ett_'.$x]=='yes'){
				$count[] = $x;
			}else{	break;	}
		}
		return $count;
	}
	// this return the count for each event type that are activated in accordance
	function evo_get_ett_count($evopt=''){
		$evopt = (!empty($evopt))? $evopt: get_option('evcal_options_evcal_1');

		$count=2;
		for($x=3; $x<= apply_filters('evo_event_type_count',5); $x++ ){
			if(!empty($evopt['evcal_ett_'.$x]) && $evopt['evcal_ett_'.$x]=='yes'){
				$count = $x;
			}else{
				break;
			}
		}
		return $count;
	}

	// this will return the count for custom meta data fields that are active
	function evo_calculate_cmd_count($evopt=''){
		$evopt = (!empty($evopt))? $evopt: get_option('evcal_options_evcal_1');

		$count=0;
		for($x=1; $x<evo_max_cmd_count(); $x++ ){
			if(!empty($evopt['evcal_af_'.$x]) && $evopt['evcal_af_'.$x]=='yes' && !empty($evopt['evcal_ec_f'.$x.'a1'])){
				$count = $x;
			}else{
				break;
			}
		}

		return $count;
	}
	function evo_retrieve_cmd_count($evopt=''){
		global $eventon;
		$opt = $eventon->frontend->evo_options;
		$evopt = (!empty($evopt))? $evopt: $opt;
		
		if(!empty($evopt['cmd_count']) && $evopt['cmd_count']==0){
			return $evopt['cmd_count'];
		}else{
			$new_c = evo_calculate_cmd_count($evopt);

			$evopt['cmd_count']=$new_c;
			//update_option('evcal_options_evcal_1', $evopt);

			return $new_c;
		}
	}
	// return maximum custom meta data field count for event
	// @version 2.3.11
	function evo_max_cmd_count(){
		return apply_filters('evo_max_cmd_count', 11);
	}

// GET event type names
	function evo_get_ettNames($options=''){
		$output = array();

		$options = (!empty($options))? $options: get_option('evcal_options_evcal_1');
		for( $x=1; $x< (evo_get_ett_count($options)+1); $x++){
			$ab = ($x==1)? '':$x;
			$output[$x] = (!empty($options['evcal_eventt'.$ab]))? $options['evcal_eventt'.$ab]:'Event Type '.$ab;
		}
		return $output;
	}
	function evo_get_localized_ettNames($lang='', $options='', $options2=''){
		$output ='';
		global $eventon;

		$options = (!empty($options))? $options: get_option('evcal_options_evcal_1');
		$options2 = (!empty($options2))? $options2: get_option('evcal_options_evcal_2');
		
		if(!empty($lang)){
			$_lang_variation = $lang;
		}else{
			$shortcode_arg = $eventon->evo_generator->shortcode_args;
			$_lang_variation = (!empty($shortcode_arg['lang']))? $shortcode_arg['lang']:'L1';
		}

		
		// foreach event type upto activated event type categories
		for( $x=1; $x< (evo_get_ett_count($options)+1); $x++){
			$ab = ($x==1)? '':$x;

			$_tax_lang_field = 'evcal_lang_et'.$x;

			// check on eventon language values for saved name
			$lang_name = (!empty($options2[$_lang_variation][$_tax_lang_field]))? 
				stripslashes($options2[$_lang_variation][$_tax_lang_field]): null;

			// conditions
			if(!empty($lang_name)){
				$output[$x] = $lang_name;
			}else{
				$output[$x] = (!empty($options['evcal_eventt'.$ab]))? $options['evcal_eventt'.$ab]:'Event Type '.$ab;
			}			
		}
		return $output;
	}

// GET  event custom taxonomy field names
	function eventon_get_event_tax_name($tax, $options=''){
		$output ='';

		$options = (!empty($options))? $options: get_option('evcal_options_evcal_1');
		if($tax =='et'){
			$output = (!empty($options['evcal_eventt']))? $options['evcal_eventt']:'Event Type';
		}elseif($tax=='et2'){
			$output = (!empty($options['evcal_eventt2']))? $options['evcal_eventt2']:'Event Type 2';
		}
		return $output;
	}

// GET  event custom taxonomy field names -- FOR FRONT END w/ Lang
	function eventon_get_event_tax_name_($tax, $lang='', $options='', $options2=''){
		$output ='';

		$options = (!empty($options))? $options: get_option('evcal_options_evcal_1');
		$options2 = (!empty($options2))? $options2: get_option('evcal_options_evcal_2');
		$_lang_variation = (!empty($lang))? $lang:'L1';

		$_tax = ($tax =='et')? 'evcal_eventt': 'evcal_eventt2';
		$_tax_lang_field = ($tax =='et')? 'evcal_lang_et1': 'evcal_lang_et2';


		// check for language first
		if(!empty($options2[$_lang_variation][$_tax_lang_field]) ){
			$output = stripslashes($options2[$_lang_variation][$_tax_lang_field]);
		
		// no lang value -> check set custom names
		}elseif(!empty($options[$_tax])) {		
			$output = $options[$_tax];
		}else{
			$output = ($tax =='et')? 'Event Type': 'Event Type 2';
		}

		return $output;
	}

// GET SAVED VALUES
	// meta value check and return
	function check_evo_meta($meta_array, $fieldname){
		return (!empty($meta_array[$fieldname]))? true:false;
	}
	function evo_meta($meta_array, $fieldname, $slashes=false){
		return (!empty($meta_array[$fieldname]))? 
			($slashes? stripcslashes($meta_array[$fieldname][0]): $meta_array[$fieldname][0])
			:null;
	}
	function evo_meta_yesno($meta_array, $fieldname, $check_value, $yes_value, $no_value){	
		return (!empty($meta_array[$fieldname]) && $meta_array[$fieldname][0] == $check_value)? $yes_value:$no_value;
	}
	
	/**
	 * check wether meta field value is not empty and equal to yes
	 * @param  $meta_array array of post meta fields
	 * @param  $fieldname  field name as a string
	 * @return boolean   
	 * @since 2.2.20          
	 */
	function evo_check_yn($meta_array, $fieldname){
		return (!empty($meta_array[$fieldname]) && $meta_array[$fieldname][0]=='yes')? true:false;
	}
	// this will return true or false after checking if eventon settings value = yes
	function evo_settings_val($fieldname, $options, $not=''){
		if($not){
			return ( empty($options[$fieldname]) || (!empty($options[$fieldname]) && $options[$fieldname]=='no') )? true:false;
		}else{
			return ( !empty($options[$fieldname]) && $options[$fieldname]=='yes' )? true:false;
		}
	}

/* 2.2.17 */
	// process taxnomy filter values and return terms and operator
		function eventon_tax_filter_pro($value){
			// value have NOT in it
			if(strpos($value, 'NOT-')!== false){
				$op = explode('-', $value);
				$filter_op='NOT';
				$vals = str_replace('NOT-', '', $value);
				//$vals = $op[1];
			}else{
				$vals= $value;
				$filter_op = 'IN';
			}
			return array($vals, $filter_op);
		}

	// get options for eventon settings
		//	tab ending = 1,2, etc. rs for rsvp
		function evo_get_options($tab_ending){
			return get_option('evcal_options_evcal_'.$tab_ending);
		}

	// PAGING functions
		// return archive eevnt page id set in previous version or in settigns
		function evo_get_event_page_id($opt=''){
			$opt == (!empty($opt))? $opt: evo_get_options('1');
			if(!empty($opt['evo_event_archive_page_id'])){
			 	$id = $opt['evo_event_archive_page_id'];
			}else{
				$id = get_option('eventon_events_page_id');
				$id = !empty($id)? $id: false;
			}

			// check if this post exist
			if($id){
				$id = (get_post_status( $id ))? $id: false;
			}

			return $id;
		}
		// get event archive page template name
		function evo_get_event_template($opt){
			$opt == (!empty($opt))? $opt: evo_get_options('1');
			$ptemp = $opt['evo_event_archive_page_template'];

			if(empty($ptemp) || $ptemp=='archive-ajde_events.php' ){
			 	$template = 'archive-ajde_events.php';
			}else{
				$template =$ptemp;
			}
			return $template;
		}
		function evo_archive_page_content(){}

// eventon and wc check function
// added 2.2.17 - updated: 2.2.19
	function evo_initial_check($slug='eventon'){
		
		if($slug=='eventon'){
			$evoURL = get_option('eventon_addon_urls');

			// if url saved in options
			if(!empty($evoURL) ){
				//echo 1;
				if(file_exists($evoURL['addons'])){
					return $evoURL['addons'];
				}else{
					$path = AJDE_EVCAL_PATH;
					$url = $path.'/classes/class-evo-addons.php';
					return file_exists($url)? $url: false;
				}				
				
			}else{
				//echo 2;
				// for multi site
				if(is_multisite()){

					$evoURL = false;

					if ( ! function_exists( 'is_plugin_active_for_network' ) )
   						require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
	   				
					if(is_plugin_active_for_network(EVENTON_BASE.'/eventon.php')){
						$path = AJDE_EVCAL_PATH;
						$url = $path.'/classes/class-evo-addons.php';
						return $url;
					}else{
						$blogs = wp_get_sites();
					
						foreach($blogs as $blog){
							//echo $blog['blog_id'];
							$_active_plugins = get_blog_option($blog['blog_id'], 'active_plugins');

							if(!empty($_active_plugins)){
								//echo 3;
								$_evoInstalled = false;
								foreach($_active_plugins as $plugin){
									// check if eventon is in activated plugins list
									if(strpos( $plugin, 'eventon.php') !== false){
										$_evoInstalled= true;
										$evoSlug = explode('/', $plugin);
									}
								}

								if(!empty($evoSlug) && $_evoInstalled){
									$path = AJDE_EVCAL_PATH;
									$url = $path.'/classes/class-evo-addons.php';

									$evoURL= (file_exists($url))? $url: false;
									break;
								}else{ $evoURL= false;	}
							}else{  
								//echo 4;
								$evoURL= false;	
							}					
						}
						return $evoURL;
					}
				}else{
					$_active_plugins = get_option( 'active_plugins' );
					if(!empty($_active_plugins)){
						$_evoInstalled = false;
						foreach($_active_plugins as $plugin){
							// check if eventon is in activated plugins list
							if(strpos( $plugin, 'eventon.php') !== false){
								$_evoInstalled= true;
								$evoSlug = explode('/', $plugin);
							}
						}

						if(!empty($evoSlug) && $_evoInstalled){
							$path = AJDE_EVCAL_PATH;
							$url = $path. '/classes/class-evo-addons.php';

							return (file_exists($url))? $url: false;
						}else{ 	return false;	}
					}else{  return false;	}
				}
			}// enfif

		}elseif($slug=='woo'){
			$_wcInstalled = false;

			if(is_multisite()){

				if ( ! function_exists( 'is_plugin_active_for_network' ) )
   					require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

				if(is_plugin_active_for_network('woocommerce/woocommerce.php')){
					return true;
				}else{
					$blogs = wp_get_sites();
					foreach($blogs as $blog){
						echo $blog['blog_id'];
						$_active_plugins = get_blog_option($blog['blog_id'], 'active_plugins');
						if(!empty($_active_plugins)){	
							print_r($_active_plugins);			
							foreach($_active_plugins as $plugin){
								// check if eventon is in activated plugins list
								if(strpos( $plugin, 'woocommerce.php') !== false){
									return true;
									break;
								}
							}						
						}
					}
				}
				
			}else{
				$_active_plugins = get_option( 'active_plugins' );				
				if(!empty($_active_plugins)){				
					foreach($_active_plugins as $plugin){
						// check if eventon is in activated plugins list
						if(strpos( $plugin, 'woocommerce.php') !== false){
							return true;
							break;
						}
					}
				}				
			}
			return $_wcInstalled;
		}
	}

// added 2.2.18 - updated 2.2.19
	function evo_get_addon_class_file(){
		$path = AJDE_EVCAL_PATH;
		return $path.'/classes/class-evo-addons.php';
	}

// added 2.2.21
	// get eventon settings option values
		function get_evoOPT($num, $field){
			$opt = get_option('evcal_options_evcal_'.$num);
			return (!empty($opt[$field]))? $opt[$field]: false;
		}
		function save_evoOPT($num, $field, $value){
			$opt = get_option('evcal_options_evcal_'.$num);
			$opt_ar = (!empty($opt))? $opt: array();

			$opt_ar[$field]= $value;
			update_option('evcal_options_evcal_'.$num, $opt_ar);
		}
		// get the entire options array
		// @since 2.2.24
		function get_evoOPT_array($num=''){
			$num = !empty($num)? $num: 1;
			return get_option('evcal_options_evcal_'.$num);
		}

/* version 2.2.25 */	
	/* when events are moved to trash record time */
		function eventon_record_trashedtime($opt){
			$opt['event_trashed'] = current_time('timestamp');
			update_option('evcal_options_evcal_1', $opt);
		}

	/* check if its time to trash old events again */
	// deprecated since 2.3.16
		function is_eventon_events_ready_to_trash($opt){

			// check if set to trash old events
			if(!empty($opt['evcal_move_trash']) && $opt['evcal_move_trash']=='yes'){
				$rightnow =current_time('timestamp');

				$last_trashed = (!empty($opt['event_trashed']))? $opt['event_trashed']:false;

				$trash_gap = 3600*24;

				// check if the time is correct for trashing
				if(!$last_trashed || $last_trashed+$trash_gap < $rightnow){
					return true;
				}else{ return false;}
			}else{ return false;}
		}

	// go through all events and trash past events
	// @version 2.3.16
		function eventon_trash_past_events(){
			global $eventon;

			if($eventon->frontend->evopt1['evcal_move_trash']!='yes' || empty($eventon->frontend->evopt1['evcal_move_trash'])) return false;

			$events = new WP_Query(array(
				'post_type'=>'ajde_events',
				'posts_per_page'=>-1
			));

			if(!$events->have_posts()) return false;

			date_default_timezone_set('UTC');
			$rightnow = current_time('timestamp');

			while($events->have_posts()): $events->the_post();
				$event_id = $events->ID;
				$eventPMV = get_post_custom($event_id);

				$repeating = evo_check_yn($eventPMV, 'evcal_repeat');
				$yearLong = evo_check_yn($eventPMV, 'evo_year_long');
				$monthLong = evo_check_yn($eventPMV, '_evo_month_long');
				
				$row_end = ( !empty($eventPMV['evcal_erow']) )? 
					$eventPMV['evcal_erow'][0]:
					((!empty($eventPMV['evcal_srow']))? 
					$eventPMV['evcal_srow'][0] :false);

				if(!$repeating && !$yearLong && !$monthLong && $row_end){

					if($row_end< $rightnow){
						$event = get_post($event_id, 'ARRAY_A');

						// only do this for event post types 5/19/15
						if($event['post_type']!='ajde_events') continue;
						
						$event['post_status']='trash';
						wp_update_post($event);
					}
				}
					

			endwhile;
			wp_reset_postdata();
		}

	/* check event post values for exclude on trashing the event post */
		function is_eventon_event_excluded($pmv){}	

/* repeat interval generation when saving event post */
// moved to core since 2.3.13
	function eventon_get_repeat_intervals($unix_S, $unix_E){
		// initial values
		$repeat_type = $_POST['evcal_rep_freq'];
		$repeat_count = (isset($_POST['evcal_rep_num']))? $_POST['evcal_rep_num']: 1;
		$repeat_gap = (isset($_POST['evcal_rep_gap']))? $_POST['evcal_rep_gap']: 1;
		$month_repeat_by = (isset($_POST['evp_repeat_rb']))? $_POST['evp_repeat_rb']: 'dom';
		$wom = (isset($_POST['evo_repeat_wom']))? $_POST['evo_repeat_wom']: 'none';
		$days = (isset($_POST['evo_rep_WK']))? $_POST['evo_rep_WK']: '';

		$errors = array();
		$data = '';

		$repeat_intervals = array();

		// switch statement
			switch($repeat_type){
				case 'daily':
					$term = 'days';
				break;
				case 'monthly':
					$term = 'month';
				break;
				case 'yearly':
					$term = 'year';
				break;
				case 'weekly':
					$term = 'week';
				break;
				case 'custom':
					$term = 'week';
				break;
			}

		// custom repeating 
		if($repeat_type=='custom'&& !empty($_POST['repeat_intervals'])){
			
			$_post_repeat_intervals = $_POST['repeat_intervals'];

			// initials
			$_is_24h = (!empty($_POST['_evo_time_format']) && $_POST['_evo_time_format']=='24h')? true:false;
			$_wp_date_format = $_POST['_evo_date_format'];

			date_default_timezone_set('UTC');

			// make sure repeats are saved along with initial times for event
			$numberof_repeats = count($_post_repeat_intervals);
			if( !empty($unix_E) && !empty($unix_S) && 
				$unix_S != $_post_repeat_intervals[0][0] &&
				$unix_E != $_post_repeat_intervals[0][1] 
			){
				if($numberof_repeats==1)
					$_post_repeat_intervals[] = array($unix_S,$unix_E);
				if($numberof_repeats>=1)
					$_post_repeat_intervals[0] = array($unix_S,$unix_E);
			}

			// each repeat interval
			foreach($_post_repeat_intervals as $interval){
				// for intervals that were added as new
				if(isset($interval['type']) && isset($interval['type'])=='dates'){
					
					// start time
					$__ti = ($_is_24h)?
						date_parse_from_format($_wp_date_format.' H:i', $interval[0]):
						date_parse_from_format($_wp_date_format.' g:ia', $interval[0]);

					// end time
					$__tie = ($_is_24h)?
						date_parse_from_format($_wp_date_format.' H:i', $interval[1]):
						date_parse_from_format($_wp_date_format.' g:ia', $interval[1]);

					$repeat_intervals[] = array(
						mktime($__ti['hour'], $__ti['minute'],0, $__ti['month'], $__ti['day'], $__ti['year'] ),
						mktime($__tie['hour'], $__tie['minute'],0, $__tie['month'], $__tie['day'], $__tie['year'] )
						);
				}else{
					$repeat_intervals[] = array($interval[0],$interval[1]);
				}
			}// end foreach

			// sort repeating dates
			asort($repeat_intervals);

		}else{
			// for each repeat times
			$count = 1;
			for($x =0; $x<=$repeat_count; $x++){

				$repeat_multiplier = ((int)$repeat_gap) * $x;

				// for day of week monthly repears
				if($repeat_type == 'monthly' && $month_repeat_by=='dow' && !empty($days) && is_array($days) ){

					// $wom = week of month
					$Names = array( 0=>"Sun", 1=>"Mon", 2=>"Tue", 3=>"Wed", 4=>"Thu", 5=>"Fri", 6=>"Sat" );


					// find time dif from 12am to selected time
					$dif_S = $unix_S - strtotime( date("Y-m-j", $unix_S) );
					$dif_E = $unix_E - strtotime( date("Y-m-j", $unix_E) );
					$dif_s_e = $unix_E - $unix_S;

					// start time
					$ThisMonthTS = strtotime( date("Y-m-01", strtotime('+'.$repeat_multiplier.' '.$term, $unix_S) ) );
					$NextMonthTS = strtotime( date("Y-m-01", strtotime('+'.($repeat_multiplier+1).' '.$term, $unix_S) ) ); 
					
					// for each day				
					foreach($days as $day){
						// add initial event time values to repeat intervals
						if($count==1){
							$repeat_intervals[] = array($unix_S, $unix_E);
							$count++;
						}

						$new_unix_S = (-1 == $wom) 
						    ? strtotime( "last ".$Names[$day], $NextMonthTS ) 
						    : strtotime( $Names[$day]." + ".($wom-1)." weeks", $ThisMonthTS );

						// add new intervals to array
						$new_start_time_repeat = $new_unix_S+$dif_S;

						// if first repeat instance is before initial event start date
						if($new_start_time_repeat<$unix_S) continue;

						$repeat_intervals[] = array( $new_start_time_repeat, $new_start_time_repeat+$dif_s_e );
						
						$data .= date("Y-m-j", $new_start_time_repeat).' ';	
						
					}
					//$errors[] = $ThisMonthTS;

				}else{
					$new_unix_S = strtotime('+'.$repeat_multiplier.' '.$term, $unix_S);
					$new_unix_E = strtotime('+'.$repeat_multiplier.' '.$term, $unix_E);
					// add new intervals to array
					$repeat_intervals[] = array($new_unix_S, $new_unix_E);
				}				
			}
		}

		//update_post_meta(1350,'aa', $data);
		//return array_merge($repeat_intervals, $errors);
		return $repeat_intervals;
	}

// EVENT COLOR
	/** Return integer value for a hex color code **/
		function eventon_get_hex_val($color){
		    if ($color[0] == '#')
		        $color = substr($color, 1);

		    if (strlen($color) == 6)
		        list($r, $g, $b) = array($color[0].$color[1],
		                                 $color[2].$color[3],
		                                 $color[4].$color[5]);
		    elseif (strlen($color) == 3)
		        list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
		    else
		        return false;

		    $r = hexdec($r); $g = hexdec($g); $b = hexdec($b);
		    $val = (int)(($r+$g+$b)/3);			
		    return $val;
		}

	// get hex color in correct format (with #)
		function eventon_get_hex_color($pmv, $defaultColor='', $opt=''){

			$pure_hex_val = '';

			if(!empty($pmv['evcal_event_color'])){
				// check if color have #
				if( strpos($pmv['evcal_event_color'][0], '#') !== false ){

					// strip all # from hex val
					$pure_hex_val = str_replace('#','',$pmv['evcal_event_color'][0]);
				}else{
					$pure_hex_val = $pmv['evcal_event_color'][0];
				}
			}else{	// if there are no event colors saved

				if(!empty($defaultColor)){
					$pure_hex_val = $defaultColor;
				}else{
					$opt = (!empty($opt))? $opt: get_option('evcal_options_evcal_1');
					$pure_hex_val = ( !empty($opt['evcal_hexcode'])? $opt['evcal_hexcode']: '206177');
				}				
			}
			return '#'.$pure_hex_val;
		}

// SUPPORT FUNCTIONS
	// Generate location latLon from address
		function eventon_get_latlon_from_address($address){
			
			$lat = $lon = '3';

		    //$request_url = "//maps.googleapis.com/maps/api/geocode/xml?address=".$address."&sensor=true";
			//$xml = simplexml_load_file($request_url) or die("url not loading");
			//$status = $xml->status;
			
			//json 
			// $json = file_get_contents("http://maps.google.com/maps/api/geocode/json?address=$address&sensor=false&region=$region");
			//$json = json_decode($json);
			
			$address = str_replace(" ", "+", $address);
			$address = urlencode($address);
			
			$url = "http://maps.google.com/maps/api/geocode/json?address=$address&sensor=false";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			$response = curl_exec($ch);
			curl_close($ch);
			$response_a = json_decode($response);

			if (!empty($response_a) && !empty($response_a->results)) {
			    $lat = $response_a->results[0]->geometry->location->lat;
			    $lon = $response_a->results[0]->geometry->location->lng;

			    //$lat = $xml->result->geometry->location->lat;
			    //$lon = $xml->result->geometry->location->lng;
			}

		    return array(
		        'lat' => $lat,
		        'lng' => $lon,
		    );
		}

	// if the calendar is set to hidden
	// @version 2.3.21
		function evo_cal_hidden(){
			global $eventon;

			$options = $eventon->frontend->evo_options;
			return (!empty($options['evcal_cal_hide']) && $options['evcal_cal_hide']=='yes')? true: false;
		}


	// get URL
		// get url with variables added
			function EVO_get_url($baseurl, $args){
				$str = '';
				foreach($args as $f=>$v){ $str .= $f.'='.$v. '&'; }
				if(strpos($baseurl, '?')!== false){
					return $baseurl.'&'.$str;
				}else{
					return $baseurl.'?'.$str;
				}
			}

	// Returns a proper form of labeling for custom post type
	/** Function that returns an array containing the IDs of the products that are on sale. */
		if( !function_exists ('eventon_get_proper_labels')){
			function eventon_get_proper_labels($sin, $plu){
				return array(
				'name' => _x($plu, 'post type general name' , 'eventon'),
				'singular_name' => _x($sin, 'post type singular name' , 'eventon'),
				'add_new' => __('Add New '. $sin , 'eventon'),
				'add_new_item' => __('Add New '.$sin , 'eventon'),
				'edit_item' => __('Edit '.$sin , 'eventon'),
				'new_item' => __('New '.$sin , 'eventon'),
				'all_items' => __('All '.$plu , 'eventon'),
				'view_item' => __('View '.$sin , 'eventon'),
				'search_items' => __('Search '.$plu , 'eventon'),
				'not_found' =>  __('No '.$plu.' found' , 'eventon'),
				'not_found_in_trash' => __('No '.$plu.' found in Trash' , 'eventon'), 
				'parent_item_colon' => '',
				'menu_name' => _x($plu, 'admin menu', 'eventon')
			  );
			}
		}
	/** Clean variables */
		function eventon_clean( $var ) {
			return sanitize_text_field( $var );
		}
	// Get capabilities for Eventon - these are assigned to admin during installation or reset
		function eventon_get_core_capabilities(){
			$capabilities = array();

			$capabilities['core'] = apply_filters('eventon_core_capabilities',array(
				"manage_eventon"
			));
			
			$capability_types = array( 'eventon' );

			foreach( $capability_types as $capability_type ) {

				$capabilities[ $capability_type ] = array(

					// Post type
					"publish_{$capability_type}",
					"publish_{$capability_type}s",
					"edit_{$capability_type}",
					"edit_{$capability_type}s",
					"edit_others_{$capability_type}s",
					"edit_private_{$capability_type}s",
					"edit_published_{$capability_type}s",

					"read_{$capability_type}s",
					"read_private_{$capability_type}s",
					"delete_{$capability_type}",
					"delete_{$capability_type}s",
					"delete_private_{$capability_type}s",
					"delete_published_{$capability_type}s",
					"delete_others_{$capability_type}s",					

					// Terms
					"assign_{$capability_type}_terms",
					"manage_{$capability_type}_terms",
					"edit_{$capability_type}_terms",
					"delete_{$capability_type}_terms",
					
					"upload_files"
				);
			}
			return $capabilities;
		}
	// currency codes for paypal
		function evo_get_currency_codes(){
			return array(
				'AUD'=>'Australian Dollar',
				'BRL'=>'Brazillian Real',
				'CAD'=>'Canadian Dollar',
				'CZK'=>'Czech Koruna',
				'DKK'=>'Danish Krone',
				'EUR'=>'Euro',
				'HKD'=>'Hong Kong Dollar',
				'HUF'=>'Hungarian Forint',
				'ILS'=>'Israeli New Sheqel',
				'JPY'=>'Japanese Yen',
				'MYR'=>'Malaysian Ringgit',
				'MXN'=>'Mexican Peso',
				'NOK'=>'Norwegian Krone',
				'NZD'=>'New Zealand Dollar',
				'PHP'=>'Philippine Peso',
				'PLN'=>'Polish Zloty',
				'GBP'=>'Pound Sterling',
				'RUB'=>'Russian Ruble',
				'SGD'=>'Singapore Dollar',
				'SEK'=>'Swedish Krona',
				'CHF'=>'Swiss Franc',
				'TWD'=>'Taiwan New Dollar',
				'THB'=>'Thai Baht',
				'TRY'=>'Turkish Lira',
				'USD'=>'US Dollar',
			);
		}

	if(!function_exists('date_parse_from_format')){
		function date_parse_from_format($_wp_format, $date){
			
			$date_pcs = preg_split('/ (?!.* )/',$_wp_format);
			$time_pcs = preg_split('/ (?!.* )/',$date);
			
			$_wp_date_str = preg_split("/[\s . , \: \- \/ ]/",$date_pcs[0]);
			$_ev_date_str = preg_split("/[\s . , \: \- \/ ]/",$time_pcs[0]);
			
			$check_array = array(
				'Y'=>'year',
				'y'=>'year',
				'm'=>'month',
				'n'=>'month',
				'M'=>'month',
				'F'=>'month',
				'd'=>'day',
				'j'=>'day',
				'D'=>'day',
				'l'=>'day',
			);
			
			foreach($_wp_date_str as $strk=>$str){
				
				if($str=='M' || $str=='F' ){
					$str_value = date('n', strtotime($_ev_date_str[$strk]));
				}else{
					$str_value=$_ev_date_str[$strk];
				}
				
				if(!empty($str) )
					$ar[ $check_array[$str] ]=$str_value;		
				
			}
			
			$ar['hour']= date('H', strtotime($time_pcs[1]));
			$ar['minute']= date('i', strtotime($time_pcs[1]));			
			
			return $ar;
		}
	}

	if( !function_exists('date_parse_from_format') ){
		function date_parse_from_format($format, $date) {
		  $dMask = array(
			'H'=>'hour',
			'i'=>'minute',
			's'=>'second',
			'y'=>'year',
			'm'=>'month',
			'd'=>'day'
		  );
		  $format = preg_split('//', $format, -1, PREG_SPLIT_NO_EMPTY); 
		  $date = preg_split('//', $date, -1, PREG_SPLIT_NO_EMPTY); 
		  foreach ($date as $k => $v) {
			if ($dMask[$format[$k]]) $dt[$dMask[$format[$k]]] .= $v;
		  }
		  return $dt;
		}
	}


?>