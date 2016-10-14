<?php
/**
 * EventON WeeklyView Ajax Handlers
 *
 * Handles ajax functions
 *
 * @author 		AJDE
 * @category 	Core
 * @package 	EventON-WV/ajax/
 * @version     0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Load Day strip for week view
	function evoWV_ajax_days_list(){
		global $eventon_wv;
		
		$filters = ((isset($_POST['filters']))? $_POST['filters']:null);
		
		$wv_grid_week = $eventon_wv->frontend->get_grid_week(
			1,
			$_POST['next_m'], 
			$_POST['next_y'],			
			$filters,'',
			$_POST['shortcode'], 
			$_POST['focus_week']
		);

		$fwd = $eventon_wv->frontend->focus_data;
				
		$return_content = array(
			'content'=> $wv_grid_week,
			'evodata'=> array(
				'focus_week'=>$fwd['focus_week'],
				'dim'=>$fwd['number_days_in_month'],
				'wim'=>$fwd['weeksinMonth'],
				'difw'=>$fwd['difw'],
			),
			'status'=>'ok'
		);
		
		echo json_encode($return_content);		
		exit;
	}
	add_action('wp_ajax_the_ajax_wv2', 'evoWV_ajax_days_list');
	add_action('wp_ajax_nopriv_the_ajax_wv2', 'evoWV_ajax_days_list');

/**
 *	AJAX Filter
 *	Get events for a single week
 *	This plugs into evcal_ajax_callback()
 */
function evoWV_ajax_filter($eve_args, $post=''){
	global $eventon_wv;

	//print_r($eve_args);
	
	if(isset($_POST['wv_focus_week'])){

		$month = date('n', $eve_args['focus_start_date_range']);
		$year = date('Y', $eve_args['focus_start_date_range'] );

		// get days in first week
		$first_dow = date('N', mktime(0,0,0,$month, 1, $year));
		$startOfWeek = ($eventon_wv->frontend->start_of_week ==0)? 
			7: $eventon_wv->frontend->start_of_week;
		
		$difw = ($startOfWeek > $first_dow)? 
			($startOfWeek-$first_dow): (7- ($first_dow- $startOfWeek));

		// focus week
			$FOCUSWEEK = (!empty($_POST['always_first_week']) && $_POST['always_first_week']=='yes')?
				'1':$_POST['wv_focus_week'];
		
		// get date range
		$range = $eventon_wv->frontend->get_cal_date_range(array(
			'focus_week'=>$FOCUSWEEK, 
			'difw'=>$difw, 
			'month'=>$month, 
			'year'=>$year),
			true
		);
		
		$eve_args['focus_start_date_range']=$range[0];
		$eve_args['focus_end_date_range']=$range[1];
	}
	return $eve_args;
}
add_filter('eventon_ajax_arguments','evoWV_ajax_filter', 10, 2);

// get new week of events
// not using since v0.7
	function evoWV_ajax_events_list(){
		global $eventon_wv;
		
		$filters = ((isset($_POST['filters']))? $_POST['filters']:null);
		
		$args = array(
			'month'=>$_POST['evodata']['cmonth'],
			'fixed_month'=>$_POST['evodata']['cmonth'],
			'year'=>$_POST['evodata']['cyear'],
			'fixed_year'=>$_POST['evodata']['cyear'],
			'focus_week'=>$_POST['newWeek'],
			'difw'=>$_POST['evodata']['difw'],
			'wim'=>$_POST['evodata']['wim'],
		);

		$args = array_merge($args, $_POST['shortcode']);
		if($filters){
			$filter_['filters']=$filters;
			$args = array_merge($args, $filter_);
		}

		// set new focused week information to wv class
		$eventon_wv->focus_data['month'] = $_POST['evodata']['cmonth'];
		$eventon_wv->focus_data['year'] = $_POST['evodata']['cyear'];
		$eventon_wv->focus_data['number_days_in_month'] = $_POST['evodata']['dim'];

		$event_list = $eventon_wv->frontend->generate_eventon_wv_calendar(
			$args,'listOnly'
		);

		$return_content = array(
			'content'=> $event_list,
			'status'=>'ok'
		);
		
		echo json_encode($return_content);		
		exit;
	}
	//add_action('wp_ajax_the_ajax_wv', 'evoWV_ajax_events_list');
	//add_action('wp_ajax_nopriv_the_ajax_wv', 'evoWV_ajax_events_list');
?>