<?php
/**
 * Check if the logged in user is an modifier, who can assign experts for ideas
 *
 * @return boolean
 */
if (!function_exists('pi_ideas_modifier')) {
	function pi_ideas_modifier() {
		global $current_user, $picasso_ideas;

		$modifier_roles = key_exists('modifier_roles', $picasso_ideas) ? $picasso_ideas['modifier_roles'] : array();
		$modifier = false;

		if ($modifier_roles) {
			foreach ($modifier_roles as $modifier_role) {
				if (in_array($modifier_role, $current_user->roles)) {
					$modifier = true;
					break;
				}
			}
		}

		return $modifier;
	}
}

/**
 * Check if logged in user is an modifier and have the ability to edit reviews
 *
 * @return boolean
 */
if (!function_exists('pi_modifier_can_edit_reviews')) {
	function pi_modifier_can_edit_reviews() {
		global $picasso_ideas;

		return (pi_ideas_modifier() && $picasso_ideas['modifier_can_edit_reviews'] != 0) ? true : false;
	}
}

/**
 * Check if the logged in user is able to provide an idea update
 *
 * @param  int $idea_id
 * @return boolean
 */
if (!function_exists('pi_check_for_idea_update_owner')) {
	function pi_check_for_idea_update_owner($idea_id) {
		if (!is_user_logged_in()) {
			return false;
		}

		$current_user_id = get_current_user_id();

		// find author of this idea as author can post user update
		$idea_author = get_post_field('post_author', $idea_id);

		if ($current_user_id == $idea_author) {
			return true;
		}

		// finally check if the logged in user is assigned to post idea update
		$idea_update_owners = get_post_meta($idea_id, '_idea_owners', true);

		if ($idea_update_owners && in_array($current_user_id, $idea_update_owners)) {
			return true;
		}

		return false;
	}
}

/**
 * Get users who have specific user roles
 *
 * @return array
 */
if (!function_exists('pi_get_idea_experts')) {
	function pi_get_idea_experts() {
		global $picasso_ideas;

		$expert_roles = key_exists('expert_roles', $picasso_ideas) ? $picasso_ideas['expert_roles'] : array();

		$args = array(
			'role__in' => $expert_roles,
		);

		return get_users($args);
	}
}

/**
 * Get experts for given idea id
 *
 * @param  int $idea_id
 * @return array
 */
if (!function_exists('pi_get_experts_for_given_idea')) {
	function pi_get_experts_for_given_idea($idea_id) {
		$idea_experts = array();

		$args = array(
		    'post_type'      => 'idea_review',
		    'post_status'    => 'publish',
		    'posts_per_page' => -1,
		    'fields'         => 'ids',
		    'meta_query'     => array(
		        array(
		            'key'     => '_idea_id',
		            'value'   => $idea_id,
		            'compare' => '=',
		        ),
		        array(
		            'key'     => '_expert_id',
		            'compare' => 'EXISTS',
		        ),
		    ),
		);

		$reviews = get_posts($args);

		if ($reviews) {
		    foreach ($reviews as $review_id) {
		        $idea_experts[] = get_post_meta($review_id, '_expert_id', true);
		    }
		}

		return $idea_experts;
	}
}

/**
 * Get user review or expert review
 *
 * @param  int $idea_id Idea ID
 * @param  int $user_id User ID
 * @param  string $type    expert or user
 * @return int|null          On success return review id otherwise null
 */
if (!function_exists('pi_get_review')) {
	function pi_get_review($idea_id, $user_id, $type, $force_check = true) {
		$type = ($type === 'expert') ? '_expert_id' : '_user_id';

		$args = array(
			'post_type'      => 'idea_review',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		);

		$meta_query = array(
			'relation' => 'AND',
			array(
			    'key'     => '_idea_id',
			    'value'   => $idea_id,
			    'compare' => '=',
			),
			array(
			    'key'     => $type,
			    'value'   => $user_id,
			    'compare' => '=',
			),
		);

		if ($force_check !== false) {
			$meta_query[] = array(
				'key'     => '_idea_review',
				'compare' => 'EXISTS',
			);
		}

		$args['meta_query'] = $meta_query;

		$review = get_posts($args);

		if ($review) {
			return $review[0];
		} else {
			return null;
		}
	}
}

