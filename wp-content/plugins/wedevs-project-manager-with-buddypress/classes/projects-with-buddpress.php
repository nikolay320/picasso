<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/*
Binding prescribed actions with buddypress activity
*/

class Wedev_Projects_With_Buddypress{

	var $project_actions = null;
	var $actions = null;
	var $language_loaded = null;	

	//contains hooks
	function __construct(){

		//register new actions under bp activity component
		add_action( 'bp_activity_register_activity_actions', array($this, 'register_project_actions_with_bb_activity'), 100 );
		
		//add/edit discussion/message on a project
		add_action( 'cpm_message_new', array($this, 'create_bb_activity_with_new_message'), 100, 3 );

		//add/edit a comment on a message on a project
		add_action( 'cpm_comment_new', array($this, 'create_bb_activity_with_new_comment'), 100, 3 );

		//add new tasklist in a project
		add_action( 'cpm_tasklist_new', array( $this, 'create_bb_activity_with_new_tasklist' ), 100, 3 );

		//add new tasklist in a project
		add_action( 'cpm_tasklist_update', array( $this, 'create_bb_activity_with_update_tasklist' ), 100, 3 );

		//add new task in a tasklist in a project
		add_action( 'cpm_task_new', array( $this, 'create_bb_activity_with_new_task' ), 100, 3 );

		//update a task in a tasklist in a project
		add_action( 'cpm_task_update', array( $this, 'create_bb_activity_with_update_task' ), 100, 3 );

		//delete a task from tasklist (in a project)
		add_action( 'cpm_task_complete', array( $this, 'create_bb_activity_with_complete_task' ), 100, 1 );

		//delete a task from tasklist (in a project)
		//add_action( 'cpm_after_upload_file', array( $this, 'create_bb_activity_with_file_upload' ), 100, 3 );

		//load language file
		//add_action('init', array($this, 'load_textdomain'));
		add_action('load_textdomain', array($this, 'load_textdomain'), 100, 2);

		//make sure classes/project-actions.php is included in main.php
		//$this->project_actions = new CPM_Project_Actions();

		//privacy filter if applicable
		add_filter('bp_has_activities', array($this, 'activity_visibility_filter'), 1000, 2);
		
		//setup content size in activity stream
		add_filter('bp_activity_excerpt_length', array($this, 'bp_truncate_excerpt_length'), 100);
	
		//buddypress hook to filter action before action meta insertion
		add_filter('bp_get_activity_action_pre_meta', array($this, 'modify_bp_activity_action_based_on_user'), 100, 2);
	
		
	}


	private function project_actions(){
		if(!is_object($this->actions)){
			$this->actions = new CPM_Project_Actions();
		}
		return $this->actions;
	}

	//Register project actions with buddypress activity 
	function register_project_actions_with_bb_activity(){
		$bp = buddypress();
		$position = 1000;	
		foreach ($this->project_actions()->get_all_actions() as $key => $value) {
			$position += $value['position']; //setup positions
			bp_activity_set_action($bp->activity->id, $key, $value['pro_action'], array( $this->project_actions(), $value['callback'] ), $value['pro_action'], array('activity'), $position);
		}
	}


