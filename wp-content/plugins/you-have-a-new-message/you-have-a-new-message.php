<?php
/*
Plugin Name: You Have a New (BuddyPress) Message
Description: Widget and Shortcode to notify users about new messages
Plugin URI: http://wordpress.org/extend/plugins/you-have-a-new-message/
Author: Markus Echterhoff
Author URI: http://www.markusechterhoff.com
Version: 1.1
License: GPLv3 or later
*/

add_action('widgets_init', create_function('', 'register_widget("YouHaveANewBuddyPressMessageWidget");'));
class YouHaveANewBuddyPressMessageWidget extends WP_Widget {

	public function __construct() {
		parent::__construct(
	 		'you-have-a-new-message-widget',
			'You Have a New (BuddyPress) Message',
			array('description' => 'Notifies you about new BuddyPress messages', 'text_domain')
		);
	}

	public function widget( $args, $instance ) {		
		echo yhanm_get_notice();		
	}
}

add_shortcode( 'you-have-a-new-message', 'yhanm_get_notice' );

function yhanm_get_notice() {
	if ( !is_user_logged_in() ) {
		return;
	}
	if ( bp_is_messages_component() && bp_is_current_action( 'inbox' ) ) {
		return;
	}
	$count = bp_get_total_unread_messages_count();
	if ( !$count ) {
		return;
	}
	return '<a class="yhanm" href="'. bp_loggedin_user_domain() . bp_get_messages_slug() .'">' .
			sprintf( _n( 'You have a new message', 'You have %s new messages', $count, 'yhanm' ), $count ) .
			'</a>';
}

?>
