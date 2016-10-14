<?php

/**
* Picasso_Ideas_Submission class
*/
class Picasso_Ideas_Submission {
	/**
	 * Instantiate this Class
	 */
	public function __construct() {
		add_shortcode('idea_create_page', array($this, 'render_idea_create_form'));
		add_shortcode('idea_edit_page', array($this, 'render_idea_edit_form'));

		add_action('init', array($this, 'process_submission'));
	}

	/**
	 * Render HTML Markup for create idea
	 *
	 * @param  array $atts
	 * @return mixed
	 */
	public function render_idea_create_form($atts) {
		$template = IDEAS_TEMPLATE_PATH . 'frontend-submisssion/create-idea.php';

		ob_start();
		pi_render_template($template);
		$content = ob_get_clean();

		return $content;
	}

	/**
	 * Render HTML Markup to edit idea
	 *
	 * @param  array $atts
	 * @return mixed
	 */
	public function render_idea_edit_form($atts) {
		$template = IDEAS_TEMPLATE_PATH . 'frontend-submisssion/edit-idea.php';

		ob_start();
		pi_render_template($template);
		$content = ob_get_clean();

		return $content;
	}

	/**
	 * Process idea submission
	 */
	public function process_submission() {
		if (!isset($_POST['submit_idea'])) {
			return;
		}

		// Verify nonce
		if (!wp_verify_nonce($_POST['_wpnonce'], 'submit_idea_nonce')) {
			wp_die(__('Nonce mismatched', 'picasso-ideas'));
		}

		// Todo: check user permission

		$post_id = !empty($_POST['idea_id']) ? intval($_POST['idea_id']) : '';
		$post_title = !empty($_POST['idea_title']) ? sanitize_text_field($_POST['idea_title']) : '';
		$post_content = !empty($_POST['idea_content']) ? wp_kses($_POST['idea_content'], wp_kses_allowed_html('post')) : '';

		// meta data
		$idea_campaign = !empty($_POST['_idea_campaign']) ? intval($_POST['_idea_campaign']) : '';
		$idea_images = !empty($_POST['_idea_images']) ? $_POST['_idea_images'] : '';
		$idea_files = !empty($_POST['_idea_files']) ? $_POST['_idea_files'] : '';
		$idea_videos = !empty($_POST['_idea_videos']) ? $_POST['_idea_videos'] : '';
		$idea_youtube = !empty($_POST['_idea_youtube']) ? sanitize_text_field($_POST['_idea_youtube']) : '';

		if (empty($post_title)) {
			wp_die(__('Idea requires a title', 'picasso-ideas'));
		}

		$new_submission = true;

		if ($post_id) {
			$found_post = get_post($post_id);

			if ($found_post && $found_post->post_type === 'idea' && $found_post->post_status === 'publish') {
				$new_submission = false;
			}
		}

		// Post Date
		$post_data = array(
			'post_title'   => $post_title,
			'post_content' => $post_content,
			'post_parent' => $idea_campaign //Phan La added campain_id as idea post 's parent for buddypress activity update
		);

		$meta_data = array(
			// '_idea_campaign' => $idea_campaign, // We will save this meta field manually
			'_idea_images'   => $idea_images,
			'_idea_files'    => $idea_files,
			'_idea_videos'   => $idea_videos,
			'_idea_youtube'  => $idea_youtube,
		);

		// new submission false, so update it
		if ($new_submission === false) {
			// check if logged in user is the author of this post
			if (!pi_is_author_post($found_post->post_author)) {
				wp_die(__('Permission denied', 'picasso-ideas'));
			}

			// Set post_id
			$post_data['ID'] = $post_id;
			// If we are updating the post get old one. We need old post to set proper post_date value
			$post_data['post_date'] = $found_post->post_date;
		} else {
			$post_data['post_type'] = 'idea';
			$post_data['post_status'] = 'publish';
			$post_data['post_author'] = get_current_user_id();
			$post_data['comment_status'] = 'open';
		}

		if ($new_submission === true) {
			$new_submission_id = wp_insert_post($post_data, true);
		} else {
			$new_submission_id = wp_update_post($post_data, true);
		}

		// If there was an while updating or creating the post then show it to the user
		if (is_wp_error($new_submission_id)) {
			wp_die($new_submission_id->get_error_message());
		}

		foreach ($meta_data as $meta_key => $meta_value) {
			add_post_meta($new_submission_id, $meta_key, $meta_value, true)
			or
			update_post_meta($new_submission_id, $meta_key, $meta_value);
		}

		if ($idea_campaign) {
			pi_save_idea_campaign($new_submission_id, $idea_campaign);
		}

		if ($new_submission) {
			do_action('after_save_idea', $new_submission_id, get_post($new_submission_id));
			$message = sprintf(__('Your new idea has been submitted successfully. %s', 'picasso-ideas'), '<a href="' . get_the_permalink($new_submission_id) . '">' . __('Go to idea', 'picasso-ideas') . '</a>');
		} else {
			$message = sprintf(__('Your idea has been updated successfully. %s', 'picasso-ideas'), '<a href="' . get_the_permalink($new_submission_id) . '">' . __('Go to idea', 'picasso-ideas') . '</a>');
		}

		// Store success message and idea id in session
		Picasso_Ideas_Session::flash('pi_frontend_submit_message', $message);
		Picasso_Ideas_Session::flash('pi_idea_id', $message);

		// Redirect user to prevent double-submissions with browser refreshes
		$redirect_to = add_query_arg(array('id' => $new_submission_id), get_the_permalink());
		pi_redirect_to($redirect_to);
	}

}

new Picasso_Ideas_Submission();
