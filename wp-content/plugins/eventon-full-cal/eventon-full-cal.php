<?php
/*
 Plugin Name: EventON - Full cal
 Plugin URI: http://www.myeventon.com/
 Description: Create a full grid calendar with a month view of eventON events.
 Author: Ashan Jay
 Version: 1.1.1
 Author URI: http://www.ashanjay.com/
 Requires at least: 3.8
 Tested up to: 4.4

 */
 
class EventON_full_cal{
	
	public $version='1.1.1';
	public $eventon_version = '2.3.20';
	public $name = 'FullCal';
		
	public $is_running_fc =false;
		
	public $addon_data = array();
	public $slug, $plugin_slug , $plugin_url , $plugin_path ;
	private $urls;
	public $template_url ;

	/*
	 * Construct
	 */
	public function __construct(){
		$this->super_init();
		add_action('plugins_loaded', array($this, 'plugin_init'));
	}

	function plugin_init(){
		include_once( 'includes/admin/class-admin_check.php' );
		$this->check = new addon_check($this->addon_data);
		$check = $this->check->initial_check();
		
		if($check){			
			$this->addon = new evo_addon($this->addon_data);
			add_action( 'init', array( $this, 'init' ), 0 );
		}else{
			add_action('admin_notices', array($this, '_eventon_warning'));
		}
	}
	function _eventon_warning(){
		?><div class="message error"><p><?php _e('EventON is required for FullCal to work properly.', 'eventon'); ?></p></div><?php
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
			include_once( 'includes/class-frontend.php' );
			include_once( 'includes/eventonFC_shortcode.php' );
			
			if ( is_admin() )
				include_once( 'includes/admin/admin-init.php' );

			if ( defined('DOING_AJAX') ){
				include_once( 'includes/eventonFC_ajax.php' );
			}

			// RUN addon updater only in dedicated pages
			if ( is_admin() ){
				$this->addon->updater();			
			}

			$this->shortcodes = new evo_fc_shortcode();
			$this->frontend = new evofc_frontend();
			
			// Activation
			$this->activate();		
			
			// Deactivation
			register_deactivation_hook( __FILE__, array($this,'deactivate'));
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
			$this->frontend->print_scripts_();
		}
}

// Initiate this addon within the plugin
$GLOBALS['eventon_fc'] = new EventON_full_cal();

/*** Only for PHP call to fullCal  */
	function add_eventon_fc($args='') {
		global $eventon_fc;		
		
		$content = $eventon_fc->frontend->generate_eventon_fc_calendar($args, 'php');
		
		echo $content;
	}
?>