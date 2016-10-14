<?php

// metabox for post type campaign
add_action('cmb2_init', 'pc_meta_box_attachments');

function pc_meta_box_attachments() {
	$locale = 'picasso-campaigns';

	$cmb = new_cmb2_box( array(
		'id'           => 'campaign-settings',
		'title'        => __( 'Campaign Settings', $locale ),
		'object_types' => array( 'campaign' ),
		'context'      => 'normal',
		'priority'     => 'high',
		'show_names'   => true,
		'cmb_styles'   => true,
	) );

	$cmb->add_field( array(
		'name'         => __( 'Image', $locale ),
		'id'           => '_campaign_image',
		'type'         => 'file',
		'preview_size' => array(150, 150),
		'options'         => array(
			'add_upload_file_text' => __('Add or Upload Image', $locale),
		),
	) );

	$cmb->add_field( array(
		'name' => __( 'Youtube Video', $locale ),
		'desc' => __( 'Enter a youtube video link.', $locale ),
		'id'   => '_campaign_youtube',
		'type' => 'text_url',
	) );

	$cmb->add_field( array(
		'name' => __( 'Campaign Deadline', $locale ),
		'id'   => '_campaign_deadline',
		'type' => 'text_datetime_timestamp',
	) );

	$cmb->add_field( array(
		'name' => __( 'Campaign Ideas', $locale ),
		'id'   => '_campaign_ideas',
		'type' => 'campaign_ideas',
	) );

	$cmb->add_field( array(
		'name'       => __( 'Campaign Criteria', $locale ),
		'id'         => '_campaign_criteria',
		'type'       => 'text',
		'repeatable' => true,
	) );

	$cmb->add_field( array(
		'name' => __( 'Enable idea votes for no-staus', $locale ),
		'desc' => __( 'Check it to enable.', $locale ),
		'id'   => '_campaign_enable_votes_for_no_status',
		'type' => 'checkbox',
	) );

	$cmb->add_field( array(
		'name'    => __( 'Change status to', $locale ),
		'desc'    => __( 'This will change the status of ideas those are under this campaign.', $locale ),
		'id'      => '_campaign_change_idea_staus_to',
		'type'    => 'select',
		'options' => pi_idea_status(),
	) );
}