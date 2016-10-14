<?php
/**
 * Function ajax for backend
 * @version   2.4.4
 */
class EVO_admin_ajax{
	public function __construct(){
		$ajax_events = array(
			'deactivate_lic'=>'eventon_deactivate_evo',
			'validate_license'=>'validate_license',
			'verify_key'=>'verify_key',
			'remote_validity'=>'remote_validity',
			'get_license_api_url'=>'get_license_api_url',
			'deactivate_addon'=>'deactivate_addon',
			'remote_test'=>'remote_test',
			'export_events'=>'export_events',			
			'get_addons_list'=>'get_addons_list',
			'export_settings'=>'export_settings',
			'import_settings'=>'import_settings',
		);
		foreach ( $ajax_events as $ajax_event => $class ) {

			$prepend = 'eventon_';
			add_action( 'wp_ajax_'. $prepend . $ajax_event, array( $this, $class ) );
			add_action( 'wp_ajax_nopriv_'. $prepend . $ajax_event, array( $this, $class ) );
		}

		add_action('wp_ajax_eventon-feature-event', array($this, 'eventon_feature_event'));
	}

	// export eventon settings
		function export_settings(){
			// check if admin and loggedin
				if(!is_admin() && !is_user_logged_in()) die('User not loggedin!');

			// verify nonce
				if(!wp_verify_nonce($_REQUEST['nonce'], 'evo_export_settings')) die('Security Check Failed!');

			header('Content-type: text/plain');
			header("Content-Disposition: attachment; filename=Evo_settings__".date("d-m-y").".json");
			
			$json = array();
			$evo_options = get_option('evcal_options_evcal_1');
			foreach($evo_options as $field=>$option){
				// skip fields
				if(in_array($field, array('option_page','action','_wpnonce','_wp_http_referer'))) continue;
				$json[$field] = $option;
			}

			echo json_encode($json);
			exit;
		}
	// import settings
		function import_settings(){
			$output = array('status'=>'','msg'=>'');
			// verify nonce
				$output['success'] =wp_create_nonce('eventon_admin_nonce');
				if(!wp_verify_nonce($_POST['nonce'], 'eventon_admin_nonce')) $output['msg'] = __('Security Check Failed!','eventon');

			// check if admin and loggedin
				if(!is_admin() && !is_user_logged_in()) $output['msg'] = __('User not loggedin!','eventon');

			$JSON_data = $_POST['jsondata'];

			// check if json array present
			if(!is_array($JSON_data))  $output['msg'] = __('Not correct json format!','eventon');

			// if all good
			if( empty($output['msg'])){
				update_option('evcal_options_evcal_1', $JSON_data);
				$output['success'] = 'good';
				$output['msg'] = 'Successfully updated settings!';
			}
			
			echo json_encode($output);
			exit;

		}

