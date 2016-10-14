<?php 
/*
 * Plugin Name: Buddypress Activity Stream for Events
 * Plugin URI: http://92.243.16.117/picasso-test
 * Description: Populates acitivity stream after each event gneration  from front end.
 * Author: Mahibul Hasan
 * Version: 2.0.1
 * Author URI: http://picasso/
 * Depenency: buddypress, eventon, EventON - Action User,
 * Modifications: BuddyPress Avatar Bubble ---> cd-avatar-bubble.php ---> cd_ab_rel_activity_filter($action)
 */
 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Budypress_Activity_Bind_With_CustomPostTypes{
	
	var $custom_post_types = 'ajde_events';
	var $single_event;
	var $events_list;	
	
	//contains hooks & initialization
	function __construct(){
		
		//EventON - Action User hook to push an acivity		
		add_action('eventonau_save_form_submissions', array($this, 'activity_after_event_generation'), 100, 2);
		
		//byddypess hook to register new actions under activity component
		add_action('bp_activity_register_activity_actions', array($this, 'bp_register_new_activity_actions'), 20);
		
		//buddypress hook to filter action before action meta insertion
		add_filter('bp_get_activity_action_pre_meta', array($this, 'bp_get_activity_action'), 100);
		
		//insert single event data with activity content
		add_filter('bp_get_activity_content_body', array($this, 'bp_get_activity_content_body'), 100);
		
		//setup content size in activity stream
		add_filter('bp_activity_excerpt_length', array($this, 'bp_truncate_excerpt_length'), 100);
		
		//default wp hook to include javascript from asset folder
		add_action('wp_enqueue_scripts', array($this, 'include_event_js'));
	
		//load language file
		add_action('plugins_loaded', array($this, 'load_textdomain'));
	}
	
	
	/*
	Crates an activity with an event
	@event_id: newley created event id
	@formtype: conditionals to identify between new event and old even
	this function also saves relations between event and activity 	
	*/
	function activity_after_event_generation($event_id, $formtype){		
		if(!function_exists('bp_activity_add')) return; //buddypresss not installed
		$current_user = wp_get_current_user();
		$event = get_post($event_id);
		
		//dealing with event thumbnail
		if(has_post_thumbnail($event_id)) {
			//$event_thumbnail = get_the_post_thumbnail( $event_id, array(150, 150) );
			$event_thumbnail = get_the_post_thumbnail( $event_id );
			$event_thumbnail_id = get_post_thumbnail_id( $event_id );
		}		
		
		//@form_type: new means new event, edit means exisiting events			
		if($formtype == 'new'){
			$args = array(
				'action' => sprintf( __( '%s published a new event', 'buddypress_eventon' ), bp_core_get_userlink( $current_user->id ) ),
				'component' => 'activity',
				'type' => 'activity_new_event',
				'content' => '<span class="event_thumbnail alignleft">' . $event_thumbnail . '</span> ' . $event->post_content,
				'item_id' => $event_id,
			);
		}
		
		if($formtype == 'edit'){
			$args = array(
				'id' => $this->get_activity_id_from_event_id($event_id),
				'action' => sprintf( __( '%s published a new event', 'buddypress_eventon' ), bp_core_get_userlink( $current_user->id ) ),
				'component' => 'activity',
				'type' => 'activity_edit_event',
				'content' => $event_thumbnail . ' ' . $event->post_content,
				'item_id' => $event_id,
					
			);
		}
				
		
		
		$bp_created_activity_id = bp_activity_add($args);
		if($bp_created_activity_id){
			update_post_meta($event_id, '_buddypress_activity_id', $bp_created_activity_id);
			bp_activity_add_meta($bp_created_activity_id, '_eventon_event_id', $event_id, true);
			
			//saving thumbnail id
			if($event_thumbnail_id) {				
				bp_activity_add_meta($bp_created_activity_id, '_eventon_event_thumbnail_id', $event_thumbnail_id, true);
			}	
		}
	}
	
	
	/*
		Helper function to truncate the excerpt	of events
	*/	
	function bp_truncate_excerpt_length($length){
		global $activities_template;	
		
		$activity = $activities_template->activity;
		
		$supported_actions = array('activity_edit_event', 'activity_new_event');
		
		if(in_array($activity->type, $supported_actions) && $activity->component == 'activity'){
			$length = 260;
		}
		
		return $length;
	}
		
	
	/*
		Helper function to get activity id from event id	
	*/
	function get_activity_id_from_event_id($event_id){
		$activity_id = get_post_meta($event_id, '_buddypress_activity_id', true);
		return $activity_id;
	}
		
	
	/*
		Register two actions for activity component
		action01: activity_new_event, action02: activity_edit_event	
	*/
	function bp_register_new_activity_actions(){
		$bp = buddypress();	
		
		bp_activity_set_action($bp->activity->id, 'activity_new_event', __( 'Published a new event', 'buddypress_eventon' ), array($this, 'bp_activity_format_activity_action_activity_create'), __( 'Events', 'buddypress_eventon' ), array('activity'));
		
		bp_activity_set_action($bp->activity->id, 'activity_edit_event', __( 'Updated an event', 'buddypress_eventon' ), array($this, 'bp_activity_format_activity_action_activity_update'), __( 'Events', 'buddypress_eventon' ), array('activity'));
	}
	
	
	/*
		This function only works in backend	
	*/
	function bp_activity_format_activity_action_activity_create($action, $activity){
		//$action = sprintf( __( '%s published a new event', 'buddypress_eventon' ), bp_core_get_userlink( $activity->user_id ) );
		//var_dump($action);
		return $action;

	}
	
	
	/*
		This function works on backend	
	*/
	function bp_activity_format_activity_action_activity_update($action, $activity){
		$action = sprintf( __( '%s updated an event', 'buddypress_eventon' ), bp_core_get_userlink( $activity->user_id ) );
		return $action;
	}	
	

	//only modify front end
	function bp_get_activity_action($action){
		global $activities_template;	
		
		$activity = $activities_template->activity;
		
		$supported_actions = array('activity_edit_event', 'activity_new_event');
		if(in_array($activity->type, $supported_actions) && $activity->component == 'activity'){
			$event_id = bp_activity_get_meta($activity->id, '_eventon_event_id', true);
									
			if($event_id){
				global $eventon;
				
				//setting action
				$single_event = $this->get_single_event($event_id);	
				$this->single_event = $single_event;				
				$append_action = sprintf( __("%s published a new event %s on %s from %s to %s", 'buddypress_eventon'), bp_core_get_userlink( $activity->user_id ), $this->get_event_link($single_event), $single_event['post_date'], $single_event['start_time'], $single_event['end_time']);
				$action = $append_action;

				//if post content is null, we set a dummy value
				//otherwise it won't print at front end
				if ( empty( $activity->content ) ){
					$activities_template->activity->content = '<span style="display: none"></span>';
				}					
					
			}
		}		
		return $action;	
	}

//“User x” has published a new event “event name” on “day of event” from “time of start” to “time of finish” . There are “x” seats available (only if the option is activated) 
	
	function get_single_event($event_id){
		$post = get_post($event_id);
		$emv = get_post_custom($event_id);
		$event_start_unix = isset($emv['evcal_srow'])? $emv['evcal_srow'][0]:0;
		$event_end_unix = isset($emv['evcal_erow'])?$emv['evcal_erow'][0]:0;
		
		$event = array(
			'id' => $post->ID,
			'title' => $post->post_title,
			//'post_date' => date('jS F', $event_start_unix),
			'post_date' => date_i18n('j F', $event_start_unix),
			'start_time' => date_i18n('G:i', $event_start_unix),
			'end_time' => date_i18n('G:i', $event_end_unix),
			'emv' => $emv
					
		);
		
		return $event;
	}	
	
	
	/*
	helper function that generates event title with a # hefre	
	*/
	function get_event_link($event){
		//return '<a class="eventon_events_list desc_trig" event_id="'.$event[id].'" href="' . get_permalink($event['id']) . '" > ' . __($event['title']) . ' </a>';	
		$link = '<a class="bb_event-activity_header" id="activity_event_trigger_'.$event['id'].'" href="#" >' . __($event['title']) . ' </a>';	
		return $link;	
	}
	
		
	
	/*
	This function returns content of an event. 
	uses global $evention->generator->get_single_event_data method	
	*/
	function bp_get_activity_content_body($content){
		global $activities_template, $eventon;			
		$activity = $activities_template->activity;			
		$supported_actions = array('activity_edit_event', 'activity_new_event');
		
		if(in_array($activity->type, $supported_actions) && $activity->component == 'activity'){
			
			$event_id = bp_activity_get_meta($activity->id, '_eventon_event_id', true);
			
			if( $event_id ){
				$event_data =  $eventon->evo_generator->get_single_event_data($event_id);
				
				/*
				if(has_post_thumbnail($event_id)) {
					$event_thumbnail = get_the_post_thumbnail( $event_id, array(100, 100) );
					$content = $event_thumbnail . $content;
				}
				*/	
				
				if( $event_data ){			
					$content .= "<div style='display: none;' id='activity_event_activity_event_trigger_{$event_id}' class='eventon_events_list desc_trig'>" . $event_data[0]['content'] . "</div>";
				}
			}					
		}
		


		return $content;
	}
	
	
	/*
	helper function to include js file
	*/
	function include_event_js(){
		$url = plugins_url('/assets/popup-helper.js', __FILE__);
		wp_register_script ('eventon_buddypress_binder', $url, array('jquery'), false, true );		
		wp_enqueue_script ('eventon_buddypress_binder');		
		
		//loading css script
		$css_url = plugins_url('/assets/eventon-buddypress-style.css', __FILE__);
		wp_register_style('eventon_buddypress_style', $css_url);
		wp_enqueue_style('eventon_buddypress_style');
	}


	/*
	Loads language files
	*/
	function load_textdomain(){
		load_plugin_textdomain('buddypress_eventon', false, dirname( plugin_basename(__FILE__) ) . '/languages/');
	}

}

$bb_activity_with_customposttypes = new Budypress_Activity_Bind_With_CustomPostTypes();
  
?>