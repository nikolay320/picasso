<?php

namespace Glcdesign\HashtagWp;

class Plugin
{

    /**
     * @var string Path of the plugin's directory
     */
    private $path;

    /**
     * @var string URI of the plugin's directory
     */
    private $uri;

    /**
     * @var string ROOT path
     */
    private $root;

    /**
     * @var string Version of the plugin
     */
    private $version = "1.1.7";

    /**
     * Textdomain of the plugin
     */
    const TEXTDOMAIN = "hashtag-wp";

    /**
     * Prefix used for database
     */
    const PREFIX = "hashtag_wp_";

    /**
     * Regex used to parse hashtags
     */

    const REGEX_RULE = '\[[^\]]*\]|<[^>]*>|\&[^\;]*\;|\B#([A-ZÉéÀàÛûÔôÎîÈèÀàÌìÒòÙùa-z0-9]+)';

    /**
     * @param $root string Path to the root directory of plugin. Usually the caller file's __FILE__ constant.
     */
    public function __construct($root)
    {

        //setting up the path and uri
        $this->root = $root;
        $this->path = plugin_dir_path($root);
        $this->uri = plugin_dir_url($root);

        //registering actions
        $this->addActions();

        //registering filters
        $this->addFilters();

        //instantiate the customizer
        new Customizer( $this );

    }

    /**
     * Adds actions (hooks)
     */
    private function addActions()
    {

        //loading text domain
        add_action( 'plugins_loaded', array( $this, '_actionLoadTextDomain' ) );

        //admin menu
        add_action( 'admin_menu', array( $this, '_actionAdminMenu' ) );

        //parsing tags
        add_action( 'save_post', array( $this, '_actionParseTags' ), 9, 2 );

        //if comments are enabled
        if( get_option( 'glcdesign_hashtag_wp_comments_enabled', true ) )
        {
            //parsing comments tags
            add_action( 'wp_insert_comment', array( $this, '_actionParseCommentsTags' ), 9, 2 );
        }

        //loading scripts
        add_action( 'wp_enqueue_scripts', array( $this, '_actionLoadScripts' ) );

        //if widget is enabled
        if( get_option( 'glcdesign_hashtag_wp_widget_enabled', true ) )
        {
            //widget init
            add_action( 'widgets_init', array( $this, '_actionRegisterWidget' ) );
        }

        //custom taxonomy
        add_action( 'init', array( $this, '_actionTaxonomy' ) );

        //if buddypress is enabled
        if( get_option( 'glcdesign_hashtag_wp_buddypress_enabled', true ) )
        {
            //parsing hashtags
            add_action( 'bp_activity_posted_update', array( $this, '_actionParseBuddyPress' ), 20, 3 );
            add_action( 'bp_activity_comment_posted', array( $this, '_actionParseBuddyPress' ), 20, 3 );

            //hashtag title
            add_action( 'bp_before_activity_loop', array( $this, '_actionBuddyPressHashtagTitle' ) );

            //registering taxonomy
            add_action( 'init', array( $this, '_actionBuddyPressTaxonomy' ) );
        }

    }

    private function addFilters()
    {

        //adding a node for javascript to know where to find hashtags
        add_filter( 'wp_insert_post_data', array( $this, '_filterEditContent' ), 9, 2 );

        //if comments are enabled
        if( get_option( 'glcdesign_hashtag_wp_comments_enabled', true ) )
        {
            //adding node to comments
            add_filter( 'pre_comment_content', array( $this, '_filterEditCommentContent' ) );
        }

        //if buddypress enabled
        if( get_option( 'glcdesign_hashtag_wp_buddypress_enabled', true ) )
        {
            //adding hashtag links
            add_filter( 'bp_activity_new_update_content', array( $this, '_filterEditActivityContent' ) );
            add_filter( 'bp_activity_comment_content', array( $this, '_filterEditActivityContent' ) );

            //buddypress query
            add_filter( 'bp_before_has_activities_parse_args', array( $this, '_filterBuddyPressHashtagQuery' ) );
        }
    }

