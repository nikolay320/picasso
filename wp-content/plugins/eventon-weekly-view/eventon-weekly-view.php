<?php
/*
 Plugin Name: EventON - Weekly View
 Plugin URI: http://www.myeventon.com/
 Description: Create a week view of events for EventON Calendar
 Author: Ashan Jay
 Version: 0.7
 Author URI: http://www.ashanjay.com/
 Requires at least: 3.8
 Tested up to: 4.1
 */
class eventon_weeklyview{	
	public $version='0.7';
	public $eventon_version = '2.2.23';
	public $name = 'WeeklyView';

	public $is_running_wv = false;

	public $addon_data = array();
	public $slug, $plugin_slug , $plugin_url , $plugin_path ;
	private $urls;
	public $template_url ;	

	public $shortcode_args = array();
	
	/* Construct	 */
		public function __construct(){			
			$this->super_init();

			include_once( 'includes/admin/class-admin_check.php' );
			$this->check = new addon_check($this->addon_data);
			$check = $this->check->initial_check();
			
			if($check){				
				$this->addon = new evo_addon($this->addon_data);
				add_action( 'init', array( $this, 'init' ), 0 );			
			}				
		}
	
	// SUPER init
		function super_init(){
			// PLUGIN SLUGS			
			$this->addon_data['plugin_url'] = path_join(plugins_url(), basename(dirname(__FILE__)));
			$this->addon_data['plugin_slug'] = plugin_basename(__FILE__);
			list ($t1, $t2) = explode('/', $this->addon_data['plugin_slug'] );
	        $this->addon_data['slug'] = $t1;
	        $this->addon_data['plugin_path'] = dirname( __FILE__ );
	        $this->addon_data['evo_version'] = $this->eventon_version;
	        $this->addon_data['version'] = $this->version;
	        $this->addon_data['name'] = $this->name;

	        $this->plugin_url = $this->addon_data['plugin_url'];
	        $this->plugin_slug = $this->addon_data['plugin_slug'];
	        $this->slug = $this->addon_data['slug'];
	        $this->plugin_path = $this->addon_data['plugin_path'];
		}

	// INITIATE please
		function init(){
			// Activation
			$this->activate();		
			
			// Deactivation
			register_deactivation_hook( __FILE__, array($this,'deactivate'));

			// RUN addon updater only in dedicated pages
			if ( is_admin() ){
				$this->addon->updater();
				include_once( 'includes/admin/admin-init.php' );		
			}

			include_once( 'includes/class-frontend.php' );
			include_once( 'includes/eventonWV_shortcode.php' );

			if ( defined('DOING_AJAX') ){
				include_once( 'includes/eventonWV_ajax.php' );
			}

			$this->shortcodes = new evo_wv_shortcode();
			$this->frontend = new evowv_frontend();
		}
	
	// SECONDARY FUNCTIONS
		function activate(){
			// add actionUser addon to eventon addons list
			$this->addon->activate();
		}

		// Deactivate addon
		function deactivate(){
			$this->addon->remove_addon();
		}
		function print_scripts(){
			$this->frontend->print_scripts();
		}
}

// Initiate this addon within the plugin
$GLOBALS['eventon_wv'] = new eventon_weeklyview();

// php tag
function add_eventon_wv($args='') {
	global $eventon_wv, $eventon;	
	echo $eventon_wv->generate_eventon_wv_calendar($args, 'php');
}
?>