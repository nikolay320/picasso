<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/shamimmoeen/
 * @since      1.0.0
 *
 * @package    Picasso_Campaigns
 * @subpackage Picasso_Campaigns/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Picasso_Campaigns
 * @subpackage Picasso_Campaigns/includes
 * @author     Shamim Al Mamun <shamim.moeen@gmail.com>
 */
class Picasso_Campaigns {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Picasso_Campaigns_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'picasso-campaigns';
		$this->version = '1.0.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_constants();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->register_post_types();
		$this->include_templates();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Picasso_Campaigns_Loader. Orchestrates the hooks of the plugin.
	 * - Picasso_Campaigns_i18n. Defines internationalization functionality.
	 * - Picasso_Campaigns_Admin. Defines all hooks for the admin area.
	 * - Picasso_Campaigns_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-picasso-campaigns-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-picasso-campaigns-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-picasso-campaigns-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-picasso-campaigns-public.php';

		/**
		 * Register custom post type 'campaign'
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-picasso-campaigns-post-type.php';

		/**
		 * CMB2 custom field - users_with_avatar
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/cmb2-custom-fields/campaign-ideas.php';

		/**
		 * Load campaign metaboxed
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/campaign-metaboxes.php';

		/**
		 * Helper functions
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/helper-functions.php';

		/**
		 * Campaign functions
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/campaign-functions.php';

		$this->loader = new Picasso_Campaigns_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Picasso_Campaigns_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Picasso_Campaigns_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Define constants for this plugin.
	 */
	public function define_constants() {
		$this->define('CAMPAIGNS_CACHE_TIME', 60*60*12);
		$this->define('CAMPAIGNS_LOCALE', $this->plugin_name);
		$this->define('CAMPAIGNS_PLUGIN_URL', plugin_dir_url(dirname(__FILE__)));
		$this->define('CAMPAIGNS_PLUGIN_PATH', plugin_dir_path(dirname(__FILE__)));
		$this->define('CAMPAIGNS_TEMPLATE_PATH', plugin_dir_path(dirname(__FILE__)) . 'templates/');
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Picasso_Campaigns_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Picasso_Campaigns_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Register custom post type 'campaign'
	 */
	public function register_post_types() {
		$plugin = new Picasso_Campaigns_Post_Type();

		$this->loader->add_action('init', $plugin, 'register_post_type');
	}

	/**
	 * Include templates
	 */
	public function include_templates() {
		$plugin = new Picasso_Campaigns_Post_Type();

		// include archive template
		$this->loader->add_action('template_include', $plugin, 'include_archive_tempate');

		// include single template
		$this->loader->add_action('single_template', $plugin, 'include_single_tempate');
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Picasso_Campaigns_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Define constants if not already defined.
	 *
	 * @param  string $name
	 * @param  string|bool $value
	 */
	public function define($name, $value) {
		if (!defined($name)) {
			define($name, $value);
		}
	}

}
