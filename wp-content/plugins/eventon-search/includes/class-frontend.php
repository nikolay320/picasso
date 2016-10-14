<?php
/**
 * Front End class for this addon
 *
 * @version 	0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class evosr_front{

	function __construct(){
		add_filter('evo_cal_above_header_btn', array($this, 'header_search_button'), 10, 2);
		add_filter('evo_cal_above_header_content', array($this, 'header_search_bar'), 10, 2);
		add_action('evo_cal_footer', array($this, 'remove_search_bar'), 10);

		// scripts and styles 
		add_action( 'init', array( $this, 'register_styles_scripts' ) ,15);	
		add_action('eventon_enqueue_scripts', array($this,'enque_script'));
		
		//shortcodes
		add_filter('eventon_shortcode_defaults', array($this,'add_shortcode_defaults'), 10, 1);
	}

	// include search in header section
		function header_search_button($array, $args){
			if(!empty($args['search']) && $args['search']=='yes'){
				$new['evo-search']='';
				$array = array_merge($new, $array);
			}
			return $array;
		}

	// header search bar
		function header_search_bar($array, $args){
			if(!empty($args['search']) && $args['search']=='yes'){
				ob_start();?>
					
					<div class='evo_search_bar'>
						<div class='evo_search_bar_in'>
							<input type="text" placeholder='<?php echo eventon_get_custom_language('', 'evoSR_001', 'Search Events');?>'/>
						</div>
					</div>

				<?php
				$content = ob_get_clean();
				$array['evo-search']= $content;
			}
			return $array;
		}
	// remove search bar
		function remove_search_bar(){
			//remove_filter('evo_cal_above_header_btn',array($this, 'header_search_button'));
			//remove_filter('evo_cal_above_header_content',array($this, 'header_search_bar'));
		}

	//shortcode defaults
		function add_shortcode_defaults($arr){
			return array_merge($arr, array(
				'search'=>'yes',
			));		
		}

	// styles and scripts
		function register_styles_scripts(){
			global $eventon_sr;
			wp_register_style( 'evo_sr_styles',$eventon_sr->plugin_url.'/assets/styles.css');
			wp_enqueue_style( 'evo_sr_styles');	

			wp_register_script('evo_sr_script',$eventon_sr->plugin_url.'/assets/script.js', array('jquery'), 1.0, true );			
		}
		function enque_script(){
			wp_enqueue_script('evcal_easing');
			wp_enqueue_script('evo_sr_script');
		}

}
new evosr_front();