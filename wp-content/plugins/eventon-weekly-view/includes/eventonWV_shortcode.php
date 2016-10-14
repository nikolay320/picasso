<?php
/**
 * EventON WeeklyView Ajax Handlers
 *
 * Handles shortcode functions
 *
 * @author 		AJDE
 * @category 	Core
 * @package 	EventON-WV/shortcode/
 * @version     0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class evo_wv_shortcode{

	static $add_script;

	function __construct(){
		add_shortcode('add_eventon_wv', array($this,'evoWV_generate_calendar'));
		add_filter('eventon_shortcode_popup',array($this,'evoWV_add_shortcode_options'), 10, 1);
	}

	/**	Shortcode processing */	
		function evoWV_generate_calendar($atts){
			global $eventon_wv, $eventon;

			// add fc scripts to footer
			add_action('wp_footer', array($eventon_wv, 'print_scripts'));

			$eventon_wv->is_running_wv=true;
			
			add_filter('eventon_shortcode_defaults', array($this,'evoWV_add_shortcode_defaults'), 10, 1);

			// connect to support arguments
			$supported_defaults = $eventon->evo_generator->get_supported_shortcode_atts();
			
			$args = shortcode_atts( $supported_defaults, $atts ) ;			
			
			ob_start();
				
				echo $eventon_wv->frontend->generate_eventon_wv_calendar($args);
			
			return ob_get_clean();
					
		}

	// add new default shortcode arguments
		function evoWV_add_shortcode_defaults($arr){			
			return array_merge($arr, array(
				'focus_week'=>0,		
				'always_first_week'=>'no',		
			));			
		}

	/*	ADD shortcode buttons to eventON shortcode popup	*/
		function evoWV_add_shortcode_options($shortcode_array){
			global $evo_shortcode_box;
			
			$new_shortcode_array = array(
				array(
					'id'=>'s_WV',
					'name'=>'WeeklyView',
					'code'=>'add_eventon_wv',
					'variables'=>array(
						$evo_shortcode_box->shortcode_default_field('show_et_ft_img'),
						$evo_shortcode_box->shortcode_default_field('ft_event_priority'),					
						$evo_shortcode_box->shortcode_default_field('month_incre'),
						$evo_shortcode_box->shortcode_default_field('event_type'),
						$evo_shortcode_box->shortcode_default_field('event_type_2'),
						$evo_shortcode_box->shortcode_default_field('fixed_month'),
						$evo_shortcode_box->shortcode_default_field('fixed_year'),
						array(
							'name'=>__('Focued Week on load','eventon'),
							'type'=>'text',
							'guide'=>'Set fixed week as calendar focused week on load. Between 1 to 6 depending on number of weeks in the month (integer)',
							'var'=>'focus_week',
							'default'=>'0',
							'placeholder'=>'eg. 2'
						),
						/*
						,array(
							'name'=>__('Always focus on 1st week when switching months','eventon'),
							'type'=>'YN',
							'guide'=>'Always focus on first week when switching months',
							'var'=>'always_first_week',
							'default'=>'no',
						),*/
						$evo_shortcode_box->shortcode_default_field('event_order'),
						$evo_shortcode_box->shortcode_default_field('lang'),
						$evo_shortcode_box->shortcode_default_field('jumper'),						
					)
				)
			);

			return array_merge($shortcode_array, $new_shortcode_array);
		}
}
?>