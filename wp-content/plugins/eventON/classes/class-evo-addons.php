<?php
/**
 * 
 * eventon addons class
 * This will be used to control everything about eventon addons
 *
 * @author 		AJDE
 * @category 	Admin
 * @package 	EventON/Classes
 * @version     2.2.27
 */

if(!class_exists('evo_addon')){


class evo_addon{

	private $addon_data;
	private $urls;

	function __construct($arr){

		// assign initial values for instance of addon
		$this->addon_data = $arr;

		// save main plugin file urls to be used from options
			$init = get_option('eventon_addon_urls');
			if(empty($init)){
				$path = AJDE_EVCAL_PATH;
				$arr = array(
					'addons'=>$path .'/classes/class-evo-addons.php',
					'date'=> time()
				);
				update_option('eventon_addon_urls',$arr);
				$init = $arr;
			}
			$this->urls = $init;

		// run updater
		if(is_admin()){
			$this->updater();
		}
	}

	// return eventon version
		public function get_eventon_version(){
			global $eventon;
			return $eventon->version;
		}

	// REQUIREMENT check
		// not using this since 2.2.19 addons will have its own version of this
		public function requirment_check(){
			
			// eventon exist if addon connect to this function
			// check if eventon version is compatible and return true of false
			global $eventon;

			$eventON_version = $eventon->version;

			// if eventON version is lower than what we need
			if(!empty($eventON_version) && version_compare($this->addon_data['evo_version'], $eventON_version)>0){				
				add_action('admin_notices', array($this, '_old_eventon_warning'));
			}
			return true;
		}

		// display warning if EventON version is old
			function _no_eventon_warning(){
		        ?>
		        <div class="message error"><p><?php printf(__('Well... looks like you dont have eventON main plugin installed... %s needs <a href="%s">EventON</a> to work properly, my friend!', 'eventon'),
		        	$this->addon_data['name'], 'http://www.myeventon.com/'); ?></p></div>
		        <?php
		    }
		    function _old_eventon_warning(){
		        ?>
		        <div class="message error"><p><?php printf(__('oh no.. your eventON version is old...  <b>%s</b> need eventON version %s or higher to work correctly! ', 'eventon'),  $this->addon_data['name'], $this->addon_data['evo_version']); ?></p></div>
		        <?php
		    }

	// Activate addon
		public function activate(){
			// dont need this any more initiating this class will 
			// add addon to eventon product data
		}
	
	/// the MAIN updater function
		public function updater(){
			global $pagenow, $eventon;
			
			$__needed_pages = $this->get_check_pages();

			// only for admin
			if(is_admin() && !empty($pagenow) && in_array($pagenow, $__needed_pages) ){
				
				if($pagenow == 'admin.php' && isset($_GET['tab']) && $_GET['tab']=='evcal_4' 
					|| $pagenow!='admin.php'){
					
					// INITIATE Updater for addon product
					$path = AJDE_EVCAL_PATH;
					require_once( $path .'/includes/admin/class-evo-updater.php' );

					$this->evo_updater = new evo_updater( 
						array(
							'version'=>$this->addon_data['version'], 
							'slug'=>$this->addon_data['slug'],
							'plugin_slug'=>$this->addon_data['plugin_slug'],
							'name'=>$this->addon_data['name'],
							'guide_file'=> !empty($this->addon_data['guide_file'])?
								$this->addon_data['guide_file']:
								(( file_exists($this->addon_data['plugin_path'].'/guide.php') )? 
								$this->addon_data['plugin_url'].'/guide.php':null),
						)
					);	
				}
			}
		}

	// Deactivate Addon from eventon products
		public function remove_addon(){
			if(!empty($this->evo_updater))
				return $this->evo_updater->product->deactivate($this->addon_data['slug']);
		}
	// return the current page names that should be used to check updates
		function get_check_pages(){
			return array('update-core.php',
				'admin-ajax.php', 'plugin-install.php','admin.php');
		}
	
}

}// endif

?>