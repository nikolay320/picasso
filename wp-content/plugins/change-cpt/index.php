<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @wordpress-plugin
 * Plugin Name:       Change CPT
 * Plugin URI:        https://github.com/shamimmoeen/
 * Description:       Change custom post types ideas, campaigns to idea, campaign
 * Version:           1.0.0
 * Author:            Shamim Al Mamun
 * Author URI:        https://github.com/shamimmoeen/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       changecpt
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once('plugin-settings.php');
require_once('helper-functions.php');