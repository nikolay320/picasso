<?php
/*
Plugin Name: Sabai Mediapress Link

Plugin URI: Sabai Mediapress Link

Description: Sabai Mediapress Link

Version: 1.0.2

Author: phandung122

Author URI: http://www.upwork.com/o/profiles/users/_~016252273d5cf3683a/

License: 

*/


/**
 * Include CSS file for Multi address.
 */

wp_enqueue_script( 'jquery', plugin_dir_url( __FILE__ ) . 'jquery-1.12.2.min.js');
wp_enqueue_script( 'flowplayer', plugin_dir_url( __FILE__ ) . 'flowplayer/flowplayer.min.js');
wp_enqueue_script("sabai-mediapress-link", plugin_dir_url( __FILE__ ) . 'sabai-mediapress-link.js'); 

    //wp_enqueue_style( 'flowplayer-all-skins',  plugin_dir_url( __FILE__ ) . 'flowplayer/skin/all-skins.css' );

add_action('sabai_entity_create_content_directory_listing_entity_success', 'sabai_directory_listing_mediapress_link', 10, 3);
function sabai_directory_listing_mediapress_link($bundle, $entity, $values) {		
	//get information
	$entity_id = $entity->content_post_id[0];
	$user_id = get_current_user_id();
	
	sabai_mediapress_directory_link ($user_id, $entity_id);
}

add_action('sabai_entity_create_content_directory_listing_review_entity_success', 'sabai_review_mediapress_link', 10, 3);
function sabai_review_mediapress_link($bundle, $entity, $values) {	
	//get information
	$entity_id = $entity->content_post_id[0];
	$user_id = get_current_user_id();
	
	sabai_mediapress_directory_link ($user_id, $entity_id);
}

add_action('sabai_entity_create_content_questions_entity_success', 'sabai_discuss_mediapress_link', 10, 3);
function sabai_discuss_mediapress_link($bundle, $entity, $values) {		
	//get information
	$entity_id = $entity->content_post_id[0];
	$user_id = get_current_user_id();	
	
	sabai_mediapress_questions_link ($user_id, $entity_id);
}

add_action('sabai_entity_create_content_questions_answers_entity_success', 'sabai_comment_mediapress_link', 10, 3);
function sabai_comment_mediapress_link($bundle, $entity, $values) {	

	//get information
	$entity_id = $entity->content_post_id[0];
	$user_id = get_current_user_id();	
	
	sabai_mediapress_questions_link ($user_id, $entity_id);
}

function sabai_mediapress_find_term_id ($slug) {
	global $wpdb;	
	$term_id = 0;
	$table_name = $wpdb->prefix . 'terms';
	$query = 'SELECT * FROM ' . $table_name .
		' WHERE slug = \'' . $slug .'\'';
	$term_query = $wpdb->get_results( $query );
	if(count($term_query)) $term_id = $term_query[0]->term_id;
	else $term_id = 0;
	return $term_id;
}

function sabai_mediapress_directory_find_gallery ( $user_id ) {
	global $wpdb;	
	$gallery_id = 0;
	
	$table_name = $wpdb->prefix . 'posts';
	$query = 'SELECT * FROM ' . $table_name .
		' WHERE post_author =' . $user_id . ' AND post_title=\'Theme Video Gallery\'' ;
	$gallery_query = $wpdb->get_results( $query );
	if(!count($gallery_query)) $gallery_id = sabai_mediapress_directory_create_gallery ( $user_id );
	else $gallery_id = $gallery_query[0]->ID;
	return $gallery_id;
}

function sabai_mediapress_directory_create_gallery ( $user_id ) {
	global $wpdb;	
	
//create new theme gallery post
	$table = $wpdb->prefix."posts";
	$data = array( 
'ID'	=>NULL,
'post_author'	=> $user_id,
'post_date'	=> current_time('mysql'),
'post_date_gmt'	=> current_time('mysql',1),
'post_content'	=> '',
'post_title'	=> 'Theme Video Gallery',
'post_excerpt'	=>'',
'post_status'	=>'publish',
'comment_status'	=>'open',
'ping_status'	=>'closed',
'post_password'	=>'',
'post_name'	=> 'theme-video-gallery-'.$user_id,
'to_ping'	=>'',
'pinged'	=>'',
'post_modified'	=> current_time('mysql'),
'post_modified_gmt'	=> current_time('mysql',1),
'post_content_filtered'	=>'',
'post_parent'	=>'',
'guid'	=> 'http://mediapress/wall-video-gallery',
'menu_order'	=> 0,
'post_type'	=>'mpp-gallery',
'post_mime_type'	=>'',
'comment_count'	=> 0,
		);
	$wpdb->insert( $table, $data);
	$post_id = $wpdb->insert_id;

	add_post_meta( $post_id, '_mpp_component_id', $user_id );
	
	$table_term = $wpdb->prefix."term_relationships";
	$term_id = sabai_mediapress_find_term_id('_followersonly');
	$data_term = array( 
'object_id'	=> $post_id,
'term_taxonomy_id'	=> $term_id,
'term_order'	=> 0,
		);
	$wpdb->insert( $table_term, $data_term);
	
	$term_id = sabai_mediapress_find_term_id('_doc');

	$data_term = array( 
'object_id'	=> $post_id,
'term_taxonomy_id'	=> $term_id,
'term_order'	=> 0,
		);
	$wpdb->insert( $table_term, $data_term);
	
	$term_id = sabai_mediapress_find_term_id('_video');

	$data_term = array( 
'object_id'	=> $post_id,
'term_taxonomy_id'	=> $term_id,
'term_order'	=> 0,
		);
	$wpdb->insert( $table_term, $data_term);
	
	return $post_id;
}

