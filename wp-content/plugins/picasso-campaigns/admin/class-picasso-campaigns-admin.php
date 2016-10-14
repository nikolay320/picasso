<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/shamimmoeen/
 * @since      1.0.0
 *
 * @package    Picasso_Campaigns
 * @subpackage Picasso_Campaigns/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Picasso_Campaigns
 * @subpackage Picasso_Campaigns/admin
 * @author     Shamim Al Mamun <shamim.moeen@gmail.com>
 */
class Picasso_Campaigns_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles($hook) {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Picasso_Campaigns_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Picasso_Campaigns_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		// Register styles
		wp_register_style( 'select2-styles', CAMPAIGNS_PLUGIN_URL . 'public/plugins/select2/select2.css', array(), $this->version, 'all' );

		global $post_type;

		if ($hook != 'edit.php' && $hook != 'post.php' && $hook != 'post-new.php') {
			return;
		}

		if ($post_type !== 'campaign') {
			return;
		}

		wp_enqueue_style( 'select2-styles' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts($hook) {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Picasso_Campaigns_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Picasso_Campaigns_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		// Register scripts
		wp_register_script( 'select2-js', CAMPAIGNS_PLUGIN_URL . 'public/plugins/select2/select2.min.js', array( 'jquery' ), $this->version, false );
		wp_register_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/picasso-campaigns-admin.js', array( 'jquery' ), $this->version, false );

		global $post_type;

		if ($hook != 'edit.php' && $hook != 'post.php' && $hook != 'post-new.php') {
			return;
		}

		if ($post_type !== 'campaign') {
			return;
		}

		wp_enqueue_script( 'select2-js' );
		wp_enqueue_script( $this->plugin_name );

	}

}