	// export events as CSV
	// @version 2.2.30
		function export_events(){

			// check if admin and loggedin
				if(!is_admin() && !is_user_logged_in()) die('User not loggedin!');

			// verify nonce
				if(!wp_verify_nonce($_REQUEST['nonce'], 'eventon_download_events')) die('Security Check Failed!');

			header('Content-Encoding: UTF-8');
        	header('Content-type: text/csv; charset=UTF-8');
			header("Content-Disposition: attachment; filename=Eventon_events_".date("d-m-y").".csv");
			header("Pragma: no-cache");
			header("Expires: 0");
			echo "\xEF\xBB\xBF"; // UTF-8 BOM
			
			$evo_opt = get_option('evcal_options_evcal_1');
			$event_type_count = evo_get_ett_count($evo_opt);
			$cmd_count = evo_calculate_cmd_count($evo_opt);

			$fields = apply_filters('evo_csv_export_fields',array(
				'publish_status',				
				'evcal_event_color'=>'color',
				'event_name',				
				'event_description','event_start_date','event_start_time','event_end_date','event_end_time',

				'evcal_allday'=>'all_day',
				'evo_hide_endtime'=>'hide_end_time',
				'evcal_gmap_gen'=>'event_gmap',
				'evo_year_long'=>'yearlong',
				'_featured'=>'featured',

				'evcal_location_name'=>'location_name',
				'evo_location_id'=>'evo_location_id',
				'evcal_location'=>'event_location',				
				'evcal_organizer'=>'event_organizer',
				'evo_organizer_id'=>'evo_organizer_id',
				'evcal_subtitle'=>'evcal_subtitle',
				'evcal_lmlink'=>'learnmore link',
				'image_url',

				'evcal_repeat'=>'repeatevent',
				'evcal_rep_freq'=>'frequency',
				'evcal_rep_num'=>'repeats',
				'evp_repeat_rb'=>'repeatby',
			));
			
			$csvHeader = '';
			foreach($fields as $var=>$val){	$csvHeader.= $val.',';	}

			// event types
				for($y=1; $y<=$event_type_count;  $y++){
					$_ett_name = ($y==1)? 'event_type': 'event_type_'.$y;
					$csvHeader.= $_ett_name.',';
				}
			// for event custom meta data
				for($z=1; $z<=$cmd_count;  $z++){
					$_cmd_name = 'cmd_'.$z;
					$csvHeader.= $_cmd_name.",";
				}

			$csvHeader.= "\n";
			echo iconv("UTF-8", "ISO-8859-2", $csvHeader);
 
			$events = new WP_Query(array(
				'posts_per_page'=>-1,
				'post_type' => 'ajde_events',
				'post_status'=>'any'			
			));

			if($events->have_posts()):
				date_default_timezone_set('UTC');

				// for each event
				while($events->have_posts()): $events->the_post();
					$__id = get_the_ID();
					$pmv = get_post_meta($__id);

					$csvRow = '';
					$csvRow.= get_post_status($__id).",";
					//echo (!empty($pmv['_featured'])?$pmv['_featured'][0]:'no').",";
					$csvRow.= (!empty($pmv['evcal_event_color'])? $pmv['evcal_event_color'][0]:'').",";

					// event name
						$eventName = get_the_title();
						$eventName = htmlentities($eventName);
						//$output = iconv("utf-8", "ascii//TRANSLIT//IGNORE", $eventName);
						//$output =  preg_replace("/^'|[^A-Za-z0-9\s-]|'$/", '', $output); 
						$csvRow.= '"'.$eventName.'",';

					$event_content = get_the_content();
						$event_content = str_replace('"', "'", $event_content);
						$event_content = str_replace(',', "\,", $event_content);
						$event_content = htmlentities( $event_content);
					$csvRow.= '"'.$event_content.'",';

					// start time
						$start = (!empty($pmv['evcal_srow'])?$pmv['evcal_srow'][0]:'');
						if(!empty($start)){
						$csvRow.= date('n/j/Y,g:i:A', $start).',';
						}else{ $csvRow.= "'','',";	}

					// end time
						$end = (!empty($pmv['evcal_erow'])?$pmv['evcal_erow'][0]:'');
						if(!empty($end)){
							$csvRow.= date('n/j/Y,g:i:A',$end).',';
						}else{ $csvRow.= "'','',";	}

					// FOR EACH field
					foreach($fields as $var=>$val){
						
						// yes no values
						if(in_array($val, array('featured','all_day','hide_end_time','event_gmap','evo_year_long','_evo_month_long','repeatevent'))){
							$csvRow.= ( (!empty($pmv[$var]) && $pmv[$var][0]=='yes') ? 'yes': 'no').',';
						}

						// organizer field
							if($val == 'evo_organizer_id'){
								$Orgterms = wp_get_object_terms( $__id, 'event_organizer' );
								if ( $Orgterms && ! is_wp_error( $Orgterms ) ){
									$csvRow.= '"'.$Orgterms[0]->term_id . '",';
								}else{	$csvRow.= ",";	}
							}
						// location tax field
							if($val == 'evo_location_id'){
								$Locterms = wp_get_object_terms( $__id, 'event_location' );
								if ( $Locterms && ! is_wp_error( $Locterms ) ){
									$csvRow.= '"'.$Locterms[0]->term_id . '",';
								}else{	$csvRow.= ",";	}
							}

						// skip fields
						if(in_array($val, array('featured','all_day','hide_end_time','event_gmap','evo_year_long','_evo_month_long','repeatevent','color','publish_status','event_name','event_description','event_start_date','event_start_time','event_end_date','event_end_time','evo_organizer_id', 'evo_location_id'))) continue;

						// image
						if($val =='image_url'){
							$img_id =get_post_thumbnail_id($__id);
							if($img_id!=''){
								$img_src = wp_get_attachment_image_src($img_id,'full');
								$csvRow.= $img_src[0].",";
							}else{ $csvRow.= ",";}
						}else{
							if(!empty($pmv[$var])){
								$value = htmlentities($pmv[$var][0]);
								$csvRow.= '"'.$value.'"';
							}else{ $csvRow.= '';}
							$csvRow.= ',';
						}
					}
					
					// event types
						for($y=1; $y<=$event_type_count;  $y++){
							$_ett_name = ($y==1)? 'event_type': 'event_type_'.$y;
							$terms = get_the_terms( $__id, $_ett_name );

							if ( $terms && ! is_wp_error( $terms ) ){
								$csvRow.= '"';
								foreach ( $terms as $term ) {
									$csvRow.= $term->term_id.',';
									//$csvRow.= $term->name.',';
								}
								$csvRow.= '",';
							}else{ $csvRow.= ",";}
						}
					// for event custom meta data
						for($z=1; $z<=$cmd_count;  $z++){
							$cmd_name = '_evcal_ec_f'.$z.'a1_cus';
							$csvRow.= (!empty($pmv[$cmd_name])? 
								'"'.str_replace('"', "'",$pmv[$cmd_name][0]).'"'
								:'');
							$csvRow.= ",";
						}

					$csvRow.= "\n";

				echo iconv("UTF-8", "ISO-8859-2", $csvRow);

				endwhile;

			endif;

			wp_reset_postdata();
		}

