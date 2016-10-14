<?php
/**
 * Event Reviewer shortcode
 *
 * Handles all shortcode related functions
 *
 * @author 		AJDE
 * @category 	Core
 * @package 	EventON-RE/Functions/shortcode
 * @version     0.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class evo_re_shortcode{

	static $add_script;

	function __construct(){		
		// add_filter('eventon_shortcode_popup',array($this, 'add_shortcode_options'), 10, 1);
		// add_shortcode('evo_review_manager',array($this, 'evo_review_manager'));
	}
	
	function add_shortcode_options($shortcode_array){
		global $evo_shortcode_box;
		
		$new_shortcode_array = array(
			array(
				'id'=>'s_re',
				'name'=>'User Review Manager',
				'code'=>'evo_review_manager',
				'variables'=>''
			)
		);
		return array_merge($shortcode_array, $new_shortcode_array);
	}

	// Frontend event review manager
		public function evo_review_manager($atts){
			global $eventon_re;
			ob_start();				
			echo $eventon_re->frontend->user_review_manager($atts);			
			return ob_get_clean();
		}
}
?>