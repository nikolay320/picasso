<?php
/*
  Plugin Name: Buddypress Quick Activity
  Plugin URI: http://geomywp.com
  Description: BuddyPress members can quick post update to activity from any page across the site
  Author: Eyal Fitoussi
  Version: 1.4
  Author URI: http://geomywp.com
  Text Domain: BPQA
  Domain Path: /languages/
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) 
	exit;

/**
 * BP_Quick_Activity class
 * @since 1.0
 * 
 */
class BP_Quick_Activity {

    /**
     * __constructor
     * 
     * @since 1.0
     * @return type
     */
    public function __construct() {
    	
    	//constants
    	define( 'BPQA_VERSION', '1.4' );
    	define( 'BPQA_AJAX'	, 	get_bloginfo( 'wpurl' ) . '/wp-admin/admin-ajax.php' );
    	define( 'BPQA_PATH', 	untrailingslashit( plugin_dir_path( __FILE__ ) ) );
        define( 'BPQA_URL',  	untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
        define( 'BPQA_IMAGES', 	BPQA_URL . '/assets/images' );
        
        //load admin pages
        if ( is_admin() && !defined( 'DOING_AJAX' ) ) {
            include_once BPQA_PATH . '/includes/bpqa-admin.php';
            return;
        }

        //if user logged out no need to load the plugin in front-end
        if ( !is_user_logged_in() )
            return;

        //settings
        $this->settings = get_option( 'bpqa_options' );
        
        //the plugin is set to form submission using ajax by default
        //use the filter to disable it and have form submission using page load
        $this->ajax_submission 		  						 = apply_filters( 'bpqa_do_ajax_submission', true ); 
        $this->settings['ajaxUrl'] 	  						 = BPQA_AJAX;
        $this->settings['imgUrl']  	  						 = BPQA_IMAGES;
        $this->settings['ajaxSubmit'] 						 = $this->ajax_submission;
        $this->settings['labels']['form']['loading'] 	 	 = __( 'Loading...', 'BPQA' );
        $this->settings['labels']['form']['updated_message'] = __( 'Update successfully posted.', 'BPQA' );
               
        $this->settings = apply_filters( 'bpqa_main_settings', $this->settings );
        
        //submit activity on page load
        if ( !is_admin() && !$this->ajax_submission ) {
           self::submit_activity();
        }

        //create shortcodes
        add_shortcode( 'bpqa_form',   array( $this, 'bpqa_form'   ) );
        add_shortcode( 'bpqa_button', array( $this, 'bpqa_button' ) );
        
        //do actions
        add_action( 'wp_head', 			  array( $this, 'custom_css'      	   ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' 	   ) );
        add_action( 'admin_bar_menu', 	  array( $this, 'adminbar_button' ), 100 );
        add_action( 'wp_footer', 		  array( $this, 'floating_button' 	   ) );
        add_action( 'wp_footer', 		  array( $this, 'display_popup_form'   ) );

        //do ajax
        //add_action( 'wp_ajax_bpqa_popup_template_display', array( $this, 'display_popup_form' ) );
        add_action( 'wp_ajax_bpqa_submit_form', array( $this, 'submit_activity' ) );
    }

    /**
     * Register scripts/styles
     */
    function enqueue_scripts() {
    	
    	//main styleshit of plugin
    	wp_register_style( 'bpqa-style', BPQA_URL.'/assets/css/bpqa-frontend.css' );
    	wp_enqueue_style( 'bpqa-style' );
    	
    	//mian JavaScript file
        wp_register_script( 'bpqa-js', BPQA_URL.'/assets/js/bpqa.min.js', array( 'jquery' ), BPQA_VERSION, true );
        wp_enqueue_script(  'bpqa-js' );
        wp_localize_script( 'bpqa-js', 'bpqaArgs', $this->settings );
    }

    /**
     * Add custom css
     */
    function custom_css() {
    
    	if ( empty( $this->settings['style']['custom_css'] ) )
    		return;
    
    	echo "\n<style type=\"text/css\">\n".$this->settings['style']['custom_css']."\n</style>\n";
    }
    
    /**
     * Add button to adminbar
     * @global type $wp_admin_bar
     * @param type $admin_bar
     * @return type
     */
    public function adminbar_button( $admin_bar ) {

    	// add link to toobar if needed
    	if ( empty( $this->settings['toolbar_button']['use'] ) )
    		return;

    	global $wp_admin_bar;

    	$admin_bar->add_menu( array(
    			'id'    => 'bpqa-post-update',
    			'title' => $this->settings['toolbar_button']['title'],
    			'href'  => '#',
    			'meta'  => array(
    					'title' => $this->settings['toolbar_button']['title'],
    					'class' => 'bpqa-form-trigger'
    			),
    	) );
    }

    /**
     * Button shortcode
     * @param type $args
     */
    public function bpqa_button( $args ) {

    	extract( shortcode_atts( array(
    			'type'  => 'button',
    			'title' => 'Say Something',
    			'img'   => false,
    			'class' => '',
    			'id'    => ''
    	), $args ) );
    	 
    	$button = '';
    	$button .= '<div class="bpqa-form-trigger-wrapper bpqa-form-'.$type.'-trigger-wrapper">';

    	if ( $type == 'image' && $img != false ) {
    		$button .= '<img src="'.BPQA_URL.'/buttons/'.$img.'" class="bpqa-form-trigger bpqa-image-trigger '.$class.'" id="'.$id.'" title="'.$title.'" />';
    	} elseif ( $type == 'link' ) {
    		$button .= '<a href="#" title="'.$title.'" class="bpqa-form-trigger bpqa-link-trigger '.$class.'" id="'.$id.'" >'.$title.'</a>';
    	} else {
    		$button .= '<button title="'.$title.'" class="bpqa-form-trigger bpqa-button-trigger '.$class.'" id="'.$id.'" >'.$title.'</button>';
    	}

    	$button .= '</div>';

    	$template = ( !empty( $this->settings['form']['popup_template'] ) ) ? $this->settings['form']['popup_template'] : 'default';
    	
    	//enqueue style of popup form
    	if ( !wp_style_is( 'bpqa-style-'.$template, 'enqueued' ) ) {
    		if ( file_exists( STYLESHEETPATH.'/bpqa/forms/'.$template.'/form.php' ) ) {
    			$include = get_stylesheet_directory_uri().'/bpqa/forms/'.$template.'/css/style.css';
    		} else {
    			$include = BPQA_URL.'/form-templates/'.$template.'/css/style.css';
    		}
    		wp_enqueue_style( 'bpqa-style-'.$template, $include );
    	}

    	return $button;
    }
    
    /**
     * Form shortcode
     * @param type $args
     */
    public function bpqa_form( $args ) {

    	$bpqaArgs = $this->settings;
    	 
    	extract( shortcode_atts( array(
    			'post_to_groups'   => '1',
    			'max_characters'   => '',
    			'template'		   => 'default',
    			'text_placeholder' => __( 'Post something to activity...', 'BPQA' )
    	), $args ) );

    	$bpqa_options = $this->settings;
    	$bpqa_options['form']['max_characters']  = $max_characters;
    	$bpqa_options['form']['groups_publish']  = $post_to_groups;
    	$bpqa_options['form']['inpage_template'] = $template;
    		
    	//enqueue style of popup form
    	if ( !wp_style_is( 'bpqa-style-'.$template, 'enqueued' ) ) {
    		if ( file_exists( STYLESHEETPATH.'/bpqa/forms/'.$template.'/form.php' ) ) {
    			$include = get_stylesheet_directory_uri().'/bpqa/forms/'.$template.'/css/style.css';
    		} else {
    			$include = BPQA_URL.'/form-templates/'.$template.'/css/style.css';
    		}
    		wp_enqueue_style( 'bpqa-style-'.$template, $include );
    	}
    	 
    	ob_start();

    	$bpqa_form_labels = apply_filters( 'bpqa_inpage_'.$template.'_form_labels', array(
    			'text_area_placeholder' => $text_placeholder,
    			'characters_remaining'  => __( 'characters remaining.', 'BPQA' ),
    			'post_in_default' 		=> __( 'Post in', 'BPQA' ),
    			'post_in_my_profile' 	=> __( 'My profile', 'BPQA' ),
    			'submit_button' 		=> __( 'Submit', 'BPQA' ),
    			'cancel_button' 		=> __( 'cancel', 'BPQA' ),
    	) );

    	if ( file_exists( STYLESHEETPATH."/bpqa/forms/{$template}/form.php" ) ) {
    		$include = STYLESHEETPATH."/bpqa/forms/{$template}/form.php";
    	} else {
    		$include = BPQA_PATH."/form-templates/{$template}/form.php";
    	}
    	
    	include( $include );

    	return ob_get_clean();
    }

    /**
     * Trigger the floating button if needed
     */
    public function floating_button() {

    	if ( empty( $this->settings['floating_button']['use'] ) )
    		return;

    	echo '<div class="bpqa-floating-button-wrapper bpqa-floating-'.$this->settings['floating_button']['location'].'" style="top:'.$this->settings['floating_button']['top'].'px">';
    	echo do_shortcode( '[bpqa_button type="image" img="'.$this->settings['floating_button']['icon'].'"]' );
    	echo '</div>';
    }

    /**
     * Display popup form
     */
    function display_popup_form() {
    	 
    	$bpqa_options = $this->settings;
    	$template 	  = ( !empty( $bpqa_options['form']['popup_template'] ) ) ? $bpqa_options['form']['popup_template'] : 'default';
    	 
    	$bpqa_form_labels = apply_filters( 'bpqa_popup_'.$template.'_form_labels', array(
    			'text_area_placeholder' => $this->settings['form']['text_placeholder'],
    			'characters_remaining'  => __( 'characters remaining.', 'BPQA' ),
    			'post_in_default' 		=> __( 'Post in', 'BPQA' ),
    			'post_in_my_profile' 	=> __( 'My profile', 'BPQA' ),
    			'submit_button' 		=> __( 'Submit', 'BPQA' ),
    			'cancel_button' 		=> __( 'cancel', 'BPQA' ),
    	) );
    	 
    	if ( file_exists( STYLESHEETPATH.'/bpqa/forms/'.$template.'/form.php' ) ) {
    		$include = STYLESHEETPATH.'/bpqa/forms/'.$template.'/form.php';
    	} else { 
    		$include = BPQA_PATH.'/form-templates/'.$template.'/form.php';
    	}
    	echo '<div id="bpqa-popup-ajax-loader"><div id="bpqa-popup-message-holder"><p>'.$bpqa_options['labels']['form']['loading'].'</p></div><img src="'.BPQA_IMAGES.'/ajax-loader.gif" /></div>';
    	echo '<div id="bpqa-popup-template-holder-'.$template.'" class="bpqa-screen-cover bpqa-popup-template-holder">';
    	include( $include );
    	echo '</div>';
    }
    
    /**
     * Submit new activity
     * @global type $bp
     */
    public function submit_activity() {

    	//if doing ajax
    	if ( defined( 'DOING_AJAX' ) && $this->ajax_submission ) {
    		parse_str( $_POST['formValues'], $formValues );
    		$_POST = array_merge( $_POST, $formValues );
    	}

    	if ( empty( $_POST['bpqa_action'] ) || $_POST['bpqa_action'] != 'submit' )
    		return;
    	 
    	if ( empty( $_POST['bpqa_update_activity'] ) || !wp_verify_nonce( $_POST['bpqa_update_activity'], 'bpqa_submit_form' ) )
    		die( 'An error occurred while trying to process the action.' );

    	if ( empty( $_POST['bpqa_post_in'] ) || $_POST['bpqa_post_in'] == '0' ) {

    		global $bp;

    		$activity_args = array(
    				'action'			=> apply_filters( 'bpqa_profile_new_activity_message', sprintf( __( '%1$s Posted an update', 'BPQA' ), bp_core_get_userlink( $bp->loggedin_user->id ), '' ) ),
    				'content'           => $_POST['bpqa_textarea'],
    				'component'         => 'profile',
    				'type'              => 'activity_update',
    				'primary_link'      => '',
    				'user_id'           => $bp->loggedin_user->id,
    				'item_id'           => 0,
    				'secondary_item_id' => false,
    				'recorded_time'     => gmdate( "Y-m-d H:i:s" ),
    				'hide_sitewide'     => false
    		);
    		$activity_id = bp_activity_add( $activity_args );

    	} else {

    		global $bp;
    		$group  = groups_get_group( array( 'group_id' => $_POST['bpqa_post_in'] ) );

    		$activity_args = array(
    				'action' 	=> apply_filters( 'bpqa_groups_new_activity_message', sprintf( __( '%1$s Posted an update in the group %2$s', 'BPQA' ), bp_core_get_userlink( $bp->loggedin_user->id ), '<a href="' . bp_get_group_permalink( $group ) . '">' . esc_attr( $group->name ) . '</a>' ) ),
    				'content' 	=> $_POST['bpqa_textarea'],
    				'type' 		=> 'activity_update',
    				'item_id' 	=> $_POST['bpqa_post_in'],
    				'user_id' 	=> $bp->loggedin_user->id
    		);
    		$activity_id = groups_record_activity( $activity_args );
    	}

    	//if doing ajax
    	if ( defined( 'DOING_AJAX' ) && $this->ajax_submission ) {
    		die(true);
    	} else {
    		//reload the page when not doing ajax
    		header("location:".$_SERVER['REQUEST_URI']);
    		exit;
    	}
    }
}

function bpqa_init() {
    new BP_Quick_Activity();
}
// load plugin
add_action( 'bp_init', 'bpqa_init' );
?>