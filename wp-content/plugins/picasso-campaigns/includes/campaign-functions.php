<?php
/**
 * Save custom post meta field '_campaign_ideas'
 */
if (!function_exists('cmb2_save__campaign_ideas')) {
	function cmb2_save__campaign_ideas($override, $args, $field_args, CMB2_Field $field) {
		$args = (object)$args;
		$campaign_id = $args->id;
		$ideas_id = $args->value;

		if ($ideas_id) {
			foreach ($ideas_id as $idea_id) {
				$prev_campaign_id = get_post_meta($idea_id, '_idea_campaign', true);
				delete_post_meta($idea_id, '_idea_campaign');
				delete_post_meta($prev_campaign_id, '_campaign_ideas', $idea_id);
			}
		}

		// Reattach
		if ($ideas_id) {
			foreach ($ideas_id as $idea_id) {
				add_post_meta($campaign_id, '_campaign_ideas', $idea_id);
				add_post_meta($idea_id, '_idea_campaign', $campaign_id);
			}
		}

		// do override, do not save the meta
		return true;
	}
	add_action('cmb2_override__campaign_ideas_meta_save', 'cmb2_save__campaign_ideas', 10, 4);
}

/**
 * Save custom post meta field '_campaign_change_idea_staus_to'
 */
if (!function_exists('cmb2_save__campaign_change_idea_staus_to')) {
	function cmb2_save__campaign_change_idea_staus_to($override, $args, $field_args, CMB2_Field $field) {
		$args = (object)$args;
		$campaign_id = $args->id;
		$status = $args->value;

		$ideas = get_post_meta($campaign_id, '_campaign_ideas', false);

		if ($ideas) {
			foreach ($ideas as $idea_id) {
				update_post_meta($idea_id, '_idea_status', $status);
			}
		}
	}
	add_action('cmb2_override__campaign_change_idea_staus_to_meta_save', 'cmb2_save__campaign_change_idea_staus_to', 10, 4);
}