<?php
add_action( 'admin_menu', 'change_cpt_add_admin_menu' );

function change_cpt_add_admin_menu(  ) { 

	add_menu_page( 'Change CPT', 'Change CPT', 'manage_options', 'changecpt', 'change_cpt_options_page' );

}

function change_cpt_options_page(  ) { 

	require_once('template-plugin-settings.php');

}