<?php
/**
* Picasso_Ideas_Review class
*/
class Picasso_Ideas_Review {
	public function __construct() {
		// add idea expert
		add_action('init', array($this, 'add_idea_expert'));

		// add idea expert
		add_action('init', array($this, 'remove_idea_expert'));

		// post idea update
		add_action('init', array($this, 'post_idea_update'));

		// post review
		add_action('init', array($this, 'post_review'));
	}

	/**
	 * Add expert, update deadline from frontend
	 */
	public function add_idea_expert() {
		if (!isset($_POST['add_idea_expert'])) {
			return;
		}

		// Verify nonce
		if (!wp_verify_nonce($_POST['_wpnonce'], 'add_idea_expert_nonce')) {
			wp_die(__('Nonce mismatched', 'picasso-ideas'));
		}

		// Check if logged in user have the permission to assign expert
		if (!pi_ideas_modifier()) {
			wp_die(__('You can\'t assign expert', 'picasso-ideas'));
		}

		$idea_id = (isset($_POST['idea_id'])) ? intval($_POST['idea_id']) : '';
		$expert_id = (isset($_POST['expert_id'])) ? intval($_POST['expert_id']) : '';
		$deadline = (isset($_POST['deadline'])) ? sanitize_text_field($_POST['deadline']) : '';
		$campaign_id = (isset($_POST['campaign_id'])) ? intval($_POST['campaign_id']) : '';
		$new_expert = false;

		if (!$idea_id) {
			wp_die(__('Idea id is required', 'picasso-ideas'));
		}

		if (!$deadline) {
			wp_die(__('Deadline is required', 'picasso-ideas'));
		}

		if ($expert_id) {
			// check if review already exists
			$args = array(
				'post_type'       => 'idea_review',
				'post_status'     => 'publish',
				'posts_per_page'  => -1,
				'fields'          => 'ids',
				'meta_query'      => array(
					'relation' => 'AND',
					array(
					    'key'     => '_idea_id',
					    'value'   => $idea_id,
					    'compare' => '=',
					),
					array(
					    'key'     => '_expert_id',
					    'value'   => $expert_id,
					    'compare' => '=',
					),
				),
			);

			$review = get_posts($args);

			$review_data = array(
				'post_type'      => 'idea_review',
				'post_title'     => 'Review # idea: ' . $idea_id . ' expert: ' . $expert_id,
				'post_status'    => 'publish',
				'post_author'    => get_current_user_id(),
			);

			$review_meta_data = array(
				'_idea_id'       => $idea_id,
				'_expert_id'     => $expert_id,
			);

			// update review
			if ($review) {
				$review_data['ID'] = $review[0];
				$review_id = wp_update_post($review_data, true);
			}
			// create a new one
			else {
				$review_id = wp_insert_post($review_data, true);
				$new_expert = true;
			}

			if (is_wp_error($review_id)) {
				wp_die($review_id->get_error_message());
			}

			foreach ($review_meta_data as $meta_key => $meta_value) {
				add_post_meta($review_id, $meta_key, $meta_value, true)
				or
				update_post_meta($review_id, $meta_key, $meta_value);
			}
		}

		// add or update deadline
		add_post_meta($idea_id, '_idea_expert_reviews_deadline', $deadline, true)
		or
		update_post_meta($idea_id, '_idea_expert_reviews_deadline', $deadline);

		// if this is new expert then send the notification mail
		if ($new_expert) {

			// send mail
			$user_data = get_userdata($expert_id);
			$to = $user_data->user_email;
			$subject = sprintf(__('[%s] You are an expert', 'picasso-ideas'), get_bloginfo());

			$modifier_id = get_current_user_id();
			$modifier_data = get_userdata($modifier_id);
			$modifier_display_name = $modifier_data->display_name;

			if (function_exists('bp_core_get_userlink')) {
				$modifier_link =  bp_core_get_userlink($modifier_id);
			} else {
				$modifier_link = '<a href="' . get_author_posts_url($modifier_id) . '">' . $modifier_display_name . '</a>';
			}

			$idea_link = '<a href="' . get_the_permalink($idea_id) . '#expert-reviews">' . get_the_title($idea_id) . '</a>';

			if ($campaign_id) {
				$campaign_link = '<a href="' . get_the_permalink($campaign_id) . '">' . get_the_title($campaign_id) . '</a>';
			} else {
				$campaign_link = __('(no campaign assigned)', 'picasso-ideas');
			}

			$body = '';
			$body .= sprintf(__('Hello,', 'picasso-ideas')) . "\n\n";
			$body .= sprintf(__('You have been invited by %1$s to review the %2$s in the challenge %3$s.', 'picasso-ideas'), $modifier_link, $idea_link, $campaign_link) . "\n";
			$body .= sprintf(__('You have until %1$s to review the idea.', 'picasso-ideas'), $deadline) . "\n";
			$body .= sprintf(__('Your individual review and grades will only be seen by admin and your inviter.', 'picasso-ideas')) . "\n\n";
			$body .= sprintf(__('Thanks for support!', 'picasso-ideas'));

			wp_mail($to, $subject, $body);
		}

		// success message
		if ($expert_id) {
			$message = __('Expert added successfully', 'picasso-ideas');
		} else {
			$message = __('Idea deadline updated successfully', 'picasso-ideas');
		}

		// Store success message in session
		Picasso_Ideas_Session::put('pi_success_message', $message);

		// Redirect
		$redirect_to = add_query_arg('add-experts', '', get_the_permalink($idea_id));
		pi_redirect_to($redirect_to);
	}

