<?php
/**
 * AJDE Plugin Settings Library
 * @version 	1.5.9
 * @updated 	2016
 */

if(isset($GLOBALS['ajde'])) return;

class ajde{

	public $version = '1.5.9';

	public function __construct(){

		$this->path = plugin_dir_url( __FILE__ );

		// text domain for language translations
		$this->domain = 'ajde';

		if(is_admin()){
			include_once('ajde-wp-admin.php');
			$this->wp_admin = new ajde_wp_admin();

			add_action('init', array($this, 'register_scripts'));
			add_action('admin_enqueue_scripts', array($this, 'load_styles_scripts' ));
		}
	}

	// load styles and scripts for all wp-admin pages
		function load_styles_scripts(){
			if(!is_admin()) return;		

			wp_enqueue_script('shortcode_generator');

			$this->register_backender_scripts();
			$this->register_backender_styles();
			$this->wp_admin_styles();
		}

	// wp-admin styles and scripts
		public function wp_admin_styles(){
			wp_enqueue_script('backender_colorpicker');
			wp_enqueue_style('ajde_wp_admin');
			wp_enqueue_script('ajde_wp_admin');
		}

	// register scripts
		function register_scripts(){
			$this->register_backender_styles();
			$this->register_backender_scripts();
		}

	// backender
		public function load_ajde_backender(){	
			wp_enqueue_media();	

			wp_enqueue_style('ajde_backender_styles');
			wp_enqueue_style('colorpicker_styles');

			wp_enqueue_script('backender_colorpicker');
			wp_enqueue_script('ajde_backender_script');

			include_once('ajde_backender.php');			
		}
		// can be called from within pages
		public function load_styles_to_page(){
			wp_enqueue_style('ajde_backender_styles');
			wp_enqueue_style('colorpicker_styles');
			wp_enqueue_style('ajde_wp_admin');
		}
		public function register_backender_styles(){
			wp_register_style( 'ajde_backender_styles',$this->path.'ajde_backender_style.css');
			wp_register_style( 'colorpicker_styles',$this->path.'colorpicker/colorpicker_styles.css');
			wp_register_style( 'ajde_wp_admin',$this->path.'ajde-wp-admin.css');
			
		}
		public function register_backender_scripts(){
			wp_register_script('shortcode_generator',$this->path.'assets/shortcode_generator.js' ,array('jquery'),$this->version, true);
			wp_register_script('backender_colorpicker',$this->path.'colorpicker/colorpicker.js' ,array('jquery'),$this->version, true);
			wp_register_script('ajde_backender_script',$this->path.'ajde_backender_script.js', array('jquery', 'jquery-ui-core', 'jquery-ui-sortable'), $this->version, true );
			wp_register_script('ajde_wp_admin',$this->path.'ajde-wp-admin.js', array('jquery'), $this->version, true );
		}

		function load_colorpicker(){
			wp_enqueue_style('colorpicker_styles');
			wp_enqueue_script('backender_colorpicker');
		}
		function register_colorpicker(){
			wp_register_script('backender_colorpicker',$this->path.'colorpicker/colorpicker.js' ,array('jquery'),$this->version, true);
			wp_register_style( 'colorpicker_styles',$this->path.'colorpicker/colorpicker_styles.css');
		}
}
$GLOBALS['ajde'] = new ajde();