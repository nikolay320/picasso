<?php
/**
 * EventON Admin Include
 *
 * Include for EventON related events in admin.
 *
 * @author 		AJDE
 * @category 	Admin
 * @package 	EventON/Admin
 * @version     2.4.4
 */
class eventon_admin_shortcode_box{
	
	private $_in_select_step=false;
	private $evopt;

	function __construct(){
		$this->evopt =  get_option('evcal_options_evcal_1');
	}
	
	public function shortcode_default_field($key){
		$options_1 = $this->evopt;

		// Additional Event Type taxonomies 
			$event_types_sc = array();
			for( $x=1; $x <= (apply_filters('evo_event_type_count',5)); $x++){
				if($x <=2 ) continue;
				if(!empty($options_1['evcal_ett_'.$x]) && $options_1['evcal_ett_'.$x]=='yes' && !empty($options_1['evcal_eventt'.$x])){
				 	$event_types_sc['event_type_'.$x] = array(
						'name'=>'Event Type '.$x,
						'type'=>'taxonomy',
						'guide'=>'Event Type '.$x.' category IDs - seperate by commas (eg. 3,12)',
						'placeholder'=>'eg. 3, 12',
						'var'=>'event_type_'.$x,
						'default'=>'0'
					);
				}else{ $event_types_sc['event_type_'.$x] = array(); }
			}

		
		$SC_defaults = array(
			'cal_id'=>array(
				'name'=>__('Calendar ID (optional)','eventon'),
				'type'=>'text',
				'var'=>'cal_id',
				'default'=>'0',
				'placeholder'=>'eg. 1'
			),
			'number_of_months'=>array(
				'name'=>__('Number of Months','eventon'),
				'type'=>'text',
				'var'=>'number_of_months',
				'default'=>'0',
				'placeholder'=>'eg. 5'
			),		
			'show_et_ft_img'=>array(
				'name'=>__('Show Featured Image','eventon'),
				'type'=>'YN',
				'var'=>'show_et_ft_img',
				'default'=>'no'
			),
			'hide_past'=>array(
				'name'=>__('Hide Past Events','eventon'),
				'type'=>'YN',
				'var'=>'hide_past',
				'default'=>'no'
			),'hide_past_by'=>array(
				'name'=>__('Hide Past Events by','eventon'),
				'guide'=>__('You can choose which date (start or end) to use to decide when to clasify them as past events.','eventon'),
				'type'=>'select',
				'var'=>'hide_past_by',
				'default'=>'ee',
				'options'=>array( 
					'ss'=>'Start Date/time',
					'ee'=>'End Date/Time',
				)
			),
			'ft_event_priority'=>array(
				'name'=>__('Feature event priority','eventon'),
				'type'=>'YN',
				'guide'=>__('Move featured events above others','eventon'),
				'var'=>'ft_event_priority',
				'default'=>'no',
			),
			'event_count'=>array(
				'name'=>__('Event count limit','eventon'),
				'placeholder'=>'eg. 3',
				'type'=>'text',
				'guide'=>__('Limit number of events for each month eg. 3','eventon'),
				'var'=>'event_count',
				'default'=>'0'
			),
			'month_incre'=>array(
				'name'=>__('Month Increment','eventon'),
				'type'=>'text',
				'placeholder'=>'eg. +1',
				'guide'=>__('Change starting month (eg. +1)','eventon'),
				'var'=>'month_incre',
				'default'=>'0'
			),
			'event_type'=>array(
				'name'=>__('Event Type','eventon'),
				'type'=>'taxonomy',
				'guide'=>__('Event Type category IDs - seperate by commas (eg. 3,12)','eventon'),
				'placeholder'=>'eg. 3, 12',
				'var'=>'event_type',
				'default'=>'0'
			),'event_type_2'=>array(
				'name'=>__('Event Type 2','eventon'),
				'type'=>'taxonomy',
				'guide'=>__('Event Type 2 category IDs - seperate by commas (eg. 3,12)','eventon'),
				'placeholder'=>'eg. 3, 12',
				'var'=>'event_type_2',
				'default'=>'0'
			),
			'event_type_3'=>$event_types_sc['event_type_3'],
			'event_type_4'=>$event_types_sc['event_type_4'],
			'event_type_5'=>$event_types_sc['event_type_5'],
			'fixed_month'=>array(
				'name'=>__('Fixed Month','eventon'),
				'type'=>'text',
				'guide'=>__('Set fixed month for calendar start (integer)','eventon'),
				'var'=>'fixed_month',
				'default'=>'0',
				'placeholder'=>'eg. 10'
			),
			'fixed_year'=>array(
				'name'=>__('Fixed Year','eventon'),
				'type'=>'text',
				'guide'=>__('Set fixed year for calendar start (integer)','eventon'),
				'var'=>'fixed_year',
				'default'=>'0',
				'placeholder'=>'eg. 2013'
			),
			'event_order'=>array(
				'name'=>__('Event Order','eventon'),
				'type'=>'select',
				'guide'=>__('Select ascending or descending order for event. By default it will be Ascending order.','eventon'),
				'var'=>'event_order',
				'default'=>'ASC',
				'options'=>array('ASC'=>'ASC','DESC'=>'DESC')
			),
			'pec'=>array(
				'name'=>__('Event Cut-off','eventon'),
				'type'=>'select',
				'guide'=>__('Past or upcoming events cut-off time. This will allow you to override past event cut-off settings for calendar events. Current date = today at 12:00am','eventon'),
				'var'=>'pec',
				'default'=>'Current Time',
				'options'=>array( 
					'ct'=>'Current Time: '.date('m/j/Y g:i a', current_time('timestamp')),
					'cd'=>'Current Date: '.date('m/j/Y', current_time('timestamp')),
				)
			),
			'lang'=>array(
				'name'=>'Language Variation (<a href="'.get_admin_url().'admin.php?page=eventon&tab=evcal_2">Update Language Text</a>)',
				'type'=>'select',
				'guide'=>__('Select which language variation text to use','eventon'),
				'var'=>'lang',
				'default'=>'L1',
				'options'=>array('L1'=>'L1','L2'=>'L2','L3'=>'L3')
			),
			'hide_mult_occur'=>array(
				'name'=>__('Hide multiple occurence (HMO)','eventon'),
				'type'=>'YN',
				'guide'=>__('Hide events from showing more than once between months','eventon'),
				'var'=>'hide_mult_occur',
				'default'=>'no',
			),
			'show_repeats'=>array(
				'name'=>__('Show all repeating events while HMO','eventon'),
				'type'=>'YN',
				'guide'=>__('If you are hiding multiple occurence of event but want to show all repeating events set this to yes','eventon'),
				'var'=>'show_repeats',
				'default'=>'no',
			),
			'fixed_mo_yr'=>array(
				'name'=>__('Fixed Month/Year','eventon'),
				'type'=>'fmy',
				'guide'=>__('Set fixed month and year value (Both values required)(integer)','eventon'),
				'var'=>'fixed_my',
			),'fixed_d_m_y'=>array(
				'name'=>__('Fixed Date/Month/Year','eventon'),
				'type'=>'fdmy',
				'guide'=>__('Set fixed date, month and year value (All values required)(integer)','eventon'),
				'var'=>'fixed_my',
			),'evc_open'=>array(
				'name'=>__('Open eventCards on load','eventon'),
				'type'=>'YN',
				'guide'=>__('Open eventCards when the calendar first load on the page by default. This will override the settings saved for default calendar.','eventon'),
				'var'=>'evc_open',
				'default'=>'no',
			),'UIX'=>array(
				'name'=>__('User Interaction','eventon'),
				'type'=>'select',
				'guide'=>__('Select the user interaction option to override individual event user interactions','eventon'),
				'var'=>'ux_val',
				'default'=>'0',
				'options'=>apply_filters('eventon_uix_shortcode_opts', array(
					'0'=>'None',
					'X'=>__('Do not interact','eventon'),
					'1'=>__('Slide Down EventCard','eventon'),
					'3'=>__('Lightbox popup window','eventon')))
			),'etc_override'=>array(
				'name'=>__('Event type color override','eventon'),
				'type'=>'YN',
				'guide'=>__('Select this option to override event colors with event type colors, if they exists','eventon'),
				'var'=>'etc_override',
				'default'=>'no',
			),'only_ft'=>array(
				'name'=>__('Show only featured events','eventon'),
				'type'=>'YN',
				'guide'=>__('Display only featured events in the calendar','eventon'),
				'var'=>'only_ft',
				'default'=>'no',
			),'jumper'=>array(
				'name'=>__('Show jump months option','eventon'),
				'type'=>'YN',
				'guide'=>__('Display month jumper on the calendar','eventon'),
				'var'=>'jumper',
				'default'=>'no',
			),'accord'=>array(
				'name'=>__('Accordion effect on eventcards','eventon'),'type'=>'YN',
				'guide'=>__('This will close open events when new one clicked','eventon'),'var'=>'accord','default'=>'no',
			),'sort_by'=>array(
				'name'=>__('Default Sort by','eventon'),
				'type'=>'select',
				'guide'=>__('Sort calendar events by on load','eventon'),
				'var'=>'sort_by',
				'default'=>'sort_date',
				'options'=>array( 
					'sort_date'=>__('Date','eventon'),
					'sort_title'=>__('Title','eventon'),
					'sort_posted'=>__('Posted Date','eventon'),
					'sort_rand'=>__('Random Order','eventon'),
				)
			),'hide_sortO'=>array(
				'name'=>__('Hide sort options section','eventon'),
				'type'=>'YN',
				'guide'=>__('This will hide sort options section on the calendar.','eventon'),
				'var'=>'hide_so',
				'default'=>'no',
			),'expand_sortO'=>array(
				'name'=>__('Expand sort options by default','eventon'),
				'type'=>'YN',
				'guide'=>__('This will expand sort options section on load for calendar.','eventon'),
				'var'=>'exp_so',
				'default'=>'no',
			),'rtl'=>array(
				'name'=>__('* RTL can now be changed from eventON settings','eventon'),
				'type'=>'note',
				'var'=>'rtl',
				'default'=>'no',
			),'show_limit'=>array(
				'name'=>__('Show load more events button','eventon'),
				'type'=>'YN',
				'guide'=>__('Require "event count limit" to work, then this will add a button to show rest of the events for calendar in increments','eventon'),
				'var'=>'show_limit',
				'default'=>'no',
			),'show_limit_redir'=>array(
				'name'=>__('Redirect load more events button','eventon'),
				'type'=>'text',
				'guide'=>__('http:// URL the load more events button will redirect to instead of loading more events on the same calendar.','eventon'),
				'var'=>'show_limit_redir',
				'default'=>'no',
			),'members_only'=>array(
				'name'=>__('Make this calendar only visible to loggedin user','eventon'),
				'type'=>'YN',
				'guide'=>__('This will make this calendar only visible to loggedin users','eventon'),
				'var'=>'members_only',
				'default'=>'no',
			),'layout_changer'=>array(
				'name'=>__('Show calendar layout changer','eventon'),
				'type'=>'YN',
				'guide'=>__('Show layout changer on calendar so users can choose between tiles or rows layout','eventon'),
				'var'=>'layout_changer',
				'default'=>'no',
			),'filter_type'=>array(
				'name'=>__('Calendar Filter Type','eventon'),
				'type'=>'select',
				'guide'=>__('If sorting/filter allowed for calendar, you can select between dropdown list or checkbox list for multiple filter selection.','eventon'),
				'var'=>'filter_type',
				'default'=>'default',
				'options'=>array( 
					'default'=>__('Dropdown Filter List','eventon'),
					'select'=>__('Multiple Checkbox Filter','eventon'),
				)
			)

		);
		
		return $SC_defaults[$key];
	
	}	
	