	/*
	create an activity while starting a new discussion/message for a cpm projects
	@message_id: a post id of custom post type "cpm_message"
	@project_id: a post id of cpm_project
	@postarr: an arry containing post_title & post_content of cpm_message
	*/
	function create_bb_activity_with_new_message( $message_id, $project_id, $postarr ){
		//selection of action based on attachment
		$message_url = $this->generate_projects_activity_url( 'single_message', array('message_id' => $message_id, 'project_id' => $project_id, 'title' => $postarr['post_title']) );
		$project_url = $this->generate_projects_activity_url( 'project_details', array('project_id' => $project_id) );
				
		if( isset($_POST['cpm_attachment']) && count($_POST['cpm_attachment']) > 0 ){
			$bb_action_type = 'created_a_new_discussion_with_attachment';
			$bb_action = $this->project_actions()->get_action_by_action_type( $bb_action_type );
			$files = $this->create_attachment_to_string( $_POST['cpm_attachment'], $project_id );
			
			//conditionals to identify single file or multiple files
			if(count($_POST['cpm_attachment']) > 1){
				$action = sprintf($bb_action['action_plural'], bp_core_get_userlink(bp_loggedin_user_id()), $files, $message_url, $project_url );
			}
			else{
				$action = sprintf($bb_action['action'], bp_core_get_userlink(bp_loggedin_user_id()), $files, $message_url, $project_url );
			}
		}
		else{
			$bb_action_type = 'created_a_new_discussion';
			$bb_action = $this->project_actions()->get_action_by_action_type( $bb_action_type );
			$action = sprintf( $bb_action['action'], bp_core_get_userlink(bp_loggedin_user_id()), $message_url, $project_url );					
		}
	
		$bb_args = array(
			'action' => $action,
			'type' => $bb_action_type,
			'component' => 'activity',
			'content' => $postarr['post_content']
		);


		//$bp_created_activity_id = bp_activity_add( $bb_args );
		$bb_meta_args['meta_key'] = '_' . $bb_action_type;
		$bb_meta_args['meta_value'] = array(
			'message_id' => $message_id,
			'project_id' => $project_id,
			'project_manager' => get_post_field( 'post_author', $project_id ),
			'notified_users' => $_POST['notify_user'],
			'attachments' => (isset($_POST['cpm_attachment'])) ? $_POST['cpm_attachment'] : '',
			'action_info' => array(
				'activity_author' => bp_core_get_userlink(bp_loggedin_user_id()),
				'project_url' => $project_url,
				'message_url' => $message_url,
				'files' => (isset($files)) ? $files : '',
			),
		);



		//keep tracking on both bb & projects database
		return $this->log_a_bb_activity( $bb_args, $bb_meta_args, array( 'post_id'=>$message_id ) );
				
	}


	/*
	Creates an activity based on comment. Only if a comment has notifiyable users
	then comment will send to activity
	@commentid: created comment id
	@project id: project id of the discussion for which this comment has been crated
	@commentdata: array of information like discussion/message id, comment content	
	*/
	function create_bb_activity_with_new_comment( $comment_id, $project_id, $commentdata ){
		
		$message = get_post( $commentdata['comment_post_ID'] );

		$message_url = $this->generate_projects_activity_url( 'single_message', array('message_id' => $message->ID, 'project_id' => $project_id, 'title' => $message->post_title) );
		$project_url = $this->generate_projects_activity_url( 'project_details', array('project_id' => $project_id) );

		if( isset($_POST['cpm_attachment']) && count($_POST['cpm_attachment']) > 0 ){
			$bb_action_type = 'made_a_comment_on_a_discussion_with_attachment';
			$bb_action = $this->project_actions()->get_action_by_action_type( $bb_action_type );
			$files = $this->create_attachment_to_string( $_POST['cpm_attachment'], $project_id );

			//conditionals to identify single file or multiple files
			if(count($_POST['cpm_attachment']) > 1){
				$action = sprintf($bb_action['action_plural'], bp_core_get_userlink(bp_loggedin_user_id()), $files, $message_url, $project_url );
			}
			else{
				$action = sprintf($bb_action['action'], bp_core_get_userlink(bp_loggedin_user_id()), $files, $message_url, $project_url );
			}
		}
		else{

			$bb_action_type = 'made_a_comment_on_a_discussion';
			$bb_action = $this->project_actions()->get_action_by_action_type( $bb_action_type );
			$action = sprintf($bb_action['action'], bp_core_get_userlink(bp_loggedin_user_id()), $message_url, $project_url );
		}

		$bb_args = array(
			'action' => $action,
			'type' => $bb_action_type,
			'component' => 'activity',
			'content' => $commentdata['comment_content']
		);	

		$bb_meta_args['meta_key'] = '_' . $bb_action_type;
		$bb_meta_args['meta_value'] = array(
			'comment_id' => $comment_id,
			'post_id' => $commentdata['comment_post_ID'],
			'project_id' => $project_id,
			'project_manager' => get_post_field( 'post_author', $project_id ),
			'notified_users' => $_POST['notify_user'],
			'attachments' => (isset($_POST['cpm_attachment'])) ? $_POST['cpm_attachment'] : '',		
			'action_info' => array(
				'activity_author' => bp_core_get_userlink(bp_loggedin_user_id()),
				'project_url' => $project_url,
				'message_url' => $message_url,
				'files' => (isset($files)) ? $files : '',
			),
		);		
		
		//keep tracking on both bb & projects database
		return $this->log_a_bb_activity( $bb_args, $bb_meta_args, array( 'post_id'=>$comment_id ) );	
	}