function sabai_mediapress_directory_link ($user_id, $entity_id) {
	global $wpdb;	
	//get entity data
	$table_name = $wpdb->prefix . 'sabai_entity_field_field_videofile';
	$query = 'SELECT * FROM ' . $table_name .
		' WHERE entity_id=' . $entity_id;
	//echo 'Query: ' . $query . "\n";
	$entity_query = $wpdb->get_results( $query );
	if(!count($entity_query)) return;
	

$gallery_id = sabai_mediapress_directory_find_gallery( $user_id );

	foreach ($entity_query as $entity) {
		$table_name = $wpdb->prefix . 'sabai_file_file';
		$query='SELECT * FROM '.$table_name.' WHERE file_id= '.$entity->file_id;
		$file_query = $wpdb->get_results( $query );
		
		$file_title = sanitize_title($file_query[0]->file_title);
		
$source_file = ABSPATH .'/wp-content/sabai/File/files/'.$file_query[0]->file_name;
$des_file = ABSPATH .'/wp-content/uploads/mediapress/member/'.$user_id.'/Theme/'.$file_query[0]->file_name;
if (!file_exists(ABSPATH .'/wp-content/uploads/mediapress/member/'.$user_id.'/Theme/')) {
    mkdir(ABSPATH .'/wp-content/uploads/mediapress/member/'.$user_id.'/Theme/', 0777, true);
}
if (!copy($source_file, $des_file)) {
    //echo "failed to copy\n";
}

//create new mediapress post
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
'comment_status'	=>'open',
'ping_status'	=>'closed',
'post_password'	=>'',
'post_name'	=> 'mediapress'.$entity_id.$file_title,
'to_ping'	=>'',
'pinged'	=>'',
'post_modified'	=> current_time('mysql'),
'post_modified_gmt'	=> current_time('mysql',1),
'post_content_filtered'	=>'',
'post_parent'	=> $gallery_id,
'guid'	=> get_site_url().'/wp-content/uploads/mediapress/member/'.$user_id.'/Theme/'.$file_query[0]->file_name,
'menu_order'	=>'',
'post_type'	=>'attachment',
'post_mime_type'	=> $file_query[0]->file_type,
'comment_count'	=> '',
		);
	$wpdb->insert( $table, $data);
	$attached_id = $wpdb->insert_id;
		
$metadata = array (
		'filesize' => $file_query[0]->file_size,
		'mime_type' => $file_query[0]->file_type,
		'length' => '',
		'length_formatted' => '',
		'width' => $file_query[0]->file_width,
		'height' => $file_query[0]->file_height,
		'fileformat' => $file_query[0]->file_extension,
		'dataformat' => '',
		'audio' => '',
		);
	add_post_meta( $attached_id, '_wp_attached_file', 'mediapress/member/'.$user_id.'/Theme/'.$file_query[0]->file_name );
	add_post_meta( $attached_id, '_mpp_component_id', $user_id );
	add_post_meta( $attached_id, '_mpp_is_mpp_media', 1 );
	add_post_meta( $attached_id, '_wp_attachment_metadata', $metadata );
	
	$table_term = $wpdb->prefix."term_relationships";
	$term_id = sabai_mediapress_find_term_id('_members');

	$data_term = array( 
'object_id'	=> $attached_id,
'term_taxonomy_id'	=> $term_id,
'term_order'	=> 0,
		);
	$wpdb->insert( $table_term, $data_term);
	
	$term_id = sabai_mediapress_find_term_id('_public');

	$data_term = array( 
'object_id'	=> $attached_id,
'term_taxonomy_id'	=> $term_id,
'term_order'	=> 0,
		);
	$wpdb->insert( $table_term, $data_term);
	
	$term_id = sabai_mediapress_find_term_id('_video');

	$data_term = array( 
'object_id'	=> $attached_id,
'term_taxonomy_id'	=> $term_id,
'term_order'	=> 0,
		);
	$wpdb->insert( $table_term, $data_term);
	}
}


