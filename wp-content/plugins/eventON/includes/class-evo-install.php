<?php
/**
 * Installation related functions and actions
 *
 * @author   AJDE
 * @category Admin
 * @package  eventon/Classes
 * @version  2.3.22
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class evo_install {

	private static $evo_updates = array(
		'2.3.16'=>'updates/eventon-update-2.3.16.php',
		'2.3.22'=>'updates/eventon-update-2.3.22.php'
	);

	public static function init(){
		add_action('init', array( __CLASS__, 'check_version'),5);
		add_action( 'admin_init', array( __CLASS__, 'install_actions' ), 5 );
	}

	// check eventon version and run the updater if required
		public static function install_actions(){
			$installed_version = get_option('eventon_plugin_version');
			if(empty($installed_version) || $installed_version != EVO()->version){

				self::update();
				do_action('eventon_updated');

				// redirect to welcome screen from eventon settings page
				if (  isset( $_GET['page'] ) && 'eventon' == $_GET['page']   )
					wp_safe_redirect( admin_url( 'index.php?page=evo-about&evo-updated=true' ) );

			}
		}

	
	public static function check_version(){
		if(get_option( 'eventon_plugin_version' ) !== EVO()->version){
			self::install();
		}
	}

	// install eventon
		public static function install(){
			self::create_cron_jobs();
		}

	// create cron jobs after clearning them
	private static function create_cron_jobs(){
		wp_clear_scheduled_hook('evo_trash_past_events');
		wp_schedule_event( time(), 'daily', 'evo_trash_past_events' );
	}

	// Update EVO
		public static function update(){
			$current_evo_version = get_option('eventon_plugin_version');
			foreach ( self::$evo_updates as $version => $updater ) {
				if(version_compare( $current_evo_version, $version, '<' ))
					include($updater);
					self::update_evo_version($version);
			}

			// after each version update update to latest
			self::update_evo_version(EVO()->version);
		}

	// update eventon version to current
		private static function update_evo_version($version=null){
			delete_option( 'eventon_plugin_version' );
			add_option( 'eventon_plugin_version', is_null( $version ) ? EVO()->version : $version );
		}

	// create pages that the plugin relies on 
		public static function create_pages(){
			include_once('admin/eventon-admin-functions.php');

			$pages = apply_filters('eventon_create_pages',array(
				'events_page' => array(
					'name'=> _x( 'event-directory', 'page_slug', 'eventon' ),
					'title'=> _x( 'Events', 'eventon' ),
					'content'=>''
				)
			));

			foreach ( $pages as $key => $page ) {
				eventon_create_page( esc_sql( $page['name'] ), 'eventon_' . $key . '_id', $page['title'], $page['content'], '' );
			}

			delete_transient( 'eventon_cache_excluded_uris' );
			update_option('_eventon_create_pages',1);

		}
}

evo_install::init();