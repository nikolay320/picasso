<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Post types
 *
 * Registers post types and taxonomies
 *
 * @class 		EVO_post_types
 * @version		2.2.9
 * @package		Eventon/Classes/events
 * @category	Class
 * @author 		AJDE
 */

class EVO_post_types{

	private static $evOpt='';
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( __CLASS__, 'register_taxonomies' ), 5 );
		add_action( 'init', array( __CLASS__, 'register_post_types' ), 5 );
		self::$evOpt = get_option('evcal_options_evcal_1');
	}

	// Register eventon taxonomies.
	public static function register_taxonomies() {
		// Taxonomies
		do_action( 'eventon_register_taxonomy' );		
		
		$evOpt = self::$evOpt;

		$__capabilities = array(
			'manage_terms' 		=> 'manage_eventon_terms',
			'edit_terms' 		=> 'edit_eventon_terms',
			'delete_terms' 		=> 'delete_eventon_terms',
			'assign_terms' 		=> 'assign_eventon_terms',
		);

		register_taxonomy( 'event_location', 
			apply_filters( 'eventon_taxonomy_objects_event_location', array('ajde_events') ),
			apply_filters( 'eventon_taxonomy_args_event_location', array(
				'hierarchical' => false, 
				'label' => __('Event Location','eventon'), 
				'show_ui' => true,
				'query_var' => true,
				'capabilities'	=> $__capabilities,
				'rewrite' => apply_filters('evotax_slug_loc', array( 'slug' => 'event-location' ) )
			)) 
		);
		register_taxonomy( 'event_organizer', 
			apply_filters( 'eventon_taxonomy_objects_event_organizer', array('ajde_events') ),
			apply_filters( 'eventon_taxonomy_args_event_organizer', array(
				'hierarchical' => false, 
				'label' => __('Event Organizer','eventon'), 
				'show_ui' => true,
				'query_var' => true,
				'capabilities'			=> $__capabilities,
				'rewrite' => apply_filters('evotax_slug_org', array( 'slug' => 'event-organizer' ) )
			)) 
		);

		// Event type custom taxonomy NAMES
		$event_type_names = evo_get_ettNames($evOpt);

		// for each activated event type category
		for($x=1; $x<evo_get_ett_count($evOpt)+1; $x++){
			$ab = ($x==1)? '':'_'.$x;
			$ab2 = ($x==1)? '':'-'.$x;
			$evt_name = $event_type_names[$x];

			register_taxonomy( 'event_type'.$ab, 
				apply_filters( 'eventon_taxonomy_objects_event_type'.$ab, array('ajde_events') ),
				apply_filters( 'eventon_taxonomy_args_event_type'.$ab, array(
					'hierarchical' => true, 
					'labels' => array(
		                    'name' 				=> __( "$evt_name Categories", 'eventon' ),
		                    'singular_name' 	=> __( "$evt_name Category", 'eventon' ),
							'menu_name'			=> _x( $evt_name, 'Admin menu name', 'eventon' ),
		                    'search_items' 		=> __( "Search {$evt_name} Categories", 'eventon' ),
		                    'all_items' 		=> __( "All {$evt_name} Categories", 'eventon' ),
		                    'parent_item' 		=> __( "Parent {$evt_name} Category", 'eventon' ),
		                    'parent_item_colon' => __( "Parent {$evt_name} Category:", 'eventon' ),
		                    'edit_item' 		=> __( "Edit {$evt_name} Category", 'eventon' ),
		                    'update_item' 		=> __( "Update {$evt_name} Category", 'eventon' ),
		                    'add_new_item' 		=> __( "Add New {$evt_name} Category", 'eventon' ),
		                    'new_item_name' 	=> __( "New {$evt_name} Category Name", 'eventon' )
		            	),
					'show_ui' => true,
					'query_var' => true,
					'capabilities'			=> $__capabilities,
					'rewrite' => array( 'slug' => 'event-type'.$ab2 ) 
				)) 
			);
		}
	}
	
	/** Register core post types */
	public static function register_post_types() {
		if ( post_type_exists('ajde_events') )
			return;

		do_action( 'eventon_register_post_type' );

		// get updated event slug for evnet posts
		$evOpt = self::$evOpt;
		$event_slug = (!empty($evOpt['evo_event_slug']))? $evOpt['evo_event_slug']: 'events';
		
		register_post_type('ajde_events', 
			apply_filters( 'eventon_register_post_type_ajde_events',
				array(
					'labels' => array(
							'name'                  => __( 'Events', 'eventon' ),
							'singular_name'         => __( 'Event', 'eventon' ),
							'menu_name'             => _x( 'Events', 'Admin menu name', 'eventon' ),
							'add_new'               => __( 'Add Event', 'eventon' ),
							'add_new_item'          => __( 'Add New Event', 'eventon' ),
							'edit'                  => __( 'Edit', 'eventon' ),
							'edit_item'             => __( 'Edit Event', 'eventon' ),
							'new_item'              => __( 'New Event', 'eventon' ),
							'view'                  => __( 'View Event', 'eventon' ),
							'view_item'             => __( 'View Event', 'eventon' ),
							'search_items'          => __( 'Search Events', 'eventon' ),
							'not_found'             => __( 'No Events found', 'eventon' ),
							'not_found_in_trash'    => __( 'No Events found in trash', 'eventon' ),
							'parent'                => __( 'Parent Event', 'eventon' ),
							'featured_image'        => __( 'Event Image', 'eventon' ),
							'set_featured_image'    => __( 'Set event image', 'eventon' ),
							'remove_featured_image' => __( 'Remove event image', 'eventon' ),
							'use_featured_image'    => __( 'Use as event image', 'eventon' ),
							'insert_into_item'      => __( 'Insert into event', 'eventon' ),
							'uploaded_to_this_item' => __( 'Uploaded to this event', 'eventon' ),
							'filter_items_list'     => __( 'Filter Events', 'eventon' ),
							'items_list_navigation' => __( 'Events navigation', 'eventon' ),
							'items_list'            => __( 'Events list', 'eventon' ),
						),
					'description' 			=> __( 'This is where you can add new events to your calendar.', 'eventon' ),
					'public' 				=> true,
					'show_ui' 				=> true,
					'capability_type' 		=> 'eventon',
					'map_meta_cap'			=> true,
					'publicly_queryable' 	=> true,
					'hierarchical' 			=> false,
					'rewrite' 				=> apply_filters('eventon_event_slug', array(
						'slug'=>$event_slug)),
					'query_var'		 		=> true,
					'supports' 				=> apply_filters('eventon_event_post_supports', array('title','author', 'editor','custom-fields','thumbnail','page-attributes')),
					//'supports' 			=> array('title','editor','thumbnail','page-attributes'),
					'menu_position' 		=> 15, 
					'has_archive' 			=> true,
					'taxonomies'			=> array('post_tag'),
					'exclude_from_search'	=> apply_filters('evo_cpt_search_visibility',true)
				)
			)
		);
	}
}

new EVO_post_types();