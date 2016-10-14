<?php
/**
 * 
 * Admin section for search
 *
 * @author 		AJDE
 * @category 	Admin
 * @package 	eventon-sr/classes
 * @version     0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class evosr_admin{
	
	public $optRS;
	function __construct(){
		add_filter('eventon_settings_lang_tab_content', array( $this, 'language' ), 10, 1);	
		add_filter('eventon_settings_tab1_arr_content', array( $this, 'search_settings' ) ,10,1 );	
	}
	
	// language
		function language($_existen){
			$new_ar = array(
				array('type'=>'togheader','name'=>'ADDON: Search'),				
				
				array('label'=>'Search Events','name'=>'evoSR_001','legend'=>'placeholder for search input fields'),
				array('type'=>'togend'),
			);
			return (is_array($_existen))? array_merge($_existen, $new_ar): $_existen;
		}

	/**
	 * Settings page content for search
	 * @param  $array 
	 * @return
	 */
		function search_settings($array){

			ob_start();?>

				<p>By default search icon and search bar are set to visible in all calendars, once you activate EventON Search.
				<br/>
				You can <strong>disable search</strong> by adding the before variable into shortcodes:
				<br/>
				<br/>
				<code>search="no"</code> example within a shortcode <code>[add_eventon search="no"]</code>
				<br/>
				<br/>
				The placeholder text that shows in the search bar can be edited from <strong>language</strong>.
				</p>


			<?php $content = ob_get_clean();
			
			$new_array = $array;
			
			$new_array[]= array(
				'id'=>'eventon_search',
				'name'=>'Settings & Instructions for Event Search',
				'display'=>'none',
				'tab_name'=>'Search Events',
				'fields'=> apply_filters('evo_se_setting_fields', array(
					array('id'=>'evo_sr_001','type'=>'customcode',
							'code'=>$content),

				)
			));
			
			return $new_array;
		}

}
new evosr_admin();
