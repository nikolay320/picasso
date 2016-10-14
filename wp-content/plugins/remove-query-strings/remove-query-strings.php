<?php
/*
Plugin Name: Remove Query String From CSS & Javascript In WordPress
Plugin URI: http://wpvkp.com/
Description: Remove query string from CSS & Javascript in WordPress websites. It also helps to improve your google page speed score, Yahoo YSlow score.
Author: designvkp
Version: 1.3
Author URI: http://wpvkp.com
*/
function rqs1( $src ){	
	$rqs = explode( '?ver', $src );
        return $rqs[0];
}
// Disabled On Admin Panel
		if ( is_admin() ) {
}

		else {
add_filter( 'script_loader_src', 'rqs1', 15, 1 );
add_filter( 'style_loader_src', 'rqs1', 15, 1 );
}

function rqs2( $src ){
	$rqs = explode( '&ver', $src );
        return $rqs[0];
}
// Disabled On Admin Panel
		if ( is_admin() ) {
}

		else {
add_filter( 'script_loader_src', 'rqs2', 15, 1 );
add_filter( 'style_loader_src', 'rqs2', 15, 1 );
}
?>
