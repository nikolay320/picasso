<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('Idea')) {
	/**
	* Idea Class
	*/
	class Idea	{
		/**
		 * Plugin version, used for cache-busting of style and script file references.
		 *
		 * @var string
		 */
		public $version = '1.0.2';

		/**
		 * Unique identifier for the plugin.
		 *
		 * The variable name is used as the text domain when internationalizing strings of text.
		 *
		 * @var string
		 */
		public $plugin_slug;

		/**
		 * A reference to an instance of this class.
		 *
		 * @var Idea
		 */
		private static $_instance = null;

		/**
		 * Initialize the plugin.
		 */
		public function __construct() {
			// at first load scripts for localization
			$this->plugin_slug = 'ideas_plugin';
			add_action('init', array($this, 'loadPluginTextdomain'));

			add_action('plugins_loaded', array($this, 'loaded'));

			// activation hook
			register_activation_hook(__FILE__, array($this, 'onActivation'));
		}

		/**
		 * Init this plugin when wordpress loaded.
		 */
		public function loaded() {}

		/**
		 * Define constants for this plugin.
		 */
		public function defineConstants() {
			$this->define('IDEAS_TEXT_DOMAIN', $this->plugin_slug);
			$this->define('IDEAS_URL', plugins_url( '', __FILE__ ) . '/');
			$this->define('IDEAS_DIR', plugin_dir_path(__FILE__) . '/');
			$this->define('IDEAS_TEMPLATE_PATH', plugin_dir_path(__FILE__) . '/templates/');
			$this->define('IDEAS_ASSETS', plugins_url( '', __FILE__ ) . '/assets/');
		}

		/**
		 * Include required files.
		 */
		public function includes() {
			require_once(MP_DIR . 'includes/class-menu.php');
			require_once(MP_DIR . 'includes/functions.php');
			require_once(MP_DIR . 'includes/plugin-settings.php');
		}

		/**
		 * Frontend Scripts
		 */
		public function frontentScripts() {
			wp_enqueue_style( 'ideas-styles', plugins_url( '/assets/css/style.css?v='.time(),__FILE__ ) );
			wp_enqueue_script( 'ideas-js', plugins_url( '/assets/js/ideas-scripts.js?v='.time(),__FILE__ ), 'jquery', '1.0', true );

			// select2
			wp_register_style( 'select2-styles', plugins_url( '/assets/select2/select2.css?v='.time(),__FILE__ ) );
			wp_register_script( 'select2-js', plugins_url( '/assets/select2/select2.min.js?v='.time(),__FILE__ ), 'jquery', '1.0', true );

			// zebra_datepicker
			wp_register_style( 'zebra_datepicker-styles', plugins_url( '/assets/zebra_datepicker/css/default.css?v='.time(),__FILE__ ) );
			wp_register_script( 'zebra_datepicker-js', plugins_url( '/assets/zebra_datepicker/javascript/zebra_datepicker.js?v='.time(),__FILE__ ), 'jquery', '1.0', true );
			
			// bootstrap notify
			wp_register_script( 'bootstrap_notify-js', plugins_url( '/assets/js/bootstrap-notify.min.js?v='.time(),__FILE__ ), 'jquery', '1.0', true );

			// chart.js
			wp_register_script( 'chart-js', plugins_url( '/assets/js/Chart.bundle.min.js?v='.time(),__FILE__ ), 'jquery', '1.0', true );
			
			// jquery_raty
			wp_register_style( 'jquery_raty-styles', plugins_url( '/assets/raty/jquery.raty.css?v='.time(),__FILE__ ) );
			wp_register_script( 'jquery_raty-js', plugins_url( '/assets/raty/jquery.raty.js?v='.time(),__FILE__ ), 'jquery', '1.0', true );

			wp_register_script( 'jquery.stickytabs', plugins_url( '/assets/js/jquery.stickytabs.js?v='.time(),__FILE__ ), 'jquery', '1.0', true );

			// cmb2-frontend-form
			wp_register_style( 'cmb2-frontend-form', plugins_url( '/assets/css/cmb2-frontend-form.css?v='.time(),__FILE__ ) );

			// jquery modal
			wp_register_style( 'jquery-modal', plugins_url( '/assets/jquery-modal/jquery.modal.css?v='.time(),__FILE__ ) );
			wp_register_script( 'jquery-modal', plugins_url( '/assets/jquery-modal/jquery.modal.js?v='.time(),__FILE__ ), 'jquery', '1.0', true );

			// tingle modal
			wp_register_style( 'tingle-modal', plugins_url( '/assets/tingle/tingle.css?v='.time(),__FILE__ ) );
			wp_register_script( 'tingle-modal', plugins_url( '/assets/tingle/tingle.js?v='.time(),__FILE__ ), 'jquery', '1.0', true );

			// reveal modal
			wp_register_style( 'reveal-modal', plugins_url( '/assets/reveal/reveal.css?v='.time(),__FILE__ ) );
			wp_register_script( 'reveal-modal', plugins_url( '/assets/reveal/reveal.js?v='.time(),__FILE__ ), 'jquery', '1.0', true );

			// remodal
			wp_register_style( 'remodal-style', plugins_url( '/assets/remodal/remodal.css?v='.time(),__FILE__ ) );
			wp_register_style( 'remodal-default-style', plugins_url( '/assets/remodal/remodal-default-theme.css?v='.time(),__FILE__ ) );
			wp_register_script( 'remodal-script', plugins_url( '/assets/remodal/remodal.js?v='.time(),__FILE__ ), 'jquery', '1.0', true );

			// upload media
			wp_register_script( 'custom-upload-media', plugins_url( '/assets/js/custom-upload-media.js?v='.time(),__FILE__ ), 'jquery', '1.0', true );
		}

		/**
		 * Backend Scripts
		 */
		public function backendScripts() {}

		/**
		 * Returns an instance of this class.
		 *
		 * @return Idea
		 */
		public static function instance() {
			if (!isset(self::$_instance)) {
				self::$_instance = new Idea();
			}

			return self::$_instance;
		}

		/**
		 * Load the plugin text domain for translation.
		 */
		public function loadPluginTextdomain() {
			load_plugin_textdomain(IDEAS_TEXT_DOMAIN, false, basename(dirname(__FILE__)) . '/lang/');
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

		/**
		 * Get the plugin Path.
		 *
		 * @return string
		 */
		public function pluginPath() {
			return untrailingslashit(plugin_dir_url(__FILE__));
		}

		/**
		 * Run at plugin activation
		 *
		 * @return null
		 */
		public function onActivation() {}
	}
}