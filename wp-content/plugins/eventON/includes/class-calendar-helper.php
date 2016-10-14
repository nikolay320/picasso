<?php
/**
 * helper fnctions for calendar
 *
 * @class 		evo_cal_help
 * @version		2.3.23
 * @package		EventON/Classes
 * @category	Class
 * @author 		AJDE
 */

class evo_cal_help {

	public function __construct(){
		// /$this->options = get_option('evcal_options_evcal_1');
	}
	
	// return classes array as a string
		function get_eventinclasses($atts){
			 
			$classnames[] = (!empty($atts['img_thumb_src']) && !empty($atts['show_et_ft_img']) && $atts['show_et_ft_img']=='yes')? 'hasFtIMG':'';

			$classnames[] = ($atts['event_type']!='nr')? 'event_repeat':null;	
			$classnames[] = $atts['event_description_trigger'];

			$classnames[] = (!empty($atts['existing_classes']['__featured']) && $atts['existing_classes']['__featured'])? 'featured_event':null;
			$classnames[] = (!empty($atts['existing_classes']['_cancel']) && $atts['existing_classes']['_cancel'])? 'cancel_event':null;
			$classnames[] = (!empty($atts['existing_classes']['_completed']) && $atts['existing_classes']['_completed'])? 'completed-event':null;

			$classnames[] = ($atts['monthlong'])? 'month_long':null;
			$classnames[] = ($atts['yearlong'])? 'year_long':null;

			
			// filter through existing class and remove true false values
				$existingClasses = array();
				if(is_array($atts)){
					foreach($atts['existing_classes'] as $field=>$value){
						//if($field==0 || $field ==1) continue;
						$existingClasses[$field]= $value;
					}
				}

			$classnames = array_merge($classnames, $existingClasses);
			$classnames = array_filter($classnames);

			return implode(' ',  $classnames);
		}

	function implode($array=''){
		if(empty($array))
			return '';

		return implode(' ', $array);
	}

	function get_attrs($array){
		if(empty($array)) return;

		$output = '';
		$array = array_filter($array);

		foreach($array as $key=>$value){
			if($key=='style' && !empty($value)){
				$output .= 'style="'. implode("", $value).'" ';
			}elseif($key=='rest'){
				$output .= implode(" ", $value);
			}else{
				$output .= $key.'="'.$value.'" ';
			}
		}

		return $output;
	}

	function evo_meta($field, $array, $type=''){
		switch($type){
			case 'tf':
				return (!empty($array[$field]) && $array[$field][0]=='yes')? true: false;
			break;
			case 'yn':
				return (!empty($array[$field]) && $array[$field][0]=='yes')? 'yes': 'no';
			break;
			case 'null':
				return (!empty($array[$field]) )? $array[$field][0]: null;
			break;
			default;
				return (!empty($array[$field]))? true: false;
			break;
		}		
	}

	// sort eventcard fields 
		function eventcard_sort($array, $opt){

			$evoCard_order = $opt['evoCard_order'];
			
			$new_array = array();
			
			// create an array
			$correct_order = (!empty($evoCard_order))? 
				explode(',',$evoCard_order): null;
			
			if(!empty($correct_order)){
				$evoCard_hide = (!empty($opt['evoCard_hide']))? 
					explode(',',$opt['evoCard_hide']): null;

				// each saved order item
				foreach($correct_order as $box){
					if(array_key_exists($box, $array) 
						&& (!empty($evoCard_hide) && !in_array($box, $evoCard_hide) || empty($evoCard_hide)) 
					){
						$new_array[$box]=$array[$box];
					}
				}
			}else{
				$new_array = $array;
			}	
			return $new_array;
		}

	// get repeating intervals for the event
		function get_ri_for_event($event_){
			return (!empty($event_['event_repeat_interval'])? 
				$event_['event_repeat_interval']: 
				( !empty($_GET['ri'])?$_GET['ri']: 0) );
		}

	// get event type #1 font awesome icon
		function get_tax_icon($tax, $term_id, $opt){

			if(!empty($opt['evcal_hide_filter_icons']) && $opt['evcal_hide_filter_icons']=='yes') return false;

			$icon_str = false;
			if($tax == 'event_type'){
				$term_meta = get_option( "evo_et_taxonomy_$term_id" ); 
				if( !empty($term_meta['et_icon']) )
					$icon_str = '<i class="fa '. $term_meta['et_icon']  .'"></i>';
			}
			return $icon_str;
		}

	// get all event default values
		function get_calendar_defaults(){
			global $eventon;
			$options = $eventon->evo_generator->evopt1;

			$defaults = array();
			// default event image
				if(!empty($options['evcal_default_event_image_set']) && $options['evcal_default_event_image_set']=='yes' && !empty($options['evcal_default_event_image']) ){
					$defaults['image'] = $options['evcal_default_event_image'];
				}

			// default event color
				$defaults['color'] = (!empty($options['evcal_hexcode']))? '#'.$options['evcal_hexcode']:'#206177';
			// event top fields
				$defaults['eventtop_fields'] = (!empty($options['evcal_top_fields']))? $options['evcal_top_fields']:null;

			// check if single events addon active
				$defaults['single_addon']  = (in_array( 'eventon-single-event/eventon-single-event.php', get_option( 'active_plugins' ) ) )? true:false;

			return $defaults;
		}
}