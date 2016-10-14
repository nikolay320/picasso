<?php
/**
 * 
 * Admin settings class
 *
 * @author 		AJDE
 * @category 	Admin
 * @package 	eventon-rsvp/classes
 * @version     0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class evorsvp_admin{
	
	public $optRS;
	function __construct(){
		add_action('admin_init', array($this, 'evoRS_admin_init'));
		include_once('evo-rsvp.php');
		include_once('evo-rsvp_meta_boxes.php');

		add_filter( 'eventon_appearance_add', array($this, 'evoRS_appearance_settings' ), 10, 1);
		add_filter( 'eventon_inline_styles_array',array($this, 'evoRS_dynamic_styles') , 10, 1);

		// eventtop
		//add_action('eventon_eventop_fields', array($this,'eventtop_option'), 10, 1);
		add_action( 'admin_menu', array( $this, 'menu' ),9);

		// delete rsvp
		add_action('wp_trash_post',array($this,'trash_rsvp'),1,1);

		// duplicating event
		add_action('eventon_duplicate_product',array($this,'duplicate_event'), 10, 2);
	}

	// INITIATE
		function evoRS_admin_init(){

			// icon
			add_filter( 'eventon_custom_icons',array($this, 'evoRS_custom_icons') , 10, 1);

			// eventCard inclusion
			add_filter( 'eventon_eventcard_boxes',array($this,'evoRS_add_toeventcard_order') , 10, 1);

			// language
			add_filter('eventon_settings_lang_tab_content', array($this, 'evoRS_language_additions'), 10, 1);

			global $pagenow, $typenow, $wpdb, $post;	
			
			if ( $typenow == 'post' && ! empty( $_GET['post'] ) ) {
				$typenow = $post->post_type;
			} elseif ( empty( $typenow ) && ! empty( $_GET['post'] ) ) {
		        $post = get_post( $_GET['post'] );
		        $typenow = $post->post_type;
		    }
			
			if ( $typenow == '' || $typenow == "ajde_events" || $typenow =='evo-rsvp') {

				// Event Post Only
				$print_css_on = array( 'post-new.php', 'post.php' );

				foreach ( $print_css_on as $page ){
					add_action( 'admin_print_styles-'. $page, array($this,'evoRS_event_post_styles' ));		
				}
			}

			// include rsvp id in the search
			if($typenow =='' || $typenow == 'evo-rsvp'){
				// Filter the search page
				add_filter('pre_get_posts', array($this, 'evors_search_pre_get_posts'));		
			}

			if($pagenow == 'edit.php' && $typenow == 'evo-rsvp'){
				add_action( 'admin_print_styles-edit.php', array($this, 'evoRS_event_post_styles' ));	
			}

			// settings
			add_filter('eventon_settings_tabs',array($this, 'evoRS_tab_array' ),10, 1);
			add_action('eventon_settings_tabs_evcal_rs',array($this, 'evoRS_tab_content' ));		
		}

	// other hooks
		function evors_search_pre_get_posts($query){
		    // Verify that we are on the search page that that this came from the event search form
		    if($query->query_vars['s'] != '' && is_search())
		    {
		        // If "s" is a positive integer, assume post id search and change the search variables
		        if(absint($query->query_vars['s']) ){
		            // Set the post id value
		            $query->set('p', $query->query_vars['s']);

		            // Reset the search value
		            $query->set('s', '');
		        }
		    }
		}		

		function evoRS_event_post_styles(){
			global $eventon_rs;
			wp_enqueue_style( 'evors_admin_post',$eventon_rs->plugin_url.'/assets/admin_evors_post.css');
			wp_enqueue_script( 'evors_admin_post_script',$eventon_rs->plugin_url.'/assets/RS_admin_script.js',array(), $eventon_rs->version);
			wp_localize_script( 
				'evors_admin_post_script', 
				'evors_admin_ajax_script', 
				array( 
					'ajaxurl' => admin_url( 'admin-ajax.php' ) , 
					'postnonce' => wp_create_nonce( 'eventonrs_nonce' )
				)
			);
		}
		function evoRS_add_toeventcard_order($array){
			$array['evorsvp']= array('evorsvp',__('RSVP Event Box','eventon'));
			return $array;
		}

		function evoRS_custom_icons($array){
			$array[] = array('id'=>'evcal__evors_001','type'=>'icon','name'=>'RSVP Event Icon','default'=>'fa-envelope');
			return $array;
		}
		// event top option for RSVP
		function eventtop_option($array){
			$array['rsvp_options'] = __('RSVP Info (Remaing Spaces & eventtop RSVP)','eventon');
			return $array;
		}
		// EventON settings menu inclusion
		function menu(){
			add_submenu_page( 'eventon', 'RSVP', __('RSVP','eventon'), 'manage_eventon', 'admin.php?page=eventon&tab=evcal_rs', '' );
		}
	// appearance
		function evoRS_appearance_settings($array){
			
			$new[] = array('id'=>'evors','type'=>'hiddensection_open','name'=>'RSVP Styles', 'display'=>'none');
			$new[] = array('id'=>'evors','type'=>'fontation','name'=>'RSVP Buttons',
				'variations'=>array(
					array('id'=>'evoRS_1', 'name'=>'Border Color','type'=>'color', 'default'=>'cdcdcd'),
					array('id'=>'evoRS_2', 'name'=>'Background Color','type'=>'color', 'default'=>'EAEAEA'),
					array('id'=>'evoRS_3', 'name'=>'Background Color (Hover)','type'=>'color', 'default'=>'ffffff')		
				)
			);
			$new[] = array('id'=>'evors','type'=>'fontation','name'=>'Count Number Ccolor',
				'variations'=>array(
					array('id'=>'evoRScc_1', 'name'=>'Font Color','type'=>'color', 'default'=>'ffffff'),
					array('id'=>'evoRScc_2', 'name'=>'Background Color','type'=>'color', 'default'=>'95D27F'),	
					array('id'=>'evoRScc_3', 'name'=>'Font Color (on EventTop)','type'=>'color', 'default'=>'8c8c8c'),
					array('id'=>'evoRScc_4', 'name'=>'Background Color (on EventTop)','type'=>'color', 'default'=>'F7EBD6'),	
					array('id'=>'evoRScc_5', 'name'=>'Border Color (on EventTop)','type'=>'color', 'default'=>'E4D6BE'),	
				)
			);
			$new[] = array('id'=>'evors','type'=>'fontation','name'=>'RSVP Form',
				'variations'=>array(
					array('id'=>'evoRS_4', 'name'=>'Background Color','type'=>'color', 'default'=>'9AB37F'),
					array('id'=>'evoRS_5', 'name'=>'Font Color','type'=>'color', 'default'=>'ffffff'),
					array('id'=>'evoRS_7', 'name'=>'Button Color','type'=>'color', 'default'=>'ffffff'),	
					array('id'=>'evoRS_8', 'name'=>'Button Text Color','type'=>'color', 'default'=>'9AB37F'),		
					array('id'=>'evoRS_8z', 'name'=>'Selected RSVP option button font color','type'=>'color', 'default'=>'9AB37F'),		
				)
			);$new[] = array('id'=>'evors','type'=>'fontation','name'=>'RSVP Form Fields',
				'variations'=>array(
					array('id'=>'evoRS_ff', 'name'=>'Font Color','type'=>'color', 'default'=>'ffffff'),
					array('id'=>'evoRS_ff2', 'name'=>'Placeholder Text Color','type'=>'color', 'default'=>'141412'),
				)
			);$new[] = array('id'=>'evors','type'=>'fontation','name'=>'Who is coming',
				'variations'=>array(
					array('id'=>'evoRS_9', 'name'=>'Background Color','type'=>'color', 'default'=>'A7A7A7'),
					array('id'=>'evoRS_10', 'name'=>'Font Color','type'=>'color', 'default'=>'ffffff'),						
				)
			);
			
			$new[] = array('id'=>'evors','type'=>'hiddensection_close',);

			return array_merge($array, $new);
		}

		function evoRS_dynamic_styles($_existen){
			$new= array(
				array(
					'item'=>'body #evcal_list .eventon_list_event .evcal_desc .evcal_desc3 .evors_eventtop_data em,body .evo_pop_body .evcal_desc .evcal_desc3 .evors_eventtop_data em',
					'multicss'=>array(
						array('css'=>'color:#$', 'var'=>'evoRScc_3',	'default'=>'8c8c8c'),
						array('css'=>'background-color:#$', 'var'=>'evoRScc_4',	'default'=>'F7EBD6'),
						array('css'=>'border-color:#$', 'var'=>'evoRScc_5',	'default'=>'E4D6BE'),
					)
				),array(
					'item'=>'.evors_whos_coming span',
					'multicss'=>array(
						array('css'=>'background-color:#$', 'var'=>'evoRS_9',	'default'=>'A7A7A7'),
						array('css'=>'color:#$', 'var'=>'evoRS_10',	'default'=>'ffffff'),						
					)
				),array(
					'item'=>'.evcal_evdata_row .evors_remaining_spots p em, .evcal_evdata_row .evors_mincap p em',
					'multicss'=>array(
						array('css'=>'background-color:#$', 'var'=>'evoRScc_2',	'default'=>'95D27F'),
						array('css'=>'color:#$', 'var'=>'evoRScc_1',	'default'=>'ffffff'),
					)
				),array(
					'item'=>'#evorsvp_form a.evcal_btn',
					'multicss'=>array(
						array('css'=>'background-color:#$', 'var'=>'evoRS_7',	'default'=>'ffffff'),
						array('css'=>'color:#$', 'var'=>'evoRS_8',	'default'=>'9AB37F'),
					)
				),array(
					'item'=>'#evorsvp_form .rsvp_status span.set',
					'css'=>'color:#$', 'var'=>'evoRS_8z',	'default'=>'9AB37F'
				),array(
					'item'=>'#evorsvp_form .form_row select, #evorsvp_form .form_row input',
					'css'=>'color:#$', 'var'=>'evoRS_ff',	'default'=>'ffffff'
				),
				array('item'=>'#evorsvp_form .form_row input::-webkit-input-placeholder','css'=>'color:#$', 'var'=>'evoRS_ff2',	'default'=>'141412'),
				array('item'=>'#evorsvp_form .form_row input:-moz-input-placeholder','css'=>'color:#$', 'var'=>'evoRS_ff2',	'default'=>'141412'),
				array('item'=>'#evorsvp_form .form_row input::-moz-input-placeholder','css'=>'color:#$', 'var'=>'evoRS_ff2',	'default'=>'141412'),
				array('item'=>'#evorsvp_form .form_row input:-ms-input-placeholder','css'=>'color:#$', 'var'=>'evoRS_ff2',	'default'=>'141412'),
				array(
					'item'=>'#evors_form_section, #evorsvp_form h3',
					'css'=>'color:#$', 'var'=>'evoRS_5',	'default'=>'ffffff'
				),array(
					'item'=>'.evors_popup:before',
					'css'=>'background-color:#$', 'var'=>'evoRS_4',	'default'=>'9AB37F'
				),array(
					'item'=>'.evoRS_status_option_selection span:hover',
					'css'=>'background-color:#$', 'var'=>'evoRS_3',	'default'=>'ffffff'
				),array(
					'item'=>'.evoRS_status_option_selection span',
					'multicss'=>array(
						array('css'=>'border-color:#$', 'var'=>'evoRS_1','default'=>'cdcdcd'),
						array('css'=>'background-color:#$', 'var'=>'evoRS_2','default'=>'EAEAEA')
					)	
				),				
			);			

			return (is_array($_existen))? array_merge($_existen, $new): $_existen;
		}
	// language settings additinos
		function evoRS_language_additions($_existen){
			$new_ar = array(
				array('type'=>'togheader','name'=>'ADDON: RSVP Events'),
				array('label'=>'Field Title','name'=>'evoRSL_001','legend'=>'','placeholder'=>'RSVP to event'),
				array('label'=>'Field Subtitle','name'=>'evoRSL_002','placeholder'=>'Make sure to RSVP to this amazing event!'),
				array('label'=>'Guests List','name'=>'evoRSL_002a'),
				array('label'=>'Attending','name'=>'evoRSL_002a1'),
				array('label'=>'Event is happening for certain','var'=>'1'),
				array('label'=>'Event is pending -count- more guests for it to happen','var'=>'1','legend'=>'Leave -count- in your translated text so it will be replaced with actual number.'),
				array('label'=>'Can not make it to this event?','name'=>'evoRSL_002a2'),
				array('label'=>'Spots remaining','name'=>'evoRSL_002b','placeholder'=>'Spots remaining'),
				array('label'=>'No more spots left!','name'=>'evoRSL_002c','placeholder'=>'No more spots left!'),
				array('label'=>'RSVPing is closed at this time.','name'=>'evoRSL_002d','placeholder'=>'RSVPing is closed at this time.'),
				array('label'=>'You have already RSVP-ed','var'=>'1'),
				array('label'=>'RSVP Status: Check-in','name'=>'evoRSL_003x',),
				array('label'=>'RSVP Status: Checked','name'=>'evoRSL_003y',),
				array('label'=>'You must login to RSVP for this event','var'=>'1',),
				array('label'=>'Login Now','var'=>'1',),
				array('label'=>'Additional Information','var'=>'1',),
				
				array('label'=>'Form RSVP Selection','type'=>'subheader'),
					array('label'=>'RSVP options: Yes','name'=>'evoRSL_003','legend'=>'',),
					array('label'=>'RSVP options: Maybe','name'=>'evoRSL_004','legend'=>'',),
					array('label'=>'RSVP options: No','name'=>'evoRSL_005','legend'=>'',),
					array('label'=>'RSVP options: Change my RSVP','name'=>'evoRSL_005a','legend'=>'',),
				array('type'=>'togend'),
				
				array('label'=>'Form Labels','type'=>'subheader'),
					array('label'=>'Form label: RSVP ID #','name'=>'evoRSL_007a',),
					array('label'=>'Form label: First Name','name'=>'evoRSL_007',),
					array('label'=>'Form label: Last Name','name'=>'evoRSL_008',),
					array('label'=>'Form label: Email Address','name'=>'evoRSL_009',),
					array('label'=>'Form label: Phone Number','name'=>'evoRSL_009a',),
					array('label'=>'Form label: How many people in your party?','name'=>'evoRSL_010',),
					array('label'=>'Form label: Additional Notes','name'=>'evoRSL_010a',),
					array('label'=>'Form label: Verify you are a human','name'=>'evoRSL_011a',),
					array('label'=>'Form label: Receive updates about event','name'=>'evoRSL_011',),
					array('label'=>'Form label: Terms & Conditions','name'=>'evoRSL_tnc',),
					array('label'=>'Form Text: We have to look up your RSVP in order to change it','name'=>'evoRSL_x1',),
				array('type'=>'togend'),

				array('label'=>'EventTop','type'=>'subheader'),
					array('label'=>'Be the first to RSVP','var'=>'1'),
					array('label'=>'One guest is attending','var'=>'1'),
					array('label'=>'Guests are attending','var'=>'1'),
				array('type'=>'togend'),
				
				array('label'=>'Form Text: **Make sure to include text part [ ] in your custom text','type'=>'subheader'),
					array('label'=>'Form Text: RSVP to',
						'name'=>'evoRSL_x2','placeholder'=>'RSVP to [event-name]'),
					array('label'=>'Form Text: Find my RSVP for',
						'name'=>'evoRSL_x3','placeholder'=>'Find my RSVP for [event-name]'),
					array('label'=>'Form Text: You have reserved',
						'name'=>'evoRSL_x6','placeholder'=>'You have reserved [spaces] space(s) for [event-name]'),

					array('label'=>'Form Text: Thank you',
						'name'=>'evoRSL_x7','placeholder'=>'Thank you'),
					array('label'=>'Form Text: We have email-ed you a confirmation to ',
						'name'=>'evoRSL_x8','placeholder'=>'We have email-ed you a confirmation to [email]'),
					
					array('label'=>'Form Text: Successfully RSVP-ed for','name'=>'evoRSL_x5','placeholder'=>'Successfully RSVP-ed for [event-name]'),
					array('label'=>'Form Text: Successfully updated RSVP for','name'=>'evoRSL_x4','placeholder'=>'Successfully updated RSVP for [event-name]'),
				array('type'=>'togend'),

				array('label'=>'Form Buttons','type'=>'subheader'),
					array('label'=>'Form Button: Submit','name'=>'evoRSL_012',),
					array('label'=>'Form Button: Change my RSVP','name'=>'evoRSL_012x',),
					array('label'=>'Form Button: Find my RSVP','name'=>'evoRSL_012y',),
				array('type'=>'togend'),

				array('type'=>'subheader','label'=>'EMAIL Body'),
					array('label'=>'RSVP Status','name'=>'evoRSLX_001','legend'=>'','placeholder'=>''),
					array('label'=>'Primary Contact on RSVP','name'=>'evoRSLX_002','legend'=>'','placeholder'=>''),
					array('label'=>'Spaces','name'=>'evoRSLX_003','legend'=>'','placeholder'=>''),
					array('label'=>'Receive Updates','name'=>'evoRSLX_003a'),
					array('label'=>'Location','name'=>'evoRSLX_003x',),
					array('label'=>'Thank you for RSVPing to our event','name'=>'evoRSLX_004','legend'=>'','placeholder'=>''),
					array('label'=>'We look forward to seeing you!','name'=>'evoRSLX_005','legend'=>'','placeholder'=>'We look forward to seeing you!'),
					array('label'=>'Contact us for quesitons and concerns.',
						'name'=>'evoRSLX_006','legend'=>'','placeholder'=>''),
					array('label'=>'New RSVP for event.',
						'name'=>'evoRSLX_007','legend'=>'','placeholder'=>''),
					array('label'=>'Event Time','name'=>'evoRSLX_008','legend'=>''),
					array('label'=>'Event Nmae','name'=>'evoRSLX_008a'),
					array('label'=>'Event Details','name'=>'evoRSLX_009'),
					array('label'=>'Capacity','name'=>'evoRSLX_email_01'),
					array('label'=>'Remaining','name'=>'evoRSLX_email_02'),
					array('label'=>'Event RSVP Stats','name'=>'evoRSLX_email_02'),
					array('label'=>'Confirmation email subheader.','name'=>'evoRSLX_009','legend'=>'','placeholder'=>'You have RSVP-ed for'),
					array('label'=>'Notification email subheader.','name'=>'evoRSLX_010','legend'=>'','placeholder'=>'New RSVP From'),
					array('label'=>'Notification email subheader (change).','name'=>'evoRSLX_010a','placeholder'=>'Update to RSVP From'),
				array('type'=>'togend'),

				array('label'=>'Form Messages','type'=>'subheader'),
					array('label'=>'Messages: Error 1','name'=>'evoRSL_013','placeholder'=>'Required fields missing'),
					array('label'=>'Messages: Error 2','name'=>'evoRSL_014','placeholder'=>'Invalid email address'),
					array('label'=>'Messages: Error 3','name'=>'evoRSL_015','placeholder'=>'Please select RSVP option'),
					array('label'=>'Messages: Error 4','name'=>'evoRSL_016','placeholder'=>'Could not update RSVP, please contact us.'),
					array('label'=>'Messages: Error 5','name'=>'evoRSL_017','placeholder'=>'Could not find RSVP, please try again.'),
					array('label'=>'Messages: Error 6','name'=>'evoRSL_017x','placeholder'=>'Invalid Captcha code.'),
					array('label'=>'Messages: Error 7','name'=>'evoRSL_017y','placeholder'=>'Could not create a RSVP please try later.'),
					array('label'=>'Messages: Error 8','name'=>'evoRSL_017z1','placeholder'=>'You can only RSVP once for this event.'),
					array('label'=>'Messages: Error 9','name'=>'evoRSL_017z2','placeholder'=>'Your party size exceed available space.'),
					array('label'=>'Messages: Error 9','name'=>'evoRSL_017z3','placeholder'=>'Your party size exceed allowed space per RSVP'),

					array('label'=>'Messages: Success 1','name'=>'evoRSL_018','placeholder'=>'Thank you for submitting your rsvp'),
					array('label'=>'Messages: Success 2','name'=>'evoRSL_019','placeholder'=>'Sorry to hear you are not going to make it to our event'),
					array('label'=>'Messages: Success 3','name'=>'evoRSL_020','placeholder'=>'Thank you for updating your rsvp'),
					array('label'=>'Messages: Success 4','name'=>'evoRSL_021','placeholder'=>'Great! we found your RSVP!'),
				array('type'=>'togend'),
				array('type'=>'togend'),
			);
			return (is_array($_existen))? array_merge($_existen, $new_ar): $_existen;
		}
	// TABS SETTINGS
		function evoRS_tab_array($evcal_tabs){
			$evcal_tabs['evcal_rs']='RSVP';		
			return $evcal_tabs;
		}
		function evoRS_tab_content(){
			global $eventon;
			$eventon->load_ajde_backender();			
		?>
			<form method="post" action=""><?php settings_fields('evoau_field_group'); 
					wp_nonce_field( AJDE_EVCAL_BASENAME, 'evcal_noncename' );?>
			<div id="evcal_csv" class="evcal_admin_meta">	
				<div class="evo_inside">
				<?php

					$site_name = get_bloginfo('name');
					$site_email = get_bloginfo('admin_email');

					$cutomization_pg_array = array(
						array(
							'id'=>'evoRS1','display'=>'show',
							'name'=>'General RSVP Settings',
							'tab_name'=>'General',
							'fields'=>array(
								array('id'=>'evors_onlylogu','type'=>'yesno','name'=>'Allow only logged-in users to submit RSVP'),
								
								array('id'=>'evors_onlylog_chg','type'=>'yesno','name'=>'Allow only logged-in users see change RSVP option','legend'=>'This will only show change RSVP options for the users that have loggedin to your site.','afterstatement'=>'evors_onlylog_chg'),
									array('id'=>'evors_onlylog_chg','type'=>'begin_afterstatement'),	
										array('id'=>'evors_change_hidden','type'=>'yesno','name'=>'Show change RSVP option only for the users who have rsvp-ed for the event'),
									array('id'=>'evors_onlylog_chg','type'=>'end_afterstatement'),

								array('id'=>'evors_prefil','type'=>'yesno','name'=>'Pre-fill fields  if user is already logged-in (eg. first name, last name, email)','legend'=>'If this option is activated, form will pre-fill fields (name & email) for logged-in users.'),
								array('id'=>'evors_prefil_block','type'=>'yesno','name'=>'Disable pre-filled fields editing','legend'=>'This will disable editing pre-filled data fields, when fields are pre-filled with loggedin user data eg. first name, last name, email.'),

								array('id'=>'evors_orderby','type'=>'dropdown','name'=>'Order Attendees by ','legend'=>'Which field to use for ordering attendees in backend and frontend. If users are not entering last name first name would be a wise option for ordering.','options'=>array('def'=>'Last Name','fn'=>'First Name')),

								array('id'=>'evors_guestlist','type'=>'dropdown','name'=>'Show guest list as ','legend'=>'Whether to show full names or initials in event card for guest list - whos coming.','options'=>array('def'=>'Initials','fn'=>'Full Name')),
								
								
								array('id'=>'evors_eventop','type'=>'subheader','name'=>'EventTop Data for RSVP.'),
								array('id'=>'evors_eventop_rsvp','type'=>'yesno','name'=>'Activate RSVPing with one-click from eventTop ONLY for logged-in users','legend'=>'This will show the normal RSVP option buttons for a logged-in user to RSVP to the event straight from the eventtop. This method will only capture user name, email and rsvp status only'),
								array('id'=>'evors_eventop_attend_count','type'=>'yesno','name'=>'Show event attending count','legend'=>'Show the attending guest count for an event on eventTOP'),
								array('id'=>'evors_eventop_remaining_count','type'=>'yesno','name'=>'Show remaining spaces count','legend'=>'Show the remaining spaces for this event on eventTOP'),

								array('id'=>'evors_eventop','type'=>'note','name'=>'NOTE: You can download all RSVPs for an event as CSV file from the event edit page under RSVP settings box.'),

						)),array(
							'id'=>'evoRS2','display'=>'',
							'name'=>'Email Templates',
							'tab_name'=>'Emails','icon'=>'envelope',
							'fields'=>array(
								array('id'=>'evors_disable_emails','type'=>'yesno','name'=>'Disable sending all emails'),
								array('id'=>'evors_notif','type'=>'yesno','name'=>'Receive email notifications upon new RSVP receipt','afterstatement'=>'evors_notif'),
								array('id'=>'evors_notif','type'=>'begin_afterstatement'),	

									array('id'=>'evcal_fcx','type'=>'note','name'=>'You can also set additional email addresses to receive notifications on each event edit page'),
									array('id'=>'evors_notfiemailfromN','type'=>'text','name'=>'"From" Name','default'=>$site_name),
									array('id'=>'evors_notfiemailfrom','type'=>'text','name'=>'"From" Email Address' ,'default'=>$site_email),
									array('id'=>'evors_notfiemailto','type'=>'text','name'=>'"To" Email Address' ,'default'=>$site_email),

									array('id'=>'evors_notfiesubjest','type'=>'text','name'=>'Email Subject line','default'=>'New RSVP Notification'),
									array('id'=>'evors_notfiesubjest_update','type'=>'text','name'=>'Email Subject line (update)','default'=>'Update RSVP Notification'),
									array('id'=>'evcal_fcx','type'=>'subheader','name'=>'HTML Template'),
									array('id'=>'evcal_fcx','type'=>'note','name'=>'To override and edit the email template copy "eventon-rsvp/templates/notification_email.php" to  "yourtheme/eventon/templates/email/notification_email.php.'),
								array('id'=>'evors_notif','type'=>'end_afterstatement'),

								array('id'=>'evors_digest','type'=>'yesno','name'=>'Receive daily digest emails for events (BETA)','afterstatement'=>'evors_digest'),
								array('id'=>'evors_digest','type'=>'begin_afterstatement'),	

									array('id'=>'evcal_fcx','type'=>'note','name'=>'NOTE: You can set which events with RSVP to receive the digest emails for, from the event edit page itself. Important: the scheduled daily email will only get sent out once someone visit your website.'),
									array('id'=>'evors_digestemail_fromN','type'=>'text','name'=>'"From" Name','default'=>$site_name),
									array('id'=>'evors_digestemail_from','type'=>'text','name'=>'"From" Email Address' ,'default'=>$site_email),
									array('id'=>'evors_digestemail_to','type'=>'text','name'=>'"To" Email Address' ,'default'=>$site_email),

									array('id'=>'evors_digestemail_subjest','type'=>'text','name'=>'Email Subject line','default'=>'Digest Email for {event-name}'),
									
									array('id'=>'evcal_fcx','type'=>'subheader','name'=>'HTML Template'),
									array('id'=>'evcal_fcx','type'=>'note','name'=>'To override and edit the email template copy "eventon-rsvp/templates/digest_email.php" to  "yourtheme/eventon/templates/email/digest_email.php.'),
								array('id'=>'evors_digest','type'=>'end_afterstatement'),


								array('id'=>'evors_notif_e','type'=>'subheader','name'=>'Send out RSVP email confirmations to attendees'),								
								array('id'=>'evors_notfiemailfromN_e','type'=>'text','name'=>'"From" Name','default'=>$site_name),
								array('id'=>'evors_notfiemailfrom_e','type'=>'text','name'=>'"From" Email Address' ,'default'=>$site_email),

								array('id'=>'evors_notfiesubjest_e','type'=>'text','name'=>'Email Subject line','default'=>'RSVP Confirmation'),
								
								array('id'=>'evors_contact_link','type'=>'text','name'=>'Contact for help link' ,'default'=>site_url(), 'legend'=>'This will be added to the bottom of RSVP confirmation email sent to attendee'),

								array('id'=>'evcal_fcx','type'=>'subheader','name'=>'HTML Template'),
								array('id'=>'evcal_fcx','type'=>'note','name'=>'To override and edit the email templates, copy default email templates from "eventon-rsvp/templates/" to  "yourtheme/eventon/templates/email/rsvp/ folder.'),
								/*array('id'=>'evors_3_000','type'=>'subheader','name'=>'Preview email templates'),
								array('id'=>'evors_3_000','type'=>'customcode','name'=>'Preview Emails','code'=>$this->__evors_settings_part_preview_email()
								),*/

						)),array(
							'id'=>'evoRS3','display'=>'',
							'name'=>'RSVP form fields',
							'tab_name'=>'RSVP Form','icon'=>'inbox',
							'fields'=>array(
								array('id'=>'evors_selection','type'=>'checkboxes','name'=>'Select RSVP status options for selection. <i>(Yes value is required)</i>', 
									'options'=>array(
										'm'=>'Maybe','n'=>'No',
								)),
								array('id'=>'evors_hide_change','type'=>'yesno','name'=>'Hide \'Change RSVP\' button','legend'=>'This will hide the change rsvp button from eventcard.'),
								array('id'=>'evors_ffields','type'=>'checkboxes','name'=>'Select RSVP form fields to show in the form. <i>(** First , Last names, and Email are required)</i>',
									'options'=>array(
										'phone'=>'Phone Number',
										'count'=>'RSVP Count (If unckecked system will count as 1 RSVP)',
										'updates'=>'Receive updates about event',
										'additional'=>'Additional notes field (visible only for NO option)',
										'captcha'=>'Verification code'
								)),	
								array('id'=>'evors_hide_change','type'=>'note','name'=>'NOTE: Additional notes field will only show when a guest select NO as RSVP status.'),
								
								array('id'=>'evors_hide_change','type'=>'subheader','name'=>'Other Form Field Options'),
								
								array('id'=>'evors_terms','type'=>'yesno','name'=>'Activate Terms & Conditions for form','afterstatement'=>'evors_terms'),
									array('id'=>'evors_terms','type'=>'begin_afterstatement'),		
									array('id'=>'evors_terms_link','type'=>'text','name'=>'Link to Terms & Conditions'),
									array('id'=>'evors_terms_text','type'=>'note','name'=>'Text Caption for Terms & Conditions can be edited from EventON > Language > EventON RSVP'),
									array('id'=>'evors_terms','type'=>'end_afterstatement'),

								array('id'=>'evors_addf1','type'=>'yesno','name'=>'Additional Field #1','afterstatement'=>'evors_addf1'),
									array('id'=>'evors_addf1','type'=>'begin_afterstatement'),								
									array('id'=>'evors_addf1_1','type'=>'text','name'=>'Field Name'),
									array('id'=>'evors_addf1_2','type'=>'dropdown','name'=>'Field Type','options'=>$this->_custom_field_types()),
									array('id'=>'evors_addf1_4','type'=>'text','name'=>'Option Values (only for Drop Down field)','default'=>'eg. cats,dogs','legend'=>'Only set these values for field type = drop down. If these values are not provided for drop down field type it will revert as text field.'),
									array('id'=>'evors_addf1_3','type'=>'yesno','name'=>'Required Field'),
									array('id'=>'evors_addf1','type'=>'end_afterstatement'),

								array('id'=>'evors_addf2','type'=>'yesno','name'=>'Additional Field #2','afterstatement'=>'evors_addf2'),
									array('id'=>'evors_addf2','type'=>'begin_afterstatement'),								
									array('id'=>'evors_addf2_1','type'=>'text','name'=>'Field Name'),
									array('id'=>'evors_addf2_2','type'=>'dropdown','name'=>'Field Type','options'=>$this->_custom_field_types()),
									array('id'=>'evors_addf2_4','type'=>'text','name'=>'Option Values (only for Drop Down field)','default'=>'eg. cats,dogs','legend'=>'Only set these values for field type = drop down. If these values are not provided for drop down field type it will revert as text field.'),
									array('id'=>'evors_addf2_3','type'=>'yesno','name'=>'Required Field'),
									array('id'=>'evors_addf2','type'=>'end_afterstatement'),

								array('id'=>'evors_addf3','type'=>'yesno','name'=>'Additional Field #3','afterstatement'=>'evors_addf3'),
									array('id'=>'evors_addf3','type'=>'begin_afterstatement'),								
									array('id'=>'evors_addf3_1','type'=>'text','name'=>'Field Name'),
									array('id'=>'evors_addf3_2','type'=>'dropdown','name'=>'Field Type','options'=> $this->_custom_field_types()),
									array('id'=>'evors_addf3_4','type'=>'text','name'=>'Option Values (only for Drop Down field)','default'=>'eg. cats,dogs','legend'=>'Only set these values for field type = drop down. If these values are not provided for drop down field type it will revert as text field.'),
									array('id'=>'evors_addf3_3','type'=>'yesno','name'=>'Required Field'),
									array('id'=>'evors_addf3','type'=>'end_afterstatement'),							
						))
					);							
					$eventon->load_ajde_backender();	
					$evcal_opt = get_option('evcal_options_evcal_rs'); 
					print_ajde_customization_form($cutomization_pg_array, $evcal_opt);	
				?>
			</div>
			</div>
			<div class='evo_diag'>
				<input type="submit" class="evo_admin_btn btn_prime" value="<?php _e('Save Changes') ?>" /><br/><br/>
				<a target='_blank' href='http://www.myeventon.com/support/'><img src='<?php echo AJDE_EVCAL_URL;?>/assets/images/myeventon_resources.png'/></a>
			</div>			
			</form>	
		<?php
		}
		function _custom_field_types(){
			return array('text'=>'Single Line Input Text Field', 'dropdown'=>'Drop Down Options', 'textarea'=>'Multiple Line Text Box','html'=>'Basic Text Line');
		}
		function __evors_settings_part_preview_email(){
			ob_start();
			echo "<a href='".get_admin_url()."admin.php?page=eventon&tab=evcal_rs&action=confirmation#evoRS2' class='evo_admin_btn btn_triad'>Confirmation Email</a> <a href='".get_admin_url()."admin.php?page=eventon&tab=evcal_rs&action=notification#evoRS2' class='evo_admin_btn btn_triad'>Notification Email</a>";
			
			if(!empty($_GET['action'])){
				echo $this->get_email_preview($_GET['action']);
			}
			return ob_get_clean();
		}
		// preview of emails that are sent out
		function get_email_preview( $type){
			global $eventon_rs;

			$email = $eventon_rs->frontend->get_email_data(array(
					'e_id'=>'934',
					'email'=>'test@msn.com',
					'rsvp_id'=>'100'
				),$type
			);		
			$email['preview']= 'yes';	
			return $eventon_rs->helper->send_email($email);
		}
	
	// duplicate event
		function duplicate_event($new_event_id, $old_event){
			global $eventon_rs;
			$eventon_rs->frontend->functions->sync_rsvp_count($new_event_id);
			delete_post_meta($new_event_id, 'ri_count_rs');// clear ri count
		}
	// trash rsvp
		public function trash_rsvp($post_id){
			if ( 'evo-rsvp' != get_post_type( $post_id ))
       			return;

       		$data = '';

       		global $eventon_rs;
       		$PMV = get_post_custom($post_id);

       		$data .= '2';

       		$event_id = !empty($PMV['e_id'])? $PMV['e_id'][0]: false;

       		$repeat_interval = !empty($PMV['repeat_interval'])? $PMV['repeat_interval'][0]:0;
       		
       		// if the userid is present for this RSVP
       		if(!empty($PMV['userid']) && !empty($PMV['e_id'])){
	       		$eventon_rs->frontend->functions->trash_user_rsvp($PMV['userid'][0], $PMV['e_id'][0], $repeat_interval);
	       	}
	       	// sync count
	       	if($event_id){
	       		//$data .= '1 '.$event_id;
	       		$eventon_rs->frontend->functions->sync_rsvp_count($event_id);
	       	}

	       	//update_post_meta(1350,'aa',$data);
		}

}

new evorsvp_admin();