/**
 * Get all reviews and average rating
 *
 * @param  int $idea_id 	Idea ID
 * @param  string $type    	expert or user
 * @param  array $type    	Review criteria
 *
 * @return array
 */
if (!function_exists('pi_get_reviews_and_average_rating')) {
	function pi_get_reviews_and_average_rating($idea_id, $type, $review_criteria = array()) {
		$type = ($type === 'expert') ? '_expert_id' : '_user_id';

		$args = array(
			'post_type'         => 'idea_review',
			'post_status'       => 'publish',
			'posts_per_page'    => -1,
			'fields'            => 'ids',
			'meta_query'        => array(
				'relation' => 'AND',
				array(
				    'key'     => '_idea_id',
				    'value'   => $idea_id,
				    'compare' => '=',
				),
				array(
				    'key'     => $type,
				    'compare' => 'EXISTS',
				),
			),
		);

		$reviews = get_posts($args);
		$review_ids = array();

		$idea_reviews = array();
		$review_found = false;

		if ($reviews) {
		    foreach ($reviews as $review_id) {
		        if ($idea_review = get_post_meta($review_id, '_idea_review', true)) {
		            $idea_reviews[] = $idea_review['rating'];
		            $review_ids[] = $review_id;
		            $review_found = true;
		        }
		    }
		}

		if (!$review_criteria) {
			global $picasso_ideas;
			$review_criteria = $picasso_ideas['review_criteria'];
		}

		$reviews_in_each_criteria = array();

		if ($review_criteria && $review_found) {
		    foreach ($review_criteria as $criteria) {
		        $slug = pi_slugify($criteria);

		        foreach ($idea_reviews as $idea_review) {
		            if (key_exists($slug, $idea_review)) {
		            	$reviews_in_each_criteria[$criteria][] = $idea_review[$slug];
		            }
		        }
		    }
		}

		// average in each criteria
		$average_in_each_criteria = array();
		$average = '';

		if ($reviews_in_each_criteria) {
		    foreach ($reviews_in_each_criteria as $criteria => $values) {
		        $average_in_each_criteria[$criteria] = number_format(array_sum($values) / count($values), 1, '.', '');
		    }

		    // average
		    $average = number_format(array_sum($average_in_each_criteria) / count($average_in_each_criteria), 1, '.', '');
		}

		return array(
			'review_found'             => $review_found,
			'average_in_each_criteria' => $average_in_each_criteria,
			'average'                  => $average,
			'reviews'                  => $review_ids,
		);
	}
}

/**
 * Count reviews for different step
 *
 * @param  string $type expert or user
 * @return array  It would work like this: 12 people gave 5 stars, 6 people gave 2 stars...
 */
if (!function_exists('pi_count_reviews')) {
	function pi_count_reviews($type) {
		$type = ($type === 'expert') ? '_expert_id' : '_user_id';

		$args = array(
			'post_type'   => 'idea_review',
			'post_status' => 'publish',
			'numberposts' => -1,
			'fields'      => 'ids',
			'meta_query'  => array(
				'relation' => 'AND',
				array(
				    'key'     => '_idea_id',
				    'value'   => get_the_ID(),
				    'compare' => '=',
				),
				array(
				    'key'     => $type,
				    'compare' => 'EXISTS',
				),
			),
		);

		$reviews = get_posts($args);

		$count_ratings = array(
			'5.0' => 0,
			'4.5' => 0,
			'4.0' => 0,
			'3.5' => 0,
			'3.0' => 0,
			'2.5' => 0,
			'2.0' => 0,
			'1.5' => 0,
			'1.0' => 0,
			'0.5' => 0,
			'0.0' => 0,
		);

		$expert_idea_average_ratings = array();

		if ($reviews) {
		    foreach ($reviews as $review_id) {
		        if ($idea_review = get_post_meta($review_id, '_idea_review', true)) {
		        	$idea_rating = $idea_review['rating'];
		        	$average = array_sum($idea_rating) / count($idea_rating);
		        	$average = floor($average * 2) / 2;
		        	$expert_idea_average_ratings[] = number_format($average, 1, '.', '');
		        }
		    }
		}

		// count
		if ($expert_idea_average_ratings) {
			foreach ($expert_idea_average_ratings as $key => $value) {
				$count_ratings[$value] += 1;
			}
		}

		return $count_ratings;
	}
}