	/*
	creates an activity basded on task list added
	to do list are child of task list
	project --> tasklist
	*/
	function create_bb_activity_with_new_tasklist( $list_id, $project_id, $data ){
		
		$list_url = $this->generate_projects_activity_url( 'tasklist_url', array('list_id'=>$list_id, 'project_id'=>$project_id, 'title'=>$data['post_title']));
		$project_url = $this->generate_projects_activity_url( 'project_details', array('project_id'=>$project_id) );
		
		$bb_action_type = 'created_a_new_tasklist';
		$bb_action = $this->project_actions()->get_action_by_action_type($bb_action_type);
		
		$bb_args = array(
			'action' => sprintf( $bb_action['action'], bp_core_get_userlink(bp_loggedin_user_id()), $list_url, $project_url ),
			'type' => $bb_action_type,
			'component' => 'activity',
			'content' => $data['post_content']
		);		
		
		$project = get_post( $project_id ); //get post author
		
		$bb_meta_args['meta_key'] = '_' . $bb_action_type;
		$bb_meta_args['meta_value'] = array(
			'tasklist_id' => $list_id,
			'project_id' => $project_id,
			'project_manager' => $project->post_author,
			'action_info' => array(
				'activity_author' => bp_core_get_userlink(bp_loggedin_user_id()),
				'project_url' => $project_url,
				'list_url' => $list_url,
			),
		);

		//keep tracking on both bb & projects database
		return $this->log_a_bb_activity( $bb_args, $bb_meta_args, array( 'post_id'=>$list_id ) );	
	}


	/*
	creates an activity basded on task list added
	to do list are child of task list
	project --> tasklist
	*/
	function create_bb_activity_with_update_tasklist( $list_id, $project_id, $data ){
		
		$list_url = $this->generate_projects_activity_url( 'tasklist_url', array('list_id'=>$list_id, 'project_id'=>$project_id, 'title'=>$data['post_title']));
		$project_url = $this->generate_projects_activity_url( 'project_details', array('project_id'=>$project_id) );

		$bb_action_type = 'edited_a_tasklist';
		$bb_action = $this->project_actions()->get_action_by_action_type($bb_action_type);
		
		$bb_args = array(
			'action' => sprintf( $bb_action['action'], bp_core_get_userlink(bp_loggedin_user_id()), $list_url, $project_url ),
			'type' => $bb_action_type,
			'component' => 'activity',
			'content' => $data['post_content']
		);	
		
		$project = get_post( $project_id ); //get post author
		$bb_meta_args['meta_key'] = '_' . $bb_action_type;
		$bb_meta_args['meta_value'] = array(
			'tasklist_id' => $list_id,
			'project_id' => $project_id,
			'project_manager' => $project->post_author,
			'action_info' => array(
				'activity_author' => bp_core_get_userlink(bp_loggedin_user_id()),
				'project_url' => $project_url,
				'list_url' => $list_url,
			),
		);

		return $this->log_a_bb_activity( $bb_args, $bb_meta_args, array( 'post_id'=>$list_id ) );			
	}



