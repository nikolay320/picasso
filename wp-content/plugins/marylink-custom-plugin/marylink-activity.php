<?php
/**
 * Plugin Name: BuddyPress Add Idea Activity & Custom Notifications
 * Plugin URI: http://fiverr.com/wp_expert_/
 * Description: Custom  Plugin For adding Idea activities and notification
 * Version: 1.0.0
 * Author: Ashik72
 * Author URI: https://www.upwork.com/freelancers/~01353e37a21e977904
 */


define( 'ACTIVITY_CUSTOM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ACTIVITY_CUSTOM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ACTIVITY_CUSTOM_PLUGIN_RECEIVER', ACTIVITY_CUSTOM_PLUGIN_URL."receiver.php");

function bp_activity_init() {
    require( dirname( __FILE__ ) . '/bp-func.php' );
    /*load everything after buddypress is loaded*/
}
add_action( 'bp_include', 'bp_activity_init' );


add_action('plugins_loaded', 'marylink_custom_lang_support');

function marylink_custom_lang_support() {
	load_plugin_textdomain( 'marylink-custom-plugin', false, dirname( plugin_basename(__FILE__) ) . '/languages/' );
}
