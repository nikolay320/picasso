<?php
/*
Plugin Name: BuddyPress Activity Privacy Extended
Plugin URI: http://192..../picasso/
Description: Add the ability for members to extend privacy to their comments in private activities  and media files. Make sure BuddyPress Activity Privacy is installed 
Version: 2.0.1
Requires at least:  WP 3.4, BuddyPress 1.5
Tested up to: BuddyPress 1.5, 2.2.1
Dependency: BP_Activity_Privacy
Author: Mahibul Hasan
Author URI: https://www.upwork.com/freelancers/~01ecf4a954c6b739b9
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class BP_Activity_Privacy_extended{
	
	//@activitiy_types type of activities that extended privacy will effective
	private $activitiy_types; 
	
	//contains hooks
	function __construct(){
		add_filter('bp_more_visibility_activity_filter', array($this, 'bp_more_visibility_activity_filter'), 20, 3);
		$this->activitiy_types = array('activity_comment');
	}
	
	
	function bp_more_visibility_activity_filter($remove_from_stream, $visibility, $activity){		
		
		if($remove_from_stream) return $remove_from_stream; // if $remove from the stream if already applied for it		
			
		if(in_array($activity->type, $this->activitiy_types)){
			
			$usernames = bp_activity_find_mentions( $activity->content );
            $is_mentioned = array_key_exists( bp_loggedin_user_id(),  (array)$usernames );

            if( !$is_mentioned ) $remove_from_stream = true;
          
		}
		
		return $remove_from_stream;			
	}

}


$bp_activity_privacy_extended = new BP_Activity_Privacy_extended();


?>
