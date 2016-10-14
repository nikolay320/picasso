<?php

/**
 * BuddyPress - Cover Header
 *
 * @package BuddyPress
 * @subpackage bp-legacy
 */

?>

<?php

/**
 * Fires before the display of a member's header.
 *
 * @since 1.2.0
 */
do_action( 'bp_before_member_header' ); ?>

	<style>
	<?php echo kleo_child_bp_member_set_color_style(); ?>
	</style>

	<div id="item-header-avatar" class="rounded <?php echo kleo_child_bp_member_get_color_class(bp_displayed_user_id()); ?>">
		<a href="<?php bp_displayed_user_link(); ?>">

			<?php bp_displayed_user_avatar( 'type=full' ); ?>

		</a>
		<?php do_action('bp_member_online_status', bp_displayed_user_id()); ?>
	</div><!-- #item-header-avatar -->

	<div id="item-header-content" <?php if (isset($_COOKIE['bp-profile-header']) && $_COOKIE['bp-profile-header'] == 'small') {echo 'style="display:none;"';} ?>>

		<?php if ( bp_is_active( 'activity' ) && bp_activity_do_mentions() ) : ?>
			<h4 class="user-nicename">@<?php bp_displayed_user_mentionname(); ?></h4>
		<?php endif; ?>

		<span class="activity"><?php bp_last_activity( bp_displayed_user_id() ); ?></span>

		<?php do_action( 'bp_before_member_header_meta' ); ?>

	</div><!-- #item-header-content -->

	<!-- KLEO MYCRED PROFILE RANK -->
	<?php
	if ( class_exists( 'myCRED_Core' ) ) :
	?>
	<div class="kleo-mycred-profile-rank">
		<?php
			$kleo_child_is_current_user_has_rank = false;
			$kleo_child_current_user_rank = mycred_get_rank_id_from_title(mycred_get_users_rank( bp_displayed_user_id() ));

			if(strlen($kleo_child_current_user_rank) != 0 && $kleo_child_current_user_rank !== NULL) {
				$kleo_child_is_current_user_has_rank = true;
			} else {
				$kleo_child_is_current_user_has_rank = false;
			}
		?>
		<?php kleo_child_mycred_display_users_badge(bp_displayed_user_id()); ?>
		<div>
			<span class="profile-rank-header">
			<?php
				if($kleo_child_is_current_user_has_rank == true) {
					echo mycred_get_users_rank( bp_displayed_user_id() );
				}
			?>
			</span>
			<span class="profile-rank-img">
			<?php
				if($kleo_child_is_current_user_has_rank == true) {
					echo mycred_get_users_rank( bp_displayed_user_id(), 'logo' );
				}
			?>
			</span>
			<span class="points-count">
			<?php echo mycred_get_users_cred( bp_displayed_user_id()); ?> POINTS
			</span>
		</div>
	</div>
	<?php
	endif;
	?>
	<!-- KLEO MYCRED PROFILE RANK -->


<?php do_action( 'bp_after_member_header' ); ?>

<?php do_action( 'template_notices' ); ?>