<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/shamimmoeen/
 * @since             1.0.0
 * @package           Picasso_Campaigns
 *
 * @wordpress-plugin
 * Plugin Name:       Picasso Campaigns
 * Plugin URI:        https://github.com/shamimmoeen/
 * Description:       A custom campaigns plugin.
 * Version:           1.0.0
 * Author:            Shamim Al Mamun
 * Author URI:        https://github.com/shamimmoeen/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       picasso-campaigns
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-picasso-campaigns-activator.php
 */
function activate_picasso_campaigns() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-picasso-campaigns-activator.php';
	Picasso_Campaigns_Activator::activate();

	// Flash rewrite rules
	flush_rewrite_rules();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-picasso-campaigns-deactivator.php
 */
function deactivate_picasso_campaigns() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-picasso-campaigns-deactivator.php';
	Picasso_Campaigns_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_picasso_campaigns' );
register_deactivation_hook( __FILE__, 'deactivate_picasso_campaigns' );

// Load wp-admin/includes/plugin.php
if (!function_exists('is_plugin_active')) {
	require_once(ABSPATH . 'wp-admin/includes/plugin.php');
}

// Check if picasso-ideas plugin is activated
if (is_plugin_active('picasso-ideas/picasso-ideas.php')) {
	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	require plugin_dir_path( __FILE__ ) . 'includes/class-picasso-campaigns.php';

	/**
	 * Begins execution of the plugin.
	 *
	 * Since everything within the plugin is registered via hooks,
	 * then kicking off the plugin from this point in the file does
	 * not affect the page life cycle.
	 *
	 * @since    1.0.0
	 */
	function run_picasso_campaigns() {

		$plugin = new Picasso_Campaigns();
		$plugin->run();

	}
	run_picasso_campaigns();
} else {
	if (!function_exists('pc_plugin_required_notice')) {
		function pc_plugin_required_notice() {
			?>
			<div class="error">
				<p><?php _e('Picasso Campaigns plugin requires <strong>Picasso Ideas</strong> plugin to work.', 'picasso-campaigns'); ?></p>
			</div>
			<?php
		}
		add_action('admin_notices', 'pc_plugin_required_notice');
	}
}