<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/
class plugin_mur {
	var $is_user_logged_in=false;
	var $locations=array();
	var $num_locations=false;
	var $logged_user_role=false;
	var $menu_tree = array();
	function plugin_mur(){
		if(true){
			add_action('init',array(&$this,'init'),9999);
			add_filter('wp_nav_menu_args',array(&$this,'wp_nav_menu_args'),9999,1);		
		}
		//add_action('parse_request',array(&$this,'admin_init'));	
		add_action('admin_head-nav-menus.php',array(&$this,'admin_init'));
		
	}
	
	function admin_init(){
		wp_enqueue_script('jquery-ui-tabs');
		$this->replace_metabox();
?>
<style>
.menu-location-main-label {
	color:#666666;
	font-family:sans-serif;
	font-style:italic;
}
.tabbed-options {
	line-height: 1.4em;
	color: #333333;
	margin-bottom:10px;
}
.tabbed-options-tabs {
	margin:12px 0 0 0;
	list-style: none outside none;
	padding:0;
	line-height: 1.4em;
	color:#333333;
}

.tabbed-options-tabs li a {
	text-decoration:none;
	color: #21759B;
	outline: 0 none;
}

.tabbed-options-tabs li{
	padding: 3px 5px 2px;	
	display: inline;
    line-height: 1.35em;	
	margin-bottom: 1px;
	list-style: none outside none;
}

.tabbed-options-tabs li.ui-state-active  {
	background-color: #ffffff;
	border-color:#dfdfdf;
	border-top-left-radius: 3px;
    border-top-right-radius: 3px;
    
	border-style: solid solid none;
    border-width: 1px 1px 0;	

}

.tabbed-option-tab-content {
	border-style: solid;
	border-width: 1px;
	overflow: auto;
	padding: 0.5em 0.9em;
	background-color: #FFFFFF;
    border-color: #DFDFDF;	
	line-height: 1.4em;	
}
</style>
<script>
jQuery(document).ready(function($){
	$('.tabbed-options').tabs();
});
</script>
<?php
	}
	
//	add_filter( 'manage_nav-menus_columns', 'wp_nav_menu_manage_columns');
	function replace_metabox(){
		//if ( wp_get_nav_menus() )
			add_meta_box( 'nav-menu-theme-locations', __( 'Theme Locations' ), array(&$this,'wp_nav_menu_locations_meta_box') , 'nav-menus', 'side', 'default' );	
	}
	
	function init(){
		$this->is_user_logged_in = is_user_logged_in();		
		$this->logged_user_role = $this->get_logged_user_role();
		//------------
		$this->derived_locations();
		//NOTE: this line should go after $this->derived_locations always:
		$this->nav_menu_locations = get_nav_menu_locations();
		//------------
	}
	
	function derived_locations(){
		//if ( ! current_theme_supports( 'menus' ) ) return;
		$label_format = "%s&nbsp;(%s)";

		$menus = wp_get_nav_menus();
		$this->locations = $locations = get_registered_nav_menus();
		$menu_locations = get_nav_menu_locations();
		$this->num_locations = count( array_keys($this->locations) );
		$WP_Roles = new WP_Roles();
		$role_names = $WP_Roles->get_names();		
		
		if(is_array($locations)&&count($locations)>0){
			$derived_locations = array();
			foreach($locations as $location_slug => $location_label){
				$derived_locations[$location_slug] = $location_label;
				$derived_locations[$location_slug.'_logged_default'] = sprintf($label_format,$location_label,__('default logged in','mur'));
				$this->menu_tree[$location_slug]['public'] = $location_slug;
				$this->menu_tree[$location_slug]['logged'] = $location_slug.'_logged_default';
				foreach($role_names as $role_slug => $role_label ){
					$derived_locations[$location_slug.'_'.$role_slug] = sprintf($label_format,$location_label,$role_label);		
					if( is_array($this->menu_tree[$location_slug]['byrole']) ){
						$this->menu_tree[$location_slug]['byrole'][] = $location_slug.'_'.$role_slug;
					}else{
						$this->menu_tree[$location_slug]['byrole'] = array($location_slug.'_'.$role_slug);
					} 
				}
			}
			$derived_locations = apply_filters('derived_menu_locations',$derived_locations);
			if(!empty($derived_locations)){
				//--- unset all menus.
				global $_wp_registered_nav_menus;
				$_wp_registered_nav_menus = array();
				//---
				foreach($derived_locations as $id => $label){
					register_nav_menu( $id, $label );
				}
			}
		}
	}
	
