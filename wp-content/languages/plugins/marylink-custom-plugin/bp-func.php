<?php 

add_post_type_support( 'ideas', 'buddypress-activity' ); //buddypress support for ideas post type 

 
function customize_page_tracking_args() {
    // Check if the Activity component is active before using it.
    if ( ! bp_is_active( 'activity' ) ) {
        return;
    }
 
bp_activity_set_post_type_tracking_args( 'ideas', array(
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
add_action( 'bp_init', 'customize_page_tracking_args' ); //custom activity posts


function profile_new_idea_item() {

	//if (bp_displayed_user_id() == get_current_user_id()) {
    bp_core_new_nav_item(
    array(
        'name'                => 'Ideas',
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
     } else {
        $name_ideas = __('User Ideas', 'marylink-custom-plugin');
        $name_comments = __('User Comments', 'marylink-custom-plugin');
        $name_bookmarks = __('User Bookmarks', 'marylink-custom-plugin');
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

 ?>