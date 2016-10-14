<?php
/**
 * Functions for the settings page in admin.
 *
 * The settings page contains options for the EventON plugin - this file contains functions to display
 * and save the list of options.
 *
 * @author 		AJDE
 * @category 	Admin
 * @package 	EventON/Admin/Settings
 * @version     2.2.28
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/** Store settings in this array */
global $eventon_settings;

if ( ! function_exists( 'eventon_settings' ) ) {
	
	// Settings page
	function eventon_settings() {
		global $eventon, $ajde;
		
		do_action('eventon_settings_start'); 
				
		// Settings Tabs array
		$evcal_tabs = apply_filters('eventon_settings_tabs',array(
			'evcal_1'=>__('Settings', 'eventon'), 
			'evcal_2'=>__('Language', 'eventon'),
			'evcal_3'=>__('Styles', 'eventon'),
			'evcal_4'=>__('Licenses', 'eventon'),
			'evcal_5'=>__('Troubleshoot', 'eventon'),
		));		
		
		// Get current tab/section
			$focus_tab = (isset($_GET['tab']) )? sanitize_text_field( urldecode($_GET['tab'])):'evcal_1';
			$current_section = (isset($_GET['section']) )? sanitize_text_field( urldecode($_GET['section'])):'';	

		// Update or add options
			if( isset($_POST['evcal_noncename']) && isset( $_POST ) ){
				if ( wp_verify_nonce( $_POST['evcal_noncename'], AJDE_EVCAL_BASENAME ) ){
					
					foreach($_POST as $pf=>$pv){
						if( ($pf!='evcal_styles' && $focus_tab!='evcal_4') || $pf!='evcal_sort_options'){
							
							$pv = (is_array($pv))? $pv: addslashes(esc_html(stripslashes(($pv)))) ;
							$evcal_options[$pf] = $pv;
						}
						if($pf=='evcal_sort_options'){
							$evcal_options[$pf] =$pv;
						}					
					}

					
					// General settings page - write styles to head option
					if($focus_tab=='evcal_1' && isset($_POST['evcal_css_head']) && $_POST['evcal_css_head']=='yes'){

						ob_start();
						include(AJDE_EVCAL_PATH.'/assets/css/dynamic_styles.php');
						$evo_dyn_css = ob_get_clean();						
						update_option('evo_dyn_css', $evo_dyn_css);
					}
					
					
					//language tab
					if($focus_tab=='evcal_2'){
						$new_lang_opt ='';
						$_lang_version = (!empty($_GET['lang']))? $_GET['lang']: 'L1';

						$lang_opt = get_option('evcal_options_evcal_2');
						if(!empty($lang_opt) ){
							$new_lang_opt[$_lang_version] = $evcal_options;
							$new_lang_opt = array_merge($lang_opt, $new_lang_opt);

						}else{
							$new_lang_opt[$_lang_version] =$evcal_options;
						}
						
						update_option('evcal_options_evcal_2', $new_lang_opt);
						
					}elseif($focus_tab == 'evcal_1' || empty($focus_tab)){
						// store custom meta box count
						$cmd_count = evo_calculate_cmd_count();
						$evcal_options['cmd_count'] = $cmd_count;

						update_option('evcal_options_'.$focus_tab, $evcal_options);

					// all other settings tabs
					}else{	
						update_option('evcal_options_'.$focus_tab, $evcal_options);
					}
					
					// STYLES
					if( isset($_POST['evcal_styles']) )
						update_option('evcal_styles', strip_tags(stripslashes($_POST['evcal_styles'])) );
					
					$_POST['settings-updated']='true';			
				

					eventon_generate_options_css();

				// nonce check
				}else{
					die( __( 'Action failed. Please refresh the page and retry.', 'eventon' ) );
				}	
			}
			
		// Load eventon settings values for current tab
			$current_tab_number = substr($focus_tab, -1);		
			if(!is_numeric($current_tab_number)){ // if the tab last character is not numeric then get the whole tab name as the variable name for the options 
				$current_tab_number = $focus_tab;
			}
		
			$evcal_opt[$current_tab_number] = get_option('evcal_options_'.$focus_tab);			
		
		// activation notification
			if(!$eventon->evo_updater->kriyathmakada()){
				echo '<div class="update-nag">'.__('EventON is not activated, it must be activated to use! <a href="'.get_admin_url().'admin.php?page=eventon&tab=evcal_4">Enter License Now</a>','eventon').'</div>';
			}

		// OTHER options
			$genral_opt = get_option('evcal_options_evcal_1');

// TABBBED HEADER		
?>
<div class="wrap" id='evcal_settings'>
	<h2><?php _e('EventON Settings','eventon')?> (ver <?php echo get_option('eventon_plugin_version');?>) <?php do_action('eventon_updates_in_settings');?></h2>
	<h2 class='nav-tab-wrapper' id='meta_tabs'>
		<?php					
			foreach($evcal_tabs as $nt=>$ntv){
				$evo_notification='';
				
				echo "<a href='?page=eventon&tab=".$nt."' class='nav-tab ".( ($focus_tab == $nt)? 'nav-tab-active':null)." {$nt}' evcal_meta='evcal_{$nt}'>".$ntv.$evo_notification."</a>";
			}			
		?>		
	</h2>	
<div class='evo_settings_box <?php echo (!empty($genral_opt['evo_rtl']) && $genral_opt['evo_rtl']=='yes')?'adminRTL':'';?>'>	
<?php
// SETTINGS SAVED MESSAGE
	$updated_code = (isset($_POST['settings-updated']) && $_POST['settings-updated']=='true')? '<div class="updated fade"><p>'.__('Settings Saved','eventon').'</p></div>':null;
	echo $updated_code;	
	
// TABS
switch ($focus_tab):	
	case "evcal_1":		
		// Event type custom taxonomy NAMES
		$event_type_names = evo_get_ettNames($evcal_opt[1]);
		$evt_name = $event_type_names[1];
		$evt_name2 = $event_type_names[2];

		?>
		<form method="post" action=""><?php settings_fields('evcal_field_group'); 
			wp_nonce_field( AJDE_EVCAL_BASENAME, 'evcal_noncename' );
		?>
		<div id="evcal_1" class=" evcal_admin_meta evcal_focus">
			<div class="evo_inside">
				<?php
					
					require_once(AJDE_EVCAL_PATH.'/includes/admin/settings/class-settings-settings.php');
					$settings = new evo_settings_settings($evcal_opt);
					
					$ajde->load_ajde_backender();
					print_ajde_customization_form($settings->content(), $evcal_opt[1]);
					
				?>
			</div>	
		</div>
		<div class='evo_diag'>			
			
	
			<!-- save settings -->
			<input type="submit" class="evo_admin_btn btn_prime" value="<?php _e('Save Changes') ?>" /> <a id='resetColor' style='display:none' class='evo_admin_btn btn_secondary'><?php _e('Reset to default colors','eventon')?></a><br/><br/>
			<a target='_blank' href='http://www.myeventon.com/support/'><img src='<?php echo AJDE_EVCAL_URL;?>/assets/images/myeventon_resources.png'/></a>
		</div>		
		</form>

		<div class="evo_lang_export">
			<?php
				$nonce = wp_create_nonce('evo_export_settings');
				// url to export settings
				$exportURL = add_query_arg(array(
				    'action' => 'eventon_export_settings',
				    'nonce'=>$nonce
				), admin_url('admin-ajax.php'));

			?>
			<h3><?php _e('Import/Export General EventON Settings','eventon');?></h3>
			<p><i><?php _e('NOTE: Make sure to save changes after importing. This will import/export the general settings saved for eventon.','eventon');?></i></p>

			<div class='import_box' id="import_box" style='display:none'>
				<span id="close">X</span>
				<form id="evo_settings_import_form" action="" method="POST" data-link='<?php echo AJDE_EVCAL_PATH;?> '>
					<input type="file" id="file-select" name="settings[]" multiple accept=".json" />
					<button type="submit" id="upload_settings_button"><?php _e('Upload','eventon');?></button>
				</form>
				<p class="msg" style='display:none'><?php _e('File Uploading','eventon');?></p>
			</div>
			<p>
				<a id='evo_settings_import' class='evo_admin_btn btn_triad'><?php _e('Import','eventon');?></a> 
				<a href='<?php echo $exportURL;?>' class='evo_admin_btn btn_triad'><?php _e('Export','eventon');?></a>
			</p>
		</div>

		
	
<?php  
	break;
		
	// LANGUAGE TAB
	case "evcal_2":		
			
		require_once(AJDE_EVCAL_PATH.'/includes/admin/settings/settings_language_tab.php');

		$settings_lang = new evo_settings_lang($evcal_opt);
		$settings_lang->get_content();
	
	break;
	
	// STYLES TAB
	case "evcal_3":
		
		echo '<form method="post" action="">';
		
		//settings_fields('evcal_field_group'); 
		wp_nonce_field( AJDE_EVCAL_BASENAME, 'evcal_noncename' );
				
		// styles settings tab content
		require_once(AJDE_EVCAL_PATH.'/includes/admin/settings/settings_styles_tab.php');
	
	break;
	
	// ADDON TAB
	case "evcal_4":
		
		// Addons settings tab content
		require_once(AJDE_EVCAL_PATH.'/includes/admin/settings/settings_addons_tab.php');

	
	break;
	
	// support TAB
	case "evcal_5":
		
		// Addons settings tab content
		require_once(AJDE_EVCAL_PATH.'/includes/admin/settings/settings_troubleshoot_tab.php');

	
	break;
	
	
		
	// ADVANDED extra field
	case "extra":
	
	// advanced tab content
	require_once(AJDE_EVCAL_PATH.'/includes/admin/settings/settings_advanced_tab.php');		
	
	break;
	
		default:
			do_action('eventon_settings_tabs_'.$focus_tab);
		break;
		endswitch;
		
		echo "</div>";
	}
} // * function exists 

?>