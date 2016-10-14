<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/shamimmoeen/
 * @since      1.0.0
 *
 * @package    Picasso_Ideas
 * @subpackage Picasso_Ideas/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Picasso_Ideas
 * @subpackage Picasso_Ideas/public
 * @author     Shamim Al Mamun <shamim.moeen@gmail.com>
 */
class Picasso_Ideas_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Picasso_Ideas_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Picasso_Ideas_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		// register styles
		wp_register_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/picasso-ideas-public.css', array(), $this->version, 'all' );
		wp_register_style( 'remodal-styles', plugin_dir_url( __FILE__ ) . 'plugins/remodal/remodal.css', array(), $this->version, 'all' );
		wp_register_style( 'remodal-default-styles', plugin_dir_url( __FILE__ ) . 'plugins/remodal/remodal-default-theme.css', array(), $this->version, 'all' );
		wp_register_style( 'select2-styles', plugin_dir_url( __FILE__ ) . 'plugins/select2/select2.css', array(), $this->version, 'all' );
		wp_register_style( 'zebra_datepicker-styles', plugin_dir_url( __FILE__ ) . 'plugins/zebra_datepicker/css/default.css', array(), $this->version, 'all' );
		wp_register_style( 'jquery_raty-styless', plugin_dir_url( __FILE__ ) . 'plugins/raty/jquery.raty.css', array(), $this->version, 'all' );

		// Load styles only for idea archive and single idea pages
		global $post, $picasso_ideas;

		$create_idea_page_id = $picasso_ideas['idea_create_page'];
		$edit_idea_page_id = $picasso_ideas['idea_edit_page'];

		if (is_post_type_archive('idea')
			|| is_singular('idea')
			|| is_singular('campaign')
			|| ($post && $post->ID == $create_idea_page_id)
			|| ($post && $post->ID == $edit_idea_page_id)
			// include css and js for my profile 's idea to show review
			|| bp_is_my_profile()) {
			wp_enqueue_style( $this->plugin_name );
			wp_enqueue_style( 'remodal-styles' );
			wp_enqueue_style( 'remodal-default-styles' );
		}

		// load styles only for single idea page
		if (is_singular('idea')
			|| is_singular('campaign')
			// include css and js for my profile 's idea to show review
			|| bp_is_my_profile()) {
			wp_enqueue_style( 'select2-styles' );
			wp_enqueue_style( 'zebra_datepicker-styles' );
			wp_enqueue_style( 'jquery_raty-styless' );
		}

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Picasso_Ideas_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Picasso_Ideas_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( 'picasso_params', plugin_dir_url( __FILE__ ), array( 'jquery' ), $this->version, true );
		wp_localize_script( 'picasso_params', 'picasso_ideas_params',
			array(
				'ajaxurl' => admin_url('admin-ajax.php'),
			)
		);

		// register scripts
		wp_register_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/picasso-ideas-public.js', array( 'jquery' ), $this->version, true );
		wp_register_script( 'remodal-js', plugin_dir_url( __FILE__ ) . 'plugins/remodal/remodal.min.js', array( 'jquery' ), $this->version, true );
		wp_register_script( 'picasso_add_comment_modal-js', plugin_dir_url( __FILE__ ) . 'js/picasso-ideas-add-comment.js', array( 'jquery' ), $this->version, true );
		wp_register_script( 'chart-js', plugin_dir_url( __FILE__ ) . 'js/Chart.bundle.min.js', array( 'jquery' ), $this->version, true );
		wp_register_script( 'picasso-ideas-chart-js', plugin_dir_url( __FILE__ ) . 'js/picasso-ideas-chart.js', array( 'jquery' ), $this->version, true );
		wp_register_script( 'picasso_edit_comment_modal-js', plugin_dir_url( __FILE__ ) . 'js/picasso-ideas-edit-comment.js', array( 'jquery' ), $this->version, true );
		wp_register_script( 'select2-js', plugin_dir_url( __FILE__ ) . 'plugins/select2/select2.min.js', array( 'jquery' ), $this->version, true );
		wp_register_script( 'zebra_datepicker-js', plugin_dir_url( __FILE__ ) . 'plugins/zebra_datepicker/javascript/zebra_datepicker.js', array( 'jquery' ), $this->version, true );
		wp_register_script( 'jquery_raty-js', plugin_dir_url( __FILE__ ) . 'plugins/raty/jquery.raty.js', array( 'jquery' ), $this->version, true );
		wp_register_script( 'jquery_stickytabs-js', plugin_dir_url( __FILE__ ) . 'js/jquery.stickytabs.js', array( 'jquery' ), $this->version, true );
		wp_register_script( 'picasso_upload-js', plugin_dir_url( __FILE__ ) . 'js/picasso-ideas-upload-file.js', array( 'jquery' ), $this->version, true );

		// Load scripts only for idea archive and single idea pages
		global $post, $picasso_ideas;

		$create_idea_page_id = $picasso_ideas['idea_create_page'];
		$edit_idea_page_id = $picasso_ideas['idea_edit_page'];

		if (is_post_type_archive('idea')
			|| is_singular('idea')
			|| is_singular('campaign')
			|| ($post && $post->ID == $create_idea_page_id)
			|| ($post && $post->ID == $edit_idea_page_id)
			// include css and js for my profile 's idea to show review
			|| bp_is_my_profile()) {
			wp_enqueue_script( $this->plugin_name );
			wp_enqueue_script( 'remodal-js' );
			wp_enqueue_script( 'picasso_add_comment_modal-js' );
			wp_dequeue_script( 'cpm_chart' );
			wp_enqueue_script( 'chart-js' );
			wp_enqueue_script( 'picasso-ideas-chart-js' );
		}

		// load styles only for single idea page
		if (is_singular('idea')
			|| is_singular('campaign')
			// include css and js for my profile 's idea to show review
			|| bp_is_my_profile()) {
			wp_enqueue_script( 'picasso_edit_comment_modal-js' );
			wp_enqueue_script( 'select2-js' );
			wp_enqueue_script( 'zebra_datepicker-js' );
			wp_enqueue_script( 'jquery_raty-js' );
			wp_enqueue_script( 'jquery_stickytabs-js' );
		}

		if ($post->ID == $create_idea_page_id || $post->ID == $edit_idea_page_id) {
			wp_enqueue_script( 'picasso_upload-js' );
		}

	}

}