function sabai_mediapress_questions_find_gallery ( $user_id ) {
	global $wpdb;	
	$gallery_id = 0;
	
	$table_name = $wpdb->prefix . 'posts';
	$query = 'SELECT * FROM ' . $table_name .
		' WHERE post_author =' . $user_id . ' AND post_title=\'Question Video Gallery\'' ;

	$gallery_query = $wpdb->get_results( $query );

	if(!count($gallery_query)) $gallery_id = sabai_mediapress_questions_create_gallery ( $user_id );
	else $gallery_id = $gallery_query[0]->ID;
	return $gallery_id;
}

function sabai_mediapress_questions_create_gallery ( $user_id ) {
	global $wpdb;	
	
//create new theme gallery post
	$table = $wpdb->prefix."posts";
	$data = array( 
'ID'	=>NULL,
'post_author'	=> $user_id,
'post_date'	=> current_time('mysql'),
'post_date_gmt'	=> current_time('mysql',1),
'post_content'	=> '',
'post_title'	=> 'Question Video Gallery',
'post_excerpt'	=>'',
'post_status'	=>'publish',
'comment_status'	=>'open',
'ping_status'	=>'closed',
'post_password'	=>'',
'post_name'	=> 'question-video-gallery-'.$user_id,
'to_ping'	=>'',
'pinged'	=>'',
'post_modified'	=> current_time('mysql'),
'post_modified_gmt'	=> current_time('mysql',1),
'post_content_filtered'	=>'',
'post_parent'	=>'',
'guid'	=> 'http://mediapress/wall-video-gallery',
'menu_order'	=> 0,
'post_type'	=>'mpp-gallery',
'post_mime_type'	=>'',
'comment_count'	=> 0,
		);
	$wpdb->insert( $table, $data);
	$post_id = $wpdb->insert_id;

	add_post_meta( $post_id, '_mpp_component_id', $user_id );
	
	$table_term = $wpdb->prefix."term_relationships";
	$term_id = sabai_mediapress_find_term_id('_followersonly');

	$data_term = array( 
'object_id'	=> $post_id,
'term_taxonomy_id'	=> $term_id,
'term_order'	=> 0,
		);
	$wpdb->insert( $table_term, $data_term);
	
	$term_id = sabai_mediapress_find_term_id('_doc');

	$data_term = array( 
'object_id'	=> $post_id,
'term_taxonomy_id'	=> $term_id,
'term_order'	=> 0,
		);
	$wpdb->insert( $table_term, $data_term);
	
	$term_id = sabai_mediapress_find_term_id('_video');

	$data_term = array( 
'object_id'	=> $post_id,
'term_taxonomy_id'	=> $term_id,
'term_order'	=> 0,
		);
	$wpdb->insert( $table_term, $data_term);
	
	return $post_id;
}

