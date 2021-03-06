<?php
/**
 * @package WordPress
 * @subpackage BuddyBoss Media
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Format various notifications generated by photos component.
 * 
 * @since BuddyBoss Media 2.0.8
 * 
 * @param string $action
 * @param int $item_id
 * @param int $secondary_item_id
 * @param int $total_items
 * @param string $format DEFAULT 'string'
 * 
 * @return mixed
 */
function buddyboss_media_format_notifications( $action, $item_id, $secondary_item_id, $total_items, $format='string' ){
	switch( $action ){
		case 'buddyboss_media_tagged':
			$notification_handler = BuddyBoss_Media_Tagging_Notifications::get_instance();
			$return = $notification_handler->format_bp_notifications( $action, $item_id, $secondary_item_id, $total_items, $format );
			break;
		/* we might have other notifications in future */
		default:
			$return = '';
			break;
	}
	if( $return )
		return $return;
}