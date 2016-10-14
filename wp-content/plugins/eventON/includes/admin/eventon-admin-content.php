<?php
/**
 * Functions used for the showing help/links to eventon resources in admin
 *
 * @author 		EventON
 * @category 	Admin
 * @package 	Eventon/Admin
 * @version     0.3
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/** Help Tab Content*/
function eventon_admin_help_tab_content() {
	$screen = get_current_screen();

	ob_start();
?>
<p><b>EventON WP Event Calendar General Information.</b></p>
<p><a class='evo_admin_btn btn_prime' href='<?php echo get_admin_url();?>index.php?page=evo-getting-started'>Getting started guide to eventON</a></p>
<p>All the updated documentation for eventON can be found from our <a class='evo_admin_btn btn_triad'href='http://www.myeventon.com/documentation/'>online documentation library.</a></p>
<?php
	$content = ob_get_clean();

	ob_start();
?>
<p><b>Support for EventON Calendar Plugin.</b></p>
<p>We do provide support for issues directly related to eventON calendar via our <a href='http://support.ashanjay.com/forum/eventon/' target='_blank' class='evo_admin_btn btn_triad'>support forum</a> We are a small team so please allow us time to get back to forum issues.</p>
<p>Before writing on support forum please check the troubleshooter guide that can very well help you solve your issue by trying our common solutions.</p>
<p>Please check out our <a href='http://www.myeventon.com/documentation/check-eventon-working/' class='evo_admin_btn btn_triad' target='_blank' >troubleshooter guide to eventon</a></p>
<?php
	$support = ob_get_clean();

	$screen->add_help_tab( 
		array(
		    'id'	=> 'eventon_overview_tab',
		    'title'	=> __( 'General', 'eventon' ),
		    'content'	=>$content
		));
	$screen->add_help_tab( array(
		    'id'	=> 'eventon_overview_tab_s',
		    'title'	=> __( 'Support', 'eventon' ),
		    'content'	=>$support
		) 
	);

	

	$screen->set_help_sidebar(
		'<p><strong>' . __( 'For more information:', 'eventon' ) . '</strong></p>' .
		'<p><a href="http://www.myeventon.com/" target="_blank">' . __( 'EventON', 'eventon' ) . '</a></p>' .
		'<p><a href="http://www.myeventon.com/faq/" target="_blank">' . __( 'FAQ Section', 'eventon' ) . '</a></p>' .
		'<p><a href="http://www.myeventon.com/changelog/" target="_blank">' . __( 'Changelog', 'eventon' ) . '</a></p>'.
		'<p><a href="http://www.myeventon.com/documentation/" target="_blank">' . __( 'Documentation', 'eventon' ) . '</a></p>'.
		'<p><a href="http://www.myeventon.com/addons/" target="_blank">' . __( 'Addons', 'eventon' ) . '</a></p>'
	);
}
?>