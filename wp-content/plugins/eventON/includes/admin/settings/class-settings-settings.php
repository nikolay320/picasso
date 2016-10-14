<?php
/**
  * evo settings class
  * @version 2.3.23
  */
class evo_settings_settings{
	function __construct($evcal_opt)	{
		$this->evcal_opt = $evcal_opt;
	}

	function content(){

		// google maps styles description
		$gmaps_desc = '<span class="evo_gmap_styles" data-url="'.AJDE_EVCAL_URL.'/assets/images/ajde_backender/"></span>';

		return apply_filters('eventon_settings_tab1_arr_content', array(
			array(
				'id'=>'evcal_001',
				'name'=>__('General Calendar Settings','eventon'),
				'display'=>'show',
				'icon'=>'gears',
				'tab_name'=>__('General Settings','eventon'),
				'top'=>'4',
				'fields'=> apply_filters('eventon_settings_general', array(
					array('id'=>'evcal_cal_hide','type'=>'yesno','name'=>__('Hide Calendars from front-end','eventon'),),
					
					//array('id'=>'evcal_only_loggedin','type'=>'yesno','name'=>__('Show calendars only to logged-in Users','eventon'),),
					
					array('id'=>'evcal_cal_hide_past','type'=>'yesno','name'=>__('Hide past events for default calendar(s)','eventon'),'afterstatement'=>'evcal_cal_hide_past'),	
											
					array('id'=>'evcal_cal_hide_past','type'=>'begin_afterstatement'),
					array('id'=>'evcal_past_ev','type'=>'radio','name'=>__('Select a precise timing for the cut off time for past events','eventon'),'width'=>'full',
						'options'=>array(
							'local_time'=>__('Hide events past current local time','eventon'),
							'today_date'=>__('Hide events past today\'s date','eventon'))
					),
					array('id'=>'evcal_cal_hide_past','type'=>'end_afterstatement'),				
					
					array('id'=>'evo_content_filter','type'=>'dropdown','name'=>__('Select calendar event content filter type','eventon'),'legend'=>__('This will disable the use of the_content filter on event details and custom field values.','eventon'), 'options'=>array( 'evo'=>'EventON Content Filter','def'=>'Default WordPress Filter','none'=>'No Filter')),				
					//array('id'=>'evcal_dis_conFilter','type'=>'yesno','name'=>__('Disable Content Filter','eventon'),'legend'=>__('This will disable to use of the_content filter on event details and custom field values.','eventon')),				
					
					
					array('id'=>'evo_googlefonts','type'=>'yesno','name'=>__('Disable google web fonts','eventon'), 'legend'=>__('This will stop loading all google fonts used in eventon calendar.','eventon')),

					array('id'=>'evo_fontawesome','type'=>'yesno','name'=>__('Disable font awesome fonts','eventon'), 'legend'=>__('This will stop loading font awesome fonts in eventon calendar.','eventon')),
					
					array('id'=>'evo_schema','type'=>'yesno','name'=>__('Remove schema data from calendar','eventon'), 'legend'=>__('Schema microdata helps in google and other search engines find events in special event data format. With this option you can remove those microdata from showing up on front-end calendar.','eventon')),
				
					array('id'=>'evcal_css_head','type'=>'yesno','name'=>__('Write dynamic styles to header','eventon'), 'legend'=>__('If making changes to appearances dont reflect on front-end try this option. This will write those dynamic styles inline to page header','eventon')),
					
					array('id'=>'evcal_move_trash','type'=>'yesno','name'=>__('Auto move events to trash when the event date is past'), 'legend'=>__('This will move events to trash when the event end date is past current date')),

					array('id'=>'evcal_header_generator','type'=>'yesno','name'=>__('Remove eventon generator meta data from website header','eventon'), 'legend'=>__('Remove the meta data eventon place on your website header with eventon version number for debugging purposes')),

					array('id'=>'evo_donot_delete','type'=>'yesno','name'=>__('Do not delete eventon settings when I delete EventON plugin','eventon'), 'legend'=>__('Activating this will not delete the saved settings for eventon when you delete eventon plugin. By Default it will delete saved data.')),
					array('id'=>'evo_rtl','type'=>'yesno','name'=>__('Enable RTL (right-to-left all eventon calendars)','eventon'), 'legend'=>__('This will make all your eventon calendars RTL.')),

					
					//array('id'=>'evo_wpml','type'=>'yesno','name'=>'Activate WPML compatibility', 'legend'=>'This will activate WPML compatibility features.'),

					array('id'=>'evcal_header_format','type'=>'text','name'=>__('Calendar Header month/year format. <i>(<b>Allowed values:</b> m = month name, Y = 4 digit year, y = 2 digit year)</i>','eventon') , 'default'=>'m, Y'),
										
					array('id'=>'evcal_additional','type'=>'subheader','name'=>__('Additional EventON Settings' ,'eventon')),

					array('id'=>'evcal_export','type'=>'customcode','code'=>$this->export()),

					array('id'=>'evcal_additional','type'=>'note','name'=>sprintf(__('Looking for additional functionality including event tickets, frontend event submissions, RSVP to events, photo gallery and more? <br/><a href="%s" target="_blank">Check out eventON addons</a>.' ,'eventon'), 'http://www.myeventon.com/addons/')),
			))),
			array(
				'id'=>'evcal_005',
				'name'=>__('Google Maps API Settings','eventon'),
				'tab_name'=>__('Google Maps API','eventon'),
				'icon'=>'map-marker',
				'fields'=>array(
					array('id'=>'evcal_cal_gmap_api','type'=>'yesno','name'=>__('Disable Google Maps API','eventon'),'legend'=>'This will stop gmaps API from loading on frontend and will stop google maps from generating on event locations.','afterstatement'=>'evcal_cal_gmap_api'),
					array('id'=>'evcal_cal_gmap_api','type'=>'begin_afterstatement'),
					array('id'=>'evcal_gmap_disable_section','type'=>'radio','name'=>__('Select which part of Google gmaps API to disable','eventon'),'width'=>'full',
						'options'=>array(
							'complete'=>__('Completely disable google maps','eventon'),
							'gmaps_js'=>__('Google maps javascript file only (If the js file is already loaded with another gmaps program)','eventon'))
					),
					array('id'=>'evcal_cal_gmap_api','type'=>'end_afterstatement'),
					
					array('id'=>'evo_gmap_api_key','type'=>'text','name'=>__('Google maps API Key (Not required)','eventon').' <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">How to get API Key</a>','legend'=>'Not required with Gmap API V3, but typing a google maps API key will append the key and will enable monitoring map loading activity from google.','afterstatement'=>'evcal_cal_gmap_api'),
					array('id'=>'evcal_gmap_scroll','type'=>'yesno','name'=>__('Disable scrollwheel zooming on Google Maps','eventon'),'legend'=>'This will stop google maps zooming when mousewheel scrolled.'),
					
					array('id'=>'evcal_gmap_format', 'type'=>'dropdown','name'=>__('Google maps display type:','eventon'),
						'options'=>array(
							'roadmap'=>__('ROADMAP Displays the normal default 2D','eventon'),
							'satellite'=>__('SATELLITE Displays photographic tiles','eventon'),
							'hybrid'=>__('HYBRID Displays a mix of photographic tiles and a tile layer','eventon'),
							'terrain'=>__('TERRAIN Displays a physical map based on terrain information','eventon'),
						)),
					array('id'=>'evcal_gmap_zoomlevel', 'type'=>'dropdown','name'=>__('Google starting zoom level:','eventon'),
						'desc'=>__('18 = zoomed in (See few roads), 7 = zoomed out. (See most of the country)','eventon'),
						'options'=>array(
							'18'=>'18',
							'16'=>'16',
							'14'=>'14',
							'12'=>'12',
							'10'=>'10',
							'8'=>'8',
							'7'=>'7',
						)),
					array('id'=>'evcal_gmap_style', 'type'=>'dropdown','name'=>__('Map Style','eventon'),
						'desc'=>$gmaps_desc,
						'options'=>array(
							'default'=>__('Default','eventon'),
							'apple'=>'Apple Maps-esque',
							'avacado'=>'Avacado World',
							'bentley'=>'Bentley',
							'blueessence'=>'Blue Essence',
							'bluewater'=>'Blue Water',
							'hotpink'=>'Hot Pink',
							'muted'=>'Muted Monotone',
							'richblack'=>'Rich Black',
							'redalert'=>'Red Alert',
							'retro'=>'Retro',
							'shift'=>'Shift Worker',
						)),
					array('id'=>'evo_gmap_iconurl','type'=>'text','name'=>__('Custom map marker icon complete http url','eventon'),'legend'=>'Type a complete http:// url for a PNG image that can be used instead of the default red google map markers.','default'=>'eg. http://www.site.com/image.png'),
			)),

			array(
				'id'=>'evcal_001b',
				'name'=>__('Time & Date Related Settings','eventon'),
				'icon'=>'clock-o',
				'tab_name'=>__('Time Settings','eventon'),
				'fields'=> apply_filters('eventon_settings_general', array(	
					array('id'=>'evcal_sh001','type'=>'subheader','name'=>__('Front-end Time/Date Settings','eventon')),
					array('id'=>'evo_usewpdateformat','type'=>'yesno','name'=>__('Use WP default Date format in eventON calendar','eventon'), 'legend'=>__('Select this option to use the default WP Date format through out eventON calendar. Default format: yyyy/mm/dd','eventon')),
										
					array('id'=>'evo_timeF','type'=>'yesno','name'=>__('Allow universal event time format on eventCard','eventon'),'legend'=>'This will change the time format on eventCard to be a universal set format regardless of the month events span for.','afterstatement'=>'evo_timeF'),
						array('id'=>'evo_timeF','type'=>'begin_afterstatement'),
						array('id'=>'evo_timeF_v','type'=>'text','name'=>__('Time Format','eventon'), 'default'=>'F j(l) g:ia'),
						array('id'=>'evcal_api_mu_note','type'=>'note',
							'name'=>'Acceptable date/time values: php <a href="http://php.net/manual/en/function.date.php" target="_blank">date()</a> '),
						array('id'=>'evo_timeF','type'=>'end_afterstatement'),

					array('id'=>'evcal_sh001','type'=>'subheader','name'=>__('Back-end Time/Date Settings','eventon')),
					array('id'=>'evo_minute_increment','type'=>'dropdown','name'=>__('Select minute increment for time select in event edit page','eventon'),'width'=>'full',
						'options'=>array(
							'60'=>'1','12'=>'5','6'=>'10','4'=>'15','2'=>'30'
						)
					),
			))),
			array(
				'id'=>'evcal_001a',
				'name'=>__('Calendar front-end Sorting and filtering options','eventon'),
				'tab_name'=>__('Sorting and Filtering','eventon'),
				'icon'=>'filter',
				'fields'=>array(
					array('id'=>'evcal_hide_sort','type'=>'yesno','name'=>__('Hide Sort/Filter Bar on Calendar','eventon')),
					array('id'=>'evcal_hide_filter_icons','type'=>'yesno','name'=>__('Hide Filter Dropdown Selection Item Icons','eventon')),
					array('id'=>'evcal_sort_options', 'type'=>'checkboxes','name'=>__('Event sorting options to show on Calendar <i>(Note: Event Date is default sorting method.)</i>','eventon'),
						'options'=>array(
							'title'=>__('Event Main Title','eventon'),
							'color'=>__('Event Color','eventon'),
							'posted'=>__('Event Posted Date','eventon'),
						)),
					array('id'=>'evcal_filter_options', 'type'=>'checkboxes','name'=>__('Event filtering options to show on the calendar</i>','eventon'),
						'options'=>$this->event_type_options()
					),
			)),
			array(
				'id'=>'evcal_002',
				'name'=>__('General Frontend Calendar Appearance','eventon'),
				'tab_name'=>__('Appearance','eventon'),
				'icon'=>'eye',
				'fields'=>$this->appearance()
			),
			array(
				'id'=>'evcal_004',
				'name'=>__('Custom Icons for Calendar','eventon'),
				'tab_name'=>__('Icons','eventon'),
				'icon'=>'diamond',
				'fields'=> apply_filters('eventon_custom_icons', array(
					array('id'=>'fs_fonti2','type'=>'fontation','name'=>__('EventCard Icons','eventon'),
						'variations'=>array(
							array('id'=>'evcal__ecI', 'type'=>'color', 'default'=>'6B6B6B'),
							array('id'=>'evcal__ecIz', 'type'=>'font_size', 'default'=>'18px'),
						)
					),
					
					array('id'=>'evcal__fai_001','type'=>'icon','name'=>__('Event Details Icon','eventon'),'default'=>'fa-align-justify'),
					array('id'=>'evcal__fai_002','type'=>'icon','name'=>__('Event Time Icon','eventon'),'default'=>'fa-clock-o'),
					array('id'=>'evcal__fai_repeats','type'=>'icon','name'=>__('Event Repeat Icon','eventon'),'default'=>'fa-repeat'),
					array('id'=>'evcal__fai_003','type'=>'icon','name'=>__('Event Location Icon','eventon'),'default'=>'fa-map-marker'),
					array('id'=>'evcal__fai_004','type'=>'icon','name'=>__('Event Organizer Icon','eventon'),'default'=>'fa-headphones'),
					array('id'=>'evcal__fai_005','type'=>'icon','name'=>__('Event Capacity Icon','eventon'),'default'=>'fa-tachometer'),
					array('id'=>'evcal__fai_006','type'=>'icon','name'=>__('Event Learn More Icon','eventon'),'default'=>'fa-link'),
					array('id'=>'evcal__fai_007','type'=>'icon','name'=>__('Event Ticket Icon','eventon'),'default'=>'fa-ticket'),
					array('id'=>'evcal__fai_008','type'=>'icon','name'=>__('Add to your calendar Icon','eventon'),'default'=>'fa-calendar-o'),
					array('id'=>'evcal__fai_008a','type'=>'icon','name'=>__('Get Directions Icon','eventon'),'default'=>'fa-road'),
				))
			)
			// event top
			,array(
				'id'=>'evcal_004aa',
				'name'=>__('EventTop Settings (EventTop is an event row on calendar)','eventon'),
				'tab_name'=>__('EventTop','eventon'),
				'icon'=>'columns',
				'fields'=>array(
					array('id'=>'evcal_top_fields', 'type'=>'checkboxes','name'=>__('Additional data fields for eventTop: <i>(NOTE: <b>Event Name</b> and <b>Event Date</b> are default fields)</i>','eventon'),
							'options'=> apply_filters('eventon_eventop_fields', $this->eventtop_settings()),
					),
					array('id'=>'evo_widget_eventtop','type'=>'yesno','name'=>__('Display all these fields in widget as well','eventon'),'legend'=>__('By default only few of the data is shown in eventtop in order to make that calendar look nice on a widget where space is limited.','eventon')),
					
					array('id'=>'evo_eventtop_customfield_icons','type'=>'yesno','name'=>__('Show event custom meta data icons on eventtop','eventon'),'legend'=>__('This will show event custom meta data icons next to custom data fields on eventtop, if those custom data fields are set to show on eventtop above and if they have data and icons set.','eventon')),

					array('id'=>'evcal_eventtop','type'=>'note','name'=>__('NOTE: Lot of these fields are NOT available in Tile layout. Reason: we dont want to potentially break the tile layout and over-crowd the clean design aspect of tile boxes.','eventon')),

					array('id'=>'evo_showeditevent','type'=>'yesno','name'=>__('Show edit event button for each event','eventon'),'legend'=>'This will show an edit event button on eventTop - only for admin - that will open in a new window edit event page. Works only for lightbox and slideDown interaction methods.'),
				)
			)
			// event card
			,array(
				'id'=>'evcal_004a',
				'name'=>__('EventCard Settings (EventCard is the full event details card)','eventon'),
				'tab_name'=>__('EventCard','eventon'),
				'icon'=>'list-alt',
				'fields'=>array(
								

					array('id'=>'evcal_sh001','type'=>'subheader','name'=>__('Featured Image','eventon')),
						array('id'=>'evo_ftimg_height_sty','type'=>'dropdown','name'=>__('Feature image display style','eventon'), 'legend'=>'Select which display style you want to show the featured image on event card when event first load',
							'options'=> array(
								'direct'=>__('Direct Image','eventon'),
								'minmized'=>__('Minimized height','eventon'),
								'100per'=>__('100% Image height with stretch to fit','eventon'),
								'full'=>__('100% Image height with propotionate to calendar width','eventon')
						)),
						array('id'=>'evo_ftimghover','type'=>'note','name'=>__('Featured image display styles: Direct image style will show &lt;img/&gt; image as oppose to the image as background image of a &lt;div/&gt;','eventon')),
						array('id'=>'evo_ftimghover','type'=>'yesno','name'=>__('Disable hover effect on featured image','eventon'),'legend'=>'Remove the hover moving animation effect from featured image on event. Hover effect is not available on Direct Image style'),
						array('id'=>'evo_ftimgclick','type'=>'yesno','name'=>__('Disable zoom effect on click','eventon'),'legend'=>'Remove the moving animation effect from featured image on click event. Zoom effect is not available in Direct Image style'),

						array('id'=>'evo_ftimgheight','type'=>'text','name'=>__('Minimal height for featured image (value in pixels)','eventon'), 'default'=>'eg. 400'),
						array('id'=>'evo_ftim_mag','type'=>'yesno','name'=>__('Show magnifying glass over featured image','eventon'),'legend'=>'This will convert the mouse cursor to a magnifying glass when hover over featured image. <br/><br/><img src="'.AJDE_EVCAL_URL.'/assets/images/admin/cursor_mag.jpg"/><br/>This is not available for Direct Image style'),
						array('id'=>'evcal_default_event_image_set','type'=>'yesno','name'=>__('Set default event image for events that doesnt have images','eventon'),'legend'=>__('Add a URL for the default event image URL that will be used on events that dont have featured images set.','eventon'),'afterstatement'=>'evcal_default_event_image_set'),
							array('id'=>'evcal_default_event_image_set','type'=>'begin_afterstatement'),
							array('id'=>'evcal_default_event_image','type'=>'text','name'=>__('Default event image URL','eventon') , 'default'=>'http://www.google.com/image.jpg'),
							array('id'=>'evcal_default_event_image_set','type'=>'end_afterstatement'),

					array('id'=>'evcal_sh001','type'=>'subheader','name'=>__('Location Image','eventon')),
					array('id'=>'evo_locimgheight','type'=>'text','name'=>__('Set event location image height (value in pixels)','eventon'), 'default'=>'eg. 400'),

					// Add to Calendar section
					array('id'=>'evcal_sh001','type'=>'subheader','name'=>__('Add to Calendar Options','eventon')),
						array('id'=>'evo_addtocal','type'=>'dropdown','name'=>__('Select which options to show for add to your calendar','eventon'),'legend'=>'Learn More & Add to your calendar field must be selected for these options to reflect on eventCard','options'=>array(
								'all'=>'All options',
								'gcal'=>'Only Google Add to Calendar',
								'ics'=>'Only ICS download event',
								'none'=>'Do not show any add to calendar options',
							)
						),

					// Other EventCard Settings
					array('id'=>'evcal_sh001','type'=>'subheader','name'=>__('Other EventCard Settings','eventon')),
																
						array('id'=>'evo_morelass','type'=>'yesno','name'=>__('Show full event description','eventon'),'legend'=>'If you select this option, you will not see More/less button on EventCard event description.'),
						
						array('id'=>'evo_opencard','type'=>'yesno','name'=>__('Open all eventCards by default','eventon'),'legend'=>'This option will load the calendar with all the eventCards open by default and will not need to be clicked to slide down and see details.'),


					array('id'=>'evo_EVC_arrange',
						'type'=>'rearrange',
						'fields_array'=>$this->rearrange_code(),
						'order_var'=> 'evoCard_order',
						'selected_var'=> 'evoCard_hide',
						'title'=>__('Order of EventCard Data Boxes','eventon'),
						'notes'=>__('Fields selected below will show in eventcard, and can be moved around to your desired order.','eventon')
					),

				)
			),array(
				'id'=>'evcal_003',
				'name'=>__('Third Party API Support for Event Calendar','eventon'),
				'tab_name'=>__('Third Party APIs','eventon'),
				'icon'=>'plug',
				'fields'=>array(
					// paypal
					array('id'=>'evcal_s','type'=>'subheader','name'=>__('Paypal','eventon')),
					array('id'=>'evcal_paypal_pay','type'=>'yesno','name'=>__('Enable PayPal event ticket payments','eventon'),'afterstatement'=>'evcal_paypal_pay', 'legend'=>'This will allow you to add a paypal direct link to each event that will allow visitors to pay for event via paypal.'),
					array('id'=>'evcal_paypal_pay','type'=>'begin_afterstatement'),
					array('id'=>'evcal_pp_email','type'=>'text','name'=>__('Your paypal email address to receive payments','eventon')),				
					array('id'=>'evcal_pp_cur','type'=>'dropdown','name'=>__('Select your currency','eventon'), 'options'=>evo_get_currency_codes() ),				
					array('id'=>'evcal_paypal_pay','type'=>'end_afterstatement'),
				)
			)
			// custom meta fields
			,array(
				'id'=>'evcal_009',
				'name'=>__('Custom Meta Data fields for events','eventon'),
				'tab_name'=>__('Custom Meta Data','eventon'),
				'icon'=>'list-ul',
				'fields'=>$this->custom_meta_fields()
			)
			// event categories
			,array(
				'id'=>'evcal_010',
				'name'=>__('EventType Categories','eventon'),
				'tab_name'=>__('Categories','eventon'),
				'icon'=>'sitemap',
				'fields'=>array(			

					array('id'=>'evcal_fcx','type'=>'note','name'=>__('Use this to assign custom names for the event type taxonomies which you can use to categorize events. Note: Once you update these custom taxonomies refresh the page for the values to show up.','eventon')),
					array('id'=>'evcal_eventt','type'=>'text','name'=>__('Custom name for Event Type Category #1','eventon')),
					array('id'=>'evcal_eventt2','type'=>'text','name'=>__('Custom name for Event Type Category #2','eventon')),


					array('id'=>'evcal_fcx','type'=>'note','name'=>__('In order to add additional event type categories make sure you activate them in order. eg. Activate #4 after you activate #3','eventon')),
					array('id'=>'evcal_ett_3','type'=>'yesno','name'=>__('Activate Event Type Category #3','eventon'),'legend'=>'This will activate additional event type category.','afterstatement'=>'evcal_ett_3'),
					array('id'=>'evcal_ett_3','type'=>'begin_afterstatement'),
						array('id'=>'evcal_eventt3','type'=>'text','name'=>__('Category Type Name','eventon')),
					array('id'=>'evcal_ett_3','type'=>'end_afterstatement'),

					array('id'=>'evcal_ett_4','type'=>'yesno','name'=>__('Activate Event Type Category #4','eventon'),'legend'=>'This will activate additional event type category.','afterstatement'=>'evcal_ett_4'),
					array('id'=>'evcal_ett_4','type'=>'begin_afterstatement'),
						array('id'=>'evcal_eventt4','type'=>'text','name'=>__('Category Type Name','eventon')),
					array('id'=>'evcal_ett_4','type'=>'end_afterstatement'),

					array('id'=>'evcal_ett_5','type'=>'yesno','name'=>__('Activate Event Type Category #5','eventon'),'legend'=>'This will activate additional event type category.','afterstatement'=>'evcal_ett_5'),
					array('id'=>'evcal_ett_5','type'=>'begin_afterstatement'),
						array('id'=>'evcal_eventt5','type'=>'text','name'=>__('Category Type Name','eventon')),
					array('id'=>'evcal_ett_5','type'=>'end_afterstatement'),
				)
			)
			// events paging
			,array(
				'id'=>'evcal_011',
				'name'=>__('Events Paging','eventon'),
				'tab_name'=>__('Events Paging','eventon'),
				'icon'=>'files-o',
				'fields'=>array(			
					array('id'=>'evcal__note','type'=>'note','name'=>__('This page will allow you to control templates and permalinks related to eventon event pages.','eventon')),
					
					array('id'=>'evo_event_archive_page_id','type'=>'dropdown','name'=>__('Select Events Page','eventon'), 'options'=>$this->event_pages(), 'desc'=>__('This will allow you to use this page with url slug /events/ as event archive page. Be sure to insert eventon shortcode in this page.','eventon')),
					array('id'=>'evo_event_archive_page_template','type'=>'dropdown','name'=>__('Select Events Page Template','eventon'), 'options'=>$this->theme_templates()),
					
					array('id'=>'evo_event_slug','type'=>'text','name'=>__('EventOn Event Post Slug','eventon'), 'default'=>'events'),
					array('id'=>'evcal__note','type'=>'note','name'=>__('NOTE: If you change the slug for events please be sure to refresh permalinks for the new single event pages to work properly..','eventon')),
					array('id'=>'evcal__note','type'=>'note','name'=>__('PROTIP: If the /events page does not work due to theme/plugin conflicts, create a new page, call it <b>"Events Directory"</b> Insert eventon shortcode and use that as your main events page which will have a URL ending like /events-directory. This would be a perfect solution if you have conflicts with /events slug.','eventon')),
				)
			),array(
				'id'=>'evcal_012',
				'name'=>__('Shortcode Settings','eventon'),
				'tab_name'=>__('ShortCodes','eventon'),
				'icon'=>'code',
				'fields'=>array(			
					array('id'=>'evcal__note','type'=>'customcode','code'=>$this->content_shortcodes()),
				)
			)
			)
		);	
	}

