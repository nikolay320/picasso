<?php
/*
Plugin Name: Dashboard Commander
Plugin URI: http://www.warpconduit.net/wordpress-plugins/dashboard-commander/
Description: Command your admin dashboard. Manage built-in widgets and dynamically registered widgets. Hide widgets depending upon user capabilities. Plugin is based upon Dashboard Heaven by Dave Kinkead.
Version: 1.0.3
Author: Josh Hartman
Author URI: http://www.warpconduit.net
License: GPL2
*/
/*
    Copyright 2014 Josh Hartman
    
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.
    
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

add_action('wp_dashboard_setup', 'dcmd_wp_dashboard_setup', 99);
add_action('admin_init', 'dcmd_admin_init');
add_action('admin_menu', 'dcmd_admin_menu');
add_action('admin_notices', 'dcmd_admin_notices');
add_filter("plugin_action_links_".plugin_basename(__FILE__), 'dcmd_plugin_settings_link' );

register_deactivation_hook(__FILE__, 'dcmd_deactivate');

/**
 *	Display admin notifications
 * 	@args	none
 *	@return	string
 */
function dcmd_admin_notices(){
	$widgets = get_option('dcmd_dashboard_widgets');
	if(!$widgets){	
		echo sprintf('<div class="updated"><p>%s</p></div>', 'To complete the installation of <strong>Dashboard Commander</strong> you must <a href="'.get_admin_url().'index.php"/><strong>visit your dashboard</strong></a> once and then go to <strong>Settings > Dashboard Commander</strong> to configure who has access to each widget.');
	}
}

/**
 *	Initialize plugin
 * 	@args	none
 *	@return	void
 */
function dcmd_admin_init() {
	$widgets =  get_option('dcmd_dashboard_widgets');
	if($widgets){
		foreach($widgets as $widget) {
			register_setting('dcmd_options', 'dcmd_'.$widget['id']);
		}
	}
}

/**
 *	Display Settings link on Plugins admin page
 * 	@args	array
 *	@return	array
 */
function dcmd_plugin_settings_link($links) { 
  $settings_link = '<a href="options-general.php?page='.plugin_basename(__FILE__).'">Settings</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
}

/**
 *	Deactivate plugin
 * 	@args	none
 *	@return void
 */
function dcmd_deactivate() {
	$widgets = get_option('dcmd_dashboard_widgets');
	foreach($widgets as $widget) {
		delete_option('dcmd_'.$widget['id']);
	}
	delete_option('dcmd_dashboard_widgets');
}

/**
 *	Loop through the dashboard widgets and remove depending on current user capabilities
 * 	@args	none
 *	@return void
 */
function dcmd_wp_dashboard_setup() {
	global $wp_meta_boxes;
	$widgets =  dcmd_get_dashboard_widgets();
	update_option('dcmd_dashboard_widgets', $widgets);
	foreach ($widgets as $widget){
		if(!current_user_can(get_option('dcmd_'.$widget['id']))) { 
			unset($wp_meta_boxes['dashboard'][$widget['context']][$widget['priority']][$widget['id']]);
		}
	}
}

/**
 *	Get array of capabilities or generate dropdown list for options page
 * 	@args	bool (optional) $selectlist, bool (optional) $name
 * 	@return array|string
 */
function dcmd_get_capabilities($selectlist = FALSE, $name = FALSE) {
	global $wp_roles;
	$option = get_option($name);	
	if ($selectlist) {
		$cap = '<select name="' . $name .'">';
		$cap .= '<option value="do-everything">Nobody</option>';	
		foreach ($wp_roles->roles['administrator']['capabilities'] as $key=>$val) {
			$cap .= '<option value="' . $key . '"';
			if ($option == $key) $cap .= ' selected="yes"';
			$cap .= '>' . $key . '</option>';		
		}
		$cap .= '</select>';
		return $cap;
	} else {
		return $wp_roles->roles['administrator']['capabilities'];
	}
}

/**
 *	Generate an array of registered dashboard widgets
 * 	@args 	none
 * 	@return array
 */
function dcmd_get_dashboard_widgets() {
	global $wp_meta_boxes;
	$widgets = array();
	if (isset($wp_meta_boxes['dashboard'])) {
		foreach($wp_meta_boxes['dashboard'] as $context=>$data){
			foreach($data as $priority=>$data){
				foreach($data as $widget=>$data){
					//echo $context.' > '.$priority.' > '.$widget.' > '.$data['title']."\n";
					$widgets[$widget] = array('id' => $widget,
									   'title' => strip_tags(preg_replace('/ <span.*span>/im', '', $data['title'])),
									   'context' => $context,
									   'priority' => $priority
									   );
				}
			}
		}
	}
	return $widgets;
}

/**
 *	Add options page to admin menu
 * 	@args 	none
 * 	@return void
 */
function dcmd_admin_menu() {
	if (function_exists('add_options_page')) {
		 add_options_page('Dashboard Commander', 'Dashboard Commander', 'manage_options', __FILE__, 'dcmd_admin_page');
	}
 }

/**
 *	Generate options page content
 * 	@args 	none
 * 	@return string
 */
function dcmd_admin_page() {
	if (empty($title)) $title = __('Dashboard Commander Options');
	$widgets = get_option('dcmd_dashboard_widgets');
?>
	<div class="wrap">
	<?php screen_icon(); ?>
	<h2><?php echo esc_html($title); ?></h2>
	<?php if($widgets): ?>
	<form method="post" action="options.php">
		<?php settings_fields('dcmd_options'); ?>
		<p>Select the minimum access level you want to make a dashboard widget visible to.</p>
		<table class="form-table">
			<tbody>
		<?php foreach($widgets as $widget): ?>
				<tr valign="top">
					<th scope="row">
						<label for="<?php echo 'dcmd_'.$widget['id'] ?>"><?php echo $widget['title'] ?></label>
					</th>
					<td><?php echo(dcmd_get_capabilities(TRUE, 'dcmd_'.$widget['id'])); ?></td>
				</tr>
		<?php endforeach; ?> 
			</tbody>
		</table>	
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
		</p>
		<p><strong>Note:</strong> You cannot force a widget to be visible if it was never visible to a capability to begin with. Example: It is not possible to make the 'Recent Comments' widget visible to a user with an 'edit_published_posts' or lower capability.  This is due to the widget's specific add-to-dashboard code and not a limitation of this plugin.</p>
		<p>Not familiar with WordPress capabilities? Refer to the <a href="http://codex.wordpress.org/Roles_and_Capabilities#Capability_vs._Role_Table" target="_blank">WordPress Codex</a> to see how capabilities relate to user roles</p>
	</form>
	<?php endif; ?>
	</div>
<?php
}