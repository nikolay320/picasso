<?php 

 
add_filter( 'bp_blogs_record_post_post_types', 'record_new_idea_submission' );
function record_new_idea_submission( $types ) {
	$types[] = 'idea';
return $types;
}


add_filter( 'bp_blogs_record_comment_post_types', 'record_new_idea_comment' );
function record_new_idea_comment( $types ) {
	$types[] = 'idea';
return $types;
}

add_filter('bp_blogs_activity_new_post_action', 'idea_activity_action', 1, 3);
/* Filter for action for post type reward*/
function idea_activity_action($activity_action, $post, $post_permalink)
{
    global $bp;
    if ($post->post_type == 'idea') {
		//$activity_action = sprintf(__('%1$s posted a new reward : %2$s', 'buddypress'), bp_core_get_userlink((int) $post->post_author), '<a href="' . $post_permalink . '">' . $post->post_title . '</a>');
    	$activity_action = sprintf(
			__( '%1$s posted a new idea %2$s' , 'marylink-custom-plugin'),
				bp_core_get_userlink((int) $post->post_author), 
				'<a href="'.$post_permalink.'">'.$post->post_title.'</a>'
			);
		
		$idea_campaign = $post->post_parent;
		$campaign_link = get_permalink($idea_campaign);
		$campaign_title = get_the_title($idea_campaign);
		if ($idea_campaign) {
			$activity_action = sprintf(
			__( '%1$s posted a new idea %2$s in campaign %3$s' , 'marylink-custom-plugin'),
				bp_core_get_userlink((int) $post->post_author), 
				'<a href="'.$post_permalink.'">'.$post->post_title.'</a>',
				'<a href="'.$campaign_link.'">'.$campaign_title.'</a>'
			);
		}
    }
       	return $activity_action; 
}

add_filter('bp_blogs_activity_new_comment_action', 'idea_activity_comment_action', 1, 3);
/* Filter for action for post type reward*/
function idea_activity_comment_action ($action, $comment, $post_url )
{
    global $bp;
	$post_post_type = get_post_type ( $comment->comment_post_ID );
    if ($post_post_type == 'idea') {
		//$activity_action = sprintf(__('%1$s posted a new reward : %2$s', 'buddypress'), bp_core_get_userlink((int) $post->post_author), '<a href="' . $post_permalink . '">' . $post->post_title . '</a>');
    	$idea_id = $comment->comment_post_ID;
		$idea_link = get_permalink($idea_id);
		$idea_title = get_the_title($idea_id);
		$campaign_id = wp_get_post_parent_id ( $idea_id );
		$campaign_link = get_permalink($campaign_id);
		$campaign_title = get_the_title($campaign_id);
		//X ( username) has made a comment on idea A (name of idea in link) which is part of campaign C
		if($campaign_id) {
			$action = sprintf(
				__( '%1$s has made a comment on idea %2$s which is part of campaign %3$s' , 'marylink-custom-plugin'),
					bp_core_get_userlink((int) $comment->user_id), 
					'<a href="'.$idea_link.'">'.$idea_title.'</a>',
					'<a href="'.$campaign_link.'">'.$campaign_title.'</a>'
				);
		} else {
			$action = sprintf(
				__( '%1$s has made a comment on idea %2$s' , 'marylink-custom-plugin'),
					bp_core_get_userlink((int) $comment->user_id), 
					'<a href="'.$idea_link.'">'.$idea_title.'</a>'
				);
		}
	}
       	return $action; 
    
}

// Get bundle path by bundle id
function idea_get_new_idea_activity_id ( $idea_id ) {
	global $wpdb;
	$idea_activity_id = 0;
	$table_name = $wpdb->prefix . 'bp_activity';
	$query = 'SELECT id FROM ' . $table_name .
		' WHERE secondary_item_id = ' .$idea_id. ' AND type = \'new_blog_post\'';
	$idea_activity_id_query = $wpdb->get_results( $query );
	$idea_activity_id = $idea_activity_id_query[0]->id;
	return $idea_activity_id;
}