	// Activate EventON Product
		// validate the license key	
			function validate_license(){
				global $eventon;
				$key = $_POST['key'];
				$verifyformat = $eventon->evo_updater->product->purchase_key_format($key, (isset($_POST['type'])? $_POST['type']:'') );

				$return_content = array(
					'status'=>($verifyformat?'good':'bad'),
					'error_msg'=>(!$verifyformat? $eventon->evo_updater->error_code_('10'):''),
				);
				echo json_encode($return_content);		
				exit;
			}
		// get API data
			function get_license_api_url(){
				global $eventon;
				
				$__passing_instance = (!empty($_POST['instance'])?(int)$_POST['instance']:'1');
				$data = array(
					'type'=>(!empty($_POST['type'])?$_POST['type']:'main'),
					'slug'=> addslashes ($_POST['slug']),
					'key'=> addslashes( str_replace(' ','',$_POST['key']) ),
					'email'=>(!empty($_POST['email'])? $_POST['email']: null),
					'product_id'=>(!empty($_POST['product_id'])?$_POST['product_id']:''),						
					'instance'=>$__passing_instance,
				);					

				echo json_encode( array('json_url'=> $eventon->evo_updater->get_api_url($data) ));
				exit;
			}
		// verify license key
			function verify_key(){
				global $eventon;

				// initial values
					$debug = $content = $addition_msg ='';
					$status = 'success';
					$error_code = '00';
					$error_msg='';

				// passing data
					$__passing_instance = (!empty($_POST['instance'])?(int)$_POST['instance']:'1');
					$__data = array(
						'slug'=> addslashes($_POST['slug']),
						'key'=> addslashes( str_replace(' ','',$_POST['key']) ),
						'email'=>(!empty($_POST['email'])? $_POST['email']: null),
						'product_id'=>(!empty($_POST['product_id'])?$_POST['product_id']:''),
						'instance'=>$__passing_instance,
					);

				// for eventon
				if($_POST['slug']=='eventon'){
					$api_url = $eventon->evo_updater->get_api_url($__data);		
					$__save_new_lic = $eventon->evo_updater->product->save_license(
						$__data['slug'],
						$__data['key']
					);
					$return_content = array(
						'status'=>$status,
						'error_msg'=>$eventon->evo_updater->error_code_($error_code),
						'addition_msg'=>$addition_msg,
						'json_url'=>$api_url,
					);
				// Addons
				}else{
					$status_ = $eventon->evo_updater->verify_product_license($__data);
					//content for success activation
						$content ="License Status: <strong>Activated</strong>";

					// save verified eventon addon product info
						$__save_new_lic = $eventon->evo_updater->product->save_license(
							$__data['slug'],
							$__data['key'],
							$__data['email'],
							$__data['product_id'],
							'valid','', (!empty($status_->instance)? $status_->instance:'1')
						);

					// CHECK remote validation results
					if($status_){
						// if activated value is true
						if($status_->activated){							
							$status = 'success';

							// append additional mesages passed from remote server
							$addition_msg = !empty($status_->message)? $status_->message:null;

						}else{ // return activated to be not true
							// if there were errors returned from eventon server
							if(!empty($status_->code) && $status_->code=='103' && $__passing_instance=='1'){
								$status = 'success';
								$error_code = '12';
							}elseif(!empty($status_->code) && $status_->code=='103'){
								$status = 'bad';
								$error_code = '103'; //exceeded max activations
							}else{
								$status = 'success';
								$error_code = '13'; //general validation failed
							}				
						}
					}else{ // couldnt connect to myeventon.com to check
						$status = 'good';
						$error_code = '13';							
					}
					$return_content = array(
						'status'=>$status,
						'error_msg'=>$eventon->evo_updater->error_code_($error_code),
						'addition_msg'=>$addition_msg,
						'this_content'=>$content,
						'extra'=>$status_,
					);
				}
				
				echo json_encode($return_content);		
				exit;				
			}

