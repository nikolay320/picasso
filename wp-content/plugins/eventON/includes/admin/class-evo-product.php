<?php
/**
 * Contain eventon product information eventon products
 * store and retrive that information
 * @version 	2.3.19
 * @updated 	2016-2
 */
class evo_product{

	public $products;
	public $slug;
	public $args;
	public function __construct($args){
		$this->slug = $args['slug'];
		$this->args = $args;
		$data = get_option('_evo_products');
		
		//echo $args['slug'];

		// if there is no data at all crunch from past
		if(empty($data)){
			$this->data_cruncher();
		}else{
			$this->products= $data;
		}

		// if product doesnt exists in the data array add it
		if(empty($data[$args['slug']]) ){
			$this->new_product();
		}
		
	}

	// run this to merge old 2 data arrays into one
	// product data for eventon products
		public function data_cruncher(){
			$license = get_option('_evo_licenses');
			$addons = get_option('eventon_addons');

			// if both these exists
			if(!empty($license) && !empty($addons)){
				$data = array_merge($license, $addons);
				update_option('_evo_products', $data);
				$this->products= $data;
				delete_option('_evo_licenses');
				delete_option('eventon_addons');
			}elseif(!empty($license) && empty($addons)){
				update_option('_evo_products', $license);
				$this->products= $license;
				delete_option('_evo_licenses');
			}else{
				// run for the first time
				$this->new_product();
			}
		}
	// update interval products array
		public function this_update_products(){
			$data = get_option('_evo_products');
			if(!empty($data)){
				$this->products = $data;
			}else{
				$this->data_cruncher();
			}
		}

	// add new eventon product OR create eventon product for the
	// first time on first time installations
		public function new_product(){			
			$array = array(
				$this->args['slug']=>array(
					'name'=>$this->args['name'],				
					'slug'=>$this->args['slug'],
					'version'=>$this->args['version'],				
					'remote_version'=>'',
					'lastchecked'=>'',
					'status'=>'inactive',
					'instance'=>'',
					'remote_validity'=>'none',
					'email'=>'',
					'key'=>'',
					'siteurl'=>get_site_url(),				
					'guide_file'=>(!empty($this->args['guide_file'])? $this->args['guide_file']: null),
				)
			);

			// if there are product info already exists
			if(!empty($this->products)){
				$new_data = array_merge($this->products, $array);
			}else{
				$new_data = $array;
			}
			update_option('_evo_products',$new_data);
		}

	// license related
		// deactivate license
			public function deactivate($slug){
				$product_data = get_option('_evo_products');
				if(!empty($product_data[$slug])){

					$new_data = $product_data;
					//unset($new_data[$slug]['key']);
					$new_data[$slug]['status']='inactive';

					update_option('_evo_products',$new_data);
					return true;
				}else{return false;}
			}

	// UPDATE
		// update with new remote version
			public function update_remote_version($slug, $remote_version, $lastchecked=false){
				if(!empty($this->products[$slug])){
					$new_data = $this->products;
					$new_data[$slug]['remote_version']=$remote_version;

					// compare versions
					$has_updates = ( version_compare($remote_version, $new_data[$slug]['version'] ) >=0)? true:false;

					$new_data[$slug]['has_new_updates']=$has_updates;

					// last check update
					if($lastchecked){
						date_default_timezone_set("UTC"); 
						$new_data[$slug]['lastchecked']=time();
					}

					update_option('_evo_products',$new_data);
					return true;
				}else{return false;}
			}
		// save license key
			public function save_license($slug, $key, $email='', $product_id='', $remote_validity='', $name='', $instance=''){

				$product_data = get_option('_evo_products');

				$debug = '';

				// if product slug present
				if(!empty($slug) && !empty($product_data[$slug])){
					$new_data = $product_data;
					$new_data[$slug]['email']=$email;					
					$new_data[$slug]['key']=$key;
					$new_data[$slug]['product_id']=$product_id;
					$new_data[$slug]['status']='active';
					$new_data[$slug]['instance']=$instance;
					$new_data[$slug]['remote_validity']=$remote_validity;
					
					update_option('_evo_products',$new_data);

					// at the same time update mismatch in remote and local versions
					$this->get_remote_version($slug);
					$debug .= '1-';
					return true;

				// if the product doesnt exist in the data array
				}elseif(empty($product_data[$slug])){
					$array = array(
						$slug=>array(
							'name'=>(!empty($name)? $name: $slug),
							'slug'=>$slug,
							'version'=>'',				
							'remote_version'=>'',
							'lastchecked'=>'',
							'instance'=>$instance,
							'status'=>'active',
							'remote_validity'=>$remote_validity,
							'email'=>$email,
							'product_id'=>$product_id,
							'key'=>$key,
							'siteurl'=>get_site_url(),				
							'guide_file'=>'',
						)
					);
					if(!empty($product_data)){
						$new_data = array_merge($product_data, $array);
					}else{	$new_data = $array;	}
					update_option('_evo_products',$new_data);

					$debug .= '2-';
					return true;
				}else{
					$debug .= '3-';
					return false;
				}
			}
		// activate a product
			public function activate_product($slug){
				if(!empty($this->products[$slug])){
					return $this->update_field($slug, 'status', 'active');
				}else{return false;}
			}
		// update last check time for new version
			public function update_lastchecked($slug=''){
				$slug = (!empty($slug))? $slug: $this->slug;
				if(!empty($this->products[$slug])){
					$new_data = $this->products;
					date_default_timezone_set("UTC"); 
					$new_data[$slug]['lastchecked']=time();
					update_option('_evo_products',$new_data);
					return true;
				}else{return false;}
			}
		// update any given fiels 
			public function update_field($slug, $field, $value){
				$product_data = get_option('_evo_products');

				if(!empty($product_data[$slug])){
					$new_data = $product_data;
					$new_data[$slug][$field]=$value;
					update_option('_evo_products',$new_data);
					return true;
				}else{return false;}
			}
			public function get_field($slug, $field){
				$product_data = get_option('_evo_products');
				return (!empty($product_data[$slug][$field]) )? $product_data[$slug][$field]: false;
			}
		// update addons existance using WP activated plugin data
		// used in addons & licenses page
			public function ADD_update_addons(){ 
				$evo_addons = get_option('_evo_products');

				// site have eventon addons and its an array
				if(!empty($evo_addons) && is_array($evo_addons)){
					$active_plugins = get_option( 'active_plugins' );  
					
					$new_addons = $evo_addons;
					foreach($evo_addons as $addon=>$some){
						// addon actually doesn not exist in plugins
						if($addon!='eventon' && !in_array($addon.'/'.$addon.'.php', $active_plugins)){
							// change status to removed if addon doesnt exists anymore
							$new_addons[$addon]['status']='removed';
							//unset($new_addons[$addon]);
						}
					}
					update_option('_evo_products',$new_addons);
				}
    		}