function sabai_mediapress_questions_link ($user_id, $entity_id) {
	global $wpdb;	
	//get entity data
	$table_name = $wpdb->prefix . 'sabai_entity_field_field_videofile';
	$query = 'SELECT * FROM ' . $table_name .
		' WHERE entity_id=' . $entity_id;
	//echo 'Query: ' . $query . "\n";
	$entity_query = $wpdb->get_results( $query );
	if(!count($entity_query)) return;
	

$gallery_id = sabai_mediapress_questions_find_gallery( $user_id );

	foreach ($entity_query as $entity) {
		$table_name = $wpdb->prefix . 'sabai_file_file';
		$query='SELECT * FROM '.$table_name.' WHERE file_id= '.$entity->file_id;
		$file_query = $wpdb->get_results( $query );
		
		$file_title = sanitize_title($file_query[0]->file_title);
		
$source_file = ABSPATH .'/wp-content/sabai/File/files/'.$file_query[0]->file_name;
$des_file = ABSPATH .'/wp-content/uploads/mediapress/member/'.$user_id.'/Question/'.$file_query[0]->file_name;
if (!file_exists(ABSPATH .'/wp-content/uploads/mediapress/member/'.$user_id.'/Question/')) {
    mkdir(ABSPATH .'/wp-content/uploads/mediapress/member/'.$user_id.'/Question/', 0777, true);
}
if (!copy($source_file, $des_file)) {
    //echo "failed to copy\n";
}

//create new mediapress post
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
'comment_status'	=>'open',
'ping_status'	=>'closed',
'post_password'	=>'',
'post_name'	=> 'mediapress'.$entity_id.$file_title,
'to_ping'	=>'',
'pinged'	=>'',
'post_modified'	=> current_time('mysql'),
'post_modified_gmt'	=> current_time('mysql',1),
'post_content_filtered'	=>'',
'post_parent'	=> $gallery_id,
'guid'	=> get_site_url().'/wp-content/uploads/mediapress/member/'.$user_id.'/Question/'.$file_query[0]->file_name,
'menu_order'	=>'',
'post_type'	=>'attachment',
'post_mime_type'	=> $file_query[0]->file_type,
'comment_count'	=> '',
		);
	$wpdb->insert( $table, $data);
	$attached_id = $wpdb->insert_id;
		
$metadata = array (
		'filesize' => $file_query[0]->file_size,
		'mime_type' => $file_query[0]->file_type,
		'length' => '',
		'length_formatted' => '',
		'width' => $file_query[0]->file_width,
		'height' => $file_query[0]->file_height,
		'fileformat' => $file_query[0]->file_extension,
		'dataformat' => '',
		'audio' => '',
		);
	add_post_meta( $attached_id, '_wp_attached_file', 'mediapress/member/'.$user_id.'/Question/'.$file_query[0]->file_name );
	add_post_meta( $attached_id, '_mpp_component_id', $user_id );
	add_post_meta( $attached_id, '_mpp_is_mpp_media', 1 );
	add_post_meta( $attached_id, '_wp_attachment_metadata', $metadata );
	
	$table_term = $wpdb->prefix."term_relationships";
	$term_id = sabai_mediapress_find_term_id('_members');

	$data_term = array( 
'object_id'	=> $attached_id,
'term_taxonomy_id'	=> $term_id,
'term_order'	=> 0,
		);
	$wpdb->insert( $table_term, $data_term);
	
	$term_id = sabai_mediapress_find_term_id('_public');

	$data_term = array( 
'object_id'	=> $attached_id,
'term_taxonomy_id'	=> $term_id,
'term_order'	=> 0,
		);
	$wpdb->insert( $table_term, $data_term);
	
	$term_id = sabai_mediapress_find_term_id('_video');

	$data_term = array( 
'object_id'	=> $attached_id,
'term_taxonomy_id'	=> $term_id,
'term_order'	=> 0,
		);
	$wpdb->insert( $table_term, $data_term);
	}
}

// define the bp_activity_after_save callback 
function action_bp_activity_after_save( $args ) { 
	
	if ($args->component != 'sabai-directory' && $args->component != 'sabai-discuss' ) return;
	if ($args->type == 'new_directory_listing_review' || $args->type == 'new_questions_answers') {
		$video_id = sabai_mediapress_activity_has_video ($args->secondary_item_id);
	
	}
	elseif ($args->type == 'new_directory_listing' || $args->type == 'new_questions') {
		$video_id = sabai_mediapress_activity_has_video ($args->item_id);
	
	}
	
	if( !$video_id) return;
	
	$content = $args->content;
	
	foreach ($video_id as $id ) {
		$string = sabai_mediapress_get_video_link ( $id );
		$content .= $string;
	}
	
	global $wpdb;
	$table_name = $wpdb->prefix . 'bp_activity';
	//$query='UPDATE '.$table_name.' SET content=\''.$content.'\' WHERE id='.$args->id;
	$wpdb->update( 
	$table_name, 
	array( 
		'content' => $content,	
	), 
	array( 'id' => $args->id ), 
	array( 
		'%s',	// value1
	), 
	array( '%d' ) 
	);
}; 
         
// add the action 
add_action( 'bp_activity_after_save', 'action_bp_activity_after_save', 10, 1 ); 


function sabai_mediapress_activity_has_video ( $entity_id ) {
	global $wpdb;
	$video_id = array();
	//get entity data
	$table_name = $wpdb->prefix . 'sabai_entity_field_field_videofile';
	$query = 'SELECT * FROM ' . $table_name .
		' WHERE entity_id=' . $entity_id;

	$entity_query = $wpdb->get_results( $query );
	if(!count($entity_query)) return $video_id;
	
	foreach ($entity_query as $entity) {
		$video_id[] = $entity->file_id;
	}
	
	return $video_id;
}

function sabai_mediapress_get_video_link ( $video_id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'sabai_file_file';
	$query='SELECT * FROM '.$table_name.' WHERE file_id= '.$video_id;

	$file_query = $wpdb->get_results( $query );
	if(!count($file_query)) return;
	
	$video_link = get_site_url().'/wp-content/sabai/File/files/'.$file_query[0]->file_name;
	/*
	$video_html = '<div style="width: 320px; " class="flowplayer functional play-button"><video controls="">'
		.'<source type="video/webm" src="'.$video_link.'">'
		.'<source type="video/mp4" src="'.$video_link.'">'
		.'</video></div>';
	*/
	$video_html = '<div class="sabai_mediapress_video" id=""><div class="'.$video_link.'"></div></div>';
	return $video_html;
}