			function check_addon_verification(){}
			
		// update remote validity status of a license
			function remote_validity(){
				global $eventon;

				$new_content = '';

				// EventON update remote validity
				if($_POST['slug'] == 'eventon'){
					$eventon->evo_updater->eventon_kriyathmaka_karanna();
					if(!empty($_POST['buyer'])) 
						$eventon->evo_updater->product->update_field($_POST['slug'], 'buyer', $_POST['buyer']);

					$new_content = '';
				}

				$remote_validity = !empty($_POST['remote_validity'])? 'valid':'';
				$status = $eventon->evo_updater->product->update_field($_POST['slug'], 'remote_validity',$remote_validity );

				if(!empty($_POST['key'])) $eventon->evo_updater->product->update_field($_POST['slug'], 'key',$_POST['key'] );

				$return_content = array(
					'status'=>($status?'good':'bad'),	
					'new_content'=>	$new_content		
				);
				echo json_encode($return_content);		
				exit;
			}
		// deactivate addon 
			function deactivate_addon(){
				global $eventon;

				// initial values
					$debug = $content ='';
					$status = 'success';
					$error_code = '00';
					$error_msg='';

				// deactivate the license locally
				$dea_local = $eventon->evo_updater->product->deactivate($_POST['slug']);
				
				// passing data
					$__data = array(
						'slug'=> addslashes ($_POST['slug']),
						'key'=> addslashes( str_replace(' ','',$_POST['key']) ),
						'email'=>(!empty($_POST['email'])? $_POST['email']: null),
						'product_id'=>(!empty($_POST['product_id'])? $_POST['product_id']: null),
					);

				// deactivate addon from remote server
					$url='http://www.myeventon.com/woocommerce/?wc-api=software-api&request=deactivation&email='.$__data['email'].'&licence_key='.$__data['key'].'&instance=0&product_id='.$__data['product_id'];

					$request = wp_remote_get($url);

					if (!is_wp_error($request) && $request['response']['code']===200) {

						$status_ = (!empty($request['body']))? json_decode($request['body']): $request; 
					}
				
				$return_content = array(
					'status'=>$status,					
					'extra'=>$status_,
					'error_msg'=>$eventon->evo_updater->error_code_($error_code),
					'content'=>"License Status: <strong>Deactivated</strong>"
				);
				echo json_encode($return_content);		
				exit;
			}

