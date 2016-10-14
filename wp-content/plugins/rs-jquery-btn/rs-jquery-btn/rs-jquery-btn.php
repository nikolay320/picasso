<?php
/**
* Plugin Name: RS jQuery Button
* Plugin URI: http://marylink.eu
* Description: jQuery Button by Rasheda Sultana
* Version: 1
* Author: Rasheda Sultana
* Author URI: http://www.sultana.me
*/
function rs_jquery_btn() {
	
	// Check its not admin/feed/trackback or robots
	if ( !is_admin() && !is_feed() && !is_robots() && !is_trackback() ) { 
		// Rs Function Start	
		function rs_jquery_btns(){ ?>
		
		<div class="rs-sticky-btn">
			<nav>
				<a id="rs-float-menu" class="rs-dropdown-toggle">
				<!-- The Button Image -->
					<img src="<?php echo plugin_dir_url( __FILE__ ) . 'plus.png'; ?>" class="rs-img"/>
				</a>
				<ul class="rs-dropdown">
				  <li><a class="evoAU_form_trigger_btn"><i class="fa fa-calendar"></i> New Event</a></li>
				  <li><a href="http://site7.marylink.eu/docs/"><i class="fa fa-file-word-o"></i> Documents</a></li>
				  <li><a href="http://site7.marylink.eu/questions/demande"><i class="fa fa-question"></i> Questions</a></li>
				  <li><a href="http://site7.marylink.eu/"><i class="fa fa-sticky-note"></i> Notes</a></li>
				  <li><a href="http://site7.marylink.eu/add-directory-listing" class=""><i class="fa fa-file"></i> Articles</a></li>
				  <li><a href="#" id="cpm-create-project"><i class="fa fa-building "></i> Start New Projects</a></li>				  
				</ul>
			</nav>
		</div>
		<style type="text/css">
		.rs-sticky-btn {position: fixed;right: 18.5%;top: 74px;}
		.rs-sticky-btn .rs-img{
			-webkit-transition: all 400ms linear;
			-moz-transition: all 400ms linear;
			-o-transition: all 400ms linear;
			-ms-transition: all 400ms linear;
			transition: all 400ms linear;
			position: static;height:45px;width:45px;
		}
		.rs-sticky-btn .rs-img-spinner {
			-webkit-transform: rotate(360deg);
			-moz-transform: rotate(360deg);
			-o-transform: rotate(360deg);
			-ms-transform: rotate(360deg);
			transform: rotate(360deg);
			height:45px;width:45px;
		}
		ul.rs-dropdown li a {text-decoration: none; color: #888;}
		/* Important stuff */
		nav {position: relative;}
		.rs-dropdown-toggle {padding: .5em 1em;border-radius: .2em .2em 0 0;cursor:pointer;}
		ul.rs-dropdown{display:none;position:absolute;top:84%;margin-top:.5em;background:#888;min-width:12em;padding:0;border-radius:0 0 .2em .2em;margin-left:-45px}
		ul.rs-dropdown li{list-style-type:none}
		ul.rs-dropdown li a{text-decoration:none;padding:.5em 1em;display:block;cursor:pointer;color:#fff}
		ul.rs-dropdown li a.evoAU_form_trigger_btn{background:#888;font-weight:400}
		ul.rs-dropdown li a:hover{background:#777}
		.rs-dropdown button{background:transparent;border:medium none;color:#fff;padding:.5em 1em;width:100%;text-align:left}
		.rs-dropdown button:hover{background:#777}
		#event-submit-hide .evoAU_form_trigger_btn{display:none}
		</style>
		<!-- Font Awesome CSS For icon on menu -->
		<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.css' type='text/css' media='all' />
		<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
		<script>
		var jq213 = jQuery.noConflict(true); 
		jQuery(function($){

				// Click Button toggle dropdown Menu
				$('.rs-dropdown-toggle').click(function(){
				  $(this).next('.rs-dropdown').slideToggle('slow');
				});
				// Click Button to Rotate it
				$('.rs-img').click(function(){
					  $(this).toggleClass('rs-img-spinner');
				});
				$('#buddyboss-media-open-uploader-button').click(function(){
					  $('.fancybox-overlay fancybox-overlay-fixed').show();
				});
				
				// Click Anywhere in webpage, dropdown close
				$(document).click(function(e) {
				  var target = e.target;
				  if (!$(target).is('.rs-dropdown-toggle') && !$(target).parents().is('.rs-dropdown-toggle')) {
					$('.rs-dropdown').slideUp();
				  }
				});

		});
		</script>
		<?php
		
		/***************************************************
				Event Manager Model Code for All Page
		****************************************************/
			echo "<div id='event-submit-hide'>".do_shortcode('[add_evo_submission_form ligthbox="yes" ]')."</div>";		
			
			/*$bossupload = new BuddyBoss_Media_Type_Photo;
			echo $bossupload->script_templates();	*/		
		?>
		
		<?php 
		/***************************************************
				Start Project Manager Model for All Page
		****************************************************/
		?>
		
		<?php
		$project_obj        = CPM_Project::getInstance();
		$projects           = $project_obj->get_projects();
		$total_projects     = $projects['total_projects'];
		$pagenum            = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
		$db_limit           = intval( cpm_get_option( 'pagination' ) );
		$limit              = $db_limit ? $db_limit : 10;
		$status_class       = isset( $_GET['status'] ) ? $_GET['status'] : 'active';
		$count              = cpm_project_count();
		$can_create_project = cpm_manage_capability( 'project_create_role' );
		$class              = $can_create_project ? '' : ' cpm-no-nav';
		$dpv                = get_user_meta( get_current_user_id(), '_cpm_project_view', true );
		$project_view       = in_array( $dpv, array( 'grid', 'list' ) ) ? $dpv : 'grid';
		unset( $projects['total_projects'] );
		?>
		<div class="cpm-row cpm-no-padding cpm-priject-search-bar" style="display:none">
			<div class="cpm-col-3 cpm-sm-col-12 cpm-no-padding cpm-no-margin">
				 <?php if ( $can_create_project ) { ?>
					<a href="#" id="cpm-create-project" class="cpm-btn cpm-plus-white"><?php _e( 'NEW PROJECT', 'cpm' ); ?></a>
				<?php } ?>
			</div>
			<div class="clearfix"> </div>
		</div>
	<?php if ( $can_create_project ) { ?>
		<div id="cpm-project-dialog" style="display:none; z-index:999;" title="<?php _e( 'Start a new project', 'cpm' ); ?>">
			<?php cpm_project_form(); ?>
		</div>
		<div id="cpm-create-user-wrap" title="<?php _e( 'Create a new user', 'cpm' ); ?>">
			<?php cpm_user_create_form(); ?>
		</div>
		<script type="text/javascript">
			jQuery(function($) {
				$( "#cpm-project-dialog" ).dialog({
					autoOpen: false,
					modal: true,
					dialogClass: 'cpm-ui-dialog',
					width: 485,
					height: 430,
					position:['middle', 100],
					zIndex: 9999,
				});
			});
			jQuery(function($) {
				$( "#cpm-create-user-wrap" ).dialog({
					autoOpen: false,
					modal: true,
					dialogClass: 'cpm-ui-dialog cpm-user-ui-dialog',
					width: 400,
					height: 'auto',
					position:['middle', 100],
				});
			});
		</script>
	<?php } ?>
	<script type="text/javascript">
		jQuery(function($) {
			if ( document.body.clientWidth < 780 ) {
				$( ".cpm-projects" ).removeClass( "cpm-project-list" ) ;
				$( ".cpm-projects" ).addClass( "cpm-project-grid" );
				$( "#cpm-list-view span" ).removeClass( 'active' ) ;
				$( "#cpm-grid-view span" ).addClass( 'active' ) ;
			}
		});
	</script>
	<?php /******** End Project Manager Model for All Page *********/ ?>
	
		<?php } // End Rs Functions
		
		rs_jquery_btns(); // Return the funtions to page for user.
		
	} // admin/feed/trackback founction end
} // End Main Function
add_action('wp_footer', 'rs_jquery_btn');
?>