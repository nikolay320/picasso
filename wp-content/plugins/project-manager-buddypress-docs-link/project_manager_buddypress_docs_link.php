<?php
/*
Plugin Name: Project Manager Buddpress Docs Link

Plugin URI: Project Manager Buddpress Docs Link

Description: Project Manager Buddpress Docs Link

Version: 1.0.2

Author: phandung122

Author URI: http://www.upwork.com/o/profiles/users/_~016252273d5cf3683a/

License: 

*/


/**
 * Include CSS file for Multi address.
 */

wp_enqueue_script( 'jquery', plugin_dir_url( __FILE__ ) . 'jquery-1.12.2.min.js');
wp_enqueue_script("project-bp-docs-link", plugin_dir_url( __FILE__ ) . 'project_manager_buddypress_docs_link.js'); 

add_action('cpm_comment_new', 'project_manager_buddypress_docs_link_cpm_comment_new', 9, 3);
function project_manager_buddypress_docs_link_cpm_comment_new( $comment_id, $project_id, $commentdata ){	
	$message_id = $commentdata['comment_post_ID'];
	$author_id = $commentdata['user_id'];
	$new_attachment = project_manager_buddypress_docs_link_check_attachment($commentdata['comment_post_ID']);

	$group_id = get_post_meta ( $project_id , '_project_bp_group', true);
	if (!$group_id && bp_is_active( 'groups' ) ) {
		$group_id = project_manager_buddypress_docs_link_create_bp_group ($project_id);
	} else {
		$group_id = 0;
	}
	
	if ( $new_attachment ) {
		if (!bp_is_active( 'groups' )) {
			$member_list = project_manager_buddypress_docs_link_get_project_user ( $project_id );
			foreach ($member_list as $author_id ) {
				project_manager_buddypress_docs_link_add_document ($message_id, 0 , $author_id, $new_attachment, $group_id );	
			}
		} else {
			project_manager_buddypress_docs_link_add_document ($message_id, 0 , $author_id, $new_attachment, $group_id );	
		}
	}
}

add_action( 'cpm_message_new', 'project_manager_buddypress_docs_link_cpm_message_new', 9, 3 );
function project_manager_buddypress_docs_link_cpm_message_new( $message_id, $project_id, $postarr ){	
	$message_id = $message_id;
	$discusstion = get_post ( $message_id );
	$author_id = $discusstion->post_author;
	$new_attachment = project_manager_buddypress_docs_link_check_attachment($message_id);
	
	$group_id = get_post_meta ( $project_id , '_project_bp_group', true);
	if (!$group_id && bp_is_active( 'groups' ) ) {
		$group_id = project_manager_buddypress_docs_link_create_bp_group ($project_id);
	} else {
		$group_id = 0;
	}
	
	if ( $new_attachment ) {
		if (!bp_is_active( 'groups' )) {
			$member_list = project_manager_buddypress_docs_link_get_project_user ( $project_id );
			foreach ($member_list as $author_id ) {
				project_manager_buddypress_docs_link_add_document ($message_id, 0 , $author_id, $new_attachment, $group_id );	
			}
		} else {
			project_manager_buddypress_docs_link_add_document ($message_id, 0 , $author_id, $new_attachment, $group_id );	
		}
	}
}

function project_manager_buddypress_docs_link_check_attachment( $post_id ){
	
	$new_attachment = array();
	
	$args = array(
		'numberposts' => -1,
		'order' => 'ASC',
		'post_parent' => $post_id,
		'post_status' => null,
		'post_type' => 'attachment',
		'post_mime_type' => array(
			'text/plain', 
			'application/pdf', 
			'application/doc', 
			'application/docx', 
			'application/xls', 
			'application/xlsx', 
			'application/ppt', 
			'application/pptx', 
			'application/txt', 
			'application/zip',
			'application/rar', 
			'application/vnd.ms-excel', 
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 
			'application/vnd.ms-powerpoint', 
			'application/vnd.openxmlformats-officedocument.presentationml.presentation', 
			'application/msword', 
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 
			),
	);

	$attachments = get_children( $args );

	if ( $attachments ) {
		foreach ( $attachments as $attachment ) {
			$buddypress_docs_added = get_post_meta( $attachment->ID , 'buddypress_docs_added', true );
			if( !$buddypress_docs_added ) {
				$new_attachment[] = $attachment;
			}
		}
	}
	return $new_attachment;
}

function project_manager_buddypress_docs_link_get_project_setting ( $project_id ){
	$project_setting = get_post_meta( $project_id , '_settings', true );
	return $project_setting;
}