	/*
	creates an activity basded on task added to a tasklist of a project
	assigns task based on assigned users
	$action: created_a_new_task & 'assigned_you_a_new_task'
	project -- > tasklist -- > task
	*/
	function create_bb_activity_with_new_task( $list_id, $task_id, $data ){
		
		$project = $this->get_project_by('tasklist', $list_id);

		$task_url = $this->generate_projects_activity_url( 'task_url', array('task_id'=>$task_id, 'list_id'=>$list_id, 'project_id'=>$project->ID, 'title'=>$data['post_title']) );
		$project_url = $this->generate_projects_activity_url ( 'project_details', array('project_id'=>$project->ID) );
		
		$list_title = get_post_field('post_title', $list_id);
		$list_url = $this->generate_projects_activity_url( 'tasklist_url', array('list_id'=>$list_id, 'project_id'=>$project_id, 'title'=>$list_title));
		
		$bb_action_type = 'created_a_new_task';
		$bb_action = $this->project_actions()->get_action_by_action_type( $bb_action_type );
				
		$bb_args = array(
			'action' => sprintf( $bb_action['action'], bp_core_get_userlink(bp_loggedin_user_id()), $task_url, $project_url ),
			'type' => $bb_action_type,
			'component' => 'activity',
			'content' => $data['post_content']
		);		
		
		$bb_meta_args['meta_key'] = '_' . $bb_action_type;	
		$bb_meta_args['meta_value'] = array(
			'tasklist_id' => $list_id,
			'project_id' => $project->ID,
			'project_manager' => $project->post_author,
			'task_id' => $task_id,
			'assigned_users' => isset( $_POST['task_assign'] ) ? $_POST['task_assign'] : array( '-1' ),
			'task_due' => trim( $_POST['task_due'] ),
			'action_info' => array(
				'activity_author' => bp_core_get_userlink(bp_loggedin_user_id()),
				'project_url' => $project_url,
				'task_url' => $task_url,
				'list_url' => $list_url,
			),

		);		

		return $this->log_a_bb_activity( $bb_args, $bb_meta_args, array( 'post_id'=>$task_id ) );			
	}


	/*
	creates an activity basded on task updated to a tasklist of a project
	assigns task based on assigned users
	$action: edited_a_new_task & 'edited_a_new_task'
	project -- > tasklist -- > task
	*/
	function create_bb_activity_with_update_task( $list_id, $task_id, $data ){
		
		$project = $this->get_project_by('tasklist', $list_id);

		$task_url = $this->generate_projects_activity_url( 'task_url', array('task_id'=>$task_id, 'list_id'=>$list_id, 'project_id'=>$project->ID, 'title'=>$data['post_title']) );
		$project_url = $this->generate_projects_activity_url ( 'project_details', array('project_id'=>$project->ID) );

		$list_title = get_post_field('post_title', $list_id);
		$list_url = $this->generate_projects_activity_url( 'tasklist_url', array('list_id'=>$list_id, 'project_id'=>$project_id, 'title'=>$list_title));

		$bb_action_type = 'edited_a_new_task';
		$bb_action = $this->project_actions()->get_action_by_action_type( $bb_action_type );
				
		$bb_args = array(
			'action' => sprintf( $bb_action['action'], bp_core_get_userlink( bp_loggedin_user_id() ), $task_url, $project_url ),
			'type' => $bb_action_type,
			'component' => 'activity',
			'content' => $data['post_content']
		);		
		
		$bb_meta_args['meta_key'] = '_' . $bb_action_type;
		$bb_meta_args['meta_value'] = array(
			'tasklist_id' => $list_id,
			'project_id' => $project->ID,
			'project_manager' => $project->post_author,
			'task_id' => $task_id,
			'assigned_users' => isset( $_POST['task_assign'] ) ? $_POST['task_assign'] : array( '-1' ),
			'task_due' => trim( $_POST['task_due'] ),
			'action_info' => array(
				'activity_author' => bp_core_get_userlink(bp_loggedin_user_id()),
				'project_url' => $project_url,
				'task_url' => $task_url,
				'list_url' => $list_url,
			),

		);

		return $this->log_a_bb_activity( $bb_args, $bb_meta_args, array( 'post_id'=>$task_id ) );			

	}


