<?php
/**
 * EventON WeeklyView Ajax Handlers
 *
 * Handles admin hook functions
 *
 * @author 		AJDE
 * @category 	Core
 * @package 	EventON-WV/admin/
 * @version     0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// INITIATE admin for weekly view
	function evoWV_admin_init(){

		add_filter( 'eventon_appearance_add', 'evoWV_appearance_settings' , 10, 1);
		add_filter( 'eventon_inline_styles_array','evoWV_dynamic_styles' , 1, 1);
		
		// language
		add_filter('eventon_settings_lang_tab_content', 'evoWV_language_additions', 10, 1);
	}
	add_action('admin_init', 'evoWV_admin_init');

// appearance settings
	function evoWV_appearance_settings($array){
		
		$new[] = array('id'=>'evoWV','type'=>'hiddensection_open','name'=>'WeeklyView Styles');
		$new[] = array('id'=>'evoWV','type'=>'fontation','name'=>'Weekly Section',
			'variations'=>array(
				array('id'=>'evoWV_1', 'name'=>'Background Color','type'=>'color', 'default'=>'D2988A'),
				array('id'=>'evoWV_2', 'name'=>'Text Color','type'=>'color', 'default'=>'ffffff'),
				array('id'=>'evoWV_2a', 'name'=>'Week Title Text Color','type'=>'color', 'default'=>'ffffff'),
				array('id'=>'evoWV_3', 'name'=>'Arrow Circle Color','type'=>'color', 'default'=>'ffffff'),			
				array('id'=>'evoWV_4', 'name'=>'Event Circle Color','type'=>'color', 'default'=>'ffffff'),			
				array('id'=>'evoWV_5', 'name'=>'Event Circle Font Color','type'=>'color', 'default'=>'A15F4F'),			
			)
		);
		
		$new[] = array('id'=>'evoWV','type'=>'hiddensection_close','name'=>'WeeklyView Styles');

		return array_merge($array, $new);
	}

// dynamic styles saving
	function evoWV_dynamic_styles($_existen){
		$new= array(
			array(
				'item'=>'.eventon_weeklyview',
				'multicss'=>array(
					array('css'=>'background-color:#$', 'var'=>'evoWV_1','default'=>'D2988A'),
					array('css'=>'color:#$', 'var'=>'evoWV_2','default'=>'ffffff')
				)						
			),array(
				'item'=>'.eventon_weeklyview p.evoWV_top',
				'multicss'=>array(
					array('css'=>'color:#$', 'var'=>'evoWV_2a','default'=>'ffffff')
				)						
			),array(
				'item'=>'.evoWV_days .evo_wv_day span.num_events',
				'multicss'=>array(
					array('css'=>'background-color:#$', 'var'=>'evoWV_4','default'=>'ffffff'),
					array('css'=>'color:#$', 'var'=>'evoWV_5','default'=>'A15F4F')
				)						
			),array(
				'item'=>'.eventon_weeklyview p.evoWV_top span i',
				'css'=>'color:#$', 'var'=>'evoWV_3','default'=>'ffffff'
			),array(
				'item'=>'.eventon_weeklyview p.evoWV_top span',
				'css'=>'border-color:#$', 'var'=>'evoWV_3','default'=>'ffffff'
			)
		);
		

		return (is_array($_existen))? array_merge($_existen, $new): $_existen;
	}

// language settings additinos
	function evoWV_language_additions($_existen){
		$new_ar = array(
			array('type'=>'togheader','name'=>'ADDON: Weekly View'),
				array('label'=>'Week View', 'name'=>'evoWV_001', 'legend'=>''),
				
			array('type'=>'togend'),
		);
		return (is_array($_existen))? array_merge($_existen, $new_ar): $_existen;
	}