function project_manager_buddypress_docs_link_get_project_user ( $project_id ){
	global $wpdb;
	$user_list = array();
	
	$table_name = $wpdb->prefix . 'cpm_user_role';
		$query='SELECT * FROM '.$table_name.' WHERE project_id= '.$project_id;
		$user_query = $wpdb->get_results( $query );
	foreach ($user_query as $user ) {
		$user_list[] = $user->user_id;
	}
	
	return $user_list;
}

function project_manager_buddypress_docs_link_add_document ($message_id, $comment_id, $author_id, $new_attachment, $group_id ) {	
	$message_post = get_post ($message_id);
	$project_id = $message_post->post_parent;
	$project_post = get_post ($message_post->post_parent);
	$message_post_link = get_site_url().'/projets/?project_id='.$message_post->post_parent.'&tab=message&action=single&message_id='.$message_id;
	if ( $comment_id ) {
		$message_post_link .= '#cpm-comment-'.$comment_id;
	}
	$bp_doc_content = '<a href="'.$message_post_link.'">'.$message_post_link.'</a>';
	//$bp_doc_title = $author_id .' '. $project_post->post_title .' '. $message_post->post_title;
	$bp_doc_title = $project_post->post_title .' '. $message_post->post_title;
	
	$sql_data_array=array();
    $sql_data_array=array(
                          'post_title'    => $bp_doc_title,
                          'post_content'  => $bp_doc_content,
                          'post_author'   => $author_id,
                          'post_status' => 'publish',
                          'post_type' => 'bp_doc',
                         );
    $now=current_time('mysql');
    $sql_data_array['post_date']=$now;
    $sql_data_array['post_date_gmt']=current_time('mysql',1);
	
    if($post_ID = wp_insert_post($sql_data_array))
    {
		if( $group_id && bp_is_active( 'groups' ) ) {		
			$permission = array (
				'read' => 'group-members',
				'edit' => 'group-members',
				'read_comments' => 'group-members',
				'post_comments' => 'group-members',
				'view_history' => 'group-members',
			);
			update_post_meta( $post_ID, 'bp_docs_settings', $permission );
			
			$term_associated = get_term_by ('slug', 'bp_docs_associated_group_'. $group_id , 'bp_docs_associated_item');
			if ( !$term_associated ) {
				wp_insert_term(
					$message_post->post_title, 
					'bp_docs_associated_item',
					array(
						'description'=> 'Docs associated with the project '. $message_post->post_title ,
						'slug' => 'bp_docs_associated_group_'. $group_id ,
						'parent'=> 0,
					)
				);
				$term_associated = get_term_by ('slug', 'bp_docs_associated_group_'. $group_id , 'bp_docs_associated_item');
			}
			
			$term_access = get_term_by ('slug', 'bp_docs_access_group_member_'. $group_id, 'bp_docs_access' );
			if ( !$term_access ) {
				wp_insert_term(
					'bp_docs_access_group_member_'. $group_id , 
					'bp_docs_access',
					array(
						'description'=> '' ,
						'slug' => 'bp_docs_access_group_member_'. $group_id ,
						'parent'=> 0,
					)
				);
				$term_access = get_term_by ('slug', 'bp_docs_access_group_member_'. $group_id, 'bp_docs_access' );
			}
			if (bp_is_active( 'groups' )) {
				bp_docs_set_associated_group_id( $post_ID, $group_id );			
				$term = wp_set_post_terms( $post_ID, 'bp_docs_access_group_member_'. $group_id , 'bp_docs_access');		
			}	
		}
		else {
			$permission = array (
				'read' => 'creator',
				'edit' => 'creator',
				'read_comments' => 'creator',
				'post_comments' => 'creator',
				'view_history' => 'creator',
			);
			update_post_meta( $post_ID, 'bp_docs_settings', $permission );
			
			$term_access = get_term_by ('slug', 'bp_docs_access_user_'. $author_id, 'bp_docs_access' );
			if ( !$term_access ) {
				wp_insert_term(
					'bp_docs_access_user_'. $author_id, 
					'bp_docs_access',
					array(
						'description'=> '' ,
						'slug' => 'bp_docs_access_user_'. $author_id ,
						'parent'=> 0,
					)
				);
				$term_access = get_term_by ('slug', 'bp_docs_access_user_'. $author_id, 'bp_docs_access' );
			}
			//$access_term = bp_docs_get_access_term_user( $author_id );
			$term = wp_set_post_terms( $post_ID, 'bp_docs_access_user_'. $author_id , 'bp_docs_access');	
		}
		
		$doc_tag = get_term_by ('slug', sanitize_title($project_post->post_title), 'bp_docs_tag' );
			if ( !$doc_tag ) {
				wp_insert_term(
					$project_post->post_title, 
					'bp_docs_tag',
					array(
						'description'=> '' ,
						'slug' => sanitize_title($project_post->post_title),
						'parent'=> 0,
					)
				);
				$doc_tag = get_term_by ('slug', sanitize_title($project_post->post_title), 'bp_docs_tag' );
			}
		$term = wp_set_post_terms( $post_ID, sanitize_title($project_post->post_title), 'bp_docs_tag');	
		
		update_post_meta( $post_ID, 'bp_docs_project', $message_post->post_parent );

	}
	
	foreach ( $new_attachment as $attachment ) {
		
		$path = get_post_meta ($attachment->ID, '_wp_attached_file', true ) ;
		$filename = explode('/', $path)[2];
		$source_file = ABSPATH .'/wp-content/uploads/'.$path;
		$des_file = ABSPATH .'/wp-content/uploads/bp-attachments/'.$post_ID.'/'.$filename;

		if (!file_exists(ABSPATH .'/wp-content/uploads/bp-attachments/'.$post_ID)) {
			mkdir(ABSPATH .'/wp-content/uploads/bp-attachments/'.$post_ID, 0777, true);
		}

		if (!copy($source_file, $des_file)) {
			//echo "failed to copy\n";
		}

		$sql_data_array=array();
		$sql_data_array=array(
                          'post_title'    => $filename,
                          'post_content'  => '',
                          'post_author'   => $author_id,
                          'post_status' => 'publish',
                          'post_type' => 'attachment',
                          'post_parent' => $post_ID,
                          'guid' => get_site_url().'/wp-content/uploads/bp-attachments/'.$post_ID.'/'.$filename,
                          'post_mime_type' => $attachment->post_mime_type,
                         );
		$now=current_time('mysql');
		$sql_data_array['post_date']=$now;
		$sql_data_array['post_date_gmt']=current_time('mysql',1);
  
		if($docs_ID = wp_insert_post($sql_data_array))
		{ 
			add_post_meta( $docs_ID, '_wp_attached_file', 'bp-attachments/'.$post_ID.'/'.$filename);
			add_post_meta( $attachment->ID, 'buddypress_docs_added', '1');
		}
	}
	
	return;
}