	/*
	creates an activity basded on a complete task in a project
	re assigns task based on assigned users
	$action: edited_a_new_task & 'edited_a_new_task'
	project -- > tasklist -- > task
	*/
	function create_bb_activity_with_complete_task( $task_id ){
		global $cpm;

		$project = $this->get_project_by('task', $task_id);
		
		//using cpm object to get a task
		$task = $cpm->task->get_task($task_id);

		$task_url = $this->generate_projects_activity_url( 'task_url', array('task_id'=>$task->ID, 'list_id'=>$task->post_parent, 'project_id'=>$project->ID, 'title'=>$task->post_title ) );
		$project_url = $this->generate_projects_activity_url ( 'project_details', array('project_id'=>$project->ID) );

		$bb_action_type = 'closed_a_new_task';
		$bb_action = $this->project_actions()->get_action_by_action_type( $bb_action_type );
				
		$bb_args = array(
			'action' => sprintf( $bb_action['action'], bp_core_get_userlink( bp_loggedin_user_id() ), $task_url, $project_url ),
			'type' => $bb_action_type,
			'component' => 'activity',
			'content' => $data['post_content']
		);
		
		//var_dump($bb_args);

		$bb_meta_args['meta_key'] = '_' . $bb_action_type;
		$bb_meta_args['meta_value'] = array(
			'tasklist_id' => $list_id,
			'project_id' => $project->ID,
			'project_manager' => $project->post_author,
			'task_id' => $task_id,
			'assigned_users' => $task->assigned_to,
			'task_due' => $task->due_date,
			'task_completed_on' => $task->completed_on,
			'action_info' => array(
				'activity_author' => bp_core_get_userlink(bp_loggedin_user_id()),
				'project_url' => $project_url,
				'task_url' => $task_url,
			),

		);

		return $this->log_a_bb_activity( $bb_args, $bb_meta_args, array( 'post_id'=>$task_id ) );
	}
	

	/*
	helper function to log an activity
	it also add activity meta
	*/
	private function log_a_bb_activity( $post_args, $meta_args, $project_meta ){
		$default_args = array(
			'action' => '',
			'type' => '',
			'component' => 'activity',
			'content' => ''
		);

		$args = wp_parse_args( $post_args, $default_args );
		$activity_id = bp_activity_add( $args );

		if( $activity_id ){
			bp_activity_add_meta( $activity_id, $meta_args['meta_key'], maybe_serialize($meta_args['meta_value']), true ); // true is making the row unique
			update_post_meta( $project_meta['post_id'], '_bb_activity_id', $activity_id );
		}
		
	}
		

	
	/*
	projects friendly url
	*/
	function generate_projects_activity_url( $activity_type, $args ){
		global $cpm;
		$url = '';
		switch($activity_type){

			case 'single_message':
				$url = do_shortcode( $cpm->activity->message_url( $args['message_id'], $args['project_id'], $args['title']) );
				break;
			
			case 'project_details':
				$project = get_post( $args['project_id'] );
				$href = cpm_url_project_details( $args['project_id'] );
				$url = "<a title='{$project->post_title}' href='{$href}'>" . $project->post_title . "</a>";
				break;
			
			case 'tasklist_url':
				$url = do_shortcode( $cpm->activity->list_url( $args['list_id'], $args['project_id'], $args['title'] ) );
				break;

			case 'task_url':
				$url = do_shortcode( $cpm->activity->task_url( $args['task_id'], $args['list_id'], $args['project_id'], $args['title'] ) );
				break;

		}		

		return $url;
	}


	/*
	helper function to get project with different child items/posts
	@item_type: search item, like task, tasklist, discussion etc
	*/
	function get_project_by($item_type, $type_id){
		if( empty($item_type) || empty($type_id) ) return false; //if item

		$project = null; //defautl project is null

		switch ($item_type) {
			case 'tasklist':
				$tasklist = get_post($type_id); //get tasklist
				$project = get_post( $tasklist->post_parent ); //get project
				break;

			case 'task':
				$task = get_post($type_id); // get task
				$tasklist = get_post($task->post_parent); //get tasklist
				$project = get_post($tasklist->post_parent); //get project
				break;			
		}

		return $project;
	}


