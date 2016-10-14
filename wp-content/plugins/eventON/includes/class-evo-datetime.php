<?php
/**
 * Eventon date time class.
 *
 * @class 		EVO_generator
 * @version		2.3.20
 * @package		EventON/Classes
 * @category	Class
 * @author 		AJDE
 */

class evo_datetime{		
	/**	Construction function	 */
		public function __construct(){
			$this->wp_time_format = get_option('time_format');
			$this->wp_date_format = get_option('date_format');
		}

	// RETURN UNIX
		// return repeat interval correct unix time stamp for start OR end
			public function get_int_correct_event_time($post_meta, $repeat_interval, $time='start'){
				if(!empty($post_meta['repeat_intervals']) && $repeat_interval>0 ){					
					$intervals = unserialize($post_meta['repeat_intervals'][0]);

					if(isset($intervals[$repeat_interval])){
						return ($time=='start')? $intervals[$repeat_interval][0]:$intervals[$repeat_interval][1];
					}else{
						return ($time=='start')? $post_meta['evcal_srow'][0]:$post_meta['evcal_erow'][0];
					}
					
				}else{
					return ($time=='start')? $post_meta['evcal_srow'][0]:$post_meta['evcal_erow'][0];
				}
			}
		// return just UNIX timestamps corrected for repeat intervals
			public function get_correct_event_repeat_time($post_meta, $repeat_interval=''){
				if(!empty($repeat_interval) && !empty($post_meta['repeat_intervals']) && $repeat_interval!='0'){
					$intervals = unserialize($post_meta['repeat_intervals'][0]);

					return array(
						'start'=> (isset($intervals[$repeat_interval][0])? 
							$intervals[$repeat_interval][0]:
							$intervals[0][0]),
						'end'=> (isset($intervals[$repeat_interval][1])? 
							$intervals[$repeat_interval][1]:
							$intervals[0][1]) ,
					);

				}else{// no repeat interval values saved
					$start = !empty($post_meta['evcal_srow'])? $post_meta['evcal_srow'][0] :0;
					return array(
						'start'=> $start,
						'end'=> ( !empty($post_meta['evcal_erow'])? $post_meta['evcal_erow'][0]: $start)
					);
				}
			}

	/*
	 * Return: array(start, end)
	 * Returns WP proper formatted corrected event time based on repeat interval provided
	 */
		public function get_correct_formatted_event_repeat_time($post_meta, $repeat_interval='', $date_format=''){
			
			// get date and time formats
			$date_format = (!empty($date_format)? $date_format: get_option('date_format'));			
			$wp_time_format = get_option('time_format');

			if(!empty($repeat_interval) && !empty($post_meta['repeat_intervals']) && $repeat_interval!='0'){
				$intervals = unserialize($post_meta['repeat_intervals'][0]);

				$formatted_unix_s = eventon_get_formatted_time($intervals[$repeat_interval][0]);
				$formatted_unix_e = eventon_get_formatted_time($intervals[$repeat_interval][1]);

				return array(
					// this didnt work on tickets addon
					'start_'=> eventon_get_lang_formatted_timestr($date_format.' '.$wp_time_format, $formatted_unix_s),
					'end_'=> eventon_get_lang_formatted_timestr($date_format.' '.$wp_time_format, $formatted_unix_e),

					'start'=> date_i18n($date_format.' h:i:a',$intervals[$repeat_interval][0]),
					'end'=> date_i18n($date_format.' h:i:a',$intervals[$repeat_interval][1]),
				);

			}else{// no repeat interval values saved
				$start = !empty($post_meta['evcal_srow'])? date_i18n($date_format.' h:i:a', $post_meta['evcal_srow'][0]) :0;
				$start_row =  !empty($post_meta['evcal_srow'])? $post_meta['evcal_srow'][0]: time();
				$end_row =  !empty($post_meta['evcal_erow'])? $post_meta['evcal_erow'][0]: $post_meta['evcal_erow'][0];
				$end = ( !empty($post_meta['evcal_erow'])? date_i18n($date_format.' h:i:a',$post_meta['evcal_erow'][0]): $start);

				$formatted_unix_s = eventon_get_formatted_time($start_row);
				$formatted_unix_e = eventon_get_formatted_time($end_row);



				//echo $end_row.' '.$post_meta['evcal_srow'][0];
				return array(
					'start'=> $start,
					'end'=> $end,
					'start_'=> eventon_get_lang_formatted_timestr($date_format.' '.$wp_time_format, $formatted_unix_s),
					'end_'=> eventon_get_lang_formatted_timestr($date_format.' '.$wp_time_format, $formatted_unix_e),
				);
			}
		}

