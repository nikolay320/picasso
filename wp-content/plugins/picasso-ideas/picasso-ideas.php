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
 * @since             1.0.2
 * @package           Picasso_Ideas
 *
 * @wordpress-plugin
 * Plugin Name:       Picasso Ideas
 * Plugin URI:        https://github.com/shamimmoeen/
 * Description:       A plugin that enables user ideas, reviews, ratings on campaigns.
 * Version:           1.0.0
 * Author:            Shamim Al Mamun
 * Author URI:        https://github.com/shamimmoeen/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       picasso-ideas
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-picasso-ideas-activator.php
 */
function activate_picasso_ideas() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-picasso-ideas-activator.php';
	Picasso_Ideas_Activator::activate();

	// Flash rewrite rules
	flush_rewrite_rules();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-picasso-ideas-deactivator.php
 */
function deactivate_picasso_ideas() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-picasso-ideas-deactivator.php';
	Picasso_Ideas_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_picasso_ideas' );
register_deactivation_hook( __FILE__, 'deactivate_picasso_ideas' );

/**
 * Start Session
 */
if (!function_exists('pi_start_session')) {
	function pi_start_session() {
		if (!session_id()) {
			session_start();
		}
	}
	add_action('init', 'pi_start_session');
}

// Load wp-admin/includes/plugin.php
if (!function_exists('is_plugin_active')) {
	require_once(ABSPATH . 'wp-admin/includes/plugin.php');
}

// Check if picasso-campaigns plugin is activated
if (is_plugin_active('picasso-campaigns/picasso-campaigns.php')) {
	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	require plugin_dir_path( __FILE__ ) . 'includes/class-picasso-ideas.php';

	/**
	 * Begins execution of the plugin.
	 *
	 * Since everything within the plugin is registered via hooks,
	 * then kicking off the plugin from this point in the file does
	 * not affect the page life cycle.
	 *
	 * @since    1.0.0
	 */
	function run_picasso_ideas() {

		$plugin = new Picasso_Ideas();
		$plugin->run();

	}
	run_picasso_ideas();
} else {
	if (!function_exists('pi_plugin_required_notice')) {
		function pi_plugin_required_notice() {
			?>
			<div class="error">
				<p><?php _e('Picasso Ideas plugin requires <strong>Picasso Campaigns</strong> plugin to work.', 'picasso-ideas'); ?></p>
			</div>
			<?php
		}
		add_action('admin_notices', 'pi_plugin_required_notice');
	}
}