	// HTML code for export events in csv and ics format
		function export(){
			global $ajde;

			$nonce = wp_create_nonce('eventon_download_events');
			
			// CSV format
			$exportURL = add_query_arg(array(
			    'action' => 'eventon_export_events',
			    'nonce'=>$nonce
			), admin_url('admin-ajax.php'));

			// ICS format
			$exportICS_URL = add_query_arg(array(
			    'action' => 'eventon_export_events_ics',
			    'nonce'=>$nonce
			), admin_url('admin-ajax.php'));

			ob_start(); ?>
			<p><a href="<?php admin_url();?>options-permalink.php" class="evo_admin_btn btn_secondary"><?php _e('Reset Permalinks','eventon');?></a></p>
			
			<p><?php _e('Download all eventON events.','eventon');?></p>
			<p><a class='evo_admin_btn btn_triad' href="<?php echo $exportURL;?>"><?php _e('CSV Format','eventon');?></a>  <a class='evo_admin_btn btn_triad' href="<?php echo $exportICS_URL;?>"><?php _e('ICS format','eventon');?></a></p>
			<?php 
			return  ob_get_clean();
		}

		function eventtop_settings(){
			global $eventon;

			$num = evo_calculate_cmd_count($this->evcal_opt[1]);
			$_add_tax_count = evo_get_ett_count($this->evcal_opt[1]);
			$_tax_names_array = evo_get_ettNames($this->evcal_opt[1]);
			
			$arr = array(
				'time'=>__('Event Time (to and from)','eventon'),
				'location'=>__('Event Location Address','eventon'),
				'locationame'=>__('Event Location Name','eventon'),				
			);

			// additional taxonomies
			for($n=1; $n<= $_add_tax_count; $n++){
				$__tax_fields = 'eventtype'.($n==1?'':$n);
				$__tax_name = $_tax_names_array[$n];
				$arr[$__tax_fields]=__($__tax_name.' (Category #'.$n.')','eventon');
			}


			$arr['tags']=__('Event Tags','eventon');
			$arr['dayname']=__('Event Day Name (Only for one day events)','eventon');
			$arr['organizer']=__('Event Organizer','eventon');

			// add custom fields
			for($x=1; $x < ($num+1); $x++){
				if(!empty($this->evcal_opt[1]['evcal_af_'.$x])  && $this->evcal_opt[1]['evcal_af_'.$x]=='yes' && !empty($this->evcal_opt[1]['evcal_ec_f'.$x.'a1']) ){
					$arr['cmd'.$x] = $this->evcal_opt[1]['evcal_ec_f'.$x.'a1'];					
				}else{ break;}
			}

			return $arr;
		}
		function event_type_options(){
			$event_type_names = evo_get_ettNames($this->evcal_opt[1]);
			// event types category names		
			$ett_verify = evo_get_ett_count($this->evcal_opt[1] );
			
			for($x=1; $x< ($ett_verify+1); $x++){
				$ab = ($x==1)? '':'_'.$x;
				$event_type_options['event_type'.$ab] = $event_type_names[$x];
			}

			$event_type_options['event_location'] = 'Event Location';
			$event_type_options['event_organizer'] = 'Event Organizer';
			
			return $event_type_options;
		}

