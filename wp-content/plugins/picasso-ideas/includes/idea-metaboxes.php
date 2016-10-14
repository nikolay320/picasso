<?php

// metabox for post type idea
add_action('cmb2_init', 'pi_meta_box_attachments');

function pi_meta_box_attachments() {
	$locale = 'picasso-ideas';

	$cmb = new_cmb2_box( array(
		'id'           => 'idea-attachments',
		'title'        => __( 'Idea Attachments', $locale ),
		'object_types' => array( 'idea' ),
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
		'options'      => array(
			'add_upload_file_text' => __('Add or Upload Images', $locale),
		),
	) );

	$cmb->add_field( array(
		'name'         => __( 'Attachments', $locale ),
		'id'           => '_idea_files',
		'type'         => 'file_list',
		'preview_size' => array(150, 150),
		'options'      => array(
			'add_upload_file_text' => __('Add or Upload Attachments', $locale),
		),
	) );

	$cmb->add_field( array(
		'name'         => __( 'Videos', $locale ),
		'id'           => '_idea_videos',
		'type'         => 'file_list',
		'preview_size' => array(150, 150),
		'options'      => array(
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
add_action( 'cmb2_init', 'pi_meta_box_for_settings' );

function pi_meta_box_for_settings() {

	$meta_prefix = '_idea_';
	$locale = 'picasso-ideas';

	$cmb = new_cmb2_box( array(
		'id'           => 'idea-settings',
		'title'        => __( 'Idea Settings', $locale ),
		'object_types' => array( 'idea' ),
		'context'      => 'normal',
		'priority'     => 'high',
		'show_names'   => true,
		'cmb_styles'   => true,
	) );

	$cmb->add_field( array(
		'name'    => __( 'Status', $locale ),
		'id'      => '_idea_status',
		'type'    => 'select',
		'options' => pi_idea_status(),
	) );

	$cmb->add_field( array(
		'name'    => __( 'Campaign', $locale ),
		'id'      => '_idea_campaign',
		'type'    => 'select',
		'options' => pi_get_campaigns_for_cmb2_field(),
	) );

	$cmb->add_field( array(
		'name' => __( 'Disable User Reviews', $locale ),
		'desc' => __( 'Check it to disable user reviews.', $locale ),
		'id'   => '_idea_disable_user_reviews',
		'type' => 'checkbox',
	) );

	$cmb->add_field( array(
		'name'        => __( 'User Reviews Deadline', $locale ),
		'id'          => '_idea_user_reviews_deadline',
		'type'        => 'text_date',
		'date_format' => 'Y-m-d',
	) );

	$cmb->add_field( array(
		'name' => __( 'Disable Expert Reviews', $locale ),
		'desc' => __( 'Check it to disable expert reviews.', $locale ),
		'id'   => '_idea_disable_expert_reviews',
		'type' => 'checkbox',
	) );

	$cmb->add_field( array(
		'name'        => __( 'Expert Reviews Deadline', $locale ),
		'id'          => '_idea_expert_reviews_deadline',
		'type'        => 'text_date',
		'date_format' => 'Y-m-d',
	) );

	$cmb->add_field( array(
		'name' => __( 'Disable Idea Updates', $locale ),
		'desc' => __( 'Check it to disable idea updates.', $locale ),
		'id'   => '_idea_disable_idea_updates',
		'type' => 'checkbox',
	) );

	$cmb->add_field( array(
		'name' => __( 'Idea Owners', $locale ),
		'desc' => __( 'Assign idea owners who can post idea update.', $locale ),
		'id'   => '_idea_owners',
		'type' => 'users_with_avatar',
	) );

}
