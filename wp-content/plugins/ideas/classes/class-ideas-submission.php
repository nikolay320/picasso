<?php

/**
* Ideas Submission class
*/
class Ideas_Submission {
	private $metabox_id,
			$object_id;

	/**
	 * Instantiate this Class
	 */
	public function __construct() {
		$this->metabox_id = '_idea_cmb2_metaboxes_idea_attachments';
		$this->object_id = $this->get_object_id();

		add_action('cmb2_init', array($this, 'submissoin_form'));
		add_action('cmb2_after_init', array($this, 'process_submission'));
		add_shortcode('idea_submission_form', array($this, 'render_submission_form'));
		add_shortcode('idea_create_page', array($this, 'render_idea_create_form'));
		add_shortcode('idea_edit_page', array($this, 'render_idea_edit_form'));
	}

	/**
	 * Get object id
	 * @return int
	 */
	public function get_object_id() {
		if (empty($_GET['id'])) {
			return null;
		}

		$post = get_post($_GET['id']);

		if (!$post) {
			return null;
		}

		if ($post->post_type !== 'ideas') {
			return null;
		}

		if ($post->post_status !== 'publish') {
			return null;
		}

		return $_GET['id'];
	}

	public function submissoin_form() {
		// Get existing metabox
		$cmb = $this->get_metabox();

		// Prepend meta fields
		if (!is_admin()) {
			// title field
			$cmb->add_field( array(
				'name'       => __( 'Idea Title', 'ideas_plugin' ),
				'id'         => 'post_title',
				'type'       => 'text',
				'default'    => 'klc_idea_post_title',
				// 'attributes' => array(
				//     'required' => 'required',
				// ),
			), 1 );

			// content field
			$cmb->add_field( array(
				'name'    => __( 'Idea Content', 'ideas_plugin' ),
				'id'      => 'post_content',
				'type'    => 'wysiwyg',
				'default' => 'klc_idea_post_content',
				'options' => array(
					'textarea_rows' => 12,
					'media_buttons' => false,
				),
			), 2 );

			// campaign field
			$cmb->add_field( array(
				'name'    => __( 'Campaign', 'ideas_plugin' ),
				'id'      => 'idea_campaign',
				'type'    => 'select',
				'options' => klc_get_campaigns(),
			), 3 );
		}
	}

	/**
	 * Get metabox
	 * 
	 * @return mixed
	 */
	public function get_metabox() {
		if (!$this->object_id) {
			$object_id = 'fake-object-id';
		} else {
			$object_id = $this->object_id;
		}
		return cmb2_get_metabox($this->metabox_id, $object_id);
	}

	/**
	 * Render submission form for create and edit
	 * 
	 * @param  array $atts
	 * @return mixed
	 */
	public function render_submission_form($atts) {
		wp_enqueue_style('cmb2-frontend-form');

		$cmb = $this->get_metabox();

		$output = '';

		// Get any submission errors
		if (($error = $cmb->prop('submission_error')) && is_wp_error($error)) {
			// If there was an error with the submission, add it to our output.
			$output .= '<div class="alert alert-danger" role="alert">' . sprintf(__('There was an error in the submission: %s', 'ideas_plugin'), '<strong>' . $error->get_error_message() . '</strong>') . '</div>';
		}

		// If the post was submitted successfully, notify the user.
		if (isset($_GET['idea_submitted']) && $_GET['idea_submitted'] === 'true') {
			// Add notice of submission to our output
			if ($this->object_id) {
				$output = '<div class="alert alert-success" role="alert">' . __('Thank you, your idea has been saved.', 'ideas_plugin') . '</div>';
			} else {
				$output = '<div class="alert alert-success" role="alert">' . __('Thank you, your new idea has been submitted.', 'ideas_plugin') . '</div>';
			}
		}

		$button_text = $this->object_id ? __('Save Idea', 'ideas_plugin') : __('Add Idea', 'ideas_plugin');

		if (!$this->object_id) {
			$object_id = 'fake-object-id';
		} else {
			$object_id = $this->object_id;
		}

		$output .= cmb2_get_metabox_form($cmb, $object_id, array('save_button' => $button_text));

		return $output;
	}

	/**
	 * Render HTML Markup for create idea
	 * 
	 * @param  array $atts
	 * @return mixed
	 */
	public function render_idea_create_form($atts) {
		$params = array();

		if ($this->object_id) {
			$params['idea_id'] = $this->object_id;
		}

		ob_start();
		klc_render_template('frontend-submission/create-idea-from-frontend', $params);
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
		if (!$this->object_id) {
			return;
		}

		ob_start();
		klc_render_template('frontend-submission/edit-idea-from-frontend');
		$content = ob_get_clean();

		return $content;
	}

	/**
	 * Process submission, save post
	 */
	public function process_submission() {
		$post_id = !empty($_GET['id']) ? intval($_GET['id']) : '';

		// If no form submission, bail
		if (empty($_POST) || !isset($_POST['submit-cmb'], $_POST['object_id'])) {
			return false;
		}

		// Get CMB2 metabox object
		$cmb = $this->get_metabox();

		// Check security nonce
		if (!isset($_POST[$cmb->nonce()]) || !wp_verify_nonce($_POST[$cmb->nonce()], $cmb->nonce())) {
			return $cmb->prop('submission_error', new WP_Error('security_fail', __('Security check failed.', 'ideas_plugin')));
		}

		// Check title submitted
		if (empty($_POST['post_title'])) {
			return $cmb->prop('submission_error', new WP_Error('post_data_missing', __('New idea requires a title.', 'ideas_plugin')));
		}

		/**
		 * Fetch sanitized values
		 */
		$sanitized_values = $cmb->get_sanitized_values($_POST);

		$post_data = array(
			'post_title'   => $sanitized_values['post_title'],
			'post_content' => wp_kses($sanitized_values['post_content'], wp_kses_allowed_html('post')),
			'post_status'  => 'publish',
			'post_type'    => 'ideas',
			'post_author'  => get_current_user_id(),
		);

		$old_post = $post_id ? get_post($post_id) : '';

		if ($old_post && $old_post->post_type === 'ideas') {
			// Set post_id
			$post_data['ID'] = $post_id;
			// If we are updating the post get old one. We need old post to set proper post_date value
			$post_data['post_date'] = $old_post->post_date;
			$new_submission = false;
		} else {
			$post_data['post_author'] = get_current_user_id();
			$new_submission = true;
		}

		if ($new_submission === true) {
			$new_submission_id = wp_insert_post($post_data, true);
		} else {
			$new_submission_id = wp_update_post($post_data, true);
		}

		// If we hit a snag, update the user
		if (is_wp_error($new_submission_id)) {
			return $cmb->prop('submission_error', $new_submission_id);
		}

		// unset post_title
		unset($sanitized_values['post_title']);
		// unset post_content
		unset($sanitized_values['post_content']);

		if ($sanitized_values) {
			$cmb->save_fields($new_submission_id, $cmb->object_type(), $sanitized_values);
		}

		/*
		 * Redirect back to the form page with a query variable with the new post ID.
		 * This will help double-submissions with browser refreshes
		 */
		wp_redirect(esc_url_raw(add_query_arg('idea_submitted', 'true')));
		exit;
	}
}

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

new Ideas_Submission();