<?php
/*
Plugin Name: Sabai Buddpress Docs Link

Plugin URI: Sabai Buddpress Docs Link

Description: Sabai Buddpress Docs Link

Version: 1.0.5

Author: phandung122

Author URI: http://www.upwork.com/o/profiles/users/_~016252273d5cf3683a/

License: 

*/


/**
 * Include CSS file for Multi address.
 */

add_action('sabai_entity_create_content_directory_listing_entity_success', 'sabai_directory_listing_buddypress_docs_link', 10, 3);
function sabai_directory_listing_buddypress_docs_link($bundle, $entity, $values) {	
	global $wpdb;	
	//get information
	$entity_id = $entity->content_post_id[0];
	$user_id = get_current_user_id();	
	$title = $entity->content_post_title[0];
	$slug = $entity->content_post_slug[0];
	//$content = $entity->getContent();

	$table_name = $wpdb->prefix . 'sabai_entity_field_field_contexte';
	$query = 'SELECT value FROM ' . $table_name .
		' WHERE entity_id=' . $entity_id;
	//echo 'Query: ' . $query . "\n";
	$value_query = $wpdb->get_results( $query );
	$content = $value_query[0]->value;
	
	$link = get_site_url().$entity->getUrlPath($bundle);
	$html_link = '<p><a href="'.$link.'">'.$link.'</a></p>';
	$content .= $html_link;
	$guid = get_site_url().'/docs/';
	
	//get entity data
	$table_name = $wpdb->prefix . 'sabai_entity_field_field_documentassociefiche';
	$query = 'SELECT * FROM ' . $table_name .
		' WHERE entity_id=' . $entity_id;
	//echo 'Query: ' . $query . "\n";
	$entity_query = $wpdb->get_results( $query );
	//write_log($entity_query);
	if(!count($entity_query)) return;
	
	//create new buddypress docs post
	$table = $wpdb->prefix."posts";
	$data = array( 
'ID'	=>NULL,
'post_author'	=> $user_id,
'post_date'	=> current_time('mysql'),
'post_date_gmt'	=> current_time('mysql',1),
'post_content'	=> $content,
'post_title'	=> $title,
'post_excerpt'	=>'',
'post_status'	=>'publish',
'comment_status'	=>'open',
'ping_status'	=>'closed',
'post_password'	=>'',
'post_name'	=> 'article-'.$slug,
'to_ping'	=>'',
'pinged'	=>'',
'post_modified'	=> current_time('mysql'),
'post_modified_gmt'	=> current_time('mysql',1),
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
	
	//get file data foreach entity row
	foreach ($entity_query as $entity) {
		$table_name = $wpdb->prefix . 'sabai_file_file';
		$query='SELECT * FROM '.$table_name.' WHERE file_id= '.$entity->file_id;
		$file_query = $wpdb->get_results( $query );
		
		$file_title = sanitize_title($file_query[0]->file_title);
$source_file = ABSPATH .'/wp-content/sabai/File/files/'.$file_query[0]->file_name;
$des_file = ABSPATH .'/wp-content/uploads/bp-attachments/'.$post_id.'/'.$file_title.'.'.$file_query[0]->file_extension;


if (!file_exists(ABSPATH .'/wp-content/uploads/bp-attachments/'.$post_id)) {
    mkdir(ABSPATH .'/wp-content/uploads/bp-attachments/'.$post_id, 0777, true);
}

if (!copy($source_file, $des_file)) {
    //echo "failed to copy\n";
}
//create new buddypress docs post
$table = $wpdb->prefix."posts";
$data = array( 
'ID'	=>NULL,
'post_author'	=> $user_id,
'post_date'	=> current_time('mysql'),
'post_date_gmt'	=> current_time('mysql',1),
'post_content'	=> '',
'post_title'	=> $file_query[0]->file_title,
'post_excerpt'	=>'',
'post_status'	=>'inherit',
'comment_status'	=>'closed',
'ping_status'	=>'closed',
'post_password'	=>'',
'post_name'	=> $file_title,
'to_ping'	=>'',
'pinged'	=>'',
'post_modified'	=> current_time('mysql'),
'post_modified_gmt'	=> current_time('mysql',1),
'post_content_filtered'	=>'',
'post_parent'	=> $post_id,
'guid'	=> get_site_url().'/wp-content/uploads/bp-attachments/'.$post_id.'/'.$file_title,
'menu_order'	=>'',
'post_type'	=>'attachment',
'post_mime_type'	=>'application/'.$file_query[0]->file_extension,
'comment_count'	=>'',
		);
	$wpdb->insert( $table, $data);
	$attached_id = $wpdb->insert_id;
	//add attached metaphone
	add_post_meta( $attached_id, '_wp_attached_file', 'bp-attachments/'.$post_id.'/'.$file_title.'.'.$file_query[0]->file_extension);
	//add database for attached file with buddypress docs
			
	}
	
}

add_action('sabai_entity_create_content_questions_entity_success', 'sabai_discuss_buddypress_docs_link', 10, 3);
function sabai_discuss_buddypress_docs_link($bundle, $entity, $values) {	
	global $wpdb;	
	//get information
	$entity_id = $entity->content_post_id[0];
	$user_id = get_current_user_id();	
	$title = $entity->content_post_title[0];
	$slug = $entity->content_post_slug[0];
	$content = $entity->getContent();
	$link = get_site_url().$entity->getUrlPath($bundle);
	$html_link = '<p><a href="'.$link.'">'.$link.'</a></p>';
	$content .= $html_link;
	$guid = get_site_url().'/docs/';
	
	//get entity data
	$table_name = $wpdb->prefix . 'sabai_entity_field_field_documentassocie';
	$query = 'SELECT * FROM ' . $table_name .
		' WHERE entity_id=' . $entity_id;
	//echo 'Query: ' . $query . "\n";
	$entity_query = $wpdb->get_results( $query );
	//write_log($entity_query);
	if(!count($entity_query)) return;
	
	//create new buddypress docs post
	$table = $wpdb->prefix."posts";
	$data = array( 
'ID'	=>NULL,
'post_author'	=> $user_id,
'post_date'	=> current_time('mysql'),
'post_date_gmt'	=> current_time('mysql',1),
'post_content'	=> $content,
'post_title'	=> $title,
'post_excerpt'	=>'',
'post_status'	=>'publish',
'comment_status'	=>'open',
'ping_status'	=>'closed',
'post_password'	=>'',
'post_name'	=> 'question-'.$slug,
'to_ping'	=>'',
'pinged'	=>'',
'post_modified'	=> current_time('mysql'),
'post_modified_gmt'	=> current_time('mysql',1),
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
	
	//get file data foreach entity row
	foreach ($entity_query as $entity) {
		$table_name = $wpdb->prefix . 'sabai_file_file';
		$query='SELECT * FROM '.$table_name.' WHERE file_id= '.$entity->file_id;
		$file_query = $wpdb->get_results( $query );
		
		$file_title = sanitize_title($file_query[0]->file_title);
$source_file = ABSPATH .'/wp-content/sabai/File/files/'.$file_query[0]->file_name;
$des_file = ABSPATH .'/wp-content/uploads/bp-attachments/'.$post_id.'/'.$file_title.'.'.$file_query[0]->file_extension;


if (!file_exists(ABSPATH .'/wp-content/uploads/bp-attachments/'.$post_id)) {
    mkdir(ABSPATH .'/wp-content/uploads/bp-attachments/'.$post_id, 0777, true);
}

if (!copy($source_file, $des_file)) {
    //echo "failed to copy\n";
}
//create new buddypress docs post
$table = $wpdb->prefix."posts";
$data = array( 
'ID'	=>NULL,
'post_author'	=> $user_id,
'post_date'	=> current_time('mysql'),
'post_date_gmt'	=> current_time('mysql',1),
'post_content'	=> '',
'post_title'	=> $file_query[0]->file_title,
'post_excerpt'	=>'',
'post_status'	=>'inherit',
'comment_status'	=>'closed',
'ping_status'	=>'closed',
'post_password'	=>'',
'post_name'	=> $file_title,
'to_ping'	=>'',
'pinged'	=>'',
'post_modified'	=> current_time('mysql'),
'post_modified_gmt'	=> current_time('mysql',1),
'post_content_filtered'	=>'',
'post_parent'	=> $post_id,
'guid'	=> get_site_url().'/wp-content/uploads/bp-attachments/'.$post_id.'/'.$file_title,
'menu_order'	=>'',
'post_type'	=>'attachment',
'post_mime_type'	=>'application/'.$file_query[0]->file_extension,
'comment_count'	=>'',
		);
	$wpdb->insert( $table, $data);
	$attached_id = $wpdb->insert_id;
	//add attached metaphone
	add_post_meta( $attached_id, '_wp_attached_file', 'bp-attachments/'.$post_id.'/'.$file_title.'.'.$file_query[0]->file_extension);
	//add database for attached file with buddypress docs
			
	}
	
	
	
}

add_action('sabai_entity_create_content_questions_answers_entity_success', 'sabai_comment_buddypress_docs_link', 10, 3);
function sabai_comment_buddypress_docs_link($bundle, $entity, $values) {	
global $wpdb;	
	//get information
	$entity_id = $entity->content_post_id[0];
	$user_id = get_current_user_id();	
	$title = $entity->content_post_title[0];
	//$slug = $entity->content_post_slug[0];
	$slug = 'sabai-entity-content-'.$entity_id;
	$content = $entity->getContent();
	$link = get_site_url().$entity->getUrlPath($bundle);
	$html_link = '<p><a href="'.$link.'">'.$link.'</a></p>';
	$content .= $html_link;
	$guid = get_site_url().'/docs/';
	
	//get entity data
	$table_name = $wpdb->prefix . 'sabai_entity_field_field_doc_reponse';
	$query = 'SELECT * FROM ' . $table_name .
		' WHERE entity_id=' . $entity_id;
	//echo 'Query: ' . $query . "\n";
	$entity_query = $wpdb->get_results( $query );
	//write_log($entity_query);
	if(!count($entity_query)) return;
	
	//create new buddypress docs post
	$table = $wpdb->prefix."posts";
	$data = array( 
'ID'	=>NULL,
'post_author'	=> $user_id,
'post_date'	=> current_time('mysql'),
'post_date_gmt'	=> current_time('mysql',1),
'post_content'	=> $content,
'post_title'	=> $title,
'post_excerpt'	=>'',
'post_status'	=>'publish',
'comment_status'	=>'open',
'ping_status'	=>'closed',
'post_password'	=>'',
'post_name'	=> 'answer-'.$slug,
'to_ping'	=>'',
'pinged'	=>'',
'post_modified'	=> current_time('mysql'),
'post_modified_gmt'	=> current_time('mysql',1),
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
	
	//get file data foreach entity row
	foreach ($entity_query as $entity) {
		$table_name = $wpdb->prefix . 'sabai_file_file';
		$query='SELECT * FROM '.$table_name.' WHERE file_id= '.$entity->file_id;
		$file_query = $wpdb->get_results( $query );
		
		$file_title = sanitize_title($file_query[0]->file_title);
$source_file = ABSPATH .'/wp-content/sabai/File/files/'.$file_query[0]->file_name;
$des_file = ABSPATH .'/wp-content/uploads/bp-attachments/'.$post_id.'/'.$file_title.'.'.$file_query[0]->file_extension;


if (!file_exists(ABSPATH .'/wp-content/uploads/bp-attachments/'.$post_id)) {
    mkdir(ABSPATH .'/wp-content/uploads/bp-attachments/'.$post_id, 0777, true);
}

if (!copy($source_file, $des_file)) {
    //echo "failed to copy\n";
}
//create new buddypress docs post
$table = $wpdb->prefix."posts";
$data = array( 
'ID'	=>NULL,
'post_author'	=> $user_id,
'post_date'	=> current_time('mysql'),
'post_date_gmt'	=> current_time('mysql',1),
'post_content'	=> '',
'post_title'	=> $file_query[0]->file_title,
'post_excerpt'	=>'',
'post_status'	=>'inherit',
'comment_status'	=>'closed',
'ping_status'	=>'closed',
'post_password'	=>'',
'post_name'	=> $file_title,
'to_ping'	=>'',
'pinged'	=>'',
'post_modified'	=> current_time('mysql'),
'post_modified_gmt'	=> current_time('mysql',1),
'post_content_filtered'	=>'',
'post_parent'	=> $post_id,
'guid'	=> get_site_url().'/wp-content/uploads/bp-attachments/'.$post_id.'/'.$file_title,
'menu_order'	=>'',
'post_type'	=>'attachment',
'post_mime_type'	=>'application/'.$file_query[0]->file_extension,
'comment_count'	=>'',
		);
	$wpdb->insert( $table, $data);
	$attached_id = $wpdb->insert_id;
	//add attached metaphone
	add_post_meta( $attached_id, '_wp_attached_file', 'bp-attachments/'.$post_id.'/'.$file_title.'.'.$file_query[0]->file_extension);
	//add database for attached file with buddypress docs
			
	}
	
}


add_action('sabai_entity_create_content_directory_listing_review_entity_success', 'sabai_review_buddypress_docs_link', 10, 3);
function sabai_review_buddypress_docs_link($bundle, $entity, $values) {
global $wpdb;	
	//get information
	$entity_id = $entity->content_post_id[0];
	$user_id = get_current_user_id();	
	$title = $entity->content_post_title[0];
	//$slug = $entity->content_post_slug[0];
	$slug = 'sabai-entity-content-'.$entity_id;
	$content = $entity->getContent();
	$link = get_site_url().$entity->getUrlPath($bundle);
	$html_link = '<p><a href="'.$link.'">'.$link.'</a></p>';
	$content .= $html_link;
	$guid = get_site_url().'/docs/';
	
	//get entity data
	
	//$table_name = $wpdb->prefix . 'sabai_entity_field_field_fichiercampagne';
	$table_name = $wpdb->prefix . 'sabai_entity_field_field_fichiersarticles';
	$query = 'SELECT * FROM ' . $table_name .
		' WHERE entity_id=' . $entity_id;
	//echo 'Query: ' . $query . "\n";
	$entity_query = $wpdb->get_results( $query );
	//write_log($entity_query);
	if(!count($entity_query)) return;
	
	//create new buddypress docs post
	$table = $wpdb->prefix."posts";
	$data = array( 
'ID'	=>NULL,
'post_author'	=> $user_id,
'post_date'	=> current_time('mysql'),
'post_date_gmt'	=> current_time('mysql',1),
'post_content'	=> $content,
'post_title'	=> $title,
'post_excerpt'	=>'',
'post_status'	=>'publish',
'comment_status'	=>'open',
'ping_status'	=>'closed',
'post_password'	=>'',
'post_name'	=> 'review-'.$slug,
'to_ping'	=>'',
'pinged'	=>'',
'post_modified'	=> current_time('mysql'),
'post_modified_gmt'	=> current_time('mysql',1),
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
	
	//get file data foreach entity row
	foreach ($entity_query as $entity) {
		$table_name = $wpdb->prefix . 'sabai_file_file';
		$query='SELECT * FROM '.$table_name.' WHERE file_id= '.$entity->file_id;
		$file_query = $wpdb->get_results( $query );
		
		$file_title = sanitize_title($file_query[0]->file_title);
$source_file = ABSPATH .'/wp-content/sabai/File/files/'.$file_query[0]->file_name;
$des_file = ABSPATH .'/wp-content/uploads/bp-attachments/'.$post_id.'/'.$file_title.'.'.$file_query[0]->file_extension;


if (!file_exists(ABSPATH .'/wp-content/uploads/bp-attachments/'.$post_id)) {
    mkdir(ABSPATH .'/wp-content/uploads/bp-attachments/'.$post_id, 0777, true);
}

if (!copy($source_file, $des_file)) {
    //echo "failed to copy\n";
}
//create new buddypress docs post
$table = $wpdb->prefix."posts";
$data = array( 
'ID'	=>NULL,
'post_author'	=> $user_id,
'post_date'	=> current_time('mysql'),
'post_date_gmt'	=> current_time('mysql',1),
'post_content'	=> '',
'post_title'	=> $file_query[0]->file_title,
'post_excerpt'	=>'',
'post_status'	=>'inherit',
'comment_status'	=>'closed',
'ping_status'	=>'closed',
'post_password'	=>'',
'post_name'	=> $file_title,
'to_ping'	=>'',
'pinged'	=>'',
'post_modified'	=> current_time('mysql'),
'post_modified_gmt'	=> current_time('mysql',1),
'post_content_filtered'	=>'',
'post_parent'	=> $post_id,
'guid'	=> get_site_url().'/wp-content/uploads/bp-attachments/'.$post_id.'/'.$file_title,
'menu_order'	=>'',
'post_type'	=>'attachment',
'post_mime_type'	=>'application/'.$file_query[0]->file_extension,
'comment_count'	=>'',
		);
	$wpdb->insert( $table, $data);
	$attached_id = $wpdb->insert_id;
	//add attached metaphone
	add_post_meta( $attached_id, '_wp_attached_file', 'bp-attachments/'.$post_id.'/'.$file_title.'.'.$file_query[0]->file_extension);
	//add database for attached file with buddypress docs
			
	}
	
}