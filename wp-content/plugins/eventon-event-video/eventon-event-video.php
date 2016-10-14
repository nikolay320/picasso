<?php
/*
Plugin Name: EventON - Event Video

Plugin URI: EventON - Event Video

Description: EventON - Event Video

Version: 1.0.1

Author: phandung122

Author URI: http://www.upwork.com/o/profiles/users/_~016252273d5cf3683a/

License: 

*/


/**
 * Include CSS file for Multi address.
 */


//wp_enqueue_script( 'jquery', plugin_dir_url( __FILE__ ) . 'jquery-1.12.2.min.js');
//wp_enqueue_script( 'flowplayer', plugin_dir_url( __FILE__ ) . 'flowplayer/flowplayer.min.js');
//wp_enqueue_script("eventon-event-video", plugin_dir_url( __FILE__ ) . 'eventon-event-video.js'); 
wp_enqueue_style("eventon-event-video", plugin_dir_url( __FILE__ ) . 'style.css'); 
 

add_filter('evoau_form_fields', 'event_video_to_form', 10, 1);
function event_video_to_form($array){
	$array['eventvideo']=array('Event Video', 'eventvideo', 'eventvideo','custom','');
	return $array;
}
// only for frontend
if(!is_admin()){
	// actionUser intergration
	add_action('evoau_frontform_eventvideo',  'event_video_field', 10, 9);	
}
// Frontend showing fields and saving values  
function event_video_field($field, $event_id, $default_val, $EPMV, $opt2, $lang){
	
	$_EDITFORM = (isset($_REQUEST['action']) && $_REQUEST['action']=='edit' && !empty($event_id))? true:false;
	$VIDEO = 0;
	
	if($_EDITFORM){
		$VIDEO = get_post_meta($event_id,'event_video',true);
	}
	
	
	echo "<div class='row event_video'>
		<p class='label'><label for='event_video'>".eventon_get_custom_language($opt_2, 'evoAULvideo_header', 'Event Video', $lang)."</label></p>";
	if($_EDITFORM && $VIDEO){
		$video_link =wp_get_attachment_url( $VIDEO ) ;
		//$video_html = '<div class="evoau_video_preview evoau_img_preview"><div class="sabai_mediapress_video" id=""><div class="'.$video_link.'"></div></div>';
		$video_html = '<div class="evoau_video_preview evoau_img_preview"><div style="width: 100%; " class="flowplayer functional play-button"><video controls="">'
			.'<source type="video/webm" src="'.$video_link.'">'
			.'<source type="video/mp4" src="'.$video_link.'">'
			.'<source type="video/flv" src="'.$video_link.'">'
			.'</video></div>';
		$video_html .= "<span>".eventon_get_custom_language($opt_2, 'evoAULvideo_remove', 'Remove Video', $lang)."</span>"
			."<input type='hidden' name='event_video_exists' value='yes'/>"
			."</p></div>";
		
		echo $video_html;

	}
	echo "<p class='evoau_file_field' style='display:".($VIDEO?'none':'block')."'><span class='evoau_img_btn'>".eventon_get_custom_language($opt_2, 'evoAULvideo_select', 'Select a Video', $lang)."</span>
			<input style='opacity:0' type='file' accept='.mp4,.flv' id='my_video_upload' name='my_video_upload' multiple='false' data-text='".eventon_get_custom_language($opt_2, 'evoAULvideo_chosen', 'Video Chosen', $lang)."'/>";
			wp_nonce_field( 'my_video_upload', 'my_video_upload_nonce' );
		echo "</p>
	</div>";
	
}

add_action( 'save_post', 'event_video_save_values_events', 1, 2 );
function event_video_save_values_events($post_id, $post){ 
	if($post->post_type!='ajde_events')
		return;
	
	if(isset($_POST['event_video_exists']) && $_POST['event_video_exists']=='yes')
		return;
	
	if(isset($_POST['event_video_exists']) && $_POST['event_video_exists']=='no') {
		delete_post_meta($post_id, 'event_video');
	}
	
	// Check that the nonce is valid, and the user can edit this post.
	if ( 
		isset( $_POST['my_video_upload_nonce'], $post_id ) 
		&& wp_verify_nonce( $_POST['my_video_upload_nonce'], 'my_video_upload' )
		//&& current_user_can( 'edit_post', $post_id )
	) {	
		// These files need to be included as dependencies when on the front end.
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );
		
		// Let WordPress handle the upload.
		// Remember, 'my_video_upload' is the name of our file input in our form above.
		$attachment_id = media_handle_upload( 'my_video_upload', $post_id );
		
		if ( is_wp_error( $attachment_id ) ) {
			// error
			
		} else {
			$allowed_file_types = array( "video/mp4", "video/flv", "video/mpeg");
			$filetype = get_post_mime_type( $attachment_id );
			if (in_array( $filetype, $allowed_file_types )) {
				update_post_meta($post_id, 'event_video', $attachment_id);
			}
			
			$user_id = get_current_user_id();
			event_video_mediapress($user_id, $post_id);
			// The image was uploaded successfully!
		}
	
	} else {
		// The security check failed, maybe show the user an error.
	}
	
	
}

