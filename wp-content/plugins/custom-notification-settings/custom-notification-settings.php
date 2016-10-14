<?php
/*
Plugin Name: Custom Notification Settings

Plugin URI: Custom Notification Settings

Description: Custom Notification Settings

Require Plugins:
	Buddpress
	Sabai Directory
	Sabai Question
	Idea
	EventON

Add user setting at user profile 's setting to get email when there're new themes, questions, ideas, events published.
	
Version: 1.0.1

Author: phandung122

Author URI: http://www.upwork.com/o/profiles/users/_~016252273d5cf3683a/

License: 

*/


function custom_notification_load_textdomain() {
	load_plugin_textdomain( 'custom-notification-settings', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
}
add_action( 'bp_init', 'custom_notification_load_textdomain' );




function custom_screen_notification_settings() {
if ( ! $theme = bp_get_user_meta( bp_displayed_user_id(), 'notification_custom_new_theme', true ) ) {
	$theme = 'yes';
}
if ( ! $question = bp_get_user_meta( bp_displayed_user_id(), 'notification_custom_new_question', true ) ) {
	$question = 'yes';
}
if ( ! $idea = bp_get_user_meta( bp_displayed_user_id(), 'notification_custom_new_idea', true ) ) {
	$idea = 'yes';
}
if ( ! $event = bp_get_user_meta( bp_displayed_user_id(), 'notification_custom_new_event', true ) ) {
	$event = 'yes';
}
?>

	<table class="notification-settings" id="custom-notification-settings">
		<thead>
			<tr>
				<th class="icon">&nbsp;</th>
				<th class="title"><?php _e( 'I want to receive mails on the following publications :', 'custom-notification-settings' ) ?></th>
				<th class="yes"><?php _e( 'Yes', 'custom-notification-settings' ) ?></th>
				<th class="no"><?php _e( 'No', 'custom-notification-settings' )?></th>
			</tr>
		</thead>

		<tbody>

			<tr id="custom-notification-settings-theme">
				<td>&nbsp;</td>
				<td><?php _e( "New themes", 'custom-notification-settings' ) ?></td>
				<td class="yes"><input type="radio" name="notifications[notification_custom_new_theme]" id="notification-custom-new-theme-yes" value="yes" <?php checked( $theme, 'yes', true ) ?>/><label for="notification-custom-new-theme-yes" class="bp-screen-reader-text"><?php _e( 'Yes, send email', 'custom-notification-settings' ); ?></label></td>
				<td class="no"><input type="radio" name="notifications[notification_custom_new_theme]" id="notification-custom-new-theme-no" value="no" <?php checked( $theme, 'no', true ) ?>/><label for="notification-custom-new-theme-no" class="bp-screen-reader-text"><?php _e( 'No, do not send email', 'custom-notification-settings' ); ?></label></td>
			</tr>

			<tr id="custom-notification-settings-question">
				<td>&nbsp;</td>
				<td><?php _e( "New questions", 'custom-notification-settings' ) ?></td>
				<td class="yes"><input type="radio" name="notifications[notification_custom_new_question]" id="notification-custom-new-question-yes" value="yes" <?php checked( $question, 'yes', true ) ?>/><label for="notification-custom-new-question-yes" class="bp-screen-reader-text"><?php _e( 'Yes, send email', 'custom-notification-settings' ); ?></label></td>
				<td class="no"><input type="radio" name="notifications[notification_custom_new_question]" id="notification-custom-new-question-no" value="no" <?php checked( $question, 'no', true ) ?>/><label for="notification-custom-new-question-no" class="bp-screen-reader-text"><?php _e( 'No, do not send email', 'custom-notification-settings' ); ?></label></td>
			</tr>

			<tr id="custom-notification-settings-idea">
				<td>&nbsp;</td>
				<td><?php _e( "New ideas", 'custom-notification-settings' ) ?></td>
				<td class="yes"><input type="radio" name="notifications[notification_custom_new_idea]" id="notification-custom-new-idea-yes" value="yes" <?php checked( $idea, 'yes', true ) ?>/><label for="notification-custom-new-idea-yes" class="bp-screen-reader-text"><?php _e( 'Yes, send email', 'custom-notification-settings' ); ?></label></td>
				<td class="no"><input type="radio" name="notifications[notification_custom_new_idea]" id="notification-custom-new-idea-no" value="no" <?php checked( $idea, 'no', true ) ?>/><label for="notification-custom-new-idea-no" class="bp-screen-reader-text"><?php _e( 'No, do not send email', 'custom-notification-settings' ); ?></label></td>
			</tr>

			<tr id="custom-notification-settings-event">
				<td>&nbsp;</td>
				<td><?php _e( "New events", 'custom-notification-settings' ) ?></td>
				<td class="yes"><input type="radio" name="notifications[notification_custom_new_event]" id="notification-custom-new-event-yes" value="yes" <?php checked( $event, 'yes', true ) ?>/><label for="notification-custom-new-event-yes" class="bp-screen-reader-text"><?php _e( 'Yes, send email', 'custom-notification-settings' ); ?></label></td>
				<td class="no"><input type="radio" name="notifications[notification_custom_new_event]" id="notification-custom-new-event-no" value="no" <?php checked( $event, 'no', true ) ?>/><label for="notification-custom-new-event-no" class="bp-screen-reader-text"><?php _e( 'No, do not send email', 'custom-notification-settings' ); ?></label></td>
			</tr>

			<?php
			do_action( 'custom_screen_notification_settings' ) ?>
		</tbody>
	</table>
<?php
}
add_action( 'bp_notification_settings', 'custom_screen_notification_settings', 3 );
add_action( 'personal_options_update', 'save_custom_screen_notification_settings' );
add_action( 'edit_user_profile_update', 'save_custom_screen_notification_settings' );

function save_custom_screen_notification_settings( $user_id ) {

	if ( !current_user_can( 'edit_user', $user_id ) )
		return false;

	update_usermeta( $user_id, 'notification_custom_new_theme', $_POST['notification_custom_new_theme'] );
	update_usermeta( $user_id, 'notification_custom_new_question', $_POST['notification_custom_new_question'] );
	update_usermeta( $user_id, 'notification_custom_new_idea', $_POST['notification_custom_new_idea'] );
	update_usermeta( $user_id, 'notification_custom_new_idea', $_POST['notification_custom_new_event'] );
}

function custom_notification_send_mail ($author_id, $event, $title, $link ) {
	$setting_slug = 'notification_custom_new_'. $event;
	global $wpdb;	
	
	$admin_email = get_option('admin_email');
	$headers[] = 'From: Picasso';
	switch ($event) {
		case 'theme':
			$subject = __('New publication: theme.', 'custom-notification-settings') ;
			$event_string = __('theme', 'custom-notification-settings') ;
		break;
		case 'question':
			$subject = __('New publication: question.', 'custom-notification-settings') ;
			$event_string = __('question', 'custom-notification-settings') ;
		break;
		case 'idea':
			$subject = __('New publication: idea.', 'custom-notification-settings') ;
			$event_string = __('idea', 'custom-notification-settings') ;
		break;
		case 'event':
			$subject = __('New publication: event.', 'custom-notification-settings') ;
			$event_string = __('event', 'custom-notification-settings') ;
		break;
		default:
			$subject = __('New publication.', 'custom-notification-settings') ;
			$event_string = __('publication', 'custom-notification-settings') ;
		break;
	}
	$site = get_site_url();
	$message = sprintf( __('Hello, 
A new %s "%s" has been published.  You can click <a href="%s">here</a> to read it.
Have a great day on Picasso !
PS : you are receiving these notifications because your profile is currently set to receive it. If you wish not to receive this kind of mails anymore, you can set options at <a href="%s">your profile\'s setting</a>.', 'custom-notification-settings'), $event_string, $title, $link, $site );
	
	$table_name = $wpdb->prefix . 'users';
	$query = 'SELECT * FROM ' . $table_name;
	write_log($query);
	$users = $wpdb->get_results( $query );
	write_log($users);
	$email_list = array();
	
		foreach ( $users as $user ) {
			if ( ! $setting = bp_get_user_meta( $user->ID , $setting_slug, true ) ) {
				$setting = 'yes';
			}
			if ( $setting == 'yes' && $user->ID != $author_id ) {
				$email = $user->user_email;
				$header[] .= 'BCc: '. $email;
			}
		}
	wp_mail($admin_email,$subject,$message,$header);
	return;
}


add_action('sabai_entity_create_content_directory_listing_entity_success', 'custom_notification_new_theme', 10, 3);
function custom_notification_new_theme($bundle, $entity, $values) {
	$title = $entity->content_post_title[0];
	$link = get_site_url().$entity->getUrlPath($bundle);
	$author_id = get_current_user_id();	
	custom_notification_send_mail ($author_id, 'theme', $title, $link );
}

add_action('sabai_entity_create_content_questions_entity_success', 'custom_notification_new_question', 10, 3);
function custom_notification_new_question($bundle, $entity, $values) {
	$title = $entity->content_post_title[0];
	$link = get_site_url().$entity->getUrlPath($bundle);
	$author_id = get_current_user_id();	
	custom_notification_send_mail ($author_id, 'question', $title, $link );
}

add_action( 'save_post', 'custom_notification_new_event', 10, 2 );
function custom_notification_new_event($post_id, $post){ 
	if($post->post_type!='ajde_events')
		return;
	
	$author_id = $post->post_author;
	$title = $post->post_title;
	$link = get_site_url().'/evenements-2/';
	custom_notification_send_mail ($author_id, 'event', $title, $link );
}


add_action( 'after_save_idea', 'custom_notification_new_idea', 10, 2 );
function custom_notification_new_idea ( $post_id, $post ) {
	if($post->post_type!='ideas')
		return;
	$author_id = $post->post_author;
	$title = $post->post_title;
	$link = get_permalink( $post_id );
	custom_notification_send_mail ($author_id, 'idea', $title, $link );
}