/**
 * Get idea update
 *
 * @param  int $idea_id Idea ID
 * @param  int $user_id User ID
 * @return int|null On success return post id otherwise null
 */
if (!function_exists('pi_get_idea_update')) {
	function pi_get_idea_update($idea_id, $user_id) {
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
		        array(
		            'key'     => '_idea_update',
		            'compare' => 'EXISTS',
		        ),
		    ),
		);

		$post = get_posts($args);

		if ($post) {
			return $post[0];
		} else {
			return null;
		}
	}
}

/**
 * Get idea updates
 *
 * @param  int $idea_id
 * @return array On success return list of post ids
 */
if (!function_exists('pi_get_idea_updates')) {
	function pi_get_idea_updates($idea_id) {
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
		            'key'     => '_idea_update',
		            'compare' => 'EXISTS',
		        ),
		    ),
		);

		return get_posts($args);
	}
}

/**
 * Human readable time
 * @param  int $post_id Post ID
 * @return string          ex: posted 12 minutes ago
 */
if (!function_exists('pi_posted_on')) {
	function pi_posted_on($post_id) {
		$days = round((date('U') - get_the_time('U', $post_id)) / (60*60*24));
		$days = (int)$days;

		if ($days === 0) {
			$posted_on = __('posted today', 'picasso-ideas');
		} elseif ($days === 1) {
			$posted_on = __('posted yesterday', 'picasso-ideas');
		} else {
			$posted_on = sprintf(__('posted %s %s ago', 'picasso-ideas'), $days, _n('day', 'days', $days, 'picasso-ideas'));
		}

		return $posted_on;
	}
}

/**
 * Get data-attributes for chart
 *
 * @param  int 		$idea_id     		Idea ID
 * @param  string 	$review_type 		expert or user
 * @param  array 	$review_criteria 	Array containing the criterias
 *
 * @return string
 */
if (!function_exists('pi_get_data_for_chart')) {
	function pi_get_data_for_chart($idea_id, $review_type, $review_criteria = array()) {
		global $picasso_ideas;

		if (!$review_criteria) {
			global $picasso_ideas;
			$review_criteria = $picasso_ideas['review_criteria'];
		}

		$reviews_and_average_ratings = pi_get_reviews_and_average_rating($idea_id, $review_type, $review_criteria);

		if (!$reviews_and_average_ratings) {
			return;
		}

		$radar_chart_labels = implode(',', $review_criteria);
		$radar_chart_label = __('Average', 'picasso-ideas');
		$radar_chart_data = implode(',', $reviews_and_average_ratings['average_in_each_criteria']);

		$reviews_found_for_different_steps = pi_count_reviews($review_type);
		$bar_chart_data = implode(',', $reviews_found_for_different_steps);
		$bar_chart_label = __('Ratings', 'picasso-ideas');

		$attributes = '';

		$attributes .= 'data-radar-chart-labels="' . $radar_chart_labels . '"';
		$attributes .= ' data-radar-chart-label="' . $radar_chart_label . '"';
		$attributes .= ' data-radar-chart-data="' . $radar_chart_data . '"';
		$attributes .= ' data-bar-chart-data="' . $bar_chart_data . '"';
		$attributes .= ' data-bar-chart-label="' . $bar_chart_label . '"';

		if ($review_type === 'expert') {
			$attributes .= ' data-bar-chart-star-color="#FE642E"';
		} else {
			$attributes .= ' data-bar-chart-star-color="#efc700"';
		}

		$attributes .= ' data-chart-data-found="true"';

		return $attributes;
	}
}

/**
 * Render chart modal markup in footer
 */
if (!function_exists('pi_render_chart_modal_markup')) {
	function pi_render_chart_modal_markup() {
		if (is_post_type_archive('idea') || is_singular('idea') || is_singular('campaign')) {
			$html = '';

			$html .= '<div class="remodal picasso-idea-chart-modal" role="dialog">';
				$html .= '<button data-remodal-action="close" class="remodal-close"></button>';

				$html .= '<div class="col-md-6 bar-chart">';
					$html .= '<div class="text-center chart-title">' . __('Rating distribution', 'picasso-ideas') . '</div>';
					$html .= '<canvas id="picasso-idea-horizontal-chart" class="picasso-idea-chart"></canvas>';
				$html .= '</div>';

				$html .= '<div class="col-md-6 radar-chart">';
					$html .= '<div class="text-center chart-title">' . __('Rating summary', 'picasso-ideas') . '</div>';
					$html .= '<canvas id="picasso-idea-radar-chart" class="picasso-idea-chart"></canvas>';
				$html .= '</div>';

			$html .= '</div>';

			echo $html;
		}
	}
	add_action('wp_footer', 'pi_render_chart_modal_markup', 1);
}

