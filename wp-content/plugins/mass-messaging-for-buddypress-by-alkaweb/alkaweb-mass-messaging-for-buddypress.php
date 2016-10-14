<?php

/**
 * Plugin Name: Mass Messaging for Buddypress - by Alkaweb
 * Description: Allow to send mass messages to all members, all members of specific groups, all memebrs with specific roles.
 * Version: 1.0.1
 * Author: Alkaweb
 * Author URI: https://woffice.io/
 * License: GPL2 or later
 * Text Domain: alkaweb-bmm
 *
 * @author Alkaweb
 * @author Webbaku
 * @package Mass Messaging for BuddyPress - by Alkaweb
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) )
	die();


function alkaweb_bmm_enqueues() {


	//basic css plugin
	wp_enqueue_style( 'alkaweb_bmm_style', plugins_url('/css/style.css', __FILE__) );

}
add_action( 'admin_enqueue_scripts', 'alkaweb_bmm_enqueues' );

if(!function_exists('alkaweb_bmm_menu_creation')) {
	function alkaweb_bmm_menu_creation() {
		//add an item to the menu
		add_menu_page(
			'Mass Messaging for BuddyPress',
			'Mass Messaging for BuddyPress',
			'administrator',
			'alkaweb-buddypress-mass-messaging',
			'alkaweb_bmm_render_primary_page',
			'dashicons-email-alt'
		);

	}
}
add_action( 'admin_menu', 'alkaweb_bmm_menu_creation' );

if(!function_exists('alkaweb_bmm_render_primary_page')) {
	function alkaweb_bmm_render_primary_page() {
		?>

		<?php if( bp_is_active ( 'messages' ) ) : ?>
			<div class="wrap">
				<form action="<?php echo get_bloginfo('url') ?>/wp-admin/admin.php?page=alkaweb-buddypress-mass-messaging" method="post" id="alkaweb-bbp-mass-messaging">
				<h2>BuddyPress Mass Messaging - by Alkaweb</h2>
				<table class="form-table alkaweb-bmm-table">
					<tbody>
						<tr><th><?php esc_html_e('Subject', 'alkaweb-bmm'); ?></th><td colspan="3"><input name="alkaweb_bmm_subject" type="text" value="" size="45" /></td></tr>
						<tr><th><?php esc_html_e('Content', 'alkaweb-bmm'); ?></th><td colspan="3"><textarea name="alkaweb_bmm_content" cols="45" rows="10"></textarea></tr>
						<tr>
							<th><?php esc_html_e('Receivers', 'alkaweb-bmm'); ?></th>
							<td class="alkaweb-bmm-receivers">
								<h4><?php esc_html_e('All', 'alkaweb-bmm'); ?></h4>
								<p><label><input type="checkbox" name="alkaweb_bmm_receiver_all" value="all"> <?php esc_html_e('All', 'alkaweb-bmm'); ?></label></p>
							</td>
							<td class="alkaweb-bmm-receivers">
								<h4><?php esc_html_e('Groups', 'alkaweb-bmm'); ?></h4>
								<?php
								if(bp_is_active('groups')) {
									$groups = BP_Groups_Group::get(array(
										'type'=>'alphabetical',
										'per_page'=>999
									));
									foreach($groups['groups'] as $group) {
										echo '<p><label><input type="checkbox" name="alkaweb_bmm_receiver_groups[]" value="'.$group->id.'">'.$group->name.'</label></p>';
									}
								} else {
									esc_html_e("Groups component is not active.", 'alkaweb-bmm');
								}
								?>
							</td>
							<td class="alkaweb-bmm-receivers">
								<h4><?php esc_html_e('Roles', 'alkaweb-bmm'); ?></h4>
								<?php
								foreach (get_editable_roles() as $role_name => $role_info){

									echo '<p><label><input type="checkbox" name="alkaweb_bmm_receiver_roles[]" value="'.$role_name.'">'.$role_info['name'].'</label></p>';
								}
								?>
							</td>
						</tr>
					</tbody>
				</table>
				<br />


				<?php wp_nonce_field( 'alkaweb_bmm' ); ?>

				<input name="alkaweb_bmm_submit" type="submit" class="button button-primary" value="<?php esc_html_e('Send Messages', 'alkaweb-bmm'); ?>"/></form>
			</div>
			<?php else: ?>

			<div id="message" class="error fade below-h2"><p><?php printf( __('Please activate the <a href="%s">BuddyPress private messaging component</a> in order to use this plugin', 'alkaweb-bmm'), admin_url('admin.php?page=bp-component-setup') ); ?></p></div>

		<?php endif; ?>

		<?php
	}
}

function alkaweb_bmm_send_mass_message() {

	if(
		isset($_GET['page'])
		&& $_GET['page'] == 'alkaweb-buddypress-mass-messaging'
		&& isset($_POST['alkaweb_bmm_submit'])
	) {
		check_admin_referer( 'alkaweb_bmm' );

		$subject = ( isset( $_POST['alkaweb_bmm_subject'] ) ) ? sanitize_text_field($_POST['alkaweb_bmm_subject']) : '';
		$content = ( isset( $_POST['alkaweb_bmm_content'] ) ) ? sanitize_text_field($_POST['alkaweb_bmm_content']) : '';

		if(empty($subject) || empty($content)) {
			echo "<script type='text/javascript'> alert('" . esc_html__( 'Please fill Subject and Content fields', 'alkaweb-bmm' ) . "')</script>";
			return;
		}

		$all    = ( isset( $_POST['alkaweb_bmm_receiver_all'] ) && $_POST['alkaweb_bmm_receiver_all'] == 'all' );
		$groups = ( isset( $_POST['alkaweb_bmm_receiver_groups'] ) ) ? $_POST['alkaweb_bmm_receiver_groups'] : array();
		$roles  = ( isset( $_POST['alkaweb_bmm_receiver_roles'] ) ) ? $_POST['alkaweb_bmm_receiver_roles'] : array();

		//Counter for sent messages
		$c = 0;

		if($all) {
			//If the message have to be sent to all members
			$users = get_users();

			foreach($users as $user) {
				if($user->ID == get_current_user_id())
					continue;

				if( messages_new_message( array('sender_id' => get_current_user_id(), 'subject' => $subject, 'content' => $content, 'recipients' => $user->ID) ) )
					$c++;
			}

		} else {
			$receivers = array();
			
			//Get receivers from groups
			foreach($groups as $group) {
				$group_members = groups_get_group_members(array('group_id' => $group));
				foreach($group_members['members'] as $member){
					if(!in_array($member->ID, $receivers))
						array_push($receivers, $member->ID);
				}
			}
			
			//Get receivers from roles
			$all_users = get_users();

			foreach($all_users as $user) {
				$the_user_role = (array) $user->roles;
				$role_intersect = array_intersect( $the_user_role, $roles );

				if($role_intersect && !in_array($user->ID, $receivers) )
					array_push($receivers, $user->ID);

			}


			foreach($receivers as $id) {
				if($id == get_current_user_id())
					continue;

				if( messages_new_message( array('sender_id' => get_current_user_id(), 'subject' => $subject, 'content' => $content, 'recipients' => $id) ) )
					$c++;
			}

		}

		//Internationalizing
		if($c >= 1){
			echo "<script type='text/javascript'> alert('". sprintf(_n( '%s message sent succesfully', '%s messages sent succesfully', $c, 'alkaweb-bmm' ), $c)."')</script>";
		} else {
			echo "<script type='text/javascript'> alert('".esc_html__('An error occurred, please try again', 'alkaweb-bmm')."')</script>";
		}
	}

}
add_action('admin_init', 'alkaweb_bmm_send_mass_message');