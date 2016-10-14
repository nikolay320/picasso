<?php
function my_bp_core_get_user_domain($domain, $user_id, $user_nicename, $user_login) {
 if(get_current_user_role()=='Spectator' && bp_loggedin_user_id()!=$user_id)
 return '#';
 
 return $domain;
}
add_filter('bp_core_get_user_domain', 'my_bp_core_get_user_domain', 10, 4);
?>