    /**
     * Loads plugin's text domain
     */
    public function _actionLoadTextDomain()
    {

        //loading the plugin text domain
        load_plugin_textdomain( Plugin::TEXTDOMAIN, false, basename( dirname( $this->root ) ) . '/languages' );

    }

    public function _actionAdminMenu()
    {

        //adding menu
        add_menu_page(

            __( 'Hashtag WP', Plugin::TEXTDOMAIN ),
            __( 'Hashtag WP', Plugin::TEXTDOMAIN ),
            'manage_options',
            'hashtag-wp',
            array( $this, 'options' ),
            $this->uri . 'assets/images/icon.png'

        );

    }

    public function _actionParseTags( $postId, $post )
    {

        //checking if post or page
        if( ! isset( $post->post_type ) || ( $post->post_type != 'post' && $post->post_type != 'page' ) )
        {
            return true;
        }

        //getting content
        $content = $post->post_content;

        //parsing hashtags
        $hashtags = $this->parseTags( $content );

        //getting currently saved hashtags
        $currentHashtags = get_option( 'glcdesign_hashtag_wp_post_' . $postId, array() );

        //saving new hashtags to database
        update_option( 'glcdesign_hashtag_wp_post_' . $postId, $hashtags );

        //getting saved tags
        $savedTags = ( $savedTags = get_the_tags( $postId ) ) ? $savedTags : array();
        $savedTagsName = array_map( function( $t ) {
            return $t->name;
        }, $savedTags );

        //removing hashtags from saved tags
        $tags = array_diff( $savedTagsName, $currentHashtags );

        //appending hashtags
        $tags = array_merge( $tags, $hashtags );

        //if post
        if( $post->post_type == 'post' )
        {
            //saving tags to post
            wp_set_post_tags( $post->ID, $tags, false );
        }

        //adding hashtags to database
        $this->saveHashtags( $hashtags, $post->ID );

        //returning true
        return true;

    }


    public function _actionParseCommentsTags( $id, \WP_Comment $comment )
    {

        //parsing tags
        $hashtags = $this->parseTags( $comment->comment_content );

        //if no hashtags
        if( !is_array( $hashtags ) || count( $hashtags ) < 1 )
        {
            return; //nothing to save
        }

        //getting existing tags array
        $commentsTags = get_option( 'glcdesign_hashtag_wp_comments_' . $comment->comment_post_ID, array() );

        //merging
        $commentsTags = array_merge( $commentsTags, $hashtags );

        //saving comments tags back to database
        update_option( 'glcdesign_hashtag_wp_comments_' . $comment->comment_post_ID, $commentsTags );

        //returning true
        return true;

    }

    public function _actionParseBuddyPress( $content, $userID, $activityID )
    {

        if( is_array( $userID ) && $activityID instanceof \BP_Activity_Activity )
        {
            $content = $userID[ 'content' ];
            $activityID = $activityID->id;
        }

        //parsing tags
        $hashtags = $this->parseTags( $content );

        //if no hashtags
        if( !is_array( $hashtags ) || count( $hashtags ) < 1 )
        {
            return; //nothing to save
        }

        //foreach hashtags
        foreach( $hashtags as $h )
        {

            //getting activities with this hashtag
            $activities = get_option( 'glcdesign_hashtag_wp_buddypress_' . $h . '_activities', array() );

            //adding this new activity
            $activities[ $activityID ] = $activityID;

            //saving it bac
            update_option( 'glcdesign_hashtag_wp_buddypress_' . $h . '_activities', $activities );

        }

        //saving hashtags
        $this->saveHashtags( $hashtags, $activityID, true );

        //returning true
        return true;

    }