	// return start OR end time unix in translated and formatted date-time-string
		function get_formatted_smart_time_piece($unix, $pmv, $lang=''){
			$time_ = eventon_get_formatted_time($unix);
			$_is_allday = (!empty($epmv['evcal_allday']) && $epmv['evcal_allday'][0]=='yes')? true:false;
			
			if($_is_allday){
				$output = $this->date($this->wp_date_format, $time_).' ('.evo_lang_get('evcal_lang_allday','All Day').')';
			}else{// not all day
				$output = $this->date($this->wp_date_format.' '.$this->wp_time_format, $time_);
			}
			return $output;
		}

	

	// return a smarter complete date-time -translated and formatted to date-time string
	// 2.3.13
		public function get_formatted_smart_time($startunix, $endunix, $epmv){

			$wp_time_format = get_option('time_format');
			$wp_date_format = get_option('date_format');

			$start_ar = eventon_get_formatted_time($startunix);
			$end_ar = eventon_get_formatted_time($endunix);
			$_is_allday = (!empty($epmv['evcal_allday']) && $epmv['evcal_allday'][0]=='yes')? true:false;
			$hideend = (!empty($epmv['evo_hide_endtime']) && $epmv['evo_hide_endtime'][0]=='yes')? true:false;

			$output = '';

			// reused
				$joint = $hideend?'':' - ';

			// same year
			if($start_ar['y']== $end_ar['y']){
				// same month
				if($start_ar['n']== $end_ar['n']){
					// same date
					if($start_ar['j']== $end_ar['j']){
						if($_is_allday){
							$output = $this->date($wp_date_format, $start_ar) .' ('.evo_lang_get('evcal_lang_allday','All Day').')';
						}else{
							$output = $this->date($wp_date_format.' '.$wp_time_format, $start_ar).$joint. 
								(!$hideend? $this->date($wp_time_format, $end_ar):'');
						}
					}else{// dif dates
						if($_is_allday){
							$output = $this->date($wp_date_format, $start_ar).' ('.evo_lang_get('evcal_lang_allday','All Day').')'.$joint.
								(!$hideend? $this->date($wp_date_format, $end_ar).' ('.evo_lang_get('evcal_lang_allday','All Day').')':'');
						}else{
							$output = $this->date($wp_date_format.' '.$wp_time_format, $start_ar).$joint.
								(!$hideend? $this->date($wp_date_format.' '.$wp_time_format, $end_ar):'');
						}
					}
				}else{// dif month
					if($_is_allday){
						$output = $this->date($wp_date_format, $start_ar).' ('.evo_lang_get('evcal_lang_allday','All Day').')'.$joint.
							(!$hideend? $this->date($wp_date_format, $end_ar).' ('.evo_lang_get('evcal_lang_allday','All Day').')':'');
					}else{// not all day
						$output = $this->date($wp_date_format.' '.$wp_time_format, $start_ar).$joint.
							(!$hideend? $this->date($wp_date_format.' '.$wp_time_format, $end_ar):'');
					}
				}
			}else{
				if($_is_allday){
					$output = $this->date($wp_date_format, $start_ar).' ('.evo_lang_get('evcal_lang_allday','All Day').')'.$joint.
						(!$hideend? $this->date($wp_date_format, $end_ar).' ('.evo_lang_get('evcal_lang_allday','All Day').')':'');
				}else{// not all day
					$output = $this->date($wp_date_format.' '.$wp_time_format, $start_ar). $joint .
						(!$hideend? $this->date($wp_date_format.' '.$wp_time_format, $end_ar):'');
				}
			}
			return $output;	
		}


	// return datetime string for a given format using date-time data array
		public function date($dateformat, $array){	
			return eventon_get_lang_formatted_timestr($dateformat, $array);
			
			/*$items = str_split($dateformat);
			$newtime = '';
			foreach($items as $item){
				$newtime .= (array_key_exists($item, $array))? $array[$item]: $item;
			}
			return $newtime;*/
		} 

	// eventon version of converted date time 
	// also filter for proper all day text
		function evo_date($unix, $eventPMV=null){
			date_default_timezone_set('UTC');
			$date_format = get_option('date_format');
			$time_format = get_option('time_format');
			$alldaytext = false;

			if($eventPMV){
				if(!empty($eventPMV['evcal_allday']) && $eventPMV['evcal_allday'][0]=='yes'){
					global $eventon;
					$alldaytext = $eventon->frontend->lang('','evcal_lang_allday','All Day');
				}				
			}

			return date_i18n($date_format.' '.($alldaytext? '':$time_format), $unix). ( $alldaytext? '('.$alldaytext.')':'' );
		}

}