	function wp_nav_menu_args($args){
		if(false===$this->is_user_logged_in){
			return $args;
		}else{
			$role_slug = $this->logged_user_role;
			$nav_menu_locations = $this->nav_menu_locations;
			$derived_menu_location = false===$role_slug?$args['theme_location']:$args['theme_location'].'_'.$role_slug;
			$default_logged_location = $args['theme_location'].'_logged_default';
		
			if( isset($nav_menu_locations[$derived_menu_location]) && $nav_menu_locations[$derived_menu_location]>0 ){
				$args['theme_location'] = $derived_menu_location;
			}else if( isset( $nav_menu_locations[$default_logged_location] ) && $nav_menu_locations[$default_logged_location]>0 ){
				$args['theme_location'] = $default_logged_location;
			}
			return $args;
		}
	}
	
	function wp_nav_menu_locations_meta_box() {
		/* a replacement for the one at wp-admin/includes/nav-menu.php */
		global $nav_menu_selected_id;
	
		if ( ! current_theme_supports( 'menus' ) ) {
			// We must only support widgets. Leave a message and bail.
			echo '<p class="howto">' . __('The current theme does not natively support menus, but you can use the &#8220;Custom Menu&#8221; widget to add any menus you create here to the theme&#8217;s sidebar.') . '</p>';
			return;
		}
		
		$locations = get_registered_nav_menus();
		$menus = wp_get_nav_menus();
		$menu_locations = get_nav_menu_locations();
		$num_locations = $this->num_locations? $this->num_locations : count( array_keys($locations) );

		$tab_labels = array(
			'public'=> __('Public','mur'),
			'logged'=> __('Logged in','mur'),
			'byrole'=> __('By User Role','mur')
		);	
		
		echo '<p class="howto">' . sprintf( _n('Your theme supports %s menu. Select which menu you would like to use.', 'Your theme supports %s menus. Select which menu appears in each location.', $num_locations ), number_format_i18n($num_locations) ) . '</p>';
			
		if( is_array($this->menu_tree) && count($this->menu_tree)>0 ){
			foreach($this->menu_tree as $slug => $sets){
				$tabs = array();
				$sections = array();
				foreach($sets as $tab_slug => $set_locations){
					$tab_id = 'tab_'.$slug.'_'.$tab_slug;
					$tab_id = str_replace(' ','_',$tab_id);
					$tabs[]='<li><a href="#'.$tab_id.'">'.$tab_labels[$tab_slug].'</a></li>';
					$content = '';
					
					$items = is_array($set_locations)?$set_locations:array($set_locations);
					foreach($items as $location){
						$description = $locations[$location];
						ob_start();
?>
			<p>
				<label class="howto" for="locations-<?php echo $location; ?>">
					<span class="menu-location-main-label"><?php echo $description; ?></span>
					<select name="menu-locations[<?php echo $location; ?>]" id="locations-<?php echo $location; ?>">
						<option value="0"></option>
						<?php foreach ( $menus as $menu ) : ?>
						<option<?php selected( isset( $menu_locations[ $location ] ) && $menu_locations[ $location ] == $menu->term_id ); ?>
							value="<?php echo $menu->term_id; ?>"><?php
							$truncated_name = wp_html_excerpt( $menu->name, 40 );
							echo $truncated_name == $menu->name ? $menu->name : trim( $truncated_name ) . '&hellip;';
						?></option>
						<?php endforeach; ?>
					</select>
				</label>
			</p>
<?php						
						$content.= ob_get_contents();
						ob_end_clean();										
					}
					$sections[]='<div class="tabbed-option-tab-content" id="'.$tab_id.'">'.$content.'</div>';
				
				}
				
?>
		<div>
			<span class="menu-location-main-label"><?php echo $locations[$slug]?></span>	
			<div class="tabbed-options">
				<ul class="tabbed-options-tabs">
					<?php echo implode("\n",$tabs) ?>
				</ul>
				<?php echo implode("\n",$sections) ?>			
			</div>
		</div>
<?php
			}
		}

		?>
		<p class="button-controls">
			<img class="waiting" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />
			<?php submit_button( __( 'Save' ), 'primary', 'nav-menu-locations', false, disabled( $nav_menu_selected_id, 0, false ) ); ?>
		</p>
		<?php
	}	
	
	function get_logged_user_role(){
		global $current_user;
		get_currentuserinfo();
		if(is_array($current_user->roles) && isset($current_user->roles[0])){
			return $current_user->roles[0];
		}else{
			return false;	
		}
	}
}

?>