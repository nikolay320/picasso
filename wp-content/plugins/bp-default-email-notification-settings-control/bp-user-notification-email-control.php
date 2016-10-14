<?php
/**
 *
 * Plugin Name: BP Default Email Notification Settings Control
 * Version: 1.0.1
 * Plugin URI: http://buddydev.com/bp-default-notification-email-settings-control/
 * Author: Brajesh Singh
 * Author URI: http://buddydev.com
 * License: GPL
 * Description: Allows site admins to set the default email preferences for new users 
 */

class BP_User_Notification_Email_Settings_Control {
    
    private static $instance;

	private $plugin_dir_path;
    private $plugin_dir_url;
    
    private function __construct() {
    
        $this->plugin_dir_path =  plugin_dir_path( __FILE__ );
        $this->plugin_dir_url = plugin_dir_url( __FILE__ );
        
        //load required files
        add_action( 'plugins_loaded', array( $this, 'load' ) );
        add_action( 'bp_core_activated_user', array( $this, 'set_preference' ) );
        
    }
   
       
    /**
     * Get Instance
     * 
     * Use singleton patteren
     * @return BP_User_Notification_Email_Settings_Control
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
		}

        return self::$instance;
    }
    
    /***
     * load files
     */
    
    public function load() {
        
        $files =  array( );
        //only load on main site
        //not a good case for BP Multinetwork
        if( is_admin() &&  is_main_site() ) {
            $files [] = 'admin/admin.php';
        }
        
        foreach( $files as $file ) {
            require_once ( $this->plugin_dir_path . $file );  
        }
    
        
    }
    /**
     * Get all settings as key val
     * An optimization will be to 
     * @return type
     */
    public function get_settings() {
        
        $all_settings = bunec_get_default_settings();
        
        $settings = array();
        
        foreach( $all_settings as $key => $setting_info ) {
            $settings[$key] = $setting_info['val']; 
		}
        
        //now get current settings
        
        $current_settings = bp_get_option( 'bp_user_email_preference', $settings );
        
        return $current_settings;
    }

    public function set_preference( $user_id ) {

        //i am putting all the notifications to no by default

        $settings_keys = $this->get_settings();
       
        foreach( $settings_keys as $setting => $preference ) {

            bp_update_user_meta( $user_id,  $setting, $preference );
        }

    //that's it man. have fun!

    }    
      
}

//initialize
BP_User_Notification_Email_Settings_Control::get_instance();


function bunec_get_default_settings() {
    
   
     $settings = array(
            'notification_activity_new_mention' => array( 
                    'label' => __( 'A member mentions the user in an update using @username', 'bunec' ),
                    'val'   => 'no'
                ) ,
            'notification_activity_new_reply' => array( 
                    'label' => __( "A member replies to an update or comment the user posted", 'bunec' ),
                    'val'   => 'no'
                ),
            'notification_messages_new_message' => array( 
                    'label' => __( 'A member sends the user a new private message', 'bunec' ),
                    'val'   => 'no'
                ),
            'notification_friends_friendship_request' => array( 
                    'label' => __( 'A member sends the user friendship request', 'bunec' ),
                    'val'   => 'no'
                ),
            'notification_friends_friendship_accepted' => array( 
                    'label' => __( "A member accepts the user's friendship request", 'bunec' ),
                    'val'   => 'no' 
                ), //shoudl we hide group related settings if group is not active?//NO
            'notification_groups_invite' => array( 
                    'label' => __( 'A member invites the user to join a group', 'bunec' ),
                    'val'   => 'no'
                ),
            'notification_groups_group_updated' => array( 
                    'label' => __( 'Group information is updated', '' ),
                    'val'   => 'no' 
                ),
            'notification_groups_admin_promotion'=> array( 
                    'label' => __( 'When user is promoted to a group administrator or moderator', 'bunec' ),
                    'val'   => 'no' 
                ),
            'notification_groups_membership_request'=> array( 
                    'label' => __( 'When a member requests to join a private group for which the users ia an admin', 'bunec' ),
                    'val'   => 'no'
            ),
           


    );
        
    
     return apply_filters( 'bunec_default_settings', $settings );
}