    public function _actionLoadScripts()
    {

        //registering script
        wp_register_script( 'glcdesign_hashtag_wp', $this->uri . '/assets/js/glcdesign-hashtag-wp.js', array( 'jquery' ), $this->version, true );

        //post tags
        $hashtags = get_terms( 'hashtag_wp', array( 'fields' => 'names', 'hide_empty' => false ) );

        //passing params
        wp_localize_script( 'glcdesign_hashtag_wp', 'glcdesign_hashtag_wp', array(

            'regexRule'         => Plugin::REGEX_RULE,
            'tagUrl'            => esc_url( home_url() . '/hashtag_wp' ),
            'commentsEnabled'   => get_option( 'glcdesign_hashtag_wp_comments_enabled', true ),
            'existingTags'      => $hashtags

        ) );

        //enqueuing script
        wp_enqueue_script( 'glcdesign_hashtag_wp' );

        //enqueuing css
        wp_enqueue_style( 'glcdesign_hashtag_wp', $this->uri . '/assets/scss/glcdesign-hashtag-wp.css', array(), $this->version );

    }

    public function _actionRegisterWidget()
    {

        //adding hashtag widget
        register_widget( 'Glcdesign\\HashtagWp\\Widget' );

    }

    public function _actionBuddyPressHashtagTitle()
    {

        //checking if hashtag is set
        if( empty( $_GET[ 'hashtag' ] ) )
        {
            return;
        }

        //if set, displaying a title
        $hashtag = sanitize_text_field( $_GET[ 'hashtag' ] );

        echo sprintf(
            '<h3>%s</h3>',
            sprintf( __( "Activities for %s", Plugin::TEXTDOMAIN ), '#' . $hashtag )
        );

    }

