<?php

function klc_idea_post_title($field_args, $field) {
	$post_id = $field->object_id;
	$idea = get_post($post_id);

	if (!$idea) {
		return '';
	}

	if ($idea->post_type !== 'ideas') {
		return '';
	}

	return $idea->post_title;
}

function klc_idea_post_content($field_args, $field) {
	$post_id = $field->object_id;
	$idea = get_post($post_id);

	if (!$idea) {
		return '';
	}

	if ($idea->post_type !== 'ideas') {
		return '';
	}

	return $idea->post_content;
}

// register fields for frontend submission
function klc_frontend_form_for_ideas() {
	$locale = 'ideas_plugin';

	$cmb = new_cmb2_box( array(
		'id'           => 'front-end-idea-form',
		'object_types' => array( 'ideas' ),
		'hookup'       => false,
		'save_fields'  => false,
		'cmb_styles'   => false,
	) );

	$cmb->add_field( array(
		'name'    => __( 'Idea Title', $locale ),
		'id'      => '_idea_post_title',
		'type'    => 'text',
		'default' => 'klc_idea_post_title',
	) );

	$cmb->add_field( array(
		'name'    => __( 'Idea Content', $locale ),
		'id'      => '_idea_post_content',
		'type'    => 'wysiwyg',
		'options' => array(
			'textarea_rows' => 12,
			'media_buttons' => false,
		),
		'default' => 'klc_idea_post_content',
	) );

	$cmb->add_field( array(
		'name'         => __( 'Image', $locale ),
		'id'           => '_idea_image',
		'type'         => 'file',
		'preview_size' => array(150, 150),
		'text'         => array(
			'add_upload_file_text' => __('Add or Upload Image', $locale),
		),
	) );

	$cmb->add_field( array(
		'name'         => __( 'Attachment', $locale ),
		'id'           => '_idea_file',
		'type'         => 'file',
		'preview_size' => array(150, 150),
		'text'         => array(
			'add_upload_file_text' => __('Add or Upload Attachment', $locale),
		),
	) );

	$cmb->add_field( array(
		'name'         => __( 'Video', $locale ),
		'id'           => '_idea_video',
		'type'         => 'file',
		'preview_size' => array(150, 150),
		'text'         => array(
			'add_upload_file_text' => __('Add or Upload Video', $locale),
		),
	) );

	$cmb->add_field( array(
		'name' => __( 'Youtube Video', $locale ),
		'desc' => __( 'Enter a youtube video link.', $locale ),
		'id'   => '_idea_youtube',
		'type' => 'text_url',
	) );
}
add_action( 'cmb2_init', 'klc_frontend_form_for_ideas' );

// handle the cmb-frontned-form shortcode
function klc_do_frontend_form_submission_shortcode($atts = array()) {
	$locale = 'ideas_plugin';
	$metabox_id = $atts['id'] ? $atts['id'] : '';
	$object_id = $atts['post_id'] == '0' ? 'fake' : $atts['post_id'];

	// get cmb2 metabox object
	$cmb = cmb2_get_metabox($metabox_id, $object_id);

	// get $cmb object_types
	// $post_types = $cmb->prop('object_types');

	// current user
	$user_id = get_current_user_id();

	// Parse attributes
	$atts = shortcode_atts(array(
		'metabox_id' => $metabox_id,
		'object_id'  => $object_id,
	), $atts, 'klc_frontend_idea_form');

	/*
	 * Let's add these attributes as hidden fields to our cmb form
	 * so that they will be passed through to our form submission
	 */
	// foreach ($atts as $key => $value) {
	// 	$cmb->add_hidden_field(array(
	// 		'field_args' => array(
	// 			'id'      => "atts[$key]",
	// 			'type'    => 'hidden',
	// 			'default' => $value,
	// 		),
	// 	));
	// }

	// initialize our output variable
	$output = '';

	$output .= cmb2_get_metabox_form($cmb, $object_id, array('save_button' => __('Add Idea', $locale)));

	return $output;
}
add_shortcode('klc_frontend_idea_form', 'klc_do_frontend_form_submission_shortcode');

// handles form submission
function klc_handle_frontend_idea_form() {
	if (empty($_POST) || !isset($_POST['submit-cmb'])) {
		return false;
	}

	$metabox_id = isset($_POST['atts']['metabox_id']) ? $_POST['atts']['metabox_id'] : '';
	$object_id = isset($_POST['atts']['object_id']) ? $_POST['atts']['object_id'] : '';

	// current user id
	$user_id = get_current_user_id();

	// post data
	$post_data = array(
		'post_title'   => sanitize_text_field($_POST['_idea_post_title']),
		'post_content' => $_POST['_idea_post_content'],
		'post_author'  => $user_id ? $user_id : 1, // Current user, or admin
		'post_status'  => 'publish',
		'post_type'    => 'ideas', // Only use first object_type in array
	);

	// post meta data
	$meta_data = array(
		'_idea_image'    => sanitize_text_field($_POST['_idea_image']),
		'_idea_image_id' => sanitize_text_field($_POST['_idea_image_id']),
		'_idea_file'     => sanitize_text_field($_POST['_idea_file']),
		'_idea_file_id'  => sanitize_text_field($_POST['_idea_file_id']),
		'_idea_video'    => sanitize_text_field($_POST['_idea_video']),
		'_idea_video_id' => sanitize_text_field($_POST['_idea_video_id']),
		'_idea_youtube'  => sanitize_text_field($_POST['_idea_youtube']),
	);

	// if idea found for given object id then update idea
	$idea = get_post($object_id);

	if ($idea && $idea->post_type === 'ideas') {
		$post_data['ID'] = $idea->ID;
		$idea_id = wp_update_post($post_data, true);
	}
	// create new one
	else {
		$idea_id = wp_insert_post($post_data, true);
	}

	// update post meta data
	if (!is_wp_error($idea_id) && $idea_id > 0) {
		foreach ($meta_data as $meta_key => $meta_value) {
			add_post_meta($idea_id, $meta_key, $meta_value, true)
			or
			update_post_meta($idea_id, $meta_key, $meta_value);
		}
	}
}
add_action('init', 'klc_handle_frontend_idea_form');