function idea_update_idea_activity_content ( $idea_activity_id, $content ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'bp_activity';
	$wpdb->update( 
		$table_name, 
		array( 
			'content' => $content,	// string
		), 
		array( 'id' => $idea_activity_id ), 
		array( 
			'%s',	// value1
		), 
		array( '%d' ) 
	);
	return;
}

add_action( 'after_save_idea', 'idea_update_activity_content', 10, 2 );
function idea_update_activity_content ( $post_id, $post ) {
	$idea_id = $post_id;
	$idea_activity_id = idea_get_new_idea_activity_id ( $idea_id );
	
	$content = $post->post_content;
	
	$idea_images = get_post_meta($idea_id, '_idea_images', true);
	$idea_files = get_post_meta($idea_id, '_idea_files', true);
	$idea_videos = get_post_meta($idea_id, '_idea_videos', true);
	$idea_youtube = get_post_meta($idea_id, '_idea_youtube', true);
	
	foreach ($idea_images as $idea_image_id => $idea_image_url) {
		$content .= '<br><img src="'.$idea_image_url.'">';
	}
	
	foreach ($idea_videos as $idea_video_id => $idea_video_url) {
		$content .= '<div class="sabai_mediapress_video" id=""><div class="'.$idea_video_url.'"></div></div>';
	}
	
	if($idea_youtube) {
		preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $idea_youtube, $match);
		$idea_youtube_video_id = $match[1];
		$content .= '<div class="sabai-idea-youtube-video"><div class="'.$idea_youtube_video_id.'"></div></div>';
	}
	
	idea_update_idea_activity_content($idea_activity_id, $content);
}

//add_post_type_support( 'idea', 'buddypress-activity' ); //buddypress support for ideas post type 
//add_action( 'bp_init', 'customize_page_tracking_args' ); //custom activity posts
function customize_page_tracking_args() {
    // Check if the Activity component is active before using it.
    if ( ! bp_is_active( 'activity' ) ) {
        return;
    }
 
bp_activity_set_post_type_tracking_args( 'idea', array(
        'action_id'                         => 'new_idea',
        'bp_activity_admin_filter'          => __( 'Published a new idea' , 'marylink-custom-plugin'),
        'bp_activity_front_filter'          => __( 'Ideas' , 'marylink-custom-plugin'),
        'bp_activity_new_post'              => __( '%1$s posted a new <a href="%2$s">idea</a>' , 'marylink-custom-plugin'),
        'bp_activity_new_post_ms'           => __( '%1$s posted a new <a href="%2$s">idea</a>, on the site %3$s' , 'marylink-custom-plugin'),
        'contexts'                          => array( 'activity', 'member' ),
        'comment_action_id'                 => 'new_idea_comment',
        'bp_activity_comments_admin_filter' => __( 'Commented a page' , 'marylink-custom-plugin'),
        'bp_activity_comments_front_filter' => __( 'Pages Comments' , 'marylink-custom-plugin'),
        'bp_activity_new_comment'           => __( '%1$s commented on the <a href="%2$s">page</a>' , 'marylink-custom-plugin'),
        'bp_activity_new_comment_ms'        => __( '%1$s commented on the <a href="%2$s">page</a>, on the site %3$s' , 'marylink-custom-plugin'),
        'position'                          => 100,
    ) );
}


function profile_new_idea_item() {

	//if (bp_displayed_user_id() == get_current_user_id()) {
	$Ideas = __('Ideas', 'marylink-custom-plugin');
    bp_core_new_nav_item(
    array(
        'name'                => $Ideas,
        'slug'                => 'idea_tab',
        'default_subnav_slug' => 'idea_sub_tab', // We add this submenu item below 
        'screen_function'     => 'view_manage_idea_main'
    )
    );
    //}

}

add_action( 'bp_setup_nav', 'profile_new_idea_item', 10 ); //user profile subtab for Ideas


function customPlugBPnav($name)
    {
        add_action('bp_template_content', 'bpNavCust' . $name . 'Content');
        bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
    //subtab support function
    }


