<?php
/*
Plugin Name: BP Disable Activation Reloaded
Plugin URI: http://www.timersys.com/plugins-wordpress/bp-disable-activation-reloaded
Description: Disable the activation email and authentificate the user. Also redirect to a page if selected . Based on BP Disable Activation from John Lynn
Version: 1.2.1
Author: timersys
Author URI: http://www.timersys.com
Requires at least: BuddyPress 1.7
Tested up to: BuddyPress 2.0.2+wp 3.9.2
License: MIT License
Text Domain: dar
Domain Path: languages
*/

/*

**********
* License
****************************************************************************
*	Copyright (C) 2011-2013 Damian Logghe and contributors
*
*	Permission is hereby granted, free of charge, to any person obtaining
*	a copy of this software and associated documentation files (the
*	"Software"), to deal in the Software without restriction, including
*	without limitation the rights to use, copy, modify, merge, publish,
*	distribute, sublicense, and/or sell copies of the Software, and to
*	permit persons to whom the Software is furnished to do so, subject to
*	the following conditions:
*
*	The above copyright notice and this permission notice shall be
*	included in all copies or substantial portions of the Software.
*
*	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
*	EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
*	MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
*	NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
*	LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
*	OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
*	WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
****************************************************************************/
/***
    Copyright (C) 2009 John Lynn(crashutah.com)

    This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 3 of the License, or  any later version.

    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along with this program; if not, see <http://www.gnu.org/licenses>.

    */
	
/***
    Credit goes to AndyPeatling for most of the initial code
    */
	
/***
    Word of Caution: Use this Plugin at your own risk.  The email activation can be one way to keep spammers from registering on your site.  Make sure you're looking at other options to prevent spammers if you use this plugin to remove the email activation.

    */


// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

require(dirname (__FILE__).'/WP_Plugin_Base.class.php');
  
class BP_Disable_Activation_Reloaded extends WP_Plugin_Base
{

	
	protected $_options;
	var $_credits;
	var $_defaults;
	protected $sections;
	
	private static $instance = null;
 
    /*--------------------------------------------*
     * Constructor
     *--------------------------------------------*/
 
    /**
     * Creates or returns an instance of this class.
     *
     * @return  Foo A single instance of this class.
     */
    public static function get_instance() {
 
        if ( null == self::$instance ) {
            self::$instance = new self;
        }
 
        return self::$instance;
 
    } // end get_instance;
    
	function __construct() {
		
		$this->WPB_PREFIX		=	'dar';
		$this->WPB_SLUG			=	'bp-disable-activation-reloaded'; // Need to match plugin folder name
		$this->WPB_PLUGIN_NAME	=	'BP Disable Activation Reloaded';
		$this->WPB_VERSION		=	'1.2.1';
		$this->PLUGIN_FILE		=   plugin_basename(__FILE__);
		$this->options_name		=   'dar_settings';
		
		$this->sections['general']      		= __( 'Main Settings', $this->WPB_PREFIX );
		
		//activation hook
		register_activation_hook( __FILE__, array(&$this,'activate' ));        
		
		//deactivation hook
		register_deactivation_hook( __FILE__, array(&$this,'deactivate' ));   
		
		//admin menu
		add_action( 'admin_menu',array(&$this,'register_menu' ) );
		
		//load js and css 
		add_action( 'init',array(&$this,'load_scripts' ),50 );	
		
		add_action( 'bp_init', array(&$this,'my_plugin_init' ));
		
		$this->loadOptions();
		#$this->upgradePlugin();
			
		#$this->setDefaults();
		
	
		parent::__construct();
		
	
		
	}	
	
