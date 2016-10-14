<?php
/**
 * Update EVO to 2.3.16
 *
 * @author 		AJDE
 * @category 	Admin
 * @package 	eventon/Admin/Updates
 * @version     2.3.16
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $eventon;

// save location and organizer ID on event posts
	$events = new WP_Query(array(
		'post_type'=>'ajde_events',
		'posts_per_page'=>-1
	));
	$haveEvents = $events->have_posts();
	if(!empty($haveEvents)){
		while($events->have_posts()): $events->the_post();
			$event_id = $events->ID;

			// location
			$location_terms = wp_get_post_terms($event_id, 'event_location');
			if ( $location_terms && ! is_wp_error( $location_terms ) ){
				add_post_meta($event_id,'evo_location_tax_id',$location_terms[0]->term_id);
			}

			// organizer			
			$organizer_terms = wp_get_post_terms($event_id, 'event_organizer');
			if ( $organizer_terms && ! is_wp_error( $organizer_terms ) ){
				add_post_meta($event_id,'evo_organizer_tax_id',$organizer_terms[0]->term_id);
			}
			
		endwhile;
		wp_reset_postdata();
	}