/**
 * Render comment modal markup in footer
 */
if (!function_exists('pi_add_comment_modal_markup')) {
	function pi_add_comment_modal_markup() {
		if (is_post_type_archive('idea') || is_singular('campaign')) {
			$html = '';

			$html .= '<div class="remodal picasso-idea-comment-modal" role="dialog">';
				$html .= '<button data-remodal-action="close" class="remodal-close"></button>';

				$html .= '<div class="message-wrapper"></div>';

				$html .= '<form action="" method="POST">';

					$html .= '<div class="form-group">';
						$html .= '<h4>' . __('Add your comment', 'picasso-ideas') . '</h4>';
						$html .= '<textarea class="form-control no-hr-resize comment" name="comment" rows="4" required></textarea>';
					$html .= '</div>';

					$html .= '<input type="hidden" class="post_id" name="post_id" value="">';

					$html .= '<div class="buttons-group">';
						$html .= '<span class="loading fa fa-spinner fa-spin"></span>';
						$html .= '<button data-remodal-action="cancel" class="btn btn-default">' . __('Cancel', 'picasso-ideas') . '</button>';
						$html .= '<input type="submit" class="btn btn-primary submit_comment" name="submit_comment" value="' . __('Add', 'picasso-ideas') . '">';
					$html .= '</div>';

				$html .= '</form>';

			$html .= '</div>';

			echo $html;
		}
	}
	add_action('wp_footer', 'pi_add_comment_modal_markup', 2);
}

/**
 * Render edit comment modal markup in footer
 */
if (!function_exists('pi_render_edit_comment_modal_markup')) {
	function pi_render_edit_comment_modal_markup() {
		if (is_singular('idea')) {
			$html = '';

			$html .= '<div class="remodal picasso-edit-comment-modal" role="dialog">';
				$html .= '<button data-remodal-action="close" class="remodal-close"></button>';
				$html .= '<div class="large-loader"><i class="fa fa-spinner fa-spin fa-4x fa-fw"></i></div>';
				$html .= '<div class="message-wrapper"></div>';
				$html .= '<form action="" method="POST"></form>';
			$html .= '</div>';

			echo $html;
		}
	}
	add_action('wp_footer', 'pi_render_edit_comment_modal_markup', 3);
}

if (!function_exists('pi_get_comment_form_ajax_callback')) {
	function pi_get_comment_form_ajax_callback() {
		$comment_id = !empty($_POST['comment_id']) ? $_POST['comment_id'] : '';
		$html = '';

		if ($comment = get_comment($comment_id)) {
			$html .= '<div class="form-group">';
				$html .= '<h4>' . __('Edit your comment', 'picasso-ideas') . '</h4>';
				$html .= '<textarea class="form-control no-hr-resize comment" name="comment" rows="4">' . $comment->comment_content . '</textarea>';
			$html .= '</div>';

			$html .= '<input type="hidden" class="comment_id" name="comment_id" value="' . $comment_id . '">';

			$html .= '<div class="buttons-group">';
				$html .= '<span class="loading fa fa-spinner fa-spin"></span>';
				$html .= '<button data-remodal-action="cancel" class="btn btn-default">' . __('Cancel', 'picasso-ideas') . '</button>';
				$html .= '<input type="submit" class="btn btn-primary submit_comment" name="submit_comment" value="' . __('Save', 'picasso-ideas') . '">';
			$html .= '</div>';
		}

		echo $html;

		exit;
	}
	add_action('wp_ajax_pi_get_comment_form', 'pi_get_comment_form_ajax_callback');
}

/**
 * Search ideas for given keywork
 *
 * @param  string $keyword
 * @return array
 */
