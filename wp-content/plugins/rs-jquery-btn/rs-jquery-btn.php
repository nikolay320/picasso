<?php
/**
* Plugin Name: RS jQuery Button
* Plugin URI: http://www.sultana.me
* Description: jQuery Button by Rasheda Sultana
* Version: 1.1
* Author: Rasheda Sultana
* Author URI: http://www.sultana.me
*/
function rs_jquery_btn() {
	
	// Check its not admin/feed/trackback or robots
	if ( !is_admin() && !is_feed() && !is_robots() && !is_trackback() ) { 
		// Rs Function Start	
		function rs_jquery_btns(){
			$idea_create_page = function_exists('pi_idea_create_page') ? pi_idea_create_page() : '';
		?>
		
		<div class="rs-sticky-btn">
			<nav>
				<a id="rs-float-menu" class="rs-dropdown-toggle">
					<!-- Change Button Image Name from Here -->
					<img src="<?php echo plugin_dir_url( __FILE__ ) . 'plus.png'; ?>" class="rs-img"/>
				</a>

				<ul class="rs-dropdown">
				  <li><i class="fa fa-calendar-o icons" aria-hidden="true"></i><a class="evoAU_form_trigger_btn">EVENEMENT</a><a class="evoAU_form_trigger_btn new">Publier un evenement</a></li>
				  <li><i class="fa fa-question-circle" aria-hidden="true"></i><a href="<?=get_site_url()?>/questions/demande">QUESTION</a><a href="<?=get_site_url()?>/questions/demande" class="new">Obtenir des reponses</a></li>
				  <li><i class="fa fa-lightbulb-o icons" aria-hidden="true"></i><a href="<?php echo $idea_create_page; ?>">IDEE</a><a href="<?php echo $idea_create_page; ?>" class="new" >Publier une idee</a></li>
				  <li><i class="fa fa-pencil-square-o" aria-hidden="true"></i><a href="<?=get_site_url()?>/add-directory-listing">THEME</a><a href="<?=get_site_url()?>/add-directory-listing" class="new">Publier un article, avec photos, videos, documents..</a></li>				  
				  <li><i class="fa fa-sticky-note-o icons" aria-hidden="true"></i><a href="#" id="kcbp-quick-activity">NOTE RAPIDE</a><a href="#" id="kcbp-quick-activity" class="new">Publier une note publique en 2 clics</a></li>
				  <li><i class="fa fa-envelope-o" aria-hidden="true"></i><a href="#" id="cpm-create-message">MESSAGE</a><a href="#" id="cpm-create-message" class="new">Envoyer un message prive a une ou plusieurs personnes</a></li>
				  <li><i class="fa fa-clipboard icons" aria-hidden="true"></i><a href="#" id="cpm-create-project" class="color">PROJET</a><a href="#" id="cpm-create-project" class="new">Creer un project prive</a></li>
                  <li><i class="fa fa-tasks icons" aria-hidden="true"></i><a href="#" id="cpm-create-task"  class="color">TACHE</a><a href="#" id="cpm-create-task" class="new">Creer une tache dans un project Prive (Liste de taches necessaire)</a></li>
                  <li><i class="fa fa-users icons" aria-hidden="true"></i><a href="#" id="cpm-create-discussion" class="color">DISCUSSION</a><a href="#" id="cpm-create-discussion" class="new">Publier une discussion dans un project prive</a></li>
				</ul>
			</nav>
		</div>
		<style type="text/css">
		.ui-widget-overlay{ background:#999 !important}

			.rs-sticky-btn {position: fixed;right: 18.5%;top: 40px !important;z-index: 9999;}
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
/*
			ul.rs-dropdown{display:none;position:absolute;top:84%;margin-top:.5em;background:#607d8b;min-width:12em;padding:0;border-radius:0 0 .2em .2em;margin-left:-45px}
			ul.rs-dropdown li{list-style-type:none}
			ul.rs-dropdown li a{text-decoration:none;padding:.5em 1em;display:block;cursor:pointer;color:#fff}
			ul.rs-dropdown li a.evoAU_form_trigger_btn{background:#607d8b;font-weight:400}
			ul.rs-dropdown li a:hover{background:#FF3700}
*/
			ul.rs-dropdown{display:none;position:absolute;top:84%;margin-top:.5em;background:#607d8b;width: 39em !important;;padding:0;border-radius:0 0 .2em .2em;margin-left:0;
			right:11px}
			ul.rs-dropdown li{list-style-type:none; float: left; width: 100%;
			border-left:5px solid transparent;
			padding: 3px 0;
			}
			ul.rs-dropdown li a {
    text-decoration: none;
    padding: 7px 0;
    display: block;
    cursor: pointer;
    color: #000;
    font-weight: bold !important;
   font-size: 12px;
	margin-top:0;
	float:left;
	margin-right:10px;
}
			ul.rs-dropdown li a.evoAU_form_trigger_btn{background:transparent !important;font-weight:400;
			text-decoration:none;
			  margin-right: 10px;}
			ul.rs-dropdown li:hover{background:#F5F5F5 !important; text-decoration: underline; border-left: 5px solid #E0E0E0; transition: none !important;}
			ul.rs-dropdown {background: #fefefe none repeat scroll 0 0 !important;}
			.rs-dropdown button{background:transparent;border:medium none;color:#fff;padding:.5em 1em;width:100%;text-align:left}
			.rs-dropdown button:hover{background:#FF3700}
			#event-submit-hide .evoAU_form_trigger_btn{display:none}
			.cpm-col-7.cpm-sm-col-12.cpm-project-search {z-index: 1;}
			#eventon_form p #evoau_submit:hover, body a.evoAU_form_trigger_btn:hover, .evoau_submission_form .msub_row a:hover {
            color: #000 !important;
            opacity: 0.7;}
			.rs-dropdown li span {
			float: left;
			margin-right: 12px;}
		i.icons {
    float: left;
    width: 10%;
    padding: 2px 0 2px 16px;
    font-size: 25px;
    color: #bcbcbc;
		}
			ul.first li {
    list-style: none;
}
		ul.rs-dropdown {
    background: #fefefe none repeat scroll 0 0 !important;
    padding: 0 0 0 0;
     margin-top: 20px;
}	
.rs-dropdown:before {
    position: absolute;
    display: block;
    content: "";
    top: -5px;
    right: 20px;
    width: 10px;
    height: 10px;
    -webkit-transform: rotate(45deg);
    -moz-transform: rotate(45deg);
    -ms-transform: rotate(45deg);
    -o-transform: rotate(45deg);
    border-style: none;
    border-width: 1px;
    border-right: 0;
    border-bottom: 0;
    z-index: 999;
    background: #fff;
}
i.fa.fa-question-circle {
    font-size: 25px;
    float: left;
    color: #bcbcbc;
    width: 10%;
    padding: 5px 0 2px 16px;
}
i.fa.fa-lightbulb-o.icons {
    padding-top: 7px;
}
i.fa.fa-envelope-o {
    font-size: 25px;
    float: left;
    color: #bcbcbc;
    width: 10%;
    padding: 2px 0 2px 16px;
}
i.fa.fa-paint-brush{
    font-size: 25px;
    float: left;
    color: #bcbcbc;
    width: 10%;
    padding: 2px 0 2px 16px;
}
i.fa.fa-pencil-square-o{
    font-size: 25px;
    float: left;
    color: #bcbcbc;
    width: 10%;
    padding: 2px 0 2px 16px;
}
ul.rs-dropdown li .new {
    color: #bcbcbc;
    float: left;
    text-decoration: none !important;
    text-transform: uppercase;
	background:transparent;
}
ul.rs-dropdown li .new:hover{color: #bcbcbc !important;
}
ul.rs-dropdown li a:hover {
    background: transparent !important;
	text-decoration:underline;
}

.color {
    color: #428bca !important;
}
	
i.fa.fa-clipboard.icons ,i.fa.fa-tasks.icons , i.fa.fa-users.icons{
    color: #00b9f7;
	    padding-top: 4px;
}
i.fa.fa-calendar-o.icons {
    color: #bcbcbc;
}
li:hover i.fa.fa-calendar-o.icons, li:hover i.fa.fa-question-circle, li:hover i.fa.fa-lightbulb-o.icons,li:hover i.fa.fa-pencil-square-o,li:hover i.fa.fa-sticky-note-o.icons, li:hover i.fa.fa-envelope-o {
    color: #FE5815;
}
		</style>
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
				$('#cpm-create-task').click(function(){
					$( "#cpm-task-dialog" ).dialog('open');
					 $('html, body').animate({
        scrollTop: $("#cpm-task-dialog").offset().top-100 }, 1);
					});
					
				$('#cpm-create-discussion').click(function(){
					$( "#cpm-discussion-dialog" ).dialog('open');
					 $('html, body').animate({
        scrollTop: $("#cpm-discussion-dialog").offset().top-100 }, 1);
					});	

$('#cpm-create-message').click(function(){
					$( "#cpm-message-dialog" ).dialog('open');
					 $('html, body').animate({
        scrollTop: $("#cpm-message-dialog").offset().top-100 }, 1);
					});					
					
				$('#cpm-create-project').click(function(){
					$( "#cpm-project-dialog" ).dialog('open');
					 $('html, body').animate({
        scrollTop: $("#cpm-project-dialog").offset().top-100 }, 1);
					});
				$('#kcbp-quick-activity').click(function(e) {
					e.preventDefault();

					if ( $('.bpqa-popup-template-holder').is(':hidden') ) {
						$('.bpqa-popup-template-holder').fadeToggle();
					}
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
		
if(class_exists('CPM_Project')):
		
		$project_obj        = CPM_Project::getInstance();
		$projects           = $project_obj->get_projects();
		$total_projects     = $projects['total_projects'];
		$pagenum            = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
		$db_limit           = intval( cpm_get_option( 'pagination' ) );
		$limit              = $db_limit ? $db_limit : 10;
		$status_class       = isset( $_GET['status'] ) ? $_GET['status'] : 'active';
		$count              = cpm_project_count();
		$can_create_project = cpm_can_create_projects();
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
		<div id="cpm-project-dialog" style="display:none; z-index:999;" title="Démarrez un nouveau projet">
			<?php cpm_project_form(); ?>
		</div>
        		<div id="cpm-task-dialog" style="display:none; z-index:999;" title="Démarrer une nouvelle tâche">
			<?php echo do_shortcode('[cpm_add_task]'); ?>
		</div>
        <div id="cpm-discussion-dialog" style="display:none; z-index:999;" title="Démarrer une nouvelle discussion">
			<?php echo do_shortcode('[cpm_add_discussion]'); ?>
		</div>
		<div id="cpm-message-dialog" style="display:none; z-index:999;" title="Démarrer une nouvelle message">
		<?php
		bp_get_template_part( 'members/single/messages/compose' );
?>
			
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
				$( "#cpm-task-dialog" ).dialog({
					autoOpen: false,
					modal: true,
					dialogClass: 'cpm-ui-dialog',
					width: 485,
					height: 470,
					position:['middle', 100],
					zIndex: 9999,
				});
			});
			jQuery(function($) {
				$( "#cpm-discussion-dialog" ).dialog({
					autoOpen: false,
					modal: true,
					dialogClass: 'cpm-ui-dialog',
					width: 520,
					height: 530,
					position:['middle', 100],
					zIndex: 9999,
				});
			});
			jQuery(function($) {
				$( "#cpm-message-dialog" ).dialog({
					autoOpen: false,
					modal: true,
					dialogClass: 'cpm-ui-dialog',
					width: 520,
					height: 530,
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
<?php endif; ?>
	<?php /******** End Project Manager Model for All Page *********/ ?>
	<?php /******** IDEES JS And Code Start *************/ ?>
	<?php  if(function_exists('add_idea_modal')) { add_idea_modal(); } ?>
	
		<?php } // End Rs Functions
		
			if ( is_user_logged_in() ) {
				rs_jquery_btns(); // Return the funtions to page for user.
			}
		
	} // admin/feed/trackback founction end
} // End Main Function
add_action('wp_footer', 'rs_jquery_btn');


?>
