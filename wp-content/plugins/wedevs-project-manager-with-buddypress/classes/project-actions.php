<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/*
This class contains whole project actions that needs to be hooked with buddypress activity
Some helper fucntion will smoth the workflow
*/
class CPM_Project_Actions{
	/*
	@project_actions contains every actions related to a project e.g publishing doc, creating task, assigning uses in task, etc
	project_actions method will allow a hook wedev_bb_proejct_actions hook 
	*/
	private $actions = array();

	/*
	return all registerd project actions	
	*/
	function get_all_actions(){
		if( empty( $this->actions ) ){

			$this->actions = array(				

				'created_a_new_tasklist' => array(
					'action' => __( '%s has created a new tasklist %s in project %s', 'cpm_buddypress' ),
					'action_own' => __( '%s has created a new tasklist %s in project %s that you are managing', 'cpm_buddypress' ),
					'pro_action' => __( 'Created a new tasklist', 'cpm_buddypress' ),
					'sprintf' => array('user', 'tasklist_title', 'project_title'),
					'callback' => 'project_action_created_a_new_tasklist',
					'position' => 4,
				),

				'edited_a_tasklist' => array(
					'action' => __( '%s has modified a tasklist %s in project %s', 'cpm_buddypress' ),
					'action_own' => __( '%s has modified a tasklist %s in project %s that you are managing', 'cpm_buddypress' ),
					'pro_action' => __( 'Modified a tasklist', 'cpm_buddypress' ),
					'sprintf' => array('user', 'tasklist_title', 'project_title'),
					'callback' => 'project_action_created_a_new_tasklist',
					'position' => 5,
				),

			/*	
				'assigned_you_a_new_task' => array(
					'action' => '%s has assigned you a new task %s in %s which is due the %s in project %s',
					'pro_action' => 'Assigned a new task',
					'sprintf' => array('user', 'task_title', 'list_of_task', 'task_date', 'project_title'),
					'callback' => 'project_action_assigned_you_a_new_task'
				),
			*/

				'created_a_new_task' => array(
					'action' => __( '%s has created a new task %s in project %s', 'cpm_buddypress'),
					'action_own' => __( '%s has created a new task %s in project %s that you are managing', 'cpm_buddypress'),
					'action_assigned' => __( '%s has assigned you a new task %s in %s which is due the %s in project %s', 'cpm_buddypress'),
					'pro_action' => __( 'Created a new task', 'cpm_buddypress' ),
					'sprintf' => array('user', 'task_title', 'project_title'),
					'callback' => 'project_action_created_a_new_task',
					'position' => 6,
				),

				'edited_a_new_task' => array(
					'action_assigned' => __( '%s has edited a task %s on which you are assigned which is due the %s in project %s', 'cpm_buddypress'),
					'action' => __( '%s has edited a task %s in project %s', 'cpm_buddypress' ),
					'action_own' => __( '%s has edited a task %s in project %s that you are managing', 'cpm_buddypress' ),
					'pro_action' => __( 'Edited a new task', 'cpm_buddypress'),
					'sprintf' => array('user', 'task_title', 'task_date', 'project_title'),
					'callback' => 'project_action_edited_a_new_task',
					'position' => 7,
				),

				'closed_a_new_task' => array(
					'action_assigned' => __( '%s has closed a task %s on which you are assigned which is due the %s in project %s', 'cpm_buddypress' ),
					'action' => __( '%s has closed a task %s in project %s', 'cpm_buddypress' ),
					'action_own' => __( '%s has closed a task %s in project %s that you are managing', 'cpm_buddypress' ),
					'pro_action' => __( 'Closed a new task', 'cpm_buddypress' ),
					'sprintf' => array('user', 'task_title', 'task_date', 'project_title'),
					'callback' => 'project_action_closed_a_new_task', 
					'position' => 8,
				),

			/*	
				'made_a_comment_on_a_task' => array(
					'action' => '%s made a comment on %s on which you are assigned which is due the %s in project %s',
					'pro_action' => 'Made a comment on a task',
					'sprintf' => array('user', 'task_title', 'task_date', 'project_title'),
					'callback' => 'project_action_made_a_comment_on_a_task',
					'conditional' => 'post is sent in activity only if user has check to notify user in CPM'
				),
			*/
				
				'made_a_comment_on_a_discussion' => array(
					'action' => __( '%s made a comment on discussion %s in project %s', 'cpm_buddypress' ),
					'action_own' => __( '%s made a comment on discussion %s which you have started in project %s', 'cpm_buddypress' ),
					'pro_action' => __( 'Made a comment on a discussion', 'cpm_buddypress' ),
					'sprintf' => array('user', 'discussion_title', 'project_title'),
					'callback' => 'project_action_made_a_comment_on_a_discussion',
					'conditional' => 'post is sent in activity only if user has check to notify user in CPM',
					'position' => 3,
				),

				'made_a_comment_on_a_discussion_with_attachment' => array(
					'action' => __( '%s published a new document %s attached to a comment on discussion %s in project %s', 'cpm_buddypress' ),
					'action_own' => __( '%s published a new document %s attached to a comment on discussion %s which you have started in project %s', 'cpm_buddypress' ),
					'action_plural' => __( '%s published few new documents %s attached to a comment on discussion %s in project %s', 'cpm_buddypress' ),
					'action_own_plural' => __( '%s published few new documents %s attached to a comment on discussion %s which you have started in project %s', 'cpm_buddypress' ),
					'pro_action' => __( 'Published a new document in comment', 'cpm_buddypress' ),
					'sprintf' => array('user', 'document_title', 'discussion_title', 'project_title'),
					'callback' => 'project_action_made_a_comment_on_a_discussion',
					'conditional' => 'post is sent in activity only if user has check to notify user in CPM',
					'position' => 3,
				),

				'created_a_new_discussion' => array(
					'action' => __( '%s created a new discussion %s in project %s', 'cpm_buddypress' ),
					'pro_action' => __( 'Created a new discussion', 'cpm_buddypress' ), 
					'sprintf' => array('user', 'discussion_title', 'project_title'),
					'callback' => 'project_action_created_a_new_discussion',
					'conditional' => 'post is sent in activity only if user has check to notify user in CPM',
					'position' => 1,
				),

				'created_a_new_discussion_with_attachment' => array(
					'action' => __( '%s has published a new document %s attached to discussion %s in project %s', 'cpm_buddypress' ),
					'action_plural' => __( '%s has published few new documents %s attached to discussion %s in project %s', 'cpm_buddypress' ),
					'pro_action' => __( 'Published a new document in discussion', 'cpm_buddypress' ),
					'sprintf' => array('user', 'document_title', 'discussion_title', 'project_title'),
					'callback' => 'project_action_published_a_new_doc',
					'position' => 2,
				),

			);
		}

		return apply_filters('wedev_bb_proejct_actions', $this->actions);	
	}



	/*
	return all registered action keys
	*/
	function get_action_types(){
		return array_keys( $this->get_all_actions() );
	}


	/*
	return a specific action based on @action type
	return null if undefined @action type provied
	*/
	function get_action_by_action_type( $action_type=null ){
		$actions = $this->get_all_actions();
		if(!empty($action_type) && isset($actions[$action_type])){
			return $actions[$action_type];
		}

		return null;
	}


	/*
	set of helper functions to modify actions values & labes
	*/
	function project_action_published_a_new_doc($action, $activity){
		return $action;
	}

	function project_action_created_a_new_tasklist($action, $activity){
		return $action;
	}

	function project_action_assigned_you_a_new_task($action, $activity){
		return $action;
	}

	function project_action_created_a_new_task($action, $activity){
		return $action;
	}

	function project_action_edited_a_new_task($action, $activity){
		return $action;
	}

	function project_action_closed_a_new_task($action, $activity){
		return $action;
	}

	function project_action_made_a_comment_on_a_task($action, $activity){
		return $action;
	}

	function project_action_made_a_comment_on_a_discussion($action, $activity){
		return $action;
	}

	function project_action_created_a_new_discussion($action, $activity){
		return $action;
	}	

}




?>