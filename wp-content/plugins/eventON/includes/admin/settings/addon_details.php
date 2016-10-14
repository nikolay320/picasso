<?php
/**
 * EventON Addons list - Local copy
 * @version 0.8
 */

if(!is_admin()) return;

// list of addons
	$addons = array(
		'eventon-action-user' => array(
			'id'=>'EVOAU',
			'name'=>'Action User',
			'link'=>'http://www.myeventon.com/addons/action-user/',
			'download'=>'http://www.myeventon.com/addons/action-user/',
			'desc'=>'Wanna get event contributors involved in your EventON calendar with better permission control? You can do that plus lot more with Action User addon.',
		),'eventon-daily-view' => array(
			'id'=>'EVODV',
			'name'=>'Daily View Addon',
			'link'=>'http://www.myeventon.com/addons/daily-view/',
			'download'=>'http://www.myeventon.com/addons/daily-view/',
			'desc'=>'Do you have too many events to fit in one month and you want to organize them into days? This addon will allow you to showcase events for one day of the month at a time.',
		),'eventon-full-cal'=>array(
			'id'=>'EVOFC',
			'name'=>'Full Cal',
			'link'=>'http://www.myeventon.com/addons/full-cal/',
			'download'=>'http://www.myeventon.com/addons/full-cal/',
			'desc'=>'The list style calendar works for you but you would really like a full grid calendar? Here is the addon that will convert EventON to a full grid calendar view.'
		),
		'eventon-events-map'=>array(
			'id'=>'EVOEM',
			'name'=>'EventsMap',
			'link'=>'http://www.myeventon.com/addons/events-map/',
			'download'=>'http://www.myeventon.com/addons/events-map/',
			'desc'=>'What is an event calendar without a map of all events? EventsMap is just the tool that adds a big google map with all the events for visitors to easily find events by location.'
		),
		'eventon-event-lists'=>array(
			'id'=>'EVEL',
			'name'=>'Event Lists Ext.',
			'link'=>'http://www.myeventon.com/addons/event-lists-extended/',
			'download'=>'http://www.myeventon.com/addons/event-lists-extended/',
			'desc'=>'Do you need to show events list regardless of what month the events are on? With this adodn you can create various event lists including past events, next 5 events, upcoming events and etc.'
		)		
		,'eventon-single-event'=>array(
			'id'=>'EVOSE',
			'name'=>'Single Events',
			'link'=>'http://www.myeventon.com/addons/single-events/',
			'download'=>'http://www.myeventon.com/addons/single-events/',
			'desc'=>'Looking to promote single events in EventON via social media? Use this addon to share individual event pages that matches the awesome EventON layout design.'
		),'eventon-daily-repeats'=>array(
			'id'=>'EVODR',
			'name'=>'Daily Repeats',
			'link'=>'http://www.myeventon.com/addons/daily-repeats/',
			'download'=>'http://www.myeventon.com/addons/daily-repeats/',
			'desc'=>'Daily Repeats will allow you to create events that can repeat on a daily basis - a feature that extends the repeating events capabilities of the calendar.'
		),'eventon-csv-importer'=>array(
			'id'=>'EVOCSV',
			'name'=>'CSV Importer',
			'link'=>'http://www.myeventon.com/addons/csv-event-importer/',
			'download'=>'http://www.myeventon.com/addons/csv-event-importer/',
			'desc'=>'Are you looking to import events from another program to EventON? CSV Import addon is the tool for you. It will import any number of events from a properly build CSV file into your EventON Calendar in few steps.'
		),'eventon-rsvp'=>array(
			'id'=>'EVORS',
			'name'=>'RSVP Events',
			'link'=>'http://www.myeventon.com/addons/rsvp-events/',
			'download'=>'http://www.myeventon.com/addons/rsvp-events/',
			'desc'=>'Do you want to allow your attendees RSVP to event so you know who is coming and who is not? and be able to check people in at the event? RSVP event can do that for you seamlessly.'
		),'eventon-tickets'=>array(
			'id'=>'EVOTX',
			'name'=>'Event Tickets',
			'link'=>'http://www.myeventon.com/addons/event-tickets/',
			'download'=>'http://www.myeventon.com/addons/event-tickets/',
			'desc'=>'Are you looking to sell tickets for your events with eventON? Event Tickets powered by Woocommerce is the ultimate solution for your ticket sales need. Stop paying percentage of your ticket sales and try event tickets addon!.'
		),'eventon-qrcode'=>array(
			'id'=>'EVOQR',
			'name'=>'QR Code',
			'link'=>'http://www.myeventon.com/addons/qr-code',
			'download'=>'http://www.myeventon.com/addons/qr-code',
			'desc'=>'Do you want to allow your attendees RSVP to event so you know who is coming and who is not? and be able to check people in at the event? RSVP event can do that for you seamlessly.'
		),'eventon-weekly-view'=>array(
			'id'=>'EVOWV',
			'name'=>'Weekly View',
			'link'=>'http://www.myeventon.com/addons/weekly-view',
			'download'=>'http://www.myeventon.com/addons/weekly-view',
			'desc'=>'Do you have too many events to fit in one month and you want to organize them into days? This addon will allow you to showcase events for one day of the month at a time.'
		),'eventon-rss'=>array(
			'id'=>'EVORSS',
			'name'=>'RSS Feed',
			'link'=>'http://www.myeventon.com/addons/rss-feed/',
			'download'=>'http://www.myeventon.com/addons/rss-feed/',
			'desc'=>'Your website visitors can now easily RSS to all your calendar events using RSS Feed addon.'
		),'eventon-search'=>array(
			'id'=>'EVOSR',
			'name'=>'Search for EventON',
			'link'=>'http://www.myeventon.com/addons/evo-search',
			'download'=>'http://www.myeventon.com/addons/evo-search',
			'desc'=>'Add search capabilities to your calendar'
		),'eventon-subscriber'=>array(
			'id'=>'EVOSB',
			'name'=>'Subscriber',
			'link'=>'http://www.myeventon.com/addons/subscriber',
			'download'=>'http://www.myeventon.com/addons/subscriber',
			'desc'=>'Allow your users to follow and subscribe to calendars'
		),'eventon-countdown'=>array(
			'id'=>'EVOCD',
			'name'=>'Countdown',
			'link'=>'http://www.myeventon.com/addons/event-countdown/',
			'download'=>'http://www.myeventon.com/addons/event-countdown/',
			'desc'=>'Add countdown timer to events'
		),'eventon-sync-events'=>array(
			'id'=>'EVOSY',
			'name'=>'Sync',
			'link'=>'http://www.myeventon.com/addons/sync-events',
			'download'=>'http://www.myeventon.com/addons/sync-events',
			'desc'=>'Sync facebook and google events'
		),'eventon-reviewer'=>array(
			'id'=>'EVORE',
			'name'=>'Event Reviewer',
			'link'=>'http://www.myeventon.com/addons/event-reviewer',
			'download'=>'http://www.myeventon.com/addons/event-reviewer',
			'desc'=>'Rate and review events'
		),'eventon-event-photos'=>array(
			'id'=>'EVOEP',
			'name'=>'Event Photos',
			'link'=>'http://www.myeventon.com/addons/event-photos',
			'download'=>'http://www.myeventon.com/addons/event-photos',
			'desc'=>'Add a photo library to events instead of one featured image'
		),'eventon-event-slider'=>array(
			'id'=>'EVOEP',
			'name'=>'Event Slider',
			'link'=>'http://www.myeventon.com/addons/event-slider',
			'download'=>'http://www.myeventon.com/addons/event-slider',
			'desc'=>'Interactive slider of events'
		),'eventon-api'=>array(
			'id'=>'EVOAP',
			'name'=>'Event API',
			'link'=>'http://www.myeventon.com/addons/event-api',
			'download'=>'http://www.myeventon.com/addons/event-api',
			'desc'=>'API to access all the calendar events from external sites'
		),'eventon-lists-items'=>array(
			'id'=>'EVOLI',
			'name'=>'Event Lists & Items',
			'link'=>'http://www.myeventon.com/addons/event-lists-items',
			'download'=>'http://www.myeventon.com/addons/event-lists-items',
			'desc'=>'Create custom eventON category lists and item boxes'
		)
	);
?>