// only admin fields
if(is_admin()){
	add_filter('eventonau_language_fields', 'event_video_language', 10, 1);
}
// language
function event_video_language($array){
	$newarray = array(
		array('label'=>'ActionUser Event Video','type'=>'subheader'),
			array('label'=>'Event Video','name'=>'evoAULvideo_header'),	
			array('label'=>'Remove Video','name'=>'evoAULvideo_remove'),	
			array('label'=>'Select a Video','name'=>'evoAULvideo_select'),	
			array('label'=>'Video Chosen','name'=>'evoAULvideo_chosen'),				
		array('type'=>'togend'),
	);
	return array_merge($array, $newarray);
}



		// add eventon video event card field to filter
			function event_video_eventcard_array($array, $pmv, $eventid, $__repeatInterval){
				$array['event_video']= array(
					'event_id' => $eventid,
					'value'=>'tt',
					'__repeatInterval'=>(!empty($__repeatInterval)? $__repeatInterval:0)
				);
				return $array;
			}
			function event_video_eventcard_adds($array){
				$array[] = 'event_video';
				return $array;
			}
			
			
		add_filter('eventon_eventcard_array', 'event_video_eventcard_array', 10, 4);
		add_filter('evo_eventcard_adds', 'event_video_eventcard_adds', 10, 1);
		
		
		function event_video__add_toeventcard_order($array){
			$array['event_video']= array('event_video',__('Video Event','eventon'));
			return $array;
		}
			// eventCard inclusion
		add_filter( 'eventon_eventcard_boxes','event_video__add_toeventcard_order' , 10, 1);
		
add_action('eventon_eventCard_event_video',  'event_video_eventon_eventCard_event_video', 10, 9);	

function event_video_eventon_eventCard_event_video ($object, $helpers) {
	$event_id = $object->event_id;
	$VIDEO = get_post_meta($event_id,'event_video',true);
	$video_html = '';
	if ($VIDEO ){
	$video_link =wp_get_attachment_url( $VIDEO ) ;
	/*
	$video_html = '<div style="width: 100%; " class="flowplayer functional play-button"><video controls="">'
		.'<source type="video/webm" src="'.$video_link.'">'
		.'<source type="video/mp4" src="'.$video_link.'">'
		.'<source type="video/flv" src="'.$video_link.'">'
		.'</video></div>';
	*/
	$video_html = '<div class="sabai_mediapress_video" id=""><div class="'.$video_link.'"></div></div>';
	}
	$html = '<div class="evo_metarow_video">'. $video_html .'</div>';
	return $html;
}