	// RETURNS
		public function get_products_array(){
			return ($this->products)? $this->products: false;
		}

		// get eventon product information array by product slug
		public function get_product_array($slug, $new= false){
			$products = ($new)? get_option('_evo_products'): $this->products;
			return ($products)? $products[$slug]: false;
		}
		public function get_license_status(){
			if(!empty($this->products[$this->slug]) && !empty($this->products[$this->slug]['status'])){
				return $this->products[$this->slug]['status'];
			}else{
				// add the stutus field if doesnt exist
				$this->update_field($this->slug, 'status', 'inactive');
				return false;
			}
		}
		public function get_license(){
			if(!empty($this->products[$this->slug]) && !empty($this->products[$this->slug]['key'])){
				return $this->products[$this->slug]['key'];
			}else{return false;}
		}
		public function get_partial_license(){			
			$key=$this->get_license($this->slug);
			if(!empty($key )){
				if($this->slug=='eventon'){
					$valid_key = $this->purchase_key_format($key);
					if($valid_key){
						$parts = explode('-', $key);
						return 'xxxxxxxx-xxxx-xxxx-xxxx-'.$parts[4];
					}else{
						$this->deactivate($this->slug);
						return 'n/a';
					}
				}else{
					// for addons
					return 'xxxxxxxx-xxxx-xxxx-xxxx-';
				}
			}else{return '--';}
		}

		// eventon products kriyathmakada kiya baleema
		public function kriyathmakada(){
			if(!empty($this->products[$this->slug])){
				return (!empty($this->products[$this->slug]['status']) && $this->products[$this->slug]['status']=='active' &&
					!empty($this->products[$this->slug]['key'])
				)? true:false;
			}else{return false;}
		}

		public function get_current_version($slug){
			if(!empty($this->products[$slug])){
				return $this->products[$slug]['version'];
			}else{return false;}
		}

		// return true if there is an update
		public function has_update($slug){
			if(empty($this->products[$slug]) || empty($this->products[$slug]['remote_version']) ) return false;

			$update = version_compare($this->products[$slug]['remote_version'], $this->products[$slug]['version']);

			return $update==1? true:false;
		}
		public function get_remote_version($slug=''){
			$slug= !empty($slug) ? $slug : $this->slug;
			if(!empty($this->products[$slug])){
				// check if saved remote version is older than current version
				// then update remote_version to same as version
				if(version_compare($this->products[$slug]['version'], $this->products[$slug]['remote_version'],'>')){
					$this->update_field($slug, 'remote_version', $this->products[$slug]['version']);
					return $this->products[$slug]['version'];
				}else{
					return $this->products[$slug]['remote_version'];
				}				
			}else{return false;}
		}

		// checking for updates
		public function can_check_remotely($product = ''){

			// if doing force check then proceed
			// @updated 2.3.19
			if(!empty($_REQUEST['force-check']) && $_REQUEST['force-check']=='1')
			 	return true;

			// if product array passed
				$product = !empty($product)? $product:$this->products;

			if(!empty($this->products[$this->slug]) && !empty($this->products[$this->slug]['lastchecked'])){

				$timenow = current_time('timestamp');
				$lastchecked = (int)$this->products[$this->slug]['lastchecked'];

				$checking_gap = 86400; // every 24 hours 3600x 24

				return ( ($lastchecked+$checking_gap)<$timenow)? true:false;

			}else{	return true;	}
		}		
		// check purchase code correct format
		// @version 2.4
			public function purchase_key_format($key, $type='eventon'){				
				if(!strpos($key, '-'))	return false;

				if($type== 'eventon' || $type=='main'){
					// /5fbe9924-1a99-4ea6-baad-22aace2a2ac0
					$str = explode('-', $key);
					return (strlen($str[1])==4 && strlen($str[2])==4 && strlen($str[3])==4 )? true: false;
				}else{
					// EVO8a8126de-acda-4017-8553-d45f51f4e581
					$str = explode('-', $key);

					return (strlen($str[1])==4 && strlen($str[2])==4 && strlen($str[3])==4 && strpos($str[0], 'EV')!== false)? true: false;
				}				
			}	
}