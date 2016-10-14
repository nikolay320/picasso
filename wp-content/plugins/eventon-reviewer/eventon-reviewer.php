<?php
/*
 Plugin Name: EventON - Event Reviewer
 Plugin URI: http://www.myeventon.com/
 Description: Rate & review events
 Author: Ashan Jay
 Version: 0.4
 Author URI: http://www.ashanjay.com/
 Requires at least: 3.8
 Tested up to: 4.4
 */

class eventon_reviewer{
	
	public $version='0.4';
	public $eventon_version = '2.3.13';
	public $name = 'Event Reviewer';
	public $id = 'EVORE';
			
	public $addon_data = array();
	public $slug, $plugin_slug , $plugin_url , $plugin_path ;
	private $urls;
	public $template_url ;

	
	// construct
	public function __construct(){

		$this->super_init();

		include_once( 'includes/admin/class-admin_check.php' );
		$this->check = new addon_check($this->addon_data);
		$check = $this->check->initial_check();
		
		if($check){
			$this->addon = new evo_addon($this->addon_data);
			$this->helper = new evo_helper();

			$this->opt = get_option('evcal_options_evcal_re');
			$this->opt2 = get_option('evcal_options_evcal_2');

			add_action( 'init', array( $this, 'init' ), 0 );

			// settings link in plugins page
			add_filter("plugin_action_links_".$this->plugin_slug, array($this,'eventon_plugin_links' ));
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

			include_once( 'includes/class-shortcode.php' );
			include_once( 'includes/class-frontend.php' );
			$this->frontend = new evore_front();
			
			if ( is_admin() ){
				$this->addon->updater();	
				include_once( 'includes/admin/admin-init.php' );
			}
			if ( defined('DOING_AJAX') ){
				include_once( 'includes/class-ajax.php' );
			}
			

			$this->register_post_type();
			
			$this->shortcodes = new evo_re_shortcode();
		}

	// create new post type
		function register_post_type(){
			$labels = $this->proper_labels('Event Review','Event Reviews');
			register_post_type('evo-review', 
				apply_filters( 'eventon_register_post_type_review',
					array(
						'labels' => $labels,
						'description'	=> 'Review for eventon events',
						'public' 				=> true,
						'show_ui' 				=> true,
						'capability_type' 		=> 'eventon',
						'map_meta_cap'			=> true,
						'publicly_queryable' 	=> false,
						'hierarchical' 			=> false,
						'query_var'		 		=> true,
						'supports' 				=> array('title','custom-fields'),					
						'menu_position' 		=> 5, 
						'show_in_menu'			=>'edit.php?post_type=ajde_events',
						'has_archive' 			=> true
					)
				)
			);
		}
		function proper_labels($sin, $plu){
			return array(
			'name' => _x($plu, 'post type general name' , 'eventon'),
			'singular_name' => _x($sin, 'post type singular name' , 'eventon'),
			'add_new' => __('Add New '. $sin , 'eventon'),
			'add_new_item' => __('Add New '.$sin , 'eventon'),
			'edit_item' => __('Edit '.$sin , 'eventon'),
			'new_item' => __('New '.$sin , 'eventon'),
			'all_items' => __('All '.$plu , 'eventon'),
			'view_item' => __('View '.$sin , 'eventon'),
			'search_items' => __('Search RSVP' , 'eventon'),
			'not_found' =>  __('No '.$plu.' found' , 'eventon'),
			'not_found_in_trash' => __('No '.$plu.' found in Trash' , 'eventon'), 
			'parent_item_colon' => '',
			'menu_name' => _x($plu, 'admin menu', 'eventon')
		  );
		}

	// SECONDARY FUNCTIONS	
		function eventon_plugin_links($links){
			$settings_link = '<a href="admin.php?page=eventon&tab=evcal_re">Settings</a>'; 
			array_unshift($links, $settings_link); 
	 		return $links; 	
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
		// duplicate language function to make it easy on the eye
			function lang($variable, $default_text, $lang=''){
				return eventon_get_custom_language($this->opt2, $variable, $default_text, $lang);
			}
}
// Initiate this addon within the plugin
$GLOBALS['eventon_re'] = new eventon_reviewer();
?>