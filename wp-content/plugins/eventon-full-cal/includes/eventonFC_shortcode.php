<?php
/**
 * EventON FullCal shortcode
 *
 * Handles all shortcode related functions
 *
 * @author 		AJDE
 * @category 	Core
 * @package 	EventON-FC/Functions/shortcode
 * @version     1.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class evo_fc_shortcode{

	static $add_script;

	function __construct(){
		add_filter('eventon_calhead_shortcode_args', array($this, 'calhead_args'), 10, 2);
		add_shortcode('add_eventon_fc', array($this,'evoFC_generate_calendar'));
		add_filter('eventon_shortcode_popup',array($this,'evoFC_add_shortcode_options'), 10, 1);
	}

	function calhead_args($array, $arg=''){
		if(!empty($arg['load_fullmonth']))
			$array['load_fullmonth'] = $arg['load_fullmonth'];
		return $array;
	}

	/*	Shortcode processing	 */	
		function evoFC_generate_calendar($atts){
			global $eventon_fc, $eventon;

			// if calendar is hidden
				if($eventon_fc->frontend->evo_cal_hidden()) return;

			// add fc scripts to footer
			add_action('wp_footer', array($eventon_fc, 'print_scripts'));

			$eventon_fc->is_running_fc=true;
			
			add_filter('eventon_shortcode_defaults', array($this,'evoFC_add_shortcode_defaults'), 10, 1);
			
			// connect to support arguments
			$supported_defaults = $eventon->evo_generator->get_supported_shortcode_atts();
			
			$args = shortcode_atts( $supported_defaults, $atts ) ;
			
			ob_start();
				
				echo $eventon_fc->frontend->generate_eventon_fc_calendar($args);
			
			return ob_get_clean();
					
		}

	// add new default shortcode arguments
		function evoFC_add_shortcode_defaults($arr){			
			return array_merge($arr, array(
				'fixed_day'=>0,
				'day_incre'=>0,
				'hide_sort_options'=>'no',
				'mo1st'=>'',
				'grid_ux'=>0,
				'load_fullmonth'=>'no',
				'heat'=>'no',	// heat graph style grid background colors
				'style'=>'',	// styles for full cal 
				'hover'=>'number',	// information on hover
				'nexttogrid'=>'no', // whether event list show next to month grid
			));			
		}

	/*	ADD shortcode buttons to eventON shortcode popup	*/
		function evoFC_add_shortcode_options($shortcode_array){
			global $evo_shortcode_box;
			
			$new_shortcode_array = array(
				array(
					'id'=>'s_FC',
					'name'=>'FullCal',
					'code'=>'add_eventon_fc',
					'variables'=>array(
						$evo_shortcode_box->shortcode_default_field('cal_id'),
						$evo_shortcode_box->shortcode_default_field('show_et_ft_img'),
						$evo_shortcode_box->shortcode_default_field('ft_event_priority')
						,array(
							'name'=>'Month Grid Interaction',
							'type'=>'select',
							'guide'=>'Select the user interaction option when a user click on a date box inside the month grid. "Focus to Events" will scroll page and focus on events list.',
							'var'=>'grid_ux',
							'default'=>'0',
							'options'=>apply_filters('evofc_uix_shortcode_opts', array(
								'0'=>'Default',
								'1'=>'Focus to Events',
								'2'=>'Lightbox Events List'
							))
						),array(
							'name'=>'Show events next to grid*',
							'type'=>'YN',
							'guide'=>'Only works when month grid interaction is set to default. The events list will show as 50% width column on right side of month grid.',
							'var'=>'nexttogrid',
							'default'=>'no'
						),array(
							'name'=>'Show all events of the month below calendar',
							'type'=>'YN',
							'guide'=>'Yes = Show entire month of events on load & when switching months.',
							'var'=>'load_fullmonth',
							'default'=>'no'
						),
						array(
							'name'=>'Day Increment',
							'type'=>'text',
							'placeholder'=>'eg. +1',
							'guide'=>'Change starting date (eg. +1)',
							'var'=>'day_incre',
							'default'=>'0'
						),
						$evo_shortcode_box->shortcode_default_field('month_incre'),
						$evo_shortcode_box->shortcode_default_field('fixed_month'),
						$evo_shortcode_box->shortcode_default_field('fixed_year'),
						$evo_shortcode_box->shortcode_default_field('event_type'),
						$evo_shortcode_box->shortcode_default_field('event_type_2'),
						array(
							'name'=>'Fixed Day',
							'type'=>'text',
							'guide'=>'Set fixed day as calendar focused day (integer)',
							'var'=>'fixed_day',
							'default'=>'0',
							'placeholder'=>'eg. 10'
						),
						$evo_shortcode_box->shortcode_default_field('etc_override'),
						array(
							'name'=>__('Open eventCards on load *'),
							'type'=>'YN',
							'guide'=>'Open eventcards when the calendar first load on the page by default. This will override the settings saved for default calendar. This does not apply if grid UX is set to lightbox **',
							'var'=>'evc_open',
							'default'=>'no'
						),
						//$evo_shortcode_box->shortcode_default_field('evc_open'),
						$evo_shortcode_box->shortcode_default_field('event_order'),
						$evo_shortcode_box->shortcode_default_field('lang'),
						$evo_shortcode_box->shortcode_default_field('jumper'),
						array(
							'name'=>'Switch to first of month',
							'type'=>'YN',
							'guide'=>'Yes = when switching month focus day will go to 1st of new month',
							'var'=>'mo1st',
							'default'=>'no'
						),array(
							'name'=>'Heat style box coloring',
							'type'=>'YN',
							'guide'=>'Boxes with more events will have darker color than boxes with fewer events.',
							'var'=>'heat',
							'default'=>'no'
						),array(
							'name'=>'Date hover information',
							'type'=>'select',
							'guide'=>'Select what information you want to display on the calendar when hover over calendar dates',
							'var'=>'hover',
							'default'=>'number',
							'options'=>apply_filters('evofc_date_hover', array('number'=>'Number of Events','numname'=>'Events + First 3 Names'))
						),array(
							'name'=>'FullCal Style',
							'type'=>'select',
							'guide'=>'Select different fullcal grid styles from available options.',
							'var'=>'style',
							'default'=>'0',
							'options'=>apply_filters('evofc_grid_styles', array('def'=>'Default','nobox'=>'No Date Outline'))
						)
					)
				)
			);

			return array_merge($shortcode_array, $new_shortcode_array);
		}


}




?>