    public function _actionBuddyPressTaxonomy()
    {
    
        // Add new taxonomy, make it hierarchical (like categories)
        $labels = array(
            'name'              => _x( 'BuddyPress Hashtags', 'taxonomy general name', Plugin::TEXTDOMAIN ),
            'singular_name'     => _x( 'BuddyPress Hashtag', 'taxonomy singular name', Plugin::TEXTDOMAIN ),
            'search_items'      => __( 'Search BuddyPress Hashtags', Plugin::TEXTDOMAIN ),
            'all_items'         => __( 'All BuddyPress Hashtags', Plugin::TEXTDOMAIN ),
            'parent_item'       => __( 'Parent BuddyPress Hashtag', Plugin::TEXTDOMAIN ),
            'parent_item_colon' => __( 'Parent BuddyPress Hashtag:', Plugin::TEXTDOMAIN ),
            'edit_item'         => __( 'Edit BuddyPress Hashtag', Plugin::TEXTDOMAIN ),
            'update_item'       => __( 'Update BuddyPress Hashtag', Plugin::TEXTDOMAIN ),
            'add_new_item'      => __( 'Add New BuddyPress Hashtag', Plugin::TEXTDOMAIN ),
            'new_item_name'     => __( 'New BuddyPress Hashtag Name', Plugin::TEXTDOMAIN ),
            'menu_name'         => __( 'BuddyPress Hashtag', Plugin::TEXTDOMAIN ),
        );
    
        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'public'            => true,
            'show_tagcloud'     => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'hashtag_wp_bp' ),
        );
    
        register_taxonomy( 'hashtag_wp_bp', 'buddypress-activity', $args );

        //redirection
        add_action( 'template_redirect', array( $this, '_actionBuddyPressRedirectToTag' ) );

    }
    
    public function _actionTaxonomy()
    {
        
        // Add new taxonomy, make it hierarchical (like categories)
        $labels = array(
            'name'              => _x( 'Hashtags', 'taxonomy general name', Plugin::TEXTDOMAIN ),
            'singular_name'     => _x( 'Hashtag', 'taxonomy singular name', Plugin::TEXTDOMAIN ),
            'search_items'      => __( 'Search Hashtags', Plugin::TEXTDOMAIN ),
            'all_items'         => __( 'All Hashtags', Plugin::TEXTDOMAIN ),
            'parent_item'       => __( 'Parent Hashtag', Plugin::TEXTDOMAIN ),
            'parent_item_colon' => __( 'Parent Hashtag:', Plugin::TEXTDOMAIN ),
            'edit_item'         => __( 'Edit Hashtag', Plugin::TEXTDOMAIN ),
            'update_item'       => __( 'Update Hashtag', Plugin::TEXTDOMAIN ),
            'add_new_item'      => __( 'Add New Hashtag', Plugin::TEXTDOMAIN ),
            'new_item_name'     => __( 'New Hashtag Name', Plugin::TEXTDOMAIN ),
            'menu_name'         => __( 'Hashtag', Plugin::TEXTDOMAIN ),
        );
        
        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'public'            => true,
            'show_tagcloud'     => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'hashtag_wp' ),
        );
        
        register_taxonomy( 'hashtag_wp', array( 'post', 'page' ), $args );
        
        //redirection
        add_action( 'template_redirect', array( $this, '_actionBuddyPressRedirectToTag' ) );
        
    }

    public function _actionBuddyPressRedirectToTag()
    {

        //if buddypress doesn't exist, return
        if( ! function_exists( 'bp_get_activity_root_slug' ) )
        {
            return;
        }

        global $wp_query;
        if( ! empty( $wp_query->query[ 'hashtag_wp_bp' ] ) )
        {
            wp_redirect( $this->buddyPressURL( $wp_query->query[ 'hashtag_wp_bp' ] ), '301' );
            exit;
        }
    }

    public function _filterEditContent( $data, $post )
    {

        //if post is not page or post
        if( $post instanceof \WP_Post )
        {
            if ( !isset( $post->post_type ) || ( $post->post_type != 'page' && $post->post_type != 'post' ) )
            {

                return $data;
            }
        }
        else
        {
            if ( !isset( $post['post_type'] ) || ( $post['post_type'] != 'page' && $post['post_type'] != 'post' ) )
            {
                return $data;
            }
        }

        //getting content
        $content = $data[ 'post_content' ];

        //adding a node for javascript to know where to find hashtags
        //emptying the node to prevent user typing into it
        $already = array();
        preg_match( '/\\\"glcdesign-hashtag-wp-node\\\"/is', $content, $already );

        //if not already there
        if( !is_array( $already ) || count( $already ) < 1 )
        {

            //adding the node
            $content .= '<div class="glcdesign-hashtag-wp-node"></div>';


        }

        $content = preg_replace( '/<div class=\\\"glcdesign-hashtag-wp-node\\\">(.*)<\/div>/is', '$1' . '<div class="glcdesign-hashtag-wp-node"></div>', $content );

        //setting it back to data
        $data[ 'post_content' ] = $content;

        //returning data
        return $data;

    }

    public function _filterEditCommentContent( $content )
    {

        //checking if there is some hashtags
        $hashtags = $this->parseTags( $content );

        //if no hashtags
        if( !is_array( $hashtags ) || count( $hashtags ) < 1 )
        {
            return $content; //return original content
        }

        //adding a node for javascript to know where to find hashtags
        //we need to check if already there
        $already = array();
        preg_match( '/\\<div \"glcdesign-hashtag-wp-comment-node\\\">([^<\/div>]+)<\/div>/is', $content, $already );

        //if not already there
        if( !is_array( $already ) || count( $already ) < 1 )
        {

            //adding the node
            $content .= '<div class="glcdesign-hashtag-wp-comment-node"></div>';

        }

        //returning data
        return $content;

    }

    public function _filterEditActivityContent( $content )
    {

        //checking if there is some hashtags
        $hashtags = $this->parseTags( $content );

        //if no hashtags
        if( !is_array( $hashtags ) || count( $hashtags ) < 1 )
        {
            return $content; //return original content
        }

        //replacing hashtags with link
        foreach( $hashtags as $h )
        {

            if( empty( $h ) )
            {
                continue;
            }

            $content = str_replace(
                '#' . $h,
                sprintf(
                    '<a href="%s" rel="nofollow" class="glcdesign-hashtag-wp">%s</a>',
                    $this->buddyPressURL( $h ),
                    '#' . $h
                ),
                $content
            );

        }

        //returning data
        return $content;

    }

    public function _filterBuddyPressHashtagQuery( $args )
    {

        //checking if hashtag is specified
        if( !isset( $_GET[ 'hashtag' ] ) )
        {
            return $args; //no modification required
        }

        //sanitizing
        $hashtag = sanitize_text_field( $_GET[ 'hashtag' ] );

        //getting activities ids
        $ids = get_option( 'glcdesign_hashtag_wp_buddypress_' . $hashtag . '_activities', array( '-1' ) );

        //including only activities with this hashtag
        $args[ 'include' ] = implode( ',', $ids );

        //returning altered args
        return $args;

    }

    public function options()
    {

        //saved
        $post = false;
        $saved = false;
        $error = false;

        //we check for form submission
        if ( isset( $_POST[ 'submit' ] ) )
        {

            //post is true
            $post = true;

            //we verify nonce
            if ( !isset( $_POST[ 'save_hashtag_wp_nonce' ] ) ||
                ( isset( $_POST[ 'save_hashtag_wp_nonce' ] ) && !wp_verify_nonce(
                        $_POST[ 'save_hashtag_wp_nonce' ], 'save_hashtag_wp'
                    ) )
            )
            {

                $saved = false;
                $error = __( 'Nonce not valid. Operation not-permitted!', Plugin::TEXTDOMAIN );

            }
            else
            {

                //the nonce verified

                //saving options
                $commentsEnabled = !empty( $_POST[ 'commentsEnabled' ] );
                update_option( 'glcdesign_hashtag_wp_comments_enabled', $commentsEnabled );

                $buddypressEnabled = !empty( $_POST[ 'buddypressEnabled' ] );
                update_option( 'glcdesign_hashtag_wp_buddypress_enabled', $buddypressEnabled );

                $hashtagWidget = !empty( $_POST[ 'hashtagWidget'  ] );
                update_option( 'glcdesign_hashtag_wp_widget_enabled', $hashtagWidget );

                $saved = true;

            }

        }

        //false, not full version
        $commentsEnabled = get_option( 'glcdesign_hashtag_wp_comments_enabled', true );
        $buddypressEnabled = get_option( 'glcdesign_hashtag_wp_buddypress_enabled', true );
        $hashtagWidget = get_option( 'glcdesign_hashtag_wp_widget_enabled', true );

        //displaying option page
        include_once $this->path . 'inc/templates/options.inc.php';

    }

    private function parseTags( $content )
    {

        //removing html
        $text = wp_strip_all_tags( $content );

        //getting tags using regex
        $tags = array();
        preg_match_all( '/' . Plugin::REGEX_RULE . '/', $text, $tags );

        //checking if matches has been found
        if( count( $tags ) > 1 && is_array( $tags[ 1 ] ) )
        {

            //returning tags
            return $tags[ 1 ];

        }
        else
        {

            //returning empty array
            return array();

        }

    }

    private function saveHashtags( $hashtags, $postID, $buddypress = false )
    {

        //adding not added hashtags to this array
        foreach( $hashtags as $h )
        {
            //if post
            if ( ! $buddypress )
            {
                $term = wp_insert_term( $h, 'hashtag_wp' );
            }
            else //if buddypress
            {
                $term = wp_insert_term( $h, 'hashtag_wp_bp' );
            }

            //if error
            if ( ! is_array( $term ) || ! isset( $term[ 'term_id' ] ) )
            {

                //if term exists
                if ( isset( $term->error_data ) && isset( $term->error_data[ 'term_exists' ] ) )
                {
                    $term = $term->error_data[ 'term_exists' ]; //getting existing term id
                }
                else
                {
                    //otherwise, it is a real error
                    continue; //next one
                }

            }
            else
            {
                //if no error, we get the term id
                $term = $term[ 'term_id' ];
            }

            //if post
            if( ! $buddypress )
            {
                //adding term to post
                wp_set_object_terms( $postID, $term, 'hashtag_wp', true );
            }
            else //if buddypress
            {
                //adding term to activity
                bp_set_object_terms( $postID, $term, 'hashtag_wp_bp', true );
            }
        }
        
    }

    private function buddyPressURL( $tag )
    {
        return esc_url( trailingslashit( home_url( bp_get_activity_root_slug() ) ) . '?hashtag=' . $tag  );
    }

    /**
     * @return string Returns the path of the plugin's directory
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return string Returns the URI of the plugin's directory
     */
    public function getUri()
    {

        return $this->uri;
    }

    /**
     * @return string Returns the version of the plugin
     */
    public function getVersion()
    {
        return $this->version;
    }

}