	/**
	 * Remove idea expert
	 */
	public function remove_idea_expert() {
		if (!isset($_GET['action'])) {
			return;
		}

		if ($_GET['action'] !== 'remove-idea-expert') {
			return;
		}

		// Verify nonce
		if (!wp_verify_nonce($_GET['_wpnonce'], 'remove_expert_nonce')) {
			wp_die(__('Nonce mismatched', 'picasso-ideas'));
		}

		// Check if logged in user have the permission to remove expert
		if (!pi_ideas_modifier()) {
			wp_die(__('You can\'t remove expert', 'picasso-ideas'));
		}

		$idea_id = isset($_GET['idea_id']) ? intval($_GET['idea_id']) : '';
		$expert_id = isset($_GET['expert_id']) ? intval($_GET['expert_id']) : '';

		if (!$idea_id) {
			wp_die(__('Idea id is required', 'picasso-ideas'));
		}

		if (!$expert_id) {
			wp_die(__('Expert id is required', 'picasso-ideas'));
		}

		// check if review found
		$args = array(
			'post_type'        => 'idea_review',
			'post_status'      => 'publish',
			'posts_per_page'   => -1,
			'fields'           => 'ids',
			'meta_query'       => array(
				'relation' => 'AND',
				array(
				    'key'     => '_idea_id',
				    'value'   => $idea_id,
				    'compare' => '=',
				),
				array(
				    'key'     => '_expert_id',
				    'value'   => $expert_id,
				    'compare' => '=',
				),
			),
		);

		$review = get_posts($args);

		if (!$review) {
			wp_die(__('Expert was not found for this idea', 'picasso-ideas'));
		}

		// Delete review
		$delete = wp_delete_post($review[0]);

		if (is_wp_error($delete)) {
			wp_die($delete->get_error_message());
		}

		// Store success message in session
		Picasso_Ideas_Session::put('pi_success_message', __('Expert removed successfully', 'picasso-ideas'));

		// Redirect
		$redirect_to = add_query_arg('add-experts', '', get_the_permalink($idea_id));
		pi_redirect_to($redirect_to);
	}

