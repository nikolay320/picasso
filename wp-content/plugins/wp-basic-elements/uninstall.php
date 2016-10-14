<?php
/**
 * WP Basic Elements
 * By Damir Calusic (damir@damircalusic.com)
 * 
 * Uninstall - removes all WP Basic Elements options from DB when user deletes the plugin via WordPress backend.
 * @since 1.1
 **/
 
if ( !defined('WP_UNINSTALL_PLUGIN') ) {
    exit();
}

delete_option( 'register_wpb_settings' );