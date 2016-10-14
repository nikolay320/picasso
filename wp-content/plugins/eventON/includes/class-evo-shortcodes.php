<?php
/**
 * EVO_Shortcodes class.
 *
 * @class 		EVO_Shortcodes
 * @version		2.4
 * @package		EventON/Classes
 * @category	Class
 * @author 		AJDE
 */

class EVO_Shortcodes {
	public function __construct(){
		// regular shortcodes
		add_shortcode('add_ajde_evcal',array($this,'eventon_show_calendar'));	// for eventon ver < 2.0.8	
		add_shortcode('add_eventon',array($this,'eventon_show_calendar'));
		add_shortcode('add_eventon_list',array($this,'events_list'));		
		add_shortcode('add_eventon_tabs',array($this,'eventon_tabs'));		
	}	
	
	// Tab view for eventon calendar
		function eventon_tabs($atts){
			$defaults = array(
				'tab1'=>'Calendar View',
				'tab1shortcode'=>'add_eventon'
			);
			$args = array_merge($defaults, $atts);

			ob_start();
			echo "<div class='evo_tab_view'>";
			echo "<ul class='evo_tabs'>";
			for($x=1; $x<=4; $x++){
				if(empty($args['tab'.$x]) || empty($args['tab'.$x.'shortcode'])) continue;

				echo "<li class='evo_tab ". ($x==1? 'selected':'')."' data-tab='tab_".'tab'.$x."'>".$args['tab'.$x]."</li>";
			}
			echo "</ul>";

			echo "<div class='evo_tab_container'>";
			for($x=1; $x<=4; $x++){
				if(empty($args['tab'.$x]) || empty($args['tab'.$x.'shortcode'])) continue;

				echo "<div class='evo_tab_section ". ($x==1?'visible':'') ." tab_".'tab'.$x."'>";
				$shortcode = '['. $args['tab'.$x.'shortcode'] . ']';
				
				echo do_shortcode($shortcode);
				echo "</div>";
			}
			echo "</div>";
			return ob_get_clean();
		}

	/*	Show multiple month calendar */
		public function events_list($atts){
			
			global $eventon;
			
			add_filter('eventon_shortcode_defaults', array($this,'event_list_shortcode_defaults'), 10, 1);
			
			// connect to support arguments
			$supported_defaults = $eventon->evo_generator->get_supported_shortcode_atts();
			
			$args = shortcode_atts( $supported_defaults, $atts ) ;	
						
			// OUT PUT	
			// check if member only calendar
			if($eventon->frontend->is_member_only($args) ){			
				ob_start();				
				echo $eventon->evo_generator->generate_events_list($args);			
				return ob_get_clean();		
			}else{
				echo $eventon->frontend->nonMemberCalendar();
			}	
		}
	
	// add new default shortcode arguments
		public function event_list_shortcode_defaults($arr){		
			return array_merge($arr, array(
				'hide_empty_months'=>'no',
				'show_year'=>'no',
			));		
		}
	
	/** Show single month calendar shortcode */
		public function eventon_show_calendar($atts){
			global $eventon;
			
			// connect to support arguments
			$supported_defaults = apply_filters('eventon_shortcode_default_values', $eventon->evo_generator->shell->get_supported_shortcode_atts());	
			
			$args = shortcode_atts( $supported_defaults, $atts ) ;	
			
			$args = apply_filters('eventon_shortcode_argument_update', $args);	
			
			// OUT PUT
			
			// check if member only calendar
			if($eventon->frontend->is_member_only($args) ){			
				ob_start();				
				echo $eventon->evo_generator->eventon_generate_calendar($args);			
				return ob_get_clean();
			}else{
				echo $eventon->frontend->nonMemberCalendar();
			}
		}
}
?>