	/**
	 * Post idea update
	 */
	public function post_idea_update() {
		if (!isset($_POST['post_idea_update'])) {
			return;
		}

		// Verify nonce
		if (!wp_verify_nonce($_POST['_wpnonce'], 'post_idea_update_nonce')) {
			wp_die(__('Nonce mismatched', 'picasso-ideas'));
		}

		$user_id = (isset($_POST['user_id'])) ? intval($_POST['user_id']) : '';
		$idea_id = (isset($_POST['idea_id'])) ? intval($_POST['idea_id']) : '';
		$review_id = (isset($_POST['review_id'])) ? intval($_POST['review_id']) : '';
		$idea_update = (isset($_POST['idea_update'])) ? wptexturize($_POST['idea_update']) : '';

		if (!$idea_id) {
			wp_die(__('Idea id is required', 'picasso-ideas'));
		}

		if (!$idea_update) {
			wp_die(__('Idea update is required', 'picasso-ideas'));
		}

		$idea_status = get_post_meta($idea_id, '_idea_status', true);
		$disable_idea_updates = get_post_meta($post_id, '_idea_disable_idea_updates', true);
		$new_update = false;

		// Check if logged in user have the permission to assign expert
		if (pi_check_for_idea_update_owner($idea_id) && $disable_idea_updates !== 'on' && $idea_status == 'in-project') {

			// Review id found so update it
			if ($idea_id && $user_id && !$review_id) {
				// check if review already exists
				$args = array(
					'post_type'      => 'idea_review',
					'post_status'    => 'publish',
					'posts_per_page' => -1,
					'fields'         => 'ids',
					'meta_query'     => array(
						'relation' => 'AND',
						array(
						    'key'     => '_idea_id',
						    'value'   => $idea_id,
						    'compare' => '=',
						),
						array(
						    'key'     => '_user_id',
						    'value'   => $user_id,
						    'compare' => '=',
						),
					),
				);

				$review = get_posts($args);

				$review_data = array(
					'post_type'   => 'idea_review',
					'post_title'  => 'Update # idea: ' . $idea_id . ' user: ' . $user_id,
					'post_status' => 'publish',
				);

				$review_meta_data = array();

				// update review
				if ($review) {
					$review_data['ID'] = $review[0];
					$review_id = wp_update_post($review_data, true);
				}
				// create a new one
				else {
					$review_data['post_author'] = $user_id;
					$review_id = wp_insert_post($review_data, true);
					$new_update = true;

					$review_meta_data = array(
						'_idea_id' => $idea_id,
						'_user_id' => $user_id,
					);
				}

				if (is_wp_error($review_id)) {
					wp_die($review_id->get_error_message());
				}

				if (!is_wp_error($review_id) && $review_id > 0) {
					foreach ($review_meta_data as $meta_key => $meta_value) {
						add_post_meta($review_id, $meta_key, $meta_value, true)
						or
						update_post_meta($review_id, $meta_key, $meta_value);
					}
				}
			}

			// update user update
			if ($review_id && get_post($review_id)) {
				update_post_meta($review_id, '_idea_update', $idea_update);
			} else {
				wp_die(__('Idea update was not found', 'picasso-ideas'));
			}

			if ($new_update) {
				$message = __('Idea update posted successfully', 'picasso-ideas');
			} else {
				$message = __('Idea update updated successfully', 'picasso-ideas');
			}

			// Store success message in session
			Picasso_Ideas_Session::put('pi_success_message', $message);

			// Redirect
			$redirect_to = get_the_permalink($idea_id) . '#idea-update';
			pi_redirect_to($redirect_to);

		} else {
			wp_die(__('You can\'t post idea update', 'picasso-ideas'));
		}
	}