	/*
	filter activities based on settings of privacy at different 
	labels of projects
	e.g. If someone may not want to share a discussion for a certain project
	*/
	function activity_visibility_filter( $has_activities, $activities ) {
		global $bp, $activities_template;

		$is_super_admin = is_super_admin();
		$bp_loggedin_user_id = bp_loggedin_user_id();

		//all registered action types
		$activity_action_types = $this->project_actions()->get_action_types();

		//we only process activites registerd by this plugin
		foreach ( $activities->activities as $key => $activity ) {
			if( in_array( $activity->type, $activity_action_types ) && $activity->component == 'activity' ){ //only applicable for registered action types
				
				$remove_from_strem = $this->remove_activity_from_stream_based_on_privacy( $activity, $bp_loggedin_user_id, $is_super_admin );
				
				if( $remove_from_strem && isset( $activities_template->activity_count ) ){
					$activities->activity_count --; //reducing activity count
					unset( $activities->activities[$key] ); 
				}
			}
		}

		$activities_new = array_values( $activities->activities );
   		// $activities_template->activities = $activities_new;
    	$activities->activities = $activities_new;
		return $has_activities;
	}



	/*
	Decission  maker to remove an activity from stream
	helper function of activity_visibility_filter method
	don't modify this directly, as it contains all front-end privacy paramters
	*/
	function remove_activity_from_stream_based_on_privacy( $activity, $bp_loggedin_user_id, $is_super_admin ){
		//if user is super admin or activity creator then skip the whole functions
		if( ($bp_loggedin_user_id == $activity->user_id) || ($is_super_admin) ) return false;
		
		//meta values to remove activities based on privacy
		$meta_values = bp_activity_get_meta( $activity->id, '_' . $activity->type, true );
		$meta_values = unserialize($meta_values);

		$remove_from_strem = false; //by default every activity should be shown
		
		switch ( $activity->type ) {

			case 'created_a_new_discussion':
			case 'made_a_comment_on_a_discussion':
			case 'created_a_new_discussion_with_attachment':
			case 'made_a_comment_on_a_discussion_with_attachment':
			case 'created_a_new_tasklist':
			case 'edited_a_tasklist':

				$meta_values['notified_users'][] = $meta_values['project_manager'];	//project manager included			
				if(!in_array($bp_loggedin_user_id, $meta_values['notified_users']) ) {
					$remove_from_strem = true;
				}
				break;

			
			case 'created_a_new_task':
			case 'edited_a_new_task':	
			case 'closed_a_new_task':		

				$meta_values['assigned_users'][] = $meta_values['project_manager'];
				if(!in_array($bp_loggedin_user_id, $meta_values['assigned_users']) ) {
					$remove_from_strem = true;
				}
				break;
				
		}

		return $remove_from_strem;

	}


	/*
	makes attachments a good formatted string
	*/
	function create_attachment_to_string( $attachments, $project_id ){
		$base_image_url  = admin_url( 'admin-ajax.php?action=cpm_file_get' );
		$files = array();
		
		foreach ($attachments as $attachment_id) {
			$file = CPM_Comment::getInstance()->get_file( $attachment_id );
			$file_url = sprintf( '%s&file_id=%d&project_id=%d', $base_image_url, $file['id'], $project_id );
			$files[] = "<a href='{$file_url}' title='{$file[name]}'>$file[name]</a>";
		}

		return implode(', ', $files);
	}


	/*
	helper function to truncate buddypress excerpt only for registered
		activities by this plugin
	Truncate buddypress activity excerpt length
	@length: default or other plugin specified length
	*/
	function bp_truncate_excerpt_length( $length ){
		global $activities_template;	
		
		$activity = $activities_template->activity;
		
		$activity_action_types = $this->project_actions()->get_action_types();
		
		if( in_array( $activity->type, $activity_action_types ) && $activity->component == 'activity' ){
			$length = 150;
		}
		
		return $length;
	}