function project_manager_buddypress_docs_link_create_bp_group($project_id) {
  if ( !bp_is_active( 'groups' ) ) return 0;
  $project = get_post($project_id);
  $group_args = array();
  $group_args['name'] = $project->post_title;
  $group_args['description'] = "A group to hold the files of project: " . $project->post_title;
  $group_args['creator_id'] = 1;
  $group_args['status'] = 'private';   //  could be hidden or public
  $id_group = groups_create_group($group_args);
  
  $user_list = project_manager_buddypress_docs_link_get_project_user ( $project_id );
  foreach ($user_list as $user_id ) {
	  groups_accept_invite($user_id, $id_group);
  }
  add_post_meta( $project_id, '_project_bp_group', $id_group);
  
  return $id_group;
  }

  
add_action( 'cpm_project_update', 'project_manager_buddypress_docs_link_project_update' , 10, 3 );
    function project_manager_buddypress_docs_link_project_update( $project_id, $data, $posted ) {
		if ( bp_is_active( 'groups' ) ) {
			$group_id = get_post_meta ( $project_id , '_project_bp_group', true);
			if (!$group_id ) {
				$group_id = project_manager_buddypress_docs_link_create_bp_group ($project_id);
			} else {
				$members = groups_get_group_members ( $group_id );
				foreach ( $members['members'] as $member ) {
					groups_leave_group( $group_id, $member->user_id );
				}
				
				$user_list = project_manager_buddypress_docs_link_get_project_user ( $project_id );
				foreach ($user_list as $user_id ) {
					groups_accept_invite($user_id, $group_id);
				}
			}
		} else {
			
		}
		
    }

add_action('pre_get_posts', 'exclude_project_docs_posts');
function exclude_project_docs_posts($query) {
	
  if ( $query->query['post_type'] == 'bp_doc' && $query->is_author ) {
		//Get original meta query
		$meta_query = $query->get('meta_query');

		//Add our meta query to the original meta queries
		$meta_query[] = array(
                    'key'=>'bp_docs_project',
                    //'value'=> '',
					'compare' => 'NOT EXISTS'
                );
		$query->set('meta_query',$meta_query);
  }
  
}