	/**
	 * Post or update review
	 */
	public function post_review() {
		if (!isset($_POST['post_idea_review'])) {
			return;
		}

		// Verify nonce
		if (!wp_verify_nonce($_POST['_wpnonce'], 'idea_review_nonce')) {
			wp_die(__('Nonce mismatched', 'picasso-ideas'));
		}

		$idea_id = (isset($_POST['idea_id'])) ? intval($_POST['idea_id']) : '';
		$review_type = (isset($_POST['review_type'])) ? sanitize_text_field($_POST['review_type']) : '';
		$review_id = (isset($_POST['review_id'])) ? sanitize_text_field($_POST['review_id']) : '';
		$reviewer_id = (isset($_POST['reviewer_id'])) ? intval($_POST['reviewer_id']) : '';
		$comment = (isset($_POST['comment'])) ? wptexturize($_POST['comment']) : '';
		$modifier = pi_ideas_modifier();

		if (!in_array($review_type, array('user', 'expert'))) {
			wp_die(__('Something went wrong', 'picasso-ideas'));
		}

		if (!$idea_id) {
			wp_die(__('Idea id is required', 'picasso-ideas'));
		}

		if (!$comment) {
			wp_die(__('Comment is required', 'picasso-ideas'));
		}

		// Campaign
		$campaign_id = get_post_meta($idea_id, '_idea_campaign', true);
		global $picasso_ideas;

		if ($campaign_id) {
			$campaign_criteria = get_post_meta($campaign_id, '_campaign_criteria', true);
		} else {
			$campaign_criteria = array();
		}

		if ($campaign_criteria) {
			$review_criteria = $campaign_criteria;
		} elseif (key_exists('review_criteria', $picasso_ideas) && $picasso_ideas['review_criteria']) {
			$review_criteria = $picasso_ideas['review_criteria'];
		} else {
			$review_criteria = array();
		}

		// expert review
		if ($review_type === 'expert') {
			$update = false;
			$rating = array();

			// check deadline
			$idea_deadline = get_post_meta($idea_id, '_idea_expert_reviews_deadline', true);
			$today = current_time('Y-m-d');

			if (!$modifier && $idea_deadline && strtotime($today) > strtotime($idea_deadline)) {
			    wp_die(__('Review deadline has already passed', 'picasso-ideas'));
			}

			if ($review_criteria) {
				foreach ($review_criteria as $criteria) {
					$slug = pi_slugify($criteria);

					$score = (isset($_POST[$slug])) ? floatval($_POST[$slug]) : 0;
					$rating[$slug] = $score;
				}
			}

			$idea_review = array(
				'rating'  => $rating,
				'comment' => $comment,
			);

			if ($review_id && get_post($review_id)) {
				if (get_post_meta($review_id, '_idea_review', true)) {
					$update = true;
				}

				update_post_meta($review_id, '_idea_review', $idea_review);
			} else {
				wp_die(__('You don\'t have the permission', 'picasso-ideas'));
			}

			if ($update) {
				$message = __('Review updated successfully', 'picasso-ideas');
			} else {
				$message = __('Review posted successfully', 'picasso-ideas');
			}
		}
		// user review
		else {
			if (!$reviewer_id) {
				wp_die(__('Reviewer id is required', 'picasso-ideas'));
			}

			// check deadline
			$idea_deadline = get_post_meta($idea_id, '_idea_user_reviews_deadline', true);
			$today = current_time('Y-m-d');

			if (!$modifier && $idea_deadline && strtotime($today) > strtotime($idea_deadline)) {
			    wp_die(__('Review deadline has already passed', 'picasso-ideas'));
			}

			// check if review already exists
			$args = array(
				'post_type'       => 'idea_review',
				'post_status'     => 'publish',
				'posts_per_page'  => -1,
				'fields'          => 'ids',
				'meta_query'      => array(
					'relation' => 'AND',
					array(
					    'key'     => '_idea_id',
					    'value'   => $idea_id,
					    'compare' => '=',
					),
					array(
					    'key'     => '_user_id',
					    'value'   => $reviewer_id,
					    'compare' => '=',
					),
				),
			);

			$review = get_posts($args);

			$review_data = array(
				'post_type'   => 'idea_review',
				'post_title'  => 'Review # idea: ' . $idea_id . ' user: ' . $reviewer_id,
				'post_status' => 'publish',
			);

			$review_meta_data = array();

			// update review
			if ($review) {
				$review_data['ID'] = $review[0];
				$review_id = wp_update_post($review_data, true);
				$update = true;
			}
			// create a new one
			else {
				$review_data['post_author'] = get_current_user_id();
				$review_id = wp_insert_post($review_data, true);
				$update = false;

				$review_meta_data = array(
					'_idea_id' => $idea_id,
					'_user_id' => $reviewer_id,
				);
			}

			if (is_wp_error($review_id)) {
				wp_die($review_id->get_error_message());
			}

			foreach ($review_meta_data as $meta_key => $meta_value) {
				add_post_meta($review_id, $meta_key, $meta_value, true)
				or
				update_post_meta($review_id, $meta_key, $meta_value);
			}

			$rating = array();

			if ($review_criteria) {
				foreach ($review_criteria as $criteria) {
					$slug = pi_slugify($criteria);

					$score = (isset($_POST[$slug])) ? floatval($_POST[$slug]) : 0;
					$rating[$slug] = $score;
				}
			}

			$idea_review = array(
				'rating'  => $rating,
				'comment' => $comment,
			);

			update_post_meta($review_id, '_idea_review', $idea_review);

			if ($update) {
				$message = __('Review updated successfully', 'picasso-ideas');
			} else {
				$message = __('Review posted successfully', 'picasso-ideas');
			}
		}

		// Store success message in session
		Picasso_Ideas_Session::put('pi_success_message', $message);

		// Redirect
		if ($review_type === 'expert') {
			$redirect_to = get_the_permalink($idea_id) . '#expert-reviews';
		} else {
			$redirect_to = get_the_permalink($idea_id) . '#user-reviews';
		}

		pi_redirect_to($redirect_to);
	}
}

new Picasso_Ideas_Review();