if (!function_exists('pi_search_ideas')) {
	function pi_search_ideas($keyword) {
		$args = array(
			'post_type'      => 'idea',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			's'              => $keyword,
		);

		$posts = get_posts($args);
		$rows = array();

		if ($posts) {
			foreach ($posts as $post) {
				$rows[$post->ID] = $post->post_title;
			}
		}

		return $rows;
	}
}

/**
 * Find similar ideas from ajax request
 */
if (!function_exists('pi_search_similar_ideas_ajax_callback')) {
	function pi_search_similar_ideas_ajax_callback() {
		$keyword = !empty($_POST['keyword']) ? $_POST['keyword'] : '';

		$posts = pi_search_ideas($keyword);
		$html = '';

		if ($posts) {
			foreach ($posts as $post_id => $post_title) {
				$html .= '<div class="similar-idea"><a href="' . get_the_permalink($post_id) . '" target="_blank">' . $post_title . '</a></div>';
			}
		} else {
			$html .= '<div class="similar-idea">' . __('No similar idea found!', 'picasso-ideas') . '</div>';
		}

		echo $html;
		exit;
	}
	add_action('wp_ajax_pi_search_similar_ideas', 'pi_search_similar_ideas_ajax_callback');
}

/**
 * Save custom post meta field '_idea_campaign'
 */
if (!function_exists('cmb2_save__idea_campaign')) {
	function cmb2_save__idea_campaign($override, $args, $field_args, CMB2_Field $field) {
		$args = (object)$args;
		$idea_id = $args->id;
		$campaign_id = $args->value;

		// unattach from existing campaigns
		$existing_campaign_args = array(
			'post_type'  => 'campaign',
			'fields'     => 'ids',
			'meta_query' => array(
				array(
					'key'     => '_campaign_ideas',
					'value'   => $idea_id,
					'compare' => '=',
				),
			),
		);

		$campaigns = get_posts($existing_campaign_args);

		if ($campaigns) {
			foreach ($campaigns as $existing_campaign_id) {
				delete_post_meta($existing_campaign_id, '_campaign_ideas', $idea_id);
			}
		}

		// Reattach campaign
		add_post_meta($campaign_id, '_campaign_ideas', $idea_id);
	}
	add_action('cmb2_override__idea_campaign_meta_save', 'cmb2_save__idea_campaign', 10, 4);
}

/**
 * Save idea campaign from our frontend idea submit form
 *
 * @param  int $idea_id
 * @param  int $campaign_id
 */
if (!function_exists('pi_save_idea_campaign')) {
	function pi_save_idea_campaign($idea_id, $campaign_id) {
		// unattach from existing campaigns
		$existing_campaign_args = array(
			'post_type'  => 'campaign',
			'fields'     => 'ids',
			'meta_query' => array(
				array(
					'key'     => '_campaign_ideas',
					'value'   => $idea_id,
					'compare' => '=',
				),
			),
		);

		$campaigns = get_posts($existing_campaign_args);

		if ($campaigns) {
			foreach ($campaigns as $existing_campaign_id) {
				delete_post_meta($existing_campaign_id, '_campaign_ideas', $idea_id);
			}
		}

		// Reattach campaign
		add_post_meta($campaign_id, '_campaign_ideas', $idea_id);

		// update post meta
		update_post_meta($idea_id, '_idea_campaign', $campaign_id);
	}
}

/**
 * Display only user-uploaded files to each user
 *
 * @param  WP_Query $wp_query
 */
if (!function_exists('pi_restrict_media_library')) {
	function pi_restrict_media_library($wp_query) {
		global $current_user, $pagenow;

		if (!is_user_logged_in()) {
			return;
		}

		if ($pagenow != 'admin-ajax.php' || $_REQUEST['action'] != 'query-attachments') {
			return;
		}

		if (!current_user_can('manage_media_library')) {
			$wp_query->set('author', $current_user->ID);
		}

		return $wp_query;
	}
	// add_action('pre_get_posts', 'pi_restrict_media_library');
}

/**
 * Register shortcode for rendering frontend upload form
 *
 * @param  array $atts
 * @return mixed
 */