	/**
	* Load the plugin once BuddyPress is loaded
	*/
	function my_plugin_init() {
		

		/*The Functions to automatically activate for Single WP Installs*/
		if ( !is_multisite() ) {
			add_action( 'bp_core_signup_user'				, array(&$this,'disable_validation' ));
			add_filter( 'bp_registration_needs_activation'	, array(&$this,'fix_signup_form_validation_text' ));
			add_filter( 'bp_core_signup_send_activation_key', array(&$this,'disable_activation_email' ));
		}
		else
		{
			//Remove filters which notifies users
			remove_filter( 'wpmu_signup_user_notification'	, 'bp_core_activation_signup_user_notification', 1, 4 );
			
			add_filter( 'wpmu_signup_user_notification'		, array(&$this,'cc_auto_activate_on_user_signup'), 1, 4 );
		#	add_action( 'signup_finished'					, array(&$this,"cc_auto_activate_finished");
		}	
		if( isset($_GET['page']) && 'bp-disable-activation-reloaded' == $_GET['page'] && !empty( $_GET['fix_pending_users'] ) ) {
			global $wpdb;

			$users = $wpdb->get_results( "SELECT activation_key, user_login FROM {$wpdb->prefix}signups WHERE active = '0' ");

			foreach ($users as $user) {
				bp_core_activate_signup($user->activation_key);
				BP_Signup::validate($user->activation_key);

				//fix roles
				$user_id = $wpdb->get_var( "SELECT ID FROM $wpdb->users WHERE user_login = '$user->user_login'");

				$u = new WP_User( $user_id );
				$u->add_role( 'subscriber' );
			}
		
		}
	}	
	function disable_validation( $user_id ) {
		global $wpdb;

		$options = $this->_options;
		
		//Hook if you want to do something before the activation
		do_action('bp_disable_activation_before_activation');
		
		$activation_key = get_user_meta($user_id, 'activation_key', true);
		$activate = apply_filters('bp_core_activate_account', bp_core_activate_signup($activation_key));
		BP_Signup::validate($activation_key);
		$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->users SET user_status = 0 WHERE ID = %d", $user_id ) );
		
		//Add note on Activity Stream
		if ( function_exists( 'bp_activity_add' ) ) {
			$userlink = bp_core_get_userlink( $user_id );
			
			bp_activity_add( array(
				'user_id' => $user_id,
				'action' => apply_filters( 'bp_core_activity_registered_member', sprintf( __( '%s became a registered member', 'buddypress' ), $userlink ), $user_id ),
				'component' => 'profile',
				'type' => 'new_member'
			) );
			
		}
		//Send email to admin
		wp_new_user_notification( $user_id );
		// Remove the activation key meta
	    delete_user_meta( $user_id, 'activation_key' );
		// Delete the total member cache
	    wp_cache_delete( 'bp_total_member_count', 'bp' );
	
		//Hook if you want to do something before the login
		do_action('bp_disable_activation_before_login');

		
		if( $options['enable_login'] == 'true' )
		{
			//Automatically log the user in	.
			//Thanks to Justin Klein's  wp-fb-autoconnect plugin for the basic code to login automatically
			$user_info = get_userdata($user_id);
			wp_set_auth_cookie($user_id);
	
			do_action('wp_signon', $user_info->user_login);
		}
		
		//Hook if you want to do something after the login
		do_action('bp_disable_activation_after_login');
		
		$redirection = apply_filters('dar_redirection_url',$options['redirection']);
		
		if( $redirection != '' )
		{
			wp_safe_redirect($redirection);
			die();
		}
	}
	
		
	
	function fix_signup_form_validation_text() {
		return false;
	}
	
	
	function disable_activation_email() {
		return false;
	}
	
	
	
	/*START Functions to automatically activate for WPMU (multi-site)  Installs (Activates User and Blogs)*/
	
	/*
	 Credit for most of the WPMU code goes to Brajesh Singh and his plugin "BP Auto activate User and Blog at Signup"
	*/
	
	
	function cc_auto_activate_on_user_signup($user, $user_email, $key, $meta) {
		//only multisite part will be executed
		$user_id = bp_core_activate_signup($key);
		
		$options = $this->_options;
		
		if( $options['enable_login'] == 'true' )
		{
			//Automatically log the user in	.
			//Thanks to Justin Klein's  wp-fb-autoconnect plugin for the basic code to login automatically
			$user_info = get_userdata($user_id);
			wp_set_auth_cookie($user_id);
	
			do_action('wp_signon', $user_info->user_login);
		}
		
		//Hook if you want to do something after the login
		do_action('bp_disable_activation_after_login');
		
		$redirection = apply_filters('dar_redirection_url',$options['redirection']);
		
		if( $redirection != '' )
		{
			wp_safe_redirect($redirection);
			die();
		}
	}
	

	/**
	* Check technical requirements before activating the plugin. 
	* Wordpress 3.0 or newer required
	*/
	function activate()
	{
		parent::activate();
		

		do_action( $this->WPB_PREFIX.'_activate' );
		
		
	}	

	/**
	* Run when plugin is deactivated
	* Wordpress 3.0 or newer required
	*/
	function deactivate()
	{
		
		
		do_action( $this->WPB_PREFIX.'_deactivate' );
	}
	


	/**
	* function that register the menu link in the settings menu	and editor section inside the option page
	*/
	 function register_menu()
	{
		#add_options_page( 'WP Plugin Base', 'WP Plugin Base', 'manage_options', WPB_SLUG ,array(&$this, 'options_page') );
		add_menu_page( 'BP DAR', 'BP DAR', 'manage_options', $this->WPB_SLUG ,array(&$this, 'display_page') );
		
	
	}

	/**
	* Load scripts and styles
	*/
	function load_scripts()
	{
		if(!is_admin())
		{
			
			#wp_enqueue_script('wsi-js', plugins_url( 'assets/js/wsi.js', __FILE__ ), array('jquery'),$this->WPB_VERSION,true);
			#wp_enqueue_style('wsi-css', plugins_url( 'assets/css/style.css', __FILE__ ) ,'',$this->WPB_VERSION,'all' );
			#wp_localize_script( 'jquery', 'WsiMyAjax', array( 'url' => site_url( 'wp-login.php' ),'admin_url' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'wsi-ajax-nonce' ) ) );
			#wp_enqueue('codemirror');
		}

		
	}
	

	
	
	/**
	* Load options to use later
	*/	
	function loadOptions()
	{

		$this->_options = get_option($this->WPB_PREFIX.'_settings',$this->_defaults);

	}
	
		
	/**
	* loads plugins defaults
	*/
	function setDefaults()
	{
		$this->_defaults = array( 'version' => $this->WPB_VERSION );		
	}

	
	
	
}
BP_Disable_Activation_Reloaded::get_instance();




?>