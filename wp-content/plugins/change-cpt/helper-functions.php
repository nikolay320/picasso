<?php

/**
 * Update post_metas of ideas
 */
if (!function_exists('change_cpt_update_post_metas_of_ideas')) {
	function change_cpt_update_post_metas_of_ideas() {
		global $wpdb;

		$ideas = $wpdb->get_results("SELECT ID FROM $wpdb->posts WHERE post_type='ideas' ORDER BY comment_count DESC");

		if ($ideas) {
			foreach ($ideas as $idea) {
				$idea_id = $idea->ID;

				// update images
				$idea_image_id = get_post_meta($idea_id, 'idea_image_id', true);
				$idea_image_url = get_post_meta($idea_id, 'idea_image_url', true);

				if ($idea_image_id && $idea_image_url && !is_array($idea_image_url)) {

					$idea_images = array($idea_image_id => $idea_image_url);
					update_post_meta($idea_id, '_idea_images', $idea_images);

				}

				// update files
				$idea_file_id = get_post_meta($idea_id, 'idea_file_id', true);
				$idea_file_url = get_post_meta($idea_id, 'idea_file_url', true);

				if ($idea_file_id && $idea_file_url && !is_array($idea_file_url)) {

					$idea_files = array($idea_file_id => $idea_file_url);
					update_post_meta($idea_id, '_idea_files', $idea_files);

				}

				// update files
				$_idea_file_id = get_post_meta($idea_id, '_idea_file_id', true);
				$_idea_file = get_post_meta($idea_id, '_idea_file', true);

				if ($_idea_file_id && $_idea_file && !is_array($_idea_file)) {

					$idea_files = array($_idea_file_id => $_idea_file);
					update_post_meta($idea_id, '_idea_files', $idea_files);

				}

				// update videos
				$_idea_video_id = get_post_meta($idea_id, '_idea_video_id', true);
				$_idea_video = get_post_meta($idea_id, '_idea_video', true);

				if ($_idea_video_id && $_idea_video && !is_array($_idea_video)) {

					$_idea_videos = array($_idea_video_id => $_idea_video);
					update_post_meta($idea_id, '_idea_videos', $_idea_videos);

				}

				// update videos
				$idea_video_id = get_post_meta($idea_id, 'idea_video_id', true);
				$idea_video_url = get_post_meta($idea_id, 'idea_video_url', true);

				if ($idea_video_id && $idea_video_url && !is_array($idea_video_url)) {

					$_idea_videos = array($idea_video_id => $idea_video_url);
					update_post_meta($idea_id, '_idea_videos', $_idea_videos);

				}

				// update youtube video
				$idea_youtube = get_post_meta($idea_id, 'idea_youtube', true);

				if ($idea_youtube) {

					update_post_meta($idea_id, '_idea_youtube', $idea_youtube);

				}

				// update views_count
				$views_count = get_post_meta($idea_id, 'views_count', true);

				if ($views_count) {

					update_post_meta($idea_id, '_views_count', $views_count);

				}

				// update idea_campaign
				$idea_campaign = get_post_meta($idea_id, 'idea_campaign', true);

				if ($idea_campaign) {

					update_post_meta($idea_id, '_idea_campaign', $idea_campaign);

				}
			}
		}

	}
}

/**
 * Delete old post_metas of ideas
 */
if (!function_exists('change_cpt_delete_old_post_metas_of_ideas')) {
	function change_cpt_delete_old_post_metas_of_ideas() {
		global $wpdb;

		$ideas = $wpdb->get_results("SELECT ID FROM $wpdb->posts WHERE post_type='ideas' ORDER BY comment_count DESC");

		if ($ideas) {
			foreach ($ideas as $idea) {
				$idea_id = $idea->ID;

				delete_post_meta($idea_id, 'idea_image_id');
				delete_post_meta($idea_id, 'idea_image_url');
				delete_post_meta($idea_id, 'idea_file_id');
				delete_post_meta($idea_id, 'idea_file_url');
				delete_post_meta($idea_id, '_idea_file_id');
				delete_post_meta($idea_id, '_idea_file');
				delete_post_meta($idea_id, '_idea_video_id');
				delete_post_meta($idea_id, '_idea_video');
				delete_post_meta($idea_id, 'idea_video_id');
				delete_post_meta($idea_id, 'idea_video_url');
				delete_post_meta($idea_id, 'idea_youtube');
				delete_post_meta($idea_id, 'views_count');
				delete_post_meta($idea_id, 'idea_campaign');
				delete_post_meta($idea_id, 'idea_status');
			}
		}

	}
}

/**
 * Change ideas to idea
 */