if (!function_exists('pi_register_shortcode_for_uploading_file')) {
	function pi_register_shortcode_for_uploading_file($atts) {
		// Attributes
		$atts = shortcode_atts(
			array(
				'supposed_file_type' => 'image',
				'button_title'       => __('Upload', 'picasso-ideas'),
				'mime_type'          => 'image/*',
				'meta_field_name'    => '',
				'post_id'            => '',
			),
			$atts
		);

		$template = IDEAS_TEMPLATE_PATH . 'frontend-submisssion/upload-file.php';

		ob_start();
		pi_render_template($template, $atts);
		$content = ob_get_clean();

		return $content;
	}
	add_shortcode('pi_upload_file', 'pi_register_shortcode_for_uploading_file');
}

/**
 * Upload file from ajax call
 */
if (!function_exists('pi_upload_file_ajax_callback')) {
	function pi_upload_file_ajax_callback() {

		$file = !empty($_FILES['file']) ? $_FILES['file'] : '';

		$supposed_file_type = !empty($_POST['supposed_file_type']) ? sanitize_text_field($_POST['supposed_file_type']) : '';

		$meta_field_name = !empty($_POST['meta_field_name']) ? sanitize_text_field($_POST['meta_field_name']) : '';

		$errors = array();

		$upload_output = '';

		$file_type = '';

		$valid_file_types = array(
			'image',
			'video',
			'document',
			'spreadsheet',
			'interactive',
			'text',
			'archive',
			'code',
		);

		if (empty($file)) {

			$errors[] = __('Please choose a file.', 'picasso-ideas');

		} else {

			if (!empty($supposed_file_type) && in_array($supposed_file_type, $valid_file_types) && !empty($meta_field_name)) {

				$file_size = $file['size'];
				$check_file_type = wp_check_filetype($file['name']);
				$file_ext = $check_file_type['ext'];
				$file_type = wp_ext2type($file_ext);

				// byte calculator: http://www.whatsabyte.com/P1/byteconverter.htm
				if ($file_type === 'image') {
					$supposed_file_size = 5242880; // 5MB
				} elseif ($file_type === 'video') {
					$supposed_file_size = 524288000; // 500MB
				} else {
					$supposed_file_size = 209715200; // 200MB
				}

				// first check the file type
				if ($file_type !== $supposed_file_type) {
					$errors[] = __('Invalid file format.', 'picasso-ideas');
					// $errors[] = __('Invalid file format. File type: ' . $file_type . ' Supposed file type: ' . $supposed_file_type, 'picasso-ideas');
				}

				// then check file size limit
				elseif ($file_size > $supposed_file_size) {
					$errors[] = sprintf(__('Exceeded filesize limit. Maximum size should be %dMB.', 'picasso-ideas'), $supposed_file_size / (1024 * 1024));
				}

			} else {

				$errors[] = __('Something went worng.', 'picasso-ideas');

			}

		}

		// hadle uploading this file to our server
		if (!$errors) {
			$upload_output = pi_handle_upload($file, $meta_field_name);
		}

		echo json_encode(array(
			'file_type' => $file_type,
			'output'    => $upload_output,
			'errors'    => $errors,
		));

		exit;
	}
	add_action('wp_ajax_pi_upload_file', 'pi_upload_file_ajax_callback');
}


/**
 * Function for uploading a file to our server
 *
 * @param  array  $file
 * @param  int $post_id
 * @param  boolean $set_as_featured
 * @return array|int Return attachment id on success, otherwise an array with error messages
 */