	// deactivate eventon license
		function eventon_deactivate_evo(){
			global $eventon;
			$error_msg ='';

			$status = $eventon->evo_updater->product->deactivate('eventon');

			if($status)	$status = 'success';
			else	$error_msg = $eventon->evo_updater->error_code_();

			$return_content = array(
				'status'=>$status,		
				'error_msg'=>$error_msg
			);
			echo json_encode($return_content);		
			exit;
		}

	/** Feature an event from admin */
		function eventon_feature_event() {

			if ( ! is_admin() ) die;

			if ( ! current_user_can('edit_eventons') ) wp_die( __( 'You do not have sufficient permissions to access this page.', 'eventon' ) );

			if ( ! check_admin_referer('eventon-feature-event')) wp_die( __( 'You have taken too long. Please go back and retry.', 'eventon' ) );

			$post_id = isset( $_GET['eventID'] ) && (int) $_GET['eventID'] ? (int) $_GET['eventID'] : '';

			if (!$post_id) die;

			$post = get_post($post_id);

			if ( ! $post || $post->post_type !== 'ajde_events' ) die;

			$featured = get_post_meta( $post->ID, '_featured', true );

			if ( $featured == 'yes' )
				update_post_meta($post->ID, '_featured', 'no');
			else
				update_post_meta($post->ID, '_featured', 'yes');

			wp_safe_redirect( remove_query_arg( array('trashed', 'untrashed', 'deleted', 'ids'), wp_get_referer() ) );
		}

