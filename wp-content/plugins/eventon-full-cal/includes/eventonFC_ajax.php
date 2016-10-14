<?php
/**
 * EventON FullCal Ajax Handlers
 *
 * Handles AJAX requests via wp_ajax hook (both admin and front-end events)
 *
 * @author 		AJDE
 * @category 	Core
 * @package 	EventON-FC/Functions/AJAX
 * @version     0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 *	AJAX
 *	generate the date list for a given month
 *	hooks into each date in fullcal
 */
function evoFC_ajax_days_list(){
	global $eventon_fc;
	
	$filters = ((isset($_POST['filters']))? $_POST['filters']:null);
	
	$start = get_option('start_of_week');

	$month_grid = $eventon_fc->frontend->get_grid_month(
		$_POST['next_d'],
		$_POST['next_m'], 
		$_POST['next_y'], 
		$start,
		$filters,'',
		$_POST['shortcode']
	);
			
	$return_content = array(
		'month_grid'=> $month_grid,
		'status'=>'ok',
	);
	
	echo json_encode($return_content);		
	exit;
}
add_action('wp_ajax_evo_fc', 'evoFC_ajax_days_list');
add_action('wp_ajax_nopriv_evo_fc', 'evoFC_ajax_days_list');

/**
 *	AJAX Filter
 *	Get events for a single day
 *	This plugs into evcal_ajax_callback()
 */
	function evoFC_ajax_filter($eve_args){
		global $eventon_fc;

		if(isset($_POST['shortcode']['load_fullmonth']) && $_POST['shortcode']['load_fullmonth']=='yes' ){
			return $eve_args;
		}else{

			if(isset($_POST['fc_focus_day'])){
				$focused_month_num = date('n', $eve_args['focus_start_date_range']);
				$focused_year = date('Y', $eve_args['focus_start_date_range'] );
				$new_day = $_POST['fc_focus_day'];

				date_default_timezone_set('UTC');
				
				$focus_start_date_range = mktime( 0,0,0,$focused_month_num,$new_day,$focused_year );
				$focus_end_date_range = mktime(23,59,59,($focused_month_num),$new_day, ($focused_year));
				
				$number_days_in_month = $eventon_fc->frontend->days_in_month( $focused_month_num, $focused_year);
				
				
				$eve_args['focus_start_date_range']=$focus_start_date_range;
				$eve_args['focus_end_date_range']=$focus_end_date_range;
				
			}
			return $eve_args;
		}
	}
	add_filter('eventon_ajax_arguments','evoFC_ajax_filter', 10, 2);
?>