if (!function_exists('change_cpt_change_ideas_to_idea')) {
	function change_cpt_change_ideas_to_idea() {
		global $wpdb;
		$change = $wpdb->update($wpdb->posts, array("post_type" => "idea"), array("post_type" => 'ideas'));
		return $change;
	}
}

/**
 * Update post_metas of campaings
 */
if (!function_exists('change_cpt_update_post_metas_of_campaigns')) {
	function change_cpt_update_post_metas_of_campaigns() {
		global $wpdb;

		$campaigns = $wpdb->get_results("SELECT ID FROM $wpdb->posts WHERE post_type='campaigns' ORDER BY comment_count DESC");

		if ($campaigns) {
			foreach ($campaigns as $campaign) {
				$campaign_id = $campaign->ID;

				// update image
				$campaign_image_id = get_post_meta($campaign_id, 'campaign_image_id', true);
				$campaign_image_url = get_post_meta($campaign_id, 'campaign_image_url', true);

				if ($campaign_image_id && $campaign_image_url) {

					update_post_meta($campaign_id, '_campaign_image_id', $campaign_image_id);
					update_post_meta($campaign_id, '_campaign_image', $campaign_image_url);

				}

				// update campaign youtube video
				$campaign_video_url = get_post_meta($campaign_id, 'campaign_video_url', true);

				if ($campaign_video_url) {

					update_post_meta($campaign_id, '_campaign_youtube', $campaign_video_url);

				}

				// update campaign deadline
				$campaign_end_date = get_post_meta($campaign_id, 'campaign_end_date', true);

				if ($campaign_end_date) {

					$campaign_end_date = strtotime($campaign_end_date);

					update_post_meta($campaign_id, '_campaign_deadline', $campaign_end_date);

				}

				// update campaign ideas
				$campaign_ideas = get_post_meta($campaign_id, 'campaign_ideas', false);

				if ($campaign_ideas) {

					foreach ($campaign_ideas as $idea_id) {
						delete_post_meta($campaign_id, '_campaign_ideas', $idea_id);
						add_post_meta($campaign_id, '_campaign_ideas', $idea_id);
					}

				}
			}
		}

	}
}

if (!function_exists('change_cpt_delete_old_post_metas_of_campaigns')) {
	function change_cpt_delete_old_post_metas_of_campaigns() {
		global $wpdb;

		$campaigns = $wpdb->get_results("SELECT ID FROM $wpdb->posts WHERE post_type='campaigns' ORDER BY comment_count DESC");

		if ($campaigns) {
			foreach ($campaigns as $campaign) {
				$campaign_id = $campaign->ID;

				delete_post_meta($campaign_id, 'campaign_image_id');
				delete_post_meta($campaign_id, 'campaign_image_url');
				delete_post_meta($campaign_id, 'campaign_video_url');
				delete_post_meta($campaign_id, 'campaign_end_date');
				delete_post_meta($campaign_id, 'campaign_ideas');
				delete_post_meta($campaign_id, '');
			}
		}
	}
}

/**
 * Change campaigns to campaign
 */
if (!function_exists('change_cpt_change_campaigns_to_campaign')) {
	function change_cpt_change_campaigns_to_campaign() {
		global $wpdb;
		$change = $wpdb->update($wpdb->posts, array("post_type" => "campaign"), array("post_type" => 'campaigns'));
		return $change;
	}
}

/**
 * Trigger actions
 */
if (!function_exists('change_cpt_handle_actions')) {
	function change_cpt_handle_actions() {
		// trigger actions
		if (empty($_POST['change_cpt'])) {
			return;
		}

		if (empty($_POST['action'])) {
			return;
		}

		$action = $_POST['action'];

		// Update post metas of ideas
		if ($action === 'update_post_metas_of_ideas') {
			change_cpt_update_post_metas_of_ideas();
		}

		// Delete old post metas of ideas
		elseif ($action === 'delete_post_metas_of_ideas') {
			change_cpt_delete_old_post_metas_of_ideas();
		}

		// Update post metas of campaigns
		elseif ($action === 'update_post_metas_of_campaigns') {
			change_cpt_update_post_metas_of_campaigns();
		}

		// Delete old post metas of campaigns
		elseif ($action === 'delete_post_metas_of_campaigns') {
			change_cpt_delete_old_post_metas_of_campaigns();
		}

		// Change ideas to idea
		elseif ($action === 'change_ideas_to_idea') {
			change_cpt_change_ideas_to_idea();
		}

		// Change campaigns to campaign
		elseif ($action === 'change_campaigns_to_campaign') {
			change_cpt_change_campaigns_to_campaign();
		}

		// Redirect to avoid multiple submissions
		$redirect_to = add_query_arg('updated', 'true', admin_url('admin.php?page=changecpt'));
		wp_redirect($redirect_to);
		exit;

	}
	add_action('init', 'change_cpt_handle_actions');
}