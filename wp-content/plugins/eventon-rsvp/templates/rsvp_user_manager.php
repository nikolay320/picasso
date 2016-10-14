<?php
/** 
 * User RSVP manager
 * @version 0.2
 * @author  AJDE
 *
 * You can copy this template file and place it in ...wp-content/themes/<--your-theme-name->/eventon/rsvp/ folder 
 * and edit that file to customize this template.
 * This sub content template will use default page.php template from your theme and the below
 * content will be placed in content area of the page template.
 */
	echo "<h2>".__('Events I have RSVP-ed to','eventon')."</h2>";
	if(!is_user_logged_in()){
		echo "<p>Login required to manage events you have RSVP-ed. <br/><a href='".wp_login_url($current_page_link)."' class='evcal_btn evors'><i class='fa fa-user'></i> ".__('Login Now','eventon')."</a></p>";
		return;
	}
?>

Hello <?php echo $current_user->display_name?>. From your RSVP manager dashboard you can view events you have RSVP-ed to and update options.

<h3><?php _e('My Events','eventon');?></h3>
<?php	
	$rsvps = $eventon_rs->frontend->functions->get_user_events($current_user->ID);

	if(!empty($rsvps)){
		echo "<div class='eventon_rsvp_rsvplist' data-uid='{$current_user->ID}'>";
		echo $rsvps;
		echo "</div>";
	}
?>