	/*
	core function that will chane action based on logged in user
	don't modify this plugin, as it contains core functionality to show sanitized action based on privacy
	*/
	function modify_bp_activity_action_based_on_user( $action, $activity ){		


		//all registered action types
		$activity_action_types = $this->project_actions()->get_action_types();
		if( in_array($activity->type, $activity_action_types) ):
			
			//getting whole action based on activity type
			$bb_action = $this->project_actions()->get_action_by_action_type( $activity->type );

			//get super admin and logged in user id
			$is_super_admin = is_super_admin();
			$bp_loggedin_user_id = bp_loggedin_user_id();

			//fetching meta values to define proper activity action
			$meta_values = bp_activity_get_meta( $activity->id, '_' . $activity->type, true );
			$meta_values = unserialize($meta_values);

			switch( $activity->type ){

				case 'created_a_new_discussion':
				case 'created_a_new_discussion_with_attachment':
					break; // we don't change anything

				case 'made_a_comment_on_a_discussion':
					$message_author_id = get_post_field('post_author', $meta_values['post_id']);
					if( @extract($meta_values['action_info']) ){
						if($bp_loggedin_user_id == (int) $message_author_id){
							$action = sprintf( $bb_action['action_own'], $activity_author, $message_url, $project_url);
						}
					}					
					break;

				case 'made_a_comment_on_a_discussion_with_attachment':
					 $message_author_id = get_post_field('post_author', $meta_values['post_id']);
					 
					 if( @extract($meta_values['action_info']) ){

						if($bp_loggedin_user_id == (int)$message_author_id){

							if(is_array($meta_values['attachments']) && count($meta_values['attachments']) > 1){
								$action = sprintf( $bb_action['action_own_plural'], $activity_author, $files, $message_url, $project_url );
							}
							else{
								$action = sprintf( $bb_action['action_own'], $activity_author, $files, $message_url, $project_url );
							}							
						}
					}					
					break;

				case 'created_a_new_tasklist':
				case 'edited_a_tasklist':
					if( @extract($meta_values['action_info']) ){
						if($bp_loggedin_user_id == (int)$meta_values['project_manager']){
							$action = sprintf($bb_action['action_own'], $activity_author, $list_url, $project_url);
						}
					}
					break;

				case 'created_a_new_task':
					if( @extract($meta_values['action_info']) ){
						if($bp_loggedin_user_id == (int)$meta_values['project_manager']){
							$action = sprintf($bb_action['action_own'], $activity_author, $task_url, $project_url);
						}
						elseif (in_array($bp_loggedin_user_id, $meta_values['assigned_users'])) {
							$task_due = date_i18n('j F', strtotime($meta_values['task_due']));
							$action = sprintf($bb_action['action_assigned'], $activity_author, $task_url, $list_url, $task_due, $project_url);
						}
					}
					break;

				case 'edited_a_new_task': 
				case 'closed_a_new_task':
					if( @extract($meta_values['action_info']) ){
						if($bp_loggedin_user_id == (int)$meta_values['project_manager']){
							$action = sprintf($bb_action['action_own'], $activity_author, $task_url, $project_url);
						}
						elseif (in_array($bp_loggedin_user_id, $meta_values['assigned_users'])) {
							$task_due = date_i18n('j F', strtotime($meta_values['task_due']));
							$action = sprintf($bb_action['action_assigned'], $activity_author, $task_url, $task_due, $project_url);
						}
					}
					break;
			}

		endif;

		return $action;
	}


	//load language files
	function load_textdomain($domain, $mofile){		

		if(!$this->language_loaded){
			$this->language_loaded = true;		
			load_plugin_textdomain('cpm_buddypress', false, dirname( CPMBUDDYPRESS_BASE ) . '/languages/');			
		}		
		
	}

}




?>