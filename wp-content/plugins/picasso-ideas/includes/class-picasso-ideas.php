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
 * @package    Picasso_Ideas
 * @subpackage Picasso_Ideas/includes
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
 * @package    Picasso_Ideas
 * @subpackage Picasso_Ideas/includes
 * @author     Shamim Al Mamun <shamim.moeen@gmail.com>
 */
class Picasso_Ideas {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Picasso_Ideas_Loader    $loader    Maintains and registers all hooks for the plugin.
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

		$this->plugin_name = 'picasso-ideas';
		$this->version = '1.0.2';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_constants();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->register_post_types();
		$this->manage_custom_columns();
		$this->include_templates();
		$this->increment_idea_views();
		$this->register_ajax_callbacks();
		$this->sort_ideas();
		
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Picasso_Ideas_Loader. Orchestrates the hooks of the plugin.
	 * - Picasso_Ideas_i18n. Defines internationalization functionality.
	 * - Picasso_Ideas_Admin. Defines all hooks for the admin area.
	 * - Picasso_Ideas_Public. Defines all hooks for the public side of the site.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-picasso-ideas-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-picasso-ideas-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-picasso-ideas-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-picasso-ideas-public.php';

		/**
		 * Register custom post type 'idea'
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-picasso-ideas-post-types.php';

		/**
		 * Plugin Options Panel
		 */
		if (!class_exists('ReduxFramework')) {
			$framework = get_template_directory() . '/kleo-framework/options/framework.php';

			if (file_exists($framework)) {
				require_once($framework);
				require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/plugin-options-panel.php';
			}
		}

		/**
		 * Load idea metaboxed
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/idea-metaboxes.php';

		/**
		 * Manage Sessions in wordpress
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-picasso-ideas-session.php';

		/**
		 * Idea Frontend Submission
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-picasso-ideas-submission.php';

		/**
		 * Picasso_Idea_Review
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-picasso-ideas-review.php';

		/**
		 * Helper functions
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/helper-functions.php';

		/**
		 * Idea functions
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/idea-functions.php';

		/**
		 * CMB2 custom field - users_with_avatar
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/cmb2-custom-fields/users-with-avatar.php';

		$this->loader = new Picasso_Ideas_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Picasso_Ideas_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Picasso_Ideas_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Define constants for this plugin.
	 */
	public function define_constants() {
		$this->define('IDEAS_CACHE_TIME', 60*60*12);
		$this->define('IDEAS_LOCALE', $this->plugin_name);
		$this->define('IDEAS_PLUGIN_URL', plugin_dir_url(dirname(__FILE__)));
		$this->define('IDEAS_PLUGIN_PATH', plugin_dir_path(dirname(__FILE__)));
		$this->define('IDEAS_TEMPLATE_PATH', plugin_dir_path(dirname(__FILE__)) . 'templates/');
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Picasso_Ideas_Admin( $this->get_plugin_name(), $this->get_version() );

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

		$plugin_public = new Picasso_Ideas_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts', 15 );

	}

	/**
	 * Register custom post types and include templates
	 */
	public function register_post_types() {
		$plugin = new Picasso_Ideas_Post_Types();

		$this->loader->add_action('init', $plugin, 'register_post_types');
	}

	/**
	 * Manage custom columns of idea post type
	 */
	public function manage_custom_columns() {
		$plugin = new Picasso_Ideas_Post_Types();

		$this->loader->add_filter('manage_idea_posts_columns', $plugin, 'add_campaign_custom_column');
		$this->loader->add_filter('manage_idea_posts_custom_column', $plugin, 'set_data_to_custom_column_campaign', 10, 2);
		$this->loader->add_filter('manage_edit-idea_sortable_columns', $plugin, 'make_custom_campaign_column_sortable');
		$this->loader->add_action('pre_get_posts', $plugin, 'sort_ideas_by_campaign_id');
	}

	/**
	 * Include templates
	 */
	public function include_templates() {
		$plugin = new Picasso_Ideas_Post_Types();

		// include archive template
		$this->loader->add_action('template_include', $plugin, 'include_archive_tempate');

		// include single template
		$this->loader->add_action('single_template', $plugin, 'include_single_tempate');
	}

	/**
	 * Increment idea views
	 */
	public function increment_idea_views() {
		$plugin = new Picasso_Ideas_Post_Types();

		$this->loader->add_action('single_template', $plugin, 'increment_idea_views');
	}

	/**
	 * Sort ideas
	 */
	public function sort_ideas() {
		$plugin = new Picasso_Ideas_Post_Types();

		$this->loader->add_action('pre_get_posts', $plugin, 'sort_ideas');
	}

	/**
	 * Register ajax callbacks
	 */
	public function register_ajax_callbacks() {
		$plugin = new Picasso_Ideas_Post_Types();

		// Put idea in favorites
		$this->loader->add_action('wp_ajax_idea_favorites', $plugin, 'put_idea_in_favorites');

		// Add comment from modal
		$this->loader->add_action('wp_ajax_add_idea_comment', $plugin, 'add_comment_from_modal');

		// Update comment from modal
		$this->loader->add_action('wp_ajax_update_idea_comment', $plugin, 'update_comment_from_modal');
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
	 * @return    Picasso_Ideas_Loader    Orchestrates the hooks of the plugin.
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