	// array of shortcode variables
		public function get_shortcode_field_array(){
			$_current_year = date('Y');
			$shortcode_guide_array = apply_filters('eventon_shortcode_popup', array(
				array(
					'id'=>'s1',
					'name'=>'Main Calendar',
					'code'=>'add_eventon',
					'variables'=>apply_filters('eventon_basiccal_shortcodebox', array(
						$this->shortcode_default_field('cal_id')
						,$this->shortcode_default_field('show_et_ft_img')
						,$this->shortcode_default_field('ft_event_priority')
						,$this->shortcode_default_field('only_ft')
						,$this->shortcode_default_field('hide_past')	
						,$this->shortcode_default_field('hide_past_by')	
						,$this->shortcode_default_field('sort_by')
						,$this->shortcode_default_field('event_order')
						,$this->shortcode_default_field('event_count')
						,$this->shortcode_default_field('show_limit')
						,$this->shortcode_default_field('show_limit_redir')
						,$this->shortcode_default_field('month_incre')
						,$this->shortcode_default_field('event_type')
						,$this->shortcode_default_field('event_type_2')
						,$this->shortcode_default_field('event_type_3')
						,$this->shortcode_default_field('event_type_4')
						,$this->shortcode_default_field('event_type_5')
						,$this->shortcode_default_field('etc_override')
						,$this->shortcode_default_field('fixed_mo_yr')						
						,$this->shortcode_default_field('lang')
						,$this->shortcode_default_field('UIX')
						,$this->shortcode_default_field('evc_open')					
						,array(
								'name'=>'Show jump months option',
								'type'=>'YN',
								'guide'=>'Display month jumper on the calendar',
								'var'=>'jumper',
								'default'=>'no',
								'afterstatement'=>'jumper_offset'
							),array(
								'name'=>' Jumper Start Year',
								'type'=>'select',
								'options'=>array(
									'0'=>$_current_year-2,
									'1'=>$_current_year-1,
									'2'=>$_current_year,
									),
								'guide'=>'Select which year you want to set to start jumper options at relative to current year',
								'var'=>'jumper_offset','default'=>'0',
								'closestatement'=>'jumper_offset'
							)

						,$this->shortcode_default_field('hide_sortO')						
						,$this->shortcode_default_field('expand_sortO')
						,$this->shortcode_default_field('filter_type')
						,$this->shortcode_default_field('accord')
						,array(
								'name'=>'Hide Calendar Arrows',
								'type'=>'YN',
								'guide'=>'This will hide calendar arrow navigations',
								'var'=>'hide_arrows',
								'default'=>'no',
							)
						,
							array(
								'name'=>'Activate Tile Design',
								'type'=>'YN',
								'guide'=>'This will activate the tile event design for calendar instead of rows of events.',
								'default'=>'no',
								'var'=>'tiles',
								'afterstatement'=>'tiles'
							),
							array(
								'name'=>'Tile Box Height (px)',
								'placeholder'=>'eg. 200',
								'type'=>'text',
								'guide'=>'Set the fixed height of event tile for the tiled calendar design',
								'var'=>'tile_height','default'=>'0'
							),array(
								'name'=>'Tile Background Color',
								'type'=>'select',
								'options'=>array(
									'0'=>'Event Color',
									'1'=>'Featured Image',
									),
								'guide'=>'Select the type of background for the event tile design',
								'var'=>'tile_bg','default'=>'0'
							),array(
								'name'=>'Number of Tiles in a Row',
								'type'=>'select',
								'options'=>array(
									'2'=>'2',
									'3'=>'3',
									'4'=>'4',
									),
								'guide'=>'Select the number of tiles to show on one row',
								'var'=>'tile_count','default'=>'0'
							),
							/*array(
								'name'=>'Tile Style',
								'type'=>'select',
								'options'=>array(
									'0'=>'Default',
									'1'=>'Top bar',
									),
								'guide'=>'With this you can select different layout styles for tiles',
								'var'=>'tile_style','default'=>'0'
							),*/
							array(
								'name'=>'Custom Code',
								'type'=>'customcode', 'value'=>'',
								'closestatement'=>'tiles'
							)
						,
						$this->shortcode_default_field('members_only')
						
					))
				),
				array(
					'id'=>'s2',
					'name'=>'Events List',
					'code'=>'add_eventon_list',
					'variables'=> apply_filters('eventon_basiclist_shortcodebox',array(
						$this->shortcode_default_field('number_of_months')
						,array(
							'name'=>'Event count limit',
							'placeholder'=>'eg. 3',
							'type'=>'text',
							'guide'=>'Limit number of events per month (integer)',
							'var'=>'event_count',
							'default'=>'0'
						)
						,$this->shortcode_default_field('show_limit')
						,$this->shortcode_default_field('show_limit_redir')
						,$this->shortcode_default_field('month_incre')
						,$this->shortcode_default_field('fixed_mo_yr')
						,$this->shortcode_default_field('cal_id')
						,$this->shortcode_default_field('event_order')
						,$this->shortcode_default_field('hide_past')
						,$this->shortcode_default_field('hide_past_by')
						,$this->shortcode_default_field('event_type')
						,$this->shortcode_default_field('event_type_2')
						,$this->shortcode_default_field('event_type_3')
						,$this->shortcode_default_field('event_type_4')
						,$this->shortcode_default_field('event_type_5')	
						,$this->shortcode_default_field('hide_mult_occur'),
						array(
							'name'=>'Show all repeating events while HMO',
							'type'=>'YN',
							'guide'=>'If you are hiding multiple occurence of event but want to show all repeating events set this to yes',
							'var'=>'show_repeats',
							'default'=>'no',
						),array(
							'name'=>'Hide empty months',
							'type'=>'YN',
							'guide'=>'Hide months without any events on the events list',
							'var'=>'hide_empty_months',
							'default'=>'no',
						),array(
							'name'=>'Show year',
							'type'=>'YN',
							'guide'=>'Show year next to month name on the events list',
							'var'=>'show_year',
							'default'=>'no',
						),$this->shortcode_default_field('ft_event_priority'),
						$this->shortcode_default_field('only_ft'),
						$this->shortcode_default_field('etc_override'),
						$this->shortcode_default_field('accord'),
						array(
								'name'=>'Activate Tile Design',
								'type'=>'YN',
								'guide'=>'This will activate the tile event design for calendar instead of rows of events.',
								'default'=>'no',
								'var'=>'tiles',
								'afterstatement'=>'tiles'
							),
							array(
								'name'=>'Tile Box Height (px)',
								'placeholder'=>'eg. 200',
								'type'=>'text',
								'guide'=>'Set the fixed height of event tile for the tiled calendar design',
								'var'=>'tile_height','default'=>'0'
							),array(
								'name'=>'Tile Background Color',
								'type'=>'select',
								'options'=>array(
									'0'=>'Event Color',
									'1'=>'Featured Image',
									),
								'guide'=>'Select the type of background for the event tile design',
								'var'=>'tile_bg','default'=>'0'
							),array(
								'name'=>'Number of Tiles in a Row',
								'type'=>'select',
								'options'=>array(
									'2'=>'2',
									'3'=>'3',
									'4'=>'4',
									),
								'guide'=>'Select the number of tiles to show on one row',
								'var'=>'tile_count','default'=>'0'
							),array(
								'name'=>'Custom Code',
								'type'=>'customcode', 'value'=>'',
								'closestatement'=>'tiles'
							)
						
					))
				)
			));
			
			return $shortcode_guide_array;
		}

	// get content for shortcode generator
		public function get_content(){
			global $ajde, $eventon;

			if(!$eventon->evo_updater->kriyathmakada()) 
				return '<p style="padding:10px;text-align:center">'.$eventon->evo_updater->akriyamath_niwedanaya() .'</p>';
			
			return $ajde->wp_admin->get_content(
				$this->get_shortcode_field_array(),
				'add_eventon'
			);
		}
}

$GLOBALS['evo_shortcode_box'] = new eventon_admin_shortcode_box();
?>