function view_manage_idea_main() {
    add_action( 'bp_template_content', 'bp_template_content_idea_function' );
    bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));

    customPlugBPnav('Ideas');

    //Ideas subtab [user profile]

}

function bpNavCustIdeasContent()
    {

    	require( dirname( __FILE__ ) . '/idea_temp.php' );

    	//template for idea subtab
    }

function bpNavCustCommentsContent()
    {

    	require( dirname( __FILE__ ) . '/idea_temp_comments.php' );
    
    	 //template for idea comments subtab

    }


function bpNavCustBookmarksContent()
    {

    	require( dirname( __FILE__ ) . '/idea_temp_bookmarks.php' );

    	//template for idea bookmarks subtab
    }


function bpNavCustUserReviewContent()
    {

    	require( dirname( __FILE__ ) . '/idea_temp_userreview.php' );

    	//template for idea user review subtab
    }


function bpNavCustExpertReviewContent()
    {

    	require( dirname( __FILE__ ) . '/idea_temp_expertreview.php' );

    	//template for idea expert review subtab
    }

function bp_template_content_idea_function() {
    if ( ! is_user_logged_in() ) {
        wp_login_form( array( 'echo' => true ) );
    }
}

function profile_new_subnav_idea_item() {
    global $bp;

    ///add all subtab to main tab and support

    if (bp_displayed_user_id() == get_current_user_id()) {
        $name_ideas = __('My Ideas', 'marylink-custom-plugin');
        $name_comments = __('My Comments', 'marylink-custom-plugin');
        $name_bookmarks = __('My Bookmarks', 'marylink-custom-plugin');
        $name_user_review = __('My User Reviews', 'marylink-custom-plugin');
        $name_expert_review = __('My Expert Reviews', 'marylink-custom-plugin');
     } else {
        $name_ideas = __('User Ideas', 'marylink-custom-plugin');
        $name_comments = __('User Comments', 'marylink-custom-plugin');
        $name_bookmarks = __('User Bookmarks', 'marylink-custom-plugin');
        $name_user_review = __('User User Reviews', 'marylink-custom-plugin');
        $name_expert_review = __('User Expert Reviews', 'marylink-custom-plugin');
    }

//var_dump($bp);

    bp_core_new_subnav_item( array(

        'name'            => $name_ideas,
        'slug'            => 'idea_sub_tab',
        'parent_url'      => $bp->displayed_user->domain . $bp->bp_nav[ 'idea_tab' ][ 'slug' ] . '/',
        'parent_slug'     => $bp->bp_nav[ 'idea_tab' ][ 'slug' ],
        'position'        => 10,
        'screen_function' => 'view_manage_sub_tab_idea_main'
    ) );

    bp_core_new_subnav_item( array(
        'name'            => $name_comments,
        'slug'            => 'idea_comments_sub_tab',
        'parent_url'      => $bp->displayed_user->domain . $bp->bp_nav[ 'idea_tab' ][ 'slug' ] . '/',
        'parent_slug'     => $bp->bp_nav[ 'idea_tab' ][ 'slug' ],
        'position'        => 10,
        'screen_function' => 'view_manage_sub_tab_idea_comments_main'
    ) );
    
    bp_core_new_subnav_item( array(
        'name'            => $name_bookmarks,
        'slug'            => 'idea_bookmarks_sub_tab',
        'parent_url'      => $bp->displayed_user->domain . $bp->bp_nav[ 'idea_tab' ][ 'slug' ] . '/',
        'parent_slug'     => $bp->bp_nav[ 'idea_tab' ][ 'slug' ],
        'position'        => 10,
        'screen_function' => 'view_manage_sub_tab_idea_bookmarks_main'
    ) );
    
    if (bp_displayed_user_id() == get_current_user_id()) {
		bp_core_new_subnav_item( array(
			'name'            => $name_user_review,
			'slug'            => 'idea_userreview_sub_tab',
			'parent_url'      => $bp->displayed_user->domain . $bp->bp_nav[ 'idea_tab' ][ 'slug' ] . '/',
			'parent_slug'     => $bp->bp_nav[ 'idea_tab' ][ 'slug' ],
			'position'        => 10,
			'screen_function' => 'view_manage_sub_tab_idea_user_review_main'
		) );
		
		bp_core_new_subnav_item( array(
			'name'            => $name_expert_review,
			'slug'            => 'idea_expertreview_sub_tab',
			'parent_url'      => $bp->displayed_user->domain . $bp->bp_nav[ 'idea_tab' ][ 'slug' ] . '/',
			'parent_slug'     => $bp->bp_nav[ 'idea_tab' ][ 'slug' ],
			'position'        => 10,
			'screen_function' => 'view_manage_sub_tab_idea_user_expert_main'
		) );
	}


}

