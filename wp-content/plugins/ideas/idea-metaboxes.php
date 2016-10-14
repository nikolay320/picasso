<?php

// metabox for post type idea
add_action('cmb2_init', 'klc_idea_meta_box_attachments');

function klc_idea_meta_box_attachments() {
	$meta_prefix = '_idea_';
	$locale = 'ideas_plugin';

	$cmb = new_cmb2_box( array(
		'id'           => $meta_prefix . 'cmb2_metaboxes_idea_attachments',
		'title'        => __( 'Idea Attachments', $locale ),
		'object_types' => array( 'ideas' ),
		'context'      => 'normal',
		'priority'     => 'high',
		'show_names'   => true,
		'cmb_styles'   => true,
	) );

	$cmb->add_field( array(
		'name'         => __( 'Images', $locale ),
		'id'           => '_idea_images',
		'type'         => 'file_list',
		'preview_size' => array(150, 150),
		'query_args'   => array(
			'post_author' => 00,
		),
		'text'         => array(
			'add_upload_file_text' => __('Add or Upload Images', $locale),
		),
		'allow' => array( 'url', 'attachment' ),
		'attributes' => array(
		    'accept' => 'image/*',
		),
	) );

	$cmb->add_field( array(
		'name'         => __( 'Attachments', $locale ),
		'id'           => '_idea_files',
		'type'         => 'file_list',
		'preview_size' => array(150, 150),
		'text'         => array(
			'add_upload_file_text' => __('Add or Upload Attachments', $locale),
		),
	) );

	$cmb->add_field( array(
		'name'         => __( 'Videos', $locale ),
		'id'           => '_idea_videos',
		'type'         => 'file_list',
		'preview_size' => array(150, 150),
		'text'         => array(
			'add_upload_file_text' => __('Add or Upload Videos', $locale),
		),
	) );

	$cmb->add_field( array(
		'name' => __( 'Youtube Video', $locale ),
		'desc' => __( 'Enter a youtube video link.', $locale ),
		'id'   => '_idea_youtube',
		'type' => 'text_url',
	) );
}

// metabox for post type idea
add_action('cmb2_init', 'klc_idea_meta_box_for_status');

function klc_idea_meta_box_for_status() {
	$meta_prefix = '_idea_';
	$locale = 'ideas_plugin';

	$cmb = new_cmb2_box( array(
		'id'           => $meta_prefix . 'cmb2_metaboxes_idea_status',
		'title'        => __( 'Idea Status', $locale ),
		'object_types' => array( 'ideas' ),
		'context'      => 'normal',
		'priority'     => 'high',
		'show_names'   => true,
		'cmb_styles'   => true,
	) );

	$cmb->add_field( array(
		'name'             => __( 'Status', $locale ),
		'id'               => 'idea_status',
		'type'             => 'select',
		'show_option_none' => true,
		// 'default'          => '',
		'options'          => array(
			'in discussion'    => __('Idea in discussion', IDEAS_TEXT_DOMAIN),
			'selected'         => __('Idea selected', IDEAS_TEXT_DOMAIN),
			'rejected'         => __('Idea rejected', IDEAS_TEXT_DOMAIN),
			'in project'       => __('Idea in project', IDEAS_TEXT_DOMAIN),
			'in review'        => __('Idea in review', IDEAS_TEXT_DOMAIN),
			'already reviewed' => __('Idea already reviewed', IDEAS_TEXT_DOMAIN),
		),
	) );
}

// metabox for post type idea
add_action( 'cmb2_init', 'klc_idea_meta_box_for_settings' );

function klc_idea_meta_box_for_settings() {

	$meta_prefix = '_idea_';
	$locale = 'ideas_plugin';

	$cmb = new_cmb2_box( array(
		'id'           => $meta_prefix . 'cmb2_metaboxes',
		'title'        => __( 'Idea Settings', $locale ),
		'object_types' => array( 'ideas' ),
		'context'      => 'normal',
		'priority'     => 'high',
		'show_names'   => true,
		'cmb_styles'   => true,
	) );

	$cmb->add_field( array(
		'name'    => __( 'Campaign', $locale ),
		'id'      => 'idea_campaign',
		'type'    => 'select',
		'options' => klc_get_campaigns(),
	) );

	$cmb->add_field( array(
		'name'    => __( 'Enable User Reviews', $locale ),
		'desc'    => __( 'Check it to enable user reviews.', $locale ),
		'id'      => $meta_prefix . 'enable_user_reviews',
		'type'    => 'checkbox',
	) );

	$cmb->add_field( array(
		'name'        => __( 'User Reviews Deadline', $locale ),
		'id'          => $meta_prefix . 'user_reviews_deadline',
		'type'        => 'text_date',
		'date_format' => 'Y-m-d',
	) );

	$cmb->add_field( array(
		'name'    => __( 'Enable Expert Reviews', $locale ),
		'desc'    => __( 'Check it to enable expert reviews.', $locale ),
		'id'      => $meta_prefix . 'enable_expert_reviews',
		'type'    => 'checkbox',
	) );

	$cmb->add_field( array(
		'name'    => __( 'Enable Idea Updates', $locale ),
		'desc'    => __( 'Check it to enable idea updates.', $locale ),
		'id'      => $meta_prefix . 'enable_idea_updates',
		'type'    => 'checkbox',
	) );

	$cmb->add_field( array(
		'name'    => __( 'Idea Owners', $locale ),
		'desc'    => __( 'Assign idea owners who can post idea update.', $locale ),
		'id'      => $meta_prefix . 'owners',
		'type'    => 'users_with_avatar',
	) );

}