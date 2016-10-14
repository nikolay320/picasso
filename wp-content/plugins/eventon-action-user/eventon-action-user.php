<?php
/*
 * Plugin Name: EventON - Action User
 * Plugin URI: http://www.myeventon.com/
 * Description: Powerful eventON user control and event submission manager
 * Author: Ashan Jay
 * Version: 2.0.1
 * Author URI: http://www.ashanjay.com/
 * Requires at least: 4.0
 * Tested up to: 4.4.1
 */

class eventon_au{
	
	public $version='2.0.1';
	public $eventon_version = '2.3.21';
	public $name = 'ActionUser';

	public $addon_data = array();
	public $slug, $plugin_slug , $plugin_url , $plugin_path ;
	
	// construct
	public function __construct(){

		$this->super_init();

		include_once( 'includes/admin/class-admin_check.php' );
		$this->check = new addon_check($this->addon_data);
		$check = $this->check->initial_check();
		
		if($check){
			$this->addon = new evo_addon($this->addon_data);
		
			add_action( 'init', array( $this, 'init' ), 0 );
			add_action( 'eventon_register_taxonomy', array( $this, 'create_user_tax' ) ,10);			
		}		
	}
	
	// SUPER init
		function super_init(){
			// PLUGIN SLUGS			
			$this->addon_data['plugin_url'] = path_join(WP_PLUGIN_URL, basename(dirname(__FILE__)));
			$this->addon_data['plugin_slug'] = plugin_basename(__FILE__);
			list ($t1, $t2) = explode('/', $this->addon_data['plugin_slug'] );
	        $this->addon_data['slug'] = $t1;
	        $this->addon_data['plugin_path'] = dirname( __FILE__ );
	        $this->addon_data['evo_version'] = $this->eventon_version;
	        $this->addon_data['version'] = $this->version;
	        $this->addon_data['name'] = 'ActionUser';

	        $this->plugin_url = $this->addon_data['plugin_url'];
	        $this->assets_path = str_replace(array('http:','https:'), '',$this->addon_data['plugin_url']).'/assets/';
	        
	        $this->plugin_slug = $this->addon_data['plugin_slug'];
	        $this->slug = $this->addon_data['slug'];
	        $this->plugin_path = $this->addon_data['plugin_path'];
		}

	// INITIATE action user
		function init(){

			include_once( 'includes/class-frontend.php' );			
			include_once( 'includes/shortcode.php' );
			
			if ( defined('DOING_AJAX') ){
				include_once( 'includes/ajax.php' );
			}

			$this->frontend = new evoau_frontend();
			
			// Activation
			$this->activate();		
			
			// Deactivation
			register_deactivation_hook( __FILE__, array($this,'deactivate'));

			// RUN addon updater only in dedicated pages
			if ( is_admin() ){
				$this->addon->updater();
				include_once( 'includes/admin/class-admin.php' );
				$this->admin = new evoau_admin();	
			}			
		}
	
	// TAXONOMY 
	// event_users
		function create_user_tax(){
			register_taxonomy( 'event_users', 
				apply_filters( 'eventon_taxonomy_objects_event_users', array('ajde_events') ),
				apply_filters( 'eventon_taxonomy_args_event_users', array(
					'hierarchical' => true, 
					'label' => 'EvenON Users', 
					'show_ui' => false,
					'query_var' => true,
					'capabilities'			=> array(
						'manage_terms' 		=> 'manage_eventon_terms',
						'edit_terms' 		=> 'edit_eventon_terms',
						'delete_terms' 		=> 'delete_eventon_terms',
						'assign_terms' 		=> 'assign_eventon_terms',
					),
					'rewrite' => array( 'slug' => 'event-user' ) 
				)) 
			);
		}
	
	// ACTIVATION
		function activate(){
			// add actionUser addon to eventon addons list
			$this->addon->activate();
		}

		// Deactivate addon
		function deactivate(){
			$this->addon->remove_addon();
		}	
}

// Initiate this addon within the plugin
$GLOBALS['eventon_au'] = new eventon_au();
?>