if (!function_exists('pi_handle_upload')) {
	function pi_handle_upload($file, $meta_field_name, $post_id = 0, $set_as_featured = false) {

		$upload = wp_upload_bits( $file['name'], null, file_get_contents( $file['tmp_name'] ) );
		$upload_error = '';
		$html_markup = '';

		if ( isset( $upload['error'] ) && $upload['error'] !== false) {

			$upload_error = $upload['error'];

		}

		else {

			$wp_filetype = wp_check_filetype( basename( $upload['file'] ), null );

			$wp_filetype_2 = wp_ext2type($wp_filetype['ext']);

			$wp_upload_dir = wp_upload_dir();

			$attachment = array(
			    'guid'           => $wp_upload_dir['baseurl'] . _wp_relative_upload_path( $upload['file'] ),
			    'post_mime_type' => $wp_filetype['type'],
			    'post_title'     => preg_replace('/\.[^.]+$/', '', basename( $upload['file'] )),
			    'post_content'   => '',
			    'post_status'    => 'inherit'
			);

			$attach_id = wp_insert_attachment( $attachment, $upload['file'], $post_id );

			require_once(ABSPATH . 'wp-admin/includes/image.php');
			require_once(ABSPATH . 'wp-admin/includes/file.php');
			require_once(ABSPATH . 'wp-admin/includes/media.php');

			$attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
			wp_update_attachment_metadata( $attach_id, $attach_data );

			if( $set_as_featured == true ) {
			    update_post_meta( $post_id, '_thumbnail_id', $attach_id );
			}

			if ( intval( $attach_id ) > 0) {

				$html = '';

				if ($wp_filetype_2 === 'image') {

					$image_link = wp_get_attachment_url($attach_id);
					$image_thumb = wp_get_attachment_image_src($attach_id);

					$html .= '<div class="single-image">';
						$html .= '<div class="image-wrapper">';
							$html .= '<a href="' . $image_link . '" rel="prettyPhoto">';
								$html .= '<img src="' . $image_thumb[0] . '" />';
							$html .= '</a>';
							$html .= '<input type="hidden" name="' . $meta_field_name . '[' . $attach_id . ']" value="' . $image_link . '">';
							$html .= '<a href="javascript:void(0)" class="remove"></a>';
						$html .= '</div>';
					$html .= '</div>';

				} else {

					$attach_link = wp_get_attachment_url($attach_id);

					$html .= '<div class="single-file">';
						$html .= '<div class="file-wrapper">';
							$html .= '<span class="attachment-title">' . get_the_title($attach_id) . '</span>';
							$html .= '<input type="hidden" name="' . $meta_field_name . '[' . $attach_id . ']" value="' . $attach_link . '">';
							$html .= '<a href="javascript:void(0)" class="remove"></a>';
						$html .= '</div>';
					$html .= '</div>';

				}


				$html_markup = $html;
			}

		}

		return array(
			'upload_error' => $upload_error,
			'html_markup'  => $html_markup,
		);
	}
}

/**
 * Render wp_ulike markup
 *
 * @param  int $post_id   		Post ID
 * @param  int $author_id 		Author ID
 * @param  string $status    	Idea Status
 *
 * @return string
 */
if (!function_exists('pi_wp_ulike_markup')) {
	function pi_wp_ulike_markup($post_id, $author_id, $status, $force_enable_vote) {
		if (function_exists('wp_ulike1')) {
			if ($status === 'votes' || ($status === 'no-status' && $force_enable_vote)) {

				echo '<div class="idea-vote" data-ulike-id="' . $post_id . '" data-user-id="' . $author_id . '">';
					echo wp_ulike1('put', $post_id);
				echo '</div>';

			} else {

				$message = __('Idea not anymore in vote phase', 'picasso-ideas');
				$count_likes = intval(wp_ulike_get_post_likes($post_id));

				if ($count_likes > 0) {
					$button_class = 'like-button liked';
				} else {
					$button_class = 'like-button';
				}

				echo '<div class="idea-vote" title="' . $message . '" data-toggle="idea-tooltip" data-placement="top">';
					echo '<div class="wpulike-disabled">';
						echo '<div class="counter"><span class="' . $button_class . '"></span><span class="count-box">' . $count_likes . '</span></div>';
					echo '</div>';
				echo '</div>';

			}

		}
	}
}

/**
 * Get tooltip message for different status
 *
 * @param  string $status
 *
 * @return string
 */
if (!function_exists('pi_tooltip_message_for_status')) {
	function pi_tooltip_message_for_status($status) {
		if ($status === 'no-status') {
			$message = __('You can vote the idea by linking', 'picasso-ideas');
		} elseif ($status === 'votes') {
			$message = __('You can vote the idea by linking', 'picasso-ideas');
		} elseif ($status === 'selected') {
			$message = __('Soon you will review the idea', 'picasso-ideas');
		} elseif ($status === 'not-selected') {
			$message = __('Sorry, this idea has not been pre-selected', 'review');
		} elseif ($status === 'review') {
			$message = __('You can review the idea', 'picasso-ideas');
		} elseif ($status === 'no-go') {
			$message = __('Idea not selected for project', 'picasso-ideas');
		} elseif ($status === 'in-project') {
			$message = __('This idea is in project now', 'picasso-ideas');
		}

		return $message;
	}
}
