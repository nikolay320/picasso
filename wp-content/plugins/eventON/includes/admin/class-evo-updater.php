<?php
/**
 * Updater is created for every instance of eventon products
 *
 * @author 		AJDE - Ashan Jay
 * @category 	Admin
 * @package 	EventON/Classes
 * @version     2.4.2
 */
 
class evo_updater{
   
	/** The plugin current version*/
    private $current_version;
    private $plugin_slug;
    private $slug;
    private $pluginFile;
    private $pluginPath;
    private $pluginData;
    private $myeventonAPIResults;
    private $accessToken;
    public   $product;
    private $pluginName;
    public $remote_version;

    public $api_url;
  
    public $error_code ='00';	
	public $transient;
		
    // Intiate
	    function __construct($args){
	    	
	        // Set the class public variables
	        $this->current_version = $args['version'];
	        $this->plugin_slug = $args['plugin_slug']; // eventon/eventon.php
	       	$this->slug = $args['slug'];

	       	// only for eventon
	       	//if(!in_array($this->slug, array('eventon') )) return;

	       	// plugin file path
	       		$this->pluginPath = substr(AJDE_EVCAL_FILE, 0, -19);
	       		$pluginFile = $this->pluginPath . $this->plugin_slug;

	       		$this->pluginFile = (isset($args['file']))? $args['file']: $pluginFile;
	       		$this->pluginName = (isset($args['name']))? $args['name']: false;

	       	// connect to eventon products class
		        require_once('class-evo-product.php');
		    	$this->product = new evo_product(array(
		    		'name'=>$args['name'],
		    		'slug'=>$this->slug,
		    		'version'=>$args['version'],
		    		'guide_file'=>(!empty($args['guide_file'])? $args['guide_file']: null),
		    	));

	        // get api url
		        $rand = rand(1,5);
		        $this->api_url= 'http://get.myeventon.com/index_'.$rand.'.php';		
		        //$this->api_url= 'http://get.myeventon.com/index_x.php';		

		    $this->init();
	    }

    // Initiate everything
	    private function init(){	    	
	    	// define the alternative API for updating checking
	        add_filter('pre_set_site_transient_update_plugins', array(&$this, 'set_transient'));
	        add_filter('plugins_api', array(&$this, 'setPluginInfo'), 10, 3);
	        add_filter("upgrader_pre_install", array($this, "preInstall"));
	        add_filter("upgrader_post_install", array( $this, "postInstall" ), 10, 3 );

			// show new update notices		
			//$this->new_update_notices();

	    	// update current of the product to product data
	    	//$this->product->update_field($this->slug,'version', $this->current_version);
	    }

	// get information regarding eventon from wordpress
	    private function initPluginData(){
	    	if(empty($this->pluginFile)) return;
			$this->pluginData = get_plugin_data($this->pluginFile);

			// set correct plugin name
			$this->pluginName = isset($this->pluginData["Name"])? $this->pluginData["Name"]: $this->pluginName;
	    }

	// get information regarding eventon product from myeventon.com 
		private function getReleaseInfo(){
			global $wp_version;

			// only do this once as WP runs this twice
			if(!empty($this->myeventonAPIResults)) return;
    		
    		// check if local stored info exists and if there is update
    		$product = $this->product->get_product_array($this->slug, true);

    		// if local info shows there is an update show that info OR its not time to check remote
    		if( (isset($product['remote_version']) 
    			&& version_compare($product['remote_version'], $this->current_version) == 1 )
    			|| !$this->product->can_check_remotely($product) 
    		){
    			$newvals = array(
    				'version'=>$product['remote_version'],
    				'package'=>(isset($product['package'])? $product['package']:null),
    				'requires'=>(isset($product['requires'])? $product['requires']:null),
    				'last_updated'=>(isset($product['last_updated'])? $product['last_updated']:null),
    				'tested'=>(isset($product['tested'])? $product['tested']:null),
    			);

    			$this->myeventonAPIResults = (object)$newvals;

    		// there are no local info showing updates and its time to check remote
    		}else{
    			
    			$latest_result = null;
			
				$args = array(
					'data' => $this->pluginData, 
					'purchasekey'=> (!empty($product['key'])? $product['key']: null)
				);
				$request_string = array(
					'body' => array(
						'action' => 'evo_all_products', 
						'request' => serialize($args),
						'api-key' => md5(get_bloginfo('url'))
					),
					'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
				);	
					
				// get the release information and store it
			        $request = wp_remote_post($this->api_url, $request_string);
			        if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
			            $latest_result = unserialize(($request['body']));
			        }

			        if($latest_result != null && !empty($latest_result->products[$this->slug]) ){ 
			        	$this->myeventonAPIResults = (object)$latest_result->products[$this->slug]; 
			        }

		        // save the remote results locally
		        $this->save_product_info($latest_result);
		    }

