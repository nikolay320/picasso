<?php
/**
 * EventON Uninstall
 *
 * Uninstalling EventON deletes everything.
 *
 * @author 		AJDE
 * @category 	Core
 * @package 	EventON/Uninstaller
 * @version     1.1
 */
if( !defined('WP_UNINSTALL_PLUGIN') ) exit();

$evo_opt = get_option('evcal_options_evcal_1');

// If options have been stored, and 'evo_donot_delete' is equal to 'yes' - exit.
if(!empty($evo_opt['evo_donot_delete']) && $evo_opt['evo_donot_delete']=='yes')
	exit();

global $wpdb, $wp_roles;

// Delete options
$wpdb->query("DELETE FROM $wpdb->options WHERE 
	option_name LIKE '%evcal_%' 
	OR option_name LIKE '%_evo_%'
	OR option_name LIKE '%eventon_%';");