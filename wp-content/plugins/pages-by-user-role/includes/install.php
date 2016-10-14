<?php

function handle_pur_install() {
	$WP_Roles = new WP_Roles();

	$capabilities = array(
		'pur_options',
		'pur_license'
	);

	foreach ( $capabilities as $cap ) {
		$WP_Roles->add_cap( PUR_ADMIN_ROLE, $cap );
	}
}

function handle_pur_uninstall() {
	$WP_Roles = new WP_Roles();

	$capabilities = array(
		'pur_options',
		'pur_license'
	);

	foreach ( $capabilities as $cap ) {
		$WP_Roles->remove_cap( PUR_ADMIN_ROLE, $cap );
	}
}

?>