add_action( 'bp_setup_nav', 'profile_new_subnav_idea_item', 10 );

function view_manage_sub_tab_idea_main() {
    add_action( 'bp_template_content', 'bp_template_content_sub_idea_function' );
    bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));

    //main Ideas tab template
}


function view_manage_sub_tab_idea_comments_main() {
    
    customPlugBPnav('Comments');

    //Comments subtab [user profile]


}

function view_manage_sub_tab_idea_bookmarks_main() {

    customPlugBPnav('Bookmarks');

    //Bookmarks subtab [user profile]

}

function view_manage_sub_tab_idea_user_review_main() {

    customPlugBPnav('UserReview');

    //Bookmarks subtab [user profile]

}

function view_manage_sub_tab_idea_user_expert_main() {

    customPlugBPnav('ExpertReview');

    //Bookmarks subtab [user profile]

}


function bp_template_content_sub_idea_function() {
    if ( is_user_logged_in() ) {
        //Add shortcode to display content in sub tab
    } else {
        wp_login_form( array( 'echo' => true ) );
    }
}

function bp_template_content_sub_idea_comments_function() {
    if ( is_user_logged_in() ) {
        //Add shortcode to display content in sub tab
    } else {
        wp_login_form( array( 'echo' => true ) );
    }
}


//ideas sorter, not in use
//add_filter('pre_get_posts', 'ideasAnsOrder');
function ideasAnsOrder($wp_query) {
	if($_GET['sort_comment'] && $wp_query->query['post_type']=='ideas'){
		switch ($_GET['sort_comment']) {
			case 'newest':
				$wp_query->set('orderby', 'date');
				break;
			case 'oldest':
				$wp_query->set('oldest', 'date');
				$wp_query->set('order', 'ASC');
				break;
			case 'recent':
				add_filter( 'posts_clauses', 'ideas_intercept_query_clauses', 20, 1 );
				break;
			case 'voted':
				$wp_query->set('orderby','meta_value');
				$wp_query->set('meta_key','_item_likes');
				break;
			case 'answers':
				$wp_query->set('orderby','comment_count');
				break;
		}
	}
}

require( dirname( __FILE__ ) . '/notification.php' ); //all notification related functions
require( dirname( __FILE__ ) . '/notifications_sabai.php' ); //all sabai notification related functions




function custom_script_style() {

    wp_enqueue_style('marylink-custom-css' , plugins_url('style.css', __FILE__));

        /*wp_enqueue_script(
        'custom_marylink-script',
        plugins_url('custom_marylink.js', __FILE__),
        array( 'jquery' )
    );*/

wp_register_script( 'custom_marylink-script', plugins_url('custom_marylink.js', __FILE__), array( 'jquery' ), '', true );

wp_localize_script( 'custom_marylink-script', 'plugin_data', array( 'receiver' => ACTIVITY_CUSTOM_PLUGIN_RECEIVER ));

wp_enqueue_script( 'custom_marylink-script' );



}

add_action( 'wp_enqueue_scripts', 'custom_script_style' );

add_action('init', 'get_post_title_jq'); 

function get_post_title_jq($id = NULL) {

    if (empty($_POST['num_p']))
        return 0;

    $the_title = get_the_title( (int) $_POST['num_p'] );
    echo json_encode($the_title, JSON_UNESCAPED_UNICODE);
    die();

}


 ?>