// define the bp_activity_after_save callback 
function event_video_action_bp_activity_after_save( $args ) { 
	if ($args->component != 'activity' ) return;
	if ($args->type == 'activity_edit_event' || $args->type == 'activity_new_event') {
		$video_id = event_video_activity_has_video ($args->item_id);
		
	}
	
	if( !$video_id) return;
	
	$content = '';
	$activity_content = $args->content;

	$string = event_video_get_video_link ( $video_id );
	$content = $string.$activity_content;

	
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
add_action( 'bp_activity_after_save', 'event_video_action_bp_activity_after_save', 10, 1 ); 


function event_video_activity_has_video ( $event_id ) {
	$video_id = get_post_meta($event_id,'event_video',true);
	return $video_id;
}

function event_video_get_video_link ( $video_id ) {
	$video_link =wp_get_attachment_url( $video_id ) ;
	/*
	$video_html = '<div style="width: 100%; " class="flowplayer functional play-button"><video controls="">'
		.'<source type="video/webm" src="'.$video_link.'">'
		.'<source type="video/mp4" src="'.$video_link.'">'
		.'<source type="video/flv" src="'.$video_link.'">'
		.'</video></div>';
	}
	*/
	$video_html = '<div class="sabai_mediapress_video" id=""><div class="'.$video_link.'"></div></div>';
	return $video_html;
}

function event_video_mediapress ($user_id, $event_id) {
	global $wpdb;

$gallery_id = event_video_find_gallery( $user_id );
$video_id = event_video_activity_has_video ( $event_id );
$video_link = wp_get_attachment_url( $video_id );
		//$file_title = sanitize_title($file_query[0]->file_title);
$filename_only = basename( get_attached_file( $video_id ) ); // Just the file name
$source_file = $video_link;
//$source_file = ABSPATH .'/wp-content/uploads/2016/08/'.$filename_only;
$des_file = ABSPATH .'/wp-content/uploads/mediapress/member/'.$user_id.'/Event/'.$filename_only;
if (!file_exists(ABSPATH .'/wp-content/uploads/mediapress/member/'.$user_id.'/Event/')) {
    mkdir(ABSPATH .'/wp-content/uploads/mediapress/member/'.$user_id.'/Event/', 0777, true);
}
if (!copy($source_file, $des_file)) {
    //echo "failed to copy\n";
	return;
}

//create new mediapress post
$table = $wpdb->prefix."posts";
$data = array(
'ID'	=>NULL,
'post_author'	=> $user_id,
'post_date'	=> current_time('mysql'),
'post_date_gmt'	=> current_time('mysql',1),
'post_content'	=> '',
'post_title'	=> $filename_only,
'post_excerpt'	=>'',
'post_status'	=>'inherit',
'comment_status'	=>'open',
'ping_status'	=>'closed',
'post_password'	=>'',
'post_name'	=> 'mediapress'.$event_id.$filename_only,
'to_ping'	=>'',
'pinged'	=>'',
'post_modified'	=> current_time('mysql'),
'post_modified_gmt'	=> current_time('mysql',1),
'post_content_filtered'	=>'',
'post_parent'	=> $gallery_id,
'guid'	=> get_site_url().'/wp-content/uploads/mediapress/member/'.$user_id.'/Event/'.$filename_only,
'menu_order'	=>'',
'post_type'	=>'attachment',
'post_mime_type'	=> get_post_mime_type( $video_id ),
'comment_count'	=> '',
		);
	$wpdb->insert( $table, $data);
	$attached_id = $wpdb->insert_id;
		
$meta = wp_get_attachment_metadata( $video_id );

	add_post_meta( $attached_id, '_wp_attached_file', 'mediapress/member/'.$user_id.'/Event/'.$filename_only );
	add_post_meta( $attached_id, '_mpp_component_id', $user_id );
	add_post_meta( $attached_id, '_mpp_is_mpp_media', 1 );
	add_post_meta( $attached_id, '_wp_attachment_metadata', $meta );
	
	$table_term = $wpdb->prefix."term_relationships";
	$term_id = event_video_find_term_id('_members');

	$data_term = array( 
'object_id'	=> $attached_id,
'term_taxonomy_id'	=> $term_id,
'term_order'	=> 0,
		);
	$wpdb->insert( $table_term, $data_term);
	
	$term_id = event_video_find_term_id('_public');

	$data_term = array( 
'object_id'	=> $attached_id,
'term_taxonomy_id'	=> $term_id,
'term_order'	=> 0,
		);
	$wpdb->insert( $table_term, $data_term);
	
	$term_id = event_video_find_term_id('_video');

	$data_term = array( 
'object_id'	=> $attached_id,
'term_taxonomy_id'	=> $term_id,
'term_order'	=> 0,
		);
	$wpdb->insert( $table_term, $data_term);
	
}

function event_video_find_gallery ( $user_id ) {
	global $wpdb;	
	$gallery_id = 0;
	
	$table_name = $wpdb->prefix . 'posts';
	$query = 'SELECT * FROM ' . $table_name .
		' WHERE post_author =' . $user_id . ' AND post_title=\'Event Video Gallery\'' ;

	$gallery_query = $wpdb->get_results( $query );

	if(!count($gallery_query)) $gallery_id = event_video_create_gallery ( $user_id );
	else $gallery_id = $gallery_query[0]->ID;
	return $gallery_id;
}

function event_video_create_gallery ( $user_id ) {
	global $wpdb;	
	
//create new theme gallery post
	$table = $wpdb->prefix."posts";
	$data = array( 
'ID'	=>NULL,
'post_author'	=> $user_id,
'post_date'	=> current_time('mysql'),
'post_date_gmt'	=> current_time('mysql',1),
'post_content'	=> '',
'post_title'	=> 'Event Video Gallery',
'post_excerpt'	=>'',
'post_status'	=>'publish',
'comment_status'	=>'open',
'ping_status'	=>'closed',
'post_password'	=>'',
'post_name'	=> 'event-video-gallery-'.$user_id,
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
	$term_id = event_video_find_term_id('_followersonly');

	$data_term = array( 
'object_id'	=> $post_id,
'term_taxonomy_id'	=> $term_id,
'term_order'	=> 0,
		);
	$wpdb->insert( $table_term, $data_term);
	
	$term_id = event_video_find_term_id('_doc');

	$data_term = array( 
'object_id'	=> $post_id,
'term_taxonomy_id'	=> $term_id,
'term_order'	=> 0,
		);
	$wpdb->insert( $table_term, $data_term);
	
	$term_id = event_video_find_term_id('_video');

	$data_term = array( 
'object_id'	=> $post_id,
'term_taxonomy_id'	=> $term_id,
'term_order'	=> 0,
		);
	$wpdb->insert( $table_term, $data_term);
	
	return $post_id;
}


function event_video_find_term_id ($slug) {
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
 