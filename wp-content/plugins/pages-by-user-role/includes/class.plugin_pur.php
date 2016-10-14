<?php
 
class plugin_pur {
	function __construct() {
		add_action( 'after_setup_theme' , array( $this, 'after_setup_theme' ) );
	}
	
	function after_setup_theme(){
		global $wp_version;

		if ( $wp_version < 3.3 ) {
			require_once PUR_PATH . 'includes/class.prewp33.FrontendPur.php';
		} else {
			require_once PUR_PATH . 'includes/class.FrontendPur.php';
		}

		new FrontendPur();

		require_once PUR_PATH.'includes/class.PurShortcodes.php';
		new PurShortcodes();		
		
		if( is_admin() ) {
			do_action('rh-php-commons');

			$settings = array(
				'id'                     => 'pur',
				'plugin_id'              => 'pur',
				'capability'             => 'pur_options',
				'capability_license'     => 'pur_license',
				'options_varname'        => 'pur_options',
				'menu_id'                => 'pur-options',
				'page_title'             => __( 'PUR Options', 'pur' ),
				'menu_text'              => __( 'PUR Options', 'pur' ),
				'option_menu_parent'     => 'options-general.php',
				'notification'           => (object) array(
					'plugin_version'         => PUR_VERSION,
					'plugin_code'            => 'PUR',
					'message'                => __( 'Pages by User Role update %s is available! <a href="%s">Please update now</a>', 'pur' )
				),
				'registration'           => true,
				'theme'                  => false,
				'stylesheet'             => 'pur-options',
				'option_show_in_metabox' => true,
				'path'                   => PUR_PATH . 'options-panel/',
				'url'                    => PUR_URL . 'options-panel/',
				'pluginslug'             => PUR_SLUG,
				'api_url'                => 'http://plugins.righthere.com',
				'layout'                 => 'horizontal',
			);

			// Options panel
			require_once PUR_PATH . 'options-panel/class.PluginOptionsPanelModule.php';
			new PluginOptionsPanelModule( $settings );

			require_once PUR_PATH.'includes/class.pur_settings.php';
			new pur_settings();
			
			require_once PUR_PATH.'includes/class.WP_Pur.php';
			new WP_Pur();	
			
			require_once PUR_PATH.'includes/class.PurCategory.php';
			new PurCategory();
		}
	}
}  

?>