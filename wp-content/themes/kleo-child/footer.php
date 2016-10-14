<?php
/**
 * The template for displaying the footer
 *
 * Contains footer content and the closing of the #main and #page div elements.
 *
 * @package WordPress
 * @subpackage Kleo
 * @since Kleo 1.0
 */
?>

			<?php
			/**
			 * After main part - action
			 */
			do_action('kleo_after_main');
			?>

		</div><!-- #main -->

		<?php get_sidebar('footer');?>
	
		<?php 
		/**
		 * After footer hook
		 * @hooked kleo_go_up
		 * @hooked kleo_show_contact_form
		 */
		do_action('kleo_after_footer');
		?>

	</div><!-- #page -->

    <?php
    /**
     * After page hook
     * @hooked kleo_show_side_menu 10
     */
    do_action('kleo_after_page');
    ?>

	<div id="kleo_child_about_page_container" class="kleo-child-about-page-container col-md-8 col-md-offset-2">
			<?php echo kleo_child_show_about_page(); ?>
	</div>

	<!-- Analytics -->
	<?php echo sq_option('analytics', ''); ?>
	
	<?php wp_footer(); ?>
<script>

jQuery( document ).ready(function() {
	
	 jQuery( ".mpp-drag-drop-inside" ).append( "<input type='button' id='cancel_upload' value='Annuler'>" );
	 jQuery( ".hide_main" ).prev('.buddyboss_edit_activity').css( "display", "none" ); 
	jQuery('video').attr('poster',' ');
	jQuery('video').attr('preload','auto');
    jQuery('#cancel_upload').click(function() {

	jQuery('.mpp-dropzone').css('display','none'); 
});
});

</script>

				  
 

		   

			
</body>
</html>