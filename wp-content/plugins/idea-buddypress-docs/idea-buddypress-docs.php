<?php
/*
Plugin Name: Idea Buddpress Docs 

Plugin URI: Idea Buddpress Docs 

Description: Idea Buddpress Docs 

Version: 1.0.2

Author: phandung122

Author URI: http://www.upwork.com/o/profiles/users/_~016252273d5cf3683a/

License: 

Instruction:

modify function in idea.php plugin: add line : do_action( 'after_save_idea', $post_id, get_post($post_id)  );

function add_idea_callback(){
	if($_POST){
		$postarr = array(
			'post_title'	=> $_POST['title'],
			'post_content'	=> $_POST['text'],			
			'post_type' 	=> 'ideas',
		);
		if(is_user_logged_in ()){
			$postarr['post_status'] = 'publish';
		}
		$post_id = wp_insert_post($postarr);

		if($post_id){
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			if($_FILES['image']['name']){
				add_attachment($_FILES['image'],$post_id,'image');
			}
			if($_FILES['attachment']['name']){
				add_attachment($_FILES['attachment'],$post_id,'file');
			}
			if($_POST['idea_campaign']){
				add_post_meta($_POST['idea_campaign'],'campaign_ideas',$post_id);
			}

			$result = 'Thank you. Idea successfully added.';
			if(!is_user_logged_in()){
				$result.= ' It will appear after moderation.';
			}
			echo json_encode(array('html'=>$result));
		}
		do_action( 'after_save_idea', $post_id, get_post($post_id)  );
	} else {
		echo json_encode(array('error'=>'Idea not added!'));
	}
    exit;
}


*/

function insert_buddypress_docs_from_idea ( $post_id, $post ) {
	
	/* return if post type is not 'ideas' or there's no docs attached. */
    $slug = 'idea';
    // If this isn't a 'book' post, don't update it.

	//$idea_file_url = get_post_meta( $post_id, 'idea_file_url', true );
	$idea_file_id = get_post_meta( $post_id, '_idea_files', true );
	
    if ( $slug != $post->post_type ) {
        return;
    }	
	if (!$idea_file_id) return;
	
	global $wpdb;	
	/* prepare buddypress docs content */
	$user_id = $post->post_author;
	$title = $post->post_title;
	$slug = $post->post_name;
	$content = $post->post_content;
	
	$link = get_post_permalink($post_id);
	$html_link = '<p><a href="'.$link.'">'.$link.'</a></p>';
	$content .= $html_link;
	$guid = get_site_url().'/docs/';
	
	$post_date = $post->post_date;
	$post_date_gmt = $post->post_date_gmt;
	
	//create new buddypress docs post
	$table = $wpdb->prefix."posts";
	$data = array( 
'ID'	=>NULL,
'post_author'	=> $user_id,
'post_date'	=> $post_date,
'post_date_gmt'	=> $post_date_gmt,
'post_content'	=> $content,
'post_title'	=> $title,
'post_excerpt'	=>'',
'post_status'	=>'publish',
'comment_status'	=>'open',
'ping_status'	=>'closed',
'post_password'	=>'',
'post_name'	=> 'idea-'.$slug,
'to_ping'	=>'',
'pinged'	=>'',
'post_modified'	=> $post_date,
'post_modified_gmt'	=> $post_date_gmt,
'post_content_filtered'	=>'',
'post_parent'	=>'',
'guid'	=> $guid,
'menu_order'	=>'',
'post_type'	=>'bp_doc',
'post_mime_type'	=>'',
'comment_count'	=>'',
		);
	$wpdb->insert( $table, $data);
	$post_id = $wpdb->insert_id;
	
	/* add buddypress docs permission */
	$permission = array (
		'read' => 'anyone',
		'edit' => 'loggedin',
		'read_comments' => 'anyone',
		'post_comments' => 'anyone',
		'view_history' => 'anyone',
		);
	add_post_meta( $post_id, 'bp_docs_settings', $permission );
	
	$table_term = $wpdb->prefix."term_relationships";
	$data_term = array( 
'object_id'	=> $post_id,
'term_taxonomy_id'	=> 232,
'term_order'	=> 0,
		);
	$wpdb->insert( $table_term, $data_term);
	
	/* create new buddypress docs folder if not exists */
if (!file_exists(ABSPATH .'/wp-content/uploads/bp-attachments/'.$post_id)) {
    mkdir(ABSPATH .'/wp-content/uploads/bp-attachments/'.$post_id, 0777, true);
}

	/* copy file from original post to buddypress docs post */
$file_url = get_post_meta( $idea_file_id, '_wp_attached_file', true );
$file = get_post($idea_file_id);
$file_name = sanitize_file_name( $file->post_title );
$source_file = ABSPATH .'/wp-content/uploads/'.$file_url;
$des_file = ABSPATH .'/wp-content/uploads/bp-attachments/'.$post_id.'/'.$file_name;

	
if (!copy($source_file, $des_file)) {
    //write_log("failed to copy\n");
}
//create new buddypress docs attached post
$table = $wpdb->prefix."posts";
$data = array( 
'ID'	=>NULL,
'post_author'	=> $user_id,
'post_date'	=> $post_date,
'post_date_gmt'	=> $post_date_gmt,
'post_content'	=> '',
'post_title'	=> $file->post_title,
'post_excerpt'	=>'',
'post_status'	=>'inherit',
'comment_status'	=>'closed',
'ping_status'	=>'closed',
'post_password'	=>'',
'post_name'	=> 'idea-'.$file->post_name,
'to_ping'	=>'',
'pinged'	=>'',
'post_modified'	=> $post_date,
'post_modified_gmt'	=> $post_date_gmt,
'post_content_filtered'	=>'',
'post_parent'	=> $post_id,
'guid'	=> get_site_url().'/wp-content/uploads/bp-attachments/'.$post_id.'/'.$file_name,
'menu_order'	=>'',
'post_type'	=>'attachment',
'post_mime_type'	=> $file->post_mime_type,
'comment_count'	=>'',
		);
	$wpdb->insert( $table, $data);
	$attached_id = $wpdb->insert_id;
	//add attached metadata
	add_post_meta( $attached_id, '_wp_attached_file', 'bp-attachments/'.$post_id.'/'.$file_name);
	
}   
add_action( 'after_save_idea', 'insert_buddypress_docs_from_idea', 10, 2 );