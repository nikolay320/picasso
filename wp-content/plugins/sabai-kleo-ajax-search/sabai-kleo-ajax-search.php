<?php
/*
Plugin Name: Sabai Kleo Ajax Search

Plugin URI: Sabai Kleo Ajax Search

Description: Sabai Kleo Ajax Search

Version: 1.0.1

Author: phandung122

Author URI: http://www.upwork.com/o/profiles/users/_~016252273d5cf3683a/

License: 

*/

define( 'SABAI_KLEO_AJAX_SEARCH_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'SABAI_KLEO_AJAX_SEARCH_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/*
 * Include CSS JS files
 */
wp_enqueue_style( 'new_kleo_ajax_search', plugin_dir_url( __FILE__ ) . 'new_kleo_ajax_search.css'  );

function new_kleo_ajax_search_load_textdomain() {
	load_plugin_textdomain( 'new_kleo_ajax_search', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'new_kleo_ajax_search_load_textdomain' );

function new_kleo_ajax_search_add_query_vars_filter( $vars ){
  $vars[] = "search_terms";
  return $vars;
}
add_filter( 'query_vars', 'new_kleo_ajax_search_add_query_vars_filter' );

require_once( SABAI_KLEO_AJAX_SEARCH_PLUGIN_PATH .'bp-activity-as-shortcode.php' );

 // Add action after theme loaded to remove old kleo ajax function and replace with new one.
	// check action is exist.
	// remove old action
	// add with new action
 
 // New Kleo Ajax search function.
	// copy and paste old action.
	// add sabai search after user search
		// theme, review, question, answer
		// search page after single
add_action( 'after_setup_theme', 'new_kleo_ajax_search_setup' );
function new_kleo_ajax_search_setup () {
	if ( !has_action('wp_ajax_kleo_ajax_search') ) return;
	//write_log('actionn kleo_ajax_search removed');
	remove_action ( 'wp_ajax_kleo_ajax_search', 'kleo_ajax_search' );
	remove_action ( 'wp_ajax_nopriv_kleo_ajax_search', 'kleo_ajax_search' );
	add_action( 'wp_ajax_kleo_ajax_search', 'new_kleo_ajax_search' );
	add_action( 'wp_ajax_nopriv_kleo_ajax_search', 'new_kleo_ajax_search' );
}
 // Search Sabai entity by keywords and type (theme, question, review, answer), return array of entity (id title content)
	// query database to get entity id which have title or content have keywords, with type provide
	// return array include: id title content link
if(!function_exists('new_kleo_ajax_search'))
{
	function new_kleo_ajax_search()
	{
	//write_log('actionn new_kleo_ajax_search playing');
		//if "s" input is missing exit
		if( empty( $_REQUEST['s'] ) && empty( $_REQUEST['bbp_search'] ) ) die();

		if( ! empty( $_REQUEST['bbp_search'] ) ) {
			$search_string = $_REQUEST['bbp_search'];
		} else {
			$search_string = $_REQUEST['s'];
		}
	$search_string = rtrim($search_string);	
		
	$sabai_directory = new_kleo_ajax_search_sabai_query ( 'directory_listing', $search_string );
	$sabai_review = new_kleo_ajax_search_sabai_query ( 'directory_listing_review', $search_string );
	$sabai_question = new_kleo_ajax_search_sabai_query ( 'questions', $search_string );
	$sabai_answer = new_kleo_ajax_search_sabai_query ( 'questions_answers', $search_string );
	$activity_notes = new_kleo_ajax_search_get_bp_activity_update ($search_string);
		$output = "";
        $context = "any";
		$defaults = array(
            'numberposts' => 4,
			'posts_per_page' => 20,
            'post_type' => 'any',
            'post_status' => 'publish',
            'post_password' => '',
            'suppress_filters' => false,
            's' => $_REQUEST['s']
        );

        if ( isset( $_REQUEST['context'] ) && $_REQUEST['context'] != '' ) {
            $context = explode( ",", $_REQUEST['context'] );
            $defaults['post_type'] = $context;
        }
		if ( ! empty( $defaults['post_type'] ) && is_array( $defaults['post_type'] ) ) {
			foreach ( $defaults['post_type'] as $ptk => $ptv ) {
				if ( $ptv == 'forum' ) {
					unset( $defaults['post_type'][$ptk] );
					break;
				}
			}
		}

		$defaults =  apply_filters( 'kleo_ajax_query_args', $defaults);
		$the_query = new WP_Query( $defaults );
		$posts = $the_query->get_posts();

		$event = array(
            'numberposts' => 4,
			'posts_per_page' => 20,
            'post_type' => 'ajde_events',
            'post_status' => 'publish',
            'post_password' => '',
            'suppress_filters' => false,
            's' => $_REQUEST['s']
        );
		
		$event_query = new WP_Query( $event );
		$events = $event_query->get_posts();
		
        $members = array();
        $members['total'] = 0;
		$groups = array();
		$groups['total'] = 0;
        $forums = FALSE;


        if ( function_exists( 'bp_is_active' ) && ( $context == "any" || in_array( "members", $context ) ) ) {
            $members = bp_core_get_users(array('search_terms' => $search_string, 'per_page' => $defaults['numberposts'], 'populate_extras' => false));
        }

		if ( function_exists( 'bp_is_active' ) && bp_is_active("groups") && ( $context == "any" || in_array( "groups", $context ) ) ) {
			$groups = groups_get_groups(array('search_terms' => $search_string, 'per_page' => $defaults['numberposts'], 'populate_extras' => false));
		}

        if ( class_exists( 'bbPress' ) && ( $context == "any" || in_array( "forum", $context ) ) ) {
            $forums = kleo_bbp_get_replies( $search_string );
        }


        //if there are no posts, groups nor members
        if( empty( $posts ) && $members['total'] == 0 && $groups['total'] == 0 && ! $forums && empty( $sabai_directory ) && empty( $sabai_question )  && empty( $sabai_review )  && empty( $sabai_answer ) && empty ($events)  && empty ($activity_notes) ) {
			$output  = "<div class='kleo_ajax_entry ajax_not_found'>";
			$output .= "<div class='ajax_search_content'>";
			$output .= "<i class='icon icon-exclamation-sign'></i> ";
			$output .= __("Sorry, we haven't found anything based on your criteria.", 'kleo_framework');
			$output .= "<br>";
			$output .= __("Please try searching by different terms.", 'kleo_framework');
			$output .= "</div>";
			$output .= "</div>";
			echo $output;
			die();
		}

        //if there are members
        if ( $members['total'] != 0 ) {

            $output .= '<div class="kleo-ajax-part kleo-ajax-type-members">';
            $output .= '<h4><span>' . __("Members", 'kleo_framework') . '</span></h4>';
            foreach ( (array) $members['users'] as $member ) {
				
				$author = new BP_Core_User( $member-> ID );
				$image = $author->avatar_mini;
					
                //$image = '<img src="' . bp_core_fetch_avatar(array('item_id' => $member-> ID, 'width' => 25, 'height' => 25, 'html' => false)) . '" class="kleo-rounded" alt="">';
                if ( $update = bp_get_user_meta( $member-> ID, 'bp_latest_update', true ) ) {
                    $latest_activity = char_trim( trim( strip_tags( bp_create_excerpt( $update['content'], 50,"..." ) ) ) );
                } else {
                    $latest_activity = '';
                }
                $output .= "<div class ='kleo_ajax_entry'>";
                $output .= "<div class='ajax_search_image'>$image</div>";
                $output .= "<div class='ajax_search_content'>";
                $output .= "<a href='" . bp_core_get_user_domain( $member->ID ) . "' class='search_title'>";
                $output .= $member->display_name;
                $output .= "</a>";
                $output .= "<span class='search_excerpt'>";
                $output .= $latest_activity;
                $output .= "</span>";
                $output .= "</div>";
                $output .= "</div>";
            }
            $output .= "<a class='ajax_view_all' href='" . esc_url( bp_get_members_directory_permalink() . "?s=" . $search_string ) . "'>" . __('View member results','kleo_framework') . "</a>";
            $output .= "</div>";
        }

		//if there are groups
		if ( $groups['total'] != 0 ) {

			$output .= '<div class="kleo-ajax-part kleo-ajax-type-groups">';
			$output .= '<h4><span>' . __("Groups", 'kleo_framework') . '</span></h4>';
			foreach ( (array) $groups['groups'] as $group ) {
				$image = '<img src="' . bp_core_fetch_avatar(array('item_id' => $group->id, 'object'=>'group', 'width' => 25, 'height' => 25, 'html' => false)) . '" class="kleo-rounded" alt="">';
				$output .= "<div class ='kleo_ajax_entry'>";
				$output .= "<div class='ajax_search_image'>$image</div>";
				$output .= "<div class='ajax_search_content'>";
				$output .= "<a href='" . bp_get_group_permalink( $group ) . "' class='search_title'>";
				$output .= $group->name;
				$output .= "</a>";
				$output .= "</div>";
				$output .= "</div>";
			}
			$output .= "<a class='ajax_view_all' href='" . esc_url( bp_get_groups_directory_permalink() . "?s=" . $search_string ) . "'>" . __('View group results','kleo_framework') . "</a>";
			$output .= "</div>";
		}
		
		if ( ! empty ( $sabai_directory ) ) {
		//if ( true ) {
			$theme_title = __("Theme", 'new_kleo_ajax_search');
			
			$output .= '<div class="kleo-ajax-part kleo-ajax-type-sabai-theme">';
			$output .= "<h4><span>" . $theme_title . "</span></h4>";
			$count = 0;
			
			foreach ($sabai_directory as $post) {

					$count++;
					if ($count > 4) {
						continue;
					}
					$format = 'sabai-directory';
					
					$image_link = new_kleo_ajax_search_get_sabai_entity_photo ( 'directory_listing', $post['id'] );
					if ( ($image_link) ) {
                        $image = aq_resize( $image_link, 44, 44, true, true, true );
                        if( ! $image ) {
                            $image = $image_link;
                        }
                        $image = '<img src="'. $image .'" class="kleo-rounded">';
                    } else {
						$image = "<i class='icon icon-book'></i>";
					}
					
                    $excerpt = "";

                    if ( ! empty($post['content'] )) {
	                    $excerpt = $post['content'];
	                    $excerpt = preg_replace("/\[(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?/s",'', $excerpt);
	                    $excerpt = char_trim( trim(strip_tags($excerpt)), 40, "..." );
                    }
                    $link = $post['link'];
                    $classes = "format-" . $format;
                    $output .= "<div class ='kleo_ajax_entry $classes'>";
                    $output .= "<div class='ajax_search_image'>$image</div>";
                    $output .= "<div class='ajax_search_content'>";
                    $output .= "<a href='$link' class='search_title'>";
                    $output .= $post['title'];
                    $output .= "</a>";
                    $output .= "<span class='search_excerpt'>";
                    $output .= $excerpt;
                    $output .= "</span>";
                    $output .= "</div>";
                    $output .= "</div>";
                }
			
			$output .= '</div>';
			
			$output .= "<a class='ajax_view_all' href='" . new_kleo_ajax_search_get_sabai_search_page ( 'directory_listing' ). $search_string . "'>" . __('View all themes', 'new_kleo_ajax_search') . "</a>";
		}
		
		if ( ! empty ( $sabai_review ) ) {
		//if ( true ) {
			$theme_title = __('Review', 'new_kleo_ajax_search');
			
			$output .= '<div class="kleo-ajax-part kleo-ajax-type-sabai-review">';
			$output .= "<h4><span>" . $theme_title . "</span></h4>";
			$count = 0;
			
			foreach ($sabai_review as $post) {

					$count++;
					if ($count > 4) {
						continue;
					}
					$format = 'sabai-review';
					
					$image_link = new_kleo_ajax_search_get_sabai_entity_photo ( 'directory_listing_review', $post['id'] );
					if ( ($image_link) ) {
                        $image = aq_resize( $image_link, 44, 44, true, true, true );
                        if( ! $image ) {
                            $image = $image_link;
                        }
                        $image = '<img src="'. $image .'" class="kleo-rounded">';
                    } else {
						$image = "<i class='icon icon-link'></i>";
					}
					
                    $excerpt = "";

                    if ( ! empty($post['content']) ) {
	                    $excerpt = $post['content'];
	                    $excerpt = preg_replace("/\[(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?/s",'', $excerpt);
	                    $excerpt = char_trim( trim(strip_tags($excerpt)), 40, "..." );
                    }
                    $link = $post['link'];
                    $classes = "format-" . $format;
                    $output .= "<div class ='kleo_ajax_entry $classes'>";
                    $output .= "<div class='ajax_search_image'>$image</div>";
                    $output .= "<div class='ajax_search_content'>";
                    $output .= "<a href='$link' class='search_title'>";
                    $output .= $post['title'];
                    $output .= "</a>";
                    $output .= "<span class='search_excerpt'>";
                    $output .= $excerpt;
                    $output .= "</span>";
                    $output .= "</div>";
                    $output .= "</div>";
                }
			
			$output .= '</div>';
			
			$output .= "<a class='ajax_view_all' href='" . new_kleo_ajax_search_get_sabai_search_page ( 'directory_listing_review' ). $search_string . "'>" . __('View all reviews', 'new_kleo_ajax_search') . "</a>";
		}
		
		if ( ! empty ( $sabai_question ) ) {
		//if ( true ) {
			$theme_title = __('Question', 'new_kleo_ajax_search');
			
			$output .= '<div class="kleo-ajax-part kleo-ajax-type-sabai-question">';
			$output .= "<h4><span>" . $theme_title . "</span></h4>";
			$count = 0;
			
			foreach ($sabai_question as $post) {

					$count++;
					if ($count > 4) {
						continue;
					}
					$format = 'sabai-question';
					
					$image_link = new_kleo_ajax_search_get_sabai_entity_photo ( 'question', $post['id'] );
					if ( ($image_link) ) {
                        $image = aq_resize( $image_link, 44, 44, true, true, true );
                        if( ! $image ) {
                            $image = $image_link;
                        }
                        $image = '<img src="'. $image .'" class="kleo-rounded">';
                    } else {
						$image = "<i class='icon icon-cog'></i>";
					}
					
                    $excerpt = "";
                    if ( ! empty($post['content']) ) {
	                    $excerpt = $post['content'];
	                    $excerpt = preg_replace("/\[(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?/s",'', $excerpt);
	                    $excerpt = char_trim( trim(strip_tags($excerpt)), 40, "..." );
                    }
                    $link = $post['link'];
                    $classes = "format-" . $format;
                    $output .= "<div class ='kleo_ajax_entry $classes'>";
                    $output .= "<div class='ajax_search_image'>$image</div>";
                    $output .= "<div class='ajax_search_content'>";
                    $output .= "<a href='$link' class='search_title'>";
                    $output .= $post['title'];
                    $output .= "</a>";
                    $output .= "<span class='search_excerpt'>";
                    $output .= $excerpt;
                    $output .= "</span>";
                    $output .= "</div>";
                    $output .= "</div>";
                }
			
			$output .= '</div>';
			
			$output .= "<a class='ajax_view_all' href='" . new_kleo_ajax_search_get_sabai_search_page ( 'question' ). $search_string . "'>" . __('View all questions', 'new_kleo_ajax_search') . "</a>";
		}
		
		if ( ! empty ( $sabai_answer ) ) {
		//if ( true ) {
			$theme_title = __('Answer', 'new_kleo_ajax_search');
			
			$output .= '<div class="kleo-ajax-part kleo-ajax-type-sabai-answer">';
			$output .= "<h4><span>" . $theme_title . "</span></h4>";
			$count = 0;
			
			foreach ($sabai_answer as $post) {

					$count++;
					if ($count > 4) {
						continue;
					}
					$format = 'sabai-answer';
					
					$image_link = new_kleo_ajax_search_get_sabai_entity_photo ( 'question_answer', $post['id'] );
					if ( ($image_link) ) {
                        $image = aq_resize( $image_link, 44, 44, true, true, true );
                        if( ! $image ) {
                            $image = $image_link;
                        }
                        $image = '<img src="'. $image .'" class="kleo-rounded">';
                    } else {
						$image = "<i class='icon icon-reply'></i>";
					}
					
                    $excerpt = "";
                    if ( ! empty($post['content']) ) {
	                    $excerpt = $post['content'];
	                    $excerpt = preg_replace("/\[(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?/s",'', $excerpt);
	                    $excerpt = char_trim( trim(strip_tags($excerpt)), 40, "..." );
                    }
                    $link = $post['link'];
                    $classes = "format-" . $format;
                    $output .= "<div class ='kleo_ajax_entry $classes'>";
                    $output .= "<div class='ajax_search_image'>$image</div>";
                    $output .= "<div class='ajax_search_content'>";
                    $output .= "<a href='$link' class='search_title'>";
                    $output .= $post['title'];
                    $output .= "</a>";
                    $output .= "<span class='search_excerpt'>";
                    $output .= $excerpt;
                    $output .= "</span>";
                    $output .= "</div>";
                    $output .= "</div>";
                }
			
			$output .= '</div>';
			
			$output .= "<a class='ajax_view_all' href='" . new_kleo_ajax_search_get_sabai_search_page ( 'question_answer' ). $search_string . "'>" . __('View all answers', 'new_kleo_ajax_search') . "</a>";
		}
		
		//events
		if( ! empty( $events ) ) {
            $theme_title = __('Events', 'new_kleo_ajax_search');
			
			$output .= '<div class="kleo-ajax-part kleo-ajax-type-sabai-event">';
			$output .= "<h4><span>" . $theme_title . "</span></h4>";
			$count = 0;
                foreach ($events as $post) {

					$count++;
					if ($count > 4) {
						continue;
					}
					$format = 'event';
                    //$format = get_post_format( $post->ID );
                    if ( $img_url = kleo_get_post_thumbnail_url( $post->ID ) ) {
                        $image = aq_resize( $img_url, 44, 44, true, true, true );
                        if( ! $image ) {
                            $image = $img_url;
                        }
                        $image = '<img src="'. $image .'" class="kleo-rounded">';
                    } else {
                        if ($format == 'video') {
                            $image = "<i class='icon icon-video'></i>";
                        } elseif ($format == 'image' || $format == 'gallery') {
                            $image = "<i class='icon icon-picture'></i>";
                        } else {
                            $image = "<i class='icon icon-calendar'></i>";
                        }
                    }

                    $excerpt = "";

                    if ( ! empty($post->post_content) ) {
	                    $excerpt = $post->post_content;
	                    $excerpt = preg_replace("/\[(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?/s",'', $excerpt);
	                    $excerpt = char_trim( trim(strip_tags($excerpt)), 40, "..." );
                    }
                    //$link = apply_filters('kleo_custom_url', get_permalink($post->ID));
					$link = new_kleo_ajax_search_get_event_link ($post);
                    $classes = "format-" . $format;
                    $output .= "<div class ='kleo_ajax_entry $classes'>";
                    $output .= "<div class='ajax_search_image'>$image</div>";
                    $output .= "<div class='ajax_search_content'>";
                    //$output .= "<a href='$link' class='search_title'>";
                    //$output .= get_the_title($post->ID);
                    //$output .= "</a>";
					$output .= $link;
                    $output .= "<span class='search_excerpt'>";
                    $output .= $excerpt;
                    $output .= "</span>";
                    $output .= "</div>";
                    $output .= "</div>";
					$output .= new_kleo_ajax_search_get_event_activity_content_body ($post);
                }
                $output .= '</div>';
            

            $output .= "<a class='ajax_view_all' href='" . esc_url( home_url( '/' ) . 'evenements-2/') . $search_string . "'>" . __('View all events', 'new_kleo_ajax_search') . "</a>";
        }
		
		//notes
		if( ! empty( $activity_notes ) ) {
            $theme_title = __('Notes', 'new_kleo_ajax_search');
			
			$output .= '<div class="kleo-ajax-part kleo-ajax-type-activity-notes">';
			$output .= "<h4><span>" . $theme_title . "</span></h4>";
			$count = 0;
                foreach ($activity_notes as $post) {

					$count++;
					if ($count > 4) {
						continue;
					}
					$format = 'notes';
                    //$format = get_post_format( $post->ID );
					
					$image = "<i class='icon icon-edit'></i>";
					
					$author = new BP_Core_User( $post['author'] );
					if ( $author ) {
                        $image = $author->avatar_mini;
                    } else {
						$image = "<i class='icon icon-edit'></i>";
					}
					
                    $excerpt = "";
                    if ( ! empty($post['content']) ) {
	                    $excerpt = $post['content'];
	                    $excerpt = preg_replace("/\[(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?/s",'', $excerpt);
	                    $excerpt = char_trim( trim(strip_tags($excerpt)), 40, "..." );
                    }
					
                    $title = "";
                    if ( ! empty($post['title']) ) {
	                    $title = $post['title'];
	                    $title = preg_replace("/\[(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?/s",'', $title);
	                    $title = char_trim( trim(strip_tags($title)), 40, "..." );
                    }
					
                    $link = $post['link'];
                    $classes = "format-" . $format;
                    $output .= "<div class ='kleo_ajax_entry $classes'>";
                    $output .= "<div class='ajax_search_image'>$image</div>";
                    $output .= "<div class='ajax_search_content'>";
                    $output .= "<a href='$link' class='search_title'>";
                    $output .= $title;
                    $output .= "</a>";
                    $output .= "<span class='search_excerpt'>";
                    $output .= $excerpt;
                    $output .= "</span>";
                    $output .= "</div>";
                    $output .= "</div>";
                }
                $output .= '</div>';
            

            $output .= "<a class='ajax_view_all' href='" . esc_url( home_url( '/' ) . 'notes/?search_terms=') . $search_string . "'>" . __('View all notes', 'new_kleo_ajax_search') . "</a>";
        }
		
		//if there are posts
        if( ! empty( $posts ) ) {
            $post_types = array();
            $post_type_obj = array();
            foreach ( $posts as $post ) {
                $post_types[$post->post_type][] = $post;
                if (empty($post_type_obj[$post->post_type])) {
                    $post_type_obj[$post->post_type] = get_post_type_object($post->post_type);
                }
            }
            foreach ($post_types as $ptype => $post_type) {
                $output .= '<div class="kleo-ajax-part kleo-ajax-type-' . esc_attr( $post_type_obj[$ptype]->name ) . '">';
                if (isset($post_type_obj[$ptype]->labels->name)) {
                    $output .= "<h4><span>" . $post_type_obj[$ptype]->labels->name . "</span></h4>";
                } else {
                    $output .= "<hr>";
                }
				$count = 0;
                foreach ($post_type as $post) {

					$count++;
					if ($count > 4) {
						continue;
					}
                    $format = get_post_format( $post->ID );
                    if ( $img_url = kleo_get_post_thumbnail_url( $post->ID ) ) {
                        $image = aq_resize( $img_url, 44, 44, true, true, true );
                        if( ! $image ) {
                            $image = $img_url;
                        }
                        $image = '<img src="'. $image .'" class="kleo-rounded">';
                    } else {
                        if ($format == 'video') {
                            $image = "<i class='icon icon-video'></i>";
                        } elseif ($format == 'image' || $format == 'gallery') {
                            $image = "<i class='icon icon-picture'></i>";
                        } else {
                            $image = "<i class='icon icon-link'></i>";
                        }
                    }

                    $excerpt = "";

                    if ( ! empty($post->post_content) ) {
	                    $excerpt = $post->post_content;
	                    $excerpt = preg_replace("/\[(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?/s",'', $excerpt);
	                    $excerpt = char_trim( trim(strip_tags($excerpt)), 40, "..." );
                    }
                    $link = apply_filters('kleo_custom_url', get_permalink($post->ID));
                    $classes = "format-" . $format;
                    $output .= "<div class ='kleo_ajax_entry $classes'>";
                    $output .= "<div class='ajax_search_image'>$image</div>";
                    $output .= "<div class='ajax_search_content'>";
                    $output .= "<a href='$link' class='search_title'>";
                    $output .= get_the_title($post->ID);
                    $output .= "</a>";
                    $output .= "<span class='search_excerpt'>";
                    $output .= $excerpt;
                    $output .= "</span>";
                    $output .= "</div>";
                    $output .= "</div>";
                }
                $output .= '</div>';
            }

            $output .= "<a class='ajax_view_all' href='" . esc_url( home_url( '/' ) . '?s=' . $search_string ) . "'>" . __('View all results', 'kleo_framework') . "</a>";
        }

        /* Forums topics search */
        if( ! empty( $forums ) ) {
            $output .= '<div class="kleo-ajax-part kleo-ajax-type-forums">';
            $output .= '<h4><span>' . __("Forums", 'kleo_framework') . '</span></h4>';

			$i = 0;
            foreach ( $forums as $fk => $forum ) {

				$i++;
				if ($i <= 4 ) {
					$image = "<i class='icon icon-chat-1'></i>";

					$output .= "<div class ='kleo_ajax_entry'>";
					$output .= "<div class='ajax_search_image'>$image</div>";
					$output .= "<div class='ajax_search_content'>";
					$output .= "<a href='" . $forum['url'] . "' class='search_title'>";
					$output .= $forum['name'];
					$output .= "</a>";
					//$output .= "<span class='search_excerpt'>";
					//$output .= $latest_activity;
					//$output .= "</span>";
					$output .= "</div>";
					$output .= "</div>";
				}
            }
            $output .= "<a class='ajax_view_all' href='" . esc_url( bbp_get_search_url() . "?bbp_search=" . $search_string ) . "'>" . __('View forum results','kleo_framework') . "</a>";
            $output .= "</div>";
        }


		echo $output;
		die();
	}
}

// query sabai entity 
function new_kleo_ajax_search_sabai_query ( $type, $keywords ) {
	$query = array();
	$sabai_result_query = array();
	
	global $wpdb;
	$table_title_name = $wpdb->prefix . 'sabai_content_post';
	$query_string = "SELECT post_id FROM " . $table_title_name .
		" WHERE post_title LIKE '%{$keywords}%' AND post_entity_bundle_type ='" . $type ."'AND post_status ='published'";
	$sabai_query = $wpdb->get_results( $query_string );
	//write_log ($sabai_query);
	foreach ($sabai_query as $entity) {
		$query[] = $entity->post_id;
	}
	
	$table_content_name = $wpdb->prefix . 'sabai_entity_field_content_body';
	$bundle_id = new_kleo_ajax_search_get_sabai_bundle_id_from_type ( $type );
	$query_string = "SELECT entity_id FROM " . $table_content_name .
		" WHERE value LIKE '%{$keywords}%' AND bundle_id =" . $bundle_id ;
	$sabai_query = $wpdb->get_results( $query_string );
	//write_log ($sabai_query);
	foreach ($sabai_query as $entity) {
		if (new_kleo_ajax_search_is_entity_publish ( $entity->entity_id ))
			$query[] = $entity->entity_id;
	}
	
	$query = array_unique($query);
	rsort($query);
	//write_log($query);
	foreach ( $query as $entity_id ) {
		$entity = array (
			'id' => $entity_id,
			'title' => new_kleo_ajax_search_get_sabai_title ( $entity_id ),
			'content' => new_kleo_ajax_search_get_sabai_content ( $entity_id ),
			'link' => new_kleo_ajax_search_get_sabai_entity_link ( $entity_id ),
		);
		$sabai_result_query[] = $entity;
	}
	//write_log($sabai_result_query);
	return $sabai_result_query;
}

// Get Sabai entity status
function new_kleo_ajax_search_get_sabai_entity_status ( $entity_id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'sabai_content_post';
	$query_string = "SELECT post_status FROM " . $table_name .
		' WHERE post_id=' . $entity_id;
	$sabai_query = $wpdb->get_results( $query_string );
	return $sabai_query[0]->post_status;
}

function new_kleo_ajax_search_is_entity_publish ( $entity_id ) {
	$status = new_kleo_ajax_search_get_sabai_entity_status ( $entity_id );
	if ($status == 'published') {
		return 1;
	} else return 0;
}
// Get Sabai title by entity id 
function new_kleo_ajax_search_get_sabai_title ( $entity_id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'sabai_content_post';
	$query_string = "SELECT post_title FROM " . $table_name .
		' WHERE post_id=' . $entity_id;
	$sabai_query = $wpdb->get_results( $query_string );
	return $sabai_query[0]->post_title;
}
// Get Sabai content by entity id
function new_kleo_ajax_search_get_sabai_content ( $entity_id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'sabai_entity_field_content_body';
	$query_string = "SELECT value FROM " . $table_name .
		' WHERE entity_id=' . $entity_id;
	$sabai_query = $wpdb->get_results( $query_string );
	if ($sabai_query[0]->value) return $sabai_query[0]->value;
	
	
	$table_name = $wpdb->prefix . 'sabai_entity_field_field_contexte';
	$query_string = "SELECT value FROM " . $table_name .
		' WHERE entity_id=' . $entity_id;
	$sabai_query = $wpdb->get_results( $query_string );
	return $sabai_query[0]->value;
}
// Get Sabai link by type and entity id
function new_kleo_ajax_search_get_sabai_entity_link ( $entity_id ) {
	$bundle_id = new_kleo_ajax_search_get_sabai_bundle_id ( $entity_id );
	//write_log('bundle id:'.$bundle_id);
	$bundle_path = new_kleo_ajax_search_get_sabai_bundle_path ( $bundle_id );
	//write_log('bundle_path :'.$bundle_path);
	return get_site_url().$bundle_path.'/'.$entity_id;
}
// Get bundle path by bundle id
function new_kleo_ajax_search_get_sabai_bundle_path ( $bundle_id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'sabai_entity_bundle';
	$query = 'SELECT * FROM ' . $table_name .
		' WHERE bundle_id=' . $bundle_id;
	$bundle_info_query = $wpdb->get_results( $query );
	$bundle_info = unserialize($bundle_info_query[0]->bundle_info);
	if ($bundle_info['permalink_path'])return $bundle_info['permalink_path'];
	return $bundle_info_query[0]->bundle_path;
}
// Get bundle id from bundle type
function new_kleo_ajax_search_get_sabai_bundle_id_from_type ( $bundle_type ) {
	$bundle_id = 0;
	global $wpdb;
	$table_name = $wpdb->prefix . 'sabai_entity_bundle';
	$query = 'SELECT bundle_id FROM ' . $table_name .
		' WHERE bundle_type=\'' . $bundle_type .'\'';
	$bundle_id_query = $wpdb->get_results( $query );
	$bundle_id = $bundle_id_query[0]->bundle_id;
	return $bundle_id;
}
// Get sabai parent (for review and answer)
 function new_kleo_ajax_search_get_sabai_parent ( $entity_id ) {
	$parent_id = 0;
	global $wpdb;
	$table_name = $wpdb->prefix . 'sabai_entity_field_content_parent';
	$query = 'SELECT value FROM ' . $table_name .
		' WHERE entity_id=' . $entity_id;
	$value_query = $wpdb->get_results( $query );
	$parent_id = $value_query[0]->value;
	return $parent_id;
 }
 
 // Get sabai bundle id by entity id
 function new_kleo_ajax_search_get_sabai_bundle_id ( $entity_id ) {
	$bundle_type = '';
	global $wpdb;
	$table_name = $wpdb->prefix . 'sabai_content_post';
	$query = 'SELECT post_entity_bundle_type FROM ' . $table_name .
		' WHERE post_id=' . $entity_id;
	$bundle_type_query = $wpdb->get_results( $query );
	$bundle_type = $bundle_type_query[0]->post_entity_bundle_type;
	return new_kleo_ajax_search_get_sabai_bundle_id_from_type ( $bundle_type );
 }
 
 // Get sabai slug by id
 function new_kleo_ajax_search_get_sabai_slug ( $entity_id ) {
	$slug = '';
	global $wpdb;
	$table_name = $wpdb->prefix . 'sabai_content_post';
	$query = 'SELECT post_slug FROM ' . $table_name .
		' WHERE post_id=' . $entity_id;
	$slug_query = $wpdb->get_results( $query );
	$slug = $slug_query[0]->post_slug;
	return $slug;
 }
 
 // Get sabai search page link
 function new_kleo_ajax_search_get_sabai_search_page ( $type ) {
	 switch ( $type ) {
		 case 'directory_listing':
			return get_site_url().new_kleo_ajax_search_get_sabai_bundle_path (2).'/?keywords=';
		 break;
		 case 'question':
			return get_site_url().new_kleo_ajax_search_get_sabai_bundle_path (8).'/?keywords=';
		 break;
		 case 'directory_listing_review':
			return get_site_url().new_kleo_ajax_search_get_sabai_bundle_path (3).'/?keywords=';
		 break;
		 case 'question_answer':
			return get_site_url().new_kleo_ajax_search_get_sabai_bundle_path (9).'/?keywords=';
		 break;
		 default:
		 break;
	 }
 }
 
 /*
helper function that generates event title with a # hefre	
*/
function new_kleo_ajax_search_get_event_link($event){
	//return '<a class="eventon_events_list desc_trig" event_id="'.$event[id].'" href="' . get_permalink($event['id']) . '" > ' . __($event['title']) . ' </a>';	
	$link = '<a class="bb_event-activity_header search_title" id="activity_event_trigger_'.$event->ID.'" href="#" >' . __($event->post_title) . ' </a>';	
	return $link;	
}

/*
This function returns content of an event. 
uses global $evention->generator->get_single_event_data method	
*/
function new_kleo_ajax_search_get_event_activity_content_body($event){
	global $activities_template, $eventon;			
		$event_id = $event->ID;
		
		if( $event_id ){
			$event_data =  $eventon->evo_generator->get_single_event_data($event_id);

			if( $event_data ){			
				$content .= "<div style='display: none;' id='activity_event_activity_event_trigger_{$event_id}' class='eventon_events_list desc_trig'>" . $event_data[0]['content'] . "</div>";
			}
		}
	return $content;
}

function new_kleo_ajax_search_get_sabai_entity_photo ( $type, $entity_id ) {
	$file_id = 0;
	global $wpdb;
	switch ($type) {
		case 'question':
			$table_name = $wpdb->prefix . 'sabai_entity_field_field_images';
		break;
		case 'question_answer':
			$table_name = $wpdb->prefix . 'entity_field_field_imageresponse';
		break;
		case 'directory_listing':
			$table_name = $wpdb->prefix . 'sabai_entity_field_file_image';
			$entity_id = new_kleo_ajax_search_get_directory_photo_entity ( 'directory_listing', $entity_id );
		break;
		case 'directory_listing_review':
			$table_name = $wpdb->prefix . 'sabai_entity_field_file_image';
			$entity_id = new_kleo_ajax_search_get_directory_photo_entity ( 'directory_listing_review', $entity_id );
		break;
	}
	$query = 'SELECT file_id FROM ' . $table_name .
		' WHERE entity_id=' . $entity_id;
	$file_id_query = $wpdb->get_results( $query );
	$file_id = $file_id_query[0]->file_id;
	if ($file_id) {
		return new_kleo_ajax_search_get_sabai_entity_photo_link ( $file_id );
	}
	else return 0;
}

function new_kleo_ajax_search_get_sabai_entity_photo_link ( $file_id ) {
	
	global $wpdb;
	$table_name = $wpdb->prefix . 'sabai_file_file';
	$query = 'SELECT file_name FROM ' . $table_name .
		' WHERE file_id=' . $file_id;
	$file_name_query = $wpdb->get_results( $query );
	$file_name = $file_name_query[0]->file_name;
	return get_site_url().'/wp-content/sabai/File/thumbnails/'.$file_name;
}

function new_kleo_ajax_search_get_directory_photo_entity ( $type, $entity_id ) {
	$photo_entity_id = 0;
	global $wpdb;
	switch ( $type ) {
		case 'directory_listing':
			$table_name = $wpdb->prefix . 'sabai_entity_field_content_parent';
		break;
		case 'directory_listing_review':
			$table_name = $wpdb->prefix . 'sabai_entity_field_content_reference';
		break;
	}
	
	$query = 'SELECT entity_id FROM ' . $table_name .
		' WHERE value=' . $entity_id;
	$entity_id_query = $wpdb->get_results( $query );
	
	foreach ( $entity_id_query as $entity ) {
		if ( $photo_entity_id ) continue;
		$id = $entity->entity_id;
		$table_content_name = $wpdb->prefix . 'sabai_content_post';
		$query = 'SELECT post_entity_bundle_type FROM ' . $table_content_name .
			' WHERE post_id=' . $id;
		$entity_type_query = $wpdb->get_results( $query );
		if ($entity_type_query[0]->post_entity_bundle_type == 'directory_listing_photo') $photo_entity_id = $id;
	}
	return $photo_entity_id;
}

function new_kleo_ajax_search_get_bp_activity_update ($search_string) {
	global $wpdb, $bp;
	$activity_notes = array();
	$table_name = $bp->activity->table_name;	
			/**
			 * SELECT DISTINCT a.id 
			 * FROM wp_bp_activity a 
			 * WHERE 
			 *		a.is_spam = 0 
			 *	AND a.content LIKE '%nothing%' 
			 *  AND a.hide_sitewide = 0 
			 *  AND a.type NOT IN ('activity_comment', 'last_activity') 
			 * 
			 * ORDER BY a.date_recorded DESC LIMIT 0, 21
			 */
			 
	$sql = " SELECT *";
	$sql .= " FROM ".$table_name."
			WHERE 
				1=1 
				AND is_spam = 0 
				AND content LIKE '%{$search_string}%'
				AND hide_sitewide = 0 
				AND type = 'activity_update' 
			ORDER BY date_recorded DESC LIMIT 0, 21
		";
	$query = $wpdb->get_results ( $sql );
	foreach ( $query as $activity ) {
		$note = array(
			'id' => $activity->id,
			'content' => '',
			'title'	=> $activity->content,
			'link'	=> get_site_url().'/activity/p/'.$activity->id,
			'author' => $activity->user_id,
		);
		$activity_notes[] = $note;
	}
	return $activity_notes;
}