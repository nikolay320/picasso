<?php

//--Pages by user role-----------------------------------------------------
if(!defined('PUR_PATH')):
define('PUR_VERSION','1.1.4'); 
define('PUR_PATH', dirname( __FILE__ ). "/pages-by-user-role/" ); 
define("PUR_URL", get_bloginfo('stylesheet_directory') . "/pages-by-user-role/" );

require_once(PUR_PATH.'pages-by-user-role-theme.php');
global $pur_plugin;
$settings = array(
	'options_parameters'=>array(
		'option_menu_parent'	=> 'plugins.php',
		'menu_text'				=> 'PUR Options'
	)
);
$pur_plugin=new plugin_pur($settings);
$pur_plugin->plugins_loaded();
endif;
//--------------------------------------------------------------------------
?>