	// rearrange fields
		function rearrange_code(){	
			$rearrange_items = apply_filters('eventon_eventcard_boxes',array(
				'ftimage'=>array('ftimage',__('Featured Image','eventon')),
				'eventdetails'=>array('eventdetails',__('Event Details','eventon')),
				'timelocation'=>array('timelocation',__('Time and Location','eventon')),
				'repeats'=>array('repeats',__('Event Repeats Info','eventon')),
				'organizer'=>array('organizer',__('Event Organizer','eventon')),
				'locImg'=>array('locImg',__('Location Image','eventon')),
				'gmap'=>array('gmap',__('Google Maps','eventon')),
				'learnmoreICS'=>array('learnmoreICS',__('Learn More & Add to your calendar','eventon')),
			));

			// otehr values
				//get directions
				$rearrange_items['getdirection']=array('getdirection',__('Get Directions','eventon'));
				
				//eventbrite
				if(!empty($this->evcal_opt[1]['evcal_evb_events']) && $this->evcal_opt[1]['evcal_evb_events']=='yes')
					$rearrange_items['eventbrite']= array('eventbrite',__('eventbrite','eventon'));
					
				//paypal
				if(!empty($this->evcal_opt[1]['evcal_paypal_pay']) && $this->evcal_opt[1]['evcal_paypal_pay']=='yes')
					$rearrange_items['paypal']= array('paypal',__('Paypal','eventon'));
				
				// custom fields
				$_cmd_num = evo_calculate_cmd_count($this->evcal_opt[1]);
				for($x=1; $x<=$_cmd_num; $x++){
					if( !empty($this->evcal_opt[1]['evcal_ec_f'.$x.'a1']) && !empty($this->evcal_opt[1]['evcal_af_'.$x]) && $this->evcal_opt[1]['evcal_af_'.$x]=='yes')
						$rearrange_items['customfield'.$x] = array('customfield'.$x,$this->evcal_opt[1]['evcal_ec_f'.$x.'a1'] );
				}
			
			return $rearrange_items;
		}
		function custom_meta_fields(){
			// reused array parts
			$__additions_009_1 = apply_filters('eventon_cmd_field_types', array('text'=>__('Single line Text','eventon'),'textarea'=>__('Multiple lines of text','eventon'), 'button'=>__('Button','eventon')) );
			// additional custom data fields
			for($cm=1; $cm<evo_max_cmd_count(); $cm++){
				$__additions_009_a[$cm]= $cm;
			}

			// fields for each custom field
			$cmf_count = !empty($this->evcal_opt[1]['evcal_cmf_count'])? $this->evcal_opt[1]['evcal_cmf_count']: 3;
			$cmf_addition_x= array(array('id'=>'evcal__note','type'=>'note','name'=>__('<b>NOTE: </b>Once new data field is activated go to <b>myEventon> Settings> EventCard</b> and rearrange the order of this new field and save changes for it to show on front-end. <br/>
				If you change field name for custom fields make sure it is updated in <b>myEventon > Language</b> as well.<br/>(* Required values)','eventon')),
					array('id'=>'evcal_cmf_count','type'=>'dropdown','name'=>__('Number of Additional Custom Data Fields','eventon'), 'options'=>$__additions_009_a, 'default'=>3),);

			for($cmf=0; $cmf< $cmf_count; $cmf++){
				$num = $cmf+1;

				$cmf_addition = array( 
					array('id'=>'evcal_af_'.$num,'type'=>'yesno','name'=>__('Activate Additional Field #'.$num,'eventon'),'legend'=>'This will activate additional event meta field.','afterstatement'=>'evcal_af_'.$num.''),
					array('id'=>'evcal_af_'.$num,'type'=>'begin_afterstatement'),
					array('id'=>'evcal_ec_f'.$num.'a1','type'=>'text','name'=>__('Field Name*','eventon')),
					array('id'=>'evcal_ec_f'.$num.'a2','type'=>'dropdown','name'=>__('Content Type','eventon'), 'options'=>$__additions_009_1),
					array('id'=>'evcal__fai_00c'.$num.'','type'=>'icon','name'=>__('Icon','eventon'),'default'=>'fa-asterisk'),
					array('id'=>'evcal_ec_f'.$num.'a3','type'=>'yesno','name'=>__('Hide this field from front-end calendar','eventon')),
					array('id'=>'evcal_ec_f'.$num.'a4','type'=>'dropdown','name'=>__('Visibility Type','eventon'), 
						'options'=>array('all'=>'Everyone','admin'=>'Admin Only','loggedin'=>'Logged-in Users Only')
						),
					array('id'=>'evcal_af_'.$num,'type'=>'end_afterstatement')
				);

				$cmf_addition_x = array_merge($cmf_addition_x, $cmf_addition);
			}
			return $cmf_addition_x;
		}

	/**
	 * theme pages and templates
	 * @return  
	 */
		function event_pages(){
			$pages = new WP_Query(array('post_type'=>'page','posts_per_page'=>-1));
			$_page_ar[]	='--';
			while($pages->have_posts()	){ $pages->the_post();								
				$page_id = get_the_ID();
				$_page_ar[$page_id] = get_the_title($page_id);
			}
			wp_reset_postdata();
			return $_page_ar;
		}
		function theme_templates(){
			// get all available templates for the theme
			$templates = get_page_templates();
			$_templates_ar['archive-ajde_events.php'] = 'Default Eventon Template';
			$_templates_ar['page.php'] = 'Default Page Template';
		   	foreach ( $templates as $template_name => $template_filename ) {
		       $_templates_ar[$template_filename] = $template_name;
		   	}
		   	return $_templates_ar;
		}

	function content_shortcodes(){
		global $eventon;

		ob_start();
		?>
			<p><?php _e('Use the "Generate shortcode" button to open lightbox shortcode generator to create your desired calendar shortcode.','eventon');?></p><br/>
			
			<a id="evo_shortcode_btn" class="ajde_popup_trig evo_admin_btn btn_prime" title="eventON Shortcode generator" data-popc='eventon_shortcode' href="#" data-textbox='evo_set_shortcodes'>[ ] <?php _e('Generate shortcode','eventon');?></a><br/>
			<p id='evo_set_shortcodes'></p>

			<p style='padding-top:10px'><b><?php _e('Other common shortcodes','eventon');?></b></p>
			<p><?php _e('[add_eventon] -- Default month calendar','eventon');?></p>
			<p><?php _e('[add_eventon_list number_of_months="5" hide_empty_months="yes" ] -- 5 months events list with empty months hidden from view','eventon');?></p>
			<p><?php _e('[add_eventon_list number_of_months="5" month_incre="-5" ] -- Show list of 5 past months','eventon');?></p>

		<?php

		// throw shortcode popup codes
		$eventon->evo_admin->eventon_shortcode_pop_content();

		return ob_get_clean();
		
	}

		function appearance_theme_selector(){
			
			ob_start();

				echo  '<h4 class="acus_header">'.__('Calendar Themes','eventon').'</h4>
				<input id="evo_cal_theme" name="evo_cal_theme" value="'.( (!empty($this->evcal_opt[1]['evo_cal_theme']))? $this->evcal_opt[1]['evo_cal_theme']:null).'" type="hidden"/>
				<div id="evo_theme_selection">';

				// scan for themes
				$dir = AJDE_EVCAL_PATH.'/themes/';				
				$a = scandir($dir);
				
				$themes =$the = '';
				foreach($a as $file){
					if($file!= '.' && $file!= '..'){
						$base = basename($file,'.php');
						$themes[$base] = $file;
						if(file_exists($dir.$file)){
							include_once($dir.$file);
							$the[] = array('name'=>$base, 'content'=>$theme);
						}
					}
				}


					echo "<p id='evo_themejson' style='display:none'>".json_encode($the)."</p>";
					$evo_theme_current =  !empty($this->evcal_opt[1]['evo_theme_current'])? $this->evcal_opt[1]['evo_theme_current']: 'default';

				?>
					<p class='evo_theme_selection'><?php _e('Current Theme:','eventon');?> <b><select name='evo_theme_current'>
						<option value='default'><?php _e('Default','eventon');?></option>
						<?php
							if(!empty($themes)){
								foreach($themes as $base=>$theme){
									echo "<option value='{$base}' ". ($base==$evo_theme_current? "selected='selected'":null).">".$base.'</option>';
								}
							}
						?>
					</select></b>
						<span class='evo_theme'>
							<span name='evcal__fc2' style='background-color:#<?php echo $this->colr('evcal__fc2','ABABAB' );?>' data-default='ABABAB'></span>
							<span name='evcal_header1_fc' style='background-color:#<?php echo $this->colr('evcal_header1_fc','C6C6C6' );?>' data-default='C6C6C6'></span>
							<span name='evcal__bgc4' style='background-color:#<?php echo $this->colr('evcal__bgc4','fafafa' );?>' data-default='fafafa'></span>
							<span name='evcal__fc3' style='background-color:#<?php echo $this->colr('evcal__fc3','6B6B6B' );?>' data-default='6B6B6B'></span>
							<span name='evcal__jm010' style='background-color:#<?php echo $this->colr('evcal__jm010','e2e2e2' );?>' data-default='e2e2e2'></span>
						</span>
					</p>
					
					<p style='clear:both'><i><?php _e('Themes are in <strong>Beta stage</strong> and we are working on it & addin more themes.','eventon');?></i></p>
					<p style='clear:both'><i><strong><?php _e('NOTE:','eventon');?></strong> <?php _e('After changing theme make sure to click "save changed"','eventon');?></i></p>
		
				<?php

				echo '</div>';

			return ob_get_clean();
		}
		private function colr($var, $def){
			return (!empty($this->evcal_opt[1][$var]))? $this->evcal_opt[1][$var]: $def;
		}
	/**
	 * appearance array section of the settings
	 * @return  
	 */
	public function appearance(){
		return apply_filters('eventon_appearance_add', 
			array(
				array('id'=>'evo_notice_1','type'=>'notice','name'=>sprintf(__('Once you make changes to appearance make sure to clear browser and website cache to see results. <br/>Can not find appearance? <a href="%s" target="_blank">See how you can add custom styles to change additional appearances</a>','eventon'),'http://www.myeventon.com/documentation/change-css-calendar/') )
				
				,array('id'=>'evoapp_code_1', 'type'=>'customcode','code'=>$this->appearance_theme_selector(), )
				,array('id'=>'fc_mcolor','type'=>'multicolor','name'=>__('Multiple colors','eventon'),
					'variations'=>array(
						array('id'=>'evcal_hexcode', 'default'=>'206177', 'name'=>__('Primary Calendar Color','eventon')),
						array('id'=>'evcal_header1_fc', 'default'=>'C6C6C6', 'name'=>'Header Month/Year text color'),
						array('id'=>'evcal__fc2', 'default'=>'ABABAB', 'name'=>'Calendar Date color'),
					)
				),
				array('id'=>'evcal_font_fam','type'=>'text','name'=>__('Primary Calendar Font family <i>(Note: type the name of the font that is supported in your website. eg. Arial)</i>','eventon')),
				array('id'=>'evcal_arrow_hide','type'=>'yesno','name'=>__('Hide month navigation arrows','eventon'), 'legend'=>'You can also hide individual calendar navigation arrows via shortcode variable hide_arrows="yes"'),
				array('id'=>'evo_arrow_right','type'=>'yesno','name'=>__('Align month navigation arrows to rightside of the calendar','eventon'),'legend'=>'This will align the month navigation arrows to the right side border of the calendar as oppose to next to month title text.'),

				

				// Calendar Header
				array('id'=>'evcal_fcx','type'=>'hiddensection_open','name'=>__('Calendar Header','eventon'), 'display'=>'none'),
				array('id'=>'fs_sort_options','type'=>'fontation','name'=>__('Sort Options Text','eventon'),
					'variations'=>array(
						array('id'=>'evcal__sot', 'name'=>'Default State', 'type'=>'color', 'default'=>'B8B8B8'),
						array('id'=>'evcal__sotH', 'name'=>'Hover State', 'type'=>'color', 'default'=>'d8d8d8'),
					)
				),array('id'=>'fs_calhead','type'=>'fontation','name'=>__('Jump Months Button','eventon'),
					'variations'=>array(
						array('id'=>'evcal__jm001', 'name'=>'Text Color', 'type'=>'color', 'default'=>'ffffff'),
						array('id'=>'evcal__jm002', 'name'=>'Background Color', 'type'=>'color', 'default'=>'ADADAD'),
						array('id'=>'evcal__jm001H', 'name'=>'Text Color (Hover)', 'type'=>'color', 'default'=>'ffffff'),
						array('id'=>'evcal__jm002H', 'name'=>'Background Color (Hover)', 'type'=>'color', 'default'=>'d3d3d3'),						
					)
				),array('id'=>'fs_calhead','type'=>'fontation','name'=>__('Jumper - Month/Year Buttons','eventon'),
					'variations'=>array(
						array('id'=>'evcal__jm003', 'name'=>'Text Color', 'type'=>'color', 'default'=>'ffffff'),
						array('id'=>'evcal__jm004', 'name'=>'Background Color', 'type'=>'color', 'default'=>'ECECEC'),
						array('id'=>'evcal__jm003H', 'name'=>'Text Color (Hover)', 'type'=>'color', 'default'=>'ffffff'),
						array('id'=>'evcal__jm004H', 'name'=>'Background Color (Hover)', 'type'=>'color', 'default'=>'c3c3c3'),							
					)
				),array('id'=>'fs_calhead','type'=>'fontation','name'=>__('Jumper - Month/Year Buttons: Current','eventon'),
					'variations'=>array(
						array('id'=>'evcal__jm006', 'name'=>'Text Color', 'type'=>'color', 'default'=>'ffffff'),
						array('id'=>'evcal__jm007', 'name'=>'Background Color', 'type'=>'color', 'default'=>'CFCFCF'),
					)
				),array('id'=>'fs_calhead','type'=>'fontation','name'=>__('Jumper - Month/Year Buttons: Active','eventon'),
					'variations'=>array(
						array('id'=>'evcal__jm008', 'name'=>'Text Color', 'type'=>'color', 'default'=>'ffffff'),
						array('id'=>'evcal__jm009', 'name'=>'Background Color', 'type'=>'color', 'default'=>'888888'),
					)
				),array('id'=>'fs_calhead','type'=>'fontation','name'=>__('Jumper - Month/Year Label Text','eventon'),
					'variations'=>array(
						array('id'=>'evcal__jm005', 'name'=>'Text Color', 'type'=>'color', 'default'=>'6e6e6e'),
					)
				),array('id'=>'fs_calhead','type'=>'fontation','name'=>__('This month Button','eventon'),
					'variations'=>array(
						array('id'=>'evcal__thm001', 'name'=>'Text Color', 'type'=>'color', 'default'=>'ffffff'),
						array('id'=>'evcal__thm002', 'name'=>'Background Color', 'type'=>'color', 'default'=>'ADADAD'),
						array('id'=>'evcal__thm001H', 'name'=>'Text Color (Hover)', 'type'=>'color', 'default'=>'ffffff'),
						array('id'=>'evcal__thm002H', 'name'=>'Background Color (Hover)', 'type'=>'color', 'default'=>'d3d3d3'),						
					)
				),array('id'=>'fs_calhead','type'=>'fontation','name'=>__('Arrow Circle','eventon'),
					'variations'=>array(
						array('id'=>'evcal__jm010', 'name'=>'Line Color', 'type'=>'color', 'default'=>'e2e2e2'),
						array('id'=>'evcal__jm011', 'name'=>'Background Color', 'type'=>'color', 'default'=>'ffffff'),
						array('id'=>'evcal__jm010H', 'name'=>'Line Color (Hover)', 'type'=>'color', 'default'=>'e2e2e2'),
						array('id'=>'evcal__jm011H', 'name'=>'Background Color (Hover)', 'type'=>'color', 'default'=>'ededed'),
						array('id'=>'evcal__jm01A', 'name'=>'The arrow color', 'type'=>'color', 'default'=>'e2e2e2'),
						array('id'=>'evcal__jm01AH', 'name'=>'The arrow color (Hover)', 'type'=>'color', 'default'=>'ffffff'),
					)
				),				
				array('id'=>'evcal_ftovrr','type'=>'hiddensection_close'),


				// event top
				array('id'=>'evcal_fcx','type'=>'hiddensection_open','name'=>__('EventTop Styles','eventon'), 'display'=>'none'),
					array('id'=>'evcal__fc3','type'=>'color','name'=>__('Event Title font color','eventon'), 'default'=>'6B6B6B'),
					array('id'=>'evcal__fc3st','type'=>'color','name'=>__('Event Sub Title font color','eventon'), 'default'=>'6B6B6B'),
					array('id'=>'evcal__fc6','type'=>'color','name'=>__('Text under event title (on EventTop. Eg. Time, location etc.)','eventon'),'default'=>'8c8c8c'),
					array('id'=>'evcal__fc7','type'=>'color','name'=>__('Category title color (eg. Event Type)','eventon'),'default'=>'c8c8c8'),
					array('id'=>'evcal__evcbrb0','type'=>'color','name'=>__('Event Top Border Color','eventon'), 'default'=>'e5e5e5'),

					array('id'=>'fs_fonti','type'=>'fontation','name'=>__('Background Color','eventon'),
						'variations'=>array(
							array('id'=>'evcal__bgc4', 'name'=>'Default State', 'type'=>'color', 'default'=>'fafafa'),
							array('id'=>'evcal__bgc4h', 'name'=>'Hover State', 'type'=>'color', 'default'=>'f4f4f4'),
							array('id'=>'evcal__bgc5', 'name'=>'Featured Event - Default State', 'type'=>'color', 'default'=>'F9ECE4'),
							array('id'=>'evcal__bgc5h', 'name'=>'Featured Event - Hover State', 'type'=>'color', 'default'=>'FAE4D7'),
						)
					),
					
					array('id'=>'fs_eventtop_tag','type'=>'fontation','name'=>__('General EventTop Tags','eventon'),
						'variations'=>array(
							array('id'=>'fs_eventtop_tag_1', 'name'=>'Background-color', 'type'=>'color', 'default'=>'F79191'),
							array('id'=>'fs_eventtop_tag_2', 'name'=>'Font Color', 'type'=>'color', 'default'=>'ffffff'),
						)
					),
					array('id'=>'fs_cancel_event','type'=>'fontation','name'=>__('Canceled Events Tag','eventon'),
						'variations'=>array(
							array('id'=>'evcal__cancel_event_1', 'name'=>'Background-color', 'type'=>'color', 'default'=>'F79191'),
							array('id'=>'evcal__cancel_event_2', 'name'=>'Font Color', 'type'=>'color', 'default'=>'ffffff'),
							array('id'=>'evcal__cancel_event_3', 'name'=>'Background Strips Color 1', 'type'=>'color', 'default'=>'FDF2F2'),
							array('id'=>'evcal__cancel_event_4', 'name'=>'Background Strips Color 2', 'type'=>'color', 'default'=>'FAFAFA'),
						)
					),
					array('id'=>'fs_eventtop_cmd','type'=>'fontation','name'=>__('Custom Field Buttons','eventon'),
						'variations'=>array(
							array('id'=>'evoeventtop_cmd_btn', 'name'=>'Background-color', 'type'=>'color', 'default'=>'237dbd'),
							array('id'=>'evoeventtop_cmd_btnA', 'name'=>'Text Color', 'type'=>'color', 'default'=>'ffffff'),
						)
					),
				array('id'=>'evcal_fcx','type'=>'hiddensection_close',),
				

				// eventCard Styles
				array('id'=>'evcal_fcxx','type'=>'hiddensection_open','name'=>__('EventCard Styles','eventon'), 'display'=>'none'),
				array('id'=>'fs_fonti1','type'=>'fontation','name'=>'Section Title Text',
					'variations'=>array(
						array('id'=>'evcal__fc4', 'type'=>'color', 'default'=>'6B6B6B'),
						array('id'=>'evcal_fs_001', 'type'=>'font_size', 'default'=>'18px'),
					)
				),
				array('id'=>'evcal__fc5','type'=>'color','name'=>__('General Font Color','eventon'), 'default'=>'656565'),
				array('id'=>'evcal__bc1','type'=>'color','name'=>'Event Card Background Color', 'default'=>'eaeaea', 'rgbid'=>'evcal__bc1_rgb'),			
				array('id'=>'evcal__bc1H','type'=>'color','name'=>'Event Card Background Color (Hover on clickable section)', 'default'=>'d8d8d8'),			
				array('id'=>'evcal__evcbrb','type'=>'color','name'=>'Event Card Border Color', 'default'=>'cdcdcd'),

					// get direction fiels
					array('id'=>'evcal_fcx','type'=>'subheader','name'=>__('Get Directions Field','eventon')),
					array('id'=>'fs_fonti3','type'=>'fontation','name'=>__('Get Directions','eventon'),
						'variations'=>array(
							array('id'=>'evcal_getdir_001', 'name'=>'Background Color', 'type'=>'color', 'default'=>'ffffff'),
							array('id'=>'evcal_getdir_002', 'name'=>'Text Color', 'type'=>'color', 'default'=>'888888'),
							array('id'=>'evcal_getdir_003', 'name'=>'Button Icon Color', 'type'=>'color', 'default'=>'858585'),
						)
					),			

					array('id'=>'evcal_fcx','type'=>'subheader','name'=>__('Buttons','eventon')),
					array('id'=>'fs_fonti3','type'=>'fontation','name'=>__('Button Color','eventon'),
						'variations'=>array(
							array('id'=>'evcal_gen_btn_bgc', 'name'=>'Default State', 'type'=>'color', 'default'=>'237ebd'),
							array('id'=>'evcal_gen_btn_bgcx', 'name'=>'Hover State', 'type'=>'color', 'default'=>'237ebd'),
						)
					),array('id'=>'fs_fonti4','type'=>'fontation','name'=>__('Button Text Color','eventon'),
						'variations'=>array(
							array('id'=>'evcal_gen_btn_fc', 'name'=>'Default State', 'type'=>'color', 'default'=>'ffffff'),
							array('id'=>'evcal_gen_btn_fcx', 'name'=>'Hover State', 'type'=>'color', 'default'=>'ffffff'),
						)
					),
					array('id'=>'fs_fonti5','type'=>'fontation','name'=>__('Close Button Color','eventon'),
						'variations'=>array(
							array('id'=>'evcal_closebtn', 'name'=>'Default State', 'type'=>'color', 'default'=>'eaeaea'),
							array('id'=>'evcal_closebtnx', 'name'=>'Hover State', 'type'=>'color', 'default'=>'c7c7c7'),
						)
					),
					array('id'=>'evcal_fcx','type'=>'hiddensection_close',),

					// featured events
					array('id'=>'evcal_fcx','type'=>'subheader','name'=>__('Featured Events','eventon')),
					array('id'=>'evo_fte_override','type'=>'yesno','name'=>'Override featured event color','legend'=>'This will override the event color you chose for featured event with a different color.','afterstatement'=>'evo_fte_override'),
					array('id'=>'evo_fte_override','type'=>'begin_afterstatement'),
						array('id'=>'evcal__ftec','type'=>'color','name'=>'Featured event left bar color', 'default'=>'ca594a'),
					array('id'=>'evcal_ftovrr','type'=>'end_afterstatement'),
			)
		);
	}
}