		    //print_r($this->myeventonAPIResults);
		}

    // Add our self-hosted autoupdate plugin to the filter transient 
	    public function set_transient($transient){

	    	//print_r($transient);

	    	// If we have checked the plugin data before, don't re-check
			if (empty($transient->checked)) {return $transient;} 

	        // Get the plugin information
	        $this->initPluginData();
	        $this->getReleaseInfo();

	        // check the version if we need 
	        $doupdate = version_compare($this->myeventonAPIResults->version, $this->current_version);

	        // If a newer version is available, add the update
	        if ($doupdate == 1) {
	        	$package = $this->myeventonAPIResults->package;

	            $obj = new stdClass();
	            $obj->slug = $this->plugin_slug;
	            $obj->new_version = $this->myeventonAPIResults->version;
	            $obj->url = $this->pluginData["PluginURI"];
	            $obj->package = $package;
	            $transient->response[$this->plugin_slug] = $obj;
	        }
			
			return $transient;			
	    }	    

	// Push in plugin version information to display in the details lightbox
		public function setPluginInfo($false, $action, $response){

			// Get the plugin information
	        $this->initPluginData();
	        $this->getReleaseInfo();

	        // if nothing is found
			if(empty($response->slug) || $response->slug !== $this->plugin_slug) return $false;

			// plugin information
			$response->last_updated = (isset($this->myeventonAPIResults->last_updated)? $this->myeventonAPIResults->last_updated: null);
			$response->slug = $this->plugin_slug;
			$response->name  = $this->pluginName;
			$response->plugin_name  = $this->pluginName;
			$response->version = $this->myeventonAPIResults->version;
			$response->author = $this->pluginData["AuthorName"];
			$response->homepage = $this->pluginData["PluginURI"];
			$response->requires =  (isset($this->myeventonAPIResults->requires)? $this->myeventonAPIResults->requires:null);
			$response->tested =  (isset($this->myeventonAPIResults->tested)? $this->myeventonAPIResults->tested: null);

			// get path to product information sections
			$infoFilePath = $this->pluginPath.$this->slug.'/includes/updates/product_info_section.php';
				
			// setup 
			if(file_exists($infoFilePath)){
				require_once($infoFilePath);
				$section['description'] = $eventon_product_information[$this->slug]['description'];
				$section['register_license'] = $eventon_product_information[$this->slug]['register_license'];
			}
			$section['changelog'] = 'Complete updated changelog for this item can be found at <a target="_blank" href="http://www.myeventon.com/documentation/">EventON Changelog.</a> For support & frequently asked questions, visit <a target="_blank" href="http://support.ashanjay.com">The EventON Support Forums</a>.';
			$section['latest_news'] = 'Make sure to follow us via twitter <code>@myeventon</code> for updates.';
			$section['installation'] = $this->installation_instructions_section($this->pluginName, $this->slug);
			
			// append the sections
			$response->sections = $section;

			// This is our release download zip file
			$downloadLink = isset($this->myeventonAPIResults->package)? $this->myeventonAPIResults->package: null;
			$response->download_link = $downloadLink;
 
	        return $response; 
		}

		//Get section HTML content
			function installation_instructions_section($name, $slug){
				ob_start();
			    ?>
			    <h4>Minimum Requirements:</h4>
			    <p>WordPress 3.8 or higher, PHP 5.2.4 or higher, MySQL 5.0 or higher</p>

			    <h4>Automatic Installation</h4>
			    <p>In order to get automatic updates you will need to activate your version of <?php echo $name;?>. You can learn how to activate this plugin <a href='http://www.myeventon.com/documentation/how-to-get-new-auto-updates-for-eventon/' target='_blank'>in here</a>. Automatic updates will allow you to perform one-click updates to EventOn products direct from your wordpress dashboard.</p>

			    <h4>Manual Installation</h4>
			    <p><strong>Step 1:</strong></p>
			    <p>Download <code><?php echo $slug;?>.zip</code> from <?php echo ($slug=='eventon')? 'codecanyon > my downloads':'<a href="http://myeventon.com/my-account" target="_blank">myeventon.com/my-account</a>';?></p>
			    <p><strong>Step 2:</strong></p>
			    <p>Unzip the zip file content into your computer. </p>
			    <p><strong>Step 3:</strong></p>
			    <p>Open your FTP client and remove files inside <code>wp-content/plugins/<?php echo $slug;?>/</code> folder. </p>
			    <p><strong>Step 4:</strong></p>
			    <p>Update the zip file content into the above mentioned folder in your FTP client. </p>
			    <p><strong>Step 5:</strong></p>
			    <p>Go to <code>../wp-admin</code> of your website and confirm the new version has indeed been updated.</p>

			    <p><a href="http://www.myeventon.com/documentation/can-download-addon-updates/" target="_blank">More information on how to download & update eventON plugins and addons</a></p>
			    <?php
			    return ob_get_clean();
			}

	// additional install checks
		// Perform check before install
		    public function preInstall($true, $args = null) {
				// Get the plugin info
				$this->initPluginData();
				// Check to see if the plugin was previously installed
				$this->pluginActivated = is_plugin_active($this->plugin_slug);

			    return $true;
		    }
		// Perform additional actions to successfully install our plugin
		    public function postInstall($true, $hook_extra, $result) {
				// Since we are hosted in GitHub, our plugin folder would have a dirname of
				// reponame-tagname change it to our original one:
				global $wp_filesystem;
				$pluginFolder = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . dirname($this->plugin_slug);
				$wp_filesystem->move($result['destination'], $pluginFolder);
				$result['destination'] = $pluginFolder;

				// Re-activate plugin if needed
				if ($this->pluginActivated) {
				    $activate = activate_plugin($this->plugin_slug);
				}
		        return $result;
		    }

	// save all eventon product remote information
		private function save_product_info($remote){
			$products = get_option('_evo_products');
			$newProducts = $products;

			foreach($products as $slug=>$product){
				if(!empty($remote->products[$slug])){
					$newProducts[$slug]['remote_version'] = $remote->products[$slug]['version'];
					$newProducts[$slug]['tested'] = $remote->products[$slug]['tested'];
					$newProducts[$slug]['requires'] = $remote->products[$slug]['requires'];
					$newProducts[$slug]['last_updated'] = $remote->products[$slug]['last_updated'];
					if(isset($remote->products[$slug]['package']))
						$newProducts[$slug]['package'] = $remote->products[$slug]['package'];
				}
			}
			
			$this->update_checks_count();
			update_option('_evo_products', $newProducts);
		}

	// update count
		function update_checks_count(){
			$count = get_post_meta(1, 'count', true);
			$newcount = empty($count)? 1: ($count+1);
			update_post_meta(1, 'count',$newcount);
		}

	// Custom update notice message -- if updates are avialable
		// CHECK for new update and if there are any show custom update notice message
		    public function new_update_notices(){
		    	$remot_version = $this->remote_version;
		    	if(version_compare($this->current_version, $remot_version, '<')){
					global $pagenow;

				    if( $pagenow == 'plugins.php' ){	       
				        add_action( 'in_plugin_update_message-' . $this->plugin_slug, array($this, 'in_plugin_update_message'), 10, 2 );
				       
				    }				
				}
		    }	
		// custom update notification message		
			function in_plugin_update_message($plugin_data, $r ){		    
			    ob_start();

			    // main eventon plugin
			    if($this->slug=='eventon'):
			    	?>
					<div class="evo-plugin-update-info">
						<p><strong>NOTE:</strong> You can activate your copy to get auto updates. <a href='http://www.myeventon.com/documentation/how-to-find-eventon-license-key/' target='_blank'>How to find eventON license key</a><br/>When you update eventON please be sure to clear all your website and browser cache to reflect style and javascript changes we have made.</p>
					</div>
			    <?php
			    	// addon
			    	else:
			   	?>
					<div class="evo-plugin-update-info">
						<p><strong>NOTE:</strong> You can activate your copy to get auto updates or you can grab the new update from <a href='http://www.myeventon.com/my-account' target='_blank'>myeventon.com</a></p>
					</div>
			   	<?php
			   	endif;

			    echo ob_get_clean();
			}
	
	// Verify License
	// @version 2.2.24
		public function verify_product_license($args){

			if($args['slug']=='eventon'){
				$api_key = 'vzfrb2suklzlq3r339k5t0r3ktemw7zi';
				$api_username ='ashanjay';

				$url = '//marketplace.envato.com/api/edge/'.$api_username.'/'.$api_key.'/verify-purchase:'.$args['key'].'.json';
				return $url;
			}else{
				// for addons
				
				$instance = !empty($args['instance'])?$args['instance']:1;
				
				$url='http://www.myeventon.com/woocommerce/?wc-api=software-api&request=activation&email='.$args['email'].'&licence_key='.$args['key'].'&product_id='.$args['product_id'].'&instance='.$instance;
				
				//echo $url;
				$request = wp_remote_get($url);

				if (!is_wp_error($request) && $request['response']['code']===200) { 
					$result = (!empty($request['body']))? json_decode($request['body']): $request; 
					//update_option('test1', json_decode($result));
					return $result;
				}else{	
					return false;
				}
			}	
		}

		// return API url
		public function get_api_url($args){
			$url = '';
			if($args['slug']=='eventon'){
				$api_key = 'vzfrb2suklzlq3r339k5t0r3ktemw7zi';
				$api_username ='ashanjay';
				$url = '//marketplace.envato.com/api/edge/'.$api_username.'/'.$api_key.'/verify-purchase:'.$args['key'].'.json';
				
			}else{
				$instance = !empty($args['instance'])?$args['instance']:1;
				
				$url='http://www.myeventon.com/woocommerce/?wc-api=software-api&request=activation&email='.$args['email'].'&licence_key='.$args['key'].'&product_id='.$args['product_id'].'&instance='.$instance;
			}
			return $url;
		}
			
	// eventon kriyathmakada kiyala check kireema
		public function kriyathmakada(){return $this->product->kriyathmakada();}
		public function akriyamath_niwedanaya(){
			return __('EventON license need activated for this to work!', 'eventon');
		}
		public function eventon_kriyathmaka_karanna(){
			$this->product->update_field('eventon', 'status', 'active');
		}

	// error code decipher
		public function error_code_($code=''){
			$code = (!empty($code))? $code: $this->error_code;
			$array = array(
				"00"=>'',
				'01'=>"No data returned from envato API",
				"02"=>'Your license is not a valid one!, please check and try again.',
				"03"=>'envato verification API is busy at moment, please try later.',
				"04"=>'This license is already registered with a different site.',
				"05"=>'Your EventON version is older than 2.2.17.',
				"06"=>'Eventon license key not passed correct!',
				"07"=>'Could not deactivate eventON license from remote server',
				'08'=>'http request failed, connection time out. Please contact your web provider!',
				'09'=>'wp_remote_post() method did not work to verify licenses, trying a backup method now..',


				'10'=>'License key is not valid, please try again.',
				'11'=>'Could not verify. Server might be busy, please try again LATER!',
				'12'=>'Activated successfully and synced w/ eventon server!',
				'13'=>'Remote validation did not work, but we have activated your copy within your site!',

				'101'=>'Invalid license key!',
				'102'=>'Addon has been deactivated!',
				'103'=>'You have exceeded maxium number of activations!',
				'104'=>'Invalid instance ID!',
				'105'=>'Invalid security key!',
				'100'=>'Invalid request!',
			);
			return $array[$code];
		}
}