	// get all addon details
		public function get_addons_list(){

			// verifications
			if(!is_admin()) return false;

			require_once('settings/addon_details.php');

			$activePlugins = get_option( 'active_plugins' );
			$products = get_option('_evo_products');

			ob_start();
			// installed addons		

				$count=1;
				// EACH ADDON
				foreach($addons as $slug=>$product){
					if($slug=='eventon') continue; // skip for eventon
					$_has_addon = false;
					$_this_addon = (!empty($products[$slug]))? $products[$slug]:$product;

					// check if the product is activated within wordpress
					if(!empty($activePlugins)){
						foreach($activePlugins as $plugin){
							// check if foodpress is in activated plugins list
							if(strpos( $plugin, $slug.'.php') !== false){
								$_has_addon = true;
							}
						}
					}else{	$_has_addon = false;	}
								
					// initial variables
						$guide = ($_has_addon && !empty($_this_addon['guide_file']) )? "<span class='eventon_guide_btn ajde_popup_trig' ajax_url='{$_this_addon['guide_file']}' poptitle='How to use {$product['name']}'>Guide</span> | ":null;
						
						$__action_btn = (!$_has_addon)? "<a class='evo_admin_btn btn_secondary' target='_blank' href='". $product['download']."'>Get it now</a>": "<a class='ajde_popup_trig evo_admin_btn btn_prime' dynamic_c='1' content_id='eventon_pop_content_{$slug}' poptitle='Activate {$product['name']} License'>Activate Now</a>";

						//$__remote_version = (!empty($_this_addon['remote_version']))? '<span title="Remote server version"> /'.$_this_addon['remote_version'].'</span>': false;

						
						
					// ACTIVATED
					if(!empty($_this_addon['status']) && $_this_addon['status']=='active' && $_has_addon):
					
					?>
						<div id='evoaddon_<?php echo $slug;?>' class="addon activated" data-slug='<?php echo $slug;?>' data-key='<?php echo $_this_addon['key'];?>' data-email='<?php echo $_this_addon['email'];?>' data-product_id='<?php echo $product['id'];?>'>
							<h2><?php echo $product['name']?></h2>
							<p class='version'><span><?php echo $_this_addon['version']?></span></p>
							<p class='status'>License Status: <strong>Activated</strong></p>
							<p><a class='evo_deact_adodn ajde_popup_trig evo_admin_btn btn_triad' dynamic_c='1' content_id='eventon_pop_content_dea_<?php echo $slug;?>' poptitle='Deactivate <?php echo $product['name'];?> License'>Deactivate</a></p>
							<p class="links"><?php echo $guide;?><a href='<?php echo $product['link'];?>' target='_blank'>Learn More</a></p>
								<div id='eventon_pop_content_dea_<?php echo $slug;?>' class='evo_hide_this'>
									<p class="evo_loader"></p>
								</div>
						</div>
					
					<?php	
						// NOT ACTIVATED
						else:
					?>
						<div id='evoaddon_<?php echo $slug;?>' class="addon <?php echo (!$_has_addon)?'donthaveit':null;?>" data-slug='<?php echo $slug;?>' data-key='<?php echo !empty($_this_addon['key'])?$_this_addon['key']:'';?>' data-email='<?php echo !empty($_this_addon['email'])?$_this_addon['email']:'';?>' data-product_id='<?php echo !empty($product['id'])? $product['id']:'';?>'>
							<h2><?php echo $product['name']?></h2>
							<?php if(!empty($_this_addon['version'])):?><p class='version'><span><?php echo $_this_addon['version']?></span></p><?php endif;?>
							<p class='status'>License Status: <strong>Not Activated</strong></p>
							<p class='action'><?php echo $__action_btn;?></p>
							<p class="links"><?php echo $guide;?><a href='<?php echo $product['link'];?>' target='_blank'>Learn More</a></p>
							<p class='activation_text'></p>
								<div id='eventon_pop_content_<?php echo $slug;?>' class='evo_hide_this'>
									<p>Addon License Key: * <br/>
									<input class='eventon_license_key_val' type='text' style='width:100%' placeholder='Enter the addon license key'/>
									<input class='eventon_slug' type='hidden' value='<?php echo $slug;?>' />
									<input class='eventon_id' type='hidden' value='<?php echo $product['id'];?>' />
									<input class='eventon_license_div' type='hidden' value='evoaddon_<?php echo $slug;?>' /></p>

									<p>Email Address: * <span class='evoGuideCall'>?<em>This must be the email address you used to purchase eventon addon from myeventon.com</em></span><br/><input class='eventon_email_val' type='text' style='width:100%' placeholder='Email address used for purchasing addon'/></p>
									
									<p>Site Instance <span class='evoGuideCall'>? <em>If your license allow more than one site activations, please select which site you are activating now eg. 2 - for 2nd website, 3 - for 3rd website etc. Leave blank for one activations</em></span><br/><input class='eventon_index_val' type='text' style='width:100%'/></p>

									<p><a class='eventonADD_submit_license evo_admin_btn btn_prime' data-type='addon' data-slug='<?php echo $slug;?>'>Activate Now</a></p>
								</div>
						</div>
					<?php		
						endif;
						$count++;
				} //endforeach

			$content = ob_get_clean();

			$return_content = array(
				'content'=> $content,
				'status'=>true
			);
			
			echo json_encode($return_content);		
			exit;	
		}

	// remote text
		public function remote_test(){
			global $wp_version, $eventon;
		
			$args = array('slug' => $_POST['slug']);
			$request_string = array(
				'body' => array(
					'action' => 'evo_latest_version', 
					'request' => serialize($args),
					'api-key' => md5(get_bloginfo('url'))
				),
				'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
			);				
		
	        $request = wp_remote_post($eventon->evo_updater->api_url, $request_string);
	        if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
	            $version = $request['body'];
	        }else{
	        	// get locally saved remote version
    			$version = $eventon->evo_updater->product->get_remote_version();
	        }

	        $return_content = array(
				'status'=>'good',		
				'api_url'=>$eventon->evo_updater->api_url,
				'version'=>$request,
			);
			echo json_encode($return_content);		
			exit;
		}
}
new EVO_admin_ajax();